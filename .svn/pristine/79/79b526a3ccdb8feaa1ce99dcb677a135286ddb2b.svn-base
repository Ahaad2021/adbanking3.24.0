DROP TYPE if exists cpte_dormant  CASCADE;
CREATE TYPE cpte_dormant AS (
 	  id_cpte integer,
 	  id_titulaire int4,
 	  solde float4,
 	  devise char(3)
 	 
 	);

CREATE OR REPLACE FUNCTION traitecomptesdormants(date, integer)
  RETURNS SETOF cpte_dormant AS
$BODY$
 DECLARE
	date_batch  ALIAS FOR $1;		-- Date d'execution du batch
	idAgc ALIAS FOR $2;			    -- id de l'agence
	ligne_param_epargne RECORD ;
    ligne RECORD ;
    nbre_cptes INTEGER ;
    ligne_resultat cpte_dormant;
BEGIN
        SELECT INTO ligne_param_epargne cpte_inactive_nbre_jour,cpte_inactive_frais_tenue_cpte 
	FROM adsys_param_epargne
	WHERE id_ag = idAgc ;
        IF ligne_param_epargne.cpte_inactive_nbre_jour IS NOT NULL THEN
		
	 
         DROP TABLE  IF EXISTS temp_ad_cpt_dormant;
         IF ligne_param_epargne.cpte_inactive_frais_tenue_cpte IS NULL OR ligne_param_epargne.cpte_inactive_frais_tenue_cpte=FALSE THEN 

	  CREATE TEMP TABLE temp_ad_cpt_dormant as SELECT  id_cpte,id_titulaire,solde,c.devise
	  FROM ad_mouvement a , ad_cpt b, adsys_produit_epargne c
	  WHERE a.id_ag=b.id_ag AND a.id_ag=c.id_ag AND b.id_ag=c.id_ag AND c.id_ag = idAgc 
	  AND cpte_interne_cli = id_cpte AND b.id_prod = c.id  AND classe_comptable=1 AND c.retrait_unique =FALSE AND c.depot_unique = FALSE 
          AND c.passage_etat_dormant = 'true'
          AND etat_cpte not in (2,4)
          GROUP BY id_cpte,id_titulaire ,solde,c.devise
          HAVING DATE(date_batch) -max(date_valeur) > ligne_param_epargne.cpte_inactive_nbre_jour ;

        ELSE
          CREATE TEMP TABLE temp_ad_cpt_dormant as SELECT  id_cpte,id_titulaire,solde,c.devise
	  FROM ad_mouvement a , ad_cpt b, adsys_produit_epargne c , ad_ecriture d
	  WHERE a.id_ag=b.id_ag AND a.id_ag=c.id_ag AND b.id_ag=c.id_ag AND c.id_ag = idAgc 
          AND a.id_ecriture = d.id_ecriture and type_operation <> 50 AND cpte_interne_cli = id_cpte AND b.id_prod = c.id  AND classe_comptable=1 AND c.retrait_unique =FALSE AND c.depot_unique = FALSE 
          AND c.passage_etat_dormant = 'true'
          AND etat_cpte not in (2,4)
          GROUP BY id_cpte,id_titulaire ,solde,c.devise
          HAVING DATE(date_batch) -max(date_valeur) > ligne_param_epargne.cpte_inactive_nbre_jour ;
       END IF;

        UPDATE ad_cpt a SET  etat_cpte = 4,date_blocage= DATE(now()), raison_blocage = 'Compte dormant'
        WHERE id_cpte in  ( SELECT id_cpte FROM temp_ad_cpt_dormant);
       FOR ligne_resultat IN SELECT  * FROM temp_ad_cpt_dormant
	   	LOOP
	   		RETURN NEXT ligne_resultat;
	   	END LOOP;
	   		
        
      ELSE 
	 	RETURN  ;
      END IF ;
      RETURN  ;
END;
$BODY$
  LANGUAGE plpgsql VOLATILE
  COST 100
  ROWS 1000;
ALTER FUNCTION traitecomptesdormants(date, integer)
  OWNER TO postgres;
