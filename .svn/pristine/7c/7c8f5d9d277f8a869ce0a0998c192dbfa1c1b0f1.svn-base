
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


if ($global_nom_ecran == "Caa-1")  {
    global $global_id_client;
    $liste_comptes = get_comptes_epargne_non_commander($global_id_client);
    $client = getClientDatas($global_id_client);
    $choix = array();
    if (isset($liste_comptes)) {
        foreach($liste_comptes as $id_cpte => $infos_cpte) {
            $infos_prod = getProdEpargne($infos_cpte['id_prod']);
            if ($infos_prod["classe_comptable"] == '1' && $infos_cpte["etat_cpte"] != '3') {
                // C'est un compte à vue, il n'est pas bloqué et aucune demande de chèquier n'est en cours
                $choix[$id_cpte] = $infos_cpte["num_complet_cpte"]." ".$infos_cpte["intitule_compte"];
            }
        }
    }
    $carte_deja_commander = getCarteATM('id_client = '.$global_id_client.' AND etat_carte = 1');
    $abonnement = getAbonnementATM();

    if (sizeof($carte_deja_commander) > 0){
        $erreur = new HTML_erreur(_("Commande déja en cours"));
        $erreur->setMessage(_("Vous avez déja effectué une commande. Veuillez attendre que votre carte soit imprimé"));
        $erreur->addButton(BUTTON_OK,"Gen-4");
        $erreur->buildHTML();
        echo $erreur->HTML_code;
        $ok = false;
        exit();
    }else if (sizeof($abonnement) == 0){
        $erreur = new HTML_erreur(_("Abonnement non existant"));
        $erreur->setMessage(_("Vous ne possedez pas d'abonnement!"));
        $erreur->addButton(BUTTON_OK,"Gen-4");
        $erreur->buildHTML();
        echo $erreur->HTML_code;
        $ok = false;
        exit();
    }
    else if (sizeof($choix) == 0){
        $erreur = new HTML_erreur(_("Carte active existante"));
        $erreur->setMessage(_("Vous possédez déja une carte activée! Veuillez attendre que votre carte actuelle ne soit plus active."));
        $erreur->addButton(BUTTON_OK,"Gen-4");
        $erreur->buildHTML();
        echo $erreur->HTML_code;
        $ok = false;
        exit();
    }
    // Création du formulaire
    $my_page = new HTML_GEN2(_("Commande de carte ATM : choix du compte d'épargne"));
    $my_page->addField("num_cpte", _("Numéro de compte"), TYPC_LSB);
    $my_page->setFieldProperties("num_cpte", FIELDP_ADD_CHOICES, $choix);
    $my_page->setFieldProperties("num_cpte",FIELDP_IS_REQUIRED, true);

    $prestataire = getAvailablePrestataire(false, 2);

    $my_page->addField("type_prestataire", _("Type prestataire"), TYPC_LSB);
    $my_page->setFieldProperties("type_prestataire", FIELDP_ADD_CHOICES, $prestataire);
    $my_page->setFieldProperties("type_prestataire", FIELDP_HAS_CHOICE_AUCUN, true);
    $my_page->setFieldProperties("type_prestataire", FIELDP_IS_REQUIRED, true);


    $my_page->addField("nom_cli", _("Nom"), TYPC_TXT);
    $my_page->setFieldProperties("nom_cli",FIELDP_DEFAULT, $client['pp_nom']);

    $my_page->addField("prenom_cli", _("Prénom"), TYPC_TXT);
    $my_page->setFieldProperties("prenom_cli",FIELDP_DEFAULT, $client['pp_prenom']);

    $my_page->addField("nom_carte", _("Nom sur la carte"), TYPC_TXT);
    $my_page->setFieldProperties("nom_carte",FIELDP_IS_REQUIRED,true);
    $my_page->setFieldProperties("nom_carte",FIELDP_DEFAULT, $client['pp_nom']." ".$client['pp_prenom']);

    $my_page->addField("etat_carte", _("Etat carte"), TYPC_TXT);
    $my_page->setFieldProperties("etat_carte",FIELDP_DEFAULT, adb_gettext($adsys["etat_carte_atm"][1]));

    $ancien_carte_exist = $data_carte_actif = getCarteATM('id_client = '.$global_id_client);
    if(sizeof($ancien_carte_exist) >0){
        $array_motif = $adsys["motif_demande"];
        unset($array_motif[1]);

    }else {
        $array_motif = $adsys["motif_demande"];unset($array_motif[2],$array_motif[3],$array_motif[4]);
    }

    $my_page->addField("motif_demande", _("Motif de la demande"), TYPC_LSB);
    $my_page->setFieldProperties("motif_demande", FIELDP_ADD_CHOICES,$array_motif);
    $my_page->setFieldProperties("motif_demande",FIELDP_IS_REQUIRED, true);

    $date_jour = date("d");
    $date_mois = date("m");
    $date_annee = date("Y");
    $date_total = $date_jour."/".$date_mois."/".$date_annee;
    $my_page->addField("date_demande", _("Date de la demande"), TYPC_TXT);
    $my_page->setFieldProperties("date_demande",FIELDP_DEFAULT, $date_total);
    $my_page->setFieldProperties("date_demande",FIELDP_IS_LABEL, true);


    $JS = "document.ADForm.etat_carte.readOnly = true ;document.ADForm.nom_cli.readOnly = true;  document.ADForm.prenom_cli.readOnly = true; document.ADForm.prenom_cli.readOnly = true;document.ADForm.date_demande.readOnly = true ;";

    $my_page->addJS(JSP_FORM,"check", $JS);
    // Boutons
    $my_page->addFormButton(1,1,"valid", _("Valider"), TYPB_SUBMIT);
    $my_page->setFormButtonProperties("valid", BUTP_PROCHAIN_ECRAN, "Caa-2");
    $my_page->addFormButton(1,2,"annul", _("Annuler"), TYPB_SUBMIT);
    $my_page->setFormButtonProperties("annul", BUTP_PROCHAIN_ECRAN, "Gen-4");
    $my_page->setFormButtonProperties("annul", BUTP_CHECK_FORM, false);

    //HTML
    $my_page->buildHTML();
    echo $my_page->getHTML();

}
else if ($global_nom_ecran == "Caa-2")  {
    global $global_id_agence, $global_id_client, $dbHandler;
    $my_page = new HTML_GEN2(_("Commande de carte ATM : Confirmation commande"));

    $array_insert = array(
        "id_client" => $global_id_client,
        "id_prestataire" => $type_prestataire,
        "id_cpte" => $num_cpte,
        "nom_sur_carte" => trim($nom_carte),
        "etat_carte" => 1,
        "motif_demande" => $motif_demande,
        "date_demande" => date('r'),
        "id_ag" => $global_id_agence,
        "date_creation" => date('r')
    );

    //Insertion dans la table ad_carte_atm
    $db = $dbHandler->openConnection();
    $sql_query = buildInsertQuery("ad_carte_atm", $array_insert);

    $result = $db->query($sql_query);
    if (DB:: isError($result)) {
        $dbHandler->closeConnection(false);
        $err_obj = new ErrorObj(ERR_DB_SQL, _("ligne: " . __LINE__ . " $sql_query"));
        $err_msg = $error[$err_obj->errCode] . " " . $err_obj->param;
    }else{
        $dbHandler->closeConnection(true);
        $my_page =new HTML_message(_("Confirmation de commande de carte ATM"));
        $my_page->setMessage("Votre commande de carte a été enregistré",true);
        $my_page->addButton("BUTTON_OK", 'Gen-4');
        //HTML
        $my_page->buildHTML();
        echo $my_page->getHTML();
    }




}
?>