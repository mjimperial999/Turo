<header>
    <img src="/icons/title-logo.svg" width="120em" height="auto">
    <span style="color: white; font-family: Alexandria, sans-serif;">Administrator's Panel</span>
    <nav>
        <div class="nav__links">
            <?php if (session()->has('user_id')): ?>
                <span style="color: white; font-family: Alexandria, sans-serif;">
                    Welcome, <?= session('user_name') ?>
                </span>
            <?php endif; ?>
            <a class="nav" href="/logout">LOGOUT</a>
        </div>
    </nav>
</header>