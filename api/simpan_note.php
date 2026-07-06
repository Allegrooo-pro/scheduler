<?php

session_start();

include "../config/koneksi.php";

$user_id=$_SESSION['id'];

$isi=mysqli_real_escape_string($conn,$_POST['isi']);

$cek=mysqli_query($conn,"
SELECT *
FROM notes
WHERE user_id='$user_id'
");

if(mysqli_num_rows($cek)>0){

mysqli_query($conn,"
UPDATE notes
SET isi='$isi'
WHERE user_id='$user_id'
");

}else{

mysqli_query($conn,"
INSERT INTO notes(user_id,isi)
VALUES('$user_id','$isi')
");

}

header("Location:../pages/schedule.php");