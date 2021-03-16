<?php
    require_once 'lib/dbProcedures/rapports.php';

    $request_form = $_REQUEST["request"];

    switch($request_form){
        case "delete_report":
            unlink_report_tag($_REQUEST["parent_key"], $_REQUEST["file_path"]);
            echo file_delete($_REQUEST["file_path"]);
            break;
        default:
            throw new Exception("Invalid call to ".__FILE__);
    }

?>