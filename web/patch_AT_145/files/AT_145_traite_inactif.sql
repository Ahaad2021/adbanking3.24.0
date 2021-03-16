DROP TYPE if exists cpte_inactif CASCADE;
CREATE TYPE cpte_inactif AS (
 	  id_cpte integer,
 	  id_titulaire int4,
 	  solde float4,
 	  devise char(3)
 	 
 	);

CREATE OR REPLACE FUNCTION traitecomptesinactifs(
    date,
    integer)
  RETURNS SETOF cpte_inactif AS
$BODY$
	DECLARE
	date_batch  ALIAS FOR $1;		-- Date d'execution du batch
	idAgc ALIAS FOR $2;			    -- id de l'agence
	ligne_param_epargne RECORD ;
	ligne RECORD ;
	nbre_cptes INTEGER ;
	ligne_resultat cpte_inactif;

	BEGIN
	SELECT INTO ligne_param_epargne cpte_dormant_nbre_jour,cpte_dormant_frais_tenue_cpte, cpte_inactive_nbre_jour, cpte_inactive_frais_tenue_cpte
	FROM adsys_param_epargne
	WHERE id_ag = idAgc ;
	IF ligne_param_epargne.cpte_dormant_nbre_jour IS NOT NULL THEN

	DROP TABLE  IF EXISTS temp_ad_cpt_inactif;
	IF ligne_param_epargne.cpte_dormant_frais_tenue_cpte IS NULL OR ligne_param_epargne.cpte_dormant_frais_tenue_cpte=FALSE THEN

			CREATE TEMP TABLE temp_ad_cpt_inactif as
			SELECT
				id_cpte,
				id_titulaire,
				solde,
				c.devise,
				m.date_dernier_mvt_tenue_cpte,
				m.date_dernier_mvt,
				DATE(date_batch) - m.date_dernier_mvt as ecart
			FROM  ad_cpt b
				inner join adsys_produit_epargne c
					on b.id_prod = c.id
						 AND b.id_ag = c.id_ag
						 AND c.classe_comptable=1
						 AND c.retrait_unique =FALSE
						 AND c.depot_unique = FALSE
						 AND c.passage_etat_inactif = 'true'

				inner join (
										 select cpte_interne_cli, id_ag,
											 max(case when type_operation = 50 then date_valeur else null end ) as date_dernier_mvt_tenue_cpte,
											 max(date_valeur) as date_dernier_mvt
										 from ad_mouvement inner join ad_ecriture using (id_ecriture, id_ag)
										 group by cpte_interne_cli,id_ag
									 ) m

					on b.id_cpte = m.cpte_interne_cli
						 AND c.id_ag = m.id_ag
						 AND c.id_ag = idAgc
			where b.etat_cpte not in (2, 8)
			and DATE(date_batch) - m.date_dernier_mvt BETWEEN ligne_param_epargne.cpte_dormant_nbre_jour AND (ligne_param_epargne.cpte_inactive_nbre_jour - 1);

	ELSE
			CREATE TEMP TABLE temp_ad_cpt_inactif as SELECT  id_cpte,id_titulaire,solde,c.devise
			FROM ad_mouvement a , ad_cpt b, adsys_produit_epargne c
			WHERE a.id_ag=b.id_ag AND a.id_ag=c.id_ag AND b.id_ag=c.id_ag AND c.id_ag = idAgc
			AND cpte_interne_cli = id_cpte AND b.id_prod = c.id  AND classe_comptable=1 AND c.retrait_unique =FALSE AND c.depot_unique = FALSE
			AND c.passage_etat_inactif = 'true'
			AND etat_cpte not in (2,8)
			GROUP BY id_cpte,id_titulaire ,solde,c.devise
			HAVING DATE(date_batch) -max(date_valeur) > ligne_param_epargne.cpte_dormant_nbre_jour AND DATE(date_batch) -max(date_valeur) < ligne_param_epargne.cpte_inactive_nbre_jour;
	END IF;

	UPDATE ad_cpt a SET  etat_cpte = 8,date_blocage= DATE(now()), raison_blocage = 'Compte inactif'
	WHERE id_cpte in  ( SELECT id_cpte FROM temp_ad_cpt_inactif);

	FOR ligne_resultat IN SELECT  * FROM temp_ad_cpt_inactif
	LOOP
	RETURN NEXT ligne_resultat;
	END LOOP;


ELSE
END IF ;
END;
$BODY$
  LANGUAGE plpgsql VOLATILE
  COST 100
  ROWS 1000;
ALTER FUNCTION traitecomptesinactifs(date, integer)
  OWNER TO adbanking;