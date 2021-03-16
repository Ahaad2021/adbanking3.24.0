-------------------------- FIN : Ticket #717 - pp#241  - Anomalie Rapport des comptes dormants à une date antérieure(Ticket trac 717) - Fix ----------------------------------------------
--------------------------- DEBUT : Ticket #693 | PP#262 : Remboursement automatique des crédits: bloquer le montant | Amelioration demande sur le ticket 693 ---------------------------
DROP FUNCTION IF EXISTS check_cre_mnt_bloq_ech_date_batch(timestamp without time zone, integer, date) CASCADE;

CREATE OR REPLACE FUNCTION check_cre_mnt_bloq_ech_date_batch(timestamp without time zone, integer, date)
  RETURNS integer AS
$BODY$
DECLARE

	p_date_ech ALIAS FOR $1;
	p_nb_jr_bloq ALIAS FOR $2;
	p_date_now ALIAS FOR $3; -- date du batch

	curr_date DATE;
	curr_day INTEGER;
	start_date timestamp without time zone;
	end_date timestamp without time zone;

	output_result INTEGER = 0;
BEGIN
	--RAISE NOTICE 'DEBUT traitement';

	IF (p_nb_jr_bloq > 0) THEN

		curr_date	:= date(p_date_now);
		curr_day	:= DATE_PART('day', curr_date)::INTEGER;

		-- get start & end date bloq date
		start_date := date(p_date_ech - "interval"(p_nb_jr_bloq||' days'));
		end_date := date(p_date_ech);

		IF ((curr_date > end_date /*AND curr_day >= 25*/) OR (curr_date >= start_date AND curr_date <= end_date)) THEN
			output_result := 1;
		END IF;

	END IF;

	--RAISE NOTICE 'FIN traitement';

	RETURN output_result;

END;
	$BODY$
  LANGUAGE plpgsql VOLATILE
  COST 100;
ALTER FUNCTION check_cre_mnt_bloq_ech_date_batch(timestamp without time zone, integer, date)
  OWNER TO adbanking;


DROP FUNCTION IF EXISTS block_montant_ech_par_batch() CASCADE;
--DROP FUNCTION IF EXISTS block_montant_ech_par_batch(date) CASCADE;

CREATE OR REPLACE FUNCTION block_montant_ech_par_batch(date)
  RETURNS text AS
$BODY$
DECLARE

  date_now ALIAS FOR $1; -- date du batch
  new_solde_dispo numeric(30,6):=0;
  solde_dispo numeric(30,6):=0;

  v_mnt_bloq_cre numeric(30,6):=0;
  v_mnt_bloq_cre_ini numeric(30,6):=0;
  v_mnt_bloq_cre_ech numeric(30,6):=0;
  v_cre_mnt_bloq numeric(30,6):=0;
  v_mnt_bloq_cre_tot numeric(30,6):=0;

  total_montant_ech numeric(30,6):=0;
  total_montant_cre numeric(30,6):=0;

  temp_id_client INTEGER:=0;
  temp_id_doss INTEGER:=0;
  skip_id_doss INTEGER:=0;

  curr_credit refcursor;
  curr_ech refcursor;
  ligne_credit RECORD;
  ligne_ech RECORD;

  output_result_total INTEGER := 0;
  output_result INTEGER := 0;

  total_credit_cpte numeric(30,6):=0;
BEGIN
  --RAISE NOTICE 'DEBUT TRAITEMENT';

  -- Open the cursor for Dossier
  OPEN curr_credit FOR SELECT DISTINCT d.id_client, d.id_doss, d.cpt_liaison, d.cre_mnt_bloq, d.nb_jr_bloq_cre_avant_ech --, d.cre_mnt_bloq d.id_client
		       --, (c.solde - c.mnt_bloq - c.mnt_min_cpte + c.decouvert_max - c.mnt_bloq_cre) AS solde_ini
                       FROM ad_dcr d INNER JOIN ad_etr e ON e.id_doss = d.id_doss AND e.id_ag = d.id_ag
                         INNER JOIN ad_cpt c ON c.id_cpte = d.cpt_liaison AND e.id_ag = d.id_ag
                       WHERE d.id_ag = numagc() --AND d.id_client IN (2257)
                             AND d.prelev_auto = TRUE
                             AND d.is_ligne_credit = FALSE
                             --AND (c.solde - c.mnt_bloq - c.mnt_min_cpte + c.decouvert_max - c.mnt_bloq_cre) > 0
                             AND (d.etat = 5 OR d.etat = 7 OR d.etat = 9 OR d.etat = 13 OR d.etat = 14 OR d.etat = 15)
                             AND e.remb = 'f' AND 1 = check_cre_mnt_bloq_ech_date_batch(e.date_ech, d.nb_jr_bloq_cre_avant_ech::INTEGER, date_now)
                             --AND e.date_ech <= (date_now + "interval"(d.nb_jr_bloq_cre_avant_ech::INTEGER||' days'))
							 order by d.id_client asc, d.id_doss asc;

  FETCH curr_credit INTO ligne_credit;
  WHILE FOUND LOOP

		-- Get Initial Solde Disponible
		IF temp_id_client != ligne_credit.id_client THEN
			--RAISE NOTICE 'ID Client-> %',ligne_credit.id_client;
			--RAISE NOTICE '----------------------------------------------------------------------------------------';
			SELECT INTO solde_dispo (solde - mnt_bloq - mnt_min_cpte + coalesce(decouvert_max,0)) FROM ad_cpt WHERE id_cpte = ligne_credit.cpt_liaison; -- - mnt_bloq_cre
			--RAISE NOTICE 'ID Doss-> % | Solde dispo Init-> %',ligne_credit.id_doss,solde_dispo;
		END IF;

		IF solde_dispo > 0 THEN

			-- Open the cursor for echeance
			OPEN curr_ech FOR SELECT e.id_doss, e.id_ech, COALESCE(e.solde_gar,0) AS solde_gar, COALESCE(e.solde_pen,0) AS solde_pen,
								COALESCE(e.solde_int,0) AS solde_int, COALESCE(e.solde_cap,0) AS solde_cap
								FROM ad_etr e
								WHERE e.id_doss = ligne_credit.id_doss AND e.remb = 'f'
								AND 1 = check_cre_mnt_bloq_ech_date_batch(e.date_ech, ligne_credit.nb_jr_bloq_cre_avant_ech::INTEGER, date_now)
								--AND e.date_ech <= (date_now + "interval"(ligne_credit.nb_jr_bloq_cre_avant_ech::INTEGER||' days'))
								ORDER BY e.id_ech ASC;

			FETCH curr_ech INTO ligne_ech;
			WHILE FOUND LOOP

					--RAISE NOTICE '===========>> ID Ech->% ',ligne_ech.id_ech;

					IF (temp_id_doss != ligne_ech.id_doss) THEN

						-- Reset total montant ech, mnt bloq cre
						total_montant_cre := 0;
						total_montant_ech := 0;
						v_mnt_bloq_cre_ini := 0;
						v_mnt_bloq_cre := 0;
						v_mnt_bloq_cre_ech := 0;
						v_mnt_bloq_cre_tot := 0;

						SELECT INTO new_solde_dispo, v_mnt_bloq_cre_ini (solde - mnt_bloq - mnt_min_cpte + coalesce(decouvert_max,0)), mnt_bloq_cre FROM ad_cpt WHERE id_cpte = ligne_credit.cpt_liaison;
						v_mnt_bloq_cre_ech := v_mnt_bloq_cre_ini;
						IF temp_id_client = ligne_credit.id_client THEN
							new_solde_dispo := solde_dispo;
						END IF;
						--new_solde_dispo := new_solde_dispo - v_mnt_bloq_cre_ech;

					END IF;
					--RAISE NOTICE 'v_mnt_bloq_cre_ech -> %',v_mnt_bloq_cre_ech;

					--RAISE NOTICE 'New solde dispo -> %',new_solde_dispo;

					IF total_montant_ech >= 0 AND new_solde_dispo > 0 THEN

						IF (skip_id_doss != ligne_ech.id_doss) THEN

							IF (temp_id_doss != ligne_ech.id_doss) THEN

								-- Update cre mnt bloq
								total_montant_cre := total_montant_cre + total_montant_ech;

								-- Get new solde dispo
								--new_solde_dispo := new_solde_dispo - total_montant_ech + ligne_credit.cre_mnt_bloq;

								-- Reset total montant ech, mnt bloq cre
								total_montant_ech := 0;

								-- Set current id doss
								temp_id_doss := ligne_ech.id_doss;
							END IF;

							IF (total_montant_ech < new_solde_dispo AND (total_montant_ech + ligne_ech.solde_gar) <= new_solde_dispo) THEN
								-- Cumul garantie
								total_montant_ech := (total_montant_ech + (ligne_ech.solde_gar));
							ELSE
									-- Cumul garantie
									total_montant_ech := (total_montant_ech + (ligne_ech.solde_gar - ((total_montant_ech + ligne_ech.solde_gar) - new_solde_dispo)));

									skip_id_doss := temp_id_doss;
									--EXIT;  -- Exit loop
							END IF;

							IF (total_montant_ech < new_solde_dispo AND (total_montant_ech + ligne_ech.solde_pen) <= new_solde_dispo) THEN
								-- Cumul penalité
								total_montant_ech := (total_montant_ech + (ligne_ech.solde_pen));
							ELSE
									-- Cumul penalité
									total_montant_ech := (total_montant_ech + (ligne_ech.solde_pen - ((total_montant_ech + ligne_ech.solde_pen) - new_solde_dispo)));

									skip_id_doss := temp_id_doss;
									--EXIT;  -- Exit loop
							END IF;

							IF (total_montant_ech < new_solde_dispo AND (total_montant_ech + ligne_ech.solde_int) <= new_solde_dispo) THEN
								-- Cumul intérêt
								total_montant_ech := (total_montant_ech + (ligne_ech.solde_int));
							ELSE
									-- Cumul intérêt
									total_montant_ech := (total_montant_ech + (ligne_ech.solde_int - ((total_montant_ech + ligne_ech.solde_int) - new_solde_dispo)));

									skip_id_doss := temp_id_doss;
									--EXIT;  -- Exit loop
							END IF;

							IF (total_montant_ech < new_solde_dispo AND (total_montant_ech + ligne_ech.solde_cap) <= new_solde_dispo) THEN
								-- Cumul capital
								total_montant_ech := (total_montant_ech + (ligne_ech.solde_cap));
							ELSE
									-- Cumul capital
									total_montant_ech := (total_montant_ech + (ligne_ech.solde_cap - ((total_montant_ech + ligne_ech.solde_cap) - new_solde_dispo)));

									skip_id_doss := temp_id_doss;
									--EXIT;  -- Exit loop
							END IF;

						END IF;

						--RAISE NOTICE 'total_montant_ech1-> %',total_montant_ech;
						-- Update cpte mnt bloq
						IF total_montant_ech >= new_solde_dispo THEN
							total_montant_ech := new_solde_dispo;
							v_mnt_bloq_cre_ech := total_montant_ech;
							v_mnt_bloq_cre_tot := v_mnt_bloq_cre_tot + v_mnt_bloq_cre_ech;
							EXIT;
						END IF;
						--RAISE NOTICE 'total_montant_ech2-> %',total_montant_ech;
						v_mnt_bloq_cre_ech := total_montant_ech;
						v_mnt_bloq_cre_tot := v_mnt_bloq_cre_tot + v_mnt_bloq_cre_ech;
						new_solde_dispo := new_solde_dispo - v_mnt_bloq_cre_ech;
						total_montant_ech := 0;

					ELSE

						EXIT;

					END IF;

			FETCH curr_ech INTO ligne_ech;
			END LOOP;

			-- Close the cursor echeance
			CLOSE curr_ech;

			-- Update cpte mnt bloq
			v_mnt_bloq_cre := v_mnt_bloq_cre_tot;
			IF v_mnt_bloq_cre > 0 THEN
				-- Nouveau Client et aussi ayant plusieur dossiers avec meme compte
				IF temp_id_client != ligne_credit.id_client THEN
					--UPDATE ad_cpt SET mnt_bloq_cre = 0 WHERE id_cpte = ligne_credit.cpt_liaison AND id_ag = numagc();
					temp_id_client = ligne_credit.id_client;
					total_credit_cpte := 0;
				END IF;
				total_credit_cpte := total_credit_cpte + v_mnt_bloq_cre;
				--RAISE NOTICE 'Total Cumulé pour compte-> %',total_credit_cpte;
				UPDATE ad_cpt SET mnt_bloq_cre = total_credit_cpte WHERE id_cpte = ligne_credit.cpt_liaison AND id_ag = numagc();

				-- Update cre mnt bloq
				UPDATE ad_dcr SET cre_mnt_bloq = v_mnt_bloq_cre WHERE id_doss = ligne_credit.id_doss AND id_ag = numagc();
				solde_dispo := solde_dispo - v_mnt_bloq_cre;
				output_result := output_result + 1;
			END IF;

			--RAISE NOTICE 'total_mnt_bloq_cre pour dossier-> %',v_mnt_bloq_cre;

		END IF;

		output_result_total := output_result_total + 1;

		--RAISE NOTICE '----------------------------------------------------------------------------------';

    FETCH curr_credit INTO ligne_credit;
  END LOOP;

  -- Close the cursor credit
  CLOSE curr_credit;

  --RAISE NOTICE 'FIN TRAITEMENT';

  RETURN output_result||' sur '||output_result_total;

END;
$BODY$
  LANGUAGE plpgsql VOLATILE
  COST 100;
ALTER FUNCTION block_montant_ech_par_batch(date)
  OWNER TO adbanking;
--------------------------- FIN : Ticket #693 | PP#262 : Remboursement automatique des crédits: bloquer le montant | Amelioration demande sur le ticket 693 ---------------------------



------------------------- Ticket #701: Ajout d'un nouveau champs pour l'ecran de provisions -------------------------
CREATE OR REPLACE FUNCTION patch_ticket_701() RETURNS INT AS
$$
DECLARE

	tableliste_ident INTEGER = 0;

	output_result INTEGER = 1;

BEGIN

tableliste_ident := (select ident from tableliste where nomc like 'adsys_produit_credit' order by ident desc limit 1);

  -- Insertion dans d_tableliste champ adsys_produit_credit.is_produit_decouvert
  IF NOT EXISTS(SELECT * FROM d_tableliste WHERE nchmpc = 'is_produit_decouvert'  and tablen = tableliste_ident) THEN
      INSERT INTO d_tableliste VALUES ((select max(ident) from d_tableliste)+1, tableliste_ident, 'is_produit_decouvert', makeTraductionLangSyst('Le produit est-il un découvert?'), FALSE, null, 'bol', FALSE, FALSE, FALSE);

  END IF;


		RAISE NOTICE 'FIN traitement';
	RETURN output_result;

END;
$$
LANGUAGE plpgsql;

SELECT patch_ticket_701();
DROP FUNCTION patch_ticket_701();




---------------------- Debut Ticket #792: Montants arrondis ----------------------
-- Function: arrete_comptes(date)

-- DROP FUNCTION arrete_comptes(date);

CREATE OR REPLACE FUNCTION arrete_comptes(date)
  RETURNS SETOF int_arret_cptes AS
$BODY$

 	DECLARE
	  date_batch ALIAS FOR $1;     				-- la date d'éxécution du batch
	  prec INTEGER;          			 				-- précision de la devise du compte
	  base_taux_agence INTEGER;    				-- la base du taux de l'agence telle que parametrée(1 ou 2)
	  base_taux_mois INTEGER ;
 	  resultat int_arret_cptes;    				-- ensemble d'informations renvoyées par cette procédure stockée
	  ligne RECORD;        								-- ensemble d'informations sur le compte encours de traitement
 	  jr_annee INTEGER;      							-- nombre de jours dans l'année en fonction du base_taux_agence(360 ou 365)
 	  interet NUMERIC(30,6);       				-- le montant de la rémunération(intérêts)
 	  cpte_destination_int INTEGER;     	-- numéro du compte de destination des intérêts
 	  cpte_cpta_assoc_prod_ep RECORD;     -- informations(id du produit,numéro du cpte comptable) sur le compte comptable associé au produit d'épargne
 	  cpte_concerne TEXT;      						-- numéro du cpte comptable associé au produit, il dépend du type de compte d'épargne(simple ou nantie)
 	  rep INTEGER;            						--valeur de retour de la fonction payeinteret
	  devise_assoc_prod TEXT;      				-- devise du compte associé au produit d'épargne
	  nouvo_solde float4;      						-- nouveau solde à  afficher
	  num_cpte_cpta_prod_ep TEXT;    			-- numero du compte comptable du produit d'epargne
	  compte_IAP TEXT;					-- numero du compte IAP : adsys_calc_int_paye.cpte_cpta_int_paye
		-- Récupère des infos sur les comptes épargne à  rémunérer
	  --Cpt_Calcul_Int NO SCROLL CURSOR FOR
	 --	SELECT  a.id_cpte,a.mode_calcul_int_cpte,a.solde_calcul_interets,a.tx_interet_cpte,a.interet_a_capitaliser,a.devise,a.cpt_vers_int,a.dat_date_fin,a.id_titulaire,
	 --	        getPeriodeCapitalisation(date(date_batch), date(a.date_ouvert), date(a.date_calcul_interets)) as perio_cap, b.classe_comptable, b.id as id_prod_epargne,
	 --			b.cpte_cpta_prod_ep, b.cpte_cpta_prod_ep_int
	 --	FROM ad_cpt a, adsys_produit_epargne b
	 --	WHERE a.id_prod = b.id AND a.id_ag = NumAgc() AND a.id_ag = b.id_ag AND (
	 --		(etat_cpte=1 AND terme_cpte > 0 AND tx_interet_cpte > 0 AND date(dat_date_fin) = date(date_batch))
	 --		OR (etat_cpte=1 AND tx_interet_cpte > 0 AND mode_paiement_cpte = 1 AND freq_calcul_int_cpte = 1 AND isFinMois(date(date_batch)) AND isRemunerable(date(date_batch), date_ouvert, marge_tolerance))
	 --		OR (etat_cpte=1 AND tx_interet_cpte > 0 AND mode_paiement_cpte = 1 AND freq_calcul_int_cpte = 2 AND isFinTrimestre(date(date_batch)) AND isRemunerable(date(date_batch), date_ouvert, marge_tolerance))
	 --		OR (etat_cpte=1 AND tx_interet_cpte > 0 AND mode_paiement_cpte = 1 AND freq_calcul_int_cpte = 3 AND isFinSemestre(date(date_batch)) AND isRemunerable(date(date_batch), date_ouvert, marge_tolerance))
	 --		OR (etat_cpte=1 AND tx_interet_cpte > 0 AND mode_paiement_cpte = 1 AND freq_calcul_int_cpte = 4 AND isFinAnnee(date(date_batch)) AND isRemunerable(date(date_batch), date_ouvert, marge_tolerance))
	 --		OR (etat_cpte=1 AND tx_interet_cpte > 0 AND mode_paiement_cpte = 2 AND isMultipleFrequence(date(date_batch),date_ouvert,freq_calcul_int_cpte))
	 --		OR (etat_cpte=1 AND tx_interet_cpte > 0 AND mode_paiement_cpte = 3 AND b.mode_calcul_int = 12 AND (isFinAnnee(date(date_batch)) OR date(b.ep_source_date_fin)=date(date_batch)))
	 --		)
	 --		AND ((solde > seuil_rem_dav and classe_comptable = 1) or classe_comptable != 1) order by a.id_cpte ;

 	BEGIN

	 	--récupération de la devise de référence : besoin pour écriture comptable généralement en multidevise------------------------
	 	--SELECT INTO code_dev_ref code_devise_reference FROM ad_agc;
	 	SELECT INTO base_taux_agence base_taux_epargne from ad_agc where id_ag = NumAgc();
	 	IF base_taux_agence  = 1 THEN
	 	    jr_annee := 360;
	 	  ELSE
	 	    jr_annee := 365;
	 	END IF;

	 	DROP TABLE IF EXISTS arrete_comptes;

		CREATE TEMP TABLE arrete_comptes AS SELECT a.id_cpte,a.mode_calcul_int_cpte,a.solde_calcul_interets,a.tx_interet_cpte,a.interet_a_capitaliser,a.devise,a.cpt_vers_int,a.dat_date_fin,a.id_titulaire,
	 	        getPeriodeCapitalisation(date(date_batch), date(a.date_ouvert), date(a.date_calcul_interets)) as perio_cap, b.classe_comptable, b.id as id_prod_epargne,
	 			b.cpte_cpta_prod_ep, b.cpte_cpta_prod_ep_int
	 	FROM ad_cpt a, adsys_produit_epargne b
	 	WHERE a.id_prod = b.id AND a.id_ag = NumAgc() AND a.id_ag = b.id_ag AND (
	 		(etat_cpte=1 AND terme_cpte > 0 AND tx_interet_cpte > 0 AND date(dat_date_fin) = date(date_batch))
	 		OR (etat_cpte=1 AND tx_interet_cpte > 0 AND mode_paiement_cpte = 1 AND freq_calcul_int_cpte = 1 AND isFinMois(date(date_batch)) AND isRemunerable(date(date_batch), date_ouvert, marge_tolerance))
	 		OR (etat_cpte=1 AND tx_interet_cpte > 0 AND mode_paiement_cpte = 1 AND freq_calcul_int_cpte = 2 AND isFinTrimestre(date(date_batch)) AND isRemunerable(date(date_batch), date_ouvert, marge_tolerance))
	 		OR (etat_cpte=1 AND tx_interet_cpte > 0 AND mode_paiement_cpte = 1 AND freq_calcul_int_cpte = 3 AND isFinSemestre(date(date_batch)) AND isRemunerable(date(date_batch), date_ouvert, marge_tolerance))
	 		OR (etat_cpte=1 AND tx_interet_cpte > 0 AND mode_paiement_cpte = 1 AND freq_calcul_int_cpte = 4 AND isFinAnnee(date(date_batch)) AND isRemunerable(date(date_batch), date_ouvert, marge_tolerance))
	 		OR (etat_cpte=1 AND tx_interet_cpte > 0 AND mode_paiement_cpte = 2 AND isMultipleFrequence(date(date_batch),date_ouvert,freq_calcul_int_cpte))
	 		OR (etat_cpte=1 AND tx_interet_cpte > 0 AND mode_paiement_cpte = 3 AND a.mode_calcul_int_cpte = 12 AND (isFinAnnee(date(date_batch)) OR date(b.ep_source_date_fin)=date(date_batch)))
	 		)
	 		AND ((solde > seuil_rem_dav and classe_comptable = 1) or classe_comptable != 1) order by a.id_cpte ;

	 	---OPEN Cpt_Calcul_Int;
	 	--FETCH Cpt_Calcul_Int INTO ligne;
	 		----------------------INSERTION DANS HISTORIQUE--------------------------------------
	 	--IF FOUND THEN
	 	    INSERT INTO ad_his (type_fonction, infos, date, id_ag)
	 	    	VALUES  (212, makeTraductionLangSyst('intérêts pour le mois écoulé'), now(),NumAgc());
	 	--END IF;
	 	--WHILE FOUND LOOP
	  FOR ligne IN SELECT  * FROM arrete_comptes
	   	LOOP
			----------------------CALCUL DES INTERETS--------------------------------------------
			IF (ligne.mode_calcul_int_cpte <> 12) THEN
				interet := ligne.solde_calcul_interets * (ligne.tx_interet_cpte * ligne.perio_cap)/jr_annee;
			ELSE --mode_calcul_int_cpte=12 epargne à la source
				interet := ligne.interet_a_capitaliser;
			END IF;

      -- On garde les arrondies pour palier a des pertes de precisions lors des calculs d'interets a payer
			-- Verifie si IAP est parametré
			SELECT INTO compte_IAP cpte_cpta_int_paye FROM adsys_calc_int_paye WHERE cpte_cpta_int_paye IS NOT NULL AND id_ag = numagc();
			IF (compte_IAP IS NULL OR compte_IAP = '') THEN -- Si non
				-- arrondi par rapport a la precision devise
				SELECT INTO prec precision FROM devise WHERE  code_devise = ligne.devise;
				interet := ROUND(interet, prec);
			END IF;

      -- RAISE NOTICE 'id_cpte=%  solde_calcul_interets=%  tx_interet_cpte=%  perio_cap=%  interet=% ',ligne.id_cpte, ligne.solde_calcul_interets, ligne.tx_interet_cpte, ligne.perio_cap, interet;

			-----------------------PAIEMENT DES INTERETS ----------------------------
			IF (interet > 0) THEN
			-----------------------Compte de destination des intérêts ----------------------------

				IF (ligne.cpt_vers_int IS NOT NULL) THEN
					cpte_destination_int := ligne.cpt_vers_int; -- cas où le compte de destination des intérêts est différent du compte encours. Ainsi le produit qui nous interesse est celui du compte de destination
				ELSE
					cpte_destination_int := ligne.id_cpte; -- cas où le compte de destination des intérêts est le compte lui-même
				END IF;

				rep:=payeInteret(ligne.id_cpte,cpte_destination_int,interet,date_batch);
				--récupération du solde
				SELECT INTO nouvo_solde solde FROM ad_cpt WHERE id_cpte = ligne.id_cpte;

			ELSE
			  interet := 0;
			END IF;	---FIN IF interet > 0 ----------------------------------------------------------

			----Initialisation du solde de calcul des intérêts et mise à jour des dates de calcul solde et de capitalisation : ----------

			IF (ligne.mode_calcul_int_cpte >= 2) AND (ligne.mode_calcul_int_cpte <= 7) THEN
			---mode de calcul des intérêts est 'Sur solde...le plus bas'. On débute la période avec le solde courant ----------

				IF cpte_destination_int = ligne.id_cpte THEN --Si le compte est le compte de destination des intérêts, ajouter les intérêts dans le solde

					UPDATE ad_cpt SET solde_calcul_interets = solde + interet, date_solde_calcul_interets = date_batch ,date_calcul_interets = date_batch WHERE id_cpte = ligne.id_cpte;
				ELSE
					UPDATE ad_cpt SET solde_calcul_interets = solde, date_solde_calcul_interets = date_batch ,date_calcul_interets = date_batch WHERE id_cpte = ligne.id_cpte;
				END IF;
			ELSIF (ligne.mode_calcul_int_cpte >= 8) AND (ligne.mode_calcul_int_cpte <= 11) THEN

				--mode de calcul des intérêts est 'Sur solde moyen ...'. On initialise à 0 le solde de calcul des intérêts
					UPDATE ad_cpt SET solde_calcul_interets = 0, date_solde_calcul_interets = date_batch, date_calcul_interets = date_batch WHERE id_cpte = ligne.id_cpte;
					-- mode de calcul des intérêts pour épargne à la source
			ELSIF ((ligne.mode_calcul_int_cpte = 12) AND (isFinAnnee(date_batch) OR date(ligne.dat_date_fin)=date(date_batch))) THEN
			  			UPDATE ad_cpt SET interet_a_capitaliser = 0, interet_annuel = 0, solde_calcul_interets = solde, date_solde_calcul_interets=date(date_batch),date_calcul_interets=date(date_batch) WHERE id_ag=numAgc() AND id_cpte=ligne.id_cpte;
			END IF;
			-- ----------le tableau renvoyé par la fonction
			SELECT INTO resultat ligne.id_cpte, ligne.id_titulaire,ligne.solde_calcul_interets, ligne.perio_cap, ligne.classe_comptable, interet,  cpte_destination_int;
			RETURN NEXT resultat;

			RAISE NOTICE 'le compte traité est %',ligne.id_cpte;

			--FETCH Cpt_Calcul_Int INTO lign;

		END LOOP;

		--CLOSE Cpt_Calcul_Int;
 	RETURN ;

 	END;
 	$BODY$
  LANGUAGE plpgsql VOLATILE
  COST 100
  ROWS 1000;
ALTER FUNCTION arrete_comptes(date)
  OWNER TO postgres;
----------------------- Fin Ticket #792: Montants arrondis -----------------------

--------------------- Debut Ticket #683 : Operation en Deplacee ---------------------
CREATE OR REPLACE FUNCTION patch_ticket_683() RETURNS INT AS
$$
DECLARE

	tableliste_ident INTEGER = 0;
	tableliste_ident_od INTEGER = 0;

	output_result INTEGER = 1;

BEGIN

tableliste_ident := (select ident from tableliste where nomc like 'adsys_multi_agence' order by ident desc limit 1);

  -- Insertion dans d_tableliste champ adsys_multi_agence.cpte_comm_od
  IF NOT EXISTS(SELECT * FROM d_tableliste WHERE nchmpc = 'cpte_comm_od'  and tablen = tableliste_ident) THEN
      INSERT INTO d_tableliste VALUES ((select max(ident) from d_tableliste)+1, tableliste_ident, 'cpte_comm_od', makeTraductionLangSyst('Compte de produit pour commissions sur opération en deplacé'), FALSE, 1400, 'txt', FALSE, FALSE, FALSE);

  ALTER TABLE adsys_multi_agence
   ADD COLUMN cpte_comm_od text;
  END IF;
   
   -- Creations des champs dans la table d_tableliste + creation dans la table adsys_produit_epargnes pour le pourcentage et les bornes Max/min des montant OD en depot/retrait
-- ==> champs pour Comm od depot pourcentage
  tableliste_ident_od:= (select ident from tableliste where nomc like 'adsys_produit_epargne' order by ident desc limit 1);
  
    IF NOT EXISTS(SELECT * FROM d_tableliste WHERE nchmpc = 'comm_depot_od' and tablen = tableliste_ident_od) THEN
      INSERT INTO d_tableliste VALUES ((select max(ident) from d_tableliste)+1, tableliste_ident_od, 'comm_depot_od', makeTraductionLangSyst('Commissions sur "dépôt en déplacé" en pourcentage'), FALSE, NULL, 'prc', FALSE, FALSE, FALSE);
  ALTER TABLE adsys_produit_epargne
   ADD COLUMN comm_depot_od numeric(30,6)  DEFAULT 0;
  END IF;
   
     IF NOT EXISTS(SELECT * FROM d_tableliste WHERE nchmpc = 'comm_depot_od_mnt_min' and tablen =tableliste_ident_od) THEN
      INSERT INTO d_tableliste VALUES ((select max(ident) from d_tableliste)+1, tableliste_ident_od, 'comm_depot_od_mnt_min', makeTraductionLangSyst('Montant minimum commissions sur "dépôt en déplacé"'), FALSE, NULL, 'mnt', FALSE, FALSE, FALSE);
  
ALTER TABLE adsys_produit_epargne
   ADD COLUMN comm_depot_od_mnt_min numeric(30,6)  DEFAULT 0;
END IF;
   
-- ==> champs pour Comm od depot bornes max
  IF NOT EXISTS(SELECT * FROM d_tableliste WHERE nchmpc = 'comm_depot_od_mnt_max' and tablen = tableliste_ident_od) THEN
      INSERT INTO d_tableliste VALUES ((select max(ident) from d_tableliste)+1, tableliste_ident_od, 'comm_depot_od_mnt_max', makeTraductionLangSyst('Montant maximum commissions sur "dépôt en déplacé"'), FALSE, NULL, 'mnt', FALSE, FALSE, FALSE);
  
ALTER TABLE adsys_produit_epargne
   ADD COLUMN comm_depot_od_mnt_max numeric(30,6)  DEFAULT 0;
END IF;
   
   
   -- ==> champs pour Comm od retrait pourcentage
  IF NOT EXISTS(SELECT * FROM d_tableliste WHERE nchmpc = 'comm_retrait_od' and tablen = tableliste_ident_od) THEN
      INSERT INTO d_tableliste VALUES ((select max(ident) from d_tableliste)+1, tableliste_ident_od, 'comm_retrait_od', makeTraductionLangSyst('Commissions sur "retrait en déplacé" en pourcentage'), FALSE, NULL, 'prc', FALSE, FALSE, FALSE);
  
ALTER TABLE adsys_produit_epargne
   ADD COLUMN comm_retrait_od numeric(30,6)  DEFAULT 0;
END IF;
   
   -- ==> champs pour Comm od retrait bornes min
  IF NOT EXISTS(SELECT * FROM d_tableliste WHERE nchmpc = 'comm_retrait_od_mnt_min' and tablen = tableliste_ident_od) THEN
      INSERT INTO d_tableliste VALUES ((select max(ident) from d_tableliste)+1, tableliste_ident_od, 'comm_retrait_od_mnt_min', makeTraductionLangSyst('Montant minimum commissions sur "retrait en déplacé"'), FALSE, NULL, 'mnt', FALSE, FALSE, FALSE);
  
ALTER TABLE adsys_produit_epargne
   ADD COLUMN comm_retrait_od_mnt_min numeric(30,6) DEFAULT 0;
END IF;

-- ==> champs pour Comm od retrait bornes max
  IF NOT EXISTS(SELECT * FROM d_tableliste WHERE nchmpc = 'comm_retrait_od_mnt_max' and tablen = tableliste_ident_od) THEN
      INSERT INTO d_tableliste VALUES ((select max(ident) from d_tableliste)+1, tableliste_ident_od, 'comm_retrait_od_mnt_max', makeTraductionLangSyst('Montant maximum commissions sur "retrait en déplacé"'), FALSE, NULL, 'mnt', FALSE, FALSE, FALSE);
  
ALTER TABLE adsys_produit_epargne
   ADD COLUMN comm_retrait_od_mnt_max numeric(30,6)  DEFAULT 0;
END IF;


	IF NOT EXISTS(SELECT * FROM information_schema.columns WHERE table_name = 'adsys_audit_multi_agence' AND column_name = 'commission_ope_deplace') THEN
	ALTER TABLE adsys_audit_multi_agence ADD COLUMN commission_ope_deplace numeric(30,6) DEFAULT 0;
	output_result := 2;
	END IF;
   
   -- ==> Creation de l'operation comptable 156
	IF NOT EXISTS(SELECT * FROM ad_cpt_ope WHERE type_operation = 156 AND categorie_ope = 1 AND id_ag = numagc()) THEN
		INSERT INTO ad_cpt_ope(type_operation, categorie_ope, id_ag, libel_ope) VALUES (156, 1, numagc(), maketraductionlangsyst('Perception des commissions sur depôt en déplacé'));
		RAISE NOTICE 'Insertion type_operation 156 dans la table ad_cpt_ope effectuée';
		output_result := 2;
	END IF;
-- ==> setup du compte debiteur comme compte de Guichet 	
	IF NOT EXISTS(SELECT * FROM ad_cpt_ope_cptes WHERE type_operation = 156 AND sens = 'd' AND categorie_cpte = 4 AND id_ag = numagc()) THEN
		INSERT INTO ad_cpt_ope_cptes(type_operation, num_cpte, sens, categorie_cpte, id_ag) VALUES (156, null, 'd', 4, numagc());

		RAISE NOTICE 'Insertion type_operation 156 sens ''d'' dans la table ad_cpt_ope_cptes effectuée';
		output_result := 2;
	END IF;
	
	-- ==> Creation de l'operation comptable 157
	IF NOT EXISTS(SELECT * FROM ad_cpt_ope WHERE type_operation = 157 AND categorie_ope = 1 AND id_ag = numagc()) THEN
		INSERT INTO ad_cpt_ope(type_operation, categorie_ope, id_ag, libel_ope) VALUES (157, 1, numagc(), maketraductionlangsyst('Perception des commissions sur retrait en déplacé'));
		RAISE NOTICE 'Insertion type_operation 157 dans la table ad_cpt_ope effectuée';
		output_result := 2;
	END IF;  

-- ==> setup du compte debiteur comme compte de Guichet 	
	IF NOT EXISTS(SELECT * FROM ad_cpt_ope_cptes WHERE type_operation = 157 AND sens = 'd' AND categorie_cpte = 1 AND id_ag = numagc()) THEN
		INSERT INTO ad_cpt_ope_cptes(type_operation, num_cpte, sens, categorie_cpte, id_ag) VALUES (157, null, 'd', 1, numagc());

		RAISE NOTICE 'Insertion type_operation 157 sens ''d'' dans la table ad_cpt_ope_cptes effectuée';
		output_result := 2;
	END IF;
	
	
	
	
		RAISE NOTICE 'FIN traitement';
	RETURN output_result;

END;
$$
LANGUAGE plpgsql;

SELECT patch_ticket_683();
DROP FUNCTION patch_ticket_683()

-------------------- fin Ticket #683 : Operation en deplace











