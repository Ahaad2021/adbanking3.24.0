-- Function: getdatarapport(integer, integer, date, date)

-- DROP FUNCTION getdatarapport(integer, integer, date, date);

CREATE OR REPLACE FUNCTION getdatarapport(
    integer,
    integer,
    date,
    date)
  RETURNS SETOF data_rapport AS
$BODY$
  DECLARE

 in_id_annee ALIAS FOR $1;
 in_id_saison ALIAS FOR $2; -- Date de calcul iar
 in_date_debut ALIAS FOR $3; -- Numero dossier de credits
 in_date_fin ALIAS FOR $4;  -- id agence

v_nom_coopec text;
v_libel_province text;
v_libel_commune text;
 v_id_zone int;
 v_libel_zone text;
 v_id_colline int;
 v_libel_colline text;
 v_id_benef int;
 v_Id_card text;
 v_id_benef_verif int;
 v_nom_prenom text;
 v_qty integer :=0;
 v_qty_paye integer :=0;
 v_montant_paye numeric(30,6) :=0;
 v_montant_paye1 numeric(30,6) :=0;
 v_montant_avance numeric(30,6) :=0;
 v_montant_avance1 numeric(30,6) :=0;
 v_montant_solde numeric(30,6) :=0;
 v_montant_solde1 numeric(30,6) :=0;
 id_ben_temp integer :=0 ;
v_saison_actif integer :=0;

 ligne record;
 ligne1 record;

 cur_idBenef refcursor;
 cur_idProduit refcursor;

 ligne_data data_rapport;

 BEGIN

SELECT INTO v_saison_actif id_saison FROM ec_saison_culturale where etat_saison = 1;
 --IF (in_id_saison = 0) THEN
 OPEN cur_idBenef FOR select distinct id_benef from commande; --where id_saison IN (SELECT id_saison from ec_saison_culturale where id_annee = in_id_annee); --and date_creation >= date('in_date_debut')  and date_creation <= date('in_date_fin');
 --//END IF;
 /*IF (in_id_saison = 1) THEN
 OPEN cur_idBenef FOR select distinct id_benef from commande;-- where id_saison = in_id_saison; --and date_creation >= date('in_date_debut')  and date_creation <= date('in_date_fin');
 END IF;
 IF (in_id_saison = 2) THEN
 OPEN cur_idBenef FOR select distinct id_benef from commande;-- where id_saison IN (in_id_saison); --and date_creation >= date('in_date_debut')  and date_creation <= date('in_date_fin');
 END IF;*/





 FETCH cur_idBenef INTO ligne;
 WHILE FOUND LOOP
  IF id_ben_temp != ligne.id_benef then
  id_ben_temp = ligne.id_benef;
  v_montant_paye1 :=0;
  v_montant_avance1 :=0;
  v_montant_solde1 :=0;

   RAISE NOTICE 'Id_benef = >  %',ligne.id_benef;
  /*
IF (in_id_saison = 0) THEN
   OPEN cur_idProduit FOR SELECT distinct d.id_produit, p.libel FROM ec_commande_detail d  RIGHT JOIN ec_produit p ON p.id_produit = d.id_produit INNER JOIN ec_commande c ON c.id_commande = d.id_commande WHERE c.id_saison IN (SELECT id_saison from ec_saison_culturale where id_annee = in_id_annee) ORDER BY d.id_produit ASC; --WHERE c.id_saison IN (SELECT id_saison from ec_saison_culturale where id_annee = in_id_annee)
   END IF;
   IF (in_id_saison <> 0) THEN
   OPEN cur_idProduit FOR SELECT distinct d.id_produit, p.libel FROM ec_commande_detail d  RIGHT JOIN ec_produit p ON p.id_produit = d.id_produit INNER JOIN ec_commande c ON c.id_commande = d.id_commande WHERE c.id_saison = in_id_saison ORDER BY d.id_produit ASC;
   END IF;*/

   --OPEN cur_idProduit FOR SELECT id_produit, libel FROM ec_produit ORDER BY id_produit;
   	IF (in_id_saison = v_saison_actif) THEN
	OPEN cur_idProduit FOR select id_produit,libel from ec_produit where etat_produit = 1;
	ELSE
	OPEN cur_idProduit FOR SELECT p.libel as libel, h.id_produit as id_produit FROM ec_produit_hist h INNER JOIN ec_produit p on p.id_produit = h.id_produit and h.id_saison = in_id_saison order by h.id_produit;
	END IF;

   FETCH cur_idProduit INTO ligne1;
   WHILE FOUND LOOP
   v_montant_paye = 0;
v_montant_avance =0 ;
v_montant_solde =0;

    --IF (in_id_saison = 0) THEN
    select into v_id_benef_verif,v_qty,v_qty_paye, v_montant_paye1, v_montant_avance1, v_montant_solde1 id_benef, sum(qty) as qty,sum(qty_paye) as qty_paye, (sum(montant_avance)+sum(montant_solde)) as montant_total, sum(montant_avance) as montant_avance, sum(montant_solde) as montant_solde from commande where id_produit = ligne1.id_produit and id_benef = id_ben_temp GROUP BY id_benef ORDER BY id_benef; -- and id_saison IN (SELECT id_saison from ec_saison_culturale where id_annee = in_id_annee);
    /*END IF;
    IF (in_id_saison = 1) THEN
    select into v_id_benef_verif ,v_qty, v_montant_paye1, v_montant_avance1, v_montant_solde1 id_benef, qty, (montant_avance+montant_solde), montant_avance, montant_solde from commande where id_produit = ligne1.id_produit and id_benef = id_ben_temp;-- and id_saison = in_id_saison;
    END IF;
    IF (in_id_saison = 2) THEN
    select into v_id_benef_verif ,v_qty, v_montant_paye1, v_montant_avance1, v_montant_solde1 id_benef, qty, (montant_avance+montant_solde), montant_avance, montant_solde from commande where id_produit = ligne1.id_produit and id_benef = id_ben_temp;-- and id_saison IN (1,in_id_saison);
    END IF;*/

select into v_nom_coopec libel_ag from ad_agc where id_ag = numagc();
select into v_libel_province l.libel from ec_localisation l INNER JOIN ec_beneficiaire b on b.id_province = l.id where b.id_beneficiaire = id_ben_temp;
select into v_libel_commune l.libel from ec_localisation l INNER JOIN ec_beneficiaire b on b.id_commune = l.id where b.id_beneficiaire = id_ben_temp;
select into v_id_card nic from ec_beneficiaire where id_beneficiaire = id_ben_temp;

     select into v_libel_zone l.libel from ec_localisation l INNER JOIN ec_beneficiaire b on b.id_zone = l.id where b.id_beneficiaire = id_ben_temp;

     select into v_libel_colline l.libel from ec_localisation l INNER JOIN ec_beneficiaire b on b.id_colline = l.id where b.id_beneficiaire = id_ben_temp;

     select into v_nom_prenom nom_prenom from ec_beneficiaire where id_beneficiaire = id_ben_temp;

     IF (v_qty IS NULL) THEN
       v_qty = 0;
     END IF;

	IF (v_qty_paye IS NULL) THEN
       v_qty_paye = 0;
     END IF;

     IF (v_montant_paye1 IS NOT NULL) THEN
       v_montant_paye = v_montant_paye1;
     END IF;

     IF (v_montant_avance1 IS NOT NULL) THEN
       v_montant_avance = v_montant_avance1;
     END IF;

     IF (v_montant_solde1 IS NOT NULL) THEN
       v_montant_solde = v_montant_solde1;
     END IF;

     SELECT INTO ligne_data v_libel_province,v_libel_commune,v_libel_zone, v_libel_colline,v_nom_coopec, id_ben_temp, v_nom_prenom,v_id_card, ligne1.id_produit, ligne1.libel, v_qty,v_qty_paye, coalesce(v_montant_paye,0), coalesce(v_montant_avance,0), coalesce(v_montant_solde,0);
     RETURN NEXT ligne_data;

     RAISE NOTICE 'zone=> %, -- le nom de la colline => % -- ID du benef => % -- Nom Prenom Benef => %, --  quantite=>% -- montant_depot=> %', v_libel_zone, v_libel_colline, id_ben_temp,v_nom_prenom,v_qty,v_montant_paye;

    --END IF;

   FETCH cur_idProduit INTO ligne1;
   END LOOP;
   CLOSE cur_idProduit;

  END IF;
 FETCH cur_idBenef INTO ligne;
 END LOOP;
 CLOSE cur_idBenef;
 END;
 $BODY$
  LANGUAGE plpgsql VOLATILE
  COST 100
  ROWS 1000;
ALTER FUNCTION getdatarapport(integer, integer, date, date)
  OWNER TO postgres;




  -- Function: getdatarapportproduit(date, date, integer, integer, integer)

-- DROP FUNCTION getdatarapportproduit(date, date, integer, integer, integer);

CREATE OR REPLACE FUNCTION getdatarapportproduit(
    date,
    date,
    integer,
    integer,
    integer)
  RETURNS SETOF data_produit AS
$BODY$
 DECLARE

in_date_debut ALIAS FOR $1;
in_date_fin ALIAS FOR $2;
in_id_annee ALIAS FOR $3;
in_id_saison ALIAS FOR $4;
in_period ALIAS FOR $5; -- 1: avance - 2: solde

v_nom_province text;
v_nom_commune text;
v_libel_ag text;
v_id_prod integer;
v_libel_prod text;
v_qtite integer :=0;
v_qtite_paye integer :=0;
v_mnt_avance numeric(30,6) :=0;
v_mnt_solde numeric(30,6):= 0;
v_nbre_agri integer;
v_date_deb_avance date;
V_date_fin_avance date;

cur_produit refcursor;
cur_loc refcursor;


ligne record;
ligne1 record;

ligne_data data_produit;

BEGIN

IF (in_period = 1) THEN
	SELECT into v_libel_ag libel_ag from ad_agc where id_ag = numagc();


	OPEN cur_loc FOR select distinct loc1.libel as nom_province, loc.libel as nom_commune,id_province, id_commune from ec_beneficiaire  b
	INNER JOIN ec_commande c on c.id_benef = b.id_beneficiaire
	INNER JOIN ec_localisation loc on loc.id = b.id_commune
	INNER JOIN ec_localisation loc1 on loc1.id = b.id_province
	where c.etat_commande not in (7,5,6);
	FETCH cur_loc INTO ligne1;
	WHILE FOUND LOOP

	IF EXISTS (SELECT id_saison FROM ec_saison_culturale WHERE id_saison = in_id_saison AND etat_saison = 1) THEN --Saison actuelle
		OPEN cur_produit FOR select id_produit, libel from ec_produit where etat_produit = 1 order by id_produit;
	END IF;

	IF EXISTS (SELECT id_saison FROM ec_saison_culturale WHERE id_saison = in_id_saison AND etat_saison = 2) THEN --Saison anterieure
		OPEN cur_produit FOR select e.id_produit, e.libel from ec_produit e inner join ec_produit_hist eh on e.id_produit = eh.id_produit where eh.id_saison = in_id_saison order by e.id_produit;
	END IF;

	--OPEN cur_produit FOR select id_produit, libel from ec_produit  order by id_produit;
	FETCH cur_produit INTO ligne;
	WHILE FOUND LOOP

		select into v_nom_province,v_nom_commune, v_id_prod, v_libel_prod, v_qtite, v_mnt_avance  loc1.libel as nom_province, loc.libel as nom_commune,d.id_produit,pd.libel,sum(d.quantite) as qtite, sum(d.montant_depose) as mnt_avance
from ec_commande_detail d
		INNER JOIN ec_commande c on c.id_commande=d.id_commande
		INNER JOIN ec_produit pd on pd.id_produit = d.id_produit
		INNER JOIN ec_beneficiaire b on b.id_beneficiaire = c.id_benef
		INNER JOIN ec_localisation loc on loc.id = b.id_commune
		INNER JOIN ec_localisation loc1 on loc1.id = b.id_province
		INNER JOIN ad_agc ag on ag.id_ag = c.id_ag
		where id_saison = in_id_saison
		and etat_commande not in (7,5,6)
		and d.date_creation >= date(in_date_debut)  and d.date_creation <= date(in_date_fin)
		and d.id_produit = ligne.id_produit
		and  loc1.id = ligne1.id_province and loc.id = ligne1.id_commune
		group by nom_province,nom_commune,d.id_produit,pd.libel
		order by id_produit;

		IF (v_qtite IS null) THEN
		v_qtite = 0;
		v_mnt_avance =0;
		v_qtite_paye =0;
		END IF;

		RAISE NOTICE 'libel => % , qtite => %', v_libel_prod,v_qtite ;
		SELECT INTO ligne_data ligne1.nom_province,ligne1.nom_commune,v_libel_ag, ligne.id_produit,ligne.libel, v_qtite,v_qtite_paye, v_mnt_avance,v_mnt_solde,in_id_annee, in_id_saison,in_period;
		RETURN NEXT ligne_data;

	FETCH cur_produit INTO ligne;
	END LOOP;
	CLOSE cur_produit;

	FETCH cur_loc INTO ligne1;
	END LOOP;
	CLOSE cur_loc;

END IF;

IF (in_period = 2) THEN
	SELECT into v_libel_ag libel_ag from ad_agc where id_ag = numagc();

	SELECT INTO v_date_deb_avance, v_date_fin_avance date_debut,date_fin_avance from ec_saison_culturale where id_saison = in_id_saison;
	RAISE NOTICE 'date_debut => %   <=> date fin =>%',v_date_deb_avance,v_date_fin_avance;
	OPEN cur_loc FOR select distinct loc1.libel as nom_province, loc.libel as nom_commune,id_province, id_commune from ec_beneficiaire  b
	INNER JOIN ec_commande c on c.id_benef = b.id_beneficiaire
	INNER JOIN ec_localisation loc on loc.id = b.id_commune
	INNER JOIN ec_localisation loc1 on loc1.id = b.id_province
	where c.etat_commande not in (7,5,6);
	FETCH cur_loc INTO ligne1;
	WHILE FOUND LOOP

	--OPEN cur_produit FOR select id_produit, libel from ec_produit where etat_produit =1 order by id_produit;
	IF EXISTS (SELECT id_saison FROM ec_saison_culturale WHERE id_saison = in_id_saison AND etat_saison = 1) THEN --Saison actuelle
		OPEN cur_produit FOR select id_produit, libel from ec_produit where etat_produit = 1 order by id_produit;
	END IF;

	IF EXISTS (SELECT id_saison FROM ec_saison_culturale WHERE id_saison = in_id_saison AND etat_saison = 2) THEN --Saison anterieure
		OPEN cur_produit FOR select e.id_produit, e.libel from ec_produit e inner join ec_produit_hist eh on e.id_produit = eh.id_produit where eh.id_saison = in_id_saison order by e.id_produit;
	END IF;
	FETCH cur_produit INTO ligne;
	WHILE FOUND LOOP

		select into v_nom_province,v_nom_commune, v_id_prod, v_libel_prod, v_qtite, v_mnt_avance  loc1.libel as nom_province, loc.libel as nom_commune,d.id_produit,pd.libel,sum(d.quantite) as qtite, sum(d.montant_depose) as mnt_avance
		from ec_commande_detail d
		INNER JOIN ec_commande c on c.id_commande=d.id_commande
		INNER JOIN ec_produit pd on pd.id_produit = d.id_produit
		INNER JOIN ec_beneficiaire b on b.id_beneficiaire = c.id_benef
		INNER JOIN ad_agc ag on ag.id_ag = c.id_ag
		INNER JOIN ec_localisation loc on loc.id = b.id_commune
		INNER JOIN ec_localisation loc1 on loc1.id = b.id_province
		INNER JOIN ec_saison_culturale l on l.id_saison = c.id_saison
		INNER JOIN ec_annee_agricole a on a.id_annee = l.id_annee
		--where id_saison <= in_id_saison
		where a.id_annee = in_id_annee
		and etat_commande in (3,8)
		and d.date_creation >= date(v_date_deb_avance)  and d.date_creation <= date(v_date_fin_avance)
		and d.id_produit = ligne.id_produit
		and  loc1.id = ligne1.id_province and loc.id = ligne1.id_commune
		group by nom_province,nom_commune,d.id_produit,pd.libel
		order by id_produit;

		SELECT into v_qtite_paye, v_mnt_solde sum(qtite_paye) as qtite_paye,sum(montant_paye) as mnt_solde_paye from ec_paiement_commande p
		INNER JOIN ec_commande_detail d on p.id_detail_commande = d.id_detail
		INNER JOIN ec_commande c on c.id_commande = d.id_commande
		INNER JOIN ec_beneficiaire b on  b.id_beneficiaire = c.id_benef
		INNER JOIN ec_localisation loc on loc.id = b.id_commune
		INNER JOIN ec_localisation loc1 on loc1.id = b.id_province
		where p.date_creation >=  date(in_date_debut)  and p.date_creation <= date(in_date_fin)
		--and c.id_saison = in_id_saison
		and d.id_produit = ligne.id_produit
		and loc1.id = ligne1.id_province and loc.id = ligne1.id_commune
		AND p.etat_paye = 2;
		RAISE NOTICE 'Montan solde = % <==> Montant avance = %',v_mnt_solde,v_mnt_avance ;

		IF (v_mnt_solde IS NULL)  THEN
		v_mnt_solde = 0;
		ELSE
		v_mnt_solde = v_mnt_solde;
		END IF;
RAISE NOTICE 'Montan solde2 = %',v_mnt_solde;
		IF (v_qtite IS NULL) THEN
		v_qtite = 0;
		--v_qtite_paye =0;
		END IF;
		IF (v_qtite_paye IS NULL) THEN
		v_qtite_paye =0;
		END IF;


		IF (v_mnt_avance IS NULL) THEN
		v_mnt_avance = 0;
		END IF;
		IF (v_mnt_solde IS NULL) THEN
		v_mnt_solde = 0;
		END IF;

		RAISE NOTICE 'province=> % / commune=> % /libel=> % /idprod=> % /libel_prod=> % / qtite=> % / qtite_paye=> % / mnt_avance=> % / mnt_solde=> %  ',ligne1.nom_province,ligne1.nom_commune,v_libel_ag,ligne.id_produit,ligne.libel,v_qtite,v_qtite_paye, v_mnt_avance,v_mnt_solde;
		SELECT INTO ligne_data ligne1.nom_province,ligne1.nom_commune,v_libel_ag,ligne.id_produit,ligne.libel,v_qtite,v_qtite_paye, v_mnt_avance,v_mnt_solde,in_id_annee, in_id_saison,in_period;
		RETURN NEXT ligne_data;


	FETCH cur_produit INTO ligne;
	END LOOP;
	CLOSE cur_produit;

	FETCH cur_loc INTO ligne1;
	END LOOP;
	CLOSE cur_loc;

END IF;




END;
$BODY$
  LANGUAGE plpgsql VOLATILE
  COST 100
  ROWS 1000;
ALTER FUNCTION getdatarapportproduit(date, date, integer, integer, integer)
  OWNER TO postgres;

  -- Function: getdatarapportqtitezone(integer, integer, date, date)

-- DROP FUNCTION getdatarapportqtitezone(integer, integer, date, date);

CREATE OR REPLACE FUNCTION getdatarapportqtitezone(
    integer,
    integer,
    date,
    date)
  RETURNS SETOF data_qtite_zone AS
$BODY$
  DECLARE


 in_id_annee ALIAS FOR $1;
 in_id_saison ALIAS FOR $2; -- Date de calcul iar
 in_date_debut ALIAS FOR $3; -- Numero dossier de credits
 in_date_fin ALIAS FOR $4;  -- id agence

v_province text;
v_id_province integer :=0;
v_parent_province integer := 0;
v_commune text;
v_id_commune integer :=0;
v_search_commune text;
v_parent_commune integer :=0;
v_coopec text;
v_zone text;
v_id_zone integer :=0;
v_search_zone text;
v_id_prod integer :=0;
v_qtit integer :=0;
v_montant numeric(30,6) := 0;
v_saison_actif integer :=0;

 ligne record;
 ligne1 record;

 cur_zone refcursor;
 cur_idProduit refcursor;

 ligne_data data_qtite_zone;

 BEGIN

SELECT INTO v_saison_actif id_saison FROM ec_saison_culturale where etat_saison = 1;
OPEN cur_zone FOR select distinct b.id_zone from ec_beneficiaire b INNER JOIN ec_commande c on c.id_benef = b.id_beneficiaire where c.date_creation >= date(in_date_debut)  and c.date_creation <= date(in_date_fin);


 FETCH cur_zone INTO ligne;
 WHILE FOUND LOOP


	IF (in_id_saison = v_saison_actif) THEN
	OPEN cur_idProduit FOR select id_produit from ec_produit where etat_produit = 1;
	ELSE
	OPEN cur_idProduit FOR select id_produit from ec_produit_hist where id_saison = in_id_saison;
	END IF;

	 FETCH cur_idProduit INTO ligne1;
	 WHILE FOUND LOOP

	SELECT INTO v_id_province,v_province,v_id_commune,v_commune,v_coopec,v_id_zone,v_zone,v_id_prod,v_qtit,v_montant  id_province,nom_province,id_commune,nom_commune,nom_coopec,id_zone,nom_zone, id_produit,sum(qtite) as qtite, sum(total) as total_mnt from(
	SELECT
	(select libel_ag from ad_agc ag where ag.id_ag=b.id_ag) as nom_coopec,
	(select libel from ec_localisation l where l.id=b.id_province) as nom_province,
	(select id from ec_localisation l where l.id=b.id_province) as id_province,
	(select libel from ec_localisation l where l.id=b.id_commune) as nom_commune,
	(select id from ec_localisation l where l.id=b.id_commune) as id_commune,
	(select libel from ec_localisation l where l.id=b.id_zone) as nom_zone,
	(select id from ec_localisation l where l.id=b.id_zone) as id_zone,
	p.id_produit,p.libel, sum(d.quantite) as qtite, sum(d.montant_depose)  as total
	FROM ec_beneficiaire b
	INNER JOIN ec_commande c on c.id_benef = b.id_beneficiaire
	INNER JOIN ec_commande_detail d on d.id_commande = c.id_commande
	INNER JOIN ec_produit p on p.id_produit = d.id_produit
	where c.etat_commande in (1,2,3,4,8)
	and c.date_creation >= date(in_date_debut)  and c.date_creation <= date(in_date_fin)
	and b.id_zone= ligne.id_zone
	and c.id_saison = in_id_saison
	and p.id_produit = ligne1.id_produit
	group by b.id_province,b.id_commune, b.id_zone, d.quantite,p.type_produit,p.id_produit,p.libel,b.id_ag)
	z
	group by id_province,nom_province,id_commune, nom_commune,nom_coopec,id_zone,nom_zone,id_produit,libel
	order by nom_province,nom_coopec,nom_zone,id_produit,libel;

	IF (v_commune IS NULL) THEN
	select into v_parent_commune parent from ec_localisation where id = ligne.id_zone;
	select into v_id_commune,v_commune id,libel from ec_localisation where id = v_parent_commune;
	END IF;

	IF (v_province IS NULL) THEN
	SELECT INTO v_parent_province parent from ec_localisation where id = v_parent_commune;
	SELECT INTO v_id_province,v_province id,libel from ec_localisation where id = v_parent_province;
	END IF;

	IF (v_coopec IS NULL) THEN
	SELECT INTO v_coopec libel_ag from ad_agc where id_ag = numagc();
	END IF;

	IF (v_zone IS NULL)THEN
	SELECT INTO v_id_zone,v_zone id,libel from ec_localisation where id = ligne.id_zone;
	v_qtit = 0;
	v_montant =0;
	END IF;

	SELECT INTO ligne_data v_id_province,v_province,v_id_commune,v_commune,v_coopec,v_id_zone,v_zone,ligne1.id_produit,v_qtit,v_montant;
	RETURN NEXT ligne_data;

	FETCH cur_idProduit INTO ligne1;
	END LOOP;
	CLOSE cur_idProduit;


FETCH cur_zone INTO ligne;
END LOOP;
CLOSE cur_zone;

 END;
 $BODY$
  LANGUAGE plpgsql VOLATILE
  COST 100
  ROWS 1000;
ALTER FUNCTION getdatarapportqtitezone(integer, integer, date, date)
  OWNER TO postgres;


