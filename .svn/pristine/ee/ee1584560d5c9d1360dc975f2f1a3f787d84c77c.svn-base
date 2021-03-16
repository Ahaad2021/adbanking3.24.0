CREATE OR REPLACE FUNCTION update_3_6_fix()  RETURNS INT AS
$$
DECLARE
output_result INTEGER = 1;

BEGIN

RAISE NOTICE 'START';

IF EXISTS(SELECT * FROM information_schema.columns WHERE table_name = 'ad_agc' AND column_name = 'calcul_penalites_credit_radie') THEN
 ALTER TABLE ad_agc DROP COLUMN calcul_penalites_credit_radie RESTRICT;
 RAISE NOTICE 'Suppression champ calcul_penalites_credit_radie effectuée';
 output_result := 2;
END IF;

IF EXISTS(SELECT * FROM d_tableliste WHERE nchmpc = 'calcul_penalites_credit_radie') THEN
 DELETE FROM d_tableliste WHERE nchmpc = 'calcul_penalites_credit_radie';
 RAISE NOTICE 'Suppression champ calcul_penalites_credit_radie de la table d_tableliste effectuée';
 output_result := 2;
END IF;

IF EXISTS(SELECT * FROM information_schema.columns WHERE table_name = 'ad_agc' AND column_name = 'licence_jours_alerte') THEN
 ALTER TABLE ad_agc DROP COLUMN licence_jours_alerte RESTRICT;
 RAISE NOTICE 'Suppression champ licence_jours_alerte effectuée';
 output_result := 2;
END IF;

IF EXISTS(SELECT * FROM d_tableliste WHERE nchmpc = 'licence_jours_alerte') THEN
 DELETE FROM d_tableliste WHERE nchmpc = 'licence_jours_alerte';
 RAISE NOTICE 'Suppression champ licence_jours_alerte de la table d_tableliste effectuée';
 output_result := 2;
END IF;

IF EXISTS(SELECT * FROM information_schema.tables WHERE table_name = 'adsys_licence') THEN
 DROP TABLE adsys_licence;
 RAISE NOTICE 'Suppression table adsys_licence effectuée';
 output_result := 2;
END IF;

RETURN output_result;

END;
$$
LANGUAGE plpgsql;
  
SELECT update_3_6_fix();
DROP FUNCTION update_3_6_fix();

-- #300
ALTER TABLE ad_agc ADD COLUMN calcul_penalites_credit_radie boolean NOT NULL DEFAULT false;
INSERT INTO d_tableliste(ident, tablen, nchmpc, nchmpl, isreq, ref_field, type, onslct, ispkey, traduit) VALUES ((select max(ident) from d_tableliste)+1, (select ident from tableliste where nomc like 'ad_agc' order by ident desc limit 1), 'calcul_penalites_credit_radie', maketraductionlangsyst('Calcul des pénalités après radiation de crédit ?'), false, NULL, 'bol', NULL, NULL, false);

-- #313
ALTER TABLE ad_agc ADD COLUMN licence_jours_alerte integer NOT NULL DEFAULT 30;
INSERT INTO d_tableliste(ident, tablen, nchmpc, nchmpl, isreq, ref_field, type, onslct, ispkey, traduit) VALUES ((select max(ident) from d_tableliste)+1, (select ident from tableliste where nomc like 'ad_agc' order by ident desc limit 1), 'licence_jours_alerte', maketraductionlangsyst('Nombre de jours avant la date d''expiration de la licence<br />à partir duquel l''alerte est donnée'), true, NULL, 'int', false, false, false);

CREATE TABLE adsys_licence
(
  id_licence serial,
  id_agc integer NOT NULL,
  date_creation timestamp without time zone,
  date_expiration timestamp without time zone,
  statut_licence boolean DEFAULT false,
  CONSTRAINT adsys_licence_pkey PRIMARY KEY (id_licence)
)
WITH (
  OIDS=FALSE
);
