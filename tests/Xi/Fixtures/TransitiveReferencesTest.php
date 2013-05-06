<?php

namespace Xi\Fixtures;

class TransitiveReferencesTest extends TestCase
{
    public function setUp()
    {
        parent::setUp();
        
        $this->factory->define('Person')
            ->reference('spaceShip', 'SpaceShip');

        $this->factory->define('Badge')
            ->reference('owner', 'Person');

        $this->factory->define('SpaceShip');
    }
    
    /**
     * @test
     */
    public function referencesGetInstantiatedTransitively()
    {
        $badge = $this->factory->get('Badge');
        
        $this->assertNotNull($badge->getOwner()->getSpaceShip());
    }
    
    /**
     * @test
     */
    public function transitiveReferencesWorkWithSingletons()
    {
        $this->factory->getAsSingleton('SpaceShip');
        $badge1 = $this->factory->get('Badge');
        $badge2 = $this->factory->get('Badge');
        
        $this->assertNotSame($badge1->getOwner(), $badge2->getOwner());
        $this->assertSame($badge1->getOwner()->getSpaceShip(), $badge2->getOwner()->getSpaceShip());
    }
}
