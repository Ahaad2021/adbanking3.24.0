-- #430 - Creation des ecrans annulation raccourcissement

CREATE OR REPLACE FUNCTION patch_ticket_430() RETURNS integer AS
$BODY$
DECLARE
var_libel_menu INTEGER;

BEGIN

var_libel_menu = (select maketraductionlangsyst('Annulation raccourcissement'));

-- Ecrans 
IF NOT EXISTS (SELECT * FROM menus WHERE nom_menu = 'Ald') THEN
INSERT INTO menus(nom_menu, libel_menu, nom_pere, pos_hierarch, ordre, is_menu,is_cliquable)
VALUES ('Ald-1', var_libel_menu, 'Mec',6,5,'f','f');
END IF;


-- Menus
IF NOT EXISTS (SELECT * FROM ecrans WHERE nom_ecran = 'Ald-1') THEN
INSERT INTO ecrans(nom_ecran, fichier, nom_menu, fonction)
VALUES ('Ald-1','modules/credit/annul_raccourci_duree.php','Ald-1',150);
END IF;

IF NOT EXISTS (SELECT * FROM ecrans WHERE nom_ecran = 'Ald-2') THEN
INSERT INTO ecrans(nom_ecran, fichier, nom_menu, fonction)
VALUES ('Ald-2','modules/credit/annul_raccourci_duree.php','Ald-1',150);
END IF;

IF NOT EXISTS (SELECT * FROM ecrans WHERE nom_ecran = 'Ald-3') THEN
INSERT INTO ecrans(nom_ecran, fichier, nom_menu, fonction)
VALUES ('Ald-3','modules/credit/annul_raccourci_duree.php','Ald-1',150);
END IF;

return 1;

END;
$BODY$
LANGUAGE plpgsql VOLATILE COST 100;
ALTER FUNCTION patch_ticket_430()
OWNER TO adbanking;

SELECT patch_ticket_430();
DROP FUNCTION patch_ticket_430();
