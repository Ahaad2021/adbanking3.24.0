---------------------------------------------Debut Ticket REL-104----------------------------------------------------------------------

CREATE OR REPLACE FUNCTION ticket_REL_104() RETURNS INT AS
$BODY$
DECLARE
output_result INTEGER = 1;
  id_str_trad integer = 0;
  tableliste_ident integer = 0;

BEGIN
	--Tables de correspondance : Confirmation Gestion
	----Menu
	IF NOT EXISTS (select * from menus where nom_menu = 'Gtc-7') THEN
	 id_str_trad := maketraductionlangsyst('Saisie de Exercice/Type du Budget');
	 INSERT INTO menus (nom_menu, libel_menu, nom_pere, pos_hierarch, ordre, is_menu,fonction, is_cliquable)
	 VALUES ('Gtc-7', maketraductionlangsyst('Saisie de Exercice/Type du Budget'), 'Gtc', 4, 7, FALSE, 701, FALSE);
	 IF EXISTS(SELECT langue FROM adsys_langues_systeme WHERE code = 'en_GB') THEN
	  INSERT INTO ad_traductions (id_str, langue, traduction) VALUES (id_str_trad, 'en_GB', 'Select exercice/type of budget');
	 END IF;
	END IF;
	IF NOT EXISTS (select * from ecrans where nom_ecran = 'Gtc-7') THEN
	 INSERT INTO ecrans(nom_ecran, fichier, nom_menu, fonction)
	 VALUES ('Gtc-7', 'modules/budget/gestion_tables_correspondance.php', 'Gtc-7', 701);
	END IF;
	RETURN output_result;
END;
$BODY$
  LANGUAGE plpgsql VOLATILE
  COST 100;
ALTER FUNCTION ticket_REL_104()
  OWNER TO postgres;

  SELECT ticket_REL_104();
  DROP FUNCTION IF EXISTS ticket_REL_104();

  ---------------------------------------------FIN Ticket REL-104----------------------------------------------------------------------
---------------------------------------------DEBUT Ticket AT-110----------------------------------------------------------------------

CREATE OR REPLACE FUNCTION ticket_AT_110() RETURNS INT AS
$BODY$
DECLARE
output_result INTEGER = 1;
column_exist INTEGER = 0;
BEGIN

 -- Create the column traduction in adsys_infos_systeme if it does not exist
 SELECT INTO column_exist count(*) from information_schema.columns WHERE table_name = 'adsys_infos_systeme' AND column_name='traduction';
  IF (column_exist = 0)  THEN
    ALTER TABLE adsys_infos_systeme ADD COLUMN traduction text;
  END IF;


RETURN output_result;
END;
$BODY$
  LANGUAGE plpgsql VOLATILE
  COST 100;
ALTER FUNCTION ticket_AT_110()
  OWNER TO postgres;

  SELECT ticket_AT_110();
  DROP FUNCTION IF EXISTS ticket_AT_110();


CREATE OR REPLACE FUNCTION update_adsys_infos_systeme(
    text,
    text)
  RETURNS integer AS
$BODY$
 DECLARE
 rpm_v ALIAS FOR $1 ;
 fileName_v ALIAS FOR $2 ;
 output_result integer :=0;

 BEGIN
 raise notice 'file name => %',fileName_v ;
raise notice 'rpm version => % ', rpm_v;

 UPDATE adsys_infos_systeme SET traduction = fileName_v WHERE version_rpm = rpm_v and  is_active = 'true' ;
 output_result := 1;
RETURN output_result;
END ;

$BODY$
  LANGUAGE plpgsql VOLATILE
  COST 100;
ALTER FUNCTION update_adsys_infos_systeme(text, text)
  OWNER TO adbanking;


---------------------------------------------FIN Ticket AT-110----------------------------------------------------------------------