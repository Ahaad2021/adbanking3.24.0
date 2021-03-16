CREATE OR REPLACE FUNCTION script_creation_table() RETURNS INT AS
$$
DECLARE
output_result INTEGER = 1;
  d_tableliste_str integer = 0;
  tableliste_ident integer = 0;

  BEGIN
  IF NOT EXISTS(SELECT * FROM information_schema.tables WHERE table_name = 'ag_commission_client') THEN
-- Table: ag_commission_client

-- DROP TABLE ag_commission_client;
  CREATE TABLE ag_commission_client
(
  cpte_comm_inst text,
  montant_comm numeric(30,6),
  remarque text,
  date_creation timestamp without time zone,
  date_modif timestamp without time zone,
  login text,
  id_ag integer)
WITH (
  OIDS=FALSE
);
  ALTER TABLE ag_commission_client
  OWNER TO postgres;
END IF;


-- Nouvelle operation commission pour agent sur creation nouveau client : 627

	IF NOT EXISTS(SELECT * FROM ad_cpt_ope WHERE type_operation = 627 AND categorie_ope = 1 AND id_ag = numagc()) THEN
		INSERT INTO ad_cpt_ope(type_operation, categorie_ope, id_ag, libel_ope) VALUES (627, 1, numagc(), maketraductionlangsyst('Commission sur nouveau client via agent'));
		RAISE NOTICE 'Insertion type_operation 627 dans la table ad_cpt_ope effectuée';
		output_result := 2;
	END IF;

	IF NOT EXISTS(SELECT * FROM ad_cpt_ope_cptes WHERE type_operation = 627 AND sens = 'c' AND categorie_cpte = 29 AND id_ag = numagc()) THEN
		INSERT INTO ad_cpt_ope_cptes(type_operation, num_cpte, sens, categorie_cpte, id_ag) VALUES (627, NULL, 'c', 29, numagc());

	END IF;


	IF NOT EXISTS (SELECT * FROM information_schema.columns WHERE table_name='adsys_profils' and column_name='is_profil_agent') THEN
	ALTER TABLE adsys_profils ADD COLUMN is_profil_agent boolean default false;
	END IF;

  RETURN output_result;
END;
$$
LANGUAGE plpgsql;

SELECT script_creation_table();
DROP FUNCTION script_creation_table();
