<?php
/* vim: set expandtab softtabstop=2 shiftwidth=2 foldmethod=marker: */

/**
 * [230] Déconnexion
 * Cette fonction appelle les écrans suivants :
 * - Flo-1 : Choix du login
 * - Flo-2 : Déconnexion du login
 *
 * @package Systeme
 **/

require_once 'lib/dbProcedures/systeme.php';

/*{{{ Flo-1 : Choix du login */
if ($global_nom_ecran == "Flo-1") {
  $logins = logged_logins();

  $MyPage = new HTML_GEN2(_("Déconnexion autre login"));

  $MyPage->addField("login", _("Autres logins connectés"), TYPC_LSB);
  $MyPage->setFieldProperties("login", FIELDP_IS_REQUIRED, true);

  reset($logins);
  while (list($key,$value) = each($logins)) {
    if ($global_nom_login != $value) {
      //On ne peut pas déconnecter l'utilisateur courant
      if (getIDGuichetFromLogin($value) > 0)
        // On ne peut pas déconnecter un login sans guichet
        $MyPage->setFieldProperties("login", FIELDP_ADD_CHOICES, array($value=>$value));
    }
  }

  $MyPage->addFormButton(1,1, "butvalid", _("Déconnecter le login sélectionné"), TYPB_SUBMIT);
  $MyPage->setFormButtonProperties("butvalid", BUTP_PROCHAIN_ECRAN, "Flo-2");
  $MyPage->addFormButton(2,1, "butannul", _("Annuler"), TYPB_SUBMIT);
  $MyPage->setFormButtonProperties("butannul", BUTP_PROCHAIN_ECRAN, "Gen-7");
  $MyPage->setFormButtonProperties("butannul", BUTP_CHECK_FORM, false);

  $MyPage->buildHTML();
  echo $MyPage->getHTML();
}
/*}}}*/

/*{{{ Flo-2 : Déconnexion du login choisi */
else if ($global_nom_ecran == "Flo-2") {
  force_logout($login);
  $MyPage = new HTML_message(_("Déconnexion autre login"));
  $MyPage->setMessage(sprintf(_("le login '%s' a été déconnecté avec succès !"),$login));
  $MyPage->addButton(BUTTON_OK, "Gen-7");
  $MyPage->buildHTML();
  echo $MyPage->HTML_code;
}
/*}}}*/

else signalErreur(__FILE__,__LINE__,__FUNCTION__, sprintf(_("L'écran %s n'a pas pu être trouvé"), $global_nom_ecran));

?>