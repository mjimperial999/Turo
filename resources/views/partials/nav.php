<?php
function getMimeTypeFromBlob($blob)
{
    $finfo = new finfo(FILEINFO_MIME_TYPE);
    return $finfo->buffer($blob);
}

$studentId = \App\Models\Students::find(session('user_id'));
$isCatchUp = false;
if (session('role_id') == 1){
  $isCatchUp = ($studentId->isCatchUp == 1);
  $isLocked  = ($studentId->isCatchUp == 0) && session('role_id') == 1;
} else {
  $isCatchUp = false;
  $isLocked = false;
}
?>

<div class="main-header">
    <div class="mobile-display-only"></div>
    <a href="/home-tutor"><img src="/icons/title-logo.svg" width="120em" height="auto"></a>
    <?php
    if (empty($users->image?->image)) {;
        $imageURL = "/icons/no-img.jpg";
    } else {
        $blobData = $users->image?->image;
        $mimeType = getMimeTypeFromBlob($blobData);
        $base64Image = base64_encode($blobData);
        $imageURL = "data:$mimeType;base64,$base64Image";
    }
    ?>
    <div class="navibar-user mobile-display-disappear">
        <div class="navibar-img" style="background-image: url('<?= $imageURL ?>'); width: 2.5em; height: 2.5em; background-size: cover; background-position: center; border-radius: 50%; cursor: pointer;">
        </div>
        <?php if (session()->has('user_id')): ?>
            <span style="color: white; font-family: Alexandria, sans-serif;">
                Welcome, <?= session('user_name') ?>
            </span>
            <?php if ($isCatchUp): ?>
                <span style="color: white; font-family: Alexandria, sans-serif;">
                    [Catch-Up Mode]
                </span>  
            <?php endif; ?>  
        <?php endif; ?>
    </div>
    <button class="nav-toggle mobile-display-only"
        aria-controls="primary-navigation"
        aria-expanded="false">
        <div class="navibar-img" style="background-image: url('<?= $imageURL ?>'); width: 2.5em; height: 2.5em; background-size: cover; background-position: center; border-radius: 50%; cursor: pointer;">
        </div>
    </button>
    <nav id="primary-navigation">
        <div class="nav__links">
            <a class="nav" href="/home-tutor">COURSES</a>
            <a class="nav" href="/profile">PROFILE</a>
            <a class="nav" href="/performance">PERFORMANCE</a>
            <a class="nav" href="/leaderboards">LEADERBOARDS</a>
            <a class="nav" href="/inbox">INBOX</a>
            <a class="nav" href="/logout">LOGOUT</a>
        </div>
    </nav>
</div>

<?php include __DIR__ . '/../partials/flash-stack.php'; ?>