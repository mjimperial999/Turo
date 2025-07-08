<div id="flash-stack">
    <?php if (session()->has('error')): ?>
        <div class="alert alert-danger alert-message">
            <?= session('error') ?>
        </div>
    <?php elseif (session()->has('success')): ?>
        <div class="alert alert-success alert-message">
            <?= session('success') ?>
        </div>
    <?php endif; ?>
    <?php if (session()->has('ach_flash')): ?>
        <?php foreach (session('ach_flash') as $msg): ?>
            <div class="alert alert-message alert-achievement"><?= $msg ?></div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>