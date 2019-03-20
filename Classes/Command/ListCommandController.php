<?php
declare(strict_types=1);

namespace Cundd\DocumentStorage\Command;

use Cundd\DocumentStorage\Domain\Model\Database;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ListCommandController extends AbstractCommandController
{
    protected static $defaultName = 'document-storage:list';

    /**
     * Configure the command by defining the name, options and arguments
     */
    protected function configure()
    {
        $help = 'List all databases.' . LF . 'If you want to get more detailed information, use the --verbose option.';
        $this->setDescription('List all databases')
            ->setHelp($help);
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $databases = $this->getDatabaseRepository()->findAll();
        $longestName = array_reduce(
            $databases,
            function ($prev, Database $database) {
                if (strlen($database->getName()) > $prev) {
                    return strlen($database->getName());
                } else {
                    return $prev;
                }
            },
            0
        );
        foreach ($databases as $database) {
            $output->writeln(
                '<info>'
                . 'Database: ' . str_pad($database->getName(), $longestName, ' ') . "\t"
                . 'Creation time: ' . ($database->getCreationTime()->format('r'))
                . '</info>'
            );
        }
    }

}