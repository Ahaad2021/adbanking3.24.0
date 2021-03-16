<?php
/**
 * Created by PhpStorm.
 * User: Roshan
 * Date: 1/24/2018
 * Time: 11:43 AM
 */
require_once 'lib/dbProcedures/guichet.php';

/*{{{ Faf-1 : Choix de la source des fonds */
if ($global_nom_ecran == "Faf-1") {
  //print_rn("Hehe you are here man ;) Congratulations!!");
  $MyPage = new HTML_GEN2(_("Perception des frais d'adhesion par lot via fichier"));

  $choices = array(1 => _("Cash"));
  $MyPage->addField("source", "Source des fonds", TYPC_LSB);
  $MyPage->setFieldProperties("source", FIELDP_ADD_CHOICES, $choices);
  $MyPage->setFieldProperties("source", FIELDP_IS_REQUIRED, true);

  /*$MyPage->addTable("ad_his_ext", OPER_INCLUDE, array("communication", "remarque"));

  $MyPage->addField("nom_ben", _("Nom du tireur"), TYPC_TXT);
  $MyPage->setFieldProperties("nom_ben", FIELDP_IS_LABEL, true);
  $MyPage->addHiddenType("id_ben", "");

  $MyPage->addLink("nom_ben", "rechercher", _("Rechercher"), "#");
  $MyPage->setLinkProperties("rechercher", LINKP_JS_EVENT, array("onclick" => "OpenBrw('$SERVER_NAME/modules/externe/rech_benef.php?m_agc=".$_REQUEST['m_agc']."&field_name=nom_ben&field_id=id_ben&type=t', '"._("Recherche")."'); return false;"));

  // Correspondant bancaire
  $libel_correspondant = getLibelCorrespondant($global_monnaie);
  $MyPage->addField("correspondant", _("Correspondant bancaire"), TYPC_LSB);
  $MyPage->setFieldProperties("correspondant", FIELDP_ADD_CHOICES, $libel_correspondant);

  // Ordonner les champs pour l'affichage
  $order = array("source", "correspondant", "nom_ben", "num_piece", "communication", "remarque");
  $MyPage->setOrder(NULL, $order);

  // Transformer les champs en labels non modifiables
  $labels = array_diff($order, array("source"));
  foreach ($labels as $value) {
    $MyPage->setFieldProperties($value, FIELDP_IS_LABEL, true);
    $MyPage->setFieldProperties($value, FIELDP_IS_REQUIRED, false);
  }

  // En fonction du choix du compte, afficher les infos avec le onChange javascript
  $jscheck = "function activateFields()
           {
             if ((document.ADForm.HTML_GEN_LSB_source.value == 0))
           {
             document.ADForm.HTML_GEN_LSB_correspondant.disabled = true;
             document.ADForm.num_piece.disabled = true;
             document.ADForm.communication.disabled = true;
             document.ADForm.remarque.disabled = true;
           }
             else if ((document.ADForm.HTML_GEN_LSB_source.value == 1) || (document.ADForm.HTML_GEN_LSB_source.value == 3))
           {
             document.ADForm.HTML_GEN_LSB_correspondant.disabled = true;
             document.ADForm.num_piece.disabled = true;
             document.ADForm.communication.disabled = false;
             document.ADForm.remarque.disabled = false;
           }
             else
           {
             document.ADForm.HTML_GEN_LSB_correspondant.disabled = false;
             document.ADForm.num_piece.disabled = false;
             document.ADForm.communication.disabled = false;
             document.ADForm.remarque.disabled = false;
           }
           }";

  $MyPage->addJS(JSP_FORM, "JS1", $jscheck);
  $MyPage->setFieldProperties("source", FIELDP_JS_EVENT, array("onchange" => "activateFields()"));

  // Checkform
  $jscheck = "if (document.ADForm.HTML_GEN_LSB_source.value == 2)
           {
             if (document.ADForm.HTML_GEN_LSB_correspondant.value == 0)
           {
             msg += '- "._("Le champ correspondant doit être renseigné")."\\n';
             ADFormValid = false;
           }
             if (document.ADForm.id_ben.value == '')
           {
             msg += '- "._("Le champ tireur doit être renseigné")."\\n';
             ADFormValid = false;
           }
             if (document.ADForm.num_piece.value == '')
           {
             msg += '- "._("Le champ numéro de pièce doit être renseigné")."\\n';
             ADFormValid = false;
           }
           }";

  $MyPage->addJS(JSP_BEGIN_CHECK, "JS2", $jscheck);*/

  $MyPage->addFormButton(1, 1, "ok", "Valider", TYPB_SUBMIT);
  $MyPage->addFormButton(1, 2, "cancel", "Annuler", TYPB_SUBMIT);
  $MyPage->setFormButtonProperties("cancel", BUTP_CHECK_FORM, false);
  $MyPage->setFormButtonProperties("ok", BUTP_PROCHAIN_ECRAN, 'Faf-2');
  $MyPage->setFormButtonProperties("cancel", BUTP_PROCHAIN_ECRAN, 'Gen-6');

  $MyPage->buildHTML();
  echo $MyPage->getHTML();
}
/*}}}*/

/*{{{ Faf-2 : Récupération du fichier de données */
else if ($global_nom_ecran == "Faf-2") {

  // Sauvegarde des valeurs provenant du premier écran
  if ($source != NULL) {
    $INFOSOURCE = array();
    $INFOSOURCE["source"] = $source;
    // Source = correspondant bancaire
    if ($source == 2) {
      $INFOSOURCE["id_source"] = $correspondant;
      $INFOSOURCE["num_piece"] = $num_piece;
      $INFOSOURCE["id_ben"] = $id_ben;
    }// Source =Guichet
    elseif($source == 1){
      $INFOSOURCE["id_source"] =$global_id_guichet;
    }
    $INFOSOURCE["communication"] = $communication;
    $INFOSOURCE["remarque"] = $remarque;
    $SESSION_VARS["SOURCE"] = $INFOSOURCE;
  }

  if (file_exists($fichier_lot)) {
    $filename = $fichier_lot.".tmp";
    move_uploaded_file($fichier_lot, $filename);
    exec("chmod a+r ".escapeshellarg($filename));
    $SESSION_VARS['fichier_lot'] = $filename;
  } else {
    $SESSION_VARS['fichier_lot'] = NULL;
  }

  $MyPage = new HTML_GEN2(_("Recuperation du fichier de donnees"));

  $htm1 = "<p align=\"center\">"._("Fichier de donnees").": <INPUT name=\"fichier_lot\" type=\"file\" /></p>";
  $htm1 .= "<p align=\"center\"> <INPUT type=\"submit\" value=\"Envoyer\" onclick=\"document.ADForm.m_agc.value='".$_REQUEST['m_agc']."';document.ADForm.prochain_ecran.value='Faf-2';\"/> </p>";
  $htm1 .= "<br />";

  $MyPage->addHTMLExtraCode("htm1", $htm1);

  $MyPage->AddField("statut", _("Statut"), TYPC_TXT);
  $MyPage->setFieldProperties("statut", FIELDP_IS_LABEL, true);

  if ($SESSION_VARS['fichier_lot'] == NULL) {
    $MyPage->setFieldProperties("statut", FIELDP_DEFAULT, _("Fichier non reçu"));
  } else {
    $MyPage->setFieldProperties("statut", FIELDP_DEFAULT, _("Fichier reçu"));
  }

  $MyPage->addHTMLExtraCode("htm2", "<br />");

  $MyPage->addFormButton(1, 1, "valider", _("Valider"), TYPB_SUBMIT);
  $MyPage->addFormButton(1, 2, "precedent", _("Precedent"), TYPB_SUBMIT);
  $MyPage->addFormButton(1, 3, "annuler", _("Annuler"), TYPB_SUBMIT);

  $MyPage->setFormButtonProperties("valider", BUTP_PROCHAIN_ECRAN, 'Faf-3');
  $MyPage->setFormButtonProperties("precedent", BUTP_PROCHAIN_ECRAN, 'Faf-1');
  $MyPage->setFormButtonProperties("annuler", BUTP_PROCHAIN_ECRAN, 'Gen-6');

  $MyPage->setFormButtonProperties("precedent", BUTP_CHECK_FORM, false);
  $MyPage->setFormButtonProperties("annuler", BUTP_CHECK_FORM, false);

  $MyPage->buildHTML();
  echo $MyPage->getHTML();
}
/*}}}*/

/*{{{ Faf-3 : Demande de confirmation de la perception des frais d'adhesion par lot via fichier */
else if ($global_nom_ecran == "Faf-3") {

  $MyErr = parse_fa_fichier_lot($SESSION_VARS['fichier_lot']);

  if ($MyErr->errCode != NO_ERR) {
    $param = $MyErr->param;
    $html_err = new HTML_erreur(_("Echec de récupération du fichier de données"));
    $msg = _("Erreur : ").$error[$MyErr->errCode];
    if ($param != NULL) {
      if(is_array($param)){
        foreach($param as $key => $val){
          $msg .= "<br /> (".$key." : ".$param["$key"].")";
        }
      }

    }
    $html_err->setMessage($msg);
    $html_err->addButton("BUTTON_OK", 'Faf-2');
    $html_err->buildHTML();
    echo $html_err->HTML_code;
  } else if ($MyErr->errCode == NO_ERR) {
    $param = $MyErr->param;

    $MyPage = new HTML_GEN2(_("Demande de confirmation de la perception frais d'adhesion par lot via fichier"));

    $MyPage->addField("montant", _("Montant Total"), TYPC_MNT);
    $MyPage->setFieldProperties("montant", FIELDP_IS_LABEL, true);
    $MyPage->setFieldProperties("montant", FIELDP_DEFAULT, $param['total']);

    $MyPage->addField("confirmation", _("Confirmation"), TYPC_MNT);
    $MyPage->setFieldProperties("confirmation", FIELDP_IS_REQUIRED, true);

    $MyPage->addHTMLExtraCode("htm1", "<BR>");

    $JS1 =  "if (recupMontant(document.ADForm.montant.value) != recupMontant(document.ADForm.confirmation.value))";
    $JS1 .= "{";
    $JS1 .= "  msg += ' - "._("Le montant entré est incorrect\\n")."';";
    $JS1 .= "  ADFormValid = false;";
    $JS1 .= "}";

    $MyPage->addJS(JSP_BEGIN_CHECK, "JS1", $JS1);

    $MyPage->addFormButton(1, 1, "valider", _("Valider"), TYPB_SUBMIT);
    $MyPage->addFormButton(1, 2, "precedent", _("Precedent"), TYPB_SUBMIT);
    $MyPage->addFormButton(1, 3, "annuler", _("Annuler"), TYPB_SUBMIT);

    $MyPage->setFormButtonProperties("valider", BUTP_PROCHAIN_ECRAN, 'Faf-4');
    $MyPage->setFormButtonProperties("precedent", BUTP_PROCHAIN_ECRAN, 'Faf-2');
    $MyPage->setFormButtonProperties("annuler", BUTP_PROCHAIN_ECRAN, 'Gen-6');

    $MyPage->setFormButtonProperties("precedent", BUTP_CHECK_FORM, false);
    $MyPage->setFormButtonProperties("annuler", BUTP_CHECK_FORM, false);

    $MyPage->buildHTML();
    echo $MyPage->getHTML();
  }
}
/*}}}*/

/*{{{ Faf-4 : Confirmation de la perception des frais d'adhesion par lot via fichier */
else if ($global_nom_ecran == "Faf-4") {

  $MyErr = traite_fa_fichier_lot($SESSION_VARS["fichier_lot"], $SESSION_VARS['SOURCE']);

  if ($MyErr->errCode == NO_ERR) {
    $html_mess = new HTML_message(_("Confirmation Perception frais d'adhesion par lot via fichier"));
    $html_mess->setMessage(_("La Perception frais d'adhesion par lot via fichier s'est deroulee avec succes !"));
    $html_mess->addButton(BUTTON_OK, "Gen-6");
    $html_mess->buildHTML();
    echo $html_mess->HTML_code;
  } else {
    $param = $MyErr->param;
    $html_err = new HTML_erreur(_("Echec de la Perception frais d'adhesion pat lot via fichier"));
    $msg = _("Erreur : ").$error[$MyErr->errCode];
    if ($param != NULL) {
      if(is_array($param)){
        foreach($param as $key => $val){
          $msg .= "<BR> (".$key." : ".$param["$key"].")";
        }
      }

    }
    $html_err->setMessage($msg);
    $html_err->addButton(BUTTON_OK, 'Gen-6');
    $html_err->buildHTML();
    echo $html_err->HTML_code;
  }

}
/*}}}*/
else signalErreur(__FILE__,__LINE__,__FUNCTION__);
?>