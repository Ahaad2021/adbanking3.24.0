CREATE OR REPLACE FUNCTION script_at_145() RETURNS INT AS
$$
DECLARE
	output_result INTEGER = 1;
  	id_str_trad integer = 0;
  	tableliste_id INTEGER = 0;
  	tableliste_epargne INTEGER = 0;
  BEGIN

  	-- ADD column cpte_dormant_nbre_jour in table adsys_param_epargne
	IF NOT EXISTS(SELECT * FROM information_schema.columns WHERE table_name = 'adsys_param_epargne' AND column_name = 'cpte_dormant_nbre_jour') THEN
		ALTER TABLE adsys_param_epargne ADD COLUMN cpte_dormant_nbre_jour INTEGER;
	END IF;

	-- ADD column cpte_dormant_frais_tenue_cpte in table adsys_param_epargne 
	IF NOT EXISTS(SELECT * FROM information_schema.columns WHERE table_name = 'adsys_param_epargne' AND column_name = 'cpte_dormant_frais_tenue_cpte') THEN
		ALTER TABLE adsys_param_epargne ADD COLUMN cpte_dormant_frais_tenue_cpte BOOLEAN;
	END IF;

	-- ADD column active_compte_sur_transaction in table adsys_param_epargne 
	IF NOT EXISTS(SELECT * FROM information_schema.columns WHERE table_name = 'adsys_param_epargne' AND column_name = 'active_compte_sur_transaction') THEN
		ALTER TABLE adsys_param_epargne ADD COLUMN active_compte_sur_transaction BOOLEAN;
	END IF;

	-- ADD column passage_etat_inactif in table adsys_produit_epargne 
	IF NOT EXISTS(SELECT * FROM information_schema.columns WHERE table_name = 'adsys_produit_epargne' AND column_name = 'passage_etat_inactif') THEN
		ALTER TABLE adsys_produit_epargne ADD COLUMN passage_etat_inactif BOOLEAN;
	END IF;

	-- ADD row cpte_dormant_nbre_jour in table d_tableliste related to table adsys_param_epargne
	tableliste_id := (SELECT ident FROM tableliste WHERE nomc = 'adsys_param_epargne'); 
    id_str_trad := maketraductionlangsyst('Nombre de jour sans mouvement pour les comptes inactifs');

    IF NOT EXISTS(SELECT * FROM d_tableliste WHERE nchmpc = 'cpte_dormant_nbre_jour' and tablen = tableliste_id) THEN 
        INSERT INTO d_tableliste VALUES ((select max(ident) 
            from d_tableliste)+1, tableliste_id, 'cpte_dormant_nbre_jour', id_str_trad, 
            true, null, 'txt', true, null, FALSE); 

        IF EXISTS(SELECT langue FROM adsys_langues_systeme WHERE code = 'en_GB') THEN
			INSERT INTO ad_traductions (id_str, langue, traduction) VALUES (id_str_trad, 'en_GB', 'Number of days without movement for inactive accounts');
		END IF;
    END IF; 

	-- ADD row cpte_dormant_frais_tenue_cpte in table d_tableliste related to table adsys_param_epargne
    tableliste_id := (SELECT ident FROM tableliste WHERE nomc = 'adsys_param_epargne'); 
    id_str_trad := maketraductionlangsyst('Inclure les frais de tenue de comptes pour les comptes inactifs?');

    IF NOT EXISTS(SELECT * FROM d_tableliste WHERE nchmpc = 'cpte_dormant_frais_tenue_cpte' and tablen = tableliste_id) THEN 
        INSERT INTO d_tableliste VALUES ((select max(ident) 
            from d_tableliste)+1, tableliste_id, 'cpte_dormant_frais_tenue_cpte', id_str_trad, 
            true, null, 'bol', null, FALSE, FALSE); 

        IF EXISTS(SELECT langue FROM adsys_langues_systeme WHERE code = 'en_GB') THEN
			INSERT INTO ad_traductions (id_str, langue, traduction) VALUES (id_str_trad, 'en_GB', 'Include account maintenance fees for inactive accounts');
		END IF;
    END IF; 
  
    -- ADD row active_compte_sur_transaction in table d_tableliste related to table adsys_param_epargne
    tableliste_id := (SELECT ident FROM tableliste WHERE nomc = 'adsys_param_epargne'); 
    id_str_trad := maketraductionlangsyst('Pour un compte dormant/ inactif, seulement un dépôt/retrait effectué par le titulaire/ou mandataire rend le compte actif ?');

    IF NOT EXISTS(SELECT * FROM d_tableliste WHERE nchmpc = 'active_compte_sur_transaction' and tablen = tableliste_id) THEN 
        INSERT INTO d_tableliste VALUES ((select max(ident) 
            from d_tableliste)+1, tableliste_id, 'active_compte_sur_transaction', id_str_trad, 
            true, null, 'bol', null, FALSE, FALSE); 

        IF EXISTS(SELECT langue FROM adsys_langues_systeme WHERE code = 'en_GB') THEN
			INSERT INTO ad_traductions (id_str, langue, traduction) VALUES (id_str_trad, 'en_GB', 'For a dormant / inactive account, only a deposit / withdrawal made by the holder / or agent makes the account active ?');
		END IF;
    END IF; 


    -- ADD row passage_etat_inactif in table d_tableliste related to table adsys_produit_epargne
    tableliste_id := (SELECT ident FROM tableliste WHERE nomc = 'adsys_produit_epargne'); 
    id_str_trad := maketraductionlangsyst('Passage à état inactif');

    IF NOT EXISTS(SELECT * FROM d_tableliste WHERE nchmpc = 'passage_etat_inactif' and tablen = tableliste_id) THEN 
        INSERT INTO d_tableliste VALUES ((select max(ident) 
            from d_tableliste)+1, tableliste_id, 'passage_etat_inactif', id_str_trad, 
            false, null, 'bol', false, FALSE, FALSE); 

        IF EXISTS(SELECT langue FROM adsys_langues_systeme WHERE code = 'en_GB') THEN
			INSERT INTO ad_traductions (id_str, langue, traduction) VALUES (id_str_trad, 'en_GB', 'Change to inactive state');
		END IF;
    END IF; 


	-- ADD data for the creation of the operation 171
    IF NOT EXISTS(SELECT * FROM ad_cpt_ope WHERE type_operation = 171 AND categorie_ope = 1 AND id_ag = numagc()) THEN
		INSERT INTO ad_cpt_ope(type_operation, categorie_ope, id_ag, libel_ope) VALUES (171, 1, numagc(), maketraductionlangsyst('Déclasser les comptes inactifs'));
		RAISE NOTICE 'Insertion type_operation 171 dans la table ad_cpt_ope effectuée';
	END IF;

	-- ADD data for the creation of the operation 171
	IF NOT EXISTS(SELECT * FROM ad_cpt_ope_cptes WHERE type_operation = 171 AND sens = 'd' AND categorie_cpte = 1 AND id_ag = numagc()) THEN
		INSERT INTO ad_cpt_ope_cptes VALUES (171, NULL, 'd', 1,numagc()); 
		RAISE NOTICE 'Insertion type_operation 171 sens ''d'' dans la table ad_cpt_ope_cptes effectuée';
	END IF;

	-- ADD data for the creation of the operation 171
	IF NOT EXISTS(SELECT * FROM ad_cpt_ope_cptes WHERE type_operation = 171 AND sens = 'c' AND categorie_cpte = 0 AND id_ag = numagc()) THEN
		INSERT INTO ad_cpt_ope_cptes VALUES (171, NULL, 'c', 0,numagc());
		RAISE NOTICE 'Insertion type_operation 171 sens ''d'' dans la table ad_cpt_ope_cptes effectuée';
	END IF;

	-- Creates adsys_type_mode_calcul_frais_dormant in tableliste and d_tableliste
	tableliste_epargne := (SELECT ident FROM tableliste WHERE nomc = 'adsys_produit_epargne'); 

    IF NOT EXISTS(SELECT * FROM tableliste WHERE nomc = 'adsys_type_mode_calcul_frais_dormant') THEN
            INSERT INTO tableliste VALUES ((select max(ident) from tableliste)+1, 'adsys_type_mode_calcul_frais_dormant', makeTraductionLangSyst('"Mode de calcul des frais de tenu de comptes sur les comptes dormants"'), false);
            RAISE NOTICE 'Données table adsys_type_mode_calcul_frais_dormant rajoutés dans table tableliste';
    END IF;

	tableliste_id := (SELECT ident FROM tableliste WHERE nomc = 'adsys_type_mode_calcul_frais_dormant'); 

    IF NOT EXISTS(SELECT * FROM d_tableliste WHERE nchmpc = 'id' and tablen = tableliste_id) THEN
            INSERT INTO d_tableliste VALUES ((select max(ident) from d_tableliste)+1, tableliste_id, 'id', makeTraductionLangSyst('Id'), true, NULL, 'int', null, true, false);
    END IF;

    IF NOT EXISTS(SELECT * FROM d_tableliste WHERE nchmpc = 'libel' and tablen = tableliste_id) THEN
            INSERT INTO d_tableliste VALUES ((select max(ident) from d_tableliste)+1, tableliste_id, 'libel', makeTraductionLangSyst('Libel'), true, NULL, 'txt', true, null, false);
    END IF;

    id_str_trad := makeTraductionLangSyst('Mode de calcul des frais de tenu de comptes sur les comptes dormants');

    IF NOT EXISTS(SELECT * FROM d_tableliste WHERE nchmpc = 'mode_calcul_frais_dormant' and tablen = tableliste_epargne) THEN
       ALTER TABLE adsys_produit_epargne ADD COLUMN mode_calcul_frais_dormant integer DEFAULT 3;
       INSERT INTO d_tableliste VALUES ((select max(ident) from d_tableliste)+1, tableliste_epargne, 'mode_calcul_frais_dormant', id_str_trad, NULL, (SELECT ident from d_tableliste where tablen = tableliste_id and nchmpc = 'id' ), 'lsb', true, false, false);
       IF EXISTS(SELECT langue FROM adsys_langues_systeme WHERE code = 'en_GB') THEN
            INSERT INTO ad_traductions VALUES (id_str_trad,'en_GB','Method of calculating account maintenance fees on dormant accounts');
       END IF;
    END IF;

	-- Creates adsys_type_mode_calcul_frais_inactif in tableliste and d_tableliste
    IF NOT EXISTS(SELECT * FROM tableliste WHERE nomc = 'adsys_type_mode_calcul_frais_inactif') THEN
            INSERT INTO tableliste VALUES ((select max(ident) from tableliste)+1, 'adsys_type_mode_calcul_frais_inactif', makeTraductionLangSyst('"Mode de calcul des frais de tenu de comptes sur les comptes inactifs"'), false);
            RAISE NOTICE 'Données table adsys_type_mode_calcul_frais_inactif rajoutés dans table tableliste';
    END IF;

	tableliste_id := (SELECT ident FROM tableliste WHERE nomc = 'adsys_type_mode_calcul_frais_inactif'); 

    IF NOT EXISTS(SELECT * FROM d_tableliste WHERE nchmpc = 'id' and tablen = tableliste_id) THEN
            INSERT INTO d_tableliste VALUES ((select max(ident) from d_tableliste)+1, tableliste_id, 'id', makeTraductionLangSyst('Id'), true, NULL, 'int', null, true, false);
    END IF;

    IF NOT EXISTS(SELECT * FROM d_tableliste WHERE nchmpc = 'libelle' and tablen = tableliste_id) THEN
            INSERT INTO d_tableliste VALUES ((select max(ident) from d_tableliste)+1, tableliste_id, 'libelle', makeTraductionLangSyst('Libelle'), true, NULL, 'txt', true, null, false);
    END IF;

    id_str_trad := makeTraductionLangSyst('Mode de calcul des frais de tenu de comptes sur les comptes inactifs');

    IF NOT EXISTS(SELECT * FROM d_tableliste WHERE nchmpc = 'mode_calcul_frais_inactif' and tablen = tableliste_epargne) THEN
       ALTER TABLE adsys_produit_epargne ADD COLUMN mode_calcul_frais_inactif integer DEFAULT 3;
       INSERT INTO d_tableliste VALUES ((select max(ident) from d_tableliste)+1, tableliste_epargne, 'mode_calcul_frais_inactif', id_str_trad, NULL, (SELECT ident from d_tableliste where tablen = tableliste_id and nchmpc = 'id' ), 'lsb', true, false, false);
       IF EXISTS(SELECT langue FROM adsys_langues_systeme WHERE code = 'en_GB') THEN
        INSERT INTO ad_traductions VALUES (id_str_trad,'en_GB','Method of calculating account maintenance fees on inactive accounts');
       END IF;
    END IF;


	RETURN output_result;
END;
$$
LANGUAGE plpgsql;

SELECT script_at_145();
DROP FUNCTION script_at_145();