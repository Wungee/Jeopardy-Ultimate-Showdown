<?php
// Helper functions for OpenTDB API integration with error handling

/**
 * Fetch all available categories from OpenTDB
 * @return array List of categories with id and name
 */
function fetch_categories() {
    $url = "https://opentdb.com/api_category.php";
    
    // Use cURL instead of file_get_contents
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // For local dev
    
    $resp = curl_exec($ch);
    $error = curl_error($ch);
    curl_close($ch);
    
    if ($resp === false || !empty($error)) {
        error_log("OpenTDB API: Failed to fetch categories - " . $error);
        return get_fallback_categories();
    }
    
    $data = json_decode($resp, true);
    
    if (!isset($data['trivia_categories']) || empty($data['trivia_categories'])) {
        error_log("OpenTDB API: Invalid category response");
        return get_fallback_categories();
    }
    
    return $data['trivia_categories'];
}

/**
 * Fetch questions for a specific category from OpenTDB
 * @param int $category Category ID
 * @param int $amount Number of questions to fetch
 * @param string $difficulty Difficulty level (easy, medium, hard)
 * @return array List of questions
 */
function fetch_questions($category, $amount = 5, $difficulty = 'medium') {
    $url = "https://opentdb.com/api.php?amount={$amount}&category={$category}&type=multiple&difficulty={$difficulty}";
    
    // Use cURL instead of file_get_contents
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // For local dev
    
    $resp = curl_exec($ch);
    $error = curl_error($ch);
    curl_close($ch);
    
    if ($resp === false || !empty($error)) {
        error_log("OpenTDB API: Failed to fetch questions for category {$category} - " . $error);
        return get_fallback_questions($category, $amount);
    }
    
    $data = json_decode($resp, true);
    
    // Check for valid response
    if (!isset($data['response_code']) || $data['response_code'] != 0) {
        error_log("OpenTDB API: Invalid response code for category {$category}");
        return get_fallback_questions($category, $amount);
    }
    
    if (!isset($data['results']) || empty($data['results'])) {
        error_log("OpenTDB API: No questions returned for category {$category}");
        return get_fallback_questions($category, $amount);
    }
    
    return $data['results'];
}

/**
 * Get fallback categories when API fails
 * @return array Predefined categories
 */
function get_fallback_categories() {
    return [
        ['id' => 9, 'name' => 'General Knowledge'],
        ['id' => 17, 'name' => 'Science & Nature'],
        ['id' => 18, 'name' => 'Science: Computers'],
        ['id' => 21, 'name' => 'Sports'],
        ['id' => 22, 'name' => 'Geography'],
        ['id' => 23, 'name' => 'History'],
        ['id' => 27, 'name' => 'Animals'],
        ['id' => 11, 'name' => 'Entertainment: Film']
    ];
}

/**
 * Get fallback questions when API fails
 * @param int $category Category ID
 * @param int $amount Number of questions needed
 * @return array Predefined questions
 */
function get_fallback_questions($category, $amount) {
    $fallback_pool = [
        [
            'category' => 'General Knowledge',
            'type' => 'multiple',
            'difficulty' => 'medium',
            'question' => 'What is the capital of France?',
            'correct_answer' => 'Paris',
            'incorrect_answers' => ['London', 'Berlin', 'Madrid']
        ],
        [
            'category' => 'Science',
            'type' => 'multiple',
            'difficulty' => 'medium',
            'question' => 'What is the chemical symbol for gold?',
            'correct_answer' => 'Au',
            'incorrect_answers' => ['Go', 'Gd', 'Ag']
        ],
        [
            'category' => 'Geography',
            'type' => 'multiple',
            'difficulty' => 'medium',
            'question' => 'Which ocean is the largest?',
            'correct_answer' => 'Pacific Ocean',
            'incorrect_answers' => ['Atlantic Ocean', 'Indian Ocean', 'Arctic Ocean']
        ],
        [
            'category' => 'History',
            'type' => 'multiple',
            'difficulty' => 'medium',
            'question' => 'In what year did World War II end?',
            'correct_answer' => '1945',
            'incorrect_answers' => ['1943', '1944', '1946']
        ],
        [
            'category' => 'Sports',
            'type' => 'multiple',
            'difficulty' => 'medium',
            'question' => 'How many players are on a basketball team?',
            'correct_answer' => '5',
            'incorrect_answers' => ['6', '7', '4']
        ],
        [
            'category' => 'Entertainment',
            'type' => 'multiple',
            'difficulty' => 'medium',
            'question' => 'Who directed the movie "Titanic"?',
            'correct_answer' => 'James Cameron',
            'incorrect_answers' => ['Steven Spielberg', 'Martin Scorsese', 'Christopher Nolan']
        ],
        [
            'category' => 'Technology',
            'type' => 'multiple',
            'difficulty' => 'medium',
            'question' => 'What does CPU stand for?',
            'correct_answer' => 'Central Processing Unit',
            'incorrect_answers' => ['Computer Personal Unit', 'Central Program Utility', 'Central Processor Unit']
        ],
        [
            'category' => 'Animals',
            'type' => 'multiple',
            'difficulty' => 'medium',
            'question' => 'What is the largest land animal?',
            'correct_answer' => 'African Elephant',
            'incorrect_answers' => ['Giraffe', 'Hippopotamus', 'White Rhinoceros']
        ]
    ];
    
    $questions = [];
    // Use category ID to pick different questions for each category
    $start_index = ($category % count($fallback_pool));
    
    for ($i = 0; $i < $amount; $i++) {
        $index = ($start_index + $i) % count($fallback_pool);
        $questions[] = $fallback_pool[$index];
    }
    
    return $questions;
}

/**
 * Utility for randomizing positions (Daily Doubles, etc)
 * @param int $total Total number of positions
 * @param int $num Number of random indices to generate
 * @return array Random indices
 */
function get_random_indices($total, $num) {
    $indices = range(0, $total - 1);
    shuffle($indices);
    return array_slice($indices, 0, $num);
}

/**
 * Decode HTML entities in question/answer text
 * @param string $text Text to decode
 * @return string Decoded text
 */
function decode_text($text) {
    return html_entity_decode($text, ENT_QUOTES | ENT_HTML5, 'UTF-8');
}
?>