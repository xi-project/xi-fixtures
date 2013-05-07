<?php
namespace Xi\Fixtures\FixtureFactory;

use Xi\Fixtures\FixtureFactory;
use Xi\Fixtures\FieldDef;

/**
 * Defines the methods that come after `define()` in FixtureFactory's DSL.
 */
class DSL
{
    /**
     * @var FixtureFactory
     */
    protected $factory;

    /**
     * @var string
     */
    protected $name;

    /**
     * @var array
     */
    protected $fields;

    /**
     * @var array
     */
    protected $options;

    public function __construct(FixtureFactory $factory, $name)
    {
        $this->factory = $factory;
        $this->name = $name;
        $this->fields = array();
        $this->options = array();
    }

    /**
     * Internal method. Don't call this manually.
     */
    public function _finish()
    {
        $this->factory->defineEntity($this->name, $this->fields, $this->options);
    }

    /**
     * Specifies the entity class in case the name given to `define()` is not the name of an entity class.
     *
     * @param string $type
     * @return DSL
     */
    public function entityType($type)
    {
        $this->options['entityType'] = $type;
        return $this;
    }

    /**
     * Defines a field as a constant or via a callback.
     *
     * @param string $name The name of the field.
     * @param mixed|callable $value The value of the field, or a function to calculate it.
     * @return DSL
     */
    public function field($name, $value)
    {
        $this->fields[$name] = $value;
        return $this;
    }

    /**
     * Defines a field to be a string based on an incrementing integer.
     *
     * This is typically used to generate unique names such as usernames.
     *
     * The parameter may be a function that receives a counter value
     * each time the entity is created or it may be a string.
     *
     * If the parameter is a string string containing "%d" then it will be
     * replaced by the counter value. If the string does not contain "%d"
     * then the number is simply appended to the parameter.
     *
     * @param string $name The name of the field.
     * @param callable|string $funcOrString The function or pattern to generate a value from.
     * @param int $firstNum The first number to use.
     * @return DSL
     */
    public function sequence($name, $funcOrString, $firstNum = 1)
    {
        return $this->field($name, FieldDef::sequence($funcOrString, $firstNum));
    }


    /**
     * Defines a field which is filled by `get()`'ing a named entity from the factory.
     *
     * The usual semantics of `get()` apply.
     * Normally this means that the field gets a fresh instance of the named
     * entity. If a singleton has been defined, `get()` will return that.
     *
     * @param string $name The name of the entity to get.
     * @param string $otherName The name of another entity in the factory.
     * @return DSL
     */
    public function reference($name, $otherName)
    {
        return $this->field($name, FieldDef::reference($otherName));
    }

    /**
     * Sets a callback that is invoked after the entity is created.
     *
     * The callback may take two parameters: the entity that was just created
     * and an array mapping field names to their values. The callback
     * is free to modify the entity. Its return value is ignored.
     *
     * @param callable $callback The callback, taking the entity and an array of values.
     * @return DSL
     */
    public function afterCreate($callback)
    {
        $this->options['afterCreate'] = $callback;
        return $this;
    }
}
