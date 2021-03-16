--------------------- MISE A JOUR PROVISION ----------------------------------------
--UPDATE adsys_etat_credits SET taux =taux/100;
CREATE OR REPLACE FUNCTION compartiment (text) RETURNS integer AS $$
  SELECT compart_cpte from ad_cpt_comptable where num_cpte_comptable = $1;
$$ LANGUAGE SQL;
CREATE OR REPLACE FUNCTION getetatcreditprovision(integer,DATE,INTEGER) RETURNS INTEGER AS $$
DECLARE
	iddoss ALIAS FOR $1;
        date_tran ALIAS FOR $2;
	idag ALIAS FOR $3;
	tmp_date DATE;

BEGIN
  SELECT INTO tmp_date  distinct date_comptable from ad_ecriture where cast ( info_ecriture as integer) = 64 and date_comptable =date(date_tran) ;
  
  IF tmp_date IS NULL THEN 
	RETURN CalculEtatCredit(iddoss,date(date_tran),idag);
  ELSE
	RETURN CalculEtatCredit(iddoss,date(date(tmp_date)-1),idag);
	
 END IF ;
END;
$$ LANGUAGE plpgsql;

DROP TABLE IF EXISTS tmp_ad_provision;
create table tmp_ad_provision as select id_doss,montant,numagc() as id_ag,e.taux as taux ,getetatcreditprovision(d.id_doss,date(a.date),numagc()) as id_cred_etat,
date(date) as prov_date,type_fonction,c.id_ecriture
,sens,compte from ad_his a, ad_ecriture b, ad_mouvement c , ad_dcr  d,adsys_etat_credits e
where  a.id_ag=b.id_ag and a.id_ag=c.id_ag and a.id_ag=d.id_ag and 
b.id_ag=c.id_ag and b.id_ag = d.id_ag and c.id_ag=d.id_ag and e.id_ag = a.id_ag AND e.id_ag = b.id_ag AND e.id_ag=c.id_ag AND
 e.id = getetatcreditprovision(d.id_doss,date(a.date),numagc()) and cast(info_ecriture as INTEGER) = id_doss and
 a.id_his= b.id_his and b.id_ecriture= c.id_ecriture and (type_fonction = 432) and sens = 'c'  order by c.id_mouvement,sens;

CREATE OR REPLACE FUNCTION montantprovision (integer,date) RETURNS numeric AS $$
  SELECT sum( case when compartiment(compte)=4 then -1*montant ELSE montant END)  
	from tmp_ad_provision 
	where id_doss = $1 and prov_date <= $2 ;
$$ LANGUAGE SQL;

insert into  ad_provision (id_doss ,
  montant,
  id_ag ,
  taux,
  id_cred_etat,
  date_prov,is_repris ) select id_doss,montantprovision(id_doss,prov_date), id_ag,taux,id_cred_etat, prov_date, TRUE
  from  tmp_ad_provision;
  
  DROP TABLE IF EXISTS tmp_ad_provision;
  DROP FUNCTION IF EXISTS montantprovision (integer,date);
  DROP FUNCTION IF EXISTS  compartiment (text);
  DROP FUNCTION IF EXISTS getetatcreditprovision(integer,DATE,INTEGER);
---------------------------  FIN MISE A JOUR POVISION ----------------------------------------------------------------------------------------------------------