<?php
session_start();
require_once('opentdb.php');

// Redirect if no active question
if (!isset($_SESSION['current_category']) || !isset($_SESSION['current_qnum'])) {
    header('Location: gameboard.php');
    exit();
}

// Retrieve question information
$cat = $_SESSION['current_category'];
$qnum = $_SESSION['current_qnum'];
$question = $_SESSION['board'][$cat][$qnum];

// Get current player info
$cur_player = $_SESSION['current_player'];
$current_player_data = $_SESSION['players'][$cur_player];

// Calculate unique question key
$cat_index = array_search($cat, array_column($_SESSION['categories'], 'id'));
$question_key = $cat_index * 1 + $qnum;

// Check if this is a Daily Double
$is_daily_double = in_array($question_key, $_SESSION['daily_double']);

// Calculate question value
$value = 500 * $_SESSION['round'];

// Shuffle answers for display
$answers = $question['incorrect_answers'];
$answers[] = $question['correct_answer'];
shuffle($answers);

// Handle answer submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['answer'])) {
    
    $selected_answer = $_POST['answer'];
    $correct_answer = $question['correct_answer'];
    
    // Check if answer is correct
    $is_correct = ($selected_answer === $correct_answer);
    
    // Initialize streak if not set
    if (!isset($_SESSION['players'][$cur_player]['streak'])) {
        $_SESSION['players'][$cur_player]['streak'] = 0;
    }
    
    // Calculate multiplier based on streak
    $streak = $_SESSION['players'][$cur_player]['streak'];
    $multiplier = ($streak >= 2) ? 2 : 1;
    
    // Handle Daily Double wager
    if ($is_daily_double && isset($_POST['wager'])) {
        $wager = intval($_POST['wager']);
        
        // Validate wager amount
        $max_wager = max(1000, $_SESSION['players'][$cur_player]['score']);
        $wager = min($wager, $max_wager);
        $wager = max(5, $wager); // Minimum wager of $5
        
        if ($is_correct) {
            $_SESSION['players'][$cur_player]['score'] += $wager * $multiplier;
            $_SESSION['players'][$cur_player]['streak'] += 1;
        } else {
            $_SESSION['players'][$cur_player]['score'] -= $wager;
            $_SESSION['players'][$cur_player]['streak'] = 0;
        }
    } else {
        // Regular question
        if ($is_correct) {
            $_SESSION['players'][$cur_player]['score'] += $value * $multiplier;
            $_SESSION['players'][$cur_player]['streak'] += 1;
        } else {
            $_SESSION['players'][$cur_player]['score'] -= $value;
            $_SESSION['players'][$cur_player]['streak'] = 0;
        }
    }
    
    // Mark question as answered
    $_SESSION['answered_questions'][$question_key] = true;
    
    // Move to next player
    $_SESSION['current_player'] = ($_SESSION['current_player'] + 1) % count($_SESSION['players']);
    
    // Clear current question data
    unset($_SESSION['current_category']);
    unset($_SESSION['current_qnum']);
    
    // Redirect back to gameboard
    header('Location: gameboard.php');
    exit();
}

// Handle Daily Double wager screen
$show_wager_screen = ($is_daily_double && !isset($_POST['wager_confirmed']));

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Question - Jeopardy</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body class="setup-screen">
    
    <?php if ($show_wager_screen): ?>
        <!-- Daily Double Wager Screen -->
        <div class="welcome-container fade-in">
            <h1 class="title slide-in pulse-animation">⭐ DAILY DOUBLE! ⭐</h1>
            
            <div class="rule-container">
                <div class="rule-box">
                    <h2><?php echo htmlspecialchars($current_player_data['name']); ?>, you've landed on a Daily Double!</h2>
                    
                    <p style="font-size: 1.3rem; margin: 1.5rem 0; text-align: center;">
                        Your current score: <strong style="color: var(--gold);">$<?php echo number_format($current_player_data['score']); ?></strong>
                    </p>
                    
                    <?php if (isset($current_player_data['streak']) && $current_player_data['streak'] >= 2): ?>
                        <p style="font-size: 1.2rem; margin: 1rem 0; text-align: center; color: var(--gold-light);">
                            🔥 Active Streak Bonus: 2x multiplier!
                        </p>
                    <?php endif; ?>
                    
                    <p style="font-size: 1.2rem; margin: 1.5rem 0; text-align: center;">
                        How much would you like to wager?
                    </p>
                    
                    <form method="POST" action="question.php">
                        <input type="hidden" name="wager_confirmed" value="1">
                        
                        <div style="text-align: center; margin: 2rem 0;">
                            <label for="wager" style="display: block; color: var(--gold); font-size: 1.3rem; margin-bottom: 1rem; font-weight: bold;">
                                Wager Amount ($):
                            </label>
                            <input 
                                type="number" 
                                id="wager" 
                                name="wager" 
                                min="5" 
                                max="<?php echo max(1000, $current_player_data['score']); ?>" 
                                value="<?php echo min(1000, max(5, $current_player_data['score'])); ?>"
                                class="player-input"
                                style="max-width: 300px; margin: 0 auto; text-align: center; font-size: 1.5rem;"
                                required
                            >
                            <small style="color: var(--gray); display: block; margin-top: 0.5rem;">
                                Min: $5 | Max: $<?php echo number_format(max(1000, $current_player_data['score'])); ?>
                            </small>
                        </div>
                        
                        <button type="submit" class="option-button" style="width: 100%; margin-top: 1rem;">
                            LOCK IN WAGER
                        </button>
                    </form>
                </div>
            </div>
        </div>
        
    <?php else: ?>
        <!-- Question Display Screen -->
        <div class="gameboard-container scale-in">
            
            <!-- Question Header -->
            <div class="game-header">
                <div style="display: flex; justify-content: space-between; align-items: center; padding-bottom: 1rem; border-bottom: 2px solid var(--gold-dark);">
                    <div class="category-header" style="flex: 1; margin-right: 1rem; grid-row: auto;">
                        <?php echo htmlspecialchars(decode_text($question['category'])); ?>
                    </div>
                    <div class="round-title" style="flex: 0 0 auto;">
                        <?php if ($is_daily_double && isset($_POST['wager'])): ?>
                            DD: $<?php echo number_format(intval($_POST['wager'])); ?>
                        <?php else: ?>
                            $<?php echo number_format($value); ?>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            
            <!-- Question Text Box -->
            <div class="rule-box" style="margin: 2rem 0; min-height: 150px; display: flex; align-items: center; justify-content: center;">
                <p style="color: var(--white); font-size: 1.8rem; text-align: center; line-height: 1.6;">
                    <?php echo decode_text($question['question']); ?>
                </p>
            </div>
            
            <!-- Current Player Indicator -->
            <div style="text-align: center; margin-bottom: 2rem; color: var(--white); font-size: 1.2rem;">
                Current Player: <span style="color: var(--gold); font-weight: bold;"><?php echo htmlspecialchars($current_player_data['name']); ?></span>
                <?php if (isset($current_player_data['streak']) && $current_player_data['streak'] >= 2): ?>
                    <br>
                    <span style="color: var(--gold-light); font-size: 1.1rem;">🔥 Streak Active: 2x Points!</span>
                <?php endif; ?>
            </div>
            
            <!-- Answer Options -->
            <form method="POST" action="question.php">
                <?php if ($is_daily_double && isset($_POST['wager'])): ?>
                    <input type="hidden" name="wager" value="<?php echo intval($_POST['wager']); ?>">
                <?php endif; ?>
                
                <div style="display: grid; grid-template-columns: 1fr; gap: 1rem;">
                    <?php foreach ($answers as $index => $ans): ?>
                        <button 
                            type="submit" 
                            name="answer" 
                            value="<?php echo htmlspecialchars($ans); ?>"
                            class="option-button scale-in"
                            style="animation-delay: <?php echo $index * 0.15; ?>s; text-align: left; width: 100%;"
                        >
                            <?php echo decode_text($ans); ?>
                        </button>
                    <?php endforeach; ?>
                </div>
            </form>
        </div>
    <?php endif; ?>
    
</body>
</html>