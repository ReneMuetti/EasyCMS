<?php
function WebsiteAutoLoader($class_name)
{
	if ( !class_exists('Autoloader', false) ) {
		$fullPathAutoloader = realpath( './include/classes/Core/class_Autoloader.php' );

		if ( is_file($fullPathAutoloader) ) {
			require_once $fullPathAutoloader;
			Autoloader::start();

			//Autoloader::getDebugOutput( implode( "\n", Autoloader::getLoadingDirs() ) );
		}
		else {
			echo "Fail to load class_Autoloader!";
		}
	}

	Autoloader::loadClass($class_name);
}

spl_autoload_register('WebsiteAutoLoader');