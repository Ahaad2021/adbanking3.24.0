CREATE OR REPLACE FUNCTION patch_ticket_507() RETURNS INT AS
$$
DECLARE
output_result INTEGER = 1;

BEGIN

	RAISE NOTICE 'START';

	-- CREATE TABLE ad_jasper_param_extras
	IF NOT EXISTS(SELECT * FROM information_schema.tables WHERE table_name = 'ad_jasper_param_extras') THEN
		CREATE TABLE ad_jasper_param_extras
		(
			id serial NOT NULL,
			code_param character varying(30) NOT NULL,
			type_lsb character varying(10) NOT NULL, -- static / dynamic
			table_name_param character varying(30) NULL,
			key_param character varying(30) NULL,
			value_param character varying(30) NULL,
			id_ag integer NOT NULL,
			CONSTRAINT ad_jasper_param_extras_pkey PRIMARY KEY (id, id_ag),
			CONSTRAINT ad_jasper_param_extras_ukey UNIQUE (code_param, id_ag)
		)
		WITH (
		  OIDS=FALSE
		);

		RAISE NOTICE 'Table ad_jasper_param_extras created';
		output_result := 2;
	END IF;

	-- CREATE TABLE ad_jasper_param_lsb
	IF NOT EXISTS(SELECT * FROM information_schema.tables WHERE table_name = 'ad_jasper_param_lsb') THEN
		CREATE TABLE ad_jasper_param_lsb
		(
			id serial NOT NULL,
			code_param character varying(30) NOT NULL,
			cle character(15) NOT NULL,
			valeur character(50) NOT NULL,
			id_ag integer NOT NULL,
			CONSTRAINT ad_jasper_param_lsb_pkey PRIMARY KEY (id, id_ag),
			CONSTRAINT ad_jasper_param_lsb_ukey UNIQUE (code_param, cle, id_ag)
		)
		WITH (
		  OIDS=FALSE
		);

		RAISE NOTICE 'Table ad_jasper_param_lsb created';
		output_result := 2;
	END IF;
	
	-- CREATE ECRANS
	-- Liste
	IF NOT EXISTS (SELECT nom_ecran FROM ecrans WHERE nom_ecran='Gjr-22') THEN
		INSERT INTO ecrans(nom_ecran, fichier, nom_menu, fonction) VALUES ('Gjr-22', 'modules/parametrage/gestion_jasper.php', 'Gjr-1', 300);
	END IF;

	-- Consulter
	IF NOT EXISTS (SELECT nom_ecran FROM ecrans WHERE nom_ecran='Gjr-23') THEN
		INSERT INTO ecrans(nom_ecran, fichier, nom_menu, fonction) VALUES ('Gjr-23', 'modules/parametrage/gestion_jasper.php', 'Gjr-1', 300);
	END IF;

	-- Ajouter
	IF NOT EXISTS (SELECT nom_ecran FROM ecrans WHERE nom_ecran='Gjr-24') THEN
		INSERT INTO ecrans(nom_ecran, fichier, nom_menu, fonction) VALUES ('Gjr-24', 'modules/parametrage/gestion_jasper.php', 'Gjr-1', 300);
	END IF;

	-- Modifier
	IF NOT EXISTS (SELECT nom_ecran FROM ecrans WHERE nom_ecran='Gjr-25') THEN
		INSERT INTO ecrans(nom_ecran, fichier, nom_menu, fonction) VALUES ('Gjr-25', 'modules/parametrage/gestion_jasper.php', 'Gjr-1', 300);
	END IF;

	-- Confirmation ajout ou modif parametre menu deroulant
	IF NOT EXISTS (SELECT nom_ecran FROM ecrans WHERE nom_ecran='Gjr-26') THEN
		INSERT INTO ecrans(nom_ecran, fichier, nom_menu, fonction) VALUES ('Gjr-26', 'modules/parametrage/gestion_jasper.php', 'Gjr-1', 300);
	END IF;

	-- Supprimer
	IF NOT EXISTS (SELECT nom_ecran FROM ecrans WHERE nom_ecran='Gjr-27') THEN
		INSERT INTO ecrans(nom_ecran, fichier, nom_menu, fonction) VALUES ('Gjr-27', 'modules/parametrage/gestion_jasper.php', 'Gjr-1', 300);
	END IF;

	-- Confirmation Supprimer
	IF NOT EXISTS (SELECT nom_ecran FROM ecrans WHERE nom_ecran='Gjr-28') THEN
		INSERT INTO ecrans(nom_ecran, fichier, nom_menu, fonction) VALUES ('Gjr-28', 'modules/parametrage/gestion_jasper.php', 'Gjr-1', 300);
	END IF;

	RETURN output_result;

END;
$$
LANGUAGE plpgsql;

SELECT patch_ticket_507();
DROP FUNCTION patch_ticket_507();
