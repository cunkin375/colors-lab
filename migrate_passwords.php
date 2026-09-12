<?php
/**
 * migrate_passwords.php
 *
 * One-time migration script to normalize all Users.Password values to bcrypt.
 *
 * Three password formats are handled:
 *   - bcrypt  ($2y$...) : already correct, skipped
 *   - MD5     (32 hex)  : unrecoverable, flagged for manual reset
 *   - plaintext         : re-hashed in-place with password_hash()
 *
 * Usage:
 *   php migrate_passwords.php [--dry-run]
 *
 * Always run with --dry-run first to preview changes before committing.
 * Delete this file from the server after the migration is complete.
 */

if (php_sapi_name() !== 'cli') {
    http_response_code(403);
    exit('This script may only be run from the command line.');
}

require_once __DIR__ . '/config.php';

$dryRun = in_array('--dry-run', $argv);

echo "Password Migration Script\n";
echo "=========================\n";
if ($dryRun) {
    echo "[DRY RUN] No changes will be written to the database.\n";
}
echo "\n";

$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error . "\n");
}
$conn->set_charset('utf8mb4');

$result = $conn->query('SELECT ID, Login, Password FROM Users ORDER BY ID');
if (!$result) {
    die("Query failed: " . $conn->error . "\n");
}

$stats = [
    'bcrypt'    => 0,   // already correct, skip it
    'plaintext' => 0,   // re-hashed successfully
    'md5'       => 0,   // unrecoverable, flag it
    'error'     => 0,   // update query failed
];

$updateStmt = $conn->prepare('UPDATE Users SET Password = ? WHERE ID = ?');
if (!$updateStmt) {
    die("Failed to prepare update statement: " . $conn->error . "\n");
}

while ($row = $result->fetch_assoc()) {
    $id       = (int)$row['ID'];
    $login    = $row['Login'];
    $password = $row['Password'];

    // Already bcrypt 
    $info = password_get_info($password);
    if ($info['algo'] !== 0 && $info['algo'] !== null) {
        echo "  [SKIP]  ID=$id  login=$login  — already bcrypt\n";
        $stats['bcrypt']++;
        continue;
    }

    // MD5 hash
    // these cannot be reversed and must be changed manually
    if (preg_match('/^[a-f0-9]{32}$/i', $password)) {
        echo "  [MD5]   ID=$id  login=$login  — unrecoverable, manual reset required\n";
        $stats['md5']++;
        continue;
    }

    // Plaintext
    $newHash = password_hash($password, PASSWORD_DEFAULT);
    echo "  [HASH]  ID=$id  login=$login  — re-hashing plaintext password\n";

    if (!$dryRun) {
        $updateStmt->bind_param('si', $newHash, $id);
        if ($updateStmt->execute()) {
            $stats['plaintext']++;
        } else {
            echo "          ERROR: " . $updateStmt->error . "\n";
            $stats['error']++;
        }
    } else {
        $stats['plaintext']++;
    }
}

// cleanup
$updateStmt->close();
$conn->close();

// summary
$total = array_sum($stats);
echo "\n";
echo "--- Summary " . ($dryRun ? "(dry run)" : "") . " ---\n";
echo "Total rows processed:     $total\n";
echo "Already bcrypt (skipped): {$stats['bcrypt']}\n";
echo "Plaintext re-hashed:      {$stats['plaintext']}\n";
echo "MD5 (needs manual reset): {$stats['md5']}\n";
echo "Errors:                   {$stats['error']}\n";

if ($stats['md5'] > 0) {
    echo "\nWARNING: {$stats['md5']} account(s) have MD5 passwords that could not be\n";
    echo "migrated automatically. Those users will need their passwords reset.\n";
}

if (!$dryRun && $stats['error'] === 0) {
    echo "\nMigration complete. Delete this file from the server:\n";
    echo "  rm /var/www/html/LAMPAPI/migrate_passwords.php\n";
}
