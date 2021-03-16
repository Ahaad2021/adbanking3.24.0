
------------------------- Ticket #648 : Sources de financement pour les crédits -------------------------
CREATE OR REPLACE FUNCTION patch_ticket_648() RETURNS INT AS
$$
DECLARE

	tableliste_ident INTEGER = 0;

	output_result INTEGER = 1;

BEGIN
	RAISE NOTICE 'DEBUT traitement';

	RAISE NOTICE 'START table adsys_bailleur';

	IF NOT EXISTS(SELECT * FROM information_schema.tables WHERE table_name = 'adsys_bailleur') THEN

		CREATE TABLE adsys_bailleur
		(
		  id serial NOT NULL,
		  id_ag integer NOT NULL,
		  libel text,
		  CONSTRAINT adsys_bailleur_pkey PRIMARY KEY (id, id_ag),
		  CONSTRAINT adsys_bailleur_ukey UNIQUE (id, libel, id_ag)
		)
		WITH (
		  OIDS=FALSE
		);

		output_result := 2;
	END IF;

	IF NOT EXISTS(SELECT * FROM tableliste WHERE nomc = 'adsys_bailleur') THEN
		INSERT INTO tableliste(ident, nomc, noml, is_table) VALUES ((select max(ident) from tableliste)+1, 'adsys_bailleur', maketraductionlangsyst('Table Sources de financement pour les crédits'), true);

		RAISE NOTICE 'Insertion table adsys_bailleur de la table tableliste effectuée';
		output_result := 2;
	END IF;

	tableliste_ident := (select ident from tableliste where nomc like 'adsys_bailleur' order by ident desc limit 1);

	IF NOT EXISTS(SELECT * FROM d_tableliste WHERE tablen=tableliste_ident AND nchmpc = 'id') THEN
		INSERT INTO d_tableliste(ident, tablen, nchmpc, nchmpl, isreq, ref_field, type, onslct, ispkey, traduit) VALUES ((select max(ident) from d_tableliste)+1, tableliste_ident, 'id', maketraductionlangsyst('Identifiant table Sources de financement pour les crédits'), true, NULL, 'int', false, true, false);

		RAISE NOTICE 'Insertion id de la table d_tableliste effectuée';
		output_result := 2;
	END IF;

	IF NOT EXISTS(SELECT * FROM d_tableliste WHERE tablen=tableliste_ident AND nchmpc = 'libel') THEN
	
		INSERT INTO d_tableliste(ident, tablen, nchmpc, nchmpl, isreq, ref_field, type, onslct, ispkey, traduit) VALUES ((select max(ident) from d_tableliste)+1, tableliste_ident, 'libel', maketraductionlangsyst('Source de financement'), true, NULL, 'txt', false, false, false);

		RAISE NOTICE 'Insertion libel de la table d_tableliste effectuée';
		output_result := 2;
	END IF;

	RAISE NOTICE 'END table adsys_bailleur';

	-- Check if field "id_bailleur" exist in table "ad_dcr"
	IF NOT EXISTS(SELECT * FROM information_schema.columns WHERE table_name = 'ad_dcr' AND column_name = 'id_bailleur') THEN
		ALTER TABLE ad_dcr ADD COLUMN id_bailleur integer;
		output_result := 2;
	END IF;

	-- Insert into "d_tableliste" if notExist
	IF NOT EXISTS(SELECT * FROM d_tableliste WHERE tablen = (select ident from tableliste where nomc like 'ad_dcr' order by ident desc limit 1) AND nchmpc = 'id_bailleur') THEN

		INSERT INTO d_tableliste(ident, tablen, nchmpc, nchmpl, isreq, ref_field, type, onslct, ispkey, traduit) VALUES ((select max(ident) from d_tableliste)+1, (select ident from tableliste where nomc like 'ad_dcr' order by ident desc limit 1), 'id_bailleur', maketraductionlangsyst('Source de financement'), false, NULL, 'int', false, false, false);
		output_result := 2;
	END IF;
	
	-- Check if field "id_bailleur" exist in table "ad_dcr_grp_sol"
	IF NOT EXISTS(SELECT * FROM information_schema.columns WHERE table_name = 'ad_dcr_grp_sol' AND column_name = 'id_bailleur') THEN
		ALTER TABLE ad_dcr_grp_sol ADD COLUMN id_bailleur integer;
		output_result := 2;
	END IF;

	-- Insert into "d_tableliste" if notExist
	IF NOT EXISTS(SELECT * FROM d_tableliste WHERE tablen = (select ident from tableliste where nomc like 'ad_dcr_grp_sol' order by ident desc limit 1) AND nchmpc = 'id_bailleur') THEN

		INSERT INTO d_tableliste(ident, tablen, nchmpc, nchmpl, isreq, ref_field, type, onslct, ispkey, traduit) VALUES ((select max(ident) from d_tableliste)+1, (select ident from tableliste where nomc like 'ad_dcr_grp_sol' order by ident desc limit 1), 'id_bailleur', maketraductionlangsyst('Source de financement'), false, NULL, 'int', false, false, false);
		output_result := 2;
	END IF;

	RAISE NOTICE 'FIN traitement';
	RETURN output_result;

END;
$$
LANGUAGE plpgsql;

SELECT patch_ticket_648();
DROP FUNCTION patch_ticket_648();

------------------------- Ticket #648 : Sources de financement pour les crédits -------------------------

-------------------------------- DEBUT : Ticket #646 : Non connexion d’autres profils lorsque l’agence est fermée --------------------------------

CREATE OR REPLACE FUNCTION init_ticket_646() RETURNS INT AS
$$
DECLARE
output_result INTEGER = 1;

BEGIN

	RAISE NOTICE 'START';

	-- Check if field "conn_agc" exist in table "adsys_profils"
	IF NOT EXISTS(SELECT * FROM information_schema.columns WHERE table_name = 'adsys_profils' AND column_name = 'conn_agc') THEN
		ALTER TABLE adsys_profils ADD COLUMN conn_agc boolean DEFAULT FALSE;
		UPDATE adsys_profils SET conn_agc = 't' WHERE id = 1; -- par defaut seulement admin peut ouvrir une agence
	END IF;

	UPDATE adsys_profils SET conn_agc = CASE WHEN guichet = true THEN false ELSE true END;

	RETURN output_result;

END;
$$
LANGUAGE plpgsql;

SELECT init_ticket_646();
DROP FUNCTION init_ticket_646();

-------------------------------- FIN : Ticket #646 : Non connexion d’autres profils lorsque l’agence est fermée --------------------------------

------------------------------------------------------------------------------------------------
------ Ticket #613: Menu Rapport Chequiers + Ajout rapport Etat des cheques imprimés         ---
------------------------------------------------------------------------------------------------
CREATE OR REPLACE FUNCTION patch_613() RETURNS void AS $BODY$
BEGIN

	-- menu rapport chequier
	IF NOT EXISTS (SELECT * FROM menus WHERE nom_menu = 'Rcq') THEN
		INSERT INTO menus(nom_menu, libel_menu, nom_pere, pos_hierarch, ordre, is_menu, fonction, is_cliquable)
		VALUES ('Rcq', maketraductionlangsyst('Rapports chéquiers'), 'Gen-13',3,6,'t',340,'t');
	END IF;

	-- Change the orders of the menu 'Rapports'
	UPDATE menus SET ordre = 7 WHERE nom_menu = 'Rae';
	UPDATE menus SET ordre = 8 WHERE nom_menu = 'Sra';
	UPDATE menus SET ordre = 9 WHERE nom_menu = 'Dra-1';

	-- Sous menu rapports chequiers
	IF NOT EXISTS (SELECT * FROM menus WHERE nom_menu = 'Rcq-1') THEN
		INSERT INTO menus(nom_menu, libel_menu, nom_pere, pos_hierarch, ordre, is_menu, is_cliquable)
		VALUES ('Rcq-1', maketraductionlangsyst('Sélection type'), 'Rcq',4,1,'f','f');
	END IF;

	IF NOT EXISTS (SELECT * FROM menus WHERE nom_menu = 'Rcq-2') THEN
		INSERT INTO menus(nom_menu, libel_menu, nom_pere, pos_hierarch, ordre, is_menu, is_cliquable)
		VALUES ('Rcq-2', maketraductionlangsyst('Personalisation du rapport'), 'Rcq',4,2,'f','f');
	END IF;

	IF NOT EXISTS (SELECT * FROM menus WHERE nom_menu = 'Rcq-3') THEN
		INSERT INTO menus(nom_menu, libel_menu, nom_pere, pos_hierarch, ordre, is_menu, is_cliquable)
		VALUES ('Rcq-3', maketraductionlangsyst('Impression'), 'Rcq',4,3,'f','f');
	END IF;

	IF NOT EXISTS (SELECT * FROM menus WHERE nom_menu = 'Rcq-4') THEN
		INSERT INTO menus(nom_menu, libel_menu, nom_pere, pos_hierarch, ordre, is_menu, is_cliquable)
		VALUES ('Rcq-4', maketraductionlangsyst('Export données'), 'Rcq',4,4,'f','f');
	END IF;

	--*********************** Les rapports chequiers ****************************************

	-- Ecran selection type rapport chequier
	IF NOT EXISTS (SELECT * FROM ecrans WHERE nom_ecran = 'Rcq-1') THEN
		INSERT INTO ecrans(nom_ecran, fichier, nom_menu, fonction)
		VALUES ('Rcq-1', 'modules/rapports/rapports_chequiers.php', 'Rcq-1', 340);
	END IF;

	---- Rapports Etat des chequiers imprimés
	IF NOT EXISTS (SELECT * FROM ecrans WHERE nom_ecran = 'Rcq-10') THEN
		INSERT INTO ecrans(nom_ecran, fichier, nom_menu, fonction)
		VALUES ('Rcq-10', 'modules/rapports/rapports_chequiers.php', 'Rcq-2', 340); -- Personalisation
	END IF;

	IF NOT EXISTS (SELECT * FROM ecrans WHERE nom_ecran = 'Rcq-11') THEN
		INSERT INTO ecrans(nom_ecran, fichier, nom_menu, fonction)
		VALUES ('Rcq-11', 'modules/rapports/rapports_chequiers.php', 'Rcq-3', 340); -- Impression PDF
	END IF;

	IF NOT EXISTS (SELECT * FROM ecrans WHERE nom_ecran = 'Rcq-12') THEN
		INSERT INTO ecrans(nom_ecran, fichier, nom_menu, fonction)
		VALUES ('Rcq-12', 'modules/rapports/rapports_chequiers.php', 'Rcq-4', 340); -- Export CSV
	END IF;

  	---- Rapports Liste des chequiers commandés
	IF NOT EXISTS (SELECT * FROM ecrans WHERE nom_ecran = 'Rcq-20') THEN
		INSERT INTO ecrans(nom_ecran, fichier, nom_menu, fonction)
		VALUES ('Rcq-20', 'modules/rapports/rapports_chequiers.php', 'Rcq-2', 340); -- Personalisation
	END IF;

	IF NOT EXISTS (SELECT * FROM ecrans WHERE nom_ecran = 'Rcq-21') THEN
		INSERT INTO ecrans(nom_ecran, fichier, nom_menu, fonction)
		VALUES ('Rcq-21', 'modules/rapports/rapports_chequiers.php', 'Rcq-3', 340); -- Impression PDF
	END IF;

	IF NOT EXISTS (SELECT * FROM ecrans WHERE nom_ecran = 'Rcq-22') THEN
		INSERT INTO ecrans(nom_ecran, fichier, nom_menu, fonction)
		VALUES ('Rcq-22', 'modules/rapports/rapports_chequiers.php', 'Rcq-4', 340); -- Export CSV
	END IF;

  ---- Rapports Liste des chequiers envoyés pour impression
	IF NOT EXISTS (SELECT * FROM ecrans WHERE nom_ecran = 'Rcq-30') THEN
		INSERT INTO ecrans(nom_ecran, fichier, nom_menu, fonction)
		VALUES ('Rcq-30', 'modules/rapports/rapports_chequiers.php', 'Rcq-2', 340); -- Personalisation
	END IF;

	IF NOT EXISTS (SELECT * FROM ecrans WHERE nom_ecran = 'Rcq-31') THEN
		INSERT INTO ecrans(nom_ecran, fichier, nom_menu, fonction)
		VALUES ('Rcq-31', 'modules/rapports/rapports_chequiers.php', 'Rcq-3', 340); -- Impression PDF
	END IF;

	IF NOT EXISTS (SELECT * FROM ecrans WHERE nom_ecran = 'Rcq-32') THEN
		INSERT INTO ecrans(nom_ecran, fichier, nom_menu, fonction)
		VALUES ('Rcq-32', 'modules/rapports/rapports_chequiers.php', 'Rcq-4', 340); -- Export CSV
	END IF;

    ---- Rapports Liste des chequiers/cheques misent en opposition
	IF NOT EXISTS (SELECT * FROM ecrans WHERE nom_ecran = 'Rcq-40') THEN
		INSERT INTO ecrans(nom_ecran, fichier, nom_menu, fonction)
		VALUES ('Rcq-40', 'modules/rapports/rapports_chequiers.php', 'Rcq-2', 340); -- Personalisation
	END IF;

	IF NOT EXISTS (SELECT * FROM ecrans WHERE nom_ecran = 'Rcq-41') THEN
		INSERT INTO ecrans(nom_ecran, fichier, nom_menu, fonction)
		VALUES ('Rcq-41', 'modules/rapports/rapports_chequiers.php', 'Rcq-3', 340); -- Impression PDF
	END IF;

	IF NOT EXISTS (SELECT * FROM ecrans WHERE nom_ecran = 'Rcq-42') THEN
		INSERT INTO ecrans(nom_ecran, fichier, nom_menu, fonction)
		VALUES ('Rcq-42', 'modules/rapports/rapports_chequiers.php', 'Rcq-4', 340); -- Export CSV
	END IF;

END;
$BODY$
LANGUAGE plpgsql VOLATILE COST 100;
ALTER FUNCTION patch_613() OWNER TO adbanking;

--------------------- Execution -----------------------------------
SELECT patch_613();
Drop function patch_613();
----------------- FIN : #613: Menu Rapport Chequiers + Ajout rapport Etat des cheques imprimés --------------------

-------------------------------- DEBUT : Ticket #650 : Frais sur retrait : distinguer les frais en fonction du type de retrait --------------------------------
CREATE OR REPLACE FUNCTION init_ticket_650() RETURNS INT AS
$$
DECLARE
output_result INTEGER = 1;

BEGIN

	RAISE NOTICE 'START';

	--- Perception des frais de retrait (Retrait en espèces)
	IF NOT EXISTS(SELECT * FROM ad_cpt_ope WHERE type_operation = 130 AND categorie_ope = 1 AND id_ag = numagc()) THEN
		INSERT INTO ad_cpt_ope(type_operation, categorie_ope, id_ag, libel_ope) VALUES (130, 1, numagc(), maketraductionlangsyst('Perception des frais de retrait (Retrait en espèces)'));

		RAISE NOTICE 'Insertion type_operation 130 dans la table ad_cpt_ope effectuée';
		output_result := 2;
	END IF;

	IF NOT EXISTS(SELECT * FROM ad_cpt_ope_cptes WHERE type_operation = 130 AND sens = 'd' AND categorie_cpte = 1 AND id_ag = numagc()) THEN
		INSERT INTO ad_cpt_ope_cptes(type_operation, num_cpte, sens, categorie_cpte, id_ag) VALUES (130, NULL, 'd', 1, numagc());

		RAISE NOTICE 'Insertion type_operation 130 sens ''d'' dans la table ad_cpt_ope_cptes effectuée';
		output_result := 2;
	END IF;

	IF NOT EXISTS(SELECT * FROM ad_cpt_ope_cptes WHERE type_operation = 130 AND sens = 'c' AND categorie_cpte = 0 AND id_ag = numagc()) THEN
		INSERT INTO ad_cpt_ope_cptes(type_operation, num_cpte, sens, categorie_cpte, id_ag) VALUES (130, NULL, 'c', 0, numagc());

		RAISE NOTICE 'Insertion type_operation 130 sens ''c'' dans la table ad_cpt_ope_cptes effectuée';
		output_result := 2;
	END IF;
	
	
	--- Annulation Perception des frais de retrait (Retrait en espèces)
	IF NOT EXISTS(SELECT * FROM ad_cpt_ope WHERE type_operation = 133 AND categorie_ope = 1 AND id_ag = numagc()) THEN
		INSERT INTO ad_cpt_ope(type_operation, categorie_ope, id_ag, libel_ope) VALUES (133, 1, numagc(), maketraductionlangsyst('Annulation Perception des frais de retrait (Retrait en espèces)'));

		RAISE NOTICE 'Insertion type_operation 133 dans la table ad_cpt_ope effectuée';
		output_result := 2;
	END IF;

	IF NOT EXISTS(SELECT * FROM ad_cpt_ope_cptes WHERE type_operation = 133 AND sens = 'd' AND categorie_cpte = 24 AND id_ag = numagc()) THEN
		INSERT INTO ad_cpt_ope_cptes(type_operation, num_cpte, sens, categorie_cpte, id_ag) VALUES (133, NULL, 'd', 24, numagc());

		RAISE NOTICE 'Insertion type_operation 133 sens ''d'' dans la table ad_cpt_ope_cptes effectuée';
		output_result := 2;
	END IF;

	IF NOT EXISTS(SELECT * FROM ad_cpt_ope_cptes WHERE type_operation = 133 AND sens = 'c' AND categorie_cpte = 1 AND id_ag = numagc()) THEN
		INSERT INTO ad_cpt_ope_cptes(type_operation, num_cpte, sens, categorie_cpte, id_ag) VALUES (133, NULL, 'c', 1, numagc());

		RAISE NOTICE 'Insertion type_operation 133 sens ''c'' dans la table ad_cpt_ope_cptes effectuée';
		output_result := 2;
	END IF;

	
	
	--- Perception des frais de retrait (Retrait cash par chèque interne 
	IF NOT EXISTS(SELECT * FROM ad_cpt_ope WHERE type_operation = 134 AND categorie_ope = 1 AND id_ag = numagc()) THEN
		INSERT INTO ad_cpt_ope(type_operation, categorie_ope, id_ag, libel_ope) VALUES (134, 1, numagc(), maketraductionlangsyst('Perception des frais de retrait (Retrait cash par chèque interne'));

		RAISE NOTICE 'Insertion type_operation 134 dans la table ad_cpt_ope effectuée';
		output_result := 2;
	END IF;

	IF NOT EXISTS(SELECT * FROM ad_cpt_ope_cptes WHERE type_operation = 134 AND sens = 'd' AND categorie_cpte = 1 AND id_ag = numagc()) THEN
		INSERT INTO ad_cpt_ope_cptes(type_operation, num_cpte, sens, categorie_cpte, id_ag) VALUES (134, NULL, 'd', 1, numagc());

		RAISE NOTICE 'Insertion type_operation 134 sens ''d'' dans la table ad_cpt_ope_cptes effectuée';
		output_result := 2;
	END IF;

	IF NOT EXISTS(SELECT * FROM ad_cpt_ope_cptes WHERE type_operation = 134 AND sens = 'c' AND categorie_cpte = 0 AND id_ag = numagc()) THEN
		INSERT INTO ad_cpt_ope_cptes(type_operation, num_cpte, sens, categorie_cpte, id_ag) VALUES (134, NULL, 'c', 0, numagc());

		RAISE NOTICE 'Insertion type_operation 134 sens ''c'' dans la table ad_cpt_ope_cptes effectuée';
		output_result := 2;
	END IF;
	
	
	--- Annulation Perception des frais de retrait (Retrait cash par chèque interne) 
	IF NOT EXISTS(SELECT * FROM ad_cpt_ope WHERE type_operation = 135 AND categorie_ope = 1 AND id_ag = numagc()) THEN
		INSERT INTO ad_cpt_ope(type_operation, categorie_ope, id_ag, libel_ope) VALUES (135, 1, numagc(), maketraductionlangsyst('Annulation Perception des frais de retrait (Retrait cash par chèque interne)'));

		RAISE NOTICE 'Insertion type_operation 135 dans la table ad_cpt_ope effectuée';
		output_result := 2;
	END IF;

	IF NOT EXISTS(SELECT * FROM ad_cpt_ope_cptes WHERE type_operation = 135 AND sens = 'd' AND categorie_cpte = 24 AND id_ag = numagc()) THEN
		INSERT INTO ad_cpt_ope_cptes(type_operation, num_cpte, sens, categorie_cpte, id_ag) VALUES (135, NULL, 'd', 24, numagc());

		RAISE NOTICE 'Insertion type_operation 135 sens ''d'' dans la table ad_cpt_ope_cptes effectuée';
		output_result := 2;
	END IF;

	IF NOT EXISTS(SELECT * FROM ad_cpt_ope_cptes WHERE type_operation = 135 AND sens = 'c' AND categorie_cpte = 1 AND id_ag = numagc()) THEN
		INSERT INTO ad_cpt_ope_cptes(type_operation, num_cpte, sens, categorie_cpte, id_ag) VALUES (135, NULL, 'c', 1, numagc());

		RAISE NOTICE 'Insertion type_operation 135 sens ''c'' dans la table ad_cpt_ope_cptes effectuée';
		output_result := 2;
	END IF;
	
	
	
	--- Perception des frais de retrait (Retrait travelers cheque) 
	IF NOT EXISTS(SELECT * FROM ad_cpt_ope WHERE type_operation = 136 AND categorie_ope = 1 AND id_ag = numagc()) THEN
		INSERT INTO ad_cpt_ope(type_operation, categorie_ope, id_ag, libel_ope) VALUES (136, 1, numagc(), maketraductionlangsyst('Perception des frais de retrait (Retrait travelers cheque)'));

		RAISE NOTICE 'Insertion type_operation 136 dans la table ad_cpt_ope effectuée';
		output_result := 2;
	END IF;

	IF NOT EXISTS(SELECT * FROM ad_cpt_ope_cptes WHERE type_operation = 136 AND sens = 'd' AND categorie_cpte = 1 AND id_ag = numagc()) THEN
		INSERT INTO ad_cpt_ope_cptes(type_operation, num_cpte, sens, categorie_cpte, id_ag) VALUES (136, NULL, 'd', 1, numagc());

		RAISE NOTICE 'Insertion type_operation 136 sens ''d'' dans la table ad_cpt_ope_cptes effectuée';
		output_result := 2;
	END IF;

	IF NOT EXISTS(SELECT * FROM ad_cpt_ope_cptes WHERE type_operation = 136 AND sens = 'c' AND categorie_cpte = 0 AND id_ag = numagc()) THEN
		INSERT INTO ad_cpt_ope_cptes(type_operation, num_cpte, sens, categorie_cpte, id_ag) VALUES (136, NULL, 'c', 0, numagc());

		RAISE NOTICE 'Insertion type_operation 136 sens ''c'' dans la table ad_cpt_ope_cptes effectuée';
		output_result := 2;
	END IF;
	
	
	--- Annulation Perception des frais de retrait (Retrait travelers cheque) 
	IF NOT EXISTS(SELECT * FROM ad_cpt_ope WHERE type_operation = 137 AND categorie_ope = 1 AND id_ag = numagc()) THEN
		INSERT INTO ad_cpt_ope(type_operation, categorie_ope, id_ag, libel_ope) VALUES (137, 1, numagc(), maketraductionlangsyst('Annulation Perception des frais de retrait (Retrait travelers cheque)'));

		RAISE NOTICE 'Insertion type_operation 137 dans la table ad_cpt_ope effectuée';
		output_result := 2;
	END IF;

	IF NOT EXISTS(SELECT * FROM ad_cpt_ope_cptes WHERE type_operation = 137 AND sens = 'd' AND categorie_cpte = 24 AND id_ag = numagc()) THEN
		INSERT INTO ad_cpt_ope_cptes(type_operation, num_cpte, sens, categorie_cpte, id_ag) VALUES (137, NULL, 'd', 24, numagc());

		RAISE NOTICE 'Insertion type_operation 137 sens ''d'' dans la table ad_cpt_ope_cptes effectuée';
		output_result := 2;
	END IF;

	IF NOT EXISTS(SELECT * FROM ad_cpt_ope_cptes WHERE type_operation = 137 AND sens = 'c' AND categorie_cpte = 1 AND id_ag = numagc()) THEN
		INSERT INTO ad_cpt_ope_cptes(type_operation, num_cpte, sens, categorie_cpte, id_ag) VALUES (137, NULL, 'c', 1, numagc());

		RAISE NOTICE 'Insertion type_operation 137 sens ''c'' dans la table ad_cpt_ope_cptes effectuée';
		output_result := 2;
	END IF;
	
	
	--- Perception des frais de retrait (Retrait chèque interne certifié) 
	IF NOT EXISTS(SELECT * FROM ad_cpt_ope WHERE type_operation = 138 AND categorie_ope = 1 AND id_ag = numagc()) THEN
		INSERT INTO ad_cpt_ope(type_operation, categorie_ope, id_ag, libel_ope) VALUES (138, 1, numagc(), maketraductionlangsyst('Perception des frais de retrait (Retrait chèque interne certifié)'));

		RAISE NOTICE 'Insertion type_operation 138 dans la table ad_cpt_ope effectuée';
		output_result := 2;
	END IF;

	IF NOT EXISTS(SELECT * FROM ad_cpt_ope_cptes WHERE type_operation = 138 AND sens = 'd' AND categorie_cpte = 1 AND id_ag = numagc()) THEN
		INSERT INTO ad_cpt_ope_cptes(type_operation, num_cpte, sens, categorie_cpte, id_ag) VALUES (138, NULL, 'd', 1, numagc());

		RAISE NOTICE 'Insertion type_operation 138 sens ''d'' dans la table ad_cpt_ope_cptes effectuée';
		output_result := 2;
	END IF;

	IF NOT EXISTS(SELECT * FROM ad_cpt_ope_cptes WHERE type_operation = 138 AND sens = 'c' AND categorie_cpte = 0 AND id_ag = numagc()) THEN
		INSERT INTO ad_cpt_ope_cptes(type_operation, num_cpte, sens, categorie_cpte, id_ag) VALUES (138, NULL, 'c', 0, numagc());

		RAISE NOTICE 'Insertion type_operation 138 sens ''c'' dans la table ad_cpt_ope_cptes effectuée';
		output_result := 2;
	END IF;
	
	
	--- Annulation Perception des frais de retrait (Retrait chèque interne certifié) 
	IF NOT EXISTS(SELECT * FROM ad_cpt_ope WHERE type_operation = 139 AND categorie_ope = 1 AND id_ag = numagc()) THEN
		INSERT INTO ad_cpt_ope(type_operation, categorie_ope, id_ag, libel_ope) VALUES (139, 1, numagc(), maketraductionlangsyst('Annulation Perception des frais de retrait (Retrait chèque interne certifié)'));

		RAISE NOTICE 'Insertion type_operation 139 dans la table ad_cpt_ope effectuée';
		output_result := 2;
	END IF;

	IF NOT EXISTS(SELECT * FROM ad_cpt_ope_cptes WHERE type_operation = 139 AND sens = 'd' AND categorie_cpte = 24 AND id_ag = numagc()) THEN
		INSERT INTO ad_cpt_ope_cptes(type_operation, num_cpte, sens, categorie_cpte, id_ag) VALUES (139, NULL, 'd', 24, numagc());

		RAISE NOTICE 'Insertion type_operation 139 sens ''d'' dans la table ad_cpt_ope_cptes effectuée';
		output_result := 2;
	END IF;

	IF NOT EXISTS(SELECT * FROM ad_cpt_ope_cptes WHERE type_operation = 139 AND sens = 'c' AND categorie_cpte = 1 AND id_ag = numagc()) THEN
		INSERT INTO ad_cpt_ope_cptes(type_operation, num_cpte, sens, categorie_cpte, id_ag) VALUES (139, NULL, 'c', 1, numagc());

		RAISE NOTICE 'Insertion type_operation 139 sens ''c'' dans la table ad_cpt_ope_cptes effectuée';
		output_result := 2;
	END IF;
	
	-- Check if field "frais_retrait_spec" exist in table "adsys_produit_epargne" 
	IF NOT EXISTS(SELECT * FROM information_schema.columns WHERE table_name = 'adsys_produit_epargne' AND column_name = 'frais_retrait_spec') THEN
		ALTER TABLE adsys_produit_epargne ADD COLUMN frais_retrait_spec  boolean DEFAULT FALSE;
		output_result := 2;
	END IF;
	
	--insert into "d_tableliste" if notExist 
	IF NOT EXISTS(SELECT * FROM d_tableliste WHERE nchmpc = 'frais_retrait_spec') THEN 
		INSERT INTO d_tableliste(ident, tablen, nchmpc, nchmpl, isreq, ref_field, type, onslct, ispkey, traduit) VALUES ((select max(ident) from d_tableliste)+1, (select ident from tableliste where nomc like 'adsys_produit_epargne' order by ident desc limit 1), 'frais_retrait_spec', maketraductionlangsyst('Utiliser les frais de retrait spécifique du module Gestion de la tarification ?'), false, NULL, 'bol', false, false, false);
		output_result := 2;
	END IF;
	

	-- Menu Gestion Tarification
	IF NOT EXISTS(SELECT * FROM adsys_tarification WHERE type_de_frais='EPG_RET_ESPECES') THEN
		INSERT INTO adsys_tarification (id_tarification, code_abonnement, type_de_frais, mode_frais, compte_comptable, date_debut_validite, date_fin_validite, statut, id_ag)
		VALUES (10, 'epargne', 'EPG_RET_ESPECES', '1', null, null, null, 'f', numagc());
		output_result := 2;
	END IF;
	
	IF NOT EXISTS(SELECT * FROM adsys_tarification WHERE type_de_frais='EPG_RET_CHEQUE_INTERNE') THEN
		INSERT INTO adsys_tarification (id_tarification, code_abonnement, type_de_frais, mode_frais, compte_comptable, date_debut_validite, date_fin_validite, statut, id_ag)
		VALUES (11, 'epargne', 'EPG_RET_CHEQUE_INTERNE', '1', null, null, null, 'f', numagc());
		output_result := 2;
	END IF;
	
	IF NOT EXISTS(SELECT * FROM adsys_tarification WHERE type_de_frais='EPG_RET_CHEQUE_TRAVELERS') THEN
		INSERT INTO adsys_tarification (id_tarification, code_abonnement, type_de_frais, mode_frais, compte_comptable, date_debut_validite, date_fin_validite, statut, id_ag)
		VALUES (12, 'epargne', 'EPG_RET_CHEQUE_TRAVELERS', '1', null, null, null, 'f', numagc());
		output_result := 2;
	END IF;
	
	IF NOT EXISTS(SELECT * FROM adsys_tarification WHERE type_de_frais='EPG_RET_CHEQUE_INTERNE_CERTIFIE') THEN
		INSERT INTO adsys_tarification (id_tarification, code_abonnement, type_de_frais, mode_frais, compte_comptable, date_debut_validite, date_fin_validite, statut, id_ag)
		VALUES (13, 'epargne', 'EPG_RET_CHEQUE_INTERNE_CERTIFIE', '1', null, null, null, 'f', numagc());
		output_result := 2;
	END IF;

	--creation champ frais dans la table ad_annulation_retrait_depot
	IF NOT EXISTS(SELECT * FROM information_schema.columns WHERE table_name = 'ad_annulation_retrait_depot' AND column_name = 'frais') THEN
		ALTER TABLE ad_annulation_retrait_depot ADD COLUMN frais numeric(30,6);
		output_result := 2;
	END IF;


	RETURN output_result;
	
END;
$$
LANGUAGE plpgsql;

SELECT init_ticket_650();
DROP FUNCTION init_ticket_650();	
-------------------------------- FIN : Ticket #650 : Frais sur retrait : distinguer les frais en fonction du type de retrait --------------------------------

------------------------- DEBUT : Ticket #668 : CECAD Batch trop lent -------------------------

CREATE OR REPLACE FUNCTION calculetatcredit(integer, date, integer)
  RETURNS integer AS
$BODY$

DECLARE
 id_dossier ALIAS FOR $1;
 date_ref ALIAS FOR $2;
 id_agence ALIAS FOR $3;
 nbr_jours_retard DOUBLE PRECISION;
 interv_min INTEGER;
 interv_max INTEGER;
 etat INTEGER;
 etat_credit INTEGER;
 date_etat_credit DATE;
 nbr_jours_retard_max INTEGER;
 etats_credits CURSOR FOR SELECT * FROM adsys_etat_credits WHERE id_ag = id_agence ORDER BY id;
 ligne RECORD;
 v_passage_perte_automatique boolean;
 
BEGIN

SELECT into  v_passage_perte_automatique passage_perte_automatique FROM ad_agc where id_ag = id_agence;
 nbr_jours_retard := calculnombrejoursretardoss(id_dossier, date_ref, id_agence);
 ---- L'état du crédit est soit en perte, soit à radier si le nombre max de jours est atteint
 SELECT INTO nbr_jours_retard_max sum(nbre_jours)  FROM adsys_etat_credits WHERE nbre_jours > 0 AND id_ag = id_agence;
   IF (nbr_jours_retard >= nbr_jours_retard_max) THEN
     SELECT INTO etat_credit, date_etat_credit cre_etat, cre_date_etat FROM ad_dcr WHERE id_doss = id_dossier;
     SELECT INTO etat id FROM adsys_etat_credits WHERE nbre_jours = -1 AND id_ag = id_agence;
     IF ((etat_credit = etat) AND (date_etat_credit <= date_ref)) THEN -- état à perte
      RETURN etat;
     ELSE -- état à radier
        IF (v_passage_perte_automatique) THEN
			SELECT INTO etat id FROM adsys_etat_credits WHERE nbre_jours = -1 AND id_ag = id_agence;        
        ELSE
			SELECT INTO etat id FROM adsys_etat_credits WHERE nbre_jours = -2 AND id_ag = id_agence;
	 END IF;
      RETURN etat;
      
     END IF;
 ElSEIF (nbr_jours_retard <= 0) THEN --- Crédits sains
   RETURN 1;
 ELSE --- Autres états
 OPEN etats_credits;
 FETCH etats_credits INTO ligne;
  interv_max := -1;
  WHILE FOUND LOOP
    interv_min := interv_max+1;
    interv_max = interv_min + ligne.nbre_jours - 1;
    IF (nbr_jours_retard >= interv_min AND nbr_jours_retard <= interv_max) THEN
     etat := ligne.id;
     exit;
    END IF;
  FETCH etats_credits INTO ligne;
  END LOOP;
  CLOSE etats_credits;
  RETURN etat;
 END IF;
END;
$BODY$
  LANGUAGE plpgsql VOLATILE
  COST 100;
ALTER FUNCTION calculetatcredit(integer, date, integer)
  OWNER TO adbanking;
  
DROP INDEX IF EXISTS idx1_ad_dcr;
CREATE UNIQUE INDEX idx1_ad_dcr
  ON ad_dcr
  USING btree
  (id_ag, id_doss, etat);

DROP INDEX IF EXISTS idx2_ad_dcr;
CREATE UNIQUE INDEX idx2_ad_dcr
  ON ad_dcr
  USING btree
  (id_ag, id_doss, cre_etat);

DROP INDEX IF EXISTS idx1_ad_etr;
CREATE UNIQUE INDEX idx1_ad_etr
  ON ad_etr
  USING btree
  (id_ag, id_doss, date_ech);

------------------------- FIN : Ticket #668 : CECAD Batch trop lent -------------------------

-------------------------------- FIN : Ticket #362 : Retrait par lots ----------------------------------

CREATE OR REPLACE FUNCTION patch_ticket_362() RETURNS INT AS
$$
DECLARE

output_result INTEGER = 1;

BEGIN

	RAISE NOTICE 'START';
	
	------- Retrait par lot  --------
	-- MENUS
	IF NOT EXISTS(SELECT nom_menu FROM menus WHERE nom_menu='Rgu') THEN
		INSERT INTO menus (nom_menu, libel_menu, nom_pere, pos_hierarch, ordre, is_menu, fonction, is_cliquable) VALUES ('Rgu', maketraductionlangsyst('Retrait par lot'), 'Gen-6', 3, 5, true, 154, true);
	END IF;

	IF NOT EXISTS(SELECT nom_menu FROM menus WHERE nom_menu='Rgu-1') THEN
		INSERT INTO menus (nom_menu, libel_menu, nom_pere, pos_hierarch, ordre, is_menu, fonction, is_cliquable) VALUES ('Rgu-1', maketraductionlangsyst('Type de retrait'), 'Rgu', 4, 1, false, NULL, false);
	END IF;

	IF NOT EXISTS(SELECT nom_menu FROM menus WHERE nom_menu='Rgu-2') THEN
		INSERT INTO menus (nom_menu, libel_menu, nom_pere, pos_hierarch, ordre, is_menu, fonction, is_cliquable) VALUES ('Rgu-2', maketraductionlangsyst('Saisie des retraits'), 'Rgu', 4, 2, false, NULL, false);
	END IF;

	IF NOT EXISTS(SELECT nom_menu FROM menus WHERE nom_menu='Rgu-3') THEN
		INSERT INTO menus (nom_menu, libel_menu, nom_pere, pos_hierarch, ordre, is_menu, fonction, is_cliquable) VALUES ('Rgu-3', maketraductionlangsyst('Demande de confirmation des mouvements'), 'Rgu', 4, 3, false, NULL, false);
	END IF;

	IF NOT EXISTS(SELECT nom_menu FROM menus WHERE nom_menu='Rgu-4') THEN
		INSERT INTO menus (nom_menu, libel_menu, nom_pere, pos_hierarch, ordre, is_menu, fonction, is_cliquable) VALUES ('Rgu-4', maketraductionlangsyst('Enregistrement des retraits'), 'Rgu', 4, 4, false, NULL, false);
	END IF;

	-- ECRANS
	IF NOT EXISTS (SELECT nom_ecran FROM ecrans WHERE nom_ecran='Rgu-1') THEN
		INSERT INTO ecrans (nom_ecran, fichier, nom_menu, fonction) VALUES ('Rgu-1', 'modules/guichet/retrait_par_lot.php', 'Rgu-1', 154);
	END IF;

	IF NOT EXISTS (SELECT nom_ecran FROM ecrans WHERE nom_ecran='Rgu-2') THEN
		INSERT INTO ecrans (nom_ecran, fichier, nom_menu, fonction) VALUES ('Rgu-2', 'modules/guichet/retrait_par_lot.php', 'Rgu-2', 154);
	END IF;

	IF NOT EXISTS (SELECT nom_ecran FROM ecrans WHERE nom_ecran='Rgu-3') THEN
		INSERT INTO ecrans (nom_ecran, fichier, nom_menu, fonction) VALUES ('Rgu-3', 'modules/guichet/retrait_par_lot.php', 'Rgu-3', 154);
	END IF;

	IF NOT EXISTS (SELECT nom_ecran FROM ecrans WHERE nom_ecran='Rgu-4') THEN
		INSERT INTO ecrans (nom_ecran, fichier, nom_menu, fonction) VALUES ('Rgu-4', 'modules/guichet/retrait_par_lot.php', 'Rgu-4', 154);
	END IF;
	
	RAISE NOTICE 'END';

	RETURN output_result;

END;
$$
LANGUAGE plpgsql;

SELECT patch_ticket_362();
DROP FUNCTION patch_ticket_362();
	
-------------------------------- FIN : Ticket #362 : Retrait par lots ----------------------------------

-------------------------------- DEBUT : Miscellaneous updates ADBanking 3.18 --------------------------------

CREATE OR REPLACE FUNCTION misc_updates_3_18() RETURNS INT AS
$$
DECLARE
output_result INTEGER = 1;

BEGIN

	RAISE NOTICE 'START UPDATE : Miscellaneous updates ADBanking 3.18';

  -- Create fonction systeme 320
  IF NOT EXISTS(SELECT * FROM adsys_fonction WHERE code_fonction=320 AND id_ag = numagc()) THEN
    INSERT INTO adsys_fonction(code_fonction, libelle, id_ag) VALUES (320, 'Rapports multi-agences', numagc());
    RAISE NOTICE 'Insertion fonction systeme 320 dans la table adsys_fonction effectuée';
    output_result := 2;
  END IF;

  -- Create fonction systeme 320
  IF NOT EXISTS(SELECT * FROM adsys_fonction WHERE code_fonction=340 AND id_ag = numagc()) THEN
    INSERT INTO adsys_fonction(code_fonction, libelle, id_ag) VALUES (340, 'Rapports chéquiers', numagc());
    RAISE NOTICE 'Insertion fonction systeme 340 dans la table adsys_fonction effectuée';
    output_result := 2;
  END IF;

	RAISE NOTICE 'END UPDATE : Miscellaneous updates ADBanking 3.18';

	RETURN output_result;

END;
$$
LANGUAGE plpgsql;

SELECT misc_updates_3_18();
DROP FUNCTION misc_updates_3_18();

-------------------------------- FIN : Miscellaneous updates ADBanking 3.18 --------------------------------

--------------------------- DEBUT : Ticket #425 : Baitoul Maal - Ordres permanents de virement ---------------------------

CREATE OR REPLACE FUNCTION trig_update_ord_perm()
  RETURNS trigger AS
$BODY$
  BEGIN
    IF (NEW.date_prem_exe != OLD.date_prem_exe AND NEW.date_prem_exe >= now()) THEN
      NEW.date_proch_exe = NEW.date_prem_exe;
    END IF;
    IF (NEW.date_dern_exe_th != OLD.date_dern_exe_th AND NEW.date_dern_exe_th >= NEW.date_proch_exe) THEN
      SELECT INTO NEW.date_proch_exe ordreperm_proch_exe(NEW.date_dern_exe_th, NEW.interv, NEW.periodicite);
    END IF;
    RETURN NEW;
  END;
$BODY$
  LANGUAGE plpgsql VOLATILE
  COST 100;
ALTER FUNCTION trig_update_ord_perm()
  OWNER TO adbanking;

--------------------------- FIN : Ticket #425 : Baitoul Maal - Ordres permanents de virement ---------------------------

--------------------------- DEBUT : Ticket #693 : Remboursement automatique des crédits: bloquer le montant ---------------------------

CREATE OR REPLACE FUNCTION patch_ticket_693() RETURNS INT AS
$$
DECLARE

	output_result INTEGER = 1;

BEGIN
	RAISE NOTICE 'DEBUT traitement';

	-- TABLE adsys_produit_credit
	IF NOT EXISTS(SELECT column_name FROM information_schema.columns WHERE table_name='adsys_produit_credit' and column_name='nb_jr_bloq_cre_avant_ech_max') THEN
		ALTER TABLE ONLY adsys_produit_credit ADD COLUMN nb_jr_bloq_cre_avant_ech_max INTEGER DEFAULT 0;

		RAISE NOTICE 'Column nb_jr_bloq_cre_avant_ech_max added in table adsys_produit_credit';
		output_result := 2;
	END IF;

  -- TABLE d_tableliste
	IF NOT EXISTS(SELECT * FROM d_tableliste WHERE tablen=(select ident from tableliste where nomc like 'adsys_produit_credit' order by ident desc limit 1) AND nchmpc = 'nb_jr_bloq_cre_avant_ech_max') THEN
		INSERT INTO d_tableliste(ident, tablen, nchmpc, nchmpl, isreq, ref_field, type, onslct, ispkey, traduit) VALUES ((select max(ident) from d_tableliste)+1, (select ident from tableliste where nomc like 'adsys_produit_credit' order by ident desc limit 1), 'nb_jr_bloq_cre_avant_ech_max', maketraductionlangsyst('Nombre de jours maximum pour bloquer le crédit avant échéance (0 pour ne pas bloquer de crédit)'), false, NULL, 'int', NULL, NULL, false);

		RAISE NOTICE 'Insertion nb_jr_bloq_cre_avant_ech_max de la table d_tableliste effectuée';
		output_result := 2;
	END IF;

  -- TABLE ad_dcr
	IF NOT EXISTS(SELECT column_name FROM information_schema.columns WHERE table_name='ad_dcr' and column_name='nb_jr_bloq_cre_avant_ech') THEN
		ALTER TABLE ONLY ad_dcr ADD COLUMN nb_jr_bloq_cre_avant_ech INTEGER DEFAULT 0;

		RAISE NOTICE 'Column nb_jr_bloq_cre_avant_ech added in table ad_dcr';
		output_result := 2;
	END IF;

	IF NOT EXISTS(SELECT * FROM d_tableliste WHERE tablen=(select ident from tableliste where nomc like 'ad_dcr' order by ident desc limit 1) AND nchmpc = 'nb_jr_bloq_cre_avant_ech') THEN
		INSERT INTO d_tableliste(ident, tablen, nchmpc, nchmpl, isreq, ref_field, type, onslct, ispkey, traduit) VALUES ((select max(ident) from d_tableliste)+1, (select ident from tableliste where nomc like 'ad_dcr' order by ident desc limit 1), 'nb_jr_bloq_cre_avant_ech', maketraductionlangsyst('Nombre de jours pour bloquer le crédit avant échéance'), false, NULL, 'int', NULL, NULL, false);

		RAISE NOTICE 'Insertion nb_jr_bloq_cre_avant_ech de la table d_tableliste effectuée';
		output_result := 2;
	END IF;

	IF NOT EXISTS(SELECT column_name FROM information_schema.columns WHERE table_name='ad_dcr' and column_name='cre_mnt_bloq') THEN
		ALTER TABLE ONLY ad_dcr ADD COLUMN cre_mnt_bloq numeric(30,6) DEFAULT 0;

		RAISE NOTICE 'Column cre_mnt_bloq added in table ad_dcr';
		output_result := 2;
	END IF;

	IF NOT EXISTS(SELECT * FROM d_tableliste WHERE tablen=(select ident from tableliste where nomc like 'ad_dcr' order by ident desc limit 1) AND nchmpc = 'cre_mnt_bloq') THEN
		INSERT INTO d_tableliste(ident, tablen, nchmpc, nchmpl, isreq, ref_field, type, onslct, ispkey, traduit) VALUES ((select max(ident) from d_tableliste)+1, (select ident from tableliste where nomc like 'ad_dcr' order by ident desc limit 1), 'cre_mnt_bloq', maketraductionlangsyst('Montant crédit bloqué'), false, NULL, 'mnt', NULL, NULL, false);

		RAISE NOTICE 'Insertion cre_mnt_bloq de la table d_tableliste effectuée';
		output_result := 2;
	END IF;

  -- TABLE ad_cpt
	IF NOT EXISTS(SELECT column_name FROM information_schema.columns WHERE table_name='ad_cpt' and column_name='mnt_bloq_cre') THEN
		ALTER TABLE ONLY ad_cpt ADD COLUMN mnt_bloq_cre numeric(30,6) DEFAULT 0;

		RAISE NOTICE 'Column mnt_bloq_cre added in table ad_cpt';
		output_result := 2;
	END IF;

	IF NOT EXISTS(SELECT * FROM d_tableliste WHERE tablen=(select ident from tableliste where nomc like 'ad_cpt' order by ident desc limit 1) AND nchmpc = 'mnt_bloq_cre') THEN
		INSERT INTO d_tableliste(ident, tablen, nchmpc, nchmpl, isreq, ref_field, type, onslct, ispkey, traduit) VALUES ((select max(ident) from d_tableliste)+1, (select ident from tableliste where nomc like 'ad_cpt' order by ident desc limit 1), 'mnt_bloq_cre', maketraductionlangsyst('Montant crédit bloqué'), false, NULL, 'mnt', NULL, NULL, false);

		RAISE NOTICE 'Insertion mnt_bloq_cre de la table d_tableliste effectuée';
		output_result := 2;
	END IF;

	RAISE NOTICE 'FIN traitement';
	RETURN output_result;

END;
$$
LANGUAGE plpgsql;

SELECT patch_ticket_693();
DROP FUNCTION patch_ticket_693();

/* Bloquer le montant d'un crédit sur le compte client lié */

-- Function: check_cre_mnt_bloq_ech_date(timestamp without time zone, integer)

-- DROP FUNCTION check_cre_mnt_bloq_ech_date(timestamp without time zone, integer);

CREATE OR REPLACE FUNCTION check_cre_mnt_bloq_ech_date(timestamp without time zone, integer)
  RETURNS integer AS
$BODY$
DECLARE

	p_date_ech ALIAS FOR $1;
	p_nb_jr_bloq ALIAS FOR $2;

	curr_date DATE;
	curr_day INTEGER;
	start_date timestamp without time zone;
	end_date timestamp without time zone;

	output_result INTEGER = 0;
BEGIN
	RAISE NOTICE 'DEBUT traitement';

	IF (p_nb_jr_bloq > 0) THEN

		curr_date	:= NOW();
		curr_day	:= DATE_PART('day', curr_date)::INTEGER;

		-- get start & end date bloq date
		start_date := date(p_date_ech - "interval"(p_nb_jr_bloq||' days'));
		end_date := date(p_date_ech);

		IF ((curr_date > end_date /*AND curr_day >= 25*/) OR (curr_date >= start_date AND curr_date < end_date)) THEN
			output_result := 1;
		END IF;

	END IF;

	RAISE NOTICE 'FIN traitement';

	RETURN output_result;

END;
	$BODY$
		LANGUAGE plpgsql VOLATILE
		COST 100;
	ALTER FUNCTION check_cre_mnt_bloq_ech_date(timestamp without time zone, integer)
		OWNER TO adbanking;

	-- Function: trig_before_update_ad_cpt_mnt_bloq_cre()

	-- DROP FUNCTION trig_before_update_ad_cpt_mnt_bloq_cre();

	CREATE OR REPLACE FUNCTION trig_before_update_ad_cpt_mnt_bloq_cre() RETURNS trigger AS $BODY$
		DECLARE

			old_solde numeric(30,6):=0;
			new_solde numeric(30,6):=0;

			new_solde_dispo numeric(30,6):=0;

			total_montant_ech numeric(30,6):=0;
			total_montant_cre numeric(30,6):=0;

			temp_id_doss INTEGER:=0;
			skip_id_doss INTEGER:=0;

			curr_credit refcursor;
			ligne RECORD;

	  BEGIN

			RAISE NOTICE 'DEBUT traitement';

			-- Get old & new solde
			old_solde := OLD.solde;
			new_solde := NEW.solde;

			-- if solde compte increased
			IF (new_solde > old_solde) THEN

				-- Get old & new solde dispo
				new_solde_dispo := (NEW.solde - NEW.mnt_bloq - NEW.mnt_min_cpte + NEW.decouvert_max - NEW.mnt_bloq_cre);

				-- Open the cursor
				OPEN curr_credit FOR SELECT d.id_doss, d.id_client, d.cpt_liaison, d.nb_jr_bloq_cre_avant_ech, d.cre_mnt_bloq, e.id_ech, e.date_ech, COALESCE(e.solde_gar,0) AS solde_gar, COALESCE(e.solde_pen,0) AS solde_pen, COALESCE(e.solde_int,0) AS solde_int, COALESCE(e.solde_cap,0) AS solde_cap FROM ad_dcr d INNER JOIN ad_etr e ON e.id_doss = d.id_doss AND e.id_ag = d.id_ag WHERE d.id_ag = OLD.id_ag AND d.cpt_liaison = OLD.id_cpte AND d.prelev_auto = TRUE AND d.is_ligne_credit = FALSE AND (d.etat = 5 OR d.etat = 7 OR d.etat = 9 OR d.etat = 13 OR d.etat = 14 OR d.etat = 15) AND e.date_ech <= (NOW() + "interval"(nb_jr_bloq_cre_avant_ech::INTEGER||' days')) AND e.remb = 'f' AND 1 = check_cre_mnt_bloq_ech_date(e.date_ech, d.nb_jr_bloq_cre_avant_ech::INTEGER) ORDER BY d.id_doss ASC, e.id_ech ASC;

				FETCH curr_credit INTO ligne;
				WHILE FOUND LOOP

						IF (skip_id_doss != ligne.id_doss) THEN

							IF (temp_id_doss != ligne.id_doss) THEN

								-- Update cre mnt bloq
								total_montant_cre := total_montant_cre + total_montant_ech;

								-- Get new solde dispo
								new_solde_dispo := new_solde_dispo - total_montant_ech + ligne.cre_mnt_bloq;

								-- Reset total montant ech
								total_montant_ech := 0;

								-- Set current id doss
								temp_id_doss := ligne.id_doss;
							END IF;

							IF (total_montant_ech < new_solde_dispo AND (total_montant_ech + ligne.solde_gar) <=	new_solde_dispo) THEN
								-- Cumul garantie
								total_montant_ech := (total_montant_ech + (ligne.solde_gar));
							ELSE
									-- Cumul garantie
									total_montant_ech := (total_montant_ech + (ligne.solde_gar - ((total_montant_ech + ligne.solde_gar) - new_solde_dispo)));

									skip_id_doss := temp_id_doss;
									--EXIT;  -- Exit loop
							END IF;

							IF (total_montant_ech < new_solde_dispo AND (total_montant_ech + ligne.solde_pen) <= new_solde_dispo) THEN
								-- Cumul penalité
								total_montant_ech := (total_montant_ech + (ligne.solde_pen));
							ELSE
									-- Cumul penalité
									total_montant_ech := (total_montant_ech + (ligne.solde_pen - ((total_montant_ech + ligne.solde_pen) - new_solde_dispo)));

									skip_id_doss := temp_id_doss;
									--EXIT;  -- Exit loop
							END IF;

							IF (total_montant_ech < new_solde_dispo AND (total_montant_ech + ligne.solde_int) <= new_solde_dispo) THEN
								-- Cumul intérêt
								total_montant_ech := (total_montant_ech + (ligne.solde_int));
							ELSE
									-- Cumul intérêt
									total_montant_ech := (total_montant_ech + (ligne.solde_int - ((total_montant_ech + ligne.solde_int) - new_solde_dispo)));

									skip_id_doss := temp_id_doss;
									--EXIT;  -- Exit loop
							END IF;

							IF (total_montant_ech < new_solde_dispo AND (total_montant_ech + ligne.solde_cap) <= new_solde_dispo) THEN
								-- Cumul capital
								total_montant_ech := (total_montant_ech + (ligne.solde_cap));
							ELSE
									-- Cumul capital
									total_montant_ech := (total_montant_ech + (ligne.solde_cap - ((total_montant_ech + ligne.solde_cap) - new_solde_dispo)));

									skip_id_doss := temp_id_doss;
									--EXIT;  -- Exit loop
							END IF;

							-- Update cre mnt bloq
							UPDATE ad_dcr SET cre_mnt_bloq = total_montant_ech WHERE id_doss = ligne.id_doss AND id_ag = OLD.id_ag;

						END IF;

				FETCH curr_credit INTO ligne;
				END LOOP;

				-- Close the cursor
				CLOSE curr_credit;

				-- Update cpte mnt bloq
				NEW.mnt_bloq_cre := total_montant_cre + total_montant_ech;

			END IF;

			RAISE NOTICE 'FIN traitement';

			RETURN NEW;
	  END;
		$BODY$
	  LANGUAGE plpgsql VOLATILE COST 100;

	-- Trigger: trig_before_update_ad_cpt_mnt_bloq_cre on ad_cpt

	DROP TRIGGER IF EXISTS trig_before_update_ad_cpt_mnt_bloq_cre ON ad_cpt;

	CREATE TRIGGER trig_before_update_ad_cpt_mnt_bloq_cre BEFORE UPDATE ON ad_cpt
	FOR EACH ROW EXECUTE PROCEDURE trig_before_update_ad_cpt_mnt_bloq_cre();

--------------------------- FIN : Ticket #693 : Remboursement automatique des crédits: bloquer le montant ---------------------------

--------------------------- DEBUT : Ticket #705 : Erreur sur Saisie ecriture libre ---------------------------
CREATE OR REPLACE FUNCTION patch_ticket_705()
	RETURNS INT AS
$$
DECLARE

	output_result INTEGER = 1;

BEGIN
	RAISE NOTICE 'DEBUT traitement';

	IF EXISTS(SELECT constraint_name
						FROM information_schema.constraint_table_usage
						WHERE table_name = 'ad_brouillard' AND constraint_name = 'ad_brouillard_id_his_compte_id_sens_id_ag_key')
	THEN
		ALTER TABLE ad_brouillard DROP CONSTRAINT ad_brouillard_id_his_compte_id_sens_id_ag_key;
		-- ecraser le "constraint:ad_brouillard_id_his_compte_id_sens_id_ag_key" actuel
		output_result := 2;
	END IF;


  IF NOT EXISTS(SELECT constraint_name
						FROM information_schema.constraint_table_usage
						WHERE table_name = 'ad_brouillard' AND constraint_name = 'ad_brouillard_id_his_compte_id_sens_id_ag_cpte_interne_cli_key')
	THEN
	  ALTER TABLE ad_brouillard ADD CONSTRAINT ad_brouillard_id_his_compte_id_sens_id_ag_cpte_interne_cli_key
	  UNIQUE (id_his, compte, id, sens, id_ag, cpte_interne_cli);
	  -- ajout du nouveau "constraint:ad_brouillard_id_his_compte_id_sens_id_ag_cpte_interne_cli_key"
	  output_result := 2;
	END IF;

	RAISE NOTICE 'FIN traitement';

	RETURN output_result;
END;
$$
LANGUAGE plpgsql VOLATILE COST 100;

SELECT patch_ticket_705();
DROP FUNCTION patch_ticket_705();
--------------------------- FIN : Ticket #705 : Erreur sur Saisie ecriture libre ---------------------------
--------------------------- DEBUT : Ticket #703 : Annulation retrait par chèque : etat du chèque ---------------------------
CREATE OR REPLACE FUNCTION patch_ticket_703() RETURNS INT AS
	$$
   DECLARE

  output_result INTEGER = 1;

	BEGIN
		RAISE NOTICE 'DEBUT traitement';
		
		--- Annulation Retrait interne certifié
		IF NOT EXISTS(SELECT * FROM ad_cpt_ope WHERE type_operation = 547 AND categorie_ope = 1 AND id_ag = numagc()) THEN
			INSERT INTO ad_cpt_ope(type_operation, categorie_ope, id_ag, libel_ope) VALUES (547, 1, numagc(), maketraductionlangsyst('Annulation Retrait interne certifié'));

			RAISE NOTICE 'Insertion type_operation 547 dans la table ad_cpt_ope effectuée';
			output_result := 2;
		END IF;

		IF NOT EXISTS(SELECT * FROM ad_cpt_ope_cptes WHERE type_operation = 547 AND sens = 'd' AND categorie_cpte = 4 AND id_ag = numagc()) THEN
			INSERT INTO ad_cpt_ope_cptes(type_operation, num_cpte, sens, categorie_cpte, id_ag) VALUES (547, NULL, 'd', 4, numagc());

			RAISE NOTICE 'Insertion type_operation 547 sens ''d'' dans la table ad_cpt_ope_cptes effectuée';
			output_result := 2;
		END IF;

		IF NOT EXISTS(SELECT * FROM ad_cpt_ope_cptes WHERE type_operation = 547 AND sens = 'c' AND categorie_cpte = 1 AND id_ag = numagc()) THEN
			INSERT INTO ad_cpt_ope_cptes(type_operation, num_cpte, sens, categorie_cpte, id_ag) VALUES (547, NULL, 'c', 1, numagc());

			RAISE NOTICE 'Insertion type_operation 547 sens ''c'' dans la table ad_cpt_ope_cptes effectuée';
			output_result := 2;
		END IF;

		output_result := 2;

		RAISE NOTICE 'FIN traitement';

		RETURN output_result;
	END;
	$$
LANGUAGE plpgsql VOLATILE COST 100;
ALTER FUNCTION patch_ticket_703()
  OWNER TO adbanking;

SELECT patch_ticket_703();
DROP FUNCTION patch_ticket_703();
--------------------------- FIN : Ticket #703 : Annulation retrait par chèque : etat du chèque ---------------------------
--------------------------- DEBUT : Ticket #715 : Blocage batch agence Siege ---------------------------

-- Function: compare_compta_cpte_interne_credit(date)

DROP FUNCTION IF EXISTS compare_compta_cpte_interne_credit(date);

CREATE OR REPLACE FUNCTION compare_compta_cpte_interne_credit(date)
	RETURNS SETOF type_cpte_dcr AS
$BODY$
DECLARE
	date_batch ALIAS FOR $1;
	ligne type_cpte_dcr;
BEGIN
	DROP TABLE IF EXISTS solde_compta;
	DROP TABLE IF EXISTS solde_cpt;
	CREATE TEMP TABLE solde_compta AS
		select  cpte_interne_cli,sum(CASE WHEN sens = 'c' THEN -montant ELSE montant END) AS solde_compta
		FROM ad_mouvement m
		WHERE (m.compte IN (SELECT DISTINCT num_cpte_comptable FROM adsys_etat_credit_cptes WHERE  id_etat_credit not in (select id from adsys_etat_credits where nbre_jours = -1)) )
					AND date_valeur <= DATE(date_batch)
		GROUP BY cpte_interne_cli;
	CREATE TEMP TABLE solde_cpt AS
		select  cpte_interne_cli,sum(CASE WHEN sens = 'c' THEN -montant ELSE montant END) AS solde_cpt
		from ad_mouvement
		where cpte_interne_cli IN ( select distinct cre_id_cpte from  ad_dcr d where d.cre_date_debloc <= DATE(date_batch) AND (etat IN (5,7,8,13,14,15) OR (etat IN (6,9,12) AND date_etat > DATE(date_batch))))
					AND date_valeur <= DATE( date_batch)
		GROUP BY cpte_interne_cli;

	FOR ligne IN (select NULL,(select distinct id_doss from ad_dcr where cre_id_cpte in (b.cpte_interne_cli) limit 1),(select distinct num_complet_cpte from ad_cpt where id_cpte in (b.cpte_interne_cli) limit 1),solde_compta,solde_cpt
								from solde_compta a, solde_cpt b
								where  a.cpte_interne_cli = b.cpte_interne_cli and a.solde_compta != b.solde_cpt)
	LOOP
		RETURN NEXT ligne;
	END LOOP;
	RETURN ;

END;
$BODY$
LANGUAGE plpgsql VOLATILE
COST 100
ROWS 1000;
ALTER FUNCTION compare_compta_cpte_interne_credit(date)
OWNER TO postgres;

-- Function: compare_credit_cpte_interne(date)

DROP FUNCTION IF EXISTS compare_credit_cpte_interne(date);

CREATE OR REPLACE FUNCTION compare_credit_cpte_interne(date)
	RETURNS SETOF type_cpte_dcr AS
$BODY$
DECLARE
	date_batch ALIAS FOR $1;
	ligne type_cpte_dcr;
BEGIN
	DROP TABLE IF EXISTS solde_cpt;
	DROP TABLE IF EXISTS mnt_etr;
	DROP TABLE IF EXISTS mnt_sre;
	CREATE TEMP TABLE solde_cpt AS
		select  cpte_interne_cli,sum(CASE WHEN sens = 'c' THEN -montant ELSE montant END) AS solde_cpt
		from ad_mouvement where cpte_interne_cli IN
														( select cre_id_cpte from  ad_dcr d where d.cre_date_debloc <= DATE(date_batch) AND (etat IN (5,7,8,13,14,15) OR (etat IN (6,9) AND date_etat > DATE(date_batch))))
														AND date_valeur <= DATE(date_batch)
		GROUP BY cpte_interne_cli;

	CREATE TEMP TABLE mnt_etr AS
		select  d.cre_id_cpte,d.id_doss,d.id_client, sum(mnt_cap) AS mnt_att from ad_etr e, ad_dcr d where e.id_doss = d.id_doss and d.cre_date_debloc <= DATE(date_batch) AND (etat IN (5,7,8,13,14,15) OR (etat IN (6,9) AND date_etat > DATE(date_batch)))
		GROUP BY d.cre_id_cpte,d.id_doss,d.id_client;

	CREATE TEMP TABLE mnt_sre AS
		select  d.cre_id_cpte, sum(mnt_remb_cap) AS mnt_remb from ad_sre e, ad_dcr d where e.id_doss = d.id_doss and e.date_remb <= DATE(date_batch) and d.cre_date_debloc <= DATE(date_batch) AND (etat IN (5,7,8,13,14,15) OR (etat IN (6,9) AND date_etat > DATE(date_batch)))
		GROUP BY d.cre_id_cpte;

	FOR ligne IN select e.id_client,e.id_doss,(select distinct num_complet_cpte from ad_cpt where id_cpte in (c.cpte_interne_cli) limit 1) , c.solde_cpt, (e.mnt_att-s.mnt_remb) as solde_cap_ech
							 from solde_cpt c, mnt_etr e, mnt_sre s
							 where c.cpte_interne_cli = e.cre_id_cpte and e.cre_id_cpte = s.cre_id_cpte and c.solde_cpt != e.mnt_att-s.mnt_remb
	LOOP
		RETURN NEXT ligne;
	END LOOP;
	RETURN ;

END;
$BODY$
LANGUAGE plpgsql VOLATILE
COST 100
ROWS 1000;
ALTER FUNCTION compare_credit_cpte_interne(date)
OWNER TO postgres;

--------------------------- FIN : Ticket #715 : Blocage batch agence Siege ---------------------------

--------------------------- DEBUT : Ticket #667 : Rapport "Inventaire de Credits" ---------------------------
CREATE OR REPLACE FUNCTION patch_ticket_667() RETURNS INT AS
	$$
   DECLARE

  output_result INTEGER = 1;

	BEGIN
		RAISE NOTICE 'DEBUT traitement';



    IF NOT EXISTS (SELECT nom_ecran FROM ecrans WHERE nom_ecran='Kra-101') THEN
        INSERT INTO ecrans(nom_ecran, fichier, nom_menu, fonction) VALUES ('Kra-101', 'modules/rapports/rapports_credit.php', 'Kra-2', 350);
      END IF;

      IF NOT EXISTS (SELECT nom_ecran FROM ecrans WHERE nom_ecran='Kra-102') THEN
        INSERT INTO ecrans(nom_ecran, fichier, nom_menu, fonction) VALUES ('Kra-102', 'modules/rapports/rapports_credit.php', 'Kra-3', 350);
      END IF;

      IF NOT EXISTS (SELECT nom_ecran FROM ecrans WHERE nom_ecran='Kra-103') THEN
        INSERT INTO ecrans(nom_ecran, fichier, nom_menu, fonction) VALUES ('Kra-103', 'modules/rapports/rapports_credit.php', 'Kra-5', 350);
      END IF;


      RAISE NOTICE 'END UPDATE : Trac#667 : Inventaire de Credits';
      RETURN output_result;

	END;
	$$
LANGUAGE plpgsql VOLATILE COST 100;
ALTER FUNCTION patch_ticket_667()
  OWNER TO adbanking;

SELECT patch_ticket_667();
DROP FUNCTION patch_ticket_667();
--------------------------- FIN : Ticket #667 : Rapport "Inventaire de Credits" ---------------------------

--------------------------- DEBUT : Ticket #708 : Transfert entre compte impossible ---------------------------
CREATE OR REPLACE FUNCTION patch_ticket_708() RETURNS INT AS
	$$
   DECLARE

    output_result INTEGER = 1;

	BEGIN
		RAISE NOTICE 'DEBUT traitement';
		
		--- Transfert entre compte impossible
		PERFORM reset_sequence('adsys_produit_epargne', 'adsys_produit_epargne_id_seq', 'id');

		output_result := 2;

		RAISE NOTICE 'FIN traitement';

		RETURN output_result;
	END;
	$$
LANGUAGE plpgsql VOLATILE COST 100;
ALTER FUNCTION patch_ticket_708()
  OWNER TO adbanking;

SELECT patch_ticket_708();
DROP FUNCTION patch_ticket_708();
--------------------------- FIN : Ticket #708 : Transfert entre compte impossible---------------------------

------------------------- Ticket #717: Anomalie Rapport des comptes dormants à une date antérieure -------------------------

CREATE OR REPLACE FUNCTION calculetatcpte_hist(
    integer,
    integer,
    date)
  RETURNS integer AS
$BODY$

DECLARE
 v_id_ag ALIAS FOR $1;
 v_id_cpte ALIAS FOR $2;
 v_date_rapport ALIAS FOR $3;
 v_etat_cpte INTEGER;
 v_date_counter DATE;

cur_etats_cpte CURSOR FOR
select date_action, etat_cpte from (
SELECT date(date_action) as date_action, max(etat_cpte) as etat_cpte
FROM ad_cpt_hist WHERE id_cpte = v_id_cpte and id_ag = v_id_ag group by date(date_action)

UNION

select date(now()) as date_action, etat_cpte from ad_cpt where id_cpte = v_id_cpte and id_ag = v_id_ag
) A order by date_action desc ;

 --SELECT date(date_action) as date_action, max(etat) as etat FROM stg_ad_dcr_hist WHERE id_doss = v_id_dossier and id_ag = v_id_ag group by date(date_action) ORDER BY date(date_action) desc;
 v_ligne RECORD;

BEGIN
RAISE NOTICE 'DEBUT traitement';

 OPEN cur_etats_cpte;
 FETCH cur_etats_cpte INTO v_ligne;

  WHILE FOUND LOOP

	v_date_counter := v_ligne.date_action;

	WHILE (v_date_counter > v_date_rapport) LOOP

	v_etat_cpte:= v_ligne.etat_cpte;


	EXIT;

	v_date_counter := v_date_counter - 1;

	END LOOP;

  FETCH cur_etats_cpte INTO v_ligne;
  END LOOP;

  CLOSE cur_etats_cpte;
  RAISE NOTICE 'FIN traitement';
  RETURN v_etat_cpte;

END;
$BODY$
  LANGUAGE plpgsql VOLATILE
  COST 100;
ALTER FUNCTION calculetatcpte_hist(integer, integer, date)
  OWNER TO postgres;

-------------------------- FIN : Ticket #717 :Anomalie Rapport des comptes dormants à une date antérieure --------------------------------
-------------------------- DEBUT : Ticket #709 :Anomalie Rapport epargne Etat général des comptes --------------------------------
CREATE OR REPLACE FUNCTION calculetatcpte_epargne_hist(integer, integer, date)
  RETURNS integer AS
$BODY$

DECLARE
 v_id_ag ALIAS FOR $1;
 v_id_cpte ALIAS FOR $2;
 v_date_rapport ALIAS FOR $3;
 v_etat INTEGER:=0;
 v_date_counter DATE;

cur_etats_cpte CURSOR FOR
 select date_action, etat_cpte from (
SELECT date_action::date as date_action, etat_cpte as etat_cpte, id
FROM ad_cpt_hist WHERE id_cpte = v_id_cpte and id_ag = v_id_ag

UNION

select now() as date_action, etat_cpte, 9999999999999999 as id from ad_cpt where id_cpte = v_id_cpte and id_ag = v_id_ag
) A order by id desc;


 v_ligne RECORD;

BEGIN

 OPEN cur_etats_cpte;
 FETCH cur_etats_cpte INTO v_ligne;

  WHILE FOUND LOOP

	v_date_counter := v_ligne.date_action;

	--Raise notice 'v_date_counter --> %', v_date_counter;

	WHILE (v_date_counter >= v_date_rapport) LOOP

	v_etat:= v_ligne.etat_cpte;

	--Raise notice 'v_etat --> %', v_etat;
	
	EXIT;

	v_date_counter := v_date_counter - 1;

	END LOOP;

  FETCH cur_etats_cpte INTO v_ligne;
  END LOOP;

  CLOSE cur_etats_cpte;
  RETURN v_etat;

END;
$BODY$
  LANGUAGE plpgsql VOLATILE
  COST 100;
ALTER FUNCTION calculetatcpte_epargne_hist(integer, integer, date)
  OWNER TO adbanking;
-------------------------- FIN : Ticket #709 :Anomalie Rapport epargne Etat général des comptes --------------------------------
-------------------------- DEBUT : Ticket #717 - pp#241  - Anomalie Rapport des comptes dormants à une date antérieure(Ticket trac 717) - Fix : ----------------------------------------------
CREATE OR REPLACE FUNCTION calculsoldecptedormant(integer, date, date)
  RETURNS numeric AS
$BODY$
DECLARE
	param_id_cpte ALIAS FOR $1;
	date_min ALIAS FOR $2;
	date_sup ALIAS FOR $3;
	mnt_dep numeric;
	mnt_debit numeric;
	mnt_credit numeric;
	date_inf DATE;
	num_cpte_dormant text;
BEGIN
	date_inf := date_min;
	IF (date_min IS NULL) THEN
	 SELECT INTO date_inf date_ouvert FROM ad_cpt c WHERE c.id_cpte =  param_id_cpte;
	END IF;

	select into num_cpte_dormant num_cpte FROM ad_cpt_ope_cptes WHERE type_operation = 170 AND sens = 'c' AND id_ag = numagc();

	--Raise Notice 'Cpte Dormant -> % and Id cpte -> %',num_cpte_dormant,param_id_cpte;

	--Raise Notice 'date_inf -> % and date_sup -> %',date_inf,date_sup;

	select into mnt_debit sum(m.montant) from ad_mouvement m, ad_ecriture e where m.id_ecriture = e.id_ecriture
	and to_number(e.info_ecriture,'9999999999') = param_id_cpte
	and m.compte = num_cpte_dormant and m.sens = 'd' and e.type_operation = 170
	--and date(m.date_valeur) >= date(date_inf)
	and date(m.date_valeur) <= date(date_sup);

	select into mnt_credit sum(m.montant) from ad_mouvement m, ad_ecriture e where m.id_ecriture = e.id_ecriture
	and to_number(e.info_ecriture,'9999999999') = param_id_cpte
	and m.compte = num_cpte_dormant and m.sens = 'c' and e.type_operation = 170
	--and date(m.date_valeur) >= date(date_inf)
	and date(m.date_valeur) <= date(date_sup);

	IF mnt_credit IS NULL THEN
		mnt_credit = 0;
	END IF;
	IF mnt_debit IS NULL THEN
		mnt_debit = 0;
	END IF;
	mnt_dep = mnt_credit - mnt_debit;
	--Raise Notice 'Cpte % | Mnt Debit -> % | Mnt Credit -> % | Solde -> %',param_id_cpte,mnt_debit,mnt_credit,mnt_dep;
	RETURN mnt_dep;
END
$BODY$
  LANGUAGE plpgsql VOLATILE
  COST 100;
ALTER FUNCTION calculsoldecptedormant(integer, date, date)
  OWNER TO adbanking;
-------------------------- FIN : Ticket #717 - pp#241  - Anomalie Rapport des comptes dormants à une date antérieure(Ticket trac 717) - Fix ----------------------------------------------