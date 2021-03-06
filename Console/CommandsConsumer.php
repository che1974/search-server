<?php

/*
 * This file is part of the Apisearch Server
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * Feel free to edit as you please, and have fun.
 *
 * @author Marc Morera <yuhu@mmoreram.com>
 */

declare(strict_types=1);

namespace Apisearch\Server\Console;

use Apisearch\Server\Domain\AsynchronousableCommand;
use Exception;
use League\Tactician\CommandBus;
use RSQueue\Command\ConsumerCommand;
use RSQueue\Services\Consumer;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class CommandsConsumer.
 */
class CommandsConsumer extends ConsumerCommand
{
    /**
     * @var CommandBus
     *
     * Command bus
     */
    private $commandBus;

    /**
     * ConsumerCommand constructor.
     *
     * @param Consumer   $consumer
     * @param CommandBus $commandBus
     */
    public function __construct(
        Consumer $consumer,
        CommandBus $commandBus
    ) {
        parent::__construct($consumer);

        $this->commandBus = $commandBus;
    }

    /**
     * Definition method.
     *
     * All RSQueue commands must implements its own define() method
     * This method will subscribe command to desired queues
     * with their respective methods
     */
    public function define()
    {
        $this->addQueue('apisearch:server:commands', 'handleCommand');
    }

    /**
     * Persist domain event.
     *
     * @param InputInterface  $input
     * @param OutputInterface $output
     * @param array           $data
     */
    protected function handleCommand(
        InputInterface $input,
        OutputInterface $output,
        array $data
    ) {
        $class = 'Apisearch\Server\Domain\Command\\'.$data['class'];
        if (
            !class_exists($class) ||
            !in_array(AsynchronousableCommand::class, class_implements($class))
        ) {
            return;
        }

        $success = true;
        $message = '';
        $command = $data['class'];
        $from = microtime(true);
        try {
            $this
                ->commandBus
                ->handle($class::fromArray($data));
        } catch (Exception $e) {
            // Silent pass
            $success = false;
            $message = $e->getMessage();
        }
        $to = microtime(true);
        $elapsedTime = (int) (($to - $from) * 1000);
        if (0 === $elapsedTime) {
            $elapsedTime = '<1';
        }

        echo true === $success
            ? "\033[01;32mOk  \033[0m"
            : "\033[01;31mFail\033[0m";
        echo " $command ";
        echo "(\e[00;37m".$elapsedTime.' ms, '.((int) (memory_get_usage() / 1000000))." MB\e[0m)";
        if (false === $success) {
            echo " - \e[00;37m".$message."\e[0m";
        }
        echo PHP_EOL;
    }
}
