<?php
session_start();

include "../config/koneksi.php";

if(!isset($_SESSION['login'])){
    header("Location:../index.php");
    exit;
}

$nama=$_SESSION['nama'];
?>

<!DOCTYPE html>

<html lang="id">

<head>

<meta charset="UTF-8">

<meta name="viewport"
content="width=device-width, initial-scale=1.0">

<title>Home</title>

<script src="https://cdn.tailwindcss.com"></script>

<style>

body{

background:#25364A;

}

.card{

background:#1F2A3A;

border-radius:20px;

padding:30px;

transition:.3s;

}

.card:hover{

transform:translateY(-5px);

box-shadow:0 20px 35px rgba(0,0,0,.35);

}

</style>

</head>

<body class="text-white">

<?php include "../includes/navbar.php"; ?>

<div class="max-w-screen-2xl mx-auto px-10 py-10">

<div class="grid grid-cols-2 gap-16 items-center">

<div>

<h1 class="text-6xl font-bold leading-tight">

Selamat Datang,

<span class="text-cyan-400">

<?= $nama ?>

</span>

</h1>

<p class="text-gray-300 text-xl mt-8 leading-9">

Scheduler merupakan aplikasi penjadwalan
untuk membantu pengguna mengatur agenda pribadi,
mengelola group,
serta menentukan waktu terbaik
untuk berkumpul bersama.

</p>

<div class="mt-10 flex gap-6">

<a
href="schedule.php"

class="bg-cyan-600 hover:bg-cyan-700 px-8 py-4 rounded-xl">

Mulai Sekarang

</a>

<a
href="tentukan_waktu.php"

class="bg-[#31475E] hover:bg-[#3D566F] px-8 py-4 rounded-xl">

Lihat Demo

</a>

</div>

</div>

<div>

<img

src="../assets/img/home.png"

class="w-full">

</div>

</div>

<div class="mt-24 card">

<h2 class="text-4xl text-cyan-400 font-bold">

Tentang Scheduler

</h2>

<p class="text-gray-300 text-xl leading-9 mt-8">

Scheduler membantu pengguna menentukan waktu
terbaik untuk berkumpul berdasarkan agenda
masing-masing anggota.

Website ini menyediakan pengelolaan agenda,
group,
serta rekomendasi waktu terbaik
menggunakan analisis jadwal seluruh anggota.

</p>

</div>

<div class="mt-24">

<h2 class="text-center text-4xl font-bold text-cyan-400">

Fitur Utama

</h2>

<div class="grid grid-cols-4 gap-8 mt-10">

<div class="card text-center">

<div class="text-6xl">

📅

</div>

<h3 class="text-2xl mt-5">

Schedule

</h3>

<p class="text-gray-300 mt-4">

Mengelola agenda pribadi.

</p>

</div>

<div class="card text-center">

<div class="text-6xl">

👥

</div>

<h3 class="text-2xl mt-5">

Group

</h3>

<p class="text-gray-300 mt-4">

Mengelola anggota group.

</p>

</div>

<div class="card text-center">

<div class="text-6xl">

🤖

</div>

<h3 class="text-2xl mt-5">

Rekomendasi

</h3>

<p class="text-gray-300 mt-4">

Menentukan waktu terbaik.

</p>

</div>

<div class="card text-center">

<div class="text-6xl">

📊

</div>

<h3 class="text-2xl mt-5">

Statistik

</h3>

<p class="text-gray-300 mt-4">

Visualisasi aktivitas agenda.

</p>

</div>

</div>

<div class="mt-24">

<h2 class="text-center text-4xl font-bold text-cyan-400">

Cara Menggunakan

</h2>

<div class="grid grid-cols-5 gap-5 mt-10">

<div class="card text-center">
<div class="text-6xl">1️⃣</div>
<h3 class="mt-5 text-xl">Tambah Agenda</h3>
</div>

<div class="card text-center">
<div class="text-6xl">2️⃣</div>
<h3 class="mt-5 text-xl">Buat Group</h3>
</div>

<div class="card text-center">
<div class="text-6xl">3️⃣</div>
<h3 class="mt-5 text-xl">Join Group</h3>
</div>

<div class="card text-center">
<div class="text-6xl">4️⃣</div>
<h3 class="mt-5 text-xl">Tentukan Waktu</h3>
</div>

<div class="card text-center">
<div class="text-6xl">5️⃣</div>
<h3 class="mt-5 text-xl">Mulai Bertemu</h3>
</div>

</div>

<div class="mt-24 card text-center">

<h2 class="text-5xl font-bold">

Siap Menggunakan Scheduler?

</h2>

<p class="text-gray-300 text-xl mt-6">

Mulai membuat agenda
dan temukan waktu terbaik
untuk berkumpul.

</p>

<div class="mt-10 flex justify-center gap-6">

<a

href="schedule.php"

class="bg-cyan-600 px-10 py-4 rounded-xl">

Kelola Agenda

</a>

<a

href="tentukan_waktu.php"

class="bg-[#31475E] px-10 py-4 rounded-xl">

Tentukan Waktu

</a>

</div>

</div>

<footer class="text-center mt-24 text-gray-400 border-t border-gray-700 pt-8">

<p>

© 2026 Scheduler

</p>

<p class="mt-2">

Developed by Maulana Ilham

</p>

</footer>

</div>

</body>

</html>