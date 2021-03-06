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

use Apisearch\Repository\RepositoryReference;
use Apisearch\Server\Domain\Command\DeleteToken;
use Apisearch\Token\TokenUUID;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class DeleteTokenCommand.
 */
class DeleteTokenCommand extends CommandWithBusAndGodToken
{
    /**
     * Configures the current command.
     */
    protected function configure()
    {
        $this
            ->setDescription('Delete a token')
            ->addArgument(
                'app-id',
                InputArgument::REQUIRED,
                'App id'
            )
            ->addArgument(
                'uuid',
                InputArgument::REQUIRED,
                'UUID'
            );
    }

    /**
     * Dispatch domain event.
     *
     * @return string
     */
    protected function getHeader(): string
    {
        return 'Delete token';
    }

    /**
     * Dispatch domain event.
     *
     * @param InputInterface  $input
     * @param OutputInterface $output
     *
     * @return mixed
     */
    protected function dispatchDomainEvent(InputInterface $input, OutputInterface $output)
    {
        $this
            ->commandBus
            ->handle(new DeleteToken(
                RepositoryReference::create(
                    $input->getArgument('app-id'),
                    '~~~'
                ),
                $this->createGodToken($input->getArgument('app-id')),
                TokenUUID::createById($input->getArgument('uuid'))
            ));
    }

    /**
     * Get success message.
     *
     * @param InputInterface $input
     * @param mixed          $result
     *
     * @return string
     */
    protected function getSuccessMessage(
        InputInterface $input,
        $result
    ): string {
        return sprintf(
            'Token with UUID <%s> deleted properly',
            $input->getArgument('uuid')
        );
    }
}
