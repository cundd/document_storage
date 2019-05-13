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
class DataMapper
{
    /**
     * Map the given rows on objects
     *
     * @param string $className The name of the class
     * @param array  $rows      An array of arrays with field_name => value pairs
     * @return DocumentInterface[]|$className[] An array of objects of the given class
     */
    public function map(string $className, array $rows): array
    {
        $objects = [];
        foreach ($rows as $row) {
            if (!is_array($row)) {
                throw new DataMappingException('Argument "rows" must be an array of arrays');
            }
            $objects[] = $this->mapSingleRow($className, $row);
        }

        return $objects;
    }

    /**
     * Map a single row on an object of the given class
     *
     * @param string $className The name of the target class
     * @param array  $row       A single array with field_name => value pairs
     * @return DocumentInterface|null An object of the given class
     */
    public function mapSingleRow(string $className, array $row): ?DocumentInterface
    {
        if (!is_a($className, Document::class, true)) {
            throw new InvalidDocumentException('Target class must be a subclass of Document');
        }

        /** @var DocumentInterface $document */
        $document = new $className;

        return $this->hydrate($document, $row);
    }

    /**
     * Hydrate the given object with data from the row
     *
     * @param DocumentInterface $document
     * @param array             $row A single array with field_name => value pairs
     * @return DocumentInterface returns the populated instance
     */
    public function hydrate(DocumentInterface $document, array $row): DocumentInterface
    {
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
        //if (isset($row['__identity'])) {
        //    $document->setValueForKey('uid', $row['__identity']);
        //    unset($row['__identity']);
        //}

        /*
         * Loop through each (remaining) key value pair from the input and
         * assign it to the Document
         */
        foreach ($row as $key => $value) {
            $document->setValueForKey($key, $value);
        }

        return $document;
    }
}
