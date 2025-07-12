<?php
declare(strict_types=1);

namespace SignalHandler\Signal;

use RuntimeException;

/**
 * Signal Registry for handling system signals in CakePHP commands.
 *
 * Provides cross-platform signal handling with support for Linux (via pcntl)
 * and Windows (via native APIs). Manages signal handler registration and
 * cleanup for graceful command termination.
 */
final class SignalRegistry
{
    /**
     * Registered signal handlers.
     *
     * @var array<int, array<int, callable>>
     */
    private array $signalHandlers = [];

    /**
     * Platform detector instance.
     *
     * @var \SignalHandler\Signal\PlatformDetector
     */
    private PlatformDetector $platformDetector;

    /**
     * Windows signal handler instance.
     *
     * @var \SignalHandler\Signal\WindowsSignalHandler|null
     */
    private ?WindowsSignalHandler $windowsHandler = null;

    /**
     * Constructor.
     *
     * @param \SignalHandler\Signal\PlatformDetector|null $platformDetector Platform detector instance
     */
    public function __construct(?PlatformDetector $platformDetector = null)
    {
        $this->platformDetector = $platformDetector ?? new PlatformDetector();

        if ($this->platformDetector->isLinux() && function_exists('pcntl_async_signals')) {
            pcntl_async_signals(true);
        }
    }

    /**
     * Register a signal handler.
     *
     * @param int $signal The signal to register (e.g., SIGINT, SIGTERM)
     * @param callable $signalHandler The handler callback
     * @return void
     * @throws \RuntimeException When signal handling is not supported
     */
    public function register(int $signal, callable $signalHandler): void
    {
        if (!$this->isSupported()) {
            throw new RuntimeException(
                'Signal handling is not supported on this platform. ' .
                'Make sure that the "pcntl" extension is installed on Linux ' .
                'or use Windows-compatible signal handling.',
            );
        }

        if (!isset($this->signalHandlers[$signal])) {
            $previousCallback = $this->getExistingHandler($signal);

            if (is_callable($previousCallback)) {
                $this->signalHandlers[$signal][] = $previousCallback;
            }
        }

        $this->signalHandlers[$signal][] = $signalHandler;

        $this->setSignalHandler($signal, $this->handle(...));
    }

    /**
     * Check if signal handling is supported on this platform.
     *
     * @return bool
     */
    public static function isSupported(): bool
    {
        $platformDetector = new PlatformDetector();

        if ($platformDetector->isLinux()) {
            return function_exists('pcntl_signal');
        }

        if ($platformDetector->isWindows()) {
            return function_exists('sapi_windows_set_ctrl_handler');
        }

        return false;
    }

    /**
     * Handle a signal by calling all registered handlers.
     *
     * @param int $signal The signal that was received
     * @return void
     * @internal
     */
    public function handle(int $signal): void
    {
        if (!isset($this->signalHandlers[$signal])) {
            return;
        }

        $count = count($this->signalHandlers[$signal]);

        foreach ($this->signalHandlers[$signal] as $i => $signalHandler) {
            $hasNext = $i !== $count - 1;
            $signalHandler($signal, $hasNext);
        }
    }

    /**
     * Schedule an alarm signal.
     *
     * @param int $seconds Seconds until alarm
     * @return void
     */
    public function scheduleAlarm(int $seconds): void
    {
        if ($this->platformDetector->isLinux() && function_exists('pcntl_alarm')) {
            pcntl_alarm($seconds);
        }
    }

    /**
     * Unregister all signal handlers and restore defaults.
     *
     * @return void
     */
    public function unregister(): void
    {
        foreach ($this->signalHandlers as $signal => $handlers) {
            $this->restoreDefaultHandler($signal);
        }

        $this->signalHandlers = [];

        if ($this->windowsHandler !== null) {
            $this->windowsHandler->unregister();
            $this->windowsHandler = null;
        }
    }

    /**
     * Get existing signal handler for a signal.
     *
     * @param int $signal The signal
     * @return callable|int|null
     */
    private function getExistingHandler(int $signal): callable|int|null
    {
        if ($this->platformDetector->isLinux() && function_exists('pcntl_signal_get_handler')) {
            $handler = pcntl_signal_get_handler($signal);
            if (is_callable($handler) || is_int($handler)) {
                return $handler;
            }
        }

        return null;
    }

    /**
     * Set signal handler for a signal.
     *
     * @param int $signal The signal
     * @param callable $handler The handler
     * @return void
     */
    private function setSignalHandler(int $signal, callable $handler): void
    {
        if ($this->platformDetector->isLinux() && function_exists('pcntl_signal')) {
            pcntl_signal($signal, $handler);
        } elseif ($this->platformDetector->isWindows() && function_exists('sapi_windows_set_ctrl_handler')) {
            if ($this->windowsHandler === null) {
                $this->windowsHandler = new WindowsSignalHandler();
            }

            $windowsHandler = function (int $windowsEvent) use ($signal): void {
                $this->handle($signal);
            };

            $windowsSignal = match ($signal) {
                Signal::CTRL_C => 0,
                Signal::CTRL_BREAK => 1,
                default => $signal,
            };

            $this->windowsHandler->register($windowsSignal, $windowsHandler);
        }
    }

    /**
     * Restore default signal handler.
     *
     * @param int $signal The signal
     * @return void
     */
    private function restoreDefaultHandler(int $signal): void
    {
        if ($this->platformDetector->isLinux() && function_exists('pcntl_signal')) {
            pcntl_signal($signal, SIG_DFL);
        } elseif ($this->platformDetector->isWindows() && $this->windowsHandler !== null) {
            $this->windowsHandler->unregister();
        }
    }
}
