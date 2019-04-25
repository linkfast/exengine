<?php
/* PHP Version Check */
namespace {
    if (version_compare(PHP_VERSION, '5.6.0', '<')) {
        print '<h1>ExEngine</h1><p>ExEngine requires PHP 5.6 or higher, please update your installation.</p>';
        exit();
    }
}
/**
 * ExEngine namespace.
 */
namespace ExEngine {

    use Throwable;

    class Rest {
        /**
         * @param array $argument_array
         * @return mixed
         * @throws ResponseException
         */
        final function executeRest(array $argument_array) {
            $request_method = 'get';
            if (isset($_SERVER['REQUEST_METHOD'])) {
                $request_method = strtolower($_SERVER['REQUEST_METHOD']);
            }
            if (method_exists($this, $request_method)) {
                return call_user_func_array([$this, $request_method], $argument_array);
            } else {
                throw new ResponseException("REST Method (".$request_method.") is not defined.", 404);
            }
        }
    }

    /**
     * Class DataClass
     * This class is supposed to be used as a parent of any object returning function.
     * ExEngine Core will automatically parse the properties as a json object.
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
    class ResponseException extends \Exception {
        public function __construct($message = "",$code = 0, Throwable $previous = null)
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

        public final function isSupressNulls() {
            return $this->supressNulls;
        }
    }

    abstract class BaseConfig
    {
        /* config default values */
        protected $controllersLocation = "_";
        protected $usePrettyPrint = true;
        protected $showVersionInfo = "MINIMAL";
        protected $suppressNulls = true;
        protected $showStackTrace = true;
        protected $showHeaderBanner = true;
        protected $dbConnectionAuto = false;
        protected $launcherFolderPath = "";
        /* getters */
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
        /* setters */
        /* default overridables */
        /**
         * Default overridable method for defining a database connection. Do not call parent::dbInit();
         */
        public function dbInit()
        {
            // RedBeanPHP Classic version
            if (class_exists("\\R")) {
                \R::setup();
                // RedBeanPHP Composer version uses PSR-4
            } else if (class_exists("\\RedBeanPHP\\R")) {
                \RedBeanPHP\R::setup();
                // POMM
            } else if (class_exists('\PommProject\Foundation\Pomm')) {
                if (file_exists($this->launcherFolderPath . '/.pomm_cli_bootstrap.php')) {
                    $pomm = require $this->launcherFolderPath . '/.pomm_cli_bootstrap.php';
                    if (sizeof($pomm->getSessionBuilders()) == 0) {
                        throw new ResponseException("POMM configuration file found, add a connection or override config::dbInit() or uninstall.", 500);
                    }
                    return $pomm;
                } else {
                    throw new ResponseException("POMM found, please configure or override config::dbInit() or uninstall.", 500);
                }
            };
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

    class StandardResponse extends DataClass
    {
        protected $took = 0;
        protected $code = 200;
        protected $data = null;
        protected $error = false;
        protected $errorDetails = null;

        /**
         * StandardResponse constructor.
         * @param int $took
         * @param int $code
         * @param array|NULL $data
         * @param bool $error
         * @param ErrorDetail|NULL $errorDetails
         */
        function __construct(
            $took,
            $code,
            array $data = NULL,
            $error = false,
            ErrorDetail $errorDetails = NULL
        )
        {
            $this->code = $code;
            $this->took = $took;
            $this->data = $data;
            $this->error = $error;
            if ($error != false) {
                $this->errorDetails = $errorDetails->expose();
            }
        }
    }

    class CoreX
    {
        private static $instance = null;

        /**
         * @return CoreX
         */
        public static function getInstance()
        {
            return self::$instance;
        }

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
        private function getController($ControllerFilePath) {
            return $this->getConfig()->getControllersLocation() . '/' . $ControllerFilePath . '.php';
        }

        /**
         * @param string $ControllerFolder
         * @return string
         */
        private function getControllerFolder($ControllerFolder) {
            return $this->getConfig()->getControllersLocation() . '/' . $ControllerFolder;
        }

        /**
         * Url query parser and executor.
         * @return string
         * @throws ResponseException
         */
        private function processArguments()
        {

            $start = time();
            $reqUri = $_SERVER['REQUEST_URI'];
            $httpCode = 200;
            $method = $_SERVER['REQUEST_METHOD'];
            //error_log('method: ' . $method);

            preg_match("/(?:\.php\/)(.*?)(?:\?|$)/", $reqUri, $matches, PREG_OFFSET_CAPTURE);
            //print_r($matches);
            if (count($matches) > 1 ) {
                $access = explode('/', $matches[1][0]);

                if (strlen($access[0]) == 0) {
                    // if the controller/folder name is empty
                    throw new ResponseException("Not found.", 404);
                }

                $method = "";
                $arguments = [];

                // Find and instantiate controller.
                if (count($access) > 0) {
                    $fpart = $access[0];
                    $uc_fpart =  ucfirst($fpart);
                    // Check if controller exists and load it.
                    if (file_exists($this->getController($fpart))) {
                        include_once ($this->getController($fpart));
                        $classObj = new $uc_fpart();
                        // check if method is defined
                        if (count($access) > 1) {
                            $method = $access[1];
                            $arguments = array_slice($access, 2);
                        }
                    } else {
                        if (count($access) > 1) {
                            $spart = $access[1];
                            $uc_spart = ucfirst($spart);
                            // Check if is folder, and load if controller found.
                            if (is_dir($this->getControllerFolder($fpart))) {
                                if (file_exists($this->getControllerFolder($fpart) . '/' . $spart . '.php')) {
                                    include_once ($this->getControllerFolder($fpart) . '/' . $spart . '.php');
                                    $classObj = new $uc_spart();

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

                if (isset($classObj) && $classObj instanceof Rest) {
                    // connect to database if autoconnection is enabled
                    if ($this->getConfig()->isDbConnectionAuto()) {
                        $this->getConfig()->dbInit();
                    }
                    // if controller is Rest, execute directly depending on the method.
                    try {
                        $data = $classObj->executeRest(array_slice($access,1));
                    } catch (\Throwable $restException) {
                        throw new ResponseException($restException->getMessage(), 500, $restException);
                    }
                } else {
                    // if not, check if method is defined
                    if (isset($classObj) && method_exists($classObj, $method)) {
                        try {
                            $data = call_user_func_array([$classObj, $method], $arguments);
                        } catch (\Throwable $methodException) {
                            throw new ResponseException($methodException->getMessage(), 500, $methodException);
                        }
                    } else {
                        // if method does not exist, return not found
                        throw new ResponseException("Not found.", 404);
                    }
                }

                if (isset($data) && $data instanceof DataClass) {
                    $data = $data->expose();
                }

                $end = time();

                if (isset($data) && is_array($data)) {
                    header('Content-type: application/json');
                    return json_encode((new StandardResponse($end - $start, $httpCode, $data))->expose());
                } else {
                    if (isset($data))
                        return $data;
                }
            } else {
                throw new ResponseException("Not found.", 404);
            }
        }

        /**
         * ExEngine Core X constructor.
         * @param BaseConfig|null $config
         */
        function __construct(BaseConfig $config = null)
        {
            CoreX::$instance = $this;
            if ($config != null && $config instanceof BaseConfig) {
                $this->config = &$config;
            } else {
                $this->config = new DefaultConfig();
            }
            if ($this->config->isShowHeaderBanner())
                header("X-Powered-By: ExEngine");

            if (strlen($this->config->getLauncherFolderPath()) == 0) {
                throw new \Exception("Launcher folder path must be passed in the Config constructor. If overriden, parent constructor must be called.");
            }

            try {
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