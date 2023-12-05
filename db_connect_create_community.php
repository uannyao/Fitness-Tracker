<?php
$servername = "dbhost.students.cs.ubc.ca:1522/stu";
$username = "ora_massad";
$password = "a54398540";

global $db_conn;

$db_conn = oci_connect("ora_massad", "a54398540", "dbhost.students.cs.ubc.ca:1522/stu");

if ($db_conn) {
    debugAlertMessage("Database is Connected");
    
} else {
    debugAlertMessage("Cannot connect to Database");
    $e = OCI_Error(); // For OCILogon errors pass no handle
    echo htmlentities($e['message']);
    
}
return $db_conn;

    function debugAlertMessage($message) {
        global $show_debug_alert_messages;

        if ($show_debug_alert_messages) {
            echo "<script type='text/javascript'>alert('" . $message . "');</script>";
        }
    }

    function oci_execute_check($stid) {
        if (oci_execute($stid)) {
            return true;
        } else {
            $error = oci_error($stid);
            echo "Error: " . $error['message'];
            echo "<br";
        }
        return false;
    }



?>
