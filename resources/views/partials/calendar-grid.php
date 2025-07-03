<?php
use Carbon\Carbon;

$pivot = Carbon::create(
            $_GET['y'] ?? Carbon::now()->year,
            $_GET['m'] ?? Carbon::now()->month,
            1
         );                       // first day of the month we’re showing

$first  = $pivot->copy();         // keep an untouched copy
$today  = Carbon::today();
$year   = $pivot->year;
$month  = $pivot->month;
$last   = $pivot->copy()->endOfMonth();

/* weeks in the grid (Sun-based) */
$weeks = intval($last->format('W')) - intval($first->format('W')) + 1;
if ($weeks <= 0) $weeks += 52;    // Dec-→Jan wrap

/* helper for arrows */
$prev = $pivot->copy()->subMonth();
$next = $pivot->copy()->addMonth();

/* ------------------------------------------------------------------*/
function cellKey($y,$m,$d){return sprintf('%04d-%02d-%02d',$y,$m,$d);}
?>

<style>
    table.cal {
        width: 100%;
        border-collapse: collapse;
        font-size: .85em
    }

    table.cal th,
    table.cal td {
        border: 1px solid #ccc;
        text-align: left;
        vertical-align: top;
        padding: 2px 4px
    }

    table.cal th {
        background: #f5f5f5;
        text-align: center
    }

    table.cal td.today {
        background: #fff3cd
    }

    .cal .num {
        font-weight: 700
    }

    .entry.ev {
        display: block;
        color: #b91c1c;
        font-weight: 600
    }

    .entry.ac {
        display: block;
        color: #0f5132
    }

    .calendar {
        width: 100%;
        table-layout: fixed;
        border-collapse: collapse;
        font-family: Albert-Sans, sans-serif;
    }

    .calendar th {
        border: 1px solid #ddd;
        font-size: 0.9rem;
        height: 2rem;
        background: #eee;
        padding: .4rem;
        text-align: center;
    }

    .calendar td {
        font-size: 0.8rem;
        border: 1px solid #ddd;
        height: 2rem;
        vertical-align: top;
        padding: .2rem;
        position: relative;
    }

    .calendar .num {
        font-weight: 300;
    }

    .calendar .today {
        background: #fdd !important;
    }

    .calendar .entry {
        display: block;
        font-size: 11px;
        margin: .1rem 0;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    .calendar .ev {
        color: #c00;
    }

    .calendar .ac {
        color: #1976d2;
    }
</style>

<!-- ===== header with ‹ › month nav ===== -->
<!-- ===== month header with ‹ › nav ===== -->
  <h4 style="text-align:center;margin:0 0 .4rem">
      <a href="?y=<?= $prev->year ?>&m=<?= $prev->month ?>">&#9664;</a>
      <?= $pivot->format('F Y') ?>
      <a href="?y=<?= $next->year ?>&m=<?= $next->month ?>">&#9654;</a>
  </h4>

  <table class="calendar">
      <tr><?php foreach (['Su','Mo','Tu','We','Th','Fr','Sa'] as $d) echo "<th>$d</th>"; ?></tr>
      <?php
      $wDay = $first->dayOfWeek;          // 0 = Sun
      $day  = 1 - $wDay;                  // start on Sunday box
      for ($w=0; $w<$weeks; $w++):
          echo "<tr>";
          for ($col=0; $col<7; $col++, $day++):
              $cur = cellKey($year,$month,$day);
              $isToday = ($cur === $today->toDateString());

              echo '<td'.($isToday?' class="today"':'').'>';
              if ($day>0 && $day<=$last->day) {
                  echo '<span class="num">'.$day.'</span>';

                  /* announcements */
                  foreach (($calendar[$cur]['ev'] ?? []) as $e)
                      echo '<span class="entry ev">'.e($e->title).'</span>';

                  /* quizzes / activities */
                  foreach (($calendar[$cur]['ac'] ?? []) as $a)
                      echo '<span class="entry ac">'.e($a->activity_name).'</span>';
              }
              echo '</td>';
          endfor;
          echo "</tr>";
      endfor; ?>
  </table>

  <div id="details" style="margin-top:.5rem;font-size:13px;"></div>