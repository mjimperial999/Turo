<?php

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use App\Models\{
  CalendarEvent,
  Activities,
  Modules,
  LongQuizzes,
  Sections,
  Screening,
  CourseSection   // <-- has course_id + section_id (+ teacher_id)
};

$userId   = session('user_id');
$roleId   = (int) session('role_id');           // 1 stu | 2 tch | 3 adm
$now      = Carbon::now();

/* helper ───────────────────────────────────────────────────────── */
function routeTo(array $piece, int $roleId): string
{
  // $piece = [ 'type'=>'short', 'course'=>…, 'section'=>…, 'act'=>… ]
  if ($roleId === 3) {  // ADMIN
    return "/admin-panel/edit-content/course/{$piece['course']}/{$piece['tail']}";
  }

  if ($roleId === 2) {  // TEACHER   (section **required**)
    return "/teachers-panel/course/{$piece['course']}/section/{$piece['section']}/{$piece['tail']}";
  }

  // STUDENT
  return "/home-tutor/course/{$piece['course']}/{$piece['tail']}";
}

$teachSections = CourseSection::where('teacher_id', $userId)
  ->pluck('section_id', 'course_id');   // [ course_id => section_id ]


$items = collect();

$calendar = [];

$add = function (string $day, array $row) use (&$calendar) {
  $calendar[$day][] = $row;
};

CalendarEvent::where('event_type_id', 1)
  ->orderByDesc('is_urgent')
  ->orderBy('date')
  ->get(['title', 'date', 'event_id', 'is_urgent'])
  ->each(function ($e) use ($add, &$items, $roleId) {

    /* ---------- 1) feed the calendar grid ---------- */
    $day = Carbon::parse($e->date)->format('Y-m-d');
    $add($day, [
      'marker' => '‼',
      'class'  => $e->is_urgent ? 'ann urgent' : 'ann',
      'text'   => $e->title,
    ]);

    /* ---------- 2) feed the notifications list ----- */
    $items->push([
      'title' => 'Announcement: ' . $e->title,
      'date'  => Carbon::parse($e->date)->format('M j, Y g:i A'),

      //  role-aware link
      'url'   => match ($roleId) {
        3       => "/admin-panel/announcement/{$e->event_id}",     // admin
        2       => "/teachers-panel/announcement/{$e->event_id}",  // teacher
        default => "/home-tutor/announcement/{$e->event_id}",      // student (or guest)
      },
    ]);
  });

/* ---------- PRACTICE + SHORT (quiz_type 2 / 1) ---------------- */
$quizColours = [2 => 'prc', 1 => 'sht'];               // css classes → colours
Activities::with('quiz', 'module')
  ->whereHas('quiz', fn($q) => $q->whereIn('quiz_type_id', [1, 2]))
  ->get()
  ->each(function ($a) use ($add, $quizColours) {
    $c  = $quizColours[$a->quiz->quiz_type_id];
    $un = Carbon::parse($a->unlock_date)->format('Y-m-d');
    $du = Carbon::parse($a->deadline_date)->format('Y-m-d');
    $add($un, ['marker' => '•', 'class' => $c, 'text' => $a->activity_name]);
    $add($du, ['marker' => '×', 'class' => $c, 'text' => $a->activity_name]);
  });

/* ---------- LONG QUIZZES ------------------------------------- */
LongQuizzes::all()->each(function ($l) use ($add) {
  $un = Carbon::parse($l->unlock_date)->format('Y-m-d');
  $du = Carbon::parse($l->deadline_date)->format('Y-m-d');
  $add($un, ['marker' => '•', 'class' => 'lng', 'text' => $l->long_quiz_name]);
  $add($du, ['marker' => '×', 'class' => 'lng', 'text' => $l->long_quiz_name]);
});


foreach (['practice' => 2, 'short' => 1] as $label => $qt) {

  Activities::with(['quiz', 'module'])
    ->where('unlock_date', '<=', $now)
    ->where('deadline_date', '>=', $now)
    ->whereHas('quiz', fn($q) => $q->where('quiz_type_id', $qt))
    ->when(
      $roleId === 1,
      fn($q) =>                        // students: hide if kept
      $q->whereDoesntHave(
        'results',
        fn($r) => $r->where('student_id', $userId)
          ->where('is_kept', 1)
      )
    )
    ->orderBy('deadline_date')
    ->get()
    ->each(function ($a) use (
      $label,
      $roleId,
      $teachSections,
      &$items
    ) {
      $course   = $a->module->course_id;
      $section  = $teachSections[$course] ?? null;
      $secName  = $section
        ? Sections::find($section)->section_name
        : '';

      /* -------- build URL PER-ROLE -------- */
      if ($roleId === 1) {                    // ── STUDENT
        $url = "/home-tutor/course/$course/" .
          "module/{$a->module_id}/quiz/{$a->activity_id}";
      } elseif ($roleId === 2) {              // ── TEACHER
        $url = "/teachers-panel/course/$course/section/$section/" .
          "module/{$a->module_id}/" .
          ($label === 'practice' ? 'practicequiz' : 'shortquiz') .
          "/{$a->activity_id}";
      } else {                                // ── ADMIN
        $url = "/admin-panel/edit-content/course/$course/" .
          "module/{$a->module_id}/" .
          ($label === 'practice' ? 'practicequiz' : 'shortquiz') .
          "/{$a->activity_id}";
      }

      /* -------- push the row into the collection -------- */
      $items->push([
        'title' => ($secName ? "[$secName] " : '') .
          '[' . strtoupper($label[0]) .
          ($label === 'short' ? 'Q' : '') . "] " .
          $a->activity_name,
        'date'  => 'Due: ' .
          Carbon::parse($a->deadline_date)
          ->format('M j, Y g:i A'),
        'url'   => $url,
      ]);
    });
}

/* === C. Long-Quizzes ========================================= */
LongQuizzes::where('unlock_date', '<=', $now)
  ->where('deadline_date', '>=', $now)
  ->when($roleId === 1, fn($q) => $q->whereDoesntHave(
    'keptResult',
    fn($r) => $r->where('student_id', $userId)->where('is_kept', 1)
  ))
  ->orderBy('deadline_date')
  ->get()
  ->each(function ($l) use (&$items, $teachSections, $roleId) {
    $course   = $l->course_id;
    $section  = $teachSections[$course] ?? null;
    $secName  = $section ? \App\Models\Sections::find($section)->section_name : '';

    $items->push([
      'title' => ($secName ? "[$secName] " : '') . '[LQ] ' . $l->long_quiz_name,
      'date' => 'Due: ' . Carbon::parse($l->deadline_date)->format('M j, Y g:i A'),
      'url'  => routeTo([
        'course' => $course,
        'section' => $section,
        'tail' => "longquiz/{$l->long_quiz_id}"
      ], $roleId)
    ]);
  });

/* === D. Screening Exams (always available) ==================== */
Screening::all()
  ->each(function ($s) use (&$items, $teachSections, $roleId) {
    $course  = $s->course_id;
    $section = $teachSections[$course] ?? null;
    $secName = $section ? \App\Models\Sections::find($section)->section_name : '';

    if ($roleId === 1) {
      $items->push([
        'title' => ($secName ? "[$secName] " : '') . '[SCREENING] ' . $s->screening_name,
        'date' => '',
        'url'  => routeTo([
          'course' => $course,
          'section' => $section,
          'tail' => "{$s->screening_id}"
        ], $roleId)
      ]);
    } else {
      $items->push([
        'title' => ($secName ? "[$secName] " : '') . '[SCREENING] ' . $s->screening_name,
        'date' => '',
        'url'  => routeTo([
          'course' => $course,
          'section' => $section,
          'tail' => "screening/{$s->screening_id}"
        ], $roleId)
      ]);
    }
  });

$notifications = $items->take(25);

/* ---- decorate every row once, right here  ----------------- */
$icon   = [
  'ann' => 'bell',        // announcement
  'prc' => 'practice-quiz',
  'sht' => 'short-quiz',
  'lng' => 'long-quiz',
  'scr' => 'screener'
];
$colour = [
  'ann' => '#c0392b',     // red
  'prc' => '#e67e22',     // orange
  'sht' => '#2e7d32',     // green
  'lng' => '#1565c0',     // blue
  'scr' => '#555555'      // grey
];

$notifications = $notifications->map(function ($n) use ($icon, $colour) {

  /* ---------- recognise the category from the prefix ---------- */
  $title = ($n['title']);          // normalise once

  switch (true) {
    case str_contains($title, 'Announcement:'):
      $cat = 'ann';
      break;

    case str_contains($title, '[P]'):      // Practice
      $cat = 'prc';
      break;

    case str_contains($title, '[SQ]'):     // Short-Quiz
      $cat = 'sht';
      break;

    case str_contains($title, '[LQ]'):     // Long-Quiz
      $cat = 'lng';
      break;

    default:                               // Screening or anything else
      $cat = 'scr';
  }  // screening (fallback)

  /* ---------- add colour + icon keys (always safe) ------------ */
  $n['icon']   = "/icons/{$icon[$cat]}.svg";
  $n['colour'] = $colour[$cat];

  return $n;      // collection → new enriched row
});


?>

<div class="content-container box-page">
  <div class="content padding">
    <div class="sidebar-box calendar">
      <?php include __DIR__ . '/calendar-grid.php'; ?>
      <div id="details" style="margin-top:.5rem;font-size:13px;"></div>
    </div>

    <div class="sidebar-box notifications">
      <h4 style="margin:.6rem 0">Tasks and Announcements</h4>
      <?php if ($notifications->isEmpty()): ?>
        <p style="font-size:.8rem;color:#555">No pending items 🎉</p>
      <?php else: ?>
        <?php foreach ($notifications as $n): ?>
          <div class="notif-block">
            <div class="notif-url">
              <img
                src="<?= $n['icon'] ?>"
                width="20em" height="auto"
                style="vertical-align:middle;margin-right:.25rem">
              <a href="<?= $n['url'] ?>" class="notif-link" style="color:<?= $n['colour'] ?>;">
                <b><?= htmlspecialchars($n['title']) ?></b>
              </a>
            </div>
            <?php if ($n['date']): ?>
              <small><?= $n['date'] ?></small>
            <?php endif; ?>
          </div>
        <?php endforeach; ?>
      <?php endif; ?>
    </div>
  </div>
</div>