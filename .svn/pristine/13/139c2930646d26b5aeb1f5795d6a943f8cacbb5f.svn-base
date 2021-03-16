CREATE OR REPLACE FUNCTION budget_function() RETURNS INT AS
$$
DECLARE
output_result INTEGER = 1;
v_id_profil INTEGER;

BEGIN
CREATE OR REPLACE FUNCTION calculutilisationbudget(
		date)
	  RETURNS integer AS
	$BODY$
	DECLARE

	-- date_deb a remplace par recuperation dans la requete de recup id_exo, la date de debut et la date de parametre comme date de fin
	date_jour ALIAS FOR $1;

	v_id_exo integer :=0;
	v_date_debut_exo date;

	V_etat_revision integer :=0;

	v_id_ligne integer :=0;
	v_mnt_debit numeric(30,6) :=  0;
	v_mnt_credit numeric(30,6) := 0;
	v_mnt_debit_cumul numeric(30,6) :=  0;
	v_mnt_credit_cumul numeric(30,6) :=  0;
	v_stock_montant numeric(30,6) :=0;
	v_mnt_total numeric(30,6) :=0;
	v_trim integer :=0;
	v_mnt_trim numeric(30,6) :=0;

	v_prc_utiliser double precision default 0;

	curr_cpte refcursor;
	ligne_budget RECORD;

	output_result INTEGER :=0;
	BEGIN

		SELECT INTO v_id_exo, v_date_debut_exo  id_exo_compta,date_deb_exo FROM ad_exercices_compta WHERE id_ag=NumAgc() and date_deb_exo <= date_jour
		AND date_jour <= date_fin_exo;

		-- CURSOR recuperant les informations pour chaque ligne budgetaire ainsi que les compte comptable associer a chaque poste
		OPEN curr_cpte FOR SELECT l.id_ligne, b.type_budget, l.id_correspondance, l.poste_budget, p.compartiment, c.cpte_comptable, l.mnt_trim1, l.mnt_trim2, l.mnt_trim3, l.mnt_trim4
		FROM ad_ligne_budgetaire l
		INNER JOIN ad_budget_cpte_comptable c ON c.id_ligne = l.id_correspondance
		INNER JOIN ad_budget b ON b.ref_budget = l.ref_budget
		INNER JOIN ad_correspondance p ON p.id = c.id_ligne
		WHERE b.exo_budget = v_id_exo and b.etat_budget >= 3
		--where l.id_ligne in (4)
		order by b.type_budget, l.id_ligne ;

		FETCH curr_cpte INTO ligne_budget;
		WHILE FOUND LOOP

		--SELECT INTO v_etat_revision COUNT(id_revision) from ad_revision_historique where id_ligne_budget = ligne_budget.id_ligne and etat_revision = 1 and exo_budget = v_id_exo;
		--IF (v_etat_revision > 0) THEN
		--FETCH NEXT FROM curr_cpte INTO ligne_budget;
		--ELSE
		-- on fait un RAZ des variable pour chaque ligne budgetaire
		IF (ligne_budget.id_ligne != v_id_ligne) THEN
			v_id_ligne = ligne_budget.id_ligne;
			v_stock_montant = 0;
			v_mnt_debit = 0;
			v_mnt_credit = 0;
			v_mnt_debit_cumul = 0;
			v_mnt_credit_cumul = 0;
			v_mnt_total = 0;
			v_trim = 0;
		END IF;

		-- Recuperation du trimestre en cours
		select into v_trim getTrimestre(date_jour);
		--RAISE NOTICE 'Trimestre mnt_trim%', v_trim;

		-- Recuperation du montant budgetaire dependant du trimestres ou l on se situe ( cumulation des trimestres )
		v_mnt_trim :=-1;
		SELECT into v_mnt_trim
		CASE
		WHEN v_trim = 1 THEN sum(mnt_trim1)
		WHEN v_trim = 2 THEN sum(mnt_trim1) + sum(mnt_trim2)
		WHEN v_trim = 3 THEN sum(mnt_trim1) + sum(mnt_trim2) + sum(mnt_trim3)
		WHEN v_trim = 4 THEN sum(mnt_trim1) + sum(mnt_trim2) + sum(mnt_trim4) + sum(mnt_trim4)
		END AS Trimestre
		from ad_ligne_budgetaire where id_ligne = v_id_ligne;
		--RAISE NOTICE 'somme des trim => %', v_mnt_trim;

		-- debut des conditions dependants des compartiments
		IF (ligne_budget.compartiment = 1) THEN
		-- compartiment 1 : Actifs
			--RAISE NOTICE 'Compartiment 1  id => % et le montant est de montant=> %',v_id_ligne, v_stock_montant;
			v_mnt_debit := 0;
			SELECT INTO v_mnt_debit coalesce(sum(montant),0) from ad_mouvement where compte = ligne_budget.cpte_comptable and sens = 'd' and date_valeur >= date(v_date_debut_exo) and date_valeur <= date(date_jour);
			v_stock_montant = v_mnt_debit ;

		ELSIF (ligne_budget.compartiment = 2) THEN
		-- compartiment 2 : Passif
			v_mnt_credit := 0;
			SELECT INTO v_mnt_credit coalesce(sum(montant),0) from ad_mouvement where compte = ligne_budget.cpte_comptable and sens = 'c' and date_valeur >= date(v_date_debut_exo) and date_valeur <= date(date_jour);
			v_stock_montant = v_mnt_credit ;

		ELSIF (ligne_budget.compartiment = 3) THEN
		-- compartiement3 : Charge
			v_mnt_debit := 0;
			v_mnt_credit := 0;
			SELECT INTO v_mnt_debit coalesce(sum(montant),0) from ad_mouvement where compte = ligne_budget.cpte_comptable and sens = 'd' and date_valeur >= date(v_date_debut_exo) and date_valeur <= date(date_jour);
			SELECT INTO v_mnt_credit coalesce(sum(montant),0) from ad_mouvement where compte = ligne_budget.cpte_comptable and sens = 'c' and date_valeur >= date(v_date_debut_exo) and date_valeur <= date(date_jour);
			-- formule pour les charges : total des debits - total des credits
			v_stock_montant := v_mnt_debit - v_mnt_credit ;


			--RAISE NOTICE 'id ligne => % --- poste => % ---- montant debit => % ---- montant credit => % ---',ligne_budget.id_ligne, ligne_budget.cpte_comptable,  v_mnt_debit, v_mnt_credit;

		ELSIF (ligne_budget.compartiment = 4) THEN
		-- compartiment 4 : Produit
			v_mnt_debit := 0;
			v_mnt_credit := 0;
			SELECT INTO v_mnt_debit coalesce(sum(montant),0) from ad_mouvement where compte = ligne_budget.cpte_comptable and sens = 'd' and date_valeur >= date(v_date_debut_exo) and date_valeur <= date(date_jour);
			SELECT INTO v_mnt_credit coalesce(sum(montant),0) from ad_mouvement where compte = ligne_budget.cpte_comptable and sens = 'c' and date_valeur >= date(v_date_debut_exo) and date_valeur <= date(date_jour);
			-- formule pour les compte de produit : total des credit - total des debit
			v_stock_montant :=  v_mnt_credit - v_mnt_debit;

			--RAISE NOTICE 'id ligne => % --- poste => % ---- montant debit => % ---- montant credit => % ---',ligne_budget.id_ligne, ligne_budget.cpte_comptable,  v_mnt_debit, v_mnt_credit;

		END IF;

		-- Cumulation des totals dependant de la ligne budgetaire
		v_mnt_total := v_mnt_total + v_stock_montant;
		--RAISE NOTICE ' montant v_stovk =>  %', v_stock_montant;

		-- Verification si le montant des totaux et trimestres sont superieur a 0, on fait le calcul du % utilisee
		IF (v_mnt_total != 0 AND v_mnt_trim != 0) THEN
			v_prc_utiliser := 0;
			--RAISE NOTICE 'Pourcentage avant %', v_prc_utiliser;
			-- Calcul du % utilisé
			v_prc_utiliser := (v_mnt_total / v_mnt_trim ) *100;

			-- Verification si le % depasse les 100
			IF (v_prc_utiliser > 100) THEN
				v_prc_utiliser := 100.00;
			END IF;
			--RAISE NOTICE 'Ligne budgetaire => % ----- Compte comptable => % ------ montant total utilisee=>  % ----- montant budgete => % ----- utiliser=> %',ligne_budget.id_ligne,  ligne_budget.cpte_comptable, v_mnt_total, v_mnt_trim, v_prc_utiliser;

			-- Mise a jour du champs % dans les ligne correspondante
			UPDATE ad_ligne_budgetaire SET prc_utilisation_trim1 = v_prc_utiliser WHERE v_trim = 1 and id_ligne = ligne_budget.id_ligne;
			UPDATE ad_ligne_budgetaire SET prc_utilisation_trim2 = v_prc_utiliser WHERE v_trim = 2 and id_ligne = ligne_budget.id_ligne;
			UPDATE ad_ligne_budgetaire SET prc_utilisation_trim3 = v_prc_utiliser WHERE v_trim = 3 and id_ligne = ligne_budget.id_ligne;
			UPDATE ad_ligne_budgetaire SET prc_utilisation_trim4 = v_prc_utiliser WHERE v_trim = 4 and id_ligne = ligne_budget.id_ligne;
		END IF;

		--END IF;
		FETCH curr_cpte INTO ligne_budget;
		END LOOP;

		CLOSE curr_cpte;

		RETURN output_result;
	END;
	$BODY$
	  LANGUAGE plpgsql VOLATILE
	  COST 100;
	ALTER FUNCTION calculutilisationbudget(date)
	  OWNER TO adbanking;
	 /* ----------------------FIN function calculutilisationbudget --------------------------------------------------------------------------------- */

/***********************************************************************************************************************************************/

CREATE OR REPLACE FUNCTION BlockCompteBudget(date)
RETURNS integer AS
$BODY$
DECLARE

date_du_jour ALIAS FOR $1;

v_prc_utilise double precision;
v_trimestre integer;
v_ligne_block integer;
v_etat_block boolean;

v_prc_util integer :=0;

curr_block refcursor;
ligne_block RECORD;

curr_cpte refcursor;
ligne_cpte RECORD;



output_result integer := 1;
BEGIN
select into v_trimestre getTrimestre(date_du_jour);
--RAISE NOTICE 'Trimestre mnt_trim%', v_trimestre;
IF (v_trimestre = 1) THEN
OPEN curr_block FOR
SELECT b.id_ligne , b.etat_bloque , b.id_correspondance, prc_utilisation_trim1 as prc_trim FROM ad_ligne_budgetaire  b INNER JOIN ad_budget t on t.ref_budget = b.ref_budget WHERE  t.etat_budget >= 3 and b.etat_bloque = 't';
ELSIF (v_trimestre = 2) THEN
OPEN curr_block FOR
SELECT b.id_ligne , b.etat_bloque , b.id_correspondance, prc_utilisation_trim2 as prc_trim FROM ad_ligne_budgetaire  b INNER JOIN ad_budget t on t.ref_budget = b.ref_budget WHERE  t.etat_budget >= 3 and b.etat_bloque = 't';
ELSIF (v_trimestre = 3) THEN
OPEN curr_block FOR
SELECT b.id_ligne , b.etat_bloque , b.id_correspondance, prc_utilisation_trim3 as prc_trim FROM ad_ligne_budgetaire  b INNER JOIN ad_budget t on t.ref_budget = b.ref_budget WHERE  t.etat_budget >= 3 and b.etat_bloque = 't';
ELSE
OPEN curr_block FOR
SELECT b.id_ligne , b.etat_bloque , b.id_correspondance, prc_utilisation_trim4 as prc_trim FROM ad_ligne_budgetaire  b INNER JOIN ad_budget t on t.ref_budget = b.ref_budget WHERE  t.etat_budget >= 3 and b.etat_bloque = 't';
END IF;


--INNER JOIN ad_revision_historique r ON b.id_ligne = r.id_ligne_budget


FETCH curr_block INTO ligne_block ;
WHILE FOUND LOOP

	IF (ligne_block.prc_trim >= 100) THEN
	RAISE NOTICE 'ligne => % ---- prc = >%', ligne_block.id_ligne ,ligne_block.prc_trim ;

	OPEN curr_cpte FOR
	SELECT cpte_comptable from ad_budget_cpte_comptable where id_ligne = ligne_block.id_correspondance;
	FETCH curr_cpte INTO ligne_cpte ;
	WHILE FOUND LOOP
	RAISE NOTICE 'cpte comptable=> % ---- Trimestre => %',ligne_cpte.cpte_comptable,ligne_block.prc_trim  ;
	UPDATE ad_cpt_comptable set etat_cpte = 2, is_actif = 'f' where num_cpte_comptable = ligne_cpte.cpte_comptable;

	FETCH curr_cpte INTO ligne_cpte;
	END LOOP;
	CLOSE curr_cpte;

	SELECT INTO v_ligne_block, v_etat_block ligne_budgetaire, cpte_bloquer from ad_budget_cpt_bloquer where ligne_budgetaire = ligne_block.id_ligne order by id_bloc desc limit 1;
	RAISE NOTICE 'Ligne budget => % ----- Etat block =>%',v_ligne_block, v_etat_block;
	IF ((v_ligne_block IS null) OR (v_ligne_block IS NOT null AND v_etat_block IS false)) THEN
	RAISE NOTICE ' in here';
	INSERT INTO ad_budget_cpt_bloquer(ligne_budgetaire, cpte_comptable, cpte_bloquer, date_creation, id_ag) VALUES (ligne_block.id_ligne, (select array_to_string(array_agg(cpte_comptable),' - ') from ad_budget_cpte_comptable where id_ligne = ligne_block.id_correspondance), 't',date(now()),numagc());
	END IF;

	END IF;
FETCH curr_block INTO ligne_block;
END LOOP;
CLOSE curr_block;

RETURN output_result;
END;
$BODY$
  LANGUAGE plpgsql VOLATILE
  COST 100;
ALTER FUNCTION BlockCompteBudget(date)
  OWNER TO adbanking;

/***************************************************************************************************************************************/

CREATE OR REPLACE FUNCTION getTrimestre(date)
RETURNS integer AS
$BODY$
DECLARE

input_date ALIAS FOR $1;

debut_exo date;
tmp1_date date;
tmp2_date date;

trimestre integer;


BEGIN

	SELECT INTO debut_exo date_deb_exo FROM ad_exercices_compta WHERE id_ag=NumAgc() and date_deb_exo <= input_date
	AND input_date <= date_fin_exo;

	-- VERIFIE SI ON EST DANS LE PREMIER TRIMESTRE DE L'EXERCICE
	SELECT INTO tmp1_date debut_exo + interval '3 month';
 	IF input_date < tmp1_date THEN
		trimestre = 1;
	END IF;

	-- VERIFIE SI ON EST DANS LE SECOND TRIMESTRE DE L'EXERCICE
	SELECT INTO tmp1_date debut_exo + interval '3 month';
	SELECT INTO tmp2_date debut_exo + interval '6 month';

 	IF input_date >= tmp1_date AND input_date < tmp2_date THEN
		trimestre = 2;
	END IF;

	-- VERIFIE SI ON EST DANS LE TROISIEME TRIMESTRE DE L'EXERCICE
	SELECT INTO tmp1_date debut_exo + interval '6 month';
	SELECT INTO tmp2_date debut_exo + interval '9 month';

 	IF input_date >= tmp1_date AND input_date < tmp2_date THEN
		trimestre = 3;
	END IF;

	-- VERIFIE SI ON EST DANS LE QUATRIEME TRIMESTRE DE L'EXERCICE
	SELECT INTO tmp1_date debut_exo + interval '9 month';
	SELECT INTO tmp2_date debut_exo + interval '12 month';

 	IF input_date >= tmp1_date AND input_date < tmp2_date THEN
		trimestre = 4;
	END IF;

	RETURN trimestre;

END;
$BODY$
  LANGUAGE plpgsql VOLATILE
  COST 100;
ALTER FUNCTION getTrimestre(date)
  OWNER TO adbanking;


  DROP TYPE IF EXISTS rapport_etat_exe CASCADE;
  DROP FUNCTION IF EXISTS  etat_execution_budget(integer, date, date) CASCADE;

 CREATE TYPE rapport_etat_exe AS (
 poste text,
 description text,
 budget_annuel numeric(30,6),
 budget_periode numeric(30,6),
 realisation_period numeric(30,6),
 performance_period double precision,
 performance_annuelle double precision
);



-- Function: etat_execution_budget(integer, integer, date, date)

-- DROP FUNCTION etat_execution_budget(integer, integer, date, date);

CREATE OR REPLACE FUNCTION etat_execution_budget(
    integer,
    integer,
    date,
    date)
  RETURNS SETOF rapport_etat_exe AS
$BODY$
	DECLARE
	p_id_exo ALIAS FOR $1;
	p_type_budget ALIAS FOR $2;
	p_date_debut ALIAS FOR $3;
	p_date_fin ALIAS FOR $4;

	v_trimestre_debut integer;
	v_trimestre_fin integer;
	v_exo_budget integer;

	v_id_ligne integer :=0;
	v_mnt_debit numeric(30,6) :=  0;
	v_mnt_credit numeric(30,6) := 0;
	v_mnt_debit_cumul numeric(30,6) :=  0;
	v_mnt_credit_cumul numeric(30,6) :=  0;
	v_stock_montant numeric(30,6) :=0;
	v_mnt_total numeric(30,6) :=0;
	v_trim integer :=0;
	v_mnt_trim numeric(30,6) :=0;

	v_realisation_period numeric(30,6) :=0;
	v_performance_periode double precision default 0;
	v_performande_annuel double precision default 0;

	ligne_rapport rapport_etat_exe;

	liste_poste refcursor;
	ligne_poste RECORD;

	liste_compte refcursor;
	ligne_compte RECORD;

	output_result integer := 1;
	BEGIN
	select into v_trimestre_debut getTrimestre(p_date_debut);
	select into v_trimestre_fin getTrimestre(p_date_fin);

	select into v_exo_budget id_exo_compta from ad_exercices_compta where id_exo_compta = p_id_exo order by id_exo_compta desc limit 1;

	OPEN liste_poste FOR
	SELECT l.id_ligne, l.poste_budget, c.description, c.compartiment,
	CASE
	WHEN v_trimestre_debut = 1 AND v_trimestre_fin = 1 THEN sum(l.mnt_trim1)
	WHEN v_trimestre_debut = 1 AND v_trimestre_fin = 2 THEN sum(l.mnt_trim1 + l.mnt_trim2)
	WHEN v_trimestre_debut = 1 AND v_trimestre_fin = 3 THEN sum(l.mnt_trim1 + l.mnt_trim2 + l.mnt_trim3)
	WHEN v_trimestre_debut = 1 AND v_trimestre_fin = 4 THEN sum(l.mnt_trim1 + l.mnt_trim2 + l.mnt_trim3 + l.mnt_trim4)
	WHEN v_trimestre_debut = 2 AND v_trimestre_fin = 2 THEN sum(l.mnt_trim2)
	WHEN v_trimestre_debut = 2 AND v_trimestre_fin = 3 THEN sum(l.mnt_trim2 + l.mnt_trim3)
	WHEN v_trimestre_debut = 2 AND v_trimestre_fin = 4 THEN sum(l.mnt_trim2 + l.mnt_trim3 + l.mnt_trim4)
	WHEN v_trimestre_debut = 3 AND v_trimestre_fin = 3 THEN sum(l.mnt_trim3)
	WHEN v_trimestre_debut = 3 AND v_trimestre_fin = 4 THEN sum(l.mnt_trim3 + l.mnt_trim4)
	WHEN v_trimestre_debut = 4 AND v_trimestre_fin = 4 THEN sum(l.mnt_trim4)
	END
	 AS budget_period,
	 (sum(l.mnt_trim1 + l.mnt_trim2 + l.mnt_trim3 + l.mnt_trim4)) as budget_annuel
	FROM ad_ligne_budgetaire l
	INNER JOIN ad_correspondance c on c.id = l.id_correspondance
	INNER JOIN ad_budget b on b.ref_budget = l.ref_budget
	WHERE b.exo_budget= v_exo_budget and b.type_budget = p_type_budget and l.etat_ligne = 2
	GROUP BY l.id_ligne,l.poste_budget, c.description,c.compartiment;

	FETCH liste_poste INTO ligne_poste ;
	WHILE FOUND LOOP
	v_realisation_period = 0;
	v_performance_periode = 0;
	v_performande_annuel = 0;

		OPEN liste_compte FOR select b.id_ligne, cpte.cpte_comptable from ad_ligne_budgetaire b
		INNER JOIN ad_budget_cpte_comptable cpte on cpte.id_ligne = b.id_correspondance
		where b.id_ligne = ligne_poste.id_ligne;

		FETCH liste_compte INTO ligne_compte ;
		WHILE FOUND LOOP
		-- On va calculer la somme pour chaque ligne budgetaire
		IF (ligne_compte.id_ligne != v_id_ligne) THEN
		v_id_ligne = ligne_compte.id_ligne;
		v_stock_montant = 0;
		v_mnt_debit = 0;
		v_mnt_credit = 0;
		v_mnt_debit_cumul = 0;
		v_mnt_credit_cumul = 0;
		v_mnt_total = 0;
		v_trim = 0;
		END IF;

		IF (ligne_poste.compartiment = 1) THEN
		-- compartiment 1 : Actifs
			v_mnt_debit = 0;
			SELECT INTO v_mnt_debit coalesce(sum(montant),0) from ad_mouvement where compte = ligne_compte.cpte_comptable and sens = 'd' and date_valeur >= date(p_date_debut) and date_valeur <= date(p_date_fin);
			v_stock_montant = v_mnt_debit ;

		ELSIF (ligne_poste.compartiment = 2) THEN
		-- compartiment 2 : Passif
			v_mnt_credit = 0;
			SELECT INTO v_mnt_credit coalesce(sum(montant),0) from ad_mouvement where compte = ligne_compte.cpte_comptable and sens = 'c' and date_valeur >= date(p_date_debut) and date_valeur <= date(p_date_fin);
			v_stock_montant = v_mnt_credit ;

		ELSIF (ligne_poste.compartiment = 3) THEN
		-- compartiement3 : Charge
			v_mnt_debit = 0;
			v_mnt_credit = 0;
			SELECT INTO v_mnt_debit coalesce(sum(montant),0) from ad_mouvement where compte = ligne_compte.cpte_comptable and sens = 'd' and date_valeur >= date(p_date_debut) and date_valeur <= date(p_date_fin);
			SELECT INTO v_mnt_credit coalesce(sum(montant),0) from ad_mouvement where compte = ligne_compte.cpte_comptable and sens = 'c' and date_valeur >= date(p_date_debut) and date_valeur <= date(p_date_fin);

			v_stock_montant = v_mnt_debit - v_mnt_credit ;

		ELSIF (ligne_poste.compartiment = 4) THEN
		-- compartiment 4 : Produit
			v_mnt_debit = 0;
			v_mnt_credit = 0;
			SELECT INTO v_mnt_debit coalesce(sum(montant),0) from ad_mouvement where compte = ligne_compte.cpte_comptable and sens = 'd' and date_valeur >= date(p_date_debut) and date_valeur <= date(p_date_fin);
			SELECT INTO v_mnt_credit coalesce(sum(montant),0) from ad_mouvement where compte = ligne_compte.cpte_comptable and sens = 'c' and date_valeur >= date(p_date_debut) and date_valeur <= date(p_date_fin);
			v_stock_montant =  v_mnt_credit - v_mnt_debit;
		END IF;
		v_mnt_total = v_mnt_total + v_stock_montant;

		FETCH liste_compte INTO ligne_compte;
		END LOOP;
		CLOSE liste_compte;
		v_realisation_period = ROUND(v_mnt_total,2);
		--RAISE NOTICE 'Poste => %',ligne_poste.poste_budget;
		--RAISE NOTICE 'budget Period=> %',ligne_poste.budget_period ;

		IF (ligne_poste.budget_period = 0) THEN
		v_performance_periode = 0;
		ELSE
		v_performance_periode = ROUND((v_realisation_period / ligne_poste.budget_period)*100, 2);
		END IF;

		IF (ligne_poste.budget_annuel = 0) THEN
		v_performande_annuel = 0;
		ELSE
		v_performande_annuel = ROUND((v_realisation_period / ligne_poste.budget_annuel)*100, 2);
		END IF;

		INSERT INTO temp_table_etat_budget (poste,description,budget_annuel,budget_periode,realisation_period,performance_period,performance_annuelle) VALUES (ligne_poste.poste_budget,ligne_poste.description,ligne_poste.budget_annuel,ligne_poste.budget_period,v_realisation_period, v_performance_periode, v_performande_annuel);

		SELECT INTO ligne_rapport ligne_poste.poste_budget,ligne_poste.description,ligne_poste.budget_annuel,ligne_poste.budget_period,v_realisation_period, v_performance_periode, v_performande_annuel;
		RETURN NEXT ligne_rapport;

	FETCH liste_poste INTO ligne_poste;
	END LOOP;
	CLOSE liste_poste;

	  RETURN;
	END;
	$BODY$
  LANGUAGE plpgsql VOLATILE
  COST 100
  ROWS 1000;
ALTER FUNCTION etat_execution_budget(integer, integer, date, date)
  OWNER TO postgres;


 DROP TYPE IF EXISTS rapport_etat_exe_complet CASCADE;
 DROP FUNCTION IF EXISTS etat_execution_budget_complet(integer, date, date) CASCADE;

CREATE TYPE rapport_etat_exe_complet AS (
 poste text,
 description text,
 budget_annuel numeric(30,6),
 budget_periode numeric(30,6),
 realisation_period numeric(30,6),
 performance_period double precision,
 performance_annuelle double precision
);


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

	OPEN curs_autre_poste FOR SELECT poste_principal, poste_niveau_1, poste_niveau_2, poste_niveau_3, description
	from ad_correspondance  c
	where dernier_niveau = 'f' and etat_correspondance = 't' and type_budget = p_type_budget
	order by coalesce(poste_principal,0), coalesce(poste_niveau_1,0), coalesce(poste_niveau_2,0), coalesce(poste_niveau_3,0) asc;

	FETCH curs_autre_poste INTO ligne_autre_poste ;
	WHILE FOUND LOOP
	v_performance_periode = 0;
	v_performance_annuel = 0;

	IF (ligne_autre_poste.poste_principal IS NOT null ) AND (ligne_autre_poste.poste_niveau_1 IS NULL) AND (ligne_autre_poste.poste_niveau_2 IS NULL) AND (ligne_autre_poste.poste_niveau_3 IS NULL) THEN
		v_poste_concat = ligne_autre_poste.poste_principal||'.%' ; --RAISE NOTICE 'test =>%',v_poste_concat;
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



	/* ----------------------Debut Type rapport_budget et function get_budget---------------------------------------------------------------- */

	DROP TYPE IF EXISTS rapport_budget CASCADE;
	DROP FUNCTION IF EXISTS  get_budget(integer,integer, date, date) CASCADE;

	create type rapport_budget as (
	id_correspondance integer,
	poste text,
	description text,
	compartiment integer,
	cpte_comptable text,
	trim_1 numeric(30,6),
	trim_2 numeric(30,6),
	trim_3 numeric(30,6),
	trim_4 numeric(30,6));


	CREATE OR REPLACE FUNCTION get_budget(
		integer,
		integer,
		date,
		date)
	  RETURNS SETOF rapport_budget AS
	$BODY$
	DECLARE
	p_exo_budget ALIAS FOR $1;
	p_type_budget ALIAS FOR $2;
	p_date_debut ALIAS FOR $3;
	p_date_fin ALIAS FOR $4;

	ligne_rapport rapport_budget;

	liste_bud refcursor;
	ligne_bud RECORD;

	output_result integer := 1;
	BEGIN
		OPEN liste_bud FOR select c.id, b.poste_budget, c.description, c.compartiment, (select array_to_string(array_agg(cpte_comptable),' - ') from ad_budget_cpte_comptable where id_ligne = c.id) as cpte_comptable, b.mnt_trim1, b.mnt_trim2, b.mnt_trim3, b.mnt_trim4  from ad_ligne_budgetaire b
		INNER JOIN ad_correspondance c ON b.id_correspondance = c.id
		INNER JOIN ad_budget t on t.ref_budget = b.ref_budget
		where c.type_budget = p_type_budget and t.exo_budget = p_exo_budget and b.etat_ligne = 2;

		FETCH liste_bud INTO ligne_bud ;
		WHILE FOUND LOOP
		--RAISE NOTICE "poste => % ----- trim 1 => % ---- trim 2 => % ----trim 3 => % ---- tri0m 4 => %",ligne_bud.poste_budget, ligne_bud.mnt_trim1, ligne_bud.mnt_trim2, ligne_bud.mnt_trim3, ligne_bud.mnt_trim4;

		SELECT INTO ligne_rapport ligne_bud.id, ligne_bud.poste_budget, ligne_bud.description, ligne_bud.compartiment, ligne_bud.cpte_comptable, ligne_bud.mnt_trim1, ligne_bud.mnt_trim2, ligne_bud.mnt_trim3, ligne_bud.mnt_trim4;
		RETURN NEXT ligne_rapport;

		FETCH liste_bud INTO ligne_bud;
		END LOOP;
		CLOSE liste_bud;

		  RETURN;
	END;
	$BODY$
	  LANGUAGE plpgsql VOLATILE
	  COST 100
	  ROWS 1000;
	ALTER FUNCTION get_budget(integer, integer, date, date)
	  OWNER TO adbanking;

    /* ----------------------FIN Type rapport_budget et function get_budget---------------------------------------------------------------- */

	/* ----------------------Debut Type rapport_budget_complet et function get_budget_complet---------------------------------------------------------------- */


	DROP TYPE IF EXISTS rapport_budget_complet CASCADE;
	DROP FUNCTION IF EXISTS  get_budget_complet(integer,integer, date, date) CASCADE;

	create type rapport_budget_complet as (
		id_correspondance integer,
		poste text,
		description text,
		compartiment integer,
		cpte_comptable text,
		trim_1 numeric(30,6),
		trim_2 numeric(30,6),
		trim_3 numeric(30,6),
		trim_4 numeric(30,6));

	  CREATE OR REPLACE FUNCTION get_budget_complet(
		integer,
		integer,
		date,
		date)
	  RETURNS SETOF rapport_budget_complet AS
	$BODY$
	DECLARE
	p_exo_budget ALIAS FOR $1;
	p_type_budget ALIAS FOR $2;
	p_date_debut ALIAS FOR $3;
	p_date_fin ALIAS FOR $4;

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

	OPEN curs_autre_poste FOR SELECT c.id ,poste_principal, poste_niveau_1, poste_niveau_2, poste_niveau_3, description, compartiment
	from ad_correspondance  c
	--INNER JOIN ad_budget_cpte_comptable p ON p.id_ligne = c.id
	where dernier_niveau = 'f' and etat_correspondance = 't' and type_budget = p_type_budget
	order by coalesce(poste_principal,0), coalesce(poste_niveau_1,0), coalesce(poste_niveau_2,0), coalesce(poste_niveau_3,0) asc;

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
	ALTER FUNCTION get_budget_complet(integer, integer, date, date)
	  OWNER TO adbanking;

	/* ----------------------FIN Type rapport_budget_complet et function get_budget_complet---------------------------------------------------------------- */

	/* --------------------Ajout des droits budget pour le profil Admin-----------------------------------------------------------------*/

	SELECT INTO v_id_profil id from adsys_profils where libel = 'admin';
	IF NOT EXISTS (select fonction from adsys_profils_axs where fonction = 700 and profil = v_id_profil) THEN
		INSERT INTO adsys_profils_axs(profil, fonction) VALUES (1,700);
	END IF;
	IF NOT EXISTS (select fonction from adsys_profils_axs where fonction = 701 and profil = v_id_profil) THEN
		INSERT INTO adsys_profils_axs(profil, fonction) VALUES (1,701);
	END IF;
	IF NOT EXISTS (select fonction from adsys_profils_axs where fonction = 702 and profil = v_id_profil) THEN
		INSERT INTO adsys_profils_axs(profil, fonction) VALUES (1,702);
	END IF;
	IF NOT EXISTS (select fonction from adsys_profils_axs where fonction = 703 and profil = v_id_profil) THEN
		INSERT INTO adsys_profils_axs(profil, fonction) VALUES (1,703);
	END IF;


RETURN output_result;

END;
$$
LANGUAGE plpgsql;
select budget_function();

DROP FUNCTION IF EXISTS budget_function();