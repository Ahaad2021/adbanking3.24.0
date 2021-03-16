CREATE OR REPLACE FUNCTION patch_modif_ewallet2() RETURNS INT AS
$$
DECLARE
  output_result INTEGER = 1;

BEGIN

  RAISE NOTICE 'START';

  ------------------------------------------------ Dépôt eWallet -----------------------------------------------------

  IF NOT EXISTS(SELECT * FROM ad_cpt_ope WHERE type_operation=117 AND categorie_ope = 1 AND id_ag = numagc()) THEN
    -- Transfert eWallet
    INSERT INTO ad_cpt_ope(type_operation, categorie_ope, id_ag, libel_ope) VALUES (117, 1, numagc(), maketraductionlangsyst('Dépôt eWallet'));

    RAISE NOTICE 'Insertion type_operation 117 dans la table ad_cpt_ope effectuée';
    output_result := 2;
  END IF;

  IF NOT EXISTS(SELECT * FROM ad_cpt_ope_cptes WHERE type_operation=117 AND sens = 'd' AND categorie_cpte = 28 AND id_ag = numagc()) THEN
    INSERT INTO ad_cpt_ope_cptes(type_operation, num_cpte, sens, categorie_cpte, id_ag) VALUES (117, NULL, 'd', 28, numagc());

    RAISE NOTICE 'Insertion type_operation 117 sens ''d'' dans la table ad_cpt_ope_cptes effectuée';
    output_result := 2;
  END IF;

  IF NOT EXISTS(SELECT * FROM ad_cpt_ope_cptes WHERE type_operation=117 AND sens = 'c' AND categorie_cpte = 1 AND id_ag = numagc()) THEN
    INSERT INTO ad_cpt_ope_cptes(type_operation, num_cpte, sens, categorie_cpte, id_ag) VALUES (117, NULL, 'c', 1, numagc());

    RAISE NOTICE 'Insertion type_operation 117 sens ''c'' dans la table ad_cpt_ope_cptes effectuée';
    output_result := 2;
  END IF;

  ------------------------------------------------ Retrait eWallet -----------------------------------------------------

  IF NOT EXISTS(SELECT * FROM ad_cpt_ope WHERE type_operation=118 AND categorie_ope = 1 AND id_ag = numagc()) THEN
    -- Transfert eWallet
    INSERT INTO ad_cpt_ope(type_operation, categorie_ope, id_ag, libel_ope) VALUES (118, 1, numagc(), maketraductionlangsyst('Retrait eWallet'));

    RAISE NOTICE 'Insertion type_operation 118 dans la table ad_cpt_ope effectuée';
    output_result := 2;
  END IF;

  IF NOT EXISTS(SELECT * FROM ad_cpt_ope_cptes WHERE type_operation=118 AND sens = 'd' AND categorie_cpte = 1 AND id_ag = numagc()) THEN
    INSERT INTO ad_cpt_ope_cptes(type_operation, num_cpte, sens, categorie_cpte, id_ag) VALUES (118, NULL, 'd', 1, numagc());

    RAISE NOTICE 'Insertion type_operation 118 sens ''d'' dans la table ad_cpt_ope_cptes effectuée';
    output_result := 2;
  END IF;

  IF NOT EXISTS(SELECT * FROM ad_cpt_ope_cptes WHERE type_operation=118 AND sens = 'c' AND categorie_cpte = 28 AND id_ag = numagc()) THEN
    INSERT INTO ad_cpt_ope_cptes(type_operation, num_cpte, sens, categorie_cpte, id_ag) VALUES (118, NULL, 'c', 28, numagc());

    RAISE NOTICE 'Insertion type_operation 118 sens ''c'' dans la table ad_cpt_ope_cptes effectuée';
    output_result := 2;
  END IF;

  --------------------------------------------- Frais retrait ewallet ---------------------------------------------------------

  IF NOT EXISTS(SELECT * FROM ad_cpt_ope WHERE type_operation=187 AND categorie_ope = 1 AND id_ag = numagc()) THEN
    -- Frais transfert E-wallet vers ADBanking
    INSERT INTO ad_cpt_ope(type_operation, categorie_ope, id_ag, libel_ope)
    VALUES (187, 1, numagc(), maketraductionlangsyst('Frais retrait eWallet'));

    RAISE NOTICE 'Insertion type_operation 187 dans la table ad_cpt_ope effectuée';
    output_result := 2;
  END IF;

  IF NOT EXISTS(SELECT * FROM ad_cpt_ope_cptes WHERE type_operation=187 AND sens = 'd' AND categorie_cpte = 1 AND id_ag = numagc()) THEN
    INSERT INTO ad_cpt_ope_cptes(type_operation, num_cpte, sens, categorie_cpte, id_ag) VALUES (187, NULL, 'd', 1, numagc());

    RAISE NOTICE 'Insertion type_operation 187 sens ''d'' dans la table ad_cpt_ope_cptes effectuée';
    output_result := 2;
  END IF;

  IF NOT EXISTS(SELECT * FROM ad_cpt_ope_cptes WHERE type_operation=187 AND sens = 'c' AND categorie_cpte = 0 AND id_ag = numagc()) THEN
    INSERT INTO ad_cpt_ope_cptes(type_operation, num_cpte, sens, categorie_cpte, id_ag) VALUES (187, NULL, 'c', 0, numagc());

    RAISE NOTICE 'Insertion type_operation 187 sens ''c'' dans la table ad_cpt_ope_cptes effectuée';
    output_result := 2;
  END IF;

  ------------------------------------------------ Mise a jour des libellé -----------------------------------------------------
  -- Libelle frais depot ewallet
  UPDATE ad_cpt_ope SET libel_ope = maketraductionlangsyst('Frais dépôt eWallet')
  WHERE type_operation = 184 AND categorie_ope = 1 AND id_ag = numagc();


  RAISE NOTICE 'END';
  RETURN output_result;

END;
$$
LANGUAGE plpgsql;

SELECT patch_modif_ewallet2();
DROP FUNCTION patch_modif_ewallet2();