CREATE OR REPLACE FUNCTION script_creation_menu_ecran_agb() RETURNS INT AS
$$
DECLARE
output_result INTEGER = 1;
  id_str_trad integer = 0;
  pos_ordre integer = 0;

BEGIN

IF NOT EXISTS (select * from ecrans where nom_ecran = 'Rab-9') THEN
 INSERT INTO ecrans(nom_ecran, fichier, nom_menu, fonction)
 VALUES ('Rab-9', 'modules/agency_banking/rapport_agency_banking.php', 'Rab-2', 787);
END IF;

----------------------------------------------------------------------------------------------------------------------------

IF NOT EXISTS (select * from ecrans where nom_ecran = 'Rab-19') THEN
 INSERT INTO ecrans(nom_ecran, fichier, nom_menu, fonction)
 VALUES ('Rab-19', 'modules/agency_banking/rapport_agency_banking.php', 'Rab-4', 787);
END IF;

----------------------------------------------------------------------------------------------------------------------------

IF NOT EXISTS (select * from ecrans where nom_ecran = 'Rab-29') THEN
 INSERT INTO ecrans(nom_ecran, fichier, nom_menu, fonction)
 VALUES ('Rab-29', 'modules/agency_banking/rapport_agency_banking.php', 'Rab-3', 787);
END IF;


	RETURN output_result;
END;
$$
LANGUAGE plpgsql;

SELECT script_creation_menu_ecran_agb();
DROP FUNCTION script_creation_menu_ecran_agb();
