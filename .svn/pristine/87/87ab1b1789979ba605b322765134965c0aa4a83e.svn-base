
-- Fonction appelée par Trigger supprimant toutes les traductions d'un string donné
CREATE OR REPLACE FUNCTION trig_del_dans_ad_traductions() RETURNS trigger AS '
    BEGIN
        DELETE FROM ad_traductions where id_str = OLD.id_str;
        RETURN OLD;
    END;
' LANGUAGE plpgsql;

CREATE OR REPLACE FUNCTION trig_suppr_trad() RETURNS trigger
    AS 'triggers.so' LANGUAGE C;

/*
  Fonction appelée après chaque modifiant d'une entrée de la table ad_cpt
  Elle paie les frais en attente d'un compte si le solde de ce dernier devient suffisant.  
*/

CREATE OR REPLACE FUNCTION trig_preleve_frais_attente() RETURNS trigger AS '
  DECLARE
    
	id_cpte_mod INTEGER;             -- numéro du compte dont le solde a été modifié
	code_dev_ref CHAR(3);	     -- Code de la devise de référence
	devise_cpte_int CHAR(3);     -- devise du compte interne dont le solde a été modifié 
	devise_cpte_debit CHAR(3);   -- devise du compte comptable directement associé au produit (= null ou devise du compte interne)
	devise_cpte_liaison CHAR(3); -- devise d''un éventuel compte de liaison 
	devise_cpte_credit CHAR(3);  -- devise du compte au crédit de l''opération 
	solde_courant NUMERIC(30,6); -- solde courant du compte (solde après modification) 
	solde_dispo NUMERIC(30,6);   -- solde diponible du compte
	montant_min NUMERIC(30,6);   -- montant minimun du produit d''épargne
	type_ope INTEGER ;           -- le numéro de l''opération associée au type de frais
	libelle_ope TEXT;            -- le libellé de l''opération associée au type de frais
	cpte_credit_ope TEXT;        -- le compte au crédit de l''opération associée au type de frais
	cpte_cpta_prod TEXT;         -- le compte associé au produit
	cpte_liaison TEXT;           -- éventuel compte de liaison
	jou_debit INTEGER;	     -- id du journal associé au compte au débit s''il est principal
	jou_credit INTEGER;          -- id du journal associé au compte au crédit s''il est principal
	journal INTEGER;             -- id du journal si les deux comptes ne sont pas principaux
	exo INTEGER;                 -- le numéro de l''exercie
	cv_montant NUMERIC(30,6);    -- c/v des frais
	cpt_pos_ch TEXT;		       -- Compte de position de change de la devise du compte traité
        cpt_cv_pos_ch TEXT;		       -- Compte de C/V de la Pos de Ch de la devise du compte traité

	-- RECUPERATION DES FRAIS EN ATTENTE POUR LE COMPTE
	frais_attente CURSOR FOR 
		SELECT * FROM ad_frais_attente WHERE id_cpte = NEW.id_cpte;

	ligne RECORD;   -- un enregistrement de la table ad_frais_attente

  BEGIN

	id_cpte_mod := NEW.id_cpte;	

	OPEN frais_attente;
  	FETCH frais_attente INTO ligne;

  	-- SI CE COMPTE A DES FRAIS EN ATTENTE DANS ad_frais_attente
	IF FOUND THEN
		solde_courant := NEW.solde;
		devise_cpte_int := NEW.devise;

		SELECT INTO cpte_cpta_prod,montant_min  cpte_cpta_prod_ep,mnt_min FROM adsys_produit_epargne
		 WHERE id = NEW.id_prod;

		-- RECUPERATION DE LA DEVISE DU COMPTE COMPTABLE ASSOCIE AU PRODUIT      
		SELECT INTO devise_cpte_debit devise FROM ad_cpt_comptable WHERE num_cpte_comptable = cpte_cpta_prod;

		-- CONSTRUCTGION DU NUMERO DU COMPTE A DEBITER  
		IF devise_cpte_debit IS NULL THEN
			cpte_cpta_prod := cpte_cpta_prod || ''.'' || devise_cpte_int;
			devise_cpte_debit := devise_cpte_int;
		END IF;
		RAISE NOTICE ''compte à débiter %'', cpte_cpta_prod;

		-- RECUPERATION DU JOURNAL ASSOCIE SI COMPTE AU DEBITER EST PRINCIPAL
			SELECT INTO jou_debit recupeJournal(cpte_cpta_prod);
		
		-- SOLDE DISPONIBLE DU COMPTE INTERNE
		solde_dispo := NEW.solde - NEW.mnt_bloq - montant_min;

		-- RECUPERATION DE LA DEVISE DE REFERENCE 
  		SELECT INTO code_dev_ref code_devise_reference FROM ad_agc;

	END IF;    

	-- PARCOURS DES ENTREES DE COMPTE DANS LA TABLE ad_frais_attente
	WHILE FOUND LOOP
		IF solde_dispo >= ligne.montant THEN

			-- Recherche du numéro de l''exercice 
			SELECT INTO exo id_exo_compta FROM ad_exercices_compta
		 	WHERE date_deb_exo<= ligne.date_frais AND date_fin_exo >= ligne.date_frais;

			-- RECUPERATION DU NUMERO DE L''OPERATION DE PRELEVEMENT DES FRAIS
			type_ope = ligne.type_frais;
			
			-- RECUPRATION DU LIBELLE ET DU COMPTE AU CREDIT DE L''OPERATION DE PRELEVEMENT DES FRAIS
			SELECT INTO libelle_ope,cpte_credit_ope libel_ope,num_cpte FROM ad_cpt_ope a, ad_cpt_ope_cptes b
			 WHERE a.type_operation = type_ope AND a.type_operation=b.type_operation AND b.sens = ''c'';  

			-- RECUPERATION DU JOURNAL ASSOCIE SI COMPTE AU CREDIT DE L''OPERATION EST PRINCIPAL
			SELECT INTO jou_credit recupeJournal(cpte_credit_ope);			

			-- PRELEVEMENT DES FRAIS SUR LE COMPOTE DU CLIENT
			UPDATE ad_cpt SET solde = solde - ligne.montant WHERE (id_cpte = ligne.id_cpte);
				
			IF jou_debit IS NOT NULL AND jou_credit IS NOT NULL AND jou_debit != jou_credit THEN

				-- COMPTES AU DEBIT ET AU CREDIT SONT PRINCIPAUX DE JOURNAUX DIFFERENTS, RECUPERER LE COMPTE DE LIAISON
				SELECT INTO cpte_liaison num_cpte_comptable FROM ad_journaux_liaison
				 WHERE (id_jou1=jou_debit AND id_jou2=jou_credit) OR (id_jou1=jou_credit AND id_jou2=jou_debit);
				RAISE NOTICE ''Compte de liason entre les journaux % et % est %'', jou_debit, jou_credit, cpte_liaison;

			END IF;

			IF cpte_liaison IS NOT NULL THEN

				-- RECUPERATION DE LA DEVISE DU COMPTE DE LIAISON      
				SELECT INTO devise_cpte_liaison devise FROM ad_cpt_comptable WHERE num_cpte_comptable = cpte_liaison;

				-- ECRITURE COMPTA : DEBIT COMPPTE ASSOCIE PRODUIT/CREDIT CPTE DE LIAISON
				INSERT INTO ad_ecriture(id_his, date_comptable, libel_ecriture, id_jou, id_exo, ref_ecriture)
				 VALUES ((SELECT currval(''ad_his_id_his_seq'')), ligne.date_frais, libelle_ope, jou_debit,
				 exo, makeNumEcriture(jou_debit, exo));

				-- DEBIT DU COMPTE ASSOCIE AU PRODUIT
				INSERT INTO ad_mouvement (id_ecriture, compte, cpte_interne_cli, sens, montant, devise, date_valeur)
			 	VALUES ((SELECT currval(''ad_ecriture_seq'')),cpte_cpta_prod,id_cpte_mod, ''d'', ligne.montant,
			 	devise_cpte_debit, ligne.date_frais);
				UPDATE ad_cpt_comptable set solde = solde - ligne.montant
			 	WHERE num_cpte_comptable = cpte_cpta_prod;

				IF devise_cpte_debit != code_dev_ref THEN					
					SELECT INTO cpt_pos_ch cpte_position_change || ''.'' || devise_cpte_debit FROM ad_agc;
					SELECT INTO cpt_cv_pos_ch cpte_contreval_position_change || ''.'' || devise_cpte_debit FROM ad_agc;

					INSERT INTO ad_mouvement (id_ecriture,compte,cpte_interne_cli,sens, montant, devise, date_valeur)
			 		VALUES ((SELECT currval(''ad_ecriture_seq'')),cpt_pos_ch,NULL, ''c'', ligne.montant,
			 		devise_cpte_debit, ligne.date_frais);
					UPDATE ad_cpt_comptable set solde = solde + ligne.montant
				 	WHERE num_cpte_comptable = cpt_pos_ch;

					SELECT INTO cv_montant CalculeCV(ligne.montant, devise_cpte_debit, code_dev_ref);

					INSERT INTO ad_mouvement (id_ecriture,compte,cpte_interne_cli,sens, montant, devise, date_valeur)
			 		VALUES ((SELECT currval(''ad_ecriture_seq'')), cpt_cv_pos_ch, NULL, ''d'', cv_montant,
			 		code_dev_ref, ligne.date_frais);
					UPDATE ad_cpt_comptable set solde = solde - cv_montant
				 	WHERE num_cpte_comptable = cpt_cv_pos_ch;

				END IF;

				-- CREDIT COMPTE DE LIASON DANS SA DEVISE
				SELECT INTO cv_montant CalculeCV(ligne.montant, devise_cpte_debit, devise_cpte_liaison);

				INSERT INTO ad_mouvement (id_ecriture,compte,cpte_interne_cli,sens, montant, devise, date_valeur)
			 	VALUES ((SELECT currval(''ad_ecriture_seq'')), cpte_liaison, NULL, ''c'', cv_montant,
			 	devise_cpte_liaison, ligne.date_frais);
				UPDATE ad_cpt_comptable set solde = solde + cv_montant
				WHERE num_cpte_comptable = cpte_liaison;

				IF devise_cpte_liaison != code_dev_ref THEN
					SELECT INTO cpt_pos_ch cpte_position_change || ''.'' || devise_cpte_liaison FROM ad_agc;
					SELECT INTO cpt_cv_pos_ch cpte_contreval_position_change || ''.'' || devise_cpte_liaison FROM ad_agc;
					INSERT INTO ad_mouvement (id_ecriture,compte,cpte_interne_cli,sens, montant, devise, date_valeur)
			 		VALUES ((SELECT currval(''ad_ecriture_seq'')),cpt_pos_ch,NULL, ''d'', cv_montant,
			 		devise_cpte_liaison, ligne.date_frais);
					UPDATE ad_cpt_comptable set solde = solde - cv_montant
				 	WHERE num_cpte_comptable = cpt_pos_ch;

					SELECT INTO cv_montant CalculeCV(ligne.montant, devise_cpte_debit, code_dev_ref);

					INSERT INTO ad_mouvement (id_ecriture,compte,cpte_interne_cli,sens, montant, devise, date_valeur)
			 		VALUES ((SELECT currval(''ad_ecriture_seq'')), cpt_cv_pos_ch, NULL, ''c'', cv_montant,
			 		code_dev_ref, ligne.date_frais);
					UPDATE ad_cpt_comptable set solde = solde + cv_montant
				 	WHERE num_cpte_comptable = cpt_cv_pos_ch;

				END IF; 
		
		
				-- ECRITURE COMPTA : DEBIT COMPPTE DE LIAISON/CREDIT CPTE DE L''OPERATION
				INSERT INTO ad_ecriture(id_his, date_comptable, libel_ecriture, id_jou, id_exo, ref_ecriture)
				 VALUES ((SELECT currval(''ad_his_id_his_seq'')), ligne.date_frais, libelle_ope, jou_credit,
				 exo, makeNumEcriture(jou_credit, exo));

				-- DEBIT COMPTE DE LIAISON
				SELECT INTO cv_montant CalculeCV(ligne.montant, devise_cpte_debit, devise_cpte_liaison);

				INSERT INTO ad_mouvement (id_ecriture,compte,cpte_interne_cli,sens, montant, devise, date_valeur)
			 	VALUES ((SELECT currval(''ad_ecriture_seq'')), cpte_liaison, NULL, ''d'', cv_montant,
			 	devise_cpte_liaison, ligne.date_frais);
				UPDATE ad_cpt_comptable set solde = solde - cv_montant
				WHERE num_cpte_comptable = cpte_liaison;
				
				IF devise_cpte_liaison != code_dev_ref THEN
					SELECT INTO cpt_pos_ch cpte_position_change || ''.'' || devise_cpte_liaison FROM ad_agc;
					SELECT INTO cpt_cv_pos_ch cpte_contreval_position_change || ''.'' || devise_cpte_liaison FROM ad_agc;
					INSERT INTO ad_mouvement (id_ecriture,compte,cpte_interne_cli,sens, montant, devise, date_valeur)
			 		VALUES ((SELECT currval(''ad_ecriture_seq'')),cpt_pos_ch,NULL, ''c'', cv_montant,
			 		devise_cpte_liaison, ligne.date_frais);
					UPDATE ad_cpt_comptable set solde = solde + cv_montant
				 	WHERE num_cpte_comptable = cpt_pos_ch;

					SELECT INTO cv_montant CalculeCV(ligne.montant, devise_cpte_debit, code_dev_ref);

					INSERT INTO ad_mouvement (id_ecriture,compte,cpte_interne_cli,sens, montant, devise, date_valeur)
			 		VALUES ((SELECT currval(''ad_ecriture_seq'')), cpt_cv_pos_ch, NULL, ''d'', cv_montant,
			 		code_dev_ref, ligne.date_frais);
					UPDATE ad_cpt_comptable set solde = solde - cv_montant
				 	WHERE num_cpte_comptable = cpt_cv_pos_ch;

				END IF; 

				-- CREDIT DU COMPTE DE AU CREDIT DE L''OPERATION:ON SUPPOSE QUE LE COMPTE EST DANS LA DEVISE DE REFERENCE
				SELECT INTO cv_montant CalculeCV(ligne.montant, devise_cpte_debit, code_dev_ref);
				
				INSERT INTO ad_mouvement (id_ecriture,compte,cpte_interne_cli,sens, montant, devise, date_valeur)
			 	VALUES ((SELECT currval(''ad_ecriture_seq'')), cpte_credit_ope, NULL, ''c'', cv_montant,
			 	code_dev_ref, ligne.date_frais);
				UPDATE ad_cpt_comptable set solde = solde + cv_montant
				WHERE num_cpte_comptable = cpte_credit_ope;

			ELSE -- S''IL N Y A PAS DE COMPTE DE LIAISON
				-- SOIT UN OU LES DEUX COMPTES NE SONT PAS PRINCIPAUX, SOIT ILS SONT PRINCIPAUX DU MEME JOURNAL
				-- SOIT LES COMPTES SONT PRINCIPAUX ET UN COMPTE DE LIAISON N''EST PAS PARAMETRE ENTRE LES DEUX JOURNAUX 
				
				-- DETERMINATION DU JOURNAL DEVANT CONTENIR L''ECRITURE			
				IF jou_debit IS NOT NULL AND jou_credit IS NOT NULL AND jou_debit != jou_credit THEN
					journal := NULL; -- LE COMPTE DE LIAISON N''EST PAS PARAMETRE ENTRE LES DEUX JOURNAUX
				END IF;

				IF jou_debit IS NOT NULL AND jou_credit IS NOT NULL AND jou_debit = jou_credit THEN
					journal := jou_debit; -- COMPTES PRINCIPAUX DE MEME JOURNAL
				END IF;

				IF jou_debit IS NOT NULL AND jou_credit IS NULL THEN
					journal := jou_debit;				
				END IF;

				IF jou_debit IS NULL AND jou_credit IS NOT NULL THEN
					journal := jou_credit;				
				END IF;
				
				IF jou_debit IS NULL AND jou_credit IS NULL THEN  -- PAS DE JOURNAL PRINCIPAL, PRENDRE JOURNAL PAR DEFAUT
					journal := 1;				
				END IF;


				-- ECRITURE COMPTA : DEBIT COMPTE DE ASSOCIE AU PRODUIT / CREDIT COMPTE DE L''OPERATION
				INSERT INTO ad_ecriture(id_his, date_comptable, libel_ecriture, id_jou, id_exo, ref_ecriture)
				 VALUES ((SELECT currval(''ad_his_id_his_seq'')), ligne.date_frais, libelle_ope, journal,
				 exo, makeNumEcriture(journal, exo));

				-- DEBIT COMPTE ASSOCIE AU PRODUIT D''EPARGNE
				INSERT INTO ad_mouvement (id_ecriture, compte, cpte_interne_cli, sens, montant, devise, date_valeur)
			 	VALUES ((SELECT currval(''ad_ecriture_seq'')),cpte_cpta_prod,id_cpte_mod, ''d'', ligne.montant,
			 	devise_cpte_debit, ligne.date_frais);
				UPDATE ad_cpt_comptable set solde = solde - ligne.montant
			 	WHERE num_cpte_comptable = cpte_cpta_prod;
								
				IF devise_cpte_debit != code_dev_ref THEN 
					SELECT INTO cpt_pos_ch cpte_position_change || ''.'' || devise_cpte_debit FROM ad_agc;
          				SELECT INTO cpt_cv_pos_ch cpte_contreval_position_change || ''.'' || devise_cpte_debit FROM ad_agc;

					INSERT INTO ad_mouvement (id_ecriture,compte,cpte_interne_cli,sens,montant, devise,date_valeur)
			 		VALUES ((SELECT currval(''ad_ecriture_seq'')), cpt_pos_ch, NULL, ''c'', ligne.montant,
			 		devise_cpte_debit, ligne.date_frais);
					UPDATE ad_cpt_comptable set solde = solde + ligne.montant
				 	WHERE num_cpte_comptable = cpt_pos_ch;

					SELECT INTO cv_montant CalculeCV(ligne.montant, devise_cpte_debit, code_dev_ref);

					INSERT INTO ad_mouvement (id_ecriture,compte,cpte_interne_cli,sens,montant,devise,date_valeur)
				 	VALUES ((SELECT currval(''ad_ecriture_seq'')), cpt_cv_pos_ch, NULL, ''d'', cv_montant,
			 		code_dev_ref, ligne.date_frais);
					UPDATE ad_cpt_comptable set solde = solde - cv_montant
				 	WHERE num_cpte_comptable = cpt_cv_pos_ch;

				END IF;
	

				-- MOUVEMENT AU CREDIT
				SELECT INTO cv_montant CalculeCV(ligne.montant, devise_cpte_debit, code_dev_ref);

				INSERT INTO ad_mouvement (id_ecriture, compte, cpte_interne_cli, sens, montant, devise, date_valeur)
			 	VALUES ((SELECT currval(''ad_ecriture_seq'')), cpte_credit_ope, NULL, ''c'', cv_montant,
			 	code_dev_ref, ligne.date_frais);
				UPDATE ad_cpt_comptable set solde = solde + cv_montant
			 	WHERE num_cpte_comptable = cpte_credit_ope;
				
			END IF; -- FIN DE IF cpte_liaison IS NOT NULL THEN
			
								
			solde_dispo := solde_dispo - ligne.montant;

			-- SUPPRESSION DE L''ATTENETE
			DELETE FROM ad_frais_attente WHERE id_cpte = ligne.id_cpte AND date(date_frais) = date(ligne.date_frais)
			 AND type_frais = ligne.type_frais;

		END IF; -- FIN IF solde_dispo >= ligne.montant 

		-- SE DEPLACER A L''ENTREE SUIVANTE
		FETCH frais_attente INTO ligne;

	END LOOP;

	CLOSE frais_attente;
    
    RETURN NEW;

  END;
' LANGUAGE 'plpgsql';
