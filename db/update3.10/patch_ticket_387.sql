CREATE OR REPLACE FUNCTION patch_387() RETURNS void AS $$
DECLARE

BEGIN

IF NOT EXISTS (SELECT nom_ecran FROM ecrans WHERE nom_ecran='Ara-58') THEN
	INSERT INTO ecrans(nom_ecran, fichier, nom_menu, fonction) 
	VALUES ('Ara-58', 'modules/rapports/rapports_agence.php', 'Ara-2', 370);
	RAISE NOTICE 'Added ecran Ara-58';
END IF;

IF NOT EXISTS (SELECT nom_ecran FROM ecrans WHERE nom_ecran='Ara-59') THEN
	INSERT INTO ecrans(nom_ecran, fichier, nom_menu, fonction) 
	VALUES ('Ara-59', 'modules/rapports/rapports_agence.php', 'Ara-3', 370);
	RAISE NOTICE 'Added ecran Ara-59';
END IF;

IF NOT EXISTS (SELECT nom_ecran FROM ecrans WHERE nom_ecran='Ara-60') THEN
	INSERT INTO ecrans(nom_ecran, fichier, nom_menu, fonction) 
	VALUES ('Ara-60', 'modules/rapports/rapports_agence.php', 'Ara-3', 370);
	RAISE NOTICE 'Added ecran Ara-60';
END IF;

END;

$$
LANGUAGE plpgsql VOLATILE COST 100;
ALTER FUNCTION patch_387() OWNER TO adbanking;

-- ----------------------------------------------------------------------------
-- Execution
-- ----------------------------------------------------------------------------
SELECT patch_387();
DROP FUNCTION patch_387();

-- ---------------------------------------------------------------------------
-- Fonction qui alimente le rapport equilibre inventaire/compta :
-- ---------------------------------------------------------------------------

CREATE OR REPLACE FUNCTION get_rpt_ecart_compta(IN date, IN text)
  RETURNS TABLE(date_ecart timestamp without time zone, numero_compte_comptable text, libel_cpte_comptable text, devise character, solde_cpte_int numeric, solde_cpte_comptable numeric, ecart numeric, login text, id_his integer, id_doss integer, cre_etat integer, solde_credit numeric, solde_cpt numeric, ecart_credits numeric) AS
$BODY$
select ec.date_ecart,
coalesce(ec.num_cpte_comptable, ed.num_cpte_comptable) as numero_compte_comptable,
ec.libel_cpte_comptable,
ec.devise,
ec.solde_cpte_int,
ec.solde_cpte_comptable,
ec.ecart,
ec.login,
ec.id_his,
ed.id_doss,
ed.cre_etat,
ed.solde_credit,
ed.solde_cpt,
ed.ecart_credits

from
(
select
date_ecart,
num_cpte_comptable,
libel_cpte_comptable,
devise,
solde_cpte_int,
solde_cpte_comptable,
ecart,
login,
id_his
from ad_ecart_compta aec
where id = (select max(id) from ad_ecart_compta where num_cpte_comptable = aec.num_cpte_comptable)
)
ec 
FULL JOIN (
		select A.*, solde_credit+solde_cpt as ecart_credits from
		(
		SELECT c.num_cpte_comptable, a.id_doss, a.cre_etat, round(sum(solde_cap)) as solde_credit, round(solde) as solde_cpt 
		from ad_dcr a, ad_etr b, ad_cpt c where a.id_doss = b.id_doss and a.cre_id_cpte = c.id_cpte and etat in( 5,6) 
		group by c.num_cpte_comptable, a.id_doss,a.cre_etat, c.solde 
		)A
		where solde_credit <> -solde_cpt 
		) ed
		on ec.num_cpte_comptable = ed.num_cpte_comptable

where ec.date_ecart::date <= coalesce($1, to_date('20991231','YYYYMMDD'))  and coalesce(ec.num_cpte_comptable, ed.num_cpte_comptable) = 
case when $2 is null then coalesce(ec.num_cpte_comptable, ed.num_cpte_comptable) else $2 end

		order by date_ecart desc, coalesce(ec.num_cpte_comptable, ed.num_cpte_comptable) asc

$BODY$
LANGUAGE sql VOLATILE COST 100 ROWS 1000;
ALTER FUNCTION get_rpt_ecart_compta(date, text) OWNER TO adbanking;
