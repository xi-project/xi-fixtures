<?php

namespace Xi\Fixtures;

use Doctrine\ORM\EntityManager;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping\ClassMetadata;
use Xi\Fixtures\FixtureFactory\DSL;
use Xi\Fixtures\FixtureFactory\EntityDef;
use Exception;

/**
 * Creates Doctrine entities for use in tests.
 * 
 * See the README file for a tutorial.
 */
class FixtureFactory
{
    /**
     * @var EntityManager
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
     * @param EntityManager $em
     */
    public function __construct(EntityManager $em)
    {
        $this->em = $em;
        $this->entityNamespace = '';
        $this->entityDefs = array();
        $this->singletons = array();
        $this->persist = false;
    }
    
    /**
     * Sets the namespace to be prefixed to all entity names passed to this class.
     *
     * @param string $namespace
     */
    public function setEntityNamespace($namespace)
    {
        $this->entityNamespace = trim($namespace, '\\');
    }

    /**
     * @return string
     */
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
     * If you've called `persistOnGet()` then the entity is also persisted.
     *
     * @param  string $name
     * @param  array  $fieldOverrides
     * @return object
     */
    public function get($name, array $fieldOverrides = array())
    {
        $ent = $this->createFixture($name, $fieldOverrides);
        
        if ($this->persist) {
            $this->em->persist($ent);
        }
        
        return $ent;
    }
    
    /**
     * Works like `get()`, but never persists Entity.
     *
     * @param  string $name
     * @param  array  $fieldOverrides
     * @return object
     */
    public function getUnpersisted($name, array $fieldOverrides = array())
    {
        return $this->createFixture($name, $fieldOverrides);
    }
    
    protected function createFixture($name, array $fieldOverrides = array())
    {
        if (isset($this->singletons[$name])) {
            return $this->singletons[$name];
        }
        
        if (!isset($this->entityDefs[$name])) {
             throw new Exception(
                 "Fixture '$name' is undefined. Define it before calling get()"
             );
        }

        if ($this->entityDefs[$name] instanceof DSL) {
            $this->entityDefs[$name]->_finish();
        }
        
        $def = $this->entityDefs[$name];
        $config = $def->getConfig();
        
        $this->checkFieldOverrides($def, $fieldOverrides);
        
        $ent = $def->getEntityMetadata()->newInstance();
        $fieldValues = array();
        foreach ($def->getFieldDefs() as $fieldName => $fieldDef) {
            $fieldValues[$fieldName] = array_key_exists($fieldName, $fieldOverrides)
                ? $fieldOverrides[$fieldName]
                : $fieldDef($this);
        }
        
        foreach ($fieldValues as $fieldName => $fieldValue) {
            $this->setField($ent, $def, $fieldName, $fieldValue);
        }
        
        if (isset($config['afterCreate'])) {
            $config['afterCreate']($ent, $fieldValues);
        }
        
        return $ent;
    }
    
    protected function checkFieldOverrides(EntityDef $def, array $fieldOverrides)
    {
        $extraFields = array_diff(array_keys($fieldOverrides), array_keys($def->getFieldDefs()));
        if (!empty($extraFields)) {
            throw new Exception(
                "Field(s) not in " . $def->getEntityType() . ": '" . implode("', '", $extraFields) . "'"
            );
        }
    }
    
    protected function setField($ent, EntityDef $def, $fieldName, $newValue)
    {
        $metadata = $def->getEntityMetadata();

        if ($metadata->isCollectionValuedAssociation($fieldName)) {
            $value = $metadata->getFieldValue($ent, $fieldName);
            if ($value === null) {
                $value = new ArrayCollection();
                if (is_array($newValue) || $newValue instanceof \IteratorAggregate) {
                    foreach ($newValue as $v) {
                        $value[] = $v;
                        $this->updateOtherSideOfAssociation($ent, $metadata, $fieldName, $v);
                    }
                }
                $metadata->setFieldValue($ent, $fieldName, $value);
            }
        } else {
            $metadata->setFieldValue($ent, $fieldName, $newValue);

            if (is_object($newValue) && $metadata->isSingleValuedAssociation($fieldName)) {
                $this->updateOtherSideOfAssociation($ent, $metadata, $fieldName, $newValue);
            }
        }
    }
    
    /**
     * Sets whether `get()` should automatically persist the entity it creates.
     * By default it does not. In any case, you still need to call
     * flush() yourself.
     *
     * @param boolean $enabled
     */
    public function persistOnGet($enabled = true)
    {
        $this->persist = $enabled;
    }
    
    /**
     * A shorthand combining `get()` and `setSingleton()`.
     * 
     * It's illegal to call this if `$name` already has a singleton.
     *
     * @param  string    $name
     * @param  array     $fieldOverrides
     * @return object
     * @throws Exception
     */
    public function getAsSingleton($name, array $fieldOverrides = array())
    {
        if (isset($this->singletons[$name])) {
            throw new Exception("Already a singleton: $name");
        }

        return $this->singletons[$name] = $this->get($name, $fieldOverrides);
    }
    
    /**
     * Sets `$entity` to be the singleton for `$name`.
     * 
     * This causes `get($name)` to return `$entity`.
     *
     * @param string $name
     * @param object $entity
     */
    public function setSingleton($name, $entity)
    {
        $this->singletons[$name] = $entity;
    }
    
    /**
     * Unsets the singleton for `$name`.
     * 
     * This causes `get($name)` to return new entities again.
     *
     * @param string $name
     */
    public function unsetSingleton($name)
    {
        unset($this->singletons[$name]);
    }

    /**
     * Starts defining how to create an entity.
     *
     * @param  string    $name The name by which these entities can be retrieved.
     * @throws Exception
     * @return DSL
     */
    public function define($name)
    {
        if (isset($this->entityDefs[$name])) {
            throw new Exception("Entity '$name' already defined in fixture factory");
        }

        $this->entityDefs[$name] = $this->createDSL($name);
        return $this->entityDefs[$name];
    }

    /**
     * @param string $entityName
     * @return DSL
     */
    protected function createDSL($entityName)
    {
        return new DSL($this, $entityName);
    }

    /**
     * Please use `define()` instead.
     *
     * This method is retained for backwards compatibility and internal use.
     *
     * @deprecated This will be made private in 2.x.
     *
     * @param  string         $name The name of the entity to define.
     * @param  array          $fieldDefs An array mapping field names to functions or constant values.
     * @param  array          $config Configuration options.
     * @throws Exception
     * @return FixtureFactory
     */
    public function defineEntity($name, array $fieldDefs = array(), array $config = array())
    {
        if (isset($this->entityDefs[$name]) && !($this->entityDefs[$name] instanceof DSL)) {
            throw new Exception("Entity '$name' already defined in fixture factory");
        }

        $type = isset($config['entityType']) ? $config['entityType'] : $name;
        $type = $this->addNamespace($type);
        if (!class_exists($type, true)) {
            throw new Exception("Not a class: $type");
        }
        
        $metadata = $this->em->getClassMetadata($type);
        if (!isset($metadata)) {
            throw new Exception("Unknown entity type: $type");
        }
        
        $this->entityDefs[$name] = new EntityDef($this->em, $name, $type, $fieldDefs, $config);
        
        return $this;
    }
    
    /**
     * @param  string $name
     * @return string
     */
    protected function addNamespace($name)
    {
        $name = rtrim($name, '\\');

        if ($name[0] === '\\') {
            return $name;
        }

        return $this->entityNamespace . '\\' . $name;
    }

    protected function updateOtherSideOfAssociation($entityBeingCreated, ClassMetadata $metadata, $fieldName, $otherEntity)
    {
        $inverseField = $this->getInverseField($metadata, $fieldName);

        if ($inverseField) {
            $otherClass = get_class($otherEntity);
            $otherMetadata = $this->em->getClassMetadata($otherClass);
            $existingValue = $otherMetadata->getFieldValue($otherEntity, $inverseField);

            if ($otherMetadata->isCollectionValuedAssociation($inverseField)) {
                if ($existingValue === null) {
                    $otherMetadata->setFieldValue($otherEntity, $inverseField, new ArrayCollection($entityBeingCreated));
                } else if (is_array($existingValue) || $existingValue instanceof \ArrayAccess) {
                    $existingValue[] = $entityBeingCreated;
                } else {
                    // Ignore. Maybe the user is doing something strange.
                }
            } else {
                $otherMetadata->setFieldValue($otherEntity, $inverseField, $entityBeingCreated);
            }
        }
    }

    protected function getInverseField(ClassMetadata $metadata, $fieldName)
    {
        $assoc = $metadata->getAssociationMapping($fieldName);
        if (isset($assoc['inversedBy'])) {
            return $assoc['inversedBy'];
        } else if (isset($assoc['mappedBy'])) {
            return $assoc['mappedBy'];
        } else {
            return null;
        }
    }
}
