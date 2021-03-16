----------- DROP TYPE int_arret_cptes -----------------
DROP TYPE IF EXISTS int_arret_cptes CASCADE ;

CREATE TYPE INT_ARRET_CPTES AS
(
  num_cpte             INTEGER,
  id_titulaire         INTEGER,
  solde_calcul_interet REAL,
  capitalisation       INTEGER,
  classe_comptable     INTEGER,
  interet_cpte         REAL,
  substitut            TEXT,
  id_mouvement         INTEGER,
  solde                NUMERIC,
  date_transaction     TIMESTAMP
);
-------------------------------------------------------

----------- DROP fonction arrete_comptes(date) -----------------
CREATE OR REPLACE function arrete_comptes(date) returns SETOF int_arret_cptes
LANGUAGE plpgsql
AS $$
DECLARE
	  date_batch ALIAS FOR $1;     				-- la date d'éxécution du batch
	  prec INTEGER;          			 				-- précision de la devise du compte
	  base_taux_agence INTEGER;    				-- la base du taux de l'agence telle que parametrée(1 ou 2)
	  base_taux_mois INTEGER ;
 	  resultat int_arret_cptes;    				-- ensemble d'informations renvoyées par cette procédure stockée
	  ligne RECORD;        								-- ensemble d'informations sur le compte encours de traitement
 	  jr_annee INTEGER;      							-- nombre de jours dans l'année en fonction du base_taux_agence(360 ou 365)
 	  interet NUMERIC(30,6);       				-- le montant de la rémunération(intérêts)
 	  cpte_destination_int INTEGER;     	-- numéro du compte de destination des intérêts
 	  cpte_cpta_assoc_prod_ep RECORD;     -- informations(id du produit,numéro du cpte comptable) sur le compte comptable associé au produit d'épargne
 	  cpte_concerne TEXT;      						-- numéro du cpte comptable associé au produit, il dépend du type de compte d'épargne(simple ou nantie)
 	  rep INTEGER;            						--valeur de retour de la fonction payeinteret
	  devise_assoc_prod TEXT;      				-- devise du compte associé au produit d'épargne
	  nouvo_solde float4;      						-- nouveau solde à  afficher
	  num_cpte_cpta_prod_ep TEXT;    			-- numero du compte comptable du produit d'epargne
	  compte_IAP TEXT;					-- numero du compte IAP : adsys_calc_int_paye.cpte_cpta_int_paye
	  agence RECORD;    -- JIRA MAE-22 pour recuperer le champ 'appl_date_val_classique'
	  -- Message queue
	  v_id_mouvement_cpte_interne_cli INTEGER; 		-- valeur de retour de la fonction payeinteret
	  v_date_transaction TIMESTAMP;
	  v_nouvo_solde NUMERIC;

		-- Récupère des infos sur les comptes épargne à  rémunérer
	  --Cpt_Calcul_Int NO SCROLL CURSOR FOR
	 --	SELECT  a.id_cpte,a.mode_calcul_int_cpte,a.solde_calcul_interets,a.tx_interet_cpte,a.interet_a_capitaliser,a.devise,a.cpt_vers_int,a.dat_date_fin,a.id_titulaire,
	 --	        getPeriodeCapitalisation(date(date_batch), date(a.date_ouvert), date(a.date_calcul_interets)) as perio_cap, b.classe_comptable, b.id as id_prod_epargne,
	 --			b.cpte_cpta_prod_ep, b.cpte_cpta_prod_ep_int
	 --	FROM ad_cpt a, adsys_produit_epargne b
	 --	WHERE a.id_prod = b.id AND a.id_ag = NumAgc() AND a.id_ag = b.id_ag AND (
	 --		(etat_cpte=1 AND terme_cpte > 0 AND tx_interet_cpte > 0 AND date(dat_date_fin) = date(date_batch))
	 --		OR (etat_cpte=1 AND tx_interet_cpte > 0 AND mode_paiement_cpte = 1 AND freq_calcul_int_cpte = 1 AND isFinMois(date(date_batch)) AND isRemunerable(date(date_batch), date_ouvert, marge_tolerance))
	 --		OR (etat_cpte=1 AND tx_interet_cpte > 0 AND mode_paiement_cpte = 1 AND freq_calcul_int_cpte = 2 AND isFinTrimestre(date(date_batch)) AND isRemunerable(date(date_batch), date_ouvert, marge_tolerance))
	 --		OR (etat_cpte=1 AND tx_interet_cpte > 0 AND mode_paiement_cpte = 1 AND freq_calcul_int_cpte = 3 AND isFinSemestre(date(date_batch)) AND isRemunerable(date(date_batch), date_ouvert, marge_tolerance))
	 --		OR (etat_cpte=1 AND tx_interet_cpte > 0 AND mode_paiement_cpte = 1 AND freq_calcul_int_cpte = 4 AND isFinAnnee(date(date_batch)) AND isRemunerable(date(date_batch), date_ouvert, marge_tolerance))
	 --		OR (etat_cpte=1 AND tx_interet_cpte > 0 AND mode_paiement_cpte = 2 AND isMultipleFrequence(date(date_batch),date_ouvert,freq_calcul_int_cpte))
	 --		OR (etat_cpte=1 AND tx_interet_cpte > 0 AND mode_paiement_cpte = 3 AND b.mode_calcul_int = 12 AND (isFinAnnee(date(date_batch)) OR date(b.ep_source_date_fin)=date(date_batch)))
	 --		)
	 --		AND ((solde > seuil_rem_dav and classe_comptable = 1) or classe_comptable != 1) order by a.id_cpte ;

 	BEGIN

	 	--récupération de la devise de référence : besoin pour écriture comptable généralement en multidevise------------------------
	 	--SELECT INTO code_dev_ref code_devise_reference FROM ad_agc;
	 	SELECT INTO base_taux_agence base_taux_epargne from ad_agc where id_ag = NumAgc();
	 	IF base_taux_agence  = 1 THEN
	 	    jr_annee := 360;
	 	  ELSE
	 	    jr_annee := 365;
	 	END IF;

	 	DROP TABLE IF EXISTS arrete_comptes;

		CREATE TEMP TABLE arrete_comptes AS SELECT a.id_cpte,a.mode_calcul_int_cpte,a.solde_calcul_interets,a.tx_interet_cpte,a.interet_a_capitaliser,a.devise,a.cpt_vers_int,a.dat_date_fin,a.id_titulaire,
	 	        getPeriodeCapitalisation(date(date_batch), date(a.date_ouvert), date(a.date_calcul_interets), date(a.dat_date_fin)) as perio_cap, b.classe_comptable, b.id as id_prod_epargne,
	 			b.cpte_cpta_prod_ep, b.cpte_cpta_prod_ep_int
	 	FROM ad_cpt a, adsys_produit_epargne b
	 	WHERE a.id_prod = b.id AND a.id_ag = NumAgc() AND a.id_ag = b.id_ag AND (
	 		(etat_cpte=1 AND terme_cpte > 0 AND tx_interet_cpte > 0 AND date(dat_date_fin) = date(date_batch))
	 		OR (etat_cpte=1 AND tx_interet_cpte > 0 AND mode_paiement_cpte = 1 AND freq_calcul_int_cpte = 1 AND isFinMois(date(date_batch)) AND isRemunerable(date(date_batch), date_ouvert, marge_tolerance))
	 		OR (etat_cpte=1 AND tx_interet_cpte > 0 AND mode_paiement_cpte = 1 AND freq_calcul_int_cpte = 2 AND isFinTrimestre(date(date_batch)) AND isRemunerable(date(date_batch), date_ouvert, marge_tolerance))
	 		OR (etat_cpte=1 AND tx_interet_cpte > 0 AND mode_paiement_cpte = 1 AND freq_calcul_int_cpte = 3 AND isFinSemestre(date(date_batch)) AND isRemunerable(date(date_batch), date_ouvert, marge_tolerance))
	 		OR (etat_cpte=1 AND tx_interet_cpte > 0 AND mode_paiement_cpte = 1 AND freq_calcul_int_cpte = 4 AND isFinAnnee(date(date_batch)) AND isRemunerable(date(date_batch), date_ouvert, marge_tolerance))
	 		OR (etat_cpte=1 AND tx_interet_cpte > 0 AND mode_paiement_cpte = 2 AND isMultipleFrequence(date(date_batch),date_ouvert,freq_calcul_int_cpte))
	 		OR (etat_cpte=1 AND tx_interet_cpte > 0 AND mode_paiement_cpte = 3 AND a.mode_calcul_int_cpte = 12 AND (isFinAnnee(date(date_batch)) OR date(b.ep_source_date_fin)=date(date_batch)))
	 		)
	 		AND ((solde > seuil_rem_dav and classe_comptable = 1) or classe_comptable != 1) order by a.id_cpte ;

	 	---OPEN Cpt_Calcul_Int;
	 	--FETCH Cpt_Calcul_Int INTO ligne;
	 		----------------------INSERTION DANS HISTORIQUE--------------------------------------
	 	--IF FOUND THEN
	 	    INSERT INTO ad_his (type_fonction, infos, date, id_ag)
	 	    	VALUES  (212, makeTraductionLangSyst('intérêts pour le mois écoulé'), now(),NumAgc());
	 	--END IF;
	 	--WHILE FOUND LOOP
	  FOR ligne IN SELECT  * FROM arrete_comptes
	   	LOOP
			----------------------CALCUL DES INTERETS--------------------------------------------
			SELECT INTO agence appl_date_val_classique FROM ad_agc WHERE id_ag = numagc();
			IF agence.appl_date_val_classique IS TRUE THEN
				interet := ligne.solde_calcul_interets * (ligne.tx_interet_cpte * ligne.perio_cap)/jr_annee;
			ELSE
				IF (ligne.mode_calcul_int_cpte <> 12) THEN
					interet := ligne.solde_calcul_interets * (ligne.tx_interet_cpte * ligne.perio_cap)/jr_annee;
				ELSE --mode_calcul_int_cpte=12 epargne à la source
					interet := ligne.interet_a_capitaliser;
				END IF;
			END IF;

      -- On garde les arrondies pour palier a des pertes de precisions lors des calculs d'interets a payer
			-- Verifie si IAP est parametré
			--SELECT INTO compte_IAP cpte_cpta_int_paye FROM adsys_calc_int_paye WHERE cpte_cpta_int_paye IS NOT NULL AND id_ag = numagc();
			--IF (compte_IAP IS NULL OR compte_IAP = '') THEN -- Si non
				-- arrondi par rapport a la precision devise
				SELECT INTO prec precision FROM devise WHERE  code_devise = ligne.devise;
				interet := ROUND(interet, prec);
			--END IF;

      -- RAISE NOTICE 'id_cpte=%  solde_calcul_interets=%  tx_interet_cpte=%  perio_cap=%  interet=% ',ligne.id_cpte, ligne.solde_calcul_interets, ligne.tx_interet_cpte, ligne.perio_cap, interet;

			-----------------------PAIEMENT DES INTERETS ----------------------------
			IF (interet > 0) THEN
			-----------------------Compte de destination des intérêts ----------------------------

				IF (ligne.cpt_vers_int IS NOT NULL) THEN
					cpte_destination_int := ligne.cpt_vers_int; -- cas où le compte de destination des intérêts est différent du compte encours. Ainsi le produit qui nous interesse est celui du compte de destination
				ELSE
					cpte_destination_int := ligne.id_cpte; -- cas où le compte de destination des intérêts est le compte lui-même
				END IF;

				v_id_mouvement_cpte_interne_cli:=payeInteret(ligne.id_cpte,cpte_destination_int,interet,date_batch);
        SELECT INTO v_nouvo_solde solde FROM ad_cpt WHERE id_cpte = ligne.id_cpte; -- valeur sera utiliser dans une requete pour la fonction f_getmouvementforproducerarretecomptebatch

				--récupération du solde
				SELECT INTO nouvo_solde solde FROM ad_cpt WHERE id_cpte = ligne.id_cpte;

			ELSE
			  interet := 0;
			END IF;	---FIN IF interet > 0 ----------------------------------------------------------

			----Initialisation du solde de calcul des intérêts et mise à jour des dates de calcul solde et de capitalisation : ----------

			IF (ligne.mode_calcul_int_cpte >= 2) AND (ligne.mode_calcul_int_cpte <= 7) THEN
			---mode de calcul des intérêts est 'Sur solde...le plus bas'. On débute la période avec le solde courant ----------

				IF cpte_destination_int = ligne.id_cpte THEN --Si le compte est le compte de destination des intérêts, ajouter les intérêts dans le solde

					UPDATE ad_cpt SET solde_calcul_interets = solde + interet, date_solde_calcul_interets = date_batch ,date_calcul_interets = date_batch WHERE id_cpte = ligne.id_cpte;
				ELSE
					UPDATE ad_cpt SET solde_calcul_interets = solde, date_solde_calcul_interets = date_batch ,date_calcul_interets = date_batch WHERE id_cpte = ligne.id_cpte;
				END IF;
			ELSIF (ligne.mode_calcul_int_cpte >= 8) AND (ligne.mode_calcul_int_cpte <= 11) THEN

				--mode de calcul des intérêts est 'Sur solde moyen ...'. On initialise à 0 le solde de calcul des intérêts
					UPDATE ad_cpt SET solde_calcul_interets = 0, date_solde_calcul_interets = date_batch, date_calcul_interets = date_batch WHERE id_cpte = ligne.id_cpte;
					-- mode de calcul des intérêts pour épargne à la source
			ELSIF ((ligne.mode_calcul_int_cpte = 12) AND (isFinAnnee(date_batch) OR date(ligne.dat_date_fin)=date(date_batch))) THEN
			  			UPDATE ad_cpt SET interet_a_capitaliser = 0, interet_annuel = 0, solde_calcul_interets = solde, date_solde_calcul_interets=date(date_batch),date_calcul_interets=date(date_batch) WHERE id_ag=numAgc() AND id_cpte=ligne.id_cpte;
			END IF;
			-- ----------le tableau renvoyé par la fonction
			SELECT date INTO v_date_transaction FROM ad_his WHERE id_his = currval('ad_his_id_his_seq'); -- valeur sera utiliser dans une requete pour la fonction f_getmouvementforproducerarretecomptebatch
			SELECT INTO resultat ligne.id_cpte, ligne.id_titulaire,ligne.solde_calcul_interets, ligne.perio_cap, ligne.classe_comptable, interet,  cpte_destination_int, v_id_mouvement_cpte_interne_cli ,v_nouvo_solde,v_date_transaction;
			RETURN NEXT resultat;

			RAISE NOTICE 'le compte traité est % | l''interet calculé est % ',ligne.id_cpte,interet;

			--FETCH Cpt_Calcul_Int INTO lign;

		END LOOP;

		--CLOSE Cpt_Calcul_Int;
 	RETURN ;

 	END;
$$;
----------------------------------------------------------------

----------- DROP fonction payeinteret(id_cpte_source integer, id_cpte_desti integer, interet numeric, date_batch date) -----------------
CREATE OR REPLACE function payeinteret(id_cpte_source integer, id_cpte_desti integer, interet numeric, date_batch date) returns integer
LANGUAGE plpgsql
AS $$
DECLARE
  prec INTEGER;
	cpte_cpta_assoc_prod_ep  text;		--compte comptable associé au produit d'epargne
	devise_assoc_prod  char(3);		--devise compte comptable associé au produit d'epargne
	id_cpte_cpta_assoc_prod_ep  integer;	-- id compte comptable associé au produit d'epargne

	cpte_cpta_int_assoc_prod_ep  text;	-- compte comptable des intêret associé au produit d'epargne
	id_cpte_cpta_int_assoc_prod_ep  text;	-- id compte comptable des intêret associé au produit d'epargne

  cpte_cpta_int_paye_param TEXT;    		-- compte comptable des interets a payer sur produits d'epargne
  interets_calcules NUMERIC(30,6); 	    -- le montant des interets 'reservé' par les routines des calcul d'interets sur compte d'epargne
  interets_calcules_a_verser NUMERIC(30,6); 	  -- le montant converti des interets 'reservé' par les routines des calcul d'interets sur compte d'epargne
  interets_diff NUMERIC(30,6); 	        -- la difference entre le montant d'interet a remunerer et le montant des interets calculé
  devise_cpte_int_paye  char(3);		    -- devise compte comptable des interets a payer sur produits d'epargne

	num_cpte_credit TEXT;			--compte au crédit
	num_cpte_debit TEXT;			--compte au debit
	exo_courant INTEGER;      		-- l'id de l'exercice courant
	jou1 INTEGER;        			-- l'id du journal associé au compte au débit s'il est principal
	jou2 INTEGER;       			-- l'id du journal associé au crédit s'il est principal
	id_journal  INTEGER;           		-- id du journal des mouvements comptables
	cpte_liaison TEXT;      		-- compte de liaison si les deux comptes à mouvementer sont principaux
	devise_cpte_liaison CHAR(3);    	-- code de la devise du compte de liaison
	code_dev_ref CHAR(3);       		-- code de la devise de référence
	cpt_pos_ch  TEXT;         		-- code de position de change de la devise du compte traité
	cpt_cv_pos_ch TEXT;        		-- code de C/V de la position de change de la devise du compte traité
	interet_a_verser NUMERIC(30,6); 	-- le montant converti de la rémunération(utile aux écritures comptables en cas de devises différentes pour les comptes comptables qui entrent en jeu)
	libel_op INTEGER ;				--libel de l'opération
	type_op INTEGER :=40 ; 			--type operation

	-- Message queue : Obtenir le id_mouvement du compte destinataire
	-- valeur sera utiliser dans une requete pour la fonction f_getmouvementforproducerarretecomptebatch
	v_id_mouvement_cpte_interne_cli INTEGER;

 BEGIN

	----------------------récupération de l'exercice courant : besoin pour écriture comptable---------------------------
	SELECT INTO exo_courant MAX(id_exo_compta) FROM ad_exercices_compta WHERE etat_exo = 1;

	----------------------récupération de la devise de référence : besoin pour écriture comptable généralement en multidevise------------------------
	SELECT INTO code_dev_ref code_devise_reference FROM ad_agc;

	------------------ Recupeation du libelle de l'operation 40--------------------------
	SELECT INTO libel_op libel_ope FROM ad_cpt_ope WHERE id_ag=numAgc() AND type_operation=type_op;

  ----------------------- COMPTE COMPTABLE DES INTERETS A PAYER SUR COMPTE D'EPARGNE --------------------
  SELECT INTO cpte_cpta_int_paye_param cpte_cpta_int_paye FROM adsys_calc_int_paye WHERE id_ag = numagc() LIMIT 1;

  -- Montants des interets calculés par les routines:
  interets_calcules := 0;

  SELECT INTO interets_calcules SUM(montant_int)
  FROM ad_calc_int_paye_his
  WHERE id_cpte = id_cpte_source AND date_calc <=date_batch AND etat_calc_int = 1 AND id_ag = numagc();

  -- RAISE NOTICE 'interets_calcules=%', interets_calcules;

  IF (interets_calcules > 0 AND cpte_cpta_int_paye_param IS NULL) THEN
    RAISE EXCEPTION ' Aucun compte comptable associé aux intérêts à payer sur compte d''épargne, veuillez revoir le paramétrage du calcul des intérêts à payer ';
  END IF;

  -- Devise du compte des interets a payer sur compte d'epargne
  SELECT INTO devise_cpte_int_paye devise FROM ad_cpt_comptable WHERE num_cpte_comptable = cpte_cpta_int_paye_param;

	-----------------------COMPTE COMPTABLE D'INTERET ASSOCIE AU PRODUIT D'EPARGNE (LE COMPTE COMPTABLE A DEBITER) --------------------
	SELECT INTO cpte_cpta_int_assoc_prod_ep,id_cpte_cpta_int_assoc_prod_ep b.cpte_cpta_prod_ep_int,b.id FROM ad_cpt a, adsys_produit_epargne b WHERE a.id_ag = NumAgc() AND a.id_ag = b.id_ag AND a.id_prod = b. id AND  a.id_cpte = id_cpte_source;
	IF cpte_cpta_int_assoc_prod_ep IS NULL THEN
		RAISE EXCEPTION ' Aucun compte comptable des interet associé au produit N°=% , veuillez revoir le paramétrage ',id_cpte_cpta_int_assoc_prod_ep;

	END IF;
	num_cpte_debit:=cpte_cpta_int_assoc_prod_ep;

	-----------------------COMPTE COMPTABLE ASSOCIE AU PRODUIT D'EPARGNE (LE COMPTE COMPTABLE A CREDITER) --------------------

	-- Récuperation du compte comptable associé au produit du compte de destination des intérêts
	SELECT INTO id_cpte_cpta_assoc_prod_ep,cpte_cpta_assoc_prod_ep  b.id, b.cpte_cpta_prod_ep FROM ad_cpt a, adsys_produit_epargne b WHERE a.id_ag = NumAgc() AND a.id_ag = b.id_ag AND a.id_prod = b. id AND a.id_cpte = id_cpte_desti;
	IF cpte_cpta_assoc_prod_ep IS NULL THEN
		RAISE EXCEPTION ' Aucun compte comptable associé au produit d''epargne N°=% , veuillez revoir le paramétrage ',id_cpte_cpta_assoc_prod_ep;
	END IF;

	-- Cas particulier du compte d'épargne nantie
	IF (id_cpte_cpta_assoc_prod_ep = 4) THEN
		SELECT INTO cpte_cpta_assoc_prod_ep cpte_cpta_prod_cr_gar from adsys_produit_credit a, ad_dcr b
			WHERE b.id_ag = NumAgc() AND b.id_ag = a.id_ag AND a.id = b.id_prod
			AND b.id_doss = (SELECT id_doss FROM ad_gar WHERE id_ag = NumAgc() AND gar_num_id_cpte_nantie = id_cpte_desti);

		IF cpte_cpta_assoc_prod_ep IS NULL THEN
			RAISE EXCEPTION ' Aucun compte comptable associé au produit N°=%, veuillez revoir le paramétrage ',id_cpte_cpta_assoc_prod_ep;

		END IF;
	END IF;

	-- récupération de la devise du compte comptable associé au produit d'epargne ----------
	SELECT INTO devise_assoc_prod devise FROM ad_cpt_comptable WHERE num_cpte_comptable = cpte_cpta_assoc_prod_ep;

	-- Construction du numéro de compte à  créditer ----
	IF devise_assoc_prod IS NULL THEN
		num_cpte_credit := cpte_cpta_assoc_prod_ep || '.' || devise_assoc_prod;
	ELSE
		num_cpte_credit := cpte_cpta_assoc_prod_ep;
	END IF;

  -----------------------INFORMATION SUR LES JOURNAUX COMPTABLE ----------------------------

	-- Récupération du journal associé au compte comptable des intérêts
	SELECT INTO jou1 recupeJournal(num_cpte_debit);

	-- Récupération du journal associé si le compte au crédit du produit d'épargne est principal
	SELECT INTO jou2 recupeJournal(num_cpte_credit);

	-------------------------------------------------------------------------------------------
	--		       PASSAGE DES ECRITURES COMPTABLES                                  --
	-------------------------------------------------------------------------------------------

	IF jou1 IS NOT NULL AND jou2 IS NOT NULL AND jou1 <> jou2 THEN -- num_cpte_debit ET COMPTE AU CREDIT SONT PINCIPAUX ET DE JOURNAUX DIFFERENTS

		--on récupère alors le compte de liaison
		SELECT INTO cpte_liaison num_cpte_comptable FROM ad_journaux_liaison
			WHERE (id_ag = NumAgc() AND id_jou1 = jou1 AND id_jou2 = jou2) OR (id_jou1 = jou2 AND id_jou2 = jou1);
		RAISE NOTICE 'compte de liaison entre journal % et journal %  est %', jou1, jou2, cpte_liaison;

		-- Récuperation de la devise du compte de liaison
		SELECT INTO devise_cpte_liaison devise FROM ad_cpt_comptable
			WHERE id_ag = NumAgc() AND num_cpte_comptable = cpte_liaison;
		RAISE NOTICE 'Devise du compte de liaison : % ', devise_cpte_liaison;

		---------- DEBIT COMPTE DES INTERET PAR CREDIT DU COMPTE DE LIAISON -----------------------
		IF devise_cpte_liaison = code_dev_ref THEN  ----- num_cpte_debit et cpte_liaison sont de la même devise

			 -- Ecriture comptable
			INSERT INTO ad_ecriture(id_his,id_ag, date_comptable, libel_ecriture, id_jou, id_exo, ref_ecriture,type_operation,info_ecriture)
				VALUES ((SELECT currval('ad_his_id_his_seq')),NumAgc(), date(date_batch), libel_op, jou1, exo_courant, makeNumEcriture(jou1, exo_courant),type_op,id_cpte_source);

			-- Mouvement comptable au débit
			INSERT INTO ad_mouvement (id_ecriture, id_ag, compte, cpte_interne_cli, sens, montant, devise, date_valeur)
				VALUES ((SELECT currval('ad_ecriture_seq')), NumAgc(), num_cpte_debit, NULL, 'd', interet, code_dev_ref, getDateValeur(NULL,'d',date_batch));

			-- Mouvement comptable au crédit
			INSERT INTO ad_mouvement (id_ecriture, id_ag,compte, cpte_interne_cli, sens, montant, devise, date_valeur)
				VALUES ((SELECT currval('ad_ecriture_seq')), NumAgc(), cpte_liaison,NULL, 'c', interet, devise_cpte_liaison, getDateValeur(NULL,'c',date_batch));

			 -- Mise à  jour des soldes comptables
			UPDATE ad_cpt_comptable set solde = solde - interet WHERE id_ag = NumAgc() AND num_cpte_comptable = num_cpte_debit;
			UPDATE ad_cpt_comptable set solde = solde + interet WHERE id_ag = NumAgc() AND num_cpte_comptable = cpte_liaison;

		ELSE --(devise_debit <> devise_cpte_liaison) donc cpte_liaison est en devise étrangère, on doit faire la conversion

			SELECT INTO cpt_pos_ch cpte_position_change || '.' || devise_cpte_liaison FROM ad_agc WHERE id_ag = NumAgc();
			SELECT INTO cpt_cv_pos_ch cpte_contreval_position_change || '.' || devise_cpte_liaison FROM ad_agc WHERE id_ag = NumAgc();

			INSERT INTO ad_ecriture(id_his,id_ag, date_comptable, libel_ecriture, id_jou, id_exo, ref_ecriture,type_operation,info_ecriture)
				VALUES ((SELECT currval('ad_his_id_his_seq')), NumAgc(), date(date_batch), libel_op, jou1, exo_courant, makeNumEcriture(jou1, exo_courant),type_op,id_cpte_source);

			-- Mouvement comptable au débit
			INSERT INTO ad_mouvement (id_ecriture,id_ag, compte, cpte_interne_cli, sens, montant, devise, date_valeur)
				VALUES ((SELECT currval('ad_ecriture_seq')), NumAgc(), num_cpte_debit, NULL, 'd', interet, code_dev_ref, getDateValeur(NULL,'d',date_batch));

			-- Mouvement comptable au crédit de la c/v du compte de liaison
			INSERT INTO ad_mouvement (id_ecriture,id_ag, compte, cpte_interne_cli, sens, montant, devise, date_valeur)
				VALUES ((SELECT currval('ad_ecriture_seq')), NumAgc(), cpt_cv_pos_ch, NULL, 'c', interet,code_dev_ref, getDateValeur(NULL,'c',date_batch));

			-- montant dans la devise du compte de liaison
			SELECT INTO interet_a_verser CalculeCV(interet, code_dev_ref, devise_cpte_liaison);

			-- Mouvement comptable au débit de la position de change du compte de liaison
			INSERT INTO ad_mouvement (id_ecriture, id_ag, compte, cpte_interne_cli, sens, montant, devise, date_valeur)
				VALUES ((SELECT currval('ad_ecriture_seq')), NumAgc(), cpt_pos_ch, NULL, 'd', interet_a_verser, devise_cpte_liaison, getDateValeur(NULL,'d',date_batch));

			-- Mouvement comptable au crédit du compte de liaison
			INSERT INTO ad_mouvement (id_ecriture,id_ag, compte, cpte_interne_cli, sens, montant, devise, date_valeur)
				VALUES ((SELECT currval('ad_ecriture_seq')), NumAgc(), cpte_liaison , NULL, 'c',interet_a_verser, devise_cpte_liaison, getDateValeur(NULL,'c',date_batch));

			-- Mise Ã  jour des soldes comptables
			UPDATE ad_cpt_comptable set solde = solde - interet WHERE id_ag = NumAgc() AND num_cpte_comptable = num_cpte_debit;
			UPDATE ad_cpt_comptable set solde = solde + interet WHERE id_ag = NumAgc() AND num_cpte_comptable = cpt_cv_pos_ch;
			UPDATE ad_cpt_comptable set solde = solde - interet_a_verser WHERE id_ag = NumAgc() AND num_cpte_comptable = cpt_pos_ch;
			UPDATE ad_cpt_comptable set solde = solde + interet_a_verser WHERE id_ag = NumAgc() AND num_cpte_comptable = cpte_liaison;

		END IF;--  FIN IF devise_cpte_liaison = code_dev_ref

		----------- FIN DEBIT COMPTE CLIENT PAR CREDIT COMPTE DE LIAISON -----------------------

		----------- DEBIT COMPTE DE LIAISON PAR CREDIT COMPTE COMPTABLE ASSOCIE AU PRODUIT D'EPARGNE DANS LE SECOND JOURNAL ------------------------

		IF devise_cpte_liaison = devise_assoc_prod THEN  -----COMPTE AU CREDIT ET cpte_liaison SONT DE LA MEME DEVISE

			-- passage d'écriture
			INSERT INTO ad_ecriture(id_his,id_ag, date_comptable, libel_ecriture, id_jou, id_exo, ref_ecriture,type_operation,info_ecriture)
				VALUES ((SELECT currval('ad_his_id_his_seq')), NumAgc(), date(date_batch), libel_op, jou2, exo_courant, makeNumEcriture(jou2, exo_courant),type_op,id_cpte_source);

			-- mouvement comptable au débit du compte de liaison
			INSERT INTO ad_mouvement (id_ecriture, id_ag, compte, cpte_interne_cli, sens, montant, devise, date_valeur)
				VALUES ((SELECT currval('ad_ecriture_seq')), NumAgc(), cpte_liaison, NULL,'d', interet, devise_cpte_liaison, getDateValeur(NULL,'d',date_batch));

			-- mouvement comptable au crédit du compte associé au produit
			INSERT INTO ad_mouvement (id_ecriture, id_ag, compte, cpte_interne_cli, sens, montant, devise, date_valeur)
				VALUES ((SELECT currval('ad_ecriture_seq')),NumAgc(),num_cpte_credit,id_cpte_desti,'c',interet, devise_assoc_prod, getDateValeur(id_cpte_desti,'c',date_batch));
			-- message_queue
			SELECT id_mouvement INTO v_id_mouvement_cpte_interne_cli FROM ad_mouvement WHERE id_mouvement = currval('ad_mouvement_seq');

			--Mise à jour des soldes
			UPDATE ad_cpt_comptable set solde = solde - interet WHERE id_ag = NumAgc() AND num_cpte_comptable = cpte_liaison;
			UPDATE ad_cpt_comptable set solde = solde + interet WHERE id_ag = NumAgc() AND num_cpte_comptable = num_cpte_credit;


		ELSE ---- compte au crédit et  cpte_liaison n'ont pas la même devise, une conversion s'impose
			IF devise_cpte_liaison = code_dev_ref THEN  -- cpte de liaison est en dévise de référence, uniquement le compte au crédit en devise étrangère

				SELECT INTO cpt_pos_ch cpte_position_change || '.' || devise_assoc_prod FROM ad_agc WHERE id_ag = NumAgc();
				SELECT INTO cpt_cv_pos_ch cpte_contreval_position_change || '.' || devise_assoc_prod FROM ad_agc WHERE id_ag = NumAgc();

				-- récupération du montant des intérêts dans la devise du compte de liaison (donc devise de référence )
				SELECT INTO interet_a_verser CalculeCV(interet, devise_assoc_prod, devise_cpte_liaison);

				-- passage d'écriture
				INSERT INTO ad_ecriture(id_his, id_ag, date_comptable, libel_ecriture, id_jou, id_exo, ref_ecriture,type_operation,info_ecriture)
					VALUES ((SELECT currval('ad_his_id_his_seq')), NumAgc(), date(date_batch), libel_op, jou2, exo_courant, makeNumEcriture(jou2, exo_courant),type_op,id_cpte_source);

				-- mouvement comptable au débit du compte de liaison
				INSERT INTO ad_mouvement (id_ecriture, id_ag, compte, cpte_interne_cli, sens, montant, devise, date_valeur)
					VALUES ((SELECT currval('ad_ecriture_seq')), NumAgc(), cpte_liaison, NULL, 'd', interet_a_verser,   devise_cpte_liaison, getDateValeur(NULL,'d',date_batch));

				--Mise à jour des soldes
				UPDATE ad_cpt_comptable set solde = solde - interet_a_verser WHERE id_ag=NumAgc() AND num_cpte_comptable = cpte_liaison;

				-- mouvement comptable au crédit de la  c/v
				INSERT INTO ad_mouvement (id_ecriture, id_ag, compte, cpte_interne_cli, sens, montant, devise, date_valeur)
					VALUES ((SELECT currval('ad_ecriture_seq')), NumAgc(), cpt_cv_pos_ch, NULL, 'c', interet_a_verser,  code_dev_ref, getDateValeur(NULL,'c',date_batch));

				UPDATE ad_cpt_comptable set solde = solde + interet_a_verser WHERE id_ag=NumAgc() AND num_cpte_comptable = cpt_cv_pos_ch;

				-- mouvement comptable au débit de la position de change du compte au crédit(compte associé au produit)
				INSERT INTO ad_mouvement (id_ecriture, id_ag, compte, cpte_interne_cli, sens, montant, devise, date_valeur)
					VALUES ((SELECT currval('ad_ecriture_seq')), NumAgc(), cpt_pos_ch, NULL, 'd', interet, devise_assoc_prod, getDateValeur(NULL,'d',date_batch));
				UPDATE ad_cpt_comptable set solde = solde - interet WHERE id_ag=NumAgc() AND num_cpte_comptable = cpt_pos_ch;

				-- mouvement comptable au crédit du compte associé au produit
				INSERT INTO ad_mouvement (id_ecriture,id_ag, compte, cpte_interne_cli, sens, montant, devise, date_valeur)
					VALUES ((SELECT currval('ad_ecriture_seq')), NumAgc(), num_cpte_credit , id_cpte_desti, 'c',interet,    devise_assoc_prod, getDateValeur(id_cpte_desti,'c',date_batch));
				-- message_queue
				SELECT id_mouvement INTO v_id_mouvement_cpte_interne_cli FROM ad_mouvement WHERE id_mouvement = currval('ad_mouvement_seq');

				UPDATE ad_cpt_comptable set solde = solde + interet WHERE id_ag=NumAgc() AND num_cpte_comptable = num_cpte_credit;

			END IF; -- FIN IF devise_cpte_liaison dans la devise de réference et seulement compte associé au produit en devise étrangère

			IF devise_assoc_prod = code_dev_ref THEN -- si compte associé au produit(compte au crédit) a la devise de référence et que le compte de liaison est en dévise étrangère

				SELECT INTO cpt_pos_ch cpte_position_change || '.' || devise_cpte_liaison FROM ad_agc WHERE id_ag = NumAgc();
				SELECT INTO cpt_cv_pos_ch cpte_contreval_position_change || '.' || devise_cpte_liaison FROM ad_agc WHERE id_ag = NumAgc();

				--  passage d'écriture
				INSERT INTO ad_ecriture(id_his, id_ag, date_comptable, libel_ecriture, id_jou, id_exo, ref_ecriture,type_operation,info_ecriture)
					VALUES ((SELECT currval('ad_his_id_his_seq')), NumAgc(), date(date_batch), libel_op, jou2, exo_courant, makeNumEcriture(jou2, exo_courant),type_op,id_cpte_source);

				-- mouvement au crédit du compte associé au produit
				INSERT INTO ad_mouvement (id_ecriture, id_ag, compte, cpte_interne_cli, sens, montant, devise, date_valeur)
					VALUES ((SELECT currval('ad_ecriture_seq')), NumAgc(), num_cpte_credit , id_cpte_desti, 'c', interet,    devise_assoc_prod, getDateValeur(id_cpte_desti,'c',date_batch));
				-- message_queue
				SELECT id_mouvement INTO v_id_mouvement_cpte_interne_cli FROM ad_mouvement WHERE id_mouvement = currval('ad_mouvement_seq');

				UPDATE ad_cpt_comptable set solde = solde + interet WHERE id_ag = NumAgc() AND num_cpte_comptable = num_cpte_credit;

				-- mouvement comptable au débit de la c/v du compte de liaison
				INSERT INTO ad_mouvement (id_ecriture,id_ag, compte, cpte_interne_cli, sens, montant, devise, date_valeur)
					VALUES ((SELECT currval('ad_ecriture_seq')), NumAgc(), cpt_cv_pos_ch , NULL, 'd',interet,  code_dev_ref, getDateValeur(NULL,'d',date_batch));

				UPDATE ad_cpt_comptable set solde = solde - interet WHERE id_ag=NumAgc() AND num_cpte_comptable = cpt_cv_pos_ch;-- car interet est déjà en devise de réference puisque c'est elle la devise compte associé au produit

				-- Récupération du montant dans la devise du compte de liaison
				SELECT INTO interet_a_verser CalculeCV(interet, devise_assoc_prod, devise_cpte_liaison);

				-- mouvement comptable au débit du compte de liaison
				INSERT INTO ad_mouvement (id_ecriture, id_ag, compte, cpte_interne_cli, sens, montant, devise, date_valeur)
					VALUES ((SELECT currval('ad_ecriture_seq')), NumAgc(), cpte_liaison, NULL, 'd', interet_a_verser,   devise_cpte_liaison, getDateValeur(NULL,'d',date_batch));
				UPDATE ad_cpt_comptable set solde = solde - interet_a_verser WHERE id_ag=NumAgc() AND num_cpte_comptable = cpte_liaison;

				-- mouvement comptable au crédit de la position de change de la devise du compte de liaison
				INSERT INTO ad_mouvement (id_ecriture, id_ag, compte, cpte_interne_cli, sens, montant, devise, date_valeur)
					VALUES ((SELECT currval('ad_ecriture_seq')), NumAgc(),cpt_pos_ch, NULL, 'c', interet_a_verser,   devise_cpte_liaison,getDateValeur(NULL,'c',date_batch));

				UPDATE ad_cpt_comptable set solde = solde + interet_a_verser WHERE id_ag = NumAgc() AND num_cpte_comptable = cpt_pos_ch;

			END IF; -- FIN IF devise_assoc_prod dans la devise de référence et seulement compte de liaison dans la devise étrangère


			IF devise_assoc_prod <> devise_cpte_liaison AND devise_assoc_prod <> code_dev_ref AND devise_cpte_liaison <> code_dev_ref THEN

				-- devise du compte de liaison et devise du compte associé au produit sont différents et aucune n'est égale à la devise de référence

				-- passage d'écriture
				INSERT INTO ad_ecriture(id_his, id_ag, date_comptable, libel_ecriture, id_jou, id_exo, ref_ecriture,type_operation,info_ecriture)
					VALUES ((SELECT currval('ad_his_id_his_seq')), NumAgc(), date(date_batch), libel_op, jou2,  exo_courant, makeNumEcriture(jou2, exo_courant),type_op,id_cpte_source);

				-- récupération du montant des intérêts dans la devise du compte de liaison (la valeur interet_a_verser represente ici celle de interet dans la devise du compte de liaison)
				SELECT INTO interet_a_verser CalculeCV(interet, devise_assoc_prod, devise_cpte_liaison);

				-- mouvement comptable au débit du compte de liaison
				INSERT INTO ad_mouvement (id_ecriture, id_ag, compte, cpte_interne_cli, sens, montant, devise, date_valeur)
					VALUES ((SELECT currval('ad_ecriture_seq')),NumAgc(), cpte_liaison, NULL, 'd', interet_a_verser,    devise_cpte_liaison, getDateValeur(NULL,'d',date_batch));

				UPDATE ad_cpt_comptable set solde = solde - interet_a_verser WHERE id_ag = NumAgc() AND num_cpte_comptable = cpte_liaison;

				-- position de change de la devise du compte de liaison
				SELECT INTO cpt_pos_ch cpte_position_change || '.' || devise_cpte_liaison FROM ad_agc WHERE id_ag=NumAgc();

				-- mouvement comptable au crédit de la position de change de la devise du compte de liaison
				INSERT INTO ad_mouvement (id_ecriture, id_ag, compte, cpte_interne_cli, sens, montant, devise, date_valeur)
					VALUES ((SELECT currval('ad_ecriture_seq')), NumAgc(), cpt_pos_ch, NULL, 'c', interet_a_verser,     devise_cpte_liaison, getDateValeur(NULL,'c',date_batch));

				UPDATE ad_cpt_comptable set solde = solde + interet_a_verser WHERE id_ag=NumAgc() AND num_cpte_comptable = cpt_pos_ch;

				-- Récupération du montant des intérêts dans la devise de référence(la valeur interet_a_verser represente ici celle de interet dans la devise de référence)
				SELECT INTO interet_a_verser CalculeCV(interet, devise_assoc_prod, code_dev_ref);

				-- c/v de la devise du compte de liaison
				SELECT INTO cpt_cv_pos_ch cpte_contreval_position_change || '.' || devise_cpte_liaison FROM ad_agc;

				-- mouvement comptable au débit de la c/v de la devise du compte de liaison
				INSERT INTO ad_mouvement (id_ecriture, id_ag, compte, cpte_interne_cli, sens, montant, devise, date_valeur)
					VALUES ((SELECT currval('ad_ecriture_seq')), NumAgc(), cpt_cv_pos_ch,NULL, 'd', interet_a_verser, code_dev_ref, getDateValeur(NULL,'d',date_batch));

				UPDATE ad_cpt_comptable set solde = solde - interet_a_verser WHERE id_ag=NumAgc() AND num_cpte_comptable = cpt_cv_pos_ch;

				-- c/v de la devise du compte associé au produit
				SELECT INTO cpt_cv_pos_ch cpte_contreval_position_change || '.' || devise_assoc_prod FROM ad_agc;

				-- mouvement comptable au crédit de la c/v du compte associé au produit
				INSERT INTO ad_mouvement (id_ecriture, id_ag, compte, cpte_interne_cli, sens, montant, devise, date_valeur)
					VALUES ((SELECT currval('ad_ecriture_seq')), NumAgc(), cpt_cv_pos_ch, NULL, 'c', interet_a_verser,  code_dev_ref, getDateValeur(NULL,'c',date_batch));

				UPDATE ad_cpt_comptable set solde = solde + interet_a_verser
					WHERE id_ag = NumAgc() AND num_cpte_comptable = cpt_cv_pos_ch;

				-- mouvement comptable au crédit du compte associé au produit
				INSERT INTO ad_mouvement (id_ecriture, id_ag, compte, cpte_interne_cli, sens, montant, devise, date_valeur)
					VALUES ((SELECT currval('ad_ecriture_seq')),NumAgc(), num_cpte_credit, id_cpte_desti, 'c', interet,    devise_assoc_prod, getDateValeur(id_cpte_desti,'c',date_batch));
				-- message_queue
				SELECT id_mouvement INTO v_id_mouvement_cpte_interne_cli FROM ad_mouvement WHERE id_mouvement = currval('ad_mouvement_seq');

				UPDATE ad_cpt_comptable set solde = solde + interet WHERE id_ag=NumAgc() AND num_cpte_comptable = num_cpte_credit;

				-- position de change de la devise du compte associé au produit
				SELECT INTO cpt_pos_ch cpte_position_change || '.' || devise_assoc_prod FROM ad_agc WHERE id_ag=NumAgc();

				-- mouvement comptable au débit de la position de change de la devise du compte associé au produit
				INSERT INTO ad_mouvement (id_ecriture, id_ag, compte, cpte_interne_cli, sens, montant, devise, date_valeur)
				 VALUES ((SELECT currval('ad_ecriture_seq')), NumAgc(), cpt_pos_ch, NULL, 'd', interet, devise_assoc_prod, getDateValeur(NULL,'d',date_batch));

				UPDATE ad_cpt_comptable set solde = solde - interet WHERE id_ag = NumAgc() AND num_cpte_comptable = cpt_pos_ch;

			END IF; -- FIN IF devise_assoc_prod != code_dev_ref AND devise_cpte_liaison != code_dev_ref

		END IF;  -- FIN  IF devise_assoc_prod = devise_cpte_liaison

		------- FIN DEBIT COMPTE DE LIAISON PAR CREDIT COMPTE COMPTABLE ASSOCIE AU PRODUIT D'EPARGNE DANS LE SECOND JOURNAL ------------------------
	ELSE
	  -- AU MOINS UN DES COMPTES N''EST PAS PRINCIPAL OU LES DEUX SONT PRINCIPAUX DU MEME JOURNAL: PAS BESOIN DONC DE COMPTE DE LIAISON
		IF jou1 IS NULL AND jou2 IS NOT NULL THEN

			id_journal := jou2;
		END IF;

		IF jou1 IS NOT NULL AND jou2 IS NULL THEN
		      id_journal := jou1;
		END IF;

		IF jou1 IS NOT NULL AND jou2 IS NOT NULL AND jou1 = jou2 THEN
			id_journal := jou1;
		END IF;

		IF jou1 IS NULL AND jou2 IS NULL THEN
			id_journal := 1; -- Ecrire donc dans le joournal principal
		END IF;

    -- Initialiser la difference avec le montant des interets a remunerer.
    interets_diff := interet;

    -- RAISE NOTICE 'code_dev_ref=%  devise_assoc_prod=%  devise_cpte_int_paye=%', code_dev_ref, devise_assoc_prod, devise_cpte_int_paye;

    IF (interets_calcules > 0 AND cpte_cpta_int_paye_param IS NOT NULL) THEN
         -- Tous dans la devise de reference
        IF ( (code_dev_ref = devise_assoc_prod) AND (devise_assoc_prod = devise_cpte_int_paye)) THEN

            -- Pas d'arrondies pour palier a des pertes de precisions lors des calcul d'interet a payer
            -- arrondi par rapport a la precision devise
            -- SELECT INTO prec precision FROM devise WHERE  code_devise = devise_cpte_int_paye;
            -- interets_calcules := ROUND(interets_calcules, prec);

            -- Ecriture comptable
            INSERT INTO ad_ecriture (id_his, id_ag, date_comptable, libel_ecriture, id_jou, id_exo, ref_ecriture,type_operation,info_ecriture)
              VALUES ((SELECT currval('ad_his_id_his_seq')), NumAgc(), date_batch,libel_op, id_journal, exo_courant, makeNumEcriture(1, exo_courant),type_op,id_cpte_source);

            -- S'il y a des interets qui sont 'reservé' pour ce compte :
            IF(interets_calcules > 0 AND cpte_cpta_int_paye_param IS NOT NULL) THEN
                -- Mouvement comptable au débit(compte des intérêt)
                INSERT INTO ad_mouvement(id_ecriture, id_ag, compte, cpte_interne_cli, sens, montant, date_valeur, devise)
                VALUES ((SELECT currval('ad_ecriture_seq')), NumAgc(), cpte_cpta_int_paye_param, NULL, 'd', interets_calcules, getDateValeur(id_cpte_desti,'c',date_batch), devise_assoc_prod);

                -- Mouvement comptable au crédit(comptes associé aux produits d'epargne de destination des intérêt)
                INSERT INTO ad_mouvement(id_ecriture, id_ag, compte, cpte_interne_cli, sens, montant, date_valeur, devise)
                VALUES ((SELECT currval('ad_ecriture_seq')), NumAgc(),  num_cpte_credit, id_cpte_desti, 'c', interets_calcules, getDateValeur(id_cpte_desti,'c',date_batch), devise_assoc_prod);
								-- message queue
								SELECT id_mouvement INTO v_id_mouvement_cpte_interne_cli FROM ad_mouvement WHERE id_mouvement = currval('ad_mouvement_seq');

                -- Mise a jour etat calcul pour les lignes reprises dans ad_calc_int_paye_his
                UPDATE ad_calc_int_paye_his SET date_reprise = getDateValeur(id_cpte_desti,'c',date_batch)
                WHERE id_cpte=id_cpte_source AND etat_calc_int=1 AND id_ag=numagc();

                UPDATE ad_calc_int_paye_his SET id_his_reprise = (SELECT currval('ad_his_id_his_seq'))
                WHERE id_cpte=id_cpte_source AND etat_calc_int=1 AND id_ag=numagc();

                UPDATE ad_calc_int_paye_his SET id_ecriture_reprise = (SELECT currval('ad_ecriture_seq'))
                WHERE id_cpte=id_cpte_source AND etat_calc_int=1 AND id_ag=numagc();

                UPDATE ad_calc_int_paye_his SET etat_calc_int=2 WHERE id_cpte=id_cpte_source AND etat_calc_int=1 AND id_ag=numagc();

                -- mise à jour des soldes  comptable
                UPDATE ad_cpt_comptable SET solde = solde - interets_calcules WHERE num_cpte_comptable = cpte_cpta_int_paye_param;
                UPDATE ad_cpt_comptable SET solde = solde + interets_calcules WHERE num_cpte_comptable = num_cpte_credit;

            END IF;
            -- Fin montant 'reservé' par calcul des interets a payer sur comptes d'epargne.

            -- La difference entre montant a remunerer et le montant 'reservé'
            interets_diff := interet - interets_calcules;

            IF (interets_diff > 0) THEN
              -- Mouvement comptable au débit(compte des intérêt)
              INSERT INTO ad_mouvement(id_ecriture, id_ag, compte, cpte_interne_cli, sens, montant, date_valeur, devise)
              VALUES ((SELECT currval('ad_ecriture_seq')), NumAgc(),num_cpte_debit , NULL, 'd', interets_diff, getDateValeur(id_cpte_desti,'c',date_batch), devise_assoc_prod);

              -- Mouvement comptable au crédit(comptes associé aux produits d'epargne de destination des intérêt)
              INSERT INTO ad_mouvement(id_ecriture, id_ag, compte, cpte_interne_cli, sens, montant, date_valeur, devise)
              VALUES ((SELECT currval('ad_ecriture_seq')), NumAgc(),  num_cpte_credit, id_cpte_desti, 'c', interets_diff, getDateValeur(id_cpte_desti,'c',date_batch), devise_assoc_prod);
							-- message queue
							SELECT id_mouvement INTO v_id_mouvement_cpte_interne_cli FROM ad_mouvement WHERE id_mouvement = currval('ad_mouvement_seq');

              -- mise à jour des soldes  comptable
              UPDATE ad_cpt_comptable SET solde = solde - interets_diff WHERE num_cpte_comptable = num_cpte_debit;
              UPDATE ad_cpt_comptable SET solde = solde + interets_diff WHERE num_cpte_comptable = num_cpte_credit;

            END IF;

        -- cas de devise de référence différent de la devise du produit épargne ------------------------------
        ELSE IF (devise_assoc_prod = devise_cpte_int_paye AND code_dev_ref <> devise_assoc_prod) THEN

           -- Si des interets sont reservé/calculé
          IF (interets_calcules > 0 AND cpte_cpta_int_paye_param IS NULL) THEN
              -- Verser l'integralite des interets calcule dans le compte produit, qui va etre ensuite utilisé pour les change
              -- Ecriture comptable
              INSERT INTO ad_ecriture (id_his, id_ag, date_comptable, libel_ecriture, id_jou, id_exo, ref_ecriture,type_operation,info_ecriture)
                VALUES ((SELECT currval('ad_his_id_his_seq')), NumAgc(), date_batch, libel_op, id_journal, exo_courant, makeNumEcriture(1, exo_courant),type_op,id_cpte_source);

              -- Mouvement comptable au débit(compte des intérêt)
              INSERT INTO ad_mouvement(id_ecriture, id_ag, compte, cpte_interne_cli, sens, montant, date_valeur, devise)
                VALUES ((SELECT currval('ad_ecriture_seq')), NumAgc(), cpte_cpta_int_paye_param , NULL, 'd', interets_calcules, getDateValeur(NULL,'d',date_batch),devise_assoc_prod);

              -- Mouvement comptable au crédit(comptes de position de change)
              INSERT INTO ad_mouvement(id_ecriture, id_ag, compte, cpte_interne_cli, sens, montant, date_valeur, devise)
                VALUES ((SELECT currval('ad_ecriture_seq')), NumAgc(), cpte_cpta_int_assoc_prod_ep , NULL, 'c', interets_calcules, getDateValeur(NULL,'c',date_batch), devise_assoc_prod);

              -- Mise a jour etat calcul pour les lignes reprises dans ad_calc_int_paye_his
              UPDATE ad_calc_int_paye_his SET date_reprise = getDateValeur(id_cpte_desti,'c',date_batch)
              WHERE id_cpte=id_cpte_source AND etat_calc_int=1 AND id_ag=numagc();

              UPDATE ad_calc_int_paye_his SET id_his_reprise = (SELECT currval('ad_his_id_his_seq'))
              WHERE id_cpte=id_cpte_source AND etat_calc_int=1 AND id_ag=numagc();

              UPDATE ad_calc_int_paye_his SET id_ecriture_reprise = (SELECT currval('ad_ecriture_seq'))
              WHERE id_cpte=id_cpte_source AND etat_calc_int=1 AND id_ag=numagc();

              UPDATE ad_calc_int_paye_his SET etat_calc_int=2 WHERE id_cpte=id_cpte_source AND etat_calc_int=1 AND id_ag=numagc();

              -- mise à jour des soldes  comptable
              UPDATE ad_cpt_comptable SET solde = solde - interets_calcules WHERE num_cpte_comptable = cpte_cpta_int_paye_param;
              UPDATE ad_cpt_comptable SET solde = solde + interets_calcules WHERE num_cpte_comptable = cpte_cpta_int_assoc_prod_ep;

          END IF;

          -- il faut faire une change du montant des intérêts dans la devise de référence --
          SELECT INTO interet_a_verser  CalculeCV(interet, devise_assoc_prod, code_dev_ref);

          --Récupérer son compte de position de change
          SELECT INTO cpt_pos_ch cpte_position_change || '.' || devise_assoc_prod FROM ad_agc WHERE id_ag=NumAgc();

          --Récupérer son compte de c/v
          SELECT INTO cpt_cv_pos_ch cpte_contreval_position_change || '.' || devise_assoc_prod FROM ad_agc WHERE id_ag=NumAgc();
            RAISE NOTICE 'cpt_pos_ch = % et cpt_cv_pos_ch = % et cpte_interne_cli=%',cpt_pos_ch, cpt_cv_pos_ch,id_cpte_desti;

          -- Ecriture comptable
          INSERT INTO ad_ecriture (id_his, id_ag, date_comptable, libel_ecriture, id_jou, id_exo, ref_ecriture,type_operation,info_ecriture)
            VALUES ((SELECT currval('ad_his_id_his_seq')), NumAgc(), date_batch, libel_op, id_journal, exo_courant, makeNumEcriture(1, exo_courant),type_op,id_cpte_source);

          -- Mouvement comptable au débit(compte des intérêt)
          INSERT INTO ad_mouvement(id_ecriture, id_ag, compte, cpte_interne_cli, sens, montant, date_valeur, devise)
            VALUES ((SELECT currval('ad_ecriture_seq')), NumAgc(), num_cpte_debit , NULL, 'd', interet_a_verser, getDateValeur(NULL,'d',date_batch),code_dev_ref);

          -- Mouvement comptable au crédit(comptes de position de change)
          INSERT INTO ad_mouvement(id_ecriture, id_ag, compte, cpte_interne_cli, sens, montant, date_valeur, devise)
            VALUES ((SELECT currval('ad_ecriture_seq')), NumAgc(), cpt_cv_pos_ch , NULL, 'c', interet_a_verser, getDateValeur(NULL,'c',date_batch), code_dev_ref);

          -- Mise à jour des soldes des comptes comptables
          UPDATE ad_cpt_comptable set solde = solde - interet_a_verser WHERE id_ag=NumAgc() AND num_cpte_comptable = num_cpte_debit;
          UPDATE ad_cpt_comptable set solde = solde + interet_a_verser WHERE id_ag=NumAgc() AND num_cpte_comptable = cpt_cv_pos_ch;

          -- Ecriture comptable contre valeur
          INSERT INTO ad_ecriture  (id_his, id_ag, date_comptable, libel_ecriture, id_jou, id_exo, ref_ecriture,type_operation,info_ecriture)
            VALUES ((SELECT currval('ad_his_id_his_seq')), NumAgc(), date(date_batch), libel_op, id_journal, exo_courant, makeNumEcriture(1, exo_courant),type_op,id_cpte_source);

          -- Mouvement comptable au débit(compte position de change)
          INSERT INTO ad_mouvement(id_ecriture, id_ag, compte, cpte_interne_cli, sens, montant, date_valeur, devise)
            VALUES ((SELECT currval('ad_ecriture_seq')), NumAgc(),cpt_pos_ch, NULL, 'd', interet, getDateValeur(NULL,'d',date_batch), devise_assoc_prod);

          -- Mouvement comptable au crédit(comptes associé aux produits d'epargne de destination des intérêt)
          INSERT INTO ad_mouvement(id_ecriture, id_ag, compte, cpte_interne_cli, sens, montant, date_valeur, devise)
            VALUES ((SELECT currval('ad_ecriture_seq')), NumAgc(),  num_cpte_credit, id_cpte_desti, 'c', interet, getDateValeur(id_cpte_desti,'c',date_batch), devise_assoc_prod);
					-- message_queue
					SELECT id_mouvement INTO v_id_mouvement_cpte_interne_cli FROM ad_mouvement WHERE id_mouvement = currval('ad_mouvement_seq');

          -- mise à jour des soldes  comptable
          UPDATE ad_cpt_comptable SET solde = solde - interet WHERE num_cpte_comptable = cpt_pos_ch;
          UPDATE ad_cpt_comptable SET solde = solde + interet WHERE num_cpte_comptable = num_cpte_credit;

          END IF;-- if code_dev_ref = devise_assoc_prod
        END IF; -- end triple equals

    ELSE ----------- Si pas d'interet calculee

        IF code_dev_ref = devise_assoc_prod  THEN
          -- Ecriture comptable
          INSERT INTO ad_ecriture (id_his, id_ag, date_comptable, libel_ecriture, id_jou, id_exo, ref_ecriture,type_operation,info_ecriture)
            VALUES ((SELECT currval('ad_his_id_his_seq')), NumAgc(), date_batch,libel_op, id_journal, exo_courant, makeNumEcriture(1, exo_courant),type_op,id_cpte_source);

          -- Mouvement comptable au débit(compte des intérêt)
          INSERT INTO ad_mouvement(id_ecriture, id_ag, compte, cpte_interne_cli, sens, montant, date_valeur, devise)
            VALUES ((SELECT currval('ad_ecriture_seq')), NumAgc(),num_cpte_debit , NULL, 'd', interet,getDateValeur(id_cpte_desti,'c',date_batch), devise_assoc_prod);

          -- Mouvement comptable au crédit(comptes associé aux produits d'epargne de destination des intérêt)
          INSERT INTO ad_mouvement(id_ecriture, id_ag, compte, cpte_interne_cli, sens, montant, date_valeur, devise)
            VALUES ((SELECT currval('ad_ecriture_seq')), NumAgc(),  num_cpte_credit, id_cpte_desti, 'c', interet,getDateValeur(id_cpte_desti,'c',date_batch), devise_assoc_prod);
					-- message_queue
					SELECT id_mouvement INTO v_id_mouvement_cpte_interne_cli FROM ad_mouvement WHERE id_mouvement = currval('ad_mouvement_seq');

          -- mise à jour des soldes  comptable
          UPDATE ad_cpt_comptable SET solde = solde - interet WHERE num_cpte_comptable = num_cpte_debit;
          UPDATE ad_cpt_comptable SET solde = solde + interet WHERE num_cpte_comptable = num_cpte_credit;

        ELSE -- cas de devise de référence différent de la devise du produit épargne ------------------------------

          -- il faut faire une change du montant des intérêts dans la devise de référence --
          SELECT INTO interet_a_verser  CalculeCV(interet, devise_assoc_prod, code_dev_ref);

          --Récupérer son compte de position de change
          SELECT INTO cpt_pos_ch cpte_position_change || '.' || devise_assoc_prod FROM ad_agc WHERE id_ag=NumAgc();

          --Récupérer son compte de c/v
          SELECT INTO cpt_cv_pos_ch cpte_contreval_position_change || '.' || devise_assoc_prod FROM ad_agc WHERE id_ag=NumAgc();
            RAISE NOTICE 'cpt_pos_ch = % et cpt_cv_pos_ch = % et cpte_interne_cli=%',cpt_pos_ch, cpt_cv_pos_ch,id_cpte_desti;

          -- Ecriture comptable
          INSERT INTO ad_ecriture (id_his, id_ag, date_comptable, libel_ecriture, id_jou, id_exo, ref_ecriture,type_operation,info_ecriture)
            VALUES ((SELECT currval('ad_his_id_his_seq')), NumAgc(), date_batch, libel_op, id_journal, exo_courant, makeNumEcriture(1, exo_courant),type_op,id_cpte_source);

          -- Mouvement comptable au débit(compte des intérêt)
          INSERT INTO ad_mouvement(id_ecriture, id_ag, compte, cpte_interne_cli, sens, montant, date_valeur, devise)
            VALUES ((SELECT currval('ad_ecriture_seq')), NumAgc(),num_cpte_debit , NULL, 'd', interet_a_verser, getDateValeur(NULL,'d',date_batch),code_dev_ref);

          -- Mouvement comptable au crédit(comptes de position de change)
          INSERT INTO ad_mouvement(id_ecriture, id_ag, compte, cpte_interne_cli, sens, montant, date_valeur, devise)
            VALUES ((SELECT currval('ad_ecriture_seq')), NumAgc(), cpt_cv_pos_ch , NULL, 'c', interet_a_verser, getDateValeur(NULL,'c',date_batch), code_dev_ref);

          -- Mise à jour des soldes des comptes comptables
          UPDATE ad_cpt_comptable set solde = solde - interet_a_verser
            WHERE id_ag=NumAgc() AND num_cpte_comptable = num_cpte_debit;
          UPDATE ad_cpt_comptable set solde = solde + interet_a_verser
            WHERE id_ag=NumAgc() AND num_cpte_comptable = cpt_cv_pos_ch;

          -- Ecriture comptable contre valeur
          INSERT INTO ad_ecriture  (id_his, id_ag, date_comptable, libel_ecriture, id_jou, id_exo, ref_ecriture,type_operation,info_ecriture)
            VALUES ((SELECT currval('ad_his_id_his_seq')), NumAgc(), date(date_batch), libel_op, id_journal, exo_courant, makeNumEcriture(1, exo_courant),type_op,id_cpte_source);

          -- Mouvement comptable au débit(compte position de change)
          INSERT INTO ad_mouvement(id_ecriture, id_ag, compte, cpte_interne_cli, sens, montant, date_valeur, devise)
            VALUES ((SELECT currval('ad_ecriture_seq')), NumAgc(),cpt_pos_ch, NULL, 'd', interet, getDateValeur(NULL,'d',date_batch), devise_assoc_prod);

          -- Mouvement comptable au crédit(comptes associé aux produits d'epargne de destination des intérêt)
          INSERT INTO ad_mouvement(id_ecriture, id_ag, compte, cpte_interne_cli, sens, montant, date_valeur, devise)
            VALUES ((SELECT currval('ad_ecriture_seq')), NumAgc(),  num_cpte_credit, id_cpte_desti, 'c', interet, getDateValeur(id_cpte_desti,'c',date_batch), devise_assoc_prod);
					-- message_queue
					SELECT id_mouvement INTO v_id_mouvement_cpte_interne_cli FROM ad_mouvement WHERE id_mouvement = currval('ad_mouvement_seq');

          -- mise à jour des soldes  comptable
          UPDATE ad_cpt_comptable SET solde = solde - interet WHERE num_cpte_comptable = cpt_pos_ch;
          UPDATE ad_cpt_comptable SET solde = solde + interet WHERE num_cpte_comptable = num_cpte_credit;

        END IF;-- if code_dev_ref = devise_assoc_prod

    END IF;

	END IF;-- if du test de journaux principaux ou pas

	-------------------------------------Mis à jour  de l'intérêt annuel -----------------------------
	UPDATE ad_cpt SET  interet_annuel = interet_annuel + interet WHERE id_ag=NumAgc() AND id_cpte = id_cpte_source;
	---------------------------------------------------------------------------------------------------------------------------------------------
  -------------------------------------Mis à jour du solde du compte epargne du client du compte de destination -----------------------------
	UPDATE ad_cpt SET  solde = solde + interet WHERE id_ag=NumAgc() AND id_cpte = id_cpte_desti;
	---------------------------------------------------------------------------------------------------------------------------------------------

 RETURN v_id_mouvement_cpte_interne_cli; -- id_mouvement va etre utiliser dans un query recuperant les donnees necessaire pour le message queue
 END;
$$;
----------------------------------------------------------------------------------------------------------------------------------------

----------- DROP fonction f_getmouvementforproducer(text, numeric, text, integer) -----------------
CREATE OR REPLACE function f_getmouvementforproducer(text, numeric, text, integer) returns TABLE(id_client integer, id_ag integer, id_cpte integer, id_transaction integer, id_mouvement integer, date_transaction timestamp without time zone, ref_ecriture text, type_opt integer, libelle_ecriture text, montant numeric, sens text, devise character, communication text, tireur text, donneur text, numero_cheque text, solde numeric, telephone character varying, langue integer, num_complet_cpte text, intitule_compte text, date_ouvert timestamp without time zone, statut_juridique integer, nom text, prenom text, libelle_produit text)
LANGUAGE plpgsql
AS $$
declare
	v_cpte_interne_cli ALIAS for $1;
	v_montant ALIAS for $2;
	v_date_valeur ALIAS for $3;
	v_id_ag ALIAS for $4;

BEGIN

return query

select
	case
		when h.id_client is null
		then cpt.id_titulaire
		else h.id_client
	end as id_client,
	m.id_ag,
	m.cpte_interne_cli as id_cpte,
	m.id_ecriture as id_transaction,
	m.id_mouvement,
	h.date as date_transaction,
	e.ref_ecriture,
	e.type_operation as type_opt,
	t.traduction as libelle_ecriture,
	m.montant,
	m.sens,
	m.devise,
	histo_ext.communication,
	case
		when h.type_fonction in (70,75)
		then histo_ext.tireur
		else null
	end as tireur,
	histo_ext.nom_client AS donneur,
	histo_ext.numero_cheque,
	cpt.solde,
	a.num_sms as telephone,
	a.langue,
	cpt.num_complet_cpte,
	cpt.intitule_compte,
	cpt.date_ouvert as date_ouvert,
	c.statut_juridique,
	c.pp_nom as nom,
	c.pp_prenom as prenom,
	produit.libel as libelle_produit
from
	ad_mouvement m
	inner join ad_ecriture e on e.id_ag=m.id_ag and e.id_ecriture=m.id_ecriture
	inner join ad_his h on h.id_ag=e.id_ag and h.id_his=e.id_his
	left join
			(select
				ext.id_ag,
				ext.id,
				p.nom_client,
				tb.denomination as tireur,
				case
					when ext.type_piece in (2,4,5,15)
					then ext.num_piece
					else null
				end AS numero_cheque,
				ext.communication
			from
				ad_his_ext ext
				left join
						(select
							pers.id_ag,pers.id_client,pers.id_pers_ext,
							COALESCE (CASE
										cli.statut_juridique
										WHEN '1'
										THEN pp_nom||' '||pp_prenom
										WHEN '2'
										THEN pm_raison_sociale
										WHEN '3'
										THEN gi_nom
										WHEN '4'
										THEN gi_nom
									END, pers.denomination)  AS nom_client
						FROM ad_pers_ext pers
						left join  ad_cli cli on cli.id_ag = pers.id_ag and cli.id_client = pers.id_client) p on ext.id_ag  = p.id_ag and ext.id_pers_ext = p.id_pers_ext
						left join tireur_benef tb on ext.id_tireur_benef = tb.id and ext.id_ag = tb.id_ag
			) histo_ext on histo_ext.id_ag=h.id_ag and h.id_his_ext = histo_ext.id
	inner join ad_traductions t on t.id_str =e.libel_ecriture
	inner join ad_cpt cpt on m.id_ag = cpt.id_ag and m.cpte_interne_cli = cpt.id_cpte
  inner join ad_abonnement a ON cpt.id_titulaire = a.id_client AND cpt.id_ag = a.id_ag
  inner join ad_cli c ON a.id_client = c.id_client AND a.id_ag = c.id_ag
  inner join adsys_produit_epargne produit ON cpt.id_prod = produit.id AND cpt.id_ag = produit.id_ag
where
	cpt.id_prod NOT IN (3,4)
and
  h.id_his =
  (
    SELECT h.id_his
    FROM ad_mouvement m
    INNER JOIN ad_ecriture e ON m.id_ecriture = e.id_ecriture AND m.id_ag = e.id_ag
    INNER JOIN ad_his h ON e.id_his = h.id_his AND h.id_ag = e.id_ag
    WHERE m.cpte_interne_cli = cast(v_cpte_interne_cli as INTEGER)
    AND m.montant = v_montant
    AND m.date_valeur = to_date(v_date_valeur, 'yyyy-MM-dd')
		AND h.id_ag = v_id_ag
    ORDER BY date_valeur DESC
    LIMIT 1
  )
and
	m.cpte_interne_cli = cast(v_cpte_interne_cli as INTEGER)
and
	m.montant = v_montant
and
	m.date_valeur = to_date(v_date_valeur, 'yyyy-MM-dd');
 end;
$$;
---------------------------------------------------------------------------------------------------

----------- DROP fonction f_getmouvementforproducerarretecomptebatch(integer, integer, numeric, timestamp without time zone) -----------------
CREATE OR REPLACE function f_getmouvementforproducerarretecomptebatch(integer, integer, numeric, timestamp without time zone) returns TABLE(id_client integer, telephone character varying, langue integer, num_complet_cpte text, intitule_compte text, date_ouvert timestamp without time zone, nom text, prenom text, statut_juridique integer, libelle_produit text, id_ag integer, id_cpte integer, id_transaction integer, id_mouvement integer, montant numeric, sens text, devise character, ref_ecriture text, type_opt integer, libelle_ecriture text, solde numeric, date_transaction timestamp without time zone)
LANGUAGE plpgsql
AS $$
DECLARE
  v_id_mouvement ALIAS FOR $1;
  v_id_ag ALIAS FOR $2;
  v_solde ALIAS FOR $3;
  v_date_transaction ALIAS FOR $4;
BEGIN

  RETURN QUERY
		with adm as
		(SELECT
		m.id_ag,
		m.cpte_interne_cli AS id_cpte,
		m.id_ecriture AS id_transaction,
		m.id_mouvement,
		m.id_ecriture,
		m.montant,
		m.sens,
		m.devise
		FROM ad_mouvement m
		WHERE m.id_mouvement = v_id_mouvement
		),

		ade as
		(SELECT
		e.*
		FROM ad_ecriture e
		join adm on e.id_ecriture = adm.id_ecriture
		),

		adt as
		(SELECT
		traduction,
		id_str
		FROM ad_traductions t
		join ade on t.id_str = ade.libel_ecriture
		)

		SELECT
		cpt.id_titulaire AS id_client,
		a.num_sms AS telephone,
		a.langue,
		cpt.num_complet_cpte,
		cpt.intitule_compte,
		cpt.date_ouvert AS date_ouvert,
		c.pp_nom AS nom,
		c.pp_prenom AS prenom,
		c.statut_juridique,
		produit.libel AS libelle_produit,
		adm.id_ag,
		adm.id_cpte,
		adm.id_transaction,
		adm.id_mouvement,
		adm.montant,
		adm.sens,
		adm.devise,
		ade.ref_ecriture,
		ade.type_operation AS type_opt,
		adt.traduction AS libelle_ecriture,
		v_solde AS solde,
		v_date_transaction AS date_transaction
		FROM ad_cpt cpt
		INNER JOIN ad_abonnement a ON cpt.id_titulaire = a.id_client AND cpt.id_ag = a.id_ag
		INNER JOIN ad_cli c ON a.id_client = c.id_client AND a.id_ag = c.id_ag
		INNER JOIN adsys_produit_epargne produit ON cpt.id_prod = produit.id AND cpt.id_ag = produit.id_ag
		INNER JOIN adm on cpt.id_cpte = adm.id_cpte and cpt.id_ag = adm.id_ag
		INNER JOIN ade on adm.id_transaction = ade.id_ecriture and adm.id_ag = ade.id_ag
		INNER JOIN adt on ade.libel_ecriture = adt.id_str
		WHERE cpt.id_cpte = adm.id_cpte AND cpt.id_ag = v_id_ag;
END;
$$;
----------------------------------------------------------------------------------------------------------------------------------------------


----------- CREATION TABLE ad_msq -----------------
CREATE OR REPLACE FUNCTION creation_tb_ad_msq() RETURNS INT AS
$$
DECLARE
output_result INTEGER = 1;

BEGIN

-- Check if table ad_msq exist
IF NOT EXISTS(SELECT * FROM information_schema.tables WHERE table_name = 'ad_msq') THEN

    CREATE TABLE ad_msq (
        id SERIAL NOT NULL PRIMARY KEY,
        encoded_message TEXT NULL,
        date_creation timestamp(6) without time zone NULL,
        date_traitement timestamp(6) without time zone NULL,
        nb_essaie INTEGER DEFAULT 0,
        statut INTEGER,
        type_msg INTEGER NULL, -- mouvement, abonnement,, etc...
        id_ag INTEGER NULL
    )
    WITH (
      OIDS=FALSE
    );
      ALTER TABLE ad_msq
      OWNER TO postgres;
END IF;
	RETURN output_result;
END;
$$
LANGUAGE plpgsql;

SELECT creation_tb_ad_msq();
DROP FUNCTION creation_tb_ad_msq();
---------------------------------------------------