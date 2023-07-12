<?php

declare(strict_types=1);

namespace Cundd\DocumentStorage\Service;

use Cundd\DocumentStorage\Command\Output\NotFoundException;
use function json_encode;
use function preg_replace;
use function str_replace;
use const JSON_PRETTY_PRINT;

class JsonFormatter
{
    /**
     * Return a formatted json-encoded version of the given data
     *
     * @param mixed $data The data to format
     * @param bool $withColors
     * @return string
     */
    public function formatJsonData(mixed $data, bool $withColors = true): string
    {
        $output = json_encode($data, JSON_PRETTY_PRINT);
        $notFoundSymbolString = '"' . NotFoundException::getSymbol() . '"';
        if (!$withColors) {
            return str_replace($notFoundSymbolString, '< not found >', $output);
        }

        $output = preg_replace('!"([^"]+)":!', '<fg=yellow>"$1"</>:', $output);
        $output = preg_replace('!"([^"]*)"(,?)$!m', '<fg=green>"$1"</>$2', $output);
        $output = preg_replace('!(-?\d+\.\d+)(,?)$!m', '<fg=magenta>$1</>$2', $output);
        $output = preg_replace('!(-?\d+)(,?)$!m', '<fg=red>$1</>$2', $output);
        $output = preg_replace('!\bnull\b!', '<fg=blue>null</>', $output);
        $output = str_replace($notFoundSymbolString, '<fg=blue>< not found ></>', $output);

        return $output;
    }
}
