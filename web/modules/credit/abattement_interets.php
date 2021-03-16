<?php
/* vim: set expandtab softtabstop=2 shiftwidth=2 foldmethod=marker: */

/**
 * [132] Abattment des intérêts d'un crédit
 * Cette opération comprends les écrans :
 * - Abi-1 : Sélection d'un dossier de crédit
 * - Abi-2 : Affichage de l'échéancier de remboursement modifiable
 * - Abi-3 : Affichage de l'échéancier à confirmer
 * - Abi-4 : Confirmation de l'enregistrement du nouvel échéancier
 * @package Credit
 */

require_once 'lib/html/HTML_GEN2.php';
require_once 'lib/html/HTML_erreur.php';
require_once 'lib/dbProcedures/credit.php';
require_once 'lib/misc/VariablesGlobales.php';
require_once 'lib/misc/divers.php';
require_once 'lib/misc/tableSys.php';

/*{{{ Abi-1 : Sélection d'un dossier de crédit */
if ($global_nom_ecran == "Abi-1") {

  // Remise à zéro des sauvegardes précédentes, au cas où on recommence la procédure
  unset($SESSION_VARS["echeancier_original"]);
  unset($SESSION_VARS["echeancier_modifie"]);
  unset($SESSION_VARS["echeancier_en_base"]);
  unset($SESSION_VARS["total_int_original"]);
  unset($SESSION_VARS["total_pen_original"]);
  unset($SESSION_VARS['infos_doss']);
  unset($SESSION_VARS['infos_client']);
  unset($SESSION_VARS['dossiers']);
  unset($SESSION_VARS['cre_date_debloc']);
  unset($SESSION_VARS['total_int']);
  unset($SESSION_VARS['total_pen']);
  unset($SESSION_VARS['total_gar_original']);
  unset($SESSION_VARS["tx_anticipation"]);
  unset($SESSION_VARS['id_doss_anti']);


  // Premier écran : Sélection d'un dossier de crédit
  // Récupération des infos du client (ou du GS)
  $SESSION_VARS['infos_client'] = getClientDatas($global_id_client);

  $dossiers = array(); // tableau contenant les infos sur les dossiers réels (dans ad_dcr) et fictifs (ad_dcr_grp_sol)
  $liste = array(); // Liste box des dossiers à afficher
  $i = 1; // clé de la liste box

  //en fonction du choix du numéro de dossier, afficher les infos avec le onChange javascript
  $codejs = "\n\nfunction getInfoDossier() {";

  // Récupération des dossiers individuels réels (dans ad_dcr) à l'état accepté
  $whereCl = "   AND (etat=5 OR etat = 7 OR etat = 9 OR etat = 13 OR etat = 14 OR etat = 15)"; // Le dossier doit être en attente de décision, déboursé ou en attente de rééch/moratoire

  $dossiers_reels = getIdDossier($global_id_client, $whereCl);

  if (is_array($dossiers_reels))
    foreach($dossiers_reels as $id_doss=>$value)
    if ($value['gs_cat'] != 2) { // les dossiers pris en groupe doivent être déboursés via le groupe
      $date = pg2phpDate($value["date_dem"]); //Fonction renvoie  des dates au format jj/mm/aaaa
      $liste[$i] ="n° $id_doss du $date"; //Construit la liste en affichant N° dossier + date
      $dossiers[$i] = $value;

      $codejs .= "\n\tif (document.ADForm.HTML_GEN_LSB_id_doss.value ==$i)\n\t";
      $codejs .= "{\n\t\tdocument.ADForm.id_prod.value =\"" . $value["libelle"] . "\";";
      $codejs .= "}\n";
      $i++;
    }

  // SI GS, récupérer les dossiers des membres dans le cas de dossiers multiples
  if ($SESSION_VARS['infos_client']['statut_juridique'] == 4) {
    // Récupération des dossiers fictifs du groupe avec dossiers multiples : cas 2
    $whereCl = " WHERE id_membre=$global_id_client and gs_cat=2";
    $dossiers_fictifs = getCreditFictif($whereCl);

    // Récupération des dossiers des membres
    $dossiers_membre = getDossiersMultiplesGS($global_id_client);

    // Ajout dans la liste box, pour chaque dossier fictif du GS, les dossiers réels des membres du GS
    foreach($dossiers_fictifs as $id=>$value) {
      // Récupération, pour chaque dossier fictif, des dossiers réels associés : CS avec dossiers multiples
      $infos = '';
      foreach($dossiers_membre as $id_doss=>$val)
      if (($val['id_dcr_grp_sol'] == $id) AND ($val['etat'] == 5 OR $val['etat'] == 7 OR $val['etat'] == 9 OR $val['etat'] == 13)) {
        $date_dem = $date = pg2phpDate($val['date_dem']);
        $infos .= "n° $id_doss "; // on affiche les numéros de dossiers réels sur une ligne
      }
      if ($infos != '') { // Si au moins on 1 dossier
        $infos .= "du $date_dem";
        $liste[$i] = $infos;
        $dossiers[$i] = $value; // on garde les infos du dossier fictif

        $codejs .= "\n\tif (document.ADForm.HTML_GEN_LSB_id_doss.value ==$i)\n\t";
        $codejs .= "{\n\t\tdocument.ADForm.id_prod.value ='" . $val["libelle"] . "';";
        $codejs .= "}\n";
        $i++;
      }
    }
  }

  $SESSION_VARS['dossiers'] = $dossiers;
  $codejs .= "\n\tif (document.ADForm.HTML_GEN_LSB_id_doss.value =='0') {";
  $codejs .= "\n\t\tdocument.ADForm.id_prod.value='';";
  $codejs .= "\n\t}\n";
  $codejs .= "}\ngetInfoDossier();";

  // Création du formulaire
  $Myform = new HTML_GEN2(_("Sélection d'un dossier de crédit"));
  $Myform->addField("id_doss",_("Dossier de crédit"), TYPC_LSB);
  $Myform->addField("id_prod",_("Type produit de crédit"), TYPC_TXT);

  $Myform->setFieldProperties("id_prod", FIELDP_IS_LABEL, true);
  $Myform->setFieldProperties("id_prod", FIELDP_IS_REQUIRED, false);
  $Myform->setFieldProperties("id_prod", FIELDP_WIDTH, 30);

  $Myform->setFieldProperties("id_doss",FIELDP_ADD_CHOICES,$liste);
  $Myform->setFieldProperties("id_doss", FIELDP_JS_EVENT, array("onChange"=>"getInfoDossier();"));
  $Myform->addJS(JSP_FORM, "JS3", $codejs);

  // Javascript : vérifie qu'un dossier est sélectionné
  $JS_1 = "";
  $JS_1.="\t\tif(document.ADForm.HTML_GEN_LSB_id_doss.options[document.ADForm.HTML_GEN_LSB_id_doss.selectedIndex].value==0){ msg+=' - "._("Aucun dossier sélectionné")." .\\n';ADFormValid=false;}\n";
  $Myform->addJS(JSP_BEGIN_CHECK,"testdos",$JS_1);

  // Ordre d'affichage des champs
  $order = array("id_doss","id_prod");

  // les boutons ajoutés
  $Myform->addFormButton(1,1,"valider",_("Valider"),TYPB_SUBMIT);
  $Myform->addFormButton(1,2,"annuler",_("Retour Menu"),TYPB_SUBMIT);

  // Propriétés des boutons
  $Myform->setFormButtonProperties("annuler", BUTP_PROCHAIN_ECRAN, "Gen-11");
  $Myform->setFormButtonProperties("valider", BUTP_PROCHAIN_ECRAN, "Abi-2");
  $Myform->setFormButtonProperties("annuler", BUTP_CHECK_FORM, false);

  $Myform->setOrder(NULL,$order);
  $Myform->buildHTML();
  echo $Myform->getHTML();
}
/*}}}*/

/*{{{ Abi-2 : Affichage de l'échéancier de remboursement modifiable */
else if ($global_nom_ecran == 'Abi-2') {


  if (strstr($global_nom_ecran_prec,"Abi-1")) {
    $SESSION_VARS['id_doss_anti'] = $SESSION_VARS['dossiers'][$HTML_GEN_LSB_id_doss]['id_doss'];
    $SESSION_VARS['infos_doss']="";
    // Récupération des dossiers à approuver
    if ($SESSION_VARS['dossiers'][$HTML_GEN_LSB_id_doss]['gs_cat'] != 2 ) { // dossier individuel
      // Les informations sur le dossier
      $id_doss = $SESSION_VARS['dossiers'][$HTML_GEN_LSB_id_doss]['id_doss'];
      $id_prod = $SESSION_VARS['dossiers'][$HTML_GEN_LSB_id_doss]['id_prod'];;
      $SESSION_VARS['infos_doss'][$id_doss] = getDossierCrdtInfo($id_doss); // infos du dossier reel
      $SESSION_VARS['infos_doss'][$id_doss]['cre_date_approb'] = date("d/m/Y");
      $SESSION_VARS['infos_doss'][$id_doss]['last_etat'] = $SESSION_VARS['infos_doss'][$id_doss]['etat'];
      $SESSION_VARS['infos_doss'][$id_doss]['cre_mnt_octr'] = $SESSION_VARS['infos_doss'][$id_doss]['mnt_dem'];
      // Infos dossiers fictifs dans le cas de GS avec dossier unique
      if ($SESSION_VARS['dossiers'][$HTML_GEN_LSB_id_doss]['gs_cat'] == 1) {
        $whereCond = " WHERE id_dcr_grp_sol = $id_doss";
        $SESSION_VARS['doss_fic'] = getCreditFictif($whereCond);
      }
    }
    elseif($SESSION_VARS['dossiers'][$HTML_GEN_LSB_id_doss]['gs_cat'] == 2 ) { // GS avec dossiers multiples
      // infos dossier fictif
      $id_doss_fic = $SESSION_VARS['dossiers'][$HTML_GEN_LSB_id_doss]['id'];  // id du dossier fictif (dossier du groupe)
      $whereCond = " WHERE id = $id_doss_fic";
      $SESSION_VARS['doss_fic'] = getCreditFictif($whereCond);

      // dossiers réels des membre du GS
      $dossiers_membre = getDossiersMultiplesGS($global_id_client);
      foreach($dossiers_membre as $id_doss=>$val) {
        if ($val['id_dcr_grp_sol'] == $SESSION_VARS['dossiers'][$HTML_GEN_LSB_id_doss]['id']) {
          $SESSION_VARS['infos_doss'][$id_doss] = $val; // infos d'un dossier reel d'un membre
          $SESSION_VARS['infos_doss'][$id_doss]['cre_date_approb'] = date("d/m/Y");
          $SESSION_VARS['infos_doss'][$id_doss]['last_etat'] = $SESSION_VARS['infos_doss'][$id_doss]['etat'];
          $SESSION_VARS['infos_doss'][$id_doss]['cre_mnt_octr'] = $SESSION_VARS['infos_doss'][$id_doss]['mnt_dem'];
          $id_prod = $SESSION_VARS['infos_doss'][$id_doss]['id_prod']; // même produit pour tous les dossiers
        }
      }
    }



    // Les informations sur le produit de crédit

  } //fin si on vient de Abi-1

  $compteDossier=0;
  $formEcheancier = new HTML_GEN2();

  $agc_data = getAgenceDatas($global_id_agence);

  foreach($SESSION_VARS['infos_doss'] as $id_doss=>$val_doss) {
    $compteDossier++;
    $Dossier = getDossierCrdtInfo($id_doss);
    $Produit = getProdInfo(" where id =".$Dossier["id_prod"], $id_doss);
    // Sauvegarde des informations nécessaire tout au long de l'opération
    $SESSION_VARS["cre_date_debloc"][$id_doss] = $Dossier["cre_date_debloc"];

    if ($SESSION_VARS["echeancier_modifie"][$id_doss]) {
      // L'échéancier de remboursement déjà modifié (on vient de Abi-3)
      $echeancier = $SESSION_VARS["echeancier_modifie"][$id_doss];
    } else {
      // L'échéancier de remboursement courant dans la BD
      $echeancier = getEcheancier("WHERE (remb='f') AND (id_doss='".$id_doss."')");
      $SESSION_VARS["echeancier_original"][$id_doss] = $echeancier;
      $SESSION_VARS["echeancier_modifie"][$id_doss] = $echeancier;
    }

    // Appel de l'affichage de l'échéancier
    // Préparation des paramètres à donner à HTML_echeancier_remboursement
    $parametre["lib_date"] = _("Date de déboursement");
    $parametre["titre"] = _("Echéancier de remboursement à modifier");
    $parametre["mnt_reech"] = '0';
    $parametre["date"] = pg2phpDate($SESSION_VARS["cre_date_debloc"][$id_doss]);
    $parametre["id_doss"] = $id_doss;
    $champs_modifiables["int"] = true;
    $champs_modifiables["pen"] = true;
    $champs_modifiables["gar"] = true;
    $HTML_code_echeancier = HTML_echeancier_remboursement($parametre, $echeancier, $champs_modifiables, $Dossier, $Produit);

    $total_pen = 0;
    $total_int = 0;

    foreach ($echeancier as $key =>$valeur) {
      $total_pen += $valeur["mnt_pen"];
      $total_int += $valeur["mnt_int"];
      $total_gar += $valeur["mnt_gar"];

    }



    echo   $SESSION_VARS['infos_doss'][$id_doss]["total_pen"];
    // Sauvegarder total_int, total_pen et total_gar si c'est le premier passage dans Abi-2
    // On en aura besoin pour vérifier que le montant total n'est pas dépassé

    if (!$SESSION_VARS["total_int_original"][$id_doss]) {
      $SESSION_VARS["total_int_original"][$id_doss] =   $total_int;
    }
    $total_int_original = $SESSION_VARS["total_int_original"][$id_doss];
    if (!$SESSION_VARS["total_pen_original"][$id_doss]) {
      $SESSION_VARS["total_pen_original"][$id_doss] =  $total_pen;
    }
    $total_pen_original = $SESSION_VARS["total_pen_original"][$id_doss];
    if (!$SESSION_VARS["total_gar_original"][$id_doss]) {
      $SESSION_VARS["total_gar_original"][$id_doss] =  $total_gar;
    }
    $total_gar_original = $SESSION_VARS["total_gar_original"][$id_doss];

    // Vérifications javascript :
    //  - Calcul automatique des totaux en intérêts, pénalités et garanties
    //  - Les nouveaux totaux ne peuvent pas être plus grands que ceux existants dans l'échéancier actuel
    $IntCells = "var total_int_original$id_doss = ".$total_int_original.";\nvar intCells$id_doss = new Array();\n";
    $PenCells = "var total_pen_original$id_doss = ".$total_pen_original.";\nvar penCells$id_doss = new Array();\n";
    $GarCells = "var total_gar_original$id_doss = ".$total_gar_original.";\nvar garCells$id_doss = new Array();\n";

    foreach($echeancier as $key =>$valeur) {
      $IntCells .= "intCells".$id_doss."[".$key."] = document.ADForm.solde_int$id_doss$key;\n";
      $PenCells .= "penCells".$id_doss."[".$key."]= document.ADForm.solde_pen$id_doss$key;\n";
      $GarCells .= "garCells".$id_doss."[".$key."]= document.ADForm.solde_gar$id_doss$key;\n";
    }

    $formEcheancier->addJS(JSP_FORM, "intCells$id_doss", $IntCells);
    $formEcheancier->addJS(JSP_FORM, "penCells$id_doss", $PenCells);
    $formEcheancier->addJS(JSP_FORM, "garCells$id_doss", $GarCells);
    $formEcheancier->addJS(JSP_BEGIN_CHECK, "computeInt".$id_doss, "msg_int".$id_doss." = checkSumLessThan(intCells$id_doss, document.ADForm.total_int".$id_doss.", total_int_original$id_doss, '"._("des intérêts à rembourser")."');\n");
    $formEcheancier->addJS(JSP_BEGIN_CHECK, "computePen".$id_doss, "msg_pen".$id_doss." = checkSumLessThan(penCells$id_doss, document.ADForm.total_pen".$id_doss.", total_pen_original$id_doss, '"._("des pénalités à rembourser")."');\n");
    $formEcheancier->addJS(JSP_BEGIN_CHECK, "computeGar".$id_doss, "msg_gar".$id_doss." = checkSumLessThan(garCells$id_doss, document.ADForm.total_gar".$id_doss.", total_gar_original$id_doss, '"._("des garanties à rembourser")."');\n");
    $formEcheancier->addJS(JSP_BEGIN_CHECK, "checkInt".$id_doss, "if (msg_int".$id_doss." != '' || msg_pen".$id_doss." != '' || msg_gar".$id_doss." != '') ADFormValid = false;\n");
    $formEcheancier->addJS(JSP_BEGIN_CHECK, "checkMsg".$id_doss, "msg += msg_int".$id_doss." + msg_pen".$id_doss." + msg_gar".$id_doss.";\n");
    $formEcheancier->addHTMLExtraCode("echeancier".$id_doss, $HTML_code_echeancier);
    $formEcheancier->addHTMLExtraCode("espace".$id_doss,"<br/>");

    if ($compteDossier==sizeof($SESSION_VARS['infos_doss'])) {

      // Les boutons
      $formEcheancier->addFormButton(1,1,"retour",_("Précédent"),TYPB_SUBMIT);
      $formEcheancier->addFormButton(1,2,"valider",_("Valider"),TYPB_SUBMIT);
      $formEcheancier->addFormButton(1,3,"annuler",_("Retour Menu"),TYPB_SUBMIT);

      // Propriétés des boutons
      $formEcheancier->setFormButtonProperties("retour", BUTP_PROCHAIN_ECRAN, "Abi-1");
      $formEcheancier->setFormButtonProperties("valider", BUTP_PROCHAIN_ECRAN, "Abi-3");
      $formEcheancier->setFormButtonProperties("annuler", BUTP_PROCHAIN_ECRAN, "Gen-11");
      $formEcheancier->setFormButtonProperties("retour", BUTP_CHECK_FORM, false);
      $formEcheancier->setFormButtonProperties("annuler", BUTP_CHECK_FORM, false);
      $formEcheancier->buildHTML();
      echo $formEcheancier->getHTML();
    }

  }

}
/*}}}*/

/*{{{ Abi-3 : Affichage de l'échéancier à confirmer */
// TODO : check sur les sommes d'intérêts et de pénalités ./. BD ?
else if ($global_nom_ecran == 'Abi-3') {

 /* if(isset($tx_anticipation)){
    $SESSION_VARS["tx_anticipation"] = $tx_anticipation;

  }*/

  $formEcheancier = new HTML_GEN2();
  $compteDossier=0;
  foreach($SESSION_VARS['infos_doss'] as $id_doss=>$val_doss) {
    $compteDossier++;
    $Dossier = getDossierCrdtInfo($id_doss);
    $Produit = getProdInfo(" where id =".$Dossier["id_prod"], $id_doss);
    // Récupération des champs de l'échéancier modifié dans les variables POST
    $echeancier_modifie = $SESSION_VARS["echeancier_modifie"][$id_doss];
    $i = 0;
    while ($i < count($echeancier_modifie)) {
      $echeancier_modifie[$i]["solde_int"] = (($ {'solde_int'.$id_doss.$i} != "")? recupMontant($ {'solde_int'.$id_doss.$i}) : 0);
      $echeancier_modifie[$i]["solde_pen"] = (($ {'solde_pen'.$id_doss.$i} != "")? recupMontant($ {'solde_pen'.$id_doss.$i}) : 0);
      $echeancier_modifie[$i]["solde_gar"] = (($ {'solde_gar'.$id_doss.$i} != "")? recupMontant($ {'solde_gar'.$id_doss.$i}) : 0);
      $echeancier_modifie[$i]["id_client"] =  $Dossier["id_client"];
      $i++;
    }
    $SESSION_VARS["echeancier_modifie"][$id_doss] = $echeancier_modifie;
    // Préparation des paramètres à donner à HTML_echeancier_remboursement
    $parametre["lib_date"] = _("Date de déboursement");
    $parametre["titre"] = _("Echéancier de remboursement à confirmer");
    $parametre["mnt_reech"] = '0';
    $parametre["date"] = pg2phpDate($SESSION_VARS["cre_date_debloc"][$id_doss]);
    $parametre["id_doss"] = $id_doss;
    $HTML_code_echeancier = HTML_echeancier_remboursement($parametre, $SESSION_VARS["echeancier_modifie"][$id_doss], NULL, $Dossier, $Produit);
    $formEcheancier->addHTMLExtraCode("echeancier".$id_doss, $HTML_code_echeancier);
    if ($compteDossier==sizeof($SESSION_VARS['infos_doss'])) {
      // les boutons ajoutés
      $formEcheancier->addFormButton(1,1,"retour",_("Modifier"),TYPB_SUBMIT);
      $formEcheancier->addFormButton(1,2,"valider",_("Valider"),TYPB_SUBMIT);
      $formEcheancier->addFormButton(1,3,"annuler",_("Retour Menu"),TYPB_SUBMIT);

      // Propriétés des boutons
      $formEcheancier->setFormButtonProperties("retour", BUTP_PROCHAIN_ECRAN, "Abi-2");
      $formEcheancier->setFormButtonProperties("valider", BUTP_PROCHAIN_ECRAN, "Abi-4");
      $formEcheancier->setFormButtonProperties("annuler", BUTP_PROCHAIN_ECRAN, "Gen-11");
      $formEcheancier->setFormButtonProperties("retour", BUTP_CHECK_FORM, false);
      $formEcheancier->setFormButtonProperties("valider", BUTP_CHECK_FORM, false);
      $formEcheancier->setFormButtonProperties("annuler", BUTP_CHECK_FORM, false);


      $formEcheancier->buildHTML();
      echo $formEcheancier->getHTML();
    }
  }
}
/*}}}*/

/*{{{ Abi-4 : Confirmation de l'enregistrement du nouvel échéancier */
else if ($global_nom_ecran == 'Abi-4') {
  global $colt_error;

  $echeancier_modifie =  $SESSION_VARS["echeancier_modifie"];
  $echeancier_original = $SESSION_VARS["echeancier_original"];

  // Recuperer l'echeancier en base
  foreach($SESSION_VARS['infos_doss'] as $id_doss=>$val_doss) {
    // cette fonction est un duplica de la fonction getEcheancier mais avec l'ajout de la close connection car blocage des blocs de transactions SQL.
    $SESSION_VARS["echeancier_en_base"][$id_doss]= getEcheancierAbattement("WHERE (remb='f') AND (id_doss='".$id_doss."')");
  }

  // Si l'echeancier a ete modifier autre part, bloquer le traitement
  if(is_array($SESSION_VARS["echeancier_en_base"]) &&(!validateEcheancierAbattement($echeancier_original, $SESSION_VARS["echeancier_en_base"]))) {
    // Affichage de la confirmation
    $html_msg = new HTML_message("Modification des données hors fonction Abattement");
    $html_msg->setMessage(_("<FONT color=$colt_error>Les données de ce dossier ont été changées concomitamment hors module Abattement.<br /> Veuillez mettre à jour la fiche.</FONT>"));
    $html_msg->addButton("BUTTON_OK", 'Abi-1');
    $html_msg->buildHTML();
    echo $html_msg->HTML_code;
    exit(1);
  }


  $infos_historique = "";
  $DATA=array();

  // Enregistrer le nouvel échéancier dans la BD (table ad_etr)
  foreach ($echeancier_modifie as $key2 => $echeance_dossier)
  foreach($echeance_dossier as $key => $echeance)
  $DATA[$echeance["id_client"]][$key] = $echeance;

  $tabMyErr = updateEcheancier($DATA);
  $nbr_ech_modifiees= $tabMyErr[1];
  $myErr=$tabMyErr[0];
  if (($myErr->errCode != NO_ERR) && ($Error->errCode != NO_ERR) ) {
    //On a un problème, l'état de l'échéancier est non garanti... :(
    $html_err = new HTML_erreur(_("Echec du traitement.")." ");
    $html_err->setMessage(_("L'opération d'abbatement des intérêts et pénalités ne s'est pas réalisée correctement.")." ".$error[$myErr->errCode].$myErr->param);
    $html_err->addButton("BUTTON_OK", 'Gen-11');
    $html_err->buildHTML();
    echo $html_err->HTML_code;
    exit();
  }

  // Confirmation à l'utilisateur de la bonne exécution
  $msg = new HTML_message(_("Confirmation opération"));
  if ($nbr_ech_modifiees == 0 ) {
    $msg->setMessage(_("Aucune échéance n'a été modifiée."));
  } else if ($nbr_ech_modifiees == 1) {
    $msg->setMessage(_("Une échéance a été modifiée.")."<br>"._("L'opération d'abattement des intérêts et pénalités s'est réalisée avec succès."));
  } else {
    $msg->setMessage(sprintf(_("%s échéances ont été modifiées.<br>L'opération d'abattement des intérêts et pénalités s'est réalisée avec succès."),$nbr_ech_modifiees));
  }
  $msg->addButton(BUTTON_OK,"Gen-11");
  $msg->buildHTML();
  echo $msg->HTML_code;
}
/*}}}*/

else signalErreur(__FILE__,__LINE__,__FUNCTION__, sprintf(_("L'écran %s n'a pas pu être trouvé"), $global_nom_ecran));

?>
