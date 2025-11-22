<?php
session_start();
require_once('opentdb.php');

// Redirect if no players
if (!isset($_SESSION['players']) || count($_SESSION['players']) < 2) {
    header('Location: player-setup.php');
    exit();
}

// Initialize Final Jeopardy if not set
if (!isset($_SESSION['final_stage'])) {
    $_SESSION['final_stage'] = 'category'; // Stages: category, wager, question, results
    
    // Fetch category and question
    $categories = fetch_categories();
    $cat = $categories[array_rand($categories)];
    $_SESSION['final_category'] = $cat;
    
    $questions = fetch_questions($cat['id'], 1, 'hard');
    if (empty($questions)) {
        $questions = get_fallback_questions($cat['id'], 1);
    }
    $_SESSION['final_question'] = $questions[0];
    $_SESSION['final_wagers'] = [];
    $_SESSION['final_answers'] = [];
}

// Handle category reveal and move to wager stage
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['view_category'])) {
    $_SESSION['final_stage'] = 'wager';
    header('Location: final-jeopardy.php');
    exit();
}

// Handle wager submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_wagers'])) {
    $all_wagered = true;
    foreach ($_SESSION['players'] as $pid => $pdata) {
        if (isset($_POST['wager_' . $pid])) {
            $wager = intval($_POST['wager_' . $pid]);
            $max_wager = max(0, $pdata['score']);
            // Wager must be at least $0 and at most current score
            $wager = max(0, min($wager, $max_wager));
            $_SESSION['final_wagers'][$pid] = $wager;
        } else {
            $all_wagered = false;
        }
    }
    
    if ($all_wagered) {
        $_SESSION['final_stage'] = 'question';
        header('Location: final-jeopardy.php');
        exit();
    }
}

// Handle answer submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_answers'])) {
    $all_answered = true;
    foreach ($_SESSION['players'] as $pid => $pdata) {
        if (isset($_POST['answer_' . $pid])) {
            $_SESSION['final_answers'][$pid] = trim($_POST['answer_' . $pid]);
        } else {
            $all_answered = false;
        }
    }
    
    if ($all_answered) {
        // Process all answers
        foreach ($_SESSION['players'] as $pid => &$pdata) {
            $wager = $_SESSION['final_wagers'][$pid];
            $player_answer = $_SESSION['final_answers'][$pid];
            $correct_answer = $_SESSION['final_question']['correct_answer'];
            
            // Check if answer is correct (case-insensitive comparison)
            $is_correct = (strcasecmp(trim($player_answer), trim($correct_answer)) === 0);
            
            if ($is_correct) {
                $pdata['score'] += $wager;
            } else {
                $pdata['score'] -= $wager;
            }
        }
        unset($pdata); // Break reference
        
        // Clear Final Jeopardy session data
        unset($_SESSION['final_stage']);
        unset($_SESSION['final_category']);
        unset($_SESSION['final_question']);
        unset($_SESSION['final_wagers']);
        unset($_SESSION['final_answers']);
        
        header('Location: results.php');
        exit();
    }
}

$stage = $_SESSION['final_stage'];
$category = $_SESSION['final_category'];
$question = $_SESSION['final_question'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Final Jeopardy - Jeopardy Battle Arena</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body class="setup-screen">
    <div class="welcome-container fade-in">
        
        <?php if ($stage === 'category'): ?>
            <!-- Stage 1: Show Category -->
            <h1 class="title slide-in pulse-animation">🏆 FINAL JEOPARDY! 🏆</h1>
            
            <div class="rule-container">
                <div class="rule-box" style="text-align: center;">
                    <h2 style="font-size: 2rem; color: var(--gold);">The Category Is:</h2>
                    <p style="font-size: 2.5rem; color: var(--white); font-weight: bold; margin: 2rem 0;">
                        <?php echo htmlspecialchars($category['name']); ?>
                    </p>
                    
                    <form method="POST" action="final-jeopardy.php">
                        <button type="submit" name="view_category" class="option-button" style="width: 100%;">
                            PROCEED TO WAGERING
                        </button>
                    </form>
                </div>
            </div>
            
        <?php elseif ($stage === 'wager'): ?>
            <!-- Stage 2: Wager Entry -->
            <h1 class="title slide-in pulse-animation">💰 PLACE YOUR WAGERS 💰</h1>
            
            <div class="rule-container">
                <div class="rule-box">
                    <h2 style="text-align: center; margin-bottom: 2rem;">Category: <?php echo htmlspecialchars($category['name']); ?></h2>
                    
                    <form method="POST" action="final-jeopardy.php">
                        <?php foreach ($_SESSION['players'] as $pid => $pdata): ?>
                            <div class="input-group scale-in" style="animation-delay: <?php echo $pid * 0.1; ?>s; margin-bottom: 1.5rem;">
                                <label for="wager_<?php echo $pid; ?>" style="font-size: 1.3rem;">
                                    <?php echo htmlspecialchars($pdata['name']); ?> 
                                    (Current Score: $<?php echo number_format($pdata['score']); ?>)
                                </label>
                                <input 
                                    type="number" 
                                    id="wager_<?php echo $pid; ?>" 
                                    name="wager_<?php echo $pid; ?>"
                                    class="player-input"
                                    min="0"
                                    max="<?php echo max(0, $pdata['score']); ?>"
                                    value="<?php echo max(0, $pdata['score']); ?>"
                                    style="font-size: 1.3rem; text-align: center;"
                                    required
                                >
                                <small style="color: var(--gray); display: block; margin-top: 0.3rem;">
                                    Min: $0 | Max: $<?php echo number_format(max(0, $pdata['score'])); ?>
                                </small>
                            </div>
                        <?php endforeach; ?>
                        
                        <button type="submit" name="submit_wagers" class="option-button" style="width: 100%; margin-top: 2rem;">
                            LOCK IN WAGERS
                        </button>
                    </form>
                </div>
            </div>
            
        <?php elseif ($stage === 'question'): ?>
            <!-- Stage 3: Show Question and Collect Answers -->
            <h1 class="title slide-in pulse-animation">❓ FINAL JEOPARDY QUESTION ❓</h1>
            
            <div class="rule-container">
                <!-- Category Display -->
                <div class="rule-box" style="text-align: center; margin-bottom: 1rem;">
                    <h2 style="font-size: 1.5rem; color: var(--gold); margin: 0;">
                        <?php echo htmlspecialchars($category['name']); ?>
                    </h2>
                </div>
                
                <!-- Question Display -->
                <div class="rule-box" style="min-height: 150px; display: flex; align-items: center; justify-content: center;">
                    <p style="font-size: 1.8rem; text-align: center; line-height: 1.6; color: var(--white);">
                        <?php echo decode_text($question['question']); ?>
                    </p>
                </div>
                
                <!-- Answer Inputs -->
                <div class="rule-box">
                    <h2 style="text-align: center; margin-bottom: 2rem;">Submit Your Answers</h2>
                    
                    <form method="POST" action="final-jeopardy.php">
                        <?php foreach ($_SESSION['players'] as $pid => $pdata): ?>
                            <div class="input-group scale-in" style="animation-delay: <?php echo $pid * 0.1; ?>s; margin-bottom: 1.5rem;">
                                <label for="answer_<?php echo $pid; ?>" style="font-size: 1.3rem;">
                                    <?php echo htmlspecialchars($pdata['name']); ?> 
                                    (Wager: $<?php echo number_format($_SESSION['final_wagers'][$pid]); ?>)
                                </label>
                                <input 
                                    type="text" 
                                    id="answer_<?php echo $pid; ?>" 
                                    name="answer_<?php echo $pid; ?>"
                                    class="player-input"
                                    placeholder="What is...?"
                                    style="font-size: 1.2rem;"
                                    required
                                >
                            </div>
                        <?php endforeach; ?>
                        
                        <button type="submit" name="submit_answers" class="option-button" style="width: 100%; margin-top: 2rem;">
                            SUBMIT ANSWERS
                        </button>
                    </form>
                </div>
                
                <!-- Answer Choices for Reference -->
                <div class="rule-box">
                    <h3 style="text-align: center; color: var(--gold); margin-bottom: 1rem;">Answer Choices:</h3>
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                        <?php 
                        $all_answers = $question['incorrect_answers'];
                        $all_answers[] = $question['correct_answer'];
                        shuffle($all_answers);
                        foreach ($all_answers as $ans): 
                        ?>
                            <div style="background: var(--deep-blue); padding: 1rem; border-radius: 8px; border: 2px solid var(--gold-dark); text-align: center;">
                                <?php echo decode_text($ans); ?>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
            
        <?php endif; ?>
        
    </div>
</body>
</html>
