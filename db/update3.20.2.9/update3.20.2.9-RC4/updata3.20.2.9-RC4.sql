CREATE OR REPLACE FUNCTION getdatajournalcomptable(text, integer, date, date, integer)
  RETURNS SETOF data_export_journal AS
$BODY$
 DECLARE

in_compte ALIAS FOR $1;		-- numero compte
in_type_ope ALIAS FOR $2;		-- type operation
in_date_debut ALIAS FOR $3;	-- date debut
in_date_fin ALIAS FOR $4;	-- date fin
in_id_agence ALIAS FOR $5;	-- id agence


v_sens_inv text;

counter integer :=0 ;

ligne record;
ligne1 record;

cur_list_compte refcursor;
cur_list_compte_inv refcursor;

ligne_data data_export_journal;

BEGIN
	--Creation table temporaire
	CREATE TEMP TABLE getdatajournal AS SELECT a.id_ecriture, a.id_his, c.date, b.compte, b.sens, b.montant, c.id_client, b.devise, a.libel_ecriture,a.type_operation,a.info_ecriture
		FROM ad_ecriture a INNER JOIN ad_mouvement b ON a.id_ag = b.id_ag AND a.id_ecriture=b.id_ecriture
		INNER JOIN ad_his c ON b.id_ag = c.id_ag AND a.id_his=c.id_his
		WHERE c.id_ag = in_id_agence AND date(date_comptable) >= date(in_date_debut) AND date(date_comptable) <= date(in_date_fin) order by c.date,a.id_ecriture;


	IF (in_compte IS NULL AND in_type_ope IS NULL) THEN
		OPEN cur_list_compte FOR SELECT abc.* FROM getdatajournal abc order by abc.date,abc.id_ecriture;
	ELSIF (in_compte IS NOT NULL AND in_type_ope IS NULL) THEN
		OPEN cur_list_compte FOR SELECT abc.* FROM getdatajournal abc WHERE (abc.compte=in_compte) order by abc.date,abc.id_ecriture;
	ELSIF (in_compte IS NULL AND in_type_ope IS NOT NULL) THEN
		OPEN cur_list_compte FOR SELECT abc.* FROM getdatajournal abc WHERE abc.libel_ecriture = (SELECT libel_ope FROM ad_cpt_ope WHERE type_operation = in_type_ope) order by abc.date,abc.id_ecriture;
	ELSE
		OPEN cur_list_compte FOR SELECT abc.* FROM getdatajournal abc WHERE (abc.compte=in_compte) AND abc.libel_ecriture = (SELECT libel_ope FROM ad_cpt_ope WHERE type_operation = in_type_ope)
		order by abc.date,abc.id_ecriture;
	END IF;

	FETCH cur_list_compte INTO ligne;
	WHILE FOUND LOOP

		IF (ligne.sens = 'c') THEN
		v_sens_inv = 'd';
		ELSE
		v_sens_inv = 'c';
		END IF;

		OPEN cur_list_compte_inv FOR SELECT abc.* FROM getdatajournal abc WHERE abc.id_ecriture = ligne.id_ecriture and abc.sens = v_sens_inv;
		FETCH cur_list_compte_inv INTO ligne1;
		WHILE FOUND LOOP
			SELECT INTO ligne_data ligne1.id_ecriture, ligne1.id_his, ligne1.date, ligne1.compte, ligne1.sens, ligne1.montant, ligne1.id_client, ligne1.devise, ligne1.libel_ecriture,ligne1.type_operation,ligne1.info_ecriture, in_id_agence;
			RETURN NEXT ligne_data;

		FETCH cur_list_compte_inv INTO ligne1;
		END LOOP;
		CLOSE cur_list_compte_inv;

		--RAISE NOTICE 'id_ecriture => %	--  id_his => %', ligne.id_ecriture,ligne.id_his;
		counter = counter + 1;

	FETCH cur_list_compte INTO ligne;
	END LOOP;
	CLOSE cur_list_compte;
	--RAISE NOTICE 'counter => %',counter;

END;
$BODY$
  LANGUAGE plpgsql VOLATILE
  COST 100
  ROWS 1000;
ALTER FUNCTION getdatajournalcomptable(text, integer, date, date, integer)
  OWNER TO postgres;