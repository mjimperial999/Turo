<?php
function getMimeTypeFromBlob($blob)
{
    $finfo = new finfo(FILEINFO_MIME_TYPE);
    return $finfo->buffer($blob);
}

?>

<div class="main-header">
    <a href="/admin-panel"><img src="/icons/title-logo.svg" width="120em" height="auto"></a>

    <div class="navibar-user" style="width: auto;">
        <?php if (session()->has('user_id')): ?>
            <span style="color: white; font-family: Alexandria, sans-serif;">
                [Administrator's Panel]
            </span>
        <?php endif; ?>
    </div>
    <button class="nav-toggle mobile-display-only"
        aria-controls="primary-navigation"
        aria-expanded="false">
        <div class="navibar-img" style="background-color:gold; width: 2.5em; height: 2.5em; background-size: cover; background-position: center; border-radius: 50%; cursor: pointer;">
        </div>
    </button>
    <nav id="primary-navigation">
        <div class="nav__links">
            <p class="nav">Welcome, <?= session('user_name') ?></p>
            <a class="nav" href="/admin-panel">PANEL</a>
            <a class="nav" href="/inbox">INBOX</a>
            <a class="nav" href="/admin-logout">LOGOUT</a>
        </div>
    </nav>
</div>

<?php include __DIR__ . '/../partials/flash-stack.php'; ?>