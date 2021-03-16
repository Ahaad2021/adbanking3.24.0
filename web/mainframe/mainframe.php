<?php
/* vim: set expandtab softtabstop=2 shiftwidth=2 foldmethod=marker: */

/**
 * Fichier PHP interprété à chaque chargement d'une page ADbanking excepté le login.
 *
 * Définit le contenu du frame principal, l'écran à afficher est stocké dans la variable : $prochain_ecran
 * @package Ifutilisateur
 */

// error_reporting(E_ALL);
// ini_set("display_errors", "on");

//echo "<pre>";
//print_r($_REQUEST);
//echo "</pre>";
//exit;

require_once 'lib/dbProcedures/main_func.php';
require_once 'lib/misc/VariablesGlobales.php';
require_once 'lib/misc/Erreur.php';
require_once 'lib/dbProcedures/login_func.php';
require_once 'lib/dbProcedures/historique.php';
require_once 'lib/dbProcedures/agence.php';
require_once 'lib/html/HTML_GEN2.php';
require_once 'lib/html/HTML_message.php';
require_once 'lib/html/HTML_erreur.php';
require_once 'lib/misc/divers.php';
require_once 'lib/misc/access.php';
require_once 'lib/misc/miseenforme.php';
require_once 'lib/dbProcedures/assert.php';
require_once 'lib/misc/VariablesSession.php';

$appli = "main"; //On est dans l'application (et pas dans le batch)

$db = $dbHandler->openConnection();

// A ce stade, il FAUT que la variable $prochain_ecran ait soit été postée à partir de l'écran précédent, soit initialisée par main.php.
// Si ce n'est pas le cas, on est dans une situation anormale, on termine l'application.
if (!isset($prochain_ecran))
  signalErreur(__FILE__,__LINE__,__FUNCTION__, _("Le prochain écran n'est pas défini !"));
$global_nom_ecran_prec = $global_nom_ecran; // On sauvegarde le nom de l'écran d'où on vient
$global_nom_ecran = $prochain_ecran; // Les modules doivent connaître l'écran actuel => on sauve dans global_nom_ecran

// On vérifie si le n° de session correspond bien à l'adresse IP
if (! check_session_login($REMOTE_ADDR, session_id())) {
  require("perte_session.php");
  die();
}

// On vérifie si le timeout n'est pas dépassé
// Et si les variables de session existent toujours sur le serveur
// (cela depend de session.gc_maxlifetime dans php.ini)
if ($SESSION_VARS['timeout'] != true && ($global_last_axs == NULL || ($global_timeout != 0 && ($global_last_axs < (time() - ($global_timeout*60)))))) {
  debug (time()-$global_last_axs, _("Dernier axs il y a (en secondes)")." ");
  
  if($global_last_axs == NULL) {
    debug (123, _("Dernier axs il y a (en secondes)")." global_last_axs=@".$global_last_axs."@ global_timeout=@".$global_timeout."@");
  }

  $global_nom_ecran = "Tot-1";
  $result = get_screen($global_nom_ecran);
  $fichier = $result['fichier'];
  $fonction = $result['fonction'];
} else {
  // A présent on va rechercher le fichier php qui contient l'écran que l'on doit afficher et la fonction à laquelle il est associé :
  $result = get_screen($global_nom_ecran);
  $fichier = $result['fichier'];
  $fonction = $result['fonction'];

  // On vérifie si l'utilisateur a le droit de visualiser cette page
  if (! check_access($fonction)) {
    debug(sprintf(_("Violation des droits d'accès à l''écran '%s'; votre login est associé au profil n° %s"),$global_nom_ecran,$global_id_profil)." ".sprintf(_("L'autorisation a la fonction n°%s nécessaire."),$fonction ));
    signalErreur(__FILE__,__LINE__,__FUNCTION__); //
  }

  // On vérifie que l'agence soit dans un état acceptable pour les requêtes
  if ($global_agence != NULL) {
    $statut_ag = get_statut_agence($global_id_agence);
    if ($statut_ag == 3) { //Si l'agence est en cours de traitements batch
      //FIXME : permettre au moins à u profil adm de se connecter
      signalErreur(__FILE__,__LINE__,__FUNCTION__); // "L'agence est en cours de traitements batch : aucune connexion acceptée !"
    } else if (($statut_ag == 2) && ($global_conn_agc == 'f')) {
      //Si l'agence est fermée et le login possède un guichet
      signalErreur(__FILE__,__LINE__,__FUNCTION__); // "L'agence est fermée : aucun login avec guichet associé n'est accepté !"
    }
  }

  /*
    FIXME
    Il faut vérifier que le champs hidden 'java_enabled' soit mis à '1'.
    Sinon ça veut dire que javascript n'est pas actif sur le poste client.
    Cela est déjà implémenté dans HTML_GEN2 mais pas dans HTML_message et HTML_erreur; ni dans toutes les pages générées manuellement.
    Pour les pages générées manuellement il faut également implémenter la solution pour éviter le double POST (isSubmit, cf. HTML_GEN2).
  */
}

//On met à jour l'heure de son dernier accès
update_last_axs(date("H:i:s d F Y"), $global_nom_login);
$global_last_axs = time();

//On inscrit le log système
ajout_log_systeme(date("H:i:s d F Y"), _('Affichage fichier').' "'.$fichier.'" '._('ecran').' "'.$global_nom_ecran.'"', $global_nom_login, $REMOTE_ADDR);

// Mise en forme des champs postés dans les formulaires HTML_GEN
mise_en_forme_MONTANT_LIE($_POST);
mise_en_forme_HTML_GEN2($_POST);
foreach($_POST as $key => $val)
$ {"$key"} = $val;

require("lib/html/HtmlHeader.php");
//Affichage du nom de l'écran si DEBUG activé
if ($DEBUG) {
  $variablesTransmises = getVariablesTransmises();
  echo sprintf(_("Ecran %s du fichier %s"),"<b>$global_nom_ecran</b>","<b>$fichier</b>")." $variablesTransmises<hr />";
  echo "<div id=\"cacheS\"><pre>";
  print_r($_SESSION['SESSION_VARS']);
  echo "</pre></div>";
  echo "<div id=\"cacheP\"><pre>";
  print_r($_POST);
  echo "</pre></div>";
  echo "<div id=\"cacheG\"><pre>";
  print_r($_GET);
  echo "</pre></div>";
  if ($global_agence != NULL && $SESSION_VARS['timeout'] != true ) {
    check_assert(); // Vérifie les assertions, uniquement si la session existe encore
  }
}

$dbHandler->closeConnection(true);

/** Multi Agence */
function getMenuGuichetScreenCodes() {
  global $dbHandler;

  $db = $dbHandler->openConnection();

  $sql ="SELECT nom_menu FROM menus WHERE nom_pere LIKE 'Gen-6';";

  $result = $db->query($sql);

  $screen_arr = array();
  if (!DB::isError($result)) {
    while ($tmprow = $result->fetchrow()) {
      $screen_arr[] = $tmprow[0];
    }
  }

  $dbHandler->closeConnection(TRUE);

  return $screen_arr;
}

$screen_arr = (array)getMenuGuichetScreenCodes();

if(in_array(substr($global_nom_ecran, 0, 3), $screen_arr))
{
    if(!function_exists("resetVariablesGlobalesClient"))
    {
        function resetAllVariablesGlobalesClient() {
            global $global_client, $global_id_client, $global_client_debiteur, $global_id_client_formate, $global_alerte_DAT, $global_credit_niveau_retard, $global_suspension_pen, $global_cli_epar_obli, $global_photo_client, $global_signature_client;
            global $global_remote_agence, $global_remote_client, $global_remote_id_client, $global_remote_id_client_formate, $global_remote_photo_client, $global_remote_signature_client, $global_remote_db_host, $global_remote_db_port, $global_remote_db_name, $global_remote_db_username, $global_remote_db_password, $global_remote_db_driver, $global_remote_agence_obj, $global_remote_id_exo;

            // Clear local info
            $global_client = "";
            $global_id_client = "";
            $global_client_debiteur = "";
            $global_id_client_formate = "";
            $global_alerte_DAT = "";
            $global_suspension_pen = false;
            $global_cli_epar_obli = "";
            $global_credit_niveau_retard = array();
            $global_signature_client = NULL;
            $global_photo_client = NULL;

            // Clear remote info
            $global_remote_agence = "";
            $global_remote_client = "";
            $global_remote_id_client = "";
            $global_remote_id_client_formate = "";
            $global_remote_photo_client = NULL;
            $global_remote_signature_client = NULL;
            $global_remote_agence_obj = NULL;
            $global_remote_db_driver = "";
            $global_remote_db_host = "";
            $global_remote_db_port = "";
            $global_remote_db_name = "";
            $global_remote_db_username = "";
            $global_remote_db_password = "";
            $global_remote_id_exo = "";
        }
    }
    
    // Clear local & remote client info
    resetAllVariablesGlobalesClient();
}
/** Multi Agence */

//Lorsqu'on a le nom du fichier on va l'utiliser pour construire la page HTML :
require("$fichier");

//Recharge les autres frames
echo "<script type=\"text/javascript\">";
if ($global_nom_ecran == "Tot-1" ||  //time out
$global_modif_pwd_login ) { //modifcation du pwd lors de la connexion
  // Lors d'un timeout, il faut effacer le menu de navigation
  echo "window.parent.status_frame.location.href = \"$SERVER_NAME/login/top_login.php\";";
  echo "window.parent.menu_frame.location.href = \"$SERVER_NAME/login/left_login.php\";";
  echo "window.parent.extra_frame.location.href = \"$SERVER_NAME/login/left_login.php\";";
} else {
  echo "window.parent.status_frame.location.href = \"$SERVER_NAME/status_gen/status_gen.php?m_agc=".$_REQUEST['m_agc']."\";";
  if ($global_have_left_frame) {
    echo "window.parent.menu_frame.location.href = \"$SERVER_NAME/menu_gen/menu_gen.php?m_agc=".$_REQUEST['m_agc']."\";";
  }
}

echo "window.focus();
function show_it(elementId) {
element = document.getElementById(elementId);
element.style.visibility = \"visible\";
}
function hide_it(elementId) {
element = document.getElementById(elementId);
element.style.visibility = \"hidden\";
}
";
echo "</script>";
require("lib/html/HtmlFooter.php");

?>
