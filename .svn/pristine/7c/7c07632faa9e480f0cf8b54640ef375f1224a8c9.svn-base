--------------------------------------------------------Debut Ticket ticket_AT_169----------------------------------------------
CREATE OR REPLACE FUNCTION ticket_AT_169()
  RETURNS INT AS
$BODY$
DECLARE
output_result INTEGER := 1;
tableliste_ident INTEGER :=0;
d_tableliste_str INTEGER :=0;

BEGIN

select INTO tableliste_ident ident from tableliste where nomc like 'adsys_produit_credit' order by ident desc limit 1;


	 IF NOT EXISTS(SELECT * FROM d_tableliste WHERE nchmpc = 'is_taux_mensuel' and tablen = tableliste_ident) THEN
	   ALTER TABLE adsys_produit_credit ADD COLUMN is_taux_mensuel boolean default false;
	   d_tableliste_str := makeTraductionLangSyst('Taux d''intérêt mensuel?');
	   INSERT INTO d_tableliste VALUES ((select max(ident) from d_tableliste)+1, tableliste_ident, 'is_taux_mensuel', d_tableliste_str, NULL,null, 'bol', NULL, NULL, false);
	   IF EXISTS(SELECT langue FROM adsys_langues_systeme WHERE code = 'en_GB') THEN
	    INSERT INTO ad_traductions VALUES (d_tableliste_str,'en_GB','Monthly rate');
	   END IF;
	 END IF;


RETURN output_result;
END;
$BODY$
  LANGUAGE plpgsql VOLATILE
  COST 100;
ALTER FUNCTION ticket_AT_169()
  OWNER TO postgres;

  SELECT ticket_AT_169();
  DROP FUNCTION IF EXISTS ticket_AT_169();
---------------------------------------------------------Fin Ticket ticket_AT_169-----------------------------------------------