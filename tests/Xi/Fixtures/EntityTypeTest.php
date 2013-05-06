<?php
namespace Xi\Fixtures;

class EntityTypeTest extends TestCase
{
    /**
     * @test
     */
    public function entityTypeCanBeOverridden()
    {
        $this->factory->define('Captain')
            ->fromEntity('Person')
            ->field('name', 'TheCaptain');

        $cap = $this->factory->get('Captain');
        $this->assertEquals('TheCaptain', $cap->getName());
    }

    /**
     * @test
     */
    public function entityTypeCanBeSetWithExplicitNamespace()
    {
        $this->factory->setEntityNamespace('Xi\Fixtures\AnotherNamespace');

        $this->factory->define('Captain')
            ->fromEntity('\Xi\Fixtures\TestEntity\Person')
            ->field('name', 'TheCaptain');

        $cap = $this->factory->get('Captain');
        $this->assertEquals('TheCaptain', $cap->getName());
    }

    /**
     * @test
     */
    public function sameTypeOfEntityCanBeDefinedWithTwoNames()
    {
        $this->factory->define('Captain')
            ->fromEntity('Person')
            ->field('name', 'TheCaptain');

        $this->factory->define('Sailor')
            ->fromEntity('Person')
            ->sequence('name', 'Sailor #%d');

        $s1 = $this->factory->get('Sailor');
        $s2 = $this->factory->get('Sailor');
        $cap = $this->factory->get('Captain');

        $this->assertEquals('TheCaptain', $cap->getName());
        $this->assertEquals('Sailor #1', $s1->getName());
        $this->assertEquals('Sailor #2', $s2->getName());
    }
}
