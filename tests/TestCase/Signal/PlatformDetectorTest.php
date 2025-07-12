<?php
declare(strict_types=1);

namespace SignalHandler\Test\TestCase\Signal;

use Cake\TestSuite\TestCase;
use SignalHandler\Signal\PlatformDetector;

/**
 * PlatformDetector Test Case
 */
class PlatformDetectorTest extends TestCase
{
    /**
     * Test subject
     *
     * @var \SignalHandler\Signal\PlatformDetector
     */
    protected PlatformDetector $PlatformDetector;

    /**
     * setUp method
     *
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();
        $this->PlatformDetector = new PlatformDetector();
    }

    /**
     * tearDown method
     *
     * @return void
     */
    protected function tearDown(): void
    {
        unset($this->PlatformDetector);
        parent::tearDown();
    }

    /**
     * Test platform detection methods
     *
     * @return void
     */
    public function testPlatformDetection(): void
    {
        $osFamily = $this->PlatformDetector->getOSFamily();

        $expectedFamilies = ['Linux', 'Windows', 'Darwin', 'Unknown'];
        $this->assertContains($osFamily, $expectedFamilies, 'OS family should be one of the expected values');
    }

    /**
     * Test signal constants
     *
     * @return void
     */
    public function testSignalConstants(): void
    {
        $constants = $this->PlatformDetector->getSignalConstants();

        if ($this->PlatformDetector->isSignalHandlingAvailable()) {
            $this->assertNotEmpty($constants, 'Constants should not be empty when signal handling is available');
        }
    }
}
