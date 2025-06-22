<?php
use Carbon\Carbon;
$now = Carbon::now('Asia/Manila');

$unlock = Carbon::parse($screening->unlock_date);
$deadline = $screening->deadline_date ? Carbon::parse($screening->deadline_date) : null;
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