

CREATE OR REPLACE FUNCTION poste_bilan_actif_brb(date, integer, boolean, boolean, boolean)
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
				where   compartiment =1 AND code_rapport = 'bilan' AND (operation = true OR operation is null)	 AND 
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
ALTER FUNCTION poste_bilan_actif_brb(date, integer, boolean, boolean, boolean) OWNER TO adbanking;

CREATE OR REPLACE FUNCTION  poste_bilan_passif_brb (DATE,INTEGER,Boolean,Boolean,Boolean, text ) RETURNS SETOF rapport_financier AS  $$
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
								              				 on  a.code = b.code  where compartiment in (2,3) AND code_rapport ='bilan' AND id_ag = idag);
				ELSE
					create temp table  bilan_passif as 
	               	 SELECT  calculesoldebilan( num_cpte_comptable,date( date_bilan),idag,compart_cpte,is_conv_devise_ref,is_consolide) as solde,
	               	 calculesoldebilan( num_cpte_comptable,getdatefinexerciceprecedent(date(date_bilan),idag) ,idag,compart_cpte, is_conv_devise_ref,is_consolide) as solde_anneeprec,
	               	 num_cpte_comptable
					 from ad_cpt_comptable 
					 where id_ag = idag  AND 
				 		num_cpte_comptable IN (SELECT  distinct b.num_cpte_comptable
									              from ad_poste a LEFT JOIN  (ad_poste_compte b inner join ad_cpt_comptable c on b.num_cpte_comptable = c.num_cpte_comptable)
									              				 on  a.code = b.code  where compartiment in (2,3) AND code_rapport ='bilan' AND id_ag = idag) ;
				
				END IF;
                
					OPEN Cpt_Calcul_Int FOR SELECT  a.code, sum(solde) as solde_bilan,sum(solde_anneeprec) as solde_bilan_prec,libel,niveau
										FROM ad_poste a LEFT JOIN  (ad_poste_compte b inner join bilan_passif c
																    on b.num_cpte_comptable = c.num_cpte_comptable) 
														ON  a.code = b.code  
										where   compartiment in (2,3) AND code_rapport ='bilan' and (is_cpte_provision= false or is_cpte_provision is null) and operation ='f'
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

CREATE OR REPLACE FUNCTION epargne_view1(date, integer, integer, integer, integer)
  RETURNS SETOF epargne_view_type AS
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
		CREATE TEMP TABLE  temp_ad_cpt1 as 
			SELECT  date_ouvert, solde,solde_clot,id_titulaire,id_cpte,id_prod,a.devise,etat_cpte,a.id_ag ,date_clot,num_complet_cpte,b.libel, b.classe_comptable
			from ad_cpt a left join adsys_produit_epargne b on ( a.id_ag=b.id_ag and a.id_prod =b.id)
			where (b.classe_comptable=1 OR b.classe_comptable=2 OR b.classe_comptable=5 OR b.classe_comptable=6)  and  
			date(date_ouvert)<= date(date_epargne) and  a.id_ag =idag and
			( etat_cpte <> 2 OR (etat_cpte = 2 and date(date_clot) >date(date_epargne))) order by id_titulaire,id_cpte limit limites OFFSET  offsets;
	ELSE 
		CREATE TEMP TABLE  temp_ad_cpt1 as 
			SELECT  date_ouvert, solde,solde_clot,id_titulaire,id_cpte,id_prod,a.devise,etat_cpte,a.id_ag ,date_clot,num_complet_cpte,b.libel, b.classe_comptable
			from ad_cpt a left join adsys_produit_epargne b on ( a.id_ag=b.id_ag and a.id_prod =b.id)
			where (b.classe_comptable=1 OR b.classe_comptable=2 OR b.classe_comptable=5 OR b.classe_comptable=6)  and  
			date(date_ouvert)<= date(date_epargne) and  a.id_ag =idag AND id_prod = v_id_prod AND
			( etat_cpte <> 2 OR (etat_cpte = 2 and date(date_clot) >date(date_epargne)))  order by id_titulaire,id_cpte limit limites OFFSET  offsets;
	END IF;
         
	-- RAISE NOTICE '%', solde_actuel ;
	IF  DATE(date_epargne) >=  DATE(now()) THEN
		OPEN cur_epargne FOR SELECT a.*,  0.0 as solde_after_date_ep FROM temp_ad_cpt a order  by id_titulaire,id_cpte;
	
	ELSE
               
               CREATE TEMP TABLE    solde_after_date_epargne1 as SELECT a.id_cpte,  sum( CASE  when sens ='c' THEN montant WHEN sens ='d' THEN -1*montant END ) as solde_after_date_ep 
		from temp_ad_cpt1 a left join  (ad_mouvement b inner join ad_ecriture c on (b.id_ecriture=c.id_ecriture) ) on (a.id_cpte =b.cpte_interne_cli ) 
		where  date(date_comptable) > date(date_epargne) group by a.id_cpte;
		
	      
		OPEN cur_epargne FOR SELECT a.*,solde_after_date_ep FROM temp_ad_cpt1 a left join solde_after_date_epargne1  b  on (a.id_cpte =b.id_cpte)
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
               
		solde_actuel  := COALESCE(ligne.solde,0) -COALESCE(ligne.solde_after_date_ep,0);
               -- solde_total := COALESCE(solde_total,0) +solde_actuel;
               
                SELECT INTO  ligne_epargne ligne.id_titulaire,ligne.id_cpte,ligne.id_prod,ligne.devise,ligne.date_ouvert,ligne.etat_cpte,nom_du_client ,solde_actuel,
			ligne.id_ag,ligne.num_complet_cpte,ligne.libel,ligne.classe_comptable ;
		RETURN NEXT ligne_epargne ;
	FETCH cur_epargne INTO ligne;
	END LOOP;
 CLOSE cur_epargne;
--RAISE NOTICE '%', solde_total;
DROP TABLE IF EXISTS temp_ad_cpt1;
DROP TABLE IF EXISTS solde_after_date_epargne1  ;
--DROP TABLE mv_credit;
RETURN;
END
$BODY$
  LANGUAGE plpgsql VOLATILE
  COST 100
  ROWS 1000;
ALTER FUNCTION epargne_view1(date, integer, integer, integer, integer) OWNER TO adbanking;