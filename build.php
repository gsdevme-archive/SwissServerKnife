<?php

	$root = realpath(dirname(__FILE__)) . '/';

	$it = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($root, FilesystemIterator::SKIP_DOTS));
	$build = new SplFileObject($root . 'built-ssk.php', 'w');

	$build->fwrite("<?php\n\n");
	$build->fwrite("\t// Built: " . date('F j, Y, g:i a'));

	for($it;$it->valid();$it->next()) {

		if(($it->getExtension() == 'php') && ($it->getFileName() != 'built-ssk.php') && ($it->getFileName() != 'build.php') && ($it->getFileName() != 'ViewFactory.php')){
			$build->fwrite("\n\n\t// File: " . (string)$it->current() . "\n\n");
			$file = new SplFileObject((string)$it->current(), 'r');

			/**
			 * Had some issues with fseek, and other methods to skip over
			 * line 1 so added this pretty nasty hack...
			 * 
			 * maybe a mac only issue?
			 */
			$lineCheck = (bool)true;

			for($file;$file->valid();$file->next()) {
				if($lineCheck === true){
					$lineCheck = $file->current();					
				}else{
					$build->fwrite((string)$file->current());
				}
			}	
		}

		unset($file, $lineCheck);
	}

	unset($it, $file, $lineCheck);

	/**
	 * This wraps up all the HTML views into static properties within ViewFactory...
	 * 
	 * an interesting solution i thought apart from the massive properties
	 * which are likely to increase memory usage highly
	 */
	$it = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($root . 'SSK/Views/', FilesystemIterator::SKIP_DOTS));

	// Abit of formatting
	$build->fwrite("\n\n\t" . 'namespace SSK\System{' . "\n\n");
	$build->fwrite("\t\t" . 'class ViewFactory extends ViewFactoryAbstract{' . "\n\n");

	for($it;$it->valid();$it->next()) {

		if(($it->getExtension() == 'html')){
			$html = preg_replace('/\r\n+|\r+|\n+|\t+/i', null, file_get_contents((string)$it->current()));			
			$build->fwrite("\t\t\t" . 'protected static $' . str_replace('.html', null, $it->getFileName()) . '="' . addcslashes($html, '"$') . '";' . "\n");
			
		}
	}

	$build->fwrite("\t\t" . '}');
	$build->fwrite("\n\n\t" . '}');