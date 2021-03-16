<?php

require_once 'lib/dbProcedures/historique.php';
require_once 'lib/dbProcedures/tarification.php';
require_once 'lib/dbProcedures/message_queue.php';
require_once 'batch/batch_declarations.php';
require_once 'batch/divers.php';

function traite_mobile($mouvements_credit){
  affiche(_("Traitement de frais forfaitaire SMS pour les mouvements de credits !"));
  incLevel();

  $process = preleveFraisTransactionnelSMS($mouvements_credit, 212);

  // Envoyer message chez le broker si le message queue system est active
  if (isMSQEnabled()) {
      envoi_sms_mouvement($mouvements_credit);
  }

  if($process == true) {
    affiche(_("OK"));
  }
  else {
    affiche(_("Erreur lors du traitement de frais forfaitaire SMS pour les mouvements de credits"));
  }

  decLevel();
  affiche(_("Traitement de de frais forfaitaire SMS pour les mouvements de credits terminé !"));
}
?>