CREATE OR REPLACE FUNCTION mise_a_jour_adsys_param_mouvement() RETURNS INT AS
$$
DECLARE
output_result INTEGER = 1;
v_ident INTEGER;

BEGIN

   -- Check if cle exist in table adsys_param_abonnement
   IF EXISTS(SELECT * FROM information_schema.tables WHERE table_name = 'adsys_param_mouvement') THEN
      SELECT INTO v_ident ident FROM tableliste WHERE nomc = 'adsys_param_mouvement';
      UPDATE d_tableliste SET isreq = 'f' WHERE tablen = v_ident AND nchmpc = 'type_opt';
   END IF;

	RETURN output_result;
END;
$$
LANGUAGE plpgsql;

SELECT mise_a_jour_adsys_param_mouvement();
DROP FUNCTION mise_a_jour_adsys_param_mouvement();