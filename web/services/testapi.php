<?php
$return_data = array(
  'success' => true,
  'datas' => array(
    'identifiant_client' => $_POST['identifiant_client'],
    'num_compte_cible' => '12121',
    'montant' => '1000',
    'msg' => 'test',
    'type_action' => $_POST['type_action'],
  ),
);
echo json_encode($return_data, 1 | 4 | 2 | 8);
exit;
?>