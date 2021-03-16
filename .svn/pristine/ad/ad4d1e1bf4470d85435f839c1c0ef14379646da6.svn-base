DROP TYPE IF EXISTS type_cpte_dcr CASCADE;
CREATE TYPE type_cpte_dcr AS (
 	  id_client int4,
      id_doss int4,
 	  num_cpte varchar(50),
 	  solde_cap float4,
      solde_cpte_interne float4
 	);

CREATE OR REPLACE FUNCTION  compare_credit_cpte_interne (DATE)
 	RETURNS SETOF  type_cpte_dcr AS  $$
DECLARE
	date_batch ALIAS FOR $1; 
	ligne type_cpte_dcr;
BEGIN 
	DROP TABLE IF EXISTS solde_cpt;
	DROP TABLE IF EXISTS mnt_etr;
	DROP TABLE IF EXISTS mnt_sre;
	CREATE TEMP TABLE solde_cpt AS
	select  cpte_interne_cli,sum(CASE WHEN sens = 'c' THEN -montant ELSE montant END) AS solde_cpt 
	from ad_mouvement where cpte_interne_cli IN
	 ( select cre_id_cpte from  ad_dcr d where d.cre_date_debloc <= DATE(date_batch) AND (etat IN (5,7,8,13) OR (etat IN (6,9) AND date_etat > DATE(date_batch))))
	 AND date_valeur <= DATE(date_batch)
	GROUP BY cpte_interne_cli;

	CREATE TEMP TABLE mnt_etr AS
	select  d.cre_id_cpte,d.id_doss,d.id_client, sum(mnt_cap) AS mnt_att from ad_etr e, ad_dcr d where e.id_doss = d.id_doss and d.cre_date_debloc <= DATE(date_batch) AND (etat IN (5,7,8,13) OR (etat IN (6,9) AND date_etat > DATE(date_batch)))
	GROUP BY d.cre_id_cpte,d.id_doss,d.id_client;
	
	CREATE TEMP TABLE mnt_sre AS
	select  d.cre_id_cpte, sum(mnt_remb_cap) AS mnt_remb from ad_sre e, ad_dcr d where e.id_doss = d.id_doss and e.date_remb <= DATE(date_batch) and d.cre_date_debloc <= DATE(date_batch) AND (etat IN (5,7,8,13) OR (etat IN (6,9) AND date_etat > DATE(date_batch)))
	GROUP BY d.cre_id_cpte;

	FOR ligne IN select e.id_client,e.id_doss,(select num_complet_cpte from ad_cpt where id_cpte = c.cpte_interne_cli) , c.solde_cpt, (e.mnt_att-s.mnt_remb) as solde_cap_ech 
	 from solde_cpt c, mnt_etr e, mnt_sre s
		where c.cpte_interne_cli = e.cre_id_cpte and e.cre_id_cpte = s.cre_id_cpte and c.solde_cpt != e.mnt_att-s.mnt_remb
	LOOP
		RETURN NEXT ligne;
	END LOOP;
	RETURN ;

END;
$$ LANGUAGE 'plpgsql';


CREATE OR REPLACE FUNCTION  compare_compta_cpte_interne_credit  (DATE)
 	RETURNS SETOF  type_cpte_dcr AS  $$
DECLARE
	date_batch ALIAS FOR $1; 
	ligne type_cpte_dcr;
BEGIN 
	DROP TABLE IF EXISTS solde_compta;
	DROP TABLE IF EXISTS solde_cpt;
	CREATE TEMP TABLE solde_compta AS
	select  cpte_interne_cli,sum(CASE WHEN sens = 'c' THEN -montant ELSE montant END) AS solde_compta 
	FROM ad_mouvement m 
	WHERE (m.compte IN (SELECT DISTINCT num_cpte_comptable FROM adsys_etat_credit_cptes WHERE  id_etat_credit != (select id from adsys_etat_credits where nbre_jours = -1)) ) 
	AND date_valeur <= DATE(date_batch)
	GROUP BY cpte_interne_cli;
	CREATE TEMP TABLE solde_cpt AS
	select  cpte_interne_cli,sum(CASE WHEN sens = 'c' THEN -montant ELSE montant END) AS solde_cpt 
	from ad_mouvement
	 where cpte_interne_cli IN ( select cre_id_cpte from  ad_dcr d where d.cre_date_debloc <= DATE(date_batch) AND (etat IN (5,7,8,13) OR (etat IN (6,9,12) AND date_etat > DATE(date_batch)))) 
	AND date_valeur <= DATE( date_batch)
	GROUP BY cpte_interne_cli;
	
	FOR ligne IN select NULL,(select id_doss from ad_dcr where cre_id_cpte = b.cpte_interne_cli),(select num_complet_cpte from ad_cpt where id_cpte = b.cpte_interne_cli ),solde_compta,solde_cpt
		from solde_compta a, solde_cpt b
		 where  a.cpte_interne_cli = b.cpte_interne_cli and a.solde_compta != b.solde_cpt 
	LOOP
		RETURN NEXT ligne;
	END LOOP;
	RETURN ;

END;
$$ LANGUAGE 'plpgsql';


select * from compare_credit_cpte_interne(date(now()));