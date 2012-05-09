<?php

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