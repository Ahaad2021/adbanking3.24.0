-- Script de mise à jour de la base de données de la version 2.6 à la version 2.8

-- pour mettre à jour menus et écrans
DELETE FROM ecrans;
DELETE FROM menus;

DELETE FROM d_tableliste;
DELETE FROM tableliste;

--Regénérer les fonctions et les types
DROP FUNCTION PreleveFraisTenueCpt(INTEGER, TEXT, INTEGER);
DROP TYPE cpte_frais CASCADE;
DROP FUNCTION PreleveInteretsDebiteurs(DATE, TEXT) ;
DROP TYPE cpte_deb CASCADE;

-- Suppression des opérations comptables automatiques inutiles
DELETE FROM ad_cpt_ope_cptes WHERE type_operation = 170;
DELETE FROM ad_cpt_ope_cptes WHERE type_operation = 171;
DELETE FROM ad_cpt_ope_cptes WHERE type_operation = 180;
DELETE FROM ad_cpt_ope_cptes WHERE type_operation = 190;
DELETE FROM ad_cpt_ope_cptes WHERE type_operation = 191;
DELETE FROM ad_cpt_ope_cptes WHERE type_operation = 240;
DELETE FROM ad_cpt_ope_cptes WHERE type_operation = 250;
DELETE FROM ad_cpt_ope_cptes WHERE type_operation = 310;
DELETE FROM ad_cpt_ope_cptes WHERE type_operation = 354;
DELETE FROM ad_cpt_ope_cptes WHERE type_operation = 380;
DELETE FROM ad_cpt_ope_cptes WHERE type_operation = 390;
DELETE FROM ad_cpt_ope_cptes WHERE type_operation = 509;
DELETE FROM ad_cpt_ope WHERE type_operation = 170;
DELETE FROM ad_cpt_ope WHERE type_operation = 171;
DELETE FROM ad_cpt_ope WHERE type_operation = 180;
DELETE FROM ad_cpt_ope WHERE type_operation = 190;
DELETE FROM ad_cpt_ope WHERE type_operation = 191;
DELETE FROM ad_cpt_ope WHERE type_operation = 240;
DELETE FROM ad_cpt_ope WHERE type_operation = 250;
DELETE FROM ad_cpt_ope WHERE type_operation = 310;
DELETE FROM ad_cpt_ope WHERE type_operation = 354;
DELETE FROM ad_cpt_ope WHERE type_operation = 380;
DELETE FROM ad_cpt_ope WHERE type_operation = 390;
DELETE FROM ad_cpt_ope WHERE type_operation = 509;

-- Ajout opération
INSERT INTO ad_cpt_ope VALUES (354, 'Passage en perte solde compte épargne débiteur',1);
INSERT INTO ad_cpt_ope_cptes VALUES (354, NULL, 'c', 1);
INSERT INTO ad_cpt_ope_cptes VALUES (354, NULL, 'd', 0);

-- Initialisation du code agence
ALTER TABLE ad_agc ALTER COLUMN code_ville SET DEFAULT '0';
ALTER TABLE ad_agc ALTER COLUMN code_banque SET DEFAULT '0';
UPDATE ad_agc SET code_ville = '0' WHERE code_ville = '' OR code_ville IS NULL;
UPDATE ad_agc SET code_banque = '0' WHERE code_banque = '' OR code_banque IS NULL;

-- 
-- Crédit solidaire
-- ad_cli
ALTER TABLE ad_cli ADD COLUMN gs_responsable integer;
-- ad_grp_sol
CREATE TABLE "ad_grp_sol" (
        "id_grp_sol" int4 NOT NULL,
        "id_membre" int4 REFERENCES "ad_cli" NOT NULL
);
COMMENT ON COLUMN "ad_grp_sol"."id_grp_sol" IS 'id_client du groupe solidaire';
COMMENT ON COLUMN "ad_grp_sol"."id_membre" IS 'id_client du membre du groupe solidaire';
-- ad_agc
ALTER TABLE ad_agc ADD COLUMN gs_montant_droits_adhesion numeric(30,6);
ALTER TABLE ad_agc ALTER COLUMN gs_montant_droits_adhesion set default 0;
-- ad_dcr
ALTER TABLE ad_dcr ADD COLUMN gs_cat smallint;
ALTER TABLE ad_dcr ADD COLUMN id_dcr_grp_sol int4;
COMMENT ON COLUMN "ad_dcr"."id_dcr_grp_sol" IS 'id du dossier fictif du groupe solidaire';
-- séquence dans ad_dcr_grp_sol
CREATE SEQUENCE "ad_dcr_grp_sol_id_seq" start 1 increment 1 maxvalue 999999 minvalue 1 cache 1 ;
-- ad_dcr_grp_sol
CREATE TABLE "ad_dcr_grp_sol" (
	"id" int4 DEFAULT nextval('ad_dcr_grp_sol_id_seq'::text) NOT NULL,
        "id_dcr_grp_sol" int4 REFERENCES "ad_dcr",
        "id_membre" int4 REFERENCES "ad_cli" NOT NULL,
        "obj_dem" int4,
        "detail_obj_dem" text,
        "mnt_dem" numeric(30,6),
	"gs_cat" int4 NOT NULL,
	PRIMARY KEY ("id")
);
-- adsys_produit_credit
ALTER TABLE adsys_produit_credit ADD COLUMN gs_cat smallint;

-- Restauration des opérations pour rééchelonnement ou moratoire
INSERT INTO ad_cpt_ope VALUES (390, 'Augmentation capital suite à rééch/mor sur crédit ',1);
INSERT INTO ad_cpt_ope_cptes VALUES (390, NULL, 'd', 2);
INSERT INTO ad_cpt_ope_cptes VALUES (390, NULL, 'c', 0);
DELETE FROM ad_cpt_ope_cptes WHERE type_operation = 430;
DELETE FROM ad_cpt_ope WHERE type_operation = 430;

-- Augmentation de la limite supérieure pour les numéros de séquence d'ad_mouvement
ALTER SEQUENCE ad_mouvement_seq MAXVALUE 2147483647;
ALTER SEQUENCE ad_ecriture_seq MAXVALUE 2147483647;

--Ajout d'un champs dans adsys_produit_credit 
ALTER TABLE adsys_produit_credit ADD max_jours_compt_penalite INTEGER; 

-- Les ad_cli.gi_categorie de valeur 2 et 3 doivent être déplacés en ad_cli.statut_juridique 4
-- et une entrée correspondante dans ad_grp_sol doit être créée
UPDATE ad_cli SET statut_juridique = '4' WHERE statut_juridique = '3' AND gi_categorie > 1;

-- Plus tard, il faudra aussi supprimer la colonne gi_categorie qui est maintenant devenue inutile

-- On recopie les droits d'adhésion GI dans GS
UPDATE ad_agc SET gs_montant_droits_adhesion = gi_montant_droits_adhesion;
-- Ajout d'un champs code antenne dans la table ad_agc
ALTER TABLE ad_agc ADD column code_antenne text;
ALTER TABLE ad_agc  ALTER COLUMN code_antenne SET DEFAULT NULL;

-- Ajout d'un champs tranche part sociale dans ad_agc
ALTER TABLE  ad_agc ADD column tranche_part_sociale boolean;

--Ajout d'un champ dans adsys_produit_epargne
ALTER TABLE adsys_produit_epargne ADD COLUMN seuil_rem_dav numeric(30,6) ;
ALTER TABLE adsys_produit_epargne ALTER COLUMN seuil_rem_dav SET DEFAULT 0 ;

-- Mise à jour du Seuil des produits d''épargne existants  
UPDATE adsys_produit_epargne set seuil_rem_dav = 0 WHERE seuil_rem_dav IS NULL; 

--Ajout d'un champ dans ad_cpt
ALTER TABLE ad_cpt ADD COLUMN solde_part_soc_restant numeric(30,6) ;
ALTER TABLE ad_cpt ALTER COLUMN solde_part_soc_restant SET DEFAULT 0 ;

-- Ajout d'une opération pour le transfert d'écritures entre comptes comptables
INSERT INTO ad_cpt_ope VALUES (275, 'Transfert du solde lié à un état de crédit d''un produit de crédit',1);
INSERT INTO ad_cpt_ope_cptes VALUES (275, NULL, 'd', 2);
INSERT INTO ad_cpt_ope_cptes VALUES (275, NULL, 'c', 2);

--Ajout d'un champ mnt_min_cpte dans ad_cpt
ALTER TABLE ad_cpt ADD COLUMN mnt_min_cpte numeric(30,6) ;
ALTER TABLE ad_cpt ALTER COLUMN mnt_min_cpte SET DEFAULT 0 ;
UPDATE ad_cpt SET mnt_min_cpte = (SELECT mnt_min FROM adsys_produit_epargne where id_prod = id);
UPDATE ad_cpt SET mnt_min_cpte = 0 WHERE mnt_min_cpte IS NULL; 

DELETE FROM adsys_profils_axs WHERE fonction IN(205,206,212) AND profil IN(SELECT id FROM adsys_profils WHERE guichet ='t'); 

-- Création d''une table association Produits de Crédit et Statuts juridique 
CREATE TABLE "adsys_asso_produitcredit_statjuri" 
	( 
 	"id_pc" integer REFERENCES "adsys_produit_credit" NOT NULL, 
 	"ident_sj" integer NOT NULL 
 	); 
COMMENT ON TABLE "adsys_asso_produitcredit_statjuri" IS 'Produits de Crédit et Statuts juridique associés';

-- Initialisation de la table d''association des produits de crédit aux statuts juridique
-- Pour une Base 2.6 -> 2.8
	-- Association de Produits de crédits existant avec le statut Personne Physique
	INSERT INTO adsys_asso_produitcredit_statjuri SELECT id,'1' FROM adsys_produit_credit;
	-- Association de Produits de crédits existant avec le statut Personne morale
	INSERT INTO adsys_asso_produitcredit_statjuri SELECT id,'2' FROM adsys_produit_credit;
	-- Association de Produits de crédits existant avec le statut Groupe Informel
	INSERT INTO adsys_asso_produitcredit_statjuri SELECT id,'3' FROM adsys_produit_credit;

-- Suppression de la colonne gi_categorie dans la table des clients (voir ticket #666) 
ALTER TABLE ad_cli DROP COLUMN "gi_categorie";

-- Mise à jour des triggers : gestion ordre d'appel et évenement déclencheur 
DROP TRIGGER preleve_frais_attente ON ad_cpt;
CREATE TRIGGER a_preleve_frais_attente BEFORE UPDATE ON ad_cpt FOR EACH ROW EXECUTE PROCEDURE trig_preleve_frais_attente();   
DROP TRIGGER utilisation_decouvert ON ad_cpt;
CREATE TRIGGER b_utilisation_decouvert BEFORE UPDATE ON ad_cpt FOR EACH ROW EXECUTE PROCEDURE trig_utilisation_decouvert();
DROP TRIGGER calcul_solde_temps_reel ON ad_cpt;
CREATE TRIGGER c_calcul_solde_temps_reel BEFORE UPDATE ON ad_cpt FOR EACH ROW EXECUTE PROCEDURE trig_calcul_solde_temps_reel();

-- Optimisation du batch (voir ticket 800)
-- La fonctionalité EFT est utilisée uniquement par la TMB
alter table ad_extrait_cpte alter column  eft_id_extrait   drop not null;
alter table ad_extrait_cpte alter column  eft_id_mvt       drop not null;
alter table ad_extrait_cpte alter column  eft_id_client    drop not null;
alter table ad_extrait_cpte alter column  eft_annee_oper   drop not null;
alter table ad_extrait_cpte alter column  eft_dern_solde   drop not null;
alter table ad_extrait_cpte alter column  eft_dern_date    drop not null;
alter table ad_extrait_cpte alter column  eft_nouv_solde   drop not null;
alter table ad_extrait_cpte alter column  eft_sceau        drop not null;
CREATE INDEX i_ad_extrait_cpte_cpte on ad_extrait_cpte(id_cpte);

