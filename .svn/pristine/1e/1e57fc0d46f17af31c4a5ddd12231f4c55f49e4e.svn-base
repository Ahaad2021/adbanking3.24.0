------------------------------- DEBUT : creation primary key dans ad_cpt_comptable --------------------------------
CREATE OR REPLACE FUNCTION create_pk_ad_cpt_comptable() RETURNS INT AS
$$
DECLARE
output_result INTEGER = 1;
tableliste_ident INTEGER = 0;

BEGIN
	RAISE NOTICE 'START UPDATE : creation primary key dans ad_cpt_comptable';

IF EXISTS (SELECT * FROM INFORMATION_SCHEMA.TABLES where table_name = 'ad_cpt_comptable_anc') THEN
	IF EXISTS (SELECT * FROM INFORMATION_SCHEMA.TABLE_CONSTRAINTS  WHERE CONSTRAINT_TYPE = 'PRIMARY KEY' AND TABLE_NAME = 'ad_cpt_comptable_anc' and CONSTRAINT_NAME = 'ad_cpt_comptable_pkey' ) THEN
		IF NOT EXISTS (SELECT * FROM INFORMATION_SCHEMA.TABLE_CONSTRAINTS  WHERE CONSTRAINT_TYPE = 'PRIMARY KEY' AND TABLE_NAME = 'ad_cpt_comptable') THEN
			ALTER TABLE ad_cpt_comptable ADD CONSTRAINT ad_cpt_comptable_pkey1 PRIMARY KEY (num_cpte_comptable, id_ag);
		END IF;
	END IF;
ELSE
	IF NOT EXISTS (SELECT * FROM INFORMATION_SCHEMA.TABLE_CONSTRAINTS  WHERE CONSTRAINT_TYPE = 'PRIMARY KEY' AND TABLE_NAME = 'ad_cpt_comptable') THEN
		ALTER TABLE ad_cpt_comptable ADD CONSTRAINT ad_cpt_comptable_pkey PRIMARY KEY (num_cpte_comptable, id_ag);
	END IF;
END IF;
RAISE NOTICE 'END UPDATE : creation primary key dans ad_cpt_comptable';
RETURN output_result;
END
$$
LANGUAGE plpgsql;

SELECT create_pk_ad_cpt_comptable();
DROP FUNCTION create_pk_ad_cpt_comptable();

-------------------------------- FIN : creation primary key dans ad_cpt_comptable --------------------------------

------------------------------- DEBUT : Ticket #356 : calcul des intérêts à payer et à recevoir : Ecrans / Parametrage --------------------------------

CREATE OR REPLACE FUNCTION patch_ticket_356() RETURNS INT AS
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
		IF NOT EXISTS(SELECT * FROM information_schema.tables WHERE table_name = 'adsys_calc_int_paye') THEN
		--SELECT INTO table_exist t.table_name FROM information_schema.tables t WHERE t.table_name = 'adsys_calc_int_paye';
		--IF (table_exist is null) THEN
			CREATE TABLE adsys_calc_int_paye
			(
			  id_ag integer NOT NULL,
			  freq_calc_int_paye integer, -- La fréquence de calcul des intérêts à payer
			  cpte_cpta_int_paye text, -- Le scompte comptable des intérêts à payer
			  CONSTRAINT adsys_calc_int_paye_pkey PRIMARY KEY (id_ag),
			  CONSTRAINT fk_adsys_calc_int_paye_id_ag FOREIGN KEY (id_ag) REFERENCES ad_agc (id_ag) MATCH SIMPLE
			  ON UPDATE NO ACTION ON DELETE NO ACTION,
			  CONSTRAINT adsys_calc_int_paye_cpte_cpta_int_paye_fkey FOREIGN KEY (cpte_cpta_int_paye, id_ag)
			  REFERENCES ad_cpt_comptable (num_cpte_comptable, id_ag) MATCH SIMPLE
			  ON UPDATE NO ACTION ON DELETE NO ACTION
			)
			WITH (
			  OIDS=FALSE
			);

			-- Insert default value for id_ag
			INSERT INTO adsys_calc_int_paye (id_ag) VALUES (numagc());

			RAISE NOTICE 'Table adsys_calc_int_paye created';
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
		IF NOT EXISTS(SELECT * FROM information_schema.tables WHERE table_name = 'adsys_calc_int_paye') THEN
		--SELECT INTO table_exist t.table_name FROM information_schema.tables t WHERE t.table_name = 'adsys_calc_int_paye';
		--IF (table_exist is null) THEN
			CREATE TABLE adsys_calc_int_paye
			(
			  id_ag integer NOT NULL,
			  freq_calc_int_paye integer, -- La fréquence de calcul des intérêts à payer
			  cpte_cpta_int_paye text, -- Le scompte comptable des intérêts à payer
			  CONSTRAINT adsys_calc_int_paye_pkey PRIMARY KEY (id_ag),
			  CONSTRAINT fk_adsys_calc_int_paye_id_ag FOREIGN KEY (id_ag) REFERENCES ad_agc (id_ag) MATCH SIMPLE
			  ON UPDATE NO ACTION ON DELETE NO ACTION,
			  CONSTRAINT adsys_calc_int_paye_cpte_cpta_int_paye_fkey FOREIGN KEY (cpte_cpta_int_paye)
			  REFERENCES ad_cpt_comptable (num_cpte_comptable) MATCH SIMPLE
			  ON UPDATE NO ACTION ON DELETE NO ACTION
			)
			WITH (
			  OIDS=FALSE
			);

			-- Insert default value for id_ag
			INSERT INTO adsys_calc_int_paye (id_ag) VALUES (numagc());

			RAISE NOTICE 'Table adsys_calc_int_paye created';
			output_result := 2;
				
		END IF;
	END IF;
	IF ((column_id_ag is not null) and (column_num_cpte_comptable is not null)) THEN
		-- Create table parametrage adsys_calc_int_paye
		IF NOT EXISTS(SELECT * FROM information_schema.tables WHERE table_name = 'adsys_calc_int_paye') THEN
		--SELECT INTO table_exist t.table_name FROM information_schema.tables t WHERE t.table_name = 'adsys_calc_int_paye';
		--IF (table_exist is null) THEN
			CREATE TABLE adsys_calc_int_paye
			(
			  id_ag integer NOT NULL,
			  freq_calc_int_paye integer, -- La fréquence de calcul des intérêts à payer
			  cpte_cpta_int_paye text, -- Le scompte comptable des intérêts à payer
			  CONSTRAINT adsys_calc_int_paye_pkey PRIMARY KEY (id_ag),
			  CONSTRAINT fk_adsys_calc_int_paye_id_ag FOREIGN KEY (id_ag) REFERENCES ad_agc (id_ag) MATCH SIMPLE
			  ON UPDATE NO ACTION ON DELETE NO ACTION,
			  CONSTRAINT adsys_calc_int_paye_cpte_cpta_int_paye_fkey FOREIGN KEY (cpte_cpta_int_paye, id_ag)
			  REFERENCES ad_cpt_comptable (num_cpte_comptable, id_ag) MATCH SIMPLE
			  ON UPDATE NO ACTION ON DELETE NO ACTION
			)
			WITH (
			  OIDS=FALSE
			);

			-- Insert default value for id_ag
			INSERT INTO adsys_calc_int_paye (id_ag) VALUES (numagc());

			RAISE NOTICE 'Table adsys_calc_int_paye created';
			output_result := 2;
				
		END IF;
	END IF;	
	---- END ticket pp 259 : SH

	RAISE NOTICE 'START UPDATE : Trac#356 : calcul des intérêts à payer et à recevoir';

  -- Check constraint primary key on ad_cpt_comptable
/*  IF CONSTRAINT PRIMARY KEY (ad_cpt_comptable_pkey) NOT EXISTS THEN
      ALTER TABLE ad_cpt_comptable ADD CONSTRAINT ad_cpt_comptable_pkey PRIMARY KEY (num_cpte_comptable, id_ag);
  END IF;*/

  /*-- Create table parametrage adsys_calc_int_paye
  IF NOT EXISTS(SELECT * FROM information_schema.tables WHERE table_name = 'adsys_calc_int_paye') THEN
    CREATE TABLE adsys_calc_int_paye
    (
      id_ag integer NOT NULL,
      freq_calc_int_paye integer, -- La fréquence de calcul des intérêts à payer
      cpte_cpta_int_paye text, -- Le scompte comptable des intérêts à payer
      CONSTRAINT adsys_calc_int_paye_pkey PRIMARY KEY (id_ag),
      CONSTRAINT fk_adsys_calc_int_paye_id_ag FOREIGN KEY (id_ag) REFERENCES ad_agc (id_ag) MATCH SIMPLE
      ON UPDATE NO ACTION ON DELETE NO ACTION,
      CONSTRAINT adsys_calc_int_paye_cpte_cpta_int_paye_fkey FOREIGN KEY (cpte_cpta_int_paye, id_ag)
      REFERENCES ad_cpt_comptable (num_cpte_comptable, id_ag) MATCH SIMPLE
      ON UPDATE NO ACTION ON DELETE NO ACTION
    )
    WITH (
      OIDS=FALSE
    );

    -- Insert default value for id_ag
    INSERT INTO adsys_calc_int_paye (id_ag) VALUES (numagc());

		RAISE NOTICE 'Table adsys_calc_int_paye created';
		output_result := 2;
	END IF;*/

  -- Insertion dans tableliste
  IF NOT EXISTS(SELECT * FROM tableliste WHERE nomc = 'adsys_calc_int_paye') THEN
    INSERT INTO tableliste VALUES ((select max(ident) from tableliste)+1, 'adsys_calc_int_paye', makeTraductionLangSyst('Paramétrage des calculs d''intérêts à payer sur comptes d''épargne'), true);
    RAISE NOTICE 'Données table adsys_calc_int_paye rajoutés dans table tableliste';
  END IF;

  -- Insertions champs dans d_tableliste

  -- Renseigne l'identifiant pour insertion dans d_tableliste
  tableliste_ident := (select ident from tableliste where nomc like 'adsys_calc_int_paye' order by ident desc limit 1);

  -- Insertion dans d_tableliste champ adsys_calc_int_paye.id_ag
  IF NOT EXISTS(SELECT * FROM d_tableliste WHERE nchmpc = 'id_ag' and tablen = tableliste_ident) THEN
      INSERT INTO d_tableliste VALUES ((select max(ident) from d_tableliste)+1, tableliste_ident, 'id_ag', makeTraductionLangSyst('ID agence'), true, NULL, 'int', NULL, true, false);
  END IF;

  -- Insertion dans d_tableliste champ adsys_calc_int_paye.freq_calc_int_paye
  IF NOT EXISTS(SELECT * FROM d_tableliste WHERE nchmpc = 'freq_calc_int_paye' and tablen = tableliste_ident) THEN
    INSERT INTO d_tableliste VALUES ((select max(ident) from d_tableliste)+1, tableliste_ident, 'freq_calc_int_paye', makeTraductionLangSyst('Fréquence de calcul des intérêts'), false, 1131, 'int', NULL, NULL, false);
  END IF;

  -- Insertion dans d_tableliste champ adsys_calc_int_paye.cpte_cpta_int_paye
  IF NOT EXISTS(SELECT * FROM d_tableliste WHERE nchmpc = 'cpte_cpta_int_paye' and tablen = tableliste_ident) THEN
    INSERT INTO d_tableliste VALUES ((select max(ident) from d_tableliste)+1, tableliste_ident, 'cpte_cpta_int_paye', makeTraductionLangSyst('Compte comptable des intérêts à payer'), false, 1400, 'txt', false, false, false);
  END IF;

  -- Create table historisation ad_calc_int_paye_his: historique des interets a payer

  IF NOT EXISTS(SELECT * FROM information_schema.tables WHERE table_name = 'ad_calc_int_paye_his') THEN
  --SELECT INTO table_exist t.table_name FROM information_schema.tables t WHERE t.table_name = 'ad_calc_int_paye_his';
  --IF (table_exist is null) THEN
    CREATE TABLE ad_calc_int_paye_his
    (
      id serial NOT NULL,
      id_cpte integer NOT NULL, -- Le compte client du DAT/CAT
      id_titulaire integer NOT NULL, -- Le titulaire du compte
      id_prod integer NOT NULL,
      montant_int numeric(30,6) DEFAULT 0, -- Le montant des intérêts a payer, calculé au prorata
      devise character(3),
      nb_jours integer, -- Nombre de jours pour lesquels les intérêts sont calculé
      nb_jours_echus integer, -- Nombre de jours échus entre la date du jour / date calcul courant et la date de mise en place du DAT.
      etat_calc_int integer NOT NULL, -- « adsys_etat_calc_int » : 1 =	Calculé,  2 : Repris etc
      date_calc timestamp without time zone, -- La date à laquelle le batch calcule et réserve le montant à payer. Correspond à l’etat_calc_int : 1.
      date_reprise timestamp without time zone, -- La date à laquelle le montant réservé est utilisé pour rémunérer le compte des intérêts du client. Correspond à l’etat_calc : 2.
      solde numeric(30,6) DEFAULT 0, -- Le solde du compte  à la date_calc (ad_cpt.solde)
      solde_calcul_interets numeric(30,6) DEFAULT 0, -- Le solde du calcul des interets à la date_calc (ad_cpt.solde_calcul_interets)
      id_his_calc integer, -- L’id_his pour les mouvements de « calcul »
      id_ecriture_calc integer, -- L’id_ecriture pour les mouvements de « calcul »
      id_his_reprise integer, -- L’id_his pour les mouvements de « reprise »
      id_ecriture_reprise integer, -- L’id_ecriture pour les mouvements de « reprise »
      id_ag integer NOT NULL,
      CONSTRAINT ad_calc_int_paye_his_pkey PRIMARY KEY (id, id_ag),
      CONSTRAINT ad_calc_int_paye_his_id_prod_fkey FOREIGN KEY (id_prod, id_ag)
      REFERENCES adsys_produit_epargne (id, id_ag) MATCH SIMPLE ON UPDATE NO ACTION ON DELETE NO ACTION
    )
    WITH (
      OIDS=FALSE
    );

    COMMENT ON TABLE ad_calc_int_paye_his IS 'Les historiques des calcul d''interets a payer';
    COMMENT ON COLUMN ad_calc_int_paye_his.id_cpte IS 'Le compte client du DAT/CAT';
    COMMENT ON COLUMN ad_calc_int_paye_his.id_titulaire IS 'Le titulaire du compte';
    COMMENT ON COLUMN ad_calc_int_paye_his.montant_int IS 'Le montant des intérêts a payer, calculé au prorata';
    COMMENT ON COLUMN ad_calc_int_paye_his.nb_jours IS 'Nombre de jours pour lesquels les intérêts sont calculé';
    COMMENT ON COLUMN ad_calc_int_paye_his.nb_jours_echus IS 'Nombre de jours échus entre la date du jour / date calcul courant et la date de mise en place du DAT';
    COMMENT ON COLUMN ad_calc_int_paye_his.etat_calc_int IS '« adsys_etat_calc_int » : 1 =	Calculé,  2 : Repris etc';
    COMMENT ON COLUMN ad_calc_int_paye_his.date_calc IS 'La date à laquelle le batch calcule et réserve le montant à payer. Correspond à l’etat_calc_int : 1.';
    COMMENT ON COLUMN ad_calc_int_paye_his.date_reprise IS 'La date à laquelle le montant réservé est utilisé pour rémunérer le compte des intérêts du client. Correspond à l’etat_calc : 2';
    COMMENT ON COLUMN ad_calc_int_paye_his.solde IS 'Le solde du compte  à la date_calc (ad_cpt.solde)';
    COMMENT ON COLUMN ad_calc_int_paye_his.solde_calcul_interets IS 'Le solde du calcul des interets à la date_calc (ad_cpt.solde_calcul_interets)';
    COMMENT ON COLUMN ad_calc_int_paye_his.id_his_calc IS 'L’id_his pour les mouvements de « calcul »';
    COMMENT ON COLUMN ad_calc_int_paye_his.id_his_reprise IS 'L’id_his pour les mouvements de « reprise »';
    COMMENT ON COLUMN ad_calc_int_paye_his.id_ecriture_calc IS 'L’id_ecriture pour les mouvements de « calcul »';
    COMMENT ON COLUMN ad_calc_int_paye_his.id_ecriture_reprise IS 'L’id_ecriture pour les mouvements de « reprise »';

		RAISE NOTICE 'Table ad_calc_int_paye_his created';
		output_result := 2;
	END IF;

	-- Update table parametrage produit epargne with flag "is_calc_int_paye"
	IF NOT EXISTS(SELECT * FROM information_schema.columns WHERE table_name = 'adsys_produit_epargne' AND column_name = 'is_calc_int_paye') THEN
		ALTER TABLE adsys_produit_epargne ADD COLUMN is_calc_int_paye boolean DEFAULT false;
		output_result := 2;
	END IF;

	-- Insert into "d_tableliste" if  flag "is_calc_int_paye" notExist
	IF NOT EXISTS
	  (SELECT * FROM d_tableliste WHERE tablen = (select ident from tableliste where nomc like 'adsys_produit_epargne' order by ident desc limit 1) AND nchmpc = 'is_calc_int_paye')
	THEN
		INSERT INTO d_tableliste(ident, tablen, nchmpc, nchmpl, isreq, ref_field, type, onslct, ispkey, traduit)
		VALUES ((select max(ident) from d_tableliste)+1, (select ident from tableliste where nomc like 'adsys_produit_epargne' order by ident desc limit 1), 'is_calc_int_paye', maketraductionlangsyst('Calculer les intérêts à payer pour les comptes de ce produit ?'), false, NULL, 'bol', false, false, false);
		output_result := 2;
	END IF;

  -- Nouvelle operation comptable des intérêts a payer : 372

	IF NOT EXISTS(SELECT * FROM ad_cpt_ope WHERE type_operation = 372 AND categorie_ope = 1 AND id_ag = numagc()) THEN
		INSERT INTO ad_cpt_ope(type_operation, categorie_ope, id_ag, libel_ope) VALUES (372, 1, numagc(), maketraductionlangsyst('Intérêts à payer sur comptes d''épargne'));
		RAISE NOTICE 'Insertion type_operation 372 dans la table ad_cpt_ope effectuée';
		output_result := 2;
	END IF;

	IF NOT EXISTS(SELECT * FROM ad_cpt_ope_cptes WHERE type_operation = 372 AND sens = 'd' AND categorie_cpte = 10 AND id_ag = numagc()) THEN
		INSERT INTO ad_cpt_ope_cptes(type_operation, num_cpte, sens, categorie_cpte, id_ag) VALUES (372, NULL, 'd', 10, numagc());

		RAISE NOTICE 'Insertion type_operation 372 sens ''d'' dans la table ad_cpt_ope_cptes effectuée';
		output_result := 2;
	END IF;

	IF NOT EXISTS(SELECT * FROM ad_cpt_ope_cptes WHERE type_operation = 372 AND sens = 'c' AND categorie_cpte = 26 AND id_ag = numagc()) THEN
		INSERT INTO ad_cpt_ope_cptes(type_operation, num_cpte, sens, categorie_cpte, id_ag) VALUES (372, NULL, 'c', 26, numagc());

		RAISE NOTICE 'Insertion type_operation 372 sens ''c'' dans la table ad_cpt_ope_cptes effectuée';
		output_result := 2;
	END IF;


  -- Ecran nouveau rapport compta calcul des interets a payer
	IF NOT EXISTS (SELECT nom_ecran FROM ecrans WHERE nom_ecran='Tra-35') THEN
		INSERT INTO ecrans(nom_ecran, fichier, nom_menu, fonction) VALUES ('Tra-35', 'modules/compta/rapports_compta.php', 'Tra-3', 430);
	END IF;

	IF NOT EXISTS (SELECT nom_ecran FROM ecrans WHERE nom_ecran='Tra-36') THEN
		INSERT INTO ecrans(nom_ecran, fichier, nom_menu, fonction) VALUES ('Tra-36', 'modules/compta/rapports_compta.php', 'Tra-3', 430);
	END IF;

	IF NOT EXISTS (SELECT nom_ecran FROM ecrans WHERE nom_ecran='Tra-37') THEN
		INSERT INTO ecrans(nom_ecran, fichier, nom_menu, fonction) VALUES ('Tra-37', 'modules/compta/rapports_compta.php', 'Tra-3', 430);
	END IF;


	RAISE NOTICE 'END UPDATE : Trac#356 : calcul des intérêts à payer et à recevoir';
	RETURN output_result;

END;
$$
LANGUAGE plpgsql;

SELECT patch_ticket_356();
DROP FUNCTION patch_ticket_356();

-------------------------------- FIN : Ticket #356 : calcul des intérêts à payer et à recevoir : Ecrans / Parametrage --------------------------------

-------------------------------- Ticket #356 : fonction calcul nombre des jours pour interets a payer --------------------------------
CREATE OR REPLACE FUNCTION get_nb_jrs_calc_int_paye(date, date, date, date) RETURNS integer AS $BODY$
DECLARE
	date_batch ALIAS FOR $1;
	date_ouv ALIAS FOR $2;
	date_last_calc ALIAS FOR $3;
	date_last_remuneration ALIAS FOR $4;
	nb_jours INTEGER;
	tmp_date DATE;

BEGIN
	nb_jours := 0;

	IF(date_last_remuneration IS NULL AND date_last_calc IS NULL AND date_ouv IS NULL) THEN
	  RAISE EXCEPTION ' Aucun date parametrer! ';
  END IF;

  IF(date_last_remuneration IS NULL AND date_last_calc IS NULL AND date_ouv IS NOT NULL) THEN
    tmp_date := date_ouv;
  END IF;

	IF(date_last_remuneration IS NOT NULL) THEN
	  IF(date_last_calc IS NULL OR (date_last_remuneration > date_last_calc)) THEN
      tmp_date := date_last_remuneration;
    ELSE
      IF (date_last_calc IS NOT NULL AND (date_last_calc > date_last_remuneration)) THEN
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

-------------------------------- FIN: Ticket #356 : fonction calcul nombre des jours pour interets a payer --------------------------------

------------------------------------------------- Ticket #356 : calcul des intérêts : Fonctions -----------------------------------------------------------------------------------------
--Type : ensemble des informations renvoyées lors des calculs des interets a payer
DROP TYPE IF EXISTS int_calc_int_paye CASCADE;
CREATE TYPE int_calc_int_paye AS (
 	  num_cpte varchar(100),                -- Le numero de compte client
 	  id_titulaire int4,            -- Le titulaire du compte
 	  id_prod int4,                 -- L'id du produit d'epargne
 	  nb_jours int4,                -- Le nombre de jours pour lesquels les intérêts sont calculés
    nb_jours_echus int4,          -- Le nombre de jours echuus entre la date de calcul et la date d'ouverture du compte
 	  classe_comptable int4,        -- Type comptable des comptes d'epargne (2: DAT, 5 : CAT)
 	  interets_calc float4          -- Le montant de l'intérêt calculé au prorata
 	);

-------------------------------------------------------------------------------------------------------------------------------------------
-- Calcul les interets a payer sur les comptes a terme (DAT,CAT) si la frequence de calcul coîncide avec la date du batch
--PARAMETRE
--					IN:
--								date_batch date date de calcul d'intérêts
--					OUT:
--							tableau des comptes d'epargne traités
--------------------------------------------------------------------------a-----------------------------------------------------------------

CREATE OR REPLACE FUNCTION  calcul_interets_a_payer(DATE)
 	RETURNS SETOF int_calc_int_paye AS  $$

 	DECLARE
	  date_batch ALIAS FOR $1;     				-- la date d'éxécution du batch
	  base_taux_agence INTEGER;    				-- la base du taux de l'agence telle que parametrée(1 ou 2)
	  freq_calc_int_paye_recup INTEGER;         -- La frequence des calculs des interets a payer
 	  resultat int_calc_int_paye;    			-- ensemble d'informations renvoyées par cette procédure stockée
	  ligne RECORD;        								-- ensemble d'informations sur le compte encours de traitement
 	  jr_annee INTEGER;      							-- nombre de jours dans l'année en fonction du base_taux_agence(360 ou 365)
 	  interet NUMERIC(30,6);       				-- le montant de la rémunération(intérêts)
    nb_jours_echus_calc INTEGER;             -- Le nombre de jours echuus entre la date de calcul et la date d'ouverture du compte
    rep INTEGER;            						-- valeur de retour de la fonction traite_int_a_payer

 	BEGIN

    -- Le base taux de calcul
	 	SELECT INTO base_taux_agence base_taux_epargne from ad_agc where id_ag = NumAgc();
	 	IF base_taux_agence  = 1 THEN
	 	    jr_annee := 360;
	 	  ELSE
	 	    jr_annee := 365;
	 	END IF;

	 	-- La frequence de calcul des interets a payer
	 	SELECT INTO freq_calc_int_paye_recup freq_calc_int_paye FROM adsys_calc_int_paye WHERE id_ag = numagc() LIMIT 1;

    -- Si la frequence est configuree
    IF(freq_calc_int_paye_recup IS NOT NULL AND freq_calc_int_paye_recup > 0) THEN

      -- Récupère des infos sur les comptes épargne concernés par les calculs d'intérêts à payer

      DROP TABLE IF EXISTS calcul_interets_a_payer;

      CREATE TEMP TABLE calcul_interets_a_payer AS
      SELECT a.id_cpte, a.num_complet_cpte, a.solde, a.solde_calcul_interets, a.tx_interet_cpte, a.interet_a_capitaliser, a.devise,
            a.id_titulaire, a.date_calcul_interets, a.mode_calcul_int_cpte,
            a.date_ouvert as date_ouvert,
            b.classe_comptable, b.id as id_prod_epargne, b.cpte_cpta_prod_ep, b.cpte_cpta_prod_ep_int, b.is_calc_int_paye,
            (SELECT freq_calc_int_paye FROM adsys_calc_int_paye WHERE id_ag = numagc() LIMIT 1) as freq_calc_int_paye_param,
            get_nb_jrs_calc_int_paye(
              date(date_batch),
              date(a.date_ouvert),
              (select date(max(date_calc)) from ad_calc_int_paye_his where id_cpte = a.id_cpte),
              (select date(max(date_reprise)) from ad_calc_int_paye_his where id_cpte = a.id_cpte)
            ) as perio_cap
      FROM ad_cpt a, adsys_produit_epargne b
      WHERE a.id_prod = b.id AND a.id_ag = NumAgc() AND a.id_ag = b.id_ag
      AND etat_cpte = 1 -- Uniquement les comptes ouverts
      AND b.service_financier = true
      AND (b.classe_comptable = 2 OR b.classe_comptable = 5) -- depot a terme ou compte a terme
      AND a.tx_interet_cpte > 0
      AND b.is_calc_int_paye = 'TRUE'
      AND terme_cpte > 0
      AND a.mode_calcul_int_cpte <> 12 -- Non epargne a la source
      AND (SELECT freq_calc_int_paye FROM adsys_calc_int_paye WHERE id_ag = numagc() LIMIT 1) > 0 -- Si la frequence est parametré
      AND ( -- Verification Frequence
      	(date(dat_date_fin) = date(date_batch))
        OR ( (SELECT freq_calc_int_paye FROM adsys_calc_int_paye WHERE id_ag = numagc() LIMIT 1) = 1 AND isFinMois(date(date_batch)) )
        OR ( (SELECT freq_calc_int_paye FROM adsys_calc_int_paye WHERE id_ag = numagc() LIMIT 1) = 2 AND isFinTrimestre(date(date_batch)) )
        OR ( (SELECT freq_calc_int_paye FROM adsys_calc_int_paye WHERE id_ag = numagc() LIMIT 1) = 3 AND isFinSemestre(date(date_batch)) )
        OR ( (SELECT freq_calc_int_paye FROM adsys_calc_int_paye WHERE id_ag = numagc() LIMIT 1) = 4 AND isFinAnnee(date(date_batch)) )
      )
      ORDER BY a.id_cpte;

      -- id his de l'operation
      INSERT INTO ad_his (type_fonction, infos, date, id_ag)
	 	  VALUES  (212, makeTraductionLangSyst('Calcul des intérêts à payer sur comptes epargne'), now(),NumAgc());

      FOR ligne IN SELECT  * FROM calcul_interets_a_payer
	   	LOOP
        ----------------------CALCUL DES INTERETS--------------------------------------------
        IF (ligne.mode_calcul_int_cpte <> 12) THEN
          interet := ligne.solde_calcul_interets * (ligne.tx_interet_cpte * ligne.perio_cap)/jr_annee;
        ELSE --mode_calcul_int_cpte=12 epargne à la source
          -- interet := ligne.interet_a_capitaliser;
          interet := 0; -- ?
        END IF;

        ---------------- S'il y a des interets a payer ------------------------------------------------
        IF (interet > 0) THEN
        ----------------------- MOUVEMENT COMPTABLE DES INTERETS A PAYER ----------------------------
          rep := traite_int_a_payer(ligne.id_cpte, interet, date_batch);

          ------------------------ ARCHIVAGE : ad_calc_int_paye_his ---------------------------------------
          nb_jours_echus_calc := date(date_batch) - date(ligne.date_ouvert);

          INSERT INTO ad_calc_int_paye_his (id_cpte, id_titulaire, id_prod, montant_int, devise, nb_jours, nb_jours_echus, etat_calc_int, date_calc, date_reprise, solde, solde_calcul_interets, id_his_calc, id_ecriture_calc, id_his_reprise, id_ecriture_reprise, id_ag)
          VALUES (ligne.id_cpte, ligne.id_titulaire, ligne.id_prod_epargne, interet, ligne.devise, ligne.perio_cap, nb_jours_echus_calc, 1, date_batch, NULL, ligne.solde, ligne.solde_calcul_interets, (SELECT currval('ad_his_id_his_seq')), (SELECT currval('ad_ecriture_seq')), NULL, NULL, numagc());

        ELSE
          interet := 0;
        END IF;

        -- ----------le tableau renvoyé par la fonction
        SELECT INTO resultat ligne.num_complet_cpte, ligne.id_titulaire, ligne.id_prod_epargne, ligne.perio_cap, nb_jours_echus_calc, ligne.classe_comptable, interet;
        RETURN NEXT resultat;
        RAISE NOTICE 'le compte traité est %',ligne.id_cpte;

		END LOOP;
		RETURN;

    ELSE
      RETURN;
    END IF;

 	END;
 	$$ LANGUAGE 'plpgsql';

--------------------------------------------------------------------------------------------------------------------------------------------------
-- Fonction permettant d'executer les mouvements comptables des calculs d'interets a payer
-- PARAMETRE
--   				IN :
--							id_cpte_source interger idenfiant du compte d'epargne source du client
--							interet				NUMERIC montant des interet à payer
--							date_batch	date	date de paiement des interets
--				OUT:
--						code_retour INTEGER	code de retour de la fonction
--							0:=tout c'est bien passé
--							-1:=erreur inconnue
--							code_retour >0 code erreur
---------------------------------------------------------------------------------------------------------------------------------------------------
CREATE OR REPLACE FUNCTION traite_int_a_payer(id_cpte_source integer, interet numeric, date_batch date) RETURNS integer AS $BODY$

 DECLARE
  cpte_cpta_int_paye_param TEXT;    		-- compte comptable du compte des interets a payer sur produits d'epargne
 	cpte_cpta_int_assoc_prod_ep  text;	-- compte comptable des intêret associé au produit d'epargne
	id_cpte_cpta_int_assoc_prod_ep  text;	-- id compte comptable des intêret associé au produit d'epargne
	devise_cpte_int  char(3);		--devise compte comptable associé au produit d'epargne

	num_cpte_credit TEXT;			--compte au crédit
	num_cpte_debit TEXT;			--compte au debit

	exo_courant INTEGER;      		-- l'id de l'exercice courant
	jou1 INTEGER;        			-- l'id du journal associé au compte au débit s'il est principal
	jou2 INTEGER;       			-- l'id du journal associé au crédit s'il est principal
	id_journal  INTEGER;           		-- id du journal des mouvements comptables

	cpte_liaison TEXT;      		-- compte de liaison si les deux comptes à mouvementer sont principaux
	devise_cpte_liaison CHAR(3);    	-- code de la devise du compte de liaison
	code_dev_ref CHAR(3);       		-- code de la devise de référence
	cpt_pos_ch  TEXT;         		-- code de position de change de la devise du compte traité
	cpt_cv_pos_ch TEXT;        		-- code de C/V de la position de change de la devise du compte traité
	interet_a_verser NUMERIC(30,6); 	-- le montant converti de la rémunération(utile aux écritures comptables en cas de devises différentes pour les comptes comptables qui entrent en jeu)
	libel_op INTEGER ;				    --libel de l'opération
	type_op INTEGER := 372 ; 			--type operation

  id_his_return INTEGER;       -- l'id his a retourner

 BEGIN
	----------------------récupération de l'exercice courant : besoin pour écriture comptable---------------------------
	SELECT INTO exo_courant MAX(id_exo_compta) FROM ad_exercices_compta WHERE etat_exo = 1;

	----------------------récupération de la devise de référence : besoin pour écriture comptable généralement en multidevise------------------------
	SELECT INTO code_dev_ref code_devise_reference FROM ad_agc;

	------------------ Recuperation du libelle de l'operation 40--------------------------
	SELECT INTO libel_op libel_ope FROM ad_cpt_ope WHERE id_ag=numAgc() AND type_operation=type_op; -- 372

	-----------------------COMPTE COMPTABLE D'INTERET ASSOCIE AU PRODUIT D'EPARGNE (LE COMPTE COMPTABLE A DEBITER) --------------------
	SELECT INTO cpte_cpta_int_assoc_prod_ep,id_cpte_cpta_int_assoc_prod_ep b.cpte_cpta_prod_ep_int,b.id FROM ad_cpt a, adsys_produit_epargne b WHERE a.id_ag = NumAgc() AND a.id_ag = b.id_ag AND a.id_prod = b. id AND  a.id_cpte = id_cpte_source;
	IF cpte_cpta_int_assoc_prod_ep IS NULL THEN
		RAISE EXCEPTION ' Aucun compte comptable des interet associé au produit N°=% , veuillez revoir le paramétrage ',id_cpte_cpta_int_assoc_prod_ep;
	END IF;
	num_cpte_debit :=cpte_cpta_int_assoc_prod_ep;

	----------------------- COMPTE COMPTABLE DES INTERETS A PAYER (LE COMPTE COMPTABLE A CREDITER) --------------------
	SELECT INTO cpte_cpta_int_paye_param cpte_cpta_int_paye FROM adsys_calc_int_paye WHERE id_ag = numagc() LIMIT 1;

  IF cpte_cpta_int_paye_param IS NULL THEN
		RAISE EXCEPTION ' Aucun compte comptable associé aux intérêts à payer sur compte d''épargne, veuillez revoir le paramétrage du calcul des intérêts à payer ';
	END IF;

	SELECT INTO devise_cpte_int devise FROM ad_cpt_comptable WHERE num_cpte_comptable = cpte_cpta_int_paye_param AND id_ag = numagc();

	-- Construction du numéro de compte à  créditer ----
  num_cpte_credit := cpte_cpta_int_paye_param;


  -----------------------INFORMATION SUR LES JOURNAUX COMPTABLE ----------------------------

	-- Récupération du journal associé au compte comptable des intérêts
	SELECT INTO jou1 recupeJournal(num_cpte_debit);

	-- Récupération du journal associé si le compte au crédit du produit d'épargne est principal
	SELECT INTO jou2 recupeJournal(num_cpte_credit);

	-------------------------------------------------------------------------------------------
	--		       PASSAGE DES ECRITURES COMPTABLES                                  --
	-------------------------------------------------------------------------------------------
  IF jou1 IS NOT NULL AND jou2 IS NOT NULL AND jou1 <> jou2 THEN -- num_cpte_debit ET COMPTE AU CREDIT SONT PINCIPAUX ET DE JOURNAUX DIFFERENTS

      --on récupère alors le compte de liaison
      SELECT INTO cpte_liaison num_cpte_comptable FROM ad_journaux_liaison
        WHERE (id_ag = NumAgc() AND id_jou1 = jou1 AND id_jou2 = jou2) OR (id_jou1 = jou2 AND id_jou2 = jou1);
      RAISE NOTICE 'compte de liaison entre journal % et journal %  est %', jou1, jou2, cpte_liaison;

      -- Récuperation de la devise du compte de liaison
      SELECT INTO devise_cpte_liaison devise FROM ad_cpt_comptable
        WHERE id_ag = NumAgc() AND num_cpte_comptable = cpte_liaison;
      RAISE NOTICE 'Devise du compte de liaison : % ', devise_cpte_liaison;

      ---------- DEBIT COMPTE DES INTERET PAR CREDIT DU COMPTE DE LIAISON -----------------------
      IF devise_cpte_liaison = code_dev_ref THEN  ----- num_cpte_debit et cpte_liaison sont de la même devise

         -- Ecriture comptable
        INSERT INTO ad_ecriture(id_his,id_ag, date_comptable, libel_ecriture, id_jou, id_exo, ref_ecriture,type_operation,info_ecriture)
          VALUES ((SELECT currval('ad_his_id_his_seq')),NumAgc(), date(date_batch), libel_op, jou1, exo_courant, makeNumEcriture(jou1, exo_courant),type_op, NULL);

        -- Mouvement comptable au débit
        INSERT INTO ad_mouvement (id_ecriture, id_ag, compte, cpte_interne_cli, sens, montant, devise, date_valeur)
          VALUES ((SELECT currval('ad_ecriture_seq')), NumAgc(), num_cpte_debit, NULL, 'd', interet, code_dev_ref, getDateValeur(NULL,'d',date_batch));

        -- Mouvement comptable au crédit
        INSERT INTO ad_mouvement (id_ecriture, id_ag,compte, cpte_interne_cli, sens, montant, devise, date_valeur)
          VALUES ((SELECT currval('ad_ecriture_seq')), NumAgc(), cpte_liaison, NULL, 'c', interet, devise_cpte_liaison, getDateValeur(NULL,'c',date_batch));

         -- Mise à  jour des soldes comptables
        UPDATE ad_cpt_comptable set solde = solde - interet WHERE id_ag = NumAgc() AND num_cpte_comptable = num_cpte_debit;
        UPDATE ad_cpt_comptable set solde = solde + interet WHERE id_ag = NumAgc() AND num_cpte_comptable = cpte_liaison;

      ELSE --(devise_debit <> devise_cpte_liaison) donc cpte_liaison est en devise étrangère, on doit faire la conversion

        SELECT INTO cpt_pos_ch cpte_position_change || '.' || devise_cpte_liaison FROM ad_agc WHERE id_ag = NumAgc();
        SELECT INTO cpt_cv_pos_ch cpte_contreval_position_change || '.' || devise_cpte_liaison FROM ad_agc WHERE id_ag = NumAgc();

        INSERT INTO ad_ecriture(id_his,id_ag, date_comptable, libel_ecriture, id_jou, id_exo, ref_ecriture,type_operation,info_ecriture)
          VALUES ((SELECT currval('ad_his_id_his_seq')), NumAgc(), date(date_batch), libel_op, jou1, exo_courant, makeNumEcriture(jou1, exo_courant),type_op,NULL);

        -- Mouvement comptable au débit
        INSERT INTO ad_mouvement (id_ecriture,id_ag, compte, cpte_interne_cli, sens, montant, devise, date_valeur)
          VALUES ((SELECT currval('ad_ecriture_seq')), NumAgc(), num_cpte_debit, NULL, 'd', interet, code_dev_ref, getDateValeur(NULL,'d',date_batch));

        -- Mouvement comptable au crédit de la c/v du compte de liaison
        INSERT INTO ad_mouvement (id_ecriture,id_ag, compte, cpte_interne_cli, sens, montant, devise, date_valeur)
          VALUES ((SELECT currval('ad_ecriture_seq')), NumAgc(), cpt_cv_pos_ch, NULL, 'c', interet,code_dev_ref, getDateValeur(NULL,'c',date_batch));

        -- montant dans la devise du compte de liaison
        SELECT INTO interet_a_verser CalculeCV(interet, code_dev_ref, devise_cpte_liaison);

        -- Mouvement comptable au débit de la position de change du compte de liaison
        INSERT INTO ad_mouvement (id_ecriture, id_ag, compte, cpte_interne_cli, sens, montant, devise, date_valeur)
          VALUES ((SELECT currval('ad_ecriture_seq')), NumAgc(), cpt_pos_ch, NULL, 'd', interet_a_verser, devise_cpte_liaison, getDateValeur(NULL,'d',date_batch));

        -- Mouvement comptable au crédit du compte de liaison
        INSERT INTO ad_mouvement (id_ecriture,id_ag, compte, cpte_interne_cli, sens, montant, devise, date_valeur)
          VALUES ((SELECT currval('ad_ecriture_seq')), NumAgc(), cpte_liaison , NULL, 'c',interet_a_verser, devise_cpte_liaison, getDateValeur(NULL,'c',date_batch));

        -- Mise Ã  jour des soldes comptables
        UPDATE ad_cpt_comptable set solde = solde - interet WHERE id_ag = NumAgc() AND num_cpte_comptable = num_cpte_debit;
        UPDATE ad_cpt_comptable set solde = solde + interet WHERE id_ag = NumAgc() AND num_cpte_comptable = cpt_cv_pos_ch;
        UPDATE ad_cpt_comptable set solde = solde - interet_a_verser WHERE id_ag = NumAgc() AND num_cpte_comptable = cpt_pos_ch;
        UPDATE ad_cpt_comptable set solde = solde + interet_a_verser WHERE id_ag = NumAgc() AND num_cpte_comptable = cpte_liaison;

      END IF;--  FIN IF devise_cpte_liaison = code_dev_ref

      ----------- FIN DEBIT COMPTE CLIENT PAR CREDIT COMPTE DE LIAISON -----------------------

      ----------- DEBIT COMPTE DE LIAISON PAR CREDIT COMPTE COMPTABLE ASSOCIE AU PRODUIT D'EPARGNE DANS LE SECOND JOURNAL ------------------------

      IF devise_cpte_liaison = devise_cpte_int THEN  -----COMPTE AU CREDIT ET cpte_liaison SONT DE LA MEME DEVISE

        -- passage d'écriture
        INSERT INTO ad_ecriture(id_his,id_ag, date_comptable, libel_ecriture, id_jou, id_exo, ref_ecriture,type_operation,info_ecriture)
          VALUES ((SELECT currval('ad_his_id_his_seq')), NumAgc(), date(date_batch), libel_op, jou2, exo_courant, makeNumEcriture(jou2, exo_courant),type_op, NULL);

        -- mouvement comptable au débit du compte de liaison
        INSERT INTO ad_mouvement (id_ecriture, id_ag, compte, cpte_interne_cli, sens, montant, devise, date_valeur)
          VALUES ((SELECT currval('ad_ecriture_seq')), NumAgc(), cpte_liaison, NULL,'d', interet, devise_cpte_liaison, getDateValeur(NULL,'d',date_batch));

        -- mouvement comptable au crédit du compte associé au produit
        INSERT INTO ad_mouvement (id_ecriture, id_ag, compte, cpte_interne_cli, sens, montant, devise, date_valeur)
          VALUES ((SELECT currval('ad_ecriture_seq')),NumAgc(),num_cpte_credit,NULL,'c',interet, devise_cpte_int, getDateValeur(NULL,'c',date_batch));

        --Mise à jour des soldes
        UPDATE ad_cpt_comptable set solde = solde - interet WHERE id_ag = NumAgc() AND num_cpte_comptable = cpte_liaison;
        UPDATE ad_cpt_comptable set solde = solde + interet WHERE id_ag = NumAgc() AND num_cpte_comptable = num_cpte_credit;

      ELSE ---- compte au crédit et  cpte_liaison n'ont pas la même devise, une conversion s'impose
        IF devise_cpte_liaison = code_dev_ref THEN  -- cpte de liaison est en dévise de référence, uniquement le compte au crédit en devise étrangère

          SELECT INTO cpt_pos_ch cpte_position_change || '.' || devise_cpte_int FROM ad_agc WHERE id_ag = NumAgc();
          SELECT INTO cpt_cv_pos_ch cpte_contreval_position_change || '.' || devise_cpte_int FROM ad_agc WHERE id_ag = NumAgc();

          -- récupération du montant des intérêts dans la devise du compte de liaison (donc devise de référence )
          SELECT INTO interet_a_verser CalculeCV(interet, devise_cpte_int, devise_cpte_liaison);

          -- passage d'écriture
          INSERT INTO ad_ecriture(id_his, id_ag, date_comptable, libel_ecriture, id_jou, id_exo, ref_ecriture,type_operation,info_ecriture)
            VALUES ((SELECT currval('ad_his_id_his_seq')), NumAgc(), date(date_batch), libel_op, jou2, exo_courant, makeNumEcriture(jou2, exo_courant),type_op, NULL);

          -- mouvement comptable au débit du compte de liaison
          INSERT INTO ad_mouvement (id_ecriture, id_ag, compte, cpte_interne_cli, sens, montant, devise, date_valeur)
            VALUES ((SELECT currval('ad_ecriture_seq')), NumAgc(), cpte_liaison, NULL, 'd', interet_a_verser, devise_cpte_liaison, getDateValeur(NULL,'d',date_batch));

          --Mise à jour des soldes
          UPDATE ad_cpt_comptable set solde = solde - interet_a_verser WHERE id_ag=NumAgc() AND num_cpte_comptable = cpte_liaison;

          -- mouvement comptable au crédit de la  c/v
          INSERT INTO ad_mouvement (id_ecriture, id_ag, compte, cpte_interne_cli, sens, montant, devise, date_valeur)
            VALUES ((SELECT currval('ad_ecriture_seq')), NumAgc(), cpt_cv_pos_ch, NULL, 'c', interet_a_verser,  code_dev_ref, getDateValeur(NULL,'c',date_batch));

          UPDATE ad_cpt_comptable set solde = solde + interet_a_verser WHERE id_ag=NumAgc() AND num_cpte_comptable = cpt_cv_pos_ch;

          -- mouvement comptable au débit de la position de change du compte au crédit(compte associé au produit)
          INSERT INTO ad_mouvement (id_ecriture, id_ag, compte, cpte_interne_cli, sens, montant, devise, date_valeur)
            VALUES ((SELECT currval('ad_ecriture_seq')), NumAgc(), cpt_pos_ch, NULL, 'd', interet, devise_cpte_int, getDateValeur(NULL,'d',date_batch));
          UPDATE ad_cpt_comptable set solde = solde - interet WHERE id_ag=NumAgc() AND num_cpte_comptable = cpt_pos_ch;

          -- mouvement comptable au crédit du compte associé au produit
          INSERT INTO ad_mouvement (id_ecriture,id_ag, compte, cpte_interne_cli, sens, montant, devise, date_valeur)
            VALUES ((SELECT currval('ad_ecriture_seq')), NumAgc(), num_cpte_credit , NULL, 'c',interet,    devise_cpte_int, getDateValeur(NULL,'c',date_batch));

          UPDATE ad_cpt_comptable set solde = solde + interet WHERE id_ag=NumAgc() AND num_cpte_comptable = num_cpte_credit;

        END IF; -- FIN IF devise_cpte_liaison dans la devise de réference et seulement compte associé au produit en devise étrangère

        IF devise_cpte_int = code_dev_ref THEN -- si compte associé au produit(compte au crédit) a la devise de référence et que le compte de liaison est en dévise étrangère

          SELECT INTO cpt_pos_ch cpte_position_change || '.' || devise_cpte_liaison FROM ad_agc WHERE id_ag = NumAgc();
          SELECT INTO cpt_cv_pos_ch cpte_contreval_position_change || '.' || devise_cpte_liaison FROM ad_agc WHERE id_ag = NumAgc();

          --  passage d'écriture
          INSERT INTO ad_ecriture(id_his, id_ag, date_comptable, libel_ecriture, id_jou, id_exo, ref_ecriture,type_operation,info_ecriture)
            VALUES ((SELECT currval('ad_his_id_his_seq')), NumAgc(), date(date_batch), libel_op, jou2, exo_courant, makeNumEcriture(jou2, exo_courant),type_op, NULL);

          -- mouvement au crédit du compte associé au produit
          INSERT INTO ad_mouvement (id_ecriture, id_ag, compte, cpte_interne_cli, sens, montant, devise, date_valeur)
            VALUES ((SELECT currval('ad_ecriture_seq')), NumAgc(), num_cpte_credit , NULL, 'c', interet,    devise_cpte_int, getDateValeur(NULL,'c',date_batch));

          UPDATE ad_cpt_comptable set solde = solde + interet WHERE id_ag = NumAgc() AND num_cpte_comptable = num_cpte_credit;

          -- mouvement comptable au débit de la c/v du compte de liaison
          INSERT INTO ad_mouvement (id_ecriture,id_ag, compte, cpte_interne_cli, sens, montant, devise, date_valeur)
            VALUES ((SELECT currval('ad_ecriture_seq')), NumAgc(), cpt_cv_pos_ch , NULL, 'd',interet,  code_dev_ref, getDateValeur(NULL,'d',date_batch));

          UPDATE ad_cpt_comptable set solde = solde - interet WHERE id_ag=NumAgc() AND num_cpte_comptable = cpt_cv_pos_ch;-- car interet est déjà en devise de réference puisque c'est elle la devise compte associé au produit

          -- Récupération du montant dans la devise du compte de liaison
          SELECT INTO interet_a_verser CalculeCV(interet, devise_cpte_int, devise_cpte_liaison);

          -- mouvement comptable au débit du compte de liaison
          INSERT INTO ad_mouvement (id_ecriture, id_ag, compte, cpte_interne_cli, sens, montant, devise, date_valeur)
            VALUES ((SELECT currval('ad_ecriture_seq')), NumAgc(), cpte_liaison, NULL, 'd', interet_a_verser,   devise_cpte_liaison, getDateValeur(NULL,'d',date_batch));
          UPDATE ad_cpt_comptable set solde = solde - interet_a_verser WHERE id_ag=NumAgc() AND num_cpte_comptable = cpte_liaison;

          -- mouvement comptable au crédit de la position de change de la devise du compte de liaison
          INSERT INTO ad_mouvement (id_ecriture, id_ag, compte, cpte_interne_cli, sens, montant, devise, date_valeur)
            VALUES ((SELECT currval('ad_ecriture_seq')), NumAgc(),cpt_pos_ch, NULL, 'c', interet_a_verser,   devise_cpte_liaison,getDateValeur(NULL,'c',date_batch));

          UPDATE ad_cpt_comptable set solde = solde + interet_a_verser WHERE id_ag = NumAgc() AND num_cpte_comptable = cpt_pos_ch;

        END IF; -- FIN IF devise_cpte_int dans la devise de référence et seulement compte de liaison dans la devise étrangère


        IF devise_cpte_int <> devise_cpte_liaison AND devise_cpte_int <> code_dev_ref AND devise_cpte_liaison <> code_dev_ref THEN

          -- devise du compte de liaison et devise du compte associé au produit sont différents et aucune n'est égale à la devise de référence

          -- passage d'écriture
          INSERT INTO ad_ecriture(id_his, id_ag, date_comptable, libel_ecriture, id_jou, id_exo, ref_ecriture,type_operation,info_ecriture)
            VALUES ((SELECT currval('ad_his_id_his_seq')), NumAgc(), date(date_batch), libel_op, jou2,  exo_courant, makeNumEcriture(jou2, exo_courant),type_op,NULL);

          -- récupération du montant des intérêts dans la devise du compte de liaison (la valeur interet_a_verser represente ici celle de interet dans la devise du compte de liaison)
          SELECT INTO interet_a_verser CalculeCV(interet, devise_cpte_int, devise_cpte_liaison);

          -- mouvement comptable au débit du compte de liaison
          INSERT INTO ad_mouvement (id_ecriture, id_ag, compte, cpte_interne_cli, sens, montant, devise, date_valeur)
            VALUES ((SELECT currval('ad_ecriture_seq')),NumAgc(), cpte_liaison, NULL, 'd', interet_a_verser,    devise_cpte_liaison, getDateValeur(NULL,'d',date_batch));

          UPDATE ad_cpt_comptable set solde = solde - interet_a_verser WHERE id_ag = NumAgc() AND num_cpte_comptable = cpte_liaison;

          -- position de change de la devise du compte de liaison
          SELECT INTO cpt_pos_ch cpte_position_change || '.' || devise_cpte_liaison FROM ad_agc WHERE id_ag=NumAgc();

          -- mouvement comptable au crédit de la position de change de la devise du compte de liaison
          INSERT INTO ad_mouvement (id_ecriture, id_ag, compte, cpte_interne_cli, sens, montant, devise, date_valeur)
            VALUES ((SELECT currval('ad_ecriture_seq')), NumAgc(), cpt_pos_ch, NULL, 'c', interet_a_verser, devise_cpte_liaison, getDateValeur(NULL,'c',date_batch));

          UPDATE ad_cpt_comptable set solde = solde + interet_a_verser WHERE id_ag=NumAgc() AND num_cpte_comptable = cpt_pos_ch;

          -- Récupération du montant des intérêts dans la devise de référence(la valeur interet_a_verser represente ici celle de interet dans la devise de référence)
          SELECT INTO interet_a_verser CalculeCV(interet, devise_cpte_int, code_dev_ref);

          -- c/v de la devise du compte de liaison
          SELECT INTO cpt_cv_pos_ch cpte_contreval_position_change || '.' || devise_cpte_liaison FROM ad_agc;

          -- mouvement comptable au débit de la c/v de la devise du compte de liaison
          INSERT INTO ad_mouvement (id_ecriture, id_ag, compte, cpte_interne_cli, sens, montant, devise, date_valeur)
            VALUES ((SELECT currval('ad_ecriture_seq')), NumAgc(), cpt_cv_pos_ch,NULL, 'd', interet_a_verser, code_dev_ref, getDateValeur(NULL,'d',date_batch));

          UPDATE ad_cpt_comptable set solde = solde - interet_a_verser WHERE id_ag=NumAgc() AND num_cpte_comptable = cpt_cv_pos_ch;

          -- c/v de la devise du compte associé au produit
          SELECT INTO cpt_cv_pos_ch cpte_contreval_position_change || '.' || devise_cpte_int FROM ad_agc;

          -- mouvement comptable au crédit de la c/v du compte associé au produit
          INSERT INTO ad_mouvement (id_ecriture, id_ag, compte, cpte_interne_cli, sens, montant, devise, date_valeur)
            VALUES ((SELECT currval('ad_ecriture_seq')), NumAgc(), cpt_cv_pos_ch, NULL, 'c', interet_a_verser, code_dev_ref, getDateValeur(NULL,'c',date_batch));

          UPDATE ad_cpt_comptable set solde = solde + interet_a_verser
            WHERE id_ag = NumAgc() AND num_cpte_comptable = cpt_cv_pos_ch;

          -- mouvement comptable au crédit du compte associé au produit
          INSERT INTO ad_mouvement (id_ecriture, id_ag, compte, cpte_interne_cli, sens, montant, devise, date_valeur)
            VALUES ((SELECT currval('ad_ecriture_seq')),NumAgc(), num_cpte_credit, NULL, 'c', interet, devise_cpte_int, getDateValeur(NULL,'c',date_batch));

          UPDATE ad_cpt_comptable set solde = solde + interet WHERE id_ag=NumAgc() AND num_cpte_comptable = num_cpte_credit;

          -- position de change de la devise du compte associé au produit
          SELECT INTO cpt_pos_ch cpte_position_change || '.' || devise_cpte_int FROM ad_agc WHERE id_ag=NumAgc();

          -- mouvement comptable au débit de la position de change de la devise du compte associé au produit
          INSERT INTO ad_mouvement (id_ecriture, id_ag, compte, cpte_interne_cli, sens, montant, devise, date_valeur)
           VALUES ((SELECT currval('ad_ecriture_seq')), NumAgc(), cpt_pos_ch, NULL, 'd', interet, devise_cpte_int, getDateValeur(NULL,'d',date_batch));

          UPDATE ad_cpt_comptable set solde = solde - interet WHERE id_ag = NumAgc() AND num_cpte_comptable = cpt_pos_ch;

        END IF; -- FIN IF devise_cpte_int != code_dev_ref AND devise_cpte_liaison != code_dev_ref

      END IF;  -- FIN  IF devise_cpte_int = devise_cpte_liaison

      ------- FIN DEBIT COMPTE DE LIAISON PAR CREDIT COMPTE COMPTABLE ASSOCIE AU PRODUIT D'EPARGNE DANS LE SECOND JOURNAL ------------------------

  ELSE
      -- AU MOINS UN DES COMPTES N''EST PAS PRINCIPAL OU LES DEUX SONT PRINCIPAUX DU MEME JOURNAL: PAS BESOIN DONC DE COMPTE DE LIAISON
      IF jou1 IS NULL AND jou2 IS NOT NULL THEN
        id_journal := jou2;
      END IF;

      IF jou1 IS NOT NULL AND jou2 IS NULL THEN
            id_journal := jou1;
      END IF;

      IF jou1 IS NOT NULL AND jou2 IS NOT NULL AND jou1 = jou2 THEN
        id_journal := jou1;
      END IF;

      IF jou1 IS NULL AND jou2 IS NULL THEN
        id_journal := 1; -- Ecrire donc dans le journal principal
      END IF;

      IF code_dev_ref = devise_cpte_int  THEN
        -- Ecriture comptable
        INSERT INTO ad_ecriture (id_his, id_ag, date_comptable, libel_ecriture, id_jou, id_exo, ref_ecriture,type_operation,info_ecriture)
          VALUES ((SELECT currval('ad_his_id_his_seq')), NumAgc(), date_batch,libel_op, id_journal, exo_courant, makeNumEcriture(1, exo_courant),type_op, id_cpte_source);

        -- Mouvement comptable au débit(compte des intérêt)
        INSERT INTO ad_mouvement(id_ecriture, id_ag, compte, cpte_interne_cli, sens, montant, date_valeur, devise)
          VALUES ((SELECT currval('ad_ecriture_seq')), NumAgc(),num_cpte_debit , NULL, 'd', interet, getDateValeur(NULL,'c',date_batch), devise_cpte_int);

        -- Mouvement comptable au crédit(comptes associé aux produits d'epargne de destination des intérêt)
        INSERT INTO ad_mouvement(id_ecriture, id_ag, compte, cpte_interne_cli, sens, montant, date_valeur, devise)
          VALUES ((SELECT currval('ad_ecriture_seq')), NumAgc(),  num_cpte_credit, NULL, 'c', interet,getDateValeur(NULL,'c',date_batch), devise_cpte_int);

        -- mise à jour des soldes  comptable
        UPDATE ad_cpt_comptable SET solde = solde - interet WHERE num_cpte_comptable = num_cpte_debit;
        UPDATE ad_cpt_comptable SET solde = solde + interet WHERE num_cpte_comptable = num_cpte_credit;

      ELSE -- cas de devise de référence différent de la devise du compte des interets a calculer ------------------------------

        -- il faut faire une change du montant des intérêts dans la devise de référence --
        SELECT INTO interet_a_verser  CalculeCV(interet, devise_cpte_int, code_dev_ref);
        RAISE NOTICE 'interet_a_verser=%  interet=%  devise_cpte_int=%  code_dev_ref=%',interet_a_verser, interet,devise_cpte_int, code_dev_ref ;

        --Récupérer son compte de position de change
        SELECT INTO cpt_pos_ch cpte_position_change || '.' || devise_cpte_int FROM ad_agc WHERE id_ag=NumAgc();

        --Récupérer son compte de c/v

        RAISE NOTICE 'devise_cpte_int = %',devise_cpte_int;

        SELECT INTO cpt_cv_pos_ch cpte_contreval_position_change || '.' || devise_cpte_int FROM ad_agc WHERE id_ag=NumAgc();
          RAISE NOTICE 'cpt_pos_ch = % et cpt_cv_pos_ch = %',cpt_pos_ch, cpt_cv_pos_ch;

        -- Ecriture comptable
        INSERT INTO ad_ecriture (id_his, id_ag, date_comptable, libel_ecriture, id_jou, id_exo, ref_ecriture,type_operation,info_ecriture)
          VALUES ((SELECT currval('ad_his_id_his_seq')), NumAgc(), date_batch, libel_op, id_journal, exo_courant, makeNumEcriture(1, exo_courant),type_op, id_cpte_source);

        -- Mouvement comptable au débit(compte des intérêt)
        INSERT INTO ad_mouvement(id_ecriture, id_ag, compte, cpte_interne_cli, sens, montant, date_valeur, devise)
          VALUES ((SELECT currval('ad_ecriture_seq')), NumAgc(),num_cpte_debit , NULL, 'd', interet_a_verser, getDateValeur(NULL,'d',date_batch),code_dev_ref);

        -- Mouvement comptable au crédit(comptes de position de change)
        INSERT INTO ad_mouvement(id_ecriture, id_ag, compte, cpte_interne_cli, sens, montant, date_valeur, devise)
          VALUES ((SELECT currval('ad_ecriture_seq')), NumAgc(), cpt_cv_pos_ch , NULL, 'c', interet_a_verser, getDateValeur(NULL,'c',date_batch), code_dev_ref);

        -- Mise à jour des soldes des comptes comptables
        UPDATE ad_cpt_comptable set solde = solde - interet_a_verser
          WHERE id_ag=NumAgc() AND num_cpte_comptable = num_cpte_debit;
        UPDATE ad_cpt_comptable set solde = solde + interet_a_verser
          WHERE id_ag=NumAgc() AND num_cpte_comptable = cpt_cv_pos_ch;

        -- Ecriture comptable contre valeur
        INSERT INTO ad_ecriture  (id_his, id_ag, date_comptable, libel_ecriture, id_jou, id_exo, ref_ecriture,type_operation,info_ecriture)
          VALUES ((SELECT currval('ad_his_id_his_seq')), NumAgc(), date(date_batch), libel_op, id_journal, exo_courant, makeNumEcriture(1, exo_courant),type_op, id_cpte_source);

        -- Mouvement comptable au débit(compte position de change)
        INSERT INTO ad_mouvement(id_ecriture, id_ag, compte, cpte_interne_cli, sens, montant, date_valeur, devise)
          VALUES ((SELECT currval('ad_ecriture_seq')), NumAgc(),cpt_pos_ch, NULL, 'd', interet, getDateValeur(NULL,'d',date_batch), devise_cpte_int);

        -- Mouvement comptable au crédit(comptes associé aux produits d'epargne de destination des intérêt)
        INSERT INTO ad_mouvement(id_ecriture, id_ag, compte, cpte_interne_cli, sens, montant, date_valeur, devise)
          VALUES ((SELECT currval('ad_ecriture_seq')), NumAgc(),  num_cpte_credit, NULL, 'c', interet, getDateValeur(NULL,'c',date_batch), devise_cpte_int);

        -- mise à jour des soldes  comptable
        UPDATE ad_cpt_comptable SET solde = solde - interet WHERE num_cpte_comptable = cpt_pos_ch;
        UPDATE ad_cpt_comptable SET solde = solde + interet WHERE num_cpte_comptable = num_cpte_credit;

      END IF;-- if code_dev_ref = devise_cpte_int
    END IF;-- if du test de journaux principaux ou pas

   RETURN 0;
   END;
   $BODY$
  LANGUAGE plpgsql VOLATILE
  COST 100;