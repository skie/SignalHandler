<?php
declare(strict_types=1);

namespace SignalHandler\Signal;

use ReflectionExtension;

/**
 * Platform detector for cross-platform signal handling.
 *
 * Detects the operating system and provides platform-specific
 * signal handling capabilities for Linux and Windows.
 */
class PlatformDetector
{
    /**
     * Check if the current platform is Linux.
     *
     * @return bool
     */
    public function isLinux(): bool
    {
        return PHP_OS_FAMILY === 'Linux';
    }

    /**
     * Check if the current platform is Windows.
     *
     * @return bool
     */
    public function isWindows(): bool
    {
        return PHP_OS_FAMILY === 'Windows';
    }

    /**
     * Check if the current platform is macOS.
     *
     * @return bool
     */
    public function isMacOS(): bool
    {
        return PHP_OS_FAMILY === 'Darwin';
    }

    /**
     * Get the current operating system family.
     *
     * @return string
     */
    public function getOSFamily(): string
    {
        return PHP_OS_FAMILY;
    }

    /**
     * Check if signal handling is available on this platform.
     *
     * @return bool
     */
    public function isSignalHandlingAvailable(): bool
    {
        if ($this->isLinux()) {
            return function_exists('pcntl_signal');
        }

        if ($this->isWindows()) {
            return function_exists('sapi_windows_set_ctrl_handler');
        }

        return false;
    }

    /**
     * Get platform-specific signal constants.
     *
     * @return array<string, int>
     */
    public function getSignalConstants(): array
    {
        if ($this->isLinux()) {
            return $this->getLinuxSignalConstants();
        }

        if ($this->isWindows()) {
            return $this->getWindowsSignalConstants();
        }

        return [];
    }

    /**
     * Get Linux signal constants.
     *
     * @return array<string, int>
     */
    private function getLinuxSignalConstants(): array
    {
        if (!extension_loaded('pcntl')) {
            return [];
        }

        $constants = [];
        $reflection = new ReflectionExtension('pcntl');
        $pcntlConstants = $reflection->getConstants();

        foreach ($pcntlConstants as $name => $value) {
            if (
                str_starts_with($name, 'SIG') &&
                !str_starts_with($name, 'SIG_') &&
                $name !== 'SIGBABY' &&
                is_int($value)
            ) {
                $constants[$name] = $value;
            }
        }

        return $constants;
    }

    /**
     * Get Windows signal constants.
     *
     * @return array<string, int>
     */
    private function getWindowsSignalConstants(): array
    {
        return [
            'CTRL_C_EVENT' => Signal::CTRL_C_EVENT,
            'CTRL_BREAK_EVENT' => Signal::CTRL_BREAK_EVENT,
            'CTRL_CLOSE_EVENT' => Signal::CTRL_CLOSE_EVENT,
            'CTRL_LOGOFF_EVENT' => Signal::CTRL_LOGOFF_EVENT,
            'CTRL_SHUTDOWN_EVENT' => Signal::CTRL_SHUTDOWN_EVENT,
        ];
    }
}
