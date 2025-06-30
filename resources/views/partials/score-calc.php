<?php
$percentage = $assessment ? $assessment->score_percentage : null;
$circle_display = $percentage !== null ? (450 - (450 * $percentage) / 100) : 450;

// Color based on percentage
if ($percentage === null) {
    $color = '#999999';
    $percentage_display = '--';
} elseif ($percentage >= 80) {
    $color = '#00cc25';
    $percentage_display = round($percentage);
} elseif ($percentage >= 75) {
    $color = '#caee01';
    $percentage_display = round($percentage);
} elseif ($percentage >= 50) {
    $color = '#ee8301';
    $percentage_display = round($percentage);
} else {
    $color = '#ee0101';
    $percentage_display = round($percentage);
}

?>