CREATE OR REPLACE FUNCTION script_creation_table() RETURNS INT AS
$$
DECLARE
output_result INTEGER = 1;
  d_tableliste_str integer = 0;
  tableliste_ident integer = 0;

  BEGIN


	IF NOT EXISTS(SELECT * FROM ad_cpt_ope WHERE type_operation = 630 AND categorie_ope = 1 AND id_ag = numagc()) THEN
		INSERT INTO ad_cpt_ope(type_operation, categorie_ope, id_ag, libel_ope) VALUES (630, 1, numagc(), maketraductionlangsyst('Annulation commission pour agent'));
		RAISE NOTICE 'Insertion type_operation 630 dans la table ad_cpt_ope effectuée';
		output_result := 2;
	END IF;

	IF NOT EXISTS(SELECT * FROM ad_cpt_ope_cptes WHERE type_operation = 630 AND sens = 'd' AND categorie_cpte = 29 AND id_ag = numagc()) THEN
		INSERT INTO ad_cpt_ope_cptes(type_operation, num_cpte, sens, categorie_cpte, id_ag) VALUES (630, NULL, 'd', 29, numagc());
	END IF;

	IF NOT EXISTS(SELECT * FROM ad_cpt_ope_cptes WHERE type_operation = 630 AND sens = 'c' AND categorie_cpte = 1 AND id_ag = numagc()) THEN
		INSERT INTO ad_cpt_ope_cptes(type_operation, num_cpte, sens, categorie_cpte, id_ag) VALUES (630, NULL, 'c', 1, numagc());

	END IF;



	IF NOT EXISTS(SELECT * FROM ad_cpt_ope WHERE type_operation = 631 AND categorie_ope = 1 AND id_ag = numagc()) THEN
		INSERT INTO ad_cpt_ope(type_operation, categorie_ope, id_ag, libel_ope) VALUES (631, 1, numagc(), maketraductionlangsyst('Annulation commission pour institution'));
		RAISE NOTICE 'Insertion type_operation 630 dans la table ad_cpt_ope effectuée';
		output_result := 2;
	END IF;

	IF NOT EXISTS(SELECT * FROM ad_cpt_ope_cptes WHERE type_operation = 631 AND sens = 'd' AND categorie_cpte = 29 AND id_ag = numagc()) THEN
		INSERT INTO ad_cpt_ope_cptes(type_operation, num_cpte, sens, categorie_cpte, id_ag) VALUES (631, NULL, 'd', 29, numagc());
	END IF;

	IF NOT EXISTS(SELECT * FROM ad_cpt_ope_cptes WHERE type_operation = 631 AND sens = 'c' AND categorie_cpte = 1 AND id_ag = numagc()) THEN
		INSERT INTO ad_cpt_ope_cptes(type_operation, num_cpte, sens, categorie_cpte, id_ag) VALUES (631, NULL, 'c', 1, numagc());

	END IF;


	IF NOT EXISTS(SELECT * FROM ad_cpt_ope WHERE type_operation = 632 AND categorie_ope = 1 AND id_ag = numagc()) THEN
		INSERT INTO ad_cpt_ope(type_operation, categorie_ope, id_ag, libel_ope) VALUES (632, 1, numagc(), maketraductionlangsyst('Annulation impôt sur commission'));
		RAISE NOTICE 'Insertion type_operation 632 dans la table ad_cpt_ope effectuée';
		output_result := 2;
	END IF;

	IF NOT EXISTS(SELECT * FROM ad_cpt_ope_cptes WHERE type_operation = 632 AND sens = 'd' AND categorie_cpte = 25 AND id_ag = numagc()) THEN
		INSERT INTO ad_cpt_ope_cptes(type_operation, num_cpte, sens, categorie_cpte, id_ag) VALUES (632, NULL, 'd', 25, numagc());
	END IF;

	IF NOT EXISTS(SELECT * FROM ad_cpt_ope WHERE type_operation = 633 AND categorie_ope = 1 AND id_ag = numagc()) THEN
		INSERT INTO ad_cpt_ope(type_operation, categorie_ope, id_ag, libel_ope) VALUES (633, 1, numagc(), maketraductionlangsyst('Annulation commission sur transaction'));
		RAISE NOTICE 'Insertion type_operation 633 dans la table ad_cpt_ope effectuée';
		output_result := 2;
	END IF;

	IF NOT EXISTS(SELECT * FROM ad_cpt_ope_cptes WHERE type_operation = 633 AND sens = 'c' AND categorie_cpte = 1 AND id_ag = numagc()) THEN
		INSERT INTO ad_cpt_ope_cptes(type_operation, num_cpte, sens, categorie_cpte, id_ag) VALUES (633, NULL, 'c', 1, numagc());
	END IF;

	


  RETURN output_result;
  END;
$$
LANGUAGE plpgsql;

SELECT script_creation_table();
DROP FUNCTION script_creation_table();
