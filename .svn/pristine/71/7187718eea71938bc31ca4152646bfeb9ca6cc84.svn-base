<?php
/* vim: set expandtab softtabstop=2 shiftwidth=2 */

/**
 * Fonctions utilisées dans le batch
 * @package Systeme
 **/

if (!function_exists('getPhpDateTimestamp')) {
    function getPhpDateTimestamp($Date) {
      // Ex : 2002-02-05
      $Date = substr($Date,0,10);
      $M = substr($Date,5,2);
      $J = substr($Date,8,2);
      $A = substr($Date,0,4);
      return gmmktime(0,0,0,$M,$J,$A);
    }
}

function get_report($id_ag)
// Récupère le report de la table ad_agc
{

  global $dbHandler;
  $db = $dbHandler->openConnection();

  $result = $db->query("SELECT report_ferie FROM ad_agc WHERE id_ag=$id_ag");
  if ((DB::isError($result)) || ($result->numrows() != 1))
    erreur("get_report", _("La requête ne s'est pas exécutée correctement")." : ".$result->getMessage());
  $row = $result->fetchrow();

  $dbHandler->closeConnection(true);

  return $row[0];
}

function jour_ouvrable($date_jour, $date_mois, $date_annee, $nbre_jour) {

// Cette fonction renvoie la date du n ème jour ouvrable suivant la date $date_jour/$date_mois/$date_annee
// Si $nbre_jour est négatif, on remonte dans le temps
// IN  : $date_jour, $date_mois, $date_annee : La date de départ
//       $nbre_jours : Le nombre de jours à avancer / reculer
// OUT : La date demandée au format jj/mm/aaaa

  if ($nbre_jour > 0) $sens = 1;
  else $sens = -1;

  $dj = $date_jour;
  $dm = $date_mois;
  $da = $date_annee;
  for ($i = 0; $i < $nbre_jour*$sens; ) {
    $timestamp = mktime(0,0,0,$dm,$dj+$sens,$da); //Incrémente ou décrémente d'un jour
    $dj = date("d", $timestamp);
    $dm = date("m", $timestamp);
    $da = date("Y", $timestamp);
    if (! is_ferie($dj, $dm, $da)) ++$i;
  }
  $timestamp = gmmktime(0,0,0,$dm,$dj,$da);
  $dj = date("d", $timestamp);
  $dm = date("m", $timestamp);
  $da = date("Y", $timestamp);

  return $dj."/".$dm."/".$da;
}

function get_produit($id_doss) {
  //global $db;

  global $dbHandler, $global_id_agence;
  $db = $dbHandler->openConnection();

  $result = $db->query("SELECT id_prod FROM ad_dcr WHERE id_ag = $global_id_agence AND id_doss=$id_doss");
  if ((DB::isError($result)) || ($result->numrows() != 1))
    erreur("get_produit()", _("La requête ne s'est pas exécutée correctement")." : ".$result->getMessage());
  $row = $result->fetchrow();

  $dbHandler->closeConnection(true);

  return $row[0];
}

function getPrelevAuto($id_dossier) {
  //Renvoie le booléen indiquant si le prélèvement automatique est autorisé pour ce dossier
  // FIXME : Pas du tout optimisé
  ////////global $db;

  global $dbHandler, $global_id_agence;
  $db = $dbHandler->openConnection();

  $sql = "SELECT prelev_auto, id_doss, cre_etat FROM ad_dcr WHERE id_ag = ".$global_id_agence." AND id_doss = ".$id_dossier;
  $result = $db->query($sql);
  if (DB::isError($result)) {
    erreur("getPrelevAuto()", _("La requête ne s'est pas exécutée correctement")." : ".$result->getMessage());
  }

  $row = $result->fetchrow();

  $dbHandler->closeConnection(true);

  return ($row[0] == 't');

}

function getIdClient($id_dossier) { //Renvoie l'id du client du dossier
  //global $db;
  global $dbHandler, $global_id_agence;
  $db = $dbHandler->openConnection();

  $sql = "SELECT id_client FROM ad_dcr WHERE id_ag = ".$global_id_agence." AND id_doss = ".$id_dossier;
  $result = $db->query($sql);
  if (DB::isError($result)) erreur("getIdClient()", $result->getMessage());
  $row = $result->fetchrow();
  $dbHandler->closeConnection(true);
  return $row[0];
}

function getComptesFraisTenue($freq) {
  // Fonction qui renvoie l'ensemble des comptes pour lesquels des frais de tenue doivent être perçus pour la date du jour
  // IN: $freq = 4 si fin d'année
  //             3 si fin de semestre
  //             2 si fin de trimestre
  //             1 si fin de mois
  //             0 sinon
  // OUT : Tableau avec la liste des id_cpte pour lesquels des frais devront être prélevés
  //global $db;

  global $dbHandler;
  $db = $dbHandler->openConnection();

  $sql = "SELECT id_cpte FROM ad_cpt a, adsys_produit_epargne b WHERE a.id_ag = b.id_ag AND a.id_prod = b.id AND b.frais_tenue_cpt > 0 AND frequence_tenue_cpt <= $freq;";
  $result=$db->query($sql);
  if (DB::isError($result))
    erreur("getComptesFraisTenue "._("ne s'est pas exécutée correctement")." : ".$result->getMessage());
  $ACC = array();
  while ($row=$result->fetchrow()) {
    array_push($ACC, $row[0]);
  }

  $dbHandler->closeConnection(true);
  return $ACC;
}

function getListeComptesProduit($id_prod) {
  // Fonction triviale renvoyant un tableau contenant les id de tous les comptes du produit $id_prod
  //global $db;

  global $dbHandler, $global_id_agence;
  $db = $dbHandler->openConnection();

  $sql = "SELECT id_cpte FROM ad_cpt WHERE id_ag = $global_id_agence AND id_prod = $id_prod AND etat_cpte <> 2;";
  $result=$db->query($sql);
  if (DB::isError($result))
    erreur("getListeComptesProduit "._("ne s'est pas exécutée correctement")." : ".$result->getMessage());
  $ACC = array();
  while ($row=$result->fetchrow()) {
    array_push($ACC, $row[0]);
  }

  $dbHandler->closeConnection(true);

  return $ACC;
}

function verif_batch_exec($date_jour, $id_agence) {
  /*

    Permet de savoir si on a déjà exécuté le batch du jour afin de pouvoir lancer le
    prélèvement des frais de tenue de compte

  IN
      La date d'exécution du batch du jour sous la forme d'array

  OUT
      TRUE si le batch a été exécute
      FALSE autrement

  Il s'agit de comparer last_batch dans la table agence avec la date avec laquelle le batch
   travaille

  */

  $date_last_batch = pg2phpDate(get_last_batch($id_agence));

  //last batch
  $date_last_batch_0 = $date_last_batch[0];
  $date_last_batch_1 = $date_last_batch[1];
  $date_last_batch_2 = $date_last_batch[2];
  settype($date_last_batch_0, "int");
  settype($date_last_batch_1, "int");
  settype($date_last_batch_2, "int");
  $t1 = mktime(0, 0, 0, $date_last_batch_1, $date_last_batch_0, $date_last_batch_2);
  //date du jour - 1 = la veille de la date du jour : batch exécuté pour hier ?
  $date_jour_0 = $date_jour[0];
  $date_jour_1 = $date_jour[1];
  $date_jour_2 = $date_jour[2];
  settype($date_jour_0, "int");
  settype($date_jour_1, "int");
  settype($date_jour_2, "int");
  $t2 = mktime(0,0,0, $date_jour_1, $date_jour_0 - 1, $date_jour_2);

  if ( $t1 >= $t2 ) {
    return TRUE;
  } else {
    return FALSE;
  }

}

function verif_frais_tenue($date_jour, $id_agc) {
  //Vérifier si les frais de tenue de compte ont déjà été prélevés pour ce jour.
  //On compare last_batch et last_prelev
  //date jour est la date d'exécution du batch, c'est normalement la meme chose que last batch
  //date jour est de la forme jj/mm/aaa
  //Retourne TRUE si déjà exécuté, FALSE autrement

  global $dbHandler;
  $db = $dbHandler->openConnection();

  $sql = "SELECT last_prelev_frais_tenue FROM ad_agc WHERE id_ag = $id_agc;";

  $result=$db->query($sql);
  if (DB::isError($result))
    erreur("verif_frais_tenue : "._("erreur d'exécution")." : ".$result->getMessage());

  $tmprow = $result->fetchrow();
  $last_prelev = $tmprow[0];

  if ($result->numRows() > 1) {
    $dbHandler->closeConnection(false);
    erreur("verif_frais_tenue : "._("les frais ont été prélevés plus d'une fois")." : ".$result->getMessage());
  } else if ($result->numRows() == 1) {
    //comparer les dates
    $formate_last_prelev = pg2phpDate($last_prelev);
    if ($formate_last_prelev == $date_jour) { //on a pris les frais
      $dbHandler->closeConnection(true);
      return TRUE;
    } else {//FIXME : gérer les cas où last prelev > last batch : ne doit pas arriver
      $dbHandler->closeConnection(true);
      return FALSE;
    }
  }
}

function getDatesWork() {
  //Fonction qui permet de déterminer les dates de travail pour la rémunération de l'épargne
  //Renvoie un array date => (today,fin de semaine, fin de mois,fin de bimestre, fin de trimestre, fin de semestre, fin année
  //on calcule ces dates et on les compare à la date du jour pour voir si on est à une de ces dates
  //pour faire la rémunération des comptes

  global $date_jour, $date_mois, $date_annee;
  global $date_total;

  //détermination des dates de travail
  $dates = array();
  $dates['today'] = $date_total;

  //voir si on est à la fin d'une semaine. on suppose par 7 jours et non par tous les dimanches ou autre
  // 1...7...14...21...28...5
  $semaine = ceil($date_jour / 7);
  $date_fin_semaine = date("d/m/Y", mktime(0, 0, 0, $date_mois, $semaine * 7, $date_annee));
  $dates['fin_semaine'] = $date_fin_semaine;

  //voir si on est à la fin du mois
  $date_fin_mois = date("d/m/Y", mktime(0, 0, 0, $date_mois + 1, 0, $date_annee));
  $dates['fin_mois'] = $date_fin_mois;

  //voir si on est à la fin d'un bimestre
  $bimestre = ceil($date_mois / 2);
  $date_fin_bimestre = date("d/m/Y", mktime(0, 0, 0, ($bimestre * 3) +1, 0, $date_annee));
  $dates['fin_bimestre'] = $date_fin_bimestre;

  //voir si on est à la fin d'un trimestre
  $trimestre = ceil($date_mois / 3);
  $date_fin_trimestre = date("d/m/Y", mktime(0, 0, 0, ($trimestre * 3) +1, 0, $date_annee));
  $dates['fin_trimestre'] = $date_fin_trimestre;

  //voir si on est à la fin d'un semestre
  $semestre = ceil($date_mois / 6);
  $date_fin_semestre = date("d/m/Y", mktime(0, 0, 0, ($semestre * 6) +1, 0, $date_annee));
  $dates['fin_semestre'] = $date_fin_semestre;

  //est-on à la fin de l'année ?
  $date_fin_annee = date("d/m/Y", mktime(0, 0, 0, 12, 31, $date_annee));
  $dates['fin_annee'] = $date_fin_annee;

  return $dates;

}

/**
 * Initialise un certain nombre de variables globales nécessaires à la bonne exécution du batch
 * Cette fonction prend du sens dans le cas où le batch s'exécute à partir de cron et où, donc, l'environnement n'a pas été initialisé par la fonction de login
 * @author Thomas FASTENAKEL
 * @since 2.0
 * @return Array Tableau des variables globales
 */
function initGlobalVarsMA() {
  global $dbHandler;
  $db = $dbHandler->openConnection();

  //Recherche info agence
  $retour["id_ag"] = getNumAgence();
  $sql = "SELECT libel_ag, statut, libel_institution, type_structure, exercice, langue_systeme_dft ";
  $sql .= "FROM ad_agc WHERE id_ag=".$retour['id_ag'];
  $result = $db->query($sql); //Cherche ds table des agences
  if (DB::isError($result)) {
    $dbHandler->closeConnection(false);
    signalErreur(__FILE__,__LINE__,__FUNCTION__);
  } else if ($result->numrows() <> 1) {
    $dbHandler->closeConnection(false);
    signalErreur(__FILE__,__LINE__,__FUNCTION__); // "Aucune ou plusieurs occurences de la même agence!"
  }
  $row = $result->fetchrow();
  $retour['libel_ag'] = $row[0];
  $retour['statut_ag'] = $row[1];
  $retour['institution'] = $row[2];
  $retour['type_structure'] = $row[3];
  $retour['exercice'] = $row[4];
  $retour['langue_systeme_dft'] = $row[5];

  // Recherche infos devise de référence
  $sql = "SELECT code_devise, precision FROM devise WHERE id_ag = ".$retour["id_ag"]." AND code_devise = (SELECT code_devise_reference FROM ad_agc WHERE id_ag =".$retour["id_ag"].")";
  $result = $db->query($sql);
  if (DB::isError($result)) {
    $dbHandler->closeConnection(false);
    signalErreur(__FILE__,__LINE__,__FUNCTION__);
  }
  $row = $result->fetchrow();
  $retour['monnaie'] = $row[0];
  $retour['monnaie_prec'] = $row[1];

  // Sommes-nous en mode unidevise ou multidevise
  $sql = "SELECT count(*) FROM devise WHERE id_ag = ".$retour["id_ag"];
  $result = $db->query($sql);
  if (DB::isError($result)) {
    $dbHandler->closeConnection(false);
    signalErreur(__FILE__,__LINE__,__FUNCTION__);
  }
  $row = $result->fetchrow();
  if ($row[0] > 1)
    $retour['multidevise'] = 1;
  else
    $retour['multidevise'] = 0;

  $dbHandler->closeConnection(true);

  return $retour;
}

/**
 * Mettre ici toutes les taches de nettoyage / archivage qui doivent etre effectuées quotdiennement par le batch
 * @author Thomas FASTENAKEL
 * @return 1
 */
function clean() {
  // Vide pour le moment
  return 1;
}
/**
 * focntion permettant de verfier la cohérence de données
 */
function verif_coherence_donnee_batch($date, $id_his = NULL) {
	global $soldeCreditSoldeInterneCredit;
	global $soldeComptaSoldeInterneCredit;

	affiche ( _ ( "Démarre Vérification cohérence de donnée  ..." ) );
	incLevel ();

	require_once 'lib/dbProcedures/coherence_donnee.php';
	affiche ( _ ( "Démarre Vérification Egalité solde comptable de crédit - compte interne de crédit  ..." ) );
	incLevel ();

	$myerr = coherenceComptaComptesInternesCredit ( $date );
	if ($myerr->errCode != NO_ERR) {
		return $myerr;
	}
	$soldeComptaSoldeInterneCredit = $myerr->param;
	affiche ( sprintf ( _ ( "OK (%s ne sont pas égaux )" ), count ( $myerr->param ) ), true, true );

	decLevel ();
	affiche ( _ ( "Vérification Egalité solde comptable de crédit - compte interne de crédit  terminé" ) );
	debug ( $myerr, 'gg' );

	affiche ( _ ( "Démarre Vérification Egalité solde dossier de crédit - compte interne de crédit  ..." ) );
	incLevel ();
	$myerr1 = coherenceDossierCreditComptesInternesCredit ( $date );
	if ($myerr1->errCode != NO_ERR) {
		return $myerr1;
	}
	$soldeCreditSoldeInterneCredit = $myerr1->param;
	debug ( $myerr1, 'gg' );
	affiche ( sprintf ( _ ( "OK (%s ne sont pas égaux )" ), count ( $myerr1->param ) ), true, true );

	decLevel ();
	affiche ( _ ( "Vérification Egalité solde dossier de crédit - compte interne de crédit terminé ..." ) );

	//#357 - Vérification Équilibre inventaire - comptabilité
	affiche ( _ ( "Démarre Vérification Équilibre inventaire - comptabilité  ..." ) );
	incLevel ();
	$myerr2 = coherenceInventaireCompta ($id_his);
	if ($myerr2->errCode != NO_ERR) {
		return $myerr2;
	}

	$counter = $myerr2->param;
	affiche ( sprintf ( _ ( "OK (%s comptes comptables sont déséquilibrés )" ), $counter[0] ), true, true );

	decLevel ();
	affiche ( _ ( "Vérification Équilibre inventaire - comptabilité terminé  ..." ) );
	// fin : #357 - Vérification Équilibre inventaire - comptabilité
	
	decLevel ();
	affiche ( _ ( "Vérification cohérence de donnée terminée !" ) );
	return new ErrorObj ( NO_ERR );
}

?>
