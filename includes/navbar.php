<nav class="bg-[#2E3141] h-20 flex items-center justify-between px-10">

    <div class="flex gap-10">

        <a href="home.php" class="text-cyan-400 hover:text-white">
            HOME
        </a>

        <a href="schedule.php" class="text-cyan-400 hover:text-white">
            SCHEDULE
        </a>

        <a href="group.php" class="text-cyan-400 hover:text-white">
            GROUP
        </a>

        <a href="tentukan_waktu.php" class="text-cyan-400 hover:text-white">
            WAKTU
        </a>

    </div>

    <div class="flex items-center gap-3">

        <div class="w-12 h-12 rounded-full bg-[#8B8CA8]"></div>

        <span class="text-white">

            <?= $_SESSION['nama']; ?>

        </span>

        <a
            href="../logout.php"
            class="bg-red-500 px-4 py-2 rounded text-white">

            Logout

        </a>

    </div>

</nav>