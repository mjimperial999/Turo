<?php
$timestamp = strtotime($course->start_date);
$formattedDate = date("F j, Y", $timestamp);

if (empty($course->image?->image)) {;
    $imageURL = "/images/no-image.jpeg";
} else {
    $blobData = $course->image?->image ?? null;
    $mimeType = getMimeTypeFromBlob($blobData);
    $base64Image = base64_encode($blobData);
    $imageURL = "data:$mimeType;base64,$base64Image";
}

if (session('role_id') == 1) {
    echo '
    <div class="flex-box">
        <form action="/home-tutor/course/' . $course->course_id . '" method="GET">
            <button type="submit" class="box" style="background-image: url(' . $imageURL . ');">
                
                    <div class="box-filler" ></div>
                    <div class="box-details">
                        <div class="box-title">
                            <div class="title">
                                <h6>' . $course->course_name . '</h6>
                                <p>'. $course->course_description .'</p>
                            </div>
                            <div class="code">' . $course->course_code . '</div>
                        </div>
                    </div>
                
            </button>
        </form>
    </div>
';
} elseif (session('role_id') == 2) {
    echo '
    <div class="flex-box">
        <form action="/teachers-panel/course/' . $course->course_id . '/section/' . $section->section_id .'" method="GET">
            <button type="submit" class="box" style="background-image: url(' . $imageURL . ');">
                
                    <div class="box-filler" ></div>
                    <div class="box-details">
                        <div class="box-title">
                            <div class="title">
                                <h6>' . $course->course_name . '</h6>
                                <p>'. $course->course_description .'</p>
                            </div>
                            <div class="code">
                            <p><b>Section</b></p>
                            <p>' . $section->section_name . '</p></div>
                        </div>
                    </div>
                
            </button>
        </form>
            <div class="box-crud">
                <div class="box-button">
                    <form action="/teachers-panel/course/'.$course->course_id.'/edit" method="GET">
                    <?= csrf_field(); ?>
                    <button type="submit" class="box-button-edit">
                        <img src="/icons/edit-black.svg" width="20em" height="auto" />
                    </button>
                    </form>
                </div>
                <div class="box-button">
                    <form action="/teachers-panel/course/'.$course->course_id.'/delete" method="POST" onsubmit="return confirm('. "'Really delete this course?'" .');">
                    '. csrf_field() .'
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
    <div class="flex-box">
        <form action="/admin-panel/edit-content/course/' . $course->course_id .'" method="GET">
            <button type="submit" class="box" style="background-image: url(' . $imageURL . ');">
                
                    <div class="box-filler" ></div>
                    <div class="box-details">
                        <div class="box-title">
                            <div class="title">
                                <h6>' . $course->course_name . '</h6>
                                <p>'. $course->course_description .'</p>
                            </div>
                            <div class="code">' . $course->course_code . '</div>
                        </div>
                    </div>
                
            </button>
        </form>
            <div class="box-crud">
                <div class="box-button">
                    <form action="/admin-panel/edit-content/course/'.$course->course_id.'/edit" method="GET">
                    <?= csrf_field(); ?>
                    <button type="submit" class="box-button-edit">
                        <img src="/icons/edit-black.svg" width="20em" height="auto" />
                    </button>
                    </form>
                </div>
                <div class="box-button">
                    <form action="/admin-panel/edit-content/course/'.$course->course_id.'/delete" method="POST" onsubmit="return confirm('. "'Really delete this course?'" .');">
                    '. csrf_field() .'
                    <button type="submit" class="box-button-delete">
                        <img src="/icons/delete.svg" width="20em" height="auto" />
                    </button>
                    </form>
                </div>
            </div>
    </div>
';
}
