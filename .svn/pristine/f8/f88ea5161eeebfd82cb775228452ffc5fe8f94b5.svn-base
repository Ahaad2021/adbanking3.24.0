<?php
//error_reporting(E_ALL);
//ini_set("display_errors", "on");

// Multi agence includes
require_once 'ad_ma/app/models/AgenceRemote.php';
require_once 'ad_ma/app/models/AuditVisualisation.php';
require_once 'ad_ma/app/models/Client.php';
require_once 'ad_ma/app/models/Parametrage.php';
require_once 'ad_ma/app/models/TireurBenef.php';

/**
 * detail_transaction
 *
 */
require_once 'lib/misc/VariablesGlobales.php';
require_once 'lib/misc/divers.php';
require_once 'lib/misc/tableSys.php';
require_once 'lib/html/html_table_gen.php';

require 'lib/html/HtmlHeader.php';

//-----------------------------------------------------------------
//-------         Initialisation                            -------
//----------------------------------------------------------------

$AuditVisualisationObj = new AuditVisualisation();

//-----------------------------------------------------------------
//-------         Execution                                 -------
//-----------------------------------------------------------------

global $global_multidevise;

if (isset($id_transaction) && isset($id_agence)) 
{
    $details = $AuditVisualisationObj->getOneRemoteTransactionDetails($id_transaction, $id_agence);
    
    $nom_agence = AgenceRemote::getRemoteAgenceName($id_agence);

    //entête du formulaire
    echo "<FORM name=\"ADForm\" action=\"$PHP_SELF\" method=\"post\">\n";

    //Infos générarles sur la transaction
    echo "<TABLE align=\"center\" bgcolor=$colb_tableau border=$tableau_border cellspacing=$tableau_cellspacing cellpadding=$tableau_cellpadding>\n";

    //Ligne titre
    echo "<TR bgcolor=$colb_tableau>
            <TD><b>n°</b></TD>
            <TD align=\"center\"><b>Date</b></TD>";
    echo " <TD align=\"center\"><b>"._("Heure")."</b></TD>
            <TD align=\"center\"><b>"._("Fonction")."</b></TD>
            <TD align=\"center\"><b>"._("Login")."</b></TD>
            <TD align=\"center\"><b>"._("Agence externe")."</b></TD>
            <TD align=\"center\"><b>"._("N° client")."</b></TD>
           </TR>\n";

    //Ligne contenu transaction
    echo "<TR bgcolor=$colb_tableau>\n";
    //n°
    echo "<TD>".$details['id_his']."</TD>";
    //Date
    echo "<TD>".pg2phpDate($details['date'])."</TD>";
    //Heure
    echo "<TD>".pg2phpHeure($details['date'])."</TD>";
    //Fonction
    echo "<TD>".adb_gettext($adsys["adsys_fonction_systeme"][$details['type_fonction']])."</TD>\n";
    //Login
    if ($details['type_fonction'] == 93 || $details['type_fonction'] == 92){
        if ($details['login'] == "distant") {
            $infos_details = explode('-', $details['infos']);
            $login_details = explode('=', $infos_details[1]);
            echo "<TD>" . $login_details[1] . "</TD>\n";
        }
        else{
            echo "<TD>".$details['login']."</TD>\n";
        }
    }else{
        echo "<TD>".$details['login']."</TD>\n";
    }
    //Agence
    echo "<TD>".$nom_agence."</TD>\n";
    //N° client
    if ($details['id_client'] > 0) echo "<TD align=\"center\">".sprintf("%06d", $details['id_client'])."</TD>\n";
    else echo "<TD></TD>\n";
    echo "</TR>\n";
    echo "</TABLE><br>";
    //infos sur les écritures et les mouvements
    $html = '';

    //infos écritures et mouvements
    echo "<BR/><BR/><TABLE align=\"center\" bgcolor=$colb_tableau border=$tableau_border cellspacing=$tableau_cellspacing cellpadding=$tableau_cellpadding>\n";

    //FIXME : les débits doivent venir avant les crédits

    //Ligne titre
    // NB/TF J'ai volontairement supprimé les références au journal vu que dorénavant le code journal est inclus dans le numéro d'écriture
    echo "<TR bgcolor=$colb_tableau><TD><b>"._("N° écriture")."</b></TD><TD><b>"._("Libellé")."</b></TD>";
    echo "<TD align=\"center\"><b>"._("Compte")."</b></TD><TD align=\"center\"><b>"._("Compte Client")."</b></TD>";
    echo "<TD align=\"center\"><b>"._("Débit")."</b></TD><TD align=\"center\"><b>"._("Crédit")."</b></TD></TR>\n";

    //Lignes contenus
    $id = '';
    $html = '';
    $color = $colb_tableau;    
    

    foreach ($details['ecritures_local'] as $value)
    {
        $count = 1;
        $color = ($color == $colb_tableau? $colb_tableau_altern : $colb_tableau);

        foreach ($value['mouvements'] as $value_mvt) {

            //On alterne la couleur de fond a chaque écriture
            $html .= "<TR bgcolor=$color>\n";

            //première ligne du tableau
            if ($count == 1) {
                //N° d'écriture
                $html .= "<TD <SPAN> nowrap><CODE>".$value['ref_ecriture']."</CODE></TD>";

                //Libellé de l'écriture
                $html .= "<TD <SPAN> nowrap width=\"10%\">".$value['libel_ecriture']."</TD>";
            }

            //N° compte
            $html .= "<TD nowrap>".$value_mvt['compte']."</TD>";
            //N° compte client
            $html .= "<TD nowrap>".$value_mvt['num_complet_cpte']."</TD>";
            if ($global_multidevise)
                setMonnaieCourante($value_mvt["devise"]);
            //Montant
            if ($value_mvt['sens'] == 'd') {
                $html .= "<TD align=\"right\" nowrap>".afficheMontant($value_mvt['montant']);
                $html .=" ";
                if ($global_multidevise)
                    $html .=$value_mvt["devise"];
                $html .="</TD><TD></TD>";
            } else {
                $html .= "<TD></TD><TD align=\"right\" nowrap>".afficheMontant($value_mvt['montant']);
                $html .=" ";
                if ($global_multidevise)
                    $html .=$value_mvt["devise"];
                $html .="</TD>";
            }
            $html .= "</TR>";
            $count++;
        }
        $count--; // On en a un de trop
        $html = str_replace('<SPAN>', "rowspan=$count", $html);

    }
    
    echo $html;
    echo "</TABLE>";
    
    // Si applicable, détails tirés de ad_his_ext
    if (is_array($details["infos_ext"]))
    {
        $INFOS_EXT = $details["infos_ext"];
    
        $pdo_conn = AgenceRemote::getRemoteAgenceConnection($id_agence);
        $ParametrageObj = new Parametrage($pdo_conn, $id_agence);
        $liste_piece = $ParametrageObj->getListeTypePieceComptables();
    
        $table = new HTML_TABLE_table(2, TABLE_STYLE_CLASSIC);
        $table->set_property("title",_("Détails supplémentaires"));
        $table->set_property("border",$tableau_border);
        $table->add_cell(new TABLE_cell(_("Type de pièce")));
        $table->set_cell_property("width","20%");
        $table->add_cell(new TABLE_cell($liste_piece[$INFOS_EXT["type_piece"]]));
        $table->add_cell(new TABLE_cell(_("N° de pièce")));
        $table->add_cell(new TABLE_cell($INFOS_EXT["num_piece"]));
        $table->add_cell(new TABLE_cell(_("Date de la pièce")));
        $table->add_cell(new TABLE_cell(pg2phpDate($INFOS_EXT["date_piece"])));
    
        if ($INFOS_EXT["sens"] == 'in ')
            $table->add_cell(new TABLE_cell(_("Tireur")));
        else
            $table->add_cell(new TABLE_cell(_("Bénéficiaire")));
    
        if(isset($INFOS_EXT["id_tireur_benef"])) {
            $TireurBenefObj = new TireurBenef($pdo_conn, $id_agence);
            $TIB = $TireurBenefObj->getTireurBenefDatas($INFOS_EXT['id_tireur_benef']);
        } else {
            $TIB = array();
        }
         
        $libel_pays = '';
    
        if(!empty($TIB["pays"])) {
            $libel_pays = getLibel("adsys_pays", $TIB["pays"]);
        }
    
        $table->add_cell(new TABLE_cell("<B>".$TIB["denomination"]."</B><BR/>".$TIB["adresse"]."<BR/>".$TIB["code_postal"]."  ".$TIB["ville"]."<BR/>".$libel_pays));
        $table->add_cell(new TABLE_cell(_("Donneur d'ordre")));
    
        if ($INFOS_EXT["id_pers_ext"] != NULL) {
            $ClientObj = new Client($pdo_conn, $id_agence);
            $PERS_EXT = $ClientObj->getPersonneExt(array("id_pers_ext" => $INFOS_EXT["id_pers_ext"]));
        }
        $table->add_cell(new TABLE_cell($PERS_EXT[0]["denomination"]));
        $table->add_cell(new TABLE_cell(_("Communication")));
        $table->add_cell(new TABLE_cell($INFOS_EXT["communication"]));
        $table->add_cell(new TABLE_cell(_("Remarque")));
        $table->add_cell(new TABLE_cell($INFOS_EXT["remarque"]));
    
        echo $table->gen_HTML();
    
        AgenceRemote::unsetRemoteAgenceConnection($pdo_conn);
    }
    
    echo "<BR> <BR> <P align=\"center\"> <INPUT type=\"submit\" value=\""._("Fermer")."\" onclick=\"window.close();\"> </P>";
    echo "</FORM>";
    
    if(isset($AuditVisualisationObj)) unset($AuditVisualisationObj);
    if(isset($ParametrageObj)) unset($ParametrageObj);
    if(isset($TireurBenefObj)) unset($TireurBenefObj);
    if(isset($ClientObj)) unset($ClientObj);
    

}
else {
    signalErreur(__FILE__,__LINE__,__FUNCTION__);
}

require 'lib/html/HtmlFooter.php';
?>