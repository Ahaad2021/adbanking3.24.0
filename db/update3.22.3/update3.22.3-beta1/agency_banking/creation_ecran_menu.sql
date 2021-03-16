
	 ------------------------------- DEBUT : Ticket AT-39 -----------------------------------------------------------------------------------------------------------
CREATE OR REPLACE FUNCTION script_creation_menu_ecran_agb() RETURNS INT AS
$$
DECLARE
output_result INTEGER = 1;
  id_str_trad integer = 0;
  pos_ordre integer = 0;

BEGIN

	--========> Update Menu 'Out' position up
	IF EXISTS (select * from menus where nom_menu = 'Out') THEN
	 SELECT INTO pos_ordre ordre FROM menus WHERE nom_menu = 'Out';
	 UPDATE menus SET ordre = (pos_ordre+1) WHERE nom_menu = 'Out';
	END IF;

	--========> Debut Agency Banking
	--Menu
	IF NOT EXISTS (select * from menus where nom_menu = 'Gen-16') THEN
	 id_str_trad := maketraductionlangsyst('Agency Banking');
	 INSERT INTO menus (nom_menu, libel_menu, nom_pere, pos_hierarch, ordre, is_menu,fonction, is_cliquable)
	 VALUES ('Gen-16', id_str_trad, 'Gen-3', 2, pos_ordre, TRUE, 751, TRUE);
	 IF EXISTS(SELECT langue FROM adsys_langues_systeme WHERE code = 'en_GB') THEN
	  INSERT INTO ad_traductions (id_str, langue, traduction) VALUES (id_str_trad, 'en_GB', 'Agency Banking');
	 END IF;
	END IF;
	--Ecran
	IF NOT EXISTS (select * from ecrans where nom_ecran = 'Gen-16') THEN
	 INSERT INTO ecrans(nom_ecran, fichier, nom_menu, fonction)
	 VALUES ('Gen-16', 'modules/menus/menu.php', 'Gen-16', 751);
	END IF;

		--========> Debut Gestions des utilisateurs agents
	--Main Menu
	IF NOT EXISTS (select * from menus where nom_menu = 'Aga') THEN
	 id_str_trad := maketraductionlangsyst('Gestion des utilisateurs agents');
	 INSERT INTO menus (nom_menu, libel_menu, nom_pere, pos_hierarch, ordre, is_menu,fonction, is_cliquable)
	 VALUES ('Aga', id_str_trad, 'Gen-16', 3, 1, TRUE, 752, FALSE);
	 IF EXISTS(SELECT langue FROM adsys_langues_systeme WHERE code = 'en_GB') THEN
	  INSERT INTO ad_traductions (id_str, langue, traduction) VALUES (id_str_trad, 'en_GB', 'Managing agent users');
	 END IF;
	END IF;

	----Menu ajout nouvelle utilisateurs agents
	--menu ( a revoir)
	IF NOT EXISTS (select * from menus where nom_menu = 'Aga-1') THEN
	 id_str_trad := maketraductionlangsyst('Liste des utulisateurs agents');
	 INSERT INTO menus (nom_menu, libel_menu, nom_pere, pos_hierarch, ordre, is_menu,fonction, is_cliquable)
	 VALUES ('Aga-1', id_str_trad, 'Aga', 4, 1, FALSE, 752, TRUE);
	 IF EXISTS(SELECT langue FROM adsys_langues_systeme WHERE code = 'en_GB') THEN
	  INSERT INTO ad_traductions (id_str, langue, traduction) VALUES (id_str_trad, 'en_GB', 'List of agent user');
	 END IF;
	END IF;
	----Ecran
	IF NOT EXISTS (select * from ecrans where nom_ecran = 'Aga-1') THEN
	 INSERT INTO ecrans(nom_ecran, fichier, nom_menu, fonction)
	 VALUES ('Aga-1', 'modules/agency_banking/utilisateur_agent.php', 'Aga', 752);
	END IF;

		--Liste des utilisateurs agents
	----Menu ajout agent
	IF NOT EXISTS (select * from menus where nom_menu = 'Aua') THEN
	 id_str_trad := maketraductionlangsyst('Ajout utilisateur agent');
	 INSERT INTO menus (nom_menu, libel_menu, nom_pere, pos_hierarch, ordre, is_menu,fonction, is_cliquable)
	 VALUES ('Aua', id_str_trad, 'Aga', 4, 1, TRUE, 753, FALSE);
	 IF EXISTS(SELECT langue FROM adsys_langues_systeme WHERE code = 'en_GB') THEN
	  INSERT INTO ad_traductions (id_str, langue, traduction) VALUES (id_str_trad, 'en_GB', 'Add agent user');
	 END IF;
	END IF;


	----Menu Informations personnelles agents
	IF NOT EXISTS (select * from menus where nom_menu = 'Aua-1') THEN
	 id_str_trad := maketraductionlangsyst('Informations personnelles agents');
	 INSERT INTO menus (nom_menu, libel_menu, nom_pere, pos_hierarch, ordre, is_menu, is_cliquable)
	 VALUES ('Aua-1', id_str_trad, 'Aua', 5, 1, FALSE,  FALSE);
	 IF EXISTS(SELECT langue FROM adsys_langues_systeme WHERE code = 'en_GB') THEN
	  INSERT INTO ad_traductions (id_str, langue, traduction) VALUES (id_str_trad, 'en_GB', 'information of agent user');
	 END IF;
	END IF;
	----Ecran
	IF NOT EXISTS (select * from ecrans where nom_ecran = 'Aua-1') THEN
	 INSERT INTO ecrans(nom_ecran, fichier, nom_menu, fonction)
	 VALUES ('Aua-1', 'modules/agency_banking/utilisateur_agent.php', 'Aua-1', 752);
	END IF;


	----Menu Confirmation
	IF NOT EXISTS (select * from menus where nom_menu = 'Aua-2') THEN
	 id_str_trad := maketraductionlangsyst('Confirmation ');
	 INSERT INTO menus (nom_menu, libel_menu, nom_pere, pos_hierarch, ordre, is_menu, is_cliquable)
	 VALUES ('Aua-2', id_str_trad, 'Aua', 5, 2, FALSE,  FALSE);
	 IF EXISTS(SELECT langue FROM adsys_langues_systeme WHERE code = 'en_GB') THEN
	  INSERT INTO ad_traductions (id_str, langue, traduction) VALUES (id_str_trad, 'en_GB', 'Confirmation ');
	 END IF;
	END IF;
	----Ecran
	IF NOT EXISTS (select * from ecrans where nom_ecran = 'Aua-2') THEN
	 INSERT INTO ecrans(nom_ecran, fichier, nom_menu, fonction)
	 VALUES ('Aua-2', 'modules/agency_banking/utilisateur_agent.php', 'Aua-2', 752);
	END IF;


	---Menu consultation
	IF NOT EXISTS (select * from menus where nom_menu = 'Cua') THEN
	 id_str_trad := maketraductionlangsyst('Consultation utilisateur agent');
	 INSERT INTO menus (nom_menu, libel_menu, nom_pere, pos_hierarch, ordre, is_menu,fonction, is_cliquable)
	 VALUES ('Cua', id_str_trad, 'Aga', 4, 2, TRUE, 752, FALSE);
	 IF EXISTS(SELECT langue FROM adsys_langues_systeme WHERE code = 'en_GB') THEN
	  INSERT INTO ad_traductions (id_str, langue, traduction) VALUES (id_str_trad, 'en_GB', 'View agent user');
	 END IF;
	END IF;

	---Menu consultation details agent
	IF NOT EXISTS (select * from menus where nom_menu = 'Cua-1') THEN
	 id_str_trad := maketraductionlangsyst('Consultation agent');
	 INSERT INTO menus (nom_menu, libel_menu, nom_pere, pos_hierarch, ordre, is_menu, is_cliquable)
	 VALUES ('Cua-1', id_str_trad, 'Cua', 5, 1, FALSE,  FALSE);
	 IF EXISTS(SELECT langue FROM adsys_langues_systeme WHERE code = 'en_GB') THEN
	  INSERT INTO ad_traductions (id_str, langue, traduction) VALUES (id_str_trad, 'en_GB', 'View agent user');
	 END IF;
	END IF;
	----Ecran
	IF NOT EXISTS (select * from ecrans where nom_ecran = 'Cua-1') THEN
	 INSERT INTO ecrans(nom_ecran, fichier, nom_menu, fonction)
	 VALUES ('Cua-1', 'modules/agency_banking/utilisateur_agent.php', 'Cua-1', 752);
	END IF;

	----Menu consultation login
	IF NOT EXISTS (select * from menus where nom_menu = 'Cua-2') THEN
	 id_str_trad := maketraductionlangsyst('Consultation code utilisateur agent');
	 INSERT INTO menus (nom_menu, libel_menu, nom_pere, pos_hierarch, ordre, is_menu, is_cliquable)
	 VALUES ('Cua-2', id_str_trad, 'Cua', 5, 2, FALSE,  FALSE);
	 IF EXISTS(SELECT langue FROM adsys_langues_systeme WHERE code = 'en_GB') THEN
	  INSERT INTO ad_traductions (id_str, langue, traduction) VALUES (id_str_trad, 'en_GB', 'View agent login');
	 END IF;
	END IF;
	----Ecran
	IF NOT EXISTS (select * from ecrans where nom_ecran = 'Cua-2') THEN
	 INSERT INTO ecrans(nom_ecran, fichier, nom_menu, fonction)
	 VALUES ('Cua-2', 'modules/agency_banking/utilisateur_agent.php', 'Cua-2', 752);
	END IF;


	---Menu modification agent
	IF NOT EXISTS (select * from menus where nom_menu = 'Mua') THEN
	 id_str_trad := maketraductionlangsyst('Modification utilisateur agent');
	 INSERT INTO menus (nom_menu, libel_menu, nom_pere, pos_hierarch, ordre, is_menu,fonction, is_cliquable)
	 VALUES ('Mua', id_str_trad, 'Aga', 4, 3, TRUE, 752, FALSE);
	 IF EXISTS(SELECT langue FROM adsys_langues_systeme WHERE code = 'en_GB') THEN
	  INSERT INTO ad_traductions (id_str, langue, traduction) VALUES (id_str_trad, 'en_GB', 'Modify agent user');
	 END IF;
	END IF;

	---Menu consultation details agent
	IF NOT EXISTS (select * from menus where nom_menu = 'Mua-1') THEN
	 id_str_trad := maketraductionlangsyst('Modification agent');
	 INSERT INTO menus (nom_menu, libel_menu, nom_pere, pos_hierarch, ordre, is_menu, is_cliquable)
	 VALUES ('Mua-1', id_str_trad, 'Mua', 5, 1, FALSE,  FALSE);
	 IF EXISTS(SELECT langue FROM adsys_langues_systeme WHERE code = 'en_GB') THEN
	  INSERT INTO ad_traductions (id_str, langue, traduction) VALUES (id_str_trad, 'en_GB', 'Modify agent user');
	 END IF;
	END IF;
	----Ecran
	IF NOT EXISTS (select * from ecrans where nom_ecran = 'Mua-1') THEN
	 INSERT INTO ecrans(nom_ecran, fichier, nom_menu, fonction)
	 VALUES ('Mua-1', 'modules/agency_banking/utilisateur_agent.php', 'Mua-1', 752);
	END IF;

	----Menu consultation login
	IF NOT EXISTS (select * from menus where nom_menu = 'Mua-2') THEN
	 id_str_trad := maketraductionlangsyst(' Confirmation ');
	 INSERT INTO menus (nom_menu, libel_menu, nom_pere, pos_hierarch, ordre, is_menu, is_cliquable)
	 VALUES ('Mua-2', id_str_trad, 'Mua', 5, 2, FALSE,  FALSE);
	 IF EXISTS(SELECT langue FROM adsys_langues_systeme WHERE code = 'en_GB') THEN
	  INSERT INTO ad_traductions (id_str, langue, traduction) VALUES (id_str_trad, 'en_GB', ' Confirmation Agent ');
	 END IF;
	END IF;
	----Ecran
	IF NOT EXISTS (select * from ecrans where nom_ecran = 'Mua-2') THEN
	 INSERT INTO ecrans(nom_ecran, fichier, nom_menu, fonction)
	 VALUES ('Mua-2', 'modules/agency_banking/utilisateur_agent.php', 'Mua-2', 752);
	END IF;



	---Menu Supression agent
	IF NOT EXISTS (select * from menus where nom_menu = 'Sua') THEN
	 id_str_trad := maketraductionlangsyst('Supression utilisateur agent');
	 INSERT INTO menus (nom_menu, libel_menu, nom_pere, pos_hierarch, ordre, is_menu,fonction, is_cliquable)
	 VALUES ('Sua', id_str_trad, 'Aga', 4, 4, TRUE, 752, FALSE);
	 IF EXISTS(SELECT langue FROM adsys_langues_systeme WHERE code = 'en_GB') THEN
	  INSERT INTO ad_traductions (id_str, langue, traduction) VALUES (id_str_trad, 'en_GB', 'Delete agent user');
	 END IF;
	END IF;

	---Menu consultation details agent
	IF NOT EXISTS (select * from menus where nom_menu = 'Sua-1') THEN
	 id_str_trad := maketraductionlangsyst('Demande confirmation de supression');
	 INSERT INTO menus (nom_menu, libel_menu, nom_pere, pos_hierarch, ordre, is_menu, is_cliquable)
	 VALUES ('Sua-1', id_str_trad, 'Sua', 5, 1, FALSE,  FALSE);
	 IF EXISTS(SELECT langue FROM adsys_langues_systeme WHERE code = 'en_GB') THEN
	  INSERT INTO ad_traductions (id_str, langue, traduction) VALUES (id_str_trad, 'en_GB', 'Confirmation request agent');
	 END IF;
	END IF;
	----Ecran
	IF NOT EXISTS (select * from ecrans where nom_ecran = 'Sua-1') THEN
	 INSERT INTO ecrans(nom_ecran, fichier, nom_menu, fonction)
	 VALUES ('Sua-1', 'modules/agency_banking/utilisateur_agent.php', 'Sua-1', 752);
	END IF;

	----Menu consultation login
	IF NOT EXISTS (select * from menus where nom_menu = 'Sua-2') THEN
	 id_str_trad := maketraductionlangsyst('Confirmation de suppression');
	 INSERT INTO menus (nom_menu, libel_menu, nom_pere, pos_hierarch, ordre, is_menu, is_cliquable)
	 VALUES ('Sua-2', id_str_trad, 'Sua', 5, 2, FALSE,  FALSE);
	 IF EXISTS(SELECT langue FROM adsys_langues_systeme WHERE code = 'en_GB') THEN
	  INSERT INTO ad_traductions (id_str, langue, traduction) VALUES (id_str_trad, 'en_GB', 'Confirmation Delete');
	 END IF;
	END IF;
	----Ecran
	IF NOT EXISTS (select * from ecrans where nom_ecran = 'Sua-2') THEN
	 INSERT INTO ecrans(nom_ecran, fichier, nom_menu, fonction)
	 VALUES ('Sua-2', 'modules/agency_banking/utilisateur_agent.php', 'Sua-2', 752);
	END IF;



	---Menu liste des codes agent
	IF NOT EXISTS (select * from menus where nom_menu = 'Lua') THEN
	 id_str_trad := maketraductionlangsyst('Liste des utilisateurs agents ');
	 INSERT INTO menus (nom_menu, libel_menu, nom_pere, pos_hierarch, ordre, is_menu,fonction, is_cliquable)
	 VALUES ('Lua', id_str_trad, 'Aga', 4, 5, TRUE, 752, FALSE);
	 IF EXISTS(SELECT langue FROM adsys_langues_systeme WHERE code = 'en_GB') THEN
	  INSERT INTO ad_traductions (id_str, langue, traduction) VALUES (id_str_trad, 'en_GB', 'List of agent user ');
	 END IF;
	END IF;

	---Menu consultation details agent
	IF NOT EXISTS (select * from menus where nom_menu = 'Lua-1') THEN
	 id_str_trad := maketraductionlangsyst('Liste des utilisateurs agents');
	 INSERT INTO menus (nom_menu, libel_menu, nom_pere, pos_hierarch, ordre, is_menu, is_cliquable)
	 VALUES ('Lua-1', id_str_trad, 'Lua', 5, 1, FALSE,  FALSE);
	 IF EXISTS(SELECT langue FROM adsys_langues_systeme WHERE code = 'en_GB') THEN
	  INSERT INTO ad_traductions (id_str, langue, traduction) VALUES (id_str_trad, 'en_GB', 'List of user agent ');
	 END IF;
	END IF;
	----Ecran
	IF NOT EXISTS (select * from ecrans where nom_ecran = 'Lua-1') THEN
	 INSERT INTO ecrans(nom_ecran, fichier, nom_menu, fonction)
	 VALUES ('Lua-1', 'modules/agency_banking/utilisateur_agent.php', 'Lua-1', 752);
	END IF;

	----------------------------------------------------------------------------------------------------------------------------------------------

	-- Gestion des codes utilisateurs agents
			--========> Debut Gestions des utilisateurs agents
	--Main Menu
	IF NOT EXISTS (select * from menus where nom_menu = 'Gla') THEN
	 id_str_trad := maketraductionlangsyst('Gestion des codes utilisateurs agents');
	 INSERT INTO menus (nom_menu, libel_menu, nom_pere, pos_hierarch, ordre, is_menu,fonction, is_cliquable)
	 VALUES ('Gla', id_str_trad, 'Gen-16', 3, 2, TRUE, 753, FALSE);
	 IF EXISTS(SELECT langue FROM adsys_langues_systeme WHERE code = 'en_GB') THEN
	  INSERT INTO ad_traductions (id_str, langue, traduction) VALUES (id_str_trad, 'en_GB', 'Managing agent logins');
	 END IF;
	END IF;

	----Menu ajout nouvelle utilisateurs agents
	----Ecran
	IF NOT EXISTS (select * from ecrans where nom_ecran = 'Gla-1') THEN
	 INSERT INTO ecrans(nom_ecran, fichier, nom_menu, fonction)
	 VALUES ('Gla-1', 'modules/agency_banking/logins_agent.php', 'Gla', 753);
	END IF;


		---Menu Ajout code utilisateur Agent
	IF NOT EXISTS (select * from menus where nom_menu = 'Ala') THEN
	 id_str_trad := maketraductionlangsyst('Ajout code agent');
	 INSERT INTO menus (nom_menu, libel_menu, nom_pere, pos_hierarch, ordre, is_menu,fonction, is_cliquable)
	 VALUES ('Ala', id_str_trad, 'Gla', 4, 1, TRUE, 753, FALSE);
	 IF EXISTS(SELECT langue FROM adsys_langues_systeme WHERE code = 'en_GB') THEN
	  INSERT INTO ad_traductions (id_str, langue, traduction) VALUES (id_str_trad, 'en_GB', 'Add agent code');
	 END IF;
	END IF;

	---Menu consultation details agent
	IF NOT EXISTS (select * from menus where nom_menu = 'Ala-1') THEN
	 id_str_trad := maketraductionlangsyst(' Information code ');
	 INSERT INTO menus (nom_menu, libel_menu, nom_pere, pos_hierarch, ordre, is_menu, is_cliquable)
	 VALUES ('Ala-1', id_str_trad, 'Ala', 5, 1, FALSE,  FALSE);
	 IF EXISTS(SELECT langue FROM adsys_langues_systeme WHERE code = 'en_GB') THEN
	  INSERT INTO ad_traductions (id_str, langue, traduction) VALUES (id_str_trad, 'en_GB', ' Information code ');
	 END IF;
	END IF;
	----Ecran
	IF NOT EXISTS (select * from ecrans where nom_ecran = 'Ala-1') THEN
	 INSERT INTO ecrans(nom_ecran, fichier, nom_menu, fonction)
	 VALUES ('Ala-1', 'modules/agency_banking/logins_agent.php', 'Ala-1', 753);
	END IF;

	----Menu consultation login
	IF NOT EXISTS (select * from menus where nom_menu = 'Ala-2') THEN
	 id_str_trad := maketraductionlangsyst('Confirmation ajout code');
	 INSERT INTO menus (nom_menu, libel_menu, nom_pere, pos_hierarch, ordre, is_menu, is_cliquable)
	 VALUES ('Ala-2', id_str_trad, 'Ala', 5, 2, FALSE,  FALSE);
	 IF EXISTS(SELECT langue FROM adsys_langues_systeme WHERE code = 'en_GB') THEN
	  INSERT INTO ad_traductions (id_str, langue, traduction) VALUES (id_str_trad, 'en_GB', 'Confirmation add code');
	 END IF;
	END IF;
	----Ecran
	IF NOT EXISTS (select * from ecrans where nom_ecran = 'Ala-2') THEN
	 INSERT INTO ecrans(nom_ecran, fichier, nom_menu, fonction)
	 VALUES ('Ala-2', 'modules/agency_banking/logins_agent.php', 'Ala-2', 753);
	END IF;

			---Menu Consultation code utilisateur Agent
	IF NOT EXISTS (select * from menus where nom_menu = 'Cla') THEN
	 id_str_trad := maketraductionlangsyst('Consultation code agent');
	 INSERT INTO menus (nom_menu, libel_menu, nom_pere, pos_hierarch, ordre, is_menu,fonction, is_cliquable)
	 VALUES ('Cla', id_str_trad, 'Gla', 4, 2, TRUE, 753, FALSE);
	 IF EXISTS(SELECT langue FROM adsys_langues_systeme WHERE code = 'en_GB') THEN
	  INSERT INTO ad_traductions (id_str, langue, traduction) VALUES (id_str_trad, 'en_GB', 'Add agent code');
	 END IF;
	END IF;

	---Menu consultation code details agent
	IF NOT EXISTS (select * from menus where nom_menu = 'Cla-1') THEN
	 id_str_trad := maketraductionlangsyst('Information code');
	 INSERT INTO menus (nom_menu, libel_menu, nom_pere, pos_hierarch, ordre, is_menu, is_cliquable)
	 VALUES ('Cla-1', id_str_trad, 'Cla', 5, 1, FALSE,  FALSE);
	 IF EXISTS(SELECT langue FROM adsys_langues_systeme WHERE code = 'en_GB') THEN
	  INSERT INTO ad_traductions (id_str, langue, traduction) VALUES (id_str_trad, 'en_GB', 'Information code');
	 END IF;
	END IF;
	----Ecran
	IF NOT EXISTS (select * from ecrans where nom_ecran = 'Cla-1') THEN
	 INSERT INTO ecrans(nom_ecran, fichier, nom_menu, fonction)
	 VALUES ('Cla-1', 'modules/agency_banking/logins_agent.php', 'Cla-1', 753);
	END IF;



				---Menu Modification code utilisateur Agent
	IF NOT EXISTS (select * from menus where nom_menu = 'Mla') THEN
	 id_str_trad := maketraductionlangsyst('Modification code agent');
	 INSERT INTO menus (nom_menu, libel_menu, nom_pere, pos_hierarch, ordre, is_menu,fonction, is_cliquable)
	 VALUES ('Mla', id_str_trad, 'Gla', 4, 3, TRUE, 753, FALSE);
	 IF EXISTS(SELECT langue FROM adsys_langues_systeme WHERE code = 'en_GB') THEN
	  INSERT INTO ad_traductions (id_str, langue, traduction) VALUES (id_str_trad, 'en_GB', 'Modify agent code');
	 END IF;
	END IF;

	---Menu saisie modification code details agent
	IF NOT EXISTS (select * from menus where nom_menu = 'Mla-1') THEN
	 id_str_trad := maketraductionlangsyst('Saisie modification agent');
	 INSERT INTO menus (nom_menu, libel_menu, nom_pere, pos_hierarch, ordre, is_menu, is_cliquable)
	 VALUES ('Mla-1', id_str_trad, 'Mla', 5, 1, FALSE,  FALSE);
	 IF EXISTS(SELECT langue FROM adsys_langues_systeme WHERE code = 'en_GB') THEN
	  INSERT INTO ad_traductions (id_str, langue, traduction) VALUES (id_str_trad, 'en_GB', '"Modification agent entry"');
	 END IF;
	END IF;
	----Ecran
	IF NOT EXISTS (select * from ecrans where nom_ecran = 'Mla-1') THEN
	 INSERT INTO ecrans(nom_ecran, fichier, nom_menu, fonction)
	 VALUES ('Mla-1', 'modules/agency_banking/logins_agent.php', 'Mla-1', 753);
	END IF;

		---Menu confirmation code details agent
	IF NOT EXISTS (select * from menus where nom_menu = 'Mla-2') THEN
	 id_str_trad := maketraductionlangsyst('Confirmation agent');
	 INSERT INTO menus (nom_menu, libel_menu, nom_pere, pos_hierarch, ordre, is_menu, is_cliquable)
	 VALUES ('Mla-2', id_str_trad, 'Mla', 5, 2, FALSE,  FALSE);
	 IF EXISTS(SELECT langue FROM adsys_langues_systeme WHERE code = 'en_GB') THEN
	  INSERT INTO ad_traductions (id_str, langue, traduction) VALUES (id_str_trad, 'en_GB', '"Confirm agent entry"');
	 END IF;
	END IF;
	----Ecran
	IF NOT EXISTS (select * from ecrans where nom_ecran = 'Mla-2') THEN
	 INSERT INTO ecrans(nom_ecran, fichier, nom_menu, fonction)
	 VALUES ('Mla-2', 'modules/agency_banking/logins_agent.php', 'Mla-2', 753);
	END IF;



	---Menu Supression code utilisateur Agent
	IF NOT EXISTS (select * from menus where nom_menu = 'Sla') THEN
	 id_str_trad := maketraductionlangsyst('Suppression code agent');
	 INSERT INTO menus (nom_menu, libel_menu, nom_pere, pos_hierarch, ordre, is_menu,fonction, is_cliquable)
	 VALUES ('Sla', id_str_trad, 'Gla', 4, 4, TRUE, 753, FALSE);
	 IF EXISTS(SELECT langue FROM adsys_langues_systeme WHERE code = 'en_GB') THEN
	  INSERT INTO ad_traductions (id_str, langue, traduction) VALUES (id_str_trad, 'en_GB', 'Delete agent code');
	 END IF;
	END IF;

	---Menu saisie supression code details agent
	IF NOT EXISTS (select * from menus where nom_menu = 'Sla-1') THEN
	 id_str_trad := maketraductionlangsyst('Demande supression code agent');
	 INSERT INTO menus (nom_menu, libel_menu, nom_pere, pos_hierarch, ordre, is_menu, is_cliquable)
	 VALUES ('Sla-1', id_str_trad, 'Sla', 5, 1, FALSE,  FALSE);
	 IF EXISTS(SELECT langue FROM adsys_langues_systeme WHERE code = 'en_GB') THEN
	  INSERT INTO ad_traductions (id_str, langue, traduction) VALUES (id_str_trad, 'en_GB', '"Delete agent user"');
	 END IF;
	END IF;
	----Ecran
	IF NOT EXISTS (select * from ecrans where nom_ecran = 'Sla-1') THEN
	 INSERT INTO ecrans(nom_ecran, fichier, nom_menu, fonction)
	 VALUES ('Sla-1', 'modules/agency_banking/logins_agent.php', 'Sla-1', 753);
	END IF;

		---Menu confirmation de supression code details agent
	IF NOT EXISTS (select * from menus where nom_menu = 'Sla-2') THEN
	 id_str_trad := maketraductionlangsyst('Confirmation de supression code agent');
	 INSERT INTO menus (nom_menu, libel_menu, nom_pere, pos_hierarch, ordre, is_menu, is_cliquable)
	 VALUES ('Sla-2', id_str_trad, 'Sla', 5, 2, FALSE,  FALSE);
	 IF EXISTS(SELECT langue FROM adsys_langues_systeme WHERE code = 'en_GB') THEN
	  INSERT INTO ad_traductions (id_str, langue, traduction) VALUES (id_str_trad, 'en_GB', '"Confirm agent user deleted"');
	 END IF;
	END IF;
	----Ecran
	IF NOT EXISTS (select * from ecrans where nom_ecran = 'Sla-2') THEN
	 INSERT INTO ecrans(nom_ecran, fichier, nom_menu, fonction)
	 VALUES ('Sla-2', 'modules/agency_banking/logins_agent.php', 'Sla-2', 753);
	END IF;

		--========> Menu de parametrage
	--Main Menu
	IF NOT EXISTS (select * from menus where nom_menu = 'Mpa') THEN
	 id_str_trad := maketraductionlangsyst('Paramétrage Agency Banking');
	 INSERT INTO menus (nom_menu, libel_menu, nom_pere, pos_hierarch, ordre, is_menu,fonction, is_cliquable)
	 VALUES ('Mpa', id_str_trad, 'Gen-16', 3, 3, TRUE, 760, TRUE);
	 IF EXISTS(SELECT langue FROM adsys_langues_systeme WHERE code = 'en_GB') THEN
	  INSERT INTO ad_traductions (id_str, langue, traduction) VALUES (id_str_trad, 'en_GB', 'Agency Banking Setup');
	 END IF;
	END IF;
		IF NOT EXISTS (select * from ecrans where nom_ecran = 'Mpa-1') THEN
	 INSERT INTO ecrans(nom_ecran, fichier, nom_menu, fonction)
	 VALUES ('Mpa-1', 'modules/agency_banking/parametrage_agency_banking.php', 'Mpa', 760);
	END IF;


			--========> Menu de parametrage des approvisionnement/transfert compte de flotte
	--Main Menu
		IF NOT EXISTS (select * from menus where nom_menu = 'Atb') THEN
	 id_str_trad := maketraductionlangsyst('Paramétrage approvisionnement/transfert');
	 INSERT INTO menus (nom_menu, libel_menu, nom_pere, pos_hierarch, ordre, is_menu,fonction, is_cliquable)
	 VALUES ('Atb', id_str_trad, 'Mpa', 4, 3, TRUE, 761, TRUE);
	 IF EXISTS(SELECT langue FROM adsys_langues_systeme WHERE code = 'en_GB') THEN
	  INSERT INTO ad_traductions (id_str, langue, traduction) VALUES (id_str_trad, 'en_GB', 'Provision/Offloading setup');
	 END IF;
	END IF;



	IF NOT EXISTS (select * from menus where nom_menu = 'Atb-1') THEN
	 id_str_trad := maketraductionlangsyst('Saisie information ');
	 INSERT INTO menus (nom_menu, libel_menu, nom_pere, pos_hierarch, ordre, is_menu,fonction, is_cliquable)
	 VALUES ('Atb-1', id_str_trad, 'Atb', 5, 1, TRUE, 761, FALSE);
	 IF EXISTS(SELECT langue FROM adsys_langues_systeme WHERE code = 'en_GB') THEN
	  INSERT INTO ad_traductions (id_str, langue, traduction) VALUES (id_str_trad, 'en_GB', 'Provision/Offloading entry');
	 END IF;
	END IF;

		IF NOT EXISTS (select * from ecrans where nom_ecran = 'Atb-1') THEN
	 INSERT INTO ecrans(nom_ecran, fichier, nom_menu, fonction)
	 VALUES ('Atb-1', 'modules/agency_banking/parametrage_agency_banking.php', 'Atb-1', 761);
	END IF;

	IF NOT EXISTS (select * from menus where nom_menu = 'Atb-2') THEN
	 id_str_trad := maketraductionlangsyst('Confirmation information ');
	 INSERT INTO menus (nom_menu, libel_menu, nom_pere, pos_hierarch, ordre, is_menu,fonction, is_cliquable)
	 VALUES ('Atb-2', id_str_trad, 'Atb', 5, 2, TRUE, 761, FALSE);
	 IF EXISTS(SELECT langue FROM adsys_langues_systeme WHERE code = 'en_GB') THEN
	  INSERT INTO ad_traductions (id_str, langue, traduction) VALUES (id_str_trad, 'en_GB', 'Provision/Offloading confirmation');
	 END IF;
	END IF;

	IF NOT EXISTS (select * from ecrans where nom_ecran = 'Atb-2') THEN
	 INSERT INTO ecrans(nom_ecran, fichier, nom_menu, fonction)
	 VALUES ('Atb-2', 'modules/agency_banking/parametrage_agency_banking.php', 'Atb-2', 761);
	END IF;



	--========> Debut Paramétrage des commissions
	--Main Menu
	IF NOT EXISTS (select * from menus where nom_menu = 'Pag') THEN
	 id_str_trad := maketraductionlangsyst('Paramétrage des commissions');
	 INSERT INTO menus (nom_menu, libel_menu, nom_pere, pos_hierarch, ordre, is_menu,fonction, is_cliquable)
	 VALUES ('Pag', id_str_trad, 'Mpa', 4, 1, TRUE, 754, FALSE);
	 IF EXISTS(SELECT langue FROM adsys_langues_systeme WHERE code = 'en_GB') THEN
	  INSERT INTO ad_traductions (id_str, langue, traduction) VALUES (id_str_trad, 'en_GB', 'Commission Setup');
	 END IF;
	END IF;

	----Menu liste de type de commissions
	--menu
	IF NOT EXISTS (select * from menus where nom_menu = 'Pag-1') THEN
	 id_str_trad := maketraductionlangsyst('Type de commissions');
	 INSERT INTO menus (nom_menu, libel_menu, nom_pere, pos_hierarch, ordre, is_menu,fonction, is_cliquable)
	 VALUES ('Pag-1', id_str_trad, 'Pag', 5, 1, FALSE, 754, TRUE);
	 IF EXISTS(SELECT langue FROM adsys_langues_systeme WHERE code = 'en_GB') THEN
	  INSERT INTO ad_traductions (id_str, langue, traduction) VALUES (id_str_trad, 'en_GB', 'Type of commission');
	 END IF;
	END IF;
	----Ecran
	IF NOT EXISTS (select * from ecrans where nom_ecran = 'Pag-1') THEN
	 INSERT INTO ecrans(nom_ecran, fichier, nom_menu, fonction)
	 VALUES ('Pag-1', 'modules/agency_banking/parametrage_commission.php', 'Pag-1', 754);
	END IF;

		----Menu Saisie des details
	--menu
	IF NOT EXISTS (select * from menus where nom_menu = 'Pag-2') THEN
	 id_str_trad := maketraductionlangsyst('Saisie des informations de commissions');
	 INSERT INTO menus (nom_menu, libel_menu, nom_pere, pos_hierarch, ordre, is_menu,fonction, is_cliquable)
	 VALUES ('Pag-2', id_str_trad, 'Pag', 5, 2, FALSE, 754, TRUE);
	 IF EXISTS(SELECT langue FROM adsys_langues_systeme WHERE code = 'en_GB') THEN
	  INSERT INTO ad_traductions (id_str, langue, traduction) VALUES (id_str_trad, 'en_GB', 'Commission details entry');
	 END IF;
	END IF;
	----Ecran
	IF NOT EXISTS (select * from ecrans where nom_ecran = 'Pag-2') THEN
	 INSERT INTO ecrans(nom_ecran, fichier, nom_menu, fonction)
	 VALUES ('Pag-2', 'modules/agency_banking/parametrage_commission.php', 'Pag-2', 754);
	END IF;



		----Menu validation des donnees
	--menu
	IF NOT EXISTS (select * from menus where nom_menu = 'Pag-3') THEN
	 id_str_trad := maketraductionlangsyst('Valider les données');
	 INSERT INTO menus (nom_menu, libel_menu, nom_pere, pos_hierarch, ordre, is_menu,fonction, is_cliquable)
	 VALUES ('Pag-3', id_str_trad, 'Pag', 5, 4, FALSE, 754, TRUE);
	 IF EXISTS(SELECT langue FROM adsys_langues_systeme WHERE code = 'en_GB') THEN
	  INSERT INTO ad_traductions (id_str, langue, traduction) VALUES (id_str_trad, 'en_GB', 'Validate entry');
	 END IF;
	END IF;
	----Ecran
	IF NOT EXISTS (select * from ecrans where nom_ecran = 'Pag-3') THEN
	 INSERT INTO ecrans(nom_ecran, fichier, nom_menu, fonction)
	 VALUES ('Pag-3', 'modules/agency_banking/parametrage_commission.php', 'Pag-3', 754);
	END IF;

				----Menu validation des donnees
	--menu
	IF NOT EXISTS (select * from menus where nom_menu = 'Pag-4') THEN
	 id_str_trad := maketraductionlangsyst('Modification les données');
	 INSERT INTO menus (nom_menu, libel_menu, nom_pere, pos_hierarch, ordre, is_menu,fonction, is_cliquable)
	 VALUES ('Pag-4', id_str_trad, 'Pag', 5, 3, FALSE, 754, TRUE);
	 IF EXISTS(SELECT langue FROM adsys_langues_systeme WHERE code = 'en_GB') THEN
	  INSERT INTO ad_traductions (id_str, langue, traduction) VALUES (id_str_trad, 'en_GB', 'Modify data');
	 END IF;
	END IF;
	----Ecran
	IF NOT EXISTS (select * from ecrans where nom_ecran = 'Pag-4') THEN
	 INSERT INTO ecrans(nom_ecran, fichier, nom_menu, fonction)
	 VALUES ('Pag-4', 'modules/agency_banking/parametrage_commission.php', 'Pag-4', 754);
	END IF;



	--========> Menu compte de param des commission de l'institution
	--Main Menu
	IF NOT EXISTS (select * from menus where nom_menu = 'Cci') THEN
	 id_str_trad := maketraductionlangsyst('Paramétrage des commissions pour l institution');
	 INSERT INTO menus (nom_menu, libel_menu, nom_pere, pos_hierarch, ordre, is_menu,fonction, is_cliquable)
	 VALUES ('Cci', id_str_trad, 'Mpa', 4, 2, TRUE, 755, TRUE);
	 IF EXISTS(SELECT langue FROM adsys_langues_systeme WHERE code = 'en_GB') THEN
	  INSERT INTO ad_traductions (id_str, langue, traduction) VALUES (id_str_trad, 'en_GB', 'Agency Commission Setup');
	 END IF;
	END IF;

	----Menu Saisie information pour les commission de l'institution
	--menu
	IF NOT EXISTS (select * from menus where nom_menu = 'Cci-1') THEN
	 id_str_trad := maketraductionlangsyst('Saisie information de commissions pour institution');
	 INSERT INTO menus (nom_menu, libel_menu, nom_pere, pos_hierarch, ordre, is_menu,fonction, is_cliquable)
	 VALUES ('Cci-1', id_str_trad, 'Cci', 5, 1, FALSE, 755, FALSE);
	 IF EXISTS(SELECT langue FROM adsys_langues_systeme WHERE code = 'en_GB') THEN
	  INSERT INTO ad_traductions (id_str, langue, traduction) VALUES (id_str_trad, 'en_GB', 'Agency commission details entry');
	 END IF;
	END IF;
	----Ecran
	IF NOT EXISTS (select * from ecrans where nom_ecran = 'Cci-1') THEN
	 INSERT INTO ecrans(nom_ecran, fichier, nom_menu, fonction)
	 VALUES ('Cci-1', 'modules/agency_banking/parametrage_commission_institution.php', 'Cci-1', 755);
	END IF;

		----Validation des commissions pour l'institution
	--menu
	IF NOT EXISTS (select * from menus where nom_menu = 'Cci-2') THEN
	 id_str_trad := maketraductionlangsyst('Validation information de commissions pour institution');
	 INSERT INTO menus (nom_menu, libel_menu, nom_pere, pos_hierarch, ordre, is_menu,fonction, is_cliquable)
	 VALUES ('Cci-2', id_str_trad, 'Cci', 5, 2, FALSE, 755, FALSE);
	 IF EXISTS(SELECT langue FROM adsys_langues_systeme WHERE code = 'en_GB') THEN
	  INSERT INTO ad_traductions (id_str, langue, traduction) VALUES (id_str_trad, 'en_GB', 'Agency commission details validation');
	 END IF;
	END IF;
	----Ecran
	IF NOT EXISTS (select * from ecrans where nom_ecran = 'Cci-2') THEN
	 INSERT INTO ecrans(nom_ecran, fichier, nom_menu, fonction)
	 VALUES ('Cci-2', 'modules/agency_banking/parametrage_commission_institution.php', 'Cci-2', 755);
	END IF;

	--========> Menu approvisionnement agent
	--Main Menu
	IF NOT EXISTS (select * from menus where nom_menu = 'Atg') THEN
	 id_str_trad := maketraductionlangsyst('Demande approvisionnement des agents');
	 INSERT INTO menus (nom_menu, libel_menu, nom_pere, pos_hierarch, ordre, is_menu,fonction, is_cliquable)
	 VALUES ('Atg', id_str_trad, 'Gen-16', 3, 5, TRUE, 756, TRUE);
	 IF EXISTS(SELECT langue FROM adsys_langues_systeme WHERE code = 'en_GB') THEN
	  INSERT INTO ad_traductions (id_str, langue, traduction) VALUES (id_str_trad, 'en_GB', 'Agent provision');
	 END IF;
	END IF;

	----Type approvisionnement agent
	--menu
	IF NOT EXISTS (select * from menus where nom_menu = 'Atg-1') THEN
	 id_str_trad := maketraductionlangsyst('Type approvisionnement agent');
	 INSERT INTO menus (nom_menu, libel_menu, nom_pere, pos_hierarch, ordre, is_menu,fonction, is_cliquable)
	 VALUES ('Atg-1', id_str_trad, 'Atg', 4, 1, FALSE, 756, FALSE);
	 IF EXISTS(SELECT langue FROM adsys_langues_systeme WHERE code = 'en_GB') THEN
	  INSERT INTO ad_traductions (id_str, langue, traduction) VALUES (id_str_trad, 'en_GB', 'Type of agent provision');
	 END IF;
	END IF;
	----Ecran
	IF NOT EXISTS (select * from ecrans where nom_ecran = 'Atg-1') THEN
	 INSERT INTO ecrans(nom_ecran, fichier, nom_menu, fonction)
	 VALUES ('Atg-1', 'modules/agency_banking/approvisionnement_transfert_agent.php', 'Atg-1', 756);
	END IF;

	----Saisie information d approvisionnement agent
	--menu
	IF NOT EXISTS (select * from menus where nom_menu = 'Atg-2') THEN
	 id_str_trad := maketraductionlangsyst('Saisie information d approvisionnement agent');
	 INSERT INTO menus (nom_menu, libel_menu, nom_pere, pos_hierarch, ordre, is_menu,fonction, is_cliquable)
	 VALUES ('Atg-2', id_str_trad, 'Atg', 4, 2, FALSE, 756, FALSE);
	 IF EXISTS(SELECT langue FROM adsys_langues_systeme WHERE code = 'en_GB') THEN
	  INSERT INTO ad_traductions (id_str, langue, traduction) VALUES (id_str_trad, 'en_GB', 'Agent provision entry');
	 END IF;
	END IF;
	----Ecran
	IF NOT EXISTS (select * from ecrans where nom_ecran = 'Atg-2') THEN
	 INSERT INTO ecrans(nom_ecran, fichier, nom_menu, fonction)
	 VALUES ('Atg-2', 'modules/agency_banking/approvisionnement_transfert_agent.php', 'Atg-2', 756);
	END IF;

		----Verification information approvisionnement agent
	--menu
	IF NOT EXISTS (select * from menus where nom_menu = 'Atg-3') THEN
	 id_str_trad := maketraductionlangsyst('Validation information approvisionnement agent');
	 INSERT INTO menus (nom_menu, libel_menu, nom_pere, pos_hierarch, ordre, is_menu,fonction, is_cliquable)
	 VALUES ('Atg-3', id_str_trad, 'Atg', 4, 3, FALSE, 756, FALSE);
	 IF EXISTS(SELECT langue FROM adsys_langues_systeme WHERE code = 'en_GB') THEN
	  INSERT INTO ad_traductions (id_str, langue, traduction) VALUES (id_str_trad, 'en_GB', 'Agent provision validation');
	 END IF;
	END IF;
	----Ecran
	IF NOT EXISTS (select * from ecrans where nom_ecran = 'Atg-3') THEN
	 INSERT INTO ecrans(nom_ecran, fichier, nom_menu, fonction)
	 VALUES ('Atg-3', 'modules/agency_banking/approvisionnement_transfert_agent.php', 'Atg-3', 756);
	END IF;

	----Validation direct appro
	--menu
	IF NOT EXISTS (select * from menus where nom_menu = 'Atg-4') THEN
	 id_str_trad := maketraductionlangsyst('Approvisionnement/Transfert direct');
	 INSERT INTO menus (nom_menu, libel_menu, nom_pere, pos_hierarch, ordre, is_menu,fonction, is_cliquable)
	 VALUES ('Atg-4', id_str_trad, 'Atg', 4, 4, FALSE, 756, FALSE);
	 IF EXISTS(SELECT langue FROM adsys_langues_systeme WHERE code = 'en_GB') THEN
	  INSERT INTO ad_traductions (id_str, langue, traduction) VALUES (id_str_trad, 'en_GB', 'Direct agent provision/transfert');
	 END IF;
	END IF;
	----Ecran
	IF NOT EXISTS (select * from ecrans where nom_ecran = 'Atg-4') THEN
	 INSERT INTO ecrans(nom_ecran, fichier, nom_menu, fonction)
	 VALUES ('Atg-4', 'modules/agency_banking/approvisionnement_transfert_agent.php', 'Atg-4', 756);
	END IF;


	--========> Menu Verification  des demandes approvisionnement agent
	--Main Menu
	IF NOT EXISTS (select * from menus where nom_menu = 'Vda') THEN
	 id_str_trad := maketraductionlangsyst('Verification des demandes approvisionnements');
	 INSERT INTO menus (nom_menu, libel_menu, nom_pere, pos_hierarch, ordre, is_menu,fonction, is_cliquable)
	 VALUES ('Vda', id_str_trad, 'Gen-16', 3, 6, TRUE, 757, TRUE);
	 IF EXISTS(SELECT langue FROM adsys_langues_systeme WHERE code = 'en_GB') THEN
	  INSERT INTO ad_traductions (id_str, langue, traduction) VALUES (id_str_trad, 'en_GB', 'Verification of Agent provision');
	 END IF;
	END IF;

	----Liste des demandes approvisionnement agent
	--menu
	IF NOT EXISTS (select * from menus where nom_menu = 'Vda-1') THEN
	 id_str_trad := maketraductionlangsyst(' Liste des demandes ');
	 INSERT INTO menus (nom_menu, libel_menu, nom_pere, pos_hierarch, ordre, is_menu,fonction, is_cliquable)
	 VALUES ('Vda-1', id_str_trad, 'Vda', 4, 1, FALSE, 757, FALSE);
	 IF EXISTS(SELECT langue FROM adsys_langues_systeme WHERE code = 'en_GB') THEN
	  INSERT INTO ad_traductions (id_str, langue, traduction) VALUES (id_str_trad, 'en_GB', ' List of request ');
	 END IF;
	END IF;
	----Ecran
	IF NOT EXISTS (select * from ecrans where nom_ecran = 'Vda-1') THEN
	 INSERT INTO ecrans(nom_ecran, fichier, nom_menu, fonction)
	 VALUES ('Vda-1', 'modules/agency_banking/approvisionnement_transfert_agent.php', 'Vda-1', 757);
	END IF;


		----Verification des demandes approvisionnement agent
	--menu
	IF NOT EXISTS (select * from menus where nom_menu = 'Vda-2') THEN
	 id_str_trad := maketraductionlangsyst(' Verification des demandes ');
	 INSERT INTO menus (nom_menu, libel_menu, nom_pere, pos_hierarch, ordre, is_menu,fonction, is_cliquable)
	 VALUES ('Vda-2', id_str_trad, 'Vda', 4, 2, FALSE, 757, FALSE);
	 IF EXISTS(SELECT langue FROM adsys_langues_systeme WHERE code = 'en_GB') THEN
	  INSERT INTO ad_traductions (id_str, langue, traduction) VALUES (id_str_trad, 'en_GB', ' Verification of request ');
	 END IF;
	END IF;
	----Ecran
	IF NOT EXISTS (select * from ecrans where nom_ecran = 'Vda-2') THEN
	 INSERT INTO ecrans(nom_ecran, fichier, nom_menu, fonction)
	 VALUES ('Vda-2', 'modules/agency_banking/approvisionnement_transfert_agent.php', 'Vda-2', 757);
	END IF;

			----Validation des demandes approvisionnement agent
	--menu
	IF NOT EXISTS (select * from menus where nom_menu = 'Vda-3') THEN
	 id_str_trad := maketraductionlangsyst(' Validation des demandes ');
	 INSERT INTO menus (nom_menu, libel_menu, nom_pere, pos_hierarch, ordre, is_menu,fonction, is_cliquable)
	 VALUES ('Vda-3', id_str_trad, 'Vda', 4, 3, FALSE, 757, FALSE);
	 IF EXISTS(SELECT langue FROM adsys_langues_systeme WHERE code = 'en_GB') THEN
	  INSERT INTO ad_traductions (id_str, langue, traduction) VALUES (id_str_trad, 'en_GB', ' Validation of request ');
	 END IF;
	END IF;
	----Ecran
	IF NOT EXISTS (select * from ecrans where nom_ecran = 'Vda-3') THEN
	 INSERT INTO ecrans(nom_ecran, fichier, nom_menu, fonction)
	 VALUES ('Vda-3', 'modules/agency_banking/approvisionnement_transfert_agent.php', 'Vda-3', 757);
	END IF;


		--========> Menu approbation des demandes approvisionnement/transfert agent
	--Main Menu
	IF NOT EXISTS (select * from menus where nom_menu = 'Vdp') THEN
	 id_str_trad := maketraductionlangsyst('Approbation  des demandes approvisionnements');
	 INSERT INTO menus (nom_menu, libel_menu, nom_pere, pos_hierarch, ordre, is_menu,fonction, is_cliquable)
	 VALUES ('Vdp', id_str_trad, 'Gen-16', 3, 7, TRUE, 758, TRUE);
	 IF EXISTS(SELECT langue FROM adsys_langues_systeme WHERE code = 'en_GB') THEN
	  INSERT INTO ad_traductions (id_str, langue, traduction) VALUES (id_str_trad, 'en_GB', 'Approbation of Agent provision');
	 END IF;
	END IF;

	----Liste des demandes approvisionnement agent
	--menu
	IF NOT EXISTS (select * from menus where nom_menu = 'Vdp-1') THEN
	 id_str_trad := maketraductionlangsyst('Liste des demandes');
	 INSERT INTO menus (nom_menu, libel_menu, nom_pere, pos_hierarch, ordre, is_menu,fonction, is_cliquable)
	 VALUES ('Vdp-1', id_str_trad, 'Vdp', 4, 1, FALSE, 758, FALSE);
	 IF EXISTS(SELECT langue FROM adsys_langues_systeme WHERE code = 'en_GB') THEN
	  INSERT INTO ad_traductions (id_str, langue, traduction) VALUES (id_str_trad, 'en_GB', 'List of  request');
	 END IF;
	END IF;
	----Ecran
	IF NOT EXISTS (select * from ecrans where nom_ecran = 'Vdp-1') THEN
	 INSERT INTO ecrans(nom_ecran, fichier, nom_menu, fonction)
	 VALUES ('Vdp-1', 'modules/agency_banking/approvisionnement_transfert_agent.php', 'Vdp-1', 758);
	END IF;


		----Verification des demandes approvisionnement agent
	--menu
	IF NOT EXISTS (select * from menus where nom_menu = 'Vdp-2') THEN
	 id_str_trad := maketraductionlangsyst('Verification des demandes');
	 INSERT INTO menus (nom_menu, libel_menu, nom_pere, pos_hierarch, ordre, is_menu,fonction, is_cliquable)
	 VALUES ('Vdp-2', id_str_trad, 'Vdp', 4, 2, FALSE, 758, FALSE);
	 IF EXISTS(SELECT langue FROM adsys_langues_systeme WHERE code = 'en_GB') THEN
	  INSERT INTO ad_traductions (id_str, langue, traduction) VALUES (id_str_trad, 'en_GB', 'Verification of request');
	 END IF;
	END IF;
	----Ecran
	IF NOT EXISTS (select * from ecrans where nom_ecran = 'Vdp-2') THEN
	 INSERT INTO ecrans(nom_ecran, fichier, nom_menu, fonction)
	 VALUES ('Vdp-2', 'modules/agency_banking/approvisionnement_transfert_agent.php', 'Vdp-2', 758);
	END IF;

			----Validation des demandes approvisionnement agent
	--menu
	IF NOT EXISTS (select * from menus where nom_menu = 'Vdp-3') THEN
	 id_str_trad := maketraductionlangsyst('Validation des demandes');
	 INSERT INTO menus (nom_menu, libel_menu, nom_pere, pos_hierarch, ordre, is_menu,fonction, is_cliquable)
	 VALUES ('Vdp-3', id_str_trad, 'Vdp', 4, 3, FALSE, 758, FALSE);
	 IF EXISTS(SELECT langue FROM adsys_langues_systeme WHERE code = 'en_GB') THEN
	  INSERT INTO ad_traductions (id_str, langue, traduction) VALUES (id_str_trad, 'en_GB', 'Validation of request');
	 END IF;
	END IF;
	----Ecran
	IF NOT EXISTS (select * from ecrans where nom_ecran = 'Vdp-3') THEN
	 INSERT INTO ecrans(nom_ecran, fichier, nom_menu, fonction)
	 VALUES ('Vdp-3', 'modules/agency_banking/approvisionnement_transfert_agent.php', 'Vdp-3', 758);
	END IF;

	--------------------------------------------------------------------------------------

	--========> Menu Trannsfert du compte de flotte de l'agent
	--Main Menu
	IF NOT EXISTS (select * from menus where nom_menu = 'Tcf') THEN
	 id_str_trad := maketraductionlangsyst('Demande de transfert du compte de flote vers compte courant');
	 INSERT INTO menus (nom_menu, libel_menu, nom_pere, pos_hierarch, ordre, is_menu,fonction, is_cliquable)
	 VALUES ('Tcf', id_str_trad, 'Gen-16', 3, 8, TRUE, 759, TRUE);
	 IF EXISTS(SELECT langue FROM adsys_langues_systeme WHERE code = 'en_GB') THEN
	  INSERT INTO ad_traductions (id_str, langue, traduction) VALUES (id_str_trad, 'en_GB', 'Offloading to current account');
	 END IF;
	END IF;

	----Type approvisionnement agent
	--menu
	IF NOT EXISTS (select * from menus where nom_menu = 'Tcf-1') THEN
	 id_str_trad := maketraductionlangsyst('Saisie information transfert');
	 INSERT INTO menus (nom_menu, libel_menu, nom_pere, pos_hierarch, ordre, is_menu,fonction, is_cliquable)
	 VALUES ('Tcf-1', id_str_trad, 'Tcf', 4, 1, FALSE, 759, FALSE);
	 IF EXISTS(SELECT langue FROM adsys_langues_systeme WHERE code = 'en_GB') THEN
	  INSERT INTO ad_traductions (id_str, langue, traduction) VALUES (id_str_trad, 'en_GB', 'Offloading entry');
	 END IF;
	END IF;
	----Ecran
	IF NOT EXISTS (select * from ecrans where nom_ecran = 'Tcf-1') THEN
	 INSERT INTO ecrans(nom_ecran, fichier, nom_menu, fonction)
	 VALUES ('Tcf-1', 'modules/agency_banking/approvisionnement_transfert_agent.php', 'Tcf-1', 759);
	END IF;

	----Saisie information d approvisionnement agent
	--menu
	IF NOT EXISTS (select * from menus where nom_menu = 'Tcf-2') THEN
	 id_str_trad := maketraductionlangsyst('Validation transfert agent');
	 INSERT INTO menus (nom_menu, libel_menu, nom_pere, pos_hierarch, ordre, is_menu,fonction, is_cliquable)
	 VALUES ('Tcf-2', id_str_trad, 'Tcf', 4, 2, FALSE, 759, FALSE);
	 IF EXISTS(SELECT langue FROM adsys_langues_systeme WHERE code = 'en_GB') THEN
	  INSERT INTO ad_traductions (id_str, langue, traduction) VALUES (id_str_trad, 'en_GB', 'Agent offloading validation');
	 END IF;
	END IF;
	----Ecran
	IF NOT EXISTS (select * from ecrans where nom_ecran = 'Tcf-2') THEN
	 INSERT INTO ecrans(nom_ecran, fichier, nom_menu, fonction)
	 VALUES ('Tcf-2', 'modules/agency_banking/approvisionnement_transfert_agent.php', 'Tcf-2', 759);
	END IF;

	------------------------------------


		--========> Menu Retrait Agent
	--Main Menu
	IF NOT EXISTS (select * from menus where nom_menu = 'Rva') THEN
	 id_str_trad := maketraductionlangsyst('Retrait via agent');
	 INSERT INTO menus (nom_menu, libel_menu, nom_pere, pos_hierarch, ordre, is_menu,fonction, is_cliquable)
	 VALUES ('Rva', id_str_trad, 'Gen-16', 3, 3, TRUE, 764, TRUE);
	 IF EXISTS(SELECT langue FROM adsys_langues_systeme WHERE code = 'en_GB') THEN
	  INSERT INTO ad_traductions (id_str, langue, traduction) VALUES (id_str_trad, 'en_GB', 'Withdrawal by agent');
	 END IF;
	END IF;

	----Choix compte client
	--menu
	IF NOT EXISTS (select * from menus where nom_menu = 'Rva-1') THEN
	 id_str_trad := maketraductionlangsyst(' Choix du compte ');
	 INSERT INTO menus (nom_menu, libel_menu, nom_pere, pos_hierarch, ordre, is_menu,fonction, is_cliquable)
	 VALUES ('Rva-1', id_str_trad, 'Rva', 4, 1, FALSE, 764, FALSE);
	 IF EXISTS(SELECT langue FROM adsys_langues_systeme WHERE code = 'en_GB') THEN
	  INSERT INTO ad_traductions (id_str, langue, traduction) VALUES (id_str_trad, 'en_GB', ' Account choice ');
	 END IF;
	END IF;
	----Ecran
	IF NOT EXISTS (select * from ecrans where nom_ecran = 'Rva-1') THEN
	 INSERT INTO ecrans(nom_ecran, fichier, nom_menu, fonction)
	 VALUES ('Rva-1', 'modules/agency_banking/retrait_via_agent.php', 'Rva-1', 764);
	END IF;

	----Saisie montant
	--menu
	IF NOT EXISTS (select * from menus where nom_menu = 'Rva-2') THEN
	 id_str_trad := maketraductionlangsyst(' Saisie montant ');
	 INSERT INTO menus (nom_menu, libel_menu, nom_pere, pos_hierarch, ordre, is_menu,fonction, is_cliquable)
	 VALUES ('Rva-2', id_str_trad, 'Rva', 4, 2, FALSE, 764, FALSE);
	 IF EXISTS(SELECT langue FROM adsys_langues_systeme WHERE code = 'en_GB') THEN
	  INSERT INTO ad_traductions (id_str, langue, traduction) VALUES (id_str_trad, 'en_GB', ' Amount seizure ');
	 END IF;
	END IF;
	----Ecran
	IF NOT EXISTS (select * from ecrans where nom_ecran = 'Rva-2') THEN
	 INSERT INTO ecrans(nom_ecran, fichier, nom_menu, fonction)
	 VALUES ('Rva-2', 'modules/agency_banking/retrait_via_agent.php', 'Rva-2', 764);
	END IF;


	----Confirmation montant
	--menu
	IF NOT EXISTS (select * from menus where nom_menu = 'Rva-3') THEN
	 id_str_trad := maketraductionlangsyst(' Confirmation montant ');
	 INSERT INTO menus (nom_menu, libel_menu, nom_pere, pos_hierarch, ordre, is_menu,fonction, is_cliquable)
	 VALUES ('Rva-3', id_str_trad, 'Rva', 4, 3, FALSE, 764, FALSE);
	 IF EXISTS(SELECT langue FROM adsys_langues_systeme WHERE code = 'en_GB') THEN
	  INSERT INTO ad_traductions (id_str, langue, traduction) VALUES (id_str_trad, 'en_GB', ' mount confirmation ');
	 END IF;
	END IF;
	----Ecran
	IF NOT EXISTS (select * from ecrans where nom_ecran = 'Rva-3') THEN
	 INSERT INTO ecrans(nom_ecran, fichier, nom_menu, fonction)
	 VALUES ('Rva-3', 'modules/agency_banking/retrait_via_agent.php', 'Rva-3', 764);
	END IF;


	----Confirmation montant
	--menu
	IF NOT EXISTS (select * from menus where nom_menu = 'Rva-4') THEN
	 id_str_trad := maketraductionlangsyst(' Traitement ');
	 INSERT INTO menus (nom_menu, libel_menu, nom_pere, pos_hierarch, ordre, is_menu,fonction, is_cliquable)
	 VALUES ('Rva-4', id_str_trad, 'Rva', 4, 4, FALSE, 764, FALSE);
	 IF EXISTS(SELECT langue FROM adsys_langues_systeme WHERE code = 'en_GB') THEN
	  INSERT INTO ad_traductions (id_str, langue, traduction) VALUES (id_str_trad, 'en_GB', ' Treatment ');
	 END IF;
	END IF;
	----Ecran
	IF NOT EXISTS (select * from ecrans where nom_ecran = 'Rva-4') THEN
	 INSERT INTO ecrans(nom_ecran, fichier, nom_menu, fonction)
	 VALUES ('Rva-4', 'modules/agency_banking/retrait_via_agent.php', 'Rva-4', 764);
	END IF;


	------------------------------------ Creation page de Depot en espece via agent---------------------------------------------------------

		IF NOT EXISTS (select * from menus where nom_menu = 'Dva') THEN
	 id_str_trad := maketraductionlangsyst(' Dépôt ');
	 INSERT INTO menus (nom_menu, libel_menu, nom_pere, pos_hierarch, ordre, is_menu,fonction, is_cliquable)
	 VALUES ('Dva', id_str_trad, 'Gen-16', 3, 2, TRUE, 763, TRUE);
	 IF EXISTS(SELECT langue FROM adsys_langues_systeme WHERE code = 'en_GB') THEN
	  INSERT INTO ad_traductions (id_str, langue, traduction) VALUES (id_str_trad, 'en_GB', ' Deposit ');
	 END IF;
	END IF;
-----------------------------------------------------------------------------------------------------------------------------------------------------
		IF NOT EXISTS (select * from menus where nom_menu = 'Dva-1') THEN
	 id_str_trad := maketraductionlangsyst(' Choix du compte  ');
	 INSERT INTO menus (nom_menu, libel_menu, nom_pere, pos_hierarch, ordre, is_menu,fonction, is_cliquable)
	 VALUES ('Dva-1', id_str_trad, 'Dva', 4, 1, FALSE, null, FALSE);
	 IF EXISTS(SELECT langue FROM adsys_langues_systeme WHERE code = 'en_GB') THEN
	  INSERT INTO ad_traductions (id_str, langue, traduction) VALUES (id_str_trad, 'en_GB', ' Account choice  ');
	 END IF;
	END IF;

			IF NOT EXISTS (select * from ecrans where nom_ecran = 'Dva-1') THEN
	 INSERT INTO ecrans(nom_ecran, fichier, nom_menu, fonction)
	 VALUES ('Dva-1', 'modules/agency_banking/depot_via_agent.php', 'Dva-1', 763);
	END IF;
------------------------------------------------------------------------------------------------------------------------------------------------------


		IF NOT EXISTS (select * from menus where nom_menu = 'Dva-2') THEN
	 id_str_trad := maketraductionlangsyst('Saisie montant  ');
	 INSERT INTO menus (nom_menu, libel_menu, nom_pere, pos_hierarch, ordre, is_menu,fonction, is_cliquable)
	 VALUES ('Dva-2', id_str_trad, 'Dva', 4, 2, FALSE, null, FALSE);
	 IF EXISTS(SELECT langue FROM adsys_langues_systeme WHERE code = 'en_GB') THEN
	  INSERT INTO ad_traductions (id_str, langue, traduction) VALUES (id_str_trad, 'en_GB', 'Amount seizure  ');
	 END IF;
	END IF;

			IF NOT EXISTS (select * from ecrans where nom_ecran = 'Dva-2') THEN
	 INSERT INTO ecrans(nom_ecran, fichier, nom_menu, fonction)
	 VALUES ('Dva-2', 'modules/agency_banking/depot_via_agent.php', 'Dva-2', 763);
	END IF;

------------------------------------------------------------------------------------------------------------------------------------------------------

		IF NOT EXISTS (select * from menus where nom_menu = 'Dva-3') THEN
	 id_str_trad := maketraductionlangsyst('Confirmation montant  ');
	 INSERT INTO menus (nom_menu, libel_menu, nom_pere, pos_hierarch, ordre, is_menu,fonction, is_cliquable)
	 VALUES ('Dva-3', id_str_trad, 'Dva', 4, 3, FALSE, null, FALSE);
	 IF EXISTS(SELECT langue FROM adsys_langues_systeme WHERE code = 'en_GB') THEN
	  INSERT INTO ad_traductions (id_str, langue, traduction) VALUES (id_str_trad, 'en_GB', 'Amount confirmation  ');
	 END IF;
	END IF;

			IF NOT EXISTS (select * from ecrans where nom_ecran = 'Dva-3') THEN
	 INSERT INTO ecrans(nom_ecran, fichier, nom_menu, fonction)
	 VALUES ('Dva-3', 'modules/agency_banking/depot_via_agent.php', 'Dva-3', 763);
	END IF;
------------------------------------------------------------------------------------------------------------------------------------------------------


		IF NOT EXISTS (select * from menus where nom_menu = 'Dva-4') THEN
	 id_str_trad := maketraductionlangsyst('Traitement dépôt');
	 INSERT INTO menus (nom_menu, libel_menu, nom_pere, pos_hierarch, ordre, is_menu,fonction, is_cliquable)
	 VALUES ('Dva-4', id_str_trad, 'Dva', 4, 4, FALSE, null, FALSE);
	 IF EXISTS(SELECT langue FROM adsys_langues_systeme WHERE code = 'en_GB') THEN
	  INSERT INTO ad_traductions (id_str, langue, traduction) VALUES (id_str_trad, 'en_GB', 'Deposit treatment');
	 END IF;
	END IF;

			IF NOT EXISTS (select * from ecrans where nom_ecran = 'Dva-4') THEN
	 INSERT INTO ecrans(nom_ecran, fichier, nom_menu, fonction)
	 VALUES ('Dva-4', 'modules/agency_banking/depot_via_agent.php', 'Dva-4', 763);
	END IF;


	----------------------------------------------------- Script de creation client via agent ---------------------------------------
			IF NOT EXISTS (select * from menus where nom_menu = 'Cpa') THEN
	 id_str_trad := maketraductionlangsyst('Creation client via agent');
	 INSERT INTO menus (nom_menu, libel_menu, nom_pere, pos_hierarch, ordre, is_menu,fonction, is_cliquable)
	 VALUES ('Cpa', id_str_trad, 'Gen-16', 3, 1, TRUE, 762, TRUE);
	 IF EXISTS(SELECT langue FROM adsys_langues_systeme WHERE code = 'en_GB') THEN
	  INSERT INTO ad_traductions (id_str, langue, traduction) VALUES (id_str_trad, 'en_GB', 'Creation of client by agent');
	 END IF;
	END IF;
-----------------------------------------------------------------------------------------------------------------------------------------------------
		IF NOT EXISTS (select * from menus where nom_menu = 'Cpa-1') THEN
	 id_str_trad := maketraductionlangsyst('Saisie statut juridique ');
	 INSERT INTO menus (nom_menu, libel_menu, nom_pere, pos_hierarch, ordre, is_menu,fonction, is_cliquable)
	 VALUES ('Cpa-1', id_str_trad, 'Cpa', 4, 1, FALSE, null, FALSE);
	 IF EXISTS(SELECT langue FROM adsys_langues_systeme WHERE code = 'en_GB') THEN
	  INSERT INTO ad_traductions (id_str, langue, traduction) VALUES (id_str_trad, 'en_GB', 'Legal status seizure ');
	 END IF;
	END IF;

			IF NOT EXISTS (select * from ecrans where nom_ecran = 'Cpa-1') THEN
	 INSERT INTO ecrans(nom_ecran, fichier, nom_menu, fonction)
	 VALUES ('Cpa-1', 'modules/agency_banking/creation_client_agent.php', 'Cpa-1', 762);
	END IF;
------------------------------------------------------------------------------------------------------------------------------------------------------


		IF NOT EXISTS (select * from menus where nom_menu = 'Cpa-2') THEN
	 id_str_trad := maketraductionlangsyst('Saisie détails ');
	 INSERT INTO menus (nom_menu, libel_menu, nom_pere, pos_hierarch, ordre, is_menu,fonction, is_cliquable)
	 VALUES ('Cpa-2', id_str_trad, 'Cpa', 4, 2, FALSE, null, FALSE);
	 IF EXISTS(SELECT langue FROM adsys_langues_systeme WHERE code = 'en_GB') THEN
	  INSERT INTO ad_traductions (id_str, langue, traduction) VALUES (id_str_trad, 'en_GB', 'Details seizure ');
	 END IF;
	END IF;

			IF NOT EXISTS (select * from ecrans where nom_ecran = 'Cpa-2') THEN
	 INSERT INTO ecrans(nom_ecran, fichier, nom_menu, fonction)
	 VALUES ('Cpa-2', 'modules/agency_banking/creation_client_agent.php', 'Cpa-2', 762);
	END IF;

------------------------------------------------------------------------------------------------------------------------------------------------------

		IF NOT EXISTS (select * from menus where nom_menu = 'Cpa-3') THEN
	 id_str_trad := maketraductionlangsyst('Confirmation insertion client');
	 INSERT INTO menus (nom_menu, libel_menu, nom_pere, pos_hierarch, ordre, is_menu,fonction, is_cliquable)
	 VALUES ('Cpa-3', id_str_trad, 'Cpa', 4, 3, FALSE, null, FALSE);
	 IF EXISTS(SELECT langue FROM adsys_langues_systeme WHERE code = 'en_GB') THEN
	  INSERT INTO ad_traductions (id_str, langue, traduction) VALUES (id_str_trad, 'en_GB', 'Confirmation of client insertion');
	 END IF;
	END IF;

			IF NOT EXISTS (select * from ecrans where nom_ecran = 'Cpa-3') THEN
	 INSERT INTO ecrans(nom_ecran, fichier, nom_menu, fonction)
	 VALUES ('Cpa-3', 'modules/agency_banking/creation_client_agent.php', 'Cpa-3', 762);
	END IF;
------------------------------------------------------------------------------------------------------------------------------------------------------


		IF NOT EXISTS (select * from menus where nom_menu = 'Cpa-4') THEN
	 id_str_trad := maketraductionlangsyst('Insertion paiement');
	 INSERT INTO menus (nom_menu, libel_menu, nom_pere, pos_hierarch, ordre, is_menu,fonction, is_cliquable)
	 VALUES ('Cpa-4', id_str_trad, 'Cpa', 4, 4, FALSE, null, FALSE);
	 IF EXISTS(SELECT langue FROM adsys_langues_systeme WHERE code = 'en_GB') THEN
	  INSERT INTO ad_traductions (id_str, langue, traduction) VALUES (id_str_trad, 'en_GB', 'Collection of the initial payment ');
	 END IF;
	END IF;

			IF NOT EXISTS (select * from ecrans where nom_ecran = 'Cpa-4') THEN
	 INSERT INTO ecrans(nom_ecran, fichier, nom_menu, fonction)
	 VALUES ('Cpa-4', 'modules/agency_banking/creation_client_agent.php', 'Cpa-4', 762);
	END IF;

------------------------------------------------------------------------------------------------------------------------------------------------------

		IF NOT EXISTS (select * from menus where nom_menu = 'Cpa-5') THEN
	 id_str_trad := maketraductionlangsyst('Perception Versement Initial ');
	 INSERT INTO menus (nom_menu, libel_menu, nom_pere, pos_hierarch, ordre, is_menu,fonction, is_cliquable)
	 VALUES ('Cpa-5', id_str_trad, 'Cpa', 4, 5, FALSE, null, FALSE);
	 IF EXISTS(SELECT langue FROM adsys_langues_systeme WHERE code = 'en_GB') THEN
	  INSERT INTO ad_traductions (id_str, langue, traduction) VALUES (id_str_trad, 'en_GB', 'Collection of the initial payment ');
	 END IF;
	END IF;

			IF NOT EXISTS (select * from ecrans where nom_ecran = 'Cpa-5') THEN
	 INSERT INTO ecrans(nom_ecran, fichier, nom_menu, fonction)
	 VALUES ('Cpa-5', 'modules/agency_banking/creation_client_agent.php', 'Cpa-5', 762);
	END IF;
------------------------------------------------------------------------------------------------------------------------------------------------------

		IF NOT EXISTS (select * from menus where nom_menu = 'Cpa-6') THEN
	 id_str_trad := maketraductionlangsyst('Confirmation creation compte ');
	 INSERT INTO menus (nom_menu, libel_menu, nom_pere, pos_hierarch, ordre, is_menu,fonction, is_cliquable)
	 VALUES ('Cpa-6',id_str_trad, 'Cpa', 4, 6, FALSE, null, FALSE);
	 IF EXISTS(SELECT langue FROM adsys_langues_systeme WHERE code = 'en_GB') THEN
	  INSERT INTO ad_traductions (id_str, langue, traduction) VALUES (id_str_trad, 'en_GB', 'Client account confimation ');
	 END IF;
	END IF;

			IF NOT EXISTS (select * from ecrans where nom_ecran = 'Cpa-6') THEN
	 INSERT INTO ecrans(nom_ecran, fichier, nom_menu, fonction)
	 VALUES ('Cpa-6', 'modules/agency_banking/creation_client_agent.php', 'Cpa-6', 762);
	END IF;

	-----------------------------------------------------------------------------------------------------------------------------------

	--========> Menu de gestion des annulations depots/retraits via agents
	--Main Menu
	IF NOT EXISTS (select * from menus where nom_menu = 'Gdr') THEN
	 id_str_trad := maketraductionlangsyst('Gestion annulation dépôts/retraits via agent');
	 INSERT INTO menus (nom_menu, libel_menu, nom_pere, pos_hierarch, ordre, is_menu,fonction, is_cliquable)
	 VALUES ('Gdr', id_str_trad, 'Gen-16', 3, 3, TRUE, 765, TRUE);
	 IF EXISTS(SELECT langue FROM adsys_langues_systeme WHERE code = 'en_GB') THEN
	  INSERT INTO ad_traductions (id_str, langue, traduction) VALUES (id_str_trad, 'en_GB', 'Management of deposit / withdrawal cancellation via agent');
	 END IF;
	END IF;
		IF NOT EXISTS (select * from ecrans where nom_ecran = 'Gdr-1') THEN
	 INSERT INTO ecrans(nom_ecran, fichier, nom_menu, fonction)
	 VALUES ('Gdr-1', 'modules/agency_banking/gestion_annulation_depot_retrait_agent.php', 'Gdr', 765);
	END IF;

	--========> Menu de demande des annulations depots/retraits via agents
	--Main Menu
	IF NOT EXISTS (select * from menus where nom_menu = 'Ddr') THEN
	 id_str_trad := maketraductionlangsyst('Demande annulation dépôts/retraits via agent');
	 INSERT INTO menus (nom_menu, libel_menu, nom_pere, pos_hierarch, ordre, is_menu,fonction, is_cliquable)
	 VALUES ('Ddr', id_str_trad, 'Gdr', 4, 1, TRUE, 766, TRUE);
	 IF EXISTS(SELECT langue FROM adsys_langues_systeme WHERE code = 'en_GB') THEN
	  INSERT INTO ad_traductions (id_str, langue, traduction) VALUES (id_str_trad, 'en_GB', 'Request of deposit / withdrawal cancellation via agent');
	 END IF;
	END IF;

	IF NOT EXISTS (select * from menus where nom_menu = 'Ldr-1') THEN
	 id_str_trad := maketraductionlangsyst('Liste des demandes ');
	 INSERT INTO menus (nom_menu, libel_menu, nom_pere, pos_hierarch, ordre, is_menu,fonction, is_cliquable)
	 VALUES ('Ldr-1', id_str_trad, 'Ddr', 5, 1, FALSE, 766, FALSE);
	 IF EXISTS(SELECT langue FROM adsys_langues_systeme WHERE code = 'en_GB') THEN
	  INSERT INTO ad_traductions (id_str, langue, traduction) VALUES (id_str_trad, 'en_GB', 'List of cancellation request ');
	 END IF;
	END IF;
		IF NOT EXISTS (select * from ecrans where nom_ecran = 'Ldr-1') THEN
	 INSERT INTO ecrans(nom_ecran, fichier, nom_menu, fonction)
	 VALUES ('Ldr-1', 'modules/agency_banking/gestion_annulation_depot_retrait_agent.php', 'Ldr-1', 766);
	END IF;

	IF NOT EXISTS (select * from menus where nom_menu = 'Ldr-2') THEN
	 id_str_trad := maketraductionlangsyst('Confirmation de la demande');
	 INSERT INTO menus (nom_menu, libel_menu, nom_pere, pos_hierarch, ordre, is_menu,fonction, is_cliquable)
	 VALUES ('Ldr-2', id_str_trad, 'Ddr', 5, 2, FALSE, 766, FALSE);
	 IF EXISTS(SELECT langue FROM adsys_langues_systeme WHERE code = 'en_GB') THEN
	  INSERT INTO ad_traductions (id_str, langue, traduction) VALUES (id_str_trad, 'en_GB', 'List of deposit / withdrawal cancellation via agent');
	 END IF;
	END IF;
		IF NOT EXISTS (select * from ecrans where nom_ecran = 'Ldr-2') THEN
	 INSERT INTO ecrans(nom_ecran, fichier, nom_menu, fonction)
	 VALUES ('Ldr-2', 'modules/agency_banking/gestion_annulation_depot_retrait_agent.php', 'Ldr-2', 766);
	END IF;


	------------------Approbation demande annulation depot/retait-----------------------------------------------------------------
	--Main Menu
	IF NOT EXISTS (select * from menus where nom_menu = 'Add') THEN
	 id_str_trad := maketraductionlangsyst('Approbation demande annulation dépôts/retraits via agent');
	 INSERT INTO menus (nom_menu, libel_menu, nom_pere, pos_hierarch, ordre, is_menu,fonction, is_cliquable)
	 VALUES ('Add',  id_str_trad, 'Gdr', 4, 2, TRUE, 767, TRUE);
	 IF EXISTS(SELECT langue FROM adsys_langues_systeme WHERE code = 'en_GB') THEN
	  INSERT INTO ad_traductions (id_str, langue, traduction) VALUES ( id_str_trad, 'en_GB', 'Approval of deposit / withdrawal request cancellation via agent');
	 END IF;
	END IF;

	IF NOT EXISTS (select * from menus where nom_menu = 'Add-1') THEN
	 id_str_trad := maketraductionlangsyst('Liste des demandes annulations ');
	 INSERT INTO menus (nom_menu, libel_menu, nom_pere, pos_hierarch, ordre, is_menu,fonction, is_cliquable)
	 VALUES ('Add-1',  id_str_trad, 'Add', 5, 1, FALSE, 767, FALSE);
	 IF EXISTS(SELECT langue FROM adsys_langues_systeme WHERE code = 'en_GB') THEN
	  INSERT INTO ad_traductions (id_str, langue, traduction) VALUES ( id_str_trad, 'en_GB', 'List of cancellation request');
	 END IF;
	END IF;
		IF NOT EXISTS (select * from ecrans where nom_ecran = 'Add-1') THEN
	 INSERT INTO ecrans(nom_ecran, fichier, nom_menu, fonction)
	 VALUES ('Add-1', 'modules/agency_banking/gestion_annulation_depot_retrait_agent.php', 'Add-1', 767);
	END IF;

	IF NOT EXISTS (select * from menus where nom_menu = 'Add-2') THEN
	 id_str_trad := maketraductionlangsyst('Confirmation de la demande ');
	 INSERT INTO menus (nom_menu, libel_menu, nom_pere, pos_hierarch, ordre, is_menu,fonction, is_cliquable)
	 VALUES ('Add-2',  id_str_trad, 'Add', 5, 2, FALSE, 767, FALSE);
	 IF EXISTS(SELECT langue FROM adsys_langues_systeme WHERE code = 'en_GB') THEN
	  INSERT INTO ad_traductions (id_str, langue, traduction) VALUES ( id_str_trad, 'en_GB', 'List of deposit / withdrawal cancellation via agent');
	 END IF;
	END IF;
		IF NOT EXISTS (select * from ecrans where nom_ecran = 'Add-2') THEN
	 INSERT INTO ecrans(nom_ecran, fichier, nom_menu, fonction)
	 VALUES ('Add-2', 'modules/agency_banking/gestion_annulation_depot_retrait_agent.php', 'Add-2', 767);
	END IF;

		------------------Effectuer demande annulation depot/retait-----------------------------------------------------------------
	--Main Menu
	IF NOT EXISTS (select * from menus where nom_menu = 'Eda') THEN
	 id_str_trad := maketraductionlangsyst('Effectuer demande annulation dépôts/retraits via agent');
	 INSERT INTO menus (nom_menu, libel_menu, nom_pere, pos_hierarch, ordre, is_menu,fonction, is_cliquable)
	 VALUES ('Eda',  id_str_trad, 'Gdr', 4, 3, TRUE, 768, TRUE);
	 IF EXISTS(SELECT langue FROM adsys_langues_systeme WHERE code = 'en_GB') THEN
	  INSERT INTO ad_traductions (id_str, langue, traduction) VALUES ( id_str_trad, 'en_GB', 'Review of deposit / withdrawal request cancellation via agent');
	 END IF;
	END IF;

	IF NOT EXISTS (select * from menus where nom_menu = 'Eda-1') THEN
	 id_str_trad := maketraductionlangsyst('Liste des demandes annulations autorisées ');
	 INSERT INTO menus (nom_menu, libel_menu, nom_pere, pos_hierarch, ordre, is_menu,fonction, is_cliquable)
	 VALUES ('Eda-1',  id_str_trad, 'Eda', 5, 1, FALSE, 768, FALSE);
	 IF EXISTS(SELECT langue FROM adsys_langues_systeme WHERE code = 'en_GB') THEN
	  INSERT INTO ad_traductions (id_str, langue, traduction) VALUES ( id_str_trad, 'en_GB', 'List of cancellation request approved');
	 END IF;
	END IF;
		IF NOT EXISTS (select * from ecrans where nom_ecran = 'Eda-1') THEN
	 INSERT INTO ecrans(nom_ecran, fichier, nom_menu, fonction)
	 VALUES ('Eda-1', 'modules/agency_banking/gestion_annulation_depot_retrait_agent.php', 'Eda-1', 768);
	END IF;

	IF NOT EXISTS (select * from menus where nom_menu = 'Eda-2') THEN
	 id_str_trad := maketraductionlangsyst('Confirmation annulation ');
	 INSERT INTO menus (nom_menu, libel_menu, nom_pere, pos_hierarch, ordre, is_menu,fonction, is_cliquable)
	 VALUES ('Eda-2',  id_str_trad, 'Eda', 5, 2, FALSE, 768, FALSE);
	 IF EXISTS(SELECT langue FROM adsys_langues_systeme WHERE code = 'en_GB') THEN
	  INSERT INTO ad_traductions (id_str, langue, traduction) VALUES ( id_str_trad, 'en_GB', 'Comfirm of deposit / withdrawal cancellation ');
	 END IF;
	END IF;
		IF NOT EXISTS (select * from ecrans where nom_ecran = 'Eda-2') THEN
	 INSERT INTO ecrans(nom_ecran, fichier, nom_menu, fonction)
	 VALUES ('Eda-2', 'modules/agency_banking/gestion_annulation_depot_retrait_agent.php', 'Eda-2', 768);
	END IF;



	RETURN output_result;
END;
$$
LANGUAGE plpgsql;

SELECT script_creation_menu_ecran_agb();
DROP FUNCTION script_creation_menu_ecran_agb();
