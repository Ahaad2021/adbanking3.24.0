----------------------------------------- DEBUT : PROJET ACB -----------------------------------------------------------

CREATE OR REPLACE FUNCTION init_projet_acb() RETURNS INT AS
$$
DECLARE
output_result INTEGER = 1;

BEGIN

	RAISE NOTICE 'START';

	-- Check if field "has_cpte_cmplt_agc" exist in table "ad_agc"
	IF NOT EXISTS(SELECT * FROM information_schema.columns WHERE table_name = 'ad_agc' AND column_name = 'has_cpte_cmplt_agc') THEN
		ALTER TABLE ad_agc ADD COLUMN has_cpte_cmplt_agc boolean DEFAULT FALSE;
	END IF;

	-- Insert into "d_tableliste" if notExist
	IF NOT EXISTS(SELECT * FROM d_tableliste WHERE nchmpc = 'has_cpte_cmplt_agc') THEN

		INSERT INTO d_tableliste(ident, tablen, nchmpc, nchmpl, isreq, ref_field, type, onslct, ispkey, traduit) VALUES ((SELECT MAX(ident) FROM d_tableliste)+1, (SELECT ident FROM tableliste WHERE nomc LIKE 'ad_agc' ORDER BY ident DESC LIMIT 1), 'has_cpte_cmplt_agc', maketraductionlangsyst('Générer le numéro de compte avec Id agence ?'), false, NULL, 'bol', false, false, false);
	END IF;

	RETURN output_result;

END;
$$
LANGUAGE plpgsql;

SELECT init_projet_acb();
DROP FUNCTION init_projet_acb();

-----------------------------------------  FIN : PROJET ACB  -----------------------------------------------------------

------------------------- Ticket #641 : Champ Détail Objet de Crédits en Texte Libre et Liste Déroulante  -------------------------
CREATE OR REPLACE FUNCTION patch_ticket_641() RETURNS INT AS
$$
DECLARE

	cur_detail_objet CURSOR FOR SELECT * FROM adsys_detail_objet WHERE id_ag = numagc() ORDER BY id ASC;
	ligne_detail_objet RECORD;

	cur_dcr refcursor;
	ligne_dcr record;

	cur_dcr_grp_sol refcursor;
	ligne_dcr_grp_sol record;

	tableliste_ident_obj INTEGER = 0;
	tableliste_ident INTEGER = 0;

	output_result INTEGER = 1;

BEGIN
	RAISE NOTICE 'DEBUT traitement';
	
	RAISE NOTICE 'START re init table adsys_objets_credits';

	-- Check if field "code" exist in table "adsys_objets_credits"
	IF NOT EXISTS(SELECT * FROM information_schema.columns WHERE table_name = 'adsys_objets_credits' AND column_name = 'code') THEN
		ALTER TABLE adsys_objets_credits ADD COLUMN code text;
	END IF;

	tableliste_ident_obj := (select ident from tableliste where nomc like 'adsys_objets_credits' order by ident desc limit 1);

	-- Insert into "d_tableliste" if notExist
	IF NOT EXISTS(SELECT * FROM d_tableliste WHERE tablen=tableliste_ident_obj AND nchmpc = 'code') THEN

		INSERT INTO d_tableliste(ident, tablen, nchmpc, nchmpl, isreq, ref_field, type, onslct, ispkey, traduit) VALUES ((select max(ident) from d_tableliste)+1, tableliste_ident_obj, 'code', maketraductionlangsyst('Code objet'), false, NULL, 'txt', false, false, false);

		RAISE NOTICE 'Insertion code de la table d_tableliste effectuée';
		output_result := 2;
	END IF;

	RAISE NOTICE 'END re init table adsys_objets_credits';

	RAISE NOTICE 'START re init table adsys_detail_objet';

	IF EXISTS(SELECT * FROM information_schema.tables WHERE table_name = 'adsys_detail_objet') THEN

	  DROP TABLE adsys_detail_objet;

		CREATE TABLE adsys_detail_objet
		(
		  id serial NOT NULL,
		  id_ag integer NOT NULL,
		  libel text,
		  code text,
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

	IF EXISTS(SELECT * FROM d_tableliste WHERE tablen=tableliste_ident AND nchmpc = 'libel') THEN
	
		DELETE FROM d_tableliste WHERE tablen=tableliste_ident AND nchmpc = 'libel';
	
		INSERT INTO d_tableliste(ident, tablen, nchmpc, nchmpl, isreq, ref_field, type, onslct, ispkey, traduit) VALUES ((select max(ident) from d_tableliste)+1, tableliste_ident, 'libel', maketraductionlangsyst('Détail Objet de crédit'), true, NULL, 'txt', false, false, false);

		RAISE NOTICE 'Insertion libel de la table d_tableliste effectuée';
		output_result := 2;
	END IF;

	IF NOT EXISTS(SELECT * FROM d_tableliste WHERE tablen=tableliste_ident AND nchmpc = 'code') THEN

		INSERT INTO d_tableliste(ident, tablen, nchmpc, nchmpl, isreq, ref_field, type, onslct, ispkey, traduit) VALUES ((select max(ident) from d_tableliste)+1, tableliste_ident, 'code', maketraductionlangsyst('Code détail objet'), false, NULL, 'txt', false, false, false);

		RAISE NOTICE 'Insertion code de la table d_tableliste effectuée';
		output_result := 2;
	END IF;

	IF NOT EXISTS(SELECT * FROM d_tableliste WHERE tablen=tableliste_ident AND nchmpc = 'id_obj') THEN
		INSERT INTO d_tableliste(ident, tablen, nchmpc, nchmpl, isreq, ref_field, type, onslct, ispkey, traduit) VALUES ((select max(ident) from d_tableliste)+1, tableliste_ident, 'id_obj', maketraductionlangsyst('Objet de crédit'), true, NULL, 'lsb', true, false, false);

		RAISE NOTICE 'Insertion id_obj de la table d_tableliste effectuée';
		output_result := 2;
	END IF;

	RAISE NOTICE 'END re init table adsys_detail_objet';

	-- Check if field "detail_obj_dem_bis" exist in table "ad_dcr"
	IF NOT EXISTS(SELECT * FROM information_schema.columns WHERE table_name = 'ad_dcr' AND column_name = 'detail_obj_dem_bis') THEN
	
		DROP TRIGGER maj_horodatage ON ad_dcr;
		DROP TRIGGER trig_before_update_ad_dcr ON ad_dcr;
	
		ALTER TABLE ad_dcr ADD COLUMN detail_obj_dem_bis integer;
		
		CREATE TRIGGER maj_horodatage
		  BEFORE UPDATE
		  ON ad_dcr
		  FOR EACH ROW
		  EXECUTE PROCEDURE maj_horodatage();

		CREATE TRIGGER trig_before_update_ad_dcr
		  BEFORE UPDATE
		  ON ad_dcr
		  FOR EACH ROW
		  EXECUTE PROCEDURE trig_insert_ad_dcr_hist();
		
		output_result := 2;
	END IF;

	-- Insert into "d_tableliste" if notExist
	IF NOT EXISTS(SELECT * FROM d_tableliste WHERE tablen = (select ident from tableliste where nomc like 'ad_dcr' order by ident desc limit 1) AND nchmpc = 'detail_obj_dem_bis') THEN

		INSERT INTO d_tableliste(ident, tablen, nchmpc, nchmpl, isreq, ref_field, type, onslct, ispkey, traduit) VALUES ((select max(ident) from d_tableliste)+1, (select ident from tableliste where nomc like 'ad_dcr' order by ident desc limit 1), 'detail_obj_dem_bis', maketraductionlangsyst('Détail objet crédit'), false, NULL, 'int', false, false, false);
		output_result := 2;
	END IF;

	-- Check if field "detail_obj_dem_bis" exist in table "ad_dcr_grp_sol"
	IF NOT EXISTS(SELECT * FROM information_schema.columns WHERE table_name = 'ad_dcr_grp_sol' AND column_name = 'detail_obj_dem_bis') THEN
		ALTER TABLE ad_dcr_grp_sol ADD COLUMN detail_obj_dem_bis integer;
		output_result := 2;
	END IF;

	-- Insert into "d_tableliste" if notExist
	IF NOT EXISTS(SELECT * FROM d_tableliste WHERE tablen = (select ident from tableliste where nomc like 'ad_dcr_grp_sol' order by ident desc limit 1) AND nchmpc = 'detail_obj_dem_bis') THEN

		INSERT INTO d_tableliste(ident, tablen, nchmpc, nchmpl, isreq, ref_field, type, onslct, ispkey, traduit) VALUES ((select max(ident) from d_tableliste)+1, (select ident from tableliste where nomc like 'ad_dcr_grp_sol' order by ident desc limit 1), 'detail_obj_dem_bis', maketraductionlangsyst('Détail objet crédit'), false, NULL, 'int', false, false, false);
		output_result := 2;
	END IF;

	-- Check if field "dcr_lsb_detail_obj_credit" exist in table "ad_agc"
	IF NOT EXISTS(SELECT * FROM information_schema.columns WHERE table_name = 'ad_agc' AND column_name = 'dcr_lsb_detail_obj_credit') THEN
		ALTER TABLE ad_agc ADD COLUMN dcr_lsb_detail_obj_credit boolean DEFAULT false;
	END IF;

	-- Insert into "d_tableliste" if notExist
	IF NOT EXISTS(SELECT * FROM d_tableliste WHERE nchmpc = 'dcr_lsb_detail_obj_credit') THEN

		INSERT INTO d_tableliste(ident, tablen, nchmpc, nchmpl, isreq, ref_field, type, onslct, ispkey, traduit) VALUES ((select max(ident) from d_tableliste)+1, (select ident from tableliste where nomc like 'ad_agc' order by ident desc limit 1), 'dcr_lsb_detail_obj_credit', maketraductionlangsyst('Afficher la liste déroulante détails objets de crédit lors de la mise en place d’un dossier de crédit ?'), false, NULL, 'bol', false, false,	false);
	END IF;
	
	RAISE NOTICE 'DEBUT reprise des details objet demande';

	-- Traitement ad_dcr
	OPEN cur_detail_objet; -- Open cursor cur_detail_objet
	FETCH cur_detail_objet INTO ligne_detail_objet;

	WHILE FOUND LOOP -- Loop in resultset
	
		IF EXISTS(SELECT * FROM ad_dcr WHERE detail_obj_dem LIKE ligne_detail_objet.id::text AND id_ag = numagc()) THEN
		
			-- Traitement ad_dcr
			OPEN cur_dcr FOR SELECT * FROM ad_dcr WHERE detail_obj_dem LIKE ligne_detail_objet.id::text AND id_ag = numagc() ORDER BY id_doss ASC;
			FETCH cur_dcr INTO ligne_dcr;

			WHILE FOUND LOOP -- Loop in resultset

				-- Update dossier
				UPDATE ad_dcr SET detail_obj_dem = REGEXP_REPLACE(ligne_detail_objet.libel, E'([\\\\]+)', '', 'g') WHERE id_doss = ligne_dcr.id_doss AND detail_obj_dem = ligne_detail_objet.id::text AND id_ag = numagc();
				
				output_result := 2;

				FETCH cur_dcr INTO ligne_dcr; -- GET next element
			END LOOP;

			CLOSE cur_dcr; -- Close cursor cur_dcr

		END IF;

		IF EXISTS(SELECT * FROM ad_dcr_grp_sol WHERE detail_obj_dem LIKE ligne_detail_objet.id::text AND id_ag = numagc()) THEN
		
			-- Traitement ad_dcr_grp_sol
			OPEN cur_dcr_grp_sol FOR SELECT * FROM ad_dcr_grp_sol WHERE detail_obj_dem LIKE ligne_detail_objet.id::text AND id_ag = numagc() ORDER BY id ASC;
			FETCH cur_dcr_grp_sol INTO ligne_dcr_grp_sol;

			WHILE FOUND LOOP -- Loop in resultset

				-- Update dossier group solidaire
				UPDATE ad_dcr_grp_sol SET detail_obj_dem = REGEXP_REPLACE(ligne_detail_objet.libel, E'([\\\\]+)', '', 'g') WHERE id = ligne_dcr_grp_sol.id AND detail_obj_dem = ligne_detail_objet.id::text AND id_ag = numagc();

				output_result := 2;

				FETCH cur_dcr_grp_sol INTO ligne_dcr_grp_sol; -- GET next element
			END LOOP;

			CLOSE cur_dcr_grp_sol; -- Close cursor cur_dcr_grp_sol

		END IF;

		FETCH cur_detail_objet INTO ligne_detail_objet; -- GET next element
	END LOOP;

	CLOSE cur_detail_objet; -- Close cursor cur_detail_objet

	RAISE NOTICE 'FIN reprise des details objet demande';

	RAISE NOTICE 'FIN traitement';
	RETURN output_result;

END;
$$
LANGUAGE plpgsql;

SELECT patch_ticket_641();
DROP FUNCTION patch_ticket_641();

------------------------- Ticket #641 : Champ Détail Objet de Crédits en Texte Libre et Liste Déroulante  -------------------------

------------------------- Ticket #653 : Validation du nombre de chiffres dans le numéro de téléphone pour les abonnements SMS  -------------------------

CREATE OR REPLACE FUNCTION patch_param_abonnement() RETURNS INT AS
$$
DECLARE
output_result INTEGER = 1;
tableliste_ident INTEGER = 0;

BEGIN

	RAISE NOTICE 'START';

	IF NOT EXISTS(SELECT * FROM information_schema.tables WHERE table_name = 'adsys_param_abonnement') THEN
		CREATE TABLE adsys_param_abonnement
		(
		  cle character varying(100) NOT NULL,
		  id_ag integer NOT NULL,
		  libelle character varying(250) NOT NULL,
		  valeur text NOT NULL,
		  lib_texte1 text,
		  CONSTRAINT adsys_param_abonnement_pkey PRIMARY KEY (cle, id_ag)
		)
		WITH (
		  OIDS=FALSE
		);

		RAISE NOTICE 'Table adsys_param_abonnement created';
		output_result := 2;
	END IF;
	
	IF NOT EXISTS(SELECT * FROM tableliste WHERE nomc = 'adsys_param_abonnement') THEN
		INSERT INTO tableliste(ident, nomc, noml, is_table) VALUES ((select max(ident) from tableliste)+1, 'adsys_param_abonnement', maketraductionlangsyst('Table Paramétrage Abonnement'), true);
		
		RAISE NOTICE 'Insertion table adsys_param_abonnement de la table tableliste effectuée';
		output_result := 2;
	END IF;

	tableliste_ident := (select ident from tableliste where nomc like 'adsys_param_abonnement' order by ident desc limit 1);

	IF NOT EXISTS(SELECT * FROM d_tableliste WHERE tablen=tableliste_ident AND nchmpc = 'cle') THEN
		INSERT INTO d_tableliste(ident, tablen, nchmpc, nchmpl, isreq, ref_field, type, onslct, ispkey, traduit) VALUES ((select max(ident) from d_tableliste)+1, tableliste_ident, 'cle', maketraductionlangsyst('Clé'), true, NULL, 'txt', false, true, false);

		RAISE NOTICE 'Insertion cle de la table d_tableliste effectuée';
		output_result := 2;
	END IF;
	
	IF NOT EXISTS(SELECT * FROM d_tableliste WHERE tablen=tableliste_ident AND nchmpc = 'libelle') THEN
		INSERT INTO d_tableliste(ident, tablen, nchmpc, nchmpl, isreq, ref_field, type, onslct, ispkey, traduit) VALUES ((select max(ident) from d_tableliste)+1, tableliste_ident, 'libelle', maketraductionlangsyst('Libellé'), true, NULL, 'txt', true, false, false);

		RAISE NOTICE 'Insertion libelle de la table d_tableliste effectuée';
		output_result := 2;
	END IF;

	IF NOT EXISTS(SELECT * FROM d_tableliste WHERE tablen=tableliste_ident AND nchmpc = 'valeur') THEN
		INSERT INTO d_tableliste(ident, tablen, nchmpc, nchmpl, isreq, ref_field, type, onslct, ispkey, traduit) VALUES ((select max(ident) from d_tableliste)+1, tableliste_ident, 'valeur', maketraductionlangsyst('Valeur'), true, NULL, 'txt', false, false, false);

		RAISE NOTICE 'Insertion valeur de la table d_tableliste effectuée';
		output_result := 2;
	END IF;

	IF NOT EXISTS(SELECT * FROM d_tableliste WHERE tablen=tableliste_ident AND nchmpc = 'lib_texte1') THEN
		INSERT INTO d_tableliste(ident, tablen, nchmpc, nchmpl, isreq, ref_field, type, onslct, ispkey, traduit) VALUES ((select max(ident) from d_tableliste)+1, tableliste_ident, 'lib_texte1', maketraductionlangsyst('Champ libre'), true, NULL, 'are', false, false, false);

		RAISE NOTICE 'Insertion lib_texte1 de la table d_tableliste effectuée';
		output_result := 2;
	END IF;
	
	-- Truncate table tarification
	IF (SELECT COUNT(*) FROM adsys_param_abonnement) > 0 THEN
	
		-- Empty table
		TRUNCATE TABLE adsys_param_abonnement RESTART IDENTITY CASCADE;
		
		RAISE NOTICE 'Truncate table adsys_param_abonnement effectuée';
		output_result := 2;
	END IF;

	-- ----------------------------
	-- Records of adsys_param_abonnement
	-- ----------------------------
	INSERT INTO adsys_param_abonnement(cle, id_ag, libelle, valeur, lib_texte1) VALUES ('NB_CARACTERES_TELEPHONE', numagc(), 'Nombre de chiffres authorisé', '10', 'Numéro SMS doit contenir 10 chiffres');

	RAISE NOTICE 'Insertion données dans la table adsys_param_abonnement effectuée';
	
	-- ----------------------------
	-- Records of ad_ewallet
	-- ----------------------------
	DELETE FROM ad_abonnement;
	DELETE FROM ad_ewallet;
	INSERT INTO ad_ewallet(id_prestataire, id_ag, code_prestataire, nom_prestataire) VALUES (1, numagc(), 'COMORES_TELECOM_KM', 'Comores Telecom');
	INSERT INTO ad_ewallet(id_prestataire, id_ag, code_prestataire, nom_prestataire) VALUES (2, numagc(), 'TELMA_KM', 'TELMA');
	
	RAISE NOTICE 'Truncate et Insertion données dans la table ad_ewallet effectuées';
	
	RAISE NOTICE 'END';

	RETURN output_result;

END;
$$
LANGUAGE plpgsql;

SELECT patch_param_abonnement();
DROP FUNCTION patch_param_abonnement();

------------------------- Ticket #653 : Validation du nombre de chiffres dans le numéro de téléphone pour les abonnements SMS  -------------------------


------------------------- Project Pro Ticket #203 : Acces sur l'autorisation de retrait  -------------------------

CREATE OR REPLACE FUNCTION script_rpm_3_16_1_fix() RETURNS INT AS
$$
DECLARE
output_result INTEGER = 1;
tableliste_ident INTEGER = (select ident from tableliste where nomc like 'adsys_produit_epargne' order by ident desc limit 1);

BEGIN

	RAISE NOTICE 'START';
	
	IF EXISTS(SELECT * FROM adsys_fonction WHERE code_fonction = 72 AND id_ag = numagc()) THEN

		UPDATE adsys_fonction SET code_fonction = 157 WHERE code_fonction = 72 AND id_ag = numagc();

		RAISE NOTICE 'Update fonction systeme 157 dans la table adsys_fonction effectuée';
		output_result := 2;
	END IF;

	------- Autorisation de retrait  --------
	-- MENUS
	IF EXISTS(SELECT nom_menu FROM menus WHERE nom_menu='Adr' AND fonction=72) THEN

		UPDATE menus SET fonction = 157 WHERE fonction = 72 AND nom_menu='Adr';

	END IF;
	
	-- ECRANS
	IF EXISTS (SELECT nom_ecran FROM ecrans WHERE nom_ecran='Adr-1' AND fonction=72) THEN
		UPDATE ecrans SET fonction = 157 WHERE fonction = 72 AND nom_ecran='Adr-1';
	END IF;

	IF EXISTS (SELECT nom_ecran FROM ecrans WHERE nom_ecran='Adr-2' AND fonction=72) THEN
		UPDATE ecrans SET fonction = 157, nom_menu = 'Adr-2' WHERE fonction = 72 AND nom_ecran='Adr-2';
	END IF;
	
	RAISE NOTICE 'END';

	RETURN output_result;

END;
$$
LANGUAGE plpgsql;

SELECT script_rpm_3_16_1_fix();
DROP FUNCTION script_rpm_3_16_1_fix();

------------------------- Project Pro Ticket #203 : Acces sur l'autorisation de retrait  -------------------------

------------- Ticket #639 ajout colonne id_his dans ad_provision ---------------------------------------
CREATE OR REPLACE FUNCTION patch_ticket_639()  RETURNS INT AS
$$
DECLARE
	output_result INTEGER = 1;
BEGIN
	-- Check if field "id_his" exist in table "ad_provision"
	IF NOT EXISTS(SELECT * FROM information_schema.columns WHERE table_name = 'ad_provision' AND column_name = 'id_his') THEN
		ALTER TABLE ad_provision ADD COLUMN id_his INTEGER DEFAULT NULL;
		output_result := 2;
	END IF;

  -- Remise a zero des prov_mnt dans ad_dcr
  UPDATE ad_dcr SET prov_mnt = 0 WHERE prov_mnt > 0;
  -- truncate ad_provision
  truncate table ad_provision;
  --reset sequence of ad_provision
  PERFORM reset_sequence('ad_provision', NULL, 'id_provision');

	RETURN output_result;
END;
$$
LANGUAGE plpgsql;

SELECT patch_ticket_639();
DROP FUNCTION patch_ticket_639();

---------------------- Ticket #634 Ajout menus/ecrans Reechelonnement 'En une fois' -------------------------------
CREATE OR REPLACE FUNCTION patch_ticket_634()  RETURNS INT AS
$$
DECLARE
	output_result INTEGER = 1;
BEGIN

	-- Menus :
	IF NOT EXISTS(SELECT nom_menu FROM menus WHERE nom_menu='Muf-1') THEN
		INSERT INTO menus VALUES ('Muf-1', maketraductionlangsyst('Demande rééchel crédits en une fois'), 'Mec', 6, 6, false, NULL, false);
	END IF;

	IF NOT EXISTS(SELECT nom_menu FROM menus WHERE nom_menu='Auf-1') THEN
	  INSERT INTO menus VALUES ('Auf-1', maketraductionlangsyst('Approb rééchel crédits en une fois'), 'Mec', 6, 7, false, NULL, false);
	END IF;

  IF NOT EXISTS(SELECT nom_menu FROM menus WHERE nom_menu='Ruf-1') THEN
    INSERT INTO menus VALUES ('Ruf-1', maketraductionlangsyst('Annul rééchel crédits en une fois'), 'Mec', 6, 8, false, NULL, false);
  END IF;


  -- Ecrans :

  -- Demande reechelonnement en une fois:
  IF NOT EXISTS (SELECT nom_ecran FROM ecrans WHERE nom_ecran='Muf-1') THEN
		INSERT INTO ecrans(nom_ecran, fichier, nom_menu, fonction) VALUES ('Muf-1', 'modules/credit/reechel_une_fois.php', 'Muf-1', 137);
	END IF;

  IF NOT EXISTS (SELECT nom_ecran FROM ecrans WHERE nom_ecran='Muf-2') THEN
    INSERT INTO ecrans(nom_ecran, fichier, nom_menu, fonction) VALUES ('Muf-2', 'modules/credit/reechel_une_fois.php', 'Muf-1', 137);
  END IF;

  IF NOT EXISTS (SELECT nom_ecran FROM ecrans WHERE nom_ecran='Muf-3') THEN
    INSERT INTO ecrans(nom_ecran, fichier, nom_menu, fonction) VALUES ('Muf-3', 'modules/credit/reechel_une_fois.php', 'Muf-1', 137);
  END IF;

  IF NOT EXISTS (SELECT nom_ecran FROM ecrans WHERE nom_ecran='Muf-4') THEN
    INSERT INTO ecrans(nom_ecran, fichier, nom_menu, fonction) VALUES ('Muf-4', 'modules/credit/reechel_une_fois.php', 'Muf-1', 137);
  END IF;

  -- Approbation reechelonnement en une fois:
  IF NOT EXISTS (SELECT nom_ecran FROM ecrans WHERE nom_ecran='Auf-1') THEN
    INSERT INTO ecrans(nom_ecran, fichier, nom_menu, fonction) VALUES ('Auf-1', 'modules/credit/approb_une_fois.php', 'Auf-1', 138);
  END IF;

  IF NOT EXISTS (SELECT nom_ecran FROM ecrans WHERE nom_ecran='Auf-2') THEN
    INSERT INTO ecrans(nom_ecran, fichier, nom_menu, fonction) VALUES ('Auf-2', 'modules/credit/approb_une_fois.php', 'Auf-1', 138);
  END IF;

  IF NOT EXISTS (SELECT nom_ecran FROM ecrans WHERE nom_ecran='Auf-3') THEN
    INSERT INTO ecrans(nom_ecran, fichier, nom_menu, fonction) VALUES ('Auf-3', 'modules/credit/approb_une_fois.php', 'Auf-1', 138);
  END IF;

  IF NOT EXISTS (SELECT nom_ecran FROM ecrans WHERE nom_ecran='Auf-4') THEN
    INSERT INTO ecrans(nom_ecran, fichier, nom_menu, fonction) VALUES ('Auf-4', 'modules/credit/approb_une_fois.php', 'Auf-1', 138);
  END IF;

  -- Annulation / Rejet reechelonnement en une fois
  IF NOT EXISTS (SELECT nom_ecran FROM ecrans WHERE nom_ecran='Ruf-1') THEN
    INSERT INTO ecrans(nom_ecran, fichier, nom_menu, fonction) VALUES ('Ruf-1', 'modules/credit/annul_une_fois.php', 'Ruf-1', 139);
  END IF;

  IF NOT EXISTS (SELECT nom_ecran FROM ecrans WHERE nom_ecran='Ruf-2') THEN
    INSERT INTO ecrans(nom_ecran, fichier, nom_menu, fonction) VALUES ('Ruf-2', 'modules/credit/annul_une_fois.php', 'Ruf-1', 139);
  END IF;

  IF NOT EXISTS (SELECT nom_ecran FROM ecrans WHERE nom_ecran='Ruf-3') THEN
    INSERT INTO ecrans(nom_ecran, fichier, nom_menu, fonction) VALUES ('Ruf-3', 'modules/credit/annul_une_fois.php', 'Ruf-1', 139);
  END IF;

  IF NOT EXISTS (SELECT nom_ecran FROM ecrans WHERE nom_ecran='Ruf-4') THEN
    INSERT INTO ecrans(nom_ecran, fichier, nom_menu, fonction) VALUES ('Ruf-4', 'modules/credit/annul_une_fois.php', 'Ruf-1', 139);
  END IF;

  RETURN output_result;
END;
$$
LANGUAGE plpgsql;
---------------------------------------------------------
SELECT patch_ticket_634();
DROP FUNCTION patch_ticket_634();

----------------------FIN : Ticket #634 Ajout menus/ecrans Reechelonnement 'En une fois' -------------------------------

------------------------------- DEBUT : PP #206 - Discordance entre la comptabilite et Rapport d'impot mobilier -------------------------------
CREATE OR REPLACE VIEW view_compta AS 
 SELECT m.id_mouvement, m.id_ecriture, m.compte, m.cpte_interne_cli, m.sens, m.montant, m.devise, m.date_valeur, m.id_ag, m.consolide, e.libel_ecriture, e.date_comptable, e.id_jou, e.id_exo, e.type_operation, e.id_his, e.info_ecriture
   FROM ad_mouvement m, ad_ecriture e
  WHERE m.id_ecriture = e.id_ecriture AND m.id_ag = e.id_ag;

ALTER TABLE view_compta
  OWNER TO postgres;

------------------------------- FIN : PP #206 - Discordance entre la comptabilite et Rapport d'impot mobilier -------------------------------

------------- Ticket #677 :  ---------------------------------------

CREATE OR REPLACE FUNCTION patch_ticket_677() RETURNS INT AS $BODY$
DECLARE

output_result INTEGER = 1;

BEGIN

	RAISE NOTICE 'START';

	-- Check if field "licence_key" exist in table "ad_agc"
	IF NOT EXISTS(SELECT * FROM information_schema.columns WHERE table_name = 'ad_agc' AND column_name = 'licence_key') THEN
		ALTER TABLE ad_agc ADD COLUMN licence_key text;
		output_result := 2;
	END IF;

	-- Clear field
	UPDATE ad_agc SET licence_code_identifier = NULL, licence_key = NULL;

	-- Truncate table
	TRUNCATE TABLE adsys_licence RESTART IDENTITY;

	-- Insert into "d_tableliste" if notExist
	/*
	IF NOT EXISTS(SELECT * FROM d_tableliste WHERE nchmpc = 'licence_key') THEN

		INSERT INTO d_tableliste(ident, tablen, nchmpc, nchmpl, isreq, ref_field, type, onslct, ispkey, traduit) VALUES ((SELECT MAX(ident) FROM d_tableliste)+1, (SELECT ident FROM tableliste WHERE nomc LIKE 'ad_agc' ORDER BY ident DESC LIMIT 1), 'licence_key', maketraductionlangsyst('Licence key'), false, NULL, 'txt', false, false, false);

	END IF;
	*/

	RAISE NOTICE 'END';

	RETURN output_result;  

END;
$BODY$
LANGUAGE plpgsql VOLATILE COST 100;
ALTER FUNCTION patch_ticket_677() OWNER TO adbanking;

--------------------- Execution ------------------------------------
SELECT patch_ticket_677();
DROP FUNCTION patch_ticket_677();
--------------------------------------------------------------------

------------------------- DEBUT : Ticket #675 : Mise a jour de la table ad_gar -------------------------
/*
CREATE OR REPLACE FUNCTION patch_ticket_675() RETURNS INT AS
$$
DECLARE
output_result INTEGER = 1;

BEGIN

	RAISE NOTICE 'START';

	-- Check if field "prov_mnt" exist in table "ad_gar"
	IF NOT EXISTS(SELECT * FROM information_schema.columns WHERE table_name = 'ad_gar' AND column_name = 'prov_mnt') THEN
		ALTER TABLE ad_gar ADD COLUMN prov_mnt numeric(30,6) DEFAULT 0;
	END IF;

	-- Check if field "prov_date" exist in table "ad_gar"
	IF NOT EXISTS(SELECT * FROM information_schema.columns WHERE table_name = 'ad_gar' AND column_name = 'prov_date') THEN
		ALTER TABLE ad_gar ADD COLUMN prov_date date;
	END IF;

	-- Check if field "prov_is_calcul" exist in table "ad_gar"
	IF NOT EXISTS(SELECT * FROM information_schema.columns WHERE table_name = 'ad_gar' AND column_name = 'prov_is_calcul') THEN
		ALTER TABLE ad_gar ADD COLUMN prov_is_calcul boolean DEFAULT true;
	END IF;
	
	RAISE NOTICE 'END';

	RETURN output_result;

END;
$$
LANGUAGE plpgsql;

SELECT patch_ticket_675();
DROP FUNCTION patch_ticket_675();
*/
------------------------- FIN : Ticket #675 : Mise a jour de la table ad_gar -------------------------

-------------------------------- DEBUT : Ticket #568 : Anomalie bilan base Siège ----------------------------------
------------------------------------------------------------------------------------------------------------
-- Calcule récursif du solde d'un compte à la date 'date_param'  dans la partie du bilan correspondant au compartiment 'compartiment'
------------------------------------------------------------------------------------------------------------

CREATE OR REPLACE FUNCTION calculeSoldeBilan(text,DATE,INTEGER,INTEGER,boolean,boolean) RETURNS NUMERIC AS $$
 DECLARE
         cpte ALIAS FOR $1;        	--Numéro du compte
	date_param  ALIAS FOR $2;	--Date du solde
	idAgc ALIAS FOR $3;		-- id de l'agence
	compartiment ALIAS FOR $4;	-- dans quelle partie du bilan on veut obtenir le solde
	cv ALIAS FOR $5;		--convertir le montant dans la devise de reference
	is_consolide ALIAS FOR $6;	-- vrai si on veut calucler un solde consolidé
	solde_bilan NUMERIC(30,6):=0;	--Solde du compte :à l'ACTIF (compartiment=1) ou au PASSIF (compartiment=2)


	BEGIN


         select INTO solde_bilan sum( SoldeBilanNonRecursif(d.num_cpte_comptable,date_param,idAgc,compartiment,cv,is_consolide)) from ad_cpt_comptable d where id_ag = idAgc and ( (num_cpte_comptable = cpte) OR (num_cpte_comptable LIKE cpte||'.%') )   ;


	RETURN solde_bilan;
 END;
$$ LANGUAGE plpgsql;

-------------------------------- FIN : Ticket #568 : Anomalie bilan base Siège ----------------------------------