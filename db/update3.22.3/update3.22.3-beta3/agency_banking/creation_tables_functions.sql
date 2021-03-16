CREATE OR REPLACE FUNCTION script_creation_table() RETURNS INT AS
$$
DECLARE
output_result INTEGER = 1;
  d_tableliste_str integer = 0;
  tableliste_ident integer = 0;

  BEGIN
	tableliste_ident := (select ident from tableliste where nomc like 'adsys_produit_epargne' order by ident desc limit 1);

	IF NOT EXISTS (SELECT * FROM information_schema.columns WHERE table_name='adsys_produit_epargne' and column_name='appl_comm_deplace') THEN
 ALTER TABLE adsys_produit_epargne ADD COLUMN appl_comm_deplace boolean default false;
 d_tableliste_str := makeTraductionLangSyst('Appliquer les commissions sur dépôt/retrait en deplacé pour les opérations Agency Banking');
	  INSERT INTO d_tableliste VALUES ((select max(ident) from d_tableliste)+1, tableliste_ident, 'appl_comm_deplace', d_tableliste_str, NULL, NULL, 'bol', false, false, false);
	  IF EXISTS(SELECT langue FROM adsys_langues_systeme WHERE code = 'en_GB') THEN
	    INSERT INTO ad_traductions VALUES (d_tableliste_str,'en_GB','Apply deposit/withdrawal commission for Agency Banking transaction');
	  END IF;
END IF;

IF NOT EXISTS(SELECT * FROM information_schema.columns WHERE table_name = 'adsys_audit_multi_agence' AND column_name = 'commission_agent') THEN
	ALTER TABLE adsys_audit_multi_agence ADD COLUMN commission_agent numeric(30,6);
END IF;


IF NOT EXISTS(SELECT * FROM information_schema.columns WHERE table_name = 'adsys_audit_multi_agence' AND column_name = 'commission_inst') THEN
	ALTER TABLE adsys_audit_multi_agence ADD COLUMN commission_inst numeric(30,6);
END IF;


tableliste_ident := (select ident from tableliste where nomc like 'ad_agc' order by ident desc limit 1);

IF NOT EXISTS(SELECT * FROM information_schema.columns WHERE table_name = 'ad_agc' AND column_name = 'bloc_crea_cli') THEN
	ALTER TABLE ad_agc ADD COLUMN bloc_crea_cli BOOLEAN DEFAULT FALSE;
	d_tableliste_str := makeTraductionLangSyst('Autorisation lors de la creation client');
	  INSERT INTO d_tableliste VALUES ((select max(ident) from d_tableliste)+1, tableliste_ident, 'bloc_crea_cli', d_tableliste_str, NULL, NULL, 'bol', false, false, false);
	  IF EXISTS(SELECT langue FROM adsys_langues_systeme WHERE code = 'en_GB') THEN
	    INSERT INTO ad_traductions VALUES (d_tableliste_str,'en_GB','Autorization on client creation');
	  END IF;
END IF;


IF NOT EXISTS(SELECT * FROM information_schema.columns WHERE table_name = 'ad_cli' AND column_name = 'is_login_agb') THEN
	ALTER TABLE ad_cli ADD COLUMN is_login_agb BOOLEAN DEFAULT FALSE;
END IF;

IF NOT EXISTS(SELECT * FROM information_schema.columns WHERE table_name = 'ad_cli' AND column_name = 'login_appr_creation') THEN
	ALTER TABLE ad_cli ADD COLUMN login_appr_creation TEXT;
END IF;

 IF NOT EXISTS(SELECT * FROM information_schema.tables WHERE table_name = 'ag_param_impot') THEN
CREATE TABLE ag_param_impot
(
  prc_import double precision,
  cpte_impot text,
  appl_impot_agent boolean DEFAULT FALSE,
  appl_impot_institution boolean DEFAULT FALSE,
  date_creation timestamp without time zone,
  date_modif timestamp without time zone,
  login text,
  id_ag integer
)
WITH (
  OIDS=FALSE
);
ALTER TABLE ag_param_impot
  OWNER TO postgres;
END IF;


	IF NOT EXISTS(SELECT * FROM information_schema.columns WHERE table_name = 'ag_param_commission_institution' AND column_name = 'cpte_comm_intermediaire') THEN
	ALTER TABLE ag_param_commission_institution ADD COLUMN cpte_comm_intermediaire text;
	END IF;


	-- Operation compte intermediaire
	IF NOT EXISTS(SELECT * FROM ad_cpt_ope WHERE type_operation = 628 AND categorie_ope = 1 AND id_ag = numagc()) THEN
		INSERT INTO ad_cpt_ope(type_operation, categorie_ope, id_ag, libel_ope) VALUES (628, 1, numagc(), maketraductionlangsyst('Commission sur transaction via agent'));
		RAISE NOTICE 'Insertion type_operation 628 dans la table ad_cpt_ope effectuée';
		output_result := 2;
	END IF;

	IF NOT EXISTS(SELECT * FROM ad_cpt_ope_cptes WHERE type_operation = 628 AND sens = 'd' AND categorie_cpte = 1 AND id_ag = numagc()) THEN
		INSERT INTO ad_cpt_ope_cptes(type_operation, num_cpte, sens, categorie_cpte, id_ag) VALUES (628, NULL, 'd', 1, numagc());
	END IF;

	-- Operation prelevement impot AGB
	IF NOT EXISTS(SELECT * FROM ad_cpt_ope WHERE type_operation = 629 AND categorie_ope = 1 AND id_ag = numagc()) THEN
		INSERT INTO ad_cpt_ope(type_operation, categorie_ope, id_ag, libel_ope) VALUES (629, 1, numagc(), maketraductionlangsyst('Compte d''impôt sur commission'));
		RAISE NOTICE 'Insertion type_operation 628 dans la table ad_cpt_ope effectuée';
		output_result := 2;
	END IF;

	IF NOT EXISTS(SELECT * FROM ad_cpt_ope_cptes WHERE type_operation = 629 AND sens = 'c' AND categorie_cpte = 25 AND id_ag = numagc()) THEN
		INSERT INTO ad_cpt_ope_cptes(type_operation, num_cpte, sens, categorie_cpte, id_ag) VALUES (629, NULL, 'c', 25, numagc());
	END IF;


  RETURN output_result;
  END;
$$
LANGUAGE plpgsql;

SELECT script_creation_table();
DROP FUNCTION script_creation_table();
