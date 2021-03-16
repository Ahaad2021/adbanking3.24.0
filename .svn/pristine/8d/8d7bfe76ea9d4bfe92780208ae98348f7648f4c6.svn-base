-- #301 - Creation des ecrans du rapport de recouvrement des credits

CREATE OR REPLACE FUNCTION patch_ticket_301() RETURNS integer AS
$BODY$
BEGIN

IF NOT EXISTS (SELECT * FROM ecrans WHERE nom_ecran = 'Kra-94') THEN
INSERT INTO ecrans(nom_ecran, fichier, nom_menu, fonction)
VALUES ('Kra-94','modules/rapports/rapports_credit.php','Kra-2',350);
END IF;

IF NOT EXISTS (SELECT * FROM ecrans WHERE nom_ecran = 'Kra-95') THEN
INSERT INTO ecrans(nom_ecran, fichier, nom_menu, fonction)
VALUES ('Kra-95','modules/rapports/rapports_credit.php','Kra-3',350);
END IF;

IF NOT EXISTS (SELECT * FROM ecrans WHERE nom_ecran = 'Kra-96') THEN
INSERT INTO ecrans(nom_ecran, fichier, nom_menu, fonction)
VALUES ('Kra-96','modules/rapports/rapports_credit.php','Kra-5',350);
END IF;

return 1;

END;
$BODY$
LANGUAGE plpgsql VOLATILE COST 100;
ALTER FUNCTION patch_ticket_301()
OWNER TO adbanking;

SELECT patch_ticket_301();
DROP FUNCTION patch_ticket_301();


-- Ticket #308

CREATE OR REPLACE FUNCTION patch_ticket_308_fix()  RETURNS INT AS
$$
DECLARE
output_result INTEGER = 1;
tableliste_ident INTEGER = 0;

BEGIN

	RAISE NOTICE 'START';

	IF EXISTS(SELECT * FROM d_tableliste WHERE nchmpc = 'id_mag') THEN
	 DELETE FROM d_tableliste WHERE nchmpc = 'id_mag';
	 RAISE NOTICE 'Suppression id_mag de la table d_tableliste effectuée';
	 output_result := 2;
	END IF;

	IF EXISTS(SELECT * FROM d_tableliste WHERE nchmpc = 'id_agc') THEN
	 DELETE FROM d_tableliste WHERE nchmpc = 'id_agc';
	 RAISE NOTICE 'Suppression id_agc de la table d_tableliste effectuée';
	 output_result := 2;
	END IF;

	IF EXISTS(SELECT * FROM d_tableliste WHERE nchmpc = 'compte_liaison') THEN
	 DELETE FROM d_tableliste WHERE nchmpc = 'compte_liaison';
	 RAISE NOTICE 'Suppression compte_liaison de la table d_tableliste effectuée';
	 output_result := 2;
	END IF;

	IF EXISTS(SELECT * FROM d_tableliste WHERE nchmpc = 'compte_avoir') THEN
	 DELETE FROM d_tableliste WHERE nchmpc = 'compte_avoir';
	 RAISE NOTICE 'Suppression compte_avoir de la table d_tableliste effectuée';
	 output_result := 2;
	END IF;

	IF EXISTS(SELECT * FROM d_tableliste WHERE nchmpc = 'is_agence_siege') THEN
	 DELETE FROM d_tableliste WHERE nchmpc = 'is_agence_siege';
	 RAISE NOTICE 'Suppression is_agence_siege de la table d_tableliste effectuée';
	 output_result := 2;
	END IF;

	IF EXISTS(SELECT * FROM d_tableliste WHERE nchmpc = 'app_db_description') THEN
	 DELETE FROM d_tableliste WHERE nchmpc = 'app_db_description';
	 RAISE NOTICE 'Suppression app_db_description de la table d_tableliste effectuée';
	 output_result := 2;
	END IF;

	IF EXISTS(SELECT * FROM d_tableliste WHERE nchmpc = 'app_db_host') THEN
	 DELETE FROM d_tableliste WHERE nchmpc = 'app_db_host';
	 RAISE NOTICE 'Suppression app_db_host de la table d_tableliste effectuée';
	 output_result := 2;
	END IF;

	IF EXISTS(SELECT * FROM d_tableliste WHERE nchmpc = 'app_db_port') THEN
	 DELETE FROM d_tableliste WHERE nchmpc = 'app_db_port';
	 RAISE NOTICE 'Suppression app_db_port de la table d_tableliste effectuée';
	 output_result := 2;
	END IF;

	IF EXISTS(SELECT * FROM d_tableliste WHERE nchmpc = 'app_db_name') THEN
	 DELETE FROM d_tableliste WHERE nchmpc = 'app_db_name';
	 RAISE NOTICE 'Suppression app_db_name de la table d_tableliste effectuée';
	 output_result := 2;
	END IF;

	IF EXISTS(SELECT * FROM d_tableliste WHERE nchmpc = 'app_db_username') THEN
	 DELETE FROM d_tableliste WHERE nchmpc = 'app_db_username';
	 RAISE NOTICE 'Suppression app_db_username de la table d_tableliste effectuée';
	 output_result := 2;
	END IF;

	IF EXISTS(SELECT * FROM d_tableliste WHERE nchmpc = 'app_db_password') THEN
	 DELETE FROM d_tableliste WHERE nchmpc = 'app_db_password';
	 RAISE NOTICE 'Suppression app_db_password de la table d_tableliste effectuée';
	 output_result := 2;
	END IF;

	IF EXISTS(SELECT * FROM tableliste WHERE nomc = 'adsys_multi_agence') THEN
	 DELETE FROM tableliste WHERE nomc = 'adsys_multi_agence';
	 RAISE NOTICE 'Suppression adsys_multi_agence de la table tableliste effectuée';
	 output_result := 2;
	END IF;

	IF EXISTS(SELECT * FROM information_schema.tables WHERE table_name = 'adsys_multi_agence') THEN
	 DROP TABLE adsys_multi_agence;
	 RAISE NOTICE 'Suppression table adsys_multi_agence effectuée';
	 output_result := 2;
	END IF;
	
	CREATE TABLE adsys_multi_agence (
		id_mag serial,
		id_agc integer NOT NULL,
		compte_liaison text,
		compte_avoir text,
		is_agence_siege boolean DEFAULT false,
		app_db_description text,
		app_db_host character varying(50),
		app_db_port character varying(10),
		app_db_name character varying(50),
		app_db_username character varying(50),
		app_db_password character varying(50),
		id_ag integer NOT NULL,
		CONSTRAINT adsys_multi_agence_pkey PRIMARY KEY (id_mag)
	)
	WITH (
	  OIDS=FALSE
	);
	
	INSERT INTO tableliste(ident, nomc, noml, is_table) VALUES ((select max(ident) from tableliste)+1, 'adsys_multi_agence', maketraductionlangsyst('Table multi agences'), true);
	
	tableliste_ident := (select ident from tableliste where nomc like 'adsys_multi_agence' order by ident desc limit 1);
	
	INSERT INTO d_tableliste(ident, tablen, nchmpc, nchmpl, isreq, ref_field, type, onslct, ispkey, traduit) VALUES ((select max(ident) from d_tableliste)+1, tableliste_ident, 'id_mag', maketraductionlangsyst('Identifiant de la table'), true, NULL, 'int', false, true, false);
	INSERT INTO d_tableliste(ident, tablen, nchmpc, nchmpl, isreq, ref_field, type, onslct, ispkey, traduit) VALUES ((select max(ident) from d_tableliste)+1, tableliste_ident, 'id_agc', maketraductionlangsyst('Identifiant de l''agence'), true, NULL, 'int', false, false, false);
	INSERT INTO d_tableliste(ident, tablen, nchmpc, nchmpl, isreq, ref_field, type, onslct, ispkey, traduit) VALUES ((select max(ident) from d_tableliste)+1, tableliste_ident, 'compte_liaison', maketraductionlangsyst('Compte de liaison'), false, (select ident from d_tableliste where nchmpc like 'num_cpte_comptable' limit 1), 'txt', false, false, false);
	INSERT INTO d_tableliste(ident, tablen, nchmpc, nchmpl, isreq, ref_field, type, onslct, ispkey, traduit) VALUES ((select max(ident) from d_tableliste)+1, tableliste_ident, 'compte_avoir', maketraductionlangsyst('Compte avoir'), false, (select ident from d_tableliste where nchmpc like 'num_cpte_comptable' limit 1), 'txt', false, false, false);
	INSERT INTO d_tableliste(ident, tablen, nchmpc, nchmpl, isreq, ref_field, type, onslct, ispkey, traduit) VALUES ((select max(ident) from d_tableliste)+1, tableliste_ident, 'is_agence_siege', maketraductionlangsyst('Agence siège?'), false, NULL, 'bol', false, false, false);
	INSERT INTO d_tableliste(ident, tablen, nchmpc, nchmpl, isreq, ref_field, type, onslct, ispkey, traduit) VALUES ((select max(ident) from d_tableliste)+1, tableliste_ident, 'app_db_description', maketraductionlangsyst('Nom de l''agence'), true, NULL, 'txt', false, false, false);
	INSERT INTO d_tableliste(ident, tablen, nchmpc, nchmpl, isreq, ref_field, type, onslct, ispkey, traduit) VALUES ((select max(ident) from d_tableliste)+1, tableliste_ident, 'app_db_host', maketraductionlangsyst('Adresse IP du serveur'), true, NULL, 'txt', false, false, false);
	INSERT INTO d_tableliste(ident, tablen, nchmpc, nchmpl, isreq, ref_field, type, onslct, ispkey, traduit) VALUES ((select max(ident) from d_tableliste)+1, tableliste_ident, 'app_db_port', maketraductionlangsyst('Numéro de port du serveur'), true, NULL, 'txt', false, false, false);
	INSERT INTO d_tableliste(ident, tablen, nchmpc, nchmpl, isreq, ref_field, type, onslct, ispkey, traduit) VALUES ((select max(ident) from d_tableliste)+1, tableliste_ident, 'app_db_name', maketraductionlangsyst('Nom de la base de données'), true, NULL, 'txt', false, false, false);
	INSERT INTO d_tableliste(ident, tablen, nchmpc, nchmpl, isreq, ref_field, type, onslct, ispkey, traduit) VALUES ((select max(ident) from d_tableliste)+1, tableliste_ident, 'app_db_username', maketraductionlangsyst('Nom utilisateur de la base de données'), true, NULL, 'txt', false, false, false);
	INSERT INTO d_tableliste(ident, tablen, nchmpc, nchmpl, isreq, ref_field, type, onslct, ispkey, traduit) VALUES ((select max(ident) from d_tableliste)+1, tableliste_ident, 'app_db_password', maketraductionlangsyst('Mot de passe de la base de données'), true, NULL, 'txt', false, false, false);

	RETURN output_result;

END;
$$
LANGUAGE plpgsql;

CREATE OR REPLACE FUNCTION update_3_8_fix()  RETURNS INT AS
$$
DECLARE
	output_result INTEGER = 1;

	menu_arr varchar[][] := '{{Mec, Modification de l''échéancier crédit, Gen-11, 5, 6, t, 136, t}, {Mdr-1, Demande modification date, Mec, 6, 1, f, NULL, f}, {Amd-1, Approbation modification date, Mec, 6, 2, f, NULL, f}, {Rdc-1, Demande raccourcissement, Mec, 6, 3, f, NULL, f}, {Ard-1, Approbation raccourcissement, Mec, 6, 4, f, NULL, f}}'; -- , {Adc-1, Demande rééchelonnement, Mec, 6, 5, f, NULL, f}

	ecran_arr varchar[][] := '{{Mec-1, modules/credit/modechdcr.php, Mec, 136}, {Mdr-1, modules/credit/moddateremb.php, Mdr-1, 141}, {Mdr-2, modules/credit/moddateremb.php, Mdr-1, 141}, {Mdr-3, modules/credit/moddateremb.php, Mdr-1, 141}, {Mdr-4, modules/credit/moddateremb.php, Mdr-1, 141}, {Amd-1, modules/credit/approbdateremb.php, Amd-1, 142}, {Amd-2, modules/credit/approbdateremb.php, Amd-1, 142}, {Amd-3, modules/credit/approbdateremb.php, Amd-1, 142}, {Amd-4, modules/credit/approbdateremb.php, Amd-1, 142}, {Rdc-1, modules/credit/raccourciduree.php, Rdc-1, 143}, {Rdc-2, modules/credit/raccourciduree.php, Rdc-1, 143}, {Rdc-3, modules/credit/raccourciduree.php, Rdc-1, 143}, {Rdc-4, modules/credit/raccourciduree.php, Rdc-1, 143}, {Ard-1, modules/credit/approbraccourciduree.php, Ard-1, 144}, {Ard-2, modules/credit/approbraccourciduree.php, Ard-1, 144}, {Ard-3, modules/credit/approbraccourciduree.php, Ard-1, 144}, {Ard-4, modules/credit/approbraccourciduree.php, Ard-1, 144}}';

	BEGIN

	RAISE NOTICE 'START';
	
	-- Delete table rpt_recouvrement
	IF EXISTS (SELECT * FROM information_schema.tables WHERE TABLE_name = 'rpt_recouvrement') THEN
       DROP TABLE rpt_recouvrement;
	END IF;

	-- Delete Screens
	FOR i IN array_lower(ecran_arr, 1) .. array_upper(ecran_arr, 1)
	LOOP
		IF EXISTS(SELECT * FROM ecrans WHERE nom_ecran = ecran_arr[i][1]) THEN
			DELETE FROM ecrans WHERE nom_ecran = ecran_arr[i][1];
			RAISE NOTICE 'Supprimer ecran % de la table ecrans effectuée', ecran_arr[i][1];
			output_result := 2;
		END IF;
	END LOOP;

	-- Delete Menus
	FOR i IN array_lower(menu_arr, 1) .. array_upper(menu_arr, 1)
	LOOP
		IF EXISTS(SELECT * FROM menus WHERE nom_menu = menu_arr[i][1]) THEN
			DELETE FROM menus WHERE nom_menu = menu_arr[i][1];
			RAISE NOTICE 'Supprimer menu % de la table menus effectuée', menu_arr[i][1];
			output_result := 2;
		END IF;
	END LOOP;

	-- Create Menus
	FOR i IN array_lower(menu_arr, 1) .. array_upper(menu_arr, 1)
	LOOP
		INSERT INTO menus(nom_menu, libel_menu, nom_pere, pos_hierarch, ordre, is_menu,fonction, is_cliquable) VALUES (menu_arr[i][1], CAST(maketraductionlangsyst(menu_arr[i][2]) AS integer), menu_arr[i][3], CAST(menu_arr[i][4] AS smallint), CAST(menu_arr[i][5] AS smallint), CAST(menu_arr[i][6] AS boolean), CAST(menu_arr[i][7] AS smallint), CAST(menu_arr[i][8] AS boolean));
		RAISE NOTICE 'Insérer menu % de la table menus effectuée', menu_arr[i][1];
	END LOOP;

	-- Create Screens
	FOR i IN array_lower(ecran_arr, 1) .. array_upper(ecran_arr, 1)
	LOOP
		INSERT INTO ecrans(nom_ecran, fichier, nom_menu, fonction) VALUES (ecran_arr[i][1], ecran_arr[i][2], ecran_arr[i][3], CAST(ecran_arr[i][4] AS integer));
		RAISE NOTICE 'Insérer ecran % de la table ecrans effectuée', ecran_arr[i][1];
	END LOOP;

	-- Tables
	IF EXISTS(SELECT * FROM information_schema.tables WHERE table_name = 'ad_dcr_his') THEN
	 DROP TABLE ad_dcr_his;
	 RAISE NOTICE 'Suppression table ad_dcr_his effectuée';
	 output_result := 2;
	END IF;
	CREATE TABLE ad_dcr_his
	(
	  id_dcr_his serial,
	  id_doss integer NOT NULL,
	  mod_type integer NOT NULL,
	  id_ech integer NULL,
	  ech_date_dem timestamp without time zone NULL,
	  reech_duree integer NULL,
	  approb_date timestamp without time zone NULL,
	  approb_flag boolean DEFAULT false,
	  date_crea timestamp without time zone,
	  date_modif timestamp without time zone NULL,
	  nom_login text,
	  id_ag integer NOT NULL,
	  CONSTRAINT ad_dcr_his_pkey PRIMARY KEY (id_dcr_his)
	)
	WITH (
	  OIDS=FALSE
	);
	COMMENT ON TABLE ad_dcr_his
	  IS 'Dossiers historisation';
	COMMENT ON COLUMN ad_dcr_his.mod_type IS '1 - modif.date, 2 - raccourcir durée, 3 - allonger durée';

	IF EXISTS(SELECT * FROM information_schema.tables WHERE table_name = 'ad_etr_his') THEN
	 DROP TABLE ad_etr_his;
	 RAISE NOTICE 'Suppression table ad_etr_his effectuée';
	 output_result := 2;
	END IF;

	CREATE TABLE ad_etr_his
	(
	  id_etr_his serial,
	  id_dcr_his integer NOT NULL,
	  id_doss integer NOT NULL,
	  id_ech integer NOT NULL,
	  ech_date timestamp without time zone,
	  mnt_cap numeric(30,6) DEFAULT 0,
	  mnt_int numeric(30,6) DEFAULT 0,
	  mnt_gar numeric(30,6) DEFAULT 0,
	  mnt_reech numeric(30,6) DEFAULT 0,
	  solde_cap numeric(30,6) DEFAULT 0,
	  solde_int numeric(30,6) DEFAULT 0,
	  solde_gar numeric(30,6) DEFAULT 0,
	  solde_pen numeric(30,6) DEFAULT 0,
	  nom_login text,
	  id_ag integer NOT NULL,
	  CONSTRAINT ad_etr_his_pkey PRIMARY KEY (id_etr_his)
	)
	WITH (
	  OIDS=FALSE
	);
	COMMENT ON TABLE ad_etr_his
	  IS 'Echéanciers théoriques historisation';

	RETURN output_result;

END;
$$
LANGUAGE plpgsql;

SELECT patch_ticket_308_fix();
DROP FUNCTION patch_ticket_308_fix();

SELECT update_3_8_fix();
DROP FUNCTION update_3_8_fix();

-- #340
-- Ajout opération 411 : Annulation recouvrement sur crédit en perte
CREATE OR REPLACE FUNCTION fix_ticket_340() RETURNS integer AS $BODY$
BEGIN

IF NOT EXISTS (SELECT type_operation FROM ad_cpt_ope WHERE type_operation = 411) THEN
INSERT INTO ad_cpt_ope (type_operation, libel_ope, categorie_ope, id_ag) 
VALUES (411, maketraductionlangsyst('Annulation recouvrement sur crédit en perte'), 1, NumAgc());

INSERT INTO ad_cpt_ope_cptes VALUES (411,NULL, 'c', 1, NumAgc());

INSERT INTO ad_cpt_ope_cptes (type_operation, num_cpte, sens, categorie_cpte, id_ag)
VALUES (411, (SELECT num_cpte FROM ad_cpt_ope_cptes WHERE type_operation = 410 AND sens = 'c' AND id_ag = NumAgc() LIMIT 1), 
'd', 7, NumAgc());

RAISE NOTICE 'Type opération 411 ajouté.';
RETURN 1;

ELSE
 RAISE NOTICE 'Type opération 411 existe déjà. Ajout annulé !';
 RETURN 0;
END IF;

END;
$BODY$

LANGUAGE plpgsql VOLATILE COST 100;
ALTER FUNCTION fix_ticket_340() OWNER TO adbanking;

SELECT fix_ticket_340();
DROP FUNCTION fix_ticket_340();