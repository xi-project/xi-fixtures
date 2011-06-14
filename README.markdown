# Xi Doctrine

A set of utility classes for integration with Doctrine. This package is part of
the Xi project.

Zend FirePHP logger
-------------------

Using the Zend FirePHP logger requires

* Zend Framework's Zend_Wildfire_Plugin_FirePhp component and all of it's dependencies.
* Firebug FirePHP plugin (http://www.firephp.org)

After installing the dependencies using the Zend FirePHP logger is easy. Just
set the Zend FirePHP logger as a Doctrine SQL logger.

        /** @var $config Doctrine\ORM\Configuration */
        $config->setSQLLogger(new Xi\Doctrine\DBAL\Logging\ZendFirePhpLogger());

Done! Your Firebug console should now log SQL queries ran by Doctrine.
