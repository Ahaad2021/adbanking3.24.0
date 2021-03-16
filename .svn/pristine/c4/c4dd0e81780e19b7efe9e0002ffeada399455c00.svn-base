---------------------------------- Mis a jour trigger starification ------------------------------------

CREATE OR REPLACE FUNCTION trig_insert_adsys_tarification_hist() RETURNS trigger AS
	$BODY$
	BEGIN
	    INSERT INTO adsys_tarification_hist (date_action, id_tarification, code_abonnement, type_de_frais, mode_frais, valeur, valeur_min, valeur_max,  compte_comptable, date_debut_validite, date_fin_validite, statut, id_ag, date_creation, date_modif)
			VALUES ( NOW(), OLD.id_tarification, OLD.code_abonnement, OLD.type_de_frais, OLD.mode_frais, OLD.valeur, OLD.valeur_min, OLD.valeur_max, OLD.compte_comptable, OLD.date_debut_validite, OLD.date_fin_validite, OLD.statut, OLD.id_ag, OLD.date_creation, OLD.date_modif);
	    RETURN NEW;
    END;
$BODY$
LANGUAGE plpgsql VOLATILE
COST 100;


---------------------------------- Mis a jour tarification ------------------------------------

CREATE OR REPLACE FUNCTION patch_ticket_536() RETURNS INT AS
$$
DECLARE
output_result INTEGER = 1;
tableliste_ident INTEGER = 0;

BEGIN

	RAISE NOTICE 'START';

-- ----------------------------
-- Update adsys_tarification
-- ----------------------------

-- Create the column valeur_min in adsys_tarification if it does not exist
  IF (SELECT count(*) from information_schema.columns WHERE table_name = 'adsys_tarification' AND column_name='valeur_min') = 0 THEN
    ALTER TABLE adsys_tarification ADD COLUMN valeur_min double precision;
    RAISE NOTICE 'Created column valeur_min in table adsys_tarification';
  END IF;

-- Create the column valeur_max in adsys_tarification if it does not exist
  IF (SELECT count(*) from information_schema.columns WHERE table_name = 'adsys_tarification' AND column_name='valeur_max') = 0 THEN
    ALTER TABLE adsys_tarification ADD COLUMN valeur_max double precision;
    RAISE NOTICE 'Created column valeur_max in table adsys_tarification';
  END IF;

	-- Create the column valeur_min in adsys_tarification_hist if it does not exist
  IF (SELECT count(*) from information_schema.columns WHERE table_name = 'adsys_tarification_hist' AND column_name='valeur_min') = 0 THEN
    ALTER TABLE adsys_tarification_hist ADD COLUMN valeur_min double precision;
    RAISE NOTICE 'Created column valeur_min in table adsys_tarification_hist';
  END IF;

-- Create the column valeur_max in adsys_tarification_hist if it does not exist
  IF (SELECT count(*) from information_schema.columns WHERE table_name = 'adsys_tarification_hist' AND column_name='valeur_max') = 0 THEN
    ALTER TABLE adsys_tarification_hist ADD COLUMN valeur_max double precision;
    RAISE NOTICE 'Created column valeur_max in table adsys_tarification_hist';
  END IF;


  tableliste_ident := (select ident from tableliste where nomc like 'adsys_tarification' order by ident desc limit 1);

	-- d_tableliste inserts :

  IF NOT EXISTS(SELECT * FROM d_tableliste WHERE tablen=tableliste_ident AND nchmpc = 'valeur_min') THEN
    INSERT INTO d_tableliste(ident, tablen, nchmpc, nchmpl, isreq, ref_field, type, onslct, ispkey, traduit)
		VALUES ((select max(ident) from d_tableliste)+1, tableliste_ident, 'valeur_min', maketraductionlangsyst('Valeur minimum'), true, NULL, 'mnt', false, false, false);

    RAISE NOTICE 'Insertion valeur_min de la table d_tableliste effectuée';
    output_result := 2;
  END IF;


  IF NOT EXISTS(SELECT * FROM d_tableliste WHERE tablen=tableliste_ident AND nchmpc = 'valeur_max') THEN
    INSERT INTO d_tableliste(ident, tablen, nchmpc, nchmpl, isreq, ref_field, type, onslct, ispkey, traduit)
    VALUES ((select max(ident) from d_tableliste)+1, tableliste_ident, 'valeur_max', maketraductionlangsyst('Valeur maximum'), true, NULL, 'mnt', false, false, false);

    RAISE NOTICE 'Insertion valeur_min de la table d_tableliste effectuée';
    output_result := 2;
  END IF;

-- ----------------------------
-- Records of adsys_tarification
-- ----------------------------

  IF NOT EXISTS(SELECT * FROM adsys_tarification WHERE type_de_frais='CRED_FRAIS') THEN
    INSERT INTO adsys_tarification (id_tarification, code_abonnement, type_de_frais, mode_frais, compte_comptable, date_debut_validite, date_fin_validite, statut, id_ag)
    VALUES (7, 'credit', 'CRED_FRAIS', '1', null, null, null, 'f', numagc());
    output_result := 2;
  END IF;

  IF NOT EXISTS(SELECT * FROM adsys_tarification WHERE type_de_frais='CRED_COMMISSION') THEN
    INSERT INTO adsys_tarification (id_tarification, code_abonnement, type_de_frais, mode_frais, compte_comptable, date_debut_validite, date_fin_validite, statut, id_ag)
    VALUES (8, 'credit', 'CRED_COMMISSION', '1', null, null, null, 'f', numagc());
    output_result := 2;
  END IF;

  IF NOT EXISTS(SELECT * FROM adsys_tarification WHERE type_de_frais='CRED_ASSURANCE') THEN
    INSERT INTO adsys_tarification (id_tarification, code_abonnement, type_de_frais, mode_frais, compte_comptable, date_debut_validite, date_fin_validite, statut, id_ag)
    VALUES (9, 'credit', 'CRED_ASSURANCE', '1', null, null, null, 'f', numagc());
    output_result := 2;
  END IF;

-- ----------------------------
-- Update ad_dcr
-- ----------------------------
-- Create the column mnt_frais in ad_dcr if it does not exist (No d_tableliste required)
  IF (SELECT count(*) from information_schema.columns WHERE table_name = 'ad_dcr' AND column_name='mnt_frais_doss') = 0 THEN
    ALTER TABLE ad_dcr ADD COLUMN mnt_frais_doss numeric(30,6) DEFAULT 0.000000;
    RAISE NOTICE 'Created column mnt_frais_doss in table ad_dcr';
  END IF;


  RAISE NOTICE 'END';

	RETURN output_result;

END;
$$
LANGUAGE plpgsql;

SELECT patch_ticket_536();
DROP FUNCTION patch_ticket_536();