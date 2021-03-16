CREATE OR REPLACE FUNCTION update_mobile_banking_airtel()
  RETURNS INT AS
$$
DECLARE
  output_result    INTEGER = 1;
  tableliste_ident INTEGER = 0;
BEGIN
  RAISE NOTICE 'START';

  -- Ajout Airtel Rwanda --
  IF NOT EXISTS(SELECT *
                FROM ad_ewallet
                WHERE nom_prestataire = 'Airtel' AND code_prestataire = 'AIRTEL_RW' AND id_ag = numagc())
  THEN
    INSERT INTO ad_ewallet (id_prestataire, id_ag, nom_prestataire, code_prestataire, compte_comptable)
    VALUES (3, numagc(), 'Airtel', 'AIRTEL_RW', NULL);
    output_result := 2;
  END IF;

  -- Add field id_cpte in ad_abonnement for airtel account linking
  IF NOT EXISTS(SELECT *
                FROM information_schema.columns
                WHERE table_name = 'ad_abonnement' AND column_name = 'id_cpte')
  THEN
    ALTER TABLE ad_abonnement
      ADD COLUMN id_cpte INTEGER DEFAULT NULL;
    output_result := 2;
    ALTER TABLE ad_abonnement
      ADD CONSTRAINT "ad_abonnement_ad_cpt_id_cpte_fkey"
    FOREIGN KEY (id_cpte, id_ag) REFERENCES ad_cpt (id_cpte, id_ag)
    ON UPDATE NO ACTION ON DELETE NO ACTION;
  END IF;

  RAISE NOTICE 'END';
  RETURN output_result;

END;
$$
LANGUAGE plpgsql;

SELECT update_mobile_banking_airtel();
DROP FUNCTION update_mobile_banking_airtel();