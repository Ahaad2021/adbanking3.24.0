----------- DROP fonction f_getmouvementforproducer(text, numeric, text, integer) -----------------
CREATE OR REPLACE function f_getmouvementforproducer(text, numeric, text, integer) returns TABLE(id_client integer, id_ag integer, id_cpte integer, id_transaction integer, id_mouvement integer, date_transaction timestamp without time zone, ref_ecriture text, type_opt integer, libelle_ecriture text, montant numeric, sens text, devise character, communication text, tireur text, donneur text, numero_cheque text, solde numeric, telephone character varying, langue integer, num_complet_cpte text, intitule_compte text, date_ouvert timestamp without time zone, statut_juridique integer, nom text, prenom text, libelle_produit text)
LANGUAGE plpgsql
AS $$
declare
	v_cpte_interne_cli ALIAS for $1;
	v_montant ALIAS for $2;
	v_date_valeur ALIAS for $3;
	v_id_ag ALIAS for $4;

BEGIN

return query

select
	case
		when h.id_client is null
		then cpt.id_titulaire
		else h.id_client
	end as id_client,
	m.id_ag,
	m.cpte_interne_cli as id_cpte,
	m.id_ecriture as id_transaction,
	m.id_mouvement,
	h.date as date_transaction,
	e.ref_ecriture,
	e.type_operation as type_opt,
	t.traduction as libelle_ecriture,
	m.montant,
	m.sens,
	m.devise,
	histo_ext.communication,
	case
		when h.type_fonction in (70,75)
		then histo_ext.tireur
		else null
	end as tireur,
	histo_ext.nom_client AS donneur,
	histo_ext.numero_cheque,
	cpt.solde,
	a.num_sms as telephone,
	a.langue,
	cpt.num_complet_cpte,
	cpt.intitule_compte,
	cpt.date_ouvert as date_ouvert,
	c.statut_juridique,
	c.pp_nom as nom,
	c.pp_prenom as prenom,
	produit.libel as libelle_produit
from
	ad_mouvement m
	inner join ad_ecriture e on e.id_ag=m.id_ag and e.id_ecriture=m.id_ecriture
	inner join ad_his h on h.id_ag=e.id_ag and h.id_his=e.id_his
	left join
			(select
				ext.id_ag,
				ext.id,
				p.nom_client,
				tb.denomination as tireur,
				case
					when ext.type_piece in (2,4,5,15)
					then ext.num_piece
					else null
				end AS numero_cheque,
				ext.communication
			from
				ad_his_ext ext
				left join
						(select
							pers.id_ag,pers.id_client,pers.id_pers_ext,
							COALESCE (CASE
										cli.statut_juridique
										WHEN '1'
										THEN pp_nom||' '||pp_prenom
										WHEN '2'
										THEN pm_raison_sociale
										WHEN '3'
										THEN gi_nom
										WHEN '4'
										THEN gi_nom
									END, pers.denomination)  AS nom_client
						FROM ad_pers_ext pers
						left join  ad_cli cli on cli.id_ag = pers.id_ag and cli.id_client = pers.id_client) p on ext.id_ag  = p.id_ag and ext.id_pers_ext = p.id_pers_ext
						left join tireur_benef tb on ext.id_tireur_benef = tb.id and ext.id_ag = tb.id_ag
			) histo_ext on histo_ext.id_ag=h.id_ag and h.id_his_ext = histo_ext.id
	inner join ad_traductions t on t.id_str =e.libel_ecriture
	inner join ad_cpt cpt on m.id_ag = cpt.id_ag and m.cpte_interne_cli = cpt.id_cpte
  inner join ad_abonnement a ON cpt.id_titulaire = a.id_client AND cpt.id_ag = a.id_ag
  inner join ad_cli c ON a.id_client = c.id_client AND a.id_ag = c.id_ag
  inner join adsys_produit_epargne produit ON cpt.id_prod = produit.id AND cpt.id_ag = produit.id_ag
where
	cpt.id_prod NOT IN (3,4)
and
  h.id_his =
  (
    SELECT h.id_his
    FROM ad_mouvement m
    INNER JOIN ad_ecriture e ON m.id_ecriture = e.id_ecriture AND m.id_ag = e.id_ag
    INNER JOIN ad_his h ON e.id_his = h.id_his AND h.id_ag = e.id_ag
    WHERE m.cpte_interne_cli = cast(v_cpte_interne_cli as INTEGER)
    AND m.montant = v_montant
    AND m.date_valeur = to_date(v_date_valeur, 'yyyy-MM-dd')
		AND h.id_ag = v_id_ag
    ORDER BY date_valeur DESC
    LIMIT 1
  )
and
	m.cpte_interne_cli = cast(v_cpte_interne_cli as INTEGER)
and
	m.montant = v_montant
and
	m.date_valeur = to_date(v_date_valeur, 'yyyy-MM-dd')
and
    a.deleted = FALSE;
 end;
$$;
---------------------------------------------------------------------------------------------------

----------- DROP fonction f_getmouvementforproducerarretecomptebatch(integer, integer, numeric, timestamp without time zone) -----------------
CREATE OR REPLACE function f_getmouvementforproducerarretecomptebatch(integer, integer, numeric, timestamp without time zone) returns TABLE(id_client integer, telephone character varying, langue integer, num_complet_cpte text, intitule_compte text, date_ouvert timestamp without time zone, nom text, prenom text, statut_juridique integer, libelle_produit text, id_ag integer, id_cpte integer, id_transaction integer, id_mouvement integer, montant numeric, sens text, devise character, ref_ecriture text, type_opt integer, libelle_ecriture text, solde numeric, date_transaction timestamp without time zone)
LANGUAGE plpgsql
AS $$
DECLARE
  v_id_mouvement ALIAS FOR $1;
  v_id_ag ALIAS FOR $2;
  v_solde ALIAS FOR $3;
  v_date_transaction ALIAS FOR $4;
BEGIN

  RETURN QUERY
		with adm as
		(SELECT
		m.id_ag,
		m.cpte_interne_cli AS id_cpte,
		m.id_ecriture AS id_transaction,
		m.id_mouvement,
		m.id_ecriture,
		m.montant,
		m.sens,
		m.devise
		FROM ad_mouvement m
		WHERE m.id_mouvement = v_id_mouvement
		),

		ade as
		(SELECT
		e.*
		FROM ad_ecriture e
		join adm on e.id_ecriture = adm.id_ecriture
		),

		adt as
		(SELECT
		traduction,
		id_str
		FROM ad_traductions t
		join ade on t.id_str = ade.libel_ecriture
		)

		SELECT
		cpt.id_titulaire AS id_client,
		a.num_sms AS telephone,
		a.langue,
		cpt.num_complet_cpte,
		cpt.intitule_compte,
		cpt.date_ouvert AS date_ouvert,
		c.pp_nom AS nom,
		c.pp_prenom AS prenom,
		c.statut_juridique,
		produit.libel AS libelle_produit,
		adm.id_ag,
		adm.id_cpte,
		adm.id_transaction,
		adm.id_mouvement,
		adm.montant,
		adm.sens,
		adm.devise,
		ade.ref_ecriture,
		ade.type_operation AS type_opt,
		adt.traduction AS libelle_ecriture,
		v_solde AS solde,
		v_date_transaction AS date_transaction
		FROM ad_cpt cpt
		INNER JOIN ad_abonnement a ON cpt.id_titulaire = a.id_client AND cpt.id_ag = a.id_ag
		INNER JOIN ad_cli c ON a.id_client = c.id_client AND a.id_ag = c.id_ag
		INNER JOIN adsys_produit_epargne produit ON cpt.id_prod = produit.id AND cpt.id_ag = produit.id_ag
		INNER JOIN adm on cpt.id_cpte = adm.id_cpte and cpt.id_ag = adm.id_ag
		INNER JOIN ade on adm.id_transaction = ade.id_ecriture and adm.id_ag = ade.id_ag
		INNER JOIN adt on ade.libel_ecriture = adt.id_str
		WHERE cpt.id_cpte = adm.id_cpte
		AND cpt.id_ag = v_id_ag
		AND a.deleted = FALSE;
END;
$$;
----------------------------------------------------------------------------------------------------------------------------------------------