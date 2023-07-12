<?php

declare(strict_types=1);

namespace Cundd\DocumentStorage\Service;

use ArrayAccess;
use Cundd\DocumentStorage\Command\Output\NotFoundException;
use Cundd\DocumentStorage\Domain\Model\Document;
use InvalidArgumentException;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use UnexpectedValueException;

use function array_push;
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

    private function writeJsonData(OutputInterface $output, mixed $data): void
    {
        $output->writeln($this->formatJsonData($data));
        $output->writeln('');
    }

    /**
     * Return a formatted json-encoded version of the given data
     *
     * @param mixed $data The data to format
     * @param bool  $withColors
     * @return string
     */
    private function formatJsonData(mixed $data, bool $withColors = true): string
    {
        return (new JsonFormatter())->formatJsonData($data, $withColors);
    }

    private function resolveKeyPath(Document $document, string $keyPath): ?array
    {
        $asteriskCount = substr_count($keyPath, '*');
        if ($asteriskCount === 0) {
            return $this->valueForKeyPath($document, $keyPath);
        }

        if ($asteriskCount === 1) {
            [$lead, $tail] = explode('*', $keyPath, 2);
            $leadKeyPath = rtrim($lead, '.');
            $leadResult = $document->valueForKeyPath($leadKeyPath, NotFoundException::instance());

            if ($leadResult === null) {
                return null;
            }
            if (!is_iterable($leadResult)) {
                throw new UnexpectedValueException(sprintf('Data of lead key-path "%s" is not iterable', $leadKeyPath));
            }

            return $this->valueForAsteriskKeyPath($leadResult, ltrim($tail, '.'));
        }

        throw new InvalidArgumentException('Maximum of one asterisk in key-path is supported');
    }

    /**
     * @param iterable $leadResult
     * @param string   $keyPath
     * @return array
     */
    private function valueForAsteriskKeyPath(iterable $leadResult, string $keyPath): array
    {
        if ('' === $keyPath) {
            $container = [];
            array_push($container, ...$leadResult);

            return $container;
        }

        $result = [];
        foreach ($leadResult as $item) {
            if ($keyPath) {
                $result[] = $this->valueForKeyPath($item, $keyPath, NotFoundException::instance());
            }
        }

        return $result;
    }

    /**
     * @param mixed  $input
     * @param string $keyPath
     * @param null   $default
     * @return mixed
     */
    private function valueForKeyPath(mixed $input, string $keyPath, $default = null): mixed
    {
        return array_reduce(
            explode('.', $keyPath),
            function ($carry, string $key) use ($default) {
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

                return $default;
            },
            $input
        );
    }

    protected function writeOutput(InputInterface $input, OutputInterface $output, mixed $data): void
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
