-- Function: getperiodecapitalisation(date, date, date, date)

-- DROP FUNCTION getperiodecapitalisation(date, date, date, date);

CREATE OR REPLACE FUNCTION getperiodecapitalisation(date, date, date, date)
  RETURNS integer AS
$BODY$
DECLARE
	date_cap ALIAS FOR $1;
	date_ouv ALIAS FOR $2;
	date_last_cap ALIAS FOR $3;
	date_fin_cyle ALIAS FOR $4;

	nb_jours INTEGER;
	jours_mois INTEGER;
	dernier_mois INTEGER;
	mois_date_fin INTEGER;
	nb_jours_mois INTEGER;
	date_proc_mois DATE;
	tmp_date DATE;
	agence RECORD;

BEGIN
	nb_jours := 0;

	-- JIRA MAE-22/27 : recupere info agence + calcul nombre jour
	SELECT INTO agence appl_date_val_classique FROM ad_agc WHERE id_ag = numagc();

	IF agence.appl_date_val_classique IS TRUE THEN

		-- Nombre jour pour mois courant + calcul nombre jour
		SELECT INTO jours_mois date_part('day', date_cap::timestamp);
		--RAISE NOTICE 'Info Agence = % | Jour Mois = %',agence.appl_date_val_classique,jours_mois;
		date_proc_mois := date_cap + (1::text || ' month')::interval;
		IF jours_mois < 15 AND date_fin_cyle > date_proc_mois THEN
			nb_jours_mois := 15;
			nb_jours := nb_jours_mois + (date_fin_cyle - date_proc_mois);
		ELSIF date_fin_cyle < date_proc_mois THEN -- dernier mois
			nb_jours_mois := date_fin_cyle - date_cap;
			nb_jours := nb_jours_mois;
		ELSE -- > 15
			nb_jours_mois := 0;
			nb_jours := date_fin_cyle - date_proc_mois;
		END IF;
		--RAISE NOTICE 'Date Mois Prochain = % | nb Jours Mois = %',date_proc_mois,nb_jours_mois;
	ELSE
		IF date_last_cap IS NOT NULL THEN
			tmp_date := date_last_cap;
		ELSIF date_ouv IS NOT NULL THEN
			tmp_date := date_ouv;
		END IF;

		IF (date_last_cap IS NOT NULL) OR (date_ouv IS NOT NULL) THEN
			SELECT INTO nb_jours date_cap - tmp_date;
		END IF;
	END IF;

	RETURN nb_jours;
END;
$BODY$
  LANGUAGE plpgsql VOLATILE
  COST 100;
ALTER FUNCTION getperiodecapitalisation(date, date, date, date)
  OWNER TO postgres;
