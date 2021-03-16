-- Script de mise à jour de la base de données de la version 3.10.x à la version 3.12.x

------------- Ticket #469 ---------------------------------------
CREATE OR REPLACE FUNCTION alter_469()  RETURNS INT AS
$$
DECLARE
	output_result INTEGER = 1;
BEGIN	
	-- Check if field "is_produit_actif" exist in table "adsys_produit_credit"
	IF NOT EXISTS(SELECT * FROM information_schema.columns WHERE table_name = 'adsys_produit_credit' AND column_name = 'is_produit_actif') THEN
		ALTER TABLE adsys_produit_credit ADD COLUMN is_produit_actif  boolean DEFAULT TRUE;
		output_result := 2;
	END IF;	
	
	--insert into "d_tableliste" if notExist
	IF NOT EXISTS(SELECT * FROM d_tableliste WHERE nchmpc = 'is_produit_actif') THEN
	
		INSERT INTO d_tableliste(ident, tablen, nchmpc, nchmpl, isreq, ref_field, type, onslct, ispkey, traduit) VALUES ((select max(ident) from d_tableliste)+1, (select ident from tableliste where nomc like 'adsys_produit_credit' order by ident desc limit 1), 'is_produit_actif', maketraductionlangsyst('Est-ce que le produit est actif ?'), false, NULL, 'bol', false, false, false);
		output_result := 2;
	END IF;

	RETURN output_result;
END;
$$
LANGUAGE plpgsql;
  
SELECT alter_469();
DROP FUNCTION alter_469();


------------- Ticket #505 ---------------------------------------

CREATE OR REPLACE FUNCTION alter_505()  RETURNS INT AS
$$
DECLARE
	output_result INTEGER = 1;
BEGIN	
	-- Check if field "passage_etat_dormant" exist in table "adsys_produit_epargne"
	IF NOT EXISTS(SELECT * FROM information_schema.columns WHERE table_name = 'adsys_produit_epargne' AND column_name = 'passage_etat_dormant') THEN
		ALTER TABLE adsys_produit_epargne ADD COLUMN passage_etat_dormant  boolean DEFAULT FALSE;
		output_result := 2;
	END IF;
	
	--insert into "d_tableliste" if notExist
	IF NOT EXISTS(SELECT * FROM d_tableliste WHERE nchmpc = 'passage_etat_dormant') THEN
	
		INSERT INTO d_tableliste(ident, tablen, nchmpc, nchmpl, isreq, ref_field, type, onslct, ispkey, traduit) VALUES ((select max(ident) from d_tableliste)+1, (select ident from tableliste where nomc like 'adsys_produit_epargne' order by ident desc limit 1), 'passage_etat_dormant', maketraductionlangsyst('Passage à état dormant'), false, NULL, 'bol', false, false, false);
		output_result := 2;
	END IF;

	RETURN output_result;
END;
$$
LANGUAGE plpgsql;
  
SELECT alter_505();
DROP FUNCTION alter_505();

----------------------------------- Ticket #416 ---------------------------------------

CREATE OR REPLACE FUNCTION patch_416() RETURNS void AS 
$$
DECLARE
tableliste_ident INTEGER = 0;

BEGIN

RAISE NOTICE 'DEMARRAGE mise a jour base de données pour le ticket #416';

-- Creation nouveau ecrans pour le depot / retrait multiagence en multidevise

-- Ecrans retrait multidevises
IF NOT EXISTS (SELECT * FROM ecrans WHERE nom_ecran = 'Rtm-1') THEN
    INSERT INTO ecrans(nom_ecran, fichier, nom_menu, fonction)
    VALUES ('Rtm-1','ad_ma/app/views/epargne/retrait_compte_multidevises.php','Ope-11',92);
ELSE 
    UPDATE ecrans SET fonction = 92 WHERE nom_ecran = 'Rtm-1' AND fonction = 97;
END IF;

IF NOT EXISTS (SELECT * FROM ecrans WHERE nom_ecran = 'Rtm-2') THEN
    INSERT INTO ecrans(nom_ecran, fichier, nom_menu, fonction)
    VALUES ('Rtm-2','ad_ma/app/views/epargne/retrait_compte_multidevises.php','Ope-11',92);
ELSE 
    UPDATE ecrans SET fonction = 92 WHERE nom_ecran = 'Rtm-2' AND fonction = 97;
END IF;

IF NOT EXISTS (SELECT * FROM ecrans WHERE nom_ecran = 'Rtm-3') THEN
    INSERT INTO ecrans(nom_ecran, fichier, nom_menu, fonction)
    VALUES ('Rtm-3','ad_ma/app/views/epargne/retrait_compte_multidevises.php','Ope-11',92);
ELSE 
    UPDATE ecrans SET fonction = 92 WHERE nom_ecran = 'Rtm-3' AND fonction = 97;
END IF;

IF NOT EXISTS (SELECT * FROM ecrans WHERE nom_ecran = 'Rtm-4') THEN
    INSERT INTO ecrans(nom_ecran, fichier, nom_menu, fonction)
    VALUES ('Rtm-4','ad_ma/app/views/epargne/retrait_compte_multidevises.php','Ope-11',92);
ELSE 
    UPDATE ecrans SET fonction = 92 WHERE nom_ecran = 'Rtm-4' AND fonction = 97;
END IF;

-- Ecrans depot multidevises
IF NOT EXISTS (SELECT * FROM ecrans WHERE nom_ecran = 'Dpm-1') THEN
    INSERT INTO ecrans(nom_ecran, fichier, nom_menu, fonction)
    VALUES ('Dpm-1','ad_ma/app/views/epargne/depot_compte_multidevises.php','Ope-11',93);
ELSE 
    UPDATE ecrans SET fonction = 93 WHERE nom_ecran = 'Dpm-1' AND fonction = 98;
END IF;

IF NOT EXISTS (SELECT * FROM ecrans WHERE nom_ecran = 'Dpm-2') THEN
    INSERT INTO ecrans(nom_ecran, fichier, nom_menu, fonction)
    VALUES ('Dpm-2','ad_ma/app/views/epargne/depot_compte_multidevises.php','Ope-11',93);
ELSE 
    UPDATE ecrans SET fonction = 93 WHERE nom_ecran = 'Dpm-2' AND fonction = 98;
END IF;

IF NOT EXISTS (SELECT * FROM ecrans WHERE nom_ecran = 'Dpm-3') THEN
    INSERT INTO ecrans(nom_ecran, fichier, nom_menu, fonction)
    VALUES ('Dpm-3','ad_ma/app/views/epargne/depot_compte_multidevises.php','Ope-11',93);
ELSE 
    UPDATE ecrans SET fonction = 93 WHERE nom_ecran = 'Dpm-3' AND fonction = 98;
END IF;

IF NOT EXISTS (SELECT * FROM ecrans WHERE nom_ecran = 'Dpm-4') THEN
    INSERT INTO ecrans(nom_ecran, fichier, nom_menu, fonction)
    VALUES ('Dpm-4','ad_ma/app/views/epargne/depot_compte_multidevises.php','Ope-11',93);
ELSE 
    UPDATE ecrans SET fonction = 93 WHERE nom_ecran = 'Dpm-4' AND fonction = 98;
END IF;

-- Ajout nouveau champ parametrage multi-devise, multi-agence pour la perception des commissions de change
tableliste_ident := (select ident from tableliste where nomc like 'ad_agc' order by ident desc limit 1);

IF NOT EXISTS(SELECT column_name FROM information_schema.columns WHERE table_name='ad_agc' and column_name='pct_comm_change_local') 
THEN
  ALTER TABLE ad_agc ADD COLUMN pct_comm_change_local boolean DEFAULT true;
  INSERT INTO d_tableliste(ident, tablen, nchmpc, nchmpl, isreq, ref_field, type, onslct, ispkey, traduit) 
  VALUES ((select max(ident) from d_tableliste)+1, tableliste_ident, 'pct_comm_change_local', maketraductionlangsyst('Appliquer la commission dans l''agence locale en mode multi-agences ?'), false, NULL, 'bol', false, false, false);  
  RAISE NOTICE 'Insertion champ pct_comm_change_local dans ad_agc pour parametrage multi-devises, multi-agence effectué.';
ELSE
  RAISE NOTICE 'Column pct_comm_change_local exists';
END IF;

RAISE NOTICE 'FIN mise a jour base de données pour le ticket #416';

END;

$$
LANGUAGE plpgsql VOLATILE COST 100;
ALTER FUNCTION patch_416() OWNER TO adbanking;

-- ----------------------------------------------------------------------------
-- Execution
-- ----------------------------------------------------------------------------
SELECT patch_416();
DROP FUNCTION patch_416();
