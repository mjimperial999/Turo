<?php
if ($isAvailable): {
    echo
    '<div class="activity">
        <a class="activity-link" href="/home-tutor/lecture/' . $activity->activity_id . '">
            <div class="activity-button lecture-activity unlocked">
                <div class="activity-logo">
                    <img class="svg" src="/icons/lecture.svg" width="30em" height="auto" />
                </div>
                <div class="activity-name">' . $activity->activity_name . '</div>
            </div>
        </a>
        <div class="activity-description">' . $activity->activity_description . '</div>
    </div>';
    };
else: {
    echo
    '<div class="activity">
        <div class="activity-button lecture-activity locked">
            <div class="activity-logo">
                <img class="svg" src="/icons/lecture.svg" width="30em" height="auto" />
            </div>
            <div class="activity-name">' . $activity->activity_name . '</div>
        </div>
        <div class="activity-description">' .$description. '</div>
    </div>';
}
endif;
?>
