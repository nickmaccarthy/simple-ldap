<?php defined('SYSPATH') or die('No direct access allowed.');

/**
 *  Simple LDAP Auth Driver
 *
 *  @package    kohana-simple-ldap
 *  @author     Nick MacCarthy
 *  @copyright  (c) 2011 Stephen Eisenhauer
 *  @license    GPL v3
 */


class Kohana_Auth_SimpleLDAP extends Auth {

    private $authenticated = FALSE;

    private $conn = null;

    private $config = array();

    private $strategy = FALSE;

    public $ldap_error = null;

    public function __construct()
    {

        // Open our config
        $this->config = Kohana::$config->load('simple-ldap');

        var_dump($this->config);
        parent::__construct($config);

        //return $this;
    }

    public function __destruct()
    {
        $this->disconnect();
    }

    public function connect($host)
    {
        $host = "ldaps://$host";
        $this->conn = ldap_connect($host);
    }

    public function disconnect()
    {
        ldap_close($this->conn);

        return $this;
    }

    public function login($username, $domain = null, $password, $remember = FALSE)
    {
    
        if (empty($password))
            return FALSE;

        return $this->_login($username, $domain, $password, $remember);
    }

    public function _login($username, $domain = null, $password, $remember)
    {
        $authenticated = $this->authenticate($username, $domain, $password);

        if ( $authenticated )
            $this->complete_login($user);

        return FALSE;

    }

    public function password($username)
    {

        return '';
    }

    public function check_password($password)
    {

        $username = $this->get_user();

        if ( $username === FALSE )
        {
            return FALSE;
        }

        return ( $password === $this->password($username));
    }

    public function get_user($default = NULL)
    {
        return $this->_session->get($this->_config['session_key'], $default);
    }

    public function authenticate($username, $domain = null, $password)
    {
        if ( ! is_null($domain))
        {
            $username = strtoupper($domain) . "\\" . $username;
        }

        if ( ! $this->strategy )
        {
            foreach($this->config['stratigies'] as $svr => $attrs)
            {

                $this->connect($attrs['host']);

                $bind = $this->bind($username, $password);

                if ( $bind ) 
                    $this->authenticated = TRUE;
                    return TRUE;
            }
        }
        else
        {
            $auth = $this->bind($username, $password);
        }

        return $this->authenticated;
    }

    public function set_strategy($name)
    {

        $this->stragegy = $this->config['stratigies'][$name];

        $this->connect($this->stragegy['host']);

        return $this;
    }

    public function bind($username, $password)
    {

        if ( @ldap_bind($this->conn, $username, $password))
        {
            $this->authenticated = TRUE;
            return TRUE;
        }
        else
        {
            $this->ldap_error = ldap_error($this->conn);
            $this->authenticated = FALSE;
            $this->disconnect();
            return FALSE;
        }

        return $this->authenticated;
    }
    

}
