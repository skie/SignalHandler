# SignalHandler Plugin

The **SignalHandler** plugin provides cross-platform signal handling for CakePHP console commands with the following features:

* Cross-platform signal handling (Linux, Windows, macOS)
* Graceful command termination with Ctrl+C and other signals
* Zero external dependencies - pure CakePHP implementation
* CakePHP event system integration
* Automatic signal handler registration and cleanup
* Support for long-running commands and infinite loops
* React event loop integration support

The plugin is designed to provide signal handling capabilities following 2 approaches:

* Quick drop-in solution for existing commands. Add signal handling in minutes.
* Extensible solution for custom signal handling. You can extend:
  * SignalableCommandInterface for custom signal handling
  * SignalHandlerTrait for callback based signal handling
  * Custom signal event listeners
  * Platform-specific signal handlers

The plugin integrates seamlessly with CakePHP's console system and maintains zero external dependencies.

## Requirements

* CakePHP 5.0+
* PHP 8.4+

## Documentation

For documentation, as well as tutorials, see the [Docs](docs/Home.md) directory of this repository.

## License

Licensed under the [MIT](http://www.opensource.org/licenses/mit-license.php) License. Redistributions of the source code included in this repository must retain the copyright notice found in each file.
