<?php
require_once 'lib/misc/VariablesGlobales.php';
require_once 'lib/misc/VariablesSession.php';
require_once 'DB.php';
require_once 'lib/dbProcedures/agence.php';
require_once 'lib/dbProcedures/handleDB.php';
require_once 'batch/batch_declarations.php';

// intégration, dans la db du siège, des données des agences choisies
global $dbHandler, $ini_array, $DB_pass, $global_id_agence;
$level = 1;
$DB_user = $ini_array['DB_user'];
$DB_pass = $ini_array['DB_pass'];
$DB_host = $ini_array['DB_host'];
if ($DB_host == '') {
  $DB_host = 'localhost';
}
$DB_name = $ini_array['DB_name'];
$liste_ag = array (); // liste des agences à consolider
unset ($SESSION_VARS['agence_consolidees']);
unset ($SESSION_VARS['erreur']);
if (isset ($global_id_agence) and $global_id_agence > 0) { // si le script est lancé via l'interface ADbanking pour une agence
  $liste_ag[$global_id_agence] = $global_id_agence; // si une agence est sélectionnée
} else { // si une agence n'est pas spécifiée
  //recupération de la version de la bases de dponnées
  $db = $dbHandler->openConnection();
  $sql = "SELECT version ";
  $sql .= "FROM adsys_version_schema;";
  $result = $db->query($sql);
  if (DB :: isError($result)) {
  	$dbHandler->closeConnection(false);
    signalErreur(__FILE__, __LINE__, __FUNCTION__);
  }
  $row = $result->fetchrow();
  $version_db = $row[0];
  $dbHandler->closeConnection(true);
}
// fermeture de la session pour pouvoir utiliser flush() et envoyer les données HTTP en continu.
session_write_close();
passthru("mkdir $lib_path/backup/images/images_sauvegardees");
$agence_traite = array ();

echo "<div class='batch'>\n";

if ($handle = opendir("$lib_path/backup/images/images_consolidation")) {
  while (false != ($file = readdir($handle))) {
    if ((substr($file, 0, 15) == 'db-conso-agence') && (substr($version_db, 0, 3) == substr($file, -12, 3))) {
      $liste_ag = explode("-", $file);
      $value = substr($liste_ag[2], 6);
      // suppression des anciennes valeurs de l'agence
      affiche(_("Suppression des anciennes données de l'agence ") . $value . " !");
      incLevel();
      $db = $dbHandler->openConnection();
      $sql = "SELECT consolidation_db(" . $value . ");";
      $result = $db->query($sql);
      if (DB :: isError($result)) {
      	$dbHandler->closeConnection(false);
        signalErreur(__FILE__, __LINE__, __FUNCTION__);
      }
      decLevel();
      affiche(sprintf(_("Les anciennes données de l'agence %s ont été supprimées !"),$value));
      // on ferme la connexion car la commande psql suivante ouvre une autre différente (si on pouvait récupérer
      //  les paramètres de la connexion ci-dessus, on aurait gardé la connexion persistante et avoir une seule transaction
      // mais on n'utilise pas un port fixe. Le 'show port;' ne donne rien.
      $dbHandler->closeConnection(true);
      // ajout des nouvelles valeurs de l'agence
      affiche(sprintf(_("Ajout des nouvelles données de l'agence %s !"),$value));
      $fichier = "$lib_path/backup/images/images_consolidation/db-conso-agence" . $value . "-v" . $version_db . ".sql.gz";
      $cmd = "PGPASSWORD=$DB_pass gunzip -c $fichier | psql -U $DB_user -d $DB_name -h $DB_host > /dev/null ";
      $retour = passthru($cmd, $code_retour);
      // vérifier que le scrip s'est bien exécuté
      decLevel();
      if ($code_retour == 0) {
        affiche(sprintf(_("Les nouvelles données de l'agence %s ont été ajoutées !"),$value));
        //Déplacement des fichiers fusionnés dans un autre repertoire
        $cmd_deplacer = "mv $fichier $lib_path/backup/images/images_sauvegardees";
        $retour_deplaces = passthru($cmd_deplacer, $code_retour1);
        if ($code_retour1 == 0) {
          affiche("Le fichier " . $fichier . " a été déplacé vers $lib_path/backup/images/images_sauvegardees !");
        }else{
        	affiche("Erreur lors du déplacement du fichier " . $fichier . " vers $lib_path/backup/images/images_sauvegardees !");
        }
        $data_agence = getAgenceDatas($value);
        $agence_traite[$value]['id'] = $value;
        $agence_traite[$value]['nom'] = $data_agence['libel_ag'];
      } else
        affiche(sprintf(_("Erreur : les nouvelles données de l'agence %s n'ont pas été ajoutées !"),$value));
    } //fin recherche des fichiers dumps conso
    elseif($file != "." && $file != ".."){
    	$SESSION_VARS['erreur'] = $error[ERR_NOM_BASE];
    }
  } //fin parcours du repertoire images_consolidation
} //fin test de l'ouverture repertoire images_consolidation
//Initialisation du tableau contenant les agences consolidées
$SESSION_VARS['agence_consolidees'] = $agence_traite;
// mise à jour des mouvments déjà consolidés
affiche(_("Mise à jour des mouvements déjà consolidés !"));
incLevel();
$db = $dbHandler->openConnection();
$sql = "UPDATE ad_mouvement SET consolide = 't' WHERE (id_ag,id_mouvement) IN (SELECT id_ag,id_mouvement FROM ad_mouvement_consolide)";
$result = $db->query($sql);
if (DB :: isError($result)) {
  signalErreur(__FILE__, __LINE__, __FUNCTION__);
}
// si au moins un mouvement est consolidé
$nb = $db->affectedRows();
decLevel();
affiche(sprintf(_("%s mouvements déjà consolidés ont été mis à jour !"),$nb));
$dbHandler->closeConnection(true);
affiche(_("OK"), true);

echo "</div>";

?>