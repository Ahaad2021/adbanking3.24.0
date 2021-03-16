CREATE OR REPLACE FUNCTION maj_adsys_tarification() RETURNS trigger AS
$BODY$
BEGIN
	IF (TG_OP = 'UPDATE') THEN
		NEW.date_modif := current_timestamp::varchar(23)::timestamp;
	END IF;

	RETURN NEW;
END;
$BODY$
LANGUAGE plpgsql VOLATILE
COST 100;

CREATE OR REPLACE FUNCTION trig_insert_adsys_tarification_hist() RETURNS trigger AS
$BODY$
	BEGIN
	    INSERT INTO adsys_tarification_hist (date_action, id_tarification, code_abonnement, type_de_frais, mode_frais, valeur, compte_comptable, date_debut_validite, date_fin_validite, statut, id_ag, date_creation, date_modif) VALUES ( NOW(), OLD.id_tarification, OLD.code_abonnement, OLD.type_de_frais, OLD.mode_frais, OLD.valeur, OLD.compte_comptable, OLD.date_debut_validite, OLD.date_fin_validite, OLD.statut, OLD.id_ag, OLD.date_creation, OLD.date_modif);
	    RETURN NEW;
    END;
$BODY$
LANGUAGE plpgsql VOLATILE
COST 100;


CREATE OR REPLACE FUNCTION patch_tarification() RETURNS INT AS
$$
DECLARE
output_result INTEGER = 1;
tableliste_ident INTEGER = 0;

BEGIN

	RAISE NOTICE 'START';

	IF NOT EXISTS(SELECT * FROM information_schema.tables WHERE table_name = 'adsys_tarification') THEN
		CREATE TABLE adsys_tarification
		(
		  id_tarification serial NOT NULL,
		  code_abonnement character varying(50),
		  type_de_frais character varying(255),
		  mode_frais integer DEFAULT 1,
		  valeur double precision,
		  compte_comptable text,
		  date_debut_validite timestamp without time zone,
		  date_fin_validite timestamp without time zone,
		  statut boolean DEFAULT false,
		  id_ag integer NOT NULL,
		  date_creation timestamp without time zone NOT NULL DEFAULT ((now())::character varying(23))::timestamp without time zone,
		  date_modif timestamp without time zone,
		  CONSTRAINT adsys_tarification_pkey PRIMARY KEY (id_tarification, id_ag),
		  CONSTRAINT adsys_tarification_ukey UNIQUE (type_de_frais, id_ag)
		)
		WITH (
		  OIDS=FALSE
		);
		
		DROP TRIGGER IF EXISTS maj_adsys_tarification ON adsys_tarification;
		CREATE TRIGGER maj_adsys_tarification
		  BEFORE UPDATE
		  ON adsys_tarification
		  FOR EACH ROW
		  EXECUTE PROCEDURE maj_adsys_tarification();
		
		DROP TRIGGER IF EXISTS trig_before_update_adsys_tarification ON adsys_tarification;
		CREATE TRIGGER trig_before_update_adsys_tarification
		  BEFORE UPDATE
		  ON adsys_tarification
		  FOR EACH ROW
		  EXECUTE PROCEDURE trig_insert_adsys_tarification_hist();

		RAISE NOTICE 'Table adsys_tarification created';
		output_result := 2;
	END IF;
	
	IF NOT EXISTS(SELECT * FROM information_schema.tables WHERE table_name = 'adsys_tarification_hist') THEN
		CREATE TABLE adsys_tarification_hist
		(
		  id serial NOT NULL,
		  date_action timestamp without time zone DEFAULT now(),
		  id_tarification integer NOT NULL,
		  code_abonnement character varying(50),
		  type_de_frais character varying(255),
		  mode_frais integer DEFAULT 1,
		  valeur double precision,
		  compte_comptable text,
		  date_debut_validite timestamp without time zone,
		  date_fin_validite timestamp without time zone,
		  statut boolean,
		  id_ag integer NOT NULL,
		  date_creation timestamp without time zone,
		  date_modif timestamp without time zone,
		  CONSTRAINT adsys_tarification_hist_pkey PRIMARY KEY (id, id_ag)
		)
		WITH (
		  OIDS=FALSE
		);

		RAISE NOTICE 'Table adsys_tarification_hist created';
		output_result := 2;
	END IF;
	
	IF NOT EXISTS(SELECT * FROM tableliste WHERE nomc = 'adsys_tarification') THEN
		INSERT INTO tableliste(ident, nomc, noml, is_table) VALUES ((select max(ident) from tableliste)+1, 'adsys_tarification', maketraductionlangsyst('Table Tarification'), true);
		
		RAISE NOTICE 'Insertion table adsys_tarification de la table tableliste effectuée';
		output_result := 2;
	END IF;

	tableliste_ident := (select ident from tableliste where nomc like 'adsys_tarification' order by ident desc limit 1);

	IF NOT EXISTS(SELECT * FROM d_tableliste WHERE tablen=tableliste_ident AND nchmpc = 'id_tarification') THEN
		INSERT INTO d_tableliste(ident, tablen, nchmpc, nchmpl, isreq, ref_field, type, onslct, ispkey, traduit) VALUES ((select max(ident) from d_tableliste)+1, tableliste_ident, 'id_tarification', maketraductionlangsyst('Identifiant table tarification'), true, NULL, 'int', false, true, false);

		RAISE NOTICE 'Insertion id_tarification de la table d_tableliste effectuée';
		output_result := 2;
	END IF;

	IF NOT EXISTS(SELECT * FROM d_tableliste WHERE tablen=tableliste_ident AND nchmpc = 'code_abonnement') THEN
		INSERT INTO d_tableliste(ident, tablen, nchmpc, nchmpl, isreq, ref_field, type, onslct, ispkey, traduit) VALUES ((select max(ident) from d_tableliste)+1, tableliste_ident, 'code_abonnement', maketraductionlangsyst('Code abonnement'), true, NULL, 'lsb', true, false, false);

		RAISE NOTICE 'Insertion code_abonnement de la table d_tableliste effectuée';
		output_result := 2;
	END IF;

	IF NOT EXISTS(SELECT * FROM d_tableliste WHERE tablen=tableliste_ident AND nchmpc = 'type_de_frais') THEN
		INSERT INTO d_tableliste(ident, tablen, nchmpc, nchmpl, isreq, ref_field, type, onslct, ispkey, traduit) VALUES ((select max(ident) from d_tableliste)+1, tableliste_ident, 'type_de_frais', maketraductionlangsyst('Type de frais'), true, NULL, 'lsb', false, false, false);

		RAISE NOTICE 'Insertion type_de_frais de la table d_tableliste effectuée';
		output_result := 2;
	END IF;

	IF NOT EXISTS(SELECT * FROM d_tableliste WHERE tablen=tableliste_ident AND nchmpc = 'mode_frais') THEN
		INSERT INTO d_tableliste(ident, tablen, nchmpc, nchmpl, isreq, ref_field, type, onslct, ispkey, traduit) VALUES ((select max(ident) from d_tableliste)+1, tableliste_ident, 'mode_frais', maketraductionlangsyst('Mode frais'), true, NULL, 'lsb', false, false, false);

		RAISE NOTICE 'Insertion mode_frais de la table d_tableliste effectuée';
		output_result := 2;
	END IF;

	IF NOT EXISTS(SELECT * FROM d_tableliste WHERE tablen=tableliste_ident AND nchmpc = 'valeur') THEN
		INSERT INTO d_tableliste(ident, tablen, nchmpc, nchmpl, isreq, ref_field, type, onslct, ispkey, traduit) VALUES ((select max(ident) from d_tableliste)+1, tableliste_ident, 'valeur', maketraductionlangsyst('Valeur'), true, NULL, 'flt', false, false, false);

		RAISE NOTICE 'Insertion valeur de la table d_tableliste effectuée';
		output_result := 2;
	END IF;

	IF NOT EXISTS(SELECT * FROM d_tableliste WHERE tablen=tableliste_ident AND nchmpc = 'compte_comptable') THEN
		INSERT INTO d_tableliste(ident, tablen, nchmpc, nchmpl, isreq, ref_field, type, onslct, ispkey, traduit) VALUES ((select max(ident) from d_tableliste)+1, tableliste_ident, 'compte_comptable', maketraductionlangsyst('Compte comptable'), true, (select ident from d_tableliste where nchmpc like 'num_cpte_comptable' limit 1), 'txt', false, false, false);

		RAISE NOTICE 'Insertion compte_comptable de la table d_tableliste effectuée';
		output_result := 2;
	END IF;

	IF NOT EXISTS(SELECT * FROM d_tableliste WHERE tablen=tableliste_ident AND nchmpc = 'date_debut_validite') THEN
		INSERT INTO d_tableliste(ident, tablen, nchmpc, nchmpl, isreq, ref_field, type, onslct, ispkey, traduit) VALUES ((select max(ident) from d_tableliste)+1, tableliste_ident, 'date_debut_validite', maketraductionlangsyst('Date début validité'), false, NULL, 'dtg', false, false, false);

		RAISE NOTICE 'Insertion date_debut_validite de la table d_tableliste effectuée';
		output_result := 2;
	END IF;

	IF NOT EXISTS(SELECT * FROM d_tableliste WHERE tablen=tableliste_ident AND nchmpc = 'date_fin_validite') THEN
		INSERT INTO d_tableliste(ident, tablen, nchmpc, nchmpl, isreq, ref_field, type, onslct, ispkey, traduit) VALUES ((select max(ident) from d_tableliste)+1, tableliste_ident, 'date_fin_validite', maketraductionlangsyst('Date fin validité'), false, NULL, 'dtg', false, false, false);

		RAISE NOTICE 'Insertion date_fin_validite de la table d_tableliste effectuée';
		output_result := 2;
	END IF;

	IF NOT EXISTS(SELECT * FROM d_tableliste WHERE tablen=tableliste_ident AND nchmpc = 'statut') THEN
		INSERT INTO d_tableliste(ident, tablen, nchmpc, nchmpl, isreq, ref_field, type, onslct, ispkey, traduit) VALUES ((select max(ident) from d_tableliste)+1, tableliste_ident, 'statut', maketraductionlangsyst('Actif?'), false, NULL, 'bol', false, false, false);

		RAISE NOTICE 'Insertion statut de la table d_tableliste effectuée';
		output_result := 2;
	END IF;
	
	-- Truncate table tarification
	IF (SELECT COUNT(*) FROM adsys_tarification) > 0 THEN
	
		-- Empty table
		TRUNCATE TABLE adsys_tarification RESTART IDENTITY CASCADE;
		
		RAISE NOTICE 'Truncate table adsys_tarification effectuée';
		output_result := 2;
	END IF;

	-- ----------------------------
	-- Records of adsys_tarification
	-- ----------------------------
	INSERT INTO adsys_tarification (id_tarification, code_abonnement, type_de_frais, mode_frais, valeur, compte_comptable, date_debut_validite, date_fin_validite, statut, id_ag) VALUES (1, 'sms', 'SMS_REG', '1', '0', null, null, null, 'f', numagc());
	INSERT INTO adsys_tarification (id_tarification, code_abonnement, type_de_frais, mode_frais, valeur, compte_comptable, date_debut_validite, date_fin_validite, statut, id_ag) VALUES (2, 'sms', 'SMS_MTH', '1', '0', null, null, null, 'f', numagc());
	INSERT INTO adsys_tarification (id_tarification, code_abonnement, type_de_frais, mode_frais, valeur, compte_comptable, date_debut_validite, date_fin_validite, statut, id_ag) VALUES (3, 'sms', 'SMS_TRC', '1', '0', null, null, null, 'f', numagc());
	INSERT INTO adsys_tarification (id_tarification, code_abonnement, type_de_frais, mode_frais, valeur, compte_comptable, date_debut_validite, date_fin_validite, statut, id_ag) VALUES (4, 'sms', 'SMS_EWT', '1', '0', null, null, null, 'f', numagc());
	INSERT INTO adsys_tarification (id_tarification, code_abonnement, type_de_frais, mode_frais, valeur, compte_comptable, date_debut_validite, date_fin_validite, statut, id_ag) VALUES (5, 'estatement', 'ESTAT_REG', '1', '0', null, null, null, 'f', numagc());
	INSERT INTO adsys_tarification (id_tarification, code_abonnement, type_de_frais, mode_frais, valeur, compte_comptable, date_debut_validite, date_fin_validite, statut, id_ag) VALUES (6, 'estatement', 'ESTAT_MTH', '1', '0', null, null, null, 'f', numagc());

	RAISE NOTICE 'Insertion données dans la table adsys_tarification effectuée';

	-- Création opération financière
	IF NOT EXISTS(SELECT * FROM ad_cpt_ope WHERE type_operation=180 AND categorie_ope = 1 AND id_ag = numagc()) THEN
		-- Frais d'activation du service SMS
		INSERT INTO ad_cpt_ope(type_operation, categorie_ope, id_ag, libel_ope) 
		VALUES (180, 1, numagc(), maketraductionlangsyst('Frais d''activation du service SMS'));
	
		RAISE NOTICE 'Insertion type_operation 180 dans la table ad_cpt_ope effectuée';
		output_result := 2;
	END IF;

	IF NOT EXISTS(SELECT * FROM ad_cpt_ope_cptes WHERE type_operation=180 AND sens = 'd' AND categorie_cpte = 1 AND id_ag = numagc()) THEN
		INSERT INTO ad_cpt_ope_cptes(type_operation, num_cpte, sens, categorie_cpte, id_ag) VALUES (180, NULL, 'd', 1, numagc());
	
		RAISE NOTICE 'Insertion type_operation 180 sens ''d'' dans la table ad_cpt_ope_cptes effectuée';
		output_result := 2;
	END IF;

	IF NOT EXISTS(SELECT * FROM ad_cpt_ope_cptes WHERE type_operation=180 AND sens = 'c' AND categorie_cpte = 0 AND id_ag = numagc()) THEN
		INSERT INTO ad_cpt_ope_cptes(type_operation, num_cpte, sens, categorie_cpte, id_ag) VALUES (180, NULL, 'c', 0, numagc());
	
		RAISE NOTICE 'Insertion type_operation 180 sens ''c'' dans la table ad_cpt_ope_cptes effectuée';
		output_result := 2;
	END IF;

	IF NOT EXISTS(SELECT * FROM ad_cpt_ope WHERE type_operation=181 AND categorie_ope = 1 AND id_ag = numagc()) THEN
		-- Frais forfaitaires mensuels SMS
		INSERT INTO ad_cpt_ope(type_operation, categorie_ope, id_ag, libel_ope) 
		VALUES (181, 1, numagc(), maketraductionlangsyst('Frais forfaitaires mensuels SMS'));
	
		RAISE NOTICE 'Insertion type_operation 181 dans la table ad_cpt_ope effectuée';
		output_result := 2;
	END IF;

	IF NOT EXISTS(SELECT * FROM ad_cpt_ope_cptes WHERE type_operation=181 AND sens = 'd' AND categorie_cpte = 1 AND id_ag = numagc()) THEN
		INSERT INTO ad_cpt_ope_cptes(type_operation, num_cpte, sens, categorie_cpte, id_ag) VALUES (181, NULL, 'd', 1, numagc());
	
		RAISE NOTICE 'Insertion type_operation 181 sens ''d'' dans la table ad_cpt_ope_cptes effectuée';
		output_result := 2;
	END IF;

	IF NOT EXISTS(SELECT * FROM ad_cpt_ope_cptes WHERE type_operation=181 AND sens = 'c' AND categorie_cpte = 0 AND id_ag = numagc()) THEN
		INSERT INTO ad_cpt_ope_cptes(type_operation, num_cpte, sens, categorie_cpte, id_ag) VALUES (181, NULL, 'c', 0, numagc());
	
		RAISE NOTICE 'Insertion type_operation 181 sens ''c'' dans la table ad_cpt_ope_cptes effectuée';
		output_result := 2;
	END IF;

	IF NOT EXISTS(SELECT * FROM ad_cpt_ope WHERE type_operation=182 AND categorie_ope = 1 AND id_ag = numagc()) THEN
		-- Frais transfert de compte à compte
		INSERT INTO ad_cpt_ope(type_operation, categorie_ope, id_ag, libel_ope) 
		VALUES (182, 1, numagc(), maketraductionlangsyst('Frais transfert de compte à compte'));
	
		RAISE NOTICE 'Insertion type_operation 182 dans la table ad_cpt_ope effectuée';
		output_result := 2;
	END IF;

	IF NOT EXISTS(SELECT * FROM ad_cpt_ope_cptes WHERE type_operation=182 AND sens = 'd' AND categorie_cpte = 1 AND id_ag = numagc()) THEN
		INSERT INTO ad_cpt_ope_cptes(type_operation, num_cpte, sens, categorie_cpte, id_ag) VALUES (182, NULL, 'd', 1, numagc());
	
		RAISE NOTICE 'Insertion type_operation 182 sens ''d'' dans la table ad_cpt_ope_cptes effectuée';
		output_result := 2;
	END IF;

	IF NOT EXISTS(SELECT * FROM ad_cpt_ope_cptes WHERE type_operation=182 AND sens = 'c' AND categorie_cpte = 0 AND id_ag = numagc()) THEN
		INSERT INTO ad_cpt_ope_cptes(type_operation, num_cpte, sens, categorie_cpte, id_ag) VALUES (182, NULL, 'c', 0, numagc());
	
		RAISE NOTICE 'Insertion type_operation 182 sens ''c'' dans la table ad_cpt_ope_cptes effectuée';
		output_result := 2;
	END IF;

	IF NOT EXISTS(SELECT * FROM ad_cpt_ope WHERE type_operation=183 AND categorie_ope = 1 AND id_ag = numagc()) THEN
		-- Frais transfert E-wallet
		INSERT INTO ad_cpt_ope(type_operation, categorie_ope, id_ag, libel_ope) 
		VALUES (183, 1, numagc(), maketraductionlangsyst('Frais transfert E-wallet'));
	
		RAISE NOTICE 'Insertion type_operation 183 dans la table ad_cpt_ope effectuée';
		output_result := 2;
	END IF;

	IF NOT EXISTS(SELECT * FROM ad_cpt_ope_cptes WHERE type_operation=183 AND sens = 'd' AND categorie_cpte = 1 AND id_ag = numagc()) THEN
		INSERT INTO ad_cpt_ope_cptes(type_operation, num_cpte, sens, categorie_cpte, id_ag) VALUES (183, NULL, 'd', 1, numagc());
	
		RAISE NOTICE 'Insertion type_operation 183 sens ''d'' dans la table ad_cpt_ope_cptes effectuée';
		output_result := 2;
	END IF;

	IF NOT EXISTS(SELECT * FROM ad_cpt_ope_cptes WHERE type_operation=183 AND sens = 'c' AND categorie_cpte = 0 AND id_ag = numagc()) THEN
		INSERT INTO ad_cpt_ope_cptes(type_operation, num_cpte, sens, categorie_cpte, id_ag) VALUES (183, NULL, 'c', 0, numagc());
	
		RAISE NOTICE 'Insertion type_operation 183 sens ''c'' dans la table ad_cpt_ope_cptes effectuée';
		output_result := 2;
	END IF;

	IF NOT EXISTS(SELECT * FROM ad_cpt_ope WHERE type_operation=184 AND categorie_ope = 1 AND id_ag = numagc()) THEN
		-- Frais transfert E-wallet vers ADBanking
		INSERT INTO ad_cpt_ope(type_operation, categorie_ope, id_ag, libel_ope) 
		VALUES (184, 1, numagc(), maketraductionlangsyst('Frais transfert E-wallet vers ADBanking'));
	
		RAISE NOTICE 'Insertion type_operation 184 dans la table ad_cpt_ope effectuée';
		output_result := 2;
	END IF;

	IF NOT EXISTS(SELECT * FROM ad_cpt_ope_cptes WHERE type_operation=184 AND sens = 'd' AND categorie_cpte = 1 AND id_ag = numagc()) THEN
		INSERT INTO ad_cpt_ope_cptes(type_operation, num_cpte, sens, categorie_cpte, id_ag) VALUES (184, NULL, 'd', 1, numagc());
	
		RAISE NOTICE 'Insertion type_operation 184 sens ''d'' dans la table ad_cpt_ope_cptes effectuée';
		output_result := 2;
	END IF;

	IF NOT EXISTS(SELECT * FROM ad_cpt_ope_cptes WHERE type_operation=184 AND sens = 'c' AND categorie_cpte = 0 AND id_ag = numagc()) THEN
		INSERT INTO ad_cpt_ope_cptes(type_operation, num_cpte, sens, categorie_cpte, id_ag) VALUES (184, NULL, 'c', 0, numagc());
	
		RAISE NOTICE 'Insertion type_operation 184 sens ''c'' dans la table ad_cpt_ope_cptes effectuée';
		output_result := 2;
	END IF;

	IF NOT EXISTS(SELECT * FROM ad_cpt_ope WHERE type_operation=185 AND categorie_ope = 1 AND id_ag = numagc()) THEN
		-- Frais d'activation du service ESTATEMENT
		INSERT INTO ad_cpt_ope(type_operation, categorie_ope, id_ag, libel_ope) 
		VALUES (185, 1, numagc(), maketraductionlangsyst('Frais d''activation du service ESTATEMENT'));
	
		RAISE NOTICE 'Insertion type_operation 185 dans la table ad_cpt_ope effectuée';
		output_result := 2;
	END IF;

	IF NOT EXISTS(SELECT * FROM ad_cpt_ope_cptes WHERE type_operation=185 AND sens = 'd' AND categorie_cpte = 1 AND id_ag = numagc()) THEN
		INSERT INTO ad_cpt_ope_cptes(type_operation, num_cpte, sens, categorie_cpte, id_ag) VALUES (185, NULL, 'd', 1, numagc());
	
		RAISE NOTICE 'Insertion type_operation 185 sens ''d'' dans la table ad_cpt_ope_cptes effectuée';
		output_result := 2;
	END IF;

	IF NOT EXISTS(SELECT * FROM ad_cpt_ope_cptes WHERE type_operation=185 AND sens = 'c' AND categorie_cpte = 0 AND id_ag = numagc()) THEN
		INSERT INTO ad_cpt_ope_cptes(type_operation, num_cpte, sens, categorie_cpte, id_ag) VALUES (185, NULL, 'c', 0, numagc());
	
		RAISE NOTICE 'Insertion type_operation 185 sens ''c'' dans la table ad_cpt_ope_cptes effectuée';
		output_result := 2;
	END IF;

	IF NOT EXISTS(SELECT * FROM ad_cpt_ope WHERE type_operation=186 AND categorie_ope = 1 AND id_ag = numagc()) THEN
		-- Frais forfaitaires mensuels ESTATEMENT
		INSERT INTO ad_cpt_ope(type_operation, categorie_ope, id_ag, libel_ope) 
		VALUES (186, 1, numagc(), maketraductionlangsyst('Frais forfaitaires mensuels ESTATEMENT'));
	
		RAISE NOTICE 'Insertion type_operation 186 dans la table ad_cpt_ope effectuée';
		output_result := 2;
	END IF;

	IF NOT EXISTS(SELECT * FROM ad_cpt_ope_cptes WHERE type_operation=186 AND sens = 'd' AND categorie_cpte = 1 AND id_ag = numagc()) THEN
		INSERT INTO ad_cpt_ope_cptes(type_operation, num_cpte, sens, categorie_cpte, id_ag) VALUES (186, NULL, 'd', 1, numagc());
	
		RAISE NOTICE 'Insertion type_operation 186 sens ''d'' dans la table ad_cpt_ope_cptes effectuée';
		output_result := 2;
	END IF;

	IF NOT EXISTS(SELECT * FROM ad_cpt_ope_cptes WHERE type_operation=186 AND sens = 'c' AND categorie_cpte = 0 AND id_ag = numagc()) THEN
		INSERT INTO ad_cpt_ope_cptes(type_operation, num_cpte, sens, categorie_cpte, id_ag) VALUES (186, NULL, 'c', 0, numagc());
	
		RAISE NOTICE 'Insertion type_operation 186 sens ''c'' dans la table ad_cpt_ope_cptes effectuée';
		output_result := 2;
	END IF;
	
	RAISE NOTICE 'END';

	RETURN output_result;

END;
$$
LANGUAGE plpgsql;

SELECT patch_tarification();
DROP FUNCTION patch_tarification();

---------------------------------------------------------------------------------------------------------------------

-- Function: maj_horodatage()

-- DROP FUNCTION maj_horodatage();

CREATE OR REPLACE FUNCTION maj_horodatage()
  RETURNS trigger AS
$BODY$
BEGIN

  IF (TG_OP = 'UPDATE') THEN
	NEW.date_modif := current_timestamp::varchar(23)::timestamp;
  END IF;

  RETURN NEW;
END;
$BODY$
  LANGUAGE plpgsql VOLATILE
  COST 100;
ALTER FUNCTION maj_horodatage()
  OWNER TO adbanking;

-- Function: ins_maj_horodatage()

-- DROP FUNCTION ins_maj_horodatage();

CREATE OR REPLACE FUNCTION ins_maj_horodatage()
  RETURNS trigger AS
$BODY$

BEGIN
  IF (TG_OP = 'UPDATE') THEN
	
	UPDATE ad_cli c
	set date_modif=((now())::character varying(23))::timestamp without time zone
	from ad_abonnement
	where c.id_client = new.id_client
	and c.id_ag = new.id_ag ;
  END IF;

 IF (TG_OP = 'INSERT') THEN
	
	UPDATE ad_cli c
	set date_modif=((now())::character varying(23))::timestamp without time zone
	from ad_abonnement
	where c.id_client = new.id_client
	and c.id_ag = new.id_ag ;
  END IF;
  
 return new;
  
END;

$BODY$
  LANGUAGE plpgsql VOLATILE
  COST 100;
ALTER FUNCTION ins_maj_horodatage()
  OWNER TO adbanking;
  
---------------------------------------------------------------------------------------------------------------------

CREATE OR REPLACE FUNCTION patch_create_ewallet() RETURNS INT AS
$$
DECLARE
output_result INTEGER = 1;
tableliste_ident INTEGER = 0;

BEGIN

	RAISE NOTICE 'START';

	IF NOT EXISTS(SELECT * FROM information_schema.tables WHERE table_name = 'ad_ewallet') THEN
		CREATE TABLE ad_ewallet
		(
		  id_prestataire serial NOT NULL,
		  id_ag integer NOT NULL,
		  code_prestataire character varying(100) NOT NULL,
		  nom_prestataire character varying(100) NOT NULL,
		  compte_comptable character varying(100) NULL,
		  CONSTRAINT ad_prestataire_pkey PRIMARY KEY (id_prestataire, id_ag)
		)
		WITH (
		  OIDS=FALSE
		);

		RAISE NOTICE 'Table ad_ewallet created';
		output_result := 2;
	END IF;
	
	-- Truncate table ad_ewallet
	--IF (SELECT COUNT(*) FROM ad_ewallet) > 0 THEN
	
		-- Empty table
		--TRUNCATE TABLE ad_ewallet RESTART IDENTITY CASCADE;
		
		--RAISE NOTICE 'Truncate table ad_ewallet effectuée';
		--output_result := 2;
	--END IF;

	-- ----------------------------
	-- Records of ad_ewallet
	-- ----------------------------
	-- Rwanda
	IF NOT EXISTS(SELECT * FROM ad_ewallet WHERE nom_prestataire='Tigo' AND code_prestataire='TIGO_RW' AND id_ag = numagc()) THEN
		INSERT INTO ad_ewallet (id_prestataire, id_ag, nom_prestataire, code_prestataire, compte_comptable) VALUES (1, numagc(), 'Tigo', 'TIGO_RW', null);
		output_result := 2;
	END IF;
	IF NOT EXISTS(SELECT * FROM ad_ewallet WHERE nom_prestataire='MTN' AND code_prestataire='MTN_RW' AND id_ag = numagc()) THEN
		INSERT INTO ad_ewallet (id_prestataire, id_ag, nom_prestataire, code_prestataire, compte_comptable) VALUES (2, numagc(), 'MTN', 'MTN_RW', null);
		output_result := 2;
	END IF;
	IF NOT EXISTS(SELECT * FROM ad_ewallet WHERE nom_prestataire='Airtel' AND code_prestataire='AIRTEL_RW' AND id_ag = numagc()) THEN
		INSERT INTO ad_ewallet (id_prestataire, id_ag, nom_prestataire, code_prestataire, compte_comptable) VALUES (3, numagc(), 'Airtel', 'AIRTEL_RW', null);
		output_result := 2;
	END IF;

	-- Burundi
	--INSERT INTO ad_ewallet (id_ag, nom_prestataire, code_prestataire, compte_comptable) VALUES (numagc(), 'Spacetel', 'SPACETEL_BI', null);
	--INSERT INTO ad_ewallet (id_ag, nom_prestataire, code_prestataire, compte_comptable) VALUES (numagc(), 'Africell', 'AFRICELL_BI', null);
	--INSERT INTO ad_ewallet (id_ag, nom_prestataire, code_prestataire, compte_comptable) VALUES (numagc(), 'Onatel', 'ONATEL_BI', null);
	--INSERT INTO ad_ewallet (id_ag, nom_prestataire, code_prestataire, compte_comptable) VALUES (numagc(), 'Smart Mobile', 'SMART_BI', null);
	--INSERT INTO ad_ewallet (id_ag, nom_prestataire, code_prestataire, compte_comptable) VALUES (numagc(), 'Leo Orascom', 'ORASCOM_BI', null);

	-- Burkina Faso
	--INSERT INTO ad_ewallet (id_ag, nom_prestataire, code_prestataire, compte_comptable) VALUES (numagc(), 'Telmob', 'TELMOB_BF', null);
	--INSERT INTO ad_ewallet (id_ag, nom_prestataire, code_prestataire, compte_comptable) VALUES (numagc(), 'Telecel Faso', 'TELECEL_BF', null);
	--INSERT INTO ad_ewallet (id_ag, nom_prestataire, code_prestataire, compte_comptable) VALUES (numagc(), 'Airtel', 'AIRTEL_BF', null);

	-- Comoros
	--INSERT INTO ad_ewallet (id_ag, nom_prestataire, code_prestataire, compte_comptable) VALUES (numagc(), 'HURI - SNPT', 'HURI_KM', null);

	-- Senegal
	--INSERT INTO ad_ewallet (id_ag, nom_prestataire, code_prestataire, compte_comptable) VALUES (numagc(), 'Orange', 'ORANGE_SN', null);
	--INSERT INTO ad_ewallet (id_ag, nom_prestataire, code_prestataire, compte_comptable) VALUES (numagc(), 'Tigo', 'TIGO_SN', null);
	--INSERT INTO ad_ewallet (id_ag, nom_prestataire, code_prestataire, compte_comptable) VALUES (numagc(), 'Expresso', 'EXPRESSO_SN', null);

	-- Niger
	--INSERT INTO ad_ewallet (id_ag, nom_prestataire, code_prestataire, compte_comptable) VALUES (numagc(), 'SahelCom', 'SAHELCOM_NE', null);
	--INSERT INTO ad_ewallet (id_ag, nom_prestataire, code_prestataire, compte_comptable) VALUES (numagc(), 'Airtel', 'AIRTEL_NE', null);
	--INSERT INTO ad_ewallet (id_ag, nom_prestataire, code_prestataire, compte_comptable) VALUES (numagc(), 'Moov', 'MOOV_NE', null);
	--INSERT INTO ad_ewallet (id_ag, nom_prestataire, code_prestataire, compte_comptable) VALUES (numagc(), 'Orange', 'ORANGE_NE', null);

	RAISE NOTICE 'Insertion données dans la table ad_ewallet effectuée';


	IF NOT EXISTS(SELECT * FROM tableliste WHERE nomc = 'ad_ewallet') THEN
		INSERT INTO tableliste(ident, nomc, noml, is_table) VALUES ((select max(ident) from tableliste)+1, 'ad_ewallet', maketraductionlangsyst('Table eWallet'), true);
		
		RAISE NOTICE 'Insertion table ad_ewallet de la table tableliste effectuée';
		output_result := 2;
	END IF;

	tableliste_ident := (select ident from tableliste where nomc like 'ad_ewallet' order by ident desc limit 1);

	IF NOT EXISTS(SELECT * FROM d_tableliste WHERE tablen=tableliste_ident AND nchmpc = 'id_prestataire') THEN
		INSERT INTO d_tableliste(ident, tablen, nchmpc, nchmpl, isreq, ref_field, type, onslct, ispkey, traduit) VALUES ((select max(ident) from d_tableliste)+1, tableliste_ident, 'id_prestataire', maketraductionlangsyst('Identifiant table eWallet'), true, NULL, 'int', false, true, false);

		RAISE NOTICE 'Insertion id_prestataire de la table d_tableliste effectuée';
		output_result := 2;
	END IF;

	IF NOT EXISTS(SELECT * FROM d_tableliste WHERE tablen=tableliste_ident AND nchmpc = 'code_prestataire') THEN
		INSERT INTO d_tableliste(ident, tablen, nchmpc, nchmpl, isreq, ref_field, type, onslct, ispkey, traduit) VALUES ((select max(ident) from d_tableliste)+1, tableliste_ident, 'code_prestataire', maketraductionlangsyst('Code prestataire'), true, NULL, 'txt', false, false, false);

		RAISE NOTICE 'Insertion code_prestataire de la table d_tableliste effectuée';
		output_result := 2;
	END IF;

	IF NOT EXISTS(SELECT * FROM d_tableliste WHERE tablen=tableliste_ident AND nchmpc = 'nom_prestataire') THEN
		INSERT INTO d_tableliste(ident, tablen, nchmpc, nchmpl, isreq, ref_field, type, onslct, ispkey, traduit) VALUES ((select max(ident) from d_tableliste)+1, tableliste_ident, 'nom_prestataire', maketraductionlangsyst('Nom prestataire'), true, NULL, 'txt', false, false, false);

		RAISE NOTICE 'Insertion nom_prestataire de la table d_tableliste effectuée';
		output_result := 2;
	END IF;

	IF NOT EXISTS(SELECT * FROM d_tableliste WHERE tablen=tableliste_ident AND nchmpc = 'compte_comptable') THEN
		INSERT INTO d_tableliste(ident, tablen, nchmpc, nchmpl, isreq, ref_field, type, onslct, ispkey, traduit) VALUES ((select max(ident) from d_tableliste)+1, tableliste_ident, 'compte_comptable', maketraductionlangsyst('Compte comptable'), true, (select ident from d_tableliste where nchmpc like 'num_cpte_comptable' limit 1), 'txt', false, false, false);

		RAISE NOTICE 'Insertion compte_comptable de la table d_tableliste effectuée';
		output_result := 2;
	END IF;
	
	RAISE NOTICE 'END';

	RETURN output_result;

END;
$$
LANGUAGE plpgsql;
  
SELECT patch_create_ewallet();
DROP FUNCTION patch_create_ewallet();

---------------------------------------------------------------------------------------------------------------------

-- #354
CREATE OR REPLACE FUNCTION patch_abonnement() RETURNS INT AS
$$
DECLARE
	output_result INTEGER = 1;

BEGIN

	RAISE NOTICE 'START';
	
	-- ALTER TABLES
	--table ad_cli
	--ALTER TABLE ad_cli DROP COLUMN date_creation, DROP COLUMN date_modif;
	IF NOT EXISTS(SELECT column_name FROM information_schema.columns WHERE table_name = 'ad_cli' AND column_name = 'date_creation') THEN
		ALTER TABLE ad_cli ADD COLUMN date_creation timestamp without time zone NOT NULL DEFAULT ((now())::character varying(23))::timestamp without time zone;
		output_result := 2;
	END IF;

	IF NOT EXISTS(SELECT column_name FROM information_schema.columns WHERE table_name = 'ad_cli' AND column_name = 'date_modif') THEN
		ALTER TABLE ad_cli ADD COLUMN date_modif timestamp without time zone;
		update ad_cli set date_creation = coalesce(date_crea,((now())::character varying(23))::timestamp without time zone);
		output_result := 2;
	END IF;

	--table ad_cpt
	--ALTER TABLE ad_cpt DROP COLUMN date_creation, DROP COLUMN date_modif;
	IF NOT EXISTS(SELECT column_name FROM information_schema.columns WHERE table_name = 'ad_cpt' AND column_name = 'date_creation') THEN
		ALTER TABLE ad_cpt ADD COLUMN date_creation timestamp without time zone NOT NULL DEFAULT ((now())::character varying(23))::timestamp without time zone;
		output_result := 2;
	END IF;

	IF NOT EXISTS(SELECT column_name FROM information_schema.columns WHERE table_name = 'ad_cpt' AND column_name = 'date_modif') THEN
		ALTER TABLE ad_cpt ADD COLUMN date_modif timestamp without time zone;
		update ad_cpt set date_creation = coalesce(date_ouvert,((now())::character varying(23))::timestamp without time zone);
		output_result := 2;
	END IF;

	--table ad_dcr
	--ALTER TABLE ad_dcr DROP COLUMN date_creation, DROP COLUMN date_modif;
	IF NOT EXISTS(SELECT column_name FROM information_schema.columns WHERE table_name = 'ad_dcr' AND column_name = 'date_creation') THEN
		ALTER TABLE ad_dcr ADD COLUMN date_creation timestamp without time zone NOT NULL DEFAULT ((now())::character varying(23))::timestamp without time zone;
		output_result := 2;
	END IF;

	IF NOT EXISTS(SELECT column_name FROM information_schema.columns WHERE table_name = 'ad_dcr' AND column_name = 'date_modif') THEN
		ALTER TABLE ad_dcr ADD COLUMN date_modif timestamp without time zone;
		update ad_dcr set date_creation = coalesce(date_dem,((now())::character varying(23))::timestamp without time zone);
		output_result := 2;
	END IF;

	--table ad_etr
	--ALTER TABLE ad_etr DROP COLUMN date_creation, DROP COLUMN date_modif;
	IF NOT EXISTS(SELECT column_name FROM information_schema.columns WHERE table_name = 'ad_etr' AND column_name = 'date_creation') THEN
		ALTER TABLE ad_etr ADD COLUMN date_creation timestamp without time zone NOT NULL DEFAULT ((now())::character varying(23))::timestamp without time zone;
		output_result := 2;
	END IF;

	IF NOT EXISTS(SELECT column_name FROM information_schema.columns WHERE table_name = 'ad_etr' AND column_name = 'date_modif') THEN
		ALTER TABLE ad_etr ADD COLUMN date_modif timestamp without time zone;
		update ad_etr etr set date_creation = (select coalesce(cre_date_debloc,((now())::character varying(23))::timestamp without time zone) from ad_dcr dcr where dcr.id_doss=etr.id_doss and dcr.id_ag=etr.id_ag);
		output_result := 2;
	END IF;

	--table ad_sre
	--ALTER TABLE ad_sre DROP COLUMN date_creation, DROP COLUMN date_modif;
	IF NOT EXISTS(SELECT column_name FROM information_schema.columns WHERE table_name = 'ad_sre' AND column_name = 'date_creation') THEN
		ALTER TABLE ad_sre ADD COLUMN date_creation timestamp without time zone NOT NULL DEFAULT ((now())::character varying(23))::timestamp without time zone;
		output_result := 2;
	END IF;

	IF NOT EXISTS(SELECT column_name FROM information_schema.columns WHERE table_name = 'ad_sre' AND column_name = 'date_modif') THEN
		ALTER TABLE ad_sre ADD COLUMN date_modif timestamp without time zone;
		update ad_sre set date_creation = coalesce(date_remb,((now())::character varying(23))::timestamp without time zone);
		output_result := 2;
	END IF;

	--table adsys_localisation
	--ALTER TABLE adsys_localisation DROP COLUMN date_creation, DROP COLUMN date_modif;
	IF NOT EXISTS(SELECT column_name FROM information_schema.columns WHERE table_name = 'adsys_localisation' AND column_name = 'date_creation') THEN
		ALTER TABLE adsys_localisation ADD COLUMN date_creation timestamp without time zone NOT NULL DEFAULT ((now())::character varying(23))::timestamp without time zone;
		output_result := 2;
	END IF;

	IF NOT EXISTS(SELECT column_name FROM information_schema.columns WHERE table_name = 'adsys_localisation' AND column_name = 'date_modif') THEN
		ALTER TABLE adsys_localisation ADD COLUMN date_modif timestamp without time zone;
		update adsys_localisation set date_creation = ((now())::character varying(23))::timestamp without time zone;
		output_result := 2;
	END IF;

	--table adsys_sect_activite
	--ALTER TABLE adsys_sect_activite DROP COLUMN date_creation, DROP COLUMN date_modif;
	IF NOT EXISTS(SELECT column_name FROM information_schema.columns WHERE table_name = 'adsys_sect_activite' AND column_name = 'date_creation') THEN
		ALTER TABLE adsys_sect_activite ADD COLUMN date_creation timestamp without time zone NOT NULL DEFAULT ((now())::character varying(23))::timestamp without time zone;
		output_result := 2;
	END IF;

	IF NOT EXISTS(SELECT column_name FROM information_schema.columns WHERE table_name = 'adsys_sect_activite' AND column_name = 'date_modif') THEN
		ALTER TABLE adsys_sect_activite ADD COLUMN date_modif timestamp without time zone;
		update adsys_sect_activite set date_creation = ((now())::character varying(23))::timestamp without time zone;
		output_result := 2;
	END IF;

	--table ad_chequier
	--ALTER TABLE ad_chequier DROP COLUMN date_creation, DROP COLUMN date_modif;
	IF NOT EXISTS(SELECT column_name FROM information_schema.columns WHERE table_name = 'ad_chequier' AND column_name = 'date_creation') THEN
		ALTER TABLE ad_chequier ADD COLUMN date_creation timestamp without time zone NOT NULL DEFAULT ((now())::character varying(23))::timestamp without time zone;
		output_result := 2;
	END IF;

	IF NOT EXISTS(SELECT column_name FROM information_schema.columns WHERE table_name = 'ad_chequier' AND column_name = 'date_modif') THEN
		ALTER TABLE ad_chequier ADD COLUMN date_modif timestamp without time zone;
		update ad_chequier set date_creation = coalesce(date_livraison,((now())::character varying(23))::timestamp without time zone);
		output_result := 2;
	END IF;

	-- Create login api
	IF NOT EXISTS(SELECT login FROM ad_log WHERE login='api') THEN

		INSERT INTO ad_log(login, pwd, profil, guichet, id_utilisateur, have_left_frame, billet_req, langue, id_ag) VALUES ('api', md5('api'), 1, NULL, 1, 't', 'f', 'fr_BE', (SELECT numagc()));

		output_result := 2;		
	END IF;

	-- Table ad_abonnement
	IF EXISTS(SELECT * FROM information_schema.tables WHERE table_name = 'ad_abonnement') THEN
	
		-- Trigger: maj_horodatage on ad_abonnement
		IF EXISTS(SELECT * FROM information_schema.triggers WHERE event_object_table = 'ad_abonnement' AND trigger_name = 'maj_horodatage') THEN
			DROP TRIGGER IF EXISTS maj_horodatage ON ad_abonnement;
		END IF;

		-- Trigger: ins_maj_horodatage on ad_abonnement
		IF EXISTS(SELECT * FROM information_schema.triggers WHERE event_object_table = 'ad_abonnement' AND trigger_name = 'ins_maj_horodatage') THEN
			DROP TRIGGER IF EXISTS ins_maj_horodatage ON ad_abonnement;
		END IF;
	
		-- DROP table ad_abonnement
		DROP TABLE ad_abonnement;

		RAISE NOTICE 'DROP table ad_abonnement';
		output_result := 2;
	END IF;

	-- Create Table ad_abonnement
	CREATE TABLE ad_abonnement
	(
	  id_abonnement serial NOT NULL,
	  id_client integer NOT NULL,
	  id_ag integer NOT NULL,
	  identifiant character varying(255) NOT NULL,
	  motdepasse character varying(255),
	  salt character varying(255),
	  num_sms character varying(50),
	  langue integer NOT NULL DEFAULT 1,
	  ewallet boolean DEFAULT false,
	  id_prestataire integer,
	  id_service integer,
	  estatement_email character varying(50),
	  estatement_journalier boolean DEFAULT false,
	  estatement_hebdo boolean DEFAULT false,
	  estatement_mensuel boolean DEFAULT false,
	  date_mdp timestamp without time zone,
	  deleted boolean NOT NULL DEFAULT false,
	  date_creation timestamp without time zone NOT NULL DEFAULT ((now())::character varying(23))::timestamp without time zone,
	  date_modif timestamp without time zone,
	  CONSTRAINT ad_abonnement_pkey PRIMARY KEY (id_abonnement, id_ag),
	  CONSTRAINT ad_abonnement_id_client_fkey FOREIGN KEY (id_client, id_ag)
		  REFERENCES ad_cli (id_client, id_ag) MATCH SIMPLE
		  ON UPDATE NO ACTION ON DELETE NO ACTION,
	  CONSTRAINT ad_abonnement_id_pres_fkey FOREIGN KEY (id_prestataire, id_ag)
		  REFERENCES ad_ewallet (id_prestataire, id_ag) MATCH SIMPLE
		  ON UPDATE NO ACTION ON DELETE NO ACTION
	)
	WITH (
	  OIDS=FALSE
	);

	RAISE NOTICE 'Create table ad_abonnement';

	IF EXISTS(SELECT constraint_name FROM information_schema.constraint_table_usage WHERE table_name = 'ad_abonnement' AND constraint_name = 'ad_abonnement_ukey') THEN
		ALTER TABLE ad_abonnement DROP CONSTRAINT ad_abonnement_ukey;
		output_result := 2;
	END IF;

	IF NOT EXISTS(SELECT column_name FROM information_schema.columns WHERE table_name = 'ad_abonnement' AND column_name = 'deleted') THEN
		ALTER TABLE ad_abonnement ADD COLUMN deleted boolean NOT NULL DEFAULT false;
		output_result := 2;
	END IF;

	-- Menus
	IF NOT EXISTS(SELECT nom_menu FROM menus WHERE nom_menu='Abn') THEN
		INSERT INTO menus (nom_menu, libel_menu, nom_pere, pos_hierarch, ordre, is_menu,fonction, is_cliquable) VALUES ('Abn', maketraductionlangsyst('Gestion des abonnements'), 'Gen-9', 5, 10, true, 12, true);
	END IF;
	
	IF NOT EXISTS(SELECT nom_menu FROM menus WHERE nom_menu='Abn-1') THEN
		INSERT INTO menus (nom_menu, libel_menu, nom_pere, pos_hierarch, ordre, is_menu,fonction, is_cliquable) VALUES ('Abn-1', maketraductionlangsyst('Liste des abonnements'), 'Abn', 6, 1, false, 12, false);
	END IF;
	
	IF NOT EXISTS(SELECT nom_menu FROM menus WHERE nom_menu='Abn-2') THEN
		INSERT INTO menus (nom_menu, libel_menu, nom_pere, pos_hierarch, ordre, is_menu,fonction, is_cliquable)	VALUES ('Abn-2', maketraductionlangsyst('Réinitialisation mot de passe'), 'Abn', 6, 2, false, 12, false);
	END IF;
	
	IF NOT EXISTS(SELECT nom_menu FROM menus WHERE nom_menu='Abn-3') THEN
		INSERT INTO menus (nom_menu, libel_menu, nom_pere, pos_hierarch, ordre, is_menu,fonction, is_cliquable) VALUES ('Abn-3', maketraductionlangsyst('Inscripton abonnement'), 'Abn', 6, 3, false, 12, false);
	END IF;
	
	IF NOT EXISTS(SELECT nom_menu FROM menus WHERE nom_menu='Abn-4') THEN
		INSERT INTO menus (nom_menu, libel_menu, nom_pere, pos_hierarch, ordre, is_menu,fonction, is_cliquable)	VALUES ('Abn-4', maketraductionlangsyst('Modification abonnement'), 'Abn', 6, 4, false, 12, false);
	END IF;
	
	IF NOT EXISTS(SELECT nom_menu FROM menus WHERE nom_menu='Abn-5') THEN
		INSERT INTO menus (nom_menu, libel_menu, nom_pere, pos_hierarch, ordre, is_menu,fonction, is_cliquable) VALUES ('Abn-5', maketraductionlangsyst('Confirmation abonnement'), 'Abn', 6, 5, false, 12, false);
	END IF;	
	
	IF NOT EXISTS(SELECT nom_menu FROM menus WHERE nom_menu='Abn-6') THEN
		INSERT INTO menus (nom_menu, libel_menu, nom_pere, pos_hierarch, ordre, is_menu,fonction, is_cliquable) VALUES ('Abn-6', maketraductionlangsyst('Supprimer un abonnement'), 'Abn', 6, 6, false, 12, false);
	END IF;	
	
	-- Ecrans
	IF NOT EXISTS (SELECT nom_ecran FROM ecrans WHERE nom_ecran='Abn-1') THEN
		INSERT INTO ecrans(nom_ecran, fichier, nom_menu, fonction) VALUES ('Abn-1', 'modules/clients/abonnement.php', 'Abn-1', 12);
	END IF;
	
	IF NOT EXISTS (SELECT nom_ecran FROM ecrans WHERE nom_ecran='Abn-2') THEN
		INSERT INTO ecrans(nom_ecran, fichier, nom_menu, fonction) VALUES ('Abn-2', 'modules/clients/abonnement.php', 'Abn-2', 12);
	END IF;
	
	IF NOT EXISTS (SELECT nom_ecran FROM ecrans WHERE nom_ecran='Abn-3') THEN
		INSERT INTO ecrans(nom_ecran, fichier, nom_menu, fonction) VALUES ('Abn-3', 'modules/clients/abonnement.php', 'Abn-3', 12);
	END IF;
	
	IF NOT EXISTS (SELECT nom_ecran FROM ecrans WHERE nom_ecran='Abn-4') THEN
		INSERT INTO ecrans(nom_ecran, fichier, nom_menu, fonction) VALUES ('Abn-4', 'modules/clients/abonnement.php', 'Abn-4', 12);
	END IF;
	
	IF NOT EXISTS (SELECT nom_ecran FROM ecrans WHERE nom_ecran='Abn-5') THEN
		INSERT INTO ecrans(nom_ecran, fichier, nom_menu, fonction) VALUES ('Abn-5', 'modules/clients/abonnement.php', 'Abn-5', 12);
	END IF;
	
	IF NOT EXISTS (SELECT nom_ecran FROM ecrans WHERE nom_ecran='Abn-6') THEN
		INSERT INTO ecrans(nom_ecran, fichier, nom_menu, fonction) VALUES ('Abn-6', 'modules/clients/abonnement.php', 'Abn-6', 12);
	END IF;
	
	RAISE NOTICE 'END';

	RETURN output_result;

END;
$$
LANGUAGE plpgsql;
  
SELECT patch_abonnement();
DROP FUNCTION patch_abonnement();

---------------------------------------------------------------------------------------------------------------------

CREATE OR REPLACE FUNCTION patch_ebanking_transfert() RETURNS INT AS
$$
DECLARE
output_result INTEGER = 1;
tableliste_ident INTEGER = 0;

BEGIN

	RAISE NOTICE 'START';

	IF NOT EXISTS(SELECT * FROM information_schema.tables WHERE table_name = 'ad_ebanking_transfert') THEN
		CREATE TABLE ad_ebanking_transfert
		(
		  id_ebanking_transfert serial NOT NULL,
		  id_ag integer NOT NULL,
		  service character varying(50),
		  action character varying(50),
		  mnt_min numeric(30,6) DEFAULT 0,
		  mnt_max numeric(30,6) DEFAULT 0,
		  devise character(3),
		  date_creation timestamp without time zone NOT NULL DEFAULT ((now())::character varying(23))::timestamp without time zone,
          date_modif timestamp without time zone,
		  CONSTRAINT ad_ebanking_transfert_pkey PRIMARY KEY (id_ebanking_transfert, id_ag),
		  CONSTRAINT ad_ebanking_transfert_ukey UNIQUE (service, action, devise)
		)
		WITH (
		  OIDS=FALSE
		);
		
		-- Trigger: maj_horodatage on ad_ebanking_transfert

		DROP TRIGGER IF EXISTS maj_horodatage ON ad_ebanking_transfert;
		CREATE TRIGGER maj_horodatage
		  BEFORE UPDATE
		  ON ad_ebanking_transfert
		  FOR EACH ROW
		  EXECUTE PROCEDURE maj_horodatage();

		RAISE NOTICE 'Table ad_ebanking_transfert created';
		output_result := 2;
	END IF;

	IF NOT EXISTS(SELECT * FROM tableliste WHERE nomc = 'ad_ebanking_transfert') THEN
		INSERT INTO tableliste(ident, nomc, noml, is_table) VALUES ((select max(ident) from tableliste)+1, 'ad_ebanking_transfert', maketraductionlangsyst('Table eBanking Transfert'), true);
		
		RAISE NOTICE 'Insertion table ad_ebanking_transfert de la table tableliste effectuée';
		output_result := 2;
	END IF;

	tableliste_ident := (select ident from tableliste where nomc like 'ad_ebanking_transfert' order by ident desc limit 1);

	IF NOT EXISTS(SELECT * FROM d_tableliste WHERE tablen=tableliste_ident AND nchmpc = 'id_ebanking_transfert') THEN
		INSERT INTO d_tableliste(ident, tablen, nchmpc, nchmpl, isreq, ref_field, type, onslct, ispkey, traduit) VALUES ((select max(ident) from d_tableliste)+1, tableliste_ident, 'id_ebanking_transfert', maketraductionlangsyst('Identifiant table eBanking Transfert'), true, NULL, 'int', false, true, false);

		RAISE NOTICE 'Insertion id_ebanking_transfert de la table d_tableliste effectuée';
		output_result := 2;
	END IF;
	
	IF NOT EXISTS(SELECT * FROM d_tableliste WHERE tablen=tableliste_ident AND nchmpc = 'service') THEN
		INSERT INTO d_tableliste(ident, tablen, nchmpc, nchmpl, isreq, ref_field, type, onslct, ispkey, traduit) VALUES ((select max(ident) from d_tableliste)+1, tableliste_ident, 'service', maketraductionlangsyst('Service'), true, NULL, 'lsb', true, false, false);

		RAISE NOTICE 'Insertion service de la table d_tableliste effectuée';
		output_result := 2;
	END IF;
	
	IF NOT EXISTS(SELECT * FROM d_tableliste WHERE tablen=tableliste_ident AND nchmpc = 'action') THEN
		INSERT INTO d_tableliste(ident, tablen, nchmpc, nchmpl, isreq, ref_field, type, onslct, ispkey, traduit) VALUES ((select max(ident) from d_tableliste)+1, tableliste_ident, 'action', maketraductionlangsyst('Action'), true, NULL, 'lsb', true, false, false);

		RAISE NOTICE 'Insertion action de la table d_tableliste effectuée';
		output_result := 2;
	END IF;

	IF NOT EXISTS(SELECT * FROM d_tableliste WHERE tablen=tableliste_ident AND nchmpc = 'mnt_min') THEN
		INSERT INTO d_tableliste(ident, tablen, nchmpc, nchmpl, isreq, ref_field, type, onslct, ispkey, traduit) VALUES ((select max(ident) from d_tableliste)+1, tableliste_ident, 'mnt_min', maketraductionlangsyst('Montant minimum'), true, NULL, 'flt', false, false, false);

		RAISE NOTICE 'Insertion mnt_min de la table d_tableliste effectuée';
		output_result := 2;
	END IF;

	IF NOT EXISTS(SELECT * FROM d_tableliste WHERE tablen=tableliste_ident AND nchmpc = 'mnt_max') THEN
		INSERT INTO d_tableliste(ident, tablen, nchmpc, nchmpl, isreq, ref_field, type, onslct, ispkey, traduit) VALUES ((select max(ident) from d_tableliste)+1, tableliste_ident, 'mnt_max', maketraductionlangsyst('Montant maximum'), true, NULL, 'flt', false, false, false);

		RAISE NOTICE 'Insertion mnt_max de la table d_tableliste effectuée';
		output_result := 2;
	END IF;
	
	IF NOT EXISTS(SELECT * FROM d_tableliste WHERE tablen=tableliste_ident AND nchmpc = 'devise') THEN
		INSERT INTO d_tableliste(ident, tablen, nchmpc, nchmpl, isreq, ref_field, type, onslct, ispkey, traduit) VALUES ((select max(ident) from d_tableliste)+1, tableliste_ident, 'devise', maketraductionlangsyst('Devise'), true, NULL, 'lsb', true, false, false);

		RAISE NOTICE 'Insertion devise de la table d_tableliste effectuée';
		output_result := 2;
	END IF;
	
	IF NOT EXISTS(SELECT * FROM ad_cpt_ope WHERE type_operation=119 AND categorie_ope = 1 AND id_ag = numagc()) THEN
		-- Transfert entre comptes par Mobile Banking
		INSERT INTO ad_cpt_ope(type_operation, categorie_ope, id_ag, libel_ope) VALUES (119, 1, numagc(), maketraductionlangsyst('Transfert entre comptes par Mobile Banking'));
	
		RAISE NOTICE 'Insertion type_operation 119 dans la table ad_cpt_ope effectuée';
		output_result := 2;
	END IF;

	IF NOT EXISTS(SELECT * FROM ad_cpt_ope_cptes WHERE type_operation=119 AND sens = 'd' AND categorie_cpte = 1 AND id_ag = numagc()) THEN
		INSERT INTO ad_cpt_ope_cptes(type_operation, num_cpte, sens, categorie_cpte, id_ag) VALUES (119, NULL, 'd', 1, numagc());
	
		RAISE NOTICE 'Insertion type_operation 119 sens ''d'' dans la table ad_cpt_ope_cptes effectuée';
		output_result := 2;
	END IF;

	IF NOT EXISTS(SELECT * FROM ad_cpt_ope_cptes WHERE type_operation=119 AND sens = 'c' AND categorie_cpte = 1 AND id_ag = numagc()) THEN
		INSERT INTO ad_cpt_ope_cptes(type_operation, num_cpte, sens, categorie_cpte, id_ag) VALUES (119, NULL, 'c', 1, numagc());
	
		RAISE NOTICE 'Insertion type_operation 119 sens ''c'' dans la table ad_cpt_ope_cptes effectuée';
		output_result := 2;
	END IF;

	RAISE NOTICE 'END';

	RETURN output_result;

END;
$$
LANGUAGE plpgsql;

SELECT patch_ebanking_transfert();
DROP FUNCTION patch_ebanking_transfert();

---------------------------------------------------------------------------------------------------------------------

CREATE OR REPLACE FUNCTION patch_modif_ewallet() RETURNS INT AS
$$
DECLARE
output_result INTEGER = 1;

BEGIN

	RAISE NOTICE 'START';

	IF NOT EXISTS(SELECT * FROM ad_cpt_ope WHERE type_operation=121 AND categorie_ope = 1 AND id_ag = numagc()) THEN
		-- Transfert eWallet
		INSERT INTO ad_cpt_ope(type_operation, categorie_ope, id_ag, libel_ope) VALUES (121, 1, numagc(), maketraductionlangsyst('Transfert vers eWallet'));
	
		RAISE NOTICE 'Insertion type_operation 121 dans la table ad_cpt_ope effectuée';
		output_result := 2;
	END IF;

	IF NOT EXISTS(SELECT * FROM ad_cpt_ope_cptes WHERE type_operation=121 AND sens = 'd' AND categorie_cpte = 1 AND id_ag = numagc()) THEN
		INSERT INTO ad_cpt_ope_cptes(type_operation, num_cpte, sens, categorie_cpte, id_ag) VALUES (121, NULL, 'd', 1, numagc());
	
		RAISE NOTICE 'Insertion type_operation 121 sens ''d'' dans la table ad_cpt_ope_cptes effectuée';
		output_result := 2;
	END IF;

	IF NOT EXISTS(SELECT * FROM ad_cpt_ope_cptes WHERE type_operation=121 AND sens = 'c' AND categorie_cpte = 1 AND id_ag = numagc()) THEN
		INSERT INTO ad_cpt_ope_cptes(type_operation, num_cpte, sens, categorie_cpte, id_ag) VALUES (121, NULL, 'c', 1, numagc());
	
		RAISE NOTICE 'Insertion type_operation 121 sens ''c'' dans la table ad_cpt_ope_cptes effectuée';
		output_result := 2;
	END IF;
	
	RAISE NOTICE 'END';

	RETURN output_result;

END;
$$
LANGUAGE plpgsql;

SELECT patch_modif_ewallet();
DROP FUNCTION patch_modif_ewallet();

---------------------------------------------------------------------------------------------------------------------

CREATE OR REPLACE FUNCTION adsys_mobile_service() RETURNS INT AS
$$
DECLARE
output_result INTEGER = 1;

BEGIN

	RAISE NOTICE 'START';

	IF NOT EXISTS(SELECT * FROM information_schema.tables WHERE table_name = 'adsys_mobile_service') THEN
		CREATE TABLE adsys_mobile_service
		(
		  id_service serial NOT NULL,
		  id_ag integer NOT NULL,
		  code character varying(50),
		  libelle character varying(50),
		  CONSTRAINT adsys_mobile_service_pkey PRIMARY KEY (id_service, id_ag),
		  CONSTRAINT adsys_mobile_service_ukey UNIQUE (id_service, id_ag, code)
		)
		WITH (
		  OIDS=FALSE
		);

		RAISE NOTICE 'Table adsys_mobile_service created';
		output_result := 2;
	END IF;

	IF NOT EXISTS(SELECT * FROM adsys_mobile_service WHERE id_ag = (SELECT numagc()) AND code = 'SMS') THEN
		INSERT INTO adsys_mobile_service(id_service, id_ag, code, libelle) VALUES ((select COALESCE(max(id_service), 0) from adsys_mobile_service)+1, (SELECT numagc()), 'SMS', 'SMS Banking');
		RAISE NOTICE 'Insertion table adsys_mobile_service de la table adsys_mobile_service effectuée';
		output_result := 2;
	END IF;
	
	IF NOT EXISTS(SELECT * FROM adsys_mobile_service WHERE id_ag = (SELECT numagc()) AND code = 'ESTATEMENT') THEN
		INSERT INTO adsys_mobile_service(id_service, id_ag, code, libelle) VALUES ((select COALESCE(max(id_service), 0) from adsys_mobile_service)+1, (SELECT numagc()), 'ESTATEMENT', 'eStatement');
		RAISE NOTICE 'Insertion table adsys_mobile_service de la table adsys_mobile_service effectuée';
		output_result := 2;
	END IF;	

	RAISE NOTICE 'END';

	RETURN output_result;

END;
$$
LANGUAGE plpgsql;

SELECT adsys_mobile_service();
DROP FUNCTION adsys_mobile_service();

---------------------------------------------------------------------------------------------------------------------

CREATE OR REPLACE FUNCTION evo_create_triggers() RETURNS INT AS
$$
DECLARE
output_result INTEGER = 1;

BEGIN

	RAISE NOTICE 'START';
	
	-- Trigger: maj_horodatage on ad_cli
	IF NOT EXISTS(SELECT * FROM information_schema.triggers WHERE event_object_table = 'ad_cli' AND trigger_name = 'maj_horodatage') THEN
		-- DROP TRIGGER IF EXISTS maj_horodatage ON ad_cli;
		CREATE TRIGGER maj_horodatage
		  BEFORE UPDATE
		  ON ad_cli
		  FOR EACH ROW
		  EXECUTE PROCEDURE maj_horodatage();
	END IF;

	-- Trigger: maj_horodatage on ad_cpt
	IF NOT EXISTS(SELECT * FROM information_schema.triggers WHERE event_object_table = 'ad_cpt' AND trigger_name = 'maj_horodatage') THEN
		-- DROP TRIGGER IF EXISTS maj_horodatage ON ad_cpt;
		CREATE TRIGGER maj_horodatage
		  BEFORE UPDATE
		  ON ad_cpt
		  FOR EACH ROW
		  EXECUTE PROCEDURE maj_horodatage();
	END IF;

	-- Trigger: maj_horodatage on ad_dcr
	IF NOT EXISTS(SELECT * FROM information_schema.triggers WHERE event_object_table = 'ad_dcr' AND trigger_name = 'maj_horodatage') THEN
		-- DROP TRIGGER IF EXISTS maj_horodatage ON ad_dcr;
		CREATE TRIGGER maj_horodatage
		  BEFORE UPDATE
		  ON ad_dcr
		  FOR EACH ROW
		  EXECUTE PROCEDURE maj_horodatage();
	END IF;

	-- Trigger: maj_horodatage on ad_etr
	IF NOT EXISTS(SELECT * FROM information_schema.triggers WHERE event_object_table = 'ad_etr' AND trigger_name = 'maj_horodatage') THEN
		-- DROP TRIGGER IF EXISTS maj_horodatage ON ad_etr;
		CREATE TRIGGER maj_horodatage
		  BEFORE UPDATE
		  ON ad_etr
		  FOR EACH ROW
		  EXECUTE PROCEDURE maj_horodatage();
	END IF;

	-- Trigger: maj_horodatage on ad_sre
	IF NOT EXISTS(SELECT * FROM information_schema.triggers WHERE event_object_table = 'ad_sre' AND trigger_name = 'maj_horodatage') THEN
		-- DROP TRIGGER IF EXISTS maj_horodatage ON ad_sre;
		CREATE TRIGGER maj_horodatage
		  BEFORE UPDATE
		  ON ad_sre
		  FOR EACH ROW
		  EXECUTE PROCEDURE maj_horodatage();
	END IF;

	-- Trigger: maj_horodatage on adsys_localisation
	IF NOT EXISTS(SELECT * FROM information_schema.triggers WHERE event_object_table = 'adsys_localisation' AND trigger_name = 'maj_horodatage') THEN
		-- DROP TRIGGER IF EXISTS maj_horodatage ON adsys_localisation;
		CREATE TRIGGER maj_horodatage
		  BEFORE UPDATE
		  ON adsys_localisation
		  FOR EACH ROW
		  EXECUTE PROCEDURE maj_horodatage();
	END IF;

	-- Trigger: maj_horodatage on adsys_sect_activite
	IF NOT EXISTS(SELECT * FROM information_schema.triggers WHERE event_object_table = 'adsys_sect_activite' AND trigger_name = 'maj_horodatage') THEN
		-- DROP TRIGGER IF EXISTS maj_horodatage ON adsys_sect_activite;
		CREATE TRIGGER maj_horodatage
		  BEFORE UPDATE
		  ON adsys_sect_activite
		  FOR EACH ROW
		  EXECUTE PROCEDURE maj_horodatage();
	END IF;

	-- Trigger: maj_horodatage on ad_chequier
	IF NOT EXISTS(SELECT * FROM information_schema.triggers WHERE event_object_table = 'ad_chequier' AND trigger_name = 'maj_horodatage') THEN
		-- DROP TRIGGER IF EXISTS maj_horodatage ON ad_chequier;
		CREATE TRIGGER maj_horodatage
		  BEFORE UPDATE
		  ON ad_chequier
		  FOR EACH ROW
		  EXECUTE PROCEDURE maj_horodatage();
	END IF;

	-- Trigger: maj_horodatage on ad_abonnement
	IF NOT EXISTS(SELECT * FROM information_schema.triggers WHERE event_object_table = 'ad_abonnement' AND trigger_name = 'maj_horodatage') THEN
		-- DROP TRIGGER IF EXISTS maj_horodatage ON ad_abonnement;
		CREATE TRIGGER maj_horodatage
		  BEFORE UPDATE
		  ON ad_abonnement
		  FOR EACH ROW
		  EXECUTE PROCEDURE maj_horodatage();
	END IF;

	-- Trigger: ins_maj_horodatage on ad_abonnement
	IF NOT EXISTS(SELECT * FROM information_schema.triggers WHERE event_object_table = 'ad_abonnement' AND trigger_name = 'ins_maj_horodatage') THEN
		-- DROP TRIGGER IF EXISTS ins_maj_horodatage ON ad_abonnement;
		CREATE TRIGGER ins_maj_horodatage
		  AFTER INSERT OR UPDATE
		  ON ad_abonnement
		  FOR EACH ROW
		  EXECUTE PROCEDURE ins_maj_horodatage();
	END IF;
	
	RAISE NOTICE 'END';

	RETURN output_result;

END;
$$
LANGUAGE plpgsql;

SELECT evo_create_triggers();
DROP FUNCTION evo_create_triggers();

---------------------------------------------------------------------------------------------------------------------
