<?php

    // On charge les variables globales
    //require_once 'lib/dbProcedures/login_func.php';
    //require_once 'lib/misc/VariablesGlobales.php';
    //require_once 'lib/misc/VariablesSession.php';
    // Multi agence includes
    require_once 'ad_ma/app/controllers/misc/VariablesSessionRemote.php';
    require_once 'ad_ma/app/models/AgenceRemote.php';
    require_once 'ad_ma/app/models/Agence.php';
    require_once 'ad_ma/app/models/Client.php';
    require_once 'ad_ma/app/models/Compta.php';
    require_once 'ad_ma/app/models/Compte.php';
    require_once 'ad_ma/app/models/Divers.php';
    require_once 'ad_ma/app/models/Epargne.php';
    require_once 'ad_ma/app/models/Historique.php';
    require_once 'ad_ma/app/models/Parametrage.php';
    require_once 'ad_ma/app/models/Traduction.php';
    require_once 'ad_ma/app/models/Rapport.php';
    require_once('lib/html/FILL_HTML_GEN2.php');
    require_once "lib/html/HTML_menu_gen.php";
    require_once "lib/misc/divers.php";
    require_once 'lib/misc/csv.php';
    require_once 'modules/rapports/xslt.php';

    if ($global_nom_ecran == "Ccd-1") {
        global $global_remote_id_agence, $global_remote_client, $global_remote_id_client, $global_nom_login;
        $pdo_conn->beginTransaction();
        $epargne = new Epargne($pdo_conn, $global_remote_id_agence);
        $historic = new Historique($pdo_conn, $global_remote_id_agence);

        //trace dans ad_his
        $historic->ajoutHistorique(788, $global_remote_id_client, 'consultation d\'un compte en déplacé via agent', $global_nom_login, date("r"), NULL);

        $html = new HTML_GEN2();
        $html->setTitle(_("Consultation d'un compte via agent: choix du compte"));

        //affichage de tous les comptes du client
        $ListeComptes = $epargne->getComptesEpargne($global_remote_id_client);

        $choix = array();
        if (isset($ListeComptes)) {
            foreach($ListeComptes as $key=>$value) $choix[$key] = $value["num_complet_cpte"]." ".$value["intitule_compte"];
        };

        $html->addField("NumCpte", _("Numéro de compte"), TYPC_LSB);
        $html->setFieldProperties("NumCpte", FIELDP_ADD_CHOICES, $choix);
        $html->setFieldProperties("NumCpte",FIELDP_IS_REQUIRED, true);

        $choix2 = array(1=>_("Derniers mouvements"), 2=>_("Période donnée"));
        $html->addField("TypeCons",_("Type de consultation"), TYPC_LSB);
        $html->setFieldProperties("TypeCons", FIELDP_ADD_CHOICES,  $choix2);//apparemment, Aucun n'est pas automatiquement sélectionné
        $html->setFieldProperties("TypeCons",FIELDP_IS_REQUIRED, true);

        $html->addField("NbHisto", _("Nombre de mouvements :"), TYPC_INT);

        $html->addField("DateDeb", _("Date de début :"), TYPC_DTE);
        $html->setFieldProperties("DateDeb",  FIELDP_HAS_CALEND, false);
        $html->addLink("DateDeb", "calendrier1", _("Calendrier"), "#");
        $codejs = "if ((document.ADForm.HTML_GEN_LSB_TypeCons.value == 0) || (document.ADForm.HTML_GEN_LSB_TypeCons.value == 1)) return false; if (! isDate(document.ADForm.HTML_GEN_date_DateDeb.value)) ";
        $codejs .= "document.ADForm.HTML_GEN_date_DateDeb.value='';open_calendrier(getMonth(document.ADForm.HTML_GEN_date_DateDeb.value), getYear(document.ADForm.HTML_GEN_date_DateDeb.value), $calend_annee_passe, $calend_annee_futur, 'HTML_GEN_date_DateDeb');return false;";
        $html->setLinkProperties("calendrier1", LINKP_JS_EVENT, array("onclick" => $codejs));

        $html->addField("DateFin", _("Date de fin :"), TYPC_DTE);
        $html->setFieldProperties("DateFin",  FIELDP_HAS_CALEND, false);
        $html->addLink("DateFin", "calendrier2", _("Calendrier"), "#");
        $codejs = "if ((document.ADForm.HTML_GEN_LSB_TypeCons.value == 0) || (document.ADForm.HTML_GEN_LSB_TypeCons.value == 1)) return false; if (! isDate(document.ADForm.HTML_GEN_date_DateFin.value)) ";
        $codejs .= "document.ADForm.HTML_GEN_date_DateFin.value='';open_calendrier(getMonth(document.ADForm.HTML_GEN_date_DateFin.value), getYear(document.ADForm.HTML_GEN_date_DateFin.value), $calend_annee_passe, $calend_annee_futur, 'HTML_GEN_date_DateFin');return false;";
        $html->setLinkProperties("calendrier2", LINKP_JS_EVENT, array("onclick" => $codejs));

        // javacript pour le choix de type de consultation
        $codejs2 = "\tfunction getTypeConsultation(){";
        $codejs2 .= "\n\tif (document.ADForm.HTML_GEN_LSB_TypeCons.value == 0){document.ADForm.HTML_GEN_date_DateDeb.disabled = true;document.ADForm.HTML_GEN_date_DateDeb.value='';\n";
        $codejs2 .= "\t\tdocument.ADForm.HTML_GEN_date_DateFin.disabled = true;document.ADForm.HTML_GEN_date_DateFin.value='';\n";
        $codejs2 .= "\t\tdocument.ADForm.NbHisto.disabled = true};document.ADForm.NbHisto.value='';\n";
        $codejs2 .= "\n\tif (document.ADForm.HTML_GEN_LSB_TypeCons.value == 1){document.ADForm.HTML_GEN_date_DateDeb.disabled = true;\n";
        $codejs2 .= "\t\tdocument.ADForm.HTML_GEN_date_DateFin.disabled = true;\n";
        $codejs2 .= "\t\tdocument.ADForm.NbHisto.disabled = false;\n};\n";
        $codejs2 .= "\tif (document.ADForm.HTML_GEN_LSB_TypeCons.value == 2){ document.ADForm.HTML_GEN_date_DateDeb.disabled = false;\n";
        $codejs2 .= "\t\tdocument.ADForm.HTML_GEN_date_DateFin.disabled = false;\n";
        $codejs2 .= "\t\tdocument.ADForm.NbHisto.disabled = true;};\n";
        $codejs2 .= "}\n";

        $html->setFieldProperties("TypeCons", FIELDP_JS_EVENT, array("onChange"=>"getTypeConsultation();"));
        $html->addJS(JSP_FORM, "JS2", $codejs2);

        //CheckForm javascript
        //en fonction du choix du type de consulation, rendre les champs required
        $ChkJS = "\n\t\tif ( (document.ADForm.HTML_GEN_LSB_TypeCons.value == 1) &&  ((\n\t\t\tdocument.ADForm.NbHisto.value=='') || ( ! isIntPos(document.ADForm.NbHisto.value)))) {";
        $ChkJS .= "msg += '-Vous devez saisir un nombre\\n'; ADFormValid=false;};\n";

        $ChkJS .= "\n\t\tif (document.ADForm.HTML_GEN_LSB_TypeCons.value == 2){";
        $ChkJS .= "\n\t\t\tif (document.ADForm.HTML_GEN_date_DateDeb.value == '') {msg += '-"._("Vous devez saisir une valeur pour la date de début")."\\n';ADFormValid=false;};\n";
        $ChkJS .= "\n\t\t\tif (document.ADForm.HTML_GEN_date_DateFin.value == '') {msg += '-"._("Vous devez saisir une valeur pour la date de fin")."\\n';ADFormValid=false;};\n";
        $ChkJS .= "\n\t\t\tif (! isDate(document.ADForm.HTML_GEN_date_DateDeb.value)) {msg += '-"._("Vous devez saisir une date de début")."\\n';ADFormValid=false;};\n";
        $ChkJS .= "\n\t\t\tif ( ! isDate(document.ADForm.HTML_GEN_date_DateFin.value)) {msg += '-"._("Vous devez saisir une date de fin")."\\n';ADFormValid=false;};\n";
        $ChkJS .= "\n\t\t}";
        $html->addJS(JSP_BEGIN_CHECK, "JS3",$ChkJS);


        $html->setOrder(NULL,array("NumCpte", "TypeCons","NbHisto","DateDeb","DateFin"));

        $fieldslabel = array("NbHisto","DateDeb","DateFin");
        foreach($fieldslabel as $value) {
            $html->setFieldProperties($value, FIELDP_IS_LABEL, true);
            $html->setFieldProperties($value, FIELDP_IS_REQUIRED, false);
        };


        $html->addFormButton(1, 1, "ok", _("Valider"), TYPB_SUBMIT);
        $html->addFormButton(1, 2, "cancel", _("Annuler"), TYPB_SUBMIT);
        $html->setFormButtonProperties("ok", BUTP_PROCHAIN_ECRAN, 'Ccd-2');
        $html->setFormButtonProperties("cancel", BUTP_PROCHAIN_ECRAN, 'God-3');
        $html->setFormButtonProperties("cancel", BUTP_CHECK_FORM, false);

        $pdo_conn->commit();
        $html->buildHTML();
        echo $html->getHTML();
    }
    else if ($global_nom_ecran == "Ccd-2") {
        global $global_remote_id_agence, $global_remote_client, $global_remote_id_client,$global_monnaie_courante_prec, $global_id_profil;
        $pdo_conn->beginTransaction();

        $compte = new Compte($pdo_conn, $global_remote_id_agence);
        $epargne = new Epargne($pdo_conn, $global_remote_id_agence);
        $parametrage = new Parametrage($pdo_conn, $global_remote_id_agence);

        //prendre les infos sur le compte
        $InfoCpte = $compte->getAccountDatas($NumCpte);
        $SESSION_VARS['NumCpte'] = $NumCpte;

        //consultation par n derniers mvts
        if ($TypeCons == "1") {
            $solde_courant = $InfoCpte["solde"];
            $InfoMvts = $compte->getMvtsCpteClientParNumero($global_remote_id_client, $NumCpte, $NbHisto);
            $SESSION_VARS['NbHisto'] = $NbHisto;
            $SESSION_VARS['TypeCons'] = 1;
        }

        //consultation par dates
        if ($TypeCons == "2") {
            // On présente de manière inversément chronologique, donc le solde de départ est le solde à la date de fin + 1 jour (consulation bornes inclues)
            $solde_courant = $compte->calculeSoldeCpteInterne($NumCpte, demain($DateFin));
            $InfoMvts = $compte->getMvtsCpteClientParDates($NumCpte, $DateDeb, $DateFin);
            $SESSION_VARS['DateDeb'] = $DateDeb;
            $SESSION_VARS['DateFin'] = $DateFin;
            $SESSION_VARS['TypeCons'] = 2;
        }
        $SESSION_VARS['solde_courant'] = $solde_courant;

        $html = new HTML_GEN2();
        $html->setTitle(_("Consultation d'un compte : détails du compte"));

        //en fonction du compte choisi, déterminer les champs à afficher
        $InfoProduit = $epargne->getProdEpargne($InfoCpte["id_prod"]);

        // Affichage des devises
        setMonnaieCourante($InfoCpte["devise"]);
        //Contôle sur l'affichage des soldes

        $access_solde = $parametrage->getProfilAccesSolde($global_id_profil, $InfoCpte['id_prod']);
        $access_solde_vip = $parametrage->get_profil_acces_solde_vip($global_id_profil, $global_remote_id_client);

        if($parametrage->manage_display_solde_access($access_solde, $access_solde_vip)) {
            $include = array("intitule_compte","num_complet_cpte","id_prod","etat_cpte","solde", "devise", "tx_interet_cpte", "freq_calcul_int_cpte", "mode_calcul_int_cpte", "terme_cpte");
        }else {
            $include = array("intitule_compte","num_complet_cpte","id_prod","etat_cpte", "devise", "tx_interet_cpte", "freq_calcul_int_cpte", "mode_calcul_int_cpte", "terme_cpte");
        }

        if ($InfoProduit["mnt_bloq"] > 0) $include[] = "mnt_bloq";
        if ($InfoProduit["mnt_bloq_cre"] > 0) $include[] = "mnt_bloq_cre";
        if ($InfoProduit["tx_interet"] > 0)  $include[] =  "interet_annuel";
        $include[] = "date_ouvert";

        if ($InfoCpte["terme_cpte"] > 0) {
            $include[] = "dat_date_fin";
            if ($InfoProduit["dat_prolongeable"] == 't') {
                $include[] = "dat_prolongation";
                $include[] = "dat_nb_prolong";
                $include[] = "dat_nb_reconduction";
            }
        }

        if ($InfoProduit["certif"] == 't') $include[] = "dat_num_certif";

        $html->addTable("ad_cpt", OPER_INCLUDE, $include);
        $html->setOrder(NULL, $include);

        $def = new FILL_HTML_GEN2();
        $def->addFillClause("cpte","ad_cpt");
        $def->addCondition("cpte", "id_cpte", $NumCpte);
        $def->addManyFillFields("cpte", OPER_INCLUDE, $include);
        $def->fill($html);

        foreach($include as $value) {
            $html->setFieldProperties($value, FIELDP_IS_LABEL, true);
            $html->setFieldProperties($value, FIELDP_IS_REQUIRED, false);
        };

        // Construction du tableau HTML pour l'affichage
        if (isset($InfoMvts)) {
            $date_jour = date("d");
            $date_mois = date("m");
            $date_annee = date("Y");
            $date_ancien = $date_annee."/".$date_mois."/".$date_jour;
            $id_his_ancien = "";
            $ExtraHTML = "<br><TABLE align=\"center\" cellpadding=\"5\" width=\"100%\">";
            $ExtraHTML .= "\n\t<tr align=\"center\" bgcolor=\"$colb_tableau\"><td><b>"._("DATE")."</b></td><td><b>"._("FONCTION")."</b></td>";
            $ExtraHTML .= "<td><b>"._("OPERATION")."</b>";
            if($parametrage->manage_display_solde_access($access_solde, $access_solde_vip))
                $ExtraHTML .= "</td><td><b>"._("SOLDE AVANT")."</b></td>";
            $ExtraHTML .="<td><b>"._("DEBIT")."</b></td><td><b>"._("CREDIT")."</b></td>";
            if($parametrage->manage_display_solde_access($access_solde, $access_solde_vip))
                $ExtraHTML .= "<td><b>"._("SOLDE APRES")."</b></td>";
            $ExtraHTML .=	"</tr>";
            $color = $colb_tableau;
            foreach ($InfoMvts as $key => $mvt) {
                //écriture des lignes de mvts dans le tableau
                $fonction = $mvt["type_fonction"];
                $libel_fonction = adb_gettext($adsys["adsys_fonction_systeme"][$fonction]);
                $id_his_ancien = $mvt["id_his"];
                $color = ($color == $colb_tableau? $colb_tableau_altern : $colb_tableau);
                if ($date_ancien != $mvt["date"]) {
                    //dans ce cas, il y a rupture sur une nouvelle date
                    $InfoMvts[$key]["nbre_jours_inactivite"] = nbreDiffJours(pg2phpDate($date_ancien),pg2phpDate($mvt["date"]));
                    $date_ancien = $mvt["date"];
                    $tmp_dte = pg2phpDatebis($mvt["date"]);
                    $date = $tmp_dte[1]."/".$tmp_dte[0]."/".$tmp_dte[2]." ".$tmp_dte[3].":".$tmp_dte[4];
                } else {
                    $date = "";
                    $InfoMvts[$key]["nbre_jours_inactivite"] = 0;
                }

                $tradLibel_operation = new Traduction($pdo_conn, $global_remote_id_agence);
                $tradLibel_operation->setTraduction($mvt['libel_ecriture']);

                // Multi agence fix
                $libel_operation = $tradLibel_operation->traduire();
                if($libel_operation=='Dépôt en déplacé' || $libel_operation=='Retrait en déplacé'){
                    $libel_operation = 'Opération en déplacé';
                }

                //pour l'operation des transfert, les numéro des comptes des transactions sont affichés dans le libellé opération.
                if ($fonction=='76' && $mvt["type_operation"]=='120'){

                    if(isset($mvt["info_ecriture"])){
                        $numcpts = explode('|', $mvt["info_ecriture"]);

                        if(count($numcpts)==2){

                            $libel_operation .= ":<br/>";
                            $libel_operation .= "Compte source: ".$numcpts[0];
                            $libel_operation .= "<br/>Compte destination: ".$numcpts[1];
                        }
                    }
                }

                // Vérifier liste opération à modifier.
                if(in_array($mvt['type_operation'], $adsys["adsys_operation_cheque_infos"]) ){
                    $libel_operation = $epargne->getChequeno($mvt['id_his'],$libel_operation,$mvt["info_ecriture"]);
                }

                $ExtraHTML .= "\n\t<tr align=\"center\" bgcolor=\"$color\"><td>$date</td>";
                $ExtraHTML .= "<td>$libel_fonction</td><td>".$libel_operation."</td>";

                // On a les infos dans l'ordre chrono inverse
                $solde_apres = $solde_courant;
                if ($mvt["sens"] == 'd')
                    $solde_courant += $mvt['montant'];
                else
                    $solde_courant -= $mvt['montant'];
                $solde_avant = $solde_courant;
                $InfoMvts[$key]['solde'] = $solde_apres;

                // Arrondi à cause des imprécisions des calculs float
                $solde_courant = round($solde_courant, $global_monnaie_courante_prec);
                if($parametrage->manage_display_solde_access($access_solde, $access_solde_vip))
                    $ExtraHTML .= "<td>".afficheMontant($solde_avant)."</td>";

                if ($mvt["sens"] == 'd')
                    $ExtraHTML .= "<td>".afficheMontant($mvt["montant"])."</td><td>&nbsp;</td>";
                else
                    $ExtraHTML .= "<td>&nbsp;</td><td>".afficheMontant($mvt["montant"])."</td>";
                if($parametrage->manage_display_solde_access($access_solde, $access_solde_vip))
                    $ExtraHTML .= "<td>".afficheMontant($solde_apres)."</td>";
                $ExtraHTML .= "</tr>";
            }
            $ExtraHTML .= "</TABLE>";
            $html->addHTMLExtraCode("htm1",$ExtraHTML);
            $SESSION_VARS['InfoMvts'] = $InfoMvts;
        }

        $html->addFormButton(1, 1, "retour", _("Précédent"), TYPB_SUBMIT);
        $html->setFormButtonProperties("retour", BUTP_PROCHAIN_ECRAN, 'Ccd-1');

        $html->addFormButton(1, 2, "cancel", _("Retour Menu"), TYPB_SUBMIT);
        $html->setFormButtonProperties("cancel", BUTP_PROCHAIN_ECRAN, 'Gen-16');
        $html->setFormButtonProperties("cancel", BUTP_CHECK_FORM, false);

        $html->addFormButton(1, 3, "pdf", _("Rapport PDF"), TYPB_SUBMIT);
        $html->setFormButtonProperties("pdf", BUTP_PROCHAIN_ECRAN, "Ccd-3");
        $html->addFormButton(1, 4, "csv", _("Export CSV"), TYPB_SUBMIT);
        $html->setFormButtonProperties("csv", BUTP_PROCHAIN_ECRAN, "Ccd-4");

        $pdo_conn->commit();

        $html->buildHTML();
        echo $html->getHTML();
    }
    else if($global_nom_ecran == "Ccd-3" || $global_nom_ecran == "Ccd-4"){
        global $global_remote_id_client, $global_remote_id_agence;

        $pdo_conn->beginTransaction();

        $client = new Client($pdo_conn, $global_remote_id_agence);
        $compte = new Compte($pdo_conn, $global_remote_id_agence);
        $epargne = new Epargne($pdo_conn, $global_remote_id_agence);
        $rapport = new Rapport($pdo_conn, $global_remote_id_agence);

        // On récupère les infos déjà en notre possession
        $DATA["client"] = $global_remote_id_client;
        $DATA["nom_client"] = $client->getClientName($global_remote_id_client);
        $InfoCpte = $compte->getAccountDatas($SESSION_VARS['NumCpte']);
        $DATA["num_cpte"] = $InfoCpte["num_complet_cpte"];
        $tmp_dte = pg2phpDatebis($InfoCpte["date_ouvert"]);
        $DATA["date_ouverture"] = $tmp_dte[1] . "/" . $tmp_dte[0] . "/" . $tmp_dte[2];
        $InfoProduit = $epargne->getProdEpargne($InfoCpte["id_prod"]);
        $DATA["produit"] = $InfoProduit["libel"];
        $DATA["id_produit"] = $InfoCpte["id_prod"];
        $DATA["solde_min"] = $InfoCpte["solde_calcul_interets"];
        $DATA["taux_int"] = $InfoProduit["tx_interet"];
        $DATA["date_debut"] = $SESSION_VARS['DateDeb'];
        $DATA["date_fin"] = $SESSION_VARS['DateFin'];
        $DATA["NbHisto"] = $SESSION_VARS['NbHisto'];
        $DATA["solde"] = $SESSION_VARS['solde_courant'];
        $DATA["mnt_bloq"] = $InfoCpte["mnt_bloq"] + $InfoCpte["mnt_bloq_cre"];
        $DATA["mnt_min"] = $InfoCpte["mnt_min_cpte"];
        $DATA["solde_disp"] = $InfoProduit["retrait_unique"] == 't' ? _("Compte à retrait unique") : $epargne->getSoldeDisponible($InfoCpte["id_cpte"]);
        $DATA["InfoMvts"] = $SESSION_VARS['InfoMvts'];
        $DATA["devise"] = $InfoProduit["devise"];

        //pour que le rapport soit dans la même langue
        basculer_langue_rpt();
        $list_criteres = array ();
        $list_criteres = array_merge($list_criteres, array ( _("ID client") => makeNumClient($DATA["client"])));
        $list_criteres = array_merge($list_criteres, array ( _("Nom") => $DATA["nom_client"]));
        $list_criteres = array_merge($list_criteres, array ( _("Compte") => $DATA["num_cpte"]));
        if ($SESSION_VARS['TypeCons'] == 2) {
            $list_criteres = array_merge($list_criteres, array ( _("Date de debut") => localiser_date_rpt($DATA["date_debut"])));
            $list_criteres = array_merge($list_criteres, array ( _("Date de fin") => localiser_date_rpt($DATA["date_fin"])));
        } else if ($SESSION_VARS['TypeCons'] == 1)
            $list_criteres = array_merge($list_criteres, array ( _("Nombre de mouvements") => $DATA["NbHisto"]));
        reset_langue();
        if ($global_nom_ecran == "Ccd-4") {
            //Génération du CSV grâce à XALAN
            $xml = $rapport->xml_epargne($DATA, $list_criteres, true);
            $csv_file = xml_2_csv($xml, 'epargne.xslt');

            //Message de confirmation + affichage du rapport dans une nouvelle fenêtre
            echo getShowCSVHTML("God-3", $csv_file);
        } else if ($global_nom_ecran == "Ccd-3") {
            //Génération du XSL-FO (grâce au XSLT) et du PDF (grâce à FOP)
            $xml = $rapport->xml_epargne($DATA, $list_criteres);
            $fichier_pdf = xml_2_xslfo_2_pdf($xml, 'epargne.xslt');

            //Message de confirmation + affichage du rapport dans une nouvelle fenêtre
            echo get_show_pdf_html("God-3", $fichier_pdf);
        }

        $pdo_conn->commit();
    }
?>