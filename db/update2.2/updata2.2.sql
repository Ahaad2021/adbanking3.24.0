-- Script de mise à jour de la base de données de la version 2.0.x à la version 2.2

-- pour mettre à jour menus et écrans
DELETE FROM ecrans;
DELETE FROM menus;

DELETE FROM d_tableliste;
DELETE FROM tableliste;

--Regénérer les fonctions et les types
DROP FUNCTION PreleveInteretsDebiteurs(DATE, TEXT) ;
DROP TYPE cpte_deb;
DROP FUNCTION PreleveFraisTenueCpt(INTEGER, TEXT, TEXT, INTEGER, INTEGER);
DROP TYPE cpte_frais;

--Suppression de tables obsolètes
DROP SEQUENCE "ad_chq_id_seq";
DROP TABLE "ad_chq";

--Changements 2.0 -> 2.0.1
ALTER TABLE ad_cli ALTER COLUMN nbre_credits SET DEFAULT 0;

--Migration du champ assurance de la table des clients à la table des dossiers de crédits
--Récupérer tous les clients ayant déjà de l'assurance sur leurs crédits. 
--On suppose ici que les clients n'ont pour l'instant qu'un seul crédit déboursé ou en cours de rééchelonnement/moratoire

select a.id_doss into mytemp
from ad_dcr a, ad_cli b where b.assurances_cre ='t' AND a.id_client=b.id_client
AND (a.etat = 5 OR a.etat=7);

ALTER TABLE ad_cli DROP COLUMN assurances_cre;
ALTER TABLE ad_dcr ADD COLUMN assurances_cre BOOL;
ALTER TABLE adsys_produit_credit ADD column approbation_obli bool;
ALTER TABLE adsys_produit_credit ALTER column approbation_obli set default 't';
ALTER TABLE adsys_produit_credit DROP COLUMN epar_obli;
ALTER TABLE ad_dcr DROP COLUMN remb_der_ech_gar;

UPDATE ad_dcr SET assurances_cre = 't' where id_doss in 
	(SELECT id_doss FROM mytemp);

DROP TABLE mytemp;

-- Ajout des champs pour la photo et pour la signature des clients
ALTER TABLE "ad_cli" ADD COLUMN "photo" OID;
ALTER TABLE "ad_cli" ADD COLUMN "signature" OID;

-- Ajout des champs pour la photo et pour la signature des relations
ALTER TABLE "ad_rel" ADD COLUMN "photo" OID;
ALTER TABLE "ad_rel" ADD COLUMN "signature" OID;

/********************** IMPLEMENTATION DES GARANTIES MULTIPLES **************************/

/* CREATION DES SEQUENCES */
CREATE SEQUENCE "adsys_types_biens_id_seq" start 1 increment 1 maxvalue 999999 minvalue 1 cache 1 ;
CREATE SEQUENCE "ad_biens_id_bien_seq" start 1 increment 1 maxvalue 999999 minvalue 1 cache 1 ;	
CREATE SEQUENCE "ad_gar_id_gar_seq" start 1 increment 1 maxvalue 999999 minvalue 1 cache 1 ;	

/* CREATION DE LA TABLE  adsys_types_biens */
CREATE TABLE "adsys_types_biens" (
	"id" int4 DEFAULT nextval('adsys_types_biens_id_seq'::text) NOT NULL,
	"libel" text NOT NULL,
	PRIMARY KEY("id")
);
COMMENT ON TABLE "adsys_types_biens" IS 'Cette table permet de définir les types de biens matériels qui sont acceptés par l''IM';
COMMENT ON COLUMN "adsys_types_biens"."id" IS 'Identifiant du type de bien';
COMMENT ON COLUMN "adsys_types_biens"."libel" IS 'le libellé du type de bien ';


/* CREATION DE LA TABLE  ad_biens */
CREATE TABLE "ad_biens" (
	"id_bien" int4 DEFAULT nextval('ad_biens_id_bien_seq'::text) NOT NULL,
	"id_client" int4 REFERENCES ad_cli(id_client) NOT NULL,
	"type_bien" int4 REFERENCES adsys_types_biens(id) NOT NULL,
	"description" text NOT NULL,
	"valeur_estimee" numeric(30,6) NOT NULL DEFAULT 0,
	"devise_valeur" char(3) REFERENCES devise(code_devise) NOT NULL,
	"piece_just" text,
	"remarque" text,
	PRIMARY KEY("id_bien")
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

/* CREATION DE LA TABLE ad_gar */
CREATE TABLE "ad_gar" (
	"id_gar" int4 DEFAULT nextval('ad_gar_id_gar_seq'::text) NOT NULL,
	"id_doss" int4 REFERENCES ad_dcr(id_doss) NOT NULL,
	"type_gar" int4 NOT NULL,
	"gar_mat_id_bien" int4 REFERENCES ad_biens(id_bien),
	"gar_num_id_cpte_prelev" int4 REFERENCES ad_cpt(id_cpte),
	"gar_num_id_cpte_nantie" int4 REFERENCES ad_cpt(id_cpte),
	"etat_gar" int4 NOT NULL,
	"montant_vente" numeric(30,6) NOT NULL DEFAULT 0,
	"devise_vente" char(3) REFERENCES devise(code_devise) NOT NULL,
	PRIMARY KEY("id_gar")
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

/* UPDATE DE LA TABLE adsys_produit_credit */
ALTER TABLE adsys_produit_credit RENAME COLUMN prc_gar TO prc_gar_num;
ALTER TABLE adsys_produit_credit ADD COLUMN prc_gar_mat double precision;
ALTER TABLE adsys_produit_credit ALTER COLUMN prc_gar_mat SET DEFAULT 0;
ALTER TABLE adsys_produit_credit ADD COLUMN prc_gar_tot double precision;
UPDATE adsys_produit_credit SET prc_gar_tot = prc_gar_num;
COMMENT ON COLUMN "adsys_produit_credit"."prc_gar_num" IS '%age de garantie numéraire exigée';
COMMENT ON COLUMN "adsys_produit_credit"."prc_gar_mat" IS '%age de garantie matérielle exigée';
COMMENT ON COLUMN "adsys_produit_credit"."prc_gar_tot" IS '%age total de garanties (matérielles et numéraires) exigé ';

/* UPDATE DE LA TABLE ad_dcr */	
ALTER TABLE ad_dcr ADD COLUMN gar_tot numeric(30,6);
ALTER TABLE ad_dcr ALTER COLUMN gar_tot	SET DEFAULT 0;
COMMENT ON COLUMN "ad_dcr"."gar_tot" IS 'Montant total mobilisé au titre de garantie';
ALTER TABLE ad_dcr DROP COLUMN gar_mat;
ALTER TABLE ad_dcr ADD COLUMN gar_mat numeric(30,6);
ALTER TABLE ad_dcr ALTER COLUMN gar_mat	SET DEFAULT 0;
ALTER TABLE ad_dcr ADD COLUMN cpt_gar_encours integer REFERENCES ad_cpt;

/* La reprise des garanties numéraires pour les dossiers en attente de décision, acceptés, déboursés, soldés ou en attente de rééchel */	

-- GARANTIES NUMERAIRES ENCOURS 
-- S'il y a des garanties encours, considérer le compte nantie existant comme le compte des garanties encours */
UPDATE ad_dcr SET cpt_gar_encours = cre_cpt_epargne_nantie WHERE gar_num_encours > 0;

-- diminuer le solde du compte des garanties numéraires bloquées au début
UPDATE ad_cpt SET solde = solde - (select gar_num from ad_dcr WHERE cre_cpt_epargne_nantie = id_cpte) WHERE id_cpte in (select cre_cpt_epargne_nantie from ad_dcr where gar_num_encours > 0 AND gar_num > 0 AND etat IN (1,2,5,7)); 

-- ajout des garanties numéraires encours dans ad_gar
INSERT INTO ad_gar (type_gar,etat_gar,id_doss,gar_num_id_cpte_prelev,gar_num_id_cpte_nantie,montant_vente,devise_vente ) SELECT 1,1,id_doss,NULL,cre_cpt_epargne_nantie,(SELECT solde FROM ad_cpt WHERE id_cpte=cre_cpt_epargne_nantie),devise FROM ad_dcr, adsys_produit_credit WHERE gar_num_encours > 0 AND etat IN (1,2,5,7) AND id_prod=id;


-- GARANTIES NUMERAIRES BLOQUEES AU DEBUT
-- Création des comptes nanties pour les garanties bloquées au début
INSERT INTO ad_cpt (id_titulaire,date_ouvert,utilis_crea, etat_cpte,solde,solde_calcul_interets,num_cpte,num_complet_cpte,id_prod,dat_prolongation,dat_date_fin,dat_num_certif,dat_nb_prolong,dat_decision_client,devise) SELECT id_client,NULL,1,1,gar_num,0,(SELECT MaxIdCpte(id_client)),(SELECT makeNumCpletCpte(id_client)),4,'f',NULL,NULL,0,'f',devise FROM ad_dcr ,adsys_produit_credit WHERE gar_num > 0 AND etat IN (1,2,5,7) AND id_prod=id ;

-- Insertion dans garanties numéraires bloquées au début ad_gar
INSERT INTO ad_gar (type_gar,etat_gar,id_doss,gar_num_id_cpte_prelev,gar_num_id_cpte_nantie,montant_vente,devise_vente ) SELECT 1,2,id_doss,cpt_prelevement_garantie,(SELECT Max(id_cpte) FROM ad_cpt WHERE id_titulaire=id_client),gar_num,devise FROM ad_dcr, adsys_produit_credit WHERE gar_num > 0 AND etat IN (1,2,5,7) AND id_prod=id;


-- MISE A JOUR DES ETATS DES GARANTIES 
-- Mettre l'état des garanties bloquées au début à 'Mobilisé' pour les dossiers dont les fonds sont déboursés ou en attente de réech  */
UPDATE ad_gar SET etat_gar=3 WHERE etat_gar=2 AND id_doss IN (SELECT id_doss FROM ad_dcr WHERE etat=5 OR etat=7); 


-- Suppression de champs 
ALTER TABLE ad_dcr DROP COLUMN cpt_prelevement_garantie;
ALTER TABLE ad_dcr DROP COLUMN cre_cpt_epargne_nantie;


/******************** FIN GARANTIES MULTIPLES ***********************/

-- Gestion du différé et paiement des intérêts :
-- dans adsys_produit_credit : renommage du champ 'differe_jour' en 'differe_jours_max' ajout des champs 'differe_ech_max' , 'freq_paiement_cap'
-- dans ad_dcr : renommage du champ 'differe' en 'differe_jours' et ajout du champ 'differe_ech'
ALTER TABLE adsys_produit_credit RENAME column differe_jour TO differe_jours_max;
ALTER TABLE adsys_produit_credit ADD column differe_ech_max integer;
ALTER TABLE adsys_produit_credit ALTER column differe_ech_max SET DEFAULT '0';
ALTER TABLE adsys_produit_credit ADD column freq_paiement_cap integer;
ALTER TABLE adsys_produit_credit ALTER column freq_paiement_cap SET DEFAULT '1';
UPDATE adsys_produit_credit SET differe_ech_max = '0', freq_paiement_cap = '1';
ALTER TABLE ad_dcr RENAME column differe TO differe_jours;
ALTER TABLE ad_dcr ADD column differe_ech integer;

/******************** FRAIS EN ATTENTE *******************************/
CREATE TABLE "ad_frais_attente" (
	"id_cpte" int4 REFERENCES ad_cpt(id_cpte) NOT NULL,
	"date_frais" timestamp NOT NULL,
	"type_frais" int4  REFERENCES ad_cpt_ope(type_operation) NOT NULL,
	"montant" numeric(30,6) NOT NULL,
	PRIMARY KEY("id_cpte", "date_frais", "type_frais")
);
COMMENT ON TABLE "ad_frais_attente" IS 'Cette table stocke pour chaque compte client concerné les frais en attente de perception';

CREATE TRIGGER preleve_frais_attente AFTER UPDATE ON ad_cpt FOR EACH ROW EXECUTE PROCEDURE trig_preleve_frais_attente();

