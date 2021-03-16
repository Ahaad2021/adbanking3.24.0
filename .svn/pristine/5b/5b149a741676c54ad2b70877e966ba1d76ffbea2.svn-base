<?php
    require_once('lib/dbProcedures/client.php');
    require_once('lib/misc/tableSys.php');
    require_once('lib/dbProcedures/historique.php');
    require_once('lib/html/FILL_HTML_GEN2.php');
    require_once 'lib/html/HTML_champs_extras.php';
    require_once('lib/algo/ech_theorique.php');
    require_once('lib/html/echeancier.php');
    require_once('lib/html/suiviCredit.php');
    require_once('lib/misc/divers.php');
    require_once "modules/rapports/xml_clients.php";
    require_once 'modules/rapports/xslt.php';

    //recuperation des données de l'agence'
    global $global_id_agence;

    $CLI = getClientHistoric($id_cli_hist);
    $AGC = getAgenceDatas($global_id_agence);
    $ad_cli_hist_tablen = getTableliste('ad_cli_hist');
    $ad_cli_tablen = getTableliste('ad_cli');

    updateTableliste(array('tablen' => $ad_cli_hist_tablen), array('tablen' => $ad_cli_tablen));

    $myForm = new HTML_GEN2(_("Consultation du client"));
    $html_js .= "<script src=\"/lib/misc/js/lib/jquery.min.js\" type=\"text/javascript\"></script>";
    $myForm->addHTMLExtraCode("html_js",$html_js);

    $Order = array ("etat", "nbre_parts", "statut_juridique", "qualite", "id_client", "anc_id_client","matricule", "date_adh", "date_crea", "langue_correspondance", "gestionnaire");
    $labels = array("id_client" => "", "statut_juridique" => "", "etat" => "");
    if ( $CLI["statut_juridique"] == 1) {     // Personne physique
        global $global_photo_client, $global_signature_client;
        $myForm->addField("signature",_("Spécimen de signature"),TYPC_IMG);
        $myForm->setFieldProperties('signature', FIELDP_IMAGE_URL, $CLI["signature_path"]);
        $myForm->addField("photo",_("Photographie"),TYPC_IMG);
        $myForm->setFieldProperties('photo', FIELDP_IMAGE_URL, $CLI["photo_path"]);
        array_push($Order, "pp_nom", "pp_prenom", "pp_date_naissance");
        array_push($Order, "pp_lieu_naissance", "pp_nationalite", "pp_pays_naiss", "pp_sexe", "pp_type_piece_id", "pp_nm_piece_id", "pp_date_piece_id", "pp_lieu_delivrance_id", "pp_date_exp_id", "pp_etat_civil", "pp_nbre_enfant");
        if ($AGC['identification_client'] == 2){
            array_push($Order, "adresse", "code_postal", "ville", "pays","num_tel", "num_fax", "num_port", "email","province","district","secteur","cellule","village", "client_zone", "education", "classe_socio_economique");
        }else{
            array_push($Order, "adresse", "code_postal", "ville", "pays","num_tel", "num_fax", "num_port", "email", "id_loc1", "id_loc2", "loc3", "client_zone");
        }

        array_push($Order, "sect_act", "pp_pm_activite_prof", "pp_fonction", "pp_employeur","pp_partenaire","categorie","classe", "langue", "pp_revenu", "pp_pm_patrimoine", "pp_casier_judiciaire", "pp_id_gi", "nb_imf", "nb_bk", "commentaires_cli","mnt_quotite","id_card");

    } else if ($CLI["statut_juridique"] == 2) { // Personne morale
        if ($AGC['identification_client'] == 2){
            array_push($Order, "pm_raison_sociale", "pm_abreviation", "adresse", "code_postal", "ville", "pays", "num_tel", "pm_tel2", "pm_tel3", "num_fax", "num_port", "email", "pm_email2","province","district","secteur","cellule","village", "client_zone");
        }else{
            array_push($Order, "pm_raison_sociale", "pm_abreviation", "adresse", "code_postal", "ville", "pays", "num_tel", "pm_tel2", "pm_tel3", "num_fax", "num_port", "email", "pm_email2", "id_loc1", "id_loc2", "loc3", "client_zone");
        }

        array_push($Order, "pm_categorie", "pm_nature_juridique", "sect_act", "pp_pm_activite_prof", "pp_pm_patrimoine", "nb_imf", "nb_bk", "nbre_hommes_grp", "nbre_femmes_grp", "pm_date_notaire", "pm_date_depot_greffe", "pm_lieu_depot_greffe", "pm_date_expiration", "pm_numero_reg_nat", "pm_numero_nric", "pm_lieu_nric", "pm_date_constitution", "pm_agrement_nature", "pm_agrement_autorite", "pm_agrement_numero", "pm_agrement_date", "commentaires_cli");

    } else if ( $CLI["statut_juridique"] == 3) { // Groupe informel
        if ($AGC['identification_client'] == 2){
            array_push($Order, "gi_nom", "adresse", "code_postal", "ville", "pays", "num_tel", "num_fax", "num_port", "email","province","district","secteur","cellule","village", "client_zone", "sect_act", "langue", "gi_nbre_membr", "nbre_hommes_grp", "nbre_femmes_grp", "nb_imf", "nb_bk", "gi_date_agre");
        }else{
            array_push($Order, "gi_nom", "adresse", "code_postal", "ville", "pays", "num_tel", "num_fax", "num_port", "email", "id_loc1", "id_loc2", "loc3", "client_zone", "sect_act", "langue", "gi_nbre_membr", "nbre_hommes_grp", "nbre_femmes_grp", "nb_imf", "nb_bk", "gi_date_agre");
        }
    } else if ( $CLI["statut_juridique"] == 4) { // Groupe solidaire
        if ($AGC['identification_client'] == 2){
            array_push($Order, "gi_nom","gs_responsable", "adresse", "code_postal", "ville", "pays", "num_tel", "num_fax", "num_port", "email", "province","district","secteur","cellule","village", "client_zone", "sect_act", "langue", "gi_nbre_membr", "nb_imf", "nb_bk", "gi_date_agre", "commentaires_cli");
        }else{
            array_push($Order, "gi_nom","gs_responsable", "adresse", "code_postal", "ville", "pays", "num_tel", "num_fax", "num_port", "email", "id_loc1", "id_loc2", "loc3", "client_zone", "sect_act", "langue", "gi_nbre_membr", "nb_imf", "nb_bk", "gi_date_agre", "commentaires_cli");
        }
    }

    if ($CLI["etat"] != 2) // Client ayant subi une défection
        array_push($Order, "raison_defection");

    $myForm->addTable("ad_cli_hist", OPER_INCLUDE, $Order);
    if ( $CLI["statut_juridique"] == 1) {       // Personne physique
        array_push($Order, "photo");
        array_push($Order, "signature");
    }
    $myForm->setOrder(NULL, $Order);

    while (list($key, $value) = each($Order)) {
        $myForm->setFieldProperties($value, FIELDP_IS_LABEL, true);
    }

    if ( $CLI["statut_juridique"] == 1) {       // Personne physique
        array_pop($Order);
        array_pop($Order);
    }

    //Affichage des groupes solidaires
    if ($CLI["statut_juridique"] == 1 || $CLI["statut_juridique"] == 2 || $CLI["statut_juridique"] == 3) {
        $listeGroupSol=getGroupSol($CLI["id_client"]);
        if (!empty($listeGroupSol->param)) {

            //Affichage des groupes solidaires
            $myForm->addHTMLExtraCode("espace_grp_sol","<br/><p align=\"center\"><font size=\"3\"><strong>"._("Appartenance à un groupe solidaire")."<strong></font></p><br/>");
            foreach($listeGroupSol->param as $cle => $valeur) {
                $id_group=$valeur["id_grp_sol"];
                $myForm->addField("group".$id_group,_("Nom du groupe"),TYPC_TXT);
                $enregGroup=getNomGroup($id_group);
                $myForm->setFieldProperties("group".$id_group,FIELDP_DEFAULT,$enregGroup->param[0]["gi_nom"]);
                $myForm->setFieldProperties("group".$id_group,FIELDP_IS_LABEL,true);
                $myForm->addLink("group".$id_group,$id_group , _("Visualiser"), "#");
                $myForm->setLinkProperties($id_group, LINKP_JS_EVENT, array("OnClick" => "OpenBrw('$SERVER_NAME/modules/clients/rech_client_gi.php?m_agc=".$_REQUEST['m_agc']."&Recherche=$id_group&fermer=yes', '"._("Recherche")."');return false; "));

            }

        }
    }
    if ($CLI["statut_juridique"] == 4) {
        // Groupe solidaire : affichage des membres
        $myForm->addHTMLExtraCode("espace","<br/>");
        $myForm->addHTMLExtraCode("membres","<table align=\"center\" valign=\"middle\" bgcolor=\"".$colb_tableau."\"><tr><td><b>"._("Membres du groupe")."</b></td></tr></table>\n");
        $result = getListeMembresGrpSol($CLI["id_client"]);
        $membres_grp_sol = $result->param;
        for ($i=1 ;  $i<=sizeof($membres_grp_sol) ; $i++) {
            $myForm->addField("num_client$i", _("Membre $i"), TYPC_INT);
            $myForm->setFieldProperties("num_client$i", FIELDP_IS_LABEL, true);
            $myForm->setFieldProperties("num_client$i", FIELDP_DEFAULT, $membres_grp_sol[$i-1]);
        }
    }

    //Traitement pour les champs extras
    $objChampsExtras = new HTML_Champs_Extras ($myForm,'ad_cli_hist',$CLI["id_client"]);
    $objChampsExtras->buildChampsExtras(getChampsExtrasCLIENTValues($CLI["id_client"]),TRUE);

    $fill = new FILL_HTML_GEN2();
    $fill->addFillClause ("client", "ad_cli_hist");
    $fill->addCondition("client", "id_client_hist", $id_cli_hist);
    $fill->addManyFillFields("client", OPER_INCLUDE, $Order);

    $js  =
    "
        $('[name~=photo]').each(function(index, item){
            $(this).parent().attr('href', '$SERVER_NAME/lib/html/image_mgr.php?m_agc=".$_REQUEST["m_agc"]."&shortname=photo&longname=Photographie&url=".$CLI["photo_path"]."&canmodif=0');
            $(this).parent().attr('onclick', 'popup(this.href); return false');
        }); 
            
        $('[name~=signature]').each(function(index, item){
            $(this).parent().attr('href', '$SERVER_NAME/lib/html/image_mgr.php?m_agc=".$_REQUEST["m_agc"]."&shortname=photo&longname=Signature&url=".$CLI["signature_path"]."&canmodif=0');
            $(this).parent().attr('onclick', 'popup(this.href); return false');
        }); 
      
        function popup(url){
            var popup = window.open(url, 'popup', 
            'height=600,width=600,left=100,top=100,resizable=yes,scrollbars=yes,toolbar=yes,menubar=no,location=no,directories=no, status=yes');
        }
        
    ";

    $myForm->addJS(JSP_FORM, "js", $js);
    $fill->fill($myForm);
    $myForm->buildHTML();
    echo $myForm->getHTML();
?>