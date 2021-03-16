-- Script de mise à jour de la base de données de la version 2.4 à la version 2.6

-- pour mettre à jour menus et écrans
DELETE FROM ecrans;
DELETE FROM menus;

DELETE FROM d_tableliste;
DELETE FROM tableliste;

--Regénérer les fonctions et les types
DROP FUNCTION PreleveFraisTenueCpt(INTEGER, TEXT, INTEGER);
DROP TYPE cpte_frais;
DROP FUNCTION PreleveInteretsDebiteurs(DATE, TEXT) ;
DROP TYPE cpte_deb;

-- Valeur par défaut de freq_paiement_cap
ALTER TABLE adsys_produit_credit ALTER column freq_paiement_cap SET DEFAULT '1';
UPDATE adsys_produit_credit SET freq_paiement_cap = '1' WHERE freq_paiement_cap = '0';

-- Ajout du mode de calcul intérêt 'Sur solde courant le plus bas' à la 3ème position de la table 'adsys_mode_calcul_int_epargne'. 
UPDATE adsys_produit_epargne SET mode_calcul_int = mode_calcul_int + 1 WHERE mode_calcul_int > 2;
UPDATE ad_cpt SET mode_calcul_int_cpte = mode_calcul_int_cpte + 1 where mode_calcul_int_cpte > 2;

-- Ajout du trigger de calcul du solde en temps réel
CREATE TRIGGER calcul_solde_temps_reel AFTER UPDATE ON ad_cpt FOR EACH ROW EXECUTE PROCEDURE trig_calcul_solde_temps_reel();

-- Frais de découvert en pourcentage
ALTER TABLE adsys_produit_epargne ADD COLUMN decouvert_frais_dossier numeric(30,6);
ALTER TABLE adsys_produit_epargne ADD COLUMN decouvert_frais_dossier_prc double precision;
ALTER TABLE adsys_produit_epargne ALTER COLUMN decouvert_frais set default 0;
ALTER TABLE adsys_produit_epargne ALTER COLUMN decouvert_frais_dossier set default 0;
ALTER TABLE adsys_produit_epargne ALTER COLUMN decouvert_frais_dossier_prc set default 0;

-- Annulation de découvert
ALTER TABLE adsys_produit_epargne ADD COLUMN decouvert_annul_auto boolean;
ALTER TABLE adsys_produit_epargne ADD COLUMN decouvert_validite smallint;
ALTER TABLE adsys_produit_epargne ALTER COLUMN decouvert_annul_auto set default false;
ALTER TABLE adsys_produit_epargne ALTER COLUMN decouvert_validite set default 0;
UPDATE adsys_produit_epargne SET decouvert_annul_auto = false;
UPDATE adsys_produit_epargne SET decouvert_validite = 0;
ALTER TABLE ad_cpt ADD COLUMN decouvert_date_util timestamp without time zone;
-- Ajout du trigger de suivi de l'utilisation du découvert
CREATE TRIGGER utilisation_decouvert AFTER UPDATE ON ad_cpt FOR EACH ROW EXECUTE PROCEDURE trig_utilisation_decouvert();

-- Impression de chèquier
INSERT INTO ad_cpt_ope VALUES (472, 'Perception des frais de chèquier',1);
INSERT INTO ad_cpt_ope_cptes VALUES (472, NULL, 'd', 1);
ALTER TABLE ad_cpt ADD COLUMN num_last_cheque integer;
ALTER TABLE ad_cpt ADD COLUMN etat_chequier smallint;
ALTER TABLE ad_cpt ADD COLUMN date_demande_chequier timestamp without time zone;
ALTER TABLE ad_cpt ADD COLUMN chequier_num_cheques smallint;
ALTER TABLE ad_cpt ALTER COLUMN num_last_cheque set default 0;
ALTER TABLE ad_cpt ALTER COLUMN etat_chequier set default 0;
ALTER TABLE ad_cpt ALTER COLUMN chequier_num_cheques set default 25;
UPDATE ad_cpt SET chequier_num_cheques = 25;
ALTER TABLE adsys_produit_epargne ADD COLUMN frais_chequier numeric(30,6);
ALTER TABLE adsys_produit_epargne ALTER COLUMN frais_chequier set default 0;

-- Ajout d'une colonne gi_categorie dans la table des clients (voir ticket #437)
ALTER TABLE ad_cli ADD COLUMN "gi_categorie" int;

-- Ajout de colonnes dans la table des clients pour la categorie de personne morale ASBL (voir ticket #436)
ALTER TABLE ad_cli ADD COLUMN "pm_date_constitution" timestamp;
ALTER TABLE ad_cli ADD COLUMN "pm_agrement_nature" text;
ALTER TABLE ad_cli ADD COLUMN "pm_agrement_autorite" text;
ALTER TABLE ad_cli ADD COLUMN "pm_agrement_numero" int;
ALTER TABLE ad_cli ADD COLUMN "pm_agrement_date" timestamp;

-- Ajout de la gestion des ODC pour le profil admin
INSERT INTO adsys_profils_axs(profil, fonction) VALUES (1, 472);
