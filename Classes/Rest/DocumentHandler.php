<?php
declare(strict_types=1);

namespace Cundd\DocumentStorage\Rest;

use Cundd\DocumentStorage\Persistence\DataMapper;
use Cundd\Rest\DataProvider\ExtractorInterface;
use Cundd\Rest\DataProvider\IdentityProviderInterface;
use Cundd\Rest\Handler\CrudHandler;
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
     * @var CrudHandler
     */
    private $concreteHandler;

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
        $this->concreteHandler = $objectManager->get(CrudHandler::class);
    }

    protected function getLogger(): LoggerInterface
    {
        return $this->logger;
    }

    public function getDescription()
    {
        return 'Handler for Document Storage requests';
    }

    public function getProperty(RestRequestInterface $request, string $databaseName, $identifier, $propertyKey)
    {
        return $this->crudGetProperty($this->getDataProvider($databaseName), $request, $identifier, $propertyKey);
    }

    public function show(RestRequestInterface $request, string $databaseName, $identifier)
    {
        return $this->crudShow($this->getDataProvider($databaseName), $request, $identifier);
    }

    public function create(RestRequestInterface $request, string $databaseName)
    {
        return $this->crudCreate($this->getDataProvider($databaseName), $request);
    }

    public function update(RestRequestInterface $request, string $databaseName, $identifier)
    {
        return $this->crudUpdate($this->getDataProvider($databaseName), $request, $identifier);
    }

    public function delete(RestRequestInterface $request, string $databaseName, $identifier)
    {
        return $this->crudDelete($this->getDataProvider($databaseName), $request, $identifier);
    }

    public function listAll(RestRequestInterface $request, string $databaseName)
    {
        return $this->crudListAll($this->getDataProvider($databaseName), $request);
    }

    public function countAll(RestRequestInterface $request, string $databaseName)
    {
        return $this->crudCountAll($this->getDataProvider($databaseName), $request);
    }

    public function info(RestRequestInterface $request)
    {
        return 'Document Storage ' . self::VERSION;
    }

    public function options()
    {
        return $this->crudOptions();
    }

    public function configureRoutes(RouterInterface $router, RestRequestInterface $request)
    {
        $resourceType = $request->getResourceType();
        $router->add(Route::get($resourceType . '/?', [$this, 'info']));
        $router->add(Route::get($resourceType . '/{slug}/?', [$this, 'listAll']));
        $router->add(Route::get($resourceType . '/{slug}/_count/?', [$this, 'countAll']));
        $router->add(Route::post($resourceType . '/{slug}/?', [$this, 'create']));
        $router->add(Route::get($resourceType . '/{slug}/{slug}/?', [$this, 'show']));
        $router->add(Route::put($resourceType . '/{slug}/{slug}/?', [$this, 'update']));
        $router->add(Route::post($resourceType . '/{slug}/{slug}/?', [$this, 'update']));
        $router->add(Route::delete($resourceType . '/{slug}/{slug}/?', [$this, 'delete']));
        $router->add(Route::routeWithPatternAndMethod($resourceType . '/{slug}/{slug}/?', 'PATCH', [$this, 'update']));
        $router->add(Route::get($resourceType . '/{slug}/{slug}/{slug}/?', [$this, 'getProperty']));
        $router->add(Route::routeWithPatternAndMethod($resourceType . '/{slug}/?', 'OPTIONS', [$this, 'options']));
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
