CREATE OR REPLACE FUNCTION evo_atm() RETURNS INT AS
$$
DECLARE
output_result INTEGER = 1;

BEGIN

	RAISE NOTICE 'START';

	IF NOT EXISTS(SELECT * FROM information_schema.tables WHERE table_name = 'adsys_prestataire') THEN
		CREATE TABLE adsys_prestataire
		(
		  id_prestataire serial NOT NULL,
		  id_ag integer NOT NULL,
		  code_prestataire character varying(100) NOT NULL,
		  nom_prestataire character varying(100) NOT NULL,
		  CONSTRAINT adsys_prestataire_pkey PRIMARY KEY (id_prestataire, id_ag)
		)
		WITH (
		  OIDS=FALSE
		);

		RAISE NOTICE 'Table adsys_prestataire created';
		output_result := 2;
	END IF;
	
	IF NOT EXISTS(SELECT * FROM adsys_prestataire WHERE code_prestataire='RSWITCH_RW') THEN
		INSERT INTO adsys_prestataire (id_ag, code_prestataire, nom_prestataire) VALUES (numagc(), 'RSWITCH_RW', 'RSWITCH');
		
		RAISE NOTICE 'Insertion code_prestataire RSWITCH_RW dans la table adsys_prestataire effectuée';
		output_result := 2;
	END IF;
	
	IF NOT EXISTS(SELECT * FROM information_schema.tables WHERE table_name = 'ad_gest_carte') THEN
		CREATE TABLE ad_gest_carte
		(
		  id serial NOT NULL,
		  id_client integer NOT NULL,
		  id_cpte integer NOT NULL,
		  request_ref_num character varying(100) UNIQUE NOT NULL,
		  branch_code character varying(10) NOT NULL,
		  first_name character varying(50) NOT NULL,
		  middle_name character varying(50),
		  last_name character varying(50) NOT NULL,
		  date_cmde date NOT NULL,
		  date_envoi_impr date,
		  date_livree date,
		  id_ag integer NOT NULL,
		  id_prestataire integer NOT NULL,
		  nom_carte character varying(100) NOT NULL,
		  titre integer NOT NULL,
		  num_identite_passeport character varying(20) NOT NULL,
		  type_client integer NOT NULL,
		  resident integer NOT NULL,
		  reason_for_issue integer NOT NULL,
		  type_compte integer NOT NULL,
		  devise character(3) NOT NULL,
		  priorite integer NOT NULL,	
		  frais numeric(30,6) NOT NULL DEFAULT 0,
		  etat integer,
		  guichet character varying(100) NOT NULL,
		  CONSTRAINT ad_geste_carte_pkey PRIMARY KEY (id),
		  CONSTRAINT ad_geste_carte_id_cpte_fkey FOREIGN KEY (id_cpte, id_ag)
			  REFERENCES ad_cpt (id_cpte, id_ag) MATCH SIMPLE
			  ON UPDATE NO ACTION ON DELETE NO ACTION,
		  CONSTRAINT ad_geste_carte_titre_check CHECK (titre = ANY (ARRAY[0, 1, 2, 3, 4, 5, 6, 7])),
		  CONSTRAINT ad_geste_carte_resident_check CHECK (resident = ANY (ARRAY[0, 1])),
		  CONSTRAINT ad_geste_carte_type_client_check CHECK (type_client = ANY (ARRAY[0, 1])),
		  CONSTRAINT ad_geste_carte_type_compte_check CHECK (type_compte = ANY (ARRAY[0, 1, 2, 3, 4, 5])),
		  CONSTRAINT ad_geste_carte_priorite_check CHECK (priorite = ANY (ARRAY[1, 2, 3])),
		  CONSTRAINT ad_geste_carte_etat_check CHECK (etat = ANY (ARRAY[1, 2, 3]))
		)
		WITH (
		  OIDS=FALSE
		);

		RAISE NOTICE 'Table adsys_prestataire created';
		output_result := 2;
	END IF;
	
	IF NOT EXISTS(SELECT * FROM information_schema.tables WHERE table_name = 'adsys_prestataire') THEN
		CREATE TABLE adsys_prestataire
		(
		  id_prestataire serial NOT NULL,
		  id_ag integer NOT NULL,
		  code_prestataire character varying(100) NOT NULL,
		  nom_prestataire character varying(100) NOT NULL,
		  CONSTRAINT ad_prestataire_pkey PRIMARY KEY (id_prestataire, id_ag)
		)
		WITH (
		  OIDS=FALSE
		);

		RAISE NOTICE 'Table adsys_prestataire created';
		output_result := 2;
	END IF;
	
	-- ----------------------------
	-- Records of adsys_tarification
	-- ----------------------------
	IF NOT EXISTS(SELECT * FROM adsys_tarification WHERE type_de_frais='ATM_REG') THEN
		INSERT INTO adsys_tarification (id_tarification, code_abonnement, type_de_frais, mode_frais, valeur, compte_comptable, date_debut_validite, date_fin_validite, statut, id_ag) VALUES (14, 'atm', 'ATM_REG', '1', '0', null, null, null, 'f', numagc());
		
		RAISE NOTICE 'Insertion type_de_frais 14 dans la table adsys_tarification effectuée';
		output_result := 2;
	END IF;
	
	IF NOT EXISTS(SELECT * FROM adsys_tarification WHERE type_de_frais='ATM_MTH') THEN
		INSERT INTO adsys_tarification (id_tarification, code_abonnement, type_de_frais, mode_frais, valeur, compte_comptable, date_debut_validite, date_fin_validite, statut, id_ag) VALUES (15, 'atm', 'ATM_MTH', '1', '0', null, null, null, 'f', numagc());
		
		RAISE NOTICE 'Insertion type_de_frais 15 dans la table adsys_tarification effectuée';
		output_result := 2;
	END IF;
	
	IF NOT EXISTS(SELECT * FROM adsys_tarification WHERE type_de_frais='ATM_USG') THEN
		INSERT INTO adsys_tarification (id_tarification, code_abonnement, type_de_frais, mode_frais, valeur, compte_comptable, date_debut_validite, date_fin_validite, statut, id_ag) VALUES (16, 'atm', 'ATM_USG', '1', '0', null, null, null, 'f', numagc());
		
		RAISE NOTICE 'Insertion type_de_frais 16 dans la table adsys_tarification effectuée';
		output_result := 2;
	END IF;
	
	
	-- Création opération financière
	IF NOT EXISTS(SELECT * FROM ad_cpt_ope WHERE type_operation=190 AND categorie_ope = 1 AND id_ag = numagc()) THEN
		-- Frais d'activation du service ATM
		INSERT INTO ad_cpt_ope(type_operation, categorie_ope, id_ag, libel_ope) 
		VALUES (190, 1, numagc(), maketraductionlangsyst('Frais d''activation du service ATM'));
	
		RAISE NOTICE 'Insertion type_operation 190 dans la table ad_cpt_ope effectuée';
		output_result := 2;
	END IF;
	
	IF NOT EXISTS(SELECT * FROM ad_cpt_ope_cptes WHERE type_operation=190 AND sens = 'd' AND categorie_cpte = 1 AND id_ag = numagc()) THEN
		INSERT INTO ad_cpt_ope_cptes(type_operation, num_cpte, sens, categorie_cpte, id_ag) VALUES (190, NULL, 'd', 1, numagc());
	
		RAISE NOTICE 'Insertion type_operation 190 sens ''d'' dans la table ad_cpt_ope_cptes effectuée';
		output_result := 2;
	END IF;

	IF NOT EXISTS(SELECT * FROM ad_cpt_ope_cptes WHERE type_operation=190 AND sens = 'c' AND categorie_cpte = 0 AND id_ag = numagc()) THEN
		INSERT INTO ad_cpt_ope_cptes(type_operation, num_cpte, sens, categorie_cpte, id_ag) VALUES (190, NULL, 'c', 0, numagc());
	
		RAISE NOTICE 'Insertion type_operation 190 sens ''c'' dans la table ad_cpt_ope_cptes effectuée';
		output_result := 2;
	END IF;
	
	IF NOT EXISTS(SELECT * FROM ad_cpt_ope WHERE type_operation=191 AND categorie_ope = 1 AND id_ag = numagc()) THEN
		-- Frais forfaitaires mensuels ATM
		INSERT INTO ad_cpt_ope(type_operation, categorie_ope, id_ag, libel_ope) 
		VALUES (191, 1, numagc(), maketraductionlangsyst('Frais forfaitaires mensuels ATM'));
	
		RAISE NOTICE 'Insertion type_operation 191 dans la table ad_cpt_ope effectuée';
		output_result := 2;
	END IF;

	IF NOT EXISTS(SELECT * FROM ad_cpt_ope_cptes WHERE type_operation=191 AND sens = 'd' AND categorie_cpte = 1 AND id_ag = numagc()) THEN
		INSERT INTO ad_cpt_ope_cptes(type_operation, num_cpte, sens, categorie_cpte, id_ag) VALUES (191, NULL, 'd', 1, numagc());
	
		RAISE NOTICE 'Insertion type_operation 191 sens ''d'' dans la table ad_cpt_ope_cptes effectuée';
		output_result := 2;
	END IF;

	IF NOT EXISTS(SELECT * FROM ad_cpt_ope_cptes WHERE type_operation=191 AND sens = 'c' AND categorie_cpte = 0 AND id_ag = numagc()) THEN
		INSERT INTO ad_cpt_ope_cptes(type_operation, num_cpte, sens, categorie_cpte, id_ag) VALUES (191, NULL, 'c', 0, numagc());
	
		RAISE NOTICE 'Insertion type_operation 191 sens ''c'' dans la table ad_cpt_ope_cptes effectuée';
		output_result := 2;
	END IF;
	
	IF NOT EXISTS(SELECT * FROM ad_cpt_ope WHERE type_operation=192 AND categorie_ope = 1 AND id_ag = numagc()) THEN
		-- Frais a l'usage ATM
		INSERT INTO ad_cpt_ope(type_operation, categorie_ope, id_ag, libel_ope) 
		VALUES (192, 1, numagc(), maketraductionlangsyst('Frais à l''usage ATM'));
	
		RAISE NOTICE 'Insertion type_operation 192 dans la table ad_cpt_ope effectuée';
		output_result := 2;
	END IF;

	IF NOT EXISTS(SELECT * FROM ad_cpt_ope_cptes WHERE type_operation=192 AND sens = 'd' AND categorie_cpte = 1 AND id_ag = numagc()) THEN
		INSERT INTO ad_cpt_ope_cptes(type_operation, num_cpte, sens, categorie_cpte, id_ag) VALUES (192, NULL, 'd', 1, numagc());
	
		RAISE NOTICE 'Insertion type_operation 192 sens ''d'' dans la table ad_cpt_ope_cptes effectuée';
		output_result := 2;
	END IF;

	IF NOT EXISTS(SELECT * FROM ad_cpt_ope_cptes WHERE type_operation=192 AND sens = 'c' AND categorie_cpte = 0 AND id_ag = numagc()) THEN
		INSERT INTO ad_cpt_ope_cptes(type_operation, num_cpte, sens, categorie_cpte, id_ag) VALUES (192, NULL, 'c', 0, numagc());
	
		RAISE NOTICE 'Insertion type_operation 192 sens ''c'' dans la table ad_cpt_ope_cptes effectuée';
		output_result := 2;
	END IF;
	
	
	--Creer Abn-7 qui remplacera Abn-1
	IF NOT EXISTS (SELECT * FROM menus WHERE nom_menu = 'Abn-7') THEN
		--MENU INTERMEDIAIRE: Liste des abonnements
		INSERT INTO menus (nom_menu, libel_menu, nom_pere, pos_hierarch, ordre, is_menu,fonction, is_cliquable) 
		VALUES ('Abn-7', maketraductionlangsyst('Choix des abonnements'), 'Abn-1', 7, 1, false, 12, false);
		
		--Remplacer Abn-1 par Abn-7
		UPDATE ecrans SET nom_ecran = 'Abn-7', nom_menu = 'Abn-7' where nom_ecran = 'Abn-1'; 
		
		--Faire pointer les page des "Gestion des abonnements" dans "Liste des abonnements" (les menu ci-dessous doivent se trouver a l'interieur de Gestion des abonnements)
		UPDATE menus SET nom_pere = 'Abn-1', pos_hierarch = 7 where nom_menu = 'Abn-2';
		UPDATE menus SET nom_pere = 'Abn-1', pos_hierarch = 7 where nom_menu = 'Abn-3';
		UPDATE menus SET nom_pere = 'Abn-1', pos_hierarch = 7 where nom_menu = 'Abn-4';
		UPDATE menus SET nom_pere = 'Abn-1', pos_hierarch = 7 where nom_menu = 'Abn-5';
		UPDATE menus SET nom_pere = 'Abn-1', pos_hierarch = 7 where nom_menu = 'Abn-6';
		
		RAISE NOTICE 'Insertion nom_ecran Abn-7 dans la table menus + MAJ ecrans effectuée';
		output_result := 2;
	END IF;
	
	IF NOT EXISTS (SELECT * FROM ecrans WHERE nom_ecran = 'Abn-1') THEN
		INSERT INTO ecrans(nom_ecran, fichier, nom_menu, fonction) VALUES ('Abn-1', 'modules/clients/menu_gestion_abonnements.php', 'Abn', 12); 
		RAISE NOTICE 'Insertion nom_menu Abn-1 dans la table ecrans effectuée';
		output_result := 2;
	END IF;

	
	
	--Lien pour le nouvel ecran: Commande de cartes - 
	IF NOT EXISTS (SELECT * FROM menus WHERE nom_menu = 'Crt-1') THEN
		--MENU INTERMEDIAIRE: Commande de cartes
		INSERT INTO menus (nom_menu, libel_menu, nom_pere, pos_hierarch, ordre, is_menu,fonction, is_cliquable) 
		VALUES ('Crt-1', maketraductionlangsyst('Commande de cartes'), 'Abn', 6, 1, false, 12, false);		
		RAISE NOTICE 'Insertion nom_ecran Crt-1 dans la table menus effectuée';
		output_result := 2;
	END IF;

	
	-- Menus: Selection compte / prestataire
	IF NOT EXISTS (SELECT * FROM menus WHERE nom_menu = 'Crt-2') THEN
		--MENU INTERMEDIAIRE: Commande de cartes
		INSERT INTO menus (nom_menu, libel_menu, nom_pere, pos_hierarch, ordre, is_menu,fonction, is_cliquable) 
		VALUES ('Crt-2', maketraductionlangsyst('Choix du compte'), 'Crt-1', 7, 1, false, 12, false);		
		RAISE NOTICE 'Insertion nom_ecran Crt-2 dans la table menus effectuée';
		output_result := 2;
	END IF;
	
	-- Menus: Formulaire demande carte
	IF NOT EXISTS (SELECT * FROM menus WHERE nom_menu = 'Crt-3') THEN
		--MENU INTERMEDIAIRE: Commande de cartes
		INSERT INTO menus (nom_menu, libel_menu, nom_pere, pos_hierarch, ordre, is_menu,fonction, is_cliquable) 
		VALUES ('Crt-3', maketraductionlangsyst('Nouvelle carte'), 'Crt-1', 7, 3, false, 12, false);		
		RAISE NOTICE 'Insertion nom_ecran Crt-3 dans la table menus effectuée';
		output_result := 2;
	END IF;
	
	-- Menus: Confirmation
	IF NOT EXISTS (SELECT * FROM menus WHERE nom_menu = 'Crt-4') THEN
		--MENU INTERMEDIAIRE: Commande de cartes
		INSERT INTO menus (nom_menu, libel_menu, nom_pere, pos_hierarch, ordre, is_menu,fonction, is_cliquable) 
		VALUES ('Crt-4', maketraductionlangsyst('Confirmation'), 'Crt-1', 7, 4, false, 12, false);		
		RAISE NOTICE 'Insertion nom_ecran Abn-11 dans la table menus effectuée';
		output_result := 2;
	END IF;
	
	
	-- Ecrans: Selection compte / prestataire
	IF NOT EXISTS (SELECT * FROM ecrans WHERE nom_ecran = 'Crt-1') THEN
		INSERT INTO ecrans(nom_ecran, fichier, nom_menu, fonction) VALUES ('Crt-1', 'modules/clients/cartes.php', 'Crt-1', 12); 
		output_result := 2;
	END IF;
	
	-- Ecrans: Formulaire demande carte
	IF NOT EXISTS (SELECT * FROM ecrans WHERE nom_ecran = 'Crt-2') THEN
		INSERT INTO ecrans(nom_ecran, fichier, nom_menu, fonction) VALUES ('Crt-2', 'modules/clients/cartes.php', 'Crt-2', 12); 
		output_result := 2;
	END IF;
	
	-- Ecrans: Confirmation demande
	IF NOT EXISTS (SELECT * FROM ecrans WHERE nom_ecran = 'Crt-3') THEN
		INSERT INTO ecrans(nom_ecran, fichier, nom_menu, fonction) VALUES ('Crt-3', 'modules/clients/cartes.php', 'Crt-3', 12); 
		output_result := 2;
	END IF;
	
	-- Confirmation opération
	IF NOT EXISTS (SELECT * FROM ecrans WHERE nom_ecran = 'Crt-4') THEN
		INSERT INTO ecrans(nom_ecran, fichier, nom_menu, fonction) VALUES ('Crt-4', 'modules/clients/cartes.php', 'Crt-4', 12); 
		output_result := 2;
	END IF;

	
	-- Menus: Rapport gestion cartes
	IF NOT EXISTS (SELECT * FROM menus WHERE nom_menu = 'Rgc') THEN
		--MENU INTERMEDIAIRE: Commande de cartes
		INSERT INTO menus (nom_menu, libel_menu, nom_pere, pos_hierarch, ordre, is_menu,fonction, is_cliquable) 
		VALUES ('Rgc', maketraductionlangsyst('Rapport gestion cartes'), 'Gen-6', 3, 16, true, 220, true);		
		RAISE NOTICE 'Insertion nom_menu Rgc dans la table menus effectuée - A activer le menu dans Gestion des clients';
		output_result := 2;
	END IF;  
	
	-- Menus: Rapport gestion cartes - ecran
	IF NOT EXISTS (SELECT * FROM menus WHERE nom_menu = 'Rgc-1') THEN
		--MENU INTERMEDIAIRE: Commande de cartes
		INSERT INTO menus (nom_menu, libel_menu, nom_pere, pos_hierarch, ordre, is_menu, is_cliquable) 
		VALUES ('Rgc-1', maketraductionlangsyst('Rapport gestion cartes'), 'Rgc', 3, 1, false, false);		
		RAISE NOTICE 'Insertion nom_menu Rgc-1 dans la table menus effectuée';
		output_result := 2;
	END IF;  
	  
	-- Ecrans: Rapport gestion cartes
	IF NOT EXISTS (SELECT * FROM ecrans WHERE nom_ecran = 'Rgc-1') THEN
		INSERT INTO ecrans(nom_ecran, fichier, nom_menu, fonction) VALUES ('Rgc-1', 'modules/guichet/gestion_cartes.php', 'Rgc', 220); 
		RAISE NOTICE 'Insertion nom_ecran Rgc-1 dans la table ecrans effectuée';
		output_result := 2;
	END IF;  
	
	-- Menus: Confirmation
	IF NOT EXISTS (SELECT * FROM menus WHERE nom_menu = 'Rgc-2') THEN
		--MENU INTERMEDIAIRE: Commande de cartes
		INSERT INTO menus (nom_menu, libel_menu, nom_pere, pos_hierarch, ordre, fonction, is_menu, is_cliquable) 
		VALUES ('Rgc-2', maketraductionlangsyst('Confirmation'), 'Rgc-1', 3, 2, 220, false, false);		
		RAISE NOTICE 'Insertion nom_ecran Abn-11 dans la table menus effectuée';
		output_result := 2;
	END IF;
	
	-- Confirmation opération
	IF NOT EXISTS (SELECT * FROM ecrans WHERE nom_ecran = 'Rgc-2') THEN
		INSERT INTO ecrans(nom_ecran, fichier, nom_menu, fonction) VALUES ('Rgc-2', 'modules/guichet/gestion_cartes.php', 'Rgc-2', 220); 
		output_result := 2;
	END IF;
	
	RETURN output_result;

	
	
	
END;
$$
LANGUAGE plpgsql;

SELECT evo_atm();
DROP FUNCTION evo_atm();	