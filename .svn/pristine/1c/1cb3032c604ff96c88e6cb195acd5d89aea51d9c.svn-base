---------------------------------- DEBUT : Ticket MSQ-26 -------------------------------------------
CREATE OR REPLACE function f_getmouvementforproducercloturecomptebatch(text, numeric, text, integer) returns TABLE(id_client integer, id_ag integer, id_cpte integer, id_transaction integer, id_mouvement integer, date_transaction timestamp without time zone, ref_ecriture text, type_opt integer, libelle_ecriture text, montant numeric, sens text, devise character, communication text, tireur text, donneur text, numero_cheque text, solde numeric, telephone character varying, langue integer, num_complet_cpte text, intitule_compte text, date_ouvert timestamp without time zone, statut_juridique integer, nom text, prenom text, libelle_produit text)
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
	cpt.solde AS solde,
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
	m.date_valeur = to_date(v_date_valeur, 'yyyy-MM-dd')
and
    a.deleted = FALSE;
 end;
$$;
---------------------------------- FIN : Ticket MSQ-26 -------------------------------------------

---------------------------------- DEBUT : Ticket MSQ-39 -------------------------------------------
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
        SELECT INTO v_nouvo_solde solde FROM ad_cpt WHERE id_cpte = cpte_destination_int; -- valeur sera utiliser dans une requete pour la fonction f_getmouvementforproducerarretecomptebatch

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
---------------------------------- FIN : Ticket MSQ-39 -------------------------------------------