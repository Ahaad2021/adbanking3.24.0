CREATE OR REPLACE FUNCTION patch_multi_agence_ticket_527() RETURNS INT AS
$$
DECLARE
output_result INTEGER = 1;

BEGIN

	RAISE NOTICE 'START';

	-- Ecrans
	IF NOT EXISTS (SELECT nom_ecran FROM ecrans WHERE nom_ecran='Mac-1') THEN
		INSERT INTO ecrans(nom_ecran, fichier, nom_menu, fonction) VALUES ('Mac-1', 'ad_ma/app/views/client/consult_client.php', 'Ope-11', 193);
	END IF;

	IF NOT EXISTS (SELECT nom_ecran FROM ecrans WHERE nom_ecran='Mac-2') THEN
		INSERT INTO ecrans(nom_ecran, fichier, nom_menu, fonction) VALUES ('Mac-2', 'ad_ma/app/views/client/consult_client.php', 'Ope-11', 193);
	END IF;

	IF NOT EXISTS (SELECT nom_ecran FROM ecrans WHERE nom_ecran='Mac-3') THEN
		INSERT INTO ecrans(nom_ecran, fichier, nom_menu, fonction) VALUES ('Mac-3', 'ad_ma/app/views/epargne/mandats.php', 'Ope-11', 193);
	END IF;

	RETURN output_result;

END;
$$
LANGUAGE plpgsql;

SELECT patch_multi_agence_ticket_527();
DROP FUNCTION patch_multi_agence_ticket_527();
