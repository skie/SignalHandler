<?php
declare(strict_types=1);

namespace TestApp\Command;

use Cake\Console\Arguments;
use Cake\Console\BaseCommand;
use Cake\Console\CommandInterface;
use Cake\Console\ConsoleIo;
use SignalHandler\Command\SignalableCommandInterface;
use SignalHandler\Command\Trait\SignalHandlerTrait;
use SignalHandler\Signal\Signal;

class ExampleSignalCommand extends BaseCommand implements SignalableCommandInterface
{
    use SignalHandlerTrait;

    public function execute(Arguments $args, ConsoleIo $io): ?int
    {
        $io->out('ExampleSignalCommand running. Press Ctrl+C to interrupt.');
        $i = 0;
        while ($i < 3) {
            usleep(100);
            $io->out('Tick ' . ++$i);
        }

        return CommandInterface::CODE_SUCCESS;
    }

    public function getSubscribedSignals(): array
    {
        return [Signal::SIGINT];
    }

    public function onInterrupt(): int|false
    {
        return CommandInterface::CODE_SUCCESS;
    }
}
