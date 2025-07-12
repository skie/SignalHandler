<?php
declare(strict_types=1);

namespace SignalHandler\Test\TestCase\Integration;

use Cake\TestSuite\TestCase;
use SignalHandler\Command\SignalableCommandInterface;
use SignalHandler\Listener\SignalEventListener;
use SignalHandler\Service\SignalService;
use SignalHandler\Signal\Signal;
use stdClass;

/**
 * SignalHandler Integration Test Case
 *
 * Tests the integration of signal handling with CakePHP's command execution flow.
 */
class SignalHandlerIntegrationTest extends TestCase
{
    /**
     * setUp method
     *
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();
    }

    /**
     * tearDown method
     *
     * @return void
     */
    protected function tearDown(): void
    {
        parent::tearDown();
    }

    /**
     * Test that SignalEventListener is created correctly
     *
     * @return void
     */
    public function testSignalEventListenerCreation(): void
    {
        $eventListener = new SignalEventListener();

        $this->assertInstanceOf(SignalEventListener::class, $eventListener);
        $this->assertEquals([
            'Command.beforeExecute' => 'beforeCommandExecute',
            'Command.afterExecute' => 'afterCommandExecute',
        ], $eventListener->implementedEvents());
    }

    /**
     * Test that SignalService is created correctly
     *
     * @return void
     */
    public function testSignalServiceCreation(): void
    {
        $signalService = new SignalService();

        $this->assertInstanceOf(SignalService::class, $signalService);
        $this->assertTrue($signalService->isEnabled());
    }

    /**
     * Test that SignalService correctly identifies signalable commands
     *
     * @return void
     */
    public function testSignalServiceCommandDetection(): void
    {
        $signalService = new SignalService();

        /** @var \PHPUnit\Framework\MockObject\MockObject&SignalableCommandInterface $signalableCommand */
        $signalableCommand = $this->createMock(SignalableCommandInterface::class);
        $regularCommand = new stdClass();

        $this->assertTrue($signalService->isSignalableCommand($signalableCommand));
        $this->assertFalse($signalService->isSignalableCommand($regularCommand));
    }

    /**
     * Test that SignalService can get subscribed signals from commands
     *
     * @return void
     */
    public function testSignalServiceGetSubscribedSignals(): void
    {
        $signalService = new SignalService();
        /** @var \PHPUnit\Framework\MockObject\MockObject&SignalableCommandInterface $command */
        $command = $this->createMock(SignalableCommandInterface::class);
        $command->method('getSubscribedSignals')
            ->willReturn([Signal::SIGINT, Signal::SIGTERM]);

        $signals = $signalService->getSubscribedSignals($command);

        $this->assertEquals([Signal::SIGINT, Signal::SIGTERM], $signals);
    }

    /**
     * Test that SignalService uses callback methods for signal handling
     *
     * @return void
     */
    public function testSignalServiceUsesCallbackMethods(): void
    {
        $signalService = new SignalService();
        /** @var \PHPUnit\Framework\MockObject\MockObject&SignalableCommandInterface $command */
        $command = $this->createMock(SignalableCommandInterface::class);

        $command->method('onInterrupt')
            ->willReturn(false);

        $result = $signalService->handleSignal($command, Signal::SIGINT);

        $this->assertFalse($result);
    }

    /**
     * Test that SignalService uses correct callback for SIGTERM
     *
     * @return void
     */
    public function testSignalServiceUsesTerminateCallback(): void
    {
        $signalService = new SignalService();
        /** @var \PHPUnit\Framework\MockObject\MockObject&SignalableCommandInterface $command */
        $command = $this->createMock(SignalableCommandInterface::class);

        $command->method('onTerminateSignal')
            ->willReturn(0);

        $result = $signalService->handleSignal($command, Signal::SIGTERM);

        $this->assertEquals(0, $result);
    }

    /**
     * Test that SignalService uses general signal callback for unknown signals
     *
     * @return void
     */
    public function testSignalServiceUsesGeneralSignalCallback(): void
    {
        $signalService = new SignalService();
        /** @var \PHPUnit\Framework\MockObject\MockObject&SignalableCommandInterface $command */
        $command = $this->createMock(SignalableCommandInterface::class);

        $command->method('onSignal')
            ->with(999)
            ->willReturn(false);

        $result = $signalService->handleSignal($command, 999);

        $this->assertFalse($result);
    }

    /**
     * Test that SignalEventListener ignores non-signalable commands
     *
     * @return void
     */
    public function testSignalEventListenerIgnoresRegularCommands(): void
    {
        $eventListener = new SignalEventListener();
        $regularCommand = new stdClass();

        /** @var \PHPUnit\Framework\MockObject\MockObject&\Cake\Event\EventInterface<\Cake\Command\Command> $event */
        $event = $this->createMock('Cake\Event\EventInterface');
        $event->method('getSubject')
            ->willReturn($regularCommand);

        $eventListener->beforeCommandExecute($event);

        $this->assertNull($eventListener->getCurrentCommand());
        $this->assertInstanceOf('SignalHandler\Signal\SignalRegistry', $eventListener->getSignalRegistry());
    }

    /**
     * Test that SignalEventListener handles signalable commands correctly
     *
     * @return void
     */
    public function testSignalEventListenerHandlesSignalableCommands(): void
    {
        $eventListener = new SignalEventListener();
        /** @var \PHPUnit\Framework\MockObject\MockObject&SignalableCommandInterface $command */
        $command = $this->createMock(SignalableCommandInterface::class);
        $command->method('getSubscribedSignals')
            ->willReturn([Signal::SIGINT, Signal::SIGTERM]);

        /** @var \PHPUnit\Framework\MockObject\MockObject&\Cake\Event\EventInterface<\Cake\Command\Command> $event */
        $event = $this->createMock('Cake\Event\EventInterface');
        $event->method('getSubject')
            ->willReturn($command);

        $eventListener->beforeCommandExecute($event);

        $this->assertSame($command, $eventListener->getCurrentCommand());
        $this->assertInstanceOf('SignalHandler\Signal\SignalRegistry', $eventListener->getSignalRegistry());
    }

    /**
     * Test that SignalEventListener cleans up after command execution
     *
     * @return void
     */
    public function testSignalEventListenerCleanup(): void
    {
        $eventListener = new SignalEventListener();
        /** @var \PHPUnit\Framework\MockObject\MockObject&SignalableCommandInterface $command */
        $command = $this->createMock(SignalableCommandInterface::class);
        $command->method('getSubscribedSignals')
            ->willReturn([Signal::SIGINT, Signal::SIGTERM]);

        /** @var \PHPUnit\Framework\MockObject\MockObject&\Cake\Event\EventInterface<\Cake\Command\Command> $event */
        $event = $this->createMock('Cake\Event\EventInterface');
        $event->method('getSubject')
            ->willReturn($command);

        $eventListener->beforeCommandExecute($event);

        $this->assertNotNull($eventListener->getCurrentCommand());
        $this->assertNotNull($eventListener->getSignalRegistry());

        $eventListener->afterCommandExecute($event);

        $this->assertNull($eventListener->getCurrentCommand());
        $this->assertNull($eventListener->getSignalRegistry());
    }

    /**
     * Test that SignalEventListener handles commands with no subscribed signals
     *
     * @return void
     */
    public function testSignalEventListenerHandlesEmptySignals(): void
    {
        $eventListener = new SignalEventListener();
        /** @var \PHPUnit\Framework\MockObject\MockObject&SignalableCommandInterface $command */
        $command = $this->createMock(SignalableCommandInterface::class);
        $command->method('getSubscribedSignals')
            ->willReturn([]);

        /** @var \PHPUnit\Framework\MockObject\MockObject&\Cake\Event\EventInterface<\Cake\Command\Command> $event */
        $event = $this->createMock('Cake\Event\EventInterface');
        $event->method('getSubject')
            ->willReturn($command);

        $eventListener->beforeCommandExecute($event);

        $this->assertSame($command, $eventListener->getCurrentCommand());
        $this->assertInstanceOf('SignalHandler\Signal\SignalRegistry', $eventListener->getSignalRegistry());
    }

    /**
     * Test that SignalService handles termination correctly
     *
     * @return void
     */
    public function testSignalServiceHandlesTermination(): void
    {
        $signalService = new SignalService();
        /** @var \PHPUnit\Framework\MockObject\MockObject&SignalableCommandInterface $command */
        $command = $this->createMock(SignalableCommandInterface::class);

        $command->expects($this->once())
            ->method('onTerminate')
            ->with(0, Signal::SIGINT);

        $signalService->handleTermination($command, 0, Signal::SIGINT);
    }
}
