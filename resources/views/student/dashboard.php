<?php $title = 'My Courses';
include __DIR__ . '/../partials/head.php';?>
    <style>
        h4 {
            font-family: Alata, sans-serif;
        }

        .course-display-grid-box {
            width: 100%;
            height: auto;
            min-height: 10rem;

            display: grid;
            grid-auto-flow: row;
            grid-template-columns: repeat(auto-fit, minmax(20rem, 1fr));
            gap: 2rem 3vw;
        }

        .course-menu {
            margin: 0;
            border-radius: 0.4rem;
            width: 100%;
            font-size: 30px;
            text-align: center;
            box-shadow: 0rem 0rem 12rem -3rem rgba(0, 0, 0, 0.8) inset, 2rem -6rem 20rem -2rem rgba(0, 0, 0, 1) inset;
            filter: drop-shadow(0 0.2rem 0.25rem rgba(0, 0, 0, 0.2));
            cursor: pointer;
            background-size: cover;
            background-position: right top;

            display: flex;
            flex-direction: column;
            
            transition: all 0.3s ease 0s;
        }

        .course-menu:hover {
            filter: drop-shadow(0 0.2rem 0.4rem rgba(0, 0, 0, 0.5));
            transform: translate(0, -0.15rem);
            transition: all 0.3s ease 0s;
        }

        .course-filler {
            height: 9rem;

            display: flex;
            flex-direction: column;
        }

        .course-details {
            height: 5rem;
            padding: 1rem 1rem;
            display: flex;
            flex-direction: row;
            justify-content: space-between;
        }

        .course-header {
            display: flex;
            flex-direction: column;
            align-items: flex-start;
            justify-content: flex-end;
        }

        .course-teacher {
            display: flex;
            flex-direction: column;
            align-items: flex-end;
            justify-content: flex-end;
        }

        .course-title,
        .course-desc {
            color: #FFFFFF;
            margin: 0;
            padding: 0;
            font-family: Alexandria, sans-serif;
            font-weight: 500;
        }

        .course-desc {
            font-size: 0.8rem;
            font-weight: 300;
        }

        a.module-link {
            text-decoration: none;
            width: 20rem;
            height: 14rem;
        }
    </style>
</head>

<body>
    <?php include __DIR__ . '/../partials/nav.php'; ?> 

    <div class="home-tutor-screen">
        <div class="home-tutor-main">
            <h4>Dashboard - Courses</h4>
            <hr>
            <div class="course-display-grid-box">
                <?php foreach ($courses as $course) {
                    $timestamp = strtotime($course->start_date);
                    $formattedDate = date("F j, Y", $timestamp);
                    echo '<a class="module-link" href="/home-tutor/course/'. $course->course_id .'"><div class="course-menu" style="background-image: url(' . "'/uploads/course/math.jpg'" . ');">
                    <div class="course-filler">
                    </div>
                    <div class="course-details">
                        <div class="course-header">
                            <h6 class="course-title">' . $course->course_name . '</h6>
                            <h6 class="course-desc">' . $course->course_code . '</h6>
                        </div>
                        <div class="course-teacher">
                            <h6 class="course-desc">Ends in:</h6>
                            <h6 class="course-desc">' . $formattedDate . '</h6>
                        </div>
                    </div>
                </div></a>';
                }
                ?>
            </div>

        </div>
        <?php include __DIR__.'/../partials/right-side-notifications.php'; ?>
    </div>

<?php include __DIR__ . '/../partials/footer.php'; ?>
</body>

</html>