--Script permettant de prélever les frais de tenue de compte
/*

IN : 
	- fréquence : indique pour quelle périodicité on calcule (mensuelle,
	trimestrielle, semestrielle, annuelle. Les comptes seront filrés sur ce critère.
	Une fréquence supérieure implique la fréquence inférieure (semestriel implique
	trimestriel et mensuel) 

	- date de prélèvement des frais

	- numéro de la fonction associée au prélèvement des frais de tenue de compte


OUT :
	- Tabeau contenant pour chaque prélèvement : 
           - l'identifiant du compte sur lequel les frais sont prélevés
           - le solde de ce compte après le prélèvement
           - le montant rélevé sur le compte  
	

Le script doit etre appelé dans une paire BEGIN - COMMIT

				Algo
				----

	1/ Déterminer tous les comptes à prélever en fonction de la fréquence (curseur). 
	Ceci  revient à chercher les produits d'épargne concernés et le montant des frais
	(vérifier que frais tenue > 0)
	2/ Récupérer le libellé et le compte au crédit de l'opération
	3/ Récupérer la devise du compte au crédit de l'opération
	4/ Récupérer son joournal associé si compte au crédit est principal
	5/ Récupérer le numéro de l'exercice contenant la date de prélèvement
	6/ Récupérer la devise de référence
	7/ Créer un id_his global pour toute l'opération
 	8/ Pour chaque compte
		8-1 Vérifier si le solde disponible du compte suffit à payer les frais de tenue 
		8-2 Si le solde est suffisant
			8-2-1 Récupérer le journal associé si le compte comptable du produit est principal
			8-2-2 Si le compte du produit et le compte au crédit de l'opération sont principaux de journaux différents
				8-2-2-1 Récupérer le compte de liason des deux journaux

				********* DEBIT DU COMPTE ASSOCIE AU PRODUIT PAR LE CREDIT DU COMPTE DE LIAISON
				8-2-2-2 Si compte du produit et compte de liaison on la même devise
					8-2-2-2-1 Diminuer le solde du compte interne des frais de prélèvement
					8-2-2-2-2 Passer une écriture comptable dans le journal du compte associé au produit
					8-2-2-2-3 Débiter le compte associé au produit
					8-2-2-2-4 Créditer le compte de l'opération de prélèvement
				8-2-2-3	Sinon Si compte du produit a la devise de référence et compte de liaison une devise étrangère
					8-2-2-3-1 Récupérer le compte de position de change de la devise du compte de liaison
					8-2-2-3-2 Récupérer le compte de c/v de la position de change de la devise du cpte de liaison
					8-2-2-3-3 Diminer le solde du compte interne des frais de tenue
					8-2-2-3-4 Passer une écriture comptable dans le journal du compte associé au produit
					8-2-2-3-5 Débiter le compte associé au produit
					8-2-2-3-6 Créditer le compte de c/v 
					8-2-2-3-7 Convertir les frais dans la devise du compte de liaison
					8-2-2-3-8 Débiter le compte de position de change de ce montant
					8-2-2-3-9 Créditer le compte de liaison de ce montant
				8-2-2-4	Sinon Si compte de liaison a la devise de référence et compte du produit une devise étrangère
					8-2-2-4-1 Récupérer le compte de position de la devise du compte du produit
					8-2-2-4-2 Récupérer le compte de c/v de la devise du compte de produit
					8-2-2-4-3 Prélever les frais sur le solde du compte interne
					8-2-2-4-4 Convertire les frais dans la devise du compte de liaison (devise de référence) 
					8-2-2-4-5 Passer une écriture comptable dans le journal du compte associé au produit
					8-2-2-4-6 Débiter le compte associé au produit des frais dans la devise du cpte du produit
					8-2-2-4-7 Créditer le compte de position de change du même montant
					8-2-2-4-8 Convertir les frais dans la devise de référence
					8-2-2-4-9 Débiter la c/v de ce montant
					8-2-2-4-10 Créditer le compte de liaison de ce montant
				8-2-2-5 Sinon Si le cpte associé au produit et le cpte de liason ont des devises étrangères différentes 
					8-2-2-5-1 Prélever les frais sur le solde du compte interne
					8-2-2-5-2 Passer une écriture dans le journal du compte associé au produit
					8-2-2-5-3 Débiter le compte associé au produit
					8-2-2-5-4 Récupérer le compte de position de la devsie du compte associé au produit
					8-2-2-5-5 Créditer le compte de position de change
					8-2-2-5-6 Convertir les frais dans la devise de référence
					8-2-2-5-7 Débiter le compte de c/v de la devise du compte associé au produit de ce montant
					8-2-2-5-8 Créditer le compte de c/v de la devise du compte de liaison
					8-2-2-5-9 Convertir les frais dans la devise du compte de liaison
					8-2-2-5-10 Créditer le compte de liaison de ce montant
					8-2-2-5-11 Récupérer le compte de position de change de la devise du compte de liaison
					8-2-2-5-12 Débiter le compte de position de change de ce montant

				****** DEBIT DU COMPTE DE LIAISON PAR LE CREDIT DU COMPTE DE L'OPERATION
				8-2-2-6 Si compte au crédit de l'opération et compte de liaison ont la même devise
					8-2-2-6-1 Convertir les frais dans cette devise
					8-2-2-6-2 Passer une écriture dans le journal associé au compte de crédit de l'opération
					8-2-2-6-3 Débiter le compte de liaison 
					8-2-2-6-4 Créditer le compte au crédit de l'opération
				8-2-2-7 Sinon Si cpte de liason a la devise de référence et cpte de l'opération une devise étrangère
					8-2-2-7-1 Récupérer le compte de c/v de la devise du compte de l'opération
					8-2-2-7-2 Récupérer le compte de position de la devise du compte de l'opération
					8-2-2-7-3 Convertir les frais dans la devise de référence
					8-2-2-7-4 Passer une écriture dans le journal associé au compte de crédit de l'opération
					8-2-2-7-5 Débiter le compte de liaison
					8-2-2-7-6 Créditer le compte de c/v
					8-2-2-7-7 Convertir les frais dans la devise du compte au crédit de l'oération
					8-2-2-7-8 Débiter le compte de position de change
					8-2-2-7-9 Créditer le compte de crédit de l'opération
				8-2-2-8 Sinon si cpte de l'opération a la devise de référence et cpte de liaison une devise étrangère
					8-2-2-8-1 Récupérer le compte de position de change de la devise du compte de liaison
					8-2-2-8-2 Récupérer le compte de c/v de la devise du compte de liaison 
					8-2-2-8-3 Passer une écriture comptable dans le journal du compte de crédit de l'opération 
					8-2-2-8-4 Convertir les frais dans la devise de référence
					8-2-2-8-5 Créditer le compte au crédit de l'opération
					8-2-2-8-6 Débiter le compte de c/v de la devise du compte de liaison
					8-2-2-8-7 Convertir les frais dans la devise du compte de liaison
					8-2-2-8-8 Débiter le compte de liaison 
					8-2-2-8-9 Créditer le compte de position de change associé à la devise du compte de liasion
				8-2-2-9 Sinon si cpte au crédit de l'opération et cpte de liaison ont des devise étrangères différentes
					8-2-2-9-1 Passer une écriture comptable dans le journal associé au compte de l'opération
					8-2-2-9-2 Convertir les frais dans la devise du compte de liaison
					8-2-2-9-3 Débiter le compte de liaison
					8-2-2-9-4 Récupérer le compte de position de change associé à la devise du compte de liaison
					8-2-2-9-5 Créditer le compte de position de change
					8-2-2-9-6 Convertir les frais dans la devise de référence
					8-2-2-9-7 Récupérer le compte de c/v associé à la devise du compte de liaison
					8-2-2-9-8 Débiter ce compte de c/v 
					8-2-2-9-10 Récupérer le compte de c/v associé à la devise du compte au crédit de l'opération
					8-2-2-9-11 CRéditer ce compte de c/v
					8-2-2-9-12 Convertir les frais dans la devise du compte au crédit de l'opération 
					8-2-2-9-13 Créditer le compte au ccrédit de l'opération de ce montant
					8-2-2-9-14 Récupérer le compte de position de change associé à la devise du compte de l'opération
					8-2-2-9-15 Débiter ce compte de position de change
			8-2-3 Sinon Si au moins un des comptes n'est pas principal ou les deux sont principaux du même journal
			***** Pas besoin de compte de liaison
				8-2-3-1 Choisir le journal adéquat
				8-2-3-2 Si devise du compte associé au produit est la devise de référence
					8-2-3-2-1 Prélever les frais sur le solde du compte interne
					8-2-3-2-2 Passer une écriture dans le journal choisi
					8-2-3-2-3 Débiter le compte associé au produit
					8-2-3-2-4 Créditer le compte au crédit de l'opération
				8-2-3-3 Sinon Si la devise du compte de produit est étrangère 
					8-2-3-3-1 Récupérer son compte de position de change
					8-2-3-3-2 Récupérer son compte de c/v
					8-2-3-3-3 Prélever les frais dans le solde du compte interne
					8-2-3-3-4 Passer une écriture comptable dans le journal choisi
					8-2-3-3-5 Débiter le compte associé au produit
					8-2-3-3-6 Créditer le compte de position de change
					8-2-3-3-7 Convertir les frais dans la devise de référence
					8-2-3-3-8 Passer une écriture comptable dans le journal choisi
					8-2-3-3-9 Débiter le compte de c/v
					8-2-3-3-10 Créditer le compte au crédit de l'opération
			8-2-4 Aouter le prélèvement dans le tableau à renvoyer
		8-3 Sinon ******* le solde est insuffisant
			8-3-1 Ajouter une entrée dans la table des attentes de faris

*/ 
-- Création d'un type pour contenir quelque infos des comptes sur lesquels on a prélevé des frais de tenue
CREATE TYPE cpte_frais AS (
	num_cpte_frais text,
	devise_frais char(3),
	id_titulaire_frais int4,
	solde_initial_frais NUMERIC(30,6),
	interet_frais NUMERIC(30,6)
);



CREATE OR REPLACE FUNCTION PreleveFraisTenueCpt(INTEGER, TEXT, INTEGER) 
RETURNS SETOF cpte_frais AS '

DECLARE 
	cur_date TIMESTAMP;
	freq_tenue_cpt ALIAS FOR $1;
	date_prelev ALIAS FOR $2;
	num_ope ALIAS FOR $3;
	jou1	INTEGER;	               -- id du journal associé au compte au débit s''il est principal
	jou2	INTEGER;	               -- id du journal associé au compte au crédit s''il est principal
	id_journal	INTEGER;	       -- id du journal des mouvements comptables
	nbre_devises	INTEGER;	       -- Nombre de devises créées
	mode_multidev	BOOLEAN;	       -- Mode multidevise ?
	devise_cpte_cr CHAR(3);		       -- Code de la devise du compte au crédit
	code_dev_ref CHAR(3);		       -- Code de la devise de référence
	devise_cpte_debit CHAR(3);	       -- Code de la devise du compte comptable associé au produit d''épargne
	cpt_pos_ch TEXT;		       -- Compte de position de change de la devise du compte traité
        cpt_cv_pos_ch TEXT;		       -- Compte de C/V de la Pos de Ch de la devise du compte traité
	cv_frais_tenue_cpte NUMERIC(30,6);     -- C/V des frais de tenue de compte
	num_cpte_debit TEXT;		       -- Compte comptable à débiter
	cpte_liaison TEXT;                     -- Compte de liaison si les deux comptes à mouvementer sont principaux  
	devise_cpte_liaison CHAR(3);		       -- Code de la devise de référence
	infos_cpte RECORD;                    -- array contenant quelques informations du compte traité
	compte_frais cpte_frais;	       -- array contenant l''id, le solde et les frais des comptes traités
	exo RECORD; -- infos sur l''exercice contenant la date de prélèvement des frais	
	type_ope RECORD; -- infos sur l''opérationn de prélèvement des frais
	
	-- Recupere des infos sur les compte épargne à prélever et leurs produits associés  
	Cpt_Prelev CURSOR FOR 
		select a.id_cpte, a.id_titulaire,a.solde, a.devise, a.num_complet_cpte, b.frais_tenue_cpt as total_frais_tenue_cpt, b.cpte_cpta_prod_ep
		from ad_cpt a, adsys_produit_epargne b where a.id_prod = b.id and (frequence_tenue_cpt BETWEEN 1 and freq_tenue_cpt) 
		and a.etat_cpte = 1 and b.frais_tenue_cpt > 0 order by a.id_titulaire;

	ligne RECORD;

	ligne_ad_cpt ad_cpt%ROWTYPE;

	cpte_base INTEGER;

	solde_dispo_cpte NUMERIC(30,6);

BEGIN

  -- Recherche du libellé et du compte au crédit de type opération
  SELECT INTO type_ope libel_ope , num_cpte FROM ad_cpt_ope a, ad_cpt_ope_cptes b WHERE a.type_operation = num_ope AND a.type_operation=b.type_operation AND b.sens = ''c''; 

  -- Récupération de la devise du compte au crédit 
  SELECT INTO devise_cpte_cr devise FROM ad_cpt_comptable WHERE num_cpte_comptable = type_ope.num_cpte;

  -- Récupération du journal associé si le compte au crédit est principal
  SELECT INTO jou2 recupeJournal(type_ope.num_cpte);

  -- Recherche du numéro de l''exercice contenant la date de prélèvement 
  SELECT INTO exo id_exo_compta FROM ad_exercices_compta WHERE date_deb_exo<= date(date_prelev) AND date_fin_exo >= date(date_prelev);

  -- Récupération du nombre de devises
  SELECT INTO nbre_devises count(*) from devise;

  IF nbre_devises = 1 THEN
    mode_multidev := false;
  ELSE 
    mode_multidev := true;
  END IF;

  -- Récupération de la devise de référence 
  SELECT INTO code_dev_ref code_devise_reference FROM ad_agc;

  cur_date := ''now'';

  OPEN Cpt_Prelev;
  FETCH Cpt_Prelev INTO ligne;

  --ajout historique à condition qu''on ait trouvé des comptes à traiter
  IF FOUND THEN
    INSERT INTO ad_his (type_fonction, infos,  date) 
    VALUES (212, ''Prelevement des frais de tenue de compte'', date(date_prelev));
  END IF;

		
  WHILE FOUND LOOP

    --calculer le solde disponible du compte en enlevant les frais de tenue

    SELECT INTO solde_dispo_cpte(a.solde - a.mnt_bloq - b.mnt_min - ligne.total_frais_tenue_cpt) 
    FROM ad_cpt a, adsys_produit_epargne b 
    WHERE a.id_cpte=ligne.id_cpte AND a.id_prod = b.id;

    RAISE NOTICE ''Solde dispo pour compte % = %'', ligne.id_cpte, solde_dispo_cpte;

    IF (solde_dispo_cpte >= 0) THEN

      -- RECUPERATION DE LA DEVISE DU COMPTE ASSOCIE AU PRODUIT      
      SELECT INTO devise_cpte_debit devise FROM ad_cpt_comptable WHERE num_cpte_comptable = ligne.cpte_cpta_prod_ep;    

      -- Construction du numéro de compte à débiter  
      IF devise_cpte_debit IS NULL THEN
        num_cpte_debit := ligne.cpte_cpta_prod_ep || ''.'' || ligne.devise;
      ELSE
        num_cpte_debit := ligne.cpte_cpta_prod_ep;
      END IF;

      RAISE NOTICE ''compte au débit (compte associé au produit ) %'', num_cpte_debit;

      -- Récupération du journal associé si le compte est principal
      SELECT INTO jou1 recupeJournal(num_cpte_debit);

       IF jou1 IS NOT NULL AND jou2 IS NOT NULL AND jou1 != jou2 THEN

        -- num_cpte_debit ET COMPTE AU CREDIT SONT PINCIPAUX ET DE JOURNAUX DIFFERENTS , ON RECUPERE ALORS LE COMPTE DE LIAISON  

          SELECT INTO cpte_liaison num_cpte_comptable FROM ad_journaux_liaison WHERE (id_jou1=jou1 AND id_jou2=jou2) OR (id_jou1=jou2 AND id_jou2=jou1);
          RAISE NOTICE ''compte de liason entre journal % et journal %  est %'', jou1, jou2, cpte_liaison;

          -- DEVISE DU COMPTE DE LIAISON
          SELECT INTO devise_cpte_liaison devise FROM ad_cpt_comptable WHERE num_cpte_comptable = cpte_liaison;
          RAISE NOTICE ''Devise du compte de liason : % '', devise_cpte_liaison;

          
         ---------- DEBIT COMPTE CLIENT PAR CREDIT DU COMPTE DE LIAISON -----------------------
          IF ligne.devise = devise_cpte_liaison THEN  ----- num_cpte_debit et cpte_liaison sont de la même devise

              -- prelevement des frais sur le compte du client
              UPDATE ad_cpt SET solde = solde - ligne.total_frais_tenue_cpt WHERE (id_cpte = ligne.id_cpte);
		  
              -- Ecriture comptable
	      INSERT INTO ad_ecriture(id_his, date_comptable, libel_ecriture, id_jou, id_exo, ref_ecriture)
		VALUES ((SELECT currval(''ad_his_id_his_seq'')), date(date_prelev), type_ope.libel_ope, jou1,
		exo.id_exo_compta, makeNumEcriture(jou1, exo.id_exo_compta));
           
              -- Mouvement comptable au débit 
	      INSERT INTO ad_mouvement (id_ecriture, compte, cpte_interne_cli, sens, montant, devise, date_valeur)
	VALUES ((SELECT currval(''ad_ecriture_seq'')),num_cpte_debit,ligne.id_cpte, ''d'', ligne.total_frais_tenue_cpt, ligne.devise, date(date_prelev));

	      -- Mouvement comptable au crédit 
	      INSERT INTO ad_mouvement (id_ecriture, compte, cpte_interne_cli, sens, montant, devise, date_valeur)
	VALUES ((SELECT currval(''ad_ecriture_seq'')), cpte_liaison, NULL, ''c'', ligne.total_frais_tenue_cpt,ligne.devise, date(date_prelev));
		
	      -- Mise à jour des soldes comptables
	      UPDATE ad_cpt_comptable set solde = solde - ligne.total_frais_tenue_cpt WHERE num_cpte_comptable = num_cpte_debit;
	      UPDATE ad_cpt_comptable set solde = solde + ligne.total_frais_tenue_cpt WHERE num_cpte_comptable = cpte_liaison;

          ELSE --------- num_cpte_debit et cpte_liaison n''ont pas la même devise, faire la conversion 

           --------- si num_cpte_debit a la devise de référence et cpte_liaison une devise étrangère  
           IF ligne.devise = code_dev_ref THEN
             
              SELECT INTO cpt_pos_ch cpte_position_change || ''.'' || devise_cpte_liaison FROM ad_agc;
              SELECT INTO cpt_cv_pos_ch cpte_contreval_position_change || ''.'' || devise_cpte_liaison FROM ad_agc;

              -- prelevement des frais sur le compte du client
              UPDATE ad_cpt SET solde = solde - ligne.total_frais_tenue_cpt WHERE (id_cpte = ligne.id_cpte);

              -- Ecriture comptable
	      INSERT INTO ad_ecriture(id_his, date_comptable, libel_ecriture, id_jou, id_exo, ref_ecriture)
	VALUES ((SELECT currval(''ad_his_id_his_seq'')), date(date_prelev), type_ope.libel_ope, jou1, exo.id_exo_compta, makeNumEcriture(jou1, exo.id_exo_compta));

              -- Mouvement comptable au débit 
	      INSERT INTO ad_mouvement (id_ecriture, compte, cpte_interne_cli, sens, montant, devise, date_valeur)
	VALUES ((SELECT currval(''ad_ecriture_seq'')),num_cpte_debit,ligne.id_cpte, ''d'', ligne.total_frais_tenue_cpt, ligne.devise, date(date_prelev));

	      -- Mouvement comptable au crédit de la c/v du compte de liaison  
	      INSERT INTO ad_mouvement (id_ecriture, compte, cpte_interne_cli, sens, montant, devise, date_valeur)
	VALUES ((SELECT currval(''ad_ecriture_seq'')), cpt_cv_pos_ch, NULL, ''c'', ligne.total_frais_tenue_cpt,ligne.devise, date(date_prelev));
              -- montant dans la devise du compte de liaison 
              SELECT INTO cv_frais_tenue_cpte CalculeCV(ligne.total_frais_tenue_cpt, ligne.devise, devise_cpte_liaison);

              -- Mouvement comptable au débit de la position de change du compte de liaison 
	      INSERT INTO ad_mouvement (id_ecriture, compte, cpte_interne_cli, sens, montant, devise, date_valeur)
	VALUES ((SELECT currval(''ad_ecriture_seq'')),cpt_pos_ch,NULL, ''d'', cv_frais_tenue_cpte,devise_cpte_liaison,date(date_prelev));

              -- Mouvement comptable au crédit du compte de liaison 
	      INSERT INTO ad_mouvement (id_ecriture, compte, cpte_interne_cli, sens, montant, devise, date_valeur)
	VALUES ((SELECT currval(''ad_ecriture_seq'')),cpte_liaison ,NULL, ''c'',cv_frais_tenue_cpte, devise_cpte_liaison, date(date_prelev));

	      -- Mise à jour des soldes comptables
	      UPDATE ad_cpt_comptable set solde = solde - ligne.total_frais_tenue_cpt WHERE num_cpte_comptable = num_cpte_debit;
	      UPDATE ad_cpt_comptable set solde = solde + ligne.total_frais_tenue_cpt WHERE num_cpte_comptable = cpt_cv_pos_ch;
              UPDATE ad_cpt_comptable set solde = solde - cv_frais_tenue_cpte WHERE num_cpte_comptable = cpt_pos_ch;
              UPDATE ad_cpt_comptable set solde = solde + cv_frais_tenue_cpte WHERE num_cpte_comptable = cpte_liaison;


           END IF; -- FIN IF ligne.devise = code_dev_ref

           -------- si cpte_liaison a la devise de référence et num_cpte_debit une devise étrangère  
           IF devise_cpte_liaison = code_dev_ref THEN
             
              SELECT INTO cpt_pos_ch cpte_position_change || ''.'' || ligne.devise FROM ad_agc;
              SELECT INTO cpt_cv_pos_ch cpte_contreval_position_change || ''.'' || ligne.devise FROM ad_agc;

              -- prelevement des frais sur le compte du client
              UPDATE ad_cpt SET solde = solde - ligne.total_frais_tenue_cpt WHERE (id_cpte = ligne.id_cpte);

              -- montant dans la devise du compte de liaison 
              SELECT INTO cv_frais_tenue_cpte CalculeCV(ligne.total_frais_tenue_cpt, ligne.devise, code_dev_ref);

              -- Ecriture comptable
	      INSERT INTO ad_ecriture(id_his, date_comptable, libel_ecriture, id_jou, id_exo, ref_ecriture)
		VALUES ((SELECT currval(''ad_his_id_his_seq'')), date(date_prelev), type_ope.libel_ope, jou1,
		exo.id_exo_compta, makeNumEcriture(jou1, exo.id_exo_compta));

              -- Mouvement comptable au crédit du compte de liaison 
	      INSERT INTO ad_mouvement (id_ecriture, compte, cpte_interne_cli, sens, montant, devise, date_valeur)
		VALUES ((SELECT currval(''ad_ecriture_seq'')),cpte_liaison ,NULL, ''c'',cv_frais_tenue_cpte,
		code_dev_ref, date(date_prelev));

              -- Mouvement comptable au débit de la c/v de num_cpte_debit 
	      INSERT INTO ad_mouvement (id_ecriture, compte, cpte_interne_cli, sens, montant, devise, date_valeur)
		VALUES ((SELECT currval(''ad_ecriture_seq'')),cpt_cv_pos_ch ,NULL, ''d'',cv_frais_tenue_cpte,
		code_dev_ref, date(date_prelev));

              -- Mouvement comptable au débit de num_cpte_debit
	      INSERT INTO ad_mouvement (id_ecriture, compte, cpte_interne_cli, sens, montant, devise, date_valeur)
		VALUES ((SELECT currval(''ad_ecriture_seq'')),num_cpte_debit,ligne.id_cpte, ''d'',
		ligne.total_frais_tenue_cpt, ligne.devise, date(date_prelev));

              -- Mouvement comptable au crédit de la position de change de num_cpte_debit
	      INSERT INTO ad_mouvement (id_ecriture, compte, cpte_interne_cli, sens, montant, devise, date_valeur)
		VALUES ((SELECT currval(''ad_ecriture_seq'')),cpt_pos_ch,NULL, ''c'', ligne.total_frais_tenue_cpt,
		ligne.devise, date(date_prelev));

               -- Mise à jour des soldes comptables
	      UPDATE ad_cpt_comptable set solde = solde - ligne.total_frais_tenue_cpt WHERE num_cpte_comptable = num_cpte_debit;
	      UPDATE ad_cpt_comptable set solde = solde + ligne.total_frais_tenue_cpt WHERE num_cpte_comptable = cpt_pos_ch;
              UPDATE ad_cpt_comptable set solde = solde - cv_frais_tenue_cpte WHERE num_cpte_comptable = cpt_cv_pos_ch;
              UPDATE ad_cpt_comptable set solde = solde + cv_frais_tenue_cpte WHERE num_cpte_comptable = cpte_liaison;

           END IF; -- FIN IF devise_cpte_liaison = code_dev_ref

         -------- si ni cpte_liaison ni num_cpte_debit n''a la devise de référence  
           IF ligne.devise != code_dev_ref AND devise_cpte_liaison != code_dev_ref THEN
             
              -- prelevement des frais sur le compte du client
              UPDATE ad_cpt SET solde = solde - ligne.total_frais_tenue_cpt WHERE (id_cpte = ligne.id_cpte);

              -- Ecriture comptable
	      INSERT INTO ad_ecriture(id_his, date_comptable, libel_ecriture, id_jou, id_exo, ref_ecriture)
	VALUES ((SELECT currval(''ad_his_id_his_seq'')), date(date_prelev), type_ope.libel_ope, jou1, exo.id_exo_compta, makeNumEcriture(jou1, exo.id_exo_compta));

              -- Mouvement comptable au débit de num_cpte_debit
	      INSERT INTO ad_mouvement (id_ecriture, compte, cpte_interne_cli, sens, montant, devise, date_valeur)
	VALUES ((SELECT currval(''ad_ecriture_seq'')),num_cpte_debit,ligne.id_cpte, ''d'', ligne.total_frais_tenue_cpt, ligne.devise, date(date_prelev));

              UPDATE ad_cpt_comptable set solde = solde - ligne.total_frais_tenue_cpt WHERE num_cpte_comptable = num_cpte_debit;

              -- position de change de la devise de num_cpte_debit
              SELECT INTO cpt_pos_ch cpte_position_change || ''.'' || ligne.devise FROM ad_agc;

              -- Mouvement comptable au crédit de la position de change de num_cpte_debit
	      INSERT INTO ad_mouvement (id_ecriture, compte, cpte_interne_cli, sens, montant, devise, date_valeur)
	VALUES ((SELECT currval(''ad_ecriture_seq'')),cpt_pos_ch,NULL, ''c'', ligne.total_frais_tenue_cpt, ligne.devise, date(date_prelev));

               UPDATE ad_cpt_comptable set solde = solde + ligne.total_frais_tenue_cpt WHERE num_cpte_comptable = cpt_pos_ch;

              -- montant dans la devise de référence 
              SELECT INTO cv_frais_tenue_cpte CalculeCV(ligne.total_frais_tenue_cpt, ligne.devise, code_dev_ref);

              -- c/v de la devise de num_cpte_debit
              SELECT INTO cpt_cv_pos_ch cpte_contreval_position_change || ''.'' || ligne.devise FROM ad_agc;

              -- Mouvement comptable au débit de la c/v de num_cpte_debit
	      INSERT INTO ad_mouvement (id_ecriture, compte, cpte_interne_cli, sens, montant, devise, date_valeur)
	VALUES ((SELECT currval(''ad_ecriture_seq'')), cpt_cv_pos_ch,NULL, ''d'', cv_frais_tenue_cpte, code_dev_ref, date(date_prelev));

              UPDATE ad_cpt_comptable set solde = solde - cv_frais_tenue_cpte WHERE num_cpte_comptable = cpt_cv_pos_ch;

              -- c/v de la devise du compte de liaison
              SELECT INTO cpt_cv_pos_ch cpte_contreval_position_change || ''.'' || devise_cpte_liaison FROM ad_agc;

              -- Mouvement comptable au crédit de la c/v du compte de liaison
	      INSERT INTO ad_mouvement (id_ecriture, compte, cpte_interne_cli, sens, montant, devise, date_valeur)
	VALUES ((SELECT currval(''ad_ecriture_seq'')), cpt_cv_pos_ch, NULL, ''c'', cv_frais_tenue_cpte, code_dev_ref, date(date_prelev));

              UPDATE ad_cpt_comptable set solde = solde + cv_frais_tenue_cpte WHERE num_cpte_comptable = cpt_cv_pos_ch;

               -- montant dans la devise du compte de liaison 
              SELECT INTO cv_frais_tenue_cpte CalculeCV(ligne.total_frais_tenue_cpt, ligne.devise, devise_cpte_liaison);

               -- Mouvement comptable au crédit du compte de liaison
	      INSERT INTO ad_mouvement (id_ecriture, compte, cpte_interne_cli, sens, montant, devise, date_valeur)
	VALUES ((SELECT currval(''ad_ecriture_seq'')), cpte_liaison, NULL, ''c'', cv_frais_tenue_cpte, devise_cpte_liaison, date(date_prelev));

               UPDATE ad_cpt_comptable set solde = solde + cv_frais_tenue_cpte WHERE num_cpte_comptable = cpte_liaison;

               -- position de change de la devise du compte de liaison
              SELECT INTO cpt_pos_ch cpte_position_change || ''.'' || devise_cpte_liaison FROM ad_agc;

               -- Mouvement comptable au débit de la position de change de la devise du compte de liaison
	      INSERT INTO ad_mouvement (id_ecriture, compte, cpte_interne_cli, sens, montant, devise, date_valeur)
	VALUES ((SELECT currval(''ad_ecriture_seq'')), cpt_pos_ch, NULL, ''d'', cv_frais_tenue_cpte, devise_cpte_liaison, date(date_prelev));

               UPDATE ad_cpt_comptable set solde = solde - cv_frais_tenue_cpte WHERE num_cpte_comptable = cpt_pos_ch;

           END IF; -- FIN IF ligne.devise != code_dev_ref AND devise_cpte_liaison != code_dev_ref

      END IF;  -- FIN  IF ligne.devise = devise_cpte_liaison 

      ----------- FIN DEBIT COMPTE CLIENT PAR CREDIT COMPTE DE LIAISON -----------------------
       

      ----------- DEBIT COMPTE DE LIAISON PAR CREDIT COMPTE AU CREDIT DANS LE SECOND JOURNAL ------------------------
 
      IF devise_cpte_liaison = devise_cpte_cr THEN  ----- COMPTE AU CREDIT ET cpte_liaison SONT DE LA MEME DEVISE
              		
              -- MONTANT DANS LA DEVISE DU COMPTE DE LIASON 
              SELECT INTO cv_frais_tenue_cpte CalculeCV(ligne.total_frais_tenue_cpt, ligne.devise, devise_cpte_liaison);

              -- PASSAGE ECRITURE COMPTABLE
	      INSERT INTO ad_ecriture(id_his, date_comptable, libel_ecriture, id_jou, id_exo, ref_ecriture)
		VALUES ((SELECT currval(''ad_his_id_his_seq'')), date(date_prelev), type_ope.libel_ope, jou2,
		exo.id_exo_compta, makeNumEcriture(jou2, exo.id_exo_compta));

              -- MOUVEMENT COMPTABLE AU DEBIT DU COMPTE DE LIAISON 
	      INSERT INTO ad_mouvement (id_ecriture, compte, cpte_interne_cli, sens, montant, devise, date_valeur)
		VALUES ((SELECT currval(''ad_ecriture_seq'')),cpte_liaison,NULL,''d'',cv_frais_tenue_cpte,
		devise_cpte_liaison,date(date_prelev));

              UPDATE ad_cpt_comptable set solde = solde - cv_frais_tenue_cpte WHERE num_cpte_comptable = cpte_liaison;

	      -- MOUVEMENT COMPTABLE AU CREDIT DU COMPTE DE CREDIT
	      INSERT INTO ad_mouvement (id_ecriture, compte, cpte_interne_cli, sens, montant, devise, date_valeur)
		VALUES ((SELECT currval(''ad_ecriture_seq'')),type_ope.num_cpte,NULL,''c'',cv_frais_tenue_cpte,
		devise_cpte_cr,date(date_prelev));
		
              UPDATE ad_cpt_comptable set solde = solde + cv_frais_tenue_cpte WHERE num_cpte_comptable = type_ope.num_cpte;


    ELSE      ----- COMPTE AU CREDIT ET cpte_liaison N''ONT PAS LA MEME DEVISE , FAIRE DONC LA CONVERSION 
      
           IF devise_cpte_liaison = code_dev_ref THEN  -- CPTE DE LIAISON A LA DEVISE DE REFERENCE ET CPTE AU CREDIT DEVISE ETRANGERE  
             
              SELECT INTO cpt_pos_ch cpte_position_change || ''.'' || devise_cpte_cr FROM ad_agc;
              SELECT INTO cpt_cv_pos_ch cpte_contreval_position_change || ''.'' || devise_cpte_cr FROM ad_agc;

              -- MONTANT DANS LA DEVISE DU COMPTE DE LIASON (DEVISE DE REFERENCE ) 
              SELECT INTO cv_frais_tenue_cpte CalculeCV(ligne.total_frais_tenue_cpt, ligne.devise, devise_cpte_liaison);
             
              -- PASSAGE ECRITURE COMPTABLE
	      INSERT INTO ad_ecriture(id_his, date_comptable, libel_ecriture, id_jou, id_exo, ref_ecriture)
		VALUES ((SELECT currval(''ad_his_id_his_seq'')), date(date_prelev), type_ope.libel_ope, jou2,
		exo.id_exo_compta, makeNumEcriture(jou2, exo.id_exo_compta));

              -- MOUVEMENT AU DEBIT DU COMPTE DE LIAISON 
	      INSERT INTO ad_mouvement (id_ecriture, compte, cpte_interne_cli, sens, montant, devise, date_valeur)
		VALUES ((SELECT currval(''ad_ecriture_seq'')), cpte_liaison, NULL, ''d'', cv_frais_tenue_cpte,
		devise_cpte_liaison, date(date_prelev));

            	UPDATE ad_cpt_comptable set solde = solde - cv_frais_tenue_cpte WHERE num_cpte_comptable = cpte_liaison;

	      -- MOUVEMENT COMPTABLE AU CREDIT DE LA c/v DU COMPTE DE CREDIT DE L''OPERATION  
	      INSERT INTO ad_mouvement (id_ecriture, compte, cpte_interne_cli, sens, montant, devise, date_valeur)
		VALUES ((SELECT currval(''ad_ecriture_seq'')), cpt_cv_pos_ch, NULL, ''c'', cv_frais_tenue_cpte,
		code_dev_ref, date(date_prelev));

              UPDATE ad_cpt_comptable set solde = solde + cv_frais_tenue_cpte WHERE num_cpte_comptable = cpt_cv_pos_ch;


              -- MONTANT DANS LA DEVISE DU COMPTE DE CREDIT DE L''OPERATION 
              SELECT INTO cv_frais_tenue_cpte CalculeCV(ligne.total_frais_tenue_cpt, ligne.devise, devise_cpte_cr);

              -- MOUVEMENT COMPTABLE AU DEBIT DE LA POSITION DE CHANGE DU COMPTE AU CREDIT DE L''OPERATION 
	      INSERT INTO ad_mouvement (id_ecriture, compte, cpte_interne_cli, sens, montant, devise, date_valeur)
		VALUES ((SELECT currval(''ad_ecriture_seq'')),cpt_pos_ch, NULL, ''d'', cv_frais_tenue_cpte,
		devise_cpte_cr,date(date_prelev));

              UPDATE ad_cpt_comptable set solde = solde - cv_frais_tenue_cpte WHERE num_cpte_comptable = cpt_pos_ch;

              -- MOUVEMENT COMPTABLE AU CREDIT DU COMPTE DE CREDIT DE L''OPERATION 
	      INSERT INTO ad_mouvement (id_ecriture, compte, cpte_interne_cli, sens, montant, devise, date_valeur)
		VALUES ((SELECT currval(''ad_ecriture_seq'')), type_ope.num_cpte , NULL, ''c'',cv_frais_tenue_cpte,
		devise_cpte_cr, date(date_prelev));

	      UPDATE ad_cpt_comptable set solde = solde + cv_frais_tenue_cpte WHERE num_cpte_comptable = type_ope.num_cpte;
              

           END IF; -- FIN IF devise_cpte_liaison = code_dev_ref

           
           IF devise_cpte_cr = code_dev_ref THEN -- SI CPTE AU CREDIT A LA DEVISE DE REFERENCE ET CPTE LIAISON UNE DEVISE ETRANGERE 
             
              SELECT INTO cpt_pos_ch cpte_position_change || ''.'' || devise_cpte_liaison FROM ad_agc;
              SELECT INTO cpt_cv_pos_ch cpte_contreval_position_change || ''.'' || devise_cpte_liaison FROM ad_agc;
                         
              -- PASSAGE ECRITURE COMPTABLE
	      INSERT INTO ad_ecriture(id_his, date_comptable, libel_ecriture, id_jou, id_exo, ref_ecriture)
		VALUES ((SELECT currval(''ad_his_id_his_seq'')), date(date_prelev), type_ope.libel_ope, jou2,
		exo.id_exo_compta, makeNumEcriture(jou2, exo.id_exo_compta));

              -- MONATANT DANS LA DEVISE DU COMPTE AU CREDIT DE L''OPERATION ( DEVISE DE REFERENCE ) 
              SELECT INTO cv_frais_tenue_cpte CalculeCV(ligne.total_frais_tenue_cpt, ligne.devise, devise_cpte_cr);

              -- MOUVEMENT AU CREDIT DU COMPTE DE CREDIT DE L''OPERATION 
	      INSERT INTO ad_mouvement (id_ecriture, compte, cpte_interne_cli, sens, montant, devise, date_valeur)
		VALUES ((SELECT currval(''ad_ecriture_seq'')),type_ope.num_cpte ,NULL, ''c'',cv_frais_tenue_cpte,
		devise_cpte_cr, date(date_prelev));

              UPDATE ad_cpt_comptable set solde = solde + cv_frais_tenue_cpte WHERE num_cpte_comptable = type_ope.num_cpte;

              -- MOUVEMENT AU DEBIT DE LA c/v DU COMPTE DE LIAISON 
	      INSERT INTO ad_mouvement (id_ecriture, compte, cpte_interne_cli, sens, montant, devise, date_valeur)
		VALUES ((SELECT currval(''ad_ecriture_seq'')),cpt_cv_pos_ch ,NULL, ''d'',cv_frais_tenue_cpte,
		code_dev_ref, date(date_prelev));

              UPDATE ad_cpt_comptable set solde = solde - cv_frais_tenue_cpte WHERE num_cpte_comptable = cpt_cv_pos_ch;

              -- MONATANT DANS LA DEVISE DU COMPTE DE LIAISON 
              SELECT INTO cv_frais_tenue_cpte CalculeCV(ligne.total_frais_tenue_cpt, ligne.devise, devise_cpte_liaison);

              -- MOUVEMENT COMPTABLE AU DEBIT DU COMPTE DE LIAISON
	      INSERT INTO ad_mouvement (id_ecriture, compte, cpte_interne_cli, sens, montant, devise, date_valeur)
		VALUES ((SELECT currval(''ad_ecriture_seq'')), cpte_liaison, NULL, ''d'', cv_frais_tenue_cpte,
		devise_cpte_liaison, date(date_prelev));

               UPDATE ad_cpt_comptable set solde = solde - cv_frais_tenue_cpte WHERE num_cpte_comptable = cpte_liaison;
              
              -- MOUVEMENT COMPTABLE AU CREDIT DA LA POSITION DE CHANGE DE LA DEVISE DU COMPTE DE LIAISON
	      INSERT INTO ad_mouvement (id_ecriture, compte, cpte_interne_cli, sens, montant, devise, date_valeur)
		VALUES ((SELECT currval(''ad_ecriture_seq'')),cpt_pos_ch, NULL, ''c'', cv_frais_tenue_cpte,
		devise_cpte_liaison, date(date_prelev));
	      
	      UPDATE ad_cpt_comptable set solde = solde + cv_frais_tenue_cpte WHERE num_cpte_comptable = cpt_pos_ch;
                           
           END IF; -- FIN IF devise_cpte_cr = code_dev_ref

           IF devise_cpte_cr != code_dev_ref AND devise_cpte_liaison != code_dev_ref THEN
           
              -- DEVISE COMPTE DE LIAISON ET DEVISE COMPTE AU CREDIT SONT DIFFERENTES ET AUCUNE N''EST EGALE A LA DEVISE DE REFERENCE  
                           
              -- PASSAGE ECRITURE COMPTABLE DANS jou2
	      INSERT INTO ad_ecriture(id_his, date_comptable, libel_ecriture, id_jou, id_exo, ref_ecriture)
		VALUES ((SELECT currval(''ad_his_id_his_seq'')), date(date_prelev), type_ope.libel_ope, jou2,
		exo.id_exo_compta, makeNumEcriture(jou2, exo.id_exo_compta));

              -- MONTANT DANS LA DEVISE DU COMPTE DE LIAISON 
              SELECT INTO cv_frais_tenue_cpte CalculeCV(ligne.total_frais_tenue_cpt, ligne.devise, devise_cpte_liaison);

              -- MOUVEMENT COMPTABLE AU DEBIT DU COMPTE DE LIAISON
	      INSERT INTO ad_mouvement (id_ecriture, compte, cpte_interne_cli, sens, montant, devise, date_valeur)
		VALUES ((SELECT currval(''ad_ecriture_seq'')), cpte_liaison, NULL, ''d'', cv_frais_tenue_cpte,
		devise_cpte_liaison, date(date_prelev));

              UPDATE ad_cpt_comptable set solde = solde - cv_frais_tenue_cpte WHERE num_cpte_comptable = cpte_liaison;

              -- POSITION DE CHANGE DE LA DEVISE DU COMPTE DE LIAISON
              SELECT INTO cpt_pos_ch cpte_position_change || ''.'' || devise_cpte_liaison FROM ad_agc;

              -- MOUVEMENT COMPTABLE AU CREDIT DE LA POSITION DE CHANGE DE LA DEVISE DU COMPTE DE LIAISON
	      INSERT INTO ad_mouvement (id_ecriture, compte, cpte_interne_cli, sens, montant, devise, date_valeur)
		VALUES ((SELECT currval(''ad_ecriture_seq'')), cpt_pos_ch, NULL, ''c'', cv_frais_tenue_cpte,
		devise_cpte_liaison, date(date_prelev));

               UPDATE ad_cpt_comptable set solde = solde + cv_frais_tenue_cpte WHERE num_cpte_comptable = cpt_pos_ch;

              -- MONATNT DANS LA DEVISE DE REFERENCE 
              SELECT INTO cv_frais_tenue_cpte CalculeCV(ligne.total_frais_tenue_cpt, ligne.devise, code_dev_ref);

              -- c/v DE LA DEVISE DU COMPTE DE LIAISON
              SELECT INTO cpt_cv_pos_ch cpte_contreval_position_change || ''.'' || devise_cpte_liaison FROM ad_agc;

              -- MOUVEMENT COMPTABLE AU DEBIT DE LA c/v DE LA DEVISE DU COMPTE DE LIAISON
	      INSERT INTO ad_mouvement (id_ecriture, compte, cpte_interne_cli, sens, montant, devise, date_valeur)
	VALUES ((SELECT currval(''ad_ecriture_seq'')), cpt_cv_pos_ch,NULL, ''d'', cv_frais_tenue_cpte, code_dev_ref, date(date_prelev));

              UPDATE ad_cpt_comptable set solde = solde - cv_frais_tenue_cpte WHERE num_cpte_comptable = cpt_cv_pos_ch;

              -- c/v DE LA DEVISE DU COMPTE AU CREDIT DE L''OPERATION
              SELECT INTO cpt_cv_pos_ch cpte_contreval_position_change || ''.'' || devise_cpte_cr FROM ad_agc;

              -- MOUVEMENT COMPTABLE AU CREDIT DE LA c/v DU COMPTE AU CREDIT DE L''OPERATION
	      INSERT INTO ad_mouvement (id_ecriture, compte, cpte_interne_cli, sens, montant, devise, date_valeur)
		VALUES ((SELECT currval(''ad_ecriture_seq'')), cpt_cv_pos_ch, NULL, ''c'', cv_frais_tenue_cpte,
		code_dev_ref, date(date_prelev));

              UPDATE ad_cpt_comptable set solde = solde + cv_frais_tenue_cpte WHERE num_cpte_comptable = cpt_cv_pos_ch;

               -- MONTANT DANS LA DEVISE DU COMPTE DE CREDIT DE L''OPERATION 
              SELECT INTO cv_frais_tenue_cpte CalculeCV(ligne.total_frais_tenue_cpt, ligne.devise, devise_cpte_cr);

               -- MOUVEMENT COMPTABLE AU CREDIT DU COMPTE DE CREDIT D EL''OPERATION
	      INSERT INTO ad_mouvement (id_ecriture, compte, cpte_interne_cli, sens, montant, devise, date_valeur)
		VALUES ((SELECT currval(''ad_ecriture_seq'')), type_ope.num_cpte, NULL, ''c'', cv_frais_tenue_cpte,
		devise_cpte_cr, date(date_prelev));

               UPDATE ad_cpt_comptable set solde = solde + cv_frais_tenue_cpte WHERE num_cpte_comptable = type_ope.num_cpte;

               -- POSITION DE CHANGE DE LA DEVISE DU COMPTE AU CREDIT DE L''OPERATION
              SELECT INTO cpt_pos_ch cpte_position_change || ''.'' || devise_cpte_cr FROM ad_agc;

               -- MOUVEMENT COMPTABLE AU DEBIT DE LA POSITION DE CHANGE DE LA DEVISE DU COMPTE AU CREDIT D EL''OPERATION
	      INSERT INTO ad_mouvement (id_ecriture, compte, cpte_interne_cli, sens, montant, devise, date_valeur)
		VALUES ((SELECT currval(''ad_ecriture_seq'')), cpt_pos_ch, NULL, ''d'', cv_frais_tenue_cpte,
		devise_cpte_cr, date(date_prelev));

               UPDATE ad_cpt_comptable set solde = solde - cv_frais_tenue_cpte WHERE num_cpte_comptable = cpt_pos_ch;

           END IF; -- FIN IF devise_cpte_cr != code_dev_ref AND devise_cpte_liaison != code_dev_ref

      END IF;  -- FIN  IF devise_cpte_cr = devise_cpte_liaison 

      ---------- FIN DEBIT COMPTE DE LIAISON PAR CREDIT COMPTEG AU CREDIT 

      ELSE 

        -- AU MOINS UN DES COMPTES N''EST PAS PRINCIPAL OU LES DEUX SONT PRINCIPAUX DU MEME JOURNAL: PAS BESOIN DONC DE COMPTE DE LIAISON

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
           UPDATE ad_cpt SET solde = solde - ligne.total_frais_tenue_cpt WHERE (id_cpte = ligne.id_cpte);
		
	   -- Ecriture comptable
	   RAISE NOTICE ''Tentative ajout dans ad_ecriturei pour le compte  %'', ligne.id_cpte;
	   INSERT INTO ad_ecriture(id_his, date_comptable, libel_ecriture, id_jou, id_exo, ref_ecriture)
	VALUES ((SELECT currval(''ad_his_id_his_seq'')), date(date_prelev), type_ope.libel_ope, id_journal, exo.id_exo_compta, makeNumEcriture(id_journal, exo.id_exo_compta));

           RAISE NOTICE ''OK fait'';

	   -- Mouvement comptable au débit 
	   INSERT INTO ad_mouvement (id_ecriture, compte, cpte_interne_cli, sens, montant, devise, date_valeur)
	VALUES ((SELECT currval(''ad_ecriture_seq'')),num_cpte_debit,ligne.id_cpte, ''d'', ligne.total_frais_tenue_cpt, ligne.devise, date(date_prelev));

	   -- Mouvement comptable au crédit 
	   INSERT INTO ad_mouvement (id_ecriture, compte, cpte_interne_cli, sens, montant, devise, date_valeur)
	VALUES ((SELECT currval(''ad_ecriture_seq'')), type_ope.num_cpte, NULL, ''c'', ligne.total_frais_tenue_cpt,ligne.devise, date(date_prelev));
		
	   -- Mise à jour des soldes comptables
	   UPDATE ad_cpt_comptable set solde = solde - ligne.total_frais_tenue_cpt WHERE num_cpte_comptable = num_cpte_debit;
	   UPDATE ad_cpt_comptable set solde = solde + ligne.total_frais_tenue_cpt WHERE num_cpte_comptable = type_ope.num_cpte;


        ELSE  -- La devise du compte n''est pas la devise de référence, il faut mouvementer la position de change

	  SELECT INTO cpt_pos_ch cpte_position_change || ''.'' || ligne.devise FROM ad_agc;
          SELECT INTO cpt_cv_pos_ch cpte_contreval_position_change || ''.'' || ligne.devise FROM ad_agc;

          RAISE NOTICE ''cpt_pos_ch = % et cpt_cv_pos_ch = %'',cpt_pos_ch, cpt_cv_pos_ch;

	  -- prelevement des frais sur le compte du client
	  UPDATE ad_cpt SET solde = solde - ligne.total_frais_tenue_cpt WHERE (id_cpte = ligne.id_cpte);

	  -- Ecriture comptable
	  INSERT INTO ad_ecriture(id_his, date_comptable, libel_ecriture, id_jou, id_exo, ref_ecriture)
	VALUES ((SELECT currval(''ad_his_id_his_seq'')), date(date_prelev), type_ope.libel_ope, id_journal, exo.id_exo_compta, makeNumEcriture(id_journal, exo.id_exo_compta));

	  -- Mouvement comptable au débit 
	  INSERT INTO ad_mouvement (id_ecriture, compte, cpte_interne_cli, sens, montant, devise, date_valeur)
	VALUES ((SELECT currval(''ad_ecriture_seq'')),num_cpte_debit,ligne.id_cpte, ''d'', ligne.total_frais_tenue_cpt,	ligne.devise, date(date_prelev));

	  INSERT INTO ad_mouvement (id_ecriture, compte, cpte_interne_cli, sens, montant, date_valeur, devise)
	VALUES ((SELECT currval(''ad_ecriture_seq'')), cpt_pos_ch, NULL, ''c'', ligne.total_frais_tenue_cpt, date(date_prelev), ligne.devise);

	  -- Mise à jour des soldes des comptes comptables	
	  UPDATE ad_cpt_comptable set solde = solde - ligne.total_frais_tenue_cpt
	WHERE num_cpte_comptable = num_cpte_debit;

	  UPDATE ad_cpt_comptable set solde = solde + ligne.total_frais_tenue_cpt
	WHERE num_cpte_comptable = cpt_pos_ch;

	  SELECT INTO cv_frais_tenue_cpte CalculeCV(ligne.total_frais_tenue_cpt, ligne.devise, code_dev_ref);

	  -- Ecriture comptable
	  INSERT INTO ad_ecriture(id_his, date_comptable, libel_ecriture, id_jou, id_exo, ref_ecriture)
	VALUES ((SELECT currval(''ad_his_id_his_seq'')), date(date_prelev),type_ope.libel_ope, id_journal, exo.id_exo_compta, makeNumEcriture(id_journal, exo.id_exo_compta));

	  INSERT INTO ad_mouvement (id_ecriture, compte, cpte_interne_cli, sens, montant, date_valeur, devise)
	VALUES ((SELECT currval(''ad_ecriture_seq'')), cpt_cv_pos_ch, NULL, ''d'', cv_frais_tenue_cpte, date(date_prelev), code_dev_ref);

	  -- mouvement comptable au crédit 
	  INSERT INTO ad_mouvement (id_ecriture, compte, cpte_interne_cli, sens, montant, devise, date_valeur)
	VALUES ((SELECT currval(''ad_ecriture_seq'')), type_ope.num_cpte, NULL, ''c'', cv_frais_tenue_cpte, code_dev_ref, date(date_prelev));

	  -- mise à jour des soldes comptables
	  UPDATE ad_cpt_comptable set solde = solde - cv_frais_tenue_cpte WHERE num_cpte_comptable = cpt_cv_pos_ch;
	  UPDATE ad_cpt_comptable set solde = solde + cv_frais_tenue_cpte WHERE num_cpte_comptable = type_ope.num_cpte;

        END IF; -- Fin vérification des devises

      END IF; -- Fin recherche compte de liaison 

             
      -- construction des données à renvoyer
      SELECT INTO compte_frais ligne.num_complet_cpte, ligne.devise, ligne.id_titulaire, ligne.solde, ligne.total_frais_tenue_cpt;
      RETURN NEXT compte_frais;


    ELSE
      
      --Mise en attente
      INSERT INTO ad_frais_attente (id_cpte, date_frais, type_frais, montant) 
      VALUES (ligne.id_cpte , date(date_prelev), num_ope, ligne.total_frais_tenue_cpt);
				
    END IF;

    FETCH Cpt_Prelev INTO ligne;

  END LOOP;

  CLOSE Cpt_Prelev;


  RETURN;

END; 
' LANGUAGE 'plpgsql';


