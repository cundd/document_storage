<?php
declare(strict_types=1);

namespace Cundd\DocumentStorage\Tests\Fixture;

use function preg_replace;

class ClassNamingUtility
{
    public static function translateRepositoryNameToModelName($repositoryName)
    {
        return preg_replace(
            ['/\\\\Domain\\\\Repository/', '/Repository$/'],
            ['\\Domain\\Model', ''],
            $repositoryName
        );
    }
}
