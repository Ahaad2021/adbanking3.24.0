------------------------------- DEBUT : Ticket #356 : calcul des intérêts recevoir : Ecrans / Parametrage --------------------------------

CREATE OR REPLACE FUNCTION patch_ticket_356_II() RETURNS INT AS
$$
DECLARE
output_result INTEGER = 1;
tableliste_ident INTEGER = 0;
----DEBUT ticket pp 259 : SH
column_id_ag text;
column_num_cpte_comptable text;
v_table_name text;
constraint_name text;
table_exist text;
count integer;
chk_pkey CURSOR FOR SELECT distinct(tc.constraint_name),tc.table_name,
		min(case when ccu.column_name = 'id_ag' then kcu.column_name else null end) as f_column_name_1,
		max(case when ccu.column_name = 'num_cpte_comptable' then kcu.column_name else null end) as f_column_name_2    
		FROM information_schema.table_constraints AS tc 
		INNER JOIN information_schema.key_column_usage AS kcu
		ON tc.constraint_name = kcu.constraint_name
		INNER JOIN information_schema.constraint_column_usage AS ccu
		ON ccu.constraint_name = tc.constraint_name
		WHERE tc.constraint_type = 'PRIMARY KEY' AND ccu.table_name='ad_cpt_comptable' AND ccu.table_schema='public'
		group by tc.constraint_name, tc.table_name
		order by tc.table_name asc, tc.constraint_name asc;
ligne_chk RECORD;
----
BEGIN
	----ticket pp 259 : SH
	count := 0;
	table_exist := '';
	FOR ligne_chk IN chk_pkey LOOP
		count := count + 1;
	END LOOP;
	IF (count < 1) THEN
		--RAISE NOTICE 'ALTER TABLE ad_cpt_comptable ADD CONSTRAINT ad_cpt_comptable_pkey PRIMARY KEY (num_cpte_comptable,id_ag);';
		ALTER TABLE ad_cpt_comptable ADD CONSTRAINT ad_cpt_comptable_pkey PRIMARY KEY (num_cpte_comptable,id_ag);
		RAISE NOTICE 'Primary Key Created for ad_cpt_comptable';
		-- Create table parametrage adsys_calc_int_paye
		IF NOT EXISTS(SELECT * FROM information_schema.tables WHERE table_name = 'adsys_calc_int_recevoir') THEN
		--SELECT INTO table_exist t.table_name FROM information_schema.tables t WHERE t.table_name = 'adsys_calc_int_recevoir';
		--IF (table_exist is null) THEN
			CREATE TABLE adsys_calc_int_recevoir
			(
			  id_ag integer NOT NULL,
			  freq_calc_int_recevoir integer, -- La fréquence de calcul des intérêts à recevoir
			  cpte_cpta_int_recevoir text, -- Le scompte comptable des intérêts à recevoir
			  CONSTRAINT adsys_calc_int_recevoir_pkey PRIMARY KEY (id_ag),
			  CONSTRAINT fk_adsys_calc_int_recevoir_id_ag FOREIGN KEY (id_ag) REFERENCES ad_agc (id_ag) MATCH SIMPLE
			  ON UPDATE NO ACTION ON DELETE NO ACTION,
			  CONSTRAINT adsys_calc_int_recevoir_cpte_cpta_int_recevoir_fkey FOREIGN KEY (cpte_cpta_int_recevoir, id_ag)
			  REFERENCES ad_cpt_comptable (num_cpte_comptable, id_ag) MATCH SIMPLE
			  ON UPDATE NO ACTION ON DELETE NO ACTION
			)
			WITH (
			  OIDS=FALSE
			);

			-- Insert default value for id_ag
			INSERT INTO adsys_calc_int_recevoir (id_ag) VALUES (numagc());

			RAISE NOTICE 'Table adsys_calc_int_recevoir created';
			output_result := 2;
				
		END IF;			
	END IF;						
		
		
	SELECT INTO constraint_name, v_table_name, column_id_ag, column_num_cpte_comptable distinct(tc.constraint_name), tc.table_name,
	min(case when ccu.column_name = 'id_ag' then kcu.column_name else null end) as f_column_name_1,
	max(case when ccu.column_name = 'num_cpte_comptable' then kcu.column_name else null end) as f_column_name_2		    
	FROM information_schema.table_constraints AS tc 
	INNER JOIN information_schema.key_column_usage AS kcu
	ON tc.constraint_name = kcu.constraint_name
	INNER JOIN information_schema.constraint_column_usage AS ccu
	ON ccu.constraint_name = tc.constraint_name
	WHERE tc.constraint_type = 'PRIMARY KEY' AND ccu.table_name='ad_cpt_comptable' AND ccu.table_schema='public'
	group by tc.constraint_name, tc.table_name
	order by tc.table_name asc, tc.constraint_name asc;	
	
	IF ((column_id_ag is null) or (column_num_cpte_comptable is null)) THEN
		-- Create table parametrage adsys_calc_int_paye
		IF NOT EXISTS(SELECT * FROM information_schema.tables WHERE table_name = 'adsys_calc_int_recevoir') THEN
		--SELECT INTO table_exist t.table_name FROM information_schema.tables t WHERE t.table_name = 'adsys_calc_int_recevoir';
		--IF (table_exist is null) THEN
			CREATE TABLE adsys_calc_int_recevoir
			(
			  id_ag integer NOT NULL,
			  freq_calc_int_recevoir integer, -- La fréquence de calcul des intérêts à recevoir
			  cpte_cpta_int_recevoir text, -- Le scompte comptable des intérêts à recevoir
			  CONSTRAINT adsys_calc_int_recevoir_pkey PRIMARY KEY (id_ag),
			  CONSTRAINT fk_adsys_calc_int_recevoir_id_ag FOREIGN KEY (id_ag) REFERENCES ad_agc (id_ag) MATCH SIMPLE
			  ON UPDATE NO ACTION ON DELETE NO ACTION,
			  CONSTRAINT adsys_calc_int_recevoir_cpte_cpta_int_recevoir_fkey FOREIGN KEY (cpte_cpta_int_recevoir)
			  REFERENCES ad_cpt_comptable (num_cpte_comptable) MATCH SIMPLE
			  ON UPDATE NO ACTION ON DELETE NO ACTION
			)
			WITH (
			  OIDS=FALSE
			);

			-- Insert default value for id_ag
			INSERT INTO adsys_calc_int_recevoir (id_ag) VALUES (numagc());

			RAISE NOTICE 'Table adsys_calc_int_recevoir created';
			output_result := 2;
				
		END IF;
	END IF;
	IF ((column_id_ag is not null) and (column_num_cpte_comptable is not null)) THEN
		-- Create table parametrage adsys_calc_int_paye
		IF NOT EXISTS(SELECT * FROM information_schema.tables WHERE table_name = 'adsys_calc_int_recevoir') THEN
		--SELECT INTO table_exist t.table_name FROM information_schema.tables t WHERE t.table_name = 'adsys_calc_int_recevoir';
		--IF (table_exist is null) THEN
			CREATE TABLE adsys_calc_int_recevoir
			(
			  id_ag integer NOT NULL,
			  freq_calc_int_recevoir integer, -- La fréquence de calcul des intérêts à recevoir
			  cpte_cpta_int_recevoir text, -- Le scompte comptable des intérêts à recevoir
			  CONSTRAINT adsys_calc_int_recevoir_pkey PRIMARY KEY (id_ag),
			  CONSTRAINT fk_adsys_calc_int_recevoir_id_ag FOREIGN KEY (id_ag) REFERENCES ad_agc (id_ag) MATCH SIMPLE
			  ON UPDATE NO ACTION ON DELETE NO ACTION,
			  CONSTRAINT adsys_calc_int_recevoir_cpte_cpta_int_recevoir_fkey FOREIGN KEY (cpte_cpta_int_recevoir, id_ag)
			  REFERENCES ad_cpt_comptable (num_cpte_comptable, id_ag) MATCH SIMPLE
			  ON UPDATE NO ACTION ON DELETE NO ACTION
			)
			WITH (
			  OIDS=FALSE
			);

			-- Insert default value for id_ag
			INSERT INTO adsys_calc_int_recevoir (id_ag) VALUES (numagc());

			RAISE NOTICE 'Table adsys_calc_int_recevoir created';
			output_result := 2;
				
		END IF;
	END IF;	
	---- END ticket pp 259 : SH

	RAISE NOTICE 'START UPDATE : Trac#356 : Mise a jour calcul des intérêts à recevoir :';

  /*-- Create table parametrage adsys_calc_int_recevoir
  IF NOT EXISTS(SELECT * FROM information_schema.tables WHERE table_name = 'adsys_calc_int_recevoir') THEN
    CREATE TABLE adsys_calc_int_recevoir
    (
      id_ag integer NOT NULL,
      freq_calc_int_recevoir integer, -- La fréquence de calcul des intérêts à recevoir
      cpte_cpta_int_recevoir text, -- Le scompte comptable des intérêts à recevoir
      CONSTRAINT adsys_calc_int_recevoir_pkey PRIMARY KEY (id_ag),
      CONSTRAINT fk_adsys_calc_int_recevoir_id_ag FOREIGN KEY (id_ag) REFERENCES ad_agc (id_ag) MATCH SIMPLE
      ON UPDATE NO ACTION ON DELETE NO ACTION,
      CONSTRAINT adsys_calc_int_recevoir_cpte_cpta_int_recevoir_fkey FOREIGN KEY (cpte_cpta_int_recevoir, id_ag)
      REFERENCES ad_cpt_comptable (num_cpte_comptable, id_ag) MATCH SIMPLE
      ON UPDATE NO ACTION ON DELETE NO ACTION
    )
    WITH (
      OIDS=FALSE
    );

    -- Insert default value for id_ag
    INSERT INTO adsys_calc_int_recevoir (id_ag) VALUES (numagc());

		RAISE NOTICE 'Table adsys_calc_int_recevoir created';
		output_result := 2;
	END IF;*/

  -- Insertion dans tableliste
  IF NOT EXISTS(SELECT * FROM tableliste WHERE nomc = 'adsys_calc_int_recevoir') THEN
    INSERT INTO tableliste VALUES ((select max(ident) from tableliste)+1, 'adsys_calc_int_recevoir', makeTraductionLangSyst('Paramétrage des calculs d''intérêts à recevoir sur dossiers de crédit'), true);
    RAISE NOTICE 'Données table adsys_calc_int_recevoir rajoutés dans table tableliste';
  END IF;

  -- Insertions champs dans d_tableliste

  -- Renseigne l'identifiant pour insertion dans d_tableliste
  tableliste_ident := (select ident from tableliste where nomc like 'adsys_calc_int_recevoir' order by ident desc limit 1);

  -- Insertion dans d_tableliste champ adsys_calc_int_recevoir.id_ag
  IF NOT EXISTS(SELECT * FROM d_tableliste WHERE nchmpc = 'id_ag' and tablen = tableliste_ident) THEN
      INSERT INTO d_tableliste VALUES ((select max(ident) from d_tableliste)+1, tableliste_ident, 'id_ag', makeTraductionLangSyst('ID agence'), true, NULL, 'int', NULL, true, false);
  END IF;

  -- Insertion dans d_tableliste champ adsys_calc_int_recevoir.freq_calc_int_recevoir
  IF NOT EXISTS(SELECT * FROM d_tableliste WHERE nchmpc = 'freq_calc_int_recevoir' and tablen = tableliste_ident) THEN
    INSERT INTO d_tableliste VALUES ((select max(ident) from d_tableliste)+1, tableliste_ident, 'freq_calc_int_recevoir', makeTraductionLangSyst('Fréquence de calcul des intérêts'), false, 1131, 'int', NULL, NULL, false);
  END IF;

  -- Insertion dans d_tableliste champ adsys_calc_int_recevoir.cpte_cpta_int_recevoir
  IF NOT EXISTS(SELECT * FROM d_tableliste WHERE nchmpc = 'cpte_cpta_int_recevoir' and tablen = tableliste_ident) THEN
    INSERT INTO d_tableliste VALUES ((select max(ident) from d_tableliste)+1, tableliste_ident, 'cpte_cpta_int_recevoir', makeTraductionLangSyst('Compte comptable des intérêts à recevoir'), false, 1400, 'txt', false, false, false);
  END IF;

  -- Create table historisation ad_calc_int_recevoir_his: historique des interets a recevoir

  IF NOT EXISTS(SELECT * FROM information_schema.tables WHERE table_name = 'ad_calc_int_recevoir_his') THEN  
  --SELECT INTO table_exist t.table_name FROM information_schema.tables t WHERE t.table_name = 'ad_calc_int_recevoir_his';
  --IF (table_exist is null) THEN
   CREATE TABLE ad_calc_int_recevoir_his
(
  id serial NOT NULL,
  id_doss integer NOT NULL, -- Le dossier de crédit
  id_ech integer NOT NULL, -- Nombre de jours d’une périodicité de remboursement
  date_traitement timestamp without time zone, -- Date de traitement : calcul intérêt/remboursement etc
  nb_jours integer, -- Nombre de jours pour lesquels les intérêts sont calculé
  periodicite_jours integer NOT NULL, -- Nombre de jours d’une périodicité de remboursement
  solde_int_ech numeric(30,6) DEFAULT 0, -- Le solde des intérêts à rembourser pour l’échéance courante à la date de calcul
  calcul_iar_theorique numeric(30,6), -- calcul theorique des interets sur l echeance
  solde_relica numeric(30,6), -- restant des interets de l echeance precedent
  montant numeric(30,6) DEFAULT 0, -- Le montant des intérêts à recevoir pour le dossier a la date de calcul
  etat_int integer NOT NULL, -- « adsys_etat_calc_int » : 1 =	Calculé,  2 : Repris etc
  solde_cap numeric(30,6) DEFAULT 0, -- Le capital restant dû du dossier à la date de calcul/reprise
  cre_etat integer NOT NULL, -- La classe de retard du crédit
  devise character(3), -- La devise
  id_his_calc integer, -- L’id_his pour les mouvements de « calcul »
  id_ecriture_calc integer, -- L’id_ecriture pour les mouvements de « calcul »
  id_his_reprise integer, -- L’id_his pour les mouvements de « reprise »
  id_ecriture_reprise integer, -- L’id_ecriture pour les mouvements de « reprise »
  id_ag integer NOT NULL,
  CONSTRAINT ad_calc_int_recevoir_his_pkey PRIMARY KEY (id, id_ag)
)
WITH (
  OIDS=FALSE
);
  ALTER TABLE ad_calc_int_recevoir_his
    OWNER TO postgres;
  COMMENT ON TABLE ad_calc_int_recevoir_his
    IS 'Les historiques des calcul d''interets a recevoir';
  COMMENT ON COLUMN ad_calc_int_recevoir_his.id_doss IS 'Le dossier de crédit';
  COMMENT ON COLUMN ad_calc_int_recevoir_his.id_ech IS 'Nombre de jours d’une périodicité de remboursement';
  COMMENT ON COLUMN ad_calc_int_recevoir_his.date_traitement IS 'Date de traitement : calcul intérêt/remboursement etc';
  COMMENT ON COLUMN ad_calc_int_recevoir_his.nb_jours IS 'Nombre de jours pour lesquels les intérêts sont calculé';
  COMMENT ON COLUMN ad_calc_int_recevoir_his.periodicite_jours IS 'Nombre de jours d’une périodicité de remboursement';
  COMMENT ON COLUMN ad_calc_int_recevoir_his.solde_int_ech IS ' Le solde des intérêts à rembourser pour l’échéance courante à la date de calcul';
  COMMENT ON COLUMN ad_calc_int_recevoir_his.calcul_iar_theorique IS ' calcul theorique des interets sur l echeance';
  COMMENT ON COLUMN ad_calc_int_recevoir_his.solde_relica IS ' restant des interets de l echeance precedent';
  COMMENT ON COLUMN ad_calc_int_recevoir_his.montant IS 'Le montant des intérêts à recevoir pour le dossier a la date de calcul';
  COMMENT ON COLUMN ad_calc_int_recevoir_his.etat_int IS '« adsys_etat_calc_int » : 1 =	Calculé,  2 : Repris etc';
  COMMENT ON COLUMN ad_calc_int_recevoir_his.solde_cap IS 'Le capital restant dû du dossier à la date de calcul/reprise';
  COMMENT ON COLUMN ad_calc_int_recevoir_his.cre_etat IS 'La classe de retard du crédit';
  COMMENT ON COLUMN ad_calc_int_recevoir_his.devise IS 'La devise';
  COMMENT ON COLUMN ad_calc_int_recevoir_his.id_his_calc IS 'L’id_his pour les mouvements de « calcul »';
  COMMENT ON COLUMN ad_calc_int_recevoir_his.id_ecriture_calc IS 'L’id_ecriture pour les mouvements de « calcul »';
  COMMENT ON COLUMN ad_calc_int_recevoir_his.id_his_reprise IS 'L’id_his pour les mouvements de « reprise »';
  COMMENT ON COLUMN ad_calc_int_recevoir_his.id_ecriture_reprise IS 'L’id_ecriture pour les mouvements de « reprise »';

		RAISE NOTICE 'Table ad_calc_int_recevoir_his created';
		output_result := 2;
	END IF;



  -- Nouvelle operation comptable des intérêts a recevoir : 374 - Intérêts à recevoir sur dossiers de crédit

	IF NOT EXISTS(SELECT * FROM ad_cpt_ope WHERE type_operation = 374 AND categorie_ope = 1 AND id_ag = numagc()) THEN
		INSERT INTO ad_cpt_ope(type_operation, categorie_ope, id_ag, libel_ope) VALUES (374, 1, numagc(), maketraductionlangsyst('Intérêts à recevoir sur dossiers de crédit'));
		RAISE NOTICE 'Insertion type_operation 374 dans la table ad_cpt_ope effectuée';
		output_result := 2;
	END IF;

	IF NOT EXISTS(SELECT * FROM ad_cpt_ope_cptes WHERE type_operation = 374 AND sens = 'd' AND categorie_cpte = 27 AND id_ag = numagc()) THEN
		INSERT INTO ad_cpt_ope_cptes(type_operation, num_cpte, sens, categorie_cpte, id_ag) VALUES (374, NULL, 'd', 27, numagc());

		RAISE NOTICE 'Insertion type_operation 374 sens ''d'' dans la table ad_cpt_ope_cptes effectuée';
		output_result := 2;
	END IF;

	IF NOT EXISTS(SELECT * FROM ad_cpt_ope_cptes WHERE type_operation = 374 AND sens = 'c' AND categorie_cpte = 6 AND id_ag = numagc()) THEN
		INSERT INTO ad_cpt_ope_cptes(type_operation, num_cpte, sens, categorie_cpte, id_ag) VALUES (374, NULL, 'c', 6, numagc());

		RAISE NOTICE 'Insertion type_operation 374 sens ''c'' dans la table ad_cpt_ope_cptes effectuée';
		output_result := 2;
	END IF;

-----------------------------------Ajout de l'operation comptable 409 : Régularisation des interêts à recevoir non remboursés-----


IF NOT EXISTS(SELECT * FROM ad_cpt_ope WHERE type_operation = 409 AND categorie_ope = 1 AND id_ag = numagc()) THEN
	INSERT INTO ad_cpt_ope(type_operation, categorie_ope, id_ag, libel_ope) VALUES (409, 1, numagc(), maketraductionlangsyst('Régularisation des interêts à recevoir non remboursés'));
	RAISE NOTICE 'Insertion type_operation 409 dans la table ad_cpt_ope effectuée';
	output_result := 2;
END IF;


IF NOT EXISTS(SELECT * FROM ad_cpt_ope_cptes WHERE type_operation = 409 AND sens = 'c' AND categorie_cpte = 27 AND id_ag = numagc()) THEN
	INSERT INTO ad_cpt_ope_cptes(type_operation, num_cpte, sens, categorie_cpte, id_ag) VALUES (409, NULL, 'c', 27, numagc());

	RAISE NOTICE 'Insertion type_operation 409 sens ''c'' dans la table ad_cpt_ope_cptes effectuée';
	output_result := 2;
END IF;

-----------------------------------Ajout du nouvelle operation comptable 375 : Remboursement Interêts à Recevoir sur dossiers de crédit-----


IF NOT EXISTS(SELECT * FROM ad_cpt_ope WHERE type_operation = 375 AND categorie_ope = 1 AND id_ag = numagc()) THEN
	INSERT INTO ad_cpt_ope(type_operation, categorie_ope, id_ag, libel_ope) VALUES (375, 1, numagc(), maketraductionlangsyst('Remboursement Interêts à Recevoir sur dossiers de crédit'));
	RAISE NOTICE 'Insertion type_operation 375 dans la table ad_cpt_ope effectuée';
	output_result := 2;
END IF;

IF NOT EXISTS(SELECT * FROM ad_cpt_ope_cptes WHERE type_operation = 375 AND sens = 'c' AND categorie_cpte = 27 AND id_ag = numagc()) THEN
	INSERT INTO ad_cpt_ope_cptes(type_operation, num_cpte, sens, categorie_cpte, id_ag) VALUES (375, NULL, 'c', 27, numagc());

	RAISE NOTICE 'Insertion type_operation 375 sens ''c'' dans la table ad_cpt_ope_cptes effectuée';
	output_result := 2;
END IF;

IF NOT EXISTS(SELECT * FROM ad_cpt_ope_cptes WHERE type_operation = 375 AND sens = 'd' AND categorie_cpte = 1 AND id_ag = numagc()) THEN
	INSERT INTO ad_cpt_ope_cptes(type_operation, num_cpte, sens, categorie_cpte, id_ag) VALUES (375, NULL, 'd', 1, numagc());

	RAISE NOTICE 'Insertion type_operation 375 sens ''d'' dans la table ad_cpt_ope_cptes effectuée';
	output_result := 2;
END IF;


  -- Ecran nouveau rapport compta calcul des interets a recevoir
	IF NOT EXISTS (SELECT nom_ecran FROM ecrans WHERE nom_ecran='Tra-41') THEN
		INSERT INTO ecrans(nom_ecran, fichier, nom_menu, fonction) VALUES ('Tra-41', 'modules/compta/rapports_compta.php', 'Tra-3', 430);
	END IF;

	IF NOT EXISTS (SELECT nom_ecran FROM ecrans WHERE nom_ecran='Tra-42') THEN
		INSERT INTO ecrans(nom_ecran, fichier, nom_menu, fonction) VALUES ('Tra-42', 'modules/compta/rapports_compta.php', 'Tra-3', 430);
	END IF;

	IF NOT EXISTS (SELECT nom_ecran FROM ecrans WHERE nom_ecran='Tra-43') THEN
		INSERT INTO ecrans(nom_ecran, fichier, nom_menu, fonction) VALUES ('Tra-43', 'modules/compta/rapports_compta.php', 'Tra-3', 430);
	END IF;


	RAISE NOTICE 'END UPDATE : Trac#356 : Mise a jour calcul des intérêts à recevoir ';
	RETURN output_result;

END;
$$
LANGUAGE plpgsql;

SELECT patch_ticket_356_II();
DROP FUNCTION patch_ticket_356_II();

-------------------------------- Ticket #356 : fonction calcul nombre des jours pour interets a recevoir --------------------------------
--------------- Calcul nombre de jours utiliser pour proratiser les interets lors des calcul d'interets a recevoir ------------
CREATE OR REPLACE FUNCTION get_nb_jrs_calc_int_recevoir(date, date, date, date) RETURNS integer AS $BODY$
DECLARE
	date_batch ALIAS FOR $1;
	date_deblocage ALIAS FOR $2;
	date_last_calc ALIAS FOR $3;
	date_last_remb ALIAS FOR $4;
	nb_jours INTEGER;
	tmp_date DATE;

BEGIN
	nb_jours := 0;

	IF(date_last_remb IS NULL AND date_last_calc IS NULL AND date_deblocage IS NULL) THEN
	  RAISE EXCEPTION ' Aucun date parametrer! ';
  END IF;

  IF(date_last_remb IS NULL AND date_last_calc IS NULL AND date_deblocage IS NOT NULL) THEN
    tmp_date := date_deblocage;
  END IF;

	IF(date_last_remb IS NOT NULL) THEN
	  IF(date_last_calc IS NULL OR (date_last_remb > date_last_calc)) THEN
      tmp_date := date_last_remb;
    ELSE
      IF (date_last_calc IS NOT NULL AND (date_last_calc > date_last_remb)) THEN
          tmp_date := date_last_calc;
      END IF;
    END IF;
  ELSE
    IF(date_last_calc IS NOT NULL) THEN
      tmp_date := date_last_calc;
    END IF;
	END IF;

	IF (date_batch IS NOT NULL) AND (tmp_date IS NOT NULL) THEN
		SELECT INTO nb_jours date_batch - tmp_date;
	END IF;

	RETURN nb_jours;
END;
$BODY$
LANGUAGE plpgsql;

-------------------------- Ticket #356 : fonction calcul nombre des jours pour periodicite par dossier -----------------
CREATE OR REPLACE FUNCTION get_nb_jrs_periodicite(
    integer,
    integer)
  RETURNS integer AS
$BODY$
DECLARE
	v_id_periodicite ALIAS FOR $1;
	v_id_doss ALIAS for $2;
	v_date_ech date;
	v_date_deblocage date;
	base_taux_agence integer;
	nb_jours INTEGER;

BEGIN
	nb_jours := 0;

  CASE
    WHEN (v_id_periodicite = 1) THEN -- Mensuelle
      nb_jours := 30;
    WHEN (v_id_periodicite = 2) THEN -- Quinzaine
      nb_jours := 60;
    WHEN (v_id_periodicite = 3) THEN -- Trimestrielle
      nb_jours := 90;
    WHEN (v_id_periodicite = 4) THEN -- Semestrielle
      nb_jours := 180;

    WHEN (v_id_periodicite = 5) THEN -- Annuelle
      -- Le base taux de calcul
      SELECT INTO base_taux_agence base_taux_epargne from ad_agc where id_ag = NumAgc();
      IF base_taux_agence  = 1 THEN
          nb_jours := 360;
        ELSE
          nb_jours := 365;
      END IF;

    WHEN (v_id_periodicite = 6) THEN -- En une fois : calcule basant sur la date echeance
      IF (v_id_doss IS NOT NULL) THEN
        SELECT INTO v_date_ech date_ech FROM ad_etr WHERE id_doss = v_id_doss AND id_ag = numagc() ORDER BY id_doss, id_ech DESC LIMIT 1;
        SELECT INTO v_date_deblocage cre_date_debloc FROM ad_dcr WHERE id_doss = v_id_doss AND id_ag = numagc();
        -- RAISE NOTICE 'v_date_ech = %  v_date_deblocage = %', v_date_ech, v_date_deblocage;
        nb_jours = v_date_ech - v_date_deblocage;
      ELSE
        RAISE EXCEPTION 'get_nb_jrs_periodicite() : Incorrect parameters supplied';
      END IF;

    WHEN (v_id_periodicite = 7) THEN -- Tous les 2 mois
      nb_jours := 60;
    WHEN (v_id_periodicite = 8) THEN -- Hebdomadaire
      nb_jours := 7;
    ELSE
  END CASE;

	RETURN nb_jours;
END;
$BODY$
LANGUAGE plpgsql;



------------------------------------------------------------------------------------------------------------------------
------------------------------------------- Ticket #356 : calcul des intérêts : Fonctions ------------------------------
------------------------------------------------------------------------------------------------------------------------

--Type : ensemble des informations renvoyées lors des calculs des interets a recevoir
DROP TYPE IF EXISTS calc_int_recevoir CASCADE;
CREATE TYPE calc_int_recevoir AS (
    id_doss varchar(100),         -- Le numero de compte client
 	  id_client int4,               -- Le titulaire du compte
 	  id_prod int4,                 -- L'id du produit d'epargne
 	  solde_cap float4,              -- solde capital du dossier
 	  nb_jours int4,                -- Le nombre de jours pour lesquels les intérêts sont calculés
 	  interets_calc float4          -- Le montant de l'intérêt calculé au prorata
 	);


 	-------------------------------------------------------------------------------------------------------------------------------------------
-- Calcul les interets a recevoir
--PARAMETRE
--					IN:
--								date_batch date date de calcul d'intérêts
--					OUT:
--							tableau des comptes d'epargne traités
-------------------------------------------------------------------------------------------------------------------------------------------

CREATE OR REPLACE FUNCTION calcul_interets_a_recevoir(DATE) RETURNS SETOF calc_int_recevoir AS $$

 	DECLARE
 	  date_batch ALIAS FOR $1;     				    -- la date d'éxécution du batch
 	  date_last_remb DATE;     				        -- date dernier remboursement du dossier
 	  date_last_calc DATE;     				        -- date dernier calcul des interets pour le dossier
	  freq_calc_int_recevoir_recup INTEGER;   -- La frequence des calculs des interets a payer
 	  resultat calc_int_recevoir;    			    -- ensemble d'informations renvoyées par cette procédure stockée
	  ligne RECORD;        								    -- ensemble d'informations sur le dossier en cours de traitement
 	  sum_solde_int NUMERIC(30,6);            -- Le solde interet pour l'echeance courante sur laquelle les interet serait proratisé
 	  interet NUMERIC(30,6);       				    -- le montant des intérêts calculés
    nb_jours_calc INTEGER;                  -- Le nombre de jours echuus entre calculs d'interets
    nb_jours_periodicite INTEGER;                    -- Le nombre de jours pour la periodicite de remboursment d'un dossier de credit

 	BEGIN
 	  -- La frequence de calcul des interets a payer
	 	SELECT INTO freq_calc_int_recevoir_recup freq_calc_int_recevoir FROM adsys_calc_int_recevoir WHERE id_ag = numagc() LIMIT 1;

	 	 -- Si la frequence est configuré
    IF(freq_calc_int_recevoir_recup IS NOT NULL AND freq_calc_int_recevoir_recup > 0) THEN

      -- Récupère des infos sur les dossiers concernés par les calculs d'intérêts à recevoir
      DROP TABLE IF EXISTS calcul_interets_a_recevoir;

      CREATE TEMP TABLE calcul_interets_a_recevoir AS
      SELECT d.id_doss, d.id_client, d.id_prod, d.etat, d.cre_etat, d.cre_date_debloc, d.cre_mnt_octr, d.cre_mnt_deb, p.periodicite,
            get_nb_jrs_calc_int_recevoir(
              date(date_batch),
              date(d.cre_date_debloc),
              (select date(max(date_traitement)) from ad_calc_int_recevoir_his where id_doss = d.id_doss and etat_calc_int = 1),
              (select date(max(date_traitement)) from ad_calc_int_recevoir_his where id_doss = d.id_doss and etat_calc_int = 2)
            ) as nb_jours
      FROM ad_dcr d
      INNER JOIN adsys_etat_credits e ON d.cre_etat = e.id
      INNER JOIN adsys_produit_credit p ON d.id_prod = p.id
      WHERE e.provisionne = 'f' -- Uniquement les dossiers non-provisionné
      AND p.mode_perc_int <> 1  -- Excluant les modes de perception 'Au debut'
      AND d.etat IN (5, 7, 13, 14, 15)
      AND (SELECT freq_calc_int_recevoir FROM adsys_calc_int_recevoir WHERE id_ag = numagc() LIMIT 1) > 0 -- Si la frequence est parametré
      AND ( -- Verification Frequence
        ( (SELECT freq_calc_int_recevoir FROM adsys_calc_int_recevoir WHERE id_ag = numagc() LIMIT 1) = 1 AND isFinMois(date(date_batch)) )
        OR ( (SELECT freq_calc_int_recevoir FROM adsys_calc_int_recevoir WHERE id_ag = numagc() LIMIT 1) = 2 AND isFinTrimestre(date(date_batch)) )
        OR ( (SELECT freq_calc_int_recevoir FROM adsys_calc_int_recevoir WHERE id_ag = numagc() LIMIT 1) = 3 AND isFinSemestre(date(date_batch)) )
        OR ( (SELECT freq_calc_int_recevoir FROM adsys_calc_int_recevoir WHERE id_ag = numagc() LIMIT 1) = 4 AND isFinAnnee(date(date_batch)) )
      )
      AND d.id_ag = e.id_ag
      AND d.id_ag = numagc()
      ORDER BY cre_etat, d.id_doss;

      -- id his de l'operation
      INSERT INTO ad_his (type_fonction, infos, date, id_ag)
	 	  VALUES  (212, makeTraductionLangSyst('Calcul des intérêts à recevoir sur dossiers de crédit'), now(),NumAgc());

    FOR ligne IN SELECT * FROM calcul_interets_a_recevoir LOOP
       ---------------------- Recuperation de solde interet pour l'echeance --------------------------------------------
      SELECT INTO sum_solde_int sum(solde_int) FROM ad_etr WHERE id_doss = ligne.id_doss
      AND date_ech <=
      (
        SELECT COALESCE
        (
          (SELECT min(date_ech) FROM ad_etr WHERE id_doss = ligne.id_doss AND date_ech >= date(date_batch)),
          (SELECT max(date_ech) FROM ad_etr WHERE id_doss = ligne.id_doss AND date_ech <= date(date_batch))
        )
      )
      AND remb = 'f' AND id_ag = numagc();

      RAISE NOTICE 'id_doss = %  sum_solde_int = %', ligne.id_doss, sum_solde_int;

      nb_jours_periodicite = get_nb_jrs_calc_int_recevoir(ligne.id_doss);
      RAISE NOTICE 'nb_jours_periodicite = %', nb_jours_periodicite;

      -- recuperation date dernier calcul interet
      SELECT INTO date_last_calc MAX(date_traitement) FROM ad_calc_int_recevoir_his WHERE id_doss = ligne.id_doss
      AND date_traitement <= date_batch AND etat_calc_int = 1 AND id_ag = numagc();

      -- recuperation date dernier remboursemernt sur le dossier
      SELECT INTO date_last_remb MAX(date_remb) AS max_date_remb FROM ad_sre WHERE id_doss = ligne.id_doss
      AND date_remb <= date_batch AND id_ag = numagc();

      nb_jours_calc = get_nb_jrs_calc_int_recevoir(date_batch, ligne.cre_date_debloc, date_last_calc, date_last_remb);


	  END LOOP;
		RETURN;


	 	ELSE
      RETURN;
    END IF;


  END;
$$ LANGUAGE 'plpgsql';

 	-------------------------------------------------------------------------------------------------------------------------------------------
-- Calcul les interets a recevoir: Propject pro 235 -236
--PARAMETRE
--					IN:
--								date_batch date date de calcul d'intérêts
--								id_doss integer dossier de calcul d'intérêts
--								id_ag integer id de l'agence
--								devise text
--					OUT:
--							tableau des interets a recevoir
-------------------------------------------------------------------------------------------------------------------------------------------

--Type : ensemble des informations renvoyées lors des calculs des interets a recevoir
DROP TYPE IF EXISTS iar_table CASCADE;
CREATE TYPE iar_table AS (
      id_doss int,
      id_ech_iar int,
      sum_solde_int numeric(30,6),
      date_ech_prec date,
      date_ech_iar date,
      nb_jours_prorata int,
      periodicite int,
      iar_calc_theorique numeric(30,6),
      solde_iar_his numeric(30,6),
      relica_prec_iar numeric(30,6),
      solde_iar numeric(30,6),
      solde_cap numeric(30,6) );


CREATE OR REPLACE FUNCTION calculsoldeiardoss(
    date,
    integer,
    integer,
    text)
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


---------------------------------------------------------------------
--    function getdebutmois utilisable dans le calcul interet IAR
---------------------------------------------------------------------

CREATE OR REPLACE FUNCTION getdebutmois(date)
  RETURNS date AS
$BODY$
DECLARE
	date_donnee ALIAS FOR $1;

	rang_jour INTEGER;
	tmp1_date DATE;


BEGIN

	-- RECUPERATION DU JOUR DANS date_donnee
	SELECT INTO rang_jour date_part('day', date_donnee);

	-- LE PREMIER DU MOIS
	SELECT INTO tmp1_date date_donnee - rang_jour + 1;


	RETURN date(tmp1_date);
END;
$BODY$
  LANGUAGE plpgsql VOLATILE
  COST 100;
ALTER FUNCTION getdebutmois(date)
  OWNER TO postgres;

---------------------------------------------------------------------
--    Type et fonction pour le rapport IAR
---------------------------------------------------------------------
DROP TYPE IF EXISTS iar_view CASCADE;
CREATE TYPE iar_view AS
(
    id_ag integer,
    id_doss integer,
    id_client integer,
	id_prod integer,
    nom_cli text,
    cap_restant_du numeric(30,6),
    cre_date_debloc date,
    id_ech integer,
    solde_int_ech numeric(30,6),
    date_debut_theorique date,
    nb_jours integer,
    montant numeric(30,6),
    montant_prec numeric(30,6),
    montant_cumul numeric(30,6)
 );
ALTER TYPE iar_view OWNER TO adbanking;


-------------------- Fonction Retournant les données pour le rapport IAR ----------------------------

CREATE OR REPLACE FUNCTION getiarview(
    date,
    integer)
  RETURNS SETOF iar_view AS
$BODY$
DECLARE
  date_rapport ALIAS FOR $1;
  id_agence ALIAS FOR $2;

  ligne_iar iar_view;
  ligne RECORD;

  v_montant_ech numeric(30,6):=0;

  v_montant_ech_prec numeric(30,6):=0;

  cur_iar CURSOR FOR
	select distinct on (id_doss)
	his.id_ag,
	his.id_doss,
	v.id_client,
	dcr.id_prod,
    v.nom_cli,
    coalesce(v.cre_mnt_octr,0) - coalesce(v.mnt_cred_paye,0) as cap_restant_du,
    v.cre_date_debloc,
	his.id_ech,
	solde_int_ech,
	case when his.id_ech = 1 then (select cre_date_debloc from ad_dcr where id_doss = his.id_doss) else
	(select max(date_ech) from ad_etr where id_doss = his.id_doss and id_ech < his.id_ech)  end as date_debut_theorique,
	nb_jours,
	etr.date_ech,
	his.date_traitement
	from ad_calc_int_recevoir_his his
	inner join ad_dcr dcr on his.id_doss = dcr.id_doss and his.id_ag = dcr.id_ag
	left join getportfeuilleview(date(date_rapport),id_agence) v on his.id_ag = v.id_ag and v.id_doss = his.id_doss
	left join ad_etr etr on his.id_doss = etr.id_doss and his.id_ech = etr.id_ech and his.id_ag = etr.id_ag
	where etat_int = 1
	and case when date(date_rapport) = date(now())
	then dcr.etat not in (6,9)
	else calculetatdossier_hist(id_agence,his.id_doss,date(date_rapport)) not in (6,9) end
	and date_traitement <= date (date_rapport) order by id_doss asc, date_traitement desc, id_ech desc;

BEGIN

  OPEN cur_iar ;
  FETCH cur_iar INTO ligne;
  WHILE FOUND LOOP

	raise notice 'id_doss : %', ligne.id_doss;

	select into v_montant_ech sum(case when etat_int = 1 then montant else -1*montant end )  as montant_iar from ad_calc_int_recevoir_his his where date_traitement <= date_rapport and id_doss = ligne.id_doss and id_ech = ligne.id_ech;

	if (v_montant_ech is null) then
	v_montant_ech := 0;
	end if;

	select into v_montant_ech_prec sum(case when etat_int = 1 then montant else -1*montant end )  as montant_iar from ad_calc_int_recevoir_his his where date_traitement <= date_rapport and id_doss = ligne.id_doss and id_ech < ligne.id_ech;

	if (v_montant_ech_prec is null) then
	v_montant_ech_prec := 0;
	end if;

	if(ligne.date_ech <= ligne.date_traitement) then
	v_montant_ech_prec := v_montant_ech;
	v_montant_ech := 0;
	end if;



	SELECT INTO ligne_iar ligne.id_ag, ligne.id_doss,ligne.id_client,ligne.id_prod, ligne.nom_cli, ligne.cap_restant_du, ligne.cre_date_debloc, ligne.id_ech, ligne.solde_int_ech, ligne.date_debut_theorique, ligne.nb_jours, v_montant_ech as montant ,v_montant_ech_prec as montant_prec,
coalesce(v_montant_ech,0)+coalesce(v_montant_ech_prec,0) as montant_cumul;

	RETURN NEXT ligne_iar;


  FETCH cur_iar INTO ligne;
  END LOOP;
 CLOSE cur_iar;
RETURN;
END;
$BODY$
  LANGUAGE plpgsql VOLATILE
  COST 100
  ROWS 1000;
ALTER FUNCTION getiarview(date, integer)
  OWNER TO adbanking;


----------------------------IAR des interets non payer avant debut des IAR-------------------------------------------------------
CREATE OR REPLACE FUNCTION calculsoldeiardoss_echnonpaye(
    date,
    integer,
    integer,
    text)
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

		/*IF : pour identifier si on est sur la dernière échéeance ou pas */
		IF ((select max(date_ech) from ad_etr where id_ag = in_id_ag and id_doss = in_id_doss) between getdebutmois(in_date_param) and in_date_param)
		THEN

			select into  v_id_ech_a_calculer, v_date_ech_iar, v_solde_int id_ech, date_ech, solde_int from ad_etr where id_doss = in_id_doss and date_ech=(select max(date_ech) from ad_etr where id_ag = in_id_ag and id_doss = in_id_doss) group by id_ech, date_ech, solde_int;

		ELSE
			select into  v_id_ech_a_calculer, v_date_ech_iar, v_solde_int id_ech, date_ech, solde_int from ad_etr where id_doss = in_id_doss and date_ech >= in_date_param group by id_ech, date_ech, solde_int order by date_ech limit 1;

		END IF;

		RAISE NOTICE 'v_id_ech_a_calculer --> %', v_id_ech_a_calculer;

		select into v_id_ech_prec min(id_ech) from ad_etr where id_ag = in_id_ag and id_doss = in_id_doss and remb = false and solde_int > 0 and id_ech < v_id_ech_a_calculer;

		RAISE NOTICE 'v_id_ech_prec --> %', v_id_ech_prec;

		/*Pour vérifier s'il y a une reprise sur les intérets des échéances précédentes */
		--IF ((select count(*) from ad_calc_int_recevoir_his where id_doss = in_id_doss) = 0) THEN

		WHILE (v_id_ech_prec < v_id_ech_a_calculer)
		LOOP
		SELECT INTO ligne_iar in_id_doss, id_ech, mnt_int, date_ech, date_ech, 0, 0,0, 0,0,solde_int, solde_cap from ad_etr where id_ag = in_id_ag and id_doss = in_id_doss and remb = false and solde_int > 0 and id_ech =  v_id_ech_prec;
	        RETURN NEXT ligne_iar;

		v_id_ech_prec:= v_id_ech_prec +1;

		END LOOP;

		--ELSE --pas de reprise sur l'historique des interest non payes

		--out_solde_iar:=0;
		/*
	        SELECT INTO ligne_iar in_id_doss, v_id_ech_a_calculer, v_solde_int, v_date_ech_prec, v_date_ech_iar, v_nb_jours_prorata, v_periodicite,v_iar_calc_theorique,
	        v_solde_his,v_relica_prec_iar,out_solde_iar, v_solde_cap ;
	        RETURN NEXT ligne_iar;


		END IF;
		*/


	ELSE --dossier non-eligible au calcul iar, donc on retourne 0

	out_solde_iar:=0;

	  SELECT INTO ligne_iar in_id_doss, v_id_ech_a_calculer, v_solde_int, v_date_ech_prec, v_date_ech_iar, v_nb_jours_prorata, v_periodicite,v_iar_calc_theorique,
	  v_solde_his,v_relica_prec_iar,out_solde_iar, v_solde_cap ;
	  RETURN NEXT ligne_iar;

	END IF;

	RETURN;
 END;
$BODY$
  LANGUAGE plpgsql VOLATILE
  COST 100
  ROWS 1000;
ALTER FUNCTION calculsoldeiardoss_echnonpaye(date, integer, integer, text)
  OWNER TO postgres;
