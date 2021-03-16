<?php

/**
 * Gestionnaire d'image permettant
 * <ul>
 *  <li> de visulaiser une image récupérée d'un formulaire HTML_GEN2 (en taille réelle)</li>
 *  <li> de modifier un telle image (uploader une nouvelle image)</li>
 * </ul>
 *
 * Ce script a besoin de 4 champs provenant de l'appelant :<ul>
 * <li> shortname = Le nom de l'image dans le formulaire</li>
 * <li> longname = Le nom 'verbeux' de l'image</li>
 * <li> url = l'URL actuelle de l'image (peut etre vide si aucun image chargée pour le moment</li>
 * <li> canmodify = booléen indiquant si l'image peut ou non etre modifiée</li>
 * </ul>
 * @author Thomas Fastenakel
 * @package Ifutilisateur
 */

require_once 'lib/misc/divers.php';
require_once 'lib/html/HtmlHeader.php';

if (sizeof($_FILES) == 0) { // Premier écran
  // Sauvegarde des valeurs postées
  $SESSION_VARS["shortname"] = $shortname;
  $SESSION_VARS["longname"] = $longname;
  $SESSION_VARS["canmodif"] = $canmodif;
} else {
  // Récupération des variables de session
  $shortname = $SESSION_VARS["shortname"];
  $longname = $SESSION_VARS["longname"];
  $canmodif = $SESSION_VARS["canmodif"];

  // Traitement du fichier uploadé

  if ($_FILES["newimage"]["name"] == "") {
    // On a veut supprimer l'image actuelle
    $url = $http_prefix."/images/travaux.gif";
    $filename = "";
  } else {
    // Test de la taille du fichier
    if ($_FILES["newimage"]["size"] == 0) {
      $html_err = new HTML_erreur(_("Echec du chargement de l'image."));
      $html_err->setMessage(sprintf(_("L'image dépasse la taille maximum autorisée (%s Ko)"),(MAX_UPLOAD_IMAGE_SIZE / 1000)));
      $html_err->addButton("BUTTON_OK", '');

      echo "<FORM name=\"imageform\" enctype=\"multipart/form-data\" action=\"$PHP_SELF\" method=\"POST\">";
      echo "<BR/><BR/><P align=center><input type=\"hidden\" name=\"MAX_FILE_SIZE\" value=\"".MAX_UPLOAD_IMAGE_SIZE."\" />";
      echo _("Nouvelle image").": <INPUT name=\"newimage\" type=\"file\" /></P>";
      echo "<P align=\"center\"> <INPUT type=\"submit\" value=\"Valider\"/> </P>";

      $html_err->buildHTML();
      echo $html_err->HTML_code;
      die();
    }

    // Génération d'un nom unique pour le fichier qui sera créé
    $tail = rand(0, 1023);  // Génère un nombre aléatoire entre 0 et 1023

    $filename = $global_nom_login."-".$tail;

    $PATHS = makeImagePaths($filename);
    // Test de l'upload
    if (move_uploaded_file($_FILES['newimage']['tmp_name'], $PATHS["localfilepath"])) {
      $url = $PATHS["url"];
      $filename = $PATHS["localfilepath"];
    } else {
      signalErreur(__FILE__,__LINE__,__FUNCTION__); // Pb lors de l'UPLOAD de l'image
    }
  }
}

// Affichage du titre de l'image
echo "<H1 align=center>"._("Gestionnaire d'images")."</H1>";

// Fonction javascript envoyant l'URL de l'image vers le parent
$js = "<SCRIPT type=\"text/javascript\">
      function assignParentWindowURL()
      {";
if ($canmodif == false) // Rien à faire
  $js .= "return true;";
else
  $js .= "opener.document.ADForm.HTML_GEN_IMG_$shortname.value = '$filename';
         opener.document.ADForm.$shortname.src = '$url';
         return true";
$js .= "}</SCRIPT>";

echo $js;

echo "<FORM name=\"imageform\" enctype=\"multipart/form-data\" action=\"$PHP_SELF\" method=\"POST\">";

// Affichage du titre de l'image
echo "<H2 align=center>$longname</H1>";

// Affichage de l'image proprement dite (en taille réelle)
echo " <p align=center>
<IMG src=\"$url\" alt=\""._("Aucune image actuellement")."\"/>
</p>";

// Si c'est permis : bouton 'Modifier'
if ($canmodif == 1) {
  echo "<BR/><BR/><P align=center><input type=\"hidden\" name=\"MAX_FILE_SIZE\" value=\"".MAX_UPLOAD_IMAGE_SIZE."\" />";
  echo _("Nouvelle image").": <INPUT name=\"newimage\" type=\"file\" /></P>";
  echo "<P align=\"center\"> <INPUT type=\"submit\" value=\""._("Valider")."\"/> </P>";
}

// Bouton 'Fermer'
echo "<BR/><BR/><P align=center>
<INPUT type=\"button\" name=\"close\" value=\""._("Enregistrer la photo")."\" onclick=\"assignParentWindowURL();window.close();\">
</P>";

// Fermeture du formulaire
echo "</FORM>";

require_once 'lib/html/HtmlFooter.php';

?>