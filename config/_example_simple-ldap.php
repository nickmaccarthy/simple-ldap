<?php

return array(

    /**
     *
     *  Defines our LDAP strategies 
     *  
     *  Each strategy needs the following:
     *  
     *      host: hostname or IP address of the LDAP server
     *      port: port we need for the ldap server, typically 636 for LDAPS and 389 for standard LDAP
     *      ldaps: TRUE to use LDAPS support, FALSE for standard non secure LDAP
     *
     */
    'stratigies' => array(

            'east.ldap.company.com'   =>  array(
                        
                            'host'  =>  '10.20.40.253',
                            'port'  =>  '636',
                            'ldaps' =>  TRUE, 
            ),
            'west.ldap.company.com'   =>  array(

                            'host'  => '10.18.40.37',
                            'port'  => '636',
                            'ldaps' => TRUE,
            ),

    ),

);
