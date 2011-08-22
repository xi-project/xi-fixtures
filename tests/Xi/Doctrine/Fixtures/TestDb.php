<?php
namespace Xi\Doctrine\Fixtures;

class TestDb
{
    private static $instance;
    
    public static function get()
    {
        if (!self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    
    /**
     * @var \Doctrine\ORM\Configuration
     */
    private $doctrineConfig;
    
    private $connectionOptions;
    
    protected function __construct()
    {
        $cache = new \Doctrine\Common\Cache\ArrayCache;

        $config = new \Doctrine\ORM\Configuration;
        
        $config->setMetadataCacheImpl($cache);
        $config->setQueryCacheImpl($cache);
        
        $here = dirname(__FILE__);
        
        $driverImpl = $config->newDefaultAnnotationDriver($here . '/TestEntity');
        $config->setMetadataDriverImpl($driverImpl);
        
        $config->setProxyDir($here . '/TestProxy');
        $config->setProxyNamespace('Xi\Doctrine\Fixtures\TestProxy');

        $config->setAutoGenerateProxyClasses(true);

        $this->connectionOptions = array(
            'driver' => 'pdo_sqlite',
            'path' => ':memory:'
        );

        $this->doctrineConfig = $config;
    }
    
    public function createEntityManager()
    {
        $em = \Doctrine\ORM\EntityManager::create($this->connectionOptions, $this->doctrineConfig);
        $this->createSchema($em);
        return $em;
    }
    
    private function createSchema(\Doctrine\ORM\EntityManager $em)
    {
        $tool = new \Doctrine\ORM\Tools\SchemaTool($em);
        
        $entityPath = dirname(__FILE__) . '/TestEntity';
        
        $tool->createSchema($em->getMetadataFactory()->getAllMetadata());
    }
}
