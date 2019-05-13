<?php
declare(strict_types=1);

namespace Cundd\DocumentStorage\Command;

use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class GeneralCommandController extends AbstractCommandController
{
    protected static $defaultName = 'document-storage';

    protected function configure()
    {
        $help = 'Read Documents from a database or list all databases.';
        $this->setDescription('Show Documents and databases')
            ->setHelp($help)
            ->addArgument('database', InputArgument::OPTIONAL, 'Name of the database (omit to list all databases)')
            ->addArgument('id', InputArgument::OPTIONAL, 'ID of the Document to show (omit to show all)')
            ->addOption('count', 'c', InputOption::VALUE_NONE, 'Count Documents instead of displaying (only w/ [<id>])')
            ->addOption('short', 's', InputOption::VALUE_NONE, 'Show only the headers of Documents (only w/ [<id>])');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $database = $input->getArgument('database');
        $id = $input->getArgument('id');

        if ($database) {
            $command = $this->getApplication()->find('document-storage:read');
            $arguments = [
                'database' => $database,
                'id'       => $id,
            ];
        } else {
            $command = $this->getApplication()->find('document-storage:list');
            $arguments = [];
        }

        $subcommandInput = new ArrayInput($arguments);

        return $command->run($subcommandInput, $output);
    }
}
