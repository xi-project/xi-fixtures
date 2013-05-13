<?php
namespace Xi\Fixtures;

class BidirectionalManyToManyTest extends TestCase
{
    protected function setUp()
    {
        parent::setUp();

        $this->factory
            ->define('SpaceShip')
            ->referenceMany('pastVisitors', 'Person', 'shipsVisited', 3);

        $this->factory
            ->define('Person')
            ->referenceMany('shipsVisited', 'SpaceShip', 'pastVisitors', 2);
    }

    /**
     * @test
     * @dataProvider persistAndDontPersist
     */
    public function owningSideCanCreateMultipleSubentitiesByDefault($persistOnGet)
    {
        $this->factory->persistOnGet($persistOnGet);

        $ss = $this->factory->get('SpaceShip');
        $this->assertCount(3, $ss->getPastVisitors());
        foreach ($ss->getPastVisitors() as $person) {
            $this->assertCount(1, $person->getShipsVisited());
            $this->assertContains($ss, $person->getShipsVisited());
        }
    }

    /**
     * @test
     * @dataProvider persistAndDontPersist
     */
    public function inverseSideCanCreateMultipleSubentitiesByDefault($persistOnGet)
    {
        $this->factory->persistOnGet($persistOnGet);

        $p = $this->factory->get('Person');
        $this->assertCount(2, $p->getShipsVisited());
        foreach ($p->getShipsVisited() as $ship) {
            $this->assertCount(1, $ship->getPastVisitors());
            $this->assertContains($p, $ship->getPastVisitors());
        }
    }

    /**
     * @test
     * @dataProvider persistAndDontPersist
     */
    public function specifyingTheOwningSideShouldUpdateTheInverseSide($persistOnGet)
    {
        $this->factory->persistOnGet($persistOnGet);

        $person1 = $this->factory->get('Person');
        $person2 = $this->factory->get('Person');
        $person3 = $this->factory->get('Person');

        $ship1 = $this->factory->get('SpaceShip', array(
            'pastVisitors' => array($person1, $person2)
        ));
        $ship2 = $this->factory->get('SpaceShip', array(
            'pastVisitors' => array($person2, $person3)
        ));

        $this->assertContains($ship1, $person1->getShipsVisited());
        $this->assertNotContains($ship2, $person1->getShipsVisited());

        $this->assertContains($ship1, $person2->getShipsVisited());
        $this->assertContains($ship2, $person2->getShipsVisited());

        $this->assertNotContains($ship1, $person3->getShipsVisited());
        $this->assertContains($ship2, $person3->getShipsVisited());

        $this->assertContains($person1, $ship1->getPastVisitors());
        $this->assertContains($person2, $ship1->getPastVisitors());
        $this->assertNotContains($person3, $ship1->getPastVisitors());

        $this->assertNotContains($person1, $ship2->getPastVisitors());
        $this->assertContains($person2, $ship2->getPastVisitors());
        $this->assertContains($person3, $ship2->getPastVisitors());
    }

    /**
     * @test
     * @dataProvider persistAndDontPersist
     */
    public function specifyingTheInverseSideShouldUpdateTheOwningSide($persistOnGet)
    {
        $this->factory->persistOnGet($persistOnGet);

        $ship1 = $this->factory->get('SpaceShip');
        $ship2 = $this->factory->get('SpaceShip');
        $ship3 = $this->factory->get('SpaceShip');

        $person1 = $this->factory->get('Person', array(
            'shipsVisited' => array($ship1, $ship2)
        ));
        $person2 = $this->factory->get('Person', array(
            'shipsVisited' => array($ship2, $ship3)
        ));

        $this->assertContains($person1, $ship1->getPastVisitors());
        $this->assertNotContains($person2, $ship1->getPastVisitors());

        $this->assertContains($person1, $ship2->getPastVisitors());
        $this->assertContains($person2, $ship2->getPastVisitors());

        $this->assertNotContains($person1, $ship3->getPastVisitors());
        $this->assertContains($person2, $ship3->getPastVisitors());

        $this->assertContains($ship1, $person1->getShipsVisited());
        $this->assertContains($ship2, $person1->getShipsVisited());
        $this->assertNotContains($ship3, $person1->getShipsVisited());

        $this->assertNotContains($ship1, $person2->getShipsVisited());
        $this->assertContains($ship2, $person2->getShipsVisited());
        $this->assertContains($ship3, $person2->getShipsVisited());
    }

    /**
     * @test
     * @dataProvider persistAndDontPersist
     * @expectedException \Exception
     * @expectedExceptionMessage Field pastVisitors of SpaceShip is defined to be a collection-valued association but its value is neither an array nor an instance of ArrayAccess.
     */
    public function settingTheInverseCollectionToSomethingWeirdShouldCauseAnException($persistOnGet)
    {
        $this->factory->persistOnGet($persistOnGet);

        $ship = $this->factory->get('SpaceShip');
        $ship->setPastVisitors('oops');
        $this->factory->get('Person', array(
            'shipsVisited' => array($ship)
        ));
    }
}
