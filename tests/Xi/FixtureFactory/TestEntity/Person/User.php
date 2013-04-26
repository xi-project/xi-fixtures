<?php

namespace Xi\FixtureFactory\TestEntity\Person;

/**
 * @Entity
 */
class User
{
    /**
     * @Id
     * @GeneratedValue(strategy="AUTO")
     * @Column(type="integer")
     */
    protected $id;
}
