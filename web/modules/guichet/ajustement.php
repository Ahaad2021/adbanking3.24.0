<?php
require_once 'lib/dbProcedures/guichet.php';

if ($global_nom_ecran == "Jgu-1") {

  global $global_multidevise;
  $MyPage = new HTML_GEN2(_("Ajustement encaisse"));

  //Champs guichet
  $libels=getLibelGuichet();
  $MyPage->addField("guichet",_("Guichet"), TYPC_LSB);

  $MyPage->setFieldProperties("guichet", FIELDP_ADD_CHOICES, $libels["libel"]);

  $MyPage->setFieldProperties("guichet", FIELDP_IS_REQUIRED, true);

  $MyPage->addTable("ad_cpt_comptable",OPER_INCLUDE, array("devise"));
  $MyPage->setFieldProperties("devise", FIELDP_IS_REQUIRED, true);
  $MyPage->setFieldProperties("devise", FIELDP_LONG_NAME,"Devise");
  $MyPage->setFieldProperties("devise", FIELDP_DEFAULT,$global_monnaie);

  $MyPage->addField("date_encaisse",_("Date"), TYPC_DTE);
  $MyPage->setFieldProperties("date_encaisse", FIELDP_IS_REQUIRED, true);
  $MyPage->setFieldProperties("date_encaisse", FIELDP_DEFAULT, date("d/m/Y"));


  //Boutons
  $MyPage->addFormButton(1,1,"butok", _("Valider"), TYPB_SUBMIT);
  $MyPage->setFormButtonProperties("butok", BUTP_PROCHAIN_ECRAN, "Jgu-2");
  $MyPage->addFormButton(1,2,"butannul", _("Annuler"), TYPB_SUBMIT);
  $MyPage->setFormButtonProperties("butannul", BUTP_PROCHAIN_ECRAN, "Gen-6");
  $MyPage->setFormButtonProperties("butannul", BUTP_CHECK_FORM, false);

  //HTML
  $MyPage->buildHTML();
  echo $MyPage->getHTML();
} else if ($global_nom_ecran == "Jgu-2") {
  //FIXME : ici, il semble que Papa a fait des modifs pour adapter->à voir
  global $global_multidevise;


  $my_js.="\nif((document.ADForm.encaisse.value !='')||(document.ADForm.conf_encaisse.value !='')){\nif (recupMontant(document.ADForm.encaisse.value) != recupMontant(document.ADForm.conf_encaisse.value)) \n{msg += '- "._("Les sommes ne correspondent pas")." \\n';ADFormValid = false;}\n}\n";

  $MyPage = new HTML_GEN2(_("Ajustement encaisse"));

  //Champs guichet
  $libels=getLibelGuichet();
  $MyPage->addField("guichet",_("Guichet"), TYPC_TXT);
  $MyPage->setFieldProperties("guichet",FIELDP_DEFAULT,$libels["libel"][$guichet]);
  $MyPage->setFieldProperties("guichet", FIELDP_IS_LABEL, true);
  $encaisses=get_encaisse($guichet,$devise);
  $SESSION_VARS["devise"] = $devise;
  $SESSION_VARS["encaisses"] = $encaisses;
  $SESSION_VARS["guichet"] = $guichet;
  $SESSION_VARS["date_encaisse"] = date("r");

  if ($global_multidevise) {
    setMonnaieCourante($devise);
  }
  //Champs encaisse courant
  $MyPage->addField("encaisse_courant", _("Encaisse courant"), TYPC_MNT);
  $MyPage->setFieldProperties("encaisse_courant", FIELDP_IS_LABEL, true);
  $MyPage->setFieldProperties("encaisse_courant", FIELDP_DEFAULT,$encaisses);
  //Champs nouvel encaisse
  $MyPage->addField("encaisse", _("Nouvel encaisse"), TYPC_MNT);
  $MyPage->setFieldProperties("encaisse", FIELDP_IS_REQUIRED, true);

  if ($global_multidevise) {
    //Champs confirmation nouvel encaisse
    $MyPage->addField("conf_encaisse", _("Confirmation encaisse"), TYPC_MNT);
    $MyPage->setFieldProperties("conf_encaisse", FIELDP_IS_REQUIRED, true);
  }

  //Champs numéro de pièce et remarque
  $MyPage->addTable("ad_his_ext", OPER_INCLUDE, array("num_piece", "remarque"));

  $MyPage->addJS(JSP_BEGIN_CHECK,"checkEncaisse",$my_js);
  //Boutons
  $MyPage->addFormButton(1,1,"butok", _("Valider"), TYPB_SUBMIT);
  $MyPage->setFormButtonProperties("butok", BUTP_PROCHAIN_ECRAN, "Jgu-3");
  $MyPage->addFormButton(1,2,"butannul", _("Annuler"), TYPB_SUBMIT);
  $MyPage->setFormButtonProperties("butannul", BUTP_PROCHAIN_ECRAN, "Gen-6");
  $MyPage->setFormButtonProperties("butannul", BUTP_CHECK_FORM, false);

  //HTML
  $MyPage->buildHTML();
  echo $MyPage->getHTML();
} else if ($global_nom_ecran == "Jgu-3") {
  $encaisse = arrondiMonnaie(recupMontant($encaisse),0,$SESSION_VARS["devise"]);
  debug($encaisse,"encaisse");

  if ($encaisse == $SESSION_VARS["encaisses"]) {
    $MyPage = new HTML_erreur(_("Erreur ajustement encaisse"));
    $MyPage->setMessage(_("Vous devez saisir un montant diffèrent de l'encaisse en cours."));
    $MyPage->addButton(BUTTON_OK, "Gen-6");
    $MyPage->buildHTML();
    echo $MyPage->HTML_code;
  } else {
    $myErr = ajustement_encaisse($SESSION_VARS["guichet"],$encaisse, $num_piece, $remarque, $SESSION_VARS["devise"], $SESSION_VARS["date_encaisse"]);

    if ($myErr->errCode == NO_ERR) { //Si OK (guichet fermé, c.à.d. login associé pas loggé)
      //HTML
      $MyPage = new HTML_message(_("Confirmation ajustement encaisse"));
      $MyPage->setMessage(_("L'encaisse du guichet  a été mis à jour avec succès")." (".afficheMontant($encaisse,true).").<br /><br />"._("Numéro de transaction")." : <B><CODE>".sprintf("%09d", $myErr->param)."</CODE></B>");
      $MyPage->addButton(BUTTON_OK, "Gen-6");
      $MyPage->buildHTML();
      echo $MyPage->HTML_code;
    } else if ($myErr->errCode == ERR_GUICHET_OUVERT) {
      //HTML
      $MyPage = new HTML_erreur(_("Erreur ajustement encaisse"));
      $MyPage->setMessage(_("L'encaisse du guichet  n'a pas pu être mis à jour : ce guichet est ouvert (le login associé est connecté)."));
      $MyPage->addButton(BUTTON_OK, "Gen-6");
      $MyPage->buildHTML();
      echo $MyPage->HTML_code;
    } else {
      $html_err = new HTML_erreur(_("Echec de l'ajustement encaisse.")." ");
      $html_err->setMessage(_("Erreur")." : ".$error[$myErr->errCode]."<br />".$myErr->param);
      $html_err->addButton("BUTTON_OK", 'Gen-6');
      $html_err->buildHTML();
      echo $html_err->HTML_code;

    }
  }
} else signalErreur(__FILE__,__LINE__,__FUNCTION__); // "Ecran '$global_nom_ecran' inconnu"
?>