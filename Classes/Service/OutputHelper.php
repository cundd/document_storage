<?php
declare(strict_types=1);

namespace Cundd\DocumentStorage\Service;

use Cundd\DocumentStorage\Domain\Model\Document;
use InvalidArgumentException;
use Symfony\Component\Console\Output\OutputInterface;
use UnexpectedValueException;
use function json_decode;
use function sprintf;

class OutputHelper implements OutputHelperInterface
{
    public function outputDocuments(
        OutputInterface $output,
        iterable $documents,
        bool $showBody = false,
        array $keyPaths = []
    ): void {
        foreach ($documents as $document) {
            $this->outputDocument($output, $document, $showBody, $keyPaths);
        }
    }

    public function outputDocument(
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
                $data = $this->resolveKeyPath($document, $keyPath);
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
    private function writeJsonData(OutputInterface $output, $data): void
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
    private function formatJsonData($data, bool $withColors = true)
    {
        return (new JsonFormatter())->formatJsonData($data, $withColors);
    }

    /**
     * @param Document $document
     * @param string   $keyPath
     * @return mixed|null
     */
    private function resolveKeyPath(Document $document, string $keyPath)
    {
        $asteriskCount = substr_count($keyPath, '*');
        if ($asteriskCount > 1) {
            throw new InvalidArgumentException('Maximum of one asterisk in key-path is supported');
        } elseif ($asteriskCount === 1) {

            list($lead, $tail) = explode('*', $keyPath, 2);
            $leadKeyPath = rtrim($lead, '.');
            $leadResult = $document->valueForKeyPath($leadKeyPath);

            if ($leadResult === null) {
                return null;
            }
            if (!is_iterable($leadResult)) {
                throw new UnexpectedValueException(sprintf('Data of lead key-path "%s" is not iterable', $leadKeyPath));
            }

            return $this->valueForKeyPath($leadResult, ltrim($tail, '.'));

        }

        return $document->valueForKeyPath($keyPath);
    }

    /**
     * @param iterable $leadResult
     * @param string   $keyPath
     * @return array
     */
    private function valueForKeyPath(iterable $leadResult, string $keyPath): array
    {
        $result = [];
        foreach ($leadResult as $item) {
            $result[] = array_reduce(
                explode('.', $keyPath),
                function ($carry, string $key) {
                    return is_array($carry) && isset($carry[$key]) ? $carry[$key] : null;
                },
                $item
            );
        }

        return $result;
    }
}
