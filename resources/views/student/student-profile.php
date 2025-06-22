<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= session('user_name') ?> | Turo </title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-LN+7fdVzj6u52u30Kp6M/trliBMCMKTyK833zpbD+pXdCLuTusPj697FH4R/5mcr" crossorigin="anonymous">
    <link rel="stylesheet" type="text/css" href="/css/styles.css">
    <style>
        table,
        th,
        td {
            border: 0.04em solid #C9C9C9;
            border-collapse: collapse;
        }

        table {
            width: 100%;
        }

        .table-left-padding {
            width: 2em;
        }

        .table-right-padding {
            padding: 1em 1.5em;
        }

        h5 {
            font-family: Alata, sans-serif;
        }

        .profile-master {
            width: 100%;
            display: flex;
            flex-direction: row;
            gap: 2rem;

            font-family: Alexandria, sans-serif;
        }

        .profile-image-container {
            display: flex;
            flex-direction: column;
        }

        .profile-image {
            width: 10rem;
            height: 10rem;
            background-position: center;
            background-size: cover;
            border: 0.2rem solid rgb(255, 255, 255);
            border-radius: 0.4rem;
            box-shadow: rgba(0, 0, 0, 0.24) 0rem 0.1875rem 0.5rem;
        }

        .profile-details-container {
            width: 100%;
            display: flex;
            flex-direction: column;

            color: #492C2C;
            font-size: 1rem;
            line-height: 1.2;
            margin: 0;
        }

        .profile-details-name {
            color: #492C2C;
            font-size: 1.6rem;
            margin: 0;
            line-height: 1.2;
        }

        .profile-details-email {
            color: rgb(103, 79, 79);
            font-size: 1.3rem;
            line-height: 1.2;
            margin: 0;
        }

        .profile-details-rank {
            line-height: 1.2;
            margin: 0;
        }
    </style>
</head>

<body>
    <?php
    include __DIR__ . '/../partials/nav.php';
    ?>

    <div class="home-tutor-screen">
        <div class="home-tutor-main">
            <h5>User Profile</h5>
            <hr>
            <div class="module-section quiz-background-container profile-color">
                <div class="profile-master">
                    <div class="profile-image-container">
                        <div
                            class="profile-image"
                            style="background-image: url('<?= $imageURL ?>');">
                        </div>
                        <div class="profile-image-edit">
                            Change Profile Image
                        </div>
                    </div>
                    <div class="profile-details-container">
                        <p class="profile-details-name"><?= strtoupper($users->last_name) . ', ' . strtoupper($users->first_name) ?></p>
                        <p class="profile-details-email"><?= $users->email ?></p>
                        <hr style="width: 100%; margin-top: 0.5em; margin-bottom: 0.5em;">
                        <p class="profile-details-email">Leaderboards Status</p>
                    </div>
                </div>
            </div>
        </div>
        <?php include __DIR__ . '/../partials/right-side-notifications.php';  ?>
    </div>
</body>

</html>