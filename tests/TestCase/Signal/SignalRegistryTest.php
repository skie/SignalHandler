<?php
declare(strict_types=1);

namespace SignalHandler\Test\TestCase\Signal;

use Cake\TestSuite\TestCase;
use SignalHandler\Signal\PlatformDetector;
use SignalHandler\Signal\SignalRegistry;

/**
 * SignalRegistry Test Case
 */
class SignalRegistryTest extends TestCase
{
    /**
     * Test subject
     *
     * @var \SignalHandler\Signal\SignalRegistry
     */
    protected SignalRegistry $SignalRegistry;

    /**
     * setUp method
     *
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();
        $this->SignalRegistry = new SignalRegistry();
    }

    /**
     * tearDown method
     *
     * @return void
     */
    protected function tearDown(): void
    {
        unset($this->SignalRegistry);
        parent::tearDown();
    }

    /**
     * Test constructor with platform detector
     *
     * @return void
     */
    public function testConstructorWithPlatformDetector(): void
    {
        $platformDetector = new PlatformDetector();
        $registry = new SignalRegistry($platformDetector);

        $this->assertInstanceOf(SignalRegistry::class, $registry);
    }
}
