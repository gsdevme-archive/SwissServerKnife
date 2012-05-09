<?php

	// Built: May 9, 2012, 1:59 pm

	// File: /Users/gav/Development/Web Development/SwissServerKnife/index.php


	namespace {

		// HTTP Auth Password
		$password = 'a51787092a78e0dd7a04c1f42bdd051ac9ee5beebb609fea8f5f6c2af9b4a63bcffde849ba337344e1675889b3640ace583ca2f4f7942fdfe4d8f8f2ae368375';

		// Quicky get our Vendor exception
		class SSKException extends \Exception{}

		// Real programming erors
		set_error_handler(function($errno, $errstr, $errfile, $errline ) {
			throw new \ErrorException($errstr, 0, $errno, $errfile, $errline);
		});

		if(PHP_VERSION_ID < '50303'){
			throw new SSKException('PHP version is too low :(');
		}

		$root = realpath(dirname(__FILE__)) . '/';

		spl_autoload_register(function($class) use ($root){
			$file = $root . str_replace('\\', '/', $class) . '.php';

			if(file_exists($file)){
				return require_once $file;
			}
		});	

		try{
			$ssk = new \SSK\SwissServerKnife($root, $password);			
			$ssk->authenicate()->request()->route();

			unset($root, $password);
		}catch(\Exception $e){
			echo '<pre>' . print_r($e, true) . '</pre>';
		}

	}

	// File: /Users/gav/Development/Web Development/SwissServerKnife/SSK/Controllers/Controller.php


	namespace SSK\Controllers{

		abstract class Controller
		{

			public function __construct()
			{
				
			}
		}
	}

	

	// File: /Users/gav/Development/Web Development/SwissServerKnife/SSK/Controllers/Home.php


	namespace SSK\Controllers{

		use \SSK\System\ViewRender;

		class Home extends Controller
		{

			public function index()
			{
				$a = array(
					'pagetitle' => 'hello',
					'piss' => array(
						array('title' => 'An amazing title', 'body' => 'some body text and stuff'),
						array('title' => 'Another title', 'body' => 'foobar sticks'),
					),

					'd' => range(1,3),
				);

				ViewRender::render('test', $a);				
			}
		}
	}

	// File: /Users/gav/Development/Web Development/SwissServerKnife/SSK/SwissServerKnife.php


	namespace SSK{

		use \SSK\System\ViewRender;
		use \ReflectionException;
		use \ReflectionMethod;
		use \ReflectionClass;

		class SwissServerKnife
		{
			
			private $_password, $_request;
			private static $_root;

			public function __construct($root, $password)
			{
				self::$_root = $root;
				$this->_password = $password;				
			}

			public function authenicate()
			{
				if (isset($_SERVER['PHP_AUTH_USER'], $_SERVER['PHP_AUTH_PW'])) {
					if(($_SERVER['PHP_AUTH_USER'] == 'ssk') && (hash('sha512', strrev($_SERVER['PHP_AUTH_PW']) . 'my_amazing_salt_which_helps_stop_rainbow_attacks') == $this->_password)){
						return $this;
					}    
				}

				header('WWW-Authenticate: Basic realm="Secure Realm"');
				header('HTTP/1.0 401 Unauthorized');
				die('Authenication failed :(');	
			}

			public function request()
			{
				if(isset($_SERVER, $_SERVER['REQUEST_URI'], $_SERVER['SCRIPT_NAME'])){
					$request = substr(str_replace($_SERVER['SCRIPT_NAME'], null, $_SERVER['REQUEST_URI']), 1);

					if(!$request){
						$request = 'home/index';
					}

					$this->_request = $request;
				}

				return $this;
			}

			public function route()
			{
				$route = explode('/', $this->_request);

				try{
					$controllerClass = new ReflectionClass('\SSK\Controllers\\' . array_shift($route));
					$controllerMethod = new ReflectionMethod($controllerClass->name, array_shift($route));

					$controllerMethod->invoke($controllerClass->newInstance());
				}catch(ReflectionException $e){

				}

				return $this;
			}

			public static function getRoot()
			{
				return self::$_root;	
			}

		}

	}

	// File: /Users/gav/Development/Web Development/SwissServerKnife/SSK/System/ViewFactoryAbstract.php


	namespace SSK\System{

		use \SSK\SwissServerKnife;

		abstract class ViewFactoryAbstract
		{

			public static function get($view)
			{
				$file = SwissServerKnife::getRoot() . 'SSK/Views/' . $view . '.html';

				if(file_exists($file)){
					return file_get_contents($file);
				}

				if(isset(static::$$view)){
					return static::$$view;
				}

				return (bool)false;
			}
		}
	}

	// File: /Users/gav/Development/Web Development/SwissServerKnife/SSK/System/ViewRender.php


	namespace SSK\System{

		class ViewRender
		{

			private static $_args;

			public static function render($view, array $args=null)
			{
				self::$_args = $args;

				$view = ViewFactory::get($view);

				$view = preg_replace_callback('/{foreach ([A-Z0-9]+) as ([A-Z0-9]+)}(.*?){foreach}/sim', 'self::_foreach', $view);
				$view = preg_replace_callback('/{([A-Z0-9]+)}/i', 'self::_print', $view);

				ob_start(function($buffer){
					// This regex was taken from http://stackoverflow.com/questions/5312349/minifying-final-html-output-using-regular-expressions-with-codeigniter
					return preg_replace('/<!--(.*?)-->/', null, preg_replace('#(?ix)(?>[^\S ]\s*|\s{2,})(?=(?:(?:[^<]++|<(?!/?(?:textarea|pre)\b))*+)(?:<(?>textarea|pre)\b|\z))#', null, $buffer));
				});

				echo $view;
			}

			private static function _foreach($value, $return=null)
			{
				if(isset($value[1], $value[2], $value[3])){

					// lets create a copy of the template for each iteration
					$template = $value[3];

					if(isset(self::$_args[$value[1]])) {

						foreach(self::$_args[$value[1]] as $data){
							// Copy for this element
							$element = $template;

							if((is_object($data)) || (is_array($data))){

								foreach($data as $key => $item){
									// apply replaces for this element
									$element = str_replace('{' . $value[2] . '.' . $key . '}', self::_xss($item), $element);
								}
							}else{
								$element = str_replace('{' . $value[2] . '}', self::_xss($data), $element);
							}

							// assign elements copy into the return
							$return .= $element;
						}
					}

					return $return;
				}

				return null;
			}

			private static function _print($value)
			{
				if(isset(self::$_args[$value[1]])){
					return self::_xss(self::$_args[$value[1]]);
				}

				return null;				
			}	

			private static function _xss($value)
			{
				return htmlspecialchars(htmlentities(trim(((string)$value)), ENT_QUOTES, 'UTF-8', false), ENT_QUOTES, 'UTF-8', false);
			}	
		}
	}

	namespace SSK\System{

		class ViewFactory extends ViewFactoryAbstract{

			protected static $foo="<html><head><title>gg</title></head><body></body></html>";
			protected static $test="<!doctype html><html><head><title>Hello!</title><style>.shit{width:800px;margin:20px auto;}</style></head><body><h1 class=\"shit\">{pagetitle}</h1>{foreach piss as item}<div class=\"shit\"><h3>{item.title}</h3><p>{item.body}</p></div>{foreach}{foreach d as h}<div class=\"shit\"><h3>{h}</h3></div>{foreach}</body></html>";
		}

	}