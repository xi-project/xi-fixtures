<?php
namespace Xi\Fixtures;

class BidirectionalReferencesTest extends TestCase
{
    /**
     * @test
     */
    public function bidirectionalOntToManyReferencesAreAssignedBothWays()
    {
        $this->factory->define('SpaceShip');
        $this->factory->define('Person')
            ->reference('spaceShip', 'SpaceShip');
        
        $person = $this->factory->get('Person');
        $ship = $person->getSpaceShip();
        
        $this->assertTrue($ship->getCrew()->contains($person));
    }
    
    /**
     * @test
     */
    public function unidirectionalReferencesWorkAsUsual()
    {
        $this->factory->define('Person');
        $this->factory->define('Badge')
            ->reference('owner', 'Person');

        $this->assertTrue($this->factory->get('Badge')->getOwner() instanceof TestEntity\Person);
    }
    
    /**
     * @test
     */
    public function whenTheOneSideIsASingletonItMayGetSeveralChildObjects()
    {
        $this->factory->define('SpaceShip');
        $this->factory->define('Person')
            ->reference('spaceShip', 'SpaceShip');
        
        $ship = $this->factory->getAsSingleton('SpaceShip');
        $p1 = $this->factory->get('Person');
        $p2 = $this->factory->get('Person');
        
        $this->assertTrue($ship->getCrew()->contains($p1));
        $this->assertTrue($ship->getCrew()->contains($p2));
    }
}
