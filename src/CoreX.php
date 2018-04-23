<?php
/* PHP Version Check */
namespace {
    if (version_compare(PHP_VERSION, '7.1.0', '<')) {
        print '<h1>ExEngine</h1><p>ExEngine requires PHP 7.1 or higher, please update your installation.</p>';
        exit();
    }
}
/**
 * ExEngine namespace.
 */
namespace ExEngine {

    use Throwable;

    class Rest {
        final function executeRest(array $argument_array) {
            //$args = func_get_args();
            if (method_exists($this,strtolower($_SERVER['REQUEST_METHOD']))) {
                return call_user_func_array([$this, strtolower($_SERVER['REQUEST_METHOD'])], $argument_array);
            } else {
                throw new ResponseException("REST Method (".strtolower($_SERVER['REQUEST_METHOD']).") is not defined.", 404);
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
         * This variable contains the DataClass configuration, should be set it in the child constructor.
         * @var null|\ExEngine\DataClassLocalConfig
         */
        protected $dcConfiguration = null;

        /**
         * This function converts all properties into a serializable array.
         * @return array
         */
        final public function expose(): array
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
     * This class is a simple extension to PHP's Exception class.
     * @package ExEngine
     */
    class ResponseException extends \Exception {
        public function __construct(string $message = "", int $code = 0, Throwable $previous = null)
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
            bool $suppressNulls = null
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

        /* getters */
        public function isSuppressNulls()
        {
            return $this->suppressNulls;
        }

        public function getControllersLocation()
        {
            return $this->controllersLocation;
        }

        public function isUsePrettyPrint()
        {
            return $this->usePrettyPrint;
        }

        public function getShowVersionInfo()
        {
            return $this->showVersionInfo;
        }

        public function getSessionConfig(): BaseSessionConfig
        {
            return $this->sessionConfig;
        }

        public function isShowStackTrace() {
            return $this->showStackTrace;
        }

        public function isShowHeaderBanner() {
            return $this->showHeaderBanner;
        }

        /* setters */

        /* default overridables */

        /**
         * Default overridable method for defining a database connection.
         */
        public function dbInit() {}
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
            string $message
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

        function __construct(
            int $took,
            int $code,
            array $data = NULL,
            bool $error = false,
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

        public static function getInstance(): CoreX
        {
            return self::$instance;
        }

        private $config = null;

        public function getConfig(): BaseConfig
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

        private function getController(string $ControllerFilePath) {
            return $this->getConfig()->getControllersLocation() . '/' . $ControllerFilePath . '.php';
        }

        private function getControllerFolder(string $ControllerFolder) {
            return $this->getConfig()->getControllersLocation() . '/' . $ControllerFolder;
        }

        /**
         * Url query parser and executor.
         * @return string
         * @throws ResponseException
         */
        private function processArguments(): string
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

                if ($classObj instanceof Rest) {
                    // if controller is Rest, execute directly depending on the method.
                    try {
                        $data = $classObj->executeRest(array_slice($access,1));
                    } catch (\Throwable $restException) {
                        throw new ResponseException($restException->getMessage(), 500, $restException);
                    }
                } else {
                    // if not, check if method is defined
                    if (method_exists($classObj, $method)) {
                        try {
                            $data = call_user_func_array([$classObj, $method], $arguments);
                        } catch (\Throwable $methodException) {
                            throw new ResponseException($methodException->getMessage(), 500, $methodException);
                        }
                    } else {
                        // if method does not exist, return not found
                        throw new ResponseException("Not found.", 500);
                    }
                }

                if ($data instanceof DataClass) {
                    $data = $data->expose();
                }

                $end = time();

                if (is_array($data)) {
                    return json_encode((new StandardResponse($end - $start, $httpCode, $data))->expose());
                } else {
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
                header("Y-Powered-By: ExEngine");
            try {
                print $this->processArguments();
            } catch (\Throwable $exception) {
                $trace = $this->getConfig()->isShowStackTrace() ? $exception->getTrace() : null;
                $resp = new StandardResponse(0, $exception->getCode(), null, true, new ErrorDetail($trace, $exception->getMessage()));
                http_response_code($exception->getCode());
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
    function ee(): \ExEngine\CoreX
    {
        return \ExEngine\CoreX::getInstance();
    }
}