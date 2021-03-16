------------------------------- DEBUT : Ticket #645 : Flexibilité des produits de crédit --------------------------------

CREATE OR REPLACE FUNCTION script_flexibilite_produits_credit() RETURNS INT AS
$$
DECLARE

output_result INTEGER = 1;
tableliste_ident INTEGER = (select ident from tableliste where nomc like 'adsys_produit_credit' order by ident desc limit 1);

BEGIN

	RAISE NOTICE 'START';

	-- Add column in table adsys_produit_credit
	IF NOT EXISTS(SELECT column_name FROM information_schema.columns WHERE table_name='adsys_produit_credit' and column_name='is_flexible') THEN
		ALTER TABLE ONLY adsys_produit_credit ADD COLUMN is_flexible boolean DEFAULT false;

		RAISE NOTICE 'Column is_flexible added in table adsys_produit_credit';
		output_result := 2;
	END IF;
	
	-- Add row in table d_tableliste
	IF NOT EXISTS(SELECT * FROM d_tableliste WHERE tablen=tableliste_ident AND nchmpc = 'is_flexible') THEN
		INSERT INTO d_tableliste(ident, tablen, nchmpc, nchmpl, isreq, ref_field, type, onslct, ispkey, traduit) VALUES ((select max(ident) from d_tableliste)+1, tableliste_ident, 'is_flexible', maketraductionlangsyst('Rendre le produit de crédit flexible ?'), false, NULL, 'bol', NULL, NULL, false);

		RAISE NOTICE 'Insertion is_flexible de la table d_tableliste effectuée';
		output_result := 2;
	END IF;	
	
	-- Add column in table ad_dcr
	IF NOT EXISTS(SELECT column_name FROM information_schema.columns WHERE table_name = 'ad_dcr' AND column_name = 'is_extended') THEN
		ALTER TABLE ad_dcr ADD COLUMN is_extended boolean DEFAULT false;
		
		RAISE NOTICE 'Column is_extended added in table ad_dcr';
		output_result := 2;
	END IF;
	
	-- CREATE TABLE ad_dcr_ext
	IF NOT EXISTS(SELECT * FROM information_schema.tables WHERE table_name = 'ad_dcr_ext') THEN
	
		-- Table: ad_dcr_ext
		-- DROP TABLE ad_dcr_ext;
		CREATE TABLE ad_dcr_ext 
		(
			  id serial NOT NULL,
			  id_doss integer NOT NULL,
			  tx_interet double precision,
			  periodicite integer,
			  gs_cat smallint,
			  mnt_assurance numeric(30,6) DEFAULT 0,
			  prc_assurance double precision DEFAULT 0,
			  mnt_frais numeric(30,6) DEFAULT 0,
			  prc_frais double precision DEFAULT 0,
			  mnt_commission numeric(30,6) DEFAULT 0,
			  prc_commission double precision DEFAULT 0,
			  prc_gar_num double precision DEFAULT 0,
			  id_ag integer NOT NULL,
			  date_creation timestamp without time zone NOT NULL DEFAULT now(),
			  date_modif timestamp without time zone,
			  CONSTRAINT ad_dcr_ext_pkey PRIMARY KEY (id_doss, id_ag),
			  CONSTRAINT ad_dcr_ext_id_doss_fkey FOREIGN KEY (id_doss, id_ag)
					REFERENCES ad_dcr (id_doss, id_ag) MATCH SIMPLE
					ON UPDATE NO ACTION ON DELETE NO ACTION
		)
		WITH (
		  OIDS=FALSE
		);
		ALTER TABLE ad_dcr_ext
		  OWNER TO adbanking;
		COMMENT ON TABLE ad_dcr_ext
		  IS 'Dossiers de crédit et crédits étendu';

		RAISE NOTICE 'Table ad_dcr_ext created';

		output_result := 2;
	END IF;
	
	-- Menu choix Modifier les paramètres du produit de crédit
	IF NOT EXISTS (SELECT * FROM menus WHERE nom_menu = 'Ado-13') THEN
		INSERT INTO menus(nom_menu, libel_menu, nom_pere, pos_hierarch, ordre, is_menu,is_cliquable)
		VALUES ('Ado-13',maketraductionlangsyst('Modification des paramètres du produit de crédit'),'Ado',6,3,'f','f');
	END IF;

	-- Ecran choix Modifier les paramètres du produit de crédit
	IF NOT EXISTS (SELECT * FROM ecrans WHERE nom_ecran = 'Ado-13') THEN
		INSERT INTO ecrans(nom_ecran, fichier, nom_menu, fonction)
		VALUES ('Ado-13', 'modules/credit/dossier.php', 'Ado-13', 106);
	END IF;

	-- Change the orders of the menu
	UPDATE menus SET ordre = 1 WHERE pos_hierarch = 6 AND nom_menu = 'Ado-1';
	UPDATE menus SET ordre = 2 WHERE pos_hierarch = 6 AND nom_menu = 'Ado-12';
	UPDATE menus SET ordre = 3 WHERE pos_hierarch = 6 AND nom_menu = 'Ado-13';
	UPDATE menus SET ordre = 4 WHERE pos_hierarch = 6 AND nom_menu = 'Ado-2';
	UPDATE menus SET ordre = 5 WHERE pos_hierarch = 6 AND nom_menu = 'Ado-3';
	UPDATE menus SET ordre = 6 WHERE pos_hierarch = 6 AND nom_menu = 'Ado-4';
	UPDATE menus SET ordre = 7 WHERE pos_hierarch = 6 AND nom_menu = 'Ado-5';
	UPDATE menus SET ordre = 8 WHERE pos_hierarch = 6 AND nom_menu = 'Ado-6';

	RAISE NOTICE 'END';

	RETURN output_result;

END;
$$
LANGUAGE plpgsql;

SELECT script_flexibilite_produits_credit();
DROP FUNCTION script_flexibilite_produits_credit();

-----------------------------------------------------------

-- Type: dcr_credit_view

DROP TYPE IF EXISTS dcr_credit_view CASCADE;

CREATE TYPE dcr_credit_view AS
(
	id_doss integer,
	id_client integer,
	id_prod integer,
	date_dem timestamp without time zone,
	mnt_dem numeric(30,6),
	obj_dem integer,
	detail_obj_dem text,
	etat integer,
	date_etat timestamp without time zone,
	motif integer,
	id_agent_gest integer,
	delai_grac integer,
	differe_jours integer,
	prelev_auto boolean,
	duree_mois smallint,
	nouv_duree_mois smallint,
	terme integer,
	gar_num numeric(30,6),
	gar_tot numeric(30,6),
	gar_mat numeric(30,6),
	gar_num_encours numeric(30,6),
	cpt_gar_encours integer,
	num_cre smallint,
	assurances_cre boolean,
	cpt_liaison integer,
	cre_id_cpte integer,
	cre_etat integer,
	cre_date_etat timestamp without time zone,
	cre_date_approb timestamp without time zone,
	cre_date_debloc timestamp without time zone,
	cre_nbre_reech integer,
	cre_mnt_octr numeric(30,6),
	details_motif text,
	suspension_pen boolean,
	perte_capital numeric(30,6),
	cre_retard_etat_max integer,
	cre_retard_etat_max_jour integer,
	differe_ech integer,
	id_dcr_grp_sol integer,
	gs_cat smallint,
	prelev_commission boolean,
	cpt_prelev_frais integer,
	id_ag integer,
	cre_prelev_frais_doss boolean,
	prov_mnt numeric(30,6),
	prov_date date,
	prov_is_calcul boolean,
	cre_mnt_deb numeric(30,6),
	doss_repris boolean,
	cre_cpt_att_deb integer,
	date_creation timestamp without time zone,
	date_modif timestamp without time zone,
	is_ligne_credit boolean,
	deboursement_autorisee_lcr boolean,
	motif_changement_authorisation_lcr text,
	date_changement_authorisation_lcr timestamp without time zone,
	duree_nettoyage_lcr integer,
	remb_auto_lcr boolean,
	tx_interet_lcr double precision,
	taux_frais_lcr double precision,
	taux_min_frais_lcr numeric(30,6),
	taux_max_frais_lcr numeric(30,6),
	ordre_remb_lcr smallint,
	mnt_assurance numeric(30,6),
	mnt_commission numeric(30,6),
	mnt_frais_doss numeric(30,6),
	detail_obj_dem_bis integer,
	id_bailleur integer,
	is_extended boolean,
	id integer,
	libel text,
	tx_interet double precision,
	mnt_min numeric(30,6),
	mnt_max numeric(30,6),
	mode_calc_int integer,
	mode_perc_int integer,
	duree_min_mois integer,
	duree_max_mois integer,
	periodicite integer,
	mnt_frais numeric(30,6),
	prc_assurance double precision,
	prc_gar_num double precision,
	prc_gar_mat double precision,
	prc_gar_tot double precision,
	prc_gar_encours double precision,
	mnt_penalite_jour numeric(30,6),
	prc_penalite_retard double precision,
	delai_grace_jour integer,
	differe_jours_max integer,
	nbre_reechelon_auth smallint,
	prc_commission double precision,
	type_duree_credit integer,
	approbation_obli boolean,
	typ_pen_pourc_dcr integer,
	cpte_cpta_prod_cr_int text,
	cpte_cpta_prod_cr_gar text,
	cpte_cpta_prod_cr_pen text,
	devise character(3),
	differe_ech_max integer,
	freq_paiement_cap integer,
	max_jours_compt_penalite integer,
	differe_epargne_nantie boolean,
	report_arrondi boolean,
	calcul_interet_differe boolean,
	prelev_frais_doss smallint,
	percep_frais_com_ass smallint,
	ordre_remb smallint,
	remb_cpt_gar boolean,
	is_produit_decouvert boolean,
	prc_frais double precision,
	cpte_cpta_att_deb text,
	is_produit_actif boolean,
	duree_nettoyage integer,
	cpte_cpta_prod_cr_frais text
);
ALTER TYPE dcr_credit_view
  OWNER TO adbanking;

-- Function: get_ad_dcr_ext_credit(integer, integer, integer, integer)

-- new function updated for GS

CREATE OR REPLACE FUNCTION get_ad_dcr_ext_credit(integer, integer, integer, integer, integer)
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

        SELECT INTO dcr_credit  d.id_doss, d.id_client, d.id_prod, d.date_dem, d.mnt_dem, d.obj_dem, d.detail_obj_dem, d.etat, d.date_etat, d.motif, d.id_agent_gest, d.delai_grac, d.differe_jours, d.prelev_auto, d.duree_mois, d.nouv_duree_mois, d.terme, d.gar_num, d.gar_tot, d.gar_mat, d.gar_num_encours, d.cpt_gar_encours, d.num_cre, d.assurances_cre, d.cpt_liaison, d.cre_id_cpte, d.cre_etat, d.cre_date_etat, d.cre_date_approb, d.cre_date_debloc, d.cre_nbre_reech, d.cre_mnt_octr, d.details_motif, d.suspension_pen, d.perte_capital, d.cre_retard_etat_max, d.cre_retard_etat_max_jour, d.differe_ech, d.id_dcr_grp_sol, dx.gs_cat, d.prelev_commission, d.cpt_prelev_frais, d.id_ag, d.cre_prelev_frais_doss, d.prov_mnt, d.prov_date, d.prov_is_calcul, d.cre_mnt_deb, d.doss_repris, d.cre_cpt_att_deb, d.date_creation, d.date_modif, d.is_ligne_credit, d.deboursement_autorisee_lcr, d.motif_changement_authorisation_lcr, d.date_changement_authorisation_lcr, d.duree_nettoyage_lcr, d.remb_auto_lcr, d.tx_interet_lcr, d.taux_frais_lcr, d.taux_min_frais_lcr, d.taux_max_frais_lcr, d.ordre_remb_lcr, dx.mnt_assurance, dx.mnt_commission, d.mnt_frais_doss, d.detail_obj_dem_bis, d.id_bailleur, d.is_extended, pc.id, pc.libel, dx.tx_interet, pc.mnt_min, pc.mnt_max, pc.mode_calc_int, pc.mode_perc_int, pc.duree_min_mois, pc.duree_max_mois, dx.periodicite, dx.mnt_frais, dx.prc_assurance, dx.prc_gar_num, pc.prc_gar_mat, pc.prc_gar_tot, pc.prc_gar_encours, pc.mnt_penalite_jour, pc.prc_penalite_retard, pc.delai_grace_jour, pc.differe_jours_max, pc.nbre_reechelon_auth, dx.prc_commission, pc.type_duree_credit, pc.approbation_obli, pc.typ_pen_pourc_dcr, pc.cpte_cpta_prod_cr_int, pc.cpte_cpta_prod_cr_gar, pc.cpte_cpta_prod_cr_pen, pc.devise, pc.differe_ech_max, pc.freq_paiement_cap, pc.max_jours_compt_penalite, pc.differe_epargne_nantie, pc.report_arrondi, pc.calcul_interet_differe, pc.prelev_frais_doss, pc.percep_frais_com_ass, pc.ordre_remb, pc.remb_cpt_gar, pc.is_produit_decouvert, dx.prc_frais, pc.cpte_cpta_att_deb, pc.is_produit_actif, pc.duree_nettoyage, pc.cpte_cpta_prod_cr_frais FROM ad_dcr d LEFT JOIN ad_dcr_ext dx ON d.id_doss = dx.id_doss AND d.id_ag = dx.id_ag INNER JOIN adsys_produit_credit pc ON d.id_prod = pc.id AND d.id_ag = pc.id_ag WHERE d.id_doss = ligne.id_doss AND d.id_ag = ligne.id_ag;

      ELSE

        SELECT INTO dcr_credit  d.id_doss, d.id_client, d.id_prod, d.date_dem, d.mnt_dem, d.obj_dem, d.detail_obj_dem, d.etat, d.date_etat, d.motif, d.id_agent_gest, d.delai_grac, d.differe_jours, d.prelev_auto, d.duree_mois, d.nouv_duree_mois, d.terme, d.gar_num, d.gar_tot, d.gar_mat, d.gar_num_encours, d.cpt_gar_encours, d.num_cre, d.assurances_cre, d.cpt_liaison, d.cre_id_cpte, d.cre_etat, d.cre_date_etat, d.cre_date_approb, d.cre_date_debloc, d.cre_nbre_reech, d.cre_mnt_octr, d.details_motif, d.suspension_pen, d.perte_capital, d.cre_retard_etat_max, d.cre_retard_etat_max_jour, d.differe_ech, d.id_dcr_grp_sol, d.gs_cat, d.prelev_commission, d.cpt_prelev_frais, d.id_ag, d.cre_prelev_frais_doss, d.prov_mnt, d.prov_date, d.prov_is_calcul, d.cre_mnt_deb, d.doss_repris, d.cre_cpt_att_deb, d.date_creation, d.date_modif, d.is_ligne_credit, d.deboursement_autorisee_lcr, d.motif_changement_authorisation_lcr, d.date_changement_authorisation_lcr, d.duree_nettoyage_lcr, d.remb_auto_lcr, d.tx_interet_lcr, d.taux_frais_lcr, d.taux_min_frais_lcr, d.taux_max_frais_lcr, d.ordre_remb_lcr, d.mnt_assurance, d.mnt_commission, d.mnt_frais_doss, d.detail_obj_dem_bis, d.id_bailleur, d.is_extended, pc.id, pc.libel, pc.tx_interet, pc.mnt_min, pc.mnt_max, pc.mode_calc_int, pc.mode_perc_int, pc.duree_min_mois, pc.duree_max_mois, pc.periodicite, pc.mnt_frais, pc.prc_assurance, pc.prc_gar_num, pc.prc_gar_mat, pc.prc_gar_tot, pc.prc_gar_encours, pc.mnt_penalite_jour, pc.prc_penalite_retard, pc.delai_grace_jour, pc.differe_jours_max, pc.nbre_reechelon_auth, pc.prc_commission, pc.type_duree_credit, pc.approbation_obli, pc.typ_pen_pourc_dcr, pc.cpte_cpta_prod_cr_int, pc.cpte_cpta_prod_cr_gar, pc.cpte_cpta_prod_cr_pen, pc.devise, pc.differe_ech_max, pc.freq_paiement_cap, pc.max_jours_compt_penalite, pc.differe_epargne_nantie, pc.report_arrondi, pc.calcul_interet_differe, pc.prelev_frais_doss, pc.percep_frais_com_ass, pc.ordre_remb, pc.remb_cpt_gar, pc.is_produit_decouvert, pc.prc_frais, pc.cpte_cpta_att_deb, pc.is_produit_actif, pc.duree_nettoyage, pc.cpte_cpta_prod_cr_frais FROM ad_dcr d LEFT JOIN adsys_produit_credit pc ON d.id_prod = pc.id AND d.id_ag = pc.id_ag WHERE d.id_doss = ligne.id_doss AND d.id_ag = ligne.id_ag;

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
  LANGUAGE plpgsql VOLATILE;

-------------------------------- FIN : Ticket #645 : Flexibilité des produits de crédit --------------------------------