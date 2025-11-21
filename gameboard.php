<?php
session_start();
require_once('opentdb.php');

// Initialize gameboard for each round
if (!isset($_SESSION['board'])) {
    // Retrieve categories
    $categories = fetch_categories();
    shuffle($categories);
    $chosen_categories = array_slice($categories, 0, 6);

    $_SESSION['categories'] = $chosen_categories;

    // Generate board questions: array[category][question]
    $_SESSION['board'] = [];
    foreach ($chosen_categories as $cat) {
        $_SESSION['board'][$cat['id']] = fetch_questions($cat['id']);
    }

    // Generate Daily Double locations
    $_SESSION['daily_double'] = get_random_indices(30, 2); // 2 spots (out of 6x5)
}

// Handle question selection
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['category']) && isset($_POST['qnum'])) {
    $_SESSION['current_category'] = $_POST['category'];
    $_SESSION['current_qnum']     = $_POST['qnum'];
    header('Location: question.php');
    exit();
}

// Board display logic below...

?>
<!DOCTYPE html>
<html>
<head>
    <title>Jeopardy Board</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body class="gameboard-screen">
    <div class="gameboard-container">
        <!-- Show round title etc -->
        <div class="game-header">
            <h2 class="round-title pulse-animation">Round <?php echo $_SESSION['round'] ?? 1; ?></h2>
        </div>

        <!-- Jeopardy board categories -->
        <div class="board-wrapper">
            <form method="POST" action="gameboard.php">
                <div class="game-board">
                <?php
                $cat_idx = 0;
                for ($cat = 0; $cat < 6; $cat++):
                    $cat_data = $_SESSION['categories'][$cat];
                    echo '<div class="category-header">'.$cat_data['name'].'</div>';
                endfor;

                for ($row = 0; $row < 5; $row++):
                    for ($cat = 0; $cat < 6; $cat++):
                        $cat_data  = $_SESSION['categories'][$cat];
                        $q_id      = $cat_data['id'];
                        $key       = $cat * 5 + $row;
                        $answered  = isset($_SESSION['answered_questions'][$key]);
                        $is_dd     = in_array($key, $_SESSION['daily_double']);
                        $value     = ($row+1)*200 * ($_SESSION['round'] ?? 1);

                        echo '<button type="submit" name="category" value="'.$q_id.'" class="question-box '.($answered?'answered':'available').'">';
                        echo '<input type="hidden" name="qnum" value="'.$row.'">';
                        echo $value;
                        if ($is_dd) echo '<span class="dd-indicator shimmer-animation">DD</span>';
                        echo '</button>';
                    endfor;
                endfor;
                ?>
                </div>
            </form>
        </div>
        <!-- Player panels -->
        <div class="player-panels">
        <?php
        foreach ($_SESSION['players'] as $pid=>$pdata) {
            echo '<div class="player-card '.($pid==$_SESSION['current_player']?'active-player':'').'">';
            echo '<div class="player-name">'.$pdata['name'].'</div>';
            echo '<div class="player-score">'.$pdata['score'].'</div>';
            echo '</div>';
        }
        ?>
        </div>
        <!-- Controls (e.g., next round, Final Jeopardy) -->
    </div>
</body>
</html>