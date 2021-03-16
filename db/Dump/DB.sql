CREATE SEQUENCE "ad_cli_id_client_seq" start 1 increment 1 maxvalue 999999 minvalue 1 cache 1 ;
CREATE SEQUENCE "adsys_version_schema_id_seq" start 1 increment 1 maxvalue 999999 minvalue 1 cache 1 ;
CREATE SEQUENCE "ad_agence_conso_id_seq" start 1 increment 1 maxvalue 999999 minvalue 1 cache 1 ;
CREATE SEQUENCE "adsys_table_conso_id_seq" start 1 increment 1 maxvalue 999999 minvalue 1 cache 1 ;
CREATE SEQUENCE "ad_pers_ext_id_pers_ext_seq" start 1 increment 1 maxvalue 2147483647 minvalue 1  cache 1 ;
CREATE SEQUENCE "ad_rel_id_rel_seq" start 1 increment 1 maxvalue 2147483647 minvalue 1  cache 1 ;
CREATE SEQUENCE "ad_cpt_id_cpte_seq" start 1 increment 1 maxvalue 2147483647 minvalue 1  cache 1 ;
CREATE SEQUENCE "ad_dcr_id_doss_seq" start 1 increment 1 maxvalue 2147483647 minvalue 1  cache 1 ;
CREATE SEQUENCE "ad_gui_id_gui_seq" start 1 increment 1 maxvalue 2147483647 minvalue 1  cache 1 ;
CREATE SEQUENCE "ad_los_id_los_seq" start 1 increment 1 maxvalue 2147483647 minvalue 1  cache 1 ;
CREATE SEQUENCE "ad_his_id_his_seq" start 1 increment 1 maxvalue 2147483647 minvalue 1  cache 1 ;
CREATE SEQUENCE "ad_uti_id_utilis_seq" start 1 increment 1 maxvalue 2147483647 minvalue 1  cache 1 ;
COMMENT ON SEQUENCE "ad_uti_id_utilis_seq" IS 'Numéro d''utilisateur, le 1 est réservé pour Administrateur';
CREATE SEQUENCE "ad_fer_id_fer_seq" start 1 increment 1 maxvalue 2147483647 minvalue 1  cache 1 ;
CREATE SEQUENCE "ad_agc_id_ag_seq" start 1 increment 1 maxvalue 2147483647 minvalue 1  cache 1 ;
CREATE SEQUENCE "tableliste_ident_seq" start 1 increment 1 maxvalue 2147483647 minvalue 1  cache 1 ;
CREATE SEQUENCE "d_tableliste_ident_seq" start 1 increment 1 maxvalue 2147483647 minvalue 1  cache 1 ;
CREATE SEQUENCE "adsys_rejet_pret_id_seq" start 1 increment 1 maxvalue 2147483647 minvalue 1  cache 1 ;
CREATE SEQUENCE "adsys_mode_deboursement_id_seq" start 1 increment 1 maxvalue 2147483647 minvalue 1  cache 1 ;
CREATE SEQUENCE "adsys_types_billets_id_seq" start 1 increment 1 maxvalue 214748647 minvalue 1  cache 1 ;
CREATE SEQUENCE "adsys_detail_garantie_ma_id_seq" start 1 increment 1 maxvalue 2147483647 minvalue 1  cache 1 ;
CREATE SEQUENCE "adsys_localisation_id_seq" start 1 increment 1 maxvalue 2147483647 minvalue 1  cache 1 ;
CREATE SEQUENCE "adsys_sect_activite_id_seq" start 1 increment 1 maxvalue 2147483647 minvalue 1  cache 1 ;
CREATE SEQUENCE "adsys_langue_id_seq" start 1 increment 1 maxvalue 2147483647 minvalue 1  cache 1 ;
CREATE SEQUENCE "adsys_terme_credit_id_seq" start 1 increment 1 maxvalue 2147483647 minvalue 1  cache 1 ;
CREATE SEQUENCE "adsys_raison_blocage_id_seq" start 1 increment 1 maxvalue 2147483647 minvalue 1  cache 1 ;
CREATE SEQUENCE "adsys_rupture_dat_id_seq" start 1 increment 1 maxvalue 2147483647 minvalue 1  cache 1 ;
CREATE SEQUENCE "adsys_type_piece_identit_id_seq" start 1 increment 1 maxvalue 2147483647 minvalue 1  cache 1 ;
CREATE SEQUENCE "adsys_produit_epargne_id_seq" start 1 increment 1 maxvalue 2147483647 minvalue 1  cache 1 ;
CREATE SEQUENCE "adsys_produit_credit_id_seq" start 1 increment 1 maxvalue 2147483647 minvalue 1  cache 1 ;
CREATE SEQUENCE "adsys_profils_id_seq" start 1 increment 1 maxvalue 2147483647 minvalue 1  cache 1 ;
COMMENT ON SEQUENCE "adsys_profils_id_seq" IS 'Id des profils, le 1 est réservé pour Administrateur';
CREATE SEQUENCE "adsys_profils_axs_id_seq" start 1 increment 1 maxvalue 2147483647 minvalue 1  cache 1 ;
CREATE SEQUENCE "ad_his_id_key_seq" start 1 increment 1 maxvalue 2147483647 minvalue 1  cache 1 ;
CREATE SEQUENCE "adsys_his_id_key_seq" start 1 increment 1 maxvalue 2147483647 minvalue 1  cache 1 ;
CREATE SEQUENCE "id_rapports" start 1 increment 1 maxvalue 2147483647 minvalue 1  cache 1 ;
CREATE SEQUENCE "id_objets_credits" start 1 increment 1 maxvalue 2147483647 minvalue 1  cache 1 ;
CREATE SEQUENCE "ad_cpt_ope_seq" start 5000 increment 1 maxvalue 2147483647 minvalue 5000  cache 1 ;
CREATE SEQUENCE "ad_journaux_id_seq" start 1 increment 1 maxvalue 999999 minvalue 1 cache 1;
CREATE SEQUENCE "ad_exo_cpta_id_seq" start 1 increment 1 maxvalue 999999 minvalue 1 cache 1;
CREATE SEQUENCE "ad_clot_per_seq" start 1 increment 1 maxvalue 999999 minvalue 1 cache 1;
CREATE SEQUENCE "ad_classes_comptables_id_seq" start 1 increment 1 maxvalue 999999 minvalue 1 cache 1;
CREATE SEQUENCE "ad_comptable_seq" start 1 increment 1 maxvalue 999999 minvalue 1 cache 1;
CREATE SEQUENCE "ad_ecriture_seq" start 1 increment 1 maxvalue 2147483647 minvalue 1 cache 1;
CREATE SEQUENCE "ad_mouvement_seq" start 1 increment 1 maxvalue 2147483647 minvalue 1 cache 1;
CREATE SEQUENCE "ad_brouillard_seq" start 1 increment 1 maxvalue 999999 minvalue 1 cache 1;
CREATE SEQUENCE "adsys_correspondant_seq" start 1 increment 1 maxvalue 999999999 minvalue 1 cache 1;
CREATE SEQUENCE "adsys_banque_seq" start 1 increment 1 maxvalue 999999999 minvalue 1 cache 1;
CREATE SEQUENCE "tireur_benef_seq" start 1 increment 1 maxvalue 999999999 minvalue 1 cache 1;
CREATE SEQUENCE "ad_his_ext_seq" start 1 increment 1 maxvalue 999999999 minvalue 1 cache 1;
CREATE SEQUENCE "adsys_etat_credits_seq" start 1 increment 1 maxvalue 2147483647 minvalue 1  cache 1 ;
CREATE SEQUENCE "ad_extrait_cpte_seq" start 1 increment 1 maxvalue 2147483647 minvalue 1  cache 1 ;
CREATE SEQUENCE "adsys_types_biens_id_seq" start 1 increment 1 maxvalue 999999 minvalue 1 cache 1 ;
CREATE SEQUENCE "ad_biens_id_bien_seq" start 1 increment 1 maxvalue 999999 minvalue 1 cache 1 ;
CREATE SEQUENCE "ad_gar_id_gar_seq" start 1 increment 1 maxvalue 999999 minvalue 1 cache 1 ;
CREATE SEQUENCE "ad_mandat_id_mandat_seq" start 1 increment 1 maxvalue 214748647 minvalue 1  cache 1 ;
CREATE SEQUENCE "ad_dcr_grp_sol_id_seq" start 1 increment 1 maxvalue 999999 minvalue 1 cache 1 ;
CREATE SEQUENCE "ad_ord_perm_id_seq" start 1 increment 1 maxvalue 214748647 minvalue 1 cache 1 ;
CREATE SEQUENCE "ad_poste_id_poste_seq" start 1 increment 1 maxvalue 214748647 minvalue 1 cache 1 ;
CREATE SEQUENCE "adsys_type_piece_payement_seq" start 1 increment 1 maxvalue 214748647 minvalue 1 cache 1 ;
CREATE SEQUENCE "adsys_licence_id_licence_seq" start 1 increment 1 maxvalue 2147483647 minvalue 1  cache 1 ;
CREATE SEQUENCE "ad_dcr_his_id_dcr_his_seq" start 1 increment 1 maxvalue 2147483647 minvalue 1  cache 1 ;
CREATE SEQUENCE "ad_etr_his_id_etr_his_seq" start 1 increment 1 maxvalue 2147483647 minvalue 1  cache 1 ;
CREATE SEQUENCE "ad_jasper_rapport_id_rapport_seq" start 1 increment 1 maxvalue 999999 minvalue 1 cache 1 ;
CREATE SEQUENCE "ad_jasper_param_id_param_seq" start 1 increment 1 maxvalue 999999 minvalue 1 cache 1 ;

CREATE TABLE "adsys_pays"(
       "id_pays" serial NOT NULL,
       "code_pays" char(2) NOT NULL,
       "libel_pays" text,
       "libel_nationalite" text,
       "id_ag" int4 NOT NULL,
       PRIMARY KEY("id_pays","id_ag"),
       UNIQUE ("code_pays","id_ag")
    );

CREATE TABLE "ad_str" (
        "id_str" serial NOT NULL PRIMARY KEY
);

CREATE TABLE "adsys_langues_systeme" (
        "code"          text NOT NULL PRIMARY KEY,
        "langue"        integer REFERENCES ad_str(id_str) ON DELETE CASCADE
);

CREATE TABLE "adsys_version_schema" (
	"id" int4 DEFAULT nextval('adsys_version_schema_id_seq'::text) NOT NULL,
	"version" text DEFAULT '1.1.0'::text,
	"date_version" timestamp,
	PRIMARY KEY("id")
	);
CREATE TABLE "ad_agence_conso" (
	"id" int4 DEFAULT nextval('ad_agence_conso_id_seq'::text) NOT NULL,
	"num_agence" int,
	"nom_agence" text,
	PRIMARY KEY("id")
	);
--Création d'une table pour la recupération ORDONNÉE de la liste des tables à consolider
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

CREATE TABLE "ad_cli" (
	"id_client" int4 DEFAULT nextval('ad_cli_id_client_seq'::text) NOT NULL,
	"anc_id_client" text,
	"statut_juridique" int4,
	"qualite" int4,
	"adresse" text,
	"code_postal" text,
	"ville" text,
	"pays" int ,
	"pp_pays_naiss" int,
	"num_tel" text,
	"num_fax" text,
	"num_port" text,
	"email" text,
	"id_cpte_base" int4,
	"id_loc1" int4,
	"id_loc2" int4,
	"loc3" text,
	"date_adh" timestamp,
	"nbre_parts" int4,
	"etat" int4,
	"date_rupt" timestamp,
	"nb_imf" int2,
	"nb_bk" int2,
	"sect_act" int4,
	"dern_modif" timestamp,
	"utilis_modif" int4,
	"date_crea" timestamp,
	"utilis_crea" int4,
	"gestionnaire" int4,
	"langue" int4,
	"nbre_credits" int2 DEFAULT 0,
	"date_defection" timestamp,
	"pp_nom" text,
	"pp_prenom" text,
	"pp_date_naissance" timestamp,
	"pp_lieu_naissance" text,
	"pp_sexe" int4,
        "pp_nationalite" int ,
	"pp_type_piece_id" int4,
	"pp_date_piece_id" date,
    "pp_lieu_delivrance_id" text,
	"pp_nm_piece_id" text,
    "pp_date_exp_id" date,
	"pp_etat_civil" int4,
	"pp_nbre_enfant" int2,
	"pp_casier_judiciaire" bool,
	"pp_revenu" numeric(30,6),
	"pp_id_gi" int4,
	"pp_pm_patrimoine" text,
	"pp_pm_activite_prof" text,
    "pp_employeur" text,
    "pp_fonction" text,
	"pm_raison_sociale" text,
	"pm_abreviation" text,
    "pm_date_expiration" date,
    "pm_date_notaire" date,
    "pm_date_depot_greffe" date,
    "pm_lieu_depot_greffe" text,
    "pm_numero_reg_nat" text,
    "pm_numero_nric" text,
    "pm_lieu_nric" text,
    "pm_nature_juridique" text,
    "pm_tel2" text,
    "pm_tel3" text,
    "pm_email2" text,
	"pm_date_constitution" timestamp,
	"pm_agrement_nature" text,
	"pm_agrement_autorite" text,
	"pm_agrement_numero" int,
	"pm_agrement_date" timestamp,
	"gi_nom" text,
	"gi_date_agre" timestamp,
	"gi_nbre_membr" int2,
	"gi_date_dissol" timestamp,
	"raison_defection" text,
	"tmp_already_accessed" bool,
	"pm_categorie" int,
    "langue_correspondance" text  NOT NULL,
    "gs_responsable" int,
    "solde_frais_adhesion_restant" numeric(30,6) DEFAULT 0,
    "commentaires_cli" text,
    "id_ag" int4 NOT NULL,
	"pp_is_vip" boolean DEFAULT false,
       	PRIMARY KEY ("id_client","id_ag"),
        FOREIGN KEY (pp_pays_naiss,id_ag) REFERENCES "adsys_pays" ,
        FOREIGN KEY (pays,id_ag) REFERENCES "adsys_pays" ,
        FOREIGN KEY (pp_nationalite,id_ag) REFERENCES "adsys_pays" ,
        FOREIGN KEY (langue_correspondance) REFERENCES "adsys_langues_systeme"
);
COMMENT ON COLUMN "ad_cli"."statut_juridique" IS 'Statut juridique du client (Personne Physique, Personne Morale, Groupe Informel). Ref table sys.';
COMMENT ON COLUMN "ad_cli"."id_cpte_base" IS 'Identificateur du compte de base du client';
COMMENT ON COLUMN "ad_cli"."qualite" IS 'Qualité du client (p.ex. ordinaire, employé, etc.). Ref table sys.';
COMMENT ON COLUMN "ad_cli"."etat" IS 'Statut (p.ex. décédé, actif, inactif, etc.). Ref table sys.';
COMMENT ON COLUMN "ad_cli"."pm_abreviation" IS 'Abréviation du nom (renseigné uniquement dans le cas de clients personne morale ou groupe informel)';
COMMENT ON COLUMN "ad_cli"."gi_nbre_membr" IS 'Nombre de membres, valide uniquement dans le cas d''un groupe informel.';
COMMENT ON COLUMN "ad_cli"."pp_id_gi" IS 'Identificateur du groupe informel auquel appartient le client personne physique. NULL si aucun.';
COMMENT ON COLUMN "ad_cli"."tmp_already_accessed" IS 'Champs indiquant si c''est le remier acces a ce client, utilise dans le processus de rerise des donnees';
COMMENT ON TABLE "ad_cli" IS 'Table des clients. Les champs précédés par "pp" ne concernent que les clients personnes physiques tout comme "gi" ne concerne que les clients groupe informel et "pm" les clients personne morale.';

CREATE TABLE "ad_grp_sol" (
        "id_grp_sol" int4 NOT NULL,
        "id_membre" int4 NOT NULL,
         "id_ag" int4 NOT NULL,
         PRIMARY KEY("id_grp_sol","id_membre","id_ag"),
         FOREIGN KEY (id_membre,id_ag) REFERENCES "ad_cli"
);
COMMENT ON COLUMN "ad_grp_sol"."id_grp_sol" IS 'id_client du groupe solidaire';
COMMENT ON COLUMN "ad_grp_sol"."id_membre" IS 'id_client du membre du groupe solidaire';

CREATE TABLE "adsys_type_piece_identite" (
        "id" integer DEFAULT nextval('"adsys_type_piece_identit_id_seq"'::text) NOT NULL,
        "libel" integer REFERENCES ad_str(id_str) ON DELETE CASCADE,
        "id_ag" int4 NOT NULL,
        PRIMARY KEY("id","id_ag")
);
COMMENT ON TABLE "adsys_type_piece_identite" IS 'Types de pièces d''identité';

CREATE TABLE "ad_pers_ext" (
	"id_pers_ext" int4 DEFAULT nextval('ad_pers_ext_id_pers_ext_seq'::text) NOT NULL,
	"id_client" int4,
	"denomination" text,
	"adresse" text,
	"code_postal" text,
	"ville" text,
	"pays" int,
	"num_tel" text,
	"date_naiss" date,
	"lieu_naiss" text,
	"type_piece_id" int4,
	"num_piece_id" text,
	"lieu_piece_id" text,
	"date_piece_id" date,
	"date_exp_piece_id" date,
	"photo" OID,
	"signature" OID,
	"id_ag" int4 NOT NULL,
	PRIMARY KEY("id_pers_ext","id_ag"),
	FOREIGN KEY (id_client,id_ag) REFERENCES "ad_cli" ,
	FOREIGN KEY (pays,id_ag) REFERENCES "adsys_pays" ,
	FOREIGN KEY (type_piece_id,id_ag) REFERENCES "adsys_type_piece_identite"
);

CREATE TABLE "ad_rel" (
	"id_rel" int4 DEFAULT nextval('ad_rel_id_rel_seq'::text) NOT NULL,
	"id_client" int4 NOT NULL,
	"id_pers_ext" int4  NOT NULL,
	"typ_rel" int4 NOT NULL,
	"valide" bool,
	"id_ag" int4 NOT NULL,
	PRIMARY KEY("id_rel","id_ag"),
	FOREIGN KEY (id_client,id_ag) REFERENCES "ad_cli" ,
	FOREIGN KEY (id_pers_ext,id_ag) REFERENCES "ad_pers_ext"
);
COMMENT ON COLUMN "ad_rel"."typ_rel" IS 'Type de relation (conjoint, frère, soeur, etc...)';
COMMENT ON COLUMN "ad_rel"."id_client" IS 'Identificateur du client avec lequel a lieu la relation';
COMMENT ON COLUMN "ad_rel"."valide" IS 'Indique si la relation est valide (p.ex. si c''est un ancien responsable)';
COMMENT ON TABLE "ad_rel" IS 'Relations entre clients, cette table permet de stocker les relations qu''ont les clients. Une personne en relation avec un client peut être client (auquel cas on renseigne id_clientrel) ou peut ne pas l''être (auquel cas on renseigne nom, prenom, date_naiss, lieu_naiss)';

CREATE TABLE "ad_exercices_compta" (
	"id_exo_compta" int4 DEFAULT nextval('ad_exo_cpta_id_seq'::text) NOT NULL,
	"date_deb_exo" date NOT NULL,
	"date_fin_exo" date NOT NULL,
	"etat_exo" int4,
	"id_ag" int4 NOT NULL,
	PRIMARY KEY ("id_exo_compta","id_ag")
);

CREATE TABLE "ad_clotures_periode" (
	"id_clot_per" int4 DEFAULT nextval('ad_clot_per_seq'::text) NOT NULL,
	"date_clot_per" date,
	"id_exo" int4,
	"id_ag" int4 NOT NULL,
	PRIMARY KEY ("id_clot_per","id_ag"),
	FOREIGN KEY (id_exo,id_ag) REFERENCES "ad_exercices_compta"
);
COMMENT ON COLUMN "ad_clotures_periode"."date_clot_per" IS 'Les dates de valeur comptables sont au format date et pas timestamp ';
CREATE TABLE "ad_classes_compta" (
	"id_classe" int4 DEFAULT nextval('ad_classes_comptables_id_seq'::text) NOT NULL,
	"numero_classe" integer NOT NULL,
	"libel_classe" text NOT NULL,
	"id_ag" int4 NOT NULL,
	PRIMARY KEY ("id_classe","id_ag"),
	UNIQUE (numero_classe,id_ag)
);

CREATE TABLE "ad_cpt_comptable" (
	"num_cpte_comptable" varchar(50) NOT NULL, 
	"libel_cpte_comptable" text NOT NULL,
	"sens_cpte" int4  NOT NULL,
	"classe_compta"  integer  NOT NULL,
	"compart_cpte" int4  NOT NULL,
	"etat_cpte" int4 NOT NULL ,
	"date_ouvert" timestamp,
	"cpte_centralise" text,
	"cpte_princ_jou" bool,
	"solde" numeric(30,6) NOT NULL DEFAULT 0,
	"devise" char(3),
	"cpte_provision" text,
	"id_ag" int4 NOT NULL,
	"is_actif" bool DEFAULT 'true',
	"date_modif" timestamp DEFAULT NULL,
	"is_hors_bilan" bool DEFAULT 'false',
	"niveau" int4 DEFAULT 0,
	PRIMARY KEY ("num_cpte_comptable","id_ag"),
	FOREIGN KEY (classe_compta,id_ag) REFERENCES ad_classes_compta(numero_classe,id_ag) ,
	FOREIGN KEY (cpte_centralise,id_ag) REFERENCES "ad_cpt_comptable" ,
	FOREIGN KEY (cpte_provision,id_ag) REFERENCES "ad_cpt_comptable"
	);
COMMENT ON COLUMN "ad_cpt_comptable"."num_cpte_comptable" IS 'On permet de saisir des n° de comptes alphanumériques ';
COMMENT ON COLUMN "ad_cpt_comptable"."sens_cpte" IS 'Le sens naturel d''un compte est soit débiteur,créditeur ou mixte';
COMMENT ON COLUMN "ad_cpt_comptable"."cpte_centralise" IS 'Si un compte a un compte centralisateur, on note le n° de ce compte ';
COMMENT ON COLUMN "ad_cpt_comptable"."cpte_provision" IS 'Si un compte a un compte de provision, on note le n° de ce compte ';
COMMENT ON COLUMN "ad_cpt_comptable"."cpte_princ_jou" IS 'Un compte ne peut etre compte principal que d''un journal auxiliaire à la fois. D''office, les sous-comptes doivent aussi être marqués comme comptes principaux';

CREATE TABLE "ad_cpt_soldes" (
	"num_cpte_comptable_solde" text  NOT NULL,
	"id_cloture" int4 NOT NULL,
	"solde_cloture" numeric(30,6) NOT NULL DEFAULT 0,
	"id_ag" int4 NOT NULL,
	PRIMARY KEY ("num_cpte_comptable_solde", "id_cloture","id_ag"),
	FOREIGN KEY (num_cpte_comptable_solde,id_ag) REFERENCES "ad_cpt_comptable" ,
	FOREIGN KEY (id_cloture,id_ag) REFERENCES "ad_clotures_periode"
);
COMMENT ON TABLE "ad_cpt_soldes" IS 'Cette table permet de mémoriser les soldes des comptes à chaque cloture périodique ';

CREATE TABLE "ad_journaux" (
	"id_jou" int4 DEFAULT nextval('ad_journaux_id_seq'::text) NOT NULL,
	"libel_jou" integer NOT NULL,
	"num_cpte_princ" text,
	"etat_jou" int4 NOT NULL,
	"code_jou" char(3),
	"last_ref_ecriture" integer DEFAULT 0,
	"id_ag" int4 NOT NULL,
	PRIMARY KEY ("id_jou","id_ag"),
	UNIQUE (code_jou,id_ag),
	FOREIGN KEY (num_cpte_princ,id_ag) REFERENCES ad_cpt_comptable(num_cpte_comptable,id_ag)
	);
COMMENT ON COLUMN "ad_journaux"."etat_jou" IS 'Le journal peut etre actif ou inactif suite à une suppression';

CREATE TABLE "ad_journaux_cptie" (
	"id_jou" int4 NOT NULL,
	"num_cpte_comptable" text NOT NULL ,
	"id_ag" int4 NOT NULL,
	PRIMARY KEY ("id_jou","num_cpte_comptable","id_ag"),
	FOREIGN KEY (id_jou,id_ag) REFERENCES ad_journaux(id_jou,id_ag) ,
	FOREIGN KEY (num_cpte_comptable,id_ag) REFERENCES ad_cpt_comptable(num_cpte_comptable,id_ag)
	);
COMMENT ON COLUMN "ad_journaux_cptie"."id_jou" IS 'Le id journal du compte principal';
COMMENT ON COLUMN "ad_journaux_cptie"."num_cpte_comptable" IS 'Le num  du compte de compte partie';

CREATE TABLE "ad_journaux_liaison" (
	"id_jou1" int4 NOT NULL,
	"id_jou2" int4 NOT NULL,
	"num_cpte_comptable" text NOT NULL ,
	"id_ag" int4 NOT NULL,
	PRIMARY KEY ("id_jou1","id_jou2","id_ag"),
	FOREIGN KEY (id_jou1,id_ag) REFERENCES ad_journaux(id_jou,id_ag) ,
	FOREIGN KEY (id_jou2,id_ag) REFERENCES ad_journaux(id_jou,id_ag) ,
	FOREIGN KEY (num_cpte_comptable,id_ag) REFERENCES ad_cpt_comptable(num_cpte_comptable,id_ag)
	);
COMMENT ON COLUMN "ad_journaux_liaison"."id_jou1" IS 'Le id du premier journal principal';
COMMENT ON COLUMN "ad_journaux_liaison"."id_jou2" IS 'Le id du deuxieme journal principal';
COMMENT ON COLUMN "ad_journaux_liaison"."num_cpte_comptable" IS 'Le num  du compte de liaison entre les deux journaux';

CREATE TABLE "devise" (
       "code_devise" char(3),
       "libel_devise" text NOT NULL,
       "taux_indicatif" float NOT NULL,
       "taux_achat_cash" float NOT NULL,
       "taux_achat_trf" float NOT NULL,
       "taux_vente_cash" float NOT NULL,
       "taux_vente_trf" float NOT NULL,
       "precision" integer,
       "cpte_produit_commission" text ,
       "cpte_produit_taux" text ,
       "cpte_perte_taux" text ,
       "id_ag" int4 NOT NULL,
       PRIMARY KEY ("code_devise","id_ag"),
       FOREIGN KEY (cpte_produit_commission,id_ag) REFERENCES "ad_cpt_comptable" ,
       FOREIGN KEY (cpte_produit_taux,id_ag) REFERENCES "ad_cpt_comptable" ,
       FOREIGN KEY (cpte_perte_taux,id_ag) REFERENCES "ad_cpt_comptable"
   );

CREATE TABLE "adsys_produit_epargne" (
	"id" integer DEFAULT nextval('"adsys_produit_epargne_id_seq"'::text) NOT NULL,
	"libel" text,
	"sens" char(1),
	"service_financier" bool,
	"nbre_occurrences" integer,
	"tx_interet" double precision,
	"terme" integer,
	"mnt_min" numeric(30,6) DEFAULT 0,
	"mnt_max" numeric(30,6) DEFAULT 0,
	"mode_paiement" integer,
	"mode_calcul_int" integer,
	"freq_calcul_int" integer,
	"marge_tolerance" integer DEFAULT 0,
	"penalite_const" numeric(30,6) DEFAULT 0,
	"penalite_prop" double precision DEFAULT 0,
	"frais_ouverture_cpt" numeric(30,6) DEFAULT 0,
	"frais_tenue_cpt" numeric(30,6) DEFAULT 0,
	"frais_tenue_prorata" bool,
	"frequence_tenue_cpt" int4,
	"frais_retrait_cpt" numeric(30,6) DEFAULT 0,
	"frais_depot_cpt" numeric(30,6) DEFAULT 0,
	"frais_fermeture_cpt" numeric(30,6) DEFAULT 0,
	"frais_transfert" numeric(30,6) DEFAULT 0,
	"retrait_unique" bool,
	"depot_unique" bool,
	"certif" bool,
	"modif_cptes_existants" bool,
	"duree_min_retrait_jour" integer,
	"dat_prolongeable" bool,
	"classe_comptable" integer,
	"mode_calcul_int_rupt"	integer,
	"cpte_cpta_prod_ep" text ,
	"cpte_cpta_prod_ep_int" text ,
        "nbre_jours_report_debit" integer DEFAULT 0,
        "nbre_jours_report_credit" integer DEFAULT 0,
	"devise" char(3) ,
	"decouvert_max"	numeric(30,6),
	"decouvert_frais" numeric(30,6) DEFAULT 0,
	"decouvert_frais_dossier" numeric(30,6) DEFAULT 0,
	"decouvert_frais_dossier_prc" double precision DEFAULT 0,
	"tx_interet_debiteur" double precision,
        "decouvert_annul_auto" bool DEFAULT false,
        "decouvert_validite" int4 DEFAULT 0,
        "frais_chequier" numeric(30,6) DEFAULT 0,
        "seuil_rem_dav" numeric(30,6) DEFAULT 0,
        "id_ag" int4 NOT NULL,
    "mode_calcul_penal_rupt" integer,
  "ep_source_date_fin" timestamp,
  "masque_solde_epargne" bool default false,
	Constraint "adsys_produit_epargne_pkey" Primary Key ("id","id_ag"),
	FOREIGN KEY (cpte_cpta_prod_ep,id_ag) REFERENCES "ad_cpt_comptable" ,
    FOREIGN KEY (cpte_cpta_prod_ep_int,id_ag) REFERENCES "ad_cpt_comptable" ,
    FOREIGN KEY (devise,id_ag) REFERENCES "devise"
);
COMMENT ON TABLE "adsys_produit_epargne" IS 'Produits d''épargne';
COMMENT ON COLUMN  "adsys_produit_epargne"."sens" IS '''d'' = compte débiteur et ''c'' = compte créditeur';
COMMENT ON COLUMN  "adsys_produit_epargne"."mnt_max" IS 'Si -1 alors pas de montant max';
COMMENT ON COLUMN  "adsys_produit_epargne"."service_financier" IS 'Si true, le produit est visible pour les clients qui désirent ouvrir un compte';
COMMENT ON COLUMN  "adsys_produit_epargne"."duree_min_retrait_jour" IS 'Si -1 alors retrait non-autorisé';
COMMENT ON COLUMN  "adsys_produit_epargne"."nbre_occurrences" IS 'Si -1 alors nombre illimité d''occurrence permises';
COMMENT ON COLUMN  "adsys_produit_epargne"."ep_source_date_fin" IS 'date de fin de cycle utilisée uniquement pour produits d épargne à la source';

CREATE TABLE "adsys_banque" (
       "id_banque" int4 DEFAULT nextval('adsys_banque_seq'::text) NOT NULL,
       "code_swift" text,
       "nom_banque" text,
       "adresse" text,
       "code_postal" text,
       "ville" text,
       "pays" int ,
       "id_ag" int4 NOT NULL,
       PRIMARY KEY ("id_banque","id_ag"),
       FOREIGN KEY (pays,id_ag) REFERENCES "adsys_pays"
);

CREATE TABLE "tireur_benef" (
       "id" int4 DEFAULT nextval('tireur_benef_seq'::text) NOT NULL,
       "denomination" text NOT NULL,
       "tireur" boolean,
       "beneficiaire" boolean,
       "adresse" text,
       "code_postal" text,
       "ville" text,
       "pays" int,
       "num_tel" text,
       "num_cpte" text,
       "id_banque" integer,
       "iban_cpte" text,
       "type_piece" integer,
       "num_piece" varchar(50),
       "lieu_delivrance" text,
       "id_ag" int4 NOT NULL,
       PRIMARY KEY ("id","id_ag"),
       FOREIGN KEY (pays,id_ag) REFERENCES "adsys_pays" ,
       FOREIGN KEY (id_banque,id_ag) REFERENCES "adsys_banque"
);

CREATE TABLE "ad_his_ext" (
       "id" int4 DEFAULT nextval('ad_his_ext_seq'::text) NOT NULL,
       "communication" text,
       "id_tireur_benef" integer,
       "type_piece" smallint,
       "num_piece" text,
       "remarque" text,
       "sens" char(3),
       "date_piece" date,
       "id_pers_ext" integer,
       "id_ag" int4 NOT NULL,
       PRIMARY KEY ("id","id_ag"),
       FOREIGN KEY (id_tireur_benef,id_ag) REFERENCES "tireur_benef" ,
       FOREIGN KEY (id_pers_ext,id_ag) REFERENCES "ad_pers_ext"
);

CREATE TABLE "ad_his" (
        "id_his" int4 DEFAULT nextval('ad_his_id_his_seq'::text) NOT NULL,
        "type_fonction" int4,
        "id_client" int4,
        "login" text,
        "infos" text,
        "date" timestamp,
        "id_his_ext" int4,
        "id_ag" int4 NOT NULL,
        PRIMARY KEY ("id_his","id_ag"),
        FOREIGN KEY (id_his_ext,id_ag) REFERENCES "ad_his_ext"
);
COMMENT ON COLUMN "ad_his"."id_client" IS 'Client pour lequel a été effectué l''opération';
COMMENT ON TABLE "ad_his" IS 'Historique';

CREATE TABLE "ad_cpt" (
	"id_cpte" int4 DEFAULT nextval('ad_cpt_id_cpte_seq'::text) NOT NULL,
	"id_titulaire" int4,
	"date_ouvert" timestamp,
	"utilis_crea" int4,
	"etat_cpte" int4,
	"nbr_jours_bloque" int2,
	"solde" numeric(30,6) DEFAULT 0,
	"interet_annuel" numeric(30,6) DEFAULT 0,
	"interet_a_capitaliser" numeric(30,6) DEFAULT 0,
	"solde_calcul_interets" numeric(30,6) DEFAULT 0,
	"date_solde_calcul_interets" timestamp,
	"date_calcul_interets" timestamp,
	"tx_interet_cpte" double precision,
	"terme_cpte" integer,
	"freq_calcul_int_cpte" integer,
	"mode_calcul_int_cpte" integer,
	"cpte_virement_clot" integer,
	"mode_paiement_cpte" integer,
	"date_clot" timestamp,
	"solde_clot" numeric(30,6) DEFAULT 0,
	"raison_clot" int4,
	"mnt_bloq" numeric(30,6) DEFAULT 0,
	"num_cpte" int4,
	"num_complet_cpte" text,
	"id_prod" int4,
	"raison_blocage" text ,
	"date_blocage" timestamp without time zone,
	"utilis_bloquant" text,
	"dat_prolongation" bool,
	"dat_date_fin" timestamp,
	"dat_num_certif" text,
	"dat_nb_prolong" int2,
	"dat_decision_client" bool,
	"dat_date_decision_client" timestamp,
	"intitule_compte" text,
	"devise" char(3),
	"decouvert_max"	numeric(30,6),
	"cpt_vers_int"	integer,
        "export_netbank" bool,
        "id_dern_extrait_imprime" int4,
        "decouvert_date_util" timestamp,
        "num_last_cheque" integer DEFAULT 0,
        "etat_chequier" int4 DEFAULT 0,
        "date_demande_chequier" timestamp,
        "chequier_num_cheques" int4 DEFAULT 25,
        "mnt_min_cpte" numeric(30,6) DEFAULT 0,
        "solde_part_soc_restant" numeric(30,6) DEFAULT 0,
        "id_ag" int4 NOT NULL,
        "dat_nb_reconduction" integer DEFAULT 0 ,
	"num_cpte_comptable" text,
	PRIMARY KEY ("id_cpte","id_ag") ,
	UNIQUE ("num_complet_cpte","id_ag"),
	FOREIGN KEY (id_titulaire,id_ag) REFERENCES "ad_cli" ,
    FOREIGN KEY (id_prod,id_ag) REFERENCES "adsys_produit_epargne" ,
    FOREIGN KEY (devise,id_ag) REFERENCES "devise"
);

COMMENT ON TABLE "ad_cpt" IS 'Table des comptes d''épargne des clients';
COMMENT ON COLUMN "ad_cpt"."date_solde_calcul_interets" IS 'Date à laquelle le solde_calcul_interets a été mit à jour pour la dernière fois';
COMMENT ON COLUMN "ad_cpt"."interet_a_capitaliser" IS 'La somme des intérêts à payer à la date de capitalisation';
COMMENT ON COLUMN "ad_cpt"."num_cpte" IS 'Numérotation du compte individuelle pour le client';
COMMENT ON COLUMN "ad_cpt"."nbr_jours_bloque" IS 'Nombre de jours durant lesquels le compte a été bloqué pour la période en cours';
COMMENT ON COLUMN "ad_cpt"."num_complet_cpte" IS 'Le numéro d''un compte est composé des 4 parties suivantes (nbre decimales) : num agence (2), num client (6), num compte client (2), check modulo97 (2)';
COMMENT ON COLUMN "ad_cpt"."solde_calcul_interets" IS 'C''est le solde utilisé pour le calcul des intérêts. Correspond en général au solde le plus bas sur une période dépendant de la fréquence de calcul des intérêts.Mis à jour par les traitements de fin de journée';
COMMENT ON TABLE "ad_cpt" IS 'Comptes, tous les comptes sont stockés ici (comptes d''épargne, de crédit, guichet, internes). Les champs précédés par "dat" ne concernent que les comptes DAT (Dépot A Terme). Le numéro complet du compte est composé comme suit : n°agence(2)-n°client(6)-n°compte client(2)-check modulo(2), cela donne donc : xx-xxxxxx-xx-xx';
COMMENT ON COLUMN "ad_cpt"."export_netbank" IS 'Exporter vers NetBank';
COMMENT ON COLUMN "ad_cpt"."id_dern_extrait_imprime" IS 'ID du dernier extrait de compte imprimé';
COMMENT ON COLUMN "ad_cpt"."num_cpte_comptable" IS 'Le compte comptable associé a ce compte interne';

CREATE INDEX i_ad_cpt_num_cpte_comptable on ad_cpt(num_cpte_comptable);

CREATE TRIGGER b_utilisation_decouvert BEFORE UPDATE ON ad_cpt FOR EACH ROW EXECUTE PROCEDURE trig_utilisation_decouvert();
CREATE TRIGGER c_calcul_solde_temps_reel BEFORE UPDATE ON ad_cpt FOR EACH ROW EXECUTE PROCEDURE trig_calcul_solde_temps_reel();
CREATE TRIGGER trig_before_update_ad_cpt BEFORE UPDATE ON ad_cpt FOR EACH ROW EXECUTE PROCEDURE trig_insert_ad_cpt_hist();

CREATE TABLE "ad_extrait_cpte" (
	"id_extrait_cpte" int4 DEFAULT nextval('ad_extrait_cpte_seq'::text) NOT NULL,
	"id_his" int4 ,
	"id_cpte" int4  NOT NULL,
	"montant" numeric(30,6) NOT NULL,
        "intitule" text NOT NULL,
	"date_exec" date NOT NULL,
	"date_valeur" date NOT NULL,
        "taux" double precision,
        "mnt_frais" numeric(30,6),
        "mnt_comm_change" numeric(30,6),
        "information" text,
        "cptie_num_cpte" text,
        "cptie_nom" text,
        "cptie_adresse" text,
        "cptie_cp" text,
        "cptie_ville" text,
        "cptie_pays" int4 ,
        "cptie_mnt" numeric(30,6),
	"cptie_devise" char(3) ,
        "eft_id_extrait" int4  ,
        "eft_id_mvt" int4 ,
        "eft_id_client" int4 ,
        "eft_annee_oper" char(4) ,
        "eft_dern_solde" numeric(30,6) ,
        "eft_dern_date" date ,
        "eft_nouv_solde" numeric(30,6) ,
        "eft_sceau" timestamp DEFAULT now(),
        "id_ag" int4 NOT NULL,
	PRIMARY KEY ("id_extrait_cpte","id_ag"),
	FOREIGN KEY (id_his,id_ag) REFERENCES "ad_his" ,
	FOREIGN KEY (id_cpte,id_ag) REFERENCES "ad_cpt" ,
	FOREIGN KEY (cptie_pays,id_ag) REFERENCES "adsys_pays" ,
	FOREIGN KEY (cptie_devise,id_ag) REFERENCES "devise" ,
	FOREIGN KEY (eft_id_client,id_ag) REFERENCES "ad_cli"
);
CREATE INDEX i_ad_extrait_cpte_cpte on ad_extrait_cpte(id_cpte);

COMMENT ON COLUMN "ad_extrait_cpte"."id_extrait_cpte" IS 'Identifiant';
COMMENT ON COLUMN "ad_extrait_cpte"."id_his" IS 'Numéro d historique';
COMMENT ON COLUMN "ad_extrait_cpte"."id_cpte" IS 'Numéro de compte';
COMMENT ON COLUMN "ad_extrait_cpte"."montant" IS 'Montant de l opération';
COMMENT ON COLUMN "ad_extrait_cpte"."intitule" IS 'Intitulé de l opération';
COMMENT ON COLUMN "ad_extrait_cpte"."date_exec" IS 'Date d exécution';
COMMENT ON COLUMN "ad_extrait_cpte"."date_valeur" IS 'Date valeur';
COMMENT ON COLUMN "ad_extrait_cpte"."taux" IS 'Taux de change utilisé (de la devise achetée vers la devise vendue)';
COMMENT ON COLUMN "ad_extrait_cpte"."mnt_frais" IS 'Frais ou taxes';
COMMENT ON COLUMN "ad_extrait_cpte"."mnt_comm_change" IS 'Commission de change';
COMMENT ON COLUMN "ad_extrait_cpte"."information" IS 'Informations complémentaires';
COMMENT ON COLUMN "ad_extrait_cpte"."cptie_num_cpte" IS 'Numéro de compte de la contrepartie';
COMMENT ON COLUMN "ad_extrait_cpte"."cptie_nom" IS 'Dénomination de la contrepartie';
COMMENT ON COLUMN "ad_extrait_cpte"."cptie_adresse" IS 'Adresse de la contrepartie';
COMMENT ON COLUMN "ad_extrait_cpte"."cptie_cp" IS 'Code postal de la contrepartie';
COMMENT ON COLUMN "ad_extrait_cpte"."cptie_ville" IS 'Ville de la contrepartie';
COMMENT ON COLUMN "ad_extrait_cpte"."cptie_pays" IS 'Pays de la contrepartie';
COMMENT ON COLUMN "ad_extrait_cpte"."cptie_mnt" IS 'Montant de la contrepartie';
COMMENT ON COLUMN "ad_extrait_cpte"."cptie_devise" IS 'Devise de la contrepartie';
COMMENT ON COLUMN "ad_extrait_cpte"."eft_id_extrait" IS 'Numéro d extrait EFT';
COMMENT ON COLUMN "ad_extrait_cpte"."eft_id_mvt" IS 'Numéro de mouvement EFT';
COMMENT ON COLUMN "ad_extrait_cpte"."eft_id_client" IS 'Numéro de client EFT';
COMMENT ON COLUMN "ad_extrait_cpte"."eft_annee_oper" IS 'Année de l opération EFT';
COMMENT ON COLUMN "ad_extrait_cpte"."eft_dern_solde" IS 'Dernier solde EFT';
COMMENT ON COLUMN "ad_extrait_cpte"."eft_dern_date" IS 'Dernière date EFT';
COMMENT ON COLUMN "ad_extrait_cpte"."eft_nouv_solde" IS 'Nouveau solde EFT';
COMMENT ON COLUMN "ad_extrait_cpte"."eft_sceau" IS 'Sceau EFT';

CREATE TABLE "ad_mandat" (
        "id_mandat" int4 DEFAULT nextval('ad_mandat_id_mandat_seq'::text) NOT NULL,
        "id_cpte" int4 NOT NULL,
        "id_pers_ext" int4 NOT NULL,
        "type_pouv_sign" int4 NOT NULL,
        "limitation" numeric(30,6),
        "date_exp" date,
        "valide" bool,
        "id_ag" int4 NOT NULL,
        PRIMARY KEY("id_mandat","id_ag"),
        FOREIGN KEY (id_cpte,id_ag) REFERENCES "ad_cpt" ,
        FOREIGN KEY (id_pers_ext,id_ag) REFERENCES "ad_pers_ext"
);

CREATE TABLE "adsys_produit_credit" (
	"id" integer DEFAULT nextval('"adsys_produit_credit_id_seq"'::text) NOT NULL,
	"libel" text,
	"tx_interet" double precision,
	"mnt_min" numeric(30,6) DEFAULT 0,
	"mnt_max" numeric(30,6) DEFAULT 0,
	"mode_calc_int" integer,
	"mode_perc_int" integer,
	"duree_min_mois" integer,
	"duree_max_mois" integer,
	"periodicite" integer,
	"mnt_frais" numeric(30,6) DEFAULT 0,
	"mnt_commission" numeric(30,6) DEFAULT 0,
	"prc_assurance" double precision DEFAULT 0,
	"prc_gar_num" double precision DEFAULT 0,
	"prc_gar_mat" double precision DEFAULT 0,
	"prc_gar_tot" double precision DEFAULT 0,
	"prc_gar_encours" double precision DEFAULT 0,
	"mnt_penalite_jour" numeric(30,6) DEFAULT 0,
	"prc_penalite_retard" double precision DEFAULT 0,
	"delai_grace_jour" integer,
	"differe_jours_max" integer,
	"nbre_reechelon_auth" smallint,
	"prc_commission" double precision DEFAULT 0,
	"type_duree_credit" integer,
	"approbation_obli" boolean DEFAULT 'true',
	"typ_pen_pourc_dcr" integer,
	"cpte_cpta_prod_cr_int" text ,
	"cpte_cpta_prod_cr_gar" text ,
	"cpte_cpta_prod_cr_pen" text ,
	"cpte_cpta_att_deb" text ,
	"devise" char(3),
  "differe_ech_max" integer,
  "freq_paiement_cap" integer DEFAULT '1',
  "max_jours_compt_penalite" integer,
  "gs_cat" smallint,
  "id_ag" int4 NOT NULL,
  "prelev_frais_doss" smallint DEFAULT '1',
  "percep_frais_com_ass" smallint DEFAULT '1',
  "differe_epargne_nantie" boolean DEFAULT 'true',
  "report_arrondi" boolean DEFAULT 'true',
  "calcul_interet_differe" boolean DEFAULT 'true',
  "ordre_remb" smallint DEFAULT '1',
  "remb_cpt_gar" boolean DEFAULT 'false',
  "mnt_assurance" numeric(30,6) DEFAULT 0,
  "prc_frais" double precision DEFAULT 0,
         Constraint "adsys_produit_credit_pkey" Primary Key ("id","id_ag"),
         FOREIGN KEY (cpte_cpta_prod_cr_int,id_ag) REFERENCES "ad_cpt_comptable" ,
         FOREIGN KEY (cpte_cpta_prod_cr_gar,id_ag) REFERENCES "ad_cpt_comptable" ,
         FOREIGN KEY (cpte_cpta_prod_cr_pen,id_ag) REFERENCES "ad_cpt_comptable" ,
         FOREIGN KEY (devise,id_ag) REFERENCES "devise"
);
COMMENT ON TABLE "adsys_produit_credit" IS 'Produits de crédit';

CREATE TABLE "adsys_etat_credits" (
	"id" int4 DEFAULT nextval('adsys_etat_credits_seq'::text) NOT NULL,
	"libel" text,
	"nbre_jours" smallint,
	"id_etat_prec" int4 ,
	"provisionne" boolean default false ,
	"taux" float ,
	"id_ag" int4 NOT NULL,
	PRIMARY KEY ("id","id_ag"),
	UNIQUE ("id_etat_prec","id_ag"),
	FOREIGN KEY (id_etat_prec,id_ag) REFERENCES "adsys_etat_credits"
);
COMMENT ON COLUMN "adsys_etat_credits"."id" IS 'Identifiant';
COMMENT ON COLUMN "adsys_etat_credits"."libel" IS 'Libellé de l''état';
COMMENT ON COLUMN "adsys_etat_credits"."nbre_jours" IS 'Nombre de jours de l''état';
COMMENT ON COLUMN "adsys_etat_credits"."id_etat_prec" IS 'ID de l''état précédent';

CREATE TABLE "ad_dcr" (
	"id_doss" int4 DEFAULT nextval('ad_dcr_id_doss_seq'::text) NOT NULL,
	"id_client" int4,
	"id_prod" int4 ,
	"date_dem" timestamp,
	"mnt_dem" numeric(30,6) DEFAULT 0,
	"obj_dem" int4,
	"detail_obj_dem" text,
	"etat" int4,
	"date_etat" timestamp,
	"motif" int4,
	"id_agent_gest" int4,
	"delai_grac" int4,
	"differe_jours" int4,
	"prelev_auto" bool,
	"duree_mois" int2,
	"nouv_duree_mois" int2,
	"terme" int4,
	"gar_num" numeric(30,6) DEFAULT 0,
	"gar_tot" numeric(30,6) DEFAULT 0,
	"gar_mat" numeric(30,6) DEFAULT 0,
    "gar_num_encours" numeric(30,6) DEFAULT 0,
	"cpt_gar_encours" integer ,
	"num_cre" int2,
    "assurances_cre" bool,
	"cpt_liaison" integer ,
	"cre_id_cpte" int4,
	"cre_etat" int4 ,
	"cre_date_etat" timestamp,
	"cre_date_approb" timestamp,
	"cre_date_debloc" timestamp,
	"cre_nbre_reech" int4,
	"cre_mnt_octr" numeric(30,6) DEFAULT 0,
	"cre_mnt_deb" numeric(30,6) DEFAULT 0,
	"details_motif" text,
	"suspension_pen" boolean,
	"perte_capital" numeric(30,6) DEFAULT 0,
	"cre_retard_etat_max" integer,
	"cre_retard_etat_max_jour" integer,
    "differe_ech" integer,
	"id_dcr_grp_sol" int4,
   "gs_cat" smallint,
   "id_ag" int4 NOT NULL,
   "prelev_commission" boolean DEFAULT 'false',
   "cre_prelev_frais_doss" boolean DEFAULT 'false',
   "cpt_prelev_frais" integer ,
   "prov_mnt" numeric(30,6) DEFAULT 0 ,
   "prov_date" date ,
   "prov_is_calcul" BOOLEAN DEFAULT 'TRUE' ,
   "doss_repris" boolean DEFAULT 'false',
   "cre_cpt_att_deb" int4,
	PRIMARY KEY ("id_doss","id_ag"),
	FOREIGN KEY (id_prod,id_ag) REFERENCES "adsys_produit_credit" ,
	FOREIGN KEY (cpt_gar_encours,id_ag) REFERENCES "ad_cpt" ,
	FOREIGN KEY (cpt_liaison,id_ag) REFERENCES "ad_cpt" ,
	FOREIGN KEY (cre_etat,id_ag) REFERENCES "adsys_etat_credits"
);
COMMENT ON COLUMN "ad_dcr"."cre_etat" IS 'Etat du crédit (sain, souffrance, perte). Ref table sys.';
COMMENT ON COLUMN "ad_dcr"."duree_mois" IS 'Durée du crédit en nombre de mois';
COMMENT ON COLUMN "ad_dcr"."gar_num" IS 'Montant de la garantie numéraire';
COMMENT ON COLUMN "ad_dcr"."cpt_gar_encours" IS 'Comptes des garanties numéraires à constituer ';
COMMENT ON COLUMN "ad_dcr"."gar_mat" IS 'Description de la garantie matérielle';
COMMENT ON COLUMN "ad_dcr"."cre_id_cpte" IS 'ID du compte débiteur de crédit';
COMMENT ON COLUMN "ad_dcr"."id_dcr_grp_sol" IS 'ID du dossier fictif du groupe solidaire';
COMMENT ON TABLE "ad_dcr" IS 'Dossiers de crédit et crédits. Les champs dont le nom est précédé de "cre_" ne sont renseignés que si le dossier a été accepté (il s''agit donc d''un crédit existant)';
CREATE TRIGGER trig_before_update_ad_dcr BEFORE UPDATE ON ad_dcr FOR EACH ROW EXECUTE PROCEDURE trig_insert_ad_dcr_hist();

CREATE TABLE "ad_dcr_grp_sol" (
	"id" int4 DEFAULT nextval('ad_dcr_grp_sol_id_seq'::text) NOT NULL,
	    "id_dcr_grp_sol" int4 ,
        "id_membre" int4 NOT NULL,
        "obj_dem" int4,
        "detail_obj_dem" text,
        "mnt_dem" numeric(30,6),
	"gs_cat" int4,
	"id_ag" int4 NOT NULL,
	PRIMARY KEY ("id","id_ag"),
	FOREIGN KEY (id_dcr_grp_sol,id_ag) REFERENCES "ad_dcr" ,
	FOREIGN KEY (id_membre,id_ag) REFERENCES "ad_cli"
);

CREATE TABLE "ad_etr" (
	"id_doss" int4 NOT NULL ,
	"id_ech" int4 NOT NULL,
	"date_ech" timestamp,
	"mnt_cap" numeric(30,6) DEFAULT 0,
	"mnt_int" numeric(30,6) DEFAULT 0,
        "mnt_gar" numeric(30,6) DEFAULT 0,
	"mnt_reech" numeric(30,6) DEFAULT 0,
	"remb" bool,
	"solde_cap" numeric(30,6) DEFAULT 0,
	"solde_int" numeric(30,6) DEFAULT 0,
        "solde_gar" numeric(30,6) DEFAULT 0,
	"solde_pen" numeric(30,6) DEFAULT 0,
	"id_ag" int4 NOT NULL,
	PRIMARY KEY ("id_doss", "id_ech","id_ag"),
	FOREIGN KEY (id_doss,id_ag) REFERENCES "ad_dcr"
);
COMMENT ON COLUMN "ad_etr"."remb" IS 'L''échéance a-t-elle été remboursée entièrement ?';
COMMENT ON COLUMN "ad_etr"."mnt_reech" IS 'Ce champs contient éventuellement un montant qui était dÃ» pour cette échéance mais dont le remboursement a été repris dans les échéances ultérieures suites à un rééchelonnement';
COMMENT ON TABLE "ad_etr" IS 'Echéanciers théoriques';

CREATE TABLE "ad_sre" (
	"id_doss" int4 NOT NULL,
	"id_ech" int4,
	"num_remb" int4 NOT NULL,
	"date_remb" timestamp,
	"mnt_remb_cap" numeric(30,6) DEFAULT 0,
	"mnt_remb_int" numeric(30,6) DEFAULT 0,
        "mnt_remb_gar" numeric(30,6) DEFAULT 0,
	"mnt_remb_pen" numeric(30,6) DEFAULT 0,
	"id_ag" int4 NOT NULL,
	PRIMARY KEY ("id_doss", "num_remb", "id_ech","id_ag"),
	FOREIGN KEY (id_doss,id_ag) REFERENCES "ad_dcr"
);
COMMENT ON COLUMN "ad_sre"."id_ech" IS 'Echéance (théorique) auquel se rapporte le remboursement';
COMMENT ON TABLE "ad_sre" IS 'Suivis de remboursements';

CREATE TABLE "ad_los" (
	"id_los" int4 DEFAULT nextval('ad_los_id_los_seq'::text) NOT NULL,
	"date" timestamp,
	"description" text,
	"login" text,
	"adr_res" text,
	PRIMARY KEY ("id_los")
);
COMMENT ON COLUMN "ad_los"."date" IS 'Date et heure à laquelle a eu lieu l''évènement';
COMMENT ON COLUMN "ad_los"."login" IS 'Login qui a réalisé l''évènement';
COMMENT ON COLUMN "ad_los"."adr_res" IS 'Adresse réseau de la machine sur laquelle a eu lieu l''évènement';
COMMENT ON COLUMN "ad_los"."description" IS 'Description de l''évènement';
COMMENT ON TABLE "ad_los" IS 'Logs système';

CREATE TABLE "ad_uti" (
	"id_utilis" int4 DEFAULT nextval('ad_uti_id_utilis_seq'::text) NOT NULL,
	"nom" text,
	"prenom" text,
	"date_naiss" timestamp,
	"lieu_naiss" text,
	"sexe" int4,
	"type_piece_id" int4,
	"num_piece_id" text,
	"adresse" text,
	"tel" text,
	"date_crea" timestamp,
	"utilis_crea" int4,
	"date_modif" timestamp,
	"utilis_modif" int4,
	"statut" int4,
	"is_gestionnaire" boolean DEFAULT 'false',
	"id_ag" int4 NOT NULL,
	PRIMARY KEY ("id_utilis","id_ag")
);
COMMENT ON COLUMN "ad_uti"."statut" IS 'Statut de l''utilisateur (actif, inactif, supprimé)';
COMMENT ON TABLE "ad_uti" IS 'Utilisateurs';

CREATE TABLE "ad_log" (
	"login" text NOT NULL,
	"pwd" text,
	"profil" int4,
	"guichet" int4,
	"id_utilisateur" int4,
	"have_left_frame" bool,
	"billet_req" bool,
     "langue" text NOT NULL,
  "date_mod_pwd" date,
  "pwd_non_expire" boolean DEFAULT false,
  "depasse_plafond_retrait" bool,
  "depasse_plafond_depot" bool,
  "id_ag" int4 NOT NULL,
	PRIMARY KEY ("login","id_ag"),
	FOREIGN KEY (langue) REFERENCES "adsys_langues_systeme"
);
COMMENT ON TABLE "ad_log" IS 'Logins, chaque login a un profil et peut avoir 0 ou 1 guichet';

CREATE TABLE "ad_gui" (
	"id_gui" int4 DEFAULT nextval('ad_gui_id_gui_seq'::text) NOT NULL,
	"libel_gui" text,
	"date_crea" timestamp,
	"utilis_crea" int4,
	"ouvert" bool,
	"last_num_recu" integer DEFAULT 0,
	"date_modif" timestamp,
	"utilis_modif" int4,
	"cpte_cpta_gui" text NOT NULL,
	"id_ag" int4 NOT NULL,
	PRIMARY KEY ("id_gui","id_ag"),
	UNIQUE ("cpte_cpta_gui","id_ag"),
	FOREIGN KEY (cpte_cpta_gui,id_ag) REFERENCES "ad_cpt_comptable"
);
COMMENT ON TABLE "ad_gui" IS 'Guichets';
COMMENT ON COLUMN "ad_gui"."cpte_cpta_gui" IS 'On lie obligatoirement un compte comptable à cet objet interne';

CREATE TABLE "tableliste" (
    "ident" int4 DEFAULT nextval('tableliste_ident_seq'::text) NOT NULL PRIMARY KEY,
    "nomc" text,
    "noml" integer REFERENCES ad_str(id_str) ON DELETE CASCADE,
    "is_table" bool
);

COMMENT ON COLUMN "tableliste"."nomc" IS 'Nom court de la table';
COMMENT ON COLUMN "tableliste"."noml" IS 'Nom long de la table';
COMMENT ON TABLE "tableliste" IS 'Liste des tables';

CREATE TABLE "d_tableliste" (
    "ident" int4 DEFAULT nextval('d_tableliste_ident_seq'::text) NOT NULL PRIMARY KEY,
    "tablen" int4 REFERENCES tableliste(ident) ON DELETE RESTRICT,
    "nchmpc" text NOT NULL,
    "nchmpl" integer REFERENCES ad_str(id_str) ON DELETE CASCADE,
    "isreq" bool,
    "ref_field" int4,
    "type" text,
    "onslct" bool,
    "ispkey" bool,
    "traduit" bool DEFAULT 'f',
	UNIQUE ("tablen", "nchmpc")
);

COMMENT ON COLUMN "d_tableliste"."tablen" IS 'Nom (court) de la table auquel appartient le champ';
COMMENT ON COLUMN "d_tableliste"."nchmpc" IS 'Nom (court) du champ';
COMMENT ON COLUMN "d_tableliste"."nchmpl" IS 'Nom (long) du champ';
COMMENT ON COLUMN "d_tableliste"."isreq" IS 'Obligatoire';
COMMENT ON COLUMN "d_tableliste"."ref_field" IS 'N° du champ qu''il référence. S''il ne référence aucun champ il vaut NULL.';
COMMENT ON COLUMN "d_tableliste"."type" IS 'Type du champ (entier, date, texte, etc.)';
COMMENT ON COLUMN "d_tableliste"."onslct" IS 'Ce champ fait-il partie des champs "représentatifs" de la table?';
COMMENT ON TABLE "d_tableliste" IS 'Description des tables: chaque champ de chaque table non-système est décrite ici';

CREATE TABLE "ad_fer" (
	"id_fer" int4 DEFAULT nextval('ad_fer_id_fer_seq'::text) NOT NULL,
	"jour_semaine" int4,
	"date_jour" int4,
	"date_mois" int4,
	"date_annee" int4,
	"id_ag" int4 NOT NULL,
	PRIMARY KEY ("id_fer","id_ag")
);
COMMENT ON COLUMN "ad_fer"."jour_semaine" IS 'Jour de la semaine (1 = lundi, 7 = dimanche, 0 = non-renseigné)';
COMMENT ON TABLE "ad_fer" IS 'Jours feriés';

CREATE TABLE "ad_agc" (
	"id_ag" int4 DEFAULT nextval('ad_agc_id_ag_seq'::text) NOT NULL,
	"code_institution" text,
	"last_date" timestamp,
	"last_batch" timestamp,
	"last_prelev_frais_tenue"  timestamp,
	"statut" int4,
	"report_ferie" int4,
	"duree_min_avant_octr_credit" int2,
	"octroi_credit_non_soc" boolean,
	"id_prod_cpte_base" int4,
	"id_prod_cpte_parts_sociales" int4,
	"id_prod_cpte_credit" int4,
	"id_prod_cpte_epargne_nantie" int4,
	"val_nominale_part_sociale" numeric(30,6) DEFAULT 0,
	"nbre_part_sociale" int4 DEFAULT 0,
	"nbre_part_social_max_cli" int4 DEFAULT 0,
	"pp_montant_droits_adhesion" numeric(30,6) DEFAULT 0,
	"pm_montant_droits_adhesion" numeric(30,6) DEFAULT 0,
	"gi_montant_droits_adhesion" numeric(30,6) DEFAULT 0,
	"gs_montant_droits_adhesion" numeric(30,6) DEFAULT 0,
	"libel_ag" text,
	"libel_institution" text,
	"base_taux" int4,
	"base_taux_epargne" int4,
	"exercice" int4 ,
	"adresse" text,
	"tel" text,
	"fax" text,
	"email" text,
	"responsable" text,
	"type_structure" int4,
	"date_agrement" timestamp,
	"num_agrement" text,
	"num_tva" text,
	"affil_reseau" text,
	"delai_max_eav"	int2,
	"alerte_dat_jours" int2,
	"cloture_per_auto" boolean,
	"frequence_clot_per" integer DEFAULT 0,
	"num_cpte_resultat" text ,
	"prc_com_vir" double precision,
	"mnt_com_vir" numeric(30,6),
	"cpte_cpta_coffre" text ,
        "code_devise_reference" char(3),
        "prc_comm_change" double precision,
        "mnt_min_comm_change" numeric(30,6),
        "constante_comm_change" integer DEFAULT 0,
        "comm_dev_ref" BOOLEAN,
        "prc_tax_change" double precision,
        "cpte_position_change" text ,
        "cpte_contreval_position_change" text ,
        "cpte_variation_taux_deb" text ,
        "cpte_variation_taux_cred" text ,
        "num_cpte_tch" text ,
	"type_numerotation_compte" int4,
	"code_ville" text DEFAULT '0'::text,
	"code_banque" text DEFAULT '0'::text,
	"code_swift_banque" char(11),
        "langue_systeme_dft" text ,
        "licence" oid,
        "clients_actifs" int4,
        "total_clients" int4,
        "code_antenne" text DEFAULT NULL,
        "tranche_part_sociale" BOOLEAN DEFAULT 'false',
        "siege" BOOLEAN DEFAULT 'false',
        "tranche_frais_adhesion" BOOLEAN DEFAULT 'false',
        "num_seq_auto" bigint DEFAULT 0,
        "paiement_parts_soc_gs" BOOLEAN DEFAULT 'false',
        "passage_perte_automatique" BOOLEAN DEFAULT 'true',
        "imprim_coordonnee" BOOLEAN DEFAULT 'false',
        "nbre_car_min_pwd" int4,
        "duree_pwd" int4,
        "plafond_retrait_guichet" BOOLEAN DEFAULT 'false',
        "plafond_depot_guichet" BOOLEAN DEFAULT 'false',
        "montant_plafond_retrait" numeric(30,6) DEFAULT 0,
        "montant_plafond_depot" numeric(30,6) DEFAULT 0,
        "utilise_netbank" BOOLEAN DEFAULT 'false',
        "imprimante_matricielle" BOOLEAN DEFAULT 'false',
        "realisation_garantie_sain" BOOLEAN DEFAULT 'false',
        "provision_credit_auto" BOOLEAN DEFAULT 'FALSE' ,
        "nb_group_for_cust" int4 DEFAULT 0,
       	"cpte_tva_dec" text ,
     	"cpte_tva_rep" text ,
		"calcul_penalites_credit_radie" BOOLEAN DEFAULT 'false' ,
		"licence_jours_alerte" integer NOT NULL DEFAULT 30,
		"licence_code_identifier" text DEFAULT NULL,
        PRIMARY KEY ("id_ag"),
        FOREIGN KEY (exercice,id_ag) REFERENCES "ad_exercices_compta" ,
        FOREIGN KEY (num_cpte_resultat,id_ag) REFERENCES "ad_cpt_comptable" ,
        FOREIGN KEY (cpte_cpta_coffre,id_ag) REFERENCES "ad_cpt_comptable" ,
        FOREIGN KEY (cpte_position_change,id_ag) REFERENCES "ad_cpt_comptable" ,
        FOREIGN KEY (cpte_contreval_position_change,id_ag) REFERENCES "ad_cpt_comptable" ,
        FOREIGN KEY (cpte_variation_taux_deb,id_ag) REFERENCES "ad_cpt_comptable" ,
        FOREIGN KEY (cpte_variation_taux_cred,id_ag) REFERENCES "ad_cpt_comptable" ,
        FOREIGN KEY (num_cpte_tch,id_ag) REFERENCES "ad_cpt_comptable" ,
        FOREIGN KEY (langue_systeme_dft) REFERENCES "adsys_langues_systeme"

);
COMMENT ON TABLE "ad_agc" IS 'Agences';
COMMENT ON COLUMN "ad_agc"."last_date" IS 'Date de la dernière ouverture de l''agence';
COMMENT ON COLUMN "ad_agc"."report_ferie" IS 'Ce nombre indique de combien de jours ouvrables il faut reporter une échéance quand elle tombe un jour ferié. Ce nombre peut être positif ou négatif. p.ex. -1 indique au système de reporter l échéance au 1er jour ouvrable précédent le jour ferié.';
COMMENT ON COLUMN "ad_agc"."statut" IS 'Statut de l''agence : ouvert, fermé, batch1, batch2, etc... (ref. table système)';
COMMENT ON COLUMN "ad_agc"."exercice" IS 'On mémorise l''exercice en cours pour les écritures automatiques ';
COMMENT ON COLUMN "ad_agc"."utilise_netbank" IS 'pour dire si l''export netbank est utilisé par l''agence ';
COMMENT ON COLUMN "ad_agc"."nb_group_for_cust" IS 'pour fixer le nombre maximum de groupes solidaires auxquels un client peut appartenir ';

CREATE TRIGGER ad_agc_passage_perte_automatique AFTER UPDATE on ad_agc FOR EACH ROW EXECUTE PROCEDURE  proc_ad_agc_passage_perte_automatique();

CREATE TABLE "ad_ses" (
    "id_sess" text,
    "login" text NOT NULL,
    "adr_ip" text NOT NULL,
    "date_creation" timestamp,
    "last_access" timestamp,
    "sess_status" int2,
    PRIMARY KEY("id_sess")
);
COMMENT ON COLUMN "ad_ses"."id_sess" IS 'Clé de la session (généré par PHP)';
COMMENT ON COLUMN "ad_ses"."login" IS 'Login';
COMMENT ON COLUMN "ad_ses"."adr_ip" IS 'Adresse réseau (IP) de la machine client';
COMMENT ON COLUMN "ad_ses"."date_creation" IS 'Date de création de la session';
COMMENT ON TABLE "ad_ses" IS 'Sessions actives';

CREATE TABLE "menus" (
    "nom_menu" text NOT NULL PRIMARY KEY,
    "libel_menu" int REFERENCES ad_str(id_str) ON DELETE CASCADE,
    "nom_pere" text,
    "pos_hierarch" int2,
    "ordre" int2,
    "is_menu" bool,
    "fonction" int2,
    "is_cliquable" boolean DEFAULT 'true'
);

COMMENT ON COLUMN "menus"."libel_menu" IS 'Libellé affiché dans le frame gauche pour cette entrée';
COMMENT ON COLUMN "menus"."nom_pere" IS 'Ecran Pere (hierarchiquement parlant)';
COMMENT ON COLUMN "menus"."pos_hierarch" IS 'Position hierarchique (la racine est 1)';
COMMENT ON COLUMN "menus"."ordre" IS 'Ordre (position) de l''entrée parmi tous ceux ayant le même père';
COMMENT ON COLUMN "menus"."is_menu" IS 'L''entrée appartient-elle aux choix d''un menu?';
COMMENT ON COLUMN "menus"."fonction" IS 'Si cette entrée du menu n''est pas rattachée à un écran on peut (optionnel) définir un n° de fonction auquel il faut avoir accès pour afficher ce menu';
COMMENT ON TABLE "menus" IS 'Menus (frame gauche)';

CREATE TABLE "ecrans" (
	"nom_ecran" text NOT NULL,
	"fichier" text,
	"nom_menu" text ,
	"fonction" int4,
	PRIMARY KEY ("nom_ecran"),
	FOREIGN KEY (nom_menu) REFERENCES "menus"
);
COMMENT ON COLUMN "ecrans"."nom_ecran" IS 'Nom de l''écran tel que définit par le document de description des interfaces';
COMMENT ON COLUMN "ecrans"."fichier" IS 'Fichier correspondant à l''écran';
COMMENT ON COLUMN "ecrans"."fonction" IS 'Fonction à laquelle est rattaché l''écran';
COMMENT ON TABLE "ecrans" IS 'Ecrans de l''application';

CREATE TABLE "adsys_rejet_pret" (
	"id" integer DEFAULT nextval('"adsys_rejet_pret_id_seq"'::text) NOT NULL,
	"libel" text,
	"id_ag" int4 NOT NULL,
	Constraint "adsys_rejet_pret_pkey" Primary Key ("id","id_ag")
);
COMMENT ON TABLE "adsys_rejet_pret" IS 'Causes de rejet d''un prêt';

CREATE TABLE "adsys_localisation" (
	"id" integer DEFAULT nextval('"adsys_localisation_id_seq"'::text) NOT NULL,
	"libel" text,
	"parent" int4,
	"id_ag" int4 NOT NULL,
	Constraint "adsys_localisation_pkey" Primary Key ("id","id_ag")
);
COMMENT ON TABLE "adsys_localisation" IS 'Localisations';

CREATE TABLE "adsys_sect_activite" (
	"id" integer DEFAULT nextval('"adsys_sect_activite_id_seq"'::text) NOT NULL,
	"libel" text,
	"id_ag" int4 NOT NULL,
	Constraint "adsys_sect_activite_pkey" Primary Key ("id","id_ag")
);
COMMENT ON TABLE "adsys_sect_activite" IS 'Secteurs d''activité';

CREATE TABLE "adsys_langue" (
	"id" integer DEFAULT nextval('"adsys_langue_id_seq"'::text) NOT NULL,
	"libel" text,
	"id_ag" int4 NOT NULL,
	Constraint "adsys_langue_pkey" Primary Key ("id","id_ag")
);
COMMENT ON TABLE "adsys_langue" IS 'Langues';

CREATE TABLE "adsys_profils" (
	"id" integer DEFAULT nextval('"adsys_profils_id_seq"'::text) NOT NULL,
	"libel" text,
	"guichet" bool,
	"timeout" int4,
	"access_solde" bool default true,
	"access_solde_vip" boolean DEFAULT false,
	Constraint "adsys_profils_pkey" Primary Key ("id")
);
COMMENT ON COLUMN "adsys_profils"."guichet" IS 'Le profile possède-t-il un guichet associé ?';
COMMENT ON TABLE "adsys_profils" IS 'Profils utilisateurs';

CREATE TABLE "adsys_profils_axs" (
	"id" integer DEFAULT nextval('"adsys_profils_axs_id_seq"'::text) NOT NULL,
	"profil" integer,
	"fonction" integer,
	Constraint "adsys_profils_axs_pkey" Primary Key ("id")
);
COMMENT ON TABLE "adsys_profils_axs" IS 'Autorisations d''accés à des profils utilisateurs';

CREATE TABLE "adsys_types_billets" (
	"id" integer DEFAULT nextval('"adsys_types_billets_id_seq"'::text)  NOT NULL,
	 "valeur" numeric(30,6),
   	 "devise" char(3) ,
   	 "id_ag" int4 NOT NULL,
	Constraint "adsys_types_billets_pkey" Primary Key ("id","id_ag"),
	FOREIGN KEY (devise,id_ag) REFERENCES "devise"
);
COMMENT ON TABLE "adsys_types_billets" IS 'Billets existant';

CREATE TABLE "ad_rapports" (
	"id" int4 DEFAULT nextval('id_rapports'::text) NOT NULL,
	"login" text,
	"date" timestamp,
	"type" int4,
	"id_ag" int4 NOT NULL,
	PRIMARY KEY ("id","id_ag")
);

CREATE TABLE "adsys_objets_credits" (
	"id" int4 DEFAULT nextval('id_objets_credits'::text) NOT NULL,
	"libel" text,
	"id_ag" int4 NOT NULL,
	PRIMARY KEY ("id","id_ag")
);

CREATE TABLE "ad_cpt_ope" (
	"type_operation" int4 NOT NULL,
	"libel_ope" integer,
	"categorie_ope" int4 NOT NULL,
	"id_ag" int4 NOT NULL,
	PRIMARY KEY ("type_operation","id_ag"),
	CONSTRAINT ad_cpt_ope_trad_fkey FOREIGN KEY (libel_ope) REFERENCES ad_str(id_str) ON DELETE CASCADE
);

CREATE TABLE "ad_cpt_ope_cptes" (
	"type_operation" int4 NOT NULL ,
	"num_cpte" text ,
	"sens" text NOT NULL,
	"categorie_cpte" int4 NOT NULL,
	"id_ag" int4 NOT NULL,
	PRIMARY KEY ("type_operation","categorie_cpte","sens","id_ag"),
	FOREIGN KEY (type_operation,id_ag) REFERENCES "ad_cpt_ope" ,
	FOREIGN KEY (num_cpte,id_ag) REFERENCES "ad_cpt_comptable"
);

CREATE TABLE "ad_brouillard" (
	"id_mouvement" int4 DEFAULT nextval('ad_brouillard_seq'::text) NOT NULL,
	"id_his" int4 NOT NULL,
	"id" int4 NOT NULL,
	"compte" text  NOT NULL,
	"cpte_interne_cli" int4 ,
	"sens" text NOT NULL,
	"montant" numeric(30,6) NOT NULL,
        "devise" char(3) NOT NULL ,
	"date_comptable" date,
	"libel_ecriture" integer,
	"id_jou" int4 ,
	"id_exo" int4 ,
	"id_ag" int4 NOT NULL,
	"type_operation" int4  DEFAULT 0,
	"id_taxe" int4,
	"sens_taxe" text,
	PRIMARY KEY ("id_mouvement","id_ag"),
	UNIQUE ("id_his","compte","id","sens","id_ag"),
	FOREIGN KEY (compte,id_ag) REFERENCES "ad_cpt_comptable" ,
	FOREIGN KEY (cpte_interne_cli,id_ag) REFERENCES "ad_cpt" ,
	FOREIGN KEY (devise,id_ag) REFERENCES "devise" ,
	FOREIGN KEY (id_jou,id_ag) REFERENCES "ad_journaux" ,
	FOREIGN KEY (id_exo,id_ag) REFERENCES "ad_exercices_compta"
);

CREATE TABLE "adsys_correspondant" (
       "id" int4 DEFAULT nextval('adsys_correspondant_seq'::text) NOT NULL,
       "id_banque" int4 ,
       "numero_cpte" text,
       "numero_iban" text,
	"cpte_bqe" text ,
	"cpte_ordre_deb" text ,
	"cpte_ordre_cred" text ,
	"id_ag" int4 NOT NULL,
       PRIMARY KEY ("id","id_ag"),
       FOREIGN KEY (id_banque,id_ag) REFERENCES "adsys_banque" ,
       FOREIGN KEY (cpte_bqe,id_ag) REFERENCES "ad_cpt_comptable" ,
       FOREIGN KEY (cpte_ordre_deb,id_ag) REFERENCES "ad_cpt_comptable" ,
       FOREIGN KEY (cpte_ordre_cred,id_ag) REFERENCES "ad_cpt_comptable"
);

CREATE TABLE "ad_ecriture" (
        "id_ecriture" int4 DEFAULT nextval('ad_ecriture_seq'::text) NOT NULL,
        "id_his" int4 NOT NULL ,
        "date_comptable" date,
        "libel_ecriture" integer,
        "id_jou" int4 NOT NULL ,
        "id_exo" int4 NOT NULL ,
        "type_operation" int4 ,
	"ref_ecriture" text,
	 "id_ag" int4 NOT NULL,
	 "info_ecriture" text,
        PRIMARY KEY ("id_ecriture","id_ag"),
         UNIQUE (ref_ecriture,id_ag),
         FOREIGN KEY (id_his,id_ag) REFERENCES "ad_his" ,
         FOREIGN KEY (id_jou,id_ag) REFERENCES "ad_journaux" ,
         FOREIGN KEY (id_exo,id_ag) REFERENCES "ad_exercices_compta"
        );
COMMENT ON COLUMN "ad_ecriture"."id_his" IS 'Numéro reliant l''opération comptable à un évènement dans la table historique (ad_his); un enregistrement de la table historique peut être reliés à plusieurs enregistrements dans la table historique_ecriture. Ce champ n''est donc pas une clé primaire.';
COMMENT ON TABLE "ad_ecriture" IS 'Table Ecriture Comptable : contient les informations sur toutes les opérations comptables (débit/crédit). Une opération est sub-divisée sur une entrées de la table mais ceux-ci sont reliés par un même numéro "id_ecriture" qui est lié dans ad_mouvement';
COMMENT ON COLUMN "ad_ecriture"."date_comptable" IS 'Cette date représente la date de valeur de l''écriture comptable. Pour la date de saisie on se réfère à ladate dans ad_his';

CREATE TABLE "ad_mouvement" (
        "id_mouvement" int4 DEFAULT nextval('ad_mouvement_seq'::text) NOT NULL,
        "id_ecriture" int4 ,
        "compte" text NOT NULL ,
        "cpte_interne_cli" int4 ,
        "sens" text NOT NULL,
        "montant" numeric(30,6) NOT NULL,
        "devise" char(3) NOT NULL ,
        "date_valeur" date NOT NULL,
        "id_ag" int4 NOT NULL,
        "consolide" boolean default false,
        PRIMARY KEY ("id_mouvement","id_ag"),
        FOREIGN KEY (id_ecriture,id_ag) REFERENCES "ad_ecriture" ,
        FOREIGN KEY (compte,id_ag) REFERENCES "ad_cpt_comptable" ,
        FOREIGN KEY (cpte_interne_cli,id_ag) REFERENCES "ad_cpt" ,
        FOREIGN KEY (devise,id_ag) REFERENCES "devise"
        );
COMMENT ON COLUMN "ad_mouvement"."id_ecriture" IS 'Numéro reliant l''opération comptable à un évènement dans la table ecriture (ad_ecriture); un enregistrementde la table ecriture peut être reliés à plusieurs enregistrements dans la table mouvement. Ce champ n''est donc pas une clé primaire.';
COMMENT ON COLUMN "ad_mouvement"."sens" IS 'Sens de l''opération ("c" pour crédit et "d" pour débit)';
COMMENT ON COLUMN "ad_mouvement"."consolide" IS 'Identificateur des mouvements réciproques déjà annulés à la consolidation';
COMMENT ON TABLE "ad_mouvement" IS 'Table mouvement : contient toutes les opérations comptables (débit/crédit). Une opération est sub-divisée sur plusieurs entrées de la table mais ceux-ci sont reliés par un même numéro "id_mouvement" qui les lie à une même entrée de la table ecriture';

CREATE TABLE "ad_traductions" (
        "id_str"        integer NOT NULL REFERENCES ad_str(id_str) ON DELETE CASCADE,
        "langue"        text NOT NULL REFERENCES adsys_langues_systeme(code) ON DELETE CASCADE,
        "traduction"    text NOT NULL,
        PRIMARY KEY (id_str, langue)
);

CREATE TABLE "adsys_etat_credit_cptes" (
	"id_etat_credit" int4,
	 "num_cpte_comptable" text,
	"id_prod_cre" int4,
	"cpte_provision_credit" text ,
	"cpte_provision_debit" text,
	"cpte_reprise_prov" text,
	"id_ag" int4 NOT NULL,
	PRIMARY KEY ("id_etat_credit", "id_prod_cre","id_ag")
);
COMMENT ON COLUMN "adsys_etat_credit_cptes"."id_etat_credit" IS 'ID de l''état du crédit';
COMMENT ON COLUMN "adsys_etat_credit_cptes"."num_cpte_comptable" IS 'Numéro de compte associé';
COMMENT ON COLUMN "adsys_etat_credit_cptes"."id_prod_cre" IS 'ID du produit de crédit';

CREATE TABLE "attentes" (
       "id" serial  NOT NULL,
       "id_correspondant" integer ,
       "id_ext_benef" integer ,
       "id_cpt_benef" integer ,
       "id_ext_ordre" integer ,
       "id_cpt_ordre" integer ,
       "sens" char(3),
       "type_piece" smallint,
       "num_piece" text,
       "date_piece" date,
       "date" date,
       "montant" double precision,
       "devise" char(3) ,
       "etat" smallint,
       "id_banque" integer ,
       "remarque" text,
       "communication" text,
       "id_ag" int4 NOT NULL,
       PRIMARY KEY("id","id_ag"),
       FOREIGN KEY (id_correspondant,id_ag) REFERENCES "adsys_correspondant" ,
       FOREIGN KEY (id_ext_benef,id_ag) REFERENCES "tireur_benef" ,
       FOREIGN KEY (id_cpt_benef,id_ag) REFERENCES "ad_cpt" ,
       FOREIGN KEY (id_ext_ordre,id_ag) REFERENCES "tireur_benef" ,
       FOREIGN KEY (id_cpt_ordre,id_ag) REFERENCES "ad_cpt" ,
       FOREIGN KEY (devise,id_ag) REFERENCES "devise" ,
       FOREIGN KEY (id_banque,id_ag) REFERENCES "adsys_banque"
   );

CREATE TABLE "swift_op_domestiques" (
       	"id_message" serial NOT NULL,
       	"nom_fichier" text,
       	"corps_messsage" text,
	"statut" smallint,
	"message_erreur" text,
	"code_swift_em" char(11),
	"num_session" integer,
	"num_sequence" integer,
	"code_swift_re" char(11),
	"ref_tech1" char(16),
	"ref_client" char(8),
	"heure_crea" char(4),
	"staut_paiement" char(3),
	"objet_paiement" char(3),
	"date_memo" char(8),
	"devise" char(3),
	"montant" numeric(30,6),
	"pays_do" char(2),
	"format_num_cpte_do" char(3),
	"devise_cpte_do" char(3),
	"num_cpte_do" char(13),
	"nom_do" char(35),
	"adresse_do_1" char(35),
	"adresse_do_2" char(35),
	"code_postal_do" char(6),
 	"ville_do" char(24),
	"pays_ben" char(2),
	"format_num_cpte_ben" char(4),
	"num_cpte_ben" char(36),
	"nom_ben" char(35),
	"adresse_ben_1" char(35),
	"adresse_ben_2" char(35),
	"code_postal_ben" char(6),
 	"ville_ben" char(24),
	"type_comm" char(4),
	"comm_1" char(53),
	"comm_2" char(53),
	"ref_tech2" char(16),
	"id_ag" int4 NOT NULL,
	PRIMARY KEY("id_message","id_ag")
    );

CREATE TABLE "swift_op_etrangers" (
       	"id_message" serial  NOT NULL,
       	"nom_fichier" text,
       	"corps_message" text,
	"statut" smallint,
	"message_erreur" text,
	"code_swift_em" char(11),
	"num_session" integer,
	"num_sequence" integer,
	"code_swift_re" char(11),
	"ref_tech1" char(16),
	"ref_client" char(8),
	"heure_crea" char(4),
	"staut_paiement" char(3),
	"code_instruction" char(4),
	"code_iblc" char(3),
	"devise_iblc" char(3),
	"sous_montant_iblc" float,
	"type_date" char(6),
	"date" char(8),
	"devise" char(3),
	"montant" numeric(30,6),
	"devise2" char(3),
	"montant2" numeric(30,6),
	"taux_change" float,
	"pays_cpt_do" char(2),
	"format_num_cpte_do" char(3),
	"devise_cpte_do" char(3),
	"num_cpte_do" char(13),
	"nom_do" char(35),
	"adresse_do_1" char(35),
	"adresse_do_2" char(35),
	"code_postal_do" char(6),
 	"ville_do" char(24),
	"pays_do" char(2),
	"code_swift_bq_interm" char(11),
	"code_swift_bq_ben" char(11),
	"code_postal_bq_ben" char(6),
 	"ville_bq_ben" char(20),
	"pays_bq_ben" char(2),
	"nom_bq_ben" char(25),
	"adresse_bq_ben_1" char(35),
	"adresse_bq_ben_2" char(35),
	"pays_cpt_ben" char(2),
	"format_num_cpte_ben" char(4),
	"num_cpte_ben" char(36),
	"nom_ben" char(35),
	"adresse_ben_1" char(35),
	"adresse_ben_2" char(35),
	"code_postal_ben" char(6),
 	"ville_ben" char(24),
	"pays_ben" char(2),
	"pays_cpt_ben2" char(2),
	"format_num_cpte_ben2" char(4),
	"num_cpte_ben2" char(36),
	"code_swift_bq_ben2" char(11),
	"mode_paiement" char(3),
	"code_charges" char(3),
	"pays_cpt_charges" char(2),
	"format_cpt_charges" char(3),
	"devise_cpt_charges" char(3),
	"num_cpte_charges" char(13),
	"com_bq_ben" char(4),
	"com_bq_do" char(70),
	"comm_1" char(140),
	"ref_tech2" char(9),
	"id_ag" int4 NOT NULL,
	PRIMARY KEY("id_message","id_ag")
    );

CREATE TABLE "adsys_types_biens" (
	"id" int4 DEFAULT nextval('adsys_types_biens_id_seq'::text) NOT NULL,
	"libel" text NOT NULL,
	"id_ag" int4 NOT NULL,
	PRIMARY KEY("id","id_ag")
);
COMMENT ON TABLE "adsys_types_biens" IS 'Cette table permet de définir les types de biens matériels qui sont acceptés par l''IM';
COMMENT ON COLUMN "adsys_types_biens"."id" IS 'Identifiant du type de bien';
COMMENT ON COLUMN "adsys_types_biens"."libel" IS 'le libellé du type de bien ';

CREATE TABLE "ad_biens" (
	"id_bien" int4 DEFAULT nextval('ad_biens_id_bien_seq'::text) NOT NULL,
	"id_client" int4  NOT NULL,
	"type_bien" int4  NOT NULL,
	"description" text NOT NULL,
	"valeur_estimee" numeric(30,6) NOT NULL DEFAULT 0,
	"devise_valeur" char(3)  NOT NULL,
	"piece_just" text,
	"remarque" text,
	"id_ag" int4 NOT NULL,
	PRIMARY KEY("id_bien","id_ag"),
	FOREIGN KEY (id_client,id_ag) REFERENCES "ad_cli" ,
	FOREIGN KEY (type_bien,id_ag) REFERENCES "adsys_types_biens" ,
	FOREIGN KEY (devise_valeur,id_ag) REFERENCES "devise"
);
COMMENT ON TABLE "ad_biens" IS 'Cette table contient tous les biens que les clients possèdent. Ces bien seront utilisés à la fois pour évaluer le patrimoine d''un client, et pour évaluer la garantie matérielle déposée par un client.';
COMMENT ON COLUMN "ad_biens"."id_bien" IS 'Identifiant du bien';
COMMENT ON COLUMN "ad_biens"."id_client" IS 'Identificateur du client auquel appartient le bien ';
COMMENT ON COLUMN "ad_biens"."type_bien" IS 'Le type de bien ';
COMMENT ON COLUMN "ad_biens"."description" IS 'La description du bien';
COMMENT ON COLUMN "ad_biens"."valeur_estimee" IS 'La valeur estimée du bien';
COMMENT ON COLUMN "ad_biens"."devise_valeur" IS 'La devise de la valeur du bien';
COMMENT ON COLUMN "ad_biens"."piece_just" IS 'Référence de la pièce justificative attestant de l''existance du bien';
COMMENT ON COLUMN "ad_biens"."remarque" IS 'La remarque concernant le bien ';

CREATE TABLE "ad_gar" (
	"id_gar" int4 DEFAULT nextval('ad_gar_id_gar_seq'::text) NOT NULL,
	"id_doss" int4  NOT NULL,
	"type_gar" int4 NOT NULL,
	"gar_mat_id_bien" int4 ,
	"gar_num_id_cpte_prelev" int4 ,
	"gar_num_id_cpte_nantie" int4 ,
	"etat_gar" int4 NOT NULL,
	"montant_vente" numeric(30,6),
	"devise_vente" char(3) ,
	"id_ag" int4 NOT NULL,
	PRIMARY KEY("id_gar","id_ag"),
	FOREIGN KEY (id_doss,id_ag) REFERENCES "ad_dcr" ,
	FOREIGN KEY (gar_mat_id_bien,id_ag) REFERENCES "ad_biens" ,
	FOREIGN KEY (gar_num_id_cpte_prelev,id_ag) REFERENCES "ad_cpt" ,
	FOREIGN KEY (gar_num_id_cpte_nantie,id_ag) REFERENCES "ad_cpt" ,
	FOREIGN KEY (devise_vente,id_ag) REFERENCES "devise"
);
COMMENT ON TABLE "ad_gar" IS 'Cette table contiendra l''ensemble des garanties (matérielles ou numéraires) que les clients ont placé en vue de l''obtention d''un crédit';
COMMENT ON COLUMN "ad_gar"."id_gar" IS 'Identifiant de la garantie ';
COMMENT ON COLUMN "ad_gar"."id_doss" IS 'Identifiant du dossier de crédit garanti ';
COMMENT ON COLUMN "ad_gar"."type_gar" IS 'Type de garantie (matérielle ou numéraire)';
COMMENT ON COLUMN "ad_gar"."gar_mat_id_bien" IS 'Identifiant du bien si garantie matérielle';
COMMENT ON COLUMN "ad_gar"."gar_num_id_cpte_prelev" IS 'ID du compte de prélèvement de la garantie si garantie numéraire';
COMMENT ON COLUMN "ad_gar"."gar_num_id_cpte_prelev" IS 'ID du compte d''épargne nantie contenant la garantie (si garantie numéraire)';
COMMENT ON COLUMN "ad_gar"."etat_gar" IS 'Etat de la garantie';
COMMENT ON COLUMN "ad_gar"."montant_vente" IS 'Montant de la vente (si état = vendu)';
CREATE TRIGGER gar_id_cpte_nantie_not_null BEFORE INSERT OR UPDATE ON ad_gar 
FOR EACH ROW EXECUTE PROCEDURE gar_id_cpte_nantie_not_null();

CREATE TABLE "ad_frais_attente" (
	"id_cpte" int4  NOT NULL,
	"date_frais" timestamp NOT NULL,
	"type_frais" int4   NOT NULL,
	"montant" numeric(30,6) NOT NULL,
	"id_ag" int4 NOT NULL,
	PRIMARY KEY("id_cpte", "date_frais", "type_frais","id_ag"),
	FOREIGN KEY (id_cpte,id_ag) REFERENCES "ad_cpt" ,
	FOREIGN KEY (type_frais,id_ag) REFERENCES "ad_cpt_ope"
);
COMMENT ON TABLE "ad_frais_attente" IS 'Cette table stocke pour chaque compte client concerné les frais en attente de perception';

-- Produits de Crédit et Statuts juridique associés
CREATE TABLE "adsys_asso_produitcredit_statjuri"(
"id_pc" integer  NOT NULL,
"ident_sj" integer NOT NULL,
"id_ag" int4 NOT NULL,
PRIMARY KEY("id_pc","ident_sj","id_ag"),
FOREIGN KEY (id_pc,id_ag) REFERENCES "adsys_produit_credit"
);
COMMENT ON TABLE "adsys_asso_produitcredit_statjuri" IS 'Produits de Crédit et Statuts juridique associés';

CREATE TABLE "ad_ord_perm"(
		"id_ord"   int4 DEFAULT nextval('ad_ord_perm_id_seq'::text) NOT NULL,
	    "cpt_from" int NOT NULL,
         CONSTRAINT ad_ord_perm_cpt_from_fk FOREIGN KEY (cpt_from,id_ag) REFERENCES ad_cpt (id_cpte,id_ag),
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
         CONSTRAINT ad_ord_perm_pkey PRIMARY KEY("id_ord","id_ag"),
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
CREATE TRIGGER ord_perm_before_update BEFORE UPDATE ON ad_ord_perm FOR EACH ROW EXECUTE PROCEDURE trig_update_ord_perm();
CREATE TRIGGER ord_perm_before_insert BEFORE INSERT ON ad_ord_perm FOR EACH ROW EXECUTE PROCEDURE trig_insert_ord_perm();

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

-- Table pour la sauvegarde des mouvements déjà consolidés avant la suppression des données d'une agence pour la fusion au siège
CREATE TABLE "ad_mouvement_consolide"(
	"id_ag" int4,
	"id_mouvement" int4,
	CONSTRAINT ad_mvt_consolide_pkey PRIMARY KEY("id_ag","id_mouvement")
);

COMMENT ON TABLE "ad_mouvement_consolide" IS 'Table utile à la consolidation pour la sauvegarde des mouvements consolidés';
COMMENT ON COLUMN "ad_mouvement_consolide"."id_ag" IS 'identifiant de agence à fusioner';
COMMENT ON COLUMN "ad_mouvement_consolide"."id_mouvement" IS 'le numéro du mouvement déjà consolidé';

CREATE TRIGGER ad_cpt_comptable_after_update AFTER UPDATE ON ad_cpt_comptable FOR EACH ROW EXECUTE PROCEDURE trig_insert_ad_libelle();
--Table pour stocker les libellés des postes(rubriques)--
CREATE TABLE ad_poste
(
  id_poste integer NOT NULL DEFAULT nextval(('ad_poste_id_poste_seq'::text)::regclass),
  libel varchar(50), 
  code varchar(30), 
  id_poste_centralise integer,--identifiant du père
  niveau integer,
  compartiment integer,
  type_etat integer,
  code_rapport varchar(30) NOT NULL,
  is_gras boolean,
  is_centralise boolean,
  CONSTRAINT pk_ad_poste PRIMARY KEY (id_poste)
);

--Table qui permet de faire l'association entre les postes et les comptes comptables --
CREATE TABLE ad_poste_compte
(
  id_poste integer NOT NULL,
  num_cpte_comptable varchar(50) NOT NULL, 
  is_cpte_provision boolean default false,
  signe char(1) NOT NULL DEFAULT '+', 
  operation boolean DEFAULT false , 
  code varchar(30)  NOT NULL , 
  CONSTRAINT pk_ad_poste_compte PRIMARY KEY (id_poste, num_cpte_comptable),
  CONSTRAINT fk_poste_poste_compte FOREIGN KEY (id_poste)
      REFERENCES ad_poste (id_poste)
      ON UPDATE CASCADE ON DELETE CASCADE
);
-- Création de la table adsys_type_piece_payement (pour l'enregistrement des différents types de pièce comptable) : Voir #782
CREATE TABLE adsys_type_piece_payement
(
  id integer NOT NULL DEFAULT nextval(('adsys_type_piece_payement_seq'::text)::regclass),
  libel integer,
  id_ag integer NOT NULL,
  CONSTRAINT pk_adsys_type_piece_payement PRIMARY KEY (id, id_ag),
  CONSTRAINT fk_adsys_type_piece_payement FOREIGN KEY (libel) REFERENCES ad_str(id_str) ON DELETE CASCADE

);
-- creation de la table ad_flux_compta contenant les resumés  des ecritures et mouvements comptables: pour une génération rapide des rapports comptables
 CREATE TABLE ad_flux_compta AS SELECT b.id_his,id_client,type_fonction,infos,a.id_ecriture,libel_ecriture, date_comptable,type_operation,id_jou,id_exo, ref_ecriture,id_mouvement,compte,sens,devise,montant,a.id_ag,consolide FROM ad_mouvement a right outer join ad_ecriture b on a.id_ecriture=b.id_ecriture inner join ad_his c on b.id_his = c.id_his where  a.id_ag = b.id_ag and b.id_ag = c.id_ag order by a.compte, b.date_comptable, b.type_operation;
  -- creation du trigger pour alimenté la table ad_flux_compta contenant les resumés  des ecritures et mouvements comptables
 CREATE TRIGGER ad_mouvement_after_insert AFTER INSERT ON ad_mouvement FOR EACH ROW EXECUTE PROCEDURE  trig_insert_ad_flux_compta();


-- Gestion de la tva (ticket 1578)
-- Ajout table adsys_taxes
CREATE TABLE "adsys_taxes" (
	"id" integer  NOT NULL DEFAULT nextval(('adsys_taxes_seq'::text)::regclass),
	"libel" integer NOT NULL,
	"type_taxe" int4   NOT NULL,
	"taux" float NOT NULL,
	"id_ag" integer NOT NULL,
	"cpte_tax_col" text,
	"cpte_tax_ded" text,
	PRIMARY KEY("id", "id_ag"),
  CONSTRAINT fk_adsys_taxe_ag FOREIGN KEY (id_ag) REFERENCES ad_agc(id_ag),
  CONSTRAINT fk_adsys_taxe FOREIGN KEY (libel) REFERENCES ad_str(id_str) ON DELETE CASCADE
);
COMMENT ON TABLE "adsys_taxes" IS 'Cette table stocke les informations concernant les taxes gérées par adbanking, pour le moment seule la tva est gérée.';
--creation de la sequence adsys_taxes
DROP SEQUENCE IF EXISTS adsys_taxes_seq;
CREATE SEQUENCE "adsys_taxes_seq" start 1 increment 1 maxvalue 214748647 minvalue 1 cache 1 ;

-- Ajout table ad_oper_taxe
CREATE TABLE "ad_oper_taxe" (
	"type_oper" int4  NOT NULL,
	"type_taxe" int4   NOT NULL,
	"id_taxe" integer NOT NULL,
	"id_ag" int4 NOT NULL,
	PRIMARY KEY("type_oper", "type_taxe", "id_ag"),
  CONSTRAINT fk_ad_oper_taxe FOREIGN KEY (type_oper, id_ag) REFERENCES ad_cpt_ope(type_operation, id_ag),
  CONSTRAINT fk_ad_taxe  FOREIGN KEY (id_taxe, id_ag) REFERENCES adsys_taxes(id, id_ag)
);
COMMENT ON TABLE "ad_oper_taxe" IS 'Cette table lie les opérations aux types de taxes gérées par adbanking';

-- Ajout table ad_declare_tva
CREATE TABLE "ad_declare_tva" (
	"id" integer  NOT NULL DEFAULT nextval(('ad_declare_tva_seq'::text)::regclass),
	"date_deb" timestamp NOT NULL,
	"date_fin" timestamp NOT NULL,
	"mnt_tva_dec" numeric(30,6),
	"mnt_cred_tva" numeric(30,6),
	"sens" text,
	"id_exo" int4 NOT NULL,
	"id_ag" int4 NOT NULL,
	PRIMARY KEY("id", "id_ag"),
  FOREIGN KEY (id_exo, id_ag) REFERENCES "ad_exercices_compta"
);
COMMENT ON TABLE "ad_declare_tva" IS 'Cette table stocke les informations sur les différentes déclarations de tva';
DROP SEQUENCE IF EXISTS ad_declare_tva_seq;
CREATE SEQUENCE "ad_declare_tva_seq" start 1 increment 1 maxvalue 214748647 minvalue 1 cache 1 ;

	CREATE TABLE "ad_chequier" (
	"id_chequier" serial  NOT NULL,
	"date_livraison" DATE,
	"num_first_cheque" int4,
	"num_last_cheque" int4,
	"etat_chequier" int4,
	"statut" int4 DEFAULT 0,
	"id_ag" int4 NOT NULL,
	"description" text,
	"date_statut" DATE,
    "id_cpte" int4 NOT NULL,
    unique (id_chequier) ,
	PRIMARY KEY(id_chequier,id_ag),
	FOREIGN KEY (id_cpte,id_ag) REFERENCES ad_cpt (id_cpte,id_ag)
	
);
CREATE TABLE "ad_commande_chequier" (
 "id" serial  NOT NULL,
 "id_cpte" int4 NOT NULL,
 "date_cmde" DATE,
 "date_envoi_impr" DATE,
 "date_impr" DATE,
 "id_ag" int4 NOT NULL,
 "nbre_carnets" int4 ,
 "frais" numeric(30,6) NOT NULL DEFAULT 0,
 "etat" int4 not null check (etat IN (1,2,3)),
 PRIMARY KEY ("id") ,
 FOREIGN KEY (id_cpte,id_ag) REFERENCES ad_cpt (id_cpte,id_ag)
);

-- ajout de la table cheque: Enregistre tous les cheques encaissés ou mise en opposition.
CREATE TABLE "ad_cheque" (
	"id_cheque" int NOT NULL,
	"id_chequier" int4 NOT NULL,
	"date_paiement" DATE,
	"date_opposition" DATE,
	"description" text,
	"etat_cheque" int4 ,
	"id_ag" int4 NOT NULL ,
	"is_opposition" BOOLEAN DEFAULT FALSE,
	"id_benef"  int4 ,
  PRIMARY KEY ("id_cheque","id_ag"),
  unique (id_cheque) ,
 FOREIGN KEY (id_chequier,id_ag) REFERENCES ad_chequier(id_chequier,id_ag),
 FOREIGN KEY (id_benef,id_ag) REFERENCES tireur_benef (id,id_ag)
);
--ALTER TABLE ad_cheque ADD constraint fk_ad_cheque_tireur_benef_id_benef FOREIGN KEY (id_benef,id_ag) REFERENCES tireur_benef (id,id_ag);

CREATE TABLE ad_dcr_hist (
	  "id" serial  NOT NULL,
	  "date_action" timestamp DEFAULT now(),
	  "id_doss" int4 NOT NULL,
	  "etat" int4,
	  "cre_etat" int4 ,
	  "cre_mnt_deb" numeric(30,6) DEFAULT 0,
	  "id_ag" int4 NOT NULL,
	  PRIMARY KEY (id, id_ag)
	);
	
CREATE TABLE ad_cpt_hist (
	  "id" serial  NOT NULL,
	  "date_action" timestamp DEFAULT now(),
	  "id_cpte" int4 NOT NULL,
	  "etat_cpte" int4,
	  "solde" numeric(30,6) DEFAULT 0,
	  "id_ag" int4 NOT NULL,
	  PRIMARY KEY (id, id_ag)
	);
-- Ajout d'un champs libre pour contenir les informations disparates de clients, crédits, etc
-- Ajout table champs_extras_table,  tickets #1018 et #1019
CREATE TABLE "champs_extras_table" (
	"id" integer  NOT NULL DEFAULT nextval(('champs_extras_table_seq'::text)::regclass),
	"libel" integer NOT NULL,
	"id_ag" integer NOT NULL,
	"table_name" varchar (255) NOT NULL,
	"type" varchar (10) NOT NULL,
	"champs_name" varchar (255) NOT NULL,
	"isreq"  bool default FALSE NOT NULL , 
	PRIMARY KEY("id", "id_ag"),
  CONSTRAINT fk_champs_extras_table_ag FOREIGN KEY (id_ag) REFERENCES ad_agc(id_ag),
  CONSTRAINT fk_champs_extras_table FOREIGN KEY (libel) REFERENCES ad_str(id_str) ON DELETE CASCADE,
  unique ("id_ag","champs_name")
);
COMMENT ON TABLE "champs_extras_table" IS 'Cette table permet de paramétrer les champs supplémentaires des tables.';
CREATE TABLE "champs_extras_valeurs_ad_dcr" (
	"id_doss" integer  NOT NULL ,
	"id_champs_extras_table" integer NOT NULL,
	"id_ag" integer NOT NULL,
	valeur varchar(255) NOT NULL,
	PRIMARY KEY("id_champs_extras_table",id_doss, "id_ag"),
  CONSTRAINT fk_champs_extras_valeurs_ad_dcr_champs_extras_id_ag FOREIGN KEY (id_ag) REFERENCES ad_agc(id_ag),
  CONSTRAINT fk_champs_extras_valeurs_ad_dcr_ad_agc_id_champs_extras_table FOREIGN KEY (id_champs_extras_table,id_ag) REFERENCES champs_extras_table(id,id_ag),
  CONSTRAINT fk_champs_extras_valeurs_ad_dcr_ad_dcr_id_doss  FOREIGN KEY (id_doss,id_ag) REFERENCES ad_dcr(id_doss,id_ag)
  
);
COMMENT ON TABLE "champs_extras_valeurs_ad_dcr" IS 'Cette table permet de stocker les valeurs dess champs supplémentaires de table ad_dcr.';

CREATE TABLE "champs_extras_valeurs_ad_cli" (
	"id_client" integer  NOT NULL ,
	"id_champs_extras_table" integer NOT NULL,
	"id_ag" integer NOT NULL,
	valeur varchar(255) NOT NULL,
	PRIMARY KEY("id_champs_extras_table",id_client, "id_ag"),
  CONSTRAINT fk_champs_extras_valeurs_dcr_champs_extras_id_ag FOREIGN KEY (id_ag) REFERENCES ad_agc(id_ag),
  CONSTRAINT fk_champs_extras_valeurs_dcr_ad_agc_id_champs_extras_table FOREIGN KEY (id_champs_extras_table,id_ag) REFERENCES champs_extras_table(id,id_ag),
  CONSTRAINT fk_champs_extras_valeurs_dcr_ad_dcr_id_client  FOREIGN KEY (id_client,id_ag) REFERENCES ad_cli(id_client,id_ag)
  
);
COMMENT ON TABLE "champs_extras_valeurs_ad_cli" IS 'Cette table permet de stocker les valeurs dess champs supplémentaires de table ad_cli.';

--creation de la sequence champs_extras_table
DROP SEQUENCE IF EXISTS champs_extras_table_seq;
CREATE SEQUENCE "champs_extras_table_seq" start 1 increment 1 maxvalue 214748647 minvalue 1 cache 1 ;
-- table adsys_param_epargne
  CREATE TABLE "adsys_param_epargne"  (
  "id_ag" INTEGER NOT NULL ,
  "cpte_inactive_nbre_jour" INTEGER ,
  "cpte_inactive_frais_tenue_cpte" BOOLEAN  DEFAULT FALSE,
   primary key (id_ag),
   CONSTRAINT fk_adsys_param_epargne_id_ag FOREIGN KEY (id_ag) references ad_agc (id_ag)
   
  );
  COMMENT ON TABLE "adsys_param_epargne" IS 'Cette table permet de stocker les paramètres des comptes d''épargne';


-- Tables jasper report

CREATE TABLE "ad_jasper_rapport" (
 "id_rapport" int4 DEFAULT nextval('ad_jasper_rapport_id_rapport_seq'::text) NOT NULL,
 "libel" text NOT NULL,
 "code_rapport" varchar(30) NOT NULL,
 "nom_fichier" varchar(200),
 "cat_rapport" varchar(30),
 "id_ag" int4 NOT NULL,
 PRIMARY KEY ("id_rapport","id_ag"),
 UNIQUE (code_rapport,"id_ag")
);

CREATE TABLE "ad_jasper_param" (
 "id_param" int4 DEFAULT nextval('ad_jasper_rapport_id_rapport_seq'::text) NOT NULL,
 "libel" text NOT NULL,
 "code_param" varchar(30) NOT NULL,
 "type_param" char(6),
 "id_ag" int4 NOT NULL,
 PRIMARY KEY ("id_param","id_ag"),
 UNIQUE (code_param,id_ag)
);

CREATE TABLE  "ad_jasper_rapport_param" (
 "code_param" varchar(30) NOT NULL ,
 "code_rapport" varchar(30),
 "id_ag" int4 NOT NULL,
 PRIMARY KEY ("code_param","code_rapport","id_ag"),
 FOREIGN KEY (code_param,id_ag) REFERENCES ad_jasper_param(code_param,id_ag),
 FOREIGN KEY (code_rapport,id_ag) REFERENCES ad_jasper_rapport(code_rapport,id_ag)
);


-- Table Provision dossier credit

CREATE TABLE ad_provision
( id_provision serial NOT NULL,
  id_doss INTEGER NOT NULL,
  montant numeric(30,6) NOT NULL,
  id_ag integer NOT NULL,
  taux double precision,
  id_cred_etat integer,
  date_prov date,
  is_repris boolean default 'FALSE' ,
  CONSTRAINT ad_provision_id_provision_id_ag_pkey PRIMARY KEY (id_provision, id_ag),
  FOREIGN KEY (id_doss,id_ag) REFERENCES ad_dcr (id_doss,id_ag)
);

-- Table pour la gestion de licence ioncube
CREATE TABLE adsys_licence
(
  "id_licence" integer  NOT NULL DEFAULT nextval(('adsys_licence_id_licence_seq'::text)::regclass),  
  "id_agc" integer NOT NULL,
  "date_creation" timestamp without time zone,
  "date_expiration" timestamp without time zone,
  "statut_licence" boolean DEFAULT false,
  CONSTRAINT adsys_licence_pkey PRIMARY KEY (id_licence)
)
WITH (
  OIDS=FALSE
);

-- Table pour historisation des dossiers de credit
CREATE TABLE ad_dcr_his
(
  "id_dcr_his" integer NOT NULL DEFAULT nextval(('ad_dcr_his_id_dcr_his_seq'::text)::regclass),
  "id_doss" integer NOT NULL,
  "mod_type" integer NOT NULL,
  "id_ech" integer NULL,
  "ech_date_dem" timestamp without time zone NULL,
  "reech_duree" integer NULL,
  "approb_date" timestamp without time zone NULL,
  "approb_flag" boolean DEFAULT false,
  "date_crea" timestamp without time zone,
  "date_modif" timestamp without time zone NULL,
  "nom_login" text,
  "id_ag" integer NOT NULL,
  CONSTRAINT ad_dcr_his_pkey PRIMARY KEY (id_dcr_his)
)
WITH (
  OIDS=FALSE
);
COMMENT ON TABLE ad_dcr_his
  IS 'Dossiers historisation';
COMMENT ON COLUMN ad_dcr_his.mod_type IS '1 - modif.date, 2 - raccourcir durée, 3 - allonger durée';

-- Table pour historisation des echeanciers théoriques de credit
CREATE TABLE ad_etr_his
(
  "id_etr_his" integer NOT NULL DEFAULT nextval(('ad_etr_his_id_etr_his_seq'::text)::regclass),
  "id_dcr_his" integer NOT NULL,
  "id_doss" integer NOT NULL,
  "id_ech" integer NOT NULL,
  "ech_date" timestamp without time zone,
  "mnt_cap" numeric(30,6) DEFAULT 0,
  "mnt_int" numeric(30,6) DEFAULT 0,
  "mnt_gar" numeric(30,6) DEFAULT 0,
  "mnt_reech" numeric(30,6) DEFAULT 0,
  "solde_cap" numeric(30,6) DEFAULT 0,
  "solde_int" numeric(30,6) DEFAULT 0,
  "solde_gar" numeric(30,6) DEFAULT 0,
  "solde_pen" numeric(30,6) DEFAULT 0,
  "nom_login" text,
  "id_ag" integer NOT NULL,
  CONSTRAINT ad_etr_his_pkey PRIMARY KEY (id_etr_his)
)
WITH (
  OIDS=FALSE
);
COMMENT ON TABLE ad_etr_his
IS 'Echéanciers théoriques historisation';


-- Create Table pour historisation des écarts inventaire / compte comptable if not exists
CREATE TABLE "ad_ecart_compta"
(  
  "id" SERIAL NOT NULL,
  "date_ecart" timestamp NOT NULL,	
  "num_cpte_comptable" text NOT NULL,
  "libel_cpte_comptable" text NOT NULL,
  "devise" character(3),
  "solde_cpte_int" numeric(30,6) DEFAULT 0,
  "solde_cpte_comptable" numeric(30,6) DEFAULT 0,
  "ecart" numeric(30,6) DEFAULT 0,
  "id_ag" integer NOT NULL,
  "login" text NULL,	
  "id_his" integer NULL,	
  PRIMARY KEY("id") 
)
WITH (
  OIDS=FALSE
);
COMMENT ON TABLE ad_ecart_compta IS 'Log des écarts comptabilité/inventaire';
COMMENT ON COLUMN "ad_ecart_compta"."date_ecart" IS 'Date à laquelle l''écart a été constaté';
COMMENT ON COLUMN "ad_ecart_compta"."solde_cpte_int" IS 'Le sum solde des comptes internes ad_cpt associés à ce compte comptable à la date_ecart';
COMMENT ON COLUMN "ad_ecart_compta"."solde_cpte_comptable" IS 'Le solde du compte comptable dans ad_cpt_comptable à la date_ecart';
COMMENT ON COLUMN "ad_ecart_compta"."ecart" IS 'L''écart entre les deux soldes';
COMMENT ON COLUMN "ad_ecart_compta"."login" IS 'Si renseigné, le login qui a peut etre causé l''écart';
COMMENT ON COLUMN "ad_ecart_compta"."id_his" IS 'Si renseigné, l''operation comptable loggé dans id_his qui a peut etre causé l''écart';

CREATE INDEX i_ad_ecart_compta_num_cpte_comptable on ad_ecart_compta(num_cpte_comptable);


-- Create Table pour les fonctions systèmes
CREATE TABLE "adsys_fonction" (
    "id" SERIAL NOT NULL,
    "code_fonction" integer NOT NULL,
    "libelle" character varying(300),
    "id_ag" integer NULL,
	PRIMARY KEY ("code_fonction")
)
WITH (
  OIDS=FALSE
);
COMMENT ON TABLE "adsys_fonction" IS 'Liste des fonctions systèmes';
