-- Function: creation_menus_ecrans_mae17()

-- DROP FUNCTION creation_menus_ecrans_mae17();

CREATE OR REPLACE FUNCTION creation_menus_ecrans_mae17()
  RETURNS integer AS
$BODY$
DECLARE

  output_result integer = 0;
  id_str_trad integer = 0;

BEGIN
	--========> Perception des frais d'adhesion par lot
	--Main Menu
	IF NOT EXISTS (select * from menus where nom_menu = 'Faf') THEN
	 id_str_trad := maketraductionlangsyst('Perception des frais d''adhesion par lot');
	 INSERT INTO menus (nom_menu, libel_menu, nom_pere, pos_hierarch, ordre, is_menu,fonction, is_cliquable)
	 VALUES ('Faf', id_str_trad, 'Gen-6', 3, 10, TRUE, 153, TRUE);
	 IF EXISTS (select * from adsys_langues_systeme where code = 'en_GB') THEN
	 INSERT INTO ad_traductions (id_str, langue, traduction) VALUES (id_str_trad, 'en_GB', 'Perception of membership fees by batch');
	 END IF;
	END IF;
	--Choix de la source des fonds
	----Menu
	IF NOT EXISTS (select * from menus where nom_menu = 'Faf-1') THEN
	 id_str_trad := maketraductionlangsyst('Saisie des Informations');
	 INSERT INTO menus (nom_menu, libel_menu, nom_pere, pos_hierarch, ordre, is_menu,fonction, is_cliquable)
	 VALUES ('Faf-1', id_str_trad, 'Faf', 4, 1, FALSE, 153, TRUE);
	 IF EXISTS (select * from adsys_langues_systeme where code = 'en_GB') THEN
	 INSERT INTO ad_traductions (id_str, langue, traduction) VALUES (id_str_trad, 'en_GB', 'Gathering Informations');
	 END IF;
	END IF;
	----Ecran
	IF NOT EXISTS (select * from ecrans where nom_ecran = 'Faf-1') THEN
	 INSERT INTO ecrans(nom_ecran, fichier, nom_menu, fonction)
	 VALUES ('Faf-1', 'modules/guichet/frais_adhesion_fichier_lot.php', 'Faf-1', 153);
	END IF;
	--Récupération du fichier de données
	----Ecran
	IF NOT EXISTS (select * from ecrans where nom_ecran = 'Faf-2') THEN
	 INSERT INTO ecrans(nom_ecran, fichier, nom_menu, fonction)
	 VALUES ('Faf-2', 'modules/guichet/frais_adhesion_fichier_lot.php', 'Faf-1', 153);
	END IF;
	--Demande de confirmation de la perception des frais d'adhesion par lot via fichier
	----Menu
	IF NOT EXISTS (select * from menus where nom_menu = 'Faf-2') THEN
	 id_str_trad := maketraductionlangsyst('Demande Confirmation');
	 INSERT INTO menus (nom_menu, libel_menu, nom_pere, pos_hierarch, ordre, is_menu,fonction, is_cliquable)
	 VALUES ('Faf-2', id_str_trad, 'Faf', 4, 2, FALSE, 153, FALSE);
	 IF EXISTS (select * from adsys_langues_systeme where code = 'en_GB') THEN
	 INSERT INTO ad_traductions (id_str, langue, traduction) VALUES (id_str_trad, 'en_GB', 'Requesting Confirmation');
	 END IF;
	END IF;
	----Ecran
	IF NOT EXISTS (select * from ecrans where nom_ecran = 'Faf-3') THEN
	 INSERT INTO ecrans(nom_ecran, fichier, nom_menu, fonction)
	 VALUES ('Faf-3', 'modules/guichet/frais_adhesion_fichier_lot.php', 'Faf-2', 153);
	END IF;
	--Confirmation de la perception des frais d'adhesion par lot via fichier
	----Menu
	IF NOT EXISTS (select * from menus where nom_menu = 'Faf-3') THEN
	 id_str_trad := maketraductionlangsyst('Confirmation des Opérations');
	 INSERT INTO menus (nom_menu, libel_menu, nom_pere, pos_hierarch, ordre, is_menu,fonction, is_cliquable)
	 VALUES ('Faf-3', id_str_trad, 'Faf', 4, 3, FALSE, 153, FALSE);
	 IF EXISTS (select * from adsys_langues_systeme where code = 'en_GB') THEN
	 INSERT INTO ad_traductions (id_str, langue, traduction) VALUES (id_str_trad, 'en_GB', 'Confiramtion of transactions');
	 END IF;
	END IF;
	----Ecran
	IF NOT EXISTS (select * from ecrans where nom_ecran = 'Faf-4') THEN
	 INSERT INTO ecrans(nom_ecran, fichier, nom_menu, fonction)
	 VALUES ('Faf-4', 'modules/guichet/frais_adhesion_fichier_lot.php', 'Faf-3', 153);
	END IF;

	output_result := 1;

	RETURN output_result;

END;
$BODY$
  LANGUAGE plpgsql VOLATILE
  COST 100;
ALTER FUNCTION creation_menus_ecrans_mae17()
  OWNER TO adbanking;

  SELECT creation_menus_ecrans_mae17();

  DROP FUNCTION IF EXISTS creation_menus_ecrans_mae17();
