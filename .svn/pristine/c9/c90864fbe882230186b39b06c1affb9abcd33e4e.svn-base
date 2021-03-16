-- Script de mise à jour de la base de données pour la version 8 de PostgreSQL

-- Meilleure gestion des constraintes et conditions ON DELETE
DROP TRIGGER suppr_trad ON menus;
ALTER TABLE menus DROP CONSTRAINT "$1";
ALTER TABLE menus ADD FOREIGN KEY (libel_menu) REFERENCES ad_str(id_str) ON DELETE CASCADE;
DROP TRIGGER suppr_trad ON d_tableliste;
ALTER TABLE d_tableliste DROP CONSTRAINT "$1";
ALTER TABLE d_tableliste DROP CONSTRAINT "$2";
ALTER TABLE d_tableliste ADD FOREIGN KEY (tablen) REFERENCES tableliste(ident) ON DELETE RESTRICT;
ALTER TABLE d_tableliste ADD FOREIGN KEY (nchmpl) REFERENCES ad_str(id_str) ON DELETE CASCADE;
DROP TRIGGER suppr_trad ON tableliste;
ALTER TABLE tableliste DROP CONSTRAINT "$1";
ALTER TABLE tableliste ADD FOREIGN KEY (noml) REFERENCES ad_str(id_str) ON DELETE CASCADE;
DROP TRIGGER suppr_trad ON adsys_type_piece_identite;
ALTER TABLE adsys_type_piece_identite DROP CONSTRAINT "$1";
ALTER TABLE adsys_type_piece_identite ADD FOREIGN KEY (libel) REFERENCES ad_str(id_str) ON DELETE CASCADE;
DROP TRIGGER suppr_trad ON adsys_langues_systeme;
ALTER TABLE adsys_langues_systeme DROP CONSTRAINT "$1";
ALTER TABLE adsys_langues_systeme ADD FOREIGN KEY (langue) REFERENCES ad_str(id_str) ON DELETE CASCADE;
DROP TRIGGER del_dans_ad_traductions ON ad_str;
ALTER TABLE ad_traductions DROP CONSTRAINT "$1";
ALTER TABLE ad_traductions DROP CONSTRAINT "$2";
ALTER TABLE ad_traductions ADD FOREIGN KEY (id_str) REFERENCES ad_str(id_str) ON DELETE CASCADE;
ALTER TABLE ad_traductions ADD FOREIGN KEY (langue) REFERENCES adsys_langues_systeme(code) ON DELETE CASCADE;

-- pour mettre à jour menus et écrans
DELETE FROM ecrans;
DELETE FROM menus;

DELETE FROM d_tableliste;
DELETE FROM tableliste;
