<?php
/* Faire jouer les assurances
   TF - 08/04/2002 */

require_once('lib/dbProcedures/client.php');
require_once('lib/dbProcedures/agence.php');
require_once('lib/dbProcedures/compte.php');
require_once('lib/dbProcedures/epargne.php');
require_once('lib/dbProcedures/credit.php');
require_once('lib/misc/divers.php');
require_once('lib/misc/VariablesSession.php');
require_once('lib/html/HTML_GEN2.php');
require_once('lib/html/FILL_HTML_GEN2.php');

//  "selection d'un  dossier de crédit";

if ($global_nom_ecran == "Ass-1") {
  // Premier écran : Sélection d'un dossier de crédit
  $whereCl=" AND ((etat=5) OR (etat=7) OR (etat=14) OR (etat=15)) AND assurances_cre ='f'"; // Le dossier doit être en état débousé ou en attente de Rééch/Moratoire

  $dossier = getIdDossier($global_id_client,$whereCl);// Info sur les dossiers de crédit du client

  if (sizeof($dossier)>0) {

    $Myform = new HTML_GEN2(_("Sélection d'un dossier de crédit"));
    $Myform->addField("id_doss",_("Dossier de crédit"), TYPC_LSB);
    $Myform->addField("id_prod",_("Type produit de crédit"), TYPC_TXT);

    $Myform->setFieldProperties("id_prod", FIELDP_IS_LABEL, true);
    $Myform->setFieldProperties("id_prod", FIELDP_IS_REQUIRED, false);
    $Myform->setFieldProperties("id_prod", FIELDP_WIDTH, 30);

    while (list($key,$rows) = each($dossier)) {
      $id_doss = $rows["id_doss"];
      $date = pg2phpDate($rows["date_dem"]); //Fonction renvoie  des dates au format jj/mm/aaaa
      $liste["$id_doss"] ="n°$id_doss du $date"; //Construit la liste en affichant N° dossier + date
    }

    $Myform->setFieldProperties("id_doss",FIELDP_ADD_CHOICES,$liste);

    $JS_1="";
    $JS_1.="\t\tif(document.ADForm.HTML_GEN_LSB_id_doss.options[document.ADForm.HTML_GEN_LSB_id_doss.selectedIndex].value==0){ msg+=' - "._("Aucun dossier sélectionné .")."\\n';ADFormValid=false;}\n";

    $Myform->addJS(JSP_BEGIN_CHECK,"testdos",$JS_1);


//en fonction du choix du numéro de dossier, afficher les infos avec le onChange javascript
    $codejs = "\n\nfunction getInfoDossier() {";
    if (isset($dossier)) {
      // debug($dossier,"hhhhhhhhhhhhhhhhhhhhhhhhh");
      foreach($dossier as $key=>$value) {
        $id_dossier = $value['id_doss'];
        $codejs .= "\n\tif (document.ADForm.HTML_GEN_LSB_id_doss.value ==$id_dossier)\n\t";
        $codejs .= "{\n\t\tdocument.ADForm.id_prod.value =\"" . $value["libelle"] . "\";";
        $codejs .= "}\n";
      }
      $codejs .= "\n\tif (document.ADForm.HTML_GEN_LSB_id_doss.value =='0') {";
      $codejs .= "\n\t\tdocument.ADForm.id_prod.value='';";
      $codejs .= "\n\t}\n";
    }
    $codejs .= "}\ngetInfoDossier();";

    $Myform->setFieldProperties("id_doss", FIELDP_JS_EVENT, array("onChange"=>"getInfoDossier();"));
    $Myform->addJS(JSP_FORM, "JS3", $codejs);

// Ordre d'affichage des champs
    $order = array("id_doss","id_prod");


    // les boutons ajoutés
    $Myform->addFormButton(1,1,"valider",_("Valider"),TYPB_SUBMIT);
    $Myform->addFormButton(1,2,"annuler",_("Retour Menu"),TYPB_SUBMIT);

    // Propriétés des boutons
    $Myform->setFormButtonProperties("annuler", BUTP_PROCHAIN_ECRAN, "Gen-9");
    $Myform->setFormButtonProperties("valider", BUTP_PROCHAIN_ECRAN, "Ass-2");
    $Myform->setFormButtonProperties("annuler", BUTP_CHECK_FORM, false);

    $Myform->setOrder(NULL,$order);
    $Myform->buildHTML();
    echo $Myform->getHTML();
  }
}




else if ($global_nom_ecran == 'Ass-2') { // Ecran de remboursement par assurances
  // Les informations sur le dossier
  if (isset($id_doss)) {
    $id_doss =$HTML_GEN_LSB_id_doss;
    $SESSION_VARS["id_doss"] = $id_doss;
  }
  /* if (!$id_doss)
  {
    $html_err = new HTML_erreur(_("Refus du traitement."));
    $html_err->setMessage(_("Ce client n'a pas de dossier de crédit en cours."));
    $html_err->addButton("BUTTON_OK", 'Gen-9');
    $html_err->buildHTML();
    echo $html_err->HTML_code;

  }
  else
  {*/
  $total = getSoldeCapital($SESSION_VARS["id_doss"]);

  $Title = _("Prise en charge par l'assurance");
  $myForm = new HTML_GEN2($Title);
  $myForm->addField("total", _("Solde du crédit à apurer"), TYPC_MNT);
  $myForm->addField("assurance", _("Montant pris en charge par l'assurance"), TYPC_MNT);
  $myForm->setFieldProperties("total", FIELDP_DEFAULT, $total);
  $myForm->setFieldProperties("total", FIELDP_IS_LABEL, true);
  $myForm->setFieldProperties("assurance", FIELDP_IS_REQUIRED, true);
  $jsCheck = "if (recupMontant(document.ADForm.assurance.value) != ".$total.") {msg += '"._("Le montant pris en charge par l\'assurance doit être égal au montant du crédit")."';ADFormValid = false;}";
  $myForm->addJS(JSP_BEGIN_CHECK, "check", $jsCheck);
  $myForm->addFormButton(1,1,"ok", _("Valider"), TYPB_SUBMIT);
  $myForm->addFormButton(1,2,"annuler", _("Annuler"), TYPB_SUBMIT);
  $myForm->setFormButtonProperties("annuler", BUTP_CHECK_FORM, false);
  $myForm->setFormButtonProperties("annuler", BUTP_PROCHAIN_ECRAN, 'Gen-9');
  $myForm->setFormButtonProperties("ok", BUTP_PROCHAIN_ECRAN, 'Ass-3');
  $myForm->buildHTML();
  echo $myForm->getHTML();

} else if ($global_nom_ecran == 'Ass-3') { // Ecran de confirmation de remboursement par l'assurance
  global $error;
  $myErr = transfertMontantAssurances($global_id_client,$SESSION_VARS["id_doss"],recupMontant($assurance));
  if ($myErr->errCode != NO_ERR) {
    $html_err = new HTML_erreur(_("Echec de la prise en compte de l'assurance"));
    $html_err->setMessage(_("Erreur")." : ".$error[$myErr->errCode]."<br/>"._("Paramètre")." : ".$myErr->param);
    $html_err->addButton("BUTTON_OK", 'Gen-8');
    $html_err->buildHTML();
    echo $html_err->HTML_code;
  } else {
    $myMsg = new HTML_message(_("Confirmation"));
    $myMsg->setMessage(_("Le montant des assurances a bien été transféré sur le compte de base du client"));
    $myMsg->addButton(BUTTON_OK, 'Gen-9');
    $myMsg->buildHTML();
    echo $myMsg->HTML_code;
  }
} else
  signalErreur(__FILE__,__LINE__,__FUNCTION__); // "Ecran '$global_nom_ecran' non pris en charge"

?>
