-------------------------- Function: calcul_date_etat_hist(integer, integer, date) ---------------------

-- DROP FUNCTION calcul_date_etat_hist(integer, integer, date);

CREATE OR REPLACE FUNCTION calcul_date_etat_hist(integer, integer, date)
  RETURNS date AS
$BODY$

DECLARE
 v_id_ag ALIAS FOR $1;
 v_id_dossier ALIAS FOR $2;
 v_date_rapport ALIAS FOR $3;
 v_date_etat DATE;
 v_date_counter DATE;

 BEGIN
select into v_date_etat
case when v_date_rapport >= date(d.date_etat) then d.date_etat else
(
select max(date(date_action)) from ad_dcr dcr inner join ad_dcr_hist hist on dcr.id_ag = hist.id_ag and dcr.id_doss  = hist.id_doss
where dcr.id_doss = v_id_dossier and dcr.id_ag = v_id_ag and
date(date_action) <= v_date_rapport
) end
from ad_dcr d where d.id_ag = v_id_ag and d.id_doss = v_id_dossier;


  RETURN v_date_etat;

END;
$BODY$
  LANGUAGE plpgsql VOLATILE
  COST 100;

-------------------------- Function: calculetatdossier_hist(integer, integer, date) ----------------------

-- DROP FUNCTION calculetatdossier_hist(integer, integer, date);

CREATE OR REPLACE FUNCTION calculetatdossier_hist(integer, integer, date)
  RETURNS integer AS
  $BODY$

DECLARE
 v_id_ag ALIAS FOR $1;
 v_id_dossier ALIAS FOR $2;
 v_date_rapport ALIAS FOR $3;
 v_etat INTEGER;
 v_date_counter DATE;

 cur_etats_doss CURSOR FOR
 select date_action, etat from (
SELECT date(date_action) as date_action, max(etat) as etat
FROM ad_dcr_hist WHERE id_doss = v_id_dossier and id_ag = v_id_ag group by date(date_action)

UNION

select date(now()) as date_action, etat from ad_dcr where id_doss = v_id_dossier and id_ag = v_id_ag
) A order by date_action desc;

 --SELECT date(date_action) as date_action, max(etat) as etat FROM stg_ad_dcr_hist WHERE id_doss = v_id_dossier and id_ag = v_id_ag group by date(date_action) ORDER BY date(date_action) desc;
 v_ligne RECORD;

BEGIN

 OPEN cur_etats_doss;
 FETCH cur_etats_doss INTO v_ligne;

  WHILE FOUND LOOP

	v_date_counter := v_ligne.date_action;

	WHILE (v_date_counter > v_date_rapport) LOOP

	v_etat:= v_ligne.etat;


	EXIT;

	v_date_counter := v_date_counter - 1;

	END LOOP;

  FETCH cur_etats_doss INTO v_ligne;
  END LOOP;

  CLOSE cur_etats_doss;
  RETURN v_etat;

END;
$BODY$
LANGUAGE plpgsql VOLATILE
COST 100;

--------------------- Create index on ad_dcr_hist -----------------------
CREATE INDEX idx_id_doss
ON ad_dcr_hist
USING btree
(id_doss, id_ag);