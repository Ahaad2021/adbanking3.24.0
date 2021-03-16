CREATE OR REPLACE FUNCTION drop_constraint_ad_cpt_cpte_virement_clot()  RETURNS INT AS
$BODY$

DECLARE

result INTEGER = 1;

BEGIN

 RAISE NOTICE 'START';

IF (select count(*) from information_schema.constraint_table_usage where constraint_name = 'ad_cpt_cpte_virement_clot_fkey')  > 0 THEN
 ALTER TABLE ad_cpt DROP CONSTRAINT ad_cpt_cpte_virement_clot_fkey;
 RAISE NOTICE 'Suppression ad_cpt_cpte_virement_clot_fkey';
 result := 2;
END IF;

IF (select count(*) from information_schema.constraint_table_usage where constraint_name = 'ad_cpt_cpte_virement_clot_fkey1')  > 0 THEN
 ALTER TABLE ad_cpt DROP CONSTRAINT ad_cpt_cpte_virement_clot_fkey1;
 RAISE NOTICE 'Suppression ad_cpt_cpte_virement_clot_fkey1';
 result := 2;
END IF;

IF (select count(*) from information_schema.constraint_table_usage where constraint_name = 'ad_cpt_cpte_virement_clot_fkey2')  > 0 THEN
 ALTER TABLE ad_cpt DROP CONSTRAINT ad_cpt_cpte_virement_clot_fkey2;
 RAISE NOTICE 'Suppression ad_cpt_cpte_virement_clot_fkey2';
 result := 2;
END IF;

IF (select count(*) from information_schema.constraint_table_usage where constraint_name = 'ad_cpt_cpte_virement_clot_fkey3')  > 0 THEN
 ALTER TABLE ad_cpt DROP CONSTRAINT ad_cpt_cpte_virement_clot_fkey3;
 RAISE NOTICE 'Suppression ad_cpt_cpte_virement_clot_fkey3';
 result := 2;
END IF;

IF (select count(*) from information_schema.constraint_table_usage where constraint_name = 'ad_cpt_cpte_virement_clot_fkey4')  > 0 THEN
 ALTER TABLE ad_cpt DROP CONSTRAINT ad_cpt_cpte_virement_clot_fkey4;
 RAISE NOTICE 'Suppression ad_cpt_cpte_virement_clot_fkey4';
 result := 2;
END IF;

IF (select count(*) from information_schema.constraint_table_usage where constraint_name = 'ad_cpt_cpte_virement_clot_fkey5')  > 0 THEN
 ALTER TABLE ad_cpt DROP CONSTRAINT ad_cpt_cpte_virement_clot_fkey5;
 RAISE NOTICE 'Suppression ad_cpt_cpte_virement_clot_fkey5';
 END IF;

RETURN result;

END;
$BODY$
  LANGUAGE plpgsql VOLATILE
  COST 100;


-- #321
SELECT drop_constraint_ad_cpt_cpte_virement_clot();
DROP FUNCTION drop_constraint_ad_cpt_cpte_virement_clot();