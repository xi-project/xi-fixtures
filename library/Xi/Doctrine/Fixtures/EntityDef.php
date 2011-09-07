<?php
namespace Xi\Doctrine\Fixtures;

/**
 * An internal class that `FixtureFactory` uses to normalize and store entity definitions in.
 */
class EntityDef
{
    private $name;
    
    private $entityType;
    
    /**
     * @var \Doctrine\ORM\Mapping\ClassMetadata
     */
    private $metadata;
    
    private $fieldDefs;
    
    public function __construct(\Doctrine\ORM\EntityManager $em, $name, $type, array $fieldDefs = array())
    {
        $this->name = $name;
        $this->entityType = $type;
        $this->metadata = $em->getClassMetadata($type);
        
        $this->fieldDefs = array();
        
        $this->readFieldDefs($fieldDefs);
        $this->defaultDefsFromMetadata();
    }
    
    private function readFieldDefs(array $params)
    {
        foreach ($params as $key => $def) {
            if ($this->metadata->hasField($key) ||
                    $this->metadata->hasAssociation($key)) {
                $this->fieldDefs[$key] = $this->normalizeFieldDef($def);
            } else {
                throw new \Exception('No such field in ' . $this->entityType . ': ' . $key);
            }
        }
    }
    
    private function defaultDefsFromMetadata() {
        $allFields = array_merge($this->metadata->getFieldNames(), $this->metadata->getAssociationNames());
        foreach ($allFields as $fieldName) {
            if (!isset($this->fieldDefs[$fieldName])) {
                $this->fieldDefs[$fieldName] = function() { return null; };
            }
        }
    }
    
    public function getName()
    {
        return $this->name;
    }
    
    public function getEntityType()
    {
        return $this->entityType;
    }
    
    public function getFieldDefs()
    {
        return $this->fieldDefs;
    }
    
    public function getEntityMetadata()
    {
        return $this->metadata;
    }
    
    private function normalizeFieldDef($def)
    {
        if (is_callable($def)) {
            return $this->ensureInvokable($def);
        } else {
            return function() use ($def) { return $def; };
        }
    }
    
    private function ensureInvokable($f)
    {
        if (method_exists($f, '__invoke')) {
            return $f;
        } else {
            return function() use ($f) {
                return call_user_func_array($f, func_get_args());
            };
        }
    }
}
