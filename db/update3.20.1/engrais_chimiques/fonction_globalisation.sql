CREATE OR REPLACE FUNCTION engrais_chimiques_function_globalisation() RETURNS INT AS
$$
DECLARE
output_result INTEGER = 1;
tableliste_ident INTEGER = 0;

BEGIN

DROP TYPE IF EXISTS data_produit_global CASCADE;

CREATE TYPE data_produit_global AS (
      nom_province text,
      nom_commune text,
      libel_ag text,
      id_produit int,
      libel_prod text,
      qtite integer,
      qtite_paye integer,
      mnt_avance numeric(30,6),
      mnt_solde numeric(30,6),
      id_annee int,
      id_saison text,
      period int
      );

-- Function: getdatarapportproduit(date, date, integer, integer, integer)

-- DROP FUNCTION getdatarapportproduit(date, date, integer, integer, integer);

CREATE OR REPLACE FUNCTION getdatarapportproduitglobal(
    date,
    date,
    integer,
    integer,
    integer)
  RETURNS SETOF data_produit_global AS
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
v_qtite_paye integer := 0;
v_mnt_avance numeric(30,6) :=0;
v_mnt_solde numeric(30,6):= 0;
v_nbre_agri integer;
v_agc_check integer :=0;

cur_produit refcursor;
cur_loc refcursor;


ligne record;
ligne1 record;

ligne_data data_produit_global;

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

	OPEN cur_produit FOR select id_produit, libel from ec_produit where etat_produit =1 order by id_produit;
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

	OPEN cur_loc FOR select distinct loc1.libel as nom_province, loc.libel as nom_commune,id_province, id_commune from ec_beneficiaire  b
	INNER JOIN ec_commande c on c.id_benef = b.id_beneficiaire
	INNER JOIN ec_localisation loc on loc.id = b.id_commune
	INNER JOIN ec_localisation loc1 on loc1.id = b.id_province
	where c.etat_commande not in (7,5,6);
	FETCH cur_loc INTO ligne1;
	WHILE FOUND LOOP

	OPEN cur_produit FOR select id_produit, libel from ec_produit where etat_produit =1 order by id_produit;
	FETCH cur_produit INTO ligne;
	WHILE FOUND LOOP

		select into  v_nom_province,v_nom_commune,v_id_prod, v_libel_prod, v_qtite, v_mnt_avance loc1.libel as nom_province, loc.libel as nom_commune, d.id_produit,pd.libel, sum(d.quantite) as qtite,sum(d.montant_depose) as mnt_avance
	from ec_commande_detail d
		INNER JOIN ec_commande c on c.id_commande=d.id_commande
		INNER JOIN ec_produit pd on pd.id_produit = d.id_produit
		INNER JOIN ec_beneficiaire b on b.id_beneficiaire = c.id_benef
		INNER JOIN ec_localisation loc on loc.id = b.id_commune
		INNER JOIN ec_localisation loc1 on loc1.id = b.id_province
		INNER JOIN ad_agc ag on ag.id_ag = c.id_ag
		where id_saison = in_id_saison
		and etat_commande in (3,8)
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
		v_qtite_paye =0;
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
ALTER FUNCTION getdatarapportproduitglobal(date, date, integer, integer, integer)
  OWNER TO postgres;
  
  
RETURN output_result;

END;
$$
LANGUAGE plpgsql;


select engrais_chimiques_function_globalisation();

DROP FUNCTION IF EXISTS engrais_chimiques_function_globalisation();