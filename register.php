<?php
include "config/koneksi.php";

$pesan = "";

if(isset($_POST['register'])){

    $nama = htmlspecialchars($_POST['nama']);
    $email = htmlspecialchars($_POST['email']);
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);

    $cek = mysqli_query($conn,"SELECT * FROM users WHERE email='$email'");

    if(mysqli_num_rows($cek)>0){

        $pesan="Email sudah digunakan";

    }else{

        mysqli_query($conn,"
            INSERT INTO users(nama,email,password)
            VALUES('$nama','$email','$password')
        ");

        header("Location:index.php");
        exit;
    }
}
?>

<!DOCTYPE html>
<html>
<head>

<meta charset="UTF-8">

<title>Register</title>

<script src="https://cdn.tailwindcss.com"></script>

</head>

<body class="bg-[#25364A]">

<div class="flex justify-center items-center h-screen">

<div class="bg-[#2E3141] w-[450px] rounded-xl p-8">

<h1 class="text-center text-cyan-400 text-4xl mb-8">
Register
</h1>

<?php
if($pesan!=""){
echo "<p class='text-red-400 mb-4'>$pesan</p>";
}
?>

<form method="POST">

<label class="text-cyan-400">
Username
</label>

<input
type="text"
name="nama"
required
class="w-full p-3 rounded bg-[#8B8CA8] mb-5 text-black">

<label class="text-cyan-400">
Email
</label>

<input
type="email"
name="email"
required
class="w-full p-3 rounded bg-[#8B8CA8] mb-5 text-black">

<label class="text-cyan-400">
Password
</label>

<input
type="password"
name="password"
required
class="w-full p-3 rounded bg-[#8B8CA8] mb-6 text-black">

<button
name="register"
class="bg-[#8B8CA8] text-cyan-700 w-full py-3 rounded hover:bg-gray-400">

Register

</button>

<p class="text-center mt-5">

<a href="index.php"
class="text-cyan-400">

Sudah punya akun?

</a>

</p>

</form>

</div>

</div>

</body>
</html>