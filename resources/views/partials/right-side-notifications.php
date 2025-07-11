<?php

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use App\Models\{
    CalendarEvent,
    Activities,
    Modules,
    LongQuizzes,
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

/* -------------------------------------------------
 | collect the teacher’s sections  (for fast lookup)
 * ------------------------------------------------*/
$teachSections = CourseSection::where('teacher_id', $userId)
    ->pluck('section_id', 'course_id');   // [ course_id => section_id ]

/* -------------------------------------------------
 | build notifications (max 25) – announcements first
 * ------------------------------------------------*/

$items = collect();

/* === A. Announcements (event_type_id = 1) ====================== */
CalendarEvent::where('date', '>=', $now)
  ->where('event_type_id', 1)
  ->orderByDesc('is_urgent')
  ->orderBy('date')
  ->take(10)
  ->get()
  ->each(function ($ev) use (&$items, $roleId) {
      $items->push([
        'title' => '[ANN] ' . $ev->title,
        'date'  => Carbon::parse($ev->date)->format('M j, Y g:i A'),
        'url'   => ($roleId === 3       // admin page lives elsewhere
                    ? "/admin-panel/announcement/{$ev->event_id}"
                    : "/home-tutor/announcement/{$ev->event_id}")
      ]);
  });


foreach (['practice' => 2, 'short' => 1] as $label => $qt) {

    Activities::with(['quiz', 'module'])
        ->where('unlock_date', '<=', $now)
        ->where('deadline_date', '>=', $now)
        ->whereHas('quiz', fn ($q) => $q->where('quiz_type_id', $qt))
        ->when($roleId === 1, fn ($q) =>                        // students: hide if kept
            $q->whereDoesntHave('results',
                fn ($r) => $r->where('student_id', $userId)
                              ->where('is_kept', 1))
        )
        ->orderBy('deadline_date')
        ->get()
        ->each(function ($a) use (
            $label, $roleId, $teachSections, &$items
        ) {
            $course   = $a->module->course_id;
            $section  = $teachSections[$course] ?? null;
            $secName  = $section
                        ? \App\Models\Sections::find($section)->section_name
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
                           \Carbon\Carbon::parse($a->deadline_date)
                               ->format('M j, Y g:i A'),
                'url'   => $url,
            ]);
        });
}

/* === C. Long-Quizzes ========================================= */
LongQuizzes::where('unlock_date','<=',$now)
  ->where('deadline_date','>=',$now)
  ->when($roleId===1, fn($q)=>$q->whereDoesntHave('keptResult',
        fn($r)=>$r->where('student_id',$userId)->where('is_kept',1)))
  ->orderBy('deadline_date')
  ->get()
  ->each(function ($l) use (&$items,$teachSections,$roleId){
      $course   = $l->course_id;
      $section  = $teachSections[$course] ?? null;
      $secName  = $section ? \App\Models\Sections::find($section)->section_name : '';

      $items->push([
        'title'=> ($secName?"[$secName] ":'').'[LQ] '.$l->long_quiz_name,
        'date' =>'Due: '.Carbon::parse($l->deadline_date)->format('M j g:i A'),
        'url'  => routeTo([
                    'course'=>$course,
                    'section'=>$section,
                    'tail'=>"longquiz/{$l->long_quiz_id}"
                ], $roleId)
      ]);
  });

/* === D. Screening Exams (always available) ==================== */
Screening::all()
  ->each(function ($s) use (&$items,$teachSections,$roleId){
      $course  = $s->course_id;
      $section = $teachSections[$course] ?? null;
      $secName = $section ? \App\Models\Sections::find($section)->section_name : '';

      if ($roleId === 1){
      $items->push([
        'title'=> ($secName?"[$secName] ":'').'[SCREENING] '.$s->screening_name,
        'date' => '',
        'url'  => routeTo([
                    'course'=>$course,
                    'section'=>$section,
                    'tail'=>"{$s->screening_id}"
                ], $roleId)
      ]);
      } else {
      $items->push([
        'title'=> ($secName?"[$secName] ":'').'[SCREENING] '.$s->screening_name,
        'date' => '',
        'url'  => routeTo([
                    'course'=>$course,
                    'section'=>$section,
                    'tail'=>"screening/{$s->screening_id}"
                ], $roleId)
      ]); }
  });

/* keep announcements first, max 25 overall */
$notifications = $items->take(25);
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
              <a href="<?= $n['url'] ?>">
                <b class="notif-link"><?= htmlspecialchars($n['title']) ?></b>
              </a>
              <?php if ($n['date']): ?><small><?= $n['date'] ?></small><?php endif; ?>
            </div>
          <?php endforeach; ?>

        <?php endif; ?>
      </div>
  </div>
</div>