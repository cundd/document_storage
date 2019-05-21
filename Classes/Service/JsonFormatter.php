<?php
declare(strict_types=1);

namespace Cundd\DocumentStorage\Service;

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
     * @param bool  $withColors
     * @return string
     */
    public function formatJsonData($data, bool $withColors = true)
    {
        $output = json_encode($data, JSON_PRETTY_PRINT);
        if (!$withColors) {
            return $output;
        }

        $output = preg_replace('!"([^"]+)":!', '<fg=yellow>"$1"</>:', $output);
        $output = preg_replace('!"([^"]*)"(,?)$!m', '<fg=green>"$1"</>$2', $output);
        $output = preg_replace('!(-?\d+\.\d+)(,?)$!m', '<fg=magenta>$1</>$2', $output);
        $output = preg_replace('!(-?\d+)(,?)$!m', '<fg=red>$1</>$2', $output);
        $output = str_replace(': null', ': <fg=blue>: null</>', $output);

        return $output;
    }
}
