<?php
declare(strict_types=1);

namespace SignalHandler\Test\TestCase\Command\Trait;

use SignalHandler\Command\SignalableCommandInterface;
use SignalHandler\Command\Trait\SignalHandlerTrait;
use SignalHandler\Signal\Signal;
use SignalHandler\Signal\SignalRegistry;

/**
 * Test command class for testing SignalHandlerTrait
 */
class TestCommand implements SignalableCommandInterface
{
    use SignalHandlerTrait;

    /**
     * Test bindSignals method
     *
     * @param array<int>|int $signals The signals to bind
     * @param callable(int): void $callback The callback to execute
     * @return void
     */
    public function testBindSignals(int|array $signals, callable $callback): void
    {
        $this->bindSignals($signals, $callback);
    }

    /**
     * Test unbindSignals method
     *
     * @return void
     */
    public function testUnbindSignals(): void
    {
        $this->unbindSignals();
    }

    /**
     * Test isSignalHandlingAvailable method
     *
     * @return bool
     */
    public function testIsSignalHandlingAvailable(): bool
    {
        return $this->isSignalHandlingAvailable();
    }

    /**
     * Test getSignalRegistry method
     *
     * @return \SignalHandler\Signal\SignalRegistry|null
     */
    public function testGetSignalRegistry(): ?SignalRegistry
    {
        return $this->getSignalRegistry();
    }

    /**
     * Test bindGracefulTermination method
     *
     * @param callable(int): void $callback The callback to execute
     * @return void
     */
    public function testBindGracefulTermination(callable $callback): void
    {
        $this->bindGracefulTermination($callback);
    }

    /**
     * Test bindDebugSignals method
     *
     * @param callable(int): void $callback The callback to execute
     * @return void
     */
    public function testBindDebugSignals(callable $callback): void
    {
        $this->bindDebugSignals($callback);
    }

    /**
     * Test getSubscribedSignals method
     *
     * @return array<int>
     */
    public function testGetSubscribedSignals(): array
    {
        return $this->getSubscribedSignals();
    }

    /**
     * Test handleSignal method
     *
     * @param int $signal The signal to handle
     * @param int|false $previousExitCode The previous exit code
     * @return int|false
     */
    public function testHandleSignal(int $signal, int|false $previousExitCode = 0): int|false
    {
        return $this->handleSignal($signal, $previousExitCode);
    }

    /**
     * Get subscribed signals
     *
     * @return array<int>
     */
    public function getSubscribedSignals(): array
    {
        return [Signal::SIGINT, Signal::SIGTERM];
    }

    /**
     * Handle a signal
     *
     * @param int $signal The signal to handle
     * @param int|false $previousExitCode The previous exit code
     * @return int|false
     */
    public function handleSignal(int $signal, int|false $previousExitCode = 0): int|false
    {
        return match ($signal) {
            Signal::SIGINT => $this->onInterrupt(),
            Signal::SIGTERM => $this->onTerminateSignal(),
            Signal::SIGUSR1 => $this->onUserSignal1(),
            Signal::SIGUSR2 => $this->onUserSignal2(),
            default => $this->onSignal($signal),
        };
    }
}
