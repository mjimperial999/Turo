<?php
// Force browser not to cache
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");
?>

<?php
$title = 'Question ' . $index + 1;
include __DIR__ . '/../partials/head.php';

use Illuminate\Support\Facades\Session;

?>
<style>
    table,
    th,
    td {
        border: 0.04em solid #C9C9C9;
        border-collapse: collapse;
    }

    table {
        width: 100%;
    }

    .table-left-padding {
        width: 2em;
    }

    .table-right-padding {
        padding: 1em 1.5em;
    }

    .quiz-interface {
        font-family: Albert-Sans, sans-serif;

        display: flex;
        flex-direction: column;

        padding: 1.5rem 2rem;

        width: 100%;
    }

    .quiz-interface-header {
        width: 100%;
        display: flex;
        flex-direction: row;
        justify-content: space-between;

        font-weight: 700;
    }

    .short-quiz {
        color: #2E4127;
    }

    .quiz-interface-header-logo {
        display: flex;
        flex-direction: row;

        padding: 0rem 1rem;
    }

    .quiz-interface-header-right-side {
        display: flex;
        flex-direction: row;

        line-height: 1.2;
    }

    .quiz-interface-header-question-total {
        display: flex;
        flex-direction: column;

        text-align: right;
    }

    .quiz-interface-header-logo img {
        transform: rotate(6deg) scale(2) translate(0.5rem, -0.8rem);
        filter: drop-shadow(0 0.1rem 0.1rem rgba(0, 0, 0, 0.2));
    }

    .quiz-interface p,
    .quiz-interface b {
        margin: 0;
        padding: 0;
    }

    #quiz-timer {
        color: #495a43;
    }

    .quiz-interface-question {
        font-weight: 600;
    }

    .quiz-interface-forms {
        width: 100%;
        display: flex;
        flex-direction: column;
        align-items: center;
        margin-top: 1rem;
    }

    .quiz-interface-answers {
        width: 100%;
        display: flex;
        flex-direction: row;
        gap: 2rem;
        padding: 1rem 0;
    }

    .radio-button {
        background:
            linear-gradient(135deg, rgb(247, 247, 247) 0%, rgb(237, 237, 237) 100%) padding-box,
            linear-gradient(90deg, rgb(211, 211, 211) 0%, rgb(199, 199, 199) 100%) border-box;
        border: 0.08rem solid transparent;
        border-radius: 0.5rem;

        position: relative;
        transition: all 0.1s ease;
    }

    .radio-button input[type="radio"] {
        appearance: none;
        -webkit-appearance: none;
        opacity: 0;

        display: block;
        position: absolute;
        width: 100%;
        height: 100%;

        padding: 1rem;
        cursor: pointer;
    }

    .radio-button label {
        display: block;
        width: 100%;
        height: 100%;
        padding: 1rem;
    }

    .radio-button.selected {
        border: 0;
        box-shadow: rgba(0, 0, 0, 0.24) 0rem 0.18rem 0.5rem;
        color: white;
    }

    .radio-short-quiz.radio-button.selected {
        background: linear-gradient(135deg, rgb(153, 240, 99) 10%, rgb(64, 183, 1) 100%);
    }

    .radio-practice-quiz.radio-button.selected {
        background: linear-gradient(135deg, rgb(240, 181, 99) 10%, rgb(183, 89, 1) 100%);
    }

    .radio-long-quiz.radio-button.selected {
        background: linear-gradient(135deg, rgb(99, 169, 240) 10%, rgb(1, 92, 183) 100%);
    }

    .quiz-interface-submit {
        margin-top: 1.5rem;
        width: 6rem;
        height: 2.8rem;
        border: 0;
        border-radius: 0.4rem;
        filter: drop-shadow(0 0.1rem 0.1rem rgba(0, 0, 0, 0.2));
        color: #FFFFFF;

        display: flex;
        flex-direction: row;
        justify-content: center;
        align-items: center;
        transition: all 0.3s ease 0s;
    }

    .quiz-interface-submit:hover {
        text-decoration: underline;
        cursor: pointer;
        transition: all 0.3s ease 0s;
    }

    .quiz-nav-number {
        text-decoration: none;
        color: white;
        font-weight: bold;
    }
</style>

</head>

<body>
    <?php
    include __DIR__ . '/../partials/nav.php';
    include __DIR__ . '/../partials/quiz-type-check.php';
    include __DIR__ . '/../partials/time-lock-check.php';
    include __DIR__ . '/../partials/time-limit-conversion.php';
    ?>

    <div class="screen">
        <div class="spacing main">
            <div class="content-container box-gold">

                <div class="content padding">
                    <div class="header logo-sub">
                        <div class="logo-and-title">
                            <div class="logo">
                                <img class="svg" src="/icons/<?= $class ?>.svg" width="50em" height="auto" />
                            </div>
                            <div class="text title">
                                <h4><?= $activity->activity_name ?></h4>
                                <h6><?= $quiz_type ?></h6>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <br>

            <div class="content-container box-page">
                <div class="content">
                    <div class="module-section quiz-interface quiz-background <?= $class ?>">
                        <div class="quiz-interface-header">
                            <div class="quiz-interface-header-question-number">
                                <p>QUESTION <?= $index + 1 ?></p>
                            </div>
                            <div class="quiz-interface-header-right-side">
                                <div class="quiz-interface-header-question-total">
                                    <p>Q<?= $index + 1 ?> OF <?= $total ?></p>
                                    <p>Time Left: <span id="quiz-timer">--:--</span></p>
                                </div>
                                <div class="quiz-interface-header-logo">
                                    <img class="svg" src="/icons/<?= $class ?>.svg" width="50em" height="auto" />
                                </div>
                            </div>
                        </div>
                        <div class="quiz-interface-question">
                            <p><?= nl2br(htmlspecialchars($question->question_text)) ?></p>
                            <?php
                            if (empty($question->questionimage?->image)) {;
                            } else {
                                $blobData = $question->questionimage?->image;
                                $mimeType = getMimeTypeFromBlob($blobData);
                                $base64Image = base64_encode($blobData);
                                $imageURL = "data:$mimeType;base64,$base64Image";
                                echo '<img src="' . $imageURL . '" width="250em" height="auto" />';
                            }
                            ?>
                        </div>
                        <form class="quiz-interface-forms" method="POST" action="/home-tutor/course/<?= $course->course_id ?>/module/<?= $module->module_id ?>/quiz/<?= $activity->activity_id ?>/s/q/<?= $index ?>">
                            <?= csrf_field() ?>
                            <div class="quiz-interface-answers">
                                <?php if ($question->question_type_id === 1): ?>
                                    <?php $question->setRelation(
                                        'options',
                                        $question->options->shuffle()
                                    );
                                    $opts = $question->options->shuffle();
                                    foreach ($opts as $option):
                                        $savedAnswer = Session::get("quiz_{$activity->activity_id}_answers")[$index] ?? null; ?>
                                        <div class="radio-button radio-<?= $class ?>">
                                            <input type="radio"
                                                id="opt<?= $option->option_id ?>"
                                                name="answer"
                                                value="<?= $option->option_id ?>"
                                                <?= ($savedAnswer == $option->option_id) ? 'checked' : '' ?>
                                                required>
                                            <label for="opt<?= $option->option_id ?>"><?= $option->option_text ?></label>
                                        </div>
                                    <?php endforeach; ?>
                                <?php else:
                                    $savedAnswer = Session::get("quiz_{$activity->activity_id}_raw_text_answers")[$index] ?? ''; ?>
                                    <div class="identification-input">
                                        <input
                                            type="text"
                                            id="answer_text"
                                            name="answer_text"
                                            value="<?= e($savedAnswer) ?>"
                                            required
                                            autocomplete="off" />
                                    </div>
                                <?php endif; ?>
                            </div>
                            <button type="submit" class="quiz-interface-submit <?= $buttonClass ?>">
                                <?= ($index + 1 < $total) ? 'NEXT' : 'SUBMIT' ?>
                            </button>
                        </form>
                    </div>
                </div>
            </div>

            <div class="content-container box-page">
                <div class="content padding">
                    <div class="header">
                        <h6>Question List</h6>
                        <br>
                    </div>
                    <div class="question-nav-list" style="display: flex; flex-wrap: wrap; gap: .5rem;">
                        <?php
                        $answers = Session::get("quiz_{$activity->activity_id}_answers", []);
                        ?>
                        <?php for ($i = 0; $i < $total; $i++): ?>
                            <?php
                            $isCurrent = ($i === $index);
                            $isAnswered = array_key_exists($i, $answers);
                            $classes = 'quiz-nav-number';
                            $styles = 'display:inline-block; width:2.5rem; height:2.5rem; line-height:2.5rem; text-align:center; border-radius:4px; font-weight:bold;';

                            if ($isCurrent) {
                                $styles .= 'background:#2d91f2; color:white; border: 2px solid #2d91f2;';
                            } elseif ($isAnswered) {
                                $styles .= 'background:#ffffff; color:#333; border:1px solid #aaa;';
                            } else {
                                $styles .= 'background:#eee; color:#999; border:1px solid #ccc;';
                            }
                            ?>

                            <?php if ($isAnswered): ?>
                                <a href="/home-tutor/course/<?= $course->course_id ?>/module/<?= $module->module_id ?>/quiz/<?= $activity->activity_id ?>/s/q/<?= $i ?>"
                                    class="<?= $classes ?>"
                                    style="<?= $styles ?>">
                                    <?= $i + 1 ?>
                                </a>
                            <?php else: ?>
                                <span class="<?= $classes ?>" style="<?= $styles ?>">
                                    <?= $i + 1 ?>
                                </span>
                            <?php endif; ?>
                        <?php endfor; ?>
                    </div>
                </div>
            </div>


        </div>

    </div>
    <?php include __DIR__ . '/../partials/footer.php'; ?>
</body>

<script>
    console.log('Hello');

    let secondsLeft = <?= $remainingSeconds ?>;
    const timerElement = document.getElementById('quiz-timer');
    const form = document.querySelector('form');

    function updateTimer() {
        const minutes = Math.floor(secondsLeft / 60);
        const seconds = secondsLeft % 60;
        timerElement.textContent = `${minutes}:${seconds.toString().padStart(2, '0')}`;

        if (secondsLeft > 0) {
            secondsLeft--;
            setTimeout(updateTimer, 1000);
        } else {
            autoSubmitQuiz();
        }
    }

    function autoSubmitQuiz() {
        const selected = document.querySelector('input[name="answer"]:checked');
        const form = document.querySelector('form');

        const input = document.createElement("input");
        input.type = "hidden";
        input.name = "auto_submit";
        input.value = "1";
        form.appendChild(input);

        if (!selected) {
            const blank = document.createElement("input");
            blank.type = "hidden";
            blank.name = "answer";
            blank.value = "";
            form.appendChild(blank);
        }

        form.submit();
    }

    updateTimer();

    const updateRadioVisuals = () => {
        document.querySelectorAll('.radio-button').forEach(div => {
            const radio = div.querySelector('input[type="radio"]');
            if (radio.checked) {
                div.classList.add('selected');
            } else {
                div.classList.remove('selected');
            }
        });
    };

    document.querySelectorAll('input[type="radio"][name="answer"]').forEach(radio => {
        radio.addEventListener('click', updateRadioVisuals);
    });

    // Run on page load
    updateRadioVisuals();

    const radioButtons = document.querySelectorAll('input[type="radio"][name="answer"]');

    radioButtons.forEach(radio => {
        radio.addEventListener('change', () => {
            document.querySelectorAll('.radio-button').forEach(div => {
                div.classList.remove('selected');
            });
            if (radio.checked) {
                radio.closest('.radio-button').classList.add('selected');
            }
        });
    });
</script>


</html>