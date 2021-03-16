------------- Ticket #537 : AJOUT NOUVEAU CHAMPS adsys_produit_epargne -------------------------
CREATE OR REPLACE FUNCTION patch_ticket_537()  RETURNS VOID AS $$
DECLARE

tableliste_ident_prod INTEGER = 0;
tableliste_ident_cpt INTEGER = 0;


BEGIN

tableliste_ident_prod := (select ident from tableliste where nomc like 'adsys_produit_epargne' order by ident desc limit 1);
tableliste_ident_cpt := (select ident from tableliste where nomc like 'ad_cpt' order by ident desc limit 1);

RAISE INFO 'AJOUT NOUVEAU CHAMP adsys_produit_epargne.tx_interet_max';
	IF NOT EXISTS(SELECT * FROM information_schema.columns WHERE table_name LIKE 'adsys_produit_epargne' AND column_name LIKE 'tx_interet_max') THEN
		ALTER TABLE adsys_produit_epargne ADD COLUMN tx_interet_max numeric(30,6) DEFAULT 0;
	END IF;

	IF EXISTS (SELECT * FROM d_tableliste WHERE nchmpc LIKE 'tx_interet_max' and tablen = tableliste_ident_prod) THEN
		RAISE INFO 'UPDATE tx_interet_max';
		UPDATE d_tableliste SET onslct = NULL, ispkey = NULL WHERE nchmpc LIKE 'tx_interet_max';
	END IF;

	IF NOT EXISTS(SELECT * FROM d_tableliste WHERE nchmpc LIKE 'tx_interet_max' and tablen = tableliste_ident_prod) THEN
		RAISE INFO 'INSERT tx_interet_max';
		INSERT INTO d_tableliste VALUES ((select max(ident) from d_tableliste) + 1, tableliste_ident_prod, 'tx_interet_max',makeTraductionLangSyst('Taux d''intérêt maximum'), true, NULL, 'prc', false, false, false);
	END IF;

	RAISE INFO 'AJOUT NOUVEAU CHAMP adsys_produit_epargne.is_produit_actif' ;
	IF NOT EXISTS(SELECT * FROM information_schema.columns WHERE table_name LIKE 'adsys_produit_epargne' AND column_name LIKE 'is_produit_actif') THEN
		ALTER TABLE adsys_produit_epargne ADD COLUMN is_produit_actif boolean DEFAULT TRUE;
	END IF;

	-- insert into "d_tableliste" if notExist
	IF NOT EXISTS(SELECT * FROM d_tableliste WHERE nchmpc LIKE 'is_produit_actif' and tablen = tableliste_ident_prod)
	THEN
		INSERT INTO d_tableliste(ident, tablen, nchmpc, nchmpl, isreq, ref_field, type, onslct, ispkey, traduit)
		VALUES ((select max(ident) from d_tableliste)+1, tableliste_ident_prod, 'is_produit_actif',
		maketraductionlangsyst('Est-ce que le produit est actif ?'), false, NULL, 'bol', false, false, false);
	END IF;

	-- Ajout champ ad_cpt.terme_cpte dans d_tableliste
	IF NOT EXISTS(SELECT * FROM d_tableliste WHERE nchmpc LIKE 'terme_cpte' and tablen = tableliste_ident_cpt)
	THEN
		INSERT INTO d_tableliste(ident, tablen, nchmpc, nchmpl, isreq, ref_field, type, onslct, ispkey, traduit)
		VALUES ((select max(ident) from d_tableliste)+1, tableliste_ident_cpt, 'terme_cpte',
		maketraductionlangsyst('Terme (mois, 0 si aucun)'), false, NULL, 'int', false, false, false);
	END IF;

	-- Mettre a jour tx_interet_max = tx_interet au moment de la creation :
	update adsys_produit_epargne set tx_interet_max = tx_interet where tx_interet_max is null OR tx_interet_max <= 0;

	-- Reprise des anciennes valeurs dans ad_cpt :
  update ad_cpt c
  set tx_interet_cpte = (select p.tx_interet from adsys_produit_epargne p where c.id_prod = p.id)
  where c.tx_interet_cpte is null;

  update ad_cpt c
  set terme_cpte = (select p.terme from adsys_produit_epargne p where c.id_prod = p.id)
  where c.terme_cpte is null;

  update ad_cpt c
  set freq_calcul_int_cpte = (select p.freq_calcul_int from adsys_produit_epargne p where c.id_prod = p.id)
  where c.freq_calcul_int_cpte is null;

  update ad_cpt c
  set mode_calcul_int_cpte = (select p.mode_calcul_int from adsys_produit_epargne p where c.id_prod = p.id)
  where c.mode_calcul_int_cpte is null;

END;
$$
LANGUAGE plpgsql;

-- ----------------------------------------------------------------------------
-- Execution
-- ----------------------------------------------------------------------------
SELECT patch_ticket_537();
DROP FUNCTION patch_ticket_537();