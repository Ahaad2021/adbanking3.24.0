<?php
require_once 'lib/html/HTML_message.php';
require_once 'lib/html/HTML_erreur.php';
require_once 'lib/dbProcedures/agence.php';

if ($global_nom_ecran == "Fag-1") {
  $MyPage = new HTML_message(_("Fermeture agence, demande confirmation"));
  $MyPage->setMessage(_("Etes-vous sûr de vouloir fermer l'agence ?"));
  $MyPage->addButton(BUTTON_OUI, "Fag-2");
  $MyPage->addButton(BUTTON_NON, "Gen-7");
  $MyPage->buildHTML();
  echo $MyPage->HTML_code;
} else if ($global_nom_ecran == "Fag-2") {
  $result = fermeture_agence($global_id_agence);

  if ($result['result'] < 0) {
    switch ($result['result']) {
    case -1 :
      $msg=_("des utilisateurs possédant un guichet sont encore loggés")." (";
      while (list(,$value) = each($result['login'])) {
        $msg .= "'$value', ";
      }
      $msg = substr($msg,0,strlen($msg)-2);
      $msg .= ") !";
      break;
    case -2 :
      $msg=_("des guichets n'ont pas été correctements fermés")."(";
      while (list(,$value) = each($result['login'])) {
        $msg .= "'$value', ";
      }
      $msg = substr($msg,0,strlen($msg)-2);
      $msg .= ") !";
      break;
    case -4 :
      $msg = _("l'agence est déjà fermée !");
      break;
    case -5 :
      $msg = _("l'agence est en cours de traitements de nuit !");
      break;
    default :
      $msg = _("erreur inconnue !");
      break;
    }

    $MyPage = new HTML_erreur(_("Fermeture de l'agence"));
    $MyPage->setMessage(_("Impossible de fermer l'agence")." : ".$msg);
    $MyPage->addButton(BUTTON_OK, "Gen-7");
    $MyPage->buildHTML();
    echo $MyPage->HTML_code;
  } else {
    $global_statut_agence = 2;
    $MyPage = new HTML_message(_("Fermeture de l'agence"));
    $MyPage->setMessage(_("L'agence a été fermée avec succès!"));
    $MyPage->addButton(BUTTON_OK, "Gen-7");
    $MyPage->buildHTML();
    echo $MyPage->HTML_code;
  }
} else signalErreur(__FILE__,__LINE__,__FUNCTION__); // "Nom d'écran inconnu : '$global_nom_ecran'"

?>