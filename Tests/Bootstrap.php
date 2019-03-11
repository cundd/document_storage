<?php
declare(strict_types=1);

namespace Cundd\DocumentStorage\Tests;

use Cundd\DocumentStorage\Tests\Fixture\AbstractEntity;

class Bootstrap
{
    public function run()
    {
        class_alias(AbstractEntity::class, '\TYPO3\CMS\Extbase\DomainObject\AbstractEntity');
    }
}

(new Bootstrap())->run();
