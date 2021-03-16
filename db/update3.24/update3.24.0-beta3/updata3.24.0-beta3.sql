
DROP TYPE IF EXISTS view_rapport_mobile_lending  cascade;
CREATE TYPE view_rapport_mobile_lending AS (
id_client integer,
id_doss integer,
etat_doss_mob integer,
imf text,
agence text ,
id_agent integer,
localisation text,
tranche_localisation integer,
sexe text,
tranche_sexe integer,
sal_moy numeric(30,6),
tranche_sal_moy integer,
lg_histo integer,
somm_tot_emprunter numeric(30,2),
tranche_somm_tot_emprunter integer,
nbre_credit_carac integer,
tranche_nbre_credit integer,
age integer,
tx_irregularite float,
tranche_tx_irregularite integer,
nbre_credit integer,
mnt_dem numeric,
date_deboursement date,
nbre_ech integer,
retard_ech_1 integer,
retard_ech_2 integer,
retard_ech_3 integer,
mnt_rest_du numeric,
penalite numeric,
score_retard_credit float,
score_client float,
commentaire text,
tranche_age integer,
tranche_lg_histo integer
 );
-- Function: get_rapport_mobile_lending(integer[], integer[])

-- DROP FUNCTION get_rapport_mobile_lending(integer[], integer[]);

CREATE OR REPLACE FUNCTION get_rapport_mobile_lending(
    integer[],
    integer[])
  RETURNS SETOF view_rapport_mobile_lending AS
$BODY$
  DECLARE

  etat_mobile_lending ALIAS FOR $1;
  cre_etat_param ALIAS FOR $2;

  v_id_client integer;
  v_id_doss integer;
  v_etat_doss_mob integer;
  v_nom_agence text;
  v_nom_imf text;
  v_id_agent integer;
  v_nbre_credit integer;
  v_mnt_dem numeric(30,2);
  v_date_debours date;
  v_nbre_ech integer;
  v_retard_eche_1 integer;
  v_retard_eche_2 integer;
  v_retard_eche_3 integer;
  v_mnt_restant_du numeric(30,2);
  v_penalite_a_payer numeric(30,2);
  v_score_retard_credit float;
  v_score_client float;
  v_commentaire text;

v_localisation text;
v_tranche_localisation integer;
v_sexe text;
v_tranche_sexe integer;
v_sal_moy numeric(30,6);
v_tranche_sal_moy integer;
v_lg_histo integer;
v_somm_tot_emprunter numeric(30,2);
v_tranche_somm_tot_emprunter integer;
v_tranche_nbre_credit integer;
v_age integer;
v_tranche_age integer;
v_tx_irregularite float;
v_tranche_tx_irregularite integer;
v_tranche_lg_histo integer;

  ligne RECORD;

  mise_a_jour_donnees view_rapport_mobile_lending;
  output INTEGER :=0;

  C1 refcursor;

  BEGIN

  IF array_length(cre_etat_param, 1) > 0 THEN
    OPEN C1 FOR SELECT * from ml_demande_credit m INNER JOIN ad_dcr d on d.id_client = m.id_client and d.id_doss = m.id_doss WHERE m.statut_demande = ANY(etat_mobile_lending) and d.cre_etat = ANY(cre_etat_param);
  ELSE
    OPEN C1 FOR SELECT * from ml_demande_credit WHERE statut_demande = ANY(etat_mobile_lending);
  END IF;




  --OPEN C1 ;
  FETCH C1 INTO ligne;
  WHILE FOUND LOOP

    -- ID client
  v_id_client := ligne.id_client;

 -- id_ doss
 v_id_doss := ligne.id_doss;

 -- etat dossier mobile lending
 v_etat_doss_mob := ligne.statut_demande;

  -- libel Agence et IMF
  SELECT INTO v_nom_agence,v_nom_imf libel_institution,libel_ag FROM ad_agc;

  -- id_agent
  SELECT INTO v_id_agent id_agent_gest FROM ad_dcr where id_doss = ligne.id_doss;

 -- nombre credit
 SELECT INTO v_nbre_credit count(*) FROM ml_demande_credit where id_client = ligne.id_client and statut_demande not IN (2,4,5);

  --Montant demande
  v_mnt_dem = ligne.mnt_dem;

  -- date remboursement
 SELECT INTO v_date_debours cre_date_debloc FROM ad_dcr where id_doss = ligne.id_doss;

 -- nombre echeance
 SELECT INTO v_nbre_ech count(*) FROM ad_etr WHERE id_doss = ligne.id_doss;

 -- retard echeance 1
 SELECT INTO v_retard_eche_1 date(now()) - date(date_ech) FROM ad_etr where id_ech = 1 and id_doss = ligne.id_doss;
 IF v_retard_eche_1 IS NULL THEN
 v_retard_eche_1 := 0;
 ELSEIF v_retard_eche_1 < 0 THEN
  v_retard_eche_1 := 0;
 END IF;

 -- retard echeance 2
 SELECT INTO v_retard_eche_2  date(now()) - date(date_ech) FROM ad_etr where id_ech = 2 and id_doss = ligne.id_doss;
 IF v_retard_eche_2 IS NULL THEN
 v_retard_eche_2 := 0;
 ELSEIF v_retard_eche_2 < 0 THEN
  v_retard_eche_2 := 0;
 END IF;

 -- retard echeance 2
 SELECT INTO v_retard_eche_3 date(now()) - date(date_ech) FROM ad_etr where id_ech = 3 and id_doss = ligne.id_doss;
 IF v_retard_eche_3 IS NULL THEN
 v_retard_eche_3 := 0;
 ELSEIF v_retard_eche_3 < 0 THEN
  v_retard_eche_3 := 0;
 END IF;

 --montant restant du
 SELECT INTO v_mnt_restant_du sum(solde_cap+solde_int+solde_gar+solde_pen) FROM ad_etr where id_doss = ligne.id_doss;

 --penalite a payer
 SELECT INTO v_penalite_a_payer sum(solde_pen) FROM ad_etr where id_doss = ligne.id_doss;

 -- score_retard_credit
 IF v_nbre_ech > 0 THEN
   v_score_retard_credit = 100 - 1/v_nbre_ech * (v_retard_eche_1+v_retard_eche_2+v_retard_eche_3);
 ELSE
   v_score_retard_credit = 0;
 END IF;

 -- score client =
  SELECT INTO v_score_client score_final FROM ml_donnees_client_abonnees where client = ligne.id_client;

 -- commentaire agent
 SELECT INTO v_commentaire details_motif FROM ad_dcr where id_doss = ligne.id_doss;

 -- localisation
 SELECT INTO v_tranche_localisation ml_localisation FROM ad_agc;
 IF v_tranche_localisation = 1 THEN
  v_localisation = 'Urbaine';
 ELSE
  v_localisation = 'Rurale';
 END IF;

 -- selection des tranches dans la table ml_donnees_client_abonnees
 SELECT INTO v_tranche_sexe,v_sal_moy,v_tranche_sal_moy,v_lg_histo,v_somm_tot_emprunter,v_tranche_somm_tot_emprunter,v_tranche_nbre_credit,v_age,v_tx_irregularite,v_tranche_tx_irregularite, v_tranche_age, v_tranche_lg_histo
  tranche_sexe,salaire_moyen,tranche_sal_moyen,lg_histo,mnt_tot_emprunter,tranche_tot_emprunter,tranche_nbre_credit,age,tx_irregularite,tranche_irregularite, tranche_age, tranche_lg_histo  FROM ml_donnees_client_abonnees WHERE client = v_id_client;

  --recuperation sexe
  IF  v_tranche_sexe = 1 THEN
  v_sexe := 'Homme';
  ELSE
  v_sexe := 'Femme';
  END IF;

 SELECT INTO mise_a_jour_donnees v_id_client,v_id_doss,v_etat_doss_mob,v_nom_agence,v_nom_imf,v_id_agent,v_localisation,v_tranche_localisation,v_sexe,v_tranche_sexe,v_sal_moy,v_tranche_sal_moy,v_lg_histo,v_somm_tot_emprunter,v_tranche_somm_tot_emprunter,v_nbre_credit,v_tranche_nbre_credit,v_age,v_tx_irregularite,v_tranche_tx_irregularite,v_nbre_credit,v_mnt_dem,v_date_debours,v_nbre_ech,v_retard_eche_1,v_retard_eche_2,v_retard_eche_3,
v_mnt_restant_du,v_penalite_a_payer,v_score_retard_credit,v_score_client,v_commentaire, v_tranche_age, v_tranche_lg_histo;

RETURN NEXT mise_a_jour_donnees;

 FETCH C1 INTO ligne;
  END LOOP;
  CLOSE C1;


  RETURN;
  END;
  $BODY$
  LANGUAGE plpgsql VOLATILE
  COST 100
  ROWS 1000;
ALTER FUNCTION get_rapport_mobile_lending(integer[], integer[])
  OWNER TO postgres;

/*---------------------------------------------------------------------------------------------------------------------------------------*/

/****************************************Partie FUSION************************************************************************************/
CREATE OR REPLACE FUNCTION update_fusion()
  RETURNS INT AS
$$
DECLARE
  output_result INTEGER = 1;
  tableliste_ident_niveau INTEGER = 0;
  tableliste_ident_tablesliste INTEGER = 0;
  tableliste_ident_client INTEGER = 0;
  tableliste_ident_log INTEGER = 0;
  d_tableliste_str INTEGER = 0;
  id_str_trad INTEGER = 0;
  tablen_id INTEGER = 0;

BEGIN



id_str_trad := maketraductionlangsyst('Niveau Agence');

    IF NOT EXISTS(SELECT * FROM tableliste WHERE nomc = 'type_niveau_agence') THEN
      INSERT INTO tableliste VALUES (
      (select max(ident) from tableliste)+1,
      'type_niveau_agence',
      id_str_trad,
      false);
      RAISE NOTICE 'Données table type de niveau rajoutés dans table tableliste';
    END IF;

    tablen_id := (SELECT ident FROM tableliste WHERE nomc = 'type_niveau_agence');

    IF NOT EXISTS(SELECT * FROM d_tableliste WHERE nchmpc = 'id' and tablen = tablen_id) THEN
    INSERT INTO d_tableliste VALUES ((select max(ident)
    from d_tableliste)+1, tablen_id, 'id', makeTraductionLangSyst('Id'), TRUE, NULL, 'int', NULL, TRUE, FALSE);
    END IF;

	IF NOT EXISTS(SELECT * FROM information_schema.tables WHERE table_name = 'adsys_niveau_agence') THEN

	   CREATE TABLE adsys_niveau_agence
	(
	  id serial NOT NULL,
	  libelle_niveau text,
	  type_niveau integer,
	  parent integer,
	  id_ag integer,
	  CONSTRAINT adsys_niveau_agence_pkey PRIMARY KEY (id, id_ag)
	);
  ALTER SEQUENCE adsys_niveau_agence_id_seq RESTART WITH 2;
	END IF;



		  -- Insertion dans tableliste
	IF NOT EXISTS(SELECT * FROM tableliste WHERE nomc = 'adsys_niveau_agence') THEN
		INSERT INTO tableliste VALUES ((select max(ident) from tableliste)+1, 'adsys_niveau_agence', makeTraductionLangSyst('"Paramétrage des niveaux agences"'), true);
		RAISE NOTICE 'Données table adsys_niveau_agence rajoutés dans table tableliste';
	END IF;

	tableliste_ident_niveau := (select ident from tableliste where nomc like 'adsys_niveau_agence' order by ident desc limit 1);

	IF NOT EXISTS(SELECT * FROM d_tableliste WHERE nchmpc = 'libelle_niveau' and tablen = tableliste_ident_niveau) THEN
	  INSERT INTO d_tableliste VALUES ((select max(ident) from d_tableliste)+1, tableliste_ident_niveau, 'libelle_niveau', makeTraductionLangSyst('Libelle niveau'), true, NULL, 'txt', true, null, false);
	END IF;

	IF NOT EXISTS(SELECT * FROM d_tableliste WHERE nchmpc = 'type_niveau' and tablen = tableliste_ident_niveau) THEN
	  tableliste_ident_tablesliste := (select ident from tableliste where nomc like 'type_niveau_agence' order by ident desc limit 1);
	  INSERT INTO d_tableliste VALUES ((select max(ident) from d_tableliste)+1, tableliste_ident_niveau, 'type_niveau', makeTraductionLangSyst('Type niveau'), false, (SELECT ident from d_tableliste where tablen = tableliste_ident_tablesliste and nchmpc = 'id'), 'int', false, false, false);
	END IF;

	IF NOT EXISTS(SELECT * FROM d_tableliste WHERE nchmpc = 'parent' and tablen = tableliste_ident_niveau) THEN
	  INSERT INTO d_tableliste VALUES ((select max(ident) from d_tableliste)+1, tableliste_ident_niveau, 'parent', makeTraductionLangSyst('Parent'), true, NULL, 'int', false, false, false);
	END IF;

	IF NOT EXISTS(SELECT * FROM d_tableliste WHERE nchmpc = 'id' and tablen = tableliste_ident_niveau) THEN
	  INSERT INTO d_tableliste VALUES ((select max(ident) from d_tableliste)+1, tableliste_ident_niveau, 'id', makeTraductionLangSyst('Id localisation'), true, NULL, 'int', null, true, false);
	END IF;

	IF NOT EXISTS (select * from ecrans where nom_ecran = 'Lob-1') THEN
		--insertion code
		INSERT INTO ecrans(nom_ecran, fichier, nom_menu, fonction)
		VALUES ('Lob-1', 'modules/parametrage/tables.php', 'Pta', 292);
		RAISE NOTICE 'Ecran 1 created!';
	END IF;

	IF NOT EXISTS (select * from ecrans where nom_ecran = 'Lob-2') THEN
		--insertion code
		INSERT INTO ecrans(nom_ecran, fichier, nom_menu, fonction)
		VALUES ('Lob-2', 'modules/parametrage/tables.php', 'Pta', 292);
		RAISE NOTICE 'Ecran 1 created!';
	END IF;

	IF NOT EXISTS (select * from ecrans where nom_ecran = 'Lob-3') THEN
		--insertion code
		INSERT INTO ecrans(nom_ecran, fichier, nom_menu, fonction)
		VALUES ('Lob-3', 'modules/parametrage/tables.php', 'Pta', 292);
		RAISE NOTICE 'Ecran 1 created!';
	END IF;


	tableliste_ident_client := (select ident from tableliste where nomc like 'ad_cli' order by ident desc limit 1);
tableliste_ident_tablesliste := (select ident from tableliste where nomc like 'adsys_niveau_agence' order by ident desc limit 1);
	IF NOT EXISTS(SELECT * FROM d_tableliste WHERE nchmpc = 'n_agence' and tablen = tableliste_ident_client) THEN
	  ALTER TABLE ad_cli ADD n_agence INTEGER;
	  d_tableliste_str := makeTraductionLangSyst('Niveau agence');
	  INSERT INTO d_tableliste VALUES ((select max(ident) from d_tableliste)+1, tableliste_ident_client, 'n_agence', d_tableliste_str, true, (SELECT ident from d_tableliste where tablen = tableliste_ident_tablesliste and nchmpc = 'id'), 'int', true, false, false);
	END IF;

	IF NOT EXISTS(SELECT * FROM d_tableliste WHERE nchmpc = 'n_guichet' and tablen = tableliste_ident_client) THEN
	  ALTER TABLE ad_cli ADD n_guichet INTEGER;
	  d_tableliste_str := makeTraductionLangSyst('Niveau guichet');
	  INSERT INTO d_tableliste VALUES ((select max(ident) from d_tableliste)+1, tableliste_ident_client, 'n_guichet', d_tableliste_str, true, (SELECT ident from d_tableliste where tablen = tableliste_ident_tablesliste and nchmpc = 'id'), 'int', true, false, false);
	END IF;

	tableliste_ident_log := (select ident from tableliste where nomc like 'ad_log' order by ident desc limit 1);

	IF NOT EXISTS(SELECT * FROM d_tableliste WHERE nchmpc = 'n_agence' and tablen = tableliste_ident_log) THEN
	  ALTER TABLE ad_log ADD n_agence INTEGER;
	  d_tableliste_str := makeTraductionLangSyst('Niveau agence');
	  INSERT INTO d_tableliste VALUES ((select max(ident) from d_tableliste)+1, tableliste_ident_log, 'n_agence', d_tableliste_str, true, (SELECT ident from d_tableliste where tablen = tableliste_ident_tablesliste and nchmpc = 'id'), 'int', true, false, false);
	END IF;

	IF NOT EXISTS(SELECT * FROM d_tableliste WHERE nchmpc = 'n_guichet' and tablen = tableliste_ident_log) THEN
	  ALTER TABLE ad_log ADD n_guichet INTEGER;
	  d_tableliste_str := makeTraductionLangSyst('Niveau guichet');
	  INSERT INTO d_tableliste VALUES ((select max(ident) from d_tableliste)+1, tableliste_ident_log, 'n_guichet', d_tableliste_str, true, (SELECT ident from d_tableliste where tablen = tableliste_ident_tablesliste and nchmpc = 'id'), 'int', true, false, false);
	END IF;



	--insertion ecran intermediaire pour filtrer les niveaux : ajout ecran Ich-5
	IF NOT EXISTS (select * from ecrans where nom_ecran = 'Ich-5') THEN
		--insertion code
		INSERT INTO ecrans(nom_ecran, fichier, nom_menu, fonction)
		VALUES ('Ich-5', 'modules/guichet/chequier_imprimer.php', 'Ich', 191);
		RAISE NOTICE 'Ecran 1 created!';
	END IF;


	RETURN output_result;

END;
$$
LANGUAGE plpgsql;

SELECT update_fusion();
DROP FUNCTION update_fusion();

--AT-270 : Fusion

CREATE OR REPLACE FUNCTION script_at_270() RETURNS INT AS
$$
DECLARE
output_result INTEGER = 1;
v_nom_coopec text;

BEGIN

  IF NOT EXISTS (SELECT * FROM adsys_niveau_agence WHERE type_niveau = 1) THEN
    SELECT INTO v_nom_coopec libel_ag FROM ad_agc;
    INSERT INTO adsys_niveau_agence VALUES (1,v_nom_coopec,1,0,numagc());
  END IF;

	RETURN output_result;

END;
$$
LANGUAGE plpgsql;

SELECT script_at_270();
DROP FUNCTION script_at_270();

