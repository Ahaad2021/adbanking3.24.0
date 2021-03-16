<?php

require_once 'lib/html/HTML_GEN2.php';
require_once 'lib/dbProcedures/rapports.php';
require_once 'lib/dbProcedures/client.php';
require_once 'modules/rapports/xml_clients.php';
require_once 'modules/rapports/xslt.php';
require_once 'lib/misc/tableSys.php';
require_once 'lib/misc/divers.php';
require_once 'lib/misc/csv.php';
require_once 'lib/misc/excel.php';
require_once 'lib/dbProcedures/mobile_lending.php';

/*{{{ Mlr-1 : Parametrage rapport */
if ($global_nom_ecran == "Mlr-1"){
//    $MyPage = new HTML_GEN2(_("Paramétrage du rapport Mobile Lending"));

    $MyPage = new HTML_message("Rapports Mobile Lending");

    $demande_msg = "Voici la liste des rapports
        <br>
        <ul>
            <li>Crédits déboursés en cours</li>
            <li>Crédits en retard</li>
            <li>Crédits acceptés par agents</li>
            <li>Crédits refusés par agents</li>
            <li>Crédits en perte</li>
            <li>Crédits soldés</li>
        </ul>
    ";


    $MyPage->setMessage(sprintf(" <br />%s <br /> ", $demande_msg));

    $MyPage->addButton("BUTTON_OK", 'Mlr-2');

    //HTML
    $MyPage->buildHTML();
    echo $MyPage->getHTML();
}

else if ($global_nom_ecran == "Mlr-2"){

    // RAPPORT --> Tous les crédits déboursés
    $mobile_dataset_1 = getMobileLendingData("2, 5", "");
    $xml_dataset_1 = xml_mobile_lending($mobile_dataset_1, 'MLR-DEB');

    $fichier_1 = xml_2_csv_ml($xml_dataset_1, 'rapport_analytics_mobile_lending.xslt');

    // RAPPORT -->  Crédits en retard
    $mobile_dataset_2 = getMobileLendingData("2, 5", "2, 3, 4");
    $xml_dataset_2 = xml_mobile_lending($mobile_dataset_2, 'MLR-RET');

    $fichier_2 = xml_2_csv_ml($xml_dataset_2, 'rapport_analytics_mobile_lending.xslt');


    // RAPPORT -->  Crédits acceptés par agents
    $mobile_dataset_3 = getMobileLendingData("5", "");
    $xml_dataset_3 = xml_mobile_lending($mobile_dataset_3, 'MLR-ACP');

    $fichier_3 = xml_2_csv_ml($xml_dataset_3, 'rapport_analytics_mobile_lending.xslt');


    //RAPPORT --> Crédits refusés par agents
    $mobile_dataset_4 = getMobileLendingData("6", "");
    $xml_dataset_4 = xml_mobile_lending($mobile_dataset_4, 'MLR-REF');

    $fichier_4 = xml_2_csv_ml($xml_dataset_4, 'rapport_analytics_mobile_lending.xslt');

    // RAPPORT --> Crédits en perte
    $id_perte = getIDEtatPerte();
    $mobile_dataset_5 = getMobileLendingData("2, 5", $id_perte);
    $xml_dataset_5 = xml_mobile_lending($mobile_dataset_5, 'MLR-PER');

    $fichier_5 = xml_2_csv($xml_dataset_5, 'rapport_analytics_mobile_lending.xslt');

    // RAPPORT --> Crédits soldés
    $mobile_dataset_6 = getMobileLendingData("3", "");
    $xml_dataset_6 = xml_mobile_lending($mobile_dataset_6, 'MLR-SOL');

    $fichier_6 = xml_2_csv_ml($xml_dataset_6, 'rapport_analytics_mobile_lending.xslt');

    echo getShowEXCELHTMLML("Gen-17", array(
        "Crédits_déboursés_en_cours" => $fichier_1,
        "Crédits_en_retard" => $fichier_2,
        "Crédits_acceptés_par_agents" => $fichier_3,
        "Crédits_refusés par_agents" => $fichier_4,
        "Crédits en perte" => $fichier_5,
        "Crédits_soldés" => $fichier_6));
}

?>