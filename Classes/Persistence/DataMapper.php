<?php

declare(strict_types=1);

namespace Cundd\DocumentStorage\Persistence;

use Cundd\DocumentStorage\Constants;
use Cundd\DocumentStorage\Domain\Model\Document;
use Cundd\DocumentStorage\Domain\Model\DocumentInterface;
use Cundd\DocumentStorage\Exception\InvalidDocumentException;
use Cundd\DocumentStorage\Persistence\Exception\DataMappingException;

use function is_array;

/**
 * Data Mapper allows to create and populate Documents with data
 */
class DataMapper implements DataMapperInterface
{
    public function map(string $className, array $rows): array
    {
        $objects = [];
        foreach ($rows as $row) {
            if (!is_array($row)) {
                throw new DataMappingException(
                    'Argument "rows" must be an array of arrays'
                );
            }
            $objects[] = $this->mapSingleRow($className, $row);
        }

        return $objects;
    }

    public function mapSingleRow(
        string $className,
        array $row,
    ): DocumentInterface {
        if (!is_a($className, DocumentInterface::class, true)) {
            throw new InvalidDocumentException(
                'Target class must be a subclass of Document'
            );
        }

        return $this->hydrate(new $className(), $row);
    }

    /**
     * Hydrate the given object with data from the row
     *
     * @template T of DocumentInterface
     *
     * @param T                   $document
     * @param array<string,mixed> $row      A single array with field_name => value pairs
     *
     * @return T Returns the populated instance
     */
    public function hydrate(
        DocumentInterface $document,
        array $row,
    ): DocumentInterface {
        /*
         * Check if the input has a value for key 'data_private' or
         * 'dataProtected' and set it first
         */
        if (isset($row['data_protected'])) {
            $row[Constants::DATA_PROPERTY_NAME] = $row['data_protected'];
            unset($row['data_protected']);
        }
        $key = Constants::DATA_PROPERTY_NAME;
        if (isset($row[$key])) {
            $document->setValueForKey($key, $row[$key]);
            unset($row[$key]);
        }

        /*
         * Loop through each (remaining) key value pair from the input and
         * assign it to the Document
         */
        foreach ($row as $key => $value) {
            assert(!empty($key));
            $document->setValueForKey($key, $value);
        }

        return $document;
    }
}
