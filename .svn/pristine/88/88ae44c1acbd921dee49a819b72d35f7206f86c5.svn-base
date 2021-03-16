CREATE OR REPLACE FUNCTION patch_quotite() RETURNS INT AS
$$
DECLARE

	tableliste_ident INTEGER = 0;

	output_result INTEGER = 1;

BEGIN

IF NOT EXISTS(SELECT * FROM information_schema.columns WHERE table_name = 'ad_agc' AND column_name = 'quotite') THEN
ALTER TABLE ad_agc
ADD COLUMN quotite boolean;
tableliste_ident := (select ident from tableliste where nomc like 'ad_agc' order by ident desc limit 1);
INSERT INTO d_tableliste VALUES ((select max(ident) from d_tableliste)+1,tableliste_ident, 'quotite', makeTraductionLangSyst('Activation gestion quotité '), NULL, NULL, 'bol', false, false, false);
END IF;

IF NOT EXISTS(SELECT * FROM information_schema.columns WHERE table_name = 'ad_cli' AND column_name = 'mnt_quotite') THEN
ALTER TABLE ad_cli
ADD COLUMN mnt_quotite numeric(30,6);
tableliste_ident := (select ident from tableliste where nomc like 'ad_cli' order by ident desc limit 1);
INSERT INTO d_tableliste VALUES ((select max(ident) from d_tableliste)+1, tableliste_ident, 'mnt_quotite', makeTraductionLangSyst('Quotité'), NULL, NULL, 'mnt', false, false, false);
END IF;

IF NOT EXISTS(SELECT * FROM information_schema.columns WHERE table_name = 'ad_ord_perm' AND column_name = 'etat_clos') THEN
ALTER TABLE ad_ord_perm
ADD COLUMN etat_clos boolean DEFAULT false;
END IF;

IF NOT EXISTS(SELECT * FROM information_schema.columns WHERE table_name = 'ad_ord_perm' AND column_name = 'date_clos') THEN
ALTER TABLE ad_ord_perm
ADD COLUMN date_clos date;
END IF;

IF NOT EXISTS(SELECT * FROM information_schema.tables WHERE table_name = 'ad_quotite') THEN
CREATE TABLE ad_quotite (
id serial NOT NULL,
id_client integer,
quotite_avant numeric(30,6),
quotite_apres numeric(30,6),
mnt_quotite numeric(30,6),
date_modif timestamp without time zone,
raison_modif text,
id_ag integer,
CONSTRAINT ad_quotite_pkey PRIMARY KEY (id, id_ag)
)
WITH (
OIDS=FALSE
);

ALTER TABLE ad_quotite
OWNER TO postgres;
COMMENT ON TABLE ad_quotite
IS ' reference aux quotites';
COMMENT ON COLUMN ad_quotite.id IS 'id quotite';
COMMENT ON COLUMN ad_quotite.id_client IS 'id du client';
COMMENT ON COLUMN ad_quotite.quotite_avant IS 'quotite avant';
COMMENT ON COLUMN ad_quotite.quotite_apres IS 'quotite apres';
COMMENT ON COLUMN ad_quotite.date_modif IS 'date modif';
COMMENT ON COLUMN ad_quotite.raison_modif IS 'raison modif';
COMMENT ON COLUMN ad_quotite.id_ag IS 'id de l agence';
END IF;

RAISE NOTICE 'FIN traitement';
RETURN output_result;

END;
$$
  LANGUAGE plpgsql VOLATILE
  COST 100;
ALTER FUNCTION patch_quotite()
  OWNER TO adbanking;

SELECT patch_quotite();

DROP FUNCTION IF EXISTS patch_quotite();