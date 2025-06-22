<?php
echo '
<a class="module-link" href="/home-tutor/course/' . $course->course_id . '/' . $screening->screening_id . '">
    <div class="module-menu" style="background-image: url(' . $backgroundImage . ');">
        <div class="module-filler">
        </div>
        <div class="module-details">
            <div class="module-menu-title">' . $screening->screening_name . '</div>
            <div class="module-menu-progress">Progress: screeningresults </div>
        </div>
    </div>
</a> '
?>