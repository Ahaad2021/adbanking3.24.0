/*****************Ticket Jira AT-44*************************************************/
CREATE OR REPLACE FUNCTION ticket_AT_44() RETURNS INT AS
$$
DECLARE
output_result INTEGER = 1;
  id_str_trad integer = 0;
  tableliste_ident integer = 0;

BEGIN

-- Creation table ad_retrait_deplace_attente
 IF NOT EXISTS(SELECT * FROM information_schema.tables WHERE table_name = 'ad_retrait_deplace_attente') THEN

   CREATE TABLE ad_retrait_deplace_attente
(
  id serial NOT NULL,
  id_ag_local integer,
  id_ag_distant integer,
  id_client_distant integer,
  id_cpte_distant integer,
  montant_retrait numeric(30,6),
  frais_retrait_cpte numeric(30,6),
  etat_retrait integer,
  type_retrait integer,
  communication text,
  remarque text,
  id_pers_ext integer,
  mandat text,
  num_chq integer,
  date_chq date,
  id_ben integer,
  beneficiaire text,
  nom_ben text,
  denomination text,
  id_his integer,
  login text,
  comments text,
  date_creation date,
  date_modif date,
  CONSTRAINT ad_retrait_deplace_attente_pkey PRIMARY KEY (id, id_ag_local)
);

END IF;


-- Insertion ecran de validation pour la demande
IF NOT EXISTS (select * from ecrans where nom_ecran = 'Rcp-51') THEN
	--insertion code
	INSERT INTO ecrans(nom_ecran, fichier, nom_menu, fonction)
	VALUES ('Rcp-51', 'ad_ma/app/views/epargne/retrait_compte.php', 'Ope-11', 92);
END IF;

IF NOT EXISTS (SELECT * FROM information_schema.columns WHERE table_name='ad_agc' and column_name='plafond_retrait_deplace_guichet') THEN
 ALTER TABLE ad_agc ADD COLUMN plafond_retrait_deplace_guichet boolean DEFAULT FALSE;
END IF;


tableliste_ident := (select ident from tableliste where nomc like 'ad_agc' order by ident desc limit 1);
  IF NOT EXISTS(SELECT * FROM d_tableliste WHERE nchmpc = 'plafond_retrait_deplace_guichet' and tablen = tableliste_ident) THEN
  INSERT INTO d_tableliste VALUES ((select max(ident) from d_tableliste)+1, tableliste_ident, 'plafond_retrait_deplace_guichet', makeTraductionLangSyst('Plafond retrait en déplacé au guichet'), false, NULL, 'bol', false, false, false);
END IF;

IF NOT EXISTS (SELECT * FROM information_schema.columns WHERE table_name='ad_agc' and column_name='montant_plafond_retrait_deplace') THEN
 ALTER TABLE ad_agc ADD COLUMN montant_plafond_retrait_deplace numeric(30,6) DEFAULT 0;
END IF;

  IF NOT EXISTS(SELECT * FROM d_tableliste WHERE nchmpc = 'montant_plafond_retrait_deplace' and tablen = tableliste_ident) THEN
  INSERT INTO d_tableliste VALUES ((select max(ident) from d_tableliste)+1, tableliste_ident, 'montant_plafond_retrait_deplace', makeTraductionLangSyst('Montant plafond retrait en déplacé'), false, NULL, 'mnt', false, false, false);
  END IF;


  -- Insertion ecran de validation pour la demande
IF NOT EXISTS (select * from ecrans where nom_ecran = 'Rcp-51') THEN
	--insertion code
	INSERT INTO ecrans(nom_ecran, fichier, nom_menu, fonction)
	VALUES ('Rcp-51', 'ad_ma/app/views/epargne/retrait_compte.php', 'Ope-11', 92);
END IF;

--Creation nouveau fonction Autorisation de transfert : 198
		IF NOT EXISTS (select * from adsys_fonction where code_fonction = 198) THEN
			 --insertion code
			 INSERT INTO adsys_fonction(code_fonction, libelle, id_ag)
			 VALUES (198, 'Autorisation de retrait en déplacé', numagc());
			 RAISE NOTICE 'Fonction created!';
		END IF;

		--Creation nouveau main menu + side menus
		IF NOT EXISTS (select * from menus where nom_menu = 'Atd') THEN
			--insertion code
			INSERT INTO menus (nom_menu, libel_menu, nom_pere, pos_hierarch, ordre, is_menu,fonction, is_cliquable)
			VALUES ('Atd', maketraductionlangsyst('Autorisation de retrait en déplacé'), 'Gen-6', 3, 6, true, 198, true);
			RAISE NOTICE 'Main Menu created!';
		END IF;
		IF NOT EXISTS (select * from menus where nom_menu = 'Atd-1') THEN
			--insertion code
			INSERT INTO menus (nom_menu, libel_menu, nom_pere, pos_hierarch, ordre, is_menu, is_cliquable)
			VALUES ('Atd-1', maketraductionlangsyst('Liste demande de retrait en déplacé'), 'Atd', 4, 1, false, false);
			RAISE NOTICE 'Side Menu 1 created!';
		END IF;
		IF NOT EXISTS (select * from menus where nom_menu = 'Atd-2') THEN
			--insertion code
			INSERT INTO menus (nom_menu, libel_menu, nom_pere, pos_hierarch, ordre, is_menu, is_cliquable)
			VALUES ('Atd-2', maketraductionlangsyst('Confirmation autorisation de retrait en déplacé'), 'Atd', 4, 2, false, false);
			RAISE NOTICE 'Side Menu 2 created!';
		END IF;

		--Creation nouveaux ecrans Atd-1, Atd-2,
		IF NOT EXISTS (select * from ecrans where nom_ecran = 'Atd-1') THEN
			--insertion code
			INSERT INTO ecrans(nom_ecran, fichier, nom_menu, fonction)
			VALUES ('Atd-1', 'modules/guichet/demande_autorisation_retrait_deplace.php', 'Atd-1', 198);
			RAISE NOTICE 'Ecran 1 created!';
		END IF;
		IF NOT EXISTS (select * from ecrans where nom_ecran = 'Atd-2') THEN
			--insertion code
			INSERT INTO ecrans(nom_ecran, fichier, nom_menu, fonction)
			VALUES ('Atd-2', 'modules/guichet/demande_autorisation_retrait_deplace.php', 'Atd-2', 198);
			RAISE NOTICE 'Ecran 2 created!';
		END IF;

		-- ECRANS Effectuer les retraits autoriser
		IF NOT EXISTS (SELECT nom_ecran FROM ecrans WHERE nom_ecran='Prd-11') THEN
			INSERT INTO ecrans(nom_ecran, fichier, nom_menu, fonction) VALUES ('Prd-11', 'ad_ma/app/views/epargne/paiement_retrait.php', 'Ope-11', 64);
		END IF;

		RETURN output_result;
END;
$$
LANGUAGE plpgsql;

SELECT ticket_AT_44();
DROP FUNCTION ticket_AT_44();


/***********************************************************************************/
------------------------------- DEBUT : Ticket AT-40 -----------------------------------------------------------------------------------------------------------
 -- Ajout nouveau champ dans la table ad_agc

-- Function: add_column_to_agence()

-- DROP FUNCTION add_column_to_agence();

CREATE OR REPLACE FUNCTION add_column_to_agence()
  RETURNS INT AS
$BODY$
DECLARE
output_result INTEGER = 1;
tableliste_ident INTEGER = 0;
tableliste_str INTEGER = 0;
d_tableliste_str INTEGER = 0;

BEGIN
	tableliste_ident := (select ident from tableliste where nomc like 'ad_agc' order by ident desc limit 1);

	IF NOT EXISTS(SELECT * FROM d_tableliste WHERE nchmpc = 'applique_frais_tenue_cpte_gs' and tablen = tableliste_ident) THEN
	  ALTER TABLE ad_agc ADD applique_frais_tenue_cpte_gs BOOLEAN DEFAULT false;
	  d_tableliste_str := makeTraductionLangSyst('Ne pas appliquer les frais de tenu de compte sur les membres des groupes solidaires?');
	  INSERT INTO d_tableliste VALUES ((select max(ident) from d_tableliste)+1, tableliste_ident, 'applique_frais_tenue_cpte_gs', d_tableliste_str, false, NULL, 'bol', false, false, false);
	  IF EXISTS(SELECT langue FROM adsys_langues_systeme WHERE code = 'en_GB') THEN
	    INSERT INTO ad_traductions VALUES (d_tableliste_str,'en_GB','Not to apply the account maintenance fees for the members of the solidarity groups?');
	  END IF;
	END IF;

RETURN output_result;
END;
$BODY$
  LANGUAGE plpgsql VOLATILE
  COST 100;
ALTER FUNCTION add_column_to_agence()
  OWNER TO postgres;

  SELECT add_column_to_agence();
  DROP FUNCTION IF EXISTS add_column_to_agence();


-- Evolution fonction existant prelevefraistenuecpt(integer, text, integer, text)
-- Function: prelevefraistenuecpt(integer, text, integer, text, text)

-- DROP FUNCTION prelevefraistenuecpt(integer, text, integer, text, text);

CREATE OR REPLACE FUNCTION prelevefraistenuecpt(integer, text, integer, text, text)
  RETURNS SETOF cpte_frais AS
$BODY$

DECLARE
	cur_date TIMESTAMP;
	freq_tenue_cpt ALIAS FOR $1;
	date_prelev ALIAS FOR $2;
	num_ope ALIAS FOR $3;
	jou1	INTEGER;	               -- id du journal associé au compte au débit s'il est principal
	jou2	INTEGER;	               -- id du journal associé au compte au crédit s'il est principal
	id_journal	INTEGER;	       -- id du journal des mouvements comptables
	nbre_devises	INTEGER;	       -- Nombre de devises créées
	mode_multidev	BOOLEAN;	       -- Mode multidevise ?
	devise_cpte_cr CHAR(3);		       -- Code de la devise du compte au crédit
	code_dev_ref CHAR(3);		       -- Code de la devise de référence
	devise_cpte_debit CHAR(3);	       -- Code de la devise du compte comptable associé au produit d'épargne
	cpt_pos_ch TEXT;		       -- Compte de position de change de la devise du compte traité
        cpt_cv_pos_ch TEXT;		       -- Compte de C/V de la Pos de Ch de la devise du compte traité
	cv_frais_tenue_cpte NUMERIC(30,6);     -- C/V des frais de tenue de compte
	num_cpte_debit TEXT;		       -- Compte comptable à débiter
	cpte_liaison TEXT;                     -- Compte de liaison si les deux comptes à mouvementer sont principaux
	devise_cpte_liaison CHAR(3);		       -- Code de la devise de référence
	infos_cpte RECORD;                    -- array contenant quelques informations du compte traité
	compte_frais cpte_frais;	       -- array contenant l'id, le solde et les frais des comptes traités
	exo RECORD; -- infos sur l'exercice contenant la date de prélèvement des frais
	type_ope RECORD; -- infos sur l'opérationn de prélèvement des frais

	v_info_tax RECORD;			-- array contenant les infos de la taxe associe a l'operation comptable
	v_mnt_tax NUMERIC(30,6) = 0;		-- Le montant tax calculé sur frais de tenue
	v_sens_tax ALIAS FOR $4;		-- Sens Tax pour mouvement comptables
	v_reglementTax INTEGER;			-- Pour la fonction reglementTaxFraisTenue
	v_scenario INTEGER;			-- Les differents scenarios prelevement frais tenue

	v_id_agc INTEGER;                       -- L'id agence

	appl_frais_gs ALIAS FOR $5;             -- champ parametrable de la table agence : si c'est 't' (true) on va prelever les frais de tenue des comptes sur les membres des groups solidaires

	-- Recupere des infos sur les compte épargne à prélever et leurs produits associés
	Cpt_Prelev refcursor;
	/*Cpt_Prelev CURSOR FOR
		SELECT a.id_cpte, a.id_titulaire,a.solde, a.devise, a.num_complet_cpte, b.frais_tenue_cpt as total_frais_tenue_cpt, b.cpte_cpta_prod_ep
		FROM ad_cpt a, adsys_produit_epargne b WHERE a.id_ag=b.id_ag AND a.id_ag=v_id_agc AND a.id_prod = b.id AND (frequence_tenue_cpt BETWEEN 1 AND freq_tenue_cpt)
		AND a.etat_cpte in (1,4) AND b.frais_tenue_cpt > 0 ORDER BY a.id_titulaire;*/

	ligne RECORD;

	ligne_ad_cpt ad_cpt%ROWTYPE;

	cpte_base INTEGER;

	solde_dispo_cpte NUMERIC(30,6);

BEGIN

  -- Recuperation l'id de l'agence
  v_id_agc := numagc();

  -- Récupération infos taxe associe a l'operation comptable
  SELECT INTO v_info_tax t.id, t.taux FROM ad_oper_taxe opt INNER JOIN adsys_taxes t ON opt.id_taxe = t.id WHERE opt.id_ag = t.id_ag AND t.id_ag = v_id_agc AND opt.type_oper = num_ope;
  --RAISE NOTICE 'Id = % Taux de Tax = % ',v_info_tax.id, v_info_tax.taux;

  -- Recherche du libellé et du compte au crédit de type opération
  SELECT INTO type_ope libel_ope , num_cpte FROM ad_cpt_ope a, ad_cpt_ope_cptes b WHERE a.id_ag=b.id_ag AND a.id_ag=v_id_agc AND a.type_operation = num_ope AND a.type_operation=b.type_operation AND b.sens = 'c';
  --RAISE NOTICE 'Libel Operation % - Compte au Crédit %',type_ope.libel_ope,type_ope.num_cpte;

  -- Récupération de la devise du compte au crédit
  SELECT INTO devise_cpte_cr devise FROM ad_cpt_comptable WHERE id_ag=v_id_agc AND num_cpte_comptable = type_ope.num_cpte;
  --RAISE NOTICE 'Devise du compte au credit = %',devise_cpte_cr;

  -- Récupération du journal associé si le compte au crédit est principal
  SELECT INTO jou2 recupeJournal(type_ope.num_cpte);
  --RAISE NOTICE 'Journal associé si le compte au crédit est principal = %',jou2;

  -- Recherche du numéro de l'exercice contenant la date de prélèvement
  SELECT INTO exo id_exo_compta FROM ad_exercices_compta WHERE id_ag=v_id_agc AND date_deb_exo<= date(date_prelev) AND date_fin_exo >= date(date_prelev);

  -- Récupération du nombre de devises
  SELECT INTO nbre_devises count(*) from devise WHERE id_ag=v_id_agc;
  --RAISE NOTICE 'Nombre devise = %',nbre_devises;

  IF nbre_devises = 1 THEN
    mode_multidev := false;
  ELSE
    mode_multidev := true;
  END IF;
  --RAISE NOTICE 'Is multi devise = %',mode_multidev;

  -- Récupération de la devise de référence
  SELECT INTO code_dev_ref code_devise_reference FROM ad_agc WHERE id_ag=v_id_agc;
  --RAISE NOTICE 'Devise de reference = %',code_dev_ref;

  cur_date := 'now';

  IF appl_frais_gs = 'f' THEN -- Pour tous les comptes epargne avec etat_cpte in (1,4) comme se faisait normalement
	  -- Recupere des infos sur les compte épargne à prélever et leurs produits associés
	  OPEN Cpt_Prelev FOR
		  SELECT a.id_cpte, a.id_titulaire,a.solde, a.devise, a.num_complet_cpte, b.frais_tenue_cpt as total_frais_tenue_cpt, b.cpte_cpta_prod_ep
		  FROM ad_cpt a, adsys_produit_epargne b WHERE a.id_ag=b.id_ag AND a.id_ag=v_id_agc AND a.id_prod = b.id AND (frequence_tenue_cpt BETWEEN 1 AND freq_tenue_cpt)
		  AND a.etat_cpte in (1,4) AND b.frais_tenue_cpt > 0 ORDER BY a.id_titulaire;
  ELSE -- Pour tous les comptes epargne avec etat_cpte in (1,4) autre que les groupes solidaires (Ticket AT-40)
	  -- Recupere des infos sur les compte épargne à prélever et leurs produits associés
	  OPEN Cpt_Prelev FOR
		  SELECT a.id_cpte, a.id_titulaire, a.solde, a.devise, a.num_complet_cpte, b.frais_tenue_cpt as total_frais_tenue_cpt, b.cpte_cpta_prod_ep
		  FROM ad_cli c, ad_cpt a, adsys_produit_epargne b WHERE a.id_titulaire = c.id_client AND a.id_ag = b.id_ag AND a.id_ag = v_id_agc AND a.id_prod = b.id
		  AND c.statut_juridique != 4 AND (frequence_tenue_cpt BETWEEN 1 AND freq_tenue_cpt)
		  AND a.etat_cpte in (1,4) AND b.frais_tenue_cpt > 0 ORDER BY a.id_titulaire;
  END IF;

  FETCH Cpt_Prelev INTO ligne;

  -- Ajout historique à condition qu'on ait trouvé des comptes à traiter
  -- On utilise la date de prélèvement (qui est normalement la date pour laquelle on exécute le batch),
  -- et la dernière minute de la journée, afin
  IF FOUND THEN
    INSERT INTO ad_his (type_fonction, login, infos, date, id_ag)
    VALUES (212, 'admin','Prelevement des frais de tenue de compte via batch', date(now()), v_id_agc);
    --RAISE NOTICE 'ajout historique!';
  END IF;


  WHILE FOUND LOOP

    --calculer le tax sur frais de tenue si necessaire
    IF v_info_tax.id IS NOT NULL THEN
	v_mnt_tax := v_info_tax.taux * ligne.total_frais_tenue_cpt;
    END IF;
    --RAISE NOTICE '==> Montant Tax Calculé = [ % ]',v_mnt_tax;

    --calculer le solde disponible du compte en enlevant les frais de tenue + tax sur frais de tenue

    SELECT INTO solde_dispo_cpte(solde - mnt_bloq - mnt_min_cpte + decouvert_max - mnt_bloq_cre - ligne.total_frais_tenue_cpt - v_mnt_tax)
    FROM ad_cpt WHERE id_ag=v_id_agc AND id_cpte = ligne.id_cpte;

    --RAISE NOTICE 'Solde dispo pour compte % avec solde initial % = %', ligne.id_cpte, ligne.solde, solde_dispo_cpte;

    IF (solde_dispo_cpte >= 0) THEN

      -- RECUPERATION DE LA DEVISE DU COMPTE ASSOCIE AU PRODUIT
      SELECT INTO devise_cpte_debit devise FROM ad_cpt_comptable WHERE id_ag=v_id_agc AND num_cpte_comptable = ligne.cpte_cpta_prod_ep;
      --RAISE NOTICE 'DEVISE DU COMPTE ASSOCIE AU PRODUIT = %',devise_cpte_debit;

      -- Construction du numéro de compte à débiter
      IF devise_cpte_debit IS NULL THEN
        num_cpte_debit := ligne.cpte_cpta_prod_ep || '.' || ligne.devise;
      ELSE
        num_cpte_debit := ligne.cpte_cpta_prod_ep;
      END IF;
      --RAISE NOTICE 'numéro de compte à débiter = %',num_cpte_debit;

      -- Récupération du journal associé si le compte est principal
      SELECT INTO jou1 recupeJournal(num_cpte_debit);
      --RAISE NOTICE 'Journal associé si le compte est principal = %',jou1;

       IF jou1 IS NOT NULL AND jou2 IS NOT NULL AND jou1 != jou2 THEN
		--RAISE NOTICE '---------------------------------IF jou1 is not null and jou2 is not null and jou1 != jou2--------------------------------';

		-- num_cpte_debit ET COMPTE AU CREDIT SONT PINCIPAUX ET DE JOURNAUX DIFFERENTS , ON RECUPERE ALORS LE COMPTE DE LIAISON

		SELECT INTO cpte_liaison num_cpte_comptable FROM ad_journaux_liaison WHERE (id_ag=v_id_agc AND id_jou1=jou1 AND id_jou2=jou2) OR (id_jou1=jou2 AND id_jou2=jou1);
		--RAISE NOTICE 'Compte de liason entre journal % et journal %  est %', jou1, jou2, cpte_liaison;

		-- DEVISE DU COMPTE DE LIAISON
		SELECT INTO devise_cpte_liaison devise FROM ad_cpt_comptable WHERE id_ag=v_id_agc AND num_cpte_comptable = cpte_liaison;
		--RAISE NOTICE 'Devise du compte de liason : % ', devise_cpte_liaison;


		---------- DEBIT COMPTE CLIENT PAR CREDIT DU COMPTE DE LIAISON -----------------------
		IF ligne.devise = devise_cpte_liaison THEN  ----- num_cpte_debit et cpte_liaison sont de la même devise
			--RAISE NOTICE 'num_cpte_debit % et cpte_liaison % sont de la même devise',ligne.devise,devise_cpte_liaison;

			-- prelevement des frais sur le compte du client
			UPDATE ad_cpt SET solde = solde - ligne.total_frais_tenue_cpt WHERE (id_cpte = ligne.id_cpte);
			--RAISE NOTICE 'prelevement des frais sur le compte du client';

			-- Ecriture comptable
			INSERT INTO ad_ecriture(id_his,id_ag, date_comptable, libel_ecriture, id_jou, id_exo, ref_ecriture,info_ecriture,type_operation)
			VALUES ((SELECT currval('ad_his_id_his_seq')),v_id_agc, date(date_prelev), type_ope.libel_ope, jou1,
			exo.id_exo_compta, makeNumEcriture(jou1, exo.id_exo_compta),ligne.id_cpte,num_ope);
			--RAISE NOTICE 'Ecriture comptable';

			-- Mouvement comptable au débit
			INSERT INTO ad_mouvement (id_ecriture,id_ag, compte, cpte_interne_cli, sens, montant, devise, date_valeur)
			VALUES ((SELECT currval('ad_ecriture_seq')),v_id_agc,num_cpte_debit,ligne.id_cpte, 'd', ligne.total_frais_tenue_cpt, ligne.devise, date(date_prelev));
			--RAISE NOTICE 'Mouvement comptable au débit';

			-- Mouvement comptable au crédit
			INSERT INTO ad_mouvement (id_ecriture,id_ag,compte, cpte_interne_cli, sens, montant, devise, date_valeur)
			VALUES ((SELECT currval('ad_ecriture_seq')),v_id_agc, cpte_liaison, NULL, 'c', ligne.total_frais_tenue_cpt,ligne.devise, date(date_prelev));
			--RAISE NOTICE 'Mouvement comptable au crédit';

			-- Mise à jour des soldes comptables
			UPDATE ad_cpt_comptable set solde = solde - ligne.total_frais_tenue_cpt WHERE id_ag=v_id_agc AND num_cpte_comptable = num_cpte_debit;
			UPDATE ad_cpt_comptable set solde = solde + ligne.total_frais_tenue_cpt WHERE id_ag=v_id_agc AND num_cpte_comptable = cpte_liaison;
			--RAISE NOTICE 'Mise à jour des soldes comptables';


		ELSE --------- num_cpte_debit et cpte_liaison n'ont pas la même devise, faire la conversion
			--RAISE NOTICE 'num_cpte_debit % et cpte_liaison % nont pas la même devise, faire la conversion',ligne.devise,devise_cpte_liaison;

			--------- si num_cpte_debit a la devise de référence et cpte_liaison une devise étrangère
			IF ligne.devise = code_dev_ref THEN
				--RAISE NOTICE ' debut si num_cpte_debit a la devise de référence et cpte_liaison une devise étrangère';

				SELECT INTO cpt_pos_ch cpte_position_change || '.' || devise_cpte_liaison FROM ad_agc WHERE id_ag=v_id_agc;
				SELECT INTO cpt_cv_pos_ch cpte_contreval_position_change || '.' || devise_cpte_liaison FROM ad_agc WHERE id_ag=v_id_agc;
				--RAISE NOTICE 'cpt_pos_ch = % cpt_cv_pos_ch = %',cpt_pos_ch,cpt_cv_pos_ch;

				-- prelevement des frais sur le compte du client
				UPDATE ad_cpt SET solde = solde - ligne.total_frais_tenue_cpt WHERE (id_cpte = ligne.id_cpte AND id_ag=v_id_agc);
				--RAISE NOTICE 'prelevement des frais sur le compte du client';

				-- Ecriture comptable
				INSERT INTO ad_ecriture(id_his,id_ag, date_comptable, libel_ecriture, id_jou, id_exo, ref_ecriture,info_ecriture,type_operation)
				VALUES ((SELECT currval('ad_his_id_his_seq')),v_id_agc, date(date_prelev), type_ope.libel_ope, jou1, exo.id_exo_compta, makeNumEcriture(jou1, exo.id_exo_compta),ligne.id_cpte,num_ope);
				--RAISE NOTICE 'Ecriture comptable';

				-- Mouvement comptable au débit
				INSERT INTO ad_mouvement (id_ecriture,id_ag, compte, cpte_interne_cli, sens, montant, devise, date_valeur)
				VALUES ((SELECT currval('ad_ecriture_seq')),v_id_agc,num_cpte_debit,ligne.id_cpte, 'd', ligne.total_frais_tenue_cpt, ligne.devise, date(date_prelev));
				--RAISE NOTICE 'Mouvement comptable au débit';

				-- Mouvement comptable au crédit de la c/v du compte de liaison
				INSERT INTO ad_mouvement (id_ecriture,id_ag, compte, cpte_interne_cli, sens, montant, devise, date_valeur)
				VALUES ((SELECT currval('ad_ecriture_seq')),v_id_agc, cpt_cv_pos_ch, NULL, 'c', ligne.total_frais_tenue_cpt,ligne.devise, date(date_prelev));
				--RAISE NOTICE 'Mouvement comptable au crédit de la c/v du compte de liaison';

				-- montant dans la devise du compte de liaison
				SELECT INTO cv_frais_tenue_cpte CalculeCV(ligne.total_frais_tenue_cpt, ligne.devise, devise_cpte_liaison);
				--RAISE NOTICE 'montant dans la devise du compte de liaison = %',cv_frais_tenue_cpte;

				-- Mouvement comptable au débit de la position de change du compte de liaison
				INSERT INTO ad_mouvement (id_ecriture,id_ag, compte, cpte_interne_cli, sens, montant, devise, date_valeur)
				VALUES ((SELECT currval('ad_ecriture_seq')),v_id_agc,cpt_pos_ch,NULL, 'd', cv_frais_tenue_cpte,devise_cpte_liaison,date(date_prelev));
				--RAISE NOTICE 'Mouvement comptable au débit de la position de change du compte de liaison';

				-- Mouvement comptable au crédit du compte de liaison
				INSERT INTO ad_mouvement (id_ecriture,id_ag, compte, cpte_interne_cli, sens, montant, devise, date_valeur)
				VALUES ((SELECT currval('ad_ecriture_seq')),v_id_agc,cpte_liaison ,NULL, 'c',cv_frais_tenue_cpte, devise_cpte_liaison, date(date_prelev));
				--RAISE NOTICE 'Mouvement comptable au crédit du compte de liaison';

				-- Mise à jour des soldes comptables
				UPDATE ad_cpt_comptable set solde = solde - ligne.total_frais_tenue_cpt WHERE id_ag=v_id_agc AND num_cpte_comptable = num_cpte_debit;
				UPDATE ad_cpt_comptable set solde = solde + ligne.total_frais_tenue_cpt WHERE id_ag=v_id_agc AND num_cpte_comptable = cpt_cv_pos_ch;
				UPDATE ad_cpt_comptable set solde = solde - cv_frais_tenue_cpte WHERE id_ag=v_id_agc AND num_cpte_comptable = cpt_pos_ch;
				UPDATE ad_cpt_comptable set solde = solde + cv_frais_tenue_cpte WHERE id_ag=v_id_agc AND num_cpte_comptable = cpte_liaison;
				--RAISE NOTICE 'Mise à jour des soldes comptables';

				--RAISE NOTICE ' fin si num_cpte_debit a la devise de référence et cpte_liaison une devise étrangère';

			END IF; -- FIN IF ligne.devise = code_dev_ref

			-------- si cpte_liaison a la devise de référence et num_cpte_debit une devise étrangère
			IF devise_cpte_liaison = code_dev_ref THEN
				--RAISE NOTICE ' debut si cpte_liaison a la devise de référence et num_cpte_debit une devise étrangère';

				SELECT INTO cpt_pos_ch cpte_position_change || '.' || ligne.devise FROM ad_agc WHERE id_ag=v_id_agc;
				SELECT INTO cpt_cv_pos_ch cpte_contreval_position_change || '.' || ligne.devise FROM ad_agc WHERE id_ag=v_id_agc;
				--RAISE NOTICE 'cpt_pos_ch = % cpt_cv_pos_ch = % ',cpt_pos_ch,cpt_cv_pos_ch;

				-- prelevement des frais sur le compte du client
				UPDATE ad_cpt SET solde = solde - ligne.total_frais_tenue_cpt WHERE (id_ag=v_id_agc AND id_cpte = ligne.id_cpte);
				--RAISE NOTICE 'prelevement des frais sur le compte du client';

				-- montant dans la devise du compte de liaison
				SELECT INTO cv_frais_tenue_cpte CalculeCV(ligne.total_frais_tenue_cpt, ligne.devise, code_dev_ref);
				--RAISE NOTICE 'montant dans la devise du compte de liaison = %',cv_frais_tenue_cpte;

				-- Ecriture comptable
				INSERT INTO ad_ecriture(id_his,id_ag, date_comptable, libel_ecriture, id_jou, id_exo, ref_ecriture,info_ecriture,type_operation)
				VALUES ((SELECT currval('ad_his_id_his_seq')),v_id_agc, date(date_prelev), type_ope.libel_ope, jou1,
				exo.id_exo_compta, makeNumEcriture(jou1, exo.id_exo_compta),ligne.id_cpte,num_ope);
				--RAISE NOTICE 'Ecriture comptable';

				-- Mouvement comptable au crédit du compte de liaison
				INSERT INTO ad_mouvement (id_ecriture,id_ag, compte, cpte_interne_cli, sens, montant, devise, date_valeur)
				VALUES ((SELECT currval('ad_ecriture_seq')),v_id_agc,cpte_liaison ,NULL, 'c',cv_frais_tenue_cpte,
				code_dev_ref, date(date_prelev));
				--RAISE NOTICE 'Mouvement comptable au crédit du compte de liaison';

				-- Mouvement comptable au débit de la c/v de num_cpte_debit
				INSERT INTO ad_mouvement (id_ecriture,id_ag, compte, cpte_interne_cli, sens, montant, devise, date_valeur)
				VALUES ((SELECT currval('ad_ecriture_seq')),v_id_agc,cpt_cv_pos_ch ,NULL, 'd',cv_frais_tenue_cpte,
				code_dev_ref, date(date_prelev));
				--RAISE NOTICE 'Mouvement comptable au débit de la c/v de num_cpte_debit';

				-- Mouvement comptable au débit de num_cpte_debit
				INSERT INTO ad_mouvement (id_ecriture,id_ag, compte, cpte_interne_cli, sens, montant, devise, date_valeur)
				VALUES ((SELECT currval('ad_ecriture_seq')),v_id_agc,num_cpte_debit,ligne.id_cpte, 'd',
				ligne.total_frais_tenue_cpt, ligne.devise, date(date_prelev));
				--RAISE NOTICE 'Mouvement comptable au débit de num_cpte_debit';

				-- Mouvement comptable au crédit de la position de change de num_cpte_debit
				INSERT INTO ad_mouvement (id_ecriture,id_ag,compte, cpte_interne_cli, sens, montant, devise, date_valeur)
				VALUES ((SELECT currval('ad_ecriture_seq')),v_id_agc,cpt_pos_ch,NULL, 'c', ligne.total_frais_tenue_cpt,
				ligne.devise, date(date_prelev));
				--RAISE NOTICE 'Mouvement comptable au crédit de la position de change de num_cpte_debit';

				-- Mise à jour des soldes comptables
				UPDATE ad_cpt_comptable set solde = solde - ligne.total_frais_tenue_cpt WHERE id_ag=v_id_agc AND num_cpte_comptable = num_cpte_debit;
				UPDATE ad_cpt_comptable set solde = solde + ligne.total_frais_tenue_cpt WHERE id_ag=v_id_agc AND num_cpte_comptable = cpt_pos_ch;
				UPDATE ad_cpt_comptable set solde = solde - cv_frais_tenue_cpte WHERE id_ag=v_id_agc AND num_cpte_comptable = cpt_cv_pos_ch;
				UPDATE ad_cpt_comptable set solde = solde + cv_frais_tenue_cpte WHERE id_ag=v_id_agc AND num_cpte_comptable = cpte_liaison;
				--RAISE NOTICE 'Mise à jour des soldes comptables';

				--RAISE NOTICE ' fin si cpte_liaison a la devise de référence et num_cpte_debit une devise étrangère';

			END IF; -- FIN IF devise_cpte_liaison = code_dev_ref

			-------- si ni cpte_liaison ni num_cpte_debit n'a la devise de référence
			IF ligne.devise != code_dev_ref AND devise_cpte_liaison != code_dev_ref THEN
				--RAISE NOTICE ' debut si ni cpte_liaison ni num_cpte_debit na la devise de référence';

				-- prelevement des frais sur le compte du client
				UPDATE ad_cpt SET solde = solde - ligne.total_frais_tenue_cpt WHERE (id_ag=v_id_agc AND id_cpte = ligne.id_cpte);
				--RAISE NOTICE 'prelevement des frais sur le compte du client';

				-- Ecriture comptable
				INSERT INTO ad_ecriture(id_his,id_ag, date_comptable, libel_ecriture, id_jou, id_exo, ref_ecriture,info_ecriture,type_operation)
				VALUES ((SELECT currval('ad_his_id_his_seq')),v_id_agc, date(date_prelev), type_ope.libel_ope, jou1, exo.id_exo_compta, makeNumEcriture(jou1, exo.id_exo_compta),ligne.id_cpte,num_ope);
				--RAISE NOTICE 'Ecriture comptable';

				-- Mouvement comptable au débit de num_cpte_debit
				INSERT INTO ad_mouvement (id_ecriture,id_ag, compte, cpte_interne_cli, sens, montant, devise, date_valeur)
				VALUES ((SELECT currval('ad_ecriture_seq')),v_id_agc,num_cpte_debit,ligne.id_cpte, 'd', ligne.total_frais_tenue_cpt, ligne.devise, date(date_prelev));
				--RAISE NOTICE 'Mouvement comptable au débit de num_cpte_debit';

				UPDATE ad_cpt_comptable set solde = solde - ligne.total_frais_tenue_cpt WHERE id_ag=v_id_agc AND num_cpte_comptable = num_cpte_debit;
				--RAISE NOTICE 'set solde = solde - ligne.total_frais_tenue_cpt';

				-- position de change de la devise de num_cpte_debit
				SELECT INTO cpt_pos_ch cpte_position_change || '.' || ligne.devise FROM ad_agc WHERE id_ag=v_id_agc;
				--RAISE NOTICE 'position de change de la devise de num_cpte_debit';

				-- Mouvement comptable au crédit de la position de change de num_cpte_debit
				INSERT INTO ad_mouvement (id_ecriture,id_ag, compte, cpte_interne_cli, sens, montant, devise, date_valeur)
				VALUES ((SELECT currval('ad_ecriture_seq')),v_id_agc,cpt_pos_ch,NULL, 'c', ligne.total_frais_tenue_cpt, ligne.devise, date(date_prelev));
				--RAISE NOTICE 'Mouvement comptable au crédit de la position de change de num_cpte_debit';

				UPDATE ad_cpt_comptable set solde = solde + ligne.total_frais_tenue_cpt WHERE id_ag=v_id_agc AND num_cpte_comptable = cpt_pos_ch;
				--RAISE NOTICE 'set solde = solde + ligne.total_frais_tenue_cpt';

				-- montant dans la devise de référence
				SELECT INTO cv_frais_tenue_cpte CalculeCV(ligne.total_frais_tenue_cpt, ligne.devise, code_dev_ref);
				--RAISE NOTICE 'montant dans la devise de référence = %',cv_frais_tenue_cpte;

				-- c/v de la devise de num_cpte_debit
				SELECT INTO cpt_cv_pos_ch cpte_contreval_position_change || '.' || ligne.devise FROM ad_agc WHERE id_ag=v_id_agc;
				--RAISE NOTICE 'c/v de la devise de num_cpte_debit = %',cpt_cv_pos_ch;

				-- Mouvement comptable au débit de la c/v de num_cpte_debit
				INSERT INTO ad_mouvement (id_ecriture,id_ag, compte, cpte_interne_cli, sens, montant, devise, date_valeur)
				VALUES ((SELECT currval('ad_ecriture_seq')),v_id_agc, cpt_cv_pos_ch,NULL, 'd', cv_frais_tenue_cpte, code_dev_ref, date(date_prelev));
				--RAISE NOTICE 'Mouvement comptable au débit de la c/v de num_cpte_debit';

				UPDATE ad_cpt_comptable set solde = solde - cv_frais_tenue_cpte WHERE id_ag=v_id_agc AND num_cpte_comptable = cpt_cv_pos_ch;
				--RAISE NOTICE 'set solde = solde - cv_frais_tenue_cpte';

				-- c/v de la devise du compte de liaison
				SELECT INTO cpt_cv_pos_ch cpte_contreval_position_change || '.' || devise_cpte_liaison FROM ad_agc WHERE id_ag=v_id_agc;
				--RAISE NOTICE 'c/v de la devise du compte de liaison = %',cpt_cv_pos_ch;

				-- Mouvement comptable au crédit de la c/v du compte de liaison
				INSERT INTO ad_mouvement (id_ecriture,id_ag, compte, cpte_interne_cli, sens, montant, devise, date_valeur)
				VALUES ((SELECT currval('ad_ecriture_seq')),v_id_agc, cpt_cv_pos_ch, NULL, 'c', cv_frais_tenue_cpte, code_dev_ref, date(date_prelev));
				--RAISE NOTICE 'Mouvement comptable au crédit de la c/v du compte de liaison';

				UPDATE ad_cpt_comptable set solde = solde + cv_frais_tenue_cpte WHERE id_ag=v_id_agc AND num_cpte_comptable = cpt_cv_pos_ch;
				--RAISE NOTICE 'set solde = solde + cv_frais_tenue_cpte';

				-- montant dans la devise du compte de liaison
				SELECT INTO cv_frais_tenue_cpte CalculeCV(ligne.total_frais_tenue_cpt, ligne.devise, devise_cpte_liaison);
				--RAISE NOTICE 'montant dans la devise du compte de liaison = %',cv_frais_tenue_cpte;

				-- Mouvement comptable au crédit du compte de liaison
				INSERT INTO ad_mouvement (id_ecriture,id_ag, compte, cpte_interne_cli, sens, montant, devise, date_valeur)
				VALUES ((SELECT currval('ad_ecriture_seq')),v_id_agc, cpte_liaison, NULL, 'c', cv_frais_tenue_cpte, devise_cpte_liaison, date(date_prelev));
				--RAISE NOTICE 'Mouvement comptable au crédit du compte de liaison';

				UPDATE ad_cpt_comptable set solde = solde + cv_frais_tenue_cpte WHERE id_ag=v_id_agc AND num_cpte_comptable = cpte_liaison;
				--RAISE NOTICE 'set solde = solde + cv_frais_tenue_cpte';

				-- position de change de la devise du compte de liaison
				SELECT INTO cpt_pos_ch cpte_position_change || '.' || devise_cpte_liaison FROM ad_agc WHERE id_ag=v_id_agc;
				--RAISE NOTICE 'position de change de la devise du compte de liaison = %',cpt_pos_ch;

				-- Mouvement comptable au débit de la position de change de la devise du compte de liaison
				INSERT INTO ad_mouvement (id_ecriture,id_ag, compte, cpte_interne_cli, sens, montant, devise, date_valeur)
				VALUES ((SELECT currval('ad_ecriture_seq')),v_id_agc, cpt_pos_ch, NULL, 'd', cv_frais_tenue_cpte, devise_cpte_liaison, date(date_prelev));
				--RAISE NOTICE 'Mouvement comptable au débit de la position de change de la devise du compte de liaison';

				UPDATE ad_cpt_comptable set solde = solde - cv_frais_tenue_cpte WHERE id_ag=v_id_agc AND num_cpte_comptable = cpt_pos_ch;
				--RAISE NOTICE 'set solde = solde - cv_frais_tenue_cpte';

				--RAISE NOTICE ' fin si ni cpte_liaison ni num_cpte_debit na la devise de référence';

			END IF; -- FIN IF ligne.devise != code_dev_ref AND devise_cpte_liaison != code_dev_ref

			--RAISE NOTICE 'FIN  IF ligne.devise = devise_cpte_liaison';

		END IF;  -- FIN  IF ligne.devise = devise_cpte_liaison

		----------- FIN DEBIT COMPTE CLIENT PAR CREDIT COMPTE DE LIAISON -----------------------
		--RAISE NOTICE '----------- FIN DEBIT COMPTE CLIENT PAR CREDIT COMPTE DE LIAISON -----------------------';


		----------- DEBIT COMPTE DE LIAISON PAR CREDIT COMPTE AU CREDIT DANS LE SECOND JOURNAL ------------------------
		--RAISE NOTICE '----------- DEBIT COMPTE DE LIAISON PAR CREDIT COMPTE AU CREDIT DANS LE SECOND JOURNAL ------------------------';

		IF devise_cpte_liaison = devise_cpte_cr THEN  ----- COMPTE AU CREDIT ET cpte_liaison SONT DE LA MEME DEVISE
			--RAISE NOTICE 'COMPTE AU CREDIT ET cpte_liaison SONT DE LA MEME DEVISE';

			-- MONTANT DANS LA DEVISE DU COMPTE DE LIASON
			SELECT INTO cv_frais_tenue_cpte CalculeCV(ligne.total_frais_tenue_cpt, ligne.devise, devise_cpte_liaison);
			--RAISE NOTICE 'MONTANT DANS LA DEVISE DU COMPTE DE LIASON = %',cv_frais_tenue_cpte;

			-- PASSAGE ECRITURE COMPTABLE
			INSERT INTO ad_ecriture(id_his,id_ag, date_comptable, libel_ecriture, id_jou, id_exo, ref_ecriture,info_ecriture,type_operation)
			VALUES ((SELECT currval('ad_his_id_his_seq')),v_id_agc,date(date_prelev), type_ope.libel_ope, jou2,
			exo.id_exo_compta, makeNumEcriture(jou2, exo.id_exo_compta),ligne.id_cpte,num_ope);
			--RAISE NOTICE 'PASSAGE ECRITURE COMPTABLE';

			-- MOUVEMENT COMPTABLE AU DEBIT DU COMPTE DE LIAISON
			INSERT INTO ad_mouvement (id_ecriture,id_ag, compte, cpte_interne_cli, sens, montant, devise, date_valeur)
			VALUES ((SELECT currval('ad_ecriture_seq')),v_id_agc,cpte_liaison,NULL,'d',cv_frais_tenue_cpte,
			devise_cpte_liaison,date(date_prelev));
			--RAISE NOTICE 'MOUVEMENT COMPTABLE AU DEBIT DU COMPTE DE LIAISON';

			UPDATE ad_cpt_comptable set solde = solde - cv_frais_tenue_cpte WHERE id_ag=v_id_agc AND num_cpte_comptable = cpte_liaison;
			--RAISE NOTICE 'set solde = solde - cv_frais_tenue_cpte';

			-- MOUVEMENT COMPTABLE AU CREDIT DU COMPTE DE CREDIT
			INSERT INTO ad_mouvement (id_ecriture,id_ag, compte, cpte_interne_cli, sens, montant, devise, date_valeur)
			VALUES ((SELECT currval('ad_ecriture_seq')),v_id_agc,type_ope.num_cpte,NULL,'c',cv_frais_tenue_cpte,
			devise_cpte_cr,date(date_prelev));
			--RAISE NOTICE 'MOUVEMENT COMPTABLE AU CREDIT DU COMPTE DE CREDIT';

			UPDATE ad_cpt_comptable set solde = solde + cv_frais_tenue_cpte WHERE id_ag=v_id_agc AND num_cpte_comptable = type_ope.num_cpte;
			--RAISE NOTICE 'set solde = solde + cv_frais_tenue_cpte';


		ELSE      ----- COMPTE AU CREDIT ET cpte_liaison N'ONT PAS LA MEME DEVISE , FAIRE DONC LA CONVERSION
			--RAISE NOTICE 'COMPTE AU CREDIT ET cpte_liaison NONT PAS LA MEME DEVISE , FAIRE DONC LA CONVERSION';

			IF devise_cpte_liaison = code_dev_ref THEN  -- CPTE DE LIAISON A LA DEVISE DE REFERENCE ET CPTE AU CREDIT DEVISE ETRANGERE
				--RAISE NOTICE 'CPTE DE LIAISON A LA DEVISE DE REFERENCE ET CPTE AU CREDIT DEVISE ETRANGERE';

				SELECT INTO cpt_pos_ch cpte_position_change || '.' || devise_cpte_cr FROM ad_agc WHERE id_ag=v_id_agc;
				SELECT INTO cpt_cv_pos_ch cpte_contreval_position_change || '.' || devise_cpte_cr FROM ad_agc WHERE id_ag=v_id_agc;
				--RAISE NOTICE 'cpt_pos_ch = % cpt_cv_pos_ch = % ',cpt_pos_ch,cpt_cv_pos_ch;

				-- MONTANT DANS LA DEVISE DU COMPTE DE LIASON (DEVISE DE REFERENCE )
				SELECT INTO cv_frais_tenue_cpte CalculeCV(ligne.total_frais_tenue_cpt, ligne.devise, devise_cpte_liaison);
				--RAISE NOTICE 'MONTANT DANS LA DEVISE DU COMPTE DE LIASON (DEVISE DE REFERENCE ) = %',cv_frais_tenue_cpte;

				-- PASSAGE ECRITURE COMPTABLE
				INSERT INTO ad_ecriture(id_his,id_ag, date_comptable, libel_ecriture, id_jou, id_exo, ref_ecriture,info_ecriture,type_operation)
				VALUES ((SELECT currval('ad_his_id_his_seq')),v_id_agc, date(date_prelev), type_ope.libel_ope, jou2,
				exo.id_exo_compta, makeNumEcriture(jou2, exo.id_exo_compta),ligne.id_cpte,num_ope);
				--RAISE NOTICE 'PASSAGE ECRITURE COMPTABLE';

				-- MOUVEMENT AU DEBIT DU COMPTE DE LIAISON
				INSERT INTO ad_mouvement (id_ecriture,id_ag, compte, cpte_interne_cli, sens, montant, devise, date_valeur)
				VALUES ((SELECT currval('ad_ecriture_seq')),v_id_agc, cpte_liaison, NULL, 'd', cv_frais_tenue_cpte,
				devise_cpte_liaison, date(date_prelev));
				--RAISE NOTICE 'MOUVEMENT AU DEBIT DU COMPTE DE LIAISON';

				UPDATE ad_cpt_comptable set solde = solde - cv_frais_tenue_cpte WHERE id_ag=v_id_agc AND num_cpte_comptable = cpte_liaison;
				--RAISE NOTICE 'set solde = solde - cv_frais_tenue_cpte';

				-- MOUVEMENT COMPTABLE AU CREDIT DE LA c/v DU COMPTE DE CREDIT DE L'OPERATION
				INSERT INTO ad_mouvement (id_ecriture,id_ag, compte, cpte_interne_cli, sens, montant, devise, date_valeur)
				VALUES ((SELECT currval('ad_ecriture_seq')),v_id_agc, cpt_cv_pos_ch, NULL, 'c', cv_frais_tenue_cpte,
				code_dev_ref, date(date_prelev));
				--RAISE NOTICE 'MOUVEMENT COMPTABLE AU CREDIT DE LA c/v DU COMPTE DE CREDIT DE LOPERATION';

				UPDATE ad_cpt_comptable set solde = solde + cv_frais_tenue_cpte WHERE id_ag=v_id_agc AND num_cpte_comptable = cpt_cv_pos_ch;
				--RAISE NOTICE 'set solde = solde + cv_frais_tenue_cpte';


				-- MONTANT DANS LA DEVISE DU COMPTE DE CREDIT DE L'OPERATION
				SELECT INTO cv_frais_tenue_cpte CalculeCV(ligne.total_frais_tenue_cpt, ligne.devise, devise_cpte_cr);
				--RAISE NOTICE 'MONTANT DANS LA DEVISE DU COMPTE DE CREDIT DE LOPERATION';

				-- MOUVEMENT COMPTABLE AU DEBIT DE LA POSITION DE CHANGE DU COMPTE AU CREDIT DE L'OPERATION
				INSERT INTO ad_mouvement (id_ecriture,id_ag, compte, cpte_interne_cli, sens, montant, devise, date_valeur)
				VALUES ((SELECT currval('ad_ecriture_seq')),v_id_agc,cpt_pos_ch, NULL, 'd', cv_frais_tenue_cpte,
				devise_cpte_cr,date(date_prelev));
				--RAISE NOTICE 'MOUVEMENT COMPTABLE AU DEBIT DE LA POSITION DE CHANGE DU COMPTE AU CREDIT DE LOPERATION';

				UPDATE ad_cpt_comptable set solde = solde - cv_frais_tenue_cpte WHERE id_ag=v_id_agc AND num_cpte_comptable = cpt_pos_ch;
				--RAISE NOTICE 'set solde = solde - cv_frais_tenue_cpte';

				-- MOUVEMENT COMPTABLE AU CREDIT DU COMPTE DE CREDIT DE L'OPERATION
				INSERT INTO ad_mouvement (id_ecriture,id_ag, compte, cpte_interne_cli, sens, montant, devise, date_valeur)
				VALUES ((SELECT currval('ad_ecriture_seq')),v_id_agc, type_ope.num_cpte , NULL, 'c',cv_frais_tenue_cpte,
				devise_cpte_cr, date(date_prelev));
				--RAISE NOTICE 'MOUVEMENT COMPTABLE AU CREDIT DU COMPTE DE CREDIT DE LOPERATION';

				UPDATE ad_cpt_comptable set solde = solde + cv_frais_tenue_cpte WHERE id_ag=v_id_agc AND num_cpte_comptable = type_ope.num_cpte;
				--RAISE NOTICE 'set solde = solde + cv_frais_tenue_cpte';

				--RAISE NOTICE 'FIN IF devise_cpte_liaison = code_dev_ref';

			END IF; -- FIN IF devise_cpte_liaison = code_dev_ref


			IF devise_cpte_cr = code_dev_ref THEN -- SI CPTE AU CREDIT A LA DEVISE DE REFERENCE ET CPTE LIAISON UNE DEVISE ETRANGERE
				--RAISE NOTICE 'SI CPTE AU CREDIT A LA DEVISE DE REFERENCE ET CPTE LIAISON UNE DEVISE ETRANGERE';

				SELECT INTO cpt_pos_ch cpte_position_change || '.' || devise_cpte_liaison FROM ad_agc WHERE id_ag=v_id_agc;
				SELECT INTO cpt_cv_pos_ch cpte_contreval_position_change || '.' || devise_cpte_liaison FROM ad_agc WHERE id_ag=v_id_agc;
				--RAISE NOTICE 'cpt_pos_ch = % cpt_cv_pos_ch = %',cpt_pos_ch,cpt_cv_pos_ch;

				-- PASSAGE ECRITURE COMPTABLE
				INSERT INTO ad_ecriture(id_his,id_ag, date_comptable, libel_ecriture, id_jou, id_exo, ref_ecriture,info_ecriture,type_operation)
				VALUES ((SELECT currval('ad_his_id_his_seq')),v_id_agc, date(date_prelev), type_ope.libel_ope, jou2,
				exo.id_exo_compta, makeNumEcriture(jou2, exo.id_exo_compta),ligne.id_cpte,num_ope);
				--RAISE NOTICE 'PASSAGE ECRITURE COMPTABLE';

				-- MONATANT DANS LA DEVISE DU COMPTE AU CREDIT DE L'OPERATION ( DEVISE DE REFERENCE )
				SELECT INTO cv_frais_tenue_cpte CalculeCV(ligne.total_frais_tenue_cpt, ligne.devise, devise_cpte_cr);
				--RAISE NOTICE 'MONATANT DANS LA DEVISE DU COMPTE AU CREDIT DE LOPERATION ( DEVISE DE REFERENCE ) = %',cv_frais_tenue_cpte;

				-- MOUVEMENT AU CREDIT DU COMPTE DE CREDIT DE L'OPERATION
				INSERT INTO ad_mouvement (id_ecriture,id_ag, compte, cpte_interne_cli, sens, montant, devise, date_valeur)
				VALUES ((SELECT currval('ad_ecriture_seq')),v_id_agc,type_ope.num_cpte ,NULL, 'c',cv_frais_tenue_cpte,
				devise_cpte_cr, date(date_prelev));
				--RAISE NOTICE 'MOUVEMENT AU CREDIT DU COMPTE DE CREDIT DE LOPERATION';

				UPDATE ad_cpt_comptable set solde = solde + cv_frais_tenue_cpte WHERE id_ag=v_id_agc AND num_cpte_comptable = type_ope.num_cpte;
				--RAISE NOTICE 'set solde = solde + cv_frais_tenue_cpte';

				-- MOUVEMENT AU DEBIT DE LA c/v DU COMPTE DE LIAISON
				INSERT INTO ad_mouvement (id_ecriture,id_ag, compte, cpte_interne_cli, sens, montant, devise, date_valeur)
				VALUES ((SELECT currval('ad_ecriture_seq')),v_id_agc,cpt_cv_pos_ch ,NULL, 'd',cv_frais_tenue_cpte,
				code_dev_ref, date(date_prelev));
				--RAISE NOTICE 'MOUVEMENT AU DEBIT DE LA c/v DU COMPTE DE LIAISON';

				UPDATE ad_cpt_comptable set solde = solde - cv_frais_tenue_cpte WHERE id_ag=v_id_agc AND num_cpte_comptable = cpt_cv_pos_ch;
				--RAISE NOTICE 'set solde = solde - cv_frais_tenue_cpte';

				-- MONATANT DANS LA DEVISE DU COMPTE DE LIAISON
				SELECT INTO cv_frais_tenue_cpte CalculeCV(ligne.total_frais_tenue_cpt, ligne.devise, devise_cpte_liaison);
				--RAISE NOTICE 'MONATANT DANS LA DEVISE DU COMPTE DE LIAISON = %',cv_frais_tenue_cpte;

				-- MOUVEMENT COMPTABLE AU DEBIT DU COMPTE DE LIAISON
				INSERT INTO ad_mouvement (id_ecriture,id_ag, compte, cpte_interne_cli, sens, montant, devise, date_valeur)
				VALUES ((SELECT currval('ad_ecriture_seq')),v_id_agc, cpte_liaison, NULL, 'd', cv_frais_tenue_cpte,
				devise_cpte_liaison, date(date_prelev));
				--RAISE NOTICE 'MOUVEMENT COMPTABLE AU DEBIT DU COMPTE DE LIAISON';

				UPDATE ad_cpt_comptable set solde = solde - cv_frais_tenue_cpte WHERE id_ag=v_id_agc AND num_cpte_comptable = cpte_liaison;
				--RAISE NOTICE 'set solde = solde - cv_frais_tenue_cpte';

				-- MOUVEMENT COMPTABLE AU CREDIT DA LA POSITION DE CHANGE DE LA DEVISE DU COMPTE DE LIAISON
				INSERT INTO ad_mouvement (id_ecriture,id_ag, compte, cpte_interne_cli, sens, montant, devise, date_valeur)
				VALUES ((SELECT currval('ad_ecriture_seq')),v_id_agc,cpt_pos_ch, NULL, 'c', cv_frais_tenue_cpte,
				devise_cpte_liaison, date(date_prelev));
				--RAISE NOTICE 'MOUVEMENT COMPTABLE AU CREDIT DA LA POSITION DE CHANGE DE LA DEVISE DU COMPTE DE LIAISON';

				UPDATE ad_cpt_comptable set solde = solde + cv_frais_tenue_cpte WHERE id_ag=v_id_agc AND num_cpte_comptable = cpt_pos_ch;
				--RAISE NOTICE 'set solde = solde + cv_frais_tenue_cpte';

				--RAISE NOTICE 'FIN IF devise_cpte_cr = code_dev_ref';

			END IF; -- FIN IF devise_cpte_cr = code_dev_ref

			IF devise_cpte_cr != code_dev_ref AND devise_cpte_liaison != code_dev_ref THEN

				-- DEVISE COMPTE DE LIAISON ET DEVISE COMPTE AU CREDIT SONT DIFFERENTES ET AUCUNE N'EST EGALE A LA DEVISE DE REFERENCE
				--RAISE NOTICE 'DEVISE COMPTE DE LIAISON ET DEVISE COMPTE AU CREDIT SONT DIFFERENTES ET AUCUNE NEST EGALE A LA DEVISE DE REFERENCE';

				-- PASSAGE ECRITURE COMPTABLE DANS jou2
				INSERT INTO ad_ecriture(id_his,id_ag, date_comptable, libel_ecriture, id_jou, id_exo, ref_ecriture,info_ecriture,type_operation)
				VALUES ((SELECT currval('ad_his_id_his_seq')),v_id_agc, date(date_prelev), type_ope.libel_ope, jou2,
				exo.id_exo_compta, makeNumEcriture(jou2, exo.id_exo_compta),ligne.id_cpte,num_ope);
				--RAISE NOTICE 'PASSAGE ECRITURE COMPTABLE DANS jou2';

				-- MONTANT DANS LA DEVISE DU COMPTE DE LIAISON
				SELECT INTO cv_frais_tenue_cpte CalculeCV(ligne.total_frais_tenue_cpt, ligne.devise, devise_cpte_liaison);
				--RAISE NOTICE 'MONTANT DANS LA DEVISE DU COMPTE DE LIAISON = %',cv_frais_tenue_cpte;

				-- MOUVEMENT COMPTABLE AU DEBIT DU COMPTE DE LIAISON
				INSERT INTO ad_mouvement (id_ecriture,id_ag, compte, cpte_interne_cli, sens, montant, devise, date_valeur)
				VALUES ((SELECT currval('ad_ecriture_seq')),v_id_agc, cpte_liaison, NULL, 'd', cv_frais_tenue_cpte,
				devise_cpte_liaison, date(date_prelev));
				--RAISE NOTICE 'MOUVEMENT COMPTABLE AU DEBIT DU COMPTE DE LIAISON';

				UPDATE ad_cpt_comptable set solde = solde - cv_frais_tenue_cpte WHERE id_ag=v_id_agc AND num_cpte_comptable = cpte_liaison;
				--RAISE NOTICE 'set solde = solde - cv_frais_tenue_cpte';

				-- POSITION DE CHANGE DE LA DEVISE DU COMPTE DE LIAISON
				SELECT INTO cpt_pos_ch cpte_position_change || '.' || devise_cpte_liaison FROM ad_agc WHERE id_ag=v_id_agc;
				--RAISE NOTICE 'POSITION DE CHANGE DE LA DEVISE DU COMPTE DE LIAISON';

				-- MOUVEMENT COMPTABLE AU CREDIT DE LA POSITION DE CHANGE DE LA DEVISE DU COMPTE DE LIAISON
				INSERT INTO ad_mouvement (id_ecriture,id_ag, compte, cpte_interne_cli, sens, montant, devise, date_valeur)
				VALUES ((SELECT currval('ad_ecriture_seq')),v_id_agc, cpt_pos_ch, NULL, 'c', cv_frais_tenue_cpte,
				devise_cpte_liaison, date(date_prelev));
				--RAISE NOTICE 'MOUVEMENT COMPTABLE AU CREDIT DE LA POSITION DE CHANGE DE LA DEVISE DU COMPTE DE LIAISON';

				UPDATE ad_cpt_comptable set solde = solde + cv_frais_tenue_cpte WHERE id_ag=v_id_agc AND num_cpte_comptable = cpt_pos_ch;
				--RAISE NOTICE 'set solde = solde + cv_frais_tenue_cpte';

				-- MONATNT DANS LA DEVISE DE REFERENCE
				SELECT INTO cv_frais_tenue_cpte CalculeCV(ligne.total_frais_tenue_cpt, ligne.devise, code_dev_ref);
				--RAISE NOTICE 'MONATNT DANS LA DEVISE DE REFERENCE = %',cv_frais_tenue_cpte;

				-- c/v DE LA DEVISE DU COMPTE DE LIAISON
				SELECT INTO cpt_cv_pos_ch cpte_contreval_position_change || '.' || devise_cpte_liaison FROM ad_agc;
				--RAISE NOTICE 'c/v DE LA DEVISE DU COMPTE DE LIAISON = %',cpt_cv_pos_ch;

				-- MOUVEMENT COMPTABLE AU DEBIT DE LA c/v DE LA DEVISE DU COMPTE DE LIAISON
				INSERT INTO ad_mouvement (id_ecriture,id_ag, compte, cpte_interne_cli, sens, montant, devise, date_valeur)
				VALUES ((SELECT currval('ad_ecriture_seq')),v_id_agc, cpt_cv_pos_ch,NULL, 'd', cv_frais_tenue_cpte, code_dev_ref, date(date_prelev));
				--RAISE NOTICE 'MOUVEMENT COMPTABLE AU DEBIT DE LA c/v DE LA DEVISE DU COMPTE DE LIAISON';

				UPDATE ad_cpt_comptable set solde = solde - cv_frais_tenue_cpte WHERE id_ag=v_id_agc AND num_cpte_comptable = cpt_cv_pos_ch;
				--RAISE NOTICE 'set solde = solde - cv_frais_tenue_cpte';

				-- c/v DE LA DEVISE DU COMPTE AU CREDIT DE L'OPERATION
				SELECT INTO cpt_cv_pos_ch cpte_contreval_position_change || '.' || devise_cpte_cr FROM ad_agc;
				--RAISE NOTICE 'c/v DE LA DEVISE DU COMPTE AU CREDIT DE LOPERATION = %',cpt_cv_pos_ch;

				-- MOUVEMENT COMPTABLE AU CREDIT DE LA c/v DU COMPTE AU CREDIT DE L'OPERATION
				INSERT INTO ad_mouvement (id_ecriture, id_ag,compte, cpte_interne_cli, sens, montant, devise, date_valeur)
				VALUES ((SELECT currval('ad_ecriture_seq')),v_id_agc, cpt_cv_pos_ch, NULL, 'c', cv_frais_tenue_cpte,
				code_dev_ref, date(date_prelev));
				--RAISE NOTICE 'MOUVEMENT COMPTABLE AU CREDIT DE LA c/v DU COMPTE AU CREDIT DE LOPERATION';

				UPDATE ad_cpt_comptable set solde = solde + cv_frais_tenue_cpte WHERE id_ag=v_id_agc AND num_cpte_comptable = cpt_cv_pos_ch;
				--RAISE NOTICE 'set solde = solde + cv_frais_tenue_cpte';

				-- MONTANT DANS LA DEVISE DU COMPTE DE CREDIT DE L'OPERATION
				SELECT INTO cv_frais_tenue_cpte CalculeCV(ligne.total_frais_tenue_cpt, ligne.devise, devise_cpte_cr);
				--RAISE NOTICE 'MONTANT DANS LA DEVISE DU COMPTE DE CREDIT DE LOPERATION';

				-- MOUVEMENT COMPTABLE AU CREDIT DU COMPTE DE CREDIT D EL'OPERATION
				INSERT INTO ad_mouvement (id_ecriture,id_ag, compte, cpte_interne_cli, sens, montant, devise, date_valeur)
				VALUES ((SELECT currval('ad_ecriture_seq')),v_id_agc, type_ope.num_cpte, NULL, 'c', cv_frais_tenue_cpte,
				devise_cpte_cr, date(date_prelev));
				--RAISE NOTICE 'MOUVEMENT COMPTABLE AU CREDIT DU COMPTE DE CREDIT D ELOPERATION';

				UPDATE ad_cpt_comptable set solde = solde + cv_frais_tenue_cpte WHERE id_ag=v_id_agc AND num_cpte_comptable = type_ope.num_cpte;
				--RAISE NOTICE 'set solde = solde + cv_frais_tenue_cpte';

				-- POSITION DE CHANGE DE LA DEVISE DU COMPTE AU CREDIT DE L'OPERATION
				SELECT INTO cpt_pos_ch cpte_position_change || '.' || devise_cpte_cr FROM ad_agc WHERE id_ag=v_id_agc;
				--RAISE NOTICE 'POSITION DE CHANGE DE LA DEVISE DU COMPTE AU CREDIT DE LOPERATION = %',cpt_pos_ch;

				-- MOUVEMENT COMPTABLE AU DEBIT DE LA POSITION DE CHANGE DE LA DEVISE DU COMPTE AU CREDIT D EL'OPERATION
				INSERT INTO ad_mouvement (id_ecriture,id_ag, compte, cpte_interne_cli, sens, montant, devise, date_valeur)
				VALUES ((SELECT currval('ad_ecriture_seq')),v_id_agc, cpt_pos_ch, NULL, 'd', cv_frais_tenue_cpte,
				devise_cpte_cr, date(date_prelev));
				--RAISE NOTICE 'MOUVEMENT COMPTABLE AU DEBIT DE LA POSITION DE CHANGE DE LA DEVISE DU COMPTE AU CREDIT D ELOPERATION';

				UPDATE ad_cpt_comptable set solde = solde - cv_frais_tenue_cpte WHERE id_ag=v_id_agc AND num_cpte_comptable = cpt_pos_ch;
				--RAISE NOTICE 'set solde = solde - cv_frais_tenue_cpte';

				--RAISE NOTICE 'FIN IF devise_cpte_cr != code_dev_ref AND devise_cpte_liaison != code_dev_ref';

			END IF; -- FIN IF devise_cpte_cr != code_dev_ref AND devise_cpte_liaison != code_dev_ref

			--RAISE NOTICE 'FIN  IF devise_cpte_cr = devise_cpte_liaison';

		END IF;  -- FIN  IF devise_cpte_cr = devise_cpte_liaison

		---------- FIN DEBIT COMPTE DE LIAISON PAR CREDIT COMPTEG AU CREDIT
		--RAISE NOTICE '---------- FIN DEBIT COMPTE DE LIAISON PAR CREDIT COMPTEG AU CREDIT';

      ELSE

		-- AU MOINS UN DES COMPTES N'EST PAS PRINCIPAL OU LES DEUX SONT PRINCIPAUX DU MEME JOURNAL: PAS BESOIN DONC DE COMPTE DE LIAISON
		--RAISE NOTICE 'AU MOINS UN DES COMPTES NEST PAS PRINCIPAL OU LES DEUX SONT PRINCIPAUX DU MEME JOURNAL: PAS BESOIN DONC DE COMPTE DE LIAISON';

		IF jou1 IS NULL AND jou2 IS NOT NULL THEN
			id_journal := jou2;
		END IF;

		IF jou1 IS NOT NULL AND jou2 IS NULL THEN
			id_journal := jou1;
		END IF;

		IF jou1 IS NOT NULL AND jou2 IS NOT NULL AND jou1=jou2 THEN
			id_journal := jou1;
		END IF;

		IF jou1 IS NULL AND jou2 IS NULL THEN
			id_journal := 1; -- Ecrire donc dans le joournal principal
		END IF;

		-- Vérifier que la devise du compte est la devise de référence
		--RAISE NOTICE 'Vérifier que la devise du compte est la devise de référence';
		IF ligne.devise = code_dev_ref THEN       -- Pas de change à effectuer
			--RAISE NOTICE 'Pas de change à effectuer';

			-- prelevement tax TVA sur frais de tenue
			v_scenario := 9; -- devise = code_dev_ref pas de change à effectuer
			v_reglementTax := reglementtaxfraistenue(num_cpte_debit, v_sens_tax, ligne.devise, id_journal, date_prelev, cpt_pos_ch, cpt_cv_pos_ch, ligne.id_cpte, exo.id_exo_compta, v_scenario, ligne.total_frais_tenue_cpt, code_dev_ref, num_ope, cpte_liaison);

			-- prelevement des frais sur le compte du client
			UPDATE ad_cpt SET solde = solde - ligne.total_frais_tenue_cpt WHERE (id_ag=v_id_agc AND id_cpte = ligne.id_cpte);
			--RAISE NOTICE 'prelevement des frais sur le compte du client';

			-- Ecriture comptable
			INSERT INTO ad_ecriture(id_his,id_ag, date_comptable, libel_ecriture, id_jou, id_exo, ref_ecriture,info_ecriture,type_operation)
			VALUES ((SELECT currval('ad_his_id_his_seq')),v_id_agc, date(date_prelev), type_ope.libel_ope, id_journal, exo.id_exo_compta, makeNumEcriture(id_journal, exo.id_exo_compta),ligne.id_cpte,num_ope);
			--RAISE NOTICE 'Ecriture comptable';

			-- Mouvement comptable au débit
			INSERT INTO ad_mouvement (id_ecriture,id_ag, compte, cpte_interne_cli, sens, montant, devise, date_valeur)
			VALUES ((SELECT currval('ad_ecriture_seq')),v_id_agc,num_cpte_debit,ligne.id_cpte, 'd', ligne.total_frais_tenue_cpt, ligne.devise, date(date_prelev));
			--RAISE NOTICE 'Mouvement comptable au débit';

			-- Mouvement comptable au crédit
			INSERT INTO ad_mouvement (id_ecriture,id_ag, compte, cpte_interne_cli, sens, montant, devise, date_valeur)
			VALUES ((SELECT currval('ad_ecriture_seq')),v_id_agc, type_ope.num_cpte, NULL, 'c', ligne.total_frais_tenue_cpt,ligne.devise, date(date_prelev));
			--RAISE NOTICE 'Mouvement comptable au crédit';

			-- Mise à jour des soldes comptables
			UPDATE ad_cpt_comptable set solde = solde - ligne.total_frais_tenue_cpt WHERE id_ag=v_id_agc AND num_cpte_comptable = num_cpte_debit;
			UPDATE ad_cpt_comptable set solde = solde + ligne.total_frais_tenue_cpt WHERE id_ag=v_id_agc AND num_cpte_comptable = type_ope.num_cpte;
			--RAISE NOTICE 'Mise à jour des soldes comptables';


		ELSE  -- La devise du compte n'est pas la devise de référence, il faut mouvementer la position de change
			--RAISE NOTICE 'La devise du compte nest pas la devise de référence, il faut mouvementer la position de change';

			SELECT INTO cpt_pos_ch cpte_position_change || '.' || ligne.devise FROM ad_agc WHERE id_ag=v_id_agc;
			SELECT INTO cpt_cv_pos_ch cpte_contreval_position_change || '.' || ligne.devise FROM ad_agc WHERE id_ag=v_id_agc;
			--RAISE NOTICE 'cpt_pos_ch = % cpt_cv_pos_ch = %',cpt_pos_ch,cpt_cv_pos_ch;

			--RAISE NOTICE 'cpt_pos_ch = % et cpt_cv_pos_ch = %',cpt_pos_ch, cpt_cv_pos_ch;

			-- prelevement tax TVA sur frais de tenue
			v_scenario := 10; -- devise = code_dev_ref mouvementer la position de change
			v_reglementTax := reglementtaxfraistenue(num_cpte_debit, v_sens_tax, ligne.devise, id_journal, date_prelev, cpt_pos_ch, cpt_cv_pos_ch, ligne.id_cpte, exo.id_exo_compta, v_scenario, ligne.total_frais_tenue_cpt, code_dev_ref, num_ope, cpte_liaison);

			-- prelevement des frais sur le compte du client
			UPDATE ad_cpt SET solde = solde - ligne.total_frais_tenue_cpt WHERE (id_ag=v_id_agc AND id_cpte = ligne.id_cpte);
			--RAISE NOTICE 'SET solde = solde - ligne.total_frais_tenue_cpt';

			-- Ecriture comptable
			INSERT INTO ad_ecriture(id_his,id_ag, date_comptable, libel_ecriture, id_jou, id_exo, ref_ecriture,info_ecriture,type_operation)
			VALUES ((SELECT currval('ad_his_id_his_seq')),v_id_agc, date(date_prelev), type_ope.libel_ope, id_journal, exo.id_exo_compta, makeNumEcriture(id_journal, exo.id_exo_compta),ligne.id_cpte,num_ope);
			--RAISE NOTICE 'Ecriture comptable';

			-- Mouvement comptable au débit
			INSERT INTO ad_mouvement (id_ecriture,id_ag, compte, cpte_interne_cli, sens, montant, devise, date_valeur)
			VALUES ((SELECT currval('ad_ecriture_seq')),v_id_agc,num_cpte_debit,ligne.id_cpte, 'd', ligne.total_frais_tenue_cpt,	ligne.devise, date(date_prelev));
			--RAISE NOTICE 'Mouvement comptable au débit';

			INSERT INTO ad_mouvement (id_ecriture,id_ag, compte, cpte_interne_cli, sens, montant, date_valeur, devise)
			VALUES ((SELECT currval('ad_ecriture_seq')),v_id_agc, cpt_pos_ch, NULL, 'c', ligne.total_frais_tenue_cpt, date(date_prelev), ligne.devise);
			--RAISE NOTICE 'Mouvement comptable au credit';

			-- Mise à jour des soldes des comptes comptables
			UPDATE ad_cpt_comptable set solde = solde - ligne.total_frais_tenue_cpt
			WHERE id_ag=v_id_agc AND num_cpte_comptable = num_cpte_debit;
			--RAISE NOTICE 'Mise à jour des soldes des comptes comptables';

			UPDATE ad_cpt_comptable set solde = solde + ligne.total_frais_tenue_cpt
			WHERE id_ag=v_id_agc AND num_cpte_comptable = cpt_pos_ch;
			--RAISE NOTICE 'Mise à jour des soldes des comptes comptables';

			SELECT INTO cv_frais_tenue_cpte CalculeCV(ligne.total_frais_tenue_cpt, ligne.devise, code_dev_ref);
			--RAISE NOTICE 'cv_frais_tenue_cpte = %',cv_frais_tenue_cpte;

			-- Ecriture comptable
			INSERT INTO ad_ecriture(id_his,id_ag, date_comptable, libel_ecriture, id_jou, id_exo, ref_ecriture,info_ecriture,type_operation)
			VALUES ((SELECT currval('ad_his_id_his_seq')),v_id_agc, date(date_prelev),type_ope.libel_ope, id_journal, exo.id_exo_compta, makeNumEcriture(id_journal, exo.id_exo_compta),ligne.id_cpte,num_ope);
			--RAISE NOTICE 'Ecriture comptable';

			INSERT INTO ad_mouvement (id_ecriture,id_ag, compte, cpte_interne_cli, sens, montant, date_valeur, devise)
			VALUES ((SELECT currval('ad_ecriture_seq')),v_id_agc, cpt_cv_pos_ch, NULL, 'd', cv_frais_tenue_cpte, date(date_prelev), code_dev_ref);
			--RAISE NOTICE 'Ecriture comptable';

			-- mouvement comptable au crédit
			INSERT INTO ad_mouvement (id_ecriture,id_ag, compte, cpte_interne_cli, sens, montant, devise, date_valeur)
			VALUES ((SELECT currval('ad_ecriture_seq')),v_id_agc, type_ope.num_cpte, NULL, 'c', cv_frais_tenue_cpte, code_dev_ref, date(date_prelev));
			--RAISE NOTICE 'mouvement comptable au crédit';

			-- mise à jour des soldes comptables
			UPDATE ad_cpt_comptable set solde = solde - cv_frais_tenue_cpte WHERE id_ag=v_id_agc AND num_cpte_comptable = cpt_cv_pos_ch;
			UPDATE ad_cpt_comptable set solde = solde + cv_frais_tenue_cpte WHERE id_ag=v_id_agc AND num_cpte_comptable = type_ope.num_cpte;
			--RAISE NOTICE 'mise à jour des soldes comptables';

		END IF; -- Fin vérification des devises

      --RAISE NOTICE '---------------------------------END IF jou1 is not null and jou2 is not null and jou1 != jou2--------------------------------';
      END IF; -- Fin recherche compte de liaison


      -- construction des données à renvoyer
      SELECT INTO compte_frais ligne.num_complet_cpte, ligne.devise, ligne.id_titulaire, ligne.solde, ligne.total_frais_tenue_cpt;
      RETURN NEXT compte_frais;


    ELSE -- solde_dispo_cpte < 0

      --Mise en attente
      INSERT INTO ad_frais_attente (id_cpte,id_ag, date_frais, type_frais, montant)
      VALUES (ligne.id_cpte ,v_id_agc, date(date_prelev), num_ope, ligne.total_frais_tenue_cpt);
      --RAISE NOTICE 'Compte % mise en attente avec total frais tenue = %',ligne.id_cpte,ligne.total_frais_tenue_cpt;

    END IF;

    FETCH Cpt_Prelev INTO ligne;

  END LOOP;

  CLOSE Cpt_Prelev;


  RETURN;

END;
$BODY$
  LANGUAGE plpgsql VOLATILE
  COST 100
  ROWS 1000;
ALTER FUNCTION prelevefraistenuecpt(integer, text, integer, text, text)
  OWNER TO postgres;

 ------------------------------- FIN : Ticket AT-40 -----------------------------------------------------------------------------------------------------------

 ------------------------------- DEBUT : Ticket AT-57 -----------------------------------------------------------------------------------------------------------
 -- Function: calculsoldeiardoss(date, integer, integer, text)

-- DROP FUNCTION calculsoldeiardoss(date, integer, integer, text);

CREATE OR REPLACE FUNCTION calculsoldeiardoss(date, integer, integer, text)
  RETURNS SETOF iar_table AS
$BODY$
 DECLARE

	/*paramètre d'entrée */

	in_date_param  ALIAS FOR $1;	-- Date de calcul iar
	in_id_doss ALIAS FOR $2;	-- Numero dossier de credits
	in_id_ag ALIAS FOR $3;		-- id agence
	in_devise ALIAS FOR $4;		-- devise

	/* variables internes */

	v_etat int;
	v_cre_etat int;
	v_cre_date_debloc date;
	v_differe_jours int;
	v_solde_his NUMERIC(30,6):=0;
	v_id_ech_prec int;
	v_id_ech_a_calculer int;
	v_date_ech_prec date;
	v_date_ech_iar date;
	v_solde_cap numeric(30,6):=0;
	v_solde_int numeric(30,6):=0;
	v_solde_prec numeric (30,6):=0;
	v_relica_prec_iar numeric (30,6):=0;
	v_relica_prec_id_ech int;
	v_nb_jours_prorata int;
	v_id_periodicite int;
	v_periodicite int;
	v_iar_calc_theorique  NUMERIC(30,6):=0;
	v_total_reprise NUMERIC(30,6):=0;

	/* RETURN */
	out_solde_iar NUMERIC(30,6):=0;	-- solde iar a la date de calcul
	ligne_iar iar_table;

	BEGIN

	/*recuperer les infos du dossier de credits */

	select into v_periodicite, v_id_periodicite,v_etat,v_cre_etat, v_cre_date_debloc, v_differe_jours  get_nb_jrs_periodicite(coalesce(ext.periodicite,prod.periodicite),in_id_doss), coalesce(ext.periodicite,prod.periodicite),
	etat,cre_etat, cre_date_debloc, coalesce(differe_jours,0)
	from ad_dcr dcr
	inner join adsys_produit_credit prod on dcr.id_ag = prod.id_ag
        and dcr.id_prod = prod.id
	left outer join ad_dcr_ext ext on dcr.id_ag = ext.id_ag and dcr.id_doss = ext.id_doss
	where dcr.id_doss = in_id_doss;

	IF(v_etat in (5, 7, 13, 14, 15) and v_cre_etat in (select id from adsys_etat_credits where provisionne = false and nbre_jours >0)) THEN

		select into   v_solde_cap  sum(solde_cap) from ad_etr where id_doss = in_id_doss and  id_ag = in_id_ag ;

		/*IF : pour identifier si on est sur la dernière échéeance ou pas */
		IF ((select max(date_ech) from ad_etr where id_ag = in_id_ag and id_doss = in_id_doss) between getdebutmois(in_date_param) and in_date_param)
		THEN

			select into  v_id_ech_a_calculer, v_date_ech_iar, v_solde_int id_ech, date_ech, solde_int from ad_etr where id_doss = in_id_doss and date_ech=(select max(date_ech) from ad_etr where id_ag = in_id_ag and id_doss = in_id_doss) group by id_ech, date_ech, solde_int;

		ELSE
			select into  v_id_ech_a_calculer, v_date_ech_iar, v_solde_int id_ech, date_ech, solde_int from ad_etr where id_doss = in_id_doss and date_ech >= in_date_param group by id_ech, date_ech, solde_int order by date_ech limit 1;

		END IF;

		select into v_id_ech_prec, v_date_ech_prec max(id_ech), max(date_ech) from ad_etr where id_doss = in_id_doss and id_ech < v_id_ech_a_calculer;

		raise notice 'v_id_periodicite -->  %',v_id_periodicite;
		raise notice 'v_id_ech_prec -->  %',v_id_ech_prec;
		raise notice 'v_date_ech_prec -->  %',v_date_ech_prec;
		raise notice 'v_id_ech_a_calculer -->  %',v_id_ech_a_calculer;
		raise notice 'v_solde_int -->  %',v_solde_int;
		raise notice 'v_solde_cap -->  %',v_solde_cap;

		/*calcul du nombre de jours prorata sur l'echeance en question */

		IF (v_solde_int IS NULL or v_solde_int = 0) THEN

		v_nb_jours_prorata:= ( in_date_param - ( v_cre_date_debloc + v_differe_jours));

		ELSE

		v_nb_jours_prorata:= in_date_param - coalesce(v_date_ech_prec,v_cre_date_debloc);

		END IF;

		/*calcul IAR theorique de l'échéance, s'il y a un prorata ou la periodicite est renseigné, sinon 0 */

		IF (v_periodicite = 0 or v_nb_jours_prorata=0) THEN

		v_iar_calc_theorique:= 0;

		ELSE

			IF (v_nb_jours_prorata > v_periodicite) THEN

			v_nb_jours_prorata:= v_periodicite;

			END IF;

		v_iar_calc_theorique :=  (v_nb_jours_prorata::numeric(30,6) / v_periodicite::numeric(30,6)) * v_solde_int ;
		/*AT-57 : Arrondir les montants IAR au moment du calcul IAR lui-meme*/
		v_iar_calc_theorique := round(v_iar_calc_theorique);


		END IF;

		/* recuperation des enregistrements dans la table iar historique */

		select into v_solde_prec, v_solde_his coalesce(solde_int_ech,0),
		coalesce(sum(case when etat_int = 1 then montant else -1*montant end),0)  - SUM(coalesce(solde_relica,0))
		from ad_calc_int_recevoir_his where id_doss = in_id_doss and devise = in_devise and date_traitement <=in_date_param and id_ech = v_id_ech_a_calculer
		GROUP BY solde_int_ech;

		raise notice 'v_solde_prec -->  %',v_solde_prec;
		raise notice 'v_solde_his -->  %',v_solde_his;




		/* reliquat depuis le dernier calcul iar */

		select into v_relica_prec_iar, v_relica_prec_id_ech  coalesce(solde_int_ech,0) - coalesce(calcul_iar_theorique,0), id_ech
		from ad_calc_int_recevoir_his where id_doss = in_id_doss and devise = in_devise
		and date_traitement = (select max(date_traitement) from ad_calc_int_recevoir_his
		where id_doss = in_id_doss and devise = in_devise and etat_int = 1 and date_traitement < in_date_param);

		raise notice 'v_relica_prec_id_ech -->  %',v_relica_prec_id_ech;
		raise notice 'v_relica_prec_iar -->  %',v_relica_prec_iar;


		select into v_total_reprise COALESCE(sum(montant),0)
		from ad_calc_int_recevoir_his where id_doss = in_id_doss and devise = in_devise and etat_int = 2 and id_ech = v_relica_prec_id_ech
		and date_traitement < in_date_param;

		v_relica_prec_iar:= v_relica_prec_iar - v_total_reprise;

		IF(v_relica_prec_iar < 0) THEN

		v_relica_prec_iar:=0;

		END IF;


		IF (v_relica_prec_id_ech  <> v_id_ech_a_calculer) THEN

		raise notice 'v_relica_prec_iar si (-) -->  %',v_relica_prec_iar;

		ELSE

		v_relica_prec_iar :=0 ;

		END IF;

		IF(COALESCE(v_solde_his,0)  = 0) THEN --pas d'enregistrement dans la table historique, alors iar_calculé = iar_theorique + reliquat s'il y en a

		out_solde_iar:= COALESCE(v_iar_calc_theorique,0) + v_relica_prec_iar ;

		raise notice 'IAR FINAL --> %', out_solde_iar;

		ELSE

		raise notice 'v_iar_calc_theorique --> %', v_iar_calc_theorique;

		out_solde_iar:= COALESCE(v_iar_calc_theorique,0) - COALESCE(v_solde_his,0) ;

		raise notice 'IAR calculé --> %', out_solde_iar;


		IF(out_solde_iar <=0) THEN

		out_solde_iar:=0;

		END IF;


		raise notice 'IAR FINAL --> %', out_solde_iar;


		END IF;


	ELSE --dossier non-eligible au calcul iar, donc on retourne 0

	out_solde_iar:=0;

	END IF;

	  SELECT INTO ligne_iar in_id_doss, v_id_ech_a_calculer, v_solde_int, v_date_ech_prec, v_date_ech_iar, v_nb_jours_prorata, v_periodicite,v_iar_calc_theorique,
	  v_solde_his,v_relica_prec_iar,out_solde_iar, v_solde_cap ;
	  RETURN NEXT ligne_iar;

	RETURN;
 END;
$BODY$
  LANGUAGE plpgsql VOLATILE
  COST 100
  ROWS 1000;
ALTER FUNCTION calculsoldeiardoss(date, integer, integer, text)
  OWNER TO postgres;

 ------------------------------- FIN : Ticket AT-57 -----------------------------------------------------------------------------------------------------------