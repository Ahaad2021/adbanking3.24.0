<?php
/* vim: set expandtab softtabstop=2 shiftwidth=2 */

/**
 * Batch : traitements de la comptabilité
 * @package Systeme
 **/

require_once 'lib/dbProcedures/agence.php';
require_once 'lib/dbProcedures/compta.php';
require_once 'batch/librairie.php';
require_once 'lib/misc/xml_lib.php';

function ouvertureExercice() {
  // Création d'un nouveau exercice
  global $dbHandler, $global_id_agence;
  $db  = $dbHandler->openConnection();
  $global_id_agence=getNumAgence();

  affiche(_("Ouverture exercice ..."));
  incLevel();

  // Récupération des informations de l'agence
  $agc = getAgenceDatas($global_id_agence);

  // Récupération des infos de l'exercice courant
  $exo = getExercicesComptables($agc["exercice"]);

  // définition de la période du nouveau exercice
  if (isset($exo)) {
    // date début nouvel exercice
    $date_fin_exo = pg2phpDateBis($exo[0]["date_fin_exo"]);
    $date_debut_nouvel_exo = date("d/m/Y", mktime(0,0,0,$date_fin_exo[0], $date_fin_exo[1]+1, $date_fin_exo[2]));

    // date fin nouvel exercice
    $DDE = php2pg($date_debut_nouvel_exo);
    $DDE = pg2phpDateBis($DDE);
    $date_fin_nouvel_exo = date("d/m/Y", mktime(0,0,0,$DDE[0], $DDE[1]-1, $DDE[2]+1));
    
    // Remise à zéro de la sequence exercice et création du nouvel exercice
    $sql = " SELECT reset_sequence('ad_exercices_compta','ad_exo_cpta_id_seq', 'id_exo_compta'); ";    
    $sql .= " INSERT INTO  ad_exercices_compta (date_deb_exo,id_ag,date_fin_exo,etat_exo) ";
    $sql .=" VALUES('$date_debut_nouvel_exo',$global_id_agence,'$date_fin_nouvel_exo',1)";

    $result = $db->query($sql);
    if (DB::isError($result)) {
      $dbHandler->closeConnection(false);
      signalErreur(__FILE__,__LINE__,__FUNCTION__); // "DB: ".$result->getMessage()
    }

    // Passer le nouvel exercice comme l'exercice en cours
    $sql = "UPDATE ad_agc SET exercice = (SELECT id_exo_compta FROM ad_exercices_compta WHERE ";
    $sql .= "id_ag=$global_id_agence AND date_deb_exo='$date_debut_nouvel_exo' AND date_fin_exo = '$date_fin_nouvel_exo')";

    $result = $db->query($sql);
    if (DB::isError($result)) {
      $dbHandler->closeConnection(false);
      signalErreur(__FILE__,__LINE__,__FUNCTION__); // "DB: ".$result->getMessage()
    }

  }

  $dbHandler->closeConnection(true);

  decLevel();
  affiche(_("Ouverture exercice terminée !"));

}

function cloturePeriodiqueAuto() {
  /*
    effectue une cloture périodique automatique

   IN: NEANT

   OUT: OBjetErr

   TRAITEMENTS:
     - récupère les informations de l'agence
     - vérifie si les clôtures périodiques automatiques sont autorisées
     - si elles sont autorisées prendre leur fréquence d'exécution
     - récupérer,en fonction de cette fréquence, la date ou il est sensé avoir une clôture automatique
     - si cette date est égale à la date d'exécution du batch alors faire la cloture périodique


  */

  global $dbHandler, $global_id_agence;
  global $date_jour, $date_mois, $date_annee;
  global $date_total;
  $db  = $dbHandler->openConnection();
  $global_id_agence = getNumAgence();
  affiche(_("Clôture périodique ..."));
  incLevel();

  //infos agences
  $agc = getAgenceDatas($global_id_agence);

  // si les clôtures automatiques sont autorisées
  if ($agc["cloture_per_auto"]=='t') {
    // informations sur l'exercice en cours, les clôtures automatiques ont lieu sur l'exo en cours
    $exo = getExercicesComptables($agc['exercice']);
    // conversion date aaaa/mm/jj => jj/mm/aaaa
    $DFE = $exo[0]["date_fin_exo"];
    $DFE = pg2phpDateBis($DFE);
    $date_fin_exo = date("d/m/Y", mktime(0,0,0,$DFE[0], $DFE[1], $DFE[2]));

    // on ne fait pas clôture période à la date de fin  exercice. C'est la fonction clôtureExercice() qui s'en charge
    if ($date_total != $date_fin_exo) {
      // fréquence de clôture automatique
      $freq = $agc["frequence_clot_per"];

      // les dates de fréquence
      $dates_freq = array();
      $dates_freq = getDatesWork();

      if ($freq==9) { // quotidienne
        $erreur=cloturePeriodique($dates_freq['today'], NULL);
      } else if ($freq==8) { //hebdomadaire
        if ($dates_freq['today'] == $dates_freq['fin_semaine'])
          $erreur=cloturePeriodique($dates_freq['today'], NULL);
      } else if ($freq==1) { //mensuelle
        if ($dates_freq['today'] ==  $dates_freq['fin_mois'])
          $erreur=cloturePeriodique($dates_freq['today'], NULL);

      } else if ($freq==2) {
        if ($dates_freq['today'] == $dates_freq['fin_bimestre'])
          $erreur=cloturePeriodique($dates_freq['today'], NULL);

      } else if ($freq==3) {
        if ($dates_freq['today'] == $dates_freq['fin_trimestre'])
          $erreur=cloturePeriodique($dates_freq['today'], NULL);
      } else if ($freq==4) {
        if ($dates_freq['today'] == $dates_freq['fin_semestre'])
          $erreur=cloturePeriodique($dates_freq['today'], NULL);
      }

      if ($erreur->errCode != NO_ERR) {
        signalErreur(__FILE__,__LINE__,__FUNCTION__); // "Erreur lors de la clôture périodique, code ".$erreur->errCode
        //return $erreur;
      }
    } // Fin si la date du batch n'est pas la fin de l'exercice
  } // Fin si clôture période automatique autorisée

  $dbHandler->closeConnection(true);

  decLevel();
  affiche(_("Clôture périodique terminée !"));

}

function transactionsFerlo() {
  global $lib_path;
	incLevel();
  $echangeFerlo = $lib_path."/ferlo/transaction/";
  $tab_files = listFiles_trans($echangeFerlo);
  if (is_array($tab_files)) {
    foreach($tab_files as $key => $file) {
      $XMLarray = traiteFichierXML($file);
      $erreur = ecrituresCompbleXml($XMLarray);
      if ($erreur->errCode == NO_ERR) {
        unlink($file);
      }
      debug($XMLarray,_("contenue fichier transacton"));
    }
  }
  decLevel();
  affiche(_("Traitement des transactions Ferlo terminé"));
}
function provision_credit_batch () {
	global $dbHandler, $global_id_agence, $error;
	affiche(_("Démarre le traitement provisions crédit ...") );
  incLevel();
  $db = $dbHandler->openConnection();

  //provision et reprise sur provision
  $myErr=provisionCredit( );
  if ($myErr->errCode != NO_ERR) {
  		$dbHandler->closeConnection(false);
        signalErreur(__FILE__,__LINE__,__FUNCTION__, $error[$myErr->errCode].$myErr->param);
        //return $erreur;
      }

  $dbHandler->closeConnection(true);

  decLevel();

  if(!is_null($myErr->param)) {
    affiche( _(sprintf("Provision                : %d dossiers de crédits traités ", $myErr->param["nbre_prov"])));
    affiche( _(sprintf("Reprise sur la provision : %d dossiers crédits traités", $myErr->param["nbre_prov_reprise"])));
  }

  affiche(_("Traitement provision crédit terminé"));
}

function traite_compta() {
  global $dbHandler, $global_id_agence, $global_id_exo;
  global $date_total;
  $db  = $dbHandler->openConnection();
  $global_id_agence = getNumAgence();
  affiche(_("Démarre le traitement comptable ..."));
  incLevel();

  // Recherche de l'id de l'exercice en cours
  $AGC = getAgenceDatas($global_id_agence);
  $id_exo = $AGC["exercice"];

  // Récupération des infos de l'exercice en cours
  $exo = array();
  $exo = getExercicesComptables($id_exo);
  $DFE = $exo[0]["date_fin_exo"];
  $DFE = pg2phpDateBis($DFE); // conversion date aaaa/mm/jj => jj/mm/aaaa
  $date_fin_exo = date("d/m/Y", mktime(0,0,0,$DFE[0], $DFE[1], $DFE[2]));

  // Clôture périodique si activée
  cloturePeriodiqueAuto();

  // on ouvre automatiquement un nouvel exercice désqu'on est à la fin de l'exercice en cours
  if ($date_fin_exo == $date_total) {
    ouvertureExercice();
    // on réinitialise l'exercice courant
    $AGC = getAgenceDatas($global_id_agence);
    $global_id_exo = $AGC["exercice"];

  }

  //calcul provision des crédits en souffrances si activé.
  //#549 provision auto pas seulement lor de la fin d'exercise
  if($AGC['provision_credit_auto'] == 't') {
    provision_credit_batch ();
  }

  //Traitement des transactions FERLO
  transactionsFerlo();

  decLevel();
  affiche(_("Traitement comptable terminé !"));
  $dbHandler->closeConnection(true);
}
