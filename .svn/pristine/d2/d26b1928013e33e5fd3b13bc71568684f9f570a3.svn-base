<?Php

/**
 * produit
 * @package Credit
 */

require_once 'lib/html/HTML_GEN2.php';
require_once 'lib/html/FILL_HTML_GEN2.php';
require_once 'lib/misc/VariablesGlobales.php';
require_once 'lib/html/HtmlHeader.php';
require_once 'lib/dbProcedures/credit.php';

/**
 * affiche_HTML_produit : renvoi le code HTML pour afficher un produit de crédit
 *
 * @param mixed $id L'identifiant du produit de crédit
 * @access public
 * @return void Le code HTML
 */
function affiche_HTML_produit($id, $id_doss = NULL) {
  global $adsys;

  $whereCl=" where id=$id";
  // Tableau associatif de produit $Produit[$i]["champ"]
  $Produit = getProdInfo($whereCl, $id_doss); // TODO

  // On règle la monnaie courante pour une affichage correct de la devise
  setMonnaieCourante($Produit[0]["devise"]);

  $Myform = new HTML_GEN2(_("Détail du produit sélectionné"));
  
  if ($Produit[0]["mode_calc_int"] == '5') {
      $Myform->addTable("adsys_produit_credit", OPER_EXCLUDE, array('mode_perc_int','periodicite','freq_paiement_cap','ordre_remb','nbre_reechelon_auth','differe_ech_max','gs_cat','is_produit_actif'));
      
      $champs = array("id","libel","devise","tx_interet","mode_calc_int","mnt_min","mnt_max","type_duree_credit","duree_min_mois","duree_max_mois","prc_gar_num","prc_gar_mat","prc_gar_tot","prc_gar_encours","prc_assurance","mnt_assurance","typ_pen_pourc_dcr","prc_penalite_retard","mnt_penalite_jour","max_jours_compt_penalite","remb_cpt_gar", "mnt_frais","prc_frais","mnt_commission","prc_commission","differe_jours_max","delai_grace_jour","nb_jr_bloq_cre_avant_ech_max","approbation_obli","cpte_cpta_prod_cr_int","cpte_cpta_prod_cr_pen","cpte_cpta_prod_cr_gar","cpte_cpta_att_deb","report_arrondi","calcul_interet_differe","prelev_frais_doss","percep_frais_com_ass","differe_epargne_nantie","duree_nettoyage","ordre_remb_lcr","taux_frais_lcr","taux_min_frais_lcr","taux_max_frais_lcr","cpte_cpta_prod_cr_frais");
  } else {
  
    $Myform->addTable("adsys_produit_credit", OPER_NONE,NULL);

    // Tous les champs à afficher, dans le bon ordre, comme ça on peut réutiliser le même tableau partout
    $champs = array("id","libel","devise","tx_interet","mode_calc_int","mode_perc_int","mnt_min","mnt_max","type_duree_credit","periodicite","gs_cat","duree_min_mois","duree_max_mois","freq_paiement_cap","prc_gar_num","prc_gar_mat","prc_gar_tot","prc_gar_encours","prc_assurance","mnt_assurance","typ_pen_pourc_dcr","prc_penalite_retard","mnt_penalite_jour","max_jours_compt_penalite","nbre_reechelon_auth","ordre_remb","remb_cpt_gar", "mnt_frais","prc_frais","mnt_commission","prc_commission","differe_jours_max","differe_ech_max","delai_grace_jour","nb_jr_bloq_cre_avant_ech_max","approbation_obli","cpte_cpta_prod_cr_int","cpte_cpta_prod_cr_pen","cpte_cpta_prod_cr_gar","cpte_cpta_att_deb","report_arrondi","calcul_interet_differe","prelev_frais_doss","percep_frais_com_ass","differe_epargne_nantie");
  }

  // Définition des valeurs par défaut
  $defaultVal = new FILL_HTML_GEN2();
  $defaultVal->addFillClause("id_prod","adsys_produit_credit");
  $defaultVal->addCondition("id_prod","id",$id);
  $defaultVal->addManyFillFields("id_prod", OPER_INCLUDE, $champs);
  $defaultVal->fill($Myform);

  // Tous les champs sont non modifiables (disabled=true)
  foreach ($champs as $champ) {
    $Myform->setFieldProperties($champ, FIELDP_IS_LABEL, true);
    $Myform->setFieldProperties($champ,FIELDP_IS_REQUIRED,false);
  }

  // On rempli les champs
  if($Produit[0]["mode_calc_int"] != '5') {
    $Myform->setFieldProperties("periodicite",FIELDP_DEFAULT,$Produit[0]['periodicite']);
  }

  if ($Produit[0]['prc_commission'])
    $Myform->setFieldProperties("prc_commission",FIELDP_DEFAULT,$Produit[0]['prc_commission']*100);
  else
    $Myform->setFieldProperties("prc_commission",FIELDP_DEFAULT,0);
  $Myform->setFieldProperties("prc_gar_num", FIELDP_DEFAULT,$Produit[0]['prc_gar_num']*100);
  $Myform->setFieldProperties("prc_gar_mat", FIELDP_DEFAULT,$Produit[0]['prc_gar_mat']*100);
  $Myform->setFieldProperties("prc_gar_tot", FIELDP_DEFAULT,$Produit[0]['prc_gar_tot']*100);
  $Myform->setFieldProperties("prc_gar_encours", FIELDP_DEFAULT,$Produit[0]['prc_gar_encours']*100);
  $Myform->setFieldProperties("prc_assurance", FIELDP_DEFAULT,$Produit[0]['prc_assurance']*100);
  $Myform->setFieldProperties("tx_interet", FIELDP_DEFAULT,$Produit[0]['tx_interet']*100);
  $Myform->setFieldProperties("prc_penalite_retard", FIELDP_DEFAULT,$Produit[0]['prc_penalite_retard']*100);
  $Myform->setFieldProperties("mnt_assurance", FIELDP_DEFAULT,$Produit[0]['mnt_assurance']);
  $Myform->setFieldProperties("mnt_frais", FIELDP_DEFAULT,$Produit[0]['mnt_frais']);
  $Myform->setFieldProperties("prc_frais", FIELDP_DEFAULT,$Produit[0]['prc_frais']*100);
  $Myform->setFieldProperties("mnt_commission", FIELDP_DEFAULT,$Produit[0]['mnt_commission']);

  // Les boutons ajoutés
  $Myform->addFormButton(1,1,"ok",_("Ok"),TYPB_SUBMIT);
  $Myform->setFormButtonProperties("ok", BUTP_JS_EVENT, array("onClick"=>"window.close();"));

    /* Affiche des informations par section */
    $tmp = "<b>"._("Paramétrage général")."</b>";
    $Myform->addHTMLExtraCode("infosgen", $tmp);
    $Myform->setHTMLExtraCodeProperties("infosgen",HTMP_IN_TABLE, true);

    $tmp = "<b>"._("Paramétrage des intérêts et des pénalités")."</b>";
    $Myform->addHTMLExtraCode("infoscal", $tmp);
    $Myform->setHTMLExtraCodeProperties("infoscal",HTMP_IN_TABLE, true);

    $tmp = "<b>"._("Paramétrage des garanties et des assurances")."</b>";
    $Myform->addHTMLExtraCode("infosgar", $tmp);
    $Myform->setHTMLExtraCodeProperties("infosgar",HTMP_IN_TABLE, true);

    $tmp = "<b>"._("Paramétrage financier")."</b>";
    $Myform->addHTMLExtraCode("infosfin", $tmp);
    $Myform->setHTMLExtraCodeProperties("infosfin",HTMP_IN_TABLE, true);

    $tmp = "<b>"._("Paramétrage comptable")."</b>";
    $Myform->addHTMLExtraCode("infoscompta", $tmp);
    $Myform->setHTMLExtraCodeProperties("infoscompta",HTMP_IN_TABLE, true);
    
    if ($Produit[0]["mode_calc_int"] == '5') {
        $tmp = "<b>"._("Paramétrage Ligne de Crédit")."</b>";
        $Myform->addHTMLExtraCode("infoslcr", $tmp);
        $Myform->setHTMLExtraCodeProperties("infoslcr",HTMP_IN_TABLE, true);
    }

    //Ordre d'affichage des champs
    $ordre = array();
    $ordre = array_merge($ordre,array("infosgen","id","libel","devise","type_duree_credit","gs_cat","duree_min_mois","duree_max_mois","differe_jours_max","differe_ech_max","delai_grace_jour","nb_jr_bloq_cre_avant_ech_max","approbation_obli","calcul_interet_differe","prelev_frais_doss","percep_frais_com_ass","differe_epargne_nantie","report_arrondi","nbre_reechelon_auth", "ordre_remb", "remb_cpt_gar"));
    $ordre = array_merge($ordre,array("infoscal","tx_interet","mode_calc_int","mode_perc_int","periodicite","freq_paiement_cap","typ_pen_pourc_dcr","prc_penalite_retard","mnt_penalite_jour","max_jours_compt_penalite"));
    $ordre = array_merge($ordre,array("infosgar","prc_gar_num","prc_gar_mat","prc_gar_tot","prc_gar_encours","prc_assurance","mnt_assurance"));
    $ordre = array_merge($ordre,array("infosfin","mnt_min","mnt_max","mnt_frais","prc_frais","mnt_commission","prc_commission"));
    $ordre = array_merge($ordre,array("infoscompta","cpte_cpta_prod_cr_int","cpte_cpta_prod_cr_pen","cpte_cpta_prod_cr_gar","cpte_cpta_att_deb"));

    if ($Produit[0]["mode_calc_int"] == '5') {
        $ordre = array_merge($ordre,array("infoslcr","duree_nettoyage","ordre_remb_lcr","taux_frais_lcr","taux_min_frais_lcr","taux_max_frais_lcr","cpte_cpta_prod_cr_frais"));
        
        foreach (array('mode_perc_int','periodicite','freq_paiement_cap','ordre_remb','nbre_reechelon_auth','differe_ech_max','gs_cat') as $del_val) {
            if(($key = array_search($del_val, $ordre)) !== false) {
                unset($ordre[$key]);
            }
        }
    }    

  // Ordre d'affichage des champs
  $Myform->setOrder(NULL, $ordre);

  $Myform->buildHTML();
  echo $Myform->getHTML();
}

if (isset($id)) affiche_HTML_produit($id,$id_doss);

?>