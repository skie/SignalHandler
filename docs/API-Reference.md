# API Reference

Complete API documentation for the SignalHandler plugin components and classes.

## SignalableCommandInterface

Interface for commands that can react to system signals.

### Methods

#### `getSubscribedSignals(): array`

Returns the list of signals to subscribe to.

**Returns:** `array<int>` Array of signal constants

**Example:**
```php
public function getSubscribedSignals(): array
{
    return [Signal::SIGINT, Signal::SIGTERM];
}
```

#### `onTerminate(int $exitCode, ?int $interruptingSignal = null): void`

Called when the command is about to terminate.

**Parameters:**
- `$exitCode` (int) - The exit code that will be returned
- `$interruptingSignal` (int|null) - The signal that caused termination (if any)

#### `onInterrupt(): int|false`

Called when a SIGINT (Ctrl+C) signal is received.

**Returns:** `int|false` The exit code to return, or false to continue execution

#### `onTerminateSignal(): int|false`

Called when a SIGTERM signal is received.

**Returns:** `int|false` The exit code to return, or false to continue execution

#### `onUserSignal1(): int|false`

Called when a SIGUSR1 signal is received.

**Returns:** `int|false` The exit code to return, or false to continue execution

#### `onUserSignal2(): int|false`

Called when a SIGUSR2 signal is received.

**Returns:** `int|false` The exit code to return, or false to continue execution

#### `onSignal(int $signal): int|false`

Called when any signal is received.

**Parameters:**
- `$signal` (int) - The signal that was received

**Returns:** `int|false` The exit code to return, or false to continue execution

## SignalHandlerTrait

Trait providing callback-based signal handling methods.

### Methods

#### `bindSignals(array $signals, callable $callback): void`

Registers signal handlers for the specified signals.

**Parameters:**
- `$signals` (array) - Array of signal constants
- `$callback` (callable) - Callback function to execute when signal is received

**Example:**
```php
$this->bindSignals([Signal::SIGINT, Signal::SIGTERM], function (int $signal) {
    $this->isRunning = false;
});
```

#### `unbindSignals(): void`

Removes all registered signal handlers and restores default signal behavior.

#### `bindGracefulTermination(callable $callback): void`

Convenience method that registers handlers for SIGINT and SIGTERM signals.

**Parameters:**
- `$callback` (callable) - Callback function for graceful termination

#### `bindDebugSignals(callable $callback): void`

Convenience method that registers handlers for debug signals (SIGUSR1, SIGUSR2, CTRL_BREAK).

**Parameters:**
- `$callback` (callable) - Callback function for debug signal handling

#### `handleSignal(int $signal, int|false $previousExitCode = 0): int|false`

Routes signals to the appropriate callback methods.

**Parameters:**
- `$signal` (int) - The signal that was received
- `$previousExitCode` (int|false) - The previous exit code (if any)

**Returns:** `int|false` The exit code to return, or false to continue normal execution

## SignalRegistry

Core signal registration and management class.

### Methods

#### `register(int $signal, callable $signalHandler): void`

Register a signal handler.

**Parameters:**
- `$signal` (int) - The signal to register (e.g., SIGINT, SIGTERM)
- `$signalHandler` (callable) - The handler callback

**Throws:** `RuntimeException` When signal handling is not supported

#### `handle(int $signal): void`

Handle a signal by calling all registered handlers.

**Parameters:**
- `$signal` (int) - The signal that was received

#### `unregister(): void`

Unregister all signal handlers and restore defaults.

#### `scheduleAlarm(int $seconds): void`

Schedule an alarm signal.

**Parameters:**
- `$seconds` (int) - Seconds until alarm

#### `isSupported(): bool`

Check if signal handling is supported on this platform.

**Returns:** `bool` True if signal handling is supported

## PlatformDetector

Operating system detection and platform-specific signal availability checking.

### Methods

#### `getOSFamily(): string`

Get the operating system family.

**Returns:** `string` Operating system family (linux, windows, macos, unknown)

#### `isLinux(): bool`

Check if the current platform is Linux.

**Returns:** `bool` True if Linux

#### `isWindows(): bool`

Check if the current platform is Windows.

**Returns:** `bool` True if Windows

#### `isMacOS(): bool`

Check if the current platform is macOS.

**Returns:** `bool` True if macOS

#### `isSignalHandlingAvailable(): bool`

Check if signal handling is available on the current platform.

**Returns:** `bool` True if signal handling is available

## Signal

Signal constants for cross-platform signal handling.

### Constants

#### Linux/macOS Signals
- `Signal::SIGTERM` (15) - Termination signal
- `Signal::SIGINT` (2) - Interrupt signal (Ctrl+C)
- `Signal::SIGQUIT` (3) - Quit signal
- `Signal::SIGUSR1` (10) - User signal 1
- `Signal::SIGUSR2` (12) - User signal 2
- `Signal::SIGHUP` (1) - Hangup signal
- `Signal::SIGKILL` (9) - Kill signal

#### Windows Signals
- `Signal::CTRL_C` (2) - Ctrl+C event
- `Signal::CTRL_BREAK` (3) - Ctrl+Break event

### Methods

#### `getAvailableSignals(): array`

Get all available signal constants.

**Returns:** `array<string, int>` Array of signal name to constant mapping

#### `isValid(int $signalNumber): bool`

Check if a signal number is valid.

**Parameters:**
- `$signalNumber` (int) - Signal number to validate

**Returns:** `bool` True if valid

#### `getName(int $signalNumber): ?string`

Get signal name by number.

**Parameters:**
- `$signalNumber` (int) - Signal number

**Returns:** `string|null` Signal name or null if not found

## SignalService

Service for managing signal handling through CakePHP's service container.

### Methods

#### `getEventListener(): SignalEventListener`

Get or create the signal event listener.

**Returns:** `SignalEventListener` The event listener instance

#### `isSupported(): bool`

Check if signal handling is supported on this platform.

**Returns:** `bool` True if supported

#### `isEnabled(): bool`

Check if signal handling is enabled.

**Returns:** `bool` True if enabled

#### `isSignalableCommand(object $command): bool`

Check if a command implements signal handling.

**Parameters:**
- `$command` (object) - The command to check

**Returns:** `bool` True if command implements SignalableCommandInterface

#### `registerSignalHandlers(SignalableCommandInterface $command): ?SignalRegistry`

Register signal handlers for a command.

**Parameters:**
- `$command` (SignalableCommandInterface) - The command to register handlers for

**Returns:** `SignalRegistry|null` The signal registry or null if not enabled

#### `unregisterSignalHandlers(SignalableCommandInterface $command): void`

Unregister signal handlers for a command.

**Parameters:**
- `$command` (SignalableCommandInterface) - The command to unregister handlers for

#### `handleSignal(SignalableCommandInterface $command, int $signal): int|false`

Handle a signal for a command.

**Parameters:**
- `$command` (SignalableCommandInterface) - The command
- `$signal` (int) - The signal that was received

**Returns:** `int|false` The exit code or false to continue execution

## SignalEventListener

CakePHP event listener for automatic signal handling integration.

### Methods

#### `implementedEvents(): array`

Returns a list of events this object is implementing.

**Returns:** `array<string, mixed>` Event mapping

#### `beforeCommandExecute(EventInterface $event): void`

Handle Command.beforeExecute event - registers signal handlers.

**Parameters:**
- `$event` (EventInterface) - The event object

#### `afterCommandExecute(EventInterface $event): void`

Handle Command.afterExecute event - cleans up signal handlers.

**Parameters:**
- `$event` (EventInterface) - The event object

#### `getSignalRegistry(): ?SignalRegistry`

Get the current signal registry.

**Returns:** `SignalRegistry|null` The signal registry or null

#### `getCurrentCommand(): ?SignalableCommandInterface`

Get the current command.

**Returns:** `SignalableCommandInterface|null` The current command or null

## WindowsSignalHandler

Windows-specific signal handling using Windows API.

### Methods

#### `register(int $signal, callable $handler): void`

Register a Windows signal handler.

**Parameters:**
- `$signal` (int) - The signal to register
- `$handler` (callable) - The handler callback

#### `unregister(): void`

Unregister Windows signal handlers.

## Events

### Command.beforeExecute

Dispatched before command execution to register signal handlers.

**Data:**
- `args` (Arguments) - Command arguments

### Command.afterExecute

Dispatched after command execution to clean up signal handlers.

**Data:**
- `args` (Arguments) - Command arguments
- `result` (int|null) - Command result

### SignalHandler.signal

Dispatched when a signal is received.

**Data:**
- `signal` (int) - The signal that was received
- `command` (SignalableCommandInterface) - The command that received the signal
