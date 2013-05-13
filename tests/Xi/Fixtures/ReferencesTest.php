<?php
namespace Xi\Fixtures;

class ReferencesTest extends TestCase
{
    /**
     * @test
     * @dataProvider persistAndDontPersist
     */
    public function bidirectionalOneToManyReferencesAreAssignedBothWays($persistOnGet)
    {
        $this->factory->persistOnGet($persistOnGet);

        $this->factory->define('SpaceShip');
        $this->factory->define('Person')
            ->reference('spaceShip', 'SpaceShip');
        
        $person = $this->factory->get('Person');
        $ship = $person->getSpaceShip();
        
        $this->assertTrue($ship->getCrew()->contains($person));
    }
    
    /**
     * @test
     * @dataProvider persistAndDontPersist
     */
    public function unidirectionalReferencesWorkAsUsual($persistOnGet)
    {
        $this->factory->persistOnGet($persistOnGet);

        $this->factory->define('Person');
        $this->factory->define('Badge')
            ->reference('owner', 'Person');

        $this->assertTrue($this->factory->get('Badge')->getOwner() instanceof TestEntity\Person);
    }
    
    /**
     * @test
     * @dataProvider persistAndDontPersist
     */
    public function whenTheOneSideIsASingletonItMayGetSeveralChildObjects($persistOnGet)
    {
        $this->factory->persistOnGet($persistOnGet);

        $this->factory->define('SpaceShip');
        $this->factory->define('Person')
            ->reference('spaceShip', 'SpaceShip');
        
        $ship = $this->factory->getAsSingleton('SpaceShip');
        $p1 = $this->factory->get('Person');
        $p2 = $this->factory->get('Person');
        
        $this->assertTrue($ship->getCrew()->contains($p1));
        $this->assertTrue($ship->getCrew()->contains($p2));
    }

    /**
     * @test
     * @dataProvider persistAndDontPersist
     */
    public function referenceManyWorksWithOneToManyAssociations($persistOnGet)
    {
        $this->factory->persistOnGet($persistOnGet);

        $this->factory->define('Person');
        $this->factory->define('SpaceShip')
            ->referenceMany('crew', 'Person', 'spaceShip', 3);

        $ship = $this->factory->get('SpaceShip');

        $this->assertCount(3, $ship->getCrew());
        foreach ($ship->getCrew() as $person) {
            $this->assertSame($ship, $person->getSpaceShip());
        }
    }
}
