<?php
declare(strict_types=1);

namespace Cundd\DocumentStorage\Service;

use ArrayAccess;
use Cundd\DocumentStorage\Domain\Model\Document;
use InvalidArgumentException;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use UnexpectedValueException;
use function is_array;
use function is_callable;
use function is_object;
use function json_decode;
use function method_exists;
use function sprintf;
use function ucfirst;

class OutputHelper implements OutputHelperInterface
{
    public function outputDocuments(
        InputInterface $input,
        OutputInterface $output,
        iterable $documents,
        bool $showBody = false,
        array $keyPaths = []
    ): void {
        foreach ($documents as $document) {
            $this->outputDocument($input, $output, $document, $showBody, $keyPaths);
        }
    }

    public function outputDocument(
        InputInterface $input,
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
                $this->writeOutput($input, $output, $data);
            }
        } elseif ($showBody) {
            $data = $document->getDataProtected() ? json_decode((string)$document->getDataProtected(), true) : [];
            $this->writeOutput($input, $output, $data);
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

            return $this->valueForAsteriskKeyPath($leadResult, ltrim($tail, '.'));

        }

        return $this->valueForKeyPath($document, $keyPath);
    }

    /**
     * @param iterable $leadResult
     * @param string   $keyPath
     * @return array
     */
    private function valueForAsteriskKeyPath(iterable $leadResult, string $keyPath): array
    {
        $result = [];
        foreach ($leadResult as $item) {
            $result[] = $this->valueForKeyPath($item, $keyPath);
        }

        return $result;
    }

    /**
     * @param mixed  $input
     * @param string $keyPath
     * @return array
     */
    private function valueForKeyPath($input, string $keyPath)
    {
        return array_reduce(
            explode('.', $keyPath),
            function ($carry, string $key) {
                if (is_array($carry) && isset($carry[$key])) {
                    return $carry[$key];
                }

                if (is_object($carry)) {
                    $getterName = 'get' . ucfirst($key);
                    if (is_callable([$carry, $getterName]) && method_exists($carry, $getterName)) {
                        return $carry->$getterName();
                    }

                    if ($carry instanceof ArrayAccess) {
                        return $carry->offsetGet($key);
                    }
                }

                return null;
            },
            $input
        );
    }

    /**
     * @param InputInterface  $input
     * @param OutputInterface $output
     * @param                 $data
     */
    protected function writeOutput(InputInterface $input, OutputInterface $output, $data): void
    {
        $format = $input->hasArgument('format') ? $input->getArgument('format') : 'json';
        switch ($format) {
            case 'json':
                $this->writeJsonData($output, $data);

                return;
            default:
                throw new UnexpectedValueException(sprintf('Format "%s" is not implemented', $format));
        }
    }
}
