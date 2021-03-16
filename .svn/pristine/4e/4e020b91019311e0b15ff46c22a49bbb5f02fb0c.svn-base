<?php
require_once 'lib/dbProcedures/agence.php';
if ($global_nom_ecran == "Fjr-1") {
  if (get_statut_agence($global_id_agence) != 2) { //Si l'agence n'est pas fermée
    $MyPage = new HTML_erreur(_("Erreur"));
    $MyPage->setMessage(_("Impossible d'exécuter les traitements de nuits : l'agence n'est pas fermée !"));
    $MyPage->addButton(BUTTON_OK, "Gen-7");
    $MyPage->buildHTML();
    echo $MyPage->HTML_code;
  } else {
    $lb = get_last_batch($global_id_agence);
    $date = date("d/m/Y", mktime(0,0,0,date("m",$lb), date("d",$lb)+1, date("Y", $lb))); //Calcul la date pour lequel le batch va bosser
    $MyPage = new HTML_message(_("Demande confirmation d'exécution des traitements de nuits"));
    $msg = _("Etes-vous sûr de vouloir exécuter les traitements de nuits ? Pour rappel, cela aura pour conséquence suivantes")." :<br>";
    $msg .= "<ul>";
    $msg .= "<li>"._("Vous serez déconnecté automatiquement")."</li>";
    $msg .= "<li>"._("Les traitements de nuits seront exécutés immédiatement : vous devrez attendre qu'ils aient terminés avant de pouvoir vous reconnecter")."</li>";
    $msg .= "<li>".sprintf(_("Les traitements de nuits vont travailler pour la date du %s : vous ne pourrez plus ouvrir l'agence pour cette date ou toute date antérieure"),$date)."</li>";
    $msg .= "</ul>";
    $MyPage->setMessage($msg);
    $MyPage->addButton(BUTTON_OUI, "Fjr-2");
    $MyPage->addButton(BUTTON_NON, "Gen-7");
    $MyPage->buildHTML();
    echo $MyPage->HTML_code;
  }
} else if ($global_nom_ecran == "Fjr-2") {

} else signalErreur(__FILE__,__LINE__,__FUNCTION__); // "Ecran '$global_nom_ecran' inconnu"
?>