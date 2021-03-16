-- Script de mise à jour de la base de données de la version 3.2.x à la version 3.4

-- pour mettre à jour menus et écrans
DELETE FROM ecrans;
DELETE FROM menus;

DELETE FROM d_tableliste;
DELETE FROM tableliste;

--ajout de la table ad_chequier
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

-- Traductions pour de nouveaux champs
-- Fonction utilitaire qui ajoute le champ pour la traduction s'il n'est pas déjà présent
CREATE OR REPLACE FUNCTION add_traduction_to_table(text, text)
RETURNS boolean AS $$
DECLARE
  table_name ALIAS FOR $1;
  column_name ALIAS FOR $2;
  column_exists boolean;
  SQL text;

BEGIN
 --SELECT INTO column_exists EXISTS (SELECT attname FROM pg_attribute WHERE attname = column_name AND atttypid != 23 AND attrelid = (SELECT relfilenode FROM pg_class WHERE relname = table_name));
  SELECT INTO column_exists EXISTS (SELECT pg_catalog.quote_ident(attname)   FROM pg_catalog.pg_attribute a, pg_catalog.pg_class c  WHERE c.oid = a.attrelid    AND a.attnum > 0    AND NOT a.attisdropped    AND attname=pg_catalog.quote_ident(column_name)   AND (pg_catalog.quote_ident(relname)=pg_catalog.quote_ident(table_name) )    AND pg_catalog.pg_table_is_visible(c.oid) and pg_catalog.format_type(a.atttypid,a.atttypmod) <> 'integer');
  
  IF column_exists THEN
    SQL = 'ALTER TABLE '||table_name||' ADD COLUMN "trad" int REFERENCES ad_str(id_str) ON DELETE CASCADE;';
    EXECUTE SQL;
    SQL = 'UPDATE '||table_name||' SET trad = makeTraductionLangSyst('||column_name||');';
    EXECUTE SQL;
    SQL = 'ALTER TABLE '||table_name||' DROP COLUMN '||column_name||';';
    EXECUTE SQL;
    SQL = 'ALTER TABLE '||table_name||' RENAME trad TO '||column_name||';';
    EXECUTE SQL;
    RETURN true;
  ELSE
    RETURN false;
  END IF;
END;
$$ LANGUAGE plpgsql;

SELECT add_traduction_to_table('ad_cpt_ope', 'libel_ope');
DROP VIEW IF EXISTS view_compta;
SELECT add_traduction_to_table('ad_ecriture', 'libel_ecriture');
SELECT add_traduction_to_table('ad_journaux', 'libel_jou');
SELECT add_traduction_to_table('ad_brouillard', 'libel_ecriture');

-- ajout du champs pourcentage de frais de dossier de crédit
ALTER TABLE adsys_produit_credit ADD COLUMN prc_frais double precision DEFAULT 0;
-- ajout du champ dat_nb_reconduction , nombre de  reconduire /prolongation à effectuer du DAT 
ALTER TABLE ad_cpt ADD column dat_nb_reconduction INTEGER DEFAULT 0;





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

--droit admin
INSERT INTO adsys_profils_axs(profil,fonction) VALUES(1,281);


 --table adsys_produit_credit
  -- ajout du champ cpte_cpta_att_deb pour le compte d'attente de déboursement
  ALTER TABLE adsys_produit_credit ADD COlUMN cpte_cpta_att_deb TEXT DEFAULT NULL;
  
  --table ad_dcr
  -- ajout du champ cre_cpt_att_deb pour le compte interne d'attente de déboursement
  ALTER TABLE ad_dcr ADD COlUMN cre_cpt_att_deb int4 DEFAULT NULL;
  
-- Compte d'attente créditeur
INSERT INTO adsys_produit_epargne
(id, id_ag,libel, sens, service_financier, nbre_occurrences, frais_tenue_prorata,
retrait_unique, depot_unique, certif,dat_prolongeable, classe_comptable)

VALUES (
5,NumAgc(), 'Compte d''attente créditeur', 'c', false, 1, false,
false,false, false,  false, 7);

-- Ajout opération 212 et 213 : Mise en attente de déboursement et Annulation déboursement progressif
INSERT INTO ad_cpt_ope (type_operation, libel_ope, categorie_ope, id_ag) VALUES (212,maketraductionlangsyst('Mise en attente de déboursement progressif'),1,NumAgc());
INSERT INTO ad_cpt_ope (type_operation, libel_ope, categorie_ope, id_ag) VALUES (213,maketraductionlangsyst('Annulation déboursement progressif'),1,NumAgc());

INSERT INTO ad_cpt_ope_cptes VALUES (212,NULL, 'd', 2, NumAgc());
INSERT INTO ad_cpt_ope_cptes VALUES (212,NULL, 'c', 1, NumAgc());
INSERT INTO ad_cpt_ope_cptes VALUES (213,NULL, 'd', 1, NumAgc());
INSERT INTO ad_cpt_ope_cptes VALUES (213,NULL, 'c', 2, NumAgc());

-- table adsys_param_epargne
  CREATE TABLE "adsys_param_epargne"  (
  "id_ag" INTEGER NOT NULL ,
  "cpte_inactive_nbre_jour" INTEGER ,
  "cpte_inactive_frais_tenue_cpte" BOOLEAN  DEFAULT FALSE,
   primary key (id_ag),
   CONSTRAINT fk_adsys_param_epargne_id_ag FOREIGN KEY (id_ag) references ad_agc (id_ag)
   
  );
  COMMENT ON TABLE "adsys_param_epargne" IS 'Cette table permet de stocker les paramètres des comptes d''épargne';
  --
  insert into adsys_param_epargne (id_ag) VALUES (numagc());

  INSERT INTO ad_cpt_ope (type_operation, libel_ope, categorie_ope, id_ag) VALUES (170, maketraductionlangsyst('Déclasser les Comptes dormants'), 1, numagc() );
  INSERT INTO ad_cpt_ope_cptes VALUES (170,NULL, 'd', 1,numagc());
   INSERT INTO ad_cpt_ope_cptes VALUES (170,NULL, 'c', 0,numagc());


  
-- tables d'historisation des crédits et des épargnes
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
CREATE TRIGGER trig_before_update_ad_dcr BEFORE UPDATE ON ad_dcr FOR EACH ROW EXECUTE PROCEDURE trig_insert_ad_dcr_hist();
CREATE TABLE ad_cpt_hist (
	  "id" serial  NOT NULL,
	  "date_action" timestamp DEFAULT now(),
	  "id_cpte" int4 NOT NULL,
	  "etat_cpte" int4,
	  "solde" numeric(30,6) DEFAULT 0,
	  "id_ag" int4 NOT NULL,
	  PRIMARY KEY (id, id_ag)
	);
ALTER TABLE tireur_benef ADD column type_piece INTEGER;
ALTER TABLE tireur_benef ADD column num_piece Varchar(50);
ALTER TABLE tireur_benef ADD column lieu_delivrance text;


