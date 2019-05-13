<?php
declare(strict_types=1);

namespace Cundd\DocumentStorage;

use Cundd\DocumentStorage\Domain\Model\DocumentInterface;
use TYPO3\CMS\Extbase\Persistence\QueryResultInterface;
use function strpos;

/**
 * Class to filter a collection of Documents according to given constraints
 */
class DocumentFilter
{
    /**
     * Loop through each Document and check it against each of the constraint properties
     *
     * @param QueryResultInterface|DocumentInterface[] $collection
     * @param array                                    $constraints
     * @param int                                      $limit
     * @return iterable
     */
    public function filterByProperties(iterable $collection, array $constraints, int $limit): iterable
    {
        $resultCount = 0;

        foreach ($collection as $currentDocument) {
            if ($this->documentMatchesProperties($constraints, $currentDocument)) {
                yield $currentDocument;

                $resultCount += 1;
                if ($resultCount >= $limit) {
                    return;
                }
            }
        }
    }

    /**
     * @param array             $constraints
     * @param DocumentInterface $currentDocument
     * @return bool
     */
    private function documentMatchesProperties(array $constraints, DocumentInterface $currentDocument): bool
    {
        foreach ($constraints as $key => $constraint) {
            if (strpos($key, '.') === false) {
                $documentValue = $currentDocument->valueForKey($key);
            } else {
                $documentValue = $currentDocument->valueForKeyPath($key);
            }
            if ($constraint !== $documentValue) {
                return false;
            }
        }

        return true;
    }
}
