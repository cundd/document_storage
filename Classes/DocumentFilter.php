<?php

declare(strict_types=1);

namespace Cundd\DocumentStorage;

use Cundd\DocumentStorage\Domain\Model\DocumentInterface;
use TYPO3\CMS\Extbase\Persistence\QueryResultInterface;

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
     * @param bool                                     $strict Define if the comparison should be strict (using `!==`) or not (using `!=`)
     * @return iterable
     */
    public function filterByProperties(
        iterable $collection,
        array $constraints,
        int $limit,
        bool $strict = true
    ): iterable {
        $resultCount = 0;

        foreach ($collection as $currentDocument) {
            if ($this->documentMatchesProperties($constraints, $currentDocument, $strict)) {
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
     * @param bool              $strict
     * @return bool
     */
    private function documentMatchesProperties(
        array $constraints,
        DocumentInterface $currentDocument,
        bool $strict
    ): bool {
        foreach ($constraints as $key => $constraint) {
            if (!str_contains($key, '.')) {
                $documentValue = $currentDocument->valueForKey($key);
            } else {
                $documentValue = $currentDocument->valueForKeyPath($key);
            }

            if ($constraint != $documentValue) {
                return false;
            } elseif ($strict && $constraint !== $documentValue) {
                return false;
            }
        }

        return true;
    }
}
