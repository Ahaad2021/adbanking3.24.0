<?php
/* vim: set expandtab softtabstop=2 shiftwidth=2 foldmethod=marker: */

/**
 * @package Ifutilisateur
 */
require_once 'lib/dbProcedures/agence.php';
require_once 'lib/dbProcedures/bdlib.php';
require_once 'lib/misc/divers.php';
require_once 'lib/multilingue/locale.php';
require_once 'lib/dbProcedures/parametrage.php';

/**
 * Vérifie si le mot de passe est correct et si la machine n'est pas déjà loggé.
 * Si ok : crée un enregistrement dans la table ad_sess.
 * @param int $id_sess Identifiant de la session
 * @param str $login_name Le nom du login de la personne se connectant
 * @param str $login_pwd Le mot de passe de la personne se connectant
 * @param str $id_adr L'adresse IP par laquelle la personne se connecte
 * @param date $date La date et l'heure de la connexion
 * @return int les valeurs de retour suivantes :
 *     1 : OK
 *    -1 : nom ou pwd incorrect
 *    -3 : Cette personne a déjà ouvert une session
 *    -4 : Agence fermée et guichet associé au login
 *    -5 : Agence en cours de batch
 *    -6 : utilisateurs inactifs
 *    -7 : le batch ne s'est pas exécuté la veille
 *    -8 : le nombre maximum de 5 tentatives de connexions autorisées a été dépassé
 */
function check_login($id_sess, $login_name, $login_pwd, $ip_adr, $date) {
  global $dbHandler;
  global $global_langue_utilisateur;

  $db = $dbHandler->openConnection();
  strtolower($login_pwd);
  $login_name = addslashes($login_name);
  $sql = "SELECT pwd=md5('$login_pwd'), guichet,langue,login_attempt FROM ad_log WHERE login='$login_name'";
  $result = $db->query($sql);
  if (DB::isError($result)) {
    $dbHandler->closeConnection(false);
    signalErreur(__FILE__,__LINE__,__FUNCTION__);
  } else if ($result->numrows() > 1) {
    $dbHandler->closeConnection(false);
    signalErreur(__FILE__,__LINE__,__FUNCTION__, _('Plusieurs occurences du même login !'));
  } else if ($result->numrows() != 1) { //Si login inexistant
    $dbHandler->closeConnection(false);
    return array("result"=>-1);
  }
  $row = $result->fetchrow();

  $global_langue_utilisateur = $row[2];
  reset_langue();

  $data_param_security = getParamSecurity();
  if ($row[3] >= $data_param_security['tentative_connexion'] and $login_name != 'admin'){ // si le nombre maximum de 5 tentatives de connexions autorisées a été dépassé
  	$dbHandler->closeConnection(false);
  	return array("result"=>-8);
  }
  
  if ($row[0] != 't') { //Si mot de passe incorrect
  	
  	$sql = "UPDATE ad_log  SET  login_attempt= login_attempt + 1 WHERE login = '$login_name'";
  	$resultat = $db->query($sql);
  	if (DB::isError($resultat)) {
  		$dbHandler->closeConnection(false);
  		signalErreur(__FILE__,__LINE__,__FUNCTION__); // $resultat->getMessage()
  	}
  
  	
    $dbHandler->closeConnection(true);
    return array("result"=>-1);

  }
  $guichet = $row[1];

  //Recherche si login déjà loggé
  $sql = "SELECT count(login) FROM ad_ses WHERE login='$login_name'";
  $result = $db->query($sql);
  if (DB::isError($result)) {
    $dbHandler->closeConnection(false);
    signalErreur(__FILE__,__LINE__,__FUNCTION__);
  }
  $row = $result->fetchrow();
  if ($row[0] > 0) { //Si login déjà loggé
    $dbHandler->closeConnection(false);
    return array("result"=>-3);
  }

  // Vérifie le statut de l'agence
  $id_ag = getNumAgence();
  $sql = "SELECT statut FROM ad_agc WHERE id_ag = $id_ag";
  $result = $db->query($sql);
  if (DB::isError($result)) {
    $dbHandler->closeConnection(false);
    signalErreur(__FILE__,__LINE__,__FUNCTION__);
  } else if ($result->numrows() != 1) {
    $dbHandler->closeConnection(false);
    signalErreur(__FILE__,__LINE__,__FUNCTION__); // "Retour DB inattendu : nombre occurences agence incohérent"
  }
  $row = $result->fetchrow();


  $sql = "SELECT P.conn_agc FROM adsys_profils P inner join ad_log L ON L.profil = P.id WHERE login = '$login_name'";
  $result = $db->query($sql);
  if (DB::isError($result)) {
    $dbHandler->closeConnection(false);
    signalErreur(__FILE__,__LINE__,__FUNCTION__);
  }
  if ($result->numrows() != 1) {
    $dbHandler->closeConnection(false);
    signalErreur(__FILE__,__LINE__,__FUNCTION__); // "Profil '$profil_id' non-trouvé dans la base de données"
  }
  $res = $result->fetchrow();
  $is_conn_agc = $res[0];

  if (($row[0] == 2) && ($is_conn_agc == 'f')) { //Si l'agence est fermée et guichet associé
    $dbHandler->closeConnection(false);
    return array("result"=>-4);
  } else if ($row[0] == 3) { // L'agence est en cours de batch
    $dbHandler->closeConnection(false);
    return array("result"=>-5);
  }

  // Gestion du statut actif ou inactif des utilisateurs
  $sql = "SELECT statut from ad_uti,ad_log WHERE ad_uti.id_utilis=ad_log.id_utilisateur and ad_log.login='$login_name'";
  $result = $db->query($sql);
  if (DB::isError($result)) {
    $dbHandler->closeConnection(false);
    signalErreur(__FILE__,__LINE__,__FUNCTION__);
  }
  $row = $result->fetchrow();
  if ($row[0] == 2) { // L'utilisateur est inactif
    $dbHandler->closeConnection(false);
    return array("result"=>-6);
  }

  // On vérifie que le batch s'est bien exécuté la veille
  $sql = "SELECT last_batch FROM ad_agc WHERE id_ag=$id_ag";
  $result = $db->query($sql);
  if (DB::isError($result)) {
    $dbHandler->closeConnection(false);
    signalErreur(__FILE__,__LINE__,__FUNCTION__);
  }
  $row = $result->fetchrow();
  $last_batch = pg2phpDate($row[0]);

  // Sélection du profil correspondant au login connécté
  $sql = "SELECT login,profil FROM ad_log where login='$login_name'";
  $resultat = $db->query($sql);
  if (DB::isError($resultat)) {
    $dbHandler->closeConnection(false);
    signalErreur(__FILE__,__LINE__,__FUNCTION__);
  }
  $row = $resultat->fetchrow();
  $profil=$row[1];

  // Vérifier si le profil a un guichet
  $sql = "SELECT id,guichet FROM adsys_profils where id='$profil'";
  $resultat = $db->query($sql);
  if (DB::isError($resultat)) {
    $dbHandler->closeConnection(false);
    signalErreur(__FILE__,__LINE__,__FUNCTION__); // $resultat->getMessage()
  }
  $row = $resultat->fetchrow();
  $guichet=$row[1];

  if ($last_batch != hier(date("d/m/Y")) && $guichet !='f')
    return array("result"=>-7);

  // On vérifie si la session n'est pas en cours d'utilisation
  $sql = "SELECT login FROM ad_ses WHERE id_sess = '$id_sess' AND login != '$login_name'";
  $result = $db->query($sql);
  if (DB::isError($result)) {
    $dbHandler->closeConnection(false);
    signalErreur(__FILE__,__LINE__,__FUNCTION__, $result->getMessage());
  }
  if ($result->numrows() > 0) {
    // Si la session est déjà utilisée, on l'éjecte
    $row = $result->fetchrow(DB_FETCHMODE_ASSOC);
    delete_session($row['login']);
  }
  
  //remetre le flag a 0 si l'utilisateur est bien connecté
  $sql = "UPDATE ad_log SET login_attempt = 0 WHERE login = '$login_name'";
  $resultat = $db->query($sql);
  if (DB::isError($resultat)) {
  	$dbHandler->closeConnection(false);
  	signalErreur(__FILE__,__LINE__,__FUNCTION__); 
  }  

  // On crée une entrée pour la session
  $sql = "INSERT INTO ad_ses(id_sess, login, adr_ip, date_creation, sess_status) VALUES('$id_sess','$login_name','$ip_adr', '$date', 0)";
  $result = $db->query($sql);
  if (DB::isError($result)) {
    $dbHandler->closeConnection(false);
    signalErreur(__FILE__,__LINE__,__FUNCTION__, _("La session n'a pas pu être créée !"));
  }
  if ($login_name != 'admin') {
//    $licence = getLicence();
//    $erreur = isLicenceValide($licence);
  }
  $dbHandler->closeConnection(true);
  return array("result"=>1, "errCode"=>$erreur->errCode);
}

/**
 * Recherche les infos d'un login à partir de son identifiant de session
 * @param str $id_sess Identifiant de session
 * @return array un tableau contenant les informations suivantes
 *    ['id_ag'] : id de l'agence
 *    ['libel_ag'] : libellé agence
 *    ['id_guichet'] : id guichet
 *    ['libel_guichet'] : libellé guichet
 *    ['login'] : nom de l'utilisateur
 *    ['id_utilisateur'] : Identificateur de l'utilisateur
 *    ['nom_utilisateur'] : Nom complet de l'utilisateur (prénom nom)
 *    ['monnaie'] : abréviation de la monnaie de référence
 *    ['monnaie_prec'] : précision de la monnaie de référence (chiffres après virgule)
 *    ['multidevise'] : booléen qui indique si on travaille ne mode multidevise (pour comptabilité avec anciens systèmes)
 *    ['id_profil'] : profil auquel appartient le login
 *    ['billets'] : billets définis (sous-tableau : ['libel'], ['valeur'])
 *    ['timeout'] : timeout associé au profil du login
 *    ['profil_axs'] : array contenant toutes les fonctions auquel à droit le profil associé
 *    ['institution'] : libellé de l'institution
 *    ['have_left_frame'] : afficher frame gauche
 *    ['exercice'] : exercice comptable en cours
 *    ['langue'] : langue de l'interface utilisateur
 *    ['langue_systeme_dft'] : lanque par défaut du système
 *    ['date_mod_pwd'] : date de la modification de mot de passe
 *    ['pwd_non_expire'] :boolean qui indique si le mot de passe n'expire jamais
 */
function get_login_info($id_sess) {
  global $dbHandler, $global_id_agence;
  $db = $dbHandler->openConnection();

  //Recherche agence et login
  $sql = "SELECT login FROM ad_ses WHERE id_sess = '$id_sess'";
  $result = $db->query($sql); //Va chercher dans la table des sessions
  if (DB::isError($result)) {
    $dbHandler->closeConnection(false);
    signalErreur(__FILE__,__LINE__,__FUNCTION__);
  } else if ($result->numrows() <> 1) {
    $dbHandler->closeConnection(false);
    signalErreur(__FILE__,__LINE__,__FUNCTION__); // "Aucune ou plusieurs occurences de la même adresse !"
  }
  $row = $result->fetchrow();
  $retour['login'] = $row[0];

  //Recherche info agence
  $retour["id_ag"] = getNumAgence(); //$global_id_agence;
  $sql = "SELECT libel_ag, statut, libel_institution, type_structure, exercice, langue_systeme_dft ";
  $sql .= "FROM ad_agc WHERE id_ag=".$retour["id_ag"]; // $global_id_agence
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
  $sql = "SELECT code_devise, precision FROM devise WHERE id_ag = ".$retour["id_ag"]." and code_devise = (SELECT code_devise_reference FROM ad_agc WHERE id_ag =".$retour["id_ag"].")";
  $result = $db->query($sql);
  if (DB::isError($result)) {
    $dbHandler->closeConnection(false);
    signalErreur(__FILE__,__LINE__,__FUNCTION__);
  } else if ($result->numrows() <> 1) {
    //   echo "<FONT COLOR=red> ATTENTION, un devise de référence doit être paramétrée</FONT>";
  }
  $row = $result->fetchrow();
  $retour['monnaie'] = $row[0];
  $retour['monnaie_prec'] = $row[1];

  // Sommes-nous en mode unidevise ou multidevise
  $sql = "SELECT count(*) FROM devise WHERE id_ag =".$retour["id_ag"];
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

  //Recherche info login
  $sql = "SELECT guichet, id_utilisateur, profil, have_left_frame, billet_req, langue, date_mod_pwd, pwd_non_expire FROM ad_log WHERE login='".addslashes($retour['login'])."'";
  $result = $db->query($sql); //Cherche ds table des logins
  if (DB::isError($result)) {
    $dbHandler->closeConnection(false);
    signalErreur(__FILE__,__LINE__,__FUNCTION__);
  } else if ($result->numrows() <> 1) {
    $dbHandler->closeConnection(false);
    signalErreur(__FILE__,__LINE__,__FUNCTION__); // "Aucune ou plusieurs occurences du même login '".$retour['login']."'!"
  }
  $row = $result->fetchrow();
  if ($row[0] > 0) $retour['id_guichet'] = $row[0];
  else $retour['id_guichet'] = NULL;
  $retour['id_utilisateur'] = $row[1];
  $retour['id_profil'] = $row[2];
  $retour['have_left_frame'] = ($row[3] == 't');
  $has_billet = ($row[4] == 't');
  $retour['langue'] = $row[5];
  $retour ['date_mod_pwd']= $row[6];
  $retour['pwd_non_expire']=$row[7];

  //Recherche info timeout
  $sql = "SELECT timeout,conn_agc  FROM adsys_profils WHERE id=".$retour['id_profil'];
  $result = $db->query($sql);
  if (DB::isError($result)) {
    $dbHandler->closeConnection(false);
    signalErreur(__FILE__,__LINE__,__FUNCTION__);
  } else if ($result->numrows() <> 1) {
    $dbHandler->closeConnection(false);
    signalErreur(__FILE__,__LINE__,__FUNCTION__ .$sql); // "Aucune ou plusieurs occurences du même profil!"
  }
  $row = $result->fetchrow();
  $retour['timeout'] = $row[0];
  $retour['conn_agc'] = $row[1];

  //Recherche info droits d'accès
  $sql = "SELECT fonction FROM adsys_profils_axs WHERE profil=".$retour['id_profil'];
  $result = $db->query($sql);
  if (DB::isError($result)) {
    $dbHandler->closeConnection(false);
    signalErreur(__FILE__,__LINE__,__FUNCTION__);
  }
  $retour['profil_axs'] = array();
  while ($row = $result->fetchrow()) {
    array_push($retour['profil_axs'], $row[0]);
  }

  //Recherche info utilisateur
  $sql = "SELECT nom, prenom FROM ad_uti WHERE id_utilis='".$retour['id_utilisateur']."'";
  $result = $db->query($sql); //Cherche ds table des utilisateurs
  if (DB::isError($result)) {
    $dbHandler->closeConnection(false);
    signalErreur(__FILE__,__LINE__,__FUNCTION__);
  } else if ($result->numrows() <> 1) {
    $dbHandler->closeConnection(false);
    signalErreur(__FILE__,__LINE__,__FUNCTION__); // "Aucune ou plusieurs occurences du même utilisateur!"
  }
  $row = $result->fetchrow();
  $retour['nom_utilisateur'] = $row[1].' '.$row[0];

  //Recherche info guichet
  if ($retour['id_guichet'] != NULL) {//Si il existe un guichet pour ce login
    $sql = "SELECT libel_gui FROM ad_gui WHERE id_ag = ".$retour["id_ag"]." AND id_gui='".$retour['id_guichet']."'";
    $result = $db->query($sql); //Cherche ds table des guichets
    if (DB::isError($result)) {
      $dbHandler->closeConnection(false);
      signalErreur(__FILE__,__LINE__,__FUNCTION__);
    } else if ($result->numrows() <> 1) {
      $dbHandler->closeConnection(false);
      signalErreur(__FILE__,__LINE__,__FUNCTION__); // "Aucune ou plusieurs occurences du même guichet!"
    }
    $row = $result->fetchrow();
    $retour['libel_guichet'] = $row[0];
  }

  // Billetage requis ?
  if ($has_billet)   // Si ce login nécessite un billettage
    $retour['billet_req'] = true;
  else
    $retour['billet_req'] = false;

  // Vecteur de billets
  $sql = "SELECT id, valeur FROM adsys_types_billets WHERE id_ag = ".$retour["id_ag"];
  $result = $db->query($sql);
  if (DB::isError($result)) {
    $dbHandler->closeConnection(false);
    signalErreur(__FILE__,__LINE__,__FUNCTION__);
  }

  $retour['billets'] = array();
  while ($tmprow = $result->fetchrow()) {
    $retour['billets'][$tmprow[0]]['libel'] = $tmprow[1];
    $retour['billets'][$tmprow[0]]['valeur'] = $tmprow[1]; // FIXME : Pour compatbilité avec ancienens fonctions
  }

  $dbHandler->closeConnection(true);

  return $retour;
}

function get_gestionnaire($id) {
  global $dbHandler;
  $db = $dbHandler->openConnection();

  $sql = "SELECT nom,prenom FROM ad_uti WHERE id_utilis= $id";
  $result = $db->query($sql); //Va chercher dans la table des sessions
  if (DB::isError($result)) {
    $dbHandler->closeConnection(false);
    signalErreur(__FILE__,__LINE__,__FUNCTION__);
  }
  $row = $result->fetchrow();
  return  $row[0]." ".$row[1];
}

/**
 * 
 * Vérifie si on a un fichier adbankingXXX.ini  exist dans le repertoire  jasper_config
 * si il n'existe pas,il vas generer un fichier adbanking"numagence".ini
 * @param $global_id_agence
 * 
 * @param $DB_host = $ini_array["DB_host"]
 * @param $DB_port = $ini_array["DB_port"]
 * @param $DB_user = $ini_array["DB_user"];
 * @param $DB_pass = $ini_array["DB_pass"]
 * @param $DB_name = $ini_array["DB_name"];
 * 
 * DB_host = 192.168.17.33, DB_port = 5432, DB_user = adbanking, DB_pass = public ,DB_name = domoni
 */
function verifyJasperConfig($agc,$host,$port,$user,$pass,$name,$doc_prefix) {

	// Create ini file
	$output = "[DATABASE]\n; Le paramètre DB_socket n'est actuellement pas utilisé\n; Pour se connecter par socket UNIX (plus rapide) il suffit de ne pas déclarer les variables\nDB_host = ".trim($host)."\nDB_port = ".trim($port)."\nDB_user = ".trim($user)."\nDB_pass = ".trim($pass)."\nDB_name = ".trim($name)."\n";

	$ini_file_path = $doc_prefix."/jasper_config/adbanking".$agc.".ini";
	//var_dump($ini_file_path);

	// Create ini file
	@touch($ini_file_path);

	// Ecriture du fichier ini
	$handler = @fopen($ini_file_path,"wb+");
	fwrite($handler, $output);
	fclose($handler);

	// Change ini file permission to 0755
	@chmod($ini_file_path, 0755);
	
}
/**
 * Fonction renvoyant les parametres de connection q chque base qui existe dans adsys_multi_agence
 * 
 * @param 
 * 
 * @return array On renvoie un tableau de la forme (identifiant => info connection)
 */
function getParamConnectMA() {
	global $dbHandler,$global_id_agence;
	$db = $dbHandler->openConnection();

	$sql ="SELECT id_agc ,app_db_host ,app_db_port ,app_db_name ,app_db_username ,app_db_password FROM adsys_multi_agence ";
	$sql .="WHERE id_ag = $global_id_agence ;";
	
	$result = $db->query($sql);
	if (DB::isError($result)) {
		$dbHandler->closeConnection(false);
		signalErreur(__FILE__,__LINE__,__FUNCTION__);
	}

	$connections = array();
	while ($row= $result->fetchrow(DB_FETCHMODE_OBJECT)){
		//$row = $result->fetchrow(DB_FETCHMODE_ASSOC)
	    
		//decriptage du password
		$plaintext = trim($row->app_db_password);
		$password = trim($row->app_db_host).'_'.trim($row->app_db_name);
		$row->app_db_password = phpseclib_Decrypt($plaintext, $password);

		$connections[$row->id_agc] = $row;
	}

	$dbHandler->closeConnection(true);
	return $connections;

}









/**
 * Vérifie si la session est bien associée avec l'adresse IP utilisée
 * Cela est utile pour vérifier s'il n'y a pas usurpation d'identité
 * @param str $ip_adr Adresse ip de machine se connectant
 * @param str $id_sess Identifiant de session
 * @return bool false si la session et IP non valide, true si ok
 */
function check_session_login($adr_ip, $id_sess) {
  global $dbHandler;

  $db = $dbHandler->openConnection();
  $sql = "SELECT adr_ip FROM ad_ses WHERE id_sess = '$id_sess'";
  $result = $db->query($sql);
  if (DB::isError($result)) {
    $dbHandler->closeConnection(false);
    signalErreur(__FILE__,__LINE__,__FUNCTION__);
  }

  if ($result->numrows() > 1) {
    $dbHandler->closeConnection(false);
    signalErreur(__FILE__,__LINE__,__FUNCTION__, _("Plusieurs occurences de la même session !"));
  }

  if ($result->numrows() == 0) {
    $result = false;
  } else {
    $row = $result->fetchrow();
    $result = ($row[0] == $adr_ip);
  }
  $dbHandler->closeConnection(true);
  return ($result);
}

function get_encaisse($id_guichet, $devise = NULL) {
  global $dbHandler, $global_id_agence;
  global $global_multidevise;
  $db = $dbHandler->openConnection();

  $sql = "SELECT cpte_cpta_gui FROM ad_gui WHERE id_ag = ".$global_id_agence." AND id_gui='".$id_guichet."'";
  $result = $db->query($sql);
  if (DB::isError($result)) {
    $dbHandler->closeConnection(false);
    signalErreur(__FILE__,__LINE__,__FUNCTION__);
  }
  $row = $result->fetchrow();
  $id_cpt = $row[0];
  if ($global_multidevise)
    if ($devise)
      $id_cpt.=".". $devise;
  $sql = "SELECT solde FROM ad_cpt_comptable WHERE id_ag = ".$global_id_agence." AND num_cpte_comptable = '".$id_cpt."'";
  if ($global_multidevise)
   if ($devise)
      $id_cpt.=".".$devise;
  $result = $db->query($sql);
  if (DB::isError($result)) {
    $dbHandler->closeConnection(false);
    signalErreur(__FILE__,__LINE__,__FUNCTION__);
  }
  if ($result->numrows() > 1) {
    $dbHandler->closeConnection(false);
    signalErreur(__FILE__,__LINE__,__FUNCTION__); // "Le nombre de guichets est différent de 1 !"
  }
  $row = $result->fetchrow();
  $dbHandler->closeConnection(true);

  return (-1 * $row[0]); //compte débiteur => * -1
}

/**
 * Supprime une session de la BD et détruit les variables de cette session
 * @param str $a_login Le login dont il faut supprimer la session
 * @param bool $a_restart A vrai si on doit redémarrer une nouvelle session
 * @return bool true si pas d'erreur
 */
function delete_session($a_login, $a_restart = true) {
  global $dbHandler;
  $db = $dbHandler->openConnection();
  $login = addslashes($a_login);
  $sql = "DELETE FROM ad_ses WHERE login = '$login'";
  $result = $db->query($sql);
  if (DB::isError($result)) {
    $dbHandler->closeConnection(false);
    signalErreur(__FILE__,__LINE__,__FUNCTION__);
  }
  $dbHandler->closeConnection(true);
  session_destroy();

  if ($a_restart) {
    // On vient de détruire la session, il faut lui redonner son nom !
    session_name("ADbanking");
    session_start();
  }
  return true;
}

/**
 * Retourne l'état d'une session
 * @param int $id_sess Identifiant de la session
 * @return int Etat de la session (0 si inactive, 1 si active)
 */
function get_sess_status($id_sess) {
  global $dbHandler;
  $db = $dbHandler->openConnection();
  $sql = "SELECT sess_status FROM ad_ses WHERE id_sess = '$id_sess'";
  $result = $db->query($sql);
  if (DB::isError($result)) {
    $dbHandler->closeConnection(false);
    signalErreur(__FILE__,__LINE__,__FUNCTION__);
  } /*else if ($result->numrows() != 1) {
    $dbHandler->closeConnection(false);
    signalErreur(__FILE__,__LINE__,__FUNCTION__); // "Statut de la session introuvable ou multiple !"
  }*/
  $row = $result->fetchrow();
  $dbHandler->closeConnection(true);
  
  $return_value = $row[0];
  if($return_value == NULL) {
      $return_value = -1;
  }

  return $return_value;
}

/**
 * Place l'état de la session
 * @param str $id_sess Identifiant de la session
 * @param int $value Etat de la session (0 si inactive, 1 si active)
 * @return bool Vrai si pas d'erreur
 */
function set_sess_status($id_sess, $value) {
  global $dbHandler;
  $db = $dbHandler->openConnection();
  $sql = "UPDATE ad_ses SET sess_status = $value WHERE id_sess = '$id_sess'";
  $result = $db->query($sql);
  if (DB::isError($result)) {
    $dbHandler->closeConnection(false);
    signalErreur(__FILE__,__LINE__,__FUNCTION__);
  }

  $dbHandler->closeConnection(true);
  return true;
}

function get_menus_struct() {
  global $dbHandler;
  $db = $dbHandler->openConnection();
  $sql = "SELECT * FROM menus ORDER BY pos_hierarch ASC, ordre ASC";
  $result = $db->query($sql);
  if (DB::isError($result)) {
    $dbHandler->closeConnection(false);
    signalErreur(__FILE__,__LINE__,__FUNCTION__);
  }
  $retour = array();
  while ($row = $result->fetchrow(DB_FETCHMODE_ASSOC)) {
    $row['libel_menu'] = new Trad($row['libel_menu']);
    $retour[$row['nom_menu']] = $row;
  }

  $dbHandler->closeConnection(true);
  return $retour;
}

function get_ecrans_struct() {
  global $dbHandler;
  $db = $dbHandler->openConnection();
  $sql = "SELECT * FROM ecrans";
  $result = $db->query($sql);
  if (DB::isError($result)) {
    $dbHandler->closeConnection(false);
    signalErreur(__FILE__,__LINE__,__FUNCTION__);
  }
  $retour = array();
  while ($row = $result->fetchrow(DB_FETCHMODE_ASSOC)) {
    $retour[$row['nom_ecran']] = $row['nom_menu'];
  }
  $dbHandler->closeConnection(true);
  return $retour;
}

function getIDGuichetFromLogin($login)
// PS qui renvoie le guichet associé au login passé en paramètre
// IN : le login
// OUT: Numéro du guichet si présent
//      -1 si pas de guichet associé
{
  global $dbHandler;
  $db = $dbHandler->openConnection();
  $sql = "SELECT guichet FROM ad_log WHERE login='$login'";
  $result = $db->query($sql);
  if (DB::isError($result)) {
    $dbHandler->closeConnection(false);
    signalErreur(__FILE__,__LINE__,__FUNCTION__);
  }
  $tmprow = $result->fetchRow();
  $dbHandler->closeConnection(true);
  if ($tmprow[0] == '')
    return -1;
  else
    return $tmprow[0];
}

/**
 * Ouvre un guichet lors de la connexion de son utilisateur
 * @param int $id_gui L'identifiant du guichet
 * @return ErrorObj NO_ERR si pas de problème
 */
function ouvertureGuichet($id_gui) {
  global $dbHandler,$global_id_agence;
  $global_id_agence=getNumAgence();
  $db = $dbHandler->openConnection();
  $sql = "UPDATE ad_gui SET ouvert='t' WHERE id_ag = ".$global_id_agence." AND id_gui = ".$id_gui;
  $result = $db->query($sql);
  if (DB::isError($result)) {
    $dbHandler->closeConnection(false);
    signalErreur(__FILE__,__LINE__,__FUNCTION__);
  }
  $dbHandler->closeConnection(true);
  return new ErrorObj(NO_ERR);
}

/**
 * Ferme un guichet lors de la déconnexion de son utilisateur
 * @param int $id_gui L'identifiant du guichet
 * @return ErrorObj NO_ERR si pas de problème
 */
function fermetureGuichet($id_gui) {
  global $dbHandler;
  $db = $dbHandler->openConnection();
  $sql = "UPDATE ad_gui SET ouvert = 'f' WHERE id_gui = $id_gui";
  $result = $db->query($sql);
  if (DB::isError($result)) {
    $dbHandler->closeConnection(false);
    signalErreur(__FILE__,__LINE__,__FUNCTION__);
  }
  $dbHandler->closeConnection(true);
  return new ErrorObj(NO_ERR);
}

/**
 * Vérifie si les chemins des fichiers sont corrects et avec les bonnes permissions.
 * @return bool True si ok, false sinon.
 */
function checkPaths() {
  global $lib_path;
  global $log_path;
  global $doc_prefix;

  // Vérification du répertoire lib_path
  $paths = array($lib_path, $log_path);
  foreach ($paths as $path) {
    if (!file_exists($path)) {
      return new ErrorObj(ERR_PATH, sprintf(_("%s n'existe pas"), $path));
    }
    if (!is_readable($path)) {
      return new ErrorObj(ERR_PATH, sprintf(_("%s n'est pas accessible en lecture par le serveur web"), $path));
    }
    if (!is_writable($path)) {
      return new ErrorObj(ERR_PATH, sprintf(_("%s n'est pas accessible en écriture par le serveur web"), $path));
    }
  }

  // Création des sous-répertoire de lib_path
  $paths = array();
  array_push($paths, $lib_path."/backup");
  array_push($paths, $lib_path."/backup/batch");
  array_push($paths, $lib_path."/backup/batch/rapports");
  array_push($paths, $lib_path."/backup/images");
  array_push($paths, $lib_path."/backup/images_agence");
  array_push($paths, $lib_path."/backup/images_clients");
  array_push($paths, $lib_path."/backup/images_clients/clients");
  array_push($paths, $lib_path."/backup/images_clients/clients/photos");
  array_push($paths, $lib_path."/backup/images_clients/clients/photos/1");
  array_push($paths, $lib_path."/backup/images_clients/clients/photos/2");
  array_push($paths, $lib_path."/backup/images_clients/clients/photos/3");
  array_push($paths, $lib_path."/backup/images_clients/clients/photos/4");
  array_push($paths, $lib_path."/backup/images_clients/clients/photos/5");
  array_push($paths, $lib_path."/backup/images_clients/clients/photos/6");
  array_push($paths, $lib_path."/backup/images_clients/clients/photos/7");
  array_push($paths, $lib_path."/backup/images_clients/clients/photos/8");
  array_push($paths, $lib_path."/backup/images_clients/clients/photos/9");
  array_push($paths, $lib_path."/backup/images_clients/clients/signatures");
  array_push($paths, $lib_path."/backup/images_clients/clients/signatures/1");
  array_push($paths, $lib_path."/backup/images_clients/clients/signatures/2");
  array_push($paths, $lib_path."/backup/images_clients/clients/signatures/3");
  array_push($paths, $lib_path."/backup/images_clients/clients/signatures/4");
  array_push($paths, $lib_path."/backup/images_clients/clients/signatures/5");
  array_push($paths, $lib_path."/backup/images_clients/clients/signatures/6");
  array_push($paths, $lib_path."/backup/images_clients/clients/signatures/7");
  array_push($paths, $lib_path."/backup/images_clients/clients/signatures/8");
  array_push($paths, $lib_path."/backup/images_clients/clients/signatures/9");
  array_push($paths, $lib_path."/backup/images_clients/perso_ext");
  array_push($paths, $lib_path."/backup/images_clients/perso_ext/photos");
  array_push($paths, $lib_path."/backup/images_clients/perso_ext/photos/1");
  array_push($paths, $lib_path."/backup/images_clients/perso_ext/photos/2");
  array_push($paths, $lib_path."/backup/images_clients/perso_ext/photos/3");
  array_push($paths, $lib_path."/backup/images_clients/perso_ext/photos/4");
  array_push($paths, $lib_path."/backup/images_clients/perso_ext/photos/5");
  array_push($paths, $lib_path."/backup/images_clients/perso_ext/photos/6");
  array_push($paths, $lib_path."/backup/images_clients/perso_ext/photos/7");
  array_push($paths, $lib_path."/backup/images_clients/perso_ext/photos/8");
  array_push($paths, $lib_path."/backup/images_clients/perso_ext/photos/9");
  array_push($paths, $lib_path."/backup/images_clients/perso_ext/signatures");
  array_push($paths, $lib_path."/backup/images_clients/perso_ext/signatures/1");
  array_push($paths, $lib_path."/backup/images_clients/perso_ext/signatures/2");
  array_push($paths, $lib_path."/backup/images_clients/perso_ext/signatures/3");
  array_push($paths, $lib_path."/backup/images_clients/perso_ext/signatures/4");
  array_push($paths, $lib_path."/backup/images_clients/perso_ext/signatures/5");
  array_push($paths, $lib_path."/backup/images_clients/perso_ext/signatures/6");
  array_push($paths, $lib_path."/backup/images_clients/perso_ext/signatures/7");
  array_push($paths, $lib_path."/backup/images_clients/perso_ext/signatures/8");
  array_push($paths, $lib_path."/backup/images_clients/perso_ext/signatures/9");
  array_push($paths, $lib_path."/backup/images_tmp");
  array_push($paths, $lib_path."/backup/licence");
  array_push($paths, $lib_path."/ferlo");
  array_push($paths, $lib_path."/ferlo/autorisation");
  array_push($paths, $lib_path."/ferlo/recharge");
  array_push($paths, $lib_path."/ferlo/transaction");

  foreach ($paths AS $path) {
    if (!file_exists($path)) {
      if (!mkdir($path, 0755)) {
        return new ErrorObj(ERR_LIB_PATH, sprintf(_("Création du répertoire %s impossible"), $path));
      }
    }
  }

  return new ErrorObj(NO_ERR);
}

?>