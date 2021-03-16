<?php

/* Information système #311


*/

require_once('lib/html/HTML_GEN2.php');
require_once('lib/html/HTML_message.php');
require_once('lib/dbProcedures/systeme.php');

if ($global_nom_ecran == 'Ifs-1') {
    $myForm = new HTML_GEN2(_("Information système"));
    $is_actif = TRUE;
    $InfoSystemeActif = getInfoSys($is_actif);

    $sss = getInfoSys();

    $myForm->addField("vrs_rpm", _("Version RPM"), TYPC_TXT);
    $myForm->setFieldProperties("vrs_rpm", FIELDP_IS_LABEL, true);
    $myForm->setFieldProperties("vrs_rpm", FIELDP_DEFAULT, $InfoSystemeActif[0]['version_rpm']);

    $myForm->addField("vrs_date", _("Date d'installation"), TYPC_TXT);
    $myForm->setFieldProperties("vrs_date", FIELDP_IS_LABEL, true);
    $myForm->setFieldProperties("vrs_date", FIELDP_DEFAULT, pg2phpDate($InfoSystemeActif[0]['date_creation']));

    $myForm->addField("vrs_base", _("Version Base de données"), TYPC_TXT);
    $myForm->setFieldProperties("vrs_base", FIELDP_IS_LABEL, true);
    $myForm->setFieldProperties("vrs_base", FIELDP_DEFAULT, $InfoSystemeActif[0]['version_bdd']);

    $myForm->addField("vrs_os", _("Version du système d'exploitation"), TYPC_TXT);
    $myForm->setFieldProperties("vrs_os", FIELDP_IS_LABEL, true);
    $myForm->setFieldProperties("vrs_os", FIELDP_DEFAULT, $InfoSystemeActif[0]['version_os']);

    $myForm->addField("vrs_php", _("Version PHP"), TYPC_TXT);
    $myForm->setFieldProperties("vrs_php", FIELDP_IS_LABEL, true);
    $myForm->setFieldProperties("vrs_php", FIELDP_DEFAULT, $InfoSystemeActif[0]['version_php']);

    $myForm->addField("vrs_apache", _("Version Apache"), TYPC_TXT);
    $myForm->setFieldProperties("vrs_apache", FIELDP_IS_LABEL, true);
    $myForm->setFieldProperties("vrs_apache", FIELDP_DEFAULT, $InfoSystemeActif[0]['version_apache']);

    $myForm->addField("vrs_active", _("Version Actuelle?"), TYPC_TXT);
    $myForm->setFieldProperties("vrs_active", FIELDP_IS_LABEL, true);
    $myForm->setFieldProperties("vrs_active", FIELDP_DEFAULT, $InfoSystemeActif[0]['is_active']==true?"OUI":"NON");


    $myForm -> addHTMLExtraCode("ExtraCode", "<br>");
        $table =& $myForm -> addHTMLTable("tb_info", 7,TABLE_STYLE_ALTERN);

        $table->add_cell(new TABLE_cell(" <h3 id='tb_his'>Historique</h3>", 7, 1));
        $table -> add_cell(new TABLE_cell(_("<b>Version RPM</b>"), 1, 1));
        $table -> add_cell(new TABLE_cell(_("<b>Date d'installation</b>"), 1, 1));
        $table -> add_cell(new TABLE_cell(_("<b>Version Base de données</b>"), 1, 1));
        $table -> add_cell(new TABLE_cell(_("<b>Version du système d'exploitation</b>"), 1, 1));
        $table -> add_cell(new TABLE_cell(_("<b>Version PHP</b>"), 1, 1));
        $table -> add_cell(new TABLE_cell(_("<b>Version Apache</b>"), 1, 1));
        $table -> add_cell(new TABLE_cell(_("<b>Version Actuelle?</b>"), 1, 1));

        $infos = getInfoSys();

        if ($infos != NULL) {
            foreach($infos as $key=>$value) {

                $version_rpm = explode('-',$value['version_rpm']);//AT-111 - afficher les versions Prod seulement
                if (!isset($version_rpm[1])){//AT-111 - afficher les versions Prod seulement
                    $table->add_cell(new TABLE_cell($value['version_rpm'], 1, 1));
                    $table->add_cell(new TABLE_cell(pg2phpDate($value['date_creation']), 1, 1));
                    $table->add_cell(new TABLE_cell($value['version_bdd']), 1, 1);
                    $table->add_cell(new TABLE_cell($value['version_os']), 1, 1);
                    $table->add_cell(new TABLE_cell($value['version_php']), 1, 1);
                    $table->add_cell(new TABLE_cell($value['version_apache']), 1, 1);
                    $table->add_cell(new TABLE_cell($value['is_active']=='t'?"OUI":"NON"), 1, 1);
                }
            }
        }


    $myForm->addFormButton(1, 1, "ok", _("Afficher Historique"), TYPB_BUTTON);
    $myForm->addFormButton(1, 2, "retour", _("Retour"), TYPB_SUBMIT);
    $myForm->setFormButtonProperties("ok", BUTP_CHECK_FORM, false);
    $myForm->setFormButtonProperties("ok", BUTP_JS_EVENT, array("onclick"=>"showHistory();"));


    $myForm->setFormButtonProperties("retour", BUTP_PROCHAIN_ECRAN, 'Gen-7');
    $myForm->setFormButtonProperties("retour", BUTP_CHECK_FORM, false);

    $JsAffichage = " var tableInfo =document.getElementById('tb_his').parentNode.parentNode.parentNode.parentNode;
                     tableInfo.style.display = 'none';
                   ";

    $JsAffichage .= "function showHistory()
                        {
                           if(tableInfo.style.display != 'none')
                           {
                                document.ADForm.ok.value = 'Afficher l\'historique';
                                tableInfo.style.display = 'none';
                           }
                           else
                           {
                                document.ADForm.ok.value = 'Cacher l\'historique';
                                tableInfo.style.display = 'inline';
                           }
                        }";

    $myForm->addJS(JSP_FORM, "JsAffichage", $JsAffichage);


    $myForm->buildHTML();
    echo $myForm->getHTML();
}

?>