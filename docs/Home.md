# SignalHandler Plugin Documentation

This plugin provides cross-platform signal handling for CakePHP console commands, allowing graceful termination of long-running commands.

## Quick Start

The SignalHandler plugin provides signal handling capabilities for CakePHP console commands with zero external dependencies. It supports cross-platform signal handling on Linux, Windows, and macOS.

## Features

* **Cross-Platform Support**: Works on Linux (pcntl), Windows (native API), and macOS
* **Zero Dependencies**: Pure CakePHP implementation with no external packages
* **Event Integration**: Seamlessly integrates with CakePHP's event system
* **Automatic Cleanup**: Signal handlers are automatically registered and cleaned up
* **React Support**: Works with React event loops and infinite-running servers

## Documentation Sections

* [Installation](Installation.md) - How to install and configure the plugin
* [Integration](Integration.md) - How to integrate signal handling into your commands
* [API Reference](API-Reference.md) - Complete API documentation

## Basic Concepts

The SignalHandler plugin provides signal handling through several key components:

* **SignalableCommandInterface**: Interface for commands that can handle signals
* **SignalHandlerTrait**: Trait providing callback-based signal handling methods
* **SignalRegistry**: Core signal registration and management
* **SignalEventListener**: CakePHP event listener for automatic integration
* **PlatformDetector**: Cross-platform signal handling detection

## Getting Help

If you need help with the SignalHandler plugin:

* Review the [API Reference](API-Reference.md) for detailed documentation
* Look at the test files in the `tests/` directory for implementation examples
