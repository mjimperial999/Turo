<?php
/* -------------------------------------------------
 * Calendar grid – 100 % self-contained
 * ------------------------------------------------*/
use Carbon\Carbon;

/* ---------- pick month to show ---------- */
$year  = (int) ($_GET['y'] ?? Carbon::now()->year);
$month = (int) ($_GET['m'] ?? Carbon::now()->month);
$pivot = Carbon::create($year, $month, 1);     // first day of month
$today = Carbon::today();

/* ---------- build a keyed map of events / activities ----------
   ( → populate $calendar['YYYY-MM-DD']['ev'|'ac'][] = … )
   NB: replace the example arrays with whatever you fetch
---------------------------------------------------------------*/
$calendar = [];

/* demo – you probably already fill this in another file
$calendar['2025-07-14']['ev'][] = (object)['title'=>'System maintenance'];
$calendar['2025-07-22']['ac'][] = (object)['activity_name'=>'[PRAC] Module 3 Quiz'];
*/

/* ---------- date helpers ---------- */
function keyFor(Carbon $c){ return $c->format('Y-m-d'); }

/* span to display: start on the **Sunday** before month-start,
   finish on the **Saturday** after month-end                    */
$start = $pivot->copy()->startOfWeek(Carbon::SUNDAY);
$end   = $pivot->copy()->endOfMonth()->endOfWeek(Carbon::SATURDAY);

/* total weeks = rows in the grid */
$weeks = $start->diffInWeeks($end) + 1;
?>
<style>
    table.calendar   {width:100%;border-collapse:collapse;font-family:Albert-Sans,sans-serif}
    .calendar th,.calendar td{border:1px solid #ddd;padding:.25rem;font-size:.8rem}
    .calendar th     {background:#eee;font-size:.9rem;text-align:center}
    .calendar .num   {font-weight:700}
    .calendar .today {background:#fde9e9}
    .calendar .entry {display:block;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;font-size:11px;margin:.1rem 0}
    .calendar .ev    {color:#c00}
    .calendar .ac    {color:#1976d2}
</style>

<!-- ===== month header with ‹ › nav ===== -->
<h4 style="text-align:center;margin:0 0 .4rem">
    <?php
      $prev = $pivot->copy()->subMonth();
      $next = $pivot->copy()->addMonth();
    ?>
    <a href="?y=<?= $prev->year ?>&m=<?= $prev->month ?>">&#9664;</a>
    <?= $pivot->format('F Y') ?>
    <a href="?y=<?= $next->year ?>&m=<?= $next->month ?>">&#9654;</a>
</h4>

<table class="calendar">
    <tr><?php foreach(['Su','Mo','Tu','We','Th','Fr','Sa'] as $d) echo "<th>$d</th>"; ?></tr>
    <?php
    $cur = $start->copy();
    for($w=0;$w<$weeks;$w++):
        echo "<tr>";
        for($d=0;$d<7;$d++, $cur->addDay()):
            $key = keyFor($cur);
            $inMonth = $cur->month === $month;
            $isToday = $cur->eq($today);

            echo '<td'.($isToday?' class="today"':'').'>';
            if($inMonth){
                echo '<span class="num">'.$cur->day.'</span>';

                foreach(($calendar[$key]['ev'] ?? []) as $e)
                    echo '<span class="entry ev">'.e($e->title).'</span>';

                foreach(($calendar[$key]['ac'] ?? []) as $a)
                    echo '<span class="entry ac">'.e($a->activity_name).'</span>';
            }
            echo '</td>';
        endfor;
        echo "</tr>";
    endfor;
    ?>
</table>

<div id="details" style="margin-top:.5rem;font-size:13px;"></div>
