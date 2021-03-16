-- Function: ticket_mae_23()

-- DROP FUNCTION ticket_mae_23();

CREATE OR REPLACE FUNCTION ticket_mae_23()
  RETURNS integer AS
$BODY$
DECLARE

  output_result integer = 0;
  id_str_trad integer = 0;
  tableliste_ident integer = 0;

BEGIN

	IF NOT EXISTS (select * from menus where nom_menu = 'Tre') THEN
	--insertion code
	INSERT INTO menus VALUES ('Tre', maketraductionlangsyst('Traitement pour remboursement anticipé'), 'Gen-11', 5, 14, true, 133, true);
	END IF;

	IF NOT EXISTS (select * from menus where nom_menu = 'Tre-1') THEN
	--insertion code
	INSERT INTO menus VALUES ('Tre-1', maketraductionlangsyst('Sélection dossier'), 'Tre', 6, 1, false, NULL, false);
	END IF;

	IF NOT EXISTS (select * from menus where nom_menu = 'Tre-2') THEN
	--insertion code
	INSERT INTO menus VALUES ('Tre-2', maketraductionlangsyst('Modification de l''échéancier'), 'Tre', 6, 2, false, NULL, false);
	END IF;

	IF NOT EXISTS (select * from menus where nom_menu = 'Tre-3') THEN
	--insertion code
	INSERT INTO menus VALUES ('Tre-3', maketraductionlangsyst('Confirmation de l''échéancier'), 'Tre', 6, 3, false, NULL, false);
	END IF;

	IF NOT EXISTS (select * from menus where nom_menu = 'Tre-4') THEN
	--insertion code
	INSERT INTO menus VALUES ('Tre-4', maketraductionlangsyst('Validation échéancier'), 'Tre', 6, 4, false, NULL, false);
	END IF;

	--ecran Validation
	IF NOT EXISTS (select * from ecrans where nom_ecran = 'Tre-1') THEN
	 --insertion code
	 INSERT INTO ecrans(nom_ecran, fichier, nom_menu, fonction)
	 VALUES ('Tre-1', 'modules/credit/traitement_remboursement_anticipe.php', 'Tre-1', 133);
	END IF;

	IF NOT EXISTS (select * from ecrans where nom_ecran = 'Tre-2') THEN
	 --insertion code
	 INSERT INTO ecrans(nom_ecran, fichier, nom_menu, fonction)
	 VALUES ('Tre-2', 'modules/credit/traitement_remboursement_anticipe.php', 'Tre-2', 133);
	END IF;

	IF NOT EXISTS (select * from ecrans where nom_ecran = 'Tre-3') THEN
	 --insertion code
	 INSERT INTO ecrans(nom_ecran, fichier, nom_menu, fonction)
	 VALUES ('Tre-3', 'modules/credit/traitement_remboursement_anticipe.php', 'Tre-3', 133);
	END IF;

	IF NOT EXISTS (select * from ecrans where nom_ecran = 'Tre-4') THEN
	 --insertion code
	 INSERT INTO ecrans(nom_ecran, fichier, nom_menu, fonction)
	 VALUES ('Tre-4', 'modules/credit/traitement_remboursement_anticipe.php', 'Tre-4', 133);
	END IF;

IF NOT EXISTS(SELECT * FROM information_schema.columns WHERE table_name = 'ad_agc' AND column_name = 'tx_remb_anticipe') THEN
ALTER TABLE ad_agc
ADD COLUMN tx_remb_anticipe boolean;
select into tableliste_ident ident from tableliste where nomc like 'ad_agc' order by ident desc limit 1;
INSERT INTO d_tableliste VALUES ((select max(ident) from d_tableliste)+1,tableliste_ident, 'tx_remb_anticipe', makeTraductionLangSyst('Appliquer taux de remboursement anticipé?'), NULL, NULL, 'bol', false, false, false);
END IF;


IF NOT EXISTS(SELECT * FROM information_schema.columns WHERE table_name = 'ad_dcr' AND column_name = 'interet_remb_anticipe') THEN
ALTER TABLE ad_dcr
ADD COLUMN interet_remb_anticipe numeric(30,6);
select into tableliste_ident ident from tableliste where nomc like 'ad_dcr' order by ident desc limit 1;
INSERT INTO d_tableliste VALUES ((select max(ident) from d_tableliste)+1, tableliste_ident, 'interet_remb_anticipe', makeTraductionLangSyst('Interet à rembourser anticipé'), NULL, NULL, 'mnt', false, false, false);
END IF;

  -- Nouvelle operation comptable Perception des intérêts pour remboursement anticipé  : 22 - Perception des intérêts pour remboursement anticipé

	IF NOT EXISTS(SELECT * FROM ad_cpt_ope WHERE type_operation = 22 AND categorie_ope = 1 AND id_ag = numagc()) THEN
  INSERT INTO ad_cpt_ope(type_operation, categorie_ope, id_ag, libel_ope) VALUES (22, 1, numagc(), maketraductionlangsyst('Perception des intérêts pour remboursement anticipé'));
		RAISE NOTICE 'Insertion type_operation 22 dans la table ad_cpt_ope effectuée';
		output_result := 2;
	END IF;

	IF NOT EXISTS(SELECT * FROM ad_cpt_ope_cptes WHERE type_operation = 22 AND sens = 'd' AND categorie_cpte = 1 AND id_ag = numagc()) THEN
		INSERT INTO ad_cpt_ope_cptes(type_operation, num_cpte, sens, categorie_cpte, id_ag) VALUES (22, NULL, 'd', 1, numagc());

		RAISE NOTICE 'Insertion type_operation 22 sens ''d'' dans la table ad_cpt_ope_cptes effectuée';
		output_result := 2;
	END IF;

	IF NOT EXISTS(SELECT * FROM ad_cpt_ope_cptes WHERE type_operation = 22 AND sens = 'c' AND categorie_cpte = 6 AND id_ag = numagc()) THEN
		INSERT INTO ad_cpt_ope_cptes(type_operation, num_cpte, sens, categorie_cpte, id_ag) VALUES (22, NULL, 'c', 6, numagc());

		RAISE NOTICE 'Insertion type_operation 22 sens ''c'' dans la table ad_cpt_ope_cptes effectuée';
		output_result := 2;
	END IF;

	output_result := 1;

	RETURN output_result;

END;
$BODY$
  LANGUAGE plpgsql VOLATILE
  COST 100;
ALTER FUNCTION ticket_mae_23()
  OWNER TO adbanking;

  SELECT ticket_mae_23();

  DROP FUNCTION IF EXISTS ticket_mae_23();