<?php
use Carbon\Carbon;
$now = Carbon::now('Asia/Manila');

$unlock = Carbon::parse($activity->unlock_date);
$deadline = $activity->deadline_date ? Carbon::parse($activity->deadline_date) : null;
$unlockTimestamp = strtotime($unlock);
$deadlineTimestamp = strtotime($deadline);

$formattedUnlockDate = date("F j, Y h:i A", $unlockTimestamp);
$formattedDeadline = date("F j, Y h:i A", $deadlineTimestamp);
$isAvailable = $unlock->lte($now) && ($deadline && $deadline->gte($now));
$description;
    if ($unlock->lte($now)):{
        $description = "Locked at ". $formattedDeadline;
    };
    else: {
        $description = "Unlocks at ". $formattedUnlockDate;
    }; endif;
?>