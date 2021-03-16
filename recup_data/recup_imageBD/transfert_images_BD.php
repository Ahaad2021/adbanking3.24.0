<?php
require_once ("lib/misc/divers.php");
require_once ("lib/html/HTML_GEN2.php");
require_once ("lib/html/FILL_HTML_GEN2.php");
require_once ("lib/html/HTML_menu_gen.php");
require_once ("lib/html/HTML_message.php");
require_once ("lib/misc/VariablesGlobales.php");
require_once ("lib/misc/VariablesSession.php");
require_once ("DB.php");
require_once ("lib/misc/Erreur.php");
require_once ("lib/misc/tableSys.php");
require_once ("lib/dbProcedures/client.php");
require_once ("lib/dbProcedures/handleDB.php");
require_once ("lib/html/html_table_gen.php");
require_once ("lib/dbProcedures/bdlib.php");
require_once ("lib/dbProcedures/agence.php");

/**
 * Recupération des images(Photos et Spécimen Signature) de la base vers le File System
 * @package Recupdata
 * @since 2.8
 * @author Stefano AMEKOUDI
**/

global $global_id_agence;
$global_id_agence=getNumAgence();

/* On a besoin du maximum de temps d'exécution ! */
ini_set('max_execution_time', '300');

/**
 * Extrait la photo et le spécimen de signature d'un client ou personne extérieure et renvoie un tableau avec les URLs donnant accès à ces deux fihiers
 * REM: Dans le cas où le client est une PM ou un GI, on prend la photo et la signature du premier responsable trouvé dans la DB ayant le pouvoir de signature
 * @author Stefano AMEKOUDI
 * @since 2.8
 * @param array $client_perso ID, photo et signature du client ou personne exterieure
 * @param misc $db Connection déjà ouverte à la BD
 * @return Array Tableau avec "photo_loc_path" => chemin d'accès vers la photo et "sign_loc_path" => chemin d'accès vers la signature
 */
function recuperImage($client_perso, $db) {
  // Photo

  if ($client_perso["photo"] != null) {
    // Recherche photo
    $sql = "SELECT loid FROM pg_largeobject WHERE loid = '".$client_perso["photo"]."' limit 1";
    $result = executeQuery($db, $sql, true);
    if ($result->param[0] != NULL) { // la photo Existe?
      $path_photo = makeImagePaths($client_perso["photo"]);
      // Extraction de la photo
      $sql = "SELECT lo_export('".$client_perso["photo"]."', '".$path_photo["localfilepath"]."')";
      $result = $db->query($sql);
      if (DB::isError($result))
        $path_photo = null;
    }
  }
  // Signature
  if ($client_perso["signature"] != null) {
    $sql = "SELECT loid FROM pg_largeobject WHERE loid = '".$client_perso["signature"]."' limit 1";
    $result = executeQuery($db, $sql, true);
    if ($result->param[0] != NULL) { // la photo Existe?
      $path_sig = makeImagePaths($client_perso["signature"]);
      // Extraction de la signature
      $sql = "SELECT lo_export('".$client_perso["signature"]."', '".$path_sig["localfilepath"]."')";
      $result = $db->query($sql);
      if (DB::isError($result))
        $path_sig = null;
    }
  }
  return array ("photo" => $path_photo["localfilepath"], "signature" => $path_sig["localfilepath"]);
}

?>


<!-- Header commun -->
<html>

<head>
<title>Assistant Transfert des images (Photos - Signature) de la BD vers le FileSystem ADbanking</title>
<?php  require_once 'lib/html/stylesheet.php'; ?>
<style type="text/css">
            h1 {font:14pt helvetica,verdana;
                margin-top:
                15px;
                margin-bottom:
                15px;
               }
            tr.tablealternheader { background-color : #e0e0ff; }
            tr.tablealternligneimpaire  { background-color : #ffd5d5; }
            tr.tablealternlignepaire { background-color : #e0e0ff; }
            table.tableclassic { background-color : #e0e0ff; }
            </style>
            <script type="text/javascript" src="<?php echo "$http_prefix/lib/java/scp.php?m_agc=$global_id_agence&http_prefix=$http_prefix";?>"></script>
                                               </head>

                                               <body bgcolor=white>
                                                             <table width="100%" cellpadding=5 cellspacing=0 border=0>
                                                                                             <tr>
                                                                                             <td><a target=_blank href="http://www.aquadev.org"><img border=0 title="<?= _("ADbanking Logo") ?>" alt="<?= _("ADbanking Logo") ?>" width=400 height=40 src="../../images/ADbanking_logo.jpg"></a></td>
                                                                                                                       <td valign=bottom align=center><font face="helvetica,verdana" size="+2">Module de Transfert d'Image</font></td>
                                                                                                                                               </tr>
                                                                                                                                               </table>

                                                                                                                                               <?php

                                                                                                                                               // Ecran 1 : Sélection du client
                                                                                                                                               if (!isset($prochain_ecran) || ($prochain_ecran == 1))
                                                                                                                                               {

                                                                                                                                               $db = $dbHandler->openConnection();
                                                                                                                                               $sql = "SELECT * FROM ad_ses";
                                                                                                                                               $result = $db->query($sql);

                                                                                                                                               if (DB::isError($result))
                                                                                                                                               {
                                                                                                                                               $dbHandler->closeConnection(false);
                                                                                                                                               signalErreur(__FILE__,__LINE__,__FUNCTION__);
                                                                                                                                               }
                                                                                                                                               else if ($result->numrows() == 0)
                                                                                                                                               {
                                                                                                                                               $dbHandler->closeConnection(true);
                                                                                                                                               $myForm = new HTML_message(_("Erreur de Connexion"));
                                                                                                                                               $myForm->setMessage(_("Avertissement : Vous devez ouvrir une session ADbanking avant d'éffectuer cette opération")." <br/> "._("Voulez vous vous connecter maintenant?"));
                                                                                                                                               $myForm->addButton(BUTTON_OUI,"login");
                                                                                                                                               $myForm->addButton(BUTTON_NON, "nologin");
                                                                                                                                               $myForm->buildHTML();
                                                                                                                                               echo $myForm->HTML_code;
                                                                                                                                               echo "<br /><p align=center>"._("L'écran d'ouverture de session sur ADbanking se fermera automatiquement si l'authentification réussie.")."</p>"      		;
                                                                                                                                                                  }
                                                                                                                                                                  else if ($result->numrows() >= 1)
                                                                                                                                                                  {
                                                                                                                                                                  session_write_close();
                                                                                                                                                                  $images_path = $lib_path."/backup/images_clients";

                                                                                                                                                                  //----  Début Récupération des clients ----//
                                                                                                                                                                  echo "<br><p><b>"._("Export des photos et signatures des clients.")."</b></p><br>";
                                                                                                                                                                  // Liste des clients ayant des images
                                                                                                                                                                  $sql = "SELECT id_client, photo, signature FROM ad_cli WHERE (photo IS NOT NULL) OR (signature IS NOT NULL) ORDER BY id_client" ;
                                                                                                                                                                  $resultat = executeQuery($db, $sql, false);

                                                                                                                                                                  $cc_p = 0; $cc_s = 0;
                                                                                                                                                                  $photo_new_path = $images_path."/clients/photos/";
                                                                                                                                                                  $signature_new_path = $images_path."/clients/signatures/";

                                                                                                                                                                  echo "<p>"._("Clients en cours de traitement")." : ";
                                                                                                                                                                  foreach($resultat->param as $client)
                                                                                                                                                                  // Si ce idendifiant correspont à un client possédant une photo et/ou une signature
                                                                                                                                                                  {
                                                                                                                                                                  $images_client 	= recuperImage($client, $db);
                                                                                                                                                                  $photo_client 	= $images_client["photo"];
                                                                                                                                                                  $signature_client 	= $images_client["signature"];

                                                                                                                                                                  // Recupération des photos
                                                                                                                                                                  if ($photo_client != NULL)
                                                                                                                                                                  {
                                                                                                                                                                  $image_name = strval($client["id_client"]);
                                                                                                                                                                  if (!(is_dir($photo_new_path.$image_name{0})))
                                                                                                                                                                  mkdir($photo_new_path.$image_name{0},0777);
                                                                                                                                                                  rename($photo_client,$photo_new_path.$image_name{0}."/".$image_name);
                                                                                                                                                                  $cc_p++;
                                                                                                                                                                  }
                                                                                                                                                                  // Recupération des Signatures
                                                                                                                                                                  if ($signature_client != NULL)
                                                                                                                                                                  {
                                                                                                                                                                  $image_name = strval($client["id_client"]);
                                                                                                                                                                  if (!(is_dir($signature_new_path.$image_name{0})))
                                                                                                                                                                  mkdir($signature_new_path.$image_name{0},0777);
                                                                                                                                                                  rename($signature_client,$signature_new_path.$image_name{0}."/".$image_name);
                                                                                                                                                                  $cc_s++;
                                                                                                                                                                  }
                                                                                                                                                                  // Suppression de la photo (large Object) dans la base
                                                                                                                                                                  if ($client["photo"] != "") {
                                                                                                                                                                  $sql = "DELETE FROM pg_largeobject WHERE loid = ".$client["photo"].";";
                                                                                                                                                                  $result = $db->query($sql);
                                                                                                                                                                  }
                                                                                                                                                                  if ($client["signature"] != "") {
                                                                                                                                                                  $sql = "DELETE FROM pg_largeobject WHERE loid = ".$client["signature"].";";
                                                                                                                                                                  $result = $db->query($sql);
                                                                                                                                                                  }
                                                                                                                                                                  // Mise à jour de la BD, remise à 0 des champs Signature et Photo
                                                                                                                                                                  $sql="UPDATE ad_cli SET photo=NULL,signature=NULL WHERE id_client=".$client["id_client"]." AND id_ag=$global_id_agence ;";
                                                                                                                                                                  $result = $db->query($sql);
                                                                                                                                                                  // On envoie les données HTTP en continu {@link PHP_MANUAL#flush}
                                                                                                                                                                  $str = $client["id_client"]." ";
                                                                                                                                                                  echo str_pad($str, 4096)."\n";
                                                                                                                                                                  flush();
                                                                                                                                                                  set_time_limit('300');
                                                                                                                                                                  // COMMIT intermediaire après récupération de 20 images (photos+signatures)
                                                                                                                                                                  if (($cc_p + $cc_s + 1) % 20 == 0) {
                                                                                                                                                                  $dbHandler->closeConnection(true);
                                                                                                                                                                  $db = $dbHandler->openConnection();
                                                                                                                                                                  echo str_pad("</p>\n<p><font color=\"green\"><b>"._("20 images traitées et sauvegardées</b>")."</font></p>\n<p>"._("Clients en cours de traitement")." : ", 4096)."\n";
                                                                                                                                                                  flush();
                                                                                                                                                                  }
                                                                                                                                                                  }
                                                                                                                                                                  echo "</p>\n";
                                                                                                                                                                  // ---- Fin récupération Client --- //

                                                                                                                                                                  // ----  Début Récupération Personnes Extérieures ---- //
                                                                                                                                                                  echo "<br><p><b>"._("Export des photos et signatures des personnes extérieures.")."</b></p><br>";
                                                                                                                                                                  // Liste des clients ayant des images
                                                                                                                                                                  $sql = "SELECT id_pers_ext, photo, signature FROM ad_pers_ext WHERE (photo IS NOT NULL) OR (signature  IS NOT NULL) ORDER BY id_pers_ext" ;
                                                                                                                                                                  $resultat = executeQuery($db, $sql, false);
                                                                                                                                                                  $cp_p = 0; $cp_s = 0;

                                                                                                                                                                  $photo_new_path = $images_path."/perso_ext/photos/";
                                                                                                                                                                  $signature_new_path = $images_path."/perso_ext/signatures/";

                                                                                                                                                                  echo "<p>"._("Personnes Extérieures en cours de traitement")." : ";
                                                                                                                                                                  foreach($resultat->param as $perso)
                                                                                                                                                                  // Si ce idendifiant correspont à un perso possédant une photo et/ou une signature
                                                                                                                                                                  {
                                                                                                                                                                  $images_perso 	= recuperImage($perso, $db);
                                                                                                                                                                  $photo_perso 	= $images_perso["photo"];
                                                                                                                                                                  $signature_perso 	= $images_perso["signature"];

                                                                                                                                                                  // Recupération des photos
                                                                                                                                                                  if ($photo_perso != NULL)
                                                                                                                                                                  {
                                                                                                                                                                  $image_name = strval($perso["id_pers_ext"]);
                                                                                                                                                                  if (!(is_dir($photo_new_path.$image_name{0})))
                                                                                                                                                                  mkdir($photo_new_path.$image_name{0},0777);
                                                                                                                                                                  rename($photo_perso,$photo_new_path.$image_name{0}."/".$image_name);
                                                                                                                                                                  $cp_p++;
                                                                                                                                                                  }
                                                                                                                                                                  // Recupération des Signatures
                                                                                                                                                                  if ($signature_perso != NULL)
                                                                                                                                                                  {
                                                                                                                                                                  $image_name = strval($perso["id_pers_ext"]);
                                                                                                                                                                  if (!(is_dir($signature_new_path.$image_name{0})))
                                                                                                                                                                  mkdir($signature_new_path.$image_name{0},0777);
                                                                                                                                                                  rename($signature_perso,$signature_new_path.$image_name{0}."/".$image_name);
                                                                                                                                                                  $cp_s++;
                                                                                                                                                                  }
                                                                                                                                                                  // Suppression de la photo (large Object) dans la base
                                                                                                                                                                  if ($perso["photo"] != "") {
                                                                                                                                                                  $sql = "DELETE FROM pg_largeobject WHERE loid = ".$perso["photo"].";";
                                                                                                                                                                  $result = $db->query($sql);
                                                                                                                                                                  }
                                                                                                                                                                  if ($perso["signature"] != "") {
                                                                                                                                                                  $sql = "DELETE FROM pg_largeobject WHERE loid = ".$perso["signature"].";";
                                                                                                                                                                  $result = $db->query($sql);
                                                                                                                                                                  }
                                                                                                                                                                  // Mise à jour de la BD, remise à 0 des champs Signature et Photo
                                                                                                                                                                  $sql="UPDATE ad_pers_ext SET photo=NULL,signature=NULL WHERE id_ag=$global_id_agence AND id_client=".$perso["id_pers_ext"].";";
                                                                                                                                                                  $result = $db->query($sql);
                                                                                                                                                                  // On envoie les données HTTP en continu {@link PHP_MANUAL#flush}
                                                                                                                                                                  $str = $perso["id_pers_ext"]." ";
                                                                                                                                                                  echo str_pad($str, 4096)."\n";
                                                                                                                                                                  flush();
                                                                                                                                                                  set_time_limit('300');
                                                                                                                                                                  // COMMIT intermediaire après récupération de 20 images (photos+signatures)
                                                                                                                                                                  if (($cc_p + $cc_s + 1) % 20 == 0) {
                                                                                                                                                                  $dbHandler->closeConnection(true);
                                                                                                                                                                  $db = $dbHandler->openConnection();
                                                                                                                                                                  echo str_pad("</p>\n<p><font color=\"green\"><b>"._("20 images traitées et sauvegardées")."</b></font></p>\n<p>"._("Personnes Extérieures en cours de traitement")." : ", 4096)."\n";
                                                                                                                                                                  flush();
                                                                                                                                                                  }

                                                                                                                                                                  }
                                                                                                                                                                  echo "</p>\n";
                                                                                                                                                                  // ---- Fin récupération Personnes Extérieures --- //

                                                                                                                                                                  $dbHandler->closeConnection(true);

                                                                                                                                                                  $myForm = new HTML_message(_("Confirmation récupération images"));
                                                                                                                                                                  $myForm->setMessage(_("<br> "._("Les photos et Signatures des Clients et/ou Personnes Extérieures ont été transféré de la BD vers FileSystem")." <div align=\"left\"><br/> "._("Images récupérées").": <br/> - "._("Photos clients:")." $cc_p<br/> - "._("Signatures clients:")." $cc_s<br> - "._("Photos Personnes extérieures:")." $cp_p<br /> - "._("Signatures Personnes extérieures:")." $cp_s</div>"));
                                                                                                                                                                  $myForm->buildHTML();
                                                                                                                                                                  echo $myForm->HTML_code;
                                                                                                                                                                  }
                                                                                                                                                                  }

                                                                                                                                                                  // Ecran login : Redirection vers l'Ecran de connexion
else if ($prochain_ecran == "login") {
  echo "<SCRIPT type=\"text/javascript\">\n";
  echo "window.open(\"$SERVER_NAME/login/main_login.php?recup_data=Vrai\", '$window_name', \"menubar=no,resizable=yes,status=yes,toolbar=no,location=no\");\n";
  echo "</SCRIPT>\n";

  $myForm = new HTML_message(_("Recharger la page"));
  $myForm->setMessage(_("Info")." : <br/>"._("Si l'ouverture de session sur ADbanking a réussie, recharger cette page <Cliquez sur OK et patientez...>"));
  $myForm->addButton(BUTTON_OK,1);

  $myForm->buildHTML();
  echo $myForm->HTML_code;
}

// Ecran nologin : Fermeture
else if ($prochain_ecran == "nologin") {
  echo "<script type=\"text/javascript\">\n";
  echo "opener = self;\n";
  echo "self.close();\n";
  echo "</script>\n";
}

?>

</body>
</html>