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
    <div class="option-container">
        <a href="player_setup.php" class="option-button glow-on-hover">Play!</a>
        
        <a href="how_to_play.php" class="option-button glow-on-hover">Rules</a>
    </div>
    <div class="rule-container">
    
        <div class="rule-box flow-explanation">
            <h2>Rules: Jeopardy Battle Arena</h2>
            <p>A game consists of two rounds, Jeopardy, Double Jeopardy, and a Final Jeopardy! round. Players select a category and dollar value, and must answer in the form of a question. The board has 6 categories with 5 questions each.</p>
            <p>Correct answers add the dollar value to score, while incorrect answers deduct it.</p>
        </div>
    
        <div class="rule-box point-system-explanation">
            <h2>Point System & Multipliers</h2>
            <p>Round 1 values: $200, $400, $600, $800, $1000. Round 2 then Doubles the value.</p>
            <p>Watch for the **point multiplier** which activates for players on a streak!</p>
        </div>
    
        <div class="rule-box daily-double-explanation">
            <h2>Daily Double</h2>
            <p>Hidden in two random spots on the board. When found, the player who selected it can answer.</p>
            <p>The player must make a wager first. They can wager up to their current score or the maximum value of the roundbased off whichever is greater.</p>
        </div>
        
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
