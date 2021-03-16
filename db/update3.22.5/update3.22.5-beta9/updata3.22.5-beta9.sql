CREATE OR REPLACE FUNCTION script_at_243() RETURNS INT AS
$$
DECLARE
output_result INTEGER = 1;
id_str_trad integer = 0;

BEGIN
  ----------------------------------------------------------------------------------------------------------------------------------------------------
  IF NOT EXISTS (select * from menus where nom_menu = 'Vdr') THEN
    id_str_trad := maketraductionlangsyst('Visualisation des rapports imprimés');
    INSERT INTO menus (nom_menu, libel_menu, nom_pere, pos_hierarch, ordre, is_menu,fonction, is_cliquable) VALUES ('Vdr', id_str_trad, 'Gen-13', 3, 10, TRUE, 398, TRUE);
    IF EXISTS(SELECT langue FROM adsys_langues_systeme WHERE code = 'en_GB') THEN
      INSERT INTO ad_traductions (id_str, langue, traduction) VALUES (id_str_trad, 'en_GB', 'Viewing printed reports');
    END IF;
  END IF;

  ----------------------------------------------------------------------------------------------------------------------------------------------------
  IF NOT EXISTS (select * from menus where nom_menu = 'Vdr-1') THEN
    id_str_trad := maketraductionlangsyst('Sélection des type de rapports');
    INSERT INTO menus (nom_menu, libel_menu, nom_pere, pos_hierarch, ordre, is_menu,fonction, is_cliquable) VALUES ('Vdr-1', id_str_trad, 'Vdr', 4, 1, FALSE, null, FALSE);
    IF EXISTS(SELECT langue FROM adsys_langues_systeme WHERE code = 'en_GB') THEN
      INSERT INTO ad_traductions (id_str, langue, traduction) VALUES (id_str_trad, 'en_GB', 'Selection of report types');
    END IF;
  END IF;

  IF NOT EXISTS (select * from ecrans where nom_ecran = 'Vdr-1') THEN
  INSERT INTO ecrans(nom_ecran, fichier, nom_menu, fonction) VALUES ('Vdr-1', 'modules/rapports/rapport_backup.php', 'Vdr-1', 398);
  END IF;

  ----------------------------------------------------------------------------------------------------------------------------------------------------
  IF NOT EXISTS (select * from menus where nom_menu = 'Vdr-2') THEN
    id_str_trad := maketraductionlangsyst('Critères des rapports');
    INSERT INTO menus (nom_menu, libel_menu, nom_pere, pos_hierarch, ordre, is_menu,fonction, is_cliquable) VALUES ('Vdr-2', id_str_trad, 'Vdr', 4, 2, FALSE, null, FALSE);
    IF EXISTS(SELECT langue FROM adsys_langues_systeme WHERE code = 'en_GB') THEN
      INSERT INTO ad_traductions (id_str, langue, traduction) VALUES (id_str_trad, 'en_GB', 'Reports criteria');
    END IF;
  END IF;

  IF NOT EXISTS (select * from ecrans where nom_ecran = 'Vdr-2') THEN
    INSERT INTO ecrans(nom_ecran, fichier, nom_menu, fonction) VALUES ('Vdr-2', 'modules/rapports/rapport_backup.php', 'Vdr-2', 398);
  END IF;

  ----------------------------------------------------------------------------------------------------------------------------------------------------
  IF NOT EXISTS (select * from menus where nom_menu = 'Vdr-21') THEN
    id_str_trad := maketraductionlangsyst('Liste des rapports');
    INSERT INTO menus (nom_menu, libel_menu, nom_pere, pos_hierarch, ordre, is_menu,fonction, is_cliquable) VALUES ('Vdr-21', id_str_trad, 'Vdr', 4, 3, FALSE, null, FALSE);
    IF EXISTS(SELECT langue FROM adsys_langues_systeme WHERE code = 'en_GB') THEN
      INSERT INTO ad_traductions (id_str, langue, traduction) VALUES (id_str_trad, 'en_GB', 'Reports list');
    END IF;
  END IF;

  IF NOT EXISTS (select * from ecrans where nom_ecran = 'Vdr-21') THEN
    INSERT INTO ecrans(nom_ecran, fichier, nom_menu, fonction) VALUES ('Vdr-21', 'modules/rapports/rapport_backup.php', 'Vdr-21', 398);
  END IF;

RETURN output_result;
END;
$$
LANGUAGE plpgsql;

SELECT script_at_243();
DROP FUNCTION script_at_243();

----------------------------------------------------------------------------------------------------------------------------------------

CREATE OR REPLACE FUNCTION script_at_216() RETURNS INT AS
$$
DECLARE
output_result INTEGER = 1;
id_str_trad integer = 0;

BEGIN

  -- Creation ecran est menu pour consulation des compte en local
  ----------------------------------------------------------------------------------------------------------------------------------------------------
  IF NOT EXISTS (select * from menus where nom_menu = 'Ccv') THEN
    id_str_trad := maketraductionlangsyst('Consultation d''un compte via agent ');
    INSERT INTO menus (nom_menu, libel_menu, nom_pere, pos_hierarch, ordre, is_menu,fonction, is_cliquable) VALUES ('Ccv', id_str_trad, 'Gen-16', 3, 18, TRUE, 789, TRUE);
    IF EXISTS(SELECT langue FROM adsys_langues_systeme WHERE code = 'en_GB') THEN
      INSERT INTO ad_traductions (id_str, langue, traduction) VALUES (id_str_trad, 'en_GB', 'Consultation of an account via agent');
    END IF;
  END IF;

  ----------------------------------------------------------------------------------------------------------------------------------------------------
  IF NOT EXISTS (select * from menus where nom_menu = 'Ccv-1') THEN
    id_str_trad := maketraductionlangsyst('Selection client ');
    INSERT INTO menus (nom_menu, libel_menu, nom_pere, pos_hierarch, ordre, is_menu,fonction, is_cliquable) VALUES ('Ccv-1', id_str_trad, 'Ccv', 4, 1, FALSE, null, FALSE);
    IF EXISTS(SELECT langue FROM adsys_langues_systeme WHERE code = 'en_GB') THEN
      INSERT INTO ad_traductions (id_str, langue, traduction) VALUES (id_str_trad, 'en_GB', 'Client selection');
    END IF;
  END IF;

  IF NOT EXISTS (select * from ecrans where nom_ecran = 'Ccv-1') THEN
    INSERT INTO ecrans(nom_ecran, fichier, nom_menu, fonction) VALUES ('Ccv-1', 'modules/agency_banking/consultation_compte.php', 'Ccv-1', 789);
  END IF;

  ----------------------------------------------------------------------------------------------------------------------------------------------------
  IF NOT EXISTS (select * from menus where nom_menu = 'Ccv-2') THEN
    id_str_trad := maketraductionlangsyst('Choix du compte   ');
    INSERT INTO menus (nom_menu, libel_menu, nom_pere, pos_hierarch, ordre, is_menu,fonction, is_cliquable) VALUES ('Ccv-2', id_str_trad, 'Ccv', 4, 2, FALSE, null, FALSE);
    IF EXISTS(SELECT langue FROM adsys_langues_systeme WHERE code = 'en_GB') THEN
      INSERT INTO ad_traductions (id_str, langue, traduction) VALUES (id_str_trad, 'en_GB', 'Accounts choice');
    END IF;
  END IF;

  IF NOT EXISTS (select * from ecrans where nom_ecran = 'Ccv-2') THEN
    INSERT INTO ecrans(nom_ecran, fichier, nom_menu, fonction) VALUES ('Ccv-2', 'modules/agency_banking/consultation_compte.php', 'Ccv-2', 789);
  END IF;

  ----------------------------------------------------------------------------------------------------------------------------------------------------

  IF NOT EXISTS (select * from menus where nom_menu = 'Ccv-3') THEN
    id_str_trad := maketraductionlangsyst('Consultation compte ');
    INSERT INTO menus (nom_menu, libel_menu, nom_pere, pos_hierarch, ordre, is_menu,fonction, is_cliquable) VALUES ('Ccv-3', id_str_trad, 'Ccv', 4, 3, FALSE, null, FALSE);
    IF EXISTS(SELECT langue FROM adsys_langues_systeme WHERE code = 'en_GB') THEN
      INSERT INTO ad_traductions (id_str, langue, traduction) VALUES (id_str_trad, 'en_GB', 'Account review');
    END IF;
  END IF;

  IF NOT EXISTS (select * from ecrans where nom_ecran = 'Ccv-3') THEN
    INSERT INTO ecrans(nom_ecran, fichier, nom_menu, fonction) VALUES ('Ccv-3', 'modules/agency_banking/consultation_compte.php', 'Ccv-3', 789);
  END IF;

  ----------------------------------------------------------------------------------------------------------------------------------------------------

  IF NOT EXISTS (select * from menus where nom_menu = 'Ccv-4') THEN
    id_str_trad := maketraductionlangsyst('Rapport PDF ');
    INSERT INTO menus (nom_menu, libel_menu, nom_pere, pos_hierarch, ordre, is_menu,fonction, is_cliquable) VALUES ('Ccv-4', id_str_trad, 'Ccv', 4, 4, FALSE, null, FALSE);
    IF EXISTS(SELECT langue FROM adsys_langues_systeme WHERE code = 'en_GB') THEN
      INSERT INTO ad_traductions (id_str, langue, traduction) VALUES (id_str_trad, 'en_GB', 'PDF report');
    END IF;
  END IF;

  IF NOT EXISTS (select * from ecrans where nom_ecran = 'Ccv-4') THEN
    INSERT INTO ecrans(nom_ecran, fichier, nom_menu, fonction) VALUES ('Ccv-4', 'modules/agency_banking/consultation_compte.php', 'Ccv-4', 789);
  END IF;


  ----------------------------------------------------------------------------------------------------------------------------------------------------

  IF NOT EXISTS (select * from menus where nom_menu = 'Ccv-5') THEN
    id_str_trad := maketraductionlangsyst('Export CSV ');
    INSERT INTO menus (nom_menu, libel_menu, nom_pere, pos_hierarch, ordre, is_menu,fonction, is_cliquable) VALUES ('Ccv-5', id_str_trad, 'Ccv', 4, 5, FALSE, null, FALSE);
    IF EXISTS(SELECT langue FROM adsys_langues_systeme WHERE code = 'en_GB') THEN
      INSERT INTO ad_traductions (id_str, langue, traduction) VALUES (id_str_trad, 'en_GB', 'CSV export');
    END IF;
  END IF;

  IF NOT EXISTS (select * from ecrans where nom_ecran = 'Ccv-5') THEN
    INSERT INTO ecrans(nom_ecran, fichier, nom_menu, fonction) VALUES ('Ccv-5', 'modules/agency_banking/consultation_compte.php', 'Ccv-5', 789);
  END IF;

  ----------------------------------------------------------------------------------------------------------------------------------------------------


  -- Creation ecran pour les conusltion de compte ne deplacé via agent
  IF NOT EXISTS (select * from ecrans where nom_ecran = 'Ccd-1') THEN
    INSERT INTO ecrans(nom_ecran, fichier, nom_menu, fonction) VALUES ('Ccd-1', 'modules/agency_banking/consultation_compte_via_agent.php', 'God', 788);
  END IF;

  IF NOT EXISTS (select * from ecrans where nom_ecran = 'Ccd-2') THEN
    INSERT INTO ecrans(nom_ecran, fichier, nom_menu, fonction) VALUES ('Ccd-2', 'modules/agency_banking/consultation_compte_via_agent.php', 'God', 788);
  END IF;


  IF NOT EXISTS (select * from ecrans where nom_ecran = 'Ccd-3') THEN
    INSERT INTO ecrans(nom_ecran, fichier, nom_menu, fonction) VALUES ('Ccd-3', 'modules/agency_banking/consultation_compte_via_agent.php', 'God', 788);
  END IF;

  IF NOT EXISTS (select * from ecrans where nom_ecran = 'Ccd-4') THEN
    INSERT INTO ecrans(nom_ecran, fichier, nom_menu, fonction) VALUES ('Ccd-4', 'modules/agency_banking/consultation_compte_via_agent.php', 'God', 788);
  END IF;


RETURN output_result;
END;
$$
LANGUAGE plpgsql;

SELECT script_at_216();
DROP FUNCTION script_at_216();

----------------------------------------------------------------------------------------------------------------------------------------
CREATE OR REPLACE FUNCTION script_at_239() RETURNS INT AS
$$
  DECLARE
  output_result INTEGER = 1;
  id_str_trad integer = 0;

  BEGIN
    IF NOT EXISTS(SELECT * FROM pg_class where relname = 'ad_cli_hist_seq') THEN
      CREATE SEQUENCE ad_cli_hist_seq
      INCREMENT 1
      MINVALUE 1
      MAXVALUE 9999999999999
      START 10711527
      CACHE 1;
      ALTER TABLE ad_cli_hist_seq
      OWNER TO postgres;
    END IF;

    IF NOT EXISTS(SELECT * FROM information_schema.tables WHERE table_name = 'ad_cli_hist') THEN
        CREATE TABLE ad_cli_hist
        (
          id_client_hist integer NOT NULL DEFAULT nextval(('ad_cli_hist_seq'::text)::regclass),
          id_client integer,
          anc_id_client text,
          statut_juridique integer,
          qualite integer,
          adresse text,
          code_postal text,
          ville text,
          pays integer,
          pp_pays_naiss integer,
          num_tel text,
          num_fax text,
          num_port text,
          email text,
          id_cpte_base integer,
          id_loc1 integer,
          id_loc2 integer,
          loc3 text,
          date_adh timestamp without time zone,
          nbre_parts integer,
          etat integer,
          date_rupt timestamp without time zone,
          nb_imf smallint,
          nb_bk smallint,
          sect_act integer,
          dern_modif timestamp without time zone,
          utilis_modif integer,
          date_crea timestamp without time zone,
          utilis_crea integer,
          gestionnaire integer,
          langue integer,
          nbre_credits smallint DEFAULT 0,
          date_defection timestamp without time zone,
          pp_nom text,
          pp_prenom text,
          pp_date_naissance timestamp without time zone,
          pp_lieu_naissance text,
          pp_sexe integer,
          pp_nationalite integer,
          pp_type_piece_id integer,
          pp_date_piece_id date,
          pp_lieu_delivrance_id text,
          pp_nm_piece_id text,
          pp_date_exp_id date,
          pp_etat_civil integer,
          pp_nbre_enfant smallint,
          pp_casier_judiciaire boolean,
          pp_revenu numeric(30,6),
          pp_id_gi integer,
          pp_pm_patrimoine text,
          pp_pm_activite_prof text,
          pp_employeur text,
          pp_fonction text,
          pm_raison_sociale text,
          pm_abreviation text,
          pm_date_expiration date,
          pm_date_notaire date,
          pm_date_depot_greffe date,
          pm_lieu_depot_greffe text,
          pm_numero_reg_nat text,
          pm_numero_nric text,
          pm_lieu_nric text,
          pm_nature_juridique text,
          pm_tel2 text,
          pm_tel3 text,
          pm_email2 text,
          pm_date_constitution timestamp without time zone,
          pm_agrement_nature text,
          pm_agrement_autorite text,
          pm_agrement_numero integer,
          pm_agrement_date timestamp without time zone,
          gi_nom text,
          gi_date_agre timestamp without time zone,
          gi_nbre_membr smallint,
          gi_date_dissol timestamp without time zone,
          raison_defection text,
          tmp_already_accessed boolean,
          pm_categorie integer,
          langue_correspondance text,
          gs_responsable integer,
          solde_frais_adhesion_restant numeric(30,6) DEFAULT 0,
          commentaires_cli text,
          id_ag integer ,
          pp_is_vip boolean DEFAULT false,
          date_creation timestamp without time zone DEFAULT ((now())::character varying(23))::timestamp without time zone,
          date_modif timestamp without time zone,
          nbre_parts_lib integer DEFAULT 0,
          mnt_quotite numeric(30,6),
          pp_partenaire integer,
          matricule text,
          categorie integer,
          classe integer,
          id_card integer,
          province integer,
          district integer,
          secteur integer,
          cellule integer,
          village integer,
          classe_socio_economique integer,
          education integer DEFAULT 0,
          nbre_hommes_grp integer,
          nbre_femmes_grp integer,
          is_login_agb boolean DEFAULT false,
          login_appr_creation text,
          mnt_retrait_limit_ewallet_jounalier numeric(30,6),
          date_retrait_limit_ewallet_jounalier date,
          mnt_depot_limit_ewallet_jounalier numeric(30,6),
          date_depot_limit_ewallet_jounalier date,
          client_zone integer,
          photo_path text,
          signature_path text,
          CONSTRAINT ad_cli_his_pkey PRIMARY KEY (id_client_hist, id_ag)
        )
          WITH (
          OIDS=FALSE
        );
        ALTER TABLE ad_cli_hist
        OWNER TO postgres;
    END IF;

    -- Insertion du table 'adsys_zone_client' dans la table tableliste
    IF NOT EXISTS(SELECT * FROM tableliste WHERE nomc = 'ad_cli_hist') THEN
        INSERT INTO tableliste VALUES ((select max(ident) from tableliste)+1, 'ad_cli_hist', maketraductionlangsyst('Client historique'), true);
        RAISE NOTICE 'Données table ad_cli_hist rajoutés dans table tableliste';
    END IF;


    RETURN output_result;
    END;
    $$
    LANGUAGE plpgsql;

SELECT script_at_239();
DROP FUNCTION script_at_239();