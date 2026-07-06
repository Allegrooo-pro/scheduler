<?php

session_start();

include "../config/koneksi.php";

$user=$_SESSION['id'];

$tanggal=$_POST['tanggal'];

$mulai=$_POST['jam_mulai'];

$selesai=$_POST['jam_selesai'];

$status=$_POST['status'];

$ket=mysqli_real_escape_string($conn,$_POST['keterangan']);

mysqli_query($conn,"
INSERT INTO agenda
(user_id,tanggal,jam_mulai,jam_selesai,status,keterangan)

VALUES

('$user','$tanggal','$mulai','$selesai','$status','$ket')
");

header("Location:../pages/schedule.php");