<?php
/* vim: set expandtab softtabstop=2 shiftwidth=2 foldmethod=marker: */

if ($global_nom_ecran == "Tot-1") {
  $MyPage = new HTML_erreur(_("Timeout"));

  if ($global_timeout > 0 && $global_timeout * 60 < get_cfg_var("session.gc_maxlifetime")) {
    $timeout = $global_timeout;
  } else {
    $timeout = get_cfg_var("session.gc_maxlifetime") / 60;
  }

  $MyPage->setMessage(sprintf(_("Votre temps d'inactivité a dépassé le maximum permis (%d minutes), vous avez été déconnecté automatiquement !"), $timeout));
  $MyPage->addButton(BUTTON_OK, "Out-3");

  // Ajout d'une variable indiquant à Out-3 qu'on vient de l'écran Timeout
  $SESSION_VARS['timeout'] = true;

  $MyPage->buildHTML();
  echo $MyPage->HTML_code;
}

else signalErreur(__FILE__,__LINE__,__FUNCTION__); // "Ecran '$global_nom_ecran' inconnu !"

?>