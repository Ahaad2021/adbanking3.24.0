------------------------------- DEBUT : Ticket MB-153 -----------------------------------------------------------------------------------------------------------
CREATE OR REPLACE FUNCTION ticket_MB_153() RETURNS INT AS
$$
DECLARE
output_result INTEGER = 1;
  id_str_trad integer = 0;
  tableliste_ident integer = 0;

BEGIN

-- Check if table adsys_param_mouvement exist
IF NOT EXISTS(SELECT * FROM information_schema.tables WHERE table_name = 'adsys_param_mouvement') THEN

CREATE TABLE adsys_param_mouvement
(
  id serial NOT NULL,
  id_ag integer,
  type_opt integer,
  libelle text,
  preleve_frais boolean,
  deleted boolean DEFAULT FALSE,
  date_creation timestamp(6) without time zone,
  date_modification timestamp(6) without time zone,
  CONSTRAINT adsys_param_mouvement_pkey PRIMARY KEY (id, id_ag)
        )
WITH (
  OIDS=FALSE
);
  ALTER TABLE adsys_param_mouvement
  OWNER TO postgres;
END IF;


  -- Insertion dans tableliste
        IF NOT EXISTS(SELECT * FROM tableliste WHERE nomc = 'adsys_param_mouvement') THEN
        INSERT INTO tableliste VALUES ((select max(ident) from tableliste)+1, 'adsys_param_mouvement', makeTraductionLangSyst('"Paramétrage frais forfaitaire transactionel"'), true);
        RAISE NOTICE 'Données table adsys_param_mouvement rajoutés dans table tableliste';
        END IF;

        tableliste_ident := (select ident from tableliste where nomc like 'adsys_param_mouvement' order by ident desc limit 1);

          -- Insertion dans d_tableliste champ adsys_param_mouvement."type_opt"
        IF NOT EXISTS(SELECT * FROM d_tableliste WHERE nchmpc = 'type_opt' and tablen = tableliste_ident) THEN
          INSERT INTO d_tableliste VALUES ((select max(ident) from d_tableliste)+1, tableliste_ident, 'type_opt', makeTraductionLangSyst('Type opération'), true, NULL, 'lsb', true, false, false);
        END IF;

        IF NOT EXISTS(SELECT * FROM d_tableliste WHERE nchmpc = 'libelle' and tablen = tableliste_ident) THEN
          INSERT INTO d_tableliste VALUES ((select max(ident) from d_tableliste)+1, tableliste_ident, 'libelle', makeTraductionLangSyst('Libelle'), false, NULL, 'txt', false, false, false);
        END IF;

        IF NOT EXISTS(SELECT * FROM d_tableliste WHERE nchmpc = 'preleve_frais' and tablen = tableliste_ident) THEN
          INSERT INTO d_tableliste VALUES ((select max(ident) from d_tableliste)+1, tableliste_ident, 'preleve_frais', makeTraductionLangSyst('Prélève frais'), false, NULL, 'bol', false, false, false);
        END IF;


  --Insertion dans adsys_param_mouvement
  IF NOT EXISTS (SELECT * FROM adsys_param_mouvement WHERE type_opt = 10 AND preleve_frais = 't' AND deleted = 'f') THEN
        INSERT INTO adsys_param_mouvement (id_ag, type_opt, libelle, preleve_frais, deleted, date_creation, date_modification) VALUES (numagc(), 10, 'Remboursement capital sur crédits', TRUE, 'f', now(), null);
  END IF;
  IF NOT EXISTS (SELECT * FROM adsys_param_mouvement WHERE type_opt = 11 AND preleve_frais = 't' AND deleted = 'f') THEN
    INSERT INTO adsys_param_mouvement (id_ag, type_opt, libelle, preleve_frais, deleted, date_creation, date_modification) VALUES (numagc(), 11, 'Annulation remboursement capital sur crédits', TRUE, 'f', now(), null);
  END IF;
  IF NOT EXISTS (SELECT * FROM adsys_param_mouvement WHERE type_opt = 20 AND preleve_frais = 't' AND deleted = 'f') THEN
    INSERT INTO adsys_param_mouvement (id_ag, type_opt, libelle, preleve_frais, deleted, date_creation, date_modification) VALUES (numagc(), 20, 'Remboursement intérêts sur crédits', TRUE, 'f', now(), null);
  END IF;
  IF NOT EXISTS (SELECT * FROM adsys_param_mouvement WHERE type_opt = 21 AND preleve_frais = 't' AND deleted = 'f') THEN
    INSERT INTO adsys_param_mouvement (id_ag, type_opt, libelle, preleve_frais, deleted, date_creation, date_modification) VALUES (numagc(), 21, 'Annulation remboursement intérêts sur crédits', TRUE, 'f', now(), null);
  END IF;
  IF NOT EXISTS (SELECT * FROM adsys_param_mouvement WHERE type_opt = 30 AND preleve_frais = 't' AND deleted = 'f') THEN
    INSERT INTO adsys_param_mouvement (id_ag, type_opt, libelle, preleve_frais, deleted, date_creation, date_modification) VALUES (numagc(), 30, 'Remboursement pénalités', TRUE, 'f', now(), null);
  END IF;
  IF NOT EXISTS (SELECT * FROM adsys_param_mouvement WHERE type_opt = 31 AND preleve_frais = 't' AND deleted = 'f') THEN
    INSERT INTO adsys_param_mouvement (id_ag, type_opt, libelle, preleve_frais, deleted, date_creation, date_modification) VALUES (numagc(), 31, 'Annulation remboursement pénalités', TRUE, 'f', now(), null);
  END IF;
  IF NOT EXISTS (SELECT * FROM adsys_param_mouvement WHERE type_opt = 40 AND preleve_frais = 't' AND deleted = 'f') THEN
    INSERT INTO adsys_param_mouvement (id_ag, type_opt, libelle, preleve_frais, deleted, date_creation, date_modification) VALUES (numagc(), 40, 'Versement des intérêts sur compte epargne', TRUE, 'f', now(), null);
  END IF;
  IF NOT EXISTS (SELECT * FROM adsys_param_mouvement WHERE type_opt = 62 AND preleve_frais = 't' AND deleted = 'f') THEN
    INSERT INTO adsys_param_mouvement (id_ag, type_opt, libelle, preleve_frais, deleted, date_creation, date_modification) VALUES (numagc(), 62, 'Fermeture de compte par transfert', TRUE, 'f', now(), null);
  END IF;
  IF NOT EXISTS (SELECT * FROM adsys_param_mouvement WHERE type_opt = 70 AND preleve_frais = 't' AND deleted = 'f') THEN
    INSERT INTO adsys_param_mouvement (id_ag, type_opt, libelle, preleve_frais, deleted, date_creation, date_modification) VALUES (numagc(), 70, 'Compensation assurance', TRUE, 'f', now(), null);
  END IF;
  IF NOT EXISTS (SELECT * FROM adsys_param_mouvement WHERE type_opt = 80 AND preleve_frais = 't' AND deleted = 'f') THEN
    INSERT INTO adsys_param_mouvement (id_ag, type_opt, libelle, preleve_frais, deleted, date_creation, date_modification) VALUES (numagc(), 80, 'Libération parts sociales', TRUE, 'f', now(), null);
  END IF;
  IF NOT EXISTS (SELECT * FROM adsys_param_mouvement WHERE type_opt = 81 AND preleve_frais = 't' AND deleted = 'f') THEN
    INSERT INTO adsys_param_mouvement (id_ag, type_opt, libelle, preleve_frais, deleted, date_creation, date_modification) VALUES (numagc(), 81, 'Remboursement parts sociales', TRUE, 'f', now(), null);
  END IF;
  IF NOT EXISTS (SELECT * FROM adsys_param_mouvement WHERE type_opt = 90 AND preleve_frais = 't' AND deleted = 'f') THEN
    INSERT INTO adsys_param_mouvement (id_ag, type_opt, libelle, preleve_frais, deleted, date_creation, date_modification) VALUES (numagc(), 90, 'Perception frais adhésion', TRUE, 'f', now(), null);
  END IF;
  IF NOT EXISTS (SELECT * FROM adsys_param_mouvement WHERE type_opt = 100 AND preleve_frais = 't' AND deleted = 'f') THEN
    INSERT INTO adsys_param_mouvement (id_ag, type_opt, libelle, preleve_frais, deleted, date_creation, date_modification) VALUES (numagc(), 100, 'Perception des frais d''ouverture de compte', TRUE, 'f', now(), null);
  END IF;
  IF NOT EXISTS (SELECT * FROM adsys_param_mouvement WHERE type_opt = 110 AND preleve_frais = 't' AND deleted = 'f') THEN
    INSERT INTO adsys_param_mouvement (id_ag, type_opt, libelle, preleve_frais, deleted, date_creation, date_modification) VALUES (numagc(), 110, 'Pénalité rupture anticipée', TRUE, 'f', now(), null);
  END IF;
  IF NOT EXISTS (SELECT * FROM adsys_param_mouvement WHERE type_opt = 120 AND preleve_frais = 't' AND deleted = 'f') THEN
    INSERT INTO adsys_param_mouvement (id_ag, type_opt, libelle, preleve_frais, deleted, date_creation, date_modification) VALUES (numagc(), 120, 'Transfert entre comptes d''epargne', TRUE, 'f', now(), null);
  END IF;
  IF NOT EXISTS (SELECT * FROM adsys_param_mouvement WHERE type_opt = 123 AND preleve_frais = 't' AND deleted = 'f') THEN
    INSERT INTO adsys_param_mouvement (id_ag, type_opt, libelle, preleve_frais, deleted, date_creation, date_modification) VALUES (numagc(), 123, 'Restitution de la garantie numéraire', TRUE, 'f', now(), null);
  END IF;
  IF NOT EXISTS (SELECT * FROM adsys_param_mouvement WHERE type_opt = 124 AND preleve_frais = 't' AND deleted = 'f') THEN
    INSERT INTO adsys_param_mouvement (id_ag, type_opt, libelle, preleve_frais, deleted, date_creation, date_modification) VALUES (numagc(), 124, 'Recupération de la garantie numéraire', TRUE, 'f', now(), null);
  END IF;
  IF NOT EXISTS (SELECT * FROM adsys_param_mouvement WHERE type_opt = 140 AND preleve_frais = 't' AND deleted = 'f') THEN
    INSERT INTO adsys_param_mouvement (id_ag, type_opt, libelle, preleve_frais, deleted, date_creation, date_modification) VALUES (numagc(), 140, 'Retrait en espèces', TRUE, 'f', now(), null);
  END IF;
  IF NOT EXISTS (SELECT * FROM adsys_param_mouvement WHERE type_opt = 151 AND preleve_frais = 'f' AND deleted = 'f') THEN
    INSERT INTO adsys_param_mouvement (id_ag, type_opt, libelle, preleve_frais, deleted, date_creation, date_modification) VALUES (numagc(), 151, 'Frais de virement', FALSE, 'f', now(), null);
  END IF;
  IF NOT EXISTS (SELECT * FROM adsys_param_mouvement WHERE type_opt = 152 AND preleve_frais = 'f' AND deleted = 'f') THEN
    INSERT INTO adsys_param_mouvement (id_ag, type_opt, libelle, preleve_frais, deleted, date_creation, date_modification) VALUES (numagc(), 152, 'Frais de transfert', FALSE, 'f', now(), null);
  END IF;
  IF NOT EXISTS (SELECT * FROM adsys_param_mouvement WHERE type_opt = 160 AND preleve_frais = 't' AND deleted = 'f') THEN
    INSERT INTO adsys_param_mouvement (id_ag, type_opt, libelle, preleve_frais, deleted, date_creation, date_modification) VALUES (numagc(), 160, 'Dépôt espèces', TRUE, 'f', now(), null);
  END IF;
  IF NOT EXISTS (SELECT * FROM adsys_param_mouvement WHERE type_opt = 180 AND preleve_frais = 'f' AND deleted = 'f') THEN
    INSERT INTO adsys_param_mouvement (id_ag, type_opt, libelle, preleve_frais, deleted, date_creation, date_modification) VALUES (numagc(), 180, 'Frais d''activation du service SMS', FALSE, 'f', now(), null);
  END IF;
  IF NOT EXISTS (SELECT * FROM adsys_param_mouvement WHERE type_opt = 181 AND preleve_frais = 'f' AND deleted = 'f') THEN
    INSERT INTO adsys_param_mouvement (id_ag, type_opt, libelle, preleve_frais, deleted, date_creation, date_modification) VALUES (numagc(), 181, 'Frais forfaitaires mensuels SMS', FALSE, 'f', now(), null);
  END IF;
  IF NOT EXISTS (SELECT * FROM adsys_param_mouvement WHERE type_opt = 182 AND preleve_frais = 'f' AND deleted = 'f') THEN
    INSERT INTO adsys_param_mouvement (id_ag, type_opt, libelle, preleve_frais, deleted, date_creation, date_modification) VALUES (numagc(), 182, 'Frais transfert de compte à compte', FALSE, 'f', now(), null);
  END IF;
  IF NOT EXISTS (SELECT * FROM adsys_param_mouvement WHERE type_opt = 183 AND preleve_frais = 'f' AND deleted = 'f') THEN
    INSERT INTO adsys_param_mouvement (id_ag, type_opt, libelle, preleve_frais, deleted, date_creation, date_modification) VALUES (numagc(), 183, 'Frais transfert E-wallet', FALSE, 'f', now(), null);
  END IF;
  IF NOT EXISTS (SELECT * FROM adsys_param_mouvement WHERE type_opt = 184 AND preleve_frais = 'f' AND deleted = 'f') THEN
    INSERT INTO adsys_param_mouvement (id_ag, type_opt, libelle, preleve_frais, deleted, date_creation, date_modification) VALUES (numagc(), 184, 'Frais dépôt eWallet', FALSE, 'f', now(), null);
  END IF;
  IF NOT EXISTS (SELECT * FROM adsys_param_mouvement WHERE type_opt = 200 AND preleve_frais = 'f' AND deleted = 'f') THEN
    INSERT INTO adsys_param_mouvement (id_ag, type_opt, libelle, preleve_frais, deleted, date_creation, date_modification) VALUES (numagc(), 200, 'Frais de dossier de crédit', FALSE, 'f', now(), null);
  END IF;
  IF NOT EXISTS (SELECT * FROM adsys_param_mouvement WHERE type_opt = 201 AND preleve_frais = 't' AND deleted = 'f') THEN
    INSERT INTO adsys_param_mouvement (id_ag, type_opt, libelle, preleve_frais, deleted, date_creation, date_modification) VALUES (numagc(), 201, 'Annulation transfert des frais', TRUE, 'f', now(), null);
  END IF;
  IF NOT EXISTS (SELECT * FROM adsys_param_mouvement WHERE type_opt = 210 AND preleve_frais = 't' AND deleted = 'f') THEN
    INSERT INTO adsys_param_mouvement (id_ag, type_opt, libelle, preleve_frais, deleted, date_creation, date_modification) VALUES (numagc(), 210, 'Déboursement crédit', TRUE, 'f', now(), null);
  END IF;
  IF NOT EXISTS (SELECT * FROM adsys_param_mouvement WHERE type_opt = 211 AND preleve_frais = 't' AND deleted = 'f') THEN
    INSERT INTO adsys_param_mouvement (id_ag, type_opt, libelle, preleve_frais, deleted, date_creation, date_modification) VALUES (numagc(), 211, 'Annulation déboursement crédit', TRUE, 'f', now(), null);
  END IF;
  IF NOT EXISTS (SELECT * FROM adsys_param_mouvement WHERE type_opt = 220 AND preleve_frais = 't' AND deleted = 'f') THEN
    INSERT INTO adsys_param_mouvement (id_ag, type_opt, libelle, preleve_frais, deleted, date_creation, date_modification) VALUES (numagc(), 220, 'Transfert des garanties numéraires', TRUE, 'f', now(), null);
  END IF;
  IF NOT EXISTS (SELECT * FROM adsys_param_mouvement WHERE type_opt = 221 AND preleve_frais = 't' AND deleted = 'f') THEN
    INSERT INTO adsys_param_mouvement (id_ag, type_opt, libelle, preleve_frais, deleted, date_creation, date_modification) VALUES (numagc(), 221, 'Annulation transfert des garanties numéraires', TRUE, 'f', now(), null);
  END IF;
  IF NOT EXISTS (SELECT * FROM adsys_param_mouvement WHERE type_opt = 230 AND preleve_frais = 't' AND deleted = 'f') THEN
    INSERT INTO adsys_param_mouvement (id_ag, type_opt, libelle, preleve_frais, deleted, date_creation, date_modification) VALUES (numagc(), 230, 'Transfert des assurances', TRUE, 'f', now(), null);
  END IF;
  IF NOT EXISTS (SELECT * FROM adsys_param_mouvement WHERE type_opt = 231 AND preleve_frais = 't' AND deleted = 'f') THEN
    INSERT INTO adsys_param_mouvement (id_ag, type_opt, libelle, preleve_frais, deleted, date_creation, date_modification) VALUES (numagc(), 231, 'Annulation transfert des assurances', TRUE, 'f', now(), null);
  END IF;
  IF NOT EXISTS (SELECT * FROM adsys_param_mouvement WHERE type_opt = 330 AND preleve_frais = 't' AND deleted = 'f') THEN
    INSERT INTO adsys_param_mouvement (id_ag, type_opt, libelle, preleve_frais, deleted, date_creation, date_modification) VALUES (numagc(), 330, 'Recouvrement de frais en attente', TRUE, 'f', now(), null);
  END IF;
  IF NOT EXISTS (SELECT * FROM adsys_param_mouvement WHERE type_opt = 360 AND preleve_frais = 't' AND deleted = 'f') THEN
    INSERT INTO adsys_param_mouvement (id_ag, type_opt, libelle, preleve_frais, deleted, date_creation, date_modification) VALUES (numagc(), 360, 'Perception commissions de déboursement', TRUE, 'f', now(), null);
  END IF;
  IF NOT EXISTS (SELECT * FROM adsys_param_mouvement WHERE type_opt = 361 AND preleve_frais = 't' AND deleted = 'f') THEN
    INSERT INTO adsys_param_mouvement (id_ag, type_opt, libelle, preleve_frais, deleted, date_creation, date_modification) VALUES (numagc(), 361, 'Annulation perception commissions de déboursement', TRUE, 'f', now(), null);
  END IF;
  IF NOT EXISTS (SELECT * FROM adsys_param_mouvement WHERE type_opt = 370 AND preleve_frais = 't' AND deleted = 'f') THEN
    INSERT INTO adsys_param_mouvement (id_ag, type_opt, libelle, preleve_frais, deleted, date_creation, date_modification) VALUES (numagc(), 370, 'Virement banque pour le compte d''un client', TRUE, 'f', now(), null);
  END IF;
  IF NOT EXISTS (SELECT * FROM adsys_param_mouvement WHERE type_opt = 410 AND preleve_frais = 't' AND deleted = 'f') THEN
    INSERT INTO adsys_param_mouvement (id_ag, type_opt, libelle, preleve_frais, deleted, date_creation, date_modification) VALUES (numagc(), 410, 'Recouvrement sur crédit en perte', TRUE, 'f', now(), null);
  END IF;
  IF NOT EXISTS (SELECT * FROM adsys_param_mouvement WHERE type_opt = 411 AND preleve_frais = 't' AND deleted = 'f') THEN
    INSERT INTO adsys_param_mouvement (id_ag, type_opt, libelle, preleve_frais, deleted, date_creation, date_modification) VALUES (numagc(), 411, 'Annulation recouvrement sur crédit en perte', TRUE, 'f', now(), null);
  END IF;
  IF NOT EXISTS (SELECT * FROM adsys_param_mouvement WHERE type_opt = 420 AND preleve_frais = 't' AND deleted = 'f') THEN
    INSERT INTO adsys_param_mouvement (id_ag, type_opt, libelle, preleve_frais, deleted, date_creation, date_modification) VALUES (numagc(), 420, 'Retrait par chèque sur compte epargne', TRUE, 'f', now(), null);
  END IF;
  IF NOT EXISTS (SELECT * FROM adsys_param_mouvement WHERE type_opt = 421 AND preleve_frais = 't' AND deleted = 'f') THEN
    INSERT INTO adsys_param_mouvement (id_ag, type_opt, libelle, preleve_frais, deleted, date_creation, date_modification) VALUES (numagc(), 421, 'Virement client sur la banque', TRUE, 'f', now(), null);
  END IF;
  IF NOT EXISTS (SELECT * FROM adsys_param_mouvement WHERE type_opt = 440 AND preleve_frais = 't' AND deleted = 'f') THEN
    INSERT INTO adsys_param_mouvement (id_ag, type_opt, libelle, preleve_frais, deleted, date_creation, date_modification) VALUES (numagc(), 440, 'Ajustement du solde d''un compte d''epargne en faveur du client', TRUE, 'f', now(), null);
  END IF;
  IF NOT EXISTS (SELECT * FROM adsys_param_mouvement WHERE type_opt = 442 AND preleve_frais = 't' AND deleted = 'f') THEN
    INSERT INTO adsys_param_mouvement (id_ag, type_opt, libelle, preleve_frais, deleted, date_creation, date_modification) VALUES (numagc(), 442, 'Ajustement du solde d''un compte d''epargne en faveur de l''IMF', TRUE, 'f', now(), null);
  END IF;
  IF NOT EXISTS (SELECT * FROM adsys_param_mouvement WHERE type_opt = 470 AND preleve_frais = 'f' AND deleted = 'f') THEN
    INSERT INTO adsys_param_mouvement (id_ag, type_opt, libelle, preleve_frais, deleted, date_creation, date_modification) VALUES (numagc(), 470, 'Perception des frais de dossier découvert', FALSE, 'f', now(), null);
  END IF;
  IF NOT EXISTS (SELECT * FROM adsys_param_mouvement WHERE type_opt = 471 AND preleve_frais = 't' AND deleted = 'f') THEN
    INSERT INTO adsys_param_mouvement (id_ag, type_opt, libelle, preleve_frais, deleted, date_creation, date_modification) VALUES (numagc(), 471, 'Perception des intérets débiteurs', TRUE, 'f', now(), null);
  END IF;
  IF NOT EXISTS (SELECT * FROM adsys_param_mouvement WHERE type_opt = 472 AND preleve_frais = 'f' AND deleted = 'f') THEN
    INSERT INTO adsys_param_mouvement (id_ag, type_opt, libelle, preleve_frais, deleted, date_creation, date_modification) VALUES (numagc(), 472, 'Perception des frais de chèquier', FALSE, 'f', now(), null);
  END IF;
  IF NOT EXISTS (SELECT * FROM adsys_param_mouvement WHERE type_opt = 508 AND preleve_frais = 't' AND deleted = 'f') THEN
    INSERT INTO adsys_param_mouvement (id_ag, type_opt, libelle, preleve_frais, deleted, date_creation, date_modification) VALUES (numagc(), 508, 'Virement national', TRUE, 'f', now(), null);
  END IF;
  IF NOT EXISTS (SELECT * FROM adsys_param_mouvement WHERE type_opt = 505 AND preleve_frais = 'f' AND deleted = 'f') THEN
    INSERT INTO adsys_param_mouvement (id_ag, type_opt, libelle, preleve_frais, deleted, date_creation, date_modification) VALUES (numagc(), 505, 'Frais de refus chèque (client)', FALSE, 'f', now(), null);
  END IF;
  IF NOT EXISTS (SELECT * FROM adsys_param_mouvement WHERE type_opt = 510 AND preleve_frais = 't' AND deleted = 'f') THEN
    INSERT INTO adsys_param_mouvement (id_ag, type_opt, libelle, preleve_frais, deleted, date_creation, date_modification) VALUES (numagc(), 510, 'Perception frais de crédit direct sauf bonne fin', TRUE, 'f', now(), null);
  END IF;
  IF NOT EXISTS (SELECT * FROM adsys_param_mouvement WHERE type_opt = 512 AND preleve_frais = 't' AND deleted = 'f') THEN
    INSERT INTO adsys_param_mouvement (id_ag, type_opt, libelle, preleve_frais, deleted, date_creation, date_modification) VALUES (numagc(), 512, 'Retrait cash par chèque interne', TRUE, 'f', now(), null);
  END IF;

        RETURN output_result;
END;
$$
LANGUAGE plpgsql;

SELECT ticket_MB_153();
DROP FUNCTION ticket_MB_153();
------------------------------- FIN : Ticket MB-153 -----------------------------------------------------------------------------------------------------------