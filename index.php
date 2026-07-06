<?php
session_start();

include "config/koneksi.php";

$error="";

if(isset($_POST['login'])){

$email=$_POST['email'];

$password=$_POST['password'];

$query=mysqli_query($conn,"
SELECT * FROM users
WHERE email='$email'
");

$user=mysqli_fetch_assoc($query);

if($user){

if(password_verify($password,$user['password'])){

$_SESSION['login']=true;

$_SESSION['id']=$user['id'];

$_SESSION['nama']=$user['nama'];

header("Location:pages/home.php");

exit;

}else{

$error="Password Salah";

}

}else{

$error="Email Tidak Ditemukan";

}

}
?>

<!DOCTYPE html>
<html>

<head>

<meta charset="UTF-8">

<title>Login</title>

<script src="https://cdn.tailwindcss.com"></script>

</head>

<body class="bg-[#25364A]">

<div class="flex justify-center items-center h-screen">

<div class="bg-[#2E3141] w-[450px] rounded-xl p-8">

<h1 class="text-center text-cyan-400 text-4xl mb-8">

Login

</h1>

<?php

if($error!=""){

echo "<p class='text-red-400'>$error</p>";

}

?>

<form method="POST">

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
name="login"
class="bg-[#8B8CA8] text-cyan-700 w-full py-3 rounded">

Login

</button>

<p class="text-center mt-5">

<a href="register.php"
class="text-cyan-400">

Belum punya akun?

</a>

</p>

</form>

</div>

</div>

</body>
</html>