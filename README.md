CORS Behavior
=============
	
Implementing Cross-origin resource sharing support in your Yii Framework application.

####Requirements

- Yii 1.0 or above
- PHP >= 5.4 or PHP < 5.4 if it was installed as an Apache module
	
####Installation
	
- Use Composer or just extract the release file under protected/extensions
	
####Configuration
	
Add the following code to your config file (protected/config/main.php): 
	
```php
	'behaviors' => array(
	        array('class' => 'application.extensions.CorsBehavior',
	            'route' => array('controller/actionA', 'controller/actionB'),
	            'allowOrigin' => '*.domain.com'
	            ),
	    ),
```

- **route** list of routes for CORS-requests.
- **allowOrigin** the origin that is allowed to access the resource. A "\*" can be specified to enable access to resource from any origin. A wildcard can be used to specify list of allowed origins, e.g. "*.yourdomain.com" (sub.yourdomain.com, yourdomain.com, sub.sub.yourdomain.com will be allowed origins in that case)
