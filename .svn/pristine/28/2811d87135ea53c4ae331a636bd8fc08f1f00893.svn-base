------------- Ticket #676 :  ---------------------------------------

CREATE OR REPLACE FUNCTION patch_ticket_676() RETURNS void AS $BODY$
BEGIN

  IF EXISTS(SELECT count(1) from information_schema.tables WHERE table_name = 'ad_cpt_hist') THEN
	
	DROP TRIGGER IF EXISTS trig_before_update_ad_cpt ON ad_cpt;
	DROP TABLE ad_cpt_hist;
  
    CREATE TABLE ad_cpt_hist (
      "id" serial  NOT NULL,
      "date_action" timestamp DEFAULT now(),
      "id_cpte" int4 NOT NULL,
      "etat_cpte" int4,
      "solde" numeric(30,6) DEFAULT 0,
      "id_ag" int4 NOT NULL,
	  "id_his" integer,
	  "login" text,
	  "type_fonction" integer,
	  "id_titulaire" integer,
      PRIMARY KEY (id, id_ag)
    );
  END IF;

END;
$BODY$
LANGUAGE plpgsql VOLATILE COST 100;
ALTER FUNCTION patch_ticket_676() OWNER TO adbanking;

--------------------- Execution -----------------------------------
SELECT patch_ticket_676();
Drop function patch_ticket_676();
--------------------------------------------------------------------


CREATE OR REPLACE FUNCTION trig_insert_ad_cpt_hist() RETURNS TRIGGER AS $BODY$
  BEGIN
    IF (OLD.etat_cpte != NEW.etat_cpte OR OLD.solde != NEW.solde) THEN
		INSERT INTO ad_cpt_hist (date_action, id_cpte, etat_cpte, solde, id_ag)
		VALUES (NOW(), OLD.id_cpte, OLD.etat_cpte, OLD.solde, OLD.id_ag);
	END IF;
    RETURN NEW;
  END;
	$BODY$
LANGUAGE plpgsql VOLATILE COST 100;
ALTER FUNCTION trig_insert_ad_cpt_hist() OWNER TO postgres;

CREATE TRIGGER trig_before_update_ad_cpt BEFORE UPDATE ON ad_cpt
FOR EACH ROW EXECUTE PROCEDURE trig_insert_ad_cpt_hist();

----------------------- fin #676 ---------------------------------------------
