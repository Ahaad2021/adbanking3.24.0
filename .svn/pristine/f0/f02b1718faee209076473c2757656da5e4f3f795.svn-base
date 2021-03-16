---------------------------------------- trac #592 / pp #178 p1 p2---------------------------------------------------------------------------------
---------------------------------------------------------------------------------------------------------------------------------------------------------------
---------------------------------------------------FONCTION :MAJ NOUVEAU CHAMPS Assurance commission dans ad_dcr
---------------------------------------------------------------------------------------------------------------------------------------------------------------
CREATE OR REPLACE FUNCTION nouveau_champs_commission_assurance()  RETURNS INT AS
$$
DECLARE
	output_result INTEGER = 1;

BEGIN
	RAISE INFO 'creation nouveau champs assurance $ commission dans la table ad_dcr ' ;
	-- Check if field "mnt_ass" exist in table "ad_dcr"
	IF NOT EXISTS(SELECT * FROM information_schema.columns WHERE table_name = 'ad_dcr' AND column_name = 'mnt_assurance') THEN
		ALTER TABLE ad_dcr ADD COLUMN mnt_assurance numeric(30,6) DEFAULT 0.000000;
		output_result := 2;
	END IF;
	--insert into "d_tableliste" if notExist
	IF NOT EXISTS(SELECT * FROM d_tableliste WHERE nchmpc = 'mnt_assurance') THEN

		INSERT INTO d_tableliste(ident, tablen, nchmpc, nchmpl, isreq, ref_field, type, onslct, ispkey, traduit) VALUES ((select max(ident) from d_tableliste)+1, (select ident from tableliste where nomc like 'ad_dcr' order by ident desc limit 1), 'mnt_assurance', maketraductionlangsyst('Montant assurance'), false, NULL, 'mnt', false, false, false);
		output_result := 2;
	END IF;


	-- Check if field "mnt_com" exist in table "ad_dcr"
	IF NOT EXISTS(SELECT * FROM information_schema.columns WHERE table_name = 'ad_dcr' AND column_name = 'mnt_commission') THEN
		ALTER TABLE ad_dcr ADD COLUMN mnt_commission numeric(30,6) DEFAULT 0.000000;
		output_result := 2;
	END IF;
	--insert into "d_tableliste" if notExist
	IF NOT EXISTS(SELECT * FROM d_tableliste WHERE nchmpc = 'mnt_commission') THEN

		INSERT INTO d_tableliste(ident, tablen, nchmpc, nchmpl, isreq, ref_field, type, onslct, ispkey, traduit) VALUES ((select max(ident) from d_tableliste)+1, (select ident from tableliste where nomc like 'ad_dcr' order by ident desc limit 1), 'mnt_commission', maketraductionlangsyst('Montant commission'), false, NULL, 'mnt', false, false, false);
		output_result := 2;
	END IF;

	RETURN output_result;

END;
$$
LANGUAGE plpgsql;

SELECT nouveau_champs_commission_assurance();
DROP FUNCTION nouveau_champs_commission_assurance();

--	ALTER TABLE ad_dcr DROP COLUMN mnt_commission;
--	ALTER TABLE ad_dcr DROP COLUMN mnt_assurance ;

------------------------------------fin script ticket pp178/ trac #592

--------------------------------------- Start Ticket #593 (PP #178) :  ---------------------------------------

CREATE OR REPLACE FUNCTION ticket_593() RETURNS INT AS
$$
DECLARE

output_result INTEGER = 1;

BEGIN

	RAISE NOTICE 'START';

	--------------------------------------------
	-- Clôturer la ligne de crédit

	IF NOT EXISTS(SELECT nom_menu FROM menus WHERE nom_menu='LCdr') THEN
		INSERT INTO menus (nom_menu, libel_menu, nom_pere, pos_hierarch, ordre, is_menu,fonction, is_cliquable) VALUES ('LCdr', maketraductionlangsyst('Clôturer la ligne de crédit'), 'Lcr-1', 6, 11, true, 610, true);
	END IF;
	
	-- Sub menus
	IF NOT EXISTS(SELECT nom_menu FROM menus WHERE nom_menu='LCdr-1') THEN
		INSERT INTO menus (nom_menu, libel_menu, nom_pere, pos_hierarch, ordre, is_menu,fonction, is_cliquable) VALUES ('LCdr-1', maketraductionlangsyst('Sélection dossier ligne de crédit'), 'LCdr', 7, 1, false, NULL, false);
	END IF;

	IF NOT EXISTS(SELECT nom_menu FROM menus WHERE nom_menu='LCdr-2') THEN
		INSERT INTO menus (nom_menu, libel_menu, nom_pere, pos_hierarch, ordre, is_menu,fonction, is_cliquable) VALUES ('LCdr-2', maketraductionlangsyst('Clôture dossier ligne de crédit'), 'LCdr', 7, 2, false, NULL, false);
	END IF;

	IF NOT EXISTS(SELECT nom_menu FROM menus WHERE nom_menu='LCdr-3') THEN
		INSERT INTO menus (nom_menu, libel_menu, nom_pere, pos_hierarch, ordre, is_menu,fonction, is_cliquable) VALUES ('LCdr-3', maketraductionlangsyst('Confirmation'), 'LCdr', 7, 3, false, NULL, false);
	END IF;
	
	-- Ecrans
	IF NOT EXISTS (SELECT nom_ecran FROM ecrans WHERE nom_ecran='LCdr-1') THEN
		INSERT INTO ecrans(nom_ecran, fichier, nom_menu, fonction) VALUES ('LCdr-1', 'modules/credit/cloturedossier_lcr.php', 'LCdr-1', 610);
	END IF;
	
	IF NOT EXISTS (SELECT nom_ecran FROM ecrans WHERE nom_ecran='LCdr-2') THEN
		INSERT INTO ecrans(nom_ecran, fichier, nom_menu, fonction) VALUES ('LCdr-2', 'modules/credit/cloturedossier_lcr.php', 'LCdr-2', 610);
	END IF;
	
	IF NOT EXISTS (SELECT nom_ecran FROM ecrans WHERE nom_ecran='LCdr-3') THEN
		INSERT INTO ecrans(nom_ecran, fichier, nom_menu, fonction) VALUES ('LCdr-3', 'modules/credit/cloturedossier_lcr.php', 'LCdr-3', 610);
	END IF;

	RAISE NOTICE 'END';

	RETURN output_result;

END;
$$
LANGUAGE plpgsql;

SELECT ticket_593();
DROP FUNCTION ticket_593();

--------------------------------------- End Ticket #593 (PP #178) :  ---------------------------------------