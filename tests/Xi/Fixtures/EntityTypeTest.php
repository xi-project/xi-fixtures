<?php
namespace Xi\Fixtures;

class EntityTypeTest extends TestCase
{
    /**
     * @test
     */
    public function entityTypeCanBeOverridden()
    {
        $this->factory->defineEntity('Captain', array(
            'name' => 'TheCaptain'
        ), array(
            'entityType' => 'Person'
        ));
        $cap = $this->factory->get('Captain');
        $this->assertEquals('TheCaptain', $cap->getName());
    }

    /**
     * @test
     */
    public function entityTypeCanBeSetWithExplicitNamespace()
    {
        $this->factory->setEntityNamespace('Xi\Fixtures\AnotherNamespace');

        $this->factory->defineEntity('Captain', array(
            'name' => 'TheCaptain'
        ), array(
            'entityType' => '\Xi\Fixtures\TestEntity\Person'
        ));
        $cap = $this->factory->get('Captain');
        $this->assertEquals('TheCaptain', $cap->getName());
    }

    /**
     * @test
     */
    public function sameTypeOfEntityCanBeDefinedWithTwoNames()
    {
        $this->factory->defineEntity('Captain', array(
            'name' => 'TheCaptain'
        ), array(
            'entityType' => 'Person'
        ));

        $this->factory->defineEntity('Sailor', array(
            'name' => FieldDef::sequence('Sailor #%d')
        ), array(
            'entityType' => 'Person'
        ));

        $s1 = $this->factory->get('Sailor');
        $cap = $this->factory->get('Captain');
        $s2 = $this->factory->get('Sailor');
        $this->assertEquals('TheCaptain', $cap->getName());
        $this->assertEquals('Sailor #1', $s1->getName());
        $this->assertEquals('Sailor #2', $s2->getName());
    }
}
