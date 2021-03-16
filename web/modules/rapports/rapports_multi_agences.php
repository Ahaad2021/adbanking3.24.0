<?php
/* vim: set expandtab softtabstop=2 shiftwidth=2 foldmethod=marker: */

/**
 * [320] Rapports multi-agences.
 * Ces fonctions appellent les écrans suivants :
 * - Rma-1 : Sélection du rapport à imprimer
 * - Rma-10 : Personalisation du rapport Situation de compensation
 * - Rma-11 et Rma-12 : Impression ou export CSV du rapport Situation de compensation
 *
 * A noter que le rapport visualisation des operations en deplacé est aussi integré pour etre appellé a partir du
 * menu Rapports multi agence
 *
 * @package Rapports
 **/
require_once 'ad_ma/app/models/AgenceRemote.php';
require_once 'ad_ma/app/models/Agence.php';
require_once 'ad_ma/app/models/AuditVisualisation.php';
require_once 'lib/html/HTML_GEN2.php';
require_once 'lib/dbProcedures/rapports.php';
require_once 'modules/rapports/xml_multi_agences.php';
require_once 'modules/rapports/xslt.php';
require_once 'lib/misc/tableSys.php';
require_once 'lib/misc/csv.php';
require_once 'lib/misc/excel.php';

/*{{{ Era-1 : Sélection du rapport à imprimer */
if ($global_nom_ecran == "Rma-1")
{
    // Recherche de tous les rapports à afficher
    foreach ($adsys["adsys_rapport"] as $key => $name) {
        if (substr($key, 0, 3) == 'RMA') {
            // acces au menu visualisation des operation en deplacer
            if(substr($key, 0, 7) == 'RMA-OPD' && check_access(194) ) {
                $rapports['RMA-OPD'] = _($name);
            }
            else
                $rapports[$key] = _($name);
        }
    }

    $MyPage = new HTML_GEN2(_("Sélection type rapport multi-agences"));
    $MyPage->addField("type", _("Type de rapport multi-agences"), TYPC_LSB);
    $MyPage->setFieldProperties("type", FIELDP_IS_REQUIRED, true);
    $MyPage->setFieldProperties("type", FIELDP_ADD_CHOICES, $rapports);

    //Boutons
    $MyPage->addFormButton(1, 1, "valider", _("Sélectionner"), TYPB_SUBMIT);
    $MyPage->addFormButton(1, 2, "annuler", _("Annuler"), TYPB_SUBMIT);
    $MyPage->setFormButtonProperties("annuler", BUTP_PROCHAIN_ECRAN, "Gen-13");
    $MyPage->setFormButtonProperties("annuler", BUTP_CHECK_FORM, false);

    // Tableau indiquant le prochain écran en fonction du code rapport
    $prochEc = array (
        "SCP" => "Rma-10",
        "OPD" => "Ama-1"

    );

    // acces au menu visualisation des operation en deplacer
    /*if(check_access(194)) {
        $prochEc["OPD"] = "Ama-1";

    }*/

    foreach ($prochEc as $code => $ecran) {
        $js .= "if (document.ADForm.HTML_GEN_LSB_type.value == 'RMA-$code') assign('$ecran');";
    }

    $MyPage->addJS(JSP_BEGIN_CHECK, "js1", $js);

    //HTML
    $MyPage->buildHTML();
    echo $MyPage->getHTML();

}
/*{{{ Rma-10 : Personnalisation du rapport situation compensation */
elseif ($global_nom_ecran == "Rma-10")
{
    $MyPage = new HTML_GEN2(_("Personnalisation du rapport Situation de compensation"));

    // Champ Agence externe
    // Récupère la liste des agences distantes
    $ListeAgences = AgenceRemote::getListRemoteAgence(true);

    $choix_agence = array();
    if (is_array($ListeAgences) && count($ListeAgences) > 0) {
        foreach ($ListeAgences as $key => $obj) {
            if (DBC::pingConnection($obj, 1) === TRUE) { // Vérifié si la BDD est active
                $choix_agence[$key] = sprintf("%s (%s)", $obj["app_db_description"], $obj["id_agc"]);
            }
        }
    }
    $MyPage->addField("IdAgence", _("Agence externe"), TYPC_LSB);
    if (!isCompensationSiege() && !isMultiAgenceSiege()) {
        $MyPage->setFieldProperties("IdAgence", FIELDP_ADD_CHOICES, $choix_agence);
    }
    $MyPage->setFieldProperties("IdAgence", FIELDP_IS_REQUIRED, false);
    $MyPage->setFieldProperties("IdAgence", FIELDP_HAS_CHOICE_TOUS, true);
    $MyPage->setFieldProperties("IdAgence", FIELDP_HAS_CHOICE_AUCUN, false);

    //Champs date début
    $MyPage->addField("date_debut", _("Date début"), TYPC_DTE);
    $MyPage->setFieldProperties("date_debut", FIELDP_DEFAULT, date("01/01/Y"));
    $MyPage->setFieldProperties("date_debut", FIELDP_IS_REQUIRED, true);
    //Champs date fin
    $MyPage->addField("date_fin", _("Date fin"), TYPC_DTE);
    $MyPage->setFieldProperties("date_fin", FIELDP_DEFAULT, date("d/m/Y"));
    $MyPage->setFieldProperties("date_fin", FIELDP_IS_REQUIRED, true);

    //Boutons
    $MyPage->addFormButton(1, 1, "valider", _("Rapport PDF"), TYPB_SUBMIT);
    $MyPage->setFormButtonProperties("valider", BUTP_PROCHAIN_ECRAN, "Rma-11");
    $MyPage->addFormButton(1, 2, "excel", _("Export EXCEL"), TYPB_SUBMIT);
    $MyPage->setFormButtonProperties("excel", BUTP_PROCHAIN_ECRAN, "Rma-12");
    $MyPage->addFormButton(1, 3, "csv", _("Export CSV"), TYPB_SUBMIT);
    $MyPage->setFormButtonProperties("csv", BUTP_PROCHAIN_ECRAN, "Rma-12");
    $MyPage->addFormButton(1, 4, "annuler", _("Annuler"), TYPB_SUBMIT);
    $MyPage->setFormButtonProperties("annuler", BUTP_PROCHAIN_ECRAN, "Gen-13");
    $MyPage->setFormButtonProperties("annuler", BUTP_CHECK_FORM, false);

    // Code javascript pour la vérification des champs obligatoires
    $JSCheck = "
        if (document.ADForm.HTML_GEN_date_date_debut.value &&
            document.ADForm.HTML_GEN_date_date_fin.value &&
            !isBefore(document.ADForm.HTML_GEN_date_date_debut.value, document.ADForm.HTML_GEN_date_date_fin.value)) {
                msg += '- La date de début doit être antérieure à la date de fin';
                ADFormValid = false;
        }
    ";

    $MyPage->addJS(JSP_BEGIN_CHECK, "JSCheck", $JSCheck);
    $MyPage->buildHTML();
    echo $MyPage->getHTML();
}
/*}}}*/
/*{{{ Rma-11 et Rma-12 : RET - Impression ou export csv situation compensation */
elseif ($global_nom_ecran == "Rma-11" || $global_nom_ecran == "Rma-12")
{
    $IdAgence = $date_debut = $date_fin = NULL;

    if(!is_null($_POST['IdAgence']))
        $IdAgence = $_POST['IdAgence'];

    if(!is_null($_POST['date_debut']))
        $date_debut = $_POST['date_debut'];

    if(!is_null($_POST['date_fin']))
        $date_fin = $_POST['date_fin'];

    $criteres_recherche['IdAgence'] = $IdAgence;
    $criteres_recherche['date_debut'] = $date_debut;
    $criteres_recherche['date_fin'] = $date_fin;

    $nom_agence = 'Tous';

    if(!empty($IdAgence)) {
        $remote_conn = AgenceRemote::getRemoteAgenceConnection($IdAgence);
        $agenceObj = new Agence($remote_conn, $IdAgence);
        $nom_agence = $agenceObj->getAgenceName($IdAgence);
    }

    $criteres = array (
        _("Agence") => $nom_agence,
        _("Date début") => date($date_debut),
        _("Date fin") => date($date_fin)
    );

    $criteres['criteres_recherche'] = $criteres_recherche;

    $AuditVisualisationObj = new AuditVisualisation();

    $DATAS = $AuditVisualisationObj->getMultiAgencesCompensationData($criteres);

    if($global_nom_ecran == "Rma-11") {
        $xml = xml_situation_compensation($DATAS, $criteres, true); //Génération du code XML
        $fichier_pdf = xml_2_xslfo_2_pdf($xml, 'situation_compensation.xslt'); //Génération du XSL-FO et du PDF
        if (isCompensationSiege() && isMultiAgenceSiege()){ //En Mode Compensation siege
          $xml = xml_situation_compensation_siege($DATAS, $criteres, true);
          $fichier_pdf = xml_2_xslfo_2_pdf($xml, 'situation_compensation_siege.xslt'); //Génération du XSL-FO et du PDF
        }
        //Message de confirmation + affichage du rapport dans une nouvelle fenêtre
        $show_pdf = get_show_pdf_html("Gen-13", $fichier_pdf);
        echo $show_pdf;
    }
    else {
        $xml = xml_situation_compensation($DATAS, $criteres, false); //Génération du code XML
        $csv_file = xml_2_csv($xml, 'situation_compensation.xslt');
        if (isCompensationSiege() && isMultiAgenceSiege()){ //En Mode Compensation siege
          $xml = xml_situation_compensation_siege($DATAS, $criteres, true);
          $csv_file = xml_2_csv($xml, 'situation_compensation_siege.xslt');
        }
        if (isset($excel) && $excel == 'Export EXCEL'){
            echo getShowEXCELHTML("Gen-13", $csv_file);
        }
        else{
            echo getShowCSVHTML("Gen-13", $csv_file);
        }
    }

    AgenceRemote::unsetRemoteAgenceConnection($remote_conn);
}
else
    signalErreur(__FILE__, __LINE__, __FUNCTION__, sprintf(_("L'écran %s n'a pas pu être trouvé"), $global_nom_ecran));
?>