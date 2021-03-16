------------------------------- DEBUT : Ticket REL-104 -----------------------------------------------------------------------------------------------------------
-- Function: gettemplatecorrespondance(text, integer, integer, text, integer)

-- DROP FUNCTION gettemplatecorrespondance(text, integer, integer, text, integer);

CREATE OR REPLACE FUNCTION gettemplatecorrespondance(
    text,
    integer,
    integer,
    text,
    integer)
  RETURNS integer AS
$BODY$
DECLARE
ref_budget_old ALIAS FOR $1;
type_budget_old ALIAS FOR $2;
type_budget_new ALIAS FOR $3;
ref_budget_new ALIAS FOR $4;
exo_budget_new ALIAS FOR $5;

-- Variable stocker des colonnes de ad_correspondance
v_cpte_comptable text;
v_etat_compte boolean default TRUE;
v_date_creation date;
v_ref_budget_exist integer := 0 ;

v_max_id_correspondance_inserted integer :=0;

curr_correspondance refcursor;
ligne_correspondance RECORD;
curr_cpte_comptable refcursor;
ligne_cpte_comptable RECORD;

output_result INTEGER :=0;
BEGIN

OPEN curr_correspondance FOR SELECT id,etat_correspondance,type_budget,poste_principal,poste_niveau_1,poste_niveau_2,poste_niveau_3,description,compartiment,dernier_niveau from ad_correspondance where ref_budget = ref_budget_old and etat_correspondance = true order by id;

FETCH curr_correspondance INTO ligne_correspondance;
WHILE FOUND LOOP
RAISE NOTICE 'id_correspondance=> %',ligne_correspondance.id;
	INSERT INTO ad_correspondance (etat_correspondance,type_budget,poste_principal,poste_niveau_1,poste_niveau_2,poste_niveau_3,description,compartiment,dernier_niveau,date_creation,id_ag,ref_budget)
	VALUES(ligne_correspondance.etat_correspondance,ligne_correspondance.type_budget,ligne_correspondance.poste_principal,ligne_correspondance.poste_niveau_1,ligne_correspondance.poste_niveau_2,ligne_correspondance.poste_niveau_3,ligne_correspondance.description,ligne_correspondance.compartiment,ligne_correspondance.dernier_niveau,date(now()),numagc(),ref_budget_new);

	SELECT INTO v_max_id_correspondance_inserted max(id) from ad_correspondance;

	OPEN curr_cpte_comptable FOR SELECT cpte_comptable, etat_compte, id_ag from ad_budget_cpte_comptable where id_ligne = ligne_correspondance.id;
	FETCH curr_cpte_comptable INTO ligne_cpte_comptable;
	WHILE FOUND LOOP


RAISE NOTICE 'id_correspondance => %    ====    compte _comptable=> %',v_max_id_correspondance_inserted,ligne_cpte_comptable.cpte_comptable;
		INSERT INTO ad_budget_cpte_comptable(id_ligne,cpte_comptable,etat_compte,date_creation,id_ag) VALUES(v_max_id_correspondance_inserted,ligne_cpte_comptable.cpte_comptable,ligne_cpte_comptable.etat_compte,date(now()),ligne_cpte_comptable.id_ag);

	FETCH curr_cpte_comptable INTO ligne_cpte_comptable;
	END LOOP;
	CLOSE curr_cpte_comptable;

	FETCH curr_correspondance INTO ligne_correspondance;
END LOOP;
CLOSE curr_correspondance;

RETURN output_result;
END;
$BODY$
  LANGUAGE plpgsql VOLATILE
  COST 100;
ALTER FUNCTION gettemplatecorrespondance(text, integer, integer, text, integer)
  OWNER TO adbanking;


CREATE OR REPLACE FUNCTION ticket_REL_104() RETURNS INT AS
$$
DECLARE
output_result INTEGER = 1;
  id_str_trad integer = 0;
  tableliste_ident integer = 0;

BEGIN

IF NOT EXISTS (SELECT * FROM information_schema.columns WHERE table_name='ad_correspondance' and column_name='ref_budget') THEN
 ALTER TABLE ad_correspondance ADD COLUMN ref_budget text;
END IF;

	RETURN output_result;
END;
$$
LANGUAGE plpgsql;

SELECT ticket_REL_104();
DROP FUNCTION ticket_REL_104();

------------------------------- FIN : Ticket REL-104 -----------------------------------------------------------------------------------------------------------

------------------------------- DEBUT ticket AT-33 --------------------------------------------------------------------------------------------------
CREATE OR REPLACE FUNCTION script_at_33()
  RETURNS INT AS
$BODY$
DECLARE
output_result INTEGER := 1;
tableliste_ident INTEGER := 0;
tableliste_ident_localisation INTEGER := 0;
tableliste_ident_client INTEGER := 0;
tableliste_str INTEGER := 0;
d_tableliste_str INTEGER := 0;

BEGIN
	tableliste_ident := (select ident from tableliste where nomc like 'ad_agc' order by ident desc limit 1);

	IF NOT EXISTS(SELECT * FROM d_tableliste WHERE nchmpc = 'identification_client' and tablen = tableliste_ident) THEN
	  ALTER TABLE ad_agc ADD identification_client INTEGER DEFAULT 1;
	  d_tableliste_str := makeTraductionLangSyst('Identification Client');
	  INSERT INTO d_tableliste VALUES ((select max(ident) from d_tableliste)+1, tableliste_ident, 'identification_client', d_tableliste_str, true, NULL, 'int', true, false, false);
	  IF EXISTS(SELECT langue FROM adsys_langues_systeme WHERE code = 'en_GB') THEN
	    INSERT INTO ad_traductions VALUES (d_tableliste_str,'en_GB','Client Identification');
	  END IF;
	END IF;
	IF NOT EXISTS(SELECT * FROM information_schema.tables WHERE table_name = 'adsys_localisation_rwanda') THEN

	   CREATE TABLE adsys_localisation_rwanda
	(
	  id serial NOT NULL,
	  code_localisation text,
	  libelle_localisation text,
	  type_localisation integer,
	  parent integer,
	  id_ag integer,
	  CONSTRAINT adsys_localisation_rwanda_pkey PRIMARY KEY (id, id_ag)
	);

	END IF;

		  -- Insertion dans tableliste
	IF NOT EXISTS(SELECT * FROM tableliste WHERE nomc = 'adsys_localisation_rwanda') THEN
	INSERT INTO tableliste VALUES ((select max(ident) from tableliste)+1, 'adsys_localisation_rwanda', makeTraductionLangSyst('"Paramétrage des localisations Rwanda"'), true);
	RAISE NOTICE 'Données table ec_localisation rajoutés dans table tableliste';
	END IF;

	tableliste_ident_localisation := (select ident from tableliste where nomc like 'adsys_localisation_rwanda' order by ident desc limit 1);

	IF NOT EXISTS(SELECT * FROM d_tableliste WHERE nchmpc = 'code_localisation' and tablen = tableliste_ident_localisation) THEN
	  INSERT INTO d_tableliste VALUES ((select max(ident) from d_tableliste)+1, tableliste_ident_localisation, 'code_localisation', makeTraductionLangSyst('Code localisation'), false, NULL, 'txt', false, false, false);
	END IF;


	IF NOT EXISTS(SELECT * FROM d_tableliste WHERE nchmpc = 'libelle_localisation' and tablen = tableliste_ident_localisation) THEN
	  INSERT INTO d_tableliste VALUES ((select max(ident) from d_tableliste)+1, tableliste_ident_localisation, 'libelle_localisation', makeTraductionLangSyst('Libel localisation'), true, NULL, 'txt', true, null, false);
	END IF;

	IF NOT EXISTS(SELECT * FROM d_tableliste WHERE nchmpc = 'type_localisation' and tablen = tableliste_ident_localisation) THEN
	  INSERT INTO d_tableliste VALUES ((select max(ident) from d_tableliste)+1, tableliste_ident_localisation, 'type_localisation', makeTraductionLangSyst('Type localisation'), false, NULL, 'int', false, false, false);
	END IF;

	IF NOT EXISTS(SELECT * FROM d_tableliste WHERE nchmpc = 'parent' and tablen = tableliste_ident_localisation) THEN
	  INSERT INTO d_tableliste VALUES ((select max(ident) from d_tableliste)+1, tableliste_ident_localisation, 'parent', makeTraductionLangSyst('Parent'), false, NULL, 'int', false, false, false);
	END IF;

	IF NOT EXISTS(SELECT * FROM d_tableliste WHERE nchmpc = 'id' and tablen = tableliste_ident_localisation) THEN
	  INSERT INTO d_tableliste VALUES ((select max(ident) from d_tableliste)+1, tableliste_ident_localisation, 'id', makeTraductionLangSyst('Id localisation'), true, NULL, 'int', null, true, false);
	END IF;

IF NOT EXISTS (select * from ecrans where nom_ecran = 'Lor-1') THEN
	--insertion code
	INSERT INTO ecrans(nom_ecran, fichier, nom_menu, fonction)
	VALUES ('Lor-1', 'modules/parametrage/tables.php', 'Pta', 292);
	RAISE NOTICE 'Ecran 1 created!';
END IF;

IF NOT EXISTS (select * from ecrans where nom_ecran = 'Lor-2') THEN
	--insertion code
	INSERT INTO ecrans(nom_ecran, fichier, nom_menu, fonction)
	VALUES ('Lor-2', 'modules/parametrage/tables.php', 'Pta', 292);
	RAISE NOTICE 'Ecran 1 created!';
END IF;
IF NOT EXISTS (select * from ecrans where nom_ecran = 'Lor-3') THEN
	--insertion code
	INSERT INTO ecrans(nom_ecran, fichier, nom_menu, fonction)
	VALUES ('Lor-3', 'modules/parametrage/tables.php', 'Pta', 292);
	RAISE NOTICE 'Ecran 1 created!';
END IF;

	tableliste_ident_client := (select ident from tableliste where nomc like 'ad_cli' order by ident desc limit 1);

	IF NOT EXISTS(SELECT * FROM d_tableliste WHERE nchmpc = 'province' and tablen = tableliste_ident_client) THEN
	  ALTER TABLE ad_cli ADD province INTEGER;
	  d_tableliste_str := makeTraductionLangSyst('Localisation province');
	  INSERT INTO d_tableliste VALUES ((select max(ident) from d_tableliste)+1, tableliste_ident_client, 'province', d_tableliste_str, true, (SELECT ident from d_tableliste where tablen = tableliste_ident_localisation and nchmpc = 'id'), 'int', true, false, false);
	END IF;

	IF NOT EXISTS(SELECT * FROM d_tableliste WHERE nchmpc = 'district' and tablen = tableliste_ident_client) THEN
	  ALTER TABLE ad_cli ADD district INTEGER;
	  d_tableliste_str := makeTraductionLangSyst('Localisation district');
	  INSERT INTO d_tableliste VALUES ((select max(ident) from d_tableliste)+1, tableliste_ident_client, 'district', d_tableliste_str, true, (SELECT ident from d_tableliste where tablen = tableliste_ident_localisation and nchmpc = 'id'), 'int', true, false, false);
	END IF;

	IF NOT EXISTS(SELECT * FROM d_tableliste WHERE nchmpc = 'secteur' and tablen = tableliste_ident_client) THEN
	  ALTER TABLE ad_cli ADD secteur INTEGER;
	  d_tableliste_str := makeTraductionLangSyst('Localisation secteur');
	  INSERT INTO d_tableliste VALUES ((select max(ident) from d_tableliste)+1, tableliste_ident_client, 'secteur', d_tableliste_str, true, (SELECT ident from d_tableliste where tablen = tableliste_ident_localisation and nchmpc = 'id'), 'int', true, false, false);
	END IF;

	IF NOT EXISTS(SELECT * FROM d_tableliste WHERE nchmpc = 'cellule' and tablen = tableliste_ident_client) THEN
	  ALTER TABLE ad_cli ADD cellule INTEGER;
	  d_tableliste_str := makeTraductionLangSyst('Localisation cellule');
	  INSERT INTO d_tableliste VALUES ((select max(ident) from d_tableliste)+1, tableliste_ident_client, 'cellule', d_tableliste_str, true, (SELECT ident from d_tableliste where tablen = tableliste_ident_localisation and nchmpc = 'id'), 'int', true, false, false);
	END IF;

	IF NOT EXISTS(SELECT * FROM d_tableliste WHERE nchmpc = 'village' and tablen = tableliste_ident_client) THEN
	  ALTER TABLE ad_cli ADD village INTEGER;
	  d_tableliste_str := makeTraductionLangSyst('Localisation village');
	  INSERT INTO d_tableliste VALUES ((select max(ident) from d_tableliste)+1, tableliste_ident_client, 'village', d_tableliste_str, true, (SELECT ident from d_tableliste where tablen = tableliste_ident_localisation and nchmpc = 'id'), 'int', true, false, false);
	END IF;




RETURN output_result;
END;
$BODY$
  LANGUAGE plpgsql VOLATILE
  COST 100;
ALTER FUNCTION script_at_33()
  OWNER TO postgres;


  SELECT script_at_33();
  DROP FUNCTION IF EXISTS script_at_33();

  ------------------------------------------------------------ FIN Ticket AT-33 ---------------------------------------------------------------------
  ----------------------------------------------------------  DEBUT Ticket AT-34 ---------------------------------------------------------------------
CREATE OR REPLACE FUNCTION script_at_34()
  RETURNS INT AS
$BODY$
DECLARE
output_result INTEGER := 1;
tableliste_ident INTEGER := 0;
tableliste_ident_classe INTEGER := 0;
tableliste_ident_client INTEGER := 0;
tableliste_str INTEGER := 0;
d_tableliste_str INTEGER := 0;

BEGIN

	tableliste_ident := (select ident from tableliste where nomc like 'ad_agc' order by ident desc limit 1);

	IF NOT EXISTS(SELECT * FROM d_tableliste WHERE nchmpc = 'identification_client' and tablen = tableliste_ident) THEN
	  ALTER TABLE ad_agc ADD identification_client INTEGER DEFAULT 1;
	  d_tableliste_str := makeTraductionLangSyst('Identification Client');
	  INSERT INTO d_tableliste VALUES ((select max(ident) from d_tableliste)+1, tableliste_ident, 'identification_client', d_tableliste_str, true, NULL, 'int', true, false, false);
	  IF EXISTS(SELECT langue FROM adsys_langues_systeme WHERE code = 'en_GB') THEN
	    INSERT INTO ad_traductions VALUES (d_tableliste_str,'en_GB','Client Identification');
	  END IF;
	END IF;
	IF NOT EXISTS(SELECT * FROM information_schema.tables WHERE table_name = 'adsys_classe_socio_economique_rwanda') THEN

	   CREATE TABLE adsys_classe_socio_economique_rwanda
	(
	  id serial NOT NULL,
	  classe integer,
	  description text,
	  id_ag integer,
	  CONSTRAINT adsys_classe_socio_economique_rwanda_pkey PRIMARY KEY (id, id_ag)
	);

	END IF;

	 -- Insertion dans tableliste
	IF NOT EXISTS(SELECT * FROM tableliste WHERE nomc = 'adsys_classe_socio_economique_rwanda') THEN
	INSERT INTO tableliste VALUES ((select max(ident) from tableliste)+1, 'adsys_classe_socio_economique_rwanda', makeTraductionLangSyst('"Paramétrage des classes socio-économiques Rwanda"'), true);
	RAISE NOTICE 'Données table adsys_classe_socio_economique_rwanda rajoutés dans table tableliste';
	END IF;

	tableliste_ident_classe := (select ident from tableliste where nomc like 'adsys_classe_socio_economique_rwanda' order by ident desc limit 1);

	IF NOT EXISTS(SELECT * FROM d_tableliste WHERE nchmpc = 'classe' and tablen = tableliste_ident_classe) THEN
	  INSERT INTO d_tableliste VALUES ((select max(ident) from d_tableliste)+1, tableliste_ident_classe, 'classe', makeTraductionLangSyst('Classe'), true, NULL, 'int', true, NULL, false);
	END IF;


	IF NOT EXISTS(SELECT * FROM d_tableliste WHERE nchmpc = 'description' and tablen = tableliste_ident_classe) THEN
	  INSERT INTO d_tableliste VALUES ((select max(ident) from d_tableliste)+1, tableliste_ident_classe, 'description', makeTraductionLangSyst('Description'), true, NULL, 'are', false, null, false);
	END IF;

	IF NOT EXISTS(SELECT * FROM d_tableliste WHERE nchmpc = 'id' and tablen = tableliste_ident_classe) THEN
	  INSERT INTO d_tableliste VALUES ((select max(ident) from d_tableliste)+1, tableliste_ident_classe, 'id', makeTraductionLangSyst('Id classe'), true, NULL, 'int', null, true, false);
	END IF;

	tableliste_ident_client := (select ident from tableliste where nomc like 'ad_cli' order by ident desc limit 1);

	IF NOT EXISTS(SELECT * FROM d_tableliste WHERE nchmpc = 'classe_socio_economique' and tablen = tableliste_ident_client) THEN
	  ALTER TABLE ad_cli ADD classe_socio_economique INTEGER;
	  d_tableliste_str := makeTraductionLangSyst('Classe socio-économique');
	  INSERT INTO d_tableliste VALUES ((select max(ident) from d_tableliste)+1, tableliste_ident_client, 'classe_socio_economique', d_tableliste_str, true, (SELECT ident from d_tableliste where tablen = tableliste_ident_classe and nchmpc = 'id'), 'int', null, null, false);
	END IF;

RETURN output_result;
END;
$BODY$
  LANGUAGE plpgsql VOLATILE
  COST 100;
ALTER FUNCTION script_at_34()
  OWNER TO postgres;

  SELECT script_at_34();
  DROP FUNCTION IF EXISTS script_at_34();
  ---------------------------------------------------------- FIN Ticket AT-34 -----------------------------------------------------------------------------------------
  ---------------------------------------------------------- DEBUT Ticket AT-41 -----------------------------------------------------------------------------------------
  CREATE OR REPLACE FUNCTION at_41_gestion_education()
  RETURNS INT AS
$BODY$
DECLARE
output_result INTEGER = 1;
tableliste_ident INTEGER = 0;
tableliste_str INTEGER = 0;
d_tableliste_str INTEGER = 0;
count_data INTEGER = 0;
table_ref INTEGER = 0;
column_ref INTEGER = 0;

BEGIN
	--Parametrage agence : ajout nouveau champ 'identification_client'
	tableliste_ident := (select ident from tableliste where nomc like 'ad_agc' order by ident desc limit 1);

	IF NOT EXISTS(SELECT * FROM d_tableliste WHERE nchmpc = 'identification_client' and tablen = tableliste_ident) THEN
	  ALTER TABLE ad_agc ADD identification_client INTEGER DEFAULT 1;
	  d_tableliste_str := makeTraductionLangSyst('Identification Client');
	  INSERT INTO d_tableliste VALUES ((select max(ident) from d_tableliste)+1, tableliste_ident, 'identification_client', d_tableliste_str, true, NULL, 'int', true, false, false);
	  IF EXISTS(SELECT langue FROM adsys_langues_systeme WHERE code = 'en_GB') THEN
	    INSERT INTO ad_traductions VALUES (d_tableliste_str,'en_GB','Client Identification');
	  END IF;
	END IF;

	tableliste_ident := 0;

	--Creation nouvelle table parametrage adsys_education_rwanda :
	IF NOT EXISTS(SELECT * FROM information_schema.tables WHERE table_name = 'adsys_education_rwanda') THEN

		CREATE TABLE adsys_education_rwanda
		(
		id serial NOT NULL, -- La clef primaire de la table
		code_education text NOT NULL, -- Un nom associé à chaque éducation (Ex. PHD….)
		description_education text, -- La description concernant l’éducation
		id_ag integer NOT NULL, -- Id de l'agence
		CONSTRAINT adsys_education_rwanda_pkey PRIMARY KEY (id, id_ag)
		)
		WITH (
		OIDS=FALSE
		);

		ALTER TABLE adsys_education_rwanda
		OWNER TO postgres;
		COMMENT ON TABLE adsys_education_rwanda
		IS ' reference aux educations';
		COMMENT ON COLUMN adsys_education_rwanda.id IS 'id de l education';
		COMMENT ON COLUMN adsys_education_rwanda.code_education IS 'nom de l education';
		COMMENT ON COLUMN adsys_education_rwanda.description_education IS 'description de l education';

		IF NOT EXISTS(SELECT * FROM tableliste WHERE nomc = 'adsys_education_rwanda') THEN
			INSERT INTO tableliste VALUES ((select max(ident) from tableliste)+1, 'adsys_education_rwanda', makeTraductionLangSyst('Paramétrage des educations'), true);
			RAISE NOTICE 'Données table adsys_education_rwanda rajoutés dans table tableliste';
		END IF;

		tableliste_ident := (select ident from tableliste where nomc like 'adsys_education_rwanda' order by ident desc limit 1);

		IF NOT EXISTS(SELECT * FROM d_tableliste WHERE nchmpc = 'id' and tablen = tableliste_ident) THEN
		 INSERT INTO d_tableliste VALUES ((select max(ident) from d_tableliste)+1, tableliste_ident, 'id', makeTraductionLangSyst('Id education'), true, NULL, 'int', false, true, false);
		END IF;

		IF NOT EXISTS(SELECT * FROM d_tableliste WHERE nchmpc = 'code_education' and tablen = tableliste_ident) THEN
		  INSERT INTO d_tableliste VALUES ((select max(ident) from d_tableliste)+1, tableliste_ident, 'code_education', makeTraductionLangSyst('Code Education'), true, NULL, 'txt', true, false, false);
		END IF;

		IF NOT EXISTS(SELECT * FROM d_tableliste WHERE nchmpc = 'description_education' and tablen = tableliste_ident) THEN
		  INSERT INTO d_tableliste VALUES ((select max(ident) from d_tableliste)+1, tableliste_ident, 'description_education', makeTraductionLangSyst('Description Education'), false, NULL, 'are', false, false, false);
		END IF;

		--Insertion dans la table adsys_education_rwanda
		count_data := (select count(id) from adsys_education_rwanda);
		IF count_data = 0 OR count_data IS NULL THEN
			INSERT INTO adsys_education_rwanda (code_education, id_ag) VALUES ('PHD', numagc());
			INSERT INTO adsys_education_rwanda (code_education, id_ag) VALUES ('Masters', numagc());
			INSERT INTO adsys_education_rwanda (code_education, id_ag) VALUES ('Bachelors Degree', numagc());
			INSERT INTO adsys_education_rwanda (code_education, id_ag) VALUES ('Diploma (A2 or A2 level)', numagc());
			INSERT INTO adsys_education_rwanda (code_education, id_ag) VALUES ('School attendance below A2 level', numagc());
			INSERT INTO adsys_education_rwanda (code_education, id_ag) VALUES ('High School', numagc());
			INSERT INTO adsys_education_rwanda (code_education, id_ag) VALUES ('Primary School', numagc());
			INSERT INTO adsys_education_rwanda (code_education, id_ag) VALUES ('Below Primary', numagc());
		END IF;

	END IF;

	--Table Client : ajout nouveau champ 'education'
	tableliste_ident := (select ident from tableliste where nomc like 'ad_cli' order by ident desc limit 1);

	table_ref := (select ident from tableliste where nomc like 'adsys_education_rwanda' order by ident desc limit 1);
	column_ref := (select ident from d_tableliste where nchmpc = 'id' and tablen = table_ref order by ident desc limit 1);

	--RAISE NOTICE 'Table de reference = % | Champe de reference = %',table_ref,column_ref;

	IF NOT EXISTS(SELECT * FROM d_tableliste WHERE nchmpc = 'education' and tablen = tableliste_ident) THEN
	  ALTER TABLE ad_cli ADD education INTEGER DEFAULT 0;
	  d_tableliste_str := makeTraductionLangSyst('Education');
	  INSERT INTO d_tableliste VALUES ((select max(ident) from d_tableliste)+1, tableliste_ident, 'education', d_tableliste_str, true, column_ref, 'int', true, false, false);
	  IF EXISTS(SELECT langue FROM adsys_langues_systeme WHERE code = 'en_GB') THEN
	    INSERT INTO ad_traductions VALUES (d_tableliste_str,'en_GB','Education');
	  END IF;
	END IF;

RETURN output_result;
END;
$BODY$
  LANGUAGE plpgsql VOLATILE
  COST 100;
ALTER FUNCTION at_41_gestion_education()
  OWNER TO postgres;

  SELECT at_41_gestion_education();
  DROP FUNCTION IF EXISTS at_41_gestion_education();
  ---------------------------------------------------------- FIN Ticket AT-41 -----------------------------------------------------------------------------------------
