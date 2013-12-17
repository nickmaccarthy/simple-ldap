<?php defined('SYSPATH') or die('No direct access allowed.');

/**
 *  Simple LDAP Auth Driver
 *
 *  @package    simple-ldap
 *  @author     Nick MacCarthy
 *  @copyright  (c) 2013 Nick MacCarthy 
 *  @license    GPL v3
 *
 *
 *  Usage:
 * 
 *      $username = "jdoe";
 *      $password = "somep4ssw0rd";
 *      $domain = "CORP";
 *
 *      $ldap = new SimpleLDAP();
 *
 *      $auth = $ldap->authenticate($useranme, $domain, $password);
 *
 *      if ( $auth )
 *      {
 *          echo "User: $username has been successfully authenticated!
 *      }
 *      else
 *      {
 *          echo "Invalid username, password or domain";
 *      }
 *  
 *  todo: map/return ldap error codes
 *
 *
 */


class SimpleLDAP {

    /**
     *  should always be false unless we successfuly authenticated
     */
    private $authenticated = FALSE;

    /**
     *  Our connection
     */
    private $conn = null;

    /**
     *  Our config - typically loaded by Kohanas config load, but can easily be overidden by:
     *
     *  $ldap = new SimpleLDAP();
     *  $ldap->config = include('ldap.config.php');
     *
     */
    private $config = array();

    /**
     *  Set a singly ldap strategy for us to use
     *
     *  Example:
     *
     *  $ldap = new SimpleLDAP();
     *  $ldap->strategy = 'some.ldap.server.com';
     *  $ldap->autenticate('jdoe', 'CORP', 'secretpassword');
     *
     */
    private $strategy = FALSE;

    /**
     *  what our LDAP error is 
     *  todo: map and return these properly
     */
    public $ldap_error = null;

    /**
     *
     *  Load our LDAP config 
     *
     */
    public function __construct()
    {

        // Open our config
        if ( ! count($this->config) )
        {
            $this->config = Kohana::$config->load('simple-ldap');
        }

        return $this;
    }
    
    /**
     *
     *  Disconnect our LDAP connection
     *
     */
    public function __destruct()
    {
        $this->disconnect();
    }

    /**
     *  
     *  connects to our ldap server
     *
     *  @param array $props - array containng our LDAP properties from ldap.conf.php
     *  @return object $this->conn
     *
     */
    public function connect($props)
    {
        $prefix = "ldaps";
        $ldap_host = "127.0.0.1";
        $ldap_port = "636";

        if ( isset($props['ldaps']) AND $props['ldaps'] )
        {
            $prefix = "ldaps";
        }
        else
        {
            $prefix = "ldap";
        }

        $ldap_host = trim($props['host']);
        $ldap_port = trim($props['port']);

        // example:  ldaps://127.0.0.1:639
        $host = "$prefix://$ldap_host:$ldap_port";

        $this->conn = ldap_connect($host);
    }

    /**
     *
     *  disconnects our ldap connection
     *
     *  @return object this
     */
    public function disconnect()
    {
        ldap_close($this->conn);

        return $this;
    }

    /**
     *
     *  authenticates our user against our ldap strategy.
     *  
     *  @param  $username   string      - the username
     *  @param  $domain     string      - the domain
     *  @param  $password   string      - the password
     *  @return $bind       bool        - true for authenticated, false for not
     */
    public function authenticate($username, $domain = null, $password)
    {

        $bind = FALSE;

        // If we have a domain for us, then lets put the two together like so "CORP\jdoe" and authetnicate that way - useful for AD authentication
        if ( ! is_null($domain))
        {
            $username = strtoupper($domain) . "\\" . $username;
        }

        if ( ! $this->strategy )
        {
            // Go through each of our LDAP stratigies and attempt to bind.  Once we can we will break out and return true for a successful auth
            foreach($this->config['stratigies'] as $svr => $attrs)
            {

                $this->connect($attrs);

                $bind = $this->bind($username, $password);

                // If our bind was successful, no need to go any further, we were authenticated
                if ( $bind )  break;
            }
        }
        else
        {
            $bind = $this->bind($username, $password);
        }

        return $bind;
    }

    /**
     *
     *  set our ldap strategy to use
     *
     *  @param string $name - name of the LDAP strategy we want to set
     *  @return object this
     */
    public function set_strategy($name)
    {

        $this->stragegy = $this->config['stratigies'][$name];

        $this->connect($this->stragegy['host']);

        return $this;
    }

    /** 
     *
     *  binds our user to the ldap server.  if the bind was successful we are authenticated
     *  
     *  @param string $username 
     *  @param string $password
     *  @return bool 
     */
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
            return FALSE;
        }

        return FALSE;
    }
    

}
