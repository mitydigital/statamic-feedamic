<?php

namespace App\Scopes;

use Statamic\Query\Scopes\Scope;

class MyQueryScope extends Scope
{
    public function apply($query, $values)
    {
        // do something
        $query->where('title', '=', 'Banana');
    }
}
