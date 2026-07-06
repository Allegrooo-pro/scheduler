<?php

session_start();

include "../config/koneksi.php";

$id=$_POST['id'];

$tanggal=$_POST['tanggal'];

$mulai=$_POST['jam_mulai'];

$selesai=$_POST['jam_selesai'];

$status=$_POST['status'];

$ket=mysqli_real_escape_string($conn,$_POST['keterangan']);

mysqli_query($conn,"
UPDATE agenda SET

tanggal='$tanggal',

jam_mulai='$mulai',

jam_selesai='$selesai',

status='$status',

keterangan='$ket'

WHERE id='$id'
");

header("Location:../pages/schedule.php");