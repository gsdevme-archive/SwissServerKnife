<?php

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