CREATE OR REPLACE FUNCTION setDormant(date, integer)
  RETURNS integer AS
$BODY$	
	declare
		curr_client refcursor;
		ligne_client RECORD;
		row_titulaire INTEGER := 0;
		row_mandataire INTEGER := 0;
		ligne_param_epargne RECORD;
		date_batch ALIAS FOR $1; 
		idAgc ALIAS FOR $2; 
		counter INTEGER := 0;

	begin
		SELECT INTO ligne_param_epargne active_compte_sur_transaction, cpte_dormant_nbre_jour
		FROM adsys_param_epargne
		WHERE id_ag = idAgc ;

		IF ligne_param_epargne.cpte_dormant_nbre_jour IS NOT NULL AND ligne_param_epargne.active_compte_sur_transaction = TRUE THEN
			-- Retreive recently activated accounts
			DROP TABLE IF EXISTS temp_activated_accounts;
			CREATE TEMP TABLE temp_activated_accounts as
			SELECT e.id_his, id_client, a.cpte_interne_cli
			FROM ad_mouvement a , ad_ecriture e, ad_his h
			WHERE  e.id_ecriture = a.id_ecriture AND e.id_his = h.id_his
			AND a.cpte_interne_cli IN 
			(

					SELECT  id_cpte
					FROM ad_mouvement a , ad_cpt b, adsys_produit_epargne c, ad_ecriture e, ad_his h
					WHERE a.id_ag=b.id_ag AND a.id_ag=c.id_ag AND b.id_ag=c.id_ag AND c.id_ag = idAgc AND e.id_ecriture = a.id_ecriture AND e.id_his = h.id_his
					AND cpte_interne_cli = id_cpte AND b.id_prod = c.id  AND classe_comptable=1 AND c.retrait_unique =FALSE AND c.depot_unique = FALSE
					AND h.type_fonction = 91
					AND b.etat_cpte NOT IN (4)
					GROUP BY id_cpte
					HAVING DATE(date_batch) - max(DATE(h.date)) < ligne_param_epargne.cpte_dormant_nbre_jour

			)
			AND e.type_operation IN (140, 160, 420, 511, 512, 532, 507, 621, 624)	-- Only operation related to 'depot / retrait'		
			GROUP BY e.id_his, id_client, a.cpte_interne_cli
			HAVING DATE(date_batch) - max(date_valeur) < ligne_param_epargne.cpte_dormant_nbre_jour;

			OPEN curr_client FOR SELECT DISTINCT id_client FROM temp_activated_accounts;

			FETCH curr_client INTO ligne_client;
			WHILE FOUND LOOP

				-- client
				SELECT count(*) INTO row_titulaire FROM ad_his_ext ext INNER JOIN ad_his h ON ext.id = h.id_his_ext where h.id_his IN (
					SELECT id_his FROM temp_activated_accounts WHERE id_client = ligne_client.id_client
				) AND ext.id_pers_ext IS NULL;

				-- mandataire
				SELECT count(*) INTO row_mandataire FROM ad_mandat m  INNER JOIN ad_his_ext ext ON m.id_pers_ext = ext.id_pers_ext 
				INNER JOIN ad_his h ON ext.id = h.id_his_ext  where h.id_his IN (
					SELECT id_his FROM temp_activated_accounts WHERE id_client = ligne_client.id_client
				)  AND ext.id_pers_ext IS NOT NULL;  -- COUNT = 1 , IS mandataire, else personne non cliente

				-- count into mandat 
				IF row_titulaire > 0 OR row_mandataire > 0 THEN
					RAISE NOTICE 'Account remains active';

				ELSE 
					RAISE NOTICE 'Resetting account to dormant, no transaction made by user';
					UPDATE ad_cpt a SET  etat_cpte = 4,date_blocage= DATE(now()), raison_blocage = 'Compte dormant'
					WHERE id_cpte IN ( SELECT id_cpte FROM temp_activated_accounts where id_client = ligne_client.id_client );
					counter := counter + 1;
				END IF;

				FETCH curr_client INTO ligne_client;
			END LOOP;
			CLOSE curr_client;

		END IF;

		RETURN counter;
	end;
$BODY$
  LANGUAGE plpgsql VOLATILE
  COST 100;
ALTER FUNCTION setDormant(date, integer)
  OWNER TO postgres;
