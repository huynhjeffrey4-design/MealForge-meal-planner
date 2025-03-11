<?php

require_once __DIR__ . '/setup.php';
require_once 'rb.php';

/**
 * Database connection manager for RedBeanPHP
 */
class DatabaseConnection
{
    private static $instance = null;
    private $isConnected = false;
	public $cstr = "";
    
    /**
     * Get singleton instance
     */
    public static function getInstance()
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    /**
     * Setup database connection with environment-aware configuration
     * 
     * @param array $config Optional configuration override
     * @return bool Connection success status
     */
    public function setup(array $config = [])
    {
        if ($this->isConnected) {
            return true;
        }
        
        $default = [
            'username' => env('DB_USERNAME', 'root'),
            'password' => env('DB_PASSWORD', 'password'),
            'host'     => env('DB_HOST', '127.0.0.1'),
            'database' => 'cse442_2025_spring_team_v_db',
            'port'     => '3306'
        ];
        
        $config = array_merge($default, $config);
        
        $conn_string = "mysql:host={$config['host']};port={$config['port']};dbname={$config['database']}";

		$this->cstr = $conn_string . " " . $config['username'] . " " . $config['password'];
        
        try {
		  if (!\R::testConnection()){
			\R::setup($conn_string, $config['username'], $config['password']);
		  }
		  $this->isConnected = \R::testConnection();
		  return $this->isConnected;
        } catch (\Exception $e) {
            // Log error or handle as needed
            throw "DB Connection failed: " . $e->getMessage() . "Config: " . json_encode($config);
			return false;
        }
    }
    
    /**
     * Close the database connection
     */
    public function close()
    {
        if ($this->isConnected) {
            \R::close();
            $this->isConnected = false;
        }
    }
    
    /**
     * Check if connected to database
     * 
     * @return bool Connection status
     */
    public function isConnected()
    {
        return $this->isConnected && \R::testConnection();
    }
}

if (!function_exists('env')) {
    /**
     * Gets the value of an environment variable
     *
     * @param string $key Environment variable name
     * @param mixed $default Default value if not found
     * @return mixed
     */
    function env($key, $default = null)
    {
        $value = getenv($key);
        if ($value === false) {
            return $default;
        }
        return $value;
    }
}
