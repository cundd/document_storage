<?php

declare(strict_types=1);

namespace Cundd\DocumentStorage\Rest;

use Cundd\Rest\DataProvider\DataProviderInterface;
use Cundd\Rest\DataProvider\Utility;
use Cundd\Rest\Http\RestRequestInterface;
use Cundd\Rest\Log\LoggerInterface;
use LimitIterator;

trait CrudHandlerTrait
{
    /**
     * Object Manager
     *
     * @var \Cundd\Rest\ObjectManagerInterface
     */
    protected $objectManager;

    /**
     * @var \Cundd\Rest\ResponseFactoryInterface
     */
    protected $responseFactory;

    abstract protected function getLogger(): LoggerInterface;

    /**
     * @param DocumentDataProvider|DataProviderInterface $dataProvider
     * @param RestRequestInterface                       $request
     * @param                                            $identifier
     * @param                                            $propertyKey
     * @return mixed|\Psr\Http\Message\ResponseInterface
     */
    public function performGetProperty(
        DataProviderInterface $dataProvider,
        RestRequestInterface $request,
        string $identifier,
        string $propertyKey
    ) {
        $resourceType = $request->getResourceType();
        $model = $dataProvider->fetchModel($identifier, $resourceType);
        if (!$model) {
            return $this->responseFactory->createSuccessResponse(null, 404, $request);
        }

        return $dataProvider->getModelProperty($model, $propertyKey);
    }

    /**
     * @param DocumentDataProvider|DataProviderInterface $dataProvider
     * @param RestRequestInterface                       $request
     * @param string                                     $identifier
     * @return \Psr\Http\Message\ResponseInterface|array
     */
    public function performShow(
        DataProviderInterface $dataProvider,
        RestRequestInterface $request,
        string $identifier
    ) {
        $resourceType = $request->getResourceType();
        $model = $dataProvider->fetchModel($identifier, $resourceType);
        if (!$model) {
            return $this->responseFactory->createSuccessResponse(null, 404, $request);
        }
        $result = $dataProvider->getModelData($model);

        return $this->prepareResult($request, $result);
    }

    /**
     * @param DocumentDataProvider|DataProviderInterface $dataProvider
     * @param RestRequestInterface                       $request
     * @param array|null                                 $data
     * @return \Psr\Http\Message\ResponseInterface|array
     */
    public function performCreate(
        DataProviderInterface $dataProvider,
        RestRequestInterface $request,
        ?array $data
    ) {
        $this->getLogger()->logRequest('create request', ['body' => $data]);

        if (null === $data) {
            return $this->responseFactory->createErrorResponse('Invalid or missing payload', 400, $request);
        }

        $resourceType = $request->getResourceType();
        $model = $dataProvider->createModel($data, $resourceType);
        if (!$model) {
            return $this->responseFactory->createErrorResponse(null, 400, $request);
        } elseif ($model instanceof \Exception) {
            return $this->responseFactory->createErrorResponse($model->getMessage(), 400, $request);
        }

        $dataProvider->saveModel($model, $resourceType);
        $result = $dataProvider->getModelData($model);

        return $this->prepareResult($request, $result);
    }

    /**
     * @param DocumentDataProvider|DataProviderInterface $dataProvider
     * @param RestRequestInterface                       $request
     * @param string                                     $identifier
     * @param array|null                                 $data
     * @return \Psr\Http\Message\ResponseInterface|array
     */
    public function performUpdate(
        DataProviderInterface $dataProvider,
        RestRequestInterface $request,
        string $identifier,
        ?array $data
    ) {
        $resourceType = $request->getResourceType();

        $data[DocumentDataProvider::IDENTIFIER_PROPERTY] = $identifier;
        if (!isset($data['id'])) {
            $data['id'] = $identifier;
        }

        $this->getLogger()->logRequest('update request', ['body' => $data]);

        // Make sure the object with the given identifier exists
        $oldObject = $dataProvider->fetchModel($identifier, $resourceType);
        if (!$oldObject) {
            return $this->responseFactory->createErrorResponse(null, 404, $request);
        }

        $model = $dataProvider->convertIntoModel($data, $resourceType);
        if (!$model) {
            return $this->responseFactory->createErrorResponse(null, 400, $request);
        } elseif ($model instanceof \Exception) {
            return $this->responseFactory->createErrorResponse($model->getMessage(), 400, $request);
        }

        $dataProvider->saveModel($model, $resourceType);
        $result = $dataProvider->getModelData($model);

        return $this->prepareResult($request, $result);
    }

    /**
     * @param DocumentDataProvider|DataProviderInterface $dataProvider
     * @param RestRequestInterface                       $request
     * @param string                                     $identifier
     * @return \Psr\Http\Message\ResponseInterface|array
     */
    public function performDelete(
        DataProviderInterface $dataProvider,
        RestRequestInterface $request,
        string $identifier
    ) {
        $resourceType = $request->getResourceType();
        $this->getLogger()->logRequest('delete request', ['identifier' => $identifier]);
        $model = $dataProvider->fetchModel($identifier, $resourceType);
        if (!$model) {
            return $this->responseFactory->createErrorResponse(null, 404, $request);
        }
        $dataProvider->removeModel($model, $resourceType);

        return $this->responseFactory->createSuccessResponse('Deleted', 200, $request);
    }

    /**
     * @param DocumentDataProvider|DataProviderInterface $dataProvider
     * @param RestRequestInterface                       $request
     * @return mixed
     */
    public function performListAll(DataProviderInterface $dataProvider, RestRequestInterface $request)
    {
        $resourceType = $request->getResourceType();
        $allModels = $dataProvider->fetchAllModels($resourceType);

        return $this->prepareResult(
            $request,
            array_map([$dataProvider, 'getModelData'], $this->sliceResults($allModels)),
            false
        );
    }

    /**
     * @param DocumentDataProvider|DataProviderInterface $dataProvider
     * @param RestRequestInterface                       $request
     * @return int
     */
    public function performCountAll(DataProviderInterface $dataProvider, RestRequestInterface $request)
    {
        return $dataProvider->countAllModels($request->getResourceType());
    }

    /**
     * @param DataProviderInterface|null $dataProvider
     * @return bool
     */
    public function performOptions(
        /** @noinspection PhpUnusedParameterInspection */
        ?DataProviderInterface $dataProvider = null
    ) {
        // TODO: Respond with the correct preflight headers
        return true;
    }

    /**
     * Add the root object key if configured
     *
     * @param RestRequestInterface $request
     * @param mixed                $result
     * @param bool                 $singularize
     * @return array
     */
    protected function prepareResult(RestRequestInterface $request, $result, $singularize = true)
    {
        if ($this->getAddRootObjectForCollection()) {
            $key = $singularize ? Utility::singularize($request->getRootObjectKey()) : $request->getRootObjectKey();

            return [$key => $result];
        }

        return $result;
    }

    /**
     * Return if the root object key should be added to the response data
     *
     * @return bool
     */
    protected function getAddRootObjectForCollection()
    {
        return (bool)$this->objectManager->getConfigurationProvider()->getSetting('addRootObjectForCollection');
    }

    /**
     * @param iterable|array $models
     * @return array|LimitIterator
     */
    protected function sliceResults($models)
    {
        $limit = $this->getListLimit();
        if (is_array($models)) {
            return array_slice($models, 0, $limit, true);
        }
        if ($models instanceof \IteratorAggregate) {
            $models = $models->getIterator();
        }
        if ($models instanceof \Iterator) {
            return iterator_to_array(new LimitIterator($models, 0, $limit));
        }

        return $models;
    }

    /**
     * Specifies the maximum number of models that should be output in `listAll()`
     *
     * @return int
     */
    protected function getListLimit(): int
    {
        return PHP_INT_MAX;
    }
}
