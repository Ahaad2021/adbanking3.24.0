<?php
/* vim: set expandtab softtabstop=2 shiftwidth=2 foldmethod=marker: */
/* 
 * Cette opération comprends les écrans :
 * - Mac-1 : Consultation d'un client
 * - Mac-2 : Affichage des informations financières
 * @since 22/01/2015
 * @package Clients
 **/

//error_reporting(E_ALL);
//ini_set("display_errors", "on");

require_once('lib/misc/tableSys.php');

// Multi agence includes
require_once 'ad_ma/app/controllers/misc/VariablesSessionRemote.php';
require_once 'ad_ma/app/models/AgenceRemote.php';
require_once 'ad_ma/app/models/Agence.php';
require_once 'ad_ma/app/models/Audit.php';
require_once 'ad_ma/app/models/Client.php';
require_once 'ad_ma/app/models/Compta.php';
require_once 'ad_ma/app/models/Compte.php';
require_once 'ad_ma/app/models/Credit.php';
require_once 'ad_ma/app/models/Devise.php';
require_once 'ad_ma/app/models/Divers.php';
require_once 'ad_ma/app/models/Epargne.php';
require_once 'ad_ma/app/models/Guichet.php';
require_once 'ad_ma/app/models/Historique.php';
require_once 'ad_ma/app/models/Parametrage.php';
require_once 'ad_ma/app/models/TireurBenef.php';

require_once 'ad_ma/app/views/externe/suivi_credit_distant.php';

require_once "lib/html/HTML_menu_gen.php";
require_once 'lib/html/FILL_HTML_GEN2.php';
require_once 'lib/html/HTML_champs_extras.php';

/*{{{ Mac-1 : Consultation client */
if ($global_nom_ecran == "Mac-1") {
      
  // Begin remote transaction
  $pdo_conn->beginTransaction();
  
  // Enregistrement l'accès au menu
  // Ajout dans l'historique, si c'est la première fois qu'on arrive à cet écran
  ajout_historique(193, NULL, 'Opération en déplacé > Consultation client : ' . $global_remote_id_agence . '-' . $global_remote_id_client, $global_nom_login, date("r"), NULL);
  
  // Init class
  $ClientObj = new Client($pdo_conn, $global_remote_id_agence);
  
  $CLI = $ClientObj->getClientDatas($global_remote_id_client);
  
  $myForm = new HTML_GEN2(_("Consultation du client"));

  $Order = array ("etat", "nbre_parts", "statut_juridique", "qualite", "id_client", "anc_id_client", "date_adh", "date_crea", "langue_correspondance", "gestionnaire");

  $labels = array("id_client" => "", "statut_juridique" => "", "etat" => "");
  if ( $CLI["statut_juridique"] == 1) { // Personne physique
    
    // Récupération de la photo et de la signature du client
    $IMGS = $ClientObj->getImagesClient($global_id_client);

    $photo_client = $IMGS["photo"];
    $signature_client = $IMGS["signature"];
    
    $myForm->addField("signature",_("Spécimen de signature"),TYPC_IMG);
    if ($signature_client != "") {
      $myForm->setFieldProperties('signature', FIELDP_IMAGE_URL, $signature_client);
    }
    
    $myForm->addField("photo",_("Photographie"),TYPC_IMG);
    if ($photo_client != "") {
      $myForm->setFieldProperties('photo', FIELDP_IMAGE_URL, $photo_client);
    }
    
    array_push($Order, "pp_nom", "pp_prenom", "pp_date_naissance");
    array_push($Order, "pp_lieu_naissance", "pp_nationalite", "pp_pays_naiss", "pp_sexe", "pp_type_piece_id", "pp_nm_piece_id", "pp_date_piece_id", "pp_lieu_delivrance_id", "pp_date_exp_id", "pp_etat_civil", "pp_nbre_enfant");
    array_push($Order, "adresse", "code_postal", "ville", "pays","num_tel", "num_fax", "num_port", "email", "id_loc1", "id_loc2", "loc3");
    array_push($Order, "sect_act", "pp_pm_activite_prof", "pp_fonction", "pp_employeur", "langue", "pp_revenu", "pp_pm_patrimoine", "pp_casier_judiciaire", "pp_id_gi", "nb_imf", "nb_bk", "commentaires_cli");
  } else if ($CLI["statut_juridique"] == 2) { // Personne morale
    array_push($Order, "pm_raison_sociale", "pm_abreviation", "adresse", "code_postal", "ville", "pays", "num_tel", "pm_tel2", "pm_tel3", "num_fax", "num_port", "email", "pm_email2", "id_loc1", "id_loc2", "loc3");
    array_push($Order, "pm_categorie", "pm_nature_juridique", "sect_act", "pp_pm_activite_prof", "pp_pm_patrimoine", "nb_imf", "nb_bk", "pm_date_notaire", "pm_date_depot_greffe", "pm_lieu_depot_greffe", "pm_date_expiration", "pm_numero_reg_nat", "pm_numero_nric", "pm_lieu_nric", "pm_date_constitution", "pm_agrement_nature", "pm_agrement_autorite", "pm_agrement_numero", "pm_agrement_date", "commentaires_cli");
  } else if ( $CLI["statut_juridique"] == 3) { // Groupe informel
    array_push($Order, "gi_nom", "adresse", "code_postal", "ville", "pays", "num_tel", "num_fax", "num_port", "email", "id_loc1", "id_loc2", "loc3", "sect_act", "langue", "gi_nbre_membr", "nb_imf", "nb_bk", "gi_date_agre");
  } else if ( $CLI["statut_juridique"] == 4) { // Groupe solidaire
    array_push($Order, "gi_nom","gs_responsable", "adresse", "code_postal", "ville", "pays", "num_tel", "num_fax", "num_port", "email", "id_loc1", "id_loc2", "loc3", "sect_act", "langue", "gi_nbre_membr", "nb_imf", "nb_bk", "gi_date_agre", "commentaires_cli");
  }

  if ($CLI["etat"] != 2) // Client ayant subi une défection
    array_push($Order, "raison_defection");

  
  $myForm->addTable("ad_cli", OPER_INCLUDE, $Order);

  $rowset = getFieldsFromTable("ad_cli");
  
  $ref_field = array();
  while ( $row = $rowset->fetchRow(DB_FETCHMODE_ASSOC) ) {
      $ref_field[$row["nchmpc"]] = $row["ref_field"];
  }

  while (list($key, $value) = each($Order)) {

    if ($ref_field[$value] > 0) {

        $defStrArr = Divers::getReferencedFields($pdo_conn, $ref_field[$value], $CLI[$value]);
        
        if ($CLI[$value] != NULL && count($defStrArr)==1) {

            $defStrKey = array_keys($defStrArr);
            $defStr = array_pop($defStrArr);

            $myForm->setFieldProperties($value, FIELDP_HAS_CHOICE_AUCUN, false);
            $myForm->setFieldProperties($value, FIELDP_INCLUDE_CHOICES, array($defStrKey[0] => $defStr));
            $myForm->setFieldProperties($value, FIELDP_ADD_CHOICES, array($defStrKey[0] => $defStr));
            $myForm->setFieldProperties($value, FIELDP_DEFAULT, $CLI[$value]);
            $myForm->setFieldProperties($value, FIELDP_CAN_MODIFY, false);
        } else {
            $myForm->setFieldProperties($value, FIELDP_INCLUDE_CHOICES, array(0 => "[Aucun]"));
            $myForm->setFieldProperties($value, FIELDP_DEFAULT, 0);
            $myForm->setFieldProperties($value, FIELDP_CAN_MODIFY, false);
        }
    } else {
        $myForm->setFieldProperties($value, FIELDP_DEFAULT, $CLI[$value]);
        $myForm->setFieldProperties($value, FIELDP_IS_LABEL, true);
    }
  }
  if ( $CLI["statut_juridique"] == 1) {       // Personne physique
    array_push($Order, "photo");
    array_push($Order, "signature");
  }
  $myForm->setOrder(NULL, $Order);

  if ( $CLI["statut_juridique"] == 1) {       // Personne physique
    array_pop($Order);
    array_pop($Order);
  }

  //Affichage des groupes solidaires
  if ($CLI["statut_juridique"] == 1 || $CLI["statut_juridique"] == 2 || $CLI["statut_juridique"] == 3) {
    //$listeGroupSol=getGroupSol($global_id_client);
      
    $listeGroupSol = $ClientObj->getGroupSol($global_id_client);
    if (!empty($listeGroupSol->param)) {

      //Affichage des groupes solidaires
      $myForm->addHTMLExtraCode("espace_grp_sol","<br/><p align=\"center\"><font size=\"3\"><strong>"._("Appartenance à un groupe solidaire")."<strong></font></p><br/>");

      foreach($listeGroupSol->param as $cle => $valeur) {
        $id_group = $valeur["id_grp_sol"];
        
        $myForm->addField("group".$id_group, _("Nom du groupe"), TYPC_TXT); // TO UNCOMMENT
        
        //$enregGroup = getNomGroup($id_group);
        $enregGroup = $ClientObj->getNomGroup($id_group);

        $myForm->setFieldProperties("group".$id_group, FIELDP_DEFAULT, $enregGroup->param[0]["gi_nom"]); // TO UNCOMMENT
        $myForm->setFieldProperties("group".$id_group, FIELDP_IS_LABEL, true); // TO UNCOMMENT
      }
    }
  }

  if ($CLI["statut_juridique"] == 4) {
      
    // Groupe solidaire : affichage des membres
    $myForm->addHTMLExtraCode("espace","<br/>");
    $myForm->addHTMLExtraCode("membres","<table align=\"center\" valign=\"middle\" bgcolor=\"".$colb_tableau."\"><tr><td><b>"._("Membres du groupe")."</b></td></tr></table>\n");
    
    //$result = getListeMembresGrpSol($CLI["id_client"]);
    $result = $ClientObj->getListeMembresGrpSol($CLI["id_client"]);

    $membres_grp_sol = $result->param;
    
    $i = 1;
    foreach($membres_grp_sol as $cle => $valeur) {
      $myForm->addField("num_client$i", _("Membre $i"), TYPC_INT);
      $myForm->setFieldProperties("num_client$i", FIELDP_IS_LABEL, true);
      $myForm->setFieldProperties("num_client$i", FIELDP_DEFAULT, $valeur);
      $i++;
    }
  }
  
  //Traitement pour les champs extras
  $objChampsExtras = new HTML_Champs_Extras ($myForm,'ad_cli',$id_cli);
  $objChampsExtras->buildChampsExtras($ClientObj->getChampsExtrasCLIENTValues($global_id_client),TRUE);
  
  $myForm->addFormButton(1, 1, "retour", _("Retour Menu"), TYPB_SUBMIT);
  $myForm->setFormButtonProperties("retour", BUTP_PROCHAIN_ECRAN, 'Ope-13');
  $myForm->setFormButtonProperties("retour", BUTP_CHECK_FORM, false);

  $myForm->buildHTML();
  echo $myForm->getHTML();

  $SESSION_VARS['statut_juridique'] = $CLI['statut_juridique'];
  
  // Destroy object
  unset($ClientObj);
  
  // Commit transaction
  $pdo_conn->commit();

}
/*}}}*/

/*{{{ Mac-2 : Affichage des informations financières */
else if ($global_nom_ecran == "Mac-2") {
    
  $myForm = new HTML_GEN2();
  $myForm->setTitle(_("Informations financières"));
  
  // Begin remote transaction
  $pdo_conn->beginTransaction();
  
  // Init class
  $CompteObj = new Compte($pdo_conn, $global_remote_id_agence);
  
  $ACCOUNTS = $CompteObj->getAccounts($global_id_client);
  
  // Init class
  $CreditObj = new Credit($pdo_conn, $global_remote_id_agence);

  $crePro = $CreditObj->getCreditProductID();

  //$crePro = getCreditProductID($global_id_agence);
  $xtHTML = "<h3> "._("Comptes d'épargne"). "</h3>
            <br /><table align=\"center\" cellpadding=\"5\" width=\"95%\">
            <tr align=\"center\" bgcolor=\"$colb_tableau\"><th>"._("Numéro compte")."</th><th>"._("Produit")."</th><th>"._("Date ouverture")."</th><th>"._("Etat")."</th><th>"._("Devise")."</th><th>"._("Solde")."</th><th>"._("Montant minimum")."</th><th>"._("Montant bloqué")."</th><th>"._("Solde disponible")."</th></tr>";
  if (is_array($ACCOUNTS)) {
    $color = $colb_tableau;
    while (list($key, $ACC) = each($ACCOUNTS)) {
      if ($ACC['id_prod'] != $crePro) { // On n'affiche pas le compte de crédit
      	//$access_solde = get_profil_acces_solde($global_id_profil, $ACC["id_prod"]);
      	//$access_solde_vip = get_profil_acces_solde_vip($global_id_profil, $global_id_client);

        //if(manage_display_solde_access($access_solde, $access_solde_vip))
        {
            $solde = $ACC['solde'];
            
            // Init class
            $EpargneObj = new Epargne($pdo_conn, $global_remote_id_agence);
            $soldeDisp = $EpargneObj->getSoldeDisponible($ACC["id_cpte"]);
        }

        $mnt_min = $ACC["mnt_min_cpte"];
        $mnt_bloq = $ACC["mnt_bloq"] + $ACC["mnt_bloq_cre"];
        $color = ($color == $colb_tableau_altern ? $colb_tableau : $colb_tableau_altern);

        Divers::setMonnaieCourante($pdo_conn, $global_remote_id_agence, $ACC['devise']);

        $xtHTML .= "\n<tr bgcolor=\"$color\"><td>".$ACC['num_complet_cpte']."</td><td>".$ACC['libel']."</td><td>".pg2phpDate($ACC['date_ouvert'])."</td><td>".adb_gettext($adsys['adsys_etat_cpt_epargne'][$ACC['etat_cpte']])."</td><td>".$ACC['devise']."</td><td>".afficheMontant($solde, false)."</td><td>".afficheMontant($mnt_min, false)."</td><td>".afficheMontant($mnt_bloq, false)."</td><td><b>".afficheMontant($soldeDisp, false)."</b></td></tr>";
      } else {
      	unset($ACCOUNTS[$key]);
      }

    }
  }
  $SESSION_VARS['suivi_epargne'] = $ACCOUNTS;
  $SESSION_VARS['suivi_credit'] = array();
  $xtHTML .= "</table>";
  $myForm->addHTMLExtraCode("xtHTML", $xtHTML);
  
  $whereCl = " AND (etat = 5 OR etat = 7 OR etat = 13 OR etat = 14 OR etat = 15)";
  $dossiers = $CreditObj->getIdDossier($global_id_client, $whereCl);
  
  unset($CreditObj);
  foreach($dossiers as $id_doss=>$value) {
      
    // Init class
    $CreditObj = new Credit($pdo_conn, $global_remote_id_agence);

    // Afficher le suivi de remboursement pour le crédit en cours
    $DOSS = $CreditObj->getDossierCrdtInfo($id_doss);
    $PRODS = $CreditObj->getProdInfo(" where id =".$DOSS["id_prod"], $id_doss);// Retourne les informations sur le produit sélectionné dans le crédit
    $PROD = $PRODS[0];
    $parametre = array();
    
    Divers::setMonnaieCourante($pdo_conn, $global_remote_id_agence, $PROD["devise"]);

    $DOSS = $CreditObj->getDossierCrdtInfo($id_doss);

    $whereCond = "WHERE id_doss = $id_doss";
    $echeancier = $CreditObj->getEcheancier($whereCond); // L'échéancier
    $remb = $CreditObj->getRemboursement($whereCond); //Les remboursements
    $reechMorat = $CreditObj->getRechMorHistorique (145, $global_id_client, $DOSS["date_dem"]); //Date demande car l date  rééch > date de demande

    $cap_du = 0;  //Capital dû (Cap remb. + Cap restant dû)
    $int_du = 0;  //Intérêt dû
    $gar_du = 0;  //Garantie dûe
    $Nbre_Ech = 0; //Nbre d'échéance

    $cap_rest = 0;  //Capital restant
    $int_rest = 0;  //Intérêt restant
    $gar_rest = 0;  //Garantie restante
    $Nbre_rest = 0; //Nbre d'échéance restant
    $i = 0;

    //Echéancier de remboursement
    if (is_array($reechMorat)) {
      reset($reechMorat);
      list($key,$historique) = each($reechMorat);
    }

    // foreach ($echeancier as $key=>$value) {
    while (list($key,$value)=each($echeancier)) {
      $AMJ_ech = pg2phpDatebis($value["date_ech"]);//Tableau aaaa/mm/jj de la date
      
      $sdEch = gmmktime(0,0,0,(int)$AMJ_ech[0],(int)$AMJ_ech[1],(int)$AMJ_ech[2],1);  //0 mois 1 jour 2 année

      $AMJ_his = pg2phpDatebis($historique["date"]);
      $sdhis = gmmktime(0,0,0,(int)$AMJ_his[0],(int)$AMJ_his[1],(int)$AMJ_his[2],1);  //0 mois 1 jour 2 année

      if (($sdEch>=$sdhis) && ($sdhis>0) && ($sdhis!=$lasthis) && ($DOSS["cre_nbre_reech"]>0)) {  //Réechelonnement /Moratoire
        $lasthis=$sdhis;
        list($key,$historique) = each($reechMorat);
      }

      //Calcul des pénalités attendues
      $pen_remb = '0'; //Pénalité remboursé
      $cap_remb = '0'; //Capital remboursé pour l'échéance i
      $int_remb = '0'; //Intérêt remboursé pour l'échéance i
      $gar_remb = '0'; //Garantie remboursée pour l'échéance i
      $som_cap_remb = '0';//Capital remboursé
      $som_int_remb = '0';//Intérêt remboursé
      $som_gar_remb = '0';//Garantie remboursée

      $REMB = $CreditObj->getRemboursement("WHERE id_doss = ".$id_doss." AND id_ech = ".$value["id_ech"]); //Les remboursements
      // Cas particlier des crédits repris
      if ((sizeof($REMB) == 0) && ($value["remb"] == 't')) {
        $cap_remb="N/A"; // Capital remboursé
        $int_remb="N/A"; // Intérêt remboursé
        $gar_remb="N/A"; // Garanrie remboursée
        $pen_remb="N/A"; // Pénalité remboursé
      } else {
        reset($REMB); //Les remboursements
        while (list($key1,$value1)=each($REMB)) {
          $som_cap_remb +=$value1["mnt_remb_cap"]; //Somme des capitaux remboursés
          $som_int_remb +=$value1["mnt_remb_int"]; //Somme des intérêts remboursés
          $som_gar_remb +=$value1["mnt_remb_gar"]; //Somme des garanties remboursées
          if ($value1["id_ech"]==$value["id_ech"]) {
            $pen_remb +=$value1["mnt_remb_pen"]; //Pénalités remboursées pour l'échéance i
            $cap_remb +=$value1["mnt_remb_cap"]; //Capital remboursé pour l'échéance i
            $int_remb +=$value1["mnt_remb_int"]; //Intérêts remboursés pour l'échéance i
            $gar_remb +=$value1["mnt_remb_gar"]; //Garanties remboursées pour l'échéance i
          }
        }
      }

      if ($value["remb"]==FALSE) {
        $cap_rest += $value["solde_cap"];  //Capital restant à payer
        $int_rest += $value["solde_int"];  //Intérêt restant à payer
        $gar_rest += $value["solde_gar"];  //Garantie restante à payer
      }

      $cap_du = $cap_du + $value["mnt_cap"];
      $int_du = $int_du + $value["mnt_int"];
      $gar_du = $gar_du + $value["mnt_gar"];
      $Nbre_Ech=$value["id_ech"];
      if ($value["remb"]=='t') $i++;
    }

    $Nbre_rest = $Nbre_Ech-$i; //Nbre d'échéances non clôturé

    // Appel de l'affichage de suivi du dossier de crédit
    $parametre["id_doss"] = $id_doss;
    $parametre["cap_du"] =  $cap_du;
    $parametre["int_du"] =  $int_du;
    $parametre["gar_du"] =  $gar_du;
    $parametre["Nbre_Ech"] = $Nbre_Ech;
    $parametre["Nbre_rest"] = $Nbre_rest;
    $parametre["cap_rest"] = $cap_rest;
    $parametre["int_rest"] = $int_rest;
    $parametre["gar_rest"] = $gar_rest;

    $ET = $CreditObj->getTousEtatCredit();
    $parametre["cre_retard_etat_max"] = $ET[$DOSS['cre_retard_etat_max']]["libel"];
    $parametre["cre_retard_etat_max_jour"] = $DOSS['cre_retard_etat_max_jour'];
    if ($DOSS['etat']== 13)
    	$parametre["cre_mnt_deb"] = $DOSS['cre_mnt_deb'];


    $HTML_code = "<h3>"._("Suivi du crédit");
    $HTML_code .=  HTML_suiviCreditMA($parametre,null);

    array_push($SESSION_VARS['suivi_credit'], $parametre);

    $HTML_code .= "<br/><br/>";
    // Permet d'ouvrir la page de détail du remboursement
    $JScode="";
    $JScode .="\nfunction open_remb(id_doss,id_ech)";
    $JScode .="\t{\n";
    $JScode .="\t	// Construction de l'URL : de type ./lib/html/detailremb.php?m_agc=".$_REQUEST['m_agc']."&id_ech=id\n";
    $JScode .="\t\turl = '$http_prefix/lib/html/detailremb.php?m_agc=".$_REQUEST['m_agc']."&id_doss='+id_doss+'&id_ech='+id_ech;\n";
    $JScode .="\t\tRembWindow = window.open(url, '"._("Produit sélectionné")."', 'alwaysRaised=1,dependent=1,scrollbars,resizable=0,width=650,height=400');\n";
    $JScode .="\t}\n";
    $myForm->addJS(JSP_FORM,"prodF".$id_doss,$JScode);

    $myForm->addHTMLExtraCode ("FormEcheancier".$id_doss, $HTML_code);
    
    unset($CreditObj);
  }

  $myForm->addFormButton(1,1, "retour", _("Retour Menu"), TYPB_SUBMIT);
  $myForm->setFormButtonProperties("retour", BUTP_PROCHAIN_ECRAN, 'Ope-13');
  $myForm->setFormButtonProperties("retour", BUTP_CHECK_FORM, false);

  $myForm->buildHTML();
  echo $myForm->getHTML();
  
  // Destroy object
  unset($CompteObj);
  unset($CreditObj);
  unset($EpargneObj);
  
  // Commit transaction
  $pdo_conn->commit();

}
/*}}}*/

else {
    signalErreur(__FILE__,__LINE__,__FUNCTION__, sprintf(_("L'écran %s n'a pas pu être trouvé"), $global_nom_ecran));
}

?>
