-- Script de mise à jour de la base de données de la version 3.6.x à la version 3.8

CREATE OR REPLACE FUNCTION alter_354()  RETURNS INT AS
$$
DECLARE
	output_result INTEGER = 1;

BEGIN

	RAISE NOTICE 'START';
	
	-- Check if field licence_code_identifier exist in table ad_agc
	IF NOT EXISTS(SELECT * FROM information_schema.columns WHERE table_name = 'ad_agc' AND column_name = 'licence_code_identifier') THEN
		ALTER TABLE ad_agc ADD COLUMN licence_code_identifier text;
		RAISE NOTICE 'Ajout champ licence_code_identifier effectuée';
		output_result := 2;
	END IF;

	IF NOT EXISTS(SELECT * FROM d_tableliste WHERE nchmpc = 'licence_code_identifier') THEN
		INSERT INTO d_tableliste(ident, tablen, nchmpc, nchmpl, isreq, ref_field, type, onslct, ispkey, traduit) VALUES ((select max(ident) from d_tableliste)+1, (select ident from tableliste where nomc like 'ad_agc' order by ident desc limit 1), 'licence_code_identifier', maketraductionlangsyst('Le code identifiant de l''agence'), false, NULL, 'txt', false, false, false);
		RAISE NOTICE 'Ajout champ licence_code_identifier dans la table d_tableliste effectuée';
		output_result := 2;
	ELSE
		UPDATE d_tableliste SET isreq='f' WHERE nchmpc = 'licence_code_identifier';
	END IF;

	RETURN output_result;

END;
$$
LANGUAGE plpgsql;
  
SELECT alter_354();
DROP FUNCTION alter_354();

-- Function: prelevefraistenuecpt(integer, text, integer)

-- DROP FUNCTION prelevefraistenuecpt(integer, text, integer);

CREATE OR REPLACE FUNCTION prelevefraistenuecpt(integer, text, integer)
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

	-- Recupere des infos sur les compte épargne à prélever et leurs produits associés
	Cpt_Prelev CURSOR FOR
		select a.id_cpte, a.id_titulaire,a.solde, a.devise, a.num_complet_cpte, b.frais_tenue_cpt as total_frais_tenue_cpt, b.cpte_cpta_prod_ep
		from ad_cpt a, adsys_produit_epargne b where a.id_ag=b.id_ag AND a.id_ag=NumAgc() AND a.id_prod = b.id and (frequence_tenue_cpt BETWEEN 1 and freq_tenue_cpt)
		and a.etat_cpte in (1,4) and b.frais_tenue_cpt > 0 order by a.id_titulaire; -- a.etat_cpte = 1

	ligne RECORD;

	ligne_ad_cpt ad_cpt%ROWTYPE;

	cpte_base INTEGER;

	solde_dispo_cpte NUMERIC(30,6);

BEGIN

  -- Recherche du libellé et du compte au crédit de type opération
  SELECT INTO type_ope libel_ope , num_cpte FROM ad_cpt_ope a, ad_cpt_ope_cptes b WHERE a.id_ag=b.id_ag AND a.id_ag=NumAgc() AND a.type_operation = num_ope AND a.type_operation=b.type_operation AND b.sens = 'c';

  -- Récupération de la devise du compte au crédit
  SELECT INTO devise_cpte_cr devise FROM ad_cpt_comptable WHERE id_ag=NumAgc() AND num_cpte_comptable = type_ope.num_cpte;

  -- Récupération du journal associé si le compte au crédit est principal
  SELECT INTO jou2 recupeJournal(type_ope.num_cpte);

  -- Recherche du numéro de l'exercice contenant la date de prélèvement
  SELECT INTO exo id_exo_compta FROM ad_exercices_compta WHERE id_ag=NumAgc() AND date_deb_exo<= date(date_prelev) AND date_fin_exo >= date(date_prelev);

  -- Récupération du nombre de devises
  SELECT INTO nbre_devises count(*) from devise WHERE id_ag=NumAgc();

  IF nbre_devises = 1 THEN
    mode_multidev := false;
  ELSE
    mode_multidev := true;
  END IF;

  -- Récupération de la devise de référence
  SELECT INTO code_dev_ref code_devise_reference FROM ad_agc WHERE id_ag=NumAgc();

  cur_date := 'now';

  OPEN Cpt_Prelev;
  FETCH Cpt_Prelev INTO ligne;

  -- Ajout historique à condition qu'on ait trouvé des comptes à traiter
  -- On utilise la date de prélèvement (qui est normalement la date pour laquelle on exécute le batch),
  -- et la dernière minute de la journée, afin
  IF FOUND THEN
    INSERT INTO ad_his (type_fonction, infos, date, id_ag)
    VALUES (212, 'Prelevement des frais de tenue de compte', date(now()), NumAgc());
  END IF;


  WHILE FOUND LOOP

    --calculer le solde disponible du compte en enlevant les frais de tenue

    SELECT INTO solde_dispo_cpte(solde - mnt_bloq - mnt_min_cpte - ligne.total_frais_tenue_cpt)
    FROM ad_cpt WHERE id_ag=NumAgc() AND id_cpte = ligne.id_cpte;

    RAISE NOTICE 'Solde dispo pour compte % = %', ligne.id_cpte, solde_dispo_cpte;

    IF (solde_dispo_cpte >= 0) THEN

      -- RECUPERATION DE LA DEVISE DU COMPTE ASSOCIE AU PRODUIT
      SELECT INTO devise_cpte_debit devise FROM ad_cpt_comptable WHERE id_ag=NumAgc() AND num_cpte_comptable = ligne.cpte_cpta_prod_ep;

      -- Construction du numéro de compte à débiter
      IF devise_cpte_debit IS NULL THEN
        num_cpte_debit := ligne.cpte_cpta_prod_ep || '.' || ligne.devise;
      ELSE
        num_cpte_debit := ligne.cpte_cpta_prod_ep;
      END IF;

      -- Récupération du journal associé si le compte est principal
      SELECT INTO jou1 recupeJournal(num_cpte_debit);

       IF jou1 IS NOT NULL AND jou2 IS NOT NULL AND jou1 != jou2 THEN

        -- num_cpte_debit ET COMPTE AU CREDIT SONT PINCIPAUX ET DE JOURNAUX DIFFERENTS , ON RECUPERE ALORS LE COMPTE DE LIAISON

          SELECT INTO cpte_liaison num_cpte_comptable FROM ad_journaux_liaison WHERE (id_ag=NumAgc() AND id_jou1=jou1 AND id_jou2=jou2) OR (id_jou1=jou2 AND id_jou2=jou1);
          RAISE NOTICE 'compte de liason entre journal % et journal %  est %', jou1, jou2, cpte_liaison;

          -- DEVISE DU COMPTE DE LIAISON
          SELECT INTO devise_cpte_liaison devise FROM ad_cpt_comptable WHERE id_ag=NumAgc() AND num_cpte_comptable = cpte_liaison;
          RAISE NOTICE 'Devise du compte de liason : % ', devise_cpte_liaison;


         ---------- DEBIT COMPTE CLIENT PAR CREDIT DU COMPTE DE LIAISON -----------------------
          IF ligne.devise = devise_cpte_liaison THEN  ----- num_cpte_debit et cpte_liaison sont de la même devise

              -- prelevement des frais sur le compte du client
              UPDATE ad_cpt SET solde = solde - ligne.total_frais_tenue_cpt WHERE (id_cpte = ligne.id_cpte);

              -- Ecriture comptable
	      INSERT INTO ad_ecriture(id_his,id_ag, date_comptable, libel_ecriture, id_jou, id_exo, ref_ecriture,info_ecriture)
		VALUES ((SELECT currval('ad_his_id_his_seq')),NumAgc(), date(date_prelev), type_ope.libel_ope, jou1,
		exo.id_exo_compta, makeNumEcriture(jou1, exo.id_exo_compta),ligne.id_cpte);

              -- Mouvement comptable au débit
	      INSERT INTO ad_mouvement (id_ecriture,id_ag, compte, cpte_interne_cli, sens, montant, devise, date_valeur)
	VALUES ((SELECT currval('ad_ecriture_seq')),NumAgc(),num_cpte_debit,ligne.id_cpte, 'd', ligne.total_frais_tenue_cpt, ligne.devise, date(date_prelev));

	      -- Mouvement comptable au crédit
	      INSERT INTO ad_mouvement (id_ecriture,id_ag,compte, cpte_interne_cli, sens, montant, devise, date_valeur)
	VALUES ((SELECT currval('ad_ecriture_seq')),NumAgc(), cpte_liaison, NULL, 'c', ligne.total_frais_tenue_cpt,ligne.devise, date(date_prelev));

	      -- Mise à jour des soldes comptables
	      UPDATE ad_cpt_comptable set solde = solde - ligne.total_frais_tenue_cpt WHERE id_ag=Numagc() AND num_cpte_comptable = num_cpte_debit;
	      UPDATE ad_cpt_comptable set solde = solde + ligne.total_frais_tenue_cpt WHERE id_ag=NumAgc() AND num_cpte_comptable = cpte_liaison;

          ELSE --------- num_cpte_debit et cpte_liaison n'ont pas la même devise, faire la conversion

           --------- si num_cpte_debit a la devise de référence et cpte_liaison une devise étrangère
           IF ligne.devise = code_dev_ref THEN

              SELECT INTO cpt_pos_ch cpte_position_change || '.' || devise_cpte_liaison FROM ad_agc WHERE id_ag=NumAgc();
              SELECT INTO cpt_cv_pos_ch cpte_contreval_position_change || '.' || devise_cpte_liaison FROM ad_agc WHERE id_ag=NumAgc();

              -- prelevement des frais sur le compte du client
              UPDATE ad_cpt SET solde = solde - ligne.total_frais_tenue_cpt WHERE (id_cpte = ligne.id_cpte AND id_ag=NumAgc());

              -- Ecriture comptable
	      INSERT INTO ad_ecriture(id_his,id_ag, date_comptable, libel_ecriture, id_jou, id_exo, ref_ecriture,info_ecriture)
	VALUES ((SELECT currval('ad_his_id_his_seq')),NumAgc(), date(date_prelev), type_ope.libel_ope, jou1, exo.id_exo_compta, makeNumEcriture(jou1, exo.id_exo_compta),ligne.id_cpte);

              -- Mouvement comptable au débit
	      INSERT INTO ad_mouvement (id_ecriture,id_ag, compte, cpte_interne_cli, sens, montant, devise, date_valeur)
	VALUES ((SELECT currval('ad_ecriture_seq')),NumAgc(),num_cpte_debit,ligne.id_cpte, 'd', ligne.total_frais_tenue_cpt, ligne.devise, date(date_prelev));

	      -- Mouvement comptable au crédit de la c/v du compte de liaison
	      INSERT INTO ad_mouvement (id_ecriture,id_ag, compte, cpte_interne_cli, sens, montant, devise, date_valeur)
	VALUES ((SELECT currval('ad_ecriture_seq')),NumAgc(), cpt_cv_pos_ch, NULL, 'c', ligne.total_frais_tenue_cpt,ligne.devise, date(date_prelev));
              -- montant dans la devise du compte de liaison
              SELECT INTO cv_frais_tenue_cpte CalculeCV(ligne.total_frais_tenue_cpt, ligne.devise, devise_cpte_liaison);

              -- Mouvement comptable au débit de la position de change du compte de liaison
	      INSERT INTO ad_mouvement (id_ecriture,id_ag, compte, cpte_interne_cli, sens, montant, devise, date_valeur)
	VALUES ((SELECT currval('ad_ecriture_seq')),NumAgc(),cpt_pos_ch,NULL, 'd', cv_frais_tenue_cpte,devise_cpte_liaison,date(date_prelev));

              -- Mouvement comptable au crédit du compte de liaison
	      INSERT INTO ad_mouvement (id_ecriture,id_ag, compte, cpte_interne_cli, sens, montant, devise, date_valeur)
	VALUES ((SELECT currval('ad_ecriture_seq')),NumAgc(),cpte_liaison ,NULL, 'c',cv_frais_tenue_cpte, devise_cpte_liaison, date(date_prelev));

	      -- Mise à jour des soldes comptables
	      UPDATE ad_cpt_comptable set solde = solde - ligne.total_frais_tenue_cpt WHERE id_ag=NumAgc() AND num_cpte_comptable = num_cpte_debit;
	      UPDATE ad_cpt_comptable set solde = solde + ligne.total_frais_tenue_cpt WHERE id_ag=NumAgc() AND num_cpte_comptable = cpt_cv_pos_ch;
              UPDATE ad_cpt_comptable set solde = solde - cv_frais_tenue_cpte WHERE id_ag=NumAgc() AND num_cpte_comptable = cpt_pos_ch;
              UPDATE ad_cpt_comptable set solde = solde + cv_frais_tenue_cpte WHERE id_ag=NumAgc() AND num_cpte_comptable = cpte_liaison;


           END IF; -- FIN IF ligne.devise = code_dev_ref

           -------- si cpte_liaison a la devise de référence et num_cpte_debit une devise étrangère
           IF devise_cpte_liaison = code_dev_ref THEN

              SELECT INTO cpt_pos_ch cpte_position_change || '.' || ligne.devise FROM ad_agc WHERE id_ag=NumAgc();
              SELECT INTO cpt_cv_pos_ch cpte_contreval_position_change || '.' || ligne.devise FROM ad_agc WHERE id_ag=NumAgc();

              -- prelevement des frais sur le compte du client
              UPDATE ad_cpt SET solde = solde - ligne.total_frais_tenue_cpt WHERE (id_ag=NumAgc() AND id_cpte = ligne.id_cpte);

              -- montant dans la devise du compte de liaison
              SELECT INTO cv_frais_tenue_cpte CalculeCV(ligne.total_frais_tenue_cpt, ligne.devise, code_dev_ref);

              -- Ecriture comptable
	      INSERT INTO ad_ecriture(id_his,id_ag, date_comptable, libel_ecriture, id_jou, id_exo, ref_ecriture,info_ecriture)
		VALUES ((SELECT currval('ad_his_id_his_seq')),NumAgc(), date(date_prelev), type_ope.libel_ope, jou1,
		exo.id_exo_compta, makeNumEcriture(jou1, exo.id_exo_compta),ligne.id_cpte);

              -- Mouvement comptable au crédit du compte de liaison
	      INSERT INTO ad_mouvement (id_ecriture,id_ag, compte, cpte_interne_cli, sens, montant, devise, date_valeur)
		VALUES ((SELECT currval('ad_ecriture_seq')),NumAgc(),cpte_liaison ,NULL, 'c',cv_frais_tenue_cpte,
		code_dev_ref, date(date_prelev));

              -- Mouvement comptable au débit de la c/v de num_cpte_debit
	      INSERT INTO ad_mouvement (id_ecriture,id_ag, compte, cpte_interne_cli, sens, montant, devise, date_valeur)
		VALUES ((SELECT currval('ad_ecriture_seq')),NumAgc(),cpt_cv_pos_ch ,NULL, 'd',cv_frais_tenue_cpte,
		code_dev_ref, date(date_prelev));

              -- Mouvement comptable au débit de num_cpte_debit
	      INSERT INTO ad_mouvement (id_ecriture,id_ag, compte, cpte_interne_cli, sens, montant, devise, date_valeur)
		VALUES ((SELECT currval('ad_ecriture_seq')),NumAgc(),num_cpte_debit,ligne.id_cpte, 'd',
		ligne.total_frais_tenue_cpt, ligne.devise, date(date_prelev));

              -- Mouvement comptable au crédit de la position de change de num_cpte_debit
	      INSERT INTO ad_mouvement (id_ecriture,id_ag,compte, cpte_interne_cli, sens, montant, devise, date_valeur)
		VALUES ((SELECT currval('ad_ecriture_seq')),NumAgc(),cpt_pos_ch,NULL, 'c', ligne.total_frais_tenue_cpt,
		ligne.devise, date(date_prelev));

               -- Mise à jour des soldes comptables
	      UPDATE ad_cpt_comptable set solde = solde - ligne.total_frais_tenue_cpt WHERE id_ag=NumAgc() AND num_cpte_comptable = num_cpte_debit;
	      UPDATE ad_cpt_comptable set solde = solde + ligne.total_frais_tenue_cpt WHERE id_ag=NumAgc() AND num_cpte_comptable = cpt_pos_ch;
              UPDATE ad_cpt_comptable set solde = solde - cv_frais_tenue_cpte WHERE id_ag=NumAgc() AND num_cpte_comptable = cpt_cv_pos_ch;
              UPDATE ad_cpt_comptable set solde = solde + cv_frais_tenue_cpte WHERE id_ag=NumAgc() AND num_cpte_comptable = cpte_liaison;

           END IF; -- FIN IF devise_cpte_liaison = code_dev_ref

         -------- si ni cpte_liaison ni num_cpte_debit n'a la devise de référence
           IF ligne.devise != code_dev_ref AND devise_cpte_liaison != code_dev_ref THEN

              -- prelevement des frais sur le compte du client
              UPDATE ad_cpt SET solde = solde - ligne.total_frais_tenue_cpt WHERE (id_ag=NumAgc() AND id_cpte = ligne.id_cpte);

              -- Ecriture comptable
	      INSERT INTO ad_ecriture(id_his,id_ag, date_comptable, libel_ecriture, id_jou, id_exo, ref_ecriture,info_ecriture)
	VALUES ((SELECT currval('ad_his_id_his_seq')),NumAgc(), date(date_prelev), type_ope.libel_ope, jou1, exo.id_exo_compta, makeNumEcriture(jou1, exo.id_exo_compta),ligne.id_cpte);

              -- Mouvement comptable au débit de num_cpte_debit
	      INSERT INTO ad_mouvement (id_ecriture,id_ag, compte, cpte_interne_cli, sens, montant, devise, date_valeur)
	VALUES ((SELECT currval('ad_ecriture_seq')),NumAgc(),num_cpte_debit,ligne.id_cpte, 'd', ligne.total_frais_tenue_cpt, ligne.devise, date(date_prelev));

              UPDATE ad_cpt_comptable set solde = solde - ligne.total_frais_tenue_cpt WHERE id_ag=NumAgc() AND num_cpte_comptable = num_cpte_debit;

              -- position de change de la devise de num_cpte_debit
              SELECT INTO cpt_pos_ch cpte_position_change || '.' || ligne.devise FROM ad_agc WHERE id_ag=NumAgc();

              -- Mouvement comptable au crédit de la position de change de num_cpte_debit
	      INSERT INTO ad_mouvement (id_ecriture,id_ag, compte, cpte_interne_cli, sens, montant, devise, date_valeur)
	VALUES ((SELECT currval('ad_ecriture_seq')),NumAgc(),cpt_pos_ch,NULL, 'c', ligne.total_frais_tenue_cpt, ligne.devise, date(date_prelev));

               UPDATE ad_cpt_comptable set solde = solde + ligne.total_frais_tenue_cpt WHERE id_ag=NumAgc() AND num_cpte_comptable = cpt_pos_ch;

              -- montant dans la devise de référence
              SELECT INTO cv_frais_tenue_cpte CalculeCV(ligne.total_frais_tenue_cpt, ligne.devise, code_dev_ref);

              -- c/v de la devise de num_cpte_debit
              SELECT INTO cpt_cv_pos_ch cpte_contreval_position_change || '.' || ligne.devise FROM ad_agc WHERE id_ag=NumAgc();

              -- Mouvement comptable au débit de la c/v de num_cpte_debit
	      INSERT INTO ad_mouvement (id_ecriture,id_ag, compte, cpte_interne_cli, sens, montant, devise, date_valeur)
	VALUES ((SELECT currval('ad_ecriture_seq')),NumAgc(), cpt_cv_pos_ch,NULL, 'd', cv_frais_tenue_cpte, code_dev_ref, date(date_prelev));

              UPDATE ad_cpt_comptable set solde = solde - cv_frais_tenue_cpte WHERE id_ag=NumAgc() AND num_cpte_comptable = cpt_cv_pos_ch;

              -- c/v de la devise du compte de liaison
              SELECT INTO cpt_cv_pos_ch cpte_contreval_position_change || '.' || devise_cpte_liaison FROM ad_agc WHERE id_ag=NumAgc();

              -- Mouvement comptable au crédit de la c/v du compte de liaison
	      INSERT INTO ad_mouvement (id_ecriture,id_ag, compte, cpte_interne_cli, sens, montant, devise, date_valeur)
	VALUES ((SELECT currval('ad_ecriture_seq')),NumAgc(), cpt_cv_pos_ch, NULL, 'c', cv_frais_tenue_cpte, code_dev_ref, date(date_prelev));

              UPDATE ad_cpt_comptable set solde = solde + cv_frais_tenue_cpte WHERE id_ag=NumAgc() AND num_cpte_comptable = cpt_cv_pos_ch;

               -- montant dans la devise du compte de liaison
              SELECT INTO cv_frais_tenue_cpte CalculeCV(ligne.total_frais_tenue_cpt, ligne.devise, devise_cpte_liaison);

               -- Mouvement comptable au crédit du compte de liaison
	      INSERT INTO ad_mouvement (id_ecriture,id_ag, compte, cpte_interne_cli, sens, montant, devise, date_valeur)
	VALUES ((SELECT currval('ad_ecriture_seq')),NumAgc(), cpte_liaison, NULL, 'c', cv_frais_tenue_cpte, devise_cpte_liaison, date(date_prelev));

               UPDATE ad_cpt_comptable set solde = solde + cv_frais_tenue_cpte WHERE id_ag=NumAgc() AND num_cpte_comptable = cpte_liaison;

               -- position de change de la devise du compte de liaison
              SELECT INTO cpt_pos_ch cpte_position_change || '.' || devise_cpte_liaison FROM ad_agc WHERE id_ag=NumAgc();

               -- Mouvement comptable au débit de la position de change de la devise du compte de liaison
	      INSERT INTO ad_mouvement (id_ecriture,id_ag, compte, cpte_interne_cli, sens, montant, devise, date_valeur)
	VALUES ((SELECT currval('ad_ecriture_seq')),NumAgc(), cpt_pos_ch, NULL, 'd', cv_frais_tenue_cpte, devise_cpte_liaison, date(date_prelev));

               UPDATE ad_cpt_comptable set solde = solde - cv_frais_tenue_cpte WHERE id_ag=NumAgc() AND num_cpte_comptable = cpt_pos_ch;

           END IF; -- FIN IF ligne.devise != code_dev_ref AND devise_cpte_liaison != code_dev_ref

      END IF;  -- FIN  IF ligne.devise = devise_cpte_liaison

      ----------- FIN DEBIT COMPTE CLIENT PAR CREDIT COMPTE DE LIAISON -----------------------


      ----------- DEBIT COMPTE DE LIAISON PAR CREDIT COMPTE AU CREDIT DANS LE SECOND JOURNAL ------------------------

      IF devise_cpte_liaison = devise_cpte_cr THEN  ----- COMPTE AU CREDIT ET cpte_liaison SONT DE LA MEME DEVISE

              -- MONTANT DANS LA DEVISE DU COMPTE DE LIASON
              SELECT INTO cv_frais_tenue_cpte CalculeCV(ligne.total_frais_tenue_cpt, ligne.devise, devise_cpte_liaison);

              -- PASSAGE ECRITURE COMPTABLE
	      INSERT INTO ad_ecriture(id_his,id_ag, date_comptable, libel_ecriture, id_jou, id_exo, ref_ecriture,info_ecriture)
		VALUES ((SELECT currval('ad_his_id_his_seq')),NumAgc(),date(date_prelev), type_ope.libel_ope, jou2,
		exo.id_exo_compta, makeNumEcriture(jou2, exo.id_exo_compta),ligne.id_cpte);

              -- MOUVEMENT COMPTABLE AU DEBIT DU COMPTE DE LIAISON
	      INSERT INTO ad_mouvement (id_ecriture,id_ag, compte, cpte_interne_cli, sens, montant, devise, date_valeur)
		VALUES ((SELECT currval('ad_ecriture_seq')),NumAgc(),cpte_liaison,NULL,'d',cv_frais_tenue_cpte,
		devise_cpte_liaison,date(date_prelev));

              UPDATE ad_cpt_comptable set solde = solde - cv_frais_tenue_cpte WHERE id_ag=NumAgc() AND num_cpte_comptable = cpte_liaison;

	      -- MOUVEMENT COMPTABLE AU CREDIT DU COMPTE DE CREDIT
	      INSERT INTO ad_mouvement (id_ecriture,id_ag, compte, cpte_interne_cli, sens, montant, devise, date_valeur)
		VALUES ((SELECT currval('ad_ecriture_seq')),NumAgc(),type_ope.num_cpte,NULL,'c',cv_frais_tenue_cpte,
		devise_cpte_cr,date(date_prelev));

              UPDATE ad_cpt_comptable set solde = solde + cv_frais_tenue_cpte WHERE id_ag=NumAgc() AND num_cpte_comptable = type_ope.num_cpte;


    ELSE      ----- COMPTE AU CREDIT ET cpte_liaison N'ONT PAS LA MEME DEVISE , FAIRE DONC LA CONVERSION

           IF devise_cpte_liaison = code_dev_ref THEN  -- CPTE DE LIAISON A LA DEVISE DE REFERENCE ET CPTE AU CREDIT DEVISE ETRANGERE

              SELECT INTO cpt_pos_ch cpte_position_change || '.' || devise_cpte_cr FROM ad_agc WHERE id_ag=NumAgc();
              SELECT INTO cpt_cv_pos_ch cpte_contreval_position_change || '.' || devise_cpte_cr FROM ad_agc WHERE id_ag=NumAgc();

              -- MONTANT DANS LA DEVISE DU COMPTE DE LIASON (DEVISE DE REFERENCE )
              SELECT INTO cv_frais_tenue_cpte CalculeCV(ligne.total_frais_tenue_cpt, ligne.devise, devise_cpte_liaison);

              -- PASSAGE ECRITURE COMPTABLE
	      INSERT INTO ad_ecriture(id_his,id_ag, date_comptable, libel_ecriture, id_jou, id_exo, ref_ecriture,info_ecriture)
		VALUES ((SELECT currval('ad_his_id_his_seq')),NumAgc(), date(date_prelev), type_ope.libel_ope, jou2,
		exo.id_exo_compta, makeNumEcriture(jou2, exo.id_exo_compta),ligne.id_cpte);

              -- MOUVEMENT AU DEBIT DU COMPTE DE LIAISON
	      INSERT INTO ad_mouvement (id_ecriture,id_ag, compte, cpte_interne_cli, sens, montant, devise, date_valeur)
		VALUES ((SELECT currval('ad_ecriture_seq')),NumAgc(), cpte_liaison, NULL, 'd', cv_frais_tenue_cpte,
		devise_cpte_liaison, date(date_prelev));

            	UPDATE ad_cpt_comptable set solde = solde - cv_frais_tenue_cpte WHERE id_ag=NumAgc() AND num_cpte_comptable = cpte_liaison;

	      -- MOUVEMENT COMPTABLE AU CREDIT DE LA c/v DU COMPTE DE CREDIT DE L'OPERATION
	      INSERT INTO ad_mouvement (id_ecriture,id_ag, compte, cpte_interne_cli, sens, montant, devise, date_valeur)
		VALUES ((SELECT currval('ad_ecriture_seq')),NumAgc(), cpt_cv_pos_ch, NULL, 'c', cv_frais_tenue_cpte,
		code_dev_ref, date(date_prelev));

              UPDATE ad_cpt_comptable set solde = solde + cv_frais_tenue_cpte WHERE id_ag=NumAgc() AND num_cpte_comptable = cpt_cv_pos_ch;


              -- MONTANT DANS LA DEVISE DU COMPTE DE CREDIT DE L'OPERATION
              SELECT INTO cv_frais_tenue_cpte CalculeCV(ligne.total_frais_tenue_cpt, ligne.devise, devise_cpte_cr);

              -- MOUVEMENT COMPTABLE AU DEBIT DE LA POSITION DE CHANGE DU COMPTE AU CREDIT DE L'OPERATION
	      INSERT INTO ad_mouvement (id_ecriture,id_ag, compte, cpte_interne_cli, sens, montant, devise, date_valeur)
		VALUES ((SELECT currval('ad_ecriture_seq')),NumAgc(),cpt_pos_ch, NULL, 'd', cv_frais_tenue_cpte,
		devise_cpte_cr,date(date_prelev));

              UPDATE ad_cpt_comptable set solde = solde - cv_frais_tenue_cpte WHERE id_ag=NumAgc() AND num_cpte_comptable = cpt_pos_ch;

              -- MOUVEMENT COMPTABLE AU CREDIT DU COMPTE DE CREDIT DE L'OPERATION
	      INSERT INTO ad_mouvement (id_ecriture,id_ag, compte, cpte_interne_cli, sens, montant, devise, date_valeur)
		VALUES ((SELECT currval('ad_ecriture_seq')),NumAgc(), type_ope.num_cpte , NULL, 'c',cv_frais_tenue_cpte,
		devise_cpte_cr, date(date_prelev));

	      UPDATE ad_cpt_comptable set solde = solde + cv_frais_tenue_cpte WHERE id_ag=NumAgc() AND num_cpte_comptable = type_ope.num_cpte;


           END IF; -- FIN IF devise_cpte_liaison = code_dev_ref


           IF devise_cpte_cr = code_dev_ref THEN -- SI CPTE AU CREDIT A LA DEVISE DE REFERENCE ET CPTE LIAISON UNE DEVISE ETRANGERE

              SELECT INTO cpt_pos_ch cpte_position_change || '.' || devise_cpte_liaison FROM ad_agc WHERE id_ag=NumAgc();
              SELECT INTO cpt_cv_pos_ch cpte_contreval_position_change || '.' || devise_cpte_liaison FROM ad_agc WHERE id_ag=NumAgc();

              -- PASSAGE ECRITURE COMPTABLE
	      INSERT INTO ad_ecriture(id_his,id_ag, date_comptable, libel_ecriture, id_jou, id_exo, ref_ecriture,info_ecriture)
		VALUES ((SELECT currval('ad_his_id_his_seq')),NumAgc(), date(date_prelev), type_ope.libel_ope, jou2,
		exo.id_exo_compta, makeNumEcriture(jou2, exo.id_exo_compta),ligne.id_cpte);

              -- MONATANT DANS LA DEVISE DU COMPTE AU CREDIT DE L'OPERATION ( DEVISE DE REFERENCE )
              SELECT INTO cv_frais_tenue_cpte CalculeCV(ligne.total_frais_tenue_cpt, ligne.devise, devise_cpte_cr);

              -- MOUVEMENT AU CREDIT DU COMPTE DE CREDIT DE L'OPERATION
	      INSERT INTO ad_mouvement (id_ecriture,id_ag, compte, cpte_interne_cli, sens, montant, devise, date_valeur)
		VALUES ((SELECT currval('ad_ecriture_seq')),NumAgc(),type_ope.num_cpte ,NULL, 'c',cv_frais_tenue_cpte,
		devise_cpte_cr, date(date_prelev));

              UPDATE ad_cpt_comptable set solde = solde + cv_frais_tenue_cpte WHERE id_ag=NumAgc() AND num_cpte_comptable = type_ope.num_cpte;

              -- MOUVEMENT AU DEBIT DE LA c/v DU COMPTE DE LIAISON
	      INSERT INTO ad_mouvement (id_ecriture,id_ag, compte, cpte_interne_cli, sens, montant, devise, date_valeur)
		VALUES ((SELECT currval('ad_ecriture_seq')),NumAgc(),cpt_cv_pos_ch ,NULL, 'd',cv_frais_tenue_cpte,
		code_dev_ref, date(date_prelev));

              UPDATE ad_cpt_comptable set solde = solde - cv_frais_tenue_cpte WHERE id_ag=NumAgc() AND num_cpte_comptable = cpt_cv_pos_ch;

              -- MONATANT DANS LA DEVISE DU COMPTE DE LIAISON
              SELECT INTO cv_frais_tenue_cpte CalculeCV(ligne.total_frais_tenue_cpt, ligne.devise, devise_cpte_liaison);

              -- MOUVEMENT COMPTABLE AU DEBIT DU COMPTE DE LIAISON
	      INSERT INTO ad_mouvement (id_ecriture,id_ag, compte, cpte_interne_cli, sens, montant, devise, date_valeur)
		VALUES ((SELECT currval('ad_ecriture_seq')),NumAgc(), cpte_liaison, NULL, 'd', cv_frais_tenue_cpte,
		devise_cpte_liaison, date(date_prelev));

               UPDATE ad_cpt_comptable set solde = solde - cv_frais_tenue_cpte WHERE id_ag=NumAgc() AND num_cpte_comptable = cpte_liaison;

              -- MOUVEMENT COMPTABLE AU CREDIT DA LA POSITION DE CHANGE DE LA DEVISE DU COMPTE DE LIAISON
	      INSERT INTO ad_mouvement (id_ecriture,id_ag, compte, cpte_interne_cli, sens, montant, devise, date_valeur)
		VALUES ((SELECT currval('ad_ecriture_seq')),NumAgc(),cpt_pos_ch, NULL, 'c', cv_frais_tenue_cpte,
		devise_cpte_liaison, date(date_prelev));

	      UPDATE ad_cpt_comptable set solde = solde + cv_frais_tenue_cpte WHERE id_ag=NumAgc() AND num_cpte_comptable = cpt_pos_ch;

           END IF; -- FIN IF devise_cpte_cr = code_dev_ref

           IF devise_cpte_cr != code_dev_ref AND devise_cpte_liaison != code_dev_ref THEN

              -- DEVISE COMPTE DE LIAISON ET DEVISE COMPTE AU CREDIT SONT DIFFERENTES ET AUCUNE N'EST EGALE A LA DEVISE DE REFERENCE

              -- PASSAGE ECRITURE COMPTABLE DANS jou2
	      INSERT INTO ad_ecriture(id_his,id_ag, date_comptable, libel_ecriture, id_jou, id_exo, ref_ecriture,info_ecriture)
		VALUES ((SELECT currval('ad_his_id_his_seq')),NumAgc(), date(date_prelev), type_ope.libel_ope, jou2,
		exo.id_exo_compta, makeNumEcriture(jou2, exo.id_exo_compta),ligne.id_cpte);

              -- MONTANT DANS LA DEVISE DU COMPTE DE LIAISON
              SELECT INTO cv_frais_tenue_cpte CalculeCV(ligne.total_frais_tenue_cpt, ligne.devise, devise_cpte_liaison);

              -- MOUVEMENT COMPTABLE AU DEBIT DU COMPTE DE LIAISON
	      INSERT INTO ad_mouvement (id_ecriture,id_ag, compte, cpte_interne_cli, sens, montant, devise, date_valeur)
		VALUES ((SELECT currval('ad_ecriture_seq')),NumAgc(), cpte_liaison, NULL, 'd', cv_frais_tenue_cpte,
		devise_cpte_liaison, date(date_prelev));

              UPDATE ad_cpt_comptable set solde = solde - cv_frais_tenue_cpte WHERE id_ag=NumAgc() AND num_cpte_comptable = cpte_liaison;

              -- POSITION DE CHANGE DE LA DEVISE DU COMPTE DE LIAISON
              SELECT INTO cpt_pos_ch cpte_position_change || '.' || devise_cpte_liaison FROM ad_agc WHERE id_ag=NumAgc();

              -- MOUVEMENT COMPTABLE AU CREDIT DE LA POSITION DE CHANGE DE LA DEVISE DU COMPTE DE LIAISON
	      INSERT INTO ad_mouvement (id_ecriture,id_ag, compte, cpte_interne_cli, sens, montant, devise, date_valeur)
		VALUES ((SELECT currval('ad_ecriture_seq')),NumAgc(), cpt_pos_ch, NULL, 'c', cv_frais_tenue_cpte,
		devise_cpte_liaison, date(date_prelev));

               UPDATE ad_cpt_comptable set solde = solde + cv_frais_tenue_cpte WHERE id_ag=NumAgc() AND num_cpte_comptable = cpt_pos_ch;

              -- MONATNT DANS LA DEVISE DE REFERENCE
              SELECT INTO cv_frais_tenue_cpte CalculeCV(ligne.total_frais_tenue_cpt, ligne.devise, code_dev_ref);

              -- c/v DE LA DEVISE DU COMPTE DE LIAISON
              SELECT INTO cpt_cv_pos_ch cpte_contreval_position_change || '.' || devise_cpte_liaison FROM ad_agc;

              -- MOUVEMENT COMPTABLE AU DEBIT DE LA c/v DE LA DEVISE DU COMPTE DE LIAISON
	      INSERT INTO ad_mouvement (id_ecriture,id_ag, compte, cpte_interne_cli, sens, montant, devise, date_valeur)
	VALUES ((SELECT currval('ad_ecriture_seq')),NumAgc(), cpt_cv_pos_ch,NULL, 'd', cv_frais_tenue_cpte, code_dev_ref, date(date_prelev));

              UPDATE ad_cpt_comptable set solde = solde - cv_frais_tenue_cpte WHERE id_ag=NumAgc() AND num_cpte_comptable = cpt_cv_pos_ch;

              -- c/v DE LA DEVISE DU COMPTE AU CREDIT DE L'OPERATION
              SELECT INTO cpt_cv_pos_ch cpte_contreval_position_change || '.' || devise_cpte_cr FROM ad_agc;

              -- MOUVEMENT COMPTABLE AU CREDIT DE LA c/v DU COMPTE AU CREDIT DE L'OPERATION
	      INSERT INTO ad_mouvement (id_ecriture, id_ag,compte, cpte_interne_cli, sens, montant, devise, date_valeur)
		VALUES ((SELECT currval('ad_ecriture_seq')),NumAgc(), cpt_cv_pos_ch, NULL, 'c', cv_frais_tenue_cpte,
		code_dev_ref, date(date_prelev));

              UPDATE ad_cpt_comptable set solde = solde + cv_frais_tenue_cpte WHERE id_ag=NumAgc() AND num_cpte_comptable = cpt_cv_pos_ch;

               -- MONTANT DANS LA DEVISE DU COMPTE DE CREDIT DE L'OPERATION
              SELECT INTO cv_frais_tenue_cpte CalculeCV(ligne.total_frais_tenue_cpt, ligne.devise, devise_cpte_cr);

               -- MOUVEMENT COMPTABLE AU CREDIT DU COMPTE DE CREDIT D EL'OPERATION
	      INSERT INTO ad_mouvement (id_ecriture,id_ag, compte, cpte_interne_cli, sens, montant, devise, date_valeur)
		VALUES ((SELECT currval('ad_ecriture_seq')),NumAgc(), type_ope.num_cpte, NULL, 'c', cv_frais_tenue_cpte,
		devise_cpte_cr, date(date_prelev));

               UPDATE ad_cpt_comptable set solde = solde + cv_frais_tenue_cpte WHERE id_ag=NumAgc() AND num_cpte_comptable = type_ope.num_cpte;

               -- POSITION DE CHANGE DE LA DEVISE DU COMPTE AU CREDIT DE L'OPERATION
              SELECT INTO cpt_pos_ch cpte_position_change || '.' || devise_cpte_cr FROM ad_agc WHERE id_ag=NumAgc();

               -- MOUVEMENT COMPTABLE AU DEBIT DE LA POSITION DE CHANGE DE LA DEVISE DU COMPTE AU CREDIT D EL'OPERATION
	      INSERT INTO ad_mouvement (id_ecriture,id_ag, compte, cpte_interne_cli, sens, montant, devise, date_valeur)
		VALUES ((SELECT currval('ad_ecriture_seq')),NumAgc(), cpt_pos_ch, NULL, 'd', cv_frais_tenue_cpte,
		devise_cpte_cr, date(date_prelev));

               UPDATE ad_cpt_comptable set solde = solde - cv_frais_tenue_cpte WHERE id_ag=NumAgc() AND num_cpte_comptable = cpt_pos_ch;

           END IF; -- FIN IF devise_cpte_cr != code_dev_ref AND devise_cpte_liaison != code_dev_ref

      END IF;  -- FIN  IF devise_cpte_cr = devise_cpte_liaison

      ---------- FIN DEBIT COMPTE DE LIAISON PAR CREDIT COMPTEG AU CREDIT

      ELSE

        -- AU MOINS UN DES COMPTES N'EST PAS PRINCIPAL OU LES DEUX SONT PRINCIPAUX DU MEME JOURNAL: PAS BESOIN DONC DE COMPTE DE LIAISON

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
        IF ligne.devise = code_dev_ref THEN       -- Pas de change à effectuer

           -- prelevement des frais sur le compte du client
           UPDATE ad_cpt SET solde = solde - ligne.total_frais_tenue_cpt WHERE (id_ag=NumAgc() AND id_cpte = ligne.id_cpte);

	   -- Ecriture comptable
	   INSERT INTO ad_ecriture(id_his,id_ag, date_comptable, libel_ecriture, id_jou, id_exo, ref_ecriture,info_ecriture)
	VALUES ((SELECT currval('ad_his_id_his_seq')),NumAgc(), date(date_prelev), type_ope.libel_ope, id_journal, exo.id_exo_compta, makeNumEcriture(id_journal, exo.id_exo_compta),ligne.id_cpte);

	   -- Mouvement comptable au débit
	   INSERT INTO ad_mouvement (id_ecriture,id_ag, compte, cpte_interne_cli, sens, montant, devise, date_valeur)
	VALUES ((SELECT currval('ad_ecriture_seq')),NumAgc(),num_cpte_debit,ligne.id_cpte, 'd', ligne.total_frais_tenue_cpt, ligne.devise, date(date_prelev));

	   -- Mouvement comptable au crédit
	   INSERT INTO ad_mouvement (id_ecriture,id_ag, compte, cpte_interne_cli, sens, montant, devise, date_valeur)
	VALUES ((SELECT currval('ad_ecriture_seq')),NumAgc(), type_ope.num_cpte, NULL, 'c', ligne.total_frais_tenue_cpt,ligne.devise, date(date_prelev));

	   -- Mise à jour des soldes comptables
	   UPDATE ad_cpt_comptable set solde = solde - ligne.total_frais_tenue_cpt WHERE id_ag=NumAgc() AND num_cpte_comptable = num_cpte_debit;
	   UPDATE ad_cpt_comptable set solde = solde + ligne.total_frais_tenue_cpt WHERE id_ag=NumAgc() AND num_cpte_comptable = type_ope.num_cpte;


        ELSE  -- La devise du compte n'est pas la devise de référence, il faut mouvementer la position de change

	  SELECT INTO cpt_pos_ch cpte_position_change || '.' || ligne.devise FROM ad_agc WHERE id_ag=NumAgc();
          SELECT INTO cpt_cv_pos_ch cpte_contreval_position_change || '.' || ligne.devise FROM ad_agc WHERE id_ag=NumAgc();

          RAISE NOTICE 'cpt_pos_ch = % et cpt_cv_pos_ch = %',cpt_pos_ch, cpt_cv_pos_ch;

	  -- prelevement des frais sur le compte du client
	  UPDATE ad_cpt SET solde = solde - ligne.total_frais_tenue_cpt WHERE (id_ag=NumAgc() AND id_cpte = ligne.id_cpte);

	  -- Ecriture comptable
	  INSERT INTO ad_ecriture(id_his,id_ag, date_comptable, libel_ecriture, id_jou, id_exo, ref_ecriture,info_ecriture)
	VALUES ((SELECT currval('ad_his_id_his_seq')),NumAgc(), date(date_prelev), type_ope.libel_ope, id_journal, exo.id_exo_compta, makeNumEcriture(id_journal, exo.id_exo_compta),ligne.id_cpte);

	  -- Mouvement comptable au débit
	  INSERT INTO ad_mouvement (id_ecriture,id_ag, compte, cpte_interne_cli, sens, montant, devise, date_valeur)
	VALUES ((SELECT currval('ad_ecriture_seq')),NumAgc(),num_cpte_debit,ligne.id_cpte, 'd', ligne.total_frais_tenue_cpt,	ligne.devise, date(date_prelev));

	  INSERT INTO ad_mouvement (id_ecriture,id_ag, compte, cpte_interne_cli, sens, montant, date_valeur, devise)
	VALUES ((SELECT currval('ad_ecriture_seq')),Numagc(), cpt_pos_ch, NULL, 'c', ligne.total_frais_tenue_cpt, date(date_prelev), ligne.devise);

	  -- Mise à jour des soldes des comptes comptables
	  UPDATE ad_cpt_comptable set solde = solde - ligne.total_frais_tenue_cpt
	WHERE id_ag=NumAgc() AND num_cpte_comptable = num_cpte_debit;

	  UPDATE ad_cpt_comptable set solde = solde + ligne.total_frais_tenue_cpt
	WHERE id_ag=NumAgc() AND num_cpte_comptable = cpt_pos_ch;

	  SELECT INTO cv_frais_tenue_cpte CalculeCV(ligne.total_frais_tenue_cpt, ligne.devise, code_dev_ref);

	  -- Ecriture comptable
	  INSERT INTO ad_ecriture(id_his,id_ag, date_comptable, libel_ecriture, id_jou, id_exo, ref_ecriture,info_ecriture)
	VALUES ((SELECT currval('ad_his_id_his_seq')),NumAgc(), date(date_prelev),type_ope.libel_ope, id_journal, exo.id_exo_compta, makeNumEcriture(id_journal, exo.id_exo_compta),ligne.id_cpte);

	  INSERT INTO ad_mouvement (id_ecriture,id_ag, compte, cpte_interne_cli, sens, montant, date_valeur, devise)
	VALUES ((SELECT currval('ad_ecriture_seq')),NumAgc(), cpt_cv_pos_ch, NULL, 'd', cv_frais_tenue_cpte, date(date_prelev), code_dev_ref);

	  -- mouvement comptable au crédit
	  INSERT INTO ad_mouvement (id_ecriture,id_ag, compte, cpte_interne_cli, sens, montant, devise, date_valeur)
	VALUES ((SELECT currval('ad_ecriture_seq')),NumAgc(), type_ope.num_cpte, NULL, 'c', cv_frais_tenue_cpte, code_dev_ref, date(date_prelev));

	  -- mise à jour des soldes comptables
	  UPDATE ad_cpt_comptable set solde = solde - cv_frais_tenue_cpte WHERE id_ag=NumAgc() AND num_cpte_comptable = cpt_cv_pos_ch;
	  UPDATE ad_cpt_comptable set solde = solde + cv_frais_tenue_cpte WHERE id_ag=NumAgc() AND num_cpte_comptable = type_ope.num_cpte;

        END IF; -- Fin vérification des devises

      END IF; -- Fin recherche compte de liaison


      -- construction des données à renvoyer
      SELECT INTO compte_frais ligne.num_complet_cpte, ligne.devise, ligne.id_titulaire, ligne.solde, ligne.total_frais_tenue_cpt;
      RETURN NEXT compte_frais;


    ELSE

      --Mise en attente
      INSERT INTO ad_frais_attente (id_cpte,id_ag, date_frais, type_frais, montant)
      VALUES (ligne.id_cpte ,NumAgc(), date(date_prelev), num_ope, ligne.total_frais_tenue_cpt);

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
ALTER FUNCTION prelevefraistenuecpt(integer, text, integer)
  OWNER TO postgres;


