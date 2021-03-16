-- Vider les tables ecrans/menus pour les mettre à jour par le fichier menus.sql 
TRUNCATE TABLE ecrans;
TRUNCATE TABLE menus;

-- Vider les tables tableliste/d_tableliste pour les mettre à jour par le fichier tableliste.sql 
TRUNCATE TABLE d_tableliste RESTART IDENTITY CASCADE;
TRUNCATE TABLE tableliste RESTART IDENTITY CASCADE;
