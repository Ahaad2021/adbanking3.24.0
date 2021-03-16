   CREATE OR REPLACE FUNCTION script_ewallet_gs() RETURNS INT AS
  $$
  DECLARE
  output_result INTEGER = 1;

  BEGIN

    ------------------------------------------------ Creation table ad_demande_ewallet_gs--------------------------------
    IF NOT EXISTS(SELECT * FROM information_schema.tables WHERE table_name = 'ad_demande_ewallet_gs') THEN
      CREATE TABLE ad_demande_ewallet_gs
      (
        id integer,
        identifiant_client varchar(255),
        id_client integer,
        id_cpte_source integer,
		num_compte_source text,
		gi_nom text,
		id_grp_sol integer,
        mnt_dem numeric(30,0),
        devise text,
        id_transaction text,
        code_imf text,
        telephone text,
		id_ag integer,
        statut_demande integer,
        date_creation timestamp without time zone,
        date_modif timestamp without time zone,
		CONSTRAINT ad_demande_ewallet_gs_pk PRIMARY KEY (id)
      );
      ALTER TABLE ad_demande_ewallet_gs
      OWNER TO adbanking;



  -- Sequence: ad_demande_ewallet_gs_id_seq

-- DROP SEQUENCE ad_demande_ewallet_gs_id_seq;

CREATE SEQUENCE ad_demande_ewallet_gs_id_seq
  INCREMENT 1
  MINVALUE 1
  MAXVALUE 999999
  START 18
  CACHE 1;
ALTER TABLE ad_demande_ewallet_gs_id_seq
  OWNER TO postgres;
  END IF;



	    ------------------------------------------------ Creation table ad_ewallet_gs_mandataire--------------------------------
    IF NOT EXISTS(SELECT * FROM information_schema.tables WHERE table_name = 'ad_ewallet_gs_mandataire') THEN
      CREATE TABLE ad_ewallet_gs_mandataire
      (
        id integer,
        id_dem integer,
        id_transaction text,
        id_client_signataire integer,
		identifiant_client_signataire varchar(255),
        telephone text,
        statut_signature integer,
		id_ag integer,
        date_creation timestamp without time zone,
        date_modif timestamp without time zone,
		CONSTRAINT ad_ewallet_gs_mandataire_pk PRIMARY KEY (id,id_dem,id_transaction)
      );
      ALTER TABLE ad_ewallet_gs_mandataire
      OWNER TO adbanking;


      -- Sequence: ad_ewallet_gs_mandataire_id_seq

    -- DROP SEQUENCE ad_ewallet_gs_mandataire_id_seq;

    CREATE SEQUENCE ad_ewallet_gs_mandataire_id_seq
      INCREMENT 1
      MINVALUE 1
      MAXVALUE 999999
      START 9
      CACHE 1;
    ALTER TABLE ad_ewallet_gs_mandataire_id_seq
      OWNER TO postgres;

      END IF;

  RETURN output_result;
  END;
  $$
  LANGUAGE plpgsql;

SELECT script_ewallet_gs();
DROP FUNCTION script_ewallet_gs();
