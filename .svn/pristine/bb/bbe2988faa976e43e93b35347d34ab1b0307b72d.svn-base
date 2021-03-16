CREATE OR REPLACE FUNCTION ticket_AT_86() RETURNS INT AS
$$
DECLARE
output_result INTEGER = 1;
  id_str_trad integer = 0;
  tableliste_ident integer = 0;

BEGIN
    -- Create the column is_centralise in ad_poste if it does not exist
  IF (SELECT count(*) from information_schema.columns WHERE table_name = 'ad_multi_agence_compensation' AND column_name='date_creation') = 0 THEN
    ALTER TABLE ad_multi_agence_compensation ADD COLUMN date_creation timestamp without time zone NOT NULL DEFAULT ((now())::character varying(23))::timestamp without time zone;
  END IF;

		RETURN output_result;
END;
$$
LANGUAGE plpgsql;

SELECT ticket_AT_86();
DROP FUNCTION ticket_AT_86();
