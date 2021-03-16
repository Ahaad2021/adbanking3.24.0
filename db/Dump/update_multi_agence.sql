CREATE OR REPLACE FUNCTION ticket_AT_95() RETURNS INT AS
$$
DECLARE
output_result INTEGER = 1;

BEGIN


RAISE NOTICE 'DEBUT - INSERTION MENU';


IF NOT EXISTS (SELECT nom_menu FROM menus WHERE nom_menu='Ope-11') THEN
	INSERT INTO menus(nom_menu, libel_menu, nom_pere, pos_hierarch, ordre, is_menu,fonction, is_cliquable) VALUES ('Ope-11', maketraductionlangsyst('Opération en déplacé'), 'Gen-6', 3,8,true,193,true);
END IF;

IF NOT EXISTS (SELECT nom_menu FROM menus WHERE nom_menu='Tnm-1') THEN
	INSERT INTO menus(nom_menu, libel_menu, nom_pere, pos_hierarch, ordre, is_menu,fonction, is_cliquable) VALUES ('Tnm-1', maketraductionlangsyst('Traitements de nuit Multi-Agence'), 'Gen-7', 3,10,true,213,true);
END IF;

IF NOT EXISTS (SELECT nom_menu FROM menus WHERE nom_menu='Ama-1') THEN
	INSERT INTO menus(nom_menu, libel_menu, nom_pere, pos_hierarch, ordre, is_menu,fonction, is_cliquable) VALUES ('Ama-1', maketraductionlangsyst('Visualisation des opérations en déplacé'), 'Gen-6', 3,9,true,194,true);
END IF;

RAISE NOTICE 'FIN - INSERTION MENU';
RAISE NOTICE 'DEBUT - INSERTION DES ECRANS';

IF NOT EXISTS (SELECT nom_ecran FROM ecrans WHERE nom_ecran='Ope-11') THEN
	INSERT INTO ecrans(nom_ecran, fichier, nom_menu, fonction) VALUES ('Ope-11', 'ad_ma/app/views/epargne/operation_deplace.php', 'Ope-11', 193);
END IF;

IF NOT EXISTS (SELECT nom_ecran FROM ecrans WHERE nom_ecran='Ope-12') THEN
	INSERT INTO ecrans(nom_ecran, fichier, nom_menu, fonction) VALUES ('Ope-12', 'ad_ma/app/views/epargne/operation_deplace.php', 'Ope-11', 193);
END IF;

IF NOT EXISTS (SELECT nom_ecran FROM ecrans WHERE nom_ecran='Ope-13') THEN
	INSERT INTO ecrans(nom_ecran, fichier, nom_menu, fonction) VALUES ('Ope-13', 'ad_ma/app/views/epargne/operation_deplace.php', 'Ope-11', 193);
END IF;

IF NOT EXISTS (SELECT nom_ecran FROM ecrans WHERE nom_ecran='Rcp-11') THEN
	INSERT INTO ecrans(nom_ecran, fichier, nom_menu, fonction) VALUES ('Rcp-11', 'ad_ma/app/views/epargne/retrait_compte.php', 'Ope-11', 92);
END IF;

IF NOT EXISTS (SELECT nom_ecran FROM ecrans WHERE nom_ecran='Rcp-21') THEN
	INSERT INTO ecrans(nom_ecran, fichier, nom_menu, fonction) VALUES ('Rcp-21', 'ad_ma/app/views/epargne/retrait_compte.php', 'Ope-11', 92);
END IF;

IF NOT EXISTS (SELECT nom_ecran FROM ecrans WHERE nom_ecran='Rcp-31') THEN
	INSERT INTO ecrans(nom_ecran, fichier, nom_menu, fonction) VALUES ('Rcp-31', 'ad_ma/app/views/epargne/retrait_compte.php', 'Ope-11', 92);
END IF;

IF NOT EXISTS (SELECT nom_ecran FROM ecrans WHERE nom_ecran='Rcp-41') THEN
	INSERT INTO ecrans(nom_ecran, fichier, nom_menu, fonction) VALUES ('Rcp-41', 'ad_ma/app/views/epargne/retrait_compte.php', 'Ope-11', 92);
END IF;

IF NOT EXISTS (SELECT nom_ecran FROM ecrans WHERE nom_ecran='Dcp-11') THEN
	INSERT INTO ecrans(nom_ecran, fichier, nom_menu, fonction) VALUES ('Dcp-11', 'ad_ma/app/views/epargne/depot_compte.php', 'Ope-11', 93);
END IF;

IF NOT EXISTS (SELECT nom_ecran FROM ecrans WHERE nom_ecran='Dcp-21') THEN
	INSERT INTO ecrans(nom_ecran, fichier, nom_menu, fonction) VALUES ('Dcp-21', 'ad_ma/app/views/epargne/depot_compte.php', 'Ope-11', 93);
END IF;

IF NOT EXISTS (SELECT nom_ecran FROM ecrans WHERE nom_ecran='Dcp-31') THEN
	INSERT INTO ecrans(nom_ecran, fichier, nom_menu, fonction) VALUES ('Dcp-31', 'ad_ma/app/views/epargne/depot_compte.php', 'Ope-11', 93);
END IF;

IF NOT EXISTS (SELECT nom_ecran FROM ecrans WHERE nom_ecran='Dcp-41') THEN
	INSERT INTO ecrans(nom_ecran, fichier, nom_menu, fonction) VALUES ('Dcp-41', 'ad_ma/app/views/epargne/depot_compte.php', 'Ope-11', 93);
END IF;

IF NOT EXISTS (SELECT nom_ecran FROM ecrans WHERE nom_ecran='Ama-1') THEN
	INSERT INTO ecrans(nom_ecran, fichier, nom_menu, fonction) VALUES ('Ama-1', 'ad_ma/app/views/audit/audit_ma.php', 'Ama-1', 194);
END IF;

IF NOT EXISTS (SELECT nom_ecran FROM ecrans WHERE nom_ecran='Ama-2') THEN
	INSERT INTO ecrans(nom_ecran, fichier, nom_menu, fonction) VALUES ('Ama-2', 'ad_ma/app/views/audit/audit_ma.php', 'Ama-1', 194);
END IF;

IF NOT EXISTS (SELECT nom_ecran FROM ecrans WHERE nom_ecran='Ama-3') THEN
	INSERT INTO ecrans(nom_ecran, fichier, nom_menu, fonction) VALUES ('Ama-3', 'ad_ma/app/views/audit/audit_ma.php', 'Ama-1', 194);
END IF;

IF NOT EXISTS (SELECT nom_ecran FROM ecrans WHERE nom_ecran='Tnm-1') THEN
	INSERT INTO ecrans(nom_ecran, fichier, nom_menu, fonction) VALUES ('Tnm-1', 'ad_ma/batch/batch_ma.php', 'Tnm-1', 213);
END IF;

IF NOT EXISTS (SELECT nom_ecran FROM ecrans WHERE nom_ecran='Tnm-2') THEN
	INSERT INTO ecrans(nom_ecran, fichier, nom_menu, fonction) VALUES ('Tnm-2', 'ad_ma/batch/batch_ma.php', 'Tnm-1', 213);
END IF;

IF NOT EXISTS (SELECT nom_ecran FROM ecrans WHERE nom_ecran='Tnm-3') THEN
	INSERT INTO ecrans(nom_ecran, fichier, nom_menu, fonction) VALUES ('Tnm-3', 'ad_ma/batch/batch_ma.php', 'Tnm-1', 213);
END IF;

RAISE NOTICE 'FIN - INSERTION DES ECRANS';

 RETURN output_result;
END;
$$
LANGUAGE plpgsql;

SELECT ticket_AT_95();
--DROP FUNCTION ticket_AT_95();
