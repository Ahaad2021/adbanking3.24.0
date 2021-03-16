<?php

/**
 * Gestion des logins
 * @package Parametrage
 */

require_once 'lib/dbProcedures/utilisateurs.php';
require_once 'lib/dbProcedures/parametrage.php';
require_once 'lib/misc/divers.php';
require_once 'lib/dbProcedures/historique.php';
require_once 'lib/dbProcedures/bdlib.php';
require_once 'lib/dbProcedures/agency_banking.php';
require_once 'lib/html/HTML_GEN2.php';
require_once 'lib/html/FILL_HTML_GEN2.php';
require_once "lib/html/HTML_menu_gen.php";

if ($global_nom_ecran == "Pag-1") {
  $MyPage = new HTML_GEN2(_("Type de commissions"));


  $MyPage->addField("type_comm",_("Type de commission"),TYPC_LSB);
  $MyPage->setFieldProperties("type_comm", FIELDP_IS_REQUIRED, true);
  $MyPage->setFieldProperties("type_comm", FIELDP_HAS_CHOICE_AUCUN, true);
  $MyPage->setFieldProperties("type_comm",FIELDP_ADD_CHOICES, $adsys['type_comm']);

  //Bouton ajouter
  $MyPage->addFormButton(1,1, "butVal", _("Valider"), TYPB_SUBMIT);
  $MyPage->setFormButtonProperties("butVal", BUTP_CHECK_FORM, false);
  $MyPage->setFormButtonProperties("butVal", BUTP_AXS, 754);
  $MyPage->setFormButtonProperties("butVal", BUTP_PROCHAIN_ECRAN, "Pag-2");

  //Bouton ajouter
  $MyPage->addFormButton(1,2, "butMod", _("Modifier"), TYPB_SUBMIT);
  $MyPage->setFormButtonProperties("butMod", BUTP_CHECK_FORM, false);
  $MyPage->setFormButtonProperties("butMod", BUTP_AXS, 754);
  $MyPage->setFormButtonProperties("butMod", BUTP_PROCHAIN_ECRAN, "Pag-4");

  //Bouton retour
  $MyPage->addFormButton(1,3, "butRet", _("Retour"), TYPB_SUBMIT);
  $MyPage->setFormButtonProperties("butRet", BUTP_CHECK_FORM, false);
  $MyPage->setFormButtonProperties("butRet", BUTP_PROCHAIN_ECRAN, "Gen-16");

  //HTML
  $MyPage->buildHTML();
  echo $MyPage->getHTML();

}
else if ($global_nom_ecran == "Pag-2") {

  $data_comm=getCommission($type_comm);
  $ad_log =  getDatasLogin();
  $plafond_depot = empty($ad_log['plafond_depot'])?0:$ad_log['plafond_depot'];
  $SESSION_VARS['type_comm'] = $type_comm;

  if ($type_comm == 3){
    $data_comm_client = getCommissionNouveauClient();
    if (sizeof($data_comm_client) > 0) {
      $erreur = new HTML_erreur(_("Commission existant"));
      $erreur->setMessage(_("La " . adb_gettext($adsys["type_comm"][$type_comm]) . " existe deja. Veuillez proceder à la modification."));
      $erreur->addButton(BUTTON_OK, "Pag-1");
      $erreur->buildHTML();
      echo $erreur->HTML_code;
      $ok = false;
    } else {
      $MyPage = new HTML_GEN2();
      $MyPage->setTitle(_("Saisie des commissions : " . adb_gettext($adsys['type_comm'][$type_comm])));

      $MyPage->addTableRefField("cpte_inst",_("Compte prelevement commission institution"), "ad_cpt_comptable");
      $MyPage->setFieldProperties("cpte_inst", FIELDP_IS_REQUIRED, true);
      $MyPage->setFieldProperties("cpte_inst", FIELDP_HAS_CHOICE_AUCUN, true);
      $MyPage->setFieldProperties("cpte_inst", FIELDP_IS_LABEL, false);
      if ($global_multidevise)
        $include = getNomsComptesComptables(	array(  "devise"        => NULL,
          "compart_cpte"  => 3    // Charge
        ));
      else
        $include = getNomsComptesComptables(array("compart_cpte"  => 3));   // Comptes Charge

      $MyPage->setFieldProperties("cpte_inst",FIELDP_INCLUDE_CHOICES, array_keys($include));


      $MyPage->addField("mnt_comm_cli", _("Montant commission client"), TYPC_MNT);
      $MyPage->setFieldProperties("mnt_comm_cli", FIELDP_DEFAULT, 0);
      $MyPage->setFieldProperties("mnt_comm_cli", FIELDP_IS_REQUIRED, true);


      $MyPage->addField("req", _("Remarque"), TYPC_ARE);
      $MyPage->setFieldProperties("req", FIELDP_WIDTH, 50);

      //Boutons
      $MyPage->addFormButton(1,1,"butok",_("Valider"), TYPB_SUBMIT);
      $MyPage->setFormButtonProperties("butok", BUTP_PROCHAIN_ECRAN, "Pag-3");
      $MyPage->addFormButton(1,2,"butno",_("Annuler"), TYPB_SUBMIT);
      $MyPage->setFormButtonProperties("butno", BUTP_PROCHAIN_ECRAN, "Mpa-1");
      $MyPage->setFormButtonProperties("butno", BUTP_CHECK_FORM, false);

      $MyPage->buildHTML();
      echo $MyPage->getHTML();
    }



  }
  else if ($type_comm == 4) {
      if(getCommissionInstitution() == NULL){
          $html_err = new HTML_erreur(_("Compte de commission intermédiaire")." ");
          $html_err->setMessage(_("Le paramétrage du compte de commission de l'institution n'as pas été effectué"));
          $html_err->addButton("BUTTON_OK", 'Gen-16');
          $html_err->buildHTML();
          echo $html_err->HTML_code;
      }else {
          $data_cpte_comm_inter = getCpteCommIntermediaire();

          if ((sizeof($data_cpte_comm_inter) > 0) && ($data_cpte_comm_inter['cpte_comm_intermediaire'] != NULL)) {
              $erreur = new HTML_erreur(_("Compte de commission intermediaire"));
              $erreur->setMessage(_("La " . adb_gettext($adsys["type_comm"][$type_comm]) . " existe deja. Veuillez proceder à la modification."));
              $erreur->addButton(BUTTON_OK, "Pag-1");
              $erreur->buildHTML();
              echo $erreur->HTML_code;
              $ok = false;
          } else {
              $MyPage = new HTML_GEN2();
              $MyPage->setTitle(_("Saisie des commissions : " . adb_gettext($adsys['type_comm'][$type_comm])));

              if ($global_multidevise)
                  $include = getNomsComptesComptables(array("devise" => NULL,
                      "compart_cpte" => 2    // Passif
                  ));
              else
                  $include = getNomsComptesComptables(array("compart_cpte" => 2));   // Comptes Passif

              $MyPage->addField("cpte_comm_inter", _("Compte créditeur de commission intermédiaire"), TYPC_LSB);
              $MyPage->setFieldProperties("cpte_comm_inter", FIELDP_ADD_CHOICES, $include);
              $MyPage->setFieldProperties("cpte_comm_inter", FIELDP_HAS_CHOICE_AUCUN, true);
              //Boutons
              $MyPage->addFormButton(1, 1, "butok", _("Valider"), TYPB_SUBMIT);
              $MyPage->setFormButtonProperties("butok", BUTP_PROCHAIN_ECRAN, "Pag-3");
              $MyPage->addFormButton(1, 2, "butno", _("Annuler"), TYPB_SUBMIT);
              $MyPage->setFormButtonProperties("butno", BUTP_PROCHAIN_ECRAN, "Mpa-1");
              $MyPage->setFormButtonProperties("butno", BUTP_CHECK_FORM, false);

              $MyPage->buildHTML();
              echo $MyPage->getHTML();
          }

      }
  }
  else {
    if (sizeof($data_comm) > 0 && $data_comm['counter'] != 0) {
      $erreur = new HTML_erreur(_("Commission existant"));
      $erreur->setMessage(_("La " . adb_gettext($adsys["type_comm"][$type_comm]) . " existe deja. Veuillez proceder à la modification."));
      $erreur->addButton(BUTTON_OK, "Pag-1");
      $erreur->buildHTML();
      echo $erreur->HTML_code;
      $ok = false;
    } else {

      $MyPage = new HTML_GEN2();
      $MyPage->setTitle(_("Saisie des commissions : " . adb_gettext($adsys['type_comm'][$type_comm])));


      $html = "<br>";
      $html .= "<script src=\"/lib/misc/js/lib/jquery.min.js\" type=\"text/javascript\"></script>";
      //$html .= "<script src=\"/lib/misc/js/chosen/chosen.jquery.js\" type=\"text/javascript\"></script>";
      $html .= "<TABLE id =\"commission_set\" align=\"center\" bgcolor=$colb_tableau border=$tableau_border cellspacing=$tableau_cellspacing cellpadding=$tableau_cellpadding>\n";

// En-tête du tableau
      $html .= "<TR bgcolor=$colb_tableau>";
      $html .= "<TH rowspan=\"2\" align=\"center\"><b>" . _("ID PALIER") . "</b></TH>";
      $html .= "<TH colspan=\"2\" align=\"center\"><b>" . _("PALIER") . "</b></TH>";
      $html .= "<TH colspan=\"2\" align=\"center\"><b>" . _("COMMISSION POUR AGENT") . "</b></TH>";
      $html .= "<TH colspan=\"2\" align=\"center\"><b>" . _("COMMISSION POUR L'INSTITUTION") . "</b></TH>";
      $html .= "<TH colspan=\"2\" align=\"center\"><b>" . _("COMISSION TOTAL") . "</b></TH>";
      $html .= "</TR>\n";
      $html .= "<TR bgcolor=$colb_tableau>";
      $html .= "<TD  align=\"center\"><b>" . _("Montant minimum") . "</b></TD>";
      $html .= "<TD  align=\"center\"><b>" . _("Montant maximum") . "</b></TD>";
      $html .= "<TD  align=\"center\"><b>" . _("En pourcentage") . "</b></TD>";
      $html .= "<TD  align=\"center\"><b>" . _("Montant fixe") . "</b></TD>";
      $html .= "<TD  align=\"center\"><b>" . _("En pourcentage") . "</b></TD>";
      $html .= "<TD  align=\"center\"><b>" . _("Montant fixe") . "</b></TD>";
      $html .= "<TD  align=\"center\"><b>" . _("En pourcentage") . "</b></TD>";
      $html .= "<TD  align=\"center\"><b>" . _("Montant fixe") . "</b></TD>";
      $html .= "</TR>\n";

      $SESSION_VARS["nb_ligne"] = 5;
      for ($key = 1; $key <= $SESSION_VARS["nb_ligne"]; $key++) {
        $i = $key;
        // On alterne la couleur de fond
        if ($i % 2)
          $color = $colb_tableau;
        else
          $color = $colb_tableau_altern;

        // une ligne de saisie
        $html .= "<TR bgcolor=$color>\n";

        //numéro de la ligne
        $html .= "<TD align='center'><b>$i</b></TD>";
        $html .= "<TD><INPUT TYPE=\"text\" NAME=\"mnt_mini_palier$key\" size=14 value=''></TD>\n";
        $html .= "<TD><INPUT TYPE=\"text\" NAME=\"mnt_max_palier$key\" size=14 value=''></TD>\n";
        $html .= "<TD><INPUT TYPE=\"text\" NAME=\"mnt_prc_agent$key\" size=14 value='' Onchange=\"changeMontant($key) ;\" ></TD>\n";
        $html .= "<TD><INPUT TYPE=\"text\" NAME=\"mnt_fixe_agent$key\" size=14 value='' Onchange=\"changeMontant($key);\"></TD>\n";
        $html .= "<TD><INPUT TYPE=\"text\" NAME=\"mnt_prc_ins$key\" size=14 value='' Onchange=\"changeMontant($key);\"></TD>\n";
        $html .= "<TD><INPUT TYPE=\"text\" NAME=\"mnt_fixe_ins$key\" size=14 value='' Onchange=\"changeMontant($key);\"></TD>\n";
        $html .= "<TD><INPUT TYPE=\"text\" NAME=\"mnt_prc_tot$key\" size=14 value='' readonly></TD>\n";
        $html .= "<TD><INPUT TYPE=\"text\" NAME=\"mnt_fixe_tot$key\" size=14 value='' readonly></TD>\n";
        $html .= "<TR>\n";

      }

      $js1 = "function disabledEnabled(){
             $(document).ready(function(){
                var list = [
                    {fsel: 'mnt_prc_agent', ssel: 'mnt_fixe_agent'}, 
                    {fsel: 'mnt_fixe_agent', ssel: 'mnt_prc_agent'}, 
                    {fsel: 'mnt_prc_ins', ssel: 'mnt_fixe_ins'}, 
                    {fsel: 'mnt_fixe_ins', ssel: 'mnt_prc_ins'}
                ];
                
                for(var index = 0; index < list.length; index++){
                    isInputEmpty(list[index].fsel, list[index].ssel);
                }
            });
        }
        
        function isInputEmpty(fsel, ssel){
            var list = [];
            $('[name*=mnt_mini_palier]').each(function(index, item){
                list.push(parseInt($(item).attr('name').match(/\d/g).join().replace(',', '')));
            });
            
            $('[name*='+fsel+']').change(function(){;
                $('[name*='+ssel+']').removeAttr('disabled');
                for(var pos = 0; pos < list.length; pos++){
                    if($('[name~='+fsel+list[pos]+']').val() != ''){
                        $('[name*='+ssel+']').attr('disabled', 'disabled');
                        break;
                    }
                }
            });	
        }
        
       var comm_agent = ($('[name=mnt_prc_agent1]').val() == '')?'mnt_prc_agent':'mnt_fixe_agent';
       var comm_ins = ($('[name=mnt_prc_ins1]').val() == '')?'mnt_prc_ins':'mnt_fixe_ins';
       $('[name*=mnt_mini_palier]').attr('readonly', 'readonly');
       
       disabledEnabled();
  ";


      $js = "function changeMontant(i) {
    var prc_tot = eval('document.ADForm.mnt_prc_tot'+i);
    var mnt_tot = eval('document.ADForm.mnt_fixe_tot'+i);

    var prc_comm_agent = eval('document.ADForm.mnt_prc_agent'+i);
    var mnt_comm_agent = eval('document.ADForm.mnt_fixe_agent'+i);
    var prc_comm_inst = eval('document.ADForm.mnt_prc_ins'+i);
    var mnt_comm_inst = eval('document.ADForm.mnt_fixe_ins'+i);

    //prc_comm_agent.value
    
    if ($('[name=mnt_prc_agent'+i+']').val() < 0){
      alert('Le pourcentage de commission pour agent doit être supérieur à 0!');
      prc_comm_agent.value = 0;
    }

    if (mnt_comm_agent.value < 0){
      alert('Le montant de commission pour agent doit être supérieur à 0!');
      mnt_comm_agent.value = 0;
    }

    if (prc_comm_inst.value < 0){
      alert('Le pourcentage de commission pour institution doit être supérieur à 0!');
      prc_comm_inst.value = 0;
    }

    if (mnt_comm_inst.value < 0){
      alert('Le montant de commission pour institution doit être supérieur à 0!');
      mnt_comm_inst.value = 0;
    }


    prc_tot.value = parseInt((prc_comm_agent.value=='')?0:prc_comm_agent.value) + parseInt((prc_comm_inst.value=='')?0:prc_comm_inst.value);
    mnt_tot.value = parseInt((mnt_comm_agent.value=='')?0:mnt_comm_agent.value) + parseInt((mnt_comm_inst.value=='')?0:mnt_comm_inst.value);

  }";


      $html .= "</TABLE>";
      $MyPage->addHTMLExtraCode("html", $html);
      $MyPage->addJS(JSP_FORM, "modif", $js);
      $MyPage->addJS(JSP_FORM, "enable", $js1);

      //Bouton ajouter
      $MyPage->addFormButton(1, 1, "butVal", _("Valider"), TYPB_SUBMIT);
      $MyPage->setFormButtonProperties("butVal", BUTP_CHECK_FORM, false);
      $MyPage->setFormButtonProperties("butVal", BUTP_AXS, 754);
      $MyPage->setFormButtonProperties("butVal", BUTP_PROCHAIN_ECRAN, "Pag-3");

      //Bouton ajout
      $MyPage->addFormButton(1, 2, "butAjout", _("Ajouter"), TYPB_BUTTON);
      $MyPage->setFormButtonProperties("butAjout", BUTP_JS_EVENT, array("onclick" => "addInput();"));

      //Bouton retour
      $MyPage->addFormButton(1, 3, "butRet", _("Retour"), TYPB_SUBMIT);
      $MyPage->setFormButtonProperties("butRet", BUTP_CHECK_FORM, false);
      $MyPage->setFormButtonProperties("butRet", BUTP_PROCHAIN_ECRAN, "Gen-16");
      //Bouton supprimé
//    $MyPage->addFormButton(1, 4, "butSupprimer", _("Supprimer"), TYPB_BUTTON);
//      $MyPage->setFormButtonProperties("butSupprimer", BUTP_JS_EVENT, array("onclick"=>"deleteElement();"));
      $MyPage->addHiddenType("nb_ligne", $SESSION_VARS['nb_ligne']);

      $js2 = "
    
        function changenextMontant(i, n = null){
          var mnt_max = $('[name = mnt_max_palier'+i+']');
          var next_mnt_min = (n == null)?$(mnt_max).parent().parent().next():$('[name = mnt_max_palier'+n+']').parent().parent();
            
          if($('[name*=mnt_max_palier]').length != i){
              while($(next_mnt_min).children().length == 0){
                next_mnt_min = $(next_mnt_min).next();
              }
              next_mnt_min = $($(next_mnt_min).children()[1]).find('input');
          }
          
          if ($(mnt_max).val() > 0 && $(mnt_max).val() != null ){
            $(next_mnt_min).attr('readonly', 'readonly');
            $(next_mnt_min).val(+$(mnt_max).val()+1);
          } else {
            $(next_mnt_min).removeAttr('readonly');
            $(next_mnt_min).val('');
          }
        }
    
     var last_node = $('#commission_set tbody tr:last-child');  //Retreive the last node
     var temp_node = ($(last_node).children().length == 0)?$(last_node).prev():$(last_node);
     var clone_snapshot = $(temp_node).clone()  //holds a snapshot of the clone DOM 
     $(clone_snapshot).find('input').val('');   //Remove existing clone input values
     var ref_id = parseInt($($(clone_snapshot).children()[0]).children().text()); //holds the id of the last cloned DOM
     
     var deletion_status = false;   //Hold the state of input deletion
     var input_disabled_state = false;  //Hold the state of input enable/disable
     
     $('#commission_set input').attr('autocomplete', 'off');    //Disable autocomplete on all inputs
     
     //Set the first min level to 0
     $('#commission_set tbody tr:nth-child(3) td:nth-child(2) input').val(0);
     $('#commission_set tbody tr:nth-child(3) td:nth-child(2) input').attr('readonly', 'readonly');
         
     //DOM injection, add a new commission field     
     function addInput(){
        var plafond_depot = " . $plafond_depot . ";
        var depot_status = isDepotValid(plafond_depot);
        var list = [];
        
        if(depot_status == 1){
            var clone = $(clone_snapshot).clone();  //recreate an identical DOM from clone DOM
            var list_name = ['mnt_mini_palier' , 'mnt_max_palier' , 'mnt_prc_agent' , 'mnt_fixe_agent' , 'mnt_prc_ins' , 'mnt_fixe_ins' , 'mnt_prc_tot' , 'mnt_fixe_tot'];
            ref_id++;
            
            //Remove clone cloned properties
            for(var pos = 1; pos <= $(temp_node).children().length; pos++){
                $($($(clone).children()[pos]).children()).attr('name', list_name[pos - 1]+ ref_id);
                $($($(clone).children()[pos]).children()).removeAttr('onchange');
                $($($(clone).children()[pos]).children()).unbind();
            }
            
            $('#commission_set tbody').append(clone); //Append modified clone to DOM
            
            $('[name*=mnt_mini_palier]').each(function(index, item){
               list.push(parseInt($(item).attr('name').match(/\d/g).join().replace(',', '')));
            });
            
            //Registers clone events
            for(var pos = 2; pos < 6; pos++){
                $('[name = '+list_name[pos]+ref_id+']').change(function(){
                    var id = parseInt($(this).attr('name').match(/\d/g).join().replace(',', ''));
                    changeMontant(id);
                });
            }
            
            
            $('[name=nb_ligne]').val(parseInt($('[name=nb_ligne]').val()) + 1);
            var preceding_elem = $('[name=mnt_max_palier'+(parseInt($('[name*=mnt_max_palier]').last().attr('name').match(/\d/g).join().replace(',', '')) - 1)+']');

            if($(preceding_elem).val() == ''){
                $('[name*=mnt_mini_palier]').last().val('');
            }else{
                var deleted_rows = getDeletionAmount();
                if(deleted_rows == 0){
                    $('[name*=mnt_mini_palier]').last().val(parseInt($('[name=mnt_max_palier'+(ref_id - 1)+']').val().replace(/\s/g, '')) + 1);
                }else{
                    $('[name*=mnt_mini_palier]').last().val(parseInt($('[name=mnt_max_palier'+list[list.indexOf(ref_id) - deleted_rows - 1]+']').val().replace(/\s/g, '')) + 1);
                }
            }
        
            $('[name=mnt_max_palier'+ref_id+']').focus(function(){
                $(this).data('arbitrary_val', $(this).val());
            })
            
            resetRowID();   //reset all field id
            bindPalierConstraint($('[name*=mnt_max_palier]').last());
            disabledEnabled();
        }else if(depot_status == 0){
            alert('Montant égal au plafond de dépôt, les valeurs suivantes ne seront pas prises en compte');
        }else{
            alert('Montant supérieur au plafond de dépôt');
        }
    }
    
    function isDepotValid(depot){
        var flag = 1;  
        var list = [];

        $('[name*=mnt_mini_palier]').each(function(index, item){
            if($(this).data('status') != 'deleted'){
                list.push(parseInt($(item).attr('name').match(/\d/g).join().replace(',', '')));
            }
        });
        
        for(var index = 0; index < list.length; index++){
            var val = parseInt($('[name=mnt_max_palier'+list[index]+']').val().replace(/\s/g, ''));
            console.log(val);
            if(val != '' && val != undefined){
                if(val > depot){
                    flag = -1;
                    break;
                }else if(val == depot){
                    flag = 0;
                    break;
                }
            }
        }
        
        return 1;
    }
    
    function disableInputSets(start){
        input_disabled_state = true;
        var list = [];

        $('[name*=mnt_mini_palier]').each(function(index, item){
            list.push(parseInt($(item).attr('name').match(/\d/g).join().replace(',', '')));
        });
        
        for(var index = list.indexOf(start) + 1; index < list.length; index++){
            var parent = $('[name=mnt_max_palier'+list[index]+']').parent().parent();
            $(parent).find('input').attr('readonly', 'readonly');
        }
    }
    
    function enableInputSets(start){
        input_disabled_state = false;
        var list = [];

        $('[name*=mnt_mini_palier]').each(function(index, item){
            list.push(parseInt($(item).attr('name').match(/\d/g).join().replace(',', '')));
        });
        
        for(var index =  index = list.indexOf(start) + 1; index < list.length; index++){
            var parent = $('[name=mnt_max_palier'+list[index]+']').parent().parent();
            $(parent).find('input').removeAttr('readonly');
        }
        $('[name*=mnt_mini_palier]').last().attr('readonly', 'readonly');
    }
    
    function triggerInputDeletion(){
        deletion_status = (deletion_status)?false:true;
        $('[name=butSupprimer]').val((deletion_status)?'Annuler':'Supprimer');
        
        if(deletion_status){
            $('#commission_set input').bind({
                mouseenter: function(){
                    $(this).parent().parent().css('border-color', 'red');
                    $(this).parent().parent().bind('click', function(){
                       var val = confirm('Confirmer la suppression de ce champ');
                       if(val){
                            deleteElement($(this));
                            resetRowID();
                       }
                    });
                },
                mouseleave: function(){
                    $(this).parent().parent().css('border-color', 'grey');
                    $(this).parent().parent().unbind('click');
                }
            });
        }else{
            $('#commission_set input').unbind('mouseenter');
            $('#commission_set input').unbind('mouseleave');
        }
    }
    
    function deleteElement(){
        var list = [];
        var pos;
        
        $('[name*=mnt_mini_palier]').each(function(index, item){
            list.push(parseInt($(item).attr('name').match(/\d/g).join().replace(',', '')));
        });
        
        pos = list.length - 1;
        while(($('[name=mnt_mini_palier'+list[pos]+']').val() == -1) && (pos >= 0)){
            pos--;
        }

        if(pos < 0){
            alert('Aucun palier a supprimer');
        }else{
            var val = confirm('Confirmer la suppression de ce champ');
            var deletion_amount = getDeletionAmount();
            if(val){
                $($('[name=mnt_mini_palier'+list[pos]+']').parent().parent()).css('display', 'none');
                $($('[name=mnt_mini_palier'+list[pos]+']').parent().parent()).find('[name*=mnt_mini_palier]').val(-1);
                $('[name=mnt_mini_palier'+list[pos]+']').data('status', 'deleted');
                $('[name=mnt_max_palier'+list[list.length - deletion_amount - 1]+']').unbind('change');
                bindPalierConstraint('[name=mnt_max_palier'+list[list.length - deletion_amount - 1]+']');
            }
        }
    }
    
    function resetRowID(){
        var temp_list = $('[name*=mnt_mini_palier]').parent().prev().find('b');
        for(var index = 0; index < temp_list.length; index++){
            if($(temp_list[index]).parent().parent().css('display') == 'none'){
                index--;
                temp_list.splice(index, 1);
            }
            else{
                $(temp_list[index]).text(index + 1);
            }
        }
    }
    
    function bindPalierConstraint(selector){
        $(selector).bind('change', function(){
            var id = parseInt($(this).attr('name').match(/\d/g).join().replace(',', ''));
            var mnt_min = parseInt($('[name=mnt_mini_palier'+id+']').val().replace(/\s/g, ''));
            var plafond_depot = " . $plafond_depot . ";
            var depot_status = isDepotValid(plafond_depot);
            var list = [];
            var current_val;
            var next_mnt_max;
            var next_elem_id;
            var is_next_deleted;
            var reset_state = false;
            
            $('[name*=mnt_mini_palier]').each(function(index, item){
                list.push(parseInt($(item).attr('name').match(/\d/g).join().replace(',', '')));
            });
            
            is_next_deleted = isDeleted('[name=mnt_mini_palier'+list[list.indexOf(id) + 1]+']');
            if(is_next_deleted){
                next_elem_id = list[list.indexOf(id) + getDeletionAmount() + 1];
            }else{
                next_elem_id = list[list.indexOf(id) + 1];
            }
            
            if($('[name=mnt_max_palier'+next_elem_id+']').length != 0){
                if($('[name=mnt_max_palier'+next_elem_id+']').val() == ''){
                    next_mnt_max = parseInt($(this).val()) + 1;
                    current_val = '';
                    $('[name=mnt_mini_palier'+next_elem_id+']').val('');
                }else{
                    current_val = parseInt($(this).data('arbitrary_val').replace(/\s/g, ''));
                    next_mnt_max = parseInt($('[name=mnt_max_palier'+next_elem_id+']').val().replace(/\s/g, '')) - 1;
                }
                if(parseInt($(this).val()) < next_mnt_max){
                    if(parseInt($(this).val()) <= mnt_min){
                        alert('Le montant maximum doit être supérieure au montant minimum');
                        $(this).val(current_val);
                    }else{
                        if(is_next_deleted){
                            changenextMontant(id, list[list.length - 1]);
                        }else{
                            changenextMontant(id);
                        }
                        $('[name=mnt_mini_palier'+id+']').attr('readonly', 'readonly');
                    }
                }else{
                    alert('Le montant maximum doit être inférieur au prochain montant maximum');
                    $(this).val(current_val);
                    reset_state = true;
                }
            }else if(parseInt($(this).val()) <= mnt_min){
                current_val = ($(this).data('arbitrary_val') == '')?'':parseInt($(this).data('arbitrary_val').replace(/\s/g, ''));
                alert('Le montant maximum doit être supérieure au montant minimum');
                $(this).val(current_val);
            }else{
                reset_state = false;
            }
            
            if(!reset_state){
                if(depot_status == 1){
                    if(input_disabled_state) enableInputSets(id);
                }else if(depot_status == 0){
                    alert('Montant égal au plafond de dépôt, les valeurs suivantes ne seront pas prises en compte');
                    disableInputSets(id);
                }else{
                    alert('Montant supérieur au plafond de dépôt');
                    $(this).val(plafond_depot);
                    disableInputSets(id);
                }
            }    
        });
    }
    
    function getDeletionAmount(){
        var count = 0;
        $('[name*=mnt_mini_palier]').each(function(index, item){
            if($(item).data('status') == 'deleted'){
                count++;
            }
        });
        return count;
    }
    
    function isDeleted(sel){
        return ($(sel).data('status') == 'deleted')?true:false;
    }
    $('[name*=mnt_max_palier]').focus(function(){
        $(this).data('arbitrary_val', $(this).val());
    });
    
    bindPalierConstraint('[name*=mnt_max_palier]');
    resetRowID();
    ";

      $MyPage->addJS(JSP_FORM, "JS2", $js2);
      $MyPage->buildHTML();

      echo $MyPage->getHTML();
    }
  }
}

else if ($global_nom_ecran == "Pag-3") {
  global $global_id_agence,$global_nom_login;
  if ($global_nom_ecran_prec == "Pag-2") {
    if ($SESSION_VARS['type_comm']==3){
      $mnt_comm = recupMontant($mnt_comm_cli);
      $data_comm_cli = array(
        "cpte_comm_inst" => $cpte_inst,
        "montant_comm" => $mnt_comm,
        "remarque" => $req,
        "date_creation" => date('d-m-y'),
        "login" => $global_nom_login,
        "id_ag" =>$global_id_agence
      );


      $db = $dbHandler->openConnection();
      $result = executeQuery($db, buildInsertQuery("ag_commission_client", $data_comm_cli));
      if (DB::isError($result)) {
        $dbHandler->closeConnection(false);
        signalErreur(__FILE__, __LINE__, __FUNCTION__, $result->getMessage());
      }else {
        updateOperationCompta(627, 'd', $data_comm_cli['cpte_comm_inst']);

        $dbHandler->closeConnection(true);
        $html_msg = new HTML_message("Commission sur creation nouveau client");

        $html_msg->setMessage(sprintf(" <br /> Vos informations ont été enregistré.<br /> "));

        $html_msg->addButton("BUTTON_OK", 'Mpa-1');

        $html_msg->buildHTML();
        echo $html_msg->HTML_code;
      }

    }
    else if ($SESSION_VARS['type_comm']==4){
      $data_cpte_inter= array(
        "cpte_comm_intermediaire" => $cpte_comm_inter
      );


      updateOperationCompta(628, 'c', $data_cpte_inter['cpte_comm_intermediaire']);
      updateOperationCompta(632, 'c', $data_cpte_inter['cpte_comm_intermediaire']);
      updateOperationCompta(633, 'd', $data_cpte_inter['cpte_comm_intermediaire']);

      $db = $dbHandler->openConnection();
      $result = executeQuery($db, buildUpdateQuery("ag_param_commission_institution", $data_cpte_inter));
      if (DB::isError($result)) {
        $dbHandler->closeConnection(false);
        signalErreur(__FILE__, __LINE__, __FUNCTION__, $result->getMessage());
      }else {
        $dbHandler->closeConnection(true);
        $html_msg = new HTML_message("Information ");

        $html_msg->setMessage(sprintf(" <br /> Vos informations ont été enregistré.<br /> "));

        $html_msg->addButton("BUTTON_OK", 'Mpa-1');

        $html_msg->buildHTML();
        echo $html_msg->HTML_code;
      }
    }
    else {

      $SESSION_VARS['nb_ligne'] = $_POST['nb_ligne'];
      //recuperation des donnees
      $DATA = array();
      for ($key = 1; $key <= $SESSION_VARS["nb_ligne"]; $key++) {
        $i = $key;
        $DATA[$i]["type_comm"] = $SESSION_VARS['type_comm'];
        $DATA[$i]["id_palier"] = $key;
        $DATA[$i]["mnt_min"] = ${"mnt_mini_palier" . $key};
        $DATA[$i]["mnt_max"] = ${"mnt_max_palier" . $key};
        $DATA[$i]["comm_agent_prc"] = ${"mnt_prc_agent" . $key};
        $DATA[$i]["comm_agent_mnt"] = ${"mnt_fixe_agent" . $key};
        $DATA[$i]["comm_inst_prc"] = ${"mnt_prc_ins" . $key};
        $DATA[$i]["comm_inst_mnt"] = ${"mnt_fixe_ins" . $key};
        $DATA[$i]["comm_tot_prc"] = ${"mnt_prc_tot" . $key};
        $DATA[$i]["comm_tot_mnt"] = ${"mnt_fixe_tot" . $key};
      }

      $insertCommission = ajoutCommission($DATA, $SESSION_VARS["nb_ligne"]);

      if ($insertCommission->errCode == NO_ERR) {

        $html_msg = new HTML_message("Confirmation d'enregistrement des commissions");

        $html_msg->setMessage(sprintf(" <br /> Les données des commissions ont été enregistré! <br /> "));

        $html_msg->addButton("BUTTON_OK", 'Pag-1');

        $html_msg->buildHTML();
        echo $html_msg->HTML_code;
      }
    }
  }

  else if ($global_nom_ecran_prec == "Pag-4") {

    if ($SESSION_VARS['type_comm'] == 3) {
      $mnt_comm = recupMontant($mnt_comm_cli);
      $data_comm_cli = array(
        "cpte_comm_inst" => $cpte_inst,
        "montant_comm" => $mnt_comm,
        "remarque" => $req,
        "date_modif" => date('d-m-y'),
        "login" => $global_nom_login,
        "id_ag" => $global_id_agence
      );


      updateOperationCompta(627, 'd', $data_comm_cli['cpte_comm_inst']);

      $db = $dbHandler->openConnection();
      $result = executeQuery($db, buildUpdateQuery("ag_commission_client", $data_comm_cli));
      if (DB::isError($result)) {
        $dbHandler->closeConnection(false);
        signalErreur(__FILE__, __LINE__, __FUNCTION__, $result->getMessage());
      } else {
        $dbHandler->closeConnection(true);
        $html_msg = new HTML_message("Commission sur creation nouveau client");

        $html_msg->setMessage(sprintf(" <br /> Vos informations ont été modifié.<br /> "));

        $html_msg->addButton("BUTTON_OK", 'Mpa-1');

        $html_msg->buildHTML();
        echo $html_msg->HTML_code;
      }

    }
    else if ($SESSION_VARS['type_comm'] == 4) {
      $data_compte_inter = array(
        "cpte_comm_intermediaire" => $cpte_comm_inter
      );

      updateOperationCompta(628, 'c', $data_compte_inter['cpte_comm_intermediaire']);
      updateOperationCompta(632, 'c', $data_compte_inter['cpte_comm_intermediaire']);
      updateOperationCompta(633, 'd', $data_compte_inter['cpte_comm_intermediaire']);

      $db = $dbHandler->openConnection();
      $result = executeQuery($db, buildUpdateQuery("ag_param_commission_institution", $data_compte_inter));
      if (DB::isError($result)) {
        $dbHandler->closeConnection(false);
        signalErreur(__FILE__, __LINE__, __FUNCTION__, $result->getMessage());
      } else {
        $dbHandler->closeConnection(true);
        $html_msg = new HTML_message("Compte de commission intermediaire");

        $html_msg->setMessage(sprintf(" <br /> Vos informations ont été modifié.<br /> "));

        $html_msg->addButton("BUTTON_OK", 'Mpa-1');

        $html_msg->buildHTML();
        echo $html_msg->HTML_code;
      }
    }

    else {
      $data_commission = getCommission($SESSION_VARS['type_comm']);
      $SESSION_VARS['nb_ligne'] = $_POST['nb_ligne'];
      $id_set = getNumbers(constructArray($_POST, 'mnt_mini_palier'));

      $DATA = array();
      for ($key = 1; $key <= $SESSION_VARS["nb_ligne"]; $key++) {
        $array_exist = $data_commission;
        $i = $id_set[$key - 1];
        unset($array_exist[$i]['id'], $array_exist[$i]['type_comm'], $array_exist[$i]['date_creation'], $array_exist[$i]['id_ag']);
        $DATA['id_palier'] = $i;
        $DATA['mnt_min'] = number_format(recupMontant(${"mnt_mini_palier" . $i}), 0, '.', '') . ".000000";
        $DATA['mnt_max'] = number_format(recupMontant(${"mnt_max_palier" . $i}), 0, '.', '') . ".000000";
        $DATA['comm_agent_prc'] = ${"mnt_prc_agent" . $i};
        if (${"mnt_fixe_agent" . $i} != '') {
          $DATA['comm_agent_mnt'] = number_format(recupMontant(${"mnt_fixe_agent" . $i}), 0, '.', '') . ".000000";
        }
        $DATA['comm_inst_prc'] = ${"mnt_prc_ins" . $i};
        if (${"mnt_fixe_ins" . $i} != '') {
          $DATA['comm_inst_mnt'] = number_format(recupMontant(${"mnt_fixe_ins" . $i}), 0, '.', '') . ".000000";
        }
        $DATA['comm_tot_prc'] = ${"mnt_prc_tot" . $i};
        if (${"mnt_fixe_tot" . $i} != '') {
          $DATA['comm_tot_mnt'] = number_format(recupMontant(${"mnt_fixe_tot" . $i}), 0, '.', '') . ".000000";
        }

        $DATA['type_transaction'] = intval(${"type_transaction_" . $i});

        if (sizeof($array_exist[$i] > 0)) {
          $type_trans = intval(${"type_transaction_" . $i});
          $delete_status = (($type_trans == 3) || ($type_trans == 2))?true:false;
          $insert_status = (($type_trans == 1) || ($type_trans == 2))?true:false;
          $temp_arr = $array_exist[$i];
          if (empty($array_exist[$i])) {
            $temp_arr = array();
            $delete_status = false;
          }

          $compare = array_diff($DATA, $temp_arr);

          if (sizeof($compare) > 0) {
            $DATA['type_comm'] = $SESSION_VARS['type_comm'];
            $DATA['date_creation'] = date('d-m-y');
            $DATA['id_ag'] = $global_id_agence;
            $data_node = $DATA;

            if(intval(${"type_transaction_" . $i}) != 4) {
                $db = $dbHandler->openConnection();
                if(intval(${"type_transaction_" . $i}) == 2){
                    $temp_data['id_palier'] = $data_commission[$i]['id_palier'];
                    $temp_data['mnt_min'] = $data_commission[$i]['mnt_min'];
                    $temp_data['mnt_max'] = $data_commission[$i]['mnt_max'];
                    $temp_data['comm_agent_prc'] = $data_commission[$i]['comm_agent_prc'];
                    $temp_data['comm_agent_mnt'] = $data_commission[$i]['comm_agent_mnt'];
                    $temp_data['comm_inst_prc'] = $data_commission[$i]['comm_inst_prc'];
                    $temp_data['comm_inst_mnt'] = $data_commission[$i]['comm_inst_mnt'];
                    $temp_data['comm_tot_prc']  = $data_commission[$i]['comm_tot_prc'];
                    $temp_data['comm_tot_mnt'] = $data_commission[$i]['comm_tot_mnt'];
                    $temp_data['type_transaction'] = 2;
                    $temp_data['type_comm'] = $DATA['type_comm'];
            		$temp_data['date_creation'] = $DATA['date_creation'];
            		$temp_data['id_ag'] = $DATA['id_ag'];
                    $data_node = $temp_data;
                }
                $result = executeQuery($db, buildInsertQuery("ag_commission_hist", $data_node));
                if (DB::isError($result)) {
                    $dbHandler->closeConnection(false);
                    signalErreur(__FILE__, __LINE__, __FUNCTION__, $result->getMessage());
                }
            }
            unset($data_node['type_transaction']);
            unset($DATA['type_transaction']);

            $ajout_new_data = modif_ligne_commission($data_commission[$i]['id'], $DATA, $delete_status, $insert_status);
            if(intval(${"type_transaction_" . $i}) != 4){
                $comm_version = generateCommissionVersion($DATA['type_comm'], intval(${"type_transaction_" . $i}));
            }
            if ($ajout_new_data->errCode != NO_ERR) {
              $dbHandler->closeConnection(false);
              signalErreur(__FILE__, __LINE__, __FUNCTION__, $result2->getMessage());
            }
            $dbHandler->closeConnection(true);
            unset($DATA['type_comm'], $DATA['date_creation'], $DATA['id_ag']);
          }
        } else if (!array_key_exists($i, $array_exist[$i])) {
            unset($DATA['type_transaction']);
          $DATA['type_comm'] = $SESSION_VARS['type_comm'];
          $DATA['date_creation'] = date('d-m-y');
          $DATA['id_ag'] = $global_id_agence;

          $db1 = $dbHandler->openConnection();
          $result1 = executeQuery($db1, buildInsertQuery("ag_commission", $DATA));
          if (DB::isError($result1)) {
            $dbHandler->closeConnection(false);
            signalErreur(__FILE__, __LINE__, __FUNCTION__, $result1->getMessage());
          }
          exit();
          $dbHandler->closeConnection(true);
        }
      }

      $html_msg = new HTML_message("Confirmation de modification des commissions");

      $html_msg->setMessage(sprintf(" <br /> Les données des commissions ont été modifié! <br /> "));

      $html_msg->addButton("BUTTON_OK", 'Pag-1');

      $html_msg->buildHTML();
      echo $html_msg->HTML_code;

    }
  }
}

else if ($global_nom_ecran == "Pag-4") {
  $MyPage = new HTML_GEN2();
  $MyPage->setTitle(_("Modification des commissions : ".adb_gettext($adsys['type_comm'][$type_comm])));
  $SESSION_VARS['type_comm'] = $type_comm;
  $ad_log =  getDatasLogin();
  $plafond_depot = empty($ad_log['plafond_depot'])?0:$ad_log['plafond_depot'];
  $data_comm=getCommission($type_comm);

  if ($type_comm == 3){
    $data_comm_client = getCommissionNouveauClient();
    if (sizeof($data_comm_client) == 0) {
      $erreur = new HTML_erreur(_("Commission inexistant"));
      $erreur->setMessage(_("La " . adb_gettext($adsys["type_comm"][$type_comm]) . " n'existe pas. Veuillez proceder à la saisie."));
      $erreur->addButton(BUTTON_OK, "Pag-1");
      $erreur->buildHTML();
      echo $erreur->HTML_code;
      $ok = false;
      die();
    }else{
      $MyPage->addTableRefField("cpte_inst",_("Compte prelevement commission institution"), "ad_cpt_comptable");
      $MyPage->setFieldProperties("cpte_inst", FIELDP_IS_REQUIRED, true);
      $MyPage->setFieldProperties("cpte_inst", FIELDP_HAS_CHOICE_AUCUN, true);
      $MyPage->setFieldProperties("cpte_inst", FIELDP_IS_LABEL, false);
      if ($global_multidevise)
        $include = getNomsComptesComptables(	array(  "devise"        => NULL,
          "compart_cpte"  => 3    // Charge
        ));
      else
        $include = getNomsComptesComptables(array("compart_cpte"  => 3));   // Comptes Charge

      $MyPage->setFieldProperties("cpte_inst",FIELDP_INCLUDE_CHOICES, array_keys($include));
      $MyPage->setFieldProperties("cpte_inst",FIELDP_DEFAULT, $data_comm_client['cpte_comm_inst']);


      $MyPage->addField("mnt_comm_cli", _("Montant commission client"), TYPC_MNT);
      $MyPage->setFieldProperties("mnt_comm_cli", FIELDP_DEFAULT, $data_comm_client['montant_comm']);
      $MyPage->setFieldProperties("mnt_comm_cli", FIELDP_IS_REQUIRED, true);


      $MyPage->addField("req", _("Remarque"), TYPC_ARE);
      $MyPage->setFieldProperties("req", FIELDP_WIDTH, 50);

      //Boutons
      $MyPage->addFormButton(1,1,"butok",_("Valider"), TYPB_SUBMIT);
      $MyPage->setFormButtonProperties("butok", BUTP_PROCHAIN_ECRAN, "Pag-3");
      $MyPage->addFormButton(1,2,"butno",_("Annuler"), TYPB_SUBMIT);
      $MyPage->setFormButtonProperties("butno", BUTP_PROCHAIN_ECRAN, "Mpa-1");
      $MyPage->setFormButtonProperties("butno", BUTP_CHECK_FORM, false);
    }

  }
  else if ($type_comm == 4){
      $data_cpte_comm_inter = getCpteCommIntermediaire();
      if ((sizeof($data_cpte_comm_inter) > 0) && ($data_cpte_comm_inter['cpte_comm_intermediaire'] == NULL) ) {
        $erreur = new HTML_erreur(_("Information sur l'impôt inexistante"));
        $erreur->setMessage(_("La " . adb_gettext($adsys["type_comm"][$type_comm]) . " existe deja. Veuillez proceder à la saisie."));
        $erreur->addButton(BUTTON_OK, "Pag-1");
        $erreur->buildHTML();
        echo $erreur->HTML_code;
        $ok = false;
      } else {
        $MyPage = new HTML_GEN2();
        $MyPage->setTitle(_("Saisie des commissions : " . adb_gettext($adsys['type_comm'][$type_comm])));

        if ($global_multidevise)
          $include = getNomsComptesComptables(array("devise" => NULL,
            "compart_cpte" => 2    // Passif
          ));
        else
          $include = getNomsComptesComptables(array("compart_cpte" => 2));   // Comptes Passif

        $MyPage->addField("cpte_comm_inter",_("Compte créditeur de commission intermédiaire"), TYPC_LSB,$data_cpte_comm_inter['cpte_comm_intermediaire']);
        $MyPage->setFieldProperties("cpte_comm_inter", FIELDP_ADD_CHOICES, $include);
        $MyPage->setFieldProperties("cpte_comm_inter", FIELDP_HAS_CHOICE_AUCUN, true);
        //Boutons
        $MyPage->addFormButton(1, 1, "butok", _("Valider"), TYPB_SUBMIT);
        $MyPage->setFormButtonProperties("butok", BUTP_PROCHAIN_ECRAN, "Pag-3");
        $MyPage->addFormButton(1, 2, "butno", _("Annuler"), TYPB_SUBMIT);
        $MyPage->setFormButtonProperties("butno", BUTP_PROCHAIN_ECRAN, "Mpa-1");
        $MyPage->setFormButtonProperties("butno", BUTP_CHECK_FORM, false);
        
      }
  }

  else {
    if ($data_comm['counter'] == 0) {
      $erreur = new HTML_erreur(_("Commission inexistant"));
      $erreur->setMessage(_("La " . adb_gettext($adsys["type_comm"][$type_comm]) . " n'existe pas. Veuillez proceder à la saisie."));
      $erreur->addButton(BUTTON_OK, "Pag-1");
      $erreur->buildHTML();
      echo $erreur->HTML_code;
      $ok = false;
      die();
    }

    $html = "<br>";
    $html .= "<script src=\"/lib/misc/js/lib/jquery.min.js\" type=\"text/javascript\"></script>";
    //$html .= "<script src=\"/lib/misc/js/chosen/chosen.jquery.js\" type=\"text/javascript\"></script>";
    $html .= "<TABLE id =\"commission_set\" align=\"center\" bgcolor=$colb_tableau border=$tableau_border cellspacing=$tableau_cellspacing cellpadding=$tableau_cellpadding>\n";

// En-tête du tableau
    $html .= "<TR bgcolor=$colb_tableau>";
    $html .= "<TH rowspan=\"2\" align=\"center\"><b>" . _("ID PALIER") . "</b></TH>";
    $html .= "<TH colspan=\"2\" align=\"center\"><b>" . _("PALIER") . "</b></TH>";
    $html .= "<TH colspan=\"2\" align=\"center\"><b>" . _("COMMISSION POUR AGENT") . "</b></TH>";
    $html .= "<TH colspan=\"2\" align=\"center\"><b>" . _("COMMISSION POUR L'INSTITUTION") . "</b></TH>";
    $html .= "<TH colspan=\"2\" align=\"center\"><b>" . _("COMISSION TOTAL") . "</b></TH>";
    $html .= "</TR>\n";
    $html .= "<TR bgcolor=$colb_tableau>";
    $html .= "<TD  align=\"center\"><b>" . _("Montant minimum") . "</b></TD>";
    $html .= "<TD  align=\"center\"><b>" . _("Montant maximum") . "</b></TD>";
    $html .= "<TD  align=\"center\"><b>" . _("En pourcentage") . "</b></TD>";
    $html .= "<TD  align=\"center\"><b>" . _("Montant fixe") . "</b></TD>";
    $html .= "<TD  align=\"center\"><b>" . _("En pourcentage") . "</b></TD>";
    $html .= "<TD  align=\"center\"><b>" . _("Montant fixe") . "</b></TD>";
    $html .= "<TD  align=\"center\"><b>" . _("En pourcentage") . "</b></TD>";
    $html .= "<TD  align=\"center\"><b>" . _("Montant fixe") . "</b></TD>";
    $html .= "</TR>\n";


    //recuperation des donnees existantes
    $data_commission = getCommission($type_comm);
    $SESSION_VARS["nb_ligne"] = $data_commission['counter'];
    $keys = array_keys($data_commission);
    array_pop($keys);

    for ($key = 0; $key < count($keys); $key++) {
      $i = $keys[$key];
      // On alterne la couleur de fond
      if ($i % 2)
        $color = $colb_tableau;
      else
        $color = $colb_tableau_altern;

      // une ligne de saisie
      $html .= "<TR bgcolor=$color>\n";

      //numéro de la ligne
      $html .= "<TD align='center'><b>$i</b></TD>";
      $html .= "<TD><INPUT TYPE=\"text\" NAME=\"mnt_mini_palier$i\" size=14 value='" . afficheMontant($data_commission[$i]['mnt_min']) . "'></TD>\n";
      $html .= "<TD><INPUT TYPE=\"text\" NAME=\"mnt_max_palier$i\" size=14 value='" . afficheMontant($data_commission[$i]['mnt_max']) . "'></TD>\n";
      $html .= "<TD><INPUT TYPE=\"text\" NAME=\"mnt_prc_agent$i\" size=14 value='" . $data_commission[$i]['comm_agent_prc'] . "' Onchange=\"changeMontant($i) ;\" ></TD>\n";
      $html .= "<TD><INPUT TYPE=\"text\" NAME=\"mnt_fixe_agent$i\" size=14 value='" . afficheMontant($data_commission[$i]['comm_agent_mnt']) . "' Onchange=\"changeMontant($i);\"></TD>\n";
      $html .= "<TD><INPUT TYPE=\"text\" NAME=\"mnt_prc_ins$i\" size=14 value='" . $data_commission[$i]['comm_inst_prc'] . "' Onchange=\"changeMontant($i);\"></TD>\n";
      $html .= "<TD><INPUT TYPE=\"text\" NAME=\"mnt_fixe_ins$i\" size=14 value='" . afficheMontant($data_commission[$i]['comm_inst_mnt']) . "' Onchange=\"changeMontant($i);\"></TD>\n";
      $html .= "<TD><INPUT TYPE=\"text\" NAME=\"mnt_prc_tot$i\" size=14 value='" . $data_commission[$i]['comm_tot_prc'] . "' readonly></TD>\n";
      $html .= "<TD><INPUT TYPE=\"text\" NAME=\"mnt_fixe_tot$i\" size=14 value='" . afficheMontant($data_commission[$i]['comm_tot_mnt']) . "' readonly></TD>\n";
      $html .= "<TD><INPUT TYPE=\"hidden\" NAME=\"type_transaction_$i\" value='4'></TD>\n";
      $html .= "<TR>\n";

    }


    $js1 = "function disabledEnabled(){
             $(document).ready(function(){
                var list = [
                    {fsel: 'mnt_prc_agent', ssel: 'mnt_fixe_agent'}, 
                    {fsel: 'mnt_fixe_agent', ssel: 'mnt_prc_agent'}, 
                    {fsel: 'mnt_prc_ins', ssel: 'mnt_fixe_ins'}, 
                    {fsel: 'mnt_fixe_ins', ssel: 'mnt_prc_ins'}
                ];
                
                for(var index = 0; index < list.length; index++){
                    isInputEmpty(list[index].fsel, list[index].ssel);
                }
            });
        }
        
        function isInputEmpty(fsel, ssel){
            var list = [];
            $('[name*=mnt_mini_palier]').each(function(index, item){
                list.push(parseInt($(item).attr('name').match(/\d/g).join().replace(',', '')));
            });
            
            $('[name*='+fsel+']').change(function(){;
                $('[name*='+ssel+']').removeAttr('disabled');
                for(var pos = 0; pos < list.length; pos++){
                    if($('[name~='+fsel+list[pos]+']').val() != ''){
                        $('[name*='+ssel+']').attr('disabled', 'disabled');
                        break;
                    }
                }
            });	
        }
       
       var first_set_id = $('#commission_set tbody tr:nth-child(3)').find('[name*=mnt]').attr('name').match(/\d/g).join().replace(',', '');
       var comm_agent = ($('[name=mnt_prc_agent'+first_set_id+']').val() == '')?'mnt_prc_agent':'mnt_fixe_agent';
       var comm_ins = ($('[name=mnt_prc_ins'+first_set_id+']').val() == '')?'mnt_prc_ins':'mnt_fixe_ins';
       $('[name*=mnt_mini_palier]').attr('readonly', 'readonly');
       $('[name*='+comm_agent+']').attr('disabled', 'disabled');
       $('[name*='+comm_ins+']').attr('disabled', 'disabled');
       disabledEnabled();
  ";


    $js = "function changeMontant(i) {

    var prc_tot = eval('document.ADForm.mnt_prc_tot'+i);
    var mnt_tot = eval('document.ADForm.mnt_fixe_tot'+i);

    var prc_comm_agent = eval('document.ADForm.mnt_prc_agent'+i);
    var mnt_comm_agent = eval('document.ADForm.mnt_fixe_agent'+i);
    var prc_comm_inst = eval('document.ADForm.mnt_prc_ins'+i);
    var mnt_comm_inst = eval('document.ADForm.mnt_fixe_ins'+i);
    
    if (prc_comm_agent.value < 0){
      alert('Le pourcentage de commission pour agent doit être supérieur à 0!');
      prc_comm_agent.value = 0;
    }

    if (mnt_comm_agent.value < 0){
      alert('Le montant de commission pour agent doit être supérieur à 0!');
      mnt_comm_agent.value = 0;
    }

    if (prc_comm_inst.value < 0){
      alert('Le pourcentage de commission pour institution doit être supérieur à 0!');
      prc_comm_inst.value = 0;
    }

    if (mnt_comm_inst.value < 0){
      alert('Le montant de commission pour institution doit être supérieur à 0!');
      mnt_comm_inst.value = 0;
    }


    prc_tot.value = parseInt((prc_comm_agent.value=='')?0:prc_comm_agent.value) + parseInt((prc_comm_inst.value=='')?0:prc_comm_inst.value);
    mnt_tot.value = parseInt((mnt_comm_agent.value=='')?0:mnt_comm_agent.value) + parseInt((mnt_comm_inst.value=='')?0:mnt_comm_inst.value);

  }";


    $html .= "</TABLE>";
    $MyPage->addHTMLExtraCode("html", $html);
    $MyPage->addJS(JSP_FORM, "modif", $js);
    $MyPage->addJS(JSP_FORM, "enable", $js1);
    //Bouton ajouter
    $MyPage->addFormButton(1, 1, "butVal", _("Valider"), TYPB_SUBMIT);
    $MyPage->setFormButtonProperties("butVal", BUTP_CHECK_FORM, false);
    $MyPage->setFormButtonProperties("butVal", BUTP_AXS, 754);
    $MyPage->setFormButtonProperties("butVal", BUTP_PROCHAIN_ECRAN, "Pag-3");

    //Bouton ajout
    $MyPage->addFormButton(1, 2, "butAjout", _("Ajouter"), TYPB_BUTTON);
    $MyPage->setFormButtonProperties("butAjout", BUTP_JS_EVENT, array("onclick" => "addInput();"));

    //Bouton supprimé
    $MyPage->addFormButton(1, 3, "butSupprimer", _("Supprimer"), TYPB_BUTTON);
    $MyPage->setFormButtonProperties("butSupprimer", BUTP_JS_EVENT, array("onclick" => "deleteElement();"));
    $MyPage->addHiddenType("nb_ligne", $SESSION_VARS['nb_ligne']);

    //Bouton retour
    $MyPage->addFormButton(1, 4, "butRet", _("Retour"), TYPB_SUBMIT);
    $MyPage->setFormButtonProperties("butRet", BUTP_CHECK_FORM, false);
    $MyPage->setFormButtonProperties("butRet", BUTP_PROCHAIN_ECRAN, "Pag-1");

    $js2 = "
        function changenextMontant(i, n = null){
          var mnt_max = $('[name = mnt_max_palier'+i+']');
          var next_mnt_min = (n == null)?$(mnt_max).parent().parent().next():$('[name = mnt_max_palier'+n+']').parent().parent();
            
          if($('[name*=mnt_max_palier]').length != i){
              while($(next_mnt_min).children().length == 0){
                next_mnt_min = $(next_mnt_min).next();
              }
              next_mnt_min = $($(next_mnt_min).children()[1]).find('input');
          }
          
          if ($(mnt_max).val() > 0 && $(mnt_max).val() != null ){
            $(next_mnt_min).attr('readonly', 'readonly');
            $(next_mnt_min).val(+$(mnt_max).val()+1);
            var temp_id = $(next_mnt_min).attr('name').match(/\d/g).join().replace(',', '');
            $('[name=type_transaction_'+temp_id+']').val(2);
          } else {
            $(next_mnt_min).removeAttr('readonly');
            $(next_mnt_min).val('');
          }
        }
    
     var last_node = $('#commission_set tbody tr:last-child');  //Retreive the last node
     var temp_node = ($(last_node).children().length == 0)?$(last_node).prev():$(last_node);
     var clone_snapshot = $(temp_node).clone()  //holds a snapshot of the clone DOM 
     $(clone_snapshot).find('input').val('');   //Remove existing clone input values
     var ref_id = parseInt($($(clone_snapshot).children()[0]).children().text()); //holds the id of the last cloned DOM
     
     var deletion_status = false;   //Hold the state of input deletion
     var input_disabled_state = false;  //Hold the state of input enable/disable
     
     $('#commission_set input').attr('autocomplete', 'off');    //Disable autocomplete on all inputs
     
     //Set the first min level to 0
     $('#commission_set tbody tr:nth-child(3) td:nth-child(2) input').val(0);
     $('#commission_set tbody tr:nth-child(3) td:nth-child(2) input').attr('readonly', 'readonly');
         
     //DOM injection, add a new commission field     
     function addInput(){
        var plafond_depot = " . $plafond_depot . ";
        var depot_status = isDepotValid(plafond_depot);
        var list = [];
        
        if(depot_status == 1){
            var clone = $(clone_snapshot).clone();  //recreate an identical DOM from clone DOM
            var list_name = ['mnt_mini_palier' , 'mnt_max_palier' , 'mnt_prc_agent' , 'mnt_fixe_agent' , 'mnt_prc_ins' , 'mnt_fixe_ins' , 'mnt_prc_tot' , 'mnt_fixe_tot', 'type_transaction_'];
            ref_id++;
            
            //Remove clone cloned properties
            for(var pos = 1; pos <= $(temp_node).children().length; pos++){
                $($($(clone).children()[pos]).children()).attr('name', list_name[pos - 1]+ ref_id);
                $($($(clone).children()[pos]).children()).removeAttr('onchange');
                $($($(clone).children()[pos]).children()).unbind();
            }
            
            $('#commission_set tbody').append(clone); //Append modified clone to DOM
            
            $('[name*=mnt_mini_palier]').each(function(index, item){
               list.push(parseInt($(item).attr('name').match(/\d/g).join().replace(',', '')));
            });
            
            //Registers clone events
            for(var pos = 2; pos < 6; pos++){
                $('[name = '+list_name[pos]+ref_id+']').change(function(){
                    var id = parseInt($(this).attr('name').match(/\d/g).join().replace(',', ''));
                    changeMontant(id);
                });
            }
            
            $('[name=nb_ligne]').val(parseInt($('[name=nb_ligne]').val()) + 1);
            var preceding_elem = $('[name=mnt_max_palier'+(parseInt($('[name*=mnt_max_palier]').last().attr('name').match(/\d/g).join().replace(',', '')) - 1)+']');

            if($(preceding_elem).val() == ''){
                $('[name*=mnt_mini_palier]').last().val('');
            }else{
                var deleted_rows = getDeletionAmount();
                if(deleted_rows == 0){
                    $('[name*=mnt_mini_palier]').last().val(parseInt($('[name=mnt_max_palier'+(ref_id - 1)+']').val().replace(/\s/g, '')) + 1);
                }else{
                    $('[name*=mnt_mini_palier]').last().val(parseInt($('[name=mnt_max_palier'+list[list.indexOf(ref_id) - deleted_rows - 1]+']').val().replace(/\s/g, '')) + 1);
                }
            }
        
            $('[name=mnt_max_palier'+ref_id+']').focus(function(){
                $(this).data('arbitrary_val', $(this).val());
            })
            
            $('[name=type_transaction_'+ref_id+']').val(1);
            
            resetRowID();   //reset all field id
            bindPalierConstraint($('[name*=mnt_max_palier]').last());
            disabledEnabled();
        }else if(depot_status == 0){
            alert('Montant égal au plafond de dépôt, les valeurs suivantes ne seront pas prises en compte');
        }else{
            alert('Montant supérieur au plafond de dépôt');
        }
    }
    
    function isDepotValid(depot){
        var flag = 1;  
        var list = [];

        $('[name*=mnt_mini_palier]').each(function(index, item){
            if($(this).data('status') != 'deleted'){
                list.push(parseInt($(item).attr('name').match(/\d/g).join().replace(',', '')));
            }
        });
        
        for(var index = 0; index < list.length; index++){
            var val = parseInt($('[name=mnt_max_palier'+list[index]+']').val().replace(/\s/g, ''));
            console.log(val);
            if(val != '' && val != undefined){
                if(val > depot){
                    flag = -1;
                    break;
                }else if(val == depot){
                    flag = 0;
                    break;
                }
            }
        }
        
        return 1;
    }
    
    function disableInputSets(start){
        input_disabled_state = true;
        var list = [];

        $('[name*=mnt_mini_palier]').each(function(index, item){
            list.push(parseInt($(item).attr('name').match(/\d/g).join().replace(',', '')));
        });
        
        for(var index = list.indexOf(start) + 1; index < list.length; index++){
            var parent = $('[name=mnt_max_palier'+list[index]+']').parent().parent();
            $(parent).find('input').attr('readonly', 'readonly');
        }
    }
    
    function enableInputSets(start){
        input_disabled_state = false;
        var list = [];

        $('[name*=mnt_mini_palier]').each(function(index, item){
            list.push(parseInt($(item).attr('name').match(/\d/g).join().replace(',', '')));
        });
        
        for(var index =  index = list.indexOf(start) + 1; index < list.length; index++){
            var parent = $('[name=mnt_max_palier'+list[index]+']').parent().parent();
            $(parent).find('input').removeAttr('readonly');
        }
        $('[name*=mnt_mini_palier]').last().attr('readonly', 'readonly');
    }
    
    function triggerInputDeletion(){
        deletion_status = (deletion_status)?false:true;
        $('[name=butSupprimer]').val((deletion_status)?'Annuler':'Supprimer');
        
        if(deletion_status){
            $('#commission_set input').bind({
                mouseenter: function(){
                    $(this).parent().parent().css('border-color', 'red');
                    $(this).parent().parent().bind('click', function(){
                       var val = confirm('Confirmer la suppression de ce champ');
                       if(val){
                            deleteElement($(this));
                            resetRowID();
                       }
                    });
                },
                mouseleave: function(){
                    $(this).parent().parent().css('border-color', 'grey');
                    $(this).parent().parent().unbind('click');
                }
            });
        }else{
            $('#commission_set input').unbind('mouseenter');
            $('#commission_set input').unbind('mouseleave');
        }
    }
    
    function deleteElement(){
        var list = [];
        var pos;
        
        $('[name*=mnt_mini_palier]').each(function(index, item){
            list.push(parseInt($(item).attr('name').match(/\d/g).join().replace(',', '')));
        });
        
        pos = list.length - 1;
        while(($('[name=type_transaction_'+list[pos]+']').val() == 3) && (pos >= 0)){
            pos--;
        }

        if(pos < 0){
            alert('Aucun palier a supprimer');
        }else{
            var val = confirm('Confirmer la suppression de ce champ');
            var deletion_amount = getDeletionAmount();
            if(val){
                $('[name=type_transaction_'+list[pos]+']').val(3);
                $($('[name=mnt_mini_palier'+list[pos]+']').parent().parent()).css('display', 'none');
                $('[name=mnt_mini_palier'+list[pos]+']').data('status', 'deleted');
                $('[name=mnt_max_palier'+list[list.length - deletion_amount - 1]+']').unbind('change');
                bindPalierConstraint('[name=mnt_max_palier'+list[list.length - deletion_amount - 1]+']');
            }
        }
    }
    
    function resetRowID(){
        var temp_list = $('[name*=mnt_mini_palier]').parent().prev().find('b');
        for(var index = 0; index < temp_list.length; index++){
            if($(temp_list[index]).parent().parent().css('display') == 'none'){
                index--;
                temp_list.splice(index, 1);
            }
            else{
                $(temp_list[index]).text(index + 1);
            }
        }
    }
    
    function bindPalierConstraint(selector){
        $(selector).bind('change', function(){
            var id = parseInt($(this).attr('name').match(/\d/g).join().replace(',', ''));
            var mnt_min = parseInt($('[name=mnt_mini_palier'+id+']').val().replace(/\s/g, ''));
            var plafond_depot = " . $plafond_depot . ";
            var depot_status = isDepotValid(plafond_depot);
            var list = [];
            var current_val;
            var next_mnt_max;
            var next_elem_id;
            var is_next_deleted;
            var reset_state = false;
            
            $('[name*=mnt_mini_palier]').each(function(index, item){
                list.push(parseInt($(item).attr('name').match(/\d/g).join().replace(',', '')));
            });
            
            is_next_deleted = isDeleted('[name=mnt_mini_palier'+list[list.indexOf(id) + 1]+']');
            if(is_next_deleted){
                next_elem_id = list[list.indexOf(id) + getDeletionAmount() + 1];
            }else{
                next_elem_id = list[list.indexOf(id) + 1];
            }
            
            if($('[name=mnt_max_palier'+next_elem_id+']').length != 0){
                if($('[name=mnt_max_palier'+next_elem_id+']').val() == ''){
                    next_mnt_max = parseInt($(this).val()) + 1;
                    current_val = '';
                    $('[name=mnt_mini_palier'+next_elem_id+']').val('');
                }else{
                    current_val = parseInt($(this).data('arbitrary_val').replace(/\s/g, ''));
                    next_mnt_max = parseInt($('[name=mnt_max_palier'+next_elem_id+']').val().replace(/\s/g, '')) - 1;
                }
                if(parseInt($(this).val()) < next_mnt_max){
                    if(parseInt($(this).val()) <= mnt_min){
                        alert('Le montant maximum doit être supérieure au montant minimum');
                        $(this).val(current_val);
                    }else{
                        if(is_next_deleted){
                            changenextMontant(id, list[list.length - 1]);
                        }else{
                            changenextMontant(id);
                        }
                        $('[name=mnt_mini_palier'+id+']').attr('readonly', 'readonly');
                    }
                }else{
                    alert('Le montant maximum doit être inférieur au prochain montant maximum');
                    $(this).val(current_val);
                    reset_state = true;
                }
            }else if(parseInt($(this).val()) <= mnt_min){
                current_val = ($(this).data('arbitrary_val') == '')?'':parseInt($(this).data('arbitrary_val').replace(/\s/g, ''));
                alert('Le montant maximum doit être supérieure au montant minimum');
                $(this).val(current_val);
            }else{
                reset_state = false;
            }
            
            if(!reset_state){
                if(depot_status == 1){
                    if(input_disabled_state) enableInputSets(id);
                }else if(depot_status == 0){
                    alert('Montant égal au plafond de dépôt, les valeurs suivantes ne seront pas prises en compte');
                    disableInputSets(id);
                }else{
                    alert('Montant supérieur au plafond de dépôt');
                    $(this).val(plafond_depot);
                    disableInputSets(id);
                }
            }    
        });
    }
    
    function getDeletionAmount(){
        var count = 0;
        $('[name*=mnt_mini_palier]').each(function(index, item){
            if($(item).data('status') == 'deleted'){
                count++;
            }
        });
        return count;
    }
    
    function isDeleted(sel){
        return ($(sel).data('status') == 'deleted')?true:false;
    }
    $('[name*=mnt_max_palier]').focus(function(){
        $(this).data('arbitrary_val', $(this).val());
    });
    
    $('[name*=mnt]').change(function(){
        var temp_id = $(this).attr('name').match(/\d/g).join().replace(',', '');
        $('[name=type_transaction_'+temp_id+']').val(2);
    });
    
    bindPalierConstraint('[name*=mnt_max_palier]');
    resetRowID();
    ";

    $MyPage->addJS(JSP_FORM, "JS2", $js2);
  }
  $MyPage->buildHTML();
  echo $MyPage->getHTML();

}
/*}}}*/
else signalErreur(__FILE__,__LINE__,__FUNCTION__, sprintf(_("L'écran %s n'a pas pu être trouvé"), $global_nom_ecran));
?>
