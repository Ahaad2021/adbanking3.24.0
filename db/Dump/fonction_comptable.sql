-------------------------------------------------------------------------------------------
-- Calcule le solde du compte $compte à la date $date_solde sans récursivité              --
--Càd que les sous compte s'ils existent ne sont pas inclus dans le solde du compte
--Il existe une version php de cette fonction
----------------------------------------------------------------------------------------------
CREATE OR REPLACE FUNCTION calculSoldeNonRecursif(text,DATE,INTEGER,boolean) RETURNS NUMERIC AS $$
 DECLARE
        cpte ALIAS FOR $1;   			-- Numéro du compte
	date_param  ALIAS FOR $2;		-- Date du solde
	idAgc ALIAS FOR $3;			-- id de l'agence
	is_consolide ALIAS FOR $4;		--vrai si on veut editer les états financiers consolidés
	solde_debit NUMERIC(30,6):=0;		--solde au débit du compte
	solde_credit NUMERIC(30,6):=0;		--solde au crédit du compte comptable
	solde_reciproque NUMERIC(30,6):=0;	--solde des mouvement reciproque(mvt passé entre l'agence etle siege ou vice versa)
	solde_courant NUMERIC(30,6):=0;		--solde courant du compte comptable( solde actuel ds ad_cpt_comptable)

	BEGIN
	------------------SOLDE COURANT DU COMPTE COMPTABLE
		SELECT INTO solde_courant COALESCE(solde,0)  FROM ad_cpt_comptable
			WHERE id_ag =idAgc AND num_cpte_comptable =cpte;

	------------------MOUVEMENTS DU LENDEMAIN DE LA DATE DE CALCUL DU SOLDE JUSQU'A AUJOURD'HUI ------------------------------------------

		--Au débit
		SELECT INTO solde_debit COALESCE(sum(montant),0)    FROM ad_mouvement a, ad_ecriture b
			WHERE a.id_ag=idAgc and b.id_ag=idAgc and a.id_ecriture = b.id_ecriture AND compte =cpte AND date_comptable BETWEEN (date(date_param) + interval '1 day') AND date(now()) AND sens = 'd';

		--Au crédit
		SELECT INTO solde_credit COALESCE(sum(montant),0)  FROM ad_mouvement a, ad_ecriture b
			WHERE a.id_ag=idAgc and b.id_ag=idAgc and a.id_ecriture = b.id_ecriture AND compte =cpte AND date_comptable BETWEEN (date(date_param) + interval '1 day') AND date(now()) AND sens = 'c';


	IF is_consolide is true THEN --si état financier  consolidé
		solde_reciproque:=calculSoldereciproque(cpte ,date_param ,idAgc );
		solde_reciproque:= COALESCE(solde_reciproque,0);
        END IF;
	RETURN solde_courant+ solde_debit-solde_credit-solde_reciproque;
 END;
$$ LANGUAGE plpgsql;

CREATE OR REPLACE FUNCTION calculSoldereciproque(text,DATE,INTEGER) RETURNS NUMERIC AS $$
 DECLARE
	cpte ALIAS FOR $1;   			-- Numéro du compte
	date_param  ALIAS FOR $2;		-- Date du solde
	idAgc ALIAS FOR $3;			-- id de l'agence
	solde_debit NUMERIC(30,6):=0;		--solde au débit du compte
	solde_credit NUMERIC(30,6):=0;		--solde au crédit du compte comptable

	BEGIN
	------- -----------------SOMME DES MOUVEMENTS PASSÉS ENTRE SIEGE-AGENCE ------------------------------------

	--AU DÉBIT
        SELECT INTO solde_debit COALESCE(sum(montant),0)   FROM ad_mouvement a, ad_ecriture b
         WHERE a.id_ag=idAgc and b.id_ag=idAgc and a.id_ecriture = b.id_ecriture AND compte =cpte AND date_comptable <=date(date_param)  AND sens = 'd' AND consolide is true;

	--AU CRÉDIT
        SELECT INTO solde_debit COALESCE(sum(montant),0)   FROM ad_mouvement a, ad_ecriture b
         WHERE a.id_ag=idAgc and b.id_ag=idAgc and a.id_ecriture = b.id_ecriture AND compte =cpte AND date_comptable <=date(date_param)  AND sens = 'd' AND consolide is true;

	RETURN  solde_debit-solde_credit;
 END;
$$ LANGUAGE plpgsql;


------------------------------------------------------------------------------------------------------------
--Calcule non récursif du solde d'un compte à la date 'date_param'  dans la partie du bilan correspondant au compartiment 'compartiment'
------------------------------------------------------------------------------------------------------------

CREATE OR REPLACE FUNCTION SoldeBilanNonRecursif(text,DATE,INTEGER,INTEGER,boolean,boolean) RETURNS NUMERIC AS $$
 DECLARE
        cpte ALIAS FOR $1;        	--Numéro du compte
	date_param  ALIAS FOR $2;	--Date du solde
	idAgc ALIAS FOR $3;		-- id de l'agence
	compartiment ALIAS FOR $4;	-- dans quelle partie du bilan on veut obtenir le solde
	cv ALIAS FOR $5;		--convertir le montant dans la devise de reference
	is_consolide ALIAS FOR $6;	-- vrai si on veut calucler un solde consolidé
	solde_reel NUMERIC(30,6):=0;	--solde reel du compte comptable
	solde_bilan NUMERIC(30,6):=0;	--Solde du compte :à l'ACTIF (compartiment=1) ou au PASSIF (compartiment=2)
	devise_ref character(3);	--devise de reference
	infosCpte RECORD ;		--info compte ( compartiment et devise du compte cpte)

	BEGIN

	SELECT INTO infosCpte compart_cpte,devise  FROM ad_cpt_comptable WHERE id_ag =idAgc AND num_cpte_comptable =cpte;

        solde_reel:=calculSoldeNonRecursif(cpte,date_param,idAgc,is_consolide);
        IF cv IS TRUE THEN
                SELECT INTO devise_ref code_devise_reference FROM ad_agc WHERE id_ag =idAgc;
           	solde_reel:=calculecv(solde_reel,infosCpte.devise,devise_ref,idAgc);
        END IF;
        solde_reel:=COALESCE(solde_reel,0.000000);

	IF (infosCpte.compart_cpte = compartiment) THEN --Si c'est un compte de la partie du bilan à créer : ACTIF ou bien PASSIF
		solde_bilan:=solde_reel;

	ELSIF (infosCpte.compart_cpte = 5 ) THEN --Compte Actif-Passif
		IF compartiment=1 AND solde_reel<0 THEN
			solde_bilan:=solde_reel;
		END IF;
		IF compartiment=2 AND solde_reel>0 THEN
			solde_bilan:=solde_reel;
		END IF;
	END IF;


	RETURN solde_bilan;
 END;
$$ LANGUAGE plpgsql;

------------------------------------------------------------------------------------------------------------
--Calcule récursif du solde d'un compte à la date 'date_param'  dans la partie du bilan correspondant au compartiment 'compartiment'
------------------------------------------------------------------------------------------------------------

CREATE OR REPLACE FUNCTION calculeSoldeBilan(text,DATE,INTEGER,INTEGER,boolean,boolean) RETURNS NUMERIC AS $$
 DECLARE
         cpte ALIAS FOR $1;        	--Numéro du compte
	date_param  ALIAS FOR $2;	--Date du solde
	idAgc ALIAS FOR $3;		-- id de l'agence
	compartiment ALIAS FOR $4;	-- dans quelle partie du bilan on veut obtenir le solde
	cv ALIAS FOR $5;		--convertir le montant dans la devise de reference
	is_consolide ALIAS FOR $6;	-- vrai si on veut calucler un solde consolidé
	solde_bilan NUMERIC(30,6):=0;	--Solde du compte :à l'ACTIF (compartiment=1) ou au PASSIF (compartiment=2)


	BEGIN


         select INTO solde_bilan sum( SoldeBilanNonRecursif(d.num_cpte_comptable,date_param,idAgc,compartiment,cv,is_consolide)) from ad_cpt_comptable d where id_ag = idAgc and ( (num_cpte_comptable = cpte) OR (num_cpte_comptable LIKE cpte||'.%') )   ;


	RETURN solde_bilan;
 END;
$$ LANGUAGE plpgsql;


------------------------------------------------------------------------------------------------------------
--Calcule récursif du solde de provision d'un compte à la date 'date_param'  dans la partie du bilan correspondant au compartiment 'compartiment'
------------------------------------------------------------------------------------------------------------

CREATE OR REPLACE FUNCTION calculeSoldeBilanProv(text,DATE,INTEGER,INTEGER,boolean,boolean) RETURNS NUMERIC AS $$
  DECLARE
         cpte ALIAS FOR $1;        	--Numéro du compte
	date_param  ALIAS FOR $2;	--Date du solde
	idAgc ALIAS FOR $3;		-- id de l'agence
	compartiment ALIAS FOR $4;	-- dans quelle partie du bilan on veut obtenir le solde
	cv ALIAS FOR $5;		--convertir le montant dans la devise de reference
	is_consolide ALIAS FOR $6;	-- vrai si on veut calucler un solde consolidé
	solde_bilan NUMERIC(30,6):=0;	--Solde du compte :à l'ACTIF (compartiment=1) ou au PASSIF (compartiment=2)

  BEGIN

         select INTO solde_bilan sum( calculeSoldeBilan(cpte_provision,date_param,idAgc,compartiment,cv,is_consolide)) from ad_cpt_comptable d where ( (num_cpte_comptable = cpte) OR (num_cpte_comptable LIKE cpte||'.%') )  AND (cpte_provision IS NOT NULL OR cpte_provision <>'') ;

	RETURN solde_bilan;
  END;
$$ LANGUAGE plpgsql;


-------------------------------------------------------------------------------------------------------------------------------------------------------------------
-- fonction permettant de verifier si un sous-compte (ou compte) à un solde nul
-------------------------------------------------------------------------------------------------------------------------------------------------------------------
CREATE OR REPLACE FUNCTION isSoldebilanNotNul(text, date, integer, integer, boolean, boolean)
  RETURNS BOOLEAN AS
$BODY$
 DECLARE
         cpte ALIAS FOR $1;        	--Numéro du compte
	date_param  ALIAS FOR $2;	--Date du solde
	idAgc ALIAS FOR $3;		-- id de l'agence
	compartiment ALIAS FOR $4;	-- dans quelle partie du bilan on veut obtenir le solde
	cv ALIAS FOR $5;		--convertir le montant dans la devise de reference
	is_consolide ALIAS FOR $6;	-- vrai si on veut calucler un solde consolidé
	solde_bilan NUMERIC(30,6):=0;	--Solde du compte :à l'ACTIF (compartiment=1) ou au PASSIF (compartiment=2)
	response boolean;		-- vrai si le solde d'un sous compte au moins est superieur a zero,ou le solde d'un cpte est >0


	BEGIN


         select INTO solde_bilan sum( abs(SoldeBilanNonRecursif(d.num_cpte_comptable,date_param,idAgc,compartiment,cv,is_consolide) )) from ad_cpt_comptable d where ( (num_cpte_comptable = cpte) OR (num_cpte_comptable LIKE cpte||'.%') )   ;


	RETURN solde_bilan > 0;
 END;
$BODY$
  LANGUAGE 'plpgsql' VOLATILE;
   
  -----------------------------------------------------------------------------------------------
 -- Function: eshango_calculesolderesultatcompte(text, date, date, date, integer, boolean)
 -- DROP FUNCTION eshango_calculesolderesultatcompte(text, date, date, date, integer, boolean);
 -- A CREER calculesolderesultatcompte
-----------------------------------------------------------------------------------------------
CREATE OR REPLACE FUNCTION calculesolderesultatcompte(text, date, date, date, integer, boolean)
  RETURNS numeric AS
$BODY$
 DECLARE
        cpte ALIAS FOR $1;        	--Numéro du compte
	date_deb  ALIAS FOR $2;	--Date debut resultat
	date_fin  ALIAS FOR $3;	--Date fin resultat
	date_fin_exo  ALIAS FOR $4;	--Date de fin exercice
	idAgc ALIAS FOR $5;		-- id de l'agence
	is_consolide ALIAS FOR $6;	-- vrai si on veut calucler un solde consolidé
	solde_resultat NUMERIC(30,6):=0;
	BEGIN	
         select INTO solde_resultat sum(solderesultatnonrecursif(d.num_cpte_comptable,date_deb,date_fin,date_fin_exo, d.id_ag,is_consolide)) from ad_cpt_comptable d where (num_cpte_comptable =cpte  OR num_cpte_comptable  LIKE cpte||'.%') AND id_ag=idAgc;
	RETURN solde_resultat;
 END;
$BODY$
  LANGUAGE plpgsql VOLATILE;
ALTER FUNCTION calculesolderesultatcompte(text, date, date, date, integer, boolean) OWNER TO adbanking;


-----------------------------------------------------------------------------------------------
 -- Function: eshango_solderesultatnonrecursif(text, date, date, date, integer, boolean)
 -- DROP FUNCTION eshango_solderesultatnonrecursif(text, date, date, date, integer, boolean);
-- A CREER  solderesultatnonrecursif
-----------------------------------------------------------------------------------------------
CREATE OR REPLACE FUNCTION solderesultatnonrecursif(text, date, date, date, integer, boolean)
  RETURNS numeric AS
$BODY$
 DECLARE
        cpte ALIAS FOR $1;        	--Numéro du compte
	date_deb  ALIAS FOR $2;	--Date debut resultat
	date_fin  ALIAS FOR $3;	--Date fin resultat
	date_fin_exo  ALIAS FOR $4;	--Date de fin exercice
	idAgc ALIAS FOR $5;		-- id de l'agence
	is_consolide ALIAS FOR $6;	-- vrai si on veut calucler un solde consolidé
	solde_reel NUMERIC(30,6):=0;	--solde reel du compte comptable
	devise_ref character(3);	--devise de reference
	solde_deb NUMERIC(30,6):=0;
	solde_fin NUMERIC(30,6):=0;
	solde_debit NUMERIC(30,6):=0;
	solde_credit NUMERIC(30,6):=0;
	BEGIN
	solde_deb := calculsoldenonrecursif(cpte,date_deb,idAgc,is_consolide);
	solde_fin := calculsoldenonrecursif(cpte,date_fin,idAgc,is_consolide);
	IF (date_fin = date_fin_exo) THEN
		SELECT INTO solde_debit sum(c.montant) FROM ad_his a, ad_ecriture b, ad_mouvement c WHERE a.id_ag=idAgc and b.id_ag=idAgc and c.id_ag=idAgc
 		AND a.type_fonction=442 AND b.date_comptable=date_fin_exo AND c.compte=cpte AND sens='d'
 		AND a.id_his=b.id_his AND b.id_ecriture=c.id_ecriture;
		solde_fin := solde_fin + COALESCE(solde_debit, 0);
		SELECT INTO solde_credit sum(c.montant) FROM ad_his a, ad_ecriture b, ad_mouvement c WHERE a.id_ag=idAgc and b.id_ag=idAgc and c.id_ag=idAgc
		AND a.type_fonction=442 AND b.date_comptable=date_fin_exo AND c.compte=cpte AND sens='c'
		AND a.id_his=b.id_his AND b.id_ecriture=c.id_ecriture;
		solde_fin := solde_fin - COALESCE(solde_credit, 0);
        END IF;
	solde_reel:= solde_fin - solde_deb;
        solde_reel:=COALESCE(solde_reel, 0);
	RETURN solde_reel;
 END;
$BODY$
  LANGUAGE plpgsql VOLATILE;
ALTER FUNCTION solderesultatnonrecursif(text, date, date, date, integer, boolean) OWNER TO adbanking;


CREATE OR REPLACE FUNCTION getdatefinexercice(date, date) RETURNS date AS $$
  SELECT date_fin_exo FROM ad_exercices_compta WHERE id_ag = numagc() AND 
  date(date_deb_exo) <= date($1) AND date(date_fin_exo) >= date($1) AND
   date(date_deb_exo) <= date($2) AND date(date_fin_exo) >= date($2)
  ;
 $$ LANGUAGE 'SQL';
 
 
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




DROP TYPE IF EXISTS rapport_financier  cascade;
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












CREATE OR REPLACE FUNCTION getdatefinexerciceprecedent(date, INTEGER) RETURNS date AS $$
SELECT MAX(date_fin_exo) FROM ad_exercices_compta WHERE id_ag = $2 AND date_deb_exo < (
  SELECT date_deb_exo FROM ad_exercices_compta WHERE id_ag = $2 AND 
  date(date_deb_exo) <= date($1) AND date(date_fin_exo) >= date($1) )
  ;
 $$ LANGUAGE 'SQL';
 
 CREATE OR REPLACE FUNCTION getdatedebutexerciceprecedent(date, INTEGER) RETURNS date AS $$
SELECT MAX(date_deb_exo) FROM ad_exercices_compta WHERE id_ag = $2 AND date_deb_exo < (
  SELECT date_deb_exo FROM ad_exercices_compta WHERE id_ag = $2 AND 
  date(date_deb_exo) <= date($1) AND date(date_fin_exo) >= date($1) )
  ;
 $$ LANGUAGE 'SQL';
 CREATE OR REPLACE FUNCTION getdatedebutexercice(date, INTEGER) RETURNS date AS $$
SELECT date_deb_exo  FROM ad_exercices_compta WHERE id_ag = $2 AND date(date_deb_exo) <=date($1) and  date(date_fin_exo) >= date($1);
 $$ LANGUAGE 'SQL';
 CREATE OR REPLACE FUNCTION getdatefinexercice(date, INTEGER) RETURNS date AS $$
SELECT date_fin_exo FROM ad_exercices_compta WHERE id_ag = $2 AND date(date_deb_exo) <=date($1) and  date(date_fin_exo) >= date($1);

 $$ LANGUAGE 'SQL';
 
 
 -------------------------------------------------------------------------------------------
-- Calcule le solde du compte $compte à la date $date_solde avec récursivité              --
--Càd que les sous compte s'ils existent sont  inclus dans le solde du compte
--Il existe une version php de cette fonction
----------------------------------------------------------------------------------------------
CREATE OR REPLACE FUNCTION calculSoldeRecursif(text,DATE,INTEGER,boolean) RETURNS NUMERIC AS $$
 DECLARE
        cpte ALIAS FOR $1;   			-- Numéro du compte
	date_param  ALIAS FOR $2;		-- Date du solde
	idAgc ALIAS FOR $3;			-- id de l'agence
	is_consolide ALIAS FOR $4;		--vrai si on veut editer les états financiers consolidés
	solde_debit NUMERIC(30,6):=0;		--solde au débit du compte
	solde_credit NUMERIC(30,6):=0;		--solde au crédit du compte comptable
	solde_reciproque NUMERIC(30,6):=0;	--solde des mouvement reciproque(mvt passé entre l'agence etle siege ou vice versa)
	solde_courant NUMERIC(30,6):=0;		--solde courant du compte comptable( solde actuel ds ad_cpt_comptable)

	BEGIN
	 select INTO solde_courant sum( calculSoldeNonRecursif(d.num_cpte_comptable,date_param,idAgc,is_consolide)) 
	from ad_cpt_comptable d where ( (num_cpte_comptable = cpte) OR (num_cpte_comptable LIKE cpte||'.%') )   ;

        RETURN COALESCE(solde_courant,0);
 END;
$$ LANGUAGE plpgsql;
 
  
-------------------------------------------------------------------------------------------
-- Calcule le solde du compte $compte à la date $date_solde avec récursivité              --
--Càd que les sous compte s'ils existent sont  inclus dans le solde du compte
--Il existe une version php de cette fonction
----------------------------------------------------------------------------------------------
CREATE OR REPLACE FUNCTION  calculeSommeMvtCpte (text,DATE,DATE,char,INTEGER,boolean) RETURNS NUMERIC AS $$
 DECLARE
        cpte ALIAS FOR $1;   			-- Numéro du compte
	date_debut  ALIAS FOR $2;		-- Date du solde
	date_fin  ALIAS FOR $3;		-- Date du solde
	
	sens_cpte ALIAS FOR $4;			-- id de l'agence
	idAgc ALIAS FOR $5;			-- id de l'agence
	is_consolide ALIAS FOR $6;		--vrai si on veut editer les états financiers consolidés
	solde_debit NUMERIC(30,6):=0;		--solde au débit du compte
	solde_credit NUMERIC(30,6):=0;		--solde au crédit du compte comptable
	solde_reciproque NUMERIC(30,6):=0;	--solde des mouvement reciproque(mvt passé entre l'agence etle siege ou vice versa)
	total_debits NUMERIC(30,6):=0;		--solde courant du compte comptable( solde actuel ds ad_cpt_comptable)

	BEGIN
	SELECT INTO total_debits SUM(montant)  
	FROM ad_ecriture,ad_mouvement 
	WHERE 	ad_ecriture.id_ag = ad_mouvement.id_ag AND ad_mouvement.id_ag = idAgc
		AND ad_ecriture.id_ecriture=ad_mouvement.id_ecriture AND compte = cpte 
		AND sens = sens_cpte AND date(date_comptable) >= date(date_debut) AND date(date_comptable) <= date(date_fin)
                ;

        RETURN COALESCE(total_debits,0);
 END;
$$ LANGUAGE plpgsql;









-------------------------------------------------------------------------------------------
-- Calcule le solde du compte $compte à la date $date_solde avec récursivité              --
--Càd que les sous compte s'ils existent sont  inclus dans le solde du compte
--Il existe une version php de cette fonction
----------------------------------------------------------------------------------------------
CREATE OR REPLACE FUNCTION  calculeSommeMvtCpteRecursif (text,DATE,DATE,char,INTEGER,boolean) RETURNS NUMERIC AS $$
 DECLARE
        cpte ALIAS FOR $1;   			-- Numéro du compte
	date_debut  ALIAS FOR $2;		-- Date du solde
	date_fin  ALIAS FOR $3;		-- Date du solde
	
	sens_cpte ALIAS FOR $4;			-- id de l'agence
	idAgc ALIAS FOR $5;			-- id de l'agence
	is_consolide ALIAS FOR $6;		--vrai si on veut editer les états financiers consolidés
	solde_debit NUMERIC(30,6):=0;		--solde au débit du compte
	solde_credit NUMERIC(30,6):=0;		--solde au crédit du compte comptable
	solde_reciproque NUMERIC(30,6):=0;	--solde des mouvement reciproque(mvt passé entre l'agence etle siege ou vice versa)
	total_debits NUMERIC(30,6):=0;		--solde courant du compte comptable( solde actuel ds ad_cpt_comptable)

	BEGIN
	select INTO total_debits sum( calculeSommeMvtCpte(d.num_cpte_comptable,date_debut,date_fin,sens_cpte,idAgc, is_consolide)) 
	from ad_cpt_comptable d where ( (num_cpte_comptable = cpte) OR (num_cpte_comptable LIKE cpte||'.%') )   ;

        RETURN COALESCE(total_debits,0);
 END;
$$ LANGUAGE plpgsql;


