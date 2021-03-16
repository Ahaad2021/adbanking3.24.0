---------------------------------- Mise a jour pour le rapport BIC BCEAO : ticket 774 ----------------------------------------------
CREATE OR REPLACE FUNCTION patch_ticket_774() RETURNS void AS $$
DECLARE

BEGIN

	IF NOT EXISTS (SELECT nom_ecran FROM ecrans WHERE nom_ecran='Ara-61') THEN
		INSERT INTO ecrans(nom_ecran, fichier, nom_menu, fonction)
		VALUES ('Ara-61', 'modules/rapports/rapports_agence.php', 'Ara-2', 370);
		RAISE NOTICE 'Added ecran Ara-61';
	END IF;

	IF NOT EXISTS (SELECT nom_ecran FROM ecrans WHERE nom_ecran='Ara-62') THEN
		INSERT INTO ecrans(nom_ecran, fichier, nom_menu, fonction)
		VALUES ('Ara-62', 'modules/rapports/rapports_agence.php', 'Ara-3', 370);
		RAISE NOTICE 'Added ecran Ara-62';
	END IF;

END;

$$
LANGUAGE plpgsql VOLATILE COST 100;
ALTER FUNCTION patch_ticket_774() OWNER TO adbanking;

-- ----------------------------------------------------------------------------
-- Execution
-- ----------------------------------------------------------------------------
SELECT patch_ticket_774();
DROP FUNCTION patch_ticket_774();



---------------------------------- Creation fonction recuperation donnees --------------------------------------------
CREATE OR REPLACE FUNCTION rapport_bic(date)  RETURNS void AS
$BODY$
DECLARE
	v_date_rapport ALIAS FOR $1;

BEGIN
	insert into ad_bic
		select
			xmlelement(name "Contract",
								 xmlelement(name "ContractCode",d.id_ag || '-' || d.id_doss),

								 xmlelement(name "ContractData",

														xmlforest(
																num_complet_cpte as "ConsentCode",
																case when d.etat in (2,5,7,13,14,15) then 'Open' else 'Close' end as "PhaseOfContract" ,
																case when d.etat = 2 then 'GrantedButNotActivated'
																when d.etat = 5 and d.cre_nbre_reech = 0 then 'GrantedAndActivated'
																when d.etat = 5 and d.cre_nbre_reech > 0 then 'Rescheduled'
																when d.etat = 6 and d.cre_retard_etat_max_jour > 30 then 'SettledOnTime'
																when d.etat = 6 and d.cre_retard_etat_max_jour <= 30 then 'WithArrearsNoRepossession'
																when d.etat in (3,4) then 'Cancelled'
																when d.etat = 9 then 'WrittenOff'
																end as "ContractStatus",
																'Installment' as "TypeOfContract",
																case when d.obj_dem = 2 then 'Construct'
																else 'Other' end as "PurposeOfFinancing",
																apc.devise as "CurrencyOfContract",
																xmlforest ( d.cre_mnt_octr  as "Value",
																						apc.devise as "Currency"
																)  as "TotalAmount",
																--end of TotalAmount
																xmlforest ( round((coalesce(p.cre_mnt_deb,0) - coalesce(p.mnt_cred_paye,0)),0)  as "Value",
																						apc.devise as "Currency"
																)  as "OutstandingAmount",
																--end of OutstandingAmount
																xmlforest ( round(coalesce(p.solde_retard,0),0)  as "Value",
																						apc.devise as "Currency"
																)  as "PastDueAmount",
																--end of PastDueAmount
																case when d.etat >= 5 then p.nbr_jours_retard else 0 end as "PastDueDays",
																case when d.etat >= 5 then (nbr_ech_total - nbr_ech_paye) else 0 end as "NumberOfDueInstallments",
																xmlforest ( (select sum(mnt_cap) - sum(solde_cap) from ad_etr e where e.id_doss = d.id_doss)  as "Value",
																						apc.devise as "Currency"
																)  as "TotalMonthlyPayment",
																--end of TotalMonthlyPayment
																case when apc.periodicite = 1 then 'Days30'
																when apc.periodicite = 2 then 'Days15'
																when apc.periodicite = 3 then 'Days90'
																when apc.periodicite = 4 then 'Days180'
																when apc.periodicite = 5 then 'Days360'
																when apc.periodicite = 6 then 'FinalDay'
																when apc.periodicite = 7 then 'Days60'
																end as "PaymentPeriodicity",
																to_char(d.cre_date_debloc,'YYYY-MM-DD') as "StartDate"
														)  -- END contract data elements


								 ), --END ContractData

								 case when cli.statut_juridique = 1 then
									 xmlelement(name "Individual",

															xmlforest(
																	d.id_ag || '-' || d.id_client as "CustomerCode",
																	pp_nom as "PresentSurname",
																	pp_nom||' '||pp_prenom as "FullName",
																	case when pp_sexe = 1 then 'Male' when pp_sexe = 2 then 'Female' else '' end as "Gender",
																	to_char(pp_date_naissance,'YYYY-MM-DD') as "DateOfBirth",
																	case when pp_etat_civil = 1 then 'Single'
																	when pp_etat_civil = 2 then 'Married'
																	when pp_etat_civil = 3 then 'Widowed'
																	when pp_etat_civil = 4 then 'Divorced'
																	else 'Other' end as "MaritalStatus",
																	'Yes' as "Residency",
																	xmlforest ( pp_nm_piece_id as "NationalID",
																							to_char(pp_date_piece_id,'YYYY-MM-DD') as "NationalIDIssueDate",
																							to_char(pp_date_exp_id,'YYYY-MM-DD') as "NationalIDExpirationDate",
																							pp_lieu_delivrance_id as "NationalIDIssuerCountry",
																							pp_nm_piece_id as "PassportNumber",
																							to_char(pp_date_piece_id,'YYYY-MM-DD') as "PassportIssueDate",
																							to_char(pp_date_exp_id,'YYYY-MM-DD') as "PassportExpirationDate",
																							pp_lieu_delivrance_id as "PassportIssuerCountry"
																	)  as "IdentificationNumbers",
																	--end of IdentificationNumbers
																	xmlforest ( id_loc2 as "Street",
																							ville as "City",
																							ap.code_pays as "Country"
																	)  as "MainAddress",
																	--end of MainAddress
																	xmlforest ( num_tel  as "MobilePhone"
																	)  as "Contacts"
																	--end of Contacts
															) --END of Individual elements
									 ) end , --END Individual

								 xmlelement(name "SubjectRole",

														xmlforest(
																d.id_ag || '-' || d.id_client as "CustomerCode",
																'MainDebtor' as "RoleOfCustomer"
														) --END of Subject Role elements
								 ), --END of Subject Role

								 case when cli.statut_juridique in (2,3,4) then
									 xmlelement(name "Company",
															xmlforest(
																	d.id_ag || '-' || d.id_client as "CustomerCode",
																	CASE statut_juridique
																	WHEN '2' THEN pm_raison_sociale
																	WHEN '3'  THEN gi_nom
																	WHEN '4'  THEN gi_nom END as "CompanyName",
																	'Branch' as "LegalForm",
																	case cli.etat
																	when '1' then 'OtherCourtActionByBank'
																	when '2' then 'Active'
																	when '3' then 'Liquidation'
																	when '6' then 'Closed'
																	when '7' then 'BankruptcyPetitionByBank'
																	when '9' then 'SupervisoryCrisisAdministration' end as "BusinessStatus",
																	to_char(date_adh,'YYYY-MM-DD') as "EstablishmentDate",
																	case when sect_act = 1 then 'Agriculture'
																	when sect_act = 2 then 'Wholesale'
																	when sect_act = 3 then 'OtherManufacturingIndustries'
																	when sect_act = 6 then 'SocialAndRelatedServicesProvidedToTheCommunity'
																	else 'Other' end as "IndustrySector",
																	xmlforest ( pm_numero_reg_nat  as "RegistrationNumber",
																							ap.code_pays as "RegistrationNumberIssuerCountry"
																	)  as "IdentificationNumbers",
																	--end of IdentificationNumbers
																	xmlforest ( id_loc2 as "Street",
																							ville as "City",
																							ap.code_pays as "Country",
																							adresse as "AddressLine"
																	)  as "MainAddress"
																	--end of MainAddress
															) --END of Company elements
									 ) end, --END of Company
								 case when g.id_gar is not null then
									 xmlelement(name "Collateral",
															xmlforest(
																	g.id_gar as "CollateralCode",
																	atb.libel as "CollateralType"
															) --END of Collateral elements
									 ) --END of Collateral
								 end


			) --END Contract

		from getportfeuilleview($1,numagc()) p
			inner join ad_dcr d on p.id_doss = d.id_doss
			inner join ad_cpt c on c.id_titulaire = d.id_client
			left join adsys_produit_credit apc on apc.id = c.id_prod
			inner join ad_cli cli on cli.id_client = d.id_client
			left join adsys_pays ap on ap.id_pays = cli.pays
			left join ad_gar g on g.id_doss = d.id_doss
			left join ad_biens b on b.id_bien = g.gar_mat_id_bien
			left join adsys_types_biens atb on atb.id = b.type_bien
		order by d.id_ag || '-' || d.id_doss
	;


END;
$BODY$
LANGUAGE plpgsql VOLATILE
COST 100;
ALTER FUNCTION rapport_bic(date)
OWNER TO adbanking;

---------------------------------- Fonction pour generer le XML --------------------------------------------------------
-- Function: rapport_bic_file(date)

-- DROP FUNCTION rapport_bic_file(date);

CREATE OR REPLACE FUNCTION rapport_bic_file(date) RETURNS void AS $BODY$

DECLARE
	v_date_rapport ALIAS FOR $1;
BEGIN

	IF NOT EXISTS(SELECT * FROM information_schema.tables WHERE table_name = 'ad_bic') THEN
		CREATE TABLE ad_bic
		(
			output xml
		)
		WITH (
		OIDS=FALSE
		);
		ALTER TABLE ad_bic OWNER TO adbanking;
	ELSE
		truncate table ad_bic;
	END IF;

	PERFORM rapport_bic(v_date_rapport);
	copy (select * from ad_bic) To '/tmp/rapport_bic.xml';
	-- DROP TABLE ad_bic;

END;
$BODY$
LANGUAGE plpgsql VOLATILE
COST 100;
ALTER FUNCTION rapport_bic_file(date)
OWNER TO adbanking;


------------------------------- Ticket #802 : ajouter les nouveaux taux de provisions dans les etats de credits
CREATE OR REPLACE FUNCTION patch_802() RETURNS INT AS
$BODY$
DECLARE
output_result INTEGER = 1;
tableliste_ident INTEGER = 0;
tableliste_str INTEGER = 0;
d_tableliste_str INTEGER = 0;

BEGIN
	tableliste_ident := (select ident from tableliste where nomc like 'adsys_etat_credits' order by ident desc limit 1);

	IF NOT EXISTS(SELECT * FROM d_tableliste WHERE nchmpc = 'taux_prov_decouvert' and tablen = tableliste_ident) THEN
	  ALTER TABLE adsys_etat_credits ADD taux_prov_decouvert double precision;
	  d_tableliste_str := makeTraductionLangSyst('Taux de provision de crédit découvert');
	  INSERT INTO d_tableliste VALUES ((select max(ident) from d_tableliste)+1, tableliste_ident, 'taux_prov_decouvert', d_tableliste_str, false, NULL, 'prc', false, false, false);
	  IF EXISTS(SELECT langue FROM adsys_langues_systeme WHERE code = 'en_GB') THEN
	    INSERT INTO ad_traductions VALUES (d_tableliste_str,'en_GB','Rate of discovered credit provision');
	  END IF;
	END IF;

	IF NOT EXISTS(SELECT * FROM d_tableliste WHERE nchmpc = 'taux_prov_reechelonne' and tablen = tableliste_ident) THEN
	  ALTER TABLE adsys_etat_credits ADD taux_prov_reechelonne double precision;
	  d_tableliste_str := makeTraductionLangSyst('Taux de provision de crédit rééchelonné');
	  INSERT INTO d_tableliste VALUES ((select max(ident) from d_tableliste)+1, tableliste_ident, 'taux_prov_reechelonne', d_tableliste_str, false, NULL, 'prc', false, false, false);
	  IF EXISTS(SELECT langue FROM adsys_langues_systeme WHERE code = 'en_GB') THEN
	    INSERT INTO ad_traductions VALUES (d_tableliste_str,'en_GB','Rate of rescheduled credit provision');
	  END IF;
	END IF;

RETURN output_result;
END;
$BODY$
  LANGUAGE plpgsql VOLATILE
  COST 100;
ALTER FUNCTION patch_802()
  OWNER TO postgres;

SELECT patch_802();
DROP FUNCTION IF EXISTS patch_802();
--------------------------------Fin ticket # 802 ----------------------------------------------------

-------------------------------- Ticket #803 ----------------------------------------------------------
CREATE OR REPLACE FUNCTION trig_update_ord_perm()
  RETURNS trigger AS
$BODY$
  BEGIN
    IF (NEW.date_prem_exe != OLD.date_prem_exe AND NEW.date_prem_exe >= now()) THEN
      NEW.date_proch_exe = NEW.date_prem_exe;
    END IF;
    IF (NEW.date_dern_exe_th != OLD.date_dern_exe_th AND NEW.date_dern_exe_th >= OLD.date_proch_exe) THEN
      SELECT INTO NEW.date_proch_exe ordreperm_proch_exe(NEW.date_dern_exe_th, NEW.interv, NEW.periodicite);
    END IF;
    IF (NEW.date_dern_exe_th >= OLD.date_proch_exe) THEN
	SELECT INTO NEW.date_proch_exe ordreperm_proch_exe(NEW.date_dern_exe_th, NEW.interv, NEW.periodicite);
    END IF;
    RETURN NEW;
  END;
$BODY$
  LANGUAGE plpgsql VOLATILE
  COST 100;
ALTER FUNCTION trig_update_ord_perm()
  OWNER TO adbanking;

  ------------------------------- Fin ticket #803 ----------------------------------------------------

  -------------------------------- Debut ticket #804 --------------------------------------------------
  CREATE OR REPLACE FUNCTION ticket_804_functions() RETURNS INT AS
$$
DECLARE
output_result INTEGER = 1;

BEGIN

CREATE OR REPLACE FUNCTION prelevefraistenuecpt(integer, text, integer, text)
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

	v_info_tax RECORD;			-- array contenant les infos de la taxe associe a l'operation comptable
	v_mnt_tax NUMERIC(30,6) = 0;		-- Le montant tax calculé sur frais de tenue
	v_sens_tax ALIAS FOR $4;		-- Sens Tax pour mouvement comptables
	v_reglementTax INTEGER;			-- Pour la fonction reglementTaxFraisTenue
	v_scenario INTEGER;			-- Les differents scenarios prelevement frais tenue

	-- Recupere des infos sur les compte épargne à prélever et leurs produits associés
	Cpt_Prelev CURSOR FOR
		SELECT a.id_cpte, a.id_titulaire,a.solde, a.devise, a.num_complet_cpte, b.frais_tenue_cpt as total_frais_tenue_cpt, b.cpte_cpta_prod_ep
		FROM ad_cpt a, adsys_produit_epargne b WHERE a.id_ag=b.id_ag AND a.id_ag=NumAgc() AND a.id_prod = b.id AND (frequence_tenue_cpt BETWEEN 1 AND freq_tenue_cpt)
		AND a.etat_cpte in (1,4) AND b.frais_tenue_cpt > 0 ORDER BY a.id_titulaire;

	ligne RECORD;

	ligne_ad_cpt ad_cpt%ROWTYPE;

	cpte_base INTEGER;

	solde_dispo_cpte NUMERIC(30,6);

BEGIN
  -- Récupération infos taxe associe a l'operation comptable
  SELECT INTO v_info_tax t.id, t.taux FROM ad_oper_taxe opt INNER JOIN adsys_taxes t ON opt.id_taxe = t.id WHERE opt.id_ag = t.id_ag AND t.id_ag = numagc() AND opt.type_oper = num_ope;
  --RAISE NOTICE 'Id = % Taux de Tax = % ',v_info_tax.id, v_info_tax.taux;

  -- Recherche du libellé et du compte au crédit de type opération
  SELECT INTO type_ope libel_ope , num_cpte FROM ad_cpt_ope a, ad_cpt_ope_cptes b WHERE a.id_ag=b.id_ag AND a.id_ag=NumAgc() AND a.type_operation = num_ope AND a.type_operation=b.type_operation AND b.sens = 'c';
  --RAISE NOTICE 'Libel Operation % - Compte au Crédit %',type_ope.libel_ope,type_ope.num_cpte;

  -- Récupération de la devise du compte au crédit
  SELECT INTO devise_cpte_cr devise FROM ad_cpt_comptable WHERE id_ag=NumAgc() AND num_cpte_comptable = type_ope.num_cpte;
  --RAISE NOTICE 'Devise du compte au credit = %',devise_cpte_cr;

  -- Récupération du journal associé si le compte au crédit est principal
  SELECT INTO jou2 recupeJournal(type_ope.num_cpte);
  --RAISE NOTICE 'Journal associé si le compte au crédit est principal = %',jou2;

  -- Recherche du numéro de l'exercice contenant la date de prélèvement
  SELECT INTO exo id_exo_compta FROM ad_exercices_compta WHERE id_ag=NumAgc() AND date_deb_exo<= date(date_prelev) AND date_fin_exo >= date(date_prelev);

  -- Récupération du nombre de devises
  SELECT INTO nbre_devises count(*) from devise WHERE id_ag=NumAgc();
  --RAISE NOTICE 'Nombre devise = %',nbre_devises;

  IF nbre_devises = 1 THEN
    mode_multidev := false;
  ELSE
    mode_multidev := true;
  END IF;
  --RAISE NOTICE 'Is multi devise = %',mode_multidev;

  -- Récupération de la devise de référence
  SELECT INTO code_dev_ref code_devise_reference FROM ad_agc WHERE id_ag=NumAgc();
  --RAISE NOTICE 'Devise de reference = %',code_dev_ref;

  cur_date := 'now';

  OPEN Cpt_Prelev;
  FETCH Cpt_Prelev INTO ligne;

  -- Ajout historique à condition qu'on ait trouvé des comptes à traiter
  -- On utilise la date de prélèvement (qui est normalement la date pour laquelle on exécute le batch),
  -- et la dernière minute de la journée, afin
  IF FOUND THEN
    INSERT INTO ad_his (type_fonction, login, infos, date, id_ag)
    VALUES (212, 'admin','Prelevement des frais de tenue de compte via batch', date(now()), NumAgc());
    --RAISE NOTICE 'ajout historique!';
  END IF;


  WHILE FOUND LOOP

    --calculer le tax sur frais de tenue si necessaire
    IF v_info_tax.id IS NOT NULL THEN
	v_mnt_tax := v_info_tax.taux * ligne.total_frais_tenue_cpt;
    END IF;
    --RAISE NOTICE '==> Montant Tax Calculé = [ % ]',v_mnt_tax;

    --calculer le solde disponible du compte en enlevant les frais de tenue + tax sur frais de tenue

    SELECT INTO solde_dispo_cpte(solde - mnt_bloq - mnt_min_cpte + decouvert_max - mnt_bloq_cre - ligne.total_frais_tenue_cpt - v_mnt_tax)
    FROM ad_cpt WHERE id_ag=NumAgc() AND id_cpte = ligne.id_cpte;

    --RAISE NOTICE 'Solde dispo pour compte % avec solde initial % = %', ligne.id_cpte, ligne.solde, solde_dispo_cpte;

    IF (solde_dispo_cpte >= 0) THEN

      -- RECUPERATION DE LA DEVISE DU COMPTE ASSOCIE AU PRODUIT
      SELECT INTO devise_cpte_debit devise FROM ad_cpt_comptable WHERE id_ag=NumAgc() AND num_cpte_comptable = ligne.cpte_cpta_prod_ep;
      --RAISE NOTICE 'DEVISE DU COMPTE ASSOCIE AU PRODUIT = %',devise_cpte_debit;

      -- Construction du numéro de compte à débiter
      IF devise_cpte_debit IS NULL THEN
        num_cpte_debit := ligne.cpte_cpta_prod_ep || '.' || ligne.devise;
      ELSE
        num_cpte_debit := ligne.cpte_cpta_prod_ep;
      END IF;
      --RAISE NOTICE 'numéro de compte à débiter = %',num_cpte_debit;

      -- Récupération du journal associé si le compte est principal
      SELECT INTO jou1 recupeJournal(num_cpte_debit);
      --RAISE NOTICE 'Journal associé si le compte est principal = %',jou1;

       IF jou1 IS NOT NULL AND jou2 IS NOT NULL AND jou1 != jou2 THEN
		--RAISE NOTICE '---------------------------------IF jou1 is not null and jou2 is not null and jou1 != jou2--------------------------------';

		-- num_cpte_debit ET COMPTE AU CREDIT SONT PINCIPAUX ET DE JOURNAUX DIFFERENTS , ON RECUPERE ALORS LE COMPTE DE LIAISON

		SELECT INTO cpte_liaison num_cpte_comptable FROM ad_journaux_liaison WHERE (id_ag=NumAgc() AND id_jou1=jou1 AND id_jou2=jou2) OR (id_jou1=jou2 AND id_jou2=jou1);
		--RAISE NOTICE 'Compte de liason entre journal % et journal %  est %', jou1, jou2, cpte_liaison;

		-- DEVISE DU COMPTE DE LIAISON
		SELECT INTO devise_cpte_liaison devise FROM ad_cpt_comptable WHERE id_ag=NumAgc() AND num_cpte_comptable = cpte_liaison;
		--RAISE NOTICE 'Devise du compte de liason : % ', devise_cpte_liaison;


		---------- DEBIT COMPTE CLIENT PAR CREDIT DU COMPTE DE LIAISON -----------------------
		IF ligne.devise = devise_cpte_liaison THEN  ----- num_cpte_debit et cpte_liaison sont de la même devise
			--RAISE NOTICE 'num_cpte_debit % et cpte_liaison % sont de la même devise',ligne.devise,devise_cpte_liaison;

			-- prelevement des frais sur le compte du client
			UPDATE ad_cpt SET solde = solde - ligne.total_frais_tenue_cpt WHERE (id_cpte = ligne.id_cpte);
			--RAISE NOTICE 'prelevement des frais sur le compte du client';

			-- Ecriture comptable
			INSERT INTO ad_ecriture(id_his,id_ag, date_comptable, libel_ecriture, id_jou, id_exo, ref_ecriture,info_ecriture,type_operation)
			VALUES ((SELECT currval('ad_his_id_his_seq')),NumAgc(), date(date_prelev), type_ope.libel_ope, jou1,
			exo.id_exo_compta, makeNumEcriture(jou1, exo.id_exo_compta),ligne.id_cpte,num_ope);
			--RAISE NOTICE 'Ecriture comptable';

			-- Mouvement comptable au débit
			INSERT INTO ad_mouvement (id_ecriture,id_ag, compte, cpte_interne_cli, sens, montant, devise, date_valeur)
			VALUES ((SELECT currval('ad_ecriture_seq')),NumAgc(),num_cpte_debit,ligne.id_cpte, 'd', ligne.total_frais_tenue_cpt, ligne.devise, date(date_prelev));
			--RAISE NOTICE 'Mouvement comptable au débit';

			-- Mouvement comptable au crédit
			INSERT INTO ad_mouvement (id_ecriture,id_ag,compte, cpte_interne_cli, sens, montant, devise, date_valeur)
			VALUES ((SELECT currval('ad_ecriture_seq')),NumAgc(), cpte_liaison, NULL, 'c', ligne.total_frais_tenue_cpt,ligne.devise, date(date_prelev));
			--RAISE NOTICE 'Mouvement comptable au crédit';

			-- Mise à jour des soldes comptables
			UPDATE ad_cpt_comptable set solde = solde - ligne.total_frais_tenue_cpt WHERE id_ag=Numagc() AND num_cpte_comptable = num_cpte_debit;
			UPDATE ad_cpt_comptable set solde = solde + ligne.total_frais_tenue_cpt WHERE id_ag=NumAgc() AND num_cpte_comptable = cpte_liaison;
			--RAISE NOTICE 'Mise à jour des soldes comptables';


		ELSE --------- num_cpte_debit et cpte_liaison n'ont pas la même devise, faire la conversion
			--RAISE NOTICE 'num_cpte_debit % et cpte_liaison % nont pas la même devise, faire la conversion',ligne.devise,devise_cpte_liaison;

			--------- si num_cpte_debit a la devise de référence et cpte_liaison une devise étrangère
			IF ligne.devise = code_dev_ref THEN
				--RAISE NOTICE ' debut si num_cpte_debit a la devise de référence et cpte_liaison une devise étrangère';

				SELECT INTO cpt_pos_ch cpte_position_change || '.' || devise_cpte_liaison FROM ad_agc WHERE id_ag=NumAgc();
				SELECT INTO cpt_cv_pos_ch cpte_contreval_position_change || '.' || devise_cpte_liaison FROM ad_agc WHERE id_ag=NumAgc();
				--RAISE NOTICE 'cpt_pos_ch = % cpt_cv_pos_ch = %',cpt_pos_ch,cpt_cv_pos_ch;

				-- prelevement des frais sur le compte du client
				UPDATE ad_cpt SET solde = solde - ligne.total_frais_tenue_cpt WHERE (id_cpte = ligne.id_cpte AND id_ag=NumAgc());
				--RAISE NOTICE 'prelevement des frais sur le compte du client';

				-- Ecriture comptable
				INSERT INTO ad_ecriture(id_his,id_ag, date_comptable, libel_ecriture, id_jou, id_exo, ref_ecriture,info_ecriture,type_operation)
				VALUES ((SELECT currval('ad_his_id_his_seq')),NumAgc(), date(date_prelev), type_ope.libel_ope, jou1, exo.id_exo_compta, makeNumEcriture(jou1, exo.id_exo_compta),ligne.id_cpte,num_ope);
				--RAISE NOTICE 'Ecriture comptable';

				-- Mouvement comptable au débit
				INSERT INTO ad_mouvement (id_ecriture,id_ag, compte, cpte_interne_cli, sens, montant, devise, date_valeur)
				VALUES ((SELECT currval('ad_ecriture_seq')),NumAgc(),num_cpte_debit,ligne.id_cpte, 'd', ligne.total_frais_tenue_cpt, ligne.devise, date(date_prelev));
				--RAISE NOTICE 'Mouvement comptable au débit';

				-- Mouvement comptable au crédit de la c/v du compte de liaison
				INSERT INTO ad_mouvement (id_ecriture,id_ag, compte, cpte_interne_cli, sens, montant, devise, date_valeur)
				VALUES ((SELECT currval('ad_ecriture_seq')),NumAgc(), cpt_cv_pos_ch, NULL, 'c', ligne.total_frais_tenue_cpt,ligne.devise, date(date_prelev));
				--RAISE NOTICE 'Mouvement comptable au crédit de la c/v du compte de liaison';

				-- montant dans la devise du compte de liaison
				SELECT INTO cv_frais_tenue_cpte CalculeCV(ligne.total_frais_tenue_cpt, ligne.devise, devise_cpte_liaison);
				--RAISE NOTICE 'montant dans la devise du compte de liaison = %',cv_frais_tenue_cpte;

				-- Mouvement comptable au débit de la position de change du compte de liaison
				INSERT INTO ad_mouvement (id_ecriture,id_ag, compte, cpte_interne_cli, sens, montant, devise, date_valeur)
				VALUES ((SELECT currval('ad_ecriture_seq')),NumAgc(),cpt_pos_ch,NULL, 'd', cv_frais_tenue_cpte,devise_cpte_liaison,date(date_prelev));
				--RAISE NOTICE 'Mouvement comptable au débit de la position de change du compte de liaison';

				-- Mouvement comptable au crédit du compte de liaison
				INSERT INTO ad_mouvement (id_ecriture,id_ag, compte, cpte_interne_cli, sens, montant, devise, date_valeur)
				VALUES ((SELECT currval('ad_ecriture_seq')),NumAgc(),cpte_liaison ,NULL, 'c',cv_frais_tenue_cpte, devise_cpte_liaison, date(date_prelev));
				--RAISE NOTICE 'Mouvement comptable au crédit du compte de liaison';

				-- Mise à jour des soldes comptables
				UPDATE ad_cpt_comptable set solde = solde - ligne.total_frais_tenue_cpt WHERE id_ag=NumAgc() AND num_cpte_comptable = num_cpte_debit;
				UPDATE ad_cpt_comptable set solde = solde + ligne.total_frais_tenue_cpt WHERE id_ag=NumAgc() AND num_cpte_comptable = cpt_cv_pos_ch;
				UPDATE ad_cpt_comptable set solde = solde - cv_frais_tenue_cpte WHERE id_ag=NumAgc() AND num_cpte_comptable = cpt_pos_ch;
				UPDATE ad_cpt_comptable set solde = solde + cv_frais_tenue_cpte WHERE id_ag=NumAgc() AND num_cpte_comptable = cpte_liaison;
				--RAISE NOTICE 'Mise à jour des soldes comptables';

				--RAISE NOTICE ' fin si num_cpte_debit a la devise de référence et cpte_liaison une devise étrangère';

			END IF; -- FIN IF ligne.devise = code_dev_ref

			-------- si cpte_liaison a la devise de référence et num_cpte_debit une devise étrangère
			IF devise_cpte_liaison = code_dev_ref THEN
				--RAISE NOTICE ' debut si cpte_liaison a la devise de référence et num_cpte_debit une devise étrangère';

				SELECT INTO cpt_pos_ch cpte_position_change || '.' || ligne.devise FROM ad_agc WHERE id_ag=NumAgc();
				SELECT INTO cpt_cv_pos_ch cpte_contreval_position_change || '.' || ligne.devise FROM ad_agc WHERE id_ag=NumAgc();
				--RAISE NOTICE 'cpt_pos_ch = % cpt_cv_pos_ch = % ',cpt_pos_ch,cpt_cv_pos_ch;

				-- prelevement des frais sur le compte du client
				UPDATE ad_cpt SET solde = solde - ligne.total_frais_tenue_cpt WHERE (id_ag=NumAgc() AND id_cpte = ligne.id_cpte);
				--RAISE NOTICE 'prelevement des frais sur le compte du client';

				-- montant dans la devise du compte de liaison
				SELECT INTO cv_frais_tenue_cpte CalculeCV(ligne.total_frais_tenue_cpt, ligne.devise, code_dev_ref);
				--RAISE NOTICE 'montant dans la devise du compte de liaison = %',cv_frais_tenue_cpte;

				-- Ecriture comptable
				INSERT INTO ad_ecriture(id_his,id_ag, date_comptable, libel_ecriture, id_jou, id_exo, ref_ecriture,info_ecriture,type_operation)
				VALUES ((SELECT currval('ad_his_id_his_seq')),NumAgc(), date(date_prelev), type_ope.libel_ope, jou1,
				exo.id_exo_compta, makeNumEcriture(jou1, exo.id_exo_compta),ligne.id_cpte,num_ope);
				--RAISE NOTICE 'Ecriture comptable';

				-- Mouvement comptable au crédit du compte de liaison
				INSERT INTO ad_mouvement (id_ecriture,id_ag, compte, cpte_interne_cli, sens, montant, devise, date_valeur)
				VALUES ((SELECT currval('ad_ecriture_seq')),NumAgc(),cpte_liaison ,NULL, 'c',cv_frais_tenue_cpte,
				code_dev_ref, date(date_prelev));
				--RAISE NOTICE 'Mouvement comptable au crédit du compte de liaison';

				-- Mouvement comptable au débit de la c/v de num_cpte_debit
				INSERT INTO ad_mouvement (id_ecriture,id_ag, compte, cpte_interne_cli, sens, montant, devise, date_valeur)
				VALUES ((SELECT currval('ad_ecriture_seq')),NumAgc(),cpt_cv_pos_ch ,NULL, 'd',cv_frais_tenue_cpte,
				code_dev_ref, date(date_prelev));
				--RAISE NOTICE 'Mouvement comptable au débit de la c/v de num_cpte_debit';

				-- Mouvement comptable au débit de num_cpte_debit
				INSERT INTO ad_mouvement (id_ecriture,id_ag, compte, cpte_interne_cli, sens, montant, devise, date_valeur)
				VALUES ((SELECT currval('ad_ecriture_seq')),NumAgc(),num_cpte_debit,ligne.id_cpte, 'd',
				ligne.total_frais_tenue_cpt, ligne.devise, date(date_prelev));
				--RAISE NOTICE 'Mouvement comptable au débit de num_cpte_debit';

				-- Mouvement comptable au crédit de la position de change de num_cpte_debit
				INSERT INTO ad_mouvement (id_ecriture,id_ag,compte, cpte_interne_cli, sens, montant, devise, date_valeur)
				VALUES ((SELECT currval('ad_ecriture_seq')),NumAgc(),cpt_pos_ch,NULL, 'c', ligne.total_frais_tenue_cpt,
				ligne.devise, date(date_prelev));
				--RAISE NOTICE 'Mouvement comptable au crédit de la position de change de num_cpte_debit';

				-- Mise à jour des soldes comptables
				UPDATE ad_cpt_comptable set solde = solde - ligne.total_frais_tenue_cpt WHERE id_ag=NumAgc() AND num_cpte_comptable = num_cpte_debit;
				UPDATE ad_cpt_comptable set solde = solde + ligne.total_frais_tenue_cpt WHERE id_ag=NumAgc() AND num_cpte_comptable = cpt_pos_ch;
				UPDATE ad_cpt_comptable set solde = solde - cv_frais_tenue_cpte WHERE id_ag=NumAgc() AND num_cpte_comptable = cpt_cv_pos_ch;
				UPDATE ad_cpt_comptable set solde = solde + cv_frais_tenue_cpte WHERE id_ag=NumAgc() AND num_cpte_comptable = cpte_liaison;
				--RAISE NOTICE 'Mise à jour des soldes comptables';

				--RAISE NOTICE ' fin si cpte_liaison a la devise de référence et num_cpte_debit une devise étrangère';

			END IF; -- FIN IF devise_cpte_liaison = code_dev_ref

			-------- si ni cpte_liaison ni num_cpte_debit n'a la devise de référence
			IF ligne.devise != code_dev_ref AND devise_cpte_liaison != code_dev_ref THEN
				--RAISE NOTICE ' debut si ni cpte_liaison ni num_cpte_debit na la devise de référence';

				-- prelevement des frais sur le compte du client
				UPDATE ad_cpt SET solde = solde - ligne.total_frais_tenue_cpt WHERE (id_ag=NumAgc() AND id_cpte = ligne.id_cpte);
				--RAISE NOTICE 'prelevement des frais sur le compte du client';

				-- Ecriture comptable
				INSERT INTO ad_ecriture(id_his,id_ag, date_comptable, libel_ecriture, id_jou, id_exo, ref_ecriture,info_ecriture,type_operation)
				VALUES ((SELECT currval('ad_his_id_his_seq')),NumAgc(), date(date_prelev), type_ope.libel_ope, jou1, exo.id_exo_compta, makeNumEcriture(jou1, exo.id_exo_compta),ligne.id_cpte,num_ope);
				--RAISE NOTICE 'Ecriture comptable';

				-- Mouvement comptable au débit de num_cpte_debit
				INSERT INTO ad_mouvement (id_ecriture,id_ag, compte, cpte_interne_cli, sens, montant, devise, date_valeur)
				VALUES ((SELECT currval('ad_ecriture_seq')),NumAgc(),num_cpte_debit,ligne.id_cpte, 'd', ligne.total_frais_tenue_cpt, ligne.devise, date(date_prelev));
				--RAISE NOTICE 'Mouvement comptable au débit de num_cpte_debit';

				UPDATE ad_cpt_comptable set solde = solde - ligne.total_frais_tenue_cpt WHERE id_ag=NumAgc() AND num_cpte_comptable = num_cpte_debit;
				--RAISE NOTICE 'set solde = solde - ligne.total_frais_tenue_cpt';

				-- position de change de la devise de num_cpte_debit
				SELECT INTO cpt_pos_ch cpte_position_change || '.' || ligne.devise FROM ad_agc WHERE id_ag=NumAgc();
				--RAISE NOTICE 'position de change de la devise de num_cpte_debit';

				-- Mouvement comptable au crédit de la position de change de num_cpte_debit
				INSERT INTO ad_mouvement (id_ecriture,id_ag, compte, cpte_interne_cli, sens, montant, devise, date_valeur)
				VALUES ((SELECT currval('ad_ecriture_seq')),NumAgc(),cpt_pos_ch,NULL, 'c', ligne.total_frais_tenue_cpt, ligne.devise, date(date_prelev));
				--RAISE NOTICE 'Mouvement comptable au crédit de la position de change de num_cpte_debit';

				UPDATE ad_cpt_comptable set solde = solde + ligne.total_frais_tenue_cpt WHERE id_ag=NumAgc() AND num_cpte_comptable = cpt_pos_ch;
				--RAISE NOTICE 'set solde = solde + ligne.total_frais_tenue_cpt';

				-- montant dans la devise de référence
				SELECT INTO cv_frais_tenue_cpte CalculeCV(ligne.total_frais_tenue_cpt, ligne.devise, code_dev_ref);
				--RAISE NOTICE 'montant dans la devise de référence = %',cv_frais_tenue_cpte;

				-- c/v de la devise de num_cpte_debit
				SELECT INTO cpt_cv_pos_ch cpte_contreval_position_change || '.' || ligne.devise FROM ad_agc WHERE id_ag=NumAgc();
				--RAISE NOTICE 'c/v de la devise de num_cpte_debit = %',cpt_cv_pos_ch;

				-- Mouvement comptable au débit de la c/v de num_cpte_debit
				INSERT INTO ad_mouvement (id_ecriture,id_ag, compte, cpte_interne_cli, sens, montant, devise, date_valeur)
				VALUES ((SELECT currval('ad_ecriture_seq')),NumAgc(), cpt_cv_pos_ch,NULL, 'd', cv_frais_tenue_cpte, code_dev_ref, date(date_prelev));
				--RAISE NOTICE 'Mouvement comptable au débit de la c/v de num_cpte_debit';

				UPDATE ad_cpt_comptable set solde = solde - cv_frais_tenue_cpte WHERE id_ag=NumAgc() AND num_cpte_comptable = cpt_cv_pos_ch;
				--RAISE NOTICE 'set solde = solde - cv_frais_tenue_cpte';

				-- c/v de la devise du compte de liaison
				SELECT INTO cpt_cv_pos_ch cpte_contreval_position_change || '.' || devise_cpte_liaison FROM ad_agc WHERE id_ag=NumAgc();
				--RAISE NOTICE 'c/v de la devise du compte de liaison = %',cpt_cv_pos_ch;

				-- Mouvement comptable au crédit de la c/v du compte de liaison
				INSERT INTO ad_mouvement (id_ecriture,id_ag, compte, cpte_interne_cli, sens, montant, devise, date_valeur)
				VALUES ((SELECT currval('ad_ecriture_seq')),NumAgc(), cpt_cv_pos_ch, NULL, 'c', cv_frais_tenue_cpte, code_dev_ref, date(date_prelev));
				--RAISE NOTICE 'Mouvement comptable au crédit de la c/v du compte de liaison';

				UPDATE ad_cpt_comptable set solde = solde + cv_frais_tenue_cpte WHERE id_ag=NumAgc() AND num_cpte_comptable = cpt_cv_pos_ch;
				--RAISE NOTICE 'set solde = solde + cv_frais_tenue_cpte';

				-- montant dans la devise du compte de liaison
				SELECT INTO cv_frais_tenue_cpte CalculeCV(ligne.total_frais_tenue_cpt, ligne.devise, devise_cpte_liaison);
				--RAISE NOTICE 'montant dans la devise du compte de liaison = %',cv_frais_tenue_cpte;

				-- Mouvement comptable au crédit du compte de liaison
				INSERT INTO ad_mouvement (id_ecriture,id_ag, compte, cpte_interne_cli, sens, montant, devise, date_valeur)
				VALUES ((SELECT currval('ad_ecriture_seq')),NumAgc(), cpte_liaison, NULL, 'c', cv_frais_tenue_cpte, devise_cpte_liaison, date(date_prelev));
				--RAISE NOTICE 'Mouvement comptable au crédit du compte de liaison';

				UPDATE ad_cpt_comptable set solde = solde + cv_frais_tenue_cpte WHERE id_ag=NumAgc() AND num_cpte_comptable = cpte_liaison;
				--RAISE NOTICE 'set solde = solde + cv_frais_tenue_cpte';

				-- position de change de la devise du compte de liaison
				SELECT INTO cpt_pos_ch cpte_position_change || '.' || devise_cpte_liaison FROM ad_agc WHERE id_ag=NumAgc();
				--RAISE NOTICE 'position de change de la devise du compte de liaison = %',cpt_pos_ch;

				-- Mouvement comptable au débit de la position de change de la devise du compte de liaison
				INSERT INTO ad_mouvement (id_ecriture,id_ag, compte, cpte_interne_cli, sens, montant, devise, date_valeur)
				VALUES ((SELECT currval('ad_ecriture_seq')),NumAgc(), cpt_pos_ch, NULL, 'd', cv_frais_tenue_cpte, devise_cpte_liaison, date(date_prelev));
				--RAISE NOTICE 'Mouvement comptable au débit de la position de change de la devise du compte de liaison';

				UPDATE ad_cpt_comptable set solde = solde - cv_frais_tenue_cpte WHERE id_ag=NumAgc() AND num_cpte_comptable = cpt_pos_ch;
				--RAISE NOTICE 'set solde = solde - cv_frais_tenue_cpte';

				--RAISE NOTICE ' fin si ni cpte_liaison ni num_cpte_debit na la devise de référence';

			END IF; -- FIN IF ligne.devise != code_dev_ref AND devise_cpte_liaison != code_dev_ref

			--RAISE NOTICE 'FIN  IF ligne.devise = devise_cpte_liaison';

		END IF;  -- FIN  IF ligne.devise = devise_cpte_liaison

		----------- FIN DEBIT COMPTE CLIENT PAR CREDIT COMPTE DE LIAISON -----------------------
		--RAISE NOTICE '----------- FIN DEBIT COMPTE CLIENT PAR CREDIT COMPTE DE LIAISON -----------------------';


		----------- DEBIT COMPTE DE LIAISON PAR CREDIT COMPTE AU CREDIT DANS LE SECOND JOURNAL ------------------------
		--RAISE NOTICE '----------- DEBIT COMPTE DE LIAISON PAR CREDIT COMPTE AU CREDIT DANS LE SECOND JOURNAL ------------------------';

		IF devise_cpte_liaison = devise_cpte_cr THEN  ----- COMPTE AU CREDIT ET cpte_liaison SONT DE LA MEME DEVISE
			--RAISE NOTICE 'COMPTE AU CREDIT ET cpte_liaison SONT DE LA MEME DEVISE';

			-- MONTANT DANS LA DEVISE DU COMPTE DE LIASON
			SELECT INTO cv_frais_tenue_cpte CalculeCV(ligne.total_frais_tenue_cpt, ligne.devise, devise_cpte_liaison);
			--RAISE NOTICE 'MONTANT DANS LA DEVISE DU COMPTE DE LIASON = %',cv_frais_tenue_cpte;

			-- PASSAGE ECRITURE COMPTABLE
			INSERT INTO ad_ecriture(id_his,id_ag, date_comptable, libel_ecriture, id_jou, id_exo, ref_ecriture,info_ecriture,type_operation)
			VALUES ((SELECT currval('ad_his_id_his_seq')),NumAgc(),date(date_prelev), type_ope.libel_ope, jou2,
			exo.id_exo_compta, makeNumEcriture(jou2, exo.id_exo_compta),ligne.id_cpte,num_ope);
			--RAISE NOTICE 'PASSAGE ECRITURE COMPTABLE';

			-- MOUVEMENT COMPTABLE AU DEBIT DU COMPTE DE LIAISON
			INSERT INTO ad_mouvement (id_ecriture,id_ag, compte, cpte_interne_cli, sens, montant, devise, date_valeur)
			VALUES ((SELECT currval('ad_ecriture_seq')),NumAgc(),cpte_liaison,NULL,'d',cv_frais_tenue_cpte,
			devise_cpte_liaison,date(date_prelev));
			--RAISE NOTICE 'MOUVEMENT COMPTABLE AU DEBIT DU COMPTE DE LIAISON';

			UPDATE ad_cpt_comptable set solde = solde - cv_frais_tenue_cpte WHERE id_ag=NumAgc() AND num_cpte_comptable = cpte_liaison;
			--RAISE NOTICE 'set solde = solde - cv_frais_tenue_cpte';

			-- MOUVEMENT COMPTABLE AU CREDIT DU COMPTE DE CREDIT
			INSERT INTO ad_mouvement (id_ecriture,id_ag, compte, cpte_interne_cli, sens, montant, devise, date_valeur)
			VALUES ((SELECT currval('ad_ecriture_seq')),NumAgc(),type_ope.num_cpte,NULL,'c',cv_frais_tenue_cpte,
			devise_cpte_cr,date(date_prelev));
			--RAISE NOTICE 'MOUVEMENT COMPTABLE AU CREDIT DU COMPTE DE CREDIT';

			UPDATE ad_cpt_comptable set solde = solde + cv_frais_tenue_cpte WHERE id_ag=NumAgc() AND num_cpte_comptable = type_ope.num_cpte;
			--RAISE NOTICE 'set solde = solde + cv_frais_tenue_cpte';


		ELSE      ----- COMPTE AU CREDIT ET cpte_liaison N'ONT PAS LA MEME DEVISE , FAIRE DONC LA CONVERSION
			--RAISE NOTICE 'COMPTE AU CREDIT ET cpte_liaison NONT PAS LA MEME DEVISE , FAIRE DONC LA CONVERSION';

			IF devise_cpte_liaison = code_dev_ref THEN  -- CPTE DE LIAISON A LA DEVISE DE REFERENCE ET CPTE AU CREDIT DEVISE ETRANGERE
				--RAISE NOTICE 'CPTE DE LIAISON A LA DEVISE DE REFERENCE ET CPTE AU CREDIT DEVISE ETRANGERE';

				SELECT INTO cpt_pos_ch cpte_position_change || '.' || devise_cpte_cr FROM ad_agc WHERE id_ag=NumAgc();
				SELECT INTO cpt_cv_pos_ch cpte_contreval_position_change || '.' || devise_cpte_cr FROM ad_agc WHERE id_ag=NumAgc();
				--RAISE NOTICE 'cpt_pos_ch = % cpt_cv_pos_ch = % ',cpt_pos_ch,cpt_cv_pos_ch;

				-- MONTANT DANS LA DEVISE DU COMPTE DE LIASON (DEVISE DE REFERENCE )
				SELECT INTO cv_frais_tenue_cpte CalculeCV(ligne.total_frais_tenue_cpt, ligne.devise, devise_cpte_liaison);
				--RAISE NOTICE 'MONTANT DANS LA DEVISE DU COMPTE DE LIASON (DEVISE DE REFERENCE ) = %',cv_frais_tenue_cpte;

				-- PASSAGE ECRITURE COMPTABLE
				INSERT INTO ad_ecriture(id_his,id_ag, date_comptable, libel_ecriture, id_jou, id_exo, ref_ecriture,info_ecriture,type_operation)
				VALUES ((SELECT currval('ad_his_id_his_seq')),NumAgc(), date(date_prelev), type_ope.libel_ope, jou2,
				exo.id_exo_compta, makeNumEcriture(jou2, exo.id_exo_compta),ligne.id_cpte,num_ope);
				--RAISE NOTICE 'PASSAGE ECRITURE COMPTABLE';

				-- MOUVEMENT AU DEBIT DU COMPTE DE LIAISON
				INSERT INTO ad_mouvement (id_ecriture,id_ag, compte, cpte_interne_cli, sens, montant, devise, date_valeur)
				VALUES ((SELECT currval('ad_ecriture_seq')),NumAgc(), cpte_liaison, NULL, 'd', cv_frais_tenue_cpte,
				devise_cpte_liaison, date(date_prelev));
				--RAISE NOTICE 'MOUVEMENT AU DEBIT DU COMPTE DE LIAISON';

				UPDATE ad_cpt_comptable set solde = solde - cv_frais_tenue_cpte WHERE id_ag=NumAgc() AND num_cpte_comptable = cpte_liaison;
				--RAISE NOTICE 'set solde = solde - cv_frais_tenue_cpte';

				-- MOUVEMENT COMPTABLE AU CREDIT DE LA c/v DU COMPTE DE CREDIT DE L'OPERATION
				INSERT INTO ad_mouvement (id_ecriture,id_ag, compte, cpte_interne_cli, sens, montant, devise, date_valeur)
				VALUES ((SELECT currval('ad_ecriture_seq')),NumAgc(), cpt_cv_pos_ch, NULL, 'c', cv_frais_tenue_cpte,
				code_dev_ref, date(date_prelev));
				--RAISE NOTICE 'MOUVEMENT COMPTABLE AU CREDIT DE LA c/v DU COMPTE DE CREDIT DE LOPERATION';

				UPDATE ad_cpt_comptable set solde = solde + cv_frais_tenue_cpte WHERE id_ag=NumAgc() AND num_cpte_comptable = cpt_cv_pos_ch;
				--RAISE NOTICE 'set solde = solde + cv_frais_tenue_cpte';


				-- MONTANT DANS LA DEVISE DU COMPTE DE CREDIT DE L'OPERATION
				SELECT INTO cv_frais_tenue_cpte CalculeCV(ligne.total_frais_tenue_cpt, ligne.devise, devise_cpte_cr);
				--RAISE NOTICE 'MONTANT DANS LA DEVISE DU COMPTE DE CREDIT DE LOPERATION';

				-- MOUVEMENT COMPTABLE AU DEBIT DE LA POSITION DE CHANGE DU COMPTE AU CREDIT DE L'OPERATION
				INSERT INTO ad_mouvement (id_ecriture,id_ag, compte, cpte_interne_cli, sens, montant, devise, date_valeur)
				VALUES ((SELECT currval('ad_ecriture_seq')),NumAgc(),cpt_pos_ch, NULL, 'd', cv_frais_tenue_cpte,
				devise_cpte_cr,date(date_prelev));
				--RAISE NOTICE 'MOUVEMENT COMPTABLE AU DEBIT DE LA POSITION DE CHANGE DU COMPTE AU CREDIT DE LOPERATION';

				UPDATE ad_cpt_comptable set solde = solde - cv_frais_tenue_cpte WHERE id_ag=NumAgc() AND num_cpte_comptable = cpt_pos_ch;
				--RAISE NOTICE 'set solde = solde - cv_frais_tenue_cpte';

				-- MOUVEMENT COMPTABLE AU CREDIT DU COMPTE DE CREDIT DE L'OPERATION
				INSERT INTO ad_mouvement (id_ecriture,id_ag, compte, cpte_interne_cli, sens, montant, devise, date_valeur)
				VALUES ((SELECT currval('ad_ecriture_seq')),NumAgc(), type_ope.num_cpte , NULL, 'c',cv_frais_tenue_cpte,
				devise_cpte_cr, date(date_prelev));
				--RAISE NOTICE 'MOUVEMENT COMPTABLE AU CREDIT DU COMPTE DE CREDIT DE LOPERATION';

				UPDATE ad_cpt_comptable set solde = solde + cv_frais_tenue_cpte WHERE id_ag=NumAgc() AND num_cpte_comptable = type_ope.num_cpte;
				--RAISE NOTICE 'set solde = solde + cv_frais_tenue_cpte';

				--RAISE NOTICE 'FIN IF devise_cpte_liaison = code_dev_ref';

			END IF; -- FIN IF devise_cpte_liaison = code_dev_ref


			IF devise_cpte_cr = code_dev_ref THEN -- SI CPTE AU CREDIT A LA DEVISE DE REFERENCE ET CPTE LIAISON UNE DEVISE ETRANGERE
				--RAISE NOTICE 'SI CPTE AU CREDIT A LA DEVISE DE REFERENCE ET CPTE LIAISON UNE DEVISE ETRANGERE';

				SELECT INTO cpt_pos_ch cpte_position_change || '.' || devise_cpte_liaison FROM ad_agc WHERE id_ag=NumAgc();
				SELECT INTO cpt_cv_pos_ch cpte_contreval_position_change || '.' || devise_cpte_liaison FROM ad_agc WHERE id_ag=NumAgc();
				--RAISE NOTICE 'cpt_pos_ch = % cpt_cv_pos_ch = %',cpt_pos_ch,cpt_cv_pos_ch;

				-- PASSAGE ECRITURE COMPTABLE
				INSERT INTO ad_ecriture(id_his,id_ag, date_comptable, libel_ecriture, id_jou, id_exo, ref_ecriture,info_ecriture,type_operation)
				VALUES ((SELECT currval('ad_his_id_his_seq')),NumAgc(), date(date_prelev), type_ope.libel_ope, jou2,
				exo.id_exo_compta, makeNumEcriture(jou2, exo.id_exo_compta),ligne.id_cpte,num_ope);
				--RAISE NOTICE 'PASSAGE ECRITURE COMPTABLE';

				-- MONATANT DANS LA DEVISE DU COMPTE AU CREDIT DE L'OPERATION ( DEVISE DE REFERENCE )
				SELECT INTO cv_frais_tenue_cpte CalculeCV(ligne.total_frais_tenue_cpt, ligne.devise, devise_cpte_cr);
				--RAISE NOTICE 'MONATANT DANS LA DEVISE DU COMPTE AU CREDIT DE LOPERATION ( DEVISE DE REFERENCE ) = %',cv_frais_tenue_cpte;

				-- MOUVEMENT AU CREDIT DU COMPTE DE CREDIT DE L'OPERATION
				INSERT INTO ad_mouvement (id_ecriture,id_ag, compte, cpte_interne_cli, sens, montant, devise, date_valeur)
				VALUES ((SELECT currval('ad_ecriture_seq')),NumAgc(),type_ope.num_cpte ,NULL, 'c',cv_frais_tenue_cpte,
				devise_cpte_cr, date(date_prelev));
				--RAISE NOTICE 'MOUVEMENT AU CREDIT DU COMPTE DE CREDIT DE LOPERATION';

				UPDATE ad_cpt_comptable set solde = solde + cv_frais_tenue_cpte WHERE id_ag=NumAgc() AND num_cpte_comptable = type_ope.num_cpte;
				--RAISE NOTICE 'set solde = solde + cv_frais_tenue_cpte';

				-- MOUVEMENT AU DEBIT DE LA c/v DU COMPTE DE LIAISON
				INSERT INTO ad_mouvement (id_ecriture,id_ag, compte, cpte_interne_cli, sens, montant, devise, date_valeur)
				VALUES ((SELECT currval('ad_ecriture_seq')),NumAgc(),cpt_cv_pos_ch ,NULL, 'd',cv_frais_tenue_cpte,
				code_dev_ref, date(date_prelev));
				--RAISE NOTICE 'MOUVEMENT AU DEBIT DE LA c/v DU COMPTE DE LIAISON';

				UPDATE ad_cpt_comptable set solde = solde - cv_frais_tenue_cpte WHERE id_ag=NumAgc() AND num_cpte_comptable = cpt_cv_pos_ch;
				--RAISE NOTICE 'set solde = solde - cv_frais_tenue_cpte';

				-- MONATANT DANS LA DEVISE DU COMPTE DE LIAISON
				SELECT INTO cv_frais_tenue_cpte CalculeCV(ligne.total_frais_tenue_cpt, ligne.devise, devise_cpte_liaison);
				--RAISE NOTICE 'MONATANT DANS LA DEVISE DU COMPTE DE LIAISON = %',cv_frais_tenue_cpte;

				-- MOUVEMENT COMPTABLE AU DEBIT DU COMPTE DE LIAISON
				INSERT INTO ad_mouvement (id_ecriture,id_ag, compte, cpte_interne_cli, sens, montant, devise, date_valeur)
				VALUES ((SELECT currval('ad_ecriture_seq')),NumAgc(), cpte_liaison, NULL, 'd', cv_frais_tenue_cpte,
				devise_cpte_liaison, date(date_prelev));
				--RAISE NOTICE 'MOUVEMENT COMPTABLE AU DEBIT DU COMPTE DE LIAISON';

				UPDATE ad_cpt_comptable set solde = solde - cv_frais_tenue_cpte WHERE id_ag=NumAgc() AND num_cpte_comptable = cpte_liaison;
				--RAISE NOTICE 'set solde = solde - cv_frais_tenue_cpte';

				-- MOUVEMENT COMPTABLE AU CREDIT DA LA POSITION DE CHANGE DE LA DEVISE DU COMPTE DE LIAISON
				INSERT INTO ad_mouvement (id_ecriture,id_ag, compte, cpte_interne_cli, sens, montant, devise, date_valeur)
				VALUES ((SELECT currval('ad_ecriture_seq')),NumAgc(),cpt_pos_ch, NULL, 'c', cv_frais_tenue_cpte,
				devise_cpte_liaison, date(date_prelev));
				--RAISE NOTICE 'MOUVEMENT COMPTABLE AU CREDIT DA LA POSITION DE CHANGE DE LA DEVISE DU COMPTE DE LIAISON';

				UPDATE ad_cpt_comptable set solde = solde + cv_frais_tenue_cpte WHERE id_ag=NumAgc() AND num_cpte_comptable = cpt_pos_ch;
				--RAISE NOTICE 'set solde = solde + cv_frais_tenue_cpte';

				--RAISE NOTICE 'FIN IF devise_cpte_cr = code_dev_ref';

			END IF; -- FIN IF devise_cpte_cr = code_dev_ref

			IF devise_cpte_cr != code_dev_ref AND devise_cpte_liaison != code_dev_ref THEN

				-- DEVISE COMPTE DE LIAISON ET DEVISE COMPTE AU CREDIT SONT DIFFERENTES ET AUCUNE N'EST EGALE A LA DEVISE DE REFERENCE
				--RAISE NOTICE 'DEVISE COMPTE DE LIAISON ET DEVISE COMPTE AU CREDIT SONT DIFFERENTES ET AUCUNE NEST EGALE A LA DEVISE DE REFERENCE';

				-- PASSAGE ECRITURE COMPTABLE DANS jou2
				INSERT INTO ad_ecriture(id_his,id_ag, date_comptable, libel_ecriture, id_jou, id_exo, ref_ecriture,info_ecriture,type_operation)
				VALUES ((SELECT currval('ad_his_id_his_seq')),NumAgc(), date(date_prelev), type_ope.libel_ope, jou2,
				exo.id_exo_compta, makeNumEcriture(jou2, exo.id_exo_compta),ligne.id_cpte,num_ope);
				--RAISE NOTICE 'PASSAGE ECRITURE COMPTABLE DANS jou2';

				-- MONTANT DANS LA DEVISE DU COMPTE DE LIAISON
				SELECT INTO cv_frais_tenue_cpte CalculeCV(ligne.total_frais_tenue_cpt, ligne.devise, devise_cpte_liaison);
				--RAISE NOTICE 'MONTANT DANS LA DEVISE DU COMPTE DE LIAISON = %',cv_frais_tenue_cpte;

				-- MOUVEMENT COMPTABLE AU DEBIT DU COMPTE DE LIAISON
				INSERT INTO ad_mouvement (id_ecriture,id_ag, compte, cpte_interne_cli, sens, montant, devise, date_valeur)
				VALUES ((SELECT currval('ad_ecriture_seq')),NumAgc(), cpte_liaison, NULL, 'd', cv_frais_tenue_cpte,
				devise_cpte_liaison, date(date_prelev));
				--RAISE NOTICE 'MOUVEMENT COMPTABLE AU DEBIT DU COMPTE DE LIAISON';

				UPDATE ad_cpt_comptable set solde = solde - cv_frais_tenue_cpte WHERE id_ag=NumAgc() AND num_cpte_comptable = cpte_liaison;
				--RAISE NOTICE 'set solde = solde - cv_frais_tenue_cpte';

				-- POSITION DE CHANGE DE LA DEVISE DU COMPTE DE LIAISON
				SELECT INTO cpt_pos_ch cpte_position_change || '.' || devise_cpte_liaison FROM ad_agc WHERE id_ag=NumAgc();
				--RAISE NOTICE 'POSITION DE CHANGE DE LA DEVISE DU COMPTE DE LIAISON';

				-- MOUVEMENT COMPTABLE AU CREDIT DE LA POSITION DE CHANGE DE LA DEVISE DU COMPTE DE LIAISON
				INSERT INTO ad_mouvement (id_ecriture,id_ag, compte, cpte_interne_cli, sens, montant, devise, date_valeur)
				VALUES ((SELECT currval('ad_ecriture_seq')),NumAgc(), cpt_pos_ch, NULL, 'c', cv_frais_tenue_cpte,
				devise_cpte_liaison, date(date_prelev));
				--RAISE NOTICE 'MOUVEMENT COMPTABLE AU CREDIT DE LA POSITION DE CHANGE DE LA DEVISE DU COMPTE DE LIAISON';

				UPDATE ad_cpt_comptable set solde = solde + cv_frais_tenue_cpte WHERE id_ag=NumAgc() AND num_cpte_comptable = cpt_pos_ch;
				--RAISE NOTICE 'set solde = solde + cv_frais_tenue_cpte';

				-- MONATNT DANS LA DEVISE DE REFERENCE
				SELECT INTO cv_frais_tenue_cpte CalculeCV(ligne.total_frais_tenue_cpt, ligne.devise, code_dev_ref);
				--RAISE NOTICE 'MONATNT DANS LA DEVISE DE REFERENCE = %',cv_frais_tenue_cpte;

				-- c/v DE LA DEVISE DU COMPTE DE LIAISON
				SELECT INTO cpt_cv_pos_ch cpte_contreval_position_change || '.' || devise_cpte_liaison FROM ad_agc;
				--RAISE NOTICE 'c/v DE LA DEVISE DU COMPTE DE LIAISON = %',cpt_cv_pos_ch;

				-- MOUVEMENT COMPTABLE AU DEBIT DE LA c/v DE LA DEVISE DU COMPTE DE LIAISON
				INSERT INTO ad_mouvement (id_ecriture,id_ag, compte, cpte_interne_cli, sens, montant, devise, date_valeur)
				VALUES ((SELECT currval('ad_ecriture_seq')),NumAgc(), cpt_cv_pos_ch,NULL, 'd', cv_frais_tenue_cpte, code_dev_ref, date(date_prelev));
				--RAISE NOTICE 'MOUVEMENT COMPTABLE AU DEBIT DE LA c/v DE LA DEVISE DU COMPTE DE LIAISON';

				UPDATE ad_cpt_comptable set solde = solde - cv_frais_tenue_cpte WHERE id_ag=NumAgc() AND num_cpte_comptable = cpt_cv_pos_ch;
				--RAISE NOTICE 'set solde = solde - cv_frais_tenue_cpte';

				-- c/v DE LA DEVISE DU COMPTE AU CREDIT DE L'OPERATION
				SELECT INTO cpt_cv_pos_ch cpte_contreval_position_change || '.' || devise_cpte_cr FROM ad_agc;
				--RAISE NOTICE 'c/v DE LA DEVISE DU COMPTE AU CREDIT DE LOPERATION = %',cpt_cv_pos_ch;

				-- MOUVEMENT COMPTABLE AU CREDIT DE LA c/v DU COMPTE AU CREDIT DE L'OPERATION
				INSERT INTO ad_mouvement (id_ecriture, id_ag,compte, cpte_interne_cli, sens, montant, devise, date_valeur)
				VALUES ((SELECT currval('ad_ecriture_seq')),NumAgc(), cpt_cv_pos_ch, NULL, 'c', cv_frais_tenue_cpte,
				code_dev_ref, date(date_prelev));
				--RAISE NOTICE 'MOUVEMENT COMPTABLE AU CREDIT DE LA c/v DU COMPTE AU CREDIT DE LOPERATION';

				UPDATE ad_cpt_comptable set solde = solde + cv_frais_tenue_cpte WHERE id_ag=NumAgc() AND num_cpte_comptable = cpt_cv_pos_ch;
				--RAISE NOTICE 'set solde = solde + cv_frais_tenue_cpte';

				-- MONTANT DANS LA DEVISE DU COMPTE DE CREDIT DE L'OPERATION
				SELECT INTO cv_frais_tenue_cpte CalculeCV(ligne.total_frais_tenue_cpt, ligne.devise, devise_cpte_cr);
				--RAISE NOTICE 'MONTANT DANS LA DEVISE DU COMPTE DE CREDIT DE LOPERATION';

				-- MOUVEMENT COMPTABLE AU CREDIT DU COMPTE DE CREDIT D EL'OPERATION
				INSERT INTO ad_mouvement (id_ecriture,id_ag, compte, cpte_interne_cli, sens, montant, devise, date_valeur)
				VALUES ((SELECT currval('ad_ecriture_seq')),NumAgc(), type_ope.num_cpte, NULL, 'c', cv_frais_tenue_cpte,
				devise_cpte_cr, date(date_prelev));
				--RAISE NOTICE 'MOUVEMENT COMPTABLE AU CREDIT DU COMPTE DE CREDIT D ELOPERATION';

				UPDATE ad_cpt_comptable set solde = solde + cv_frais_tenue_cpte WHERE id_ag=NumAgc() AND num_cpte_comptable = type_ope.num_cpte;
				--RAISE NOTICE 'set solde = solde + cv_frais_tenue_cpte';

				-- POSITION DE CHANGE DE LA DEVISE DU COMPTE AU CREDIT DE L'OPERATION
				SELECT INTO cpt_pos_ch cpte_position_change || '.' || devise_cpte_cr FROM ad_agc WHERE id_ag=NumAgc();
				--RAISE NOTICE 'POSITION DE CHANGE DE LA DEVISE DU COMPTE AU CREDIT DE LOPERATION = %',cpt_pos_ch;

				-- MOUVEMENT COMPTABLE AU DEBIT DE LA POSITION DE CHANGE DE LA DEVISE DU COMPTE AU CREDIT D EL'OPERATION
				INSERT INTO ad_mouvement (id_ecriture,id_ag, compte, cpte_interne_cli, sens, montant, devise, date_valeur)
				VALUES ((SELECT currval('ad_ecriture_seq')),NumAgc(), cpt_pos_ch, NULL, 'd', cv_frais_tenue_cpte,
				devise_cpte_cr, date(date_prelev));
				--RAISE NOTICE 'MOUVEMENT COMPTABLE AU DEBIT DE LA POSITION DE CHANGE DE LA DEVISE DU COMPTE AU CREDIT D ELOPERATION';

				UPDATE ad_cpt_comptable set solde = solde - cv_frais_tenue_cpte WHERE id_ag=NumAgc() AND num_cpte_comptable = cpt_pos_ch;
				--RAISE NOTICE 'set solde = solde - cv_frais_tenue_cpte';

				--RAISE NOTICE 'FIN IF devise_cpte_cr != code_dev_ref AND devise_cpte_liaison != code_dev_ref';

			END IF; -- FIN IF devise_cpte_cr != code_dev_ref AND devise_cpte_liaison != code_dev_ref

			--RAISE NOTICE 'FIN  IF devise_cpte_cr = devise_cpte_liaison';

		END IF;  -- FIN  IF devise_cpte_cr = devise_cpte_liaison

		---------- FIN DEBIT COMPTE DE LIAISON PAR CREDIT COMPTEG AU CREDIT
		--RAISE NOTICE '---------- FIN DEBIT COMPTE DE LIAISON PAR CREDIT COMPTEG AU CREDIT';

      ELSE

		-- AU MOINS UN DES COMPTES N'EST PAS PRINCIPAL OU LES DEUX SONT PRINCIPAUX DU MEME JOURNAL: PAS BESOIN DONC DE COMPTE DE LIAISON
		--RAISE NOTICE 'AU MOINS UN DES COMPTES NEST PAS PRINCIPAL OU LES DEUX SONT PRINCIPAUX DU MEME JOURNAL: PAS BESOIN DONC DE COMPTE DE LIAISON';

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
		--RAISE NOTICE 'Vérifier que la devise du compte est la devise de référence';
		IF ligne.devise = code_dev_ref THEN       -- Pas de change à effectuer
			--RAISE NOTICE 'Pas de change à effectuer';

			-- prelevement tax TVA sur frais de tenue
			v_scenario := 9; -- devise = code_dev_ref pas de change à effectuer
			v_reglementTax := reglementtaxfraistenue(num_cpte_debit, v_sens_tax, ligne.devise, id_journal, date_prelev, cpt_pos_ch, cpt_cv_pos_ch, ligne.id_cpte, exo.id_exo_compta, v_scenario, ligne.total_frais_tenue_cpt, code_dev_ref, num_ope, cpte_liaison);

			-- prelevement des frais sur le compte du client
			UPDATE ad_cpt SET solde = solde - ligne.total_frais_tenue_cpt WHERE (id_ag=NumAgc() AND id_cpte = ligne.id_cpte);
			--RAISE NOTICE 'prelevement des frais sur le compte du client';

			-- Ecriture comptable
			INSERT INTO ad_ecriture(id_his,id_ag, date_comptable, libel_ecriture, id_jou, id_exo, ref_ecriture,info_ecriture,type_operation)
			VALUES ((SELECT currval('ad_his_id_his_seq')),NumAgc(), date(date_prelev), type_ope.libel_ope, id_journal, exo.id_exo_compta, makeNumEcriture(id_journal, exo.id_exo_compta),ligne.id_cpte,num_ope);
			--RAISE NOTICE 'Ecriture comptable';

			-- Mouvement comptable au débit
			INSERT INTO ad_mouvement (id_ecriture,id_ag, compte, cpte_interne_cli, sens, montant, devise, date_valeur)
			VALUES ((SELECT currval('ad_ecriture_seq')),NumAgc(),num_cpte_debit,ligne.id_cpte, 'd', ligne.total_frais_tenue_cpt, ligne.devise, date(date_prelev));
			--RAISE NOTICE 'Mouvement comptable au débit';

			-- Mouvement comptable au crédit
			INSERT INTO ad_mouvement (id_ecriture,id_ag, compte, cpte_interne_cli, sens, montant, devise, date_valeur)
			VALUES ((SELECT currval('ad_ecriture_seq')),NumAgc(), type_ope.num_cpte, NULL, 'c', ligne.total_frais_tenue_cpt,ligne.devise, date(date_prelev));
			--RAISE NOTICE 'Mouvement comptable au crédit';

			-- Mise à jour des soldes comptables
			UPDATE ad_cpt_comptable set solde = solde - ligne.total_frais_tenue_cpt WHERE id_ag=NumAgc() AND num_cpte_comptable = num_cpte_debit;
			UPDATE ad_cpt_comptable set solde = solde + ligne.total_frais_tenue_cpt WHERE id_ag=NumAgc() AND num_cpte_comptable = type_ope.num_cpte;
			--RAISE NOTICE 'Mise à jour des soldes comptables';


		ELSE  -- La devise du compte n'est pas la devise de référence, il faut mouvementer la position de change
			--RAISE NOTICE 'La devise du compte nest pas la devise de référence, il faut mouvementer la position de change';

			SELECT INTO cpt_pos_ch cpte_position_change || '.' || ligne.devise FROM ad_agc WHERE id_ag=NumAgc();
			SELECT INTO cpt_cv_pos_ch cpte_contreval_position_change || '.' || ligne.devise FROM ad_agc WHERE id_ag=NumAgc();
			--RAISE NOTICE 'cpt_pos_ch = % cpt_cv_pos_ch = %',cpt_pos_ch,cpt_cv_pos_ch;

			--RAISE NOTICE 'cpt_pos_ch = % et cpt_cv_pos_ch = %',cpt_pos_ch, cpt_cv_pos_ch;

			-- prelevement tax TVA sur frais de tenue
			v_scenario := 10; -- devise = code_dev_ref mouvementer la position de change
			v_reglementTax := reglementtaxfraistenue(num_cpte_debit, v_sens_tax, ligne.devise, id_journal, date_prelev, cpt_pos_ch, cpt_cv_pos_ch, ligne.id_cpte, exo.id_exo_compta, v_scenario, ligne.total_frais_tenue_cpt, code_dev_ref, num_ope, cpte_liaison);

			-- prelevement des frais sur le compte du client
			UPDATE ad_cpt SET solde = solde - ligne.total_frais_tenue_cpt WHERE (id_ag=NumAgc() AND id_cpte = ligne.id_cpte);
			--RAISE NOTICE 'SET solde = solde - ligne.total_frais_tenue_cpt';

			-- Ecriture comptable
			INSERT INTO ad_ecriture(id_his,id_ag, date_comptable, libel_ecriture, id_jou, id_exo, ref_ecriture,info_ecriture,type_operation)
			VALUES ((SELECT currval('ad_his_id_his_seq')),NumAgc(), date(date_prelev), type_ope.libel_ope, id_journal, exo.id_exo_compta, makeNumEcriture(id_journal, exo.id_exo_compta),ligne.id_cpte,num_ope);
			--RAISE NOTICE 'Ecriture comptable';

			-- Mouvement comptable au débit
			INSERT INTO ad_mouvement (id_ecriture,id_ag, compte, cpte_interne_cli, sens, montant, devise, date_valeur)
			VALUES ((SELECT currval('ad_ecriture_seq')),NumAgc(),num_cpte_debit,ligne.id_cpte, 'd', ligne.total_frais_tenue_cpt,	ligne.devise, date(date_prelev));
			--RAISE NOTICE 'Mouvement comptable au débit';

			INSERT INTO ad_mouvement (id_ecriture,id_ag, compte, cpte_interne_cli, sens, montant, date_valeur, devise)
			VALUES ((SELECT currval('ad_ecriture_seq')),Numagc(), cpt_pos_ch, NULL, 'c', ligne.total_frais_tenue_cpt, date(date_prelev), ligne.devise);
			--RAISE NOTICE 'Mouvement comptable au credit';

			-- Mise à jour des soldes des comptes comptables
			UPDATE ad_cpt_comptable set solde = solde - ligne.total_frais_tenue_cpt
			WHERE id_ag=NumAgc() AND num_cpte_comptable = num_cpte_debit;
			--RAISE NOTICE 'Mise à jour des soldes des comptes comptables';

			UPDATE ad_cpt_comptable set solde = solde + ligne.total_frais_tenue_cpt
			WHERE id_ag=NumAgc() AND num_cpte_comptable = cpt_pos_ch;
			--RAISE NOTICE 'Mise à jour des soldes des comptes comptables';

			SELECT INTO cv_frais_tenue_cpte CalculeCV(ligne.total_frais_tenue_cpt, ligne.devise, code_dev_ref);
			--RAISE NOTICE 'cv_frais_tenue_cpte = %',cv_frais_tenue_cpte;

			-- Ecriture comptable
			INSERT INTO ad_ecriture(id_his,id_ag, date_comptable, libel_ecriture, id_jou, id_exo, ref_ecriture,info_ecriture,type_operation)
			VALUES ((SELECT currval('ad_his_id_his_seq')),NumAgc(), date(date_prelev),type_ope.libel_ope, id_journal, exo.id_exo_compta, makeNumEcriture(id_journal, exo.id_exo_compta),ligne.id_cpte,num_ope);
			--RAISE NOTICE 'Ecriture comptable';

			INSERT INTO ad_mouvement (id_ecriture,id_ag, compte, cpte_interne_cli, sens, montant, date_valeur, devise)
			VALUES ((SELECT currval('ad_ecriture_seq')),NumAgc(), cpt_cv_pos_ch, NULL, 'd', cv_frais_tenue_cpte, date(date_prelev), code_dev_ref);
			--RAISE NOTICE 'Ecriture comptable';

			-- mouvement comptable au crédit
			INSERT INTO ad_mouvement (id_ecriture,id_ag, compte, cpte_interne_cli, sens, montant, devise, date_valeur)
			VALUES ((SELECT currval('ad_ecriture_seq')),NumAgc(), type_ope.num_cpte, NULL, 'c', cv_frais_tenue_cpte, code_dev_ref, date(date_prelev));
			--RAISE NOTICE 'mouvement comptable au crédit';

			-- mise à jour des soldes comptables
			UPDATE ad_cpt_comptable set solde = solde - cv_frais_tenue_cpte WHERE id_ag=NumAgc() AND num_cpte_comptable = cpt_cv_pos_ch;
			UPDATE ad_cpt_comptable set solde = solde + cv_frais_tenue_cpte WHERE id_ag=NumAgc() AND num_cpte_comptable = type_ope.num_cpte;
			--RAISE NOTICE 'mise à jour des soldes comptables';

		END IF; -- Fin vérification des devises

      --RAISE NOTICE '---------------------------------END IF jou1 is not null and jou2 is not null and jou1 != jou2--------------------------------';
      END IF; -- Fin recherche compte de liaison


      -- construction des données à renvoyer
      SELECT INTO compte_frais ligne.num_complet_cpte, ligne.devise, ligne.id_titulaire, ligne.solde, ligne.total_frais_tenue_cpt;
      RETURN NEXT compte_frais;


    ELSE -- solde_dispo_cpte < 0

      --Mise en attente
      INSERT INTO ad_frais_attente (id_cpte,id_ag, date_frais, type_frais, montant)
      VALUES (ligne.id_cpte ,NumAgc(), date(date_prelev), num_ope, ligne.total_frais_tenue_cpt);
      --RAISE NOTICE 'Compte % mise en attente avec total frais tenue = %',ligne.id_cpte,ligne.total_frais_tenue_cpt;

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
ALTER FUNCTION prelevefraistenuecpt(integer, text, integer, text)
  OWNER TO postgres;


/***********************************************************************************************************************************************/

CREATE OR REPLACE FUNCTION reglementtaxfraistenue(text, text, character, integer, text, text, text, integer, integer, integer, numeric, character, integer, text)
  RETURNS integer AS
$BODY$
DECLARE
  num_cpte_debit ALIAS FOR $1;
  v_sens_tax ALIAS FOR $2;
  v_devise ALIAS FOR $3;
  id_journal ALIAS FOR $4;
  date_prelev ALIAS FOR $5;
  cpt_pos_ch ALIAS FOR $6;
  cpt_cv_pos_ch ALIAS FOR $7;
  id_cpt ALIAS FOR $8;
  id_exo_compta ALIAS FOR $9;
  scenario ALIAS FOR $10;
  frais_tenue ALIAS FOR $11;
  code_dev_ref ALIAS FOR $12;
  num_ope ALIAS FOR $13;
  compte_liaison ALIAS FOR $14;

  output_result INTEGER;

  --v_taux_tax INTEGER;			-- Le taux de tax associe a l'operation comptable
  v_cpte_tax_col TEXT;			-- Le compte comptable tax collecté associe a l'operation comptable
  v_cpte_tax_ded TEXT;			-- Le compte comptable tax recuperé associe a l'operation comptable
  v_info_tax RECORD;			-- array contenant les infos de la taxe associe a l'operation comptable
  v_mnt_tax NUMERIC(30,6) = 0;		-- Le montant tax calculé sur frais de tenue
  --v_sens_tax ALIAS FOR $4;		-- Sens Tax pour mouvement comptables
  v_cpte_tax_debit TEXT;		-- Le compte comptable à débiter pour l'operation mouvement comptable tax
  v_cpte_tax_credit TEXT;		-- Le compte comptable à créditer pour l'operation mouvement comptable tax
  v_libel_tax_ope INTEGER;		-- Le libel de l'operation tax pour ecriture comptable
  v_tax_ope INTEGER;			-- L'operation tax TVA
  v_cpte_oper_tax TEXT;			-- Compte operation tax TVA
  cv_mnt_tax NUMERIC(30,6);		-- C/V montant tax TVA

BEGIN
	-- Récupération infos taxe associe a l'operation comptable
	SELECT INTO v_info_tax t.id, t.taux, t.cpte_tax_col, t.cpte_tax_ded FROM ad_oper_taxe opt INNER JOIN adsys_taxes t ON opt.id_taxe = t.id WHERE opt.id_ag = t.id_ag AND t.id_ag = numagc() AND opt.type_oper = num_ope;
	--RAISE NOTICE 'Id = % Taux de Tax = % Compte Tax Collecté = % Compte Tax Recuperé = %',v_info_tax.id, v_info_tax.taux, v_info_tax.cpte_tax_col, v_info_tax.cpte_tax_ded;
	IF v_info_tax.id IS NOT NULL THEN

		-- Récupération comptes comptables
		v_cpte_tax_col := v_info_tax.cpte_tax_col;
		v_cpte_tax_ded := v_info_tax.cpte_tax_ded;

		-- Calculer le tax sur frais de tenue si necessaire
		v_mnt_tax := v_info_tax.taux * frais_tenue;

		-- setting variables tax sur frais de tenue si necessaire
		--RAISE NOTICE '---------------Tax Info----------------';
		--RAISE NOTICE '==> Montant Tax Calculé = [ % ]',v_mnt_tax;

		--RAISE NOTICE 'Tax info sur frais de tenue';
		IF scenario = 9 OR scenario = 10 THEN
			IF v_sens_tax = 'd' THEN -- sens operation is SENS_DEBIT --> operation 473
				v_tax_ope := 473;
				-- Compte au debit et Compte au credit
				v_cpte_tax_debit := v_cpte_tax_ded;
				v_cpte_tax_credit := num_cpte_debit;
				--RAISE NOTICE 'v_cpte_tax_debit = % v_cpte_tax_credit = %',v_cpte_tax_debit,v_cpte_tax_credit;
			END IF;
			IF v_sens_tax = 'c' THEN -- sens operation is SENS_CREDIT --> operation 474
				v_tax_ope := 474;
				-- Compte au debit et Compte au credit
				v_cpte_tax_debit := num_cpte_debit;
				v_cpte_tax_credit := v_cpte_tax_col;
				--RAISE NOTICE 'v_cpte_tax_debit = % v_cpte_tax_credit = %',v_cpte_tax_debit,v_cpte_tax_credit;
			END IF;
		END IF;

		-- Récupération libel operation 473/474 pour ecriture comptable
		SELECT INTO v_libel_tax_ope t.id_str FROM ad_cpt_ope op INNER JOIN ad_traductions t ON op.libel_ope = t.id_str
		WHERE op.type_operation = v_tax_ope AND langue = (SELECT langue_systeme_dft FROM ad_agc WHERE id_ag = numagc());
		--RAISE NOTICE 'Libel Oper Tax = % Compte debit = % Compte credit = % Operation Tax = %',v_libel_tax_ope,v_cpte_tax_debit,v_cpte_tax_credit,v_tax_ope;
		--RAISE NOTICE '---------------Tax Info----------------';

		IF scenario = 9 THEN
			-- prelevement tax sur frais de tenue
			--RAISE NOTICE '---------------Tax Operation----------------';
			UPDATE ad_cpt SET solde = solde - v_mnt_tax WHERE (id_ag=NumAgc() AND id_cpte = id_cpt);
			--RAISE NOTICE 'prelevement tax sur frais de tenue';
			INSERT INTO ad_ecriture(id_his,id_ag, date_comptable, libel_ecriture, id_jou, id_exo, ref_ecriture,info_ecriture,type_operation)
			VALUES ((SELECT currval('ad_his_id_his_seq')),NumAgc(), date(date_prelev), v_libel_tax_ope, id_journal, id_exo_compta, makeNumEcriture(id_journal, id_exo_compta),id_cpt,v_tax_ope);
			--RAISE NOTICE 'Ecriture comptable';
			INSERT INTO ad_mouvement (id_ecriture,id_ag, compte, cpte_interne_cli, sens, montant, devise, date_valeur)
			VALUES ((SELECT currval('ad_ecriture_seq')),NumAgc(),v_cpte_tax_debit,id_cpt, 'd', v_mnt_tax, v_devise, date(date_prelev));
			--RAISE NOTICE 'Mouvement comptable au débit';
			INSERT INTO ad_mouvement (id_ecriture,id_ag, compte, cpte_interne_cli, sens, montant, devise, date_valeur)
			VALUES ((SELECT currval('ad_ecriture_seq')),NumAgc(), v_cpte_tax_credit, NULL, 'c', v_mnt_tax,v_devise, date(date_prelev));
			--RAISE NOTICE 'Mouvement comptable au crédit';
			-- Mise à jour des soldes comptables
			UPDATE ad_cpt_comptable set solde = solde - v_mnt_tax WHERE id_ag=NumAgc() AND num_cpte_comptable = v_cpte_tax_debit;
			UPDATE ad_cpt_comptable set solde = solde + v_mnt_tax WHERE id_ag=NumAgc() AND num_cpte_comptable = v_cpte_tax_credit;
			--RAISE NOTICE 'Mise à jour des soldes comptables';
			--RAISE NOTICE'---------------Tax Operation----------------';
		END IF;

		IF scenario = 10 THEN
			-- C/V Position de change prelevement tax sur frais de tenue
			--RAISE NOTICE '--------------- C/V Tax Operation ----------------';
			UPDATE ad_cpt SET solde = solde - v_mnt_tax WHERE (id_ag=NumAgc() AND id_cpte = id_cpt);
			--RAISE NOTICE 'C/V prelevement tax sur frais de tenue';
			INSERT INTO ad_ecriture(id_his,id_ag, date_comptable, libel_ecriture, id_jou, id_exo, ref_ecriture,info_ecriture,type_operation)
			VALUES ((SELECT currval('ad_his_id_his_seq')),NumAgc(), date(date_prelev), v_libel_tax_ope, id_journal, id_exo_compta, makeNumEcriture(id_journal, id_exo_compta),id_cpt,v_tax_ope);
			--RAISE NOTICE 'Ecriture comptable';
			INSERT INTO ad_mouvement (id_ecriture,id_ag, compte, cpte_interne_cli, sens, montant, devise, date_valeur)
			VALUES ((SELECT currval('ad_ecriture_seq')),NumAgc(),v_cpte_tax_debit,id_cpt, 'd', v_mnt_tax, v_devise, date(date_prelev));
			--RAISE NOTICE 'Mouvement comptable au débit';
			INSERT INTO ad_mouvement (id_ecriture,id_ag, compte, cpte_interne_cli, sens, montant, devise, date_valeur)
			VALUES ((SELECT currval('ad_ecriture_seq')),NumAgc(), cpt_pos_ch, NULL, 'c', v_mnt_tax,v_devise, date(date_prelev));
			--RAISE NOTICE 'Mouvement comptable au crédit';
			-- Mise à jour des soldes comptables
			UPDATE ad_cpt_comptable set solde = solde - v_mnt_tax WHERE id_ag=NumAgc() AND num_cpte_comptable = v_cpte_tax_debit;
			UPDATE ad_cpt_comptable set solde = solde + v_mnt_tax WHERE id_ag=NumAgc() AND num_cpte_comptable = cpt_pos_ch;
			--RAISE NOTICE 'Mise à jour des soldes comptables';

			SELECT INTO cv_mnt_tax CalculeCV(v_mnt_tax, devise, code_dev_ref);
			--RAISE NOTICE 'cv_mnt_tax = %',cv_mnt_tax;
			INSERT INTO ad_ecriture(id_his,id_ag, date_comptable, libel_ecriture, id_jou, id_exo, ref_ecriture,info_ecriture,type_operation)
			VALUES ((SELECT currval('ad_his_id_his_seq')),NumAgc(), date(date_prelev), v_libel_tax_ope, id_journal, id_exo_compta, makeNumEcriture(id_journal, id_exo_compta),ligne.id_cpte,v_tax_ope);
			--RAISE NOTICE 'Ecriture comptable';
			INSERT INTO ad_mouvement (id_ecriture,id_ag, compte, cpte_interne_cli, sens, montant, devise, date_valeur)
			VALUES ((SELECT currval('ad_ecriture_seq')),NumAgc(),cpt_cv_pos_ch,ligne.id_cpte, 'd', v_mnt_tax, code_dev_ref, date(date_prelev));
			--RAISE NOTICE 'Mouvement comptable au débit';
			INSERT INTO ad_mouvement (id_ecriture,id_ag, compte, cpte_interne_cli, sens, montant, devise, date_valeur)
			VALUES ((SELECT currval('ad_ecriture_seq')),NumAgc(), v_cpte_tax_credit, NULL, 'c', v_mnt_tax,code_dev_ref, date(date_prelev));
			--RAISE NOTICE 'Mouvement comptable au crédit';
			-- Mise à jour des soldes comptables
			UPDATE ad_cpt_comptable set solde = solde - cv_mnt_tax WHERE id_ag=NumAgc() AND num_cpte_comptable = cpt_cv_pos_ch;
			UPDATE ad_cpt_comptable set solde = solde + cv_mnt_tax WHERE id_ag=NumAgc() AND num_cpte_comptable = v_cpte_tax_credit;
			--RAISE NOTICE 'Mise à jour des soldes comptables';
			--RAISE NOTICE'--------------- C/V Tax Operation ----------------';
		END IF;
	END IF;

	output_result := 1;

	RETURN output_result;

END;
$BODY$
  LANGUAGE plpgsql VOLATILE
  COST 100;
ALTER FUNCTION reglementtaxfraistenue(text, text, character, integer, text, text, text, integer, integer, integer, numeric, character, integer, text)
  OWNER TO postgres;

/***************************************************************************************************************************************/

RETURN output_result;

END;
$$
LANGUAGE plpgsql;
select ticket_804_functions();

DROP FUNCTION IF EXISTS ticket_804_functions();
  -------------------------------- Fin ticket #804 ----------------------------------------------------

  -------------------------------- Debut ticket #805 --------------------------------------------------
  CREATE OR REPLACE FUNCTION ticket_805() RETURNS INT AS
$$
DECLARE
output_result INTEGER = 1;
tableliste_ident INTEGER = -1;
d_tableliste_str INTEGER = -1;
oper_str INTEGER = -1;

BEGIN

	-- Nouvelle operation comptable frais duree minimum entre deux retraits

	IF NOT EXISTS(SELECT * FROM ad_cpt_ope WHERE type_operation = 158 AND categorie_ope = 1 AND id_ag = numagc()) THEN
		oper_str := maketraductionlangsyst('Perception des frais de non respect de la durée minimum entre deux rétraits');
		INSERT INTO ad_cpt_ope(type_operation, categorie_ope, id_ag, libel_ope) VALUES (158, 1, numagc(), oper_str);
		IF EXISTS(SELECT langue FROM adsys_langues_systeme WHERE code = 'en_GB') THEN
		  INSERT INTO ad_traductions VALUES (oper_str,'en_GB','Charges Perception of Minimum Duration between two withdrawals not achieved');
		END IF;
		RAISE NOTICE 'Insertion type_operation 158 dans la table ad_cpt_ope effectuée';
		output_result := 2;
	END IF;

	IF NOT EXISTS(SELECT * FROM ad_cpt_ope_cptes WHERE type_operation = 158 AND sens = 'd' AND categorie_cpte = 1 AND id_ag = numagc()) THEN
		INSERT INTO ad_cpt_ope_cptes(type_operation, num_cpte, sens, categorie_cpte, id_ag) VALUES (158, NULL, 'd', 1, numagc());

		RAISE NOTICE 'Insertion type_operation 158 sens ''d'' dans la table ad_cpt_ope_cptes effectuée';
		output_result := 2;
	END IF;

	/*---------------- Ajout nouvelle colonne et mise a jour adsys_produit_epargne ----------------------------*/
	---- Nouvelle Colonne
	select INTO tableliste_ident ident from tableliste where nomc like 'adsys_produit_epargne' order by ident desc limit 1;

	 IF NOT EXISTS(SELECT * FROM d_tableliste WHERE nchmpc = 'frais_duree_min2retrait' and tablen = tableliste_ident) THEN
	   ALTER TABLE adsys_produit_epargne ADD COLUMN frais_duree_min2retrait numeric(30,6) DEFAULT 0;
	   d_tableliste_str := makeTraductionLangSyst('Frais de non respect de la durée minimum entre deux rétraits');
	   INSERT INTO d_tableliste VALUES ((select max(ident) from d_tableliste)+1, tableliste_ident, 'frais_duree_min2retrait', d_tableliste_str, false, NULL, 'mnt', false, false, false);
	   IF EXISTS(SELECT langue FROM adsys_langues_systeme WHERE code = 'en_GB') THEN
	    INSERT INTO ad_traductions VALUES (d_tableliste_str,'en_GB','Charge of Minimum Duration between two withdrawals not achieved');
	   END IF;
	 END IF;

RETURN output_result;

END;
$$
LANGUAGE plpgsql;
select ticket_805();

DROP FUNCTION IF EXISTS ticket_805();
  -------------------------------- Fin ticket #805 ----------------------------------------------------



--------------------------- DEBUT : Tikcet #695 - Demande autorisation de transfert entre compte ---------------------------
-- Function: ticket_695()

CREATE OR REPLACE FUNCTION ticket_695()
  RETURNS integer AS
$BODY$
	DECLARE
		success integer;
		tableliste_ident integer;
		d_tableliste_str integer;

	BEGIN

		RAISE NOTICE 'Started';

		tableliste_ident := (select ident from tableliste where nomc like 'ad_agc' order by ident desc limit 1);
IF NOT EXISTS(SELECT * FROM d_tableliste WHERE nchmpc = 'autorisation_transfert' and tablen = tableliste_ident) THEN
   ALTER TABLE ad_agc ADD autorisation_transfert boolean default false;
   d_tableliste_str := makeTraductionLangSyst('Autorisation de transfert obligatoire');
   INSERT INTO d_tableliste VALUES ((select max(ident) from d_tableliste)+1, tableliste_ident, 'autorisation_transfert', d_tableliste_str, false, NULL, 'bol', false, false, false);
   IF EXISTS(SELECT langue FROM adsys_langues_systeme WHERE code = 'en_GB') THEN
     INSERT INTO ad_traductions VALUES (d_tableliste_str,'en_GB','Authorization of transfert');
   END IF;
 END IF;

		--Creation nouveau fonction Autorisation de transfert : 100
		IF NOT EXISTS (select * from adsys_fonction where code_fonction = 100) THEN
			 --insertion code
			 INSERT INTO adsys_fonction(code_fonction, libelle, id_ag)
			 VALUES (100, 'Paiement transfert autorisé', numagc());
			 RAISE NOTICE 'Fonction created!';
		END IF;

		--Creation nouveau main menu + side menus
		IF NOT EXISTS (select * from menus where nom_menu = 'Pdt') THEN
			--insertion code
			INSERT INTO menus (nom_menu, libel_menu, nom_pere, pos_hierarch, ordre, is_menu,fonction, is_cliquable)
			VALUES ('Pdt', maketraductionlangsyst('Paiement transfert autorisé'), 'Gen-10', 5, 18, true, 100, true);
			RAISE NOTICE 'Main Menu created!';
		END IF;
		IF NOT EXISTS (select * from menus where nom_menu = 'Pdt-1') THEN
			--insertion code
			INSERT INTO menus (nom_menu, libel_menu, nom_pere, pos_hierarch, ordre, is_menu, is_cliquable)
			VALUES ('Pdt-1', maketraductionlangsyst('Liste des transfert autorisé'), 'Pdt', 5, 1, false, false);
			RAISE NOTICE 'Side Menu 1 created!';
		END IF;

		--Creation nouveaux ecrans Pdt-1,
		IF NOT EXISTS (select * from ecrans where nom_ecran = 'Pdt-1') THEN
			--insertion code
			INSERT INTO ecrans(nom_ecran, fichier, nom_menu, fonction)
			VALUES ('Pdt-1', 'modules/epargne/paiement_transfert.php', 'Pdt-1', 100);
			RAISE NOTICE 'Ecran 1 created!';
		END IF;

		-- Insertion ecran de validation pour la demande
		IF NOT EXISTS (select * from ecrans where nom_ecran = 'Tcp-6') THEN
			--insertion code
			INSERT INTO ecrans(nom_ecran, fichier, nom_menu, fonction)
			VALUES ('Tcp-6', 'modules/epargne/transfert_compte.php', 'Tcp-3', 76);
			RAISE NOTICE 'Ecran 1 created!';
		END IF;

		------------------------------

		--Creation nouveau fonction Autorisation de transfert : 152
		IF NOT EXISTS (select * from adsys_fonction where code_fonction = 152) THEN
			 --insertion code
			 INSERT INTO adsys_fonction(code_fonction, libelle, id_ag)
			 VALUES (152, 'Autorisation de transfert', numagc());
			 RAISE NOTICE 'Fonction created!';
		END IF;

		--Creation nouveau main menu + side menus
		IF NOT EXISTS (select * from menus where nom_menu = 'Adt') THEN
			--insertion code
			INSERT INTO menus (nom_menu, libel_menu, nom_pere, pos_hierarch, ordre, is_menu,fonction, is_cliquable)
			VALUES ('Adt', maketraductionlangsyst('Autorisation de transfert'), 'Gen-6', 3, 6, true, 152, true);
			RAISE NOTICE 'Main Menu created!';
		END IF;
		IF NOT EXISTS (select * from menus where nom_menu = 'Adt-1') THEN
			--insertion code
			INSERT INTO menus (nom_menu, libel_menu, nom_pere, pos_hierarch, ordre, is_menu, is_cliquable)
			VALUES ('Adt-1', maketraductionlangsyst('Liste demande de transfert'), 'Adt', 4, 1, false, false);
			RAISE NOTICE 'Side Menu 1 created!';
		END IF;
		IF NOT EXISTS (select * from menus where nom_menu = 'Adt-2') THEN
			--insertion code
			INSERT INTO menus (nom_menu, libel_menu, nom_pere, pos_hierarch, ordre, is_menu, is_cliquable)
			VALUES ('Adt-2', maketraductionlangsyst('Confirmation autorisation de transfert'), 'Adt', 4, 2, false, false);
			RAISE NOTICE 'Side Menu 2 created!';
		END IF;

		--Creation nouveaux ecrans Adt-1, Adt-2,
		IF NOT EXISTS (select * from ecrans where nom_ecran = 'Adt-1') THEN
			--insertion code
			INSERT INTO ecrans(nom_ecran, fichier, nom_menu, fonction)
			VALUES ('Adt-1', 'modules/guichet/demande_autorisation_transfert.php', 'Adt-1', 152);
			RAISE NOTICE 'Ecran 1 created!';
		END IF;
		IF NOT EXISTS (select * from ecrans where nom_ecran = 'Adt-2') THEN
			--insertion code
			INSERT INTO ecrans(nom_ecran, fichier, nom_menu, fonction)
			VALUES ('Adt-2', 'modules/guichet/demande_autorisation_transfert.php', 'Adt-2', 152);
			RAISE NOTICE 'Ecran 2 created!';
		END IF;
		success := 1;

		RAISE NOTICE 'Ended';

	RETURN success;
	END;

$BODY$
  LANGUAGE plpgsql VOLATILE
  COST 100;
ALTER FUNCTION ticket_695()
  OWNER TO postgres;

  SELECT ticket_695();

  DROP FUNCTION IF EXISTS ticket_695();


CREATE OR REPLACE FUNCTION table_transfert_attente() RETURNS INT AS
$$
DECLARE
output_result INTEGER = 1;

BEGIN

	RAISE NOTICE 'START';

	-- Check if field "conn_agc" exist in table "adsys_profils"
	IF NOT EXISTS(SELECT * FROM information_schema.tables WHERE table_name = 'ad_transfert_attente') THEN

	   CREATE TABLE ad_transfert_attente
	(
	  id serial NOT NULL,
	  id_ag integer NOT NULL, -- agence
	  id_client_src integer NOT NULL, -- Id du client emmeteur
	  id_cpte_client_src TEXT, -- num cpte complet client emmeteur
	  montant_transfert numeric(30,6) DEFAULT 0, -- montant a transfer
	  etat_transfert integer NOT NULL, -- l'etat du transfert
	  type_transfert integer NOT NULL, -- Virement int ou ext ou groupé
	  id_client_dest integer , -- le client destinataire
	  id_cpte_client_dest TEXT, -- num cpte complet destinataire
	  id_beneficiaire integer, -- id du beneficiaire de la table tireur_benef
	  id_cpte_ben TEXT, -- compte assoscier au beneficiaire
	  id_correspondant integer, --correspond au correspondant associe au beneficiaire
	  groupe_clients character varying(500), -- correspond au client-montant pour les transferts groupees
	  type_frais_prelev integer, -- type de frais de prelevement
	  mnt_frais_type integer, -- le type montant des frais
	  id_cpte_frais_transfert_prelev text, -- cpte sur lequel prelevement les frais
	  devise_cpte_frais character varying(10), --devise du compte de frais
	  mnt_frais numeric(30,6) DEFAULT 0, -- le montant des frais
	  devise_frais character varying(10), -- la devise du montant des frais
	  type_piece_justificatif integer, -- piece justificatif ( virement / cheque)
	  num_chq_virement text, -- numero de chque
	  date_chq_virement timestamp without time zone, -- date du cheque
	  type_retrait integer, -- le type de retrait
	  id_mandat integer, -- id du mandataire
	  communication text, -- communication
	  remarque text, -- remarque
	  id_his integer, -- L’id_his p
	  login character varying(50) NOT NULL, -- L’id_ecriture pour les mouvements de « reprise »
	  date_crea timestamp without time zone NOT NULL DEFAULT now(), -- date de creation
	  date_modif timestamp without time zone, -- date de mise a jour
	  comments TEXT,
	  CONSTRAINT ad_transfert_attente_pkey PRIMARY KEY (id, id_ag)
	)
	WITH (
	  OIDS=FALSE
	);
	  ALTER TABLE ad_transfert_attente
	  OWNER TO postgres;
	END IF;



	IF NOT EXISTS(SELECT * FROM information_schema.tables WHERE table_name = 'ad_transfert_attente_hist') THEN

   CREATE TABLE ad_transfert_attente_hist
	(
	  id serial NOT NULL,
	  date_action timestamp without time zone,
	  id_transfert_attente integer NOT NULL,
	  etat_transfert integer,
	  comments text,
	  id_ag integer NOT NULL,
	  CONSTRAINT ad_transfert_attente_hist_pkey PRIMARY KEY (id, id_ag)
	)
	WITH (
	  OIDS=FALSE
	);
	  ALTER TABLE ad_transfert_attente_hist
	  OWNER TO postgres;
	END IF;


	CREATE OR REPLACE FUNCTION trig_insert_ad_transfert_attente_hist()
	  RETURNS trigger AS
	$BODY$
		  BEGIN
			INSERT INTO ad_transfert_attente_hist (date_action, id_transfert_attente, etat_transfert, comments, id_ag)
			VALUES (NOW(), OLD.id, OLD.etat_transfert, OLD.comments, OLD.id_ag);
			RETURN NEW;
		  END;
			$BODY$
	  LANGUAGE plpgsql VOLATILE
	  COST 100;
	ALTER FUNCTION trig_insert_ad_transfert_attente_hist()
	  OWNER TO postgres;

  DROP TRIGGER IF EXISTS trig_before_update_ad_transfert_attente ON ad_transfert_attente;

	CREATE TRIGGER trig_before_update_ad_transfert_attente
	BEFORE UPDATE
	ON ad_transfert_attente
	FOR EACH ROW
	EXECUTE PROCEDURE trig_insert_ad_transfert_attente_hist();

	RETURN output_result;

END;
$$
LANGUAGE plpgsql;

SELECT table_transfert_attente();
DROP FUNCTION table_transfert_attente();


--------------------------- Fin : Tikcet #695 - Demande autorisation de transfert entre compte ---------------------------

--------------------------- Debut : Ticket #813 - Urgent lenteur batch passage en perte dossier -------------------------

-- Function: getportfeuilleview(date, integer)

-- DROP FUNCTION getportfeuilleview(date, integer);

CREATE OR REPLACE FUNCTION getportfeuilleviewDoss(
    date,
    integer,
    integer)
  RETURNS SETOF portefeuille_view AS
$BODY$
DECLARE
  date_export ALIAS FOR $1;
  p_id_doss ALIAS FOR $2;
  id_agence ALIAS FOR $3;
  ligne_portefeuille portefeuille_view;
  ligne RECORD;
  ligne_ech RECORD;
  ligne_remb RECORD;
    portefeuille CURSOR FOR SELECT d.gs_cat,d.id_dcr_grp_sol,d.date_dem ,d.id_doss,d.id_client,d.id_ag,d
  .cre_mnt_octr,d.cre_date_debloc,d.duree_mois, d.etat, d.cre_id_cpte, calculnombrejoursretardoss(d.id_doss, date
  (date_export), id_agence) AS nbr_jours_retard, (case WHEN date(date_export) = date(now()) THEN d.cre_etat ELSE CalculEtatCredit(d.id_doss, date(date_export), id_agence) END ) AS cre_etat, d.cre_etat AS cre_etat_cur, d.date_etat, d.cre_date_etat, d.cre_nbre_reech, d.perte_capital, d.id_agent_gest, d.id_prod, d.obj_dem, d.id_ag, d.cre_mnt_deb, d.is_ligne_credit, d.detail_obj_dem, d.detail_obj_dem_bis, d.type_duree_credit, d.periodicite, d.devise, d.is_produit_decouvert, d.differe_ech, d.differe_jours, COALESCE(d.prov_mnt,0) as prov_mnt FROM get_ad_dcr_ext_credit(p_id_doss, null, null, null, id_agence) d WHERE d.id_doss = p_id_doss AND d.cre_date_debloc <= date(date_export) AND ((d.etat IN (5,7,8,13,14,15)) OR (d.etat IN (6,9,11,12) AND d.date_etat > date(date_export))) AND d.id_ag=id_agence  ORDER BY d.id_doss;
  gs_catx integer;
  id_dcr_grp_solx integer;
  date_demx date;
  type_duree_creditx integer;
  nom_client TEXT;
  nbr_ech_total INTEGER;
  nbr_ech_impaye INTEGER;
  mnt_cap_att NUMERIC(30,6);
  mnt_cred_paye NUMERIC(30,6);
  mnt_int_att NUMERIC(30,6);
  mnt_int_paye NUMERIC(30,6);
  mnt_gar_att NUMERIC(30,6);
  mnt_gar_paye NUMERIC(30,6);
  mnt_pen_att NUMERIC(30,6);
  mnt_pen_paye NUMERIC(30,6);
  mnt_gar_mob NUMERIC(30,6);
  solde_retard NUMERIC(30,6);
  int_retard NUMERIC(30,6);
  gar_retard NUMERIC(30,6);
  pen_retard NUMERIC(30,6);
  prev_prov NUMERIC(30,6);
  date_echeance date;
  nbr_jours_retard INTEGER;
  nbre_ech_retard INTEGER;
  jours_retard_ech INTEGER;
  etat_credit TEXT;
  id_etat_credit INTEGER;
  credit_en_perte BOOLEAN;
  id_etat_perte INTEGER;
  taux_prov double precision;
  prov_req NUMERIC(30,6);
  mnt_reech NUMERIC(30,6);
  date_reech date;
  devise_credit character(3);
  is_credit_decouvert BOOLEAN;
  cre_mnt_deb NUMERIC(30,6);
  grace_period INTEGER;
  periodicitex INTEGER;

  differe_echx INTEGER;
  differe_joursx INTEGER;
  gs_periodicite INTEGER;


BEGIN
  -- Récupère l' id de l'état en perte
  SELECT INTO id_etat_perte id FROM adsys_etat_credits WHERE nbre_jours = -1 AND id_ag = id_agence;

  OPEN portefeuille ;
  FETCH portefeuille INTO ligne;
  WHILE FOUND LOOP

  gs_catx := ligne.gs_cat;
  id_dcr_grp_solx := ligne.id_dcr_grp_sol;
  date_demx := ligne.date_dem;
  type_duree_creditx := ligne.type_duree_credit;

  -- Récupère le nom du client
  SELECT INTO nom_client CASE statut_juridique WHEN '1' THEN pp_nom||' '||pp_prenom WHEN '2' THEN pm_raison_sociale WHEN '3'  THEN gi_nom WHEN '4'  THEN gi_nom END FROM ad_cli
  WHERE id_client = ligne.id_client;

  -- periodicité
  periodicitex := ligne.periodicite;

  -- grace_periode
  IF (ligne.differe_ech is null) THEN
	differe_echx := 0;
  ELSE
	differe_echx := ligne.differe_ech;
  END IF;

  IF (ligne.periodicite = 1) THEN
	gs_periodicite := 30;
  ELSIF (ligne.periodicite = 2) THEN
	gs_periodicite := 15;
  ELSIF (ligne.periodicite = 3) THEN
	gs_periodicite := 90;
  ELSIF (ligne.periodicite = 4) THEN
	gs_periodicite := 180;
  ELSIF (ligne.periodicite = 5) THEN
	gs_periodicite := 365;
  ELSIF (ligne.periodicite = 6) THEN
	gs_periodicite := 0;
  ELSIF (ligne.periodicite = 7) THEN
	gs_periodicite := 60;
  ELSE
	gs_periodicite := 7;
  END IF;

  IF (ligne.differe_jours is null) THEN
	differe_joursx := 0;
  ELSE
	differe_joursx := ligne.differe_jours;
  END IF;

  grace_period := ((differe_echx * gs_periodicite) + differe_joursx);

 -- Parcourir les échéances
  nbr_ech_total := 0;
  nbr_ech_impaye := 0;
  mnt_cap_att := 0;
  mnt_cred_paye := 0;
  mnt_int_att := 0;
  mnt_int_paye := 0;
  mnt_gar_att := 0;
  mnt_gar_paye := 0;
  mnt_pen_att := 0;
  mnt_pen_paye := 0;
  mnt_gar_mob := 0;
  solde_retard := 0;
  int_retard := 0;
  gar_retard := 0;
  pen_retard := 0;
  prev_prov := 0;
  mnt_reech := 0;
  date_echeance := ligne.cre_date_debloc;

  --nbr_jours_retard := 0;
  nbre_ech_retard := 0;
  FOR ligne_ech IN SELECT *, COALESCE(CalculMntPenEch(ligne.id_doss, id_ech, date_export, id_agence),0) AS mnt_pen FROM ad_etr e WHERE id_doss = ligne.id_doss AND id_ag=id_agence ORDER BY date_ech
    LOOP
     nbr_ech_total := nbr_ech_total + 1;
     -- Maturity date
     IF (date_echeance < ligne_ech.date_ech) THEN
     	date_echeance := ligne_ech.date_ech;
     END IF;
     mnt_cap_att := mnt_cap_att + COALESCE(ligne_ech.mnt_cap,0);
     mnt_int_att := mnt_int_att + COALESCE(ligne_ech.mnt_int,0);
     mnt_gar_att := mnt_gar_att + COALESCE(ligne_ech.mnt_gar,0);
     mnt_pen_att := mnt_pen_att + COALESCE(ligne_ech.mnt_pen,0);
     mnt_reech := mnt_reech + COALESCE(ligne_ech.mnt_reech,0);
     SELECT  INTO ligne_remb sum(COALESCE(mnt_remb_cap,0)) AS mnt_remb_cap, sum(COALESCE(mnt_remb_int,0)) AS mnt_remb_int,
       sum(COALESCE(mnt_remb_gar,0)) AS mnt_remb_gar, sum(COALESCE(mnt_remb_pen,0)) AS mnt_remb_pen
       FROM ad_sre WHERE id_ech = ligne_ech.id_ech AND id_doss = ligne.id_doss AND date_remb <= date_export AND id_ag=id_agence;
     mnt_cred_paye := mnt_cred_paye + COALESCE(ligne_remb.mnt_remb_cap,0);
     mnt_int_paye := mnt_int_paye + COALESCE(ligne_remb.mnt_remb_int,0);
     mnt_gar_paye := mnt_gar_paye + COALESCE(ligne_remb.mnt_remb_gar,0);
     mnt_pen_paye := mnt_pen_paye + COALESCE(ligne_remb.mnt_remb_pen,0);
     -- Si l'échéance est non remboursée
     IF ((ligne_ech.mnt_cap > COALESCE(ligne_remb.mnt_remb_cap,0)) OR (ligne_ech.mnt_int > COALESCE(ligne_remb.mnt_remb_int,0)) OR (ligne_ech.mnt_gar > COALESCE(ligne_remb.mnt_remb_gar,0)) OR (ligne_ech.mnt_pen > COALESCE(ligne_remb.mnt_remb_pen,0))) THEN
         nbr_ech_impaye := nbr_ech_impaye + 1;
         -- Solde, intérêt, garantie, pénalité en retard et nombre de jours de retard
         jours_retard_ech := date_part('day', date_export::timestamp - ligne_ech.date_ech::timestamp);
         IF (ligne_ech.date_ech < date_export) THEN
            IF (ligne_ech.mnt_cap > COALESCE(ligne_remb.mnt_remb_cap,0)) THEN
	          solde_retard := solde_retard + (COALESCE(ligne_ech.mnt_cap,0) - COALESCE(ligne_remb.mnt_remb_cap,0));
            END IF;
            IF (ligne_ech.mnt_int > COALESCE(ligne_remb.mnt_remb_int,0)) THEN
	          int_retard := int_retard + (COALESCE(ligne_ech.mnt_int,0) - COALESCE(ligne_remb.mnt_remb_int,0));
            END IF;
            IF (ligne_ech.mnt_gar > COALESCE(ligne_remb.mnt_remb_gar,0)) THEN
	          gar_retard := gar_retard + (COALESCE(ligne_ech.mnt_gar,0) - COALESCE(ligne_remb.mnt_remb_gar,0));
            END IF;
            IF (ligne_ech.mnt_pen > COALESCE(ligne_remb.mnt_remb_pen,0)) THEN
	          pen_retard := pen_retard + (COALESCE(ligne_ech.mnt_pen,0) - COALESCE(ligne_remb.mnt_remb_pen,0));
            END IF;
            --IF (nbr_jours_retard < jours_retard_ech) THEN
            --  nbr_jours_retard := jours_retard_ech;
            --END IF;
            nbre_ech_retard := nbre_ech_retard + 1;
         END IF;
     END IF;
    END LOOP; -- Fin de calcul des infos sur les échéances

  -- infos du produit de crédit
  devise_credit := ligne.devise;
  is_credit_decouvert := ligne.is_produit_decouvert;
  -- état du crédit, taux et montant de la provision

  IF ((ligne.cre_etat_cur = id_etat_perte) AND ligne.cre_date_etat <= date(date_export)) THEN
   id_etat_credit := id_etat_perte;
   credit_en_perte := 't';
   SELECT INTO mnt_gar_mob sum(COALESCE(calculsoldecpte(gar_num_id_cpte_nantie, NULL, date(date_export)), 0)) FROM ad_gar WHERE id_doss = ligne.id_doss AND type_gar = 1 AND id_ag = id_agence;
  ELSE
    --id_etat_credit := 1;
   --id_etat_credit := CalculEtatCredit(ligne.cre_id_cpte, date(date_export), id_agence);
   id_etat_credit := ligne.cre_etat;
   credit_en_perte := 'f';
   SELECT INTO mnt_gar_mob sum(COALESCE(calculsoldecpte(gar_num_id_cpte_nantie, NULL, date_export), 0)) FROM ad_gar WHERE id_doss = ligne.id_doss AND type_gar = 1 AND id_ag = id_agence;
  END IF;

  IF (id_etat_credit IS NOT NULL) THEN
    SELECT INTO etat_credit, taux_prov libel, COALESCE(taux, 0) FROM adsys_etat_credits WHERE id = id_etat_credit AND id_ag = id_agence;
  END IF;
  -- Previous provisions
      --SELECT INTO prev_prov COALESCE(montant,0) FROM ad_provision WHERE id_doss = ligne.id_doss AND id_ag = id_agence AND date_prov = (SELECT MAX(date_prov)
      --FROM ad_provision WHERE date_prov < date_export AND id_doss = ligne.id_doss AND id_ag = id_agence);

  --new code for previous provision
    IF (date(date_export)=  date(now())) THEN
		prev_prov := ligne.prov_mnt;
     ELSE
        SELECT INTO prev_prov COALESCE(montant,0) FROM ad_provision WHERE id_doss = ligne.id_doss AND id_ag = id_agence AND date_prov = (SELECT MAX(date_prov) FROM ad_provision WHERE date_prov <= date_export AND id_doss = ligne.id_doss AND id_ag = id_agence) order by id_provision desc limit 1 ;
  END IF ;


 -- solde et nombres jours de retard du credit
 --solde := 0;
 --solde := calculsoldecpte(ligne.cre_id_cpte, NULL, date(date_export));
 --nbr_jours_retard := 1;
 -- nbr_jours_retard := calculnombrejoursretardoss(ligne.cre_id_cpte, date(date_export), id_agence);
 -- Reechelonnement
  IF (ligne.cre_nbre_reech > 0) THEN
  	SELECT INTO date_reech h.date from ad_his h where type_fonction = 146 and infos = ligne.id_doss::text AND id_ag = id_agence;
  	IF (date_reech > date_export) THEN
  	  mnt_cap_att := mnt_cap_att - mnt_reech;
  	END IF;
  END IF;
  -- Resultat de la vue

  SELECT INTO ligne_portefeuille  ligne.id_doss, ligne.id_client, ligne.id_prod, ligne.obj_dem, date_demx, (mnt_cap_att) AS cre_mnt_octr, gs_catx, id_dcr_grp_solx, devise_credit AS devise, ligne.cre_id_cpte, ligne.cre_date_debloc, ligne.date_etat AS date_etat_doss, type_duree_creditx, ligne.duree_mois, id_etat_credit, ligne.cre_date_etat, credit_en_perte, ligne.perte_capital, nom_client AS nom_cli, nbr_ech_total,(nbr_ech_total - nbr_ech_impaye) AS nbr_ech_paye, mnt_cred_paye, mnt_int_att, mnt_int_paye, mnt_gar_att, mnt_gar_paye, mnt_pen_att, mnt_pen_paye, COALESCE(mnt_gar_mob,0), solde_retard, int_retard, gar_retard, pen_retard, date_echeance, ligne.nbr_jours_retard, nbre_ech_retard, etat_credit, ligne.cre_nbre_reech, taux_prov, COALESCE(prev_prov,0) AS prov_mnt, ligne.id_agent_gest, is_credit_decouvert, ligne.id_ag, ligne.cre_mnt_deb, grace_period, periodicitex as periodicite, ligne.is_ligne_credit,ligne.detail_obj_dem,ligne.detail_obj_dem_bis;
  RETURN NEXT ligne_portefeuille;
  FETCH portefeuille INTO ligne;
  END LOOP;
 CLOSE portefeuille;
RETURN;
END;
$BODY$
  LANGUAGE plpgsql VOLATILE
  COST 100
  ROWS 1000;
ALTER FUNCTION getportfeuilleviewDoss(date,integer, integer)
  OWNER TO adbanking;


-------------------------- Fin : Ticket #813 ---------------------------------------------------------------------------

-------------------------- Debut : Ticket #739 -------------------------------------------------------------------------
CREATE OR REPLACE FUNCTION ticket_739() RETURNS INT AS
$$
DECLARE
output_result INTEGER = 1;
tableliste_ident INTEGER = -1;
d_tableliste_str INTEGER = -1;
oper_str INTEGER = -1;

BEGIN

	-- Nouvelle operation comptable 477 annulation paiement TVA deductible l'operation inverse de 473

	IF NOT EXISTS(SELECT * FROM ad_cpt_ope WHERE type_operation = 477 AND categorie_ope = 1 AND id_ag = numagc()) THEN
		oper_str := maketraductionlangsyst('Annulation paiement TVA deductible');
		INSERT INTO ad_cpt_ope(type_operation, categorie_ope, id_ag, libel_ope) VALUES (477, 1, numagc(), oper_str);
		IF EXISTS(SELECT langue FROM adsys_langues_systeme WHERE code = 'en_GB') THEN
		  INSERT INTO ad_traductions VALUES (oper_str,'en_GB','Cancellation of deductible VAT payment');
		  RAISE NOTICE 'Insertion type_operation 477 dans la table ad_cpt_ope effectuée';
		END IF;
		output_result := 2;
	END IF;

	IF NOT EXISTS(SELECT * FROM ad_cpt_ope_cptes WHERE type_operation = 477 AND sens = 'c' AND categorie_cpte = 1 AND id_ag = numagc()) THEN
		INSERT INTO ad_cpt_ope_cptes(type_operation, num_cpte, sens, categorie_cpte, id_ag) VALUES (477, NULL, 'c', 1, numagc());

		RAISE NOTICE 'Insertion type_operation 477 sens ''c'' dans la table ad_cpt_ope_cptes effectuée';
		output_result := 2;
	END IF;

	-- Nouvelle operation comptable 478 annulation perception TVA collectee l'operation inverse de 474

	IF NOT EXISTS(SELECT * FROM ad_cpt_ope WHERE type_operation = 478 AND categorie_ope = 1 AND id_ag = numagc()) THEN
		oper_str := maketraductionlangsyst('Annulation perception TVA collectée');
		INSERT INTO ad_cpt_ope(type_operation, categorie_ope, id_ag, libel_ope) VALUES (478, 1, numagc(), oper_str);
		IF EXISTS(SELECT langue FROM adsys_langues_systeme WHERE code = 'en_GB') THEN
		  INSERT INTO ad_traductions VALUES (oper_str,'en_GB','Cancellation of VAT collected');
		  RAISE NOTICE 'Insertion type_operation 478 dans la table ad_cpt_ope effectuée';
		END IF;
		output_result := 2;
	END IF;

	IF NOT EXISTS(SELECT * FROM ad_cpt_ope_cptes WHERE type_operation = 478 AND sens = 'c' AND categorie_cpte = 1 AND id_ag = numagc()) THEN
		INSERT INTO ad_cpt_ope_cptes(type_operation, num_cpte, sens, categorie_cpte, id_ag) VALUES (478, NULL, 'c', 1, numagc());

		RAISE NOTICE 'Insertion type_operation 478 sens ''c'' dans la table ad_cpt_ope_cptes effectuée';
		output_result := 2;
	END IF;
	

RETURN output_result;

END;
$$
LANGUAGE plpgsql;
select ticket_739();

DROP FUNCTION IF EXISTS ticket_739();
-------------------------- Fin : Ticket #739 ---------------------------------------------------------------------------

--------------------------- DEBUT : Ticket #667 | PP#221 : Rapport 'Inventaire de crédits' ---------------------------

DROP TYPE IF EXISTS inventairecredits_view CASCADE;

CREATE TYPE inventairecredits_view AS
(
  type_rapport text,
  id_prod integer,
  obj_dem integer,
  id_doss integer,
  cre_date_debloc date,
  cre_mnt_deb numeric(30,6),
  etat integer,
  date_etat date,
  cre_etat integer,
  cre_date_etat date,
  id_client integer,
  nom_cli text,
  mnt_cap_debut numeric(30,6),
  cre_mnt_deb_per numeric(30,6),
  tot_mnt_remb_cap numeric(30,6),
  mnt_remb_cap numeric(30,6),
  tot_mnt_remb_int numeric(30,6),
  mnt_remb_pen numeric(30,6),
  montant_tot numeric(30,6),
  mnt_tot numeric(30,6),
  mnt_restant_du numeric(30,6),
  perte_capital numeric(30,6)
);

-- Function: getinventairecredits(date, date, integer)


CREATE OR REPLACE FUNCTION getinventairecredits(date, date, integer)
  RETURNS SETOF inventairecredits_view AS
$BODY$
DECLARE
  DECLARE
  p_date_debut ALIAS FOR $1;
  p_date_fin ALIAS FOR $2;
  p_id_etat ALIAS FOR $3;

  table_remb_result integer;
  v_date_fin date;

  ligne_inventaire inventairecredits_view;
  ligne RECORD;


 opening_list CURSOR for select R.type_rapport, R.id_prod, R.obj_dem, R.id_doss, date(R.cre_date_debloc) as cre_date_debloc, R.cre_id_cpte, R.cre_mnt_octr, R.etat_dossier, R.date_etat_doss,
				max(R.id_etat_credit) as id_etat_credit, R.cre_date_etat, R.id_client, R.nom_cli,
				case when R.cre_date_debloc >= date(p_date_debut) then R.cre_mnt_deb else 0.000000 end as cre_mnt_deb_per, sum(R.capital_debut) as capital_debut,
				abs(sum(R.int_rembourse_fin) - sum(R.int_rembourse_debut)) as int_rembourse_period, abs(sum(R.pen_rembourse_fin) - sum(R.pen_rembourse_debut)) as pen_rembourse_period,
				sum(case when R.type_rapport = '4-RADIE' and R.cre_date_etat between date(p_date_debut) and date(p_date_fin) then R.perte_capital else R.capital_fin end) as capital_fin,
				R.perte_capital
			from
			(
				select
				case
				when v.id_etat_credit = p_id_etat and dcr.etat = 6 then '3-RADIE-SOLDE'
				when dcr.etat = 6 and v.date_etat_doss between date(p_date_debut) and date(p_date_fin) then '2-SOLDE'
				when dcr.etat = 9 and v.date_etat_doss between date(p_date_debut) and date(p_date_fin) then '4-RADIE'
				else '1-ENCOURS' end as type_rapport,
				v.id_doss, v.id_etat_credit, dcr.cre_etat as dcr_cre_etat,  v.cre_date_etat, dcr.etat as etat_dossier, v.date_etat_doss, v.id_client, v.id_prod,
				v.obj_dem, v.nom_cli, v.cre_mnt_octr, dcr.cre_mnt_deb, dcr.cre_date_debloc, v.cre_id_cpte, coalesce(v.mnt_int_paye,0.000000) as int_rembourse_debut,
				coalesce(v.mnt_pen_paye,0.000000) as pen_rembourse_debut, 0.000000 as int_rembourse_fin, 0.000000 as pen_rembourse_fin,
				coalesce(v.cre_mnt_octr,0.000000) - coalesce(v.mnt_cred_paye,0.000000) as capital_debut, 0.000000 as capital_fin,
				case when dcr.etat = 6 and v.id_etat_credit = p_id_etat and v.cre_date_etat < date(p_date_debut) then 0.000000 else dcr.perte_capital end as perte_capital
				from getportfeuilleview(date(p_date_debut),numagc()) v
				inner join ad_dcr dcr on v.id_ag = dcr.id_ag and dcr.id_doss = v.id_doss

				union all

				select
				case
				when v.id_etat_credit = p_id_etat and dcr.etat = 6 then '3-RADIE-SOLDE'
				when dcr.etat = 6 and v.date_etat_doss between date(p_date_debut) and date(p_date_fin) then '2-SOLDE'
				when dcr.etat = 9 and v.date_etat_doss between date(p_date_debut) and date(p_date_fin) then '4-RADIE'
				else '1-ENCOURS' end as type_rapport,
				v.id_doss, v.id_etat_credit, dcr.cre_etat as dcr_cre_etat,  v.cre_date_etat, dcr.etat as etat_dossier, v.date_etat_doss, v.id_client, v.id_prod,
				v.obj_dem, v.nom_cli, v.cre_mnt_octr, dcr.cre_mnt_deb, dcr.cre_date_debloc, v.cre_id_cpte, 0.000000 as int_rembourse_debut, 0.000000 as pen_rembourse_debut,
				coalesce(v.mnt_int_paye,0.000000) as int_rembourse_fin,	coalesce(v.mnt_pen_paye,0.000000) as pen_rembourse_fin, 0.000000 as capital_debut,
				coalesce(v.cre_mnt_octr,0.000000) - coalesce(v.mnt_cred_paye,0.000000) as capital_fin,
				case when dcr.etat = 6 and v.id_etat_credit = p_id_etat and v.cre_date_etat < date(p_date_debut) then 0.000000 else dcr.perte_capital end as perte_capital
				from getportfeuilleview(date(p_date_fin),numagc()) v
				inner join ad_dcr dcr on v.id_ag = dcr.id_ag and dcr.id_doss = v.id_doss
			) R
			group by R.id_doss, R.type_rapport, R.id_prod, R.obj_dem, R.cre_date_debloc, R.cre_id_cpte, R.cre_mnt_octr, R.cre_mnt_deb, R.etat_dossier, R.date_etat_doss,
			R.cre_date_etat, R.id_client, R.nom_cli, R.perte_capital
			order by R.type_rapport asc;


tot_mnt_remb_cap numeric(30,6):=0;
tot_mnt_remb_cap1 numeric(30,6):=0; -- test
tot_mnt_remb_int numeric(30,6):=0;
tot_mnt_remb_pen numeric(30,6):=0;
mnt_remb_cap numeric(30,6):=0;
montant_tot  numeric(30,6):=0;
mnt_tot numeric(30,6):=0;
cre_mnt_deb_per numeric(30,6):=0;
capital_fin numeric(30,6):=0;
capital_fin1 numeric(30,6):=0; --test
perte_cap numeric(30,6):=0;
perte_capital1 numeric(30,6):=0; --test
mnt_cap_debut numeric(30,6):=0; --test

BEGIN

  v_date_fin := p_date_fin;

  OPEN opening_list ;
  FETCH opening_list INTO ligne;
  WHILE FOUND LOOP
	v_date_fin := p_date_fin;
	--montant debourse au cours de la periode : par defaut ligne.cre_mnt_deb_per sinon c'est ligne.cre_mnt_octr pour l'etat deboursement progressif
	cre_mnt_deb_per := ligne.cre_mnt_deb_per;
	IF ligne.etat_dossier = 13 THEN
		cre_mnt_deb_per := ligne.cre_mnt_octr;
	END IF;

	--capital fin : par defaut ligne.capital_fin sinon c'est 0 pour les radiés
	capital_fin := ligne.capital_fin;
	IF ligne.type_rapport = '4-RADIE' THEN
		capital_fin := 0;
	END IF;

	--perte capital
	perte_cap := ligne.perte_capital;
	if ligne.perte_capital > 0 then
		perte_cap := 0;
	end if;

	--montant rembourse au cours de la periode
	tot_mnt_remb_cap := (ligne.capital_debut + cre_mnt_deb_per) - (capital_fin + ligne.perte_capital); --pour le total
	mnt_remb_cap := (ligne.capital_debut + cre_mnt_deb_per) - (ligne.capital_fin + perte_cap); --pour la partie detail

	--interets ordinaires et interets de retards
	IF ligne.type_rapport = '4-RADIE' OR (ligne.type_rapport = '2-SOLDE' AND ligne.perte_capital > 0) THEN
		SELECT INTO v_date_fin max(e.date_comptable) FROM ad_mouvement m INNER JOIN ad_ecriture e ON m.id_ecriture = e.id_ecriture WHERE e.type_operation = 280
		AND m.cpte_interne_cli = ligne.cre_id_cpte AND e.date_comptable >= date(p_date_debut) AND e.date_comptable <= date(p_date_fin)
		GROUP BY m.cpte_interne_cli; --date quand le dossier est passé en perte
		--v_date_fin := v_date_fin; --ligne.cre_date_etat;

	END IF;
	SELECT INTO tot_mnt_remb_int sum(case when e.type_operation IN (10,20,374) and m.sens = 'c' then coalesce(m.montant,0)
	when e.type_operation IN (11,21,375) and m.sens = 'd' then coalesce(m.montant,0)*-1 else 0 end)
	FROM ad_mouvement m INNER JOIN ad_ecriture e ON m.id_ecriture = e.id_ecriture INNER JOIN ad_his h ON h.id_his = e.id_his
	WHERE m.compte IN (SELECT DISTINCT cpte_cpta_prod_cr_int FROM adsys_produit_credit) --m.compte = p.cpte_cpta_prod_cr_int
	AND coalesce(e.info_ecriture,h.infos) = to_char(ligne.id_doss,'FM999999999MI') AND e.type_operation IN (10,11,20,21,374,375)
	AND e.date_comptable >= date(p_date_debut) AND e.date_comptable <= date(v_date_fin) GROUP BY coalesce(e.info_ecriture,h.infos); --interet ordinaire

	SELECT INTO tot_mnt_remb_pen sum(case when e.type_operation IN (30,374) and m.sens = 'c' then coalesce(m.montant,0)
	when e.type_operation IN (31,375) and m.sens = 'd' then coalesce(m.montant,0)*-1 else 0 end)
	FROM ad_mouvement m INNER JOIN ad_ecriture e ON m.id_ecriture = e.id_ecriture
	WHERE m.compte IN (SELECT DISTINCT cpte_cpta_prod_cr_pen FROM adsys_produit_credit) --m.compte = p.cpte_cpta_prod_cr_pen
	AND e.info_ecriture = to_char(ligne.id_doss,'FM999999999MI') AND e.type_operation IN (30,31,374,375)
	AND e.date_comptable >= date(p_date_debut) AND e.date_comptable <= date(v_date_fin) GROUP BY e.info_ecriture; --GROUP BY e.info_ecriture; --interet de retard

	IF tot_mnt_remb_int IS NULL THEN
			tot_mnt_remb_int := 0;
	END IF;
	IF tot_mnt_remb_pen IS NULL THEN
			tot_mnt_remb_pen := 0;
	END IF;

	--sum des montants rembourse au cours de la periode (capital + interet + retard)
	montant_tot := tot_mnt_remb_cap + tot_mnt_remb_int + tot_mnt_remb_pen; --pour le total
	mnt_tot := mnt_remb_cap + tot_mnt_remb_int + tot_mnt_remb_pen; --pour la partie detail

	--debug et test
	--les totaux
	tot_mnt_remb_cap1 := tot_mnt_remb_cap1 + tot_mnt_remb_cap;
	capital_fin1 := capital_fin1 + capital_fin;
	perte_capital1 := perte_capital1 + ligne.perte_capital;
	mnt_cap_debut := mnt_cap_debut + ligne.capital_debut;

  --Affichage ligne du rapport
  /*RAISE NOTICE '-------------------------------';
  RAISE NOTICE 'Numero Dossier : %',ligne.id_doss;
  RAISE NOTICE ' | Type rapport - % | Obj Dem - % | Cre Date Debloc - % | Cre mnt Deb - % | Etat - % | Date Etat - % | Cre Etat - % | Cre Date Etat - % | ID Client - % |
  Nom Cli - % | Capital Debut - % | Capital Debourse Periode - % | Capital Rembourse Periode - % | Interet Rembourse Periode - % | Retard Rembourse Periode - % |
  Montant Total Rembourse - % | Capital Fin - % | Perte Capital - %',ligne.type_rapport,ligne.obj_dem,ligne.cre_date_debloc,ligne.cre_mnt_octr,ligne.etat_dossier,
  ligne.date_etat_doss,ligne.id_etat_credit,ligne.cre_date_etat,ligne.id_client,ligne.nom_cli,ligne.capital_debut,cre_mnt_deb_per,mnt_remb_cap,tot_mnt_remb_int,
  tot_mnt_remb_pen,mnt_tot,capital_fin,ligne.perte_capital;
  RAISE NOTICE '-------------------------------';*/

  --ligne du rapport
  SELECT INTO ligne_inventaire  ligne.type_rapport,ligne.id_prod,ligne.obj_dem,ligne.id_doss,ligne.cre_date_debloc,ligne.cre_mnt_octr,ligne.etat_dossier,ligne.date_etat_doss,ligne.id_etat_credit,
  ligne.cre_date_etat,ligne.id_client,ligne.nom_cli,ligne.capital_debut,cre_mnt_deb_per,tot_mnt_remb_cap,mnt_remb_cap,tot_mnt_remb_int,tot_mnt_remb_pen,montant_tot,mnt_tot,capital_fin,ligne.perte_capital;
  RETURN NEXT ligne_inventaire;


  FETCH opening_list INTO ligne;
  END LOOP;

  --Affichage totaux
  /*RAISE NOTICE '-------------------------------';
  RAISE NOTICE 'Total Capital Debut Periode : %',mnt_cap_debut;
  RAISE NOTICE 'Total Capital Rembourse au cours de la Periode : %',tot_mnt_remb_cap1;
  RAISE NOTICE 'Total Capital Fin Periode : %',capital_fin1;
  RAISE NOTICE 'Total Perte Capital Fin Periode : %',perte_capital1;
  RAISE NOTICE '-------------------------------';*/

  CLOSE opening_list;
  RETURN;
END;
$BODY$
LANGUAGE plpgsql VOLATILE
COST 100
ROWS 1000;
ALTER FUNCTION getinventairecredits(date, date, integer)
OWNER TO adbanking;
--------------------------- FIN : Ticket #667 | PP#221 : Rapport 'Inventaire de crédits' ---------------------------
--------------------------- DEBUT : Ticket #750 Correctif dans fonction calculesommemvtcpterecursif ---------------------------
-- Function: calculesommemvtcpterecursif(text, date, date, character, integer, boolean)

DROP FUNCTION IF EXISTS calculesommemvtcpterecursif(text, date, date, character, integer, boolean);

CREATE OR REPLACE FUNCTION calculesommemvtcpterecursif(text, date, date, character, integer, boolean)
  RETURNS numeric AS
$BODY$
 DECLARE
        cpte ALIAS FOR $1;   			-- Numéro du compte
	date_debut  ALIAS FOR $2;		-- Date du solde
	date_fin  ALIAS FOR $3;		-- Date du solde

	v_sens_cpte ALIAS FOR $4;			-- id de l'agence : ticket 750
	idAgc ALIAS FOR $5;			-- id de l'agence
	is_consolide ALIAS FOR $6;		--vrai si on veut editer les états financiers consolidés
	solde_debit NUMERIC(30,6):=0;		--solde au débit du compte
	solde_credit NUMERIC(30,6):=0;		--solde au crédit du compte comptable
	solde_reciproque NUMERIC(30,6):=0;	--solde des mouvement reciproque(mvt passé entre l'agence etle siege ou vice versa)
	total_debits NUMERIC(30,6):=0;		--solde courant du compte comptable( solde actuel ds ad_cpt_comptable)

	BEGIN
	select INTO total_debits sum( calculeSommeMvtCpte(d.num_cpte_comptable,date_debut,date_fin,v_sens_cpte,idAgc, is_consolide))
	from ad_cpt_comptable d where ( (num_cpte_comptable = cpte) OR (num_cpte_comptable LIKE cpte||'.%') )   ;

        RETURN COALESCE(total_debits,0);
 END;
$BODY$
  LANGUAGE plpgsql VOLATILE
  COST 100;
ALTER FUNCTION calculesommemvtcpterecursif(text, date, date, character, integer, boolean)
  OWNER TO postgres;
--------------------------- FIN : Ticket #750 Correctif dans fonction calculesommemvtcpterecursif ---------------------------
--------------------------- DEBUT : Ticket #696 Demande d'amelioration Simulation pour les produits epargne DAT ---------------------------
-- Function: ticket_696()

CREATE OR REPLACE FUNCTION ticket_696()
  RETURNS integer AS
$BODY$
	DECLARE
		success integer;

	BEGIN

		RAISE NOTICE 'Started';

		--Creation nouveau fonction Simulation Echeancier Produit Epargne : 68
		IF NOT EXISTS (select * from adsys_fonction where code_fonction = 68) THEN
			 --insertion code
			 INSERT INTO adsys_fonction(code_fonction, libelle, id_ag)
			 VALUES (68, 'Simulation échéancier', numagc());
			 --RAISE NOTICE 'Fonction created!';
		END IF;

		--Creation nouveau main menu + side menus
		IF NOT EXISTS (select * from menus where nom_menu = 'Spe') THEN
			--insertion code
			INSERT INTO menus (nom_menu, libel_menu, nom_pere, pos_hierarch, ordre, is_menu,fonction, is_cliquable)
			VALUES ('Spe', maketraductionlangsyst('Simulation échéancier'), 'Gen-10', 5, 15, true, 68, true);
			--RAISE NOTICE 'Main Menu created!';
		END IF;
		IF NOT EXISTS (select * from menus where nom_menu = 'Spe-1') THEN
			--insertion code
			INSERT INTO menus (nom_menu, libel_menu, nom_pere, pos_hierarch, ordre, is_menu, is_cliquable)
			VALUES ('Spe-1', maketraductionlangsyst('Choix du Produit'), 'Spe', 6, 1, false, false);
			--RAISE NOTICE 'Side Menu 1 created!';
		END IF;
		IF NOT EXISTS (select * from menus where nom_menu = 'Spe-3') THEN
			--insertion code
			INSERT INTO menus (nom_menu, libel_menu, nom_pere, pos_hierarch, ordre, is_menu, is_cliquable)
			VALUES ('Spe-3', maketraductionlangsyst('Impression proposition'), 'Spe', 6, 4, false, false);
			--RAISE NOTICE 'Side Menu 3 created!';
		END IF;
		IF NOT EXISTS (select * from menus where nom_menu = 'Spe-4') THEN
			--insertion code
			INSERT INTO menus (nom_menu, libel_menu, nom_pere, pos_hierarch, ordre, is_menu, is_cliquable)
			VALUES ('Spe-4', maketraductionlangsyst('Saisie proposition'), 'Spe', 6, 3, false, false);
			--RAISE NOTICE 'Side Menu 4 created!';
		END IF;
		IF NOT EXISTS (select * from menus where nom_menu = 'Spe-2') THEN
			--insertion code
			INSERT INTO menus (nom_menu, libel_menu, nom_pere, pos_hierarch, ordre, is_menu, is_cliquable)
			VALUES ('Spe-2', maketraductionlangsyst('Affichage proposition'), 'Spe', 6, 2, false, false);
			--RAISE NOTICE 'Side Menu 2 created!';
		END IF;

		--Creation nouveaux ecrans Spe-1, Spe-2, Spe-3, Spe-4
		IF NOT EXISTS (select * from ecrans where nom_ecran = 'Spe-1') THEN
			--insertion code
			INSERT INTO ecrans(nom_ecran, fichier, nom_menu, fonction)
			VALUES ('Spe-1', 'modules/epargne/simulecheancier.php', 'Spe-1', 68);
			--RAISE NOTICE 'Ecran 1 created!';
		END IF;
		IF NOT EXISTS (select * from ecrans where nom_ecran = 'Spe-2') THEN
			--insertion code
			INSERT INTO ecrans(nom_ecran, fichier, nom_menu, fonction)
			VALUES ('Spe-2', 'modules/epargne/simulecheancier.php', 'Spe-2', 68);
			--RAISE NOTICE 'Ecran 2 created!';
		END IF;
		IF NOT EXISTS (select * from ecrans where nom_ecran = 'Spe-3') THEN
			--insertion code
			INSERT INTO ecrans(nom_ecran, fichier, nom_menu, fonction)
			VALUES ('Spe-3', 'modules/epargne/simulecheancier.php', 'Spe-3', 68);
			--RAISE NOTICE 'Ecran 3 created!';
		END IF;
		IF NOT EXISTS (select * from ecrans where nom_ecran = 'Spe-4') THEN
			--insertion code
			INSERT INTO ecrans(nom_ecran, fichier, nom_menu, fonction)
			VALUES ('Spe-4', 'modules/epargne/simulecheancier.php', 'Spe-4', 68);
			--RAISE NOTICE 'Ecran 4 created!';
		END IF;

		success := 1;

		RAISE NOTICE 'Ended';

	RETURN success;
	END;

$BODY$
  LANGUAGE plpgsql VOLATILE
  COST 100;
ALTER FUNCTION ticket_696()
  OWNER TO postgres;

  SELECT ticket_696();

  DROP FUNCTION IF EXISTS ticket_696();
--------------------------- FIN : Ticket #696 Demande d'amelioration Simulation pour les produits epargne DAT ---------------------------
--------------------------- DEBUT : Ticket #659 FINAIR - Amélioration la concentration de l'épargne ---------------------------
-- Function: epargne_view(date, integer, integer, integer, integer, date, date)

DROP FUNCTION IF EXISTS epargne_view(date, integer, integer, integer, integer);

CREATE OR REPLACE FUNCTION epargne_view(date, integer, integer, integer, integer, date, date)
  RETURNS SETOF epargne_view_type AS
$BODY$
DECLARE
	--id_cpte_u ALIAS FOR $1;
	date_epargne ALIAS FOR $1;
	--v_date_debut ALIAS FOR $1; --ticket 659
	--v_date_epargne date; --ticket 659
	idag ALIAS FOR $2;
	v_id_prod ALIAS FOR $3;
        v_limit ALIAS FOR $4;
	v_offset  ALIAS FOR $5;
	v_date_debut ALIAS FOR $6; --ticket 659
	v_date_fin ALIAS FOR $7; --ticket 659
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
	--v_date_epargne := DATE(v_date_debut); --ticket 659
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
			(case when v_date_debut is null and v_date_fin is null then (date(date_ouvert)<= date(date_epargne)) else (date(date_ouvert) between date(v_date_debut) and date(v_date_fin)) end) --ticket 659
			and  a.id_ag =idag and
			( etat_cpte <> 2 OR (etat_cpte = 2 and (case when v_date_fin is null then (date(date_clot) > date(date_epargne)) else (date(date_clot) > date(v_date_fin)) end))) --ticket 659 : case
			order by id_titulaire,id_cpte limit limites OFFSET  offsets;
	ELSE
		CREATE TEMP TABLE  temp_ad_cpt as
			SELECT  date_ouvert, solde,solde_clot,id_titulaire,id_cpte,id_prod,a.devise,etat_cpte,a.id_ag ,date_clot,num_complet_cpte,b.libel, b.classe_comptable
			from ad_cpt a left join adsys_produit_epargne b on ( a.id_ag=b.id_ag and a.id_prod =b.id)
			where (b.classe_comptable IN (1,2,3,5,6,8))  and
			(case when v_date_debut is null and v_date_fin is null then (date(date_ouvert)<= date(date_epargne)) else (date(date_ouvert) between date(v_date_debut) and date(v_date_fin)) end) --ticket 659
			and  a.id_ag =idag AND id_prod = v_id_prod AND
			( etat_cpte <> 2 OR (etat_cpte = 2 and (case when v_date_fin is null then (date(date_clot) > date(date_epargne)) else (date(date_clot) > date(v_date_fin)) end))) --ticket 659 : case
			order by id_titulaire,id_cpte limit limites OFFSET  offsets;
	END IF;

	-- RAISE NOTICE '%', solde_actuel ;

	IF  DATE(date_epargne) >=  DATE(now()) THEN
		OPEN cur_epargne FOR SELECT a.*,  0 as solde_after_date_ep FROM temp_ad_cpt a order  by id_titulaire,id_cpte;

	ELSE

               CREATE TEMP TABLE    solde_after_date_epargne as SELECT a.id_cpte,  sum( CASE  when sens ='c' THEN montant WHEN sens ='d' THEN -1*montant END ) as solde_after_date_ep
		from temp_ad_cpt a left join  (ad_mouvement b inner join ad_ecriture c on (b.id_ecriture=c.id_ecriture) ) on (a.id_cpte =b.cpte_interne_cli )
		where date(date_comptable) > date(date_epargne)
		--(case when v_date_fin is null then (date(date_comptable) > date(v_date_debut)) else (date(date_comptable) > date(v_date_fin)) end) --ticket 659 : case
		group by a.id_cpte;


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
ALTER FUNCTION epargne_view(date, integer, integer, integer, integer, date, date)
  OWNER TO adbanking;

--------------------------- FIN : Ticket #659 FINAIR - Amélioration la concentration de l'épargne ---------------------------

------------------------- Ticket #782: Export du journal -------------------------
DROP TYPE IF EXISTS data_export_journal CASCADE;


CREATE TYPE data_export_journal AS (
      id_ecriture int,
      id_his int,
      date_s date,
      compte text,
      sens text,
      montant numeric(30,6),
      id_client int,
      devise text,
      libel_ecriture int,
      type_operation int,
      info_ecriture text,
      id_ag int
      );


CREATE OR REPLACE FUNCTION getdatajournal(
    text,
    date,
    date,
    integer)
  RETURNS SETOF data_export_journal AS
$BODY$
 DECLARE

in_compte ALIAS FOR $1;		-- numero compte
in_date_debut ALIAS FOR $2;	-- date debut
in_date_fin ALIAS FOR $3;	-- date fin
in_id_agence ALIAS FOR $4;	-- id agence


v_sens_inv text;

counter integer :=0 ;

ligne record;
ligne1 record;

cur_list_compte refcursor;
cur_list_compte_inv refcursor;

ligne_data data_export_journal;

BEGIN

IF (in_compte IS NULL) THEN
OPEN cur_list_compte FOR SELECT a.id_ecriture, a.id_his, c.date, b.compte, b.sens, b.montant, c.id_client, b.devise, a.libel_ecriture,a.type_operation,a.info_ecriture
FROM ad_ecriture a ,ad_mouvement b, ad_his c
WHERE a.id_ag = b.id_ag AND b.id_ag = c.id_ag AND c.id_ag = in_id_agence AND a.id_ecriture=b.id_ecriture AND a.id_his= c.id_his
AND date(date_comptable) >= in_date_debut AND date(date_comptable) <= in_date_fin order by c.date,a.id_ecriture;
ELSE
OPEN cur_list_compte FOR SELECT a.id_ecriture, a.id_his, c.date, b.compte, b.sens, b.montant, c.id_client, b.devise, a.libel_ecriture,a.type_operation,a.info_ecriture
FROM ad_ecriture a ,ad_mouvement b, ad_his c
WHERE a.id_ag = b.id_ag AND b.id_ag = c.id_ag AND c.id_ag = in_id_agence AND a.id_ecriture=b.id_ecriture AND a.id_his= c.id_his
AND date(date_comptable) >= in_date_debut AND date(date_comptable) <= in_date_fin AND (compte=in_compte) order by c.date,a.id_ecriture;
END IF;

FETCH cur_list_compte INTO ligne;
WHILE FOUND LOOP

	SELECT INTO ligne_data ligne.id_ecriture, ligne.id_his, ligne.date, ligne.compte, ligne.sens, ligne.montant, ligne.id_client, ligne.devise, ligne.libel_ecriture,ligne.type_operation,ligne.info_ecriture, in_id_agence;
	RETURN NEXT ligne_data;

	IF (ligne.sens = 'c') THEN
	v_sens_inv = 'd';
	ELSE
	v_sens_inv = 'c';
	END IF;

	OPEN cur_list_compte_inv FOR SELECT a.id_ecriture, a.id_his, c.date, b.compte, b.sens, b.montant, c.id_client, b.devise, a.libel_ecriture,a.type_operation,a.info_ecriture
	FROM ad_ecriture a ,ad_mouvement b, ad_his c
	WHERE a.id_ag = b.id_ag AND b.id_ag = c.id_ag AND c.id_ag = in_id_agence AND a.id_ecriture=b.id_ecriture AND a.id_his= c.id_his
	AND b.id_ecriture = ligne.id_ecriture and b.sens = v_sens_inv;
	FETCH cur_list_compte_inv INTO ligne1;
	WHILE FOUND LOOP
	SELECT INTO ligne_data ligne1.id_ecriture, ligne1.id_his, ligne1.date, ligne1.compte, ligne1.sens, ligne1.montant, ligne1.id_client, ligne1.devise, ligne1.libel_ecriture,ligne1.type_operation,ligne1.info_ecriture, in_id_agence;
	RETURN NEXT ligne_data;

	FETCH cur_list_compte_inv INTO ligne1;
	END LOOP;
	CLOSE cur_list_compte_inv;




	RAISE NOTICE 'id_ecriture => %	--  id_his => %', ligne.id_ecriture,ligne.id_his;
	counter = counter + 1;

FETCH cur_list_compte INTO ligne;
END LOOP;
CLOSE cur_list_compte;
 RAISE NOTICE 'counter => %',counter;

END;
$BODY$
  LANGUAGE plpgsql VOLATILE
  COST 100
  ROWS 1000;
ALTER FUNCTION getdatajournal(text, date, date,integer)
  OWNER TO postgres;

------------------------- Ticket #782: Export du journal -------------------------