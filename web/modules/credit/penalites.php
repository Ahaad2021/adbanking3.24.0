<?Php
/* vim: set expandtab softtabstop=2 shiftwidth=2 foldmethod=marker: */

/**
 * [131] Suspension et ajustement des pénalités.
 * Cette opération comprends les écrans :
 * - Pen-1 : sélection d'un dossier de crédit
 * - Pen-2 : suspension / ajustement de pénalité
 * - Pen-3 : demande de confirmation de la modification
 * - Pen-4 : confirmation de la modification
 * @package Credit
 */

require_once 'lib/html/HTML_GEN2.php';
require_once 'lib/html/HTML_erreur.php';
require_once 'lib/dbProcedures/credit.php';
require_once 'lib/misc/VariablesGlobales.php';
require_once 'lib/misc/divers.php';
require_once('lib/misc/tableSys.php');

/*{{{ Pen-1 : Sélection d'un dossier de crédit */

if ($global_nom_ecran == "Pen-1") {
  // Premier écran : Sélection d'un dossier de crédit

  // Récupération des infos du client (ou du GS)
  $SESSION_VARS['infos_client'] = getClientDatas($global_id_client);
  unset($SESSION_VARS['infos_doss']);
  $dossiers = array(); // tableau contenant les infos sur les dossiers réels (dans ad_dcr) et fictifs (ad_dcr_grp_sol)
  $liste = array(); // Liste box des dossiers à afficher
  $i = 1; // clé de la liste box

  //en fonction du choix du numéro de dossier, afficher les infos avec le onChange javascript
  $codejs = "\n\nfunction getInfoDossier() {";

  // Récupération des dossiers individuels réels (dans ad_dcr) à l'état accepté
  $whereCl = "   AND (etat=5 OR etat = 7 OR etat = 14 OR etat = 15)"; // Le dossier doit être en attente de décisiodeboursé  ou en attente de Rééch/Moratoire
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
      if (($val['id_dcr_grp_sol'] == $id) AND ($val['etat'] == 5 OR $val['etat'] == 7)) {
        $date_dem = $date = pg2phpDate($val['date_dem']);
        $infos .= "n° $id_doss "; // on affiche les numéros de dossiers réels sur une ligne
      }
      if ($infos != '') { // Si au moins on 1 dossier
        $infos .= "du $date_dem";
        $liste[$i] = $infos;
        $dossiers[$i] = $value; // on garde les infos du dossier fictif

        $codejs .= "\n\tif (document.ADForm.HTML_GEN_LSB_id_doss.value ==$i)\n\t";
        $codejs .= "{\n\t\tdocument.ADForm.id_prod.value =\"" . $val["libelle"] . "\";";
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
  $Myform->setFormButtonProperties("valider", BUTP_PROCHAIN_ECRAN, "Pen-2");
  $Myform->setFormButtonProperties("annuler", BUTP_CHECK_FORM, false);

  $Myform->setOrder(NULL,$order);
  $Myform->buildHTML();
  echo $Myform->getHTML();
}


/*{{{ Pen-2 : Suspension / ajustement de pénalité */
else if ($global_nom_ecran == 'Pen-2') {
  //*********************************************************
  if (strstr($global_nom_ecran_prec,"Pen-1")) {
    $SESSION_VARS['infos_doss']="";
    // Récupération des dossiers à approuver
    if ($SESSION_VARS['dossiers'][$HTML_GEN_LSB_id_doss]['gs_cat'] != 2 ) { // dossier individuel
      // Les informations sur le dossier
      $id_doss = $SESSION_VARS['dossiers'][$HTML_GEN_LSB_id_doss]['id_doss'];
      $id_prod = $SESSION_VARS['dossiers'][$HTML_GEN_LSB_id_doss]['id_prod'];;
      $SESSION_VARS['infos_doss'][$id_doss] = getDossierCrdtInfo($id_doss); // infos du dossier reel
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
          $SESSION_VARS['infos_doss'][$id_doss]['last_etat'] = $SESSION_VARS['infos_doss'][$id_doss]['etat'];
          $SESSION_VARS['infos_doss'][$id_doss]['cre_mnt_octr'] = $SESSION_VARS['infos_doss'][$id_doss]['mnt_dem'];
          $id_prod = $SESSION_VARS['infos_doss'][$id_doss]['id_prod']; // même produit pour tous les dossiers
        }
      }
    }

  } //fin si on vient de Pen-1

  //*********************************************************

  $compteDossier=0;
  $myForm = new HTML_GEN2(_("Suspension / ajustement des pénalités"));

  foreach($SESSION_VARS['infos_doss'] as $id_doss=>$val_doss) {

    // Récupération du N° de DCR concerné

    $compteDossier++;
    $SESSION_VARS["id_doss"] = $id_doss;

    // Récupération du statut de la suspension des pénalités
    $DOSS = getDossierCrdtInfo($id_doss);
    $suspension_pen = $DOSS["suspension_pen"];

    // Récupération du montant des pénalités pour ce crédit
    $echeancier = getEcheancier(" WHERE id_doss = $id_doss");
    $solde_pen = 0;
    while (list(,$ech) = each($echeancier)) {
      $solde_pen += $ech["solde_pen"];
    }

    // Enregistrement des informations
    $SESSION_VARS["solde_pen"][$id_doss] = $solde_pen;
    $SESSION_VARS["suspension_pen"][$id_doss] = $suspension_pen;

    // Génération de l'interface HTML


    $myForm->addField("solde_pen".$id_doss, _("Montant attendu en pénalités dossier n° $id_doss"), TYPC_MNT);
    $myForm->setFieldProperties("solde_pen".$id_doss, FIELDP_DEFAULT, $solde_pen);
    $myForm->setFieldProperties("solde_pen".$id_doss, FIELDP_IS_LABEL, true);

    $myForm->addField("nouv_pen".$id_doss, _("Nouveau montant attendu dossier"), TYPC_MNT);

    // $myForm->addHTMLExtraCode("ligne".$id_doss, "<BR>");

    $myForm->addField("suspension_pen".$id_doss, _("Suspension du calcul des pénalités"), TYPC_BOL);
    $myForm->setFieldProperties("suspension_pen".$id_doss, FIELDP_DEFAULT, ($suspension_pen == 't')? true : false);

    $myForm->addHTMLExtraCode("ligne2".$id_doss, "<br />");

    // Un peu de javascript, le nouveau solde en pénalits doit être inférieur à l'ancien solde
    $ {'js'.$id_doss} = "if (recupMontant(document.ADForm.nouv_pen".$id_doss.".value) > $solde_pen)
                        {
                        ADFormValid = false;
                        msg += '".sprintf(_("Le nouveau montant attendu (dossier crédit n° %s) doit être inférieur à l\'ancien montant"),$id_doss)."\\n';
                      }";
    $myForm->addJS(JSP_BEGIN_CHECK, "js$id_doss",  $ {'js'.$id_doss});

    if ($compteDossier==sizeof($SESSION_VARS['infos_doss'])) {


      $myForm->addFormButton(1,1,"valider",_("Valider"),TYPB_SUBMIT);
      $myForm->setFormButtonProperties("valider", BUTP_PROCHAIN_ECRAN, "Pen-3");

      $myForm->addFormButton(1,2,"annuler",_("Annuler"),TYPB_SUBMIT);
      $myForm->setFormButtonProperties("annuler", BUTP_PROCHAIN_ECRAN, "Gen-11");
      $myForm->setFormButtonProperties("annuler", BUTP_CHECK_FORM, false);

      $myForm->buildHTML();
      echo $myForm->getHTML();
    }
  }
}
/*}}}*/

/*{{{ Pen-3 : Demande de confirmation de la modification */
else if ($global_nom_ecran == 'Pen-3') {
  // Enregistrement du montant de la pénalité
  $compteDossier=0;
  $texte = _("Les actions suivantes seront effectuées")." : <UL>";
  foreach($SESSION_VARS['infos_doss'] as $id_doss=>$val_doss) {
    $compteDossier++;
    $msg = new HTML_message(_("Confirmation ajustement / suspension pénalités"));

    if ($ {'nouv_pen'.$id_doss} != '' && recupMontant($ {'nouv_pen'.$id_doss}) != $SESSION_VARS["solde_pen"][$id_doss]) {
      $SESSION_VARS["nouv_pen"][$id_doss] = recupMontant($ {'nouv_pen'.$id_doss});
      $SESSION_VARS["abattement_pen"][$id_doss] = true;
      $texte .= "<LI> ".sprintf(_("Abattement des pénalités du dossier n° %s de %s à %s"),$id_doss,afficheMontant($SESSION_VARS["solde_pen"][$id_doss], true),afficheMontant($SESSION_VARS["nouv_pen"][$id_doss], true))." </LI>";
    }
    $ancienne_susp = ($SESSION_VARS["suspension_pen"][$id_doss] == 't'? true : false);
    $nouvelle_susp = ($ {'suspension_pen'.$id_doss}? true : false);
    if ($ancienne_susp xor $nouvelle_susp) {
      if ($nouvelle_susp) {
        $SESSION_VARS["suspension"][$id_doss] = true;
        $texte .= "<LI>"._("Suspension du décompte des pénalités")." </LI>";
      } else {
        $SESSION_VARS["suspension"][$id_doss] = false;
        $texte .= "<LI>"._("Rétablissement du décompte des pénalités")." </LI>";
      }
    }

    if ($compteDossier==sizeof($SESSION_VARS['infos_doss'])) {
      $texte .= " </UL><br/>"._("Etes-vous sûr de vouloir effectuer cette opération ?")." ";
      $msg->setMessage($texte);
      $msg->addButton(BUTTON_OUI,"Pen-4");
      $msg->addButton(BUTTON_NON,"Pen-2");
      $msg->buildHTML();
      echo $msg->HTML_code;
    }
  }
}
/*}}}*/

/*{{{ Pen-3 : Confirmation de la modification */
else if ($global_nom_ecran == 'Pen-4') {
  $DATA_ABAT=array();
  $DATA_SUSPEN=array();
  foreach($SESSION_VARS['infos_doss'] as $id_doss=>$val_doss) {
    if ($SESSION_VARS["abattement_pen"][$id_doss] == true) {
      $DATA_ABAT[$id_doss]["nouv_pen"]=$SESSION_VARS["nouv_pen"][$id_doss];
      $DATA_ABAT[$id_doss]["id_client"]=$val_doss["id_client"];
    }
    if (isset($SESSION_VARS["suspension"][$id_doss])) {
      $DATA_SUSPEN[$id_doss]["suspension"]=$SESSION_VARS["suspension"][$id_doss];
      $DATA_SUSPEN[$id_doss]["id_client"]=$val_doss["id_client"];
      $global_suspension_pen = $SESSION_VARS["suspension"][$id_doss];

    }

  }

  $myErr = abattementPenalites($DATA_ABAT);
  if ($myErr->errCode != NO_ERR) {
    $html_err = new HTML_erreur(_("Echec du traitement. "));
    $html_err->setMessage(_("L'opération de suspension / ajustement ne s'est pas réalisée.")." ".$error[$myErr->errCode].$myErr->param);
    $html_err->addButton("BUTTON_OK", 'Gen-11');
    $html_err->buildHTML();
    echo $html_err->HTML_code;
    exit();
  }


  $myErr = suspensionPenalites($DATA_SUSPEN);
  if ($myErr->errCode != NO_ERR) {
    $html_err = new HTML_erreur(_("Echec du traitement. "));
    $html_err->setMessage(_("L'opération de suspension / ajustement ne s'est pas réalisée.")." ".$error[$myErr->errCode].$myErr->param);
    $html_err->addButton("BUTTON_OK", 'Gen-11');
    $html_err->buildHTML();
    echo $html_err->HTML_code;
    exit();
  }




  $msg = new HTML_message(_("Confirmation opération"));
  $msg->setMessage(_("L'opération de suspension / ajustement des pénalités s'est réalisée avec succès"));
  $msg->addButton(BUTTON_OK,"Gen-11");
  $msg->buildHTML();
  echo $msg->HTML_code;
}
/*}}}*/

else signalErreur(__FILE__,__LINE__,__FUNCTION__, sprintf(_("L'écran %s n'a pas pu être trouvé"), $global_nom_ecran));

?>
