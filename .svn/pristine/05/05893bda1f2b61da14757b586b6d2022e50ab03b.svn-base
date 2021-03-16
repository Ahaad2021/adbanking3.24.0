-- Function: creation_menus_ecrans_at32()

-- DROP FUNCTION creation_menus_ecrans_at32();

CREATE OR REPLACE FUNCTION creation_menus_ecrans_at32()
  RETURNS integer AS
$BODY$
DECLARE

  output_result integer = 0;
  id_str_trad integer = 0;

BEGIN
	--========> Rapport Etat de la Compensation au siege des operations en deplace
	--Main Menu
	IF NOT EXISTS (select * from menus where nom_menu = 'Rec') THEN
	 id_str_trad := maketraductionlangsyst('Rapport Etat de la Compensation des opérations en déplacé');
	 INSERT INTO menus (nom_menu, libel_menu, nom_pere, pos_hierarch, ordre, is_menu,fonction, is_cliquable)
	 VALUES ('Rec', id_str_trad, 'Gen-7', 3, 12, TRUE, 242, TRUE);
	 IF EXISTS (select * from adsys_langues_systeme where code = 'en_GB') THEN
	 INSERT INTO ad_traductions (id_str, langue, traduction) VALUES (id_str_trad, 'en_GB', 'Remote Transactions Compensation Status Report');
	 END IF;
	END IF;
	--Personnalisation du rapport
	----Menu
	IF NOT EXISTS (select * from menus where nom_menu = 'Rec-1') THEN
	 id_str_trad := maketraductionlangsyst('Criteres de recherche');
	 INSERT INTO menus (nom_menu, libel_menu, nom_pere, pos_hierarch, ordre, is_menu,fonction, is_cliquable)
	 VALUES ('Rec-1', id_str_trad, 'Rec', 4, 1, FALSE, 242, TRUE);
	 IF EXISTS (select * from adsys_langues_systeme where code = 'en_GB') THEN
	 INSERT INTO ad_traductions (id_str, langue, traduction) VALUES (id_str_trad, 'en_GB', 'Report Customization');
	 END IF;
	END IF;
	----Ecran
	IF NOT EXISTS (select * from ecrans where nom_ecran = 'Rec-1') THEN
	 INSERT INTO ecrans(nom_ecran, fichier, nom_menu, fonction)
	 VALUES ('Rec-1', 'modules/systeme/traitements_compensation.php', 'Rec-1', 242);
	END IF;
	--Confirmation du Rapport
	----Menu
	IF NOT EXISTS (select * from menus where nom_menu = 'Rec-2') THEN
	 id_str_trad := maketraductionlangsyst('Confirmation du Rapport');
	 INSERT INTO menus (nom_menu, libel_menu, nom_pere, pos_hierarch, ordre, is_menu,fonction, is_cliquable)
	 VALUES ('Rec-2', id_str_trad, 'Rec', 4, 2, FALSE, 242, FALSE);
	 IF EXISTS (select * from adsys_langues_systeme where code = 'en_GB') THEN
	 INSERT INTO ad_traductions (id_str, langue, traduction) VALUES (id_str_trad, 'en_GB', 'Report Confirmation');
	 END IF;
	END IF;
	----Ecran
	IF NOT EXISTS (select * from ecrans where nom_ecran = 'Rec-2') THEN
	 INSERT INTO ecrans(nom_ecran, fichier, nom_menu, fonction)
	 VALUES ('Rec-2', 'modules/systeme/traitements_compensation.php', 'Rec-2', 242);
	END IF;

	output_result := 1;

	RETURN output_result;

END;
$BODY$
  LANGUAGE plpgsql VOLATILE
  COST 100;
ALTER FUNCTION creation_menus_ecrans_at32()
  OWNER TO adbanking;

  SELECT creation_menus_ecrans_at32();

  DROP FUNCTION IF EXISTS creation_menus_ecrans_at32();
