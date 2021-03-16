--------------------------------------------------------Debut Ticket AT-36----------------------------------------------
CREATE OR REPLACE FUNCTION ticket_AT_36()
  RETURNS INT AS
$BODY$
DECLARE
output_result INTEGER := 1;
tableliste_ident INTEGER :=0;
tableliste_ident_adsys INTEGER :=0;
d_tableliste_str INTEGER :=0;
change_traduction INTEGER :=0;

BEGIN

select INTO tableliste_ident ident from tableliste where nomc like 'adsys_produit_epargne' order by ident desc limit 1;

	IF NOT EXISTS(SELECT * FROM tableliste WHERE nomc = 'adsys_type_duree_min2retrait') THEN
        INSERT INTO tableliste VALUES ((select max(ident) from tableliste)+1, 'adsys_type_duree_min2retrait', makeTraductionLangSyst('"Type de duree pour les duree minimum entre deux retraits"'), false);
        RAISE NOTICE 'Données table adsys_param_mouvement rajoutés dans table tableliste';
    END IF;

select INTO tableliste_ident_adsys ident from tableliste where nomc like 'adsys_type_duree_min2retrait' order by ident desc limit 1;

	-- Insertion dans d_tableliste champ adsys_type_duree_min2retrait."id" et adsys_type_duree_min2retrait."libelle"
    IF NOT EXISTS(SELECT * FROM d_tableliste WHERE nchmpc = 'id' and tablen = tableliste_ident_adsys) THEN
          INSERT INTO d_tableliste VALUES ((select max(ident) from d_tableliste)+1, tableliste_ident_adsys, 'id', makeTraductionLangSyst('Id'), true, NULL, 'int', null, true, false);
    END IF;

    IF NOT EXISTS(SELECT * FROM d_tableliste WHERE nchmpc = 'libelle' and tablen = tableliste_ident_adsys) THEN
        INSERT INTO d_tableliste VALUES ((select max(ident) from d_tableliste)+1, tableliste_ident_adsys, 'libelle', makeTraductionLangSyst('Libelle'), true, NULL, 'txt', true, null, false);
    END IF;

	 IF NOT EXISTS(SELECT * FROM d_tableliste WHERE nchmpc = 'type_duree_min2retrait' and tablen = tableliste_ident) THEN
	   ALTER TABLE adsys_produit_epargne ADD COLUMN type_duree_min2retrait integer;
	   d_tableliste_str := makeTraductionLangSyst('Type de durée pour le non respect de la durée minimum entre deux rétraits');
	   INSERT INTO d_tableliste VALUES ((select max(ident) from d_tableliste)+1, tableliste_ident, 'type_duree_min2retrait', d_tableliste_str, NULL, (SELECT ident from d_tableliste where tablen = tableliste_ident_adsys and nchmpc = 'id' ), 'lsb', true, false, false);
	   IF EXISTS(SELECT langue FROM adsys_langues_systeme WHERE code = 'en_GB') THEN
	    INSERT INTO ad_traductions VALUES (d_tableliste_str,'en_GB','Type frequency of Minimum Duration between two withdrawals not achieved');
	   END IF;
	 END IF;


	change_traduction := (select nchmpl from d_tableliste where nchmpc = 'duree_min_retrait_jour');
	UPDATE ad_traductions SET traduction = 'Durée minimum entre deux retraits(0 si aucun)' WHERE id_str = change_traduction and langue = 'fr_BE';



RETURN output_result;
END;
$BODY$
  LANGUAGE plpgsql VOLATILE
  COST 100;
ALTER FUNCTION ticket_AT_36()
  OWNER TO postgres;

  SELECT ticket_AT_36();
  DROP FUNCTION IF EXISTS ticket_AT_36();
---------------------------------------------------------Fin Ticket AT-36-----------------------------------------------
--------------------------------------------------------Debut Ticket AT-126---------------------------------------------
-- Function: getgrandlivreview(date, date, integer)

-- DROP FUNCTION getgrandlivreview(date, date, integer);

CREATE OR REPLACE FUNCTION getgrandlivreview(
    date,
    date,
    integer)
  RETURNS SETOF grandlivre_view AS
$BODY$
DECLARE
  date_deb ALIAS FOR $1;
  date_fin ALIAS FOR $2;
  id_agence ALIAS FOR $3;

  ligne_grandlivre grandlivre_view;
  ligne            RECORD;
  grandlivre       REFCURSOR;
  idClient        INTEGER;
BEGIN
  --date_deb_calc := date(date_deb) - interval '1 day';
  OPEN grandlivre FOR SELECT * FROM view_compta WHERE date_valeur >= date_deb AND date_valeur <= date_fin AND id_ag = id_agence;
  FETCH grandlivre INTO ligne;
  WHILE FOUND LOOP
  -- Récupère l'id du client s'il existe
  SELECT INTO idClient id_client FROM ad_his WHERE id_his = ligne.id_his AND id_ag = id_agence;
  -- Ticket AT-126, amelioration - si l'id du client n'existe pas dans la table ad_his, il faut le recuperer par rapport au compte interne cli
  IF idClient IS NULL THEN
	SELECT INTO idClient id_titulaire FROM ad_cpt WHERE id_cpte = ligne.cpte_interne_cli AND id_ag = id_agence;
  END IF;

  -- Resultat de la vue
 SELECT INTO ligne_grandlivre ligne.compte, ligne.id_ecriture, ligne.libel_ecriture, ligne.date_comptable, ligne.type_operation, ligne.sens, ligne.devise, ligne.montant, ligne.id_his, idClient AS id_client, ligne.id_ag , ligne.id_jou;
  RETURN NEXT ligne_grandlivre;
  FETCH grandlivre INTO ligne;
  END LOOP;
 CLOSE grandlivre;
RETURN;
END;
$BODY$
  LANGUAGE plpgsql VOLATILE
  COST 100
  ROWS 1000;
ALTER FUNCTION getgrandlivreview(date, date, integer)
  OWNER TO adbanking;
---------------------------------------------------------Fin Ticket AT-126----------------------------------------------
-----------------------------------------------Debut AT-125-------------------------------------------------------------

CREATE OR REPLACE FUNCTION ticket_AT_125() RETURNS INT AS
$BODY$
DECLARE
output_result INTEGER = 1;
column_exist INTEGER = 0;
BEGIN

 -- Met le type fonction zero pour les écrans associé à la gestion des écritures libres
 SELECT INTO column_exist count(*) from ecrans WHERE (nom_ecran LIKE '%Opd%' OR nom_ecran LIKE '%Opa%') AND fonction = 470;
  IF (column_exist > 0)  THEN
    UPDATE ecrans SET fonction = 0 WHERE (nom_ecran LIKE '%Opd%' OR nom_ecran LIKE '%Opa%') AND fonction = 470;
  END IF;

RETURN output_result;
END;
$BODY$
  LANGUAGE plpgsql VOLATILE
  COST 100;
ALTER FUNCTION ticket_AT_125()
  OWNER TO postgres;

  SELECT ticket_AT_125();
  DROP FUNCTION IF EXISTS ticket_AT_125();

-----------------------------------------------Fin  AT-125-------------------------------------------------------------
-----------------------------------------------Debut  AT-137-------------------------------------------------------------

CREATE OR REPLACE FUNCTION ticket_AT_137() RETURNS INT AS
$BODY$
DECLARE
output_result INTEGER = 1;
id_str_trad INTEGER = 0;
BEGIN
----Menu impression echeancier
IF NOT EXISTS (select * from menus where nom_menu = 'Cdo-8') THEN
  id_str_trad := maketraductionlangsyst('Impression échéancier credit');
  INSERT INTO menus (nom_menu, libel_menu, nom_pere, pos_hierarch, ordre, is_menu, is_cliquable)
  VALUES ('Cdo-8', id_str_trad, 'Cdo', 6, 8, FALSE,  FALSE);
  IF EXISTS(SELECT langue FROM adsys_langues_systeme WHERE code = 'en_GB') THEN
    INSERT INTO ad_traductions (id_str, langue, traduction) VALUES (id_str_trad, 'en_GB', 'Print schedule');
  END IF;
END IF;
----Ecran
IF NOT EXISTS (select * from ecrans where nom_ecran = 'Cdo-8') THEN
  INSERT INTO ecrans(nom_ecran, fichier, nom_menu, fonction)
  VALUES ('Cdo-8', 'modules/credit/consultdossier.php', 'Cdo-8', 140);
END IF;


RETURN output_result;
END;
$BODY$
LANGUAGE plpgsql VOLATILE
COST 100;
ALTER FUNCTION ticket_AT_137()
OWNER TO postgres;

SELECT ticket_AT_137();
DROP FUNCTION IF EXISTS ticket_AT_137();

-----------------------------------------------Fin  AT-137-------------------------------------------------------------