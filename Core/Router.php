<?php

namespace Core;

/**
 * Router
 *
 * PHP version 7.0
 */
class Router
{
   /**
   * Associative array of routes (the routing table)
   * @var array
   */
   protected $routes = [];

   /**
   * Parameters from the matched route
   * @var array
   */
   protected $params = [];

   /**
   * Add a route to the routing table
   *
   * @param string $route  The route URL
   * @param array  $params Parameters (controller, action, etc)
   *
   * @return void
   */
   public function add($route, $params = []) // params is an optional argument
   {
      // Convert the route {controller}/{action} to a regular expression

      // test
      // echo '------- start add($route, $params = []) ---------------------------------------------------------------------<br>';
      // echo '------- this method adds a route to the array of routes -----------------------------------------------------<br>';
      // echo '------- first, it converts the route ('.$route.') to a regular expression of the route -------------------------------------------<br>';
      // echo '------- then, it adds it to the array of routes (supported routes) --------------------------------<br><br>';
      // echo "INSIDE add() method: <br><br>";
      // echo '$route = '  . $route . '<br><br>';
      // echo '$params[] passed to add() in index.php:';
      // echo '<pre>';
      // print_r($params);
      // echo '</pre>';
      // exit();

      // 1) find forward slash & replace with backslash & forward slash
      $route = preg_replace('/\//', '\\/', $route);

      // test
      // echo '$route after 1st preg_replace, which adds backslash before slash = <br>'  . $route . '<br><br>';
      // exit();


      // convert variable e.g. {controller} to regular expression
      // 2) find word between curly braces & replace with regular expression
      $route = preg_replace('/\{([a-z]+)\}/', '(?P<\1>[a-z-]+)', $route);

      // test
      // echo '$route after 2nd preg_replace, which converts alpha character variable to regEx = <br>'  . $route . '<br><br>';
      // exit();


      // Convert variables with custom regular expressions e.g.{id:\d+}
      // ([^\}]) means any character except a right curly brace
      $route = preg_replace('/\{([a-z]+):([^\}]+)\}/i', '(?P<\1>\2)', $route);

      // test
      // echo '$route after 3rd preg_replace, which converts custom alpha character variable with value to regEx = <br>'  . $route . '<br><br>';
      // exit();

      // add start and end delimiters, and case insensitive flag
      $route = '/^' . $route . '$/i';


      // test
      // echo '$route after delimiters added = <br>'  . $route . '<br><br>';
      // echo '------- end add($route, $params = [])  --------------------------------<br><br>';
      // exit();

      // add $params[] passed to add() to $routes[$route] ($route is the endpoint path of the type String)
      // to see routes array, echo $this->routes (done below in match() testing)
      $this->routes[$route] = $params;

      // test
      // echo '$this->routes[$route] Array = ';
      // echo '<pre>';
      // print_r($this->routes[$route]);
      // echo '</pre>';
      // echo '------- end add($route, $params = [])  --------------------------------<br><br>';
      // exit();
    }



   /**
   * Get all the routes from the routing table
   *
   * @return array
   */
   public function getRoutes()
   {
     return $this->routes;
   }



   /**
   * Match the route to the routes in the routing table, setting the $params
   * property if a route is found
   *
   * @param  string $url The route URL
   *
   * @return boolean   True if a match found, false otherwise
   */
   public function match($url)
   {
      // test
      // echo '------- start match($url)  --------------------------------<br><br>';
      // echo '$url value passed to match(): ' . $url . '<br>';
      // echo 'Display all routes in $routes[] of Router Class';
      // echo '<pre>';
      // echo '$this->routes: ';
      // print_r($this->routes);
      // echo '</pre>';
      // exit();

      // loop thru $routes[]
      foreach($this->routes as $route => $params)
      {
         // echo 'First iteration inside foreach($this->routes as $route => $params): <br><br>';
         // echo '$route = ' . $route . '<br><br>';
         // echo '<pre>';
         // print_r($params);
         // echo '</pre>';
         // exit();

         // $route is a regular expression (not literal route)
         // check if a route pattern in $routes[] matches $url
         // e.g. if $url = products/8, it will match regular expression /^products\/(?P[0-9]+)$/i
         // preg_match(pattern, subject, [, array matches])
         // php.net: preg_match searches subject for a match to the regular expression given in pattern.
         if(preg_match($route, $url, $matches))
         {
            foreach($matches as $key => $match)
            {
              if(is_string($key))
              {
                  $params[$key] = $match;
              }
            }

            $this->params = $params;
            return true;
         }
      }
      return false;
   }


   /**
   * Get the currently matched parameters
   *
   * @return array
   */
   public function getParams()
   {
     return $this->params;
   }


   /**
   * [dispatch description]
   *
   * @param  string $url The route URL
   *
   * @return void
   */
   public function dispatch($url)
   {
      // echo '------- start dispatch($url)  --------------------------------<br><br>';
      // test - before removeQueryStringVariables()
      // echo "Before removeQueryStringVariables(): $url <br>";

      // remove query string variables
      $url = $this->removeQueryStringVariables($url);

      // test - after removeQueryStringVariables()
      // echo "After removeQueryStringVariables(): $url <br><br>";
      // echo 'Next we pass the $url value ('.$url.') to match() to check if the pattern exists in $routes[]. <br>';
      // echo 'If it returns true, we return here to dispatch($url) for more processing <br>';
      // echo '------- end dispatch($url)  -------------------------------- <br><br>';
      // exit();

      // check if $url exists in $routes
      if($this->match($url))
      {
         $controller = $this->params['controller'];
         $controller = $this->convertToStudlyCaps($controller);
         //$controller = "App\Controllers\\$controller"; // App\Controllers (namespace) where $controller class lives
         $controller = $this->getNamespace() . $controller;

         // test
         // echo '$controller: ' . $controller. '<br><br>';
         // exit();

         if(class_exists($controller))
         {
             $controller_object = new $controller($this->params);

             $action = $this->params['action'];
             $action = $this->convertToCamelCase($action);

             if(is_callable([$controller_object, $action]))
             {
                 $controller_object->$action();
             }
             else
             {
                 throw new \Exception("Method '$action' (in controller $controller)
                 not found");
             }
         }
         else
         {
             throw new \Exception("Controller class $controller not found");
         }
      }
      else
      {
         throw new \Exception('No route matched', 404);
      }
   }


   /**
   * Convert the string with hyphens to StudlyCaps,
   * e.g. post-authors => PostAuthors
   *
   * @param  string $string The string to convert
   *
   * @return string
   */
   public function convertToStudlyCaps($string)
   {
     return str_replace(' ', '', ucwords(str_replace('-', '', $string)));
   }


   /**
   * Convert the string with hyphens to camelCase
   * e.g. add-new => addNew
   *
   * @param  string $string The string to convert
   *
   * @return string
   */
   public function convertToCamelCase($string)
   {
     return lcfirst($this->convertToStudlyCaps($string));
   }


   /**
   * Remove the query string variables from the URL (if any). As the full
   * query string is used for the route, any variables at the end will need
   * to be removed before the route is matched to the routing table. For
   * example:
   *
   *   URL                           $_SERVER['QUERY_STRING']  Route
   *   -------------------------------------------------------------------
   *   localhost                     ''                        ''
   *   localhost/?                   ''                        ''
   *   localhost/?page=1             page=1                    ''
   *   localhost/posts?page=1        posts&page=1              posts
   *   localhost/posts/index         posts/index               posts/index
   *   localhost/posts/index?page=1  posts/index&page=1        posts/index
   *
   * A URL of the format localhost/?page (one variable name, no value) won't
   * work however. (NB. The .htaccess file converts the first ? to a & when
   * it's passed through to the $_SERVER variable).

   * @param  string $url The full URL
   *
   * @return string   The URL with the query string variables removed
   */
   protected function removeQueryStringVariables($url)
   {
      if($url != '')
      {
         $parts = explode('&', $url, 2); // & is converted ?

         if(strpos($parts[0], '=') === false)
         {
            $url = $parts[0];
         }
         else
         {
            $url = '';
         }
      }
      return $url;
   }



   /**
   * Get the namespace for the controller class. The namespace defined in the
   * route parameters is added if present
   *
   * @return string  The request URL
   */
   protected function getNamespace()
   {
     // default namespace name
     $namespace = 'App\Controllers\\';

      // Checks if the given key or index exists in the array (boolean)
      if(array_key_exists('namespace', $this->params))
      {
         $namespace .= $this->params['namespace'] . '\\';
      }

     return $namespace;
   }
}
