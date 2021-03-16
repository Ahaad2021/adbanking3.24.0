-- Type: portefeuille_view

-- Function: DROP TYPE portefeuille_view Cascade;
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
    id_ag integer);
ALTER TYPE portefeuille_view
  OWNER TO adbanking;

-- Function: getportfeuilleview(date, integer)

-- DROP FUNCTION getportfeuilleview(date, integer);

CREATE OR REPLACE FUNCTION getportfeuilleview(date, integer)
  RETURNS SETOF portefeuille_view AS
$BODY$
DECLARE
  date_export ALIAS FOR $1;
  id_agence ALIAS FOR $2;
  ligne_portefeuille portefeuille_view;
  ligne RECORD;
  ligne_ech RECORD;
  ligne_remb RECORD;
    portefeuille CURSOR FOR SELECT gs_cat,id_dcr_grp_sol,date_dem ,id_doss,id_client,id_ag,cre_mnt_octr,cre_date_debloc,duree_mois, etat, cre_id_cpte, calculnombrejoursretardoss(id_doss, date(date_export), id_agence) AS nbr_jours_retard, 
                            (case WHEN date(date_export) = date(now()) THEN cre_etat ELSE CalculEtatCredit(id_doss, date(date_export), id_agence) END ) AS cre_etat,
      						cre_etat AS cre_etat_cur, date_etat, cre_date_etat, cre_nbre_reech, perte_capital, id_agent_gest, id_prod, obj_dem, id_ag 
    					  FROM ad_dcr WHERE cre_date_debloc <= date(date_export) AND ((etat IN (5,7,8,13,14,15)) OR (etat IN (6,11,12) AND date_etat > date(date_export))) AND id_ag=id_agence ORDER BY id_doss;
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
  
  
 
BEGIN
  -- Récupère l' id de l'état en perte
  SELECT INTO id_etat_perte id FROM adsys_etat_credits WHERE nbre_jours = -1 AND id_ag = id_agence;
  
  OPEN portefeuille ;
  FETCH portefeuille INTO ligne;
  WHILE FOUND LOOP
  SELECT INTO gs_catx gs_cat  FROM ad_dcr WHERE id_doss = ligne.id_doss;
  SELECT INTO id_dcr_grp_solx id_dcr_grp_sol  FROM ad_dcr WHERE id_doss = ligne.id_doss;
  SELECT INTO date_demx date_dem  FROM ad_dcr WHERE id_doss = ligne.id_doss;
  SELECT INTO type_duree_creditx type_duree_credit FROM adsys_produit_credit WHERE id = ligne.id_prod;
  
  
  -- Récupère le nom du client
  SELECT INTO nom_client CASE statut_juridique WHEN '1' THEN pp_nom||' '||pp_prenom WHEN '2' THEN pm_raison_sociale WHEN '3'  THEN gi_nom WHEN '4'  THEN gi_nom END FROM ad_cli WHERE id_client = ligne.id_client;
  
 
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
  SELECT INTO devise_credit, is_credit_decouvert devise, is_produit_decouvert FROM adsys_produit_credit WHERE id = ligne.id_prod AND id_ag = id_agence; 
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
        SELECT INTO prev_prov COALESCE(prov_mnt,0) FROM ad_dcr WHERE id_doss = ligne.id_doss AND id_ag = id_agence ;
     ELSE 
        SELECT INTO prev_prov COALESCE(montant,0) FROM ad_provision WHERE id_doss = ligne.id_doss AND id_ag = id_agence AND date_prov = (SELECT MAX(date_prov) FROM ad_provision WHERE date_prov < date_export AND id_doss = ligne.id_doss AND id_ag = id_agence) ;
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
  
  SELECT INTO ligne_portefeuille  ligne.id_doss, ligne.id_client, ligne.id_prod, ligne.obj_dem, date_demx, (mnt_cap_att) AS cre_mnt_octr, gs_catx, id_dcr_grp_solx, devise_credit AS devise, ligne.cre_id_cpte, ligne.cre_date_debloc, ligne.date_etat AS date_etat_doss, type_duree_creditx, ligne.duree_mois, id_etat_credit, ligne.cre_date_etat, credit_en_perte, ligne.perte_capital, nom_client AS nom_cli, nbr_ech_total,(nbr_ech_total - nbr_ech_impaye) AS nbr_ech_paye, mnt_cred_paye, mnt_int_att, mnt_int_paye, mnt_gar_att, mnt_gar_paye, mnt_pen_att, mnt_pen_paye, COALESCE(mnt_gar_mob,0), solde_retard, int_retard, gar_retard, pen_retard, date_echeance, ligne.nbr_jours_retard, nbre_ech_retard, etat_credit, ligne.cre_nbre_reech, taux_prov, COALESCE(prev_prov,0) AS prov_mnt, ligne.id_agent_gest, is_credit_decouvert, ligne.id_ag;
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
