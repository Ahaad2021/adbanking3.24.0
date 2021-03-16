  CREATE OR REPLACE FUNCTION module_budget() RETURNS INT AS
$BODY$
DECLARE
output_result INTEGER = 1;
tableliste_ident INTEGER = 0;
tableliste_str INTEGER = 0;
d_tableliste_str INTEGER = 0;

BEGIN

-- Creation table ad_correspondance + d_tableliste
IF NOT EXISTS(SELECT * FROM information_schema.tables WHERE table_name = 'ad_correspondance') THEN

   CREATE TABLE ad_correspondance
(
  id serial NOT NULL,
  etat_correspondance boolean DEFAULT true,
  type_budget integer,
  poste_principal integer,
  poste_niveau_1 integer,
  poste_niveau_2 integer,
  poste_niveau_3 integer,
  description text,
  compartiment integer,
  dernier_niveau boolean DEFAULT false,
  date_creation timestamp without time zone,
  date_modif timestamp without time zone,
  id_ag integer,
  CONSTRAINT ad_correspondance_pkey PRIMARY KEY(id, id_ag)
)
WITH (
  OIDS=FALSE
);
  ALTER TABLE ad_correspondance
    OWNER TO postgres;
  COMMENT ON TABLE ad_correspondance
    IS ' la table de correspondance';
  COMMENT ON COLUMN ad_correspondance.id IS 'id du ad_correspondance';
  COMMENT ON COLUMN ad_correspondance.etat_correspondance IS 'etat du poste';
  COMMENT ON COLUMN ad_correspondance.type_budget IS 'type de budget';
  COMMENT ON COLUMN ad_correspondance.poste_principal IS 'Poste principale';
  COMMENT ON COLUMN ad_correspondance.poste_niveau_1 IS 'Sous poste de niveau 1';
  COMMENT ON COLUMN ad_correspondance.poste_niveau_2 IS 'Sous poste de niveau 2';
  COMMENT ON COLUMN ad_correspondance.poste_niveau_3 IS 'Sous poste de niveau 3';
  COMMENT ON COLUMN ad_correspondance.description IS ' description';
  COMMENT ON COLUMN ad_correspondance.compartiment IS ' Compartiment (charge/produit/actif/passif)';
  COMMENT ON COLUMN ad_correspondance.dernier_niveau IS 'Dernier niveau de la hierarchie';
  COMMENT ON COLUMN ad_correspondance.date_creation IS ' date de creation';
  COMMENT ON COLUMN ad_correspondance.date_modif IS ' date modification';
  COMMENT ON COLUMN ad_correspondance.id_ag IS ' id de agence';
  		RAISE NOTICE 'Table ad_correspondance created';

	-- Insertion dans tableliste
	IF NOT EXISTS(SELECT * FROM tableliste WHERE nomc = 'ad_correspondance') THEN
	tableliste_str := makeTraductionLangSyst('Gestion des tables de correspondances');
	INSERT INTO tableliste VALUES ((select max(ident) from tableliste)+1, 'ad_correspondance', tableliste_str , true);
	IF EXISTS(SELECT langue FROM adsys_langues_systeme WHERE code = 'en_GB') THEN
    INSERT INTO ad_traductions VALUES (tableliste_str,'en_GB', 'Mapping tables management');
    RAISE NOTICE 'Données table ad_correspondance rajoutés dans table tableliste';
	END IF;
	END IF;

	tableliste_ident := (select ident from tableliste where nomc like 'ad_correspondance' order by ident desc limit 1);

	IF NOT EXISTS(SELECT * FROM d_tableliste WHERE nchmpc = 'etat_correspondance' and tablen = tableliste_ident) THEN
	  d_tableliste_str := makeTraductionLangSyst('Etat ligne correspondance');
	  INSERT INTO d_tableliste VALUES ((select max(ident) from d_tableliste)+1, tableliste_ident, 'etat_correspondance', d_tableliste_str, true, NULL, 'bol', false, false, false);
    IF EXISTS(SELECT langue FROM adsys_langues_systeme WHERE code = 'en_GB') THEN
      INSERT INTO ad_traductions VALUES (d_tableliste_str,'en_GB','Line status');
    END IF;
	END IF;

	IF NOT EXISTS(SELECT * FROM d_tableliste WHERE nchmpc = 'type_budget' and tablen = tableliste_ident) THEN
	  d_tableliste_str := makeTraductionLangSyst('Type de budget');
	  INSERT INTO d_tableliste VALUES ((select max(ident) from d_tableliste)+1, tableliste_ident, 'type_budget', d_tableliste_str, true, NULL, 'int', false, false, false);
    IF EXISTS(SELECT langue FROM adsys_langues_systeme WHERE code = 'en_GB') THEN
      INSERT INTO ad_traductions VALUES (d_tableliste_str,'en_GB','Type of budget');
    END IF;
	END IF;

	IF NOT EXISTS(SELECT * FROM d_tableliste WHERE nchmpc = 'poste_principal' and tablen = tableliste_ident) THEN
	  d_tableliste_str := makeTraductionLangSyst('Poste principal');
	  INSERT INTO d_tableliste VALUES ((select max(ident) from d_tableliste)+1, tableliste_ident, 'poste_principal', d_tableliste_str, true, NULL, 'int', false, false, false);
    IF EXISTS(SELECT langue FROM adsys_langues_systeme WHERE code = 'en_GB') THEN
      INSERT INTO ad_traductions VALUES (d_tableliste_str,'en_GB','Main item');
    END IF;
	END IF;

	IF NOT EXISTS(SELECT * FROM d_tableliste WHERE nchmpc = 'poste_niveau_1' and tablen = tableliste_ident) THEN
	  d_tableliste_str := makeTraductionLangSyst('Sous poste niveau 1');
	  INSERT INTO d_tableliste VALUES ((select max(ident) from d_tableliste)+1, tableliste_ident, 'poste_niveau_1', d_tableliste_str, true, NULL, 'int', false, false, false);
    IF EXISTS(SELECT langue FROM adsys_langues_systeme WHERE code = 'en_GB') THEN
      INSERT INTO ad_traductions VALUES (d_tableliste_str,'en_GB','Sub item level 1');
    END IF;
	END IF;

	IF NOT EXISTS(SELECT * FROM d_tableliste WHERE nchmpc = 'poste_niveau_2' and tablen = tableliste_ident) THEN
	  d_tableliste_str := makeTraductionLangSyst('Sous poste niveau 2');
	  INSERT INTO d_tableliste VALUES ((select max(ident) from d_tableliste)+1, tableliste_ident, 'poste_niveau_2', d_tableliste_str, true, NULL, 'int', false, false, false);
    IF EXISTS(SELECT langue FROM adsys_langues_systeme WHERE code = 'en_GB') THEN
      INSERT INTO ad_traductions VALUES (d_tableliste_str,'en_GB','Sub item level 2');
    END IF;
	END IF;

	IF NOT EXISTS(SELECT * FROM d_tableliste WHERE nchmpc = 'poste_niveau_3' and tablen = tableliste_ident) THEN
	  d_tableliste_str := makeTraductionLangSyst('Sous poste niveau 3');
	  INSERT INTO d_tableliste VALUES ((select max(ident) from d_tableliste)+1, tableliste_ident, 'poste_niveau_3', d_tableliste_str, true, NULL, 'int', false, false, false);
    IF EXISTS(SELECT langue FROM adsys_langues_systeme WHERE code = 'en_GB') THEN
      INSERT INTO ad_traductions VALUES (d_tableliste_str,'en_GB','Sub item level 3');
    END IF;
	END IF;

	IF NOT EXISTS(SELECT * FROM d_tableliste WHERE nchmpc = 'description' and tablen = tableliste_ident) THEN
	  d_tableliste_str := makeTraductionLangSyst('Description');
	  INSERT INTO d_tableliste VALUES ((select max(ident) from d_tableliste)+1, tableliste_ident, 'description', d_tableliste_str, true, NULL, 'txt', false, false, false);
    IF EXISTS(SELECT langue FROM adsys_langues_systeme WHERE code = 'en_GB') THEN
      INSERT INTO ad_traductions VALUES (d_tableliste_str,'en_GB','Description');
    END IF;
	END IF;

	IF NOT EXISTS(SELECT * FROM d_tableliste WHERE nchmpc = 'compartiment' and tablen = tableliste_ident) THEN
	  d_tableliste_str := makeTraductionLangSyst('Compartiment');
	  INSERT INTO d_tableliste VALUES ((select max(ident) from d_tableliste)+1, tableliste_ident, 'compartiment', d_tableliste_str, true, NULL, 'int', false, false, false);
    IF EXISTS(SELECT langue FROM adsys_langues_systeme WHERE code = 'en_GB') THEN
      INSERT INTO ad_traductions VALUES (d_tableliste_str,'en_GB','Compartment');
    END IF;
	END IF;

	IF NOT EXISTS(SELECT * FROM d_tableliste WHERE nchmpc = 'dernier_niveau' and tablen = tableliste_ident) THEN
	  d_tableliste_str := makeTraductionLangSyst('Dernier niveau');
	  INSERT INTO d_tableliste VALUES ((select max(ident) from d_tableliste)+1, tableliste_ident, 'dernier_niveau', d_tableliste_str, true, NULL, 'bol', false, false, false);
    IF EXISTS(SELECT langue FROM adsys_langues_systeme WHERE code = 'en_GB') THEN
      INSERT INTO ad_traductions VALUES (d_tableliste_str,'en_GB','Last level');
    END IF;
	END IF;


	IF NOT EXISTS(SELECT * FROM d_tableliste WHERE nchmpc = 'date_creation' and tablen = tableliste_ident) THEN
	  d_tableliste_str := makeTraductionLangSyst('Date de creation');
	  INSERT INTO d_tableliste VALUES ((select max(ident) from d_tableliste)+1, tableliste_ident, 'date_creation', d_tableliste_str, true, NULL, 'dte', false, false, false);
    IF EXISTS(SELECT langue FROM adsys_langues_systeme WHERE code = 'en_GB') THEN
      INSERT INTO ad_traductions VALUES (d_tableliste_str,'en_GB','Creation date');
    END IF;
	END IF;

	IF NOT EXISTS(SELECT * FROM d_tableliste WHERE nchmpc = 'date_modif' and tablen = tableliste_ident) THEN
	  d_tableliste_str := makeTraductionLangSyst('Date de modification budget');
	  INSERT INTO d_tableliste VALUES ((select max(ident) from d_tableliste)+1, tableliste_ident, 'date_modif', d_tableliste_str, true, NULL, 'dte', false, false, false);
    IF EXISTS(SELECT langue FROM adsys_langues_systeme WHERE code = 'en_GB') THEN
      INSERT INTO ad_traductions VALUES (d_tableliste_str,'en_GB','Modification date');
    END IF;
	END IF;
END IF;

/************/

-- Creation table ad_budget + d_tableliste
IF NOT EXISTS(SELECT * FROM information_schema.tables WHERE table_name = 'ad_budget') THEN

   CREATE TABLE ad_budget
(
  id_budget serial NOT NULL,
  exo_budget integer,
  ref_budget text,
  type_budget integer,
  etat_budget integer,
  date_creation timestamp without time zone,
  date_modif timestamp without time zone,
  id_ag integer,
  CONSTRAINT ad_budget_pkey PRIMARY KEY (id_budget, id_ag)
)
WITH (
  OIDS=FALSE
);
  ALTER TABLE ad_budget
    OWNER TO postgres;
  COMMENT ON TABLE ad_budget
    IS ' la table du budget';
  COMMENT ON COLUMN ad_budget.id_budget IS 'id du budget';
  COMMENT ON COLUMN ad_budget.exo_budget IS 'exercice budget';
  COMMENT ON COLUMN ad_budget.ref_budget IS 'ref du budget';
  COMMENT ON COLUMN ad_budget.type_budget IS 'type budget';
  COMMENT ON COLUMN ad_budget.etat_budget IS ' etat budget';
  COMMENT ON COLUMN ad_budget.date_creation IS ' date creation';
  COMMENT ON COLUMN ad_budget.date_modif IS ' date modif';
  COMMENT ON COLUMN ad_budget.id_ag IS ' id de agence';
  		RAISE NOTICE 'Table ad_budget created';

  		        -- Insertion dans tableliste
  IF NOT EXISTS(SELECT * FROM tableliste WHERE nomc = 'ad_budget') THEN
  tableliste_str := makeTraductionLangSyst('Gestion des tables de budget');
  INSERT INTO tableliste VALUES ((select max(ident) from tableliste)+1, 'ad_budget', tableliste_str , true);
  IF EXISTS(SELECT langue FROM adsys_langues_systeme WHERE code = 'en_GB') THEN
    INSERT INTO ad_traductions VALUES (tableliste_str,'en_GB', 'Budget tables management');
    RAISE NOTICE 'Données table ad_budget rajoutés dans table tableliste';
  END IF;
  END IF;

  tableliste_ident := (select ident from tableliste where nomc like 'ad_budget' order by ident desc limit 1);

  IF NOT EXISTS(SELECT * FROM d_tableliste WHERE nchmpc = 'exo_budget' and tablen = tableliste_ident) THEN
    d_tableliste_str := makeTraductionLangSyst('Exercice budget');
    INSERT INTO d_tableliste VALUES ((select max(ident) from d_tableliste)+1, tableliste_ident, 'exo_budget', d_tableliste_str, true, NULL, 'int', false, false, false);
    IF EXISTS(SELECT langue FROM adsys_langues_systeme WHERE code = 'en_GB') THEN
      INSERT INTO ad_traductions VALUES (d_tableliste_str,'en_GB','Budget Exercice');
    END IF;
  END IF;

  IF NOT EXISTS(SELECT * FROM d_tableliste WHERE nchmpc = 'ref_budget' and tablen = tableliste_ident) THEN
  d_tableliste_str := makeTraductionLangSyst('Référence budget');
  INSERT INTO d_tableliste VALUES ((select max(ident) from d_tableliste)+1, tableliste_ident, 'ref_budget', d_tableliste_str, true, NULL, 'txt', false, false, false);
    IF EXISTS(SELECT langue FROM adsys_langues_systeme WHERE code = 'en_GB') THEN
      INSERT INTO ad_traductions VALUES (d_tableliste_str,'en_GB','Budget reference');
    END IF;
  END IF;

  IF NOT EXISTS(SELECT * FROM d_tableliste WHERE nchmpc = 'type_budget' and tablen = tableliste_ident) THEN
  d_tableliste_str := makeTraductionLangSyst('Type budget');
  INSERT INTO d_tableliste VALUES ((select max(ident) from d_tableliste)+1, tableliste_ident, 'type_budget', d_tableliste_str, true, NULL, 'int', false, false, false);
    IF EXISTS(SELECT langue FROM adsys_langues_systeme WHERE code = 'en_GB') THEN
      INSERT INTO ad_traductions VALUES (d_tableliste_str,'en_GB','Dudget type');
    END IF;
  END IF;

   IF NOT EXISTS(SELECT * FROM d_tableliste WHERE nchmpc = 'etat_budget' and tablen = tableliste_ident) THEN
  d_tableliste_str := makeTraductionLangSyst('Etat budget');
  INSERT INTO d_tableliste VALUES ((select max(ident) from d_tableliste)+1, tableliste_ident, 'etat_budget', d_tableliste_str, true, NULL, 'int', false, false, false);
    IF EXISTS(SELECT langue FROM adsys_langues_systeme WHERE code = 'en_GB') THEN
      INSERT INTO ad_traductions VALUES (d_tableliste_str,'en_GB','budget status');
    END IF;
  END IF;

  IF NOT EXISTS(SELECT * FROM d_tableliste WHERE nchmpc = 'date_creation' and tablen = tableliste_ident) THEN
    d_tableliste_str := makeTraductionLangSyst('Date création');
    INSERT INTO d_tableliste VALUES ((select max(ident) from d_tableliste)+1, tableliste_ident, 'date_creation', d_tableliste_str, true, NULL, 'dte', false, false, false);
    IF EXISTS(SELECT langue FROM adsys_langues_systeme WHERE code = 'en_GB') THEN
      INSERT INTO ad_traductions VALUES (d_tableliste_str,'en_GB','creation date');
    END IF;
  END IF;

  IF NOT EXISTS(SELECT * FROM d_tableliste WHERE nchmpc = 'date_modif' and tablen = tableliste_ident) THEN
    d_tableliste_str := makeTraductionLangSyst('Date Modification');
    INSERT INTO d_tableliste VALUES ((select max(ident) from d_tableliste)+1, tableliste_ident, 'date_modif', d_tableliste_str, true, NULL, 'dte', false, false, false);
    IF EXISTS(SELECT langue FROM adsys_langues_systeme WHERE code = 'en_GB') THEN
      INSERT INTO ad_traductions VALUES (d_tableliste_str,'en_GB','Modification date');
    END IF;
  END IF;
END IF;

-- Creation table ad_ligne_budgetaire + d_tableliste
IF NOT EXISTS(SELECT * FROM information_schema.tables WHERE table_name = 'ad_ligne_budgetaire') THEN

   CREATE TABLE ad_ligne_budgetaire
(
  id_ligne serial NOT NULL,
  id_correspondance integer,
  ref_budget text,
  poste_budget text,
  mnt_trim1 numeric(30,6),
  mnt_restant_trim1 numeric(30,6),
  prc_utilisation_trim1 numeric(30,6),
  mnt_trim2 numeric(30,6),
  mnt_restant_trim2 numeric(30,6),
  prc_utilisation_trim2 numeric(30,6),
  mnt_trim3 numeric(30,6),
  mnt_restant_trim3 numeric(30,6),
  prc_utilisation_trim3 numeric(30,6),
  mnt_trim4 numeric(30,6),
  mnt_restant_trim4 numeric(30,6),
  prc_utilisation_trim4 numeric(30,6),
  etat_bloque boolean DEFAULT false,
  etat_ligne integer,
  date_creation timestamp without time zone,
  date_modif timestamp without time zone,
  id_ag integer,
  CONSTRAINT ad_ligne_budgetaire_pkey PRIMARY KEY (id_ligne, id_ag)
)
WITH (
  OIDS=FALSE
);
  ALTER TABLE ad_ligne_budgetaire
    OWNER TO postgres;
  COMMENT ON TABLE ad_ligne_budgetaire
    IS ' la table du budget';
  COMMENT ON COLUMN ad_ligne_budgetaire.id_ligne IS 'id du budget';
  COMMENT ON COLUMN ad_ligne_budgetaire.id_correspondance IS 'nom du corespondant';
  COMMENT ON COLUMN ad_ligne_budgetaire.ref_budget IS 'ref du budget';
  COMMENT ON COLUMN ad_ligne_budgetaire.poste_budget IS 'reference au poste budgetaire ';
  COMMENT ON COLUMN ad_ligne_budgetaire.mnt_trim1 IS 'montant trim 1';
  COMMENT ON COLUMN ad_ligne_budgetaire.mnt_restant_trim1 IS ' montant restant trim 1';
  COMMENT ON COLUMN ad_ligne_budgetaire.prc_utilisation_trim1 IS ' pourcentage utiliser trim1';
  COMMENT ON COLUMN ad_ligne_budgetaire.mnt_trim2 IS ' montant trim 2';
  COMMENT ON COLUMN ad_ligne_budgetaire.mnt_restant_trim2 IS ' montant restant trim 2';
  COMMENT ON COLUMN ad_ligne_budgetaire.prc_utilisation_trim2 IS ' pourcentage utiliser trim2';
  COMMENT ON COLUMN ad_ligne_budgetaire.mnt_trim3 IS ' montant trim 3';
  COMMENT ON COLUMN ad_ligne_budgetaire.mnt_restant_trim3 IS ' montant restant trim 3';
  COMMENT ON COLUMN ad_ligne_budgetaire.prc_utilisation_trim3 IS 'pourcentage utiliser trim3';
  COMMENT ON COLUMN ad_ligne_budgetaire.mnt_trim4 IS ' montant trim 4';
  COMMENT ON COLUMN ad_ligne_budgetaire.mnt_restant_trim4 IS ' montant restant trim 4';
  COMMENT ON COLUMN ad_ligne_budgetaire.prc_utilisation_trim4 IS ' pourcentage utiliser trim4';
  COMMENT ON COLUMN ad_ligne_budgetaire.etat_bloque IS ' Etat bloquer boolean';
  COMMENT ON COLUMN ad_ligne_budgetaire.etat_ligne IS ' Etat ligne budgetaire integer';
  COMMENT ON COLUMN ad_ligne_budgetaire.date_creation IS ' date creation';
  COMMENT ON COLUMN ad_ligne_budgetaire.date_modif IS ' date modif';
  COMMENT ON COLUMN ad_ligne_budgetaire.id_ag IS ' id de agence';
  		RAISE NOTICE 'Table ad_ligne_budgetaire created';

        -- Insertion dans tableliste
  IF NOT EXISTS(SELECT * FROM tableliste WHERE nomc = 'ad_ligne_budgetaire') THEN
  tableliste_str := makeTraductionLangSyst('Table ligne budgetaire');
  INSERT INTO tableliste VALUES ((select max(ident) from tableliste)+1, 'ad_ligne_budgetaire', tableliste_str , true);
    IF EXISTS(SELECT langue FROM adsys_langues_systeme WHERE code = 'en_GB') THEN
      INSERT INTO ad_traductions VALUES (tableliste_str,'en_GB', 'Budget line table');
      RAISE NOTICE 'Données table ad_ligne_budgetaire rajoutées dans table tableliste';
    END IF;
  END IF;

  tableliste_ident := (select ident from tableliste where nomc like 'ad_ligne_budgetaire' order by ident desc limit 1);

  IF NOT EXISTS(SELECT * FROM d_tableliste WHERE nchmpc = 'ref_budget' and tablen = tableliste_ident) THEN
    d_tableliste_str := makeTraductionLangSyst('Reference budget');
    INSERT INTO d_tableliste VALUES ((select max(ident) from d_tableliste)+1, tableliste_ident, 'ref_budget', d_tableliste_str, true, NULL, 'txt', false, false, false);
    IF EXISTS(SELECT langue FROM adsys_langues_systeme WHERE code = 'en_GB') THEN
      INSERT INTO ad_traductions VALUES (d_tableliste_str,'en_GB','Budget reference');
    END IF;
  END IF;

  IF NOT EXISTS(SELECT * FROM d_tableliste WHERE nchmpc = 'poste_budget' and tablen = tableliste_ident) THEN
    d_tableliste_str := makeTraductionLangSyst('Poste budget');
    INSERT INTO d_tableliste VALUES ((select max(ident) from d_tableliste)+1, tableliste_ident, 'poste_budget', d_tableliste_str, true, NULL, 'txt', false, false, false);
    IF EXISTS(SELECT langue FROM adsys_langues_systeme WHERE code = 'en_GB') THEN
      INSERT INTO ad_traductions VALUES (d_tableliste_str,'en_GB','Budget item');
    END IF;
  END IF;

  IF NOT EXISTS(SELECT * FROM d_tableliste WHERE nchmpc = 'mnt_trim1' and tablen = tableliste_ident) THEN
    d_tableliste_str := makeTraductionLangSyst('Montant Budget Trimestre 1');
    INSERT INTO d_tableliste VALUES ((select max(ident) from d_tableliste)+1, tableliste_ident, 'mnt_trim1', d_tableliste_str, true, NULL, 'mnt', false, false, false);
    IF EXISTS(SELECT langue FROM adsys_langues_systeme WHERE code = 'en_GB') THEN
      INSERT INTO ad_traductions VALUES (d_tableliste_str,'en_GB','Amount first quarter');
    END IF;
  END IF;

  IF NOT EXISTS(SELECT * FROM d_tableliste WHERE nchmpc = 'mnt_restant_trim1' and tablen = tableliste_ident) THEN
    d_tableliste_str := makeTraductionLangSyst('Montant Budget Restant Trimestre 1');
    INSERT INTO d_tableliste VALUES ((select max(ident) from d_tableliste)+1, tableliste_ident, 'mnt_restant_trim1', d_tableliste_str, true, NULL, 'mnt', false, false, false);
    IF EXISTS(SELECT langue FROM adsys_langues_systeme WHERE code = 'en_GB') THEN
      INSERT INTO ad_traductions VALUES (d_tableliste_str,'en_GB','Remaining amount first quarter');
    END IF;
  END IF;

  IF NOT EXISTS(SELECT * FROM d_tableliste WHERE nchmpc = 'prc_utilisation_trim1' and tablen = tableliste_ident) THEN
    d_tableliste_str := makeTraductionLangSyst('% Utilisé Trimestre 1');
    INSERT INTO d_tableliste VALUES ((select max(ident) from d_tableliste)+1, tableliste_ident, 'prc_utilisation_trim1', d_tableliste_str, true, NULL, 'mnt', false, false, false);
    IF EXISTS(SELECT langue FROM adsys_langues_systeme WHERE code = 'en_GB') THEN
      INSERT INTO ad_traductions VALUES (d_tableliste_str,'en_GB','Used percentage first quarter');
    END IF;
  END IF;

  IF NOT EXISTS(SELECT * FROM d_tableliste WHERE nchmpc = 'mnt_trim2' and tablen = tableliste_ident) THEN
    d_tableliste_str := makeTraductionLangSyst('Montant Budget Trimestre 2');
    INSERT INTO d_tableliste VALUES ((select max(ident) from d_tableliste)+1, tableliste_ident, 'mnt_trim2', d_tableliste_str, true, NULL, 'mnt', false, false, false);
    IF EXISTS(SELECT langue FROM adsys_langues_systeme WHERE code = 'en_GB') THEN
      INSERT INTO ad_traductions VALUES (d_tableliste_str,'en_GB','Amount second quarter');
    END IF;
  END IF;

  IF NOT EXISTS(SELECT * FROM d_tableliste WHERE nchmpc = 'mnt_restant_trim2' and tablen = tableliste_ident) THEN
    d_tableliste_str := makeTraductionLangSyst('Montant Budget Restant Trimestre 2');
    INSERT INTO d_tableliste VALUES ((select max(ident) from d_tableliste)+1, tableliste_ident, 'mnt_restant_trim2', d_tableliste_str, true, NULL, 'mnt', false, false, false);
    IF EXISTS(SELECT langue FROM adsys_langues_systeme WHERE code = 'en_GB') THEN
      INSERT INTO ad_traductions VALUES (d_tableliste_str,'en_GB','Remaining amount second quarter');
    END IF;
  END IF;

  IF NOT EXISTS(SELECT * FROM d_tableliste WHERE nchmpc = 'prc_utilisation_trim2' and tablen = tableliste_ident) THEN
    d_tableliste_str := makeTraductionLangSyst('% Utilisé Trimestre 2');
    INSERT INTO d_tableliste VALUES ((select max(ident) from d_tableliste)+1, tableliste_ident, 'prc_utilisation_trim2', d_tableliste_str, true, NULL, 'mnt', false, false, false);
    IF EXISTS(SELECT langue FROM adsys_langues_systeme WHERE code = 'en_GB') THEN
      INSERT INTO ad_traductions VALUES (d_tableliste_str,'en_GB','Used percentage second quarter');
    END IF;
  END IF;

  IF NOT EXISTS(SELECT * FROM d_tableliste WHERE nchmpc = 'mnt_trim3' and tablen = tableliste_ident) THEN
    d_tableliste_str := makeTraductionLangSyst('Montant Budget Trimestre 3');
    INSERT INTO d_tableliste VALUES ((select max(ident) from d_tableliste)+1, tableliste_ident, 'mnt_trim3', d_tableliste_str, true, NULL, 'mnt', false, false, false);
    IF EXISTS(SELECT langue FROM adsys_langues_systeme WHERE code = 'en_GB') THEN
      INSERT INTO ad_traductions VALUES (d_tableliste_str,'en_GB','Amount third quarter');
    END IF;
  END IF;

  IF NOT EXISTS(SELECT * FROM d_tableliste WHERE nchmpc = 'mnt_restant_trim3' and tablen = tableliste_ident) THEN
    d_tableliste_str := makeTraductionLangSyst('Montant Budget Restant Trimestre 3');
    INSERT INTO d_tableliste VALUES ((select max(ident) from d_tableliste)+1, tableliste_ident, 'mnt_restant_trim3', d_tableliste_str, true, NULL, 'mnt', false, false, false);
    IF EXISTS(SELECT langue FROM adsys_langues_systeme WHERE code = 'en_GB') THEN
      INSERT INTO ad_traductions VALUES (d_tableliste_str,'en_GB','Remaining amount third quarter');
    END IF;
  END IF;

  IF NOT EXISTS(SELECT * FROM d_tableliste WHERE nchmpc = 'prc_utilisation_trim3' and tablen = tableliste_ident) THEN
    d_tableliste_str := makeTraductionLangSyst('% Utilisé Trimestre 3');
    INSERT INTO d_tableliste VALUES ((select max(ident) from d_tableliste)+1, tableliste_ident, 'prc_utilisation_trim3', d_tableliste_str, true, NULL, 'mnt', false, false, false);
    IF EXISTS(SELECT langue FROM adsys_langues_systeme WHERE code = 'en_GB') THEN
      INSERT INTO ad_traductions VALUES (d_tableliste_str,'en_GB','Used percentage third quarter');
    END IF;
  END IF;

  IF NOT EXISTS(SELECT * FROM d_tableliste WHERE nchmpc = 'mnt_trim4' and tablen = tableliste_ident) THEN
    d_tableliste_str := makeTraductionLangSyst('Montant Budget Trimestre 4');
    INSERT INTO d_tableliste VALUES ((select max(ident) from d_tableliste)+1, tableliste_ident, 'mnt_trim4', d_tableliste_str, true, NULL, 'mnt', false, false, false);
    IF EXISTS(SELECT langue FROM adsys_langues_systeme WHERE code = 'en_GB') THEN
      INSERT INTO ad_traductions VALUES (d_tableliste_str,'en_GB','Amount fourth quarter');
    END IF;
  END IF;

  IF NOT EXISTS(SELECT * FROM d_tableliste WHERE nchmpc = 'mnt_restant_trim4' and tablen = tableliste_ident) THEN
    d_tableliste_str := makeTraductionLangSyst('Montant Budget Restant Trimestre 4');
    INSERT INTO d_tableliste VALUES ((select max(ident) from d_tableliste)+1, tableliste_ident, 'mnt_restant_trim4', d_tableliste_str, true, NULL, 'mnt', false, false, false);
    IF EXISTS(SELECT langue FROM adsys_langues_systeme WHERE code = 'en_GB') THEN
      INSERT INTO ad_traductions VALUES (d_tableliste_str,'en_GB','Remaining amount fourth quarter');
    END IF;
  END IF;

  IF NOT EXISTS(SELECT * FROM d_tableliste WHERE nchmpc = 'prc_utilisation_trim4' and tablen = tableliste_ident) THEN
    d_tableliste_str := makeTraductionLangSyst('% Utilisé Trimestre 4');
    INSERT INTO d_tableliste VALUES ((select max(ident) from d_tableliste)+1, tableliste_ident, 'prc_utilisation_trim4', d_tableliste_str, true, NULL, 'mnt', false, false, false);
    IF EXISTS(SELECT langue FROM adsys_langues_systeme WHERE code = 'en_GB') THEN
      INSERT INTO ad_traductions VALUES (d_tableliste_str,'en_GB','Used percentage fourth quarter');
    END IF;
  END IF;

  IF NOT EXISTS(SELECT * FROM d_tableliste WHERE nchmpc = 'date_creation' and tablen = tableliste_ident) THEN
    d_tableliste_str := makeTraductionLangSyst('Date création ligne budgetaire');
    INSERT INTO d_tableliste VALUES ((select max(ident) from d_tableliste)+1, tableliste_ident, 'date_creation', d_tableliste_str, true, NULL, 'dte', false, false, false);
    IF EXISTS(SELECT langue FROM adsys_langues_systeme WHERE code = 'en_GB') THEN
      INSERT INTO ad_traductions VALUES (d_tableliste_str,'en_GB','Creation date');
    END IF;
  END IF;

  IF NOT EXISTS(SELECT * FROM d_tableliste WHERE nchmpc = 'date_modif' and tablen = tableliste_ident) THEN
    d_tableliste_str := makeTraductionLangSyst('Date Modification ligne budgetaire');
    INSERT INTO d_tableliste VALUES ((select max(ident) from d_tableliste)+1, tableliste_ident, 'date_modif', d_tableliste_str, true, NULL, 'dte', false, false, false);
    IF EXISTS(SELECT langue FROM adsys_langues_systeme WHERE code = 'en_GB') THEN
      INSERT INTO ad_traductions VALUES (d_tableliste_str,'en_GB','Modification date');
    END IF;
  END IF;

  IF NOT EXISTS(SELECT * FROM d_tableliste WHERE nchmpc = 'etat_bloque' and tablen = tableliste_ident) THEN
    d_tableliste_str := makeTraductionLangSyst('Etat bloqué');
    INSERT INTO d_tableliste VALUES ((select max(ident) from d_tableliste)+1, tableliste_ident, 'etat_bloque', d_tableliste_str, true, NULL, 'bol', false, false, false);
    IF EXISTS(SELECT langue FROM adsys_langues_systeme WHERE code = 'en_GB') THEN
      INSERT INTO ad_traductions VALUES (d_tableliste_str,'en_GB','Blocked status');
    END IF;
  END IF;

	IF NOT EXISTS(SELECT * FROM d_tableliste WHERE nchmpc = 'etat_ligne' and tablen = tableliste_ident) THEN
	  d_tableliste_str := makeTraductionLangSyst('Etat ligne budgetaire');
	  INSERT INTO d_tableliste VALUES ((select max(ident) from d_tableliste)+1, tableliste_ident, 'etat_ligne', d_tableliste_str, false, NULL, 'int', false, false, false);
    IF EXISTS(SELECT langue FROM adsys_langues_systeme WHERE code = 'en_GB') THEN
      INSERT INTO ad_traductions VALUES (d_tableliste_str,'en_GB','Status budgtet line');
    END IF;

	END IF;

END IF;


-- Creation table ad_revision_historique + d_tableliste
IF NOT EXISTS(SELECT * FROM information_schema.tables WHERE table_name = 'ad_revision_historique') THEN

   CREATE TABLE ad_revision_historique
(
  id_revision serial NOT NULL,
  exo_budget integer,
  ref_budget text,
  id_ligne_budget integer,
  id_trimestre integer,
  anc_montant numeric(30,6),
  nouv_montant numeric(30,6),
  id_util_revise integer,
  id_util_valide integer,
  etat_revision integer,
  date_creation timestamp without time zone,
  date_modif timestamp without time zone,
  id_ag integer,
  CONSTRAINT ad_revision_historique_pkey PRIMARY KEY (id_revision, id_ag)
)
WITH (
  OIDS=FALSE
);
  ALTER TABLE ad_revision_historique
    OWNER TO postgres;
  COMMENT ON TABLE ad_revision_historique
    IS ' la table historisation des revision';
  COMMENT ON COLUMN ad_revision_historique.id_revision IS 'id de la revision';
  COMMENT ON COLUMN ad_revision_historique.exo_budget IS 'exercice comptable attache';
  COMMENT ON COLUMN ad_revision_historique.ref_budget IS 'reference du budget';
  COMMENT ON COLUMN ad_revision_historique.id_ligne_budget IS 'la ligne budgetaire concernee';
  COMMENT ON COLUMN ad_revision_historique.id_trimestre IS 'id du trimestre a modifie';
  COMMENT ON COLUMN ad_revision_historique.anc_montant IS 'ancien montant';
  COMMENT ON COLUMN ad_revision_historique.nouv_montant IS 'nouveau montant';
  COMMENT ON COLUMN ad_revision_historique.id_util_revise IS ' id utilisateur qui fait la revision';
  COMMENT ON COLUMN ad_revision_historique.id_util_valide IS ' id utilisateur qui valide la revision';
  COMMENT ON COLUMN ad_revision_historique.etat_revision IS ' etat de la revision';
  COMMENT ON COLUMN ad_revision_historique.date_creation IS ' date de creation';
  COMMENT ON COLUMN ad_revision_historique.date_modif IS ' date modification';
  COMMENT ON COLUMN ad_revision_historique.id_ag IS ' id de agence';
  		RAISE NOTICE 'Table ad_revision_historique created';


        -- Insertion dans tableliste
  IF NOT EXISTS(SELECT * FROM tableliste WHERE nomc = 'ad_revision_historique') THEN
  tableliste_str := makeTraductionLangSyst('Gestion des revisions historiques des lignes budgetaires');
  INSERT INTO tableliste VALUES ((select max(ident) from tableliste)+1, 'ad_revision_historique', tableliste_str , true);
  IF EXISTS(SELECT langue FROM adsys_langues_systeme WHERE code = 'en_GB') THEN
    INSERT INTO ad_traductions VALUES (tableliste_str,'en_GB', 'Management of budget lines revisions history');
    RAISE NOTICE 'Données table ad_revision_historique rajoutés dans table tableliste';
  END IF;
  END IF;

  tableliste_ident := (select ident from tableliste where nomc like 'ad_revision_historique' order by ident desc limit 1);

  IF NOT EXISTS(SELECT * FROM d_tableliste WHERE nchmpc = 'exo_budget' and tablen = tableliste_ident) THEN
    d_tableliste_str := makeTraductionLangSyst('Exercice Budget');
    INSERT INTO d_tableliste VALUES ((select max(ident) from d_tableliste)+1, tableliste_ident, 'exo_budget', d_tableliste_str, true, NULL, 'int', false, false, false);
    IF EXISTS(SELECT langue FROM adsys_langues_systeme WHERE code = 'en_GB') THEN
      INSERT INTO ad_traductions VALUES (d_tableliste_str,'en_GB','Revision budget exercice');
    END IF;
  END IF;

  IF NOT EXISTS(SELECT * FROM d_tableliste WHERE nchmpc = 'ref_budget' and tablen = tableliste_ident) THEN
    d_tableliste_str := makeTraductionLangSyst('Référence Budget');
    INSERT INTO d_tableliste VALUES ((select max(ident) from d_tableliste)+1, tableliste_ident, 'ref_budget', d_tableliste_str, true, NULL, 'txt', false, false, false);
    IF EXISTS(SELECT langue FROM adsys_langues_systeme WHERE code = 'en_GB') THEN
      INSERT INTO ad_traductions VALUES (d_tableliste_str,'en_GB','');
    END IF;
  END IF;

  IF NOT EXISTS(SELECT * FROM d_tableliste WHERE nchmpc = 'id_ligne_budget' and tablen = tableliste_ident) THEN
    d_tableliste_str := makeTraductionLangSyst('Ligne budgetaire');
    INSERT INTO d_tableliste VALUES ((select max(ident) from d_tableliste)+1, tableliste_ident, 'id_ligne_budget', d_tableliste_str, true, NULL, 'int', false, false, false);
    IF EXISTS(SELECT langue FROM adsys_langues_systeme WHERE code = 'en_GB') THEN
      INSERT INTO ad_traductions VALUES (d_tableliste_str,'en_GB','Budget line history');
    END IF;
  END IF;

  IF NOT EXISTS(SELECT * FROM d_tableliste WHERE nchmpc = 'id_trimestre' and tablen = tableliste_ident) THEN
    d_tableliste_str := makeTraductionLangSyst('Trimestre revisé');
    INSERT INTO d_tableliste VALUES ((select max(ident) from d_tableliste)+1, tableliste_ident, 'id_trimestre', d_tableliste_str, true, NULL, 'int', false, false, false);
    IF EXISTS(SELECT langue FROM adsys_langues_systeme WHERE code = 'en_GB') THEN
      INSERT INTO ad_traductions VALUES (d_tableliste_str,'en_GB','Quarter id');
    END IF;
  END IF;

  IF NOT EXISTS(SELECT * FROM d_tableliste WHERE nchmpc = 'anc_montant' and tablen = tableliste_ident) THEN
    d_tableliste_str := makeTraductionLangSyst('Ancien montant');
    INSERT INTO d_tableliste VALUES ((select max(ident) from d_tableliste)+1, tableliste_ident, 'anc_montant', d_tableliste_str, true, NULL, 'mnt', false, false, false);
    IF EXISTS(SELECT langue FROM adsys_langues_systeme WHERE code = 'en_GB') THEN
      INSERT INTO ad_traductions VALUES (d_tableliste_str,'en_GB','Previous amount');
    END IF;
  END IF;

  IF NOT EXISTS(SELECT * FROM d_tableliste WHERE nchmpc = 'nouv_montant' and tablen = tableliste_ident) THEN
    d_tableliste_str := makeTraductionLangSyst('Nouveau montant');
    INSERT INTO d_tableliste VALUES ((select max(ident) from d_tableliste)+1, tableliste_ident, 'nouv_montant', d_tableliste_str, true, NULL, 'mnt', false, false, false);
    IF EXISTS(SELECT langue FROM adsys_langues_systeme WHERE code = 'en_GB') THEN
      INSERT INTO ad_traductions VALUES (d_tableliste_str,'en_GB','Actual amount');
    END IF;
  END IF;

  IF NOT EXISTS(SELECT * FROM d_tableliste WHERE nchmpc = 'id_util_revise' and tablen = tableliste_ident) THEN
    d_tableliste_str := makeTraductionLangSyst('Utilisateur revisé');
    INSERT INTO d_tableliste VALUES ((select max(ident) from d_tableliste)+1, tableliste_ident, 'id_util_revise', d_tableliste_str, true, NULL, 'int', false, false, false);
    IF EXISTS(SELECT langue FROM adsys_langues_systeme WHERE code = 'en_GB') THEN
      INSERT INTO ad_traductions VALUES (d_tableliste_str,'en_GB','Revised user');
    END IF;
  END IF;

  IF NOT EXISTS(SELECT * FROM d_tableliste WHERE nchmpc = 'id_util_valide' and tablen = tableliste_ident) THEN
    d_tableliste_str := makeTraductionLangSyst('Utilisateur validé');
    INSERT INTO d_tableliste VALUES ((select max(ident) from d_tableliste)+1, tableliste_ident, 'id_util_valide', d_tableliste_str, true, NULL, 'int', false, false, false);
    IF EXISTS(SELECT langue FROM adsys_langues_systeme WHERE code = 'en_GB') THEN
      INSERT INTO ad_traductions VALUES (d_tableliste_str,'en_GB','Validated user');
    END IF;
  END IF;

  IF NOT EXISTS(SELECT * FROM d_tableliste WHERE nchmpc = 'etat_revision' and tablen = tableliste_ident) THEN
    d_tableliste_str := makeTraductionLangSyst('Etat révision');
    INSERT INTO d_tableliste VALUES ((select max(ident) from d_tableliste)+1, tableliste_ident, 'etat_revision', d_tableliste_str, true, NULL, 'int', false, false, false);
    IF EXISTS(SELECT langue FROM adsys_langues_systeme WHERE code = 'en_GB') THEN
      INSERT INTO ad_traductions VALUES (d_tableliste_str,'en_GB','Revision status');
    END IF;
  END IF;

  IF NOT EXISTS(SELECT * FROM d_tableliste WHERE nchmpc = 'date_creation' and tablen = tableliste_ident) THEN
    d_tableliste_str := makeTraductionLangSyst('Date création historique');
    INSERT INTO d_tableliste VALUES ((select max(ident) from d_tableliste)+1, tableliste_ident, 'date_creation', d_tableliste_str, true, NULL, 'dte', false, false, false);
    IF EXISTS(SELECT langue FROM adsys_langues_systeme WHERE code = 'en_GB') THEN
      INSERT INTO ad_traductions VALUES (d_tableliste_str,'en_GB','Creation date');
    END IF;
  END IF;

  IF NOT EXISTS(SELECT * FROM d_tableliste WHERE nchmpc = 'date_modif' and tablen = tableliste_ident) THEN
    d_tableliste_str := makeTraductionLangSyst('Date modification historique');
    INSERT INTO d_tableliste VALUES ((select max(ident) from d_tableliste)+1, tableliste_ident, 'date_modif', d_tableliste_str, true, NULL, 'dte', false, false, false);
    IF EXISTS(SELECT langue FROM adsys_langues_systeme WHERE code = 'en_GB') THEN
      INSERT INTO ad_traductions VALUES (d_tableliste_str,'en_GB','Modification date');
    END IF;
  END IF;

END IF;



-- Creation table ad_budget_cpt_bloquer + d_tableliste
IF NOT EXISTS(SELECT * FROM information_schema.tables WHERE table_name = 'ad_budget_cpt_bloquer') THEN

   CREATE TABLE ad_budget_cpt_bloquer
(
  id_bloc serial NOT NULL,
  ligne_budgetaire integer,
  cpte_comptable text,
  cpte_bloquer boolean DEFAULT false,
  date_creation timestamp without time zone,
  date_modif timestamp without time zone,
  id_ag integer,
  CONSTRAINT ad_budget_cpt_bloquer_pkey PRIMARY KEY (id_bloc, id_ag)
)
WITH (
  OIDS=FALSE
);
  ALTER TABLE ad_budget_cpt_bloquer
    OWNER TO postgres;
  COMMENT ON TABLE ad_budget_cpt_bloquer
    IS ' la table du budget';
  COMMENT ON COLUMN ad_budget_cpt_bloquer.id_bloc IS 'id du budget bloquer';
  COMMENT ON COLUMN ad_budget_cpt_bloquer.ligne_budgetaire IS 'Ligne budgetaire bloquer';
  COMMENT ON COLUMN ad_budget_cpt_bloquer.cpte_comptable IS 'compte comptable';
  COMMENT ON COLUMN ad_budget_cpt_bloquer.cpte_bloquer IS ' compte bloquer : true or false';
  COMMENT ON COLUMN ad_budget_cpt_bloquer.date_creation IS ' date creation';
  COMMENT ON COLUMN ad_budget_cpt_bloquer.date_modif IS ' date modif';
  COMMENT ON COLUMN ad_budget_cpt_bloquer.id_ag IS ' id de agence';
  		RAISE NOTICE 'Table ad_budget_cpt_bloquer created';

  		        -- Insertion dans tableliste
  IF NOT EXISTS(SELECT * FROM tableliste WHERE nomc = 'ad_budget_cpt_bloquer') THEN
  tableliste_str := makeTraductionLangSyst('Gestion des comptes bloqués');
  INSERT INTO tableliste VALUES ((select max(ident) from tableliste)+1, 'ad_budget_cpt_bloquer', tableliste_str , true);
  IF EXISTS(SELECT langue FROM adsys_langues_systeme WHERE code = 'en_GB') THEN
    INSERT INTO ad_traductions VALUES (tableliste_str,'en_GB', 'Management of blocked accounts');
    RAISE NOTICE 'Données table ad_budget_cpt_bloquer rajoutés dans table tableliste';
  END IF;
  END IF;

  tableliste_ident := (select ident from tableliste where nomc like 'ad_budget_cpt_bloquer' order by ident desc limit 1);

  IF NOT EXISTS(SELECT * FROM d_tableliste WHERE nchmpc = 'ligne_budgetaire' and tablen = tableliste_ident) THEN
    d_tableliste_str := makeTraductionLangSyst('Ligne budgetaire bloquer');
    INSERT INTO d_tableliste VALUES ((select max(ident) from d_tableliste)+1, tableliste_ident, 'ligne_budgetaire', d_tableliste_str, true, NULL, 'int', false, false, false);
    IF EXISTS(SELECT langue FROM adsys_langues_systeme WHERE code = 'en_GB') THEN
      INSERT INTO ad_traductions VALUES (d_tableliste_str,'en_GB','Budget line blocked');
    END IF;
  END IF;

  IF NOT EXISTS(SELECT * FROM d_tableliste WHERE nchmpc = 'cpte_comptable' and tablen = tableliste_ident) THEN
  d_tableliste_str := makeTraductionLangSyst('Compte comptable bloquer');
  INSERT INTO d_tableliste VALUES ((select max(ident) from d_tableliste)+1, tableliste_ident, 'cpte_comptable', d_tableliste_str, true, NULL, 'txt', false, false, false);
  IF EXISTS(SELECT langue FROM adsys_langues_systeme WHERE code = 'en_GB') THEN
    INSERT INTO ad_traductions VALUES (d_tableliste_str,'en_GB','Account');
  END IF;
  END IF;

   IF NOT EXISTS(SELECT * FROM d_tableliste WHERE nchmpc = 'cpte_bloquer' and tablen = tableliste_ident) THEN
  d_tableliste_str := makeTraductionLangSyst('Compte bloqué ?');
  INSERT INTO d_tableliste VALUES ((select max(ident) from d_tableliste)+1, tableliste_ident, 'cpte_bloquer', d_tableliste_str, true, NULL, 'bol', false, false, false);
  IF EXISTS(SELECT langue FROM adsys_langues_systeme WHERE code = 'en_GB') THEN
    INSERT INTO ad_traductions VALUES (d_tableliste_str,'en_GB','Account blocked ?');
  END IF;
  END IF;

  IF NOT EXISTS(SELECT * FROM d_tableliste WHERE nchmpc = 'date_creation' and tablen = tableliste_ident) THEN
    d_tableliste_str := makeTraductionLangSyst('Date création compte bloquer');
    INSERT INTO d_tableliste VALUES ((select max(ident) from d_tableliste)+1, tableliste_ident, 'date_creation', d_tableliste_str, true, NULL, 'dte', false, false, false);
    IF EXISTS(SELECT langue FROM adsys_langues_systeme WHERE code = 'en_GB') THEN
      INSERT INTO ad_traductions VALUES (d_tableliste_str,'en_GB','Creation date');
    END IF;
  END IF;

  IF NOT EXISTS(SELECT * FROM d_tableliste WHERE nchmpc = 'date_modif' and tablen = tableliste_ident) THEN
    d_tableliste_str := makeTraductionLangSyst('Date Modification compte bloquer');
    INSERT INTO d_tableliste VALUES ((select max(ident) from d_tableliste)+1, tableliste_ident, 'date_modif', d_tableliste_str, true, NULL, 'dte', false, false, false);
    IF EXISTS(SELECT langue FROM adsys_langues_systeme WHERE code = 'en_GB') THEN
      INSERT INTO ad_traductions VALUES (d_tableliste_str,'en_GB','Modification date');
    END IF;
  END IF;
END IF;


IF NOT EXISTS(SELECT * FROM information_schema.tables WHERE table_name = 'ad_budget_cpte_comptable') THEN

   CREATE TABLE ad_budget_cpte_comptable
(
  id serial NOT NULL,
  id_ligne integer,
  cpte_comptable text,
  etat_compte boolean DEFAULT true,
  date_creation timestamp without time zone,
  date_modif timestamp without time zone,
  id_ag integer,
  CONSTRAINT ad_budget_cpte_comptable_pkey PRIMARY KEY (id, id_ag)
)
WITH (
  OIDS=FALSE
);
  ALTER TABLE ad_budget_cpte_comptable
    OWNER TO postgres;
  COMMENT ON TABLE ad_budget_cpte_comptable
    IS ' la table du budget';
  COMMENT ON COLUMN ad_budget_cpte_comptable.id IS 'id ';
  COMMENT ON COLUMN ad_budget_cpte_comptable.id_ligne IS 'Ligne budgetaire id ';
  COMMENT ON COLUMN ad_budget_cpte_comptable.cpte_comptable IS 'compte comptable';
  COMMENT ON COLUMN ad_budget_cpte_comptable.etat_compte IS 'etat compte';
  COMMENT ON COLUMN ad_budget_cpte_comptable.date_creation IS ' date creation';
  COMMENT ON COLUMN ad_budget_cpte_comptable.date_modif IS ' date modif';
  COMMENT ON COLUMN ad_budget_cpte_comptable.id_ag IS ' id de agence';
  		RAISE NOTICE 'Table ad_budget_cpte_comptable created';

		        -- Insertion dans tableliste
  IF NOT EXISTS(SELECT * FROM tableliste WHERE nomc = 'ad_budget_cpte_comptable') THEN
  tableliste_str := makeTraductionLangSyst('Compte associé');
  INSERT INTO tableliste VALUES ((select max(ident) from tableliste)+1, 'ad_budget_cpte_comptable', tableliste_str , true);
  IF EXISTS(SELECT langue FROM adsys_langues_systeme WHERE code = 'en_GB') THEN
    INSERT INTO ad_traductions VALUES (tableliste_str,'en_GB', 'Associated account');
    RAISE NOTICE 'Données table ad_budget_cpte_comptable rajoutés dans table tableliste';
  END IF;
  END IF;

  tableliste_ident := (select ident from tableliste where nomc like 'ad_budget_cpte_comptable' order by ident desc limit 1);

  IF NOT EXISTS(SELECT * FROM d_tableliste WHERE nchmpc = 'id_ligne' and tablen = tableliste_ident) THEN
    d_tableliste_str := makeTraductionLangSyst('Ligne budgetaire associé');
    INSERT INTO d_tableliste VALUES ((select max(ident) from d_tableliste)+1, tableliste_ident, 'id_ligne', d_tableliste_str, true, NULL, 'int', false, false, false);
    IF EXISTS(SELECT langue FROM adsys_langues_systeme WHERE code = 'en_GB') THEN
      INSERT INTO ad_traductions VALUES (d_tableliste_str,'en_GB','Budget line associated');
    END IF;
  END IF;

  IF NOT EXISTS(SELECT * FROM d_tableliste WHERE nchmpc = 'cpte_comptable' and tablen = tableliste_ident) THEN
    d_tableliste_str := makeTraductionLangSyst('Compte comptable associé au budget');
    INSERT INTO d_tableliste VALUES ((select max(ident) from d_tableliste)+1, tableliste_ident, 'cpte_comptable', d_tableliste_str, true, NULL, 'txt', false, false, false);
    IF EXISTS(SELECT langue FROM adsys_langues_systeme WHERE code = 'en_GB') THEN
      INSERT INTO ad_traductions VALUES (d_tableliste_str,'en_GB','Accounting account budget associated');
    END IF;
  END IF;
    IF NOT EXISTS(SELECT * FROM d_tableliste WHERE nchmpc = 'etat_compte' and tablen = tableliste_ident) THEN
    d_tableliste_str := makeTraductionLangSyst('Etat Compte');
    INSERT INTO d_tableliste VALUES ((select max(ident) from d_tableliste)+1, tableliste_ident, 'etat_compte', d_tableliste_str, true, NULL, 'bol', false, false, false);
    IF EXISTS(SELECT langue FROM adsys_langues_systeme WHERE code = 'en_GB') THEN
      INSERT INTO ad_traductions VALUES (d_tableliste_str,'en_GB','Account budget status');
    END IF;
  END IF;

  IF NOT EXISTS(SELECT * FROM d_tableliste WHERE nchmpc = 'date_creation' and tablen = tableliste_ident) THEN
    d_tableliste_str := makeTraductionLangSyst('Date création compte associé');
    INSERT INTO d_tableliste VALUES ((select max(ident) from d_tableliste)+1, tableliste_ident, 'date_creation', d_tableliste_str, true, NULL, 'dte', false, false, false);
    IF EXISTS(SELECT langue FROM adsys_langues_systeme WHERE code = 'en_GB') THEN
      INSERT INTO ad_traductions VALUES (d_tableliste_str,'en_GB','creation date');
    END IF;
  END IF;

  IF NOT EXISTS(SELECT * FROM d_tableliste WHERE nchmpc = 'date_modif' and tablen = tableliste_ident) THEN
    d_tableliste_str := makeTraductionLangSyst('Date Modification compte associé');
    INSERT INTO d_tableliste VALUES ((select max(ident) from d_tableliste)+1, tableliste_ident, 'date_modif', d_tableliste_str, true, NULL, 'dte', false, false, false);
    IF EXISTS(SELECT langue FROM adsys_langues_systeme WHERE code = 'en_GB') THEN
      INSERT INTO ad_traductions VALUES (d_tableliste_str,'en_GB','Modification date');
    END IF;
  END IF;
END IF;

RETURN output_result;
END;
$BODY$
  LANGUAGE plpgsql VOLATILE
  COST 100;
ALTER FUNCTION module_budget()
  OWNER TO postgres;


select module_budget();

DROP FUNCTION IF EXISTS module_budget();


