<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Create New Community</title>
</head>
<body>

<?php
require 'db_connect_create_community.php';
require 'security.php';
 
redirectIfNotLoggedIn();


$userID = $_SESSION['userid'];

$userName_sql = "
SELECT userName
FROM users
WHERE userID = :userID
";

$userName_stid = oci_parse($db_conn, $userName_sql);
oci_bind_by_name($userName_stid, ':userID', $userID);
oci_execute($userName_stid, OCI_DEFAULT);
$userName = oci_fetch_assoc($userName_stid)['USERNAME'];

oci_free_statement($userName_stid);

$stid = null;

if (isset($_POST['submit'])) {
    $name = $_POST['communityName'];

    if (!sanitizeUserInputCheck($name)) {
        $check_sql = "SELECT communityID FROM community WHERE UPPER(name) = UPPER(:name) ";
        $check_stid = oci_parse($db_conn, $check_sql);
        oci_bind_by_name($check_stid, ":name", $name);
        oci_execute($check_stid);
        $row=oci_fetch_assoc($check_stid);
        oci_free_statement($check_stid);
    
     if($row){
        echo "<p><strong> ERROR[Sorry...this name already exist, please enter another one].</p>";
    } else{
    
    $insert_sql = "
        INSERT INTO community (name, leader, memberCount) 
        VALUES (:name, :userName, 1)
        ";
    
        $stid = oci_parse($db_conn, $insert_sql);
        oci_bind_by_name($stid, ':name', $name);
        oci_bind_by_name($stid, ':userName', $userName);
    
        oci_execute($stid, OCI_DEFAULT);
    
        if($stid){
            oci_free_statement($stid);
        }
    
        $commit_success = oci_commit($db_conn);
    
        if($commit_success){
    
        $fetch_ID_sql = "
        SELECT communityID
        FROM community
        WHERE leader = :userName
        ";
    
        $fetch_stid = oci_parse($db_conn, $fetch_ID_sql);
        oci_bind_by_name($fetch_stid, ':userName', $userName);
        oci_execute($fetch_stid, OCI_DEFAULT);
        $communityID = oci_fetch_assoc($fetch_stid)['COMMUNITYID'];
    
        $update_sql = "
        UPDATE users
        SET partof_community = :communityID
        WHERE userID = :userID
        ";
    
        $update_stid = oci_parse($db_conn, $update_sql);
        oci_bind_by_name($update_stid, ':communityID', $communityID);
        oci_bind_by_name($update_stid, ':userID', $userID);
        oci_execute($update_stid, OCI_DEFAULT);
    
    
    
        if($fetch_stid){
            oci_free_statement($fetch_stid);
        }
    
        if($update_stid){
            oci_free_statement($update_stid);
        }
    
        oci_commit($db_conn);
    
        echo "Community has been successfully created. Click <a href='community.php'>HERE</a> to go back to your Community Information Page";
    } else {
        oci_rollback($db_conn);
        echo "Failed";
    }
    }

    }else{
        echo "invalid input for security purpose!! ";
    }
}


oci_close($db_conn);
?>

<h3>Create a New Community: </h3>
<form method="post" action="create_community.php"> 
        <label for="communityName">Community Name: </label>
        <input type ="text" id ="communityName" name="communityName" required>
        <button type = "submit" name = "submit">Submit</button>
        </form> 

</body>
</html>

