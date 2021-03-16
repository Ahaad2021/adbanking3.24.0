-------------------------------------------Debut Tickt REL-110 : Bug tres urgent : Versement des Interets de DAT--------------------------------------
  -- Function: arrete_comptes(date)

-- DROP FUNCTION arrete_comptes(date);

CREATE OR REPLACE FUNCTION arrete_comptes(date)
  RETURNS SETOF int_arret_cptes AS
$BODY$

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

				rep:=payeInteret(ligne.id_cpte,cpte_destination_int,interet,date_batch);
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
			SELECT INTO resultat ligne.id_cpte, ligne.id_titulaire,ligne.solde_calcul_interets, ligne.perio_cap, ligne.classe_comptable, interet,  cpte_destination_int;
			RETURN NEXT resultat;

			RAISE NOTICE 'le compte traité est % | l''interet calculé est % ',ligne.id_cpte,interet;

			--FETCH Cpt_Calcul_Int INTO lign;

		END LOOP;

		--CLOSE Cpt_Calcul_Int;
 	RETURN ;

 	END;
 	$BODY$
  LANGUAGE plpgsql VOLATILE
  COST 100
  ROWS 1000;
ALTER FUNCTION arrete_comptes(date)
  OWNER TO postgres;
-------------------------------------------Fin Tickt REL-110 : Bug tres urgent : Versement des Interets de DAT--------------------------------------