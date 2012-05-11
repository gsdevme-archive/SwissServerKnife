<?php

	namespace SSK\Controllers{

		class Server extends Controller
		{

			public function phpinfo()
			{
				$a = array(
					'title' => 'hello',
				);

				$this->render('phpinfo', $a);				
			}

			public function phpinforender()
			{
				ob_start(function($buffer){
					$buffer = str_replace('<table border="0" cellpadding="3" width="600">', '<table class="table table-striped">', preg_replace('/<style type="text\/css">(.*?)<\/style>/sim', null, $buffer));

					return preg_replace('/<td class="v">(.*?)<\/td>/sim', '<td class="v"><pre>$1</pre></td>', $buffer);
				});

				phpinfo();
				exit;
			}
		}
	}