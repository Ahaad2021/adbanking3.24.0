CREATE OR REPLACE FUNCTION script_mb_325() RETURNS INT AS
$$
DECLARE
output_result INTEGER = 1;
  d_tableliste_str integer = 0;
  tableliste_ident integer = 0;

  BEGIN

tableliste_ident := (select ident from tableliste where nomc like 'ad_agc' order by ident desc limit 1);

IF NOT EXISTS (SELECT * FROM information_schema.columns WHERE table_name='ad_agc' and column_name='mnt_retrait_ewallet_max_journalier') THEN
	ALTER TABLE ad_agc ADD COLUMN mnt_retrait_ewallet_max_journalier numeric(30,6);
	d_tableliste_str := makeTraductionLangSyst('Montant journalier maximal sur retrait Ewallet');
	INSERT INTO d_tableliste VALUES ((select max(ident) from d_tableliste)+1, tableliste_ident, 'mnt_retrait_ewallet_max_journalier', d_tableliste_str, NULL, NULL, 'mnt', false, false, false);
	  IF EXISTS(SELECT langue FROM adsys_langues_systeme WHERE code = 'en_GB') THEN
	    INSERT INTO ad_traductions VALUES (d_tableliste_str,'en_GB','Maximum daily amount on Ewallet withdrawal');
	  END IF;
END IF;

IF NOT EXISTS (SELECT * FROM information_schema.columns WHERE table_name='ad_cli' and column_name='mnt_limit_ewallet_jounalier') THEN
  IF NOT EXISTS (SELECT * FROM information_schema.columns WHERE table_name='ad_cli' and column_name='mnt_retrait_limit_ewallet_jounalier') THEN
	  ALTER TABLE ad_cli ADD COLUMN mnt_limit_ewallet_jounalier numeric(30,6);
	END IF;
END IF;

IF NOT EXISTS (SELECT * FROM information_schema.columns WHERE table_name='ad_cli' and column_name='date_limit_ewallet_jounalier') THEN
  IF NOT EXISTS (SELECT * FROM information_schema.columns WHERE table_name='ad_cli' and column_name='date_retrait_limit_ewallet_jounalier') THEN
    ALTER TABLE ad_cli ADD COLUMN date_limit_ewallet_jounalier date;
  END IF;
END IF;

  RETURN output_result;
END;
$$
LANGUAGE plpgsql;

SELECT script_mb_325();
DROP FUNCTION script_mb_325();


------------------------------------------------------------ ticket AT-191 -----------------------------------------------------------
-- Function: getdatajournalcomptable(text, integer, date, date, integer)

-- DROP FUNCTION getdatajournalcomptable(text, integer, date, date, integer);

CREATE OR REPLACE FUNCTION getdatajournalcomptable(
    text,
    integer,
    date,
    date,
    integer)
  RETURNS SETOF data_export_journal AS
$BODY$
 DECLARE

in_compte ALIAS FOR $1;		-- numero compte
in_type_ope ALIAS FOR $2;		-- type operation
in_date_debut ALIAS FOR $3;	-- date debut
in_date_fin ALIAS FOR $4;	-- date fin
in_id_agence ALIAS FOR $5;	-- id agence


v_sens_inv text;

counter integer :=0 ;

ligne record;
ligne1 record;

cur_list_compte refcursor;
cur_list_compte_inv refcursor;

ligne_data data_export_journal;

BEGIN
	--Creation table temporaire
	CREATE TEMP TABLE getdatajournal AS SELECT a.id_ecriture, a.id_his, c.date, b.compte, b.sens, b.montant, c.id_client, b.devise, a.libel_ecriture,a.type_operation,a.info_ecriture
		FROM ad_ecriture a INNER JOIN ad_mouvement b ON a.id_ag = b.id_ag AND a.id_ecriture=b.id_ecriture
		INNER JOIN ad_his c ON b.id_ag = c.id_ag AND a.id_his=c.id_his
		WHERE c.id_ag = in_id_agence AND date(date_comptable) >= date(in_date_debut) AND date(date_comptable) <= date(in_date_fin) order by c.date,a.id_ecriture;


	IF (in_compte IS NULL AND in_type_ope IS NULL) THEN
		OPEN cur_list_compte FOR SELECT abc.* FROM getdatajournal abc order by abc.date,abc.id_ecriture;
	ELSIF (in_compte IS NOT NULL AND in_type_ope IS NULL) THEN
		OPEN cur_list_compte FOR SELECT abc.* FROM getdatajournal abc WHERE (abc.compte=in_compte) order by abc.date,abc.id_ecriture;
	ELSIF (in_compte IS NULL AND in_type_ope IS NOT NULL) THEN
		OPEN cur_list_compte FOR SELECT abc.* FROM getdatajournal abc WHERE abc.libel_ecriture = (SELECT libel_ope FROM ad_cpt_ope WHERE type_operation = in_type_ope) order by abc.date,abc.id_ecriture;
	ELSE
		OPEN cur_list_compte FOR SELECT abc.* FROM getdatajournal abc WHERE (abc.compte=in_compte) AND abc.libel_ecriture = (SELECT libel_ope FROM ad_cpt_ope WHERE type_operation = in_type_ope)
		order by abc.date,abc.id_ecriture;
	END IF;

	FETCH cur_list_compte INTO ligne;
	WHILE FOUND LOOP

		SELECT INTO ligne_data ligne.id_ecriture, ligne.id_his, ligne.date, ligne.compte, ligne.sens, ligne.montant, ligne.id_client, ligne.devise, ligne.libel_ecriture,ligne.type_operation,ligne.info_ecriture, in_id_agence;
		RETURN NEXT ligne_data;

		/*IF (ligne.sens = 'c') THEN
		v_sens_inv = 'd';
		ELSE
		v_sens_inv = 'c';
		END IF;

		OPEN cur_list_compte_inv FOR SELECT abc.* FROM getdatajournal abc WHERE abc.id_ecriture = ligne.id_ecriture and abc.sens = v_sens_inv;
		FETCH cur_list_compte_inv INTO ligne1;
		WHILE FOUND LOOP
			SELECT INTO ligne_data ligne1.id_ecriture, ligne1.id_his, ligne1.date, ligne1.compte, ligne1.sens, ligne1.montant, ligne1.id_client, ligne1.devise, ligne1.libel_ecriture,ligne1.type_operation,ligne1.info_ecriture, in_id_agence;
			RETURN NEXT ligne_data;

		FETCH cur_list_compte_inv INTO ligne1;
		END LOOP;
		CLOSE cur_list_compte_inv;*/

		--RAISE NOTICE 'id_ecriture => %	--  id_his => %', ligne.id_ecriture,ligne.id_his;
		counter = counter + 1;

	FETCH cur_list_compte INTO ligne;
	END LOOP;
	CLOSE cur_list_compte;
	--RAISE NOTICE 'counter => %',counter;

END;
$BODY$
  LANGUAGE plpgsql VOLATILE
  COST 100
  ROWS 1000;
ALTER FUNCTION getdatajournalcomptable(text, integer, date, date, integer)
  OWNER TO postgres;

------------------------------------------------AT-194 -------------------------------------------
CREATE OR REPLACE FUNCTION script_at_194() RETURNS INT AS
$$
DECLARE
output_result INTEGER = 1;

  BEGIN
  IF EXISTS (SELECT constraint_name FROM information_schema.constraint_column_usage WHERE table_name = 'ad_brouillard'  AND constraint_name = 'ad_brouillard_id_his_compte_id_sens_id_ag_cpte_interne_cli_key' ) THEN
	  ALTER TABLE ad_brouillard
	  DROP CONSTRAINT ad_brouillard_id_his_compte_id_sens_id_ag_cpte_interne_cli_key;
  END IF;
  RETURN output_result;
END;
$$
LANGUAGE plpgsql;

SELECT script_at_194();
DROP FUNCTION script_at_194();

----------------------------------------------------------  AT-186 ---------------------------------
CREATE OR REPLACE FUNCTION script_at_186() RETURNS INT AS
$$
DECLARE
output_result INTEGER = 1;
  d_tableliste_str integer = 0;
  tableliste_ident integer = 0;
  id_str_trad integer = 0;

  BEGIN


IF NOT EXISTS(SELECT * FROM tableliste WHERE nomc = 'adsys_interet_differe_echeance') THEN
INSERT INTO tableliste VALUES ((select max(ident) from tableliste)+1, 'adsys_interet_differe_echeance', makeTraductionLangSyst('Appliquer differ?? interet sur les credits debours??'), false);
RAISE NOTICE 'Donn??es table adsys_interet_differe_echeance rajout??s dans table tableliste';
END IF;

 tableliste_ident := (SELECT ident FROM tableliste WHERE nomc = 'adsys_interet_differe_echeance');

IF NOT EXISTS(SELECT * FROM d_tableliste WHERE nchmpc = 'id' and tablen = tableliste_ident ) THEN
    INSERT INTO d_tableliste VALUES ((select max(ident) from d_tableliste)+1, tableliste_ident, 'id', makeTraductionLangSyst('Id'), TRUE, NULL, 'int', NULL, TRUE, FALSE);
END IF;


 tableliste_ident := (SELECT ident FROM tableliste WHERE nomc = 'adsys_produit_credit');

IF NOT EXISTS(SELECT * FROM d_tableliste WHERE nchmpc = 'appl_interet_diff_echeance' and tablen = tableliste_ident) THEN
    INSERT INTO d_tableliste VALUES ((select max(ident) from d_tableliste)+1, tableliste_ident, 'appl_interet_diff_echeance', makeTraductionLangSyst('Appliquer differ?? interet sur les credits debours??'), null, (SELECT ident FROM d_tableliste WHERE nchmpc = 'id' and tablen =
    (SELECT ident FROM tableliste WHERE nomc = 'adsys_interet_differe_echeance')), 'int', null, FALSE, FALSE);
END IF;

IF NOT EXISTS(SELECT * FROM information_schema.columns WHERE table_name = 'adsys_produit_credit' AND column_name = 'appl_interet_diff_echeance') THEN
	ALTER TABLE adsys_produit_credit ADD COLUMN appl_interet_diff_echeance INTEGER DEFAULT 1;
END IF;

IF NOT EXISTS(SELECT * FROM information_schema.columns WHERE table_name = 'ad_dcr_his' AND column_name = 'diff_ech') THEN
	ALTER TABLE ad_dcr_his ADD COLUMN diff_ech INTEGER DEFAULT NULL;
END IF;

IF NOT EXISTS(SELECT * FROM information_schema.columns WHERE table_name = 'ad_dcr' AND column_name = 'diff_ech_apres_deb') THEN
  ALTER TABLE ad_dcr ADD COLUMN diff_ech_apres_deb INTEGER DEFAULT NULL;
END IF;

/*********************** ECRAN / MENU ********************************************/

/*********************** ECRAN Differ?? en echeance ********************************************/
IF NOT EXISTS (select * from menus where nom_menu = 'Dec-1') THEN
 id_str_trad := maketraductionlangsyst('Demande de differ?? en ??ch??ances');
 INSERT INTO menus (nom_menu, libel_menu, nom_pere, pos_hierarch, ordre, is_menu,fonction, is_cliquable)
 VALUES ('Dec-1', id_str_trad, 'Mec', 6, 9, FALSE, null, FALSE);
 IF EXISTS(SELECT langue FROM adsys_langues_systeme WHERE code = 'en_GB') THEN
  INSERT INTO ad_traductions (id_str, langue, traduction) VALUES (id_str_trad, 'en_GB', 'Request for deferral in due dates');
 END IF;
END IF;

IF NOT EXISTS (select * from ecrans where nom_ecran = 'Dec-1') THEN
 INSERT INTO ecrans(nom_ecran, fichier, nom_menu, fonction)
 VALUES ('Dec-1', 'modules/credit/mod_diff_ech.php', 'Dec-1', 103);
END IF;

IF NOT EXISTS (select * from ecrans where nom_ecran = 'Dec-2') THEN
 INSERT INTO ecrans(nom_ecran, fichier, nom_menu, fonction)
 VALUES ('Dec-2', 'modules/credit/mod_diff_ech.php', 'Dec-1', 103);
END IF;

IF NOT EXISTS (select * from ecrans where nom_ecran = 'Dec-3') THEN
 INSERT INTO ecrans(nom_ecran, fichier, nom_menu, fonction)
 VALUES ('Dec-3', 'modules/credit/mod_diff_ech.php', 'Dec-1', 103);
END IF;

IF NOT EXISTS (select * from ecrans where nom_ecran = 'Dec-4') THEN
 INSERT INTO ecrans(nom_ecran, fichier, nom_menu, fonction)
 VALUES ('Dec-4', 'modules/credit/mod_diff_ech.php', 'Dec-1', 103);
END IF;

/*********************** ECRAN Approbation du differ?? en echeance ********************************************/
IF NOT EXISTS (select * from menus where nom_menu = 'Ade-1') THEN
 id_str_trad := maketraductionlangsyst('Approbation differ?? en ??ch??ances');
 INSERT INTO menus (nom_menu, libel_menu, nom_pere, pos_hierarch, ordre, is_menu,fonction, is_cliquable)
 VALUES ('Ade-1', id_str_trad, 'Mec', 6, 10, FALSE, null, FALSE);
 IF EXISTS(SELECT langue FROM adsys_langues_systeme WHERE code = 'en_GB') THEN
  INSERT INTO ad_traductions (id_str, langue, traduction) VALUES (id_str_trad, 'en_GB', 'Deferred approval in due dates');
 END IF;
END IF;

IF NOT EXISTS (select * from ecrans where nom_ecran = 'Ade-1') THEN
 INSERT INTO ecrans(nom_ecran, fichier, nom_menu, fonction)
 VALUES ('Ade-1', 'modules/credit/approb_diff_ech.php', 'Ade-1', 104);
END IF;

IF NOT EXISTS (select * from ecrans where nom_ecran = 'Ade-2') THEN
 INSERT INTO ecrans(nom_ecran, fichier, nom_menu, fonction)
 VALUES ('Ade-2', 'modules/credit/approb_diff_ech.php', 'Ade-1', 104);
END IF;

IF NOT EXISTS (select * from ecrans where nom_ecran = 'Ade-3') THEN
 INSERT INTO ecrans(nom_ecran, fichier, nom_menu, fonction)
 VALUES ('Ade-3', 'modules/credit/approb_diff_ech.php', 'Ade-1', 104);
END IF;

IF NOT EXISTS (select * from ecrans where nom_ecran = 'Ade-4') THEN
 INSERT INTO ecrans(nom_ecran, fichier, nom_menu, fonction)
 VALUES ('Ade-4', 'modules/credit/approb_diff_ech.php', 'Ade-1', 104);
END IF;


/*********************** ECRAN Annulation du differ?? en echeance ********************************************/
IF NOT EXISTS (select * from menus where nom_menu = 'Cde-1') THEN
 id_str_trad := maketraductionlangsyst('Annulation differ?? en ??ch??ances');
 INSERT INTO menus (nom_menu, libel_menu, nom_pere, pos_hierarch, ordre, is_menu,fonction, is_cliquable)
 VALUES ('Cde-1', id_str_trad, 'Mec', 6, 11, FALSE, null, FALSE);
 IF EXISTS(SELECT langue FROM adsys_langues_systeme WHERE code = 'en_GB') THEN
  INSERT INTO ad_traductions (id_str, langue, traduction) VALUES (id_str_trad, 'en_GB', 'Deferred cancellation in due dates');
 END IF;
END IF;

IF NOT EXISTS (select * from ecrans where nom_ecran = 'Cde-1') THEN
 INSERT INTO ecrans(nom_ecran, fichier, nom_menu, fonction)
 VALUES ('Cde-1', 'modules/credit/annul_diff_ech.php', 'Cde-1', 107);
END IF;

IF NOT EXISTS (select * from ecrans where nom_ecran = 'Cde-2') THEN
 INSERT INTO ecrans(nom_ecran, fichier, nom_menu, fonction)
 VALUES ('Cde-2', 'modules/credit/annul_diff_ech.php', 'Cde-1', 107);
END IF;

IF NOT EXISTS (select * from ecrans where nom_ecran = 'Cde-3') THEN
 INSERT INTO ecrans(nom_ecran, fichier, nom_menu, fonction)
 VALUES ('Cde-3', 'modules/credit/annul_diff_ech.php', 'Cde-1', 107);
END IF;

/*********************** ECRAN Rejet du differ?? en echeance ********************************************/
IF NOT EXISTS (select * from menus where nom_menu = 'Rde-1') THEN
 id_str_trad := maketraductionlangsyst('Rejet differ?? en ??ch??ances');
 INSERT INTO menus (nom_menu, libel_menu, nom_pere, pos_hierarch, ordre, is_menu,fonction, is_cliquable)
 VALUES ('Rde-1', id_str_trad, 'Mec', 6, 12, FALSE, null, FALSE);
 IF EXISTS(SELECT langue FROM adsys_langues_systeme WHERE code = 'en_GB') THEN
  INSERT INTO ad_traductions (id_str, langue, traduction) VALUES (id_str_trad, 'en_GB', 'Deferred rejection in due dates');
 END IF;
END IF;

IF NOT EXISTS (select * from ecrans where nom_ecran = 'Rde-1') THEN
 INSERT INTO ecrans(nom_ecran, fichier, nom_menu, fonction)
 VALUES ('Rde-1', 'modules/credit/rejet_diff_ech.php', 'Rde-1', 108);
END IF;

IF NOT EXISTS (select * from ecrans where nom_ecran = 'Rde-2') THEN
 INSERT INTO ecrans(nom_ecran, fichier, nom_menu, fonction)
 VALUES ('Rde-2', 'modules/credit/rejet_diff_ech.php', 'Rde-1', 108);
END IF;

IF NOT EXISTS (select * from ecrans where nom_ecran = 'Rde-3') THEN
 INSERT INTO ecrans(nom_ecran, fichier, nom_menu, fonction)
 VALUES ('Rde-3', 'modules/credit/rejet_diff_ech.php', 'Rde-1', 108);
END IF;


  RETURN output_result;
END;
$$
  LANGUAGE plpgsql ;

SELECT script_at_186();
DROP FUNCTION script_at_186();



------------- REMAKE EXISTANT VIEW TYPE -------------------

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
	detail_obj_dem_2 integer,
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
	cpte_cpta_prod_cr_frais text,
	appl_interet_diff_echeance INTEGER
);
ALTER TYPE dcr_credit_view
  OWNER TO adbanking;

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

      SELECT INTO dcr_credit  d.id_doss, d.id_client, d.id_prod, d.date_dem, d.mnt_dem, d.obj_dem, d.detail_obj_dem, d.etat, d.date_etat, d.motif, d.id_agent_gest,
      d.delai_grac, d.differe_jours, d.prelev_auto, d.duree_mois, d.nouv_duree_mois, d.terme, d.gar_num, d.gar_tot, d.gar_mat, d.gar_num_encours, d.cpt_gar_encours,
      d.num_cre, d.assurances_cre, d.cpt_liaison, d.cre_id_cpte, d.cre_etat, d.cre_date_etat, d.cre_date_approb, d.cre_date_debloc, d.cre_nbre_reech, d.cre_mnt_octr,
      d.details_motif, d.suspension_pen, d.perte_capital, d.cre_retard_etat_max, d.cre_retard_etat_max_jour, d.differe_ech, d.id_dcr_grp_sol, dx.gs_cat, d.prelev_commission,
      d.cpt_prelev_frais, d.id_ag, d.cre_prelev_frais_doss, d.prov_mnt, d.prov_date, d.prov_is_calcul, d.cre_mnt_deb, d.doss_repris, d.cre_cpt_att_deb, d.date_creation,
      d.date_modif, d.is_ligne_credit, d.deboursement_autorisee_lcr, d.motif_changement_authorisation_lcr, d.date_changement_authorisation_lcr, d.duree_nettoyage_lcr,
      d.remb_auto_lcr, d.tx_interet_lcr, d.taux_frais_lcr, d.taux_min_frais_lcr, d.taux_max_frais_lcr, d.ordre_remb_lcr, dx.mnt_assurance, dx.mnt_commission,
      d.mnt_frais_doss, d.detail_obj_dem_bis,d.detail_obj_dem_2, d.id_bailleur, d.is_extended, pc.id, pc.libel, dx.tx_interet, pc.mnt_min, pc.mnt_max, pc.mode_calc_int,
      pc.mode_perc_int, pc.duree_min_mois, pc.duree_max_mois, dx.periodicite, dx.mnt_frais, dx.prc_assurance, dx.prc_gar_num, pc.prc_gar_mat,
      (dx.prc_gar_num + pc.prc_gar_mat), pc.prc_gar_encours, pc.mnt_penalite_jour, pc.prc_penalite_retard, pc.delai_grace_jour, pc.differe_jours_max,
      pc.nbre_reechelon_auth, dx.prc_commission, pc.type_duree_credit, pc.approbation_obli, pc.typ_pen_pourc_dcr, pc.cpte_cpta_prod_cr_int, pc.cpte_cpta_prod_cr_gar,
      pc.cpte_cpta_prod_cr_pen, pc.devise, pc.differe_ech_max, pc.freq_paiement_cap, pc.max_jours_compt_penalite, pc.differe_epargne_nantie, pc.report_arrondi,
      pc.calcul_interet_differe, pc.prelev_frais_doss, pc.percep_frais_com_ass, pc.ordre_remb, pc.remb_cpt_gar, pc.is_produit_decouvert, dx.prc_frais,
      pc.cpte_cpta_att_deb, pc.is_produit_actif, pc.duree_nettoyage, pc.cpte_cpta_prod_cr_frais,  pc.appl_interet_diff_echeance FROM ad_dcr d LEFT JOIN ad_dcr_ext dx ON d.id_doss = dx.id_doss
      AND d.id_ag = dx.id_ag INNER JOIN adsys_produit_credit pc ON d.id_prod = pc.id AND d.id_ag = pc.id_ag WHERE d.id_doss = ligne.id_doss AND d.id_ag = ligne.id_ag;

      ELSE

        SELECT INTO dcr_credit  d.id_doss, d.id_client, d.id_prod, d.date_dem, d.mnt_dem, d.obj_dem, d.detail_obj_dem, d.etat, d.date_etat,
      d.motif, d.id_agent_gest, d.delai_grac, d.differe_jours, d.prelev_auto, d.duree_mois, d.nouv_duree_mois,
      d.terme, d.gar_num, d.gar_tot, d.gar_mat, d.gar_num_encours, d.cpt_gar_encours, d.num_cre, d.assurances_cre,
      d.cpt_liaison, d.cre_id_cpte, d.cre_etat, d.cre_date_etat, d.cre_date_approb, d.cre_date_debloc, d.cre_nbre_reech,
      d.cre_mnt_octr, d.details_motif, d.suspension_pen, d.perte_capital, d.cre_retard_etat_max, d.cre_retard_etat_max_jour, d.differe_ech,
      d.id_dcr_grp_sol, d.gs_cat, d.prelev_commission, d.cpt_prelev_frais, d.id_ag, d.cre_prelev_frais_doss, d.prov_mnt, d.prov_date,
      d.prov_is_calcul, d.cre_mnt_deb, d.doss_repris, d.cre_cpt_att_deb, d.date_creation, d.date_modif, d.is_ligne_credit,
      d.deboursement_autorisee_lcr, d.motif_changement_authorisation_lcr, d.date_changement_authorisation_lcr,
      d.duree_nettoyage_lcr, d.remb_auto_lcr, d.tx_interet_lcr, d.taux_frais_lcr, d.taux_min_frais_lcr,
      d.taux_max_frais_lcr, d.ordre_remb_lcr, d.mnt_assurance, d.mnt_commission, d.mnt_frais_doss,
      d.detail_obj_dem_bis,d.detail_obj_dem_2, d.id_bailleur, d.is_extended, pc.id, pc.libel, pc.tx_interet, pc.mnt_min, pc.mnt_max,
      pc.mode_calc_int, pc.mode_perc_int, pc.duree_min_mois, pc.duree_max_mois, pc.periodicite, pc.mnt_frais, pc.prc_assurance,
      pc.prc_gar_num, pc.prc_gar_mat, pc.prc_gar_tot, pc.prc_gar_encours, pc.mnt_penalite_jour, pc.prc_penalite_retard, pc.delai_grace_jour,
      pc.differe_jours_max, pc.nbre_reechelon_auth, pc.prc_commission, pc.type_duree_credit, pc.approbation_obli, pc.typ_pen_pourc_dcr,
      pc.cpte_cpta_prod_cr_int, pc.cpte_cpta_prod_cr_gar, pc.cpte_cpta_prod_cr_pen, pc.devise, pc.differe_ech_max, pc.freq_paiement_cap,
      pc.max_jours_compt_penalite, pc.differe_epargne_nantie, pc.report_arrondi, pc.calcul_interet_differe, pc.prelev_frais_doss,
      pc.percep_frais_com_ass, pc.ordre_remb, pc.remb_cpt_gar, pc.is_produit_decouvert, pc.prc_frais, pc.cpte_cpta_att_deb,
      pc.is_produit_actif, pc.duree_nettoyage, pc.cpte_cpta_prod_cr_frais, pc.appl_interet_diff_echeance FROM ad_dcr d LEFT JOIN adsys_produit_credit pc
      ON d.id_prod = pc.id AND d.id_ag = pc.id_ag WHERE d.id_doss = ligne.id_doss AND d.id_ag = ligne.id_ag;

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





