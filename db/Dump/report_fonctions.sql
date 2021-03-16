
DROP TYPE IF EXISTS portefeuille_view CASCADE;

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
    cre_mnt_deb numeric(30,6),
    grace_period integer,
    periodicite integer, 
    id_ag integer
);

 --DROP FUNCTION getPortfeuilleView(date, integer);
CREATE OR REPLACE FUNCTION getPortfeuilleView(date, integer)
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
      						cre_etat AS cre_etat_cur, date_etat, cre_date_etat, cre_nbre_reech, perte_capital, id_agent_gest, id_prod, obj_dem, id_ag, cre_mnt_deb 
    					  FROM ad_dcr WHERE cre_date_debloc <= date(date_export) AND ((etat IN (5,7,8,13,14,15)) OR (etat IN (6,9,11,12) AND date_etat > date(date_export))) AND id_ag=id_agence ORDER BY id_doss;
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
  SELECT INTO nom_client CASE statut_juridique WHEN '1' THEN pp_nom||' '||pp_prenom WHEN '2' THEN pm_raison_sociale WHEN '3'  THEN gi_nom WHEN '4'  THEN gi_nom END FROM ad_cli 
  WHERE id_client = ligne.id_client;


  -- grace_periode 
  SELECT INTO grace_period ((case when ad_dcr.differe_ech is null then 0 else ad_dcr.differe_ech end) * (case when adsys_produit_credit.periodicite=1 then 30 
														when adsys_produit_credit.periodicite=2 then 15  
														when adsys_produit_credit.periodicite=3 then 90 
														when adsys_produit_credit.periodicite=4 then 180
														when adsys_produit_credit.periodicite=5 then 365
														when adsys_produit_credit.periodicite=6 then 0
														when adsys_produit_credit.periodicite=7 then 60
														else 7
													end )) + (case when ad_dcr.differe_jours is null then 0 else ad_dcr.differe_jours end)
  from ad_dcr inner join adsys_produit_credit on ad_dcr.id_ag=adsys_produit_credit.id_ag and ad_dcr.id_ag=id_agence
  and ad_dcr.id_prod=adsys_produit_credit.id and ad_dcr.id_prod=ligne.id_prod
  and ad_dcr.id_doss=ligne.id_doss;

   -- periodicité
 SELECT INTO periodicitex periodicite FROM adsys_produit_credit WHERE id = ligne.id_prod and id_ag=id_agence;
 
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
  
  SELECT INTO ligne_portefeuille  ligne.id_doss, ligne.id_client, ligne.id_prod, ligne.obj_dem, date_demx, (mnt_cap_att) AS cre_mnt_octr, gs_catx, id_dcr_grp_solx, devise_credit AS devise, ligne.cre_id_cpte, ligne.cre_date_debloc, ligne.date_etat AS date_etat_doss, type_duree_creditx, ligne.duree_mois, id_etat_credit, ligne.cre_date_etat, credit_en_perte, ligne.perte_capital, nom_client AS nom_cli, nbr_ech_total,(nbr_ech_total - nbr_ech_impaye) AS nbr_ech_paye, mnt_cred_paye, mnt_int_att, mnt_int_paye, mnt_gar_att, mnt_gar_paye, mnt_pen_att, mnt_pen_paye, COALESCE(mnt_gar_mob,0), solde_retard, int_retard, gar_retard, pen_retard, date_echeance, ligne.nbr_jours_retard, nbre_ech_retard, etat_credit, ligne.cre_nbre_reech, taux_prov, COALESCE(prev_prov,0) AS prov_mnt, ligne.id_agent_gest, is_credit_decouvert, ligne.cre_mnt_deb, grace_period, periodicitex as periodicite, ligne.id_ag;
  RETURN NEXT ligne_portefeuille;
  FETCH portefeuille INTO ligne;
  END LOOP;
 CLOSE portefeuille;
RETURN;
END;
$BODY$
  LANGUAGE plpgsql VOLATILE;
ALTER FUNCTION getPortfeuilleView(date, integer) OWNER TO adbanking;

-- View compta
DROP VIEW IF EXISTS view_compta;
CREATE VIEW view_compta AS SELECT m.*, e.libel_ecriture, e.date_comptable, e.id_jou, e.id_exo, e.type_operation, e.id_his FROM ad_mouvement m, ad_ecriture e WHERE m.id_ecriture = e.id_ecriture AND m.id_ag = e.id_ag;

-- View balance

DROP TYPE IF EXISTS balance_view CASCADE;
CREATE TYPE balance_view AS (
	num_cpte_comptable   VARCHAR(50),
        libel_cpte_comptable TEXT,
	solde_debut          NUMERIC(30,6),
	som_mvt_debit        NUMERIC(30,6),
	som_mvt_credit       NUMERIC(30,6),
	solde_fin            NUMERIC(30,6),
        devise               CHARACTER(3),
        is_hors_bilan        BOOLEAN,
        is_actif             BOOLEAN,
        date_modif           DATE,
        niveau               INTEGER,
	id_ag	    INTEGER
);
 --DROP FUNCTION getBalanceView(date, date, integer, integer, boolean);
CREATE OR REPLACE FUNCTION getBalanceView(date, date, integer, integer, boolean)
  RETURNS SETOF balance_view AS
$BODY$
DECLARE
  date_deb ALIAS FOR $1;
  date_fin ALIAS FOR $2;
  id_agence ALIAS FOR $3;
  niveau ALIAS FOR $4;
  consolide ALIAS FOR $5;
  ligne_balance balance_view;
  ligne RECORD;
  balance CURSOR FOR SELECT * FROM ad_cpt_comptable WHERE num_cpte_comptable IN ( SELECT compte FROM view_compta) OR solde != 0 ORDER  BY num_cpte_comptable;
  soldeDebut         NUMERIC(30,6);
  somMvtDebit       NUMERIC(30,6);
  somMvtCredit      NUMERIC(30,6);
  soldeFin           NUMERIC(30,6);
  somMvtDebitApDateFin       NUMERIC(30,6);
  somMvtCreditApDateFin      NUMERIC(30,6);
  somMvtDebitApDateDeb      NUMERIC(30,6);
  somMvtCreditApDateDeb     NUMERIC(30,6);
  date_deb_calc		     DATE;
BEGIN
  date_deb_calc := date(date_deb) - interval '1 day';
  OPEN balance ;
  FETCH balance INTO ligne;
  WHILE FOUND LOOP
  -- Récupère les infos du compte
  SELECT INTO somMvtDebit sum(COALESCE(montant,0)) FROM view_compta c  WHERE sens = 'd' AND c.compte = ligne.num_cpte_comptable and c.date_comptable >= date_deb and c.date_comptable <= date_fin ;
  SELECT INTO somMvtCredit sum(COALESCE(montant,0)) FROM view_compta c  WHERE sens = 'c' AND c.compte = ligne.num_cpte_comptable and c.date_comptable >= date_deb and c.date_comptable <= date_fin;
  SELECT INTO somMvtDebitApDateFin sum(COALESCE(montant,0)) FROM view_compta c  WHERE sens = 'd' AND c.compte = ligne.num_cpte_comptable and c.date_comptable > date_fin and c.date_comptable <= date(now());
  SELECT INTO somMvtCreditApDateFin sum(COALESCE(montant,0)) FROM view_compta c  WHERE sens = 'c' AND c.compte = ligne.num_cpte_comptable and c.date_comptable > date_fin and c.date_comptable <= date(now());
 SELECT INTO somMvtDebitApDateDeb sum(COALESCE(montant,0)) FROM view_compta c  WHERE sens = 'd' AND c.compte = ligne.num_cpte_comptable and c.date_comptable > date_deb_calc and c.date_comptable <= date(now());
  SELECT INTO somMvtCreditApDateDeb sum(COALESCE(montant,0)) FROM view_compta c  WHERE sens = 'c' AND c.compte = ligne.num_cpte_comptable and c.date_comptable > date_deb_calc and c.date_comptable <= date(now());
 -- SELECT INTO somMvtDebitDateDebut sum(COALESCE(montant,0)) FROM view_compta c  WHERE sens = 'd' AND c.compte = ligne.num_cpte_comptable and c.date_valeur = date_deb;
 -- SELECT INTO somMvtCreditDateDebut sum(COALESCE(montant,0)) FROM view_compta c  WHERE sens = 'c' AND c.compte = ligne.num_cpte_comptable and c.date_valeur = date_deb;
 soldeFin := COALESCE(ligne.solde,0) - COALESCE(somMvtCreditApDateFin,0) + COALESCE(somMvtDebitApDateFin,0);
 soldeDebut := COALESCE(ligne.solde,0) - COALESCE(somMvtCreditApDateDeb,0) + COALESCE(somMvtDebitApDateDeb,0);
  -- Resultat de la vue
 SELECT INTO ligne_balance ligne.num_cpte_comptable, ligne.libel_cpte_comptable, soldeDebut AS solde_debut, somMvtDebit AS som_mvt_debit, somMvtCredit AS som_mvt_credit, soldeFin AS solde_fin, ligne.devise, ligne.is_hors_bilan, ligne.is_actif, ligne.date_modif, ligne.niveau, ligne.id_ag;
 -- On prend que les comptes qui ont des soldes
 IF ((somMvtDebit != 0) OR (somMvtCredit != 0) OR (soldeDebut != 0) OR (soldeFin != 0)) THEN
  RETURN NEXT ligne_balance;
 END IF;
  FETCH balance INTO ligne;
  END LOOP;
 CLOSE balance;
RETURN;
END;
$BODY$
  LANGUAGE plpgsql VOLATILE;
ALTER FUNCTION getBalanceView(date, date, integer, integer, boolean) OWNER TO adbanking;
-- SELECT * FROM getBalanceView('27/04/2011', '27/04/2011', 1,0,'f');


-- View grandlivre
DROP TYPE IF EXISTS grandlivre_view CASCADE;
CREATE TYPE grandlivre_view AS (
	compte               VARCHAR(50),
        id_ecriture          INTEGER,
        libel_ecriture	     TEXT,
        date_comptable	     DATE,
	type_operation	     INTEGER,
        sens                 CHARACTER(1),
        devise               CHARACTER(3),
        montant		     NUMERIC(30,6),
	id_his		     INTEGER,
	id_client	     INTEGER,
	id_ag	             INTEGER
);
 --DROP FUNCTION getGrandLivreView(date, date, integer);
CREATE OR REPLACE FUNCTION getGrandLivreView(date, date, integer)
  RETURNS SETOF grandlivre_view AS
$BODY$
DECLARE
  date_deb ALIAS FOR $1;
  date_fin ALIAS FOR $2;
  id_agence ALIAS FOR $3;

  ligne_grandlivre grandlivre_view;
  ligne            RECORD;
  grandlivre       REFCURSOR;
  idClient        INTEGER;
BEGIN
  --date_deb_calc := date(date_deb) - interval '1 day';
  OPEN grandlivre FOR SELECT * FROM view_compta WHERE date_valeur >= date_deb AND date_valeur <= date_fin AND id_ag = id_agence;
  FETCH grandlivre INTO ligne;
  WHILE FOUND LOOP
  -- Récupère l'id du client s'il existe
  SELECT INTO idClient id_client FROM ad_his   WHERE id_his = ligne.id_his AND id_ag = id_agence;
  -- Resultat de la vue
 SELECT INTO ligne_grandlivre ligne.compte, ligne.id_ecriture, ligne.libel_ecriture, ligne.date_comptable, ligne.type_operation, ligne.sens, ligne.devise, ligne.montant, ligne.id_his, idClient AS id_client, ligne.id_ag;
  RETURN NEXT ligne_grandlivre;
  FETCH grandlivre INTO ligne;
  END LOOP;
 CLOSE grandlivre;
RETURN;
END;
$BODY$
  LANGUAGE plpgsql VOLATILE;
ALTER FUNCTION getGrandLivreView(date, date, integer) OWNER TO adbanking;

 -------------------------------------------------------------------------------------------
 ---  somme du resulatat provisisoire 
 ---------------------------------------------------------------------------------------------
 
CREATE OR REPLACE FUNCTION bnr_resultat_provisoire( date, integer, boolean)
  RETURNS numeric AS
$$
 DECLARE
 date_bilan ALIAS FOR $1;   
 idagc ALIAS FOR $2;
 is_consolide ALIAS FOR $3;
 ligne RECORD ;
 solde_credit numeric ;
 solde_debit numeric;
 solde numeric;
 Cpt_Calcul_Int CURSOR FOR  SELECT * FROM ad_exercices_compta where id_ag=idagc AND etat_exo != 3 ORDER BY id_exo_compta;
BEGIN
 solde := 0;

  OPEN Cpt_Calcul_Int;
  FETCH Cpt_Calcul_Int INTO ligne;
   WHILE FOUND LOOP
    --id_exo_compta'];
     --    $datedeb = $exo['date_deb_exo'];
   --     $datefin = $exo['date_fin_exo'];


	SELECT into solde_credit  SUM(mv.montant) FROM ad_mouvement mv, ad_ecriture ec, ad_cpt_comptable cpt WHERE 
		mv.id_ag = ec.id_ag AND ec.id_ag = cpt.id_ag AND cpt.id_ag =idagc  AND 
		date(ec.date_comptable) >= date(ligne. date_deb_exo) AND 
	       date( ec.date_comptable) <= date(ligne.date_fin_exo) AND date(ec.date_comptable) <= date(date_bilan) AND
		mv.id_ecriture=ec.id_ecriture    AND mv.sens='c' AND mv.compte=cpt.num_cpte_comptable AND
		 (cpt.compart_cpte=3 OR cpt.compart_cpte=4) ;


SELECT into solde_debit  SUM(mv.montant) FROM ad_mouvement mv, ad_ecriture ec, ad_cpt_comptable cpt WHERE 
		mv.id_ag = ec.id_ag AND ec.id_ag = cpt.id_ag AND cpt.id_ag =idagc  AND 
		date(ec.date_comptable) >= date(ligne. date_deb_exo) AND 
	       date( ec.date_comptable) <= date(ligne.date_fin_exo) AND date(ec.date_comptable) <= date(date_bilan) AND
		mv.id_ecriture=ec.id_ecriture    AND mv.sens='d' AND mv.compte=cpt.num_cpte_comptable AND
		 (cpt.compart_cpte=3 OR cpt.compart_cpte=4) ;
	solde := COALESCE(solde,0) + ( COALESCE(solde_credit,0)- COALESCE(solde_debit,0));
       FETCH Cpt_Calcul_Int INTO ligne;
	END LOOP;
CLOSE Cpt_Calcul_Int;
return  solde;
 END;
$$  LANGUAGE plpgsql VOLATILE;

 
drop type rapport_financier  cascade;
CREATE TYPE rapport_financier AS (
 	 code text,
 	 libel text,
     niveau integer ,
 	 solde_bilan numeric ,
 	 solde_provision numeric,
     solde_bilan_prec numeric,
     solde_provision_prec numeric
 );


CREATE OR REPLACE FUNCTION  poste_bilan_passif (DATE,INTEGER,Boolean,Boolean,Boolean, text ) RETURNS SETOF rapport_financier AS  $$
		DECLARE
	  		date_bilan ALIAS FOR $1; 
	  		idag ALIAS FOR $2; 
			is_conv_devise_ref ALIAS FOR $3; 
			is_consolide ALIAS FOR $4; 
			is_not_solde_anneeprec ALIAS FOR $5; 
			code_resultat_execercice ALIAS FOR $6 ;
	   		resultat rapport_financier;  
        	Cpt_Calcul_Int refcursor;
			ligne record;
	  	 BEGIN
		  	   IF is_not_solde_anneeprec  THEN 
            	create temp table  bilan_passif as 
               	 SELECT  calculesoldebilan( num_cpte_comptable,date( date_bilan),idag,compart_cpte,is_conv_devise_ref,is_consolide) as solde,
               	 0 as solde_anneeprec, num_cpte_comptable
				 from ad_cpt_comptable 
				 where  id_ag = idag  AND 
				 num_cpte_comptable IN (SELECT  distinct b.num_cpte_comptable
								              from ad_poste a LEFT JOIN  (ad_poste_compte b inner join ad_cpt_comptable c on b.num_cpte_comptable = c.num_cpte_comptable)
								              				 on  a.code = b.code  where compartiment in (2,3) AND code_rapport='bilan' AND id_ag = idag);
				ELSE
					create temp table  bilan_passif as 
	               	 SELECT  calculesoldebilan( num_cpte_comptable,date( date_bilan),idag,compart_cpte,is_conv_devise_ref,is_consolide) as solde,
	               	 calculesoldebilan( num_cpte_comptable,getdatefinexerciceprecedent(date(date_bilan),idag) ,idag,compart_cpte, is_conv_devise_ref,is_consolide) as solde_anneeprec,
	               	 num_cpte_comptable
					 from ad_cpt_comptable 
					 where id_ag = idag  AND 
				 		num_cpte_comptable IN (SELECT  distinct b.num_cpte_comptable
									              from ad_poste a LEFT JOIN  (ad_poste_compte b inner join ad_cpt_comptable c on b.num_cpte_comptable = c.num_cpte_comptable)
									              				 on  a.code = b.code  where compartiment in (2,3) AND code_rapport='bilan' AND id_ag = idag) ;
				
				END IF;
                
					OPEN Cpt_Calcul_Int FOR SELECT  a.code, sum(solde) as solde_bilan,sum(solde_anneeprec) as solde_bilan_prec,libel,niveau
										FROM ad_poste a LEFT JOIN  (ad_poste_compte b inner join bilan_passif c
																    on b.num_cpte_comptable = c.num_cpte_comptable) 
														ON  a.code = b.code  
										where   compartiment in (2,3) AND code_rapport='bilan' and (is_cpte_provision= false or is_cpte_provision is null) 
										group by a.id_poste,a.code,libel ,niveau
										order by a.id_poste;
       				 FETCH Cpt_Calcul_Int INTO ligne;
   					WHILE FOUND LOOP
   			
			          IF is_not_solde_anneeprec  THEN 
						IF ligne.code <> code_resultat_execercice THEN
						  SELECT INTO resultat ligne.code,ligne.libel,ligne.niveau,ligne.solde_bilan,0 ;
						ELSE
         				 	SELECT INTO resultat ligne.code,ligne.libel,ligne.niveau, bnr_resultat_provisoire(date_bilan,numagc(),false),0 ;
        				END IF;
        			 ELSE
        			      IF ligne.code <> code_resultat_execercice THEN
						  	SELECT INTO resultat ligne.code,ligne.libel,ligne.niveau,ligne.solde_bilan,0,ligne.solde_bilan_prec;
							ELSE
         				 		SELECT INTO resultat ligne.code,ligne.libel,ligne.niveau, bnr_resultat_provisoire(date_bilan,numagc(),false),0,bnr_resultat_provisoire(date_bilan,numagc(),false);
        					END IF;
        			 END IF;
		  				RETURN NEXT resultat;
        				FETCH Cpt_Calcul_Int INTO ligne;
					END LOOP;
					CLOSE Cpt_Calcul_Int;
					DROP TABLE bilan_passif;
					RETURN ;
 			END;
$$ LANGUAGE 'plpgsql';

CREATE OR REPLACE FUNCTION poste_bilan_actif(date, integer, boolean, boolean, boolean)
  RETURNS SETOF rapport_financier AS
$BODY$
	DECLARE
		date_bilan ALIAS FOR $1; 
		idag ALIAS FOR $2; 
		is_conv_devise_ref ALIAS FOR $3; 
		is_consolide ALIAS FOR $4; 
		is_solde_anneeprec ALIAS FOR $5; 
		resultat rapport_financier;  
		solde_provision numeric;
                solde_provision_prec numeric;
		Cpt_Calcul_Int refcursor;
		ligne record;
	BEGIN
		IF is_solde_anneeprec  THEN 
			create temp table  bilan_actif as 
			SELECT  calculesoldebilan( num_cpte_comptable,date(date_bilan),idag,compart_cpte, is_conv_devise_ref,is_consolide) as solde,
			0 as solde_anneeprec ,num_cpte_comptable
			from ad_cpt_comptable where id_ag = idag AND num_cpte_comptable IN 
					(SELECT  distinct b.num_cpte_comptable
					from ad_poste a LEFT JOIN  
						(ad_poste_compte b inner join ad_cpt_comptable c on 
							b.num_cpte_comptable = c.num_cpte_comptable)
 						on  a.code = b.code 
						where compartiment =1 AND operation = true AND code_rapport = 'bilan' AND id_ag = idag) ;
		ELSE 
			create temp table  bilan_actif as 
			SELECT  calculesoldebilan( num_cpte_comptable,date(date_bilan),idag,compart_cpte, is_conv_devise_ref,is_consolide) as solde,
				calculesoldebilan( num_cpte_comptable,getdatefinexerciceprecedent(date(date_bilan),idag) ,idag,compart_cpte, is_conv_devise_ref,is_consolide) as solde_anneeprec,num_cpte_comptable
			from ad_cpt_comptable where id_ag = idag AND num_cpte_comptable IN 
					(SELECT  distinct b.num_cpte_comptable
					 from ad_poste a LEFT JOIN  (ad_poste_compte b inner join ad_cpt_comptable c on
									b.num_cpte_comptable = c.num_cpte_comptable)
									on  a.code = b.code 
									where compartiment =1 AND operation = true AND code_rapport = 'bilan' AND id_ag = idag) ;
		END IF ;
                
		OPEN Cpt_Calcul_Int FOR SELECT  a.code, sum(solde) as solde_bilan,sum(solde_anneeprec) as solde_bilan_prec,libel,niveau
				FROM ad_poste a LEFT JOIN  (ad_poste_compte b inner join bilan_actif c on b.num_cpte_comptable = c.num_cpte_comptable)
						ON  a.code = b.code 
				where   compartiment =1 AND code_rapport = 'bilan' AND operation = true AND 
					(is_cpte_provision=false OR is_cpte_provision is null)  
				group by a.id_poste,a.code,libel,a.niveau order by a.id_poste;
		FETCH Cpt_Calcul_Int INTO ligne;
		WHILE FOUND LOOP
			IF is_solde_anneeprec  THEN 
				SELECT INTO solde_provision  sum(solde) FROM ad_poste_compte b
					inner join bilan_actif c on b.num_cpte_comptable = c.num_cpte_comptable and is_cpte_provision=true
					and b.code = ligne.code ;
			ELSE
				SELECT INTO solde_provision,solde_provision_prec sum(solde),sum(solde_anneeprec) FROM ad_poste_compte b
					inner join bilan_actif c on b.num_cpte_comptable = c.num_cpte_comptable and is_cpte_provision=true
					and b.code = ligne.code ;
			END IF;
			SELECT INTO resultat ligne.code,ligne.libel,ligne.niveau,-1*ligne.solde_bilan,COALESCE(solde_provision,0),-1*ligne.solde_bilan_prec, COALESCE(solde_provision_prec,0);
			RETURN NEXT resultat;
			FETCH Cpt_Calcul_Int INTO ligne;
		END LOOP;
		CLOSE Cpt_Calcul_Int;
		DROP TABLE bilan_actif;
		RETURN ;

 	END;
 $BODY$
  LANGUAGE plpgsql ;
ALTER FUNCTION poste_bilan_actif(date, integer, boolean, boolean, boolean) OWNER TO adbanking;


	

CREATE OR REPLACE FUNCTION  poste_compte_resultat (DATE,DATE,INTEGER,INTEGER,Boolean,Boolean)
 	RETURNS SETOF rapport_financier AS  $$
DECLARE
	  date_debut ALIAS FOR $1; 
	  date_fin ALIAS FOR $2; 
	  compart ALIAS FOR $3;
	  idag ALIAS FOR $4;
	  is_consolide ALIAS FOR $5; 
	  is_not_solde_anneeprec ALIAS FOR $6; 
	  date_debut_prec date;
	  date_fin_prec date; 
	  date_debut_hier date; 
	  resultat rapport_financier;  
      Cpt_Calcul_Int refcursor;
ligne record;
	  BEGIN
		  date_debut_hier := date(date(date_debut)+ interval '-1 day');
		IF is_not_solde_anneeprec THEN
		create temp table  compte_resulat as 
			SELECT case WHEN compart_cpte = 3 THEN 
					-1*calculesolderesultatcompte( num_cpte_comptable,date_debut_hier, date(date_fin),getdatefinexercice(date(date_debut),date(date_fin)),idag,is_consolide) 
				ELSE 
					calculesolderesultatcompte( num_cpte_comptable,date_debut_hier, date(date_fin),getdatefinexercice(date(date_debut),date(date_fin)),idag,is_consolide)
				END
				as solde, 0 as solde_anneeprec,num_cpte_comptable,compart_cpte
				from ad_cpt_comptable where num_cpte_comptable IN (SELECT  distinct b.num_cpte_comptable
								   from ad_poste a LEFT JOIN  (ad_poste_compte b inner join ad_cpt_comptable c on b.num_cpte_comptable = c.num_cpte_comptable)
 								 on  a.code = b.code  WHERE  c.compart_cpte = compart AND code_rapport ='resultat') ;
		ELSE 
			date_debut_prec := getdatedebutexerciceprecedent(date_fin,idag);
			date_fin_prec := getdatefinexerciceprecedent(date_fin,idag);
		     create temp table  compte_resulat as 
				SELECT case WHEN compart_cpte = 3 THEN 
						-1*calculesolderesultatcompte( num_cpte_comptable,date_debut_hier, date(date_fin),
						getdatefinexercice(date(date_debut),date(date_fin)),idag,is_consolide) 
					ELSE 
						calculesolderesultatcompte( num_cpte_comptable,date_debut_hier, date(date_fin),
						getdatefinexercice(date(date_debut),date(date_fin)),idag,is_consolide)
					END
					as solde, 
					case WHEN compart_cpte = 3 THEN 
						-1*calculesolderesultatcompte( num_cpte_comptable,date_debut_hier, date(date_fin_prec),
						getdatefinexercice(date(date_debut_prec),date(date_fin_prec)),idag,is_consolide) 
					ELSE 
						calculesolderesultatcompte( num_cpte_comptable,date_debut_hier, date(date_fin_prec),
						getdatefinexercice(date(date_debut_prec),date(date_fin_prec)),idag,is_consolide)
					END as solde_anneeprec,num_cpte_comptable,compart_cpte
				from ad_cpt_comptable where num_cpte_comptable IN (SELECT  distinct b.num_cpte_comptable
								   from ad_poste a LEFT JOIN  (ad_poste_compte b inner join ad_cpt_comptable c on b.num_cpte_comptable = c.num_cpte_comptable)
 								 on  a.code = b.code  WHERE  c.compart_cpte = compart AND code_rapport ='resultat') ;
		END IF;
                
		OPEN Cpt_Calcul_Int FOR SELECT  a.code, sum(case WHEN signe = '+' THEN solde ELSE -1*solde END ) as solde_bilan,sum(case WHEN signe = '+' THEN solde_anneeprec ELSE -1*solde_anneeprec END ) as solde_bilan_prec,libel,niveau
		FROM ad_poste a LEFT JOIN  (ad_poste_compte b inner join compte_resulat c on b.num_cpte_comptable = c.num_cpte_comptable)
                	ON  a.code = b.code  where code_rapport ='resultat' group by a.id_poste,a.code,libel,niveau order by a.id_poste;
        FETCH Cpt_Calcul_Int INTO ligne;
   	WHILE FOUND LOOP
	  SELECT INTO resultat ligne.code,ligne.libel,ligne.niveau,ligne.solde_bilan,0,ligne.solde_bilan_prec ;
			RETURN NEXT resultat;
        FETCH Cpt_Calcul_Int INTO ligne;
	END LOOP;
CLOSE Cpt_Calcul_Int;
DROP TABLE compte_resulat;
RETURN ;

 	END;
 	$$ LANGUAGE 'plpgsql';



DROP TYPE IF EXISTS epargne_view_type CASCADE;
CREATE TYPE epargne_view_type AS (
	
	id_client INTEGER,
        id_cpte INTEGER ,	
        id_prod INTEGER,
        devise character(3),
	date_ouverture date,
	etat_cpte INTEGER,
        nom_cli TEXT,
       	solde NUMERIC(30,6),
	id_ag INTEGER ,
	num_complet_cpte varchar(50),
	libel varchar(60),
	classe_comptable INTEGER
);

CREATE OR REPLACE FUNCTION epargne_view ( date, INTEGER,INTEGER,INTEGER,INTEGER )
  RETURNS SETOF  epargne_view_type AS
$BODY$
DECLARE
	--id_cpte_u ALIAS FOR $1;
	date_epargne ALIAS FOR $1;
	idag ALIAS FOR $2;
	v_id_prod ALIAS FOR $3;
        v_limit ALIAS FOR $4;
	v_offset  ALIAS FOR $5;
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
			where (b.classe_comptable IN (1,2,3,5,6))  and  
			date(date_ouvert)<= date(date_epargne) and  a.id_ag =idag and
			( etat_cpte <> 2 OR (etat_cpte = 2 and date(date_clot) >date(date_epargne))) order by id_titulaire,id_cpte limit limites OFFSET  offsets;
	ELSE 
		CREATE TEMP TABLE  temp_ad_cpt as 
			SELECT  date_ouvert, solde,solde_clot,id_titulaire,id_cpte,id_prod,a.devise,etat_cpte,a.id_ag ,date_clot,num_complet_cpte,b.libel, b.classe_comptable
			from ad_cpt a left join adsys_produit_epargne b on ( a.id_ag=b.id_ag and a.id_prod =b.id)
			where (b.classe_comptable IN (1,2,3,5,6))  and  
			date(date_ouvert)<= date(date_epargne) and  a.id_ag =idag AND id_prod = v_id_prod AND
			( etat_cpte <> 2 OR (etat_cpte = 2 and date(date_clot) >date(date_epargne)))  order by id_titulaire,id_cpte limit limites OFFSET  offsets;
	END IF;
         
	-- RAISE NOTICE '%', solde_actuel ;
	IF  DATE(date_epargne) >=  DATE(now()) THEN
		OPEN cur_epargne FOR SELECT a.*,  0 as solde_after_date_ep FROM temp_ad_cpt a order  by id_titulaire,id_cpte;
	
	ELSE
               
               CREATE TEMP TABLE    solde_after_date_epargne as SELECT a.id_cpte,  sum( CASE  when sens ='c' THEN montant WHEN sens ='d' THEN -1*montant END ) as solde_after_date_ep 
		from temp_ad_cpt a left join  (ad_mouvement b inner join ad_ecriture c on (b.id_ecriture=c.id_ecriture) ) on (a.id_cpte =b.cpte_interne_cli ) 
		where  date(date_comptable) > date(date_epargne) group by a.id_cpte;
		
	      
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
LANGUAGE plpgsql ;
  
  
 CREATE OR REPLACE FUNCTION garantieCreditSoldeTroisMois (date ) RETURNS numeric AS $$
 SELECT sum(solde) 
from ad_gar a , ad_cpt b  
where a.id_doss in( SELECT  a.id_doss  
			from ad_dcr b  inner  join ad_etr a  on (a.id_doss=b.id_doss and a.id_ag=b.id_ag)
			where date_ech = (select max(date_ech) 
						from ad_etr 
						where id_doss =a.id_doss
						having  max(date_ech)<=date ( now() + interval '3 month') and 
						max(date_ech)>=date($1))
			 and remb =false AND etat in (5,7,13)) AND

(b.id_cpte=a.gar_num_id_cpte_nantie OR gar_num_id_cpte_nantie is null) and a.id_ag=b.id_ag and  type_gar=1 and etat_gar=3 ;
$$ LANGUAGE SQL;
