<?php
/*  ------------------------------------------------------------------
    This partial is fully self-contained – it pulls everything it needs
    directly from the DB every time it is included.
    ------------------------------------------------------------------*/

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

use App\Models\{
  CalendarEvent,      // table: calendarevent
  Activities,         // table: activity
  Modules,            // table: module   (for course_id lookup)
  LongQuizzes,        // table: longquiz
  Screening           // table: screening (if you have it)
};

/* -------------------------------------------------
 | runtime context
 |-------------------------------------------------*/

$userId    = session('user_id');
$isTeacher = session('role_id') == 2;                 // 2 = teacher in your seed
$base      = $isTeacher ? '/teachers-panel' : '/home-tutor';

/* -------------------------------------------------
 | helpers
 |-------------------------------------------------*/
function activityUrl(Activities $a, bool $isTeacher, string $base): string
{
  $qt = $a->quiz->quiz_type_id;                     // 1 short, 2 practice, 3 long, 4 screening

  if ($qt == 1) {                      // short / practice need course+module
    $courseId = $a->module->course_id;
    return "$base/course/$courseId/module/$a->module_id/quiz/$a->activity_id";
  }

  if ($qt == 2) {                      // short / practice need course+module
    $courseId = $a->module->course_id;
    return "$base/course/$courseId/module/$a->module_id/practicequiz/$a->activity_id";
  }

  if ($qt == 3) {                                  // long quiz
    $courseId = $a->quiz->longquiz->course_id;
    return "$base/course/$courseId/longquiz/$a->activity_id";
  }

  if ($qt == 4) {                                  // screening exam
    $courseId = $a->quiz->screening->course_id;
    return "$base/course/$courseId/screening/$a->activity_id";
  }

  return $base;                                    // fallback
}

/* =================================================
 | 1.  CALENDAR  ───────────────────────────────────*/
$pivot = Carbon::create(
  request('y', Carbon::now()->year),
  request('m', Carbon::now()->month),
  1
);                                          // first day of month
$today = Carbon::today();

/* ---------- announce & activity rows for this month ---------- */
$y = $pivot->year;
$m = $pivot->month;

$calendar = [];

/* announcements (event_type_id = 1) */
CalendarEvent::whereYear('date', $y)
  ->whereMonth('date', $m)
  ->where('event_type_id', 1)             // ANNOUNCEMENT
  ->get()
  ->each(function ($e) use (&$calendar) {
    $key = \Carbon\Carbon::parse($e->date)->format('Y-m-d');
    $calendar[$key]['ev'][] = $e;
  });

/* activities that unlock this month (ignore CATCH_UP type-id 5) */
Activities::whereYear('unlock_date', $y)
  ->whereMonth('unlock_date', $m)
  ->whereHas('quiz', fn($q) => $q->whereNot('quiz_type_id', 5))
  ->get()
  ->each(function ($a) use (&$calendar) {
    $key = \Carbon\Carbon::parse($a->unlock_date)->format('Y-m-d');
    $calendar[$key]['ac'][] = $a;
  });

/* =================================================
 | 2.  NOTIFICATIONS  ─────────────────────────────*/
$now = Carbon::now();

/* A) announcements first (max 10) */
$notifications = CalendarEvent::where('date', '>=', $now)
  ->where('event_type_id', 1)
  ->orderByDesc('is_urgent')
  ->orderBy('date')
  ->take(10)
  ->get()
  ->map(fn($e) => [
    'type' => 'announcement',
    'title' => '[ ' . $e->title . ' ]',
    'date' => Carbon::parse($e->date)->format('M j, Y g:i A'),
    'url'  => "$base/announcement/$e->event_id",
  ]);

/* -------- 1. SHORT + PRACTICE (quiz_type 1 & 2) ------------------*/
$practice = Activities::with(['quiz', 'module'])          // only 1 & 2 live here
  ->where('unlock_date', '<=', $now)
  ->where('deadline_date', '>=', $now)
  ->whereHas('quiz', fn($q) => $q->whereIn('quiz_type_id', [2]))
  ->when(!$isTeacher, function ($q) use ($userId) {
    $q->whereDoesntHave('results', fn($r) => $r->where('student_id', $userId)
      ->where('is_kept', 1));
  })
  ->orderBy('deadline_date')
  ->get()
  ->map(fn($a) => [
    'type' => 'quiz',
    'title' => '[PRAC] ' . $a->activity_name,
    'date' => 'Due: ' . Carbon::parse($a->deadline_date)->format('M j, Y g:i A'),
    'url'  => activityUrl($a, $isTeacher, $base)               // helper from earlier
  ]);

$short = Activities::with(['quiz', 'module'])          // only 1 & 2 live here
  ->where('unlock_date', '<=', $now)
  ->where('deadline_date', '>=', $now)
  ->whereHas('quiz', fn($q) => $q->whereIn('quiz_type_id', [1]))
  ->when(!$isTeacher, function ($q) use ($userId) {
    $q->whereDoesntHave('results', fn($r) => $r->where('student_id', $userId)
      ->where('is_kept', 1));
  })
  ->orderBy('deadline_date')
  ->get()
  ->map(fn($a) => [
    'type' => 'quiz',
    'title' => '[SQ] ' . $a->activity_name,
    'date' => 'Due: ' . Carbon::parse($a->deadline_date)->format('M j, Y g:i A'),
    'url'  => activityUrl($a, $isTeacher, $base)               // helper from earlier
  ]);

/* -------- 2. LONG-QUIZ   (own table) -----------------------------*/
$long = \App\Models\LongQuizzes::query()
  ->where('unlock_date', '<=', $now)
  ->where('deadline_date', '>=', $now)
  ->when(!$isTeacher, function ($q) use ($userId) {
    $q->whereDoesntHave('keptResult', fn($r) => $r->where('student_id', $userId)
      ->where('is_kept', 1));
  })
  ->orderBy('deadline_date')
  ->get()
  ->map(fn($l) => [
    'type' => 'quiz',
    'title' => '[LQ] ' . $l->long_quiz_name,
    'date' => 'Due: ' . Carbon::parse($l->deadline_date)->format('M j, Y g:i A'),
    'url'  => "$base/course/{$l->course_id}/longquiz/{$l->long_quiz_id}",
  ]);

/* -------- 3. SCREENING EXAM  (own table) ------------------------*/
$screen = \App\Models\Screening::query()
  ->when(!$isTeacher, function ($q) use ($userId) {
    $q->whereDoesntHave('results', fn($r) => $r->where('student_id', $userId)
      ->where('is_kept', 1));
  })
  ->get()
  ->map(fn($s) => [
    'type' => 'quiz',
    'title' => '[SCREENING] ' . $s->screening_name,
    'date' => '',
    'url'  => "$base/course/{$s->course_id}/screening/{$s->screening_id}",
  ]);

/* -------- merge: announcements already in $notifications --------*/
$notifications = collect($notifications);
$practice = collect($practice);
$short    = collect($short);
$long     = collect($long);
$screen   = collect($screen);
$notifications = $notifications
    ->concat($practice)
    ->concat($short)
    ->concat($long)
    ->concat($screen)
    ->take(25); 
// announcements remain on top
?>

<div class="content-container box-page">
  <div class="content padding">
    <div class="sidebar-box calendar">
      <?php include __DIR__ . '/calendar-grid.php'; ?>
      <div id="details" style="margin-top:.5rem;font-size:13px;"></div>
    </div>

    <div class="sidebar-box notifications">
      <h4 style="margin:.2rem 0 .6rem">Notifications</h4>
      <?php if ($notifications->isEmpty()): ?>
        <p style="font-size:.8rem;color:#555">No pending items 🎉</p>
      <?php else: ?>

        <?php foreach ($notifications as $n): ?>
          <div class="notif-block">
            <a class="<?= $n['type'] ?>" href="<?= $n['url'] ?>">
              <b class="notif-link"><?= htmlspecialchars($n['title']) ?></b>
            </a>
            <small><?= $n['date'] ?></small>
          </div>
        <?php endforeach; ?>

      <?php endif; ?>
    </div>
  </div>
</div>