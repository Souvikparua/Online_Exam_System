<?php
/**
 * Utility functions for the Online Exam System
 */

/**
 * Sanitizes user input to prevent XSS and SQL injection
 * 
 * @param string $data The input data to be sanitized
 * @return string The sanitized data
 */
function clean_input($data) {
    // Trim whitespace from the beginning and end
    $data = trim($data);
    
    // Remove backslashes (\)
    $data = stripslashes($data);
    
    // Convert special characters to HTML entities to prevent XSS
    $data = htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
    
    return $data;
}

/**
 * Redirects the user to a specified URL
 * 
 * @param string $url The URL to redirect to
 */
function redirect($url) {
    header("Location: $url");
    exit();
}

/**
 * Checks if a user is logged in
 * 
 * @return bool True if the user is logged in, false otherwise
 */
function is_logged_in() {
    return isset($_SESSION['user_id']);
}

/**
 * Checks if the logged-in user has a specific role
 * 
 * @param string $role The role to check (e.g., 'admin', 'teacher', 'student')
 * @return bool True if the user has the specified role, false otherwise
 */
function has_role($role) {
    return isset($_SESSION['role']) && $_SESSION['role'] === $role;
}

/**
 * Displays a flash message (e.g., success or error messages)
 * 
 * @param string $message The message to display
 * @param string $type The type of message (e.g., 'success', 'error')
 */
function flash_message($message, $type = 'success') {
    $_SESSION['flash_message'] = $message;
    $_SESSION['flash_type'] = $type;
}

/**
 * Renders the flash message (call this in your HTML template)
 */
function render_flash_message() {
    if (isset($_SESSION['flash_message'])) {
        $message = $_SESSION['flash_message'];
        $type = $_SESSION['flash_type'];
        
        echo "<div class='flash-message $type'>$message</div>";
        
        // Clear the message after displaying it
        unset($_SESSION['flash_message']);
        unset($_SESSION['flash_type']);
    }
}

/**
 * Validates an email address
 * 
 * @param string $email The email address to validate
 * @return bool True if the email is valid, false otherwise
 */
function validate_email($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL);
}

/**
 * Generates a random string (useful for generating tokens or passwords)
 * 
 * @param int $length The length of the random string
 * @return string The generated random string
 */
function generate_random_string($length = 10) {
    $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $randomString = '';
    
    for ($i = 0; $i < $length; $i++) {
        $randomString .= $characters[rand(0, strlen($characters) - 1)];
    }
    
    return $randomString;
}

/**
 * Checks if a string is empty or contains only whitespace
 * 
 * @param string $str The string to check
 * @return bool True if the string is empty or contains only whitespace, false otherwise
 */
function is_empty($str) {
    return trim($str) === '';
}

/**
 * Formats a date for display
 * 
 * @param string $date The date string to format
 * @param string $format The format to use (default: 'F j, Y H:i')
 * @return string The formatted date
 */
function format_date($date, $format = 'F j, Y H:i') {
    return date($format, strtotime($date));
}

/**
 * Escapes data for safe output in HTML
 * 
 * @param string $data The data to escape
 * @return string The escaped data
 */
function escape_html($data) {
    return htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
}



function sanitizeInput($input) {
    return htmlspecialchars(trim($input), ENT_QUOTES, 'UTF-8');
}

function isTeacherLoggedIn() {
    return isset($_SESSION['user_id']) && $_SESSION['role'] === 'teacher';
}
?>

