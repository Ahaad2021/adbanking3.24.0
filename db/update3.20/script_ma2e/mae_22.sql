  CREATE OR REPLACE FUNCTION script_mae_22() RETURNS INT AS
$BODY$
DECLARE
output_result INTEGER = 1;
tableliste_ident INTEGER = 0;
tableliste_str INTEGER = 0;
d_tableliste_str INTEGER = 0;

BEGIN

-- Creation nouveau champ dans la table ad_agc + d_tableliste
IF EXISTS(SELECT * FROM information_schema.tables WHERE table_name = 'ad_agc') THEN

	-- Ajout champ dans la table
	IF EXISTS(SELECT * FROM tableliste WHERE nomc = 'ad_agc') THEN

	tableliste_ident := (select ident from tableliste where nomc like 'ad_agc' order by ident desc limit 1);

		IF NOT EXISTS(SELECT * FROM d_tableliste WHERE nchmpc = 'appl_date_val_classique' and tablen = tableliste_ident) THEN
		  ALTER TABLE ad_agc ADD COLUMN appl_date_val_classique BOOLEAN DEFAULT FALSE;
		  d_tableliste_str := makeTraductionLangSyst('Appliquer date valeur classique?');
		  INSERT INTO d_tableliste VALUES ((select max(ident) from d_tableliste)+1, tableliste_ident, 'appl_date_val_classique', d_tableliste_str, false, NULL, 'bol', false, false, false);
		  IF EXISTS (select * from adsys_langues_systeme where code = 'en_GB') THEN
		  INSERT INTO ad_traductions VALUES (d_tableliste_str,'en_GB','Apply classic date value?');
		  END IF;
		END IF;
	END IF;

END IF;

RETURN output_result;
END;
$BODY$
  LANGUAGE plpgsql VOLATILE
  COST 100;
ALTER FUNCTION script_mae_22()
  OWNER TO postgres;


select script_mae_22();

DROP FUNCTION IF EXISTS script_mae_22();
