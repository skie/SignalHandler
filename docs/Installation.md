# Installation

## Composer

```
composer require skie/signal_handler
```

## Load the Plugin

Ensure the SignalHandler Plugin is loaded in your src/Application.php file

```
$this->addPlugin(\SignalHandler\Plugin::class);
```

The plugin automatically registers signal event listeners and integrates with CakePHP's console system. No additional configuration is required for basic signal handling functionality.

## Platform Support

The plugin automatically detects your platform and uses the appropriate signal handling method:

* **Linux**: Uses pcntl extension for signal handling
* **Windows**: Uses native Windows API for control events
* **macOS**: Uses pcntl extension (same as Linux)

The plugin gracefully degrades when signal handling is not available on your platform.

## Signal Handling Availability

The plugin checks for signal handling availability on your platform:

* Linux: Requires pcntl extension
* Windows: Requires sapi_windows_set_ctrl_handler function
* macOS: Requires pcntl extension

If signal handling is not available, the plugin will continue to work without signal handling capabilities.

## Automatic Integration

The plugin automatically integrates with CakePHP's console system through event listeners:

* Registers signal handlers before command execution
* Cleans up signal handlers after command completion
* Dispatches signal events during command execution
* Integrates with CakePHP's service container

No manual configuration is required for basic signal handling functionality.

## Customization

The plugin is designed to be extensible and customizable:

* Implement SignalableCommandInterface for custom signal handling
* Use SignalHandlerTrait for callback-based signal handling
* Create custom signal event listeners
* Extend platform-specific signal handlers

For detailed customization options, see the Integration documentation.
