<?php
namespace Xi\Doctrine\Fixtures\TestEntity;

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
    
    
    public function __construct($name)
    {
        $this->name = $name;
        $this->crew = new \Doctrine\Common\Collections\ArrayCollection();
    }
    
    public function getId()
    {
        return $this->id;
    }

    public function getName()
    {
        return $this->name;
    }

    public function getCrew()
    {
        return $this->crew;
    }
}
