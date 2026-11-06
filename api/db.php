<?php
function getConnection(): mysqli
{
    mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

    try {
        $conn = new mysqli(getenv('DB_HOST') ?: 'localhost', getenv('DB_USER') ?: 'TheBeast', getenv('DB_PASSWORD') ?: '', getenv('DB_NAME') ?: 'COP4331');
        $conn->set_charset('utf8mb4');
        return $conn;
    } catch (mysqli_sql_exception $e) {
        http_response_code(500);
        $message = 'Database is unavailable';
        echo json_encode(['error' => $message]);
        exit();
    }
}
