--------------------------------------------------------Debut add_tib_retrait_attente----------------------------------------------
CREATE OR REPLACE FUNCTION add_tib_retrait_attente()
  RETURNS INT AS
$BODY$
DECLARE
output_result INTEGER := 1;

BEGIN

	 IF NOT EXISTS(SELECT column_name FROM information_schema.columns WHERE table_name='ad_retrait_deplace_attente' and column_name='tib') THEN
	   ALTER TABLE ad_retrait_deplace_attente ADD COLUMN tib text;
	 END IF;

	 RETURN output_result;
END;
$BODY$
  LANGUAGE plpgsql VOLATILE
  COST 100;
ALTER FUNCTION add_tib_retrait_attente()
  OWNER TO postgres;

  SELECT add_tib_retrait_attente();
  DROP FUNCTION IF EXISTS add_tib_retrait_attente();