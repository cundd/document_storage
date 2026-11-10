<?php

declare(strict_types=1);

namespace Cundd\DocumentStorage\Command\Database;

use Cundd\DocumentStorage\Command\AbstractCommand;
use Cundd\DocumentStorage\Domain\Model\Database;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

final class ListCommand extends AbstractCommand
{
    protected function configure(): void
    {
        $this->setDescription('List all databases')
            ->setHelp('Display a list of all databases.');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $databases = $this->databaseRepository->findAll();
        $longestName = array_reduce(
            $databases,
            function ($prev, Database $database) {
                if (strlen($database->name) > $prev) {
                    return strlen($database->name);
                } else {
                    return $prev;
                }
            },
            0
        );
        foreach ($databases as $database) {
            $output->writeln(
                '<info>'
                . 'Database: ' . str_pad($database->name, $longestName, ' ') . "\t"
                . 'Creation time: ' . ($database->creationTime?->format('r'))
                . '</info>'
            );
        }

        return self::SUCCESS;
    }
}
