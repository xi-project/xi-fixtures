<?php
namespace Xi\Doctrine\Fixtures;

/**
 * Creates Doctrine entities for use in tests.
 * 
 * See the README file for a tutorial.
 */
class FixtureFactory
{
    /**
     * @var \Doctrine\ORM\EntityManager
     */
    protected $em;
    
    /**
     * @var string
     */
    protected $entityNamespace;
    
    /**
     * @var array<EntityDef>
     */
    protected $entityDefs;
    
    /**
     * @var array
     */
    protected $singletons;
    
    /**
     * @var boolean
     */
    protected $persist;
    
    /**
     * @var boolean
     */
    protected $flush;


    public function __construct(\Doctrine\ORM\EntityManager $em)
    {
        $this->em = $em;
        
        $this->entityNamespace = '';
        
        $this->entityDefs = array();
        
        $this->singletons = array();
        
        $this->persist = false;
        $this->flush = false;
    }
    
    /**
     * Sets the namespace to be prefixed to all entity names passed to this class.
     */
    public function setEntityNamespace($namespace)
    {
        $this->entityNamespace = trim($namespace, '\\');
    }
    
    public function getEntityNamespace()
    {
        return $this->entityNamespace;
    }
    
    
    /**
     * Get an entity and its dependencies.
     * 
     * Whether the entity is new or not depends on whether you've created
     * a singleton with the entity name. See `getAsSingleton()`.
     * 
     * If you've called `persistAndFlushOnGet()`then the entity is also
     * persisted and the entity manager flushed.
     */
    public function get($name, array $fields = array())
    {
        if (isset($this->singletons[$name])) {
            return $this->singletons[$name];
        }
        
        $def = $this->entityDefs[$name];
        $entityType = $def->getEntityType();
        $metadata = $this->em->getClassMetadata($entityType);
        
        $extraFields = array_diff(array_keys($fields), array_keys($def->getFieldDefs()));
        if (!empty($extraFields)) {
            throw new \Exception("Field(s) not in $entityType: '" . implode("', '", $extraFields) . "'");
        }
        
        $ent = $metadata->newInstance();
        foreach ($def->getFieldDefs() as $fieldName => $fieldDef) {
            if ($metadata->isCollectionValuedAssociation($fieldName)) {
                $metadata->setFieldValue($ent, $fieldName, new \Doctrine\Common\Collections\ArrayCollection());
            } else {
                if (isset($fields[$fieldName])) {
                    $value = $fields[$fieldName];
                } else {
                    $value = $fieldDef($this);
                }
                $metadata->setFieldValue($ent, $fieldName, $value);
            }
        }
        
        if ($this->persist) {
            $this->em->persist($ent);
            if ($this->flush) {
                $this->em->flush();
            }
        }
        
        return $ent;
    }
    
    /**
     * Sets whether `get()` should automatically persist the entity it creates
     * and flush the entity manager. By default it does not.
     */
    public function persistAndFlushOnGet($enabled = true)
    {
        $this->persist = $enabled;
        $this->flush = $enabled;
    }
    
    /**
     * A shorthand combining `get()` and `setSingleton()`.
     * 
     * It's illegal to call this if `$name` already has a singleton.
     */
    public function getAsSingleton($name, array $fields = array())
    {
        if (isset($this->singletons[$name])) {
            throw new \Exception("Already a singleton: $name");
        }
        $this->singletons[$name] = $this->get($name, $fields);
        return $this->singletons[$name];
    }
    
    /**
     * Sets `$entity` to be the singleton for `$name`.
     * 
     * This causes `get($name)` to return `$entity`.
     */
    public function setSingleton($name, $entity)
    {
        $this->singletons[$name] = $entity;
    }
    
    /**
     * Unsets the singleton for `$name`.
     * 
     * This causes `get($name)` to return new entities again.
     */
    public function unsetSingleton($name)
    {
        unset($this->singletons[$name]);
    }
    
    /**
     * Defines how to create a default entity of type `$name`.
     * 
     * See the readme for a tutorial.
     * 
     * @return FixtureFactory
     */
    public function defineEntity($name, array $params = array())
    {
        if (isset($this->entityDefs[$name])) {
            throw new \Exception("Entity '$name' already defined in fixture factory");
        }
        
        $type = $this->addNamespace($name);
        if (!class_exists($type, true)) {
            throw new \Exception("Not a class: $type");
        }
        
        $metadata = $this->em->getClassMetadata($type);
        if (!isset($metadata)) {
            throw new \Exception("Unknown entity type: $type");
        }
        
        $this->entityDefs[$name] = new EntityDef($this->em, $name, $type, $params);
        
        return $this;
    }
    
    private function addNamespace($name)
    {
        $name = trim($name, '\\');
        return $this->entityNamespace . '\\' . $name;
    }
}
