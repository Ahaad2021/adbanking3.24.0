<?php
require_once 'lib/dbProcedures/parametrage.php';

if ($global_nom_ecran == "Map-1") {
  $MyPage = new HTML_GEN2(_("Modification autre mot de passe"));
  //Champs
  $MyPage->addTableRefField("login", "Login", "ad_log");
  $MyPage->setFieldProperties("login", FIELDP_IS_REQUIRED, true);
  $MyPage->addField("mdp_new1", _("Nouveau mot de passe"), TYPC_PWD);
  $MyPage->setFieldProperties("mdp_new1", FIELDP_IS_REQUIRED, true);
  $MyPage->addField("mdp_new2", _("Confirmation nouveau mot de passe"), TYPC_PWD);
  $MyPage->setFieldProperties("mdp_new2", FIELDP_IS_REQUIRED, true);
  //Boutons
  $MyPage->addFormButton(1,1, "butok", _("Valider"), TYPB_SUBMIT);
  $MyPage->setFormButtonProperties("butok", BUTP_PROCHAIN_ECRAN, "Map-2");
  $MyPage->addFormButton(1,2, "butanul", _("Annuler"), TYPB_SUBMIT);
  $MyPage->setFormButtonProperties("butanul", BUTP_PROCHAIN_ECRAN, "Gen-7");
  $MyPage->setFormButtonProperties("butanul", BUTP_CHECK_FORM, false);
  //Javascript vérification mot de passe
  $js = "if (document.ADForm.mdp_new1.value != document.ADForm.mdp_new2.value) {msg += '- "._("Les mots de passe doivent être identiques !")."\\n'; ADFormValid = false;}\n";
  // recuperation des données de l'agence'
  $AG = getAgenceDatas($global_id_agence);
  $nbre_car_min = $AG["nbre_car_min_pwd"];
  if($nbre_car_min > 0){

  	$js.= "if ( document.ADForm.mdp_new1.value.length < $nbre_car_min)" .
  		" {msg+= '".sprintf(_("la longueur minimale du mot de passe doit être de %s caractères !"),$nbre_car_min)."\\n'; ADFormValid = false;}";


  }
  $MyPage->addJS(JSP_BEGIN_CHECK, "js1", $js);
  //HTML
  $MyPage->buildHTML();
  echo $MyPage->getHTML();

} else if ($global_nom_ecran == "Map-2") {
  if ($mdp_new1 == $mdp_new2) { //Si tout OK
    update_other_pass($login, $mdp_new1); //Enregistre le nouveau mot de passe

    //HTML
    $MyPage = new HTML_message(_('Confirmation modification mot de passe'));
    $MyPage->setMessage(sprintf(_("Le mot de passe de '%s' a été modifié avec succès !"),$login));
    $MyPage->addButton(BUTTON_OK, "Gen-7");
    $MyPage->buildHTML();
    echo $MyPage->HTML_code;
  } else { //Si les 2 nouveaux mots de passes ne sont pas équivalents
    $html_err = new HTML_erreur(_("Refus de la modification.")." ");
    $html_err->setMessage(_("Les nouveaux mot de passe ne correspondent pas."));
    $html_err->addButton("BUTTON_OK", 'Gen-7');
    $html_err->buildHTML();
    echo $html_err->HTML_code;
    exit();

  }
} else signalErreur(__FILE__,__LINE__,__FUNCTION__); // "Ecran '$global_nom_ecran' inconnu !"
?>