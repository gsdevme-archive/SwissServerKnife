<?php

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