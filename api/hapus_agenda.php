<?php

session_start();

include "../config/koneksi.php";

$id=$_GET['id'];

mysqli_query($conn,"
DELETE FROM agenda
WHERE id='$id'
");

header("Location:../pages/schedule.php");