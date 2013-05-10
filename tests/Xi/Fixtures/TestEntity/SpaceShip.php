<?php
namespace Xi\Fixtures\TestEntity;

use Doctrine\Common\Collections\ArrayCollection;

/**
 * @Entity
 */
class SpaceShip
{
    /**
     * @Id
     * @GeneratedValue(strategy="AUTO")
     * @Column(type="integer")
     */
    protected $id;
    
    /** @Column */
    protected $name;
    
    /**
     * @OneToMany(targetEntity="Person", mappedBy="spaceShip")
     */
    protected $crew;

    /**
     * @var Person[]
     * @ManyToMany(targetEntity="Person", inversedBy="shipsVisited")
     * @JoinTable(name="space_ship_visitors")
     */
    protected $pastVisitors;
    
    /**
     * @var boolean
     */
    protected $constructorWasCalled = false;
    
    
    public function __construct($name)
    {
        $this->name = $name;
        $this->crew = new ArrayCollection();
        $this->pastVisitors = new ArrayCollection();
        $this->constructorWasCalled = true;
    }
    
    public function getId()
    {
        return $this->id;
    }

    public function getName()
    {
        return $this->name;
    }
    
    public function setName($name)
    {
        $this->name = $name;
    }

    public function getCrew()
    {
        return $this->crew;
    }

    public function getPastVisitors()
    {
        return $this->pastVisitors;
    }
    
    public function constructorWasCalled()
    {
        return $this->constructorWasCalled;
    }
}
