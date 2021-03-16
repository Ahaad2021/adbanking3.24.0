CREATE OR REPLACE FUNCTION script_creation_table() RETURNS INT AS
$$
DECLARE
output_result INTEGER = 1;
  d_tableliste_str integer = 0;
  tableliste_ident integer = 0;

  BEGIN
IF NOT EXISTS(SELECT * FROM information_schema.tables WHERE table_name = 'ag_comm_hist_resumer') THEN

   CREATE TABLE ag_comm_hist_resumer
(
  id_hist_res serial NOT NULL,
  date_creation timestamp without time zone,
  version_set text NOT NULL,
  type_comm INTEGER NOT NULL,
  type_transaction INTEGER NOT NULL,
  CONSTRAINT ag_comm_hist_resumer_pkey PRIMARY KEY (id_hist_res)
);

END IF;

IF NOT EXISTS(SELECT * FROM information_schema.columns WHERE table_name = 'ag_commission_hist' AND column_name = 'type_transaction') THEN
	ALTER TABLE ag_commission_hist ADD COLUMN type_transaction INTEGER DEFAULT NULL;
END IF;

  RETURN output_result;
END;
$$
LANGUAGE plpgsql;

SELECT script_creation_table();
DROP FUNCTION script_creation_table();

