<?php

//error_reporting(E_ALL);
//ini_set("display_errors", "on");

/*
 Audit des operations en déplacé

Description :
Ce module crée 3 écrans :
Ama-1 : Critères de recherche
Ama-2 : Résultat recherche
Ama-3 : Affichage PDF

*/

// Multi agence includes
require_once 'DB.php';
require_once 'ad_ma/app/controllers/misc/VariablesSessionRemote.php';
require_once 'ad_ma/app/models/AgenceRemote.php';
require_once 'ad_ma/app/models/Agence.php';
require_once 'ad_ma/app/models/Audit.php';
require_once 'ad_ma/app/models/AuditVisualisation.php';
require_once 'ad_ma/app/models/Client.php';
require_once 'ad_ma/app/models/Compta.php';
require_once 'ad_ma/app/models/Compte.php';
require_once 'ad_ma/app/models/Credit.php';
require_once 'ad_ma/app/models/Devise.php';
require_once 'ad_ma/app/models/Divers.php';
require_once 'ad_ma/app/models/Epargne.php';
require_once 'ad_ma/app/models/Guichet.php';
require_once 'ad_ma/app/models/Historique.php';
require_once 'ad_ma/app/models/Parametrage.php';
require_once 'ad_ma/app/models/TireurBenef.php';

require_once "lib/html/HTML_menu_gen.php";
require_once 'lib/html/FILL_HTML_GEN2.php';
require_once 'modules/rapports/xml_devise.php';
require_once 'modules/rapports/xslt.php';

require_once 'lib/dbProcedures/guichet.php';
require_once 'modules/rapports/xml_guichet.php';

//-----------------------------------------------------------------
//-------         Initialisation                            -------
//-----------------------------------------------------------------

// Clear all remote info
//resetVariablesGlobalesRemoteClient();

$loginDistant = "distant";
$AuditVisualisationObj = new AuditVisualisation();

//-----------------------------------------------------------------
//------- Ecran Ama-1 : Critères de recherche               -------
//-----------------------------------------------------------------
if ($global_nom_ecran == "Ama-1")
{
    $MyPage = new HTML_GEN2(_("Critères de recherche"));

    //Champ login
    $logins = AuditVisualisation::getListeLoginsForAudit();
    $MyPage->addField("login", _("Login ayant exécuté la fonction"), TYPC_LSB);
    $MyPage->setFieldProperties("login", FIELDP_DEFAULT, '');
    $MyPage->setFieldProperties("login", FIELDP_ADD_CHOICES, $logins);
    $MyPage->setFieldProperties("login", FIELDP_HAS_CHOICE_AUCUN, false);
    $MyPage->setFieldProperties("login", FIELDP_HAS_CHOICE_TOUS, false);
    $MyPage->setFieldProperties("login", FIELDP_JS_EVENT, array("onChange" => "processLoginAgencyVal();"));   

    //Champs type de fonction
    $fonctions = AuditVisualisation::getListeFonctionsForAudit();
    $MyPage->addField("num_fonction", _("Fonction"), TYPC_LSB);
    $MyPage->setFieldProperties("num_fonction", FIELDP_ADD_CHOICES, $fonctions);
    $MyPage->setFieldProperties("num_fonction", FIELDP_HAS_CHOICE_AUCUN, false);
    $MyPage->setFieldProperties("num_fonction", FIELDP_HAS_CHOICE_TOUS, false);

    //Uniquement transactions reussis?
    $MyPage->addField("trans_reussi", _("Transactions réussies ?"), TYPC_BOL);
    $MyPage->setFieldProperties("trans_reussi", FIELDP_DEFAULT, 't');
  
    // Champ Agence distant
        
    // Récupère la liste des agences distantes
    $ListeAgences = AgenceRemote::getListRemoteAgence(true);
    
    $choix_agence = array();
    if (is_array($ListeAgences) && count($ListeAgences) > 0) {
        foreach ($ListeAgences as $key => $obj) {
            if (DBC::pingConnection($obj, 1) === TRUE) { // Vérifié si la BDD est active
                $choix_agence[$key] = sprintf("%s (%s)", $obj["app_db_description"], $obj["id_agc"]);
            }
        }
    }
    $MyPage->addField("IdAgence", _("Agence externe"), TYPC_LSB);
    $MyPage->setFieldProperties("IdAgence", FIELDP_ADD_CHOICES, $choix_agence);
    $MyPage->setFieldProperties("IdAgence", FIELDP_IS_REQUIRED, false);
    $MyPage->setFieldProperties("IdAgence", FIELDP_JS_EVENT, array("onChange" => "generateClientUrl();"));
    
    $MyPage->setFieldProperties("IdAgence", FIELDP_HAS_CHOICE_TOUS, true);
    $MyPage->setFieldProperties("IdAgence", FIELDP_HAS_CHOICE_AUCUN, false);
      
    //Javascript
    /*
     * En fonction du choix du login
     * Si (login == distant) current server operates as a remote server in the transaction
     * Else current server operates as a local server in the transaction
     */
    $login_distant_value = AuditVisualisation::LOGIN_DISTANT; // value of login distant
    
    $codejs = "
            function populateSelectAgency(select)
            {\n
                ";
            if (isset($choix_agence) && count($choix_agence)>0) {
                foreach ($choix_agence as $key => $value) {
                    $codejs .= " select.options[select.options.length] = new Option(\"".$value."\",\"".$key."\",false,false);\n";
                }
            }
    
    $codejs .= " }\n
                    
            function processLoginAgencyVal()
            {
                 document.ADForm.num_client.value = '';
            
                var select = document.ADForm.HTML_GEN_LSB_IdAgence;

                // Reset select
		        select.options.length = 0;

                if (document.ADForm.HTML_GEN_LSB_login.value != '$login_distant_value'){
                    // Set default value
                    select.options[0] = new Option(\"[Tous]\",\"0\",true,true);
                }             

                populateSelectAgency(select);  

                generateClientUrl();
            }
            
            /* genere l'url appele pour la recherche client */
            function generateClientUrl()
            {    
                document.ADForm.num_client.value = '';
                      
                if (document.ADForm.HTML_GEN_LSB_login.value == '$login_distant_value'){
                     urlClient = urlLocal;
                }
                else {
                    var IdAgence = document.ADForm.HTML_GEN_LSB_IdAgence.value;
                    
                    if(IdAgence != 0) {
                         urlClient = urlRemote + '&idAgence=' + IdAgence;
                    }       
                    else {
    
                    }
                }             
            }
            
            ";

    $codejs .= "\n var urlClient = ''; ";
    $codejs .= "\n var urlLocal = '../modules/clients/rech_client.php?m_agc=".$_REQUEST['m_agc']."&field_name=num_client'; ";
    $codejs .= "\n var urlRemote = '../ad_ma/app/views/client/rech_client_distant.php?m_agc=".$_REQUEST['m_agc']."&field_name=num_client'; ";    
        
    // Handle opening of recherche client popup
    $codejs .= "\n function openRechercheClient() {"; 
    $codejs .= "\n var IdAgence = document.ADForm.HTML_GEN_LSB_IdAgence.value;";
    $codejs .= "\n var login = document.ADForm.HTML_GEN_LSB_login.value; ";
    // agence shouldnt be 'tous' and login shouldnt be tous, OR login should be 'distant'
    $codejs .= "\n\t if((IdAgence !=0 && login !=0) || (login == '".$login_distant_value."')) { \n\t";
    $codejs .= "\n\tOpenBrw(urlClient, '"._("Recherche")."');";
    $codejs .= "\n\t}";    
    $codejs .= "\n}\n\n";
    
    //Add JS    
    $MyPage->addJS(JSP_FORM, "JS3", $codejs);

    //Champs client
    $MyPage->addField("num_client", _("Numéro client"), TYPC_INT);
    //$MyPage->setFieldProperties("num_client", FIELDP_IS_LABEL, true);    
        
    $MyPage->addLink("num_client", "rechercher", _("Rechercher"), "#");
    $MyPage->setLinkProperties("rechercher", LINKP_JS_EVENT, array("onclick" => "openRechercheClient(); return false;"));

    //Champs date début
    $MyPage->addField("date_min", _("Date min"), TYPC_DTE);

    //Champs date fin
    $MyPage->addField("date_max", _("Date max"), TYPC_DTE);

    //Champs n° transaction local
    $MyPage->addField("trans_local", _("N° transaction interne"), TYPC_INT);

    //Champs n° transaction distant
    $MyPage->addField("trans_distant", _("N° transaction externe"), TYPC_INT);

    //Boutons
    $MyPage->addFormButton(1,1,"valider", _("Valider"), TYPB_SUBMIT);
    $MyPage->addFormButton(1,2,"annuler", _("Annuler"), TYPB_SUBMIT);
    $MyPage->setFormButtonProperties("annuler", BUTP_CHECK_FORM, false);

    $MyPage->setFormButtonProperties("valider", BUTP_PROCHAIN_ECRAN, "Ama-2");
    $MyPage->setFormButtonProperties("annuler", BUTP_PROCHAIN_ECRAN, "Gen-6");


    $MyPage->buildHTML();
    echo $MyPage->getHTML();
}
//-----------------------------------------------------------------
//------- Ecran Ama-2 : Résultat recherche                  -------
//-----------------------------------------------------------------
else if ($global_nom_ecran == "Ama-2")
{
    if (($num_client <= 0) || ($num_client == "")) $client = NULL;
    if ($IdAgence == "") $IdAgence = NULL;
    if ($date_min == "") $date_min = NULL;
    if ($date_max == "") $date_max = NULL;
    if ($trans_local == "") $trans_min = NULL;
    if ($trans_distant == "") $trans_max = NULL;
    if (isset($trans_fin)) $trans_fin = true;
    else $trans_fin = false;
     
    $SESSION_VARS['criteres'] = array();
    $SESSION_VARS['criteres']['login'] = $login;
    $SESSION_VARS['criteres']['num_fonction'] = $num_fonction;
    $SESSION_VARS['criteres']['trans_reussi'] = $trans_reussi;
    $SESSION_VARS['criteres']['IdAgence'] = $IdAgence;
    $SESSION_VARS['criteres']['num_client'] = $num_client;
    $SESSION_VARS['criteres']['date_min'] = $date_min;
    $SESSION_VARS['criteres']['date_max'] = $date_max;
    $SESSION_VARS['criteres']['trans_local'] = $trans_local;
    $SESSION_VARS['criteres']['trans_distant'] = $trans_distant;
    $SESSION_VARS['criteres']['trans_fin'] = $trans_fin;     
   
    $nombre = $AuditVisualisationObj->countTransactions($SESSION_VARS['criteres']);
       
    if($login == AuditVisualisation::LOGIN_DISTANT) {
        $rapportClientInterne = true;
    }
    else {
        $rapportClientInterne = false;
    }
            
    if ($nombre > 300) {
        $MyPage = new HTML_erreur(_("Trop de correspondances"));
        //$MyPage->setMessage(sprintf(_("La recherche a renvoyé %s résultats; veuillez affiner vos critères de recherche ou imprimer."),$nombre));
        $MyPage->setMessage(sprintf(_("La recherche a renvoyé %s résultats; veuillez affiner vos critères de recherche."),$nombre));
        $nextScreen = "Ama-1";
        $printScreen = "Ama-3";
        $MyPage->addButton(BUTTON_OK, $nextScreen);
        //$MyPage->addCustomButton("print", _("Imprimer"), $printScreen, TYPB_SUBMIT);
        $MyPage->buildHTML();
        echo $MyPage->HTML_code;

    } else {                 
        $resultat = $AuditVisualisationObj->rechercheTransactions($SESSION_VARS['criteres']);       
        
        $html = "<h1 align=\"center\">"._("Résultat recherche")."</h1><br><br>\n";
        $html .= "<FORM name=\"ADForm\" action=\"$PHP_SELF\" method=\"post\" onsubmit=\"return ADFormValid;\">\n";
        $html .= "<TABLE align=\"center\" bgcolor=$colb_tableau border=$tableau_border cellspacing=$tableau_cellspacing cellpadding=$tableau_cellpadding>\n";

        //$html .= "<TD><b>n° transaction</b></TD><TD align=\"center\"><b>"._("Date")."</b></TD><TD align=\"center\"><b>"._("Heure")."</b></TD><TD align=\"center\"><b>"._("Fonction")."</b></TD><TD align=\"center\"><b>"._("Login")."</b></TD><TD align=\"center\"><b>"._("Agence")."</b></TD><TD align=\"center\"><b>"._("N° client")."</b></TD></TR>\n";
       
        if($rapportClientInterne) {
            $libelleClient = "N° client interne";
        }
        else {
            $libelleClient = "N° client externe";
        }
        
        //Ligne titre
        $html .= "
                <TR bgcolor=$colb_tableau>
                <TD style='width:7%'><b>"._("N° interne")."</b></TD>
                <TD style='width:7%'><b>"._("N° externe")."</b></TD>
                <TD align=\"center\"><b>"._("Date")."</b></TD>
                <TD align=\"center\"><b>"._("Heure")."</b></TD>
                <TD align=\"center\"><b>"._("Opération")."</b></TD>
                <TD align=\"center\"><b>"._("Login")."</b></TD>
                <TD align=\"center\"><b>"._("Agence externe")."</b></TD>
                <TD align=\"center\"><b>"._($libelleClient)."</b></TD>
                </TR>\n";
                 
        reset($resultat);

        while (list(,$value) = each($resultat))  //Pour chaque résultat
        {            
            //On alterne la couleur de fond
            if ($a) $color = $colb_tableau;
            else $color = $colb_tableau_altern;
            $a = !$a;
            $html .= "<TR bgcolor=$color>\n";          
         
            if($rapportClientInterne) 
            {    
                //n° local 
                if (($value['id_ecriture_distant'])) {   // il y a des ecritures en local : depot especes et tout retrait  
                    $url = "'$http_prefix/ad_ma/app/views/audit/detail_transaction.php?m_agc=".$_REQUEST['m_agc']."&id_transaction=".$value['id_his_distant']."'";
                    $html .= "<TD><A href=# onclick=\"OpenBrwXY($url ,'', 800, 600);\">".$value['id_his_distant']."</A></TD>";                                  
                }
                else { // pas des ecritures locals : depot autre qu'especes
                    $html .= "<TD>".$value['id_his_distant']."</TD>\n";                 
                }     

                //n° distant : recuperé du serveur local
                //$url = "'$http_prefix/ad_ma/app/views/audit/detail_transaction.php?m_agc=".$_REQUEST['m_agc']."&id_transaction=".$value['id_his_distant']."'";
                //$html .= "<TD><A href=# onclick=\"OpenBrwXY($url ,'', 800, 600);\">".$value['id_his_distant']."</A></TD>";

                if (($value['id_ecriture_local'])) {
                    $url_distant = "'$http_prefix/ad_ma/app/views/audit/detail_transaction_distant.php?m_agc=".$_REQUEST['m_agc']."&id_transaction=".$value['id_his_local']."&id_agence=".$value['id_ag_local'] . "'";
                    $html .= "<TD><A href=# onclick=\"OpenBrwXY($url_distant ,'', 800, 600);\">".$value['id_his_local']."</A></TD>";
                } else {
                    $html .= "<TD>".$value['id_his_local']."</TD>\n";
                }
                
            }
            else  // Pour les logins autres que distant (serveur courant en tant que local)
            {  //n° local
                if (($value['trans_fin'])) { // il y a des ecritures en local : depot especes et tout retrait
                    $url = "'$http_prefix/ad_ma/app/views/audit/detail_transaction.php?m_agc=".$_REQUEST['m_agc']."&id_transaction=".$value['id_his_local']."'";
                    $html .= "<TD><A href=# onclick=\"OpenBrwXY($url ,'', 800, 600);\">".$value['id_his_local']."</A></TD>";
                }
                else { // pas des ecritures locals : depot autre qu'especes
                    $html .= "<TD>".$value['id_his_local']."</TD>\n";
                }                
                //n° distant : recuperé du serveur distant
                $url_distant = "'$http_prefix/ad_ma/app/views/audit/detail_transaction_distant.php?m_agc=".$_REQUEST['m_agc']."&id_transaction=".$value['id_his_distant']."&id_agence=".$value['id_ag_distant'] . "'";
                $html .= "<TD><A href=# onclick=\"OpenBrwXY($url_distant ,'', 800, 600);\">".$value['id_his_distant']."</A></TD>";
            }           
                   
            //Date
            $html .= "<TD>".pg2phpDate($value['date_maj'])."</TD>";

            //Heure
            $html .= "<TD>".pg2phpHeure($value['date_maj'])."</TD>";

            //Fonction           
            //$fonctionDeplace = Divers::getLibelleFonctionDeplace($value['type_transaction']);
            //$html .= "<TD>".$fonctionDeplace."</TD>\n"; 
            $html .= "<TD>". $value['type_choix_libel']."</TD>\n";
           
            //Login
            $html .= "<TD>".$value['nom_login']."</TD>\n";

            //Agence
            $html .= "<TD>".$value['nom_agence']."</TD>\n";

            //N° client
            if ($value['id_client_distant'] > 0) {
                $html .= "<TD align=\"center\">".sprintf("%06d", $value['id_client_distant'])."</TD>\n";
            }
            else $html .= "<TD></TD>\n";           
            $html .= "</TR>\n";

            //array_push($SESSION_VARS['id_his_local'], $value['id_his_local']);
        }

        $html .= "<TR bgcolor=$colb_tableau><TD colspan=8 align=\"center\">\n";

        //Boutons
        $html .= "<TABLE align=\"center\"><TR>";
        $html .= "<TD><INPUT TYPE=\"submit\" VALUE=\""._("Précédent")."\" onclick=\"ADFormValid = true; assign('Ama-1');\"></TD>";
        $html .= "<TD><INPUT TYPE=\"submit\" VALUE=\""._("Imprimer détails")."\" onclick=\"ADFormValid=true; assign('Ama-3');\"></TD>";
        $html .= "<TD><INPUT TYPE=\"submit\" VALUE=\""._("Retour menu")."\" onclick=\"ADFormValid=true; assign('Gen-6');\"></TD>";
        $html .= "</TR></TABLE>\n";

        $html .= "</TD></TR></TABLE>\n";
        $html .= "<INPUT TYPE=\"hidden\" NAME=\"m_agc\"><INPUT TYPE=\"hidden\" NAME=\"prochain_ecran\"></FORM>\n";

        echo $html;
    }
}
//-----------------------------------------------------------------
//------- Ecran Ama-3 : Affichage PDF                       -------
//-----------------------------------------------------------------
else if ($global_nom_ecran == "Ama-3")
{    
    //error_reporting(E_ALL);
    //ini_set("display_errors", "on");    
    
    $loginDistant = AuditVisualisation::LOGIN_DISTANT;
    
    $login = $SESSION_VARS['criteres']['login'];
    $num_fonction = $SESSION_VARS['criteres']['num_fonction'];
    $trans_reussi = $SESSION_VARS['criteres']['trans_reussi'];
    $IdAgence = $SESSION_VARS['criteres']['IdAgence'];
    $num_client = $SESSION_VARS['criteres']['num_client'];
    $date_min = $SESSION_VARS['criteres']['date_min'];
    $date_max = $SESSION_VARS['criteres']['date_max'];
    $trans_local = $SESSION_VARS['criteres']['trans_local'];
    $trans_distant = $SESSION_VARS['criteres']['trans_distant'];
    $trans_fin = $SESSION_VARS['criteres']['trans_fin'];   
    
    $nom_agence = '';
    
    if(!empty($IdAgence)) {
        $nom_agence = AgenceRemote::getRemoteAgenceName($IdAgence);
    }    
    
    if(!empty($num_fonction)) {
        if($num_fonction == 'depot') $num_fonction = 93;
        elseif($num_fonction == 'retrait') $num_fonction = 92;
    }
    
    if(empty($trans_reussi)) $trans_reussi = _('Non');
    else $trans_reussi = _('Oui');
       
    $criteres = array (
            _("Login") => $login,
            _("Fonction") => $adsys["adsys_fonction_systeme"][$num_fonction],
            _("Transactions réussies") => $trans_reussi,
            _("Agence") => $nom_agence,
            _("Numéro client") => $num_client,
            _("Date min") => date($date_min),
            _("Date max") => date($date_max),
            _("N° transaction local") => $trans_local,
            _("N° transaction distant") => $trans_distant
    );      
    
    $criteres['criteres_recherche'] = $SESSION_VARS['criteres'];
    
    // Infos sur les transactions    
    $DATAS = $AuditVisualisationObj->getMultiAgenceAuditData($SESSION_VARS['criteres']);   
    
    $xml = xml_operations_deplace($DATAS, $criteres); //Génération du code XML
        
    if(!empty($login) && $login == $loginDistant) { //clients interne/rapport distant
        $xslt = 'operations_deplace_clients_interne.xslt';
    }
    else { // clients externes / rapport local       
        $xslt = 'operations_deplace_clients_externe.xslt';
    }
        
    $fichier_pdf = xml_2_xslfo_2_pdf($xml, $xslt); //Génération du XSL-FO et du PDF   
        
    //Message de confirmation + affichage du rapport dans une nouvelle fenêtre
    $show_pdf = get_show_pdf_html("Gen-6", $fichier_pdf);    
    echo $show_pdf;    
}

else signalErreur(__FILE__,__LINE__,__FUNCTION__, sprintf(_("L'écran %s n'a pas pu être trouvé"), $global_nom_ecran));

if(isset($AuditVisualisationObj)) unset($AuditVisualisationObj);
?>