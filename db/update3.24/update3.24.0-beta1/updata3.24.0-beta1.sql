	CREATE OR REPLACE FUNCTION script_creation_menus_ecrans() RETURNS INT AS
$$
DECLARE
output_result INTEGER = 1;
tableliste_ident INTEGER = 0;

BEGIN

	-- Menus Abonnement service ATM
	IF NOT EXISTS(SELECT nom_menu FROM menus WHERE nom_menu='Abt') THEN
		INSERT INTO menus (nom_menu, libel_menu, nom_pere, pos_hierarch, ordre, is_menu,fonction, is_cliquable) VALUES ('Abt', maketraductionlangsyst('Gestion des abonnements ATM'), 'Gen-9', 5, 11, true, 13, true);
	END IF;

	IF NOT EXISTS(SELECT nom_menu FROM menus WHERE nom_menu='Abt-1') THEN
		INSERT INTO menus (nom_menu, libel_menu, nom_pere, pos_hierarch, ordre, is_menu,fonction, is_cliquable) VALUES ('Abt-1', maketraductionlangsyst('Liste des abonnements ATM'), 'Abt', 6, 1, false, 13, false);
	END IF;

	IF NOT EXISTS(SELECT nom_menu FROM menus WHERE nom_menu='Abt-2') THEN
		INSERT INTO menus (nom_menu, libel_menu, nom_pere, pos_hierarch, ordre, is_menu,fonction, is_cliquable)	VALUES ('Abt-2', maketraductionlangsyst('Inscripton abonnement'), 'Abt', 6, 2, false, 13, false);
	END IF;

	IF NOT EXISTS(SELECT nom_menu FROM menus WHERE nom_menu='Abt-3') THEN
		INSERT INTO menus (nom_menu, libel_menu, nom_pere, pos_hierarch, ordre, is_menu,fonction, is_cliquable) VALUES ('Abt-3', maketraductionlangsyst('Modification abonnement'), 'Abt', 6, 3, false, 13, false);
	END IF;

	IF NOT EXISTS(SELECT nom_menu FROM menus WHERE nom_menu='Abt-4') THEN
		INSERT INTO menus (nom_menu, libel_menu, nom_pere, pos_hierarch, ordre, is_menu,fonction, is_cliquable) VALUES ('Abt-4', maketraductionlangsyst('Confirmation abonnement'), 'Abt', 6, 4, false, 13, false);
	END IF;


	-- Ecrans
	IF NOT EXISTS (SELECT nom_ecran FROM ecrans WHERE nom_ecran='Abt-1') THEN
		INSERT INTO ecrans(nom_ecran, fichier, nom_menu, fonction) VALUES ('Abt-1', 'modules/clients/abonnement_atm.php', 'Abt-1', 13);
	END IF;

	IF NOT EXISTS (SELECT nom_ecran FROM ecrans WHERE nom_ecran='Abt-2') THEN
		INSERT INTO ecrans(nom_ecran, fichier, nom_menu, fonction) VALUES ('Abt-2', 'modules/clients/abonnement_atm.php', 'Abt-2', 13);
	END IF;

	IF NOT EXISTS (SELECT nom_ecran FROM ecrans WHERE nom_ecran='Abt-3') THEN
		INSERT INTO ecrans(nom_ecran, fichier, nom_menu, fonction) VALUES ('Abt-3', 'modules/clients/abonnement_atm.php', 'Abt-3', 13);
	END IF;

	IF NOT EXISTS (SELECT nom_ecran FROM ecrans WHERE nom_ecran='Abt-4') THEN
		INSERT INTO ecrans(nom_ecran, fichier, nom_menu, fonction) VALUES ('Abt-4', 'modules/clients/abonnement_atm.php', 'Abt-4', 13);
	END IF;


	-- Menus Commande de carte ATM
	IF NOT EXISTS(SELECT nom_menu FROM menus WHERE nom_menu='Caa') THEN
		INSERT INTO menus (nom_menu, libel_menu, nom_pere, pos_hierarch, ordre, is_menu,fonction, is_cliquable) VALUES ('Caa', maketraductionlangsyst('Commande carte ATM'), 'Gen-4', 5, 6, true, 46, true);
	END IF;

	IF NOT EXISTS(SELECT nom_menu FROM menus WHERE nom_menu='Caa-1') THEN
		INSERT INTO menus (nom_menu, libel_menu, nom_pere, pos_hierarch, ordre, is_menu,fonction, is_cliquable) VALUES ('Caa-1', maketraductionlangsyst('Sélection compte'), 'Caa', 6, 1, false, 46, false);
	END IF;

	IF NOT EXISTS(SELECT nom_menu FROM menus WHERE nom_menu='Caa-2') THEN
		INSERT INTO menus (nom_menu, libel_menu, nom_pere, pos_hierarch, ordre, is_menu,fonction, is_cliquable) VALUES ('Caa-2', maketraductionlangsyst('Confirmation'), 'Caa', 6, 2, false, 46, false);
	END IF;


	--Ecrans
	IF NOT EXISTS (SELECT nom_ecran FROM ecrans WHERE nom_ecran='Caa-1') THEN
		INSERT INTO ecrans(nom_ecran, fichier, nom_menu, fonction) VALUES ('Caa-1', 'modules/clients/commande_carte_atm.php', 'Caa-1', 46);
	END IF;

	IF NOT EXISTS (SELECT nom_ecran FROM ecrans WHERE nom_ecran='Caa-2') THEN
		INSERT INTO ecrans(nom_ecran, fichier, nom_menu, fonction) VALUES ('Caa-2', 'modules/clients/commande_carte_atm.php', 'Caa-2', 46);
	END IF;


	--Creation nouveau main menu + side menus :Gestion des cartes ATM : 198
	IF NOT EXISTS (select * from menus where nom_menu = 'Gca') THEN
		--insertion code
		INSERT INTO menus (nom_menu, libel_menu, nom_pere, pos_hierarch, ordre, is_menu,fonction, is_cliquable)
		VALUES ('Gca', maketraductionlangsyst('Gestion des cartes ATM'), 'Gen-6', 3, 17, true, 807, true);
		RAISE NOTICE 'Main Menu created!';
	END IF;

	----------------ecrans
	IF NOT EXISTS (select * from ecrans where nom_ecran = 'Gca-1') THEN
	 --insertion code
	 INSERT INTO ecrans(nom_ecran, fichier, nom_menu, fonction)
	 VALUES ('Gca-1', 'modules/guichet/gestion_carte_atm.php', 'Gca', 807);
	END IF;


	-- Menus Liste des commandes a envoyer pour impression
	IF NOT EXISTS(SELECT nom_menu FROM menus WHERE nom_menu='Lci') THEN
		INSERT INTO menus (nom_menu, libel_menu, nom_pere, pos_hierarch, ordre, is_menu,fonction, is_cliquable) VALUES ('Lci', maketraductionlangsyst('Listes des commandes à envoyer pour impression'), 'Gca', 4, 1, true, 808, true);
	END IF;

	IF NOT EXISTS(SELECT nom_menu FROM menus WHERE nom_menu='Lci-1') THEN
		INSERT INTO menus (nom_menu, libel_menu, nom_pere, pos_hierarch, ordre, is_menu,fonction, is_cliquable) VALUES ('Lci-1', maketraductionlangsyst('Liste des commandes'), 'Lci', 5, 1, false, 808, false);
	END IF;

	IF NOT EXISTS(SELECT nom_menu FROM menus WHERE nom_menu='Lci-2') THEN
		INSERT INTO menus (nom_menu, libel_menu, nom_pere, pos_hierarch, ordre, is_menu,fonction, is_cliquable) VALUES ('Lci-2', maketraductionlangsyst('Export CSV'), 'Lci', 5, 2, false, 808, false);
	END IF;


	-- Ecrans Liste des commandes a envoyer pour impression
	IF NOT EXISTS (SELECT nom_ecran FROM ecrans WHERE nom_ecran='Lci-1') THEN
		INSERT INTO ecrans(nom_ecran, fichier, nom_menu, fonction) VALUES ('Lci-1', 'modules/guichet/liste_carte_imprimer.php', 'Lci-1', 808);
	END IF;

	IF NOT EXISTS (SELECT nom_ecran FROM ecrans WHERE nom_ecran='Lci-2') THEN
		INSERT INTO ecrans(nom_ecran, fichier, nom_menu, fonction) VALUES ('Lci-2', 'modules/guichet/liste_carte_imprimer.php', 'Lci-2', 808);
	END IF;


	-- Menus Import des cartes imprimées
	IF NOT EXISTS(SELECT nom_menu FROM menus WHERE nom_menu='Ici') THEN
		INSERT INTO menus (nom_menu, libel_menu, nom_pere, pos_hierarch, ordre, is_menu,fonction, is_cliquable) VALUES ('Ici', maketraductionlangsyst('Liste des cartes imprimées à importer'), 'Gca', 4, 2, true, 809, true);
	END IF;

	IF NOT EXISTS(SELECT nom_menu FROM menus WHERE nom_menu='Ici-1') THEN
		INSERT INTO menus (nom_menu, libel_menu, nom_pere, pos_hierarch, ordre, is_menu,fonction, is_cliquable) VALUES ('Ici-1', maketraductionlangsyst('Import CSV'), 'Ici', 5, 1, false, 809, false);
	END IF;

	IF NOT EXISTS(SELECT nom_menu FROM menus WHERE nom_menu='Ici-2') THEN
		INSERT INTO menus (nom_menu, libel_menu, nom_pere, pos_hierarch, ordre, is_menu,fonction, is_cliquable) VALUES ('Ici-2', maketraductionlangsyst('Liste des cartes imprimées à importer'), 'Ici', 5, 2, false, 809, false);
	END IF;

	IF NOT EXISTS(SELECT nom_menu FROM menus WHERE nom_menu='Ici-3') THEN
		INSERT INTO menus (nom_menu, libel_menu, nom_pere, pos_hierarch, ordre, is_menu,fonction, is_cliquable) VALUES ('Ici-3', maketraductionlangsyst('Confirmation des cartes imprimées'), 'Ici', 5, 3, false, 809, false);
	END IF;


	-- Ecrans Liste des commandes a envoyer pour impression
	IF NOT EXISTS (SELECT nom_ecran FROM ecrans WHERE nom_ecran='Ici-1') THEN
		INSERT INTO ecrans(nom_ecran, fichier, nom_menu, fonction) VALUES ('Ici-1', 'modules/guichet/import_carte_imprimer.php', 'Ici-1', 809);
	END IF;

	IF NOT EXISTS (SELECT nom_ecran FROM ecrans WHERE nom_ecran='Ici-2') THEN
		INSERT INTO ecrans(nom_ecran, fichier, nom_menu, fonction) VALUES ('Ici-2', 'modules/guichet/import_carte_imprimer.php', 'Ici-2', 809);
	END IF;

	IF NOT EXISTS (SELECT nom_ecran FROM ecrans WHERE nom_ecran='Ici-3') THEN
		INSERT INTO ecrans(nom_ecran, fichier, nom_menu, fonction) VALUES ('Ici-3', 'modules/guichet/import_carte_imprimer.php', 'Ici-3', 809);
	END IF;



	-- Menus Historisation des imports
	IF NOT EXISTS(SELECT nom_menu FROM menus WHERE nom_menu='Hic') THEN
		INSERT INTO menus (nom_menu, libel_menu, nom_pere, pos_hierarch, ordre, is_menu,fonction, is_cliquable) VALUES ('Hic', maketraductionlangsyst('Historique des commandes envoyées'), 'Gca', 4, 3, true, 810, true);
	END IF;

	IF NOT EXISTS(SELECT nom_menu FROM menus WHERE nom_menu='Hic-1') THEN
		INSERT INTO menus (nom_menu, libel_menu, nom_pere, pos_hierarch, ordre, is_menu,fonction, is_cliquable) VALUES ('Hic-1', maketraductionlangsyst('Liste exports commandes'), 'Hic', 5, 1, false, 810, false);
	END IF;

	IF NOT EXISTS(SELECT nom_menu FROM menus WHERE nom_menu='Hic-2') THEN
		INSERT INTO menus (nom_menu, libel_menu, nom_pere, pos_hierarch, ordre, is_menu,fonction, is_cliquable) VALUES ('Hic-2', maketraductionlangsyst('Export CSV'), 'Hic', 5, 2, false, 810, false);
	END IF;


	IF NOT EXISTS (SELECT nom_ecran FROM ecrans WHERE nom_ecran='Hic-1') THEN
		INSERT INTO ecrans(nom_ecran, fichier, nom_menu, fonction) VALUES ('Hic-1', 'modules/guichet/historique_commande_export.php', 'Hic-1', 810);
	END IF;

	IF NOT EXISTS (SELECT nom_ecran FROM ecrans WHERE nom_ecran='Hic-2') THEN
		INSERT INTO ecrans(nom_ecran, fichier, nom_menu, fonction) VALUES ('Hic-2', 'modules/guichet/historique_commande_export.php', 'Hic-1', 810);
	END IF;



		-- Menus Liste des cartes
	IF NOT EXISTS(SELECT nom_menu FROM menus WHERE nom_menu='Ldc') THEN
		INSERT INTO menus (nom_menu, libel_menu, nom_pere, pos_hierarch, ordre, is_menu,fonction, is_cliquable) VALUES ('Ldc', maketraductionlangsyst('Liste de toutes les cartes'), 'Gca', 4, 4, true, 811, true);
	END IF;

	IF NOT EXISTS(SELECT nom_menu FROM menus WHERE nom_menu='Ldc-1') THEN
		INSERT INTO menus (nom_menu, libel_menu, nom_pere, pos_hierarch, ordre, is_menu,fonction, is_cliquable) VALUES ('Ldc-1', maketraductionlangsyst('Critères de recherche'), 'Ldc', 5, 1, false, 811, false);
	END IF;

	IF NOT EXISTS(SELECT nom_menu FROM menus WHERE nom_menu='Ldc-2') THEN
		INSERT INTO menus (nom_menu, libel_menu, nom_pere, pos_hierarch, ordre, is_menu,fonction, is_cliquable) VALUES ('Ldc-2', maketraductionlangsyst('Export CSV'), 'Ldc', 5, 2, false, 811, false);
	END IF;


	IF NOT EXISTS (SELECT nom_ecran FROM ecrans WHERE nom_ecran='Ldc-1') THEN
		INSERT INTO ecrans(nom_ecran, fichier, nom_menu, fonction) VALUES ('Ldc-1', 'modules/guichet/liste_all_cartes.php', 'Ldc-1', 811);
	END IF;

	IF NOT EXISTS (SELECT nom_ecran FROM ecrans WHERE nom_ecran='Ldc-2') THEN
		INSERT INTO ecrans(nom_ecran, fichier, nom_menu, fonction) VALUES ('Ldc-2', 'modules/guichet/liste_all_cartes.php', 'Ldc-2', 811);
	END IF;

		IF NOT EXISTS (SELECT nom_ecran FROM ecrans WHERE nom_ecran='Ldc-3') THEN
		INSERT INTO ecrans(nom_ecran, fichier, nom_menu, fonction) VALUES ('Ldc-3', 'modules/guichet/liste_all_cartes.php', 'Ldc-2', 811);
	END IF;



	-- Menus Retrait de carte ATM
	IF NOT EXISTS(SELECT nom_menu FROM menus WHERE nom_menu='Rct') THEN
		INSERT INTO menus (nom_menu, libel_menu, nom_pere, pos_hierarch, ordre, is_menu,fonction, is_cliquable) VALUES ('Rct', maketraductionlangsyst('Retrait carte ATM'), 'Gen-4', 5, 7, true, 47, true);
	END IF;

	IF NOT EXISTS(SELECT nom_menu FROM menus WHERE nom_menu='Rct-1') THEN
		INSERT INTO menus (nom_menu, libel_menu, nom_pere, pos_hierarch, ordre, is_menu,fonction, is_cliquable) VALUES ('Rct-1', maketraductionlangsyst('Sélection compte'), 'Rct', 6, 1, false, 47, false);
	END IF;

	IF NOT EXISTS(SELECT nom_menu FROM menus WHERE nom_menu='Rct-2') THEN
		INSERT INTO menus (nom_menu, libel_menu, nom_pere, pos_hierarch, ordre, is_menu,fonction, is_cliquable) VALUES ('Rct-2', maketraductionlangsyst('Confirmation'), 'Rct', 6, 2, false, 47, false);
	END IF;

	IF NOT EXISTS(SELECT nom_menu FROM menus WHERE nom_menu='Rct-3') THEN
		INSERT INTO menus (nom_menu, libel_menu, nom_pere, pos_hierarch, ordre, is_menu,fonction, is_cliquable) VALUES ('Rct-3', maketraductionlangsyst('Validation'), 'Rct', 6, 3, false, 47, false);
	END IF;


	--Ecrans
	IF NOT EXISTS (SELECT nom_ecran FROM ecrans WHERE nom_ecran='Rct-1') THEN
		INSERT INTO ecrans(nom_ecran, fichier, nom_menu, fonction) VALUES ('Rct-1', 'modules/clients/retrait_carte_atm.php', 'Rct-1', 47);
	END IF;

	IF NOT EXISTS (SELECT nom_ecran FROM ecrans WHERE nom_ecran='Rct-2') THEN
		INSERT INTO ecrans(nom_ecran, fichier, nom_menu, fonction) VALUES ('Rct-2', 'modules/clients/retrait_carte_atm.php', 'Rct-2', 47);
	END IF;

	IF NOT EXISTS (SELECT nom_ecran FROM ecrans WHERE nom_ecran='Rct-3') THEN
		INSERT INTO ecrans(nom_ecran, fichier, nom_menu, fonction) VALUES ('Rct-3', 'modules/clients/retrait_carte_atm.php', 'Rct-3', 47);
	END IF;


	-- Menus gestion des cartes suspendues et desactives
	IF NOT EXISTS(SELECT nom_menu FROM menus WHERE nom_menu='Gcs') THEN
		INSERT INTO menus (nom_menu, libel_menu, nom_pere, pos_hierarch, ordre, is_menu,fonction, is_cliquable) VALUES ('Gcs', maketraductionlangsyst('Gestion des cartes suspendues/désactivées'), 'Gen-4', 5, 8, true, 48, true);
	END IF;

	IF NOT EXISTS(SELECT nom_menu FROM menus WHERE nom_menu='Gcs-1') THEN
		INSERT INTO menus (nom_menu, libel_menu, nom_pere, pos_hierarch, ordre, is_menu,fonction, is_cliquable) VALUES ('Gcs-1', maketraductionlangsyst('Liste des cartes'), 'Gcs', 6, 1, false, 48, false);
	END IF;

	IF NOT EXISTS(SELECT nom_menu FROM menus WHERE nom_menu='Gcs-2') THEN
		INSERT INTO menus (nom_menu, libel_menu, nom_pere, pos_hierarch, ordre, is_menu,fonction, is_cliquable) VALUES ('Gcs-2', maketraductionlangsyst('Details cartes'), 'Gcs', 6, 2, false, 48, false);
	END IF;

	IF NOT EXISTS(SELECT nom_menu FROM menus WHERE nom_menu='Gcs-3') THEN
		INSERT INTO menus (nom_menu, libel_menu, nom_pere, pos_hierarch, ordre, is_menu,fonction, is_cliquable) VALUES ('Gcs-3', maketraductionlangsyst('Confirmation carte'), 'Gcs', 6, 3, false, 48, false);
	END IF;



	IF NOT EXISTS (SELECT nom_ecran FROM ecrans WHERE nom_ecran='Gcs-1') THEN
		INSERT INTO ecrans(nom_ecran, fichier, nom_menu, fonction) VALUES ('Gcs-1', 'modules/clients/gestion_carte_suspendues_desactivees.php', 'Gcs-1', 48);
	END IF;

	IF NOT EXISTS (SELECT nom_ecran FROM ecrans WHERE nom_ecran='Gcs-2') THEN
		INSERT INTO ecrans(nom_ecran, fichier, nom_menu, fonction) VALUES ('Gcs-2', 'modules/clients/gestion_carte_suspendues_desactivees.php', 'Gcs-2', 48);
	END IF;

	IF NOT EXISTS (SELECT nom_ecran FROM ecrans WHERE nom_ecran='Gcs-3') THEN
		INSERT INTO ecrans(nom_ecran, fichier, nom_menu, fonction) VALUES ('Gcs-3', 'modules/clients/gestion_carte_suspendues_desactivees.php', 'Gcs-3', 48);
	END IF;

	-- Menus Rapports
	IF NOT EXISTS(SELECT nom_menu FROM menus WHERE nom_menu='Rat') THEN
		INSERT INTO menus (nom_menu, libel_menu, nom_pere, pos_hierarch, ordre, is_menu,fonction, is_cliquable) VALUES ('Rat', maketraductionlangsyst('Liste des rapports ATM'), 'Gca', 4, 5, true, 812, true);
	END IF;

	IF NOT EXISTS(SELECT nom_menu FROM menus WHERE nom_menu='Rat-1') THEN
		INSERT INTO menus (nom_menu, libel_menu, nom_pere, pos_hierarch, ordre, is_menu,fonction, is_cliquable) VALUES ('Rat-1', maketraductionlangsyst('Choix rapports'), 'Rat', 5, 1, false, 812, false);
	END IF;

	IF NOT EXISTS(SELECT nom_menu FROM menus WHERE nom_menu='Rat-2') THEN
		INSERT INTO menus (nom_menu, libel_menu, nom_pere, pos_hierarch, ordre, is_menu,fonction, is_cliquable) VALUES ('Rat-2', maketraductionlangsyst('Personnalisation rapport'), 'Rat', 5, 2, false, 812, false);
	END IF;

	IF NOT EXISTS(SELECT nom_menu FROM menus WHERE nom_menu='Rat-3') THEN
		INSERT INTO menus (nom_menu, libel_menu, nom_pere, pos_hierarch, ordre, is_menu,fonction, is_cliquable) VALUES ('Rat-3', maketraductionlangsyst('Export données'), 'Rat', 5, 3, false, 812, false);
	END IF;



	IF NOT EXISTS (SELECT nom_ecran FROM ecrans WHERE nom_ecran='Rat-1') THEN
		INSERT INTO ecrans(nom_ecran, fichier, nom_menu, fonction) VALUES ('Rat-1', 'modules/guichet/rapport_atm.php', 'Rat-1', 812);
	END IF;

	IF NOT EXISTS (SELECT nom_ecran FROM ecrans WHERE nom_ecran='Rat-2') THEN
		INSERT INTO ecrans(nom_ecran, fichier, nom_menu, fonction) VALUES ('Rat-2', 'modules/guichet/rapport_atm.php', 'Rat-2', 812);
	END IF;

		IF NOT EXISTS (SELECT nom_ecran FROM ecrans WHERE nom_ecran='Rat-3') THEN
		INSERT INTO ecrans(nom_ecran, fichier, nom_menu, fonction) VALUES ('Rat-3', 'modules/guichet/rapport_atm.php', 'Rat-2', 812);
	END IF;




	RETURN output_result;

END;
$$
LANGUAGE plpgsql;

SELECT script_creation_menus_ecrans();
DROP FUNCTION script_creation_menus_ecrans();


--------------------------------------------------------Debut Ticket ticket_AT_169----------------------------------------------
CREATE OR REPLACE FUNCTION script_creation_table()
  RETURNS INT AS
$$
DECLARE
output_result INTEGER := 1;
tableliste_ident INTEGER :=0;
d_tableliste_str INTEGER :=0;

BEGIN

IF NOT EXISTS(SELECT * FROM information_schema.tables WHERE table_name = 'ad_carte_atm') THEN

create table ad_carte_atm
(	id_carte serial NOT NULL,
	ref_no varchar(250),
	id_client integer,
	id_prestataire integer,
	id_cpte integer,
	nom_sur_carte text,
	etat_carte integer,
	num_carte_atm varchar(250),
	motif_demande	integer,
	date_carte_debut_validite	timestamp without time zone,
	date_carte_expiration	timestamp without time zone,
	date_demande	timestamp without time zone,
	date_envoi_impression	timestamp without time zone,
	date_livraison	timestamp without time zone,
	date_activation	timestamp without time zone,
	date_suspension	timestamp without time zone,
	motif_suspension	integer,
	date_desactivation	timestamp without time zone,
	id_ag	integer,
	id_export	integer,
	id_import	integer,
	date_creation	timestamp without time zone,
	date_modif	timestamp without time zone,
	CONSTRAINT ad_carte_atm_id_pkey PRIMARY KEY (id_carte, id_ag),
	CONSTRAINT ad_carte_atm_id_client_fkey FOREIGN KEY (id_client,id_ag) REFERENCES ad_cli (id_client,id_ag) MATCH SIMPLE ON UPDATE NO ACTION ON DELETE NO ACTION,
	CONSTRAINT ad_carte_atm_id_cpte_fkey FOREIGN KEY (id_cpte,id_ag) REFERENCES ad_cpt (id_cpte,id_ag) MATCH SIMPLE ON UPDATE NO ACTION ON DELETE NO ACTION
	);

END IF;


IF NOT EXISTS(SELECT * FROM information_schema.tables WHERE table_name = 'ad_carte_atm_his') THEN

create table ad_carte_atm_his (
	id	serial NOT NULL,
	id_carte	integer,
	etat_carte	integer,
	date_etat	timestamp without time zone,
	commentaires	text,
	id_ag	integer,
	date_creation	timestamp without time zone,
	CONSTRAINT ad_carte_atm_his_id_pkey PRIMARY KEY (id, id_ag),
	CONSTRAINT ad_carte_atm_his_id_carte_fkey FOREIGN KEY (id_carte,id_ag) REFERENCES ad_carte_atm (id_carte,id_ag) MATCH SIMPLE ON UPDATE NO ACTION ON DELETE NO ACTION
	);
END IF;

IF NOT EXISTS(SELECT * FROM information_schema.tables WHERE table_name = 'ad_commande_carte_his') THEN
create table ad_commande_carte_his (
	id	serial NOT NULL,
	type	integer,
	date_traitement	timestamp without time zone,
	nom_interne	varchar(250),
	chemin_fichier	text,
	nbre_cartes	integer,
	ref_externe	text,
	id_ag	integer,
	id_his integer,
	date_creation	timestamp without time zone,
	CONSTRAINT ad_commande_carte_his_id_pkey PRIMARY KEY (id, id_ag)
	);
END IF;



IF NOT EXISTS(SELECT * FROM information_schema.tables WHERE table_name = 'ad_transactions_atm_his') THEN
create table ad_transactions_atm_his (
	id	serial NOT NULL,
	id_carte	integer,
	id_client	integer,
	date_comptable	timestamp without time zone,
	compte_comptable	varchar(250),
	cpte_interne_cli	integer,
	montant	numeric(30,0),
	id_mouvement	text,
	id_his	integer,
	sens	varchar(250),
	devise	text,
	type_operation	integer,
	libel_ecriture	integer,
	id_ag	integer,
	date_creation	timestamp without time zone,
	CONSTRAINT ad_transactions_atm_his_id_pkey PRIMARY KEY (id, id_ag),
	CONSTRAINT ad_transactions_atm_his_id_carte_fkey FOREIGN KEY (id_carte,id_ag) REFERENCES ad_carte_atm (id_carte,id_ag) MATCH SIMPLE ON UPDATE NO ACTION ON DELETE NO ACTION,
	CONSTRAINT ad_transactions_atm_his_id_client_fkey FOREIGN KEY (id_client,id_ag) REFERENCES ad_cli (id_client,id_ag) MATCH SIMPLE ON UPDATE NO ACTION ON DELETE NO ACTION
	);
END IF;

IF NOT EXISTS(SELECT * FROM information_schema.tables WHERE table_name = 'ad_abonnement_atm') THEN
create table ad_abonnement_atm (
	id_abonnement	serial NOT NULL,
	id_cpte	integer,
	id_client	integer,
	identifiant_client	varchar(250),
	id_carte	integer	,
	num_carte_atm	varchar(250),
	statut	integer	,
	id_ag	integer	,
	date_creation	timestamp without time zone	,
	date_modif	timestamp without time zone,
	CONSTRAINT ad_abonnement_atm_id_pkey PRIMARY KEY (id_abonnement, id_ag),
	CONSTRAINT ad_abonnement_atm_id_cpte_fkey FOREIGN KEY (id_cpte,id_ag) REFERENCES ad_cpt (id_cpte,id_ag) MATCH SIMPLE ON UPDATE NO ACTION ON DELETE NO ACTION,
	CONSTRAINT ad_abonnement_atm_id_client_fkey FOREIGN KEY (id_client,id_ag) REFERENCES ad_cli (id_client,id_ag) MATCH SIMPLE ON UPDATE NO ACTION ON DELETE NO ACTION,
	CONSTRAINT ad_abonnement_atm_id_carte_fkey FOREIGN KEY (id_carte,id_ag) REFERENCES ad_carte_atm (id_carte,id_ag) MATCH SIMPLE ON UPDATE NO ACTION ON DELETE NO ACTION
	);
END IF;

-- column type_prestataire
IF NOT EXISTS(SELECT * FROM information_schema.columns WHERE table_name = 'ad_ewallet' AND column_name = 'type_prestataire') THEN
	ALTER TABLE ad_ewallet ADD COLUMN type_prestataire integer;
	select INTO tableliste_ident ident from tableliste where nomc like 'ad_ewallet' order by ident desc limit 1;
	d_tableliste_str := makeTraductionLangSyst('Type prestataire');
	INSERT INTO d_tableliste VALUES ((select max(ident) from d_tableliste)+1, tableliste_ident, 'type_prestataire', d_tableliste_str, true,null, 'int', NULL, NULL, false);
	IF EXISTS(SELECT langue FROM adsys_langues_systeme WHERE code = 'en_GB') THEN
		INSERT INTO ad_traductions VALUES (d_tableliste_str,'en_GB','Type of operator');
	END IF;
END IF;



-- Function: trig_insert_ad_carte_atm_his()

-- DROP FUNCTION trig_insert_ad_carte_atm_his();

CREATE OR REPLACE FUNCTION trig_insert_ad_carte_atm_his()
  RETURNS trigger AS
$BODY$
  BEGIN
    IF (OLD.etat_carte != NEW.etat_carte) THEN
		INSERT INTO ad_carte_atm_his (date_etat, id_carte, etat_carte, id_ag, date_creation)
		VALUES (NOW(), OLD.id_carte, OLD.etat_carte, OLD.id_ag, NOW());
	END IF;
    RETURN NEW;
  END;
	$BODY$
  LANGUAGE plpgsql VOLATILE
  COST 100;
ALTER FUNCTION trig_insert_ad_carte_atm_his()
  OWNER TO postgres;

DROP TRIGGER IF EXISTS  trig_before_update_ad_carte_atm ON ad_carte_atm;
CREATE TRIGGER trig_before_update_ad_carte_atm
  BEFORE UPDATE
  ON ad_carte_atm
  FOR EACH ROW
  EXECUTE PROCEDURE trig_insert_ad_carte_atm_his();

	RETURN output_result;

END;
$$
LANGUAGE plpgsql;

SELECT script_creation_table();
DROP FUNCTION script_creation_table();



  CREATE OR REPLACE FUNCTION update_mobile_banking_bswitch()
  RETURNS INT AS
$$
DECLARE
  output_result    INTEGER = 1;
  tableliste_ident INTEGER = 0;
BEGIN
  RAISE NOTICE 'START';

  IF NOT EXISTS(SELECT *
                FROM ad_ewallet
                WHERE nom_prestataire = 'BSWITCH' AND code_prestataire = 'BSWITCH_BI' AND id_ag = numagc())
  THEN
    INSERT INTO ad_ewallet (id_prestataire, id_ag, nom_prestataire, code_prestataire, compte_comptable)
    VALUES (4, numagc(), 'BSWITCH', 'BSWITCH_BI', NULL);
    output_result := 2;
  END IF;

 IF NOT EXISTS(SELECT *
                FROM ad_ewallet
                WHERE nom_prestataire = 'MTN' AND code_prestataire = 'MTN_RW' AND id_ag = numagc())
  THEN
  UPDATE ad_ewallet SET type_prestataire = 1 WHERE nom_prestataire = 'MTN' AND code_prestataire = 'MTN_RW';
  END IF;

  IF NOT EXISTS(SELECT *
                FROM ad_ewallet
                WHERE nom_prestataire = 'Airtel' AND code_prestataire = 'AIRTEL_RW' AND id_ag = numagc())
  THEN
  UPDATE ad_ewallet SET type_prestataire = 1 WHERE nom_prestataire = 'Airtel' AND code_prestataire = 'AIRTEL_RW';
  END IF;


   RAISE NOTICE 'END';
  RETURN output_result;

END;
$$
LANGUAGE plpgsql;

SELECT update_mobile_banking_bswitch();
DROP FUNCTION update_mobile_banking_bswitch();



CREATE OR REPLACE FUNCTION script_operation_compta() RETURNS INT AS
$$
DECLARE
output_result INTEGER = 1;
tableliste_ident INTEGER = 0;

BEGIN

	-- Création opération Frais activation abonnement ATM
	IF NOT EXISTS(SELECT * FROM ad_cpt_ope WHERE type_operation=189 AND categorie_ope = 1 AND id_ag = numagc()) THEN
		-- Frais activation abonnement ATM
		INSERT INTO ad_cpt_ope(type_operation, categorie_ope, id_ag, libel_ope)
		VALUES (189, 1, numagc(), maketraductionlangsyst('Frais activation abonnement ATM'));

		RAISE NOTICE 'Insertion type_operation 189 dans la table ad_cpt_ope effectuée';
		output_result := 2;
	END IF;

	IF NOT EXISTS(SELECT * FROM ad_cpt_ope_cptes WHERE type_operation=189 AND sens = 'd' AND categorie_cpte = 1 AND id_ag = numagc()) THEN
		INSERT INTO ad_cpt_ope_cptes(type_operation, num_cpte, sens, categorie_cpte, id_ag) VALUES (189, NULL, 'd', 1, numagc());

		RAISE NOTICE 'Insertion type_operation 189 sens ''d'' dans la table ad_cpt_ope_cptes effectuée';
		output_result := 2;
	END IF;

	IF NOT EXISTS(SELECT * FROM ad_cpt_ope_cptes WHERE type_operation=189 AND sens = 'c' AND categorie_cpte = 0 AND id_ag = numagc()) THEN
		INSERT INTO ad_cpt_ope_cptes(type_operation, num_cpte, sens, categorie_cpte, id_ag) VALUES (189, NULL, 'c', 0, numagc());

		RAISE NOTICE 'Insertion type_operation 189 sens ''c'' dans la table ad_cpt_ope_cptes effectuée';
		output_result := 2;
	END IF;

		-- Création opération Frais transaction ATM
	IF NOT EXISTS(SELECT * FROM ad_cpt_ope WHERE type_operation=190 AND categorie_ope = 1 AND id_ag = numagc()) THEN
		-- Frais transaction ATM
		INSERT INTO ad_cpt_ope(type_operation, categorie_ope, id_ag, libel_ope)
		VALUES (190, 1, numagc(), maketraductionlangsyst('Frais transaction ATM'));

		RAISE NOTICE 'Insertion type_operation 190 dans la table ad_cpt_ope effectuée';
		output_result := 2;
	END IF;

	IF NOT EXISTS(SELECT * FROM ad_cpt_ope_cptes WHERE type_operation=190 AND sens = 'd' AND categorie_cpte = 1 AND id_ag = numagc()) THEN
		INSERT INTO ad_cpt_ope_cptes(type_operation, num_cpte, sens, categorie_cpte, id_ag) VALUES (190, NULL, 'd', 1, numagc());

		RAISE NOTICE 'Insertion type_operation 190 sens ''d'' dans la table ad_cpt_ope_cptes effectuée';
		output_result := 2;
	END IF;

	IF NOT EXISTS(SELECT * FROM ad_cpt_ope_cptes WHERE type_operation=190 AND sens = 'c' AND categorie_cpte = 0 AND id_ag = numagc()) THEN
		INSERT INTO ad_cpt_ope_cptes(type_operation, num_cpte, sens, categorie_cpte, id_ag) VALUES (190, NULL, 'c', 0, numagc());

		RAISE NOTICE 'Insertion type_operation 190 sens ''c'' dans la table ad_cpt_ope_cptes effectuée';
		output_result := 2;
	END IF;


		-- Création opération Retrait ATM
	IF NOT EXISTS(SELECT * FROM ad_cpt_ope WHERE type_operation=191 AND categorie_ope = 1 AND id_ag = numagc()) THEN
		-- Retrait ATM
		INSERT INTO ad_cpt_ope(type_operation, categorie_ope, id_ag, libel_ope)
		VALUES (191, 1, numagc(), maketraductionlangsyst('Retrait ATM'));

		RAISE NOTICE 'Insertion type_operation 191 dans la table ad_cpt_ope effectuée';
		output_result := 2;
	END IF;

	IF NOT EXISTS(SELECT * FROM ad_cpt_ope_cptes WHERE type_operation=191 AND sens = 'd' AND categorie_cpte = 1 AND id_ag = numagc()) THEN
		INSERT INTO ad_cpt_ope_cptes(type_operation, num_cpte, sens, categorie_cpte, id_ag) VALUES (191, NULL, 'd', 1, numagc());

		RAISE NOTICE 'Insertion type_operation 191 sens ''d'' dans la table ad_cpt_ope_cptes effectuée';
		output_result := 2;
	END IF;

	IF NOT EXISTS(SELECT * FROM ad_cpt_ope_cptes WHERE type_operation=191 AND sens = 'c' AND categorie_cpte = 28 AND id_ag = numagc()) THEN
		INSERT INTO ad_cpt_ope_cptes(type_operation, num_cpte, sens, categorie_cpte, id_ag) VALUES (191, NULL, 'c', 28, numagc());

		RAISE NOTICE 'Insertion type_operation 191 sens ''c'' dans la table ad_cpt_ope_cptes effectuée';
		output_result := 2;
	END IF;



	IF NOT EXISTS(SELECT * FROM ad_cpt_ope WHERE type_operation=192 AND categorie_ope = 1 AND id_ag = numagc()) THEN
    -- Revirement ATM
    INSERT INTO ad_cpt_ope(type_operation, categorie_ope, id_ag, libel_ope) VALUES (192, 1, numagc(), maketraductionlangsyst('Revirement ATM'));

    RAISE NOTICE 'Insertion type_operation 192 dans la table ad_cpt_ope effectuée';
    output_result := 2;
  END IF;

  IF NOT EXISTS(SELECT * FROM ad_cpt_ope_cptes WHERE type_operation=192 AND sens = 'd' AND categorie_cpte = 28 AND id_ag = numagc()) THEN
    INSERT INTO ad_cpt_ope_cptes(type_operation, num_cpte, sens, categorie_cpte, id_ag) VALUES (192, NULL, 'd', 28, numagc());

    RAISE NOTICE 'Insertion type_operation 192 sens ''d'' dans la table ad_cpt_ope_cptes effectuée';
    output_result := 2;
  END IF;

  IF NOT EXISTS(SELECT * FROM ad_cpt_ope_cptes WHERE type_operation=192 AND sens = 'c' AND categorie_cpte = 1 AND id_ag = numagc()) THEN
    INSERT INTO ad_cpt_ope_cptes(type_operation, num_cpte, sens, categorie_cpte, id_ag) VALUES (192, NULL, 'c', 1, numagc());

    RAISE NOTICE 'Insertion type_operation 192 sens ''c'' dans la table ad_cpt_ope_cptes effectuée';
    output_result := 2;
  END IF;

	-- Insertion dans la table tarification : Frais abonnement ATM
	  --Creation du frais
	  IF NOT EXISTS (SELECT * FROM adsys_tarification where id_tarification = 15) THEN
	    INSERT INTO adsys_tarification (id_tarification, code_abonnement, type_de_frais, mode_frais, valeur, compte_comptable, date_debut_validite, date_fin_validite, statut, id_ag) VALUES (15, 'atm', 'ATM_REG', '1', '0', null, null, null, 't', numagc());
	  END IF;

	  RAISE NOTICE 'Insertion type_frais SMS_FRAIS dans la table adsys_tarification effectuée';


	-- Insertion dans la table tarification : Frais activation service ATM
	  	  --Creation du frais
	  IF NOT EXISTS (SELECT * FROM adsys_tarification where id_tarification = 16) THEN
	    INSERT INTO adsys_tarification (id_tarification, code_abonnement, type_de_frais, mode_frais, valeur, compte_comptable, date_debut_validite, date_fin_validite, statut, id_ag) VALUES (16, 'atm', 'ATM_TSC', '1', '0', null, null, null, 't', numagc());
	  END IF;

	  RAISE NOTICE 'Insertion type_frais SMS_FRAIS dans la table adsys_tarification effectuée';


	RETURN output_result;

END;
$$
LANGUAGE plpgsql;

SELECT script_operation_compta();
DROP FUNCTION script_operation_compta();



	CREATE OR REPLACE FUNCTION f_getmouvementforproducer(
		IN text,
		IN numeric,
		IN text,
		IN integer,
		IN text)
		RETURNS TABLE(id_client integer, id_ag integer, id_cpte integer, id_transaction integer, id_mouvement integer, date_transaction timestamp without time zone, ref_ecriture text, type_opt integer, libelle_ecriture text, montant numeric, sens text, devise character, communication text, tireur text, donneur text, numero_cheque text, solde numeric, telephone character varying, langue integer, num_complet_cpte text, intitule_compte text, date_ouvert timestamp without time zone, statut_juridique integer, nom text, prenom text, libelle_produit text) AS
		$BODY$
		declare
		v_cpte_interne_cli ALIAS for $1;
		v_montant ALIAS for $2;
		v_date_valeur ALIAS for $3;
		v_id_ag ALIAS for $4;
		v_solde ALIAS for $5;

		BEGIN

		return query

		select
			case
			when h.id_client is null
				then cpt.id_titulaire
			else h.id_client
			end as id_client,
			m.id_ag,
			m.cpte_interne_cli as id_cpte,
			m.id_ecriture as id_transaction,
			m.id_mouvement,
			h.date as date_transaction,
			e.ref_ecriture,
			e.type_operation as type_opt,
			t.traduction as libelle_ecriture,
			m.montant,
			m.sens,
			m.devise,
			histo_ext.communication,
			case
			when h.type_fonction in (70,75)
				then histo_ext.tireur
			else null
			end as tireur,
			histo_ext.nom_client AS donneur,
			histo_ext.numero_cheque,
			cast(v_solde as NUMERIC) AS solde,
			a.num_sms as telephone,
			a.langue,
			cpt.num_complet_cpte,
			cpt.intitule_compte,
			cpt.date_ouvert as date_ouvert,
			c.statut_juridique,
			c.pp_nom as nom,
			c.pp_prenom as prenom,
			produit.libel as libelle_produit
		from
			ad_mouvement m
			inner join ad_ecriture e on e.id_ag=m.id_ag and e.id_ecriture=m.id_ecriture
			inner join ad_his h on h.id_ag=e.id_ag and h.id_his=e.id_his
			left join
			(select
				 ext.id_ag,
				 ext.id,
				 p.nom_client,
				 tb.denomination as tireur,
				 case
				 when ext.type_piece in (2,4,5,15)
					 then ext.num_piece
				 else null
				 end AS numero_cheque,
				 ext.communication
			 from
				 ad_his_ext ext
				 left join
				 (select
						pers.id_ag,pers.id_client,pers.id_pers_ext,
						COALESCE (CASE
											cli.statut_juridique
											WHEN '1'
												THEN pp_nom||' '||pp_prenom
											WHEN '2'
												THEN pm_raison_sociale
											WHEN '3'
												THEN gi_nom
											WHEN '4'
												THEN gi_nom
											END, pers.denomination)  AS nom_client
					FROM ad_pers_ext pers
						left join  ad_cli cli on cli.id_ag = pers.id_ag and cli.id_client = pers.id_client) p on ext.id_ag  = p.id_ag and ext.id_pers_ext = p.id_pers_ext
				 left join tireur_benef tb on ext.id_tireur_benef = tb.id and ext.id_ag = tb.id_ag
			) histo_ext on histo_ext.id_ag=h.id_ag and h.id_his_ext = histo_ext.id
			inner join ad_traductions t on t.id_str =e.libel_ecriture
			inner join ad_cpt cpt on m.id_ag = cpt.id_ag and m.cpte_interne_cli = cpt.id_cpte
			inner join ad_abonnement a ON cpt.id_titulaire = a.id_client AND cpt.id_ag = a.id_ag
			inner join ad_cli c ON a.id_client = c.id_client AND a.id_ag = c.id_ag
			inner join adsys_produit_epargne produit ON cpt.id_prod = produit.id AND cpt.id_ag = produit.id_ag
		where
			cpt.id_prod NOT IN (3,4)
			and
			h.id_his =
			(
				SELECT h.id_his
				FROM ad_mouvement m
					INNER JOIN ad_ecriture e ON m.id_ecriture = e.id_ecriture AND m.id_ag = e.id_ag
					INNER JOIN ad_his h ON e.id_his = h.id_his AND h.id_ag = e.id_ag
				WHERE m.cpte_interne_cli = cast(v_cpte_interne_cli as INTEGER)
							AND m.montant = v_montant
							AND m.date_valeur = to_date(v_date_valeur, 'yyyy-MM-dd')
							AND h.id_ag = v_id_ag
				ORDER BY h.date DESC
				LIMIT 1
			)
			and
			m.cpte_interne_cli = cast(v_cpte_interne_cli as INTEGER)
			and
			m.montant = v_montant
			and
			m.date_valeur = to_date(v_date_valeur, 'yyyy-MM-dd')
			and
			a.deleted = FALSE
			and
			a.id_service = 1;
		end;
		$BODY$
		LANGUAGE plpgsql VOLATILE
		COST 100
		ROWS 1000;
		ALTER FUNCTION f_getmouvementforproducer(text, numeric, text, integer, text)
	OWNER TO postgres;

	CREATE OR REPLACE FUNCTION f_getmouvementforproducerarretecomptebatch(
		IN integer,
		IN integer,
		IN numeric,
		IN timestamp without time zone)
		RETURNS TABLE(id_client integer, telephone character varying, langue integer, num_complet_cpte text, intitule_compte text, date_ouvert timestamp without time zone, nom text, prenom text, statut_juridique integer, libelle_produit text, id_ag integer, id_cpte integer, id_transaction integer, id_mouvement integer, montant numeric, sens text, devise character, ref_ecriture text, type_opt integer, libelle_ecriture text, solde numeric, date_transaction timestamp without time zone) AS
		$BODY$
		DECLARE
		v_id_mouvement ALIAS FOR $1;
		v_id_ag ALIAS FOR $2;
		v_solde ALIAS FOR $3;
		v_date_transaction ALIAS FOR $4;
		BEGIN

		RETURN QUERY
		with adm as
		(SELECT
		m.id_ag,
		m.cpte_interne_cli AS id_cpte,
		m.id_ecriture AS id_transaction,
		m.id_mouvement,
		m.id_ecriture,
		m.montant,
		m.sens,
		m.devise
		FROM ad_mouvement m
		WHERE m.id_mouvement = v_id_mouvement
		),

		ade as
		(SELECT
		e.*
		FROM ad_ecriture e
		join adm on e.id_ecriture = adm.id_ecriture
		),

		adt as
		(SELECT
		traduction,
		id_str
		FROM ad_traductions t
		join ade on t.id_str = ade.libel_ecriture
		)

		SELECT
			cpt.id_titulaire AS id_client,
			a.num_sms AS telephone,
			a.langue,
			cpt.num_complet_cpte,
			cpt.intitule_compte,
			cpt.date_ouvert AS date_ouvert,
			c.pp_nom AS nom,
			c.pp_prenom AS prenom,
			c.statut_juridique,
			produit.libel AS libelle_produit,
			adm.id_ag,
			adm.id_cpte,
			adm.id_transaction,
			adm.id_mouvement,
			adm.montant,
			adm.sens,
			adm.devise,
			ade.ref_ecriture,
			ade.type_operation AS type_opt,
			adt.traduction AS libelle_ecriture,
			v_solde AS solde,
			v_date_transaction AS date_transaction
		FROM ad_cpt cpt
			INNER JOIN ad_abonnement a ON cpt.id_titulaire = a.id_client AND cpt.id_ag = a.id_ag
			INNER JOIN ad_cli c ON a.id_client = c.id_client AND a.id_ag = c.id_ag
			INNER JOIN adsys_produit_epargne produit ON cpt.id_prod = produit.id AND cpt.id_ag = produit.id_ag
			INNER JOIN adm on cpt.id_cpte = adm.id_cpte and cpt.id_ag = adm.id_ag
			INNER JOIN ade on adm.id_transaction = ade.id_ecriture and adm.id_ag = ade.id_ag
			INNER JOIN adt on ade.libel_ecriture = adt.id_str
		WHERE cpt.id_cpte = adm.id_cpte
					AND cpt.id_ag = v_id_ag
					AND a.deleted = FALSE
					AND a.id_service = 1;
		END;
		$BODY$
		LANGUAGE plpgsql VOLATILE
		COST 100
		ROWS 1000;
	ALTER FUNCTION f_getmouvementforproducerarretecomptebatch(integer, integer, numeric, timestamp without time zone)
	OWNER TO postgres;

	CREATE OR REPLACE FUNCTION f_getmouvementforproducercloturecomptebatch(
			IN text,
			IN numeric,
			IN text,
			IN integer)
			RETURNS TABLE(id_client integer, id_ag integer, id_cpte integer, id_transaction integer, id_mouvement integer, date_transaction timestamp without time zone, ref_ecriture text, type_opt integer, libelle_ecriture text, montant numeric, sens text, devise character, communication text, tireur text, donneur text, numero_cheque text, solde numeric, telephone character varying, langue integer, num_complet_cpte text, intitule_compte text, date_ouvert timestamp without time zone, statut_juridique integer, nom text, prenom text, libelle_produit text) AS
			$BODY$
			declare
			v_cpte_interne_cli ALIAS for $1;
			v_montant ALIAS for $2;
			v_date_valeur ALIAS for $3;
			v_id_ag ALIAS for $4;

			BEGIN

			return query

			select
				case
				when h.id_client is null
					then cpt.id_titulaire
				else h.id_client
				end as id_client,
				m.id_ag,
				m.cpte_interne_cli as id_cpte,
				m.id_ecriture as id_transaction,
				m.id_mouvement,
				h.date as date_transaction,
				e.ref_ecriture,
				e.type_operation as type_opt,
				t.traduction as libelle_ecriture,
				m.montant,
				m.sens,
				m.devise,
				histo_ext.communication,
				case
				when h.type_fonction in (70,75)
					then histo_ext.tireur
				else null
				end as tireur,
				histo_ext.nom_client AS donneur,
				histo_ext.numero_cheque,
				cpt.solde AS solde,
				a.num_sms as telephone,
				a.langue,
				cpt.num_complet_cpte,
				cpt.intitule_compte,
				cpt.date_ouvert as date_ouvert,
				c.statut_juridique,
				c.pp_nom as nom,
				c.pp_prenom as prenom,
				produit.libel as libelle_produit
			from
				ad_mouvement m
				inner join ad_ecriture e on e.id_ag=m.id_ag and e.id_ecriture=m.id_ecriture
				inner join ad_his h on h.id_ag=e.id_ag and h.id_his=e.id_his
				left join
				(select
					 ext.id_ag,
					 ext.id,
					 p.nom_client,
					 tb.denomination as tireur,
					 case
					 when ext.type_piece in (2,4,5,15)
						 then ext.num_piece
					 else null
					 end AS numero_cheque,
					 ext.communication
				 from
					 ad_his_ext ext
					 left join
					 (select
							pers.id_ag,pers.id_client,pers.id_pers_ext,
							COALESCE (CASE
												cli.statut_juridique
												WHEN '1'
													THEN pp_nom||' '||pp_prenom
												WHEN '2'
													THEN pm_raison_sociale
												WHEN '3'
													THEN gi_nom
												WHEN '4'
													THEN gi_nom
												END, pers.denomination)  AS nom_client
						FROM ad_pers_ext pers
							left join  ad_cli cli on cli.id_ag = pers.id_ag and cli.id_client = pers.id_client) p on ext.id_ag  = p.id_ag and ext.id_pers_ext = p.id_pers_ext
					 left join tireur_benef tb on ext.id_tireur_benef = tb.id and ext.id_ag = tb.id_ag
				) histo_ext on histo_ext.id_ag=h.id_ag and h.id_his_ext = histo_ext.id
				inner join ad_traductions t on t.id_str =e.libel_ecriture
				inner join ad_cpt cpt on m.id_ag = cpt.id_ag and m.cpte_interne_cli = cpt.id_cpte
				inner join ad_abonnement a ON cpt.id_titulaire = a.id_client AND cpt.id_ag = a.id_ag
				inner join ad_cli c ON a.id_client = c.id_client AND a.id_ag = c.id_ag
				inner join adsys_produit_epargne produit ON cpt.id_prod = produit.id AND cpt.id_ag = produit.id_ag
			where
				cpt.id_prod NOT IN (3,4)
				and
				h.id_his =
				(
					SELECT h.id_his
					FROM ad_mouvement m
						INNER JOIN ad_ecriture e ON m.id_ecriture = e.id_ecriture AND m.id_ag = e.id_ag
						INNER JOIN ad_his h ON e.id_his = h.id_his AND h.id_ag = e.id_ag
					WHERE m.cpte_interne_cli = cast(v_cpte_interne_cli as INTEGER)
								AND m.montant = v_montant
								AND m.date_valeur = to_date(v_date_valeur, 'yyyy-MM-dd')
								AND h.id_ag = v_id_ag
					ORDER BY date_valeur DESC
					LIMIT 1
				)
				and
				m.cpte_interne_cli = cast(v_cpte_interne_cli as INTEGER)
				and
				m.montant = v_montant
				and
				m.date_valeur = to_date(v_date_valeur, 'yyyy-MM-dd')
				and
				a.deleted = FALSE
				and a.id_service = 1;
			end;
			$BODY$
			LANGUAGE plpgsql VOLATILE
			COST 100
			ROWS 1000;
			ALTER FUNCTION f_getmouvementforproducercloturecomptebatch(text, numeric, text, integer)
	OWNER TO postgres;

	CREATE OR REPLACE FUNCTION f_getremmoblendingdataforproducer(
			IN text,
			IN text,
			IN text)
			RETURNS TABLE(num_sms character varying, langue integer, id_transaction text, num_imf text) AS
			$BODY$
			declare
			v_id_client ALIAS for $1;
			v_id_doss ALIAS for $2;
			v_mnt_dem ALIAS for $3;

			BEGIN

			RETURN QUERY
			SELECT abn.num_sms, abn.langue, mldem.id_transaction, agc.tel
			FROM ad_abonnement abn
				INNER JOIN ml_demande_credit mldem ON abn.id_client = mldem.id_client
				INNER JOIN ad_agc agc ON abn.id_ag = agc.id_ag
			WHERE abn.deleted = 'f'
						AND abn.id_client = cast(v_id_client as INTEGER)
						AND mldem.id_doss = cast(v_id_doss as INTEGER)
						AND mldem.mnt_dem = cast(v_mnt_dem as NUMERIC)
						AND abn.id_service = 1;

			END;
			$BODY$
			LANGUAGE plpgsql VOLATILE
			COST 100
			ROWS 1000;
			ALTER FUNCTION f_getremmoblendingdataforproducer(text, text, text)
	OWNER TO postgres;