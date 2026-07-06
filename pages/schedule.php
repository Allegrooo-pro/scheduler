<?php
session_start();
include "../config/koneksi.php";

if (!isset($_SESSION['login'])) {
    header("Location:../index.php");
    exit;
}

$user_id = $_SESSION['id'];
$nama    = $_SESSION['nama'];

/* ======================================================
   DATA USER
====================================================== */

$stmt = mysqli_prepare($conn,"SELECT * FROM users WHERE id=?");
mysqli_stmt_bind_param($stmt,"i",$user_id);
mysqli_stmt_execute($stmt);
$user = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));

/* ======================================================
   STATISTIK
====================================================== */

$stmt = mysqli_prepare($conn,"SELECT COUNT(*) FROM agenda WHERE user_id=?");
mysqli_stmt_bind_param($stmt,"i",$user_id);
mysqli_stmt_execute($stmt);
$totalAgenda = mysqli_fetch_row(mysqli_stmt_get_result($stmt))[0];

$stmt = mysqli_prepare($conn,"SELECT COUNT(*) FROM agenda WHERE user_id=? AND status='Kosong'");
mysqli_stmt_bind_param($stmt,"i",$user_id);
mysqli_stmt_execute($stmt);
$totalKosong = mysqli_fetch_row(mysqli_stmt_get_result($stmt))[0];

$stmt = mysqli_prepare($conn,"SELECT COUNT(*) FROM agenda WHERE user_id=? AND status='Sibuk'");
mysqli_stmt_bind_param($stmt,"i",$user_id);
mysqli_stmt_execute($stmt);
$totalSibuk = mysqli_fetch_row(mysqli_stmt_get_result($stmt))[0];

/* ======================================================
   AGENDA HARI INI
====================================================== */

$today=date("Y-m-d");

$stmt = mysqli_prepare($conn,"
SELECT *
FROM agenda
WHERE
user_id=?
AND tanggal=?
ORDER BY jam_mulai
");
mysqli_stmt_bind_param($stmt,"is",$user_id,$today);
mysqli_stmt_execute($stmt);
$agendaHariIniRows = mysqli_fetch_all(mysqli_stmt_get_result($stmt), MYSQLI_ASSOC);

/* ======================================================
   SEMUA AGENDA
====================================================== */

$stmt = mysqli_prepare($conn,"
SELECT *
FROM agenda
WHERE user_id=?
ORDER BY tanggal,jam_mulai
");
mysqli_stmt_bind_param($stmt,"i",$user_id);
mysqli_stmt_execute($stmt);
$agendaResult = mysqli_stmt_get_result($stmt);

// Fetch ke array supaya bisa dipakai berkali-kali
// (mysqli_data_seek tidak work pada prepared statement result)
$agendaRows = [];
while($row = mysqli_fetch_assoc($agendaResult)){
    $agendaRows[] = $row;
}
$totalAgendaRows = count($agendaRows);

/* ======================================================
   REMINDER
====================================================== */

$stmt = mysqli_prepare($conn,"
SELECT *
FROM agenda
WHERE
user_id=?
AND tanggal>=CURDATE()
ORDER BY tanggal,jam_mulai
LIMIT 5
");
mysqli_stmt_bind_param($stmt,"i",$user_id);
mysqli_stmt_execute($stmt);
$reminderRows = mysqli_fetch_all(mysqli_stmt_get_result($stmt), MYSQLI_ASSOC);

/* ======================================================
   CATATAN (NOTE) TERAKHIR MILIK USER
   (FIX: sebelumnya $note tidak pernah diambil dari
   database, jadi textarea Catatan selalu kosong)
====================================================== */

$stmt = mysqli_prepare($conn,"
SELECT *
FROM notes
WHERE user_id=?
ORDER BY id DESC
LIMIT 1
");
mysqli_stmt_bind_param($stmt,"i",$user_id);
mysqli_stmt_execute($stmt);
$note = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
?>

<!DOCTYPE html>

<html lang="id">

<head>

<meta charset="UTF-8">

<meta
name="viewport"
content="width=device-width, initial-scale=1.0">

<title>Schedule</title>

<script src="https://cdn.tailwindcss.com"></script>

<link
href="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.11/index.global.min.css"
rel="stylesheet">

<script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.11/index.global.min.js"></script>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<style>

body{

background:#25364A;

}

.card{

background:#1F2A3A;

border-radius:22px;

padding:24px;

box-shadow:0 20px 35px rgba(0,0,0,.25);

transition:.3s;

}

.card:hover{

transform:translateY(-4px);

}

.title{

color:#22D3EE;

font-weight:bold;

}

.input{

width:100%;

padding:12px;

border-radius:12px;

background:#31475E;

color:white;

margin-top:8px;

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

<!-- =====================================================
HEADER
===================================================== -->

<div class="flex justify-between items-center">

<div>

<h1 class="text-5xl font-bold">

📅 Schedule Saya

</h1>

<p class="text-gray-300 mt-3 text-lg">

Halo,

<b class="text-cyan-400">

<?= htmlspecialchars($nama) ?>

</b>

Kelola seluruh agenda pribadi Anda.

</p>

</div>

<div class="flex gap-4">

<button

id="btnTambah"

class="btn">

+ Tambah Agenda

</button>

<a

href="tentukan_waktu.php"

class="bg-green-600 px-6 py-3 rounded-xl">

Cari Waktu

</a>

</div>

</div>

<!-- =====================================================
RINGKASAN
===================================================== -->

<div class="grid grid-cols-4 gap-6 mt-10">

<div class="card text-center">

<div class="text-5xl">

📅

</div>

<div class="text-4xl mt-4 font-bold">

<?= $totalAgenda ?>

</div>

<div class="text-gray-300 mt-2">

Total Agenda

</div>

</div>

<div class="card text-center">

<div class="text-5xl">

🟢

</div>

<div class="text-4xl mt-4 font-bold">

<?= $totalKosong ?>

</div>

<div class="text-gray-300 mt-2">

Hari Kosong

</div>

</div>

<div class="card text-center">

<div class="text-5xl">

🔴

</div>

<div class="text-4xl mt-4 font-bold">

<?= $totalSibuk ?>

</div>

<div class="text-gray-300 mt-2">

Hari Sibuk

</div>

</div>

<div class="card text-center">

<div class="text-5xl">

👥

</div>

<div class="text-4xl mt-4 font-bold">

<?= count($agendaHariIniRows) ?>

</div>

<div class="text-gray-300 mt-2">

Agenda Hari Ini

</div>

</div>

</div> <!-- tutup grid ringkasan -->
<!-- =====================================================
WORKSPACE
===================================================== -->

<div class="grid grid-cols-12 gap-8 mt-8">

    <!-- ===========================
         KALENDER
    ============================ -->

    <div class="col-span-8">

        <div class="card">

            <div class="flex justify-between items-center mb-5">

                <h2 class="title text-2xl">

                    📅 Kalender Agenda

                </h2>

                <div class="text-gray-300">

                    Semua Jadwal

                </div>

            </div>

<div
id="calendar"
class="bg-white rounded-xl p-4 text-black min-h-[650px]">
</div>

        </div>

    </div>

    <!-- ===========================
         AGENDA HARI INI
    ============================ -->

    <div class="col-span-4">

        <div class="card">

            <h2 class="title text-2xl mb-5">

                📌 Agenda Hari Ini

            </h2>

            <?php

            if(count($agendaHariIniRows)==0){

            ?>

                <div class="bg-[#31475E] rounded-xl p-6 text-center">

                    Tidak ada agenda hari ini.

                </div>

            <?php

            }

            

            foreach($agendaHariIniRows as $a){

            ?>

            <div class="bg-[#31475E] rounded-xl p-5 mb-4">

                <div class="flex justify-between">

                    <div class="font-bold text-lg">

                        <?= substr($a['jam_mulai'],0,5); ?>

                    </div>

                    <span class="<?= ($a['status']=="Kosong") ? 'text-green-400' : 'text-red-400'; ?>">

                        ● <?= $a['status']; ?>

                    </span>

                </div>

                <div class="mt-3">

                    <?= htmlspecialchars($a['keterangan']); ?>

                </div>

                <div class="text-sm text-gray-300 mt-3">

                    <?= substr($a['jam_mulai'],0,5); ?>

                    -

                    <?= substr($a['jam_selesai'],0,5); ?>

                </div>

            </div>

            <?php } ?>

        </div>

        <!-- ===========================
             REMINDER
        ============================ -->

        <div class="card mt-6">

            <h2 class="title text-2xl mb-5">

                🔔 Reminder

            </h2>

            <?php

            if(count($reminderRows)==0){

            ?>

            <div class="bg-[#31475E] rounded-xl p-4">

                Tidak ada reminder.

            </div>

            <?php

            }

            

            foreach($reminderRows as $r){

            ?>

            <div class="bg-[#31475E] rounded-xl p-4 mb-3">

                <div class="font-bold">

                    <?= date("d M Y",strtotime($r['tanggal'])); ?>

                </div>

                <div class="text-gray-300 mt-2">

                    <?= htmlspecialchars($r['keterangan']); ?>

                </div>

                <div class="text-cyan-300 mt-2">

                    <?= substr($r['jam_mulai'],0,5); ?>

                    -

                    <?= substr($r['jam_selesai'],0,5); ?>

                </div>

            </div>

            <?php } ?>

        </div>

    </div>

</div>

<!-- =====================================================
MODAL TAMBAH AGENDA
===================================================== -->

<div
id="modalAgenda"
class="hidden fixed inset-0 bg-black/60 flex justify-center items-center z-50">

<div class="bg-[#1F2A3A] rounded-2xl w-[650px] p-8">

<h2 class="text-3xl title mb-8">

Tambah Agenda

</h2>

<form
action="../api/tambah_agenda.php"
method="POST">

<label>Tanggal</label>

<input
type="date"
name="tanggal"
required
class="input">

<div class="grid grid-cols-2 gap-5 mt-5">

<div>

<label>Jam Mulai</label>

<input
type="time"
name="jam_mulai"
required
class="input">

</div>

<div>

<label>Jam Selesai</label>

<input
type="time"
name="jam_selesai"
required
class="input">

</div>

</div>

<label class="block mt-5">

Status

</label>

<select
name="status"
class="input">

<option value="Kosong">

Kosong

</option>

<option value="Sibuk">

Sibuk

</option>

</select>

<label class="block mt-5">

Keterangan

</label>

<textarea
name="keterangan"
rows="5"
class="input"></textarea>

<div class="flex justify-end gap-5 mt-8">

<button
type="button"
id="closeModal"
class="bg-gray-600 px-6 py-3 rounded-xl">

Batal

</button>

<button
name="simpan"
class="btn">

Simpan Agenda

</button>

</div>

</form>

</div>

</div>

<!-- =====================================================
TIMELINE AGENDA
===================================================== -->

<div class="card mt-8">

    <div class="flex justify-between items-center mb-6">

        <h2 class="title text-3xl">

            🕒 Timeline Agenda

        </h2>

        <div class="text-gray-300">

            Urutan aktivitas berdasarkan jam

        </div>

    </div>

    <?php

    

    if(count($agendaRows)==0){

    ?>

    <div class="bg-[#31475E] rounded-xl p-8 text-center">

        Belum ada agenda.

    </div>

    <?php

    }

    foreach($agendaRows as $a){

    ?>

    <div class="flex gap-8 mb-6">

        <!-- Jam -->

        <div class="w-40 text-center">

            <div class="text-cyan-400 text-3xl font-bold">

                <?= substr($a['jam_mulai'],0,5); ?>

            </div>

            <div class="text-gray-400 mt-2">

                <?= date("d M Y",strtotime($a['tanggal'])); ?>

            </div>

        </div>

        <!-- Garis -->

        <div class="w-1 bg-cyan-600 rounded"></div>

        <!-- Isi -->

        <div class="flex-1 bg-[#25364A] rounded-xl p-6">

            <div class="flex justify-between">

                <div>

                    <div class="text-2xl font-bold">

                        <?= htmlspecialchars($a['keterangan']); ?>

                    </div>

                    <div class="text-gray-400 mt-3">

                        <?= substr($a['jam_mulai'],0,5); ?>

                        -

                        <?= substr($a['jam_selesai'],0,5); ?>

                    </div>

                </div>

                <div>

                    <?php

                    if($a['status']=="Kosong"){

                    ?>

                    <span class="text-green-500 font-bold">

                        ● Kosong

                    </span>

                    <?php

                    }else{

                    ?>

                    <span class="text-red-500 font-bold">

                        ● Sibuk

                    </span>

                    <?php } ?>

                </div>

            </div>

        </div>

    </div>

    <?php } ?>

</div>

<!-- =====================================================
DAFTAR AGENDA
===================================================== -->

<div class="card mt-8">

    <h2 class="title text-3xl mb-6">

        📄 Daftar Agenda Saya

    </h2>

    <div class="overflow-x-auto">

        <table class="w-full">

            <thead>

                <tr class="text-left bg-[#31475E]">

                    <th class="p-4">Tanggal</th>

                    <th class="p-4">Jam</th>

                    <th class="p-4">Status</th>

                    <th class="p-4">Keterangan</th>

                    <th class="p-4 text-center">Aksi</th>

                </tr>

            </thead>

            <tbody>

            <?php

            

            foreach($agendaRows as $a){

            ?>

            <tr class="border-b border-[#31475E]">

                <td class="p-4">

                    <?= date("d M Y",strtotime($a['tanggal'])); ?>

                </td>

                <td class="p-4">

                    <?= substr($a['jam_mulai'],0,5); ?>

                    -

                    <?= substr($a['jam_selesai'],0,5); ?>

                </td>

                <td class="p-4">

                    <?php

                    if($a['status']=="Kosong"){

                        echo '<span class="text-green-500 font-bold">Kosong</span>';

                    }else{

                        echo '<span class="text-red-500 font-bold">Sibuk</span>';

                    }

                    ?>

                </td>

                <td class="p-4">

                    <?= htmlspecialchars($a['keterangan']); ?>

                </td>

                <td class="p-4 text-center">

                    <div class="flex justify-center gap-3">

                        <a
                        href="#"

                        class="editAgenda bg-yellow-500 hover:bg-yellow-600 px-5 py-2 rounded-lg"

                        data-id="<?= $a['id']; ?>"

                        data-tanggal="<?= $a['tanggal']; ?>"

                        data-mulai="<?= $a['jam_mulai']; ?>"

                        data-selesai="<?= $a['jam_selesai']; ?>"

                        data-status="<?= $a['status']; ?>"

                        data-keterangan="<?= htmlspecialchars($a['keterangan']); ?>">

                            Edit

                        </a>

                        <a
                        href="../api/hapus_agenda.php?id=<?= $a['id']; ?>"
                        onclick="return confirm('Yakin ingin menghapus agenda ini?')"
                        class="bg-red-600 hover:bg-red-700 px-5 py-2 rounded-lg">

                            Hapus

                        </a>

                    </div>

                </td>

            </tr>

            <?php } ?>

            </tbody>

        </table>

    </div>

</div>

<!-- =====================================================
STATISTIK
===================================================== -->

<div class="grid grid-cols-3 gap-6 mt-8">

    <!-- Chart -->

    <div class="card">

        <h2 class="title text-2xl mb-5">

            📊 Statistik Agenda

        </h2>

        <div style="max-height:260px; position:relative;">
        <canvas id="chartAgenda"></canvas>
        </div>

    </div>

    <!-- Catatan -->

    <div class="card">

        <div class="flex justify-between items-center mb-5">

            <h2 class="title text-2xl">

                📝 Catatan

            </h2>

        </div>

        <form action="../api/simpan_note.php" method="POST">

            <textarea

            name="note"

            rows="10"

            class="input"

            placeholder="Tulis catatan pribadi..."><?=
            isset($note['isi']) ? $note['isi'] : "";
            ?></textarea>

            <button
            class="btn mt-5">

                Simpan Catatan

            </button>

        </form>

    </div>

    <!-- Quick Action -->

    <div class="card">

        <h2 class="title text-2xl mb-5">

            ⚡ Quick Action

        </h2>

        <div class="space-y-4">

            <a

            href="#"

            id="btnTambah2"

            class="block bg-cyan-600 hover:bg-cyan-700 rounded-xl p-4 text-center">

                ➕ Tambah Agenda

            </a>

            <a

            href="group.php"

            class="block bg-indigo-600 hover:bg-indigo-700 rounded-xl p-4 text-center">

                👥 Kelola Group

            </a>

            <a

            href="tentukan_waktu.php"

            class="block bg-green-600 hover:bg-green-700 rounded-xl p-4 text-center">

                🤖 Tentukan Waktu

            </a>

        </div>

    </div>

</div>

<!-- =====================================================
TARGET MINGGU INI
===================================================== -->

<div class="card mt-8">

    <div class="flex justify-between items-center">

        <h2 class="title text-2xl">

            🎯 Target Minggu Ini

        </h2>

        <span class="text-gray-300">

            Produktivitas

        </span>

    </div>

    <div class="mt-6">

        <?php

        $persen = 0;

        if($totalAgenda>0){

            $persen = round(($totalKosong/$totalAgenda)*100);

        }

        ?>

        <div class="w-full bg-[#31475E] rounded-full h-6">

            <div

            class="bg-cyan-500 h-6 rounded-full text-center text-sm"

            style="width:<?= $persen ?>%">

                <?= $persen ?>%

            </div>

        </div>

        <div class="mt-3 text-gray-300">

            Persentase hari kosong dibanding seluruh agenda.

        </div>

    </div>

</div>

<!-- =====================================================
MODAL EDIT AGENDA
===================================================== -->

<div
id="modalEdit"
class="hidden fixed inset-0 bg-black/60 flex justify-center items-center z-50">

<div class="bg-[#1F2A3A] rounded-2xl w-[650px] p-8">

<h2 class="text-3xl title mb-8">

✏ Edit Agenda

</h2>

<form
action="../api/update_agenda.php"
method="POST">

<input
type="hidden"
name="id"
id="edit_id">

<label>Tanggal</label>

<input
type="date"
name="tanggal"
id="edit_tanggal"
class="input">

<div class="grid grid-cols-2 gap-5 mt-5">

<div>

<label>Jam Mulai</label>

<input
type="time"
name="jam_mulai"
id="edit_mulai"
class="input">

</div>

<div>

<label>Jam Selesai</label>

<input
type="time"
name="jam_selesai"
id="edit_selesai"
class="input">

</div>

</div>

<label class="block mt-5">

Status

</label>

<select
name="status"
id="edit_status"
class="input">

<option value="Kosong">

Kosong

</option>

<option value="Sibuk">

Sibuk

</option>

</select>

<label class="block mt-5">

Keterangan

</label>

<textarea
name="keterangan"
id="edit_keterangan"
rows="5"
class="input"></textarea>

<div class="flex justify-end gap-5 mt-8">

<button
type="button"
id="closeEdit"
class="bg-gray-600 px-6 py-3 rounded-xl">

Batal

</button>

<button
class="btn">

Update Agenda

</button>

</div>

</form>

</div>

</div>

</div>   <!-- penutup max-w-screen-2xl -->

<script>

window.onload=function(){

const calendar=new FullCalendar.Calendar(

document.getElementById("calendar"),

{

initialView:"dayGridMonth",

height:650,

headerToolbar:{
left:"prev,next today",
center:"title",
right:"dayGridMonth,timeGridWeek"
},

events:
<?php
$events = [];
foreach($agendaRows as $a){
    $events[] = [
        "title"  => $a['keterangan'],
        "start"  => $a['tanggal'],
        "end"    => $a['tanggal'],
        "color"  => ($a['status']=="Kosong") ? "#22c55e" : "#ef4444"
    ];
}
echo json_encode($events);
?>

}

);

calendar.render();

const modal = document.getElementById("modalAgenda");

document.getElementById("btnTambah").onclick = function(){
    modal.classList.remove("hidden");
};

document.getElementById("btnTambah2").onclick = function(e){
    e.preventDefault();
    modal.classList.remove("hidden");
};

document.getElementById("closeModal").onclick = function(){
    modal.classList.add("hidden");
};

};

</script>

<script>

document.querySelectorAll(".editAgenda").forEach(function(btn){
    btn.addEventListener("click", function(e){
        e.preventDefault();
        document.getElementById("modalEdit").classList.remove("hidden");
        document.getElementById("edit_id").value        = this.dataset.id;
        document.getElementById("edit_tanggal").value   = this.dataset.tanggal;
        document.getElementById("edit_mulai").value     = this.dataset.mulai;
        document.getElementById("edit_selesai").value   = this.dataset.selesai;
        document.getElementById("edit_status").value    = this.dataset.status;
        document.getElementById("edit_keterangan").value= this.dataset.keterangan;
    });
});

document.getElementById("closeEdit").onclick = function(){
    document.getElementById("modalEdit").classList.add("hidden");
};

</script>

<script>

const ctx = document.getElementById("chartAgenda");

if(ctx){

new Chart(ctx,{

type:"doughnut",

data:{

labels:["Kosong","Sibuk"],

datasets:[{

data:[<?= (int)$totalKosong ?>, <?= (int)$totalSibuk ?>],

backgroundColor:["#22c55e","#ef4444"]

}]

},

options:{

responsive:true,

maintainAspectRatio:true,

plugins:{

legend:{
labels:{
color:"white",
font:{size:13}
}
}

}

}

});

}

</script>

</body>

</html>