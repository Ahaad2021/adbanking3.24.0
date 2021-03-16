/*
  Fonction appelée avant l'insertion d'un ordre permanent pour remplir le champ date_proch_exe
*/
CREATE OR REPLACE FUNCTION trig_insert_ord_perm() RETURNS trigger AS '
  BEGIN
    NEW.date_proch_exe = NEW.date_prem_exe;
    RETURN NEW;
  END;
' LANGUAGE plpgsql;

/*
  Fonction appelée avant l'update d'un ordre permanent pour remplir le champ date_proch_exe
*/
CREATE OR REPLACE FUNCTION trig_update_ord_perm() RETURNS trigger AS '
  BEGIN
    IF (NEW.date_prem_exe != OLD.date_prem_exe AND NEW.date_prem_exe >= now()) THEN
      NEW.date_proch_exe = NEW.date_prem_exe;
    END IF;
    IF ((NEW.date_dern_exe_th != OLD.date_dern_exe_th OR OLD.date_dern_exe_th IS NULL) AND NEW.date_dern_exe_th >= NEW.date_proch_exe) THEN
      SELECT INTO NEW.date_proch_exe ordreperm_proch_exe(NEW.date_dern_exe_th,OLD.interv,OLD.periodicite);
    END IF;
    RETURN NEW;
  END;
' LANGUAGE plpgsql;

/*
  Fonction appelée après chaque modification d'une entrée de la table ad_cpt.
  Elle met à jour le solde de calcul intérêt si ce dernier > au solde courant et que mode de calcul est 'Sur solde courant le plus bas'
*/
CREATE OR REPLACE FUNCTION trig_calcul_solde_temps_reel() RETURNS trigger AS '
  BEGIN
    IF NEW.mode_calcul_int_cpte = 3 AND NEW.solde < NEW.solde_calcul_interets THEN
      -- Il ne faut faire les traitements que si le mode de calcul des intérêts est "solde courant le plus bas"
      -- Et si le solde courant est plus petit que le solde de calcul précédent
       NEW.solde_calcul_interets := NEW.solde;
       NEW.date_solde_calcul_interets := date(now());
    END IF;
    RETURN NEW;
  END;
' LANGUAGE 'plpgsql';

/*
 * Fonction appellée après chaque modification de la la table ad_cpt //TODO le faire que si le champ solde est modifié
 * Elle fait les traitements nécessaires à la gestion des découverts et plus particulièrement à l'annulation automatique de découvert.
 * Voir le cahier des charges à ce sujet : https://devel.adbanking.org/wiki/CdCh/EparGne/Decouverts
 */
CREATE OR REPLACE FUNCTION trig_utilisation_decouvert() RETURNS trigger AS '
  DECLARE
    old_solde_dispo NUMERIC(30,6); -- le solde disponible avant mouvement (hors découvert)
    new_solde_dispo NUMERIC(30,6); -- le solde disponible après mouvement (hors découvert)
    annulation_auto BOOLEAN; -- le flag disant si le produit est configuré pour l annulation automatique
    new_decouvert_max NUMERIC(30,6); -- le nouveau découvert autorisé pour le client
    date_util TIMESTAMP; -- la date de première utilisation du découvert

  BEGIN
    -- On calcule les soldes disponibles avant et après mouvement
	SELECT INTO old_solde_dispo (OLD.solde - OLD.mnt_bloq - OLD.mnt_min_cpte);

    SELECT INTO new_solde_dispo (NEW.solde - NEW.mnt_bloq - NEW.mnt_min_cpte);

    IF new_solde_dispo < 0 AND old_solde_dispo >= 0 THEN
      -- Le découvert commence à être utilisé
       NEW.decouvert_date_util := date(now());

    ELSIF old_solde_dispo < 0 AND new_solde_dispo > old_solde_dispo THEN
      SELECT INTO annulation_auto decouvert_annul_auto FROM adsys_produit_epargne WHERE id_ag=NumAgc() AND id = NEW.id_prod;
      IF annulation_auto THEN
        -- Et si on a activé l annulation automatique du découvert dans le produit
        IF (new_solde_dispo < 0) THEN
          new_decouvert_max := NEW.decouvert_max + (old_solde_dispo - new_solde_dispo);
        ELSE
          -- On ne rembourse le découvert qu à raison de ce qu il a été utilisé
          new_decouvert_max := NEW.decouvert_max + old_solde_dispo;
        END IF;
        IF new_decouvert_max <= 0 THEN
          -- Le client a remboursé complètement son découvert, donc la date d utilisation doit revenir à NULL
          new_decouvert_max := 0;
          date_util := NULL;
        ELSE
          -- On recopie la date d utilisation
          date_util := NEW.decouvert_date_util;
        END IF;
      NEW.decouvert_date_util := date_util;
	  NEW.decouvert_max := new_decouvert_max;
      END IF;
    END IF;

    RETURN NEW;
  END;
' LANGUAGE 'plpgsql';

/*
Fonction appelée après chaque modification d'une entrée de la table ad_cpt_comptable.
Elle insert une nouveau libellé dans ad_libelle pour gérer l'historique des modifications d'un libellé comptable
*/
CREATE OR REPLACE FUNCTION trig_insert_ad_libelle() RETURNS trigger AS '
	DECLARE
		old_ref_libel TEXT;
	BEGIN
		SELECT INTO old_ref_libel id_libelle FROM ad_libelle WHERE id_ag=NumAgc() AND ident=OLD.num_cpte_comptable AND date_modification=(SELECT CURRENT_DATE);
		-- Si pas fausse update (Nouveau libellé différent d ancien libellé)
		IF (NEW.libel_cpte_comptable <> OLD.libel_cpte_comptable) THEN
			IF NOT FOUND THEN
				INSERT INTO ad_libelle (ident, libelle, date_modification, type_libelle, id_ag) VALUES (OLD.num_cpte_comptable, OLD.libel_cpte_comptable, (SELECT CURRENT_DATE), ''ad_cpt_comptable'', NumAgc());
			ELSE
				UPDATE ad_libelle SET libelle = OLD.libel_cpte_comptable WHERE ident = OLD.num_cpte_comptable AND id_ag = NumAgc();
			END IF;
		END IF;
		RETURN NEW;
	END;
' LANGUAGE 'plpgsql';

/*
Fonction appelée après chaque insertion d'une entrée de la table ad_mouvement.
Elle insert des nouvelles données dans ad_flux_compta pour avoir un resumé des  ecritures et mouvements comptables
*/
CREATE OR REPLACE FUNCTION trig_insert_ad_flux_compta() RETURNS trigger AS $$
	DECLARE
		enr_his RECORD ;
		enr_ecriture RECORD ;
	BEGIN
    -- Travaille sur l'ajout.
  IF (TG_OP = 'INSERT') THEN
   SELECT  INTO enr_ecriture id_ecriture, id_his, libel_ecriture, date_comptable,type_operation,id_jou,id_exo, ref_ecriture FROM ad_ecriture WHERE id_ecriture=NEW.id_ecriture;

  SELECT INTO enr_his  id_his,id_client,type_fonction,infos FROM ad_his WHERE id_his=enr_ecriture.id_his;
  INSERT INTO ad_flux_compta (id_his,id_client,type_fonction,infos,id_ecriture,libel_ecriture, date_comptable,type_operation,id_jou,id_exo, ref_ecriture,id_mouvement,compte,sens,devise,montant,id_ag,consolide) VALUES (enr_his.id_his,enr_his.id_client,enr_his.type_fonction,enr_his.infos,enr_ecriture.id_ecriture, enr_ecriture.libel_ecriture, enr_ecriture.date_comptable,enr_ecriture.type_operation,enr_ecriture.id_jou,enr_ecriture.id_exo, enr_ecriture.ref_ecriture,NEW.id_mouvement,NEW.compte,NEW.sens,NEW.devise,NEW.montant,NEW.id_ag,NEW.consolide);
  END IF;

RETURN NULL;
	END;
$$  LANGUAGE 'plpgsql';
/*
  Fonction appelée lors de la mise à jour du champ passage_perte_automatique dans ad_agc
*/
CREATE OR REPLACE FUNCTION proc_ad_agc_passage_perte_automatique() RETURNS trigger AS '
DECLARE
 etat_perte RECORD;
 etat_radier RECORD;

BEGIN
SELECT INTO etat_perte id, id_etat_prec FROM adsys_etat_credits WHERE nbre_jours = -1 and id_ag = NEW.id_ag;
SELECT INTO etat_radier id, id_etat_prec FROM adsys_etat_credits WHERE nbre_jours = -2 and id_ag = NEW.id_ag;
IF (OLD.passage_perte_automatique != NEW.passage_perte_automatique) THEN
   IF (NEW.passage_perte_automatique =''f'' ) THEN -- De passage en perte automatique à passage en perte manuelle
 	UPDATE adsys_etat_credits SET id_etat_prec = NULL where nbre_jours = -1 and id_ag = NEW.id_ag;
 	UPDATE adsys_etat_credits SET id_etat_prec = etat_perte.id_etat_prec where nbre_jours = -2 and id_ag = NEW.id_ag;
 	UPDATE adsys_etat_credits SET id_etat_prec = etat_radier.id where nbre_jours = -1 and id_ag = NEW.id_ag;
   ELSE -- De passage en perte manuelle à passage en perte automatique
	UPDATE adsys_etat_credits SET id_etat_prec = NULL where nbre_jours = -2 and id_ag = NEW.id_ag;
	UPDATE adsys_etat_credits SET id_etat_prec = etat_radier.id_etat_prec where nbre_jours = -1 and id_ag = NEW.id_ag;
   END IF;
END IF;
RETURN NEW;
END;
' LANGUAGE plpgsql;
CREATE OR REPLACE FUNCTION trig_insert_ad_dcr_hist() RETURNS trigger AS '
	BEGIN
	    INSERT INTO ad_dcr_hist
	      (date_action, id_doss, etat, cre_etat, cre_mnt_deb, id_ag)
	    VALUES
	      ( NOW(), OLD.id_doss, OLD.etat, OLD.cre_etat, OLD.cre_mnt_deb, OLD.id_ag);
	    RETURN NEW;
    END;
' LANGUAGE 'plpgsql';
CREATE OR REPLACE FUNCTION trig_insert_ad_cpt_hist() RETURNS trigger AS '
	BEGIN
	    INSERT INTO ad_cpt_hist
	      (date_action, id_cpte, etat_cpte, solde, id_ag)
	    VALUES
	      (NOW(), OLD.id_cpte, OLD.etat_cpte, OLD.solde, OLD.id_ag);
	    RETURN NEW;
    END;
' LANGUAGE 'plpgsql';

CREATE OR REPLACE FUNCTION gar_id_cpte_nantie_not_null() RETURNS TRIGGER AS $$
DECLARE
  etat_credit integer ;
BEGIN
	SELECT INTO etat_credit etat FROM ad_dcr where id_ag = numagc() and id_doss = NEW.id_doss;
    IF (etat_credit <> 10) THEN
       IF NEW.etat_gar in (1,3) and NEW.gar_num_id_cpte_nantie is   null  AND NEW.type_gar = 1 THEN 
       	 RAISE EXCEPTION '(adbanking) le compte interne d'' une  garantie numéraire ne peut pas être nul : id_agar =% ,id_doss = %', NEW.id_gar,NEW.id_doss;
       END IF;
    END IF;
    RETURN NEW;
END;
$$ LANGUAGE plpgsql; 
CREATE TRIGGER gar_id_cpte_nantie_not_null BEFORE INSERT OR UPDATE ON "ad_gar" 
  FOR EACH ROW 
  EXECUTE PROCEDURE gar_id_cpte_nantie_not_null();
