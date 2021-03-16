-- Function: creation_menus_ecrans_mae20()

-- DROP FUNCTION creation_menus_ecrans_mae20();

CREATE OR REPLACE FUNCTION creation_menus_ecrans_mae21()
  RETURNS integer AS
$BODY$
DECLARE

  output_result integer = 0;
  id_str_trad integer = 0;

BEGIN
	--========> Gestion des retenues EPA / NP : Rapport Agence
	--Personnalisation du Rapport
	----Ecran
	IF NOT EXISTS (select * from ecrans where nom_ecran = 'Ara-65') THEN
	 INSERT INTO ecrans(nom_ecran, fichier, nom_menu, fonction)
	 VALUES ('Ara-65', 'modules/rapports/rapports_agence.php', 'Ara-2', 370);
	END IF;
	--Generation du Rapport
	----Ecran
	IF NOT EXISTS (select * from ecrans where nom_ecran = 'Ara-66') THEN
	 INSERT INTO ecrans(nom_ecran, fichier, nom_menu, fonction)
	 VALUES ('Ara-66', 'modules/rapports/rapports_agence.php', 'Ara-2', 370);
	END IF;

	----Ecran
	IF NOT EXISTS (select * from ecrans where nom_ecran = 'Ara-67') THEN
	 INSERT INTO ecrans(nom_ecran, fichier, nom_menu, fonction)
	 VALUES ('Ara-67', 'modules/rapports/rapports_agence.php', 'Ara-3', 370);
	END IF;

	output_result := 1;

	RETURN output_result;

END;
$BODY$
  LANGUAGE plpgsql VOLATILE
  COST 100;
ALTER FUNCTION creation_menus_ecrans_mae21()
  OWNER TO adbanking;

  SELECT creation_menus_ecrans_mae21();

  DROP FUNCTION IF EXISTS creation_menus_ecrans_mae21();
