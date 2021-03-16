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

if ($global_nom_ecran == "Lci-1"){
    $MyPage = new HTML_GEN2(_("Liste des commandes de cartes"));
    $MyPage->addHiddenType("serialized_data");

    $cartes_atm = getCarteATM(" etat_carte = 1");
    if (sizeof($cartes_atm) == 0){
        $erreur = new HTML_erreur(_("Commande de cartes indisponible"));
        $erreur->setMessage(_("Il n'y a pas de cartes pour impressions"));
        $erreur->addButton(BUTTON_OK,"Gca-1");
        $erreur->buildHTML();
        echo $erreur->HTML_code;
        $ok = false;
        exit();
    }

    $table = "<script src=\"/lib/misc/js/lib/jquery.min.js\" type=\"text/javascript\"></script>";
    $table .= "<table align=\"center\" cellpadding=\"5\" width=\"95% \" bgcolor=\"#FDF2A6\"  style='margin-bottom: 50px;' id='liste_commande_carte'>
                    <thead>
                        <tr>
                            <th>Id carte</th>
                            <th>Date demande</th>
                            <th>Id client</th>
                            <th>Numéro de compte</th>
                            <th>Nom</th>
                            <th>ID</th>
                            <th>Prestataire</th>
                            <th>Motif demande</th>
                            <th>Envoi impression ?</th>
                        </tr>
                    <thead>  
                    <tbody>";

    foreach($cartes_atm as $carte_atm){
        $client_info = getClientDatas($carte_atm['id_client']);
        $epargne_info = getAccountDatas($carte_atm['id_cpte']);
        $table .= "<tr bgcolor='$color'>";
        $table .= "<td align=\"center\">".$carte_atm['id_carte']."</td>";
        $table .= "<td align=\"center\">".$carte_atm['date_demande']."</td>";
        $table .= "<td align=\"center\">".$carte_atm['id_client']."</td>";
        $table .= "<td align=\"center\">".$epargne_info['num_complet_cpte']."</td>";

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

        $table .= "<td align=\"center\">".$nom."</td>";
        $table .= "<td align=\"center\">".$carte_atm['id_export']."</td>";
        $data_prestataire = getPrestataireATM($carte_atm['id_prestataire']);
        $table .= "<td align=\"center\">".$data_prestataire['nom_prestataire']."</td>";
        $table .= "<td align=\"center\">".$adsys["motif_demande"][$carte_atm['motif_demande']]."</td>";
        $table .= "<td align=\"center\" width='10%'><input type='checkbox' checked name='checkbox_".$carte_atm['id_carte']."' ></td>";
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
    $MyPage->addFormButton(1, 4, "checkbox_state", _("Cocher/ Décocher tous"), TYPB_BUTTON);
    $MyPage->setFormButtonProperties("checkbox_state", BUTP_JS_EVENT, array('onclick' => 'changeCheckBoxState()'));

    $js_alternate = "
        $('#liste_commande_carte').data('allChecked', true);
        $('#liste_commande_carte').find('th, td').css('border', '2px solid #fff');
        $('#liste_commande_carte').find('tr:nth-child(even)').css('background-color','#f2f2f2');
        
        function changeCheckBoxState(){
            var curr_state = $('#liste_commande_carte').data('allChecked');
            $('#liste_commande_carte').find('[name*=checkbox]').attr('checked', !$('#liste_commande_carte').data('allChecked'));
            $('#liste_commande_carte').data('allChecked', !$('#liste_commande_carte').data('allChecked'));
        }
        
        function serializeTable(){
            var data = [];
            
            var row = [];
            $('#liste_commande_carte').find('th').each(function(index, value){
                row.push($(value).text());
            });
            
            row.pop();
            data.push(row);
            
            $('#liste_commande_carte').find('tr').each(function(index, value){
                var row = [];
                var checkbox = $(value).find('[name *= checkbox_]');
                if(($(checkbox).length > 0) && $(checkbox).is(':checked')){
                    console.log($(this));
                    $(value).find('td').each(function(cell_index, cell_value){
                        row.push($(cell_value).text());
                    });
                }
                row.pop();
                data.push(row);
            });
            
            $('[name~=serialized_data]').val(JSON.stringify(data));
            
            //set next screen and submit form
            assign('Lci-2');
            document.ADForm.submit();
        }
    ";

    $MyPage->addHTMLExtraCode("xtHTML", $table);
    $MyPage->addJS(JSP_FORM, "table_alternate", $js_alternate);
    $MyPage->buildHTML();
    echo $MyPage->getHTML();
}

else if ($global_nom_ecran == "Lci-2"){
    $serialized_table_data = array_filter(json_decode($serialized_data, true), function($value){
        return !empty($value);
    });

    $new_field = array('Réference', 'Numéro carte', 'Date début validité', 'Date expiration');

    foreach($new_field as $field){
        $serialized_table_data[0][] = $field;
    }

    $checked_references = array_filter(array_keys($_POST), function($value){
        return (strpos($value, "checkbox_") === FALSE)?FALSE:TRUE;
    });

    foreach($checked_references as $key => $checked_reference){
        $parts = explode("_", $checked_reference);
        $checked_references[$key] = "'".$parts[count($parts) - 1]."'";
    }
    $err = exportImpressionCarte(implode(',', $checked_references), $serialized_table_data);
    echo getShowCSVHTML("Gca-1", $err->datas);
}