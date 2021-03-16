------------------------
ALTER TABLE ad_agc ADD  FOREIGN KEY (exercice,id_ag) REFERENCES ad_exercices_compta (id_exo_compta,id_ag) ON UPDATE No action ON DELETE No action;
ALTER TABLE ad_agc ADD   FOREIGN KEY (num_cpte_resultat,id_ag) REFERENCES ad_cpt_comptable (num_cpte_comptable,id_ag) ON UPDATE No action ON DELETE No action;
ALTER TABLE ad_agc ADD  FOREIGN KEY (cpte_cpta_coffre,id_ag) REFERENCES ad_cpt_comptable (num_cpte_comptable,id_ag) ON UPDATE No action ON DELETE No action;
ALTER TABLE ad_agc ADD  FOREIGN KEY (cpte_position_change,id_ag) REFERENCES ad_cpt_comptable (num_cpte_comptable,id_ag) ON UPDATE No action ON DELETE No action;
ALTER TABLE ad_agc ADD   FOREIGN KEY (cpte_contreval_position_change,id_ag) REFERENCES ad_cpt_comptable (num_cpte_comptable,id_ag) ON UPDATE No action ON DELETE No action;
ALTER TABLE ad_agc ADD  FOREIGN KEY (cpte_variation_taux_deb,id_ag) REFERENCES ad_cpt_comptable (num_cpte_comptable,id_ag) ON UPDATE No action ON DELETE No action;
ALTER TABLE ad_agc ADD   FOREIGN KEY (cpte_variation_taux_cred,id_ag) REFERENCES ad_cpt_comptable (num_cpte_comptable,id_ag) ON UPDATE No action ON DELETE No action;
ALTER TABLE ad_agc ADD FOREIGN KEY (num_cpte_tch,id_ag) REFERENCES ad_cpt_comptable (num_cpte_comptable,id_ag) ON UPDATE No action ON DELETE No action;
ALTER TABLE ad_agc ADD   FOREIGN KEY (langue_systeme_dft) REFERENCES adsys_langues_systeme (code) ON UPDATE No action ON DELETE No action;
ALTER TABLE ad_biens ADD  FOREIGN KEY (id_client,id_ag) REFERENCES ad_cli (id_client,id_ag) ON UPDATE No action ON DELETE No action;
ALTER TABLE ad_biens ADD   FOREIGN KEY (type_bien,id_ag) REFERENCES adsys_types_biens (id,id_ag) ON UPDATE No action ON DELETE No action;
ALTER TABLE ad_biens ADD  FOREIGN KEY (devise_valeur,id_ag) REFERENCES devise (code_devise,id_ag) ON UPDATE No action ON DELETE No action;
ALTER TABLE ad_brouillard ADD   FOREIGN KEY (compte,id_ag) REFERENCES ad_cpt_comptable (num_cpte_comptable,id_ag) ON UPDATE No action ON DELETE No action;
ALTER TABLE ad_brouillard ADD  FOREIGN KEY (cpte_interne_cli,id_ag) REFERENCES ad_cpt (id_cpte,id_ag) ON UPDATE No action ON DELETE No action;
ALTER TABLE ad_brouillard ADD   FOREIGN KEY (devise,id_ag) REFERENCES devise (code_devise) ON UPDATE No action ON DELETE No action;
ALTER TABLE ad_brouillard ADD  FOREIGN KEY (id_jou,id_ag) REFERENCES ad_journaux (id_jou,id_ag) ON UPDATE No action ON DELETE No action;
ALTER TABLE ad_brouillard ADD  FOREIGN KEY (id_exo,id_ag) REFERENCES ad_exercices_compta (id_exo_compta,id_ag) ON UPDATE No action ON DELETE No action;
ALTER TABLE ad_cli ADD FOREIGN KEY (pays,id_ag) REFERENCES adsys_pays (id_pays,id_ag) ON UPDATE No action ON DELETE No action;
ALTER TABLE ad_cli ADD  FOREIGN KEY (pp_pays_naiss,id_ag) REFERENCES adsys_pays (id_pays,id_ag) ON UPDATE No action ON DELETE No action;
ALTER TABLE ad_cli ADD  FOREIGN KEY (pp_nationalite,id_ag) REFERENCES adsys_pays (id_pays,id_ag) ON UPDATE No action ON DELETE No action;
ALTER TABLE ad_cli ADD  FOREIGN KEY (langue_correspondance) REFERENCES adsys_langues_systeme (code) ON UPDATE No action ON DELETE No action;
ALTER TABLE ad_clotures_periode ADD   FOREIGN KEY (id_exo,id_ag) REFERENCES ad_exercices_compta (id_exo_compta,id_ag) ON UPDATE No action ON DELETE No action;
--ALTER TABLE ad_comptable_attente ADD  FOREIGN KEY (cpte_interne_cli,id_ag) REFERENCES ad_cpt (id_cpte,id_ag) ON UPDATE No action ON DELETE No action;
ALTER TABLE ad_cpt ADD  FOREIGN KEY (id_titulaire,id_ag) REFERENCES ad_cli (id_client,id_ag) ON UPDATE No action ON DELETE No action;
ALTER TABLE ad_cpt ADD   FOREIGN KEY (id_prod,id_ag) REFERENCES adsys_produit_epargne (id,id_ag) ON UPDATE No action ON DELETE No action;
ALTER TABLE ad_cpt ADD  FOREIGN KEY (devise,id_ag) REFERENCES devise (code_devise,id_ag) ON UPDATE No action ON DELETE No action;
ALTER TABLE ad_cpt ADD FOREIGN KEY (cpte_virement_clot,id_ag) REFERENCES ad_cpt (id_cpte,id_ag) ON UPDATE No action ON DELETE No action;
ALTER TABLE ad_cpt_comptable ADD  FOREIGN KEY (classe_compta,id_ag) REFERENCES ad_classes_compta (numero_classe,id_ag) ON UPDATE No action ON DELETE No action;
ALTER TABLE ad_cpt_comptable ADD  FOREIGN KEY (cpte_centralise,id_ag) REFERENCES ad_cpt_comptable (num_cpte_comptable,id_ag) ON UPDATE No action ON DELETE No action;
ALTER TABLE ad_cpt_comptable ADD  FOREIGN KEY (cpte_provision,id_ag) REFERENCES ad_cpt_comptable (num_cpte_comptable,id_ag) ON UPDATE No action ON DELETE No action;
ALTER TABLE ad_cpt_comptable ADD  FOREIGN KEY (id_ag) REFERENCES ad_agc (id_ag) ON UPDATE No action ON DELETE No action;
ALTER TABLE ad_cpt_ope_cptes ADD  FOREIGN KEY (type_operation,id_ag) REFERENCES ad_cpt_ope (type_operation,id_ag) ON UPDATE No action ON DELETE No action;
ALTER TABLE ad_cpt_ope_cptes ADD  FOREIGN KEY (num_cpte,id_ag) REFERENCES ad_cpt_comptable (num_cpte_comptable,id_ag) ON UPDATE No action ON DELETE No action;
ALTER TABLE ad_cpt_soldes ADD FOREIGN KEY (num_cpte_comptable_solde,id_ag) REFERENCES ad_cpt_comptable (num_cpte_comptable,id_ag) ON UPDATE No action ON DELETE No action;
ALTER TABLE ad_cpt_soldes ADD FOREIGN KEY (id_cloture,id_ag) REFERENCES ad_clotures_periode (id_clot_per,id_ag) ON UPDATE No action ON DELETE No action;
ALTER TABLE ad_dcr ADD   FOREIGN KEY (id_prod,id_ag) REFERENCES adsys_produit_credit (id,id_ag) ON UPDATE No action ON DELETE No action;
ALTER TABLE ad_dcr ADD   FOREIGN KEY (cpt_gar_encours,id_ag) REFERENCES ad_cpt (id_cpte,id_ag) ON UPDATE No action ON DELETE No action;
ALTER TABLE ad_dcr ADD  FOREIGN KEY (cpt_liaison,id_ag) REFERENCES ad_cpt (id_cpte,id_ag) ON UPDATE No action ON DELETE No action;
ALTER TABLE ad_dcr ADD   FOREIGN KEY (cre_id_cpte,id_ag) REFERENCES ad_cpt (id_cpte,id_ag) ON UPDATE No action ON DELETE No action;
--ALTER TABLE ad_dcr ADD  FOREIGN KEY (cre_prelev_frais_doss,id_ag) REFERENCES ad_cpt (id_cpte,id_ag) ON UPDATE No action ON DELETE No action;
ALTER TABLE ad_dcr ADD   FOREIGN KEY (id_dcr_grp_sol,id_ag) REFERENCES ad_dcr_grp_sol (id,id_ag) ON UPDATE No action ON DELETE No action DEFERRABLE INITIALLY DEFERRED;
ALTER TABLE ad_dcr ADD  FOREIGN KEY (cre_etat,id_ag) REFERENCES adsys_etat_credits (id,id_ag) ON UPDATE No action ON DELETE No action;
ALTER TABLE ad_dcr ADD  FOREIGN KEY (id_agent_gest,id_ag) REFERENCES ad_uti (id_utilis,id_ag) ON UPDATE No action ON DELETE No action;
ALTER TABLE ad_dcr ADD   FOREIGN KEY (obj_dem,id_ag) REFERENCES adsys_objets_credits (id,id_ag) ON UPDATE No action ON DELETE No action;
ALTER TABLE ad_dcr_grp_sol ADD   FOREIGN KEY (id_dcr_grp_sol,id_ag) REFERENCES ad_dcr (id_doss,id_ag) ON UPDATE No action ON DELETE No action;
ALTER TABLE ad_dcr_grp_sol ADD   FOREIGN KEY (id_membre,id_ag) REFERENCES ad_cli (id_client,id_ag) ON UPDATE No action ON DELETE No action;
ALTER TABLE ad_dcr_grp_sol ADD   FOREIGN KEY (obj_dem,id_ag) REFERENCES adsys_objets_credits (id,id_ag) ON UPDATE No action ON DELETE No action;

ALTER TABLE ad_ecriture ADD  FOREIGN KEY (id_his,id_ag) REFERENCES ad_his (id_his,id_ag) ON UPDATE No action ON DELETE No action;
ALTER TABLE ad_ecriture ADD  FOREIGN KEY (id_jou,id_ag) REFERENCES ad_journaux (id_jou,id_ag) ON UPDATE No action ON DELETE No action;
ALTER TABLE ad_ecriture ADD  FOREIGN KEY (id_exo,id_ag) REFERENCES ad_exercices_compta (id_exo_compta,id_ag) ON UPDATE No action ON DELETE No action;
ALTER TABLE ad_etr ADD   FOREIGN KEY (id_doss,id_ag) REFERENCES ad_dcr (id_doss,id_ag) ON UPDATE No action ON DELETE No action;

ALTER TABLE ad_extrait_cpte ADD   FOREIGN KEY (id_his,id_ag) REFERENCES ad_his (id_his,id_ag) ON UPDATE No action ON DELETE No action;
ALTER TABLE ad_extrait_cpte ADD   FOREIGN KEY (id_cpte,id_ag) REFERENCES ad_cpt (id_cpte,id_ag) ON UPDATE No action ON DELETE No action;
ALTER TABLE ad_extrait_cpte ADD   FOREIGN KEY (cptie_num_cpte,id_ag) REFERENCES ad_cpt_comptable (num_cpte_comptable,id_ag) ON UPDATE No action ON DELETE No action;
ALTER TABLE ad_extrait_cpte ADD   FOREIGN KEY (eft_id_mvt,id_ag) REFERENCES ad_mouvement (id_mouvement,id_ag) ON UPDATE No action ON DELETE No action;

ALTER TABLE ad_extrait_cpte ADD   FOREIGN KEY (cptie_pays,id_ag) REFERENCES adsys_pays (id_pays,id_ag) ON UPDATE No action ON DELETE No action;
ALTER TABLE ad_extrait_cpte ADD   FOREIGN KEY (cptie_devise,id_ag) REFERENCES devise (code_devise,id_ag) ON UPDATE No action ON DELETE No action;
ALTER TABLE ad_extrait_cpte ADD   FOREIGN KEY (eft_id_client,id_ag) REFERENCES ad_cli (id_client,id_ag) ON UPDATE No action ON DELETE No action;
ALTER TABLE ad_frais_attente ADD   FOREIGN KEY (id_cpte,id_ag) REFERENCES ad_cpt (id_cpte,id_ag) ON UPDATE No action ON DELETE No action;
ALTER TABLE ad_frais_attente ADD   FOREIGN KEY (type_frais,id_ag) REFERENCES ad_cpt_ope (type_operation,id_ag) ON UPDATE No action ON DELETE No action;
ALTER TABLE ad_gar ADD   FOREIGN KEY (id_doss,id_ag) REFERENCES ad_dcr (id_doss,id_ag) ON UPDATE No action ON DELETE No action;
ALTER TABLE ad_gar ADD   FOREIGN KEY (gar_mat_id_bien,id_ag) REFERENCES ad_biens (id_bien,id_ag) ON UPDATE No action ON DELETE No action;
ALTER TABLE ad_gar ADD   FOREIGN KEY (gar_num_id_cpte_prelev,id_ag) REFERENCES ad_cpt (id_cpte,id_ag) ON UPDATE No action ON DELETE No action;
ALTER TABLE ad_gar ADD   FOREIGN KEY (gar_num_id_cpte_nantie,id_ag) REFERENCES ad_cpt (id_cpte,id_ag) ON UPDATE No action ON DELETE No action;
ALTER TABLE ad_gar ADD   FOREIGN KEY (devise_vente,id_ag) REFERENCES devise (code_devise,id_ag) ON UPDATE No action ON DELETE No action;
ALTER TABLE ad_grp_sol ADD   FOREIGN KEY (id_membre,id_ag) REFERENCES ad_cli (id_client,id_ag) ON UPDATE No action ON DELETE No action;

ALTER TABLE ad_gui ADD   FOREIGN KEY (cpte_cpta_gui,id_ag) REFERENCES ad_cpt_comptable (num_cpte_comptable,id_ag) ON UPDATE No action ON DELETE No action;

ALTER TABLE ad_his ADD   FOREIGN KEY (id_his_ext,id_ag) REFERENCES ad_his_ext (id,id_ag) ON UPDATE No action ON DELETE No action;
ALTER TABLE ad_his ADD   FOREIGN KEY (id_client,id_ag) REFERENCES ad_cli (id_client,id_ag) ON UPDATE No action ON DELETE No action;

ALTER TABLE ad_his_ext ADD   FOREIGN KEY (id_tireur_benef,id_ag) REFERENCES tireur_benef (id,id_ag) ON UPDATE No action ON DELETE No action;
ALTER TABLE ad_his_ext ADD   FOREIGN KEY (id_pers_ext,id_ag) REFERENCES ad_pers_ext (id_pers_ext,id_ag) ON UPDATE No action ON DELETE No action;
ALTER TABLE ad_journaux ADD   FOREIGN KEY (num_cpte_princ,id_ag) REFERENCES ad_cpt_comptable (num_cpte_comptable,id_ag) ON UPDATE No action ON DELETE No action;
ALTER TABLE ad_journaux_cptie ADD   FOREIGN KEY (id_jou,id_ag) REFERENCES ad_journaux (id_jou,id_ag) ON UPDATE No action ON DELETE No action;
ALTER TABLE ad_journaux_cptie ADD   FOREIGN KEY (num_cpte_comptable,id_ag) REFERENCES ad_cpt_comptable (num_cpte_comptable,id_ag) ON UPDATE No action ON DELETE No action;
ALTER TABLE ad_journaux_liaison ADD   FOREIGN KEY (id_jou1,id_ag) REFERENCES ad_journaux (id_jou,id_ag) ON UPDATE No action ON DELETE No action;
ALTER TABLE ad_journaux_liaison ADD   FOREIGN KEY (id_jou2,id_ag) REFERENCES ad_journaux (id_jou,id_ag) ON UPDATE No action ON DELETE No action;
ALTER TABLE ad_journaux_liaison ADD   FOREIGN KEY (num_cpte_comptable,id_ag) REFERENCES ad_cpt_comptable (num_cpte_comptable,id_ag) ON UPDATE No action ON DELETE No action;
ALTER TABLE ad_log ADD   FOREIGN KEY (langue) REFERENCES adsys_langues_systeme (code) ON UPDATE No action ON DELETE No action;
ALTER TABLE ad_log ADD   FOREIGN KEY (id_utilisateur,id_ag) REFERENCES ad_uti (id_utilis,id_ag) ON UPDATE No action ON DELETE No action;
ALTER TABLE ad_log ADD   FOREIGN KEY (guichet,id_ag) REFERENCES ad_gui (id_gui,id_ag) ON UPDATE No action ON DELETE No action;

ALTER TABLE ad_mandat ADD   FOREIGN KEY (id_cpte,id_ag) REFERENCES ad_cpt (id_cpte,id_ag) ON UPDATE No action ON DELETE No action;
ALTER TABLE ad_mandat ADD   FOREIGN KEY (id_pers_ext,id_ag) REFERENCES ad_pers_ext (id_pers_ext,id_ag) ON UPDATE No action ON DELETE No action;
ALTER TABLE ad_mouvement ADD   FOREIGN KEY (id_ecriture,id_ag) REFERENCES ad_ecriture (id_ecriture,id_ag) ON UPDATE No action ON DELETE No action;
ALTER TABLE ad_mouvement ADD   FOREIGN KEY (compte,id_ag) REFERENCES ad_cpt_comptable (num_cpte_comptable,id_ag) ON UPDATE No action ON DELETE No action;
ALTER TABLE ad_mouvement ADD   FOREIGN KEY (cpte_interne_cli,id_ag) REFERENCES ad_cpt (id_cpte,id_ag) ON UPDATE No action ON DELETE No action;
ALTER TABLE ad_mouvement ADD   FOREIGN KEY (devise,id_ag) REFERENCES devise (code_devise,id_ag) ON UPDATE No action ON DELETE No action;
ALTER TABLE ad_pers_ext ADD   FOREIGN KEY (id_client,id_ag) REFERENCES ad_cli (id_client,id_ag) ON UPDATE No action ON DELETE No action;
ALTER TABLE ad_pers_ext ADD   FOREIGN KEY (pays,id_ag) REFERENCES adsys_pays (id_pays,id_ag) ON UPDATE No action ON DELETE No action;
ALTER TABLE ad_pers_ext ADD   FOREIGN KEY (type_piece_id,id_ag) REFERENCES adsys_type_piece_identite (id,id_ag) ON UPDATE No action ON DELETE No action;
ALTER TABLE ad_rel ADD   FOREIGN KEY (id_client,id_ag) REFERENCES ad_cli (id_client,id_ag) ON UPDATE No action ON DELETE No action;
ALTER TABLE ad_rel ADD   FOREIGN KEY (id_pers_ext,id_ag) REFERENCES ad_pers_ext (id_pers_ext,id_ag) ON UPDATE No action ON DELETE No action;
ALTER TABLE ad_sre ADD   FOREIGN KEY (id_doss,id_ag) REFERENCES ad_dcr (id_doss,id_ag) ON UPDATE No action ON DELETE No action;
ALTER TABLE ad_traductions ADD   FOREIGN KEY (id_str) REFERENCES ad_str (id_str) ON UPDATE No action ON DELETE No action;
ALTER TABLE ad_traductions ADD   FOREIGN KEY (langue) REFERENCES adsys_langues_systeme (code) ON UPDATE No action ON DELETE No action;
ALTER TABLE adsys_banque ADD   FOREIGN KEY (pays,id_ag) REFERENCES adsys_pays (id_pays,id_ag) ON UPDATE No action ON DELETE No action;
ALTER TABLE adsys_correspondant ADD   FOREIGN KEY (id_banque,id_ag) REFERENCES adsys_banque (id_banque,id_ag) ON UPDATE No action ON DELETE No action;
ALTER TABLE adsys_correspondant ADD   FOREIGN KEY (cpte_bqe,id_ag) REFERENCES ad_cpt_comptable (num_cpte_comptable,id_ag) ON UPDATE No action ON DELETE No action;
ALTER TABLE adsys_correspondant ADD   FOREIGN KEY (cpte_ordre_deb,id_ag) REFERENCES ad_cpt_comptable (num_cpte_comptable,id_ag) ON UPDATE No action ON DELETE No action;
ALTER TABLE adsys_correspondant ADD   FOREIGN KEY (cpte_ordre_cred,id_ag) REFERENCES ad_cpt_comptable (num_cpte_comptable,id_ag) ON UPDATE No action ON DELETE No action;

ALTER TABLE adsys_etat_credit_cptes ADD   FOREIGN KEY (id_etat_credit,id_ag) REFERENCES adsys_etat_credits (id,id_ag) ON UPDATE No action ON DELETE No action;
ALTER TABLE adsys_etat_credit_cptes ADD   FOREIGN KEY (num_cpte_comptable,id_ag) REFERENCES ad_cpt_comptable( num_cpte_comptable,id_ag) ON UPDATE No action ON DELETE No action;
ALTER TABLE adsys_etat_credit_cptes ADD   FOREIGN KEY (id_prod_cre,id_ag) REFERENCES adsys_produit_credit( id,id_ag) ON UPDATE No action ON DELETE No action;
ALTER TABLE adsys_etat_credit_cptes ADD   FOREIGN KEY (cpte_provision_credit,id_ag) REFERENCES ad_cpt_comptable( num_cpte_comptable,id_ag) ON UPDATE No action ON DELETE No action;
ALTER TABLE adsys_etat_credit_cptes ADD   FOREIGN KEY (cpte_provision_debit,id_ag) REFERENCES ad_cpt_comptable( num_cpte_comptable,id_ag) ON UPDATE No action ON DELETE No action;
ALTER TABLE adsys_etat_credit_cptes ADD   FOREIGN KEY (cpte_reprise_prov,id_ag) REFERENCES ad_cpt_comptable( num_cpte_comptable,id_ag) ON UPDATE No action ON DELETE No action;


ALTER TABLE adsys_etat_credits ADD   FOREIGN KEY (id_etat_prec,id_ag) REFERENCES adsys_etat_credits (id,id_ag) ON UPDATE No action ON DELETE No action;

ALTER TABLE adsys_langues_systeme ADD   FOREIGN KEY (langue) REFERENCES ad_str (id_str) ON UPDATE No action ON DELETE No action;

ALTER TABLE adsys_produit_credit ADD   FOREIGN KEY (cpte_cpta_prod_cr_int,id_ag) REFERENCES ad_cpt_comptable (num_cpte_comptable,id_ag) ON UPDATE No action ON DELETE No action;
ALTER TABLE adsys_produit_credit ADD   FOREIGN KEY (cpte_cpta_prod_cr_gar,id_ag) REFERENCES ad_cpt_comptable (num_cpte_comptable,id_ag) ON UPDATE No action ON DELETE No action;
ALTER TABLE adsys_produit_credit ADD   FOREIGN KEY (cpte_cpta_prod_cr_pen,id_ag) REFERENCES ad_cpt_comptable (num_cpte_comptable,id_ag) ON UPDATE No action ON DELETE No action;
ALTER TABLE adsys_produit_credit ADD   FOREIGN KEY (devise,id_ag) REFERENCES devise (code_devise,id_ag) ON UPDATE No action ON DELETE No action;
ALTER TABLE adsys_produit_epargne ADD   FOREIGN KEY (cpte_cpta_prod_ep,id_ag) REFERENCES ad_cpt_comptable (num_cpte_comptable,id_ag) ON UPDATE No action ON DELETE No action;
ALTER TABLE adsys_produit_epargne ADD   FOREIGN KEY (cpte_cpta_prod_ep_int,id_ag) REFERENCES ad_cpt_comptable (num_cpte_comptable,id_ag) ON UPDATE No action ON DELETE No action;
ALTER TABLE adsys_produit_epargne ADD   FOREIGN KEY (devise,id_ag) REFERENCES devise (code_devise,id_ag) ON UPDATE No action ON DELETE No action;
ALTER TABLE adsys_type_piece_identite ADD   FOREIGN KEY (libel) REFERENCES ad_str (id_str) ON UPDATE No action ON DELETE No action;
ALTER TABLE adsys_types_billets ADD   FOREIGN KEY (devise,id_ag) REFERENCES devise (code_devise,id_ag) ON UPDATE No action ON DELETE No action;
ALTER TABLE attentes ADD   FOREIGN KEY (id_correspondant,id_ag) REFERENCES adsys_correspondant (id,id_ag) ON UPDATE No action ON DELETE No action;
ALTER TABLE attentes ADD   FOREIGN KEY (id_ext_benef,id_ag) REFERENCES tireur_benef (id,id_ag) ON UPDATE No action ON DELETE No action;
ALTER TABLE attentes ADD   FOREIGN KEY (id_cpt_benef,id_ag) REFERENCES ad_cpt (id_cpte,id_ag) ON UPDATE No action ON DELETE No action;
ALTER TABLE attentes ADD   FOREIGN KEY (id_ext_ordre,id_ag) REFERENCES tireur_benef (id,id_ag) ON UPDATE No action ON DELETE No action;
ALTER TABLE attentes ADD   FOREIGN KEY (id_cpt_ordre,id_ag) REFERENCES ad_cpt (id_cpte,id_ag) ON UPDATE No action ON DELETE No action;
ALTER TABLE attentes ADD   FOREIGN KEY (devise,id_ag) REFERENCES devise (code_devise,id_ag) ON UPDATE No action ON DELETE No action;
ALTER TABLE attentes ADD   FOREIGN KEY (id_banque,id_ag) REFERENCES adsys_banque (id_banque,id_ag) ON UPDATE No action ON DELETE No action;
ALTER TABLE d_tableliste ADD   FOREIGN KEY (tablen) REFERENCES tableliste (ident) ON UPDATE No action ON DELETE No action;
ALTER TABLE d_tableliste ADD   FOREIGN KEY (nchmpl) REFERENCES ad_str (id_str) ON UPDATE No action ON DELETE No action;
ALTER TABLE devise ADD   FOREIGN KEY (cpte_produit_commission,id_ag) REFERENCES ad_cpt_comptable (num_cpte_comptable,id_ag) ON UPDATE No action ON DELETE No action;
ALTER TABLE devise ADD   FOREIGN KEY (cpte_produit_taux,id_ag) REFERENCES ad_cpt_comptable (num_cpte_comptable,id_ag) ON UPDATE No action ON DELETE No action;
ALTER TABLE devise ADD   FOREIGN KEY (cpte_perte_taux,id_ag) REFERENCES ad_cpt_comptable (num_cpte_comptable,id_ag) ON UPDATE No action ON DELETE No action;
ALTER TABLE ecrans ADD   FOREIGN KEY (nom_menu) REFERENCES menus (nom_menu) ON UPDATE No action ON DELETE No action;
ALTER TABLE menus ADD   FOREIGN KEY (libel_menu) REFERENCES ad_str (id_str) ON UPDATE No action ON DELETE No action;
ALTER TABLE tableliste ADD   FOREIGN KEY (noml) REFERENCES ad_str (id_str) ON UPDATE No action ON DELETE No action;
ALTER TABLE tireur_benef ADD   FOREIGN KEY (pays,id_ag) REFERENCES adsys_pays (id_pays,id_ag) ON UPDATE No action ON DELETE No action;
ALTER TABLE tireur_benef ADD   FOREIGN KEY (id_banque,id_ag) REFERENCES adsys_banque (id_banque,id_ag) ON UPDATE No action ON DELETE No action;

-- contrainte sur la table ad_gar




