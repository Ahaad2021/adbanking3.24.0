-- Script de mise à jour de la base de données de la version 3.0.x à la version 3.2

-- pour mettre à jour menus et écrans
DELETE FROM ecrans;
DELETE FROM menus;

DELETE FROM d_tableliste;
DELETE FROM tableliste;

-- Mise à jour ad_agc pour la réalisation de la garantie des crédits sains (voir #1859)
-- Table ad_agc --
ALTER TABLE ad_agc ADD COLUMN realisation_garantie_sain BOOLEAN DEFAULT 'false';

