<?php
$blobData = $module->moduleimage->image ?? null;
if (!$blobData) {
    $backgroundImage = "/uploads/course/math.jpg";
} else {
    $mimeType = getMimeTypeFromBlob($blobData);
    $base64Image = base64_encode($blobData);
    $backgroundImage = "data:$mimeType;base64,$base64Image";
}

if (session('role_id') == 1) {
    $raw = optional($module->studentprogress->first())->progress;   // ← may be null

    /* ───────── decide what to show ───────── */
    if ($raw == 0) {
        $progressDisplay = '-';               // never started
    } else {
        $progressDisplay = number_format($raw, 0) . '%';
    }

    echo '
    <div class="flex-box modules">
        <form action="/home-tutor/course/' . $course->course_id . '/module/' . $module->module_id . '" method="GET">
            <button type="submit" class="box" style="background-image: url(' . $backgroundImage . ');">
                
                    <div class="box-filler" ></div>
                    <div class="box-details">
                        <div class="box-title">
                            <div class="title">
                                <h6>' . $module->module_name . '</h6>
                                <p>' . $module->module_description . '</p>
                            </div>
                            <div class="prog"><h6>' . $progressDisplay . '</h6></div>
                        </div>
                    </div>
                
            </button>
        </form>
    </div>
';
} elseif (session('role_id') == 2) {
    echo '
    <div class="flex-box modules">
        <form action="/teachers-panel/course/' . $course->course_id . '/section/' . $section->section_id . '/module/' . $module->module_id . '" method="GET">
            <button type="submit" class="box" style="background-image: url(' . $backgroundImage . ');">
                
                    <div class="box-filler" ></div>
                    <div class="box-details">
                        <div class="box-title">
                            <div class="title">
                                <h6>' . $module->module_name . '</h6>
                                <p>' . $module->module_description . '</p>
                            </div>
                            <div class="prog"></div>
                        </div>
                    </div>
                
            </button>
        </form>
            <div class="box-crud">
                <div class="box-button">
                    <form action="/teachers-panel/course/' . $course->course_id . '/section/' . $section->section_id . '/module/' . $module->module_id . '/edit" method="GET">
                    <button type="submit" class="box-button-edit">
                        <img src="/icons/edit-black.svg" width="20em" height="auto" />
                    </button>
                    </form>
                </div>
                <div class="box-button">
                    <form action="/teachers-panel/course/' . $course->course_id . '/section/' . $section->section_id . '/module/' . $module->module_id . '/delete" method="POST"
                    onsubmit="return confirm(' . "'Are you sure you want to delete this module: " . $module->module_name . "? '" . ');">
                    ' . csrf_field() . '
                    <button type="submit" class="box-button-delete">
                        <img src="/icons/delete.svg" width="20em" height="auto" />
                    </button>
                    </form>
                </div>
            </div>
    </div>
';
} else {
    echo '
    <div class="flex-box modules">
        <form action="/admin-panel/edit-content/course/' . $course->course_id . '/module/' . $module->module_id . '" method="GET">
            <button type="submit" class="box" style="background-image: url(' . $backgroundImage . ');">
                
                    <div class="box-filler" ></div>
                    <div class="box-details">
                        <div class="box-title">
                            <div class="title">
                                <h6>' . $module->module_name . '</h6>
                                <p>' . $module->module_description . '</p>
                            </div>
                            <div class="prog"></div>
                        </div>
                    </div>
                
            </button>
        </form>
            <div class="box-crud">
                <div class="box-button">
                    <form action="/admin-panel/edit-content/course/' . $course->course_id . '/module/' . $module->module_id . '/edit" method="GET">
                    <button type="submit" class="box-button-edit">
                        <img src="/icons/edit-black.svg" width="20em" height="auto" />
                    </button>
                    </form>
                </div>
                <div class="box-button">
                    <form action="/admin-panel/edit-content/course/' . $course->course_id . '/module/' . $module->module_id . '/delete" method="POST"
                    onsubmit="return confirm(' . "'Are you sure you want to delete this module: " . $module->module_name . "? '" . ');">
                    ' . csrf_field() . '
                    <button type="submit" class="box-button-delete">
                        <img src="/icons/delete.svg" width="20em" height="auto" />
                    </button>
                    </form>
                </div>
            </div>
    </div>
';
}
