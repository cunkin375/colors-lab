<?php

declare(strict_types=1);

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class ApiHelpersTest extends TestCase
{
    public function testRequiredFieldsAreNormalized(): void
    {
        self::assertSame('Ada', requireField(['login' => ' Ada '], 'login'));
        self::assertSame('0', requireField(['color' => 0], 'color'));
        self::assertSame(7, requireInt(['userId' => '7'], 'userId'));
    }

    public function testOptionalFieldsUseDefaultsForMissingOrNonScalarValues(): void
    {
        self::assertSame('Blue', optionalField(['search' => ' Blue '], 'search'));
        self::assertSame('', optionalField([], 'search'));
        self::assertSame('default', optionalField(['search' => []], 'search', 'default'));
    }

    public function testColorSearchTreatsLikeWildcardsAsLiteralCharacters(): void
    {
        self::assertSame('Blue', escapeLike('Blue'));
        self::assertSame('Blue\\%\\_\\\\', escapeLike('Blue%_\\'));
    }

    public function testIssuedSessionTokenIsValidAndExpiresInOneHour(): void
    {
        $before = time();
        $session = issueSessionToken();
        $after = time();

        self::assertMatchesRegularExpression('/^[a-f0-9]{64}$/', $session['token']);
        self::assertTrue(verifySessionToken($session['token'], $session['expiresAt']));
        $expiresAt = (new DateTimeImmutable($session['expiresAt']))->getTimestamp();
        self::assertGreaterThanOrEqual($before + 3600, $expiresAt);
        self::assertLessThanOrEqual($after + 3600, $expiresAt);
    }

    #[DataProvider('invalidSessions')]
    public function testInvalidSessionsAreRejected(string $token, string $expiresAt): void
    {
        self::assertFalse(verifySessionToken($token, $expiresAt));
    }

    public static function invalidSessions(): array
    {
        $token = str_repeat('a', 64);
        $future = date('Y-m-d H:i:s', time() + 3600);
        $past = date('Y-m-d H:i:s', time() - 3600);

        return [
            'short token' => [str_repeat('a', 63), $future],
            'non-hex token' => [str_repeat('z', 64), $future],
            'invalid expiration' => [$token, 'invalid'],
            'expired token' => [$token, $past],
        ];
    }
}
