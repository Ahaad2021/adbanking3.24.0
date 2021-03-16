------------- Ticket #677 :  ---------------------------------------

CREATE OR REPLACE FUNCTION patch_ticket_677() RETURNS INT AS $BODY$
DECLARE

output_result INTEGER = 1;

BEGIN

	RAISE NOTICE 'START';

	-- Check if field "licence_key" exist in table "ad_agc"
	IF NOT EXISTS(SELECT * FROM information_schema.columns WHERE table_name = 'ad_agc' AND column_name = 'licence_key') THEN
		ALTER TABLE ad_agc ADD COLUMN licence_key text;
		output_result := 2;
	END IF;

	-- Clear field
	UPDATE ad_agc SET licence_code_identifier = NULL, licence_key = NULL;

	-- Truncate table
	TRUNCATE TABLE adsys_licence RESTART IDENTITY;

	RAISE NOTICE 'END';

	RETURN output_result;  

END;
$BODY$
LANGUAGE plpgsql VOLATILE COST 100;
ALTER FUNCTION patch_ticket_677() OWNER TO adbanking;

--------------------- Execution ------------------------------------
SELECT patch_ticket_677();
DROP FUNCTION patch_ticket_677();
--------------------------------------------------------------------