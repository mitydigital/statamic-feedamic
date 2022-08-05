<?php

namespace MityDigital\Feedamic\UpdateScripts\v2_2_0;

use Statamic\UpdateScripts\UpdateScript;

class CheckForViews extends UpdateScript
{
    public function shouldUpdate($newVersion, $oldVersion)
    {
        return $this->isUpdatingTo('2.2.0');
    }

    public function update()
    {
        // Check if the views have been published, and if so, request a manual check
        if (file_exists(resource_path('views/vendor/mitydigital/feedamic/atom.blade.php')))
        {
            $this->console()->alert('You have the Feedamic Atom blade file in your project: changes may be needed!');
        }
        if (file_exists(resource_path('views/vendor/mitydigital/feedamic/rss.blade.php')))
        {
            $this->console()->alert('You have the Feedamic RSS blade file in your project: changes may be needed!');
        }
    }
}