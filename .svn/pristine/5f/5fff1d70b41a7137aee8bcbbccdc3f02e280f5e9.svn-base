  CREATE OR REPLACE FUNCTION engrais_chimiques_tables_operations_function() RETURNS INT AS
$$
DECLARE
output_result INTEGER = 1;
tableliste_ident INTEGER = 0;

BEGIN
-- Creation table ec_beneficiaire + d_tableliste
 IF NOT EXISTS(SELECT * FROM information_schema.tables WHERE table_name = 'ec_beneficiaire') THEN

   CREATE TABLE ec_beneficiaire
(
  id_beneficiaire serial NOT NULL, -- id du beneficiaire
  nom_prenom text, -- nom du beneficiaire
  nic text, -- id de la piece identite du beneficiaire
  id_province integer, -- reference a la table ec_localisation
  id_zone integer, -- reference a la table ec_localisation
  id_commune integer, -- reference a la table ec_localisation
  id_colline integer, -- reference a la table ec_localisation
  id_ag integer, -- id de l'agence
  CONSTRAINT ec_beneficiaire_pkey PRIMARY KEY (id_beneficiaire, id_ag)
)
WITH (
  OIDS=FALSE
);
  ALTER TABLE ec_beneficiaire
    OWNER TO postgres;
  COMMENT ON TABLE ec_beneficiaire
    IS ' reference tous les beneficiaire de la PNSEB';
  COMMENT ON COLUMN ec_beneficiaire.id_beneficiaire IS 'id du beneficiaire';
  COMMENT ON COLUMN ec_beneficiaire.nom_prenom IS 'nom du beneficiaire';
  COMMENT ON COLUMN ec_beneficiaire.nic IS 'id de la piece identite du beneficiaire';
  COMMENT ON COLUMN ec_beneficiaire.id_province IS 'reference a la table ec_localisation';
  COMMENT ON COLUMN ec_beneficiaire.id_zone IS 'reference a la table ec_localisation';
  COMMENT ON COLUMN ec_beneficiaire.id_commune IS 'reference a la table ec_localisation ';
  COMMENT ON COLUMN ec_beneficiaire.id_colline IS 'reference a la table ec_localisation';
  COMMENT ON COLUMN ec_beneficiaire.id_ag IS ' id de agence';
  		RAISE NOTICE 'Table ec_beneficiaire created';


  -- Insertion dans tableliste
  IF NOT EXISTS(SELECT * FROM tableliste WHERE nomc = 'ec_beneficiaire') THEN
    INSERT INTO tableliste VALUES ((select max(ident) from tableliste)+1, 'ec_beneficiaire', makeTraductionLangSyst('Parametrage des beneficiaires'), true);
    RAISE NOTICE 'Données table ec_beneficiaire rajoutés dans table tableliste';
  END IF;

  -- Insertions champs dans d_tableliste

  -- Renseigne l'identifiant pour insertion dans d_tableliste
  tableliste_ident := (select ident from tableliste where nomc like 'ec_beneficiaire' order by ident desc limit 1);

  -- Insertion dans d_tableliste champ ec_beneficiaire.id_ag
  IF NOT EXISTS(SELECT * FROM d_tableliste WHERE nchmpc = 'id_ag' and tablen = tableliste_ident) THEN
      INSERT INTO d_tableliste VALUES ((select max(ident) from d_tableliste)+1, tableliste_ident, 'id_ag', makeTraductionLangSyst('ID agence'), true, NULL, 'int', NULL, true, false);
  END IF;
		output_result := 2;
	END IF;

-- creation table ec_produit + tableliste
  IF NOT EXISTS(SELECT * FROM information_schema.tables WHERE table_name = 'ec_produit') THEN

	CREATE TABLE ec_produit
	(
	id_produit serial NOT NULL, -- id du produit
	libel text, -- libelle du produit
	type_produit text, -- type : engrais ou amendement
	prix_unitaire numeric(30,6), -- prix unitiaire du produit
	etat_produit integer, -- etat du produit
	montant_minimum numeric(30,6), -- montant minimum a deposer par unite de ce produit
	compte_produit text, -- le compte produit associer
	id_ag integer, -- id de l'agence
	CONSTRAINT ec_produit_pkey PRIMARY KEY (id_produit, id_ag)
	)
WITH (
  OIDS=FALSE
);

	ALTER TABLE ec_produit
	OWNER TO postgres;
	COMMENT ON TABLE ec_produit
	IS ' reference tous les produits de la PNSEB';
	COMMENT ON COLUMN ec_produit.id_produit IS 'id du produit';
	COMMENT ON COLUMN ec_produit.libel IS 'libelle du produit';
	COMMENT ON COLUMN ec_produit.type_produit IS 'type : engrais ou amendement';
	COMMENT ON COLUMN ec_produit.prix_unitaire IS 'prix unitiaire du produit';
	COMMENT ON COLUMN ec_produit.etat_produit IS 'etat du produit';
	COMMENT ON COLUMN ec_produit.montant_minimum IS 'montant minimum a deposer par unite de ce produit ';
	COMMENT ON COLUMN ec_produit.compte_produit IS 'le compte produit associer';
	COMMENT ON COLUMN ec_produit.id_ag IS ' id de agence';
		RAISE NOTICE 'Table ec_produit created';


	  -- Insertion dans tableliste
	IF NOT EXISTS(SELECT * FROM tableliste WHERE nomc = 'ec_produit') THEN
	INSERT INTO tableliste VALUES ((select max(ident) from tableliste)+1, 'ec_produit', makeTraductionLangSyst('"Paramétrage des produits PNSEB"'), true);
	RAISE NOTICE 'Données table ec_produit rajoutés dans table tableliste';
	END IF;

	tableliste_ident := (select ident from tableliste where nomc like 'ec_produit' order by ident desc limit 1);

	  -- Insertion dans d_tableliste champ ec_produit."libel"
	IF NOT EXISTS(SELECT * FROM d_tableliste WHERE nchmpc = 'libel' and tablen = tableliste_ident) THEN
	  INSERT INTO d_tableliste VALUES ((select max(ident) from d_tableliste)+1, tableliste_ident, 'libel', makeTraductionLangSyst('Libellé produit'), true, NULL, 'txt', false, false, false);
	END IF;

	IF NOT EXISTS(SELECT * FROM d_tableliste WHERE nchmpc = 'type_produit' and tablen = tableliste_ident) THEN
	  INSERT INTO d_tableliste VALUES ((select max(ident) from d_tableliste)+1, tableliste_ident, 'type_produit', makeTraductionLangSyst('Type produit'), false, NULL, 'int', false, false, false);
	END IF;

	IF NOT EXISTS(SELECT * FROM d_tableliste WHERE nchmpc = 'prix_unitaire' and tablen = tableliste_ident) THEN
	  INSERT INTO d_tableliste VALUES ((select max(ident) from d_tableliste)+1, tableliste_ident, 'prix_unitaire', makeTraductionLangSyst('Prix unitaire produit'), false, NULL, 'mnt', false, false, false);
	END IF;

	IF NOT EXISTS(SELECT * FROM d_tableliste WHERE nchmpc = 'etat_produit' and tablen = tableliste_ident) THEN
	 INSERT INTO d_tableliste VALUES ((select max(ident) from d_tableliste)+1, tableliste_ident, 'etat_produit', makeTraductionLangSyst('Etat produit'), false, NULL, 'int', false, false, false);
	END IF;

	IF NOT EXISTS(SELECT * FROM d_tableliste WHERE nchmpc = 'compte_produit' and tablen = tableliste_ident) THEN
	  INSERT INTO d_tableliste VALUES ((select max(ident) from d_tableliste)+1, tableliste_ident, 'compte_produit', makeTraductionLangSyst('Compte produit PNSEB'), true, 1400, 'txt', false, false, false);
	END IF;

	IF NOT EXISTS(SELECT * FROM d_tableliste WHERE nchmpc = 'montant_minimum' and tablen = tableliste_ident) THEN
	  INSERT INTO d_tableliste VALUES ((select max(ident) from d_tableliste)+1, tableliste_ident, 'montant_minimum', makeTraductionLangSyst('Montant minimum de dépôt'), true, NULL, 'mnt', false, false, false);
	END IF;

	IF NOT EXISTS(SELECT * FROM d_tableliste WHERE nchmpc = 'id_produit' and tablen = tableliste_ident) THEN
	  INSERT INTO d_tableliste VALUES ((select max(ident) from d_tableliste)+1, tableliste_ident, 'id_produit', makeTraductionLangSyst('Montant minimum de dépôt'), false, NULL, 'int', null, true, false);
	END IF;

	END IF;

-- creation table ec_annee_agricole + tableliste
  IF NOT EXISTS(SELECT * FROM information_schema.tables WHERE table_name = 'ec_annee_agricole') THEN
	CREATE TABLE ec_annee_agricole
	(
	id_annee serial NOT NULL, -- id annee_agricole
	libel text, -- nom de l'annee
	date_debut timestamp without time zone, -- date de but de l'annee agricole
	date_fin timestamp without time zone, -- date fin de l'annee agricole
	etat integer, -- etat de l'annee = ouvert/fermer
	id_ag integer NOT NULL, -- id de agence
	CONSTRAINT ec_annee_agricole_pkey PRIMARY KEY (id_annee, id_ag)
	)
	WITH (
	OIDS=FALSE
	);
	ALTER TABLE ec_annee_agricole
	OWNER TO postgres;
	COMMENT ON TABLE ec_annee_agricole
	IS ' reference a lannee agricole en cours ou fermer';
	COMMENT ON COLUMN ec_annee_agricole.id_annee IS 'id annee_agricole';
	COMMENT ON COLUMN ec_annee_agricole.libel IS 'nom de l''annee';
	COMMENT ON COLUMN ec_annee_agricole.date_debut IS ' date de but de l''annee agricole';
	COMMENT ON COLUMN ec_annee_agricole.date_fin IS 'date fin de l''annee agricole';
	COMMENT ON COLUMN ec_annee_agricole.etat IS ' etat de l''annee = ouvert/fermer';
	COMMENT ON COLUMN ec_annee_agricole.id_ag IS 'id de agence';

	-- Insertion dans tableliste
	IF NOT EXISTS(SELECT * FROM tableliste WHERE nomc = 'ec_annee_agricole') THEN
	INSERT INTO tableliste VALUES ((select max(ident) from tableliste)+1, 'ec_annee_agricole', makeTraductionLangSyst('"Paramétrage des années agricole PNSEB"'), true);
	RAISE NOTICE 'Données table ec_annee_agricole rajoutés dans table tableliste';
	END IF;

	tableliste_ident := (select ident from tableliste where nomc like 'ec_annee_agricole' order by ident desc limit 1);

	IF NOT EXISTS(SELECT * FROM d_tableliste WHERE nchmpc = 'libel' and tablen = tableliste_ident) THEN
	  INSERT INTO d_tableliste VALUES ((select max(ident) from d_tableliste)+1, tableliste_ident, 'libel', makeTraductionLangSyst('Libellé année agricole'), true, NULL, 'txt', false, false, false);
	END IF;

	IF NOT EXISTS(SELECT * FROM d_tableliste WHERE nchmpc = 'date_debut' and tablen = tableliste_ident) THEN
	  INSERT INTO d_tableliste VALUES ((select max(ident) from d_tableliste)+1, tableliste_ident, 'date_debut', makeTraductionLangSyst('Date début'), true, NULL, 'dtg', false, false, false);
	END IF;

	IF NOT EXISTS(SELECT * FROM d_tableliste WHERE nchmpc = 'date_fin' and tablen = tableliste_ident) THEN
	  INSERT INTO d_tableliste VALUES ((select max(ident) from d_tableliste)+1, tableliste_ident, 'date_fin', makeTraductionLangSyst('Date fin'), true, NULL, 'dtg', false, false, false);
	END IF;

	IF NOT EXISTS(SELECT * FROM d_tableliste WHERE nchmpc = 'etat' and tablen = tableliste_ident) THEN
	  INSERT INTO d_tableliste VALUES ((select max(ident) from d_tableliste)+1, tableliste_ident, 'etat', makeTraductionLangSyst('Etat en cours'), true, NULL, 'int', false, false, false);
	END IF;

	IF NOT EXISTS(SELECT * FROM d_tableliste WHERE nchmpc = 'id_annee' and tablen = tableliste_ident) THEN
	  INSERT INTO d_tableliste VALUES ((select max(ident) from d_tableliste)+1, tableliste_ident, 'id_annee', makeTraductionLangSyst('Id Année'), true, NULL, 'int', null, true, false);
	END IF;

  END IF;


  IF NOT EXISTS(SELECT * FROM information_schema.tables WHERE table_name = 'ec_saison_culturale') THEN

	CREATE TABLE ec_saison_culturale
	(
	id_saison serial NOT NULL, -- id de la saison
	id_annee integer, -- id de l annee agricole
	nom_saison text, -- nom de la saison
	date_debut timestamp without time zone, -- date de debut saison
	date_fin_avance timestamp without time zone, -- date fin des avances
	date_debut_solde timestamp without time zone, -- date debut des soldes
	date_fin_solde timestamp without time zone, -- date fin des soldes
	date_fin timestamp without time zone, --  date fin de la saison
	plafond_engrais integer, -- plafond limite pour une type de categorie : engrais
	plafond_amendement integer, -- plafond limite pour une type de categorie : amendement
	etat_saison integer, -- etat de saison : ouvert - fermer
	id_ag integer, -- id de l'agence
	CONSTRAINT ec_saison__culturale_pkey PRIMARY KEY (id_saison, id_ag),
	CONSTRAINT ec_saison_culturale_fkey FOREIGN KEY (id_annee, id_ag)
    REFERENCES ec_annee_agricole (id_annee, id_ag) MATCH SIMPLE
    ON UPDATE NO ACTION ON DELETE NO ACTION
	)
	WITH (
	OIDS=FALSE
	);
	ALTER TABLE ec_saison_culturale
	OWNER TO postgres;
	COMMENT ON TABLE ec_saison_culturale
	IS ' reference au saison en cours ou fermer';
	COMMENT ON COLUMN ec_saison_culturale.id_saison IS 'id de la saison';
	COMMENT ON COLUMN ec_saison_culturale.id_annee IS 'id de l annee agricole';
	COMMENT ON COLUMN ec_saison_culturale.nom_saison IS 'nom de la saison';
	COMMENT ON COLUMN ec_saison_culturale.date_debut IS 'date de debut saison';
	COMMENT ON COLUMN ec_saison_culturale.date_fin_avance IS 'date fin des avances';
	COMMENT ON COLUMN ec_saison_culturale.date_debut_solde IS 'date debut des soldes';
	COMMENT ON COLUMN ec_saison_culturale.date_fin_solde IS 'date fin des soldes';
	COMMENT ON COLUMN ec_saison_culturale.date_fin IS 'date fin de la saison';
	COMMENT ON COLUMN ec_saison_culturale.plafond_engrais IS ' plafond limite pour une type de categorie : engrais';
	COMMENT ON COLUMN ec_saison_culturale.plafond_amendement IS '  plafond limite pour une type de categorie : amendement';
	COMMENT ON COLUMN ec_saison_culturale.etat_saison IS ' etat de saison : ouvert - fermer';
	COMMENT ON COLUMN ec_saison_culturale.id_ag IS ' id de agence';
		RAISE NOTICE 'Table ec_saison_culturale created';

		  -- Insertion dans tableliste
	IF NOT EXISTS(SELECT * FROM tableliste WHERE nomc = 'ec_saison_culturale') THEN
	INSERT INTO tableliste VALUES ((select max(ident) from tableliste)+1, 'ec_saison_culturale', makeTraductionLangSyst('"Paramétrage des saisons PNSEB"'), true);
	RAISE NOTICE 'Données table ec_saison_culturale rajoutés dans table tableliste';
	END IF;

	tableliste_ident := (select ident from tableliste where nomc like 'ec_saison_culturale' order by ident desc limit 1);

	IF NOT EXISTS(SELECT * FROM d_tableliste WHERE nchmpc = 'nom_saison' and tablen = tableliste_ident) THEN
	  INSERT INTO d_tableliste VALUES ((select max(ident) from d_tableliste)+1, tableliste_ident, 'nom_saison', makeTraductionLangSyst('Nom saison'), true, NULL, 'txt', false, false, false);
	END IF;

	IF NOT EXISTS(SELECT * FROM d_tableliste WHERE nchmpc = 'date_debut' and tablen = tableliste_ident) THEN
	  INSERT INTO d_tableliste VALUES ((select max(ident) from d_tableliste)+1, tableliste_ident, 'date_debut', makeTraductionLangSyst('Date début de la saison'), false, NULL, 'dtg', false, false, false);
	END IF;

	IF NOT EXISTS(SELECT * FROM d_tableliste WHERE nchmpc = 'date_fin_avance' and tablen = tableliste_ident) THEN
	  INSERT INTO d_tableliste VALUES ((select max(ident) from d_tableliste)+1, tableliste_ident, 'date_fin_avance', makeTraductionLangSyst('Date fin des avances'), false, NULL, 'dtg', false, false, false);
	END IF;

	IF NOT EXISTS(SELECT * FROM d_tableliste WHERE nchmpc = 'date_debut_solde' and tablen = tableliste_ident) THEN
	  INSERT INTO d_tableliste VALUES ((select max(ident) from d_tableliste)+1, tableliste_ident, 'date_debut_solde', makeTraductionLangSyst('Date début des soldes'), false, NULL, 'dtg', false, false, false);
	END IF;

	IF NOT EXISTS(SELECT * FROM d_tableliste WHERE nchmpc = 'date_fin_solde' and tablen = tableliste_ident) THEN
	  INSERT INTO d_tableliste VALUES ((select max(ident) from d_tableliste)+1, tableliste_ident, 'date_fin_solde', makeTraductionLangSyst('Date fin des soldes'), false, NULL, 'dtg', false, false, false);
	END IF;

	IF NOT EXISTS(SELECT * FROM d_tableliste WHERE nchmpc = 'date_fin' and tablen = tableliste_ident) THEN
	  INSERT INTO d_tableliste VALUES ((select max(ident) from d_tableliste)+1, tableliste_ident, 'date_fin', makeTraductionLangSyst('Date fin de la saison'), false, NULL, 'dtg', false, false, false);
	END IF;

	IF NOT EXISTS(SELECT * FROM d_tableliste WHERE nchmpc = 'plafond_engrais' and tablen = tableliste_ident) THEN
	  INSERT INTO d_tableliste VALUES ((select max(ident) from d_tableliste)+1, tableliste_ident, 'plafond_engrais', makeTraductionLangSyst('Plafond du type Engrais'), true, NULL, 'int', false, false, false);
	END IF;

	IF NOT EXISTS(SELECT * FROM d_tableliste WHERE nchmpc = 'plafond_amendement' and tablen = tableliste_ident) THEN
	  INSERT INTO d_tableliste VALUES ((select max(ident) from d_tableliste)+1, tableliste_ident, 'plafond_amendement', makeTraductionLangSyst('Plafond du type Amendement'), true, NULL, 'int', false, false, false);
	END IF;

	IF NOT EXISTS(SELECT * FROM d_tableliste WHERE nchmpc = 'etat_saison' and tablen = tableliste_ident) THEN
	  INSERT INTO d_tableliste VALUES ((select max(ident) from d_tableliste)+1, tableliste_ident, 'etat_saison', makeTraductionLangSyst('Etat de la saison'), true, NULL, 'int', false, false, false);
	END IF;

	IF NOT EXISTS(SELECT * FROM d_tableliste WHERE nchmpc = 'id_saison' and tablen = tableliste_ident) THEN
	  INSERT INTO d_tableliste VALUES ((select max(ident) from d_tableliste)+1, tableliste_ident, 'id_saison', makeTraductionLangSyst('Id saison'), true, NULL, 'int', false, true, false);
	END IF;

	IF NOT EXISTS(SELECT * FROM d_tableliste WHERE nchmpc = 'id_annee' and tablen = tableliste_ident) THEN
	  INSERT INTO d_tableliste VALUES ((select max(ident) from d_tableliste)+1, tableliste_ident, 'id_annee', makeTraductionLangSyst('Nom année agricole'), true, NULL, 'int', false, false, false);
	END IF;
		output_result := 2;
	END IF;

	-- creation table ec_localisation + tableliste
  IF NOT EXISTS(SELECT * FROM information_schema.tables WHERE table_name = 'ec_localisation') THEN

   CREATE TABLE ec_localisation
(
	id serial NOT NULL, -- id localisation
	libel text, -- nom de la localisation
	type_localisation integer, -- type localisation : commune , zone, colline
	parent integer, -- dependance par rapport a une entite plus grande
	id_ag integer, -- id de l'agence
	CONSTRAINT ec_localisation_pkey PRIMARY KEY (id, id_ag)
)
WITH (
  OIDS=FALSE
);
	ALTER TABLE ec_localisation
	OWNER TO postgres;
	COMMENT ON TABLE ec_localisation
	IS ' reference au saison en cours ou fermer';
	COMMENT ON COLUMN ec_localisation.id IS 'id localisation';
	COMMENT ON COLUMN ec_localisation.libel IS 'nom de la localisation';
	COMMENT ON COLUMN ec_localisation.type_localisation IS 'type localisation : commune , zone, colline';
	COMMENT ON COLUMN ec_localisation.parent IS 'dependance par rapport a une entite plus grande';
	COMMENT ON COLUMN ec_localisation.id_ag IS 'id de agence';
		RAISE NOTICE 'Table ec_localisation created';

			  -- Insertion dans tableliste
	IF NOT EXISTS(SELECT * FROM tableliste WHERE nomc = 'ec_localisation') THEN
	INSERT INTO tableliste VALUES ((select max(ident) from tableliste)+1, 'ec_localisation', makeTraductionLangSyst('"Paramétrage des localisations PNSEB"'), true);
	RAISE NOTICE 'Données table ec_localisation rajoutés dans table tableliste';
	END IF;

	tableliste_ident := (select ident from tableliste where nomc like 'ec_localisation' order by ident desc limit 1);

	IF NOT EXISTS(SELECT * FROM d_tableliste WHERE nchmpc = 'libel' and tablen = tableliste_ident) THEN
	  INSERT INTO d_tableliste VALUES ((select max(ident) from d_tableliste)+1, tableliste_ident, 'libel', makeTraductionLangSyst('Libel localisation'), false, NULL, 'txt', false, false, false);
	END IF;

	IF NOT EXISTS(SELECT * FROM d_tableliste WHERE nchmpc = 'type_localisation' and tablen = tableliste_ident) THEN
	  INSERT INTO d_tableliste VALUES ((select max(ident) from d_tableliste)+1, tableliste_ident, 'type_localisation', makeTraductionLangSyst('Type localisation'), false, NULL, 'int', false, false, false);
	END IF;

	IF NOT EXISTS(SELECT * FROM d_tableliste WHERE nchmpc = 'parent' and tablen = tableliste_ident) THEN
	  INSERT INTO d_tableliste VALUES ((select max(ident) from d_tableliste)+1, tableliste_ident, 'parent', makeTraductionLangSyst('Parent'), false, NULL, 'int', false, false, false);
	END IF;

	IF NOT EXISTS(SELECT * FROM d_tableliste WHERE nchmpc = 'id' and tablen = tableliste_ident) THEN
	  INSERT INTO d_tableliste VALUES ((select max(ident) from d_tableliste)+1, tableliste_ident, 'id', makeTraductionLangSyst('Id localisation'), false, NULL, 'int', false, true, false);
	END IF;
		output_result := 2;
	END IF;


  IF NOT EXISTS(SELECT * FROM information_schema.tables WHERE table_name = 'ec_commande') THEN

   CREATE TABLE ec_commande
(
  id_commande serial NOT NULL, -- id de la commande
  id_benef integer, -- reference table ec_beneficiaire
  id_saison integer, -- reference table ec_saison
  montant_total numeric(30,6), -- total montant de la commande
  montant_depose numeric(30,6), -- montant depose lors de la commande
  etat_commande integer , -- etat de la commande : enregistre, en cours, solde, non-solde, annule
  date_creation timestamp without time zone, --  date creation commande
  date_modif timestamp without time zone, -- date modif
  id_ag integer, -- id de l'agence
  CONSTRAINT ec_commande_pkey PRIMARY KEY (id_commande, id_ag)
)
WITH (
  OIDS=FALSE
);
  ALTER TABLE ec_commande
    OWNER TO postgres;
  COMMENT ON TABLE ec_commande
    IS ' la table des enregistrements des commandes ';
  COMMENT ON COLUMN ec_commande.id_commande IS 'id de la commande';
  COMMENT ON COLUMN ec_commande.id_benef IS 'reference table ec_beneficiaire';
  COMMENT ON COLUMN ec_commande.id_saison IS 'reference table ec_saison';
  COMMENT ON COLUMN ec_commande.montant_total IS 'total montant de la commande';
  COMMENT ON COLUMN ec_commande.montant_depose IS 'montant depose lors de la commande';
  COMMENT ON COLUMN ec_commande.etat_commande IS 'etat de la commande : enregistre, en cours, solde, non-solde, annule';
  COMMENT ON COLUMN ec_commande.date_creation IS 'date creation commande';
  COMMENT ON COLUMN ec_commande.date_modif IS 'date modif';
  COMMENT ON COLUMN ec_commande.id_ag IS ' id de agence';
  		RAISE NOTICE 'Table ec_commande created';
		output_result := 2;
	END IF;



  IF NOT EXISTS(SELECT * FROM information_schema.tables WHERE table_name = 'ec_commande_detail') THEN

   CREATE TABLE ec_commande_detail
(
  id_detail serial NOT NULL, -- id detail de la commande
  id_commande integer, -- reference ec_commande
  id_produit integer, -- reference a ec_produit
  quantite integer, -- quantite du produit choisi
  prix_total numeric(30,6), -- prix total
  montant_depose numeric(30,6), --  montant depose
  date_creation timestamp without time zone, -- date creation
  date_modif timestamp without time zone, -- date modif
  id_ag integer, -- id de l'agence
  CONSTRAINT ec_commande_detail_pkey PRIMARY KEY (id_detail, id_ag)
)
WITH (
  OIDS=FALSE
);
  ALTER TABLE ec_commande_detail
    OWNER TO postgres;
  COMMENT ON TABLE ec_commande_detail
    IS ' reference au detail produits des commandes';
  COMMENT ON COLUMN ec_commande_detail.id_detail IS ' id detail de la commande';
  COMMENT ON COLUMN ec_commande_detail.id_commande IS 'reference ec_commande';
  COMMENT ON COLUMN ec_commande_detail.id_produit IS 'reference a ec_produit';
  COMMENT ON COLUMN ec_commande_detail.quantite IS 'quantite du produit choisi';
  COMMENT ON COLUMN ec_commande_detail.prix_total IS 'prix total';
  COMMENT ON COLUMN ec_commande_detail.montant_depose IS 'montant depose';
  COMMENT ON COLUMN ec_commande_detail.date_creation IS 'date creation ';
  COMMENT ON COLUMN ec_commande_detail.date_modif IS ' date modif';
  COMMENT ON COLUMN ec_commande_detail.id_ag IS ' id de agence';
  		RAISE NOTICE 'Table ec_commande_detail created';
		output_result := 2;
	END IF;



  IF NOT EXISTS(SELECT * FROM information_schema.tables WHERE table_name = 'ec_paiement_commande') THEN

   CREATE TABLE ec_paiement_commande
(
  id serial NOT NULL, -- id detail de paiement
  id_commande integer, -- reference ec_commande
  id_detail_commande integer, -- reference ec_commande_detail
  id_remb integer, -- enumere le nbre de remb pour une commande
  type_paiement integer, -- type de paiement : espece ou cheque
  montant_paye numeric (30,6), -- montant que le client paye
  date_creation timestamp without time zone, --  date de paiement
  id_ag integer, -- id de l'agence
  CONSTRAINT ec_paiement_commande_pkey PRIMARY KEY (id, id_ag)
)
WITH (
  OIDS=FALSE
);
  ALTER TABLE ec_paiement_commande
    OWNER TO postgres;
  COMMENT ON TABLE ec_paiement_commande
    IS ' reference au paiement pour chaque commande';
  COMMENT ON COLUMN ec_paiement_commande.id IS ' id detail de paiement';
  COMMENT ON COLUMN ec_paiement_commande.id_commande IS 'reference ec_commande';
  COMMENT ON COLUMN ec_paiement_commande.id_detail_commande IS 'reference ec_commande_detail';
  COMMENT ON COLUMN ec_paiement_commande.id_remb IS 'enumere le nbre de remb pour une commande';
  COMMENT ON COLUMN ec_paiement_commande.type_paiement IS 'type de paiement : espece ou cheque';
  COMMENT ON COLUMN ec_paiement_commande.montant_paye IS 'montant que le client paye';
  COMMENT ON COLUMN ec_paiement_commande.date_creation IS 'date de paiement';
  COMMENT ON COLUMN ec_paiement_commande.id_ag IS ' id de agence';
  		RAISE NOTICE 'Table ec_paiement_commande created';
		output_result := 2;
	END IF;


  IF NOT EXISTS(SELECT * FROM information_schema.tables WHERE table_name = 'ec_derogation') THEN

   CREATE TABLE ec_derogation
(
  id_derogation serial NOT NULL, -- id detail de derogation
  id_benef integer, -- reference ec_beneficiaire
  id_commande integer, -- reference ec_saison
  etat integer, -- reference ec_saison
  nbre_engrais integer, -- reference ec_saison
  nbre_amendement integer, -- le nouveau plafond pour les engrais
  date_creation timestamp without time zone, --  date de creation
  date_modif timestamp without time zone, -- date de modification
  login_uti text,
  comment text,
  id_his integer,
  id_ag integer, -- id de l'agence
  CONSTRAINT ec_derogation_pkey PRIMARY KEY (id_derogation, id_ag)
)
WITH (
  OIDS=FALSE
);
  ALTER TABLE ec_derogation
    OWNER TO postgres;
  COMMENT ON TABLE ec_derogation
    IS ' reference au demande et approbation des derogations';
  COMMENT ON COLUMN ec_derogation.id_derogation IS ' id detail de derogation';
  COMMENT ON COLUMN ec_derogation.id_benef IS 'reference ec_beneficiaire';
  COMMENT ON COLUMN ec_derogation.id_commande IS 'ID de la commande';
  COMMENT ON COLUMN ec_derogation.etat IS ' etat de la derogation : en cours, accepte, rejete';
  COMMENT ON COLUMN ec_derogation.nbre_engrais IS 'nbre engrais';
  COMMENT ON COLUMN ec_derogation.nbre_amendement IS 'nbre amendement';
  COMMENT ON COLUMN ec_derogation.date_creation IS 'date de creation';
  COMMENT ON COLUMN ec_derogation.date_modif IS 'date de modification';
  COMMENT ON COLUMN ec_derogation.login_uti IS 'login utilisateur';
  COMMENT ON COLUMN ec_derogation.comment IS 'commentaire';
  COMMENT ON COLUMN ec_derogation.id_his IS 'id his';
  COMMENT ON COLUMN ec_derogation.id_ag IS ' id de agence';
  		RAISE NOTICE 'Table ec_derogation created';
		output_result := 2;
	END IF;



-- Operation comptable
	IF NOT EXISTS(SELECT * FROM ad_cpt_ope WHERE type_operation = 615 AND categorie_ope = 1 AND id_ag = numagc()) THEN
		INSERT INTO ad_cpt_ope(type_operation, categorie_ope, id_ag, libel_ope) VALUES (615, 1, numagc(), maketraductionlangsyst('Ajout commande PNSEB'));
		RAISE NOTICE 'Insertion type_operation 615 dans la table ad_cpt_ope effectuée';
		output_result := 2;
	END IF;

	IF NOT EXISTS(SELECT * FROM ad_cpt_ope_cptes WHERE type_operation = 615 AND sens = 'd' AND categorie_cpte = 4 AND id_ag = numagc()) THEN
		INSERT INTO ad_cpt_ope_cptes(type_operation, num_cpte, sens, categorie_cpte, id_ag) VALUES (615, NULL, 'd', 4, numagc());

		RAISE NOTICE 'Insertion type_operation 615 sens ''d'' dans la table ad_cpt_ope_cptes effectuée';
		output_result := 2;
	END IF;

		IF NOT EXISTS(SELECT * FROM ad_cpt_ope WHERE type_operation = 616 AND categorie_ope = 1 AND id_ag = numagc()) THEN
		INSERT INTO ad_cpt_ope(type_operation, categorie_ope, id_ag, libel_ope) VALUES (616, 1, numagc(), maketraductionlangsyst('Annulation commande PNSEB'));
		RAISE NOTICE 'Insertion type_operation 616 dans la table ad_cpt_ope effectuée';
		output_result := 2;
	END IF;

	IF NOT EXISTS(SELECT * FROM ad_cpt_ope_cptes WHERE type_operation = 616 AND sens = 'c' AND categorie_cpte = 4 AND id_ag = numagc()) THEN
		INSERT INTO ad_cpt_ope_cptes(type_operation, num_cpte, sens, categorie_cpte, id_ag) VALUES (616, NULL, 'c', 4, numagc());

		RAISE NOTICE 'Insertion type_operation 616 sens ''d'' dans la table ad_cpt_ope_cptes effectuée';
		output_result := 2;
	END IF;

		IF NOT EXISTS(SELECT * FROM ad_cpt_ope WHERE type_operation = 617 AND categorie_ope = 1 AND id_ag = numagc()) THEN
		INSERT INTO ad_cpt_ope(type_operation, categorie_ope, id_ag, libel_ope) VALUES (617, 1, numagc(), maketraductionlangsyst('Paiement des soldes commandes'));
		RAISE NOTICE 'Insertion type_operation 617 dans la table ad_cpt_ope effectuée';
		output_result := 2;
	END IF;

	IF NOT EXISTS(SELECT * FROM ad_cpt_ope_cptes WHERE type_operation = 617 AND sens = 'd' AND categorie_cpte = 4 AND id_ag = numagc()) THEN
		INSERT INTO ad_cpt_ope_cptes(type_operation, num_cpte, sens, categorie_cpte, id_ag) VALUES (617, NULL, 'd', 4, numagc());

		RAISE NOTICE 'Insertion type_operation 617 sens ''d'' dans la table ad_cpt_ope_cptes effectuée';
		output_result := 2;
	END IF;

	-- Debut Retour v4.3 : fonctions par defaut pour le profil admin
	IF NOT EXISTS(SELECT * FROM adsys_profils_axs WHERE profil = (SELECT id FROM adsys_profils WHERE libel = 'admin') AND fonction = 252) THEN
		INSERT INTO adsys_profils_axs(profil, fonction) VALUES ((SELECT id FROM adsys_profils WHERE libel = 'admin'), 252);

		RAISE NOTICE 'Insertion fonction 252 - Gestion des modules spécifiques pour le profil admin dans la table adsys_profils_axs effectuée';
		output_result := 2;
	END IF;

	IF NOT EXISTS(SELECT * FROM adsys_profils_axs WHERE profil = (SELECT id FROM adsys_profils WHERE libel = 'admin') AND fonction = 253) THEN
		INSERT INTO adsys_profils_axs(profil, fonction) VALUES ((SELECT id FROM adsys_profils WHERE libel = 'admin'), 253);

		RAISE NOTICE 'Insertion fonction 253 - PNSEB_FENACOBU pour le profil admin dans la table adsys_profils_axs effectuée';
		output_result := 2;
	END IF;

	IF NOT EXISTS(SELECT * FROM adsys_profils_axs WHERE profil = (SELECT id FROM adsys_profils WHERE libel = 'admin') AND fonction = 254) THEN
		INSERT INTO adsys_profils_axs(profil, fonction) VALUES ((SELECT id FROM adsys_profils WHERE libel = 'admin'), 254);

		RAISE NOTICE 'Insertion fonction 254 - Automatisme module spécifique pour le profil admin dans la table adsys_profils_axs effectuée';
		output_result := 2;
	END IF;
	-- Fin Retour v4.3 : fonctions par defaut pour le profil admin

	  IF NOT EXISTS(SELECT * FROM information_schema.tables WHERE table_name = 'ec_produit_hist') THEN
	   CREATE TABLE ec_produit_hist
	(
	  id_prod_hist serial NOT NULL, -- id detail de derogation
	  id_produit integer, -- reference ec_beneficiaire
	  id_saison integer, -- reference ec_saison
	  prix_unitaire numeric(30,6), -- reference ec_saison
	  date_creation timestamp without time zone, --  date de creation
	  id_ag integer, -- id de l'agence
	  CONSTRAINT ec_produit_hist_pkey PRIMARY KEY (id_prod_hist, id_ag)
	)
	WITH (
	  OIDS=FALSE
	);
	  ALTER TABLE ec_produit_hist
		OWNER TO postgres;
	  COMMENT ON TABLE ec_produit_hist
		IS ' reference au demande et approbation des derogations';
	  COMMENT ON COLUMN ec_produit_hist.id_prod_hist IS ' id detail de derogation';
	  COMMENT ON COLUMN ec_produit_hist.id_produit IS 'reference ec_beneficiaire';
	  COMMENT ON COLUMN ec_produit_hist.id_saison IS 'ID de la commande';
	  COMMENT ON COLUMN ec_produit_hist.prix_unitaire IS ' etat de la derogation : en cours, accepte, rejete';
	  COMMENT ON COLUMN ec_produit_hist.date_creation IS 'nbre engrais';
	  COMMENT ON COLUMN ec_produit_hist.id_ag IS ' id de agence';
			RAISE NOTICE 'Table ec_derogation created';
			output_result := 2;
		END IF;

-- Function: update_montant_commande(integer, integer)

-- DROP FUNCTION update_montant_commande(integer, integer);

CREATE OR REPLACE FUNCTION update_montant_commande(
    integer,
    integer)
  RETURNS integer AS
$BODY$
DECLARE
  id_annee_agricole ALIAS FOR $1;
  id_saison_culturale ALIAS FOR $2;

  mnt_produit numeric(30,6) := 0;
  mnt_detail numeric(30,6) :=0;
  id_comm_temp integer :=0 ;
  mnt_total numeric(30,6) :=0;

  ligne record;
  ligne1 record;

  cur_cmd refcursor;
  cur_detail_cmd refcursor;

BEGIN

  OPEN cur_cmd FOR select id_commande,etat_commande from ec_commande where etat_commande in (1,6) and id_saison = id_saison_culturale;
  FETCH cur_cmd INTO ligne;
  WHILE FOUND LOOP
	--RAISE NOTICE '';
	--id_comm_temp = ligne.id_commande;
	IF id_comm_temp != ligne.id_commande then

		id_comm_temp = ligne.id_commande;
		mnt_total =0;

		OPEN cur_detail_cmd FOR
		select id_detail,id_produit, quantite, prix_total, montant_depose from ec_commande_detail where id_commande = id_comm_temp;

		FETCH cur_detail_cmd INTO ligne1;
		WHILE FOUND LOOP

			select into mnt_produit prix_unitaire from ec_produit where id_produit = ligne1.id_produit;

			if mnt_produit > 0 then
			mnt_detail = mnt_produit * ligne1.quantite;
			update ec_commande_detail set prix_total = mnt_detail, date_modif=date(now()) where id_detail = ligne1.id_detail;
			RAISE NOTICE 'id detail : %  <==> id produit: %  <==> prix_produit: %  <==> mnt_total_detail:%',ligne1.id_detail, ligne1.id_produit,mnt_produit,mnt_detail;
			mnt_total = mnt_total + mnt_detail;
			end if;


		FETCH cur_detail_cmd INTO ligne1;
		END LOOP;
		CLOSE cur_detail_cmd;

		IF ligne.etat_commande = 1 then
		update ec_commande set montant_total = mnt_total, date_modif=date(now()), etat_commande = 2 where id_commande = id_comm_temp;
		RAISE NOTICE 'ID commande :%  <==> montant total de la commande : %',id_comm_temp,mnt_total ;
		RAISE NOTICE '=========================================================================================';
		ELSE
		update ec_commande set etat_commande = 5,date_modif=date(now()) where id_commande = id_comm_temp;
		update ec_derogation set etat = 3 , date_modif = date(now()), comment= 'Derogation annulé car la periode des avances termines' where id_commande = id_comm_temp;
		RAISE NOTICE 'ID commande :%  <==> commande annuler: %',id_comm_temp,mnt_total ;
		END IF;


	END IF;

  FETCH cur_cmd INTO ligne;
  END LOOP;
 CLOSE cur_cmd;
RETURN 1;
END;
$BODY$
  LANGUAGE plpgsql VOLATILE
  COST 100;
ALTER FUNCTION update_montant_commande(integer, integer)
  OWNER TO postgres;

 -- Function: fermeture_annee_agricole(integer)

-- DROP FUNCTION fermeture_annee_agricole(integer);

CREATE OR REPLACE FUNCTION fermeture_annee_agricole(
    integer)
  RETURNS integer AS
$BODY$
DECLARE
  id_annee_agricole ALIAS FOR $1;

  counter integer:=0;
  ligne record;
  ligne1 record;

  cur_saison refcursor;
  cur_commande_cmd refcursor;

BEGIN
  OPEN cur_saison FOR select id_saison from ec_saison_culturale where id_annee = id_annee_agricole;
  FETCH cur_saison INTO ligne;
  WHILE FOUND LOOP

	OPEN cur_commande_cmd FOR
	select id_commande from ec_commande where id_saison = ligne.id_saison and etat_commande in  (1,2,6) order by id_commande;
	FETCH cur_commande_cmd INTO ligne1;
	WHILE FOUND LOOP
	counter = counter+1;
		update ec_commande set etat_commande = 4 where id_commande = ligne1.id_commande;
		RAISE NOTICE 'id saison = % <==> id commande : %  ',ligne.id_saison,ligne1.id_commande;

	FETCH cur_commande_cmd INTO ligne1;
	END LOOP;
	CLOSE cur_commande_cmd;

FETCH cur_saison INTO ligne;
END LOOP;
CLOSE cur_saison;
RAISE NOTICE 'Counter : %',counter;
RETURN 1;

END;
$BODY$
  LANGUAGE plpgsql VOLATILE
  COST 100;
ALTER FUNCTION fermeture_annee_agricole(integer)
  OWNER TO postgres;


DROP TYPE IF EXISTS data_rapport CASCADE;

CREATE TYPE data_rapport AS (
      nom_zone text,
      nom_colline text,
      id_benef text,
      nom_prenom text,
      id_prod int,
      libel text,
      quantite int,
      montant_paye numeric(30,6),
	  montant_avance numeric(30,6),
	  montant_solde numeric(30,6)
      );


-- Function: getdatarapport(integer, integer, date, date)

-- DROP FUNCTION getdatarapport(integer, integer, date, date);

CREATE OR REPLACE FUNCTION getdatarapport(integer, integer, date, date)
  RETURNS SETOF data_rapport AS
$BODY$
  DECLARE

 in_id_annee ALIAS FOR $1;
 in_id_saison ALIAS FOR $2; -- Date de calcul iar
 in_date_debut ALIAS FOR $3; -- Numero dossier de credits
 in_date_fin ALIAS FOR $4;  -- id agence

 v_id_zone int;
 v_libel_zone text;
 v_id_colline int;
 v_libel_colline text;
 v_id_benef int;
 v_id_benef_verif int;
 v_nom_prenom text;
 v_qty integer :=0;
 v_montant_paye numeric(30,6) :=0;
 v_montant_paye1 numeric(30,6) :=0;
 v_montant_avance numeric(30,6) :=0;
 v_montant_avance1 numeric(30,6) :=0;
 v_montant_solde numeric(30,6) :=0;
 v_montant_solde1 numeric(30,6) :=0;
 id_ben_temp integer :=0 ;

 ligne record;
 ligne1 record;

 cur_idBenef refcursor;
 cur_idProduit refcursor;

 ligne_data data_rapport;

 BEGIN

 IF (in_id_saison = 0) THEN
 OPEN cur_idBenef FOR select distinct id_benef from commande; --where id_saison IN (SELECT id_saison from ec_saison_culturale where id_annee = in_id_annee); --and date_creation >= date('in_date_debut')  and date_creation <= date('in_date_fin');
 END IF;
 IF (in_id_saison = 1) THEN
 OPEN cur_idBenef FOR select distinct id_benef from commande;-- where id_saison = in_id_saison; --and date_creation >= date('in_date_debut')  and date_creation <= date('in_date_fin');
 END IF;
 IF (in_id_saison = 2) THEN
 OPEN cur_idBenef FOR select distinct id_benef from commande;-- where id_saison IN (in_id_saison); --and date_creation >= date('in_date_debut')  and date_creation <= date('in_date_fin');
 END IF;



 FETCH cur_idBenef INTO ligne;
 WHILE FOUND LOOP
  IF id_ben_temp != ligne.id_benef then
  id_ben_temp = ligne.id_benef;
  v_montant_paye1 :=0;
  v_montant_avance1 :=0;
  v_montant_solde1 :=0;

   RAISE NOTICE 'Id_benef = >  %',ligne.id_benef;
   IF (in_id_saison = 0) THEN
   OPEN cur_idProduit FOR SELECT distinct d.id_produit, p.libel FROM ec_commande_detail d  RIGHT JOIN ec_produit p ON p.id_produit = d.id_produit INNER JOIN ec_commande c ON c.id_commande = d.id_commande ORDER BY d.id_produit ASC; --WHERE c.id_saison IN (SELECT id_saison from ec_saison_culturale where id_annee = in_id_annee)
   END IF;
   IF (in_id_saison = 1) THEN
   OPEN cur_idProduit FOR SELECT distinct d.id_produit, p.libel FROM ec_commande_detail d  RIGHT JOIN ec_produit p ON p.id_produit = d.id_produit INNER JOIN ec_commande c ON c.id_commande = d.id_commande WHERE c.id_saison = in_id_saison ORDER BY d.id_produit ASC;
   END IF;
   IF (in_id_saison = 2) THEN
   OPEN cur_idProduit FOR SELECT distinct d.id_produit, p.libel FROM ec_commande_detail d  RIGHT JOIN ec_produit p ON p.id_produit = d.id_produit INNER JOIN ec_commande c ON c.id_commande = d.id_commande WHERE c.id_saison IN (1,in_id_saison) ORDER BY d.id_produit ASC;
   END IF;
   FETCH cur_idProduit INTO ligne1;
   WHILE FOUND LOOP
    IF (in_id_saison = 0) THEN
    select into v_id_benef_verif ,v_qty, v_montant_paye1, v_montant_avance1, v_montant_solde1 id_benef, qty, (montant_avance+montant_solde), montant_avance, montant_solde from commande where id_produit = ligne1.id_produit and id_benef = id_ben_temp;-- and id_saison IN (SELECT id_saison from ec_saison_culturale where id_annee = in_id_annee);
    END IF;
    IF (in_id_saison = 1) THEN
    select into v_id_benef_verif ,v_qty, v_montant_paye1, v_montant_avance1, v_montant_solde1 id_benef, qty, (montant_avance+montant_solde), montant_avance, montant_solde from commande where id_produit = ligne1.id_produit and id_benef = id_ben_temp;-- and id_saison = in_id_saison;
    END IF;
    IF (in_id_saison = 2) THEN
    select into v_id_benef_verif ,v_qty, v_montant_paye1, v_montant_avance1, v_montant_solde1 id_benef, qty, (montant_avance+montant_solde), montant_avance, montant_solde from commande where id_produit = ligne1.id_produit and id_benef = id_ben_temp;-- and id_saison IN (1,in_id_saison);
    END IF;


     select into v_libel_zone l.libel from ec_localisation l INNER JOIN ec_beneficiaire b on b.id_zone = l.id where b.id_beneficiaire = id_ben_temp;

     select into v_libel_colline l.libel from ec_localisation l INNER JOIN ec_beneficiaire b on b.id_colline = l.id where b.id_beneficiaire = id_ben_temp;

     select into v_nom_prenom nom_prenom from ec_beneficiaire where id_beneficiaire = id_ben_temp;

     IF (v_qty IS NULL) THEN
       v_qty = 0;
     END IF;

     IF (v_montant_paye1 IS NOT NULL) THEN
       v_montant_paye = v_montant_paye1;
     END IF;

     IF (v_montant_avance1 IS NOT NULL) THEN
       v_montant_avance = v_montant_avance1;
     END IF;

     IF (v_montant_solde1 IS NOT NULL) THEN
       v_montant_solde = v_montant_solde1;
     END IF;

     SELECT INTO ligne_data v_libel_zone, v_libel_colline, id_ben_temp, v_nom_prenom, ligne1.id_produit, ligne1.libel, v_qty, coalesce(v_montant_paye,0), coalesce(v_montant_avance,0), coalesce(v_montant_solde,0);
     RETURN NEXT ligne_data;

     RAISE NOTICE 'zone=> %, -- le nom de la colline => % -- ID du benef => % -- Nom Prenom Benef => %, --  quantite=>% -- montant_depot=> %', v_libel_zone, v_libel_colline, id_ben_temp,v_nom_prenom,v_qty,v_montant_paye;

    --END IF;

   FETCH cur_idProduit INTO ligne1;
   END LOOP;
   CLOSE cur_idProduit;

  END IF;
 FETCH cur_idBenef INTO ligne;
 END LOOP;
 CLOSE cur_idBenef;
 END;
 $BODY$
  LANGUAGE plpgsql VOLATILE
  COST 100
  ROWS 1000;
ALTER FUNCTION getdatarapport(integer, integer, date, date)
  OWNER TO postgres;



  DROP TYPE IF EXISTS data_produit CASCADE;

CREATE TYPE data_produit AS (
      id_produit int,
      libel_prod text,
      mnt_avance numeric(30,6),
      mnt_solde numeric(30,6),
      id_annee int,
      id_saison text,
      period int
      );



-- Function: getdatarapportproduit(date, date,integer,integer,integer)

-- DROP FUNCTION getdatarapportproduit( date, date,integer,integer,integer);

CREATE OR REPLACE FUNCTION getdatarapportproduit(
    date,
    date,
    integer,
	integer,
	integer)
  RETURNS SETOF data_produit AS
$BODY$
 DECLARE

in_date_debut ALIAS FOR $1;
in_date_fin ALIAS FOR $2;
in_id_annee ALIAS FOR $3;
in_id_saison ALIAS FOR $4;
in_period ALIAS FOR $5; -- 1: avance - 2: solde

mnt_solde numeric(30,6) :=0;

ligne record;
ligne1 record;

cur_prod_avance refcursor;
cur_prod_solde refcursor;

ligne_data data_produit;

 BEGIN


 IF (in_period = 1) THEN

	OPEN cur_prod_avance FOR  select d.id_produit,pd.libel, sum(d.montant_depose) as mnt_avance
	from ec_commande_detail d
	INNER JOIN ec_commande c on c.id_commande=d.id_commande
	INNER JOIN ec_produit pd on pd.id_produit = d.id_produit
	where id_saison = in_id_saison
	and etat_commande not in (5)
	and d.date_creation >= date(in_date_debut)  and d.date_creation <= date(in_date_fin)
	group by d.id_produit,pd.libel
	order by id_produit;
	FETCH cur_prod_avance INTO ligne;
	WHILE FOUND LOOP
	RAISE NOTICE 'Id prod => % -- montant depot =>%',ligne.id_produit,ligne.mnt_avance;

	SELECT INTO ligne_data ligne.id_produit,ligne.libel ,ligne.mnt_avance,mnt_solde,in_id_annee, in_id_saison,in_period;
	RETURN NEXT ligne_data;

	FETCH cur_prod_avance INTO ligne;
	END LOOP;
	CLOSE cur_prod_avance;
END IF;

IF (in_period = 2) THEN

	OPEN cur_prod_avance FOR  select d.id_produit,pd.libel, sum(d.montant_depose) as mnt_avance
	from ec_commande_detail d
	INNER JOIN ec_commande c on c.id_commande=d.id_commande
	INNER JOIN ec_produit pd on pd.id_produit = d.id_produit
	where id_saison = in_id_saison
	and etat_commande not in (5)
	and d.date_creation >= date(in_date_debut)  and d.date_creation <= date(in_date_fin)
	group by d.id_produit, pd.libel
	order by id_produit;
	FETCH cur_prod_avance INTO ligne;
	WHILE FOUND LOOP
		OPEN cur_prod_solde FOR SELECT sum(montant_paye) as mnt_solde_paye from ec_paiement_commande p
		INNER JOIN ec_commande_detail d on p.id_detail_commande = d.id_detail
		INNER JOIN ec_commande c on c.id_commande = d.id_commande
		where p.date_creation >=  date(in_date_debut)  and p.date_creation <= date(in_date_fin)
		--and c.id_saison = in_id_saison
		and d.id_produit = ligne.id_produit;

		FETCH cur_prod_solde INTO ligne1;
		WHILE FOUND LOOP

		IF (ligne1.mnt_solde_paye IS NULL)  THEN
		mnt_solde = 0;
		ELSE
		mnt_solde = ligne1.mnt_solde_paye;
		END IF;

		RAISE NOTICE 'Id prod => % -- montant depot =>%, montant paye =>%',ligne.id_produit,ligne.mnt_avance,ligne1.mnt_solde_paye ;

		SELECT INTO ligne_data ligne.id_produit,ligne.libel, ligne.mnt_avance,mnt_solde,in_id_annee, in_id_saison,in_period;
		RETURN NEXT ligne_data;


		FETCH cur_prod_solde INTO ligne1;
		END LOOP;
		CLOSE cur_prod_solde;


	--SELECT INTO ligne_data ligne.id_produit, ligne.mnt_avance,mnt_solde,in_id_annee, in_id_saison,in_period;
	--RETURN NEXT ligne_data;

	FETCH cur_prod_avance INTO ligne;
	END LOOP;
	CLOSE cur_prod_avance;
END IF;

END;
$BODY$
  LANGUAGE plpgsql VOLATILE
  COST 100
  ROWS 1000;
ALTER FUNCTION getdatarapportproduit(date,date,integer,integer,	integer)
  OWNER TO postgres;


RETURN output_result;

END;
$$
LANGUAGE plpgsql;


select engrais_chimiques_tables_operations_function();

DROP FUNCTION IF EXISTS engrais_chimiques_tables_operations_function();



-- Function: creation_menus_ecrans_engrais_chimiques()

-- DROP FUNCTION creation_menus_ecrans_engrais_chimiques();

CREATE OR REPLACE FUNCTION creation_menus_ecrans_engrais_chimiques()
  RETURNS integer AS
$BODY$
DECLARE

  output_result integer := 0;

BEGIN
	--Parametrage
	----------------menus
	IF NOT EXISTS (select * from menus where nom_menu = 'Gfp') THEN
	 --insertion code
	 INSERT INTO menus (nom_menu, libel_menu, nom_pere, pos_hierarch, ordre, is_menu,fonction, is_cliquable)
	 VALUES ('Gfp', maketraductionlangsyst('Gestion des modules spécifiques'), 'Gen-12', 3, 10, TRUE, 252, TRUE);
	END IF;

	IF NOT EXISTS (select * from menus where nom_menu = 'Gfp-1') THEN
	 --insertion code
	 INSERT INTO menus (nom_menu, libel_menu, nom_pere, pos_hierarch, ordre, is_menu,fonction, is_cliquable)
	 VALUES ('Gfp-1', maketraductionlangsyst('Selection module'), 'Gfp', 4, 1, FALSE, NULL, FALSE);
	END IF;

	IF NOT EXISTS (select * from menus where nom_menu = 'Gfp-2') THEN
	 --insertion code
	 INSERT INTO menus (nom_menu, libel_menu, nom_pere, pos_hierarch, ordre, is_menu,fonction, is_cliquable)
	 VALUES ('Gfp-2', maketraductionlangsyst('Selection table'), 'Gfp', 4, 2, FALSE, NULL, FALSE);
	END IF;

	IF NOT EXISTS (select * from menus where nom_menu = 'Gfp-3') THEN
	 --insertion code
	 INSERT INTO menus (nom_menu, libel_menu, nom_pere, pos_hierarch, ordre, is_menu,fonction, is_cliquable)
	 VALUES ('Gfp-3', maketraductionlangsyst('Selection entrée'), 'Gfp', 4, 3, FALSE, NULL, FALSE);
	END IF;

	IF NOT EXISTS (select * from menus where nom_menu = 'Gaf-1') THEN
	 --insertion code
	 INSERT INTO menus (nom_menu, libel_menu, nom_pere, pos_hierarch, ordre, is_menu,fonction, is_cliquable)
	 VALUES ('Gaf-1', maketraductionlangsyst('Ajouter parametre'), 'Gfp', 4, 4, FALSE, NULL, FALSE);
	END IF;

	IF NOT EXISTS (select * from menus where nom_menu = 'Gaf-2') THEN
	 --insertion code
	 INSERT INTO menus (nom_menu, libel_menu, nom_pere, pos_hierarch, ordre, is_menu,fonction, is_cliquable)
	 VALUES ('Gaf-2', maketraductionlangsyst('Confirmation ajouter parametre'), 'Gfp', 4, 7, FALSE, NULL, FALSE);
	END IF;

	IF NOT EXISTS (select * from menus where nom_menu = 'Gcf-1') THEN
	 --insertion code
	 INSERT INTO menus (nom_menu, libel_menu, nom_pere, pos_hierarch, ordre, is_menu,fonction, is_cliquable)
	 VALUES ('Gcf-1', maketraductionlangsyst('Consultation parametre'), 'Gfp', 4, 6, FALSE, NULL, FALSE);
	END IF;

	IF NOT EXISTS (select * from menus where nom_menu = 'Gmf-1') THEN
	 --insertion code
	 INSERT INTO menus (nom_menu, libel_menu, nom_pere, pos_hierarch, ordre, is_menu,fonction, is_cliquable)
	 VALUES ('Gmf-1', maketraductionlangsyst('Modification parametre'), 'Gfp', 4, 5, FALSE, NULL, FALSE);
	END IF;

	IF NOT EXISTS (select * from menus where nom_menu = 'Gmf-2') THEN
	 --insertion code
	 INSERT INTO menus (nom_menu, libel_menu, nom_pere, pos_hierarch, ordre, is_menu,fonction, is_cliquable)
	 VALUES ('Gmf-2', maketraductionlangsyst('Confirmation Modification'), 'Gfp', 4, 8, FALSE, NULL, FALSE);
	END IF;

	IF NOT EXISTS (select * from menus where nom_menu = 'Gmd-1') THEN
	 --insertion code
	 INSERT INTO menus (nom_menu, libel_menu, nom_pere, pos_hierarch, ordre, is_menu,fonction, is_cliquable)
	 VALUES ('Gmd-1', maketraductionlangsyst('Selection automatisme'), 'Gfp', 4, 9, FALSE, NULL, FALSE);
	END IF;

	----------------ecrans
	IF NOT EXISTS (select * from ecrans where nom_ecran = 'Gfp-1') THEN
	 --insertion code
	 INSERT INTO ecrans(nom_ecran, fichier, nom_menu, fonction)
	 VALUES ('Gfp-1', 'modules/parametrage/module_specifique.php', 'Gfp-1', 252);
	END IF;

	IF NOT EXISTS (select * from ecrans where nom_ecran = 'Gfp-2') THEN
	 --insertion code
	 INSERT INTO ecrans(nom_ecran, fichier, nom_menu, fonction)
	 VALUES ('Gfp-2', 'modules/parametrage/module_specifique.php', 'Gfp-2', 252);
	END IF;

	IF NOT EXISTS (select * from ecrans where nom_ecran = 'Gfp-3') THEN
	 --insertion code
	 INSERT INTO ecrans(nom_ecran, fichier, nom_menu, fonction)
	 VALUES ('Gfp-3', 'modules/parametrage/module_specifique.php', 'Gfp-3', 252);
	END IF;

	IF NOT EXISTS (select * from ecrans where nom_ecran = 'Gaf-1') THEN
	 --insertion code
	 INSERT INTO ecrans(nom_ecran, fichier, nom_menu, fonction)
	 VALUES ('Gaf-1', 'modules/parametrage/module_specifique.php', 'Gaf-1', 252);
	END IF;

	IF NOT EXISTS (select * from ecrans where nom_ecran = 'Gaf-2') THEN
	 --insertion code
	 INSERT INTO ecrans(nom_ecran, fichier, nom_menu, fonction)
	 VALUES ('Gaf-2', 'modules/parametrage/module_specifique.php', 'Gaf-2', 252);
	END IF;

	IF NOT EXISTS (select * from ecrans where nom_ecran = 'Gcf-1') THEN
	 --insertion code
	 INSERT INTO ecrans(nom_ecran, fichier, nom_menu, fonction)
	 VALUES ('Gcf-1', 'modules/parametrage/module_specifique.php', 'Gcf-1', 252);
	END IF;

	IF NOT EXISTS (select * from ecrans where nom_ecran = 'Gmf-1') THEN
	 --insertion code
	 INSERT INTO ecrans(nom_ecran, fichier, nom_menu, fonction)
	 VALUES ('Gmf-1', 'modules/parametrage/module_specifique.php', 'Gmf-1', 252);
	END IF;

	IF NOT EXISTS (select * from ecrans where nom_ecran = 'Gmf-2') THEN
	 --insertion code
	 INSERT INTO ecrans(nom_ecran, fichier, nom_menu, fonction)
	 VALUES ('Gmf-2', 'modules/parametrage/module_specifique.php', 'Gmf-2', 252);
	END IF;

	IF NOT EXISTS (select * from ecrans where nom_ecran = 'Gmd-1') THEN
	 --insertion code
	 INSERT INTO ecrans(nom_ecran, fichier, nom_menu, fonction)
	 VALUES ('Gmd-1', 'modules/parametrage/module_specifique.php', 'Gmd-1', 254);
	END IF;

	IF NOT EXISTS (select * from ecrans where nom_ecran = 'Gmd-2') THEN
	 --insertion code
	 INSERT INTO ecrans(nom_ecran, fichier, nom_menu, fonction)
	 VALUES ('Gmd-2', 'modules/parametrage/module_specifique.php', 'Gmd-1', 254);
	END IF;

	IF NOT EXISTS (select * from ecrans where nom_ecran = 'Gmd-3') THEN
	 --insertion code
	 INSERT INTO ecrans(nom_ecran, fichier, nom_menu, fonction)
	 VALUES ('Gmd-3', 'modules/parametrage/module_specifique.php', 'Gmd-1', 254);
	END IF;

	IF NOT EXISTS (select * from ecrans where nom_ecran = 'Gmd-4') THEN
	 --insertion code
	 INSERT INTO ecrans(nom_ecran, fichier, nom_menu, fonction)
	 VALUES ('Gmd-4', 'modules/parametrage/module_specifique.php', 'Gmd-1', 254);
	END IF;



	---PNSEB-FENACOBU (Core Operations)
	----------------menus
	IF NOT EXISTS (select * from menus where nom_menu = 'Pns') THEN
	 --insertion code
	 INSERT INTO menus (nom_menu, libel_menu, nom_pere, pos_hierarch, ordre, is_menu,fonction, is_cliquable)
	 VALUES ('Pns', maketraductionlangsyst('PNSEB-FENACOBU'), 'Gen-6', 3, 16, TRUE, 171, TRUE);
	END IF;

	IF NOT EXISTS (select * from menus where nom_menu = 'Pns-1') THEN
	 --insertion code
	 INSERT INTO menus (nom_menu, libel_menu, nom_pere, pos_hierarch, ordre, is_menu,fonction, is_cliquable)
	 VALUES ('Pns-1', maketraductionlangsyst('Gestion des Operations/Rapports'), 'Pns', 4, 1, FALSE, NULL, FALSE);
	END IF;

	IF NOT EXISTS (select * from menus where nom_menu = 'Pns-5') THEN
	 --insertion code
	 INSERT INTO menus (nom_menu, libel_menu, nom_pere, pos_hierarch, ordre, is_menu,fonction, is_cliquable)
	 VALUES ('Pns-5', maketraductionlangsyst('Recherche Beneficiaire'), 'Pns', 4, 2, FALSE, NULL, FALSE);
	END IF;

	IF NOT EXISTS (select * from menus where nom_menu = 'Pns-2') THEN
	 --insertion code
	 INSERT INTO menus (nom_menu, libel_menu, nom_pere, pos_hierarch, ordre, is_menu,fonction, is_cliquable)
	 VALUES ('Pns-2', maketraductionlangsyst('Ajout Benficiaire'), 'Pns', 4, 3, FALSE, NULL, FALSE);
	END IF;

	IF NOT EXISTS (select * from menus where nom_menu = 'Pns-4') THEN
	 --insertion code
	 INSERT INTO menus (nom_menu, libel_menu, nom_pere, pos_hierarch, ordre, is_menu,fonction, is_cliquable)
	 VALUES ('Pns-4', maketraductionlangsyst('Confirmation Beneficiaire'), 'Pns', 4, 4, FALSE, NULL, FALSE);
	END IF;

	IF NOT EXISTS (select * from menus where nom_menu = 'Pns-3') THEN
	 --insertion code
	 INSERT INTO menus (nom_menu, libel_menu, nom_pere, pos_hierarch, ordre, is_menu,fonction, is_cliquable)
	 VALUES ('Pns-3', maketraductionlangsyst('Menu Engrais Chimiques'), 'Pns', 4, 5, TRUE, NULL, FALSE);
	END IF;

	IF NOT EXISTS (select * from menus where nom_menu = 'Pnm-1') THEN
	 --insertion code
	 INSERT INTO menus (nom_menu, libel_menu, nom_pere, pos_hierarch, ordre, is_menu,fonction, is_cliquable)
	 VALUES ('Pnm-1', maketraductionlangsyst('Modification Beneficiaire'), 'Pns-3', 5, 1, FALSE, NULL, FALSE);
	END IF;

	IF NOT EXISTS (select * from menus where nom_menu = 'Pnc-1') THEN
	 --insertion code
	 INSERT INTO menus (nom_menu, libel_menu, nom_pere, pos_hierarch, ordre, is_menu,fonction, is_cliquable)
	 VALUES ('Pnc-1', maketraductionlangsyst('Ajout Commande'), 'Pns-3', 5, 2, FALSE, NULL, FALSE);
	END IF;

	IF NOT EXISTS (select * from menus where nom_menu = 'Pnd-1') THEN
	 --insertion code
	 INSERT INTO menus (nom_menu, libel_menu, nom_pere, pos_hierarch, ordre, is_menu,fonction, is_cliquable)
	 VALUES ('Pnd-1', maketraductionlangsyst('Detail Commande'), 'Pns-3', 5, 3, FALSE, NULL, FALSE);
	END IF;

	IF NOT EXISTS (select * from menus where nom_menu = 'Pna-1') THEN
	 --insertion code
	 INSERT INTO menus (nom_menu, libel_menu, nom_pere, pos_hierarch, ordre, is_menu,fonction, is_cliquable)
	 VALUES ('Pna-1', maketraductionlangsyst('Autorisation de derogation'), 'Pns-3', 5, 4, FALSE, NULL, FALSE);
	END IF;

	IF NOT EXISTS (select * from menus where nom_menu = 'Pne-1') THEN
	 --insertion code
	 INSERT INTO menus (nom_menu, libel_menu, nom_pere, pos_hierarch, ordre, is_menu,fonction, is_cliquable)
	 VALUES ('Pne-1', maketraductionlangsyst('Effectuer derogation'), 'Pns-3', 5, 5, FALSE, NULL, FALSE);
	END IF;

	IF NOT EXISTS (select * from menus where nom_menu = 'Pnn-1') THEN
	 --insertion code
	 INSERT INTO menus (nom_menu, libel_menu, nom_pere, pos_hierarch, ordre, is_menu,fonction, is_cliquable)
	 VALUES ('Pnn-1', maketraductionlangsyst('Annulation Commande'), 'Pns-3', 5, 6, FALSE, NULL, FALSE);
	END IF;

	IF NOT EXISTS (select * from menus where nom_menu = 'Pnp-1') THEN
	 --insertion code
	 INSERT INTO menus (nom_menu, libel_menu, nom_pere, pos_hierarch, ordre, is_menu,fonction, is_cliquable)
	 VALUES ('Pnp-1', maketraductionlangsyst('Paiement des Commandes'), 'Pns-3', 5, 7, FALSE, NULL, FALSE);
	END IF;

	IF NOT EXISTS (select * from menus where nom_menu = 'Pnr-1') THEN
	--insertion code
	INSERT INTO menus (nom_menu, libel_menu, nom_pere, pos_hierarch, ordre, is_menu, is_cliquable)
	VALUES ('Pnr-1', maketraductionlangsyst('Selection rapport'), 'Pns-1', 5, 1, false, false);
	END IF;

	IF NOT EXISTS (select * from menus where nom_menu = 'Pnr-2') THEN
	--insertion code
	INSERT INTO menus (nom_menu, libel_menu, nom_pere, pos_hierarch, ordre, is_menu, is_cliquable)
	VALUES ('Pnr-2', maketraductionlangsyst('Selection entrée'), 'Pns-1', 5, 2, false, false);
	END IF;

	IF NOT EXISTS (select * from menus where nom_menu = 'Pnr-3') THEN
	--insertion code
	INSERT INTO menus (nom_menu, libel_menu, nom_pere, pos_hierarch, ordre, is_menu, is_cliquable)
	VALUES ('Pnr-3', maketraductionlangsyst('Export des données'), 'Pns-1', 5, 3, false, false);
	--RAISE NOTICE 'Side Menu 1 created!';

	IF NOT EXISTS (select * from menus where nom_menu = 'Pnt') THEN
	 --insertion code
	 INSERT INTO menus (nom_menu, libel_menu, nom_pere, pos_hierarch, ordre, is_menu,fonction, is_cliquable)
	 VALUES ('Pnt', maketraductionlangsyst('Visualisation des transactions'), 'Pns', 4, 6, true, 182, true);
	END IF;

	IF NOT EXISTS (select * from menus where nom_menu = 'Pnt-1') THEN
	 --insertion code
	 INSERT INTO menus (nom_menu, libel_menu, nom_pere, pos_hierarch, ordre, is_menu,fonction, is_cliquable)
	 VALUES ('Pnt-1', maketraductionlangsyst('Criteres de recherche'), 'Pnt', 5, 1, false, null, false);
	END IF;

	IF NOT EXISTS (select * from menus where nom_menu = 'Pnt-2') THEN
	 --insertion code
	 INSERT INTO menus (nom_menu, libel_menu, nom_pere, pos_hierarch, ordre, is_menu,fonction, is_cliquable)
	 VALUES ('Pnt-2', maketraductionlangsyst('Visualisation'), 'Pnt', 5, 2, false, null, false);
	END IF;

	IF NOT EXISTS (select * from menus where nom_menu = 'Pnt-3') THEN
	 --insertion code
	 INSERT INTO menus (nom_menu, libel_menu, nom_pere, pos_hierarch, ordre, is_menu,fonction, is_cliquable)
	 VALUES ('Pnt-3', maketraductionlangsyst('Rapport transactions'), 'Pnt', 5, 3, false, null, false);
	END IF;

END IF;


	----------------ecrans
	IF NOT EXISTS (select * from ecrans where nom_ecran = 'Pns-1') THEN
	 --insertion code
	 INSERT INTO ecrans(nom_ecran, fichier, nom_menu, fonction)
	 VALUES ('Pns-1', 'modules/guichet/module_engrais_chimiques.php', 'Pns', 171);
	END IF;

	IF NOT EXISTS (select * from ecrans where nom_ecran = 'Pns-3') THEN
	 --insertion code
	 INSERT INTO ecrans(nom_ecran, fichier, nom_menu, fonction)
	 VALUES ('Pns-3', 'modules/guichet/module_engrais_chimiques.php', 'Pns-5', 171);
	END IF;

	IF NOT EXISTS (select * from ecrans where nom_ecran = 'Pnb-1') THEN
	 --insertion code
	 INSERT INTO ecrans(nom_ecran, fichier, nom_menu, fonction)
	 VALUES ('Pnb-1', 'modules/guichet/module_engrais_chimiques.php', 'Pns-2', 177);
	END IF;

	IF NOT EXISTS (select * from ecrans where nom_ecran = 'Pnb-2') THEN
	 --insertion code
	 INSERT INTO ecrans(nom_ecran, fichier, nom_menu, fonction)
	 VALUES ('Pnb-2', 'modules/guichet/module_engrais_chimiques.php', 'Pns-4', 171);
	END IF;

	IF NOT EXISTS (select * from ecrans where nom_ecran = 'Pns-2') THEN
	 --insertion code
	 INSERT INTO ecrans(nom_ecran, fichier, nom_menu, fonction)
	 VALUES ('Pns-2', 'modules/guichet/module_engrais_chimiques.php', 'Pns-3', 171);
	END IF;

	IF NOT EXISTS (select * from ecrans where nom_ecran = 'Pnm-1') THEN
	 --insertion code
	 INSERT INTO ecrans(nom_ecran, fichier, nom_menu, fonction)
	 VALUES ('Pnm-1', 'modules/guichet/module_engrais_chimiques.php', 'Pnm-1', 178);
	END IF;

	IF NOT EXISTS (select * from ecrans where nom_ecran = 'Pnc-1') THEN
	 --insertion code
	 INSERT INTO ecrans(nom_ecran, fichier, nom_menu, fonction)
	 VALUES ('Pnc-1', 'modules/guichet/module_engrais_chimiques.php', 'Pnc-1', 172);
	END IF;

	IF NOT EXISTS (select * from ecrans where nom_ecran = 'Pnc-2') THEN
	 --insertion code
	 INSERT INTO ecrans(nom_ecran, fichier, nom_menu, fonction)
	 VALUES ('Pnc-2', 'modules/guichet/module_engrais_chimiques.php', 'Pnc-1', 171);
	END IF;

	IF NOT EXISTS (select * from ecrans where nom_ecran = 'Pnc-3') THEN
	 --insertion code
	 INSERT INTO ecrans(nom_ecran, fichier, nom_menu, fonction)
	 VALUES ('Pnc-3', 'modules/guichet/module_engrais_chimiques.php', 'Pnc-1', 171);
	END IF;

	IF NOT EXISTS (select * from ecrans where nom_ecran = 'Pnd-1') THEN
	 --insertion code
	 INSERT INTO ecrans(nom_ecran, fichier, nom_menu, fonction)
	 VALUES ('Pnd-1', 'modules/guichet/module_engrais_chimiques.php', 'Pnd-1', 171);
	END IF;

	IF NOT EXISTS (select * from ecrans where nom_ecran = 'Pna-1') THEN
	 --insertion code
	 INSERT INTO ecrans(nom_ecran, fichier, nom_menu, fonction)
	 VALUES ('Pna-1', 'modules/guichet/module_engrais_chimiques.php', 'Pna-1', 174);
	END IF;

	IF NOT EXISTS (select * from ecrans where nom_ecran = 'Pna-2') THEN
	 --insertion code
	 INSERT INTO ecrans(nom_ecran, fichier, nom_menu, fonction)
	 VALUES ('Pna-2', 'modules/guichet/module_engrais_chimiques.php', 'Pna-1', 174);
	END IF;

	IF NOT EXISTS (select * from ecrans where nom_ecran = 'Pne-1') THEN
	 --insertion code
	 INSERT INTO ecrans(nom_ecran, fichier, nom_menu, fonction)
	 VALUES ('Pne-1', 'modules/guichet/module_engrais_chimiques.php', 'Pne-1', 175);
	END IF;

	IF NOT EXISTS (select * from ecrans where nom_ecran = 'Pnn-1') THEN
	 --insertion code
	 INSERT INTO ecrans(nom_ecran, fichier, nom_menu, fonction)
	 VALUES ('Pnn-1', 'modules/guichet/module_engrais_chimiques.php', 'Pnn-1', 176);
	END IF;

	IF NOT EXISTS (select * from ecrans where nom_ecran = 'Pnn-2') THEN
	 --insertion code
	 INSERT INTO ecrans(nom_ecran, fichier, nom_menu, fonction)
	 VALUES ('Pnn-2', 'modules/guichet/module_engrais_chimiques.php', 'Pnn-1', 176);
	END IF;

	IF NOT EXISTS (select * from ecrans where nom_ecran = 'Pnn-3') THEN
	 --insertion code
	 INSERT INTO ecrans(nom_ecran, fichier, nom_menu, fonction)
	 VALUES ('Pnn-3', 'modules/guichet/module_engrais_chimiques.php', 'Pnn-1', 176);
	END IF;

	IF NOT EXISTS (select * from ecrans where nom_ecran = 'Pnp-1') THEN
	 --insertion code
	 INSERT INTO ecrans(nom_ecran, fichier, nom_menu, fonction)
	 VALUES ('Pnp-1', 'modules/guichet/module_engrais_chimiques.php', 'Pnp-1', 173);
	END IF;

	IF NOT EXISTS (select * from ecrans where nom_ecran = 'Pnp-2') THEN
	 --insertion code
	 INSERT INTO ecrans(nom_ecran, fichier, nom_menu, fonction)
	 VALUES ('Pnp-2', 'modules/guichet/module_engrais_chimiques.php', 'Pnp-1', 173);
	END IF;

	IF NOT EXISTS (select * from ecrans where nom_ecran = 'Pnp-3') THEN
	 --insertion code
	 INSERT INTO ecrans(nom_ecran, fichier, nom_menu, fonction)
	 VALUES ('Pnp-3', 'modules/guichet/module_engrais_chimiques.php', 'Pnp-1', 173);
	END IF;

	IF NOT EXISTS (select * from ecrans where nom_ecran = 'Pnp-4') THEN
	 --insertion code
	 INSERT INTO ecrans(nom_ecran, fichier, nom_menu, fonction)
	 VALUES ('Pnp-4', 'modules/guichet/module_engrais_chimiques.php', 'Pnp-1', 173);
	END IF;

	IF NOT EXISTS (select * from ecrans where nom_ecran = 'Pnr-1') THEN
	--insertion code
	INSERT INTO ecrans(nom_ecran, fichier, nom_menu, fonction)
	VALUES ('Pnr-1', 'modules/rapports/rapport_engrais_chimiques.php', 'Pnr-1', 179);
	END IF;

	IF NOT EXISTS (select * from ecrans where nom_ecran = 'Pnr-2') THEN
	--insertion code
	INSERT INTO ecrans(nom_ecran, fichier, nom_menu, fonction)
	VALUES ('Pnr-2', 'modules/rapports/rapport_engrais_chimiques.php', 'Pnr-2', 179);
	END IF;

	IF NOT EXISTS (select * from ecrans where nom_ecran = 'Pnr-3') THEN
	--insertion code
	INSERT INTO ecrans(nom_ecran, fichier, nom_menu, fonction)
	VALUES ('Pnr-3', 'modules/rapports/rapport_engrais_chimiques.php', 'Pnr-3', 179);
	END IF;

	IF NOT EXISTS (select * from ecrans where nom_ecran = 'Pnt-1') THEN
	--insertion code
	INSERT INTO ecrans(nom_ecran, fichier, nom_menu, fonction)
	VALUES ('Pnt-1', 'modules/guichet/module_engrais_chimiques.php', 'Pnt-1', 182);
	END IF;

	IF NOT EXISTS (select * from ecrans where nom_ecran = 'Pnt-2') THEN
	--insertion code
	INSERT INTO ecrans(nom_ecran, fichier, nom_menu, fonction)
	VALUES ('Pnt-2', 'modules/guichet/module_engrais_chimiques.php', 'Pnt-2', 182);
	END IF;

	IF NOT EXISTS (select * from ecrans where nom_ecran = 'Pnt-3') THEN
	--insertion code
	INSERT INTO ecrans(nom_ecran, fichier, nom_menu, fonction)
	VALUES ('Pnt-3', 'modules/guichet/module_engrais_chimiques.php', 'Pnt-3', 182);
	END IF;

	output_result := 1;

	RETURN output_result;

END;
$BODY$
  LANGUAGE plpgsql VOLATILE
  COST 100;
ALTER FUNCTION creation_menus_ecrans_engrais_chimiques()
  OWNER TO adbanking;

  SELECT creation_menus_ecrans_engrais_chimiques();

  DROP FUNCTION IF EXISTS creation_menus_ecrans_engrais_chimiques();

/***********************ACU 2 = UPGRADE************************************************/

CREATE OR REPLACE FUNCTION patch_ecran_menu_ba()
  RETURNS integer AS
$BODY$
DECLARE

  output_result integer = 0;
  id_str_trad integer = 0;

BEGIN

------------------------------Ajout des menus--------------------------------------------------------

	-- Creation menu Validation commande
	IF NOT EXISTS (select * from menus where nom_menu = 'Pvc-1') THEN
	 --insertion code
	 INSERT INTO menus (nom_menu, libel_menu, nom_pere, pos_hierarch, ordre, is_menu,fonction, is_cliquable)
	 VALUES ('Pvc-1', maketraductionlangsyst('Valider commandes en attente'), 'Pns-3', 5, 8, FALSE, NULL, FALSE);
	END IF;


	IF NOT EXISTS (select * from menus where nom_menu = 'Pba-1') THEN
	--insertion code
	INSERT INTO menus (nom_menu, libel_menu, nom_pere, pos_hierarch, ordre, is_menu, is_cliquable)
	VALUES ('Pba-1', maketraductionlangsyst('Ajout des bons achats'), 'Pns-1', 5, 1, false, false);
	END IF;

	IF NOT EXISTS (select * from menus where nom_menu = 'Pba-2') THEN
	--insertion code
	INSERT INTO menus (nom_menu, libel_menu, nom_pere, pos_hierarch, ordre, is_menu, is_cliquable)
	VALUES ('Pba-2', maketraductionlangsyst('Confirmation des bons achats '), 'Pns-1', 5, 2, false, false);
	END IF;

	IF NOT EXISTS (select * from menus where nom_menu = 'Pba-3') THEN
	--insertion code
	INSERT INTO menus (nom_menu, libel_menu, nom_pere, pos_hierarch, ordre, is_menu, is_cliquable)
	VALUES ('Pba-3', maketraductionlangsyst('Enregistrement des bons achats'), 'Pns-1', 5, 3, false, false);
	--RAISE NOTICE 'Side Menu 1 created!';
	END IF;

	IF NOT EXISTS (select * from menus where nom_menu = 'Pst-1') THEN
	--insertion code
	INSERT INTO menus (nom_menu, libel_menu, nom_pere, pos_hierarch, ordre, is_menu, is_cliquable)
	VALUES ('Pst-1', maketraductionlangsyst('Consultation du stock'), 'Pns-1', 5, 1, false, false);
	END IF;

	IF NOT EXISTS (select * from menus where nom_menu = 'Psb-1') THEN
	--insertion code
	INSERT INTO menus (nom_menu, libel_menu, nom_pere, pos_hierarch, ordre, is_menu, is_cliquable)
	VALUES ('Psb-1', maketraductionlangsyst('Gestion des stocks bons achats'), 'Pns-1', 5, 7, false, false);
	END IF;


	IF NOT EXISTS (select * from menus where nom_menu = 'Psb-2') THEN
	--insertion code
	INSERT INTO menus (nom_menu, libel_menu, nom_pere, pos_hierarch, ordre, is_menu, is_cliquable)
	VALUES ('Psb-2', maketraductionlangsyst('Approvisionnement bon achats'), 'Psb-1', 6, 1, false, false);
	END IF;

	IF NOT EXISTS (select * from menus where nom_menu = 'Psb-3') THEN
	--insertion code
	INSERT INTO menus (nom_menu, libel_menu, nom_pere, pos_hierarch, ordre, is_menu, is_cliquable)
	VALUES ('Psb-3', maketraductionlangsyst('Confirmation approvisionnement bon achats'), 'Psb-1', 6, 2, false, false);
	END IF;


	IF NOT EXISTS (select * from menus where nom_menu = 'Psb-4') THEN
	--insertion code
	INSERT INTO menus (nom_menu, libel_menu, nom_pere, pos_hierarch, ordre, is_menu, is_cliquable)
	VALUES ('Psb-4', maketraductionlangsyst('Delestage bon achats'), 'Psb-1', 6, 3, false, false);
	END IF;

	IF NOT EXISTS (select * from menus where nom_menu = 'Psb-5') THEN
	--insertion code
	INSERT INTO menus (nom_menu, libel_menu, nom_pere, pos_hierarch, ordre, is_menu, is_cliquable)
	VALUES ('Psb-5', maketraductionlangsyst('Confirmation delestage bon achats'), 'Psb-1', 6, 4, false, false);
	END IF;

	IF NOT EXISTS (select * from menus where nom_menu = 'Pca-1') THEN
	--insertion code
	INSERT INTO menus (nom_menu, libel_menu, nom_pere, pos_hierarch, ordre, is_menu, is_cliquable)
	VALUES ('Pca-1', maketraductionlangsyst('Paiement des commandes'), 'Pns-1', 5, 8, false, false);
	END IF;

	IF NOT EXISTS (select * from menus where nom_menu = 'Pca-2') THEN
	--insertion code
	INSERT INTO menus (nom_menu, libel_menu, nom_pere, pos_hierarch, ordre, is_menu, is_cliquable)
	VALUES ('Pca-2', maketraductionlangsyst('Selection produit'), 'Pca-1', 5, 9, false, false);
	END IF;


	IF NOT EXISTS (select * from menus where nom_menu = 'Pca-3') THEN
	--insertion code
	INSERT INTO menus (nom_menu, libel_menu, nom_pere, pos_hierarch, ordre, is_menu, is_cliquable)
	VALUES ('Pca-3', maketraductionlangsyst('Confirmation commande'), 'Pca-1', 5, 10, false, false);
	END IF;

	IF NOT EXISTS (select * from menus where nom_menu = 'Pdd-1') THEN
	--insertion code
	INSERT INTO menus (nom_menu, libel_menu, nom_pere, pos_hierarch, ordre, is_menu, is_cliquable)
	VALUES ('Pdd-1', maketraductionlangsyst('Distribution des bons achats'), 'Pns-1', 5, 11, false, false);
	END IF;


	IF NOT EXISTS (select * from menus where nom_menu = 'Pdd-2') THEN
	--insertion code
	INSERT INTO menus (nom_menu, libel_menu, nom_pere, pos_hierarch, ordre, is_menu, is_cliquable)
	VALUES ('Pdd-2', maketraductionlangsyst('Selection des bons achats'), 'Pdd-1', 6, 1, false, false);
	END IF;

	IF NOT EXISTS (select * from menus where nom_menu = 'Pdd-3') THEN
	--insertion code
	INSERT INTO menus (nom_menu, libel_menu, nom_pere, pos_hierarch, ordre, is_menu, is_cliquable)
	VALUES ('Pdd-3', maketraductionlangsyst('Confirmation des bons achats'), 'Pdd-1', 6, 2, false, false);
	END IF;

	IF NOT EXISTS (select * from menus where nom_menu = 'Psa-1') THEN
	--insertion code
	INSERT INTO menus (nom_menu, libel_menu, nom_pere, pos_hierarch, ordre, is_menu, is_cliquable)
	VALUES ('Psa-1', maketraductionlangsyst('Consultation des stocks agent'), 'Pns-1', 5, 1, false, false);
	END IF;

	IF NOT EXISTS (select * from menus where nom_menu = 'Paa-1') THEN
	--insertion code
	INSERT INTO menus (nom_menu, libel_menu, nom_pere, pos_hierarch, ordre, is_menu, is_cliquable)
	VALUES ('Paa-1', maketraductionlangsyst('Consultation des stocks tous les agents'), 'Pns-1', 5, 1, false, false);
	END IF;



--------------------Ajout des Ecrans-------------------------------------------------------------


	--ecran Validation
	IF NOT EXISTS (select * from ecrans where nom_ecran = 'Pvc-1') THEN
	 --insertion code
	 INSERT INTO ecrans(nom_ecran, fichier, nom_menu, fonction)
	 VALUES ('Pvc-1', 'modules/guichet/module_engrais_chimiques.php', 'Pvc-1', 183);
	END IF;

	IF NOT EXISTS (select * from ecrans where nom_ecran = 'Pvc-2') THEN
	 --insertion code
	 INSERT INTO ecrans(nom_ecran, fichier, nom_menu, fonction)
	 VALUES ('Pvc-2', 'modules/guichet/module_engrais_chimiques.php', 'Pvc-1', 171);
	END IF;

	IF NOT EXISTS (select * from ecrans where nom_ecran = 'Pvc-3') THEN
	 --insertion code
	 INSERT INTO ecrans(nom_ecran, fichier, nom_menu, fonction)
	 VALUES ('Pvc-3', 'modules/guichet/module_engrais_chimiques.php', 'Pvc-1', 171);
	END IF;

	IF NOT EXISTS (select * from ecrans where nom_ecran = 'Pba-1') THEN
	--insertion code
	INSERT INTO ecrans(nom_ecran, fichier, nom_menu, fonction)
	VALUES ('Pba-1', 'modules/guichet/module_engrais_chimiques.php', 'Pba-1', 184);
	END IF;

	IF NOT EXISTS (select * from ecrans where nom_ecran = 'Pba-2') THEN
	--insertion code
	INSERT INTO ecrans(nom_ecran, fichier, nom_menu, fonction)
	VALUES ('Pba-2', 'modules/guichet/module_engrais_chimiques.php', 'Pba-2', 184);
	END IF;

	IF NOT EXISTS (select * from ecrans where nom_ecran = 'Pba-3') THEN
	--insertion code
	INSERT INTO ecrans(nom_ecran, fichier, nom_menu, fonction)
	VALUES ('Pba-3', 'modules/guichet/module_engrais_chimiques.php', 'Pba-3', 184);
	END IF;

	IF NOT EXISTS (select * from ecrans where nom_ecran = 'Pst-1') THEN
	--insertion code
	INSERT INTO ecrans(nom_ecran, fichier, nom_menu, fonction)
	VALUES ('Pst-1', 'modules/guichet/module_engrais_chimiques.php', 'Pst-1', 185);
	END IF;

	IF NOT EXISTS (select * from ecrans where nom_ecran = 'Psa-1') THEN
	--insertion code
	INSERT INTO ecrans(nom_ecran, fichier, nom_menu, fonction)
	VALUES ('Psa-1', 'modules/guichet/module_engrais_chimiques.php', 'Psa-1', 802);
	END IF;

	IF NOT EXISTS (select * from ecrans where nom_ecran = 'Paa-1') THEN
	--insertion code
	INSERT INTO ecrans(nom_ecran, fichier, nom_menu, fonction)
	VALUES ('Paa-1', 'modules/guichet/module_engrais_chimiques.php', 'Paa-1', 803);
	END IF;

	IF NOT EXISTS (select * from ecrans where nom_ecran = 'Psb-1') THEN
	--insertion code
	INSERT INTO ecrans(nom_ecran, fichier, nom_menu, fonction)
	VALUES ('Psb-1', 'modules/guichet/module_engrais_chimiques.php', 'Psb-1', 199);
	END IF;

	IF NOT EXISTS (select * from ecrans where nom_ecran = 'Psb-2') THEN
	--insertion code
	INSERT INTO ecrans(nom_ecran, fichier, nom_menu, fonction)
	VALUES ('Psb-2', 'modules/guichet/module_engrais_chimiques.php', 'Psb-2', 200);
	END IF;

	IF NOT EXISTS (select * from ecrans where nom_ecran = 'Psb-3') THEN
	--insertion code
	INSERT INTO ecrans(nom_ecran, fichier, nom_menu, fonction)
	VALUES ('Psb-3', 'modules/guichet/module_engrais_chimiques.php', 'Psb-3', 200);
	END IF;

	IF NOT EXISTS (select * from ecrans where nom_ecran = 'Psb-4') THEN
	--insertion code
	INSERT INTO ecrans(nom_ecran, fichier, nom_menu, fonction)
	VALUES ('Psb-4', 'modules/guichet/module_engrais_chimiques.php', 'Psb-4', 168);
	END IF;

	IF NOT EXISTS (select * from ecrans where nom_ecran = 'Psb-5') THEN
	--insertion code
	INSERT INTO ecrans(nom_ecran, fichier, nom_menu, fonction)
	VALUES ('Psb-5', 'modules/guichet/module_engrais_chimiques.php', 'Psb-5', 168);
	END IF;

	IF NOT EXISTS (select * from ecrans where nom_ecran = 'Pca-1') THEN
	--insertion code
	INSERT INTO ecrans(nom_ecran, fichier, nom_menu, fonction)
	VALUES ('Pca-1', 'modules/guichet/module_engrais_chimiques.php', 'Pca-1', 169);
	END IF;


	IF NOT EXISTS (select * from ecrans where nom_ecran = 'Pca-2') THEN
	--insertion code
	INSERT INTO ecrans(nom_ecran, fichier, nom_menu, fonction)
	VALUES ('Pca-2', 'modules/guichet/module_engrais_chimiques.php', 'Pca-2', 169);
	END IF;


	IF NOT EXISTS (select * from ecrans where nom_ecran = 'Pca-3') THEN
	--insertion code
	INSERT INTO ecrans(nom_ecran, fichier, nom_menu, fonction)
	VALUES ('Pca-3', 'modules/guichet/module_engrais_chimiques.php', 'Pca-3', 169);
	END IF;


	IF NOT EXISTS (select * from ecrans where nom_ecran = 'Pdd-1') THEN
	--insertion code
	INSERT INTO ecrans(nom_ecran, fichier, nom_menu, fonction)
	VALUES ('Pdd-1', 'modules/guichet/module_engrais_chimiques.php', 'Pdd-1', 801);
	END IF;


	IF NOT EXISTS (select * from ecrans where nom_ecran = 'Pdd-2') THEN
	--insertion code
	INSERT INTO ecrans(nom_ecran, fichier, nom_menu, fonction)
	VALUES ('Pdd-2', 'modules/guichet/module_engrais_chimiques.php', 'Pdd-2', 801);
	END IF;


	IF NOT EXISTS (select * from ecrans where nom_ecran = 'Pdd-3') THEN
	--insertion code
	INSERT INTO ecrans(nom_ecran, fichier, nom_menu, fonction)
	VALUES ('Pdd-3', 'modules/guichet/module_engrais_chimiques.php', 'Pdd-3', 801);
	END IF;


output_result := 1;

RETURN output_result;

END;
$BODY$
  LANGUAGE plpgsql VOLATILE
  COST 100;
ALTER FUNCTION patch_ecran_menu_ba()
  OWNER TO adbanking;

  SELECT patch_ecran_menu_ba();

  DROP FUNCTION IF EXISTS patch_ecran_menu_ba();


  CREATE OR REPLACE FUNCTION patch_ecran_menu()
  RETURNS integer AS
$BODY$
DECLARE

  output_result integer = 0;
  id_str_trad integer = 0;
  tableliste_ident integer = 0;

BEGIN

	-- Partie creation tables
-- Check if table ec_livraison_ba exist
IF NOT EXISTS(SELECT * FROM information_schema.tables WHERE table_name = 'ec_livraison_ba') THEN

 CREATE TABLE ec_livraison_ba
(
  id serial NOT NULL,
  id_annee integer,
  id_saison integer,
  date_livraison date,
  numero_livraison varchar,
  id_produit integer,
  qtite_ba integer,
  id_ag integer,
  CONSTRAINT ec_livraison_ba_pkey PRIMARY KEY (id, id_ag)
	)
WITH (
  OIDS=FALSE
);
  ALTER TABLE ec_livraison_ba
  OWNER TO postgres;
END IF;

-- Check if table ec_stock_ba exist
IF NOT EXISTS(SELECT * FROM information_schema.tables WHERE table_name = 'ec_stock_ba') THEN

CREATE TABLE ec_stock_ba
(
  id serial NOT NULL,
  id_annee integer,
  id_saison integer,
  id_produit integer,
  qtite_ba integer,
  id_ag integer,
  CONSTRAINT ec_stock_ba_pkey PRIMARY KEY (id, id_ag)
	)
WITH (
  OIDS=FALSE
);
  ALTER TABLE ec_stock_ba
  OWNER TO postgres;
END IF;

-- Check if table ec_agent_ba exist
IF NOT EXISTS(SELECT * FROM information_schema.tables WHERE table_name = 'ec_agent_ba') THEN

CREATE TABLE ec_agent_ba
(
	id serial NOT NULL,
	id_agent text,
	id_produit integer,
	qtite_ba integer,
	date_modif date,
	id_ag integer,
	CONSTRAINT ec_agent_ba_pkey PRIMARY KEY (id, id_ag)
	)
WITH (
  OIDS=FALSE
);
  ALTER TABLE ec_agent_ba
  OWNER TO postgres;
END IF;

-- Check if table ec_flux_ba exist
IF NOT EXISTS(SELECT * FROM information_schema.tables WHERE table_name = 'ec_flux_ba') THEN

CREATE TABLE ec_flux_ba
(
  id serial NOT NULL,
  id_annee integer,
  id_saison integer,
  type_flux integer,
  id_agent text,
  id_produit integer,
  qtite_ba integer,
  id_utilisateur integer,
  id_ag integer,
  CONSTRAINT ec_flux_ba_pkey PRIMARY KEY (id, id_ag)
	)
WITH (
  OIDS=FALSE
);
  ALTER TABLE ec_flux_ba
  OWNER TO postgres;
END IF;

-- column id_utilisateur
IF NOT EXISTS(SELECT * FROM information_schema.columns WHERE table_name = 'ec_paiement_commande' AND column_name = 'id_utilisateur') THEN
ALTER TABLE ec_paiement_commande ADD COLUMN id_utilisateur integer;
END IF;

-- column etat_paye
IF NOT EXISTS(SELECT * FROM information_schema.columns WHERE table_name = 'ec_paiement_commande' AND column_name = 'etat_paye') THEN
ALTER TABLE ec_paiement_commande ADD COLUMN etat_paye integer;
END IF;

-- column bon_achat
IF NOT EXISTS(SELECT * FROM information_schema.columns WHERE table_name = 'ec_paiement_commande' AND column_name = 'bon_achat') THEN
ALTER TABLE ec_paiement_commande ADD COLUMN bon_achat integer;
END IF;

-- column qtite_paye
IF NOT EXISTS(SELECT * FROM information_schema.columns WHERE table_name = 'ec_paiement_commande' AND column_name = 'qtite_paye') THEN
ALTER TABLE ec_paiement_commande ADD COLUMN qtite_paye integer;
END IF;

-- column is_agent_ec de la table ad_log
IF NOT EXISTS(SELECT * FROM information_schema.columns WHERE table_name = 'ad_log' AND column_name = 'is_agent_ec') THEN
ALTER TABLE ad_log ADD COLUMN is_agent_ec  BOOLEAN DEFAULT false;
END IF;

tableliste_ident := (select ident from tableliste where nomc like 'ad_log' order by ident desc limit 1);
  IF NOT EXISTS(SELECT * FROM d_tableliste WHERE nchmpc = 'is_agent_ec' and tablen = tableliste_ident) THEN
  INSERT INTO d_tableliste VALUES ((select max(ident) from d_tableliste)+1, tableliste_ident, 'is_agent_ec', makeTraductionLangSyst('Est un agent PNSEB ?'), true, NULL, 'bol', false, false, false);
END IF;



	output_result := 1;

	RETURN output_result;

END;
$BODY$
  LANGUAGE plpgsql VOLATILE
  COST 100;
ALTER FUNCTION patch_ecran_menu()
  OWNER TO adbanking;

  SELECT patch_ecran_menu();

  DROP FUNCTION IF EXISTS patch_ecran_menu();



  --------------------- Creation d'un nouveau trigger pour la table ad_log repondant au update des nom_login et se repercutant sur l'update dans la table ec_agent_ba-------------

 DROP TRIGGER IF EXISTS ad_log_before_update ON ad_log;

CREATE OR REPLACE FUNCTION trig_update_login_ec() RETURNS trigger AS $BODY$
DECLARE
old_login_ec text;
  BEGIN
  SELECT INTO old_login_ec  distinct id_agent from ec_agent_ba where id_agent = OLD.login;
  IF NEW.login != old_login_ec THEN
  UPDATE ec_agent_ba SET id_agent = NEW.login where id_agent = old_login_ec;
    END IF;
    RETURN NEW;
END;
$BODY$
LANGUAGE plpgsql VOLATILE
COST 100;


 CREATE TRIGGER ad_log_before_update
  BEFORE UPDATE
  ON ad_log
  FOR EACH ROW
  EXECUTE PROCEDURE trig_update_login_ec();
