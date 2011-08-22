<?php
namespace Xi\Doctrine\Fixtures;

class ReferenceTest extends TestCase
{
    /**
     * @test
     */
    public function referencedObjectShouldBeCreatedAutomatically()
    {
        $this->factory->defineEntity('SpaceShip');
        $this->factory->defineEntity('Person', array(
            'name' => 'John',
            'spaceShip' => FieldDef::reference('SpaceShip')
        ));
        
        $ss1 = $this->factory->get('Person')->getSpaceShip();
        $ss2 = $this->factory->get('Person')->getSpaceShip();
        
        $this->assertNotNull($ss1);
        $this->assertNotNull($ss2);
        $this->assertNotSame($ss1, $ss2);
    }
}
