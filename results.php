<?php
session_start();

// Redirect if no players
if (!isset($_SESSION['players']) || count($_SESSION['players']) < 2) {
    header('Location: player-setup.php');
    exit();
}

// Find winner and sort players by score
$players = $_SESSION['players'];
usort($players, function($a, $b) {
    return $b['score'] - $a['score'];
});

$winner = $players[0];
$max_score = $winner['score'];

// Handle reset game
if (isset($_GET['reset'])) {
    session_destroy();
    header('Location: index.php');
    exit();
}

// Handle play again
if (isset($_GET['play_again'])) {
    // Keep player names but reset scores
    foreach ($_SESSION['players'] as &$player) {
        $player['score'] = 0;
        $player['streak'] = 0;
    }
    unset($player); // Break reference
    
    // Clear game state
    unset($_SESSION['board']);
    unset($_SESSION['categories']);
    unset($_SESSION['answered_questions']);
    unset($_SESSION['daily_double']);
    unset($_SESSION['current_category']);
    unset($_SESSION['current_qnum']);
    $_SESSION['current_player'] = 0;
    $_SESSION['round'] = 1;
    
    header('Location: gameboard.php');
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Results - Jeopardy Battle Arena</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body class="welcome-screen">
    <div class="welcome-container fade-in">
        
        <!-- Title -->
        <h1 class="title slide-in pulse-animation">GAME OVER</h1>
        
        <!-- Results Container -->
        <div class="rule-container">
            
            <!-- Winner Announcement -->
            <div class="rule-box" style="text-align: center; background: linear-gradient(145deg, var(--deep-blue), var(--medium-blue)); padding: 2rem;">
                <h2 style="font-size: 2.5rem; color: var(--gold); border: none; padding: 0; margin-bottom: 1rem;">
                    🏆 CHAMPION 🏆
                </h2>
                <p style="font-size: 3rem; color: var(--white); font-weight: bold; margin: 1rem 0;">
                    <?php echo htmlspecialchars($winner['name']); ?>
                </p>
                <p style="font-size: 2.5rem; color: var(--gold); font-weight: bold;">
                    $<?php echo number_format($max_score); ?>
                </p>
            </div>
            
            <!-- All Players Scores -->
            <div class="rule-box">
                <h2 style="text-align: center; font-size: 1.8rem;">Final Scoreboard</h2>
                
                <div style="display: flex; flex-direction: column; gap: 1rem; margin-top: 1.5rem;">
                    <?php foreach ($players as $index => $pdata): ?>
                        <div class="player-card <?php echo $index == 0 ? 'active-player' : ''; ?> scale-in" 
                             style="display: flex; align-items: center; gap: 1rem; padding: 1rem; animation-delay: <?php echo $index * 0.2; ?>s;">
                            <div style="font-size: 2rem; min-width: 50px; text-align: center;">
                                <?php 
                                if ($index == 0) echo '🥇';
                                elseif ($index == 1) echo '🥈';
                                elseif ($index == 2) echo '🥉';
                                else echo ($index + 1) . '.';
                                ?>
                            </div>
                            <div style="flex: 1; display: flex; justify-content: space-between; align-items: center;">
                                <div class="player-name" style="font-size: 1.5rem;">
                                    <?php echo htmlspecialchars($pdata['name']); ?>
                                </div>
                                <div class="player-score" style="font-size: 1.5rem; color: var(--gold);">
                                    $<?php echo number_format($pdata['score']); ?>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
            
            <!-- Action Buttons -->
            <div class="option-container" style="display: flex; flex-direction: column; gap: 1rem; margin-top: 2rem;">
                <form method="GET" action="results.php">
                    <button type="submit" name="play_again" value="1" class="option-button" style="width: 100%;">
                        PLAY AGAIN
                    </button>
                </form>
                
                <form method="GET" action="results.php">
                    <button type="submit" name="reset" value="1" class="option-button" style="width: 100%;">
                        EXIT TO MENU
                    </button>
                </form>
            </div>
        </div>
    </div>
</body>
</html>