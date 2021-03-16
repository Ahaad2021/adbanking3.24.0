------------------------- Ticket AT-91 : Bug Export Journal  comptable -------------------------
DROP TYPE IF EXISTS data_export_journal CASCADE;


CREATE TYPE data_export_journal AS (
      id_ecriture int,
      id_his int,
      date_s timestamp without time zone,
      compte text,
      sens text,
      montant numeric(30,6),
      id_client int,
      devise text,
      libel_ecriture int,
      type_operation int,
      info_ecriture text,
      id_ag int
      );


CREATE OR REPLACE FUNCTION getdatajournal(
    text,
    date,
    date,
    integer)
  RETURNS SETOF data_export_journal AS
$BODY$
 DECLARE

in_compte ALIAS FOR $1;		-- numero compte
in_date_debut ALIAS FOR $2;	-- date debut
in_date_fin ALIAS FOR $3;	-- date fin
in_id_agence ALIAS FOR $4;	-- id agence


v_sens_inv text;

counter integer :=0 ;

ligne record;
ligne1 record;

cur_list_compte refcursor;
cur_list_compte_inv refcursor;

ligne_data data_export_journal;

BEGIN

IF (in_compte IS NULL) THEN
OPEN cur_list_compte FOR SELECT a.id_ecriture, a.id_his, c.date, b.compte, b.sens, b.montant, c.id_client, b.devise, a.libel_ecriture,a.type_operation,a.info_ecriture
FROM ad_ecriture a ,ad_mouvement b, ad_his c
WHERE a.id_ag = b.id_ag AND b.id_ag = c.id_ag AND c.id_ag = in_id_agence AND a.id_ecriture=b.id_ecriture AND a.id_his= c.id_his
AND date(date_comptable) >= in_date_debut AND date(date_comptable) <= in_date_fin order by c.date,a.id_ecriture;
ELSE
OPEN cur_list_compte FOR SELECT a.id_ecriture, a.id_his, c.date, b.compte, b.sens, b.montant, c.id_client, b.devise, a.libel_ecriture,a.type_operation,a.info_ecriture
FROM ad_ecriture a ,ad_mouvement b, ad_his c
WHERE a.id_ag = b.id_ag AND b.id_ag = c.id_ag AND c.id_ag = in_id_agence AND a.id_ecriture=b.id_ecriture AND a.id_his= c.id_his
AND date(date_comptable) >= in_date_debut AND date(date_comptable) <= in_date_fin AND (compte=in_compte) order by c.date,a.id_ecriture;
END IF;

FETCH cur_list_compte INTO ligne;
WHILE FOUND LOOP

	SELECT INTO ligne_data ligne.id_ecriture, ligne.id_his, ligne.date, ligne.compte, ligne.sens, ligne.montant, ligne.id_client, ligne.devise, ligne.libel_ecriture,ligne.type_operation,ligne.info_ecriture, in_id_agence;
	RETURN NEXT ligne_data;

	IF (ligne.sens = 'c') THEN
	v_sens_inv = 'd';
	ELSE
	v_sens_inv = 'c';
	END IF;

	OPEN cur_list_compte_inv FOR SELECT a.id_ecriture, a.id_his, c.date, b.compte, b.sens, b.montant, c.id_client, b.devise, a.libel_ecriture,a.type_operation,a.info_ecriture
	FROM ad_ecriture a ,ad_mouvement b, ad_his c
	WHERE a.id_ag = b.id_ag AND b.id_ag = c.id_ag AND c.id_ag = in_id_agence AND a.id_ecriture=b.id_ecriture AND a.id_his= c.id_his
	AND b.id_ecriture = ligne.id_ecriture and b.sens = v_sens_inv;
	FETCH cur_list_compte_inv INTO ligne1;
	WHILE FOUND LOOP
	SELECT INTO ligne_data ligne1.id_ecriture, ligne1.id_his, ligne1.date, ligne1.compte, ligne1.sens, ligne1.montant, ligne1.id_client, ligne1.devise, ligne1.libel_ecriture,ligne1.type_operation,ligne1.info_ecriture, in_id_agence;
	RETURN NEXT ligne_data;

	FETCH cur_list_compte_inv INTO ligne1;
	END LOOP;
	CLOSE cur_list_compte_inv;




	RAISE NOTICE 'id_ecriture => %	--  id_his => %', ligne.id_ecriture,ligne.id_his;
	counter = counter + 1;

FETCH cur_list_compte INTO ligne;
END LOOP;
CLOSE cur_list_compte;
 RAISE NOTICE 'counter => %',counter;

END;
$BODY$
  LANGUAGE plpgsql VOLATILE
  COST 100
  ROWS 1000;
ALTER FUNCTION getdatajournal(text, date, date,integer)
  OWNER TO postgres;

------------------------- Ticket AT-91: Bug Export Journal  comptable -------------------------