<?php
session_start();
include "../config/koneksi.php";

if(!isset($_SESSION['login'])){
    header("Location:../index.php");
    exit;
}

$user_id=$_SESSION['id'];
$nama=$_SESSION['nama'];

/* =====================================================
BUAT GROUP
===================================================== */

if(isset($_POST['buat'])){

    $nama_group=mysqli_real_escape_string($conn,$_POST['nama_group']);
    $deskripsi=mysqli_real_escape_string($conn,$_POST['deskripsi']);

    mysqli_query($conn,"
    INSERT INTO groups
    (nama_group,deskripsi,created_by)

    VALUES

    ('$nama_group','$deskripsi','$user_id')
    ");

    $group_id=mysqli_insert_id($conn);

    mysqli_query($conn,"
    INSERT INTO group_members
    (group_id,user_id)

    VALUES

    ('$group_id','$user_id')
    ");

    header("Location:group.php?id=".$group_id);
    exit;

}

/* =====================================================
SEARCH
===================================================== */

$cari="";

if(isset($_GET['cari'])){

$cari=mysqli_real_escape_string($conn,$_GET['cari']);

}

/* =====================================================
SEMUA GROUP
===================================================== */

$groups=mysqli_query($conn,"
SELECT

groups.*,

users.nama

FROM groups

JOIN users

ON users.id=groups.created_by

WHERE

nama_group LIKE '%$cari%'

ORDER BY nama_group
");

/* =====================================================
GROUP TERPILIH
===================================================== */

if(isset($_GET['id'])){

$group_id=$_GET['id'];

}else{

$temp=mysqli_fetch_assoc(mysqli_query($conn,"
SELECT id
FROM groups
LIMIT 1
"));

$group_id=$temp['id']??0;

}

$detail=mysqli_fetch_assoc(mysqli_query($conn,"
SELECT

groups.*,

users.nama

FROM groups

JOIN users

ON users.id=groups.created_by

WHERE groups.id='$group_id'
"));

/* =====================================================
CEK MEMBER
===================================================== */

$cek=mysqli_query($conn,"
SELECT *
FROM group_members
WHERE
group_id='$group_id'
AND
user_id='$user_id'
");

$sudah_join=mysqli_num_rows($cek)>0;
?>

<!DOCTYPE html>

<html lang="id">

<head>

<meta charset="UTF-8">

<title>Group</title>

<script src="https://cdn.tailwindcss.com"></script>

<link
href="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.11/index.global.min.css"
rel="stylesheet">

<style>

body{

background:#25364A;

}

.card{

background:#1F2A3A;

border-radius:20px;

padding:25px;

transition:.3s;

box-shadow:0 20px 35px rgba(0,0,0,.25);

}

.card:hover{

transform:translateY(-4px);

}

.input{

width:100%;

padding:12px;

background:#31475E;

border-radius:12px;

margin-top:10px;

color:white;

}

.btn{

background:#0891B2;

padding:12px 20px;

border-radius:12px;

}

.btn:hover{

background:#0E7490;

}

/* FullCalendar Styling */
#calendarGroup {
  color: #1e293b;
}

#calendarGroup .fc-toolbar-title {
  font-size: 1.4rem !important;
  font-weight: 700 !important;
  color: #0f172a !important;
}

#calendarGroup .fc-col-header-cell-cushion {
  color: #334155 !important;
  font-weight: 600 !important;
  text-decoration: none !important;
}

#calendarGroup .fc-daygrid-day-number {
  color: #334155 !important;
  text-decoration: none !important;
}

#calendarGroup .fc-day-today {
  background-color: #fef9c3 !important;
}

#calendarGroup .fc-button {
  background-color: #1e293b !important;
  border-color: #1e293b !important;
  color: white !important;
  border-radius: 8px !important;
  font-weight: 600 !important;
}

#calendarGroup .fc-button:hover {
  background-color: #0891B2 !important;
  border-color: #0891B2 !important;
}

#calendarGroup .fc-button-active {
  background-color: #0891B2 !important;
  border-color: #0891B2 !important;
}

#calendarGroup .fc-event {
  border-radius: 6px !important;
  font-size: 0.78rem !important;
  font-weight: 500 !important;
  padding: 2px 5px !important;
  border: none !important;
}

#calendarGroup .fc-daygrid-day {
  min-height: 90px !important;
}

</style>

</head>

<body class="text-white">

<?php include "../includes/navbar.php"; ?>

<div class="max-w-screen-2xl mx-auto px-8 py-8">

<div class="flex justify-between items-center">

<div>

<h1 class="text-5xl font-bold">

👥 Group Workspace

</h1>

<p class="text-gray-300 mt-3">

Kelola group dan lihat agenda seluruh anggota.

</p>

</div>

<button

id="btnGroup"

class="btn">

+ Buat Group

</button>

</div>

<!-- =====================================================
LAYOUT
===================================================== -->

<div class="grid grid-cols-12 gap-8 mt-8">

<!-- SIDEBAR -->

<div class="col-span-3">

<div class="card">

<h2 class="text-2xl text-cyan-400 font-bold">

Cari Group

</h2>

<form class="mt-5">

<input

type="text"

name="cari"

value="<?= $cari ?>"

placeholder="Cari group..."

class="input">

<button

class="btn mt-5 w-full">

Cari

</button>

</form>

</div>

<div class="card mt-6">

<h2 class="text-2xl text-cyan-400 font-bold mb-5">

Daftar Group

</h2>

<?php while($g=mysqli_fetch_assoc($groups)){ ?>

<a

href="?id=<?= $g['id']; ?>"

class="block rounded-xl p-4 mb-3

<?= ($group_id==$g['id'])?

'bg-cyan-700':

'bg-[#31475E]'; ?>">

<div class="font-bold">

<?= $g['nama_group']; ?>

</div>

<div class="text-sm text-gray-300 mt-2">

<?= $g['nama']; ?>

</div>

</a>

<?php } ?>

</div>

</div>

<!-- PANEL KANAN -->

<div class="col-span-9">

<div class="card">

<h2 class="text-4xl text-cyan-400 font-bold">

<?= $detail['nama_group']??"-"; ?>

</h2>

<p class="text-gray-300 mt-4">

<?= $detail['deskripsi']??"-"; ?>

</p>

<div class="mt-5">

Creator :

<b>

<?= $detail['nama']??"-"; ?>

</b>

</div>

<div class="mt-6">

<?php if($sudah_join){ ?>

<button
class="bg-green-600 px-8 py-3 rounded-xl">

✔ Sudah Bergabung

</button>

<?php } else { ?>

<form action="../api/join_group.php" method="POST">

<input type="hidden"
name="group_id"
value="<?= $group_id ?>">

<button class="btn">

Join Group

</button>

</form>

<?php } ?>

</div>

</div>

<div class="grid grid-cols-12 gap-8 mt-8"></div>
<!-- =====================================================
MEMBER + KALENDER
===================================================== -->

<?php

/* =======================================
   MEMBER GROUP
======================================= */

$member=mysqli_query($conn,"
SELECT users.*
FROM group_members

JOIN users
ON users.id=group_members.user_id

WHERE group_members.group_id='$group_id'

ORDER BY users.nama
");

/* =======================================
   AGENDA GROUP
======================================= */
$agenda=mysqli_query($conn,"
SELECT agenda.*,users.nama
AS member_nama
FROM agenda
INNER JOIN users
ON users.id=agenda.user_id
INNER JOIN group_members
ON group_members.user_id=users.id
WHERE group_members.group_id='$group_id'
ORDER BY agenda.tanggal,agenda.jam_mulai
");

/* =======================================
   STATISTIK GROUP
======================================= */

// Total Member
$totalMember = mysqli_num_rows($member);

// Total Agenda
$totalAgenda = mysqli_num_rows($agenda);

// Hitung status Kosong
$kosong = mysqli_fetch_row(mysqli_query($conn,"
SELECT COUNT(*)
FROM agenda
JOIN group_members
ON group_members.user_id = agenda.user_id
WHERE
group_members.group_id = '$group_id'
AND agenda.status='Kosong'
"))[0];

// Hitung status Sibuk
$sibuk = mysqli_fetch_row(mysqli_query($conn,"
SELECT COUNT(*)
FROM agenda
JOIN group_members
ON group_members.user_id = agenda.user_id
WHERE
group_members.group_id = '$group_id'
AND agenda.status='Sibuk'
"))[0];

// Kembalikan pointer hasil query
mysqli_data_seek($member,0);
mysqli_data_seek($agenda,0);
?>

<div class="grid md:grid-cols-3 gap-8 mt-8">

<!-- MEMBER -->

<div class="col-span-4">

<div class="card">

<h2 class="text-2xl text-cyan-400 font-bold mb-5">

👥 Member Group

</h2>

<?php

if(mysqli_num_rows($member)==0){

?>

<div class="bg-[#31475E] rounded-xl p-5">

Belum ada anggota.

</div>

<?php

}

while($m=mysqli_fetch_assoc($member)){

?>

<div
class="flex items-center gap-4 bg-[#31475E] rounded-xl p-4 mb-3">

<div
class="w-14 h-14 rounded-full bg-cyan-600 flex items-center justify-center text-xl font-bold">

<?= strtoupper(substr($m['nama'],0,1)); ?>

</div>

<div>

<div class="font-bold text-lg">

<?= $m['nama']; ?>

</div>

<div class="text-sm text-gray-300">

Member

</div>

</div>

</div>

<?php } ?>

</div>

</div>

<!-- KALENDER -->

<div class="col-span-4">

<div class="card">

<div class="flex justify-between items-center mb-5">

<h2 class="text-2xl text-cyan-400 font-bold">

📅 Kalender Group

</h2>

<div class="text-gray-300">

Agenda Semua Member

</div>

</div>

<div
id="calendarGroup"
class="bg-white rounded-xl p-3">

</div>

</div>

<?php

$aktivitas = mysqli_query($conn,"
SELECT
agenda.*,
users.nama
FROM agenda
JOIN users
ON users.id=agenda.user_id
JOIN group_members
ON group_members.user_id=users.id
WHERE group_members.group_id='$group_id'
ORDER BY agenda.tanggal DESC, agenda.jam_mulai ASC
LIMIT 10
");

?>

<div class="card mt-8">

<div class="flex justify-between items-center mb-6">

<h2 class="text-3xl text-cyan-400 font-bold">

📝 Aktivitas Member

</h2>

<div class="text-gray-300">

10 Aktivitas Terbaru

</div>

</div>

<?php

if(mysqli_num_rows($aktivitas)==0){

?>

<div class="bg-[#31475E] rounded-xl p-6 text-center">

Belum ada aktivitas.

</div>

<?php

}

while($a=mysqli_fetch_assoc($aktivitas)){

?>

<div class="flex justify-between items-center bg-[#31475E] rounded-xl p-5 mb-3">

<div>

<div class="font-bold text-lg">

<?= $a['nama']; ?>

</div>

<div class="text-gray-300 mt-1">

<?= $a['keterangan']; ?>

</div>

</div>

<div class="text-right">

<div>

<?= date("d M Y",strtotime($a['tanggal'])); ?>

</div>

<div class="text-cyan-300">

<?= substr($a['jam_mulai'],0,5); ?>

-

<?= substr($a['jam_selesai'],0,5); ?>

</div>

</div>

</div>

<?php } ?>

</div>

<div class="card mt-8">

<h2 class="text-2xl text-cyan-400 font-bold mb-6">

⚡ Quick Action

</h2>

<div class="grid grid-cols-3 gap-6">

<a
href="schedule.php"
class="bg-cyan-600 hover:bg-cyan-700 rounded-xl p-5 text-center">

📅

<br><br>

Kelola Agenda

</a>

<a
href="tentukan_waktu.php?id=<?= $group_id ?>"
class="bg-green-600 hover:bg-green-700 rounded-xl p-5 text-center">

🤖

<br><br>

Tentukan Waktu

</a>

<a
href="#"
id="btnGroup2"
class="bg-indigo-600 hover:bg-indigo-700 rounded-xl p-5 text-center">

➕

<br><br>

Buat Group

</a>

</div>

</div>

<div class="grid grid-cols-2 gap-8 mt-8">

<!-- Chart -->

<div class="card">

<h2 class="text-2xl text-cyan-400 font-bold mb-6">

📊 Statistik Agenda Group

</h2>

<canvas id="chartGroup" height="250"></canvas>

</div>

<!-- Notifikasi -->

<div class="card">

<h2 class="text-2xl text-cyan-400 font-bold mb-6">

🔔 Aktivitas Terbaru

</h2>

<?php

mysqli_data_seek($aktivitas,0);

$count=0;

while($n=mysqli_fetch_assoc($aktivitas)){

$count++;

if($count>5) break;

?>

<div class="bg-[#31475E] rounded-xl p-4 mb-3">

<div class="font-bold">

<?= $n['nama']; ?>

</div>

<div class="text-gray-300 mt-2">

<?= $n['keterangan']; ?>

</div>

<div class="text-cyan-300 mt-2">

<?= date("d M Y",strtotime($n['tanggal'])) ?>

</div>

</div>

<?php } ?>

</div>

</div>

<div class="card mt-8">

<h2 class="text-2xl text-cyan-400 font-bold mb-6">

🤖 Ringkasan Group

</h2>

<div class="grid grid-cols-3 gap-6">

<div class="bg-[#31475E] rounded-xl p-6 text-center">

<div class="text-5xl">

👥

</div>

<div class="text-4xl mt-4">

<?= $totalMember ?>

</div>

<div class="text-gray-300 mt-2">

Total Member

</div>

</div>

<div class="bg-[#31475E] rounded-xl p-6 text-center">

<div class="text-5xl">

📅

</div>

<div class="text-4xl mt-4">

<?= $totalAgenda ?>

</div>

<div class="text-gray-300 mt-2">

Agenda Tercatat

</div>

</div>

<div class="bg-[#31475E] rounded-xl p-6 text-center">

<div class="text-5xl">

🤖

</div>

<div class="mt-4">

Website siap menghitung

waktu terbaik

untuk seluruh anggota.

</div>

</div>

</div>

</div>

</div>

</div>




<script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.11/index.global.min.js"></script>

<script>

document.addEventListener("DOMContentLoaded",function(){

const calendar=new FullCalendar.Calendar(

document.getElementById("calendarGroup"),

{

initialView:"dayGridMonth",

height:"auto",

locale:"id",

firstDay:0,

dayMaxEvents:3,

headerToolbar:{
left:"prev,next today",
center:"title",
right:"dayGridMonth,timeGridWeek"
},

buttonText:{
today:"today",
month:"month",
week:"week"
},

events:[

<?php
mysqli_data_seek($agenda,0);

while($a=mysqli_fetch_assoc($agenda)){
?>

{

title:"<?= addslashes($a['member_nama'].' - '.$a['keterangan']) ?>",

start:"<?= $a['tanggal'] ?>",

color:"<?= $a['status']=="Kosong" ? "#22c55e" : "#ef4444"; ?>"

},

<?php } ?>

]

}

);

calendar.render();

});

</script>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>

const ctx=document.getElementById("chartGroup");

new Chart(ctx,{

type:"doughnut",

data:{

labels:[

"Kosong",

"Sibuk"

],

datasets:[{

data:[

<?= $kosong ?>,

<?= $sibuk ?>

],

backgroundColor:[

"#22c55e",

"#ef4444"

]

}]

},

options:{

plugins:{

legend:{

labels:{

color:"white"

}

}

}

}

});

</script>

</body>

</html>