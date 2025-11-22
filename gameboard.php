<?php
session_start();
require_once('opentdb.php');

// Redirect if no players set up
if (!isset($_SESSION['players']) || count($_SESSION['players']) < 2) {
    header('Location: player-setup.php');
    exit();
}

// Initialize round if not set
if (!isset($_SESSION['round'])) {
    $_SESSION['round'] = 1;
}

// Initialize current player if not set
if (!isset($_SESSION['current_player'])) {
    $_SESSION['current_player'] = 0;
}

// Initialize answered questions tracker
if (!isset($_SESSION['answered_questions'])) {
    $_SESSION['answered_questions'] = [];
}

// Initialize gameboard for the first time or when resetting
if (!isset($_SESSION['board']) || !isset($_SESSION['categories'])) {
    
    // Retrieve categories from API
    $all_categories = fetch_categories();
    
    if (empty($all_categories)) {
        die("Error: Unable to fetch categories from API. Please check your internet connection.");
    }
    
    // Shuffle and select 5 random categories
    shuffle($all_categories);
    $chosen_categories = array_slice($all_categories, 0, 5);
    
    $_SESSION['categories'] = $chosen_categories;
    $_SESSION['board'] = [];
    
    // Generate 1 question for each selected category (5 total)
    foreach ($chosen_categories as $cat) {
        $questions = fetch_questions($cat['id'], 1, 'medium');
        
        // Ensure we have exactly 1 question (use fallback if needed)
        if (empty($questions)) {
            $fallback = get_fallback_questions($cat['id'], 1);
            $questions = $fallback;
        }
        
        // Limit to exactly 1 question
        $questions = array_slice($questions, 0, 1);
        
        $_SESSION['board'][$cat['id']] = $questions;
    }
    
    // Pick 1 random Daily Double position (out of 5 total questions)
    $_SESSION['daily_double'] = get_random_indices(5, 1);
}

// Handle question cell selection
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['cell'])) {
    list($catID, $qnum) = explode(':', $_POST['cell']);
    
    $_SESSION['current_category'] = intval($catID);
    $_SESSION['current_qnum'] = intval($qnum);
    
    header('Location: question.php');
    exit();
}

// Check if all questions have been answered
$total_answered = count($_SESSION['answered_questions']);
if ($total_answered >= 5) {
    // All questions answered, go to results or round 2
    if ($_SESSION['round'] == 1) {
        // Option to go to Round 2
        $_SESSION['round'] = 2;
        unset($_SESSION['board']);
        unset($_SESSION['categories']);
        $_SESSION['answered_questions'] = [];
        header('Location: gameboard.php');
        exit();
    } else {
        // Game complete, go to results
        header('Location: results.php');
        exit();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Jeopardy - Round <?php echo $_SESSION['round']; ?></title>
    <link rel="stylesheet" href="styles.css">
</head>
<body class="setup-screen">
    <div class="gameboard-container scale-in">
        
        <!-- Game Header -->
        <div class="game-header">
            <h2 class="round-title pulse-animation">
                ROUND <?php echo $_SESSION['round']; ?>
            </h2>
            <p style="color: var(--gold); font-size: 1.2rem; margin-top: 0.5rem;">
                Questions Remaining: <?php echo 5 - $total_answered; ?>
            </p>
        </div>

        <!-- Game Board -->
        <div class="board-wrapper">
            <form method="POST" action="gameboard.php">
                <div class="game-board" style="grid-template-columns: repeat(5, 1fr); grid-template-rows: 50px 110px;">

                    <!-- Category Headers (5 columns) -->
                    <?php foreach ($_SESSION['categories'] as $cat): ?>
                        <div class="category-header scale-in shimmer-animation">
                            <?php echo htmlspecialchars($cat['name']); ?>
                        </div>
                    <?php endforeach; ?>

                    <!-- Question Cells (1 row × 5 columns = 5 questions) -->
                    <?php
                    for ($row = 0; $row < 1; $row++) {
                        foreach ($_SESSION['categories'] as $catIdx => $catData) {
                            
                            $catID = $catData['id'];
                            $question = $_SESSION['board'][$catID][$row];
                            
                            // Calculate unique key for this question (0-4)
                            $key = $catIdx * 1 + $row;
                            
                            // Check if already answered
                            $answered = isset($_SESSION['answered_questions'][$key]);
                            
                            // Check if this is a Daily Double
                            $is_dd = in_array($key, $_SESSION['daily_double']);
                            
                            // Calculate point value (all questions same value, multiplied by round)
                            $value = 500 * $_SESSION['round'];
                            
                            // Check if question is available
                            $not_available = (isset($question['question']) && $question['question'] == "[No question available]");
                            $is_disabled = $answered || $not_available;
                            
                            // Create cell value for form submission
                            $cellValue = $catID . ':' . $row;
                            ?>

                            <button
                                type="submit"
                                name="cell"
                                value="<?php echo $cellValue; ?>"
                                class="question-box <?php echo $answered ? 'answered' : 'available'; ?> scale-in"
                                <?php echo $is_disabled ? 'disabled' : ''; ?>
                                style="animation-delay: <?php echo ($catIdx * 0.05 + $row * 0.1); ?>s;"
                            >
                                <?php if (!$answered && !$not_available): ?>
                                    <?php echo $value; ?>
                                    <?php if ($is_dd): ?>
                                        <span class="dd-indicator shimmer-animation">DD</span>
                                    <?php endif; ?>
                                <?php elseif ($not_available): ?>
                                    N/A
                                <?php endif; ?>
                            </button>

                        <?php } // end foreach category ?>
                    <?php } // end for rows ?>

                </div>
            </form>
        </div>

        <!-- Player Panels -->
        <div class="player-panels">
            <?php foreach ($_SESSION['players'] as $pid => $pdata): ?>
                <div class="player-card <?php echo ($pid == $_SESSION['current_player']) ? 'active-player' : ''; ?>">
                    <div class="player-name"><?php echo htmlspecialchars($pdata['name']); ?></div>
                    <div class="player-score">$<?php echo number_format($pdata['score']); ?></div>
                    <?php if (isset($pdata['streak']) && $pdata['streak'] > 1): ?>
                        <div style="color: var(--gold-light); font-size: 0.9rem; margin-top: 0.3rem;">
                            🔥 Streak: x<?php echo $pdata['streak']; ?>
                        </div>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        </div>

        <!-- Game Controls -->
        <div class="option-container" style="display: flex; gap: 1rem; margin-top: 2rem; justify-content: center;">
            <form method="GET" action="index.php">
                <input type="hidden" name="reset" value="1">
                <button type="submit" class="option-button">Exit Game</button>
            </form>
            
            <?php if ($total_answered >= 5 && $_SESSION['round'] == 1): ?>
                <form method="POST" action="gameboard.php">
                    <button type="submit" class="option-button">
                        PROCEED TO ROUND 2
                    </button>
                </form>
            <?php elseif ($total_answered >= 5 && $_SESSION['round'] == 2): ?>
                <form method="GET" action="results.php">
                    <button type="submit" class="option-button">
                        VIEW RESULTS
                    </button>
                </form>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>