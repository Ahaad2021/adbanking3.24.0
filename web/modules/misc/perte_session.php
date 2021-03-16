<?php

/**
 * Perte de la session de l'utilisateur
 * @package Systeme
 **/

if ($global_nom_ecran == "Pse-1") {
  $MyPage = new HTML_erreur(_("Perte de la session"));
  $MyPage->setMessage(_("La session a été perdue, vous allez être déconnecté automatiquement !"));
  $MyPage->addButton(BUTTON_OK, "Out-3");
  $MyPage->buildHTML();
  echo $MyPage->HTML_code;
} else signalErreur(__FILE__,__LINE__,__FUNCTION__); // "Ecran '$global_nom_ecran' inconnu !"

?>