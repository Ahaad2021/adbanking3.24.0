CREATE OR REPLACE FUNCTION script_ml_creation_table() RETURNS INT AS
  $$
  DECLARE
  output_result INTEGER = 1;
  BEGIN

    ------------------------------------------------ Creation table ml_donnees_client_abonnees --------------------------------
    IF NOT EXISTS(SELECT * FROM information_schema.tables WHERE table_name = 'ml_donnees_client_abonnees') THEN
      CREATE TABLE ml_donnees_client_abonnees
      (
        client integer,
        age integer,
        duree integer,
        depots numeric(30,0),
        salaire_moyen numeric(30,0),
        tranche_sal_moyen integer,
        irregularite integer,
        tx_irregularite float,
        tranche_irregularite integer,
        nbre_credit integer,
        tranche_nbre_credit integer,
        mnt_tot_emprunter numeric(30,0),
        tranche_tot_emprunter integer,
        lg_histo integer,
        tranche_lg_histo integer,
        tranche_sexe integer,
        mnt_restant_du numeric(30,0),
        mnt_max numeric(30,0),
        bonus_gar float,
        score_passe float,
        score_present float,
        score_futur float,
        score_final float,
        id_ag integer,
        date_creation timestamp without time zone,
        date_modif timestamp without time zone

      );
      ALTER TABLE ml_donnees_client_abonnees
      OWNER TO adbanking;
    END IF;
    ------------------------------------------------ Creation table ml_donnees_client_credit --------------------------------
    IF NOT EXISTS(SELECT * FROM information_schema.tables WHERE table_name = 'ml_donnees_client_credit') THEN
      CREATE TABLE ml_donnees_client_credit
      (
        categorie text,
        sous_categorie text,
        client integer,
        imf text,
        agence text,
        sexe text,
        nbr_credit integer,
        dossier integer,
        mnt_cred numeric(30,0),
        duree integer,
        age integer,
        lg_histo integer,
        salaire_moyen numeric(30,0),
        regularite integer,
        nbr_retard integer,
        max_etat integer,
        mbre_echeance integer,
        score_retard numeric(30,2),
        mnt_tot_emprunts numeric(30,0),
        echeance numeric(30,0),
        montant_du numeric(30,0),
        max_jours_retard integer,
        taux_regularite numeric(30,0),
        montant_maximum numeric(30,0)

      );
      ALTER TABLE ml_donnees_client_credit
      OWNER TO adbanking;
    END IF;

    ------------------------------------------------ Creation table ml_statistique_client_all --------------------------------
    IF NOT EXISTS(SELECT * FROM information_schema.tables WHERE table_name = 'ml_statistique_client_all') THEN
      CREATE TABLE ml_statistique_client_all
      (
        categorie text,
        sous_categorie text,
        client integer,
        imf text,
        agence text,
        localisation text,
        sexe text,
        nbr_credit integer,
        dossier integer,
        mnt_cred numeric(30,0),
        duree integer,
        age integer,
        lg_histo integer,
        salaire_moyen numeric(30,0),
        regularite integer,
        nbr_retard integer,
        max_etat integer,
        mbre_echeance integer,
        score_retard numeric(30,2),
        mnt_tot_emprunts numeric(30,0),
        salaire_moyen_montant_ech numeric(30,0),
        echeance numeric(30,0),
        montant_du numeric(30,0),
        max_jours_retard integer,
        taux_regularite numeric(30,0),
        montant_maximum numeric(30,0)

      );
      ALTER TABLE ml_statistique_client_all
      OWNER TO adbanking;
    END IF;
    ------------------------------------------------ Creation table ml_combinaison_global --------------------------------
    IF NOT EXISTS(SELECT * FROM information_schema.tables WHERE table_name = 'ml_combinaison_global') THEN
      CREATE TABLE ml_combinaison_global
      (
        combinaison integer,
        nbre_dossier integer,
        score_retard numeric(30,2),
        tranche_sal_moyen integer,
        tranche_irregularite integer,
        tranche_nbre_credit integer,
        tranche_tot_emprunter integer,
        tranche_localisation integer,
        data_combinaison text
      );
      ALTER TABLE ml_combinaison_global
      OWNER TO adbanking;
    END IF;
    ------------------------------------------------ Creation table ml_demande_credit --------------------------------
    IF NOT EXISTS(SELECT * FROM information_schema.tables WHERE table_name = 'ml_demande_credit') THEN
      CREATE TABLE ml_demande_credit
      (
        id_client integer,
        id_prod integer,
        id_doss integer,
        mnt_dem numeric(30,0),
        devise text,
        duree integer,
        id_transaction text,
        code_agent text,
        code_imf text,
        signature_contrat boolean default FALSE,
        telephone text,
        statut_demande integer,
        date_creation timestamp without time zone,
        date_modif timestamp without time zone
      );
      ALTER TABLE ml_demande_credit
      OWNER TO adbanking;
    END IF;

    IF NOT EXISTS(SELECT * FROM information_schema.tables WHERE table_name = 'ml_donnees_client_abonnees_specifique') THEN
          CREATE TABLE ml_donnees_client_abonnees_specifique
        (
          client integer,
          age integer,
          tranche_age integer,
          duree integer,
          tranche_duree integer,
          depots numeric(30,0),
          salaire_moyen numeric(30,0),
          tranche_sal_moyen integer,
          irregularite integer,
          tx_irregularite float,
          tranche_irregularite integer,
          nbre_credit integer,
          tranche_nbre_credit integer,
          mnt_tot_emprunter numeric(30,0),
          tranche_tot_emprunter integer,
          lg_histo integer,
          tranche_lg_histo integer,
          tranche_sexe integer,
          mnt_restant_du numeric(30,0),
          mnt_max numeric(30,0),
          bonus_gar float,
          score_passe float,
          score_present float,
          score_futur float,
          score_final float,
          new_client boolean DEFAULT true,
          id_ag integer,
          date_creation timestamp without time zone,
          date_modif timestamp without time zone

        );
        ALTER TABLE ml_donnees_client_abonnees_specifique
          OWNER TO adbanking;

    END IF;


  RETURN output_result;
  END;
  $$
  LANGUAGE plpgsql;

SELECT script_ml_creation_table();
DROP FUNCTION script_ml_creation_table();

------------------------------------------------ ML-30 Creation champ is_mobile_lending_credit ----------------------------------------
CREATE OR REPLACE FUNCTION script_ML_30() RETURNS INT AS
  $$
  DECLARE
  output_result INTEGER = 1;
  d_tableliste_str integer = 0;
  tableliste_ident integer = 0;

  BEGIN

  tableliste_ident := (select ident from tableliste where nomc like 'adsys_produit_credit' order by ident desc limit 1);

  IF NOT EXISTS (SELECT * FROM information_schema.columns WHERE table_name='adsys_produit_credit' and column_name='is_mobile_lending_credit') THEN
    ALTER TABLE adsys_produit_credit ADD COLUMN is_mobile_lending_credit boolean DEFAULT false;
    d_tableliste_str := makeTraductionLangSyst('Est-ce un cr??dit mobile lending?');
    INSERT INTO d_tableliste VALUES ((select max(ident) from d_tableliste)+1, tableliste_ident, 'is_mobile_lending_credit', d_tableliste_str, NULL, NULL, 'bol', false, false, false);
    IF EXISTS(SELECT langue FROM adsys_langues_systeme WHERE code = 'en_GB') THEN
      INSERT INTO ad_traductions VALUES (d_tableliste_str,'en_GB','Is this a mobile lending credit?');
    END IF;
  END IF;

  RETURN output_result;
  END;
  $$
  LANGUAGE plpgsql;

SELECT script_ML_30();
DROP FUNCTION script_ML_30();

------------------------------------ ML-46 Cr??ation d'un champ localisation dans ad_agc ---------------------
CREATE OR REPLACE FUNCTION script_ml_46() RETURNS INT AS $$
  DECLARE
    tablen_id INTEGER = 0;
    tableliste_agc_id INTEGER = 0;
    output_result INTEGER = 0;
    id_str_trad INTEGER = 0;
  BEGIN

    tableliste_agc_id := (SELECT ident FROM tableliste WHERE nomc = 'ad_agc');
    id_str_trad := maketraductionlangsyst('Localisation');

    IF NOT EXISTS(SELECT * FROM tableliste WHERE nomc = 'adsys_localisation_ml') THEN
      INSERT INTO tableliste VALUES (
      (select max(ident) from tableliste)+1,
      'adsys_localisation_ml',
      id_str_trad,
      false);
      RAISE NOTICE 'Donn??es table adsys_localisation_ml rajout??s dans table tableliste';
    END IF;

    tablen_id := (SELECT ident FROM tableliste WHERE nomc = 'adsys_localisation_ml');

    IF NOT EXISTS(SELECT * FROM d_tableliste WHERE nchmpc = 'id' and tablen = tablen_id) THEN
    INSERT INTO d_tableliste VALUES ((select max(ident)
    from d_tableliste)+1, tablen_id, 'id', makeTraductionLangSyst('Id'), TRUE, NULL, 'int', NULL, TRUE, FALSE);
    END IF;

    IF NOT EXISTS(SELECT * FROM d_tableliste WHERE nchmpc = 'ml_localisation' and tablen = tableliste_agc_id) THEN
      INSERT INTO d_tableliste VALUES ((select max(ident)
      from d_tableliste)+1, tableliste_agc_id, 'ml_localisation', id_str_trad,
      null, (SELECT ident FROM d_tableliste WHERE nchmpc = 'id' and tablen = tablen_id), 'int', null, FALSE, FALSE);
      IF EXISTS(SELECT langue FROM adsys_langues_systeme WHERE code = 'en_GB') THEN
        INSERT INTO ad_traductions VALUES (id_str_trad,'en_GB','Localisation');
      END IF;
    END IF;

    IF NOT EXISTS(SELECT * FROM information_schema.columns WHERE table_name = 'ad_agc' AND column_name = 'ml_localisation') THEN
    ALTER TABLE ad_agc ADD COLUMN ml_localisation INTEGER DEFAULT 0;
    END IF;

  RETURN output_result;
  END;
  $$
  LANGUAGE plpgsql ;

SELECT script_ml_46();
DROP FUNCTION script_ml_46();
------------------------------------------ ML-47 Creation nouveau champ ml_code_agent dans la table ad_uti ------------------------
CREATE OR REPLACE FUNCTION script_ml_47() RETURNS INT AS $$
  DECLARE
    id_str_trad INTEGER = 0;
    output_result INTEGER = 0;
    tableliste_id INTEGER = 0;
  BEGIN

  tableliste_id := (SELECT ident FROM tableliste WHERE nomc = 'ad_uti');
  id_str_trad := maketraductionlangsyst('Code l''agent Mobile Lending');

  IF NOT EXISTS(SELECT * FROM d_tableliste WHERE nchmpc = 'ml_code_agent' and tablen = tableliste_id) THEN
    INSERT INTO d_tableliste VALUES ((select max(ident)
    from d_tableliste)+1, tableliste_id, 'ml_code_agent', id_str_trad,
    null, null, 'txt', null, FALSE, FALSE);
      IF EXISTS(SELECT langue FROM adsys_langues_systeme WHERE code = 'en_GB') THEN
      INSERT INTO ad_traductions VALUES (id_str_trad,'en_GB','Mobile lending agent code');
    END IF;
  END IF;

  IF NOT EXISTS(SELECT * FROM information_schema.columns WHERE table_name = 'ad_uti' AND column_name = 'ml_code_agent') THEN
    ALTER TABLE ad_uti ADD COLUMN ml_code_agent text DEFAULT null;
  END IF;

  RETURN output_result;
  END;
  $$
  LANGUAGE plpgsql ;

SELECT script_ml_47();
DROP FUNCTION script_ml_47();
-------------------------------------------ML-53 Ajout champ 'is_agent_ml'----------------------------------------------------
CREATE OR REPLACE FUNCTION script_ml_53() RETURNS INT AS $$
  DECLARE
  id_str_trad INTEGER = 0;
  output_result INTEGER = 0;
  tableliste_id INTEGER = 0;
  BEGIN

  tableliste_id := (SELECT ident FROM tableliste WHERE nomc = 'ad_uti');
  id_str_trad := maketraductionlangsyst('Est un agent mobile lending?');

  IF NOT EXISTS(SELECT * FROM d_tableliste WHERE nchmpc = 'is_agent_ml' and tablen = tableliste_id) THEN
    INSERT INTO d_tableliste VALUES ((select max(ident)
    from d_tableliste)+1, tableliste_id, 'is_agent_ml', id_str_trad,
    null, null, 'bol', null, FALSE, FALSE);
    IF EXISTS(SELECT langue FROM adsys_langues_systeme WHERE code = 'en_GB') THEN
      INSERT INTO ad_traductions VALUES (id_str_trad,'en_GB','Mobile Lending agent?');
    END IF;
  END IF;

  IF NOT EXISTS(SELECT * FROM information_schema.columns WHERE table_name = 'ad_uti' AND column_name = 'is_agent_ml') THEN
    ALTER TABLE ad_uti ADD COLUMN is_agent_ml BOOLEAN DEFAULT FALSE;
  END IF;

  RETURN output_result;
  END;
  $$
  LANGUAGE plpgsql ;

SELECT script_ml_53();
DROP FUNCTION script_ml_53();

---------------------------------------------------------------------------
	CREATE OR REPLACE FUNCTION script_ml_creation_ecran() RETURNS INT AS
$$
DECLARE
output_result INTEGER = 1;
  id_str_trad integer = 0;
    pos_ordre integer = 0;

BEGIN

	--========> Update Menu 'Out' position up
	IF EXISTS (select * from menus where nom_menu = 'Out') THEN
	 SELECT INTO pos_ordre ordre FROM menus WHERE nom_menu = 'Out';
	 UPDATE menus SET ordre = (pos_ordre+1) WHERE nom_menu = 'Out';
	END IF;


	--========> Debut Agency Banking
	--Menu
	IF NOT EXISTS (select * from menus where nom_menu = 'Gen-17') THEN
	 id_str_trad := maketraductionlangsyst('Mobile Lending');
	 INSERT INTO menus (nom_menu, libel_menu, nom_pere, pos_hierarch, ordre, is_menu,fonction, is_cliquable)
	 VALUES ('Gen-17', id_str_trad, 'Gen-3', 2, 10, TRUE, 900, TRUE);
	 IF EXISTS(SELECT langue FROM adsys_langues_systeme WHERE code = 'en_GB') THEN
	  INSERT INTO ad_traductions (id_str, langue, traduction) VALUES (id_str_trad, 'en_GB', 'Mobile Lending');
	 END IF;
	END IF;
	--Ecran
	IF NOT EXISTS (select * from ecrans where nom_ecran = 'Gen-17') THEN
	 INSERT INTO ecrans(nom_ecran, fichier, nom_menu, fonction)
	 VALUES ('Gen-17', 'modules/menus/menu.php', 'Gen-17', 900);
	END IF;


	IF NOT EXISTS (select * from menus where nom_menu = 'Mle') THEN
	 id_str_trad := maketraductionlangsyst('Nouveaux clients Mobile Lending');
	 INSERT INTO menus (nom_menu, libel_menu, nom_pere, pos_hierarch, ordre, is_menu,fonction, is_cliquable)
	 VALUES ('Mle', id_str_trad, 'Gen-17', 3, 1, TRUE, 901, FALSE);
	 IF EXISTS(SELECT langue FROM adsys_langues_systeme WHERE code = 'en_GB') THEN
	  INSERT INTO ad_traductions (id_str, langue, traduction) VALUES (id_str_trad, 'en_GB', 'New client Mobile Lending');
	 END IF;
	END IF;

	IF NOT EXISTS (select * from ecrans where nom_ecran = 'Mle-1') THEN
	 INSERT INTO ecrans(nom_ecran, fichier, nom_menu, fonction)
	 VALUES ('Mle-1', 'modules/mobile_lending/nouveau_client_mobile_lending.php', 'Mle', 901);
	END IF;

	IF NOT EXISTS (select * from ecrans where nom_ecran = 'Mle-2') THEN
	 INSERT INTO ecrans(nom_ecran, fichier, nom_menu, fonction)
	 VALUES ('Mle-2', 'modules/mobile_lending/nouveau_client_mobile_lending.php', 'Mle', 901);
	END IF;
	
	IF NOT EXISTS (select * from menus where nom_menu = 'Mlt-1') THEN
	 id_str_trad := maketraductionlangsyst('Dossiers Mobile Lending en attente');
	 INSERT INTO menus (nom_menu, libel_menu, nom_pere, pos_hierarch, ordre, is_menu,fonction, is_cliquable)
	 VALUES ('Mlt-1', id_str_trad, 'Gen-17', 3, 2, TRUE, 902, FALSE);
	 IF EXISTS(SELECT langue FROM adsys_langues_systeme WHERE code = 'en_GB') THEN
	  INSERT INTO ad_traductions (id_str, langue, traduction) VALUES (id_str_trad, 'en_GB', 'Loan Mobile Lending standby');
	 END IF;
	END IF;

	IF NOT EXISTS (select * from ecrans where nom_ecran = 'Mlt-1') THEN
	 INSERT INTO ecrans(nom_ecran, fichier, nom_menu, fonction)
	 VALUES ('Mlt-1', 'modules/mobile_lending/dossiers_mobile_lending_attente.php', 'Mlt-1', 902);
	END IF;





	RETURN output_result;
END;
$$
LANGUAGE plpgsql;

SELECT script_ml_creation_ecran();
DROP FUNCTION script_ml_creation_ecran();



CREATE OR REPLACE FUNCTION script_mob_lending() RETURNS INT AS
$$
DECLARE
output_result INTEGER = 1;
  BEGIN

  	IF NOT EXISTS(SELECT * FROM information_schema.columns WHERE table_name = 'ad_abonnement' AND column_name = 'premier_credit') THEN
    	ALTER TABLE ad_abonnement ADD COLUMN premier_credit BOOLEAN DEFAULT TRUE;
  	END IF;

	IF NOT EXISTS(SELECT * FROM information_schema.columns WHERE table_name = 'ad_abonnement' AND column_name = 'agent_prime') THEN
    	ALTER TABLE ad_abonnement ADD COLUMN agent_prime INTEGER;
  	END IF;

	IF NOT EXISTS(SELECT * FROM information_schema.columns WHERE table_name = 'ad_abonnement' AND column_name = 'signature_contrat') THEN
    	ALTER TABLE ad_abonnement ADD COLUMN signature_contrat BOOLEAN DEFAULT FALSE;
  	END IF;

	IF NOT EXISTS(SELECT * FROM information_schema.columns WHERE table_name = 'ad_abonnement' AND column_name = 'ml_mnt_max') THEN
    	ALTER TABLE ad_abonnement ADD COLUMN ml_mnt_max numeric(30,0);
  	END IF;

	IF NOT EXISTS(SELECT * FROM information_schema.columns WHERE table_name = 'ad_abonnement' AND column_name = 'ml_score') THEN
    	ALTER TABLE ad_abonnement ADD COLUMN ml_score numeric(30,2);
  	END IF;

  IF NOT EXISTS(SELECT * FROM information_schema.constraint_column_usage  WHERE table_name = 'ad_cli' and  constraint_name = 'ad_cli_id_client_unique') THEN
    ALTER TABLE ad_cli ADD CONSTRAINT  ad_cli_id_client_unique UNIQUE (id_client);
  END IF;

  IF NOT EXISTS(SELECT * FROM information_schema.referential_constraints WHERE constraint_name = 'fk_ad_cli_ml_demande_credit') THEN
    ALTER TABLE ml_demande_credit ADD CONSTRAINT fk_ad_cli_ml_demande_credit FOREIGN KEY (id_client) REFERENCES ad_cli(id_client);
  END IF;

	RETURN output_result;
END;
$$
LANGUAGE plpgsql ;

SELECT script_mob_lending();
DROP FUNCTION script_mob_lending();

------------------------------------------------------------------------------------------------------------------------
--changement v2.1
----Longueur historique en mois
----salaire moyen : calcul?? sur 6 mois et correction calcul (sur duree au lieu de 12 par d??faut)
----changement date debut et date fin pour calcul salaire moyen : compter ?? partir de la derniere echeance au lieu de la date de deboursement
----taux de regularit??
DROP TYPE IF EXISTS extract_donnees_view_v2 CASCADE;

CREATE TYPE extract_donnees_view_v2 AS
(
  categorie TEXT,
  sous_categorie TEXT,
  client integer,
  IMF text,
  agence text,
  sexe TEXT,
  nbr_credit integer ,
  dossier integer,
  mnt_cred numeric(30,0) ,
  duree integer ,
  age integer,
  lg_histo DOUBLE PRECISION,
  salaire_moyen numeric(30,0),
  regularite integer,
  nbr_retard integer ,
  max_etat integer,
  nbre_echeance numeric(30,2),
  --nbre_jours_retard_tot integer,
  score_retard numeric(30,2),
  mnt_tot_emprunts numeric(30,0),
  --salaire_moyen_montant_ech numeric(30,2),
  echeance numeric(30,0),
  montant_du numeric(30,0),
  max_jours_retard integer ,
  taux_regularite numeric(30,2),
  montant_maximum numeric(30,0)
  );
ALTER TYPE extract_donnees_view_v2
OWNER TO adbanking;

  -- Function: extraction_donnees_v2()

  -- DROP FUNCTION extraction_donnees_v2();

CREATE OR REPLACE FUNCTION extraction_donnees_mobile_lending_v2()
  RETURNS SETOF extract_donnees_view_v2 AS
  $BODY$
  DECLARE
  ligne RECORD;
  IMF text;
  agence text ;
  mnt_cred numeric(30,0) ;
  duree integer ;
  dossier integer;
  client integer;
  categorie TEXT;
  sous_categorie TEXT;
  date_demande date;
  naissance date ;
  adhesion date ;
  nbr_credit integer ;
  nmr_credit integer;
  numero integer;
  sexe TEXT;
  age integer;
  lg_histo DOUBLE PRECISION;
  fin_credit date ;
  max_etat integer ;
  ech RECORD ;
  nbr_retard integer ;
  max_date DATE;
  dateech DATE;
  cap_remb numeric(30,6);
  int_remb numeric(30,6);
  --pen_remb numeric(30,6);
  cap_att numeric(30,6);
  int_att numeric(30,6);
  pen_att numeric(30,6);
  garantie numeric(30,6);
  nbr_jours_retard integer;
  cpte_credit integer ;
  debut date ;
  fin date;
  limite integer ;
  depots numeric(30,0);
  salaire_moyen numeric(30,0);
  depots_mens numeric(30,0);
  salaire_10 numeric(30,0);
  regularite integer ;
  sal integer ;
  interv1 date;
  interv2 date;
  echeance numeric(30,0);
  dateremb date ;
  nbre_echeance numeric(30,2) ;
  nbre_jours_retard_tot numeric(30,2) ;
  score_retard numeric(30,2);
  moyenne_retard numeric(30,2);
  mnt_tot_emprunts numeric(30,0);
  salaire_moyen_montant_ech numeric(30,2);
  montant_du numeric(30,0);
  max_jours_retard integer ;
  montant_irregul numeric(30,0);
  montant_manquant  numeric(30,0);
  taux_regularite numeric(30,2);
  montant_maximum numeric(30,0);
  derniere_ech date ;

  extract_donnees extract_donnees_view_v2;

  C1 CURSOR FOR select * from ad_dcr where etat = 6 and id_client in (SELECT id_client FROM ad_cli WHERE statut_juridique = 1 AND qualite in (1,2)) ORDER BY id_client,id_doss ; --LIMIT 7 ;

  BEGIN

  TRUNCATE TABLE ml_donnees_client_credit CASCADE;

  OPEN C1 ;
  FETCH C1 INTO ligne;


  RAISE INFO 'Debut Extraction des donnees V2 ...' ;

  numero := 0 ;
  SELECT INTO IMF,agence libel_institution,libel_ag FROM ad_agc ;

  WHILE FOUND LOOP

  numero := numero + 1 ;

  dossier := ligne.id_doss;
  client := ligne.id_client ;
  mnt_cred := ligne.cre_mnt_deb;
  duree := ligne.duree_mois ;
  date_demande := ligne.cre_date_debloc ;
  max_etat := ligne.cre_retard_etat_max ;
  fin_credit := ligne.date_etat ;
  cpte_credit := ligne.cpt_liaison;


  SELECT INTO sexe (CASE pp_sexe WHEN 1 THEN 'M' WHEN 2 THEN 'F' ELSE '' END) FROM ad_cli where id_client = ligne.id_client ;
  SELECT INTO nbr_credit count(id_doss) FROM ad_dcr WHERE id_client = ligne.id_client and etat = 6 and cre_date_debloc < date_demande ;
  SELECT INTO nmr_credit num_cre FROM ad_dcr WHERE id_doss = ligne.id_doss ;
  SELECT INTO naissance pp_date_naissance FROM ad_cli WHERE id_client = ligne.id_client ;
  SELECT INTO adhesion date_adh FROM ad_cli WHERE id_client = ligne.id_client ;
  SELECT INTO garantie montant_vente FROM ad_gar WHERE id_doss = ligne.id_doss ;
  SELECT INTO echeance (sum(mnt_cap + mnt_int)/duree) FROM ad_etr WHERE id_doss = ligne.id_doss and mnt_cap != 0 ;
  SELECT INTO mnt_tot_emprunts sum(cre_mnt_deb) FROM ad_dcr WHERE id_client = ligne.id_client and etat in (5,6,9,13,14,15) and cre_date_debloc <= date_demande ;
  SELECT INTO montant_du sum(solde_cap) FROM ad_dcr a, ad_etr b WHERE a.id_doss = b.id_doss and a.id_client = ligne.id_client and etat in (5,9,13,14,15);

  IF duree <= 6 THEN
  limite = 6 ;
  ELSE
  limite = duree ;-- + 1  ' month - 1 day';
  END IF ;

  --SELECT INTO debut date_trunc('month',date(date_demande))::DATE ;
  --SELECT INTO fin (debut + (limite * interval '1 month') - interval '1 day')::DATE;
  SELECT INTO derniere_ech max(date_ech) FROM ad_etr WHERE id_doss = ligne.id_doss ;

  SELECT INTO fin (date_trunc('month',date(derniere_ech)) - interval '1 day')::DATE;
  SELECT INTO debut ((date(fin) + interval '1 day') - (limite * interval '1 month'))::DATE;


  SELECT INTO depots sum(montant) FROM ad_mouvement a, ad_ecriture b WHERE a.id_ecriture = b.id_ecriture and cpte_interne_cli = cpte_credit and sens = 'c'
  and (date_valeur between debut and fin) and type_operation not in (11,21,31,51,132,133,135,144,153,154,155,201,210,221,231,361,411,542,544,545,547) ;

  IF duree <= 6 THEN
  --salaire_moyen := (depots - mnt_cred)/12 ;
  salaire_moyen := depots/limite ;
  salaire_moyen := round(salaire_moyen);

  ELSE
  --salaire_moyen := (depots - mnt_cred)/duree ;
  salaire_moyen := depots/limite ;
  salaire_moyen := round(salaire_moyen);
  END IF ;

  salaire_moyen_montant_ech :=  0;

  age := date_part('year', age(date_demande,naissance));
  --lg_histo := date_part('year', age(date_demande,adhesion));
  lg_histo := date_part('year', age(date_demande,adhesion)) * 12 + date_part('month', age(date_demande,adhesion));

  sal:= 0 ;
  regularite := 0 ;
  montant_irregul := 0;
  montant_manquant := 0 ;

  WHILE sal < limite
  LOOP

  interv1 = (debut + (sal * interval '1 month'))::DATE ;
  interv2 = (debut + ((sal+1) * interval '1 month')- interval '1 day')::DATE ;

  SELECT INTO depots_mens sum(montant) FROM ad_mouvement a, ad_ecriture b WHERE a.id_ecriture = b.id_ecriture and
  cpte_interne_cli = cpte_credit and sens = 'c' and (date_valeur between interv1 and interv2)
  and type_operation not in (11,21,31,51,132,133,135,144,153,154,155,201,210,221,231,361,411,542,544,545,547) ;

  IF depots_mens < salaire_moyen  THEN
  montant_manquant = salaire_moyen - depots_mens ;
  montant_irregul = montant_irregul + montant_manquant ;
  regularite = regularite + 1 ;
  ELSE
  regularite = regularite + 0 ;
  END IF;

  sal = sal+1;
  END LOOP ;

  nbr_retard := 0 ;
  nbre_jours_retard_tot := 0 ;
  nbre_echeance := 0 ;
  --salaire_moyen_montant_ech :=  salaire_moyen/echeance;

  IF salaire_moyen != 0 THEN
  taux_regularite := (1/salaire_moyen) * montant_irregul ;
  ELSE
  taux_regularite := montant_irregul ;
  END IF ;

  IF regularite > 0 THEN
  montant_maximum := (salaire_moyen - (montant_irregul/regularite))*0.3 ;
  ELSE
  montant_maximum := (salaire_moyen)*0.3 ;
  END IF ;

  max_jours_retard := 0 ;

  FOR ech IN SELECT * FROM ad_etr WHERE id_doss = ligne.id_doss ORDER BY id_ech
  LOOP

  SELECT INTO dateech date_ech FROM ad_etr WHERE id_doss = ligne.id_doss AND id_ech = ech.id_ech;
  SELECT INTO dateremb max(date_remb) FROM ad_sre WHERE id_doss = ligne.id_doss AND id_ech = ech.id_ech;

  nbr_jours_retard := date_part('day', dateremb::timestamp - dateech::timestamp);

  IF nbr_jours_retard < 0 THEN
  nbr_jours_retard = 0 ;
  END IF ;

  IF nbr_jours_retard > max_jours_retard	THEN
  max_jours_retard = nbr_jours_retard;
  --ELSE
  END IF;

  nbre_jours_retard_tot := nbre_jours_retard_tot + nbr_jours_retard ;

  SELECT INTO cap_remb sum (mnt_remb_cap) from ad_sre where id_doss = ligne.id_doss and id_ech = ech.id_ech and date_remb <= dateech;
  SELECT INTO int_remb sum (mnt_remb_int) from ad_sre where id_doss = ligne.id_doss and id_ech = ech.id_ech and date_remb <= dateech;

  SELECT INTO cap_att mnt_cap from ad_etr where id_doss = ligne.id_doss and id_ech = ech.id_ech;
  SELECT INTO int_att mnt_int from ad_etr where id_doss = ligne.id_doss and id_ech = ech.id_ech;

  --SELECT INTO max_date MAX(date_remb) FROM ad_sre WHERE id_ag = id_agence AND id_doss = ligne.id_doss AND id_ech = ech.id_ech;
  IF ( cap_remb = cap_att and int_remb >= int_att  ) THEN
  nbr_retard := nbr_retard + 0;
  ELSE
  nbr_retard := nbr_retard  + 1;
  END IF;

  nbre_echeance := nbre_echeance + 1 ;
  END LOOP ;

  moyenne_retard := nbre_jours_retard_tot/nbre_echeance ;
  score_retard := 100 - moyenne_retard ;

  IF garantie is null THEN
  categorie = 'Cat??gorie 1';
  sous_categorie = '1.' ;
  ELSIF garantie >= 0 THEN
  categorie = 'Cat??gorie 2';
  sous_categorie = '2.';
  ELSE
  categorie = '';
  END IF;

  --IF nbr_credit >= 2 AND max_etat = 0 THEN
  IF max_etat = 0 THEN

  sous_categorie = sous_categorie || '1' ;

  --ELSIF nbr_credit >= 2 AND max_etat = 1 THEN
  ELSIF max_etat = 1 THEN

  sous_categorie = sous_categorie || '2' ;

  ELSIF max_etat = 2 THEN

  sous_categorie = sous_categorie || '3' ;

  ELSIF max_etat = 3 THEN

  sous_categorie = sous_categorie || '4' ;

  ELSIF max_etat > 3 THEN

  sous_categorie = sous_categorie || '5' ;

  END IF;

  --RAISE NOTICE 'Dossier % Sexe % Nombre % Num % Montant % Duree % Age % Histo % NbrRet % MaxEta %', ligne.id_doss, sexe, nbr_credit, nmr_credit,round(mnt_cred), duree,age, lg_histo, nbr_retard, max_etat  ;
  INSERT INTO ml_donnees_client_credit VALUES (categorie,sous_categorie , client ,IMF,agence , sexe, nbr_credit, dossier , mnt_cred, duree, age ,lg_histo,
                                                                                                                                                 salaire_moyen , regularite,  nbr_retard, max_etat, nbre_echeance, score_retard, mnt_tot_emprunts, echeance, montant_du, max_jours_retard,
                                               taux_regularite, montant_maximum);
  --RETURN NEXT extract_donnees;



  FETCH C1 INTO ligne;
  END LOOP;
  CLOSE C1;


  RETURN;
  END;
  $BODY$
  LANGUAGE plpgsql VOLATILE
  COST 100
  ROWS 1000;
  ALTER FUNCTION extraction_donnees_mobile_lending_v2()
OWNER TO postgres;

-------------------------------------------------------------------------------------------------------------------


DROP TYPE IF EXISTS mise_a_jour_donnees_view_v2 CASCADE;

CREATE TYPE mise_a_jour_donnees_view_v2 AS
(
  client integer,
  duree integer,
  depots numeric(30,0),
  salaire_moyen numeric(30,0),
  irregularite integer,
  nbre_credit integer,
  mnt_tot_emprunter numeric(30,0),
  lg_histo integer,
  mnt_restant_du numeric(30,0),
  mnt_max numeric(30,0),
  score_passe float,
  score_present float,
  score_futur float,
  score_final float,
  id_ag integer,
  date_creation timestamp without time zone,
  date_modif timestamp without time zone

);
ALTER TYPE mise_a_jour_donnees_view_v2
OWNER TO adbanking;

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

  ligne_extract_donnee_credit refcursor;

  mise_a_jour_donnees mise_a_jour_donnees_view_v2;
  output INTEGER :=0;
  C1 CURSOR FOR select b.* from ad_abonnement b INNER JOIN ad_cli c ON c.id_client = b.id_client where deleted = 'f' and c.statut_juridique = 1 and b.id_service = 1;

  BEGIN

  TRUNCATE TABLE ml_donnees_client_abonnees CASCADE;

  OPEN C1 ;
  FETCH C1 INTO ligne;

  WHILE FOUND LOOP

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
  ELSE
  limite = duree ;-- + 1  ' month - 1 day';
  END IF ;




  -- Salaire moyen abo
  SELECT INTO fin (date_trunc('month',date(date(now()))) - interval '1 day')::DATE;
  SELECT INTO debut ((date(fin) + interval '1 day') - (limite * interval '1 month'))::DATE;
  RAISE NOTICE 'fin %',fin;RAISE NOTICE 'debut %',debut;


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
  salaire_moyen_cli := round(salaire_moyen_cli);RAISE NOTICE 'limit %',limite;
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


  --score pass??
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





  INSERT INTO ml_donnees_client_abonnees(client,age,duree,depots,salaire_moyen,irregularite,tx_irregularite,nbre_credit,mnt_tot_emprunter,lg_histo,mnt_restant_du,tranche_sexe,mnt_max,score_passe,score_present,id_ag,date_creation)
  VALUES (client, age_cli,duree, depots, salaire_moyen_cli, irregularite,tx_irregularite,nbre_credit,mnt_tot_emprunter,lg_histo_cli,mnt_restant_du,sexe,mnt_max,score_passe,score_present,agence,date_crea);

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

------------------------------------------------------------------------------------------------------------------------

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

  ligne_extract_donnee_credit refcursor;

  mise_a_jour_donnees mise_a_jour_donnees_view_v2;
  output INTEGER :=0;
  C1 CURSOR FOR select b.* from ad_abonnement b INNER JOIN ad_cli c ON c.id_client = b.id_client where deleted = 'f' and c.statut_juridique = 1 and b.id_service = 1 and b.id_client = client_spe;

  BEGIN


  OPEN C1 ;
  FETCH C1 INTO ligne;

  WHILE FOUND LOOP

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
  ELSE
  limite = duree ;-- + 1  ' month - 1 day';
  END IF ;




  -- Salaire moyen abo
  SELECT INTO fin (date_trunc('month',date(date(now()))) - interval '1 day')::DATE;
  SELECT INTO debut ((date(fin) + interval '1 day') - (limite * interval '1 month'))::DATE;
  RAISE NOTICE 'fin %',fin;RAISE NOTICE 'debut %',debut;


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
  salaire_moyen_cli := round(salaire_moyen_cli);RAISE NOTICE 'limit %',limite;
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


  --score pass??
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





  INSERT INTO ml_donnees_client_abonnees(client,age,duree,depots,salaire_moyen,irregularite,tx_irregularite,nbre_credit,mnt_tot_emprunter,lg_histo,mnt_restant_du,tranche_sexe,mnt_max,score_passe,score_present,id_ag,date_creation)
  VALUES (client, age_cli,duree, depots, salaire_moyen_cli, irregularite,tx_irregularite,nbre_credit,mnt_tot_emprunter,lg_histo_cli,mnt_restant_du,sexe,mnt_max,score_passe,score_present,agence,date_crea);

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



------------------------------------------------------------------------------------------------------------------------------------------------------
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

ligne_extract_donnee_credit refcursor;

mise_a_jour_donnees mise_a_jour_donnees_view_v2;
  output INTEGER :=0;
C1 CURSOR FOR select b.* from ad_abonnement b INNER JOIN ad_cli c ON c.id_client = b.id_client where deleted = 'f' and c.statut_juridique = 1 and b.id_service = 1 and b.id_client = client_spe;

BEGIN

  TRUNCATE TABLE ml_donnees_client_abonnees_specifique CASCADE;

  OPEN C1 ;
  FETCH C1 INTO ligne;

	  WHILE FOUND LOOP

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
	  ELSE
		 limite = duree ;-- + 1  ' month - 1 day';
	  END IF ;




-- Salaire moyen abo
	  SELECT INTO fin (date_trunc('month',date(date(now()))) - interval '1 day')::DATE;
	  SELECT INTO debut ((date(fin) + interval '1 day') - (limite * interval '1 month'))::DATE;
	  RAISE NOTICE 'fin %',fin;RAISE NOTICE 'debut %',debut;

	  RAISE NOTICE 'date debut => %,    Date_fin => %',debut,fin;
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
		salaire_moyen_cli := round(salaire_moyen_cli);RAISE NOTICE 'limit %',limite;
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


--score pass??
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






	INSERT INTO ml_donnees_client_abonnees_specifique(client,age,duree,depots,salaire_moyen,irregularite,tx_irregularite,nbre_credit,mnt_tot_emprunter,lg_histo,mnt_restant_du,tranche_sexe,mnt_max,score_passe,score_present,id_ag,date_creation)
	VALUES (client, age_cli,duree, depots, salaire_moyen_cli, irregularite,tx_irregularite,nbre_credit,mnt_tot_emprunter,lg_histo_cli,mnt_restant_du,sexe,mnt_max,score_passe,score_present,agence,date_crea);


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



------------------------------------------------------------------------------------------------------------------------------------------------------
-- Function: f_getRemMobLendingDataForProducer(text, text, text)

-- DROP FUNCTION f_getRemMobLendingDataForProducer(text, text, text);
CREATE OR REPLACE function f_getRemMobLendingDataForProducer(text, text, text) returns TABLE (num_sms varchar, langue integer, id_transaction text, num_imf text)
LANGUAGE plpgsql
AS $$
declare
	v_id_client ALIAS for $1;
	v_id_doss ALIAS for $2;
	v_mnt_dem ALIAS for $3;

BEGIN

RETURN QUERY
	SELECT abn.num_sms, abn.langue, mldem.id_transaction, agc.tel
	FROM ad_abonnement abn
	INNER JOIN ml_demande_credit mldem ON abn.id_client = mldem.id_client
	INNER JOIN ad_agc agc ON abn.id_ag = agc.id_ag
	WHERE abn.deleted = 'f'
	AND abn.id_client = cast(v_id_client as INTEGER)
	AND mldem.id_doss = cast(v_id_doss as INTEGER)
	AND mldem.mnt_dem = cast(v_mnt_dem as NUMERIC);

END;
$$;