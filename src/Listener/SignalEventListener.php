<?php
declare(strict_types=1);

namespace SignalHandler\Listener;

use Cake\Event\EventInterface;
use Cake\Event\EventListenerInterface;
use SignalHandler\Command\SignalableCommandInterface;
use SignalHandler\Service\SignalService;
use SignalHandler\Signal\SignalRegistry;

/**
 * Signal Event Listener
 *
 * Handles signal events during command execution by automatically
 * registering and managing signal handlers for commands that implement
 * SignalableCommandInterface.
 */
class SignalEventListener implements EventListenerInterface
{
    /**
     * The current command being executed
     *
     * @var \SignalHandler\Command\SignalableCommandInterface|null
     */
    protected ?SignalableCommandInterface $currentCommand = null;

    /**
     * The signal service instance
     *
     * @var \SignalHandler\Service\SignalService
     */
    protected SignalService $signalService;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->signalService = new SignalService();
    }

    /**
     * Returns a list of events this object is implementing.
     *
     * @return array<string, mixed>
     */
    public function implementedEvents(): array
    {
        return [
            'Command.beforeExecute' => 'beforeCommandExecute',
            'Command.afterExecute' => 'afterCommandExecute',
        ];
    }

    /**
     * Handle Command.beforeExecute event
     *
     * Registers signal handlers for commands that implement SignalableCommandInterface
     *
     * @param \Cake\Event\EventInterface<\Cake\Command\Command> $event The event object
     * @return void
     */
    public function beforeCommandExecute(EventInterface $event): void
    {
        $command = $event->getSubject();

        if (!$command instanceof SignalableCommandInterface) {
            return;
        }

        if (!$this->signalService->isSignalableCommand($command)) {
            return;
        }

        $this->currentCommand = $command;
        $this->signalService->registerSignalHandlers($command);
    }

    /**
     * Handle Command.afterExecute event
     *
     * Cleans up signal handlers after command execution
     *
     * @param \Cake\Event\EventInterface<\Cake\Command\Command> $event The event object
     * @return void
     */
    public function afterCommandExecute(EventInterface $event): void
    {
        if ($this->currentCommand) {
            $this->signalService->unregisterSignalHandlers($this->currentCommand);
        }

        $this->currentCommand = null;
    }

    /**
     * Get the current signal registry
     *
     * @return \SignalHandler\Signal\SignalRegistry|null
     */
    public function getSignalRegistry(): ?SignalRegistry
    {
        return $this->signalService->getSignalRegistry();
    }

    /**
     * Get the current command
     *
     * @return \SignalHandler\Command\SignalableCommandInterface|null
     */
    public function getCurrentCommand(): ?SignalableCommandInterface
    {
        return $this->currentCommand;
    }
}
