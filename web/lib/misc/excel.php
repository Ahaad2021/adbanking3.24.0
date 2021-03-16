<?php

/**
 * Fonction pour la gestion des fichiers XLS (exports)
 *
 * @package Rapports
 **/

/**
 * Vérifie que le fichier XLS a bien été créé et ouvre une fenêtre appelée à le contenir.
 *
 * @author Roshan Bolah
 * @since 15/05/18
 * @param string $ecran_retour Le code de l'écran de retour après l'export
 * @param string $filename Le nom du fichier XLS
 * @return void
 */
function getShowEXCELHTML($ecran_retour, $filename) {
    global $SERVER_NAME;

    if (file_exists($filename)) {
        $url = "$SERVER_NAME/rapports/http/rapport_excel.php?m_agc=".$_REQUEST['m_agc']."&filename=$filename";
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

/** FONCTION ML**/
function getShowEXCELHTMLML($ecran_retour, $fileset) {
    global $SERVER_NAME;

    $file_exists = true;
    foreach($fileset as $f){
        if(!file_exists($f)){
            $file_exists = false;
            break;
        }
    }

    if ($file_exists) {
        $js = "<SCRIPT type=\"text/javascript\">";
        foreach ($fileset as $file_name => $file){
            $url = "$SERVER_NAME/rapports/http/rapport_excel.php?m_agc=".$_REQUEST['m_agc']."&filename=$file&file_title=$file_name";
            $js .= "window.open('".$url."');";
        }
        $js .= "</SCRIPT>";


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
?>