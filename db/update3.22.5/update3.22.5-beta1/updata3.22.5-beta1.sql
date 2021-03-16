------------------------------------- AT-184 -------------------------------
CREATE OR REPLACE FUNCTION calculnombrejoursretardech(integer,integer,date,integer) RETURNS double precision AS
		$BODY$
		DECLARE
		iddoss ALIAS FOR $1;
		idech ALIAS FOR $2;
		date_arrete ALIAS FOR $3;
		id_agence ALIAS FOR $4;
		max_date DATE;
		dateech DATE;
		isremb boolean;
		capital numeric(30,6);
		interet numeric(30,6);
		penalite numeric(30,6);
		capital_att numeric(30,6);
		interet_att numeric(30,6);
		penalite_att numeric(30,6);
		nbr_jours_retard DOUBLE PRECISION;
BEGIN
	SELECT INTO dateech,isremb date_ech, remb FROM ad_etr WHERE id_ag = id_agence AND id_doss = iddoss AND id_ech = idech;
	nbr_jours_retard := date_part('day', date_arrete::timestamp - dateech::timestamp);

	select into capital sum (mnt_remb_cap) from ad_sre where id_doss = iddoss and id_ech = idech and date_remb <= date_arrete;
	select into interet sum (mnt_remb_int) from ad_sre where id_doss = iddoss and id_ech = idech and date_remb <= date_arrete;

	select into capital_att mnt_cap from ad_etr where id_doss = iddoss and id_ech = idech;
	select into interet_att mnt_int from ad_etr where id_doss = iddoss and id_ech = idech;

	SELECT INTO max_date MAX(date_remb) FROM ad_sre WHERE id_ag = id_agence AND id_doss = iddoss AND id_ech = idech;
	IF ((capital = capital_att and interet >= interet_att)  OR ((max_date <= date_arrete) AND (isremb = 't'))) THEN
		nbr_jours_retard := 0;
	END IF;
RETURN nbr_jours_retard;
END;
$BODY$
LANGUAGE plpgsql VOLATILE
COST 100;
ALTER FUNCTION calculnombrejoursretardech(integer, integer, date, integer)
OWNER TO adbanking;


/*----------------- AT-188 Erreur de la gestion des comptes dormants ----------------------*/
CREATE OR REPLACE FUNCTION traitecomptesdormants(date,integer) RETURNS SETOF cpte_dormant AS
	$BODY$
	DECLARE
	date_batch  ALIAS FOR $1;		-- Date d'execution du batch
	idAgc ALIAS FOR $2;			    -- id de l'agence
	ligne_param_epargne RECORD ;
	ligne RECORD ;
	nbre_cptes INTEGER ;
	ligne_resultat cpte_dormant;

	BEGIN
	SELECT INTO ligne_param_epargne cpte_inactive_nbre_jour,cpte_inactive_frais_tenue_cpte
	FROM adsys_param_epargne
	WHERE id_ag = idAgc ;
	IF ligne_param_epargne.cpte_inactive_nbre_jour IS NOT NULL THEN

	DROP TABLE  IF EXISTS temp_ad_cpt_dormant;
	IF ligne_param_epargne.cpte_inactive_frais_tenue_cpte IS NULL OR ligne_param_epargne.cpte_inactive_frais_tenue_cpte=FALSE THEN

			CREATE TEMP TABLE temp_ad_cpt_dormant as
			SELECT
				id_cpte,
				id_titulaire,
				solde,
				c.devise,
				m.date_dernier_mvt_tenue_cpte,
				m.date_dernier_mvt,
				DATE(date_batch) - m.date_dernier_mvt as ecart
			FROM  ad_cpt b
				inner join adsys_produit_epargne c
					on b.id_prod = c.id
						 AND b.id_ag = c.id_ag
						 AND c.classe_comptable=1
						 AND c.retrait_unique =FALSE
						 AND c.depot_unique = FALSE
						 AND c.passage_etat_dormant = 'true'

				inner join (
										 select cpte_interne_cli, id_ag,
											 max(case when type_operation = 50 then date_valeur else null end ) as date_dernier_mvt_tenue_cpte,
											 max(date_valeur) as date_dernier_mvt
										 from ad_mouvement inner join ad_ecriture using (id_ecriture, id_ag)
										 group by cpte_interne_cli,id_ag
									 ) m

					on b.id_cpte = m.cpte_interne_cli
						 AND c.id_ag = m.id_ag
						 AND c.id_ag = idAgc
			where b.etat_cpte not in (2,4)
			and DATE(date_batch) - m.date_dernier_mvt >= ligne_param_epargne.cpte_inactive_nbre_jour;

	ELSE
			CREATE TEMP TABLE temp_ad_cpt_dormant as SELECT  id_cpte,id_titulaire,solde,c.devise
			FROM ad_mouvement a , ad_cpt b, adsys_produit_epargne c
			WHERE a.id_ag=b.id_ag AND a.id_ag=c.id_ag AND b.id_ag=c.id_ag AND c.id_ag = idAgc
			AND cpte_interne_cli = id_cpte AND b.id_prod = c.id  AND classe_comptable=1 AND c.retrait_unique =FALSE AND c.depot_unique = FALSE
			AND c.passage_etat_dormant = 'true'
			AND etat_cpte not in (2,4)
			GROUP BY id_cpte,id_titulaire ,solde,c.devise
			HAVING DATE(date_batch) -max(date_valeur) > ligne_param_epargne.cpte_inactive_nbre_jour ;
	END IF;

	UPDATE ad_cpt a SET  etat_cpte = 4,date_blocage= DATE(now()), raison_blocage = 'Compte dormant'
	WHERE id_cpte in  ( SELECT id_cpte FROM temp_ad_cpt_dormant);

	FOR ligne_resultat IN SELECT  * FROM temp_ad_cpt_dormant
	LOOP
	RETURN NEXT ligne_resultat;
	END LOOP;


ELSE
RETURN  ;
END IF ;
RETURN  ;
END;
$BODY$
LANGUAGE plpgsql VOLATILE
COST 100
ROWS 1000;
ALTER FUNCTION traitecomptesdormants(date, integer)
OWNER TO adbanking;

/*----------------- AT-200 Demande d'amelioration pour la module de credits  ----------------------*/
CREATE OR REPLACE FUNCTION script_at_200() RETURNS INT AS
$$
DECLARE
output_result INTEGER = 1;
BEGIN

	IF NOT EXISTS(SELECT * FROM information_schema.columns WHERE table_name = 'ad_dcr' AND column_name = 'cpt_remb') THEN
		ALTER TABLE ad_dcr ADD COLUMN cpt_remb INTEGER DEFAULT NULL;
	END IF;

RETURN output_result;
END;
$$
LANGUAGE plpgsql ;

SELECT script_at_200();
DROP FUNCTION script_at_200();

/*----------------- AT-106 Garanties numeraire a prelever lors du déboursement  ----------------------*/
CREATE OR REPLACE FUNCTION script_at_106() RETURNS INT AS $$
	DECLARE
	tablen_id INTEGER = 0;
	tableliste_prelev_id INTEGER = 0;
	output_result INTEGER = 1;
	BEGIN

	tableliste_prelev_id := (SELECT ident FROM tableliste WHERE nomc = 'adsys_produit_credit');

	IF NOT EXISTS(SELECT * FROM tableliste WHERE nomc = 'adsys_prelev_garanti') THEN
		INSERT INTO tableliste VALUES (
		(select max(ident) from tableliste)+1,
		'adsys_prelev_garanti',
		makeTraductionLangSyst('Prélèvement de garantie'),
		false);
		RAISE NOTICE 'Données table adsys_prelev_garanti rajoutés dans table tableliste';
	END IF;

	tablen_id := (SELECT ident FROM tableliste WHERE nomc = 'adsys_prelev_garanti');

	IF NOT EXISTS(SELECT * FROM d_tableliste WHERE nchmpc = 'id' and tablen = tablen_id) THEN
		INSERT INTO d_tableliste VALUES ((select max(ident)
		from d_tableliste)+1, tablen_id, 'id', makeTraductionLangSyst('Id'), TRUE, NULL, 'int', NULL, TRUE, FALSE);
	END IF;

	IF NOT EXISTS(SELECT * FROM d_tableliste WHERE nchmpc = 'prelev_garanti' and tablen = tableliste_prelev_id) THEN
		INSERT INTO d_tableliste VALUES ((select max(ident)
		from d_tableliste)+1, tableliste_prelev_id, 'prelev_garanti', makeTraductionLangSyst('Prélèvement de garantie'),
		null, (SELECT ident FROM d_tableliste WHERE nchmpc = 'id' and tablen = tablen_id), 'int', null, FALSE, FALSE);
	END IF;

	IF NOT EXISTS(SELECT * FROM information_schema.columns WHERE table_name = 'adsys_produit_credit' AND column_name = 'prelev_garanti') THEN
		ALTER TABLE adsys_produit_credit ADD COLUMN prelev_garanti INTEGER DEFAULT 1;
	END IF;

	RETURN output_result;
	END;
	$$
	LANGUAGE plpgsql ;

SELECT script_at_106();
DROP FUNCTION script_at_106();

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
		prelev_garanti INTEGER,
		appl_interet_diff_echeance INTEGER,
		diff_ech_apres_deb INTEGER,
	  cpt_remb INTEGER
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
	pc.cpte_cpta_att_deb, pc.is_produit_actif, pc.duree_nettoyage, pc.cpte_cpta_prod_cr_frais, pc.prelev_garanti, pc.appl_interet_diff_echeance, d.diff_ech_apres_deb, d.cpt_remb FROM ad_dcr d LEFT JOIN ad_dcr_ext dx ON d.id_doss = dx.id_doss
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
	pc.is_produit_actif, pc.duree_nettoyage, pc.cpte_cpta_prod_cr_frais, pc.prelev_garanti, pc.appl_interet_diff_echeance, d.diff_ech_apres_deb, d.cpt_remb FROM ad_dcr d LEFT JOIN adsys_produit_credit pc
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

----------- AT 205: Amelioration : Debloquer le montant  utiliser lors de la remboursement de credit ---------------
CREATE OR REPLACE FUNCTION script_at_205() RETURNS INT AS
$$
DECLARE
output_result INTEGER = 1;
id_str_trad integer = 0;
	BEGIN

		IF NOT EXISTS (select * from menus where nom_menu = 'Dlm') THEN
		id_str_trad := maketraductionlangsyst('Débloquer les montant');
		INSERT INTO menus (nom_menu, libel_menu, nom_pere, pos_hierarch, ordre, is_menu,fonction, is_cliquable)
		VALUES ('Dlm', id_str_trad, 'Gen-10', 5, 16, TRUE, 52, TRUE);
		IF EXISTS(SELECT langue FROM adsys_langues_systeme WHERE code = 'en_GB') THEN
		INSERT INTO ad_traductions (id_str, langue, traduction) VALUES (id_str_trad, 'en_GB', 'Unlock amounts');
		END IF;
		END IF;

		-----------------------------------------------------------------------------------------------------------------------------

		IF NOT EXISTS (select * from menus where nom_menu = 'Dlm-1') THEN
		id_str_trad := maketraductionlangsyst('Choix du compte');
		INSERT INTO menus (nom_menu, libel_menu, nom_pere, pos_hierarch, ordre, is_menu,fonction, is_cliquable)
		VALUES ('Dlm-1', maketraductionlangsyst('Choix du compte'), 'Dlm', 6, 1, FALSE, 52, FALSE);
		IF EXISTS(SELECT langue FROM adsys_langues_systeme WHERE code = 'en_GB') THEN
		INSERT INTO ad_traductions (id_str, langue, traduction) VALUES (maketraductionlangsyst('Choix du compte '), 'en_GB', 'Account choice ');
		END IF;
		END IF;

		IF NOT EXISTS (select * from ecrans where nom_ecran = 'Dlm-1') THEN
		INSERT INTO ecrans(nom_ecran, fichier, nom_menu, fonction)
		VALUES ('Dlm-1', 'modules/epargne/debloq_montant_credit.php', 'Dlm-1', 52);
		END IF;

		-----------------------------------------------------------------------------------------------------------------------------

		IF NOT EXISTS (select * from menus where nom_menu = 'Dlm-2') THEN
		id_str_trad := maketraductionlangsyst('Confirmation   ');
		INSERT INTO menus (nom_menu, libel_menu, nom_pere, pos_hierarch, ordre, is_menu,fonction, is_cliquable)
		VALUES ('Dlm-2', id_str_trad, 'Dlm', 6, 2, FALSE, 52, FALSE);
		IF EXISTS(SELECT langue FROM adsys_langues_systeme WHERE code = 'en_GB') THEN
		INSERT INTO ad_traductions (id_str, langue, traduction) VALUES (id_str_trad, 'en_GB', 'Confirmation   ');
		END IF;
		END IF;

		IF NOT EXISTS (select * from ecrans where nom_ecran = 'Dlm-2') THEN
		INSERT INTO ecrans(nom_ecran, fichier, nom_menu, fonction)
		VALUES ('Dlm-2', 'modules/epargne/debloq_montant_credit.php', 'Dlm-2', 52);
		END IF;

		-----------------------------------------------------------------------------------------------------------------------------

		IF NOT EXISTS (select * from ecrans where nom_ecran = 'Dlm-3') THEN
		INSERT INTO ecrans(nom_ecran, fichier, nom_menu, fonction)
		VALUES ('Dlm-3', 'modules/epargne/debloq_montant_credit.php', 'Dlm-2', 52);
		END IF;

	RETURN output_result;
END;
$$
LANGUAGE plpgsql ;

SELECT script_at_205();
DROP FUNCTION script_at_205();


--------------------------------- AT-198 Remboursement de crédit par lot ---------------------------------------
CREATE OR REPLACE FUNCTION script_at_198() RETURNS INT AS
		$$
		DECLARE
		output_result INTEGER = 1;
		id_str_trad integer = 0;

		BEGIN

		IF NOT EXISTS (select * from menus where nom_menu = 'Rpl') THEN
		id_str_trad := maketraductionlangsyst('Remboursement de crédit par lot');
		INSERT INTO menus (nom_menu, libel_menu, nom_pere, pos_hierarch, ordre, is_menu,fonction, is_cliquable)
		VALUES ('Rpl', id_str_trad, 'Gen-6', 3, 29, TRUE, 806, TRUE);
		IF EXISTS(SELECT langue FROM adsys_langues_systeme WHERE code = 'en_GB') THEN
		INSERT INTO ad_traductions (id_str, langue, traduction) VALUES (id_str_trad, 'en_GB', 'Batch credit repayment');
		END IF;
		END IF;


		IF NOT EXISTS (select * from menus where nom_menu = 'Rpl-1') THEN
		id_str_trad := maketraductionlangsyst('Demande de confirmation');
		INSERT INTO menus (nom_menu, libel_menu, nom_pere, pos_hierarch, ordre, is_menu,fonction, is_cliquable)
		VALUES ('Rpl-1', id_str_trad, 'Rpl', 4, 1, FALSE, null, FALSE);
		IF EXISTS(SELECT langue FROM adsys_langues_systeme WHERE code = 'en_GB') THEN
		INSERT INTO ad_traductions (id_str, langue, traduction) VALUES (id_str_trad, 'en_GB', 'Confirmation request');
		END IF;
		END IF;


		IF NOT EXISTS (select * from ecrans where nom_ecran = 'Rpl-1') THEN
		INSERT INTO ecrans(nom_ecran, fichier, nom_menu, fonction)
		VALUES ('Rpl-1', 'modules/credit/remboursement_batch_credit.php', 'Rpl-1', 806);
		END IF;


		IF NOT EXISTS (select * from menus where nom_menu = 'Rpl-2') THEN
		id_str_trad := maketraductionlangsyst('Resultat');
		INSERT INTO menus (nom_menu, libel_menu, nom_pere, pos_hierarch, ordre, is_menu,fonction, is_cliquable)
		VALUES ('Rpl-2', id_str_trad, 'Rpl', 4, 2, FALSE, null, FALSE);
		IF EXISTS(SELECT langue FROM adsys_langues_systeme WHERE code = 'en_GB') THEN
		INSERT INTO ad_traductions (id_str, langue, traduction) VALUES (id_str_trad, 'en_GB', 'Results');
		END IF;
		END IF;


		IF NOT EXISTS (select * from ecrans where nom_ecran = 'Rpl-2') THEN
		INSERT INTO ecrans(nom_ecran, fichier, nom_menu, fonction)
		VALUES ('Rpl-2', 'modules/credit/remboursement_batch_credit.php', 'Rpl-2', 806);
		END IF;

		RETURN output_result;
		END;
		$$
		LANGUAGE plpgsql;

SELECT script_at_198();
DROP FUNCTION script_at_198();

----------------------------- AT-207 Plafond dépôt Ewalet -------------------------------
CREATE OR REPLACE FUNCTION script_AT_207() RETURNS INT AS
	$$
	DECLARE
		output_result INTEGER = 1;
		d_tableliste_str integer = 0;
		tableliste_ident integer = 0;

	BEGIN

	tableliste_ident := (select ident from tableliste where nomc like 'ad_agc' order by ident desc limit 1);

	IF NOT EXISTS (SELECT * FROM information_schema.columns WHERE table_name='ad_agc' and column_name='mnt_depot_ewallet_max_journalier') THEN
			ALTER TABLE ad_agc ADD COLUMN mnt_depot_ewallet_max_journalier numeric(30,6);
			d_tableliste_str := makeTraductionLangSyst('Montant journalier maximal sur dépôt Ewallet');
			INSERT INTO d_tableliste VALUES ((select max(ident) from d_tableliste)+1, tableliste_ident, 'mnt_depot_ewallet_max_journalier', d_tableliste_str, NULL, NULL, 'mnt', false, false, false);
			IF EXISTS(SELECT langue FROM adsys_langues_systeme WHERE code = 'en_GB') THEN
				INSERT INTO ad_traductions VALUES (d_tableliste_str,'en_GB','Maximum daily amount on Ewallet deposit');
			END IF;
	END IF;

	IF EXISTS (SELECT * FROM information_schema.columns WHERE table_name='ad_cli' and column_name='mnt_limit_ewallet_jounalier') THEN
			ALTER TABLE ad_cli RENAME COLUMN mnt_limit_ewallet_jounalier TO mnt_retrait_limit_ewallet_jounalier;
	END IF;

	IF EXISTS (SELECT * FROM information_schema.columns WHERE table_name='ad_cli' and column_name='date_limit_ewallet_jounalier') THEN
			ALTER TABLE ad_cli RENAME COLUMN date_limit_ewallet_jounalier TO date_retrait_limit_ewallet_jounalier;
	END IF;

	IF NOT EXISTS (SELECT * FROM information_schema.columns WHERE table_name='ad_cli' and column_name='mnt_depot_limit_ewallet_jounalier') THEN
			ALTER TABLE ad_cli ADD COLUMN mnt_depot_limit_ewallet_jounalier numeric(30,6);
	END IF;

	IF NOT EXISTS (SELECT * FROM information_schema.columns WHERE table_name='ad_cli' and column_name='date_depot_limit_ewallet_jounalier') THEN
			ALTER TABLE ad_cli ADD COLUMN date_depot_limit_ewallet_jounalier date;
	END IF;

	RETURN output_result;
	END;

	$$
	LANGUAGE plpgsql;

SELECT script_AT_207();
DROP FUNCTION script_AT_207();
