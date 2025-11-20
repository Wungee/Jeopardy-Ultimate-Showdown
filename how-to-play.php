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
            <!-- Game Flow Explanation -->
            <div class="rules-section">
                <h3 class="rules-heading">Game Flow</h3>
                <p class="rules-text">
                    Jeopardy is played in rounds with multiple players competing to answer trivia questions. 
                    Players take turns selecting questions from the game board. Each correct answer adds points 
                    to your score, while incorrect answers deduct points. The player with the highest score 
                    at the end wins!
                </p>
            </div>
            
            <!-- Point System -->
            <div class="rules-section">
                <h3 class="rules-heading">Point System</h3>
                <p class="rules-text">
                    Questions are valued from $200 to $1000 based on difficulty. Answer correctly to earn 
                    points, but beware - wrong answers will cost you! Build a streak by answering multiple 
                    questions correctly in a row to activate a point multiplier.
                </p>
            </div>
            
            <!-- Daily Doubles -->
            <div class="rules-section">
                <h3 class="rules-heading">Daily Doubles</h3>
                <p class="rules-text">
                    Hidden throughout the board are Daily Doubles! When you land on one, you can wager 
                    any amount up to your current score (or $1000 if you have less). Answer correctly 
                    to multiply your score - but get it wrong and lose your wager!
                </p>
            </div>
            
            <!-- Ready to Play Button -->
            <form method="POST" action="player-setup.php">
                <button type="submit" class="continue-button glow-hover">
                    READY TO PLAY!
                </button>
            </form>
            
            <form method="GET" action="index.php" class="back-form">
                <button type="submit" class="back-button">← Back to Home</button>
            </form>
        </div>
    </div>
    
    <style>
        .rules-section {
            margin-bottom: 2rem;
            padding: 1.5rem;
            background: rgba(10, 14, 39, 0.5);
            border-radius: 8px;
            border-left: 4px solid var(--gold);
        }
        
        .rules-heading {
            color: var(--gold);
            font-size: 1.5rem;
            margin-bottom: 1rem;
            text-transform: uppercase;
        }
        
        .rules-text {
            color: var(--white);
            font-size: 1.1rem;
            line-height: 1.6;
        }
    </style>
</body>
</html>
