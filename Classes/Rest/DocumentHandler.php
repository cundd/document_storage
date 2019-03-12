<?php
declare(strict_types=1);

namespace Cundd\DocumentStorage\Rest;

use Cundd\DocumentStorage\Persistence\DataMapper;
use Cundd\Rest\DataProvider\ExtractorInterface;
use Cundd\Rest\DataProvider\IdentityProviderInterface;
use Cundd\Rest\Exception\InvalidPropertyException;
use Cundd\Rest\Handler\HandlerInterface;
use Cundd\Rest\Http\RestRequestInterface;
use Cundd\Rest\Log\LoggerInterface;
use Cundd\Rest\ObjectManagerInterface;
use Cundd\Rest\ResponseFactoryInterface;
use Cundd\Rest\Router\Route;
use Cundd\Rest\Router\RouterInterface;

class DocumentHandler implements HandlerInterface
{
    use CrudHandlerTrait;

    private const VERSION = '0.1.0';

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

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * Handler constructor
     *
     * @param ObjectManagerInterface   $objectManager
     * @param ResponseFactoryInterface $responseFactory
     * @param LoggerInterface          $logger
     */
    public function __construct(
        ObjectManagerInterface $objectManager,
        ResponseFactoryInterface $responseFactory,
        LoggerInterface $logger
    ) {
        $this->objectManager = $objectManager;
        $this->responseFactory = $responseFactory;
        $this->logger = $logger;
    }

    protected function getLogger(): LoggerInterface
    {
        return $this->logger;
    }

    public function getDescription()
    {
        return 'Handler for Document Storage requests';
    }

    public function getProperty(
        RestRequestInterface $request,
        string $databaseName,
        string $identifier,
        string $propertyKey
    ) {
        return $this->performGetProperty($this->getDataProvider($databaseName), $request, $identifier, $propertyKey);
    }

    public function show(
        RestRequestInterface $request,
        string $databaseName,
        string $identifier
    ) {
        return $this->performShow($this->getDataProvider($databaseName), $request, $identifier);
    }

    public function createOrUpdate(
        RestRequestInterface $request,
        string $databaseName,
        string $identifier
    ) {
        $resourceType = $request->getResourceType();
        $dataProvider = $this->getDataProvider($databaseName);
        if ($dataProvider->fetchModel($identifier, $resourceType)) {
            $response = $this->performUpdate($dataProvider, $request, $identifier, $request->getSentData());

            if ($response instanceof \Exception) {
                throw $response;
            }

            return $response;
        } else {
            $data = $request->getSentData();
            if (isset($data['id']) && (string)$data['id'] !== $identifier) {
                return new InvalidPropertyException('Property "id" is set but does not match the URI\'s identifier');
            }
            $data['id'] = $identifier;

            $response = $this->performCreate($dataProvider, $request, $data);
            if ($response instanceof \Exception) {
                throw $response;
            }

            return $response;
        }
    }

    public function create(RestRequestInterface $request, string $databaseName)
    {
        return $this->performCreate($this->getDataProvider($databaseName), $request, $request->getSentData());
    }

    public function update(RestRequestInterface $request, string $databaseName, $identifier)
    {
        return $this->performUpdate(
            $this->getDataProvider($databaseName),
            $request,
            $identifier,
            $request->getSentData()
        );
    }

    public function delete(RestRequestInterface $request, string $databaseName, string $identifier)
    {
        return $this->performDelete($this->getDataProvider($databaseName), $request, $identifier);
    }

    public function listAll(RestRequestInterface $request, string $databaseName)
    {
        return $this->performListAll($this->getDataProvider($databaseName), $request);
    }

    public function countAll(RestRequestInterface $request, string $databaseName)
    {
        return $this->performCountAll($this->getDataProvider($databaseName), $request);
    }

    public function info(
        /** @noinspection PhpUnusedParameterInspection */
        RestRequestInterface $request
    ) {
        return 'Document Storage ' . self::VERSION;
    }

    public function options()
    {
        return $this->performOptions();
    }

    public function configureRoutes(RouterInterface $router, RestRequestInterface $request)
    {
        $resourceType = $request->getResourceType();
        $router->add(Route::get($resourceType . '/?', [$this, 'info']));
        $router->add(Route::get($resourceType . '/{slug}/?', [$this, 'listAll']));
        $router->add(Route::get($resourceType . '/{slug}/_count/?', [$this, 'countAll']));
        $router->add(Route::post($resourceType . '/{slug}/?', [$this, 'create']));
        $router->add(Route::get($resourceType . '/{slug}/{slug}/?', [$this, 'show']));
        $router->add(Route::put($resourceType . '/{slug}/{slug}/?', [$this, 'createOrUpdate']));
        $router->add(Route::post($resourceType . '/{slug}/{slug}/?', [$this, 'createOrUpdate']));
        $router->add(Route::options($resourceType . '/{slug}/{slug}/?', [$this, 'options']));
        $router->add(Route::delete($resourceType . '/{slug}/{slug}/?', [$this, 'delete']));
        $router->add(Route::patch($resourceType . '/{slug}/{slug}/?', [$this, 'update']));
        $router->add(Route::get($resourceType . '/{slug}/{slug}/{slug}/?', [$this, 'getProperty']));
        $router->add(Route::options($resourceType . '/{slug}/?', [$this, 'options']));
    }

    /**
     * Returns the Data Provider
     *
     * @param string $databaseName
     * @return DocumentDataProvider
     */
    protected function getDataProvider(string $databaseName): DocumentDataProvider
    {
        return new DocumentDataProvider(
            $this->objectManager,
            $this->objectManager->get(ExtractorInterface::class),
            $this->objectManager->get(IdentityProviderInterface::class),
            $this->objectManager->get(LoggerInterface::class),
            $databaseName,
            $this->objectManager->get(DataMapper::class)
        );
    }
}
