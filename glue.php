<?php

    /**
     * glue
     *
     * Provides an easy way to map URLs to classes. URLs can be literal
     * strings or regular expressions.
     *
     * If the url is a literal it will be transformed to a regular
	 * expression with an anchored begin and end, and an optional
	 * end slash.
	 * 
	 * The regular expression matches become the arguments of the class
	 * method. The full url match is always the last argument.
     *
     * Example:
     *
     * $urls = array(
     *     '/' => 'index',
     *     '/page/(\d+)' => 'page'
     * );
     *
     * class page {
     *      function get($number) {
     *          echo "Your requested page " . $number;
     *      }
     * }
     *
     * glue::stick($urls);
     *
     */
    class glue {

        /**
         * stick
         *
         * the main static function of the glue class.
         *
         * @param   array    	$urls  	    The regex-based url to class mapping
         * @throws  Exception               Thrown if corresponding class is not found
         * @throws  Exception               Thrown if no match is found
         * @throws  BadMethodCallException  Thrown if a corresponding GET,POST is not found
         *
         */
        static function stick ($urls) {

            $method = strtolower($_SERVER['REQUEST_METHOD']);
            $path = $_SERVER['REQUEST_URI'];

            $found = false;

            krsort($urls);

            foreach ($urls as $regex => $class) {
                
				if(strpos($regex,'#') === false) $regex = '#^' . $regex . '/?$#';
				
                if (preg_match($regex, $path, $matches)) {
                    $found = true;
                    if (class_exists($class)) {
                        $obj = new $class;
                        if (method_exists($obj, $method)) {
                            $full_url = array_shift($matches);
							
							$matches[] = $full_url;
							
							call_user_func_array(array($obj, $method), $matches);
                        } else {
                            throw new BadMethodCallException("Method, $method, not supported.");
                        }
                    } else {
                        throw new Exception("Class, $class, not found.");
                    }
                    break;
                }
            }
            if (!$found) {
                throw new Exception("URL, $path, not found.");
            }
        }
    }
