------------- Ticket #558 :  ---------------------------------------

CREATE OR REPLACE FUNCTION patch_558() RETURNS void AS $BODY$
BEGIN

  IF (SELECT count(*) from information_schema.columns WHERE table_name = 'ad_cpt_hist' AND column_name='id_his') = 0 THEN
    ALTER TABLE ad_cpt_hist ADD COLUMN id_his integer;    
  END IF;

  IF (SELECT count(*) from information_schema.columns WHERE table_name = 'ad_cpt_hist' AND column_name='login') = 0 THEN
    ALTER TABLE ad_cpt_hist ADD COLUMN login text;
  END IF;

  IF (SELECT count(*) from information_schema.columns WHERE table_name = 'ad_cpt_hist' AND column_name='type_fonction') = 0 THEN
    ALTER TABLE ad_cpt_hist ADD COLUMN type_fonction integer;
  END IF;
  IF (SELECT count(*) from information_schema.columns WHERE table_name = 'ad_cpt_hist' AND column_name='id_titulaire') = 0 THEN
    ALTER TABLE ad_cpt_hist ADD COLUMN id_titulaire integer;
  END IF;

  RAISE INFO 'MAJ schema ad_cpt_hist ';  

END;
$BODY$
LANGUAGE plpgsql VOLATILE COST 100;
ALTER FUNCTION patch_558() OWNER TO adbanking;

--------------------- Execution ------------------------------
SELECT patch_558();
Drop function patch_558();
--------------------------------------------------------------
