CREATE OR REPLACE FUNCTION patch_lcr() RETURNS INT AS
$$
DECLARE
output_result INTEGER = 1;
tableliste_ident INTEGER = (select ident from tableliste where nomc like 'adsys_produit_credit' order by ident desc limit 1);

BEGIN

	RAISE NOTICE 'START';

	-- ADD COLUMNS
	-- TABLE ad_dcr
	IF NOT EXISTS(SELECT column_name FROM information_schema.columns WHERE table_name = 'ad_dcr' AND column_name = 'is_ligne_credit') THEN
		ALTER TABLE ad_dcr ADD COLUMN is_ligne_credit boolean DEFAULT false;
		
		RAISE NOTICE 'Column is_ligne_credit added in table ad_dcr';
		output_result := 2;
	END IF;

	IF NOT EXISTS(SELECT column_name FROM information_schema.columns WHERE table_name='ad_dcr' and column_name='deboursement_autorisee_lcr') THEN
		ALTER TABLE ONLY ad_dcr ADD COLUMN deboursement_autorisee_lcr boolean DEFAULT true;

		RAISE NOTICE 'Column deboursement_autorisee_lcr added in table ad_dcr';
		output_result := 2;
	END IF;

	-- Motif changement authorisation
	IF NOT EXISTS(SELECT column_name FROM information_schema.columns WHERE table_name='ad_dcr' and column_name='motif_changement_authorisation_lcr') THEN
		ALTER TABLE ONLY ad_dcr ADD COLUMN motif_changement_authorisation_lcr text;

		RAISE NOTICE 'Column motif_changement_authorisation_lcr added in table ad_dcr';
		output_result := 2;
	END IF;
	
	-- Date changement authorisation
	IF NOT EXISTS(SELECT column_name FROM information_schema.columns WHERE table_name='ad_dcr' and column_name='date_changement_authorisation_lcr') THEN
		ALTER TABLE ONLY ad_dcr ADD COLUMN date_changement_authorisation_lcr timestamp without time zone;		

		RAISE NOTICE 'Column date_changement_authorisation_lcr added in table ad_dcr';
		output_result := 2;
	END IF;

	/*
	IF NOT EXISTS(SELECT column_name FROM information_schema.columns WHERE table_name='ad_dcr' and column_name='solde_frais_lcr') THEN
		ALTER TABLE ONLY ad_dcr ADD COLUMN solde_frais_lcr numeric(30,6) DEFAULT 0;

		RAISE NOTICE 'Column solde_frais_lcr added in table ad_dcr';
		output_result := 2;
	END IF;
	*/

	IF NOT EXISTS(SELECT column_name FROM information_schema.columns WHERE table_name='ad_dcr' and column_name='duree_nettoyage_lcr') THEN
		ALTER TABLE ONLY ad_dcr ADD COLUMN duree_nettoyage_lcr integer DEFAULT 0;

		RAISE NOTICE 'Column duree_nettoyage_lcr added in table ad_dcr';
		output_result := 2;
	END IF;
	
	IF NOT EXISTS(SELECT column_name FROM information_schema.columns WHERE table_name='ad_dcr' and column_name='remb_auto_lcr') THEN
		ALTER TABLE ONLY ad_dcr ADD COLUMN remb_auto_lcr boolean DEFAULT false;

		RAISE NOTICE 'Column remb_auto_lcr added in table ad_dcr';
		output_result := 2;
	END IF;
	
	IF NOT EXISTS(SELECT column_name FROM information_schema.columns WHERE table_name='ad_dcr' and column_name='tx_interet_lcr') THEN
		ALTER TABLE ONLY ad_dcr ADD COLUMN tx_interet_lcr double precision DEFAULT 0;

		RAISE NOTICE 'Column tx_interet_lcr added in table ad_dcr';
		output_result := 2;
	END IF;
	
	IF NOT EXISTS(SELECT column_name FROM information_schema.columns WHERE table_name='ad_dcr' and column_name='taux_frais_lcr') THEN
		ALTER TABLE ONLY ad_dcr ADD COLUMN taux_frais_lcr double precision DEFAULT 0;

		RAISE NOTICE 'Column taux_frais_lcr added in table ad_dcr';
		output_result := 2;
	END IF;
	
	IF NOT EXISTS(SELECT column_name FROM information_schema.columns WHERE table_name='ad_dcr' and column_name='taux_min_frais_lcr') THEN
		ALTER TABLE ONLY ad_dcr ADD COLUMN taux_min_frais_lcr numeric(30,6) DEFAULT 0;

		RAISE NOTICE 'Column taux_min_frais_lcr added in table ad_dcr';
		output_result := 2;
	END IF;
	
	IF NOT EXISTS(SELECT column_name FROM information_schema.columns WHERE table_name='ad_dcr' and column_name='taux_max_frais_lcr') THEN
		ALTER TABLE ONLY ad_dcr ADD COLUMN taux_max_frais_lcr numeric(30,6) DEFAULT 0;

		RAISE NOTICE 'Column taux_max_frais_lcr added in table ad_dcr';
		output_result := 2;
	END IF;
	
	IF NOT EXISTS(SELECT column_name FROM information_schema.columns WHERE table_name='ad_dcr' and column_name='ordre_remb_lcr') THEN
		ALTER TABLE ONLY ad_dcr ADD COLUMN ordre_remb_lcr smallint DEFAULT (1)::smallint;

		RAISE NOTICE 'Column ordre_remb_lcr added in table ad_dcr';
		output_result := 2;
	END IF;

	-- TABLE ad_sre
	-- Check if field "annul_remb" exist in table "ad_sre"
	IF NOT EXISTS(SELECT * FROM information_schema.columns WHERE table_name = 'ad_sre' AND column_name = 'annul_remb') THEN
		ALTER TABLE ad_sre ADD COLUMN annul_remb int DEFAULT null;

		RAISE NOTICE 'Column annul_remb added in table ad_sre';
		output_result := 2;
	END IF;

	-- Check if field "id_his" exist in table "ad_sre"
	IF NOT EXISTS(SELECT * FROM information_schema.columns WHERE table_name = 'ad_sre' AND column_name = 'id_his') THEN
		ALTER TABLE ad_sre ADD COLUMN id_his int DEFAULT null;

		RAISE NOTICE 'Column id_his added in table ad_sre';
		output_result := 2;
	END IF;	

	-- TABLE adsys_produit_credit
	IF NOT EXISTS(SELECT column_name FROM information_schema.columns WHERE table_name='adsys_produit_credit' and column_name='taux_frais_lcr') THEN
		ALTER TABLE ONLY adsys_produit_credit ADD COLUMN taux_frais_lcr double precision DEFAULT 0;

		RAISE NOTICE 'Column taux_frais_lcr added in table adsys_produit_credit';
		output_result := 2;
	END IF;

	IF NOT EXISTS(SELECT column_name FROM information_schema.columns WHERE table_name='adsys_produit_credit' and column_name='taux_min_frais_lcr') THEN
		ALTER TABLE ONLY adsys_produit_credit ADD COLUMN taux_min_frais_lcr numeric(30,6) DEFAULT 0;

		RAISE NOTICE 'Column taux_min_frais_lcr added in table adsys_produit_credit';
		output_result := 2;
	END IF;

	IF NOT EXISTS(SELECT column_name FROM information_schema.columns WHERE table_name='adsys_produit_credit' and column_name='taux_max_frais_lcr') THEN
		ALTER TABLE ONLY adsys_produit_credit ADD COLUMN taux_max_frais_lcr numeric(30,6) DEFAULT 0;

		RAISE NOTICE 'Column taux_max_frais_lcr added in table adsys_produit_credit';
		output_result := 2;
	END IF;

	IF NOT EXISTS(SELECT column_name FROM information_schema.columns WHERE table_name='adsys_produit_credit' and column_name='duree_nettoyage') THEN
		ALTER TABLE ONLY adsys_produit_credit ADD COLUMN duree_nettoyage integer DEFAULT 0;

		RAISE NOTICE 'Column duree_nettoyage added in table adsys_produit_credit';
		output_result := 2;
	END IF;

	IF NOT EXISTS(SELECT column_name FROM information_schema.columns WHERE table_name='adsys_produit_credit' and column_name='ordre_remb_lcr') THEN
		ALTER TABLE ONLY adsys_produit_credit ADD COLUMN ordre_remb_lcr smallint DEFAULT (1)::smallint;

		RAISE NOTICE 'Column ordre_remb_lcr added in table adsys_produit_credit';
		output_result := 2;
	END IF;

	IF NOT EXISTS(SELECT column_name FROM information_schema.columns WHERE table_name='adsys_produit_credit' and column_name='cpte_cpta_prod_cr_frais') THEN
		ALTER TABLE ONLY adsys_produit_credit ADD COLUMN cpte_cpta_prod_cr_frais text;

		RAISE NOTICE 'Column cpte_cpta_prod_cr_frais added in table adsys_produit_credit';
		output_result := 2;
	END IF;

	-- Add in table d_tableliste
	IF NOT EXISTS(SELECT * FROM d_tableliste WHERE tablen=tableliste_ident AND nchmpc = 'taux_frais_lcr') THEN
		INSERT INTO d_tableliste(ident, tablen, nchmpc, nchmpl, isreq, ref_field, type, onslct, ispkey, traduit) VALUES ((select max(ident) from d_tableliste)+1, tableliste_ident, 'taux_frais_lcr', maketraductionlangsyst('Pourcentage taux de frais sur montant non-utilisé'), true, NULL, 'prc', NULL, NULL, false);

		RAISE NOTICE 'Insertion taux_frais_lcr de la table d_tableliste effectuée';
		output_result := 2;
	END IF;
	
	IF NOT EXISTS(SELECT * FROM d_tableliste WHERE tablen=tableliste_ident AND nchmpc = 'taux_min_frais_lcr') THEN
		INSERT INTO d_tableliste(ident, tablen, nchmpc, nchmpl, isreq, ref_field, type, onslct, ispkey, traduit) VALUES ((select max(ident) from d_tableliste)+1, tableliste_ident, 'taux_min_frais_lcr', maketraductionlangsyst('Frais minimum par jour pour montant non-utilisé'), false, NULL, 'mnt', NULL, NULL, false);

		RAISE NOTICE 'Insertion taux_min_frais_lcr de la table d_tableliste effectuée';
		output_result := 2;
	END IF;
	
	IF NOT EXISTS(SELECT * FROM d_tableliste WHERE tablen=tableliste_ident AND nchmpc = 'taux_max_frais_lcr') THEN
		INSERT INTO d_tableliste(ident, tablen, nchmpc, nchmpl, isreq, ref_field, type, onslct, ispkey, traduit) VALUES ((select max(ident) from d_tableliste)+1, tableliste_ident, 'taux_max_frais_lcr', maketraductionlangsyst('Frais maximum par jour pour montant non-utilisé'), false, NULL, 'mnt', NULL, NULL, false);

		RAISE NOTICE 'Insertion taux_max_frais_lcr de la table d_tableliste effectuée';
		output_result := 2;
	END IF;
	
	IF NOT EXISTS(SELECT * FROM d_tableliste WHERE tablen=tableliste_ident AND nchmpc = 'duree_nettoyage') THEN
		INSERT INTO d_tableliste(ident, tablen, nchmpc, nchmpl, isreq, ref_field, type, onslct, ispkey, traduit) VALUES ((select max(ident) from d_tableliste)+1, tableliste_ident, 'duree_nettoyage', maketraductionlangsyst('Durée période de nettoyage avant date échéance (0 si aucun)'), true, NULL, 'int', NULL, NULL, false);

		RAISE NOTICE 'Insertion duree_nettoyage de la table d_tableliste effectuée';
		output_result := 2;
	END IF;
	
	IF NOT EXISTS(SELECT * FROM tableliste WHERE nomc = 'adsys_ordre_remb_lcr') THEN
		INSERT INTO tableliste(ident, nomc, noml, is_table) VALUES ((select max(ident) from tableliste)+1, 'adsys_ordre_remb_lcr', maketraductionlangsyst('Ordre de remboursement ligne de crédit'), false);
		
		RAISE NOTICE 'Insertion table adsys_ordre_remb_lcr de la table tableliste effectuée';
		output_result := 2;
	END IF;
	
	IF NOT EXISTS(SELECT * FROM d_tableliste WHERE tablen=(select ident from tableliste where nomc like 'adsys_ordre_remb_lcr' order by ident desc limit 1) AND nchmpc = 'ordre_remb_lcr') THEN
		INSERT INTO d_tableliste(ident, tablen, nchmpc, nchmpl, isreq, ref_field, type, onslct, ispkey, traduit) VALUES ((select max(ident) from d_tableliste)+1, (select ident from tableliste where nomc like 'adsys_ordre_remb_lcr' order by ident desc limit 1), 'ordre_remb_lcr', maketraductionlangsyst('Ordre de remboursement ligne de crédit'), true, NULL, 'int', NULL, true, false);

		RAISE NOTICE 'Insertion ordre_remb_lcr de la table d_tableliste effectuée';
		output_result := 2;
	END IF;
	
	IF NOT EXISTS(SELECT * FROM d_tableliste WHERE tablen=tableliste_ident AND nchmpc = 'ordre_remb_lcr') THEN
		INSERT INTO d_tableliste(ident, tablen, nchmpc, nchmpl, isreq, ref_field, type, onslct, ispkey, traduit) VALUES ((select max(ident) from d_tableliste)+1, tableliste_ident, 'ordre_remb_lcr', maketraductionlangsyst('Ordre de remboursement ligne de crédit'), true, (SELECT ident FROM d_tableliste WHERE tablen=(select ident from tableliste where nomc like 'adsys_ordre_remb_lcr' order by ident desc limit 1) AND nchmpc = 'ordre_remb_lcr'), 'int', NULL, NULL, false);

		RAISE NOTICE 'Insertion ordre_remb_lcr de la table d_tableliste effectuée';
		output_result := 2;
	END IF;
	
	IF NOT EXISTS(SELECT * FROM d_tableliste WHERE tablen=tableliste_ident AND nchmpc = 'cpte_cpta_prod_cr_frais') THEN
		INSERT INTO d_tableliste(ident, tablen, nchmpc, nchmpl, isreq, ref_field, type, onslct, ispkey, traduit) VALUES ((select max(ident) from d_tableliste)+1, tableliste_ident, 'cpte_cpta_prod_cr_frais', maketraductionlangsyst('Compte comptable des frais sur montant non-utilisé'), false, 1400, 'txt', NULL, NULL, false);

		RAISE NOTICE 'Insertion cpte_cpta_prod_cr_frais de la table d_tableliste effectuée';
		output_result := 2;
	END IF;

	-- Truncate table ad_lcr_his
	/*
	IF (SELECT COUNT(*) FROM ad_lcr_his) > 0 THEN

		-- Empty table
		TRUNCATE TABLE ad_lcr_his RESTART IDENTITY CASCADE;

		RAISE NOTICE 'Truncate table ad_lcr_his effectuée';
		output_result := 2;
	END IF;
	*/

	-- DROP TABLE ad_lcr_his
	/*
	IF EXISTS(SELECT * FROM information_schema.tables WHERE table_name = 'ad_lcr_his') THEN
		DROP TABLE ad_lcr_his;
		RAISE NOTICE 'Suppression table ad_lcr_his effectuée';
	END IF;
	*/

	-- CREATE TABLE ad_lcr_his
	IF NOT EXISTS(SELECT * FROM information_schema.tables WHERE table_name = 'ad_lcr_his') THEN
		CREATE TABLE ad_lcr_his
		(
			id serial NOT NULL,
			id_doss integer NOT NULL,
			date_evnt date NOT NULL,
			type_evnt integer NOT NULL,
			nature_evnt integer NULL,
			login character varying(50) NOT NULL,
			valeur numeric(30,6) NOT NULL,
			id_his integer,
			id_ag integer NOT NULL,
			comments text,
			date_creation timestamp without time zone NOT NULL DEFAULT now(),
			CONSTRAINT ad_lcr_his_pkey PRIMARY KEY (id, id_ag)
		)
		WITH (
		  OIDS=FALSE
		);

		RAISE NOTICE 'Table ad_lcr_his created';
		output_result := 2;
	END IF;
	
	-- Create fonction systeme -- IN PROGRESS
	IF NOT EXISTS(SELECT * FROM adsys_fonction WHERE code_fonction=102 AND id_ag = numagc()) THEN

		INSERT INTO adsys_fonction(code_fonction, libelle, id_ag) VALUES (102, 'Gestion ligne de crédit', numagc());

		RAISE NOTICE 'Insertion fonction systeme 102 dans la table adsys_fonction effectuée';
		output_result := 2;
	END IF;

	IF NOT EXISTS(SELECT * FROM adsys_fonction WHERE code_fonction=600 AND id_ag = numagc()) THEN

		INSERT INTO adsys_fonction(code_fonction, libelle, id_ag) VALUES (600, 'Mise en place ligne de crédit', numagc());

		RAISE NOTICE 'Insertion fonction systeme 600 dans la table adsys_fonction effectuée';
		output_result := 2;
	END IF;

	IF NOT EXISTS(SELECT * FROM adsys_fonction WHERE code_fonction=601 AND id_ag = numagc()) THEN

		INSERT INTO adsys_fonction(code_fonction, libelle, id_ag) VALUES (601, 'Approbation ligne de crédit', numagc());

		RAISE NOTICE 'Insertion fonction systeme 601 dans la table adsys_fonction effectuée';
		output_result := 2;
	END IF;

	IF NOT EXISTS(SELECT * FROM adsys_fonction WHERE code_fonction=602 AND id_ag = numagc()) THEN

		INSERT INTO adsys_fonction(code_fonction, libelle, id_ag) VALUES (602, 'Rejet ligne de crédit', numagc());

		RAISE NOTICE 'Insertion fonction systeme 602 dans la table adsys_fonction effectuée';
		output_result := 2;
	END IF;

	IF NOT EXISTS(SELECT * FROM adsys_fonction WHERE code_fonction=603 AND id_ag = numagc()) THEN

		INSERT INTO adsys_fonction(code_fonction, libelle, id_ag) VALUES (603, 'Annulation ligne de crédit', numagc());

		RAISE NOTICE 'Insertion fonction systeme 603 dans la table adsys_fonction effectuée';
		output_result := 2;
	END IF;

	IF NOT EXISTS(SELECT * FROM adsys_fonction WHERE code_fonction=604 AND id_ag = numagc()) THEN

		INSERT INTO adsys_fonction(code_fonction, libelle, id_ag) VALUES (604, 'Déboursement fonds ligne de crédit', numagc());

		RAISE NOTICE 'Insertion fonction systeme 604 dans la table adsys_fonction effectuée';
		output_result := 2;
	END IF;

	IF NOT EXISTS(SELECT * FROM adsys_fonction WHERE code_fonction=605 AND id_ag = numagc()) THEN

		INSERT INTO adsys_fonction(code_fonction, libelle, id_ag) VALUES (605, 'Modification ligne de crédit', numagc());

		RAISE NOTICE 'Insertion fonction systeme 605 dans la table adsys_fonction effectuée';
		output_result := 2;
	END IF;

	IF NOT EXISTS(SELECT * FROM adsys_fonction WHERE code_fonction=606 AND id_ag = numagc()) THEN

		INSERT INTO adsys_fonction(code_fonction, libelle, id_ag) VALUES (606, 'Consultation ligne de crédit', numagc());

		RAISE NOTICE 'Insertion fonction systeme 606 dans la table adsys_fonction effectuée';
		output_result := 2;
	END IF;

	IF NOT EXISTS(SELECT * FROM adsys_fonction WHERE code_fonction=607 AND id_ag = numagc()) THEN

		INSERT INTO adsys_fonction(code_fonction, libelle, id_ag) VALUES (607, 'Remboursement ligne de crédit', numagc());

		RAISE NOTICE 'Insertion fonction systeme 607 dans la table adsys_fonction effectuée';
		output_result := 2;
	END IF;

	IF NOT EXISTS(SELECT * FROM adsys_fonction WHERE code_fonction=608 AND id_ag = numagc()) THEN

		INSERT INTO adsys_fonction(code_fonction, libelle, id_ag) VALUES (608, 'Réalisation garanties ligne de crédit', numagc());

		RAISE NOTICE 'Insertion fonction systeme 608 dans la table adsys_fonction effectuée';
		output_result := 2;
	END IF;

	IF NOT EXISTS(SELECT * FROM adsys_fonction WHERE code_fonction=609 AND id_ag = numagc()) THEN

		INSERT INTO adsys_fonction(code_fonction, libelle, id_ag) VALUES (609, 'Correction dossier ligne de crédit', numagc());

		RAISE NOTICE 'Insertion fonction systeme 609 dans la table adsys_fonction effectuée';
		output_result := 2;
	END IF;

	-- Création opérations financière
	IF NOT EXISTS(SELECT * FROM ad_cpt_ope WHERE type_operation=25 AND categorie_ope = 1 AND id_ag = numagc()) THEN
		-- Remboursement frais sur crédits
		INSERT INTO ad_cpt_ope(type_operation, categorie_ope, id_ag, libel_ope) 
		VALUES (25, 1, numagc(), maketraductionlangsyst('Remboursement frais sur crédits'));

		RAISE NOTICE 'Insertion type_operation 25 dans la table ad_cpt_ope effectuée';
		output_result := 2;
	END IF;

	IF NOT EXISTS(SELECT * FROM ad_cpt_ope_cptes WHERE type_operation=25 AND sens = 'd' AND categorie_cpte = 1 AND id_ag = numagc()) THEN
		INSERT INTO ad_cpt_ope_cptes(type_operation, num_cpte, sens, categorie_cpte, id_ag) VALUES (25, NULL, 'd', 1, numagc());

		RAISE NOTICE 'Insertion type_operation 25 sens ''d'' dans la table ad_cpt_ope_cptes effectuée';
		output_result := 2;
	END IF;

	IF NOT EXISTS(SELECT * FROM ad_cpt_ope_cptes WHERE type_operation=25 AND sens = 'c' AND categorie_cpte = 11 AND id_ag = numagc()) THEN
		INSERT INTO ad_cpt_ope_cptes(type_operation, num_cpte, sens, categorie_cpte, id_ag) VALUES (25, NULL, 'c', 11, numagc());

		RAISE NOTICE 'Insertion type_operation 25 sens ''c'' dans la table ad_cpt_ope_cptes effectuée';
		output_result := 2;
	END IF;

	IF NOT EXISTS(SELECT * FROM ad_cpt_ope WHERE type_operation=26 AND categorie_ope = 1 AND id_ag = numagc()) THEN
		-- Annulation remboursement frais sur crédits
		INSERT INTO ad_cpt_ope(type_operation, categorie_ope, id_ag, libel_ope) 
		VALUES (26, 1, numagc(), maketraductionlangsyst('Annulation remboursement frais sur crédits'));

		RAISE NOTICE 'Insertion type_operation 26 dans la table ad_cpt_ope effectuée';
		output_result := 2;
	END IF;

	IF NOT EXISTS(SELECT * FROM ad_cpt_ope_cptes WHERE type_operation=26 AND sens = 'd' AND categorie_cpte = 11 AND id_ag = numagc()) THEN
		INSERT INTO ad_cpt_ope_cptes(type_operation, num_cpte, sens, categorie_cpte, id_ag) VALUES (26, NULL, 'd', 11, numagc());

		RAISE NOTICE 'Insertion type_operation 26 sens ''d'' dans la table ad_cpt_ope_cptes effectuée';
		output_result := 2;
	END IF;

	IF NOT EXISTS(SELECT * FROM ad_cpt_ope_cptes WHERE type_operation=26 AND sens = 'c' AND categorie_cpte = 1 AND id_ag = numagc()) THEN
		INSERT INTO ad_cpt_ope_cptes(type_operation, num_cpte, sens, categorie_cpte, id_ag) VALUES (26, NULL, 'c', 1, numagc());

		RAISE NOTICE 'Insertion type_operation 26 sens ''c'' dans la table ad_cpt_ope_cptes effectuée';
		output_result := 2;
	END IF;

	-- Menu
	IF NOT EXISTS(SELECT nom_menu FROM menus WHERE nom_menu='Lcr-1') THEN
		INSERT INTO menus (nom_menu, libel_menu, nom_pere, pos_hierarch, ordre, is_menu,fonction, is_cliquable) VALUES ('Lcr-1', maketraductionlangsyst('Gestion ligne de crédit'), 'Gen-11', 5, 2, true, 102, true);
	END IF;
	
	-- Ecran
	IF NOT EXISTS (SELECT nom_ecran FROM ecrans WHERE nom_ecran='Lcr-1') THEN
		INSERT INTO ecrans(nom_ecran, fichier, nom_menu, fonction) VALUES ('Lcr-1', 'modules/credit/menu_lcr.php', 'Lcr-1', 102);
	END IF;
	
	--------------------------------------------
	-- Mise en place dossier ligne de crédit

	IF NOT EXISTS(SELECT nom_menu FROM menus WHERE nom_menu='LAdo') THEN
		INSERT INTO menus (nom_menu, libel_menu, nom_pere, pos_hierarch, ordre, is_menu,fonction, is_cliquable) VALUES ('LAdo', maketraductionlangsyst('Mise en place ligne de crédit'), 'Lcr-1', 6, 1, true, 600, true);
	END IF;
	
	-- Sub menus
	IF NOT EXISTS(SELECT nom_menu FROM menus WHERE nom_menu='LAdo-1') THEN
		INSERT INTO menus (nom_menu, libel_menu, nom_pere, pos_hierarch, ordre, is_menu,fonction, is_cliquable) VALUES ('LAdo-1', maketraductionlangsyst('Saisie informations'), 'LAdo', 7, 1, false, NULL, false);
	END IF;

	IF NOT EXISTS(SELECT nom_menu FROM menus WHERE nom_menu='LAdo-2') THEN
		INSERT INTO menus (nom_menu, libel_menu, nom_pere, pos_hierarch, ordre, is_menu,fonction, is_cliquable) VALUES ('LAdo-2', maketraductionlangsyst('Echéancier théorique'), 'LAdo', 7, 2, false, NULL, false);
	END IF;

	IF NOT EXISTS(SELECT nom_menu FROM menus WHERE nom_menu='LAdo-3') THEN
		INSERT INTO menus (nom_menu, libel_menu, nom_pere, pos_hierarch, ordre, is_menu,fonction, is_cliquable) VALUES ('LAdo-3', maketraductionlangsyst('Perception frais dossier'), 'LAdo', 7, 3, false, NULL, false);
	END IF;

	IF NOT EXISTS(SELECT nom_menu FROM menus WHERE nom_menu='LAdo-4') THEN
		INSERT INTO menus (nom_menu, libel_menu, nom_pere, pos_hierarch, ordre, is_menu,fonction, is_cliquable) VALUES ('LAdo-4', maketraductionlangsyst('Blocage des garanties'), 'LAdo', 7, 4, false, NULL, false);
	END IF;

	IF NOT EXISTS(SELECT nom_menu FROM menus WHERE nom_menu='LAdo-5') THEN
		INSERT INTO menus (nom_menu, libel_menu, nom_pere, pos_hierarch, ordre, is_menu,fonction, is_cliquable) VALUES ('LAdo-5', maketraductionlangsyst('Confirmation'), 'LAdo', 7, 5, false, NULL, false);
	END IF;

	IF NOT EXISTS(SELECT nom_menu FROM menus WHERE nom_menu='LAdo-6') THEN
		INSERT INTO menus (nom_menu, libel_menu, nom_pere, pos_hierarch, ordre, is_menu,fonction, is_cliquable) VALUES ('LAdo-6', maketraductionlangsyst('Confirmation ajout de dossier'), 'LAdo', 7, 6, false, NULL, false);
	END IF;

	IF NOT EXISTS(SELECT nom_menu FROM menus WHERE nom_menu='LAdo-7') THEN
		INSERT INTO menus (nom_menu, libel_menu, nom_pere, pos_hierarch, ordre, is_menu,fonction, is_cliquable) VALUES ('LAdo-7', maketraductionlangsyst('Mobilisation des garanties'), 'LAdo', 7, 7, false, NULL, false);
	END IF;

	-- Menu garantie
	IF NOT EXISTS(SELECT nom_menu FROM menus WHERE nom_menu='LAdo-8') THEN
		INSERT INTO menus (nom_menu, libel_menu, nom_pere, pos_hierarch, ordre, is_menu,fonction, is_cliquable) VALUES ('LAdo-8', maketraductionlangsyst('Ajout de garantie'), 'LAdo-7', 8, 1, false, NULL, false);
	END IF;

	IF NOT EXISTS(SELECT nom_menu FROM menus WHERE nom_menu='LAdo-9') THEN
		INSERT INTO menus (nom_menu, libel_menu, nom_pere, pos_hierarch, ordre, is_menu,fonction, is_cliquable) VALUES ('LAdo-9', maketraductionlangsyst('Confirmation ajout garantie'), 'LAdo-7', 8, 2, false, NULL, false);
	END IF;

	IF NOT EXISTS(SELECT nom_menu FROM menus WHERE nom_menu='LAdo-10') THEN
		INSERT INTO menus (nom_menu, libel_menu, nom_pere, pos_hierarch, ordre, is_menu,fonction, is_cliquable) VALUES ('LAdo-10', maketraductionlangsyst('Modification garantie'), 'LAdo-7', 8, 3, false, NULL, false);
	END IF;

	IF NOT EXISTS(SELECT nom_menu FROM menus WHERE nom_menu='LAdo-11') THEN
		INSERT INTO menus (nom_menu, libel_menu, nom_pere, pos_hierarch, ordre, is_menu,fonction, is_cliquable) VALUES ('LAdo-11', maketraductionlangsyst('Suppression garantie'), 'LAdo-7', 8, 4, false, NULL, false);
	END IF;
	
	-- Ecrans
	IF NOT EXISTS (SELECT nom_ecran FROM ecrans WHERE nom_ecran='LAdo-1') THEN
		INSERT INTO ecrans(nom_ecran, fichier, nom_menu, fonction) VALUES ('LAdo-1', 'modules/credit/dossier_lcr.php', 'LAdo-1', 600);
	END IF;

	IF NOT EXISTS (SELECT nom_ecran FROM ecrans WHERE nom_ecran='LAdo-2') THEN
		INSERT INTO ecrans(nom_ecran, fichier, nom_menu, fonction) VALUES ('LAdo-2', 'modules/credit/dossier_lcr.php', 'LAdo-1', 600);
	END IF;

	IF NOT EXISTS (SELECT nom_ecran FROM ecrans WHERE nom_ecran='LAdo-3') THEN
		INSERT INTO ecrans(nom_ecran, fichier, nom_menu, fonction) VALUES ('LAdo-3', 'modules/credit/dossier_lcr.php', 'LAdo-2', 600);
	END IF;

	IF NOT EXISTS (SELECT nom_ecran FROM ecrans WHERE nom_ecran='LAdo-4') THEN
		INSERT INTO ecrans(nom_ecran, fichier, nom_menu, fonction) VALUES ('LAdo-4', 'modules/credit/dossier_lcr.php', 'LAdo-3', 600);
	END IF;

	IF NOT EXISTS (SELECT nom_ecran FROM ecrans WHERE nom_ecran='LAdo-5') THEN
		INSERT INTO ecrans(nom_ecran, fichier, nom_menu, fonction) VALUES ('LAdo-5', 'modules/credit/dossier_lcr.php', 'LAdo-4', 600);
	END IF;

	IF NOT EXISTS (SELECT nom_ecran FROM ecrans WHERE nom_ecran='LAdo-6') THEN
		INSERT INTO ecrans(nom_ecran, fichier, nom_menu, fonction) VALUES ('LAdo-6', 'modules/credit/dossier_lcr.php', 'LAdo-5', 600);
	END IF;

	IF NOT EXISTS (SELECT nom_ecran FROM ecrans WHERE nom_ecran='LAdo-7') THEN
		INSERT INTO ecrans(nom_ecran, fichier, nom_menu, fonction) VALUES ('LAdo-7', 'modules/credit/dossier_lcr.php', 'LAdo-7', 600);
	END IF;

	IF NOT EXISTS (SELECT nom_ecran FROM ecrans WHERE nom_ecran='LAdo-8') THEN
		INSERT INTO ecrans(nom_ecran, fichier, nom_menu, fonction) VALUES ('LAdo-8', 'modules/credit/dossier_lcr.php', 'LAdo-8', 600);
	END IF;

	IF NOT EXISTS (SELECT nom_ecran FROM ecrans WHERE nom_ecran='LAdo-9') THEN
		INSERT INTO ecrans(nom_ecran, fichier, nom_menu, fonction) VALUES ('LAdo-9', 'modules/credit/dossier_lcr.php', 'LAdo-9', 600);
	END IF;

	IF NOT EXISTS (SELECT nom_ecran FROM ecrans WHERE nom_ecran='LAdo-10') THEN
		INSERT INTO ecrans(nom_ecran, fichier, nom_menu, fonction) VALUES ('LAdo-10', 'modules/credit/dossier_lcr.php', 'LAdo-10', 600);
	END IF;

	IF NOT EXISTS (SELECT nom_ecran FROM ecrans WHERE nom_ecran='LAdo-11') THEN
		INSERT INTO ecrans(nom_ecran, fichier, nom_menu, fonction) VALUES ('LAdo-11', 'modules/credit/dossier_lcr.php', 'LAdo-11', 600);
	END IF;

	--------------------------------------------
	-- Approbation dossier ligne de crédit

	IF NOT EXISTS(SELECT nom_menu FROM menus WHERE nom_menu='LApd') THEN
		INSERT INTO menus (nom_menu, libel_menu, nom_pere, pos_hierarch, ordre, is_menu,fonction, is_cliquable) VALUES ('LApd', maketraductionlangsyst('Approbation ligne de crédit'), 'Lcr-1', 6, 2, true, 601, true);
	END IF;
	
	-- Sub menus
	IF NOT EXISTS(SELECT nom_menu FROM menus WHERE nom_menu='LApd-1') THEN
		INSERT INTO menus (nom_menu, libel_menu, nom_pere, pos_hierarch, ordre, is_menu,fonction, is_cliquable) VALUES ('LApd-1', maketraductionlangsyst('Sélection dossier de crédit'), 'LApd', 7, 1, false, NULL, false);
	END IF;
	
	IF NOT EXISTS(SELECT nom_menu FROM menus WHERE nom_menu='LApd-2') THEN
		INSERT INTO menus (nom_menu, libel_menu, nom_pere, pos_hierarch, ordre, is_menu,fonction, is_cliquable) VALUES ('LApd-2', maketraductionlangsyst('Approbation dossier de crédit'), 'LApd', 7, 2, false, NULL, false);
	END IF;
	
	IF NOT EXISTS(SELECT nom_menu FROM menus WHERE nom_menu='LApd-3') THEN
		INSERT INTO menus (nom_menu, libel_menu, nom_pere, pos_hierarch, ordre, is_menu,fonction, is_cliquable) VALUES ('LApd-3', maketraductionlangsyst('Echéancier théorique'), 'LApd', 7, 3, false, NULL, false);
	END IF;
	
	IF NOT EXISTS(SELECT nom_menu FROM menus WHERE nom_menu='LApd-4') THEN
		INSERT INTO menus (nom_menu, libel_menu, nom_pere, pos_hierarch, ordre, is_menu,fonction, is_cliquable) VALUES ('LApd-4', maketraductionlangsyst('Blocage des garanties'), 'LApd', 7, 4, false, NULL, false);
	END IF;
	
	IF NOT EXISTS(SELECT nom_menu FROM menus WHERE nom_menu='LApd-5') THEN
		INSERT INTO menus (nom_menu, libel_menu, nom_pere, pos_hierarch, ordre, is_menu,fonction, is_cliquable) VALUES ('LApd-5', maketraductionlangsyst('Confirmation'), 'LApd', 7, 5, false, NULL, false);
	END IF;
	
	IF NOT EXISTS(SELECT nom_menu FROM menus WHERE nom_menu='LApd-6') THEN
		INSERT INTO menus (nom_menu, libel_menu, nom_pere, pos_hierarch, ordre, is_menu,fonction, is_cliquable) VALUES ('LApd-6', maketraductionlangsyst('Gestion des garanties'), 'LApd', 7, 6, false, NULL, false);
	END IF;

	-- Menu garantie
	IF NOT EXISTS(SELECT nom_menu FROM menus WHERE nom_menu='LApd-7') THEN
		INSERT INTO menus (nom_menu, libel_menu, nom_pere, pos_hierarch, ordre, is_menu,fonction, is_cliquable) VALUES ('LApd-7', maketraductionlangsyst('Ajout de garantie'), 'LApd-6', 8, 1, false, NULL, false);
	END IF;
	
	IF NOT EXISTS(SELECT nom_menu FROM menus WHERE nom_menu='LApd-8') THEN
		INSERT INTO menus (nom_menu, libel_menu, nom_pere, pos_hierarch, ordre, is_menu,fonction, is_cliquable) VALUES ('LApd-8', maketraductionlangsyst('Modification de garantie'), 'LApd-6', 8, 2, false, NULL, false);
	END IF;
	
	IF NOT EXISTS(SELECT nom_menu FROM menus WHERE nom_menu='LApd-9') THEN
		INSERT INTO menus (nom_menu, libel_menu, nom_pere, pos_hierarch, ordre, is_menu,fonction, is_cliquable) VALUES ('LApd-9', maketraductionlangsyst('Suppression de garantie'), 'LApd-6', 8, 3, false, NULL, false);
	END IF;
	
	IF NOT EXISTS(SELECT nom_menu FROM menus WHERE nom_menu='LApd-10') THEN
		INSERT INTO menus (nom_menu, libel_menu, nom_pere, pos_hierarch, ordre, is_menu,fonction, is_cliquable) VALUES ('LApd-10', maketraductionlangsyst('Confirmation garantie'), 'LApd-6', 8, 4, false, NULL, false);
	END IF;
	
	-- Ecrans
	IF NOT EXISTS (SELECT nom_ecran FROM ecrans WHERE nom_ecran='LApd-1') THEN
		INSERT INTO ecrans(nom_ecran, fichier, nom_menu, fonction) VALUES ('LApd-1', 'modules/credit/approbation_lcr.php', 'LApd-1', 601);
	END IF;
	
	IF NOT EXISTS (SELECT nom_ecran FROM ecrans WHERE nom_ecran='LApd-2') THEN
		INSERT INTO ecrans(nom_ecran, fichier, nom_menu, fonction) VALUES ('LApd-2', 'modules/credit/approbation_lcr.php', 'LApd-2', 601);
	END IF;
	
	IF NOT EXISTS (SELECT nom_ecran FROM ecrans WHERE nom_ecran='LApd-3') THEN
		INSERT INTO ecrans(nom_ecran, fichier, nom_menu, fonction) VALUES ('LApd-3', 'modules/credit/approbation_lcr.php', 'LApd-3', 601);
	END IF;
	
	IF NOT EXISTS (SELECT nom_ecran FROM ecrans WHERE nom_ecran='LApd-4') THEN
		INSERT INTO ecrans(nom_ecran, fichier, nom_menu, fonction) VALUES ('LApd-4', 'modules/credit/approbation_lcr.php', 'LApd-4', 601);
	END IF;
	
	IF NOT EXISTS (SELECT nom_ecran FROM ecrans WHERE nom_ecran='LApd-5') THEN
		INSERT INTO ecrans(nom_ecran, fichier, nom_menu, fonction) VALUES ('LApd-5', 'modules/credit/approbation_lcr.php', 'LApd-5', 601);
	END IF;
	
	IF NOT EXISTS (SELECT nom_ecran FROM ecrans WHERE nom_ecran='LApd-6') THEN
		INSERT INTO ecrans(nom_ecran, fichier, nom_menu, fonction) VALUES ('LApd-6', 'modules/credit/approbation_lcr.php', 'LApd-6', 601);
	END IF;
	
	IF NOT EXISTS (SELECT nom_ecran FROM ecrans WHERE nom_ecran='LApd-7') THEN
		INSERT INTO ecrans(nom_ecran, fichier, nom_menu, fonction) VALUES ('LApd-7', 'modules/credit/approbation_lcr.php', 'LApd-7', 601);
	END IF;
	
	IF NOT EXISTS (SELECT nom_ecran FROM ecrans WHERE nom_ecran='LApd-8') THEN
		INSERT INTO ecrans(nom_ecran, fichier, nom_menu, fonction) VALUES ('LApd-8', 'modules/credit/approbation_lcr.php', 'LApd-8', 601);
	END IF;
	
	IF NOT EXISTS (SELECT nom_ecran FROM ecrans WHERE nom_ecran='LApd-9') THEN
		INSERT INTO ecrans(nom_ecran, fichier, nom_menu, fonction) VALUES ('LApd-9', 'modules/credit/approbation_lcr.php', 'LApd-9', 601);
	END IF;
	
	IF NOT EXISTS (SELECT nom_ecran FROM ecrans WHERE nom_ecran='LApd-10') THEN
		INSERT INTO ecrans(nom_ecran, fichier, nom_menu, fonction) VALUES ('LApd-10', 'modules/credit/approbation_lcr.php', 'LApd-10', 601);
	END IF;

	--------------------------------------------
	-- Rejet dossier ligne de crédit

	IF NOT EXISTS(SELECT nom_menu FROM menus WHERE nom_menu='LRfd') THEN
		INSERT INTO menus (nom_menu, libel_menu, nom_pere, pos_hierarch, ordre, is_menu,fonction, is_cliquable) VALUES ('LRfd', maketraductionlangsyst('Rejet ligne de crédit'), 'Lcr-1', 6, 3, true, 602, true);
	END IF;
	
	-- Sub menus
	IF NOT EXISTS(SELECT nom_menu FROM menus WHERE nom_menu='LRfd-1') THEN
		INSERT INTO menus (nom_menu, libel_menu, nom_pere, pos_hierarch, ordre, is_menu,fonction, is_cliquable) VALUES ('LRfd-1', maketraductionlangsyst('Sélection dossier ligne de crédit'), 'LRfd', 7, 1, false, NULL, false);
	END IF;

	IF NOT EXISTS(SELECT nom_menu FROM menus WHERE nom_menu='LRfd-2') THEN
		INSERT INTO menus (nom_menu, libel_menu, nom_pere, pos_hierarch, ordre, is_menu,fonction, is_cliquable) VALUES ('LRfd-2', maketraductionlangsyst('Rejet dossier ligne de crédit'), 'LRfd', 7, 2, false, NULL, false);
	END IF;

	IF NOT EXISTS(SELECT nom_menu FROM menus WHERE nom_menu='LRfd-3') THEN
		INSERT INTO menus (nom_menu, libel_menu, nom_pere, pos_hierarch, ordre, is_menu,fonction, is_cliquable) VALUES ('LRfd-3', maketraductionlangsyst('Confirmation'), 'LRfd', 7, 3, false, NULL, false);
	END IF;
	
	-- Ecrans
	IF NOT EXISTS (SELECT nom_ecran FROM ecrans WHERE nom_ecran='LRfd-1') THEN
		INSERT INTO ecrans(nom_ecran, fichier, nom_menu, fonction) VALUES ('LRfd-1', 'modules/credit/rejetdossier_lcr.php', 'LRfd-1', 602);
	END IF;
	
	IF NOT EXISTS (SELECT nom_ecran FROM ecrans WHERE nom_ecran='LRfd-2') THEN
		INSERT INTO ecrans(nom_ecran, fichier, nom_menu, fonction) VALUES ('LRfd-2', 'modules/credit/rejetdossier_lcr.php', 'LRfd-2', 602);
	END IF;
	
	IF NOT EXISTS (SELECT nom_ecran FROM ecrans WHERE nom_ecran='LRfd-3') THEN
		INSERT INTO ecrans(nom_ecran, fichier, nom_menu, fonction) VALUES ('LRfd-3', 'modules/credit/rejetdossier_lcr.php', 'LRfd-3', 602);
	END IF;
	
	--------------------------------------------
	-- Annulation d''un dossier ligne de crédit

	IF NOT EXISTS(SELECT nom_menu FROM menus WHERE nom_menu='LAnd') THEN
		INSERT INTO menus (nom_menu, libel_menu, nom_pere, pos_hierarch, ordre, is_menu,fonction, is_cliquable) VALUES ('LAnd', maketraductionlangsyst('Annulation ligne de crédit'), 'Lcr-1', 6, 4, true, 603, true);
	END IF;
	
	-- Sub menus
	IF NOT EXISTS(SELECT nom_menu FROM menus WHERE nom_menu='LAnd-1') THEN
		INSERT INTO menus (nom_menu, libel_menu, nom_pere, pos_hierarch, ordre, is_menu,fonction, is_cliquable) VALUES ('LAnd-1', maketraductionlangsyst('Sélection dossier ligne de crédit'), 'LAnd', 7, 1, false, NULL, false);
	END IF;
	
	IF NOT EXISTS(SELECT nom_menu FROM menus WHERE nom_menu='LAnd-2') THEN
		INSERT INTO menus (nom_menu, libel_menu, nom_pere, pos_hierarch, ordre, is_menu,fonction, is_cliquable) VALUES ('LAnd-2', maketraductionlangsyst('Annulation dossier de crédit'), 'LAnd', 7, 2, false, NULL, false);
	END IF;
	
	IF NOT EXISTS(SELECT nom_menu FROM menus WHERE nom_menu='LAnd-3') THEN
		INSERT INTO menus (nom_menu, libel_menu, nom_pere, pos_hierarch, ordre, is_menu,fonction, is_cliquable) VALUES ('LAnd-3', maketraductionlangsyst('Confirmation'), 'LAnd', 7, 3, false, NULL, false);
	END IF;
	
	-- Ecrans
	IF NOT EXISTS (SELECT nom_ecran FROM ecrans WHERE nom_ecran='LAnd-1') THEN
		INSERT INTO ecrans(nom_ecran, fichier, nom_menu, fonction) VALUES ('LAnd-1', 'modules/credit/annul_dossier_lcr.php', 'LAnd-1', 603);
	END IF;
	
	IF NOT EXISTS (SELECT nom_ecran FROM ecrans WHERE nom_ecran='LAnd-2') THEN
		INSERT INTO ecrans(nom_ecran, fichier, nom_menu, fonction) VALUES ('LAnd-2', 'modules/credit/annul_dossier_lcr.php', 'LAnd-2', 603);
	END IF;
	
	IF NOT EXISTS (SELECT nom_ecran FROM ecrans WHERE nom_ecran='LAnd-3') THEN
		INSERT INTO ecrans(nom_ecran, fichier, nom_menu, fonction) VALUES ('LAnd-3', 'modules/credit/annul_dossier_lcr.php', 'LAnd-3', 603);
	END IF;
	
	--------------------------------------------
	-- Déboursement des fonds ligne de crédit

	IF NOT EXISTS(SELECT nom_menu FROM menus WHERE nom_menu='LDbd') THEN
		INSERT INTO menus (nom_menu, libel_menu, nom_pere, pos_hierarch, ordre, is_menu,fonction, is_cliquable) VALUES ('LDbd', maketraductionlangsyst('Déboursement fonds ligne de crédit'), 'Lcr-1', 6, 5, true, 604, true);
	END IF;
	
	-- Sub menus
	IF NOT EXISTS(SELECT nom_menu FROM menus WHERE nom_menu='LDbd-1') THEN
		INSERT INTO menus (nom_menu, libel_menu, nom_pere, pos_hierarch, ordre, is_menu,fonction, is_cliquable) VALUES ('LDbd-1', maketraductionlangsyst('Sélection dossier ligne de crédit'), 'LDbd', 7, 1, false, NULL, false);
	END IF;
	
	IF NOT EXISTS(SELECT nom_menu FROM menus WHERE nom_menu='LDbd-2') THEN
		INSERT INTO menus (nom_menu, libel_menu, nom_pere, pos_hierarch, ordre, is_menu,fonction, is_cliquable) VALUES ('LDbd-2', maketraductionlangsyst('Déboursement des fonds'), 'LDbd', 7, 2, false, NULL, false);
	END IF;
	
	IF NOT EXISTS(SELECT nom_menu FROM menus WHERE nom_menu='LDbd-3') THEN
		INSERT INTO menus (nom_menu, libel_menu, nom_pere, pos_hierarch, ordre, is_menu,fonction, is_cliquable) VALUES ('LDbd-3', maketraductionlangsyst('Echéancier réel'), 'LDbd', 7, 3, false, NULL, false);
	END IF;
	
	IF NOT EXISTS(SELECT nom_menu FROM menus WHERE nom_menu='LDbd-4') THEN
		INSERT INTO menus (nom_menu, libel_menu, nom_pere, pos_hierarch, ordre, is_menu,fonction, is_cliquable) VALUES ('LDbd-4', maketraductionlangsyst('Transfert des garanties'), 'LDbd', 7, 4, false, NULL, false);
	END IF;
	
	IF NOT EXISTS(SELECT nom_menu FROM menus WHERE nom_menu='LDbd-5') THEN
		INSERT INTO menus (nom_menu, libel_menu, nom_pere, pos_hierarch, ordre, is_menu,fonction, is_cliquable) VALUES ('LDbd-5', maketraductionlangsyst('Perception des commissions'), 'LDbd', 7, 5, false, NULL, false);
	END IF;
	
	IF NOT EXISTS(SELECT nom_menu FROM menus WHERE nom_menu='LDbd-6') THEN
		INSERT INTO menus (nom_menu, libel_menu, nom_pere, pos_hierarch, ordre, is_menu,fonction, is_cliquable) VALUES ('LDbd-6', maketraductionlangsyst('Transfert des assurances'), 'LDbd', 7, 6, false, NULL, false);
	END IF;
	
	IF NOT EXISTS(SELECT nom_menu FROM menus WHERE nom_menu='LDbd-7') THEN
		INSERT INTO menus (nom_menu, libel_menu, nom_pere, pos_hierarch, ordre, is_menu,fonction, is_cliquable) VALUES ('LDbd-7', maketraductionlangsyst('Transfert des fonds du crédit'), 'LDbd', 7, 7, false, NULL, false);
	END IF;
	
	IF NOT EXISTS(SELECT nom_menu FROM menus WHERE nom_menu='LDbd-8') THEN
		INSERT INTO menus (nom_menu, libel_menu, nom_pere, pos_hierarch, ordre, is_menu,fonction, is_cliquable) VALUES ('LDbd-8', maketraductionlangsyst('Confirmation'), 'LDbd', 7, 8, false, NULL, false);
	END IF;
	
	IF NOT EXISTS(SELECT nom_menu FROM menus WHERE nom_menu='LDbd-9') THEN
		INSERT INTO menus (nom_menu, libel_menu, nom_pere, pos_hierarch, ordre, is_menu,fonction, is_cliquable) VALUES ('LDbd-9', maketraductionlangsyst('Impression échéancier'), 'LDbd', 7, 9, false, NULL, false);
	END IF;
	
	IF NOT EXISTS(SELECT nom_menu FROM menus WHERE nom_menu='LDbd-10') THEN
		INSERT INTO menus (nom_menu, libel_menu, nom_pere, pos_hierarch, ordre, is_menu,fonction, is_cliquable) VALUES ('LDbd-10', maketraductionlangsyst('Perception frais dossier'), 'LDbd', 7, 10, false, NULL, false);
	END IF;
	
	IF NOT EXISTS(SELECT nom_menu FROM menus WHERE nom_menu='LDbd-11') THEN
		INSERT INTO menus (nom_menu, libel_menu, nom_pere, pos_hierarch, ordre, is_menu,fonction, is_cliquable) VALUES ('LDbd-11', maketraductionlangsyst('Mode de déboursement'), 'LDbd', 7, 11, false, NULL, false);
	END IF;
	
	-- Ecrans
	IF NOT EXISTS (SELECT nom_ecran FROM ecrans WHERE nom_ecran='LDbd-1') THEN
		INSERT INTO ecrans(nom_ecran, fichier, nom_menu, fonction) VALUES ('LDbd-1', 'modules/credit/debourdossier_lcr.php', 'LDbd-1', 604);
	END IF;
	
	IF NOT EXISTS (SELECT nom_ecran FROM ecrans WHERE nom_ecran='LDbd-2') THEN
		INSERT INTO ecrans(nom_ecran, fichier, nom_menu, fonction) VALUES ('LDbd-2', 'modules/credit/debourdossier_lcr.php', 'LDbd-2', 604);
	END IF;
	
	IF NOT EXISTS (SELECT nom_ecran FROM ecrans WHERE nom_ecran='LDbd-3') THEN
		INSERT INTO ecrans(nom_ecran, fichier, nom_menu, fonction) VALUES ('LDbd-3', 'modules/credit/debourdossier_lcr.php', 'LDbd-3', 604);
	END IF;
	
	IF NOT EXISTS (SELECT nom_ecran FROM ecrans WHERE nom_ecran='LDbd-4') THEN
		INSERT INTO ecrans(nom_ecran, fichier, nom_menu, fonction) VALUES ('LDbd-4', 'modules/credit/debourdossier_lcr.php', 'LDbd-4', 604);
	END IF;
	
	IF NOT EXISTS (SELECT nom_ecran FROM ecrans WHERE nom_ecran='LDbd-5') THEN
		INSERT INTO ecrans(nom_ecran, fichier, nom_menu, fonction) VALUES ('LDbd-5', 'modules/credit/debourdossier_lcr.php', 'LDbd-5', 604);
	END IF;
	
	IF NOT EXISTS (SELECT nom_ecran FROM ecrans WHERE nom_ecran='LDbd-6') THEN
		INSERT INTO ecrans(nom_ecran, fichier, nom_menu, fonction) VALUES ('LDbd-6', 'modules/credit/debourdossier_lcr.php', 'LDbd-6', 604);
	END IF;
	
	IF NOT EXISTS (SELECT nom_ecran FROM ecrans WHERE nom_ecran='LDbd-7') THEN
		INSERT INTO ecrans(nom_ecran, fichier, nom_menu, fonction) VALUES ('LDbd-7', 'modules/credit/debourdossier_lcr.php', 'LDbd-7', 604);
	END IF;
	
	IF NOT EXISTS (SELECT nom_ecran FROM ecrans WHERE nom_ecran='LDbd-8') THEN
		INSERT INTO ecrans(nom_ecran, fichier, nom_menu, fonction) VALUES ('LDbd-8', 'modules/credit/debourdossier_lcr.php', 'LDbd-8', 604);
	END IF;
	
	IF NOT EXISTS (SELECT nom_ecran FROM ecrans WHERE nom_ecran='LDbd-9') THEN
		INSERT INTO ecrans(nom_ecran, fichier, nom_menu, fonction) VALUES ('LDbd-9', 'modules/credit/debourdossier_lcr.php', 'LDbd-9', 604);
	END IF;
	
	IF NOT EXISTS (SELECT nom_ecran FROM ecrans WHERE nom_ecran='LDbd-10') THEN
		INSERT INTO ecrans(nom_ecran, fichier, nom_menu, fonction) VALUES ('LDbd-10', 'modules/credit/debourdossier_lcr.php', 'LDbd-10', 604);
	END IF;
	
	IF NOT EXISTS (SELECT nom_ecran FROM ecrans WHERE nom_ecran='LDbd-11') THEN
		INSERT INTO ecrans(nom_ecran, fichier, nom_menu, fonction) VALUES ('LDbd-11', 'modules/credit/debourdossier_lcr.php', 'LDbd-11', 604);
	END IF;
	
	--------------------------------------------
	-- Modification dossier ligne de crédit

	IF NOT EXISTS(SELECT nom_menu FROM menus WHERE nom_menu='LMdd') THEN
		INSERT INTO menus (nom_menu, libel_menu, nom_pere, pos_hierarch, ordre, is_menu,fonction, is_cliquable) VALUES ('LMdd', maketraductionlangsyst('Modification ligne de crédit'), 'Lcr-1', 6, 6, true, 605, true);
	END IF;
	
	-- Sub menus
	IF NOT EXISTS(SELECT nom_menu FROM menus WHERE nom_menu='LMdd-1') THEN
		INSERT INTO menus (nom_menu, libel_menu, nom_pere, pos_hierarch, ordre, is_menu,fonction, is_cliquable) VALUES ('LMdd-1', maketraductionlangsyst('Sélection dossier ligne de crédit'), 'LMdd', 7, 1, false, NULL, false);
	END IF;
	
	IF NOT EXISTS(SELECT nom_menu FROM menus WHERE nom_menu='LMdd-2') THEN
		INSERT INTO menus (nom_menu, libel_menu, nom_pere, pos_hierarch, ordre, is_menu,fonction, is_cliquable) VALUES ('LMdd-2', maketraductionlangsyst('Modification dossier'), 'LMdd', 7, 2, false, NULL, false);
	END IF;
	
	IF NOT EXISTS(SELECT nom_menu FROM menus WHERE nom_menu='LMdd-5') THEN
		INSERT INTO menus (nom_menu, libel_menu, nom_pere, pos_hierarch, ordre, is_menu,fonction, is_cliquable) VALUES ('LMdd-5', maketraductionlangsyst('Echéancier théorique'), 'LMdd', 7, 3, false, NULL, false);
	END IF;
	
	IF NOT EXISTS(SELECT nom_menu FROM menus WHERE nom_menu='LMdd-3') THEN
		INSERT INTO menus (nom_menu, libel_menu, nom_pere, pos_hierarch, ordre, is_menu,fonction, is_cliquable) VALUES ('LMdd-3', maketraductionlangsyst('Blocage des garanties'), 'LMdd', 7, 4, false, NULL, false);
	END IF;
	
	IF NOT EXISTS(SELECT nom_menu FROM menus WHERE nom_menu='LMdd-4') THEN
		INSERT INTO menus (nom_menu, libel_menu, nom_pere, pos_hierarch, ordre, is_menu,fonction, is_cliquable) VALUES ('LMdd-4', maketraductionlangsyst('Confirmation'), 'LMdd', 7, 5, false, NULL, false);
	END IF;
	
	IF NOT EXISTS(SELECT nom_menu FROM menus WHERE nom_menu='LMdd-6') THEN
		INSERT INTO menus (nom_menu, libel_menu, nom_pere, pos_hierarch, ordre, is_menu,fonction, is_cliquable) VALUES ('LMdd-6', maketraductionlangsyst('Affichage des garanties'), 'LMdd', 7, 6, false, NULL, false);
	END IF;

	-- Menu garantie
	IF NOT EXISTS(SELECT nom_menu FROM menus WHERE nom_menu='LMdd-7') THEN
		INSERT INTO menus (nom_menu, libel_menu, nom_pere, pos_hierarch, ordre, is_menu,fonction, is_cliquable) VALUES ('LMdd-7', maketraductionlangsyst('Ajout de garantie'), 'LMdd-6', 8, 1, false, NULL, false);
	END IF;
	
	IF NOT EXISTS(SELECT nom_menu FROM menus WHERE nom_menu='LMdd-8') THEN
		INSERT INTO menus (nom_menu, libel_menu, nom_pere, pos_hierarch, ordre, is_menu,fonction, is_cliquable) VALUES ('LMdd-8', maketraductionlangsyst('Modification de garantie'), 'LMdd-6', 8, 2, false, NULL, false);
	END IF;
	
	IF NOT EXISTS(SELECT nom_menu FROM menus WHERE nom_menu='LMdd-9') THEN
		INSERT INTO menus (nom_menu, libel_menu, nom_pere, pos_hierarch, ordre, is_menu,fonction, is_cliquable) VALUES ('LMdd-9', maketraductionlangsyst('Suppression de garantie'), 'LMdd-6', 8, 3, false, NULL, false);
	END IF;
	
	IF NOT EXISTS(SELECT nom_menu FROM menus WHERE nom_menu='LMdd-10') THEN
		INSERT INTO menus (nom_menu, libel_menu, nom_pere, pos_hierarch, ordre, is_menu,fonction, is_cliquable) VALUES ('LMdd-10', maketraductionlangsyst('Confirmation garantie'), 'LMdd-6', 8, 4, false, NULL, false);
	END IF;
	
	-- Ecrans
	IF NOT EXISTS (SELECT nom_ecran FROM ecrans WHERE nom_ecran='LMdd-1') THEN
		INSERT INTO ecrans(nom_ecran, fichier, nom_menu, fonction) VALUES ('LMdd-1', 'modules/credit/modifdossier_lcr.php', 'LMdd-1', 605);
	END IF;
	
	IF NOT EXISTS (SELECT nom_ecran FROM ecrans WHERE nom_ecran='LMdd-2') THEN
		INSERT INTO ecrans(nom_ecran, fichier, nom_menu, fonction) VALUES ('LMdd-2', 'modules/credit/modifdossier_lcr.php', 'LMdd-2', 605);
	END IF;
	
	IF NOT EXISTS (SELECT nom_ecran FROM ecrans WHERE nom_ecran='LMdd-3') THEN
		INSERT INTO ecrans(nom_ecran, fichier, nom_menu, fonction) VALUES ('LMdd-3', 'modules/credit/modifdossier_lcr.php', 'LMdd-3', 605);
	END IF;
	
	IF NOT EXISTS (SELECT nom_ecran FROM ecrans WHERE nom_ecran='LMdd-4') THEN
		INSERT INTO ecrans(nom_ecran, fichier, nom_menu, fonction) VALUES ('LMdd-4', 'modules/credit/modifdossier_lcr.php', 'LMdd-4', 605);
	END IF;
	
	IF NOT EXISTS (SELECT nom_ecran FROM ecrans WHERE nom_ecran='LMdd-5') THEN
		INSERT INTO ecrans(nom_ecran, fichier, nom_menu, fonction) VALUES ('LMdd-5', 'modules/credit/modifdossier_lcr.php', 'LMdd-5', 605);
	END IF;
	
	IF NOT EXISTS (SELECT nom_ecran FROM ecrans WHERE nom_ecran='LMdd-6') THEN
		INSERT INTO ecrans(nom_ecran, fichier, nom_menu, fonction) VALUES ('LMdd-6', 'modules/credit/modifdossier_lcr.php', 'LMdd-6', 605);
	END IF;
	
	IF NOT EXISTS (SELECT nom_ecran FROM ecrans WHERE nom_ecran='LMdd-7') THEN
		INSERT INTO ecrans(nom_ecran, fichier, nom_menu, fonction) VALUES ('LMdd-7', 'modules/credit/modifdossier_lcr.php', 'LMdd-7', 605);
	END IF;
	
	IF NOT EXISTS (SELECT nom_ecran FROM ecrans WHERE nom_ecran='LMdd-8') THEN
		INSERT INTO ecrans(nom_ecran, fichier, nom_menu, fonction) VALUES ('LMdd-8', 'modules/credit/modifdossier_lcr.php', 'LMdd-8', 605);
	END IF;
	
	IF NOT EXISTS (SELECT nom_ecran FROM ecrans WHERE nom_ecran='LMdd-9') THEN
		INSERT INTO ecrans(nom_ecran, fichier, nom_menu, fonction) VALUES ('LMdd-9', 'modules/credit/modifdossier_lcr.php', 'LMdd-9', 605);
	END IF;
	
	IF NOT EXISTS (SELECT nom_ecran FROM ecrans WHERE nom_ecran='LMdd-10') THEN
		INSERT INTO ecrans(nom_ecran, fichier, nom_menu, fonction) VALUES ('LMdd-10', 'modules/credit/modifdossier_lcr.php', 'LMdd-10', 605);
	END IF;
	
	--------------------------------------------
	-- Consultation dossier ligne de crédit

	IF NOT EXISTS(SELECT nom_menu FROM menus WHERE nom_menu='LCdo') THEN
		INSERT INTO menus (nom_menu, libel_menu, nom_pere, pos_hierarch, ordre, is_menu,fonction, is_cliquable) VALUES ('LCdo', maketraductionlangsyst('Consultation ligne de crédit'), 'Lcr-1', 6, 7, true, 606, true);
	END IF;
	
	-- Sub menus
	IF NOT EXISTS(SELECT nom_menu FROM menus WHERE nom_menu='LCdo-1') THEN
		INSERT INTO menus (nom_menu, libel_menu, nom_pere, pos_hierarch, ordre, is_menu,fonction, is_cliquable) VALUES ('LCdo-1', maketraductionlangsyst('Sélection dossier ligne de crédit'), 'LCdo', 7, 1, false, NULL, false);
	END IF;
	
	IF NOT EXISTS(SELECT nom_menu FROM menus WHERE nom_menu='LCdo-2') THEN
		INSERT INTO menus (nom_menu, libel_menu, nom_pere, pos_hierarch, ordre, is_menu,fonction, is_cliquable) VALUES ('LCdo-2', maketraductionlangsyst('Consultation dossier'), 'LCdo', 7, 2, false, NULL, false);
	END IF;
	
	IF NOT EXISTS(SELECT nom_menu FROM menus WHERE nom_menu='LCdo-3') THEN
		INSERT INTO menus (nom_menu, libel_menu, nom_pere, pos_hierarch, ordre, is_menu,fonction, is_cliquable) VALUES ('LCdo-3', maketraductionlangsyst('Consultation échéancier'), 'LCdo', 7, 3, false, NULL, false);
	END IF;
	
	IF NOT EXISTS(SELECT nom_menu FROM menus WHERE nom_menu='LCdo-4') THEN
		INSERT INTO menus (nom_menu, libel_menu, nom_pere, pos_hierarch, ordre, is_menu,fonction, is_cliquable) VALUES ('LCdo-4', maketraductionlangsyst('Suivi du crédit'), 'LCdo', 7, 4, false, NULL, false);
	END IF;
	
	IF NOT EXISTS(SELECT nom_menu FROM menus WHERE nom_menu='LCdo-5') THEN
		INSERT INTO menus (nom_menu, libel_menu, nom_pere, pos_hierarch, ordre, is_menu,fonction, is_cliquable) VALUES ('LCdo-5', maketraductionlangsyst('Consultation des garanties'), 'LCdo', 7, 5, false, NULL, false);
	END IF;
	
	IF NOT EXISTS(SELECT nom_menu FROM menus WHERE nom_menu='LCdo-6') THEN
		INSERT INTO menus (nom_menu, libel_menu, nom_pere, pos_hierarch, ordre, is_menu,fonction, is_cliquable) VALUES ('LCdo-6', maketraductionlangsyst('Impression Suivi crédit'), 'LCdo', 7, 6, false, NULL, false);
	END IF;
	
	IF NOT EXISTS(SELECT nom_menu FROM menus WHERE nom_menu='LCdo-7') THEN
		INSERT INTO menus (nom_menu, libel_menu, nom_pere, pos_hierarch, ordre, is_menu,fonction, is_cliquable) VALUES ('LCdo-7', maketraductionlangsyst('Export CSV'), 'LCdo', 7, 7, false, NULL, false);
	END IF;
	
	-- Ecrans
	IF NOT EXISTS (SELECT nom_ecran FROM ecrans WHERE nom_ecran='LCdo-1') THEN
		INSERT INTO ecrans(nom_ecran, fichier, nom_menu, fonction) VALUES ('LCdo-1', 'modules/credit/consultdossier_lcr.php', 'LCdo-1', 606);
	END IF;

	IF NOT EXISTS (SELECT nom_ecran FROM ecrans WHERE nom_ecran='LCdo-2') THEN
		INSERT INTO ecrans(nom_ecran, fichier, nom_menu, fonction) VALUES ('LCdo-2', 'modules/credit/consultdossier_lcr.php', 'LCdo-2', 606);
	END IF;

	IF NOT EXISTS (SELECT nom_ecran FROM ecrans WHERE nom_ecran='LCdo-3') THEN
		INSERT INTO ecrans(nom_ecran, fichier, nom_menu, fonction) VALUES ('LCdo-3', 'modules/credit/consultdossier_lcr.php', 'LCdo-3', 606);
	END IF;

	IF NOT EXISTS (SELECT nom_ecran FROM ecrans WHERE nom_ecran='LCdo-4') THEN
		INSERT INTO ecrans(nom_ecran, fichier, nom_menu, fonction) VALUES ('LCdo-4', 'modules/credit/consultdossier_lcr.php', 'LCdo-4', 606);
	END IF;

	IF NOT EXISTS (SELECT nom_ecran FROM ecrans WHERE nom_ecran='LCdo-5') THEN
		INSERT INTO ecrans(nom_ecran, fichier, nom_menu, fonction) VALUES ('LCdo-5', 'modules/credit/consultdossier_lcr.php', 'LCdo-5', 606);
	END IF;

	IF NOT EXISTS (SELECT nom_ecran FROM ecrans WHERE nom_ecran='LCdo-6') THEN
		INSERT INTO ecrans(nom_ecran, fichier, nom_menu, fonction) VALUES ('LCdo-6', 'modules/credit/consultdossier_lcr.php', 'LCdo-6', 606);
	END IF;

	IF NOT EXISTS (SELECT nom_ecran FROM ecrans WHERE nom_ecran='LCdo-7') THEN
		INSERT INTO ecrans(nom_ecran, fichier, nom_menu, fonction) VALUES ('LCdo-7', 'modules/credit/consultdossier_lcr.php', 'LCdo-7', 606);
	END IF;
	
	--------------------------------------------
	-- Remboursement dossier ligne de crédit

	IF NOT EXISTS(SELECT nom_menu FROM menus WHERE nom_menu='LRcr') THEN
		INSERT INTO menus (nom_menu, libel_menu, nom_pere, pos_hierarch, ordre, is_menu,fonction, is_cliquable) VALUES ('LRcr', maketraductionlangsyst('Remboursement ligne de crédit'), 'Lcr-1', 6, 8, true, 607, true);
	END IF;
	
	-- Sub menus
	IF NOT EXISTS(SELECT nom_menu FROM menus WHERE nom_menu='LRcr-1') THEN
		INSERT INTO menus (nom_menu, libel_menu, nom_pere, pos_hierarch, ordre, is_menu,fonction, is_cliquable) VALUES ('LRcr-1', maketraductionlangsyst('Sélection dossier ligne de crédit'), 'LRcr', 7, 1, false, NULL, false);
	END IF;
	
	IF NOT EXISTS(SELECT nom_menu FROM menus WHERE nom_menu='LRcr-2') THEN
		INSERT INTO menus (nom_menu, libel_menu, nom_pere, pos_hierarch, ordre, is_menu,fonction, is_cliquable) VALUES ('LRcr-2', maketraductionlangsyst('Mode de remboursement'), 'LRcr', 7, 2, false, NULL, false);
	END IF;
	
	IF NOT EXISTS(SELECT nom_menu FROM menus WHERE nom_menu='LRcr-3') THEN
		INSERT INTO menus (nom_menu, libel_menu, nom_pere, pos_hierarch, ordre, is_menu,fonction, is_cliquable) VALUES ('LRcr-3', maketraductionlangsyst('Saisie informations'), 'LRcr', 7, 3, false, NULL, false);
	END IF;
	
	IF NOT EXISTS(SELECT nom_menu FROM menus WHERE nom_menu='LRcr-4') THEN
		INSERT INTO menus (nom_menu, libel_menu, nom_pere, pos_hierarch, ordre, is_menu,fonction, is_cliquable) VALUES ('LRcr-4', maketraductionlangsyst('Confirmation saisie'), 'LRcr', 7, 4, false, NULL, false);
	END IF;
	
	IF NOT EXISTS(SELECT nom_menu FROM menus WHERE nom_menu='LRcr-5') THEN
		INSERT INTO menus (nom_menu, libel_menu, nom_pere, pos_hierarch, ordre, is_menu,fonction, is_cliquable) VALUES ('LRcr-5', maketraductionlangsyst('Confirmation'), 'LRcr', 7, 5, false, NULL, false);
	END IF;
	
	IF NOT EXISTS(SELECT nom_menu FROM menus WHERE nom_menu='LRcr-6') THEN
		INSERT INTO menus (nom_menu, libel_menu, nom_pere, pos_hierarch, ordre, is_menu,fonction, is_cliquable) VALUES ('LRcr-6', maketraductionlangsyst('Recouvrement crédit en perte'), 'LRcr', 7, 6, false, NULL, false);
	END IF;
	
	-- Ecrans
	IF NOT EXISTS (SELECT nom_ecran FROM ecrans WHERE nom_ecran='LRcr-1') THEN
		INSERT INTO ecrans(nom_ecran, fichier, nom_menu, fonction) VALUES ('LRcr-1', 'modules/credit/remboursement_lcr.php', 'LRcr-1', 607);
	END IF;
	
	IF NOT EXISTS (SELECT nom_ecran FROM ecrans WHERE nom_ecran='LRcr-2') THEN
		INSERT INTO ecrans(nom_ecran, fichier, nom_menu, fonction) VALUES ('LRcr-2', 'modules/credit/remboursement_lcr.php', 'LRcr-2', 607);
	END IF;
	
	IF NOT EXISTS (SELECT nom_ecran FROM ecrans WHERE nom_ecran='LRcr-3') THEN
		INSERT INTO ecrans(nom_ecran, fichier, nom_menu, fonction) VALUES ('LRcr-3', 'modules/credit/remboursement_lcr.php', 'LRcr-3', 607);
	END IF;
	
	IF NOT EXISTS (SELECT nom_ecran FROM ecrans WHERE nom_ecran='LRcr-4') THEN
		INSERT INTO ecrans(nom_ecran, fichier, nom_menu, fonction) VALUES ('LRcr-4', 'modules/credit/remboursement_lcr.php', 'LRcr-4', 607);
	END IF;
	
	IF NOT EXISTS (SELECT nom_ecran FROM ecrans WHERE nom_ecran='LRcr-5') THEN
		INSERT INTO ecrans(nom_ecran, fichier, nom_menu, fonction) VALUES ('LRcr-5', 'modules/credit/remboursement_lcr.php', 'LRcr-5', 607);
	END IF;
	
	IF NOT EXISTS (SELECT nom_ecran FROM ecrans WHERE nom_ecran='LRcr-6') THEN
		INSERT INTO ecrans(nom_ecran, fichier, nom_menu, fonction) VALUES ('LRcr-6', 'modules/credit/remboursement_lcr.php', 'LRcr-6', 607);
	END IF;
	
	--------------------------------------------
	-- Réalisation garanties ligne de crédit

	IF NOT EXISTS(SELECT nom_menu FROM menus WHERE nom_menu='LRga') THEN
		INSERT INTO menus (nom_menu, libel_menu, nom_pere, pos_hierarch, ordre, is_menu,fonction, is_cliquable) VALUES ('LRga', maketraductionlangsyst('Réalisation garanties ligne de crédit'), 'Lcr-1', 6, 9, true, 608, true);
	END IF;
	
	-- Sub menus
	IF NOT EXISTS(SELECT nom_menu FROM menus WHERE nom_menu='LRga-1') THEN
		INSERT INTO menus (nom_menu, libel_menu, nom_pere, pos_hierarch, ordre, is_menu,fonction, is_cliquable) VALUES ('LRga-1', maketraductionlangsyst('Sélection dossier ligne de crédit'), 'LRga', 7, 1, false, NULL, false);
	END IF;

	IF NOT EXISTS(SELECT nom_menu FROM menus WHERE nom_menu='LRga-2') THEN
		INSERT INTO menus (nom_menu, libel_menu, nom_pere, pos_hierarch, ordre, is_menu,fonction, is_cliquable) VALUES ('LRga-2', maketraductionlangsyst('Selection de la garantie'), 'LRga', 7, 2, false, NULL, false);
	END IF;

	IF NOT EXISTS(SELECT nom_menu FROM menus WHERE nom_menu='LRga-3') THEN
		INSERT INTO menus (nom_menu, libel_menu, nom_pere, pos_hierarch, ordre, is_menu,fonction, is_cliquable) VALUES ('LRga-3', maketraductionlangsyst('Réalisation de la garantie'), 'LRga', 7, 3, false, NULL, false);
	END IF;

	IF NOT EXISTS(SELECT nom_menu FROM menus WHERE nom_menu='LRga-4') THEN
		INSERT INTO menus (nom_menu, libel_menu, nom_pere, pos_hierarch, ordre, is_menu,fonction, is_cliquable) VALUES ('LRga-4', maketraductionlangsyst('Confirmation de la réalisation'), 'LRga', 7, 4, false, NULL, false);
	END IF;
	
	-- Ecrans
	IF NOT EXISTS (SELECT nom_ecran FROM ecrans WHERE nom_ecran='LRga-1') THEN
		INSERT INTO ecrans(nom_ecran, fichier, nom_menu, fonction) VALUES ('LRga-1', 'modules/credit/realisationgarantie_lcr.php', 'LRga-1', 608);
	END IF;
	
	IF NOT EXISTS (SELECT nom_ecran FROM ecrans WHERE nom_ecran='LRga-2') THEN
		INSERT INTO ecrans(nom_ecran, fichier, nom_menu, fonction) VALUES ('LRga-2', 'modules/credit/realisationgarantie_lcr.php', 'LRga-2', 608);
	END IF;
	
	IF NOT EXISTS (SELECT nom_ecran FROM ecrans WHERE nom_ecran='LRga-3') THEN
		INSERT INTO ecrans(nom_ecran, fichier, nom_menu, fonction) VALUES ('LRga-3', 'modules/credit/realisationgarantie_lcr.php', 'LRga-3', 608);
	END IF;
	
	IF NOT EXISTS (SELECT nom_ecran FROM ecrans WHERE nom_ecran='LRga-4') THEN
		INSERT INTO ecrans(nom_ecran, fichier, nom_menu, fonction) VALUES ('LRga-4', 'modules/credit/realisationgarantie_lcr.php', 'LRga-4', 608);
	END IF;
	
	--------------------------------------------
	-- Correction des dossier de crédit
	
	IF NOT EXISTS(SELECT nom_menu FROM menus WHERE nom_menu='LCdd') THEN
		INSERT INTO menus (nom_menu, libel_menu, nom_pere, pos_hierarch, ordre, is_menu,fonction, is_cliquable) VALUES ('LCdd', maketraductionlangsyst('Correction dossier ligne de crédit'), 'Lcr-1', 6, 10, true, 609, true);
	END IF;
	
	-- Sub menus
	IF NOT EXISTS(SELECT nom_menu FROM menus WHERE nom_menu='LCdd-1') THEN
		INSERT INTO menus (nom_menu, libel_menu, nom_pere, pos_hierarch, ordre, is_menu,fonction, is_cliquable) VALUES ('LCdd-1', maketraductionlangsyst('Sélection du type de correction'), 'LCdd', 7, 1, false, NULL, false);
	END IF;
	
	IF NOT EXISTS(SELECT nom_menu FROM menus WHERE nom_menu='LCdd-2') THEN
		INSERT INTO menus (nom_menu, libel_menu, nom_pere, pos_hierarch, ordre, is_menu,fonction, is_cliquable) VALUES ('LCdd-2', maketraductionlangsyst('Sélection d''un dossier de crédit'), 'LCdd', 7, 2, false, NULL, false);
	END IF;
	
	IF NOT EXISTS(SELECT nom_menu FROM menus WHERE nom_menu='LCdd-3') THEN
		INSERT INTO menus (nom_menu, libel_menu, nom_pere, pos_hierarch, ordre, is_menu,fonction, is_cliquable) VALUES ('LCdd-3', maketraductionlangsyst('Affichage des informations dépendant du type de correction'), 'LCdd', 7, 3, false, NULL, false);
	END IF;
	
	IF NOT EXISTS(SELECT nom_menu FROM menus WHERE nom_menu='LCdd-4') THEN
		INSERT INTO menus (nom_menu, libel_menu, nom_pere, pos_hierarch, ordre, is_menu,fonction, is_cliquable) VALUES ('LCdd-4', maketraductionlangsyst('Affichage des remboursements effectués sur le crédit'), 'LCdd', 7, 4, false, NULL, false);
	END IF;
	
	IF NOT EXISTS(SELECT nom_menu FROM menus WHERE nom_menu='LCdd-5') THEN
		INSERT INTO menus (nom_menu, libel_menu, nom_pere, pos_hierarch, ordre, is_menu,fonction, is_cliquable) VALUES ('LCdd-5', maketraductionlangsyst('Affichage des remboursements à annuler'), 'LCdd', 7, 5, false, NULL, false);
	END IF;
	
	IF NOT EXISTS(SELECT nom_menu FROM menus WHERE nom_menu='LCdd-6') THEN
		INSERT INTO menus (nom_menu, libel_menu, nom_pere, pos_hierarch, ordre, is_menu,fonction, is_cliquable) VALUES ('LCdd-6', maketraductionlangsyst('Confirmation annulation des remboursements'), 'LCdd', 7, 6, false, NULL, false);
	END IF;
	
	IF NOT EXISTS(SELECT nom_menu FROM menus WHERE nom_menu='LCdd-7') THEN
		INSERT INTO menus (nom_menu, libel_menu, nom_pere, pos_hierarch, ordre, is_menu,fonction, is_cliquable) VALUES ('LCdd-7', maketraductionlangsyst('Affichage du dossier à supprimer'), 'LCdd', 7, 7, false, NULL, false);
	END IF;
	
	IF NOT EXISTS(SELECT nom_menu FROM menus WHERE nom_menu='LCdd-8') THEN
		INSERT INTO menus (nom_menu, libel_menu, nom_pere, pos_hierarch, ordre, is_menu,fonction, is_cliquable) VALUES ('LCdd-8', maketraductionlangsyst('Confirmation suppression dossier de crédit'), 'LCdd', 7, 8, false, NULL, false);
	END IF;

	-- Ecrans
	IF NOT EXISTS (SELECT nom_ecran FROM ecrans WHERE nom_ecran='LCdd-1') THEN
		INSERT INTO ecrans(nom_ecran, fichier, nom_menu, fonction) VALUES ('LCdd-1', 'modules/credit/correctdossier_lcr.php', 'LCdd-1', 609);
	END IF;
	
	IF NOT EXISTS (SELECT nom_ecran FROM ecrans WHERE nom_ecran='LCdd-2') THEN
		INSERT INTO ecrans(nom_ecran, fichier, nom_menu, fonction) VALUES ('LCdd-2', 'modules/credit/correctdossier_lcr.php', 'LCdd-2', 609);
	END IF;
	
	IF NOT EXISTS (SELECT nom_ecran FROM ecrans WHERE nom_ecran='LCdd-3') THEN
		INSERT INTO ecrans(nom_ecran, fichier, nom_menu, fonction) VALUES ('LCdd-3', 'modules/credit/correctdossier_lcr.php', 'LCdd-3', 609);
	END IF;
	
	IF NOT EXISTS (SELECT nom_ecran FROM ecrans WHERE nom_ecran='LCdd-4') THEN
		INSERT INTO ecrans(nom_ecran, fichier, nom_menu, fonction) VALUES ('LCdd-4', 'modules/credit/correctdossier_lcr.php', 'LCdd-4', 609);
	END IF;
	
	IF NOT EXISTS (SELECT nom_ecran FROM ecrans WHERE nom_ecran='LCdd-5') THEN
		INSERT INTO ecrans(nom_ecran, fichier, nom_menu, fonction) VALUES ('LCdd-5', 'modules/credit/correctdossier_lcr.php', 'LCdd-5', 609);
	END IF;
	
	IF NOT EXISTS (SELECT nom_ecran FROM ecrans WHERE nom_ecran='LCdd-6') THEN
		INSERT INTO ecrans(nom_ecran, fichier, nom_menu, fonction) VALUES ('LCdd-6', 'modules/credit/correctdossier_lcr.php', 'LCdd-6', 609);
	END IF;
	
	IF NOT EXISTS (SELECT nom_ecran FROM ecrans WHERE nom_ecran='LCdd-7') THEN
		INSERT INTO ecrans(nom_ecran, fichier, nom_menu, fonction) VALUES ('LCdd-7', 'modules/credit/correctdossier_lcr.php', 'LCdd-7', 609);
	END IF;
	
	IF NOT EXISTS (SELECT nom_ecran FROM ecrans WHERE nom_ecran='LCdd-8') THEN
		INSERT INTO ecrans(nom_ecran, fichier, nom_menu, fonction) VALUES ('LCdd-8', 'modules/credit/correctdossier_lcr.php', 'LCdd-8', 609);
	END IF;

	-- Ecrans Nouveaux Rapport
	IF NOT EXISTS (SELECT nom_ecran FROM ecrans WHERE nom_ecran='Kra-97') THEN
		INSERT INTO ecrans(nom_ecran, fichier, nom_menu, fonction) VALUES ('Kra-97', 'modules/rapports/rapports_credit.php', 'Kra-2', 350);
	END IF;

	IF NOT EXISTS (SELECT nom_ecran FROM ecrans WHERE nom_ecran='Kra-98') THEN
		INSERT INTO ecrans(nom_ecran, fichier, nom_menu, fonction) VALUES ('Kra-98', 'modules/rapports/rapports_credit.php', 'Kra-3', 350);
	END IF;

	IF NOT EXISTS (SELECT nom_ecran FROM ecrans WHERE nom_ecran='Kra-99') THEN
		INSERT INTO ecrans(nom_ecran, fichier, nom_menu, fonction) VALUES ('Kra-99', 'modules/rapports/rapports_credit.php', 'Kra-5', 350);
	END IF;

	RETURN output_result;

END;
$$
LANGUAGE plpgsql;

SELECT patch_lcr();
DROP FUNCTION patch_lcr();

-----------------------------------------------------------

CREATE OR REPLACE FUNCTION get_plafond_lcr (integer)
  RETURNS numeric(30,6) AS
$BODY$

DECLARE
	id_dossier ALIAS FOR $1;
	mnt_plafond numeric(30,6):=0;
BEGIN
	SELECT INTO mnt_plafond  valeur FROM ad_lcr_his WHERE id_doss = id_dossier AND type_evnt = 1 AND id_ag = numagc();

	RETURN (CASE WHEN mnt_plafond IS NULL THEN 0 ELSE COALESCE(mnt_plafond, 0) END);
END;
$BODY$
LANGUAGE plpgsql VOLATILE;

-----------------------------------------------------------

CREATE OR REPLACE FUNCTION get_cap_restant_du_lcr (integer, date)
  RETURNS numeric(30,6) AS
$BODY$

DECLARE
	id_dossier ALIAS FOR $1;
	date_due ALIAS FOR $2; 

	mnt_deb numeric(30,6):=0;
	mnt_remb numeric(30,6):=0;
	--mnt_cap_rest_du numeric(30,6):=0;
BEGIN
	-- Calcul total montant déboursement
	SELECT INTO mnt_deb COALESCE(SUM(valeur),0) FROM ad_lcr_his WHERE id_doss = id_dossier AND type_evnt = 2 AND date_evnt <= date_due AND id_ag = numagc();

	-- Calcul total montant remboursement
	SELECT INTO mnt_remb COALESCE(SUM(valeur),0) FROM ad_lcr_his WHERE id_doss = id_dossier AND type_evnt = 3 AND nature_evnt = 1 AND date_evnt <= date_due AND id_ag = numagc();

	RETURN COALESCE(mnt_deb - mnt_remb, 0);
END;
$BODY$
LANGUAGE plpgsql VOLATILE;

-----------------------------------------------------------

CREATE OR REPLACE FUNCTION get_montant_restant_debourser_lcr (integer, date)
  RETURNS numeric(30,6) AS
$BODY$

DECLARE
	id_dossier ALIAS FOR $1;
	date_due ALIAS FOR $2; 
BEGIN
	-- Calcul total montant restant à débourser 
	RETURN COALESCE(get_plafond_lcr(id_dossier) - get_cap_restant_du_lcr(id_dossier, date_due), 0);
END;
$BODY$
LANGUAGE plpgsql VOLATILE;

-----------------------------------------------------------

CREATE OR REPLACE FUNCTION calcul_frais_lcr (integer, date, integer)
  RETURNS numeric(30,6) AS
$BODY$

DECLARE
	id_dossier ALIAS FOR $1;
	date_donnee ALIAS FOR $2;
	b_restant ALIAS FOR $3;

	ech_date date;
	approb_date date;
	date_due date;

	lcr_taux_frais double precision;
	lcr_taux_min_frais numeric(30,6);
	lcr_taux_max_frais numeric(30,6);

	date_range RECORD;
	lcr_frais RECORD;
	
	mnt_dispo numeric(30,6):=0;
	curr_frais numeric(30,6):=0;
	total_frais numeric(30,6):=0;
	
BEGIN

	SELECT INTO ech_date  date_ech::date FROM ad_etr WHERE id_doss = id_dossier ORDER BY id_ech ASC LIMIT 1;
	SELECT INTO approb_date  date_evnt FROM ad_lcr_his WHERE id_doss = id_dossier AND type_evnt = 1 ORDER BY date_creation ASC LIMIT 1;
	
	SELECT INTO lcr_taux_frais,lcr_taux_min_frais,lcr_taux_max_frais  taux_frais_lcr,taux_min_frais_lcr,taux_max_frais_lcr FROM ad_dcr WHERE is_ligne_credit='t' AND id_doss = id_dossier LIMIT 1;
	
	-- Si date dû > date échéance
	IF (date_donnee::date > ech_date::date) THEN
		date_due := ech_date::date;
	ELSE
		date_due := date_donnee::date;
	END IF;

	FOR date_range IN SELECT a::date as today from generate_series(approb_date::date,date_due::date,'1 day') s(a) LOOP
	
		-- Calcul frais quotidien
		IF (mnt_dispo > 0) THEN
			curr_frais := ((mnt_dispo * lcr_taux_frais) / 360);

			-- Average frais
			IF (lcr_taux_min_frais > 0) AND (curr_frais < lcr_taux_min_frais) THEN
				curr_frais := lcr_taux_min_frais;
			END IF;
			IF (lcr_taux_max_frais > 0) AND (curr_frais > lcr_taux_max_frais) THEN
				curr_frais := lcr_taux_max_frais;
			END IF;
		END IF;
		
		FOR lcr_frais IN SELECT * FROM ad_lcr_his WHERE id_doss = id_dossier AND (type_evnt IN (1,2,4) OR (type_evnt = 3 AND nature_evnt = 1)) AND date_evnt = date_range.today ORDER BY date_creation ASC LOOP
			
			IF (lcr_frais.type_evnt = 1) THEN -- Approbation
				mnt_dispo := (mnt_dispo + lcr_frais.valeur);
			END IF;

			IF (lcr_frais.type_evnt = 2) THEN -- Déboursement
				mnt_dispo := (mnt_dispo - lcr_frais.valeur);
			ELSIF (lcr_frais.type_evnt = 3) AND (lcr_frais.nature_evnt = 1) THEN -- Remboursement Capital
				mnt_dispo := (mnt_dispo + lcr_frais.valeur);
			ELSIF (lcr_frais.type_evnt = 4) AND (b_restant = 1) THEN -- Prélèvement frais
				total_frais := (total_frais - lcr_frais.valeur);
			END IF;
			
		END LOOP;
		
		-- Cumul total frais
		total_frais := (total_frais + (curr_frais));
			
		--RAISE NOTICE '------ % ------', date_range.today;
		--RAISE NOTICE 'Montant dispo = %', mnt_dispo;
		--RAISE NOTICE 'Current frais = %', (curr_frais);			
		--RAISE NOTICE 'Total frais = %', round(total_frais);
		
		-- Reset frais quotidien
		curr_frais := 0;

	END LOOP;

	-- Calcul total frais
	RETURN COALESCE((CASE WHEN round(total_frais) < 0 THEN 0 ELSE round(total_frais) END), 0);
END;
$BODY$
LANGUAGE plpgsql VOLATILE;

-----------------------------------------------------------

CREATE OR REPLACE FUNCTION calcul_interets_lcr (integer, date, integer)
  RETURNS numeric(30,6) AS
$BODY$

DECLARE
	id_dossier ALIAS FOR $1;
	date_donnee ALIAS FOR $2;
	b_restant ALIAS FOR $3;
	
	ech_date date;
	deb_date date;
	date_due date;

	lcr_tx_interet double precision;

	date_range RECORD;
	lcr_interet RECORD;
	
	cap_restant_du numeric(30,6):=0;
	curr_interet numeric(30,6):=0;
	total_interet numeric(30,6):=0;
BEGIN

	SELECT INTO ech_date  date_ech::date FROM ad_etr WHERE id_doss = id_dossier ORDER BY id_ech ASC LIMIT 1;
	SELECT INTO deb_date  date_evnt FROM ad_lcr_his WHERE id_doss = id_dossier AND type_evnt = 2 ORDER BY date_creation ASC LIMIT 1;
	
	SELECT INTO lcr_tx_interet  tx_interet_lcr FROM ad_dcr WHERE is_ligne_credit='t' AND id_doss = id_dossier LIMIT 1;
	
	-- Si date dû > date échéance
	IF (date_donnee::date > ech_date::date) THEN
		date_due := ech_date::date;
	ELSE
		date_due := date_donnee::date;
	END IF;

	FOR date_range IN SELECT a::date as today from generate_series(deb_date::date,date_due::date,'1 day') s(a) LOOP

		-- Calcul intérêt quotidien
		IF (cap_restant_du > 0) THEN
			curr_interet := ((cap_restant_du * lcr_tx_interet) / 360);
		END IF;
		
		-- Calcul capital restant dû
		cap_restant_du := get_cap_restant_du_lcr(id_dossier, date_range.today);
		
		FOR lcr_interet IN SELECT * FROM ad_lcr_his WHERE id_doss = id_dossier AND type_evnt = 3 AND nature_evnt = 2 AND date_evnt = date_range.today ORDER BY date_creation ASC LOOP

			IF (lcr_interet.type_evnt = 3) AND (lcr_interet.nature_evnt = 2) AND (b_restant = 1) THEN -- Remboursement Intérêts
				total_interet := (total_interet - lcr_interet.valeur);
			END IF;

		END LOOP;

		-- Cumul total intérêt
		total_interet := (total_interet + (curr_interet));
			
		--RAISE NOTICE '------ % ------', date_range.today;
		--RAISE NOTICE 'Capital restant dû = %', cap_restant_du;
		--RAISE NOTICE 'Current intérêt = %', (curr_interet);			
		--RAISE NOTICE 'Total intérêt = %', round(total_interet);
		
		-- Reset intérêt quotidien
		curr_interet := 0;

	END LOOP;

	-- Calcul total intérêts
	RETURN COALESCE((CASE WHEN round(total_interet) < 0 THEN 0 ELSE round(total_interet) END), 0);
END;
$BODY$
LANGUAGE plpgsql VOLATILE;

-----------------------------------------------------------
