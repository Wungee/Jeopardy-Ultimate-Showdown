<?php
session_start();
// Clear game session if resetting via homepage link
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
            <a href="player-setup.php" class="option-button glow-hover">Play Game</a>
            <a href="how-to-play.php" class="option-button glow-hover">Ruleset</a>
        </div>
        <div class="rule-container fade-in shimmer-animation">
            <div class="rule-box">
                <h2>Rules: Jeopardy Battle Arena</h2>
                <p>
                  The game consists of two main rounds and a Final Jeopardy! Players select a category and dollar value, then answer in the form of a question. Both correct and incorrect answers impact your score. 
                </p>
            </div>
            <div class="rule-box">
                <h2>Point System & Multipliers</h2>
                <p>
                  Round 1 values: $200–$1000. Round 2: All values are doubled! <br>
                  Correct answers score points, wrong answers lose them. <br>
                  Consecutive correct answers activate a multiplier (streak bonus)!
                </p>
            </div>
            <div class="rule-box">
                <h2>Daily Double</h2>
                <p>
                  Daily Doubles are hidden in two random board spots. When found, only the selecting player can wager and respond. Wager up to your score or the max board value—high risk, high reward!
                </p>
            </div>
        </div>
    </div>
</body>
</html>