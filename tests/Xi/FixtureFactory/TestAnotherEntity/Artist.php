<?php

namespace Xi\FixtureFactory\TestAnotherEntity;

/**
 * @Entity
 */
class Artist
{
    /**
     * @Id
     * @GeneratedValue(strategy="AUTO")
     * @Column(type="integer")
     */
    private $id;
}
