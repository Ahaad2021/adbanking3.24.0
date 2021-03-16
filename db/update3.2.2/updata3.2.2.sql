-- Script de mise à jour de la base de données de la version 3.0.x à la version 3.2

-- pour mettre à jour menus et écrans
DELETE FROM ecrans;
DELETE FROM menus;

DELETE FROM d_tableliste;
DELETE FROM tableliste;

 CREATE SEQUENCE "ad_jasper_rapport_id_rapport_seq" start 1 increment 1 maxvalue 999999 minvalue 1 cache 1 ;
CREATE SEQUENCE "ad_jasper_param_id_param_seq" start 1 increment 1 maxvalue 999999 minvalue 1 cache 1 ;

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
-- Modifications des types des champs 
ALTER TABLE ad_cpt_comptable ALTER COLUMN num_cpte_comptable TYPE VARCHAR(50);
ALTER TABLE ad_cpt_comptable ADD PRIMARY KEY (num_cpte_comptable);

-- Modification des types des champs sens et compte et création des index pour la table mouvement
DROP INDEX IF EXISTS index_mvt_compte;
DROP INDEX IF EXISTS index_mvt_date;
DROP INDEX IF EXISTS index_mvt_sens;
DROP INDEX IF EXISTS IDX_cpte_compta_sens_date;
ALTER TABLE ad_mouvement ALTER COLUMN sens TYPE CHAR(1);
ALTER TABLE ad_mouvement ALTER COLUMN compte TYPE VARCHAR(50);

CREATE INDEX IDX_cpte_compta_sens_date ON ad_mouvement (compte, sens, date_valeur);


-- Modifications des taux de provisions des états de crédits
UPDATE adsys_etat_credits SET taux = taux/100 WHERE taux > 1;


-- Ajout du champs is_produit_decouvert dans adsys_produit_credit
ALTER TABLE adsys_produit_credit ADD COLUMN  is_produit_decouvert boolean DEFAULT false;

ALTER TABLE ad_cpt_comptable ALTER COLUMN num_cpte_comptable  TYPE VARCHAR(50);


--droit admin
INSERT INTO adsys_profils_axs(profil,fonction) VALUES(1,300);


--ALTER TABLE ad_poste_compte ADD COLUMN signe char(1) NOT NULL DEFAULT '+'::char;
--ALTER TABLE ad_poste_compte ADD COLUMN  operation boolean DEFAULT false;

ALTER TABLE ad_agc ADD COLUMN realisation_garantie_sain BOOLEAN DEFAULT 'false';

-- Modification de la table ad_poste_compte et
DELETE FROM ad_poste_compte;
DELETE FROM ad_poste ;
ALTER TABLE ad_poste ADD COLUMN code_rapport varchar(30) NOT NULL;
ALTER TABLE ad_poste_compte DROP CONSTRAINT pk_ad_poste_compte;
ALTER TABLE ad_poste_compte ALTER COLUMN  id_poste DROP not null;
ALTER TABLE ad_poste_compte ADD COLUMN signe char(1) NOT NULL DEFAULT '+';
ALTER TABLE ad_poste_compte ADD COLUMN operation boolean DEFAULT false;
ALTER TABLE ad_poste_compte ADD COLUMN code varchar(30)  NOT NULL;
ALTER TABLE ad_poste_compte Add CONSTRAINT pk_ad_poste_compte PRIMARY KEY (id_poste, num_cpte_comptable);
ALTER TABLE ad_poste_compte Add CONSTRAINT fk_ad_poste_compte_ad_cpt_comptable FOREIGN KEY ( num_cpte_comptable)  REFERENCES ad_cpt_comptable  ( num_cpte_comptable);

 --ajout champ prov_mnt montant de la provision
 ALTER TABLE ad_dcr ADD COLUMN prov_mnt numeric(30,6) DEFAULT 0 ;
 
DROP TABLE if exists ad_provision ;

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