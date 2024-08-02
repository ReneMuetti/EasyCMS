<?php
/**
* Class to handle shutdown
*/
class Shutdown
{
	var $shutdown = array();

	/**
	* Constructor. Empty.
	*/
	function Shutdown()
	{
	}

	/**
	* Singleton emulation - use this function to instantiate the class
	*
	* @return	Shutdown
	*/
	function &init()
	{
		static $instance;

		if (!$instance)
		{
			$instance = new Shutdown();
			// we register this but it might not be used
			if (phpversion() < '5.0.5')
			{
				register_shutdown_function(array(&$instance, '__destruct'));
			}
		}

		return $instance;
	}

	/**
	* Add function to be executed at shutdown
	*
	* @param	string	Name of function to be executed on shutdown
	*/
	function add($function)
	{
		$obj =& Shutdown::init();
		if (function_exists($function) AND !in_array($function, $obj->shutdown))
		{
			$obj->shutdown[] = $function;
		}
	}

	// only called when an object is destroyed, so $this is appropriate
	function __destruct()
	{
		if (!empty($this->shutdown))
		{
			foreach ($this->shutdown AS $key => $funcname)
			{
				$funcname();
				unset($this->shutdown[$key]);
			}
		}
	}

	// called if unserialized
	function __wakeup()
	{
		$this->shutdown = array();
	}
}
?>