<?php      
    $quiz_type = "Quiz";
    $class = "short-quiz";
    $buttonClass = "quiz-short-activity";

    if ($activity->quiz->quiz_type_id == 1):{
        $quiz_type = "Short Quiz";
        $class = "short-quiz";
        $buttonClass = "quiz-short-activity";
    };
        
    elseif ($activity->quiz->quiz_type_id == 2):{
        $quiz_type = "Practice Test";
        $class = "practice";
        $buttonClass = "quiz-practice-activity";
    };

    elseif ($activity->quiz->quiz_type_id == 3):{
        $quiz_type = "Long Quiz";
        $class = "long-quiz";
        $buttonClass = "quiz-long-activity";
    };

    endif;
