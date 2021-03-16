<?php

/**
 * [164] Enregistrement des chèques
 *
 * Cette opération comprends les écrans :
 * - Ecc-1 : Enregistrement ficher Excel
 * - Ecc-2 : Confirmation enregistrement
 *
 * @package Chèques certifiés
 *
 */

require_once 'lib/html/HTML_GEN2.php';
require_once 'lib/html/FILL_HTML_GEN2.php';
require_once 'lib/html/HTML_erreur.php';
require_once 'lib/misc/VariablesGlobales.php';
require_once 'lib/dbProcedures/agence.php';
require_once 'lib/dbProcedures/guichet.php';
require_once 'lib/dbProcedures/cheque_interne.php';
require_once 'lib/misc/divers.php';
require_once 'lib/misc/tableSys.php';

if ($global_nom_ecran == "Ecc-1") {

    $MyPage = new HTML_GEN2(_("Enregistrement fichier Excel des chèques remis à la compensation"));

    $htm1 = "<table cellspacing=\"0\" cellpadding=\"0\" border=\"0\"><tr><td width=\"300px\">&nbsp;</td><td
    style=\"padding-left:30px;\" align=\"right\">"._("Fichier excel")."<font color=\"#FF0000\" face=\"HELVETICA\" size=\"4\"><b>*</b></font> : </td><td
    style=\"padding-left:40px;\"><INPUT id=\"fichier_excel\" name=\"fichier_excel\" type=\"file\" /></td></tr><tr><td width=\"300px\">&nbsp;</td><td align=\"right\">&nbsp;</td><td>&nbsp;</td></tr><tr><td width=\"300px\">&nbsp;</td><td align=\"right\">&nbsp;</td><td>&nbsp;</td></tr></table>";

    $MyPage->addHTMLExtraCode("htm1", $htm1);

    // Validation csv file
    $jsFile = "
                  function validCsvFile(){
                    if(document.ADForm.fichier_excel.value==''){
                        alert ('Veuillez choisir le fichier CSV à télécharger !');
                        ADFormValid=false;
                    }
                  }
                  validCsvFile();
    ";

    $MyPage->addJS(JSP_BEGIN_CHECK, "JS_CHQ", $jsFile);

    $MyPage->addFormButton(1, 1, "valider", _("Enregistrer"), TYPB_SUBMIT);
    $MyPage->setFormButtonProperties("valider", BUTP_PROCHAIN_ECRAN, 'Ecc-2');

    $MyPage->addFormButton(1, 2, "annuler", _("Annuler"), TYPB_SUBMIT);
    $MyPage->setFormButtonProperties("annuler", BUTP_PROCHAIN_ECRAN, 'Tcc-1');
    $MyPage->setFormButtonProperties("annuler", BUTP_CHECK_FORM, false);

    $SESSION_VARS['fichier_excel'] = NULL;

    $MyPage->buildHTML();
    echo $MyPage->getHTML();

} elseif ($global_nom_ecran == "Ecc-2") {

    if (file_exists($fichier_excel)) {
        $filename = $fichier_excel.".tmp";
        move_uploaded_file($fichier_excel, $filename);
        exec("chmod a+r ".escapeshellarg($filename));
        $SESSION_VARS['fichier_excel'] = $filename;
    } else {
        $SESSION_VARS['fichier_excel'] = NULL;
    }

    $err = ChequeCertifie::parseFileChequeCompensation($SESSION_VARS['fichier_excel']);

    if ($err->errCode != NO_ERR) {

        $titre = "Enregistrement partiel des chèques remis à la compensation";

        $err_msg .= "";

        if ($err->param['chq_count'] > 0) {

            if ($err->param['chq_count'] > 1){
                $chq_msg = "nouveaux chèques internes ont été enregistrés avec succès";
            } else {
                $chq_msg = "nouveau chèque interne a été enregistré avec succès";
            }

            $err_msg .= sprintf('<p align="center" style="padding-bottom:15px;"><br/><font color="#007777">%s %s !</font><br/></p>', $err->param['chq_count'], $chq_msg);
        }

        $err_msg .= "Ci-dessous la liste des chèques ayant des erreurs :<br />";

        if(is_array($err->param['cheque_err'])) {
            foreach($err->param['cheque_err'] as $key => $val) {
                $err_msg .= "<br /> Chèque No. ".$key." : ".$val;
            }
        }

        $html_err = new HTML_erreur($titre);

        $html_err->setMessage($err_msg);

        $html_err->addButton("BUTTON_OK", 'Ecc-1');

        $html_err->buildHTML();
        echo $html_err->HTML_code;
    }
    else
    {
        // Affichage de la confirmation
        $html_msg = new HTML_message("Confirmation enregistrement");

        if ($err->param['chq_count'] > 1){
            $chq_msg = "nouveaux chèques internes ont été enregistrés avec succès";
        } else {
            $chq_msg = "nouveau chèque interne a été enregistré avec succès";
        }

        $html_msg->setMessage(sprintf(" <br />%s %s !<br /> ", $err->param['chq_count'], $chq_msg));

        $html_msg->addButton("BUTTON_OK", 'Tcc-1');

        $html_msg->buildHTML();
        echo $html_msg->HTML_code;
    }

} else {
    signalErreur(__FILE__, __LINE__, __FUNCTION__);
    // _("L'écran $global_nom_ecran n'existe pas")
}