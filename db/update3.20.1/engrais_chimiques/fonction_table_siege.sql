  CREATE OR REPLACE FUNCTION engrais_chimiques_rapport_globalisation() RETURNS INT AS
$$
DECLARE
output_result INTEGER = 1;
tableliste_ident INTEGER = 0;

BEGIN

-------------------------------------------------------------------------

-- Creation table ec_beneficiaire + d_tableliste
 IF NOT EXISTS(SELECT * FROM information_schema.tables WHERE table_name = 'ec_situation_paiement') THEN

   CREATE TABLE ec_situation_paiement
(
  id serial NOT NULL,
  nom_province text,
  nom_commune text,
  libel_ag text, 
  id_produit integer, 
  libel_prod text, 
  qtite integer, 
  qtite_paye integer, 
  mnt_avance numeric(30,6), 
  mnt_solde numeric(30,6), 
  id_annee integer,
  id_saison integer,
  period integer,
  id_ag integer,
  CONSTRAINT ec_situation_paiement_pkey PRIMARY KEY (id)
);

END IF;


 IF NOT EXISTS(SELECT * FROM information_schema.tables WHERE table_name = 'ec_repartition_zone') THEN

   CREATE TABLE ec_repartition_zone
(
  id serial NOT NULL,
  id_province integer, 
  nom_province text,
  id_commune integer,  
  nom_commune text,
  nom_coopec text,
  id_zone integer,
  nom_zone text,
  id_prod integer,
  qtite integer,
  montant numeric(30,6),
  id_ag integer,
  CONSTRAINT ec_repartition_zone_pkey PRIMARY KEY (id)
);

END IF;

 IF NOT EXISTS(SELECT * FROM information_schema.tables WHERE table_name = 'ec_benef_paye') THEN

   CREATE TABLE ec_benef_paye
(
  id serial NOT NULL,
  nom_province text,
  nom_commune text,
  nom_zone text,
  nom_colline text,
  nom_coopec text,
  id_benef text,
  nom_prenom text,
  id_card text,
  id_prod integer,
  libel text,
  quantite integer,
  qtite_paye integer,
  montant_paye numeric(30,6),
  montant_avance numeric(30,6),
  montant_solde numeric(30,6),
  id_ag integer,
  CONSTRAINT ec_benef_paye_pkey PRIMARY KEY (id)
);

END IF;

-- Creation table ec_beneficiaire + d_tableliste
 IF NOT EXISTS(SELECT * FROM information_schema.tables WHERE table_name = 'ec_situation_paiement_historique') THEN

   CREATE TABLE ec_situation_paiement_historique
(
  id serial NOT NULL,
  nom_province text,
  nom_commune text,
  libel_ag text, 
  id_produit integer, 
  libel_prod text, 
  qtite integer, 
  qtite_paye integer, 
  mnt_avance numeric(30,6), 
  mnt_solde numeric(30,6), 
  id_annee integer,
  id_saison integer,
  period integer,
  id_ag integer,
  CONSTRAINT ec_situation_paiement_historique_pkey PRIMARY KEY (id)
);

END IF;


 IF NOT EXISTS(SELECT * FROM information_schema.tables WHERE table_name = 'ec_repartition_zone_historique') THEN

   CREATE TABLE ec_repartition_zone_historique
(
  id serial NOT NULL,
  id_province integer, 
  nom_province text,
  id_commune integer,  
  nom_commune text,
  nom_coopec text,
  id_zone integer,
  nom_zone text,
  id_prod integer,
  qtite integer,
  montant numeric(30,6),
  id_ag integer,
  CONSTRAINT ec_repartition_zone_historique_pkey PRIMARY KEY (id)
);

END IF;

 IF NOT EXISTS(SELECT * FROM information_schema.tables WHERE table_name = 'ec_benef_paye_historique') THEN

   CREATE TABLE ec_benef_paye_historique
(
  id serial NOT NULL,
  nom_province text,
  nom_commune text,
  nom_zone text,
  nom_colline text,
  nom_coopec text,
  id_benef text,
  nom_prenom text,
  id_card text,
  id_prod integer,
  libel text,
  quantite integer,
  qtite_paye integer,
  montant_paye numeric(30,6),
  montant_avance numeric(30,6),
  montant_solde numeric(30,6),
  id_ag integer,
  CONSTRAINT ec_benef_paye_historique_pkey PRIMARY KEY (id)
);

END IF;


RETURN output_result;

END;
$$
LANGUAGE plpgsql;


select engrais_chimiques_rapport_globalisation();

DROP FUNCTION IF EXISTS engrais_chimiques_rapport_globalisation();