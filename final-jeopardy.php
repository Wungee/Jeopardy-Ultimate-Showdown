<?php
session_start();
require_once('opentdb.php');

if (!isset($_SESSION['final_question'])) {
    // Pull a random category and question
    $categories = fetch_categories();
    $cat        = $categories[array_rand($categories)];
    $_SESSION['final_category'] = $cat;
    $questions = fetch_questions($cat['id'], 1, 'hard');
    $_SESSION['final_question'] = $questions[0];
    $_SESSION['final_wagers']   = [];
    $_SESSION['final_answers']  = [];
}

// Wager submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['wager'])) {
        $_SESSION['final_wagers'][$_POST['player_id']] = intval($_POST['wager']);
    }
    if (isset($_POST['answer'])) {
        $_SESSION['final_answers'][$_POST['player_id']] = $_POST['answer'];
        // Once all answers submitted, compute scores!
        if (count($_SESSION['final_answers']) >= count($_SESSION['players'])) {
            foreach ($_SESSION['players'] as $pid => &$pdata) {
                $wager = $_SESSION['final_wagers'][$pid];
                $is_correct = ($_SESSION['final_answers'][$pid] === $_SESSION['final_question']['correct_answer']);
                if ($is_correct) $pdata['score'] += $wager;
                else $pdata['score'] -= $wager;
            }
            header('Location: results.php');
            exit();
        }
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Final Jeopardy</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body class="gameboard-screen">
    <div class="gameboard-container bounce-animation" style="border: 5px solid gold; background: #1a1f4d;">
        <h2 class="round-title shimmer-animation pulse-animation">Final Jeopardy!</h2>
        <div class="category-header scale-in">
            Category: <?php echo $_SESSION['final_category']['name']; ?>
        </div>
        <div class="question-box scale-in">
            <form method="POST" action="final-jeopardy.php">
                <div>
                    <?php echo htmlspecialchars($_SESSION['final_question']['question']); ?>
                </div>
                <!-- Each player wager & answer -->
                <?php
                foreach ($_SESSION['players'] as $pid=>$pdata):
                    if (!isset($_SESSION['final_wagers'][$pid])): ?>
                    <label>Wager for <?php echo $pdata['name']; ?>: </label>
                    <input type="number" name="wager" min="1" max="<?php echo $pdata['score']; ?>" required>
                    <input type="hidden" name="player_id" value="<?php echo $pid; ?>">
                    <button type="submit">Submit Wager</button>
                    <?php elseif (!isset($_SESSION['final_answers'][$pid])): ?>
                    <label>Answer for <?php echo $pdata['name']; ?>: </label>
                    <input type="text" name="answer" required>
                    <input type="hidden" name="player_id" value="<?php echo $pid; ?>">
                    <button type="submit">Submit Answer</button>
                <?php endif; endforeach; ?>
            </form>
        </div>
    </div>
</body>
</html>