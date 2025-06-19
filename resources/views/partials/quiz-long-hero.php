<?php
if ($isAvailable): {
    echo
    '<div class="activity">
        <a class="activity-link" href="/home-tutor/long-quiz/'. $course->course_id . '/' . $longquiz->long_quiz_id . '">
            <div class="activity-button quiz-long-activity unlocked">
                <div class="activity-logo">
                    <img class="svg" src="/icons/long-quiz.svg" width="40em" height="auto" />
                </div>
                <div class="activity-name">' . $longquiz->long_quiz_name . '</div>
            </div>
        </a>
        <div class="activity-description"></div>
    </div>';
    };
else: {
    echo
    '<div class="activity">
        <div class="activity-button quiz-long-activity locked">
            <div class="activity-logo">
                <img class="svg" src="/icons/long-quiz.svg" width="40em" height="auto" />
            </div>
            <div class="activity-name">' . $longquiz->long_quiz_name . '</div>
        </div>
        <div class="activity-description">' . $description. '</div>
    </div>';
}
endif;
?>
