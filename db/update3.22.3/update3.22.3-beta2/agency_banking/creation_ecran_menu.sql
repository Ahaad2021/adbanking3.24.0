CREATE OR REPLACE FUNCTION script_creation_menu_ecran_agb() RETURNS INT AS
$$
DECLARE
output_result INTEGER = 1;
  id_str_trad integer = 0;

BEGIN
-----------------Gestion des profils agents-----------------------------------------------------------------
	--Main Menu
	IF NOT EXISTS (select * from menus where nom_menu = 'Gpa') THEN
	 id_str_trad := maketraductionlangsyst('Gestion profil agency banking');
	 INSERT INTO menus (nom_menu, libel_menu, nom_pere, pos_hierarch, ordre, is_menu,fonction, is_cliquable)
	 VALUES ('Gpa',  id_str_trad, 'Gen-16', 3, 3, TRUE, 776, TRUE);
	 IF EXISTS(SELECT langue FROM adsys_langues_systeme WHERE code = 'en_GB') THEN
	  INSERT INTO ad_traductions (id_str, langue, traduction) VALUES ( id_str_trad, 'en_GB', 'Agency Banking Profile setup');
	 END IF;
	END IF;

	IF NOT EXISTS (select * from ecrans where nom_ecran = 'Gpa-1') THEN
	 INSERT INTO ecrans(nom_ecran, fichier, nom_menu, fonction)
	 VALUES ('Gpa-1', 'modules/agency_banking/profils_agent.php', 'Gpa', 776);
	END IF;

	IF NOT EXISTS (select * from menus where nom_menu = 'Apa') THEN
	 id_str_trad := maketraductionlangsyst('Ajout profil agent ');
	 INSERT INTO menus (nom_menu, libel_menu, nom_pere, pos_hierarch, ordre, is_menu,fonction, is_cliquable)
	 VALUES ('Apa',  id_str_trad, 'Gpa', 4, 1, FALSE, 777, FALSE);
	 IF EXISTS(SELECT langue FROM adsys_langues_systeme WHERE code = 'en_GB') THEN
	  INSERT INTO ad_traductions (id_str, langue, traduction) VALUES ( id_str_trad, 'en_GB', 'Add Agent profile');
	 END IF;
	END IF;
		IF NOT EXISTS (select * from ecrans where nom_ecran = 'Apa-1') THEN
	 INSERT INTO ecrans(nom_ecran, fichier, nom_menu, fonction)
	 VALUES ('Apa-1', 'modules/agency_banking/profils_agent.php', 'Apa', 777);
	END IF;


	IF NOT EXISTS (select * from ecrans where nom_ecran = 'Apa-2') THEN
	 INSERT INTO ecrans(nom_ecran, fichier, nom_menu, fonction)
	 VALUES ('Apa-2', 'modules/agency_banking/profils_agent.php', 'Apa', 777);
	END IF;


		IF NOT EXISTS (select * from menus where nom_menu = 'Mpr') THEN
	 id_str_trad := maketraductionlangsyst('Modification profil agent ');
	 INSERT INTO menus (nom_menu, libel_menu, nom_pere, pos_hierarch, ordre, is_menu,fonction, is_cliquable)
	 VALUES ('Mpr',  id_str_trad, 'Gpa', 4, 2, FALSE, 778, FALSE);
	 IF EXISTS(SELECT langue FROM adsys_langues_systeme WHERE code = 'en_GB') THEN
	  INSERT INTO ad_traductions (id_str, langue, traduction) VALUES ( id_str_trad, 'en_GB', 'Modify Agent profile');
	 END IF;
	END IF;
		IF NOT EXISTS (select * from ecrans where nom_ecran = 'Mpr-1') THEN
	 INSERT INTO ecrans(nom_ecran, fichier, nom_menu, fonction)
	 VALUES ('Mpr-1', 'modules/agency_banking/profils_agent.php', 'Mpr', 778);
	END IF;


	IF NOT EXISTS (select * from ecrans where nom_ecran = 'Mpr-2') THEN
	 INSERT INTO ecrans(nom_ecran, fichier, nom_menu, fonction)
	 VALUES ('Mpr-2', 'modules/agency_banking/profils_agent.php', 'Mpr', 778);
	END IF;


	IF NOT EXISTS (select * from menus where nom_menu = 'Cpr') THEN
	 id_str_trad := maketraductionlangsyst('Consultation profil agent ');
	 INSERT INTO menus (nom_menu, libel_menu, nom_pere, pos_hierarch, ordre, is_menu,fonction, is_cliquable)
	 VALUES ('Cpr',  id_str_trad, 'Gpa', 4, 3, FALSE, 779, FALSE);
	 IF EXISTS(SELECT langue FROM adsys_langues_systeme WHERE code = 'en_GB') THEN
	  INSERT INTO ad_traductions (id_str, langue, traduction) VALUES ( id_str_trad, 'en_GB', 'Consult Agent profile');
	 END IF;
	END IF;
		IF NOT EXISTS (select * from ecrans where nom_ecran = 'Cpr-1') THEN
	 INSERT INTO ecrans(nom_ecran, fichier, nom_menu, fonction)
	 VALUES ('Cpr-1', 'modules/agency_banking/profils_agent.php', 'Cpr', 779);
	END IF;


	IF NOT EXISTS (select * from menus where nom_menu = 'Spr') THEN
	 id_str_trad := maketraductionlangsyst('Suppression profil agent ');
	 INSERT INTO menus (nom_menu, libel_menu, nom_pere, pos_hierarch, ordre, is_menu,fonction, is_cliquable)
	 VALUES ('Spr',  id_str_trad, 'Gpa', 4, 4, FALSE, 780, FALSE);
	 IF EXISTS(SELECT langue FROM adsys_langues_systeme WHERE code = 'en_GB') THEN
	  INSERT INTO ad_traductions (id_str, langue, traduction) VALUES ( id_str_trad, 'en_GB', 'Delete Agent profile');
	 END IF;
	END IF;
		IF NOT EXISTS (select * from ecrans where nom_ecran = 'Spr-1') THEN
	 INSERT INTO ecrans(nom_ecran, fichier, nom_menu, fonction)
	 VALUES ('Spr-1', 'modules/agency_banking/profils_agent.php', 'Spr', 780);
	END IF;
			IF NOT EXISTS (select * from ecrans where nom_ecran = 'Spr-2') THEN
	 INSERT INTO ecrans(nom_ecran, fichier, nom_menu, fonction)
	 VALUES ('Spr-2', 'modules/agency_banking/profils_agent.php', 'Spr', 780);
	END IF;


	RETURN output_result;
END;
$$
LANGUAGE plpgsql;

SELECT script_creation_menu_ecran_agb();
DROP FUNCTION script_creation_menu_ecran_agb();