<?php
declare(strict_types=1);

namespace SignalHandler\Command\Trait;

use SignalHandler\Signal\Signal;
use SignalHandler\Signal\SignalRegistry;

/**
 * Trait for adding signal handling capabilities to commands.
 *
 * Provides simple methods for registering and managing signal handlers
 * in CakePHP commands.
 *
 * This trait provides empty default implementations for all callback
 * methods defined in SignalableCommandInterface.
 */
trait SignalHandlerTrait
{
    /**
     * The signal registry instance.
     *
     * @var \SignalHandler\Signal\SignalRegistry|null
     */
    protected ?SignalRegistry $signals = null;

    /**
     * Define a callback to be run when the given signal(s) occurs.
     *
     * @param array<int>|int $signals The signal(s) to bind
     * @param callable(int $signal): void $callback The callback to execute
     * @return void
     */
    public function bindSignals(int|array $signals, callable $callback): void
    {
        if (!$this->isSignalHandlingAvailable()) {
            return;
        }

        $this->signals ??= new SignalRegistry();

        $signalArray = is_array($signals) ? $signals : [$signals];

        foreach ($signalArray as $signal) {
            $this->signals->register($signal, $callback);
        }
    }

    /**
     * Unbind all signal handlers set within the command.
     *
     * @return void
     */
    public function unbindSignals(): void
    {
        if ($this->signals !== null) {
            $this->signals->unregister();
            $this->signals = null;
        }
    }

    /**
     * Check if signal handling is available on this platform.
     *
     * @return bool
     */
    protected function isSignalHandlingAvailable(): bool
    {
        return SignalRegistry::isSupported();
    }

    /**
     * Get the signal registry instance.
     *
     * @return \SignalHandler\Signal\SignalRegistry|null
     */
    protected function getSignalRegistry(): ?SignalRegistry
    {
        return $this->signals;
    }

    /**
     * Register common signal handlers for graceful termination.
     *
     * @param callable(int $signal): void $callback The callback to execute
     * @return void
     */
    protected function bindGracefulTermination(callable $callback): void
    {
        $this->bindSignals([Signal::SIGINT, Signal::SIGTERM], $callback);
    }

    /**
     * Register signal handlers for debugging.
     *
     * @param callable(int $signal): void $callback The callback to execute
     * @return void
     */
    protected function bindDebugSignals(callable $callback): void
    {
        $this->bindSignals([Signal::SIGUSR1, Signal::SIGUSR2, Signal::CTRL_BREAK], $callback);
    }

    /**
     * Called when the command is about to terminate.
     *
     * Default implementation does nothing. Override in concrete commands.
     *
     * @param int $exitCode The exit code that will be returned
     * @param int|null $interruptingSignal The signal that caused termination (if any)
     * @return void
     */
    public function onTerminate(int $exitCode, ?int $interruptingSignal = null): void
    {
    }

    /**
     * Called when a SIGINT (Ctrl+C) signal is received.
     *
     * Default implementation returns false (continue execution).
     * Override in concrete commands for specific SIGINT handling.
     *
     * @return int|false The exit code to return, or false to continue execution
     */
    public function onInterrupt(): int|false
    {
        return false;
    }

    /**
     * Called when a SIGTERM signal is received.
     *
     * Default implementation returns 0 (normal termination).
     * Override in concrete commands for specific SIGTERM handling.
     *
     * @return int|false The exit code to return, or false to continue execution
     */
    public function onTerminateSignal(): int|false
    {
        return 0;
    }

    /**
     * Called when a SIGUSR1 signal is received.
     *
     * Default implementation returns false (continue execution).
     * Override in concrete commands for debug/reload handling.
     *
     * @return int|false The exit code to return, or false to continue execution
     */
    public function onUserSignal1(): int|false
    {
        return false;
    }

    /**
     * Called when a SIGUSR2 signal is received.
     *
     * Default implementation returns false (continue execution).
     * Override in concrete commands for debug/reload handling.
     *
     * @return int|false The exit code to return, or false to continue execution
     */
    public function onUserSignal2(): int|false
    {
        return false;
    }

    /**
     * Called when a CTRL_BREAK signal is received (Windows).
     *
     * Default implementation returns false (continue execution).
     * Override in concrete commands for Windows debug handling.
     *
     * @return int|false The exit code to return, or false to continue execution
     */
    public function onCtrlBreak(): int|false
    {
        return false;
    }

    /**
     * Called when any signal is received.
     *
     * Default implementation returns false (continue execution).
     * Override in concrete commands for general signal handling.
     *
     * @param int $signal The signal that was received
     * @return int|false The exit code to return, or false to continue execution
     */
    public function onSignal(int $signal): int|false
    {
        return false;
    }

    /**
     * Returns the list of signals to subscribe to.
     *
     * Default implementation returns empty array. Override in concrete commands
     * to specify which signals this command wants to handle.
     *
     * @return array<int> Array of signal constants
     */
    public function getSubscribedSignals(): array
    {
        return [];
    }

    /**
     * Handle a signal that was received.
     *
     * This method routes signals to the appropriate callback methods.
     * Override in concrete commands for custom signal routing.
     *
     * @param int $signal The signal that was received
     * @param int|false $previousExitCode The previous exit code (if any)
     * @return int|false The exit code to return, or false to continue normal execution
     */
    public function handleSignal(int $signal, int|false $previousExitCode = 0): int|false
    {
        switch ($signal) {
            case Signal::SIGINT:
                return $this->onInterrupt();

            case Signal::SIGTERM:
                return $this->onTerminateSignal();

            case Signal::SIGUSR1:
                return $this->onUserSignal1();

            case Signal::SIGUSR2:
                return $this->onUserSignal2();

            case Signal::CTRL_BREAK:
                return $this->onCtrlBreak();

            default:
                return $this->onSignal($signal);
        }
    }
}
