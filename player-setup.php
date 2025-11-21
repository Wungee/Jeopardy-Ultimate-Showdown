<?php
session_start();

$player_count = isset($_POST['player_count']) ? intval($_POST['player_count']) : 0;
$show_name_inputs = false;

if ($player_count >= 2 && $player_count <= 4) {
    $show_name_inputs = true;
}

// Handle player names submission and redirect to gameboard
if (isset($_POST['player_names']) && isset($_POST['players'])) {
    $_SESSION['players'] = [];
    $players = $_POST['players'];
    
    foreach ($players as $index => $name) {
        if (!empty(trim($name))) {
            $_SESSION['players'][] = [
                'id' => $index + 1,
                'name' => htmlspecialchars(trim($name)),
                'score' => 0,
                'streak' => 0
            ];
        }
    }
    
    if (count($_SESSION['players']) >= 2) {
        $_SESSION['current_player'] = 0;
        $_SESSION['round'] = 1;
        $_SESSION['answered_questions'] = [];
        header('Location: gameboard.php');
        exit();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Player Setup - Jeopardy</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body class="setup-screen">
    <div class="setup-container fade-in">
        <h2 class="section-title shimmer-animation">Player Setup</h2>
        <?php if (!$show_name_inputs): ?>
            <div class="setup-box">
                <p class="setup-instruction">How many players will be competing?</p>
                <form method="POST" action="player-setup.php">
                    <div class="player-count-options">
                        <button type="submit" name="player_count" value="2" class="count-button glow-hover">2 PLAYERS</button>
                        <button type="submit" name="player_count" value="3" class="count-button glow-hover">3 PLAYERS</button>
                        <button type="submit" name="player_count" value="4" class="count-button glow-hover">4 PLAYERS</button>
                    </div>
                </form>
                <form method="GET" action="index.php" class="back-form">
                    <button type="submit" class="back-button">← Back to Home</button>
                </form>
            </div>
        <?php else: ?>
            <div class="setup-box">
                <p class="setup-instruction">Enter player names:</p>
                <form method="POST" action="player-setup.php">
                    <div class="name-inputs">
                        <?php for ($i = 1; $i <= $player_count; $i++): ?>
                            <div class="input-group scale-in" style="animation-delay: <?php echo $i * 0.1; ?>s;">
                                <label for="player<?php echo $i; ?>">Player <?php echo $i; ?>:</label>
                                <input 
                                    type="text" 
                                    id="player<?php echo $i; ?>" 
                                    name="players[]" 
                                    class="player-input"
                                    placeholder="Enter name"
                                    required
                                    maxlength="20"
                                >
                            </div>
                        <?php endfor; ?>
                    </div>
                    <input type="hidden" name="player_names" value="1">
                    <button type="submit" class="continue-button glow-hover">CONTINUE TO GAME</button>
                </form>
                <form method="POST" action="player-setup.php" class="back-form">
                    <button type="submit" class="back-button">← Change Player Count</button>
                </form>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>