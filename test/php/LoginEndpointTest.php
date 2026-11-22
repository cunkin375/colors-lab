<?php

declare(strict_types=1);

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class LoginEndpointTest extends TestCase
{
    #[DataProvider('requestsRejectedBeforeDatabaseAccess')]
    public function testLoginResponse(string $method, int $status, string $body): void
    {
        // Run the endpoint separately because it exits after sending the response.
        $script = <<<'PHP'
            $_SERVER['REQUEST_METHOD'] = $argv[1];
            register_shutdown_function(static function (): void {
                echo "\n", http_response_code();
            });
            require $argv[2];
            PHP;
        $endpoint = __DIR__ . '/../../src/api/login.php';
        $command = implode(' ', array_map('escapeshellarg', [PHP_BINARY, '-r', $script, $method, $endpoint]));

        exec($command, $output, $exitCode);

        self::assertSame(0, $exitCode);
        self::assertSame((string) $status, array_pop($output));
        self::assertSame($body, implode("\n", $output));
    }

    public static function requestsRejectedBeforeDatabaseAccess(): array
    {
        return [
            'preflight' => ['OPTIONS', 204, ''],
            'unsupported method' => ['GET', 405, '{"error":"Only POST is supported on this endpoint"}'],
            'empty request' => ['POST', 400, '{"error":"Request body is empty"}'],
        ];
    }
}
