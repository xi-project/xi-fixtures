<?php
namespace Xi\Fixtures;

class ExtraConfigurationTest extends TestCase
{
    /**
     * @test
     */
    public function canInvokeACallbackAfterObjectConstruction()
    {
        $this->factory->define('SpaceShip')
            ->field('name', 'Foo')
            ->afterCreate(function(TestEntity\SpaceShip $ss, array $fieldValues) {
                $ss->setName($ss->getName() . '-' . $fieldValues['name']);
            });

        $ss = $this->factory->get('SpaceShip');
        
        $this->assertEquals("Foo-Foo", $ss->getName());
    }
    
    /**
     * @test
     */
    public function theAfterCreateCallbackCanBeUsedToCallTheConstructor()
    {
        $this->factory->define('SpaceShip')
            ->field('name', 'Foo')
            ->afterCreate(function(TestEntity\SpaceShip $ss, array $fieldValues) {
                $ss->__construct($fieldValues['name'] . 'Master');
            });

        $ss = $this->factory->get('SpaceShip', array('name' => 'Xoo'));

        $this->assertTrue($ss->constructorWasCalled());
        $this->assertEquals('XooMaster', $ss->getName());
    }
}
