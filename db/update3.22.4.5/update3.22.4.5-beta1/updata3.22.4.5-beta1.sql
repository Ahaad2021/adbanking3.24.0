CREATE OR REPLACE FUNCTION script_update_ml_v3() RETURNS INT AS
  $$
  DECLARE
  id_str_trad INTEGER;
  output_result INTEGER = 1;
  BEGIN

    IF NOT EXISTS(SELECT * FROM information_schema.columns WHERE table_name = 'ml_donnees_client_abonnees' AND column_name = 'tranche_age') THEN
    ALTER TABLE ml_donnees_client_abonnees ADD COLUMN tranche_age int;
    END IF;

    IF NOT EXISTS(SELECT * FROM information_schema.columns WHERE table_name = 'ml_donnees_client_abonnees_specifique' AND column_name = 'tranche_age') THEN
    ALTER TABLE ml_donnees_client_abonnees_specifique ADD COLUMN tranche_age int;
    END IF;

    IF NOT EXISTS (select * from menus where nom_menu = 'Mlr') THEN
	 id_str_trad := maketraductionlangsyst('Rapport Mobile Lending');
	 INSERT INTO menus (nom_menu, libel_menu, nom_pere, pos_hierarch, ordre, is_menu,fonction, is_cliquable)
	 VALUES ('Mlr', id_str_trad, 'Gen-17', 3, 3, TRUE, 906, FALSE);
	 IF EXISTS(SELECT langue FROM adsys_langues_systeme WHERE code = 'en_GB') THEN
	  INSERT INTO ad_traductions (id_str, langue, traduction) VALUES (id_str_trad, 'en_GB', 'Rapport Mobile Lending');
	 END IF;
	END IF;

	IF NOT EXISTS (select * from ecrans where nom_ecran = 'Mlr-1') THEN
	 INSERT INTO ecrans(nom_ecran, fichier, nom_menu, fonction)
	 VALUES ('Mlr-1', 'modules/mobile_lending/rapport_mobile_lending.php', 'Mlr', 906);
	END IF;

	IF NOT EXISTS (select * from ecrans where nom_ecran = 'Mlr-2') THEN
	 INSERT INTO ecrans(nom_ecran, fichier, nom_menu, fonction)
	 VALUES ('Mlr-2', 'modules/mobile_lending/rapport_mobile_lending.php', 'Mlr', 906);
	END IF;

  RETURN output_result;
  END;
  $$
  LANGUAGE plpgsql;

SELECT script_update_ml_v3();
DROP FUNCTION script_update_ml_v3();


-- Function: mise_a_jour_donnee_abonnee(numeric, numeric, integer, integer, numeric)

-- DROP FUNCTION mise_a_jour_donnee_abonnee(numeric, numeric, integer, integer, numeric);

CREATE OR REPLACE FUNCTION mise_a_jour_donnee_abonnee(
    numeric,
    numeric,
    integer,
    integer,
    numeric)
  RETURNS SETOF mise_a_jour_donnees_view_v2 AS
$BODY$
  DECLARE
  prc_mnt_max ALIAS FOR $1;
  coeff_present_irregularite ALIAS FOR $2;
  mnt_max_emprunter ALIAS FOR $3;
  mnt_max_new_client ALIAS FOR $4;
  coeff_def_irregularite ALIAS FOR $5;

  ligne RECORD;
  ligne2 RECORD;
  duree integer ;
  debut date ;
  fin date;
  limite integer;
  depots numeric(30,0):= 0;
  client integer;
  cpte_base integer;
  salaire_moyen_cli numeric(30,0);
  adhesion date ;
  lg_histo_cli integer;
  sal integer;
  irregularite integer ;
  depots_mens numeric(30,0);
  interv1 date;
  interv2 date;
  nbre_credit integer;
  mnt_tot_emprunter numeric(30,0);
  mnt_restant_du numeric(30,0);
  mnt_max numeric(30,0) :=0 ;
  nominateur_regularite integer;
  score_present float;
  score_passe float;
  score_futur float;
  total_retard float;
  nbre_retard integer;
  age_cli integer;
  naissance date;
  sexe_cli text;
  sexe integer;
  score_tot_credit_sim float;
  count_retard integer;
  agence integer;
  date_crea date;
  tx_irregularite float;

  bool_lg_hist boolean default false;
  bool_actif_3_mois boolean default false;
  date_deb_bool_actif_3mois date;
  date_fin_bool_actif_3mois date;
  nbre_actif_depot_retrait integer;
  bool_avec_credits boolean default false;
  bool_salaire_moyen_non_nul boolean default false;

  ligne_extract_donnee_credit refcursor;

  mise_a_jour_donnees mise_a_jour_donnees_view_v2;
  output INTEGER :=0;
  C1 CURSOR FOR select b.* from ad_abonnement b INNER JOIN ad_cli c ON c.id_client = b.id_client where deleted = 'f' and c.statut_juridique = 1 and b.id_service = 1;

  BEGIN

  TRUNCATE TABLE ml_donnees_client_abonnees CASCADE;

  OPEN C1 ;
  FETCH C1 INTO ligne;

  WHILE FOUND LOOP

bool_lg_hist = false;
bool_actif_3_mois = false;
bool_avec_credits = false;
bool_salaire_moyen_non_nul = false;

  RAISE INFO 'Debut Extraction des donnees V2 ...' ;

  -- agence
  SELECT INTO agence numagc();

  -- date creation
  SELECT INTO date_crea now();

  -- compte de base
  client := ligne.id_client ;
  SELECT INTO cpte_base id_cpte_base FROM ad_cli where id_client = client;

  --duree
  SELECT INTO adhesion date_adh FROM ad_cli WHERE id_client = client;
  lg_histo_cli := date_part('year', age(date(now()),adhesion)) * 12 + date_part('month', age(date(now()),adhesion));
  RAISE NOTICE 'lg_histo_cli %',lg_histo_cli;
  duree := lg_histo_cli ;
  IF duree >= 6 THEN
  limite = 6 ;
  bool_lg_hist = true;
  ELSE
  limite = duree ;-- + 1  ' month - 1 day';
  END IF ;




  -- Salaire moyen abo
  SELECT INTO fin (date_trunc('month',date(date(now()))) - interval '1 day')::DATE;
  SELECT INTO debut ((date(fin) + interval '1 day') - (limite * interval '1 month'))::DATE;
  RAISE NOTICE 'fin %',fin;RAISE NOTICE 'debut %',debut;

  --Recuperation de la date de debut et fin pour les 3 dernier mois
  SELECT INTO date_fin_bool_actif_3mois (date_trunc('month',date(date(now()))) - interval '1 day')::DATE;
  SELECT INTO date_deb_bool_actif_3mois ((date(fin) + interval '1 day') - (3 * interval '1 month'))::DATE;
  RAISE NOTICE 'fin %',fin;RAISE NOTICE 'debut %',debut;

  -- Verification si le compte du client est actif durant les 3 mois derniers mois
  SELECT INTO nbre_actif_depot_retrait  count(*) FROM ad_mouvement a, ad_ecriture b WHERE a.id_ecriture = b.id_ecriture and cpte_interne_cli = cpte_base and sens = 'c'
  and (date_valeur between date_deb_bool_actif_3mois and date_fin_bool_actif_3mois) and type_operation not in (11,21,31,51,132,133,135,144,153,154,155,201,210,221,231,361,411,542,544,545,547) ;
  IF nbre_actif_depot_retrait > 0 THEN
  bool_actif_3_mois = true;
  END IF;

  SELECT INTO depots sum(montant) FROM ad_mouvement a, ad_ecriture b WHERE a.id_ecriture = b.id_ecriture and cpte_interne_cli = cpte_base and sens = 'c'
  and (date_valeur between debut and fin) and type_operation not in (11,21,31,51,132,133,135,144,153,154,155,201,210,221,231,361,411,542,544,545,547) ;
  IF depots  = NULL THEN
  depots := 0;
  END IF;
  RAISE NOTICE 'depot %',depots;
  IF duree >= 6 THEN
  --salaire_moyen_cli := (depots - mnt_cred)/12 ;
  salaire_moyen_cli := depots/limite ;
  RAISE NOTICE 'salaire moyen %', salaire_moyen_cli;
  IF salaire_moyen_cli > 0 THEN
  salaire_moyen_cli := round(salaire_moyen_cli);
  bool_salaire_moyen_non_nul = true;
  ELSE
  salaire_moyen_cli :=0;
  END IF;

  ELSE
  --salaire_moyen_cli := (depots - mnt_cred)/duree ;
  salaire_moyen_cli := depots/limite ;
  IF salaire_moyen_cli > 0 THEN
  salaire_moyen_cli := round(salaire_moyen_cli);
  bool_salaire_moyen_non_nul = true;
  ELSE
  salaire_moyen_cli :=0;
  END IF;
  END IF ;


  -- Taux de regularite des salaires abo

  sal:= 0 ;
  irregularite := 0;
  nominateur_regularite :=0;


  WHILE sal < limite
  LOOP
  interv1 = (debut + (sal * interval '1 month'))::DATE ;
  interv2 = (debut + ((sal+1) * interval '1 month')- interval '1 day')::DATE ;
  SELECT INTO depots_mens case when sum(montant) is null then 0 else sum(montant) end as mnt_depot FROM ad_mouvement a, ad_ecriture b WHERE a.id_ecriture = b.id_ecriture and
  cpte_interne_cli = cpte_base and sens = 'c' and (date_valeur between interv1 and interv2)
  and type_operation not in (11,21,31,51,132,133,135,144,153,154,155,201,210,221,231,361,411,542,544,545,547) ;

  IF depots_mens < coeff_def_irregularite * salaire_moyen_cli THEN
  irregularite = irregularite + 1 ;
  nominateur_regularite = nominateur_regularite + salaire_moyen_cli - depots_mens;
  ELSE
  irregularite = irregularite + 0 ;
  END IF;

  sal = sal+1;
  END LOOP ;

  -- Nbre de credit
  SELECT INTO nbre_credit count(id_doss) FROM ad_dcr WHERE id_client = client AND etat IN (2,5,6,7,9,13,14,15);
  IF nbre_credit > 0 THEN
  bool_avec_credits = true;
  END IF;
  -- mnt_total emprunter
  SELECT INTO mnt_tot_emprunter sum(cre_mnt_octr) FROM ad_dcr WHERE id_client = client AND etat IN (2,5,6,7,9,13,14,15);
  IF mnt_tot_emprunter IS NULL THEN
  mnt_tot_emprunter := 0;
  END IF;
  -- mnt restant du
  SELECT INTO mnt_restant_du sum(solde_cap +solde_int+solde_gar+solde_pen) FROM ad_etr e INNER JOIN ad_dcr d on d.id_doss = e.id_doss WHERE d.id_client = client AND d.etat IN (2,5,7,9,13,14,15);
  -- age client
  select into naissance pp_date_naissance from ad_cli where id_client = client;
  age_cli := date_part('year', age(now(),naissance));
  -- montant maximum
  IF nbre_credit > 0 THEN
  IF irregularite >0 THEN
  mnt_max = salaire_moyen_cli-((nominateur_regularite / irregularite)*(prc_mnt_max));
  IF mnt_max > mnt_max_emprunter THEN
  mnt_max = mnt_max_emprunter;
  END IF;
  ELSE
  mnt_max = salaire_moyen_cli *(prc_mnt_max);
  IF mnt_max > mnt_max_emprunter THEN
  mnt_max = mnt_max_emprunter;
  END IF;
  END IF;
  ELSE
  IF irregularite >0 THEN
  mnt_max = salaire_moyen_cli-((nominateur_regularite / irregularite)*(prc_mnt_max));
  IF mnt_max > mnt_max_new_client THEN
  mnt_max = mnt_max_new_client;
  END IF;
  ELSE
  mnt_max = salaire_moyen_cli *(prc_mnt_max);
  IF mnt_max > mnt_max_new_client THEN
  mnt_max = mnt_max_new_client;
  END IF;
  END IF;

  END IF;

  RAISE NOTICE 'salaire moyen => %',salaire_moyen_cli;
  -- Calcul taux irregularite
  IF salaire_moyen_cli <> 0 THEN
  tx_irregularite = (100/salaire_moyen_cli)* nominateur_regularite;
  tx_irregularite = round(tx_irregularite);
  ELSE
  tx_irregularite = 3000;
  END IF;


  -- score present
  RAISE NOTICE 'sal moyen => % <---> , nominateur iree => % <--> , ',salaire_moyen_cli,nominateur_regularite;
  IF irregularite >0 THEN
  /*score_present = (100/salaire_moyen_cli)*(nominateur_regularite);
  score_present = 100- ($2*score_present);
  score_present = round(score_present);*/
  score_present = 100 - (coeff_present_irregularite * tx_irregularite);
  ELSE
  /*score_present = (100/salaire_moyen_cli)*(salaire_moyen_cli);
  score_present = 100 - ($2*score_present);
  score_present = round(score_present);*/
  score_present = 100 - (coeff_present_irregularite * tx_irregularite);
  END IF;


  --score passé
  IF nbre_credit <> 0 THEN
  total_retard := 0;
  nbre_retard := 0;

  OPEN ligne_extract_donnee_credit FOR SELECT c.* FROM ml_donnees_client_credit c WHERE c.client = ligne.id_client;
  FETCH ligne_extract_donnee_credit INTO ligne2;
  WHILE FOUND LOOP
  total_retard = total_retard + ligne2.score_retard;
  nbre_retard = nbre_retard +1;
  FETCH ligne_extract_donnee_credit INTO ligne2;
  END LOOP;
  CLOSE ligne_extract_donnee_credit;

  if nbre_retard > 0 THEN
  score_passe = total_retard / nbre_retard;
  ELSE
  score_passe = 100;
  END IF;
  ELSE
  score_passe = 0;
  END IF;
  -- score futur
  SELECT INTO sexe_cli,sexe (CASE pp_sexe WHEN 1 THEN 'M' WHEN 2 THEN 'F' ELSE '' END ),pp_sexe  FROM ad_cli WHERE id_client = client;

  /*SELECT INTO score_tot_credit_sim,count_retard  sum(m.score_retard), count(m.score_retard)
  FROM ml_donnees_client_credit m
  WHERE m.age = age_cli AND m.sexe = sexe_cli AND m.salaire_moyen = salaire_moyen_cli AND m.regularite = irregularite
  AND m.nbr_credit = nbre_credit AND m.mnt_tot_emprunts = mnt_tot_emprunter AND m.lg_histo = lg_histo_cli;

  score_futur = score_tot_credit_sim / count_retard;
  score_futur = round(score_futur);*/





  INSERT INTO ml_donnees_client_abonnees(client,age,duree,depots,salaire_moyen,irregularite,tx_irregularite,nbre_credit,mnt_tot_emprunter,lg_histo,mnt_restant_du,tranche_sexe,mnt_max,score_passe,score_present,id_ag,date_creation,plus_de_6_mois,actif_3_mois,avec_credits,salaire_moyen_non_nul)
  VALUES (client, age_cli,duree, depots, salaire_moyen_cli, irregularite,tx_irregularite,nbre_credit,mnt_tot_emprunter,lg_histo_cli,mnt_restant_du,sexe,mnt_max,score_passe,score_present,agence,date_crea,bool_lg_hist,bool_actif_3_mois,bool_avec_credits,bool_salaire_moyen_non_nul);

  UPDATE ad_abonnement SET ml_mnt_max = mnt_max WHERE id_client = client AND deleted = 'f';

  FETCH C1 INTO ligne;
  END LOOP;
  CLOSE C1;


  RETURN;
  END;
  $BODY$
  LANGUAGE plpgsql VOLATILE
  COST 100
  ROWS 1000;
ALTER FUNCTION mise_a_jour_donnee_abonnee(numeric, numeric, integer, integer, numeric)
  OWNER TO postgres;



-- Function: mise_a_jour_donnee_abonnee_specifique(numeric, numeric, integer, integer, numeric, integer)

-- DROP FUNCTION mise_a_jour_donnee_abonnee_specifique(numeric, numeric, integer, integer, numeric, integer);

CREATE OR REPLACE FUNCTION mise_a_jour_donnee_abonnee_specifique(
    numeric,
    numeric,
    integer,
    integer,
    numeric,
    integer)
  RETURNS SETOF mise_a_jour_donnees_view_v2 AS
$BODY$
DECLARE
  prc_mnt_max ALIAS FOR $1;
  coeff_present_irregularite ALIAS FOR $2;
  mnt_max_emprunter ALIAS FOR $3;
  mnt_max_new_client ALIAS FOR $4;
  coeff_def_irregularite ALIAS FOR $5;
  client_spe ALIAS FOR $6;

  ligne RECORD;
  ligne2 RECORD;
  duree integer ;
  debut date ;
  fin date;
  limite integer;
  depots numeric(30,0):= 0;
  client integer;
  cpte_base integer;
  salaire_moyen_cli numeric(30,0);
  adhesion date ;
  lg_histo_cli integer;
  sal integer;
  irregularite integer ;
  depots_mens numeric(30,0);
  interv1 date;
  interv2 date;
  nbre_credit integer;
  mnt_tot_emprunter numeric(30,0);
  mnt_restant_du numeric(30,0);
  mnt_max numeric(30,0) :=0 ;
  nominateur_regularite integer;
  score_present float;
  score_passe float;
  score_futur float;
  total_retard float;
  nbre_retard integer;
  age_cli integer;
  naissance date;
  sexe_cli text;
  sexe integer;
  score_tot_credit_sim float;
  count_retard integer;
  agence integer;
  date_crea date;
  tx_irregularite float;


  bool_lg_hist boolean default false;
  bool_actif_3_mois boolean default false;
  date_deb_bool_actif_3mois date;
  date_fin_bool_actif_3mois date;
  nbre_actif_depot_retrait integer;
  bool_avec_credits boolean default false;
  bool_salaire_moyen_non_nul boolean default false;

ligne_extract_donnee_credit refcursor;

mise_a_jour_donnees mise_a_jour_donnees_view_v2;
  output INTEGER :=0;
C1 CURSOR FOR select b.* from ad_abonnement b INNER JOIN ad_cli c ON c.id_client = b.id_client where deleted = 'f' and c.statut_juridique = 1 and b.id_service = 1 and b.id_client = client_spe;

BEGIN

  TRUNCATE TABLE ml_donnees_client_abonnees_specifique CASCADE;

  OPEN C1 ;
  FETCH C1 INTO ligne;

	  WHILE FOUND LOOP
bool_lg_hist = false;
bool_actif_3_mois = false;
bool_avec_credits = false;
bool_salaire_moyen_non_nul = false;

	  RAISE INFO 'Debut Extraction des donnees V2 ...' ;

-- agence
SELECT INTO agence numagc();

-- date creation
SELECT INTO date_crea now();

-- compte de base
      client := ligne.id_client ;
	  SELECT INTO cpte_base id_cpte_base FROM ad_cli where id_client = client;

--duree
SELECT INTO adhesion date_adh FROM ad_cli WHERE id_client = client;
lg_histo_cli := date_part('year', age(date(now()),adhesion)) * 12 + date_part('month', age(date(now()),adhesion));
RAISE NOTICE 'lg_histo_cli %',lg_histo_cli;
	  duree := lg_histo_cli ;
	  IF duree >= 6 THEN
		 limite = 6 ;
  bool_lg_hist = true;
	  ELSE
		 limite = duree ;-- + 1  ' month - 1 day';
	  END IF ;




-- Salaire moyen abo
	  SELECT INTO fin (date_trunc('month',date(date(now()))) - interval '1 day')::DATE;
	  SELECT INTO debut ((date(fin) + interval '1 day') - (limite * interval '1 month'))::DATE;
	  RAISE NOTICE 'fin %',fin;RAISE NOTICE 'debut %',debut;

	    --Recuperation de la date de debut et fin pour les 3 dernier mois
	  SELECT INTO date_fin_bool_actif_3mois (date_trunc('month',date(date(now()))) - interval '1 day')::DATE;
	  SELECT INTO date_deb_bool_actif_3mois ((date(fin) + interval '1 day') - (3 * interval '1 month'))::DATE;
	  RAISE NOTICE 'fin %',fin;RAISE NOTICE 'debut %',debut;

	  -- Verification si le compte du client est actif durant les 3 mois derniers mois
	  SELECT INTO nbre_actif_depot_retrait  count(*) FROM ad_mouvement a, ad_ecriture b WHERE a.id_ecriture = b.id_ecriture and cpte_interne_cli = cpte_base and sens = 'c'
	  and (date_valeur between date_deb_bool_actif_3mois and date_fin_bool_actif_3mois) and type_operation not in (11,21,31,51,132,133,135,144,153,154,155,201,210,221,231,361,411,542,544,545,547) ;
	  IF nbre_actif_depot_retrait > 0 THEN
	  bool_actif_3_mois = true;
	  END IF;
	  SELECT INTO depots sum(montant) FROM ad_mouvement a, ad_ecriture b WHERE a.id_ecriture = b.id_ecriture and cpte_interne_cli = cpte_base and sens = 'c'
	  and (date_valeur between debut and fin) and type_operation not in (11,21,31,51,132,133,135,144,153,154,155,201,210,221,231,361,411,542,544,545,547) ;
	  IF depots  = NULL THEN
		depots := 0;
	  END IF;
	  RAISE NOTICE 'depot %',depots;
	  IF duree >= 6 THEN
	  --salaire_moyen_cli := (depots - mnt_cred)/12 ;
		salaire_moyen_cli := depots/limite ;
		IF salaire_moyen_cli > 0 THEN
		salaire_moyen_cli := round(salaire_moyen_cli);
		bool_salaire_moyen_non_nul = true;
		RAISE NOTICE 'bool sala moyen non null => %',bool_salaire_moyen_non_nul;
		ELSE
		salaire_moyen_cli :=0;
		END IF;

	  ELSE
	  --salaire_moyen_cli := (depots - mnt_cred)/duree ;
		salaire_moyen_cli := depots/limite ;
		IF salaire_moyen_cli > 0 THEN
		salaire_moyen_cli := round(salaire_moyen_cli);
		bool_salaire_moyen_non_nul = true;
		ELSE
		salaire_moyen_cli :=0;
		END IF;
	  END IF ;


-- Taux de regularite des salaires abo

	  sal:= 0 ;
	  irregularite := 0;
	  nominateur_regularite :=0;


	  WHILE sal < limite
	     LOOP
		    interv1 = (debut + (sal * interval '1 month'))::DATE ;
		    interv2 = (debut + ((sal+1) * interval '1 month')- interval '1 day')::DATE ;
		    SELECT INTO depots_mens case when sum(montant) is null then 0 else sum(montant) end as mnt_depot FROM ad_mouvement a, ad_ecriture b WHERE a.id_ecriture = b.id_ecriture and
		    cpte_interne_cli = cpte_base and sens = 'c' and (date_valeur between interv1 and interv2)
		    and type_operation not in (11,21,31,51,132,133,135,144,153,154,155,201,210,221,231,361,411,542,544,545,547) ;

			IF depots_mens < coeff_def_irregularite * salaire_moyen_cli THEN
				irregularite = irregularite + 1 ;
				nominateur_regularite = nominateur_regularite + salaire_moyen_cli - depots_mens;
			ELSE
				irregularite = irregularite + 0 ;
			END IF;

	   sal = sal+1;
	   END LOOP ;

-- Nbre de credit
SELECT INTO nbre_credit count(id_doss) FROM ad_dcr WHERE id_client = client AND etat IN (2,5,6,7,9,13,14,15);
  IF nbre_credit > 0 THEN
  bool_avec_credits = true;
  END IF;
-- mnt_total emprunter
SELECT INTO mnt_tot_emprunter sum(cre_mnt_octr) FROM ad_dcr WHERE id_client = client AND etat IN (2,5,6,7,9,13,14,15);
IF mnt_tot_emprunter IS NULL THEN
mnt_tot_emprunter := 0;
END IF;
-- mnt restant du
SELECT INTO mnt_restant_du sum(solde_cap +solde_int+solde_gar+solde_pen) FROM ad_etr e INNER JOIN ad_dcr d on d.id_doss = e.id_doss WHERE d.id_client = client AND d.etat IN (2,5,7,9,13,14,15);
-- age client
select into naissance pp_date_naissance from ad_cli where id_client = client;
age_cli := date_part('year', age(now(),naissance));

RAISE NOTICE 'nbrecredit => % -----   irregularite=> % ---- mnt_max => %', nbre_credit,irregularite,mnt_max;
-- montant maximum
IF nbre_credit > 0 THEN
	IF irregularite >0 THEN
		mnt_max = salaire_moyen_cli-((nominateur_regularite / irregularite)*(prc_mnt_max));
		IF mnt_max > mnt_max_emprunter THEN
			mnt_max = mnt_max_emprunter;
		END IF;
	ELSE
		mnt_max = salaire_moyen_cli *(prc_mnt_max);
		IF mnt_max > mnt_max_emprunter THEN
			mnt_max = mnt_max_emprunter;
		END IF;
	END IF;
ELSE
	IF irregularite >0 THEN
		mnt_max = salaire_moyen_cli-((nominateur_regularite / irregularite)*(prc_mnt_max));
		IF mnt_max > mnt_max_new_client THEN
			mnt_max = mnt_max_new_client;
		END IF;
	ELSE
		mnt_max = salaire_moyen_cli *(prc_mnt_max);
		IF mnt_max > mnt_max_new_client THEN
			mnt_max = mnt_max_new_client;
		END IF;
	END IF;

END IF;

RAISE NOTICE 'salaire moyen => %',salaire_moyen_cli;
-- Calcul taux irregularite
IF salaire_moyen_cli <> 0 THEN
tx_irregularite = (100/salaire_moyen_cli)* nominateur_regularite;
tx_irregularite = round(tx_irregularite);
ELSE
tx_irregularite = 3000;
END IF;


-- score present
RAISE NOTICE 'sal moyen => % <---> , nominateur iree => % <--> , ',salaire_moyen_cli,nominateur_regularite;
IF irregularite >0 THEN
/*score_present = (100/salaire_moyen_cli)*(nominateur_regularite);
score_present = 100- ($2*score_present);
score_present = round(score_present);*/
score_present = 100 - (coeff_present_irregularite * tx_irregularite);
ELSE
/*score_present = (100/salaire_moyen_cli)*(salaire_moyen_cli);
score_present = 100 - ($2*score_present);
score_present = round(score_present);*/
score_present = 100 - (coeff_present_irregularite * tx_irregularite);
END IF;


--score passé
IF nbre_credit <> 0 THEN
    total_retard := 0;
    nbre_retard := 0;

    OPEN ligne_extract_donnee_credit FOR SELECT c.* FROM ml_donnees_client_credit c WHERE c.client = ligne.id_client;
	FETCH ligne_extract_donnee_credit INTO ligne2;
	 WHILE FOUND LOOP
		total_retard = total_retard + ligne2.score_retard;
		nbre_retard = nbre_retard +1;
	FETCH ligne_extract_donnee_credit INTO ligne2;
	END LOOP;
	CLOSE ligne_extract_donnee_credit;

	if nbre_retard > 0 THEN
	score_passe = total_retard / nbre_retard;
	ELSE
	score_passe = 100;
	END IF;
 ELSE
score_passe = 0;
 END IF;
-- score futur
SELECT INTO sexe_cli,sexe (CASE pp_sexe WHEN 1 THEN 'M' WHEN 2 THEN 'F' ELSE '' END ),pp_sexe  FROM ad_cli WHERE id_client = client;

/*SELECT INTO score_tot_credit_sim,count_retard  sum(m.score_retard), count(m.score_retard)
FROM ml_donnees_client_credit m
WHERE m.age = age_cli AND m.sexe = sexe_cli AND m.salaire_moyen = salaire_moyen_cli AND m.regularite = irregularite
AND m.nbr_credit = nbre_credit AND m.mnt_tot_emprunts = mnt_tot_emprunter AND m.lg_histo = lg_histo_cli;

score_futur = score_tot_credit_sim / count_retard;
score_futur = round(score_futur);*/






	INSERT INTO ml_donnees_client_abonnees_specifique(client,age,duree,depots,salaire_moyen,irregularite,tx_irregularite,nbre_credit,mnt_tot_emprunter,lg_histo,mnt_restant_du,tranche_sexe,mnt_max,score_passe,score_present,id_ag,date_creation,plus_de_6_mois,actif_3_mois,avec_credits,salaire_moyen_non_nul)
	VALUES (client, age_cli,duree, depots, salaire_moyen_cli, irregularite,tx_irregularite,nbre_credit,mnt_tot_emprunter,lg_histo_cli,mnt_restant_du,sexe,mnt_max,score_passe,score_present,agence,date_crea,bool_lg_hist,bool_actif_3_mois,bool_avec_credits,bool_salaire_moyen_non_nul);


		FETCH C1 INTO ligne;
	  END LOOP;
	 CLOSE C1;


	RETURN;
END;
$BODY$
  LANGUAGE plpgsql VOLATILE
  COST 100
  ROWS 1000;
ALTER FUNCTION mise_a_jour_donnee_abonnee_specifique(numeric, numeric, integer, integer, numeric, integer)
  OWNER TO postgres;


-- Function: mise_a_jour_donnee_abonnee_alone(numeric, numeric, integer, integer, numeric, integer)

-- DROP FUNCTION mise_a_jour_donnee_abonnee_alone(numeric, numeric, integer, integer, numeric, integer);

CREATE OR REPLACE FUNCTION mise_a_jour_donnee_abonnee_alone(
    numeric,
    numeric,
    integer,
    integer,
    numeric,
    integer)
  RETURNS SETOF mise_a_jour_donnees_view_v2 AS
$BODY$
  DECLARE
  prc_mnt_max ALIAS FOR $1;
  coeff_present_irregularite ALIAS FOR $2;
  mnt_max_emprunter ALIAS FOR $3;
  mnt_max_new_client ALIAS FOR $4;
  coeff_def_irregularite ALIAS FOR $5;
  client_spe ALIAS FOR $6;

  ligne RECORD;
  ligne2 RECORD;
  duree integer ;
  debut date ;
  fin date;
  limite integer;
  depots numeric(30,0):= 0;
  client integer;
  cpte_base integer;
  salaire_moyen_cli numeric(30,0);
  adhesion date ;
  lg_histo_cli integer;
  sal integer;
  irregularite integer ;
  depots_mens numeric(30,0);
  interv1 date;
  interv2 date;
  nbre_credit integer;
  mnt_tot_emprunter numeric(30,0);
  mnt_restant_du numeric(30,0);
  mnt_max numeric(30,0) :=0 ;
  nominateur_regularite integer;
  score_present float;
  score_passe float;
  score_futur float;
  total_retard float;
  nbre_retard integer;
  age_cli integer;
  naissance date;
  sexe_cli text;
  sexe integer;
  score_tot_credit_sim float;
  count_retard integer;
  agence integer;
  date_crea date;
  tx_irregularite float;

  bool_lg_hist boolean default false;
  bool_actif_3_mois boolean default false;
  date_deb_bool_actif_3mois date;
  date_fin_bool_actif_3mois date;
  nbre_actif_depot_retrait integer;
  bool_avec_credits boolean default false;
  bool_salaire_moyen_non_nul boolean default false;

  ligne_extract_donnee_credit refcursor;

  mise_a_jour_donnees mise_a_jour_donnees_view_v2;
  output INTEGER :=0;
  C1 CURSOR FOR select b.* from ad_abonnement b INNER JOIN ad_cli c ON c.id_client = b.id_client where deleted = 'f' and c.statut_juridique = 1 and b.id_service = 1 and b.id_client = client_spe;

  BEGIN


  OPEN C1 ;
  FETCH C1 INTO ligne;

  WHILE FOUND LOOP

bool_lg_hist = false;
bool_actif_3_mois = false;
bool_avec_credits = false;
bool_salaire_moyen_non_nul = false;

  RAISE INFO 'Debut Extraction des donnees V2 ...' ;

  -- agence
  SELECT INTO agence numagc();

  -- date creation
  SELECT INTO date_crea now();

  -- compte de base
  client := ligne.id_client ;
  SELECT INTO cpte_base id_cpte_base FROM ad_cli where id_client = client;

  --duree
  SELECT INTO adhesion date_adh FROM ad_cli WHERE id_client = client;
  lg_histo_cli := date_part('year', age(date(now()),adhesion)) * 12 + date_part('month', age(date(now()),adhesion));
  RAISE NOTICE 'lg_histo_cli %',lg_histo_cli;
  duree := lg_histo_cli ;
  IF duree >= 6 THEN
  limite = 6 ;
  bool_lg_hist = true;
  ELSE
  limite = duree ;-- + 1  ' month - 1 day';
  END IF ;




  -- Salaire moyen abo
  SELECT INTO fin (date_trunc('month',date(date(now()))) - interval '1 day')::DATE;
  SELECT INTO debut ((date(fin) + interval '1 day') - (limite * interval '1 month'))::DATE;
  RAISE NOTICE 'fin %',fin;RAISE NOTICE 'debut %',debut;

  --Recuperation de la date de debut et fin pour les 3 dernier mois
  SELECT INTO date_fin_bool_actif_3mois (date_trunc('month',date(date(now()))) - interval '1 day')::DATE;
  SELECT INTO date_deb_bool_actif_3mois ((date(fin) + interval '1 day') - (3 * interval '1 month'))::DATE;
  RAISE NOTICE 'fin %',fin;RAISE NOTICE 'debut %',debut;

  -- Verification si le compte du client est actif durant les 3 mois derniers mois
  SELECT INTO nbre_actif_depot_retrait  count(*) FROM ad_mouvement a, ad_ecriture b WHERE a.id_ecriture = b.id_ecriture and cpte_interne_cli = cpte_base and sens = 'c'
  and (date_valeur between date_deb_bool_actif_3mois and date_fin_bool_actif_3mois) and type_operation not in (11,21,31,51,132,133,135,144,153,154,155,201,210,221,231,361,411,542,544,545,547) ;
  IF nbre_actif_depot_retrait > 0 THEN
  bool_actif_3_mois = true;
  END IF;


  SELECT INTO depots sum(montant) FROM ad_mouvement a, ad_ecriture b WHERE a.id_ecriture = b.id_ecriture and cpte_interne_cli = cpte_base and sens = 'c'
  and (date_valeur between debut and fin) and type_operation not in (11,21,31,51,132,133,135,144,153,154,155,201,210,221,231,361,411,542,544,545,547) ;
  IF depots  = NULL THEN
  depots := 0;
  END IF;
  RAISE NOTICE 'depot %',depots;
  IF duree >= 6 THEN
  --salaire_moyen_cli := (depots - mnt_cred)/12 ;
  salaire_moyen_cli := depots/limite ;
  IF salaire_moyen_cli > 0 THEN
  salaire_moyen_cli := round(salaire_moyen_cli);
  bool_salaire_moyen_non_nul = true;
  ELSE
  salaire_moyen_cli :=0;
  END IF;

  ELSE
  --salaire_moyen_cli := (depots - mnt_cred)/duree ;
  salaire_moyen_cli := depots/limite ;
  IF salaire_moyen_cli > 0 THEN
  salaire_moyen_cli := round(salaire_moyen_cli);
  bool_salaire_moyen_non_nul = true;
  ELSE
  salaire_moyen_cli :=0;
  END IF;
  END IF ;


  -- Taux de regularite des salaires abo

  sal:= 0 ;
  irregularite := 0;
  nominateur_regularite :=0;


  WHILE sal < limite
  LOOP
  interv1 = (debut + (sal * interval '1 month'))::DATE ;
  interv2 = (debut + ((sal+1) * interval '1 month')- interval '1 day')::DATE ;
  SELECT INTO depots_mens case when sum(montant) is null then 0 else sum(montant) end as mnt_depot FROM ad_mouvement a, ad_ecriture b WHERE a.id_ecriture = b.id_ecriture and
  cpte_interne_cli = cpte_base and sens = 'c' and (date_valeur between interv1 and interv2)
  and type_operation not in (11,21,31,51,132,133,135,144,153,154,155,201,210,221,231,361,411,542,544,545,547) ;

  IF depots_mens < coeff_def_irregularite * salaire_moyen_cli THEN
  irregularite = irregularite + 1 ;
  nominateur_regularite = nominateur_regularite + salaire_moyen_cli - depots_mens;
  ELSE
  irregularite = irregularite + 0 ;
  END IF;

  sal = sal+1;
  END LOOP ;

  -- Nbre de credit
  SELECT INTO nbre_credit count(id_doss) FROM ad_dcr WHERE id_client = client AND etat IN (2,5,6,7,9,13,14,15);
  IF nbre_credit > 0 THEN
  bool_avec_credits = true;
  END IF;
  -- mnt_total emprunter
  SELECT INTO mnt_tot_emprunter sum(cre_mnt_octr) FROM ad_dcr WHERE id_client = client AND etat IN (2,5,6,7,9,13,14,15);
  IF mnt_tot_emprunter IS NULL THEN
  mnt_tot_emprunter := 0;
  END IF;
  -- mnt restant du
  SELECT INTO mnt_restant_du sum(solde_cap +solde_int+solde_gar+solde_pen) FROM ad_etr e INNER JOIN ad_dcr d on d.id_doss = e.id_doss WHERE d.id_client = client AND d.etat IN (2,5,7,9,13,14,15);
  -- age client
  select into naissance pp_date_naissance from ad_cli where id_client = client;
  age_cli := date_part('year', age(now(),naissance));
  -- montant maximum
  IF nbre_credit > 0 THEN
  IF irregularite >0 THEN
  mnt_max = salaire_moyen_cli-((nominateur_regularite / irregularite)*(prc_mnt_max));
  IF mnt_max > mnt_max_emprunter THEN
  mnt_max = mnt_max_emprunter;
  END IF;
  ELSE
  mnt_max = salaire_moyen_cli *(prc_mnt_max);
  IF mnt_max > mnt_max_emprunter THEN
  mnt_max = mnt_max_emprunter;
  END IF;
  END IF;
  ELSE
  IF irregularite >0 THEN
  mnt_max = salaire_moyen_cli-((nominateur_regularite / irregularite)*(prc_mnt_max));
  IF mnt_max > mnt_max_new_client THEN
  mnt_max = mnt_max_new_client;
  END IF;
  ELSE
  mnt_max = salaire_moyen_cli *(prc_mnt_max);
  IF mnt_max > mnt_max_new_client THEN
  mnt_max = mnt_max_new_client;
  END IF;
  END IF;

  END IF;

  RAISE NOTICE 'salaire moyen => %',salaire_moyen_cli;
  -- Calcul taux irregularite
  IF salaire_moyen_cli <> 0 THEN
  tx_irregularite = (100/salaire_moyen_cli)* nominateur_regularite;
  tx_irregularite = round(tx_irregularite);
  ELSE
  tx_irregularite = 3000;
  END IF;


  -- score present
  RAISE NOTICE 'sal moyen => % <---> , nominateur iree => % <--> , ',salaire_moyen_cli,nominateur_regularite;
  IF irregularite >0 THEN
  /*score_present = (100/salaire_moyen_cli)*(nominateur_regularite);
  score_present = 100- ($2*score_present);
  score_present = round(score_present);*/
  score_present = 100 - (coeff_present_irregularite * tx_irregularite);
  ELSE
  /*score_present = (100/salaire_moyen_cli)*(salaire_moyen_cli);
  score_present = 100 - ($2*score_present);
  score_present = round(score_present);*/
  score_present = 100 - (coeff_present_irregularite * tx_irregularite);
  END IF;


  --score passé
  IF nbre_credit <> 0 THEN
  total_retard := 0;
  nbre_retard := 0;

  OPEN ligne_extract_donnee_credit FOR SELECT c.* FROM ml_donnees_client_credit c WHERE c.client = ligne.id_client;
  FETCH ligne_extract_donnee_credit INTO ligne2;
  WHILE FOUND LOOP
  total_retard = total_retard + ligne2.score_retard;
  nbre_retard = nbre_retard +1;
  FETCH ligne_extract_donnee_credit INTO ligne2;
  END LOOP;
  CLOSE ligne_extract_donnee_credit;

  if nbre_retard > 0 THEN
  score_passe = total_retard / nbre_retard;
  ELSE
  score_passe = 100;
  END IF;
  ELSE
  score_passe = 0;
  END IF;
  -- score futur
  SELECT INTO sexe_cli,sexe (CASE pp_sexe WHEN 1 THEN 'M' WHEN 2 THEN 'F' ELSE '' END ),pp_sexe  FROM ad_cli WHERE id_client = client;

  /*SELECT INTO score_tot_credit_sim,count_retard  sum(m.score_retard), count(m.score_retard)
  FROM ml_donnees_client_credit m
  WHERE m.age = age_cli AND m.sexe = sexe_cli AND m.salaire_moyen = salaire_moyen_cli AND m.regularite = irregularite
  AND m.nbr_credit = nbre_credit AND m.mnt_tot_emprunts = mnt_tot_emprunter AND m.lg_histo = lg_histo_cli;

  score_futur = score_tot_credit_sim / count_retard;
  score_futur = round(score_futur);*/





  INSERT INTO ml_donnees_client_abonnees(client,age,duree,depots,salaire_moyen,irregularite,tx_irregularite,nbre_credit,mnt_tot_emprunter,lg_histo,mnt_restant_du,tranche_sexe,mnt_max,score_passe,score_present,id_ag,date_creation,plus_de_6_mois,actif_3_mois,avec_credits,salaire_moyen_non_nul)
  VALUES (client, age_cli,duree, depots, salaire_moyen_cli, irregularite,tx_irregularite,nbre_credit,mnt_tot_emprunter,lg_histo_cli,mnt_restant_du,sexe,mnt_max,score_passe,score_present,agence,date_crea,bool_lg_hist,bool_actif_3_mois,bool_avec_credits,bool_salaire_moyen_non_nul);

  UPDATE ad_abonnement SET ml_mnt_max = mnt_max WHERE id_client = client AND deleted = 'f';

  FETCH C1 INTO ligne;
  END LOOP;
  CLOSE C1;


  RETURN;
  END;
  $BODY$
  LANGUAGE plpgsql VOLATILE
  COST 100
  ROWS 1000;
ALTER FUNCTION mise_a_jour_donnee_abonnee_alone(numeric, numeric, integer, integer, numeric, integer)
  OWNER TO postgres;



DROP TYPE IF EXISTS view_rapport_mobile_lending  cascade;
CREATE TYPE view_rapport_mobile_lending AS (
id_client integer,
id_doss integer,
etat_doss_mob integer,
imf text,
agence text ,
id_agent integer,
localisation text,
tranche_localisation integer,
sexe text,
tranche_sexe integer,
sal_moy numeric(30,6),
tranche_sal_moy integer,
lg_histo integer,
somm_tot_emprunter numeric(30,2),
tranche_somm_tot_emprunter integer,
nbre_credit_carac integer,
tranche_nbre_credit integer,
age integer,
tx_irregularite float,
tranche_tx_irregularite integer,
nbre_credit integer,
mnt_dem numeric,
date_deboursement date,
nbre_ech integer,
retard_ech_1 integer,
retard_ech_2 integer,
retard_ech_3 integer,
mnt_rest_du numeric,
penalite numeric,
score_retard_credit float,
score_client float,
commentaire text,
tranche_age integer,
tranche_lg_histo integer
 );



-- Function: get_rapport_mobile_lending(integer, integer)

-- DROP FUNCTION get_rapport_mobile_lending(integer, integer);


CREATE OR REPLACE FUNCTION get_rapport_mobile_lending(
    INT[],
    INT[])
  RETURNS SETOF view_rapport_mobile_lending AS
$BODY$
  DECLARE

  etat_mobile_lending ALIAS FOR $1;
  cre_etat_param ALIAS FOR $2;

  v_id_client integer;
  v_id_doss integer;
  v_etat_doss_mob integer;
  v_nom_agence text;
  v_nom_imf text;
  v_id_agent integer;
  v_nbre_credit integer;
  v_mnt_dem numeric(30,2);
  v_date_debours date;
  v_nbre_ech integer;
  v_retard_eche_1 integer;
  v_retard_eche_2 integer;
  v_retard_eche_3 integer;
  v_mnt_restant_du numeric(30,2);
  v_penalite_a_payer numeric(30,2);
  v_score_retard_credit float;
  v_score_client float;
  v_commentaire text;

v_localisation text;
v_tranche_localisation integer;
v_sexe text;
v_tranche_sexe integer;
v_sal_moy numeric(30,6);
v_tranche_sal_moy integer;
v_lg_histo integer;
v_somm_tot_emprunter numeric(30,2);
v_tranche_somm_tot_emprunter integer;
v_tranche_nbre_credit integer;
v_age integer;
v_tranche_age integer;
v_tx_irregularite float;
v_tranche_tx_irregularite integer;
v_tranche_lg_histo integer;

  ligne RECORD;

  mise_a_jour_donnees view_rapport_mobile_lending;
  output INTEGER :=0;

  C1 refcursor;

  BEGIN

  IF array_length(cre_etat_param, 1) > 0 THEN
    OPEN C1 FOR SELECT * from ml_demande_credit m INNER JOIN ad_dcr d on d.id_client = m.id_client and d.id_doss = m.id_doss WHERE m.statut_demande = ANY(etat_mobile_lending) and d.cre_etat = ANY(cre_etat_param);
  ELSE
    OPEN C1 FOR SELECT * from ml_demande_credit WHERE statut_demande = ANY(etat_mobile_lending);
  END IF;




  --OPEN C1 ;
  FETCH C1 INTO ligne;
  WHILE FOUND LOOP

    -- ID client
  v_id_client := ligne.id_client;

 -- id_ doss
 v_id_doss := ligne.id_doss;

 -- etat dossier mobile lending
 v_etat_doss_mob := ligne.statut_demande;

  -- libel Agence et IMF
  SELECT INTO v_nom_agence,v_nom_imf libel_institution,libel_ag FROM ad_agc;

  -- id_agent
  SELECT INTO v_id_agent id_agent_gest FROM ad_dcr where id_doss = ligne.id_doss;

 -- nombre credit
 SELECT INTO v_nbre_credit count(*) FROM ml_demande_credit where id_client = ligne.id_client and statut_demande not IN (2,4,5);

  --Montant demande
  v_mnt_dem = ligne.mnt_dem;

  -- date remboursement
 SELECT INTO v_date_debours cre_date_debloc FROM ad_dcr where id_doss = ligne.id_doss;

 -- nombre echeance
 SELECT INTO v_nbre_ech count(*) FROM ad_etr WHERE id_doss = ligne.id_doss;

 -- retard echeance 1
 SELECT INTO v_retard_eche_1 date(now()) - date(date_ech) FROM ad_etr where id_ech = 1 and id_doss = ligne.id_doss;
 IF v_retard_eche_1 IS NULL THEN
 v_retard_eche_1 := 0;
 ELSEIF v_retard_eche_1 < 0 THEN
  v_retard_eche_1 := 0;
 END IF;

 -- retard echeance 2
 SELECT INTO v_retard_eche_2  date(now()) - date(date_ech) FROM ad_etr where id_ech = 2 and id_doss = ligne.id_doss;
 IF v_retard_eche_2 IS NULL THEN
 v_retard_eche_2 := 0;
 ELSEIF v_retard_eche_2 < 0 THEN
  v_retard_eche_2 := 0;
 END IF;

 -- retard echeance 2
 SELECT INTO v_retard_eche_3 date(now()) - date(date_ech) FROM ad_etr where id_ech = 3 and id_doss = ligne.id_doss;
 IF v_retard_eche_3 IS NULL THEN
 v_retard_eche_3 := 0;
 ELSEIF v_retard_eche_3 < 0 THEN
  v_retard_eche_3 := 0;
 END IF;

 --montant restant du
 SELECT INTO v_mnt_restant_du sum(solde_cap+solde_int+solde_gar+solde_pen) FROM ad_etr where id_doss = ligne.id_doss;

 --penalite a payer
 SELECT INTO v_penalite_a_payer sum(solde_pen) FROM ad_etr where id_doss = ligne.id_doss;

 -- score_retard_credit
 IF v_nbre_ech > 0 THEN
   v_score_retard_credit = 100 - 1/v_nbre_ech * (v_retard_eche_1+v_retard_eche_2+v_retard_eche_3);
 ELSE
   v_score_retard_credit = 0;
 END IF;

 -- score client =
  SELECT INTO v_score_client score_final FROM ml_donnees_client_abonnees where client = ligne.id_client;

 -- commentaire agent
 SELECT INTO v_commentaire motif FROM ad_dcr where id_doss = ligne.id_doss;

 -- localisation
 SELECT INTO v_tranche_localisation ml_localisation FROM ad_agc;
 IF v_tranche_localisation = 1 THEN
  v_localisation = 'Urbaine';
 ELSE
  v_localisation = 'Rurale';
 END IF;

 -- selection des tranches dans la table ml_donnees_client_abonnees
 SELECT INTO v_tranche_sexe,v_sal_moy,v_tranche_sal_moy,v_lg_histo,v_somm_tot_emprunter,v_tranche_somm_tot_emprunter,v_tranche_nbre_credit,v_age,v_tx_irregularite,v_tranche_tx_irregularite, v_tranche_age, v_tranche_lg_histo
  tranche_sexe,salaire_moyen,tranche_sal_moyen,lg_histo,mnt_tot_emprunter,tranche_tot_emprunter,tranche_nbre_credit,age,tx_irregularite,tranche_irregularite, tranche_age, tranche_lg_histo  FROM ml_donnees_client_abonnees WHERE client = v_id_client;

  --recuperation sexe
  IF  v_tranche_sexe = 1 THEN
  v_sexe := 'Homme';
  ELSE
  v_sexe := 'Femme';
  END IF;

 SELECT INTO mise_a_jour_donnees v_id_client,v_id_doss,v_etat_doss_mob,v_nom_agence,v_nom_imf,v_id_agent,v_localisation,v_tranche_localisation,v_sexe,v_tranche_sexe,v_sal_moy,v_tranche_sal_moy,v_lg_histo,v_somm_tot_emprunter,v_tranche_somm_tot_emprunter,v_nbre_credit,v_tranche_nbre_credit,v_age,v_tx_irregularite,v_tranche_tx_irregularite,v_nbre_credit,v_mnt_dem,v_date_debours,v_nbre_ech,v_retard_eche_1,v_retard_eche_2,v_retard_eche_3,
v_mnt_restant_du,v_penalite_a_payer,v_score_retard_credit,v_score_client,v_commentaire, v_tranche_age, v_tranche_lg_histo;

RETURN NEXT mise_a_jour_donnees;

 FETCH C1 INTO ligne;
  END LOOP;
  CLOSE C1;


  RETURN;
  END;
  $BODY$
  LANGUAGE plpgsql VOLATILE
  COST 100
  ROWS 1000;
ALTER FUNCTION get_rapport_mobile_lending(INT[], INT[])
  OWNER TO postgres;

-- SELECT * FROM get_rapport_mobile_lending(2,0)