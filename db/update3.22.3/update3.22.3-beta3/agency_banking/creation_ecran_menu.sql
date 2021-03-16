CREATE OR REPLACE FUNCTION script_creation_menu_ecran_agb() RETURNS INT AS
$$
DECLARE
output_result INTEGER = 1;
  id_str_trad integer = 0;
  tableliste_ident INTEGER = 0;
  d_tableliste_str INTEGER =0;

BEGIN
	--========> Gestion operation en deplacee via agent
	--Main Menu
	IF NOT EXISTS (select * from menus where nom_menu = 'God') THEN
	 id_str_trad := maketraductionlangsyst('Opération en déplacée via agent');
	 INSERT INTO menus (nom_menu, libel_menu, nom_pere, pos_hierarch, ordre, is_menu,fonction, is_cliquable)
	 VALUES ('God', id_str_trad, 'Gen-16', 3, 1, TRUE, 773, TRUE);
	 IF EXISTS(SELECT langue FROM adsys_langues_systeme WHERE code = 'en_GB') THEN
	  INSERT INTO ad_traductions (id_str, langue, traduction) VALUES (id_str_trad, 'en_GB', 'Agent remote operation');
	 END IF;
	END IF;


	----Ecran Agence
	IF NOT EXISTS (select * from ecrans where nom_ecran = 'God-1') THEN
	 INSERT INTO ecrans(nom_ecran, fichier, nom_menu, fonction)
	 VALUES ('God-1', 'modules/agency_banking/operation_deplace_via_agent.php', 'God', 773);
	END IF;


	----Ecran choix client
	IF NOT EXISTS (select * from ecrans where nom_ecran = 'God-2') THEN
	 INSERT INTO ecrans(nom_ecran, fichier, nom_menu, fonction)
	 VALUES ('God-2', 'modules/agency_banking/operation_deplace_via_agent.php', 'God', 773);
	END IF;

	----Ecran Menu operation en deplace
	IF NOT EXISTS (select * from ecrans where nom_ecran = 'God-3') THEN
	 INSERT INTO ecrans(nom_ecran, fichier, nom_menu, fonction)
	 VALUES ('God-3', 'modules/agency_banking/operation_deplace_via_agent.php', 'God', 773);
	END IF;

		----Ecran Retait
	IF NOT EXISTS (select * from ecrans where nom_ecran = 'Rda-1') THEN
	 INSERT INTO ecrans(nom_ecran, fichier, nom_menu, fonction)
	 VALUES ('Rda-1', 'modules/agency_banking/retrait_deplace_via_agent.php', 'God', 774);
	END IF;

			----Ecran Retait
	IF NOT EXISTS (select * from ecrans where nom_ecran = 'Rda-2') THEN
	 INSERT INTO ecrans(nom_ecran, fichier, nom_menu, fonction)
	 VALUES ('Rda-2', 'modules/agency_banking/retrait_deplace_via_agent.php', 'God', 774);
	END IF;

				----Ecran Retait
	IF NOT EXISTS (select * from ecrans where nom_ecran = 'Rda-3') THEN
	 INSERT INTO ecrans(nom_ecran, fichier, nom_menu, fonction)
	 VALUES ('Rda-3', 'modules/agency_banking/retrait_deplace_via_agent.php', 'God', 774);
	END IF;

					----Ecran Retait
	IF NOT EXISTS (select * from ecrans where nom_ecran = 'Rda-4') THEN
	 INSERT INTO ecrans(nom_ecran, fichier, nom_menu, fonction)
	 VALUES ('Rda-4', 'modules/agency_banking/retrait_deplace_via_agent.php', 'God', 774);
	END IF;


		----Ecran Depot
	IF NOT EXISTS (select * from ecrans where nom_ecran = 'Dda-1') THEN
	 INSERT INTO ecrans(nom_ecran, fichier, nom_menu, fonction)
	 VALUES ('Dda-1', 'modules/agency_banking/depot_deplace_via_agent.php', 'God', 775);
	END IF;

			----Ecran Depot
	IF NOT EXISTS (select * from ecrans where nom_ecran = 'Dda-2') THEN
	 INSERT INTO ecrans(nom_ecran, fichier, nom_menu, fonction)
	 VALUES ('Dda-2', 'modules/agency_banking/depot_deplace_via_agent.php', 'God', 775);
	END IF;
				----Ecran Depot
	IF NOT EXISTS (select * from ecrans where nom_ecran = 'Dda-3') THEN
	 INSERT INTO ecrans(nom_ecran, fichier, nom_menu, fonction)
	 VALUES ('Dda-3', 'modules/agency_banking/depot_deplace_via_agent.php', 'God', 775);
	END IF;

					----Ecran Depot
	IF NOT EXISTS (select * from ecrans where nom_ecran = 'Dda-4') THEN
	 INSERT INTO ecrans(nom_ecran, fichier, nom_menu, fonction)
	 VALUES ('Dda-4', 'modules/agency_banking/depot_deplace_via_agent.php', 'God', 775);
	END IF;

			----Ecran Retait Multidevise
	IF NOT EXISTS (select * from ecrans where nom_ecran = 'Rda-11') THEN
	 INSERT INTO ecrans(nom_ecran, fichier, nom_menu, fonction)
	 VALUES ('Rda-11', 'modules/agency_banking/retrait_deplace_multidevises_via_agent.php', 'God', 774);
	END IF;

			----Ecran Retait Multidevise
	IF NOT EXISTS (select * from ecrans where nom_ecran = 'Rda-12') THEN
	 INSERT INTO ecrans(nom_ecran, fichier, nom_menu, fonction)
	 VALUES ('Rda-12', 'modules/agency_banking/retrait_deplace_multidevises_via_agent.php', 'God', 774);
	END IF;

				----Ecran Retait Multidevise
	IF NOT EXISTS (select * from ecrans where nom_ecran = 'Rda-13') THEN
	 INSERT INTO ecrans(nom_ecran, fichier, nom_menu, fonction)
	 VALUES ('Rda-13', 'modules/agency_banking/retrait_deplace_multidevises_via_agent.php', 'God', 774);
	END IF;

					----Ecran Retait Multidevise
	IF NOT EXISTS (select * from ecrans where nom_ecran = 'Rda-14') THEN
	 INSERT INTO ecrans(nom_ecran, fichier, nom_menu, fonction)
	 VALUES ('Rda-14', 'modules/agency_banking/retrait_deplace_multidevises_via_agent.php', 'God', 774);
	END IF;

			----Ecran Depot Multidevise
	IF NOT EXISTS (select * from ecrans where nom_ecran = 'Dda-11') THEN
	 INSERT INTO ecrans(nom_ecran, fichier, nom_menu, fonction)
	 VALUES ('Dda-11', 'modules/agency_banking/depot_deplace_multidevises_via_agent.php', 'God', 775);
	END IF;

			----Ecran Depot Multidevise
	IF NOT EXISTS (select * from ecrans where nom_ecran = 'Dda-12') THEN
	 INSERT INTO ecrans(nom_ecran, fichier, nom_menu, fonction)
	 VALUES ('Dda-12', 'modules/agency_banking/depot_deplace_multidevises_via_agent.php', 'God', 775);
	END IF;
				----Ecran Depot Multidevise
	IF NOT EXISTS (select * from ecrans where nom_ecran = 'Dda-13') THEN
	 INSERT INTO ecrans(nom_ecran, fichier, nom_menu, fonction)
	 VALUES ('Dda-13', 'modules/agency_banking/depot_deplace_multidevises_via_agent.php', 'God', 775);
	END IF;

					----Ecran Depot Multidevise
	IF NOT EXISTS (select * from ecrans where nom_ecran = 'Dda-14') THEN
	 INSERT INTO ecrans(nom_ecran, fichier, nom_menu, fonction)
	 VALUES ('Dda-14', 'modules/agency_banking/depot_deplace_multidevises_via_agent.php', 'God', 775);
	END IF;

	-- Ecran de visualisation des appro/trasnfert agent
	IF NOT EXISTS (select * from menus where nom_menu = 'Vis') THEN
	 id_str_trad := maketraductionlangsyst(' Visualisation ');
	 INSERT INTO menus (nom_menu, libel_menu, nom_pere, pos_hierarch, ordre, is_menu,fonction, is_cliquable)
	 VALUES ('Vis',  id_str_trad, 'Gen-16', 3, 12, TRUE, 0, FALSE);
	 IF EXISTS(SELECT langue FROM adsys_langues_systeme WHERE code = 'en_GB') THEN
	  INSERT INTO ad_traductions (id_str, langue, traduction) VALUES ( id_str_trad, 'en_GB', ' Visualization ');
	 END IF;
	END IF;

	IF NOT EXISTS (select * from ecrans where nom_ecran = 'Vis-1') THEN
	 INSERT INTO ecrans(nom_ecran, fichier, nom_menu, fonction)
	 VALUES ('Vis-1', 'modules/agency_banking/visualisation_agent.php', 'Vis', 0);
	END IF;




	-- Ecran de visualisation des appro/trasnfert agent
	IF NOT EXISTS (select * from menus where nom_menu = 'Vad') THEN
	 id_str_trad := maketraductionlangsyst('Visualisation des demandes dapprovisionnement / délestage des agents');
	 INSERT INTO menus (nom_menu, libel_menu, nom_pere, pos_hierarch, ordre, is_menu,fonction, is_cliquable)
	 VALUES ('Vad',  id_str_trad, 'Vis', 4, 1, TRUE, 771, FALSE);
	 IF EXISTS(SELECT langue FROM adsys_langues_systeme WHERE code = 'en_GB') THEN
	  INSERT INTO ad_traductions (id_str, langue, traduction) VALUES ( id_str_trad, 'en_GB', 'Visualization of supply requests / load shedding for agents');
	 END IF;
	END IF;


		IF NOT EXISTS (select * from menus where nom_menu = 'Vad-1') THEN
	 id_str_trad := maketraductionlangsyst(' Visualisation');
	 INSERT INTO menus (nom_menu, libel_menu, nom_pere, pos_hierarch, ordre, is_menu,fonction, is_cliquable)
	 VALUES ('Vad-1',  id_str_trad, 'Vad', 5, 1, FALSE, 771, FALSE);
	 IF EXISTS(SELECT langue FROM adsys_langues_systeme WHERE code = 'en_GB') THEN
	  INSERT INTO ad_traductions (id_str, langue, traduction) VALUES ( id_str_trad, 'en_GB', ' Visualisation');
	 END IF;
	END IF;

			IF NOT EXISTS (select * from ecrans where nom_ecran = 'Vad-1') THEN
	 INSERT INTO ecrans(nom_ecran, fichier, nom_menu, fonction)
	 VALUES ('Vad-1', 'modules/agency_banking/visualisation_agent.php', 'Vad-1', 771);
	END IF;


		IF NOT EXISTS (select * from menus where nom_menu = 'Vad-2') THEN
	 id_str_trad := maketraductionlangsyst('Rapport transactions');
	 INSERT INTO menus (nom_menu, libel_menu, nom_pere, pos_hierarch, ordre, is_menu,fonction, is_cliquable)
	 VALUES ('Vad-2',  id_str_trad, 'Vad', 5, 2, FALSE, 771, FALSE);
	 IF EXISTS(SELECT langue FROM adsys_langues_systeme WHERE code = 'en_GB') THEN
	  INSERT INTO ad_traductions (id_str, langue, traduction) VALUES ( id_str_trad, 'en_GB', 'Transactions report');
	 END IF;
	END IF;

			IF NOT EXISTS (select * from ecrans where nom_ecran = 'Vad-2') THEN
	 INSERT INTO ecrans(nom_ecran, fichier, nom_menu, fonction)
	 VALUES ('Vad-2', 'modules/agency_banking/visualisation_agent.php', 'Vad-2', 771);
	END IF;


	IF NOT EXISTS (select * from menus where nom_menu = 'Vad-3') THEN
	 id_str_trad := maketraductionlangsyst('Critère de recherche');
	 INSERT INTO menus (nom_menu, libel_menu, nom_pere, pos_hierarch, ordre, is_menu,fonction, is_cliquable)
	 VALUES ('Vad-3',  id_str_trad, 'Vad', 5, 3, FALSE, 771, FALSE);
	 IF EXISTS(SELECT langue FROM adsys_langues_systeme WHERE code = 'en_GB') THEN
	  INSERT INTO ad_traductions (id_str, langue, traduction) VALUES ( id_str_trad, 'en_GB', 'Research criteria');
	 END IF;
	END IF;

			IF NOT EXISTS (select * from ecrans where nom_ecran = 'Vad-3') THEN
	 INSERT INTO ecrans(nom_ecran, fichier, nom_menu, fonction)
	 VALUES ('Vad-3', 'modules/agency_banking/visualisation_agent.php', 'Vad-3', 771);
	END IF;



		-- Ecran de visualisation des transactions agents
	IF NOT EXISTS (select * from menus where nom_menu = 'Vta') THEN
	 id_str_trad := maketraductionlangsyst('Visualisation des transactions agents');
	 INSERT INTO menus (nom_menu, libel_menu, nom_pere, pos_hierarch, ordre, is_menu,fonction, is_cliquable)
	 VALUES ('Vta',  id_str_trad, 'Vis', 4, 2, TRUE, 781, FALSE);
	 IF EXISTS(SELECT langue FROM adsys_langues_systeme WHERE code = 'en_GB') THEN
	  INSERT INTO ad_traductions (id_str, langue, traduction) VALUES ( id_str_trad, 'en_GB', 'Visualization of agents transactions');
	 END IF;
	END IF;


		IF NOT EXISTS (select * from menus where nom_menu = 'Vta-1') THEN
	 id_str_trad := maketraductionlangsyst('Criteres de recherche');
	 INSERT INTO menus (nom_menu, libel_menu, nom_pere, pos_hierarch, ordre, is_menu,fonction, is_cliquable)
	 VALUES ('Vta-1',  id_str_trad, 'Vta', 5, 1, FALSE, 781, FALSE);
	 IF EXISTS(SELECT langue FROM adsys_langues_systeme WHERE code = 'en_GB') THEN
	  INSERT INTO ad_traductions (id_str, langue, traduction) VALUES ( id_str_trad, 'en_GB', 'Criteria ');
	 END IF;
	END IF;

			IF NOT EXISTS (select * from ecrans where nom_ecran = 'Vta-1') THEN
	 INSERT INTO ecrans(nom_ecran, fichier, nom_menu, fonction)
	 VALUES('Vta-1', 'modules/agency_banking/visualisation_transaction_agent.php', 'Vta-1', 781);
	END IF;

		IF NOT EXISTS (select * from menus where nom_menu = 'Vta-2') THEN
	 id_str_trad := maketraductionlangsyst(' Visualisation transaction ');
	 INSERT INTO menus (nom_menu, libel_menu, nom_pere, pos_hierarch, ordre, is_menu,fonction, is_cliquable)
	 VALUES ('Vta-2',  id_str_trad, 'Vta', 5, 2, FALSE, 781, FALSE);
	 IF EXISTS(SELECT langue FROM adsys_langues_systeme WHERE code = 'en_GB') THEN
	  INSERT INTO ad_traductions (id_str, langue, traduction) VALUES ( id_str_trad, 'en_GB', ' Transaction Visualization ');
	 END IF;
	END IF;

			IF NOT EXISTS (select * from ecrans where nom_ecran = 'Vta-2') THEN
	 INSERT INTO ecrans(nom_ecran, fichier, nom_menu, fonction)
	 VALUES('Vta-2', 'modules/agency_banking/visualisation_transaction_agent.php', 'Vta-2', 781);
	END IF;


	IF NOT EXISTS (select * from menus where nom_menu = 'Vta-3') THEN
	 id_str_trad := maketraductionlangsyst('Impression rapport transaction');
	 INSERT INTO menus (nom_menu, libel_menu, nom_pere, pos_hierarch, ordre, is_menu,fonction, is_cliquable)
	 VALUES ('Vta-3',  id_str_trad, 'Vta', 5, 3, FALSE, 781, FALSE);
	 IF EXISTS(SELECT langue FROM adsys_langues_systeme WHERE code = 'en_GB') THEN
	  INSERT INTO ad_traductions (id_str, langue, traduction) VALUES ( id_str_trad, 'en_GB', ' Print transaction report');
	 END IF;
	END IF;

			IF NOT EXISTS (select * from ecrans where nom_ecran = 'Vta-3') THEN
	 INSERT INTO ecrans(nom_ecran, fichier, nom_menu, fonction)
	 VALUES('Vta-3', 'modules/agency_banking/visualisation_transaction_agent.php', 'Vta-3', 781);
	END IF;

		-- Ecran de visualisation des creations clients via agent
	IF NOT EXISTS (select * from menus where nom_menu = 'Cva') THEN
	 id_str_trad := maketraductionlangsyst('Visualisation des créations clients via agent');
	 INSERT INTO menus (nom_menu, libel_menu, nom_pere, pos_hierarch, ordre, is_menu,fonction, is_cliquable)
	 VALUES ('Cva',  id_str_trad, 'Vis', 4, 3, TRUE, 784, FALSE);
	 IF EXISTS(SELECT langue FROM adsys_langues_systeme WHERE code = 'en_GB') THEN
	  INSERT INTO ad_traductions (id_str, langue, traduction) VALUES ( id_str_trad, 'en_GB', 'Visualization of client creation by agent');
	 END IF;
	END IF;


		IF NOT EXISTS (select * from menus where nom_menu = 'Cva-1') THEN
	 id_str_trad := maketraductionlangsyst(' Criteres de recherche');
	 INSERT INTO menus (nom_menu, libel_menu, nom_pere, pos_hierarch, ordre, is_menu,fonction, is_cliquable)
	 VALUES ('Cva-1',  id_str_trad, 'Cva', 5, 1, FALSE, 784, FALSE);
	 IF EXISTS(SELECT langue FROM adsys_langues_systeme WHERE code = 'en_GB') THEN
	  INSERT INTO ad_traductions (id_str, langue, traduction) VALUES ( id_str_trad, 'en_GB', 'Criteria ');
	 END IF;
	END IF;

			IF NOT EXISTS (select * from ecrans where nom_ecran = 'Cva-1') THEN
	 INSERT INTO ecrans(nom_ecran, fichier, nom_menu, fonction)
	 VALUES('Cva-1', 'modules/agency_banking/visualisation_creation_client_agent.php', 'Cva-1', 784);
	END IF;


	IF NOT EXISTS (select * from menus where nom_menu = 'Cva-2') THEN
	 id_str_trad := maketraductionlangsyst(' Visualisation client ');
	 INSERT INTO menus (nom_menu, libel_menu, nom_pere, pos_hierarch, ordre, is_menu,fonction, is_cliquable)
	 VALUES ('Cva-2',  id_str_trad, 'Cva', 5, 2, FALSE, 784, FALSE);
	 IF EXISTS(SELECT langue FROM adsys_langues_systeme WHERE code = 'en_GB') THEN
	  INSERT INTO ad_traductions (id_str, langue, traduction) VALUES ( id_str_trad, 'en_GB', 'Customer Visualization ');
	 END IF;
	END IF;

			IF NOT EXISTS (select * from ecrans where nom_ecran = 'Cva-2') THEN
	 INSERT INTO ecrans(nom_ecran, fichier, nom_menu, fonction)
	 VALUES('Cva-2', 'modules/agency_banking/visualisation_creation_client_agent.php', 'Cva-2', 784);
	END IF;

	IF NOT EXISTS (select * from menus where nom_menu = 'Cva-3') THEN
	 id_str_trad := maketraductionlangsyst(' Rapport creation client');
	 INSERT INTO menus (nom_menu, libel_menu, nom_pere, pos_hierarch, ordre, is_menu,fonction, is_cliquable)
	 VALUES ('Cva-3',  id_str_trad, 'Cva', 5, 3, FALSE, 784, FALSE);
	 IF EXISTS(SELECT langue FROM adsys_langues_systeme WHERE code = 'en_GB') THEN
	  INSERT INTO ad_traductions (id_str, langue, traduction) VALUES ( id_str_trad, 'en_GB', 'Client creation report ');
	 END IF;
	END IF;

			IF NOT EXISTS (select * from ecrans where nom_ecran = 'Cva-3') THEN
	 INSERT INTO ecrans(nom_ecran, fichier, nom_menu, fonction)
	 VALUES('Cva-3', 'modules/agency_banking/visualisation_creation_client_agent.php', 'Cva-3', 784);
	END IF;

		IF NOT EXISTS (select * from menus where nom_menu = 'Pic') THEN
	 id_str_trad := maketraductionlangsyst('Paramétrage impôt sur commission');
	 INSERT INTO menus (nom_menu, libel_menu, nom_pere, pos_hierarch, ordre, is_menu,fonction, is_cliquable)
	 VALUES ('Pic', id_str_trad, 'Mpa', 4, 4, TRUE, 785, TRUE);
	 IF EXISTS(SELECT langue FROM adsys_langues_systeme WHERE code = 'en_GB') THEN
	  INSERT INTO ad_traductions (id_str, langue, traduction) VALUES (id_str_trad, 'en_GB', 'Commission tax setup');
	 END IF;
	END IF;



	IF NOT EXISTS (select * from menus where nom_menu = 'Pic-1') THEN
	 id_str_trad := maketraductionlangsyst(' Saisie information ');
	 INSERT INTO menus (nom_menu, libel_menu, nom_pere, pos_hierarch, ordre, is_menu,fonction, is_cliquable)
	 VALUES ('Pic-1', id_str_trad, 'Pic', 5, 1, TRUE, 785, FALSE);
	 IF EXISTS(SELECT langue FROM adsys_langues_systeme WHERE code = 'en_GB') THEN
	  INSERT INTO ad_traductions (id_str, langue, traduction) VALUES (id_str_trad, 'en_GB', 'Provision/Offloading entry ');
	 END IF;
	END IF;

		IF NOT EXISTS (select * from ecrans where nom_ecran = 'Pic-1') THEN
	 INSERT INTO ecrans(nom_ecran, fichier, nom_menu, fonction)
	 VALUES ('Pic-1', 'modules/agency_banking/parametrage_impot_commission.php', 'Pic-1', 785);
	END IF;

	IF NOT EXISTS (select * from menus where nom_menu = 'Pic-2') THEN
	 id_str_trad := maketraductionlangsyst(' Confirmation information ');
	 INSERT INTO menus (nom_menu, libel_menu, nom_pere, pos_hierarch, ordre, is_menu,fonction, is_cliquable)
	 VALUES ('Pic-2', id_str_trad, 'Pic', 5, 2, TRUE, 785, FALSE);
	 IF EXISTS(SELECT langue FROM adsys_langues_systeme WHERE code = 'en_GB') THEN
	  INSERT INTO ad_traductions (id_str, langue, traduction) VALUES (id_str_trad, 'en_GB', 'Provision/Offloading confirmation');
	 END IF;
	END IF;

	IF NOT EXISTS (select * from ecrans where nom_ecran = 'Pic-2') THEN
	 INSERT INTO ecrans(nom_ecran, fichier, nom_menu, fonction)
	 VALUES ('Pic-2', 'modules/agency_banking/parametrage_impot_commission.php', 'Pic-2', 785);
	END IF;

	IF NOT EXISTS (select * from menus where nom_menu = 'Acc') THEN
	 id_str_trad := maketraductionlangsyst('Approbation creation client via agent');
	 INSERT INTO menus (nom_menu, libel_menu, nom_pere, pos_hierarch, ordre, is_menu,fonction, is_cliquable)
	 VALUES ('Acc', id_str_trad, 'Gen-16', 3, 1, TRUE, 782, TRUE);
	 IF EXISTS(SELECT langue FROM adsys_langues_systeme WHERE code = 'en_GB') THEN
	  INSERT INTO ad_traductions (id_str, langue, traduction) VALUES (id_str_trad, 'en_GB', 'Approval of client creation by agent');
	 END IF;
	END IF;

-----------------------------------------------------------------------------------------------------------------------------------------------------------

	IF NOT EXISTS (select * from menus where nom_menu = 'Acc-1') THEN
		id_str_trad := maketraductionlangsyst(' Liste des demandes client');
		INSERT INTO menus (nom_menu, libel_menu, nom_pere, pos_hierarch, ordre, is_menu,fonction, is_cliquable)
		VALUES ('Acc-1', id_str_trad, 'Acc', 4, 1, FALSE, null, FALSE);
		IF EXISTS(SELECT langue FROM adsys_langues_systeme WHERE code = 'en_GB') THEN
			INSERT INTO ad_traductions (id_str, langue, traduction) VALUES (id_str_trad, 'en_GB', 'Request Client list');
	 	END IF;
	END IF;

	IF NOT EXISTS (select * from ecrans where nom_ecran = 'Acc-1') THEN
		INSERT INTO ecrans(nom_ecran, fichier, nom_menu, fonction)
		VALUES ('Acc-1', 'modules/agency_banking/approbation_creation_client_via_agent.php', 'Acc-1', 782);
	END IF;

-------------------------------------------------------------------------------------------------------------------------------------------------------------

	IF NOT EXISTS (select * from menus where nom_menu = 'Acc-2') THEN
		id_str_trad := maketraductionlangsyst('Verification des demandes clients ');
		INSERT INTO menus (nom_menu, libel_menu, nom_pere, pos_hierarch, ordre, is_menu,fonction, is_cliquable)
		VALUES ('Acc-2', id_str_trad, 'Acc', 4, 2, FALSE, null, FALSE);
		IF EXISTS(SELECT langue FROM adsys_langues_systeme WHERE code = 'en_GB') THEN
			INSERT INTO ad_traductions (id_str, langue, traduction) VALUES (id_str_trad, 'en_GB', 'Request clients verification');
	 	END IF;
	END IF;

	IF NOT EXISTS (select * from ecrans where nom_ecran = 'Acc-2') THEN
		INSERT INTO ecrans(nom_ecran, fichier, nom_menu, fonction)
		VALUES ('Acc-2', 'modules/agency_banking/approbation_creation_client_via_agent.php', 'Acc-2', 782);
	END IF;

-------------------------------------------------------------------------------------------------------------------------------------------------------------

	IF NOT EXISTS (select * from menus where nom_menu = 'Acc-3') THEN
		id_str_trad := maketraductionlangsyst('Validation des demandes clientes');
		INSERT INTO menus (nom_menu, libel_menu, nom_pere, pos_hierarch, ordre, is_menu,fonction, is_cliquable)
		VALUES ('Acc-3', id_str_trad, 'Acc', 4, 3, FALSE, null, FALSE);
		IF EXISTS(SELECT langue FROM adsys_langues_systeme WHERE code = 'en_GB') THEN
			INSERT INTO ad_traductions (id_str, langue, traduction) VALUES (id_str_trad, 'en_GB', 'Request client validation');
	 	END IF;
	END IF;

	IF NOT EXISTS (select * from ecrans where nom_ecran = 'Acc-3') THEN
		INSERT INTO ecrans(nom_ecran, fichier, nom_menu, fonction)
		VALUES ('Acc-3', 'modules/agency_banking/approbation_creation_client_via_agent.php', 'Acc-3', 782);
	END IF;



		IF NOT EXISTS (select * from menus where nom_menu = 'Eca') THEN
	 id_str_trad := maketraductionlangsyst('Effectuer creation client par agent');
	 INSERT INTO menus (nom_menu, libel_menu, nom_pere, pos_hierarch, ordre, is_menu,fonction, is_cliquable)
	 VALUES ('Eca', id_str_trad, 'Gen-16', 3, 1, TRUE, 783, TRUE);
	 IF EXISTS(SELECT langue FROM adsys_langues_systeme WHERE code = 'en_GB') THEN
	  INSERT INTO ad_traductions (id_str, langue, traduction) VALUES (id_str_trad, 'en_GB', 'Carry out client creation by agent');
	 END IF;
	END IF;

--------------------------------------------------------------------------------------------------------------------------------------

	IF NOT EXISTS (select * from menus where nom_menu = 'Eca-1') THEN
		id_str_trad := maketraductionlangsyst('Effectuer creation client');
		INSERT INTO menus (nom_menu, libel_menu, nom_pere, pos_hierarch, ordre, is_menu,fonction, is_cliquable)
		VALUES ('Eca-1', id_str_trad, 'Eca', 4, 1, FALSE, null, FALSE);
		IF EXISTS(SELECT langue FROM adsys_langues_systeme WHERE code = 'en_GB') THEN
			INSERT INTO ad_traductions (id_str, langue, traduction) VALUES (id_str_trad, 'en_GB', 'Carry out client creation');
	 	END IF;
	END IF;

	IF NOT EXISTS (select * from ecrans where nom_ecran = 'Eca-1') THEN
		INSERT INTO ecrans(nom_ecran, fichier, nom_menu, fonction)
		VALUES ('Eca-1', 'modules/agency_banking/approbation_creation_client_via_agent.php', 'Eca-1', 783);
	END IF;

	---------------------------------------------------------------------------------------------------------------------------------------------------------

	IF NOT EXISTS (select * from menus where nom_menu = 'Cpa-7') THEN
		id_str_trad := maketraductionlangsyst('Blocage creation client');
		INSERT INTO menus (nom_menu, libel_menu, nom_pere, pos_hierarch, ordre, is_menu,fonction, is_cliquable)
		VALUES ('Cpa-7',maketraductionlangsyst('Blocage creation client'), 'Cpa', 4, 7, FALSE, null, FALSE);
		IF EXISTS(SELECT langue FROM adsys_langues_systeme WHERE code = 'en_GB') THEN
			INSERT INTO ad_traductions (id_str, langue, traduction) VALUES (maketraductionlangsyst('Blocage creation client'), 'en_GB', 'Client creation approval');
	 	END IF;
	END IF;

	IF NOT EXISTS (select * from ecrans where nom_ecran = 'Cpa-7') THEN
		INSERT INTO ecrans(nom_ecran, fichier, nom_menu, fonction)
		VALUES ('Cpa-7', 'modules/agency_banking/creation_client_agent.php', 'Cpa-7', 762);
	END IF;



	RETURN output_result;
END;
$$
LANGUAGE plpgsql;

SELECT script_creation_menu_ecran_agb();
DROP FUNCTION script_creation_menu_ecran_agb();
