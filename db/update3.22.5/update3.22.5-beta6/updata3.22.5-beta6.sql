-- AT-222 [Ajout d'un nouveau champ sur la fiche client]
CREATE OR REPLACE FUNCTION script_AT_222() RETURNS INT AS $$
  DECLARE
    tablen_id INTEGER = 0;
    tableliste_id INTEGER = 0;
    output_result INTEGER = 0;
    id_str_trad INTEGER = 0;
    id_d_tableliste INTEGER = 0;
  BEGIN

  -- Parametrage du champ zone dans la table Agence
  -- insertion dans la table d_tableliste
  tableliste_id := (SELECT ident FROM tableliste WHERE nomc = 'ad_agc');
  id_str_trad := maketraductionlangsyst('Rendre la zone du client obligatoire ?');

  IF NOT EXISTS(SELECT * FROM d_tableliste WHERE nchmpc = 'is_zone_required' and tablen = tableliste_id) THEN
    INSERT INTO d_tableliste VALUES ((select max(ident)
    from d_tableliste)+1, tableliste_id, 'is_zone_required', id_str_trad,
    'f', null, 'bol', null, null, FALSE);
  END IF;


  -- insertion du champ 'is_zone_required' dans la table ad_agc
  IF NOT EXISTS(SELECT * FROM information_schema.columns WHERE table_name = 'ad_agc' AND column_name = 'is_zone_required') THEN
    ALTER TABLE ad_agc ADD COLUMN is_zone_required BOOLEAN DEFAULT FALSE;
    IF EXISTS(SELECT langue FROM adsys_langues_systeme WHERE code = 'en_GB') THEN
      INSERT INTO ad_traductions VALUES (id_str_trad,'en_GB','Make the customer area mandatory');
    END IF;
  END IF;

  -- Id traduction dans la table ad_traduction pour la table 'adsys_zone_client'
  id_str_trad := maketraductionlangsyst('Zone client');

  -- Insertion du table 'adsys_zone_client' dans la table tableliste
  IF NOT EXISTS(SELECT * FROM tableliste WHERE nomc = 'adsys_zone_client') THEN
    INSERT INTO tableliste VALUES (
    (select max(ident) from tableliste)+1,
    'adsys_zone_client',
    maketraductionlangsyst('Zone client'),
    true);
    RAISE NOTICE 'Données table adsys_zone_client rajoutés dans table tableliste';
  END IF;

  -- Id de la table 'adsys_zone_client'
  tablen_id := (SELECT ident FROM tableliste WHERE nomc = 'adsys_zone_client');

  -- d_tableliste --> Insertion de l'id dans la table d_tableliste avec association de la table 'adsys_zone_client'
  IF NOT EXISTS(SELECT * FROM d_tableliste WHERE nchmpc = 'id' and tablen = tablen_id) THEN
    INSERT INTO d_tableliste VALUES ((select max(ident)
    from d_tableliste)+1, tablen_id, 'id', makeTraductionLangSyst('Id'), TRUE, NULL, 'int', NULL, TRUE, FALSE);
  END IF;

  -- d_tableliste --> Insertion de libelle_zone dans la table d_tableliste avec association de la table 'adsys_zone_client'
  IF NOT EXISTS(SELECT * FROM d_tableliste WHERE nchmpc = 'libelle_zone' and tablen = tablen_id) THEN
    INSERT INTO d_tableliste VALUES ((select max(ident)
    from d_tableliste)+1, tablen_id, 'libelle_zone', makeTraductionLangSyst('libelle_zone'), FALSE, NULL, 'txt', TRUE, FALSE, FALSE);
  END IF;

  -- d_tableliste --> Insertion de code_zone dans la table d_tableliste avec association de la table 'adsys_zone_client'
  IF NOT EXISTS(SELECT * FROM d_tableliste WHERE nchmpc = 'code_zone' and tablen = tablen_id) THEN
    INSERT INTO d_tableliste VALUES ((select max(ident)
    from d_tableliste)+1, tablen_id, 'code_zone', makeTraductionLangSyst('code_zone'), FALSE, NULL, 'txt', NULL, FALSE, FALSE);
  END IF;

  -- d_tableliste --> Id
  id_d_tableliste := (SELECT ident FROM d_tableliste WHERE nchmpc = 'id' and tablen = tablen_id);
  id_str_trad := maketraductionlangsyst('Zone');

  -- Id de la table 'adsys_zone_client'
  tablen_id := (SELECT ident FROM tableliste WHERE nomc = 'ad_cli');

  -- d_tableliste --> Insertion de client_zone dans la table d_tableliste avec association de la table 'adsys_zone_client'
  IF NOT EXISTS(SELECT * FROM d_tableliste WHERE nchmpc = 'client_zone' and tablen = tablen_id) THEN
    INSERT INTO d_tableliste VALUES ((select max(ident)
    from d_tableliste)+1, tablen_id, 'client_zone', id_str_trad,
    'f', id_d_tableliste, 'int', null, null, FALSE);
  END IF;

  -- Insertion du champ 'client_zone' dans la table ad_cli
  IF NOT EXISTS(SELECT * FROM information_schema.columns WHERE table_name = 'ad_cli' AND column_name = 'client_zone') THEN
    ALTER TABLE ad_cli ADD COLUMN client_zone INTEGER DEFAULT NULL;
    IF EXISTS(SELECT langue FROM adsys_langues_systeme WHERE code = 'en_GB') THEN
      INSERT INTO ad_traductions VALUES (id_str_trad,'en_GB','Zone');
    END IF;
  END IF;

  --- Creation ecran pour la table de parametrage
  IF NOT EXISTS (select * from ecrans where nom_ecran = 'Lzc-1') THEN
    INSERT INTO ecrans(nom_ecran, fichier, nom_menu, fonction)
    VALUES ('Lzc-1', 'modules/parametrage/tables.php', 'Pta', 292);
  END IF;

  IF NOT EXISTS (select * from ecrans where nom_ecran = 'Lzc-2') THEN
    INSERT INTO ecrans(nom_ecran, fichier, nom_menu, fonction)
    VALUES ('Lzc-2', 'modules/parametrage/tables.php', 'Pta', 292);
  END IF;

  --- Creation table 'adsys_zone_client'

  IF NOT EXISTS(SELECT * FROM information_schema.tables WHERE table_name = 'adsys_zone_client') THEN
    CREATE TABLE adsys_zone_client
    (
      id serial NOT NULL,
      code_zone text,
      libelle_zone text,
      id_ag integer NOT NULL,
      CONSTRAINT adsys_zone_client_pk PRIMARY KEY (id, id_ag)
    )
      WITH (
      OIDS=FALSE
    );
    ALTER TABLE adsys_zone_client
    OWNER TO postgres;
  END IF;

  RETURN output_result;
  END;
  $$
  LANGUAGE plpgsql ;

SELECT script_AT_222();
DROP FUNCTION script_AT_222();


CREATE OR REPLACE FUNCTION script_update_ml_v2() RETURNS INT AS
  $$
  DECLARE
  output_result INTEGER = 1;
  BEGIN
    IF NOT EXISTS (SELECT * FROM information_schema.columns WHERE table_name='ml_donnees_client_abonnees' and column_name='plus_de_6_mois') THEN
        ALTER TABLE ml_donnees_client_abonnees ADD COLUMN plus_de_6_mois  boolean default FALSE;
    END IF;

    IF NOT EXISTS (SELECT * FROM information_schema.columns WHERE table_name='ml_donnees_client_abonnees_specifique' and column_name='plus_de_6_mois') THEN
        ALTER TABLE ml_donnees_client_abonnees_specifique ADD COLUMN plus_de_6_mois  boolean default FALSE;
    END IF;

    IF NOT EXISTS (SELECT * FROM information_schema.columns WHERE table_name='ml_donnees_client_abonnees' and column_name='actif_3_mois') THEN
        ALTER TABLE ml_donnees_client_abonnees ADD COLUMN actif_3_mois  boolean default FALSE;
    END IF;

    IF NOT EXISTS (SELECT * FROM information_schema.columns WHERE table_name='ml_donnees_client_abonnees_specifique' and column_name='actif_3_mois') THEN
        ALTER TABLE ml_donnees_client_abonnees_specifique ADD COLUMN actif_3_mois  boolean default FALSE;
    END IF;

    IF NOT EXISTS (SELECT * FROM information_schema.columns WHERE table_name='ml_donnees_client_abonnees' and column_name='avec_credits') THEN
        ALTER TABLE ml_donnees_client_abonnees ADD COLUMN avec_credits  boolean default FALSE;
    END IF;

    IF NOT EXISTS (SELECT * FROM information_schema.columns WHERE table_name='ml_donnees_client_abonnees_specifique' and column_name='avec_credits') THEN
        ALTER TABLE ml_donnees_client_abonnees_specifique ADD COLUMN avec_credits  boolean default FALSE;
    END IF;

    IF NOT EXISTS (SELECT * FROM information_schema.columns WHERE table_name='ml_donnees_client_abonnees' and column_name='combinaison_pleine') THEN
        ALTER TABLE ml_donnees_client_abonnees ADD COLUMN combinaison_pleine  boolean default FALSE;
    END IF;

    IF NOT EXISTS (SELECT * FROM information_schema.columns WHERE table_name='ml_donnees_client_abonnees_specifique' and column_name='combinaison_pleine') THEN
        ALTER TABLE ml_donnees_client_abonnees_specifique ADD COLUMN combinaison_pleine  boolean default FALSE;
    END IF;

    IF NOT EXISTS (SELECT * FROM information_schema.columns WHERE table_name='ml_donnees_client_abonnees' and column_name='salaire_moyen_non_nul') THEN
        ALTER TABLE ml_donnees_client_abonnees ADD COLUMN salaire_moyen_non_nul boolean default FALSE;
    END IF;

    IF NOT EXISTS (SELECT * FROM information_schema.columns WHERE table_name='ml_donnees_client_abonnees_specifique' and column_name='salaire_moyen_non_nul') THEN
        ALTER TABLE ml_donnees_client_abonnees_specifique ADD COLUMN salaire_moyen_non_nul boolean default FALSE;
    END IF;

  RETURN output_result;
  END;
  $$
  LANGUAGE plpgsql;

SELECT script_update_ml_v2();
DROP FUNCTION script_update_ml_v2();

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
		ELSE
		salaire_moyen_cli :=0;
		END IF;

	  ELSE
	  --salaire_moyen_cli := (depots - mnt_cred)/duree ;
		salaire_moyen_cli := depots/limite ;
		IF salaire_moyen_cli > 0 THEN
		salaire_moyen_cli := round(salaire_moyen_cli);
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


