-- Function: ticket_at_32()

-- DROP FUNCTION ticket_at_32();

CREATE OR REPLACE FUNCTION ticket_at_32()
  RETURNS integer AS
$BODY$
DECLARE

  output_result integer = 0;
  id_str_trad integer = 0;
  tableliste_ident integer = 0;

BEGIN

	IF EXISTS(SELECT id_agc FROM adsys_multi_agence WHERE id_agc = numagc() AND is_agence_siege = 't') THEN
		-- Add new ADBanking function to database
		IF NOT EXISTS(SELECT * FROM adsys_fonction WHERE code_fonction = 242) THEN
			INSERT INTO adsys_fonction (code_fonction, libelle, id_ag) VALUES (242, 'Rapport Etat de la compensation des operations en deplace', numagc());
		END IF;

		-- By default give new report access to admin
		IF NOT EXISTS(SELECT * FROM adsys_profils_axs WHERE profil = 1 AND fonction = 242) THEN
			INSERT INTO adsys_profils_axs (profil, fonction) VALUES (1, 242);
		END IF;

		-- Create new column for table agence 'traite_compensation_automatique'
		IF NOT EXISTS(SELECT * FROM information_schema.columns WHERE table_name = 'ad_agc' AND column_name = 'traite_compensation_automatique') THEN
			ALTER TABLE ad_agc
			ADD COLUMN traite_compensation_automatique BOOLEAN NOT NULL DEFAULT FALSE;
			select into tableliste_ident ident from tableliste where nomc like 'ad_agc' order by ident desc limit 1;
			INSERT INTO d_tableliste VALUES ((select max(ident) from d_tableliste)+1,tableliste_ident, 'traite_compensation_automatique', makeTraductionLangSyst('Est-ce traitement compensation au siege automatique?'), NULL, NULL, 'bol', false, false, false);
		END IF;
	END IF;

	output_result := 1;

	RETURN output_result;

END;
$BODY$
  LANGUAGE plpgsql VOLATILE
  COST 100;
ALTER FUNCTION ticket_at_32()
  OWNER TO adbanking;

  SELECT ticket_at_32();

  DROP FUNCTION IF EXISTS ticket_at_32();