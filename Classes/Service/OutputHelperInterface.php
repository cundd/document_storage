<?php
declare(strict_types=1);

namespace Cundd\DocumentStorage\Service;

use Cundd\DocumentStorage\Domain\Model\Document;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

interface OutputHelperInterface
{
    /**
     * Display information about the given Documents
     *
     * @param InputInterface  $input
     * @param OutputInterface $output
     * @param iterable        $documents
     * @param bool            $showBody
     * @param string[]        $keyPaths
     */
    public function outputDocuments(
        InputInterface $input,
        OutputInterface $output,
        iterable $documents,
        bool $showBody = false,
        array $keyPaths = []
    ): void;

    /**
     * Display information about the given Document
     *
     * @param InputInterface  $input
     * @param OutputInterface $output
     * @param Document        $document
     * @param bool            $showBody
     * @param string[]        $keyPaths
     */
    public function outputDocument(
        InputInterface $input,
        OutputInterface $output,
        Document $document,
        bool $showBody = false,
        array $keyPaths = []
    ): void;
}