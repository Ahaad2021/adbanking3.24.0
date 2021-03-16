
<?php
/* vim: set expandtab softtabstop=2 shiftwidth=2 foldmethod=marker: */

require_once 'lib/misc/VariablesGlobales.php';
require_once 'lib/misc/VariablesSession.php';
require_once 'lib/misc/Erreur.php';
require_once 'lib/dbProcedures/agence.php';
require_once 'lib/dbProcedures/abonnement.php';
require_once 'lib/dbProcedures/carte_atm.php';
require_once 'lib/misc/access.php';
require_once 'lib/misc/divers.php';
include_once 'lib/misc/debug.php';
include_once 'lib/dbProcedures/bdlib.php';


if ($global_nom_ecran == "Gcs-1")  {
    global $global_id_client;

    $cartes_suspendues_desactivee = getCarteSuspenduDesactivee($global_id_client);

    if (sizeof($cartes_suspendues_desactivee) == 0){
        $erreur = new HTML_erreur(_("Pas de cartes suspendues/desactivées"));
        $erreur->setMessage(_("Vous ne possedez pas de cartes suspendues/desactivées"));
        $erreur->addButton(BUTTON_OK,"Gen-4");
        $erreur->buildHTML();
        echo $erreur->HTML_code;
        $ok = false;
        exit();
    }

    $MyPage = new HTML_GEN2(_("Cartes suspendues/désactivées"));

    $table = "<script src=\"/lib/misc/js/lib/jquery.min.js\" type=\"text/javascript\"></script>";
    $table .= "<table align=\"center\" cellpadding=\"5\" border=\"1\" cellspacing=\"2\" width=\"95% \" bgcolor=\"#FDF2A6\"  style='margin-bottom: 50px;' id='liste_commande_carte'>
                    <thead>
                        <tr>
                            <th>Réference</th>
                            <th>Date Suspension</th>
                            <th>Nom</th>
                            <th>Prénom</th>
                            <th>Numéro de compte</th>
                            <th>Numéro carte ATM</th>
                            <th>Prestataire</th>
                            <th>Motif </th>
                            <th>Action</th>
                        </tr>
                    <thead>  
                    <tbody>";
    $prochain_ecran = "Gcs-2";
    foreach($cartes_suspendues_desactivee as $data){
        $table .= "<tr>";
        $table .= "<td align=\"center\" >".$data["ref_no"]."</td>";
        $table .= "<td align=\"center\" >".$data["date_suspension"]."</td>";
        $data_cli = getClientDatas($global_id_client);
        $table .= "<td align=\"center\" >".$data_cli["pp_nom"]."</td>";
        $table .= "<td align=\"center\" >".$data_cli["pp_prenom"]."</td>";
        $data_cpte = getAccountDatas($data["id_cpte"]);
        $table .= "<td align=\"center\" >".$data_cpte["num_complet_cpte"]."</td>";
        $table .= "<td align=\"center\" >".$data["num_carte_atm"]."</td>";
        $data_prestataire = getPrestataireATM($data["id_prestataire"]);
        $table .= "<td align=\"center\" >".$data_prestataire['nom_prestataire']."</td>";
        $table .= "<td align=\"center\" >".adb_gettext($adsys['motif_inactif'][$data['motif_suspension']])."</td>";
        $table .= "<td align=\"center\" ><a href=".$PHP_SELF."?m_agc=".$_REQUEST['m_agc']."&prochain_ecran=".$prochain_ecran."&id_carte=".$data['id_carte'].">Effectuer action</a></td>";

    }


    $table .=  "</tbody>
                    </table>";

    $MyPage->addFormButton(1, 1, "valider", _("Valider"), TYPB_BUTTON);
    $MyPage->setFormButtonProperties("valider", BUTP_JS_EVENT, array('onclick' => 'serializeTable()'));
    $MyPage->addFormButton(1, 2, "retour", _("Précédent"), TYPB_SUBMIT);
    $MyPage->setFormButtonProperties("retour", BUTP_PROCHAIN_ECRAN, "Gen-4");
    $MyPage->addFormButton(1, 3, "annuler", _("Annuler"), TYPB_SUBMIT);
    $MyPage->setFormButtonProperties("annuler", BUTP_PROCHAIN_ECRAN, "Gen-4");
    $MyPage->addJS(JSP_FORM, "serialized_table", $js_table_array);
    $MyPage->addHTMLExtraCode("xtHTML", $table);
    $MyPage->buildHTML();
    echo $MyPage->getHTML();


}
else if ($global_nom_ecran == "Gcs-2")  {

    $id_carte = $id_carte;

    $data_carte = getCarteATM('id_carte = '.$id_carte);
    $data_prestataire = getPrestataireATM($data_carte[0]["id_prestataire"]);
    $data_cpte = getAccountDatas($data_carte[0]['id_cpte']);
    // Création du formulaire
    $my_page = new HTML_GEN2(_("Gestion des cartes suspendues : Informations de la carte"));

    $my_page->addHiddenType("id_carte", $id_carte);

    $my_page->addField("num_cpte", "Numéro du compte", TYPC_TXT);
    $my_page->setFieldProperties("num_cpte", FIELDP_DEFAULT, $data_cpte['num_complet_cpte']);
    $my_page->setFieldProperties("num_cpte", FIELDP_IS_LABEL,true) ;


    $my_page->addField("num_carte", "Numéro de la carte", TYPC_TXT);
    $my_page->setFieldProperties("num_carte", FIELDP_DEFAULT, $data_carte[0]['num_carte_atm']);
    $my_page->setFieldProperties("num_carte", FIELDP_IS_LABEL,true) ;


    $my_page->addField("motif_suspension", "Motif de la suspension", TYPC_TXT);
    $my_page->setFieldProperties("motif_suspension", FIELDP_DEFAULT, adb_gettext($adsys['motif_inactif'][$data_carte[0]['motif_suspension']]));
    $my_page->setFieldProperties("motif_suspension", FIELDP_IS_LABEL,true) ;

    $my_page->addField("prestataire", "Prestataire", TYPC_TXT);
    $my_page->setFieldProperties("prestataire", FIELDP_DEFAULT, $data_prestataire['nom_prestataire']);
    $my_page->setFieldProperties("prestataire", FIELDP_IS_LABEL,true) ;

    $my_page->addField("etat_carte", "Etat actuelle carte", TYPC_TXT);
    $my_page->setFieldProperties("etat_carte", FIELDP_DEFAULT, adb_gettext($adsys['etat_carte_atm'][$data_carte[0]['etat_carte']]));
    $my_page->setFieldProperties("etat_carte", FIELDP_IS_LABEL,true) ;

    $choix_etat = array(1=>"Re-activer" , 2 => "Supprimer");
    $my_page->addField("new_etat", "Action carte", TYPC_LSB);
    $my_page->setFieldProperties("new_etat", FIELDP_ADD_CHOICES, $choix_etat);
    $my_page->setFieldProperties("new_etat",FIELDP_IS_REQUIRED, true);

    $my_page->addFormButton(1, 1, "valider", _("Valider"), TYPB_SUBMIT);
    $my_page->setFormButtonProperties("valider", BUTP_PROCHAIN_ECRAN, "Gcs-3");
    $my_page->addFormButton(1, 2, "annuler", _("Annuler"), TYPB_SUBMIT);
    $my_page->setFormButtonProperties("annuler", BUTP_PROCHAIN_ECRAN, "Gen-4");

    $my_page->buildHTML();
    echo $my_page->getHTML();
}

else if ($global_nom_ecran == "Gcs-3")  {


    $new_etat_carte = $new_etat;
    $data_carte = getCarteATM('id_carte = '.$id_carte);
    $data_abonnement = getAbonnementCarteAtm($data_carte[0]['id_client'],$id_carte);

    if ($new_etat_carte == 1){
        if (sizeof($data_abonnement) > 0){
            $array_carte = array('etat_carte' => 5, 'date_modif' => date('r'));
            $array_carte_condi = array('id_carte' => $id_carte);
            $update_ad_carte_atm = UpdateTable("ad_carte_atm",$array_carte,$array_carte_condi);

            $array_abo = array('statut' => 2, 'date_modif' => date('r'));
            $array_abo_condi = array('id_abonnement' => $data_abonnement['id_abonnement']);
            $update_ad_abonnement_atm = UpdateTable("ad_abonnement_atm",$array_abo,$array_abo_condi);

            $my_page = new HTML_message(_("Confirmation d'activation de la carte"));
            $my_page->setMessage("Votre carte ATM a été activé à nouveau", true);
            $my_page->addButton("BUTTON_OK", 'Gen-4');
            //HTML
            $my_page->buildHTML();
            echo $my_page->getHTML();

        }
    }else{
        $array_carte = array('etat_carte' => 9,'date_desactivation' => date('r'), 'date_modif' => date('r'));
        $array_carte_condi = array('id_carte' => $id_carte);
        $update_ad_carte_atm = UpdateTable("ad_carte_atm",$array_carte,$array_carte_condi);

        $my_page = new HTML_message(_("Confirmation de supression de la carte"));
        $my_page->setMessage("Votre carte ATM a été supprimé", true);
        $my_page->addButton("BUTTON_OK", 'Gen-4');
        //HTML
        $my_page->buildHTML();
        echo $my_page->getHTML();
    }
}
?>