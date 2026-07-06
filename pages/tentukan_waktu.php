<?php
session_start();
include "../config/koneksi.php";

if(!isset($_SESSION['login'])){
    header("Location:../index.php");
    exit;
}

$user_id = $_SESSION['id'];
$nama    = $_SESSION['nama'];

/* ==========================================
   GROUP YANG DIPILIH
========================================== */

$group_id = isset($_GET['group']) ? (int)$_GET['group'] : 0;

if($group_id == 0){

    $stmt = mysqli_prepare($conn,"
    SELECT group_id
    FROM group_members
    WHERE user_id=?
    LIMIT 1
    ");
    mysqli_stmt_bind_param($stmt,"i",$user_id);
    mysqli_stmt_execute($stmt);
    $g = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));

    $group_id = $g['group_id'] ?? 0;

}

/* ==========================================
   DETAIL GROUP
========================================== */

$stmt = mysqli_prepare($conn,"SELECT * FROM groups WHERE id=?");
mysqli_stmt_bind_param($stmt,"i",$group_id);
mysqli_stmt_execute($stmt);
$group = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));

/* ==========================================
   MEMBER GROUP
========================================== */

$stmt = mysqli_prepare($conn,"
SELECT
users.id,
users.nama
FROM group_members
JOIN users
ON users.id=group_members.user_id
WHERE group_members.group_id=?
ORDER BY users.nama
");
mysqli_stmt_bind_param($stmt,"i",$group_id);
mysqli_stmt_execute($stmt);
$members = mysqli_stmt_get_result($stmt);

$totalMember = mysqli_num_rows($members);

/* ==========================================
   AGENDA GROUP
========================================== */

$stmt = mysqli_prepare($conn,"
SELECT

agenda.*,

users.nama

FROM agenda

JOIN users
ON users.id=agenda.user_id

JOIN group_members
ON group_members.user_id=users.id

WHERE group_members.group_id=?

ORDER BY agenda.tanggal,agenda.jam_mulai
");
mysqli_stmt_bind_param($stmt,"i",$group_id);
mysqli_stmt_execute($stmt);
$agenda = mysqli_stmt_get_result($stmt);

$totalAgenda = mysqli_num_rows($agenda);

/* ==========================================
   TANGGAL YANG ADA
   (FIX: tabel agenda tidak punya kolom group_id
   langsung -- harus lewat JOIN group_members
   seperti query lain, supaya konsisten & tidak
   menghasilkan data kosong/salah)
========================================== */

$stmt = mysqli_prepare($conn,"
SELECT DISTINCT agenda.tanggal
FROM agenda
JOIN group_members
    ON group_members.user_id = agenda.user_id
WHERE group_members.group_id = ?
ORDER BY agenda.tanggal
");
mysqli_stmt_bind_param($stmt,"i",$group_id);
mysqli_stmt_execute($stmt);
$tanggal = mysqli_stmt_get_result($stmt);

/* ==========================================
   ALGORITMA PENILAIAN
========================================== */

$hasilRekomendasi = [];

while($tgl = mysqli_fetch_assoc($tanggal)){

    $tanggalSekarang = $tgl['tanggal'];

    $stmtKosong = mysqli_prepare($conn,"
    SELECT COUNT(*)
    FROM agenda
    JOIN group_members
        ON group_members.user_id = agenda.user_id
    WHERE
        group_members.group_id = ?
        AND agenda.tanggal = ?
        AND agenda.status = 'Kosong'
    ");
    mysqli_stmt_bind_param($stmtKosong,"is",$group_id,$tanggalSekarang);
    mysqli_stmt_execute($stmtKosong);
    $kosong = mysqli_fetch_row(mysqli_stmt_get_result($stmtKosong))[0];

    $stmtSibuk = mysqli_prepare($conn,"
    SELECT COUNT(*)
    FROM agenda
    JOIN group_members
        ON group_members.user_id = agenda.user_id
    WHERE
        group_members.group_id = ?
        AND agenda.tanggal = ?
        AND agenda.status = 'Sibuk'
    ");
    mysqli_stmt_bind_param($stmtSibuk,"is",$group_id,$tanggalSekarang);
    mysqli_stmt_execute($stmtSibuk);
    $sibuk = mysqli_fetch_row(mysqli_stmt_get_result($stmtSibuk))[0];

    $score = 0;

    if($totalMember > 0){
        $score = round(($kosong / $totalMember) * 100);
    }

    $hasilRekomendasi[] = [

        "tanggal"=>$tanggalSekarang,

        "kosong"=>$kosong,

        "sibuk"=>$sibuk,

        "score"=>$score

    ];

}

usort($hasilRekomendasi,function($a,$b){

    return $b['score'] <=> $a['score'];

});
?>

<!DOCTYPE html>

<html lang="id">

<head>

<meta charset="UTF-8">

<meta
name="viewport"
content="width=device-width, initial-scale=1.0">

<title>Tentukan Waktu</title>

<script src="https://cdn.tailwindcss.com"></script>

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

.title{

color:#22D3EE;

font-weight:bold;

}

.btn{

background:#0891B2;

padding:12px 24px;

border-radius:12px;

transition:.3s;

}

.btn:hover{

background:#0E7490;

}

</style>

</head>

<body class="text-white">

<?php include "../includes/navbar.php"; ?>

<div class="max-w-screen-2xl mx-auto px-8 py-8">

<div class="flex justify-between items-center">

<div>

<h1 class="text-5xl font-bold">

🤖 Tentukan Waktu

</h1>

<p class="text-gray-300 mt-3">

Analisis seluruh agenda anggota group.

</p>

</div>

<a
href="group.php?id=<?= (int)$group_id ?>"
class="btn">

← Kembali ke Group

</a>

</div>

<div class="grid grid-cols-4 gap-6 mt-10">

<div class="card text-center">

<div class="text-5xl">

👥

</div>

<div class="text-4xl mt-4">

<?= $totalMember ?>

</div>

<div class="text-gray-300 mt-2">

Member

</div>

</div>

<div class="card text-center">

<div class="text-5xl">

📅

</div>

<div class="text-4xl mt-4">

<?= $totalAgenda ?>

</div>

<div class="text-gray-300 mt-2">

Agenda

</div>

</div>

<div class="card text-center">

<div class="text-5xl">

📂

</div>

<div class="text-2xl mt-4">

<?= htmlspecialchars($group['nama_group'] ?? "-") ?>

</div>

<div class="text-gray-300 mt-2">

Group

</div>

</div>

<div class="card text-center">

<div class="text-5xl">

🤖

</div>

<div class="text-2xl mt-4">

Ready

</div>

<div class="text-gray-300 mt-2">

Analisis

</div>

</div>

</div>

<div class="grid grid-cols-12 gap-8 mt-8">

<div class="col-span-4">

<div class="card">

<h2 class="title text-2xl">

👥 Member Group

</h2>

<?php

mysqli_data_seek($members,0);

while($m=mysqli_fetch_assoc($members)){

?>

<div class="flex items-center gap-4 bg-[#31475E] rounded-xl p-4 mt-4">

<div
class="w-12 h-12 rounded-full bg-cyan-600 flex items-center justify-center font-bold">

<?= htmlspecialchars(strtoupper(substr($m['nama'],0,1))); ?>

</div>

<div>

<?= htmlspecialchars($m['nama']); ?>

</div>

</div>

<?php } ?>

</div>

</div>

<div class="col-span-8">

<div class="card">

<h2 class="title text-2xl">

📅 Data Agenda

</h2>

<div class="mt-6">

Semua agenda anggota akan dianalisis
untuk menentukan waktu terbaik.

</div>

</div>

</div>

</div>

<div class="card mt-8">

<h2 class="title text-3xl">

🤖 AI Recommendation

</h2>

<div class="mt-6 space-y-5">

<?php

foreach($hasilRekomendasi as $row){

    if($row['score']>=100){

        $bintang="★★★★★";
        $warna="text-green-400";
        $status="Sangat Direkomendasikan";

    }elseif($row['score']>=80){

        $bintang="★★★★☆";
        $warna="text-cyan-400";
        $status="Direkomendasikan";

    }elseif($row['score']>=60){

        $bintang="★★★☆☆";
        $warna="text-yellow-400";
        $status="Cukup Baik";

    }else{

        $bintang="★★☆☆☆";
        $warna="text-red-400";
        $status="Kurang Direkomendasikan";

    }

?>

<div class="bg-[#31475E] rounded-xl p-5">

<div class="flex justify-between items-center">

<div>

<div class="text-2xl font-bold">

<?= date("d F Y",strtotime($row['tanggal'])) ?>

</div>

<div class="<?= $warna ?> mt-2">

<?= $bintang ?>

</div>

</div>

<div class="text-right">

<div>

Score

<b>

<?= $row['score'] ?>%

</b>

</div>

<div class="text-gray-300">

<?= $status ?>

</div>

</div>

</div>

<div class="mt-4">

👥 Kosong :

<b>

<?= $row['kosong'] ?>

</b>

|

🔴 Sibuk :

<b>

<?= $row['sibuk'] ?>

</b>

</div>

</div>

<?php } ?>

</div>

</div>

<h2 class="title text-2xl mb-6">

📅 Data Agenda yang Dianalisis

</h2>

<?php

mysqli_data_seek($agenda,0);

if(mysqli_num_rows($agenda)==0){

?>

<div class="bg-[#31475E] rounded-xl p-6 text-center">

Belum ada agenda pada group ini.

</div>

<?php

}else{

?>

<div class="overflow-x-auto">

<table class="w-full">

<thead>

<tr class="border-b border-gray-600">

<th class="text-left py-3">Tanggal</th>

<th class="text-left py-3">Member</th>

<th class="text-left py-3">Jam</th>

<th class="text-left py-3">Status</th>

<th class="text-left py-3">Keterangan</th>

</tr>

</thead>

<tbody>

<?php

while($a=mysqli_fetch_assoc($agenda)){

?>

<tr class="border-b border-[#31475E]">

<td class="py-3">

<?= date("d M Y",strtotime($a['tanggal'])) ?>

</td>

<td>

<?= htmlspecialchars($a['nama']) ?>

</td>

<td>

<?= substr($a['jam_mulai'],0,5) ?>

-

<?= substr($a['jam_selesai'],0,5) ?>

</td>

<td>

<?php

if($a['status']=="Kosong"){

?>

<span class="text-green-400 font-bold">

🟢 Kosong

</span>

<?php

}else{

?>

<span class="text-red-400 font-bold">

🔴 Sibuk

</span>

<?php } ?>

</td>

<td>

<?= htmlspecialchars($a['keterangan'] ?? '') ?>

</td>

</tr>

<?php } ?>

</tbody>

</table>

</div>

<?php } ?>

<div class="card mt-8">

<h2 class="title text-3xl mb-6">

🏆 Ranking Tanggal Terbaik

</h2>

<div class="space-y-4">

<?php

$ranking = $hasilRekomendasi;

usort($ranking,function($a,$b){

return $b['score'] <=> $a['score'];

});

$no=1;

foreach($ranking as $r){

?>

<div class="bg-[#31475E] rounded-xl p-5 flex justify-between items-center">

<div>

<div class="text-xl font-bold">

#<?= $no ?>

<?= date("d F Y",strtotime($r['tanggal'])) ?>

</div>

<div class="text-gray-300 mt-2">

<?= $r['kosong'] ?> Member Kosong

|

<?= $r['sibuk'] ?> Member Sibuk

</div>

</div>

<div class="text-right">

<div class="text-3xl font-bold text-cyan-400">

<?= $r['score'] ?>%

</div>

</div>

</div>

<?php

$no++;

}

?>

</div>

</div>


<div class="card mt-8">

<h2 class="title text-3xl mb-6">

📅 Hari Kosong Bersama

</h2>

<div class="grid grid-cols-3 gap-5">

<?php

foreach($hasilRekomendasi as $r){

if($r['score']==100){

?>

<div class="bg-green-600 rounded-xl p-5 text-center">

<div class="text-2xl font-bold">

<?= date("d M Y",strtotime($r['tanggal'])) ?>

</div>

<div class="mt-3">

Semua Member Kosong

</div>

</div>

<?php

}

}

?>

</div>

</div>

<?php

$libur=[];

$temp=[];

$rankingTanggal=$hasilRekomendasi;

usort($rankingTanggal,function($a,$b){

return strcmp($a['tanggal'],$b['tanggal']);

});

foreach($rankingTanggal as $r){

if($r['score']==100){

$temp[]=$r['tanggal'];

}else{

if(count($temp)>=2){

$libur[]=$temp;

}

$temp=[];

}

}

if(count($temp)>=2){

$libur[]=$temp;

}

?>

<div class="card mt-8">

<h2 class="title text-3xl mb-6">

🏖 Libur Panjang

</h2>

<?php

if(count($libur)==0){

?>

<div class="bg-[#31475E] rounded-xl p-6 text-center">

Belum ditemukan libur panjang.

</div>

<?php

}else{

foreach($libur as $l){

?>

<div class="bg-[#31475E] rounded-xl p-5 mb-4">

<div class="text-2xl font-bold">

<?= date("d M Y",strtotime(reset($l))) ?>

-

<?= date("d M Y",strtotime(end($l))) ?>

</div>

<div class="text-gray-300 mt-2">

<?= count($l) ?> Hari Berturut-turut

</div>

</div>

<?php

}

}

?>

</div>

</div>

<link href="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.11/index.global.min.css" rel="stylesheet">

<div class="card mt-8">

<h2 class="title text-3xl mb-6">

📅 Kalender Rekomendasi

</h2>

<div id="calendarRekomendasi"
class="bg-white rounded-xl p-5 text-black">

</div>

</div>

<div class="card mt-8">

<h2 class="title text-3xl mb-6">

📊 Statistik Analisis

</h2>

<canvas id="chartAnalisis" height="120"></canvas>

</div>

<?php

$terbaik=$ranking[0];

?>

<div class="card mt-8">

<h2 class="title text-3xl mb-6">

🤖 Kesimpulan AI

</h2>

<div class="bg-[#31475E] rounded-xl p-8">

<div class="text-4xl font-bold text-green-400">

<?= date("d F Y",strtotime($terbaik['tanggal'])) ?>

</div>

<div class="mt-5 text-xl">

Website merekomendasikan tanggal di atas
karena memiliki tingkat ketersediaan
anggota paling tinggi.

</div>

<div class="grid grid-cols-3 gap-6 mt-8">

<div>

<div class="text-4xl font-bold">

<?= $terbaik['score'] ?>%

</div>

<div class="text-gray-300">

Score

</div>

</div>

<div>

<div class="text-4xl font-bold">

<?= $terbaik['kosong'] ?>

</div>

<div class="text-gray-300">

Member Kosong

</div>

</div>

<div>

<div class="text-4xl font-bold">

<?= $terbaik['sibuk'] ?>

</div>

<div class="text-gray-300">

Member Sibuk

</div>

</div>

</div>

</div>

</div>

<div class="flex justify-center gap-6 mt-10">

<a
href="group.php?id=<?= $group_id ?>"
class="bg-gray-600 hover:bg-gray-700 px-8 py-4 rounded-xl">

← Kembali ke Group

</a>

<button
onclick="window.print()"
class="bg-cyan-600 hover:bg-cyan-700 px-8 py-4 rounded-xl">

🖨 Cetak Hasil

</button>

</div>

<script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.11/index.global.min.js"></script>

<script>

document.addEventListener("DOMContentLoaded",function(){

const calendar=new FullCalendar.Calendar(

document.getElementById("calendarRekomendasi"),

{

initialView:"dayGridMonth",

height:650,

headerToolbar:{

left:"prev,next today",

center:"title",

right:"dayGridMonth,timeGridWeek"

},

events:[

<?php

foreach($hasilRekomendasi as $r){

if($r['score']>=100){

$color="#22c55e";

}elseif($r['score']>=80){

$color="#06b6d4";

}elseif($r['score']>=60){

$color="#facc15";

}else{

$color="#ef4444";

}

?>

{

title:"<?= $r['score'] ?>%",

start:"<?= $r['tanggal'] ?>",

color:"<?= $color ?>"

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

const ctx=document.getElementById("chartAnalisis");

new Chart(ctx,{

type:"bar",

data:{

labels:[

<?php

foreach($ranking as $r){

echo "'".date("d M",strtotime($r['tanggal']))."',";

}

?>

],

datasets:[{

label:"Score",

data:[

<?php

foreach($ranking as $r){

echo $r['score'].",";

}

?>

],

backgroundColor:"#06b6d4"

}]

},

options:{

responsive:true,

plugins:{

legend:{

labels:{

color:"white"

}

}

},

scales:{

x:{

ticks:{

color:"white"

}

},

y:{

ticks:{

color:"white"

},

beginAtZero:true,

max:100

}

}

}

});

</script>

</body>

</html>