<?php
/* vim: set expandtab softtabstop=2 shiftwidth=2 foldmethod=marker: */

/**
 * [89] Bloquage et débloquage de compte
 * Cette opération comprends les écrans :
 * - Dlm-1 : Liste de tous les comptes du client
 * - Bdc-2 : Demande de confirmation du bloquage
 * - Bdc-3 : Confirmation du bloquage
 * @package Epargne
 */

require_once 'lib/html/HTML_GEN2.php';
require_once 'lib/dbProcedures/compte.php';
require_once 'lib/dbProcedures/epargne.php';
require_once 'lib/misc/Erreur.php';

/*{{{ Bdc-1 : Liste de tous les comptes du client */
if ($global_nom_ecran == "Dlm-1") {
    $html = new HTML_GEN2(_("Déblocage du compte"));
    // Création du formulaire
    $table =& $html->addHTMLTable('tablecomptes', 9 /*nbre colonnes*/, TABLE_STYLE_ALTERN);

    $table->add_cell(new TABLE_cell(_("Nº dossier"),		/*colspan*/1,	/*rowspan*/1	));
    $table->add_cell(new TABLE_cell(_("Numéro"),		/*colspan*/1,	/*rowspan*/1	));
    $table->add_cell(new TABLE_cell(_("Intitulé"),	/*colspan*/1,	/*rowspan*/1	));
// $table->add_cell(new TABLE_cell(_("Devise"),		/*colspan*/1,	/*rowspan*/1	));
    $table->add_cell(new TABLE_cell(_("Type de produit"),	/*colspan*/1,	/*rowspan*/1	));
    $table->add_cell(new TABLE_cell(_("Etat"),		/*colspan*/1,	/*rowspan*/1	));
    $table->add_cell(new TABLE_cell(_("Raison blocage"),		/*colspan*/1,	/*rowspan*/1	));
    $table->add_cell(new TABLE_cell(_("Montant bloqué"),		/*colspan*/1,	/*rowspan*/1	));
    $table->add_cell(new TABLE_cell(_("Login"),		/*colspan*/1,	/*rowspan*/1	));
    $table->add_cell(new TABLE_cell(_("Action"),		/*colspan*/1,	/*rowspan*/1	));

    // Liste des comptes
    $ListeComptes = getAllDossier($global_id_client);

    if (is_array($ListeComptes)) {
        foreach($ListeComptes as $key=>$value) {
            $etat_cpte = $value['etat_cpte'];
            $id_prod = $value['id_prod'];
            $account_datas = getAccountDatas($value['id_cpte']);
            $table->add_cell(new TABLE_cell($value['id_doss']));
            $table->add_cell(new TABLE_cell($value['num_complet_cpte']));
            $table->add_cell(new TABLE_cell($value['intitule_compte']));
            //$table->add_cell(new TABLE_cell($value['devise']));
            $table->add_cell(new TABLE_cell($account_datas['libel']));
            $cell = new TABLE_cell(adb_gettext($adsys['adsys_etat_cpt_epargne'][$etat_cpte]));
            if ($etat_cpte == '3') {
                $cell->set_property("color","red");
            }
            $table->add_cell($cell);

            if ($id_prod != '2' && $id_prod != '4') {
                if ($etat_cpte == '1') {
                    $cell = new TABLE_cell_link(_("Débloquer"),"$PHP_SELF?m_agc=".$_REQUEST['m_agc']."&prochain_ecran=Dlm-2&id_cpte=".$value['id_cpte']."&action=1&etat_cpte=$etat_cpte&id_doss=".$value['id_doss']."&cre_mnt_bloq=".$value['cre_mnt_bloq']);
                    $raison_bloc = "Remboursement d'un échéance";
                    $mnt_bloque = $value['cre_mnt_bloq'];
                    $login = NULL;
                }
            } else {
                $cell = new TABLE_cell("");
                $date_bloc = NULL;
                $raison_bloc = NULL;
                $login = NULL;
            }
            $table->add_cell(new TABLE_cell($raison_bloc));
            $table->add_cell(new TABLE_cell(afficheMontant($mnt_bloque)));
            $table->add_cell(new TABLE_cell(getLibel("ad_uti", $login)));
            $table->add_cell($cell);

        }
    }

//Boutons
    $html->addFormButton(1,1, "retour", _("Retour menu"), TYPB_SUBMIT);
    $html->setFormButtonProperties("retour", BUTP_PROCHAIN_ECRAN, "Gen-10");
    $html->setFormButtonProperties("retour", BUTP_CHECK_FORM, false);

    $html->buildHTML();
    echo $html->getHTML();
}
/*}}}*/

/*{{{ Bdc-2 : Demande de confirmation du bloquage */
else if ($global_nom_ecran == "Dlm-2") {
    global $global_nom_login;
    // Liste des utlisateurs
    $SESSION_VARS["userId"]=get_login_utilisateur($global_nom_login);
    $userName=get_utilisateur_nom($SESSION_VARS["userId"]);
    /*$utilisateurs = getUtilisateurs();
    foreach($utilisateurs as $id_uti=>$val_uti)
    $SESSION_VARS['utilisateurs'][$id_uti] = $val_uti['nom']." ".$val_uti['prenom'];
  */
    $SESSION_VARS['id_cpte'] = $id_cpte;
    $SESSION_VARS['action'] = $action;
    $SESSION_VARS['etat_cpte'] = $etat_cpte;
    $SESSION_VARS['id_doss'] = $id_doss;
    $SESSION_VARS['cre_mnt_bloq'] = $cre_mnt_bloq;

    $InfoCompte = get_compte_epargne_info($id_cpte);

    //Si les dépôts et retraits étaient bloqués, définir quel type de déblocage
    $html = new HTML_GEN2(_("Déblocage du compte"));
    debug($SESSION_VARS['etat_cpte'], _("etat cpte"));
    //Sélectionner le type de blocage
    $html->addField("mnt_bloq_cre",_("Montant credit bloqué"),TYPC_MNT);
    $html->setFieldProperties("mnt_bloq_cre", FIELDP_DEFAULT, $SESSION_VARS['cre_mnt_bloq']);
    $html->setFieldProperties("mnt_bloq_cre", FIELDP_IS_LABEL, true);

    $html->addField("confirm_mnt",_("Confirmé le montant"),TYPC_TXT);
    $html->setFieldProperties("confirm_mnt", FIELDP_IS_REQUIRED, true);

    $html->addField("login",_("Gestionnaire"),TYPC_TXT);
    $html->setFieldProperties("login", FIELDP_DEFAULT, $userName);
    $html->setFieldProperties("login", FIELDP_IS_REQUIRED, true);
    $html->setFieldProperties("login", FIELDP_IS_LABEL, true);

    $check_mnt = "
        function check_mnt(){
            if(recupMontant(document.ADForm.confirm_mnt.value) > recupMontant(document.ADForm.mnt_bloq_cre.value) || (document.ADForm.confirm_mnt.value == 0)){
                if(document.ADForm.confirm_mnt.value == ''){
                    alert('-Le champ \'Confirmé le montant\' n\'est pas rensiegné');
                }else{
                    alert('-Le montant saisie doit etre inférieure ou égal aux montant bloqué');
                }
                return true;
            }
            return false;    
        }
    ";
    $html->addFormButton(1, 1, "ok", _("Valider"), TYPB_BUTTON);
    $html->setFormButtonProperties("ok", BUTP_JS_EVENT, array("onclick" => "if(!check_mnt()){assign('Dlm-3'); document.ADForm.submit();}"));
    $html->addFormButton(1, 2, "retour", _("Retour"), TYPB_SUBMIT);
    $html->setFormButtonProperties("retour", BUTP_CHECK_FORM, false);
    $html->setFormButtonProperties("retour", BUTP_PROCHAIN_ECRAN,'Dlm-1');
    $html->addJS(JSP_FORM,"check_mnt",$check_mnt);
    $html->buildHTML();
    echo $html->getHTML();

}
/*}}}*/

/*{{{ Dlm-3 : Confirmation du bloquage */
else if ($global_nom_ecran == "Dlm-3") {
    global $global_cpt_base_ouvert, $global_depot_bloque, $global_retrait_bloque;

    $InfoCompte = get_compte_epargne_info($SESSION_VARS['id_cpte']);
    $mnt_bloq_cre = $InfoCompte['mnt_bloq_cre'] - $confirm_mnt;
    $cre_mnt_bloq = $SESSION_VARS['cre_mnt_bloq'] - $confirm_mnt;

    $err = debloqMontantCredit($InfoCompte['id_titulaire'], $InfoCompte['id_cpte'], $SESSION_VARS['id_doss'], $mnt_bloq_cre, $cre_mnt_bloq);
    $msg = ($SESSION_VARS['cre_mnt_bloq'] - $confirm_mnt) == 0?'complètement':'partiellement';
    if ($err->errCode == NO_ERR) {
        $html_msg = new HTML_message(_("Confirmation de blocage du compte"));
        $html_msg->setMessage(sprintf(_("Le compte n° %s est maintenant débloque %s"), $InfoCompte['num_complet_cpte'], $msg)."<br/><br/>"._("N° de transaction")." : <B><code>".sprintf("%09d", $err->param['id'])."</code></B>");
        $html_msg->addButton("BUTTON_OK", 'Gen-10');
        $html_msg->buildHTML();
        echo $html_msg->HTML_code;
    }else{
        $html_err = new HTML_erreur(_("Echec de déblocage du compte"));
        $html_err->setMessage(_("Erreur")." : ".$error[$err->errCode]);
        $html_err->addButton("BUTTON_OK", 'Gen-10');
        $html_err->buildHTML();
        echo $html_err->HTML_code;
    }
}
/*}}}*/

else signalErreur(__FILE__,__LINE__,__FUNCTION__, sprintf(_("L'écran %s n'a pas pu être trouvé"), $global_nom_ecran));

?>
