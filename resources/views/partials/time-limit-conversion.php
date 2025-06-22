<?php
$seconds = $activity->quiz->time_limit;
$minutes = floor($seconds / 60);
$fTimeLimit = sprintf("%2d", $minutes);
?>