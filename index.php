<?php
session_start();
// Clear any existing game session when returning to homepage
if (isset($_GET['reset'])) {
    session_destroy();
    session_start();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Jeopardy Battle Arena</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body class="welcome-screen">
    <div class="welcome-container">
        <h1 class="game-title pulse-animation slide-down">JEOPARDY<br>BATTLE ARENA</h1>
        
        <div class="button-container">
            <form method="POST" action="how-to-play.php">
                <button type="submit" class="game-button glow-hover" name="action" value="play">
                    PLAY GAME
                </button>
            </form>
            
            <form method="POST" action="how-to-play.php">
                <button type="submit" class="game-button glow-hover" name="action" value="rules">
                    RULESET
                </button>
            </form>
        </div>
    </div>
</body>
</html>
