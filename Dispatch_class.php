<?php
/**
 * @author Jesus A. Domingo
 * @license MIT
 * 
 * Class version with procedural facade by Neil McGann.
 * 
 */

/**
 * Class version of Dispatch
 *
 * - Singleton only via extending Singleton class and using ::instance() to create.
 * - Static variables in functions moved out into private properties.
 * - Improved routing params with regexes and optional sections.
 * - Autoloader added. (dispatch.plugins and dispatch.autoload are the 2 extra paths)
 * - dispatch.request in configs has lots of routing params (GET/POST etc.)
 * - When dispatch.url not set auto detection of path is improved.
 * 
 * - Calls to exit() and header() moved into functions so can be substituted
 *   in testing.
 *
 */

// ----------------------------------------------------------------------------
// Procedural functions to mimic standard version of dispatch

// routing functions
function on(){

  return call_user_func_array(array(Dispatch::instance(),'on'),func_get_args()); 
}
function resource(){
  
  return call_user_func_array(array(Dispatch::instance(),'resource'),func_get_args());
}
function error(){
  
  return call_user_func_array(array(Dispatch::instance(),'error'),func_get_args());
}
function before(){
  
  return call_user_func_array(array(Dispatch::instance(),'before'),func_get_args());
}
function after(){
  
  return call_user_func_array(array(Dispatch::instance(),'after'),func_get_args());
}
function bind(){
  
  return call_user_func_array(array(Dispatch::instance(),'bind'),func_get_args());
}
function filter(){
  
  return call_user_func_array(array(Dispatch::instance(),'filter'),func_get_args());
}
function redirect(){
  
  return call_user_func_array(array(Dispatch::instance(),'redirect'),func_get_args());
}

// views, templates and responses
function render(){
  
  return call_user_func_array(array(Dispatch::instance(),'render'),func_get_args());
}
function template(){
  
  return call_user_func_array(array(Dispatch::instance(),'template'),func_get_args());
}
function partial(){
  
  return call_user_func_array(array(Dispatch::instance(),'partial'),func_get_args());
}
function json(){
  
  return call_user_func_array(array(Dispatch::instance(),'json'),func_get_args());
}
function nocache(){
  
  return call_user_func_array(array(Dispatch::instance(),'nocache'),func_get_args());
}

// request data helpers
function params(){
  
  return call_user_func_array(array(Dispatch::instance(),'params'),func_get_args());
}
function cookie(){
  
  return call_user_func_array(array(Dispatch::instance(),'cookie'),func_get_args());
}
function scope(){
  
  return call_user_func_array(array(Dispatch::instance(),'scope'),func_get_args());
}
function files(){
  
  return call_user_func_array(array(Dispatch::instance(),'files'),func_get_args());
}
function send(){
  
  return call_user_func_array(array(Dispatch::instance(),'send'),func_get_args());
}
function request_headers(){
  
  return call_user_func_array(array(Dispatch::instance(),'request_headers'),func_get_args());
}
function request_body(){
  
  return call_user_func_array(array(Dispatch::instance(),'request_body'),func_get_args());
}

// configurations and settings
function config(){
  
  return call_user_func_array(array(Dispatch::instance(),'config'),func_get_args());
}

// misc helpers
function flash(){
  
  return call_user_func_array(array(Dispatch::instance(),'flash'),func_get_args());
}
function url(){
  
  return call_user_func_array(array(Dispatch::instance(),'url'),func_get_args());
}
function html(){
  
  return call_user_func_array(array(Dispatch::instance(),'html'),func_get_args());
}
function ip(){
  
  return call_user_func_array(array(Dispatch::instance(),'ip'),func_get_args());
}

// entry point
function dispatch(){
  
  return call_user_func_array(array(Dispatch::instance(),'dispatch'),func_get_args());
}

// ----------------------------------------------------------------------------
// ----------------------------------------------------------------------------
// ----------------------------------------------------------------------------
     
class Dispatch extends Singleton {
    
  public function __construct() {
    
    parent::__construct();
      
    // Register autoloader
    spl_autoload_register(array($this,'autoload'));
      
  }
  
  ///////////////////////////////////////////////////////////////////////////////
  // Standard code below modified to live in a class.
  // Static vars have been moved out of functions and created as protected properties
  //
  ///////////////////////////////////////////////////////////////////////////////
  
  /**
   * Function for setting http error code handlers and for
   * triggering them. Execution stops after an error callback
   * handler finishes.
   *
   * @param int $code http status code to use
   * @param callable optional, callback for the error
   *
   * @return void
   */
  protected $error_error_callbacks = array();
   
  public function error($code, $callback = null) {
  
    //static $error_callbacks = array();
  
    $code = (string) $code;
  
    // this is a hook setup, save and return
    if (is_callable($callback)) {
      $this->error_error_callbacks[$code] = $callback;
      return;
    }
  
    // see if passed callback is a message (string)
    $message = (is_string($callback) ? $callback : 'Page Error');
  
    // set the response code
    $this->send_header(
      "{$_SERVER['SERVER_PROTOCOL']} {$code} {$message}",
      true,
      (int) $code
    );
  
    // bail early if no handler is set
    if(!isset($this->error_error_callbacks[$code]))
    {
      $this->call_exit("{$code} {$message}");
    
    }
    else
    {
      // if we got callbacks, try to invoke
      call_user_func($this->error_error_callbacks[$code], $code);
      
      //call stub method to exit 
      $this->call_exit();
     }
  }

    /**
     * Dispatch::call_exit()
     * 
     * @param string $msg
     * @return void
     * 
     * Routine that can be stubbed out in testing to prevent exiting from script.
     */
    protected function call_exit($msg = '')
    {
        exit ($msg);
    }

    /**
     * Dispatch::send_header()
     * 
     * @return void
     * 
     * Routine that can be stubbed out in testing to prevent headers being sent
     * and capture for inspection.
     */
    protected function send_header()
    {
        return call_user_func_array('header',func_get_args());
    }

  /**
   * Sets or gets an entry from the loaded config.ini file. If the $key passed
   * is 'source', it expects $value to be a path to an ini file to load. Calls
   * to config('source', 'inifile.ini') will aggregate the contents of the ini
   * file into config().
   *
   * @param string $key setting to set or get. passing null resets the config
   * @param string $value optional, If present, sets $key to this $value.
   *
   * @return mixed|null value
   */
  protected $config_config = array(); //moved out of fn
    
  public function config($key = null, $value = null) {
  
    //static $config = array();
  
    // if key is source, load ini file and return
    if ($key === 'source') {
      if (!file_exists($value)) {
        trigger_error(
          "File passed to config('source') not found",
          E_USER_ERROR
        );
      }
      $this->config_config = array_merge($this->config_config, parse_ini_file($value, true));
      return;
    }
      //reset configuration to default
      if ($key === null){
        $this->config_config = array();
        return;
      }
      
    // for all other string keys, set or get
    if (is_string($key)) {
      if ($value === null)
        return (isset($this->config_config[$key]) ? $this->config_config[$key] : null);
      return ($this->config_config[$key] = $value);
    }
  
    // setting multiple settings. merge together if $key is array.
    //filters key names as a sanity check
    if (is_array($key)) {
      $keys = array_filter(array_keys($key), 'is_string');
      $keys = array_intersect_key($key, array_flip($keys));
      $this->config_config = array_merge($this->config_config, $keys);
    }
    
  }

  /**
   * Utility for setting cross-request messages using cookies,
   * referred to as flash messages (invented by Rails folks).
   * Calling flash('key') will return the message and remove
   * the message making it unavailable in the following request.
   * Calling flash('key', 'message', true) will store that message
   * for the current request but not available for the next one.
   *
   * @param string $key name of the flash message
   * @param string $msg string to store as the message
   * @param bool $now if the message is available immediately
   *
   * @return $string message for the key
   */
   
  protected $flash_token = null; //moved outside fn
  protected $flash_store = null;//moved outside fn
  protected $flash_cache = array();//moved outside fn
   
  public function flash($key, $msg = null, $now = false) {
  
    // initialize these things only once per request
    //static $token = null;
    //static $store = null;
    //static $cache = array();
  
    if (!$this->flash_token) {
      $this->flash_token = $this->config('dispatch.flash_cookie');
      $this->flash_token = (!$this->flash_token ? '_F' : $this->flash_token);
    }
  
    // get messages from cookie, if any, or from new hash
    if (!$this->flash_store) {
      if ($this->flash_store = $this->cookie($this->flash_token))
        $this->flash_store = json_decode($this->flash_store, true);
      else
        $this->flash_store = array();
    }
  
    // if this is a fetch request
    if ($msg == null) {
  
      // cache value, unset from cookie
      if (isset($this->flash_store[$key])) {
        $this->flash_cache[$key] = $this->flash_store[$key];
        unset($this->flash_store[$key]);
        $this->cookie($this->flash_token, json_encode($this->flash_store));
      }
  
      // value can now be taken from the cache
      return (isset($this->flash_cache[$key]) ? $this->flash_cache[$key] : null);
    }
  
    // cache it and put it in the cookie
    $this->flash_store[$key] = $this->flash_cache[$key] = $msg;
  
    // rewrite cookie unless now-type
    if (!$now)
      $this->cookie($this->flash_token, json_encode($this->flash_store));
  
    // return the new message
    return ($this->flash_cache[$key] = $msg);
  }

  /**
   * Convenience wrapper for urlencode()
   *
   * @param string $str string to encode.
   *
   * @return string url encoded string
   */
  public function url($str) {
    return urlencode($str);
  }
  
  /**
   * Convenience wrapper for htmlentities().
   *
   * @param string $str string to encode
   * @param string $enc encoding to use.
   * @param string $flags htmlentities() flags
   *
   * @return string encoded string
   */
  public function html($str, $flags = -1, $enc = 'UTF-8', $denc = true) {
    $flags = ($flags < 0 ? ENT_QUOTES : $flags);
    return htmlentities($str, $flags, $enc, $denc);
  }
  
  /**
   * Helper for getting values from $_GET, $_POST and route
   * symbols.
   *
   * @param string $name optional. parameter to get the value for
   * @param mixed $default optional. default value for param
   *
   * @return mixed param value.
   * 
   */
   
  protected $params_source = null; //moved out of fn
   
  public function params($name = null, $default = null) {
  
    //static $source = null;
  
    // initialize source if this is the first call
    if (!$this->params_source) 
    {
      $this->params_source = array_merge($_GET, $_POST);
      if (get_magic_quotes_gpc())
        array_walk_recursive(
          $this->params_source,
          function (&$v) { $v = stripslashes($v); }
        );
    }
  
    // this is a value fetch call
    if (is_string($name))
      return (isset($this->params_source[$name]) ? $this->params_source[$name] : $default);
  
    // used by on() for merging in route symbols.
    $this->params_source = array_merge($this->params_source, $name);

  }

  /**
   * Wraps around $_SESSION
   *
   * @param string $name name of session variable to set
   * @param mixed $value value for the variable. Set this to null to
   *   unset the variable from the session.
   *
   * @return mixed value for the session variable
   */
   
  protected $session_session_active = false; //moved out of fn
   
  public function session($name, $value = null) {
  
    //static $session_active = false;
  
    // stackoverflow.com: 3788369
    if ($this->session_session_active === false) {
  
      if (($current = ini_get('session.use_trans_sid')) === false) {
        trigger_error(
          'Call to session() requires that sessions be enabled in PHP',
          E_USER_ERROR
        );
      }
  
      $test = "mix{$current}{$current}";
  
      $prev = @ini_set('session.use_trans_sid', $test);
      $peek = @ini_set('session.use_trans_sid', $current);
  
      if ($peek !== $current && $peek !== false)
        session_start();
  
      $this->session_session_active = true;
    }
  
    if (func_num_args() === 1)
      return (isset($_SESSION[$name]) ? $_SESSION[$name] : null);
  
    $_SESSION[$name] = $value;
  }

  /**
   * Wraps around $_COOKIE and setcookie().
   *
   * @param string $name name of the cookie to get or set
   * @param string $value optional. value to set for the cookie
   * @param integer $expire default 1 year. expiration in seconds.
   * @param string $path default '/'. path for the cookie.
   *
   * @return string value if only the name param is passed.
   */
   
  protected $cookie_quoted = -1; //moved outside fn

  public function cookie($name, $value = null, $expire = 31536000, $path = '/') {
  
    //static $quoted = -1;
  
    if ($this->cookie_quoted < 0)
      $this->cookie_quoted = get_magic_quotes_gpc();
  
    if (func_num_args() === 1) {
      return (
        isset($_COOKIE[$name]) ? (
          $this->cookie_quoted ?
          stripslashes($_COOKIE[$name]) :
          $_COOKIE[$name]
        ) : null
      );
    }
  
    setcookie($name, $value, time() + $expire, $path);
  }

  /**
   * Convenience wrapper for accessing http request headers.
   *
   * @param string $key name of http request header to fetch
   *
   * @return string value for the header, or null if header isn't there.
   */
   
  protected $request_headers_headers = null; //moved outside fn
   
  public function request_headers($key = null) {
  
    //static $headers = null;
  
    // if first call, pull headers
    if (!$this->request_headers_headers) {
      // if we're not on apache
      $this->request_headers_headers = array();
      foreach ($_SERVER as $k => $v)
        if (substr($k, 0, 5) == 'HTTP_')
          $this->request_headers_headers[strtolower(str_replace('_', '-', substr($k, 5)))] = $v;
    }
  
    // header fetch
    if ($key !== null) {
      $key = strtolower($key);
      return isset($this->request_headers_headers[$key]) ? $this->request_headers_headers[$key] : null;
    }
  
    return $this->request_headers_headers;
  }

  /**
   * Convenience function for reading in the request body. JSON
   * and form-urlencoded content are automatically parsed and returned
   * as arrays.
   *
   * @param boolean $load if false, you get a temp file path with the data
   *
   * @return mixed raw string or decoded JSON object
   */
   
  protected $request_body_content = null; //moved outside fn
   
  public function request_body($load = true) {
  
    //static $content = null;
  
    // called before, just return the value
    if ($this->request_body_content)
      return $this->request_body_content;
  
    // get correct content-type of body (hopefully)
    $content_type = isset($_SERVER['HTTP_CONTENT_TYPE']) ?
      $_SERVER['HTTP_CONTENT_TYPE'] :
      $_SERVER['CONTENT_TYPE'];
  
    // try to load everything
    if ($load) {
  
      $this->request_body_content = file_get_contents('php://input');
      $content_type = preg_split('/ ?; ?/', $content_type);
  
      // if json, cache the decoded value
      if ($content_type[0] == 'application/json')
        $this->request_body_content = json_decode($this->request_body_content, true);
      else if ($content_type[0] == 'application/x-www-form-urlencoded')
        parse_str($this->request_body_content, $this->request_body_content);
  
      return $this->request_body_content;
    }
  
    // create a temp file with the data
    $path = tempnam(sys_get_temp_dir(), 'disp-');
    $temp = fopen($path, 'w');
    $data = fopen('php://input', 'r');
  
    // 8k per read
    while ($buff = fread($data, 8192))
      fwrite($temp, $buff);
  
    fclose($temp);
    fclose($data);
  
    return $path;
  }

  /**
   * Creates a file download response for the specified path using the passed
   * filename. If $sec_expires is specified, this duration will be used
   * to specify the download's cache expiration header.
   *
   * @param string $path full path to the file to stream
   * @param string $filename filename to use in the content-disposition header
   * @param int $sec_expires optional, defaults to 0. in seconds.
   *
   * @return void
   */
  public function send($path, $filename, $sec_expires = 0) {
  
    $mime = 'application/octet-stream';
    $etag = md5($path);
    $lmod = filemtime($path);
    $size = filesize($path);
  
    // cache headers
    $this->send_header('Pragma: public');
    $this->send_header('Last-Modified: '.gmdate('D, d M Y H:i:s', $lmod).' GMT');
    $this->send_header('ETag: '.$etag);
  
    // if we want this to persist
    if ($sec_expires > 0) {
      $this->send_header('Cache-Control: maxage='.$sec_expires);
      $this->send_header(
        'Expires: '.gmdate('D, d M Y H:i:s',
        time() + $sec_expires).' GMT'
      );
    }
  
    // file info
    $this->send_header('Content-Disposition: attachment; filename='.urlencode($filename));
    $this->send_header('Content-Type: '.$mime);
    $this->send_header('Content-Length: '.$size);
  
    // no time limit, clear buffers
    set_time_limit(0);
    ob_clean();
  
    // dump the file
    $fp = fopen($path, 'rb');
    while (!feof($fp)) {
      echo fread($fp, 1024*8);
      ob_flush();
      flush();
    }
    fclose($fp);
  }

  /**
   * File upload wrapper. Returns a hash containing file
   * upload info. Skips invalid uploads based on
   * is_uploaded_file() check.
   *
   * @param string $name input file field name to check.
   *
   * @param array info of file if found.
   */
  public function files($name) {
  
    if (!isset($_FILES[$name]))
      return null;
  
    $result = null;
  
    // if file field is an array
    if (is_array($_FILES[$name]['name'])) {
  
      $result = array();
  
      // consolidate file info
      foreach ($_FILES[$name] as $k1 => $v1)
        foreach ($v1 as $k2 => $v2)
          $result[$k2][$k1] = $v2;
  
      // remove invalid uploads
      foreach ($result as $i => $f)
        if (!is_uploaded_file($f['tmp_name']))
          unset($result[$i]);
  
      // if no entries, null, else, return it
      $result = (!count($result) ? null : array_values($result));
  
    } else {
      // only if file path is valid
      if (is_uploaded_file($_FILES[$name]['tmp_name']))
        $result = $_FILES[$name];
    }
  
    // null if no file or invalid, hash if valid
    return $result;
  }

  /**
   * A utility for passing values between scopes. If $value
   * is passed, $name will be set to $value. If $value is not
   * passed, the value currently mapped against $name will be
   * returned instead (or null if nothing mapped).
   * 
   * If $name is null all the store will be cleared.
   *
   * @param string $name name of variable to store.
   * @param mixed $value optional, value to store against $name
   *
   * @return mixed value mapped to $name
   */
  protected $scope_stash = array(); //moved out of fn
   
  public function scope($name = null, $value = null) {
  
    //static $stash = array();
  
    if (is_string($name) && $value === null)
      return isset($this->scope_stash[$name]) ? $this->scope_stash[$name] : null;

    //if no $name clear $stash
    if(is_null($name)) {
      $this->scope_stash = array();
      return;
    }

  //set new $value
  if(is_string($name))  
    return ($this->scope_stash[$name] = $value);
  }
  
  /**
   * Returns the client's IP address.
   *
   * @return string client's ip address.
   */
  public function ip() {
    if (isset($_SERVER['HTTP_CLIENT_IP']))
      return $_SERVER['HTTP_CLIENT_IP'];
    else if (isset($_SERVER['HTTP_X_FORWARDED_FOR']))
      return $_SERVER['HTTP_X_FORWARDED_FOR'];
    return $_SERVER['REMOTE_ADDR'];
  }
  
  /**
   * Performs an HTTP redirect.
   *
   * @param int|string http code for redirect, or path to redirect to
   * @param string|bool path to redirect to, or condition for the redirect
   * @param bool condition for the redirect, true means it happens
   *
   * @return void
   */
  public function redirect($path, $code = 302, $condition = true) {
    if (!$condition)
      return;
    @$this->send_header("Location: {$path}", true, $code);
      //call stubbable exit routine
    $this->call_exit;
  }
  
  /**
   * Convenience function for storing/fetching content to be
   * plugged into the layout within render().
   *
   * @param string $value optional, value to use as content.
   *
   * @return string content
   */
  public function content($value = null) {
    return $this->scope('$content$', $value);
  }

  /**
   * Returns the contents of the template $view, using
   * $locals (optional).
   *
   * @param string $view path to partial
   * @param array $locals optional, hash to load as scope variables
   *
   * @return string content of the partial.
   */
  public function template($view, $locals = null) {
  
    if (($view_root = $this->config('dispatch.views')) == null)
      trigger_error("config('dispatch.views') is not set.", E_USER_ERROR);
  
    extract((array) $locals, EXTR_SKIP);
  
    $view = $view_root.DIRECTORY_SEPARATOR.$view.'.html.php';
    $html = '';
  
    if (file_exists($view)) {
      ob_start();
      require $view;
      $html = ob_get_clean();
    } else {
      trigger_error("Template [{$view}] not found.", E_USER_ERROR);
    }
  
    return $html;
  }
  
  /**
   * Returns the contents of the partial $view, using $locals (optional).
   * Partials differ from templates in that their filenames start with _.
   *
   * @param string $view path to partial
   * @param array $locals optional, hash to load as scope variables
   *
   * @return string content of the partial.
   */
  public function partial($view, $locals = null) {
    $path = basename($view);
    $view = preg_replace('/'.$path.'$/', "_{$path}", $view);
    return $this->template($view, $locals);
  }
  
  /**
   * Renders the contents of $view using $locals (optional), into
   * $layout (optional). If $layout === false, no layout will be used.
   *
   * @param string $view path to the view file to render
   * @param array $locals optional, hash to load into $view's scope
   * @param string|bool path to the layout file to use, false means no layout
   *
   * @return string contents of the view + layout
   */
  public function render($view, $locals = array(), $layout = null) {
  
    // load the template and plug it into content()
    $content = $this->template($view, $locals);
    $this->content(trim($content));
  
    // if we're to use a layout
    if ($layout !== false) {
  
      // layout = null means use the default
      if ($layout == null) {
        $layout = $this->config('dispatch.layout');
        $layout = ($layout == null) ? 'layout' : $layout;
      }
  
      // load the layout template, with content() already populated
      return (print $this->template($layout, $locals));
    }
  
    // no layout was to be used (layout = false)
    echo $content;
  }

  /**
   * Convenience wrapper for creating route handlers
   * that show nothing but a view.
   *
   * @param string $file name of the view to render
   * @param array|callable $locals locals array or callable that return locals
   * @param string|boolean $layout layout file to use
   *
   * @return callable handler function
   */
  public function inline($file, $locals = array(), $layout = 'layout') {
    $locals = is_callable($locals) ? $locals() : $locals;
    return function () use ($file, $locals, $layout) {
      $this->render($file, $locals, $layout);
    };
  }
  
  /**
   * Spit headers that force cache volatility.
   *
   * @param string $content_type optional, defaults to text/html.
   *
   * @return void
   */
  public function nocache() {
    $this->send_header('Expires: Tue, 13 Mar 1979 18:00:00 GMT');
    $this->send_header('Last-Modified: '.gmdate('D, d M Y H:i:s').' GMT');
    $this->send_header('Cache-Control: no-store, no-cache, must-revalidate');
    $this->send_header('Cache-Control: post-check=0, pre-check=0', false);
    $this->send_header('Pragma: no-cache');
  }
  
  /**
   * Dump a JSON response along with the appropriate headers.
   *
   * @param mixed $obj object to serialize into JSON
   * @param string $func for JSONP output, this is the callback name
   *
   * @return void
   */
  public function json($obj, $func = null) {
    $this->nocache();
    if (!$func) {
      $this->send_header('Content-type: application/json');
      echo json_encode($obj);
    } else {
      $this->send_header('Content-type: application/javascript');
      echo ";{$func}(".json_encode($obj).");";
    }
  }
  
  /**
   * Creates callbacks (filters) against certain
   * symbols within a route. Whenever $sym is encountered
   * in a route, the filter is invoked.
   *
   * @param string $sym symbol to create a filter for
   * @param callable|mixed filter or value to pass to the filter
   *
   * @return void
   * 
   */
   
  protected $filter_symfilters = array(); //moved outside fn
   
  public function filter($symbol, $callback = null) {
  
    //static $symfilters = array();
  
    // this is a mapping call
    if (is_callable($callback)) {
      $this->filter_symfilters[$symbol][] = $callback;
      return;
    }
  
    // run symbol filters
    foreach ($symbol as $sym => $val) {
      if (isset($this->filter_symfilters[$sym])) {
        foreach ($this->filter_symfilters[$sym] as $callback) {
          call_user_func($callback, $val);
        }
      }
    }
  }

  /**
   * Filters parameters for certain symbols that are passed to the request
   * callback. Only one callback can be bound to a symbol. The original request
   * parameter can be accessed using the param() function.
   *
   * @param string $symbol symbol to bind a callback to
   * @param callable|mixed callback to bind to that symbol
   *
   * @return mixed transformed value based on the param
   */
   
  protected $bind_bindings = array(); //moved outside function
  protected $bind_symcache = array();//moved outside function
   
  public function bind($symbol, $callback = null) {
  
    // callback store and symbol cache
    //static $bindings = array();
    //static $symcache = array();
  
    // Bind a callback to the symbol
    if (is_callable($callback)) {
      $this->bind_bindings[$symbol] = $callback;
      return;
    }
  
    // If the symbol is given but is not an array - see if we have filtered it
    if (!is_array($symbol))
      return isset($this->bind_symcache[$symbol]) ? $this->bind_symcache[$symbol] : null;
  
    // If callbacks are bound to symbols, apply them
    $values = array();
    foreach ($symbol as $sym => $val) {
      if (isset($this->bind_bindings[$sym]))
        $this->bind_symcache[$sym] = $val = call_user_func($this->bind_bindings[$sym], $val);
      $values[$sym] = $val;
    }
  
    return $values;
  }

  /**
   * Function for mapping callbacks to be invoked before each request.
   * If called with two args, with first being regex, callback is only
   * invoked if the regex matches the request URI.
   *
   * @param callable|string $callback_or_regex callable or regex
   * @param callable $callback required if arg 1 is regex
   *
   * @return void
   */
   
  protected $before_regexp_callbacks = array(); //moved outside function
  protected $before_before_callbacks = array(); //moved outside function

  public function before() {
  
    //static $regexp_callbacks = array();
    //static $before_callbacks = array();
  
    $args = func_get_args();
    $func = array_pop($args);
    $rexp = array_pop($args);
  
    // mapping call
    if (is_callable($func)) {
      if ($rexp)
        $this->before_regexp_callbacks[$rexp] = $func;
      else
        $this->before_before_callbacks[] = $func;
      return;
    }
  
    // remap args for clarity
    $verb = $rexp;
    $path = substr($func, 1);
  
    // let's run regexp callbacks first
    foreach ($this->before_regexp_callbacks as $rexp => $func)
      if (preg_match($rexp, $path))
        $func($verb, $path);
  
    // call generic callbacks
    foreach ($this->before_before_callbacks as $func)
      $func($verb, $path);
  }

  /**
   * Function for mapping callbacks to be invoked after each request.
   * If called with two args, with first being regex, callback is only
   * invoked if the regex matches the request URI.
   *
   * @param callable|string $callback_or_regex callable or regex
   * @param callable $callback required if arg 1 is regex
   *
   * @return void
   */
   
  protected $after_regexp_callbacks = array(); //moved outside function
  protected $after_after_callbacks = array(); //moved outside function
   
  public function after($method_or_cb = null, $path = null) {
  
    //static $regexp_callbacks = array();
    //static $after_callbacks = array();
  
    $args = func_get_args();
    $func = array_pop($args);
    $rexp = array_pop($args);
  
    // mapping call
    if (is_callable($func)) {
      if ($rexp)
        $this->after_regexp_callbacks[$rexp] = $func;
      else
        $this->after_after_callbacks[] = $func;
      return;
    }
  
    // remap args for clarity
    $verb = $rexp;
    $path = $func;
  
    // let's run regexp callbacks first
    foreach ($this->after_regexp_callbacks as $rexp => $func)
      if (preg_match($rexp, $path))
        $func($verb, $path);
  
    // call generic callbacks
    foreach ($this->after_after_callbacks as $func)
      $func($verb, $path);
  }

  /**
   * Maps a callback or invokes a callback for requests
   * on $pattern. If $callback is not set, $pattern
   * is matched against all routes for $method, and the
   * the mapped callback for the match is invoked. If $callback
   * is set, that callback is mapped against $pattern for $method
   * requests.
   *
   * @param string $method HTTP request method or method + path
   * @param string $pattern path or callback
   * @param callable $callback optional, handler to map
   *
   * @return void
   */
   
  protected $on_routes = array(); //moved outside function
  
  public function on($method, $path, $callback = null) {
  
    // state (routes and cache util)
    //static $routes = array();

    $regexp = null;
    $path = trim($path, '/');
  
    // a callback was passed, so we create a route definition
    if (is_callable($callback)) {
  
      //add optional bracketed sections and "match anything" section
      $path = str_replace(array(')','*'), array(')?','.*?'), $path); 
      //revised regex that allows named capture groups with optional regexes 
      // (uses @ to separate param name and regex)
      $regexp = preg_replace_callback (
            '#:([\w]+)(@([^/\(\)]*))?#',
            function($matches) {
              if (isset($matches[3])) //2 versions of named capture groups - with and without a following regex.
              {
                return '(?P<'.$matches[1].'>'.$matches[3].')'; //with
              }
                return '(?P<'.$matches[1].'>[^/]+)'; //without
            },
            $path
      );

      $method = array_map('strtoupper', (array) $method);
  
      foreach ($method as $m)
        $this->on_routes[$m]['@^'.$regexp.'$@'] = $callback;
        
      return;
    }
  
    // setup method and rexp for dispatch
    $method = strtoupper($method);
  
    // cache miss, do a lookup
    $finder = function ($routes, $path) {
      $found = false;
      foreach ($routes as $regexp => $callback) {
        if (preg_match($regexp, $path, $values))
          return array($regexp, $callback, $values);
      }
      return array(null, null, null);
    };
  
    // lookup a matching route
    if (isset($this->on_routes[$method]))
    {
      list($regexp, $callback, $values) = $finder($this->on_routes[$method], $path);
    }
    
    // if no match, try the any-method handlers
    if (!$regexp && isset($this->on_routes['*']))
    {
        list($regexp, $callback, $values) = $finder($this->on_routes['*'], $path);
    }
    
    // we got a match
    if ($regexp) 
    {
        // construct the params for the callback
        $tokens = array_filter(array_keys($values), 'is_string');
        $values = array_map('urldecode', array_intersect_key(
          $values,
          array_flip($tokens)
        ));
    
        // setup + dispatch
        ob_start();
        $this->params($values);
        $this->filter($values);
        $this->before($method, "@{$path}");

        //adjust $values array to suit the number of args that the callback is expecting
        //padding is added to the array with null elements having numeric keys starting from zero.
        //stops error if optional args don't match the number of parameters.
        $ref = new ReflectionFunction($callback);
        $num_args_expected = $ref->getNumberOfParameters();
        //append filler array. (note: can't call array_fill with zero quantity - throws error)
        $values += (($diff = $num_args_expected - count($values)) > 0) ? array_fill(0,$diff,null) : array();

        call_user_func_array($callback, array_values($this->bind($values)));
        $this->after($method, $path);
        $buff = ob_get_clean();
    
        if ($method !== 'HEAD')
          echo $buff;
    
      } else {
        // nothing, so just 404
        $this->error(404, 'Page not found');
    }
    
  }
  
  /**
   * Entry point for the library.
   *
   * @param string $method optional, for testing in the cli
   * @param string $path optional, for testing in the cli
   *
   * @return void
   */
  public function dispatch() {
  
    // see if we were invoked with params
    $method = strtoupper($_SERVER['REQUEST_METHOD']);
    if ($method == 'POST') {
      if (isset($_SERVER['HTTP_X_HTTP_METHOD_OVERRIDE']))
        $method = $_SERVER['HTTP_X_HTTP_METHOD_OVERRIDE'];
      else
        $method = $this->params('_method') ? $this->params('_method') : $method;
    }
  
    // get the request_uri basename
    $path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
  
    // remove dir path if we live in a subdir
    if ($base = $this->config('dispatch.url')) 
    {
      $base = rtrim(parse_url($base, PHP_URL_PATH), '/');
      $path = preg_replace('@^'.preg_quote($base).'@', '', $path);
    }
    else {
      $base = rtrim(strtr(dirname($_SERVER['SCRIPT_NAME']),'\\','/' ) ,'/');    
      $path = preg_replace('@^'.preg_quote($base).'@', '', $path);
    }

    // remove router file from URI
    if ($stub = $this->config('dispatch.router')) {
      $stub = $this->config('dispatch.router');
      $path = preg_replace('@^/?'.preg_quote(trim($stub, '/')).'@i', '', $path);
    }

// ----------------------------------------------------------------------------
  //save request info for subsequent use
  $this->config('dispatch.request', 
    array('method' => $method, 
      'path' => $path, 
      'base' => $base,
      'host' => $_SERVER['SERVER_NAME'],
      'scheme' => isset($_SERVER['HTTPS']) && $_SERVER['HTTPS']=='on' ||
            ($this->request_headers('X-Forwarded-Proto')=='https')?'https':'http',
      'ajax' =>   $this->request_headers('X-Requested-With') &&
            $this->request_headers('X-Requested-With') == 'XMLHttpRequest'
      ));
// ----------------------------------------------------------------------------
    
    // dispatch it
    $this->on($method, $path);
  }
  
///////////////////////////////////////////////////////////////////////////////
  /**
   * Dispatch::autoload()
   * 
   * Namespace aware autoloader. 
   * Takes semi-colon separated sets of directory paths.
   * 
   * @param mixed $class
   * @return
   */
  protected function autoload($class) {
   
    $class = $this->fixslashes(ltrim($class,'\\'));
    
    foreach($this->split($this->config('dispatch.plugins')
                .';'.$this->config('dispatch.autoload')) as $auto)
    {
      //run through the directory list trying the 3 upper/lower case options
      if (is_file($file = $auto.$class.'.php') ||
        is_file($file = $auto.strtolower($class).'.php') ||
        is_file($file = strtolower($auto.$class).'.php'))
      {
        return require($file);
      }
    }
  }
  
  /**
   * Dispatch::fixslashes()
   * 
   * Convert backslashes to slashes
   * 
   * @param mixed $str
   * @return
   */
  protected function fixslashes($str) {
    return $str ? strtr($str,'\\','/') : $str;
  }
  
  /**
   * Dispatch::split()
   * 
   * Split semi-colon separated string into an array. Trim spaces and remove empty array elements.
   * 
   * @param mixed $str
   * @return
   */
  protected function split($str) {
    return array_map('trim', preg_split('/[;]/',$str,-1,PREG_SPLIT_NO_EMPTY));
  }
  

} //EOC

/**
 * Singleton Class
 * 
 * Base singleton class that is extended to create single instance classes.
 * Idea from end of: http://www.guzaba.org/page/singleton-implementation-php53.html
 * but with the bugs fixed so it works!
 * 
 */
abstract class Singleton
{
  //array of instances. Set as protected rather than private so can be
  //accessed in testing (e.g. to force an object to be re-created)
  protected static $instances;

  /**
   * Singleton::__construct()
   * 
   * @return void
   */
  public function __construct() {
    $c = get_class($this);
  
    if(isset(self::$instances[$c])) {
      trigger_error ('You can not create more than one copy of a singleton.',E_USER_ERROR);
    } 
    else {
      self::$instances[$c] = $this;
    }
  }

  /**
   * Singleton::instance()
   * 
   * @return
   */
  public static function instance() {
    $c = get_called_class();
    
    if (!isset(self::$instances[$c])) {
      $args = func_get_args();
      $reflection_object = new ReflectionClass($c);
      self::$instances[$c] = $reflection_object->newInstanceArgs($args);
    }
    
    return self::$instances[$c];
  }

  /**
   * Singleton::__clone()
   * 
   * @return void
   */
  public function __clone() {
    trigger_error ('You can not clone a singleton.',E_USER_ERROR);
  }
  
} //EOC

/* end */