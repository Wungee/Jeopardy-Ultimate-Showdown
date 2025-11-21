<?php
session_start();
$winner = null;
$max_score = null;
foreach ($_SESSION['players'] as $pdata) {
    if ($winner === null || $pdata['score'] > $max_score) {
        $winner = $pdata['name'];
        $max_score = $pdata['score'];
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Results</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body class="welcome-screen">
    <div class="welcome-container fade-in">
        <h2 class="game-title pulse-animation">Results</h2>
        <div class="rule-container">
            <div class="rule-box">
                <h2>Congratulations <?php echo $winner; ?>!</h2>
                <p>You are the Jeopardy Battle Arena champion!</p>
                <ul>
                    <?php foreach ($_SESSION['players'] as $pdata): ?>
                    <li><strong><?php echo $pdata['name']; ?>:</strong> <?php echo $pdata['score']; ?> points</li>
                    <?php endforeach; ?>
                </ul>
            </div>
            <form method="get" action="index.php">
                <button type="submit" class="game-button glow-hover">Play Again</button>
            </form>
            <form method="get" action="index.php">
                <button type="submit" class="game-button glow-hover">Exit</button>
            </form>
        </div>
    </div>
</body>
</html>