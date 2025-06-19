<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    
    <title>Screening | Turo</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-LN+7fdVzj6u52u30Kp6M/trliBMCMKTyK833zpbD+pXdCLuTusPj697FH4R/5mcr" crossorigin="anonymous">
    <link rel="stylesheet" type="text/css" href="css/styles.css">
    <style>
        html,
        body {
            width: 100%;
            height: 100%;
            margin: 0 auto;
        }

        .home-tutor-screen {
            width: 100%;
            min-height: 100em;
            padding: 1em 2% 0 2%;

            display: flex;
        }

        .home-tutor-main {
            width: 75%;
            min-height: 100em;
            padding: 0 1%;

            display: flex;
        }

        h4 {
            font-family: Alata, sans-serif;
        }

    </style>
</head>

<body>
    <header>
        <img src="icons/title-logo.png" width="120em" height="auto">
        <nav>
            <div class="nav__links">
                <a class="nav" href="#">CALENDAR</a>
            </div>
        </nav>
    </header>

    <div class="home-tutor-screen">
        <div class="home-tutor-main">
            <h4>Screening Test</h4>

        </div>
        <?php include('right-side-notifications.php'); ?>
    </div>
</body>
</html>