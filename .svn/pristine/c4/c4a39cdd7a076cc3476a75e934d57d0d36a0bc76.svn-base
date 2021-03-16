-----------------------------------------------Debut REL-104-----------------------------------------------------------
--Retour Pascal : Point 7 - Le rapport d’execution budgétaire est erroné si une nouvelle table de correspondance est
-- créée sans utiliser la table de correspondance existante
-- Function: etat_execution_budget_complet(integer, integer, date, date)

-- DROP FUNCTION etat_execution_budget_complet(integer, integer, date, date);

CREATE OR REPLACE FUNCTION etat_execution_budget_complet(
    integer,
    integer,
    date,
    date)
  RETURNS SETOF rapport_etat_exe_complet AS
$BODY$
	DECLARE
	p_id_exo ALIAS FOR $1;
	p_type_budget ALIAS FOR $2;
	p_date_debut ALIAS FOR $3;
	p_date_fin ALIAS FOR $4;

	nom_poste text;
	v_poste_concat text;
	v_budget_annuel_cumul numeric(30,6);
	v_budget_period_cumul numeric(30,6);
	v_realisation_periode numeric(30,6);
	v_performance_periode double precision;
	v_performance_annuel double precision;

	ligne_rapport_complet rapport_etat_exe_complet;

	curs_autre_poste refcursor;
	ligne_autre_poste RECORD;

	curs_calcul refcursor;
	ligne_calcul RECORD;


	output_result integer := 1;
	BEGIN

	OPEN curs_autre_poste FOR SELECT c.poste_principal, c.poste_niveau_1, c.poste_niveau_2, c.poste_niveau_3, c.description
	from ad_correspondance  c inner join ad_budget b on c.ref_budget = b.ref_budget
	where c.dernier_niveau = 'f' and c.etat_correspondance = 't' and c.type_budget = p_type_budget and b.exo_budget = p_id_exo
	order by coalesce(c.poste_principal,0), coalesce(c.poste_niveau_1,0), coalesce(c.poste_niveau_2,0), coalesce(c.poste_niveau_3,0) asc;

	FETCH curs_autre_poste INTO ligne_autre_poste ;
	WHILE FOUND LOOP
	v_performance_periode = 0;
	v_performance_annuel = 0;

	IF (ligne_autre_poste.poste_principal IS NOT null ) AND (ligne_autre_poste.poste_niveau_1 IS NULL) AND (ligne_autre_poste.poste_niveau_2 IS NULL) AND (ligne_autre_poste.poste_niveau_3 IS NULL) THEN
		v_poste_concat = ligne_autre_poste.poste_principal||'.%' ;
		--RAISE NOTICE 'test =>%',v_poste_concat;
		nom_poste = ligne_autre_poste.poste_principal;

	END IF;
	IF (ligne_autre_poste.poste_principal IS NOT null ) AND (ligne_autre_poste.poste_niveau_1 IS NOT NULL) AND (ligne_autre_poste.poste_niveau_2 IS NULL) AND (ligne_autre_poste.poste_niveau_3 IS NULL) THEN
		v_poste_concat = ligne_autre_poste.poste_principal||'.'||ligne_autre_poste.poste_niveau_1||'.%';
		nom_poste = ligne_autre_poste.poste_principal||'.'||ligne_autre_poste.poste_niveau_1;
	END IF;
	IF (ligne_autre_poste.poste_principal IS NOT null ) AND (ligne_autre_poste.poste_niveau_1 IS NOT NULL) AND (ligne_autre_poste.poste_niveau_2 IS NOT NULL) AND (ligne_autre_poste.poste_niveau_3 IS NULL) THEN
		v_poste_concat = ligne_autre_poste.poste_principal||'.'||ligne_autre_poste.poste_niveau_1||'.'||ligne_autre_poste.poste_niveau_2||'.%';
		nom_poste = ligne_autre_poste.poste_principal||'.'||ligne_autre_poste.poste_niveau_1||'.'||ligne_autre_poste.poste_niveau_2;
	END IF;

		--OPEN curs_calcul FOR SELECT * from etat_execution_budget(p_id_exo,p_type_budget,p_date_debut, p_date_fin) where poste LIKE v_poste_concat;
		OPEN curs_calcul FOR SELECT * from temp_table_etat_budget where poste LIKE v_poste_concat;
		v_budget_annuel_cumul = 0;
		v_budget_period_cumul = 0;
		v_realisation_periode = 0;

		FETCH curs_calcul INTO ligne_calcul ;
		WHILE FOUND LOOP
		--RAISE NOTICE 'Poste = > % ---- description => % ----- budget Annuel=>% ----- budget periode = >% ----- reali period=>% ------ Perf period =>% ------ Perf annuelle =>%', ligne_calcul.poste, ligne_calcul.description, ligne_calcul.budget_annuel, ligne_calcul.budget_periode, ligne_calcul.realisation_period, ligne_calcul.performance_period, ligne_calcul.performance_annuelle;
		--RAISE NOTICE '---------------------------------------------------------------------------------------------------------------------------';
		--RAISE NOTICE 'les sous postes sont =>%', ligne_calcul.poste;

		-- budget annuel
		v_budget_annuel_cumul = v_budget_annuel_cumul + ligne_calcul.budget_annuel;
		-- budget de la periode
		v_budget_period_cumul = v_budget_period_cumul + ligne_calcul.budget_periode;
		-- Realisation de la periode
		v_realisation_periode = v_realisation_periode + ligne_calcul.realisation_period;

		FETCH curs_calcul INTO ligne_calcul;
		END LOOP;
		CLOSE curs_calcul;

		-- Performance de la periode
		IF (v_budget_period_cumul = 0) THEN
		v_performance_periode = 0;
		ELSE
		v_performance_periode = ROUND((v_realisation_periode/v_budget_period_cumul)*100,2);
		END IF;

		IF (v_budget_annuel_cumul = 0) THEN
		v_performance_annuel = 0;
		ELSE
		-- performance annuel
		v_performance_annuel = ROUND((v_realisation_periode/v_budget_annuel_cumul)*100,2);
		END IF;

		SELECT INTO ligne_rapport_complet nom_poste, ligne_autre_poste.description, v_budget_annuel_cumul,v_budget_period_cumul,v_realisation_periode,v_performance_periode,v_performance_annuel;
		RETURN NEXT ligne_rapport_complet;

	FETCH curs_autre_poste INTO ligne_autre_poste;
	END LOOP;
	CLOSE curs_autre_poste;

	  RETURN;
	END;
	$BODY$
  LANGUAGE plpgsql VOLATILE
  COST 100
  ROWS 1000;
ALTER FUNCTION etat_execution_budget_complet(integer, integer, date, date)
  OWNER TO postgres;

--Retour Pascal : Point 3 pour le rapport Bugget - Pour le rapport d’execution budgetaire et rapport budget, si aucun
-- budget n’a été mis en place, il faudrait donner l’arte disant qu’aucun budget n’est disponible au lieu d’afficher une
--  page blanche ou rapport avec valeurs zero
-- Function: get_budget_complet(integer, integer, text, date, date)

-- DROP FUNCTION get_budget_complet(integer, integer, text, date, date);

CREATE OR REPLACE FUNCTION get_budget_complet(
    integer,
    integer,
    text,
    date,
    date)
  RETURNS SETOF rapport_budget_complet AS
$BODY$
	DECLARE
	p_exo_budget ALIAS FOR $1;
	p_type_budget ALIAS FOR $2;
	p_ref_budget ALIAS FOR $3;
	p_date_debut ALIAS FOR $4;
	p_date_fin ALIAS FOR $5;

	nom_poste text;
	v_poste_concat text;
	v_trim1_cumul numeric(30,6);
	v_trim2_cumul numeric(30,6);
	v_trim3_cumul numeric(30,6);
	v_trim4_cumul numeric(30,6);

	v_cpte_comptable text;

	ligne_rapport_complet rapport_budget_complet;

	curs_autre_poste refcursor;
	ligne_autre_poste RECORD;

	curs_calcul refcursor;
	ligne_calcul RECORD;


	output_result integer := 1;
	BEGIN

	OPEN curs_autre_poste FOR SELECT c.id ,c.poste_principal, c.poste_niveau_1, c.poste_niveau_2, c.poste_niveau_3, c.description, c.compartiment
	from ad_correspondance  c inner join ad_budget b on c.ref_budget = b.ref_budget
	--INNER JOIN ad_budget_cpte_comptable p ON p.id_ligne = c.id
	where c.dernier_niveau = 'f' and c.etat_correspondance = 't' and c.type_budget = p_type_budget and b.ref_budget =p_ref_budget and b.etat_budget != 6 and b.exo_budget = p_exo_budget
	order by coalesce(c.poste_principal,0), coalesce(c.poste_niveau_1,0), coalesce(c.poste_niveau_2,0), coalesce(c.poste_niveau_3,0) asc;

	FETCH curs_autre_poste INTO ligne_autre_poste ;
	WHILE FOUND LOOP

	IF (ligne_autre_poste.poste_principal IS NOT null ) AND (ligne_autre_poste.poste_niveau_1 IS NULL) AND (ligne_autre_poste.poste_niveau_2 IS NULL) AND (ligne_autre_poste.poste_niveau_3 IS NULL) THEN
		v_poste_concat = ligne_autre_poste.poste_principal||'.%' ; RAISE NOTICE 'test =>%',v_poste_concat;
		nom_poste = ligne_autre_poste.poste_principal;

	END IF;
	IF (ligne_autre_poste.poste_principal IS NOT null ) AND (ligne_autre_poste.poste_niveau_1 IS NOT NULL) AND (ligne_autre_poste.poste_niveau_2 IS NULL) AND (ligne_autre_poste.poste_niveau_3 IS NULL) THEN
		v_poste_concat = ligne_autre_poste.poste_principal||'.'||ligne_autre_poste.poste_niveau_1||'.%';
		nom_poste = ligne_autre_poste.poste_principal||'.'||ligne_autre_poste.poste_niveau_1;
	END IF;
	IF (ligne_autre_poste.poste_principal IS NOT null ) AND (ligne_autre_poste.poste_niveau_1 IS NOT NULL) AND (ligne_autre_poste.poste_niveau_2 IS NOT NULL) AND (ligne_autre_poste.poste_niveau_3 IS NULL) THEN
		v_poste_concat = ligne_autre_poste.poste_principal||'.'||ligne_autre_poste.poste_niveau_1||'.'||ligne_autre_poste.poste_niveau_2||'.%';
		nom_poste = ligne_autre_poste.poste_principal||'.'||ligne_autre_poste.poste_niveau_1||'.'||ligne_autre_poste.poste_niveau_2;
	END IF;

		OPEN curs_calcul FOR SELECT * from get_budget(p_exo_budget, p_type_budget,p_date_debut, p_date_fin) where poste LIKE v_poste_concat;
		v_trim1_cumul = 0;
		v_trim2_cumul = 0;
		v_trim3_cumul = 0;
		v_trim4_cumul = 0;

		FETCH curs_calcul INTO ligne_calcul ;
		WHILE FOUND LOOP
		--RAISE NOTICE 'les sous postes sont =>%', ligne_calcul.poste;

		-- budget annuel
		v_trim1_cumul = v_trim1_cumul + coalesce(ligne_calcul.trim_1,0);
		v_trim2_cumul = v_trim2_cumul + coalesce(ligne_calcul.trim_2,0);
		v_trim3_cumul = v_trim3_cumul + coalesce(ligne_calcul.trim_3,0);
		v_trim4_cumul = v_trim4_cumul + coalesce(ligne_calcul.trim_4,0);



		FETCH curs_calcul INTO ligne_calcul;
		END LOOP;
		CLOSE curs_calcul;


		select into v_cpte_comptable array_to_string(array_agg(cpte_comptable),' - ') from ad_budget_cpte_comptable where id_ligne = ligne_autre_poste.id;

		SELECT INTO ligne_rapport_complet ligne_autre_poste.id, nom_poste, ligne_autre_poste.description,ligne_autre_poste.compartiment,v_cpte_comptable, v_trim1_cumul,v_trim2_cumul,v_trim3_cumul,v_trim4_cumul;
		RETURN NEXT ligne_rapport_complet;

	FETCH curs_autre_poste INTO ligne_autre_poste;
	END LOOP;
	CLOSE curs_autre_poste;


	  RETURN;
	END;
	$BODY$
  LANGUAGE plpgsql VOLATILE
  COST 100
  ROWS 1000;
ALTER FUNCTION get_budget_complet(integer, integer, text, date, date)
  OWNER TO postgres;

-----------------------------------------------Fin REL-104-------------------------------------------------------------

-----------------------------------------------Debut AT-141-------------------------------------------------------------

CREATE OR REPLACE FUNCTION ticket_AT_141() RETURNS INT AS
$BODY$
DECLARE
output_result INTEGER = 1;
column_exist INTEGER = 0;
BEGIN

 -- Create the column traduction in adsys_infos_systeme if it does not exist
 SELECT INTO column_exist count(*) from information_schema.columns WHERE table_name = 'ad_retrait_attente' AND column_name='devise';
  IF (column_exist = 0)  THEN
    ALTER TABLE ad_retrait_attente ADD COLUMN devise character(3);
  END IF;

 SELECT INTO column_exist count(*) from information_schema.columns WHERE table_name = 'ad_retrait_attente' AND column_name='mnt_devise';
  IF (column_exist = 0)  THEN
    ALTER TABLE ad_retrait_attente ADD COLUMN mnt_devise numeric(30,6);
  END IF;

   SELECT INTO column_exist count(*) from information_schema.columns WHERE table_name = 'ad_retrait_attente' AND column_name='mnt_reste';
  IF (column_exist = 0)  THEN
    ALTER TABLE ad_retrait_attente ADD COLUMN mnt_reste numeric(30,6);
  END IF;

   SELECT INTO column_exist count(*) from information_schema.columns WHERE table_name = 'ad_retrait_attente' AND column_name='taux_devise';
  IF (column_exist = 0)  THEN
    ALTER TABLE ad_retrait_attente ADD COLUMN taux_devise double precision;
  END IF;

   SELECT INTO column_exist count(*) from information_schema.columns WHERE table_name = 'ad_retrait_attente' AND column_name='taux_commission';
  IF (column_exist = 0)  THEN
    ALTER TABLE ad_retrait_attente ADD COLUMN taux_commission numeric(30,6);
  END IF;

   SELECT INTO column_exist count(*) from information_schema.columns WHERE table_name = 'ad_retrait_attente' AND column_name='dest_reste';
  IF (column_exist = 0)  THEN
    ALTER TABLE ad_retrait_attente ADD COLUMN dest_reste integer;
  END IF;

RETURN output_result;
END;
$BODY$
  LANGUAGE plpgsql VOLATILE
  COST 100;
ALTER FUNCTION ticket_AT_141()
  OWNER TO postgres;

  SELECT ticket_AT_141();
  DROP FUNCTION IF EXISTS ticket_AT_141();

-----------------------------------------------Fin  AT-141-------------------------------------------------------------