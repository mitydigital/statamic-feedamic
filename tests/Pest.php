<?php

/*
|--------------------------------------------------------------------------
| Test Case
|--------------------------------------------------------------------------
|
| The closure you provide to your test functions is always bound to a specific PHPUnit test
| case class. By default, that class is "PHPUnit\Framework\TestCase". Of course, you may
| need to change it using the "uses()" function to bind a different classes or traits.
|
*/

use MityDigital\Feedamic\Tests\TestCase;
use Statamic\Facades\Role;
use Statamic\Facades\User;
use Statamic\Statamic;

use function Pest\Laravel\actingAs;

uses(TestCase::class)->in('.');

/*
|--------------------------------------------------------------------------
| Expectations
|--------------------------------------------------------------------------
|
| When you're writing tests, you often need to check that values meet certain conditions. The
| "expect()" function gives you access to a set of "expectations" methods that you can use
| to assert different things. Of course, you may extend the Expectation API at any time.
|
*/

/*
|--------------------------------------------------------------------------
| Functions
|--------------------------------------------------------------------------
|
| While Pest is very powerful out-of-the-box, you may have some testing code specific to your
| project that you don't want to repeat in every file. Here you can also expose helpers as
| global functions to help you to reduce the number of lines of code in your test files.
|
*/
function createSuperUser()
{
    $user = User::make()
        ->makeSuper()
        ->set('name', 'Peter Parker')
        ->email('peter.parker@spiderman.com')
        ->set('password', 'secret')
        ->save();

    actingAs($user);

    return $user;
}

function createCpUser()
{
    $user = User::make()
        ->set('name', 'Peter Parker')
        ->email('peter.parker@spiderman.com')
        ->set('password', 'secret')
        ->save();

    actingAs($user);

    return $user;
}

function createFeedamicRole()
{
    return Role::make()
        ->title('Has Feedamic')
        ->handle('has-feedamic')
        ->permissions(['access cp', 'feedamic.config'])
        ->save();
}

function getPrivateProperty($className, $propertyName): ReflectionProperty
{
    $reflector = new ReflectionClass($className);
    $property = $reflector->getProperty($propertyName);
    $property->setAccessible(true);

    return $property;
}

function getStatamicVersion()
{
    if (! class_exists(Statamic::class)) {
        return 0;
    }

    $version = Statamic::version(); // returns string like "5.9.0"

    return (int) explode('.', $version)[0];
}
