
----------------------------------------- DEBUT : Ticket #477 - PROJET ANNULATION RETRAIT ET DÉPÔT -----------------------------------------------------------

CREATE OR REPLACE FUNCTION init_projet_annulation_retrait_depot() RETURNS INT AS
$$
DECLARE
output_result INTEGER = 1;

BEGIN

	RAISE NOTICE 'START';

	-- CREATE TABLE ad_annulation_retrait_depot
	IF NOT EXISTS(SELECT * FROM information_schema.tables WHERE table_name = 'ad_annulation_retrait_depot') THEN
		-- DROP TABLE ad_annulation_retrait_depot;
		CREATE TABLE ad_annulation_retrait_depot
		(
			id serial NOT NULL,
			id_ag integer NOT NULL,
			login character varying(50) NOT NULL,
			id_his integer NOT NULL,
			annul_id_his integer NULL,
			id_client integer NOT NULL,
			etat_annul integer NOT NULL, -- 1:enregistré, 2:autorisé, 3:rejeté, 4:effectué, 5:supprimé
			fonc_sys integer NOT NULL, -- 70:Retrait, 75:Dépôt, 85:Retrait express, 86:Dépôt express
			type_ope integer NULL,
			montant numeric(30,6) NULL,
			devise character(3) NULL,
			comments text,
			date_crea timestamp without time zone NOT NULL DEFAULT now(),
			date_modif timestamp without time zone NULL,
			date_annul timestamp without time zone NULL,
			CONSTRAINT ad_annulation_retrait_depot_pkey PRIMARY KEY (id, id_ag)
		)
		WITH (
		  OIDS=FALSE
		);

		RAISE NOTICE 'Table ad_annulation_retrait_depot created';
		output_result := 2;
	END IF;

	-- Create fonction systeme
	IF NOT EXISTS(SELECT * FROM adsys_fonction WHERE code_fonction=60 AND id_ag = numagc()) THEN

		INSERT INTO adsys_fonction(code_fonction, libelle, id_ag) VALUES (60, 'Gestion Annulation Retrait et Dépôt', numagc());

		RAISE NOTICE 'Insertion fonction systeme 60 dans la table adsys_fonction effectuée';
		output_result := 2;
	END IF;

	IF NOT EXISTS(SELECT * FROM adsys_fonction WHERE code_fonction=61 AND id_ag = numagc()) THEN

		INSERT INTO adsys_fonction(code_fonction, libelle, id_ag) VALUES (61, 'Demande annulation retrait / dépôt', numagc());

		RAISE NOTICE 'Insertion fonction systeme 61 dans la table adsys_fonction effectuée';
		output_result := 2;
	END IF;

	IF NOT EXISTS(SELECT * FROM adsys_fonction WHERE code_fonction=62 AND id_ag = numagc()) THEN

		INSERT INTO adsys_fonction(code_fonction, libelle, id_ag) VALUES (62, 'Approbation demande annulation retrait / dépôt', numagc());

		RAISE NOTICE 'Insertion fonction systeme 62 dans la table adsys_fonction effectuée';
		output_result := 2;
	END IF;

	IF NOT EXISTS(SELECT * FROM adsys_fonction WHERE code_fonction=63 AND id_ag = numagc()) THEN

		INSERT INTO adsys_fonction(code_fonction, libelle, id_ag) VALUES (63, 'Effectuer annulation retrait / dépôt', numagc());

		RAISE NOTICE 'Insertion fonction systeme 63 dans la table adsys_fonction effectuée';
		output_result := 2;
	END IF;

	IF NOT EXISTS(SELECT * FROM adsys_fonction WHERE code_fonction=65 AND id_ag = numagc()) THEN

		INSERT INTO adsys_fonction(code_fonction, libelle, id_ag) VALUES (65, 'Annulation Retrait', numagc());

		RAISE NOTICE 'Insertion fonction systeme 65 dans la table adsys_fonction effectuée';
		output_result := 2;
	END IF;

	IF NOT EXISTS(SELECT * FROM adsys_fonction WHERE code_fonction=66 AND id_ag = numagc()) THEN

		INSERT INTO adsys_fonction(code_fonction, libelle, id_ag) VALUES (66, 'Annulation Dépôt', numagc());

		RAISE NOTICE 'Insertion fonction systeme 66 dans la table adsys_fonction effectuée';
		output_result := 2;
	END IF;

	-- Création opérations financière
	-- Annulation retrait
	--- Annulation Retrait en espèces
	IF NOT EXISTS(SELECT * FROM ad_cpt_ope WHERE type_operation = 144 AND categorie_ope = 1 AND id_ag = numagc()) THEN
		INSERT INTO ad_cpt_ope(type_operation, categorie_ope, id_ag, libel_ope) VALUES (144, 1, numagc(), maketraductionlangsyst('Annulation Retrait en espèces'));

		RAISE NOTICE 'Insertion type_operation 144 dans la table ad_cpt_ope effectuée';
		output_result := 2;
	END IF;

	IF NOT EXISTS(SELECT * FROM ad_cpt_ope_cptes WHERE type_operation = 144 AND sens = 'd' AND categorie_cpte = 4 AND id_ag = numagc()) THEN
		INSERT INTO ad_cpt_ope_cptes(type_operation, num_cpte, sens, categorie_cpte, id_ag) VALUES (144, NULL, 'd', 4, numagc());

		RAISE NOTICE 'Insertion type_operation 144 sens ''d'' dans la table ad_cpt_ope_cptes effectuée';
		output_result := 2;
	END IF;

	IF NOT EXISTS(SELECT * FROM ad_cpt_ope_cptes WHERE type_operation = 144 AND sens = 'c' AND categorie_cpte = 1 AND id_ag = numagc()) THEN
		INSERT INTO ad_cpt_ope_cptes(type_operation, num_cpte, sens, categorie_cpte, id_ag) VALUES (144, NULL, 'c', 1, numagc());

		RAISE NOTICE 'Insertion type_operation 144 sens ''c'' dans la table ad_cpt_ope_cptes effectuée';
		output_result := 2;
	END IF;

	--- Annulation Retrait cash par chèque interne
	IF NOT EXISTS(SELECT * FROM ad_cpt_ope WHERE type_operation = 542 AND categorie_ope = 1 AND id_ag = numagc()) THEN
		INSERT INTO ad_cpt_ope(type_operation, categorie_ope, id_ag, libel_ope) VALUES (542, 1, numagc(), maketraductionlangsyst('Annulation Retrait cash par chèque interne'));

		RAISE NOTICE 'Insertion type_operation 542 dans la table ad_cpt_ope effectuée';
		output_result := 2;
	END IF;

	IF NOT EXISTS(SELECT * FROM ad_cpt_ope_cptes WHERE type_operation = 542 AND sens = 'd' AND categorie_cpte = 4 AND id_ag = numagc()) THEN
		INSERT INTO ad_cpt_ope_cptes(type_operation, num_cpte, sens, categorie_cpte, id_ag) VALUES (542, NULL, 'd', 4, numagc());

		RAISE NOTICE 'Insertion type_operation 542 sens ''d'' dans la table ad_cpt_ope_cptes effectuée';
		output_result := 2;
	END IF;

	IF NOT EXISTS(SELECT * FROM ad_cpt_ope_cptes WHERE type_operation = 542 AND sens = 'c' AND categorie_cpte = 1 AND id_ag = numagc()) THEN
		INSERT INTO ad_cpt_ope_cptes(type_operation, num_cpte, sens, categorie_cpte, id_ag) VALUES (542, NULL, 'c', 1, numagc());

		RAISE NOTICE 'Insertion type_operation 542 sens ''c'' dans la table ad_cpt_ope_cptes effectuée';
		output_result := 2;
	END IF;

	--- Annulation Retrait travelers cheque
	IF NOT EXISTS(SELECT * FROM ad_cpt_ope WHERE type_operation = 543 AND categorie_ope = 1 AND id_ag = numagc()) THEN
		INSERT INTO ad_cpt_ope(type_operation, categorie_ope, id_ag, libel_ope) VALUES (543, 1, numagc(), maketraductionlangsyst('Annulation Retrait travelers cheque'));

		RAISE NOTICE 'Insertion type_operation 543 dans la table ad_cpt_ope effectuée';
		output_result := 2;
	END IF;

	IF NOT EXISTS(SELECT * FROM ad_cpt_ope_cptes WHERE type_operation = 543 AND sens = 'd' AND categorie_cpte = 18 AND id_ag = numagc()) THEN
		INSERT INTO ad_cpt_ope_cptes(type_operation, num_cpte, sens, categorie_cpte, id_ag) VALUES (543, NULL, 'd', 18, numagc());

		RAISE NOTICE 'Insertion type_operation 543 sens ''d'' dans la table ad_cpt_ope_cptes effectuée';
		output_result := 2;
	END IF;

	IF NOT EXISTS(SELECT * FROM ad_cpt_ope_cptes WHERE type_operation = 543 AND sens = 'c' AND categorie_cpte = 1 AND id_ag = numagc()) THEN
		INSERT INTO ad_cpt_ope_cptes(type_operation, num_cpte, sens, categorie_cpte, id_ag) VALUES (543, NULL, 'c', 1, numagc());

		RAISE NOTICE 'Insertion type_operation 543 sens ''c'' dans la table ad_cpt_ope_cptes effectuée';
		output_result := 2;
	END IF;

	--- Annulation Perception des frais de retrait
	IF NOT EXISTS(SELECT * FROM ad_cpt_ope WHERE type_operation = 132 AND categorie_ope = 1 AND id_ag = numagc()) THEN
		INSERT INTO ad_cpt_ope(type_operation, categorie_ope, id_ag, libel_ope) VALUES (132, 1, numagc(), maketraductionlangsyst('Annulation Perception des frais de retrait'));

		RAISE NOTICE 'Insertion type_operation 132 dans la table ad_cpt_ope effectuée';
		output_result := 2;
	END IF;

	IF NOT EXISTS(SELECT * FROM ad_cpt_ope_cptes WHERE type_operation = 132 AND sens = 'd' AND categorie_cpte = 24 AND id_ag = numagc()) THEN
		INSERT INTO ad_cpt_ope_cptes(type_operation, num_cpte, sens, categorie_cpte, id_ag) VALUES (132, NULL, 'd', 24, numagc());

		RAISE NOTICE 'Insertion type_operation 132 sens ''d'' dans la table ad_cpt_ope_cptes effectuée';
		output_result := 2;
	END IF;

	IF NOT EXISTS(SELECT * FROM ad_cpt_ope_cptes WHERE type_operation = 132 AND sens = 'c' AND categorie_cpte = 1 AND id_ag = numagc()) THEN
		INSERT INTO ad_cpt_ope_cptes(type_operation, num_cpte, sens, categorie_cpte, id_ag) VALUES (132, NULL, 'c', 1, numagc());

		RAISE NOTICE 'Insertion type_operation 132 sens ''c'' dans la table ad_cpt_ope_cptes effectuée';
		output_result := 2;
	END IF;

	--- Annulation Frais de transfert
	IF NOT EXISTS(SELECT * FROM ad_cpt_ope WHERE type_operation = 153 AND categorie_ope = 1 AND id_ag = numagc()) THEN
		INSERT INTO ad_cpt_ope(type_operation, categorie_ope, id_ag, libel_ope) VALUES (153, 1, numagc(), maketraductionlangsyst('Annulation Frais de transfert'));

		RAISE NOTICE 'Insertion type_operation 153 dans la table ad_cpt_ope effectuée';
		output_result := 2;
	END IF;

	IF NOT EXISTS(SELECT * FROM ad_cpt_ope_cptes WHERE type_operation = 153 AND sens = 'd' AND categorie_cpte = 24 AND id_ag = numagc()) THEN
		INSERT INTO ad_cpt_ope_cptes(type_operation, num_cpte, sens, categorie_cpte, id_ag) VALUES (153, NULL, 'd', 24, numagc());

		RAISE NOTICE 'Insertion type_operation 153 sens ''d'' dans la table ad_cpt_ope_cptes effectuée';
		output_result := 2;
	END IF;

	IF NOT EXISTS(SELECT * FROM ad_cpt_ope_cptes WHERE type_operation = 153 AND sens = 'c' AND categorie_cpte = 1 AND id_ag = numagc()) THEN
		INSERT INTO ad_cpt_ope_cptes(type_operation, num_cpte, sens, categorie_cpte, id_ag) VALUES (153, NULL, 'c', 1, numagc());

		RAISE NOTICE 'Insertion type_operation 153 sens ''c'' dans la table ad_cpt_ope_cptes effectuée';
		output_result := 2;
	END IF;

	-- Annulation dépôt
	--- Annulation Dépôt espèces
	IF NOT EXISTS(SELECT * FROM ad_cpt_ope WHERE type_operation = 161 AND categorie_ope = 1 AND id_ag = numagc()) THEN
		INSERT INTO ad_cpt_ope(type_operation, categorie_ope, id_ag, libel_ope) VALUES (161, 1, numagc(), maketraductionlangsyst('Annulation Dépôt espèces'));

		RAISE NOTICE 'Insertion type_operation 161 dans la table ad_cpt_ope effectuée';
		output_result := 2;
	END IF;
	
	IF NOT EXISTS(SELECT * FROM ad_cpt_ope_cptes WHERE type_operation = 161 AND sens = 'd' AND categorie_cpte = 1 AND id_ag = numagc()) THEN
		INSERT INTO ad_cpt_ope_cptes(type_operation, num_cpte, sens, categorie_cpte, id_ag) VALUES (161, NULL, 'd', 1, numagc());

		RAISE NOTICE 'Insertion type_operation 161 sens ''d'' dans la table ad_cpt_ope_cptes effectuée';
		output_result := 2;
	END IF;

	IF NOT EXISTS(SELECT * FROM ad_cpt_ope_cptes WHERE type_operation = 161 AND sens = 'c' AND categorie_cpte = 4 AND id_ag = numagc()) THEN
		INSERT INTO ad_cpt_ope_cptes(type_operation, num_cpte, sens, categorie_cpte, id_ag) VALUES (161, NULL, 'c', 4, numagc());

		RAISE NOTICE 'Insertion type_operation 161 sens ''c'' dans la table ad_cpt_ope_cptes effectuée';
		output_result := 2;
	END IF;
	
	--- Annulation Réception chèque externe
	IF NOT EXISTS(SELECT * FROM ad_cpt_ope WHERE type_operation = 544 AND categorie_ope = 1 AND id_ag = numagc()) THEN
		INSERT INTO ad_cpt_ope(type_operation, categorie_ope, id_ag, libel_ope) VALUES (544, 1, numagc(), maketraductionlangsyst('Annulation Réception chèque externe'));

		RAISE NOTICE 'Insertion type_operation 544 dans la table ad_cpt_ope effectuée';
		output_result := 2;
	END IF;
	
	IF NOT EXISTS(SELECT * FROM ad_cpt_ope_cptes WHERE type_operation = 544 AND sens = 'd' AND categorie_cpte = 1 AND id_ag = numagc()) THEN
		INSERT INTO ad_cpt_ope_cptes(type_operation, num_cpte, sens, categorie_cpte, id_ag) VALUES (544, NULL, 'd', 1, numagc());

		RAISE NOTICE 'Insertion type_operation 544 sens ''d'' dans la table ad_cpt_ope_cptes effectuée';
		output_result := 2;
	END IF;

	IF NOT EXISTS(SELECT * FROM ad_cpt_ope_cptes WHERE type_operation = 544 AND sens = 'c' AND categorie_cpte = 5 AND id_ag = numagc()) THEN
		INSERT INTO ad_cpt_ope_cptes(type_operation, num_cpte, sens, categorie_cpte, id_ag) VALUES (544, NULL, 'c', 5, numagc());

		RAISE NOTICE 'Insertion type_operation 544 sens ''c'' dans la table ad_cpt_ope_cptes effectuée';
		output_result := 2;
	END IF;
	
	--- Annulation Virement national
	IF NOT EXISTS(SELECT * FROM ad_cpt_ope WHERE type_operation = 509 AND categorie_ope = 1 AND id_ag = numagc()) THEN
		INSERT INTO ad_cpt_ope(type_operation, categorie_ope, id_ag, libel_ope) VALUES (509, 1, numagc(), maketraductionlangsyst('Annulation Virement national'));

		RAISE NOTICE 'Insertion type_operation 509 dans la table ad_cpt_ope effectuée';
		output_result := 2;
	END IF;
	
	IF NOT EXISTS(SELECT * FROM ad_cpt_ope_cptes WHERE type_operation = 509 AND sens = 'd' AND categorie_cpte = 1 AND id_ag = numagc()) THEN
		INSERT INTO ad_cpt_ope_cptes(type_operation, num_cpte, sens, categorie_cpte, id_ag) VALUES (509, NULL, 'd', 1, numagc());

		RAISE NOTICE 'Insertion type_operation 509 sens ''d'' dans la table ad_cpt_ope_cptes effectuée';
		output_result := 2;
	END IF;

	IF NOT EXISTS(SELECT * FROM ad_cpt_ope_cptes WHERE type_operation = 509 AND sens = 'c' AND categorie_cpte = 5 AND id_ag = numagc()) THEN
		INSERT INTO ad_cpt_ope_cptes(type_operation, num_cpte, sens, categorie_cpte, id_ag) VALUES (509, NULL, 'c', 5, numagc());

		RAISE NOTICE 'Insertion type_operation 509 sens ''c'' dans la table ad_cpt_ope_cptes effectuée';
		output_result := 2;
	END IF;

	--- Annulation Perception frais de crédit direct sauf bonne fin
	IF NOT EXISTS(SELECT * FROM ad_cpt_ope WHERE type_operation = 545 AND categorie_ope = 1 AND id_ag = numagc()) THEN
		INSERT INTO ad_cpt_ope(type_operation, categorie_ope, id_ag, libel_ope) VALUES (545, 1, numagc(), maketraductionlangsyst('Annulation Perception frais de crédit direct sauf bonne fin'));

		RAISE NOTICE 'Insertion type_operation 545 dans la table ad_cpt_ope effectuée';
		output_result := 2;
	END IF;

	IF NOT EXISTS(SELECT * FROM ad_cpt_ope_cptes WHERE type_operation = 545 AND sens = 'd' AND categorie_cpte = 24 AND id_ag = numagc()) THEN
		INSERT INTO ad_cpt_ope_cptes(type_operation, num_cpte, sens, categorie_cpte, id_ag) VALUES (545, NULL, 'd', 24, numagc());

		RAISE NOTICE 'Insertion type_operation 545 sens ''d'' dans la table ad_cpt_ope_cptes effectuée';
		output_result := 2;
	END IF;

	IF NOT EXISTS(SELECT * FROM ad_cpt_ope_cptes WHERE type_operation = 545 AND sens = 'c' AND categorie_cpte = 1 AND id_ag = numagc()) THEN
		INSERT INTO ad_cpt_ope_cptes(type_operation, num_cpte, sens, categorie_cpte, id_ag) VALUES (545, NULL, 'c', 1, numagc());

		RAISE NOTICE 'Insertion type_operation 545 sens ''c'' dans la table ad_cpt_ope_cptes effectuée';
		output_result := 2;
	END IF;

	--- Annulation Mise en attente chèque
	IF NOT EXISTS(SELECT * FROM ad_cpt_ope WHERE type_operation = 546 AND categorie_ope = 1 AND id_ag = numagc()) THEN
		INSERT INTO ad_cpt_ope(type_operation, categorie_ope, id_ag, libel_ope) VALUES (546, 1, numagc(), maketraductionlangsyst('Annulation Mise en attente chèque'));

		RAISE NOTICE 'Insertion type_operation 546 dans la table ad_cpt_ope effectuée';
		output_result := 2;
	END IF;

	IF NOT EXISTS(SELECT * FROM ad_cpt_ope_cptes WHERE type_operation = 546 AND sens = 'd' AND categorie_cpte = 20 AND id_ag = numagc()) THEN
		INSERT INTO ad_cpt_ope_cptes(type_operation, num_cpte, sens, categorie_cpte, id_ag) VALUES (546, NULL, 'd', 20, numagc());

		RAISE NOTICE 'Insertion type_operation 546 sens ''d'' dans la table ad_cpt_ope_cptes effectuée';
		output_result := 2;
	END IF;

	IF NOT EXISTS(SELECT * FROM ad_cpt_ope_cptes WHERE type_operation = 546 AND sens = 'c' AND categorie_cpte = 19 AND id_ag = numagc()) THEN
		INSERT INTO ad_cpt_ope_cptes(type_operation, num_cpte, sens, categorie_cpte, id_ag) VALUES (546, NULL, 'c', 19, numagc());

		RAISE NOTICE 'Insertion type_operation 546 sens ''c'' dans la table ad_cpt_ope_cptes effectuée';
		output_result := 2;
	END IF;

	--- Annulation Frais de virement
	IF NOT EXISTS(SELECT * FROM ad_cpt_ope WHERE type_operation = 154 AND categorie_ope = 1 AND id_ag = numagc()) THEN
		INSERT INTO ad_cpt_ope(type_operation, categorie_ope, id_ag, libel_ope) VALUES (154, 1, numagc(), maketraductionlangsyst('Annulation Frais de virement'));

		RAISE NOTICE 'Insertion type_operation 154 dans la table ad_cpt_ope effectuée';
		output_result := 2;
	END IF;

	IF NOT EXISTS(SELECT * FROM ad_cpt_ope_cptes WHERE type_operation = 154 AND sens = 'd' AND categorie_cpte = 24 AND id_ag = numagc()) THEN
		INSERT INTO ad_cpt_ope_cptes(type_operation, num_cpte, sens, categorie_cpte, id_ag) VALUES (154, NULL, 'd', 24, numagc());

		RAISE NOTICE 'Insertion type_operation 154 sens ''d'' dans la table ad_cpt_ope_cptes effectuée';
		output_result := 2;
	END IF;

	IF NOT EXISTS(SELECT * FROM ad_cpt_ope_cptes WHERE type_operation = 154 AND sens = 'c' AND categorie_cpte = 1 AND id_ag = numagc()) THEN
		INSERT INTO ad_cpt_ope_cptes(type_operation, num_cpte, sens, categorie_cpte, id_ag) VALUES (154, NULL, 'c', 1, numagc());

		RAISE NOTICE 'Insertion type_operation 154 sens ''c'' dans la table ad_cpt_ope_cptes effectuée';
		output_result := 2;
	END IF;
	
	--- Annulation Perception frais de dépôt
	IF NOT EXISTS(SELECT * FROM ad_cpt_ope WHERE type_operation = 155 AND categorie_ope = 1 AND id_ag = numagc()) THEN
		INSERT INTO ad_cpt_ope(type_operation, categorie_ope, id_ag, libel_ope) VALUES (155, 1, numagc(), maketraductionlangsyst('Annulation Perception frais de dépôt'));

		RAISE NOTICE 'Insertion type_operation 155 dans la table ad_cpt_ope effectuée';
		output_result := 2;
	END IF;

	IF NOT EXISTS(SELECT * FROM ad_cpt_ope_cptes WHERE type_operation = 155 AND sens = 'd' AND categorie_cpte = 24 AND id_ag = numagc()) THEN
		INSERT INTO ad_cpt_ope_cptes(type_operation, num_cpte, sens, categorie_cpte, id_ag) VALUES (155, NULL, 'd', 24, numagc());

		RAISE NOTICE 'Insertion type_operation 155 sens ''d'' dans la table ad_cpt_ope_cptes effectuée';
		output_result := 2;
	END IF;

	IF NOT EXISTS(SELECT * FROM ad_cpt_ope_cptes WHERE type_operation = 155 AND sens = 'c' AND categorie_cpte = 4 AND id_ag = numagc()) THEN
		INSERT INTO ad_cpt_ope_cptes(type_operation, num_cpte, sens, categorie_cpte, id_ag) VALUES (155, NULL, 'c', 4, numagc());

		RAISE NOTICE 'Insertion type_operation 155 sens ''c'' dans la table ad_cpt_ope_cptes effectuée';
		output_result := 2;
	END IF;
	
	--- Annulation Retrait des frais de tenue de compte
	IF NOT EXISTS(SELECT * FROM ad_cpt_ope WHERE type_operation = 51 AND categorie_ope = 1 AND id_ag = numagc()) THEN
		INSERT INTO ad_cpt_ope(type_operation, categorie_ope, id_ag, libel_ope) VALUES (51, 1, numagc(), maketraductionlangsyst('Annulation Retrait des frais de tenue de compte'));

		RAISE NOTICE 'Insertion type_operation 51 dans la table ad_cpt_ope effectuée';
		output_result := 2;
	END IF;

	IF NOT EXISTS(SELECT * FROM ad_cpt_ope_cptes WHERE type_operation = 51 AND sens = 'd' AND categorie_cpte = 24 AND id_ag = numagc()) THEN
		INSERT INTO ad_cpt_ope_cptes(type_operation, num_cpte, sens, categorie_cpte, id_ag) VALUES (51, NULL, 'd', 24, numagc());

		RAISE NOTICE 'Insertion type_operation 51 sens ''d'' dans la table ad_cpt_ope_cptes effectuée';
		output_result := 2;
	END IF;

	IF NOT EXISTS(SELECT * FROM ad_cpt_ope_cptes WHERE type_operation = 51 AND sens = 'c' AND categorie_cpte = 4 AND id_ag = numagc()) THEN
		INSERT INTO ad_cpt_ope_cptes(type_operation, num_cpte, sens, categorie_cpte, id_ag) VALUES (51, NULL, 'c', 4, numagc());

		RAISE NOTICE 'Insertion type_operation 51 sens ''c'' dans la table ad_cpt_ope_cptes effectuée';
		output_result := 2;
	END IF;

	------- Gestion des annulation retrait et dépôt --------
	-- MENU
	IF NOT EXISTS(SELECT nom_menu FROM menus WHERE nom_menu='Gae-1') THEN
		INSERT INTO menus (nom_menu, libel_menu, nom_pere, pos_hierarch, ordre, is_menu, fonction, is_cliquable) VALUES ('Gae-1', maketraductionlangsyst('Gestion annulations retrait / dépôt'), 'Gen-10', 5, 17, true, 60, true);
	END IF;
	
	-- ECRAN
	IF NOT EXISTS (SELECT nom_ecran FROM ecrans WHERE nom_ecran='Gae-1') THEN
		INSERT INTO ecrans(nom_ecran, fichier, nom_menu, fonction) VALUES ('Gae-1', 'modules/epargne/menu_gestion_annulation_retrait_depot.php', 'Gae-1', 60);
	END IF;

	------- Demande annulation retrait et dépôt --------
	-- MENUS
	IF NOT EXISTS(SELECT nom_menu FROM menus WHERE nom_menu='Dae') THEN
		INSERT INTO menus (nom_menu, libel_menu, nom_pere, pos_hierarch, ordre, is_menu,fonction, is_cliquable) VALUES ('Dae', maketraductionlangsyst('Demande annulation'), 'Gae-1', 6, 1, true, 61, true);
	END IF;

	-- SUB MENUS
	IF NOT EXISTS(SELECT nom_menu FROM menus WHERE nom_menu='Dae-1') THEN
		INSERT INTO menus (nom_menu, libel_menu, nom_pere, pos_hierarch, ordre, is_menu,fonction, is_cliquable) VALUES ('Dae-1', maketraductionlangsyst('Liste des opérations'), 'Dae', 7, 1, false, 61, false);
	END IF;

	IF NOT EXISTS(SELECT nom_menu FROM menus WHERE nom_menu='Dae-2') THEN
		INSERT INTO menus (nom_menu, libel_menu, nom_pere, pos_hierarch, ordre, is_menu,fonction, is_cliquable) VALUES ('Dae-2', maketraductionlangsyst('Confirmation demande'), 'Dae', 7, 2, false, 61, false);
	END IF;

	-- ECRANS
	IF NOT EXISTS (SELECT nom_ecran FROM ecrans WHERE nom_ecran='Dae-1') THEN
		INSERT INTO ecrans(nom_ecran, fichier, nom_menu, fonction) VALUES ('Dae-1', 'modules/epargne/demande_annulation_retrait_depot.php', 'Dae-1', 61);
	END IF;

	IF NOT EXISTS (SELECT nom_ecran FROM ecrans WHERE nom_ecran='Dae-2') THEN
		INSERT INTO ecrans(nom_ecran, fichier, nom_menu, fonction) VALUES ('Dae-2', 'modules/epargne/demande_annulation_retrait_depot.php', 'Dae-2', 61);
	END IF;

	------- Approbation demande annulation retrait et dépôt --------
	-- MENUS
	IF NOT EXISTS(SELECT nom_menu FROM menus WHERE nom_menu='Aae') THEN
		INSERT INTO menus (nom_menu, libel_menu, nom_pere, pos_hierarch, ordre, is_menu,fonction, is_cliquable) VALUES ('Aae', maketraductionlangsyst('Approbation demande'), 'Gae-1', 6, 2, true, 62, true);
	END IF;

	-- SUB MENUS
	IF NOT EXISTS(SELECT nom_menu FROM menus WHERE nom_menu='Aae-1') THEN
		INSERT INTO menus (nom_menu, libel_menu, nom_pere, pos_hierarch, ordre, is_menu,fonction, is_cliquable) VALUES ('Aae-1', maketraductionlangsyst('Liste demandes annulation'), 'Aae', 7, 1, false, 62, false);
	END IF;

	IF NOT EXISTS(SELECT nom_menu FROM menus WHERE nom_menu='Aae-2') THEN
		INSERT INTO menus (nom_menu, libel_menu, nom_pere, pos_hierarch, ordre, is_menu,fonction, is_cliquable) VALUES ('Aae-2', maketraductionlangsyst('Confirmation approbation'), 'Aae', 7, 2, false, 62, false);
	END IF;

	-- ECRANS
	IF NOT EXISTS (SELECT nom_ecran FROM ecrans WHERE nom_ecran='Aae-1') THEN
		INSERT INTO ecrans(nom_ecran, fichier, nom_menu, fonction) VALUES ('Aae-1', 'modules/epargne/approbation_annulation_retrait_depot.php', 'Aae-1', 62);
	END IF;

	IF NOT EXISTS (SELECT nom_ecran FROM ecrans WHERE nom_ecran='Aae-2') THEN
		INSERT INTO ecrans(nom_ecran, fichier, nom_menu, fonction) VALUES ('Aae-2', 'modules/epargne/approbation_annulation_retrait_depot.php', 'Aae-2', 62);
	END IF;

	------- Effectuer annulation retrait et dépôt --------
	-- MENUS
	IF NOT EXISTS(SELECT nom_menu FROM menus WHERE nom_menu='Eae') THEN
		INSERT INTO menus (nom_menu, libel_menu, nom_pere, pos_hierarch, ordre, is_menu,fonction, is_cliquable) VALUES ('Eae', maketraductionlangsyst('Effectuer annulation'), 'Gae-1', 6, 3, true, 63, true);
	END IF;

	-- SUB MENUS
	IF NOT EXISTS(SELECT nom_menu FROM menus WHERE nom_menu='Eae-1') THEN
		INSERT INTO menus (nom_menu, libel_menu, nom_pere, pos_hierarch, ordre, is_menu,fonction, is_cliquable) VALUES ('Eae-1', maketraductionlangsyst('Liste demandes autorisées'), 'Eae', 7, 1, false, 63, false);
	END IF;

	IF NOT EXISTS(SELECT nom_menu FROM menus WHERE nom_menu='Eae-2') THEN
		INSERT INTO menus (nom_menu, libel_menu, nom_pere, pos_hierarch, ordre, is_menu,fonction, is_cliquable) VALUES ('Eae-2', maketraductionlangsyst('Confirmation annulation'), 'Eae', 7, 2, false, 63, false);
	END IF;

	-- ECRANS
	IF NOT EXISTS (SELECT nom_ecran FROM ecrans WHERE nom_ecran='Eae-1') THEN
		INSERT INTO ecrans(nom_ecran, fichier, nom_menu, fonction) VALUES ('Eae-1', 'modules/epargne/effectuer_annulation_retrait_depot.php', 'Eae-1', 63);
	END IF;

	IF NOT EXISTS (SELECT nom_ecran FROM ecrans WHERE nom_ecran='Eae-2') THEN
		INSERT INTO ecrans(nom_ecran, fichier, nom_menu, fonction) VALUES ('Eae-2', 'modules/epargne/effectuer_annulation_retrait_depot.php', 'Eae-2', 63);
	END IF;

	RETURN output_result;

END;
$$
LANGUAGE plpgsql;

SELECT init_projet_annulation_retrait_depot();
DROP FUNCTION init_projet_annulation_retrait_depot();

-----------------------------------------  FIN : Ticket #477 - PROJET ANNULATION RETRAIT ET DÉPÔT  -----------------------------------------------------------

