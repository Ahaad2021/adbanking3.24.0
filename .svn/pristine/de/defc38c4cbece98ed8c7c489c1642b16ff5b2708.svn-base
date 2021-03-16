
-----------------------------------------------------------------------------------------------
---------------------------Ticket #538  : Ajout champs manquant dans ad_poste------------------
-----------------------------------------------------------------------------------------------

CREATE OR REPLACE FUNCTION patch_538() RETURNS void AS $BODY$
BEGIN

-- Create the column is_gras in ad_poste if it does not exist

  IF (SELECT count(*) from information_schema.columns WHERE table_name = 'ad_poste' AND column_name='is_gras') = 0 THEN
    ALTER TABLE ad_poste ADD COLUMN is_gras boolean;
    RAISE INFO 'Created column is_gras in table ad_poste';
  END IF;

-- Create the column is_centralise in ad_poste if it does not exist
  IF (SELECT count(*) from information_schema.columns WHERE table_name = 'ad_poste' AND column_name='is_centralise') = 0 THEN
    ALTER TABLE ad_poste ADD COLUMN is_centralise boolean;
    RAISE INFO 'Created column is_centralise in table ad_poste';
  END IF;

END;
$BODY$
LANGUAGE plpgsql VOLATILE COST 100;
ALTER FUNCTION patch_538() OWNER TO adbanking;

--------------------- Execution -----------------------------------
SELECT patch_538();
Drop function patch_538();
--------------------------------------------------------------------


-----------------------------------------------------------------------------------------------
----------------Ticket #496  : Sécurité lors de la connexion au logiciel adbanking-------------
-----------------------------------------------------------------------------------------------

CREATE OR REPLACE FUNCTION patch_496() RETURNS void AS $$

BEGIN

RAISE NOTICE 'DEMARRAGE mise a jour base de données pour le ticket #496';

-- Modification de la table ad_log
-- Ajout du champ login_attempt

ALTER TABLE ad_log ADD COLUMN login_attempt int DEFAULT 0;
        EXCEPTION
            WHEN duplicate_column THEN RAISE NOTICE 'Le champ login_attempt  existe déjà dans la table ad_log.';


RAISE NOTICE 'FIN mise a jour base de données pour le ticket #496';

END;

$$
LANGUAGE plpgsql VOLATILE COST 100;
ALTER FUNCTION patch_496() OWNER TO adbanking;

-- ----------------------------------------------------------------------------
-- Execution
-- ----------------------------------------------------------------------------
SELECT patch_496();
DROP FUNCTION patch_496();

-----------------------------------------------------------------------------------------------
--------------Ticket #492  : Paramètre pour afficher le billetage sur le bordereau-------------
-----------------------------------------------------------------------------------------------


CREATE OR REPLACE FUNCTION nouveau_champs_492()  RETURNS INT AS $$
DECLARE
	output_result INTEGER = 1;

BEGIN
	RAISE INFO 'MAJ NOUVEAU CHAMPS AD_AGC' ;
	-- Check if field "param_affiche_billetage" exist in table "ad_agc"
	IF NOT EXISTS(SELECT * FROM information_schema.columns WHERE table_name = 'ad_agc' AND column_name = 'param_affiche_billetage') THEN
		ALTER TABLE ad_agc ADD COLUMN param_affiche_billetage BOOLEAN DEFAULT true;
		output_result := 2;
	END IF;
	
	
	--insert into "d_tableliste" if notExist
	IF NOT EXISTS(SELECT * FROM d_tableliste WHERE nchmpc = 'param_affiche_billetage') THEN
	
		INSERT INTO d_tableliste(ident, tablen, nchmpc, nchmpl, isreq, ref_field, type, onslct, ispkey, traduit) VALUES ((select max(ident) from d_tableliste)+1, (select ident from tableliste where nomc like 'ad_agc' order by ident desc limit 1), 'param_affiche_billetage', maketraductionlangsyst('Affichage billetage recu'), true, NULL, 'bol', false, false, false);
		output_result := 2;
	END IF;

	RETURN output_result;

END;
$$
LANGUAGE plpgsql;

-- ----------------------------------------------------------------------------
-- Execution
-- ----------------------------------------------------------------------------
SELECT nouveau_champs_492();
DROP FUNCTION nouveau_champs_492();


-----------------------------------------------------------------------------------------------
--------Ticket #495  : Contrôle sur le nombre de caractères de la pièce d'identité-------------
-----------------------------------------------------------------------------------------------

CREATE OR REPLACE FUNCTION patch_ticket_495()  RETURNS INT AS
$$
DECLARE
	output_result INTEGER = 1;

BEGIN
	RAISE INFO 'MAJ NOUVEAU CHAMPS adsys_type_piece_identite' ;
	-- Check if field "char_length" exist in table "adsys_type_piece_identite"
	IF NOT EXISTS(SELECT * FROM information_schema.columns WHERE table_name = 'adsys_type_piece_identite' AND column_name = 'char_length') THEN
		ALTER TABLE adsys_type_piece_identite ADD COLUMN char_length int DEFAULT 0;
		output_result := 2;
	END IF;
	
	
	--insert into "d_tableliste" if notExist
	IF NOT EXISTS(SELECT * FROM d_tableliste WHERE nchmpc = 'char_length') THEN
	
		INSERT INTO d_tableliste(ident, tablen, nchmpc, nchmpl, isreq, ref_field, type, onslct, ispkey, traduit)
		VALUES ((select max(ident) from d_tableliste)+1, (select ident from tableliste where nomc like 'adsys_type_piece_identite' order by ident desc limit 1), 'char_length', maketraductionlangsyst('Nombre de caractères (0 si aucune limite)'), true, NULL, 'int', false, false, false);
		output_result := 2;
	END IF;

	RETURN output_result;

END;
$$
LANGUAGE plpgsql;
  
SELECT patch_ticket_495();
DROP FUNCTION patch_ticket_495();


-----------------------------------------------------------------------------------------------
-----------------------------Ticket #544  : Rapport INVENTAIRE DE DEPOT------------------------
-----------------------------------------------------------------------------------------------

CREATE OR REPLACE FUNCTION patch_544() RETURNS void AS $BODY$
BEGIN

  IF (SELECT count(*) from information_schema.tables WHERE table_name = 'ad_cpt_hist') = 0 THEN
    CREATE TABLE ad_cpt_hist (
      "id" serial  NOT NULL,
      "date_action" timestamp DEFAULT now(),
      "id_cpte" int4 NOT NULL,
      "etat_cpte" int4,
      "solde" numeric(30,6) DEFAULT 0,
      "id_ag" int4 NOT NULL,
      PRIMARY KEY (id, id_ag)
    );
  END IF;

END;
$BODY$
LANGUAGE plpgsql VOLATILE COST 100;
ALTER FUNCTION patch_544() OWNER TO adbanking;

--------------------- Execution -----------------------------------
SELECT patch_544();
Drop function patch_544();
--------------------------------------------------------------------


CREATE OR REPLACE FUNCTION trig_insert_ad_cpt_hist() RETURNS TRIGGER AS $BODY$
  BEGIN
    INSERT INTO ad_cpt_hist
    (date_action, id_cpte, etat_cpte, solde, id_ag)
    VALUES
      (NOW(), OLD.id_cpte, OLD.etat_cpte, OLD.solde, OLD.id_ag);
    RETURN NEW;
  END;
	$BODY$
LANGUAGE plpgsql VOLATILE COST 100;
ALTER FUNCTION trig_insert_ad_cpt_hist() OWNER TO postgres;

DROP TRIGGER IF EXISTS trig_before_update_ad_cpt ON ad_cpt;

CREATE TRIGGER trig_before_update_ad_cpt BEFORE UPDATE ON ad_cpt
FOR EACH ROW EXECUTE PROCEDURE trig_insert_ad_cpt_hist();

----------------------- fin #544 ---------------------------------------------


-----------------------------------------------------------------------------------------------
---------------------------- Ticket #550  : Rapport INVENTAIRE DE DEPOT------------------------
-----------------------------------------------------------------------------------------------

CREATE OR REPLACE FUNCTION patch_550() RETURNS void AS 
$$
DECLARE


BEGIN

RAISE NOTICE 'DEMARRAGE mise a jour base de données pour le ticket #550';

--insert into "ecrans" if notExist
	IF NOT EXISTS(SELECT * FROM ecrans WHERE nom_ecran = 'Era-54') THEN
	
	insert into ecrans (nom_ecran,fichier,nom_menu,fonction)VALUES('Era-54','modules/rapports/rapports_epargne.php','Era-2',330);
		
	END IF;
	
	IF NOT EXISTS(SELECT * FROM ecrans WHERE nom_ecran = 'Era-55') THEN
	
	insert into ecrans (nom_ecran,fichier,nom_menu,fonction)VALUES('Era-55','modules/rapports/rapports_epargne.php','Era-3',330);
		
	END IF;
	
	IF NOT EXISTS(SELECT * FROM ecrans WHERE nom_ecran = 'Era-56') THEN
	
	insert into ecrans (nom_ecran,fichier,nom_menu,fonction)VALUES('Era-56','modules/rapports/rapports_epargne.php','Era-4',330);
		
	END IF;

RAISE NOTICE 'FIN mise a jour base de données pour le ticket #550';

END;

$$
LANGUAGE plpgsql VOLATILE COST 100;
ALTER FUNCTION patch_550() OWNER TO adbanking;

-- ----------------------------------------------------------------------------
-- Execution
-- ----------------------------------------------------------------------------
SELECT patch_550();
DROP FUNCTION patch_550();

----------------------- fin #550 ---------------------------------------------



-----------------------------------------------------------------------------------------------
---------------------------- Ticket #535  : Ajout du montant minimum dépôt--------------------- 
--------------------------initial dans le paramétrage des produits d'épargne-------------------
-----------------------------------------------------------------------------------------------



CREATE OR REPLACE FUNCTION patch_ticket_535()  RETURNS INT AS
$$
DECLARE
	output_result INTEGER = 1;

BEGIN
	RAISE INFO 'MAJ NOUVEAU CHAMPS adsys_produit_epargne' ;
	-- Check if field "mnt_dpt_min" exist in table "adsys_produit_epargne"
	IF NOT EXISTS(SELECT * FROM information_schema.columns WHERE table_name = 'adsys_produit_epargne' AND column_name = 'mnt_dpt_min') THEN
		ALTER TABLE adsys_produit_epargne ADD COLUMN mnt_dpt_min int DEFAULT 0;
		output_result := 2;
	END IF;
	
	
	--insert into "d_tableliste" if notExist
	IF NOT EXISTS(SELECT * FROM d_tableliste WHERE nchmpc = 'mnt_dpt_min') THEN
	
		INSERT INTO d_tableliste(ident, tablen, nchmpc, nchmpl, isreq, ref_field, type, onslct, ispkey, traduit) VALUES ((select max(ident) from d_tableliste)+1, (select ident from tableliste where nomc like 'adsys_produit_epargne' order by ident desc limit 1), 'mnt_dpt_min', maketraductionlangsyst('Montant minimum de dépôt initial (0 si aucun)'), false, NULL, 'mnt', false, false, false);
		output_result := 2;
	END IF;

	RETURN output_result;

END;
$$
LANGUAGE plpgsql;
-- ----------------------------------------------------------------------------
-- Execution
-- ----------------------------------------------------------------------------  
SELECT patch_ticket_535();
DROP FUNCTION patch_ticket_535();

----------------------- fin #535 ---------------------------------------------


-----------------------------------------------------------------------------------------------
---------------------------- Ticket #529 : Gestion des comptes dormants------------------------
-----------------------------------------------------------------------------------------------

---------------------------------------------------------------------------------------------------------------------------------------------------------------
---------------------------------------------------FONCTION :prelevefraistenuecpt
---------------------------------------------------------------------------------------------------------------------------------------------------------------
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
		and a.etat_cpte in (1,4) and b.frais_tenue_cpt > 0 order by a.id_titulaire;

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
	      INSERT INTO ad_ecriture(id_his,id_ag, date_comptable, libel_ecriture, id_jou, id_exo, ref_ecriture,info_ecriture,type_operation)
		VALUES ((SELECT currval('ad_his_id_his_seq')),NumAgc(), date(date_prelev), type_ope.libel_ope, jou1,
		exo.id_exo_compta, makeNumEcriture(jou1, exo.id_exo_compta),ligne.id_cpte,num_ope);

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
	      INSERT INTO ad_ecriture(id_his,id_ag, date_comptable, libel_ecriture, id_jou, id_exo, ref_ecriture,info_ecriture,type_operation)
	VALUES ((SELECT currval('ad_his_id_his_seq')),NumAgc(), date(date_prelev), type_ope.libel_ope, jou1, exo.id_exo_compta, makeNumEcriture(jou1, exo.id_exo_compta),ligne.id_cpte,num_ope);

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
	      INSERT INTO ad_ecriture(id_his,id_ag, date_comptable, libel_ecriture, id_jou, id_exo, ref_ecriture,info_ecriture,type_operation)
		VALUES ((SELECT currval('ad_his_id_his_seq')),NumAgc(), date(date_prelev), type_ope.libel_ope, jou1,
		exo.id_exo_compta, makeNumEcriture(jou1, exo.id_exo_compta),ligne.id_cpte,num_ope);

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
	      INSERT INTO ad_ecriture(id_his,id_ag, date_comptable, libel_ecriture, id_jou, id_exo, ref_ecriture,info_ecriture,type_operation)
	VALUES ((SELECT currval('ad_his_id_his_seq')),NumAgc(), date(date_prelev), type_ope.libel_ope, jou1, exo.id_exo_compta, makeNumEcriture(jou1, exo.id_exo_compta),ligne.id_cpte,num_ope);

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
	      INSERT INTO ad_ecriture(id_his,id_ag, date_comptable, libel_ecriture, id_jou, id_exo, ref_ecriture,info_ecriture,type_operation)
		VALUES ((SELECT currval('ad_his_id_his_seq')),NumAgc(),date(date_prelev), type_ope.libel_ope, jou2,
		exo.id_exo_compta, makeNumEcriture(jou2, exo.id_exo_compta),ligne.id_cpte,num_ope);

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
	      INSERT INTO ad_ecriture(id_his,id_ag, date_comptable, libel_ecriture, id_jou, id_exo, ref_ecriture,info_ecriture,type_operation)
		VALUES ((SELECT currval('ad_his_id_his_seq')),NumAgc(), date(date_prelev), type_ope.libel_ope, jou2,
		exo.id_exo_compta, makeNumEcriture(jou2, exo.id_exo_compta),ligne.id_cpte,num_ope);

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
	      INSERT INTO ad_ecriture(id_his,id_ag, date_comptable, libel_ecriture, id_jou, id_exo, ref_ecriture,info_ecriture,type_operation)
		VALUES ((SELECT currval('ad_his_id_his_seq')),NumAgc(), date(date_prelev), type_ope.libel_ope, jou2,
		exo.id_exo_compta, makeNumEcriture(jou2, exo.id_exo_compta),ligne.id_cpte,num_ope);

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
	      INSERT INTO ad_ecriture(id_his,id_ag, date_comptable, libel_ecriture, id_jou, id_exo, ref_ecriture,info_ecriture,type_operation)
		VALUES ((SELECT currval('ad_his_id_his_seq')),NumAgc(), date(date_prelev), type_ope.libel_ope, jou2,
		exo.id_exo_compta, makeNumEcriture(jou2, exo.id_exo_compta),ligne.id_cpte,num_ope);

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
	   INSERT INTO ad_ecriture(id_his,id_ag, date_comptable, libel_ecriture, id_jou, id_exo, ref_ecriture,info_ecriture,type_operation)
	VALUES ((SELECT currval('ad_his_id_his_seq')),NumAgc(), date(date_prelev), type_ope.libel_ope, id_journal, exo.id_exo_compta, makeNumEcriture(id_journal, exo.id_exo_compta),ligne.id_cpte,num_ope);

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
	  INSERT INTO ad_ecriture(id_his,id_ag, date_comptable, libel_ecriture, id_jou, id_exo, ref_ecriture,info_ecriture,type_operation)
	VALUES ((SELECT currval('ad_his_id_his_seq')),NumAgc(), date(date_prelev), type_ope.libel_ope, id_journal, exo.id_exo_compta, makeNumEcriture(id_journal, exo.id_exo_compta),ligne.id_cpte,num_ope);

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
	  INSERT INTO ad_ecriture(id_his,id_ag, date_comptable, libel_ecriture, id_jou, id_exo, ref_ecriture,info_ecriture,type_operation)
	VALUES ((SELECT currval('ad_his_id_his_seq')),NumAgc(), date(date_prelev),type_ope.libel_ope, id_journal, exo.id_exo_compta, makeNumEcriture(id_journal, exo.id_exo_compta),ligne.id_cpte,num_ope);

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
  
  
---------------------------------------------------------------------------------------------------------------------------------------------------------------
---------------------------------------------------FONCTION :traitecomptesdormants
---------------------------------------------------------------------------------------------------------------------------------------------------------------

-- Function: traitecomptesdormants(date, integer)

-- DROP FUNCTION traitecomptesdormants(date, integer);

CREATE OR REPLACE FUNCTION traitecomptesdormants(date, integer)
  RETURNS SETOF cpte_dormant AS
$BODY$
 DECLARE
	date_batch  ALIAS FOR $1;		-- Date d'execution du batch
	idAgc ALIAS FOR $2;			    -- id de l'agence
	ligne_param_epargne RECORD ;
    ligne RECORD ;
    nbre_cptes INTEGER ;
    ligne_resultat cpte_dormant;
BEGIN
        SELECT INTO ligne_param_epargne cpte_inactive_nbre_jour,cpte_inactive_frais_tenue_cpte 
	FROM adsys_param_epargne
	WHERE id_ag = idAgc ;
        IF ligne_param_epargne.cpte_inactive_nbre_jour IS NOT NULL THEN
		
	 
         DROP TABLE  IF EXISTS temp_ad_cpt_dormant;
         IF ligne_param_epargne.cpte_inactive_frais_tenue_cpte IS NULL OR ligne_param_epargne.cpte_inactive_frais_tenue_cpte=FALSE THEN 

	  CREATE TEMP TABLE temp_ad_cpt_dormant as SELECT  id_cpte,id_titulaire,solde,c.devise
	  FROM ad_mouvement a , ad_cpt b, adsys_produit_epargne c
	  WHERE a.id_ag=b.id_ag AND a.id_ag=c.id_ag AND b.id_ag=c.id_ag AND c.id_ag =  idAgc  
	  AND cpte_interne_cli = id_cpte AND b.id_prod = c.id  AND classe_comptable=1 AND c.retrait_unique =FALSE AND c.depot_unique = FALSE 
          AND c.passage_etat_dormant = 'true'
          AND etat_cpte not in (2,4) 
          GROUP BY id_cpte,id_titulaire ,solde,c.devise
          HAVING DATE(date_batch) -max(date_valeur) > ligne_param_epargne.cpte_inactive_nbre_jour 
		UNION	
		SELECT  id_cpte,id_titulaire,solde,c.devise
	  FROM ad_mouvement a , ad_cpt b, adsys_produit_epargne c, ad_ecriture d
	  WHERE a.id_ag=b.id_ag AND a.id_ag=c.id_ag AND b.id_ag=c.id_ag AND c.id_ag =  idAgc  
		AND a.id_ecriture = d.id_ecriture and d.id_ag = c.id_ag
	  AND cpte_interne_cli = id_cpte AND b.id_prod = c.id  AND classe_comptable=1 AND c.retrait_unique =FALSE AND c.depot_unique = FALSE 
          AND c.passage_etat_dormant = 'true' and type_operation = 50
          AND etat_cpte not in (2,4)
          GROUP BY id_cpte,id_titulaire ,solde,c.devise
          HAVING DATE(date_batch) -max(date_valeur) < ligne_param_epargne.cpte_inactive_nbre_jour ;

        ELSE
          CREATE TEMP TABLE temp_ad_cpt_dormant as SELECT  id_cpte,id_titulaire,solde,c.devise
	  FROM ad_mouvement a , ad_cpt b, adsys_produit_epargne c
	  WHERE a.id_ag=b.id_ag AND a.id_ag=c.id_ag AND b.id_ag=c.id_ag AND c.id_ag = idAgc 
	  AND cpte_interne_cli = id_cpte AND b.id_prod = c.id  AND classe_comptable=1 AND c.retrait_unique =FALSE AND c.depot_unique = FALSE 
          AND c.passage_etat_dormant = 'true'
          AND etat_cpte not in (2,4)
          GROUP BY id_cpte,id_titulaire ,solde,c.devise
          HAVING DATE(date_batch) -max(date_valeur) > ligne_param_epargne.cpte_inactive_nbre_jour ;
       END IF;

        UPDATE ad_cpt a SET  etat_cpte = 4,date_blocage= DATE(now()), raison_blocage = 'Compte dormant'
        WHERE id_cpte in  ( SELECT id_cpte FROM temp_ad_cpt_dormant);
       FOR ligne_resultat IN SELECT  * FROM temp_ad_cpt_dormant
	   	LOOP
	   		RETURN NEXT ligne_resultat;
	   	END LOOP;
	   		
        
      ELSE 
	 	RETURN  ;
      END IF ;
      RETURN  ;
END;
$BODY$
  LANGUAGE plpgsql VOLATILE
  COST 100
  ROWS 1000;
ALTER FUNCTION traitecomptesdormants(date, integer)
  OWNER TO postgres;

 ----------------------- fin #529 ---------------------------------------------
 
 
------------------------------------------------------------------------------------------------
------------------------------Ticket #326 : MAJ NOUVEAU CHAMPS ad_sre---------------------------
------------------------------------------------------------------------------------------------


CREATE OR REPLACE FUNCTION nouveau_champs_326()  RETURNS INT AS $$
DECLARE
	output_result INTEGER = 1;

BEGIN
	RAISE INFO 'MAJ NOUVEAU CHAMPS ad_sre.annul_remb' ;
	-- Check if field "annul_remb" exist in table "ad_sre"
	IF NOT EXISTS(SELECT * FROM information_schema.columns WHERE table_name = 'ad_sre' AND column_name = 'annul_remb') THEN
		ALTER TABLE ad_sre ADD COLUMN annul_remb int DEFAULT null;
		output_result := 2;
	END IF;
	
		RAISE INFO 'MAJ NOUVEAU CHAMPS ad_sre.id_his' ;
	-- Check if field "annul_remb" exist in table "id_his"
	IF NOT EXISTS(SELECT * FROM information_schema.columns WHERE table_name = 'ad_sre' AND column_name = 'id_his') THEN
		ALTER TABLE ad_sre ADD COLUMN id_his int DEFAULT null;
		output_result := 2;
	END IF;	

	RETURN output_result;

END;
$$
LANGUAGE plpgsql;
-- --------------
-- Execution
-- ---------------
SELECT nouveau_champs_326();
DROP FUNCTION nouveau_champs_326();

---------------------------------------Ticket #558 :------------------------------------------- 
--------------------Reprise de données de ad_cpt pour les comptes DAT - ref. #544---------------
-----------------------------------MAJ schema ad_dcr_hist---------------------------------------


CREATE OR REPLACE FUNCTION patch_558() RETURNS void AS $BODY$
BEGIN

  IF (SELECT count(*) from information_schema.columns WHERE table_name = 'ad_cpt_hist' AND column_name='id_his') = 0 THEN
    ALTER TABLE ad_cpt_hist ADD COLUMN id_his integer;    
  END IF;

  IF (SELECT count(*) from information_schema.columns WHERE table_name = 'ad_cpt_hist' AND column_name='login') = 0 THEN
    ALTER TABLE ad_cpt_hist ADD COLUMN login text;
  END IF;

  IF (SELECT count(*) from information_schema.columns WHERE table_name = 'ad_cpt_hist' AND column_name='type_fonction') = 0 THEN
    ALTER TABLE ad_cpt_hist ADD COLUMN type_fonction integer;
  END IF;
  IF (SELECT count(*) from information_schema.columns WHERE table_name = 'ad_cpt_hist' AND column_name='id_titulaire') = 0 THEN
    ALTER TABLE ad_cpt_hist ADD COLUMN id_titulaire integer;
  END IF;

  RAISE INFO 'MAJ schema ad_cpt_hist ';  

END;
$BODY$
LANGUAGE plpgsql VOLATILE COST 100;
ALTER FUNCTION patch_558() OWNER TO adbanking;

--------------------- Execution ------------------------------
SELECT patch_558();
Drop function patch_558();
--------------------------------------------------------------

--------------------------------fin MAJ schema(Ticket #558)-------------------

-------------------------Ticket #522 : Mise à jours des états de crédits----------------------- 
-----------------------dans la table d'historisation des états(ad_dcr_hist)--------------------
-----------------------------------MAJ trigger ad_dcr_hist-------------------------------------

-- Function: trig_insert_ad_cpt_hist()

-- DROP FUNCTION trig_insert_ad_cpt_hist();
CREATE OR REPLACE FUNCTION trig_insert_ad_dcr_hist()
  RETURNS trigger AS
$BODY$
	BEGIN
	    --credit soldé(copy date_etat)
	   IF((NEW.etat = 6 AND OLD.etat = 5) OR (NEW.etat = 6 AND OLD.etat = 9))  THEN 
                INSERT INTO ad_dcr_hist
	         (date_action, id_doss, etat, cre_etat, cre_mnt_deb, id_ag)
	        VALUES
	         (OLD.date_etat, OLD.id_doss, OLD.etat, OLD.cre_etat, OLD.cre_mnt_deb, OLD.id_ag);
	        RETURN NEW;

	    ELSE
	    --declassement/ reclassement ou  radiation(copy cre_date_etat)
	        INSERT INTO ad_dcr_hist
	         (date_action, id_doss, etat, cre_etat, cre_mnt_deb, id_ag)
	        VALUES
	         (OLD.cre_date_etat, OLD.id_doss, OLD.etat, OLD.cre_etat, OLD.cre_mnt_deb, OLD.id_ag);
	        RETURN NEW;

	    END IF;
	    
    END;
$BODY$
  LANGUAGE plpgsql VOLATILE
  COST 100;
ALTER FUNCTION trig_insert_ad_dcr_hist()
  OWNER TO postgres;
 
--------------------------------fin MAJ trigger (Ticket #522)-------------------

-----------------------------------------------------------------------------------------------
------------------ #573 : Rendre optionnelle le nouveau solde du bordéreau ------------------
-----------------------------------------------------------------------------------------------
-----------------------------------------------------------------------------------------------------------------------------------------------------
CREATE OR REPLACE FUNCTION nouveau_champs_573()   RETURNS void AS $BODY$

BEGIN
	RAISE INFO 'MAJ NOUVEAU CHAMPS AD_AGC' ;
	-- Check if field "param_affiche_solde" exist in table "ad_agc"
	IF NOT EXISTS(SELECT * FROM information_schema.columns WHERE table_name = 'ad_agc' AND column_name = 'param_affiche_solde') THEN
		ALTER TABLE ad_agc ADD COLUMN param_affiche_solde BOOLEAN DEFAULT true;
		
	END IF;
	
	
	--insert into "d_tableliste" if notExist
	IF NOT EXISTS(SELECT * FROM d_tableliste WHERE nchmpc = 'param_affiche_solde') THEN
	
		INSERT INTO d_tableliste(ident, tablen, nchmpc, nchmpl, isreq, ref_field, type, onslct, ispkey, traduit) VALUES ((select max(ident) from d_tableliste)+1, (select ident from tableliste where nomc like 'ad_agc' order by ident desc limit 1), 'param_affiche_solde', maketraductionlangsyst('Affichage solde sur recu'), true, NULL, 'bol', false, false, false);
		
	END IF;

	

END;
$BODY$
LANGUAGE plpgsql VOLATILE COST 100;
ALTER FUNCTION nouveau_champs_573() OWNER TO adbanking;
  
SELECT nouveau_champs_573();
DROP FUNCTION nouveau_champs_573();

----------------------------- Fin #573 --------------------------------------------------------------

 
 -------------------------Ticket #102[PROJECT_PRO] : Modification Paramétrage agence----------------------- 
----------------------------------gestion des etats de credits(id_etat_prec)------------------------------
-----------------------------------MAJ trigger proc_ad_agc_passage_perte_automatique()------------------------------------------------

-- Function: proc_ad_agc_passage_perte_automatique()

-- DROP FUNCTION proc_ad_agc_passage_perte_automatique();

CREATE OR REPLACE FUNCTION proc_ad_agc_passage_perte_automatique()
  RETURNS trigger AS
$BODY$
DECLARE
 etat_perte RECORD;
 etat_radier RECORD;

BEGIN
SELECT INTO etat_perte id, id_etat_prec FROM adsys_etat_credits WHERE nbre_jours = -1 and id_ag = NEW.id_ag;
SELECT INTO etat_radier id, id_etat_prec FROM adsys_etat_credits WHERE nbre_jours = -2 and id_ag = NEW.id_ag;
IF (OLD.passage_perte_automatique != NEW.passage_perte_automatique) THEN
   IF (NEW.passage_perte_automatique ='f' ) THEN -- De passage en perte automatique Ã  passage en perte manuelle(true -> false)
 	UPDATE adsys_etat_credits SET id_etat_prec = NULL where nbre_jours = -1 and id_ag = NEW.id_ag;
 	UPDATE adsys_etat_credits SET id_etat_prec = NULL where nbre_jours = -2 and id_ag = NEW.id_ag;
   ELSE -- De passage en perte manuelle Ã  passage en perte automatique(false -> true)
	UPDATE adsys_etat_credits SET id_etat_prec = (etat_perte.id-1) where nbre_jours = -1 and id_ag = NEW.id_ag;
	UPDATE adsys_etat_credits SET id_etat_prec = (etat_radier.id-1) where nbre_jours = -2 and id_ag = NEW.id_ag;
   END IF;
END IF;
RETURN NEW;
END;
$BODY$
  LANGUAGE plpgsql VOLATILE
  COST 100;
ALTER FUNCTION proc_ad_agc_passage_perte_automatique()
  OWNER TO adbanking;

--------------------------------fin MAJ trigger (Ticket #102[PROJECT_PRO])-------------------
 

 -------------------------Ticket #159[PROJECT_PRO](Ticket 340) : Tests de non régression_suppression ----------------------- 
-----------------------------------------d'un remboursement pour un crédit radiés--------------------------------
-- #340
-- Ajout opération 411 : Annulation recouvrement sur crédit en perte
CREATE OR REPLACE FUNCTION fix_ticket_340() RETURNS integer AS $BODY$
BEGIN

IF NOT EXISTS (SELECT type_operation FROM ad_cpt_ope WHERE type_operation = 411) THEN
INSERT INTO ad_cpt_ope (type_operation, libel_ope, categorie_ope, id_ag) 
VALUES (411, maketraductionlangsyst('Annulation recouvrement sur crédit en perte'), 1, NumAgc());

INSERT INTO ad_cpt_ope_cptes VALUES (411,NULL, 'c', 1, NumAgc());

INSERT INTO ad_cpt_ope_cptes (type_operation, num_cpte, sens, categorie_cpte, id_ag)
VALUES (411, (SELECT num_cpte FROM ad_cpt_ope_cptes WHERE type_operation = 410 AND sens = 'c' AND id_ag = NumAgc() LIMIT 1), 
'd', 7, NumAgc());

RAISE NOTICE 'Type opération 411 ajouté.';
RETURN 1;

ELSE
 RAISE NOTICE 'Type opération 411 existe déjà. Ajout annulé !';
 RETURN 0;
END IF;

END;
$BODY$

LANGUAGE plpgsql VOLATILE COST 100;
ALTER FUNCTION fix_ticket_340() OWNER TO adbanking;

SELECT fix_ticket_340();
DROP FUNCTION fix_ticket_340();


--------------------------------fin MAJ (Ticket #159[PROJECT_PRO])-------------------

------------- Ticket #580 :  ---------------------------------------
CREATE OR REPLACE FUNCTION calculnombrejoursretardech(integer, integer, date, integer)
  RETURNS double precision AS
$BODY$
DECLARE
        iddoss ALIAS FOR $1;
	idech ALIAS FOR $2;
	date_arrete ALIAS FOR $3;
        id_agence ALIAS FOR $4;
	max_date DATE;
	dateech DATE;
	isremb boolean;
	nbr_jours_retard DOUBLE PRECISION;
BEGIN
       SELECT INTO dateech,isremb date_ech, remb FROM ad_etr WHERE id_ag = id_agence AND id_doss = iddoss AND id_ech = idech;
       nbr_jours_retard := date_part('day', date_arrete::timestamp - dateech::timestamp);
       -- Pour les échéances remboursées avant la date d'arrete: nbr_jours_retard = 0
       SELECT INTO max_date MAX(date_remb) FROM ad_sre WHERE id_ag = id_agence AND id_doss = iddoss AND id_ech = idech;

       IF ((max_date < date_arrete) AND (isremb = 't')) THEN
		nbr_jours_retard := 0;
       END IF;
       RETURN nbr_jours_retard;
END;
$BODY$
  LANGUAGE plpgsql VOLATILE
  COST 100;
ALTER FUNCTION calculnombrejoursretardech(integer, integer, date, integer)
  OWNER TO adbanking;
