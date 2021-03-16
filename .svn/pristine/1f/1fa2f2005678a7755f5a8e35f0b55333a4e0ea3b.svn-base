 ------------------------------- DEBUT : Ticket AT-39 -----------------------------------------------------------------------------------------------------------
CREATE OR REPLACE FUNCTION ticket_AT_39() RETURNS INT AS
$$
DECLARE
output_result INTEGER = 1;
  id_str_trad integer = 0;
  tableliste_ident integer = 0;

BEGIN
IF NOT EXISTS (SELECT * FROM information_schema.columns WHERE table_name='ad_agc' and column_name='autorisation_approvisionnement_delestage') THEN
 ALTER TABLE ad_agc ADD COLUMN autorisation_approvisionnement_delestage boolean DEFAULT FALSE;
END IF;


tableliste_ident := (select ident from tableliste where nomc like 'ad_agc' order by ident desc limit 1);
  IF NOT EXISTS(SELECT * FROM d_tableliste WHERE nchmpc = 'autorisation_approvisionnement_delestage' and tablen = tableliste_ident) THEN
  INSERT INTO d_tableliste VALUES ((select max(ident) from d_tableliste)+1, tableliste_ident, 'autorisation_approvisionnement_delestage', makeTraductionLangSyst('Autorisation sur approvisionnement/délestage'), false, NULL, 'bol', false, false, false);
END IF;


  -- Insertion ecran de validation pour la demande
IF NOT EXISTS (select * from ecrans where nom_ecran = 'Agu-3') THEN
	--insertion code
	INSERT INTO ecrans(nom_ecran, fichier, nom_menu, fonction)
	VALUES ('Agu-3', 'modules/guichet/approvisionnement_delestage.php', 'Agu-2', 155);
END IF;


  -- Insertion ecran de validation pour la demande
IF NOT EXISTS (select * from ecrans where nom_ecran = 'Dgu-3') THEN
	--insertion code
	INSERT INTO ecrans(nom_ecran, fichier, nom_menu, fonction)
	VALUES ('Dgu-3', 'modules/guichet/approvisionnement_delestage.php', 'Dgu-2', 156);
END IF;

-- Creation table ad_approvisionnement_delestage_attente
 IF NOT EXISTS(SELECT * FROM information_schema.tables WHERE table_name = 'ad_approvisionnement_delestage_attente') THEN

   CREATE TABLE ad_approvisionnement_delestage_attente
(
  id serial NOT NULL,
  id_guichet integer,
  montant numeric(30,6),
  devise text,
  etat_appro_delestage integer,
  type_action integer,
  date_creation date,
  date_modif date,
  id_his integer,
  id_ag integer,
  CONSTRAINT ad_approvisionnement_delestage_attente_pkey PRIMARY KEY (id, id_ag)
);

END IF;


--Creation nouveau fonction Autorisation de transfert : 198
		IF NOT EXISTS (select * from adsys_fonction where code_fonction = 804) THEN
			 --insertion code
			 INSERT INTO adsys_fonction(code_fonction, libelle, id_ag)
			 VALUES (804, 'Autorisation d approvisionnement/délestage', numagc());
			 RAISE NOTICE 'Fonction created!';
		END IF;

		--Creation nouveau main menu + side menus
IF NOT EXISTS (select * from menus where nom_menu = 'Aad') THEN
	--insertion code
	INSERT INTO menus (nom_menu, libel_menu, nom_pere, pos_hierarch, ordre, is_menu,fonction, is_cliquable)
	VALUES ('Aad', maketraductionlangsyst('Autorisation approvisionnement / délestage'), 'Gen-6', 3, 6, true, 804, true);
	RAISE NOTICE 'Main Menu created!';
END IF;
IF NOT EXISTS (select * from menus where nom_menu = 'Aad-1') THEN
	--insertion code
	INSERT INTO menus (nom_menu, libel_menu, nom_pere, pos_hierarch, ordre, is_menu, is_cliquable)
	VALUES ('Aad-1', maketraductionlangsyst('Liste demande approvisionnement / délestage'), 'Aad', 4, 1, false, false);
	RAISE NOTICE 'Side Menu 1 created!';
END IF;
IF NOT EXISTS (select * from menus where nom_menu = 'Aad-2') THEN
	--insertion code
	INSERT INTO menus (nom_menu, libel_menu, nom_pere, pos_hierarch, ordre, is_menu, is_cliquable)
	VALUES ('Aad-2', maketraductionlangsyst('Confirmation autorisation approvisionnement / délestage'), 'Aad', 4, 2, false, false);
	RAISE NOTICE 'Side Menu 2 created!';
END IF;

--Creation nouveaux ecrans Atd-1, Atd-2,
IF NOT EXISTS (select * from ecrans where nom_ecran = 'Aad-1') THEN
	--insertion code
	INSERT INTO ecrans(nom_ecran, fichier, nom_menu, fonction)
	VALUES ('Aad-1', 'modules/guichet/demande_autorisation_approvisionnement_delestage.php', 'Aad-1', 804);
	RAISE NOTICE 'Ecran 1 created!';
END IF;
IF NOT EXISTS (select * from ecrans where nom_ecran = 'Aad-2') THEN
	--insertion code
	INSERT INTO ecrans(nom_ecran, fichier, nom_menu, fonction)
	VALUES ('Aad-2', 'modules/guichet/demande_autorisation_approvisionnement_delestage.php', 'Aad-2', 804);
	RAISE NOTICE 'Ecran 2 created!';
END IF;


-- ECRANS Effectuer les retraits autoriser
IF NOT EXISTS (SELECT nom_ecran FROM ecrans WHERE nom_ecran='Ead-1') THEN
	INSERT INTO ecrans(nom_ecran, fichier, nom_menu, fonction) VALUES ('Ead-1', 'modules/guichet/effectuer_approvisionnement_delestage.php', 'Gen-6', 177);
END IF;


	RETURN output_result;
END;
$$
LANGUAGE plpgsql;

SELECT ticket_AT_39();
DROP FUNCTION ticket_AT_39();
------------------------------- FIN : Ticket AT-39 -----------------------------------------------------------------------------------------------------------
------------------------------- DEBUT : Ticket REL-40 -----------------------------------------------------------------------------------------------------------
-- Function: getinventairecredits(date, date, integer)

-- DROP FUNCTION getinventairecredits(date, date, integer);

CREATE OR REPLACE FUNCTION getinventairecredits(date, date, integer)
  RETURNS SETOF inventairecredits_view AS
$BODY$
DECLARE
  DECLARE
  p_date_debut ALIAS FOR $1;
  p_date_fin ALIAS FOR $2;
  p_id_etat ALIAS FOR $3;

  v_date_fin date;

  ligne_inventaire inventairecredits_view;
  ligne RECORD;


 opening_list CURSOR for select R.type_rapport, R.id_prod, R.obj_dem, R.id_doss, date(R.cre_date_debloc) as cre_date_debloc, R.cre_id_cpte, R.cre_mnt_octr, R.etat_dossier, R.date_etat_doss,
				max(R.id_etat_credit) as id_etat_credit, R.cre_date_etat, R.id_client, R.nom_cli,
				case when R.cre_date_debloc >= date(p_date_debut) then R.cre_mnt_deb else 0.000000 end as cre_mnt_deb_per, sum(R.capital_debut) as capital_debut,
				abs(sum(R.int_rembourse_fin) - sum(R.int_rembourse_debut)) as int_rembourse_period, abs(sum(R.pen_rembourse_fin) - sum(R.pen_rembourse_debut)) as pen_rembourse_period,
				sum(case when R.type_rapport = '4-RADIE' and R.cre_date_etat between date(p_date_debut) and date(p_date_fin) then R.perte_capital else R.capital_fin end) as capital_fin,
				R.perte_capital, R.is_ligne_credit
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
				case when dcr.etat = 6 and v.id_etat_credit = p_id_etat and v.cre_date_etat < date(p_date_debut) then 0.000000 else dcr.perte_capital end as perte_capital,
				dcr.is_ligne_credit
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
				case when dcr.etat = 6 and v.id_etat_credit = p_id_etat and v.cre_date_etat < date(p_date_debut) then 0.000000 else dcr.perte_capital end as perte_capital,
				dcr.is_ligne_credit
				from getportfeuilleview(date(p_date_fin),numagc()) v
				inner join ad_dcr dcr on v.id_ag = dcr.id_ag and dcr.id_doss = v.id_doss
			) R
			group by R.id_doss, R.type_rapport, R.id_prod, R.obj_dem, R.cre_date_debloc, R.cre_id_cpte, R.cre_mnt_octr, R.cre_mnt_deb, R.etat_dossier, R.date_etat_doss,
			R.cre_date_etat, R.id_client, R.nom_cli, R.perte_capital, R.is_ligne_credit
			order by R.type_rapport asc;


tot_mnt_remb_cap numeric(30,6):=0;
--tot_mnt_remb_cap1 numeric(30,6):=0; -- test
tot_mnt_remb_int numeric(30,6):=0;
tot_mnt_remb_pen numeric(30,6):=0;
mnt_remb_cap numeric(30,6):=0;
montant_tot  numeric(30,6):=0;
mnt_tot numeric(30,6):=0;
cre_mnt_deb_per numeric(30,6):=0;
capital_fin numeric(30,6):=0;
--capital_fin1 numeric(30,6):=0; --test
perte_cap numeric(30,6):=0;
--perte_capital1 numeric(30,6):=0; --test
mnt_cap_debut numeric(30,6):=0; --test
date_nettoyage date;
duree_nettoyage integer;
date_echeance_lcr date;
capital_debut_lcr numeric(30,6):=0;
capital_debut numeric(30,6):=0;
date_interval date;
date_nettoyage_cur refcursor;
date_nettoyage_rec RECORD;
--cpte_cpta_int text;
--cpte_cpta_pen text;

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

	--montant rembourse au cours de la periode pour les dossiers normaux
	tot_mnt_remb_cap := (ligne.capital_debut + cre_mnt_deb_per) - (capital_fin + ligne.perte_capital); --pour le total
	mnt_remb_cap := (ligne.capital_debut + cre_mnt_deb_per) - (ligne.capital_fin + perte_cap); --pour la partie detail

	--capital debut periode
	capital_debut := ligne.capital_debut;

	--Gestion pour les lignes de credit
	IF ligne.is_ligne_credit = 't' THEN
		--calcul date de nettoyage
		SELECT INTO duree_nettoyage duree_nettoyage_lcr FROM ad_dcr WHERE id_doss = ligne.id_doss;
		SELECT INTO date_echeance_lcr date_ech FROM ad_etr WHERE id_doss = ligne.id_doss;
		open date_nettoyage_cur FOR EXECUTE 'SELECT (DATE('''||date_echeance_lcr||''') - interval '''||duree_nettoyage||' month'') AS d_nettoyage';
		LOOP
			FETCH date_nettoyage_cur INTO date_nettoyage_rec; EXIT WHEN NOT FOUND;
			date_nettoyage := date_nettoyage_rec.d_nettoyage;
		END LOOP;
		--capital debut periode
		SELECT INTO capital_debut_lcr SUM(CASE WHEN type_evnt = 2 THEN valeur WHEN type_evnt = 3 THEN -1*valeur ELSE 0 END) FROM ad_lcr_his
		WHERE id_doss = ligne.id_doss AND type_evnt IN (2,3) AND DATE(date_evnt) < DATE(p_date_debut);
		capital_debut := capital_debut_lcr;
		IF capital_debut IS NULL THEN
			capital_debut := 0;
		END IF;
		--Si date nettoyage est superieure a la date debut du rapport
		IF DATE(date_nettoyage) > DATE(p_date_debut) THEN
			--Capital deboursé au cours de la période
			--Différence entre la somme des montants déboursés  et la somme de capital remboursé entre la date debut
			--et la date de nettoyage si la date de netoyage est superieure à la date début. Sinon, le montant sera null.
			SELECT INTO cre_mnt_deb_per SUM(CASE WHEN type_evnt = 2 THEN valeur WHEN type_evnt = 3 THEN -1*valeur ELSE 0 END) FROM ad_lcr_his
			WHERE id_doss = ligne.id_doss AND type_evnt IN (2,3)
			AND DATE(date_evnt) BETWEEN DATE(p_date_debut) AND DATE(date_nettoyage);
			--Capital remboursé au cours de la periode
			--Sommation de capital remboursé depuis la date de nettoyage jusqu’à la date du rapport. Si la date de nettoyage est inferieur à la date debut,
			--il faut faire la summation de capital remboursé entre la date debut et la date du rapport.
			SELECT INTO tot_mnt_remb_cap SUM(valeur) FROM ad_lcr_his
			WHERE id_doss = ligne.id_doss AND type_evnt = 3
			AND DATE(date_evnt) BETWEEN DATE(date_nettoyage) AND DATE(p_date_fin);
		ELSE
			--Capital deboursé au cours de la période
			cre_mnt_deb_per := 0;
			--Capital remboursé au cours de la periode
			SELECT INTO tot_mnt_remb_cap SUM(valeur) FROM ad_lcr_his
			WHERE id_doss = ligne.id_doss AND type_evnt = 3
			AND DATE(date_evnt) BETWEEN DATE(p_date_debut) AND DATE(p_date_fin);
		END IF;
		IF tot_mnt_remb_cap IS NULL THEN
			tot_mnt_remb_cap := 0;
		END IF;
		IF cre_mnt_deb_per IS NULL THEN
			cre_mnt_deb_per := 0;
		END IF;
		mnt_remb_cap := tot_mnt_remb_cap;
		capital_fin := capital_debut + cre_mnt_deb_per - mnt_remb_cap;
	END IF;

	--interets ordinaires et interets de retards
	IF ligne.type_rapport = '4-RADIE' OR (ligne.type_rapport = '2-SOLDE' AND ligne.perte_capital > 0) THEN
		SELECT INTO v_date_fin max(e.date_comptable) FROM ad_mouvement m INNER JOIN ad_ecriture e ON m.id_ecriture = e.id_ecriture WHERE e.type_operation = 280
		AND m.cpte_interne_cli = ligne.cre_id_cpte AND e.date_comptable >= date(p_date_debut) AND e.date_comptable <= date(p_date_fin)
		GROUP BY m.cpte_interne_cli; --date quand le dossier est passé en perte
		--v_date_fin := v_date_fin; --ligne.cre_date_etat;

	END IF;
	--SELECT INTO cpte_cpta_int cpte_cpta_prod_cr_int FROM adsys_produit_credit WHERE id = ligne.id_prod;
	SELECT INTO tot_mnt_remb_int sum(case when emh.type_operation = 20 and emh.sens = 'c' then coalesce(emh.montant,0) --10,20,374
	when emh.type_operation = 21 and emh.sens = 'd' then coalesce(emh.montant,0)*-1 else 0 end) --11,21,375
	FROM inventaireCredit_ecr_mvt_his emh
	WHERE emh.compte IN (SELECT cpte_cpta_prod_cr_int FROM adsys_produit_credit WHERE id = ligne.id_prod) --m.compte = p.cpte_cpta_prod_cr_int
	AND coalesce(emh.info_ecriture,emh.infos) = to_char(ligne.id_doss,'FM999999999MI') AND emh.type_operation IN (20,21) --10,11,20,21,374,375
	AND date(emh.date_comptable) >= date(p_date_debut) AND date(emh.date_comptable) <= date(v_date_fin) GROUP BY coalesce(emh.info_ecriture,emh.infos); --interet ordinaire

	--SELECT INTO cpte_cpta_pen cpte_cpta_prod_cr_pen FROM adsys_produit_credit WHERE id = ligne.id_prod;
	SELECT INTO tot_mnt_remb_pen sum(case when emh.type_operation = 30 and emh.sens = 'c' then coalesce(emh.montant,0) --30,374
	when emh.type_operation = 31 and emh.sens = 'd' then coalesce(emh.montant,0)*-1 else 0 end) --31,375
	FROM inventaireCredit_ecr_mvt_his emh
	WHERE emh.compte IN (SELECT cpte_cpta_prod_cr_pen FROM adsys_produit_credit WHERE id = ligne.id_prod) --m.compte = p.cpte_cpta_prod_cr_pen
	AND emh.info_ecriture = to_char(ligne.id_doss,'FM999999999MI') AND emh.type_operation IN (30,31) --30,31,374,375
	AND date(emh.date_comptable) >= date(p_date_debut) AND date(emh.date_comptable) <= date(v_date_fin) GROUP BY emh.info_ecriture; --GROUP BY e.info_ecriture; --interet de retard

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
	--tot_mnt_remb_cap1 := tot_mnt_remb_cap1 + tot_mnt_remb_cap;
	--capital_fin1 := capital_fin1 + capital_fin;
	--perte_capital1 := perte_capital1 + ligne.perte_capital;
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
  ligne.cre_date_etat,ligne.id_client,ligne.nom_cli,capital_debut,cre_mnt_deb_per,tot_mnt_remb_cap,mnt_remb_cap,tot_mnt_remb_int,tot_mnt_remb_pen,montant_tot,mnt_tot,capital_fin,ligne.perte_capital;
  RETURN NEXT ligne_inventaire; --ligne.capital_debut


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

-- Function: getinventairecreditsprincipale(date, date, integer, integer)

-- DROP FUNCTION getinventairecreditsprincipale(date, date, integer, integer);

CREATE OR REPLACE FUNCTION getinventairecreditsprincipale(date, date, integer, integer)
  RETURNS SETOF inventairecredits_view AS
$BODY$
DECLARE
  DECLARE
  p_date_debut ALIAS FOR $1;
  p_date_fin ALIAS FOR $2;
  p_id_etat ALIAS FOR $3;
  p_etat_selecte ALIAS FOR $4;

  ligne_inventaire inventairecredits_view;
  ligne RECORD;
  donnee_inventairecredits refcursor;
  cre_date_debloc date;

BEGIN

  --Table temporaire pour les informations ad_ecriture, ad_mouvement et ad_his pour la periode donnees
  DROP TABLE IF EXISTS inventaireCredit_ecr_mvt_his;
  CREATE TEMP TABLE inventaireCredit_ecr_mvt_his AS SELECT e.date_comptable, e.type_operation, e.info_ecriture, m.compte, m.sens, m.montant, m.devise, h.infos
  FROM ad_ecriture e INNER JOIN ad_mouvement m ON e.id_ecriture = m.id_ecriture INNER JOIN ad_his h ON e.id_his = h.id_his
  WHERE e.type_operation IN (20,21,30,31) AND date(e.date_comptable) >= date(p_date_debut) AND date(e.date_comptable) <= date(p_date_fin)
  ORDER BY e.id_ecriture ASC;

  --Table temporaire pour les donnees inventaires credits
  DROP TABLE IF EXISTS inventaireCredit;
  CREATE TEMP TABLE inventaireCredit AS SELECT * FROM getinventairecredits(date(p_date_debut),date(p_date_fin),p_id_etat);

  --Create table temporaire pour ramener les certains dossiers credits qui se sont deboursés et soldés au cours de la periode exclude du fonction getinventairecredits
  IF p_etat_selecte = 3 OR p_etat_selecte = 4 THEN
	--Drop table temporaire
	DROP TABLE IF EXISTS inventaireCredit_solde;

	CREATE TEMP TABLE inventaireCredit_solde AS SELECT s.type_rapport::text, s.id_prod, s.obj_dem, s.id_doss, s.cre_date_debloc, s.cre_mnt_deb, s.etat, s.date_etat,
	s.cre_etat, s.cre_date_etat, s.id_client, s.nom_cli, 0.000000 AS mnt_cap_debut, s.cre_mnt_deb_per, s.tot_mnt_remb_cap, s.tot_mnt_remb_cap AS mnt_remb_cap,
	s.tot_mnt_remb_int, s.mnt_remb_pen, (s.tot_mnt_remb_cap + s.tot_mnt_remb_int + s.mnt_remb_pen) AS montant_tot,
	(s.tot_mnt_remb_cap + s.tot_mnt_remb_int + s.mnt_remb_pen) AS mnt_tot, 0.000000 AS mnt_restant_du, s.perte_capital
	FROM (SELECT '2-SOLDE' as type_rapport, d.id_prod, d.obj_dem, d.id_doss, d.cre_date_debloc, d.cre_mnt_octr AS cre_mnt_deb, d.etat, d.date_etat, d.cre_etat,
	d.cre_date_etat, d.id_client, CASE c.statut_juridique WHEN '1' THEN c.pp_nom||' '||c.pp_prenom WHEN '2' THEN c.pm_raison_sociale WHEN '3'  THEN c.gi_nom WHEN '4'
	THEN c.gi_nom END AS nom_cli, d.cre_mnt_octr AS cre_mnt_deb_per, (SELECT sum(COALESCE(mnt_remb_cap,0))
	FROM ad_sre WHERE id_doss = d.id_doss AND date_remb BETWEEN date(p_date_debut) AND date(p_date_fin)) AS tot_mnt_remb_cap,
	COALESCE((SELECT sum(CASE WHEN emh.type_operation = 20 AND emh.sens = 'c' THEN COALESCE(emh.montant,0) WHEN emh.type_operation = 21 AND emh.sens = 'd'
	THEN COALESCE(emh.montant,0)*-1 ELSE 0 END) FROM inventaireCredit_ecr_mvt_his emh
	WHERE emh.compte IN (SELECT cpte_cpta_prod_cr_int FROM adsys_produit_credit WHERE id = d.id_prod) AND
	COALESCE(emh.info_ecriture,emh.infos) = to_char(d.id_doss,'FM999999999MI') AND emh.type_operation IN (20,21)
	--AND e.date_comptable >= date(p_date_debut) AND e.date_comptable <= date(p_date_fin)
	GROUP BY COALESCE(emh.info_ecriture,emh.infos)),0.000000) AS tot_mnt_remb_int,COALESCE((SELECT sum(CASE WHEN emh.type_operation = 30 AND emh.sens = 'c'
	THEN COALESCE(emh.montant,0) WHEN emh.type_operation = 31 AND emh.sens = 'd' THEN COALESCE(emh.montant,0)*-1 ELSE 0 END)
	FROM inventaireCredit_ecr_mvt_his emh WHERE emh.compte IN (SELECT cpte_cpta_prod_cr_pen FROM adsys_produit_credit
	WHERE id = d.id_prod) AND emh.info_ecriture = to_char(d.id_doss,'FM999999999MI') AND emh.type_operation IN (30,31)
	--AND e.date_comptable >= date(p_date_debut) AND e.date_comptable <= date(p_date_fin)
	GROUP BY emh.info_ecriture),0.000000) AS mnt_remb_pen, d.perte_capital FROM ad_dcr d INNER JOIN ad_cli c ON d.id_client = c.id_client
	INNER JOIN (SELECT DISTINCT dcr.id_doss FROM ad_dcr dcr WHERE dcr.etat = 6 AND dcr.date_etat BETWEEN date(p_date_debut) AND date(p_date_fin)
	AND dcr.cre_date_debloc >= date(p_date_debut) EXCEPT (SELECT DISTINCT id_doss FROM inventaireCredit)) d1 ON d.id_doss = d1.id_doss) s;
  END IF;

  --Donnees pour les etat dossiers Encours et Radie
  IF p_etat_selecte = 1 OR p_etat_selecte = 2 THEN
	OPEN donnee_inventairecredits FOR SELECT * FROM inventaireCredit;
  END IF;

  --Donnees pour les etat dossiers Soldé et Tous
  IF p_etat_selecte = 3 OR p_etat_selecte = 4 THEN
	OPEN donnee_inventairecredits FOR SELECT A.* FROM (SELECT * FROM inventaireCredit UNION SELECT * FROM inventaireCredit_solde) A;
  END IF;

  FETCH donnee_inventairecredits INTO ligne;
  WHILE FOUND LOOP

	cre_date_debloc := ligne.cre_date_debloc;
	IF p_etat_selecte = 3 OR p_etat_selecte = 4 THEN
		cre_date_debloc := DATE(ligne.cre_date_debloc);
	END IF;
	--ligne du rapport
	SELECT INTO ligne_inventaire  ligne.type_rapport,ligne.id_prod,ligne.obj_dem,ligne.id_doss,cre_date_debloc,ligne.cre_mnt_deb,ligne.etat,ligne.date_etat,ligne.cre_etat,
	ligne.cre_date_etat,ligne.id_client,ligne.nom_cli,ligne.mnt_cap_debut,ligne.cre_mnt_deb_per,ligne.tot_mnt_remb_cap,ligne.mnt_remb_cap,ligne.tot_mnt_remb_int,ligne.mnt_remb_pen,ligne.montant_tot,ligne.mnt_tot,ligne.mnt_restant_du,ligne.perte_capital;
	RETURN NEXT ligne_inventaire;
	FETCH donnee_inventairecredits INTO ligne;
  END LOOP;

  CLOSE donnee_inventairecredits;

  RETURN;
END;
$BODY$
  LANGUAGE plpgsql VOLATILE
  COST 100
  ROWS 1000;
ALTER FUNCTION getinventairecreditsprincipale(date, date, integer, integer)
  OWNER TO adbanking;

------------------------------- FIN : Ticket REL-40 -----------------------------------------------------------------------------------------------------------
