------------------------------------------------- Debut REL-116----------------------------------
DROP TYPE IF EXISTS cpte_comptable_latest CASCADE;

CREATE TYPE cpte_comptable_latest AS
(
	num_cpte_comptable character varying(50), -- On permet de saisir des n° de comptes alphanumériques
  libel_cpte_comptable text
  );
  ALTER TYPE cpte_comptable_latest
  OWNER TO adbanking;


-- Function: get_latest_cpte_comptables()

-- DROP FUNCTION get_latest_cpte_comptables();

CREATE OR REPLACE FUNCTION get_latest_cpte_comptables()
  RETURNS SETOF cpte_comptable_latest AS
$BODY$
  DECLARE

  curs_get_cpte_comptable CURSOR FOR SELECT num_cpte_comptable, libel_cpte_comptable FROM ad_cpt_comptable ORDER BY num_cpte_comptable;

  ligne RECORD;
  count_cpte INTEGER :=0;

  cpte_comptable_last cpte_comptable_latest;

  BEGIN

  OPEN curs_get_cpte_comptable;
	FETCH curs_get_cpte_comptable INTO ligne;
	WHILE FOUND LOOP
		SELECT INTO count_cpte COUNT(num_cpte_comptable) FROM ad_cpt_comptable WHERE num_cpte_comptable LIKE ligne.num_cpte_comptable||'.%';
		IF count_cpte = 0 OR count_cpte IS NULL THEN
			SELECT INTO cpte_comptable_last ligne.num_cpte_comptable, ligne.libel_cpte_comptable;
			RETURN NEXT cpte_comptable_last;
		END IF;

	FETCH curs_get_cpte_comptable INTO ligne;
	END LOOP;
	CLOSE curs_get_cpte_comptable;

	RETURN;
	END;
$BODY$
  LANGUAGE plpgsql VOLATILE
  COST 100
  ROWS 1000;
ALTER FUNCTION get_latest_cpte_comptables()
  OWNER TO postgres;
----------------------------------------------------- FIN REL-116---------------------------------------------------

----------------------------------------------------- DEBUT AT-149--------------------------------------------------

CREATE OR REPLACE FUNCTION ticket_AT_149() RETURNS INT AS
$BODY$
DECLARE
output_result INTEGER = 1;
column_exist INTEGER = 0;
BEGIN

 SELECT INTO column_exist count(*) from information_schema.columns WHERE table_name = 'ad_retrait_attente' AND column_name='num_piece';
  IF (column_exist = 0)  THEN
    ALTER TABLE ad_retrait_attente ADD COLUMN num_piece text;
  END IF;

 SELECT INTO column_exist count(*) from information_schema.columns WHERE table_name = 'ad_retrait_attente' AND column_name='lieu_delivrance';
  IF (column_exist = 0)  THEN
    ALTER TABLE ad_retrait_attente ADD COLUMN lieu_delivrance text;
  END IF;

  RETURN output_result;
END;
$BODY$
  LANGUAGE plpgsql VOLATILE
  COST 100;
ALTER FUNCTION ticket_AT_149()
  OWNER TO postgres;

  SELECT ticket_AT_149();
  DROP FUNCTION IF EXISTS ticket_AT_149();


------------------------------------------------------ FIN AT-149----------------------------------------------------