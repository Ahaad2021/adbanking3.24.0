------------- Ticket #549 :  ---------------------------------------

CREATE OR REPLACE FUNCTION patch_549() RETURNS void AS $BODY$

DECLARE
  to_raz INTEGER;

BEGIN

to_raz := 0;

-- Create the column montant_dotation in ad_provision if it does not exist

  IF (SELECT count(*) from information_schema.columns WHERE table_name = 'ad_provision' AND column_name='montant_dotation') = 0 THEN
    ALTER TABLE ad_provision ADD COLUMN montant_dotation numeric(30,6);
    to_raz := 1;
    RAISE INFO 'Created column montant_dotation in table ad_provision';
  END IF;

-- Create the column montant_repris in ad_provision if it does not exist
  IF (SELECT count(*) from information_schema.columns WHERE table_name = 'ad_provision' AND column_name='montant_repris') = 0 THEN
    ALTER TABLE ad_provision ADD COLUMN montant_repris numeric(30,6);
    to_raz := 1;
    RAISE INFO 'Created column montant_repris in table ad_provision';
  END IF;
  
  IF(to_raz = 1) THEN
    -- Remise a zero des prov_mnt dans ad_dcr
  update ad_dcr set prov_mnt = 0 where prov_mnt > 0;
  
  -- truncate ad_provision
  truncate table ad_provision;
  
  --reset sequence of ad_provision
  PERFORM reset_sequence('ad_provision', NULL, 'id_provision');
  END IF; 

END;
$BODY$
LANGUAGE plpgsql VOLATILE COST 100;
ALTER FUNCTION patch_549() OWNER TO adbanking;

--------------------- Execution -----------------------------------
SELECT patch_549();
Drop function patch_549();
--------------------------------------------------------------------