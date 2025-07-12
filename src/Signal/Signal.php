<?php
declare(strict_types=1);

namespace SignalHandler\Signal;

/**
 * Signal constants for cross-platform signal handling
 *
 * This class provides constants for common signal numbers that work
 * across different platforms (Linux, Windows, macOS).
 */
class Signal
{
    public const SIGTERM = 15;
    public const SIGINT = 2;
    public const SIGQUIT = 3;

    public const SIGUSR1 = 10;
    public const SIGUSR2 = 12;

    public const SIGHUP = 1;
    public const SIGKILL = 9;

    public const CTRL_C = 2;
    public const CTRL_BREAK = 3;

    public const CTRL_C_EVENT = 0;
    public const CTRL_BREAK_EVENT = 1;
    public const CTRL_CLOSE_EVENT = 2;
    public const CTRL_LOGOFF_EVENT = 5;
    public const CTRL_SHUTDOWN_EVENT = 6;

    /**
     * Get all available signal constants
     *
     * @return array<string, int>
     */
    public static function getAvailableSignals(): array
    {
        return [
            'SIGTERM' => self::SIGTERM,
            'SIGINT' => self::SIGINT,
            'SIGQUIT' => self::SIGQUIT,
            'SIGUSR1' => self::SIGUSR1,
            'SIGUSR2' => self::SIGUSR2,
            'SIGHUP' => self::SIGHUP,
            'SIGKILL' => self::SIGKILL,
            'CTRL_C' => self::CTRL_C,
            'CTRL_BREAK' => self::CTRL_BREAK,
            'CTRL_C_EVENT' => self::CTRL_C_EVENT,
            'CTRL_BREAK_EVENT' => self::CTRL_BREAK_EVENT,
            'CTRL_CLOSE_EVENT' => self::CTRL_CLOSE_EVENT,
            'CTRL_LOGOFF_EVENT' => self::CTRL_LOGOFF_EVENT,
            'CTRL_SHUTDOWN_EVENT' => self::CTRL_SHUTDOWN_EVENT,
        ];
    }

    /**
     * Check if a signal number is valid
     *
     * @param int $signalNumber
     * @return bool
     */
    public static function isValid(int $signalNumber): bool
    {
        return in_array($signalNumber, self::getAvailableSignals(), true);
    }

    /**
     * Get signal name by number
     *
     * @param int $signalNumber
     * @return string|null
     */
    public static function getName(int $signalNumber): ?string
    {
        $signals = array_flip(self::getAvailableSignals());

        return $signals[$signalNumber] ?? null;
    }
}
