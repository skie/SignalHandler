<?php
declare(strict_types=1);

namespace SignalHandler\Test\TestCase\Service;

use Cake\TestSuite\TestCase;
use SignalHandler\Command\SignalableCommandInterface;
use SignalHandler\Listener\SignalEventListener;
use SignalHandler\Service\SignalService;
use SignalHandler\Signal\Signal;
use SignalHandler\Signal\SignalRegistry;
use stdClass;

/**
 * SignalService Test Case
 */
class SignalServiceTest extends TestCase
{
    /**
     * Test subject
     *
     * @var \SignalHandler\Service\SignalService
     */
    protected SignalService $SignalService;

    /**
     * setUp method
     *
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();
        $this->SignalService = new SignalService();
    }

    /**
     * tearDown method
     *
     * @return void
     */
    protected function tearDown(): void
    {
        unset($this->SignalService);
        parent::tearDown();
    }

    /**
     * Test isSupported method
     *
     * @return void
     */
    public function testIsSupported(): void
    {
        $result = $this->SignalService->isSupported();

        $result2 = $this->SignalService->isSupported();
        $this->assertEquals($result, $result2, 'Support status should be consistent');
    }

    /**
     * Test isEnabled method
     *
     * @return void
     */
    public function testIsEnabled(): void
    {
        $result = $this->SignalService->isEnabled();

        $supported = $this->SignalService->isSupported();
        $this->assertEquals($supported, $result, 'Enabled should match supported status');
    }

    /**
     * Test isSignalableCommand method
     *
     * @return void
     */
    public function testIsSignalableCommand(): void
    {
        /** @var \PHPUnit\Framework\MockObject\MockObject&SignalableCommandInterface $command */
        $command = $this->createMock(SignalableCommandInterface::class);
        $result = $this->SignalService->isSignalableCommand($command);
        $this->assertTrue($result);

        $regularObject = new stdClass();
        $result = $this->SignalService->isSignalableCommand($regularObject);
        $this->assertFalse($result);
    }

    /**
     * Test getSubscribedSignals method
     *
     * @return void
     */
    public function testGetSubscribedSignals(): void
    {
        /** @var \PHPUnit\Framework\MockObject\MockObject&SignalableCommandInterface $command */
        $command = $this->createMock(SignalableCommandInterface::class);
        $command->method('getSubscribedSignals')
            ->willReturn([Signal::SIGINT, Signal::SIGTERM]);

        $result = $this->SignalService->getSubscribedSignals($command);
        $this->assertContains(Signal::SIGINT, $result);
        $this->assertContains(Signal::SIGTERM, $result);
    }

    /**
     * Test registerSignalHandlers method
     *
     * @return void
     */
    public function testRegisterSignalHandlers(): void
    {
        /** @var \PHPUnit\Framework\MockObject\MockObject&SignalableCommandInterface $command */
        $command = $this->createMock(SignalableCommandInterface::class);
        $command->method('getSubscribedSignals')
            ->willReturn([Signal::SIGINT, Signal::SIGTERM]);

        $result = $this->SignalService->registerSignalHandlers($command);

        if ($this->SignalService->isEnabled()) {
            $this->assertInstanceOf(SignalRegistry::class, $result);
        } else {
            $this->assertNull($result);
        }
    }

    /**
     * Test registerSignalHandlers with empty signals
     *
     * @return void
     */
    public function testRegisterSignalHandlersWithEmptySignals(): void
    {
        /** @var \PHPUnit\Framework\MockObject\MockObject&SignalableCommandInterface $command */
        $command = $this->createMock(SignalableCommandInterface::class);
        $command->method('getSubscribedSignals')
            ->willReturn([]);

        $result = $this->SignalService->registerSignalHandlers($command);
        $this->assertNull($result);
    }

    /**
     * Test unregisterSignalHandlers method
     *
     * @return void
     */
    public function testUnregisterSignalHandlers(): void
    {
        /** @var \PHPUnit\Framework\MockObject\MockObject&SignalableCommandInterface $command */
        $command = $this->createMock(SignalableCommandInterface::class);
        $command->method('getSubscribedSignals')
            ->willReturn([Signal::SIGINT]);

        // First register handlers
        $registry = $this->SignalService->registerSignalHandlers($command);

        if ($registry !== null) {
            // Then unregister
            $this->SignalService->unregisterSignalHandlers($command);

            // Verify registry is cleaned up
            $registryAfter = $this->SignalService->getSignalRegistry();
            $this->assertNull($registryAfter, 'Registry should be null after unregister');
        }
    }

    /**
     * Test handleSignal method
     *
     * @return void
     */
    public function testHandleSignal(): void
    {
        /** @var \PHPUnit\Framework\MockObject\MockObject&SignalableCommandInterface $command */
        $command = $this->createMock(SignalableCommandInterface::class);
        $command->method('onInterrupt')->willReturn(0);
        $command->method('onTerminateSignal')->willReturn(0);
        $command->method('onUserSignal1')->willReturn(false);
        $command->method('onUserSignal2')->willReturn(false);
        $command->method('onSignal')->willReturn(false);

        $result = $this->SignalService->handleSignal($command, Signal::SIGINT);
        $this->assertEquals(0, $result, 'SIGINT should return 0');

        $result = $this->SignalService->handleSignal($command, Signal::SIGTERM);
        $this->assertEquals(0, $result, 'SIGTERM should return 0');

        $result = $this->SignalService->handleSignal($command, Signal::SIGUSR1);
        $this->assertFalse($result, 'SIGUSR1 should return false');
    }

    /**
     * Test handleTermination method
     *
     * @return void
     */
    public function testHandleTermination(): void
    {
        /** @var \PHPUnit\Framework\MockObject\MockObject&SignalableCommandInterface $command */
        $command = $this->createMock(SignalableCommandInterface::class);
        $command->expects($this->once())
            ->method('onTerminate')
            ->with(0, Signal::SIGINT);

        $this->SignalService->handleTermination($command, 0, Signal::SIGINT);
    }

    /**
     * Test getSignalRegistry method
     *
     * @return void
     */
    public function testGetSignalRegistry(): void
    {
        $registry = $this->SignalService->getSignalRegistry();
        $this->assertInstanceOf(SignalRegistry::class, $registry);
    }

    /**
     * Test getEventListener method
     *
     * @return void
     */
    public function testGetEventListener(): void
    {
        $listener = $this->SignalService->getEventListener();
        $this->assertInstanceOf(SignalEventListener::class, $listener);
    }
}
