------------ TICKET #365 -----------------------------------------

CREATE OR REPLACE FUNCTION script_365() RETURNS INT AS
$$
DECLARE
output_result INTEGER = 1;
tableliste_ident INTEGER = (select ident from tableliste where nomc like 'adsys_produit_epargne' order by ident desc limit 1);

BEGIN

	RAISE NOTICE 'START';

	-- ADD COLUMNS
	-- TABLE adsys_produit_epargne
	
	IF NOT EXISTS(SELECT column_name FROM information_schema.columns WHERE table_name='adsys_produit_epargne' and column_name='calcul_pen_int') THEN
		ALTER TABLE ONLY adsys_produit_epargne ADD COLUMN calcul_pen_int int DEFAULT (0);

		RAISE NOTICE 'Column calcul_pen_int added in table adsys_produit_epargne';
		output_result := 2;
	END IF;

	
	-- Add in table d_tableliste

	IF NOT EXISTS(SELECT * FROM tableliste WHERE nomc = 'adsys_calcul_pen_int') THEN
		INSERT INTO tableliste(ident, nomc, noml, is_table) VALUES ((select max(ident) from tableliste)+1, 'adsys_calcul_pen_int', maketraductionlangsyst('Pénalités à la rupture calculées sur'), false);
		
		RAISE NOTICE 'Insertion table adsys_calcul_pen_int de la table tableliste effectuée';
		output_result := 2;
	END IF;
	
	IF NOT EXISTS(SELECT * FROM d_tableliste WHERE tablen=(select ident from tableliste where nomc like 'adsys_calcul_pen_int' order by ident desc limit 1) AND nchmpc = 'calcul_pen_int') THEN
		INSERT INTO d_tableliste(ident, tablen, nchmpc, nchmpl, isreq, ref_field, type, onslct, ispkey, traduit) VALUES ((select max(ident) from d_tableliste)+1, (select ident from tableliste where nomc like 'adsys_calcul_pen_int' order by ident desc limit 1), 'calcul_pen_int', maketraductionlangsyst('Pénalités à la rupture calculées sur'), true, NULL, 'int', NULL, true, false);

		RAISE NOTICE 'Insertion calcul_pen_int de la table d_tableliste effectuée';
		output_result := 2;
	END IF;
	
	IF NOT EXISTS(SELECT * FROM d_tableliste WHERE tablen=tableliste_ident AND nchmpc = 'calcul_pen_int') THEN
		INSERT INTO d_tableliste(ident, tablen, nchmpc, nchmpl, isreq, ref_field, type, onslct, ispkey, traduit) VALUES ((select max(ident) from d_tableliste)+1, tableliste_ident, 'calcul_pen_int', maketraductionlangsyst('Pénalités à la rupture calculées sur'), false, (SELECT ident FROM d_tableliste WHERE tablen=(select ident from tableliste where nomc like 'adsys_calcul_pen_int' order by ident desc limit 1) AND nchmpc = 'calcul_pen_int'), 'int', NULL, NULL, false);

		RAISE NOTICE 'Insertion calcul_pen_int de la table d_tableliste effectuée';
		output_result := 2;
	END IF;
	
	--set default capital pour les comptes DAT
	UPDATE adsys_produit_epargne set calcul_pen_int = 1 where id > 5;
	
	RETURN output_result;
	
END;
$$
LANGUAGE plpgsql;

SELECT script_365();
DROP FUNCTION script_365();

-----------------------------------------------------------------------------------------------------------

------------ TICKET #499 -----------------------------------------

CREATE OR REPLACE FUNCTION patch_ticket_499() RETURNS integer AS
$BODY$
DECLARE
output_result INTEGER;

BEGIN
RAISE INFO 'CREATION ECRANS' ;
----------------------------------CREATION ECRANS
-- ecran  menu
IF NOT EXISTS (SELECT * FROM menus WHERE nom_menu = 'Lus') THEN
INSERT INTO menus(nom_menu, libel_menu, nom_pere, pos_hierarch, ordre, is_menu,fonction,is_cliquable)
VALUES ('Lus', maketraductionlangsyst('Liste des utilisateurs'), 'Gus',4,5,'t',278,'f');
END IF;

-- ecran  menu
IF NOT EXISTS (SELECT * FROM menus WHERE nom_menu = 'Lus-1') THEN
INSERT INTO menus(nom_menu, libel_menu, nom_pere, pos_hierarch, ordre, is_menu,is_cliquable)
VALUES ('Lus-1', maketraductionlangsyst('Liste des utilisateurs'), 'Lus-1',5,1,'f','f');
END IF;

-- Ecrans 
IF NOT EXISTS (SELECT * FROM ecrans WHERE nom_ecran = 'Lus-1') THEN
INSERT INTO ecrans(nom_ecran, fichier, nom_menu, fonction)
VALUES ('Lus-1','modules/parametrage/utilisateurs.php','Lus',278);
END IF;

--fonction (278 : Afficher les utilisateurs) autorisée pour le profil admin par défaut
IF NOT EXISTS (SELECT * from adsys_profils_axs where fonction = 278 AND profil = 1) THEN
INSERT into adsys_profils_axs (profil,fonction)
VALUES(1,278);
END IF;

return 1;

END;
$BODY$
LANGUAGE plpgsql VOLATILE COST 100;
ALTER FUNCTION patch_ticket_499()
OWNER TO adbanking;

SELECT patch_ticket_499();
DROP FUNCTION patch_ticket_499();

---------------------------------------------------------------------------------------------------------------------------------------------------------------
-------------------------------------------------FONCTION :CREATION ECRANS
---------------------------------------------------------------------------------------------------------------------------------------------------------------

-- Table: dwh_log_export_fichiers

DROP TABLE IF EXISTS dwh_log_export_fichiers;

CREATE TABLE dwh_log_export_fichiers
(
  date_transfert timestamp without time zone,
  nom_table character varying(100),
  nom_fichier_cible character varying(200),
  mode_transfert character varying(100),
  max_id integer,
  type_transfert character varying(100),
  nb_lignes_source integer,
  nb_lignes_cible integer,
  statut_ftp character(2),
  id_ligne bigserial NOT NULL,
  CONSTRAINT pk_dwh_log_export_fichers PRIMARY KEY (id_ligne)
)
WITH (
  OIDS=FALSE
);

-------------------------------------------------

-- Function: last_post(text, character)

DROP FUNCTION IF EXISTS last_post(text, character);

CREATE OR REPLACE FUNCTION last_post(text, character)
  RETURNS integer AS
$BODY$  
select length($1)- length(regexp_replace($1, E'.*\\' || $2,''));  
$BODY$
  LANGUAGE sql VOLATILE
  COST 100;

-------------------------------------------------

-- Function: split_compte(character varying)

DROP FUNCTION IF EXISTS split_compte(character varying);

CREATE OR REPLACE FUNCTION split_compte(IN compte character varying)
  RETURNS SETOF text AS
$BODY$

  begin
  return query 
  select regexp_split_to_table(replace($1,'|',' '), E'\\s+') as code_compte;
  end;
  $BODY$
  LANGUAGE plpgsql VOLATILE
  COST 100
  ROWS 1000;
  

---------- Ticket #574 -------------------------

CREATE OR REPLACE FUNCTION patch_script_574() RETURNS INT AS
$$
DECLARE
output_result INTEGER = 1;
tableliste_ident INTEGER = 0;

BEGIN

	RAISE NOTICE 'START';

	IF NOT EXISTS(SELECT * FROM information_schema.tables WHERE table_name = 'adsys_detail_objet') THEN
		CREATE TABLE adsys_detail_objet
		(
		  id serial NOT NULL,
		  id_ag integer NOT NULL,
		  libel text,
		  id_obj integer NOT NULL,
		  CONSTRAINT adsys_detail_objet_pkey PRIMARY KEY (id, id_ag),
		  CONSTRAINT adsys_detail_objet_ukey UNIQUE (id, id_obj, id_ag),
		  CONSTRAINT adsys_detail_objet_id_obj FOREIGN KEY (id_obj,id_ag)
		  REFERENCES adsys_objets_credits (id,id_ag) MATCH SIMPLE
		  ON UPDATE NO ACTION ON DELETE CASCADE
		)
		WITH (
		  OIDS=FALSE
		);

		output_result := 2;
	END IF;

	IF NOT EXISTS(SELECT * FROM tableliste WHERE nomc = 'adsys_detail_objet') THEN
		INSERT INTO tableliste(ident, nomc, noml, is_table) VALUES ((select max(ident) from tableliste)+1, 'adsys_detail_objet', maketraductionlangsyst('Table Détails demande de crédit'), true);

		RAISE NOTICE 'Insertion table adsys_detail_objet de la table tableliste effectuée';
		output_result := 2;
	END IF;

	tableliste_ident := (select ident from tableliste where nomc like 'adsys_detail_objet' order by ident desc limit 1);

	IF NOT EXISTS(SELECT * FROM d_tableliste WHERE tablen=tableliste_ident AND nchmpc = 'id') THEN
		INSERT INTO d_tableliste(ident, tablen, nchmpc, nchmpl, isreq, ref_field, type, onslct, ispkey, traduit) VALUES ((select max(ident) from d_tableliste)+1, tableliste_ident, 'id', maketraductionlangsyst('Identifiant table Détails demande de crédit'), true, NULL, 'int', false, true, false);

		RAISE NOTICE 'Insertion id de la table d_tableliste effectuée';
		output_result := 2;
	END IF;

	IF NOT EXISTS(SELECT * FROM d_tableliste WHERE tablen=tableliste_ident AND nchmpc = 'libel') THEN
		INSERT INTO d_tableliste(ident, tablen, nchmpc, nchmpl, isreq, ref_field, type, onslct, ispkey, traduit) VALUES ((select max(ident) from d_tableliste)+1, tableliste_ident, 'libel', maketraductionlangsyst('libel'), true, NULL, 'txt', false, false, false);

		RAISE NOTICE 'Insertion libel de la table d_tableliste effectuée';
		output_result := 2;
	END IF;

	IF NOT EXISTS(SELECT * FROM d_tableliste WHERE tablen=tableliste_ident AND nchmpc = 'id_obj') THEN
		INSERT INTO d_tableliste(ident, tablen, nchmpc, nchmpl, isreq, ref_field, type, onslct, ispkey, traduit) VALUES ((select max(ident) from d_tableliste)+1, tableliste_ident, 'id_obj', maketraductionlangsyst('Objet de demande'), true, NULL, 'lsb', true, false, false);

		RAISE NOTICE 'Insertion id_obj de la table d_tableliste effectuée';
		output_result := 2;
	END IF;

	RAISE NOTICE 'END';

	RETURN output_result;

END;
$$
LANGUAGE plpgsql;

SELECT patch_script_574();
DROP FUNCTION patch_script_574();

---------- Fin du Ticket #574 -------------------------

---------- Ticket #574 : reprise des dossiers existant pour les details objets  -------------------------
CREATE OR REPLACE FUNCTION script_reprise_dossiers_574() RETURNS INT AS
$$
DECLARE

	cur_dcr CURSOR FOR SELECT id_doss, obj_dem, detail_obj_dem FROM ad_dcr WHERE obj_dem IS NOT NULL AND detail_obj_dem IS NOT NULL;
	ligne_dcr RECORD;
	var_dcr_obj INTEGER ;
	var_dcr_detail_obj INTEGER ;

	cur_dcr_grp_sol CURSOR FOR SELECT id, obj_dem, detail_obj_dem  FROM ad_dcr_grp_sol WHERE obj_dem IS NOT NULL AND detail_obj_dem IS NOT NULL;
	ligne_dcr_grp_sol RECORD;

	var_count_detail_obj INTEGER DEFAULT 0;
	var_id_detail_obj INTEGER DEFAULT 0;

	output_result INTEGER = 1;

BEGIN
	RAISE NOTICE 'DEBUT traitement';
	RAISE NOTICE 'DEBUT reprise des details objet demande dans ad_dcr';

	-- Traitement ad_dcr
	OPEN cur_dcr; -- Open cursor cur_dcr
	FETCH cur_dcr INTO ligne_dcr;

	WHILE FOUND LOOP -- Loop in resultset

		SELECT INTO var_count_detail_obj count(*)
		FROM adsys_detail_objet
		WHERE id_obj = ligne_dcr.obj_dem
		AND libel = ligne_dcr.detail_obj_dem;

		IF(var_count_detail_obj > 0) THEN -- Detail objet existant, on insert seulement la reference.
			-- recup l'id
			SELECT INTO var_id_detail_obj id
			FROM adsys_detail_objet
			WHERE id_obj = ligne_dcr.obj_dem
			AND libel = ligne_dcr.detail_obj_dem;

		ELSE -- Nouveau detail objet, a inserer
			INSERT INTO adsys_detail_objet(libel, id_obj, id_ag)
			VALUES (ligne_dcr.detail_obj_dem, ligne_dcr.obj_dem, numagc());

			SELECT INTO var_id_detail_obj id
			FROM adsys_detail_objet
			WHERE id_obj = ligne_dcr.obj_dem
			AND libel = ligne_dcr.detail_obj_dem;

		END IF;

		-- m-a-j dossier
		UPDATE ad_dcr SET detail_obj_dem = var_id_detail_obj WHERE  id_doss = ligne_dcr.id_doss;

		FETCH cur_dcr INTO ligne_dcr; -- GET next element
	END LOOP;

	CLOSE cur_dcr; -- Close cursor cur_dcr

	RAISE NOTICE 'FIN reprise des details objet demande dans ad_dcr';


	RAISE NOTICE 'DEBUT reprise des details objet demande dans ad_dcr_grp_sol';

	-- Traitement ad_dcr_grp_sol
	OPEN cur_dcr_grp_sol; -- Open cursor cur_dcr_grp_sol
	FETCH cur_dcr_grp_sol INTO ligne_dcr_grp_sol;

	WHILE FOUND LOOP -- Loop in resultset

		SELECT INTO var_count_detail_obj count(*)
		FROM adsys_detail_objet
		WHERE id_obj = ligne_dcr_grp_sol.obj_dem
		AND libel = ligne_dcr_grp_sol.detail_obj_dem;

		IF(var_count_detail_obj > 0) THEN -- Detail objet existant, on insert seulement la reference.
			-- recup l'id
			SELECT INTO var_id_detail_obj id
			FROM adsys_detail_objet
			WHERE id_obj = ligne_dcr_grp_sol.obj_dem
			AND libel = ligne_dcr_grp_sol.detail_obj_dem;

		ELSE -- Nouveau detail objet, a inserer
			INSERT INTO adsys_detail_objet(libel, id_obj, id_ag)
			VALUES (ligne_dcr_grp_sol.detail_obj_dem, ligne_dcr_grp_sol.obj_dem, numagc());

			SELECT INTO var_id_detail_obj id
			FROM adsys_detail_objet
			WHERE id_obj = ligne_dcr_grp_sol.obj_dem
			AND libel = ligne_dcr_grp_sol.detail_obj_dem;
		END IF;

		-- m-a-j dossier
		UPDATE ad_dcr_grp_sol SET detail_obj_dem = var_id_detail_obj WHERE  id = ligne_dcr_grp_sol.id;

		FETCH cur_dcr_grp_sol INTO ligne_dcr_grp_sol; -- GET next element
	END LOOP;

	CLOSE cur_dcr_grp_sol; -- Close cursor cur_dcr

	RAISE NOTICE 'FIN reprise des details objet demande dans ad_dcr_grp_sol';

	RAISE NOTICE 'FIN traitement';
	RETURN output_result;

END;
$$
LANGUAGE plpgsql;

SELECT script_reprise_dossiers_574();
DROP FUNCTION script_reprise_dossiers_574();

---------- Fin scripte reprise Ticket #574 -------------------------

----------------------------------------- DEBUT : PROJET CHEQUE INTERNE -----------------------------------------------------------

CREATE OR REPLACE FUNCTION init_projet_cheque_interne() RETURNS INT AS
$$
DECLARE
output_result INTEGER = 1;
tableliste_ident INTEGER = (select ident from tableliste where nomc like 'adsys_produit_epargne' order by ident desc limit 1);

BEGIN

	RAISE NOTICE 'START';

	-- ADD COLUMNS
	-- TABLE adsys_produit_epargne
	IF NOT EXISTS(SELECT column_name FROM information_schema.columns WHERE table_name = 'adsys_produit_epargne' AND column_name = 'cpte_cpta_prod_ep_commission') THEN
		ALTER TABLE adsys_produit_epargne ADD COLUMN cpte_cpta_prod_ep_commission text;

		RAISE NOTICE 'Column cpte_cpta_prod_ep_commission added in table adsys_produit_epargne';
		output_result := 2;
	END IF;

	IF NOT EXISTS(SELECT column_name FROM information_schema.columns WHERE table_name='adsys_produit_epargne' and column_name='mnt_commission') THEN
		ALTER TABLE ONLY adsys_produit_epargne ADD COLUMN mnt_commission numeric(30,6) DEFAULT 0;

		RAISE NOTICE 'Column mnt_commission added in table adsys_produit_epargne';
		output_result := 2;
	END IF;

	-- Add in table d_tableliste
	IF NOT EXISTS(SELECT * FROM d_tableliste WHERE tablen=tableliste_ident AND nchmpc = 'cpte_cpta_prod_ep_commission') THEN
		INSERT INTO d_tableliste(ident, tablen, nchmpc, nchmpl, isreq, ref_field, type, onslct, ispkey, traduit) VALUES ((select max(ident) from d_tableliste)+1, tableliste_ident, 'cpte_cpta_prod_ep_commission', maketraductionlangsyst('Compte comptable des commissions'), false, 1400, 'txt', NULL, NULL, false);

		RAISE NOTICE 'Insertion cpte_cpta_prod_ep_commission de la table d_tableliste effectuée';
		output_result := 2;
	END IF;

	IF NOT EXISTS(SELECT * FROM d_tableliste WHERE tablen=tableliste_ident AND nchmpc = 'mnt_commission') THEN
		INSERT INTO d_tableliste(ident, tablen, nchmpc, nchmpl, isreq, ref_field, type, onslct, ispkey, traduit) VALUES ((select max(ident) from d_tableliste)+1, tableliste_ident, 'mnt_commission', maketraductionlangsyst('Montant des commissions'), false, NULL, 'mnt', NULL, NULL, false);

		RAISE NOTICE 'Insertion mnt_commission de la table d_tableliste effectuée';
		output_result := 2;
	END IF;

	-- Create produit épargne : Chèque certifié
	IF NOT EXISTS(SELECT * FROM adsys_produit_epargne WHERE classe_comptable = 8 AND id_ag = numagc()) THEN

		INSERT INTO adsys_produit_epargne (id, id_ag, libel, sens, service_financier, nbre_occurrences, frais_tenue_prorata, retrait_unique, depot_unique, certif, dat_prolongeable, classe_comptable) VALUES ((SELECT MAX(id) FROM adsys_produit_epargne)+1, numagc(), 'Chèque certifié','c', true, 1, false, false, false, false, false, 8);

		RAISE NOTICE 'Insertion produit épargne "Chèque certifié" dans la table adsys_produit_epargne effectuée';
		output_result := 2;
	END IF;

	-- CREATE TABLE ad_cheque_certifie
	IF NOT EXISTS(SELECT * FROM information_schema.tables WHERE table_name = 'ad_cheque_certifie') THEN
		CREATE TABLE ad_cheque_certifie
		(
			id serial NOT NULL,
			id_ag integer NOT NULL,
			num_cheque integer NOT NULL,
			date_cheque date NOT NULL,
			montant numeric(30,6) NOT NULL,
			id_benef integer NOT NULL,
			num_cpte_cli integer NOT NULL,
			num_cpte_cheque integer NULL,
			etat_cheque integer NOT NULL,
			comments text,
			date_traitement timestamp without time zone NULL,
			date_certifie timestamp without time zone NOT NULL DEFAULT now(),
			CONSTRAINT ad_cheque_certifie_pkey PRIMARY KEY (id, id_ag)
		)
		WITH (
		  OIDS=FALSE
		);

		RAISE NOTICE 'Table ad_cheque_certifie created';
		output_result := 2;
	END IF;

	-- CREATE TABLE ad_cheques_compensation

	IF EXISTS (SELECT * FROM information_schema.tables WHERE table_name = 'ad_cheques_compensation') THEN
			DROP TABLE 	ad_cheques_compensation;
	END IF;

	CREATE TABLE ad_cheques_compensation
	(
		id serial NOT NULL,
		id_ag integer NOT NULL,
		num_cpte_cli integer NOT NULL,
		num_cheque integer NOT NULL,
		date_cheque date NOT NULL,
		montant numeric(30,6) NOT NULL,
		etab_benef text,
		is_certifie boolean DEFAULT false,
		etat_cheque integer NOT NULL,
		date_etat date NOT NULL,
		nom_benef text,
		comments text,
		date_crea timestamp without time zone NOT NULL DEFAULT now(),
		CONSTRAINT ad_cheques_compensation_pkey PRIMARY KEY (id, id_ag)
	)
	WITH (
		OIDS=FALSE
	);

	RAISE NOTICE 'Table ad_cheques_compensation created';
	output_result := 2;


	-- CREATE TABLE ad_cheques_compensation_hist

	IF EXISTS(SELECT * FROM information_schema.tables WHERE table_name = 'ad_cheques_compensation_hist') THEN
		DROP TABLE 	ad_cheques_compensation_hist;
	END IF;


	CREATE TABLE ad_cheques_compensation_hist
	(
		id serial NOT NULL,
		date_action timestamp without time zone DEFAULT now(),
		id_cheques_compensation integer NOT NULL,
		etat_cheque integer,
		id_ag integer NOT NULL,
		CONSTRAINT ad_cheques_compensation_hist_pkey PRIMARY KEY (id, id_ag)
	)
	WITH (
		OIDS=FALSE
	);

	RAISE NOTICE 'Table ad_cheques_compensation_hist created';
	output_result := 2;


	-- Function: trig_insert_ad_cheques_compensation_hist()

	-- DROP FUNCTION trig_insert_ad_cheques_compensation_hist();

	CREATE OR REPLACE FUNCTION trig_insert_ad_cheques_compensation_hist() RETURNS trigger AS $BODY$
	  BEGIN
		INSERT INTO ad_cheques_compensation_hist (date_action, id_cheques_compensation, etat_cheque, id_ag)
		VALUES (NOW(), OLD.id, OLD.etat_cheque, OLD.id_ag);
		RETURN NEW;
	  END;
		$BODY$
	  LANGUAGE plpgsql VOLATILE COST 100;

	-- Trigger: trig_before_update_ad_cheques_compensation on ad_cheques_compensation

	DROP TRIGGER IF EXISTS trig_before_update_ad_cheques_compensation ON ad_cheques_compensation;

	CREATE TRIGGER trig_before_update_ad_cheques_compensation BEFORE UPDATE ON ad_cheques_compensation
	FOR EACH ROW EXECUTE PROCEDURE trig_insert_ad_cheques_compensation_hist();

	-- Create fonction systeme
	IF NOT EXISTS(SELECT * FROM adsys_fonction WHERE code_fonction=162 AND id_ag = numagc()) THEN

		INSERT INTO adsys_fonction(code_fonction, libelle, id_ag) VALUES (162, 'Gestion des chèques certifiés', numagc());

		RAISE NOTICE 'Insertion fonction systeme 162 dans la table adsys_fonction effectuée';
		output_result := 2;
	END IF;

	IF NOT EXISTS(SELECT * FROM adsys_fonction WHERE code_fonction=163 AND id_ag = numagc()) THEN

		INSERT INTO adsys_fonction(code_fonction, libelle, id_ag) VALUES (163, 'Traitement des chèques reçus en compensation', numagc());

		RAISE NOTICE 'Insertion fonction systeme 163 dans la table adsys_fonction effectuée';
		output_result := 2;
	END IF;

	IF NOT EXISTS(SELECT * FROM adsys_fonction WHERE code_fonction=164 AND id_ag = numagc()) THEN

		INSERT INTO adsys_fonction(code_fonction, libelle, id_ag) VALUES (164, 'Enregistrement des chèques', numagc());

		RAISE NOTICE 'Insertion fonction systeme 164 dans la table adsys_fonction effectuée';
		output_result := 2;
	END IF;

	IF NOT EXISTS(SELECT * FROM adsys_fonction WHERE code_fonction=165 AND id_ag = numagc()) THEN

		INSERT INTO adsys_fonction(code_fonction, libelle, id_ag) VALUES (165, 'Traitement des chèques certifiés', numagc());

		RAISE NOTICE 'Insertion fonction systeme 165 dans la table adsys_fonction effectuée';
		output_result := 2;
	END IF;

	IF NOT EXISTS(SELECT * FROM adsys_fonction WHERE code_fonction=166 AND id_ag = numagc()) THEN

		INSERT INTO adsys_fonction(code_fonction, libelle, id_ag) VALUES (166, 'Traitement des chèques ordinaires (non certifiés)', numagc());

		RAISE NOTICE 'Insertion fonction systeme 166 dans la table adsys_fonction effectuée';
		output_result := 2;
	END IF;
	
	-- Création opérations financière
	-- Certification chèque
	IF NOT EXISTS(SELECT * FROM ad_cpt_ope WHERE type_operation = 530 AND categorie_ope = 1 AND id_ag = numagc()) THEN
		INSERT INTO ad_cpt_ope(type_operation, categorie_ope, id_ag, libel_ope) VALUES (530, 1, numagc(), maketraductionlangsyst('Certification chèque'));

		RAISE NOTICE 'Insertion type_operation 530 dans la table ad_cpt_ope effectuée';
		output_result := 2;
	END IF;

	IF NOT EXISTS(SELECT * FROM ad_cpt_ope_cptes WHERE type_operation = 530 AND sens = 'd' AND categorie_cpte = 1 AND id_ag = numagc()) THEN
		INSERT INTO ad_cpt_ope_cptes(type_operation, num_cpte, sens, categorie_cpte, id_ag) VALUES (530, NULL, 'd', 1, numagc());

		RAISE NOTICE 'Insertion type_operation 530 sens ''d'' dans la table ad_cpt_ope_cptes effectuée';
		output_result := 2;
	END IF;

	IF NOT EXISTS(SELECT * FROM ad_cpt_ope_cptes WHERE type_operation = 530 AND sens = 'c' AND categorie_cpte = 22 AND id_ag = numagc()) THEN
		INSERT INTO ad_cpt_ope_cptes(type_operation, num_cpte, sens, categorie_cpte, id_ag) VALUES (530, NULL, 'c', 22, numagc());

		RAISE NOTICE 'Insertion type_operation 530 sens ''c'' dans la table ad_cpt_ope_cptes effectuée';
		output_result := 2;
	END IF;

	-- Commissions sur certification chèque
	IF NOT EXISTS(SELECT * FROM ad_cpt_ope WHERE type_operation = 531 AND categorie_ope = 1 AND id_ag = numagc()) THEN
		INSERT INTO ad_cpt_ope(type_operation, categorie_ope, id_ag, libel_ope) VALUES (531, 1, numagc(), maketraductionlangsyst('Commissions sur certification chèque'));

		RAISE NOTICE 'Insertion type_operation 531 dans la table ad_cpt_ope effectuée';
		output_result := 2;
	END IF;

	IF NOT EXISTS(SELECT * FROM ad_cpt_ope_cptes WHERE type_operation = 531 AND sens = 'd' AND categorie_cpte = 1 AND id_ag = numagc()) THEN
		INSERT INTO ad_cpt_ope_cptes(type_operation, num_cpte, sens, categorie_cpte, id_ag) VALUES (531, NULL, 'd', 1, numagc());

		RAISE NOTICE 'Insertion type_operation 531 sens ''d'' dans la table ad_cpt_ope_cptes effectuée';
		output_result := 2;
	END IF;

	IF NOT EXISTS(SELECT * FROM ad_cpt_ope_cptes WHERE type_operation = 531 AND sens = 'c' AND categorie_cpte = 23 AND id_ag = numagc()) THEN
		INSERT INTO ad_cpt_ope_cptes(type_operation, num_cpte, sens, categorie_cpte, id_ag) VALUES (531, NULL, 'c', 23, numagc());

		RAISE NOTICE 'Insertion type_operation 531 sens ''c'' dans la table ad_cpt_ope_cptes effectuée';
		output_result := 2;
	END IF;
	
	-- Retrait chèque interne certifié
	IF NOT EXISTS(SELECT * FROM ad_cpt_ope WHERE type_operation = 532 AND categorie_ope = 1 AND id_ag = numagc()) THEN
		INSERT INTO ad_cpt_ope(type_operation, categorie_ope, id_ag, libel_ope) VALUES (532, 1, numagc(), maketraductionlangsyst('Retrait chèque interne certifié'));

		RAISE NOTICE 'Insertion type_operation 532 dans la table ad_cpt_ope effectuée';
		output_result := 2;
	END IF;

	IF NOT EXISTS(SELECT * FROM ad_cpt_ope_cptes WHERE type_operation = 532 AND sens = 'd' AND categorie_cpte = 22 AND id_ag = numagc()) THEN
		INSERT INTO ad_cpt_ope_cptes(type_operation, num_cpte, sens, categorie_cpte, id_ag) VALUES (532, NULL, 'd', 22, numagc());

		RAISE NOTICE 'Insertion type_operation 532 sens ''d'' dans la table ad_cpt_ope_cptes effectuée';
		output_result := 2;
	END IF;

	IF NOT EXISTS(SELECT * FROM ad_cpt_ope_cptes WHERE type_operation = 532 AND sens = 'c' AND categorie_cpte = 4 AND id_ag = numagc()) THEN
		INSERT INTO ad_cpt_ope_cptes(type_operation, num_cpte, sens, categorie_cpte, id_ag) VALUES (532, NULL, 'c', 4, numagc());

		RAISE NOTICE 'Insertion type_operation 532 sens ''c'' dans la table ad_cpt_ope_cptes effectuée';
		output_result := 2;
	END IF;
	
	-- Remise chèque interne certifié
	IF NOT EXISTS(SELECT * FROM ad_cpt_ope WHERE type_operation = 533 AND categorie_ope = 1 AND id_ag = numagc()) THEN
		INSERT INTO ad_cpt_ope(type_operation, categorie_ope, id_ag, libel_ope) VALUES (533, 1, numagc(), maketraductionlangsyst('Remise chèque interne certifié'));

		RAISE NOTICE 'Insertion type_operation 533 dans la table ad_cpt_ope effectuée';
		output_result := 2;
	END IF;

	IF NOT EXISTS(SELECT * FROM ad_cpt_ope_cptes WHERE type_operation = 533 AND sens = 'd' AND categorie_cpte = 22 AND id_ag = numagc()) THEN
		INSERT INTO ad_cpt_ope_cptes(type_operation, num_cpte, sens, categorie_cpte, id_ag) VALUES (533, NULL, 'd', 22, numagc());

		RAISE NOTICE 'Insertion type_operation 533 sens ''d'' dans la table ad_cpt_ope_cptes effectuée';
		output_result := 2;
	END IF;

	IF NOT EXISTS(SELECT * FROM ad_cpt_ope_cptes WHERE type_operation = 533 AND sens = 'c' AND categorie_cpte = 1 AND id_ag = numagc()) THEN
		INSERT INTO ad_cpt_ope_cptes(type_operation, num_cpte, sens, categorie_cpte, id_ag) VALUES (533, NULL, 'c', 1, numagc());

		RAISE NOTICE 'Insertion type_operation 533 sens ''c'' dans la table ad_cpt_ope_cptes effectuée';
		output_result := 2;
	END IF;
	
	-- Remise chèque interne
	IF NOT EXISTS(SELECT * FROM ad_cpt_ope WHERE type_operation = 534 AND categorie_ope = 1 AND id_ag = numagc()) THEN
		INSERT INTO ad_cpt_ope(type_operation, categorie_ope, id_ag, libel_ope) VALUES (534, 1, numagc(), maketraductionlangsyst('Remise chèque interne'));

		RAISE NOTICE 'Insertion type_operation 534 dans la table ad_cpt_ope effectuée';
		output_result := 2;
	END IF;

	IF NOT EXISTS(SELECT * FROM ad_cpt_ope_cptes WHERE type_operation = 534 AND sens = 'd' AND categorie_cpte = 1 AND id_ag = numagc()) THEN
		INSERT INTO ad_cpt_ope_cptes(type_operation, num_cpte, sens, categorie_cpte, id_ag) VALUES (534, NULL, 'd', 1, numagc());

		RAISE NOTICE 'Insertion type_operation 534 sens ''d'' dans la table ad_cpt_ope_cptes effectuée';
		output_result := 2;
	END IF;

	IF NOT EXISTS(SELECT * FROM ad_cpt_ope_cptes WHERE type_operation = 534 AND sens = 'c' AND categorie_cpte = 1 AND id_ag = numagc()) THEN
		INSERT INTO ad_cpt_ope_cptes(type_operation, num_cpte, sens, categorie_cpte, id_ag) VALUES (534, NULL, 'c', 1, numagc());

		RAISE NOTICE 'Insertion type_operation 534 sens ''c'' dans la table ad_cpt_ope_cptes effectuée';
		output_result := 2;
	END IF;
	
	-- Compensation chèque certifié
	IF NOT EXISTS(SELECT * FROM ad_cpt_ope WHERE type_operation = 535 AND categorie_ope = 1 AND id_ag = numagc()) THEN
		INSERT INTO ad_cpt_ope(type_operation, categorie_ope, id_ag, libel_ope) VALUES (535, 1, numagc(), maketraductionlangsyst('Compensation chèque certifié'));

		RAISE NOTICE 'Insertion type_operation 535 dans la table ad_cpt_ope effectuée';
		output_result := 2;
	END IF;

	IF NOT EXISTS(SELECT * FROM ad_cpt_ope_cptes WHERE type_operation = 535 AND sens = 'd' AND categorie_cpte = 22 AND id_ag = numagc()) THEN
		INSERT INTO ad_cpt_ope_cptes(type_operation, num_cpte, sens, categorie_cpte, id_ag) VALUES (535, NULL, 'd', 22, numagc());

		RAISE NOTICE 'Insertion type_operation 535 sens ''d'' dans la table ad_cpt_ope_cptes effectuée';
		output_result := 2;
	END IF;

	IF NOT EXISTS(SELECT * FROM ad_cpt_ope_cptes WHERE type_operation = 535 AND sens = 'c' AND categorie_cpte = 5 AND id_ag = numagc()) THEN
		INSERT INTO ad_cpt_ope_cptes(type_operation, num_cpte, sens, categorie_cpte, id_ag) VALUES (535, NULL, 'c', 5, numagc());

		RAISE NOTICE 'Insertion type_operation 535 sens ''c'' dans la table ad_cpt_ope_cptes effectuée';
		output_result := 2;
	END IF;
	
	-- Validation compensation chèque ordinaire
	IF NOT EXISTS(SELECT * FROM ad_cpt_ope WHERE type_operation = 536 AND categorie_ope = 1 AND id_ag = numagc()) THEN
		INSERT INTO ad_cpt_ope(type_operation, categorie_ope, id_ag, libel_ope) VALUES (536, 1, numagc(), maketraductionlangsyst('Validation compensation chèque ordinaire'));

		RAISE NOTICE 'Insertion type_operation 536 dans la table ad_cpt_ope effectuée';
		output_result := 2;
	END IF;

	IF NOT EXISTS(SELECT * FROM ad_cpt_ope_cptes WHERE type_operation = 536 AND sens = 'd' AND categorie_cpte = 1 AND id_ag = numagc()) THEN
		INSERT INTO ad_cpt_ope_cptes(type_operation, num_cpte, sens, categorie_cpte, id_ag) VALUES (536, NULL, 'd', 1, numagc());

		RAISE NOTICE 'Insertion type_operation 536 sens ''d'' dans la table ad_cpt_ope_cptes effectuée';
		output_result := 2;
	END IF;

	IF NOT EXISTS(SELECT * FROM ad_cpt_ope_cptes WHERE type_operation = 536 AND sens = 'c' AND categorie_cpte = 5 AND id_ag = numagc()) THEN
		INSERT INTO ad_cpt_ope_cptes(type_operation, num_cpte, sens, categorie_cpte, id_ag) VALUES (536, NULL, 'c', 5, numagc());

		RAISE NOTICE 'Insertion type_operation 536 sens ''c'' dans la table ad_cpt_ope_cptes effectuée';
		output_result := 2;
	END IF;

	-- Mise en attente compensation chèque ordinaire
	IF NOT EXISTS(SELECT * FROM ad_cpt_ope WHERE type_operation = 537 AND categorie_ope = 1 AND id_ag = numagc()) THEN
		INSERT INTO ad_cpt_ope(type_operation, categorie_ope, id_ag, libel_ope) VALUES (537, 1, numagc(), maketraductionlangsyst('Mise en attente compensation chèque ordinaire'));

		RAISE NOTICE 'Insertion type_operation 537 dans la table ad_cpt_ope effectuée';
		output_result := 2;
	END IF;

	IF NOT EXISTS(SELECT * FROM ad_cpt_ope_cptes WHERE type_operation = 537 AND sens = 'd' AND categorie_cpte = 0 AND id_ag = numagc()) THEN
		INSERT INTO ad_cpt_ope_cptes(type_operation, num_cpte, sens, categorie_cpte, id_ag) VALUES (537, NULL, 'd', 0, numagc());

		RAISE NOTICE 'Insertion type_operation 537 sens ''d'' dans la table ad_cpt_ope_cptes effectuée';
		output_result := 2;
	END IF;

	IF NOT EXISTS(SELECT * FROM ad_cpt_ope_cptes WHERE type_operation = 537 AND sens = 'c' AND categorie_cpte = 5 AND id_ag = numagc()) THEN
		INSERT INTO ad_cpt_ope_cptes(type_operation, num_cpte, sens, categorie_cpte, id_ag) VALUES (537, NULL, 'c', 5, numagc());

		RAISE NOTICE 'Insertion type_operation 537 sens ''c'' dans la table ad_cpt_ope_cptes effectuée';
		output_result := 2;
	END IF;
	
	-- Rejet chèque mis en attente
	IF NOT EXISTS(SELECT * FROM ad_cpt_ope WHERE type_operation = 538 AND categorie_ope = 1 AND id_ag = numagc()) THEN
		INSERT INTO ad_cpt_ope(type_operation, categorie_ope, id_ag, libel_ope) VALUES (538, 1, numagc(), maketraductionlangsyst('Rejet chèque mis en attente'));

		RAISE NOTICE 'Insertion type_operation 538 dans la table ad_cpt_ope effectuée';
		output_result := 2;
	END IF;

	IF NOT EXISTS(SELECT * FROM ad_cpt_ope_cptes WHERE type_operation = 538 AND sens = 'd' AND categorie_cpte = 0 AND id_ag = numagc()) THEN
		INSERT INTO ad_cpt_ope_cptes(type_operation, num_cpte, sens, categorie_cpte, id_ag) VALUES (538, NULL, 'd', 0, numagc());

		RAISE NOTICE 'Insertion type_operation 538 sens ''d'' dans la table ad_cpt_ope_cptes effectuée';
		output_result := 2;
	END IF;

	IF NOT EXISTS(SELECT * FROM ad_cpt_ope_cptes WHERE type_operation = 538 AND sens = 'c' AND categorie_cpte = 0 AND id_ag = numagc()) THEN
		INSERT INTO ad_cpt_ope_cptes(type_operation, num_cpte, sens, categorie_cpte, id_ag) VALUES (538, NULL, 'c', 0, numagc());

		RAISE NOTICE 'Insertion type_operation 538 sens ''c'' dans la table ad_cpt_ope_cptes effectuée';
		output_result := 2;
	END IF;
	
	-- Acceptation chèque mis en attente
	IF NOT EXISTS(SELECT * FROM ad_cpt_ope WHERE type_operation = 539 AND categorie_ope = 1 AND id_ag = numagc()) THEN
		INSERT INTO ad_cpt_ope(type_operation, categorie_ope, id_ag, libel_ope) VALUES (539, 1, numagc(), maketraductionlangsyst('Acceptation chèque mis en attente'));

		RAISE NOTICE 'Insertion type_operation 539 dans la table ad_cpt_ope effectuée';
		output_result := 2;
	END IF;

	IF NOT EXISTS(SELECT * FROM ad_cpt_ope_cptes WHERE type_operation = 539 AND sens = 'd' AND categorie_cpte = 1 AND id_ag = numagc()) THEN
		INSERT INTO ad_cpt_ope_cptes(type_operation, num_cpte, sens, categorie_cpte, id_ag) VALUES (539, NULL, 'd', 1, numagc());

		RAISE NOTICE 'Insertion type_operation 539 sens ''d'' dans la table ad_cpt_ope_cptes effectuée';
		output_result := 2;
	END IF;

	IF NOT EXISTS(SELECT * FROM ad_cpt_ope_cptes WHERE type_operation = 539 AND sens = 'c' AND categorie_cpte = 5 AND id_ag = numagc()) THEN
		INSERT INTO ad_cpt_ope_cptes(type_operation, num_cpte, sens, categorie_cpte, id_ag) VALUES (539, NULL, 'c', 5, numagc());

		RAISE NOTICE 'Insertion type_operation 539 sens ''c'' dans la table ad_cpt_ope_cptes effectuée';
		output_result := 2;
	END IF;

	-- Réception d'un chèque certifié client
	IF NOT EXISTS(SELECT * FROM ad_cpt_ope WHERE type_operation = 540 AND categorie_ope = 1 AND id_ag = numagc()) THEN
		INSERT INTO ad_cpt_ope(type_operation, categorie_ope, id_ag, libel_ope) VALUES (540, 1, numagc(), maketraductionlangsyst('Réception d''un chèque certifié client'));

		RAISE NOTICE 'Insertion type_operation 540 dans la table ad_cpt_ope effectuée';
		output_result := 2;
	END IF;

	IF NOT EXISTS(SELECT * FROM ad_cpt_ope_cptes WHERE type_operation = 540 AND sens = 'd' AND categorie_cpte = 22 AND id_ag = numagc()) THEN
		INSERT INTO ad_cpt_ope_cptes(type_operation, num_cpte, sens, categorie_cpte, id_ag) VALUES (540, NULL, 'd', 22, numagc());

		RAISE NOTICE 'Insertion type_operation 540 sens ''d'' dans la table ad_cpt_ope_cptes effectuée';
		output_result := 2;
	END IF;

	IF NOT EXISTS(SELECT * FROM ad_cpt_ope_cptes WHERE type_operation = 540 AND sens = 'c' AND categorie_cpte = 5 AND id_ag = numagc()) THEN
		INSERT INTO ad_cpt_ope_cptes(type_operation, num_cpte, sens, categorie_cpte, id_ag) VALUES (540, NULL, 'c', 5, numagc());

		RAISE NOTICE 'Insertion type_operation 540 sens ''c'' dans la table ad_cpt_ope_cptes effectuée';
		output_result := 2;
	END IF;

	-- Mise en opposition chèque certifié
	IF NOT EXISTS(SELECT * FROM ad_cpt_ope WHERE type_operation = 541 AND categorie_ope = 1 AND id_ag = numagc()) THEN
		INSERT INTO ad_cpt_ope(type_operation, categorie_ope, id_ag, libel_ope) VALUES (541, 1, numagc(), maketraductionlangsyst('Mise en opposition chèque certifié'));

		RAISE NOTICE 'Insertion type_operation 541 dans la table ad_cpt_ope effectuée';
		output_result := 2;
	END IF;

	IF NOT EXISTS(SELECT * FROM ad_cpt_ope_cptes WHERE type_operation = 541 AND sens = 'd' AND categorie_cpte = 22 AND id_ag = numagc()) THEN
		INSERT INTO ad_cpt_ope_cptes(type_operation, num_cpte, sens, categorie_cpte, id_ag) VALUES (541, NULL, 'd', 22, numagc());

		RAISE NOTICE 'Insertion type_operation 541 sens ''d'' dans la table ad_cpt_ope_cptes effectuée';
		output_result := 2;
	END IF;

	IF NOT EXISTS(SELECT * FROM ad_cpt_ope_cptes WHERE type_operation = 541 AND sens = 'c' AND categorie_cpte = 1 AND id_ag = numagc()) THEN
		INSERT INTO ad_cpt_ope_cptes(type_operation, num_cpte, sens, categorie_cpte, id_ag) VALUES (541, NULL, 'c', 1, numagc());

		RAISE NOTICE 'Insertion type_operation 541 sens ''c'' dans la table ad_cpt_ope_cptes effectuée';
		output_result := 2;
	END IF;

	IF NOT EXISTS (SELECT traduction FROM ad_traductions WHERE traduction='Retrait cash par chèque interne' AND langue='fr_BE') THEN
		UPDATE ad_traductions SET traduction = 'Retrait cash par chèque interne'
		WHERE langue = 'fr_BE' AND id_str = (
			SELECT adt.id_str FROM ad_cpt_ope aco
				INNER JOIN ad_traductions adt
				ON aco.libel_ope = adt.id_str
				WHERE type_operation = 512
				LIMIT 1
			);
	END IF;

	-- Mise a jour du categorie pour les operations 536 et 537 pour permettre de choisir le compte comptable
	--UPDATE ad_cpt_ope_cptes SET categorie_cpte = 0 WHERE type_operation = 535 AND sens = 'c';
	--UPDATE ad_cpt_ope_cptes SET categorie_cpte = 0 WHERE type_operation = 536 AND sens = 'c';
	--UPDATE ad_cpt_ope_cptes SET categorie_cpte = 0 WHERE type_operation = 537 AND sens = 'c';
	--UPDATE ad_cpt_ope_cptes SET categorie_cpte = 0 WHERE type_operation = 539 AND sens = 'c';

	IF EXISTS(SELECT * FROM ad_cpt_ope_cptes WHERE type_operation = 535 AND sens = 'c' AND id_ag = numagc()) THEN
	
		DELETE FROM ad_cpt_ope_cptes WHERE type_operation = 535 AND sens = 'c' AND id_ag = numagc();
		
		INSERT INTO ad_cpt_ope_cptes (type_operation, num_cpte, sens, categorie_cpte, id_ag) VALUES (535, NULL, 'c', 0, numagc());

		RAISE NOTICE 'Update type_operation 535 sens ''c'' dans la table ad_cpt_ope_cptes effectuée';
		output_result := 2;
	END IF;

	IF EXISTS(SELECT * FROM ad_cpt_ope_cptes WHERE type_operation = 536 AND sens = 'c' AND id_ag = numagc()) THEN
	
		DELETE FROM ad_cpt_ope_cptes WHERE type_operation = 536 AND sens = 'c' AND id_ag = numagc();
		
		INSERT INTO ad_cpt_ope_cptes (type_operation, num_cpte, sens, categorie_cpte, id_ag) VALUES (536, NULL, 'c', 0, numagc());

		RAISE NOTICE 'Update type_operation 536 sens ''c'' dans la table ad_cpt_ope_cptes effectuée';
		output_result := 2;
	END IF;

	IF EXISTS(SELECT * FROM ad_cpt_ope_cptes WHERE type_operation = 537 AND sens = 'c' AND id_ag = numagc()) THEN
	
		DELETE FROM ad_cpt_ope_cptes WHERE type_operation = 537 AND sens = 'c' AND id_ag = numagc();
		
		INSERT INTO ad_cpt_ope_cptes (type_operation, num_cpte, sens, categorie_cpte, id_ag) VALUES (537, NULL, 'c', 0, numagc());

		RAISE NOTICE 'Update type_operation 537 sens ''c'' dans la table ad_cpt_ope_cptes effectuée';
		output_result := 2;
	END IF;

	IF EXISTS(SELECT * FROM ad_cpt_ope_cptes WHERE type_operation = 539 AND sens = 'c' AND id_ag = numagc()) THEN
	
		DELETE FROM ad_cpt_ope_cptes WHERE type_operation = 539 AND sens = 'c' AND id_ag = numagc();
		
		INSERT INTO ad_cpt_ope_cptes (type_operation, num_cpte, sens, categorie_cpte, id_ag) VALUES (539, NULL, 'c', 0, numagc());

		RAISE NOTICE 'Update type_operation 539 sens ''c'' dans la table ad_cpt_ope_cptes effectuée';
		output_result := 2;
	END IF;

	------- Gestion des chèques certifiés --------
	-- MENUS
	IF NOT EXISTS(SELECT nom_menu FROM menus WHERE nom_menu='Gcc') THEN
		INSERT INTO menus (nom_menu, libel_menu, nom_pere, pos_hierarch, ordre, is_menu, fonction, is_cliquable) VALUES ('Gcc', maketraductionlangsyst('Gestion des chèques certifiés'), 'Gen-6', 3, 14, true, 162, true);
	END IF;
	
	IF NOT EXISTS(SELECT nom_menu FROM menus WHERE nom_menu='Gcc-1') THEN
		INSERT INTO menus (nom_menu, libel_menu, nom_pere, pos_hierarch, ordre, is_menu, fonction, is_cliquable) VALUES ('Gcc-1', maketraductionlangsyst('Liste des chèques certifiés'), 'Gcc', 3, 1, false, NULL, false);
	END IF;
	
	IF NOT EXISTS(SELECT nom_menu FROM menus WHERE nom_menu='Gcc-2') THEN
		INSERT INTO menus (nom_menu, libel_menu, nom_pere, pos_hierarch, ordre, is_menu, fonction, is_cliquable) VALUES ('Gcc-2', maketraductionlangsyst('Ajout chèque certifié'), 'Gcc', 3, 2, false, NULL, false);
	END IF;
	
	IF NOT EXISTS(SELECT nom_menu FROM menus WHERE nom_menu='Gcc-3') THEN
		INSERT INTO menus (nom_menu, libel_menu, nom_pere, pos_hierarch, ordre, is_menu, fonction, is_cliquable) VALUES ('Gcc-3', maketraductionlangsyst('Confirmation chèque certifié'), 'Gcc', 3, 3, false, NULL, false);
	END IF;
	
	--IF NOT EXISTS(SELECT nom_menu FROM menus WHERE nom_menu='Gcc-4') THEN
		--INSERT INTO menus (nom_menu, libel_menu, nom_pere, pos_hierarch, ordre, is_menu, fonction, is_cliquable) VALUES ('Gcc-4', maketraductionlangsyst('Modification chèque certifié'), 'Gcc', 3, 4, false, NULL, false);
	--END IF;

	-- ECRANS
	IF NOT EXISTS (SELECT nom_ecran FROM ecrans WHERE nom_ecran='Gcc-1') THEN
		INSERT INTO ecrans(nom_ecran, fichier, nom_menu, fonction) VALUES ('Gcc-1', 'modules/guichet/gestion_cheques_certifies.php', 'Gcc', 162);
	END IF;

	IF NOT EXISTS (SELECT nom_ecran FROM ecrans WHERE nom_ecran='Gcc-2') THEN
		INSERT INTO ecrans(nom_ecran, fichier, nom_menu, fonction) VALUES ('Gcc-2', 'modules/guichet/gestion_cheques_certifies.php', 'Gcc', 162);
	END IF;

	IF NOT EXISTS (SELECT nom_ecran FROM ecrans WHERE nom_ecran='Gcc-3') THEN
		INSERT INTO ecrans(nom_ecran, fichier, nom_menu, fonction) VALUES ('Gcc-3', 'modules/guichet/gestion_cheques_certifies.php', 'Gcc', 162);
	END IF;

	--IF NOT EXISTS (SELECT nom_ecran FROM ecrans WHERE nom_ecran='Gcc-4') THEN
		--INSERT INTO ecrans(nom_ecran, fichier, nom_menu, fonction) VALUES ('Gcc-4', 'modules/guichet/gestion_cheques_certifies.php', 'Gcc', 162);
	--END IF;

	------- Traitement des chèques reçus en compensation --------
	-- MENU
	IF NOT EXISTS(SELECT nom_menu FROM menus WHERE nom_menu='Tcc-1') THEN
		INSERT INTO menus (nom_menu, libel_menu, nom_pere, pos_hierarch, ordre, is_menu, fonction, is_cliquable) VALUES ('Tcc-1', maketraductionlangsyst('Traitement des chèques reçus en compensation'), 'Gen-6', 3, 15, true, 163, true);
	END IF;
	
	-- ECRAN
	IF NOT EXISTS (SELECT nom_ecran FROM ecrans WHERE nom_ecran='Tcc-1') THEN
		INSERT INTO ecrans(nom_ecran, fichier, nom_menu, fonction) VALUES ('Tcc-1', 'modules/guichet/menu_cheques_certifies.php', 'Tcc-1', 163);
	END IF;

	------- Enregistrement des chèques --------
	-- MENUS
	IF NOT EXISTS(SELECT nom_menu FROM menus WHERE nom_menu='Ecc') THEN
		INSERT INTO menus (nom_menu, libel_menu, nom_pere, pos_hierarch, ordre, is_menu,fonction, is_cliquable) VALUES ('Ecc', maketraductionlangsyst('Enregistrement des chèques'), 'Tcc-1', 4, 1, true, 164, true);
	END IF;

	-- SUB MENUS
	IF NOT EXISTS(SELECT nom_menu FROM menus WHERE nom_menu='Ecc-1') THEN
		INSERT INTO menus (nom_menu, libel_menu, nom_pere, pos_hierarch, ordre, is_menu,fonction, is_cliquable) VALUES ('Ecc-1', maketraductionlangsyst('Enregistrement ficher Excel'), 'Ecc', 5, 1, false, NULL, false);
	END IF;

	IF NOT EXISTS(SELECT nom_menu FROM menus WHERE nom_menu='Ecc-2') THEN
		INSERT INTO menus (nom_menu, libel_menu, nom_pere, pos_hierarch, ordre, is_menu,fonction, is_cliquable) VALUES ('Ecc-2', maketraductionlangsyst('Confirmation enregistrement'), 'Ecc', 5, 2, false, NULL, false);
	END IF;
	
	-- ECRANS
	IF NOT EXISTS (SELECT nom_ecran FROM ecrans WHERE nom_ecran='Ecc-1') THEN
		INSERT INTO ecrans(nom_ecran, fichier, nom_menu, fonction) VALUES ('Ecc-1', 'modules/guichet/enregistre_cheques_interne.php', 'Ecc-1', 164);
	END IF;

	IF NOT EXISTS (SELECT nom_ecran FROM ecrans WHERE nom_ecran='Ecc-2') THEN
		INSERT INTO ecrans(nom_ecran, fichier, nom_menu, fonction) VALUES ('Ecc-2', 'modules/guichet/enregistre_cheques_interne.php', 'Ecc-2', 164);
	END IF;

	------- Traitement des chèques certifiés --------
	-- MENUS
	IF NOT EXISTS(SELECT nom_menu FROM menus WHERE nom_menu='Pcc') THEN
		INSERT INTO menus (nom_menu, libel_menu, nom_pere, pos_hierarch, ordre, is_menu,fonction, is_cliquable) VALUES ('Pcc', maketraductionlangsyst('Traitement des chèques certifiés'), 'Tcc-1', 4, 2, true, 165, true);
	END IF;

	-- SUB MENUS
	IF NOT EXISTS(SELECT nom_menu FROM menus WHERE nom_menu='Pcc-1') THEN
		INSERT INTO menus (nom_menu, libel_menu, nom_pere, pos_hierarch, ordre, is_menu,fonction, is_cliquable) VALUES ('Pcc-1', maketraductionlangsyst('Traiter les chèques certifiés'), 'Pcc', 5, 1, false, NULL, false);
	END IF;

	IF NOT EXISTS(SELECT nom_menu FROM menus WHERE nom_menu='Pcc-2') THEN
		INSERT INTO menus (nom_menu, libel_menu, nom_pere, pos_hierarch, ordre, is_menu,fonction, is_cliquable) VALUES ('Pcc-2', maketraductionlangsyst('Confirmation traitement des chèques certifiés'), 'Pcc', 5, 2, false, NULL, false);
	END IF;
	
	-- ECRANS
	IF NOT EXISTS (SELECT nom_ecran FROM ecrans WHERE nom_ecran='Pcc-1') THEN
		INSERT INTO ecrans(nom_ecran, fichier, nom_menu, fonction) VALUES ('Pcc-1', 'modules/guichet/traitement_cheques_certifies.php', 'Pcc-1', 165);
	END IF;

	IF NOT EXISTS (SELECT nom_ecran FROM ecrans WHERE nom_ecran='Pcc-2') THEN
		INSERT INTO ecrans(nom_ecran, fichier, nom_menu, fonction) VALUES ('Pcc-2', 'modules/guichet/traitement_cheques_certifies.php', 'Pcc-2', 165);
	END IF;

	------- Traitement des chèques ordinaires (non certifiés) --------
	-- MENUS
	IF NOT EXISTS(SELECT nom_menu FROM menus WHERE nom_menu='Pco') THEN
		INSERT INTO menus (nom_menu, libel_menu, nom_pere, pos_hierarch, ordre, is_menu,fonction, is_cliquable) VALUES ('Pco', maketraductionlangsyst('Traitement des chèques ordinaires'), 'Tcc-1', 4, 3, true, 166, true);
	END IF;

	-- SUB MENUS
	IF NOT EXISTS(SELECT nom_menu FROM menus WHERE nom_menu='Pco-1') THEN
		INSERT INTO menus (nom_menu, libel_menu, nom_pere, pos_hierarch, ordre, is_menu,fonction, is_cliquable) VALUES ('Pco-1', maketraductionlangsyst('Traiter les chèques ordinaires'), 'Pco', 5, 1, false, NULL, false);
	END IF;

	IF NOT EXISTS(SELECT nom_menu FROM menus WHERE nom_menu='Pco-2') THEN
		INSERT INTO menus (nom_menu, libel_menu, nom_pere, pos_hierarch, ordre, is_menu,fonction, is_cliquable) VALUES ('Pco-2', maketraductionlangsyst('Confirmation traitement des chèques ordinaires'), 'Pco', 5, 2, false, NULL, false);
	END IF;
	
	-- ECRANS
	IF NOT EXISTS (SELECT nom_ecran FROM ecrans WHERE nom_ecran='Pco-1') THEN
		INSERT INTO ecrans(nom_ecran, fichier, nom_menu, fonction) VALUES ('Pco-1', 'modules/guichet/traitement_cheques_ordinaires.php', 'Pco-1', 166);
	END IF;

	IF NOT EXISTS (SELECT nom_ecran FROM ecrans WHERE nom_ecran='Pco-2') THEN
		INSERT INTO ecrans(nom_ecran, fichier, nom_menu, fonction) VALUES ('Pco-2', 'modules/guichet/traitement_cheques_ordinaires.php', 'Pco-2', 166);
	END IF;

	------- Traitement des chèques ordinaires mis en attente --------
	-- MENUS
	IF NOT EXISTS(SELECT nom_menu FROM menus WHERE nom_menu='Pom') THEN
		INSERT INTO menus (nom_menu, libel_menu, nom_pere, pos_hierarch, ordre, is_menu,fonction, is_cliquable) VALUES ('Pom', maketraductionlangsyst('Traitement des chèques ordinaires mis en attente'), 'Tcc-1', 4, 4, true, 167, true);
	END IF;

	-- SUB MENUS
	IF NOT EXISTS(SELECT nom_menu FROM menus WHERE nom_menu='Pom-1') THEN
		INSERT INTO menus (nom_menu, libel_menu, nom_pere, pos_hierarch, ordre, is_menu,fonction, is_cliquable) VALUES ('Pom-1', maketraductionlangsyst('Traiter les chèques ordinaires'), 'Pom', 5, 1, false, NULL, false);
	END IF;

	IF NOT EXISTS(SELECT nom_menu FROM menus WHERE nom_menu='Pom-2') THEN
		INSERT INTO menus (nom_menu, libel_menu, nom_pere, pos_hierarch, ordre, is_menu,fonction, is_cliquable) VALUES ('Pom-2', maketraductionlangsyst('Confirmation traitement des chèques ordinaires'), 'Pom', 5, 2, false, NULL, false);
	END IF;

	IF NOT EXISTS(SELECT nom_menu FROM menus WHERE nom_menu='Pom-3') THEN
		INSERT INTO menus (nom_menu, libel_menu, nom_pere, pos_hierarch, ordre, is_menu,fonction, is_cliquable) VALUES ('Pom-3', maketraductionlangsyst('Traitement individuel des chèques ordinaires'), 'Pom', 5, 3, false, NULL, false);
	END IF;
	
	-- ECRANS
	IF NOT EXISTS (SELECT nom_ecran FROM ecrans WHERE nom_ecran='Pom-1') THEN
		INSERT INTO ecrans(nom_ecran, fichier, nom_menu, fonction) VALUES ('Pom-1', 'modules/guichet/traitement_cheques_mise_en_attente.php', 'Pom-1', 167);
	END IF;

	IF NOT EXISTS (SELECT nom_ecran FROM ecrans WHERE nom_ecran='Pom-2') THEN
		INSERT INTO ecrans(nom_ecran, fichier, nom_menu, fonction) VALUES ('Pom-2', 'modules/guichet/traitement_cheques_mise_en_attente.php', 'Pom-2', 167);
	END IF;

	IF NOT EXISTS (SELECT nom_ecran FROM ecrans WHERE nom_ecran='Pom-3') THEN
		INSERT INTO ecrans(nom_ecran, fichier, nom_menu, fonction) VALUES ('Pom-3', 'modules/guichet/traitement_cheques_mise_en_attente.php', 'Pom-3', 167);
	END IF;

	-- Check if field "validite_chq_ord" exist in table "ad_agc"
	IF NOT EXISTS(SELECT * FROM information_schema.columns WHERE table_name = 'ad_agc' AND column_name = 'validite_chq_ord') THEN
		ALTER TABLE ad_agc ADD COLUMN validite_chq_ord int DEFAULT (373);
	END IF;

	-- Insert into "d_tableliste" if notExist
	IF NOT EXISTS(SELECT * FROM d_tableliste WHERE nchmpc = 'validite_chq_ord') THEN

		INSERT INTO d_tableliste(ident, tablen, nchmpc, nchmpl, isreq, ref_field, type, onslct, ispkey, traduit)
		VALUES ((select max(ident) from d_tableliste)+1, (select ident from tableliste where nomc like 'ad_agc' order by ident desc limit 1), 'validite_chq_ord', maketraductionlangsyst('Validité chèque ordinaire (jours)'), true, NULL, 'int', false, false, false);
	END IF;

	-- Check if field "validite_chq_cert" exist in table "ad_agc"
	IF NOT EXISTS(SELECT * FROM information_schema.columns WHERE table_name = 'ad_agc' AND column_name = 'validite_chq_cert') THEN
		ALTER TABLE ad_agc ADD COLUMN validite_chq_cert int DEFAULT (373);
	END IF;

	-- Insert into "d_tableliste" if notExist
	IF NOT EXISTS(SELECT * FROM d_tableliste WHERE nchmpc = 'validite_chq_cert') THEN

		INSERT INTO d_tableliste(ident, tablen, nchmpc, nchmpl, isreq, ref_field, type, onslct, ispkey, traduit)
		VALUES ((select max(ident) from d_tableliste)+1, (select ident from tableliste where nomc like 'ad_agc' order by ident desc limit 1), 'validite_chq_cert', maketraductionlangsyst('Validité chèque certifié (jours)'), true, NULL, 'int', false, false, false);
	END IF;

	RETURN output_result;

END;
$$
LANGUAGE plpgsql;

SELECT init_projet_cheque_interne();
DROP FUNCTION init_projet_cheque_interne();

-----------------------------------------  FIN : PROJET CHEQUE INTERNE  -----------------------------------------------------------


---------------------------------------------------------------------------------------------------------------------------------------------------------------
---------------------------------------------------FONCTION :MAJ NOUVEAU CHAMPS #583
---------------------------------------------------------------------------------------------------------------------------------------------------------------
CREATE OR REPLACE FUNCTION script_583()   RETURNS void AS $BODY$

BEGIN
	RAISE INFO 'MAJ NOUVEAU CHAMPS AD_AGC' ;

-- Check if field "validite_ord_pay" exist in table "ad_agc"
	IF NOT EXISTS(SELECT * FROM information_schema.columns WHERE table_name = 'ad_agc' AND column_name = 'validite_ord_pay') THEN
		ALTER TABLE ad_agc ADD COLUMN validite_ord_pay int DEFAULT (373);
	END IF;

	-- Insert into "d_tableliste" if notExist
	IF NOT EXISTS(SELECT * FROM d_tableliste WHERE nchmpc = 'validite_ord_pay') THEN

		INSERT INTO d_tableliste(ident, tablen, nchmpc, nchmpl, isreq, ref_field, type, onslct, ispkey, traduit)
		VALUES ((select max(ident) from d_tableliste)+1, (select ident from tableliste where nomc like 'ad_agc' order by ident desc limit 1), 'validite_ord_pay', maketraductionlangsyst('Validité ordre de paiement (jours)'), true, NULL, 'int', false, false, false);
	END IF;


END;
$BODY$
LANGUAGE plpgsql VOLATILE COST 100;
ALTER FUNCTION script_583() OWNER TO adbanking;

SELECT script_583();
DROP FUNCTION script_583();

---------------------------------------------------------------------------------------------------------------------------------------------------------------

-- Function: epargne_view(date, integer, integer, integer, integer)

-- DROP FUNCTION epargne_view(date, integer, integer, integer, integer);

CREATE OR REPLACE FUNCTION epargne_view(date, integer, integer, integer, integer)
  RETURNS SETOF epargne_view_type AS
$BODY$
DECLARE
	--id_cpte_u ALIAS FOR $1;
	date_epargne ALIAS FOR $1;
	idag ALIAS FOR $2;
	v_id_prod ALIAS FOR $3;
        v_limit ALIAS FOR $4;
	v_offset  ALIAS FOR $5;
        limites  bigint ;
        offsets  integer :=0;
       
	date_inf DATE;
------------------------
	
	nom_du_client TEXT  :='ssss' ;
	solde_actuel NUMERIC(30,6);
	solde_courant NUMERIC(30,6);
	solde_total NUMERIC(30,6);
	solde_ancien NUMERIC(30,6);

	ligne_epargne epargne_view_type;
	ligne record ;
	cur_epargne  refcursor;  
---------------------------
    
BEGIN   
	  IF v_limit IS NULL THEN 
		limites := 999999999999;
          ELSE
		limites := v_limit;
	  END IF;

        IF v_offset IS NULL THEN 
		offsets := 0;
	ELSE
		offsets := v_offset;
	 END IF;
        IF v_id_prod is NULL THEN 
		CREATE TEMP TABLE  temp_ad_cpt as 
			SELECT  date_ouvert, solde,solde_clot,id_titulaire,id_cpte,id_prod,a.devise,etat_cpte,a.id_ag ,date_clot,num_complet_cpte,b.libel, b.classe_comptable
			from ad_cpt a left join adsys_produit_epargne b on ( a.id_ag=b.id_ag and a.id_prod =b.id)
			where (b.classe_comptable IN (1,2,3,5,6,8))  and  
			date(date_ouvert)<= date(date_epargne) and  a.id_ag =idag and
			( etat_cpte <> 2 OR (etat_cpte = 2 and date(date_clot) >date(date_epargne))) order by id_titulaire,id_cpte limit limites OFFSET  offsets;
	ELSE 
		CREATE TEMP TABLE  temp_ad_cpt as 
			SELECT  date_ouvert, solde,solde_clot,id_titulaire,id_cpte,id_prod,a.devise,etat_cpte,a.id_ag ,date_clot,num_complet_cpte,b.libel, b.classe_comptable
			from ad_cpt a left join adsys_produit_epargne b on ( a.id_ag=b.id_ag and a.id_prod =b.id)
			where (b.classe_comptable IN (1,2,3,5,6,8))  and  
			date(date_ouvert)<= date(date_epargne) and  a.id_ag =idag AND id_prod = v_id_prod AND
			( etat_cpte <> 2 OR (etat_cpte = 2 and date(date_clot) >date(date_epargne)))  order by id_titulaire,id_cpte limit limites OFFSET  offsets;
	END IF;
         
	-- RAISE NOTICE '%', solde_actuel ;
	IF  DATE(date_epargne) >=  DATE(now()) THEN
		OPEN cur_epargne FOR SELECT a.*,  0 as solde_after_date_ep FROM temp_ad_cpt a order  by id_titulaire,id_cpte;
	
	ELSE
               
               CREATE TEMP TABLE    solde_after_date_epargne as SELECT a.id_cpte,  sum( CASE  when sens ='c' THEN montant WHEN sens ='d' THEN -1*montant END ) as solde_after_date_ep 
		from temp_ad_cpt a left join  (ad_mouvement b inner join ad_ecriture c on (b.id_ecriture=c.id_ecriture) ) on (a.id_cpte =b.cpte_interne_cli ) 
		where  date(date_comptable) > date(date_epargne) group by a.id_cpte;
		
	      
		OPEN cur_epargne FOR SELECT a.*,solde_after_date_ep FROM temp_ad_cpt a left join solde_after_date_epargne  b  on (a.id_cpte =b.id_cpte)
		--group by a.id_cpte,date_ouvert, a.solde,a.id_titulaire,a.id_prod,a.devise,etat_cpte,a.id_ag ,a.date_clot,
		--	num_complet_cpte,solde_clot,libel, classe_comptable 
		order by id_titulaire,a.id_cpte;
	END IF;
	 --RAISE NOTICE '%', nom_du_client;
	FETCH cur_epargne INTO ligne;
	WHILE FOUND LOOP
		
               --RAISE NOTICE '%', ligne.id_titulaire;
		SELECT  CASE statut_juridique 
					WHEN 1 THEN 
					pp_nom||' '||pp_prenom
					WHEN 2 THEN
					pm_raison_sociale 
					WHEN 3  THEN gi_nom WHEN 4  THEN 
					gi_nom END   INTO nom_du_client
					 
		FROM ad_cli WHERE id_client = ligne.id_titulaire;
               
		solde_actuel  = COALESCE(ligne.solde,0) -COALESCE(ligne.solde_after_date_ep,0);
               -- solde_total := COALESCE(solde_total,0) +solde_actuel;
               
                SELECT INTO  ligne_epargne ligne.id_titulaire,ligne.id_cpte,ligne.id_prod,ligne.devise,ligne.date_ouvert,ligne.etat_cpte,nom_du_client ,solde_actuel,
			ligne.id_ag,ligne.num_complet_cpte,ligne.libel,ligne.classe_comptable ;
		RETURN NEXT ligne_epargne ;
	FETCH cur_epargne INTO ligne;
	END LOOP;
 CLOSE cur_epargne;
--RAISE NOTICE '%', solde_total;
DROP TABLE temp_ad_cpt;
DROP TABLE IF EXISTS solde_after_date_epargne  ;
--DROP TABLE mv_credit;
RETURN;
END
$BODY$
  LANGUAGE plpgsql VOLATILE
  COST 100
  ROWS 1000;
ALTER FUNCTION epargne_view(date, integer, integer, integer, integer)
  OWNER TO postgres;
 

---------- Ticket #572 -------------------------

CREATE OR REPLACE FUNCTION patch_script_572() RETURNS INT AS $BODY$

DECLARE

output_result INTEGER = 1;

BEGIN

	RAISE NOTICE 'START';
	
	-- Ecrans Nouveaux Rapport
	IF NOT EXISTS (SELECT nom_ecran FROM ecrans WHERE nom_ecran='Era-57') THEN
		INSERT INTO ecrans(nom_ecran, fichier, nom_menu, fonction) VALUES ('Era-57', 'modules/rapports/rapports_epargne.php', 'Era-2', 330);
	END IF;

	IF NOT EXISTS (SELECT nom_ecran FROM ecrans WHERE nom_ecran='Era-58') THEN
		INSERT INTO ecrans(nom_ecran, fichier, nom_menu, fonction) VALUES ('Era-58', 'modules/rapports/rapports_epargne.php', 'Era-3', 330);
	END IF;

	IF NOT EXISTS (SELECT nom_ecran FROM ecrans WHERE nom_ecran='Era-59') THEN
		INSERT INTO ecrans(nom_ecran, fichier, nom_menu, fonction) VALUES ('Era-59', 'modules/rapports/rapports_epargne.php', 'Era-4', 330);
	END IF;
	
	RAISE NOTICE 'END';

	RETURN output_result;

END;
$BODY$
LANGUAGE plpgsql VOLATILE COST 100;
ALTER FUNCTION patch_script_572() OWNER TO adbanking;

SELECT patch_script_572();
DROP FUNCTION patch_script_572();

---------- Fin du Ticket #572 -------------------------

---------- Ticket #602 -------------------------

-- Function: traitecomptesdormants(date, integer)
CREATE OR REPLACE FUNCTION traitecomptesdormants(
    date,
    integer)
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

	  CREATE TEMP TABLE temp_ad_cpt_dormant as
	   SELECT
		id_cpte,
		id_titulaire,
		solde,
		c.devise,
		m.date_dernier_mvt_tenue_cpte,
		m.date_dernier_mvt,
		DATE(date_batch) - m.date_dernier_mvt as ecart
			FROM  ad_cpt b
			inner join adsys_produit_epargne c
			 on b.id_prod = c.id
			 AND b.id_ag = c.id_ag
			 AND c.classe_comptable=1
			 AND c.retrait_unique =FALSE
			 AND c.depot_unique = FALSE
			 AND c.passage_etat_dormant = 'true'

			inner join (
					select cpte_interne_cli, id_ag,
						max(case when type_operation = 50 then date_valeur else null end ) as date_dernier_mvt_tenue_cpte,
						max(date_valeur) as date_dernier_mvt
						from ad_mouvement inner join ad_ecriture using (id_ecriture, id_ag)
						group by cpte_interne_cli,id_ag
				   ) m

			on b.id_cpte = m.cpte_interne_cli
			AND c.id_ag = m.id_ag
			AND c.id_ag = idAgc
			where b.etat_cpte not in (2,4)
			and DATE(date_batch) - m.date_dernier_mvt > ligne_param_epargne.cpte_inactive_nbre_jour
			or (
					(
						(m.date_dernier_mvt = m.date_dernier_mvt_tenue_cpte) and (DATE(date_batch) - m.date_dernier_mvt <= ligne_param_epargne.cpte_inactive_nbre_jour)
					)
				);

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
  OWNER TO adbanking;
---------- Fin du Ticket #602 -------------------------

----------- Ticket #597-------------------------------------------

CREATE OR REPLACE FUNCTION patch_ticket_597() RETURNS INT AS
$$
DECLARE
output_result INTEGER = 1;

BEGIN

	RAISE NOTICE 'START';

	-- Ecrans
	IF NOT EXISTS (SELECT nom_ecran FROM ecrans WHERE nom_ecran='Vcp-11') THEN
		INSERT INTO ecrans(nom_ecran, fichier, nom_menu, fonction) VALUES ('Vcp-11', 'ad_ma/app/views/epargne/consult_compte.php', 'Ope-11', 193);
	END IF;

	IF NOT EXISTS (SELECT nom_ecran FROM ecrans WHERE nom_ecran='Vcp-21') THEN
		INSERT INTO ecrans(nom_ecran, fichier, nom_menu, fonction) VALUES ('Vcp-21', 'ad_ma/app/views/epargne/consult_compte.php', 'Ope-11', 193);
	END IF;

	IF NOT EXISTS (SELECT nom_ecran FROM ecrans WHERE nom_ecran='Vcp-31') THEN
		INSERT INTO ecrans(nom_ecran, fichier, nom_menu, fonction) VALUES ('Vcp-31', 'ad_ma/app/views/epargne/consult_compte.php', 'Ope-11', 193);
	END IF;

	IF NOT EXISTS (SELECT nom_ecran FROM ecrans WHERE nom_ecran='Vcp-41') THEN
		INSERT INTO ecrans(nom_ecran, fichier, nom_menu, fonction) VALUES ('Vcp-41', 'ad_ma/app/views/epargne/consult_compte.php', 'Ope-11', 193);
	END IF;


	RETURN output_result;

END;
$$
LANGUAGE plpgsql;

SELECT patch_ticket_597();
DROP FUNCTION patch_ticket_597();

----------- Fin du Ticket #597------------------------------------


----------------------------------------- DEBUT : PROJET RETRAIT PLAFOND -----------------------------------------------------------

CREATE OR REPLACE FUNCTION patch_ticket_571() RETURNS INT AS
$$
DECLARE

output_result INTEGER = 1;

BEGIN

	RAISE NOTICE 'START';

	-- CREATE TABLE ad_retrait_attente
	IF NOT EXISTS(SELECT * FROM information_schema.tables WHERE table_name = 'ad_retrait_attente') THEN
		CREATE TABLE ad_retrait_attente
		(
			id serial NOT NULL,
			id_ag integer NOT NULL,
			montant_retrait numeric(30,6) NOT NULL,
			etat_retrait integer NOT NULL, -- 1:demandé, 2:autorisé, 3:payé, 4:refusé
			type_retrait integer NOT NULL, -- 1:Retrait ordinaire, 2:Retrait express
			choix_retrait integer NOT NULL,
			id_client integer NOT NULL,
			id_cpte integer NOT NULL,
			communication text NULL,
			remarque text NULL,
			id_pers_ext integer NULL,
			mandat text NULL,
			num_chq text NULL,
			date_chq date NULL,
			id_ben integer NULL,			
			beneficiaire text NULL,
			nom_ben text NULL,
			denomination text NULL,
			frais_retrait_cpt numeric(30,6) NULL,
			id_his integer NULL,
			login character varying(50) NOT NULL,
			comments text,
			date_crea timestamp without time zone NOT NULL DEFAULT now(),
			date_modif timestamp without time zone NULL,
			CONSTRAINT ad_retrait_attente_pkey PRIMARY KEY (id, id_ag)
		)
		WITH (
		  OIDS=FALSE
		);

		RAISE NOTICE 'Table ad_retrait_attente created';
		output_result := 2;
	END IF;

	-- CREATE TABLE ad_retrait_attente_hist
	IF NOT EXISTS(SELECT * FROM information_schema.tables WHERE table_name = 'ad_retrait_attente_hist') THEN
		CREATE TABLE ad_retrait_attente_hist
		(
			id serial NOT NULL,
			date_action timestamp without time zone DEFAULT now(),
			id_retrait_attente integer NOT NULL,
			etat_retrait integer,
			comments text,
			id_ag integer NOT NULL,
			CONSTRAINT ad_retrait_attente_hist_pkey PRIMARY KEY (id, id_ag)
		)
		WITH (
		  OIDS=FALSE
		);

		RAISE NOTICE 'Table ad_retrait_attente_hist created';
		output_result := 2;
	END IF;

	-- Function: trig_insert_ad_retrait_attente_hist()

	-- DROP FUNCTION trig_insert_ad_retrait_attente_hist();

	CREATE OR REPLACE FUNCTION trig_insert_ad_retrait_attente_hist() RETURNS trigger AS $BODY$
	  BEGIN
		INSERT INTO ad_retrait_attente_hist (date_action, id_retrait_attente, etat_retrait, comments, id_ag)
		VALUES (NOW(), OLD.id, OLD.etat_retrait, OLD.comments, OLD.id_ag);
		RETURN NEW;
	  END;
		$BODY$
	  LANGUAGE plpgsql VOLATILE COST 100;

	-- Trigger: trig_before_update_ad_retrait_attente on ad_retrait_attente

	DROP TRIGGER IF EXISTS trig_before_update_ad_retrait_attente ON ad_retrait_attente;

	CREATE TRIGGER trig_before_update_ad_retrait_attente BEFORE UPDATE ON ad_retrait_attente
	FOR EACH ROW EXECUTE PROCEDURE trig_insert_ad_retrait_attente_hist();

	-- Create fonction systeme
	IF NOT EXISTS(SELECT * FROM adsys_fonction WHERE code_fonction=71 AND id_ag = numagc()) THEN

		INSERT INTO adsys_fonction(code_fonction, libelle, id_ag) VALUES (71, 'Demande autorisation retrait', numagc());

		RAISE NOTICE 'Insertion fonction systeme 71 dans la table adsys_fonction effectuée';
		output_result := 2;
	END IF;

	IF NOT EXISTS(SELECT * FROM adsys_fonction WHERE code_fonction=72 AND id_ag = numagc()) THEN

		INSERT INTO adsys_fonction(code_fonction, libelle, id_ag) VALUES (72, 'Autorisation retrait', numagc());

		RAISE NOTICE 'Insertion fonction systeme 72 dans la table adsys_fonction effectuée';
		output_result := 2;
	END IF;

	IF NOT EXISTS(SELECT * FROM adsys_fonction WHERE code_fonction=73 AND id_ag = numagc()) THEN

		INSERT INTO adsys_fonction(code_fonction, libelle, id_ag) VALUES (73, 'Refus retrait', numagc());

		RAISE NOTICE 'Insertion fonction systeme 73 dans la table adsys_fonction effectuée';
		output_result := 2;
	END IF;

	IF NOT EXISTS(SELECT * FROM adsys_fonction WHERE code_fonction=74 AND id_ag = numagc()) THEN

		INSERT INTO adsys_fonction(code_fonction, libelle, id_ag) VALUES (74, 'Paiement retrait', numagc());

		RAISE NOTICE 'Insertion fonction systeme 74 dans la table adsys_fonction effectuée';
		output_result := 2;
	END IF;

	------- Demande autorisation retrait --------
	-- MENU
	IF NOT EXISTS(SELECT nom_menu FROM menus WHERE nom_menu='Rex-4') THEN
		INSERT INTO menus (nom_menu, libel_menu, nom_pere, pos_hierarch, ordre, is_menu, fonction, is_cliquable) VALUES ('Rex-4', maketraductionlangsyst('Demande autorisation retrait'), 'Rex', '5', '4', 'f', NULL, 'f');
	END IF;

	-- ECRAN
	IF NOT EXISTS (SELECT nom_ecran FROM ecrans WHERE nom_ecran='Rex-4') THEN
		INSERT INTO ecrans (nom_ecran, fichier, nom_menu, fonction) VALUES ('Rex-4', 'modules/epargne/retrait_express.php', 'Rex-4', 71);
	END IF;

	------- Autorisation de retrait  --------
	-- MENUS
	IF NOT EXISTS(SELECT nom_menu FROM menus WHERE nom_menu='Adr') THEN
		INSERT INTO menus (nom_menu, libel_menu, nom_pere, pos_hierarch, ordre, is_menu, fonction, is_cliquable) VALUES ('Adr', maketraductionlangsyst('Autorisation de retrait'), 'Gen-6', 3, 6, true, 72, true);
	END IF;

	IF NOT EXISTS(SELECT nom_menu FROM menus WHERE nom_menu='Adr-1') THEN
		INSERT INTO menus (nom_menu, libel_menu, nom_pere, pos_hierarch, ordre, is_menu, fonction, is_cliquable) VALUES ('Adr-1', maketraductionlangsyst('Liste demande autorisation retrait'), 'Adr', 4, 1, false, NULL, false);
	END IF;

	IF NOT EXISTS(SELECT nom_menu FROM menus WHERE nom_menu='Adr-2') THEN
		INSERT INTO menus (nom_menu, libel_menu, nom_pere, pos_hierarch, ordre, is_menu, fonction, is_cliquable) VALUES ('Adr-2', maketraductionlangsyst('Confirmation autorisation retrait'), 'Adr', 4, 2, false, NULL, false);
	END IF;

	-- ECRANS
	IF NOT EXISTS (SELECT nom_ecran FROM ecrans WHERE nom_ecran='Adr-1') THEN
		INSERT INTO ecrans(nom_ecran, fichier, nom_menu, fonction) VALUES ('Adr-1', 'modules/guichet/demande_autorisation_retrait.php', 'Adr-1', 72);
	END IF;

	IF NOT EXISTS (SELECT nom_ecran FROM ecrans WHERE nom_ecran='Adr-2') THEN
		INSERT INTO ecrans(nom_ecran, fichier, nom_menu, fonction) VALUES ('Adr-2', 'modules/guichet/demande_autorisation_retrait.php', 'Adr-1', 72);
	END IF;

	------- Paiement retrait autorisé  --------
	-- MENUS
	IF NOT EXISTS(SELECT nom_menu FROM menus WHERE nom_menu='Pdr') THEN
		INSERT INTO menus (nom_menu, libel_menu, nom_pere, pos_hierarch, ordre, is_menu, fonction, is_cliquable) VALUES ('Pdr', maketraductionlangsyst('Paiement retrait autorisé'), 'Gen-10', 5, 16, true, 74, true);
	END IF;

	IF NOT EXISTS(SELECT nom_menu FROM menus WHERE nom_menu='Pdr-1') THEN
		INSERT INTO menus (nom_menu, libel_menu, nom_pere, pos_hierarch, ordre, is_menu, fonction, is_cliquable) VALUES ('Pdr-1', maketraductionlangsyst('Liste paiement retrait autorisé'), 'Pdr', 6, 1, false, NULL, false);
	END IF;

	/*
	IF NOT EXISTS(SELECT nom_menu FROM menus WHERE nom_menu='Pdr-2') THEN
		INSERT INTO menus (nom_menu, libel_menu, nom_pere, pos_hierarch, ordre, is_menu, fonction, is_cliquable) VALUES ('Pdr-2', maketraductionlangsyst('Confirmation paiement retrait autorisé'), 'Pdr', 6, 2, false, NULL, false);
	END IF;
	*/

	-- ECRANS
	IF NOT EXISTS (SELECT nom_ecran FROM ecrans WHERE nom_ecran='Pdr-1') THEN
		INSERT INTO ecrans(nom_ecran, fichier, nom_menu, fonction) VALUES ('Pdr-1', 'modules/epargne/paiement_retrait.php', 'Pdr-1', 74);
	END IF;

	/*
	IF NOT EXISTS (SELECT nom_ecran FROM ecrans WHERE nom_ecran='Pdr-2') THEN
		INSERT INTO ecrans(nom_ecran, fichier, nom_menu, fonction) VALUES ('Pdr-2', 'modules/epargne/paiement_retrait.php', 'Pdr-1', 74);
	END IF;
	*/
	
	UPDATE menus SET ordre = 8 WHERE nom_menu LIKE 'Out';

	RETURN output_result;

END;
$$
LANGUAGE plpgsql;

SELECT patch_ticket_571();
DROP FUNCTION patch_ticket_571();

-----------------------------------------  FIN : PROJET RETRAIT PLAFOND  -----------------------------------------------------------


----------- Ticket #611 : mise a jour menu PS -------------------------------------------

CREATE OR REPLACE FUNCTION patch_ticket_611() RETURNS INT AS $$
DECLARE
output_result INTEGER = 1;

BEGIN

	RAISE NOTICE 'START patch #611';

  -- Menu Gestion PS:
  update menus set is_cliquable = 't' where nom_menu = 'Mgp-1';

  -- Consultation
  update menus set nom_pere = 'Mgp-1' where nom_menu = 'Cps-1';
  update menus set pos_hierarch = 6 where nom_menu = 'Cps-1';
  update menus set ordre = 1 where nom_menu = 'Cps-1';
  update menus set is_menu = 'f' where nom_menu = 'Cps-1';

  -- Souscription
  update menus set ordre = 2 where nom_menu = 'Sps';

  -- Transfert
  update menus set nom_pere = 'Mgp-1' where nom_menu = 'Mps-1';
  update menus set pos_hierarch = 6 where nom_menu = 'Mps-1';
  update menus set ordre = 3 where nom_menu = 'Mps-1';
  update menus set is_menu = 'f' where nom_menu = 'Mps-1';

  -- Liberation
  update menus set ordre = 4 where nom_menu = 'Lps';

  RAISE NOTICE 'END patch #611';

  RETURN output_result;

END;
$$
LANGUAGE plpgsql;

SELECT patch_ticket_611();
DROP FUNCTION patch_ticket_611();

----------- Fin du Ticket #611------------------------------------

-----------------------------------------------------------------------------------------------
-----------------------------Ticket #311  : Renseigner les infos système------------------------
-----------------------------------------------------------------------------------------------

CREATE OR REPLACE FUNCTION patch_311() RETURNS void AS $BODY$
BEGIN

  IF (SELECT count(*) from information_schema.tables WHERE table_name = 'adsys_infos_systeme') = 0 THEN
    CREATE TABLE adsys_infos_systeme (
      "id" serial  NOT NULL,
      "date_creation" timestamp DEFAULT now(),
      "version_rpm" text NOT NULL,
	  "version_bdd" text NOT NULL,
	  "version_os" text NOT NULL,
	  "version_php" text NOT NULL,
	  "version_apache" text NOT NULL,
	  "is_active" boolean NOT NULL,
	  "id_ag" int not null
    );
  END IF;

  IF NOT EXISTS (SELECT * FROM menus WHERE nom_menu = 'Ifs') THEN
INSERT INTO menus(nom_menu, libel_menu, nom_pere, pos_hierarch, ordre, is_menu,is_cliquable)
VALUES ('Ifs', maketraductionlangsyst('Informations système'), 'Gen-7',3,7,'t','t');
END IF;

-- ecran  menu
IF NOT EXISTS (SELECT * FROM ecrans WHERE nom_ecran = 'Ifs-1') THEN
INSERT INTO ecrans(nom_ecran, fichier, nom_menu, fonction)
VALUES ('Ifs-1','modules/systeme/info_systeme.php','Ifs',241);
END IF;

IF NOT EXISTS (SELECT * from adsys_profils_axs where fonction = 241 AND profil = 1) THEN
INSERT into adsys_profils_axs (profil,fonction)
VALUES(1,241);
END IF;


END;
$BODY$
LANGUAGE plpgsql VOLATILE COST 100;
ALTER FUNCTION patch_311() OWNER TO adbanking;

--------------------- Execution -----------------------------------
SELECT patch_311();
Drop function patch_311();
--------------------------------------------------------------------
CREATE
OR REPLACE FUNCTION insert_adsys_infos_systeme (TEXT, TEXT, TEXT, TEXT) RETURNS void AS $BODY$
 DECLARE
	 rpm_v ALIAS FOR $1 ;
	 os_v ALIAS FOR $2 ;
	 php_v ALIAS FOR $3 ;
	 apache_v ALIAS FOR $4 ;

	 rpm TEXT;
	 bdd TEXT ;
	 os TEXT ;
	 php TEXT ;
	 apache TEXT ;
	 idAgc INTEGER ;

BEGIN

 SELECT
				SUBSTR(rpm_v, 31 ,50) into rpm;

 SELECT
				SUBSTR(VERSION(), 1, 17) INTO bdd;
 SELECT
				SUBSTR(os_v, 1, 32) INTO os;
 SELECT
				SUBSTR(php_v, 1, 16) INTO php ;
 SELECT
				SUBSTR(apache_v, 1, 37) INTO apache;
 SELECT
				numagc () INTO idAgc;

 UPDATE adsys_infos_systeme
					SET is_active = FALSE ;



 INSERT INTO adsys_infos_systeme (
						version_rpm,
						version_bdd,
						version_os,
						version_php,
						version_apache,
						is_active,
						id_ag
					)
					VALUES
						(
							rpm,
							bdd,
							os,
							php,
							apache,
							TRUE,
							idAgc
						) ;
					END ; $BODY$ LANGUAGE plpgsql VOLATILE COST 100;

ALTER FUNCTION insert_adsys_infos_systeme (TEXT, TEXT, TEXT, TEXT) OWNER TO adbanking;

-----------------------------------------------------------------------------------------------
--------------- Ticket #367:  choisir les membres du GS lors de la mise en place --------------
-----------------------------------------------------------------------------------------------
CREATE OR REPLACE FUNCTION patch_367() RETURNS void AS $BODY$
BEGIN

	-- menu choix membre GS
	IF NOT EXISTS (SELECT * FROM menus WHERE nom_menu = 'Ado-12') THEN
		INSERT INTO menus(nom_menu, libel_menu, nom_pere, pos_hierarch, ordre, is_menu,is_cliquable)
		VALUES ('Ado-12', maketraductionlangsyst('Choix des membres'), 'Ado',6,2,'f','f');
	END IF;

	-- ecran choix membre GS
	IF NOT EXISTS (SELECT * FROM ecrans WHERE nom_ecran = 'Ado-12') THEN
		INSERT INTO ecrans(nom_ecran, fichier, nom_menu, fonction)
		VALUES ('Ado-12', 'modules/credit/dossier.php', 'Ado-12', 105);
	END IF;

	-- Change the orders of the menu
	UPDATE menus SET ordre = 1 WHERE nom_menu = 'Ado-1';
	UPDATE menus SET ordre = 2 WHERE nom_menu = 'Ado-12';
	UPDATE menus SET ordre = 3 WHERE nom_menu = 'Ado-2';
	UPDATE menus SET ordre = 4 WHERE nom_menu = 'Ado-3';
	UPDATE menus SET ordre = 5 WHERE nom_menu = 'Ado-4';
	UPDATE menus SET ordre = 6 WHERE nom_menu = 'Ado-5';
	UPDATE menus SET ordre = 7 WHERE nom_menu = 'Ado-6';

END;
$BODY$
LANGUAGE plpgsql VOLATILE COST 100;
ALTER FUNCTION patch_367() OWNER TO adbanking;

--------------------- Execution -----------------------------------
SELECT patch_367();
Drop function patch_367();
--------------------------------------------------------------------

------------------------------------------------------------------------------------------------
------ Ticket #319:Sous menu Rapport Multi-agences + Ajout rapport Situation de compensation ---
------------------------------------------------------------------------------------------------
CREATE OR REPLACE FUNCTION patch_319() RETURNS void AS $BODY$
BEGIN

	-- menu rapport multi-agence
	IF NOT EXISTS (SELECT * FROM menus WHERE nom_menu = 'Rma') THEN
		INSERT INTO menus(nom_menu, libel_menu, nom_pere, pos_hierarch, ordre, is_menu, fonction, is_cliquable)
		VALUES ('Rma', maketraductionlangsyst('Rapports multi-agences'), 'Gen-13',3,5,'t',320,'t');
	END IF;

	-- Change the orders of the menu 'Rapports'
	UPDATE menus SET ordre = 6 WHERE nom_menu = 'Rae';
	UPDATE menus SET ordre = 7 WHERE nom_menu = 'Sra';
	UPDATE menus SET ordre = 8 WHERE nom_menu = 'Dra-1';

	-- Sous menu rapports multi-agence
	IF NOT EXISTS (SELECT * FROM menus WHERE nom_menu = 'Rma-1') THEN
		INSERT INTO menus(nom_menu, libel_menu, nom_pere, pos_hierarch, ordre, is_menu, is_cliquable)
		VALUES ('Rma-1', maketraductionlangsyst('Sélection type'), 'Rma',4,1,'f','f');
	END IF;

	IF NOT EXISTS (SELECT * FROM menus WHERE nom_menu = 'Rma-2') THEN
		INSERT INTO menus(nom_menu, libel_menu, nom_pere, pos_hierarch, ordre, is_menu, is_cliquable)
		VALUES ('Rma-2', maketraductionlangsyst('Personalisation du rapport'), 'Rma',4,2,'f','f');
	END IF;

	IF NOT EXISTS (SELECT * FROM menus WHERE nom_menu = 'Rma-3') THEN
		INSERT INTO menus(nom_menu, libel_menu, nom_pere, pos_hierarch, ordre, is_menu, is_cliquable)
		VALUES ('Rma-3', maketraductionlangsyst('Impression'), 'Rma',4,3,'f','f');
	END IF;

	IF NOT EXISTS (SELECT * FROM menus WHERE nom_menu = 'Rma-4') THEN
		INSERT INTO menus(nom_menu, libel_menu, nom_pere, pos_hierarch, ordre, is_menu, is_cliquable)
		VALUES ('Rma-4', maketraductionlangsyst('Export données'), 'Rma',4,4,'f','f');
	END IF;


	--*********************** Les rapports multi-agence ****************************************

	-- Ecran selection type rapport multi-agence
	IF NOT EXISTS (SELECT * FROM ecrans WHERE nom_ecran = 'Rma-1') THEN
		INSERT INTO ecrans(nom_ecran, fichier, nom_menu, fonction)
		VALUES ('Rma-1', 'modules/rapports/rapports_multi_agences.php', 'Rma-1', 320);
	END IF;

	---- Rapports Situation compensation
	IF NOT EXISTS (SELECT * FROM ecrans WHERE nom_ecran = 'Rma-10') THEN
		INSERT INTO ecrans(nom_ecran, fichier, nom_menu, fonction)
		VALUES ('Rma-10', 'modules/rapports/rapports_multi_agences.php', 'Rma-2', 320); -- Personalisation
	END IF;

	IF NOT EXISTS (SELECT * FROM ecrans WHERE nom_ecran = 'Rma-11') THEN
		INSERT INTO ecrans(nom_ecran, fichier, nom_menu, fonction)
		VALUES ('Rma-11', 'modules/rapports/rapports_multi_agences.php', 'Rma-3', 320); -- Impression PDF
	END IF;

	IF NOT EXISTS (SELECT * FROM ecrans WHERE nom_ecran = 'Rma-12') THEN
		INSERT INTO ecrans(nom_ecran, fichier, nom_menu, fonction)
		VALUES ('Rma-12', 'modules/rapports/rapports_multi_agences.php', 'Rma-3', 320); -- Export CSV
	END IF;

END;
$BODY$
LANGUAGE plpgsql VOLATILE COST 100;
ALTER FUNCTION patch_319() OWNER TO adbanking;

--------------------- Execution -----------------------------------
SELECT patch_319();
Drop function patch_319();
--------------------------------------------------------------------
-----------------------------------------------------------------------------------------------
---------------------------- Ticket #186 -------------------
-----------------------------------------------------------------------------------------------

CREATE OR REPLACE FUNCTION patch_ticket_186()  RETURNS INT AS
$$
DECLARE
	output_result INTEGER = 1;

BEGIN
	RAISE INFO 'MAJ NOUVEAU CHAMPS adsys_produit_epargne' ;
	-- Check if field "prelev_impot_imob" exist in table "adsys_produit_epargne"
	IF NOT EXISTS(SELECT * FROM information_schema.columns WHERE table_name = 'adsys_produit_epargne' AND column_name = 'prelev_impot_imob') THEN
		ALTER TABLE adsys_produit_epargne ADD COLUMN prelev_impot_imob boolean DEFAULT false;
		output_result := 2;
	END IF;


	--insert into "d_tableliste" if notExist
	IF NOT EXISTS(SELECT * FROM d_tableliste WHERE nchmpc = 'prelev_impot_imob') THEN

		INSERT INTO d_tableliste(ident, tablen, nchmpc, nchmpl, isreq, ref_field, type, onslct, ispkey, traduit) VALUES ((select max(ident) from d_tableliste)+1, (select ident from tableliste where nomc like 'adsys_produit_epargne' order by ident desc limit 1), 'prelev_impot_imob',maketraductionlangsyst('Prélever l''impôt mobilier '), false, NULL, 'bol', false, false, false);
		output_result := 2;
	END IF;

	--- Perception taxe impôt mobilier
 IF NOT EXISTS(SELECT * FROM ad_cpt_ope WHERE type_operation = 476 AND categorie_ope = 1 AND id_ag = numagc()) THEN
  INSERT INTO ad_cpt_ope(type_operation, categorie_ope, id_ag, libel_ope) VALUES (476, 1, numagc(), maketraductionlangsyst('Perception taxe impôt mobilier'));

  RAISE NOTICE 'Insertion type_operation 476 dans la table ad_cpt_ope effectuée';
  output_result := 2;
 END IF;

 IF NOT EXISTS(SELECT * FROM ad_cpt_ope_cptes WHERE type_operation = 476 AND sens = 'd' AND categorie_cpte = 1 AND id_ag = numagc()) THEN
  INSERT INTO ad_cpt_ope_cptes(type_operation, num_cpte, sens, categorie_cpte, id_ag) VALUES (476, NULL, 'd', 1, numagc());

  RAISE NOTICE 'Insertion type_operation 476 sens ''d'' dans la table ad_cpt_ope_cptes effectuée';
  output_result := 2;
 END IF;

 IF NOT EXISTS(SELECT * FROM ad_cpt_ope_cptes WHERE type_operation = 476 AND sens = 'c' AND categorie_cpte = 25 AND id_ag = numagc()) THEN
  INSERT INTO ad_cpt_ope_cptes(type_operation, num_cpte, sens, categorie_cpte, id_ag) VALUES (476, NULL, 'c', 25, numagc());

  RAISE NOTICE 'Insertion type_operation 476 sens ''c'' dans la table ad_cpt_ope_cptes effectuée';
  output_result := 2;
 END IF;

	-- Ecrans Nouveaux Rapport
	IF NOT EXISTS (SELECT nom_ecran FROM ecrans WHERE nom_ecran='Tra-32') THEN
		INSERT INTO ecrans(nom_ecran, fichier, nom_menu, fonction) VALUES ('Tra-32', 'modules/compta/rapports_compta.php', 'Tra-3', 430);
	END IF;

	IF NOT EXISTS (SELECT nom_ecran FROM ecrans WHERE nom_ecran='Tra-33') THEN
		INSERT INTO ecrans(nom_ecran, fichier, nom_menu, fonction) VALUES ('Tra-33', 'modules/compta/rapports_compta.php', 'Tra-3', 430);
	END IF;

	IF NOT EXISTS (SELECT nom_ecran FROM ecrans WHERE nom_ecran='Tra-34') THEN
		INSERT INTO ecrans(nom_ecran, fichier, nom_menu, fonction) VALUES ('Tra-34', 'modules/compta/rapports_compta.php', 'Tra-3', 430);
	END IF; 

	RETURN output_result;

END;
$$
LANGUAGE plpgsql;
-- ----------------------------------------------------------------------------
-- Execution
-- ----------------------------------------------------------------------------
SELECT patch_ticket_186();
DROP FUNCTION patch_ticket_186();

----------------------- fin #186 ---------------------------------------------