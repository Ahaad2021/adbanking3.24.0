<?php
    require_once 'lib/html/HTML_GEN2.php';
    require_once 'lib/dbProcedures/rapports.php';
    require_once 'lib/misc/divers.php';
    require_once 'lib/misc/csv.php';
    require_once "lib/html/HTML_menu_gen.php";

    if ($global_nom_ecran == "Vdr-1") {
        $MyMenu = new HTML_menu_gen(_("Sélection des type de rapports"));

        $MyMenu->addItem(_("Reçu dépôt ou retrait"), "$PHP_SELF?m_agc=".$_REQUEST['m_agc']."&prochain_ecran=Vdr-2", null, "$http_prefix/images/gestion_plan_comptable.gif","1");

        $MyMenu->buildHTML();
        echo $MyMenu->HTMLCode;
    }
    else if ($global_nom_ecran == "Vdr-2"){
        $MyPage = new HTML_GEN2();
        $MyPage->setTitle(_("Critères des rapports pour les reçu dépôt ou retrait"));

        $MyPage->addField("type_recu", _("Type de reçu"), TYPC_LSB );
        $MyPage->setFieldProperties("type_recu", FIELDP_ADD_CHOICES, array("depot" => "Dépôt", "retrait" => "Retrait"));
        $MyPage->setFieldProperties("type_recu", FIELDP_HAS_CHOICE_AUCUN, false);
        $MyPage->setFieldProperties("type_recu", FIELDP_HAS_CHOICE_TOUS, true);

        $MyPage->addField("num_client", _("N° de client"), TYPC_INT);
        $MyPage->addLink("num_client", "rechercher", _("Rechercher"), "#");
        $MyPage->setLinkProperties("rechercher", LINKP_JS_EVENT, array("onclick" => "OpenBrw('$SERVER_NAME/modules/clients/rech_client.php?m_agc=".$_REQUEST['m_agc']."&field_name=num_client', '"._("Recherche")."');return false;"));
        $MyPage->setFieldProperties("num_client", FIELDP_JS_EVENT, array("onkeypress" => "return true;"));

        $MyPage->addField("num_transactions", _("Numero de transactions"), TYPC_TXT );

        $MyPage->addField("date_debut", _("Date de début"), TYPC_DTG);
        $MyPage->setFieldProperties("date_debut", FIELDP_DEFAULT, date("01/m/Y"));

        $MyPage->addField("date_fin", _("Date de fin"), TYPC_DTG);
        $MyPage->setFieldProperties("date_fin", FIELDP_DEFAULT, date("d/m/Y"));

        $MyPage->addFormButton(1, 1, "valider", _("Chercher"), TYPB_SUBMIT);
        $MyPage->setFormButtonProperties("valider", BUTP_PROCHAIN_ECRAN, "Vdr-21");
        $MyPage->addFormButton(1, 2, "annuler", _("Annuler"), TYPB_SUBMIT);
        $MyPage->setFormButtonProperties("annuler", BUTP_PROCHAIN_ECRAN, "Gen-13");
        $MyPage->setFormButtonProperties("annuler", BUTP_CHECK_FORM, false);

        $MyPage->buildHTML();
        echo $MyPage->getHTML();
    }else if ($global_nom_ecran == "Vdr-21"){
        global $global_id_agence;
        $myForm = new HTML_GEN2();
        $myForm->setTitle(_("Liste des rapports"));

        $table = "<script src=\"/lib/misc/js/lib/jquery.min.js\" type=\"text/javascript\"></script>";

        $table .= "<table align=\"center\" cellpadding=\"5\" width=\"85% \" bgcolor=\"#FDF2A6\" border=\"1\" cellspacing=\"2\">
                    <thead>
                        <tr>
                            <th width=\"10% \" >N° client</th>
                            <th width=\"10% \" >Id transaction</th>
                            <th width=\"10% \" >Montant</th>
                            <th width=\"10% \" >Type reçu</th>
                            <th width=\"25% \" >Date de creation</th>
                            <th>chemin relatif</th>
                            <th width=\"20% \">Accès</th>
                        </tr>
                    <thead>  
                    <tbody>";

        $files_metadata = getRapports("/tmp", "/^.+\.([p][d][f])$/");
        $files_tags = getReportTag();

        foreach($files_metadata as $key => $file_metadata){
            $ref = $files_tags["report"]["epargne"][$file_metadata["relative_path"]];
            $filters = array("type_recu" => $type_recu, "num_client" => $num_client,  "num_transactions" => $num_transactions, "id_ag" =>$global_id_agence);
            $date_debut_timestamp = strtotime(str_replace('/', '-', $date_debut));
            $date_fin_timestamp = strtotime(str_replace('/', '-', $date_fin." 23:59:59"));
            $date_mask = DateTime::createFromFormat('F D Y H:i:s.', $file_metadata["modification_date"]);
            $file_timestamp = strtotime($date_mask->format('d-m-Y H:i:s.'));

            if((isFileValid($filters, $ref))) {
                if($file_timestamp >= $date_debut_timestamp && $file_timestamp <= $date_fin_timestamp) {
                    $file_path = "/tmp/" . $file_metadata["file_name"];
                    $url = "$SERVER_NAME/rapports/http/rapport_http.php?m_agc=" . $_REQUEST['m_agc'] . "&filename=".$file_path;
                    $type_report = 'epargne';
                    $table .= "<tr>
                           <td align=\"center\">" . $ref["num_client"] . "</td><td align=\"center\">" . $ref["num_transactions"] . "</td><td align=\"center\">" . $ref["montant"] . "</td><td align=\"center\">" . $ref["type_recu"] . "</td><td align=\"center\">" . $file_metadata["modification_date"] . "</td><td>" . $file_metadata["relative_path"] . "</td>
                           <td align=\"center\"><a href=$url data-type='download'>Télécharger</a>&nbsp;/&nbsp;<a href='#' data-type='deletion' data-file-path=$file_path data-file-type=$type_report>Supprimer</a></td>
                       </tr>";
                }
            }
        }

        $table .=   "<tbody>
                  </table>";

        $js = "$('[data-type=download]').click(function(e){
                    e.preventDefault();
                    window.open($(this).attr('href'));
                });
        
                $('[data-type=deletion]').click(function(e){
                    e.preventDefault();
                    var self = $(this);
                    $.ajax({ 
                        url: '".$SERVER_NAME."/lib/misc/ajax_handler.php',
                        data: {request: 'delete_report', file_path: $(self).attr('data-file-path'), parent_key: $(self).attr('data-file-type')},
                        error: function(err){console.log(err)},
                        success: function(request){
                            $(self).parent().parent().css('display', 'none');
                            console.log(request);
                        }
                    });
                });
        ";

        $myForm->addFormButton(1, 4, "retour", _("Retour"), TYPB_SUBMIT);
        $myForm->setFormButtonProperties("retour", BUTP_PROCHAIN_ECRAN, "Gen-13");

        $myForm->addHTMLExtraCode("xtHTML", $table);
        $myForm->addJS(JSP_FORM, "export_dispatcher", $js);
        $myForm->buildHTML();
        echo $myForm->getHTML();
    }
?>
