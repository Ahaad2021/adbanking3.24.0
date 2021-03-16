
CREATE OR REPLACE FUNCTION script_creation_table() RETURNS INT AS
$$
DECLARE
output_result INTEGER = 1;
  d_tableliste_str integer = 0;
  tableliste_ident integer = 0;

  BEGIN

IF NOT EXISTS (SELECT * FROM information_schema.columns WHERE table_name='ad_uti' and column_name='is_agent_ag') THEN
 ALTER TABLE ad_uti ADD COLUMN is_agent_ag boolean DEFAULT FALSE;
END IF;

	tableliste_ident := (select ident from tableliste where nomc like 'ad_log' order by ident desc limit 1);

IF NOT EXISTS (SELECT * FROM information_schema.columns WHERE table_name='ad_log' and column_name='cpte_flotte_agent') THEN
	ALTER TABLE ad_log ADD COLUMN cpte_flotte_agent text;
	d_tableliste_str := makeTraductionLangSyst('Compte de flotte de agent');
	INSERT INTO d_tableliste VALUES ((select max(ident) from d_tableliste)+1, tableliste_ident, 'cpte_flotte_agent', d_tableliste_str, true, NULL, 'txt', false, false, false);
	  IF EXISTS(SELECT langue FROM adsys_langues_systeme WHERE code = 'en_GB') THEN
	    INSERT INTO ad_traductions VALUES (d_tableliste_str,'en_GB','Agent float account');
	  END IF;
END IF;

IF NOT EXISTS (SELECT * FROM information_schema.columns WHERE table_name='ad_log' and column_name='cpte_base_agent') THEN
	ALTER TABLE ad_log ADD COLUMN cpte_base_agent text;
	d_tableliste_str := makeTraductionLangSyst('Compte client de l agent');
	  INSERT INTO d_tableliste VALUES ((select max(ident) from d_tableliste)+1, tableliste_ident, 'cpte_base_agent', d_tableliste_str, true, NULL, 'txt', false, false, false);
	  IF EXISTS(SELECT langue FROM adsys_langues_systeme WHERE code = 'en_GB') THEN
	    INSERT INTO ad_traductions VALUES (d_tableliste_str,'en_GB','Agent demand deposit account');
	  END IF;
END IF;

IF NOT EXISTS (SELECT * FROM information_schema.columns WHERE table_name='ad_log' and column_name='cpte_comm_agent') THEN
 ALTER TABLE ad_log ADD COLUMN cpte_comm_agent text;
 d_tableliste_str := makeTraductionLangSyst('Compte de commission pour l agent');
	  INSERT INTO d_tableliste VALUES ((select max(ident) from d_tableliste)+1, tableliste_ident, 'cpte_comm_agent', d_tableliste_str, true, NULL, 'txt', false, false, false);
	  IF EXISTS(SELECT langue FROM adsys_langues_systeme WHERE code = 'en_GB') THEN
	    INSERT INTO ad_traductions VALUES (d_tableliste_str,'en_GB','Agent account for commissions');
	  END IF;
END IF;


IF NOT EXISTS (SELECT * FROM information_schema.columns WHERE table_name='ad_log' and column_name='plafond_retrait') THEN
 ALTER TABLE ad_log ADD COLUMN plafond_retrait numeric(30,6);
 d_tableliste_str := makeTraductionLangSyst('Plafond retrait pour agent');
	  INSERT INTO d_tableliste VALUES ((select max(ident) from d_tableliste)+1, tableliste_ident, 'plafond_retrait', d_tableliste_str, null, NULL, 'mnt', false, false, false);
	  IF EXISTS(SELECT langue FROM adsys_langues_systeme WHERE code = 'en_GB') THEN
	    INSERT INTO ad_traductions VALUES (d_tableliste_str,'en_GB','Agent withdraw limit ');
	  END IF;
END IF;


IF NOT EXISTS (SELECT * FROM information_schema.columns WHERE table_name='ad_log' and column_name='plafond_depot') THEN
 ALTER TABLE ad_log ADD COLUMN plafond_depot numeric(30,6);
 d_tableliste_str := makeTraductionLangSyst('Plafond dépôt pour agent');
	  INSERT INTO d_tableliste VALUES ((select max(ident) from d_tableliste)+1, tableliste_ident, 'plafond_depot', d_tableliste_str, null, NULL, 'mnt', false, false, false);
	  IF EXISTS(SELECT langue FROM adsys_langues_systeme WHERE code = 'en_GB') THEN
	    INSERT INTO ad_traductions VALUES (d_tableliste_str,'en_GB','Agent deposit limit');
	  END IF;
END IF;


IF NOT EXISTS (SELECT * FROM information_schema.columns WHERE table_name='ad_log' and column_name='masquer_solde_client') THEN
 ALTER TABLE ad_log ADD COLUMN masquer_solde_client boolean default false;
 d_tableliste_str := makeTraductionLangSyst('Masquer le solde client pour l''agent?');
	  INSERT INTO d_tableliste VALUES ((select max(ident) from d_tableliste)+1, tableliste_ident, 'masquer_solde_client', d_tableliste_str, null, NULL, 'bol', false, false, false);
	  IF EXISTS(SELECT langue FROM adsys_langues_systeme WHERE code = 'en_GB') THEN
	    INSERT INTO ad_traductions VALUES (d_tableliste_str,'en_GB','Hide customer balance to an agent?');
	  END IF;
END IF;


IF NOT EXISTS(SELECT * FROM information_schema.tables WHERE table_name = 'ag_commission') THEN

CREATE TABLE ag_commission
(
  id serial NOT NULL,
  type_comm integer NOT NULL,
  id_palier integer NOT NULL,
  mnt_min numeric(30,6),
  mnt_max numeric(30,6),
  comm_agent_prc double precision,
  comm_agent_mnt numeric(30,6),
  comm_inst_prc double precision,
  comm_inst_mnt numeric(30,6),
  comm_tot_prc double precision,
  comm_tot_mnt numeric(30,6),
  date_creation timestamp without time zone,
  id_ag integer NOT NULL,
  CONSTRAINT ag_commission_pkey PRIMARY KEY (id, id_ag)
        )
WITH (
  OIDS=FALSE
);
  ALTER TABLE ag_commission
  OWNER TO postgres;
END IF;


	IF NOT EXISTS(SELECT * FROM tableliste WHERE nomc = 'ag_commission') THEN
	INSERT INTO tableliste VALUES ((select max(ident) from tableliste)+1, 'ag_commission', makeTraductionLangSyst('"Table des commissions Agency Banking"'), true);
	RAISE NOTICE 'Données table ag_commission rajoutés dans table tableliste';
	END IF;





IF NOT EXISTS(SELECT * FROM information_schema.tables WHERE table_name = 'ag_commission_hist') THEN

CREATE TABLE ag_commission_hist
(
  id serial NOT NULL,
  type_comm integer NOT NULL,
  id_palier integer NOT NULL,
  mnt_min numeric(30,6),
  mnt_max numeric(30,6),
  comm_agent_prc double precision,
  comm_agent_mnt numeric(30,6),
  comm_inst_prc double precision,
  comm_inst_mnt numeric(30,6),
  comm_tot_prc double precision,
  comm_tot_mnt numeric(30,6),
  date_creation timestamp without time zone,
  id_ag integer NOT NULL,
  CONSTRAINT ag_commission_hist_pkey PRIMARY KEY (id, id_ag)
        )
WITH (
  OIDS=FALSE
);
  ALTER TABLE ag_commission_hist
  OWNER TO postgres;
END IF;

	IF NOT EXISTS(SELECT * FROM tableliste WHERE nomc = 'ag_commission_hist') THEN
	INSERT INTO tableliste VALUES ((select max(ident) from tableliste)+1, 'ag_commission_hist', makeTraductionLangSyst('"Table des commissions historiées Agency Banking"'), true);
	RAISE NOTICE 'Données table ag_commission_hist rajoutés dans table tableliste';
	END IF;


	IF NOT EXISTS(SELECT * FROM information_schema.tables WHERE table_name = 'ag_param_commission_institution') THEN

CREATE TABLE ag_param_commission_institution
(
  choix_retrait_comm integer,
  cpte_compta_comm_retrait text,
  choix_depot_comm integer,
  cpte_compta_comm_depot text,
  date_creation timestamp without time zone,
  date_modif timestamp without time zone,
  login text,
  id_ag integer)
WITH (
  OIDS=FALSE
);
  ALTER TABLE ag_param_commission_institution
  OWNER TO postgres;
END IF;


IF NOT EXISTS(SELECT * FROM information_schema.tables WHERE table_name = 'ag_approvisionnement_transfert') THEN

CREATE TABLE ag_approvisionnement_transfert
(
  id serial NOT NULL,
  type_transaction integer,
  num_cpte_base integer,
  num_cpte_flotte text,
  etat_appro integer,
  login_agent text,
  login_util text,
  montant numeric(30,6),
  devise text,
  nom_banque text,
  ref_versement text,
  motif text,
  date_creation timestamp without time zone,
  date_modif timestamp without time zone,
  id_his integer,
  id_ag integer,
  CONSTRAINT ag_approvisionnement_transfert_pkey PRIMARY KEY (id, id_ag)
  )
WITH (
  OIDS=FALSE
);
  ALTER TABLE ag_approvisionnement_transfert
  OWNER TO postgres;
END IF;

  -- Nouvelle operation comptable des intérêts a payer : 618

	IF NOT EXISTS(SELECT * FROM ad_cpt_ope WHERE type_operation = 618 AND categorie_ope = 1 AND id_ag = numagc()) THEN
		INSERT INTO ad_cpt_ope(type_operation, categorie_ope, id_ag, libel_ope) VALUES (618, 1, numagc(), maketraductionlangsyst('Compte de flotte agent 1'));
		RAISE NOTICE 'Insertion type_operation 618 dans la table ad_cpt_ope effectuée';
		output_result := 2;
	END IF;

	IF NOT EXISTS(SELECT * FROM ad_cpt_ope_cptes WHERE type_operation = 618 AND sens = 'd' AND categorie_cpte = 1 AND id_ag = numagc()) THEN
		INSERT INTO ad_cpt_ope_cptes(type_operation, num_cpte, sens, categorie_cpte, id_ag) VALUES (618, NULL, 'd', 1, numagc());
	END IF;

	IF NOT EXISTS(SELECT * FROM ad_cpt_ope_cptes WHERE type_operation = 618 AND sens = 'c' AND categorie_cpte = 29 AND id_ag = numagc()) THEN
	INSERT INTO ad_cpt_ope_cptes(type_operation, num_cpte, sens, categorie_cpte, id_ag) VALUES (618, NULL, 'c', 29, numagc());

	END IF;


  -- Nouvelle operation comptable des intérêts a payer : 619

	IF NOT EXISTS(SELECT * FROM ad_cpt_ope WHERE type_operation = 619 AND categorie_ope = 1 AND id_ag = numagc()) THEN
		INSERT INTO ad_cpt_ope(type_operation, categorie_ope, id_ag, libel_ope) VALUES (619, 1, numagc(), maketraductionlangsyst('Transfert compte de flotte vers compte courant Agency Banking'));
		RAISE NOTICE 'Insertion type_operation 618 dans la table ad_cpt_ope effectuée';
		output_result := 2;
	END IF;

	IF NOT EXISTS(SELECT * FROM ad_cpt_ope_cptes WHERE type_operation = 619 AND sens = 'd' AND categorie_cpte = 29 AND id_ag = numagc()) THEN
		INSERT INTO ad_cpt_ope_cptes(type_operation, num_cpte, sens, categorie_cpte, id_ag) VALUES (619, NULL, 'd', 29, numagc());

	END IF;

	IF NOT EXISTS(SELECT * FROM ad_cpt_ope_cptes WHERE type_operation = 619 AND sens = 'c' AND categorie_cpte = 1 AND id_ag = numagc()) THEN
		INSERT INTO ad_cpt_ope_cptes(type_operation, num_cpte, sens, categorie_cpte, id_ag) VALUES (619, NULL, 'c', 1, numagc());

	END IF;

	  -- Nouvelle operation comptable des intérêts a payer : 621

	IF NOT EXISTS(SELECT * FROM ad_cpt_ope WHERE type_operation = 621 AND categorie_ope = 1 AND id_ag = numagc()) THEN
		INSERT INTO ad_cpt_ope(type_operation, categorie_ope, id_ag, libel_ope) VALUES (621, 1, numagc(), maketraductionlangsyst('Dépôt en espèce via agent'));
		RAISE NOTICE 'Insertion type_operation 621 dans la table ad_cpt_ope effectuée';
		output_result := 2;
	END IF;

	IF NOT EXISTS(SELECT * FROM ad_cpt_ope_cptes WHERE type_operation = 621 AND sens = 'd' AND categorie_cpte = 29 AND id_ag = numagc()) THEN
		INSERT INTO ad_cpt_ope_cptes(type_operation, num_cpte, sens, categorie_cpte, id_ag) VALUES (621, NULL, 'd', 29, numagc());

	END IF;

	IF NOT EXISTS(SELECT * FROM ad_cpt_ope_cptes WHERE type_operation = 621 AND sens = 'c' AND categorie_cpte = 1 AND id_ag = numagc()) THEN
		INSERT INTO ad_cpt_ope_cptes(type_operation, num_cpte, sens, categorie_cpte, id_ag) VALUES (621, NULL, 'c', 1, numagc());

	END IF;


	-- Nouvelle operation commission pour agent : 622

	IF NOT EXISTS(SELECT * FROM ad_cpt_ope WHERE type_operation = 622 AND categorie_ope = 1 AND id_ag = numagc()) THEN
		INSERT INTO ad_cpt_ope(type_operation, categorie_ope, id_ag, libel_ope) VALUES (622, 1, numagc(), maketraductionlangsyst('Commission pour agent'));
		RAISE NOTICE 'Insertion type_operation 622 dans la table ad_cpt_ope effectuée';
		output_result := 2;
	END IF;

	IF NOT EXISTS(SELECT * FROM ad_cpt_ope_cptes WHERE type_operation = 622 AND sens = 'd' AND categorie_cpte = 1 AND id_ag = numagc()) THEN
		INSERT INTO ad_cpt_ope_cptes(type_operation, num_cpte, sens, categorie_cpte, id_ag) VALUES (622, NULL, 'd', 1, numagc());

	END IF;

	IF NOT EXISTS(SELECT * FROM ad_cpt_ope_cptes WHERE type_operation = 622 AND sens = 'c' AND categorie_cpte = 29 AND id_ag = numagc()) THEN
		INSERT INTO ad_cpt_ope_cptes(type_operation, num_cpte, sens, categorie_cpte, id_ag) VALUES (622, NULL, 'c', 29, numagc());

	END IF;


		-- Nouvelle operation commission pour institution : 623

	IF NOT EXISTS(SELECT * FROM ad_cpt_ope WHERE type_operation = 623 AND categorie_ope = 1 AND id_ag = numagc()) THEN
		INSERT INTO ad_cpt_ope(type_operation, categorie_ope, id_ag, libel_ope) VALUES (623, 1, numagc(), maketraductionlangsyst('Commission pour institution'));
		RAISE NOTICE 'Insertion type_operation 622 dans la table ad_cpt_ope effectuée';
		output_result := 2;
	END IF;

	IF NOT EXISTS(SELECT * FROM ad_cpt_ope_cptes WHERE type_operation = 623 AND sens = 'd' AND categorie_cpte = 1 AND id_ag = numagc()) THEN
		INSERT INTO ad_cpt_ope_cptes(type_operation, num_cpte, sens, categorie_cpte, id_ag) VALUES (623, NULL, 'd', 1, numagc());

	END IF;

	IF NOT EXISTS(SELECT * FROM ad_cpt_ope_cptes WHERE type_operation = 623 AND sens = 'c' AND categorie_cpte = 29 AND id_ag = numagc()) THEN
		INSERT INTO ad_cpt_ope_cptes(type_operation, num_cpte, sens, categorie_cpte, id_ag) VALUES (623, NULL, 'c', 29, numagc());

	END IF;


		-- Nouvelle operation retrait en espece via agent : 624

	IF NOT EXISTS(SELECT * FROM ad_cpt_ope WHERE type_operation = 624 AND categorie_ope = 1 AND id_ag = numagc()) THEN
		INSERT INTO ad_cpt_ope(type_operation, categorie_ope, id_ag, libel_ope) VALUES (624, 1, numagc(), maketraductionlangsyst('Retrait en espèce via agent'));
		RAISE NOTICE 'Insertion type_operation 624 dans la table ad_cpt_ope effectuée';
		output_result := 2;
	END IF;

	IF NOT EXISTS(SELECT * FROM ad_cpt_ope_cptes WHERE type_operation = 624 AND sens = 'd' AND categorie_cpte = 1 AND id_ag = numagc()) THEN
		INSERT INTO ad_cpt_ope_cptes(type_operation, num_cpte, sens, categorie_cpte, id_ag) VALUES (624, NULL, 'd', 1, numagc());

	END IF;

	IF NOT EXISTS(SELECT * FROM ad_cpt_ope_cptes WHERE type_operation = 624 AND sens = 'c' AND categorie_cpte = 29 AND id_ag = numagc()) THEN
		INSERT INTO ad_cpt_ope_cptes(type_operation, num_cpte, sens, categorie_cpte, id_ag) VALUES (624, NULL, 'c', 29, numagc());

	END IF;


	-- Nouvelle operation retrait en espece via agent : 625

	IF NOT EXISTS(SELECT * FROM ad_cpt_ope WHERE type_operation = 625 AND categorie_ope = 1 AND id_ag = numagc()) THEN
		INSERT INTO ad_cpt_ope(type_operation, categorie_ope, id_ag, libel_ope) VALUES (625, 1, numagc(), maketraductionlangsyst('Annulation retrait en espèce via agent'));
		RAISE NOTICE 'Insertion type_operation 625 dans la table ad_cpt_ope effectuée';
		output_result := 2;
	END IF;

	IF NOT EXISTS(SELECT * FROM ad_cpt_ope_cptes WHERE type_operation = 625 AND sens = 'd' AND categorie_cpte = 29 AND id_ag = numagc()) THEN
		INSERT INTO ad_cpt_ope_cptes(type_operation, num_cpte, sens, categorie_cpte, id_ag) VALUES (625, NULL, 'd', 29, numagc());
	END IF;

	IF NOT EXISTS(SELECT * FROM ad_cpt_ope_cptes WHERE type_operation = 625 AND sens = 'c' AND categorie_cpte = 1 AND id_ag = numagc()) THEN
		INSERT INTO ad_cpt_ope_cptes(type_operation, num_cpte, sens, categorie_cpte, id_ag) VALUES (625, NULL, 'c', 1, numagc());

	END IF;


	-- Nouvelle operation retrait en espece via agent : 626

	IF NOT EXISTS(SELECT * FROM ad_cpt_ope WHERE type_operation = 626 AND categorie_ope = 1 AND id_ag = numagc()) THEN
		INSERT INTO ad_cpt_ope(type_operation, categorie_ope, id_ag, libel_ope) VALUES (626, 1, numagc(), maketraductionlangsyst('Annulation dépôt en espèce via agent'));
		RAISE NOTICE 'Insertion type_operation 626 dans la table ad_cpt_ope effectuée';
		output_result := 2;
	END IF;

	IF NOT EXISTS(SELECT * FROM ad_cpt_ope_cptes WHERE type_operation = 626 AND sens = 'd' AND categorie_cpte = 29 AND id_ag = numagc()) THEN
		INSERT INTO ad_cpt_ope_cptes(type_operation, num_cpte, sens, categorie_cpte, id_ag) VALUES (626, NULL, 'd', 29, numagc());
	END IF;

	IF NOT EXISTS(SELECT * FROM ad_cpt_ope_cptes WHERE type_operation = 626 AND sens = 'c' AND categorie_cpte = 1 AND id_ag = numagc()) THEN
		INSERT INTO ad_cpt_ope_cptes(type_operation, num_cpte, sens, categorie_cpte, id_ag) VALUES (626, NULL, 'c', 1, numagc());

	END IF;




IF NOT EXISTS(SELECT * FROM information_schema.tables WHERE table_name = 'ag_param_appro_transfert') THEN

CREATE TABLE ag_param_appro_transfert
(
  autorisation_appro boolean default false,
  autorisation_transfert boolean default false,
  date_creation timestamp without time zone,
  date_modif timestamp without time zone,
  id_ag integer
  )
WITH (
  OIDS=FALSE
);
  ALTER TABLE ag_param_appro_transfert
  OWNER TO postgres;
END IF;


IF NOT EXISTS(SELECT * FROM information_schema.tables WHERE table_name = 'ad_annulation_retrait_depot_agent') THEN
-- Table: ad_annulation_retrait_depot_agent

-- DROP TABLE ad_annulation_retrait_depot_agent;

CREATE TABLE ad_annulation_retrait_depot_agent
(
  id serial NOT NULL,
  id_ag integer NOT NULL,
  login character varying(50) NOT NULL,
  id_his integer NOT NULL,
  annul_id_his integer,
  id_client integer NOT NULL,
  etat_annul integer NOT NULL,
  fonc_sys integer NOT NULL,
  type_ope integer,
  montant numeric(30,6),
  devise character(3),
  comments text,
  date_crea timestamp without time zone NOT NULL DEFAULT now(),
  date_modif timestamp without time zone,
  date_annul timestamp without time zone,
  commission_agent numeric(30,6),
  commission_inst numeric(30,6),
  CONSTRAINT ad_annulation_retrait_depot_agent_pkey PRIMARY KEY (id, id_ag)
)
WITH (
  OIDS=FALSE
);
ALTER TABLE ad_annulation_retrait_depot_agent
  OWNER TO postgres;

  END IF;

  RETURN output_result;
END;
$$
LANGUAGE plpgsql;

SELECT script_creation_table();
DROP FUNCTION script_creation_table();

