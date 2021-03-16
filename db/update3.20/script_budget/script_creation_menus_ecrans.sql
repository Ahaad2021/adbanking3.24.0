-- Function: creation_menus_ecrans_budget()

-- DROP FUNCTION creation_menus_ecrans_budget();

CREATE OR REPLACE FUNCTION creation_menus_ecrans_budget()
  RETURNS integer AS
$BODY$
DECLARE

  output_result integer = 0;
  id_str_trad integer = 0;
  pos_ordre integer = 0;

BEGIN
	--========> Update Menu 'Out' position up
	IF EXISTS (select * from menus where nom_menu = 'Out') THEN
	 SELECT INTO pos_ordre ordre FROM menus WHERE nom_menu = 'Out';
	 UPDATE menus SET ordre = (pos_ordre+1) WHERE nom_menu = 'Out';
	END IF;

	--========> Debut Gestion du Budget
	--Menu
	IF NOT EXISTS (select * from menus where nom_menu = 'Gen-15') THEN
	 id_str_trad := maketraductionlangsyst('Budget');
	 INSERT INTO menus (nom_menu, libel_menu, nom_pere, pos_hierarch, ordre, is_menu,fonction, is_cliquable)
	 VALUES ('Gen-15', id_str_trad, 'Gen-3', 2, pos_ordre, TRUE, 700, TRUE);
	 IF EXISTS(SELECT langue FROM adsys_langues_systeme WHERE code = 'en_GB') THEN
	  INSERT INTO ad_traductions (id_str, langue, traduction) VALUES (id_str_trad, 'en_GB', 'Budget');
	 END IF;
	END IF;
	--Ecran
	IF NOT EXISTS (select * from ecrans where nom_ecran = 'Gen-15') THEN
	 INSERT INTO ecrans(nom_ecran, fichier, nom_menu, fonction)
	 VALUES ('Gen-15', 'modules/menus/menu.php', 'Gen-15', 700);
	END IF;
	--========> Fin Gestion du Budget

	--========> Debut Gestion des tables de correspondance
	--Main Menu
	IF NOT EXISTS (select * from menus where nom_menu = 'Gtc') THEN
	 id_str_trad := maketraductionlangsyst('Gestion des tables de correspondance');
	 INSERT INTO menus (nom_menu, libel_menu, nom_pere, pos_hierarch, ordre, is_menu,fonction, is_cliquable)
	 VALUES ('Gtc', id_str_trad, 'Gen-15', 3, 1, TRUE, 701, FALSE);
	 IF EXISTS(SELECT langue FROM adsys_langues_systeme WHERE code = 'en_GB') THEN
	  INSERT INTO ad_traductions (id_str, langue, traduction) VALUES (id_str_trad, 'en_GB', 'Mapping table management');
	 END IF;
	END IF;
	--Saisie Exercice/Type de Budget
	----Menu
	IF NOT EXISTS (select * from menus where nom_menu = 'Gtc-1') THEN
	 id_str_trad := maketraductionlangsyst('Saisie Type de Budget');
	 INSERT INTO menus (nom_menu, libel_menu, nom_pere, pos_hierarch, ordre, is_menu,fonction, is_cliquable)
	 VALUES ('Gtc-1', id_str_trad, 'Gtc', 4, 1, FALSE, 701, TRUE);
	 IF EXISTS(SELECT langue FROM adsys_langues_systeme WHERE code = 'en_GB') THEN
	  INSERT INTO ad_traductions (id_str, langue, traduction) VALUES (id_str_trad, 'en_GB', 'Select Type of Budget');
	 END IF;
	END IF;
	----Ecran
	IF NOT EXISTS (select * from ecrans where nom_ecran = 'Gtc-1') THEN
	 INSERT INTO ecrans(nom_ecran, fichier, nom_menu, fonction)
	 VALUES ('Gtc-1', 'modules/budget/gestion_tables_correspondance.php', 'Gtc-1', 701);
	END IF;
	--Parametrer/Modifier Tables de correspondance
	----Menu
	IF NOT EXISTS (select * from menus where nom_menu = 'Gtc-2') THEN
	 id_str_trad := maketraductionlangsyst('Table de correspondances');
	 INSERT INTO menus (nom_menu, libel_menu, nom_pere, pos_hierarch, ordre, is_menu,fonction, is_cliquable)
	 VALUES ('Gtc-2', id_str_trad, 'Gtc', 4, 2, FALSE, 701, FALSE);
	 IF EXISTS(SELECT langue FROM adsys_langues_systeme WHERE code = 'en_GB') THEN
	  INSERT INTO ad_traductions (id_str, langue, traduction) VALUES (id_str_trad, 'en_GB', 'Mapping Table');
	 END IF;
	END IF;
	----Ecran
	IF NOT EXISTS (select * from ecrans where nom_ecran = 'Gtc-2') THEN
	 INSERT INTO ecrans(nom_ecran, fichier, nom_menu, fonction)
	 VALUES ('Gtc-2', 'modules/budget/gestion_tables_correspondance.php', 'Gtc-2', 701);
	END IF;
	--Tables de correspondance : Ajout d'un Poste Principale
	----Menu
	IF NOT EXISTS (select * from menus where nom_menu = 'Gtc-3') THEN
	 id_str_trad := maketraductionlangsyst('Ajout Poste Principal');
	 INSERT INTO menus (nom_menu, libel_menu, nom_pere, pos_hierarch, ordre, is_menu,fonction, is_cliquable)
	 VALUES ('Gtc-3', id_str_trad, 'Gtc', 4, 3, FALSE, 702, FALSE);
	 IF EXISTS(SELECT langue FROM adsys_langues_systeme WHERE code = 'en_GB') THEN
	  INSERT INTO ad_traductions (id_str, langue, traduction) VALUES (id_str_trad, 'en_GB', 'Add Main Item');
	 END IF;
	END IF;
	----Ecran
	IF NOT EXISTS (select * from ecrans where nom_ecran = 'Gtc-3') THEN
	 INSERT INTO ecrans(nom_ecran, fichier, nom_menu, fonction)
	 VALUES ('Gtc-3', 'modules/budget/gestion_tables_correspondance.php', 'Gtc-3', 702);
	END IF;
	--Tables de correspondance : Ajout d’un Sous Poste
	----Menu
	IF NOT EXISTS (select * from menus where nom_menu = 'Gtc-4') THEN
	 id_str_trad := maketraductionlangsyst('Ajout Sous Poste');
	 INSERT INTO menus (nom_menu, libel_menu, nom_pere, pos_hierarch, ordre, is_menu,fonction, is_cliquable)
	 VALUES ('Gtc-4', id_str_trad, 'Gtc', 4, 4, FALSE, 702, FALSE);
	 IF EXISTS(SELECT langue FROM adsys_langues_systeme WHERE code = 'en_GB') THEN
	  INSERT INTO ad_traductions (id_str, langue, traduction) VALUES (id_str_trad, 'en_GB', 'Add Sub Item Level');
	 END IF;
	END IF;
	----Ecran
	IF NOT EXISTS (select * from ecrans where nom_ecran = 'Gtc-4') THEN
	 INSERT INTO ecrans(nom_ecran, fichier, nom_menu, fonction)
	 VALUES ('Gtc-4', 'modules/budget/gestion_tables_correspondance.php', 'Gtc-4', 702);
	END IF;
	--Tables de correspondance : Modifier/Supprimer un Poste
	----Menu
	IF NOT EXISTS (select * from menus where nom_menu = 'Gtc-5') THEN
	 id_str_trad := maketraductionlangsyst('Modifier/Supprimer un Poste');
	 INSERT INTO menus (nom_menu, libel_menu, nom_pere, pos_hierarch, ordre, is_menu,fonction, is_cliquable)
	 VALUES ('Gtc-5', id_str_trad, 'Gtc', 4, 5, FALSE, 703, FALSE);
	 IF EXISTS(SELECT langue FROM adsys_langues_systeme WHERE code = 'en_GB') THEN
	  INSERT INTO ad_traductions (id_str, langue, traduction) VALUES (id_str_trad, 'en_GB', 'Modify/Delete an Item');
	 END IF;
	END IF;
	----Ecran
	IF NOT EXISTS (select * from ecrans where nom_ecran = 'Gtc-5') THEN
	 INSERT INTO ecrans(nom_ecran, fichier, nom_menu, fonction)
	 VALUES ('Gtc-5', 'modules/budget/gestion_tables_correspondance.php', 'Gtc-5', 703);
	END IF;
	--Tables de correspondance : Confirmation Gestion
	----Menu
	IF NOT EXISTS (select * from menus where nom_menu = 'Gtc-6') THEN
	 id_str_trad := maketraductionlangsyst('Confirmation table de correspondances');
	 INSERT INTO menus (nom_menu, libel_menu, nom_pere, pos_hierarch, ordre, is_menu,fonction, is_cliquable)
	 VALUES ('Gtc-6', id_str_trad, 'Gtc', 4, 6, FALSE, 701, FALSE);
	 IF EXISTS(SELECT langue FROM adsys_langues_systeme WHERE code = 'en_GB') THEN
	  INSERT INTO ad_traductions (id_str, langue, traduction) VALUES (id_str_trad, 'en_GB', 'Confirmation of mapping table');
	 END IF;
	END IF;
	----Ecran
	IF NOT EXISTS (select * from ecrans where nom_ecran = 'Gtc-6') THEN
	 INSERT INTO ecrans(nom_ecran, fichier, nom_menu, fonction)
	 VALUES ('Gtc-6', 'modules/budget/gestion_tables_correspondance.php', 'Gtc-6', 701);
	END IF;
	--========> Fin Gestion des tables de correspondance

	--========> Debut Gestion Budget : Mise en Place, Raffinement et Revision
	--Mise en Place
	----Main Menu
	IF NOT EXISTS (select * from menus where nom_menu = 'Meb') THEN
	 id_str_trad := maketraductionlangsyst('Mise en Place du Budget');
	 INSERT INTO menus (nom_menu, libel_menu, nom_pere, pos_hierarch, ordre, is_menu,fonction, is_cliquable)
	 VALUES ('Meb', id_str_trad, 'Gen-15', 3, 2, TRUE, 705, FALSE);
	 IF EXISTS(SELECT langue FROM adsys_langues_systeme WHERE code = 'en_GB') THEN
	  INSERT INTO ad_traductions (id_str, langue, traduction) VALUES (id_str_trad, 'en_GB', 'Budget Management');
	 END IF;
	END IF;
	--Mise en Place : Saisie Type de Budget
	----Menu
	IF NOT EXISTS (select * from menus where nom_menu = 'Meb-1') THEN
	 id_str_trad := maketraductionlangsyst('Mise en Place : Saisie Type de Budget');
	 INSERT INTO menus (nom_menu, libel_menu, nom_pere, pos_hierarch, ordre, is_menu,fonction, is_cliquable)
	 VALUES ('Meb-1', id_str_trad, 'Meb', 4, 1, FALSE, 705, TRUE);
	 IF EXISTS(SELECT langue FROM adsys_langues_systeme WHERE code = 'en_GB') THEN
	  INSERT INTO ad_traductions (id_str, langue, traduction) VALUES (id_str_trad, 'en_GB', 'Implementing : Select Type of Budget');
	 END IF;
	END IF;
	----Ecran
	IF NOT EXISTS (select * from ecrans where nom_ecran = 'Meb-1') THEN
	 INSERT INTO ecrans(nom_ecran, fichier, nom_menu, fonction)
	 VALUES ('Meb-1', 'modules/budget/mise_en_place_budget.php', 'Meb-1', 705);
	END IF;
	--Mise en Place : Saisie Données
	----Menu
	IF NOT EXISTS (select * from menus where nom_menu = 'Meb-2') THEN
	 id_str_trad := maketraductionlangsyst('Mise en Place : Saisie Données');
	 INSERT INTO menus (nom_menu, libel_menu, nom_pere, pos_hierarch, ordre, is_menu,fonction, is_cliquable)
	 VALUES ('Meb-2', id_str_trad, 'Meb', 4, 2, FALSE, 705, FALSE);
	 IF EXISTS(SELECT langue FROM adsys_langues_systeme WHERE code = 'en_GB') THEN
	  INSERT INTO ad_traductions (id_str, langue, traduction) VALUES (id_str_trad, 'en_GB', 'Implementing : Data Entry');
	 END IF;
	END IF;
	----Ecran
	IF NOT EXISTS (select * from ecrans where nom_ecran = 'Meb-2') THEN
	 INSERT INTO ecrans(nom_ecran, fichier, nom_menu, fonction)
	 VALUES ('Meb-2', 'modules/budget/mise_en_place_budget.php', 'Meb-2', 705);
	END IF;
	--Raffiner le Budget
	----Main Menu
	IF NOT EXISTS (select * from menus where nom_menu = 'Rlb') THEN
	 id_str_trad := maketraductionlangsyst('Raffiner le Budget');
	 INSERT INTO menus (nom_menu, libel_menu, nom_pere, pos_hierarch, ordre, is_menu,fonction, is_cliquable)
	 VALUES ('Rlb', id_str_trad, 'Gen-15', 3, 3, TRUE, 706, TRUE);
	 IF EXISTS(SELECT langue FROM adsys_langues_systeme WHERE code = 'en_GB') THEN
	  INSERT INTO ad_traductions (id_str, langue, traduction) VALUES (id_str_trad, 'en_GB', 'Budget Cleaning');
	 END IF;
	END IF;
	--Raffiner le Budget : Saisie Type de Budget
	----Menu
	IF NOT EXISTS (select * from menus where nom_menu = 'Rlb-1') THEN
	 id_str_trad := maketraductionlangsyst('Raffiner le Budget : Saisie Type de Budget');
	 INSERT INTO menus (nom_menu, libel_menu, nom_pere, pos_hierarch, ordre, is_menu,fonction, is_cliquable)
	 VALUES ('Rlb-1', id_str_trad, 'Rlb', 4, 1, FALSE, 706, TRUE);
	 IF EXISTS(SELECT langue FROM adsys_langues_systeme WHERE code = 'en_GB') THEN
	  INSERT INTO ad_traductions (id_str, langue, traduction) VALUES (id_str_trad, 'en_GB', 'Cleaning : Select Type of Budget');
	 END IF;
	END IF;
	----Ecran
	IF NOT EXISTS (select * from ecrans where nom_ecran = 'Rlb-1') THEN
	 INSERT INTO ecrans(nom_ecran, fichier, nom_menu, fonction)
	 VALUES ('Rlb-1', 'modules/budget/mise_en_place_budget.php', 'Rlb-1', 706);
	END IF;
	--Raffiner le Budget : Saisie Données
	----Menu
	IF NOT EXISTS (select * from menus where nom_menu = 'Rlb-2') THEN
	 id_str_trad := maketraductionlangsyst('Raffiner le Budget : Saisie Données');
	 INSERT INTO menus (nom_menu, libel_menu, nom_pere, pos_hierarch, ordre, is_menu,fonction, is_cliquable)
	 VALUES ('Rlb-2', id_str_trad, 'Rlb', 4, 2, FALSE, 706, FALSE);
	 IF EXISTS(SELECT langue FROM adsys_langues_systeme WHERE code = 'en_GB') THEN
	  INSERT INTO ad_traductions (id_str, langue, traduction) VALUES (id_str_trad, 'en_GB', 'Cleaning : Data Entry');
	 END IF;
	END IF;
	----Ecran
	IF NOT EXISTS (select * from ecrans where nom_ecran = 'Rlb-2') THEN
	 INSERT INTO ecrans(nom_ecran, fichier, nom_menu, fonction)
	 VALUES ('Rlb-2', 'modules/budget/mise_en_place_budget.php', 'Rlb-2', 706);
	END IF;
	--Réviser le Budget
	----Main Menu
	IF NOT EXISTS (select * from menus where nom_menu = 'Rdb') THEN
	 id_str_trad := maketraductionlangsyst('Réviser le Budget');
	 INSERT INTO menus (nom_menu, libel_menu, nom_pere, pos_hierarch, ordre, is_menu,fonction, is_cliquable)
	 VALUES ('Rdb', id_str_trad, 'Gen-15', 3, 4, TRUE, 707, TRUE);
	 IF EXISTS(SELECT langue FROM adsys_langues_systeme WHERE code = 'en_GB') THEN
	  INSERT INTO ad_traductions (id_str, langue, traduction) VALUES (id_str_trad, 'en_GB', 'Budget Review');
	 END IF;
	END IF;
	--Réviser le Budget : Saisie Type de Budget
	----Menu
	IF NOT EXISTS (select * from menus where nom_menu = 'Rdb-1') THEN
	 id_str_trad := maketraductionlangsyst('Réviser le Budget : Saisie Type de Budget');
	 INSERT INTO menus (nom_menu, libel_menu, nom_pere, pos_hierarch, ordre, is_menu,fonction, is_cliquable)
	 VALUES ('Rdb-1', id_str_trad, 'Rdb', 4, 1, FALSE, 707, TRUE);
	 IF EXISTS(SELECT langue FROM adsys_langues_systeme WHERE code = 'en_GB') THEN
	  INSERT INTO ad_traductions (id_str, langue, traduction) VALUES (id_str_trad, 'en_GB', 'Review : Select Type of Budget');
	 END IF;
	END IF;
	----Ecran
	IF NOT EXISTS (select * from ecrans where nom_ecran = 'Rdb-1') THEN
	 INSERT INTO ecrans(nom_ecran, fichier, nom_menu, fonction)
	 VALUES ('Rdb-1', 'modules/budget/revision_budget.php', 'Rdb-1', 707);
	END IF;
	--Réviser le Budget : Tableau
	----Menu
	IF NOT EXISTS (select * from menus where nom_menu = 'Rdb-2') THEN
	 id_str_trad := maketraductionlangsyst('Réviser le Budget : Tableau');
	 INSERT INTO menus (nom_menu, libel_menu, nom_pere, pos_hierarch, ordre, is_menu,fonction, is_cliquable)
	 VALUES ('Rdb-2', id_str_trad, 'Rdb', 4, 2, FALSE, 707, FALSE);
	 IF EXISTS(SELECT langue FROM adsys_langues_systeme WHERE code = 'en_GB') THEN
	  INSERT INTO ad_traductions (id_str, langue, traduction) VALUES (id_str_trad, 'en_GB', 'Review : Table');
	 END IF;
	END IF;
	----Ecran
	IF NOT EXISTS (select * from ecrans where nom_ecran = 'Rdb-2') THEN
	 INSERT INTO ecrans(nom_ecran, fichier, nom_menu, fonction)
	 VALUES ('Rdb-2', 'modules/budget/revision_budget.php', 'Rdb-2', 707);
	END IF;
	--Réviser le Budget : Effectuer la révision du Budget
	----Menu
	IF NOT EXISTS (select * from menus where nom_menu = 'Rdb-3') THEN
	 id_str_trad := maketraductionlangsyst('Effectuer la révision du Budget');
	 INSERT INTO menus (nom_menu, libel_menu, nom_pere, pos_hierarch, ordre, is_menu,fonction, is_cliquable)
	 VALUES ('Rdb-3', id_str_trad, 'Rdb', 4, 3, FALSE, 707, FALSE);
	 IF EXISTS(SELECT langue FROM adsys_langues_systeme WHERE code = 'en_GB') THEN
	  INSERT INTO ad_traductions (id_str, langue, traduction) VALUES (id_str_trad, 'en_GB', 'Review of Budget');
	 END IF;
	END IF;
	----Ecran
	IF NOT EXISTS (select * from ecrans where nom_ecran = 'Rdb-3') THEN
	 INSERT INTO ecrans(nom_ecran, fichier, nom_menu, fonction)
	 VALUES ('Rdb-3', 'modules/budget/revision_budget.php', 'Rdb-3', 707);
	END IF;
	--========> Fin Gestion Budget : Mise en Place, Raffinement et Revision

	--========> Debut Validation Budget
	----Main Menu
	IF NOT EXISTS (select * from menus where nom_menu = 'Vlb') THEN
	 id_str_trad := maketraductionlangsyst('Valider le Budget');
	 INSERT INTO menus (nom_menu, libel_menu, nom_pere, pos_hierarch, ordre, is_menu,fonction, is_cliquable)
	 VALUES ('Vlb', id_str_trad, 'Gen-15', 3, 5, TRUE, 708, TRUE);
	 IF EXISTS(SELECT langue FROM adsys_langues_systeme WHERE code = 'en_GB') THEN
	  INSERT INTO ad_traductions (id_str, langue, traduction) VALUES (id_str_trad, 'en_GB', 'Validation of Budget');
	 END IF;
	END IF;
	--Valider le Budget : Saisie Type de Budget et Validation
	----Menu
	IF NOT EXISTS (select * from menus where nom_menu = 'Vlb-1') THEN
	 id_str_trad := maketraductionlangsyst('Valider le Budget : Type de Budget et Validation');
	 INSERT INTO menus (nom_menu, libel_menu, nom_pere, pos_hierarch, ordre, is_menu,fonction, is_cliquable)
	 VALUES ('Vlb-1', id_str_trad, 'Vlb', 4, 1, FALSE, 708, FALSE);
	 IF EXISTS(SELECT langue FROM adsys_langues_systeme WHERE code = 'en_GB') THEN
	  INSERT INTO ad_traductions (id_str, langue, traduction) VALUES (id_str_trad, 'en_GB', 'Type of Budget and Validation');
	 END IF;
	END IF;
	----Ecran
	IF NOT EXISTS (select * from ecrans where nom_ecran = 'Vlb-1') THEN
	 INSERT INTO ecrans(nom_ecran, fichier, nom_menu, fonction)
	 VALUES ('Vlb-1', 'modules/budget/validation_budget.php', 'Vlb-1', 708);
	END IF;
	--Valider le Budget : Verification Données
	----Menu
	IF NOT EXISTS (select * from menus where nom_menu = 'Vlb-2') THEN
	 id_str_trad := maketraductionlangsyst('Valider le Budget : Verification Données');
	 INSERT INTO menus (nom_menu, libel_menu, nom_pere, pos_hierarch, ordre, is_menu,fonction, is_cliquable)
	 VALUES ('Vlb-2', id_str_trad, 'Vlb', 4, 2, FALSE, 708, FALSE);
	 IF EXISTS(SELECT langue FROM adsys_langues_systeme WHERE code = 'en_GB') THEN
	  INSERT INTO ad_traductions (id_str, langue, traduction) VALUES (id_str_trad, 'en_GB', 'Budget Data verification');
	 END IF;
	END IF;
	----Ecran
	IF NOT EXISTS (select * from ecrans where nom_ecran = 'Vlb-2') THEN
	 INSERT INTO ecrans(nom_ecran, fichier, nom_menu, fonction)
	 VALUES ('Vlb-2', 'modules/budget/validation_budget.php', 'Vlb-2', 708);
	END IF;
	--========> Fin Validation Budget

	--========> Debut Confirmation du Budget
	----Menu
	IF NOT EXISTS (select * from menus where nom_menu = 'Cmb-1') THEN
	 id_str_trad := maketraductionlangsyst('Confirmation du Budget');
	 INSERT INTO menus (nom_menu, libel_menu, nom_pere, pos_hierarch, ordre, is_menu,fonction, is_cliquable)
	 VALUES ('Cmb-1', id_str_trad, 'Meb', 4, 3, FALSE, 701, FALSE);
	 IF EXISTS(SELECT langue FROM adsys_langues_systeme WHERE code = 'en_GB') THEN
	  INSERT INTO ad_traductions (id_str, langue, traduction) VALUES (id_str_trad, 'en_GB', 'Confirmation of Budget');
	 END IF;
	END IF;
	----Ecran
	IF NOT EXISTS (select * from ecrans where nom_ecran = 'Cmb-1') THEN
	 INSERT INTO ecrans(nom_ecran, fichier, nom_menu, fonction)
	 VALUES ('Cmb-1', 'modules/budget/mise_en_place_budget.php', 'Cmb-1', 701);
	END IF;
	--========> Fin Confirmation du Budget

	--========> Debut Visualisation du Budget
	----Main Menu
	IF NOT EXISTS (select * from menus where nom_menu = 'Vdb') THEN
	 id_str_trad := maketraductionlangsyst('Visualisation du Budget');
	 INSERT INTO menus (nom_menu, libel_menu, nom_pere, pos_hierarch, ordre, is_menu,fonction, is_cliquable)
	 VALUES ('Vdb', id_str_trad, 'Gen-15', 3, 6, TRUE, 712, TRUE);
	 IF EXISTS(SELECT langue FROM adsys_langues_systeme WHERE code = 'en_GB') THEN
	  INSERT INTO ad_traductions (id_str, langue, traduction) VALUES (id_str_trad, 'en_GB', 'View Budget');
	 END IF;
	END IF;
	--Visualisation du Budget : Saisie Type de Budget
	----Menu
	IF NOT EXISTS (select * from menus where nom_menu = 'Vdb-1') THEN
	 id_str_trad := maketraductionlangsyst('Visualisation : Saisie Type de Budget');
	 INSERT INTO menus (nom_menu, libel_menu, nom_pere, pos_hierarch, ordre, is_menu,fonction, is_cliquable)
	 VALUES ('Vdb-1', id_str_trad, 'Vdb', 4, 1, TRUE, 712, TRUE);
	 IF EXISTS(SELECT langue FROM adsys_langues_systeme WHERE code = 'en_GB') THEN
	  INSERT INTO ad_traductions (id_str, langue, traduction) VALUES (id_str_trad, 'en_GB', 'View Budget : Type of Budget');
	 END IF;
	END IF;
	----Ecran
	IF NOT EXISTS (select * from ecrans where nom_ecran = 'Vdb-1') THEN
	 INSERT INTO ecrans(nom_ecran, fichier, nom_menu, fonction)
	 VALUES ('Vdb-1', 'modules/budget/visualisation_budget.php', 'Vdb-1', 712);
	END IF;
	--Visualisation du Budget : Visualiser
	----Menu
	IF NOT EXISTS (select * from menus where nom_menu = 'Vdb-2') THEN
	 id_str_trad := maketraductionlangsyst('Visualisation : Visualiser');
	 INSERT INTO menus (nom_menu, libel_menu, nom_pere, pos_hierarch, ordre, is_menu,fonction, is_cliquable)
	 VALUES ('Vdb-2', id_str_trad, 'Vdb', 4, 2, FALSE, 712, FALSE);
	 IF EXISTS(SELECT langue FROM adsys_langues_systeme WHERE code = 'en_GB') THEN
	  INSERT INTO ad_traductions (id_str, langue, traduction) VALUES (id_str_trad, 'en_GB', 'View Budget : View');
	 END IF;
	END IF;
	----Ecran
	IF NOT EXISTS (select * from ecrans where nom_ecran = 'Vdb-2') THEN
	 INSERT INTO ecrans(nom_ecran, fichier, nom_menu, fonction)
	 VALUES ('Vdb-2', 'modules/budget/visualisation_budget.php', 'Vdb-2', 712);
	END IF;
	--========> Fin Visualisation du Budget

	--========> Debut Visualisation des Comptes Comptables bloqués
	----Main Menu
	IF NOT EXISTS (select * from menus where nom_menu = 'Dcc') THEN
	 id_str_trad := maketraductionlangsyst('Deblocage des comptes comptables');
	 INSERT INTO menus (nom_menu, libel_menu, nom_pere, pos_hierarch, ordre, is_menu,fonction, is_cliquable)
	 VALUES ('Dcc', id_str_trad, 'Gen-15', 3, 7, TRUE, 713, TRUE);
	 IF EXISTS(SELECT langue FROM adsys_langues_systeme WHERE code = 'en_GB') THEN
	  INSERT INTO ad_traductions (id_str, langue, traduction) VALUES (id_str_trad, 'en_GB', 'Unblocking Accounts');
	 END IF;
	END IF;
	--Visualisation Comptes Comptables bloqués
	----Menu
	IF NOT EXISTS (select * from menus where nom_menu = 'Dcc-1') THEN
	 id_str_trad := maketraductionlangsyst('Selection type de budget');
	 INSERT INTO menus (nom_menu, libel_menu, nom_pere, pos_hierarch, ordre, is_menu,fonction, is_cliquable)
	 VALUES ('Dcc-1', id_str_trad, 'Dcc', 4, 1, FALSE, 713, FALSE);
	 IF EXISTS(SELECT langue FROM adsys_langues_systeme WHERE code = 'en_GB') THEN
	  INSERT INTO ad_traductions (id_str, langue, traduction) VALUES (id_str_trad, 'en_GB', 'Select type of budget');
	 END IF;
	END IF;
	----Ecran
	IF NOT EXISTS (select * from ecrans where nom_ecran = 'Dcc-1') THEN
	 INSERT INTO ecrans(nom_ecran, fichier, nom_menu, fonction)
	 VALUES ('Dcc-1', 'modules/budget/debloq_comptes_budget.php', 'Dcc-1', 713);
	END IF;
		----Menu
	IF NOT EXISTS (select * from menus where nom_menu = 'Dcc-2') THEN
	 id_str_trad := maketraductionlangsyst('Visualisation Comptes Comptables bloqués');
	 INSERT INTO menus (nom_menu, libel_menu, nom_pere, pos_hierarch, ordre, is_menu,fonction, is_cliquable)
	 VALUES ('Dcc-2', id_str_trad, 'Dcc', 4, 2, FALSE, 713, FALSE);
	 IF EXISTS(SELECT langue FROM adsys_langues_systeme WHERE code = 'en_GB') THEN
	  INSERT INTO ad_traductions (id_str, langue, traduction) VALUES (id_str_trad, 'en_GB', 'View Blocked Accounts');
	 END IF;
	END IF;
	----Ecran
	IF NOT EXISTS (select * from ecrans where nom_ecran = 'Dcc-2') THEN
	 INSERT INTO ecrans(nom_ecran, fichier, nom_menu, fonction)
	 VALUES ('Dcc-2', 'modules/budget/debloq_comptes_budget.php', 'Dcc-2', 713);
	END IF;
	--Debloquer Comptes Comptables
	----Menu
	IF NOT EXISTS (select * from menus where nom_menu = 'Dcc-3') THEN
	 id_str_trad := maketraductionlangsyst('Debloquer Lignes budgetaires');
	 INSERT INTO menus (nom_menu, libel_menu, nom_pere, pos_hierarch, ordre, is_menu,fonction, is_cliquable)
	 VALUES ('Dcc-3', id_str_trad, 'Dcc', 4, 3, FALSE, 714, FALSE);
	 IF EXISTS(SELECT langue FROM adsys_langues_systeme WHERE code = 'en_GB') THEN
	  INSERT INTO ad_traductions (id_str, langue, traduction) VALUES (id_str_trad, 'en_GB', 'Unblocked line of budget');
	 END IF;
	END IF;
	----Ecran
	IF NOT EXISTS (select * from ecrans where nom_ecran = 'Dcc-3') THEN
	 INSERT INTO ecrans(nom_ecran, fichier, nom_menu, fonction)
	 VALUES ('Dcc-3', 'modules/budget/debloq_comptes_budget.php', 'Dcc-3', 714);
	END IF;
		--Validation Deblocage Comptes Comptables
	----Menu
	IF NOT EXISTS (select * from menus where nom_menu = 'Dcc-4') THEN
	 id_str_trad := maketraductionlangsyst('Validation déblocage ligne budgetaire');
	 INSERT INTO menus (nom_menu, libel_menu, nom_pere, pos_hierarch, ordre, is_menu,fonction, is_cliquable)
	 VALUES ('Dcc-4', id_str_trad, 'Dcc', 4, 4, FALSE, 701, FALSE);
	 IF EXISTS(SELECT langue FROM adsys_langues_systeme WHERE code = 'en_GB') THEN
	  INSERT INTO ad_traductions (id_str, langue, traduction) VALUES (id_str_trad, 'en_GB', 'Confirmation of unblock line of budget');
	 END IF;
	END IF;
	----Ecran
	IF NOT EXISTS (select * from ecrans where nom_ecran = 'Dcc-4') THEN
	 INSERT INTO ecrans(nom_ecran, fichier, nom_menu, fonction)
	 VALUES ('Dcc-4', 'modules/budget/debloq_comptes_budget.php', 'Dcc-4', 701);
	END IF;
	--========> Fin Visualisation des Comptes Comptables bloqués

	--========> Debut Rapport Budget
	----Main Menu
	IF NOT EXISTS (select * from menus where nom_menu = 'Rpb') THEN
	 id_str_trad := maketraductionlangsyst('Rapports Budget');
	 INSERT INTO menus (nom_menu, libel_menu, nom_pere, pos_hierarch, ordre, is_menu,fonction, is_cliquable)
	 VALUES ('Rpb', id_str_trad, 'Gen-15', 3, 8, TRUE, 715, TRUE);
	 IF EXISTS(SELECT langue FROM adsys_langues_systeme WHERE code = 'en_GB') THEN
	  INSERT INTO ad_traductions (id_str, langue, traduction) VALUES (id_str_trad, 'en_GB', 'Budget Reports');
	 END IF;
	END IF;
	--Selection Rapport Budget
	----Menu
	IF NOT EXISTS (select * from menus where nom_menu = 'Rpb-1') THEN
	 id_str_trad := maketraductionlangsyst('Selection Rapport');
	 INSERT INTO menus (nom_menu, libel_menu, nom_pere, pos_hierarch, ordre, is_menu,fonction, is_cliquable)
	 VALUES ('Rpb-1', id_str_trad, 'Rpb', 4, 1, FALSE, 715, FALSE);
	 IF EXISTS(SELECT langue FROM adsys_langues_systeme WHERE code = 'en_GB') THEN
	  INSERT INTO ad_traductions (id_str, langue, traduction) VALUES (id_str_trad, 'en_GB', 'Select Report');
	 END IF;
	END IF;
	----Ecran
	IF NOT EXISTS (select * from ecrans where nom_ecran = 'Rpb-1') THEN
	 INSERT INTO ecrans(nom_ecran, fichier, nom_menu, fonction)
	 VALUES ('Rpb-1', 'modules/rapports/rapports_budget.php', 'Rpb-1', 715);
	END IF;
	--Personnalisation du Rapport Budget
	----Menu
	IF NOT EXISTS (select * from menus where nom_menu = 'Rpb-2') THEN
	 id_str_trad := maketraductionlangsyst('Personnalisation du Rapport');
	 INSERT INTO menus (nom_menu, libel_menu, nom_pere, pos_hierarch, ordre, is_menu,fonction, is_cliquable)
	 VALUES ('Rpb-2', id_str_trad, 'Rpb', 4, 2, FALSE, 715, FALSE);
	 IF EXISTS(SELECT langue FROM adsys_langues_systeme WHERE code = 'en_GB') THEN
	  INSERT INTO ad_traductions (id_str, langue, traduction) VALUES (id_str_trad, 'en_GB', 'Report Customization');
	 END IF;
	END IF;
	----Ecran
	IF NOT EXISTS (select * from ecrans where nom_ecran = 'Rpb-2') THEN
	 INSERT INTO ecrans(nom_ecran, fichier, nom_menu, fonction)
	 VALUES ('Rpb-2', 'modules/rapports/rapports_budget.php', 'Rpb-2', 715);
	END IF;
	--Impression du Rapport Budget
	----Menu
	IF NOT EXISTS (select * from menus where nom_menu = 'Rpb-3') THEN
	 id_str_trad := maketraductionlangsyst('Impression du Rapport');
	 INSERT INTO menus (nom_menu, libel_menu, nom_pere, pos_hierarch, ordre, is_menu,fonction, is_cliquable)
	 VALUES ('Rpb-3', id_str_trad, 'Rpb', 4, 3, FALSE, 715, FALSE);
	 IF EXISTS(SELECT langue FROM adsys_langues_systeme WHERE code = 'en_GB') THEN
	  INSERT INTO ad_traductions (id_str, langue, traduction) VALUES (id_str_trad, 'en_GB', 'Generation of Report');
	 END IF;
	END IF;
	----Ecran
	IF NOT EXISTS (select * from ecrans where nom_ecran = 'Rpb-3') THEN
	 INSERT INTO ecrans(nom_ecran, fichier, nom_menu, fonction)
	 VALUES ('Rpb-3', 'modules/rapports/rapports_budget.php', 'Rpb-3', 715);
	END IF;
	--========> Fin Rapport Budget

	/* ----------------- Creation Ecrans/Menus pour la Mise en Place/Validation des nouvelles lignes budgetaires ----------------------------*/
	--Mise en place nouveau ligne budgetaire
	----Main Menu
	IF NOT EXISTS (select * from menus where nom_menu = 'Mnl') THEN
	 id_str_trad := maketraductionlangsyst('Mise en Place Nouvelle Ligne Budgetaire');
	 INSERT INTO menus (nom_menu, libel_menu, nom_pere, pos_hierarch, ordre, is_menu,fonction, is_cliquable)
	 VALUES ('Mnl', id_str_trad, 'Gen-15', 3, (SELECT (ordre + 1) FROM menus WHERE nom_pere LIKE '%Gen-15%' ORDER BY ordre DESC LIMIT 1), TRUE, 716, TRUE);
	 IF EXISTS(SELECT langue FROM adsys_langues_systeme WHERE code = 'en_GB') THEN
	  INSERT INTO ad_traductions (id_str, langue, traduction) VALUES (id_str_trad, 'en_GB', 'New Line of Budget');
	 END IF;
	END IF;
	--Mise en place : Saisie Type de Budget
	----Menu
	IF NOT EXISTS (select * from menus where nom_menu = 'Mnl-1') THEN
	 id_str_trad := maketraductionlangsyst('Nouvelle Ligne Budget: Saisie Type de Budget');
	 INSERT INTO menus (nom_menu, libel_menu, nom_pere, pos_hierarch, ordre, is_menu,fonction, is_cliquable)
	 VALUES ('Mnl-1', id_str_trad, 'Mnl', 4, 1, FALSE, 716, TRUE);
	 IF EXISTS(SELECT langue FROM adsys_langues_systeme WHERE code = 'en_GB') THEN
	  INSERT INTO ad_traductions (id_str, langue, traduction) VALUES (id_str_trad, 'en_GB', 'New Budget Line : Select Type of Budget');
	 END IF;
	END IF;
	----Ecran
	IF NOT EXISTS (select * from ecrans where nom_ecran = 'Mnl-1') THEN
	 INSERT INTO ecrans(nom_ecran, fichier, nom_menu, fonction)
	 VALUES ('Mnl-1', 'modules/budget/mise_en_place_budget.php', 'Mnl-1', 716);
	END IF;
	--Mise en place : Tableau
	----Menu
	IF NOT EXISTS (select * from menus where nom_menu = 'Mnl-2') THEN
	 id_str_trad := maketraductionlangsyst('Nouvelle Ligne Budget: Saisie Ligne');
	 INSERT INTO menus (nom_menu, libel_menu, nom_pere, pos_hierarch, ordre, is_menu,fonction, is_cliquable)
	 VALUES ('Mnl-2', id_str_trad, 'Mnl', 4, 2, FALSE, 716, FALSE);
	 IF EXISTS(SELECT langue FROM adsys_langues_systeme WHERE code = 'en_GB') THEN
	  INSERT INTO ad_traductions (id_str, langue, traduction) VALUES (id_str_trad, 'en_GB', 'New Budget Line : Select Line');
	 END IF;
	END IF;
	----Ecran
	IF NOT EXISTS (select * from ecrans where nom_ecran = 'Mnl-2') THEN
	 INSERT INTO ecrans(nom_ecran, fichier, nom_menu, fonction)
	 VALUES ('Mnl-2', 'modules/budget/mise_en_place_budget.php', 'Mnl-2', 716);
	END IF;
	--Mise en place : L'ajout du nouveau ligne budgetaire
	----Menu
	IF NOT EXISTS (select * from menus where nom_menu = 'Mnl-3') THEN
	 id_str_trad := maketraductionlangsyst('Ajout de la nouvelle ligne budgetaire');
	 INSERT INTO menus (nom_menu, libel_menu, nom_pere, pos_hierarch, ordre, is_menu,fonction, is_cliquable)
	 VALUES ('Mnl-3', id_str_trad, 'Mnl', 4, 3, FALSE, 716, FALSE);
	 IF EXISTS(SELECT langue FROM adsys_langues_systeme WHERE code = 'en_GB') THEN
	  INSERT INTO ad_traductions (id_str, langue, traduction) VALUES (id_str_trad, 'en_GB', 'Add new Budget Line');
	 END IF;
	END IF;
	----Ecran
	IF NOT EXISTS (select * from ecrans where nom_ecran = 'Mnl-3') THEN
	 INSERT INTO ecrans(nom_ecran, fichier, nom_menu, fonction)
	 VALUES ('Mnl-3', 'modules/budget/mise_en_place_budget.php', 'Mnl-3', 716);
	END IF;
	--------------------------------------------------------------------------------------
	--Validation Nouvelle Ligne Budgetaire
	----Main Menu
	IF NOT EXISTS (select * from menus where nom_menu = 'Vnl') THEN
	 id_str_trad := maketraductionlangsyst('Validation Nouvelle Ligne Budgetaire');
	 INSERT INTO menus (nom_menu, libel_menu, nom_pere, pos_hierarch, ordre, is_menu,fonction, is_cliquable)
	 VALUES ('Vnl', id_str_trad, 'Gen-15', 3, (SELECT (ordre + 1) FROM menus WHERE nom_pere LIKE '%Gen-15%' ORDER BY ordre DESC LIMIT 1), TRUE, 717, TRUE);
	 IF EXISTS(SELECT langue FROM adsys_langues_systeme WHERE code = 'en_GB') THEN
	  INSERT INTO ad_traductions (id_str, langue, traduction) VALUES (id_str_trad, 'en_GB', 'Validaation New Line of Budget');
	 END IF;
	END IF;
	--Validation : Saisie Type de Budget
	----Menu
	IF NOT EXISTS (select * from menus where nom_menu = 'Vnl-1') THEN
	 id_str_trad := maketraductionlangsyst('Validation Nouvelle Ligne: Saisie Type de Budget');
	 INSERT INTO menus (nom_menu, libel_menu, nom_pere, pos_hierarch, ordre, is_menu,fonction, is_cliquable)
	 VALUES ('Vnl-1', id_str_trad, 'Vnl', 4, 1, FALSE, 717, TRUE);
	 IF EXISTS(SELECT langue FROM adsys_langues_systeme WHERE code = 'en_GB') THEN
	  INSERT INTO ad_traductions (id_str, langue, traduction) VALUES (id_str_trad, 'en_GB', 'Validation New Line : Select Type of Budget');
	 END IF;
	END IF;
	----Ecran
	IF NOT EXISTS (select * from ecrans where nom_ecran = 'Vnl-1') THEN
	 INSERT INTO ecrans(nom_ecran, fichier, nom_menu, fonction)
	 VALUES ('Vnl-1', 'modules/budget/validation_budget.php', 'Vnl-1', 717);
	END IF;
	--Validation : Tableau Nouvelle Ligne Budgetaire
	----Menu
	IF NOT EXISTS (select * from menus where nom_menu = 'Vnl-2') THEN
	 id_str_trad := maketraductionlangsyst('Validation Nouvelle Ligne: Saisie Ligne');
	 INSERT INTO menus (nom_menu, libel_menu, nom_pere, pos_hierarch, ordre, is_menu,fonction, is_cliquable)
	 VALUES ('Vnl-2', id_str_trad, 'Vnl', 4, 2, FALSE, 717, FALSE);
	 IF EXISTS(SELECT langue FROM adsys_langues_systeme WHERE code = 'en_GB') THEN
	  INSERT INTO ad_traductions (id_str, langue, traduction) VALUES (id_str_trad, 'en_GB', 'Validation New Line : Select Line');
	 END IF;
	END IF;
	----Ecran
	IF NOT EXISTS (select * from ecrans where nom_ecran = 'Vnl-2') THEN
	 INSERT INTO ecrans(nom_ecran, fichier, nom_menu, fonction)
	 VALUES ('Vnl-2', 'modules/budget/validation_budget.php', 'Vnl-2', 717);
	END IF;

	output_result := 1;

	RETURN output_result;

END;
$BODY$
  LANGUAGE plpgsql VOLATILE
  COST 100;
ALTER FUNCTION creation_menus_ecrans_budget()
  OWNER TO adbanking;

  SELECT creation_menus_ecrans_budget();

  DROP FUNCTION IF EXISTS creation_menus_ecrans_budget();
