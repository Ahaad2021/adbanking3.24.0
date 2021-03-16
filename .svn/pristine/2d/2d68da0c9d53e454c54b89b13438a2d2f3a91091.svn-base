
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


if ($global_nom_ecran == "Rct-1")  {
global $global_id_client;
    $data_carte = getRetraitCarte($global_id_client);
    $choix_compte = array();
    foreach ($data_carte as $data){
        $data_compte = getAccountDatas($data['id_cpte']);
        $choix_compte[$data['id_carte']] = $data_compte['num_complet_cpte']."-".$data['nom_sur_carte'];
    }
    // Création du formulaire
    $my_page = new HTML_GEN2(_("Retrait de carte ATM : choix du compte d'épargne"));
    $my_page->addField("num_cpte", _("Numéro de compte"), TYPC_LSB);
    $my_page->setFieldProperties("num_cpte", FIELDP_ADD_CHOICES, $choix_compte);
    $my_page->setFieldProperties("num_cpte",FIELDP_IS_REQUIRED, true);




    // Boutons
    $my_page->addFormButton(1,1,"valid", _("Valider"), TYPB_SUBMIT);
    $my_page->setFormButtonProperties("valid", BUTP_PROCHAIN_ECRAN, "Rct-2");
    $my_page->addFormButton(1,2,"annul", _("Annuler"), TYPB_SUBMIT);
    $my_page->setFormButtonProperties("annul", BUTP_PROCHAIN_ECRAN, "Gen-4");
    $my_page->setFormButtonProperties("annul", BUTP_CHECK_FORM, false);

    //HTML
    $my_page->buildHTML();
    echo $my_page->getHTML();

}
else if ($global_nom_ecran == "Rct-2")  {
    global $global_id_client;

    //check carte actif
    $data_carte_actif = getCarteATM('id_client = '.$global_id_client.' and etat_carte = 5');
    if (sizeof($data_carte_actif)>0){
        $erreur = new HTML_erreur(_("Carte actif existante"));
        $erreur->setMessage(_("Vous possédez déja une carte activée! Veuillez attendre que votre carte actuelle ne soit plus active."));
        $erreur->addButton(BUTTON_OK,"Gen-4");
        $erreur->buildHTML();
        echo $erreur->HTML_code;
        $ok = false;
        exit();
    }

    //recuperation des données de la carte
    $data_carte = getCarteATM('id_carte = '.$num_cpte);
    $data_cpte = getAccountDatas($data_carte[0]['id_cpte']);
    // Création du formulaire
    $my_page = new HTML_GEN2(_("Retrait de carte ATM : Information de la carte"));

    $my_page->addHiddenType("id_carte", $num_cpte);

    $my_page->addField("num_cpte", "Numéro du compte", TYPC_TXT);
    $my_page->setFieldProperties("num_cpte", FIELDP_DEFAULT, $data_cpte['num_complet_cpte']);
    $my_page->setFieldProperties("num_cpte", FIELDP_IS_LABEL,true) ;

    $array_split = str_split($data_carte[0]['num_carte_atm'],4);
    $num_atm = implode(" ",$array_split);
    $my_page->addField("num_carte", "Numéro de la carte", TYPC_TXT);
    $my_page->setFieldProperties("num_carte", FIELDP_DEFAULT, $num_atm);
    $my_page->setFieldProperties("num_carte", FIELDP_IS_LABEL,true) ;


    $my_page->addField("motif_dem", "Motif de la demande", TYPC_TXT);
    $my_page->setFieldProperties("motif_dem", FIELDP_DEFAULT, adb_gettext($adsys['motif_demande'][$data_carte[0]['motif_demande']]));
    $my_page->setFieldProperties("motif_dem", FIELDP_IS_LABEL,true) ;


    $data_prestataire = getPrestataireATM($data_carte[0]['id_prestataire']);
    $my_page->addField("prestataire", "Prestataire", TYPC_TXT);
    $my_page->setFieldProperties("prestataire", FIELDP_DEFAULT, $data_prestataire['nom_prestataire']);
    $my_page->setFieldProperties("prestataire", FIELDP_IS_LABEL,true) ;

    $my_page->addField("etat_carte", "Etat actuelle carte", TYPC_TXT);
    $my_page->setFieldProperties("etat_carte", FIELDP_DEFAULT, adb_gettext($adsys['etat_carte_atm'][$data_carte[0]['etat_carte']]));
    $my_page->setFieldProperties("etat_carte", FIELDP_IS_LABEL,true) ;

    $my_page->addField("date_deb_valid", "Date début de validité", TYPC_DTE);
    $my_page->setFieldProperties("date_deb_valid", FIELDP_DEFAULT, $data_carte[0]['date_carte_debut_validite']);
    $my_page->setFieldProperties("date_deb_valid", FIELDP_IS_LABEL,true) ;


    $my_page->addField("date_fin_valid", "Date fin de validité", TYPC_DTE);
    $my_page->setFieldProperties("date_fin_valid", FIELDP_DEFAULT, $data_carte[0]['date_carte_expiration']);
    $my_page->setFieldProperties("date_fin_valid", FIELDP_IS_LABEL,true) ;

    $date_jour = date("d");
    $date_mois = date("m");
    $date_annee = date("Y");
    $date_total = $date_jour."/".$date_mois."/".$date_annee;
    $my_page->addField("date_activation", "Date activation", TYPC_DTE);
    $my_page->setFieldProperties("date_activation", FIELDP_DEFAULT, $date_total);
    $my_page->setFieldProperties("date_activation", FIELDP_IS_LABEL,true) ;
//
//    $array_etat_carte = array(4 => "Annulé", 5=>"Activé");
//    $my_page->addField("new_etat_carte", _("Nouveau état carte"), TYPC_LSB);
//    $my_page->setFieldProperties("new_etat_carte", FIELDP_ADD_CHOICES, $array_etat_carte);
//    $my_page->setFieldProperties("new_etat_carte",FIELDP_IS_REQUIRED, true);
//

    $my_page->addFormButton(1, 1, "valider", _("Valider"), TYPB_SUBMIT);
    $my_page->setFormButtonProperties("valider", BUTP_PROCHAIN_ECRAN, "Rct-3");
    $my_page->addFormButton(1, 2, "annuler", _("Annuler"), TYPB_SUBMIT);
    $my_page->setFormButtonProperties("annuler", BUTP_PROCHAIN_ECRAN, "Gen-4");

    $my_page->buildHTML();
    echo $my_page->getHTML();
}
else if ($global_nom_ecran == "Rct-3")  {

    global $global_id_agence, $global_id_client, $dbHandler;
    $my_page = new HTML_GEN2(_("Retrait de carte ATM : Activation de la carte"));

    $array_update = array(
        "etat_carte" => 5,
        "date_modif" => date('r'),
        "date_activation" => date('r')
    );

    $array_condition = array("id_carte" => $id_carte);

    //Insertion dans la table ad_carte_atm
    $update_ad_carte = UpdateTable("ad_carte_atm",$array_update,$array_condition);

    $data_carte_abo = getCarteATM('id_carte = '.$id_carte);
    $array_update_abo = array(
        "id_carte" => $id_carte,
        "num_carte_atm" => $data_carte_abo[0]['num_carte_atm'],
        'statut' => 2,
        "date_modif" => date('r')
    );

    $array_condition_abo = array("id_cpte" => $data_carte_abo[0]['id_cpte'], "id_client" => $data_carte_abo[0]['id_client']);

    $update_ad_abonnement_atm = UpdateTable("ad_abonnement_atm",$array_update_abo,$array_condition_abo);

    $my_page = new HTML_message(_("Confirmation de retrait de carte ATM"));
    $my_page->setMessage("Votre carte ATM a été activé", true);
    $my_page->addButton("BUTTON_OK", 'Gen-4');
    //HTML
    $my_page->buildHTML();
    echo $my_page->getHTML();



}