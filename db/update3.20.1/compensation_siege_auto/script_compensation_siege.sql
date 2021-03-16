  CREATE OR REPLACE FUNCTION compensation_siege_logs() RETURNS INT AS
$$
DECLARE
output_result INTEGER = 1;
tableliste_ident INTEGER = 0;

BEGIN

IF NOT EXISTS (SELECT 0 FROM pg_class where relname = 'seq_id_log_multiagences' )
THEN
  CREATE SEQUENCE public.seq_id_log_multiagences
    INCREMENT 1
    START 1
    MINVALUE 1
    MAXVALUE 9223372036854775807
    CACHE 1;

ALTER SEQUENCE public.seq_id_log_multiagences
    OWNER TO postgres;
END IF;

IF EXISTS (SELECT 0 FROM pg_class where relname = 'seq_id_log_multiagences' )
THEN

IF NOT EXISTS(SELECT * FROM information_schema.tables WHERE table_name = 'log_multiagence_details') THEN
	DROP TABLE IF EXISTS log_multiagence;
END IF;

IF NOT EXISTS(SELECT * FROM information_schema.tables WHERE table_name = 'log_multiagence') THEN
CREATE TABLE public.log_multiagence
(
    id integer NOT NULL DEFAULT nextval('seq_id_log_multiagences'::regclass),
    date_exe timestamp(0) without time zone,
    nom_job character varying(255), -- COLLATE pg_catalog."default",
    is_sucess boolean,
    CONSTRAINT log_multiagence_pkey PRIMARY KEY (id)
)
WITH (
    OIDS = FALSE
)
TABLESPACE pg_default;

ALTER TABLE public.log_multiagence
    OWNER to postgres;
	END IF;
END IF;

IF NOT EXISTS (SELECT 0 FROM pg_class where relname = 'seq_id_log_multiagences_details' )
THEN
CREATE SEQUENCE public.seq_id_log_multiagences_details
    INCREMENT 1
    START 1
    MINVALUE 1
    MAXVALUE 9223372036854775807
    CACHE 1;

ALTER SEQUENCE public.seq_id_log_multiagences_details
    OWNER TO postgres;
END IF;


IF EXISTS (SELECT 0 FROM pg_class where relname = 'seq_id_log_multiagences_details' )
THEN
IF NOT EXISTS(SELECT * FROM information_schema.tables WHERE table_name = 'log_multiagence_details') THEN
CREATE TABLE public.log_multiagence_details
(
    id integer NOT NULL DEFAULT nextval('seq_id_log_multiagences_details'::regclass),
    date timestamp(0) without time zone,
    nom_projet character varying(50), -- COLLATE pg_catalog."default",
    nom_job character varying(255), -- COLLATE pg_catalog."default",
    nom_composant character varying(255), -- COLLATE pg_catalog."default",
    message character varying(255), -- COLLATE pg_catalog."default",
    id_log integer,
    nom_agence character varying(255), -- COLLATE pg_catalog."default",
    status boolean,
    CONSTRAINT log_multiagence_details_id_log_fkey FOREIGN KEY (id_log)
        REFERENCES public.log_multiagence (id) MATCH SIMPLE
        ON UPDATE NO ACTION
        ON DELETE NO ACTION
)
WITH (
    OIDS = FALSE
)
TABLESPACE pg_default;

ALTER TABLE public.log_multiagence_details
    OWNER to postgres;
	END IF;
END IF;

-- Function Truncate table log after 2 month
CREATE OR REPLACE FUNCTION truncatelogmultiagence(date)
  RETURNS integer AS
$BODY$
    DECLARE
        date_traitement ALIAS FOR $1;
        date_truncate DATE;
        totalDelete INTEGER;

    BEGIN

 SELECT INTO date_truncate date_traitement - interval '2 month';
 --SELECT INTO date_truncate date_traitement - integer '80';
 --RAISE NOTICE 'Date from to truncate = %',date_truncate;

 IF EXISTS (SELECT l.* FROM log_multiagence l WHERE date(l.date_exe) < date_truncate LIMIT 1) THEN
  --SELECT INTO totalDelete COUNT(*) FROM log_multiagence l WHERE date(l.date_exe) < date_truncate;
  DELETE FROM log_multiagence_details WHERE id_log IN (SELECT l.id FROM log_multiagence l WHERE date(l.date_exe) < date_truncate);
  DELETE FROM log_multiagence l WHERE date(l.date_exe) < date_truncate;
  --RAISE NOTICE 'Table not empty! Delete where date(l.date) < date_truncate -> Count data = %',totalDelete;
 END IF;

        RETURN totalDelete;
    END;
$BODY$
  LANGUAGE plpgsql VOLATILE
  COST 100;
ALTER FUNCTION truncatelogmultiagence(date)
  OWNER TO postgres;

RETURN output_result;

END;
$$
LANGUAGE plpgsql;


select compensation_siege_logs();

DROP FUNCTION IF EXISTS compensation_siege_logs();