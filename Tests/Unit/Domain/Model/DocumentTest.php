<?php
declare(strict_types=1);

namespace Cundd\DocumentStorage\Tests\Unit\Domain\Model;

use Cundd\DocumentStorage\Domain\Model\Document;

/**
 * Document test case
 */
class DocumentTest extends AbstractDocumentCase
{
    /**
     * @var Document
     */
    protected $fixture = null;

    protected function buildDocument(): Document
    {
        return new Document();
    }
}
