<?php
if (session('role_id') == 1) {
    $percentage = $longquiz->keptResult() 
                       ->where('student_id', session('user_id'))
                       ->value('score_percentage')
            ?? '--';

    include __DIR__ . '/../partials/score-color.php';

    if ($isAvailable) {
        echo
        '<div class="quiz-flex-box">
        <form action="/home-tutor/course/' . $course->course_id . '/longquiz/' . $longquiz->long_quiz_id . '" method="GET">
        <button class="quiz-box long">
            <div class="quiz-title">
                <div class="logo">
                    <img class="svg" src="/icons/long-quiz.svg" width="42em" height="auto" />
                </div>
                <div class="text title">
                    <h6>' . $longquiz->long_quiz_name . '</h6>
                </div>
            </div>
            <div class="quiz-score">
                <div class="text title">
                    <h6 style="--percentage:' . $color . '" >' . $percentage . '%</h6>
                </div>
            </div>
        </button>
        </form>
        </div>';
    } else {
        echo
        '<div class="quiz-flex-box locked">
        <div class="quiz-box long">
            <div class="quiz-title">
                <div class="logo">
                    <img class="svg" src="/icons/long-quiz.svg" width="42em" height="auto" />
                </div>
                <div class="text title">
                    <h6>' . $longquiz->long_quiz_name . ' (LOCKED)</h6>
                    <p>' . $description . '</p>
                </div>
            </div>
            <div class="quiz-score">
                <div class="text title">
                    <h6 style="--percentage:' . $color . '" >' . $percentage . '%</h6>
                </div>
            </div>
        </div>
        </div>';
    };
} elseif (session('role_id') == 2) {
    echo
    '<div class="quiz-flex-box">
    <form action="/teachers-panel/course/' . $course->course_id . '/section/'. $section->section_id .'/longquiz/' . $longquiz->long_quiz_id . '" method="GET">
        <button type="submit" class="quiz-box long">
            <div class="quiz-title">
                <div class="logo">
                    <img class="svg" src="/icons/long-quiz.svg" width="42em" height="auto" />
                </div>
                <div class="text title">
                    <h6>' . $longquiz->long_quiz_name . '</h6>';
                    if (!$isAvailable) {
                    echo '<p>' . $description . '</p>';
                    }
            echo '</div>
            </div>
            <div class="quiz-score">
                <div class="text title">
                </div>
            </div>
        </button>
    </form>
    <div class="quiz-crud">
                <div class="box-button">
                    <form action="/teachers-panel/course/' . $course->course_id . '/section/'. $section->section_id .'/longquiz/' . $longquiz->long_quiz_id . '/edit" method="GET">
                    <button type="submit" class="box-button-edit">
                        <img src="/icons/edit-black.svg" width="20em" height="auto" />
                    </button>
                    </form>
                </div>
                <div class="box-button">
                    <form action="/teachers-panel/course/' . $course->course_id . '/section/'. $section->section_id .'/longquiz/' . $longquiz->long_quiz_id . '/delete" method="POST"
                    onsubmit="return confirm(' . "'Are you sure you want to delete this module: " . $longquiz->long_quiz_name ."? '" .');">
                    '. csrf_field() .'
                    <button type="submit" class="box-button-delete">
                        <img src="/icons/delete.svg" width="20em" height="auto" />
                    </button>
                    </form>
                </div>
            </div>
    </div>';
} else {
    echo
    '<div class="quiz-flex-box">
    <form action="/admin-panel/edit-content/course/' . $course->course_id . '/longquiz/' . $longquiz->long_quiz_id . '" method="GET">
        <button type="submit" class="quiz-box long">
            <div class="quiz-title">
                <div class="logo">
                    <img class="svg" src="/icons/long-quiz.svg" width="42em" height="auto" />
                </div>
                <div class="text title">
                    <h6>' . $longquiz->long_quiz_name . '</h6>';
                    if (!$isAvailable) {
                    echo '<p>' . $description . '</p>';
                    }
                echo '</div>
            </div>
            <div class="quiz-score">
                <div class="text title">
                </div>
            </div>
        </button>
    </form>
    <div class="quiz-crud">
                <div class="box-button">
                    <form action="/admin-panel/edit-content/course/' . $course->course_id .'/longquiz/' . $longquiz->long_quiz_id . '/edit" method="GET">
                    <button type="submit" class="box-button-edit">
                        <img src="/icons/edit-black.svg" width="20em" height="auto" />
                    </button>
                    </form>
                </div>
                <div class="box-button">
                    <form action="/admin-panel/edit-content/course/' . $course->course_id .'/longquiz/' . $longquiz->long_quiz_id . '/delete" method="POST"
                    onsubmit="return confirm(' . "'Are you sure you want to delete this module: " . $longquiz->long_quiz_name ."? '" .');">
                    '. csrf_field() .'
                    <button type="submit" class="box-button-delete">
                        <img src="/icons/delete.svg" width="20em" height="auto" />
                    </button>
                    </form>
                </div>
            </div>
    </div>';
}

