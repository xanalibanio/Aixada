<?php
/** 
 * @package Aixada
 */ 

/*$slash = explode('/', getenv('SCRIPT_NAME'));
if (isset($slash[1])) {
    $app = getenv('DOCUMENT_ROOT') . '/' . $slash[1] . '/';
} else { // this happens when called by make
    $app = '';
}*/

require_once(__ROOT__. 'local_config'.DS.'config.php');

require_once(__ROOT__. 'FirePHPCore/lib/FirePHPCore/FirePHP.class.php');
ob_start(); // Starts FirePHP output buffering
$firephp = FirePHP::getInstance(true);

 DBWrap::get_instance()->debug = true;

/**
 * The following class implements caching. It takes ideas from 
 * George Schlossnagle, Advanced PHP Programming, but doesn't actually
 * use any of that code.
 *
 * @package Aixada
 * @subpackage caching
 */

class QueryCache {

    private $cachedir;
    private $filename;

    public function __construct($query_array)
    {
        $this->cachedir = configuration_vars::get_instance()->cache_dir;
        $this->filename = $this->cachedir . 
            (is_array($query_array) ? implode('_', $query_array) : $query_array);
    }

    public function exists()
    {
        return file_exists($this->filename);
        return true;
    }

    public function read()
    {
        global $firephp;
        $firephp->log($this->filename, 'reading from cache');

        return file_get_contents($this->filename);
    }

    public function write($contents)
    {
        /*
        global $firephp;
        $firephp->log($this->filename, 'writing to cache');
        $tmpfname = $this->cachedir . getmypid();
        $handle = fopen($tmpfname, "a");
        fwrite($handle, $contents);
        fclose($handle);
        rename($tmpfname, $this->filename);
        */
    }
}


/**
 * clears the cache of all queries that depend on the $table_name
 */
function clear_cache($table_name)
{
    if (!isset(configuration_vars::get_instance()->queries_reading[$table_name])) 
        return;
    $queries = configuration_vars::get_instance()->queries_reading[$table_name];
    $cachedir = configuration_vars::get_instance()->cache_dir;
    global $firephp;
    foreach ($queries as $query) {
        foreach (glob($cachedir . $query . "*") as $filename) {
            unlink($filename);
            $firephp->log($filename, 'removed query result');
        }
    }
}

?>