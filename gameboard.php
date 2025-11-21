<?php
session_start();
require_once('opentdb.php');

// Initialize gameboard for each round
if (!isset($_SESSION['board'])) {

    // Retrieve categories
    $categories = fetch_categories();
    shuffle($categories);
    $chosen_categories = array_slice($categories, 0, 6);

    $_SESSION['categories'] = $chosen_categories;

    // Generate 5 questions for each selected category
    $_SESSION['board'] = [];
    foreach ($chosen_categories as $cat) {
        $questions = fetch_questions($cat['id'], 5, 'medium');

        // If not enough questions, insert placeholder
        while (count($questions) < 5) {
            $questions[] = [
                "category" => $cat['name'],
                "question" => "[No question available]",
                "correct_answer" => "",
                "incorrect_answers" => [],
            ];
        }
        $_SESSION['board'][$cat['id']] = $questions;
    }

    // Pick 2 Daily Doubles
    $_SESSION['daily_double'] = get_random_indices(30, 2);
}

// Handle clicking on a question cell
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['cell'])) {
    list($catID, $qnum) = explode(':', $_POST['cell']);

    $_SESSION['current_category'] = $catID;
    $_SESSION['current_qnum'] = $qnum;

    header('Location: question.php');
    exit();
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Jeopardy Board</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body class="gameboard-screen">
    <div class="gameboard-container scale-in">
        <div class="game-header">
            <h2 class="round-title pulse-animation">
                Round <?php echo $_SESSION['round'] ?? 1; ?>
            </h2>
        </div>

        <div class="board-wrapper">
            <form method="POST" action="gameboard.php">
                <div class="game-board">

                <!-- Category names -->
                <?php foreach ($_SESSION['categories'] as $cat): ?>
                    <div class="category-header scale-in shimmer-animation">
                        <?php echo htmlspecialchars($cat['name']); ?>
                    </div>
                <?php endforeach; ?>

                <!-- 5 rows of questions -->
                <?php
                for ($row = 0; $row < 5; $row++) {
                    foreach ($_SESSION['categories'] as $catIdx => $catData) {

                        $catID = $catData['id'];
                        $question = $_SESSION['board'][$catID][$row];

                        $key = $catIdx * 5 + $row;
                        $answered = isset($_SESSION['answered_questions'][$key]);
                        $is_dd = in_array($key, $_SESSION['daily_double']);
                        $value = ($row + 1) * 200 * ($_SESSION['round'] ?? 1);

                        $not_available = ($question['question'] == "[No question available]");
                        $is_disabled = $answered || $not_available;

                        $cellValue = $catID . ':' . $row;
                        ?>

                        <button
                            type="submit"
                            name="cell"
                            value="<?php echo $cellValue; ?>"
                            class="question-box <?php echo $answered ? 'answered' : 'available'; ?> scale-in"
                            <?php echo $is_disabled ? 'disabled' : ''; ?>
                        >
                            <?php echo $not_available ? 'N/A' : $value; ?>
                            <?php if ($is_dd) echo '<span class="dd-indicator shimmer-animation">DD</span>'; ?>
                        </button>

                    <?php } // end foreach category
                } // end for rows
                ?>

                </div>
            </form>
        </div>

        <!-- Player panels -->
        <div class="player-panels">
        <?php
        foreach ($_SESSION['players'] as $pid => $pdata) {
            echo '<div class="player-card ' . ($pid == $_SESSION['current_player'] ? 'active-player' : '') . '">';
            echo '<div class="player-name">' . htmlspecialchars($pdata['name']) . '</div>';
            echo '<div class="player-score">' . htmlspecialchars($pdata['score']) . '</div>';
            echo '</div>';
        }
        ?>
        </div>
    </div>
</body>
</html>
