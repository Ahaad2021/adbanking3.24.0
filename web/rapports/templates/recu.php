<!doctype html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Recu_Retrait</title>
<style type="text/css">

  @page {
      size: A4 portrait;
      margin: 0.5cm 0.7cm 0.3cm 0.7cm;
  }
  body {
      text-align: left;
      font-size:11pt;
      color:#000;
      counter-reset: page-number 1;
      
  }

  
  * {
        font-family: Verdana, Arial, sans-serif;
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
      border: 0.5px solid #f5f5f5;
      border-radius: 3px;
      padding-left: 5px;
      padding-left: 3px;
      padding-bottom: 7px;
      padding-top: 1px;
  }
  .self_aware{
      width:100% !important; padding: 0px 8px;line-height: 15px;font-size: 12px
  }
  .emptyline_height{
  line-height:2px !important;
  }
  
</style>
</head>
<body>

  <table  class="borderlightgray width100" style="padding: 3px 10px 10px 10px !important">
    <tr>
        <td align="left">
            <h3 style="margin-block-start: 0em;"><?php echo $xml->header->institution; ?></h3>
            <p>
                Agency    : <?php echo $xml->header->agence;?>
                <br>
                Phone     : <?php echo $xml->header->telephone;?>
                <br>
                Opérateur : <?php echo $xml->header->utilisateur;?>
            </p>
        </td>
        
        <td align="right">
            <img src="data:image/jpeg;base64,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" alt="" height="30" style="padding-top: 34px;"/>
            <p>
                Time Stamp: <?php echo $xml->header->date." ".$xml->header->heure;?>
            </p>
        </td>
    </tr>

    <tr>
        <td align="center" colspan="2">
            <h3 style="margin-block-start: 0em;">Reçu retrait en espèces</h3>
        </td>
    </tr>
    <tr>
        <td colspan="1">Nom du client</td>
        <td colspan="1">: <?php echo $xml->body->nom_client;?></td>
    </tr>
    <tr>
        <td colspan="1">Numéro de compte</td>
        <td colspan="1">: <?php echo $xml->body->num_cpte;?></td>
    </tr>
    <tr>
        <td colspan="1">Montant</td>
        <td colspan="1">: <?php echo $xml->body->montant;?></td>
    </tr>
    <tr>
        <td colspan="1">Montant en lettre</td>
        <td colspan="1">: <?php echo $xml->body->mntEnLettre;?></td>
    </tr>
    <tr>
        <td colspan="1">Frais de retrait/dépôt</td>
        <td colspan="1">: <?php echo $xml->body->frais;?></td>
    </tr>
    <tr>
        <td colspan="1">Frais de non respect de la duree minimum entre deux retraits</td>
        <td colspan="1">: <?php echo $xml->body->fraisDureeMin;?></td>
    </tr>
    <tr>
        <td colspan="1">Nouveau solde</td>
        <td colspan="1">: <?php echo $xml->body->solde;?></td>
    </tr>
    <tr>
        <td colspan="1">Numéro de transaction</td>
        <td colspan="1">: <?php echo $xml->body->num_trans;?></td>
    </tr>
    <tr>
        <td align="center">
        <?php if($xml->body->hasBilletage == 1){ ?>
            <table width="100%" style="margin-left: 150px;margin-top: 30px;border: 1px solid black;border-collapse: collapse;">
                <tbody>
                    <tr>
                        <td style="border: 1px solid black;padding-left: 5px">Billets et pièces de monnaie</td>
                        <td align="right"  style="border: 1px solid black;padding-right: 5px"><?php echo $xml->body->libel_billet_4;?></td>
                    </tr>
                    <tr>
                        <td  style="border: 1px solid black;padding-left: 5px">Nombre</td>
                        <td align="right"  style="border: 1px solid black;padding-right: 5px"><?php echo $xml->body->valeur_billet_4;?></td>
                    </tr>
                    <tr>
                        <td  style="border: 1px solid black;padding-left: 5px">Total</td>
                        <td align="right"  style="border: 1px solid black;padding-right: 5px"><?php echo $xml->body->total_billet_4;?></td>
                    </tr>
                </tbody>
            </table>
        <?php } ?>
        </td>
    </tr>
  </table>


</body>
</html>