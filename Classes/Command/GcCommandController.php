<?php
declare(strict_types=1);

namespace Cundd\DocumentStorage\Command;

use Cundd\DocumentStorage\Service\GcService;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Utility\MathUtility;

class GcCommandController extends AbstractCommandController
{
    protected function configure()
    {
        $help = 'Permanently delete all Documents marked as "deleted" from the database.';
        $this->setDescription('Permanently remove deleted Documents')
            ->setHelp($help)
            ->addArgument('database', InputArgument::OPTIONAL, 'Name of the database')
            ->addOption('min-age', 'a', InputOption::VALUE_REQUIRED, 'Minimum age in days of the Documents to delete');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $db = $input->getArgument('database');
        $minAge = $this->getMinAge($input);

        $gcService = new GcService(GeneralUtility::makeInstance(ConnectionPool::class));

        $count = $gcService->countDeletedDocuments($minAge, $db);
        $result = $gcService->removeDeletedDocuments($minAge, $db);
        if ($result) {
            $output->writeln("<info>Permanently deleted $count Documents</info>");
        } else {
            $output->writeln("<error>Could not permanently deleted $count Documents</error>");
        }
    }

    /**
     * @param InputInterface $input
     * @return bool|int|string|string[]|null
     */
    protected function getMinAge(InputInterface $input)
    {
        $minAge = $input->getOption('min-age');
        if (MathUtility::canBeInterpretedAsInteger($minAge)) {
            return (int)$minAge * 60 * 60 * 24;
        } else {
            return 0;
        }
    }
}
