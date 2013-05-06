<?php

namespace Xi\Fixtures;

use Doctrine\Common\Collections\ArrayCollection;

class BasicUsageTest extends TestCase
{
    /**
     * @test
     */
    public function acceptsConstantValuesInEntityDefinitions()
    {
        $this->factory
            ->define('SpaceShip')
            ->field('name', 'My BattleCruiser');
        $ss = $this->factory->get('SpaceShip');
        
        $this->assertEquals('My BattleCruiser', $ss->getName());
    }
    
    /**
     * @test
     */
    public function acceptsGeneratorFunctionsInEntityDefinitions()
    {
        $name = "Star";
        $this->factory
            ->define('SpaceShip')
            ->field('name', function () use (&$name) { return "M/S $name"; });
        
        $this->assertEquals('M/S Star', $this->factory->get('SpaceShip')->getName());

        $name = "Superstar";
        $this->assertEquals('M/S Superstar', $this->factory->get('SpaceShip')->getName());
    }
    
    /**
     * @test
     */
    public function valuesCanBeOverriddenAtCreationTime()
    {
        $this->factory
            ->define('SpaceShip')
            ->field('name', 'My BattleCruiser');

        $ss = $this->factory->get('SpaceShip', array('name' => 'My CattleBruiser'));
        $this->assertEquals('My CattleBruiser', $ss->getName());
    }
    
    /**
     * @test
     */
    public function doesNotCallTheConstructorOfTheEntity()
    {
        $this->factory->define('SpaceShip');
        $ss = $this->factory->get('SpaceShip');
        $this->assertFalse($ss->constructorWasCalled());
    }
    
    /**
     * @test
     */
    public function instantiatesCollectionAssociationsToBeEmptyCollections()
    {
        $this->factory
            ->define('SpaceShip')
            ->field('name', 'Battlestar Galaxy');
        $ss = $this->factory->get('SpaceShip');
        
        $this->assertTrue($ss->getCrew() instanceof ArrayCollection);
        $this->assertTrue($ss->getCrew()->isEmpty());
    }
    
    /**
     * @test
     */
    public function unspecifiedFieldsAreLeftNull()
    {
        $this->factory->define('SpaceShip');
        $this->assertNull($this->factory->get('SpaceShip')->getName());
    }

    /**
     * @test
     */
    public function entityIsDefinedToDefaultNamespace()
    {
        $this->factory->define('SpaceShip');
        $this->factory->define('Person\User');

        $this->assertEquals(
            'Xi\Fixtures\TestEntity\SpaceShip',
            get_class($this->factory->get('SpaceShip'))
        );

        $this->assertEquals(
            'Xi\Fixtures\TestEntity\Person\User',
            get_class($this->factory->get('Person\User'))
        );
    }

    /**
     * @test
     */
    public function entityCanBeDefinedToAnotherNamespace()
    {
        $this->factory->define(
            '\Xi\Fixtures\TestAnotherEntity\Artist'
        );

        $this->assertEquals(
            'Xi\Fixtures\TestAnotherEntity\Artist',
            get_class($this->factory->get(
                '\Xi\Fixtures\TestAnotherEntity\Artist'
            ))
        );
    }
}
