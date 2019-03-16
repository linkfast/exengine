<?php namespace{if(version_compare(php_version,'5.6.0','<')){print '<h1>ExEngine</h1><p>ExEngine requires PHP 5.6 or higher, please update your installation.</p>';exit();}include_once 'Classes/BaseConfig/BaseConfig.php';}namespace exengine{use throwable;class rest{final function executerest(array $argument_array){$request_method='get';if(isset($_server['REQUEST_METHOD'])){$request_method=strtolower($_server['REQUEST_METHOD']);}if(method_exists($this,$request_method)){return call_user_func_array([$this,$request_method],$argument_array);}else{throw new responseexception("REST Method (".$request_method.") is not defined.",404);}}}abstract class dataclass{protected $dcconfiguration=null;final public function expose(){if((\ee()->getconfig()->issuppressnulls()&&$this->dcconfiguration==null)||($this->dcconfiguration!=null&&$this->dcconfiguration->issupressnulls())){return array_filter(get_object_vars($this),function($v){if($v===$this->dcconfiguration){return false;}return $v!==null;});}else{return array_filter(get_object_vars($this),function($v){if($v===$this->dcconfiguration){return false;}return true;});}}}class responseexception extends \exception{public function __construct($message="",$code=0,throwable $previous=null){parent::__construct($message,$code,$previous);}}class dataclasslocalconfig{protected $supressnulls=false;public final function __construct($suppressnulls=null){if($suppressnulls===null){$this->supressnulls=\ee()->getconfig()->issuppressnulls();}else{$this->supressnulls=$suppressnulls;}}public final function issupressnulls(){return $this->supressnulls;}}abstract class baseconfig{protected $controllerslocation="_";protected $useprettyprint=true;protected $showversioninfo="MINIMAL";protected $suppressnulls=true;protected $showstacktrace=true;protected $showheaderbanner=true;protected $dbconnectionauto=false;public function issuppressnulls(){return $this->suppressnulls;}public function getcontrollerslocation(){return $this->controllerslocation;}public function isuseprettyprint(){return $this->useprettyprint;}public function getshowversioninfo(){return $this->showversioninfo;}public function getsessionconfig(){return $this->sessionconfig;}public function isshowstacktrace(){return $this->showstacktrace;}public function isshowheaderbanner(){return $this->showheaderbanner;}public function isdbconnectionauto(){return $this->dbconnectionauto;}public function dbinit(){if(class_exists("\\R")){\r::setup();}}}class defaultconfig extends baseconfig{}class errordetail extends dataclass{protected $stacktrace=[];protected $message="";function __construct(array $stacktrace=null,$message){$this->stacktrace=$stacktrace;$this->message=$message;}}class standardresponse extends dataclass{protected $took=0;protected $code=200;protected $data=null;protected $error=false;protected $errordetails=null;function __construct($took,$code,array $data=null,$error=false,errordetail $errordetails=null){$this->code=$code;$this->took=$took;$this->data=$data;$this->error=$error;if($error!=false){$this->errordetails=$errordetails->expose();}}}class corex{private static $instance=null;public static function getinstance(){return self::$instance;}private $config=null;public function getconfig(){return $this->config;}private function useprettyprint(){if($this->getconfig()->isuseprettyprint()){return json_pretty_print;}return null;}private function getcontroller($controllerfilepath){return $this->getconfig()->getcontrollerslocation().'/'.$controllerfilepath.'.php';}private function getcontrollerfolder($controllerfolder){return $this->getconfig()->getcontrollerslocation().'/'.$controllerfolder;}private function processarguments(){$start=time();$requri=$_server['REQUEST_URI'];$httpcode=200;$method=$_server['REQUEST_METHOD'];preg_match("/(?:\.php\/)(.*?)(?:\?|$)/",$requri,$matches,preg_offset_capture);if(count($matches)>1){$access=explode('/',$matches[1][0]);if(strlen($access[0])==0){throw new responseexception("Not found.",404);}$method="";$arguments=[];if(count($access)>0){$fpart=$access[0];$uc_fpart=ucfirst($fpart);if(file_exists($this->getcontroller($fpart))){include_once($this->getcontroller($fpart));$classobj=new $uc_fpart();if(count($access)>1){$method=$access[1];$arguments=array_slice($access,2);}}else{if(count($access)>1){$spart=$access[1];$uc_spart=ucfirst($spart);if(is_dir($this->getcontrollerfolder($fpart))){if(file_exists($this->getcontrollerfolder($fpart).'/'.$spart.'.php')){include_once($this->getcontrollerfolder($fpart).'/'.$spart.'.php');$classobj=new $uc_spart();if(count($access)>2){$method=$access[2];$arguments=array_slice($access,3);}}else{throw new responseexception("Not found.",404);}}}else{throw new responseexception("Not found.",404);}}}else{throw new responseexception("Not found.",404);}if(isset($classobj)&&$classobj instanceof rest){if($this->getconfig()->isdbconnectionauto()){$this->getconfig()->dbinit();}try{$data=$classobj->executerest(array_slice($access,1));}catch(\throwable $restexception){throw new responseexception($restexception->getmessage(),500,$restexception);}}else{if(isset($classobj)&&method_exists($classobj,$method)){try{$data=call_user_func_array([$classobj,$method],$arguments);}catch(\throwable $methodexception){throw new responseexception($methodexception->getmessage(),500,$methodexception);}}else{throw new responseexception("Not found.",404);}}if(isset($data)&&$data instanceof dataclass){$data=$data->expose();}$end=time();if(isset($data)&&is_array($data)){header('Content-type: application/json');return json_encode((new standardresponse($end-$start,$httpcode,$data))->expose());}else{if(isset($data))return $data;}}else{throw new responseexception("Not found.",404);}}function __construct(baseconfig $config=null){corex::$instance=$this;if($config!=null&&$config instanceof baseconfig){$this->config=&$config;}else{$this->config=new defaultconfig();}if($this->config->isshowheaderbanner())header("X-Powered-By: ExEngine");try{print $this->processarguments();}catch(\throwable $exception){$trace=$this->getconfig()->isshowstacktrace()?$exception->gettrace():null;$resp=new standardresponse(0,$exception->getcode(),null,true,new errordetail($trace,$exception->getmessage()));http_response_code($exception->getcode());header('Content-type: application/json');print json_encode($resp->expose(),$this->useprettyprint());}}}}namespace{function ee(){return \exengine\corex::getinstance();}}