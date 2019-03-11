<?php
declare(strict_types=1);

namespace Cundd\DocumentStorage\Persistence;

use Cundd\DocumentStorage\Domain\Exception\InvalidDocumentException;
use Cundd\DocumentStorage\Domain\Model\Document;

class DataMapper
{
    /**
     * Map the given rows on objects
     *
     * @param string $className The name of the class
     * @param array  $rows      An array of arrays with field_name => value pairs
     * @return Document[]|$className[] An array of objects of the given class
     */
    public function map(string $className, array $rows): array
    {
        $objects = [];
        foreach ($rows as $row) {
            $objects[] = $this->mapSingleRow($className, $row);
        }

        return $objects;
    }

    /**
     * Map a single row on an object of the given class
     *
     * @param string $className The name of the target class
     * @param array  $row       A single array with field_name => value pairs
     * @return Document|null An object of the given class
     */
    public function mapSingleRow(string $className, array $row): ?Document
    {
        if (!is_a($className, Document::class, true)) {
            throw new InvalidDocumentException('Target class must be a subclass of Document');
        }

        /** @var Document $document */
        $document = new $className;

        return $this->hydrate($document, $row);
    }

    /**
     * Hydrate the given object with data from the row
     *
     * @param Document $document
     * @param array    $row A single array with field_name => value pairs
     * @return Document returns the populated instance
     */
    public function hydrate(Document $document, array $row): Document
    {
        /*
         * Check if the input has a value for key 'data_private' or
         * 'dataProtected' and set it first
         */
        if (isset($row['data_protected'])) {
            $row[Document::DATA_PROPERTY_NAME] = $row['data_protected'];
            unset($row['data_protected']);
        }
        $key = Document::DATA_PROPERTY_NAME;
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