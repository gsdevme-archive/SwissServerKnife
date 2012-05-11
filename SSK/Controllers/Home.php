<?php

	namespace SSK\Controllers{

		class Home extends Controller
		{

			public function index()
			{
				$a = array(
					'title' => 'hello',
				);

				$this->render('index', $a);				
			}
		}
	}