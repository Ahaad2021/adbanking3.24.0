<?php
/* vim: set expandtab softtabstop=2 shiftwidth=2 foldmethod=marker: */

/**
 * [147] Remboursement de crédit par lot
 *
 * Cette opération comprends les écrans :
 * - Rpl-1 : Confirmation du processus
 * - Rpl-2 : Affichage du resultat
 * @package Credit
 */

require_once 'lib/misc/Erreur.php';
require_once 'lib/dbProcedures/credit.php';
require_once 'lib/misc/divers.php';
require_once 'modules/rapports/xml_credits.php';
require_once 'batch/batch_declarations.php';

/*{{{ Rcr-1 : Sélection d'un dossier de crédit */
if ($global_nom_ecran == "Rpl-1") {
    $MyPage = new HTML_erreur(_("Confirmation du remboursement de credit par lot"));
    $nombre_dossier = getNombrePrelevAuto();
    $SESSION_VARS['nombre_dossier'] = $nombre_dossier;
    $date = strftime("%A %d %B %Y");

    if($nombre_dossier == 0){
        $MyPage->setMessage(sprintf(_("Tous les remboursements ont été effectués pour la date du %s"), "<b>$date</b>"));
        $MyPage->addButton(BUTTON_OK, "Gen-6");
    }else{
        $MyPage->setMessage("<b>"._("Attention")."</b>, ".sprintf(_("vous allez procéder au remboursement de crédit par lot pour
        la date du %s et les dates antérieur <br/> Le nombre d'échéances à traiter est de %s <br/> Veuillez cliquer sur OK
        pour démarrer"), "<b>$date</b>", "<b>$nombre_dossier</b>")."
        <br/><br/> <label for=\"remb_limit\">Nombre limite d'échéances à traiter</label> <input type='text' name='remb_limit'
        id='remb_limit' value='2000' />");

        $MyPage->addButton(BUTTON_OK, "Rpl-2");
        $MyPage->addButton(BUTTON_CANCEL, "Gen-6");
    }

    $MyPage->buildHTML();

    echo $MyPage->HTML_code;
}
elseif ($global_nom_ecran == "Rpl-2"){
    global $error;
    affiche(_("Démarrage des remboursement des échéance pour les dossier concerné"));
    $myErr=remboursementParLot($remb_limit); //Prélèvements automatiques
    if ($myErr->errCode == NO_ERR) {
        $dossier_traiter = $myErr->param['dossier_count'];
        $nombre_dossier_restant = $SESSION_VARS['nombre_dossier'] - $dossier_traiter;
        $MyPage = new HTML_message(_("Remboursement crédit par lot"));
        $MyPage->setMessage(sprintf(_("%s échéance ont été remboursé pour les dossier concerné, <br/>le nombre restant est de %s"),"<b>".$dossier_traiter."</b>", "<b>".$nombre_dossier_restant."</b>")." <br />");
        $MyPage->addButton(BUTTON_OK, "Gen-6");
        $MyPage->buildHTML();
    }else{
        $MyPage = new HTML_erreur(_("Echec du Remboursement crédit par lot"));
        $MyPage->setMessage($error[$myErr->errCode].$myErr->param);
        $MyPage->addButton(BUTTON_OK, "Gen-6");
        $MyPage->buildHTML();
    }
    echo $MyPage->HTML_code;
}
?>