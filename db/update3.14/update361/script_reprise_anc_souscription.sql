
---------------------------------------------------------------------------------------------------------------------------------------------------------------
---------------------------------------------------FONCTION :SCRIPT REPRISE ANCIENNE SOUSCRIPTION DANS LA TABLE ad_part_sociale_his
---------------------------------------------------------------------------------------------------------------------------------------------------------------

-- Function: reprise_ad_part_sociale_his()
-- DROP FUNCTION reprise_ad_part_sociale_his()

CREATE OR REPLACE FUNCTION reprise_ad_part_sociale_his()
  RETURNS VOID AS $BODY$
  
DECLARE

BEGIN
RAISE INFO 'REPRISE DONNEES ANCIENNE SOUSCRIPTIONS -ad_part_sociale_his' ;
SET statement_timeout = 0;
SET client_encoding = 'UTF8';
SET standard_conforming_strings = off;
SET check_function_bodies = false;
SET client_min_messages = warning;
SET escape_string_warning = off;

---------------------Gestion de qualite client-----------------------------------------------------
--Mise Ã  jour de la qualitÃ© d'un client en ordinaire si le nombre de parts sociales libÃ©rÃ©es est > 0
update ad_cli set qualite = case when nbre_parts_lib > 0 then 2 else 1 end where qualite = 1 and nbre_parts_lib > 0;

---------------------Inserer les donnÃ©es aprÃ¨s la reprise-------------------------------------------
insert into ad_part_sociale_his (date_his,id_client,qualite,type_fonc,id_his,nbre_ps_souscrite,nbre_ps_lib,solde_ps_lib,solde_ps_restant,nom_login,id_ag)
SELECT finale.date,finale.id_client,finale.qualite,finale.type_fonction, finale.id_his ,finale.nbre_parts,finale.nbre_parts_lib,finale.solde,finale.solde_part_soc_restant,finale.login,finale.id_ag From
(SELECT donnees.date,donnees.id_client,donnees.qualite,donnees.type_fonction, donnees.id_his ,donnees.nbre_parts,donnees.nbre_parts_lib,donnees.solde,donnees.solde_part_soc_restant,donnees.login,donnees.id_ag FROM
(select his.date,cli.id_client,case when cli.nbre_parts_lib > 0 then 2 else 1 end as qualite ,his.type_fonction,his.id_his, cli.nbre_parts,cli.nbre_parts_lib,cpt.solde,cpt.solde_part_soc_restant,his.login,his.id_ag 
from ad_cli cli JOIN ad_his his on his.id_client=cli.id_client and his.type_fonction in (20,30) 
JOIN ad_cpt cpt on cpt.id_titulaire=cli.id_client and cpt.id_prod=2) AS donnees left JOIN (SELECT * FROM ad_part_sociale_his) AS partsoc ON donnees.id_client =partsoc.id_client AND donnees.id_his = partsoc.id_his AND donnees.type_fonction = partsoc.type_fonc 
WHERE partsoc.id_client is null AND partsoc.id_his is null AND partsoc.type_fonc is null
) as finale;
---------------------Inserer les donnÃ©es avant la reprise-------------------------------------------
insert into ad_part_sociale_his (date_his,id_client,qualite,type_fonc,id_his,nbre_ps_souscrite,nbre_ps_lib,solde_ps_lib,solde_ps_restant,nom_login,id_ag)
SELECT finale.date_valeur, finale.id_client, finale.qualite , finale.type_fonction, finale.id_his, finale.nbre_parts, finale.nbre_parts_lib, finale.solde, finale.solde_part_soc_restant, finale.nom_login, finale.id_ag From
(SELECT  d.date_valeur, d.id_client, d.qualite ,d.type_fonction,d.id_his,d.nbre_parts,d.nbre_parts_lib,d.solde, d.solde_part_soc_restant, d.nom_login, d.id_ag FROM 
(select mvt.date_valeur,cli.id_client,case when cli.nbre_parts_lib > 0 then 2 else 1 end as qualite ,500 as type_fonction,(select id_his from ad_his where type_fonction = 500) as id_his, cli.nbre_parts,cli.nbre_parts_lib,
cpt.solde,cpt.solde_part_soc_restant,'admin' as nom_login,cli.id_ag from ad_cli cli 
JOIN ad_cpt cpt on cpt.id_titulaire=cli.id_client and cpt.id_prod=2 JOIN ad_mouvement mvt on mvt.cpte_interne_cli=cpt.id_cpte and mvt.sens='c' 
and mvt.compte=(SELECT distinct b.cpte_cpta_prod_ep FROM ad_cpt a, adsys_produit_epargne b WHERE b.id_ag = a.id_ag AND a.id_prod = b.id  and b.id =2 ) 
and mvt.id_ecriture=(select distinct id_ecriture from ad_ecriture where id_his = (select id_his from ad_his where type_fonction = 500)limit 1) )AS d left join (SELECT * FROM ad_part_sociale_his) as partsoc 
ON d.id_client =partsoc.id_client  AND d.id_his = partsoc.id_his AND d.type_fonction = partsoc.type_fonc
WHERE partsoc.id_client is null  AND partsoc.id_his is null AND partsoc.type_fonc is null
)as finale;

END;
$BODY$
  LANGUAGE plpgsql VOLATILE;
  
ALTER FUNCTION reprise_ad_part_sociale_his()
  OWNER TO adbanking;
SELECT reprise_ad_part_sociale_his();
DROP FUNCTION reprise_ad_part_sociale_his();

