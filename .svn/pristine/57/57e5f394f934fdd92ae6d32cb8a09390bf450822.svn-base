-- Script de mise à jour de la base de données de la version 2.8 à la version 3.0

-- pour mettre à jour menus et écrans
DELETE FROM ecrans;
DELETE FROM menus;

DELETE FROM d_tableliste;
DELETE FROM tableliste;

-- On supprime cette fonction car on va la recharger
DROP FUNCTION PreleveFraisTenueCpt(INTEGER, TEXT, INTEGER);
DROP FUNCTION PreleveInteretsDebiteurs(DATE, TEXT);
DROP TYPE cpte_frais CASCADE;
DROP TYPE cpte_deb CASCADE;

-- Déclaration de 2 fonctions utilitaires, elles sont également présentent dans fonctions.sql
-- mais on en a besoin ici tout de suite, donc on les copie exceptionnellement ici aussi

-- Fonction utilitaire qui ajoute un champ dans une table s'il n'est pas encore présent
CREATE OR REPLACE FUNCTION add_column_to_table(text, text, text)
RETURNS boolean AS '
DECLARE
  table_name ALIAS FOR $1;
  column_name ALIAS FOR $2;
  column_type ALIAS FOR $3;
  column_exists boolean;
  SQL text;

BEGIN
  SELECT INTO column_exists EXISTS (SELECT attname FROM pg_attribute WHERE attname = column_name AND attrelid = (SELECT relfilenode FROM pg_class WHERE relname = table_name));
  IF NOT column_exists THEN
    SQL = ''ALTER TABLE ''||table_name||'' ADD COLUMN ''||column_name||'' ''||column_type||'';'';
    EXECUTE SQL;
    RETURN true;
  ELSE
    RETURN false;
  END IF;
END;
' LANGUAGE plpgsql;

-- Fonction utilitaire qui supprime un champ dans une table s'il est bien présent
CREATE OR REPLACE FUNCTION delete_column_from_table(text, text)
RETURNS boolean AS '
DECLARE
  table_name ALIAS FOR $1;
  column_name ALIAS FOR $2;
  column_exists boolean;
  SQL text;

BEGIN
  SELECT INTO column_exists EXISTS (SELECT attname FROM pg_attribute WHERE attname = column_name AND attrelid = (SELECT relfilenode FROM pg_class WHERE relname = table_name));
  IF column_exists THEN
    SQL = ''ALTER TABLE ''||table_name||'' DROP COLUMN ''||column_name||'';'';
    EXECUTE SQL;
    RETURN true;
  ELSE
    RETURN false;
  END IF;
END;
' LANGUAGE plpgsql;


-- Suppression du prélèvement automatique des frais en attente par un déclencheur. Le batch le fait maintenant.
DROP FUNCTION trig_preleve_frais_attente() CASCADE;

-- Corrections de la 2.8.2
SELECT delete_column_from_table('ad_agc', 'licence');

-- Corrections de la 2.8.3
-- Correction clé primaire sur adsys_etat_credit_cptes
ALTER TABLE adsys_etat_credit_cptes DROP CONSTRAINT adsys_etat_credit_cptes_pkey;
ALTER TABLE adsys_etat_credit_cptes ADD PRIMARY KEY ("id_etat_credit", "id_prod_cre");

-- Correction pour les crédits passés en perte avant la 2.8
-- Recopie de la perte en capital dans la dernière échéance non remboursée des crédits passés en perte
-- On ne considère que les échéances ayant un solde_cap = 0 car il ne faut pas mettre à jour les échéanciers
-- des dossiers créés sur la 2.8 !
UPDATE ad_etr SET solde_cap = (SELECT perte_capital FROM ad_dcr WHERE ad_etr.id_doss = ad_dcr.id_doss AND etat = '9')
  WHERE remb = false AND solde_cap = 0 AND id_ech = (SELECT MAX(id_ech) FROM ad_etr a WHERE ad_etr.id_doss = a.id_doss);

-- Suppression de la colonne annulation dans ad_his car l'annulation de fonction n'est plus utilisée.
SELECT delete_column_from_table('ad_his', 'annulation');

-- Corrections de la 2.8.4
-- Constante commission de change
SELECT add_column_to_table ('ad_agc', 'constante_comm_change', 'integer');
ALTER TABLE ad_agc ALTER COLUMN constante_comm_change set default 0;

-- Affichage des coordonnées et réfèrences
SELECT add_column_to_table ('ad_agc', 'imprim_coordonnee', 'BOOLEAN');
ALTER TABLE ad_agc ALTER COLUMN imprim_coordonnee set default 'false';

--Tenir compte du différé en échéance par l'épargne nantie
SELECT add_column_to_table ('adsys_produit_credit', 'differe_epargne_nantie', 'BOOLEAN');
ALTER TABLE adsys_produit_credit ALTER COLUMN differe_epargne_nantie set default 'true';
UPDATE adsys_produit_credit set differe_epargne_nantie='true';

-- Pouvoir reporter l'arrondi des échéances à la dernière
SELECT add_column_to_table ('adsys_produit_credit', 'report_arrondi', 'BOOLEAN');
ALTER TABLE adsys_produit_credit ALTER COLUMN report_arrondi set default 'true';
UPDATE adsys_produit_credit set report_arrondi='true';
-- Calculer les intérêts sur les échéances diffèrées
SELECT add_column_to_table ('adsys_produit_credit', 'calcul_interet_differe', 'BOOLEAN');
ALTER TABLE adsys_produit_credit ALTER COLUMN calcul_interet_differe set default 'true';
UPDATE adsys_produit_credit set calcul_interet_differe='true';
-- Prélèvement des commission dans le montant du crédit lors du déboursement
SELECT add_column_to_table ('ad_dcr', 'prelev_commission', 'BOOLEAN');
ALTER TABLE ad_dcr ALTER COLUMN prelev_commission set default 'false';
--Ajout du champs de prelevement des frais de dossiers
SELECT add_column_to_table ('ad_dcr', 'cpt_prelev_frais', 'integer');
UPDATE ad_dcr SET cpt_prelev_frais=cpt_liaison WHERE cpt_prelev_frais is null;

-- Suppression des objets lourds dans ad_cli & ad_pers_ext
SELECT delete_column_from_table('ad_cli', 'photo');
SELECT delete_column_from_table('ad_cli', 'signature');
SELECT delete_column_from_table('ad_pers_ext', 'photo');
SELECT delete_column_from_table('ad_pers_ext', 'signature');

-- Changement de clé primaire pour ad_ses (#994)
ALTER TABLE ad_ses DROP CONSTRAINT ad_ses_pkey;
ALTER TABLE ad_ses ADD PRIMARY KEY (id_sess);

-- Supprimer la colonne gi_categorie qui est maintenant devenue inutile
SELECT delete_column_from_table('ad_cli', 'gi_categorie');

-- Corrections de la 2.8.5
UPDATE ad_cpt_ope_cptes SET categorie_cpte = 4 WHERE type_operation = 90 AND sens = 'd';


-- Corrections propres à la 3.0
-- Suppression de la table ad_comptable_attente
DROP SEQUENCE ad_comptable_attente_seq ;
DROP TABLE ad_comptable_attente ;
DELETE FROM d_tableliste WHERE tablen = 103;
DELETE FROM tableliste WHERE ident = 103;

-- Création de la table systéme pour la version du schéma
CREATE SEQUENCE "adsys_version_schema_id_seq" start 1 increment 1 maxvalue 999999 minvalue 1 cache 1 ;
CREATE TABLE "adsys_version_schema" (
	"id" int4 DEFAULT nextval('adsys_version_schema_id_seq'::text) NOT NULL,
	"version" text DEFAULT '1.1.0'::text,
	"date_version" timestamp,
	PRIMARY KEY("id")
	);
-- POUR CHAQUE MODIFICATION DU SCHEMA, APPLIQUEZ LA LOGIQUE
-- Incrémentation de la version

INSERT INTO adsys_version_schema(version,date_version) VALUES('1.1.0','2010-02-24');

-- Creation des objets liés aux ordres permanents
CREATE SEQUENCE "ad_ord_perm_id_seq" start 1 increment 1 maxvalue 214748647 minvalue 1 cache 1 ;
CREATE TABLE "ad_ord_perm"(
		"id_ord"   int4 DEFAULT nextval('ad_ord_perm_id_seq'::text) NOT NULL,
        "cpt_from" int NOT NULL,
         CONSTRAINT ad_ord_perm_cpt_from_fk FOREIGN KEY (cpt_from) REFERENCES ad_cpt (id_cpte) ON DELETE CASCADE,
        "cpt_to" int NULL,
        "type_transfert" int NOT NULL,
         CONSTRAINT ad_ord_perm_type_trans_ck CHECK (type_transfert IN (1,2,3)),
        "id_cor" int,
        "id_benef" int,
        "date_prem_exe" date NOT NULL,
        "date_proch_exe" date,
        "date_fin" date,
        "montant" numeric(30,6) NOT NULL,
         CONSTRAINT ad_ord_perm_mont_ck CHECK (montant > 0),
        "frais_transfert" numeric(30,6) not null default 0,
        "periodicite" int NOT NULL,
        "interv" int NOT NULL DEFAULT 1,
         CONSTRAINT ad_ord_perm_period_ck CHECK (periodicite IN (1,2,3,4)),
        "actif" bool default true,
        "communication" text,
        "date_dern_exe_th" date,
        "date_dern_exe_ef" date,
		"dern_statut" int,
		"id_ag" int4 NOT NULL,
         CONSTRAINT ad_ord_perm_pk PRIMARY KEY(id_ord, id_ag),
         CONSTRAINT ad_ord_perm_date_ck CHECK (date_prem_exe < date_fin OR date_fin IS NULL)
);

COMMENT ON TABLE "ad_ord_perm" IS 'Cette table stocke les ordres permanents';
COMMENT ON COLUMN "ad_ord_perm"."cpt_from" IS 'Compte source';
COMMENT ON COLUMN "ad_ord_perm"."cpt_to" IS 'Compte de destination pour un virement interne, null sinon';
COMMENT ON COLUMN "ad_ord_perm"."type_transfert" IS 'type de transfert : 1 (meme client), 2 (interne), 3 (externe)';
COMMENT ON COLUMN "ad_ord_perm"."id_cor" IS 'id du correspondant, pour type_transfert = 3 ';
COMMENT ON COLUMN "ad_ord_perm"."id_benef" IS 'id du beneficiaire, pour type_transfert = 3 ';
COMMENT ON COLUMN "ad_ord_perm"."date_prem_exe" IS 'Date de première execution';
COMMENT ON COLUMN "ad_ord_perm"."date_fin" IS 'Date de fin de validité';
COMMENT ON COLUMN "ad_ord_perm"."date_proch_exe" IS 'Date de dernière exécution, mise à jour par le batch';
COMMENT ON COLUMN "ad_ord_perm"."date_dern_exe_th" IS 'Date de dernière exécution théorique, mise à jour par le batch';
COMMENT ON COLUMN "ad_ord_perm"."date_dern_exe_ef" IS 'Date de dernière exécution réelle, si le batch est exécuté avec retard (jour férié),mise à jour par le batch';
COMMENT ON COLUMN "ad_ord_perm"."dern_statut" IS 'Statut de la dernière exécution 1=OK, 2=ERREUR';
COMMENT ON COLUMN "ad_ord_perm"."interv" IS 'Intervalle : tout les x mois ou semaine';
COMMENT ON COLUMN "ad_ord_perm"."actif" IS 'Permet de désactiver un ordre permanent sans le supprimer';
COMMENT ON COLUMN "ad_ord_perm"."periodicite" IS 'Periodicite de l''ordre : Jour, Sem., Mois ou Annee';

-- Script de mise à jour pour la consolidation
-- Mise à jour ad_agc
SELECT add_column_to_table ('ad_agc', 'passage_perte_automatique', 'BOOLEAN');
ALTER TABLE ad_agc ALTER COLUMN passage_perte_automatique set default 'true';
UPDATE ad_agc SET passage_perte_automatique='true';

ALTER TABLE ad_agc ADD COLUMN paiement_parts_soc_gs BOOLEAN;
ALTER TABLE ad_agc ALTER COLUMN paiement_parts_soc_gs set default 'false';
UPDATE ad_agc SET paiement_parts_soc_gs='false';
/*
  Fonction appelée après l'update d'un ordre permanent pour remplir le champ date_proch_exe
*/
CREATE OR REPLACE FUNCTION trig_update_ord_perm() RETURNS trigger AS '
  BEGIN
    IF (NEW.date_prem_exe != OLD.date_prem_exe AND NEW.date_prem_exe >= now()) THEN
      NEW.date_proch_exe = NEW.date_prem_exe;
    END IF;
    IF (NEW.date_dern_exe_th != OLD.date_dern_exe_th AND NEW.date_dern_exe_th >= NEW.date_proch_exe) THEN
      SELECT INTO NEW.date_proch_exe ordreperm_proch_exe(NEW.date_dern_exe_th, NEW.periodicite, NEW.interv);
    END IF;
    RETURN NEW;
  END;
' LANGUAGE plpgsql;
CREATE TRIGGER ord_perm_before_update BEFORE UPDATE ON ad_ord_perm FOR EACH ROW EXECUTE PROCEDURE trig_update_ord_perm();
CREATE TRIGGER ord_perm_before_insert BEFORE INSERT ON ad_ord_perm FOR EACH ROW EXECUTE PROCEDURE trig_insert_ord_perm();

-- Mise à jour de la table ad_agc

-- Ajout du champ  nbre_part_social_max_cli (nbre de part sociale maximum pour un client)
	ALTER TABLE ad_agc ADD COLUMN nbre_part_social_max_cli int4;
	ALTER TABLE ad_agc ALTER COLUMN nbre_part_social_max_cli SET DEFAULT 0;
	UPDATE ad_agc SET nbre_part_social_max_cli = 0 where nbre_part_social_max_cli is null;
	--Ajout  d'un champ nbre_car_min_pwd (nbre de caractere maximum du mot de passe)
	ALTER TABLE ad_agc ADD COLUMN nbre_car_min_pwd int4;
	ALTER TABLE ad_agc ALTER COLUMN nbre_car_min_pwd  set DEFAULT 0;
	--Ajout du champ duree_pwd durée de vie d'un mot de passe en jour
	ALTER TABLE ad_agc ADD COLUMN duree_pwd int4 ;
	ALTER TABLE ad_agc ALTER COLUMN duree_pwd  set DEFAULT 0;

-- Mise à jour de la table ad_log

--Ajout du champ date_mod_pwd ( date de la dernière modification du mot de passe)
	ALTER TABLE ad_log ADD COLUMN date_mod_pwd Date;
	--Ajout du champ pwd_non_expire ( boolean indique si le mot de passe ne peut pas expiré)
	ALTER TABLE ad_log ADD COLUMN pwd_non_expire boolean ;
	ALTER TABLE ad_log ALTER COLUMN pwd_non_expire  set DEFAULT false;
	--le mot de passe du login admin n'expire jamais
	UPDATE ad_log set pwd_non_expire='true' where login='admin';

-- Mise à jour de la table ad_cli

ALTER TABLE ad_cli ADD COLUMN id_ag int4;
UPDATE ad_cli SET id_ag = NumAgc();
ALTER TABLE ad_cli ALTER COLUMN id_ag SET NOT NULL;
ALTER TABLE ad_cli DROP CONSTRAINT ad_cli_pkey CASCADE;
ALTER TABLE ad_cli ADD PRIMARY KEY(id_client,id_ag);

-- Mise à jour de la table ad_grp_sol

ALTER TABLE ad_grp_sol ADD COLUMN id_ag int4;
UPDATE ad_grp_sol SET id_ag = NumAgc();
ALTER TABLE ad_grp_sol ALTER COLUMN id_ag SET NOT NULL;
ALTER TABLE ad_grp_sol ADD PRIMARY KEY(id_grp_sol,id_membre,id_ag);


-- Mise à jour de la table ad_cli

ALTER TABLE adsys_pays ADD COLUMN id_ag int4;
UPDATE adsys_pays SET id_ag = NumAgc();
ALTER TABLE adsys_pays ALTER COLUMN id_ag SET NOT NULL;
ALTER TABLE adsys_pays DROP CONSTRAINT adsys_pays_pkey CASCADE;
ALTER TABLE adsys_pays ADD PRIMARY KEY(id_pays,id_ag);
ALTER TABLE adsys_pays DROP CONSTRAINT adsys_pays_code_pays_key;
ALTER TABLE adsys_pays ADD CONSTRAINT adsys_pays_code_pays_key UNIQUE(code_pays,id_ag);

-- Mise à jour table dossier de crédit ad_dcr

ALTER TABLE ad_dcr ADD COLUMN id_ag int4;
UPDATE ad_dcr SET id_ag = NumAgc();
ALTER TABLE ad_dcr ALTER COLUMN id_ag SET NOT NULL;
ALTER TABLE ad_dcr DROP CONSTRAINT ad_dcr_pkey CASCADE;
ALTER TABLE ad_dcr ADD PRIMARY KEY(id_doss,id_ag);
ALTER TABLE ad_dcr ADD COLUMN cre_prelev_frais_doss boolean;
UPDATE ad_dcr SET cre_prelev_frais_doss='t' where cre_prelev_frais_doss is null ;

-- Mise à jour ad_etr

ALTER TABLE ad_etr ADD COLUMN id_ag int4;
UPDATE ad_etr SET id_ag = NumAgc();
ALTER TABLE ad_etr ALTER COLUMN id_ag SET NOT NULL;
ALTER TABLE ad_etr DROP CONSTRAINT ad_etr_pkey CASCADE;
ALTER TABLE ad_etr ADD PRIMARY KEY(id_doss,id_ech,id_ag);

-- Mise à jour ad_sre

ALTER TABLE ad_sre ADD COLUMN id_ag int4;
UPDATE ad_sre SET id_ag = NumAgc();
ALTER TABLE ad_sre ALTER COLUMN id_ag SET NOT NULL;
ALTER TABLE ad_sre DROP CONSTRAINT ad_sre_pkey CASCADE;
ALTER TABLE ad_sre ADD PRIMARY KEY(id_doss,num_remb,id_ech,id_ag);

-- Mise à jour ad_log --
ALTER TABLE ad_log ADD COLUMN id_ag int4;
UPDATE ad_log SET id_ag = NumAgc();
ALTER TABLE ad_log ALTER COLUMN id_ag SET NOT NULL;
ALTER TABLE ad_log DROP CONSTRAINT ad_log_pkey CASCADE;
ALTER TABLE ad_log ADD PRIMARY KEY(login,id_ag);

-- Mise à jour ad_uti --
ALTER TABLE ad_uti ADD COLUMN id_ag int4;
UPDATE ad_uti SET id_ag = NumAgc();
ALTER TABLE ad_uti ALTER COLUMN id_ag SET NOT NULL;
ALTER TABLE ad_uti DROP CONSTRAINT ad_uti_pkey CASCADE;
ALTER TABLE ad_uti ADD PRIMARY KEY(id_utilis,id_ag);

-- Mise à jour ad_cpt

ALTER TABLE ad_cpt ADD COLUMN id_ag int4;
ALTER TABLE ad_cpt ADD COLUMN raison_blocage text;
ALTER TABLE ad_cpt ADD COLUMN date_blocage timestamp without time zone;
ALTER TABLE ad_cpt ADD COLUMN utilis_bloquant text;
UPDATE ad_cpt SET id_ag = NumAgc();
ALTER TABLE ad_cpt ALTER COLUMN id_ag SET NOT NULL;
ALTER TABLE ad_cpt DROP CONSTRAINT ad_cpt_pkey CASCADE;
ALTER TABLE ad_cpt ADD PRIMARY KEY(id_cpte,id_ag);
ALTER TABLE ad_cpt DROP CONSTRAINT ad_cpt_num_complet_cpte_key;
ALTER TABLE ad_cpt ADD CONSTRAINT ad_cpt_num_complet_cpte_key UNIQUE(num_complet_cpte,id_ag);
-- Corrections de la 2.8.6
-- Place les soldes des comptes de parts sociales comme étant des montants bloqués (#1202)
UPDATE ad_cpt SET mnt_bloq = solde WHERE id_prod = 2;


-- Mise à jour ad_cpt_comptable

ALTER TABLE ad_cpt_comptable ADD COLUMN id_ag int4;
UPDATE ad_cpt_comptable SET id_ag = NumAgc();
ALTER TABLE ad_cpt_comptable ALTER COLUMN id_ag SET NOT NULL;
ALTER TABLE ad_cpt_comptable DROP CONSTRAINT ad_cpt_comptable_pkey CASCADE;
ALTER TABLE ad_cpt_comptable ADD PRIMARY KEY(num_cpte_comptable,id_ag);

-- Mise à jour ad_cpt_soldes

ALTER TABLE ad_cpt_soldes ADD COLUMN id_ag int4;
UPDATE ad_cpt_soldes SET id_ag = NumAgc();
ALTER TABLE ad_cpt_soldes ALTER COLUMN id_ag SET NOT NULL;
ALTER TABLE ad_cpt_soldes DROP CONSTRAINT ad_cpt_soldes_pkey CASCADE;
ALTER TABLE ad_cpt_soldes ADD PRIMARY KEY(num_cpte_comptable_solde,id_ag);

-- Mise à jour ad_mouvement

ALTER TABLE ad_mouvement ADD COLUMN id_ag int4;
UPDATE ad_mouvement SET id_ag = NumAgc();
ALTER TABLE ad_mouvement ALTER COLUMN id_ag SET NOT NULL;
ALTER TABLE ad_mouvement DROP CONSTRAINT ad_mouvement_pkey CASCADE;
ALTER TABLE ad_mouvement ADD PRIMARY KEY(id_mouvement,id_ag);

-- Mise à jour ad_pers_ext

ALTER TABLE ad_pers_ext ADD COLUMN id_ag int4;
UPDATE ad_pers_ext SET id_ag = NumAgc();
ALTER TABLE ad_pers_ext ALTER COLUMN id_ag SET NOT NULL;
ALTER TABLE ad_pers_ext DROP CONSTRAINT ad_pers_ext_pkey CASCADE;
ALTER TABLE ad_pers_ext ADD PRIMARY KEY(id_pers_ext,id_ag);

-- Mise à jour ad_rel

ALTER TABLE ad_rel ADD COLUMN id_ag int4;
UPDATE ad_rel SET id_ag = NumAgc();
ALTER TABLE ad_rel ALTER COLUMN id_ag SET NOT NULL;
ALTER TABLE ad_rel DROP CONSTRAINT ad_rel_pkey CASCADE;
ALTER TABLE ad_rel ADD PRIMARY KEY(id_rel,id_ag);

-- Mise à jour ad_exercies_compta

ALTER TABLE ad_exercices_compta ADD COLUMN id_ag int4;
UPDATE ad_exercices_compta SET id_ag = NumAgc();
ALTER TABLE ad_exercices_compta ALTER COLUMN id_ag SET NOT NULL;
ALTER TABLE ad_exercices_compta DROP CONSTRAINT ad_exercices_compta_pkey CASCADE;
ALTER TABLE ad_exercices_compta ADD PRIMARY KEY(id_exo_compta,id_ag);

-- Mise à jour ad_clotures_periode

ALTER TABLE ad_clotures_periode ADD COLUMN id_ag int4;
UPDATE ad_clotures_periode SET id_ag = NumAgc();
ALTER TABLE ad_clotures_periode ALTER COLUMN id_ag SET NOT NULL;
ALTER TABLE ad_clotures_periode DROP CONSTRAINT ad_clotures_periode_pkey CASCADE;
ALTER TABLE ad_clotures_periode ADD PRIMARY KEY(id_clot_per,id_ag);

-- Mise à jour adsys_produit_epargne

ALTER TABLE adsys_produit_epargne ADD COLUMN id_ag int4;
ALTER TABLE adsys_produit_epargne ADD COLUMN mode_calcul_penal_rupt int4;
UPDATE adsys_produit_epargne SET id_ag = NumAgc();
ALTER TABLE adsys_produit_epargne ALTER COLUMN id_ag SET NOT NULL;
ALTER TABLE adsys_produit_epargne DROP CONSTRAINT adsys_produit_epargne_pkey CASCADE;
ALTER TABLE adsys_produit_epargne ADD PRIMARY KEY(id,id_ag);
UPDATE adsys_produit_epargne SET mode_calcul_penal_rupt=1 WHERE classe_comptable = 2 OR classe_comptable = 5;

-- Mise à jour adsys_banque

ALTER TABLE adsys_banque ADD COLUMN id_ag int4;
UPDATE adsys_banque SET id_ag = NumAgc();
ALTER TABLE adsys_banque ALTER COLUMN id_ag SET NOT NULL;
ALTER TABLE adsys_banque DROP CONSTRAINT adsys_banque_pkey CASCADE;
ALTER TABLE adsys_banque ADD PRIMARY KEY(id_banque,id_ag);

-- Mise à jour tireur_benef

ALTER TABLE tireur_benef ADD COLUMN id_ag int4;
UPDATE tireur_benef SET id_ag = NumAgc();
ALTER TABLE tireur_benef ALTER COLUMN id_ag SET NOT NULL;
ALTER TABLE tireur_benef DROP CONSTRAINT tireur_benef_pkey CASCADE;
ALTER TABLE tireur_benef ADD PRIMARY KEY(id,id_ag);

-- Mise à jour ad_his_ext

ALTER TABLE ad_his_ext ADD COLUMN id_ag int4;
UPDATE ad_his_ext SET id_ag = NumAgc();
ALTER TABLE ad_his_ext ALTER COLUMN id_ag SET NOT NULL;
ALTER TABLE ad_his_ext DROP CONSTRAINT ad_his_ext_pkey CASCADE;
ALTER TABLE ad_his_ext ADD PRIMARY KEY(id,id_ag);

-- Mise à jour ad_his

ALTER TABLE ad_his ADD COLUMN id_ag int4;
UPDATE ad_his SET id_ag = NumAgc();
ALTER TABLE ad_his ALTER COLUMN id_ag SET NOT NULL;
ALTER TABLE ad_his DROP CONSTRAINT ad_his_pkey CASCADE;
ALTER TABLE ad_his ADD PRIMARY KEY(id_his,id_ag);

-- Mise à jour ad_extrait_cpte

ALTER TABLE ad_extrait_cpte ADD COLUMN id_ag int4;
UPDATE ad_extrait_cpte SET id_ag = NumAgc();
ALTER TABLE ad_extrait_cpte ALTER COLUMN id_ag SET NOT NULL;
ALTER TABLE ad_extrait_cpte DROP CONSTRAINT ad_extrait_cpte_pkey CASCADE;
ALTER TABLE ad_extrait_cpte ADD PRIMARY KEY(id_extrait_cpte,id_ag);

-- Mise à jour ad_mandat

ALTER TABLE ad_mandat ADD COLUMN id_ag int4;
UPDATE ad_mandat SET id_ag = NumAgc();
ALTER TABLE ad_mandat ALTER COLUMN id_ag SET NOT NULL;
ALTER TABLE ad_mandat DROP CONSTRAINT ad_mandat_pkey CASCADE;
ALTER TABLE ad_mandat ADD PRIMARY KEY(id_mandat,id_ag);

-- Mise à jour adsys_produit_credit

ALTER TABLE adsys_produit_credit ADD COLUMN id_ag int4;
UPDATE adsys_produit_credit SET id_ag = NumAgc();
ALTER TABLE adsys_produit_credit ALTER COLUMN id_ag SET NOT NULL;
ALTER TABLE adsys_produit_credit DROP CONSTRAINT adsys_produit_credit_pkey CASCADE;
ALTER TABLE adsys_produit_credit ADD PRIMARY KEY(id,id_ag);
ALTER TABLE adsys_produit_credit ADD COLUMN prelev_frais_doss smallint;
ALTER TABLE adsys_produit_credit ALTER COLUMN prelev_frais_doss set default '1';
UPDATE adsys_produit_credit SET prelev_frais_doss='1' where prelev_frais_doss is null;

-- Mise à jour adsys_etat_credits

ALTER TABLE adsys_etat_credits ADD COLUMN id_ag int4;
UPDATE adsys_etat_credits SET id_ag = NumAgc();
ALTER TABLE adsys_etat_credits ALTER COLUMN id_ag SET NOT NULL;
ALTER TABLE adsys_etat_credits DROP CONSTRAINT adsys_etat_credits_pkey CASCADE;
ALTER TABLE adsys_etat_credits ADD PRIMARY KEY(id,id_ag);
ALTER TABLE adsys_etat_credits DROP CONSTRAINT adsys_etat_credits_id_etat_prec_key;
ALTER TABLE adsys_etat_credits ADD CONSTRAINT adsys_etat_credits_id_etat_prec_key UNIQUE(id_etat_prec,id_ag);
ALTER TABLE adsys_produit_credit ADD COLUMN percep_frais_com_ass smallint;
ALTER TABLE adsys_produit_credit ALTER COLUMN percep_frais_com_ass set default '1';
UPDATE adsys_produit_credit SET percep_frais_com_ass='1';


-- Mise à jour ad_dcr_grp_sol

ALTER TABLE ad_dcr_grp_sol ADD COLUMN id_ag int4;
UPDATE ad_dcr_grp_sol SET id_ag = NumAgc();
ALTER TABLE ad_dcr_grp_sol ALTER COLUMN id_ag SET NOT NULL;
ALTER TABLE ad_dcr_grp_sol DROP CONSTRAINT ad_dcr_grp_sol_pkey CASCADE;
ALTER TABLE ad_dcr_grp_sol ADD PRIMARY KEY(id,id_ag);

-- Mise à jour ad_classes_compta

ALTER TABLE ad_classes_compta ADD COLUMN id_ag int4;
UPDATE ad_classes_compta SET id_ag = NumAgc();
ALTER TABLE ad_classes_compta ALTER COLUMN id_ag SET NOT NULL;
ALTER TABLE ad_classes_compta DROP CONSTRAINT ad_classes_compta_pkey CASCADE;
ALTER TABLE ad_classes_compta ADD PRIMARY KEY(id_classe,id_ag);
ALTER TABLE ad_classes_compta DROP CONSTRAINT ad_classes_compta_numero_classe_key CASCADE;
ALTER TABLE ad_classes_compta ADD CONSTRAINT ad_classes_compta_numero_classe_key UNIQUE(numero_classe,id_ag);

-- Mise à jour ad_journaux

ALTER TABLE ad_journaux ADD COLUMN id_ag int4;
UPDATE ad_journaux SET id_ag = NumAgc();
ALTER TABLE ad_journaux ALTER COLUMN id_ag SET NOT NULL;
ALTER TABLE ad_journaux DROP CONSTRAINT ad_journaux_pkey CASCADE;
ALTER TABLE ad_journaux ADD PRIMARY KEY(id_jou,id_ag);
ALTER TABLE ad_journaux DROP CONSTRAINT ad_journaux_code_jou_key CASCADE;
ALTER TABLE ad_journaux ADD CONSTRAINT ad_journaux_code_jou_key UNIQUE(code_jou,id_ag);

-- Mise à jour ad_journaux_liaison

ALTER TABLE ad_journaux_liaison ADD COLUMN id_ag int4;
UPDATE ad_journaux_liaison SET id_ag = NumAgc();
ALTER TABLE ad_journaux_liaison ALTER COLUMN id_ag SET NOT NULL;
ALTER TABLE ad_journaux_liaison DROP CONSTRAINT ad_journaux_liaison_pkey CASCADE;
ALTER TABLE ad_journaux_liaison ADD PRIMARY KEY(id_jou1,id_jou2,id_ag);

-- Mise à jour ad_journaux_cptie

ALTER TABLE ad_journaux_cptie ADD COLUMN id_ag int4;
UPDATE ad_journaux_cptie SET id_ag = NumAgc();
ALTER TABLE ad_journaux_cptie ALTER COLUMN id_ag SET NOT NULL;
ALTER TABLE ad_journaux_cptie DROP CONSTRAINT ad_journaux_cptie_pkey CASCADE;
ALTER TABLE ad_journaux_cptie ADD PRIMARY KEY(id_jou,num_cpte_comptable,id_ag);



-- Mise à jour devise

ALTER TABLE devise ADD COLUMN id_ag int4;
UPDATE devise SET id_ag = NumAgc();
ALTER TABLE devise ALTER COLUMN id_ag SET NOT NULL;
ALTER TABLE devise DROP CONSTRAINT devise_pkey CASCADE;
ALTER TABLE devise ADD PRIMARY KEY(code_devise,id_ag);

-- Mise à jour ad_gui

ALTER TABLE ad_gui ADD COLUMN id_ag int4;
UPDATE ad_gui SET id_ag = NumAgc();
ALTER TABLE ad_gui ALTER COLUMN id_ag SET NOT NULL;
ALTER TABLE ad_gui DROP CONSTRAINT ad_gui_pkey CASCADE;
ALTER TABLE ad_gui ADD PRIMARY KEY(id_gui,id_ag);
ALTER TABLE ad_gui DROP CONSTRAINT ad_gui_cpte_cpta_gui_key;
ALTER TABLE ad_gui ADD CONSTRAINT ad_gui_cpte_cpta_gui_key UNIQUE(cpte_cpta_gui,id_ag);

-- Mise à jour ad_fer

ALTER TABLE ad_fer ADD COLUMN id_ag int4;
UPDATE ad_fer SET id_ag = NumAgc();
ALTER TABLE ad_fer ALTER COLUMN id_ag SET NOT NULL;
ALTER TABLE ad_fer DROP CONSTRAINT ad_fer_pkey CASCADE;
ALTER TABLE ad_fer ADD PRIMARY KEY(id_fer,id_ag);

-- Mise à jour adsys_rejet_pret

ALTER TABLE adsys_rejet_pret ADD COLUMN id_ag int4;
UPDATE adsys_rejet_pret SET id_ag = NumAgc();
ALTER TABLE adsys_rejet_pret ALTER COLUMN id_ag SET NOT NULL;
ALTER TABLE adsys_rejet_pret DROP CONSTRAINT adsys_rejet_pret_pkey CASCADE;
ALTER TABLE adsys_rejet_pret ADD PRIMARY KEY(id,id_ag);

-- Mise à jour adsys_localisation

ALTER TABLE adsys_localisation ADD COLUMN id_ag int4;
UPDATE adsys_localisation SET id_ag = NumAgc();
ALTER TABLE adsys_localisation ALTER COLUMN id_ag SET NOT NULL;
ALTER TABLE adsys_localisation DROP CONSTRAINT adsys_localistion_pkey CASCADE;
ALTER TABLE adsys_localisation ADD PRIMARY KEY(id,id_ag);

-- Mise à jour adsys_sect_activite

ALTER TABLE adsys_sect_activite ADD COLUMN id_ag int4;
UPDATE adsys_sect_activite SET id_ag = NumAgc();
ALTER TABLE adsys_sect_activite ALTER COLUMN id_ag SET NOT NULL;
ALTER TABLE adsys_sect_activite DROP CONSTRAINT adsys_sect_activite_pkey CASCADE;
ALTER TABLE adsys_sect_activite ADD PRIMARY KEY(id,id_ag);

-- Mise à jour adsys_langue

ALTER TABLE adsys_langue ADD COLUMN id_ag int4;
UPDATE adsys_langue SET id_ag = NumAgc();
ALTER TABLE adsys_langue ALTER COLUMN id_ag SET NOT NULL;
ALTER TABLE adsys_langue DROP CONSTRAINT adsys_langue_pkey CASCADE;
ALTER TABLE adsys_langue ADD PRIMARY KEY(id,id_ag);


-- Mise à jour adsys_types_billets

ALTER TABLE adsys_types_billets ADD COLUMN id_ag int4;
UPDATE adsys_types_billets SET id_ag = NumAgc();
ALTER TABLE adsys_types_billets ALTER COLUMN id_ag SET NOT NULL;
ALTER TABLE adsys_types_billets DROP CONSTRAINT adsys_types_billets_pkey CASCADE;
ALTER TABLE adsys_types_billets ADD PRIMARY KEY(id,id_ag);

-- Mise à jour adsys_type_piece_identite

ALTER TABLE adsys_type_piece_identite ADD COLUMN id_ag int4;
UPDATE adsys_type_piece_identite SET id_ag = NumAgc();
ALTER TABLE adsys_type_piece_identite ALTER COLUMN id_ag SET NOT NULL;
ALTER TABLE adsys_type_piece_identite DROP CONSTRAINT adsys_type_piece_identite_pkey CASCADE;
ALTER TABLE adsys_type_piece_identite ADD PRIMARY KEY(id,id_ag);

-- Mise à jour adsys_asso_produitcredit_statjuri
-- Il faut d'abord supprimer les entrées dupliquées s'il y en a
CREATE TEMP TABLE tmp_apcsj AS SELECT DISTINCT * from adsys_asso_produitcredit_statjuri;
DELETE FROM adsys_asso_produitcredit_statjuri;
INSERT INTO adsys_asso_produitcredit_statjuri SELECT * from tmp_apcsj;
ALTER TABLE adsys_asso_produitcredit_statjuri ADD COLUMN id_ag int4;
UPDATE adsys_asso_produitcredit_statjuri SET id_ag = NumAgc();
ALTER TABLE adsys_asso_produitcredit_statjuri ALTER COLUMN id_ag SET NOT NULL;
ALTER TABLE adsys_asso_produitcredit_statjuri ADD PRIMARY KEY(id_pc,ident_sj,id_ag);

-- Mise à jour ad_rapports

ALTER TABLE ad_rapports ADD COLUMN id_ag int4;
UPDATE ad_rapports SET id_ag = NumAgc();
ALTER TABLE ad_rapports ALTER COLUMN id_ag SET NOT NULL;
ALTER TABLE ad_rapports DROP CONSTRAINT ad_rapports_pkey CASCADE;
ALTER TABLE ad_rapports ADD PRIMARY KEY(id,id_ag);

-- Mise à jour adsys_objets_credits

ALTER TABLE adsys_objets_credits ADD COLUMN id_ag int4;
UPDATE adsys_objets_credits SET id_ag = NumAgc();
ALTER TABLE adsys_objets_credits ALTER COLUMN id_ag SET NOT NULL;
ALTER TABLE adsys_objets_credits DROP CONSTRAINT adsys_objets_credits_pkey CASCADE;
ALTER TABLE adsys_objets_credits ADD PRIMARY KEY(id,id_ag);

-- Mise à jour ad_cpt_ope

ALTER TABLE ad_cpt_ope ADD COLUMN id_ag int4;
UPDATE ad_cpt_ope SET id_ag = NumAgc();
ALTER TABLE ad_cpt_ope ALTER COLUMN id_ag SET NOT NULL;
ALTER TABLE ad_cpt_ope DROP CONSTRAINT ad_cpt_ope_pkey CASCADE;
ALTER TABLE ad_cpt_ope ADD PRIMARY KEY(type_operation,id_ag);

-- Mise à jour ad_cpt_ope_cptes

ALTER TABLE ad_cpt_ope_cptes ADD COLUMN id_ag int4;
UPDATE ad_cpt_ope_cptes SET id_ag = NumAgc();
ALTER TABLE ad_cpt_ope_cptes ALTER COLUMN id_ag SET NOT NULL;
ALTER TABLE ad_cpt_ope_cptes DROP CONSTRAINT ad_cpt_ope_cptes_pkey CASCADE;
ALTER TABLE ad_cpt_ope_cptes ADD PRIMARY KEY(type_operation,categorie_cpte,sens,id_ag);


-- Mise à jour ad_brouillard

ALTER TABLE ad_brouillard ADD COLUMN id_ag int4;
UPDATE ad_brouillard SET id_ag = NumAgc();
ALTER TABLE ad_brouillard ALTER COLUMN id_ag SET NOT NULL;
ALTER TABLE ad_brouillard DROP CONSTRAINT ad_brouillard_pkey CASCADE;
ALTER TABLE ad_brouillard ADD PRIMARY KEY(id_mouvement,id_ag);
ALTER TABLE ad_brouillard DROP CONSTRAINT ad_brouillard_id_his_key;
ALTER TABLE ad_brouillard ADD CONSTRAINT ad_brouillard_id_his_key UNIQUE(id_his,compte,id,sens,id_ag);

-- Mise à jour adsys_correspondant

ALTER TABLE adsys_correspondant ADD COLUMN id_ag int4;
UPDATE adsys_correspondant SET id_ag = NumAgc();
ALTER TABLE adsys_correspondant ALTER COLUMN id_ag SET NOT NULL;
ALTER TABLE adsys_correspondant DROP CONSTRAINT adsys_correspondant_pkey CASCADE;
ALTER TABLE adsys_correspondant ADD PRIMARY KEY(id,id_ag);

-- Mise à jour ad_ecriture

ALTER TABLE ad_ecriture ADD COLUMN id_ag int4;
UPDATE ad_ecriture SET id_ag = NumAgc();
ALTER TABLE ad_ecriture ALTER COLUMN id_ag SET NOT NULL;
ALTER TABLE ad_ecriture DROP CONSTRAINT ad_ecriture_pkey CASCADE;
ALTER TABLE ad_ecriture ADD PRIMARY KEY(id_ecriture,id_ag);
ALTER TABLE ad_ecriture DROP CONSTRAINT ad_ecriture_ref_ecriture_key;
ALTER TABLE ad_ecriture ADD CONSTRAINT ad_ecriture_ref_ecriture_key UNIQUE(ref_ecriture,id_ag);

-- Mise à jour adsys_etat_credit_cptes

ALTER TABLE adsys_etat_credit_cptes ADD COLUMN id_ag int4;
UPDATE adsys_etat_credit_cptes SET id_ag = NumAgc();
ALTER TABLE adsys_etat_credit_cptes ALTER COLUMN id_ag SET NOT NULL;
ALTER TABLE adsys_etat_credit_cptes DROP CONSTRAINT adsys_etat_credit_cptes_pkey CASCADE;
ALTER TABLE adsys_etat_credit_cptes ADD PRIMARY KEY(id_etat_credit, num_cpte_comptable, id_prod_cre,id_ag);

-- Mise à jour attentes

ALTER TABLE attentes ADD COLUMN id_ag int4;
UPDATE attentes SET id_ag = NumAgc();
ALTER TABLE attentes ALTER COLUMN id_ag SET NOT NULL;
ALTER TABLE attentes DROP CONSTRAINT attentes_pkey CASCADE;
ALTER TABLE attentes ADD PRIMARY KEY(id,id_ag);

-- Mise à jour swift_op_domestiques

ALTER TABLE swift_op_domestiques ADD COLUMN id_ag int4;
UPDATE swift_op_domestiques SET id_ag = NumAgc();
ALTER TABLE swift_op_domestiques ALTER COLUMN id_ag SET NOT NULL;
ALTER TABLE swift_op_domestiques DROP CONSTRAINT swift_op_domestiques_pkey CASCADE;
ALTER TABLE swift_op_domestiques ADD PRIMARY KEY(id_message,id_ag);

-- Mise à jour swift_op_etrangers

ALTER TABLE swift_op_etrangers ADD COLUMN id_ag int4;
UPDATE swift_op_etrangers SET id_ag = NumAgc();
ALTER TABLE swift_op_etrangers ALTER COLUMN id_ag SET NOT NULL;
ALTER TABLE swift_op_etrangers DROP CONSTRAINT swift_op_etrangers_pkey CASCADE;
ALTER TABLE swift_op_etrangers ADD PRIMARY KEY(id_message,id_ag);

-- Mise à jour adsys_types_biens

ALTER TABLE adsys_types_biens ADD COLUMN id_ag int4;
UPDATE adsys_types_biens SET id_ag = NumAgc();
ALTER TABLE adsys_types_biens ALTER COLUMN id_ag SET NOT NULL;
ALTER TABLE adsys_types_biens DROP CONSTRAINT adsys_types_biens_pkey CASCADE;
ALTER TABLE adsys_types_biens ADD PRIMARY KEY(id,id_ag);

-- Mise à jour ad_biens

ALTER TABLE ad_biens ADD COLUMN id_ag int4;
UPDATE ad_biens SET id_ag = NumAgc();
ALTER TABLE ad_biens ALTER COLUMN id_ag SET NOT NULL;
ALTER TABLE ad_biens DROP CONSTRAINT ad_biens_pkey CASCADE;
ALTER TABLE ad_biens ADD PRIMARY KEY(id_bien,id_ag);

-- Mise à jour ad_gar

ALTER TABLE ad_gar ADD COLUMN id_ag int4;
UPDATE ad_gar SET id_ag = NumAgc();
ALTER TABLE ad_gar ALTER COLUMN id_ag SET NOT NULL;
ALTER TABLE ad_gar DROP CONSTRAINT ad_gar_pkey CASCADE;
ALTER TABLE ad_gar ADD PRIMARY KEY(id_gar,id_ag);

-- Mise à jour ad_frais_attente

ALTER TABLE ad_frais_attente ADD COLUMN id_ag int4;
UPDATE ad_frais_attente SET id_ag = NumAgc();
ALTER TABLE ad_frais_attente ALTER COLUMN id_ag SET NOT NULL;
ALTER TABLE ad_frais_attente DROP CONSTRAINT ad_frais_attente_pkey CASCADE;
ALTER TABLE ad_frais_attente ADD PRIMARY KEY(id_cpte, date_frais, type_frais,id_ag);

-- Ajout opérations entre siège et agences
INSERT INTO ad_cpt_ope VALUES ( 600, 'Dépôt au siège', 1, NumAgc() );
INSERT INTO ad_cpt_ope VALUES ( 601, 'Dépôt agence', 1, NumAgc() );
INSERT INTO ad_cpt_ope VALUES ( 602, 'Emprunt auprès du siège', 1, NumAgc() );
INSERT INTO ad_cpt_ope VALUES ( 603, 'Prêt à une agence', 1, NumAgc() );
INSERT INTO ad_cpt_ope VALUES ( 604, 'Titres de participation', 1, NumAgc() );
INSERT INTO ad_cpt_ope VALUES ( 605, 'Parts sociales agence', 1, NumAgc() );
INSERT INTO ad_cpt_ope VALUES ( 606, 'Participation aux charges du réseau', 1, NumAgc() );
INSERT INTO ad_cpt_ope VALUES ( 607, 'Refacturation aux agences', 1, NumAgc() );
INSERT INTO ad_cpt_ope VALUES ( 608, 'Retrait au siège', 1, NumAgc() );
INSERT INTO ad_cpt_ope VALUES ( 609, 'Retrait des agences', 1, NumAgc() );
INSERT INTO ad_cpt_ope VALUES ( 610, 'Remboursement crédit du siège', 1, NumAgc() );
INSERT INTO ad_cpt_ope VALUES ( 611, 'Remboursement crédit aux agences', 1, NumAgc() );
INSERT INTO ad_cpt_ope VALUES ( 612, 'Récupération parts sociales', 1, NumAgc() );
INSERT INTO ad_cpt_ope VALUES ( 613, 'Remboursement parts sociales', 1, NumAgc() );

-- Paramétrage des comptes au débit et au crédit des opérations
INSERT INTO ad_cpt_ope_cptes VALUES ( 600, NULL, 'd', 0, NumAgc() );
INSERT INTO ad_cpt_ope_cptes VALUES ( 600, NULL, 'c', 0, NumAgc() );
INSERT INTO ad_cpt_ope_cptes VALUES ( 601, NULL, 'd', 0, NumAgc() );
INSERT INTO ad_cpt_ope_cptes VALUES ( 601, NULL, 'c', 0, NumAgc() );
INSERT INTO ad_cpt_ope_cptes VALUES ( 602, NULL, 'd', 0, NumAgc() );
INSERT INTO ad_cpt_ope_cptes VALUES ( 602, NULL, 'c', 0, NumAgc() );
INSERT INTO ad_cpt_ope_cptes VALUES ( 603, NULL, 'd', 0, NumAgc() );
INSERT INTO ad_cpt_ope_cptes VALUES ( 603, NULL, 'c', 0, NumAgc() );
INSERT INTO ad_cpt_ope_cptes VALUES ( 604, NULL, 'd', 0, NumAgc() );
INSERT INTO ad_cpt_ope_cptes VALUES ( 604, NULL, 'c', 0, NumAgc() );
INSERT INTO ad_cpt_ope_cptes VALUES ( 605, NULL, 'd', 0, NumAgc() );
INSERT INTO ad_cpt_ope_cptes VALUES ( 605, NULL, 'c', 0, NumAgc() );
INSERT INTO ad_cpt_ope_cptes VALUES ( 606, NULL, 'd', 0, NumAgc() );
INSERT INTO ad_cpt_ope_cptes VALUES ( 606, NULL, 'c', 0, NumAgc() );
INSERT INTO ad_cpt_ope_cptes VALUES ( 607, NULL, 'd', 0, NumAgc() );
INSERT INTO ad_cpt_ope_cptes VALUES ( 607, NULL, 'c', 0, NumAgc() );
INSERT INTO ad_cpt_ope_cptes VALUES ( 608, NULL, 'd', 0, NumAgc() );
INSERT INTO ad_cpt_ope_cptes VALUES ( 608, NULL, 'c', 0, NumAgc() );
INSERT INTO ad_cpt_ope_cptes VALUES ( 609, NULL, 'd', 0, NumAgc() );
INSERT INTO ad_cpt_ope_cptes VALUES ( 609, NULL, 'c', 0, NumAgc() );
INSERT INTO ad_cpt_ope_cptes VALUES ( 610, NULL, 'd', 0, NumAgc() );
INSERT INTO ad_cpt_ope_cptes VALUES ( 610, NULL, 'c', 0, NumAgc() );
INSERT INTO ad_cpt_ope_cptes VALUES ( 611, NULL, 'd', 0, NumAgc() );
INSERT INTO ad_cpt_ope_cptes VALUES ( 611, NULL, 'c', 0, NumAgc() );
INSERT INTO ad_cpt_ope_cptes VALUES ( 612, NULL, 'd', 0, NumAgc() );
INSERT INTO ad_cpt_ope_cptes VALUES ( 612, NULL, 'c', 0, NumAgc() );
INSERT INTO ad_cpt_ope_cptes VALUES ( 613, NULL, 'd', 0, NumAgc() );
INSERT INTO ad_cpt_ope_cptes VALUES ( 613, NULL, 'c', 0, NumAgc() );


-- ajout champ dans ad_mouvement pour identifier les mouvements déjà annulés
ALTER TABLE ad_mouvement ADD COLUMN consolide boolean;
ALTER TABLE ad_mouvement ALTER consolide SET DEFAULT false;
UPDATE ad_mouvement set consolide='false';
COMMENT ON COLUMN "ad_mouvement"."consolide" IS 'Identificateur des mouvements réciproques déjà annulés à la consolidation';

ALTER TABLE ad_agc ADD COLUMN siege BOOLEAN;
ALTER TABLE ad_agc ALTER COLUMN siege SET DEFAULT 'false';

-- Ajout d'un champ tranche frais adhesion dans ad_agc
ALTER TABLE ad_agc ADD COLUMN tranche_frais_adhesion boolean;
ALTER TABLE ad_agc ALTER COLUMN tranche_frais_adhesion SET DEFAULT 'false';
UPDATE ad_agc SET tranche_frais_adhesion = 'false';

-- Ajout d'un champ is_actif dans ad_cpt_comptable
ALTER TABLE ad_cpt_comptable ADD COLUMN is_actif boolean;
ALTER TABLE ad_cpt_comptable ALTER COLUMN is_actif SET DEFAULT 'true';
UPDATE ad_cpt_comptable SET is_actif = 'true';

-- Ajout d'un champ date_modif dans ad_cpt_comptable
ALTER TABLE ad_cpt_comptable ADD COLUMN date_modif timestamp;
ALTER TABLE ad_cpt_comptable ALTER COLUMN date_modif SET DEFAULT NULL;

-- Modification du champ tranche part sociale dans ad_agc (déjà ajouté en 2.8)
ALTER TABLE ad_agc ALTER COLUMN tranche_part_sociale SET DEFAULT 'false';
UPDATE ad_agc SET tranche_part_sociale = 'false';

-- Ajout d'un champ is_hors_bilan dans ad_cpt_comptable
ALTER TABLE ad_cpt_comptable ADD COLUMN is_hors_bilan boolean;
ALTER TABLE ad_cpt_comptable ALTER COLUMN is_hors_bilan SET DEFAULT 'false';
UPDATE ad_cpt_comptable SET is_hors_bilan = 'false';

--Ajout d'un champ dans ad_cli pour paiement par tranche frais adhésion
ALTER TABLE ad_cli ADD COLUMN solde_frais_adhesion_restant numeric(30,6) ;
ALTER TABLE ad_cli ALTER COLUMN solde_frais_adhesion_restant SET DEFAULT 0 ;

-- Modification de la BD pour module Ferlo
  -- Ajout du champs numéro de séquence autorisation  pour l''agence
  ALTER TABLE ad_agc ADD COLUMN num_seq_auto bigint;
  UPDATE ad_agc SET num_seq_auto = 0;

  -- Ajout opération caisse/compte : Recharge Carte Ferlo par compte Epargne
  INSERT INTO ad_cpt_ope VALUES ( 141, 'Recharge Carte Ferlo par compte Epargne', 1, NumAgc() );
	INSERT INTO ad_cpt_ope_cptes VALUES ( 141, NULL, 'd', 1, NumAgc() );
	INSERT INTO ad_cpt_ope_cptes VALUES ( 141, NULL, 'c', 0, NumAgc() );

	-- Ajout opération caisse/compte : Retrait espèce Ferlo
  INSERT INTO ad_cpt_ope VALUES ( 142, 'Retrait espèce par Carte Ferlo', 1, NumAgc() );
	INSERT INTO ad_cpt_ope_cptes VALUES ( 142, NULL, 'd', 0, NumAgc() );
	INSERT INTO ad_cpt_ope_cptes VALUES ( 142, NULL, 'c', 0, NumAgc() );

	-- Ajout opération caisse/compte : Recharge Carte Ferlo par versement Espèce
  INSERT INTO ad_cpt_ope VALUES ( 143, 'Recharge Carte Ferlo par versement espèce', 1, NumAgc() );
	INSERT INTO ad_cpt_ope_cptes VALUES ( 143, NULL, 'd', 4, NumAgc() );
	INSERT INTO ad_cpt_ope_cptes VALUES ( 143, NULL, 'c', 0, NumAgc() );

	-- Ajout opération caisse/compte : Dépot-Payement Ferlo
  INSERT INTO ad_cpt_ope VALUES ( 162, 'Dépôt/Payement par Carte Ferlo', 1, NumAgc() );
	INSERT INTO ad_cpt_ope_cptes VALUES ( 162, NULL, 'd', 0, NumAgc() );
	INSERT INTO ad_cpt_ope_cptes VALUES ( 162, NULL, 'c', 1, NumAgc() );

-- Creation des objets liés à la fonctionnalité d''historique des libellés
CREATE TABLE "ad_libelle"(
				"id_libelle" SERIAL NOT NULL,
				"ident"   text,
        "libelle" text NOT NULL,
        "date_modification" DATE,
        "type_libelle" text,
        "id_ag" int4 NOT NULL,
         CONSTRAINT ad_libelle_pkey PRIMARY KEY("id_libelle","date_modification","id_ag")
        );
CREATE INDEX i_ad_libelle_ident on ad_libelle(ident);
CREATE INDEX i_ad_libelle_date_creation on ad_libelle(date_modification);
CREATE INDEX i_ad_libelle_type_libelle on ad_libelle(type_libelle);

COMMENT ON TABLE "ad_libelle" IS 'Cette table stocke l''historique des libellés';
COMMENT ON COLUMN "ad_libelle"."id_libelle" IS 'Numéro autoincrémental pour identifier un libellé';
COMMENT ON COLUMN "ad_libelle"."ident" IS 'Référence du libéllé courant dans la table source';
COMMENT ON COLUMN "ad_libelle"."libelle" IS 'Libellé historisé';
COMMENT ON COLUMN "ad_libelle"."date_modification" IS 'Date de modification du libéllé';
COMMENT ON COLUMN "ad_libelle"."type_libelle" IS 'Type de libellé';

CREATE TRIGGER ad_cpt_comptable_after_update AFTER UPDATE ON ad_cpt_comptable FOR EACH ROW EXECUTE PROCEDURE trig_insert_ad_libelle();

-- Table pour la sauvegarde des mouvements déjà consolidés avant la suppression des données d'une agence pour la fusion au siège
CREATE TABLE "ad_mouvement_consolide"(
	"id_ag" int4,
	"id_mouvement" int4,
	CONSTRAINT ad_mvt_consolide_pkey PRIMARY KEY("id_ag","id_mouvement")
);

COMMENT ON TABLE "ad_mouvement_consolide" IS 'Table utile à la consolidation pour la sauvegarde des mouvements consolidés';
COMMENT ON COLUMN "ad_mouvement_consolide"."id_ag" IS 'identifiant de agence à fusioner';
COMMENT ON COLUMN "ad_mouvement_consolide"."id_mouvement" IS 'le numéro du mouvement déjà consolidé';

--
-- Ajout de droit sur la fonction consolidation pour le profil admin
--
INSERT INTO adsys_profils_axs(profil,fonction) VALUES(1, 211);

-- Suppression du contenu de la table ad_extrait_cpte (voir ticket #465)
DELETE FROM ad_extrait_cpte;

-- Mise à NULL du champ id_dern_extrait_imprime de la table ad_cpt (voir ticket #465)
UPDATE ad_cpt SET id_dern_extrait_imprime = NULL;

-- Corrections 3.0 vers 3.0.1
-- Portage des modifs de la BD depuis la v2.8.9, voir #1677
-- Ajout du champ nbre_part_social_max_cli (nbre de part sociale maximum pour un client)
ALTER TABLE ad_agc ADD COLUMN nbre_part_social_max_cli int4;
ALTER TABLE ad_agc ALTER COLUMN nbre_part_social_max_cli SET DEFAULT 0;
UPDATE ad_agc SET nbre_part_social_max_cli = 0 where nbre_part_social_max_cli is null;
-- Ajout d'un champ nbre_car_min_pwd (nbre de caractere maximum du mot de passe)
ALTER TABLE ad_agc ADD COLUMN nbre_car_min_pwd int4;
ALTER TABLE ad_agc ALTER COLUMN nbre_car_min_pwd set DEFAULT 0;
-- Ajout du champ duree_pwd durée de vie d'un mot de passe en jour
ALTER TABLE ad_agc ADD COLUMN duree_pwd int4 ;
ALTER TABLE ad_agc ALTER COLUMN duree_pwd  set DEFAULT 0;

-- Ajout du champ date_mod_pwd (date de la dernière modification du mot de passe)
ALTER TABLE ad_log ADD COLUMN date_mod_pwd Date;
-- Ajout du champ pwd_non_expire (boolean indique si le mot de passe ne peut pas expiré)
ALTER TABLE ad_log ADD COLUMN pwd_non_expire boolean ;
ALTER TABLE ad_log ALTER COLUMN pwd_non_expire  set DEFAULT false;
-- le mot de passe du login admin n'expire jamais
UPDATE ad_log set pwd_non_expire='true' where login='admin';

--Création d'une table contenant les numéros des agences du réseau
CREATE SEQUENCE "ad_agence_conso_id_seq" start 1 increment 1 maxvalue 999999 minvalue 1 cache 1 ;
CREATE TABLE "ad_agence_conso" (
	"id" int4 DEFAULT nextval('ad_agence_conso_id_seq'::text) NOT NULL,
	"num_agence" int,
	"nom_agence" text,
	PRIMARY KEY("id")
);

--Création d'une table pour la recupération correcte de la liste des tables à consolider

CREATE SEQUENCE "adsys_table_conso_id_seq" start 1 increment 1 maxvalue 999999 minvalue 1 cache 1 ;

CREATE TABLE "adsys_table_conso" (
	"id" int4 DEFAULT nextval('adsys_table_conso_id_seq'::text) NOT NULL,
	"nom_table" text ,
	PRIMARY KEY("id")
);


-- Ajouter le nom des tables contenant id_ag de manière ordonnée dans la table ADSYS_TABLE_CONSO
INSERT INTO adsys_table_conso(nom_table) VALUES('swift_op_etrangers');
INSERT INTO adsys_table_conso(nom_table) VALUES('swift_op_domestiques');
INSERT INTO adsys_table_conso(nom_table) VALUES('ad_mouvement_consolide');
INSERT INTO adsys_table_conso(nom_table) VALUES('ad_libelle');
INSERT INTO adsys_table_conso(nom_table) VALUES('ad_ord_perm');
INSERT INTO adsys_table_conso(nom_table) VALUES('adsys_asso_produitcredit_statjuri');
INSERT INTO adsys_table_conso(nom_table) VALUES('ad_frais_attente');
INSERT INTO adsys_table_conso(nom_table) VALUES('ad_gar');
INSERT INTO adsys_table_conso(nom_table) VALUES('ad_biens');
INSERT INTO adsys_table_conso(nom_table) VALUES('adsys_types_biens');
INSERT INTO adsys_table_conso(nom_table) VALUES('attentes');
INSERT INTO adsys_table_conso(nom_table) VALUES('adsys_etat_credit_cptes');
INSERT INTO adsys_table_conso(nom_table) VALUES('ad_mouvement');
INSERT INTO adsys_table_conso(nom_table) VALUES('ad_ecriture');
INSERT INTO adsys_table_conso(nom_table) VALUES('adsys_correspondant');
INSERT INTO adsys_table_conso(nom_table) VALUES('ad_brouillard');
INSERT INTO adsys_table_conso(nom_table) VALUES('adsys_objets_credits');
INSERT INTO adsys_table_conso(nom_table) VALUES('ad_rapports');
INSERT INTO adsys_table_conso(nom_table) VALUES('adsys_types_billets');
INSERT INTO adsys_table_conso(nom_table) VALUES('adsys_langue');
INSERT INTO adsys_table_conso(nom_table) VALUES('adsys_sect_activite');
INSERT INTO adsys_table_conso(nom_table) VALUES('adsys_localisation');
INSERT INTO adsys_table_conso(nom_table) VALUES('adsys_rejet_pret');
INSERT INTO adsys_table_conso(nom_table) VALUES('ad_agc');
INSERT INTO adsys_table_conso(nom_table) VALUES('ad_fer');
INSERT INTO adsys_table_conso(nom_table) VALUES('ad_gui');
INSERT INTO adsys_table_conso(nom_table) VALUES('ad_sre');
INSERT INTO adsys_table_conso(nom_table) VALUES('ad_etr');
INSERT INTO adsys_table_conso(nom_table) VALUES('ad_dcr_grp_sol');
INSERT INTO adsys_table_conso(nom_table) VALUES('ad_dcr');
INSERT INTO adsys_table_conso(nom_table) VALUES('adsys_etat_credits');
INSERT INTO adsys_table_conso(nom_table) VALUES('adsys_produit_credit');
INSERT INTO adsys_table_conso(nom_table) VALUES('ad_mandat');
INSERT INTO adsys_table_conso(nom_table) VALUES('ad_extrait_cpte');
INSERT INTO adsys_table_conso(nom_table) VALUES('ad_cpt');
INSERT INTO adsys_table_conso(nom_table) VALUES('ad_his');
INSERT INTO adsys_table_conso(nom_table) VALUES('ad_his_ext');
INSERT INTO adsys_table_conso(nom_table) VALUES('tireur_benef');
INSERT INTO adsys_table_conso(nom_table) VALUES('adsys_banque');
INSERT INTO adsys_table_conso(nom_table) VALUES('adsys_produit_epargne');
INSERT INTO adsys_table_conso(nom_table) VALUES('devise');
INSERT INTO adsys_table_conso(nom_table) VALUES('ad_journaux_liaison');
INSERT INTO adsys_table_conso(nom_table) VALUES('ad_journaux_cptie');
INSERT INTO adsys_table_conso(nom_table) VALUES('ad_journaux');
INSERT INTO adsys_table_conso(nom_table) VALUES('ad_cpt_soldes');
INSERT INTO adsys_table_conso(nom_table) VALUES('ad_cpt_ope_cptes');
INSERT INTO adsys_table_conso(nom_table) VALUES('ad_cpt_ope');
INSERT INTO adsys_table_conso(nom_table) VALUES('ad_clotures_periode');
INSERT INTO adsys_table_conso(nom_table) VALUES('ad_exercices_compta');
INSERT INTO adsys_table_conso(nom_table) VALUES('ad_rel');
INSERT INTO adsys_table_conso(nom_table) VALUES('ad_pers_ext');
INSERT INTO adsys_table_conso(nom_table) VALUES('adsys_type_piece_identite');
INSERT INTO adsys_table_conso(nom_table) VALUES('ad_grp_sol');
INSERT INTO adsys_table_conso(nom_table) VALUES('ad_cli');
INSERT INTO adsys_table_conso(nom_table) VALUES('adsys_pays');
INSERT INTO adsys_table_conso(nom_table) VALUES('ad_classes_compta');
INSERT INTO adsys_table_conso(nom_table) VALUES('ad_cpt_comptable');

-- Corrections 3.0.1 vers 3.0.2
--Ajout d'un champ dans ad_uti pour vérifier s'il est gestionnaire
ALTER TABLE ad_uti ADD COLUMN is_gestionnaire BOOLEAN ;
ALTER TABLE ad_uti ALTER COLUMN is_gestionnaire SET DEFAULT false ;

-- Mise à jour ad_ecriture

ALTER TABLE ad_ecriture ADD COLUMN type_operation integer;



-- Ajouter les nouvelles opérations comptables pour la correction d'un dossier de crédit:

INSERT INTO ad_cpt_ope VALUES (11,'Annulation remboursement capital sur crédits ',1, NumAgc());
INSERT INTO ad_cpt_ope VALUES (21,'Annulation remboursement intérêts sur crédits',1, NumAgc());
INSERT INTO ad_cpt_ope VALUES (31,'Annulation remboursement pénalités',1, NumAgc());
INSERT INTO ad_cpt_ope VALUES (124,'Recupération de la garantie numéraire',1, NumAgc());
INSERT INTO ad_cpt_ope VALUES (221,'Annulation transfert des garanties numéraires',1, NumAgc());
INSERT INTO ad_cpt_ope VALUES (401,'Contre régularisation suite à apurement crédit rééchelonné',1, NumAgc());

INSERT INTO ad_cpt_ope VALUES (231,'Annulation transfert des assurances',1, NumAgc());
INSERT INTO ad_cpt_ope VALUES (361,'Annulation perception commissions de déboursement',1, NumAgc());
INSERT INTO ad_cpt_ope VALUES (211,'Annulation déboursement crédit',1, NumAgc());
INSERT INTO ad_cpt_ope VALUES (201,'Annulation transfert des frais',1, NumAgc());


-- Paramétrage des comptes au débit et au crédit des opérations pour la correction de dossier de crédit:

INSERT INTO ad_cpt_ope_cptes VALUES (11,NULL, 'd', 2, NumAgc());
INSERT INTO ad_cpt_ope_cptes VALUES (11,NULL, 'c', 1, NumAgc());
INSERT INTO ad_cpt_ope_cptes VALUES (21,NULL, 'd', 6, NumAgc());
INSERT INTO ad_cpt_ope_cptes VALUES (21,NULL, 'c', 1, NumAgc());
INSERT INTO ad_cpt_ope_cptes VALUES (31,NULL, 'd', 7, NumAgc());
INSERT INTO ad_cpt_ope_cptes VALUES (31,NULL, 'c', 1, NumAgc());
INSERT INTO ad_cpt_ope_cptes VALUES (124,NULL, 'c', 8, NumAgc());
INSERT INTO ad_cpt_ope_cptes VALUES (124,NULL, 'd', 1, NumAgc());
INSERT INTO ad_cpt_ope_cptes VALUES (221,NULL, 'd', 8, NumAgc());
INSERT INTO ad_cpt_ope_cptes VALUES (221,NULL, 'c', 1, NumAgc());
INSERT INTO ad_cpt_ope_cptes VALUES (401,NULL, 'd', 0, NumAgc());
INSERT INTO ad_cpt_ope_cptes VALUES (401,NULL, 'c', 0, NumAgc());

INSERT INTO ad_cpt_ope_cptes VALUES (201,NULL, 'd', 0, NumAgc());
INSERT INTO ad_cpt_ope_cptes VALUES (201,NULL, 'c', 1, NumAgc());
INSERT INTO ad_cpt_ope_cptes VALUES (211,NULL, 'd', 1, NumAgc());
INSERT INTO ad_cpt_ope_cptes VALUES (211,NULL, 'c', 2, NumAgc());
INSERT INTO ad_cpt_ope_cptes VALUES (231,NULL, 'd', 0, NumAgc());
INSERT INTO ad_cpt_ope_cptes VALUES (231,NULL, 'c', 1, NumAgc());
INSERT INTO ad_cpt_ope_cptes VALUES (361,NULL, 'd', 0, NumAgc());
INSERT INTO ad_cpt_ope_cptes VALUES (361,NULL, 'c', 1, NumAgc());

-- Corrections de la 3.0.2 vers 3.0.3
-- Mise à jour ad_agc et ad_log pour la gestion du plafond de retrait au niveau du guichet (voir #1515)
-- Table ad_agc --
ALTER TABLE ad_agc ADD COLUMN plafond_retrait_guichet BOOLEAN;
ALTER TABLE ad_agc ADD COLUMN montant_plafond_retrait numeric(30,6) DEFAULT 0;
-- Table ad_log --
ALTER TABLE ad_log ADD COLUMN depasse_plafond_retrait BOOLEAN DEFAULT false;

-- Ajout d'une colonne pour l'utilisation de l'imprimante matricielle pour les demi-reçus --
ALTER TABLE ad_agc ADD COLUMN imprimante_matricielle BOOLEAN DEFAULT false;

-- Ajout table ad_poste --

CREATE TABLE ad_poste
(
  id_poste integer NOT NULL DEFAULT nextval(('ad_poste_id_poste_seq'::text)::regclass),
  libel text,
  code text,
  id_poste_centralise integer,
  niveau integer,
  compartiment integer,
  type_etat integer,
  CONSTRAINT pk_ad_poste PRIMARY KEY (id_poste)

);
--ajout de la table ad_poste_compte --

CREATE TABLE ad_poste_compte
(
  id_poste integer NOT NULL,
  num_cpte_comptable text NOT NULL,
  is_cpte_provision boolean default false ,
  CONSTRAINT pk_ad_poste_compte PRIMARY KEY (id_poste, num_cpte_comptable),
  CONSTRAINT fk_poste_poste_compte FOREIGN KEY (id_poste) REFERENCES ad_poste (id_poste)
  ON UPDATE CASCADE ON DELETE CASCADE

);

--creation de la sequence ad_poste
CREATE SEQUENCE "ad_poste_id_poste_seq" start 1 increment 1 maxvalue 214748647 minvalue 1 cache 1 ;

-- Corrections 3.0.3 vers 3.0.4
--suppression de la contrainte de clé primaire ad_cpt_soldes_pkey
ALTER TABLE ad_cpt_soldes DROP CONSTRAINT  ad_cpt_soldes_pkey;
-- ajout de la constrainte de clé primaire ds la table ad_cpt_soldes
ALTER TABLE ad_cpt_soldes ADD  CONSTRAINT  ad_cpt_soldes_pkey primary key (num_cpte_comptable_solde, id_ag, id_cloture );

-- Corrections 3.0.4 vers 3.0.5
--corriger la date déboursement pour tous les dossiers de credits
-- ayant une date de déboursement null
-- et dont l'etat est egal à déboursé,reécholonné ou en perte
UPDATE ad_dcr d SET cre_date_debloc=(
										SELECT date(c.date) FROM ad_his c
										WHERE d.id_client=c.id_client
										AND infos=d.id_doss
										AND d.id_ag = c.id_ag
										AND d.id_ag = numAgc()
 										AND type_fonction=125 )
 	WHERE d.cre_date_debloc is null
 	AND etat IN (5,7,9);

-- Mise à jour table ad_agc et ad_cpt pour l'interface Netbank : voir #1685
-- Ajout du champ  utilise_netbank (pour dire que l'agence utilise l'interface Netbank) voir #1685
	ALTER TABLE ad_agc ADD COLUMN utilise_netbank BOOLEAN ;
	ALTER TABLE ad_agc ALTER COLUMN utilise_netbank SET DEFAULT false;
-- Mise à jour table ad_cpt pour l'export netbank voir #1685
	UPDATE ad_cpt SET export_netbank = false;

-- Corrections 3.0.5 vers 3.0.6
--Ajout du numéro de la TVA dans l'agence voir #1736.
	ALTER TABLE ad_agc ADD COLUMN num_tva text ;
	ALTER TABLE ad_agc ALTER COLUMN num_tva SET DEFAULT 0;

	-- insertion d'une nouvelle operation
	INSERT INTO ad_cpt_ope VALUES (1005,'transfert solde  d'' un compte supprimé',1, NumAgc());