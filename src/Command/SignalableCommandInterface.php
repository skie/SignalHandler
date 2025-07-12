<?php
declare(strict_types=1);

namespace SignalHandler\Command;

/**
 * Interface for commands that can react to signals.
 *
 * Commands implementing this interface can subscribe to system signals
 * and handle them gracefully during execution. This allows for proper
 * cleanup and graceful termination of long-running commands.
 *
 * The interface provides callback methods that can be overridden by
 * concrete commands to handle different signal events.
 */
interface SignalableCommandInterface
{
    /**
     * Returns the list of signals to subscribe to.
     *
     * Return an array of signal constants (e.g., [SIGINT, SIGTERM])
     * that this command wants to handle. The command will be notified
     * when any of these signals are received.
     *
     * @return array<int> Array of signal constants
     */
    public function getSubscribedSignals(): array;

    /**
     * Called when the command is about to terminate.
     *
     * This callback is called before the command exits, allowing for
     * final cleanup operations. Override this method in concrete commands.
     *
     * @param int $exitCode The exit code that will be returned
     * @param int|null $interruptingSignal The signal that caused termination (if any)
     * @return void
     */
    public function onTerminate(int $exitCode, ?int $interruptingSignal = null): void;

    /**
     * Called when a SIGINT (Ctrl+C) signal is received.
     *
     * This callback is called when the user presses Ctrl+C. Override
     * this method in concrete commands for specific SIGINT handling.
     *
     * @return int|false The exit code to return, or false to continue execution
     */
    public function onInterrupt(): int|false;

    /**
     * Called when a SIGTERM signal is received.
     *
     * This callback is called when the process receives a termination
     * signal. Override this method in concrete commands for specific
     * SIGTERM handling.
     *
     * @return int|false The exit code to return, or false to continue execution
     */
    public function onTerminateSignal(): int|false;

    /**
     * Called when a SIGUSR1 signal is received.
     *
     * This callback is called when a SIGUSR1 signal is received.
     * Override this method in concrete commands for debug/reload handling.
     *
     * @return int|false The exit code to return, or false to continue execution
     */
    public function onUserSignal1(): int|false;

    /**
     * Called when a SIGUSR2 signal is received.
     *
     * This callback is called when a SIGUSR2 signal is received.
     * Override this method in concrete commands for debug/reload handling.
     *
     * @return int|false The exit code to return, or false to continue execution
     */
    public function onUserSignal2(): int|false;

    /**
     * Called when any signal is received.
     *
     * This is a general callback for any signal. Override this method
     * in concrete commands for general signal handling.
     *
     * @param int $signal The signal that was received
     * @return int|false The exit code to return, or false to continue execution
     */
    public function onSignal(int $signal): int|false;
}
