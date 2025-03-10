<?php
session_start();

/**
 * Establishes a PDO connection to the MySQL database.
 * @return PDO Database connection object
 */
function pdo_connect_mysql() {
    $host = 'localhost';
    $user = 'root';
    $pass = '';
    $name = 'printcost';
    try {
        return new PDO("mysql:host=$host;dbname=$name;charset=utf8", $user, $pass, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
        ]);
    } catch (PDOException $e) {
        exit('Database connection failed: ' . $e->getMessage());
    }
}

/**
 * Generates the HTML header with title and CSRF token.
 * @param string $title Page title
 */
function template_header($title) {
    $csrf_token = bin2hex(random_bytes(32));
    $_SESSION['csrf_token'] = $csrf_token;

    echo <<<HTML
<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>$title</title>
    <link href="css/style.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/boxicons@2.1.4/css/boxicons.min.css">
</head>
<body>
    <input type="hidden" id="csrf_token" value="$csrf_token">
HTML;
}

/**
 * Generates the HTML footer with script includes.
 */
function template_footer() {
    echo <<<HTML
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.3/jquery.min.js"></script>
    <script src="js/main.js"></script>
</body>
</html>
HTML;
}

/**
 * Formats minutes into hours and minutes (e.g., "2h 30m").
 * @param int $minutes Total minutes
 * @return string Formatted duration
 */
function format_duration($minutes) {
    $hours = floor($minutes / 60);
    $remaining_minutes = $minutes % 60;
    return sprintf('%dh %dm', $hours, $remaining_minutes);
}

/**
 * Redirects with a flash message.
 * @param string $url Redirect URL
 * @param string $message Message text
 * @param string $type Message type (success/error)
 */
function redirect_with_message($url, $message, $type = 'success') {
    $_SESSION['flash'] = ['message' => $message, 'type' => $type];
    header("Location: $url");
    exit;
}

/**
 * Retrieves and clears the flash message.
 * @return string HTML-formatted message or empty string
 */
function get_flash_message() {
    if (!isset($_SESSION['flash'])) return '';
    $flash = $_SESSION['flash'];
    unset($_SESSION['flash']);
    return "<p class='flash-{$flash['type']}'>" . htmlspecialchars($flash['message']) . "</p>";
}

/**
 * Validates required fields in an array.
 * @param array $data Input data
 * @param array $fields Required field names
 * @return bool True if all fields are non-empty
 */
function validate_required($data, $fields) {
    return !in_array(true, array_map(fn($field) => empty($data[$field]), $fields));
}

/**
 * Sanitizes input data by trimming strings.
 * @param array $data Input data
 * @return array Sanitized data
 */
function sanitize_input($data) {
    return array_map(fn($value) => is_string($value) ? trim($value) : $value, $data);
}

/**
 * Verifies CSRF token.
 * @param string $token Submitted token
 * @return bool True if valid
 */
function verify_csrf_token($token) {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

/**
 * Generates pagination links.
 * @param int $page Current page
 * @param int $total_records Total records
 * @param int $records_per_page Records per page
 * @param string $base_url Base URL for links
 * @return string HTML pagination links
 */
function generate_pagination($page, $total_records, $records_per_page, $base_url) {
    $total_pages = ceil($total_records / $records_per_page);
    if ($total_pages <= 1) return '';

    $html = '<div class="pagination">';
    if ($page > 1) {
        $html .= "<a href='$base_url?page=" . ($page - 1) . "' class='prev'>Vorige</a>";
    }

    $start = max(1, $page - 2);
    $end = min($total_pages, $page + 2);
    for ($i = $start; $i <= $end; $i++) {
        $class = $i == $page ? 'active' : '';
        $html .= "<a href='$base_url?page=$i' class='$class'>$i</a>";
    }

    if ($page < $total_pages) {
        $html .= "<a href='$base_url?page=" . ($page + 1) . "' class='next'>Volgende</a>";
    }
    $html .= '</div>';
    return $html;
}