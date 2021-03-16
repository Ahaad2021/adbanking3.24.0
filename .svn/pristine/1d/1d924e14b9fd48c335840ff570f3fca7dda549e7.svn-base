<?php
/**
 * Created by PhpStorm.
 * User: Roshan
 * Date: 1/30/2018
 * Time: 2:30 PM
 */

/**
 * Fonction pour la gestion des fichiers ZIP (exports)
 *
 * @package Rapports
 **/

/**
 * Vérifie que le fichier ZIP a bien été créé et ouvre une fenêtre appelée à le contenir.
 *
 * @author Roshan
 * @since 1/30/2018
 * @param string $ecran_retour Le code de l'écran de retour après l'export
 * @param string $filename Le nom du fichier ZIP
 * @return void
 */
function getShowZIPHTML($ecran_retour, $filename, $nomfile, $erreur) {
  global $SERVER_NAME;

  if (file_exists($filename)) {
    $url = "$SERVER_NAME/rapports/http/rapport_zip.php?m_agc=".$_REQUEST['m_agc']."&filename=$filename&nomFichier=$nomfile";
    $js = "<SCRIPT type=\"text/javascript\">child_window=OpenBrw('$url', 'Téléchargement des Rapports');</SCRIPT>";

    $MyPage = new HTML_message(_("Bilan du Téléchargement"));
    $msg = _("Téléchargement de(s) rapport(s) en version zip a été effectuée avec succès !");
    if(isset($erreur) && $erreur != ''){
      $msg .= "\n"._("Veuillez noter qu'aucun fichier 'txt' a été créé pour les informations suivant : ".$erreur."!!");
    }
    $MyPage->setMessage($msg);
    $MyPage->addButton(BUTTON_OK, $ecran_retour);
    $MyPage->buildHTML();
    return $MyPage->HTML_code." ".$js;
  } else {
    $erreur = new HTML_erreur(_("Echec lors du Téléchargement"));
    $erreur->setMessage(_("ATTENTION, aucune données pour cette periode de ce fait aucun fichier(txt/zip) trouvé!!"));
    $erreur->addButton(BUTTON_OK, $ecran_retour);
    $erreur->buildHTML();
    return $erreur->HTML_code;
  }
}

?>