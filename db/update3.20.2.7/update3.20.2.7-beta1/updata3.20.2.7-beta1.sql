CREATE OR REPLACE FUNCTION ticket_AT_101() RETURNS INT AS
$$
DECLARE
output_result INTEGER = 1;
  id_str_trad integer = 0;
  tableliste_ident integer = 0;

BEGIN
    -- Create the column is_centralise in ad_poste if it does not exist
  IF (SELECT count(*) from information_schema.columns WHERE table_name = 'adsys_multi_agence' AND column_name='client_actifs') = 0 THEN
    ALTER TABLE adsys_multi_agence ADD COLUMN client_actifs integer;
  END IF;

		RETURN output_result;
END;
$$
LANGUAGE plpgsql;

SELECT ticket_AT_101();
DROP FUNCTION ticket_AT_101();