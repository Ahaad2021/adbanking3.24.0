-- Type: grandlivre_view

-- DROP TYPE grandlivre_view cascade;
DROP TYPE grandlivre_view Cascade;

CREATE TYPE grandlivre_view AS
   (compte character varying(50),
    id_ecriture integer,
    libel_ecriture text,
    date_comptable date,
    type_operation integer,
    sens character(1),
    devise character(3),
    montant numeric(30,6),
    id_his integer,
    id_client integer,
    id_ag integer,
    id_jou integer
    );
ALTER TYPE grandlivre_view
  OWNER TO adbanking;


-- Function: getgrandlivreview(date, date, integer)

-- DROP FUNCTION getgrandlivreview(date, date, integer);

CREATE OR REPLACE FUNCTION getgrandlivreview(date, date, integer)
  RETURNS SETOF grandlivre_view AS
$BODY$
DECLARE
  date_deb ALIAS FOR $1;
  date_fin ALIAS FOR $2;
  id_agence ALIAS FOR $3;

  ligne_grandlivre grandlivre_view;
  ligne            RECORD;
  grandlivre       REFCURSOR;
  idClient        INTEGER;
BEGIN
  --date_deb_calc := date(date_deb) - interval '1 day';
  OPEN grandlivre FOR SELECT * FROM view_compta WHERE date_valeur >= date_deb AND date_valeur <= date_fin AND id_ag = id_agence;
  FETCH grandlivre INTO ligne;
  WHILE FOUND LOOP
  -- Récupère l'id du client s'il existe
  SELECT INTO idClient id_client FROM ad_his   WHERE id_his = ligne.id_his AND id_ag = id_agence;
  -- Resultat de la vue
 SELECT INTO ligne_grandlivre ligne.compte, ligne.id_ecriture, ligne.libel_ecriture, ligne.date_comptable, ligne.type_operation, ligne.sens, ligne.devise, ligne.montant, ligne.id_his, idClient AS id_client, ligne.id_ag , ligne.id_jou;
  RETURN NEXT ligne_grandlivre;
  FETCH grandlivre INTO ligne;
  END LOOP;
 CLOSE grandlivre;
RETURN;
END;
$BODY$
  LANGUAGE plpgsql VOLATILE
  COST 100
  ROWS 1000;
ALTER FUNCTION getgrandlivreview(date, date, integer)
  OWNER TO adbanking;
