<?php
require_once('lib/dbProcedures/epargne.php');
require_once('lib/dbProcedures/client.php');
require_once('lib/dbProcedures/parametrage.php');
require_once 'lib/dbProcedures/guichet.php';
require_once 'lib/dbProcedures/carte_atm.php';
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

if ($global_nom_ecran == "Ici-1"){

    if ($_POST['list_agence']=="" && $SESSION_VARS['select_agence']=="")
        $SESSION_VARS['select_agence']=$global_id_agence;
    elseif($SESSION_VARS['select_agence']=="")
        $SESSION_VARS['select_agence']=$_POST['list_agence'];
    setGlobalIdAgence($SESSION_VARS['select_agence']);


    if (file_exists($fichier_lot)) {
        $filename = $fichier_lot.".tmp";
        move_uploaded_file($fichier_lot, $filename);
        exec("chmod a+r ".escapeshellarg($filename));
        $SESSION_VARS['fichier_lot'] = $filename;
    } else {
        $SESSION_VARS['fichier_lot'] = NULL;
    }


    $titre=_("Récupération du fichier pour l'importation des cartes ATM");
    // $titre.=" ".adb_gettext($adsys["adsys_rapport_BNR"][$SESSION_VARS['type_rapport']]);
    $MyPage = new HTML_GEN2($titre);

    $htm1 = "<P align=\"center\">"._("Fichier de données").": <INPUT name=\"fichier_lot\" type=\"file\" /></P>";
    $htm1 .= "<P align=\"center\"> <INPUT type=\"submit\" value=\"Envoyer\" onclick=\"document.ADForm.m_agc.value='".$_REQUEST['m_agc']."';document.ADForm.prochain_ecran.value='Ici-1';\"/> </P>";
    $htm1 .= "<BR/>";

    $MyPage->addHTMLExtraCode("htm1", $htm1);

    $MyPage->AddField("statut", _("Statut"), TYPC_TXT);
    $MyPage->setFieldProperties("statut", FIELDP_IS_LABEL, true);

    if ($SESSION_VARS['fichier_lot'] == NULL) {
        $MyPage->setFieldProperties("statut", FIELDP_DEFAULT, _("Fichier non reçu"));
    } else {
        $MyPage->setFieldProperties("statut", FIELDP_DEFAULT, _("Fichier reçu"));
    }

    $MyPage->addHTMLExtraCode("htm2", "<BR>");

    $MyPage->addFormButton(1, 1, "valider", _("Valider"), TYPB_SUBMIT);
    $MyPage->addFormButton(1, 2, "annuler", _("Annuler"), TYPB_SUBMIT);

    $MyPage->setFormButtonProperties("valider", BUTP_PROCHAIN_ECRAN, 'Ici-2');
    $MyPage->setFormButtonProperties("annuler", BUTP_PROCHAIN_ECRAN, 'Gen-6');
    $MyPage->setFormButtonProperties("annuler", BUTP_CHECK_FORM, false);

    $MyPage->buildHTML();
    echo $MyPage->getHTML();


}

else if ($global_nom_ecran == "Ici-2"){

    global $adsys;
    $MyErr=parse_ajout_carte_atm_imprimer_fichier($SESSION_VARS['fichier_lot']);
    if (sizeof($MyErr->datas['data'])== 0){
        if (sizeof($MyErr->datas['data_failed']) == 0) {
            $erreur = new HTML_erreur(_("Fichier vide!"));
            $erreur->setMessage(_("Il n'y a pas de données dans le fichier importé."));
            $erreur->addButton(BUTTON_OK, "Gca-1");
            $erreur->buildHTML();
            echo $erreur->HTML_code;
            $ok = false;
            exit();
        }
    }
    $SESSION_VARS['counter_failed'] = $MyErr->datas['counter_failed'];
    $SESSION_VARS['counter_success'] = $MyErr->datas['counter_success'];
    if (sizeof($MyErr->datas['data_failed']) > 0) {
        $array_failed = array_merge($MyErr->datas['data_failed'], array($MyErr->datas['header']));
        krsort($array_failed);
        $SESSION_VARS['data_failed'] = $array_failed;

    }
    if (sizeof($MyErr->datas['data']) > 0) {

    $MyPage = new HTML_GEN2(_("Liste des commandes de cartes"));
    $MyPage->addHiddenType("serialized_data");

    $table = "<script src=\"/lib/misc/js/lib/jquery.min.js\" type=\"text/javascript\"></script>";
    $table .= "<table align=\"center\" cellpadding=\"5\" width=\"95% \" bgcolor=\"#FDF2A6\"  style='margin-bottom: 50px;' id='liste_commande_carte'>
                    <thead>
                        <tr>
                            <th>ID carte</th>
                            <th>Date demande</th>
                            <th>Id client</th>
                            <th>Numéro de compte</th>
                            <th>Nom</th>
                            <th>ID</th>
                            <th>Prestataire</th>
                            <th>Motif demande</th>                            
                            <th>Réference</th>
                            <th>Numéro carte</th>
                            <th>Date début validité</th>
                            <th>Date expiration</th>
                            <th>Valider la carte?</th>
                        </tr>
                    <thead>  
                    <tbody>";
$array_idCarte = array();
$count = 0;

    foreach ($MyErr->datas['data'] as $key => $value) {
        $array_idCarte[$key]['id_carte'] = $value['id_carte'];
        $array_idCarte[$key]['id_client'] = $value['id_client'];
        $array_idCarte[$key]['num_carte_atm'] = $value['num_carte_atm'];
        $array_idCarte[$key]['date_carte_debut_validite'] = $value['date_carte_debut_validite'];
        $array_idCarte[$key]['date_carte_expiration'] = $value['date_carte_expiration'];
        $array_idCarte[$key]['ref_no'] = $value['reference'];
        $array_idCarte[$key]['passed'] = $value['passed'];
        $table .= "<tr bgcolor='$color'>";
        $table .= "<td align=\"center\">" . $value['id_carte'] . "</td>";
        $table .= "<td align=\"center\">" . $value['date_demande'] . "</td>";
        $table .= "<td align=\"center\">" . $value['id_client'] . "</td>";
        $table .= "<td align=\"center\">" . $value['num_cpte'] . "</td>";
        $table .= "<td align=\"center\">" . $value['nom'] . "</td>";
        $table .= "<td align=\"center\">" . $value['id'] . "</td>";
        $table .= "<td align=\"center\">" . $value['prestataire'] . "</td>";
        $table .= "<td align=\"center\">" . $value["motif_demande"] . "</td>";
        $table .= "<td align=\"center\">" . $value['reference'] . "</td>";
        $array_split = str_split($value['num_carte_atm'], 4);
        $num_atm = implode(" ", $array_split);
        $table .= "<td align=\"center\">" . $num_atm . "</td>";
        $table .= "<td align=\"center\">" . $value['date_carte_debut_validite'] . "</td>";
        $table .= "<td align=\"center\">" . $value['date_carte_expiration'] . "</td>";
        if ($value['passed'] == true) {
            $data_carte_actif = getCarteATM("num_carte_atm = '" . $value['num_carte_atm'] . "' ");
            if (sizeof($data_carte_actif) > 0) {
                $table .= "<td align=\"center\"><p style=\"color:red;\">Numéro existant</p></td>";
            } else {
                $table .= "<td align=\"center\" width='10%'><input type='checkbox' checked name='checkbox_" . $value['id_carte'] . "'></td>";
                $count++;
            }
        } else {
            if (isBefore($value['date_carte_expiration'], $value['date_carte_debut_validite'])) {
                $table .= "<td align=\"center\"><p style=\"color:red;\">Date expiration erronée</p></td>";
            } else {
                $table .= "<td align=\"center\"><p style=\"color:red;\">Données manquantes/erronées</p></td>";
            }
        }
        $table .= "</tr>";
    }
}
else {
    $MyPage = new HTML_GEN2(_("Fichier imcomplet!"));
    //$MyPage = new HTML_erreur(_("Fichier imcomplet!"));
    $MyPage->setTitle(_("Le fichier est vide ou incomplet."));

}
$SESSION_VARS['id_carte_import']=$array_idCarte;
    $table .=  "</tbody>
                    </table>";

    if ($count > 0 || $SESSION_VARS['counter_failed'] > 0) {
        if ($count > 0){
            $MyPage->addFormButton(1, 1, "valider", _("Valider"), TYPB_SUBMIT);
            $MyPage->setFormButtonProperties("valider", BUTP_PROCHAIN_ECRAN, "Ici-3");
        }else{
            $MyPage->addFormButton(1, 1, "valider", _("Export des données erronées"), TYPB_SUBMIT);
            $MyPage->setFormButtonProperties("valider", BUTP_PROCHAIN_ECRAN, "Ici-3");
        }

        $MyPage->addFormButton(1, 2, "retour", _("Précédent"), TYPB_SUBMIT);
        $MyPage->setFormButtonProperties("retour", BUTP_PROCHAIN_ECRAN, "Gca-1");
        $MyPage->addFormButton(1, 3, "annuler", _("Annuler"), TYPB_SUBMIT);
        $MyPage->setFormButtonProperties("annuler", BUTP_PROCHAIN_ECRAN, "Gca-1");
        if ($count > 0) {
            $MyPage->addFormButton(1, 4, "checkbox_state", _("Cocher/ Décocher tous"), TYPB_BUTTON);
            $MyPage->setFormButtonProperties("checkbox_state", BUTP_JS_EVENT, array('onclick' => 'changeCheckBoxState()'));
        }
    }else{
        $MyPage->addFormButton(1, 1, "retour", _("Précédent"), TYPB_SUBMIT);
        $MyPage->setFormButtonProperties("retour", BUTP_PROCHAIN_ECRAN, "Gca-1");
        $MyPage->addFormButton(1, 2, "annuler", _("Annuler"), TYPB_SUBMIT);
        $MyPage->setFormButtonProperties("annuler", BUTP_PROCHAIN_ECRAN, "Gca-1");
    }


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
                $(value).find('td').each(function(cell_index, cell_value){
                    row.push($(cell_value).text());
                });
                row.pop();
                data.push(row);
            });
            
            $('[name~=serialized_data]').val(JSON.stringify(data));
            
            //set next screen and submit form
            assign('Ici-3');
            document.ADForm.submit();
        }
      
    ";
    $MyPage->addHTMLExtraCode("xtHTML", $table);
    $MyPage->addJS(JSP_FORM, "table_alternate", $js_alternate);
    $MyPage->buildHTML();
    echo $MyPage->getHTML();

}

else if ($global_nom_ecran == "Ici-3"){
    global $dbHandler, $global_id_agence;
    foreach ($SESSION_VARS['id_carte_import'] as $key => $value) {
        $data_abonnement = getAbonnementCarteAtm($value['id_client'],$value['id_carte']);
        if (sizeof($data_abonnement) < 1) {
            if ($value['passed'] == true){
                if (isset(${"checkbox_" . $value['id_carte']})) {
                    $array_update = array(
                        'etat_carte' => 3,
                        'date_livraison' => date('d/m/Y H:i:s'),
                        'date_modif' => date('d/m/Y H:i:s'),
                        'date_carte_debut_validite' => $value['date_carte_debut_validite'],
                        'date_carte_expiration' => $value['date_carte_expiration'],
                        'ref_no' => $value['ref_no'],
                        'num_carte_atm' => $value['num_carte_atm']
                    );
                    $array_condition = array(
                        'id_carte' => $value['id_carte']
                    );
                    $db = $dbHandler->openConnection();
                    $sql = buildUpdateQuery("ad_carte_atm", $array_update, $array_condition);

                    // Insertion dans la DB
                    $result = $db->query($sql);
                    if (DB:: isError($result)) {
                        $dbHandler->closeConnection(false);
                        return $result->errCode;
                    }
                    $dbHandler->closeConnection(true);
                }
            }
        }
    }

    if (isset($SESSION_VARS['data_failed'])) {
        // formation CSV failed
        $id = getNextSequence('ad_commande_carte_his_id_seq');
        $path_export = "/tmp/export_carte_atm/export_impression_carte_failed_" . date("dmY") . "_" . $id . ".csv";
        $filename = "export_impression_carte_failed_" . date("dmY") . "_" . $id . ".csv";
        $handle = fopen($path_export, 'w');
        foreach ($SESSION_VARS['data_failed'] as $value) {
            $csv_out = fputcsv($handle, $value, ',');
        }
        fclose($handle);
        $db = $dbHandler->openConnection();
        $ad_his_id = ajout_historique(808, NULL, NULL, $global_nom_login, date("r"), NULL);

        echo getShowCSVHTMLATM('Gca-1', $path_export);

        $DATA_INSERT['date_traitement'] = date('d/m/Y H:i:s');
        $DATA_INSERT['nom_interne'] = $filename;
        $DATA_INSERT['chemin_fichier'] = $path_export;
        $DATA_INSERT['nbre_cartes'] = $SESSION_VARS['counter_failed'];
        $DATA_INSERT['id_ag'] = $global_id_agence;
        $DATA_INSERT['id_his'] = $ad_his_id->param;

        $sql = buildInsertQuery("ad_commande_carte_his", $DATA_INSERT);
        $result = executeQuery($db, $sql);

        if (DB :: isError($result)) {
            $dbHandler->closeConnection(false);
            return $result->errCode;
        }

        $dbHandler->closeConnection(true);
        unset($SESSION_VARS['data_failed']);

        $myForm = new HTML_message(_("Confirmation de l'import des cartes ATM"));
        $msg = _("L'import des cartes s'est déroulé avec succès pour ".$SESSION_VARS['counter_success']." carte(s). ATTENTION, ".$SESSION_VARS['counter_failed']." carte(s) comporte(nt) des erreurs qui sont à verifier dans l'export ci-après.");
        $myForm->setMessage($msg);
        $myForm->addButton(BUTTON_OK, "Gca-1");
        $myForm->buildHTML();
        echo $myForm->HTML_code;

    }else {
        $myForm = new HTML_message(_("Confirmation de l'import des cartes ATM"));
        $msg = _("L'import des cartes ATM validées a été effectué");
        $myForm->setMessage($msg);
        $myForm->addButton(BUTTON_OK, "Gca-1");
        $myForm->buildHTML();
        echo $myForm->HTML_code;
    }
unset($SESSION_VARS['id_carte_import']);
unset($SESSION_VARS['fichier_lot']);

}