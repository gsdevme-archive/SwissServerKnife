<?php

	namespace SSK\Controllers{

		use \SSK\SwissServerKnife;
		use \SSK\System\ViewRender;

		abstract class Controller
		{

			protected $url, $css, $js, $img;

			public function __construct()
			{
				$this->url = (((isset($_SERVER['HTTPS'])) && (!empty($_SERVER['HTTPS']))) ? 'https://' : 'http://') . $_SERVER['HTTP_HOST'] . '/';

				// Check if we have any assets, if not load from the CDN
				if(is_dir(SwissServerKnife::getRoot() . '/assets')){
					$this->css = $this->url . 'assets/css/';
					$this->js = $this->url . 'assets/js/';
					$this->img = $this->url . 'assets/img/';
				}else{
					die('no CDN');
				}

				$this->url .= $_SERVER['SCRIPT_NAME'] . '/';
			}

			protected function render($view, array $args=null)
			{
				$args = array_merge(array(
					'url' => $this->url,
					'css' => $this->css,
					'js' => $this->js,
					'img' => $this->img,
				), (array)$args);

				return ViewRender::render($view, $args);
			}
		}
	}

	