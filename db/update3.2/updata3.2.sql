-- Script de mise à jour de la base de données de la version 3.0.x à la version 3.2

-- pour mettre à jour menus et écrans
DELETE FROM ecrans;
DELETE FROM menus;

DELETE FROM d_tableliste;
DELETE FROM tableliste;

--Regénérer les fonctions et les types (on les recharge par frais_tenue_cpt.sql et fonctions.sql)
DROP FUNCTION PreleveFraisTenueCpt(INTEGER, TEXT, INTEGER);
DROP TYPE cpte_frais CASCADE;
DROP FUNCTION PreleveInteretsDebiteurs(DATE, TEXT) ;
DROP TYPE cpte_deb CASCADE;

-- Mise à jour ad_agc et ad_log pour la gestion du plafond de depot au niveau du guichet (voir #1610)
-- Table ad_agc --
ALTER TABLE ad_agc ADD COLUMN plafond_depot_guichet BOOLEAN;
ALTER TABLE ad_agc ADD COLUMN montant_plafond_depot numeric(30,6) DEFAULT 0;
-- Table ad_log --
ALTER TABLE ad_log ADD COLUMN depasse_plafond_depot BOOLEAN DEFAULT false;
-- insertion d'une nouvelle operation

-- Mise à jour table ad_agc et ad_cpt pour l'interface Netbank : voir #1611
-- Ajout du champ  utilise_netbank (pour dire que l'agence utilise l'interface Netbank) voir #1611
ALTER TABLE ad_agc ADD COLUMN utilise_netbank BOOLEAN ;
ALTER TABLE ad_agc ALTER COLUMN utilise_netbank SET DEFAULT false;
-- Mise à jour table ad_cpt pour l'export netbank voir #1611
UPDATE ad_cpt SET export_netbank = false;

INSERT INTO ad_cpt_ope VALUES (1005,'transfert solde  d'' un compte supprimé',1, NumAgc());

-- Mise à jour ad_cpt la gestion des épargnes à la source
-- Table ad_cpt --
ALTER TABLE ad_cpt ADD COLUMN interet_a_capitaliser numeric(30,6) DEFAULT 0;

-- Table adsys_produit_epargne --
-- Mise à jour adsys_produit_epargne pour la gestion des épargnes à la source
ALTER TABLE adsys_produit_epargne ADD COLUMN ep_source_date_fin timestamp;
-- ajout champ access_solde pour donner accès au solde des comptes clients pour certains profils
 ALTER TABLE adsys_produit_epargne ADD COLUMN masque_solde_epargne boolean DEFAULT FALSE;

-- Création de la table adsys_type_piece_payement (pour l'enregistrement des différents types de pièce comptable) : Voir #782
CREATE TABLE adsys_type_piece_payement
(
  id integer NOT NULL DEFAULT nextval(('adsys_type_piece_payement_seq'::text)::regclass),
  libel integer,
  id_ag integer NOT NULL,
  CONSTRAINT pk_adsys_type_piece_payement PRIMARY KEY (id, id_ag),
  CONSTRAINT fk_adsys_type_piece_payement FOREIGN KEY (libel) REFERENCES ad_str(id_str) ON DELETE CASCADE

);

--creation de la sequence adsys_type_piece_payement : Voir #782
DROP SEQUENCE IF EXISTS adsys_type_piece_payement_seq;
CREATE SEQUENCE "adsys_type_piece_payement_seq" start 1 increment 1 maxvalue 214748647 minvalue 1 cache 1 ;


-- ajout de l'opération dotation aux provisions
INSERT INTO ad_cpt_ope VALUES (271,'Dotation aux provisions',1,numAgc() );
INSERT INTO ad_cpt_ope_cptes VALUES (271,NULL, 'd', 2,numAgc() );
INSERT INTO ad_cpt_ope_cptes VALUES (271,NULL, 'c', 2,numAgc() );
-- ajout de l'opération 'Reprise sur provisions crédits'
INSERT INTO ad_cpt_ope VALUES (272,'Reprise sur provisions crédits',1,numAgc() );
INSERT INTO ad_cpt_ope_cptes VALUES (272,NULL, 'd', 2,numAgc() );
INSERT INTO ad_cpt_ope_cptes VALUES (272,NULL, 'c', 2,numAgc() );
--table adsys_etat_credits
-- ajout du champ taux de provision d'un etat de crédit
 ALTER TABLE adsys_etat_credits  ADD COLUMN taux float;
 -- ajout du champ  provisionne d'un etat de crédit
 ALTER TABLE adsys_etat_credits  ADD COLUMN provisionne boolean default false;

 --table dsys_etat_credit_cptes
 --ajout champ cpte_provision compte de provision
 ALTER TABLE adsys_etat_credit_cptes ADD COLUMN cpte_provision_credit text ;
 --ajout champ cpte_provision compte de provision
 ALTER TABLE adsys_etat_credit_cptes ADD COLUMN cpte_provision_debit text ;
 --ajout champ cpte_reprise_prov compte de reprise provision
 ALTER TABLE adsys_etat_credit_cptes ADD COLUMN cpte_reprise_prov text;

 --table ad_dcr
 --ajout champ prov_mnt montant de la provision
 ALTER TABLE ad_dcr ADD COLUMN prov_mnt numeric(30,6) DEFAULT 0 ;
 --ajout champ prov_date derniere date de la modifiaction du montant de provision
 ALTER TABLE ad_dcr ADD COLUMN prov_date date ;
 --ajout champ prov_is_calcul flag true si on peut calculer la provision du crédit
 ALTER TABLE ad_dcr ADD COLUMN prov_is_calcul boolean DEFAULT TRUE;
-- Mise à jour ad_dcr pour la gestion du déboursement progressif (voir #549)
-- Table ad_dcr --
ALTER TABLE ad_dcr ADD COLUMN cre_mnt_deb numeric(30,6) DEFAULT 0;
-- Mettre à jour le champs cre_mnt_deb, qui doit prendre la valeur du champs cre_mnt_octr, pour garder la cohérence de la base--
UPDATE ad_dcr SET cre_mnt_deb = cre_mnt_octr where etat IN (5,6,7,8,9,10,11,12);

 --table ad_agc
 -- ajout champ provision_auto calcul automatique des provisions des crédits chaque 31  decembre
 ALTER TABLE ad_agc ADD COLUMN provision_credit_auto boolean DEFAULT FALSE;

-- Table adsys_produit_credit --
-- Mise à jour adsys_produit_credit pour intégrer le paramétrage de l'ordre de remboursement
ALTER TABLE adsys_produit_credit ADD COLUMN ordre_remb smallint DEFAULT '1';
-- Mise à jour adsys_produit_credit pour intégrer le paramétrage de remboursement par compte de garantie
ALTER TABLE adsys_produit_credit ADD COLUMN remb_cpt_gar boolean DEFAULT FALSE;
-- Ajout du champ montant assurance dans la table adsys_produit_credit
ALTER TABLE adsys_produit_credit ADD COLUMN mnt_assurance numeric(30,6) DEFAULT 0;

-- Ces données sont insérés dans adsys_type_piece_payement pour garder la cohérence avec l'array $adsys["adsys_type_piece_payement"] de tableSys.php : Voir #782
-- la colonne id doit respecter l'ordre établi dans tableSys.php
DELETE FROM adsys_type_piece_payement;
insert into adsys_type_piece_payement (libel,id_ag) values(maketraductionlangsyst('Espèce'),NumAgc());            /* id=1 */
insert into adsys_type_piece_payement (libel,id_ag) values(maketraductionlangsyst('Chèque extérieur'),NumAgc());  /* id=2 */
insert into adsys_type_piece_payement (libel,id_ag) values(maketraductionlangsyst('Ordre de paiement'),NumAgc()); /* id=3 */
insert into adsys_type_piece_payement (libel,id_ag) values(maketraductionlangsyst('Autorisation de retrait sans livret/chèque'),NumAgc());    /* id=4 */
insert into adsys_type_piece_payement (libel,id_ag) values(maketraductionlangsyst('Travelers cheque'),NumAgc());  /* id=5 */
insert into adsys_type_piece_payement (libel,id_ag) values(maketraductionlangsyst('Mise à disposition'),NumAgc());/* id=6 */
insert into adsys_type_piece_payement (libel,id_ag) values(maketraductionlangsyst('Envoi argent'),NumAgc());      /* id=7 */
insert into adsys_type_piece_payement (libel,id_ag) values(maketraductionlangsyst('Reçu ADbanking'),NumAgc());    /* id=8 */
insert into adsys_type_piece_payement (libel,id_ag) values(maketraductionlangsyst('Facture'),NumAgc());           /* id=9 */
insert into adsys_type_piece_payement (libel,id_ag) values(maketraductionlangsyst('Extrait de compte'),NumAgc()); /* id=10 */
insert into adsys_type_piece_payement (libel,id_ag) values(maketraductionlangsyst('Reçu externe'),NumAgc());      /* id=11 */
insert into adsys_type_piece_payement (libel,id_ag) values(maketraductionlangsyst('Contrat'),NumAgc());           /* id=12 */
insert into adsys_type_piece_payement (libel,id_ag) values(maketraductionlangsyst('Bordereau'),NumAgc());         /* id=13 */
insert into adsys_type_piece_payement (libel,id_ag) values(maketraductionlangsyst('Opération Diverse'),NumAgc()); /* id=14 */
insert into adsys_type_piece_payement (libel,id_ag) values(maketraductionlangsyst('Chèque guichet'),NumAgc());    /* id=15 */
insert into adsys_type_piece_payement (libel,id_ag) values(maketraductionlangsyst('Ordre permanent'),NumAgc());   /* id=16 */


 --table ad_dcr
 -- ajout champ doss_repris pour signaler si un dossier est repris ou non
 ALTER TABLE ad_dcr ADD COLUMN doss_repris boolean DEFAULT FALSE;
  -- mise à jour du champs doss_repris pour tous les dossiers repris
  update ad_dcr set doss_repris='t' where id_doss IN (SELECT d.id_doss FROM ad_mouvement m, ad_ecriture e, ad_his h, ad_dcr d WHERE m.id_ecriture = e.id_ecriture and e.id_his= h.id_his and h.type_fonction = 503  and d.cre_id_cpte = m.cpte_interne_cli);

  --table ad_ecriture
  -- ajout du champ info_ecriture
  ALTER TABLE ad_ecriture ADD COlumn info_ecriture text ;

--renseigne le champs type_operation à partir de la table ad_cpt_ope
--Ce champs est ajouté pour déterminer le type de l'opération pendant la contre passation d'une écriture donnée
 UPDATE ad_ecriture e SET type_operation = (SELECT  o.type_operation from ad_cpt_ope o where o.libel_ope = e.libel_ecriture) ;

  -- Mise à jour ad_agc pour gestion de l'appartenance d'un client à plusieurs groupes (voir #1177)
-- Table ad_agc --
ALTER TABLE ad_agc ADD COLUMN nb_group_for_cust int4 DEFAULT 0;

--table adsys_profils
 -- ajout champ access_solde pour donner accès au solde des clients au profil
 ALTER TABLE adsys_profils ADD COLUMN access_solde boolean DEFAULT TRUE;

  -- creation de la table ad_flux_compta contenant les resumés  des ecritures et mouvements comptables
 CREATE TABLE ad_flux_compta AS SELECT b.id_his,id_client,type_fonction,infos,a.id_ecriture,libel_ecriture, date_comptable,type_operation,id_jou,id_exo, ref_ecriture,id_mouvement,compte,sens,devise,montant,a.id_ag,consolide FROM ad_mouvement a right outer join ad_ecriture b on a.id_ecriture=b.id_ecriture inner join ad_his c on b.id_his = c.id_his where  a.id_ag = b.id_ag and b.id_ag = c.id_ag order by a.compte, b.date_comptable, b.type_operation;
  -- creation du trigger pour alimenté la table ad_flux_compta contenant les resumés  des ecritures et mouvements comptables
 CREATE TRIGGER ad_mouvement_after_insert AFTER INSERT ON ad_mouvement FOR EACH ROW EXECUTE PROCEDURE  trig_insert_ad_flux_compta();

   -- Ajout du champs niveau pour enregistrer le niveau de chaque compte dans ad_cpt_comptable
-- Table ad_cpt_comptable --
ALTER TABLE ad_cpt_comptable ADD COLUMN niveau int4 DEFAULT 0;
-- Mise à jour du champs niveau
UPDATE ad_cpt_comptable SET niveau = getNiveau(num_cpte_comptable,NumAgc());

-- Nettoyage anciennes traductions, on supprime toutes les entrées en double
DELETE FROM ad_traductions WHERE id_str IN (SELECT t2.id_str FROM ad_traductions AS t1, ad_traductions AS t2 WHERE t1.traduction = t2.traduction AND t1.id_str > t2.id_str GROUP BY t2.id_str);

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
	"sens" text NOT NULL,
	"id_exo" int4 NOT NULL,
	"id_ag" int4 NOT NULL,
	PRIMARY KEY("id", "id_ag"),
  FOREIGN KEY (id_exo, id_ag) REFERENCES "ad_exercices_compta"
);
COMMENT ON TABLE "ad_declare_tva" IS 'Cette table stocke les informations sur les différentes déclarations de tva';
DROP SEQUENCE IF EXISTS ad_declare_tva_seq;
CREATE SEQUENCE "ad_declare_tva_seq" start 1 increment 1 maxvalue 214748647 minvalue 1 cache 1 ;

-- Table ad_agc --
ALTER TABLE ad_agc ADD COLUMN "cpte_tva_dec" text;
ALTER TABLE ad_agc ADD COLUMN "cpte_tva_rep" text;

ALTER TABLE ad_agc ADD COLUMN "num_tva" text;

-- Ajout des opérations de perception de tva
INSERT INTO ad_cpt_ope VALUES (473,'Paiement de tva déductible',1, numAgc());
INSERT INTO ad_cpt_ope VALUES (474,'Perception de tva collectée',1, numAgc());
INSERT INTO ad_cpt_ope VALUES (475,'Déclaration de tva',1, numAgc());
INSERT INTO ad_cpt_ope_cptes VALUES (473,NULL, 'c', 1, numAgc());
INSERT INTO ad_cpt_ope_cptes VALUES (473,NULL, 'd', 1, numAgc());
INSERT INTO ad_cpt_ope_cptes VALUES (474,NULL, 'c', 1, numAgc());
INSERT INTO ad_cpt_ope_cptes VALUES (474,NULL, 'd', 1, numAgc());
INSERT INTO ad_cpt_ope_cptes VALUES (475,NULL, 'c', 1, numAgc());
INSERT INTO ad_cpt_ope_cptes VALUES (475,NULL, 'd', 1, numAgc());

-- Table ad_cli --
-- Ajout d'un champ pour les commentaires sur le client : voir #1802.
ALTER TABLE ad_cli ADD COLUMN "commentaires_cli" text;

-- Table ad_brouillard --
-- Ajout des champs type_operation, is_taxe(flag, TRUE si écriture de taxe FALSE sinon) : voir #1830.
ALTER TABLE ad_brouillard ADD COLUMN "type_operation" int4  DEFAULT 0;
ALTER TABLE ad_brouillard ADD COLUMN "id_taxe" int4;
ALTER TABLE ad_brouillard ADD COLUMN "sens_taxe" text;

-- Table ad_agc
-- Mise à jour de l'id état précédent pour les états à radier et en perte dans adsys_etat_credits lors de la modification du champs passage_perte_automatique
CREATE TRIGGER ad_agc_passage_perte_automatique AFTER UPDATE on ad_agc FOR EACH ROW EXECUTE PROCEDURE  proc_ad_agc_passage_perte_automatique();

--Mise à jour table adsys_version_schema
DELETE from adsys_version_schema ;
INSERT INTO adsys_version_schema(version,date_version) VALUES('1.1.0','2010-02-24');
