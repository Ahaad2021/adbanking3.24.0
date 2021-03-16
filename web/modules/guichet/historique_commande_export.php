<?php
require_once('lib/dbProcedures/epargne.php');
require_once('lib/dbProcedures/carte_atm.php');
require_once('lib/dbProcedures/client.php');
require_once('lib/dbProcedures/parametrage.php');
require_once 'lib/dbProcedures/guichet.php';
require_once 'lib/html/HTML_GEN2.php';
require_once 'lib/html/FILL_HTML_GEN2.php';
require_once 'lib/html/HTML_erreur.php';
require_once 'lib/misc/VariablesGlobales.php';
require_once 'lib/dbProcedures/agence.php';
require_once 'lib/misc/divers.php';
require_once 'lib/misc/tableSys.php';
require_once "lib/html/HTML_menu_gen.php";
require_once 'lib/dbProcedures/historique.php' ;
require_once 'lib/misc/csv.php';

if ($global_nom_ecran == "Hic-1") {
    $MyPage = new HTML_GEN2(_("Historique des commandes envoyées"));

    $commande_historique = getCommandeCarteHistorique();
    $MyPage->addHiddenType("download_path");

    $table = "<script src=\"/lib/misc/js/lib/jquery.min.js\" type=\"text/javascript\"></script>";
    $table .= "<table align=\"center\" cellpadding=\"5\" border=\"1\" cellspacing=\"2\" width=\"75% \" bgcolor=\"#FDF2A6\" id='historique_des_commande' style='margin-bottom:50px;'>
                    <thead>
                        <tr>
                            <th>Date de traitement</th>
                            <th>Nom interne</th>
                            <th>Nombre de carte</th>
                            <th>Date creation</th>
                            <th>Lien</th>
                        </tr>
                    <thead>  
                    <tbody>";

    foreach($commande_historique as $commande){
        $file_path = $commande['chemin_fichier'];
        $table .= "<tr>";
        $table .= "<td align=\"center\">".$commande['date_traitement']."</td>";
        $table .= "<td align=\"center\">".$commande['nom_interne']."</td>";
        $table .= "<td align=\"center\">".$commande['nbre_cartes']."</td>";
        $table .= "<td align=\"center\">".$commande['date_traitement']."</td>";
        $table .= "<td align=\"center\" ><a href='#' data-file-path='$file_path' data-type='download'>Imprimer</a></td>";
        $table .= "</tr>";
    }

    $table .=  "</tbody>
                    </table>";

    $js_download_trigger = "
         $('[data-type=download]').click(function(e){
            console.log($(this));
            e.preventDefault();
            $('[name~=download_path]').val($(this).attr('data-file-path'));
            assign('Hic-2');
            document.ADForm.submit();
        });
    ";

    $MyPage->addFormButton(1, 1, "retour", _("Annuler"), TYPB_SUBMIT);
    $MyPage->setFormButtonProperties("retour", BUTP_PROCHAIN_ECRAN, "Gca-1");

    $MyPage->addJS(JSP_FORM, "download_file", $js_download_trigger);
    $MyPage->addHTMLExtraCode("xtHTML", $table);
    $MyPage->buildHTML();
    echo $MyPage->getHTML();
}
else if($global_nom_ecran == "Hic-2"){
    echo getShowCSVHTML("Gca-1", $download_path);
}
?>