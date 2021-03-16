-- Function: compare_compta_cpte_interne_credit(date)

-- DROP FUNCTION compare_compta_cpte_interne_credit(date);

CREATE OR REPLACE FUNCTION compare_compta_cpte_interne_credit(date)
  RETURNS SETOF type_cpte_dcr AS
$BODY$
DECLARE
	date_batch ALIAS FOR $1; 
	ligne type_cpte_dcr;
BEGIN 
	DROP TABLE IF EXISTS solde_compta;
	DROP TABLE IF EXISTS solde_cpt;
	CREATE TEMP TABLE solde_compta AS
	select  cpte_interne_cli,sum(CASE WHEN sens = 'c' THEN -montant ELSE montant END) AS solde_compta 
	FROM ad_mouvement m 
	WHERE (m.compte IN (SELECT DISTINCT num_cpte_comptable FROM adsys_etat_credit_cptes WHERE  id_etat_credit != (select id from adsys_etat_credits where nbre_jours = -1)) ) 
	AND date_valeur <= DATE(date_batch)
	GROUP BY cpte_interne_cli;
	CREATE TEMP TABLE solde_cpt AS
	select  cpte_interne_cli,sum(CASE WHEN sens = 'c' THEN -montant ELSE montant END) AS solde_cpt 
	from ad_mouvement
	 where cpte_interne_cli IN ( select cre_id_cpte from  ad_dcr d where d.cre_date_debloc <= DATE(date_batch) AND (etat IN (5,7,8,13,14,15) OR (etat IN (6,9,12) AND date_etat > DATE(date_batch)))) 
	AND date_valeur <= DATE( date_batch)
	GROUP BY cpte_interne_cli;
	
	FOR ligne IN select NULL,(select id_doss from ad_dcr where cre_id_cpte = b.cpte_interne_cli),(select num_complet_cpte from ad_cpt where id_cpte = b.cpte_interne_cli ),solde_compta,solde_cpt
		from solde_compta a, solde_cpt b
		 where  a.cpte_interne_cli = b.cpte_interne_cli and a.solde_compta != b.solde_cpt 
	LOOP
		RETURN NEXT ligne;
	END LOOP;
	RETURN ;

END;
$BODY$
  LANGUAGE plpgsql VOLATILE
  COST 100
  ROWS 1000;
ALTER FUNCTION compare_compta_cpte_interne_credit(date)
  OWNER TO postgres;

  
-- Function: compare_credit_cpte_interne(date)

-- DROP FUNCTION compare_credit_cpte_interne(date);

CREATE OR REPLACE FUNCTION compare_credit_cpte_interne(date)
  RETURNS SETOF type_cpte_dcr AS
$BODY$
DECLARE
	date_batch ALIAS FOR $1; 
	ligne type_cpte_dcr;
BEGIN 
	DROP TABLE IF EXISTS solde_cpt;
	DROP TABLE IF EXISTS mnt_etr;
	DROP TABLE IF EXISTS mnt_sre;
	CREATE TEMP TABLE solde_cpt AS
	select  cpte_interne_cli,sum(CASE WHEN sens = 'c' THEN -montant ELSE montant END) AS solde_cpt 
	from ad_mouvement where cpte_interne_cli IN
	 ( select cre_id_cpte from  ad_dcr d where d.cre_date_debloc <= DATE(date_batch) AND (etat IN (5,7,8,13,14,15) OR (etat IN (6,9) AND date_etat > DATE(date_batch))))
	 AND date_valeur <= DATE(date_batch)
	GROUP BY cpte_interne_cli;

	CREATE TEMP TABLE mnt_etr AS
	select  d.cre_id_cpte,d.id_doss,d.id_client, sum(mnt_cap) AS mnt_att from ad_etr e, ad_dcr d where e.id_doss = d.id_doss and d.cre_date_debloc <= DATE(date_batch) AND (etat IN (5,7,8,13,14,15) OR (etat IN (6,9) AND date_etat > DATE(date_batch)))
	GROUP BY d.cre_id_cpte,d.id_doss,d.id_client;
	
	CREATE TEMP TABLE mnt_sre AS
	select  d.cre_id_cpte, sum(mnt_remb_cap) AS mnt_remb from ad_sre e, ad_dcr d where e.id_doss = d.id_doss and e.date_remb <= DATE(date_batch) and d.cre_date_debloc <= DATE(date_batch) AND (etat IN (5,7,8,13,14,15) OR (etat IN (6,9) AND date_etat > DATE(date_batch)))
	GROUP BY d.cre_id_cpte;

	FOR ligne IN select e.id_client,e.id_doss,(select num_complet_cpte from ad_cpt where id_cpte = c.cpte_interne_cli) , c.solde_cpt, (e.mnt_att-s.mnt_remb) as solde_cap_ech 
	 from solde_cpt c, mnt_etr e, mnt_sre s
		where c.cpte_interne_cli = e.cre_id_cpte and e.cre_id_cpte = s.cre_id_cpte and c.solde_cpt != e.mnt_att-s.mnt_remb
	LOOP
		RETURN NEXT ligne;
	END LOOP;
	RETURN ;

END;
$BODY$
  LANGUAGE plpgsql VOLATILE
  COST 100
  ROWS 1000;
ALTER FUNCTION compare_credit_cpte_interne(date)
  OWNER TO postgres;

  
-- Function: garantiecreditsoldetroismois(date)

-- DROP FUNCTION garantiecreditsoldetroismois(date);

CREATE OR REPLACE FUNCTION garantiecreditsoldetroismois(date)
  RETURNS numeric AS
$BODY$
 SELECT sum(solde) 
from ad_gar a , ad_cpt b  
where a.id_doss in( SELECT  a.id_doss  
			from ad_dcr b  inner  join ad_etr a  on (a.id_doss=b.id_doss and a.id_ag=b.id_ag)
			where date_ech = (select max(date_ech) 
						from ad_etr 
						where id_doss =a.id_doss
						having  max(date_ech)<=date ( now() + interval '3 month') and 
						max(date_ech)>=date($1))
			 and remb =false AND etat in (5,7,13,14,15)) AND

(b.id_cpte=a.gar_num_id_cpte_nantie OR gar_num_id_cpte_nantie is null) and a.id_ag=b.id_ag and  type_gar=1 and etat_gar=3 ;
$BODY$
  LANGUAGE sql VOLATILE
  COST 100;
ALTER FUNCTION garantiecreditsoldetroismois(date)
  OWNER TO postgres;

  
-- Function: get_rpt_ecart_compta(date, text)

-- DROP FUNCTION get_rpt_ecart_compta(date, text);

CREATE OR REPLACE FUNCTION get_rpt_ecart_compta(IN date, IN text)
  RETURNS TABLE(date_ecart timestamp without time zone, numero_compte_comptable text, libel_cpte_comptable text, devise character, solde_cpte_int numeric, solde_cpte_comptable numeric, ecart numeric, login text, id_his integer, id_doss integer, cre_etat integer, solde_credit numeric, solde_cpt numeric, ecart_credits numeric) AS
$BODY$
select ec.date_ecart,
coalesce(ec.num_cpte_comptable, ed.num_cpte_comptable) as numero_compte_comptable,
ec.libel_cpte_comptable,
ec.devise,
ec.solde_cpte_int,
ec.solde_cpte_comptable,
ec.ecart,
ec.login,
ec.id_his,
ed.id_doss,
ed.cre_etat,
ed.solde_credit,
ed.solde_cpt,
ed.ecart_credits

from
(
select
date_ecart,
num_cpte_comptable,
libel_cpte_comptable,
devise,
solde_cpte_int,
solde_cpte_comptable,
ecart,
login,
id_his
from ad_ecart_compta aec
where id = (select max(id) from ad_ecart_compta where num_cpte_comptable = aec.num_cpte_comptable)
)
ec 
FULL JOIN (
		select A.*, solde_credit+solde_cpt as ecart_credits from
		(
		SELECT c.num_cpte_comptable, a.id_doss, a.cre_etat, round(sum(solde_cap)) as solde_credit, round(solde) as solde_cpt 
		from ad_dcr a, ad_etr b, ad_cpt c where a.id_doss = b.id_doss and a.cre_id_cpte = c.id_cpte and etat in( 5,6,7,14,15 ) 
		group by c.num_cpte_comptable, a.id_doss,a.cre_etat, c.solde 
		)A
		where solde_credit <> -solde_cpt 
		) ed
		on ec.num_cpte_comptable = ed.num_cpte_comptable

where ec.date_ecart::date <= coalesce($1, to_date('20991231','YYYYMMDD'))  and coalesce(ec.num_cpte_comptable, ed.num_cpte_comptable) = 
case when $2 is null then coalesce(ec.num_cpte_comptable, ed.num_cpte_comptable) else $2 end

		order by date_ecart desc, coalesce(ec.num_cpte_comptable, ed.num_cpte_comptable) asc

$BODY$
  LANGUAGE sql VOLATILE
  COST 100
  ROWS 1000;
ALTER FUNCTION get_rpt_ecart_compta(date, text)
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
  portefeuille CURSOR FOR SELECT id_doss,id_client,id_ag,cre_mnt_octr,cre_date_debloc,duree_mois, etat, cre_id_cpte, calculnombrejoursretardoss(id_doss, date(date_export), id_agence) AS nbr_jours_retard, 
                            (case WHEN date(date_export) = date(now()) THEN cre_etat ELSE CalculEtatCredit(id_doss, date(date_export), id_agence) END ) AS cre_etat,
      						cre_etat AS cre_etat_cur, date_etat, cre_date_etat, cre_nbre_reech, perte_capital, id_agent_gest, id_prod, obj_dem, id_ag 
      					  FROM ad_dcr WHERE cre_date_debloc <= date(date_export) AND ((etat IN (5,7,8,13,14,15)) OR (etat IN (6,11,12) AND date_etat > date(date_export))) AND id_ag=id_agence ORDER BY id_doss;
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
  SELECT INTO prev_prov COALESCE(montant,0) FROM ad_provision WHERE id_doss = ligne.id_doss AND id_ag = id_agence AND date_prov = (SELECT MAX(date_prov) 
   FROM ad_provision WHERE date_prov < date_export AND id_doss = ligne.id_doss AND id_ag = id_agence);
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
  SELECT INTO ligne_portefeuille ligne.id_doss, ligne.id_client, ligne.id_prod, ligne.obj_dem, (mnt_cap_att) AS cre_mnt_octr, devise_credit AS devise, ligne.cre_id_cpte, ligne.cre_date_debloc, ligne.date_etat AS date_etat_doss, ligne.duree_mois, id_etat_credit, ligne.cre_date_etat, credit_en_perte, ligne.perte_capital, nom_client AS nom_cli, nbr_ech_total,(nbr_ech_total - nbr_ech_impaye) AS nbr_ech_paye, mnt_cred_paye, mnt_int_att, mnt_int_paye, mnt_gar_att, mnt_gar_paye, mnt_pen_att, mnt_pen_paye, COALESCE(mnt_gar_mob,0), solde_retard, int_retard, gar_retard, pen_retard, date_echeance, ligne.nbr_jours_retard, nbre_ech_retard, etat_credit, ligne.cre_nbre_reech, taux_prov, COALESCE(prev_prov,0) AS prov_mnt, ligne.id_agent_gest, is_credit_decouvert, ligne.id_ag;
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


CREATE OR REPLACE FUNCTION add_field_adsys_produit_credit() RETURNS VOID AS
$BODY$
DECLARE

BEGIN

	IF NOT EXISTS(SELECT * FROM information_schema.columns WHERE table_name = 'adsys_produit_credit' AND column_name = 'is_produit_decouvert') THEN
		ALTER TABLE adsys_produit_credit ADD COLUMN is_produit_decouvert boolean DEFAULT false;
	END IF;

END;

$BODY$
LANGUAGE plpgsql;

SELECT add_field_adsys_produit_credit();
DROP FUNCTION add_field_adsys_produit_credit();
  
