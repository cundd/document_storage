<?php

declare(strict_types=1);

namespace Cundd\DocumentStorage\Rest;

use Cundd\DocumentStorage\Constants;
use Cundd\DocumentStorage\Domain\Model\Document;
use Cundd\DocumentStorage\Domain\Model\DocumentInterface;
use Cundd\DocumentStorage\Domain\Repository\DocumentRepository;
use Cundd\DocumentStorage\Domain\Repository\DocumentRepositoryFactory;
use Cundd\DocumentStorage\Exception\InvalidIdException;
use Cundd\DocumentStorage\Persistence\DataMapper;
use Cundd\Rest\DataProvider\DataProvider;
use Cundd\Rest\DataProvider\DataProviderInterface;
use Cundd\Rest\DataProvider\ExtractorInterface;
use Cundd\Rest\DataProvider\IdentityProviderInterface;
use Cundd\Rest\Domain\Model\ResourceType;
use Cundd\Rest\Log\LoggerInterface;
use Cundd\Rest\ObjectManagerInterface;
use JsonSerializable;

class DocumentDataProvider extends DataProvider implements DataProviderInterface
{
    public const IDENTIFIER_PROPERTY = '__identity';

    private DocumentRepository $repository;

    private string $databaseName;

    private DataMapper $dataMapper;

    public function __construct(
        ObjectManagerInterface $objectManager,
        ExtractorInterface $extractor,
        IdentityProviderInterface $identityProvider,
        DocumentRepositoryFactory $documentRepositoryFactory,
        string $databaseName,
        LoggerInterface $logger = null,
        ?DataMapper $dataMapper = null
    ) {
        parent::__construct($objectManager, $extractor, $identityProvider, $logger);
        $this->repository = $documentRepositoryFactory->buildDocumentRepository($databaseName);
        $this->dataMapper = $dataMapper ?? $objectManager->get(DataMapper::class);
        $this->databaseName = $databaseName;
    }

    public function getRepositoryForResourceType(ResourceType $resourceType): object
    {
        return $this->repository;
    }

    public function fetchAllModels(ResourceType $resourceType): iterable
    {
        return $this->repository->findAll();
    }

    public function countAllModels(ResourceType $resourceType): int
    {
        return $this->repository->countAll();
    }

    /**
     * @param int|array|string $identifier
     * @param ResourceType     $resourceType
     * @return DocumentInterface|null
     */
    public function fetchModel(int|array|string $identifier, ResourceType $resourceType): ?object
    {
        return $this->repository->findById($identifier);
    }

    //public function createModel(array $data, ResourceType $resourceType)
    //{
    //    // If no data is given return a new empty instance
    //    if (!$data) {
    //        return $this->getEmptyModelForResourceType($resourceType);
    //    }
    //
    //    // It is **not** allowed to insert Models with a defined UID
    //    if (isset($data['__identity']) && $data['__identity']) {
    //        return new InvalidPropertyException('Invalid property "__identity"');
    //    }
    //
    //    if (isset($data['uid']) && isset($data['id'])) {
    //        return new InvalidPropertyException('Either the "id" or "uid" must be given');
    //    }
    //    if (isset($data['uid'])) {
    //        $data['id'] = $data['uid'];
    //        unset($data['uid']);
    //    }
    //
    //    // Get a fresh model
    //    return $this->convertIntoModel($data, $resourceType);
    //}

    public function convertIntoModel(array $data, ResourceType $resourceType): ?object
    {
        // If no data is given return NULL
        if (!$data) {
            return null;
        }

        $data = $this->prepareModelData($data);
        if (!isset($data['id']) || !$data['id']) {
            $exception = new InvalidIdException('Missing object ID', 1390319238);
            $this->logException($exception);

            return $exception;
        }

        try {
            $identity = $data[self::IDENTIFIER_PROPERTY] ?? null;
            if (null !== $identity) {
                $model = $this->fetchModel($identity, $resourceType);
                unset($data[self::IDENTIFIER_PROPERTY]);
                $model = $this->dataMapper->hydrate($model, $data);
            } else {
                $model = $this->dataMapper->mapSingleRow(Document::class, $data);
            }

            $model->setDb($this->databaseName);

            return $model;
        } catch (\Exception $exception) {
            $this->logException($exception);

            return null;
        }
    }

    /**
     * @param DocumentInterface $model
     * @param string            $propertyParameter
     * @return array|bool|float|int|mixed|string|null
     */
    public function getModelProperty($model, string $propertyParameter): mixed
    {
        assert($model instanceof DocumentInterface);
        $propertyKey = $this->convertPropertyParameterToKey($propertyParameter);

        $normalizedGetter = 'get' . ucfirst($propertyKey);
        if (method_exists($model, $normalizedGetter) && is_callable([$model, $normalizedGetter])) {
            return $this->getModelData($model->$normalizedGetter());
        }

        $getter = 'get' . ucfirst($propertyParameter);
        if (method_exists($model, $getter) && is_callable([$model, $getter])) {
            return $this->getModelData($model->$getter());
        }

        $value = $model->valueForKey($propertyKey);
        if (null !== $value) {
            return $this->getModelData($value);
        } else {
            return $this->getModelData($model->valueForKey($propertyParameter));
        }
    }

    /**
     * Returns the data from the given model
     *
     * @param mixed $model
     * @return array
     */
    public function getModelData(mixed $model): ?array
    {
        if (!is_object($model)) {
            return null;
        }

        if ($model instanceof JsonSerializable) {
            return $model->jsonSerialize();
        }

        assert($model instanceof DocumentInterface);
        if (!($model instanceof Document)) {
            return parent::getModelData($model);
        }

        $unpackedData = $model->getUnpackedData();
        if ($unpackedData === null) {
            $unpackedData = [];
        }
        $properties = array_merge(
            (array)$unpackedData,
            $this->extractor->extract($model),
            [
                '_meta' => [
                    'db'               => $model->getDb(),
                    'guid'             => $model->getGuid(),
                    'modificationTime' => $this->buildDateFromValue($model->valueForKey('modificationTime')),
                    'creationTime'     => $this->buildDateFromValue($model->valueForKey('creationTime')),
                ],
            ]
        );

        // Remove the already assigned entries
        unset($properties[Constants::DATA_PROPERTY_NAME]);
        unset($properties['db']);
        unset($properties['modificationTime']);
        unset($properties['creationTime']);

        return $properties;
    }

    /**
     * @param $input
     * @return string|null
     */
    protected function buildDateFromValue($input): ?string
    {
        if (!is_numeric($input)) {
            return null;
        }

        try {
            return (new \DateTimeImmutable('@' . $input))->format(\DateTime::ATOM);
        } catch (\Exception $e) {
            return null;
        }
    }
}
