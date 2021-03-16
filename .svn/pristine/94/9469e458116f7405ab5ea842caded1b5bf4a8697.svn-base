CREATE OR REPLACE FUNCTION script_setup_salarie() RETURNS INT AS
  $$
  DECLARE
  id_str_trad INTEGER;
  output_result INTEGER = 1;
  d_tableliste_str  INTEGER :=0;
  tableliste_ident INTEGER :=0;
  BEGIN
-- column crdt_salairie
IF NOT EXISTS(SELECT * FROM information_schema.columns WHERE table_name = 'adsys_produit_credit' AND column_name = 'crdt_salairie') THEN
	ALTER TABLE adsys_produit_credit ADD COLUMN crdt_salairie boolean DEFAULT false;
	select INTO tableliste_ident ident from tableliste where nomc like 'adsys_produit_credit' order by ident desc limit 1;
	d_tableliste_str := makeTraductionLangSyst('Crédit salairié?');
	INSERT INTO d_tableliste VALUES ((select max(ident) from d_tableliste)+1, tableliste_ident, 'crdt_salairie', d_tableliste_str, true,null, 'bol', NULL, NULL, false);
	IF EXISTS(SELECT langue FROM adsys_langues_systeme WHERE code = 'en_GB') THEN
		INSERT INTO ad_traductions VALUES (d_tableliste_str,'en_GB','Employee Loan');
	END IF;
END IF;


-- column salarie
IF NOT EXISTS(SELECT * FROM information_schema.columns WHERE table_name = 'ad_abonnement' AND column_name = 'salarie') THEN
	ALTER TABLE ad_abonnement ADD COLUMN salarie boolean DEFAULT false;
END IF;


  RETURN output_result;
  END;
  $$
  LANGUAGE plpgsql;

SELECT script_setup_salarie();
DROP FUNCTION script_setup_salarie();


CREATE OR REPLACE FUNCTION recup_salarie()
RETURNS integer as
$BODY$

DECLARE

v_montant_lot numeric(30,0) := 0;
v_date_debut date;
fin date;
v_date_fin date;
counter_mois integer;
counter_mois_fin integer;
counter_sal integer :=0;
v_montant_max_mois numeric(30,0) := 0;
v_bornes_mnt_min numeric(30,0) := 0;
v_bornes_mnt_max numeric(30,0) := 0;
v_mnt_ref numeric(30,0) := 0;
v_id_client_encours integer :=0;
v_date_debut_moy date;
v_date_fin_moy date;
v_montant_tot_sal numeric(30,0) := 0;
v_nbre_tot_sal integer := 0;
v_montant_tot_sal_moy numeric(30,0) := 0;




cur_abon CURSOR FOR
SELECT id_client, id_abonnement FROM ad_abonnement where id_service = 1 and deleted = 'f' and ml_score > 0  and salarie = 'f';

ligne RECORD;

list_montant_depot refcursor;
ligne2 RECORD;

BEGIN

--SELECT INTO fin (date_trunc('month',date(date(now()))) - interval '1 day')::DATE;
--SELECT INTO v_date_debut ((date(fin) + interval '1 day') - (6 * interval '1 month'))::DATE;


OPEN cur_abon;
FETCH cur_abon INTO ligne;

  WHILE FOUND LOOP
	counter_mois = 1;
	/*v_montant_tot_sal = 0;
	v_nbre_tot_sal = 0;*/
	RAISE NOTICE 'ID Client => %', ligne.id_client;

	LOOP
	counter_mois_fin =0;
	EXIT WHEN counter_mois = 7;
	-- date de fin
	IF counter_mois <> 1 THEN
	counter_mois_fin = counter_mois -1;
	SELECT INTO fin ((date_trunc('month',date(date('2021-01-03'))) - interval '1 day') - (counter_mois_fin * interval '1 month'))::DATE;
	--SELECT INTO v_date_debut ((date(fin) + interval '1 day') - (1 * interval '1 month'))::DATE;
	SELECT INTO v_date_debut date_trunc('month', date(fin)) :: DATE;
	ELSE
	v_montant_max_mois =0;
	SELECT INTO fin (date_trunc('month',date(date('2021-01-03'))) - interval '1 day')::DATE;
	SELECT INTO v_date_debut ((date(fin) + interval '1 day') - (1 * interval '1 month'))::DATE;
	END IF;

	-- date fin pour moyenne
	SELECT INTO v_date_fin_moy (date_trunc('month',date(date('2021-01-03'))) - interval '1 day')::DATE;
	-- date debut pour moyenne
	SELECT INTO v_date_debut_moy ((date(v_date_fin_moy) + interval '1 day') - (6 * interval '1 month'))::DATE;


	SELECT INTO v_montant_tot_sal,v_nbre_tot_sal sum(t.montant), count(t.montant) from ad_his h
	inner join ad_ecriture e on e.id_his = h.id_his
	inner join ad_mouvement t on t.id_ecriture = e.id_ecriture
	inner join ad_str s on s.id_str = e.libel_ecriture
	inner join ad_traductions d on d.id_str = s.id_str
	where (h.type_fonction in (158,159) and e.type_operation in (160,508) and t.sens = 'c' and e.date_comptable between v_date_debut_moy and v_date_fin_moy and t.cpte_interne_cli in (select distinct c.id_cpte from ad_cpt c 	   inner join ad_abonnement b on b.id_client = c.id_titulaire where b.id_client = ligne.id_client))
	or (h.type_fonction = 470 and (d.traduction ilike '%salair%' or d.traduction ilike '%salar%' or d.traduction ilike '%payment%' or d.traduction ilike '%pyt%' or d.traduction ilike '%deposit%' or d.traduction ilike '%allowence%') and t.sens = 'c' and e.date_comptable between v_date_debut_moy and v_date_fin_moy and t.cpte_interne_cli in (select distinct c.id_cpte from ad_cpt c inner join ad_abonnement b on b.id_client = c.id_titulaire where b.id_client = ligne.id_client)) order by sum(t.montant) DESC;


	-- calcul de la moyenne
	v_montant_tot_sal_moy = v_montant_tot_sal / v_nbre_tot_sal;
	v_bornes_mnt_min = v_montant_tot_sal_moy * 0.8;
	v_bornes_mnt_max = v_montant_tot_sal_moy * 1.2;

	RAISE NOTICE 'montant tot sal moy => % ------------- bornes min => % ------------- bornes max => % ',v_montant_tot_sal_moy ,v_bornes_mnt_min,v_bornes_mnt_max;


	OPEN list_montant_depot FOR select t.montant from ad_his h
	inner join ad_ecriture e on e.id_his = h.id_his
	inner join ad_mouvement t on t.id_ecriture = e.id_ecriture
	inner join ad_str s on s.id_str = e.libel_ecriture
	inner join ad_traductions d on d.id_str = s.id_str
	where (h.type_fonction in (158,159) and e.type_operation in (160,508) and t.sens = 'c' and e.date_comptable between v_date_debut and fin and t.cpte_interne_cli in (select distinct c.id_cpte from ad_cpt c inner join ad_abonnement b on b.id_client = c.id_titulaire where b.id_client = ligne.id_client))
	or (h.type_fonction = 470 and (d.traduction ilike '%salair%' or d.traduction ilike '%salar%' or d.traduction ilike '%payment%' or d.traduction ilike '%pyt%' or d.traduction ilike '%deposit%' or d.traduction ilike '%allowence%') and t.sens = 'c' and e.date_comptable between v_date_debut and fin and t.cpte_interne_cli in (select distinct c.id_cpte from ad_cpt c inner join ad_abonnement b on b.id_client = c.id_titulaire where b.id_client = ligne.id_client)) order by t.montant DESC;


	IF v_id_client_encours <> ligne.id_client THEN
		v_id_client_encours = ligne.id_client;
		counter_sal = 0;
	END IF;

	FETCH list_montant_depot INTO ligne2 ;
	WHILE FOUND LOOP

	/*
	IF ligne2.montant > v_montant_max_mois THEN
		v_montant_max_mois = ligne2.montant;
	END IF;	*/




	IF  ligne2.montant >= v_bornes_mnt_min THEN
		IF ligne2.montant <= v_bornes_mnt_max THEN
			counter_sal = counter_sal +1;
		END IF;
	END IF;
	RAISE NOTICE 'montant = > % ---------- bornes min => % ------------ bornes max => % ----  counter_sale => %',ligne2.montant,v_bornes_mnt_min,v_bornes_mnt_max, counter_sal;

	FETCH list_montant_depot INTO ligne2;
	END LOOP;
	CLOSE list_montant_depot;

	counter_mois = counter_mois +1;
	END LOOP;

	RAISE NOTICE 'Le client a % fois son montant entre les bornes du salaire' , counter_sal;

	IF counter_sal >= 5 AND counter_sal <= 7 THEN
		UPDATE ad_abonnement set salarie = 't' where id_abonnement = ligne.id_abonnement;
	END IF;



  FETCH cur_abon INTO ligne;
  END LOOP;
  RETURN 1;

END;
$BODY$
  LANGUAGE plpgsql VOLATILE
  COST 100;
ALTER FUNCTION recup_salarie()
  OWNER TO adbanking;


  --select recup_salarie();



CREATE OR REPLACE FUNCTION recup_salarie_defini(integer)
RETURNS integer as
$BODY$

DECLARE

v_id_client ALIAS FOR $1;

v_montant_lot numeric(30,0) := 0;
v_date_debut date;
fin date;
v_date_fin date;
counter_mois integer;
counter_mois_fin integer;
counter_sal integer :=0;
v_montant_max_mois numeric(30,0) := 0;
v_bornes_mnt_min numeric(30,0) := 0;
v_bornes_mnt_max numeric(30,0) := 0;
v_mnt_ref numeric(30,0) := 0;
v_id_client_encours integer :=0;
v_date_debut_moy date;
v_date_fin_moy date;
v_montant_tot_sal numeric(30,0) := 0;
v_nbre_tot_sal integer := 0;
v_montant_tot_sal_moy numeric(30,0) := 0;




cur_abon CURSOR FOR
SELECT id_client, id_abonnement FROM ad_abonnement where id_service = 1 and deleted = 'f' and ml_score > 0  and salarie = 'f' and id_client = v_id_client;

ligne RECORD;

list_montant_depot refcursor;
ligne2 RECORD;

BEGIN

--SELECT INTO fin (date_trunc('month',date(date(now()))) - interval '1 day')::DATE;
--SELECT INTO v_date_debut ((date(fin) + interval '1 day') - (6 * interval '1 month'))::DATE;


OPEN cur_abon;
FETCH cur_abon INTO ligne;

  WHILE FOUND LOOP
	counter_mois = 1;
	/*v_montant_tot_sal = 0;
	v_nbre_tot_sal = 0;*/
	RAISE NOTICE 'ID Client => %', ligne.id_client;

	LOOP
	counter_mois_fin =0;
	EXIT WHEN counter_mois = 7;
	-- date de fin
	IF counter_mois <> 1 THEN
	counter_mois_fin = counter_mois -1;
	SELECT INTO fin ((date_trunc('month',date(date('2021-01-03'))) - interval '1 day') - (counter_mois_fin * interval '1 month'))::DATE;
	--SELECT INTO v_date_debut ((date(fin) + interval '1 day') - (1 * interval '1 month'))::DATE;
	SELECT INTO v_date_debut date_trunc('month', date(fin)) :: DATE;
	ELSE
	v_montant_max_mois =0;
	SELECT INTO fin (date_trunc('month',date(date('2021-01-03'))) - interval '1 day')::DATE;
	SELECT INTO v_date_debut ((date(fin) + interval '1 day') - (1 * interval '1 month'))::DATE;
	END IF;

	-- date fin pour moyenne
	SELECT INTO v_date_fin_moy (date_trunc('month',date(date('2021-01-03'))) - interval '1 day')::DATE;
	-- date debut pour moyenne
	SELECT INTO v_date_debut_moy ((date(v_date_fin_moy) + interval '1 day') - (6 * interval '1 month'))::DATE;


	SELECT INTO v_montant_tot_sal,v_nbre_tot_sal sum(t.montant), count(t.montant) from ad_his h
	inner join ad_ecriture e on e.id_his = h.id_his
	inner join ad_mouvement t on t.id_ecriture = e.id_ecriture
	inner join ad_str s on s.id_str = e.libel_ecriture
	inner join ad_traductions d on d.id_str = s.id_str
	where (h.type_fonction in (158,159) and e.type_operation in (160,508) and t.sens = 'c' and e.date_comptable between v_date_debut_moy and v_date_fin_moy and t.cpte_interne_cli in (select distinct c.id_cpte from ad_cpt c 	   inner join ad_abonnement b on b.id_client = c.id_titulaire where b.id_client = ligne.id_client))
	or (h.type_fonction = 470 and (d.traduction ilike '%salair%' or d.traduction ilike '%salar%' or d.traduction ilike '%payment%' or d.traduction ilike '%pyt%' or d.traduction ilike '%deposit%' or d.traduction ilike '%allowence%') and t.sens = 'c' and e.date_comptable between v_date_debut_moy and v_date_fin_moy and t.cpte_interne_cli in (select distinct c.id_cpte from ad_cpt c inner join ad_abonnement b on b.id_client = c.id_titulaire where b.id_client = ligne.id_client)) order by sum(t.montant) DESC;


	-- calcul de la moyenne
	v_montant_tot_sal_moy = v_montant_tot_sal / v_nbre_tot_sal;
	v_bornes_mnt_min = v_montant_tot_sal_moy * 0.8;
	v_bornes_mnt_max = v_montant_tot_sal_moy * 1.2;

	RAISE NOTICE 'montant tot sal moy => % ------------- bornes min => % ------------- bornes max => % ',v_montant_tot_sal_moy ,v_bornes_mnt_min,v_bornes_mnt_max;


	OPEN list_montant_depot FOR select t.montant from ad_his h
	inner join ad_ecriture e on e.id_his = h.id_his
	inner join ad_mouvement t on t.id_ecriture = e.id_ecriture
	inner join ad_str s on s.id_str = e.libel_ecriture
	inner join ad_traductions d on d.id_str = s.id_str
	where (h.type_fonction in (158,159) and e.type_operation in (160,508) and t.sens = 'c' and e.date_comptable between v_date_debut and fin and t.cpte_interne_cli in (select distinct c.id_cpte from ad_cpt c inner join ad_abonnement b on b.id_client = c.id_titulaire where b.id_client = ligne.id_client))
	or (h.type_fonction = 470 and (d.traduction ilike '%salair%' or d.traduction ilike '%salar%' or d.traduction ilike '%payment%' or d.traduction ilike '%pyt%' or d.traduction ilike '%deposit%' or d.traduction ilike '%allowence%') and t.sens = 'c' and e.date_comptable between v_date_debut and fin and t.cpte_interne_cli in (select distinct c.id_cpte from ad_cpt c inner join ad_abonnement b on b.id_client = c.id_titulaire where b.id_client = ligne.id_client)) order by t.montant DESC;


	IF v_id_client_encours <> ligne.id_client THEN
		v_id_client_encours = ligne.id_client;
		counter_sal = 0;
	END IF;

	FETCH list_montant_depot INTO ligne2 ;
	WHILE FOUND LOOP

	/*
	IF ligne2.montant > v_montant_max_mois THEN
		v_montant_max_mois = ligne2.montant;
	END IF;	*/




	IF  ligne2.montant >= v_bornes_mnt_min THEN
		IF ligne2.montant <= v_bornes_mnt_max THEN
			counter_sal = counter_sal +1;
		END IF;
	END IF;
	RAISE NOTICE 'montant = > % ---------- bornes min => % ------------ bornes max => % ----  counter_sale => %',ligne2.montant,v_bornes_mnt_min,v_bornes_mnt_max, counter_sal;

	FETCH list_montant_depot INTO ligne2;
	END LOOP;
	CLOSE list_montant_depot;

	counter_mois = counter_mois +1;
	END LOOP;

	RAISE NOTICE 'Le client a % fois son montant entre les bornes du salaire' , counter_sal;

	IF counter_sal >= 5 AND counter_sal <= 7 THEN
		UPDATE ad_abonnement set salarie = 't' where id_abonnement = ligne.id_abonnement;
	END IF;



  FETCH cur_abon INTO ligne;
  END LOOP;
  RETURN 1;

END;
$BODY$
  LANGUAGE plpgsql VOLATILE
  COST 100;
ALTER FUNCTION recup_salarie_defini(integer)
  OWNER TO adbanking;


  --select recup_salarie_defini();