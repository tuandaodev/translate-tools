<style>
    body {
        padding-top: 56px;
    }
</style>
<nav class="navbar navbar-expand-lg navbar-dark bg-dark fixed-top">
    <div class="container">
        <a class="navbar-brand" href="#">Translate - SRT</a>
        <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarResponsive" aria-controls="navbarResponsive" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarResponsive">
            <ul class="navbar-nav ml-auto">
                <li class="nav-item <?php if ($page == 'translate') echo 'active' ?>">
                    <a class="nav-link" href="./index.php">Translate</a>
                </li>
                <li class="nav-item <?php if ($page == 'srt') echo 'active' ?>">
                    <a class="nav-link" href="./sub.php">SRT Sub</a>
                </li>
                <li class="nav-item <?php if ($page == 'srt-time') echo 'active' ?>">
                    <a class="nav-link" href="./srt-time.php">SRT Time</a>
                </li>
            </ul>
        </div>
    </div>
</nav>