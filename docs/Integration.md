# Integration

The SignalHandler plugin provides multiple ways to integrate signal handling into your CakePHP console commands. Choose the approach that best fits your needs.

## Basic Integration

For simple signal handling, implement the SignalableCommandInterface:

```php
use SignalHandler\Command\SignalableCommandInterface;
use SignalHandler\Signal\Signal;

class MyCommand extends Command implements SignalableCommandInterface
{
    public function getSubscribedSignals(): array
    {
        return [Signal::SIGINT, Signal::SIGTERM];
    }

    public function onInterrupt(): int|false
    {
        // Handle Ctrl+C
        return self::CODE_SUCCESS;
    }

    public function onTerminateSignal(): int|false
    {
        // Handle SIGTERM
        return self::CODE_SUCCESS;
    }

    public function onTerminate(int $exitCode, ?int $interruptingSignal = null): void
    {
        // Cleanup before exit
    }
}
```

## Callback-based Integration

Plugin provides a trait that allows you to register signal handlers using callbacks. It provides methods to subscribe and unsubscribe to signals.

### Bind and Unbind Methods

The SignalHandlerTrait provides several methods for registering and managing signal handlers:

#### `bindSignals(array $signals, callable $callback): void`

Registers signal handlers for the specified signals. When any of the signals are received, the callback function is executed.

```php
// Bind handlers for SIGINT and SIGTERM
$this->bindSignals([Signal::SIGINT, Signal::SIGTERM], function (int $signal) {
    // Handle signal
    $this->isRunning = false;
});
```

#### `unbindSignals(): void`

Removes all registered signal handlers and restores default signal behavior.

```php
// Clean up signal handlers
$this->unbindSignals();
```

#### `bindGracefulTermination(callable $callback): void`

Convenience method that registers handlers for SIGINT and SIGTERM signals for graceful termination.

```php
$this->bindGracefulTermination(function (int $signal) {
    // Graceful termination logic
    $this->isRunning = false;
});
```

#### `bindDebugSignals(callable $callback): void`

Convenience method that binds handlers for debug signals (SIGUSR1, SIGUSR2, CTRL_BREAK).

```php
$this->bindDebugSignals(function (int $signal) {
    // Debug signal handling
    $this->dumpDebugInfo();
});
```

### Example

For callback-based signal handling, use the SignalHandlerTrait:

```php
use SignalHandler\Command\Trait\SignalHandlerTrait;
use SignalHandler\Signal\Signal;

class MyCommand extends Command
{
    use SignalHandlerTrait;

    public function execute(Arguments $args, ConsoleIo $io): ?int
    {
        $this->bindGracefulTermination(function (int $signal) use ($io): void {
            $this->handleGracefulTermination($signal, $io);
        });

        while ($this->isRunning) {
            sleep(1);
        }

        return self::CODE_SUCCESS;
    }

    protected function handleGracefulTermination(int $signal, ConsoleIo $io): void
    {
        $io->out('Gracefully terminating...');
        $this->isRunning = false;
    }
}
```



## React Event Loop Integration

For React-based servers, implement SignalableCommandInterface and call loop->stop():

```php
use SignalHandler\Command\SignalableCommandInterface;
use React\EventLoop\LoopInterface;

class ServerCommand extends Command implements SignalableCommandInterface
{
    protected ?LoopInterface $loop = null;

    public function getSubscribedSignals(): array
    {
        return [Signal::SIGINT, Signal::SIGTERM];
    }

    public function onInterrupt(): int|false
    {
        if ($this->loop) {
            $this->loop->stop();
        }
        return self::CODE_SUCCESS;
    }

    public function execute(Arguments $args, ConsoleIo $io): ?int
    {
        $server = $this->createServer();
        $this->loop = $server->getLoop();

        $server->start(); // This runs the React loop

        return self::CODE_SUCCESS;
    }
}
```

## Event-Based Integration

The plugin automatically integrates with CakePHP's event system:

```php
// SignalEventListener automatically handles:
// - Command.beforeExecute: Registers signal handlers
// - Command.afterExecute: Cleans up signal handlers

// You can also listen to signal events:
$eventManager->on('SignalHandler.signal', function ($event) {
    $signal = $event->getData('signal');
    $command = $event->getData('command');

    // Handle signal event
});
```

## Service Container Integration

The plugin registers services in the container:

```php
// Get the signal service
$signalService = $container->get(\SignalHandler\Service\SignalService::class);

// Check if signal handling is enabled
if ($signalService->isEnabled()) {
    // Signal handling is available
}

// Get the signal registry
$registry = $signalService->getSignalRegistry();
```

## Cross-Platform Considerations

The plugin handles platform differences automatically:

```php
// Linux/macOS signals
Signal::SIGINT   // Ctrl+C
Signal::SIGTERM  // Termination signal
Signal::SIGUSR1  // User signal 1
Signal::SIGUSR2  // User signal 2

// Windows signals
Signal::CTRL_C     // Ctrl+C
Signal::CTRL_BREAK // Ctrl+Break
```

## Testing Signal Handling

Test your signal handling implementation:

```php
use SignalHandler\Signal\Signal;
use SignalHandler\Command\Trait\SignalHandlerTrait;

class TestSignalCommand extends Command
{
    use SignalHandlerTrait;

    public function execute(Arguments $args, ConsoleIo $io): ?int
    {
        $sleepTime = (int)$args->getOption('time', 20);

        $this->bindGracefulTermination(function (int $signal) use ($io): void {
            $io->out('Signal received, terminating gracefully');
            $this->isRunning = false;
        });

        $this->isRunning = true;
        $remaining = $sleepTime;

        while ($this->isRunning && $remaining > 0) {
            usleep(100000); // 100ms
            $remaining -= 0.1;
        }

        return self::CODE_SUCCESS;
    }
}
```

Run the test command:

```bash
bin/cake test_signal --time=30
```

Then press Ctrl+C to test signal handling.
