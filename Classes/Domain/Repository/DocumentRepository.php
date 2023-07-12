<?php

declare(strict_types=1);

namespace Cundd\DocumentStorage\Domain\Repository;

use Cundd\DocumentStorage\Domain\Model\Document;
use Cundd\DocumentStorage\Persistence\Repository\FixedDatabaseBridge;

/**
 * Document Repository provides access to a single Database (specified at the time of creation)
 *
 * Only Documents of the specified Database can be managed by this Repository. So this is a more traditional Repository.
 * To manage Documents belonging to any Database use the `FreeDocumentRepository`.
 * To create a custom Repository to manage Documents use the `AbstractDocumentRepository`.
 */
class DocumentRepository extends FixedDatabaseBridge
{
}
