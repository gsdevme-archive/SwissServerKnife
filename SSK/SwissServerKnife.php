<?php

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

					// Remove '/' Prefix
					if (substr($request, 0, 1) == '/') {
						$request = substr($request, 1);
					}

					// Remove '/' Suffix
					if (substr($request, strlen($request) - 1, 1) == '/') {
						$request = substr($request, 0, strlen($request) - 1);
					}

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
					$controllerClass = new ReflectionClass('\SSK\Controllers\\' . ucfirst(array_shift($route)));
					$controllerMethod = new ReflectionMethod($controllerClass->name, array_shift($route));

					if(($controllerMethod->isPublic() && (!$controllerMethod->isConstructor()))){
						$controllerMethod->invoke($controllerClass->newInstance());
					}
				}catch(ReflectionException $e){
					throw $e;
				}

				return $this;
			}

			public static function getRoot()
			{
				return self::$_root;
			}

		}

	}