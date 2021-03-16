<?php
/**
 * [186] Demande modification du differé de l'echeance
 *
 * Cette opération comprends les écrans :
 * - Ade-1 : sélection d'un dossier de crédit
 * - Ade-2 : échéancier actuel à modifier
 * - Ade-3 : nouvel échéancier
 * - Ade-4 : confirmation enregistrement
 *
 * @package Credit
 */

require_once 'lib/html/HTML_GEN2.php';
require_once 'lib/html/FILL_HTML_GEN2.php';
require_once 'lib/html/HTML_erreur.php';
require_once 'lib/dbProcedures/credit.php';
require_once 'lib/misc/VariablesGlobales.php';
require_once 'lib/algo/ech_theorique.php';
require_once 'lib/html/echeancier.php';
require_once 'lib/html/suiviCredit.php';
require_once 'lib/misc/divers.php';

require_once 'lib/dbProcedures/historisation.php';

/*{{{ Ade-1 : Sélection d'un dossier de crédit */
if ($global_nom_ecran == "Ade-1") {
    unset($SESSION_VARS['infos_doss']);
    // Récupération des infos du client
    $SESSION_VARS['infos_client'] = getClientDatas($global_id_client);

    //en fonction du choix du numéro de dossier, afficher les infos avec le onChange javascript
    $codejs = "\n\nfunction getInfoDossier() {";

    $dossiers = array(); // tableau contenant les infos sur dossiers réels (dans ad_dcr) et fictifs (ad_dcr_grp_sol)
    $liste = array(); // Liste des dossiers à afficher
    $i = 1;

    // Récupération des dossiers individuels dans ad_dcr en attente de décision ou en attente de Rééch/Moratoire
    $whereCl=" AND ((etat=16))";
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
                if (($val['id_dcr_grp_sol'] == $id) AND ($val['etat'] == 16)) {
                    $date_dem = $date = pg2phpDate($val['date_dem']);
                    $infos .= "n° $id_doss "; // on affiche les numéros de dossiers réels sur une ligne
                }
            if ($infos != '') { // Si au moins on 1 dossier
                $infos .= "du $date_dem";
                $liste[$i] = $infos;
                $dossiers[$i] = $value; // on garde les infos du dossier fictif

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
    $Myform->setFormButtonProperties("valider", BUTP_PROCHAIN_ECRAN, "Ade-2");
    $Myform->setFormButtonProperties("annuler", BUTP_CHECK_FORM, false);

    $Myform->setOrder(NULL,$order);
    $Myform->buildHTML();
    echo $Myform->getHTML();
}
/*{{{ Ade-2 : échéancier courant à modifier */
elseif ($global_nom_ecran == 'Ade-2') {
    global $adsys;

    if ($SESSION_VARS['dossiers'][$HTML_GEN_LSB_id_doss]['gs_cat'] != 2 ) { // dossier individuel
        $id_doss = $SESSION_VARS['dossiers'][$HTML_GEN_LSB_id_doss]['id_doss'];
        $id_prod = $SESSION_VARS['dossiers'][$HTML_GEN_LSB_id_doss]['id_prod'];

        $SESSION_VARS['infos_doss'][$id_doss] = getDossierCrdtInfo($id_doss); // infos du dossier reel
        $SESSION_VARS['infos_doss'][$id_doss]['ech_date_dem'] = date("d/m/Y");
        $SESSION_VARS['infos_doss'][$id_doss]['last_etat'] = $SESSION_VARS['infos_doss'][$id_doss]['etat'];
    }
    elseif($SESSION_VARS['dossiers'][$HTML_GEN_LSB_id_doss]['gs_cat'] == 2 ) { // GS avec dossiers multiples
        // id du dossier fictif : id du dossier du groupe
        $id_doss_fic = $SESSION_VARS['dossiers'][$HTML_GEN_LSB_id_doss]['id'];
        $whereCond = " WHERE id = $id_doss_fic";
        $SESSION_VARS['doss_fic'] = getCreditFictif($whereCond);

        // dossiers réels des membre du GS
        $dossiers_membre = getDossiersMultiplesGS($global_id_client);
        foreach($dossiers_membre as $id_doss=>$val) {
            if ($val['id_dcr_grp_sol'] == $SESSION_VARS['dossiers'][$HTML_GEN_LSB_id_doss]['id']) {
                $SESSION_VARS['infos_doss'][$id_doss] = $val; // infos d'un dossier reel d'un membre
                $SESSION_VARS['infos_doss'][$id_doss]['last_etat'] = $SESSION_VARS['infos_doss'][$id_doss]['etat'];
                $SESSION_VARS['infos_doss'][$id_doss]['cre_mnt_octr'] = $SESSION_VARS['infos_doss'][$id_doss]['cre_mnt_octr'];
                $id_prod = $SESSION_VARS['infos_doss'][$id_doss]['id_prod']; // même produit pour tous les dossiers
            }
        }
    }

    /*
    $id_doss = $SESSION_VARS['dossiers'][$HTML_GEN_LSB_id_doss]['id_doss'];
    $id_prod = $SESSION_VARS['dossiers'][$HTML_GEN_LSB_id_doss]['id_prod'];

    $SESSION_VARS['infos_doss'][$id_doss] = getDossierCrdtInfo($id_doss); // infos du dossier reel
    */

    // Les informations sur le produit de crédit
    $Produit = getProdInfo(" where id =".$id_prod, $id_doss);
    $SESSION_VARS['infos_prod'] = $Produit[0];

    // Création du formulaire
    $Myform = new HTML_GEN2(_("Approbation de la differé échéance"));
    $js_duree = '';
    $HTML_code = '';
    $checkDate = '';

    foreach($SESSION_VARS['infos_doss'] as $id_doss=>$val_doss) {
        $SESSION_VARS["id_prod"] = $val_doss["id_prod"];
        $SESSION_VARS["cre_etat"] = $val_doss["cre_etat"];
        $SESSION_VARS["garantie"] = $val_doss["gar_num"];
        $SESSION_VARS["last_duree_mois"] = $val_doss["duree_mois"];
        $SESSION_VARS["cre_mnt_octr"] = $val_doss["cre_mnt_octr"];
        $SESSION_VARS["cre_date_debloc"] = $val_doss["cre_date_debloc"];
        $SESSION_VARS["cpte_credit"] = $val_doss["cre_id_cpte"];

        // Retourne les informations sur l'échéancier passé et non remboursé
        $SESSION_VARS['infos_doss'][$id_doss]['cap'] = 0;
        $SESSION_VARS['infos_doss'][$id_doss]['int'] = 0;
        $SESSION_VARS['infos_doss'][$id_doss]['pen'] = 0;

        $SESSION_VARS['infos_doss'][$id_doss]['last_etat'] = $SESSION_VARS['infos_doss'][$id_doss]['etat'];

        // Recup la date souhaité de remboursement
        $his_obj = Historisation::getListDossierHis($id_doss, 4, 'f');

        $SESSION_VARS['differe_ech'] = $his_obj[$id_doss]['diff_ech'];
        // Retourne les informations sur l'échéancier passé sous réserve de la date du jour de rééchelonnement
        $dateRechMor = date("d/m/Y");
        $whereCond="WHERE (remb='f') AND (id_doss='".$id_doss."')";
        $lastEch = getEcheancier($whereCond);

        if (is_array($lastEch))
            while (list($key,$value)=each($lastEch)) {
                $SESSION_VARS['infos_doss'][$id_doss]['cap'] += $value["solde_cap"];
                $SESSION_VARS['infos_doss'][$id_doss]['int'] += $value["solde_int"];  //Somme des intérêts
                $SESSION_VARS['infos_doss'][$id_doss]['pen'] += $value["solde_pen"];  //Somme des pénalités
            }

        // Recherche du montant rééchelonné (= intérêts exigibles + pénalités)
        $MNT_EXIG = getMontantExigible($id_doss);
        $SESSION_VARS['infos_doss'][$id_doss]['mnt_reech'] = 0; //$MNT_EXIG["int"] + $MNT_EXIG["pen"];

        $SESSION_VARS['infos_doss'][$id_doss]["nouveau_cap"] = $SESSION_VARS['infos_doss'][$id_doss]['cap'] + $SESSION_VARS['infos_doss'][$id_doss]['mnt_reech'];  // Nouveau capital = capital + montant rééchelonné

        // Calcul de la nouvelle garantie attendue
        $SESSION_VARS['infos_doss'][$id_doss]["garantie"] = $SESSION_VARS['infos_prod']['prc_gar_tot'] * $SESSION_VARS['infos_doss'][$id_doss]["nouveau_cap"];

        // Ajout des champs
        $nom_cli = getClientName($val_doss['id_client']);
        $Myform->addHTMLExtraCode("espace".$id_doss,"<b><p align=center><b> ".sprintf(_("Differé des échéances du dossier N° %s de %s"),$id_doss,$nom_cli)."</b></p>");

        $HTML_code = '';
        if($val_doss["etat"] == 16) {

            $whereCond = "WHERE id_doss = $id_doss";
            $echeancier = getEcheancier($whereCond);// L'échéancier
            $reechMorat = getRechMorHistorique (145,$val_doss['id_client'],$val_doss["date_dem"]); //Date demande car date rééch > date demande
            $parametre=array ();
            $cap_du =0;  //Capital dû (Cap remb. + Cap restant dû)
            $int_du =0;  //Intérêt dû
            $gar_du =0;  //Garantie dûe
            $Nbre_Ech =0; //Nbre d'échéance

            $cap_rest =0;  //Capital restant
            $int_rest =0;  //Intérêt restant
            $gar_rest =0;  //Garantie restante
            $Nbre_rest =0; //Nbre d'échéance restant
            $i=0;

            //Echéancier de remboursement
            if (is_array($reechMorat)) {
                reset($reechMorat);
                list($key,$historique) = each($reechMorat);
            }
            while (list($key,$value)=each($echeancier)) {
                $AMJ_ech = pg2phpDatebis($value["date_ech"]);//Tableau aaaa/mm/jj de la date
                $sdEch = gmmktime(0,0,0,$AMJ_ech[0],$AMJ_ech[1],$AMJ_ech[2],1);  //0 mois 1 jour 2 année

                $AMJ_his = pg2phpDatebis($historique["date"]);
                $sdhis = gmmktime(0,0,0,$AMJ_his[0],$AMJ_his[1],$AMJ_his[2],1);  //0 mois 1 jour 2 année

                if (($sdEch > $sdhis) && ($sdhis > 0) && ($sdhis!=$lasthis) && ($val_doss["cre_nbre_reech"] > 0)) { //Réechelonnement /Moratoire
                    $lasthis = $sdhis;
                    list($key,$historique) = each($reechMorat);
                }

                //Calcul des pénalités attendues
                $pen_remb='0'; //Pénalité remboursé
                $cap_remb='0'; //Capital remboursé pour l'échéance i
                $int_remb='0'; //Intérêt remboursé pour l'échéance i
                $gar_remb='0'; //Garantie remboursée pour l'échéance i
                $som_cap_remb='0';//Capital remboursé
                $som_int_remb='0';//Intérêt remboursé

                $REMB = getRemboursement("WHERE id_doss = ".$id_doss." AND id_ech = ".$value["id_ech"]); //Les remboursements
                // Cas particlier des crédits repris
                if ((sizeof($REMB) == 0) && ($value["remb"] == 't')) {
                    $cap_remb="N/A"; // Capital remboursé
                    $int_remb="N/A"; // Intérêt remboursé
                    $pen_remb="N/A"; // Pénalité remboursée
                    $gar_remb="N/A"; // Garantie remboursée
                } else {
                    while (list($num_remb, $remb) = each($REMB)) {
                        $som_cap_remb += $remb["mnt_remb_cap"]; //Somme des capitaux remboursés
                        $som_int_remb += $remb["mnt_remb_int"]; //Somme des intérêts remboursés
                        $pen_remb += $remb["mnt_remb_pen"]; //Pénalités remboursées pour l'échéance i
                        $cap_remb += $remb["mnt_remb_cap"]; //Capital remboursé pour l'échéance i
                        $int_remb += $remb["mnt_remb_int"]; //Intérêts remboursés pour l'échéance i
                        $gar_remb += $remb["mnt_remb_gar"]; //Intérêts remboursés pour l'échéance i
                    }
                }

                if ($value["remb"]=='f') {
                    $cap_rest += $value["solde_cap"];  //Capital restant à payer
                    $int_rest += $value["solde_int"];  //Intérêt restant à payer
                    $gar_rest += $value["solde_gar"];  //Garantie restant à payer
                }
                $cap_du = $cap_du + $value["mnt_cap"];
                $int_du = $int_du + $value["mnt_int"];
                $gar_du = $gar_du + $value["mnt_gar"];
                $Nbre_Ech = $value["id_ech"];
                if ($value["remb"] =='t')
                    $i++;
            }

            $Nbre_rest= $Nbre_Ech-$i; //Nbre d'échéances non clôturé
            $parametre["titre"]= _("Echéancier existant Dossier de crédit N°")." ".$id_doss;
            $parametre["id_doss"] = $id_doss;
            $parametre["cap_du"] = $cap_du; //$som_cap_remb+$cap_rest;
            $parametre["int_du"] = $int_du; //$som_int_remb+$int_rest;
            $parametre["gar_du"] = $gar_du; //$som_gar_remb+$gar_rest;
            $parametre["Nbre_Ech"] = $Nbre_Ech;
            $parametre["Nbre_rest"] = $Nbre_rest;
            $parametre["cap_rest"] = $cap_rest;
            $parametre["int_rest"] = $int_rest;
            $parametre["gar_rest"] = $gar_rest;
            $parametre["cre_retard_etat_max"] = getLibel("adsys_etat_credits", $val_dos['cre_retard_etat_max']);
            $parametre["cre_retard_etat_max_jour"] = $val_doss['cre_retard_etat_max_jour'];
            $parametre["prov_mnt"]=$val_doss["prov_mnt"];
            if ($val_doss["etat"] == 13 ) {
                $parametre["cre_mnt_deb"]=$val_doss["cre_mnt_deb"];
            }
            $HTML_code .=  HTML_suiviCredit($parametre,null);

            $Myform->addHTMLExtraCode("html_code".$id_doss, "<br />".$HTML_code."<br />");
        }


//        $Myform->addHTMLExtraCode("espace".$id_doss,"<b><p align=center><b> ".sprintf(_("Dossier de crédit N° %s de %s"), $id_doss, $nom_cli)."</b></p>");

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
        $Myform->addField("etat".$id_doss, _("Etat du dossier"), TYPC_TXT);
        $Myform->setFieldProperties("etat".$id_doss,FIELDP_DEFAULT,adb_gettext($adsys["adsys_etat_dossier_credit"][$val_doss['etat']]));
        $Myform->setFieldProperties("etat".$id_doss,FIELDP_IS_LABEL,true);
        $Myform->addField("date_etat".$id_doss, _("Date état du dossier"), TYPC_DTE);
        $Myform->setFieldProperties("date_etat".$id_doss,FIELDP_IS_LABEL,true);
        $Myform->setFieldProperties("date_etat".$id_doss,FIELDP_DEFAULT,$val_doss['date_etat']);
        $Myform->addField("cre_id_cpte".$id_doss, _("Compte de crédit"), TYPC_TXT);
        $Myform->setFieldProperties("cre_id_cpte".$id_doss,FIELDP_DEFAULT,$val_doss['cre_id_cpte']);
        $Myform->setFieldProperties("cre_id_cpte".$id_doss,FIELDP_IS_LABEL,true);
        $Myform->addField("cre_etat".$id_doss, _("Etat crédit"), TYPC_INT);
        $Myform->setFieldProperties("cre_etat".$id_doss,FIELDP_DEFAULT,getlibel("adsys_etat_credits",$val_doss['cre_etat']));
        $Myform->setFieldProperties("cre_etat".$id_doss,FIELDP_IS_LABEL,true);

        $Myform->addField("cre_mnt_octr".$id_doss, _("Montant octroyé"), TYPC_MNT);
        $Myform->setFieldProperties("cre_mnt_octr".$id_doss,FIELDP_IS_REQUIRED,true);
        $Myform->setFieldProperties("cre_mnt_octr".$id_doss,FIELDP_IS_LABEL,false);
        $Myform->setFieldProperties("cre_mnt_octr".$id_doss,FIELDP_DEFAULT,$val_doss['cre_mnt_octr']);

        //type de durée : en mois ou en semaine
        $type_duree = $SESSION_VARS['infos_prod']['type_duree_credit'];
        $libelle_duree = mb_strtolower(adb_gettext($adsys['adsys_type_duree_credit'][$type_duree])); // libellé type durée en minuscules
        $Myform->addField("duree_mois".$id_doss, _("Durée en ".$libelle_duree), TYPC_INT);
        $Myform->setFieldProperties("duree_mois".$id_doss,FIELDP_IS_REQUIRED,true);
        $Myform->setFieldProperties("duree_mois".$id_doss,FIELDP_IS_LABEL,false);
        $Myform->setFieldProperties("duree_mois".$id_doss,FIELDP_DEFAULT,$val_doss['duree_mois']);

        $Myform->addField("nmbr_diff_ech".$id_doss, _("Nombre de differé en échéance"), TYPC_TXT);
        $Myform->setFieldProperties("nmbr_diff_ech".$id_doss,FIELDP_IS_REQUIRED,true);
        $Myform->setFieldProperties("nmbr_diff_ech".$id_doss,FIELDP_IS_LABEL,false);
        $Myform->setFieldProperties("nmbr_diff_ech".$id_doss,FIELDP_DEFAULT,$SESSION_VARS['differe_ech']);

        $curr_ech_row = getDernierEcheanceNonRemb($id_doss); // Récupéré les infos de la dernière échéance non remboursée
        $curr_ech_arr = pg2phpDatebis($curr_ech_row['date_ech']); // Récupéré la date de l'échéance

        //Les champs obligatoires
        $Myform->setFieldProperties("id_prod".$id_doss,FIELDP_IS_REQUIRED,false);
        $Myform->setFieldProperties("id_doss".$id_doss,FIELDP_IS_REQUIRED,false);
        $Myform->setFieldProperties("duree_mois".$id_doss,FIELDP_IS_REQUIRED,false);
        $Myform->setFieldProperties("cre_mnt_octr".$id_doss,FIELDP_IS_REQUIRED,false);
        $Myform->setFieldProperties("cre_etat".$id_doss,FIELDP_IS_REQUIRED,false);
        $Myform->setFieldProperties("etat".$id_doss,FIELDP_IS_REQUIRED,false);

        $Myform->setFieldProperties("duree_mois".$id_doss,FIELDP_IS_LABEL,true);
        $Myform->setFieldProperties("cre_id_cpte".$id_doss,FIELDP_IS_LABEL,true);
        $Myform->setFieldProperties("date_etat".$id_doss,FIELDP_IS_LABEL,true);
        $Myform->setFieldProperties("id_doss".$id_doss,FIELDP_IS_LABEL,true);
        $Myform->setFieldProperties("etat".$id_doss,FIELDP_IS_LABEL,true);
        $Myform->setFieldProperties("id_prod".$id_doss,FIELDP_IS_LABEL,true);
        $Myform->setFieldProperties("cre_mnt_octr".$id_doss,FIELDP_IS_LABEL,true);
        $Myform->setFieldProperties("cre_etat".$id_doss,FIELDP_IS_LABEL,true);
        $Myform->setFieldProperties("etat".$id_doss,FIELDP_IS_LABEL,true);
        $Myform->setFieldProperties("nmbr_diff_ech".$id_doss,FIELDP_IS_LABEL,true);

    } // Fin parcours dossiers

    // Permet d'ouvrir la page de détail du remboursement
    $JScode="";
    $JScode .="\nfunction open_remb(id_doss,id_ech)";
    $JScode .="\t{\n";
    $JScode .="\t	// "._("Construction de l'URL : de type")." ./lib/html/detailremb.php?m_agc=".$_REQUEST['m_agc']."&id_ech=id\n";
    $JScode .="\t\turl = '../lib/html/detailremb.php?m_agc=".$_REQUEST['m_agc']."&id_doss='+id_doss+'&id_ech='+id_ech;\n";
    $JScode .="\t\tRembWindow = window.open(url, '"._("Produit sélectionné")."', 'alwaysRaised=1,dependent=1,scrollbars,resizable=0,width=650,height=400');\n";
    $JScode .="\t}\n";
    $Myform->addJS(JSP_FORM,"prodF",$JScode);

    // les boutons ajoutés
    $Myform->addFormButton(1,1,"valider",_("Valider"),TYPB_SUBMIT);
    $Myform->addFormButton(1, 2, "back", _("Retour Menu"), TYPB_SUBMIT);

    // Propriétés des boutons
    $Myform->setFormButtonProperties("valider", BUTP_PROCHAIN_ECRAN, "Ade-3");
    $Myform->setFormButtonProperties("back", BUTP_PROCHAIN_ECRAN, "Mec-1");
    $Myform->setFormButtonProperties("back", BUTP_CHECK_FORM, false);
    $Myform->buildHTML();

    echo $Myform->getHTML();
    //xdebug_dump_superglobals();

}
elseif ($global_nom_ecran == 'Ade-3') {
    require_once 'lib/algo/ech_dyn.php';

    $id_prod = $SESSION_VARS['infos_prod']['id'];
    $differe_ech = intval($SESSION_VARS['differe_ech']);
    $HTML_code = '';
    unset($SESSION_VARS['echeancier']);
    //$formEcheancier = new HTML_GEN2();

    foreach($SESSION_VARS['infos_doss'] as $id_doss=>$val_doss) {
        //${'ech_date_dem'.$id_doss}
        $curr_ech_row = getDernierEcheanceNonRemb($id_doss, "AND date_ech >= DATE('".date("Y/m/d")."')"); // Récupéré les infos de la dernière échéance non remboursée
        $echeance_index = $curr_ech_row['id_ech']; // Récupéré l'ID échéance
        $SESSION_VARS["mod_diff_ech$id_doss"]['echeance_index'] = $echeance_index; // Store echeance index in session
        $SESSION_VARS["nmbr_diff_ech$id_doss"] = $differe_ech;

        //date ech
        $curr_ech_date =  pg2phpDate($curr_ech_row['date_ech']);
        $curr_ech_arr = pg2phpDatebis($curr_ech_row['date_ech']); // Récupéré la date de l'échéance

        $duree_mois = $val_doss['duree_mois']; // Récupéré la durée du dossier de crédit
        if(isset($val_doss['nouv_duree_mois']) && $val_doss['nouv_duree_mois'] > 0) {
            $duree_mois = $val_doss['nouv_duree_mois']; // Récupéré la durée du dossier de crédit si déjà rééch
        }

        $capital = $val_doss['cre_mnt_octr']; // Récupéré le montant octroyer
        $interet_actuelle = $curr_ech_row['solde_int'];

        $differe_jours = 0;
        $interets_attendus = 0;

        $echeancierNonRemb = getEcheancier("WHERE id_doss=$id_doss AND id_ech>=$echeance_index AND remb='f'");

        /****************************** AT-186 **********************************/
        $date_offset = "";
        switch($SESSION_VARS['infos_prod']['periodicite']){
            case '1':
                $date_offset = '+1 month';
                break;
            case '2':
                $date_offset = '+15 day';
                break;
            case '3':
                $date_offset = '+3 month';
                break;
            case '4':
                $date_offset = '+6 month';
                break;
            case '5':
                $date_offset = '+12 month';
                break;
            case '6':
                $date_offset = '+0 day';
                break;
            case '7':
                $date_offset = '+2 month';
                break;
            case '8':
                $date_offset = '+1 week';
                break;
            default:
        }

        for($pos = 0; $pos < $differe_ech; $pos++){
            $mod_ech = $echeancierNonRemb[0];
            $mod_ech['mnt_cap'] = 0;
            $mod_ech['solde_cap'] = 0;
            $mod_ech['is_differe'] = true;

            if($SESSION_VARS['infos_prod']['appl_interet_diff_echeance'] == 0){
                $mod_ech['mnt_int'] = 0;
                $mod_ech['solde_int'] = 0;
            }
            array_splice($echeancierNonRemb, $pos, 0,array($mod_ech));
        }

        for($index = 1; $index < count($echeancierNonRemb) ; $index++){
            $ndate = strtotime(str_replace('/', '-', pg2phpDate($echeancierNonRemb[$index - 1]['date_ech'])));
            $echeancierNonRemb[$index]['date_ech'] = date("Y-m-d", strtotime($date_offset, $ndate));
            $echeancierNonRemb[$index]['id_ech'] = intval($echeancierNonRemb[$index - 1]['id_ech']) + 1;
            if(empty($echeancierNonRemb[$index]['is_differe'])){
                $echeancierNonRemb[$index]['is_differe'] = false;
            }
        }

        $SESSION_VARS['echeancier'][$id_doss] = $echeancierNonRemb;
        /****************************** FIN AT-186 **********************************/

        // Appel de l'affichage de l'échéancier
        $parametre["lib_curr_date"] = _("Date du jour");
        $parametre["index"] = $echeance_index;//Index de début de l'échéancier
        $parametre["titre"] = _("Nouvelle Echéancier théorique de remboursement du Dossier de crédit N°")." ".$id_doss;
        $parametre["nbre_jour_mois"] = 30; // FIXME : En dur ?????
        $parametre["montant"] = recupMontant($capital);
        $parametre["mnt_reech"] = 0; //Montant rééchelonnement
        $parametre["mnt_octr"] = $parametre["montant"]; //Montant octroyé
        $parametre["mnt_frais_doss"] = $SESSION_VARS['infos_prod']["mnt_frais"];
        $parametre["mnt_commission" ]= $parametre["montant"]*$SESSION_VARS['infos_prod']["prc_commission"];
        $parametre["mnt_assurance"] = $parametre["montant"]*$SESSION_VARS['infos_prod']["prc_assurance"];
        $parametre["garantie"] = $parametre["montant"]*$SESSION_VARS['infos_prod']["prc_gar_num"]+$parametre["montant"]*$SESSION_VARS['infos_prod']["prc_gar_encours"];
        $parametre["garantie_mat"] = $parametre["montant"]*$SESSION_VARS['infos_prod']["prc_gar_mat"];
        $parametre["duree"] = $duree_mois; //Nouvelle durée du crédit
        //$parametre["date"] = $val_doss['cre_date_debloc'];
        $parametre["id_prod"] = $id_prod;
        $parametre["id_doss"] = $val_doss['id_doss'];
        $parametre["differe_jours"] = $differe_jours;
        $parametre["differe_ech"] = $differe_ech;
        $parametre["EXIST"] = 1; // Vaut 0 si l'échéancier n'est pas stocké dans la BD 1 sinon
        $parametre["id_client"] = $val_doss['id_client']; // L'identifiant du client

        $HTML_code .= HTML_new_echeancier($parametre, $echeancierNonRemb);
    }

    $formEcheancier = new HTML_GEN2();

    // les boutons ajoutés
    $formEcheancier->addFormButton(1,1,"valider",_("Valider"),TYPB_SUBMIT);
    $formEcheancier->addFormButton(1,2,"retour",_("Retour Menu"),TYPB_SUBMIT);

    $formEcheancier->setFormButtonProperties("valider", BUTP_PROCHAIN_ECRAN, "Ade-4");
    $formEcheancier->setFormButtonProperties("retour", BUTP_PROCHAIN_ECRAN, "Mec-1");
    $formEcheancier->setFormButtonProperties("retour", BUTP_CHECK_FORM, false);

    $formEcheancier->buildHTML();
    echo  $HTML_code;
    echo $formEcheancier->getHTML();
}
/*{{{ Ade-4 : Confirmation */
elseif ($global_nom_ecran == "Ade-4") {

    global $dbHandler, $global_id_agence, $global_nom_login;

    $id_his = NULL;
    $has_error = FALSE;
    $id_doss_arr = array();

    foreach ($SESSION_VARS['infos_doss'] as $id_doss => $val_doss) {

        //Remplissage de $DATA avec les données concernant la mise à jour du dossier de crédit
        $DATA["id_doss"] = $id_doss;
        $DATA["id_client"] = $val_doss['id_client'];
        $DATA["echeance_index"] = $SESSION_VARS["mod_diff_ech$id_doss"]['echeance_index'];
        $DATA["nmbr_echeance_differe"] = $SESSION_VARS["nmbr_diff_ech$id_doss"];

        if (is_array($DATA) && count($DATA) > 0) {
            if (is_array($SESSION_VARS['echeancier'][$id_doss]) && count($SESSION_VARS['echeancier'][$id_doss]) > 0) {

                // Update table echeance
                $db = $dbHandler->openConnection();

                for($index = 0; $index < (count($SESSION_VARS['echeancier'][$id_doss]) - $DATA["nmbr_echeance_differe"]); $index++) {
                    $appl_interet_diff_echeance = intval($SESSION_VARS['infos_prod']['appl_interet_diff_echeance']);
                    $Fields = $Where = array();
                    $Fields['id_ech'] = $SESSION_VARS['echeancier'][$id_doss][$index]['id_ech'];
                    $Fields['date_ech'] = $SESSION_VARS['echeancier'][$id_doss][$index]['date_ech'];
                    $Fields['mnt_cap'] = $SESSION_VARS['echeancier'][$id_doss][$index]['mnt_cap'];
                    $Fields['mnt_int'] = $SESSION_VARS['echeancier'][$id_doss][$index]['mnt_int'];
                    $Fields['mnt_gar'] = $SESSION_VARS['echeancier'][$id_doss][$index]['mnt_gar'];
                    $Fields['mnt_reech'] = $SESSION_VARS['echeancier'][$id_doss][$index]['mnt_reech'];
                    $Fields['remb'] = (($appl_interet_diff_echeance == 0) && $SESSION_VARS['echeancier'][$id_doss][$index]['is_differe'])?'t':'f';
                    $Fields['solde_cap'] = $SESSION_VARS['echeancier'][$id_doss][$index]['solde_cap'];
                    $Fields['solde_int'] = $SESSION_VARS['echeancier'][$id_doss][$index]['solde_int'];
                    $Fields['solde_gar'] = $SESSION_VARS['echeancier'][$id_doss][$index]['solde_gar'];
                    $Fields['solde_pen'] = $SESSION_VARS['echeancier'][$id_doss][$index]['solde_pen'];
                    $Fields['id_ag'] = $SESSION_VARS['echeancier'][$id_doss][$index]['id_ag'];
                    $Fields['date_creation'] = $SESSION_VARS['echeancier'][$id_doss][$index]['date_creation'];
                    $Fields['date_modif'] = date('r');

                    $Where["id_doss"] = $SESSION_VARS['echeancier'][$id_doss][$index]['id_doss'];
                    $Where["id_ech"] = $SESSION_VARS['echeancier'][$id_doss][$index]['id_ech'];
                    $Where["id_ag"] = $global_id_agence;

                    unset($SESSION_VARS['echeancier'][$id_doss][$index]['is_differe']);

                    $sql = buildUpdateQuery("ad_etr", $Fields, $Where);

                    $result = $db->query($sql);
                    if (DB::isError($result)) {
                        $dbHandler->closeConnection(false);
                        signalErreur(__FILE__, __LINE__, __FUNCTION__, _("Erreur dans la requête SQL") . " : " . $sql);
                    }
                }

                $dbHandler->closeConnection(true);
                $db = $dbHandler->openConnection();

                $end_index = count($SESSION_VARS['echeancier'][$id_doss]) - $DATA["nmbr_echeance_differe"];
                for($x = $end_index; $x < count($SESSION_VARS['echeancier'][$id_doss]); $x++) {
                    unset($SESSION_VARS['echeancier'][$id_doss][$x]['is_differe']);
                    $sql_insert = buildInsertQuery("ad_etr", $SESSION_VARS['echeancier'][$id_doss][$x]);

                    $result_insert = $db->query($sql_insert);
                    if (DB::isError($result_insert)) {
                        $dbHandler->closeConnection(false);
                        signalErreur(__FILE__, __LINE__, __FUNCTION__, _("Erreur dans la requête SQL") . " : " . $sql_insert);
                    }
                }

                $dbHandler->closeConnection(true);
                $db = $dbHandler->openConnection();

                $sql = buildUpdateQuery("ad_dcr", array('diff_ech_apres_deb' => $DATA["nmbr_echeance_differe"]), array('id_doss' => $DATA["id_doss"]));

                $result = $db->query($sql);
                if (DB::isError($result)) {
                    $dbHandler->closeConnection(false);
                    signalErreur(__FILE__, __LINE__, __FUNCTION__, _("Erreur dans la requête SQL") . " : " . $sql);
                }

                $dbHandler->closeConnection(true);
            }

            $UPDATE_DATA["etat"] = 5;
            $UPDATE_DATA["date_etat"] = date('r');

            if (updateCredit($DATA["id_doss"], $UPDATE_DATA)) {

                if ($id_his == NULL) {
                    // Log action
                    $myErr = ajout_historique(104, $DATA["id_client"], "Approbation differé des échéances (" . $DATA["id_doss"] . ")", $global_nom_login, date("r"), NULL);

                    if ($myErr->errCode != NO_ERR) {
                        $dbHandler->closeConnection(false);
                        return $myErr;
                    } else {
                        $id_his = $myErr->param;
                    }
                }

                $HisObj = new Historisation();

                $erreur = $HisObj->updateDossierHis($DATA["id_doss"], 4, 't', $DATA["echeance_index"]);

                if ($erreur->errCode == NO_ERR) {
                    $id_doss_arr[] = $DATA["id_doss"];
                } else {
                    $has_error == TRUE;
                    break;
                }

                unset($HisObj);
            }
        }
    }

    if ($has_error == FALSE) {
        $msg = new HTML_message(_("Confirmation approbation de differé des échéances"));

        if (is_array($id_doss_arr) && count($id_doss_arr) > 0) {

            $list_id_doss = "";
            for ($x = 0; $x < count($id_doss_arr); $x++) {
                $list_id_doss .= "N° " . $id_doss_arr[$x] . " ";
            }

            $msg->setMessage(_("La modification de differé des échéance <br />pour les Dossiers de crédit " . $list_id_doss . "<br />ont été effectuées avec succès !"));
        } else {
            $msg->setMessage(_("La modification de differé des échéance <br />du dossier de crédit N°" . $id_doss_arr[0] . " a été effectuée avec succès !"));
        }
        $msg->addButton(BUTTON_OK, "Gen-11");
        $msg->buildHTML();
        echo $msg->HTML_code;
    } else {
        $html_err = new HTML_erreur(_("Echec de l'approbation demande de differé des échéance"));
        $html_err->setMessage(_("Erreur") . " : " . $error[$erreur->errCode] . "<br />" . _("Paramètre") . " : " . $erreur->param);
        $html_err->addButton("BUTTON_OK", 'Gen-11');
        $html_err->buildHTML();
        echo $html_err->HTML_code;
    }
}
?>