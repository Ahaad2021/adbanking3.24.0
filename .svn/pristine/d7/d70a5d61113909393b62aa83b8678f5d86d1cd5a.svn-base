---------------------------------------------------------------------------------------------------------------------------------------------------------------
---------------------------------------------------FONCTION : Reprise de données de ad_cpt pour les comptes DAT - ref. #544
---------------------------------------------------------------------------------------------------------------------------------------------------------------

-- Function: reprise_dat_part1()
-- DROP FUNCTION reprise_dat_part1()


CREATE OR REPLACE FUNCTION reprise_dat_part2(integer)
  RETURNS VOID AS $BODY$
  
DECLARE
id_his_param alias for $1;  
BEGIN
RAISE INFO 'REPRISE comptes DAT' ;
RAISE INFO 'REPRISE Part 2' ;
SET statement_timeout = 0;
SET client_encoding = 'UTF8';
SET standard_conforming_strings = off;
SET check_function_bodies = false;
SET client_min_messages = warning;
SET escape_string_warning = off;


---------------------Inserer les donnÃ©es avant la reprise-------------------------------------------

insert into ad_cpt_hist(date_action,id_cpte,id_titulaire,etat_cpte,solde,id_ag,id_his,login,type_fonction)
SELECT finale.date_valeur,finale.id_cpte,finale.id_titulaire, finale.etat_cpte, finale.solde, finale.id_ag, finale.id_his, finale.login, finale.type_fonction 
FROM(

SELECT donnees.date_valeur,donnees.id_cpte,donnees.id_titulaire, donnees.etat_cpte, donnees.solde, donnees.id_ag ,donnees.id_his, donnees.login, donnees.type_fonction 
FROM(
select mvt.date_valeur,cpte.id_cpte,cpte.id_titulaire,cpte.etat_cpte,cpte.solde,his.id_ag, id_his_param as id_his,'admin'::text as login,501 as type_fonction 
from ad_his his,ad_cpt cpte 
JOIN ad_mouvement mvt on mvt.cpte_interne_cli=cpte.id_cpte and cpte.id_prod not in(1,2,3,4) and mvt.sens='c'
and mvt.compte in (SELECT distinct b.cpte_cpta_prod_ep FROM ad_cpt a, adsys_produit_epargne b WHERE b.id_ag = a.id_ag AND a.id_prod = b.id and b.id not in(1,2,3,4) ) 
and mvt.id_ecriture=(select distinct id_ecriture from ad_ecriture where id_his =id_his_param) )AS donnees

left JOIN (SELECT * FROM ad_cpt_hist)AS histo ON donnees.date_valeur=histo.date_action AND 
donnees.id_cpte=histo.id_cpte AND donnees.id_titulaire=histo.id_titulaire AND donnees.etat_cpte=histo.etat_cpte AND donnees.solde=histo.solde AND donnees.id_ag=histo.id_ag AND donnees.id_his=histo.id_his AND donnees.login=histo.login AND donnees.type_fonction=histo.type_fonction

WHERE histo.date_action is null AND histo.id_cpte is null AND histo.id_titulaire is null AND histo.etat_cpte is null AND histo.solde is null AND histo.id_ag is null AND histo.id_his is null AND histo.login is null 
AND histo.type_fonction is null ) AS finale;
--commit;

END;
$BODY$
  LANGUAGE plpgsql VOLATILE;
  
--ALTER FUNCTION reprise_dat_part2()
 -- OWNER TO adbanking;
--SELECT reprise_dat_part2();
--DROP FUNCTION reprise_dat_part2();

