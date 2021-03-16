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


if ($global_nom_ecran == "Ldc-1"){
    global $adsys;
    $MyPage = new HTML_GEN2(_(" Critères de recherche"));

    $MyPage->addField("etat_carte", _("Etat carte"), TYPC_LSB );
    $MyPage->setFieldProperties("etat_carte", FIELDP_ADD_CHOICES, $adsys['etat_carte_atm']);
    $MyPage->setFieldProperties("etat_carte", FIELDP_HAS_CHOICE_AUCUN, false);
    $MyPage->setFieldProperties("etat_carte", FIELDP_HAS_CHOICE_TOUS, true);

    $MyPage->addField("motif_demande", _("Motif demande"), TYPC_LSB );
    $MyPage->setFieldProperties("motif_demande", FIELDP_ADD_CHOICES, $adsys['motif_demande']);
    $MyPage->setFieldProperties("motif_demande", FIELDP_HAS_CHOICE_AUCUN, false);
    $MyPage->setFieldProperties("motif_demande", FIELDP_HAS_CHOICE_TOUS, true);

    $MyPage->addFormButton(1, 1, "valider", _("Chercher"), TYPB_SUBMIT);
    $MyPage->setFormButtonProperties("valider", BUTP_PROCHAIN_ECRAN, "Ldc-2");
    $MyPage->addFormButton(1, 2, "annuler", _("Annuler"), TYPB_SUBMIT);
    $MyPage->setFormButtonProperties("annuler", BUTP_PROCHAIN_ECRAN, "Gca-1");
    $MyPage->setFormButtonProperties("annuler", BUTP_CHECK_FORM, false);

    $MyPage->buildHTML();
    echo $MyPage->getHTML();
}else if($global_nom_ecran == "Ldc-2"){
    global $adsys;

    $MyPage = new HTML_GEN2(_("Liste de toutes les cartes"));
    $MyPage->addHiddenType("serialized_data");

    $etat_carte = empty($etat_carte)?"etat_carte":$etat_carte;
    $motif_demande = empty($motif_demande)?"motif_demande":$motif_demande;
    $carte_dataset = getCarteATM(" etat_carte = $etat_carte AND motif_demande = $motif_demande");

    $table = "<script src=\"/lib/misc/js/lib/jquery.min.js\" type=\"text/javascript\"></script>";
    $table .= "<table align=\"center\" cellpadding=\"5\" border=\"1\" cellspacing=\"2\" width=\"95% \" bgcolor=\"#FDF2A6\"  style='margin-bottom: 50px;' id='liste_commande_carte'>
                    <thead>
                        <tr>
                            <th>Réference</th>
                            <th>Nom</th>
                            <th>Prestataire</th>
                            <th>Numéro de compte</th>
                            <th>Etat carte</th>
                            <th>Numéro carte ATM</th>
                            <th>Motif demande</th>
                            <th>Date carte expiration</th>
                        </tr>
                    <thead>  
                    <tbody>";

    foreach($carte_dataset as $carte){
        $client_info = getClientDatas($carte['id_client']);
        $epargne_info = getAccountDatas($carte['id_cpte']);

        $table .= "<tr>";
        $table .= "<td align=\"center\" >".$carte["ref_no"]."</td>";

        $nom = "";
        switch ($client_info['statut_juridique']) {
            case 1 :
                $nom = $client_info['pp_nom'] . " " . $client_info['pp_prenom'];
                break;
            case 2 :
                $nom = $client_info['pm_raison_sociale'];
                break;
            case 3 :
                $nom = $client_info['gi_nom'];
                break;
            case 4 :
                $nom = $client_info['gi_nom'];
                break;
            default :
                signalErreur(__FILE__, __LINE__, __FUNCTION__, "Statut juridique inconnu");
        }

        $table .= "<td align=\"center\" >".$nom."</td>";
        $data_prestataire = getPrestataireATM($carte['id_prestataire']);
        $table .= "<td align=\"center\" >".$data_prestataire['nom_prestataire']."</td>";
        $table .= "<td align=\"center\" >".$epargne_info['num_complet_cpte']."</td>";
        $table .= "<td align=\"center\" >".$adsys['etat_carte_atm'][$carte['etat_carte']]."</td>";
        $array_split = str_split($carte['num_carte_atm'],4);
        $num_atm = implode(" ",$array_split);
        $table .= "<td align=\"center\" >".$num_atm."</td>";
        $table .= "<td align=\"center\" >".$adsys['motif_demande'][$carte['motif_demande']]."</td>";
        $table .= "<td align=\"center\" >".pg2phpDate($carte['date_carte_expiration'])."</td>";
        $table .= "</tr>";
    }

    $table .=  "</tbody>
                    </table>";

    $MyPage->addFormButton(1, 1, "valider", _("Valider"), TYPB_BUTTON);
    $MyPage->setFormButtonProperties("valider", BUTP_JS_EVENT, array('onclick' => 'serializeTable()'));
    $MyPage->addFormButton(1, 2, "retour", _("Précédent"), TYPB_SUBMIT);
    $MyPage->setFormButtonProperties("retour", BUTP_PROCHAIN_ECRAN, "Gca-1");
    $MyPage->addFormButton(1, 3, "annuler", _("Annuler"), TYPB_SUBMIT);
    $MyPage->setFormButtonProperties("annuler", BUTP_PROCHAIN_ECRAN, "Gca-1");

    $js_table_array= "
        function serializeTable(){
            var data = [];
            
            var row = [];
            $('#liste_commande_carte').find('th').each(function(index, value){
                row.push($(value).text());
            });
            
            data.push(row);
            
            $('#liste_commande_carte').find('tr').each(function(index, value){
                var row = [];
                $(value).find('td').each(function(cell_index, cell_value){
                    row.push($(cell_value).text());
                });
                data.push(row);
            });
            
            $('[name~=serialized_data]').val(JSON.stringify(data));
            
            //set next screen and submit form
            assign('Ldc-3');
            document.ADForm.submit();
        }
    ";

    $MyPage->addJS(JSP_FORM, "serialized_table", $js_table_array);
    $MyPage->addHTMLExtraCode("xtHTML", $table);
    $MyPage->buildHTML();
    echo $MyPage->getHTML();
}
else if($global_nom_ecran == "Ldc-3"){
    $serialized_table_data = array_filter(json_decode($serialized_data, true), function($value){
        return !empty($value);
    });

    $err = exportImpressionCarte("ref_no", $serialized_table_data);
    echo getShowCSVHTML("Gca-1", $err->datas);
}