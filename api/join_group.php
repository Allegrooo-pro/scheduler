<?php
session_start();

include "../config/koneksi.php";

$user = $_SESSION['id'];
$group = $_POST['group_id'];

$cek = mysqli_query($conn,"
SELECT *
FROM group_members
WHERE group_id='$group'
AND user_id='$user'
");

if(mysqli_num_rows($cek)==0){

    mysqli_query($conn,"
    INSERT INTO group_members(group_id,user_id)
    VALUES('$group','$user')
    ");

}

header("Location:../pages/group.php");
exit;