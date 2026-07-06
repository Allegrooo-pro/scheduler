<?php
session_start();
include "../config/koneksi.php";

if(!isset($_SESSION['login'])){
    header("Location:../index.php");
    exit;
}

$user_id = $_SESSION['id'];

$id = $_GET['id'];

$data = mysqli_fetch_assoc(mysqli_query($conn,"
SELECT *
FROM agenda
WHERE id='$id'
AND user_id='$user_id'
"));

if(!$data){
    header("Location:schedule.php");
    exit;
}

if(isset($_POST['update'])){

    $tanggal     = $_POST['tanggal'];
    $jam_mulai   = $_POST['jam_mulai'];
    $jam_selesai = $_POST['jam_selesai'];
    $status      = $_POST['status'];
    $keterangan  = mysqli_real_escape_string($conn,$_POST['keterangan']);

    mysqli_query($conn,"
    UPDATE agenda SET

    tanggal='$tanggal',
    jam_mulai='$jam_mulai',
    jam_selesai='$jam_selesai',
    status='$status',
    keterangan='$keterangan'

    WHERE id='$id'
    ");

    header("Location:schedule.php");
    exit;
}
?>

<!DOCTYPE html>
<html>

<head>

<meta charset="UTF-8">

<title>Edit Agenda</title>

<script src="https://cdn.tailwindcss.com"></script>

</head>

<body class="bg-[#25364A] text-white">

<?php include "../includes/navbar.php"; ?>

<div class="max-w-3xl mx-auto mt-10">

<div class="bg-[#1F2A3A] rounded-xl p-8">

<h1 class="text-3xl text-cyan-400 font-bold mb-8">

✏️ Edit Agenda

</h1>

<form method="POST">

<label>Tanggal</label>

<input
type="date"
name="tanggal"
value="<?= $data['tanggal']; ?>"
class="w-full bg-[#31475E] rounded p-3 mt-2 mb-5">

<label>Jam Mulai</label>

<input
type="time"
name="jam_mulai"
value="<?= $data['jam_mulai']; ?>"
class="w-full bg-[#31475E] rounded p-3 mt-2 mb-5">

<label>Jam Selesai</label>

<input
type="time"
name="jam_selesai"
value="<?= $data['jam_selesai']; ?>"
class="w-full bg-[#31475E] rounded p-3 mt-2 mb-5">

<label>Status</label>

<select
name="status"
class="w-full bg-[#31475E] rounded p-3 mt-2 mb-5">

<option value="Kosong"
<?= ($data['status']=="Kosong")?"selected":""; ?>>

Kosong

</option>

<option value="Sibuk"
<?= ($data['status']=="Sibuk")?"selected":""; ?>>

Sibuk

</option>

</select>

<label>Keterangan</label>

<textarea
name="keterangan"
class="w-full bg-[#31475E] rounded p-3 mt-2 mb-5"
rows="5"><?= $data['keterangan']; ?></textarea>

<div class="flex justify-end gap-4">

<a
href="schedule.php"
class="bg-gray-600 px-6 py-3 rounded">

Batal

</a>

<button
name="update"
class="bg-cyan-600 px-8 py-3 rounded">

Update Agenda

</button>

</div>

</form>

</div>

</div>

</body>
</html>