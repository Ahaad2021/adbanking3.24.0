<?php

require_once 'lib/misc/VariablesGlobales.php';
require_once 'lib/dbProcedures/agence.php';

// si le script est lancé via l'interface ADbanking
if ($global_nom_ecran == "Cdb-1") {
  // sélection des agences à consolider
  $MyPage = new HTML_GEN2(_("Consolidation des agences"));

  $agences = getAllIdNomAgence(); // récupération de toutes les agences

  // fonctionnalité accéssible qu'au siège : suppression de la liste l'entrée du siège
  $siege = getNumAgence();
  unset($agences[$siege]);

  //Boutons
  $MyPage->addFormButton(1, 1, "valider", _("Consolider"), TYPB_SUBMIT);
  $MyPage->setFormButtonProperties("valider", BUTP_PROCHAIN_ECRAN, "Cdb-2");
  $MyPage->addFormButton(1, 2, "annuler", _("Annuler"), TYPB_SUBMIT);
  $MyPage->setFormButtonProperties("annuler", BUTP_PROCHAIN_ECRAN, "Gen-7");
  $MyPage->setFormButtonProperties("annuler", BUTP_CHECK_FORM, false);

  $MyPage->buildHTML();
  echo $MyPage->getHTML();
}
elseif ($global_nom_ecran == "Cdb-2") {
  global $global_id_agence;
  $MyPage = new HTML_message(_("Consolidation de données"));

  if (isset($_POST['agence']) and ($_POST['agence'] != $global_id_agence)) // consolidation d'une agence
    setGlobalIdAgence($_POST['agence']);


  // fermeture de la session pour pouvoir utiliser flush() et envoyer les données HTTP en continu.
  ///session_write_close(); le resetGlobalIdAgence ne le permet pas

  // lancement du script de consolidation
  require('script_consolidation_db.php');

  // reinitialisation de global_id_agence
  //resetGlobalIdAgence();

  // affichage des résultats en temps réel
  flush();
  $liste_ag = "<BR>";
  foreach($SESSION_VARS['agence_consolidees'] as $ag_conso => $nom_ag) {
    $liste_ag .= $nom_ag['id']." ".$nom_ag['nom']."<br />";
  }
  if($SESSION_VARS['erreur'] != NULL)
  	$MyPage->setMessage($msg.$SESSION_VARS['erreur']);
  elseif (sizeof($SESSION_VARS['agence_consolidees']) <= 0) {
    $MyPage->setMessage($msg._("Aucune agence n'a été consolidée: vérifier la présence des fichiers dump dans le repertoire images_consolidation."));
  } else {
    $MyPage->setMessage($msg._("Les données ont été consolidées avec succès, les agences consolidées sont").": ".$liste_ag);
  }
  
  $MyPage->addButton(BUTTON_OK, "Gen-7");
  $MyPage->buildHTML();
  echo $MyPage->HTML_code;
}
else signalErreur(__FILE__,__LINE__,__FUNCTION__, sprintf(_("L'écran %s n'a pas pu être trouvé"), $global_nom_ecran));

?>