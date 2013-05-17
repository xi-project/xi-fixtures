# Xi Fixtures

Xi Fixtures provides convenient and scalable creation of Doctrine entities in tests. If you're familiar with [FactoryGirl](https://github.com/thoughtbot/factory_girl) for Ruby, then this is essentially the same thing for Doctrine/PHP.

[![Build Status](https://travis-ci.org/xi-project/xi-fixtures.png?branch=master)](https://travis-ci.org/xi-project/xi-fixtures)

### In a nutshell ###

Imagine we're setting up a test and need 3 users in the database. With Xi Fixtures we can specify in one place that each user needs a unique username and needs to belong to a group (via a one-to-many relation):

```php
$this->factory
    ->define('User')
    ->sequence('username', 'user_%d')
    ->field('administrator', false)
    ->reference('group', 'Group');
```

Now in our tests we can simply say:

```php
$user1 = $this->factory->get('User');
$user2 = $this->factory->get('User');

// We can selectively override attributes
$user3 = $this->factory->get('User', array('administrator' => true));

testStuffWith($user1, $user2, $user3);
```

### Motivation ###

Many web applications have non-trivial database structures with lots of dependencies between tables. One component of such an application might deal with entities from only one or two tables, but those entities may depend on a complex entity graph to be useful or to pass validation.

For instance, a `User` may be a member of a `Group`, which is part of an `Organization`, which in turn depends on five different tables describing who-knows-what about the organization. You are writing a component that changes the user's password and are currently uninterested in groups, organizations and their dependencies. How do you set up your test?

1. Do you create all dependencies for `Organization` and `Group` to get a valid `User` in your `setUp()`? No, that would be horribly tedious and verbose.
2. Do you make a shared fixture for all your tests that includes an example organization with satisifed dependencies? No, having loads of tests depend on a single fixture makes changing that fixture later difficult.
3. Do you use mock objects? Sure, but in many cases, however, the code you're testing interacts with the entities in such a complex way that mocking them sufficiently is impractical.

Xi Fixtures is a middle ground between *(1)* and *(2)*. You specify how to generate your entities and their dependencies in one central place but explicitly create them in your tests, overriding only the fields you want.

### Tutorial ###

We'll assume you have a base class for your tests that sets up a fresh `EntityManager` connected to a minimally initialized blank test database. A simple factory setup looks like this.

```php
<?php
use Xi\Fixtures\FixtureFactory;
use Xi\Fixtures\FieldDef;

abstract class TestCase extends \PHPUnit_Framework_TestCase
{
    protected $factory;
    
    public function setUp()
    {
        // ... (set up a blank database and $this->entityManager) ...
        
        $this->factory = new FixtureFactory($this->entityManager);
        $this->factory->setEntityNamespace('What\Ever'); // If applicable
        
        // Define that users have names like user_1, user_2, etc.,
        // that they are not administrators by default and
        // that they point to a Group entity.
        $this->factory
            ->define('User')
            ->sequence('username', 'user_%d')
            ->field('administrator', false)
            ->reference('group', 'Group');
        
        // Define a Group to just have a unique name as above.
        // The order of the definitions does not matter.
        $this->factory
            ->define('Group')
            ->sequence('name', 'group_%d');

        // If you want your created entities to be saved by default
        // then do the following. You can selectively re-enable or disable
        // this behavior in each test as well.
        // It's recommended to only enable this in tests that need it.
        // In any case, you'll need to call flush() yourself.
        $this->factory->persistOnGet();
    }
}
```

Now you can easily get entities and override fields relevant to your test case like this.

```php
<?php
class UserServiceTest extends TestCase
{
    // ...
    
    public function testChangingPasswords()
    {
        $user = $this->factory->get('User', array(
            'name' => 'John'
        ));
        $this->service->changePassword($user, 'xoo');
        $this->assertSame($user, $this->service->authenticateUser('john', 'xoo'));
    }
}
```

### Singletons ###

Sometimes your entity has a dependency graph with several references to some entity type. For instance, the application may have a concept of a "current organization" with users, groups, products, categories etc. belonging to an organization. By default `FixtureFactory` would create a new `Organization` each time one is needed, which is not always what you want. Sometimes you'd like each new entity to point to one shared `Organization`.

Your first reaction should be to avoid situations like this and specify the shared entity explicitly when you can't. If that isn't feasible for whatever reason, `FixtureFactory` allows you to make an entity a *singleton*. If a singleton exists for a type of entity then `get()` will return that instead of creating a new instance.

```php
<?php
class SomeTest extends TestCase
{
    public function setUp()
    {
        parent::setUp();
        $this->org = $this->factory->getAsSingleton('Organization');
    }
    
    public function testSomething()
    {
        $user1 = $this->factory->get('User');
        $user2 = $this->factory->get('User');
        
        // now $user1->getOrganization() === $user2->getOrganization() ...
    }
}
```

It's highly recommended to create singletons only in the setups of individual test classes and *NOT* in the base class of your tests.

### Many-to-many ###

FixtureFactory helps you get started in constructing many-to-many associations.

The following example creates a User that belongs to three Groups. Both sides of the association are updated.

```php
<?php
$factory
    ->define('User')
    ->referenceMany('group', 'Group', 'users', 3);
    // 'group' is the field in User
    // 'Group' is the target entity
    // 'users' is the inverse field in 'Group'
    // 3 is the default number of 'Group' entities to generate.

$user = $factory->get('User');
```

The above code also works if the association is one to many. This is an alternative to using `->reference()` from the 'many' side.


### Advanced ###

You can give an `afterCreate` callback to be called after an entity is created and its fields are set. Here you can, for instance, invoke the entity's constructor. `FixtureFactory` doesn't invoke the constructor by default since Doctrine doesn't either.

```php
<?php
$factory->define('User')
    ->sequence('username', 'user_%d')
    ->afterCreate(function(User $user, array $fieldValues) {
        $user->__construct($fieldValues['username']);
    });
```

You can define multiple versions of the same entity under different names with the `entityType` method.

```php
<?php
$factory->define('NormalUser')
    ->entityType('User')
    ->sequence('username', 'user_%d')
    ->field('administrator', false);

$factory->define('Administrator')
    ->entityType('User')
    ->sequence('username', 'user_%d')
    ->field('administrator', true);
```

### API reference ###

```php
<?php

// Defining entities
$factory->define('EntityName')
    ->field('simpleField', 'constantValue')
    ->field('generatedField', function($factory) { return ...; })
    
    ->sequence('sequenceField1', 'name-%d') // name-1, name-2, ...
    ->sequence('sequenceField2', 'name-')   // the same
    ->sequence('sequenceField3', function($n) { return "name-$n"; })
    
    ->reference('referenceField', 'OtherEntity')
    ->referenceMany('referenceField', 'OtherEntity', 'inverseField', $count)
    
    ->afterCreate(function($entity, $fieldValues) {
        // ...
    })
    
    ->entityType('Type') // or '\Namespaced\Type'

// Getting an entity (new or singleton)
$factory->get('EntityName', array('field' => 'value'));

// If you have set persistOnGet to true and still want an unpersisted Entity
$factory->getUnpersisted('EntityName', array('field' => 'value'));

// Singletons
$factory->getAsSingleton('EntityName', array('field' => 'value'));
$factory->setSingleton('EntityName', $entity);
$factory->unsetSingleton('EntityName');

// Configuration
$this->factory->setEntityNamespace('What\Ever');  // Default: empty
$this->factory->persistOnGet();                   // Default: don't persist
$this->factory->persistOnGet(false);
```

### Miscellaneous ###

- `FixtureFactory` and `DSL` are designed to be subclassable.
- With bidirectional one-to-many associations, the collection on the 'one'
  side will get updated as long as you've remembered to specify the
  `inversedBy` attribute in your mapping.
- If you share your Doctrine entity manager between tests then
  remember to clear its internal state between tests with `$em->clear()`.

### Change log ###

* 1.1.1
  - Added `referenceMany` and made one-to-many references specifiable on the many-side.

* 1.1
  - Deprecated legacy API, implemented DSL.

* 1.0
  - Initial release with legacy API.

