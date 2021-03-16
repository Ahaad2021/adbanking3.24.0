<?php
/* vim: set expandtab softtabstop=2 shiftwidth=2 foldmethod=marker: */

/**
 * T361
 * Recherche d'un client et leur compte courant  dans la BD 
 * Cette opération comprends un seul ecran :visualisation direct des comptes courants du client) :
 * - Cpt : Visualisation directe du client passé en parametre
 * @package Clients
 **/

require_once('lib/html/HTML_GEN2.php');
require_once('lib/dbProcedures/client.php');
require_once('lib/dbProcedures/epargne.php');
require_once('lib/misc/VariablesGlobales.php');
require("lib/html/HtmlHeader.php");

echo "<script language=\"javascript\">";
echo "opener.onfocuso = react;\n";
echo "function react() { window.focus(); }\n";
echo "</script>";

/* {{{ Cpt : Visualisation directe du client passé en parametre */

// id_client est passé dans le cpt_dest
if (isset ( $cpt_dest )) {
	
	// getnum cpte parts sociale 361
	$ListeComptes = get_comptes_epargne_compte_courant ( $cpt_dest, $devise );
	
	// affichage de l'ecran avec les compte courant du client
	$title = _ ( "Choix du compte" );
	if ($devise != NULL)
		$title .= " ($devise)";
	$myForm = new HTML_GEN2 ( $title );
	$choix = array ();
	if (isset ( $ListeComptes )) {
		$xtHTML = "
              <h3> " . _ ( "Client" ) . " : $cpt_dest</h3>
              <table align=\"center\">
              <tr bgcolor=\"$colb_tableau\">
              <td><b>" . _ ( "Numéro de compte" ) . "</b></td>
              <td><b>" . _ ( "Intitulé" ) . "</b></td>
              <td><b>" . _ ( "Devise" ) . "</b></td>
              </tr>";
		foreach ( $ListeComptes as $key => $value ) {
			$numCpte = $value ["num_complet_cpte"];
			$intCpte = $value ["libel"];
			$devCpte = $value ["devise"];
			$choix [$key] = $numCpte . " " . $intCpte;
			$numCpte = addslashes ( $numCpte );
			$key = addslashes ( $key );
			$xtHTML .= " <tr bgcolor=\"$colb_tableau\"> ";
			if (! isset ( $SESSION_VARS ['devise_cpte_dest'] )) {
				$xtHTML .= " <td><a onclick=\"validateSearch('$numCpte', $key)\" href=\"#\">$numCpte</a>";
			} else {
				$xtHTML .= " <td><a onclick=\"validateSearch('$numCpte', $key,'$devCpte')\" href=\"#\">$numCpte</a>";
			}
			
			$xtHTML .= " </td> ";
			$xtHTML .= "
                 <td>$intCpte</td>
                 <td>$devCpte</td>
                 </tr>";
		}
		$xtHTML .= "</table>";
	}
	if (! isset ( $SESSION_VARS ['devise_cpte_dest'] )) {
		$JScode1 = "
              function validateSearch(id, num)
            {
              window.opener.document.ADForm.cpt_dest.value = id;
              window.opener.document.ADForm.cpt_dest_hdd.value = id;
              window.close();
            }
              ";
	} else {
		$JScode1 = "
              function validateSearch(id, num,devise)
            {
              window.opener.document.ADForm.cpt_dest.value = id;
              window.opener.document.ADForm.cpt_dest_hdd.value = id;
              window.close();
            }
              ";
	}
	unset ( $SESSION_VARS ['devise_cpte_dest'] );
	unset ( $SESSION_VARS ["is_depot"] );
	
	$myForm->addJS ( JSP_FORM, "JScode1", $JScode1 );
	$xtHTML .= "<br>";
	$myForm->addHTMLExtraCode ( "xtHTML", $xtHTML );
	//$myForm->addFormButton ( 1, 1, "new_search", _ ( "Nouvelle recherche" ), TYPB_SUBMIT );
	$myForm->addFormButton ( 1, 2, "cancel", _ ( "Annuler" ), TYPB_SUBMIT );
	$myForm->setFormButtonProperties ( "cancel", BUTP_JS_EVENT, array (
			"onclick" => "window.close();" 
	) );
	$myForm->addHiddenType ( "Recherche", "KO" );
	$myForm->buildHTML ();
	echo $myForm->getHTML ();
} 

else
	signalErreur ( __FILE__, __LINE__, __FUNCTION__, _ ( "[rech_client_cpt_courant.php] Problème de propagation des variables" ) );

require ("lib/html/HtmlFooter.php");
?>