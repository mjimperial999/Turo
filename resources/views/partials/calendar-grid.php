<?php

use Carbon\Carbon;

/* ---------- pick month ---------- */

$year  = (int) ($_GET['y'] ?? date('Y'));
$month = (int) ($_GET['m'] ?? date('n'));
$first = Carbon::create($year, $month, 1);
$today = Carbon::today();

/* helpers */
$key   = fn(Carbon $c) => $c->format('Y-m-d');
$start = $first->copy()->startOfWeek();
$end   = $first->copy()->endOfMonth()->endOfWeek();
$weeks = $start->diffInWeeks($end) + 1;

?>
<style>
    table.calendar {
        width: 100%;
        border-collapse: collapse;
        font-family: Albert-Sans, sans-serif
    }

    .calendar th,
    .calendar td {
        border: 1px solid #ddd;
        padding: .25rem;
        font-size: .8rem;
        vertical-align: top
    }

    .calendar th {
        background: #eee;
        font-size: .9rem;
        text-align: center
    }

    .calendar .num {
        font-weight: 700
    }

    .calendar .today {
        background: #fde9e9
    }

    /* colour text */
    .calendar .prc {
        color: #e67e22
    }

    /* orange   */
    .calendar .sht {
        color: #2e7d32
    }

    /* green    */
    .calendar .lng {
        color: #1565c0
    }

    /* blue     */
    .calendar .ann {
        color: #c0392b;
    }

    /* red text  */
    .calendar .urgent {
        font-weight: 700;
    }

    /* bold      */
    .calendar .ann-bg {
        background: #fff8c5
    }

    /* yellow cell */
    .calendar .ann-urg {
        border: 3px solid #c0392b !important;
    }

    /* urgent border */

    /* cell tint */
    .calendar .entry {
        display: block;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
        font-size: 11px
    }
</style>

<!-- ===== month header with ‹ › nav ===== -->
<h4 style="text-align:center;margin:.3rem 0">
    <?php $prev = $first->copy()->subMonth();
    $next = $first->copy()->addMonth(); ?>
    <a href="?y=<?= $prev->year ?>&m=<?= $prev->month ?>">&#9664;</a>
    <?= $first->format('F Y') ?>
    <a href="?y=<?= $next->year ?>&m=<?= $next->month ?>">&#9654;</a>
</h4>

<table class="calendar">
    <tr><?php foreach (['Su', 'Mo', 'Tu', 'We', 'Th', 'Fr', 'Sa'] as $d) echo "<th>$d</th>"; ?></tr>
    <?php
    $cur = $start->copy();
    for ($w = 0; $w < $weeks; $w++):
        echo '<tr>';
        for ($d = 0; $d < 7; $d++, $cur->addDay()):
            $dayKey = $key($cur);
            $inMonth = ($cur->month == $month);
            $cellItems = $calendar[$dayKey] ?? [];
            $hasAnn = collect($cellItems)->contains(fn($i) => str_contains($i['class'], 'ann'));
            $hasUrg = collect($cellItems)->contains(fn($i) => str_contains($i['class'], 'urgent'));
            $cls = ($cur->eq($today) ? 'today ' : '')
                . ($hasAnn ? 'ann-bg ' : '')
                . ($hasUrg ? 'ann-urg ' : '');
            echo "<td class=\"$cls\">";
            if ($inMonth) {
                echo '<span class="num">' . $cur->day . '</span>';
                foreach ($cellItems as $it) {
                    echo '<span class="entry ' . $it['class'] . '" '
                        . 'data-marker="' . $it['marker'] . '" '
                        . 'data-text="' . e($it['text']) . '">'
                        . $it['marker'] . ' ' . $it['text'] . '</span>';
                }
            }
            echo '</td>';
        endfor;
        echo '</tr>';
    endfor; ?>
</table>

<div id="details" style="margin-top:.5rem;font-size:13px;"></div>