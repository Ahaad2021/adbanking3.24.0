----------------------------------------------------DEBUT REL-76/REL-85---------------------------------------------------------------------
CREATE OR REPLACE FUNCTION ModifyAdTransfertAttente() RETURNS INT AS
$$
DECLARE
output_result INTEGER = 1;

BEGIN

	RAISE NOTICE 'START';
IF EXISTS (SELECT * FROM information_schema.columns WHERE table_name='ad_transfert_attente' and column_name='id_mandat') THEN
 ALTER TABLE ad_transfert_attente RENAME COLUMN id_mandat to mandat;
 ALTER TABLE ad_transfert_attente ALTER COLUMN mandat TYPE text;
END IF;

IF NOT EXISTS (SELECT * FROM information_schema.columns WHERE table_name='ad_transfert_attente' and column_name='commission_duree_2retrait') THEN
 ALTER TABLE ad_transfert_attente ADD COLUMN commission_duree_2retrait numeric(30,6);
END IF;

RETURN output_result;

END;
$$
LANGUAGE plpgsql;

SELECT ModifyAdTransfertAttente();
DROP FUNCTION ModifyAdTransfertAttente();
----------------------------------------------------FIN REL-76/REL-85---------------------------------------------------------------------
----------------------------------------------------DEBUT REL-80/REL-81---------------------------------------------------------------------
CREATE OR REPLACE FUNCTION traiteRepriseIAR()
  RETURNS integer AS
$BODY$
DECLARE
  ligne_calcul_iar RECORD;
  count_nonreprise_ech INTEGER;
  output_result INTEGER:=0;
  
  calcul_iar CURSOR FOR SELECT id_doss, id_ech, montant, devise FROM ad_calc_int_recevoir_his WHERE etat_int = 1 ORDER BY id_doss, date_traitement, id;

  ecriture_iar refcursor;
  ligne_ecriture_iar RECORD;
  current_ecriture_iar INTEGER;

  temp_id_doss INTEGER;
  temp_id_ech INTEGER;
  temp_id_ecriture INTEGER;
  temp_id INTEGER;
  temp_doss_cre_etat INTEGER;
  cumule_mnt_ech_iar NUMERIC(30,6):=0;
  diffArrondMntIAR NUMERIC(30,6):=0;
  
  existReprise RECORD;

BEGIN

  --initialisation des variables temporaire
  temp_id_doss := -1;
  temp_id_ech := -1;
  temp_id_ecriture := -1;
  temp_id := -1;
  temp_doss_cre_etat = -1;	
  OPEN calcul_iar ;
  FETCH calcul_iar INTO ligne_calcul_iar;
  WHILE FOUND LOOP

	--Prendre le prochain id dossier dans le flow
	IF temp_id_doss != ligne_calcul_iar.id_doss THEN
		temp_id_doss := ligne_calcul_iar.id_doss;
		temp_id_ech := -1;
		temp_id_ecriture := -1;
		temp_id := -1;
		SELECT INTO temp_doss_cre_etat cre_etat FROM ad_dcr WHERE id_doss = ligne_calcul_iar.id_doss; --recuperer le cre_etat du dossier courant
		cumule_mnt_ech_iar := 0;
		--RAISE NOTICE '======================================================================================';
		--RAISE NOTICE 'Current ID Doss : % | Cre Etat : % ',ligne_calcul_iar.id_doss,temp_doss_cre_etat;
	END IF;

	--recuperer les ecritures concernant la reprise IAR du dossier courant 
	OPEN ecriture_iar FOR SELECT e.id_ecriture, m.montant, e.date_comptable, e.id_his FROM ad_ecriture e INNER JOIN ad_mouvement m ON m.id_ecriture = e.id_ecriture 
	WHERE e.info_ecriture IN (ligne_calcul_iar.id_doss::TEXT) AND e.type_operation = 375 
	AND m.compte in (SELECT cpte_cpta_int_recevoir FROM adsys_calc_int_recevoir WHERE id_ag = numagc()) 
	AND m.cpte_interne_cli IS NULL AND m.sens = 'c' ORDER BY e.id_ecriture ASC;
	FETCH ecriture_iar INTO ligne_ecriture_iar;
	WHILE FOUND LOOP

		--Prendre le prochain id ech du dossier courant
		IF temp_id_ech != ligne_calcul_iar.id_ech THEN			
			--RAISE NOTICE '----------------------------------------------';
			temp_id_ech := ligne_calcul_iar.id_ech;
			temp_id := -1;
			cumule_mnt_ech_iar := 0;
			--RAISE NOTICE '================> ID ECH = % MONTANT CALCUL IAR = % <================',ligne_calcul_iar.id_ech,ligne_calcul_iar.montant;
		END IF;
		
		--RAISE NOTICE '[ Id Ecriture Prec = % | Id Ecriture Curr = % ]',temp_id_ecriture,ligne_ecriture_iar.id_ecriture;
		IF temp_id_ecriture < ligne_ecriture_iar.id_ecriture THEN 
			cumule_mnt_ech_iar := cumule_mnt_ech_iar + ligne_ecriture_iar.montant;
		END IF;		

		--Recuperation du reprise existante dans la table ad_calc_int_recevoir_his et mouvement
		SELECT INTO existReprise id, id_ecriture_reprise FROM ad_calc_int_recevoir_his WHERE id > temp_id AND id_doss = ligne_calcul_iar.id_doss AND id_ech = ligne_calcul_iar.id_ech 
			AND date(date_traitement) = date(ligne_ecriture_iar.date_comptable) AND (montant = ligne_ecriture_iar.montant OR ROUND(montant) = ROUND(ligne_ecriture_iar.montant)) AND etat_int = 2 
			AND id_his_reprise = ligne_ecriture_iar.id_his AND id_ag = numagc() ORDER BY id ASC LIMIT 1;

		--Traitement Reprise partielle
		IF cumule_mnt_ech_iar > 0 AND cumule_mnt_ech_iar < ligne_calcul_iar.montant THEN
			temp_id_ecriture := ligne_ecriture_iar.id_ecriture;
			--RAISE NOTICE '[ ID = % ID Ecriture = % ]',existReprise.id,existReprise.id_ecriture_reprise;
			IF existReprise.id IS NOT NULL THEN
				temp_id := existReprise.id;
				IF existReprise.id_ecriture_reprise IS NULL OR existReprise.id_ecriture_reprise != ligne_ecriture_iar.id_ecriture THEN
					UPDATE ad_calc_int_recevoir_his SET id_ecriture_reprise = ligne_ecriture_iar.id_ecriture WHERE id = existReprise.id;
					--RAISE NOTICE '[[[ UPDATED ID ECRITURE!! ]]]';
				END IF;
			END IF;
			IF existReprise.id IS NULL THEN
				INSERT INTO ad_calc_int_recevoir_his (id_doss,id_ech,date_traitement,nb_jours,periodicite_jours,solde_int_ech,montant,etat_int,solde_cap,cre_etat
				,devise,id_his_reprise,id_ecriture_reprise,id_ag) VALUES (temp_id_doss,temp_id_ech,ligne_ecriture_iar.date_comptable,0,0,0,ligne_ecriture_iar.montant
				,2,0,temp_doss_cre_etat,ligne_calcul_iar.devise,ligne_ecriture_iar.id_his,ligne_ecriture_iar.id_ecriture,numagc());
				--RAISE NOTICE '[[[ INSERTED NEW TRANSACTION!! ]]]';
			END IF;
			--RAISE NOTICE '====> MONTANT REPRISE IAR PARTIELLE = % <====',cumule_mnt_ech_iar;
			--RAISE NOTICE '[ Montant Cumule % | Montant Mouvement % ]',cumule_mnt_ech_iar,ligne_ecriture_iar.montant;
		END IF;

		--Traitement Reprise Complete
		IF cumule_mnt_ech_iar = ligne_calcul_iar.montant OR cumule_mnt_ech_iar = ROUND(ligne_calcul_iar.montant)  THEN
			temp_id_ecriture := ligne_ecriture_iar.id_ecriture;			
			--RAISE NOTICE '[ ID = % ID Ecriture = % ]',existReprise.id,existReprise.id_ecriture_reprise;
			IF existReprise.id IS NOT NULL THEN
				temp_id := existReprise.id;
				IF existReprise.id_ecriture_reprise IS NULL OR existReprise.id_ecriture_reprise != ligne_ecriture_iar.id_ecriture THEN
					UPDATE ad_calc_int_recevoir_his SET id_ecriture_reprise = ligne_ecriture_iar.id_ecriture WHERE id = existReprise.id;
					--RAISE NOTICE '[[[ UPDATED ID ECRITURE!! ]]]';
				END IF;
				diffArrondMntIAR := cumule_mnt_ech_iar - ligne_calcul_iar.montant;
				/*IF diffArrondMntIAR > 0 AND diffArrondMntIAR < 1 THEN
					RAISE NOTICE '[[[ UPDATE MONTANT TRANSACTION!! ]]]';
				END IF;*/
			END IF;
			IF existReprise.id IS NULL THEN
				INSERT INTO ad_calc_int_recevoir_his (id_doss,id_ech,date_traitement,nb_jours,periodicite_jours,solde_int_ech,montant,etat_int,solde_cap,cre_etat
				,devise,id_his_reprise,id_ecriture_reprise,id_ag) VALUES (temp_id_doss,temp_id_ech,ligne_ecriture_iar.date_comptable,0,0,0,ligne_ecriture_iar.montant
				,2,0,temp_doss_cre_etat,ligne_calcul_iar.devise,ligne_ecriture_iar.id_his,ligne_ecriture_iar.id_ecriture,numagc());
				--RAISE NOTICE '[[[ INSERTED NEW TRANSACTION!! ]]]';
			END IF;
			--RAISE NOTICE '====> MONTANT REPRISE IAR FULL = % | ID Ecriture = % <====',cumule_mnt_ech_iar,temp_id_ecriture;
			--RAISE NOTICE '[ Montant Cumule % | Montant Mouvement % ]',cumule_mnt_ech_iar,ligne_ecriture_iar.montant;
			EXIT;
		END IF;		

	FETCH ecriture_iar INTO ligne_ecriture_iar;
	END LOOP;
	CLOSE ecriture_iar;	

  FETCH calcul_iar INTO ligne_calcul_iar;
  END LOOP;
 CLOSE calcul_iar;

 --Supprimer les lignes inutiles
 temp_id_doss := -1;
 --RAISE NOTICE '================================Deleting Unnecessary Echeances============================================';
 OPEN calcul_iar ;
  FETCH calcul_iar INTO ligne_calcul_iar;
  WHILE FOUND LOOP
	IF temp_id_doss != ligne_calcul_iar.id_doss THEN
		temp_id_doss := ligne_calcul_iar.id_doss;
		SELECT INTO count_nonreprise_ech COUNT(id_ech) FROM ad_calc_int_recevoir_his 
		WHERE id_doss = ligne_calcul_iar.id_doss AND etat_int = 2 
		AND id_ech NOT IN (SELECT DISTINCT id_ech FROM ad_calc_int_recevoir_his WHERE id_doss = ligne_calcul_iar.id_doss AND etat_int = 1);
		--RAISE NOTICE '============================================================================';
		--RAISE NOTICE 'Current ID Doss : %',ligne_calcul_iar.id_doss;
		DELETE FROM ad_calc_int_recevoir_his WHERE id_doss = ligne_calcul_iar.id_doss AND etat_int = 2 
		AND id_ech NOT IN (SELECT DISTINCT id_ech FROM ad_calc_int_recevoir_his WHERE id_doss = ligne_calcul_iar.id_doss AND etat_int = 1);
		--RAISE NOTICE 'Total Echeances Deleted : %',count_nonreprise_ech;
	END IF;
  FETCH calcul_iar INTO ligne_calcul_iar;
  END LOOP;
 CLOSE calcul_iar;
 
 --Correction devise dans la table ad_calc_int_recevoir_his
 UPDATE ad_calc_int_recevoir_his SET devise = (SELECT code_devise_reference FROM ad_agc) WHERE devise NOT IN (SELECT code_devise_reference FROM ad_agc);
 
 --Delete repeated similar reprise IAR
 DELETE FROM ad_calc_int_recevoir_his WHERE id IN (SELECT calc.id FROM ad_calc_int_recevoir_his calc INNER JOIN (SELECT MIN(id) AS ini_id, id_ecriture_reprise FROM ad_calc_int_recevoir_his WHERE etat_int = 2
GROUP BY id_ecriture_reprise
HAVING COUNT(id_ecriture_reprise) > 1) m ON m.id_ecriture_reprise = calc.id_ecriture_reprise
WHERE etat_int = 2 AND calc.id > m.ini_id);
 
RETURN output_result;
END;
$BODY$
  LANGUAGE plpgsql VOLATILE
  COST 100;
ALTER FUNCTION traiteRepriseIAR()
  OWNER TO adbanking;

  SELECT traiteRepriseIAR();

  DROP FUNCTION IF EXISTS traiteRepriseIAR();
----------------------------------------------------FIN REL-80/REL-81---------------------------------------------------------------------

----------------------------------------------------MB-54---------------------------------------------------------------------------------
CREATE OR REPLACE FUNCTION frais_transactionnel_SMS() RETURNS INT AS
$$
DECLARE
  output_result INTEGER = 1;

BEGIN

  RAISE NOTICE 'START';



	------------------------------------------------ Creation du nouveau frais SMS ---------------------------------------------------------
	IF NOT EXISTS (SELECT * FROM adsys_tarification WHERE type_de_frais = 'SMS_FRAIS' AND id_tarification = 14) THEN
	  --Creation du frais
	  INSERT INTO adsys_tarification (id_tarification, code_abonnement, type_de_frais, mode_frais, valeur, compte_comptable, date_debut_validite, date_fin_validite, statut, id_ag) VALUES (14, 'sms', 'SMS_FRAIS', '1', '0', null, null, null, 'f', numagc());

	  RAISE NOTICE 'Insertion type_frais SMS_FRAIS dans la table adsys_tarification effectuée';
    output_result := 2;
	END IF;

  --------------------------------------------- Opération frais transactionnel SMS ---------------------------------------------------------
  IF NOT EXISTS(SELECT * FROM ad_cpt_ope WHERE type_operation=188 AND categorie_ope = 1 AND id_ag = numagc()) THEN
    -- Opération du frais
    INSERT INTO ad_cpt_ope(type_operation, categorie_ope, id_ag, libel_ope)
    VALUES (188, 1, numagc(), maketraductionlangsyst('Frais forfaitaires du service SMS'));

    RAISE NOTICE 'Insertion type_operation 188 dans la table ad_cpt_ope effectuée';
    output_result := 2;
  END IF;

  IF NOT EXISTS(SELECT * FROM ad_cpt_ope_cptes WHERE type_operation=188 AND sens = 'd' AND categorie_cpte = 1 AND id_ag = numagc()) THEN
    INSERT INTO ad_cpt_ope_cptes(type_operation, num_cpte, sens, categorie_cpte, id_ag) VALUES (188, NULL, 'd', 1, numagc());

    RAISE NOTICE 'Insertion type_operation 188 sens ''d'' dans la table ad_cpt_ope_cptes effectuée';
    output_result := 2;
  END IF;

  IF NOT EXISTS(SELECT * FROM ad_cpt_ope_cptes WHERE type_operation=188 AND sens = 'c' AND categorie_cpte = 0 AND id_ag = numagc()) THEN
    INSERT INTO ad_cpt_ope_cptes(type_operation, num_cpte, sens, categorie_cpte, id_ag) VALUES (188, NULL, 'c', 0, numagc());

    RAISE NOTICE 'Insertion type_operation 188 sens ''c'' dans la table ad_cpt_ope_cptes effectuée';
    output_result := 2;
  END IF;
  ---------------------------------------------------------------------------------------------------------------------------------------------

  RAISE NOTICE 'END';
  RETURN output_result;

END;
$$
LANGUAGE plpgsql VOLATILE
  COST 100;
ALTER FUNCTION frais_transactionnel_SMS()
  OWNER TO adbanking;

SELECT frais_transactionnel_SMS();
DROP FUNCTION frais_transactionnel_SMS();
----------------------------------------------------FIN MB-54-----------------------------------------------------------------------------
------------------------------- DEBUT : Ticket 84 -----------------------------------------------------------------------------------------------------------
-- Function: getiarview(date, integer)

-- DROP FUNCTION getiarview(date, integer);

CREATE OR REPLACE FUNCTION getiarview(date, integer)
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
	/*and case when date(date_rapport) = date(now())
	then dcr.etat not in (6,9)
	else calculetatdossier_hist(id_agence,his.id_doss,date(date_rapport)) not in (6,9) end*/
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

	/*if date(date_rapport) < date(now()) then
		if(ligne.date_ech <= ligne.date_traitement) then
		v_montant_ech_prec := v_montant_ech;
		v_montant_ech := 0;
		end if;
	end if;*/



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

 ------------------------------- FIN : Ticket 84 -----------------------------------------------------------------------------------------------------------
