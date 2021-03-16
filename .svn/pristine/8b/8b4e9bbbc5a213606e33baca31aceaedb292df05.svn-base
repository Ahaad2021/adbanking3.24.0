CREATE OR REPLACE FUNCTION patch_ticket_437() RETURNS integer AS
$BODY$
DECLARE
var_libel_menu INTEGER;
var_libel_menu1 INTEGER;
var_libel_menu2 INTEGER;
output_result INTEGER;

BEGIN
RAISE INFO 'CREATION ECRANS/MENUS -->Rapports sur les opération diverses' ;
----------------------------------CREATION ECRANS/MENUS
var_libel_menu = (select maketraductionlangsyst('Rapport sur les opérations diverses '));
-- Menus Rapport sur les opération diverses 
IF NOT EXISTS (SELECT * FROM menus WHERE nom_menu = 'Rod') THEN
INSERT INTO menus(nom_menu, libel_menu, nom_pere, pos_hierarch, ordre, is_menu,fonction,is_cliquable)
VALUES ('Rod', var_libel_menu, 'Gen-6',3,11,'t',195,'f');
END IF;

var_libel_menu1 = (select maketraductionlangsyst('Saisie de critères '));
-- Menus Rapport sur les opération diverses 
IF NOT EXISTS (SELECT * FROM menus WHERE nom_menu = 'Rod-1') THEN
INSERT INTO menus(nom_menu, libel_menu, nom_pere, pos_hierarch, ordre, is_menu,is_cliquable)
VALUES ('Rod-1', var_libel_menu1, 'Rod',4,1,'f','f');
END IF;

var_libel_menu2 = (select maketraductionlangsyst('Génération du rapport '));
-- Menus Rapport sur les opération diverses 
IF NOT EXISTS (SELECT * FROM menus WHERE nom_menu = 'Rod-2') THEN
INSERT INTO menus(nom_menu, libel_menu, nom_pere, pos_hierarch, ordre, is_menu,is_cliquable)
VALUES ('Rod-2', var_libel_menu2, 'Rod',4,2,'f','f');
END IF;



--Creation ecrans

IF NOT EXISTS (SELECT * FROM ecrans WHERE nom_ecran = 'Rod-1') THEN
INSERT INTO ecrans(nom_ecran, fichier, nom_menu, fonction)
VALUES ('Rod-1','modules/guichet/rapport_oper_diver.php','Rod-1',195);
END IF;

IF NOT EXISTS (SELECT * FROM ecrans WHERE nom_ecran = 'Rod-2') THEN
INSERT INTO ecrans(nom_ecran, fichier, nom_menu, fonction)
VALUES ('Rod-2','modules/guichet/rapport_oper_diver.php','Rod-2',195);
END IF;

IF NOT EXISTS (SELECT * FROM ecrans WHERE nom_ecran = 'Rod-3') THEN
INSERT INTO ecrans(nom_ecran, fichier, nom_menu, fonction)
VALUES ('Rod-3','modules/guichet/rapport_oper_diver.php','Rod',195);
END IF;

return 1;

END;
$BODY$
LANGUAGE plpgsql VOLATILE COST 100;
ALTER FUNCTION patch_ticket_437()
OWNER TO adbanking;

SELECT patch_ticket_437();
DROP FUNCTION patch_ticket_437();
