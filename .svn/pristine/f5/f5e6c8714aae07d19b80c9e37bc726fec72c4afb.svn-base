<?Php
/* vim: set expandtab softtabstop=2 shiftwidth=2 foldmethod=marker: */

/**
 * [1138] Approbation reechelonnement des dossiers de crédit 'En une fois' (periodicite = 6)
 * Cette opération comprends les écrans :
 * - Auf-1 : sélection d'un dossier de crédit
 * - Auf-2 : approbation d'un dossier de crédit
 * - Auf-3 : affichage de l'échéancier
 * - Auf-4 : confirmation approbation d'un dossier de crédit
 * @package Credit
 */

require_once 'lib/html/HTML_GEN2.php';
require_once 'lib/html/FILL_HTML_GEN2.php';
require_once 'lib/html/HTML_erreur.php';
require_once 'lib/dbProcedures/credit.php';
require_once 'lib/misc/VariablesGlobales.php';
require_once 'lib/algo/ech_theorique.php';
require_once 'lib/html/echeancier.php';
require_once 'lib/misc/divers.php';

require_once 'lib/dbProcedures/historisation.php';

/*{{{ Auf-1 : Sélection d'un dossier de crédit */
if ($global_nom_ecran == "Auf-1") {
    unset($SESSION_VARS['infos_doss']);
    // Récupération des infos du client
    $SESSION_VARS['infos_client'] = getClientDatas($global_id_client);

    //en fonction du choix du numéro de dossier, afficher les infos avec le onChange javascript
    $codejs = "\n\nfunction getInfoDossier() {";

    $dossiers = array(); // tableau contenant les infos sur dossiers réels (dans ad_dcr) et fictifs (ad_dcr_grp_sol)
    $liste = array(); // Liste des dossiers à afficher
    $i = 1;

    // Récupération des dossiers individuels 'En une fois' dans ad_dcr en attente de Rééch/Moratoire
    $whereCl=" AND (etat=7) AND (periodicite=6) ";
    $dossiers_reels = getIdDossier($global_id_client,$whereCl);
    if (is_array($dossiers_reels))
        foreach($dossiers_reels as $id_doss=>$value)
            if ($value['gs_cat'] != 2) { // les dossiers pris en groupe doivent être approuvés via le groupe
                $date = pg2phpDate($value["date_dem"]); //Fonction renvoie  des dates au format jj/mm/aaaa
                $liste[$i] ="n° $id_doss du $date"; //Construit la liste en affichant N° dossier + date
                $dossiers[$i] = $value;

                $codejs .= "\n\tif (document.ADForm.HTML_GEN_LSB_id_doss.value ==$i)\n\t";
                $codejs .= "{\n\t\tdocument.ADForm.id_prod.value =\"" . $value["libelle"] . "\";";
                $codejs .= "}\n";
                $i++;
            }

    // SI GS, récupérer les dossiers des membres dans le cas de dossiers multiples
    if ($SESSION_VARS['infos_client']['statut_juridique'] == 4) {
        // Récupération des dossiers fictifs du groupe avec dossiers multiples : cas 2
        $whereCl = " WHERE id_membre=$global_id_client and gs_cat=2";
        $dossiers_fictifs = getCreditFictif($whereCl);

        // Pour chaque dossier fictif du GS, récupération des dossiers réels des membres du GS
        $dossiers_membre = getDossiersMultiplesGS($global_id_client);

        foreach($dossiers_fictifs as $id=>$value) {
            // Récupération, pour chaque dossier fictif, des dossiers réels associés : CS avec dossiers multiples
            $infos = '';
            foreach($dossiers_membre as $id_doss=>$val)
                if (($val['id_dcr_grp_sol'] == $id) AND ($val['etat'] == 7) AND ($val['periodicite'] == 6) ) {
                    $date_dem = $date = pg2phpDate($val['date_dem']);
                    $infos .= "n° $id_doss "; // on affiche les numéros de dossiers réels sur une ligne
                }
            if ($infos != '') { // Si au moins on 1 dossier
                $infos .= "du $date_dem";
                $liste[$i] = $infos;
                $dossiers[$i] = $value; // on gAufe les infos du dossier fictif

                $codejs .= "\n\tif (document.ADForm.HTML_GEN_LSB_id_doss.value ==$i)\n\t";
                $codejs .= "{\n\t\tdocument.ADForm.id_prod.value =\"" . $val["libelle"] . "\";";
                $codejs .= "}\n";
                $i++;
            }
        }
    }

    $SESSION_VARS['dossiers'] = $dossiers;
    $codejs .= "\n\tif (document.ADForm.HTML_GEN_LSB_id_doss.value =='0') {";
    $codejs .= "\n\t\tdocument.ADForm.id_prod.value='';";
    $codejs .= "\n\t}\n";
    $codejs .= "}\ngetInfoDossier();";

    $Myform = new HTML_GEN2(_("Sélection d'un dossier de crédit"));
    $Myform->addField("id_doss",_("Dossier de crédit"), TYPC_LSB);
    $Myform->addField("id_prod",_("Type produit de crédit"), TYPC_TXT);

    $Myform->setFieldProperties("id_prod", FIELDP_IS_LABEL, true);
    $Myform->setFieldProperties("id_prod", FIELDP_IS_REQUIRED, false);
    $Myform->setFieldProperties("id_prod", FIELDP_WIDTH, 30);

    $Myform->setFieldProperties("id_doss",FIELDP_ADD_CHOICES,$liste);
    $Myform->setFieldProperties("id_doss", FIELDP_JS_EVENT, array("onChange"=>"getInfoDossier();"));
    $Myform->addJS(JSP_FORM, "JS3", $codejs);

    // Javascript : vérifie qu'un dossier est sélectionné
    $JS_1 = "";
    $JS_1.="\t\tif(document.ADForm.HTML_GEN_LSB_id_doss.options[document.ADForm.HTML_GEN_LSB_id_doss.selectedIndex].value==0){ msg+=' - "._("Aucun dossier sélectionné")." .\\n';ADFormValid=false;}\n";
    $Myform->addJS(JSP_BEGIN_CHECK,"testdos",$JS_1);

    // Ordre d'affichage des champs
    $order = array("id_doss","id_prod");

    // les boutons ajoutés
    $Myform->addFormButton(1,1,"valider",_("Valider"),TYPB_SUBMIT);
    $Myform->addFormButton(1,2,"annuler",_("Retour Menu"),TYPB_SUBMIT);

    // Propriétés des boutons
    $Myform->setFormButtonProperties("annuler", BUTP_PROCHAIN_ECRAN, "Gen-11");
    $Myform->setFormButtonProperties("valider", BUTP_PROCHAIN_ECRAN, "Auf-2");
    $Myform->setFormButtonProperties("annuler", BUTP_CHECK_FORM, false);

    $Myform->setOrder(NULL,$order);
    $Myform->buildHTML();
    echo $Myform->getHTML();
}
/*}}}*/


/*{{{ Auf-2 : Approbation d'un dossier de crédit */
else if ($global_nom_ecran == "Auf-2") 
{
    global $adsys;
    // Si on vient de Auf-1, on récupère les infos de la BD
    if (strstr($global_nom_ecran_prec,"Auf-1")) {

        // Récupération des dossiers à approuver
        if ($SESSION_VARS['dossiers'][$HTML_GEN_LSB_id_doss]['gs_cat'] != 2 ) { // dossier individuel
            // Les informations sur le dossier
            $id_doss = $SESSION_VARS['dossiers'][$HTML_GEN_LSB_id_doss]['id_doss'];
            $id_prod = $SESSION_VARS['dossiers'][$HTML_GEN_LSB_id_doss]['id_prod'];;
            $SESSION_VARS['infos_doss'][$id_doss] = getDossierCrdtInfo($id_doss); // infos du dossier reel
            $SESSION_VARS['infos_doss'][$id_doss]['cre_date_approb'] = date("d/m/Y");
            $SESSION_VARS['infos_doss'][$id_doss]['last_etat'] = $SESSION_VARS['infos_doss'][$id_doss]['etat'];
            $SESSION_VARS['infos_doss'][$id_doss]['cre_mnt_octr'] = $SESSION_VARS['infos_doss'][$id_doss]['mnt_dem'];
            // Infos dossiers fictifs dans le cas de GS avec dossier unique
            if ($SESSION_VARS['dossiers'][$HTML_GEN_LSB_id_doss]['gs_cat'] == 1) {
                $whereCond = " WHERE id_dcr_grp_sol = $id_doss";
                $SESSION_VARS['infos_doss'][$id_doss]['doss_fic'] = getCreditFictif($whereCond);
            }
        }
        elseif($SESSION_VARS['dossiers'][$HTML_GEN_LSB_id_doss]['gs_cat'] == 2 ) { // GS avec dossiers multiples
            // id du dossier fictif : id du dossier du groupe
            $id_doss_fic = $SESSION_VARS['dossiers'][$HTML_GEN_LSB_id_doss]['id'];

            // dossiers réels des membre du GS
            $dossiers_membre = getDossiersMultiplesGS($global_id_client);
            foreach($dossiers_membre as $id_doss=>$val)
            {
                if ($val['id_dcr_grp_sol']==$SESSION_VARS['dossiers'][$HTML_GEN_LSB_id_doss]['id'] and ($val['etat']==7 and $val['periodicite'] ==6)) {
                    $SESSION_VARS['infos_doss'][$id_doss] = $val; // infos d'un dossier reel d'un membre
                    $SESSION_VARS['infos_doss'][$id_doss]['cre_date_approb'] = date("d/m/Y");
                    $SESSION_VARS['infos_doss'][$id_doss]['last_etat'] = $SESSION_VARS['infos_doss'][$id_doss]['etat'];
                    $SESSION_VARS['infos_doss'][$id_doss]['cre_mnt_octr'] = $SESSION_VARS['infos_doss'][$id_doss]['mnt_dem'];
                    $id_prod = $SESSION_VARS['infos_doss'][$id_doss]['id_prod']; // même produit pour tous les dossiers
                }
            }
        }

        /* Vérificateur de l'état des garanties  */
        $gar_pretes = true;

        /* Récupération des garanties déjà mobilisées pour ce dossier */
        foreach($SESSION_VARS['infos_doss'] as $id_doss=>$infos_doss) {
            $SESSION_VARS['infos_doss'][$id_doss]['DATA_GAR'] = array();
            $liste_gar = getListeGaranties($id_doss);

            foreach($liste_gar as $key=>$value )
            {
                $num = count($SESSION_VARS['infos_doss'][$id_doss]['DATA_GAR']) + 1; // indice du tableau
                $SESSION_VARS['infos_doss'][$id_doss]['DATA_GAR'][$num]['id_gar'] = $value['id_gar'];
                $SESSION_VARS['infos_doss'][$id_doss]['DATA_GAR'][$num]['type'] = $value['type_gar'];
                $SESSION_VARS['infos_doss'][$id_doss]['DATA_GAR'][$num]['valeur'] = recupMontant($value['montant_vente']);
                $SESSION_VARS['infos_doss'][$id_doss]['DATA_GAR'][$num]['devise_vente'] = $value['devise_vente'];
                $SESSION_VARS['infos_doss'][$id_doss]['DATA_GAR'][$num]['etat'] = $value['etat_gar'];

                /* Les garanties doivent être à l'état 'Prête' ou mobilisé  au moment de l'approbation  */
                if ($value['etat_gar'] !=2  and $value['etat_gar'] != 3) {
                    $gar_pretes = false;
                }

                /* Si c'est une garantie numéraire récupérer le numéro complet du compte de prélèvement */
                if ($value['type_gar'] == 1) /* Garantie numéraire */
                    $SESSION_VARS['infos_doss'][$id_doss]['DATA_GAR'][$num]['descr_ou_compte'] = $value['gar_num_id_cpte_prelev'];

                elseif($value['type_gar'] == 2 and isset($value['gar_mat_id_bien'])) { /* garantie matérielle */
                    $id_bien = $value['gar_mat_id_bien'];
                    $infos_bien = getInfosBien($id_bien);
                    $SESSION_VARS['infos_doss'][$id_doss]['DATA_GAR'][$num]['descr_ou_compte'] = $infos_bien['description'];
                    $SESSION_VARS['infos_doss'][$id_doss]['DATA_GAR'][$num]['type_bien'] = $infos_bien['type_bien'];
                    $SESSION_VARS['infos_doss'][$id_doss]['DATA_GAR'][$num]['piece_just'] = $infos_bien['piece_just'];
                    $SESSION_VARS['infos_doss'][$id_doss]['DATA_GAR'][$num]['num_client'] = $infos_bien['id_client'];
                    $SESSION_VARS['infos_doss'][$id_doss]['DATA_GAR'][$num]['remarq'] = $infos_bien['remarque'];
                    $SESSION_VARS['infos_doss'][$id_doss]['DATA_GAR'][$num]['gar_mat_id_bien'] = $id_bien;
                }
            } /* Fin foreach garantie */
        } /* Fin foreach infos dossiers */

        /* Toutes les garanties doivent être à l'état 'Prête' ou 'Mobilisé' au moment du déboursement  */
        if ( $gar_pretes == false) {
            $erreur = new HTML_erreur(_("Approbation de dossier de crédit"));
            $msg = _("Impossible d'approuver le dossier de crédit. Les garanties mobilisées ne sont pas toutes prêtes");
            $erreur->setMessage($msg);
            $erreur->addButton(BUTTON_OK,"Gen-11");
            $erreur->buildHTML();
            echo $erreur->HTML_code;
            exit();
        }

        // Les informations sur le produit de crédit
        $Produit = getProdInfo(" where id =".$id_prod, $id_doss);
        $SESSION_VARS['infos_prod'] = $Produit[0];

        // Récupérations des utilisateurs. C'est à dire les agents gestionnaires
        $SESSION_VARS['utilisateurs'] = array();
        $utilisateurs = getUtilisateurs();
        foreach($utilisateurs as $id_uti=>$val_uti)
            $SESSION_VARS['utilisateurs'][$id_uti] = $val_uti['nom']." ".$val_uti['prenom'];
        //Tri par ordre alphabétique des utilisateurs
        natcasesort($SESSION_VARS['utilisateurs']);
        // Objet demande de crédit
        $SESSION_VARS['obj_dem'] = getObjetsCredit();
    } //fin si on vient de Auf-1

    //on revient de Auf-6 aprés consultation des garanties
    if (strstr($global_nom_ecran_prec,"Auf-6")) {
        $id_doss = $SESSION_VARS['id_doss'];
        debug($id_doss  ,"id_doss "._("venant de ecran 6"));
    }
    /* Récupération des garanties déjà mobilisées pour ce dossier */
    $SESSION_VARS['DATA_GAR'] = array();
    $liste_gar = getListeGaranties($id_doss);
    //recuperation de la precision de la devise du produit de credit
    $devise_prod=$SESSION_VARS['infos_prod']['devise'];
    $DEV = getInfoDevise($devise_prod);// recuperation d'info sur la devise'
    $precision_devise=pow(10,$DEV["precision"]);

    foreach($liste_gar as $key=>$value ) {
        /* Mémorisation des garanties */
        $num = count($SESSION_VARS['DATA_GAR']) +1;
        $SESSION_VARS['DATA_GAR'][$num]['id_gar'] = $value['id_gar'];
        $SESSION_VARS['DATA_GAR'][$num]['type'] = $value['type_gar'];
        $SESSION_VARS['DATA_GAR'][$num]['valeur'] = recupMontant($value['montant_vente']);
        $SESSION_VARS['DATA_GAR'][$num]['devise_vente'] = $value['devise_vente'];
        $SESSION_VARS['DATA_GAR'][$num]['etat'] = $value['etat_gar'];

        /* Les garanties doivent être à l'état 'Prête' ou mobilisé sauf les garanties numéraires encours  */
        if ($value['etat_gar'] !=2  and $value['etat_gar'] != 3 and $value['type_gar'] != 1 and $value['gar_num_id_cpte_prelev'] != NULL)
            $gar_pretes = false;

        /* Si c'est une garantie numéraire récupérer le numéro complet du compte de prélèvement */
        if ($value['type_gar'] == 1) /* Garantie numéraire */
            $SESSION_VARS['DATA_GAR'][$num]['descr_ou_compte'] = $value['gar_num_id_cpte_prelev'];
        elseif($value['type_gar'] == 2 and isset($value['gar_mat_id_bien'])) { /* garantie matérielle */
            $id_bien = $value['gar_mat_id_bien'];
            $infos_bien = getInfosBien($id_bien);
            $SESSION_VARS['DATA_GAR'][$num]['descr_ou_compte'] = $infos_bien['description'];
            $SESSION_VARS['DATA_GAR'][$num]['type_bien'] = $infos_bien['type_bien'];
            $SESSION_VARS['DATA_GAR'][$num]['piece_just'] = $infos_bien['piece_just'];
            $SESSION_VARS['DATA_GAR'][$num]['num_client'] = $infos_bien['id_client'];
            $SESSION_VARS['DATA_GAR'][$num]['remarq'] = $infos_bien['remarque'];
            $SESSION_VARS['DATA_GAR'][$num]['gar_mat_id_bien'] = $id_bien;
        }
    } /* Fin foreach */

    // Gestion de la devise
    setMonnaieCourante($SESSION_VARS['infos_prod']['devise']);
    $id_prod  = $SESSION_VARS['infos_prod']['id'];

    // Création du formulaire
    $js_date_approb = '';
    $js_gar = '';
    $js_check = ""; // Javascript de validation de la saisie
    $can_mob_gar = false ; // On ne peut mobiliser des garanties que si le dossier est en attente de décision
    $Myform = new HTML_GEN2(_("Approbation dossier de crédit"));

    foreach($SESSION_VARS['infos_doss'] as $id_doss=>$val_doss)
    {
        $nom_cli = getClientName($val_doss['id_client']);
        $Myform->addHTMLExtraCode("espace".$id_doss,"<br/><b><p align=\"center\"><b> ".sprintf(_("Approbation Rééchelonnement du dossier N° %s de %s"),$id_doss,$nom_cli)."</b></p>");

        $Myform->addField("id_doss".$id_doss, _("Numéro de dossier"), TYPC_TXT);
        $Myform->setFieldProperties("id_doss".$id_doss,FIELDP_IS_LABEL,true);
        $Myform->setFieldProperties("id_doss".$id_doss,FIELDP_DEFAULT,$val_doss['id_doss']);
        $Myform->addField("id_prod".$id_doss, _("Produit de crédit"), TYPC_LSB);
        $Myform->setFieldProperties("id_prod".$id_doss,FIELDP_IS_LABEL,true);
        $Myform->setFieldProperties("id_prod".$id_doss,FIELDP_ADD_CHOICES, array("$id_prod"=>$SESSION_VARS['infos_prod']['libel']));
        $Myform->setFieldProperties("id_prod".$id_doss,FIELDP_DEFAULT, $id_prod);
        // Ajout de liens
        $Myform->addLink("id_prod".$id_doss, "produit".$id_doss,_("Détail produit"), "#");
        $Myform->setLinkProperties("produit".$id_doss,LINKP_JS_EVENT,array("onClick"=>"open_produit(".$id_prod.",".$id_doss.");"));
        $Myform->addField("periodicite".$id_doss, _("Périodicité"), TYPC_INT);
        $Myform->setFieldProperties("periodicite".$id_doss,FIELDP_DEFAULT,adb_gettext($adsys["adsys_type_periodicite"][$SESSION_VARS['infos_prod']['periodicite']]));
        $Myform->setFieldProperties("periodicite".$id_doss,FIELDP_IS_LABEL,true);
        $Myform->addField("obj_dem".$id_doss, _("Objet de la demande"), TYPC_LSB);
        $Myform->setFieldProperties("obj_dem".$id_doss, FIELDP_ADD_CHOICES, $SESSION_VARS['obj_dem']);
        $Myform->setFieldProperties("obj_dem".$id_doss,FIELDP_IS_LABEL,true);
        $Myform->setFieldProperties("obj_dem".$id_doss,FIELDP_DEFAULT,$val_doss['obj_dem']);
        $Myform->addField("detail_obj_dem".$id_doss, _("Détail objet demande"), TYPC_TXT);
        $Myform->setFieldProperties("detail_obj_dem".$id_doss,FIELDP_IS_LABEL,true);
        $Myform->setFieldProperties("detail_obj_dem".$id_doss,FIELDP_DEFAULT,$val_doss['detail_obj_dem']);
        $Myform->addField("date_dem".$id_doss, _("Date demande"), TYPC_DTE);
        $Myform->setFieldProperties("date_dem".$id_doss,FIELDP_IS_LABEL,true);
        $Myform->setFieldProperties("date_dem".$id_doss,FIELDP_DEFAULT,$val_doss['date_dem']);
        $Myform->addField("cre_date_debloc".$id_doss, _("Date de déblocage"), TYPC_DTE);
        $Myform->setFieldProperties("cre_date_debloc".$id_doss,FIELDP_IS_LABEL,true);
        $Myform->setFieldProperties("cre_date_debloc".$id_doss,FIELDP_DEFAULT,$val_doss['cre_date_debloc']);
        $Myform->addField("mnt_dem".$id_doss, _("Montant demandé"), TYPC_MNT);
        $Myform->setFieldProperties("mnt_dem".$id_doss,FIELDP_IS_LABEL,true);
        $Myform->setFieldProperties("mnt_dem".$id_doss,FIELDP_DEFAULT, $val_doss['mnt_dem'],true);
        $Myform->addField("num_cre".$id_doss, _("Numéro de crédit"), TYPC_INT);
        $Myform->setFieldProperties("num_cre".$id_doss,FIELDP_IS_LABEL,true);
        $Myform->setFieldProperties("num_cre".$id_doss,FIELDP_DEFAULT,$val_doss['num_cre']);
        $Myform->addField("cpt_liaison".$id_doss, _("Compte de liaison"), TYPC_TXT);
        $Myform->setFieldProperties("cpt_liaison".$id_doss,FIELDP_IS_LABEL,true);
        $cpt_lie = getAccountDatas($val_doss['cpt_liaison']);
        $Myform->setFieldProperties("cpt_liaison".$id_doss,FIELDP_DEFAULT,$cpt_lie["num_complet_cpte"]." ".$cpt_lie['intitule_compte']);
        $Myform->addField("id_agent_gest".$id_doss, _("Agent gestionnaire"), TYPC_LSB);
        $Myform->setFieldProperties("id_agent_gest".$id_doss, FIELDP_ADD_CHOICES, $SESSION_VARS['utilisateurs']);
        $Myform->setFieldProperties("id_agent_gest".$id_doss,FIELDP_IS_LABEL,false);
        $Myform->setFieldProperties("id_agent_gest".$id_doss,FIELDP_DEFAULT,$val_doss['id_agent_gest']);
        $Myform->addField("etat".$id_doss, _("Etat du dossier"), TYPC_TXT);
        $Myform->setFieldProperties("etat".$id_doss,FIELDP_IS_LABEL,true);
        $Myform->setFieldProperties("etat".$id_doss,FIELDP_DEFAULT,adb_gettext($adsys["adsys_etat_dossier_credit"][$val_doss['etat']]));
        $Myform->addField("date_etat".$id_doss, _("Date état du dossier"), TYPC_DTE);
        $Myform->setFieldProperties("date_etat".$id_doss,FIELDP_IS_LABEL,true);
        $Myform->setFieldProperties("date_etat".$id_doss,FIELDP_DEFAULT,$val_doss['date_etat']);
        $Myform->addField("cre_mnt_octr".$id_doss, _("Montant octroyé"), TYPC_MNT);
        $Myform->setFieldProperties("cre_mnt_octr".$id_doss,FIELDP_IS_REQUIRED,true);
        $Myform->setFieldProperties("cre_mnt_octr".$id_doss,FIELDP_IS_LABEL,false);
        $Myform->setFieldProperties("cre_mnt_octr".$id_doss,FIELDP_DEFAULT,$val_doss['cre_mnt_octr']);
        $Myform->setFieldProperties("cre_mnt_octr".$id_doss,FIELDP_JS_EVENT,array("OnFocus"=>"reset($id_doss);"));
        $Myform->setFieldProperties("cre_mnt_octr".$id_doss,FIELDP_JS_EVENT,array("OnChange"=>"init($id_doss);"));
        $Myform->addHiddenType("mnt_octr".$id_doss, $val_doss['cre_mnt_octr']);

        if ($SESSION_VARS['infos_doss'][$id_doss]["gs_cat"]==1)
            $Myform->setFieldProperties("cre_mnt_octr".$id_doss,FIELDP_IS_LABEL,true);

        //type de durée : en mois ou en semaine
        $type_duree = $SESSION_VARS['infos_prod']['type_duree_credit'];
        $duree_mois = $val_doss['duree_mois'];
        $nouv_duree_mois = $val_doss['nouv_duree_mois'];
        $duree_min = $nouv_duree_mois;

        $libelle_duree = mb_strtolower(adb_gettext($adsys['adsys_type_duree_credit'][$type_duree])); // libellé type durée en minuscules
        $Myform->addField("duree_mois".$id_doss, sprintf(_("Durée en %s initiale"),$libelle_duree), TYPC_INT);
        $Myform->setFieldProperties("duree_mois".$id_doss,FIELDP_IS_REQUIRED,false);
        $Myform->setFieldProperties("duree_mois".$id_doss,FIELDP_IS_LABEL,true);
        $Myform->setFieldProperties("duree_mois".$id_doss,FIELDP_DEFAULT, $duree_mois);

        $Myform->addField("duree_mois_dem".$id_doss, _("Durée en $libelle_duree souhaitée"), TYPC_INT);
        $Myform->setFieldProperties("duree_mois_dem".$id_doss,FIELDP_IS_REQUIRED,false);
        $Myform->setFieldProperties("duree_mois_dem".$id_doss,FIELDP_IS_LABEL,true);
        $Myform->setFieldProperties("duree_mois_dem".$id_doss,FIELDP_DEFAULT, $nouv_duree_mois);

        $Myform->addField("nouv_duree_mois".$id_doss, _("Durée en $libelle_duree confirmée"), TYPC_INT);
        $Myform->setFieldProperties("nouv_duree_mois".$id_doss,FIELDP_IS_REQUIRED,true);
        $Myform->setFieldProperties("nouv_duree_mois".$id_doss,FIELDP_IS_LABEL,false);
        $Myform->setFieldProperties("nouv_duree_mois".$id_doss,FIELDP_DEFAULT, $duree_min);

        $Myform->addField("cre_date_approb".$id_doss, _("Date approbation"), TYPC_DTE);
        $Myform->setFieldProperties("cre_date_approb".$id_doss,FIELDP_IS_REQUIRED,true);
        $Myform->setFieldProperties("cre_date_approb".$id_doss,FIELDP_IS_LABEL, false);
        $Myform->setFieldProperties("cre_date_approb".$id_doss,FIELDP_DEFAULT,$val_doss['cre_date_approb']);

        // Test de la date d'approbation
        $js_date_approb.= "\t\tif(isBefore(document.ADForm.HTML_GEN_date_cre_date_approb".$id_doss.".value, 'document.ADForm.HTML_GEN_date_date_dem".$id_doss."value')){ msg+=' - ".sprintf(_("La date d\'approbation pour le dossier %s ne peut être antérieure à la date de demande."),$id_doss)."\\n';ADFormValid=false;}\n";

        $Myform->addField("differe_jours".$id_doss, _("Différé en jours"), TYPC_INN);
        $Myform->setFieldProperties("differe_jours".$id_doss,FIELDP_IS_LABEL,false);
        $Myform->setFieldProperties("differe_jours".$id_doss,FIELDP_DEFAULT,$val_doss['differe_jours']);
        $Myform->addField("differe_ech".$id_doss, _("Différé en échéance"), TYPC_INT);
        $Myform->setFieldProperties("differe_ech".$id_doss,FIELDP_DEFAULT,$val_doss['differe_ech']);
        $Myform->addField("delai_grac".$id_doss, _("Délai de grace"), TYPC_INT);
        $Myform->setFieldProperties("delai_grac".$id_doss,FIELDP_DEFAULT,$val_doss['delai_grac']);
        $Myform->addField("mnt_commission".$id_doss, _("Montant commission"), TYPC_MNT);
        $Myform->setFieldProperties("mnt_commission".$id_doss,FIELDP_IS_LABEL,true);
        $Myform->setFieldProperties("mnt_commission".$id_doss,FIELDP_DEFAULT,$val_doss['mnt_commission']);
        $Myform->addField("mnt_assurance".$id_doss, _("Montant assurance"), TYPC_MNT);
        $Myform->setFieldProperties("mnt_assurance".$id_doss,FIELDP_IS_LABEL,true);
        $Myform->setFieldProperties("mnt_assurance".$id_doss,FIELDP_DEFAULT,$val_doss["mnt_dem"]*$SESSION_VARS['infos_prod']['prc_assurance']);

        if(!empty($val_doss['cpt_prelev_frais'])) {
            $Myform->addField("prelev_auto".$id_doss, _("Prélèvement automatique"), TYPC_BOL);
            $Myform->setFieldProperties("prelev_auto".$id_doss,FIELDP_IS_LABEL,true);
            $Myform->setFieldProperties("prelev_auto".$id_doss,FIELDP_DEFAULT,$val_doss['prelev_auto']);
            $Myform->addField("cpt_prelev_frais".$id_doss, _("Compte de prélévement des frais"), TYPC_TXT);
            $Myform->setFieldProperties("cpt_prelev_frais".$id_doss,FIELDP_IS_LABEL,true);
            $cpt_frais = getAccountDatas($val_doss['cpt_prelev_frais']);
            $Myform->setFieldProperties("cpt_prelev_frais".$id_doss,FIELDP_DEFAULT,$cpt_frais["num_complet_cpte"]." ".$cpt_frais['intitule_compte']);
        }

        if ($SESSION_VARS['infos_doss'][$id_doss]["gar_num"] > 0) {
            $Myform->addField("gar_num".$id_doss, _("Garantie numéraire attendue"), TYPC_MNT);
            $Myform->setFieldProperties("gar_num".$id_doss,FIELDP_IS_LABEL,true);
            $Myform->setFieldProperties("gar_num".$id_doss,FIELDP_DEFAULT,$val_doss['gar_num']);
        }
        if ($SESSION_VARS['infos_doss'][$id_doss]["gar_num_encours"] > 0) {
            $Myform->addField("gar_num_encours".$id_doss, _("Garantie numéraire encours"), TYPC_MNT);
            $Myform->setFieldProperties("gar_num_encours".$id_doss,FIELDP_IS_LABEL,true);
            $Myform->setFieldProperties("gar_num_encours".$id_doss,FIELDP_DEFAULT,$val_doss['gar_num_encours']);
        }
        if ($SESSION_VARS['infos_doss'][$id_doss]["gar_mat"] > 0) {
            $Myform->addField("gar_mat".$id_doss, _("Garantie matérielle attendue"), TYPC_MNT);
            $Myform->setFieldProperties("gar_mat".$id_doss,FIELDP_IS_LABEL,true);
            $Myform->setFieldProperties("gar_mat".$id_doss,FIELDP_DEFAULT,$val_doss['gar_mat']);
        }
        if ($SESSION_VARS['infos_doss'][$id_doss]["gar_tot"] > 0) {
            $Myform->addField("gar_tot".$id_doss, _("Garantie totale attendue"), TYPC_MNT);
            $Myform->setFieldProperties("gar_tot".$id_doss,FIELDP_IS_LABEL,true);
            $Myform->setFieldProperties("gar_tot".$id_doss,FIELDP_DEFAULT,$val_doss['gar_tot']);
        }

        /* Initialisation des garanties numéraires et matérielles au début et les garanties à numéraires à constituer */
        $mnt_credit = $val_doss['cre_mnt_octr'];

        if ($SESSION_VARS['infos_doss'][$id_doss]["gar_num"] > 0) {
            $js_gar .="\n\tdocument.ADForm.gar_num".$id_doss.".value=Math.round(".$SESSION_VARS['infos_prod']['prc_gar_num']."*parseFloat(".$mnt_credit.")*".$precision_devise.")/".$precision_devise.";";
            $js_gar .="\n\tdocument.ADForm.gar_num".$id_doss.".value =formateMontant(document.ADForm.gar_num".$id_doss.".value);\n";
        }
        if ($SESSION_VARS['infos_doss'][$id_doss]["gar_mat"] > 0) {
            $js_gar .="\n\tdocument.ADForm.gar_mat".$id_doss.".value=Math.round(".$SESSION_VARS['infos_prod']['prc_gar_mat']."*parseFloat(".$mnt_credit.")*".$precision_devise.")/".$precision_devise.";";
            $js_gar .="\n\tdocument.ADForm.gar_mat".$id_doss.".value =formateMontant(document.ADForm.gar_mat".$id_doss.".value);\n";
        }
        if ($SESSION_VARS['infos_doss'][$id_doss]["gar_tot"] > 0) {
            $js_gar .="\n\tdocument.ADForm.gar_tot".$id_doss.".value=Math.round(".$SESSION_VARS['infos_prod']['prc_gar_tot']."*parseFloat(".$mnt_credit.")*".$precision_devise.")/".$precision_devise.";";
            $js_gar .="\n\tdocument.ADForm.gar_tot".$id_doss.".value =formateMontant(document.ADForm.gar_tot".$id_doss.".value);\n";
        }
        if ($SESSION_VARS['infos_doss'][$id_doss]["gar_num_encours"] > 0) {
            $js_gar .="\n\tdocument.ADForm.gar_num_encours".$id_doss.".value=Math.round(".$SESSION_VARS['infos_prod']['prc_gar_encours']."*parseFloat(".$mnt_credit.")*".$precision_devise.")/".$precision_devise.";";
            $js_gar .="\n\tdocument.ADForm.gar_num_encours".$id_doss.".value =formateMontant(document.ADForm.gar_num_encours".$id_doss.".value);\n";
        }
        $SESSION_VARS['infos_doss'][$id_doss]['gar_num_mob'] = 0; // garanties numéraires totales mobilisées
        $SESSION_VARS['infos_doss'][$id_doss]['gar_mat_mob'] = 0; // garanties matérilles totales mobilisées

        if (is_array($SESSION_VARS['infos_doss'][$id_doss]['DATA_GAR']))
            foreach($SESSION_VARS['infos_doss'][$id_doss]['DATA_GAR'] as $key=>$value ) {
                if ($value['type'] == 1)
                    $SESSION_VARS['infos_doss'][$id_doss]['gar_num_mob'] += recupMontant($value['valeur']);
                elseif($value['type'] == 2)
                    $SESSION_VARS['infos_doss'][$id_doss]['gar_mat_mob'] += recupMontant($value['valeur']);
            }
        if ($SESSION_VARS['infos_doss'][$id_doss]["gar_num_mob"] > 0) {
            $Myform->addField("gar_num_mob".$id_doss, _("Garantie numéraire mobilisée"), TYPC_MNT);
            $Myform->setFieldProperties("gar_num_mob".$id_doss,FIELDP_IS_LABEL,true);
            $Myform->setFieldProperties("gar_num_mob".$id_doss,FIELDP_DEFAULT, $SESSION_VARS['infos_doss'][$id_doss]['gar_num_mob']);
        }
        if ($SESSION_VARS['infos_doss'][$id_doss]["gar_mat_mob"] > 0) {
            $Myform->addField("gar_mat_mob".$id_doss, _("Garantie matérielle mobilisée"), TYPC_MNT);
            $Myform->setFieldProperties("gar_mat_mob".$id_doss,FIELDP_IS_LABEL,true);
            $Myform->setFieldProperties("gar_mat_mob".$id_doss,FIELDP_DEFAULT, $SESSION_VARS['infos_doss'][$id_doss]['gar_mat_mob']);
        }

        $Myform->addField("cre_id_cpte".$id_doss, _("Compte de crédit"), TYPC_TXT);
        $cpt_cr = getAccountDatas($val_doss['cre_id_cpte']);
        $Myform->setFieldProperties("cre_id_cpte".$id_doss,FIELDP_DEFAULT,$cpt_cr["num_complet_cpte"]." ".$cpt_cr['intitule_compte']);
        $Myform->setFieldProperties("cre_id_cpte".$id_doss,FIELDP_IS_LABEL,true);
        $Myform->addField("cre_etat".$id_doss, _("Etat crédit"), TYPC_INT);
        $Myform->setFieldProperties("cre_etat".$id_doss,FIELDP_DEFAULT,getLibel("adsys_etat_credits",$val_doss['cre_etat']));
        $Myform->setFieldProperties("cre_etat".$id_doss,FIELDP_IS_LABEL,true);
        $Myform->addField("cre_nbre_reech".$id_doss, _("Nombre de rééchelonnement"), TYPC_INT);
        $Myform->setFieldProperties("cre_nbre_reech".$id_doss,FIELDP_IS_LABEL,true);
        $Myform->setFieldProperties("cre_nbre_reech".$id_doss,FIELDP_DEFAULT,$val_doss['cre_nbre_reech']);
        $Myform->addField("nbre_reechelon_auth".$id_doss, _("Nombre maximum de rééchelonnements"), TYPC_INT);
        $Myform->setFieldProperties("nbre_reechelon_auth".$id_doss,FIELDP_DEFAULT,$SESSION_VARS['infos_prod']['nbre_reechelon_auth']);
        $Myform->setFieldProperties("nbre_reechelon_auth".$id_doss,FIELDP_IS_LABEL,true);

        $SESSION_VARS['infos_doss'][$id_doss]['cap'] = 0;
        $SESSION_VARS['infos_doss'][$id_doss]['int'] = 0; //Somme des intérêts
        $SESSION_VARS['infos_doss'][$id_doss]['pen'] = 0; //Somme des pénalités

        $dateRechMor = date("d/m/Y");
        $whereCond = "WHERE (remb='f') AND (id_doss='".$id_doss."')";
        $lastEch = getEcheancier($whereCond);

        if (is_array($lastEch))
            while (list($key,$value)=each($lastEch)) {
                $SESSION_VARS['infos_doss'][$id_doss]['cap'] += $value["solde_cap"];
                $SESSION_VARS['infos_doss'][$id_doss]['int'] += $value["solde_int"];
                $SESSION_VARS['infos_doss'][$id_doss]['pen'] += $value["solde_pen"];
            }
        // FIXME : A-t-on besoin de cet appel à getLastRechMorHistorique si on appelle de nouveua getMontantExigible ?
        $reech_moratoire = getLastRechMorHistorique (145,$val_doss['id_client']);

        //Verification si le dossier de credit a ete 1 cree/2 debourse/rechelonnee/approbationReech le meme jour.
        $today = date("Y-m-d 00:00:00");

        if (($val_doss ['cre_date_debloc'] == $SESSION_VARS ['infos_doss'] [$id_doss] ['date_etat']) AND ($SESSION_VARS ['infos_doss'] [$id_doss] ['date_etat'] == $today)) {
            $SESSION_VARS ['infos_doss'] [$id_doss] ['mnt_reech'] = 0;
        } else {
            $SESSION_VARS ['infos_doss'] [$id_doss] ['mnt_reech'] = $reech_moratoire ['infos'];
        }

        //Le montant rééchelonné
        $SESSION_VARS['infos_doss'][$id_doss]['date_reech']= $reech_moratoire["date"]; //La date de mise en attente de rééchélonnement
        $MNT_EXIG = getMontantExigible($id_doss);

        // Nouveau capital = capital + montant rééchelonné
        $SESSION_VARS['infos_doss'][$id_doss]['nouveau_cap'] =  $SESSION_VARS['infos_doss'][$id_doss]['cap'] + $SESSION_VARS['infos_doss'][$id_doss]['mnt_reech'];

        $Myform->addField("mnt_cap".$id_doss, _("Montant dû en capital"), TYPC_MNT);
        $Myform->addField("mnt_reech".$id_doss,_("Montant rééchelonné"), TYPC_MNT);
        $Myform->addField("nouveau_cap".$id_doss,_("Nouveau capital"), TYPC_MNT);
        $Myform->addField("date_reech".$id_doss, "Date de demande de Rééchelonnement/Moratoire", TYPC_DTE);
        $Myform->setFieldProperties("mnt_cap".$id_doss,FIELDP_DEFAULT, $SESSION_VARS['infos_doss'][$id_doss]['cap']);
        $Myform->setFieldProperties("mnt_reech".$id_doss,FIELDP_DEFAULT, $SESSION_VARS['infos_doss'][$id_doss]['mnt_reech']);
        $Myform->setFieldProperties("nouveau_cap".$id_doss,FIELDP_DEFAULT, $SESSION_VARS['infos_doss'][$id_doss]['nouveau_cap']);
        $Myform->setFieldProperties("date_reech".$id_doss,FIELDP_DEFAULT,  $SESSION_VARS['infos_doss'][$id_doss]['date_etat']);
        $Myform->setFieldProperties("mnt_cap".$id_doss,FIELDP_IS_LABEL,true);
        $Myform->setFieldProperties("mnt_reech".$id_doss,FIELDP_IS_LABEL,true);
        $Myform->setFieldProperties("nouveau_cap".$id_doss,FIELDP_IS_LABEL,true);
        $Myform->setFieldProperties("date_reech".$id_doss,FIELDP_IS_LABEL,true);
        $Myform->setFieldProperties("cre_nbre_reech".$id_doss,FIELDP_DEFAULT,$SESSION_VARS['infos_doss'][$id_doss]['cre_nbre_reech']+1);
        $Myform->setFieldProperties("duree_mois".$id_doss,FIELDP_IS_LABEL,true);
        $Myform->setFieldProperties("differe_jours".$id_doss,FIELDP_IS_LABEL,true);
        $Myform->setFieldProperties("differe_ech".$id_doss,FIELDP_IS_LABEL,true);
        $Myform->setFieldProperties("delai_grac".$id_doss,FIELDP_IS_LABEL,true);
        $Myform->setFieldProperties("cre_mnt_octr".$id_doss,FIELDP_IS_LABEL,true);
        $Myform->setFieldProperties("cre_date_approb".$id_doss,FIELDP_IS_LABEL,true);
        $Myform->setFieldProperties("cre_date_debloc".$id_doss,FIELDP_IS_LABEL,true);
        $Myform->setFieldProperties("id_agent_gest".$id_doss,FIELDP_IS_LABEL,true);


        // Afficahge des dossiers fictifs dans le cas d'un GS avec dossier réel unique
        if ($SESSION_VARS['infos_doss'][$id_doss]['gs_cat'] == 1) {
            $js_mnt_octr = "function calculeMontant() {"; // function de calcule du montant octroyé
            $js_mnt_octr .= "var tot_mnt_octr = 0;\n";

            foreach($SESSION_VARS['infos_doss'][$id_doss]['doss_fic'] as $id_fic=>$val_fic) {
                $Myform->addHTMLExtraCode("espace_fic".$id_fic,"<BR>");
                $Myform->addField("membre".$id_fic, _("Membre"), TYPC_TXT);
                $Myform->setFieldProperties("membre".$id_fic, FIELDP_IS_REQUIRED, true);
                $Myform->setFieldProperties("membre".$id_fic, FIELDP_IS_LABEL, true);
                $Myform->setFieldProperties("membre".$id_fic,FIELDP_DEFAULT,$val_fic['id_membre']." ".getClientName($val_fic['id_membre']));
                $Myform->addField("obj_dem_fic".$id_fic, _("Objet demande"), TYPC_LSB);
                $Myform->setFieldProperties("obj_dem_fic".$id_fic, FIELDP_ADD_CHOICES, $SESSION_VARS['obj_dem']);
                $Myform->setFieldProperties("obj_dem_fic".$id_fic, FIELDP_IS_REQUIRED, true);
                $Myform->setFieldProperties("obj_dem_fic".$id_fic, FIELDP_DEFAULT, $val_fic['obj_dem']);
                $Myform->addField("detail_obj_dem_fic".$id_fic, _("Détail demande"), TYPC_TXT);
                $Myform->setFieldProperties("detail_obj_dem_fic".$id_fic, FIELDP_IS_REQUIRED, true);
                $Myform->setFieldProperties("detail_obj_dem_fic".$id_fic, FIELDP_DEFAULT, $val_fic['detail_obj_dem']);
                $Myform->addField("mnt_dem_fic".$id_fic, _("Montant demande"), TYPC_MNT);
                $Myform->setFieldProperties("mnt_dem_fic".$id_fic, FIELDP_IS_REQUIRED, true);
                $Myform->setFieldProperties("mnt_dem_fic".$id_fic,FIELDP_DEFAULT,$val_fic['mnt_dem'],true);
                $Myform->setFieldProperties("mnt_dem_fic".$id_fic,FIELDP_JS_EVENT,array("OnChange"=>"calculeMontant();"));

                $js_mnt_octr .= "tot_mnt_octr = tot_mnt_octr + recupMontant(document.ADForm.mnt_dem_fic".$id_fic.".value);\n";
            }
            $js_mnt_octr .= "document.ADForm.cre_mnt_octr".$id_doss.".value = formateMontant(tot_mnt_octr);\n";
            $js_mnt_octr .= "document.ADForm.mnt_octr".$id_doss.".value = formateMontant(tot_mnt_octr);\n";
            if ($SESSION_VARS['infos_doss'][$id_doss]["gar_num"] > 0) {
                $js_mnt_octr .="\n\tdocument.ADForm.gar_num".$id_doss.".value =formateMontant(Math.round(".$SESSION_VARS['infos_prod']['prc_gar_num']."*parseFloat(tot_mnt_octr))*".$precision_devise.")/".$precision_devise.";";
            }
            if ($SESSION_VARS['infos_doss'][$id_doss]["gar_mat"] > 0) {
                $js_mnt_octr .="\n\tdocument.ADForm.gar_mat".$id_doss.".value = formateMontant(Math.round(".$SESSION_VARS['infos_prod']['prc_gar_mat']."*parseFloat(tot_mnt_octr))*".$precision_devise.")/".$precision_devise.";";
            }
            if ($SESSION_VARS['infos_doss'][$id_doss]["gar_tot"] > 0) {
                $js_mnt_octr .="\n\tdocument.ADForm.gar_tot".$id_doss.".value = formateMontant(Math.round(".$SESSION_VARS['infos_prod']['prc_gar_tot']."*parseFloat(tot_mnt_octr))*".$precision_devise.")/".$precision_devise.";";
            }
            if ($SESSION_VARS['infos_doss'][$id_doss]["gar_num_encours"] > 0) {
                $js_mnt_octr .="\n\tdocument.ADForm.gar_num_encours".$id_doss.".value = formateMontant(Math.round(".$SESSION_VARS['infos_prod']['prc_gar_encours']."*parseFloat(tot_mnt_octr))*".$precision_devise.")/".$precision_devise.";";
            }
            $js_mnt_octr .= "}\n";
        }

        // Contrôle Javascript
        // Vérifier que le montant totat mobilisé est supérieur ou égal au montant attendu
        if ($SESSION_VARS['infos_doss'][$id_doss]['gar_tot'] > 0) {
            $gar_num_mob = "document.ADForm.gar_num_mob".$id_doss;
            $gar_mat_mob = "document.ADForm.gar_mat_mob".$id_doss;
            $gar_tot = "document.ADForm.gar_tot".$id_doss;
            if ($SESSION_VARS['infos_doss'][$id_doss]['gar_num'] > 0) {
                $gar_num = "document.ADForm.gar_num".$id_doss;
                // Vérifer que les garanties numéraires mobilisées sont supérieues aux garanties numéraires attendues
                $js_check .= "if (recupMontant($gar_num.value) > recupMontant($gar_num_mob.value)) {\n";
                $js_check .= "\tmsg += '- ".sprintf(_("Les garanties matérielles mobilisées par le dossier %s sont insuffisantes"),$id_doss)."\\n';\n";
                $js_check .= "\tADFormValid = false;\n";
                $js_check .= "}\n";
            }
            if ($SESSION_VARS['infos_doss'][$id_doss]['gar_mat'] > 0) {
                $gar_mat = "document.ADForm.gar_mat".$id_doss;
                // Vérifer que les garanties matérielle mobilisées sont supérieues aux garanties matérielle attendues
                $js_check .= "if (recupMontant($gar_mat.value) > recupMontant($gar_mat_mob.value)) {\n";
                $js_check .= "\tmsg += '- ".sprintf(_("Les garanties matérielles mobilisées par le dossier %s sont insuffisantes"),$id_doss)."\\n';\n";
                $js_check .= "\tADFormValid = false;\n";
                $js_check .= "}\n";
            }
            $js_check .= "gar_tot_mob = 0;\n";
            if ($SESSION_VARS['infos_doss'][$id_doss]['gar_num_mob'] > 0) {
                $js_check .= "gar_tot_mob += recupMontant($gar_num_mob.value);\n";
            }
            if ($SESSION_VARS['infos_doss'][$id_doss]['gar_mat_mob'] > 0) {
                $js_check .= "gar_tot_mob += recupMontant($gar_mat_mob.value);\n";
            }
            $js_check .= "if (recupMontant($gar_tot.value) > gar_tot_mob) {\n";
            $js_check .= "\tmsg += '- ".sprintf(_("Le montant total des garanties numéraires et matérielles mobilisées par le dossier %s est insuffisant"),$id_doss)."\\n';\n";
            $js_check .= "\tADFormValid = false;\n";
            $js_check .= "}\n";
        }
        //Contrôle de la date de déblocage
        if($SESSION_VARS['infos_doss'][$id_doss]["cre_date_debloc"] != "" && $SESSION_VARS['infos_doss'][$id_doss]["cre_date_debloc"] != NULL && $SESSION_VARS['infos_doss'][$id_doss]['etat'] != 7){
            $js_check.="if(('".$SESSION_VARS['infos_doss'][$id_doss]["cre_date_debloc"]."') <  ('".php2pg(date("d/m/Y"))."'))
               {
                 msg += '- ".sprintf(_("La date de déboursement du dossier %s est dépassée, Veuillez Contacter l\'agent de crédit"),$id_doss)."\\n';
                 ADFormValid = false;
               }\t";
        }
        // Vérifier que le montant à octroyer est conforme aux paramètres du produit de crédit
        $js_check .="\tif(parseFloat(".$SESSION_VARS['infos_prod']['mnt_max'].")>0){ \n";
        $js_check .="\t\tif((parseFloat(recupMontant(document.ADForm.cre_mnt_octr".$id_doss.".value)) < parseFloat(".$SESSION_VARS['infos_prod']['mnt_min'].")) || (parseFloat(recupMontant(document.ADForm.cre_mnt_octr".$id_doss.".value)) > parseFloat(".$SESSION_VARS['infos_prod']['mnt_max']."))) {  msg+='- ".sprintf(_("Le montant demandé pour le dossier %s doit être compris entre %s et %s comme défini dans le produit."),$id_doss,afficheMontant($SESSION_VARS['infos_prod']['mnt_min']),afficheMontant($SESSION_VARS['infos_prod']['mnt_max']))."';ADFormValid=false;;}\n";
        $js_check .="\t}\n";

        $js_check .="\telse if( parseFloat(recupMontant(document.ADForm.cre_mnt_octr".$id_doss.".value)) < parseFloat(".$SESSION_VARS['infos_prod']['mnt_min'].")) { msg+='- ".sprintf(_("Le montant demandé doit être au moins égal à %s comme défini dans le produit"),afficheMontant($SESSION_VARS['infos_prod']['mnt_min']))."'; ADFormValid=false;;}\n";

        // validation montant approuvé
        $js_check .="\tif(parseFloat(recupMontant(document.ADForm.cre_mnt_octr".$id_doss.".value)) > parseInt(recupMontant(document.ADForm.mnt_dem".$id_doss.".value))) { msg+='- "._("Le montant approuvé doit être au plus égal au montant demandé")."'; ADFormValid=false;}\n";


        // Vérification de la durée en mois
        $js_check .="\tif(parseInt(".$SESSION_VARS['infos_prod']['duree_max_mois'].")>0){\n";
        $js_check .="\t\tif((parseInt(document.ADForm.duree_mois".$id_doss.".value) < parseInt(".$SESSION_VARS['infos_prod']['duree_min_mois'].")) || (parseInt(document.ADForm.duree_mois".$id_doss.".value) > parseInt(".$SESSION_VARS['infos_prod']['duree_max_mois']."))) { msg+=' - ".sprintf(_("La durée du crédit doit être comprise entre %s et %s"),$SESSION_VARS['infos_prod']['duree_min_mois'],$SESSION_VARS['infos_prod']['duree_max_mois'])." "._("Comme définie dans le produit.")."\\n';ADFormValid=false;}\n";
        $js_check .="\t}else\n";
        $JS_1.="\t\tif(parseInt(document.ADForm.duree_mois".$id_doss.".value) < parseInt(".$SESSION_VARS['infos_prod']['duree_min_mois'].")) { msg+=' - ".sprintf(_("La durée du crédit doit être au moins égale à %s"),$SESSION_VARS['infos_prod']['duree_min_mois'])." "._("Comme définie dans le produit.")."\\n';ADFormValid=false;}\n";

        // Test de la durée demandée à faire uniquement si le nombre de mois de la période est > 1
        if ($adsys["adsys_duree_periodicite"][$SESSION_VARS['infos_prod']['periodicite']] > 1) {
            $js_check .= "\t\tif (parseInt(document.ADForm.duree_mois".$id_doss.".value) % parseInt(".$adsys["adsys_duree_periodicite"][$SESSION_VARS['infos_prod']['periodicite']].") != 0)
                 {
                   msg += '- ".sprintf(_("La durée doit être multiple de %s"),$adsys["adsys_duree_periodicite"][$SESSION_VARS['infos_prod']['periodicite']])."'; ADFormValid = false;
                 }\n";
        }

        // Les controls de duree :
        $msgControlDuree = _(" La nouvelle durée en mois du dossier $id_doss dois être égale ou supérieure à $duree_min");

        $js_check .="\t\t
        var nouv_duree_mois = document.ADForm.nouv_duree_mois$id_doss.value;
        \n \t\t nouv_duree_mois = parseInt(nouv_duree_mois);
        \n \t\t if(nouv_duree_mois < $duree_min) {
        \n \t\t   msg +='- $msgControlDuree\\n';
        \n \t\t   document.ADForm.nouv_duree_mois$id_doss.value = $duree_min;
        \n \t\t   ADFormValid=false;
        \n \t\t }
          ";

    } // fin parcours dossiers

    $Myform->addJS(JSP_BEGIN_CHECK,"testdateapprob",$js_date_approb);
    $Myform->addJS(JSP_FORM,"testgar",$js_gar);
    $Myform->addJS(JSP_BEGIN_CHECK,"js_check",$js_check);
    $Myform->addJS(JSP_FORM,"js_mnt_octr",$js_mnt_octr);

    // les boutons ajoutés
    $Myform->addFormButton(1,1,"valider",_("Valider"),TYPB_SUBMIT);
    if ($can_mob_gar) {
        $Myform->addFormButton(1,2,"mobiliser_gar", _("Mobilisation garanties"), TYPB_SUBMIT);
        $Myform->addFormButton(1,3,"annuler",_("Annuler"),TYPB_SUBMIT);
        $Myform->setFormButtonProperties("mobiliser_gar", BUTP_PROCHAIN_ECRAN, "Auf-6");
        $Myform->setFormButtonProperties("mobiliser_gar", BUTP_CHECK_FORM, false);
    } else
        $Myform->addFormButton(1,2,"annuler",_("Annuler"),TYPB_SUBMIT);

    // Propriétés des boutons
    $Myform->setFormButtonProperties("annuler", BUTP_PROCHAIN_ECRAN, "Gen-11");
    $Myform->setFormButtonProperties("valider", BUTP_PROCHAIN_ECRAN, "Auf-3");
    $Myform->setFormButtonProperties("valider", BUTP_CHECK_FORM, true);
    $Myform->setFormButtonProperties("annuler", BUTP_CHECK_FORM, false);

    // JavaScript
    // Initialisation de champs dèsque le champ mnt_octr est activé
    $js_mnt_reset = "function reset(id_doss) { \n";
    $js_mnt_reset .= "var cre_mnt_octr = 'cre_mnt_octr'+id_doss;\n";
    $js_mnt_reset .= "var mnt_assurance = 'mnt_assurance'+id_doss;\n";
    $js_mnt_reset .= "var mnt_commission = 'mnt_commission'+id_doss;\n";
    $js_mnt_reset .= "var gar_num ='gar_num'+id_doss;\n";
    $js_mnt_reset .= "var gar_mat ='gar_mat'+id_doss;\n";
    $js_mnt_reset .= "var gar_tot ='gar_tot'+id_doss;\n";
    $js_mnt_reset .= "var gar_num_encours ='gar_num_encours'+id_doss;\n";
    $js_mnt_reset .= "document.ADForm.eval(cre_mnt_octr).value ='';\n";
    $js_mnt_reset .= "document.ADForm.eval(mnt_assurance).value ='';\n";
    $js_mnt_reset .= "document.ADForm.eval(mnt_commission).value ='';\n";
    if ($SESSION_VARS['infos_doss'][$id_doss]["gar_num"] > 0) {
        $js_mnt_reset .= "document.ADForm.eval(gar_num).value ='';\n";
    }
    if ($SESSION_VARS['infos_doss'][$id_doss]["gar_mat"] > 0) {
        $js_mnt_reset .= "document.ADForm.eval(gar_mat).value ='';\n";
    }
    if ($SESSION_VARS['infos_doss'][$id_doss]["gar_tot"] > 0) {
        $js_mnt_reset .= "document.ADForm.eval(gar_tot).value ='';\n";
    }
    if ($SESSION_VARS['infos_doss'][$id_doss]["gar_num_encours"] > 0) {
        $js_mnt_reset .= "document.ADForm.eval(gar_num_encours).value ='';\n";
    }
    $js_mnt_reset .= "}\n";
    $Myform->addJS(JSP_FORM,"js_mnt_reset",$js_mnt_reset);

    // Calule du montant de l'assurance, de la commission et des garanties en fonction du montant à octoyer
    $js_mnt_init = "function init(id_doss) { \n";
    $js_mnt_init .= "var cre_mnt_octr = 'cre_mnt_octr'+id_doss;\n";
    $js_mnt_init .= "var mnt_octr = 'mnt_octr'+id_doss;\n";
    $js_mnt_init .= "var mnt_assurance = 'mnt_assurance'+id_doss;\n";
    $js_mnt_init .= "var mnt_commission = 'mnt_commission'+id_doss;\n";
    $js_mnt_init .= "var gar_num ='gar_num'+id_doss;\n";
    $js_mnt_init .= "var gar_mat ='gar_mat'+id_doss;\n";
    $js_mnt_init .= "var gar_tot ='gar_tot'+id_doss;\n";
    $js_mnt_init .= "var gar_num_encours ='gar_num_encours'+id_doss;\n";

    $js_mnt_init .="\t\t eval('document.ADForm.'+mnt_assurance).value = Math.round(".$SESSION_VARS['infos_prod']['prc_assurance']."*parseFloat(recupMontant(eval('document.ADForm.'+cre_mnt_octr).value))*".$precision_devise.")/".$precision_devise.";\n";
    $js_mnt_init .="\t\t\teval('document.ADForm.'+mnt_assurance).value =formateMontant(eval('document.ADForm.'+mnt_assurance).value);\n";
    $js_mnt_init .="\t\t\t eval('document.ADForm.'+mnt_commission).value = Math.round((".$SESSION_VARS['infos_prod']['prc_commission']."* parseFloat(recupMontant(eval('document.ADForm.'+cre_mnt_octr).value))+ ".$SESSION_VARS['infos_prod']['mnt_commission'].")*".$precision_devise.")/".$precision_devise.";\n";
    $js_mnt_init .="\t\t\t eval('document.ADForm.'+mnt_commission).value =formateMontant( eval ('document.ADForm.'+mnt_commission).value);\n";
    if ($SESSION_VARS['infos_doss'][$id_doss]["gar_num"] > 0) {
        $js_mnt_init .="\t\t\t eval('document.ADForm.'+gar_num).value =Math.round(".$SESSION_VARS['infos_prod']['prc_gar_num']."* parseFloat(recupMontant( eval('document.ADForm.'+cre_mnt_octr).value))*".$precision_devise.")/".$precision_devise.";\n";
        // 2116
        $js_mnt_init .="\t\t\t eval('document.ADForm.'+gar_num).value =formateMontant( eval('document.ADForm.'+gar_num).value);\n";
    }
    if ($SESSION_VARS['infos_doss'][$id_doss]["gar_mat"] > 0) {
        $js_mnt_init .="\t\t\t eval('document.ADForm.'+gar_mat).value =Math.round(".$SESSION_VARS['infos_prod']['prc_gar_mat']."* parseFloat(recupMontant( eval('document.ADForm.'+cre_mnt_octr).value)));\n";
        $js_mnt_init .="\t\t\t eval('document.ADForm.'+gar_mat).value =formateMontant( eval('document.ADForm.'+gar_mat).value);\n";
    }
    if ($SESSION_VARS['infos_doss'][$id_doss]["gar_tot"] > 0) {
        // $js_mnt_init .="\t\t\t eval('document.ADForm.'+gar_tot).value = Math.round(".$SESSION_VARS['infos_prod']['prc_gar_tot']."* parseFloat(recupMontant(  eval('document.ADForm.'+cre_mnt_octr).value))*".$precision_devise.")/".$precision_devise.";\n";
        // $js_mnt_init .="\t\t\t eval('document.ADForm.'+gar_tot).value = formateMontant( eval('document.ADForm.'+gar_tot).value);\n";
    }
    if ($SESSION_VARS['infos_doss'][$id_doss]["gar_num_encours"] > 0) {
        $js_mnt_init .="\t\t\t eval('document.ADForm.'+gar_num_encours).value =Math.round(".$SESSION_VARS['infos_prod']['prc_gar_encours']."* parseFloat(recupMontant(eval('document.ADForm.'+cre_mnt_octr).value))*".$precision_devise.")/".$precision_devise.";\n";
        $js_mnt_init .="\t\t\t eval('document.ADForm.'+gar_num_encours).value = formateMontant( eval('document.ADForm.'+gar_num_encours).value);\n";
    }
    $js_mnt_init .="\t\t\t eval('document.ADForm.'+mnt_octr).value = recupMontant(eval('document.ADForm.'+cre_mnt_octr).value);\n";
    $js_mnt_init .= "}";
    $Myform->addJS(JSP_FORM,"js_mnt_init",$js_mnt_init);

    $Myform->buildHTML();
    echo $Myform->getHTML();
}

/*}}}*/

/*{{{ Auf-3 : Affichage de l'échéancier */
else if ($global_nom_ecran == "Auf-3")
{
    $HTML_code = '';
    $JS = "\t\tassign('Auf-4');\n"; // Determination du prochain écran

    // Parcours des dossiers effectifs (dans ad_dcr)
    foreach($SESSION_VARS['infos_doss'] as $id_doss=>$val_doss)
    {
        if($val_doss["last_etat"] == 7)  // 7: En attente de Rééch/Moratoire
        {
            $differe_jours = 0;
            $differe_ech = 0;
            $SESSION_VARS['infos_doss'][$id_doss]["nouv_duree_mois"] = $ {'nouv_duree_mois'.$id_doss};

            // Echéancier
            $echeancier = calcul_echeancier_theorique($SESSION_VARS['infos_doss'][$id_doss]['id_prod'], $SESSION_VARS['infos_doss'][$id_doss]["nouveau_cap"], $SESSION_VARS['infos_doss'][$id_doss]["nouv_duree_mois"], $differe_jours, $differe_ech, NULL, 1, $id_doss);

            // Appel de l'affichage de l'échéancier
            if ($SESSION_VARS['infos_doss'][$id_doss]["cre_etat"]==2)
                $parametre["lib_date"]=_("Date de rééchelonnement");
            else if (($SESSION_VARS['infos_doss'][$id_doss]["cre_etat"]==3) || ($SESSION_VARS['infos_doss'][$id_doss]["cre_etat"]==4))
                $parametre["lib_date"]=_("Date de moratoire");

            // Récupération de l'id_ech de la dernière échéance remboursé partiellement
            //$SESSION_VARS['infos_doss'][$id_doss]['id_ech'] = getRembPartiel($val_doss['id_doss']);

            // Récupération dernière échéance remboursée
            if (!$SESSION_VARS['infos_doss'][$id_doss]['id_ech']) {
                $SESSION_VARS['infos_doss'][$id_doss]['id_ech'] = getLastEchRemb($SESSION_VARS['infos_doss'][$id_doss]["id_doss"]);
            }

            $parametre["id_client"] = $SESSION_VARS['infos_doss'][$id_doss]['id_client'];
            $parametre["index"] = $SESSION_VARS['infos_doss'][$id_doss]['id_ech']; // Index de début des n° d'echéance
            $parametre["titre"] = _("Nouvel échéancier réel de remboursement du dossier $id_doss");
            $parametre["nbre_jour_mois"]= 30;
            $parametre["montant"] = $SESSION_VARS['infos_doss'][$id_doss]["nouveau_cap"]; // Nouveau capital
            $parametre["mnt_reech"]= $SESSION_VARS['infos_doss'][$id_doss]["mnt_reech"]; //Montant rééchelonnement
            $parametre["mnt_octr"]= $SESSION_VARS['infos_doss'][$id_doss]["cre_mnt_octr"]; //Montant octroyé
            if ($SESSION_VARS['infos_doss'][$id_doss]["gar_num"] > 0 && $SESSION_VARS['infos_doss'][$id_doss]["gar_num_encours"] > 0)
                $parametre["garantie"] = $SESSION_VARS['infos_doss'][$id_doss]["gar_num"] + $SESSION_VARS['infos_doss'][$id_doss]["gar_num_encours"];
            else if ($SESSION_VARS['infos_doss'][$id_doss]["gar_num"] > 0)
                $parametre["garantie"] = $SESSION_VARS['infos_doss'][$id_doss]["gar_num"];
            else if ($SESSION_VARS['infos_doss'][$id_doss]["gar_num_encours"] > 0)
                $parametre["garantie"] = $SESSION_VARS['infos_doss'][$id_doss]["gar_num_encours"];
            $parametre["duree"] = $SESSION_VARS['infos_doss'][$id_doss]["nouv_duree_mois"]; // Nouvelle durée du crédit
            $parametre["date"] = pg2phpDate($SESSION_VARS['infos_doss'][$id_doss]["date_reech"]);// Date de rééchelonnement extrait de ad_his
            $parametre["id_prod"] = $val_doss['id_prod'];
            $parametre["id_doss"] = $id_doss;
            $parametre["differe_jours"] = $differe_jours;
            $parametre["differe_ech"] = $differe_ech;
            $parametre["EXIST"] = 0; // Vaut 0 si l'échéancier n'est stocké dans la BD 1 sinon
        } // fin si rééchelonnement

        // Génération de l'échéancier
        $HTML_code .= HTML_echeancier($parametre,$echeancier,$id_doss);
    }

    // Création du formulaire
    $formEcheancier = new HTML_GEN2();
    $formEcheancier->addJS(JSP_BEGIN_CHECK,"test",$JS);

    // les boutons ajoutés
    $formEcheancier->addFormButton(1,1,"valider",_("Valider"),TYPB_SUBMIT);
    $formEcheancier->addFormButton(1,2,"annuler",_("Annuler"),TYPB_SUBMIT);

    // Propriétés des boutons
    $formEcheancier->setFormButtonProperties("annuler", BUTP_PROCHAIN_ECRAN, "Gen-11");
    $formEcheancier->setFormButtonProperties("annuler", BUTP_CHECK_FORM, false);
    //$formEcheancier->setFormButtonProperties("valider", BUTP_PROCHAIN_ECRAN, "Auf-4");
    $formEcheancier->buildHTML();
    echo  $HTML_code;
    echo $formEcheancier->getHTML();
}
/*}}}*/


/*{{{ Auf-5 : Confirmation approbation d'un dossier de crédit */
else if ($global_nom_ecran == "Auf-4")
{
    // Préparation des dossiers à approuver
    foreach($SESSION_VARS['infos_doss'] as $id_doss=>$val_doss)
    {
        // Infos du dossier
        $DATA[$id_doss]['last_etat'] = $val_doss['last_etat'];
        $DATA[$id_doss]['etat'] = 5;   // Met le dossier de crédit à l'état fonds déboursé
        $DATA[$id_doss]['cre_etat'] = 1;    // Met le crédit à l'état sain après rééchelonnement
        $DATA[$id_doss]['cre_nbre_reech'] = getNbreReechel($id_doss) + 1; //Incrémente le nbre de rééchelonnement

        // Détermination du nouveau terme du crédit après moratoire
        if ($val_doss['cre_etat'] >=3 ) { // En souffrance (donc moratoire)
            // Court terme
            if (($val_doss['nouv_duree_mois'] >= $adsys["adsys_termes_credit"][1]['mois_min'])
                && ($val_doss["nouv_duree_mois"] <= $adsys["adsys_termes_credit"][1]['mois_max']))
                $DATA[$id_doss]['terme'] = 1;
            //Moyen terme
            elseif(($val_doss["nouv_duree_mois"] >= $adsys["adsys_termes_credit"][2]['mois_min'])
                && ($val_doss["nouv_duree_mois"] <= $adsys["adsys_termes_credit"][2]['mois_max']))
                $DATA[$id_doss]['terme'] = 2;
            //Long terme
            elseif($val_doss["nouv_duree_mois"] >= $adsys["adsys_termes_credit"][3]['mois_min'])
                $DATA['terme'] = 3;
            // Erreur
            else
                signalErreur(__FILE__,__LINE__,__FUNCTION__, _("Impossible de trouver le nouveau terme"));
        } else // Il s'agit d'un rééchelonnement, le terme ne varie pas
            $DATA[$id_doss]['terme'] = $val_doss['terme'];

        // $val_doss["id_ech"] correspond à la dernière échéance totalement ou partiellement remboursée (fonction getRembPartiel)

        $DATA[$id_doss]['infos_ech'] = array();
        if ($val_doss["id_ech"] > 0 ) {
            $DATA[$id_doss]['infos_ech']['id_doss'] = $id_doss;
            $DATA[$id_doss]['infos_ech']['id_ech'] = $val_doss['id_ech'];
            $DATA[$id_doss]['infos_ech']['date_ech'] = php2pg(date("d/m/Y"));
            $DATA[$id_doss]['infos_ech']['solde_cap'] ='0';
            $DATA[$id_doss]['infos_ech']['solde_int'] ='0';
            $DATA[$id_doss]['infos_ech']['solde_pen'] ='0';
            $DATA[$id_doss]['infos_ech']['mnt_reech'] = $val_doss["mnt_reech"];
            $DATA[$id_doss]['infos_ech']['remb'] ='t';
            $DATA[$id_doss]['id_ech_update'] = $val_doss["id_ech"];
        } else {
            $DATA[$id_doss]['infos_ech']['mnt_reech'] = $val_doss["mnt_reech"];
            $DATA[$id_doss]['id_ech_update'] = getLastEchRemb($id_doss);
        }

        $DATA[$id_doss]['maxEchNum'] = getLastEchID($id_doss); // Le id_ech de la dernière échéance
        $DATA[$id_doss]['etr'] = $val_doss['etr'];

    } // Fin parcours dossiers

    $myErr = approbationCredit($DATA);

    if ($myErr->errCode == NO_ERR) {
        $msg = new HTML_message(_("Confirmation approbation du dossier de crédit"));
        $message = _("Le dossier de crédit est passé avec succès à l'état accepté !");
        $message .= "<BR><BR>"._("N° de transaction")." : <B><code>".sprintf("%09d", $myErr->param)."</code></B>";
        $msg->setMessage($message);
        $msg->addButton(BUTTON_OK,"Gen-11");
        $msg->buildHTML();
        echo $msg->HTML_code;
    } else {
        $html_err = new HTML_erreur(_("Echec de l'approbation du dossier de crédit."));
        $html_err->setMessage(_("Erreur")." : ".$error[$myErr->errCode]."<br />"._("Paramètre")." : ".$myErr->param);
        $html_err->addButton("BUTTON_OK", 'Gen-11');
        $html_err->buildHTML();
        echo $html_err->HTML_code;
    }
}
/*}}}*/

else signalErreur(__FILE__,__LINE__,__FUNCTION__, sprintf(_("L'écran %s n'a pas pu être trouvé"), $global_nom_ecran));

?>