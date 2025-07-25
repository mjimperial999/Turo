<?php

use Carbon\Carbon;

$title = "Inbox – " . ($inbox->messages->last()->subject ?? '');
include __DIR__ . '/../partials/head.php'; ?>

</head>

<body>
    <?php
    $folder = $folder ?? 'inbox';
    $truncate = fn(string $txt, int $len = 60) =>
    mb_strlen($txt) > $len ? mb_substr($txt, 0, $len - 3) . '…' : $txt;

    if (session('role_id') == 1) {
        include __DIR__ . '/../partials/nav.php';
    } elseif (session('role_id') == 2) {
        include __DIR__ . '/../partials/nav-teach.php';
    } else {
        include __DIR__ . '/../partials/nav-admin.php';
    }
    ?>

    <div class="screen flex-column">
        <div class="spacing whole">
            <div class="content-container box-page">
                <div class="mini-navigation">
                    <div class="text title">
                        <h6>
                            <?php if (session('role_id') == 1): ?>
                                <a href="/home-tutor">
                                <?php elseif (session('role_id') == 2): ?>
                                    <a href="/teachers-panel">
                                    <?php else: ?>
                                        <a href="/admin-panel">
                                        <?php endif; ?>

                                        Back to Menu Page</a>
                        </h6>
                        <div class="line"></div>
                    </div>
                    <div class="divider">
                        <h6> > </h6>
                    </div>
                    <div class="text title">
                        <h6>Inbox</h6>
                        <div class="line active"></div>
                    </div>
                </div>
            </div>
        </div>

        <div class="inbox-content flex-row">
            <div class="spacing side">

                <div class="content-container box-page">

                    <div class="content padding">
                        <div class="header">
                            <div class="text title">
                                <h6>
                                    <?php if ($folder === 'sent'): ?>
                                        Sent Messages
                                    <?php else: ?>
                                        Incoming Messages
                                    <?php endif; ?>
                                </h6>
                            </div>
                        </div>
                    </div>

                    <div class="content padding">
                        <ul class="msg-list">
                            <?php if ($threads->isEmpty()): ?>
                                <li>No messages yet.</li>
                            <?php endif; ?>

                            <?php foreach ($threads as $t):

                                $latest = $t->messages->last();
                                $state  = $latest->userStates->firstWhere('user_id', session('user_id'));
                                $unread = $state && !$state->is_read;

                                /* ---------- names & label ---------- */
                                if ($folder === 'sent') {
                                    /* To: everyone except me */
                                    $name = $t->participants
                                        ->filter(fn($p) => $p->user_id !== session('user_id'))
                                        ->map(fn($p) => $p->first_name . ' ' . $p->last_name)
                                        ->implode(', ');
                                    $label = 'To: ';
                                } else {
                                    /* From: sender */
                                    $name  = $latest->sender->first_name . ' ' . $latest->sender->last_name;
                                    $label = 'From: ';
                                }

                                $stamp = date('F j, Y, g:i A', $latest->timestamp);    // e.g. July 8, 2025, 1:09 PM
                            ?>
                                <li class="msg-item <?= $unread ? 'unread' : 'read'; ?>">
                                    <hr class="msg-hr">
                                    <a href="<?= route('inbox.show', ['inbox' => $t, 'folder' => $folder ?? 'inbox']) ?>"
                                        class="msg-link <?= $active ?? '' ?>">
                                        <span class="msg-from">
                                            <?= $unread ? '* ' : ''; ?><b><?= $label . htmlspecialchars($name) ?></b>
                                        </span>
                                        <small class="msg-time"><b><?= $latest->subject ?? '(no subject)' ?></b></small>
                                        <small class="msg-time"><?= $stamp ?></small>
                                        <small class="msg-prev">"<?= htmlspecialchars($truncate($latest->body)) ?>"</small>
                                    </a>
                                    <hr class="msg-hr">
                                </li>
                            <?php endforeach; ?>
                        </ul>

                    </div>

                    <div class="content padding box-page">
                        <button class="btn btn-primary" onclick="openCompose()">
                            + New Inbox
                        </button>
                    </div>

                </div>

            </div>

            <div class="spacing main">

                <div class="content-container box-gray">

                    <div class="content padding flex-row" style="align-items: center; gap: 0.5rem;">
                        <form action="/inbox" method="GET">
                            <button class="self-button" <?= ($folder ?? 'inbox') === 'inbox' ? 'disabled' : '' ?>>All Inboxes</button>
                        </form>

                        <form action="/inbox/sent" method="GET">
                            <button class="self-button" <?= ($folder ?? 'inbox') === 'sent' ? 'disabled' : '' ?>>All Sent</button>
                        </form>
                        <!-- “btn” is assumed in your global CSS; otherwise style as you like -->
                    </div>

                </div>

                <div class="content-container box-page">

                    <div class="content padding">
                        <!-- message list -->
                        <div class="msg-wrapper">
                            <?php foreach ($inbox->messages as $m):
                                $state  = $m->userStates->firstWhere('user_id', session('user_id'));
                                $unread = $state && !$state->is_read;
                                $isSent = $m->sender_id === session('user_id');

                                /* build From / To label + avatar */
                                if ($isSent) {
                                    $names = 'To: ' . $inbox->participants
                                        ->filter(fn($p) => $p->user_id !== session('user_id'))
                                        ->map(fn($p) => $p->first_name . ' ' . $p->last_name)
                                        ->implode(', ');
                                    if (empty($m->sender->image?->image)) {;
                                        $imageURL = "/icons/no-img.jpg";
                                    } else {
                                        $blobData = $m->sender->image?->image;
                                        $mimeType = getMimeTypeFromBlob($blobData);
                                        $base64Image = base64_encode($blobData);
                                        $imageURL = "data:$mimeType;base64,$base64Image";
                                    }
                                } else {
                                    $names = ($unread ? '* ' : '') . 'From: '
                                        . $m->sender->first_name . ' ' . $m->sender->last_name;
                                    if (empty($m->sender->image?->image)) {;
                                        $imageURL = "/icons/no-img.jpg";
                                    } else {
                                        $blobData = $m->sender->image?->image;
                                        $mimeType = getMimeTypeFromBlob($blobData);
                                        $base64Image = base64_encode($blobData);
                                        $imageURL = "data:$mimeType;base64,$base64Image";
                                    }
                                }

                                $elapsed = \Carbon\Carbon::createFromTimestamp($m->timestamp)->diffForHumans();   /* “2 hours ago” */  /* :contentReference[oaicite:0]{index=0} */
                                $stamp   = date('F j, Y, g:i A', $m->timestamp);                                   /* Jul 8, 2025 1:09 PM */  /* :contentReference[oaicite:1]{index=1} */
                            ?>
                                <div class="msg-card <?= $unread ? 'unread' : 'read'; ?>">
                                    <!-- top row -->
                                    <div class="msg-head">
                                        <div class="msg-info">
                                            <img src="<?= htmlspecialchars($imageURL) ?>" alt="avatar" class="msg-avatar">
                                            <span class="msg-names"><?= htmlspecialchars($names) ?></span>
                                        </div>

                                        <div class="flex-row" style="gap: 1rem;">
                                            <div class="msg-timewrap">
                                                <?= htmlspecialchars($elapsed) ?><time><?= $stamp ?></time>
                                            </div>
                                            <?php if ($m->sender_id === session('user_id')): ?>
                                                <form action="<?= route('message.destroy', ['message' => $m->message_id]) ?>"
                                                    method="POST"
                                                    style="display:inline"
                                                    onsubmit="return confirm('Delete this message?');">
                                                    <?= csrf_field(); ?>
                                                    <button type="submit" class="btn btn-danger">Delete</button>
                                                </form>
                                            <?php else: ?>
                                                <form action="<?= route('message.toggleRead', $m); ?>" method="POST">
                                                    <?= csrf_field(); ?><input type="hidden" name="_method" value="PATCH">
                                                    <button class="btn btn-primary">
                                                        <?= $state && $state->is_read ? 'Mark Unread' : 'Mark Read' ?>
                                                    </button>
                                                </form>
                                            <?php endif; ?>
                                        </div>
                                    </div>

                                    <hr class="msg-hr">

                                    <!-- subject + body -->
                                    <?php if ($m->subject): ?>
                                        <p class="msg-subject"><?= htmlspecialchars($m->subject) ?></p>
                                    <?php endif; ?>
                                    <p class="msg-body"><?= nl2br(htmlspecialchars($m->body)) ?></p>

                                    <hr class="msg-hr">
                                </div>
                            <?php endforeach; ?>
                        </div>

                        <!-- ========== QUICK REPLY ========== -->
                        <form action="<?= route('inbox.reply', $inbox); ?>" method="POST" style="border-top:1px solid #ccc;padding-top:.5rem">
                            <?= csrf_field(); ?>
                            <div style="display:flex;gap:.5rem">
                                <textarea name="body" rows="3" style="flex:1"></textarea>
                            </div>
                            <button class="btn btn-primary">Send</button>
                        </form>
                    </div>

                </div>

            </div>
        </div>


    </div>

    <?php include __DIR__ . '/../inbox/modal-compose.php'; ?>
    <?php include __DIR__ . '/../partials/footer.php'; ?>
</body>
<script>
    // concat ID to input (keeps commas tidy, no duplicates)
    function addID(id) {
        const field = document.getElementById('participantField');
        const ids = field.value.split(',').map(s => s.trim()).filter(Boolean);
        if (!ids.includes(id)) ids.push(id);
        field.value = ids.join(', ');
    }
</script>

</html>