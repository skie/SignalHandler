<?php
declare(strict_types=1);

namespace SignalHandler\Signal;

/**
 * Windows-specific signal handler.
 *
 * Provides signal handling for Windows using the Windows API.
 * Handles CTRL+C, CTRL+BREAK, and other Windows control events.
 */
class WindowsSignalHandler
{
    /**
     * Registered signal handlers.
     *
     * @var array<int, callable>
     */
    private array $signalHandlers = [];

    /**
     * Whether the handler is active.
     *
     * @var bool
     */
    private bool $isActive = false;

    /**
     * Register a signal handler for Windows control events.
     *
     * @param int $signal The Windows signal constant
     * @param callable(int): void $handler The handler callback
     * @return bool True if registration was successful
     */
    public function register(int $signal, callable $handler): bool
    {
        if (!function_exists('sapi_windows_set_ctrl_handler')) {
            return false;
        }

        $this->signalHandlers[$signal] = $handler;

        $this->isActive = sapi_windows_set_ctrl_handler(function (int $event): void {
            $this->handle($event);
        }, true);

        return $this->isActive;
    }

    /**
     * Unregister all signal handlers.
     *
     * @return void
     */
    public function unregister(): void
    {
        if ($this->isActive && function_exists('sapi_windows_set_ctrl_handler')) {
            sapi_windows_set_ctrl_handler(null, true);
            $this->isActive = false;
        }

        $this->signalHandlers = [];
    }

    /**
     * Handle Windows control events.
     *
     * @param int $event The Windows control event
     * @return void
     */
    private function handle(int $event): void
    {
        if (!isset($this->signalHandlers[$event])) {
            return;
        }

        $handler = $this->signalHandlers[$event];
        $handler($event);
    }

    /**
     * Check if Windows signal handling is supported.
     *
     * @return bool
     */
    public static function isSupported(): bool
    {
        return function_exists('sapi_windows_set_ctrl_handler');
    }
}
