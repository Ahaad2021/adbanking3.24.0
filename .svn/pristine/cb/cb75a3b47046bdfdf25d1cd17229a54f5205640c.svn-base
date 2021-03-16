------------------ #514 : Correction Bug gestion des arrondis dans ADBanking ----------------------------------
CREATE OR REPLACE FUNCTION correction_arrondis(text) RETURNS void AS
$BODY$
DECLARE
  v_compte alias for $1;     
  ligne RECORD;
  v_count_compte integer;
  v_precision integer;
  v_current_id_exo integer;   


C1 CURSOR FOR select id_cpte,id_titulaire, solde, solde % 1 AS valeur_decimale, num_cpte_comptable from ad_cpt where solde %1 >0 ;  

BEGIN

  OPEN C1 ;
  FETCH C1 INTO ligne;
  
  SELECT INTO v_current_id_exo id_exo_compta FROM ad_exercices_compta WHERE etat_exo = 1 AND date('now') between date_deb_exo AND date_fin_exo;

  SELECT INTO v_precision  "precision" FROM devise WHERE code_devise  = (SELECT code_devise_reference FROM ad_agc);

  SELECT INTO v_count_compte count(num_cpte_comptable) from ad_cpt_comptable where num_cpte_comptable = v_compte;

    -- Si la precision = 0, il faut arrondir, et il faut aussi le compte parametre pour écritures de régularisations existe
  IF(v_precision = 0 AND v_count_compte = 1) THEN 
  
     RAISE INFO 'Traitement Arrondis' ;
    -- RAISE INFO 'Exercice en Cours Seulement : id_exo = %' , v_current_id_exo;
 WHILE FOUND LOOP
IF (ligne.valeur_decimale >=0.5) THEN

	UPDATE ad_cpt set solde  = solde + (1 - ligne.valeur_decimale) where id_cpte = ligne.id_cpte;

	INSERT INTO ad_his(type_fonction, date, login,id_ag) VALUES (510, now(), 'admin',  numagc());

	/* Ecriture Comptable  */
	
        INSERT INTO ad_ecriture (id_his, date_comptable, type_operation, libel_ecriture, id_jou,id_exo, ref_ecriture,id_ag)
        VALUES ((SELECT currval('ad_his_id_his_seq')),date(now()),270,(Select makeTraductionLangSyst('Correction arrondi')) , 1, v_current_id_exo, makeNumEcriture(1, v_current_id_exo), numagc());

        INSERT INTO ad_mouvement (id_ecriture, compte, cpte_interne_cli, sens, montant, date_valeur, devise,id_ag) VALUES
        ((SELECT currval('ad_ecriture_seq')),ligne.num_cpte_comptable,ligne.id_cpte, 'c', (1 - ligne.valeur_decimale), date(now()), (select code_devise_reference from ad_agc), numagc());

        INSERT INTO ad_mouvement (id_ecriture, compte, sens, montant, date_valeur, devise,id_ag) VALUES
        ((SELECT currval('ad_ecriture_seq')),v_compte, 'd', (1 - ligne.valeur_decimale), date(now()), (select code_devise_reference from ad_agc), numagc());


	/* MAJ ad_cpt_comptable */
	
        UPDATE ad_cpt_comptable set solde = solde + (1 - ligne.valeur_decimale) WHERE num_cpte_comptable = ligne.num_cpte_comptable;
        UPDATE ad_cpt_comptable set solde = solde - (1 - ligne.valeur_decimale) WHERE num_cpte_comptable = v_compte;

ELSE --valeur decimale est moins que 0.5
	
       UPDATE ad_cpt set solde  = solde - ligne.valeur_decimale where id_cpte = ligne.id_cpte;

       INSERT INTO ad_his(type_fonction, date, login,id_ag) VALUES (510, now(), 'admin',  numagc());

    /* Ecriture Comptable  */
	
        INSERT INTO ad_ecriture (id_his, date_comptable, type_operation, libel_ecriture, id_jou,id_exo, ref_ecriture,id_ag)
        VALUES ((SELECT currval('ad_his_id_his_seq')),date(now()),270,(Select makeTraductionLangSyst('Correction arrondi')) , 1, v_current_id_exo, makeNumEcriture(1, v_current_id_exo), numagc());

        ---on retire la valeur_decimale si c moins que 0.5
        INSERT INTO ad_mouvement (id_ecriture, compte, cpte_interne_cli, sens, montant, date_valeur, devise,id_ag) VALUES
        ((SELECT currval('ad_ecriture_seq')),ligne.num_cpte_comptable,ligne.id_cpte, 'd',  ligne.valeur_decimale, date(now()), (select code_devise_reference from ad_agc), numagc());

        INSERT INTO ad_mouvement (id_ecriture, compte, sens, montant, date_valeur, devise,id_ag) VALUES
        ((SELECT currval('ad_ecriture_seq')),v_compte, 'c',  ligne.valeur_decimale, date(now()), (select code_devise_reference from ad_agc), numagc());

    /* MAJ ad_cpt_comptable */
	
        UPDATE ad_cpt_comptable set solde = solde - ligne.valeur_decimale WHERE num_cpte_comptable = ligne.num_cpte_comptable;
        UPDATE ad_cpt_comptable set solde = solde + ligne.valeur_decimale WHERE num_cpte_comptable = v_compte;


	END IF; --FIN test valeur decimale
     

     
FETCH C1 INTO ligne;
  END LOOP;
   CLOSE C1;
   
    ELSE --SINON Test de precision et test de compte de regularisation
    
    RAISE INFO 'Pas de Traitement' ;
    RAISE INFO 'Valeur Precision de la devise de référence != 0 ou le compte de régularisation n''existe pas !';
    
  END IF; -- FIN Test de precision et test de compte de regularisation
  
  
RETURN;
END;
$BODY$
  LANGUAGE plpgsql VOLATILE COST 100;
ALTER FUNCTION correction_arrondis(text) OWNER TO postgres;

------------- Execution --------------------------
--SELECT correction_arrondis('4.8.9.1');
--------------------------------------------------



