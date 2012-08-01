<?php

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