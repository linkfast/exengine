<?php
/* PHP Version Check */

namespace {
    if (version_compare(PHP_VERSION, '5.6.0', '<')) {
        print 'ExEngine requires PHP 5.6 or higher, please update your installation.';
        exit();
    }
}
/**
 * Framework namespace.
 */

namespace ExEngine {

    use Throwable;

    class RestController
    {
        /**
         * @param array $argument_array
         * @return mixed
         * @throws ResponseException
         */
        final function executeRest(array $argument_array)
        {
            $request_method = 'get';
            if (isset($_SERVER['REQUEST_METHOD'])) {
                $request_method = strtolower($_SERVER['REQUEST_METHOD']);
            }
            if (method_exists($this, $request_method)) {
                return call_user_func_array([$this, $request_method], $argument_array);
            } else {
                throw new ResponseException("REST Method (" . $request_method . ") is not defined.", 404);
            }
        }
    }

    class Body
    {
        private $rawBody;

        public function __construct($rawBody)
        {
            $this->rawBody = $rawBody;
        }

        function raw()
        {
            return $this->rawBody;
        }

        function array()
        {
            return json_decode($this->rawBody, true);
        }
    }

    /**
     * Class Request
     * This class can be used as argument of a controller constructor for dependency injection.
     * @package ExEngine
     */
    class Request
    {
        private $body;

        public function __construct()
        {
            $this->body = new Body(file_get_contents("php://input"));
        }

        function getBody()
        {
            return $this->body;
        }
    }

    /**
     * Class DataClass
     * This class is supposed to be used as a parent of any object returning function.
     * ExEngine CoreX will automatically parse the properties as a json object.
     * Available modifiers:
     *  supressNulls: if true, all non-initialized or null properties will be stripped out.
     *      Can be set globally in `BaseConfig::supressNulls` or in `$this->dcConfiguration->supressNulls`.
     * @package ExEngine
     */
    abstract class DataClass
    {
        /**
         * Contains the DataClass configuration, should be set it in the child constructor.
         * @var null|\ExEngine\DataClassLocalConfig
         */
        protected $dcConfiguration = null;

        /**
         * Converts all properties into a serializable array.
         * @return array
         */
        final public function expose()
        {
            if ((\ee()->getConfig()->isSuppressNulls() && $this->dcConfiguration == null) ||
                ($this->dcConfiguration != null && $this->dcConfiguration->isSupressNulls())) {
                return array_filter(get_object_vars($this), function ($v) {
                    if ($v === $this->dcConfiguration) {
                        return false;
                    }
                    return $v !== null;
                });
            } else {
                return array_filter(get_object_vars($this), function ($v) {
                    if ($v === $this->dcConfiguration) {
                        return false;
                    }
                    return true;
                });
            }
        }
    }

    /**
     * Class ResponseException
     * Simple extension to PHP's Exception class.
     * @package ExEngine
     */
    class ResponseException extends \Exception
    {
        public function __construct($message = "", $code = 0, Throwable $previous = null)
        {
            parent::__construct($message, $code, $previous);
        }
    }

    class DataClassLocalConfig
    {
        protected $supressNulls = false;

        /**
         * DataClassLocalConfig constructor.
         * @param bool|null $suppressNulls Activates `DataClass` automatic null suppression.
         */
        public final function __construct(
            $suppressNulls = null
        )
        {
            if ($suppressNulls === null) {
                $this->supressNulls = \ee()->getConfig()->isSuppressNulls();
            } else {
                $this->supressNulls = $suppressNulls;
            }
        }

        public final function isSupressNulls()
        {
            return $this->supressNulls;
        }
    }

    interface Extension {
        public static function validateConfiguration($configuration);
    }

    abstract class BaseConfig
    {
        /* config default values */
        protected $controllersLocation = 'App';
        protected $usePrettyPrint = true;
        protected $showVersionInfo = 'MINIMAL';
        protected $suppressNulls = true;
        protected $showStackTrace = true;
        protected $showHeaderBanner = true;
        protected $dbConnectionAuto = false;
        protected $launcherFolderPath = '';
        protected $filters = [];
        protected $services = [];
// TODO:        protected $extensions = [];
        protected $production = false;
        protected $forceAutoDbInit = false;
        protected $defaultControllerFunction = '';
        protected $defaultStaticAppStart = '';
        /* getters */
        /**
         * True if production optimizations are enabled. Please test your app in development mode first, production
         * mode will disable most of classes/methods/duplicates/etc checks to improve performance.
         * @return bool
         */
        public function isProduction()
        {
            return $this->production;
        }
        /**
         * @return bool
         */
        public function isForceAutoDbInit()
        {
            return $this->forceAutoDbInit;
        }
        /**
         * Returns an array with the filters to be chained.
         * @return array
         */
        public function getFilters()
        {
            return $this->filters;
        }
        /**
         * Returns the instance launcher folder path.
         * @return string
         */
        public function getLauncherFolderPath()
        {
            return $this->launcherFolderPath;
        }
        /**
         * True if JSON output null suppression is enabled.
         * @return bool
         */
        public function isSuppressNulls()
        {
            return $this->suppressNulls;
        }
        /**
         * Returns the controller's folder.
         * @return string
         */
        public function getControllersLocation()
        {
            return $this->controllersLocation;
        }
        /**
         * Returns true if pretty JSON printing is enabled.
         * @return bool
         */
        public function isUsePrettyPrint()
        {
            return $this->usePrettyPrint;
        }
        /**
         * @return string
         */
        public function getShowVersionInfo()
        {
            return $this->showVersionInfo;
        }
        /**
         * @return mixed
         */
        public function getSessionConfig()
        {
            return $this->sessionConfig;
        }
        /**
         * @return bool
         */
        public function isShowStackTrace()
        {
            return $this->showStackTrace;
        }
        /**
         * @return bool
         */
        public function isShowHeaderBanner()
        {
            return $this->showHeaderBanner;
        }
        /**
         * @return bool
         */
        public function isDbConnectionAuto()
        {
            return $this->dbConnectionAuto;
        }
        /**
         * @return string
         */
        public function getDefaultControllerFunction()
        {
            return $this->defaultControllerFunction;
        }
        /**
         * @return string
         */
        public function getDefaultStaticAppStart()
        {
            return $this->defaultStaticAppStart;
        }
        /**
         * @return array
         */
        public function getServices()
        {
            return $this->services;
        }
        // Setters
        public function setFilterInstance($filterClass, $filter) {
            $this->filters[$filterClass] = $filter;
        }
        /* non overridable methods */
//        final public function registerFilter(Filter $filter)
//        {
//            if (!$this->isProduction()) {
//                if (!$filter instanceof Filter) {
//                    throw new ResponseException("Invalid filter is trying to be registered in chain. Filters must
//                    be an instance of Filter interface.", 500);
//                }
//                foreach ($this->filters as $registeredFilter) {
//                    if (get_class($registeredFilter) == get_class($filter)) {
//                        CoreX::addDevelopmentMessage(['WARNING' => 'Filter class ' . get_class($registeredFilter) . ' is
//                            registered twice, not an error, but maybe a typo or intentional?.']);
//                    }
//                }
//            }
//            $this->filters[] = $filter;
//        }
    final function registerFilter($filterClass) {
        if (class_exists($filterClass)) {
            $this->filters[$filterClass] = 0;
        } else {
            throw new ResponseException("Class '$filterClass' not available. Cannot register as filter.",
                500);
        }
    }
// TODO: Extension Support, Planned For 1.2+
//    final public function enableExtension($extensionLoaderClass, $extensionConfig=null) {
//        if (class_exists($extensionLoaderClass)
//            && in_array(Extension::class, class_implements($extensionLoaderClass))) {
//            if ($extensionConfig == null)
//                $this->extensions[$extensionLoaderClass] = 0;
//            else {
//                // validate configuration
//                if (
//                    forward_static_call([$extensionLoaderClass, 'validateConfiguration'], $extensionConfig)
//                ) {
//                    $this->extensions[$extensionLoaderClass] = $extensionConfig;
//                } else {
//                    throw new ResponseException("Extension '$extensionLoaderClass' cannot be enabled. ".
//                        "Configuration is not valid.",
//                        500);
//                }
//            }
//        } else {
//            throw new ResponseException("Extension '$extensionLoaderClass' not available. Cannot enable.",
//                500);
//        }
//    }

        final public function registerService($serviceClass, $singleton = false)
        {
            if (class_exists($serviceClass)) {
                $this->services[$serviceClass] = ['singleton' => $singleton];
            } else {
                throw new ResponseException("Class '$serviceClass' not available. Cannot register as service.",
                    500);
            }
        }
        /* default overridables */
        /**
         * Default overridable method for defining a host_init connection. Do not call parent::dbInit();
         *
         * Important: Automatic detection and initialization of supported host_init managers is disabled by default
         * in production mode. You can force the execution in production mode setting $this->forceAutoDbInit to true.
         *
         * @throws ResponseException
         */
        public function dbInit()
        {
            if (!$this->isProduction() && !$this->forceAutoDbInit) {
                if (class_exists('\R')) {
                    // RedBeanPHP Classic version
                    \R::setup();
                } else if (class_exists('\RedBeanPHP\R')) {
                    // RedBeanPHP Composer version uses PSR-4
                    \RedBeanPHP\R::setup();
                } else if (class_exists('\PommProject\Foundation\Pomm')) {
                    // POMM
                    $pommLoader = $this->launcherFolderPath . '/.pomm_cli_bootstrap.php';
                    if (file_exists($pommLoader)) {
                        /** @noinspection PhpIncludeInspection */
                        $pomm = require $pommLoader;
                        if (sizeof($pomm->getSessionBuilders()) == 0) {
                            throw new ResponseException("POMM configuration file found, add a connection or override config::dbInit() or uninstall.", 500);
                        }
                        return $pomm;
                    } else {
                        throw new ResponseException("POMM found, please configure or override config::dbInit() or uninstall.", 500);
                    }
                } else if (class_exists('\ExEngine\DBO')) {
                    // EEDBM
                    return \ExEngine\EEDBM('./._EEDBM/');
                };
            }
        }

        /**
         * BaseConfig constructor, if you override, you must call parent constructor.
         * @param $launcherFolderPath You must pass the full folder path of your instance launcher, ex. new CoreX(new Config(__DIR__);.
         */
        public function __construct($launcherFolderPath)
        {
            $this->launcherFolderPath = $launcherFolderPath;
        }
    }

    class DefaultConfig extends BaseConfig
    {
    }

    class ErrorDetail extends DataClass
    {
        protected $stackTrace = [];
        protected $message = "";

        function __construct(
            array $stackTrace = null,
            $message
        )
        {
            $this->stackTrace = $stackTrace;
            $this->message = $message;
        }
    }

    /**
     * Class StandardResponse
     * This class is a generic serialization ready response data structure, part of the ExEngine Request Lifecycle
     * @package ExEngine
     */
    class StandardResponse extends DataClass
    {
        protected $took = 0;
        protected $code = 200;
        protected $data = null;
        protected $error = false;
        protected $errorDetails = null;
        protected $developmentMessages = null;

        /**
         * StandardResponse constructor.
         * @param int $took
         * @param int $code
         * @param array|NULL $data
         * @param bool $error
         * @param ErrorDetail|NULL|false $errorDetails
         * @param array|NULL|false $developmentMessages When set to an array, displays the development messages to the
         *                                              exposed http response body.
         */
        function __construct(
            $took,
            $code,
            array $data = NULL,
            $error = false,
            ErrorDetail $errorDetails = NULL,
            $developmentMessages = NULL
        )
        {
            $this->code = $code;
            $this->took = $took;
            $this->data = $data;
            $this->error = $error;
            if (!\ee()->getConfig()->isProduction())
                if ($developmentMessages != NULL || $developmentMessages != false) {
                    $this->developmentMessages = $developmentMessages;
                }
            if ($error != NULL || $error != false) {
                $this->errorDetails = $errorDetails->expose();
            }
        }
    }

    /**
     * Class ControllerMethodMeta
     * This class represents the metadata of a controller method just before being executed.
     * Specially created for using with filters, this allows to have a good structure for using a framework level
     * filter system, just as for Filter and RESTController classes.
     * @package ExEngine
     */
    final class ControllerMethodMeta
    {
        private $controllerLocation = '';
        private $controllerName = '';
        private $methodName = '';
        private $arguments = [];
        private $excludedFilters = [];
        private $allowedFilters = [];

        /**
         * ControllerMethodMeta constructor.
         * @param string $controllerLocation
         * @param string $controllerName
         * @param string $methodName
         * @param array $arguments
         * @param array $excludedFilters
         * @param array $allowedFilters
         */
        public function __construct(
            $controllerLocation,
            $controllerName,
            $methodName,
            array $arguments,
            array $excludedFilters = [],
            array $allowedFilters = [])
        {
            $this->controllerLocation = $controllerLocation;
            $this->controllerName = $controllerName;
            $this->methodName = $methodName;
            $this->arguments = $arguments;
            $this->excludedFilters = $excludedFilters;
            $this->allowedFilters = $allowedFilters;
        }

        public function link($method, ...$arguments)
        {
            return ee()->link(ee()->controller(), $method, ...$arguments);
        }

        /**
         * @return string
         */
        public function getControllerName()
        {
            return $this->controllerName;
        }

        /**
         * @return string
         */
        public function getMethodName()
        {
            return $this->methodName;
        }

        /**
         * @return array
         */
        public function getArguments()
        {
            return $this->arguments;
        }

        /**
         * @return string
         */
        public function getControllerLocation()
        {
            return $this->controllerLocation;
        }

        /**
         * @return array
         */
        public function getExcludedFilters()
        {
            return $this->excludedFilters;
        }

        /**
         * @return array
         */
        public function getAllowedFilters()
        {
            return $this->allowedFilters;
        }



    }

    abstract class Filter
    {
        function requestFilter(ControllerMethodMeta $controllerMeta, array &$filtersData)
        {}

        function responseFilter(ControllerMethodMeta $controllerMeta, $rawControllerResponse)
        {
            return $rawControllerResponse;
        }
    }

    class NullLogger {
        final function debug(...$arg) {}
        final function error(...$arg) {}
        final function info(...$arg) {}
    }

    final class CoreX
    {
        // Static part of CoreX
        /***
         * @param string $name
         * @return Monolog\Logger|NullLogger
         * @throws Exception
         */
        public static function getLogger($name='CoreX') {
            if (defined("EXENGINE_LOG") || defined("EXENGINE_LOG_WEB")) {
                $log = new \Monolog\Logger($name);
                if (defined('EXENGINE\LOG_LOCATION')) {
                    $log->pushHandler(new Monolog\Handler\StreamHandler(LOG_LOCATION, Monolog\Logger::WARNING));
                }
                return $log;
            } else return new NullLogger();
        }

        /**
         * This static variable contains the Core X instance that will be accessed globally.
         * @var CoreX
         */
        private static $instance = null;
        private static $developmentMessages = [];

        /**
         * Adds a message (can be any serializable object) to the DevelopmentMessages chain, only available
         * in development mode and with Standard responses.
         * @param string|mixed $message
         */
        public static function addDevelopmentMessage($message)
        {
            CoreX::$developmentMessages[] = $message;
        }

        /**
         * @return CoreX
         */
        public static function getInstance()
        {
            return self::$instance;
        }

        // Non-static part of CoreX

        private $config = null;

        /**
         * @return BaseConfig
         */
        public function getConfig()
        {
            return $this->config;
        }

        private function usePrettyPrint()
        {
            if ($this->getConfig()->isUsePrettyPrint()) {
                return JSON_PRETTY_PRINT;
            }
            return null;
        }

        /**
         * @param string $ControllerFilePath
         * @return string
         */
        private function getController($ControllerFilePath)
        {
            return $this->getConfig()->getControllersLocation() . '/' . $ControllerFilePath . '.php';
        }

        /**
         * @param string $ControllerFolder
         * @return string
         */
        private function getControllerFolder($ControllerFolder)
        {
            return $this->getConfig()->getControllersLocation() . '/' . $ControllerFolder;
        }

        // Filter Functions
        private $filterData = [];
        public function filtersData()
        {
            return $this->filterData;
        }
        private $singletonServiceInstances = [];
        /***
         * @throws ResponseException
         * @throws \ReflectionException
         */
        private function instantiateFilters() {
            foreach (array_keys($this->config->getFilters()) as $filterClass) {
                $this->config->setFilterInstance($filterClass, $this->injectDependenciesAndInstance($filterClass));
            }
        }
        private function processRequestFilters(ControllerMethodMeta $controllerMeta)
        {
            foreach ($this->getConfig()->getFilters() as $filter) {
                if (count($controllerMeta->getAllowedFilters()) > 0 &&
                    !in_array(get_class($filter), $controllerMeta->getAllowedFilters())) {
                        continue;
                }
                if (count($controllerMeta->getExcludedFilters()) > 0 &&
                    in_array(get_class($filter), $controllerMeta->getExcludedFilters())) {
                    continue;
                }
                $filterReturnData = $filter->requestFilter($controllerMeta, $this->filterData);
                if ($filterReturnData != null)
                    $this->filterData[get_class($filter)] = $filterReturnData;
            }
        }

        private function processResponseFilters(ControllerMethodMeta $controllerMeta, $rawControllerResponse)
        {
            foreach ($this->getConfig()->getFilters() as $filter) {
                if (count($controllerMeta->getAllowedFilters()) > 0 &&
                    !in_array(get_class($filter), $controllerMeta->getAllowedFilters())) {
                    continue;
                }
                if (count($controllerMeta->getExcludedFilters()) > 0 &&
                    in_array(get_class($filter), $controllerMeta->getExcludedFilters())) {
                    continue;
                }
                $filterReturnData = $filter->responseFilter($controllerMeta, $rawControllerResponse);
                if ($filterReturnData != null) {
                    $rawControllerResponse = $filter->responseFilter($controllerMeta, $rawControllerResponse);
                }
            }
            return $rawControllerResponse;
        }

        private $currentControllerMeta = null;
        private $currentControllerInstance = null;

        /***
         * This will expose the current controller metadata. Call it using ee()->meta();
         * @return ControllerMethodMeta
         */
        public function meta()
        {
            return $this->currentControllerMeta;
        }

        /***
         * This will expose the current controller instance. Call it using ee()->controller();
         * @return object
         */
        public function controller() {
            return $this->currentControllerInstance;
        }

        /**
         * Url query parser and executor.
         * @return string
         * @throws ResponseException
         * @throws Throwable
         */
        private function processArguments()
        {
            $start = time();
            $httpCode = 200;
            preg_match("/(?:\.php\/)(.*?)(?:\?|$)/", $_SERVER['REQUEST_URI'],
                $matches, PREG_OFFSET_CAPTURE);
            if (count($matches) > 1) {
                $access = explode('/', $matches[1][0]);

                if (strlen($access[0]) == 0) {
                    // if the controller/folder name is empty, redirect to empty uri handler.
                    header('Location: ' . substr($_SERVER['REQUEST_URI'], 0, strlen($_SERVER['REQUEST_URI']) - 1));
                    exit();
                }

                $method = "";
                $folderName = $this->getConfig()->getControllersLocation();
                $arguments = [];

                // Find and instantiate controller.
                if (count($access) > 0) {
                    $controllerFileName = $access[0];
                    $className = ucfirst($controllerFileName);
                    // Check if controller exists and load it.
                    if (file_exists($this->getController($controllerFileName))) {
                        include_once($this->getController($controllerFileName));
                        // check if method is defined
                        if (count($access) > 1) {
                            $method = $access[1];
                            $arguments = array_slice($access, 2);
                        }
                    } else {
                        if (count($access) > 1) {
                            $folderName = $controllerFileName;
                            $controllerFileName = $access[1];
                            $className = ucfirst($controllerFileName);
                            // Check if is folder, and load if controller found.
                            if (is_dir($this->getControllerFolder($folderName))) {
                                if (file_exists($this->getControllerFolder($folderName) . '/' . $controllerFileName . '.php')) {
                                    include_once($this->getControllerFolder($folderName) . '/' . $controllerFileName . '.php');
                                    // check if method is defined
                                    if (count($access) > 2) {
                                        $method = $access[2];
                                        $arguments = array_slice($access, 3);
                                    }
                                } else {
                                    // 404
                                    throw new ResponseException("Not found.", 404);
                                }
                            }
                        } else {
                            // 404
                            throw new ResponseException("Not found.", 404);
                        }
                    }
                } else {
                    // if the controller/folder name is not defined correctly
                    throw new ResponseException("Not found.", 404);
                }
                $this->currentControllerInstance = $this->injectDependenciesAndInstance($className);
                $isRestController = false;
                $allowedFilters = [];
                $ignoredFilters = [];
                if (property_exists($this->currentControllerInstance, '__allowedFilters')) {
                    $allowedFilters = $this->currentControllerInstance->__allowedFilters;
                }
                if (property_exists($this->currentControllerInstance, '__ignoredFilters')) {
                    $ignoredFilters = $this->currentControllerInstance->__ignoredFilters;
                }
                // Rest Controller Processing
                if (isset($this->currentControllerInstance) && $this->currentControllerInstance instanceof RestController) {
                    // Auto-connect to database if is RestController and auto-connection is enabled in config.
                    if ($this->getConfig()->isDbConnectionAuto()) {
                        $this->getConfig()->dbInit();
                    }
                    // if controller is Rest, execute directly depending on the method.
                    try {
                        // Extract Controller Meta
                        $this->currentControllerMeta = new ControllerMethodMeta($folderName, $className,
                            strtolower($_SERVER['REQUEST_METHOD']), $arguments, $allowedFilters, $ignoredFilters);
                        // Process Filters
                        $this->processRequestFilters($this->currentControllerMeta);
                        // Execute Method
                        $data = $this->currentControllerInstance->executeRest(array_slice($access, 1));
                        // Process Response Filters
                        $data = $this->processResponseFilters($this->currentControllerMeta, $data);
                    } catch (\Throwable $restException) {
                        if ($restException instanceof ResponseException) {
                            throw $restException;
                        }
                        throw new ResponseException($restException->getMessage(), 500, $restException);
                    }
                    $isRestController = true;
                } else {
                    // Non-rest Controller Processing
                    // if not, check if method is defined
                    if (isset($this->currentControllerInstance) && method_exists($this->currentControllerInstance, $method)) {
                        try {
                            // Extract Controller Meta
                            $this->currentControllerMeta = new ControllerMethodMeta($folderName,
                                $className, $method, $arguments, $allowedFilters, $ignoredFilters);
                            // Process Filters
                            $this->processRequestFilters($this->currentControllerMeta);
                            // Execute Raw Method
                            $data = call_user_func_array([$this->currentControllerInstance, $method], $arguments);
                            // }
                            // Process Response Filters
                            $data = $this->processResponseFilters($this->currentControllerMeta, $data);
                        } catch (\Throwable $methodException) {
                            if ($methodException instanceof ResponseException) {
                                throw $methodException;
                            }
                            throw new ResponseException($methodException->getMessage(), 500, $methodException);
                        }
                    } else {
                        // if method is empty, check for default method
                        if (strlen($method) == 0 && property_exists($this->currentControllerInstance, "__default")) {
                            $defaultMethod = $this->currentControllerInstance->__default;
                            if (is_string($defaultMethod)) {
                                if (method_exists($this->currentControllerInstance, explode('/', $defaultMethod)[0])) {
                                    // if default method exists, redirect
                                    $this->redirect($this->currentControllerInstance, $defaultMethod);
                                }
                            }
                        }
                        throw new ResponseException("Not found.", 404);
                    }
                }
                // Process DataClass Object.
                if (isset($data) && $data instanceof DataClass) {
                    $data = $data->expose();
                }
                $end = time();
                // Controller Method execution finished.
                // Finalize response processing
                if (isset($data)) {
                    if (is_array($data)) {
                        if (!isset($data['_useStandardResponse'])) {
                            // Not defined. For REST controllers is disabled but in standard controllers is enabled
                            // by default.
                            if ($isRestController) {
                                $data['_useStandardResponse'] = false;
                            } else {
                                $data['_useStandardResponse'] = true;
                            }
                        }
                        header('Content-type: application/json');
                        if ($data["_useStandardResponse"]) {
                            return json_encode(
                                (new StandardResponse($end - $start,
                                    $httpCode,
                                    $data,
                                    false,
                                    NULL,
                                    CoreX::$developmentMessages)
                                )->expose());
                        } else {
                            return json_encode($data);
                        }
                    } else {
                        // Return RAW if it is not safely serializable.
                        return $data;
                    }
                }

            } else {
                $this->redirectToDefault();
            }
            return null;
        }

        /***
         * This is the ExEngine Dependency Injector
         * Version: 1.0
         * @param $className
         * @param $root
         * @param $injectionStack
         * @return object
         * @throws \ReflectionException
         * @throws ResponseException
         */
        private function injectDependenciesAndInstance($className, $root = true, &$injectionStack = [])
        {
            $classReflection = new \ReflectionClass($className);
            $classConstructor = $classReflection->getConstructor();
            $constructorParams = [];
            if ($root) {
                $injectionStack = [];
            }
            if (!is_null($classConstructor)) {
                foreach ($classConstructor->getParameters() as $num => $parameter) {
                    $parameterType = $parameter->getType();
                    if (!is_null($parameterType)) {
                        // Inject Embedded Services
                        switch ($parameterType->getName()) {
                            case Request::class:
                                $constructorParams[$num] = new Request();
                                break;
                            case CoreX::class:
                                $constructorParams[$num] = self::getInstance();
                                break;
                            default:
                                // Inject Embedded-based Objects
                                if ($parameter->getClass()->getParentClass() &&
                                    $parameter->getClass()->getParentClass()->getName() == BaseConfig::class) {
                                    $constructorParams[$num] = $this->config;
                                    break;
                                }
                                // Inject User Services
                                if (in_array($parameterType->getName(), array_keys($this->getConfig()->getServices()), true)) {
                                    $service = $parameterType->getName();
                                    $singleton = $this->getConfig()->getServices()[$service]['singleton'];
                                    if ($className != $service) {
                                        if (in_array($service, $injectionStack)) {
                                            throw new ResponseException("Circular dependency injection detected " .
                                                "when trying to instantiate '$className'. Stack: " . print_r($injectionStack, true), 500);
                                        } else {
                                            $injectionStack[] = $className;
                                            if (!$singleton) {
                                                $constructorParams[$num] =
                                                    $this->injectDependenciesAndInstance($service, false, $injectionStack);
                                            } else {
                                                if (in_array($service, array_keys($this->singletonServiceInstances))) {
                                                    $constructorParams[$num] = $this->singletonServiceInstances[$service];
                                                } else {
                                                    $this->singletonServiceInstances[$service] =
                                                        $this->injectDependenciesAndInstance($service, true, $injectionStack);
                                                    $constructorParams[$num] = $this->singletonServiceInstances[$service];
                                                }
                                            }
                                        }
                                    } else {
                                        throw new ResponseException("Circular dependency injection detected " .
                                            "when trying to instantiate '$className'.", 500);
                                    }
                                } else {
                                    throw new ResponseException("Service '" . $parameterType->getName() . "' is not registered.", 500);
                                }
                                break;
                        }
                    }
                }
            }
            return $classReflection->newInstanceArgs($constructorParams);
        }

        /***
         * Redirects to a different controller's method.
         * @param string $controller
         * @param string $method
         * @param mixed ...$arguments
         * @throws ResponseException
         */
        function redirect($controller, $method, ...$arguments)
        {
            header('Location: ' . $this->link($controller, $method, ...$arguments));
            exit();
        }

        /***
         * Redirects to the default (if set) if not, will throw an exception.
         * @throws ResponseException
         */
        function redirectToDefault()
        {
            if (strlen($this->config->getDefaultStaticAppStart()) > 0) {
                header('Location: ' . $this->config->getDefaultStaticAppStart());
            } else if (strlen($this->config->getDefaultControllerFunction()) > 0) {
                header('Location: ' . $_SERVER['SCRIPT_NAME'] . '/' . $this->config->getDefaultControllerFunction());
            } else
                throw new ResponseException("Not found (No parameters|No default serving).", 404);
        }

        /**
         * Returns the root relative url to the defined controller's method.
         * @param $controller
         * @param $method
         * @param mixed ...$arguments
         * @return string
         * @throws ResponseException
         */
        function link($controller, $method, ...$arguments)
        {
            $methodArguments = '';
            if (count($arguments) > 0) {
                foreach ($arguments as $arg) {
                    $methodArguments .= '/' . $arg;
                }
            }
            if (is_string($controller))
                return $_SERVER['SCRIPT_NAME'] . '/' . $controller . '/' . $method . $methodArguments;
            elseif (is_object($controller) && $controller == $this->controller()) {
                $base = $this->meta()->getControllerLocation();
                $base = str_replace($this->getConfig()->getControllersLocation(), '', $base) . '/';
                $controllerName = get_class($controller);
                return $_SERVER['SCRIPT_NAME'] . $base . $controllerName . '/' . $method . $methodArguments;
            } else {
                throw new ResponseException(
                    "Invalid argument for this method. Please provide a string or a controller ".
                        "instance as argument.", 500);
            }
        }

        /**
         * CoreX constructor.
         * @param BaseConfig|string|null $baseConfigChildInstanceOrLauncherFolderPath
         * @throws \Exception
         */
        function __construct($baseConfigChildInstanceOrLauncherFolderPath = null)
        {
            if (CoreX::$instance != null) {
                throw new \Exception("CoreX is already instantiated, cannot instantiate twice. Please check.");
            }
            CoreX::$instance = &$this;
            if ($baseConfigChildInstanceOrLauncherFolderPath == null) {
                throw new \Exception('CoreX first parameter must be either a string containing the launcher ' .
                    'folder path or an instantiated BaseConfig child class. Example: new \ExEngine\CoreX(__DIR__);');
            }
            if ($baseConfigChildInstanceOrLauncherFolderPath instanceof BaseConfig) {
                $this->config = &$baseConfigChildInstanceOrLauncherFolderPath;
            } else {
                if (is_string($baseConfigChildInstanceOrLauncherFolderPath) && file_exists($baseConfigChildInstanceOrLauncherFolderPath) && is_dir($baseConfigChildInstanceOrLauncherFolderPath)) {
                    $this->config = new DefaultConfig($baseConfigChildInstanceOrLauncherFolderPath);
                } else {
                    throw new \Exception("If default config is being used, you must pass the launcher folder path. Example: new \ExEngine\CoreX(__DIR__);");
                }
            }
            if (strlen($this->config->getDefaultControllerFunction()) > 0 &&
                strlen($this->config->getDefaultStaticAppStart()) > 0) {
                throw new \Exception("Default serving mode must be set to Static App bootstrap or Controller Function but not both at the same time.");
            }
            if ($this->config->isShowHeaderBanner()) {
                if (!$this->config->isProduction()) {
                    header("Y-Powered-By: ExEngine - Development Mode");
                } else {
                    header("Y-Powered-By: ExEngine");
                }
            }
            if (strlen($this->config->getLauncherFolderPath()) == 0) {
                throw new \Exception("Launcher folder path must be passed in the Config constructor. If overriden, parent constructor must be called.");
            } else {
                if (!file_exists($this->config->getLauncherFolderPath()) || !is_dir($this->config->getLauncherFolderPath())) {
                    throw new \Exception("Launcher folder path is invalid or does not exists. Please use PHP's constant `__DIR__` from the instance launcher.");
                }
            }
            try {
                $this->instantiateFilters();
                print $this->processArguments();
            } catch (\Throwable $exception) {
                $trace = $this->getConfig()->isShowStackTrace() ? $exception->getTrace() : null;
                $resp = new StandardResponse(0, $exception->getCode(), null, true, new ErrorDetail($trace, $exception->getMessage()));
                http_response_code($exception->getCode());
                header('Content-type: application/json');
                print json_encode($resp->expose(), $this->usePrettyPrint());
            }
        }
    }
}

namespace {
    const EXENGINE_RELEASE = '1.1.0';
    const EXENGINE_API_LEVEL = 2;
    /**
     * Global shortcut for \ExEngine\CoreX::getInstance();
     *
     * @return \ExEngine\CoreX
     */
    function ee()
    {
        return \ExEngine\CoreX::getInstance();
    }
}