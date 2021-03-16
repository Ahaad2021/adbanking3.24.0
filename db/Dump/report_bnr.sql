DROP FUNCTION IF EXISTS getCRDView(date);
DROP TYPE IF EXISTS crb_type CASCADE;
CREATE TYPE crb_type AS (
"ClientNumber"         INTEGER,
"Surname"              VARCHAR(100),
"ForemoreOrInitial1"   VARCHAR(100),
"ForenameOrInitial2"   VARCHAR(100),
"ForenameOrInitial3"   VARCHAR(100),
"IDType"               INTEGER,
"IDNumber"             VARCHAR(30),
"Nationality"          VARCHAR(50),
"TaxNo"                VARCHAR(30),
"DrivingLicense"       VARCHAR(30),
"SocialSecurityNumber" VARCHAR(30),
"HealthInsuranceNumber" VARCHAR(30),
"MaritalStatus"         VARCHAR(30),
"NomberOfDependants"    SMALLINT,
"Gender"                CHARACTER(1),
"DateOfBirth"           DATE,
"PlaceOfBirth"          VARCHAR(150),

"PostalAdressLine1Number" VARCHAR(20),
"PhysicalAdressLine1"     VARCHAR(250),
"PhysicalAdresseLine2"    VARCHAR(250),
"PhysicalAdressProvince"  VARCHAR(250),
"PhysicalAdressDistrict"  VARCHAR(250),
"Sector"                  VARCHAR(100),
"Country"                 VARCHAR(100),
"PhysicalAdressCell"      VARCHAR(250),

"EmailAdress"             VARCHAR(250),
"ResidentType"            CHARACTER(1),
"WorkPhone"               VARCHAR(30),
"HomePhone"               VARCHAR(30),
"MobilePhone"             VARCHAR(30),
"Fascimile"               VARCHAR(30),
"EmployerName"            VARCHAR(100),
"EmployeurAdresseLine1"   VARCHAR(100),
"EmployeurAdressLine2"    VARCHAR(100),
"EmployeurCountry"        VARCHAR(100),

"EmployeurTwon"           VARCHAR(50),
"Occupation"              VARCHAR(50),
"Income"                  NUMERIC(30,6),
"IncomeFrequency"         CHARACTER(1),
"LoanNumber"              VARCHAR(30),
"AccountType"            TEXT,
"AccountStatus"          TEXT,
"Classification"         TEXT,
"AccountOwner"           TEXT,
"CurrencyType"           CHARACTER(3),
"DateOpened"              DATE,
"UpdatedDate"             DATE,
"TermsDuration"           SMALLINT,
 "RepayementTerm"         TEXT,
 "ApprovedAmount"         NUMERIC(30,6),
 "CurrentBalance"         NUMERIC(30,6),
 "AvailableCredit"         NUMERIC(30,6),
 "CurrentBalanceIndicator"        CHARACTER(1),
 "ScheduleMonthlyPaiementAmount"  NUMERIC(30,6),
 "ActualPaymentAmount"           NUMERIC(30,6),
 "AmountPastDue"                  NUMERIC(30,6),
 "InstallementsInArrears"         INTEGER,
 "DaysInArreas"                   INTEGER,
 "DateClosed"                     DATE,
 "LastPayementDate"               DATE,
 "InterestRate"                   DOUBLE PRECISION,
 "FirstPayementDate"              DATE,
 "Nature"                         TEXT,
 "Category"                       TEXT,
 "SectorActivity"                 TEXT,
 "ApprovedDate"                   DATE,
 "FinalPayementDate"              DATE
);

CREATE OR REPLACE FUNCTION getCRDView(date)
  RETURNS SETOF crb_type AS
$BODY$
DECLARE
  date_arrete ALIAS FOR $1; 
  ligne_crb crb_type;
  --ligne RECORD;
BEGIN
   
-- Production des informations sur le client
CREATE TEMP TABLE infos_clients AS SELECT id_client as "ClientNumber", case statut_juridique WHEN 1 THEN pp_nom WHEN 2 THEN pm_raison_sociale ELSE gi_nom END  as "Surname",pp_prenom as "ForemoreOrInitial1",CASE id_client WHEN 1 THEN '' END as "ForenameOrInitial2",CASE id_client WHEN 1 THEN '' END as "ForenameOrInitial3",f.id as "IDType",pp_nm_piece_id as "IDNumber",pn.libel_pays as "Nationality",CASE id_client WHEN 1 THEN '' END as "TaxNo",CASE id_client WHEN 1 THEN '' END as "DrivingLicense",CASE id_client WHEN 1 THEN '' END as "SocialSecurityNumber",CASE id_client WHEN 1 THEN '' END as "HealthInsuranceNumber",CASE pp_etat_civil WHEN 1 THEN 'Single' WHEN 2 THEN 'Married' WHEN 3 THEN 'Widower' WHEN 4 THEN 'Divorced' ELSE '' END as "MaritalStatus",pp_nbre_enfant as "NomberOfDependants",CASE pp_sexe WHEN 1 THEN 'M' WHEN 2 THEN 'F' ELSE '' END as "Gender",pp_date_naissance as "DateOfBirth",pp_lieu_naissance as "PlaceOfBirth",code_postal as "PostalAdressLine1Number",adresse as "PhysicalAdressLine1" ,CASE id_client WHEN 1 THEN '' END as "PhysicalAdresseLine2", b.libel as "PhysicalAdressProvince" ,d.libel as "PhysicalAdressDistrict",loc3 as "Sector",p.libel_pays as "Country",CASE id_client WHEN 1 THEN '' END as "PhysicalAdressCell",email as "EmailAdress",CASE id_client WHEN 1 THEN 'O' ELSE 'T' END as "ResidentType",num_tel as "WorkPhone",CASE id_client WHEN 1 THEN '' END as "HomePhone",num_port as "MobilePhone",CASE id_client WHEN 1 THEN '' END as "Fascimile",pp_employeur as "EmployerName",CASE id_client WHEN 1 THEN '' END as "EmployeurAdresseLine1",CASE id_client WHEN 1 THEN '' END as "EmployeurAdressLine2",CASE id_client WHEN 1 THEN '' END as "EmployeurCountry",CASE id_client WHEN 1 THEN '' END as "EmployeurTwon",pp_fonction as "Occupation", pp_revenu as "Income", CASE id_client WHEN 1 THEN '' END as "IncomeFrequency" from ad_cli a LEFT JOIN adsys_localisation b ON (a.id_loc1 = b.id and  a.id_ag = b.id_ag) LEFT JOIN adsys_localisation d ON (a.id_loc2 = d.id)  LEFT JOIN adsys_type_piece_identite f ON (a.pp_type_piece_id = f.id) LEFT JOIN adsys_pays p ON (a.pays = p.id_pays) LEFT JOIN adsys_pays pn ON (a.pp_nationalite = pn.id_pays) WHERE a.date_adh <= date_arrete order by id_client;

-- credits
CREATE TEMP TABLE credits AS SELECT a.id_doss, a.cre_etat, a.etat, ((select code_institution from ad_agc)||'-'||a.id_doss||'-'||cre_id_cpte ) as "LoanNumber",CASE cre_mnt_octr WHEN 0 THEN '-' ELSE 'I' END as "AccountType" ,CASE etat WHEN 5 THEN 'A' WHEN 6 THEN 'C' WHEN 9 THEN 'W' ELSE '-' END as "AccountStatus",b.libel as "Classification",CASE id_dcr_grp_sol WHEN NULL THEN 'J' ELSE 'O' END as "AccountOwner",c.devise as "CurrencyType",date_dem as "DateOpened",cre_date_approb as "UpdatedDate",duree_mois as "TermsDuration",CASE WHEN (periodicite = 1) OR (periodicite = 3) OR (periodicite = 4) OR (periodicite = 7) THEN 'MTH' WHEN (periodicite = 8) THEN 'WKY' WHEN (periodicite = 2) THEN 'FNY' WHEN (periodicite = 5) THEN 'ANY' WHEN (periodicite = 6) THEN 'ONCE' END as "RepayementTerm",round(cre_mnt_octr) as "ApprovedAmount",round(sum(solde_cap)) AS "CurrentBalance",CASE a.id_doss WHEN 0 THEN '' END as "AvailableCredit",CASE cre_etat WHEN 1 THEN 'C' ELSE 'D' END as "CurrentBalanceIndicator",round((sum(mnt_cap) + sum(mnt_int))/duree_mois) as "ScheduleMonthlyPaiementAmount",(cre_date_debloc + (Interval '1 day')*30*duree_mois) as  "DateClosed",tx_interet as "InterestRate",CASE WHEN a.duree_mois <= 12 THEN 'Court Terme' WHEN (a.duree_mois > 12 AND a.duree_mois <= 60) THEN 'Moyen Terme' ELSE 'Long Terme' END as "Nature", CASE a.id_doss WHEN 0 THEN '' END as "Category",e.libel as "SectorActivity",cre_date_debloc as "ApprovedDate",(cre_date_debloc + (Interval '1 day')*30*duree_mois) as  "FinalPayementDate",id_client from adsys_objets_credits e,adsys_produit_credit c,ad_dcr a , adsys_etat_credits b, ad_etr d where cre_date_debloc <= date_arrete and obj_dem = e.id and (a.etat in (5,7,8,9,13) OR (a.etat = 6 and date_etat > getFinMois(date(date_arrete - Interval '1 month')))) and  a.cre_etat = b.id and c.id = a.id_prod and a.id_doss = d.id_doss 
group by  "LoanNumber","AccountType" ,"AccountStatus","Classification","AccountOwner","CurrencyType","DateOpened","UpdatedDate","ApprovedDate","TermsDuration","RepayementTerm",
"ApprovedAmount","AvailableCredit","CurrentBalanceIndicator","DateClosed","FinalPayementDate","InterestRate","Nature","Category","SectorActivity","ApprovedDate",a.id_client,a.id_doss,a.cre_etat,a.etat order by a.id_doss ;

-- Jour de retard 
CREATE TEMP TABLE retard AS  select id_doss, date_part('day',(date_arrete - min(date_ech))) as "DaysInArreas" from ad_etr where id_doss in (select id_doss from credits) and remb ='f' 
group by id_doss;

-- Montant et nombre d'echeance en retard
CREATE TEMP TABLE installementspast as SELECT id_doss,round(sum(solde_cap+solde_int)) as "AmountPastDue" ,count(*) as "InstallementsInArrears" FROM ad_etr WHERE id_doss in (SELECT id_doss FROM credits WHERE cre_etat > 1) AND remb = 'f' AND date_ech <= date_arrete 
group by id_doss; 

-- Infos sur les remboursements
CREATE TEMP TABLE payements as SELECT id_doss, SUM(CASE WHEN (date_remb > getFinMois(date(date_arrete - Interval '1 month')) AND  date_remb <= getFinMois(date(date_arrete))) THEN (mnt_remb_cap+mnt_remb_int) ELSE 0 END) as "ActualPaymentAmount", min(date_remb) as "FirstPayementDate", max(date_remb) as "LastPayementDate" FROM ad_sre WHERE id_doss in (SELECT id_doss FROM credits) AND date_remb <= date_arrete 
group by id_doss;

-- Infos credits
CREATE TEMP TABLE infos_credits AS  SELECT "LoanNumber"   , "AccountType" , "AccountStatus" , "Classification" , "AccountOwner" , "CurrencyType" ,     "DateOpened"     ,    "UpdatedDate"     , "TermsDuration" , "RepayementTerm","ApprovedAmount" , "CurrentBalance" , "AvailableCredit" , "CurrentBalanceIndicator" , "ScheduleMonthlyPaiementAmount" , "ActualPaymentAmount", "AmountPastDue","InstallementsInArrears" ,"DaysInArreas",     "DateClosed"    , "LastPayementDate", "InterestRate" , "FirstPayementDate" , "Nature" , "Category" , "SectorActivity" ,    "ApprovedDate"    , "FinalPayementDate" , id_client from credits a LEFT JOIN payements d on (a.id_doss = d.id_doss) LEFT JOIN retard b ON (a.id_doss = b.id_doss) LEFT JOIN installementspast c on (a.id_doss = c.id_doss);

  -- Resultat de la vue
 FOR ligne_crb IN SELECT  "ClientNumber","Surname","ForemoreOrInitial1","ForenameOrInitial2","ForenameOrInitial3","IDType","IDNumber","Nationality", "TaxNo","DrivingLicense","SocialSecurityNumber","HealthInsuranceNumber","MaritalStatus","NomberOfDependants","Gender","DateOfBirth","PlaceOfBirth",
"PostalAdressLine1Number","PhysicalAdressLine1","PhysicalAdresseLine2","PhysicalAdressProvince","PhysicalAdressDistrict","Sector","Country","PhysicalAdressCell",
"EmailAdress","ResidentType","WorkPhone","HomePhone","MobilePhone","Fascimile","EmployerName","EmployeurAdresseLine1","EmployeurAdressLine2","EmployeurCountry",
"EmployeurTwon","Occupation","Income","IncomeFrequency","LoanNumber"   , "AccountType" , "AccountStatus" , "Classification" , "AccountOwner" , "CurrencyType" ,"DateOpened","UpdatedDate","TermsDuration" , "RepayementTerm" , "ApprovedAmount" , "CurrentBalance" , "AvailableCredit" , "CurrentBalanceIndicator" , "ScheduleMonthlyPaiementAmount" , "ActualPaymentAmount" , "AmountPastDue" , "InstallementsInArrears" , "DaysInArreas",     "DateClosed"     , "LastPayementDate", "InterestRate" , "FirstPayementDate" , "Nature" , "Category" , "SectorActivity" ,    "ApprovedDate"    , "FinalPayementDate" FROM infos_clients a, infos_credits b WHERE "ClientNumber" = id_client
  LOOP
    RETURN NEXT ligne_crb;
  END LOOP;
RETURN;
END;
$BODY$
  LANGUAGE plpgsql VOLATILE;
ALTER FUNCTION getCRDView(date) OWNER TO adbanking;
 
