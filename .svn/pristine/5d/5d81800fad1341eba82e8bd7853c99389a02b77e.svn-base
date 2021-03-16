<?php

// Change Cash -- MBAYE & FASTY

require_once 'lib/dbProcedures/guichet.php';
require_once 'lib/dbProcedures/credit.php';
require_once 'modules/rapports/xml_devise.php';
require_once 'lib/dbProcedures/billetage.php';

if ($global_nom_ecran == "Cca-1") {
  $MyPage = new HTML_GEN2(_("Etape 1 - Choix de la devise"));
  $MyPage->addTableRefField("devise_achat", "Devise de départ", "devise");
  $MyPage->setFieldProperties("devise_achat", FIELDP_IS_REQUIRED, true);
  //Boutons
  $MyPage->addFormButton(1,1,"valid", _("Valider"), TYPB_SUBMIT);
  $MyPage->setFormButtonProperties("valid", BUTP_PROCHAIN_ECRAN, "Cca-2");
  $MyPage->addFormButton(1,2,"annul", _("Annuler"), TYPB_SUBMIT);
  $MyPage->setFormButtonProperties("annul", BUTP_PROCHAIN_ECRAN, "Gen-6");
  $MyPage->setFormButtonProperties("annul", BUTP_CHECK_FORM, false);
  $MyPage->buildHTML();
  echo $MyPage->getHTML();
} else if ($global_nom_ecran == "Cca-2") {

  $SESSION_VARS["devise_achat"] = $devise_achat;

  setMonnaieCourante($devise_achat);
  $MyPage = new HTML_GEN2(_("Etape 2 - Choix du montant"));
  $MyPage->addTableRefField("devise_achat", "Devise de départ", "devise");
  $MyPage->setFieldProperties("devise_achat", FIELDP_DEFAULT, $devise_achat);
  $MyPage->addField("mnt",_("Montant à changer"),TYPC_MNT);
  $MyPage->addField("mnt_cv",_("Contre valeur "),TYPC_DVR);
  $MyPage->linkFieldsChange("mnt_cv","mnt","vente",1,true);
  $MyPage->setOrder(NULL, array("devise_achat", "mnt"));
  $MyPage->setFieldProperties("mnt", FIELDP_IS_REQUIRED, true);
  $MyPage->setFieldProperties("mnt_cv", FIELDP_IS_REQUIRED, true);
  $MyPage->setFieldProperties("devise_achat", FIELDP_IS_LABEL, true);
  //Boutons
  $MyPage->addFormButton(1,1,"valid", _("Valider"), TYPB_SUBMIT);
  $MyPage->setFormButtonProperties("valid", BUTP_PROCHAIN_ECRAN, "Cca-3");
  $MyPage->addFormButton(1,2,"retour", _("Retour"), TYPB_SUBMIT);
  $MyPage->setFormButtonProperties("retour", BUTP_PROCHAIN_ECRAN, "Cca-1");
  $MyPage->setFormButtonProperties("retour", BUTP_CHECK_FORM, false);
  $MyPage->addFormButton(1,3,"annul", _("Annuler"), TYPB_SUBMIT);
  $MyPage->setFormButtonProperties("annul", BUTP_PROCHAIN_ECRAN, "Gen-6");
  $MyPage->setFormButtonProperties("annul", BUTP_CHECK_FORM, false);

  $js = "if (document.ADForm.HTML_GEN_dvr_mnt_cv.value == '$devise_achat')
      {
        msg += '"._("Vous devez choisir une devise différente de la devise achetée")."';ADFormValid = false;
      }";
  $MyPage->addJS(JSP_BEGIN_CHECK, "js", $js);

  $MyPage->buildHTML();
  echo $MyPage->getHTML();
} else if ($global_nom_ecran == "Cca-3") {
  $CHANGE = $mnt_cv;
  $CHANGE["mnt_achat"] = recupMontant($mnt);
  $CHANGE["devise_achat"] = $SESSION_VARS["devise_achat"];

  $SESSION_VARS["change"] = $CHANGE;

  $devise_achat = $SESSION_VARS["devise_achat"];
  $devise_vente = $CHANGE["devise"];

  if ($devise_achat == $devise_vente) {
    $html_err = new HTML_erreur(_("Erreur"));
    $html_err->setMessage(_("Les devises achetées et vendues sont les memes !"));
    $html_err->addButton("BUTTON_OK", 'Cca-2');
    $html_err->buildHTML();
    echo $html_err->HTML_code;
  } else {
    $MyPage = new HTML_GEN2(_("Etape 3 - Encaissement et décaissement"));

    setMonnaieCourante($devise_achat);
    $MyPage->addField("mnt_achat",_("Montant à changer"),TYPC_MNT);
    $MyPage->setFieldProperties("mnt_achat", FIELDP_DEFAULT, $CHANGE["mnt_achat"]);
    $MyPage->setFieldProperties("mnt_achat", FIELDP_IS_LABEL, true);
    $MyPage->addField("conf_mnt_achat",_("Montant encaissé"),TYPC_MNT);
    $MyPage->setFieldProperties("conf_mnt_achat", FIELDP_IS_REQUIRED, true);
    $MyPage->setFieldProperties("conf_mnt_achat", FIELDP_HAS_BILLET, true);
    $js = "if (recupMontant(document.ADForm.mnt_achat.value) != recupMontant(document.ADForm.conf_mnt_achat.value))
        {
          ADFormValid = false;
          msg += '- "._("Le montant à changer doit etre égal au montant encaissé")."\\n';
        }";

    setMonnaieCourante($devise_vente);
    $MyPage->addField("mnt_vente",_("Contrevaleur"),TYPC_MNT);
    $MyPage->setFieldProperties("mnt_vente", FIELDP_DEFAULT, $CHANGE["cv"]);
    $MyPage->setFieldProperties("mnt_vente", FIELDP_IS_LABEL, true);
    $MyPage->addField("conf_mnt_vente",_("Montant décaissé"),TYPC_MNT);
    $MyPage->setFieldProperties("conf_mnt_vente", FIELDP_IS_REQUIRED, true);
    $MyPage->setFieldProperties("conf_mnt_vente", FIELDP_HAS_BILLET, true);
    $js .= "if (recupMontant(document.ADForm.mnt_vente.value) != recupMontant(document.ADForm.conf_mnt_vente.value))
         {
           ADFormValid = false;
           msg += '- "._("La contrevaleur doit etre égale au montant décaissé")."\\n';
         }";

    // Reste
    if ($CHANGE["reste"] > 0) {
      setMonnaieCourante($global_monnaie);
      $MyPage->addField("mnt_reste",_("Reste du change"),TYPC_MNT);
      $MyPage->setFieldProperties("mnt_reste", FIELDP_DEFAULT, $CHANGE["reste"]);
      $MyPage->setFieldProperties("mnt_reste", FIELDP_IS_LABEL, true);
      $MyPage->addTableRefField("dest_reste", "Destination du reste","adsys_change_dest_reste");
      $MyPage->setFieldProperties("dest_reste", FIELDP_DEFAULT, $CHANGE["dest_reste"]);
      $MyPage->setFieldProperties("dest_reste", FIELDP_IS_LABEL, true);
      if ($CHANGE["dest_reste"] == 1) {
        $MyPage->addField("conf_mnt_reste","Montant décaissé en $global_monnaie",TYPC_MNT);
        $MyPage->setFieldProperties("conf_mnt_reste", FIELDP_IS_REQUIRED, true);
        $MyPage->setFieldProperties("conf_mnt_reste", FIELDP_HAS_BILLET, true);
        $js .= "if (recupMontant(document.ADForm.mnt_reste.value) != recupMontant(document.ADForm.conf_mnt_reste.value))
             {
               ADFormValid = false;
               msg += '- "._("Le reste de change doit etre égal au montant décaissé en")." $global_monnaie\\n';
             }";
      }
    }

    $MyPage->addJS(JSP_BEGIN_CHECK, "js", $js);

    // Boutons
    $MyPage->addFormButton(1,1,"valid", _("Valider"), TYPB_SUBMIT);
    $MyPage->setFormButtonProperties("valid", BUTP_PROCHAIN_ECRAN, "Cca-4");
    $MyPage->addFormButton(1,2,"annul", _("Annuler"), TYPB_SUBMIT);
    $MyPage->setFormButtonProperties("annul", BUTP_PROCHAIN_ECRAN, "Gen-6");
    $MyPage->setFormButtonProperties("annul", BUTP_CHECK_FORM, false);

    $MyPage->buildHTML();
    echo $MyPage->getHTML();
  }
} else if ($global_nom_ecran == "Cca-4") {
  $SESSION_VARS["change"]["source_achat"] = "Guichet";
  $SESSION_VARS["change"]["deste_vente"] = "Guichet";

  $myErr = changeCash($global_id_guichet, $SESSION_VARS["change"]["mnt_achat"], $SESSION_VARS["change"]["cv"], $SESSION_VARS["change"]["devise_achat"], $SESSION_VARS["change"]["devise"], $SESSION_VARS["change"]["comm_nette"],  $SESSION_VARS["change"]["taux"], $SESSION_VARS["change"]["dest_reste"]);
  if ($myErr->errCode == NO_ERR) {

    $valeurBilletArr = array();

    //recuperation du parametre d'affichage de billetage sur les recu
    $isbilletage = getParamAffichageBilletage();

    $hasBilletageRecu = true;
    $hasBilletageChange = false;

    // Multidevises
    if(!empty($SESSION_VARS['change']['cv'])){
      $dev = $SESSION_VARS['change']['devise'];
      $hasBilletageRecu = false;
      $hasBilletageChange = true;
    }
    else {
      $dev = $SESSION_VARS["set_monnaie_courante"];
    }

    $listTypesBilletArr = buildBilletsVect($dev);

    $total_billetArr = array();

    //insert nombre billet into array
    for($x = 0; $x < 20; $x++) {
      if(isset($_POST['conf_mnt_vente_billet_'.$x]) && trim($_POST['conf_mnt_vente_billet_'.$x])!='') {
        $valeurBilletArr[] = trim($_POST['conf_mnt_vente_billet_'.$x]);
      }
      else{
        if(isset($listTypesBilletArr[$x]['libel']) && trim($listTypesBilletArr[$x]['libel'])!='') {
          $valeurBilletArr[] = 'XXXX';
        }
      }
    }
    // calcul total pour chaque billets
    for($x = 0; $x < 20; $x ++) {

      if ($valeurBilletArr [$x] == 'XXXX') {
        $total_billetArr [] = 'XXXX';
      } else {
        if (isset ( $listTypesBilletArr [$x] ['libel'] ) && trim ( $listTypesBilletArr [$x] ['libel'] ) != '' && isset ( $valeurBilletArr [$x] ['libel'] ) && trim ( $valeurBilletArr [$x] ['libel'] ) != '') {
          $total_billetArr [] = ( int ) ($valeurBilletArr [$x]) * ( int ) ($listTypesBilletArr [$x] ['libel']);
        }
      }
    }

    // parametre d'affichage de billetage sur les recu
    if($isbilletage=='f' or !$isbilletage){
      $hasBilletageRecu = false;
      $hasBilletageChange = false;
    }

    $id_his = $myErr->param;
    printRecuChange($id_his, $SESSION_VARS["change"]["mnt_achat"],$SESSION_VARS["devise_achat"],$SESSION_VARS["change"]["source_achat"],$SESSION_VARS["change"]["cv"],$SESSION_VARS["change"]["devise"],$SESSION_VARS["change"]["comm_nette"],$SESSION_VARS["change"]["taux"],$SESSION_VARS["change"]["reste"],$SESSION_VARS["change"]["deste_vente"],$SESSION_VARS["change"]["dest_reste"],$SESSION_VARS["envoi_reste"],$listTypesBilletArr,$valeurBilletArr,null,$total_billetArr,$hasBilletageChange);
    unset($SESSION_VARS["print_recu_change"]);
    $myMsg = new HTML_message(_("Change cash"));
    $msg = _("L'opération de change cash s'est terminée avec succès");
    $msg .= "<br /><br />"._("Numéro de transaction")." : <B><code>".sprintf("%09d", $myErr->param)."</code></B>";
    $myMsg->setMessage($msg);
    $myMsg->addButton(BUTTON_OK, 'Gen-6');
    $myMsg->buildHTML();
    echo $myMsg->HTML_code;
  } else {
    $html_err = new HTML_erreur(_("Echec de l'opération de change cash."));
    $html_err->setMessage("Erreur : ".$error[$myErr->errCode]."<BR>Paramètre : ".$myErr->param);
    $html_err->addButton("BUTTON_OK", 'Gen-6');

    $html_err->buildHTML();
    echo $html_err->HTML_code;
  }
} else  signalErreur(__FILE__,__LINE__,__FUNCTION__); // _("L'écran $global_nom_ecran n'existe pas")
?>
