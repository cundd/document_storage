<?php
declare(strict_types=1);

namespace Cundd\DocumentStorage\Tests;

use Cundd\DocumentStorage\Tests\Fixture\AbstractEntity;
use Cundd\DocumentStorage\Tests\Fixture\ClassNamingUtility;
use Cundd\DocumentStorage\Tests\Fixture\RepositoryInterface;
use Cundd\DocumentStorage\Tests\Fixture\SingletonInterface;

class Bootstrap
{
    public function run()
    {
        class_alias(AbstractEntity::class, '\\TYPO3\\CMS\\Extbase\\' . 'DomainObject\\AbstractEntity');
        class_alias(RepositoryInterface::class, '\\TYPO3\\CMS\\Extbase\\' . 'Persistence\\RepositoryInterface');
        class_alias(SingletonInterface::class, '\\TYPO3\\CMS\\Core\\' . 'SingletonInterface');
        class_alias(ClassNamingUtility::class, '\\TYPO3\\CMS\\Core\\' . 'Utility\\ClassNamingUtility');
    }
}

(new Bootstrap())->run();
