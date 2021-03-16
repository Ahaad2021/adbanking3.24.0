CREATE OR REPLACE FUNCTION config_montant_ewallet() RETURNS INT AS
$$
DECLARE
  output_result INTEGER = 1;
  devise_agc VARCHAR(25);

BEGIN

  RAISE NOTICE 'START';

  SELECT INTO devise_agc code_devise FROM devise;
  ------------------------------------------------ Config montant transfert ewallet -----------------------------------------------------
IF NOT EXISTS (SELECT * FROM ad_ebanking_transfert WHERE action = 'TRANSFERT_EWALLET_DEPOT' AND service = 'SMS') THEN
  INSERT INTO ad_ebanking_transfert(id_ag, service, action, mnt_min, mnt_max, devise, date_creation) VALUES (numagc(), 'SMS','TRANSFERT_EWALLET_DEPOT', 100, 200000, devise_agc, now());
END IF;

IF NOT EXISTS (SELECT * FROM ad_ebanking_transfert WHERE action = 'TRANSFERT_EWALLET_RETRAIT' AND service = 'SMS') THEN
  INSERT INTO ad_ebanking_transfert(id_ag, service, action, mnt_min, mnt_max, devise, date_creation) VALUES (numagc(), 'SMS','TRANSFERT_EWALLET_RETRAIT', 100, 200000, devise_agc, now());
END IF;

  RAISE NOTICE 'END';
  RETURN output_result;

END;
$$
LANGUAGE plpgsql;

SELECT config_montant_ewallet();
DROP FUNCTION config_montant_ewallet();


---------------------------------------------Fonction Budget : get_budget_complet()----------------------------------------------------------------------
-- Function: get_budget_complet(integer, integer, text, date, date)

-- DROP FUNCTION get_budget_complet(integer, integer, text, date, date);

CREATE OR REPLACE FUNCTION get_budget_complet(
    integer,
    integer,
    text,
    date,
    date)
  RETURNS SETOF rapport_budget_complet AS
$BODY$
	DECLARE
	p_exo_budget ALIAS FOR $1;
	p_type_budget ALIAS FOR $2;
	p_ref_budget ALIAS FOR $3;
	p_date_debut ALIAS FOR $4;
	p_date_fin ALIAS FOR $5;

	nom_poste text;
	v_poste_concat text;
	v_trim1_cumul numeric(30,6);
	v_trim2_cumul numeric(30,6);
	v_trim3_cumul numeric(30,6);
	v_trim4_cumul numeric(30,6);

	v_cpte_comptable text;

	ligne_rapport_complet rapport_budget_complet;

	curs_autre_poste refcursor;
	ligne_autre_poste RECORD;

	curs_calcul refcursor;
	ligne_calcul RECORD;


	output_result integer := 1;
	BEGIN

	OPEN curs_autre_poste FOR SELECT c.id ,poste_principal, poste_niveau_1, poste_niveau_2, poste_niveau_3, description, compartiment
	from ad_correspondance  c
	--INNER JOIN ad_budget_cpte_comptable p ON p.id_ligne = c.id
	where dernier_niveau = 'f' and etat_correspondance = 't' and type_budget = p_type_budget and ref_budget =p_ref_budget
	order by coalesce(poste_principal,0), coalesce(poste_niveau_1,0), coalesce(poste_niveau_2,0), coalesce(poste_niveau_3,0) asc;

	FETCH curs_autre_poste INTO ligne_autre_poste ;
	WHILE FOUND LOOP

	IF (ligne_autre_poste.poste_principal IS NOT null ) AND (ligne_autre_poste.poste_niveau_1 IS NULL) AND (ligne_autre_poste.poste_niveau_2 IS NULL) AND (ligne_autre_poste.poste_niveau_3 IS NULL) THEN
		v_poste_concat = ligne_autre_poste.poste_principal||'.%' ; RAISE NOTICE 'test =>%',v_poste_concat;
		nom_poste = ligne_autre_poste.poste_principal;

	END IF;
	IF (ligne_autre_poste.poste_principal IS NOT null ) AND (ligne_autre_poste.poste_niveau_1 IS NOT NULL) AND (ligne_autre_poste.poste_niveau_2 IS NULL) AND (ligne_autre_poste.poste_niveau_3 IS NULL) THEN
		v_poste_concat = ligne_autre_poste.poste_principal||'.'||ligne_autre_poste.poste_niveau_1||'.%';
		nom_poste = ligne_autre_poste.poste_principal||'.'||ligne_autre_poste.poste_niveau_1;
	END IF;
	IF (ligne_autre_poste.poste_principal IS NOT null ) AND (ligne_autre_poste.poste_niveau_1 IS NOT NULL) AND (ligne_autre_poste.poste_niveau_2 IS NOT NULL) AND (ligne_autre_poste.poste_niveau_3 IS NULL) THEN
		v_poste_concat = ligne_autre_poste.poste_principal||'.'||ligne_autre_poste.poste_niveau_1||'.'||ligne_autre_poste.poste_niveau_2||'.%';
		nom_poste = ligne_autre_poste.poste_principal||'.'||ligne_autre_poste.poste_niveau_1||'.'||ligne_autre_poste.poste_niveau_2;
	END IF;

		OPEN curs_calcul FOR SELECT * from get_budget(p_exo_budget, p_type_budget,p_date_debut, p_date_fin) where poste LIKE v_poste_concat;
		v_trim1_cumul = 0;
		v_trim2_cumul = 0;
		v_trim3_cumul = 0;
		v_trim4_cumul = 0;

		FETCH curs_calcul INTO ligne_calcul ;
		WHILE FOUND LOOP
		--RAISE NOTICE 'les sous postes sont =>%', ligne_calcul.poste;

		-- budget annuel
		v_trim1_cumul = v_trim1_cumul + coalesce(ligne_calcul.trim_1,0);
		v_trim2_cumul = v_trim2_cumul + coalesce(ligne_calcul.trim_2,0);
		v_trim3_cumul = v_trim3_cumul + coalesce(ligne_calcul.trim_3,0);
		v_trim4_cumul = v_trim4_cumul + coalesce(ligne_calcul.trim_4,0);



		FETCH curs_calcul INTO ligne_calcul;
		END LOOP;
		CLOSE curs_calcul;


		select into v_cpte_comptable array_to_string(array_agg(cpte_comptable),' - ') from ad_budget_cpte_comptable where id_ligne = ligne_autre_poste.id;

		SELECT INTO ligne_rapport_complet ligne_autre_poste.id, nom_poste, ligne_autre_poste.description,ligne_autre_poste.compartiment,v_cpte_comptable, v_trim1_cumul,v_trim2_cumul,v_trim3_cumul,v_trim4_cumul;
		RETURN NEXT ligne_rapport_complet;

	FETCH curs_autre_poste INTO ligne_autre_poste;
	END LOOP;
	CLOSE curs_autre_poste;


	  RETURN;
	END;
	$BODY$
  LANGUAGE plpgsql VOLATILE
  COST 100
  ROWS 1000;
ALTER FUNCTION get_budget_complet(integer, integer, text, date, date)
  OWNER TO postgres;



  ---------------------------------------------Debut Ticket AT-96----------------------------------------------------------------------
  CREATE OR REPLACE FUNCTION at_96() RETURNS INT AS
$BODY$
DECLARE
output_result INTEGER = 1;
tableliste_ident INTEGER = 0;
tableliste_str INTEGER = 0;
d_tableliste_str INTEGER = 0;

BEGIN

tableliste_ident := (select ident from tableliste where nomc like 'ad_cli' order by ident desc limit 1);

IF NOT EXISTS(SELECT * FROM d_tableliste WHERE nchmpc = 'nbre_hommes_grp' and tablen = tableliste_ident) THEN
	ALTER TABLE ad_cli ADD COLUMN nbre_hommes_grp INTEGER;
	d_tableliste_str := makeTraductionLangSyst('Nombre d''hommes du groupe');
	INSERT INTO d_tableliste VALUES ((select max(ident) from d_tableliste)+1, tableliste_ident, 'nbre_hommes_grp', d_tableliste_str, false, NULL, 'int', false, false, false);
	IF EXISTS(SELECT langue FROM adsys_langues_systeme WHERE code = 'en_GB') THEN
		INSERT INTO ad_traductions VALUES (d_tableliste_str,'en_GB','Number of men in the group');
	END IF;
END IF;

IF NOT EXISTS(SELECT * FROM d_tableliste WHERE nchmpc = 'nbre_femmes_grp' and tablen = tableliste_ident) THEN
	ALTER TABLE ad_cli ADD COLUMN nbre_femmes_grp INTEGER;
	d_tableliste_str := makeTraductionLangSyst('Nombre de femmes du groupe');
	INSERT INTO d_tableliste VALUES ((select max(ident) from d_tableliste)+1, tableliste_ident, 'nbre_femmes_grp', d_tableliste_str, false, NULL, 'int', false, false, false);
	IF EXISTS(SELECT langue FROM adsys_langues_systeme WHERE code = 'en_GB') THEN
		INSERT INTO ad_traductions VALUES (d_tableliste_str,'en_GB','Number of women in the group');
	END IF;
END IF;

RETURN output_result;
END;
$BODY$
  LANGUAGE plpgsql VOLATILE
  COST 100;
ALTER FUNCTION at_96()
  OWNER TO postgres;

  SELECT at_96();
  DROP FUNCTION IF EXISTS at_96();
  ---------------------------------------------Fin Ticket AT-96----------------------------------------------------------------------


--------------------------------------------Ticket AT-97------------------------------------------------------------------------
CREATE OR REPLACE FUNCTION ticket_AT_97() RETURNS INT AS
$$
DECLARE
output_result INTEGER = 1;
  id_str_trad integer = 0;
  tableliste_ident integer = 0;

BEGIN

-- Check if table adsys_detail_objet_2 exist
IF NOT EXISTS(SELECT * FROM information_schema.tables WHERE table_name = 'adsys_detail_objet_2') THEN

CREATE TABLE adsys_detail_objet_2
(
  id serial NOT NULL,
  id_ag integer NOT NULL,
  libelle text,
  id_obj integer NOT NULL,
  CONSTRAINT adsys_detail_objet_2_pkey PRIMARY KEY (id, id_ag)
        )
WITH (
  OIDS=FALSE
);
  ALTER TABLE adsys_detail_objet_2
  OWNER TO postgres;
END IF;

  -- Insertion dans tableliste

        IF NOT EXISTS(SELECT * FROM tableliste WHERE nomc = 'adsys_detail_objet_2') THEN
        INSERT INTO tableliste VALUES ((select max(ident) from tableliste)+1, 'adsys_detail_objet_2', makeTraductionLangSyst('"Détail ojet de crédit 2"'), true);
        RAISE NOTICE 'Données table adsys_detail_objet_2 rajoutés dans table tableliste';
        END IF;

        tableliste_ident := (select ident from tableliste where nomc like 'adsys_detail_objet_2' order by ident desc limit 1);

		IF NOT EXISTS(SELECT * FROM d_tableliste WHERE tablen=tableliste_ident AND nchmpc = 'id') THEN
		INSERT INTO d_tableliste(ident, tablen, nchmpc, nchmpl, isreq, ref_field, type, onslct, ispkey, traduit) VALUES ((select max(ident) from d_tableliste)+1, tableliste_ident, 'id', maketraductionlangsyst('Identifiant table Détails demande de crédit 2'), true, NULL, 'int', false, true, false);
		END IF;

    IF NOT EXISTS(SELECT * FROM d_tableliste WHERE nchmpc = 'libelle' and tablen = tableliste_ident) THEN
      INSERT INTO d_tableliste VALUES ((select max(ident) from d_tableliste)+1, tableliste_ident, 'libelle', makeTraductionLangSyst('Libelle Objet de crédit 2'), true, NULL, 'txt', false, false, false);
    END IF;

        IF NOT EXISTS(SELECT * FROM d_tableliste WHERE tablen=tableliste_ident AND nchmpc = 'id_obj') THEN
		INSERT INTO d_tableliste(ident, tablen, nchmpc, nchmpl, isreq, ref_field, type, onslct, ispkey, traduit) VALUES ((select max(ident) from d_tableliste)+1, tableliste_ident, 'id_obj', maketraductionlangsyst('Objet de crédit'), true, NULL, 'lsb', true, false, false);
		END IF;


		-- Check if field "detail_obj_dem_2" exist in table "ad_dcr"
		IF NOT EXISTS(SELECT * FROM information_schema.columns WHERE table_name = 'ad_dcr' AND column_name = 'detail_obj_dem_2') THEN
		ALTER TABLE ad_dcr ADD COLUMN detail_obj_dem_2 integer;
		output_result := 2;
		END IF;

		-- Check if field "detail_obj_dem_2" exist in table "ad_dcr_grp_sol"
		IF NOT EXISTS(SELECT * FROM information_schema.columns WHERE table_name = 'ad_dcr_grp_sol' AND column_name = 'detail_obj_dem_2') THEN
		ALTER TABLE ad_dcr_grp_sol ADD COLUMN detail_obj_dem_2 integer;
		output_result := 2;
		END IF;


		RETURN output_result;
END;
$$
LANGUAGE plpgsql;

SELECT ticket_AT_97();
DROP FUNCTION ticket_AT_97();
----------------


--------------------------------------------Fion Ticket AT-97 ---------------------------------------------------------------

-------------------------------------------Fonction get_ad_dcr_ext_credit()--------------------------------------------------------
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
	cpte_cpta_prod_cr_frais text
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

        SELECT INTO dcr_credit  d.id_doss, d.id_client, d.id_prod, d.date_dem, d.mnt_dem, d.obj_dem, d.detail_obj_dem, d.etat, d.date_etat, d.motif, d.id_agent_gest, d.delai_grac, d.differe_jours, d.prelev_auto, d.duree_mois, d.nouv_duree_mois, d.terme, d.gar_num, d.gar_tot, d.gar_mat, d.gar_num_encours, d.cpt_gar_encours, d.num_cre, d.assurances_cre, d.cpt_liaison, d.cre_id_cpte, d.cre_etat, d.cre_date_etat, d.cre_date_approb, d.cre_date_debloc, d.cre_nbre_reech, d.cre_mnt_octr, d.details_motif, d.suspension_pen, d.perte_capital, d.cre_retard_etat_max, d.cre_retard_etat_max_jour, d.differe_ech, d.id_dcr_grp_sol, dx.gs_cat, d.prelev_commission, d.cpt_prelev_frais, d.id_ag, d.cre_prelev_frais_doss, d.prov_mnt, d.prov_date, d.prov_is_calcul, d.cre_mnt_deb, d.doss_repris, d.cre_cpt_att_deb, d.date_creation, d.date_modif, d.is_ligne_credit, d.deboursement_autorisee_lcr, d.motif_changement_authorisation_lcr, d.date_changement_authorisation_lcr, d.duree_nettoyage_lcr, d.remb_auto_lcr, d.tx_interet_lcr, d.taux_frais_lcr, d.taux_min_frais_lcr, d.taux_max_frais_lcr, d.ordre_remb_lcr, dx.mnt_assurance, dx.mnt_commission, d.mnt_frais_doss, d.detail_obj_dem_bis,d.detail_obj_dem_2, d.id_bailleur, d.is_extended, pc.id, pc.libel, dx.tx_interet, pc.mnt_min, pc.mnt_max, pc.mode_calc_int, pc.mode_perc_int, pc.duree_min_mois, pc.duree_max_mois, dx.periodicite, dx.mnt_frais, dx.prc_assurance, dx.prc_gar_num, pc.prc_gar_mat, pc.prc_gar_tot, pc.prc_gar_encours, pc.mnt_penalite_jour, pc.prc_penalite_retard, pc.delai_grace_jour, pc.differe_jours_max, pc.nbre_reechelon_auth, dx.prc_commission, pc.type_duree_credit, pc.approbation_obli, pc.typ_pen_pourc_dcr, pc.cpte_cpta_prod_cr_int, pc.cpte_cpta_prod_cr_gar, pc.cpte_cpta_prod_cr_pen, pc.devise, pc.differe_ech_max, pc.freq_paiement_cap, pc.max_jours_compt_penalite, pc.differe_epargne_nantie, pc.report_arrondi, pc.calcul_interet_differe, pc.prelev_frais_doss, pc.percep_frais_com_ass, pc.ordre_remb, pc.remb_cpt_gar, pc.is_produit_decouvert, dx.prc_frais, pc.cpte_cpta_att_deb, pc.is_produit_actif, pc.duree_nettoyage, pc.cpte_cpta_prod_cr_frais FROM ad_dcr d LEFT JOIN ad_dcr_ext dx ON d.id_doss = dx.id_doss AND d.id_ag = dx.id_ag INNER JOIN adsys_produit_credit pc ON d.id_prod = pc.id AND d.id_ag = pc.id_ag WHERE d.id_doss = ligne.id_doss AND d.id_ag = ligne.id_ag;

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

---------------------------------------------------Fin Fonction get_ad_dcr_ext_credit()----------------------------------------------------------------
---------------------------------------------Debut Ticket AT-39----------------------------------------------------------------------
CREATE OR REPLACE FUNCTION at_39() RETURNS INT AS
$BODY$
DECLARE
output_result INTEGER = 1;
tableliste_ident INTEGER = 0;
tableliste_str INTEGER = 0;
d_tableliste_str INTEGER = 0;

BEGIN

IF NOT EXISTS(select * from tableliste where nomc = 'ad_approvisionnement_delestage_attente') THEN
	INSERT INTO tableliste VALUES ((select max(ident) from tableliste)+1, 'ad_approvisionnement_delestage_attente', makeTraductionLangSyst('Approvisionnement/Delestage en Attente'), true);
END IF;

tableliste_ident := (select ident from tableliste where nomc like 'ad_approvisionnement_delestage_attente' order by ident desc limit 1);

IF NOT EXISTS(SELECT * FROM d_tableliste WHERE nchmpc = 'billetage' and tablen = tableliste_ident) THEN
	ALTER TABLE ad_approvisionnement_delestage_attente ADD COLUMN billetage TEXT;
	d_tableliste_str := makeTraductionLangSyst('Billetage');
	INSERT INTO d_tableliste VALUES ((select max(ident) from d_tableliste)+1, tableliste_ident, 'billetage', d_tableliste_str, false, NULL, 'txt', false, false, false);
	IF EXISTS(SELECT langue FROM adsys_langues_systeme WHERE code = 'en_GB') THEN
		INSERT INTO ad_traductions VALUES (d_tableliste_str,'en_GB','Ticketing');
	END IF;
END IF;

RETURN output_result;
END;
$BODY$
  LANGUAGE plpgsql VOLATILE
  COST 100;
ALTER FUNCTION at_39()
  OWNER TO postgres;

  SELECT at_39();
  DROP FUNCTION IF EXISTS at_39();
---------------------------------------------Fin Ticket AT-39----------------------------------------------------------------------