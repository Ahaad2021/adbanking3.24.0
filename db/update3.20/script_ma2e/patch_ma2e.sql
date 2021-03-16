
CREATE OR REPLACE FUNCTION patch_ma2e() RETURNS INT AS
$$
DECLARE

	tableliste_ident INTEGER = 0;

	output_result INTEGER = 1;

BEGIN

IF NOT EXISTS(SELECT * FROM information_schema.tables WHERE table_name = 'adsys_employeur') THEN

CREATE TABLE adsys_employeur
(
id serial NOT NULL, -- id du produit
nom text, -- libelle du produit
sigle text, -- type : engrais ou amendement
adresse text, -- prix unitiaire du produit
cible integer, -- etat du produit
id_ag integer,
CONSTRAINT adsys_employeur_pkey PRIMARY KEY (id, id_ag)
)
WITH (
OIDS=FALSE
);

ALTER TABLE adsys_employeur
OWNER TO postgres;
COMMENT ON TABLE adsys_employeur
IS ' reference aux employeurs';
COMMENT ON COLUMN adsys_employeur.id IS 'id de l employeur';
COMMENT ON COLUMN adsys_employeur.nom IS 'nom de l employeur';
COMMENT ON COLUMN adsys_employeur.sigle IS 'sigle de l employeur';
COMMENT ON COLUMN adsys_employeur.adresse IS 'adresse';
COMMENT ON COLUMN adsys_employeur.cible IS 'cible';
COMMENT ON COLUMN adsys_employeur.id_ag IS 'id de l agence';

/***********************************************************************/

/***********************************************************************/

IF NOT EXISTS(SELECT * FROM tableliste WHERE nomc = 'adsys_employeur') THEN
	INSERT INTO tableliste VALUES ((select max(ident) from tableliste)+1, 'adsys_employeur', makeTraductionLangSyst('"Paramétrage des employeurs"'), true);
	RAISE NOTICE 'Données table adsys_employeur rajoutés dans table tableliste';
	END IF;

	tableliste_ident := (select ident from tableliste where nomc like 'adsys_employeur' order by ident desc limit 1);


	  -- Insertion dans d_tableliste champ ec_produit."libel"
	IF NOT EXISTS(SELECT * FROM d_tableliste WHERE nchmpc = 'nom' and tablen = tableliste_ident) THEN
	  INSERT INTO d_tableliste VALUES ((select max(ident) from d_tableliste)+1, tableliste_ident, 'nom', makeTraductionLangSyst('Nom employeur'), true, NULL, 'txt', false, false, false);
	END IF;

	IF NOT EXISTS(SELECT * FROM d_tableliste WHERE nchmpc = 'sigle' and tablen = tableliste_ident) THEN
	  INSERT INTO d_tableliste VALUES ((select max(ident) from d_tableliste)+1, tableliste_ident, 'sigle', makeTraductionLangSyst('Sigle'), false, NULL, 'txt', true, false, false);
	END IF;

	IF NOT EXISTS(SELECT * FROM d_tableliste WHERE nchmpc = 'adresse' and tablen = tableliste_ident) THEN
	  INSERT INTO d_tableliste VALUES ((select max(ident) from d_tableliste)+1, tableliste_ident, 'adresse', makeTraductionLangSyst('Adresse'), false, NULL, 'txt', false, false, false);
	END IF;

	IF NOT EXISTS(SELECT * FROM d_tableliste WHERE nchmpc = 'cible' and tablen = tableliste_ident) THEN
	 INSERT INTO d_tableliste VALUES ((select max(ident) from d_tableliste)+1, tableliste_ident, 'cible', makeTraductionLangSyst('Cible'), true, NULL, 'int', false, false, false);
	END IF;

	IF NOT EXISTS(SELECT * FROM d_tableliste WHERE nchmpc = 'id' and tablen = tableliste_ident) THEN
	 INSERT INTO d_tableliste VALUES ((select max(ident) from d_tableliste)+1, tableliste_ident, 'id', makeTraductionLangSyst('Id employeur'), true, NULL, 'int', false, true, false);
	END IF;

END IF;

--PP PARTENAUIRE
IF NOT EXISTS(SELECT * FROM information_schema.columns WHERE table_name = 'ad_cli' AND column_name = 'pp_partenaire') THEN
ALTER TABLE ad_cli ADD COLUMN pp_partenaire integer;
tableliste_ident := (select ident from tableliste where nomc like 'ad_cli' order by ident desc limit 1);
IF NOT EXISTS(SELECT * FROM d_tableliste WHERE nchmpc = 'pp_partenaire' and tablen = tableliste_ident) THEN
INSERT INTO d_tableliste VALUES ((select max(ident) from d_tableliste)+1, tableliste_ident, 'pp_partenaire', makeTraductionLangSyst('Partenaire'), null, (select ident from d_tableliste where nchmpc = 'id' and tablen = (select ident from tableliste where nomc = 'adsys_employeur')), 'int', NULL, NULL, false);
END IF;
END IF;

-- Matricule
IF NOT EXISTS(SELECT * FROM information_schema.columns WHERE table_name = 'ad_cli' AND column_name = 'matricule') THEN
ALTER TABLE ad_cli ADD COLUMN matricule text;
tableliste_ident := (select ident from tableliste where nomc like 'ad_cli' order by ident desc limit 1);
IF NOT EXISTS(SELECT * FROM d_tableliste WHERE nchmpc = 'matricule' and tablen = tableliste_ident) THEN
INSERT INTO d_tableliste VALUES ((select max(ident) from d_tableliste)+1, tableliste_ident, 'matricule', makeTraductionLangSyst('Numéro matricule'), NULL, NULL, 'txt', false, false, false);
END IF;
END IF;

--nombre period
IF NOT EXISTS(SELECT * FROM information_schema.columns WHERE table_name = 'ad_ord_perm' AND column_name = 'nb_periode') THEN
ALTER TABLE ad_ord_perm ADD COLUMN nb_periode integer;
END IF;

-- montant total
IF NOT EXISTS(SELECT * FROM information_schema.columns WHERE table_name = 'ad_ord_perm' AND column_name = 'mnt_total_prevu') THEN
ALTER TABLE ad_ord_perm ADD COLUMN mnt_total_prevu numeric(30,6);
END IF;


RAISE NOTICE 'FIN traitement';
RETURN output_result;

END;
$$
  LANGUAGE plpgsql VOLATILE
  COST 100;
ALTER FUNCTION patch_ma2e()
  OWNER TO adbanking;

SELECT patch_ma2e();

DROP FUNCTION IF EXISTS patch_ma2e();