<?php
session_start();

// Connect to MySQL database
function pdo_connect_mysql() {
    $DATABASE_HOST = 'localhost';
    $DATABASE_USER = 'root';
    $DATABASE_PASS = 'root';
    $DATABASE_NAME = 'printcost';
    try {
        return new PDO('mysql:host=' . $DATABASE_HOST . ';dbname=' . $DATABASE_NAME . ';charset=utf8', $DATABASE_USER, $DATABASE_PASS);
    } catch (PDOException $exception) {
        exit('Failed to connect to database!');
    }
}

// Template header, outputs HTML header content
function template_header($title) {
    $title = htmlspecialchars($title);
    $toast_message = isset($_SESSION['toast_message']) ? json_encode($_SESSION['toast_message']) : 'null';
    $toast_type = isset($_SESSION['toast_type']) ? json_encode($_SESSION['toast_type']) : 'null';

    echo <<<EOT
<!DOCTYPE html>
<html lang="nl">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>$title - Printkosten</title>
        <link rel="preload" href="fonts/poppins-regular.woff2" as="font" type="font/woff2" crossorigin>
        <link rel="preload" href="fonts/poppins-medium.woff2" as="font" type="font/woff2" crossorigin>
        <link rel="preload" href="fonts/poppins-semibold.woff2" as="font" type="font/woff2" crossorigin>
        <link rel="preload" href="fonts/boxicons.woff2" as="font" type="font/woff2" crossorigin>
        <link rel="stylesheet" href="css/style.css">
        <link rel="stylesheet" href="css/boxicons.min.css">
        <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
        <script src="js/main.js"></script>
    </head>
    <body>
    <div class="toast-container"></div>
    <script>
        window.addEventListener('load', function() {
            const toastMessage = $toast_message;
            const toastType = $toast_type;
            if (toastMessage !== null) {
                showToast(toastMessage, toastType || 'success');
                fetch('clear_toast.php', { method: 'POST' });
            }
        });
    </script>
EOT;
}

// Template footer, outputs HTML footer content
function template_footer() {
    echo <<<EOT
    </body>
</html>
EOT;
}

// Sanitize input data to prevent XSS and trim whitespace
function sanitize_input($data) {
    return array_map(function($value) {
        return is_array($value) ? sanitize_input($value) : htmlspecialchars(strip_tags(trim($value)));
    }, $data);
}

// Validate required fields are not empty
function validate_required($data, $required) {
    foreach ($required as $field) {
        if (empty($data[$field])) return false;
    }
    return true;
}

// Redirect with a toast message
function redirect_with_message($url, $message, $type = 'success') {
    $_SESSION['toast_message'] = $message;
    $_SESSION['toast_type'] = $type;
    header("Location: $url");
    exit;
}

// Legacy flash message function, now unused but kept for compatibility
function get_flash_message() {
    return '';
}

// Generate pagination links with chevron icons
function generate_pagination($current_page, $total_records, $records_per_page, $base_url) {
    $total_pages = ceil($total_records / $records_per_page);
    if ($total_pages <= 1) return '';
    $html = '<div class="pagination">';
    if ($current_page > 1) {
        $html .= '<a href="' . $base_url . '?page=' . ($current_page - 1) . '" class="prev"><i class="bx bx-chevron-left"></i></a>';
    }
    for ($i = 1; $i <= $total_pages; $i++) {
        $html .= '<a href="' . $base_url . '?page=' . $i . '"' . ($i == $current_page ? ' class="active"' : '') . '>' . $i . '</a>';
    }
    if ($current_page < $total_pages) {
        $html .= '<a href="' . $base_url . '?page=' . ($current_page + 1) . '" class="next"><i class="bx bx-chevron-right"></i></a>';
    }
    $html .= '</div>';
    return $html;
}

// Format duration from minutes to hours and minutes
function format_duration($minutes) {
    $hours = floor($minutes / 60);
    $remaining_minutes = $minutes % 60;
    return $hours . ' uur ' . $remaining_minutes . ' min';
}

// Generate CSRF token only if not already set
if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Verify CSRF token
function verify_csrf_token($token) {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}
?>