# Kohana Simple LDAP #

## A Kohana module for "Simple" LDAP authentication ##

What is it?
----------------------------------------------------------
This module provides "simple" LDAP authentication for your application.  I say simple because it does not require a "bind" account like other LDAP modules, but simply attempts to bind a username/password ( and optionally, domain ) to a defined and configured LDAP server. If the bind was successful, we are authenticated, if not we keep trying the other servers in our LDAP stratigies until we find a successful bind ( if any ).  

How does it work?
---------------------------------------------------------
We simply authenticate the user against a single LDAP strategy or multiple stratigies as defined in the `config/simple-ldap.php` config.  Once we are able to successfully bind to a an LDAP server using the supplied credentials, then the user is authenticated.

Example Usage:
---------------------------------------------------------

As a standalone script:
`
$ldap = new SimpleLDAP();
$ldap->config = include('../config/simple-ldap.php');
$ldap->authenticate('jdoe', 'CORP', 'somepassword');
`


Requirements:
-----------------------------------------------------------
1. Kohana 3+ if you are using Kohana
2. PHP 5.3+ with LDAP modules compiled

How to setup:
-----------------------------------------------------------

1. Clone this into your modules directory
2. Enable the module in bootstrap.php
3. Extend AuthORM to use the simple-ldap class
