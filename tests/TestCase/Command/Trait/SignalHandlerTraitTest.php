<?php
declare(strict_types=1);

namespace SignalHandler\Test\TestCase\Command\Trait;

use Cake\TestSuite\TestCase;
use SignalHandler\Signal\Signal;

/**
 * SignalHandlerTrait Test Case
 */
class SignalHandlerTraitTest extends TestCase
{
    /**
     * Test subject
     *
     * @var TestCommand
     */
    protected TestCommand $TestCommand;

    /**
     * setUp method
     *
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();
        $this->TestCommand = new TestCommand();
    }

    /**
     * tearDown method
     *
     * @return void
     */
    protected function tearDown(): void
    {
        unset($this->TestCommand);
        parent::tearDown();
    }

    /**
     * Test getSubscribedSignals method
     *
     * @return void
     */
    public function testGetSubscribedSignals(): void
    {
        $signals = $this->TestCommand->testGetSubscribedSignals();
        $this->assertContains(Signal::SIGINT, $signals);
        $this->assertContains(Signal::SIGTERM, $signals);
    }

    /**
     * Test handleSignal method
     *
     * @return void
     */
    public function testHandleSignal(): void
    {
        $result = $this->TestCommand->testHandleSignal(Signal::SIGINT);
        $this->assertTrue($result === false || $result >= 0);

        $result = $this->TestCommand->testHandleSignal(Signal::SIGTERM);
        $this->assertTrue($result === false || $result >= 0);

        $result = $this->TestCommand->testHandleSignal(Signal::SIGUSR1);
        $this->assertTrue($result === false || $result >= 0);
    }
}
