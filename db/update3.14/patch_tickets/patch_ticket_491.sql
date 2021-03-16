CREATE OR REPLACE FUNCTION patch_multi_agence_3bases() RETURNS INT AS
$$
DECLARE
output_result INTEGER = 1;
tableliste_ident INTEGER = (select ident from tableliste where nomc like 'adsys_multi_agence' order by ident desc limit 1);

BEGIN

	RAISE NOTICE 'START';
	
	-- FIX Multi-agence fields
	UPDATE d_tableliste SET ref_field=1400 WHERE tablen=tableliste_ident AND nchmpc='compte_liaison';
	UPDATE d_tableliste SET ref_field=1400 WHERE tablen=tableliste_ident AND nchmpc='compte_avoir';

	-- ADD COLUMNS
	IF NOT EXISTS(SELECT column_name FROM information_schema.columns WHERE table_name = 'adsys_multi_agence' AND column_name = 'app_max_id_audit_ma') THEN
		ALTER TABLE adsys_multi_agence ADD COLUMN app_max_id_audit_ma integer DEFAULT 0 NULL;
		
		RAISE NOTICE 'Column app_max_id_audit_ma added in table adsys_multi_agence';
		output_result := 2;
	END IF;
	
	IF NOT EXISTS(SELECT column_name FROM information_schema.columns WHERE table_name='adsys_audit_multi_agence' and column_name='code_devise_montant') THEN
		ALTER TABLE ONLY adsys_audit_multi_agence ADD COLUMN code_devise_montant character(3) NULL;

		RAISE NOTICE 'Column code_devise_montant added in table adsys_audit_multi_agence';
		output_result := 2;
	END IF;
	
	IF NOT EXISTS(SELECT column_name FROM information_schema.columns WHERE table_name='adsys_audit_multi_agence' and column_name='commission') THEN
		ALTER TABLE ONLY adsys_audit_multi_agence ADD COLUMN commission numeric(30,6) DEFAULT 0;

		RAISE NOTICE 'Column commission added in table adsys_audit_multi_agence';
		output_result := 2;
	END IF;
	
	IF NOT EXISTS(SELECT column_name FROM information_schema.columns WHERE table_name='adsys_audit_multi_agence' and column_name='code_devise_commission') THEN
		ALTER TABLE ONLY adsys_audit_multi_agence ADD COLUMN code_devise_commission character(3) NULL;

		RAISE NOTICE 'Column code_devise_commission added in table adsys_audit_multi_agence';
		output_result := 2;
	END IF;

	-- CREATE TABLE adsys_job_externe
	IF NOT EXISTS(SELECT * FROM information_schema.tables WHERE table_name = 'adsys_job_externe') THEN
		CREATE TABLE adsys_job_externe
		(
			nom_job character varying(255) NULL,
			id_ag integer NOT NULL,
			dernier_traitement timestamp without time zone,
			statut character varying(100) NULL,
			CONSTRAINT ad_mobile_service_pkey PRIMARY KEY (nom_job, id_ag)
		)
		WITH (
		  OIDS=FALSE
		);

		RAISE NOTICE 'Table adsys_job_externe created';
		output_result := 2;
	END IF;
	
	-- CREATE TABLE log_multiagence
	IF NOT EXISTS(SELECT * FROM information_schema.tables WHERE table_name = 'log_multiagence') THEN
		CREATE TABLE log_multiagence
		(
		  id integer,
		  date timestamp(0) without time zone,
		  nom_projet character varying(50),
		  nom_job character varying(255),
		  nom_composant character varying(255),
		  message character varying(255)
		)
		WITH (
		  OIDS=FALSE
		);

		RAISE NOTICE 'Table log_multiagence created';
		output_result := 2;
	END IF;

	-- Truncate table adsys_job_externe
	IF (SELECT COUNT(*) FROM adsys_job_externe) > 0 THEN

		-- Empty table
		TRUNCATE TABLE adsys_job_externe RESTART IDENTITY CASCADE;

		RAISE NOTICE 'Truncate table adsys_job_externe effectuée';
		output_result := 2;
	END IF;

	-- ----------------------------
	-- Records of adsys_job_externe
	-- ----------------------------
	INSERT INTO adsys_job_externe (nom_job, id_ag, dernier_traitement, statut) VALUES ('COMPENSATION_MA', numagc(), NOW(), NULL); -- 'TERMINE'

	-- DROP TABLE ad_multi_agence_compensation
	IF EXISTS(SELECT * FROM information_schema.tables WHERE table_name = 'ad_multi_agence_compensation') THEN
		DROP TABLE ad_multi_agence_compensation;
		RAISE NOTICE 'Suppression table ad_multi_agence_compensation effectuée';
	END IF;

	-- CREATE TABLE ad_multi_agence_compensation
	IF NOT EXISTS(SELECT * FROM information_schema.tables WHERE table_name = 'ad_multi_agence_compensation') THEN
		CREATE TABLE ad_multi_agence_compensation
		(
			id serial NOT NULL,
			id_audit_agc integer NOT NULL,
			date_crea timestamp without time zone,
			date_maj timestamp without time zone,
			id_ag_local integer NOT NULL,
			id_ag_distant integer NOT NULL,
			nom_login character(80) NOT NULL,			
			type_transaction character(20) NOT NULL,
			type_choix_libel text,
			montant numeric(30,6) DEFAULT 0,
			code_devise_montant character(3) NULL,
			commission numeric(30,6) DEFAULT 0,
			code_devise_commission character(3) NULL,
			compte_liaison_local text,
			compte_debit_siege text,
			compte_credit_siege text,			
			id_his_siege integer,
			id_ecriture_siege integer,
			ajout_historique boolean DEFAULT false,
			msg_erreur text,
			CONSTRAINT ad_multi_agence_compensation_pkey PRIMARY KEY (id)
		)
		WITH (
		  OIDS=FALSE
		);

		RAISE NOTICE 'Table ad_multi_agence_compensation created';
		output_result := 2;
	END IF;
	
	-- Create fonction systeme 214
	IF NOT EXISTS(SELECT * FROM adsys_fonction WHERE code_fonction=214 AND id_ag = numagc()) THEN

		INSERT INTO adsys_fonction(code_fonction, libelle, id_ag) VALUES (214, 'Traitement compensation au siège', numagc());

		RAISE NOTICE 'Insertion fonction systeme 214 dans la table adsys_fonction effectuée';
		output_result := 2;
	END IF;

	-- Création opération financière
	IF NOT EXISTS(SELECT * FROM ad_cpt_ope WHERE type_operation=614 AND categorie_ope = 1 AND id_ag = numagc()) THEN
		-- Compensation au siège
		INSERT INTO ad_cpt_ope(type_operation, categorie_ope, id_ag, libel_ope) 
		VALUES (614, 1, numagc(), maketraductionlangsyst('Compensation au siège'));

		RAISE NOTICE 'Insertion type_operation 614 dans la table ad_cpt_ope effectuée';
		output_result := 2;
	END IF;

	IF NOT EXISTS(SELECT * FROM ad_cpt_ope_cptes WHERE type_operation=614 AND sens = 'd' AND categorie_cpte = 0 AND id_ag = numagc()) THEN
		INSERT INTO ad_cpt_ope_cptes(type_operation, num_cpte, sens, categorie_cpte, id_ag) VALUES (614, NULL, 'd', 0, numagc());

		RAISE NOTICE 'Insertion type_operation 614 sens ''d'' dans la table ad_cpt_ope_cptes effectuée';
		output_result := 2;
	END IF;

	IF NOT EXISTS(SELECT * FROM ad_cpt_ope_cptes WHERE type_operation=614 AND sens = 'c' AND categorie_cpte = 0 AND id_ag = numagc()) THEN
		INSERT INTO ad_cpt_ope_cptes(type_operation, num_cpte, sens, categorie_cpte, id_ag) VALUES (614, NULL, 'c', 0, numagc());

		RAISE NOTICE 'Insertion type_operation 614 sens ''c'' dans la table ad_cpt_ope_cptes effectuée';
		output_result := 2;
	END IF;

	-- Menus
	IF NOT EXISTS(SELECT nom_menu FROM menus WHERE nom_menu='Tcs') THEN
		INSERT INTO menus (nom_menu, libel_menu, nom_pere, pos_hierarch, ordre, is_menu,fonction, is_cliquable) VALUES ('Tcs', maketraductionlangsyst('Traitement compensation au siège'), 'Gen-7', 3, 11, true, 214, true);
	END IF;

	IF NOT EXISTS(SELECT nom_menu FROM menus WHERE nom_menu='Tcs-1') THEN
		INSERT INTO menus (nom_menu, libel_menu, nom_pere, pos_hierarch, ordre, is_menu,fonction, is_cliquable) VALUES ('Tcs-1', maketraductionlangsyst('Initialisation compensation'), 'Tcs', 4, 1, false, 214, false);
	END IF;

	IF NOT EXISTS(SELECT nom_menu FROM menus WHERE nom_menu='Tcs-2') THEN
		INSERT INTO menus (nom_menu, libel_menu, nom_pere, pos_hierarch, ordre, is_menu,fonction, is_cliquable)	VALUES ('Tcs-2', maketraductionlangsyst('Traitement des écritures de compensation'), 'Tcs', 4, 2, false, 214, false);
	END IF;

	/*
	IF NOT EXISTS(SELECT nom_menu FROM menus WHERE nom_menu='Tcs-3') THEN
		INSERT INTO menus (nom_menu, libel_menu, nom_pere, pos_hierarch, ordre, is_menu,fonction, is_cliquable)	VALUES ('Tcs-3', maketraductionlangsyst('Confirmation Traitement des compensations'), 'Tcs', 4, 3, false, 214, false);
	END IF;
	*/

	-- Ecrans
	IF NOT EXISTS (SELECT nom_ecran FROM ecrans WHERE nom_ecran='Tcs-1') THEN
		INSERT INTO ecrans(nom_ecran, fichier, nom_menu, fonction) VALUES ('Tcs-1', 'modules/systeme/traitements_compensation.php', 'Tcs-1', 214);
	END IF;

	IF NOT EXISTS (SELECT nom_ecran FROM ecrans WHERE nom_ecran='Tcs-2') THEN
		INSERT INTO ecrans(nom_ecran, fichier, nom_menu, fonction) VALUES ('Tcs-2', 'modules/systeme/traitements_compensation.php', 'Tcs-2', 214);
	END IF;

	/*
	IF NOT EXISTS (SELECT nom_ecran FROM ecrans WHERE nom_ecran='Tcs-3') THEN
		INSERT INTO ecrans(nom_ecran, fichier, nom_menu, fonction) VALUES ('Tcs-3', 'modules/systeme/traitements_compensation.php', 'Tcs-3', 214);
	END IF;
	*/
	
	IF NOT EXISTS (SELECT fonction FROM adsys_profils_axs WHERE profil=1 AND fonction=214) THEN
		INSERT INTO adsys_profils_axs (profil, fonction) VALUES (1, 214);
	END IF;

	RETURN output_result;

END;
$$
LANGUAGE plpgsql;

SELECT patch_multi_agence_3bases();
DROP FUNCTION patch_multi_agence_3bases();
