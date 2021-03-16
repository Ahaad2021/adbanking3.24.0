<?php
/* vim: set expandtab softtabstop=2 shiftwidth=2 foldmethod=marker: */

/**
 * Définition des variables globales (elles ne sont pas ici enregistrées dans la session)
 * @package Systeme
 */

require_once "lib/dbProcedures/handleDB.php";
require_once "debug.php";
require_once "decryptMessageQueue.php";
require_once "cryptage.php";
require_once "password_encrypt_decrypt.php";

//Définition des constantes
define("SENS_CREDIT", "c");
define("SENS_DEBIT", "d");
//Nom du projet
$ProjectName = "ADbanking v3.24"; //Nom "visuel" du projet. Le nom interne est "ADbanking".
$start_screen = 'Gen-3'; //Nom du premier ecran
$window_name = 'ADbanking'; //Nom (javascript) de la fenêtre principale

//Construction de la variable http_prefix (par exemple: '' ou '/~adbanking')
preg_match("/(^\/~.*?)\//", $_SERVER["PHP_SELF"], $http_prefix);
$http_prefix = $http_prefix[1]."/adbanking";

//Construction de la variable doc_prefix (par exemple: '/var/www/html/adbanking' ou '/home/adbanking/public_html')
preg_match("/(.*)\/lib\/misc\/VariablesGlobales\.php/",__FILE__,$doc_prefix);
$doc_prefix = $doc_prefix[1];

//Construction de la variable SERVER_NAME
$SERVER_NAME="${SERVER_NAME}:$SERVER_PORT$http_prefix";

//Vérification de l'existence du fichier adbanking.ini
if (!file_exists("$doc_prefix/adbanking.ini")) {
  die("Le fichier adbanking.ini n'existe pas : $doc_prefix/adbanking.ini");
}

//Récupération des informations du fichier adbanking.ini
$ini_array = parse_ini_file("$doc_prefix/adbanking.ini");

if((isset($_REQUEST['m_agc']) && $_REQUEST['m_agc'] > 0))
{
    $m_agc = $_REQUEST['m_agc'];
    
    $ini_file_path = sprintf('%s/jasper_config/adbanking%s.ini', $doc_prefix, $m_agc);

    //Vérification de l'existence du fichier adbanking$m_agc.ini
    if (file_exists($ini_file_path)) {
        //Récupération des informations du fichier adbanking$m_agc.ini
        $new_ini_array = parse_ini_file($ini_file_path);

        $ini_array = array_merge($ini_array, $new_ini_array);
	}else{
        $ini_files = scandir($doc_prefix.'/jasper_config/', 1);

        if(is_array($ini_files) && count($ini_files)>3) {            
            die("Le fichier adbanking$m_agc.ini n'existe pas : ".$ini_file_path);
        }
    }
}

//Paramétrage PATH
$lib_path = $ini_array["lib_path"];
$log_path = $ini_array["log_path"];

//Paramétrage XSLT
$fop_path = $ini_array["fop_path"]; //Répertoire du processeur XSL-FO
$xslfo_output = $ini_array["xslfo_output"]; //Préfixe du fichier destination du XSL-FO
$xml_output = $ini_array["xml_output"]; //Préfixe du fichier XML généré par ADBanking
$pdf_output = $ini_array["pdf_output"]; //Préfixe du fichier destination du PDF
$csv_output = $ini_array["csv_output"]; // Préfixe du fichier CSV généré
$MAE_path = $ini_array["MAE_path"];
$report_backup_path = null;

//Paramétrage JAVA
$java_home = $ini_array["java_home"]; // Répertoire contenant la machine virtuelle JAVA
$java_memory = $ini_array["java_memory"]; // La mémoire à allouer à la machine virtuelle JAVA

//Paramétrage WEBSERVER
$protocol = $ini_array["protocol"];

//Paramétrage OPTIMISATION
$disable_backup = $ini_array["disable_backup"];
$disable_vacuum = $ini_array["disable_vacuum"];

//Paramétrage DEBUG
$DEBUG = $ini_array["debug"];

//Paramétrage double affiliation
$double_affiliation = $ini_array["double_affiliation"];

//Construction de la variable SERVER_NAME avec le protocol
$SERVER_NAME = $protocol."://".$SERVER_NAME;

/**
 * Connexion à la base de donnée
 * @var handleDB $dbHandler l'objet de connexion à la base de données
 */
$dbHandler = new handleDB();
$DB_name = $ini_array["DB_name"];
$DB_user = $ini_array["DB_user"];
//$DB_pass = $ini_array["DB_pass"];
$DB_cluster = $ini_array["DB_cluster"];
/**
 * AT-31 : d'en servir la version decrypter du mot de passe encrpter de l'utilisateur
 * pour que l'application puisse se connecter avec la base de données
 */
/*************************************************************************/
$password_converter = new Encryption;
$decoded_password = $password_converter->decode($ini_array["DB_pass"]);
$DB_pass = $decoded_password;
/************************************************************************/
if ($ini_array["DB_host"] != '') {
  // Connexion par socket TCP
  $DB_dsn = sprintf("pgsql://%s:%s@%s:%s/%s", $ini_array["DB_user"], $decoded_password, $ini_array["DB_host"], $ini_array["DB_port"], $DB_name);//$ini_array["DB_pass"]
} else {
  // Connexion par socket UNIX
  $DB_dsn = sprintf("pgsql://%s:%s@/%s", $ini_array["DB_user"], $decoded_password, $DB_name);//print_rn($DB_dsn);
  // FIXME le DSN "unix()" n'est actuellement pas correctement reconnu par PEAR:DB, il faut donc utiliser la syntaxe ci-avant pour se connecter par le socket.
  // voir http://pear.php.net/bugs/bug.php?id=339&edit=1
  //$DB_dsn = sprintf("pgsql://%s:%s@unix(%s:%s)/%s", $ini_array["DB_user"], $ini_array["DB_pass"], $ini_array["DB_socket"], $ini_array["DB_port"], $DB_name);
}

//Paramétrage MSQ
if (!empty($ini_array["code_imf"])) $code_imf = trim($ini_array["code_imf"]);

if (!empty($ini_array["MSQ_ENABLED"])) $MSQ_ENABLED = trim($ini_array["MSQ_ENABLED"]);

if (!empty($ini_array["MSQ_HOST"])) $MSQ_HOST = decrypt_credentials(trim($ini_array["MSQ_HOST"]));

if (!empty($ini_array["MSQ_PORT"])) $MSQ_PORT = trim($ini_array["MSQ_PORT"]);

if (!empty($ini_array["MSQ_USERNAME"])) $MSQ_USERNAME = decrypt_credentials(trim($ini_array["MSQ_USERNAME"]));

if (!empty($ini_array["MSQ_PASSWORD"])) $MSQ_PASSWORD = decrypt_credentials(trim($ini_array["MSQ_PASSWORD"]));

if (!empty($ini_array["MSQ_VHOST"])) $MSQ_VHOST = trim($ini_array["MSQ_VHOST"]);

if (!empty($ini_array["MSQ_EXCHANGE_NAME"])) $MSQ_EXCHANGE_NAME = trim($ini_array["MSQ_EXCHANGE_NAME"]);

if (!empty($ini_array["MSQ_EXCHANGE_TYPE"])) $MSQ_EXCHANGE_TYPE = trim($ini_array["MSQ_EXCHANGE_TYPE"]);

if (!empty($ini_array["MSQ_QUEUE_NAME_MOUVEMENT"])) $MSQ_QUEUE_NAME_MOUVEMENT = trim($ini_array["MSQ_QUEUE_NAME_MOUVEMENT"]);

if (!empty($ini_array["MSQ_ROUTING_KEY_MOUVEMENT"])) $MSQ_ROUTING_KEY_MOUVEMENT = trim($ini_array["MSQ_ROUTING_KEY_MOUVEMENT"]);

if (!empty($ini_array["MSQ_QUEUE_NAME_MOB_LENDING"])) $MSQ_QUEUE_NAME_MOB_LENDING = trim($ini_array["MSQ_QUEUE_NAME_MOB_LENDING"]);

if (!empty($ini_array["MSQ_ROUTING_KEY_MOB_LENDING"])) $MSQ_ROUTING_KEY_MOB_LENDING = trim($ini_array["MSQ_ROUTING_KEY_MOB_LENDING"]);


/**
 * Couleurs codées en RGB : "#RRGGBB"
 * RR: quantité de rouge en hexadécimal (de 00 à FF => 256 valeurs possibles)
 * GG: quantité de rouge en hexadécimal (de 00 à FF => 256 valeurs possibles)
 * BB: quantité de rouge en hexadécimal (de 00 à FF => 256 valeurs possibles)
*/

//Définition de couleurs
$color_def['bleu marine'] = '#000099';
$color_def['noir'] = '#000000';
$color_def['blanc'] = '#FFFFFF';
$color_def['cyan sombre'] = '#007777';
$color_def['orange aquadev'] = '#FFD116';
$color_def['orange clair aquadev'] = '#FDF2A6';
$color_def['rouge'] = '#FF0000';
$color_def['rouge bordeau'] = '#660033';
$color_def['gris clair'] = '#DDDDDD';

//Couleurs utilisées
$colt_statut = $color_def['cyan sombre']; //Couleur du texte du frame supérieur
$colt_statut_dyn = $color_def['rouge bordeau']; //Couleur du texte dynamique du frame supérieur
$colb_statut = $color_def['blanc']; //Couleur du background du frame supérieur
$colt_main = $color_def['cyan sombre']; //Couleur du texte du frame principal
$colb_main = $color_def['blanc']; //Couleur du background du frame principal
$colt_tableau = $color_def['cyan sombre']; //Couleur texte au sein du tableau du frame principal
$colb_tableau = $color_def['orange clair aquadev']; //Couleur background au sein du tableau du frame principal
$colb_tableau_altern = $color_def['gris clair']; //Couleur background au sein du tableau du frame principal lorsqu'il s'agit d'alterner
$colt_menu = $color_def['blanc']; //Couleur texte du menu (frame gauche)
$colst_menu = $color_def['orange aquadev']; //Couleur texte du menu sélectionné (frame gauche)
$colb_menu = $color_def['cyan sombre']; //Couleur background du menu (frame gauche)
$col_bord = $color_def['noir']; //Couleur du séparateur de frame horizontal
$colt_error = $color_def['rouge']; //Couleur en cas d'erreur (p.ex. login incorrect)
$colt_login_gauche = $color_def['blanc']; //Couleur du frame gauche lors du login
$colb_login_gauche = $color_def['cyan sombre']; //Couleur de fond du frame gauche lors du login
$colb_login_droite_tableau = $color_def['orange clair aquadev']; //Couleur de fond du tableau contenant le login à droite
$colt_login_droite = $color_def['rouge bordeau']; //Couleur du texte du frame droit lors du login
$colt_champ_oblig = $color_def['rouge']; //Couleur du texte pour le symbole signalant les champs obligatoires
$colb_ferie = $color_def['gris clair']; //Couleur de fond pour jour ferie

//Présentation des frames :
$screen_frameborder = "'no'";
$screen_border = "'0'"; //Pour netscape
$screen_framespacing = "'0'"; //Pour IE
$screen_margin_height = "'0'";
$screen_margin_width = "'0'";

//Code HTML pour les champs obligatoires
$HTML_champ_oblig = "<FONT color=$colt_champ_oblig face=\"HELVETICA\" size=4><b>*</b></FONT>";

//Séparateurs pour les montants
$mnt_sep_mil = " "; //Séparateur pour les milliers
$mnt_sep_mil_csv = ""; //Séparateur pour les milliers dans export csv
$mnt_sep_dec = ","; //Séparateur pour la partie décimale
$mnt_sep_dec_csv = "."; //Séparateur pour la partie décimale dans export csv

//Nombre maximum de tentatives pour la saisie de l'encaisse
$nbre_max_tentative_encaisse = 3;

//Aspect des tableaux définis "manuellement" (sans HTML_GEN2)
$tableau_border = 1;
$tableau_cellspacing = 2;
$tableau_cellpadding = 3;

//Paramétrage calendrier
$calend_annee_passe = 1900;
$calend_annee_futur = 2050;

//Nombre de chiffres après la virgule (pour un nombre > 1) ou après le dernier 0 (pour un nombre < 1) pour l'affichage d'un taux.
$precision_taux = 3;

require_once 'lib/multilingue/traductions.php'; // La classe doit être déclarée avant l'ouverture de la session
require_once 'lib/misc/divers.php';

/* Vérifié l'accès */
checkADBankingAccess();

?>
