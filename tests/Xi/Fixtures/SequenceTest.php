<?php

namespace Xi\Fixtures;

class SequenceTest extends TestCase
{
    /**
     * @test
     */
    public function sequenceGeneratorCallsAFunctionWithAnIncrementingArgument()
    {
        $this->factory->define('SpaceShip')
            ->sequence('name', function($n) { return "Alpha $n"; });

        $this->assertEquals('Alpha 1', $this->factory->get('SpaceShip')->getName());
        $this->assertEquals('Alpha 2', $this->factory->get('SpaceShip')->getName());
        $this->assertEquals('Alpha 3', $this->factory->get('SpaceShip')->getName());
        $this->assertEquals('Alpha 4', $this->factory->get('SpaceShip')->getName());
    }
    
    /**
     * @test
     */
    public function sequenceGeneratorCanTakeAPlaceholderString()
    {
        $this->factory->define('SpaceShip')
            ->sequence('name', 'Beta %d');

        $this->assertEquals('Beta 1', $this->factory->get('SpaceShip')->getName());
        $this->assertEquals('Beta 2', $this->factory->get('SpaceShip')->getName());
        $this->assertEquals('Beta 3', $this->factory->get('SpaceShip')->getName());
        $this->assertEquals('Beta 4', $this->factory->get('SpaceShip')->getName());
    }
    
    /**
     * @test
     */
    public function sequenceGeneratorCanTakeAStringToAppendTo()
    {
        $this->factory->define('SpaceShip')
            ->sequence('name', 'Gamma ');

        $this->assertEquals('Gamma 1', $this->factory->get('SpaceShip')->getName());
        $this->assertEquals('Gamma 2', $this->factory->get('SpaceShip')->getName());
        $this->assertEquals('Gamma 3', $this->factory->get('SpaceShip')->getName());
        $this->assertEquals('Gamma 4', $this->factory->get('SpaceShip')->getName());
    }
}
