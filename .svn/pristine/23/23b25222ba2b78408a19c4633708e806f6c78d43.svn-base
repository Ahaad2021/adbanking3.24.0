	CREATE OR REPLACE FUNCTION script_creation_menu_ecran_agb() RETURNS INT AS
$$
DECLARE
output_result INTEGER = 1;
  id_str_trad integer = 0;
  tableliste_ident INTEGER = 0;
  d_tableliste_str INTEGER =0;

BEGIN

	IF NOT EXISTS (select * from menus where nom_menu = 'Rab') THEN
	 id_str_trad := maketraductionlangsyst('Rapports agency banking');
	 INSERT INTO menus (nom_menu, libel_menu, nom_pere, pos_hierarch, ordre, is_menu,fonction, is_cliquable)
	 VALUES ('Rab', id_str_trad, 'Gen-16', 3, 17, TRUE, 787, TRUE);
	 IF EXISTS(SELECT langue FROM adsys_langues_systeme WHERE code = 'en_GB') THEN
	  INSERT INTO ad_traductions (id_str, langue, traduction) VALUES (id_str_trad, 'en_GB', 'Agency banking reports');
	 END IF;
	END IF;

---------------------------------------------------------------------------------------------------------------------------

		IF NOT EXISTS (select * from menus where nom_menu = 'Rab-1') THEN
	 id_str_trad := maketraductionlangsyst(' Critère de recherche ');
	 INSERT INTO menus (nom_menu, libel_menu, nom_pere, pos_hierarch, ordre, is_menu,fonction, is_cliquable)
	 VALUES ('Rab-1', id_str_trad, 'Rab', 4, 1, FALSE, null, FALSE);
	 IF EXISTS(SELECT langue FROM adsys_langues_systeme WHERE code = 'en_GB') THEN
	  INSERT INTO ad_traductions (id_str, langue, traduction) VALUES (id_str_trad, 'en_GB', ' Reports criteria agency ');
	 END IF;
	END IF;

			IF NOT EXISTS (select * from ecrans where nom_ecran = 'Rab-1') THEN
	 INSERT INTO ecrans(nom_ecran, fichier, nom_menu, fonction)
	 VALUES ('Rab-1', 'modules/agency_banking/rapport_agency_banking.php', 'Rab-1', 787);
	END IF;

---------------------------------------------------------------------------------------------------------------------------

		IF NOT EXISTS (select * from menus where nom_menu = 'Rab-2') THEN
	 id_str_trad := maketraductionlangsyst('Personnalisation rapport');
	 INSERT INTO menus (nom_menu, libel_menu, nom_pere, pos_hierarch, ordre, is_menu,fonction, is_cliquable)
	 VALUES ('Rab-2', id_str_trad, 'Rab', 4, 2, FALSE, null, FALSE);
	 IF EXISTS(SELECT langue FROM adsys_langues_systeme WHERE code = 'en_GB') THEN
	  INSERT INTO ad_traductions (id_str, langue, traduction) VALUES (id_str_trad, 'en_GB', 'Reports personalization ');
	 END IF;
	END IF;

			IF NOT EXISTS (select * from ecrans where nom_ecran = 'Rab-2') THEN
	 INSERT INTO ecrans(nom_ecran, fichier, nom_menu, fonction)
	 VALUES ('Rab-2', 'modules/agency_banking/rapport_agency_banking.php', 'Rab-2', 787);
	END IF;

---------------------------------------------------------------------------------------------------------------------------

		IF NOT EXISTS (select * from menus where nom_menu = 'Rab-3') THEN
	 id_str_trad := maketraductionlangsyst('Export données ');
	 INSERT INTO menus (nom_menu, libel_menu, nom_pere, pos_hierarch, ordre, is_menu,fonction, is_cliquable)
	 VALUES ('Rab-3', id_str_trad, 'Rab', 4, 3, FALSE, null, FALSE);
	 IF EXISTS(SELECT langue FROM adsys_langues_systeme WHERE code = 'en_GB') THEN
	  INSERT INTO ad_traductions (id_str, langue, traduction) VALUES (id_str_trad, 'en_GB', 'Data export ');
	 END IF;
	END IF;

			IF NOT EXISTS (select * from ecrans where nom_ecran = 'Rab-3') THEN
	 INSERT INTO ecrans(nom_ecran, fichier, nom_menu, fonction)
	 VALUES ('Rab-3', 'modules/agency_banking/rapport_agency_banking.php', 'Rab-3', 787);
	END IF;

---------------------------------------------------------------------------------------------------------------------------

		IF NOT EXISTS (select * from menus where nom_menu = 'Rab-4') THEN
	 id_str_trad := maketraductionlangsyst('Impression rapport ');
	 INSERT INTO menus (nom_menu, libel_menu, nom_pere, pos_hierarch, ordre, is_menu,fonction, is_cliquable)
	 VALUES ('Rab-4', id_str_trad, 'Rab', 4, 4, FALSE, null, FALSE);
	 IF EXISTS(SELECT langue FROM adsys_langues_systeme WHERE code = 'en_GB') THEN
	  INSERT INTO ad_traductions (id_str, langue, traduction) VALUES (id_str_trad, 'en_GB', 'Report generation');
	 END IF;
	END IF;

			IF NOT EXISTS (select * from ecrans where nom_ecran = 'Rab-4') THEN
	 INSERT INTO ecrans(nom_ecran, fichier, nom_menu, fonction)
	 VALUES ('Rab-4', 'modules/agency_banking/rapport_agency_banking.php', 'Rab-4', 787);
	END IF;

----------------------------------------------------------------------------------------------------------------------------

	IF NOT EXISTS (select * from ecrans where nom_ecran = 'Rab-14') THEN
	 INSERT INTO ecrans(nom_ecran, fichier, nom_menu, fonction)
	 VALUES ('Rab-14', 'modules/agency_banking/rapport_agency_banking.php', 'Rab-3', 787);
	END IF;

----------------------------------------------------------------------------------------------------------------------------

	IF NOT EXISTS (select * from ecrans where nom_ecran = 'Rab-5') THEN
	 INSERT INTO ecrans(nom_ecran, fichier, nom_menu, fonction)
	 VALUES ('Rab-5', 'modules/agency_banking/rapport_agency_banking.php', 'Rab-2', 787);
	END IF;

----------------------------------------------------------------------------------------------------------------------------

	IF NOT EXISTS (select * from ecrans where nom_ecran = 'Rab-15') THEN
	 INSERT INTO ecrans(nom_ecran, fichier, nom_menu, fonction)
	 VALUES ('Rab-15', 'modules/agency_banking/rapport_agency_banking.php', 'Rab-4', 787);
	END IF;

----------------------------------------------------------------------------------------------------------------------------

	IF NOT EXISTS (select * from ecrans where nom_ecran = 'Rab-25') THEN
	 INSERT INTO ecrans(nom_ecran, fichier, nom_menu, fonction)
	 VALUES ('Rab-25', 'modules/agency_banking/rapport_agency_banking.php', 'Rab-3', 787);
	END IF;

----------------------------------------------------------------------------------------------------------------------------

	IF NOT EXISTS (select * from ecrans where nom_ecran = 'Rab-6') THEN
	 INSERT INTO ecrans(nom_ecran, fichier, nom_menu, fonction)
	 VALUES ('Rab-6', 'modules/agency_banking/rapport_agency_banking.php', 'Rab-2', 787);
	END IF;

----------------------------------------------------------------------------------------------------------------------------

	IF NOT EXISTS (select * from ecrans where nom_ecran = 'Rab-16') THEN
	 INSERT INTO ecrans(nom_ecran, fichier, nom_menu, fonction)
	 VALUES ('Rab-16', 'modules/agency_banking/rapport_agency_banking.php', 'Rab-4', 787);
	END IF;

----------------------------------------------------------------------------------------------------------------------------

	IF NOT EXISTS (select * from ecrans where nom_ecran = 'Rab-26') THEN
	 INSERT INTO ecrans(nom_ecran, fichier, nom_menu, fonction)
	 VALUES ('Rab-26', 'modules/agency_banking/rapport_agency_banking.php', 'Rab-3', 787);
	END IF;


IF NOT EXISTS (select * from ecrans where nom_ecran = 'Rab-7') THEN
	 INSERT INTO ecrans(nom_ecran, fichier, nom_menu, fonction)
	 VALUES ('Rab-7', 'modules/agency_banking/rapport_agency_banking.php', 'Rab-2', 787);
	END IF;

----------------------------------------------------------------------------------------------------------------------------

	IF NOT EXISTS (select * from ecrans where nom_ecran = 'Rab-17') THEN
	 INSERT INTO ecrans(nom_ecran, fichier, nom_menu, fonction)
	 VALUES ('Rab-17', 'modules/agency_banking/rapport_agency_banking.php', 'Rab-4', 787);
	END IF;

----------------------------------------------------------------------------------------------------------------------------

	IF NOT EXISTS (select * from ecrans where nom_ecran = 'Rab-27') THEN
	 INSERT INTO ecrans(nom_ecran, fichier, nom_menu, fonction)
	 VALUES ('Rab-27', 'modules/agency_banking/rapport_agency_banking.php', 'Rab-3', 787);
	END IF;



	IF NOT EXISTS (select * from ecrans where nom_ecran = 'Rab-8') THEN
	 INSERT INTO ecrans(nom_ecran, fichier, nom_menu, fonction)
	 VALUES ('Rab-8', 'modules/agency_banking/rapport_agency_banking.php', 'Rab-2', 787);
	END IF;

----------------------------------------------------------------------------------------------------------------------------

	IF NOT EXISTS (select * from ecrans where nom_ecran = 'Rab-18') THEN
	 INSERT INTO ecrans(nom_ecran, fichier, nom_menu, fonction)
	 VALUES ('Rab-18', 'modules/agency_banking/rapport_agency_banking.php', 'Rab-4', 787);
	END IF;

----------------------------------------------------------------------------------------------------------------------------

	IF NOT EXISTS (select * from ecrans where nom_ecran = 'Rab-28') THEN
	 INSERT INTO ecrans(nom_ecran, fichier, nom_menu, fonction)
	 VALUES ('Rab-28', 'modules/agency_banking/rapport_agency_banking.php', 'Rab-3', 787);
	END IF;


	RETURN output_result;
END;
$$
LANGUAGE plpgsql;

SELECT script_creation_menu_ecran_agb();
DROP FUNCTION script_creation_menu_ecran_agb();