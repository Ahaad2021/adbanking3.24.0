<?php
/* vim: set expandtab softtabstop=2 shiftwidth=2 foldmethod=marker: */

require_once 'lib/misc/VariablesSession.php';
require_once 'lib/dbProcedures/login_func.php';
require_once 'lib/dbProcedures/historique.php';
require_once 'lib/misc/VariablesGlobales.php';
require_once 'lib/misc/Erreur.php';
require_once 'lib/multilingue/traductions.php'; // La classe doit être déclarée avant l'ouverture de la session
require_once 'lib/misc/divers.php';
require_once 'lib/html/HTML_GEN2.php';
require_once 'lib/html/HTML_message.php';
require_once 'lib/html/HTML_erreur.php';
require_once 'lib/dbProcedures/agence.php';

/* Vérifié l'accès */
checkADBankingAccess();


$appli = "main"; // On est dans l'application (et pas dans le batch)
$no_screen = true; // On est entré dans aucun écran

if ($prochain_ecran == "delete_session") { // Supprime une session d'un login
  $no_screen = false;
  delete_session($login);
  $prochain_ecran = "login_check";
}

if ($prochain_ecran == "login_check") {
  // On a entré code user et pwd et validé
  // La session est ici déjà créée (par les session_register dans VariableSession.php)
  // session_id() doit donc retourner l'identifiant de cette session

  $no_screen = false;
  $retour = check_login(session_id(), $login, $pwd, $REMOTE_ADDR, date("H:i:s d F Y")); // Vérifie si le mot de passe est correct, si oui : crée un enregistrement pour la session
  $res = $retour['result'];
  $new_login = $retour['login'];

  // Doit être après le delete_session sinon on a une erreur sur session_start car les entêtes HTTP ont déjà été envoyés.
  require 'lib/html/HtmlHeader.php';

  if ($res == -5) {
    // Batch en cours de traitement
    // On interdit toute connexion car il serait dangereux de pouvoir modifier les données pendant l'exécution du batch
    ajout_log_systeme(date("H:i:s d F Y"), _("Tentative connexion mais agence en cours de batch"), $login, $REMOTE_ADDR);
    $MyPage = new HTML_erreur(_("Connexion"));
    $MyPage->setMessage(_("Impossible d'établir une session avec l'agence : celle-ci est en cours de traitements de nuit !"));
    $MyPage->addButton(BUTTON_OK, "login");
    $MyPage->buildHTML();
    echo $MyPage->HTML_code;
  }
  if ($res == -4) {
    // Erreur agence fermée
    ajout_log_systeme(date("H:i:s d F Y"), _("Tentative connexion mais agence non-ouverte"), $login, $REMOTE_ADDR);
    $MyPage = new HTML_erreur(_("Connexion"));
    $MyPage->setMessage(_("Impossible d'établir une session avec l'agence : celle-ci n'est pas ouverte !"));
    $MyPage->addButton(BUTTON_OK, "login");
    $MyPage->buildHTML();
    echo $MyPage->HTML_code;
  } else if ($res == -3) {
    // Double connexion meme user
    ajout_log_systeme(date("H:i:s d F Y"), _("Tentative double connexion d'un même utilisateur"), $login, $REMOTE_ADDR);
    $MyPage = new HTML_message(_("Double connexion"));
    $MyPage->setMessage(_("Vous êtes déjà connecté sur le système; voulez-vous quitter votre session précédente ?"));
    $MyPage->addButton(BUTTON_OUI, "delete_session");
    $MyPage->addButton(BUTTON_NON, "login");
    $MyPage->buildHTML();
    echo $MyPage->HTML_code;
    session_register("login"); // On sauve pour faire le login direct après
    session_register("pwd");
    session_register("recup_data");
  } else if ($res == -1) {
    // Login ou mot de passe incorrect
    $no_screen = true;
    $bad_pwd = true;
    ajout_log_systeme(date("H:i:s d F Y"), _("Code utilisateur incorrect"), $login, $REMOTE_ADDR);
  } else if ($res == -6) {
    // Utilisateur inactif
    $no_screen = true;
    $inactivite= true;
    ajout_log_systeme(date("H:i:s d F Y"), _("utilisateur inactif"), $login, $REMOTE_ADDR);
    
  }
  	else if ($res == -8) {
  	// Tentative de connexion plus de 5 fois
  		$no_screen = true;
  		$bad_attempt = true;
  		ajout_log_systeme(date("H:i:s d F Y"), _("tentative de connexion dépassé"), $login, $REMOTE_ADDR);
  
  }
      
   else  if ($res == -7) {
    // Batch non exécuté : pas de connexion sauf pour l'administrateur
    ajout_log_systeme(date("H:i:s d F Y"), _("Tentative connexion mais batch non exécuté"), $login, $REMOTE_ADDR);
    $MyPage = new HTML_erreur(_("Connexion"));
    $MyPage->setMessage(_("Impossible d'établir une session avec l'agence : le batch ne s'est pas exécuté correctement la veille !")."<br />"._("Contactez votre administrateur système"));
    $MyPage->addButton(BUTTON_OK, "login");
    $MyPage->buildHTML();
    echo $MyPage->HTML_code;
  } else if ($res == 1) {
    // Login OK : On va chercher toutes les infos concernant le poste client
    $valeurs = get_login_info(session_id());
    $agence = $valeurs['id_ag'];
    $date = $valeurs['date'];

    // On enregistre dans la table des logs
    ajout_log_systeme(date("H:i:s d F Y"), _("Login OK"), $login, $REMOTE_ADDR);

    // Vérification de la licence
    if ($retour['errCode'] != NO_ERR) {
      $MyPage = new HTML_erreur(_("Licence"));
      $MyPage->setMessage(_("Avertissement ")." : ".$error[$retour['errCode']]);
      $MyPage->addButton(BUTTON_OK, "login");
      $MyPage->buildHTML();
      echo $MyPage->HTML_code;
    } else {
      $prochain_ecran = "login_ok";
    }

    session_register("recup_data");
  }
}

if ($prochain_ecran == "login_encaisse") {
  $no_screen = false;

  $valeurs = get_login_info(session_id());
  $global_monnaie = $valeurs['monnaie'];
  $global_monnaie_prec = $valeurs['monnaie_prec'];

  $MyPage = new HTML_GEN2(sprintf(_("Saisie encaisse du guichet '%s'"),$valeurs['libel_guichet']));
  $MyPage->addField("encaisse", _("Encaisse"), TYPC_MNT);
  $MyPage->setFieldProperties("encaisse", FIELDP_IS_REQUIRED, true);

  $MyPage->addFormButton(1,1,"butok",_("Valider"),TYPB_SUBMIT);
  $MyPage->setFormButtonProperties("butok", BUTP_PROCHAIN_ECRAN, "login_ok");

  $MyPage->addFormButton(2,1,"butanul",_("Annuler connexion"),TYPB_SUBMIT);
  $MyPage->setFormButtonProperties("butanul", BUTP_PROCHAIN_ECRAN, "anul_login");
  $MyPage->setFormButtonProperties("butanul", BUTP_CHECK_FORM, false);

  $encaisse = get_encaisse($valeurs['id_guichet']);
  $js =  "if ((recupMontant(document.ADForm.encaisse.value) != $encaisse) && (document.ADForm.encaisse.value != ''))\n";
  $js .= "{\n";
  $js .= "  ++nbre_tentative;\n";
  $js .= "  if (nbre_tentative < $nbre_max_tentative_encaisse)\n";
  $js .= "  {\n";
  $js .= "    msg += '"._("Le montant de l\\'encaisse ne correspond pas !")." !\\n';\n";
  $js .= "    ADFormValid = false;\n";
  $js .= "  }\n";
  $js .= "  else\n";
  $js .= "  {\n";
  $js .= "    msg += '".sprintf(_("Ceci est votre \\'%s\\'ème tentative : veuillez vous adresser auprès d\\'un administrateur !"),"+nbre_tentative+")."\\n';\n";
  $js .= "    assign('anul_login');ADFormValid = true;\n";
  $js .= "  }\n";
  $js .= "}\n";

  $MyPage->addJS(JSP_BEGIN_CHECK, "js1", $js);
  $MyPage->addJS(JSP_FORM, "js2", "nbre_tentative=0;clique=false;");

  $MyPage->buildHTML();
  echo $MyPage->getHTML();
}

// Doit être après le delete_session sinon on a une erreur sur session_start car les entêtes HTTP ont déjà été envoyés.
require 'lib/html/HtmlHeader.php';

if ($prochain_ecran == "login_ok") {

  $no_screen = false;

  // Bascule l'état du guichet à 'ouvert'
  $valeurs=get_login_info(session_id());
  if ($valeurs['id_guichet'] != '') {
    ouvertureGuichet($valeurs['id_guichet']);
  }

  // Set session nb_clients_actifs
  $_SESSION['nb_clients_actifs'] = getNumClientsActifs();

  // Si multi id agence pa renseigné
  if((null === $_REQUEST['m_agc']) || (null !== $_REQUEST['m_agc'] && trim($_REQUEST['m_agc'])=='')){
      $_REQUEST['m_agc'] = getNumAgence();
  }

  // La variable $recup_data existe : on vient donc d'un ecran de reprise de données
  // On charge ADbanking pour que tous les variables de session soient initialisées puis on referme la fenetre
  if (isset($recup_data)) {
    echo "<SCRIPT type=\"text/javascript\">\n";
    echo "window.parent.location = \"$SERVER_NAME/main/main.php?m_agc=".$_REQUEST['m_agc']."\";\n";
    echo "opener=self;\n";
    echo "self.close();\n";
    echo "</SCRIPT>\n";
  } else {
    echo "<SCRIPT type=\"text/javascript\">\n";
    echo "window.parent.location = \"$SERVER_NAME/main/main.php?m_agc=".$_REQUEST['m_agc']."\";\n";
    echo "</SCRIPT>\n";
  }

}

if ($prochain_ecran == "anul_login") {
  $no_screen = false;
  $prochain_ecran = "Out-3";
  include "modules/misc/logout.php";
}

if (($no_screen == true) || ($prochain_ecran == "login")) {
		// Ecran de saisie du code utilisateur et du mot de passe. En principe, premier écran renconré par l'utilisateur. Aboutit à login check.
	$id_agc = getNumAgence ();
	// verification si repertoire jasper_config contient un fichier adbanking.ini
	// sinon il va le generer

		$dir = $doc_prefix . "/jasper_config/";
		//si le repertoire contient aucun fichier
		if (count ( glob ( "$dir/*" ) ) === 0) {
			if($ini_array ["DB_host"] == "localhost" OR $ini_array ["DB_host"]==" " OR $ini_array ["DB_port"] =" ") {
				
				$ip = $_SERVER['SERVER_ADDR'];
				$port = 5432;
			verifyJasperConfig ( $id_agc, $ip, $port, $ini_array ["DB_user"], $ini_array ["DB_pass"], $ini_array ["DB_name"], $doc_prefix );
			}else{
				verifyJasperConfig ( $id_agc, $ini_array ["DB_host"], $ini_array ["DB_port"], $ini_array ["DB_user"], $ini_array ["DB_pass"], $ini_array ["DB_name"], $doc_prefix );
			}	
			}
	
	 

  // Vérification des paths
  $retour = checkPaths();
  if ($retour->errCode != NO_ERR) {
    $MyError = new HTML_erreur(_("Erreur lors de la vérification des chemins d'accès"));
    $MyError->setMessage($error[$retour->errCode]." : ".$retour->param);
    $MyError->addButton(BUTTON_OK, "");
    $MyError->buildHTML();
    echo $MyError->HTML_code;
  } else {
      
    //global $dbHandler;
    require_once 'ad_ma/app/controllers/misc/class.db.oo.php';

    $has_multi_agence = false;

    if(isAgenceSiege()) {
        $db = $dbHandler->openConnection();
        $sql = "SELECT * FROM adsys_multi_agence ORDER BY app_db_description ASC";

        $result = $db->query($sql);
        if (DB::isError($result)) {
            $dbHandler->closeConnection(false);
            //signalErreur(__FILE__, __LINE__, __FUNCTION__);
        }
        $dbHandler->closeConnection(true);

        if (!DB::isError($result) && $result->numRows() > 1) {
            $has_multi_agence = true;
            echo "<br><br>";
        }else{
            echo "<br><br><br><br>"; // <br>
        }
    } else {
        echo "<br><br><br><br>";
    }

    // Début formulaire
    if ($DEBUG) {
      echo "<FORM ID=\"ADForm\" NAME=\"ADForm\" METHOD=\"POST\" ACTION=\"$PHP_SELF\">\n";
    } else {
      echo "<FORM ID=\"ADForm\" NAME=\"ADForm\" METHOD=\"POST\" ACTION=\"$PHP_SELF\" autocomplete=\"off\">\n";
    }

    echo '<TABLE ALIGN="center" BORDER=0 CELLSPACING=8 CELLPADDING=8>'."\n";

    if ($inactivite == true) {
      $inactivite == false;

      //echo "<tr>";
      echo "<td ALIGN=center VALIGN=middle WIDTH=200 COLSPAN=2><FONT COLOR=\"$colt_error\">"._("Utilisateur inactif, veuillez contacter l'administrateur svp !")."</FONT></td>";
      echo "</tr>\n";
    }

    // Eventuelement une pré-première ligne en cas de mauvais pwd
    if ($bad_pwd == true) {
      //echo "<tr>";
      echo "<td ALIGN=center VALIGN=middle WIDTH=200 COLSPAN=2><FONT COLOR=\"$colt_error\">"._("Nom d'utilisateur ou mot de passe incorrect")."</FONT></td>";
      echo "</tr>\n";
    }
    
    if ($bad_attempt == true) {    	
    	echo "<td ALIGN=center VALIGN=middle WIDTH=200 COLSPAN=2><FONT COLOR=\"$colt_error\">"._("Votre code utilisateur a été bloqué! Contactez l'administrateur")."</FONT></td>";
  			echo "</tr>\n";
    }
    
    $agence_counter = 0;
    if ($has_multi_agence) {
        // Ligne Agence
        echo "<tr BGCOLOR=\"$colb_login_droite_tableau\">";
        echo "<td CLASS=\"login_droite\" ALIGN=right VALIGN=middle>"._("Votre agence")."</td>";
        echo "<td CLASS=\"login_droite\" ALIGN=left VALIGN=middle>";
        echo "<select id=\"m_agc\" name=\"m_agc\" onchange=\"if(this.value!=''){top.window.location.href='/login/login2.php?m_agc='+this.value;}\">";
        echo "<option value=\"\"> - - Choisissez votre agence - - </option>";
        //$curr_agence_id = (null !== $_REQUEST['m_agc'] && $_REQUEST['m_agc'] > 0) ? $_REQUEST['m_agc'] : getNumAgence();
        $curr_agence_id = $_REQUEST['m_agc'];

        while ($prod = $result->fetchrow(DB_FETCHMODE_ASSOC)) {
            
            $ini_file_path = $doc_prefix."/jasper_config/adbanking".$prod["id_agc"].".ini";
            
            if(file_exists($ini_file_path)) {
                $plaintext = trim($prod['app_db_password']);
                $password = trim($prod['app_db_host']).'_'.trim($prod['app_db_name']);

                $prod['app_db_password'] = phpseclib_Decrypt($plaintext, $password);

                if (DBC::pingConnection($prod, 1) === TRUE) { // Vérifié si la BDD est active
                    $agence_id = $prod["id_agc"];
                    $agence_name = trim($prod["app_db_description"]);

                    echo "<option value=\"".$agence_id."\" ".(($curr_agence_id==$agence_id)?"selected":"").">".$agence_name." ($agence_id)</option>";
                    
                    $agence_counter++;
                }
            }
        }
        echo "</select>";
        echo "</td></tr>\n";
    }

    // Première ligne
    echo "<tr BGCOLOR=\"$colb_login_droite_tableau\">";
    echo "<td CLASS=\"login_droite\" ALIGN=\"right\" VALIGN=\"middle\" WIDTH=200>"._("Code utilisateur")."</td>";
    echo "<td CLASS=\"login_droite\" ALIGN=\"left\" VALIGN=\"middle\" WIDTH=200>";
    echo "<INPUT type=\"text\" name=\"login\" value=\"$login\"></td>";
    echo "</tr>\n";

    // Deuxième ligne
    echo "<tr BGCOLOR=\"$colb_login_droite_tableau\">";
    echo "<td CLASS=\"login_droite\" ALIGN=right VALIGN=middle>"._("Mot de passe")."</td>";
    echo "<td CLASS=\"login_droite\" ALIGN=left VALIGN=middle><INPUT type=\"password\" name=\"pwd\" value=\"\"></td>";
    echo "</tr>\n";

    echo "<INPUT type=\"hidden\" name=\"prochain_ecran\">";
    echo "<INPUT type=\"hidden\" name=\"recup_data\" value=\"$recup_data\">";
    echo "<INPUT type=\"hidden\" id=\"m_agc\" name=\"m_agc\" value=\"".$_REQUEST['m_agc']."\">";

    // Droite : troisième ligne
    echo "<tr>";
    echo "<td ALIGN=\"center\" VALIGN=\"middle\" COLSPAN=2><INPUT TYPE=\"submit\" NAME=\"enterButton\" Value=\""._("Valider")."\" onclick=\"";
    
    if($has_multi_agence && $agence_counter>0) {
        echo "if (document.getElementById('m_agc').selectedIndex == '') {alert('"._("Veuillez choisir votre agence")."');return false;} else ";
    }
    
    echo "if (document.ADForm.pwd.value == '') {alert('"._("Vous devez entrer un mot de passe")."');return false;} else {document.ADForm.m_agc.value='".$_REQUEST['m_agc']."';document.ADForm.prochain_ecran.value = 'login_check'; return true;}\"></td>";
    echo '<SCRIPT type="text/javascript"> enterButtonExist=true; </SCRIPT>';
    echo "</tr>\n";

    //Fin du formulaire
    echo "</TABLE></FORM>\n";

    session_unregister("login");
    session_unregister("pwd");
    session_unregister("recup_data");

  }
}

require_once 'lib/html/HtmlFooter.php';
