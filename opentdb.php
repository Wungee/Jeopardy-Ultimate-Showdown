<?php
// Helper function: Retrieve OpenTDB trivia questions and categories

function fetch_categories() {
    $url = "https://opentdb.com/api_category.php";
    $resp = file_get_contents($url);
    $data = json_decode($resp, true);
    return $data['trivia_categories'] ?? [];
}

function fetch_questions($category, $amount=5, $difficulty='medium') {
    $url = "https://opentdb.com/api.php?amount=$amount&category=$category&type=multiple&difficulty=$difficulty";
    $resp = file_get_contents($url);
    $data = json_decode($resp, true);
    return $data['results'] ?? [];
}

// Utility for randomizing positions (Daily Doubles, etc)
function get_random_indices($total, $num) {
    $indices = range(0, $total-1);
    shuffle($indices);
    return array_slice($indices, 0, $num);
}
?>