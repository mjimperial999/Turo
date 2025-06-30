<?php
if (session('role_id') == 1) {
    if ($isAvailable) {
        echo
        '<div class="lecture-flex-box">
        <form action="/home-tutor/course/'. $course->course_id .'/module/' . $module->module_id . '/tutorial/' . $activity->activity_id . '" method="GET">
        <button class="lecture-box tutorial">
            <div class="lecture-title">
                <div class="logo">
                    <img class="svg" src="/icons/vid.svg" width="38em" height="auto" />
                </div>
                <div class="text title">
                    <h6>' . $activity->activity_name . '</h6>
                    <p>' . $activity->activity_description . '</p>
                </div>
            </div>
        </button>
        </form>
        </div>';
    } else {
        echo
        '<div class="lecture-flex-box locked">
        <div class="lecture-box tutorial">
            <div class="lecture-title">
                <div class="logo">
                    <img class="svg" src="/icons/vid.svg" width="38em" height="auto" />
                </div>
                <div class="text title">
                    <h6>' . $activity->activity_name . ' (LOCKED)</h6>
                    <p>'. $description . '</h6>
                </div>
            </div>
        </div>
        </div>';
    };
} else {
    echo
    '<div class="lecture-flex-box">
    <form action="/teachers-panel/course/'. $course->course_id .'/module/' . $module->module_id . '/tutorial/' . $activity->activity_id . '" method="GET">
        <button type="submit" class="lecture-box tutorial">
            <div class="lecture-title">
                <div class="logo">
                    <img class="svg" src="/icons/vid.svg" width="38em" height="auto" />
                </div>
                <div class="text title">
                    <h6>' . $activity->activity_name . '</h6>';
                if (!$isAvailable) {
                    echo '<p>' . $description . '</p>';
                } else {
                    echo '<p>' . $activity->activity_description . '</p>';
                }
            echo '</div>
            </div>
        </button>
    </form>
    <div class="lecture-crud">
                <div class="box-button">
                    <form action="/teachers-panel/course/'. $course->course_id .'/module/' . $module->module_id . '/tutorial/' . $activity->activity_id . '/edit" method="GET">
                    <button type="submit" class="box-button-edit">
                        <img src="/icons/edit-black.svg" width="20em" height="auto" />
                    </button>
                    </form>
                </div>
                <div class="box-button">
                    <form action="/teachers-panel/course/'. $course->course_id .'/module/' . $module->module_id . '/tutorial/' . $activity->activity_id . '/delete" method="POST"
                    onsubmit="return confirm(' . "'Are you sure you want to delete this tutorial: " . $activity->activity_name ."? '" .');">
                    '. csrf_field() .'
                    <button type="submit" class="box-button-delete">
                        <img src="/icons/delete.svg" width="20em" height="auto" />
                    </button>
                    </form>
                </div>
            </div>
    </div>';
} ?>