DROP TYPE portefeuille_view Cascade;



CREATE TYPE portefeuille_view AS
(
  id_doss integer,
  id_client integer,
  id_prod integer,
  obj_dem integer,
  date_dem date,
  cre_mnt_octr numeric(30,6),
  gs_cat integer,
  id_dcr_grp_sol integer,
  devise character(3),
  cre_id_cpte integer,
  cre_date_debloc date,
  date_etat_doss date,
  type_duree_credit integer,
  duree_mois integer,
  id_etat_credit integer,
  cre_date_etat date,
  credit_en_perte boolean,
  perte_capital numeric(30,6),
  nom_cli text,
  nbr_ech_total integer,
  nbr_ech_paye integer,
  mnt_cred_paye numeric(30,6),
  mnt_int_att numeric(30,6),
  mnt_int_paye numeric(30,6),
  mnt_gar_att numeric(30,6),
  mnt_gar_paye numeric(30,6),
  mnt_pen_att numeric(30,6),
  mnt_pen_paye numeric(30,6),
  mnt_gar_mob numeric(30,6),
  solde_retard numeric(30,6),
  int_retard numeric(30,6),
  gar_retard numeric(30,6),
  pen_retard numeric(30,6),
  date_echeance date,
  nbr_jours_retard integer,
  nbre_ech_retard integer,
  libel_etat_credit text,
  cre_nbre_reech integer,
  taux_prov double precision,
  prov_mnt numeric(30,6),
  id_agent_gest integer,
  is_credit_decouvert boolean,
  id_ag integer,
  cre_mnt_deb numeric(30,6),
  grace_period integer,
  periodicite integer,
  is_ligne_credit boolean,
  detail_obj_dem text,
  detail_obj_dem_bis integer,
  n_agence integer,
  n_guichet integer
);

  -- Function: getportfeuilleview(date, integer)

-- DROP FUNCTION getportfeuilleview(date, integer);

CREATE OR REPLACE FUNCTION getportfeuilleview(
    date,
    integer)
  RETURNS SETOF portefeuille_view AS
$BODY$
DECLARE
  date_export ALIAS FOR $1;
  id_agence ALIAS FOR $2;
  ligne_portefeuille portefeuille_view;
  ligne RECORD;
  ligne_ech RECORD;
  ligne_remb RECORD;
    portefeuille CURSOR FOR SELECT d.gs_cat,d.id_dcr_grp_sol,d.date_dem ,d.id_doss,d.id_client,d.id_ag,d
  .cre_mnt_octr,d.cre_date_debloc,d.duree_mois, d.etat, d.cre_id_cpte, calculnombrejoursretardoss(d.id_doss, date
  (date_export), id_agence) AS nbr_jours_retard, (case WHEN date(date_export) = date(now()) THEN d.cre_etat ELSE CalculEtatCredit(d.id_doss, date(date_export), id_agence) END ) AS cre_etat, d.cre_etat AS cre_etat_cur, d.date_etat, d.cre_date_etat, d.cre_nbre_reech, d.perte_capital, d.id_agent_gest, d.id_prod, d.obj_dem, d.id_ag, d.cre_mnt_deb, d.is_ligne_credit, d.detail_obj_dem, d.detail_obj_dem_bis, d.type_duree_credit, d.periodicite, d.devise, d.is_produit_decouvert, d.differe_ech, d.differe_jours, COALESCE(d.prov_mnt,0) as prov_mnt FROM get_ad_dcr_ext_credit(null, null, null, null, id_agence) d WHERE d.cre_date_debloc <= date(date_export) AND ((d.etat IN (5,7,8,13,14,15)) OR (d.etat IN (6,9,11,12) AND d.date_etat > date(date_export))) AND d.id_ag=id_agence ORDER BY d.id_doss;
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
  v_agence INTEGER;
  v_guichet INTEGER;
  
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

  -- Recuperation niveau agence et guichet

  SELECT INTO v_agence, v_guichet n_agence, n_guichet FROM ad_cli WHERE  id_client = ligne.id_client; 

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
  
  SELECT INTO ligne_portefeuille  ligne.id_doss, ligne.id_client, ligne.id_prod, ligne.obj_dem, date_demx, (mnt_cap_att) AS cre_mnt_octr, gs_catx, id_dcr_grp_solx, devise_credit AS devise, ligne.cre_id_cpte, ligne.cre_date_debloc, ligne.date_etat AS date_etat_doss, type_duree_creditx, ligne.duree_mois, id_etat_credit, ligne.cre_date_etat, credit_en_perte, ligne.perte_capital, nom_client AS nom_cli, nbr_ech_total,(nbr_ech_total - nbr_ech_impaye) AS nbr_ech_paye, mnt_cred_paye, mnt_int_att, mnt_int_paye, mnt_gar_att, mnt_gar_paye, mnt_pen_att, mnt_pen_paye, COALESCE(mnt_gar_mob,0), solde_retard, int_retard, gar_retard, pen_retard, date_echeance, ligne.nbr_jours_retard, nbre_ech_retard, etat_credit, ligne.cre_nbre_reech, taux_prov, COALESCE(prev_prov,0) AS prov_mnt, ligne.id_agent_gest, is_credit_decouvert, ligne.id_ag, ligne.cre_mnt_deb, grace_period, periodicitex as periodicite, ligne.is_ligne_credit,ligne.detail_obj_dem,ligne.detail_obj_dem_bis,v_agence, v_guichet;
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
ALTER FUNCTION getportfeuilleview(date, integer)
  OWNER TO adbanking;


  -- Function: getportfeuilleviewdoss(date, integer, integer)

-- DROP FUNCTION getportfeuilleviewdoss(date, integer, integer);

CREATE OR REPLACE FUNCTION getportfeuilleviewdoss(
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
    v_agence INTEGER;
  v_guichet INTEGER;

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

    -- Recuperation niveau agence et guichet

  SELECT INTO v_agence, v_guichet n_agence, n_guichet FROM ad_cli WHERE  id_client = ligne.id_client;

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

  SELECT INTO ligne_portefeuille  ligne.id_doss, ligne.id_client, ligne.id_prod, ligne.obj_dem, date_demx, (mnt_cap_att) AS cre_mnt_octr, gs_catx, id_dcr_grp_solx, devise_credit AS devise, ligne.cre_id_cpte, ligne.cre_date_debloc, ligne.date_etat AS date_etat_doss, type_duree_creditx, ligne.duree_mois, id_etat_credit, ligne.cre_date_etat, credit_en_perte, ligne.perte_capital, nom_client AS nom_cli, nbr_ech_total,(nbr_ech_total - nbr_ech_impaye) AS nbr_ech_paye, mnt_cred_paye, mnt_int_att, mnt_int_paye, mnt_gar_att, mnt_gar_paye, mnt_pen_att, mnt_pen_paye, COALESCE(mnt_gar_mob,0), solde_retard, int_retard, gar_retard, pen_retard, date_echeance, ligne.nbr_jours_retard, nbre_ech_retard, etat_credit, ligne.cre_nbre_reech, taux_prov, COALESCE(prev_prov,0) AS prov_mnt, ligne.id_agent_gest, is_credit_decouvert, ligne.id_ag, ligne.cre_mnt_deb, grace_period, periodicitex as periodicite, ligne.is_ligne_credit,ligne.detail_obj_dem,ligne.detail_obj_dem_bis,v_agence, v_guichet;
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
ALTER FUNCTION getportfeuilleviewdoss(date, integer, integer)
  OWNER TO adbanking;



DROP TYPE IF EXISTS dcr_credit_view CASCADE;

CREATE TYPE dcr_credit_view AS
(
		id_doss integer,
		id_client integer,
		id_prod integer,
		date_dem timestamp without time zone,
		mnt_dem numeric(30,6),
		obj_dem integer,
		detail_obj_dem text,
		etat integer,
		date_etat timestamp without time zone,
		motif integer,
		id_agent_gest integer,
		delai_grac integer,
		differe_jours integer,
		prelev_auto boolean,
		duree_mois smallint,
		nouv_duree_mois smallint,
		terme integer,
		gar_num numeric(30,6),
		gar_tot numeric(30,6),
		gar_mat numeric(30,6),
		gar_num_encours numeric(30,6),
		cpt_gar_encours integer,
		num_cre smallint,
		assurances_cre boolean,
		cpt_liaison integer,
		cre_id_cpte integer,
		cre_etat integer,
		cre_date_etat timestamp without time zone,
		cre_date_approb timestamp without time zone,
		cre_date_debloc timestamp without time zone,
		cre_nbre_reech integer,
		cre_mnt_octr numeric(30,6),
		details_motif text,
		suspension_pen boolean,
		perte_capital numeric(30,6),
		cre_retard_etat_max integer,
		cre_retard_etat_max_jour integer,
		differe_ech integer,
		id_dcr_grp_sol integer,
		gs_cat smallint,
		prelev_commission boolean,
		cpt_prelev_frais integer,
		id_ag integer,
		cre_prelev_frais_doss boolean,
		prov_mnt numeric(30,6),
		prov_date date,
		prov_is_calcul boolean,
		cre_mnt_deb numeric(30,6),
		doss_repris boolean,
		cre_cpt_att_deb integer,
		date_creation timestamp without time zone,
		date_modif timestamp without time zone,
		is_ligne_credit boolean,
		deboursement_autorisee_lcr boolean,
		motif_changement_authorisation_lcr text,
		date_changement_authorisation_lcr timestamp without time zone,
		duree_nettoyage_lcr integer,
		remb_auto_lcr boolean,
		tx_interet_lcr double precision,
		taux_frais_lcr double precision,
		taux_min_frais_lcr numeric(30,6),
		taux_max_frais_lcr numeric(30,6),
		ordre_remb_lcr smallint,
		mnt_assurance numeric(30,6),
		mnt_commission numeric(30,6),
		mnt_frais_doss numeric(30,6),
		detail_obj_dem_bis integer,
		detail_obj_dem_2 integer,
		id_bailleur integer,
		is_extended boolean,
		id integer,
		libel text,
		tx_interet double precision,
		mnt_min numeric(30,6),
		mnt_max numeric(30,6),
		mode_calc_int integer,
		mode_perc_int integer,
		duree_min_mois integer,
		duree_max_mois integer,
		periodicite integer,
		mnt_frais numeric(30,6),
		prc_assurance double precision,
		prc_gar_num double precision,
		prc_gar_mat double precision,
		prc_gar_tot double precision,
		prc_gar_encours double precision,
		mnt_penalite_jour numeric(30,6),
		prc_penalite_retard double precision,
		delai_grace_jour integer,
		differe_jours_max integer,
		nbre_reechelon_auth smallint,
		prc_commission double precision,
		type_duree_credit integer,
		approbation_obli boolean,
		typ_pen_pourc_dcr integer,
		cpte_cpta_prod_cr_int text,
		cpte_cpta_prod_cr_gar text,
		cpte_cpta_prod_cr_pen text,
		devise character(3),
		differe_ech_max integer,
		freq_paiement_cap integer,
		max_jours_compt_penalite integer,
		differe_epargne_nantie boolean,
		report_arrondi boolean,
		calcul_interet_differe boolean,
		prelev_frais_doss smallint,
		percep_frais_com_ass smallint,
		ordre_remb smallint,
		remb_cpt_gar boolean,
		is_produit_decouvert boolean,
		prc_frais double precision,
		cpte_cpta_att_deb text,
		is_produit_actif boolean,
		duree_nettoyage integer,
		cpte_cpta_prod_cr_frais text,
		prelev_garanti INTEGER,
		appl_interet_diff_echeance INTEGER,
		diff_ech_apres_deb INTEGER,
		cpt_remb INTEGER,
		n_agence INTEGER,
		n_guichet INTEGER
);
ALTER TYPE dcr_credit_view
OWNER TO adbanking;


-- Function: get_ad_dcr_ext_credit(integer, integer, integer, integer, integer)

-- DROP FUNCTION get_ad_dcr_ext_credit(integer, integer, integer, integer, integer);

CREATE OR REPLACE FUNCTION get_ad_dcr_ext_credit(
    integer,
    integer,
    integer,
    integer,
    integer)
  RETURNS SETOF dcr_credit_view AS
$BODY$
	DECLARE
	p_id_dossier ALIAS FOR $1;
	p_id_client ALIAS FOR $2;
	p_etat ALIAS FOR $3;
	p_cre_etat ALIAS FOR $4;
	p_id_agence ALIAS FOR $5;
	statut INTEGER ;


	cur_credit_gs CURSOR FOR SELECT grp.id_grp_sol as id_grp, grp.id_membre as id_client, dcr.id_doss, dcr.id_ag, dcr.is_extended FROM ad_grp_sol grp
		inner join ad_dcr dcr on grp.id_membre = dcr.id_client and grp.id_ag = dcr.id_ag
	WHERE grp.id_grp_sol = p_id_client
													 union
													 select CASE WHEN dcr.gs_cat = 1 THEN dcr.id_client END as id_grp, dcr.id_client, dcr.id_doss, dcr.id_ag, dcr.is_extended from ad_dcr dcr where dcr.id_client = p_id_client and dcr.id_ag= p_id_agence;


	cur_credit CURSOR FOR SELECT id_doss, id_ag, is_extended FROM ad_dcr WHERE id_client = CASE WHEN p_id_client IS NULL THEN id_client ELSE p_id_client END AND id_doss = CASE WHEN p_id_dossier IS NULL THEN id_doss
																																																																																				 ELSE p_id_dossier END AND etat = CASE WHEN p_etat IS NULL THEN etat ELSE p_etat END AND coalesce(cre_etat,0) = CASE WHEN p_cre_etat IS NULL THEN coalesce(cre_etat,0) ELSE p_cre_etat END AND id_ag = p_id_agence
												ORDER BY id_doss ASC;


	ligne RECORD;

	dcr_credit dcr_credit_view;

BEGIN

	select into statut statut_juridique from ad_cli where id_client = p_id_client;

	IF (statut = '4') THEN
		OPEN cur_credit_gs;
		FETCH cur_credit_gs INTO ligne;
	ELSE
		OPEN cur_credit;
		FETCH cur_credit INTO ligne;
	END IF;

WHILE FOUND LOOP

IF (ligne.is_extended = 't') THEN

	SELECT INTO dcr_credit  d.id_doss, d.id_client, d.id_prod, d.date_dem, d.mnt_dem, d.obj_dem, d.detail_obj_dem, d.etat, d.date_etat, d.motif, d.id_agent_gest,
	d.delai_grac, d.differe_jours, d.prelev_auto, d.duree_mois, d.nouv_duree_mois, d.terme, d.gar_num, d.gar_tot, d.gar_mat, d.gar_num_encours, d.cpt_gar_encours,
	d.num_cre, d.assurances_cre, d.cpt_liaison, d.cre_id_cpte, d.cre_etat, d.cre_date_etat, d.cre_date_approb, d.cre_date_debloc, d.cre_nbre_reech, d.cre_mnt_octr,
	d.details_motif, d.suspension_pen, d.perte_capital, d.cre_retard_etat_max, d.cre_retard_etat_max_jour, d.differe_ech, d.id_dcr_grp_sol, dx.gs_cat, d.prelev_commission,
	d.cpt_prelev_frais, d.id_ag, d.cre_prelev_frais_doss, d.prov_mnt, d.prov_date, d.prov_is_calcul, d.cre_mnt_deb, d.doss_repris, d.cre_cpt_att_deb, d.date_creation,
	d.date_modif, d.is_ligne_credit, d.deboursement_autorisee_lcr, d.motif_changement_authorisation_lcr, d.date_changement_authorisation_lcr, d.duree_nettoyage_lcr,
	d.remb_auto_lcr, d.tx_interet_lcr, d.taux_frais_lcr, d.taux_min_frais_lcr, d.taux_max_frais_lcr, d.ordre_remb_lcr, dx.mnt_assurance, dx.mnt_commission,
	d.mnt_frais_doss, d.detail_obj_dem_bis,d.detail_obj_dem_2, d.id_bailleur, d.is_extended, pc.id, pc.libel, dx.tx_interet, pc.mnt_min, pc.mnt_max, pc.mode_calc_int,
	pc.mode_perc_int, pc.duree_min_mois, pc.duree_max_mois, dx.periodicite, dx.mnt_frais, dx.prc_assurance, dx.prc_gar_num, pc.prc_gar_mat,
	(dx.prc_gar_num + pc.prc_gar_mat), pc.prc_gar_encours, pc.mnt_penalite_jour, pc.prc_penalite_retard, pc.delai_grace_jour, pc.differe_jours_max,
	pc.nbre_reechelon_auth, dx.prc_commission, pc.type_duree_credit, pc.approbation_obli, pc.typ_pen_pourc_dcr, pc.cpte_cpta_prod_cr_int, pc.cpte_cpta_prod_cr_gar,
	pc.cpte_cpta_prod_cr_pen, pc.devise, pc.differe_ech_max, pc.freq_paiement_cap, pc.max_jours_compt_penalite, pc.differe_epargne_nantie, pc.report_arrondi,
	pc.calcul_interet_differe, pc.prelev_frais_doss, pc.percep_frais_com_ass, pc.ordre_remb, pc.remb_cpt_gar, pc.is_produit_decouvert, dx.prc_frais,
	pc.cpte_cpta_att_deb, pc.is_produit_actif, pc.duree_nettoyage, pc.cpte_cpta_prod_cr_frais, pc.prelev_garanti, pc.appl_interet_diff_echeance, d.diff_ech_apres_deb, d.cpt_remb,cli.n_agence,cli.n_guichet FROM ad_dcr d LEFT JOIN ad_dcr_ext dx ON d.id_doss = dx.id_doss
	AND d.id_ag = dx.id_ag INNER JOIN adsys_produit_credit pc ON d.id_prod = pc.id AND d.id_ag = pc.id_ag INNER JOIN ad_cli cli on cli.id_client = d.id_client WHERE d.id_doss = ligne.id_doss AND d.id_ag = ligne.id_ag;

	ELSE

	SELECT INTO dcr_credit  d.id_doss, d.id_client, d.id_prod, d.date_dem, d.mnt_dem, d.obj_dem, d.detail_obj_dem, d.etat, d.date_etat,
	d.motif, d.id_agent_gest, d.delai_grac, d.differe_jours, d.prelev_auto, d.duree_mois, d.nouv_duree_mois,
	d.terme, d.gar_num, d.gar_tot, d.gar_mat, d.gar_num_encours, d.cpt_gar_encours, d.num_cre, d.assurances_cre,
	d.cpt_liaison, d.cre_id_cpte, d.cre_etat, d.cre_date_etat, d.cre_date_approb, d.cre_date_debloc, d.cre_nbre_reech,
	d.cre_mnt_octr, d.details_motif, d.suspension_pen, d.perte_capital, d.cre_retard_etat_max, d.cre_retard_etat_max_jour, d.differe_ech,
	d.id_dcr_grp_sol, d.gs_cat, d.prelev_commission, d.cpt_prelev_frais, d.id_ag, d.cre_prelev_frais_doss, d.prov_mnt, d.prov_date,
	d.prov_is_calcul, d.cre_mnt_deb, d.doss_repris, d.cre_cpt_att_deb, d.date_creation, d.date_modif, d.is_ligne_credit,
	d.deboursement_autorisee_lcr, d.motif_changement_authorisation_lcr, d.date_changement_authorisation_lcr,
	d.duree_nettoyage_lcr, d.remb_auto_lcr, d.tx_interet_lcr, d.taux_frais_lcr, d.taux_min_frais_lcr,
	d.taux_max_frais_lcr, d.ordre_remb_lcr, d.mnt_assurance, d.mnt_commission, d.mnt_frais_doss,
	d.detail_obj_dem_bis,d.detail_obj_dem_2, d.id_bailleur, d.is_extended, pc.id, pc.libel, pc.tx_interet, pc.mnt_min, pc.mnt_max,
	pc.mode_calc_int, pc.mode_perc_int, pc.duree_min_mois, pc.duree_max_mois, pc.periodicite, pc.mnt_frais, pc.prc_assurance,
	pc.prc_gar_num, pc.prc_gar_mat, pc.prc_gar_tot, pc.prc_gar_encours, pc.mnt_penalite_jour, pc.prc_penalite_retard, pc.delai_grace_jour,
	pc.differe_jours_max, pc.nbre_reechelon_auth, pc.prc_commission, pc.type_duree_credit, pc.approbation_obli, pc.typ_pen_pourc_dcr,
	pc.cpte_cpta_prod_cr_int, pc.cpte_cpta_prod_cr_gar, pc.cpte_cpta_prod_cr_pen, pc.devise, pc.differe_ech_max, pc.freq_paiement_cap,
	pc.max_jours_compt_penalite, pc.differe_epargne_nantie, pc.report_arrondi, pc.calcul_interet_differe, pc.prelev_frais_doss,
	pc.percep_frais_com_ass, pc.ordre_remb, pc.remb_cpt_gar, pc.is_produit_decouvert, pc.prc_frais, pc.cpte_cpta_att_deb,
	pc.is_produit_actif, pc.duree_nettoyage, pc.cpte_cpta_prod_cr_frais, pc.prelev_garanti, pc.appl_interet_diff_echeance, d.diff_ech_apres_deb, d.cpt_remb, cli.n_agence, cli.n_guichet FROM ad_dcr d LEFT JOIN adsys_produit_credit pc
	ON d.id_prod = pc.id AND d.id_ag = pc.id_ag INNER JOIN ad_cli cli on cli.id_client = d.id_client WHERE d.id_doss = ligne.id_doss AND d.id_ag = ligne.id_ag;

END IF;

RETURN NEXT dcr_credit;
IF (statut = '4') THEN
	FETCH cur_credit_gs INTO ligne;
ELSE
	FETCH cur_credit INTO ligne;
END IF;

END LOOP;
IF (statut = '4') THEN
	CLOSE cur_credit_gs;
ELSE
	CLOSE cur_credit;
END IF;

RETURN;
END;
$BODY$
  LANGUAGE plpgsql VOLATILE
  COST 100
  ROWS 1000;
ALTER FUNCTION get_ad_dcr_ext_credit(integer, integer, integer, integer, integer)
  OWNER TO postgres;



	CREATE OR REPLACE FUNCTION script_ajout_column_ad_cli_hist() RETURNS INT AS
$$
DECLARE
output_result INTEGER = 1;

BEGIN

    IF NOT EXISTS(SELECT * FROM information_schema.columns WHERE table_name = 'ad_cli_hist' AND column_name = 'n_agence') THEN
    ALTER TABLE ad_cli_hist ADD COLUMN n_agence INTEGER;
    END IF;

    IF NOT EXISTS(SELECT * FROM information_schema.columns WHERE table_name = 'ad_cli_hist' AND column_name = 'n_guichet') THEN
    ALTER TABLE ad_cli_hist ADD COLUMN n_guichet INTEGER;
    END IF;


	RETURN output_result;

END;
$$
LANGUAGE plpgsql;

SELECT script_ajout_column_ad_cli_hist();
DROP FUNCTION script_ajout_column_ad_cli_hist();