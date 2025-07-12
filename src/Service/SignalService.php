<?php
declare(strict_types=1);

namespace SignalHandler\Service;

use SignalHandler\Command\SignalableCommandInterface;
use SignalHandler\Listener\SignalEventListener;
use SignalHandler\Signal\Signal;
use SignalHandler\Signal\SignalRegistry;

/**
 * Signal Service
 *
 * Provides signal management functionality through CakePHP's service container.
 * Manages signal event listeners and provides utilities for signal handling.
 */
class SignalService
{
    /**
     * The signal event listener instance
     *
     * @var \SignalHandler\Listener\SignalEventListener|null
     */
    protected ?SignalEventListener $eventListener = null;

    /**
     * The signal registry instance
     *
     * @var \SignalHandler\Signal\SignalRegistry|null
     */
    protected ?SignalRegistry $signalRegistry = null;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->signalRegistry = new SignalRegistry();
    }

    /**
     * Get or create the signal event listener
     *
     * @return \SignalHandler\Listener\SignalEventListener
     */
    public function getEventListener(): SignalEventListener
    {
        if ($this->eventListener === null) {
            $this->eventListener = new SignalEventListener();
        }

        return $this->eventListener;
    }

    /**
     * Check if signal handling is supported on this platform
     *
     * @return bool
     */
    public function isSupported(): bool
    {
        return SignalRegistry::isSupported();
    }

    /**
     * Check if signal handling is enabled
     *
     * @return bool
     */
    public function isEnabled(): bool
    {
        return $this->isSupported();
    }

    /**
     * Check if a command implements signal handling
     *
     * @param object $command The command to check
     * @return bool
     */
    public function isSignalableCommand(object $command): bool
    {
        return $command instanceof SignalableCommandInterface;
    }

    /**
     * Get subscribed signals for a command
     *
     * @param \SignalHandler\Command\SignalableCommandInterface $command The command
     * @return array<int>
     */
    public function getSubscribedSignals(SignalableCommandInterface $command): array
    {
        return $command->getSubscribedSignals();
    }

    /**
     * Handle command termination
     *
     * @param \SignalHandler\Command\SignalableCommandInterface $command The command
     * @param int $exitCode The exit code
     * @param int|null $interruptingSignal The signal that caused termination
     * @return void
     */
    public function handleTermination(
        SignalableCommandInterface $command,
        int $exitCode,
        ?int $interruptingSignal = null,
    ): void {
        $command->onTerminate($exitCode, $interruptingSignal);
    }

    /**
     * Register signal handlers for a command
     *
     * @param \SignalHandler\Command\SignalableCommandInterface $command The command
     * @return \SignalHandler\Signal\SignalRegistry|null The signal registry or null if not enabled
     */
    public function registerSignalHandlers(SignalableCommandInterface $command): ?SignalRegistry
    {
        if (!$this->isEnabled()) {
            return null;
        }

        if ($this->signalRegistry === null) {
            return null;
        }

        $subscribedSignals = $this->getSubscribedSignals($command);

        if (empty($subscribedSignals)) {
            return null;
        }

        foreach ($subscribedSignals as $signal) {
            $this->signalRegistry->register($signal, function (int $signal) use ($command): void {
                $this->handleSignal($command, $signal);
            });
        }

        return $this->signalRegistry;
    }

    /**
     * Unregister signal handlers for a command
     *
     * @param \SignalHandler\Command\SignalableCommandInterface $command The command
     * @return void
     */
    public function unregisterSignalHandlers(SignalableCommandInterface $command): void
    {
        if ($this->signalRegistry !== null) {
            $this->signalRegistry->unregister();
            $this->signalRegistry = null;
        }
    }

    /**
     * Handle a signal for a command
     *
     * @param \SignalHandler\Command\SignalableCommandInterface $command The command
     * @param int $signal The signal that was received
     * @return int|false The exit code or false to continue execution
     */
    public function handleSignal(SignalableCommandInterface $command, int $signal): int|false
    {
        return match ($signal) {
            Signal::SIGINT => $command->onInterrupt(),
            Signal::SIGTERM => $command->onTerminateSignal(),
            Signal::SIGUSR1 => $command->onUserSignal1(),
            Signal::SIGUSR2 => $command->onUserSignal2(),
            default => $command->onSignal($signal),
        };
    }

    /**
     * Get the current signal registry
     *
     * @return \SignalHandler\Signal\SignalRegistry|null
     */
    public function getSignalRegistry(): ?SignalRegistry
    {
        return $this->signalRegistry;
    }
}
