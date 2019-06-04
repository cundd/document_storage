<?php
declare(strict_types=1);

namespace Cundd\DocumentStorage\Command;

use Cundd\DocumentStorage\Domain\Model\Document;
use Cundd\DocumentStorage\Service\OutputHelper;
use Cundd\DocumentStorage\Service\OutputHelperInterface;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

trait DocumentOutputTrait
{
    protected function getOutputHelper(): OutputHelperInterface
    {
        return new OutputHelper();
    }

    /**
     * Displays information about the given Documents
     *
     * @param InputInterface  $input
     * @param OutputInterface $output
     * @param iterable        $documents
     * @param bool            $showBody
     * @param array           $keyPaths
     */
    protected function outputDocuments(
        InputInterface $input,
        OutputInterface $output,
        iterable $documents,
        bool $showBody = false,
        array $keyPaths = []
    ): void {
        $this->getOutputHelper()->outputDocuments($input, $output, $documents, $showBody, $keyPaths);
    }

    /**
     * Displays information about the given Document
     *
     * @param InputInterface  $input
     * @param OutputInterface $output
     * @param Document        $document
     * @param bool            $showBody
     * @param array           $keyPaths
     */
    protected function outputDocument(
        InputInterface $input,
        OutputInterface $output,
        Document $document,
        bool $showBody = false,
        array $keyPaths = []
    ): void {
        $this->getOutputHelper()->outputDocument($input, $output, $document, $showBody, $keyPaths);;
    }
}
