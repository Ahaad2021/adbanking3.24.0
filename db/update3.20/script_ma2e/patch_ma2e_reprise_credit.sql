
CREATE OR REPLACE FUNCTION patch_ma2e() RETURNS INT AS
$$
DECLARE

	output_result INTEGER = 1;

BEGIN

IF NOT EXISTS(SELECT * FROM information_schema.tables WHERE table_name = 'ad_etr_temp') THEN

		-- Table: ad_etr_temp

		-- DROP TABLE ad_etr_temp;

		CREATE TABLE ad_etr_temp
		(
		  id_doss integer NOT NULL,
		  id_ech integer NOT NULL,
		  date_ech timestamp without time zone,
		  mnt_cap numeric(30,6) DEFAULT 0,
		  mnt_int numeric(30,6) DEFAULT 0,
		  mnt_gar numeric(30,6) DEFAULT 0,
		  mnt_reech numeric(30,6) DEFAULT 0, -- Ce champs contient éventuellement un montant qui était dÃ» pour cette échéance mais dont le remboursement a été repris dans les échéances ultérieures suites à un rééchelonnement
		  remb boolean, -- L'échéance a-t-elle été remboursée entièrement ?
		  solde_cap numeric(30,6) DEFAULT 0,
		  solde_int numeric(30,6) DEFAULT 0,
		  solde_gar numeric(30,6) DEFAULT 0,
		  solde_pen numeric(30,6) DEFAULT 0,
		  id_ag integer NOT NULL,
		  date_creation timestamp without time zone NOT NULL DEFAULT ((now())::character varying(23))::timestamp without time zone,
		  date_modif timestamp without time zone,
		  CONSTRAINT ad_etr_temp_pkey PRIMARY KEY (id_doss, id_ech, id_ag),
		  CONSTRAINT ad_etr_temp_id_doss_fkey FOREIGN KEY (id_doss, id_ag)
			  REFERENCES ad_dcr (id_doss, id_ag) MATCH SIMPLE
			  ON UPDATE NO ACTION ON DELETE NO ACTION
		)
		WITH (
		  OIDS=FALSE
		);
		ALTER TABLE ad_etr_temp
		  OWNER TO postgres;
		COMMENT ON TABLE ad_etr_temp
		  IS 'Echéanciers théoriques';
		COMMENT ON COLUMN ad_etr_temp.mnt_reech IS 'Ce champs contient éventuellement un montant qui était dÃ» pour cette échéance mais dont le remboursement a été repris dans les échéances ultérieures suites à un rééchelonnement';
		COMMENT ON COLUMN ad_etr_temp.remb IS 'L''échéance a-t-elle été remboursée entièrement ?';


		-- Index: idx1_ad_etr_temp

		-- DROP INDEX idx1_ad_etr_temp;

		CREATE UNIQUE INDEX idx1_ad_etr_temp
		  ON ad_etr_temp
		  USING btree
		  (id_ag, id_doss, date_ech);


		-- Trigger: maj_horodatage on ad_etr_temp

		-- DROP TRIGGER maj_horodatage ON ad_etr_temp;

		CREATE TRIGGER maj_horodatage
		  BEFORE UPDATE
		  ON ad_etr_temp
		  FOR EACH ROW
		  EXECUTE PROCEDURE maj_horodatage();

		output_result := 0;

END IF;

RETURN output_result;

END;
$$
  LANGUAGE plpgsql VOLATILE
  COST 100;
ALTER FUNCTION patch_ma2e()
  OWNER TO adbanking;

SELECT patch_ma2e();

DROP FUNCTION IF EXISTS patch_ma2e();
