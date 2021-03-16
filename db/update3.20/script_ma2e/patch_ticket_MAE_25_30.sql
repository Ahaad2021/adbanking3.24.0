CREATE OR REPLACE FUNCTION patch_cat_emp() RETURNS INT AS
$$
DECLARE

	tableliste_ident INTEGER = 0;

	output_result INTEGER = 1;

BEGIN

IF NOT EXISTS(SELECT * FROM information_schema.tables WHERE table_name = 'adsys_categorie_emp') THEN

CREATE TABLE adsys_categorie_emp
(
id serial NOT NULL, -- id du produit
libel text, -- libelle du produit
code text, -- type : engrais ou amendement
parent integer, -- prix unitiaire du produit
id_ag integer,
CONSTRAINT adsys_categorie_emp_pk PRIMARY KEY (id, id_ag)
)
WITH (
OIDS=FALSE
);

ALTER TABLE adsys_categorie_emp
OWNER TO postgres;
COMMENT ON TABLE adsys_categorie_emp
IS ' reference aux employeurs';
COMMENT ON COLUMN adsys_categorie_emp.id IS 'id';
COMMENT ON COLUMN adsys_categorie_emp.libel IS 'nom de la categorie';
COMMENT ON COLUMN adsys_categorie_emp.code IS 'code employeur';
COMMENT ON COLUMN adsys_categorie_emp.parent IS 'categorie du parent';
COMMENT ON COLUMN adsys_categorie_emp.id_ag IS 'id de l agence';

/***********************************************************************/

/***********************************************************************/

IF NOT EXISTS(SELECT * FROM tableliste WHERE nomc = 'adsys_categorie_emp') THEN
	INSERT INTO tableliste VALUES ((select max(ident) from tableliste)+1, 'adsys_categorie_emp', makeTraductionLangSyst('"Catégorie des employés"'), true);
	RAISE NOTICE 'Données table adsys_categorie_emp rajoutés dans table tableliste';
	END IF;

	tableliste_ident := (select ident from tableliste where nomc like 'adsys_categorie_emp' order by ident desc limit 1);


	  -- Insertion dans d_tableliste champ ec_produit."libel"
	IF NOT EXISTS(SELECT * FROM d_tableliste WHERE nchmpc = 'libel' and tablen = tableliste_ident) THEN
	  INSERT INTO d_tableliste VALUES ((select max(ident) from d_tableliste)+1, tableliste_ident, 'libel', makeTraductionLangSyst('Libellé'), true, NULL, 'txt', false, false, false);
	END IF;

	IF NOT EXISTS(SELECT * FROM d_tableliste WHERE nchmpc = 'code' and tablen = tableliste_ident) THEN
	  INSERT INTO d_tableliste VALUES ((select max(ident) from d_tableliste)+1, tableliste_ident, 'code', makeTraductionLangSyst('Code'), false, NULL, 'txt', true, false, false);
	END IF;

	IF NOT EXISTS(SELECT * FROM d_tableliste WHERE nchmpc = 'parent' and tablen = tableliste_ident) THEN
	  INSERT INTO d_tableliste VALUES ((select max(ident) from d_tableliste)+1, tableliste_ident, 'parent', makeTraductionLangSyst('Catégorie parent'), false, NULL, 'int', false, false, false);
	END IF;

	IF NOT EXISTS(SELECT * FROM d_tableliste WHERE nchmpc = 'id' and tablen = tableliste_ident) THEN
	 INSERT INTO d_tableliste VALUES ((select max(ident) from d_tableliste)+1, tableliste_ident, 'id', makeTraductionLangSyst('Id catégorie'), true, NULL, 'int', false, true, false);
	END IF;

END IF;



IF NOT EXISTS (SELECT nom_ecran FROM ecrans WHERE nom_ecran='Gce-1') THEN
	INSERT INTO ecrans(nom_ecran, fichier, nom_menu, fonction)
	VALUES ('Gce-1', 'modules/parametrage/tables.php', 'Pta', 292);
	RAISE NOTICE 'Added ecran Gce-1';
END IF;

IF NOT EXISTS (SELECT nom_ecran FROM ecrans WHERE nom_ecran='Gce-2') THEN
	INSERT INTO ecrans(nom_ecran, fichier, nom_menu, fonction)
	VALUES ('Gce-2', 'modules/parametrage/tables.php', 'Pta', 292);
	RAISE NOTICE 'Added ecran Gce-2';
END IF;

IF NOT EXISTS (SELECT nom_ecran FROM ecrans WHERE nom_ecran='Gce-3') THEN
	INSERT INTO ecrans(nom_ecran, fichier, nom_menu, fonction)
	VALUES ('Gce-3', 'modules/parametrage/tables.php', 'Pta', 292);
	RAISE NOTICE 'Added ecran Gce-3';
END IF;

IF NOT EXISTS (SELECT nom_ecran FROM ecrans WHERE nom_ecran='Gce-4') THEN
	INSERT INTO ecrans(nom_ecran, fichier, nom_menu, fonction)
	VALUES ('Gce-4', 'modules/parametrage/tables.php', 'Pta', 292);
	RAISE NOTICE 'Added ecran Gce-4';
END IF;
IF NOT EXISTS (SELECT nom_ecran FROM ecrans WHERE nom_ecran='Gce-5') THEN
	INSERT INTO ecrans(nom_ecran, fichier, nom_menu, fonction)
	VALUES ('Gce-5', 'modules/parametrage/tables.php', 'Pta', 292);
	RAISE NOTICE 'Added ecran Gce-5';
END IF;

IF NOT EXISTS(SELECT * FROM information_schema.columns WHERE table_name = 'ad_cli' AND column_name = 'categorie') THEN
ALTER TABLE ad_cli
ADD COLUMN categorie int;
tableliste_ident := (select ident from tableliste where nomc like 'ad_cli' order by ident desc limit 1);
INSERT INTO d_tableliste VALUES ((select max(ident) from d_tableliste)+1,tableliste_ident, 'categorie', makeTraductionLangSyst('Catégorie employé'), NULL, (select ident from d_tableliste where nchmpc = 'id' and tablen = (select ident from tableliste where nomc = 'adsys_categorie_emp')), 'int', null, null, false);
END IF;

IF NOT EXISTS(SELECT * FROM information_schema.columns WHERE table_name = 'ad_cli' AND column_name = 'classe') THEN
ALTER TABLE ad_cli
ADD COLUMN classe int;
tableliste_ident := (select ident from tableliste where nomc like 'ad_cli' order by ident desc limit 1);
INSERT INTO d_tableliste VALUES ((select max(ident) from d_tableliste)+1,tableliste_ident, 'classe', makeTraductionLangSyst('Classe'), NULL, (select ident from d_tableliste where nchmpc = 'id' and tablen = (select ident from tableliste where nomc = 'adsys_categorie_emp')), 'int', null, null, false);
END IF;

IF NOT EXISTS(SELECT * FROM information_schema.columns WHERE table_name = 'ad_cli' AND column_name = 'id_card') THEN
ALTER TABLE ad_cli
ADD COLUMN id_card int;
tableliste_ident := (select ident from tableliste where nomc like 'ad_cli' order by ident desc limit 1);
INSERT INTO d_tableliste VALUES ((select max(ident) from d_tableliste)+1,1, 'id_card', makeTraductionLangSyst('Numéro carte bancaire'), NULL, NULL, 'int', null, null, false);
END IF;


RAISE NOTICE 'FIN traitement';
RETURN output_result;

END;
$$
LANGUAGE plpgsql;

select patch_cat_emp();

DROP function patch_cat_emp();
