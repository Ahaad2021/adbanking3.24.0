--------------------------------------------Debut Ticket AT-155------------------------------------------------------------------------------------
-- Function: get_ad_dcr_ext_credit(integer, integer, integer, integer, integer)

-- DROP FUNCTION get_ad_dcr_ext_credit(integer, integer, integer, integer, integer);

CREATE OR REPLACE FUNCTION get_ad_dcr_ext_credit(
    integer,
    integer,
    integer,
    integer,
    integer)
  RETURNS SETOF dcr_credit_view AS
$BODY$
  DECLARE
    p_id_dossier ALIAS FOR $1;
    p_id_client ALIAS FOR $2;
    p_etat ALIAS FOR $3;
    p_cre_etat ALIAS FOR $4;
    p_id_agence ALIAS FOR $5;
    statut INTEGER ;


cur_credit_gs CURSOR FOR SELECT grp.id_grp_sol as id_grp, grp.id_membre as id_client, dcr.id_doss, dcr.id_ag, dcr.is_extended FROM ad_grp_sol grp
inner join ad_dcr dcr on grp.id_membre = dcr.id_client and grp.id_ag = dcr.id_ag
WHERE grp.id_grp_sol = p_id_client
union
select CASE WHEN dcr.gs_cat = 1 THEN dcr.id_client END as id_grp, dcr.id_client, dcr.id_doss, dcr.id_ag, dcr.is_extended from ad_dcr dcr where dcr.id_client = p_id_client and dcr.id_ag= p_id_agence;


cur_credit CURSOR FOR SELECT id_doss, id_ag, is_extended FROM ad_dcr WHERE id_client = CASE WHEN p_id_client IS NULL THEN id_client ELSE p_id_client END AND id_doss = CASE WHEN p_id_dossier IS NULL THEN id_doss
ELSE p_id_dossier END AND etat = CASE WHEN p_etat IS NULL THEN etat ELSE p_etat END AND coalesce(cre_etat,0) = CASE WHEN p_cre_etat IS NULL THEN coalesce(cre_etat,0) ELSE p_cre_etat END AND id_ag = p_id_agence
ORDER BY id_doss ASC;


ligne RECORD;

dcr_credit dcr_credit_view;

  BEGIN

   select into statut statut_juridique from ad_cli where id_client = p_id_client;

   IF (statut = '4') THEN
	OPEN cur_credit_gs;
    FETCH cur_credit_gs INTO ligne;
   ELSE
	OPEN cur_credit;
    FETCH cur_credit INTO ligne;
   END IF;

    WHILE FOUND LOOP

      IF (ligne.is_extended = 't') THEN

        SELECT INTO dcr_credit  d.id_doss, d.id_client, d.id_prod, d.date_dem, d.mnt_dem, d.obj_dem, d.detail_obj_dem, d.etat, d.date_etat, d.motif, d.id_agent_gest, d.delai_grac, d.differe_jours, d.prelev_auto, d.duree_mois, d.nouv_duree_mois, d.terme, d.gar_num, d.gar_tot, d.gar_mat, d.gar_num_encours, d.cpt_gar_encours, d.num_cre, d.assurances_cre, d.cpt_liaison, d.cre_id_cpte, d.cre_etat, d.cre_date_etat, d.cre_date_approb, d.cre_date_debloc, d.cre_nbre_reech, d.cre_mnt_octr, d.details_motif, d.suspension_pen, d.perte_capital, d.cre_retard_etat_max, d.cre_retard_etat_max_jour, d.differe_ech, d.id_dcr_grp_sol, dx.gs_cat, d.prelev_commission, d.cpt_prelev_frais, d.id_ag, d.cre_prelev_frais_doss, d.prov_mnt, d.prov_date, d.prov_is_calcul, d.cre_mnt_deb, d.doss_repris, d.cre_cpt_att_deb, d.date_creation, d.date_modif, d.is_ligne_credit, d.deboursement_autorisee_lcr, d.motif_changement_authorisation_lcr, d.date_changement_authorisation_lcr, d.duree_nettoyage_lcr, d.remb_auto_lcr, d.tx_interet_lcr, d.taux_frais_lcr, d.taux_min_frais_lcr, d.taux_max_frais_lcr, d.ordre_remb_lcr, dx.mnt_assurance, dx.mnt_commission, d.mnt_frais_doss, d.detail_obj_dem_bis,d.detail_obj_dem_2, d.id_bailleur, d.is_extended, pc.id, pc.libel, dx.tx_interet, pc.mnt_min, pc.mnt_max, pc.mode_calc_int, pc.mode_perc_int, pc.duree_min_mois, pc.duree_max_mois, dx.periodicite, dx.mnt_frais, dx.prc_assurance, dx.prc_gar_num, pc.prc_gar_mat, (dx.prc_gar_num + pc.prc_gar_mat), pc.prc_gar_encours, pc.mnt_penalite_jour, pc.prc_penalite_retard, pc.delai_grace_jour, pc.differe_jours_max, pc.nbre_reechelon_auth, dx.prc_commission, pc.type_duree_credit, pc.approbation_obli, pc.typ_pen_pourc_dcr, pc.cpte_cpta_prod_cr_int, pc.cpte_cpta_prod_cr_gar, pc.cpte_cpta_prod_cr_pen, pc.devise, pc.differe_ech_max, pc.freq_paiement_cap, pc.max_jours_compt_penalite, pc.differe_epargne_nantie, pc.report_arrondi, pc.calcul_interet_differe, pc.prelev_frais_doss, pc.percep_frais_com_ass, pc.ordre_remb, pc.remb_cpt_gar, pc.is_produit_decouvert, dx.prc_frais, pc.cpte_cpta_att_deb, pc.is_produit_actif, pc.duree_nettoyage, pc.cpte_cpta_prod_cr_frais FROM ad_dcr d LEFT JOIN ad_dcr_ext dx ON d.id_doss = dx.id_doss AND d.id_ag = dx.id_ag INNER JOIN adsys_produit_credit pc ON d.id_prod = pc.id AND d.id_ag = pc.id_ag WHERE d.id_doss = ligne.id_doss AND d.id_ag = ligne.id_ag;

      ELSE

        SELECT INTO dcr_credit  d.id_doss, d.id_client, d.id_prod, d.date_dem, d.mnt_dem, d.obj_dem, d.detail_obj_dem, d.etat, d.date_etat, d.motif, d.id_agent_gest, d.delai_grac, d.differe_jours, d.prelev_auto, d.duree_mois, d.nouv_duree_mois, d.terme, d.gar_num, d.gar_tot, d.gar_mat, d.gar_num_encours, d.cpt_gar_encours, d.num_cre, d.assurances_cre, d.cpt_liaison, d.cre_id_cpte, d.cre_etat, d.cre_date_etat, d.cre_date_approb, d.cre_date_debloc, d.cre_nbre_reech, d.cre_mnt_octr, d.details_motif, d.suspension_pen, d.perte_capital, d.cre_retard_etat_max, d.cre_retard_etat_max_jour, d.differe_ech, d.id_dcr_grp_sol, d.gs_cat, d.prelev_commission, d.cpt_prelev_frais, d.id_ag, d.cre_prelev_frais_doss, d.prov_mnt, d.prov_date, d.prov_is_calcul, d.cre_mnt_deb, d.doss_repris, d.cre_cpt_att_deb, d.date_creation, d.date_modif, d.is_ligne_credit, d.deboursement_autorisee_lcr, d.motif_changement_authorisation_lcr, d.date_changement_authorisation_lcr, d.duree_nettoyage_lcr, d.remb_auto_lcr, d.tx_interet_lcr, d.taux_frais_lcr, d.taux_min_frais_lcr, d.taux_max_frais_lcr, d.ordre_remb_lcr, d.mnt_assurance, d.mnt_commission, d.mnt_frais_doss, d.detail_obj_dem_bis,d.detail_obj_dem_2, d.id_bailleur, d.is_extended, pc.id, pc.libel, pc.tx_interet, pc.mnt_min, pc.mnt_max, pc.mode_calc_int, pc.mode_perc_int, pc.duree_min_mois, pc.duree_max_mois, pc.periodicite, pc.mnt_frais, pc.prc_assurance, pc.prc_gar_num, pc.prc_gar_mat, pc.prc_gar_tot, pc.prc_gar_encours, pc.mnt_penalite_jour, pc.prc_penalite_retard, pc.delai_grace_jour, pc.differe_jours_max, pc.nbre_reechelon_auth, pc.prc_commission, pc.type_duree_credit, pc.approbation_obli, pc.typ_pen_pourc_dcr, pc.cpte_cpta_prod_cr_int, pc.cpte_cpta_prod_cr_gar, pc.cpte_cpta_prod_cr_pen, pc.devise, pc.differe_ech_max, pc.freq_paiement_cap, pc.max_jours_compt_penalite, pc.differe_epargne_nantie, pc.report_arrondi, pc.calcul_interet_differe, pc.prelev_frais_doss, pc.percep_frais_com_ass, pc.ordre_remb, pc.remb_cpt_gar, pc.is_produit_decouvert, pc.prc_frais, pc.cpte_cpta_att_deb, pc.is_produit_actif, pc.duree_nettoyage, pc.cpte_cpta_prod_cr_frais FROM ad_dcr d LEFT JOIN adsys_produit_credit pc ON d.id_prod = pc.id AND d.id_ag = pc.id_ag WHERE d.id_doss = ligne.id_doss AND d.id_ag = ligne.id_ag;

      END IF;

      RETURN NEXT dcr_credit;
  IF (statut = '4') THEN
    FETCH cur_credit_gs INTO ligne;
   ELSE
	FETCH cur_credit INTO ligne;
   END IF;

    END LOOP;
  IF (statut = '4') THEN
	CLOSE cur_credit_gs;
   ELSE
	CLOSE cur_credit;
   END IF;

    RETURN;
  END;
	$BODY$
  LANGUAGE plpgsql VOLATILE
  COST 100
  ROWS 1000;
ALTER FUNCTION get_ad_dcr_ext_credit(integer, integer, integer, integer, integer)
  OWNER TO postgres;
  --------------------------------------------Fin Ticket AT-155------------------------------------------------------------------------------------
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
