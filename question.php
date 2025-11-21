<?php
session_start();

// retrieve question info
$cat = $_SESSION['current_category'];
$qnum = $_SESSION['current_qnum'];
$question = $_SESSION['board'][$cat][$qnum];

// shuffle answers
$answers = $question['incorrect_answers'];
$answers[] = $question['correct_answer'];
shuffle($answers);

$key = array_search($_SESSION['current_category'], array_column($_SESSION['categories'], 'id'));
$is_daily_double = in_array($key * 5 + $qnum, $_SESSION['daily_double']);

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['answer'])) {

    $is_correct = ($_POST['answer'] === $question['correct_answer']);
    $cur_player = $_SESSION['current_player'];

    $value = ($qnum + 1) * 200 * ($_SESSION['round'] ?? 1);

    $multiplier = ($_SESSION['players'][$cur_player]['streak'] >= 2) ? 2 : 1;

    if ($is_daily_double && isset($_POST['wager'])) {
        $wager = intval($_POST['wager']);
        if ($is_correct) {
            $_SESSION['players'][$cur_player]['score'] += $wager * $multiplier;
        } else {
            $_SESSION['players'][$cur_player]['score'] -= $wager;
        }
    } else {
        if ($is_correct) {
            $_SESSION['players'][$cur_player]['score'] += $value * $multiplier;
            $_SESSION['players'][$cur_player]['streak'] += 1;
        } else {
            $_SESSION['players'][$cur_player]['score'] -= $value;
            $_SESSION['players'][$cur_player]['streak'] = 0;
        }
    }

    $unique_key = $key * 5 + $qnum;
    $_SESSION['answered_questions'][$unique_key] = true;

    $_SESSION['current_player'] = ($_SESSION['current_player'] + 1) % count($_SESSION['players']);
    header('Location: gameboard.php');
    exit();
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Question</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body class="gameboard-screen">
    <div class="gameboard-container">
        <div class="question-form">
            <form method="POST" action="question.php">
                <div class="question-box scale-in">
                    <div class="category">
                        <?php echo htmlspecialchars($question['category']); ?>
                    </div>

                    <div class="question">
                        <?php echo htmlspecialchars($question['question']); ?>
                    </div>

                    <?php if ($is_daily_double): ?>
                        <div>
                            <label for="wager">Daily Double! Enter wager ($):</label>
                            <input type="number" name="wager" min="1"
                                max="<?php echo $_SESSION['players'][$_SESSION['current_player']]['score']; ?>"
                                required>
                        </div>
                    <?php endif; ?>

                    <div class="answers">
                    <?php foreach ($answers as $ans): ?>
                        <button type="submit" name="answer"
                            value="<?php echo htmlspecialchars($ans); ?>"
                            class="game-button glow-hover">
                            <?php echo htmlspecialchars($ans); ?>
                        </button>
                    <?php endforeach; ?>
                    </div>
                </div>
            </form>
        </div>
    </div>
</body>
</html>
