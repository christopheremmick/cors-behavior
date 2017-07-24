<?php

/*
 * CorsBehavior class file
 * 
 * @author Igor Manturov, Jr. <im@youtu.me>
 * @link https://github.com/iAchilles/cors-behavior
 */

/**
 * CorsBehavior Automatically adds the Access-Control-Allow-Origin response 
 * header for specific routes.
 * 
 * @version 2.0
 *
 */
class CorsBehavior extends CBehavior
{
    
    private $_settings = null;
    
    
    public function events()
    {
        return array_merge(parent::events(), 
                array('onBeginRequest' => 'onBeginRequestHandler'));
    }
    
    
    public function onBeginRequestHandler($event)
    {
        if (is_null($this->_settings))
        {
            return;
        }

	    $origin = $this->parseHeaders();
		if ($origin !== false) {
			foreach ($this->_settings as $setting) {
				if ( $this->checkAllowedRoute($setting['route']) && $this->checkOrigin($origin, $setting['allowedOrigin']))
				{
					$this->setAllowOriginHeader($origin);
					return;
				}
			}
		}
        // if we reach this point, we set the header to a blank value, which tells the client this origin is not allowed to access the requested route
	    $this->setAllowOriginHeader("");

    }


	/**
	 * Sets a many-to-many relationship between routes and allowed origins.
	 * @param mixed settings An unindexed array of settings in the following format
	 *  [
	 *      'route' => array of route(s) or the string "*" (for all routes)
	 *      'allowedOrigin' => array of origin(s)
	 *  ]
	 * @throws CException
	 */

	public function setSettings($settings) {
		if (!is_array($settings)) {
			throw new CException('Invalid CORS settings - should be an array of settings');
		}

		foreach ($settings as $s) {
			$this->validateRoute($s['route']);
			$this->validateAllowedOrigin($s['allowedOrigin']);
		}

		$this->_settings = $settings;
	}

    /**
     * check list of routes for CORS-requests.
     * @param mixed $route An array of routes (controllerID/actionID). If you 
     * want to allow CORS-requests for any routes, the value of the parameter
     * must be a string that contains the "*". 
     * @throws CException
     */
    public function validateRoute($route)
    {
		if (!is_array($route) && $route !== '*')
        {
            throw new CException('The value of the "route" property in settings must be an '
                    . 'array or a string that contains the "*".');
        }

    }
    
    
    /**
     * checks the allowed origin.
     * @param string $origin The origin that is allowed to access the resource.
     * A "*" can be specified to enable access to resource from any origin. 
     * A wildcard can be used to specify list of allowed origins, 
     * e.g. "*.yourdomain.com" (sub.yourdomain.com, yourdomain.com, 
     * sub.sub.yourdomain.com will be allowed origins in that case)
     * @throws CExecption
     */
    public function validateAllowedOrigin($origin)
    {
        if (is_array($origin)) {
        	foreach( $origin as $o) {
        		$this->validateAllowedOrigin($o);
	        }
        }
    	elseif (!is_string($origin))
        {
            throw new CExecption('The value of the "allowOrigin" property in settings must be '
                    . 'a string.');
        }
    }
    
    
    /**
     * Parses headers and returns the value of the Origin request header.
     * @return mixed The origin that is allowed to access the resource. 
     * (the value of the Origin request header), otherwise false. 
     */
    protected function parseHeaders()
    {
        if (!function_exists('getallheaders'))
        {
            $headers = $this->getAllHeaders();
        }
        else
        {
            $headers = getallheaders();
        }
        
        if ($headers === false)
        {
            return false;
        }
        
        $headers = array_change_key_case($headers, CASE_LOWER);
        
        if (!array_key_exists('origin', $headers))
        {
            return false;
        }
        
        $origin = $headers['origin'];
        $origin = parse_url($origin, PHP_URL_HOST);
        
        if (is_null($origin))
        {
            return false;
        }
        
		else return $headers['origin'];
        
    }
    
    
    /**
     * Checks if CORS-request is allowed for the current route.
     * @return boolean Whether CORS-request is allowed for the current route.
     */
    protected function checkAllowedRoute($settingRoute)
    {
	    if ($settingRoute === '*')
        {
            return true;
        }
        
        $requestedRoute = Yii::app()->getUrlManager()
                ->parseUrl(Yii::app()->getRequest());

        $wildcardRequestedRoute = preg_replace('#([^/]*)$#', '*', $requestedRoute, 1);
        
        return in_array($requestedRoute, $settingRoute) || in_array($wildcardRequestedRoute, $settingRoute);
    }
    
    
    protected function checkOrigin ($origin, $allowedOrigins) {
		foreach($allowedOrigins as $ao) {
			if(strlen($ao) === 1)
			{
				return true;
			}

			if (stripos($ao, '*') === false && $origin === $ao)
			{
				return true;
			}

			$pattern = '/' . substr($ao, 1) . '$/';

			if (substr($ao, 2) === $origin
			    || preg_match($pattern, $origin) === 1)
			{
				return true;
			}
		}
		return false;
    }

    /**
     * Sets Access-Control-Allow-Origin response header.
     * @param string $origin the value of the Access-Control-Allow-Origin response 
     * header.
     */
    protected function setAllowOriginHeader($origin)
    {
        header('Access-Control-Allow-Origin: ' . $origin);
    }
    
    
    /**
     * This method is used to get HTTP headers when PHP runs as FastCGI.
     * @return array An associative array of all the HTTP headers in the current request.
     */
    protected function getAllHeaders()
    {
       $headers = ''; 
       
       foreach ($_SERVER as $name => $value) 
       { 
           if (substr($name, 0, 5) == 'HTTP_') 
           { 
               $headers[str_replace(' ', '-', ucwords(strtolower
                       (str_replace('_', ' ', substr($name, 5)))))] = $value; 
           } 
       } 
       
       return $headers; 
    }
   
}
