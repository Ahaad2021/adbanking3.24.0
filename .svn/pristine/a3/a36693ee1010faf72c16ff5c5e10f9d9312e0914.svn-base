-- Script de mise à jour de la base de données de la version 2.2 à la version 2.4

-- Suppression des menus, des ecrans et de la liste des tables
DELETE FROM ecrans;
DELETE FROM menus;
DELETE FROM d_tableliste;
DELETE FROM tableliste;

--Regénérer les fonctions et les types
DROP FUNCTION PreleveInteretsDebiteurs(DATE, TEXT) ;
DROP TYPE cpte_deb;
--DROP FUNCTION PreleveFraisTenueCpt(INTEGER, TEXT, INTEGER);
DROP TYPE cpte_frais CASCADE;

-- Ajout de la table des personnes extérieures
CREATE SEQUENCE "ad_pers_ext_id_pers_ext_seq" start 1 increment 1 maxvalue 2147483647 minvalue 1  cache 1;

CREATE TABLE "ad_pers_ext" (
	"id_pers_ext" int4 DEFAULT nextval('ad_pers_ext_id_pers_ext_seq'::text) NOT NULL,
	"id_client" int4 REFERENCES "ad_cli",
	"denomination" text,
	"adresse" text,
	"code_postal" text,
	"ville" text,
	"pays" int REFERENCES "adsys_pays",
	"num_tel" text,
	"date_naiss" date,
	"lieu_naiss" text,
	"type_piece_id" int4 REFERENCES "adsys_type_piece_identite",
	"num_piece_id" text,
	"lieu_piece_id" text,
	"date_piece_id" date,
	"date_exp_piece_id" date,
	"photo" OID,
	"signature" OID,
	PRIMARY KEY("id_pers_ext")
);

-- Ajout de la table des mandats
CREATE SEQUENCE "ad_mandat_id_mandat_seq" start 1 increment 1 maxvalue 214748647 minvalue 1  cache 1;

CREATE TABLE "ad_mandat" (
        "id_mandat" int4 DEFAULT nextval('ad_mandat_id_mandat_seq'::text) NOT NULL,
        "id_cpte" int4 REFERENCES "ad_cpt" NOT NULL,
        "id_pers_ext" int4 REFERENCES "ad_pers_ext" NOT NULL,
        "type_pouv_sign" int4 NOT NULL,
        "limitation" numeric(30,6),
        "date_exp" date,
        "valide" bool,
        PRIMARY KEY("id_mandat")
);


-- Modification de la table des relations
ALTER TABLE ad_rel ADD COLUMN "id_pers_ext" int4 REFERENCES "ad_pers_ext";


-- Procédure : création d'une entrée dans la table des personnes extérieures par client
CREATE OR REPLACE FUNCTION PSUpdate2_2() RETURNS integer AS '
DECLARE
        client CURSOR FOR select id_client from ad_cli;
        ligne RECORD;
BEGIN
        OPEN client;
        FETCH client INTO ligne;
        WHILE FOUND LOOP
        INSERT INTO ad_pers_ext (id_client) VALUES (ligne.id_client);
        FETCH client INTO ligne;
        END LOOP;
        CLOSE client;
        return 0;
END;
' LANGUAGE plpgsql;

-- Exécution de la procédure
SELECT PSUpdate2_2();

-- Suppression de la procédure
DROP FUNCTION PSUpdate2_2();


-- Procédure : création d'une entrée dans la table des mandats par compte avec titulaire PP
CREATE OR REPLACE FUNCTION PSUpdate2_2() RETURNS integer AS '
DECLARE
        compte CURSOR FOR select a.id_cpte, c.id_pers_ext from ad_cpt a, ad_cli b, ad_pers_ext c where a.id_titulaire = b.id_client and b.id_client = c.id_client and b.statut_juridique = 1;
        ligne RECORD;
BEGIN
        OPEN compte;
        FETCH compte INTO ligne;
        WHILE FOUND LOOP
        INSERT INTO ad_mandat (id_cpte, id_pers_ext, type_pouv_sign, valide) VALUES (ligne.id_cpte, ligne.id_pers_ext, 1, true);
        FETCH compte INTO ligne;
        END LOOP;
        CLOSE compte;
        return 0;
END;
' LANGUAGE plpgsql;

-- Exécution de la procédure
SELECT PSUpdate2_2();

-- Suppression de la procédure
DROP FUNCTION PSUpdate2_2();


-- Procédure : traitement de la table des relations
CREATE OR REPLACE FUNCTION PSUpdate2_2() RETURNS integer AS '
DECLARE
        relation CURSOR FOR select * from ad_rel;
        ligne RECORD;
        pers_ext INTEGER;
BEGIN
        OPEN relation;
        FETCH relation INTO ligne;
        WHILE FOUND LOOP
        IF (ligne.valide = true AND ligne.typ_rel = 6 AND ligne.pouv_sign = true) THEN
                IF (ligne.id_clientrel IS NULL) THEN
                        INSERT INTO ad_pers_ext (denomination, date_naiss, lieu_naiss) VALUES (ligne.nom || '' '' || ligne.prenom, ligne.date_naiss, ligne.lieu_naiss);
                        SELECT currval(''ad_pers_ext_id_pers_ext_seq'') INTO pers_ext;
                ELSE
                        SELECT id_pers_ext INTO pers_ext FROM ad_pers_ext WHERE id_client = ligne.id_clientrel;
                END IF;
                DECLARE
                        compte CURSOR FOR select id_cpte from ad_cpt where id_titulaire = ligne.id_client;
                        ligne_cpte RECORD;
                BEGIN
                        OPEN compte;
                        FETCH compte INTO ligne_cpte;
                        WHILE FOUND LOOP
                                INSERT INTO ad_mandat (id_cpte, id_pers_ext, type_pouv_sign, valide) VALUES (ligne_cpte.id_cpte, pers_ext, 1, true);
                                FETCH compte INTO ligne_cpte;
                        END LOOP;
                        CLOSE compte;
                END;
                DELETE FROM ad_rel where id_rel = ligne.id_rel;
        ELSIF (ligne.valide = true AND ligne.typ_rel != 6) THEN
                IF (ligne.id_clientrel IS NULL) THEN
                        INSERT INTO ad_pers_ext (denomination, date_naiss, lieu_naiss) VALUES (ligne.nom || '' '' || ligne.prenom, ligne.date_naiss, ligne.lieu_naiss);
                        SELECT currval(''ad_pers_ext_id_pers_ext_seq'') INTO pers_ext;
                ELSE
                        SELECT id_pers_ext INTO pers_ext FROM ad_pers_ext WHERE id_client = ligne.id_clientrel;
                END IF;
                UPDATE ad_rel SET id_pers_ext = pers_ext WHERE id_rel = ligne.id_rel;
        ELSE
                DELETE FROM ad_rel where id_rel = ligne.id_rel;
        END IF;
        FETCH relation INTO ligne;
        END LOOP;
        CLOSE relation;
        return 0;
END;
' LANGUAGE plpgsql;

-- Exécution de la procédure
SELECT PSUpdate2_2();

-- Suppression de la procédure
DROP FUNCTION PSUpdate2_2();


-- Modification de la table des relations
ALTER TABLE ad_rel DROP COLUMN "nom";
ALTER TABLE ad_rel DROP COLUMN "prenom";
ALTER TABLE ad_rel DROP COLUMN "date_naiss";
ALTER TABLE ad_rel DROP COLUMN "lieu_naiss";
ALTER TABLE ad_rel DROP COLUMN "id_clientrel";
ALTER TABLE ad_rel DROP COLUMN "pouv_sign";
ALTER TABLE ad_rel DROP COLUMN "photo";
ALTER TABLE ad_rel DROP COLUMN "signature";
ALTER TABLE ad_rel ALTER COLUMN typ_rel SET NOT NULL;
ALTER TABLE ad_rel ALTER COLUMN id_client SET NOT NULL;
ALTER TABLE ad_rel ALTER COLUMN id_pers_ext SET NOT NULL;


-- Modification de la table des clients
ALTER TABLE ad_cli ADD COLUMN "pp_date_piece_id" date;


-- Suppression de la table des extraits de compte
DROP TABLE "ad_extrait_cpte";
DROP SEQUENCE "ad_extrait_cpte_seq";

-- Ajout de la table des extraits de compte
CREATE SEQUENCE "ad_extrait_cpte_seq" start 1 increment 1 maxvalue 2147483647 minvalue 1 cache 1 ;

CREATE TABLE "ad_extrait_cpte" (
        "id_extrait_cpte" int4 DEFAULT nextval('ad_extrait_cpte_seq'::text) NOT NULL,
        "id_his" int4 REFERENCES "ad_his",
        "id_cpte" int4 REFERENCES "ad_cpt" NOT NULL,
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
        "cptie_pays" int4 REFERENCES "adsys_pays",
        "cptie_mnt" numeric(30,6),
        "cptie_devise" char(3) REFERENCES "devise",
        "eft_id_extrait" int4  NOT NULL,
        "eft_id_mvt" int4 NOT NULL,
        "eft_id_client" int4 NOT NULL REFERENCES "ad_cli",
        "eft_annee_oper" char(4) NOT NULL,
        "eft_dern_solde" numeric(30,6) NOT NULL,
        "eft_dern_date" date NOT NULL,
        "eft_nouv_solde" numeric(30,6) NOT NULL,
        "eft_sceau" timestamp NOT NULL DEFAULT now(),
        PRIMARY KEY ("id_extrait_cpte")
);


-- Modification de la table des comptes
ALTER TABLE ad_cpt ADD COLUMN export_netbank bool;
ALTER TABLE ad_cpt ADD COLUMN id_dern_extrait_imprime int4 REFERENCES ad_extrait_cpte;


-- Modification de la table de l'historique extérieur
ALTER TABLE ad_his_ext ADD COLUMN id_pers_ext int4 REFERENCES ad_pers_ext;

-- Modification de la table des agences
ALTER TABLE ad_agc ADD COLUMN licence oid;
ALTER TABLE ad_agc ADD COLUMN clients_actifs int4;
ALTER TABLE ad_agc ADD COLUMN total_clients int4;


/***************** Mise à jour pour Rémunération de l'épargne ***************************/

-- Modification de adsys_produit_epargne
ALTER TABLE adsys_produit_epargne DROP COLUMN dat_nbre_prolongations;
ALTER TABLE adsys_produit_epargne DROP COLUMN mode_remun;

ALTER TABLE adsys_produit_epargne ADD COLUMN mode_paiement integer;
ALTER TABLE adsys_produit_epargne ADD COLUMN marge_tolerance integer;
ALTER TABLE adsys_produit_epargne ALTER COLUMN marge_tolerance set default 0;
ALTER TABLE adsys_produit_epargne ADD COLUMN modif_cptes_existants bool;

-- Modification de ad_cpt
ALTER TABLE ad_cpt ADD COLUMN tx_interet_cpte double precision;
ALTER TABLE ad_cpt ADD COLUMN terme_cpte integer;
ALTER TABLE ad_cpt ADD COLUMN freq_calcul_int_cpte integer;
ALTER TABLE ad_cpt ADD COLUMN mode_calcul_int_cpte integer;
ALTER TABLE ad_cpt ADD COLUMN cpte_virement_clot integer;
ALTER TABLE ad_cpt ADD COLUMN mode_paiement_cpte integer;

-- 'Paiement fin de mois' pour les DAV
UPDATE adsys_produit_epargne SET mode_paiement =1 where classe_comptable=1;
UPDATE ad_cpt SET mode_paiement_cpte =1 where id_prod in (select id from adsys_produit_epargne where classe_comptable=1);

-- 'Paiement date ouverture' pour les DAT et les CAT
UPDATE adsys_produit_epargne SET mode_paiement =2 where classe_comptable=2 OR classe_comptable=5;
UPDATE ad_cpt SET mode_paiement_cpte=2 where id_prod in
 (select id from adsys_produit_epargne where classe_comptable=2 or classe_comptable=5);


-- Héritage de données
UPDATE ad_cpt SET tx_interet_cpte = (select tx_interet from adsys_produit_epargne where id=id_prod);
UPDATE ad_cpt SET terme_cpte = (select terme from adsys_produit_epargne where id=id_prod);
UPDATE ad_cpt SET freq_calcul_int_cpte = (select freq_calcul_int from adsys_produit_epargne where id=id_prod);
UPDATE ad_cpt SET mode_calcul_int_cpte = (select mode_calcul_int from adsys_produit_epargne where id=id_prod);

/***************** Fin mise à jour pour Rémunération de l'épargne ***************************/

