<?php

if ($percentage === null) {
    $color = '#d5d5d5';
    $percentage_display = '--';
} elseif ($percentage >= 80) {
    $color = '#00cc25';
    $percentage_display = round((int)$percentage);
} elseif ($percentage >= 75) {
    $color = '#adcb00';
    $percentage_display = round((int)$percentage);
} elseif ($percentage >= 50) {
    $color = '#ee8301';
    $percentage_display = round((int)$percentage);
} else {
    $color = '#999999';
    $percentage_display = round((int)$percentage);
}