ALTER TABLE ad_agc ADD CONSTRAINT "un exercice comptable est associé à une agence" FOREIGN KEY (exercice,id_ag) REFERENCES ad_exercices_compta (id_exo_compta,id_ag) ON UPDATE No action ON DELETE No action;ALTER TABLE ad_agc ADD CONSTRAINT "ad_agc_ad_cpt_comptable_num_cpte_resultat_fkey" FOREIGN KEY (num_cpte_resultat) REFERENCES ad_cpt_comptable (num_cpte_comptable) ON UPDATE No action ON DELETE No action;
ALTER TABLE ad_agc ADD CONSTRAINT "ad_agc_ad_cpt_comptable_cpte_cpta_coffre_fkey" FOREIGN KEY (cpte_cpta_coffre,id_ag) REFERENCES ad_cpt_comptable (num_cpte_comptable,id_ag) ON UPDATE No action ON DELETE No action;
ALTER TABLE ad_agc ADD CONSTRAINT "ad_agc_ad_cpt_comptable_cpte_position_change_fkey" FOREIGN KEY (cpte_position_change,id_ag) REFERENCES ad_cpt_comptable (num_cpte_comptable,id_ag) ON UPDATE No action ON DELETE No action;ALTER TABLE ad_agc ADD CONSTRAINT "ad_agc_ad_cpt_comptable_cpte_contreval_position_change_fkey" FOREIGN KEY (cpte_contreval_position_change) REFERENCES ad_cpt_comptable (num_cpte_comptable) ON UPDATE No action ON DELETE No action;
ALTER TABLE ad_agc ADD CONSTRAINT "ad_agc_ad_cpt_comptable_cpte_variation_taux_deb_fkey" FOREIGN KEY (cpte_variation_taux_deb,id_ag) REFERENCES ad_cpt_comptable (num_cpte_comptable,id_ag) ON UPDATE No action ON DELETE No action;ALTER TABLE ad_agc ADD CONSTRAINT "ad_agc_ad_cpt_comptable_cpte_variation_taux_cred_fkey" FOREIGN KEY (cpte_variation_taux_cred) REFERENCES ad_cpt_comptable (num_cpte_comptable) ON UPDATE No action ON DELETE No action;
ALTER TABLE ad_agc ADD CONSTRAINT "ad_agc_ad_cpt_comptable_num_cpte_tch_fkey" FOREIGN KEY (num_cpte_tch,id_ag) REFERENCES ad_cpt_comptable (num_cpte_comptable,id_ag) ON UPDATE No action ON DELETE No action;ALTER TABLE ad_agc ADD CONSTRAINT "ad_agc_adsys_langues_systeme_langue_systeme_dft_fkey" FOREIGN KEY (langue_systeme_dft) REFERENCES adsys_langues_systeme (code) ON UPDATE No action ON DELETE No action;
ALTER TABLE ad_biens ADD CONSTRAINT "ad_biens_ad_cli_id_client_fkey" FOREIGN KEY (id_client,id_ag) REFERENCES ad_cli (id_client,id_ag) ON UPDATE No action ON DELETE No action;ALTER TABLE ad_biens ADD CONSTRAINT "ad_biens_adsys_types_biens_type_bien_fkey" FOREIGN KEY (type_bien) REFERENCES adsys_types_biens (id) ON UPDATE No action ON DELETE No action;
ALTER TABLE ad_biens ADD CONSTRAINT "ad_biens_devise__devise_valeur_fkey" FOREIGN KEY (devise_valeur,id_ag) REFERENCES devise (code_devise,id_ag) ON UPDATE No action ON DELETE No action;ALTER TABLE ad_brouillard ADD CONSTRAINT "ad_brouillard_ad_cpt_comptable_compte_fkey" FOREIGN KEY (compte) REFERENCES ad_cpt_comptable (num_cpte_comptable) ON UPDATE No action ON DELETE No action;
ALTER TABLE ad_brouillard ADD CONSTRAINT "ad_brouillard_ad_cpt_cpte_interne_cli_fkey" FOREIGN KEY (cpte_interne_cli,id_ag) REFERENCES ad_cpt (id_cpte,id_ag) ON UPDATE No action ON DELETE No action;ALTER TABLE ad_brouillard ADD CONSTRAINT "ad_brouillard_devise_devise_fkey" FOREIGN KEY (devise) REFERENCES devise (code_devise) ON UPDATE No action ON DELETE No action;
ALTER TABLE ad_brouillard ADD CONSTRAINT "ad_brouillard_ad_journaux_id_jou_fkey" FOREIGN KEY (id_jou,id_ag) REFERENCES ad_journaux (id_jou,id_ag) ON UPDATE No action ON DELETE No action;
ALTER TABLE ad_brouillard ADD CONSTRAINT "ad_brouillard_ad_exercices_compta_id_exo_fkey" FOREIGN KEY (id_exo,id_ag) REFERENCES ad_exercices_compta (id_exo_compta,id_ag) ON UPDATE No action ON DELETE No action;
ALTER TABLE ad_cli ADD CONSTRAINT "ad_cli_adsys_pays_pays_fkey" FOREIGN KEY (pays,id_ag) REFERENCES adsys_pays (id_pays,id_ag) ON UPDATE No action ON DELETE No action;
--ALTER TABLE ad_cli ADD CONSTRAINT "ad_cli_adsys_pays_pp_pays_naiss_fkey" FOREIGN KEY (pp_pays_naiss,id_ag) REFERENCES adsys_pays (id_pays,id_ag) ON UPDATE No action ON DELETE No action;
--ALTER TABLE ad_cli ADD CONSTRAINT "ad_cli_adsys_pays_pp_nationalite_fkey" FOREIGN KEY (pp_nationalite,id_ag) REFERENCES adsys_pays (id_pays,id_ag) ON UPDATE No action ON DELETE No action;
--ALTER TABLE ad_cli ADD CONSTRAINT "ad_cli_adsys_langues_systeme_langue_correspondance_fkey" FOREIGN KEY (langue_correspondance) REFERENCES adsys_langues_systeme (code) ON UPDATE No action ON DELETE No action;ALTER TABLE ad_clotures_periode ADD CONSTRAINT "ad_clotures_periode_ad_exercices_compta_id_exo_fkey" FOREIGN KEY (id_exo) REFERENCES ad_exercices_compta (id_exo_compta) ON UPDATE No action ON DELETE No action;
--ALTER TABLE ad_comptable_attente ADD CONSTRAINT "ad_comptable_attente_ad_cpt_cpte_interne_cli_fkey" FOREIGN KEY (cpte_interne_cli) REFERENCES ad_cpt (id_cpte) ON UPDATE No action ON DELETE No action;
ALTER TABLE ad_cpt ADD CONSTRAINT "ad_cpt_ad_cli_id_titulaire_fkey" FOREIGN KEY (id_titulaire,id_ag) REFERENCES ad_cli (id_client,id_ag) ON UPDATE No action ON DELETE No action;ALTER TABLE ad_cpt ADD CONSTRAINT "ad_cpt_adsys_produit_epargne_id_prod_fkey" FOREIGN KEY (id_prod) REFERENCES adsys_produit_epargne (id) ON UPDATE No action ON DELETE No action;
ALTER TABLE ad_cpt ADD CONSTRAINT "ad_cpt_devise_devise" FOREIGN KEY (devise,id_ag) REFERENCES devise (code_devise,id_ag) ON UPDATE No action ON DELETE No action;
ALTER TABLE ad_cpt ADD CONSTRAINT "ad_cpt_ad_cpt_cpte_virement_clot_fkey"FOREIGN KEY (cpte_virement_clot,id_ag) REFERENCES ad_cpt (id_cpte,id_ag) ON UPDATE No action ON DELETE No action;
--ALTER TABLE ad_cpt_comptable ADD CONSTRAINT "ad_cpt_comptable_ad_classes_compta_classe_compta_fkey" FOREIGN KEY (classe_compta,id_ag) REFERENCES ad_classes_compta (numero_classe,id_ag) ON UPDATE No action ON DELETE No action;
--ALTER TABLE ad_cpt_comptable ADD CONSTRAINT "ad_cpt_comptable_ad_cpt_comptable_cpte_centralise_key" FOREIGN KEY (cpte_centralise,id_ag) REFERENCES ad_cpt_comptable (num_cpte_comptable,id_ag) ON UPDATE No action ON DELETE No action;
--ALTER TABLE ad_cpt_comptable ADD CONSTRAINT "ad_cpt_comptable_ad_cpt_comptable_cpte_provision_fkey" FOREIGN KEY (cpte_provision,id_ag) REFERENCES ad_cpt_comptable (num_cpte_comptable,id_ag) ON UPDATE No action ON DELETE No action;
ALTER TABLE ad_cpt_comptable ADD CONSTRAINT "ad_cpt_comptable_ad_agc_id_ag_fkey" FOREIGN KEY (id_ag) REFERENCES ad_agc (id_ag) ON UPDATE No action ON DELETE No action;
ALTER TABLE ad_cpt_ope_cptes ADD CONSTRAINT "ad_cpt_ope_cptes_ad_cpt_ope_type_operation_fkey" FOREIGN KEY (type_operation,id_ag) REFERENCES ad_cpt_ope (type_operation,id_ag) ON UPDATE No action ON DELETE No action;
ALTER TABLE ad_cpt_ope_cptes ADD CONSTRAINT "ad_cpt_ope_cptes_ad_cpt_comptable_num_cpte_fkey" FOREIGN KEY (num_cpte,id_ag) REFERENCES ad_cpt_comptable (num_cpte_comptable,id_ag) ON UPDATE No action ON DELETE No action;
--ALTER TABLE ad_cpt_soldes ADD CONSTRAINT "ad_cpt_soldes_ad_cpt_comptable_num_cpte_comptable_solde_fkey" FOREIGN KEY (num_cpte_comptable_solde,id_ag) REFERENCES ad_cpt_comptable (num_cpte_comptable,id_ag) ON UPDATE No action ON DELETE No action;
--ALTER TABLE ad_cpt_soldes ADD CONSTRAINT "ad_cpt_soldes_ad_clotures_periode_id_cloture_fkey" FOREIGN KEY (id_cloture,id_ag) REFERENCES ad_clotures_periode (id_clot_per,id_ag) ON UPDATE No action ON DELETE No action;
ALTER TABLE ad_dcr ADD CONSTRAINT "ad_dcr_adsys_produit_credit_id_prod_fkey" FOREIGN KEY (id_prod,id_ag) REFERENCES adsys_produit_credit (id,id_ag) ON UPDATE No action ON DELETE No action;
ALTER TABLE ad_dcr ADD CONSTRAINT "ad_dcr_ad_cpt_cpt_gar_encours_fkey" FOREIGN KEY (cpt_gar_encours,id_ag) REFERENCES ad_cpt (id_cpte,id_ag) ON UPDATE No action ON DELETE No action;
ALTER TABLE ad_dcr ADD CONSTRAINT "ad_dcr_ad_cpt_cpt_liaison_fkey" FOREIGN KEY (cpt_liaison,id_ag) REFERENCES ad_cpt (id_cpte,id_ag) ON UPDATE No action ON DELETE No action;
ALTER TABLE ad_dcr ADD CONSTRAINT "ad_dcr_ad_cpt_cre_id_cpte_fkey" FOREIGN KEY (cre_id_cpte,id_ag) REFERENCES ad_cpt (id_cpte,id_ag) ON UPDATE No action ON DELETE No action;
--ALTER TABLE ad_dcr ADD CONSTRAINT "ad_dcr_ad_cpt_cre_prelev_frais_doss_fkey" FOREIGN KEY (cre_prelev_frais_doss,id_ag) REFERENCES ad_cpt (id_cpte,id_ag) ON UPDATE No action ON DELETE No action;
ALTER TABLE ad_dcr ADD CONSTRAINT "ad_dcr_ad_dcr_grp_sol_id_dcr_grp_sol" FOREIGN KEY (id_dcr_grp_sol,id_ag) REFERENCES ad_dcr_grp_sol (id,id_ag) ON UPDATE No action ON DELETE No action;
ALTER TABLE ad_dcr ADD CONSTRAINT "ad_dcr_adsys_etat_credits_cre_etat_fkey" FOREIGN KEY (cre_etat,id_ag) REFERENCES adsys_etat_credits (id,id_ag) ON UPDATE No action ON DELETE No action;
ALTER TABLE ad_dcr ADD CONSTRAINT "ad_dcr_ad_uti_id_agent_gest_fkey" FOREIGN KEY (id_agent_gest,id_ag) REFERENCES ad_uti (id_utilis,id_ag) ON UPDATE No action ON DELETE No action;
ALTER TABLE ad_dcr ADD CONSTRAINT "ad_dcr_adsys_objets_credits_obj_dem_fkey" FOREIGN KEY (obj_dem,id_ag) REFERENCES adsys_objets_credits (id,id_ag) ON UPDATE No action ON DELETE No action;
ALTER TABLE ad_dcr_grp_sol ADD CONSTRAINT "ad_dcr_grp_sol_ad_dcr_id_dcr_grp_sol_fkey" FOREIGN KEY (id_dcr_grp_sol,id_ag) REFERENCES ad_dcr (id_doss,id_ag) ON UPDATE No action ON DELETE No action;
ALTER TABLE ad_dcr_grp_sol ADD CONSTRAINT "ad_dcr_grp_sol_ad_cli_id_membre_fkey" FOREIGN KEY (id_membre,id_ag) REFERENCES ad_cli (id_client,id_ag) ON UPDATE No action ON DELETE No action;
ALTER TABLE ad_dcr_grp_sol ADD CONSTRAINT "ad_dcr_grp_sol_adsys_objets_credits_obj_dem_fkey" FOREIGN KEY (obj_dem,id_ag) REFERENCES adsys_objets_credits (id,id_ag) ON UPDATE No action ON DELETE No action;

ALTER TABLE ad_ecriture ADD CONSTRAINT "ad_ecriture_ad_his_id_his_fkey" FOREIGN KEY (id_his,id_ag) REFERENCES ad_his (id_his,id_ag) ON UPDATE No action ON DELETE No action;
ALTER TABLE ad_ecriture ADD CONSTRAINT "ad_ecriture_ad_journaux_id_jou_fkey" FOREIGN KEY (id_jou,id_ag) REFERENCES ad_journaux (id_jou,id_ag) ON UPDATE No action ON DELETE No action;
ALTER TABLE ad_ecriture ADD CONSTRAINT "ad_ecriture_d_exercices_compta_id_exo_fkey" FOREIGN KEY (id_exo,id_ag) REFERENCES ad_exercices_compta (id_exo_compta,id_ag) ON UPDATE No action ON DELETE No action;
ALTER TABLE ad_etr ADD CONSTRAINT "ad_etr_ad_dcr_id_doss_fkey" FOREIGN KEY (id_doss,id_ag) REFERENCES ad_dcr (id_doss,id_ag) ON UPDATE No action ON DELETE No action;

ALTER TABLE ad_extrait_cpte ADD CONSTRAINT "ad_extrait_cpte_ad_his_id_his_fkey" FOREIGN KEY (id_his,id_ag) REFERENCES ad_his (id_his,id_ag) ON UPDATE No action ON DELETE No action;
ALTER TABLE ad_extrait_cpte ADD CONSTRAINT "ad_extrait_cpte_ad_cpt_id_cpte_fkey" FOREIGN KEY (id_cpte,id_ag) REFERENCES ad_cpt (id_cpte,id_ag) ON UPDATE No action ON DELETE No action;
ALTER TABLE ad_extrait_cpte ADD CONSTRAINT "ad_extrait_cpte_ad_cpt_comptable_cptie_num_cpte_fkey" FOREIGN KEY (cptie_num_cpte,id_ag) REFERENCES ad_cpt_comptable (num_cpte_comptable,id_ag) ON UPDATE No action ON DELETE No action;
ALTER TABLE ad_extrait_cpte ADD CONSTRAINT "ad_extrait_cpte_ad_mouvement_eft_id_mvt_fkey" FOREIGN KEY (eft_id_mvt,id_ag) REFERENCES ad_mouvement (id_mouvement,id_ag) ON UPDATE No action ON DELETE No action;

ALTER TABLE ad_extrait_cpte ADD CONSTRAINT "ad_extrait_cpte_adsys_pays_cptie_pays_fkey" FOREIGN KEY (cptie_pays,id_ag) REFERENCES adsys_pays (id_pays,id_ag) ON UPDATE No action ON DELETE No action;
ALTER TABLE ad_extrait_cpte ADD CONSTRAINT "ad_extrait_cpte_devise_cptie_devise_fkey " FOREIGN KEY (cptie_devise,id_ag) REFERENCES devise (code_devise,id_ag) ON UPDATE No action ON DELETE No action;
ALTER TABLE ad_extrait_cpte ADD CONSTRAINT "ad_extrait_cpte_ad_cli_eft_id_client_fkey" FOREIGN KEY (eft_id_client,id_ag) REFERENCES ad_cli (id_client,id_ag) ON UPDATE No action ON DELETE No action;
ALTER TABLE ad_frais_attente ADD CONSTRAINT "ad_frais_attente_ad_cpt_id_cpte_fkey" FOREIGN KEY (id_cpte,id_ag) REFERENCES ad_cpt (id_cpte,id_ag) ON UPDATE No action ON DELETE No action;
ALTER TABLE ad_frais_attente ADD CONSTRAINT "ad_frais_attente_ad_cpt_ope_type_frais_fkey" FOREIGN KEY (type_frais,id_ag) REFERENCES ad_cpt_ope (type_operation,id_ag) ON UPDATE No action ON DELETE No action;
ALTER TABLE ad_gar ADD CONSTRAINT "ad_agr_ad_dcr_id_doss_fkey" FOREIGN KEY (id_doss,id_ag) REFERENCES ad_dcr (id_doss,id_ag) ON UPDATE No action ON DELETE No action;
ALTER TABLE ad_gar ADD CONSTRAINT "ad_gar_ad_biens_gar_mat_id_bien_fkey" FOREIGN KEY (gar_mat_id_bien,id_ag) REFERENCES ad_biens (id_bien,id_ag) ON UPDATE No action ON DELETE No action;
ALTER TABLE ad_gar ADD CONSTRAINT "ad_gar_ad_cpt_gar_num_id_cpte_prelev_fkey" FOREIGN KEY (gar_num_id_cpte_prelev,id_ag) REFERENCES ad_cpt (id_cpte,id_ag) ON UPDATE No action ON DELETE No action;
ALTER TABLE ad_gar ADD CONSTRAINT "ad_gar_ad_cpt_gar_num_id_cpte_nantie" FOREIGN KEY (gar_num_id_cpte_nantie,id_ag) REFERENCES ad_cpt (id_cpte,id_ag) ON UPDATE No action ON DELETE No action;
ALTER TABLE ad_gar ADD CONSTRAINT "ad_gar_devise_devise_vente_fkey" FOREIGN KEY (devise_vente,id_ag) REFERENCES devise (code_devise,id_ag) ON UPDATE No action ON DELETE No action;
--ALTER TABLE ad_grp_sol ADD CONSTRAINT "ad_grp_sol_ad_cli_id_membre" FOREIGN KEY (id_membre,id_ag) REFERENCES ad_cli (id_client,id_ag) ON UPDATE No action ON DELETE No action;

ALTER TABLE ad_gui ADD CONSTRAINT "ad_gui_ad_cpt_comptable_cpte_cpta_gui_fkey" FOREIGN KEY (cpte_cpta_gui,id_ag) REFERENCES ad_cpt_comptable (num_cpte_comptable,id_ag) ON UPDATE No action ON DELETE No action;
----
--ALTER TABLE ad_his ADD CONSTRAINT "ad_his_ad_his_ext_id_his_ext_fkey" FOREIGN KEY (id_his_ext,id_ag) REFERENCES ad_his_ext (id,id_ag) ON UPDATE No action ON DELETE No action;
ALTER TABLE ad_his ADD CONSTRAINT "ad_his_ad_cli_id_client_fkey" FOREIGN KEY (id_client,id_ag) REFERENCES ad_cli (id_client,id_ag) ON UPDATE No action ON DELETE No action;

--ALTER TABLE ad_his_ext ADD CONSTRAINT "ad_his_ext_tireur_benef_id_tireur_benef_fkey" FOREIGN KEY (id_tireur_benef,id_ag) REFERENCES tireur_benef (id,id_ag) ON UPDATE No action ON DELETE No action;
--ALTER TABLE ad_his_ext ADD CONSTRAINT "d_his_ext_ad_pers_ext_id_pers_ext_fkey" FOREIGN KEY (id_pers_ext,id_ag) REFERENCES ad_pers_ext (id_pers_ext,id_ag) ON UPDATE No action ON DELETE No action;
--ALTER TABLE ad_journaux ADD CONSTRAINT "ad_journaux_ad_cpt_comptable_num_cpte_princ_fkey" FOREIGN KEY (num_cpte_princ,id_ag) REFERENCES ad_cpt_comptable (num_cpte_comptable,id_ag) ON UPDATE No action ON DELETE No action;
--ALTER TABLE ad_journaux_cptie ADD CONSTRAINT "ad_journaux_cptie_ad_journaux_id_jou_fkey" FOREIGN KEY (id_jou,id_ag) REFERENCES ad_journaux (id_jou,id_ag) ON UPDATE No action ON DELETE No action;
--ALTER TABLE ad_journaux_cptie ADD CONSTRAINT "ad_journaux_cptie_ad_cpt_comptable_num_cpte_comptable" FOREIGN KEY (num_cpte_comptable,id_ag) REFERENCES ad_cpt_comptable (num_cpte_comptable,id_ag) ON UPDATE No action ON DELETE No action;
--ALTER TABLE ad_journaux_liaison ADD CONSTRAINT "ad_journaux_liaison_ad_journaux_id_jou1_fkey" FOREIGN KEY (id_jou1,id_ag) REFERENCES ad_journaux (id_jou,id_ag) ON UPDATE No action ON DELETE No action;
--ALTER TABLE ad_journaux_liaison ADD CONSTRAINT "ad_journaux_liaison_ad_journaux_id_jou2_fkey" FOREIGN KEY (id_jou2,id_ag) REFERENCES ad_journaux (id_jou,id_ag) ON UPDATE No action ON DELETE No action;
--ALTER TABLE ad_journaux_liaison ADD CONSTRAINT "ad_journaux_liaison_ad_cpt_comptable_num_cpte_comptable_fkey" FOREIGN KEY (num_cpte_comptable,id_ag) REFERENCES ad_cpt_comptable (num_cpte_comptable,id_ag) ON UPDATE No action ON DELETE No action;
ALTER TABLE ad_log ADD CONSTRAINT "ad_log_adsys_langues_systeme_langue_fkey" FOREIGN KEY (langue) REFERENCES adsys_langues_systeme (code) ON UPDATE No action ON DELETE No action;
ALTER TABLE ad_log ADD CONSTRAINT "ad_log_ad_uti_id_utilisateur_fkey" FOREIGN KEY (id_utilisateur,id_ag) REFERENCES ad_uti (id_utilis,id_ag) ON UPDATE No action ON DELETE No action;
ALTER TABLE ad_log ADD CONSTRAINT "ad_log_ad_gui_guichet_fkey" FOREIGN KEY (guichet,id_ag) REFERENCES ad_gui (id_gui,id_ag) ON UPDATE No action ON DELETE No action;

ALTER TABLE ad_mandat ADD CONSTRAINT "ad_mandat_ad_cpt_id_cpte_fkey" FOREIGN KEY (id_cpte,id_ag) REFERENCES ad_cpt (id_cpte,id_ag) ON UPDATE No action ON DELETE No action;
ALTER TABLE ad_mandat ADD CONSTRAINT "ad_mandat_ad_pers_ext_id_pers_ext_fkey" FOREIGN KEY (id_pers_ext,id_ag) REFERENCES ad_pers_ext (id_pers_ext,id_ag) ON UPDATE No action ON DELETE No action;
ALTER TABLE ad_mouvement ADD CONSTRAINT "ad_mouvement_ad_ecriture_id_ecriture_fkey" FOREIGN KEY (id_ecriture,id_ag) REFERENCES ad_ecriture (id_ecriture,id_ag) ON UPDATE No action ON DELETE No action;
ALTER TABLE ad_mouvement ADD CONSTRAINT "ad_mouvement_ad_cpt_comptable_compte_fkey" FOREIGN KEY (compte,id_ag) REFERENCES ad_cpt_comptable (num_cpte_comptable,id_ag) ON UPDATE No action ON DELETE No action;
ALTER TABLE ad_mouvement ADD CONSTRAINT "ad_mouvement_ad_cpt_cpte_interne_cli_fkey" FOREIGN KEY (cpte_interne_cli,id_ag) REFERENCES ad_cpt (id_cpte,id_ag) ON UPDATE No action ON DELETE No action;
ALTER TABLE ad_mouvement ADD CONSTRAINT "ad_mouvement_devise_devise_fkey" FOREIGN KEY (devise,id_ag) REFERENCES devise (code_devise,id_ag) ON UPDATE No action ON DELETE No action;
--ALTER TABLE ad_pers_ext ADD CONSTRAINT "ad_pers_ext_ad_cli_id_client_fkey" FOREIGN KEY (id_client,id_ag) REFERENCES ad_cli (id_client,id_ag) ON UPDATE No action ON DELETE No action;
--ALTER TABLE ad_pers_ext ADD CONSTRAINT "ad_pers_ext_adsys_pays_pays_fkey" FOREIGN KEY (pays,id_ag) REFERENCES adsys_pays (id_pays,id_ag) ON UPDATE No action ON DELETE No action;
--ALTER TABLE ad_pers_ext ADD CONSTRAINT "ad_pers_ext_adsys_type_piece_identite_type_piece_id_fkey" FOREIGN KEY (type_piece_id,id_ag) REFERENCES adsys_type_piece_identite (id,id_ag) ON UPDATE No action ON DELETE No action;
--ALTER TABLE ad_rel ADD CONSTRAINT "ad_rel_ad_cli_id_client_fkey" FOREIGN KEY (id_client) REFERENCES ad_cli (id_client) ON UPDATE No action ON DELETE No action;
--ALTER TABLE ad_rel ADD CONSTRAINT "ad_rel_ad_pers_ext_id_pers_ext_fkey" FOREIGN KEY (id_pers_ext) REFERENCES ad_pers_ext (id_pers_ext) ON UPDATE No action ON DELETE No action;
ALTER TABLE ad_sre ADD CONSTRAINT "ad_sre_ad_dcr_id_doss_fkey" FOREIGN KEY (id_doss,id_ag) REFERENCES ad_dcr (id_doss,id_ag) ON UPDATE No action ON DELETE No action;
ALTER TABLE ad_traductions ADD CONSTRAINT "ad_traductions_ad_str_id_str_fkey" FOREIGN KEY (id_str) REFERENCES ad_str (id_str) ON UPDATE No action ON DELETE No action;
ALTER TABLE ad_traductions ADD CONSTRAINT "ad_traductions_adsys_langues_systeme_langue_fkey" FOREIGN KEY (langue) REFERENCES adsys_langues_systeme (code) ON UPDATE No action ON DELETE No action;
--ALTER TABLE adsys_banque ADD CONSTRAINT "adsys_banque_adsys_pays_pays_fkey" FOREIGN KEY (pays,id_ag) REFERENCES adsys_pays (id_pays,id_ag) ON UPDATE No action ON DELETE No action;
ALTER TABLE adsys_correspondant ADD CONSTRAINT "adsys_correspondant_adsys_banque_id_banque_fkey" FOREIGN KEY (id_banque,id_ag) REFERENCES adsys_banque (id_banque,id_ag) ON UPDATE No action ON DELETE No action;
ALTER TABLE adsys_correspondant ADD CONSTRAINT "adsys_correspondant_ad_cpt_comptable_cpte_bqe_fkey" FOREIGN KEY (cpte_bqe,id_ag) REFERENCES ad_cpt_comptable (num_cpte_comptable,id_ag) ON UPDATE No action ON DELETE No action;
ALTER TABLE adsys_correspondant ADD CONSTRAINT "adsys_correspondant_ad_cpt_comptable_cpte_ordre_deb_fkey" FOREIGN KEY (cpte_ordre_deb,id_ag) REFERENCES ad_cpt_comptable (num_cpte_comptable,id_ag) ON UPDATE No action ON DELETE No action;
ALTER TABLE adsys_correspondant ADD CONSTRAINT "adsys_correspondant_ad_cpt_comptable_cpte_ordre_cred_fkey" FOREIGN KEY (cpte_ordre_cred,id_ag) REFERENCES ad_cpt_comptable (num_cpte_comptable,id_ag) ON UPDATE No action ON DELETE No action;

ALTER TABLE adsys_etat_credit_cptes ADD CONSTRAINT "adsys_etat_credit__adsys_etat_credits_cptes_id_etat_credit_fkey" FOREIGN KEY (id_etat_credit,id_ag) REFERENCES adsys_etat_credits (id,id_ag) ON UPDATE No action ON DELETE No action;
ALTER TABLE adsys_etat_credit_cptes ADD CONSTRAINT "adsys_etat_credit_cptes_ad_cpt_comptable_num_cpte_comptable_fkey" FOREIGN KEY (num_cpte_comptable,id_ag) REFERENCES ad_cpt_comptable( num_cpte_comptable,id_ag) ON UPDATE No action ON DELETE No action;
ALTER TABLE adsys_etat_credit_cptes ADD CONSTRAINT "adsys_etat_credit_cptes_adsys_produit_credit_id_prod_cre_fkey" FOREIGN KEY (id_prod_cre,id_ag) REFERENCES adsys_produit_credit( id,id_ag) ON UPDATE No action ON DELETE No action;
ALTER TABLE adsys_etat_credit_cptes ADD CONSTRAINT "adsys_etat_credit_cptes_ad_cpt_comptable_cpte_provision_credit_fkey" FOREIGN KEY (cpte_provision_credit,id_ag) REFERENCES ad_cpt_comptable( num_cpte_comptable,id_ag) ON UPDATE No action ON DELETE No action;
ALTER TABLE adsys_etat_credit_cptes ADD CONSTRAINT "adsys_etat_credit_cptes_ad_cpt_comptable_cpte_provision_debit_fkey" FOREIGN KEY (cpte_provision_debit,id_ag) REFERENCES ad_cpt_comptable( num_cpte_comptable,id_ag) ON UPDATE No action ON DELETE No action;
ALTER TABLE adsys_etat_credit_cptes ADD CONSTRAINT "adsys_etat_credit_cptes_ad_cpt_comptable_cpte_reprise_prov_fkey" FOREIGN KEY (cpte_reprise_prov,id_ag) REFERENCES ad_cpt_comptable( num_cpte_comptable,id_ag) ON UPDATE No action ON DELETE No action;


ALTER TABLE adsys_etat_credits ADD CONSTRAINT "adsys_etat_credits_adsys_etat_credits_id_etat_prec_fkey" FOREIGN KEY (id_etat_prec,id_ag) REFERENCES adsys_etat_credits (id,id_ag) ON UPDATE No action ON DELETE No action;
----------
ALTER TABLE adsys_langues_systeme ADD CONSTRAINT "adsys_langues_systeme_ad_str_langue_fkey" FOREIGN KEY (langue) REFERENCES ad_str (id_str) ON UPDATE No action ON DELETE No action;

ALTER TABLE adsys_produit_credit ADD CONSTRAINT "adsys_produit_credit_ad_cpt_comptable_cpte_cpta_prod_cr_int_fkey" FOREIGN KEY (cpte_cpta_prod_cr_int,id_ag) REFERENCES ad_cpt_comptable (num_cpte_comptable,id_ag) ON UPDATE No action ON DELETE No action;
ALTER TABLE adsys_produit_credit ADD CONSTRAINT "adsys_produit_credit_ad_cpt_comptable_cpte_cpta_prod_cr_gar_fkey" FOREIGN KEY (cpte_cpta_prod_cr_gar,id_ag) REFERENCES ad_cpt_comptable (num_cpte_comptable,id_ag) ON UPDATE No action ON DELETE No action;
ALTER TABLE adsys_produit_credit ADD CONSTRAINT "adsys_produit_credit_ad_cpt_comptable_cpte_cpta_prod_cr_pen_fkey" FOREIGN KEY (cpte_cpta_prod_cr_pen,id_ag) REFERENCES ad_cpt_comptable (num_cpte_comptable,id_ag) ON UPDATE No action ON DELETE No action;
ALTER TABLE adsys_produit_credit ADD CONSTRAINT "adsys_produit_credit_devise_devise_fkey" FOREIGN KEY (devise,id_ag) REFERENCES devise (code_devise,id_ag) ON UPDATE No action ON DELETE No action;
--ALTER TABLE adsys_produit_epargne ADD CONSTRAINT "adsys_produit_epargne_ad_cpt_comptable_cpte_cpta_prod_ep_fkey" FOREIGN KEY (cpte_cpta_prod_ep,id_ag) REFERENCES ad_cpt_comptable (num_cpte_comptable,id_ag) ON UPDATE No action ON DELETE No action;
--ALTER TABLE adsys_produit_epargne ADD CONSTRAINT "adsys_produit_epargne_ad_cpt_comptable_cpte_cpta_prod_ep_int_fkey" FOREIGN KEY (cpte_cpta_prod_ep_int,id_ag) REFERENCES ad_cpt_comptable (num_cpte_comptable,id_ag) ON UPDATE No action ON DELETE No action;
--ALTER TABLE adsys_produit_epargne ADD CONSTRAINT "adsys_produit_epargne_devise_devise_fkey" FOREIGN KEY (devise,id_ag) REFERENCES devise (code_devise,id_ag) ON UPDATE No action ON DELETE No action;
--ALTER TABLE adsys_type_piece_identite ADD CONSTRAINT "adsys_type_piece_identite_ad_str_libel_fkey" FOREIGN KEY (libel) REFERENCES ad_str (id_str) ON UPDATE No action ON DELETE No action;
ALTER TABLE adsys_types_billets ADD CONSTRAINT "adsys_types_billets_devise_devise_fkey" FOREIGN KEY (devise,id_ag) REFERENCES devise (code_devise,id_ag) ON UPDATE No action ON DELETE No action;
ALTER TABLE attentes ADD CONSTRAINT "attentes_adsys_correspondant_id_correspondant_fkey" FOREIGN KEY (id_correspondant,id_ag) REFERENCES adsys_correspondant (id,id_ag) ON UPDATE No action ON DELETE No action;
ALTER TABLE attentes ADD CONSTRAINT "attentes_tireur_benef_id_ext_benef_fkey" FOREIGN KEY (id_ext_benef,id_ag) REFERENCES tireur_benef (id,id_ag) ON UPDATE No action ON DELETE No action;
ALTER TABLE attentes ADD CONSTRAINT "attentes_ad_cpt_id_cpt_benef_fkey" FOREIGN KEY (id_cpt_benef,id_ag) REFERENCES ad_cpt (id_cpte,id_ag) ON UPDATE No action ON DELETE No action;
ALTER TABLE attentes ADD CONSTRAINT "attentes_tireur_benef_id_ext_ordre_fkey" FOREIGN KEY (id_ext_ordre,id_ag) REFERENCES tireur_benef (id,id_ag) ON UPDATE No action ON DELETE No action;
ALTER TABLE attentes ADD CONSTRAINT "attentes_ad_cpt_id_cpt_ordre_fkey" FOREIGN KEY (id_cpt_ordre,id_ag) REFERENCES ad_cpt (id_cpte,id_ag) ON UPDATE No action ON DELETE No action;
ALTER TABLE attentes ADD CONSTRAINT "attentes_devise_code_devise_fkey" FOREIGN KEY (devise,id_ag) REFERENCES devise (code_devise,id_ag) ON UPDATE No action ON DELETE No action;
ALTER TABLE attentes ADD CONSTRAINT "attentes_adsys_banque_id_banque_fkey" FOREIGN KEY (id_banque,id_ag) REFERENCES adsys_banque (id_banque,id_ag) ON UPDATE No action ON DELETE No action;
ALTER TABLE d_tableliste ADD CONSTRAINT "d_tableliste_tableliste_tablen_fkey" FOREIGN KEY (tablen) REFERENCES tableliste (ident) ON UPDATE No action ON DELETE No action;
ALTER TABLE d_tableliste ADD CONSTRAINT "d_tableliste_ad_str_nchmpl_fkey" FOREIGN KEY (nchmpl) REFERENCES ad_str (id_str) ON UPDATE No action ON DELETE No action;
--ALTER TABLE devise ADD CONSTRAINT "devise_ad_cpt_comptable_cpte_produit_commission_fkey" FOREIGN KEY (cpte_produit_commission,id_ag) REFERENCES ad_cpt_comptable (num_cpte_comptable,id_ag) ON UPDATE No action ON DELETE No action;
--ALTER TABLE devise ADD CONSTRAINT "devise_ad_cpt_comptable_cpte_produit_taux_fkey" FOREIGN KEY (cpte_produit_taux,id_ag) REFERENCES ad_cpt_comptable (num_cpte_comptable,id_ag) ON UPDATE No action ON DELETE No action;
--ALTER TABLE devise ADD CONSTRAINT "devise_ad_cpt_comptable_cpte_perte_taux_fkey" FOREIGN KEY (cpte_perte_taux,id_ag) REFERENCES ad_cpt_comptable (num_cpte_comptable,id_ag) ON UPDATE No action ON DELETE No action;
ALTER TABLE ecrans ADD CONSTRAINT "ecrans_menus_nom_menu_fkey" FOREIGN KEY (nom_menu) REFERENCES menus (nom_menu) ON UPDATE No action ON DELETE No action;
ALTER TABLE menus ADD CONSTRAINT "menus_ad_str_libel_menu_fkey" FOREIGN KEY (libel_menu) REFERENCES ad_str (id_str) ON UPDATE No action ON DELETE No action;
ALTER TABLE tableliste ADD CONSTRAINT "tableliste_ad_str_noml_fkey" FOREIGN KEY (noml) REFERENCES ad_str (id_str) ON UPDATE No action ON DELETE No action;
--ALTER TABLE tireur_benef ADD CONSTRAINT "tireur_benef_adsys_pays_pays_fkey" FOREIGN KEY (pays,id_ag) REFERENCES adsys_pays (id_pays,id_ag) ON UPDATE No action ON DELETE No action;
--ALTER TABLE tireur_benef ADD CONSTRAINT "tireur_benef_adsys_banque_id_banque_fkey" FOREIGN KEY (id_banque,id_ag) REFERENCES adsys_banque (id_banque,id_ag) ON UPDATE No action ON DELETE No action;

