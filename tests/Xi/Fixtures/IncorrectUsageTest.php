<?php
namespace Xi\Fixtures;

class IncorrectUsageTest extends TestCase
{
    /**
     * @test
     */
    public function throwsWhenTryingToDefineTheSameEntityTwice()
    {
        $this->factory->define('SpaceShip');

        $self = $this;
        $this->assertThrows(function() use ($self) {
            $self->factory->define('SpaceShip');
        });
    }
    
    /**
     * @test
     */
    public function throwsWhenTryingToDefineEntitiesThatAreNotEvenClasses()
    {
        $self = $this;
        $this->assertThrows(function() use ($self) {
            $self->factory->define('NotAClass');
            $self->factory->get('NotAClass');
        });
    }
    
    /**
     * @test
     */
    public function throwsWhenTryingToDefineEntitiesThatAreNotEntities()
    {
        $this->assertTrue(class_exists('Xi\Fixtures\TestEntity\NotAnEntity', true));
        
        $self = $this;
        $this->assertThrows(function() use ($self) {
            $self->factory->define('NotAnEntity');
            $self->factory->get('NotAnEntity');
        });
    }
    
    /**
     * @test
     */
    public function throwsWhenTryingToDefineNonexistentFields()
    {
        $self = $this;
        $this->assertThrows(function() use ($self) {
            $self->factory->define('SpaceShip')
                ->field('pieType', 'blueberry');

            $self->factory->get('SpaceShip');
        });
    }
    
    /**
     * @test
     */
    public function throwsWhenTryingToGiveNonexistentFieldsWhileConstructing()
    {
        $this->factory->define('SpaceShip')
            ->field('name', 'Alpha');

        $self = $this;
        $this->assertThrows(function() use ($self) {
            $self->factory->get('SpaceShip', array(
                'pieType' => 'blueberry'
            ));
        });
    }
    
    /**
     * @test
     */
    public function throwsWhenTryingToGetFixtureThatIsNotDefined()
    {
        $self = $this;
        $this->assertThrows(function() use ($self) {
            $self->factory->get('Undefined');
        });
    }
}
