<?php
$roleId   = session('role_id');
$type     = $activity->activity_type;                  // LECTURE / TUTORIAL
$boxClass = strtolower($type);                         // lecture | tutorial
$icon     = $type == 'LECTURE' ? '/icons/lecture.svg'
    : '/icons/vid.svg';

$url = ''; 
    
if (session('role_id') == 1) {
    $url  = "/home-tutor/course/{$course->course_id}/module/{$module->module_id}/";
} elseif (session('role_id') == 2) {
    $url = "/teachers-panel/course/{$course->course_id}/section/{$section->section_id}/module/{$module->module_id}/";
} else {
    $url = "/admin-panel/edit-content/course/{$course->course_id}/module/{$module->module_id}/";
}

$url .= strtolower($type) . "/{$activity->activity_id}";

if (session('role_id') == 2){
    $urlEdit = "/teachers-panel/course/{$course->course_id}/section/{$section->section_id}/module/{$module->module_id}/"
    . strtolower($type) . "/{$activity->activity_id}/edit";
    $urlDel   = "/teachers-panel/course/{$course->course_id}/section/{$section->section_id}/module/{$module->module_id}/"
    . strtolower($type) . "/{$activity->activity_id}/delete";
} else {
    $urlEdit = "/admin-panel/edit-content/course/{$course->course_id}/module/{$module->module_id}/"
    . strtolower($type) . "/{$activity->activity_id}/edit";
    $urlDel   = "/admin-panel/edit-content/course/{$course->course_id}/module/{$module->module_id}/"
    . strtolower($type) . "/{$activity->activity_id}/delete";
}

?>


<?php if (session('role_id') == 1): ?>
    <div class="lecture-flex-box<?= $isAvailable ? '' : ' locked' ?>">
        <form action="<?= $url ?>" method="GET">
            <button type="submit" class="lecture-box <?= $boxClass ?>">
                <div class="lecture-title">
                    <div class="logo">
                        <img class="svg" src="<?= $icon ?>" width="42em" alt="">
                    </div>
                    <div class="text title">
                        <h6>
                            <?= e($activity->activity_name) ?>
                            <?= $isAvailable ? '' : ' (LOCKED)' ?>
                        </h6>
                        <p>
                            <?= e($isAvailable ? $activity->activity_description : $description) ?>
                        </p>
                    </div>
                </div>
            </button>
        </form>
    </div>
<?php endif; ?>

<?php if (session('role_id') == 2): /* teacher CRUD buttons */ ?>
    <div class="lecture-flex-box">
        <form action="<?= $url ?>" method="GET">
            <button type="submit" class="lecture-box <?= $boxClass ?>">
                <div class="lecture-title">
                    <div class="logo">
                        <img class="svg" src="<?= $icon ?>" width="42em" alt="">
                    </div>
                    <div class="text title">
                        <h6>
                            <?= e($activity->activity_name) ?>
                            <?= $isAvailable ? '' : ' (LOCKED)' ?>
                        </h6>
                        <p>
                            <?= e($isAvailable ? $activity->activity_description : $description) ?>
                        </p>
                    </div>
                </div>
            </button>
        </form>
        <div class="lecture-crud">
            <div class="box-button">
                <form action="<?= $urlEdit ?>" method="GET">
                    <button type="submit" class="box-button-edit">
                        <img src="/icons/edit-black.svg" width="20em" alt="">
                    </button>
                </form>
            </div>
            <div class="box-button">
                <form action="<?= $urlDel ?>" method="POST"
                    onsubmit="return confirm('Delete this <?= strtolower($type) ?>: <?= e($activity->activity_name) ?> ?');">
                    <?= csrf_field() ?>
                    <button type="submit" class="box-button-delete">
                        <img src="/icons/delete.svg" width="20em" alt="">
                    </button>
                </form>
            </div>
        </div>
    </div>
<?php endif; ?>

<?php if (session('role_id') == 3): /* admin CRUD buttons */ ?>
    <div class="lecture-flex-box">
        <form action="<?= $url ?>" method="GET">
            <button type="submit" class="lecture-box <?= $boxClass ?>">
                <div class="lecture-title">
                    <div class="logo">
                        <img class="svg" src="<?= $icon ?>" width="42em" alt="">
                    </div>
                    <div class="text title">
                        <h6>
                            <?= e($activity->activity_name) ?>
                            <?= $isAvailable ? '' : ' (LOCKED)' ?>
                        </h6>
                        <p>
                            <?= e($isAvailable ? $activity->activity_description : $description) ?>
                        </p>
                    </div>
                </div>
            </button>
        </form>
        <div class="lecture-crud">
            <div class="box-button">
                <form action="<?= $urlEdit ?>" method="GET">
                    <button type="submit" class="box-button-edit">
                        <img src="/icons/edit-black.svg" width="20em" alt="">
                    </button>
                </form>
            </div>
            <div class="box-button">
                <form action="<?= $urlDel ?>" method="POST"
                    onsubmit="return confirm('Delete this <?= strtolower($type) ?>: <?= e($activity->activity_name) ?> ?');">
                    <?= csrf_field() ?>
                    <button type="submit" class="box-button-delete">
                        <img src="/icons/delete.svg" width="20em" alt="">
                    </button>
                </form>
            </div>
        </div>
    </div>
<?php endif; ?>