<?php

use Illuminate\Contracts\Validation\ValidationRule;
use Statamic\Dictionaries\BasicDictionary;
use Statamic\Tags\Tags;

arch()->preset()->security();

arch('uses strict equality')
    ->expect('App')
    ->toUseStrictEquality();

test('debugs are removed')
    ->expect(['dd', 'dump', 'var_dump', 'ray'])
    ->not()
    ->toBeUsed();

test('contracts are interfaces')
    ->expect('src\Contracts')
    ->toBeInterfaces();

test('dictionaries extend the basic dictionary')
    ->expect('src\Dictionaries')
    ->toExtend(BasicDictionary::class);

arch('env function used only in config')
    ->expect([' env'])
    ->not()->toBeUsed();

test('rules implement the validation rule')
    ->expect('src\Rules')
    ->toImplement(ValidationRule::class);

test('tags extend the tag class')
    ->expect('src\Tags')
    ->toExtend(Tags::class);

arch('Do not access session data in Async jobs')
    ->expect([
        'session',
        'auth',
        'request',
        'Illuminate\Support\Facades\Auth',
        'Illuminate\Support\Facades\Session',
        'Illuminate\Http\Request',
        'Illuminate\Support\Facades\Request',
    ])
    ->each->not->toBeUsedIn('src\Jobs\Jobs');
