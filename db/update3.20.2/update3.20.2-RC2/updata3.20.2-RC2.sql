-------------------------------------------------Debut Ticket AT-40-----------------------------------------------------
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
	--RAISE NOTICE 'appl_frais_gs = f';
  ELSE -- Pour tous les comptes epargne avec etat_cpte in (1,4) autre que les membres des groupes solidaires (Ticket AT-40)
	  -- Recupere des infos sur les compte épargne à prélever et leurs produits associés
	  OPEN Cpt_Prelev FOR
		  SELECT a.id_cpte, a.id_titulaire, a.solde, a.devise, a.num_complet_cpte, b.frais_tenue_cpt as total_frais_tenue_cpt, b.cpte_cpta_prod_ep
		  FROM ad_cpt a, adsys_produit_epargne b WHERE a.id_ag=b.id_ag AND a.id_ag=v_id_agc AND a.id_prod = b.id AND (frequence_tenue_cpt BETWEEN 1 AND freq_tenue_cpt)
		  AND a.etat_cpte in (1,4) AND a.id_titulaire NOT IN (SELECT id_membre FROM ad_grp_sol) AND b.frais_tenue_cpt > 0 ORDER BY a.id_titulaire;
	--RAISE NOTICE 'appl_frais_gs = t';
  END IF;
  --EXIT; for testing purpose only

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
-------------------------------------------------Fin Ticket AT-40-------------------------------------------------------