<?php
session_start();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>How to Play - Jeopardy</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body class="setup-screen">
    <div class="setup-container fade-in">
        <h2 class="section-title shimmer-animation">How to Play</h2>
        
        <div class="setup-box">
            <div class="rules-section">
                <h3 class="rules-heading">Game Flow</h3>
                <p class="rules-text">
                    Jeopardy is played in rounds with multiple players competing to answer trivia questions. 
                    Players take turns selecting questions from the game board. Each correct answer adds points to your score, while incorrect answers deduct points. The highest scorer wins!
                </p>
            </div>
            <div class="rules-section">
                <h3 class="rules-heading">Point System</h3>
                <p class="rules-text">
                    Questions are valued $200 to $1000 depending on difficulty. Each correct answer earns you points, each wrong answer subtracts. Build a streak for a point multiplier!
                </p>
            </div>
            <div class="rules-section">
                <h3 class="rules-heading">Daily Doubles</h3>
                <p class="rules-text">
                    Daily Doubles let you wager up to your current score (or $1000 if lower). Success multiplies your score—failure costs your wager.
                </p>
            </div>
            <form method="POST" action="player-setup.php">
                <button type="submit" class="continue-button glow-hover">READY TO PLAY!</button>
            </form>
            <form method="GET" action="index.php" class="back-form">
                <button type="submit" class="back-button">← Back to Home</button>
            </form>
        </div>
    </div>
</body>
</html>