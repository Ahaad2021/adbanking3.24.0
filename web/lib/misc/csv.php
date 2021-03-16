<?php

/**
 * Fonction pour la gestion des fichiers CSV (exports)
 *
 * @package Rapports
 **/

/**
 * Vérifie que le fichier CSV a bien été créé et ouvre une fenêtre appelée à le contenir.
 *
 * @author Thomas Fastenakel
 * @since 21/08/03
 * @param string $ecran_retour Le code de l'écran de retour après l'export
 * @param string $filename Le nom du fichier CSV
 * @return void
 */
function getShowCSVHTML($ecran_retour, $filename) {
  global $SERVER_NAME;

  if (file_exists($filename)) {
    $url = "$SERVER_NAME/rapports/http/rapport_csv.php?m_agc=".$_REQUEST['m_agc']."&filename=$filename";
    $js = "<SCRIPT type=\"text/javascript\">child_window=OpenBrw('$url', 'Exportation');</SCRIPT>";

    $MyPage = new HTML_message(_("Bilan de l'exportation"));
    $MyPage->setMessage(_("L'exportation de données a été effectuée avec succès !"));
    $MyPage->addButton(BUTTON_OK, $ecran_retour);
    $MyPage->buildHTML();
    return $MyPage->HTML_code." ".$js;
  } else {
    $erreur = new HTML_erreur(_("Echec lors de l'exportation"));
    $erreur->setMessage(_("Une erreur est survenue lors de l'exportation, aucun fichier trouvé."));
    $erreur->addButton(BUTTON_OK, $ecran_retour);
    $erreur->buildHTML();
    return $erreur->HTML_code;
  }
}

function getShowCSVHTMLATM($ecran_retour, $filename) {
    global $SERVER_NAME;

    if (file_exists($filename)) {
        $url = "$SERVER_NAME/rapports/http/rapport_csv.php?m_agc=".$_REQUEST['m_agc']."&filename=$filename";
        $js = "<SCRIPT type=\"text/javascript\">child_window=OpenBrw('$url', 'Exportation');</SCRIPT>";
        return $js;
    } else {
        $erreur = new HTML_erreur(_("Echec lors de l'exportation"));
        $erreur->setMessage(_("Une erreur est survenue lors de l'exportation, aucun fichier trouvé."));
        $erreur->addButton(BUTTON_OK, $ecran_retour);
        $erreur->buildHTML();
        return $erreur->HTML_code;
    }
}

/**
 * Vérifie que le fichier XML a bien été créé et ouvre une fenêtre appelée à le contenir.
 *
 * @author B&D
 * @param string $ecran_retour Le code de l'écran de retour après l'export
 * @param string $filename Le nom du fichier XML
 * @return void
 */
function getShowXMLHTML($ecran_retour, $filename) {
  global $SERVER_NAME;

  if (file_exists($filename)) {
    $url = "$SERVER_NAME/rapports/http/rapport_xml.php?m_agc=".$_REQUEST['m_agc']."&filename=$filename";
    $js = "<SCRIPT type=\"text/javascript\">child_window=OpenBrw('$url', 'Exportation');</SCRIPT>";

    $MyPage = new HTML_message(_("Bilan de l'exportation"));
    $MyPage->setMessage(_("L'exportation de données a été effectuée avec succès !"));
    $MyPage->addButton(BUTTON_OK, $ecran_retour);
    $MyPage->buildHTML();
    return $MyPage->HTML_code." ".$js;
  } else {
    $erreur = new HTML_erreur(_("Echec lors de l'exportation"));
    $erreur->setMessage(_("Une erreur est survenue lors de l'exportation, aucun fichier trouvé."));
    $erreur->addButton(BUTTON_OK, $ecran_retour);
    $erreur->buildHTML();
    return $erreur->HTML_code;
  }
}

/**
 * Ecrit un fichier CSV sur le disque pour envoi ultérieur par le serveur.
 *
 * @author Antoine Delvaux
 * @since 2.5
 * @param string $a_csv Le code CSV à écrire dans le fichier.
 * @return ErrorObj ErrorObj NO_ERR avec le nom du fichier en paramètre en cas de succès.
 */
function doWriteCSV($a_csv) {
  global $csv_output;

  $filename = $csv_output.".".session_id();
  $fich = fopen($filename, 'w');
  // TODO Intercepter les erreurs lors de l'écriture du fichier
  fwrite($fich, $a_csv);
  fclose($fich);
  chmod($filename, 0666);

  return new ErrorObj(NO_ERR, $filename);
}

?>