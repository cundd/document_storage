<?php
declare(strict_types=1);

namespace Cundd\DocumentStorage\Command;

use Cundd\DocumentStorage\Domain\Model\Document;
use Cundd\DocumentStorage\Service\JsonFormatter;
use Symfony\Component\Console\Output\OutputInterface;
use function json_decode;
use function sprintf;

trait DocumentOutputTrait
{
    /**
     * Displays information about the given Documents
     *
     * @param OutputInterface $output
     * @param iterable        $documents
     * @param bool            $showBody
     * @param array           $keyPaths
     */
    protected function outputDocuments(
        OutputInterface $output,
        iterable $documents,
        bool $showBody = false,
        array $keyPaths = []
    ): void {
        foreach ($documents as $document) {
            $this->outputDocument($output, $document, $showBody, $keyPaths);
        }
    }

    /**
     * Displays information about the given Document
     *
     * @param OutputInterface $output
     * @param Document        $document
     * @param bool            $showBody
     * @param array           $keyPaths
     */
    protected function outputDocument(
        OutputInterface $output,
        Document $document,
        bool $showBody = false,
        array $keyPaths = []
    ): void {
        $output->writeln(
            '<info>'
            . 'Database: ' . $document->getDb() . ' '
            . 'ID: ' . ($document->getId() ? $document->getId() : '(Missing ID)') . ' '
            . '</info>'
        );

        if ($keyPaths) {
            foreach ($keyPaths as $keyPath) {
                $output->writeln(sprintf('Data for key-path <options=bold>%s</>:', $keyPath));
                $data = $document->valueForKeyPath($keyPath);
                $this->writeJsonData($output, $data);
            }
        } elseif ($showBody) {
            $data = $document->getDataProtected() ? json_decode((string)$document->getDataProtected(), true) : [];
            $this->writeJsonData($output, $data);
        }
    }

    /**
     * @param OutputInterface $output
     * @param array|null      $data
     */
    protected function writeJsonData(OutputInterface $output, $data): void
    {
        $output->writeln($this->formatJsonData($data));
        $output->writeln('');
    }


    /**
     * Returns a formatted json-encoded version of the given data
     *
     * @param mixed $data The data to format
     * @param bool  $withColors
     * @return string
     */
    protected function formatJsonData($data, bool $withColors = true)
    {
        return (new JsonFormatter())->formatJsonData($data, $withColors);
    }
}
