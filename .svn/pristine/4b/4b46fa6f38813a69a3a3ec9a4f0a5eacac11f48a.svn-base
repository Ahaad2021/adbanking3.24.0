
---------------------------------------------------------------------------------------------------------------------------------------------------------------
---------------------------------------------------FONCTION : Reprise de données de ad_cpt pour les comptes DAT - ref. #544
---------------------------------------------------------------------------------------------------------------------------------------------------------------

-- Function: reprise_dat_part1()
-- DROP FUNCTION reprise_dat_part1()

CREATE OR REPLACE FUNCTION reprise_dat_part1()
  RETURNS VOID AS $BODY$
  
DECLARE

BEGIN
RAISE INFO 'REPRISE comptes DAT' ;
RAISE INFO 'REPRISE Part 1' ;
SET statement_timeout = 0;
SET client_encoding = 'UTF8';
SET standard_conforming_strings = off;
SET check_function_bodies = false;
SET client_min_messages = warning;
SET escape_string_warning = off;


---------------------Inserer les données après la reprise-------------------------------------------
INSERT INTO ad_cpt_hist (date_action,id_cpte,id_titulaire,etat_cpte,solde,id_ag,id_his,login,type_fonction)
SELECT distinct finale.date,finale.cpte_interne_cli,finale.id_titulaire, finale.etat_cpte, finale.montant, finale.id_ag,finale.id_his,finale.login,finale.type_fonction
FROM (

SELECT donnees.date,donnees.cpte_interne_cli,donnees.id_titulaire, donnees.etat_cpte, donnees.montant, donnees.id_ag,donnees.id_his,donnees.login,donnees.type_fonction
FROM (
select distinct his.date,v.cpte_interne_cli,cpt.id_titulaire, cpt.etat_cpte,v.montant,his.id_ag,his.id_his,his.login,his.type_fonction 
from ad_his his  
inner join view_compta v on his.id_his = v.id_his 
and v.compte in (SELECT distinct b.cpte_cpta_prod_ep FROM ad_cpt a, adsys_produit_epargne b WHERE b.id_ag = a.id_ag AND a.id_prod = b.id and b.id not in (1,2,3,4) ) 
and v.sens = case 
when his.type_fonction  = 53 then 'c'
when his.type_fonction  = 54 then 'd'
end
INNER JOIN ad_cpt cpt on cpt.id_cpte = v.cpte_interne_cli and cpt.id_ag = v.id_ag
where his.type_fonction in (53,54,78) and cpt.id_prod not in (1,2,3,4)

) AS donnees

LEFT JOIN ad_cpt_hist AS histo ON donnees.date =histo.date_action AND donnees.cpte_interne_cli=histo.id_cpte AND donnees.id_titulaire=histo.id_titulaire
AND donnees.etat_cpte=histo.etat_cpte AND donnees.montant=histo.solde AND donnees.id_ag=histo.id_ag AND donnees.id_his=histo.id_his AND donnees.login=histo.login AND donnees.type_fonction=histo.type_fonction

WHERE histo.date_action is null AND histo.id_cpte is null AND histo.id_titulaire is null AND histo.etat_cpte is null AND histo.solde is null AND histo.id_ag is null AND histo.id_his is null AND histo.login is null 
AND histo.type_fonction is null 
 ) AS finale;
--commit ;


END;
$BODY$
  LANGUAGE plpgsql VOLATILE;
  
--ALTER FUNCTION reprise_dat_part1()
 -- OWNER TO adbanking;
--SELECT reprise_dat_part1();
--DROP FUNCTION reprise_dat_part1();

