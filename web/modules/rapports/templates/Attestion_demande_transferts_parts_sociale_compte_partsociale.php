<!doctype html>
<html lang="en">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
<?php if($format == 'A4'){ ?>
<title>Attestion_demande_transferts_parts_sociale_compte_partsociale_A4</title>
<?php }else if($format == 'A6'){ ?>
<title>Attestion_demande_transferts_parts_sociale_compte_partsociale_A6</title>
<?php }else if($format == 'CT'){ ?>
<title>Attestion_demande_transferts_parts_sociale_compte_partsociale_CT</title>
<?php }else if($format == 'JALI'){ ?>
<title>Attestion_demande_transferts_parts_sociale_compte_partsociale_JALI</title>
<?php }else if($format == 'TWG'){ ?>
<title>Attestion_demande_transferts_parts_sociale_compte_partsociale_TWG</title>
<?php }else if($format == 'DTB'){ ?>
<title>Attestion_demande_transferts_parts_sociale_compte_partsociale_DTB</title>
<?php }else{ ?>
<title>Attestion_demande_transferts_parts_sociale_compte_partsociale</title>
<?php } 
if($format != 'TWG'){
    $font = "Verdana, Arial, sans-serif";
}else{
    $font = "'Fira Sans', sans-serif";
}
?>

<link rel="preconnect" href="https://fonts.gstatic.com">
<link href="https://fonts.googleapis.com/css2?family=Fira+Sans:wght@500&display=swap" rel="stylesheet">

<style type="text/css">
  @page {
      /* size: A4 portrait; */
      size: <?php echo $x_inch." ".$y_inch;?>;
      margin: <?php echo $paper_margin?>;
  }
  body {
      text-align: left;
      font-size:11pt;
      color:#000;
      counter-reset: page-number 1;
      
  }
  
  * {
        font-family: <?php echo $font ?>;
    }
    table{
        font-size: x-small;
    }
    tfoot tr td{
        font-weight: bold;
        font-size: x-small;
    }
    .gray {
        background-color: lightgray
    }
    
  .page-number {
      text-align: right;
      position:absolute;
      bottom: 10px;
      right: 4%;
      border:0.5px solid #f5f5f5;
      width:4%;
      text-align:center;
      background-color:#fff;
      border-radius: 4px;
      font-size:7pt;
  }

  .page-number:before {
      counter-increment: page-number;
      content: counter(page-number);
  }
  hr {
      page-break-after: always;
      border: 0;
  }

  .head {
      color:#365f91;
      font-weight:bold;
  }

  table.collapse {
      border-collapse: collapse;
      border: 0.7pt solid black;
  }

  table.collapse td {
      border: 0.7pt solid black;
  }


  h1 {
      font-size:25pt;
      font-weight:normal;
  }

  h2 {
      font-size:13pt;
  }
  #page {
      width:700px;
  }

  .grey {
      background-color: #e5e5e5;
  }

  #table tr td {
      padding:2px;
      font-size:8pt;
  }

  .actions { display: none; }

  .table {
      width:100%;
  }

  .td_logo{
      width:25%;
  }
  .td_right{
      width:75%;
      /*border: 0.7pt solid black;*/
  }
  #watermark {
      position: fixed;
      top: 45%;
      width: 100%;
      color:#ddd;
      text-align: center;
      opacity: .3;
      font-size:50px;
      transform: rotate(310deg);
      transform-origin: 50% 50%;
      z-index: -1000;
  }
  .w3-border{
      border:2px solid #DEA274!important;
      height:40px;
      /*padding-left: 50px;*/
      width:75%;
      margin: 0px 35px 0px 35px;
      /*padding-right: 50px;*/
  }
  .w3-grey{
      color:#404040!important;background-color:#B7A7CA!important;
      /*padding-left: 50px;*/
      /*position: absolute;*/
      position: relative;
      /*top: 5px;*/
  }
  .sup {
      vertical-align:text-top;
      font-size: 15pt;
  }
  .p_align2{
      padding: 0px 10px;
      font-size: 12px;
  }
  .p_align3{
      margin: 10px 20px;
      font-size: 14px;
  }
  .p3_heading{
      background-color: #E46C0A ;
      padding: 7px;
      color: #fff;
      margin: 20px;
  }
  .p3_graph1_img{
      height: 150px;
      width: 107%;
  }
  .p3_graph_val_img{
      height: 80px;
      margin-left: 20px;
      width: 99%;
  }
  .end_line{
      position: relative;
      margin: 10px 22px;
  }
  .p3_graph2_val_img{
      height: 200px;
      width: 96%;
      margin: 0px 20px;
  }
  h4{
      line-height: 8px;
  }
  .td_odd_left{
      width: 75%; background-color: #e7eadf; padding: 5px 0px 5px 10px; border-radius: 5px;
  }
  .td_odd_right{
      width: 25%;  background-color: #e7eadf; text-align: center; border-radius: 5px;
  }
  .td_even_left{
      width: 75%; background-color: #d7e4bd; padding: 5px 0px 5px 10px; border-radius: 5px;
  }
  .td_even_right{
      width: 25%;  background-color: #d7e4bd; text-align: center; border-radius: 5px;
  }

  .borderE46C0A{
      border: 1px solid #E46C0A;
  }
  .border_topE46C0A{
      border-top: 1px solid #E46C0A;
      height:2px;
  }
  .width100{
      width:100%;
  }
  .width99_5{
      width:99.5%;
  }
  .fbold{
      font-weight: bold;
  }
  .borderlightgray{
      /* border: 0.5px solid #f5f5f5; */
      /* border-radius: 3px; */
      /* padding-left: 5px; */
      padding-left: 3px;
      /* padding-bottom: 7px; */
      padding-top: 1px;
  }
  .self_aware{
      width:100% !important; padding: 0px 8px;line-height: 15px;font-size: 12px
  }
  .emptyline_height{
  line-height:2px !important;
  }
  .f_4{
      font-size: 4pt;
  }
  .f_5{
      font-size: 5pt;
  }
  .f_6{
      font-size: 6pt;
  }
  .f_7{
      font-size: 7pt;
  }
  .f_8{
      font-size: 8pt;
  }
  .f_9{
      font-size: 9pt;
  }
  .f_10{
      font-size: 10pt;
  }
  .f_11{
      font_size: 11pt;
  }
  .f_12{
      font-size: 12pt;
  }
  .f_13{
      font_size: 13pt;
  }
  .f_14{
      font-size: 14pt;
  }
  .f_15{
      font_size: 15pt;
  }
  .pt_34{
      padding_top: 34px;
  }
  .pt_20{
    padding_top: 20px;
  }
  .pt_15{
    padding_top: 15px;
  }
  .pt_27{
    padding_top: 27px;
  }
  .billage_a4{
    margin-top: 30px;
  }
  .billage_a5{
    margin-top: 20px;
  }
  .billage_a6{
    margin-top: 15px;
  }
  .billage_twg{
    margin-top: 0px;
  }
  .billage_a6_low{
    margin-top: 15px;
  }
  .f_twg_detail{
      font-size: 5pt;
  }
  .f_twg{
      font-size: 5.5pt;
  }
  <?php
  if($format == 'TWG'){
    echo "td{
        padding: 0px;
    }";
  }
  ?>
</style>
</head>
<body>
<?php 
$count = 0;

$padding = "padding: 2px;";
$margin = "margin: 2px;";
$body_padding="padding: 3px 10px 10px 10px !important";
$margin_bt = $margin_zero = "";
if($format == 'A4'){
    $header_institution = "f_11";
    $heder_detail = "f_10";
    $logo_height = '30';
    $logo_padding_top = 'pt_34';
    $table_margin = 'billage_a4';
    $cashier_height = "height: 40px;";
 }else if($format == 'A6'){ 
    $header_institution = "f_6";
    $heder_detail = "f_5";
    $logo_height = '20';
    $logo_padding_top = 'pt_27';
    $table_margin = 'billage_a6';
    $cashier_height = "height: 25px;";
    if($count > 5){
        $table_margin = 'billage_a6_low';
    }
}else if($format == 'JALI'){ 
    $header_institution = "f_6";
    $heder_detail = "f_5";
    $logo_height = '20';
    $logo_padding_top = 'pt_27';
    $table_margin = 'billage_a6';
    $cashier_height = "height: 25px;";
    $body_padding="padding: 3px 10px 2px 0px !important";
    if($count > 5){
        $table_margin = 'billage_a6_low';
    }
}else if($format == 'CT'){ 
    $header_institution = "f_6";
    $heder_detail = "f_5";
    $logo_height = '20';
    $logo_padding_top = 'pt_27';
    $table_margin = 'billage_a6';
    $cashier_height = "height: 15px;";
    $body_padding="padding: 0px 10px 0px 0px !important";
}else if($format == 'TWG'){ 
    $header_institution = "f_twg";
    $heder_detail = "f_twg_detail";
    $logo_height = '15';
    $logo_padding_top = 'pt_15';
    $table_margin = 'billage_twg';
    $padding = "padding: 0px;";
    $margin = "margin: 0px;";
    $margin_bt = "margin-bottom: 0px;";
    $margin_zero= "margin: 0px;";
    $cashier_height = "height: 0px;";
    $body_padding="padding: 0px 5px 0px 0px !important";
}else if($format == 'DTB'){ 
    $header_institution = "f_8";
    $heder_detail = "f_7";
    $logo_height = '20';
    $logo_padding_top = 'pt_27';
    $table_margin = 'billage_a6';
    $padding = "padding: 2px;";
    $margin = "margin: 2px;";
    $cashier_height = "height: 20px;";
    $body_padding="padding: 3px 20px 0px 0px !important";
}else{ 
    $header_institution = "f_7";
    $heder_detail = "f_6";
    $logo_height = '25';
    $logo_padding_top = 'pt_27';
    $table_margin = 'billage_a5';
    $cashier_height = "height: 40px;";
 } 
 
 ?>

  <table  class="borderlightgray width100" style="<?php echo $body_padding?>">
    <tr>
        <td align="left" style="width: 50%">
            <h3 style="margin-block-start: 0em;<?php echo $margin;?>" class="<?php echo $header_institution ?>"><?php echo $xml->header->institution; ?></h3>
            <p class="<?php echo $heder_detail; ?>" style="<?php echo $padding.' '.$margin;?>">
                Agency    : <?php echo $xml->header->agence;?>
                <br>
                Phone     : <?php echo $xml->header->telephone;?>
                <br>
                Opérateur : <?php echo $xml->header->utilisateur;?>
            </p>
        </td>
        
        <td align="right" style="width: 50%">
            <img src="data:image/jpeg;base64,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" alt="" height="<?php echo $logo_height ?>" class="<?php echo $logo_padding_top?>"/>
            <p class="<?php echo $heder_detail; ?>" style="<?php echo $margin_zero;?>">
                Time Stamp: <?php echo $xml->header->date." ".$xml->header->heure;?>
            </p>
        </td>
    </tr>

    <tr>
        <td align="center" colspan="2">
            <h3 style="margin-block-start: 0em;text-align: center;<?php echo $margin_bt?>"  class="<?php echo $header_institution ?>">Attestation de demande de transfert parts sociales<br>Transfert vers autre compte de parts sociales</h3>
        </td>
    </tr>
    <tr class="<?php echo $heder_detail; ?>">
        <td style="width: 50%;font-weight: bold;text-decoration: underline;">Infos compte source</td>
        <td style="width: 50%;font-weight: bold;text-decoration: underline;">Infos compte destinataire</td>
    </tr>
    <tr class="<?php echo $heder_detail; ?>">
        <td style="width: 50%">Numéro de client: <?php echo $xml->body->num_client;?></td>
        <td style="width: 50%">Numero du client destinataire: <?php echo $xml->body->num_cli_dest;?></td>
    </tr>
    <tr class="<?php echo $heder_detail; ?>">
        <td style="width: 50%">Nom: <?php echo $xml->body->nom_client;?></td>
        <td style="width: 50%">Nom du client: <?php echo $xml->body->nom_cli_dest;?></td>
    </tr>
    <tr class="<?php echo $heder_detail; ?>">
        <td style="width: 50%">Intitulé du compte: <?php echo $xml->body->intitule_cpte_courant_src;?></td>
        <td style="width: 50%">Intitulé du compte: <?php echo $xml->body->libelle_ps;?></td>
    </tr>
    <tr class="<?php echo $heder_detail; ?>">
        <td style="width: 50%">Numéro du compte courant: <?php echo $xml->body->num_cpte_courant_src;?></td>
        <td style="width: 50%">Numero du compte: <?php echo $xml->body->num_compte_dest;?></td>
    </tr>
    <tr class="<?php echo $heder_detail; ?>">
        <td style="width: 50%">Numéro compte de part sociales: <?php echo $xml->body->num_cpte_ps;?></td>
        <td style="width: 50%">&nbsp;</td>
    </tr>
    <tr class="<?php echo $heder_detail; ?>">
        <td style="width: 50%">Valeur nominale d'une part sociale: <?php echo $xml->body->prix_part;?></td>
        <td style="width: 50%">&nbsp;</td>
    </tr>
    <tr class="<?php echo $heder_detail; ?>">
        <td style="width: 50%">Nombre de PS transférées: <?php echo $xml->body->nbre_parts;?></td>
        <td style="width: 50%">&nbsp;</td>
    </tr>
    <tr class="<?php echo $heder_detail; ?>">
        <td style="width: 50%">Valeur de PS transférées: <?php echo $xml->body->total_ps;?></td>
        <td style="width: 50%">&nbsp;</td>
    </tr>
    <tr class="<?php echo $heder_detail; ?>">
        <td style="width: 50%">Nouveau solde du compte part sociale: <?php echo $xml->body->total_ps_restant;?></td>
        <td style="width: 50%">&nbsp;</td>
    </tr>
    <tr class="<?php echo $heder_detail; ?>">
        <td style="width: 50%">Nombre finale de PS souscrites: <?php echo $xml->body->nbre_total_ps_sous;?></td>
        <td style="width: 50%">&nbsp;</td>
    </tr>
    <tr class="<?php echo $heder_detail; ?>">
        <td style="width: 50%">Nombre finale de PS Libérées: <?php echo $xml->body->nbre_total_ps_lib;?></td>
        <td style="width: 50%">&nbsp;</td>
    </tr>
    <tr class="<?php echo $heder_detail; ?>">
        <td style="width: 50%">Numéro de transaction: <?php echo $xml->body->num_trans;?></td>
        <td style="width: 50%">&nbsp;</td>
    </tr>
    <tr class="<?php echo $heder_detail; ?>">
        <td style="padding-left: 5px;"><div style="margin-left:20px;margin-right:20px;">Signature opérateur</div></td>
        <td  colspan="1" style="padding-left: 5px;"><div style="margin-left:20px;margin-right:20px;">Signature client</div></td>
    </tr>          
    <tr>
        <td colspan="4" style="<?php echo $cashier_height?>"> </td>
    </tr> 
  </table>


</body>
</html>