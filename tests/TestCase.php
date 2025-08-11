<?php

namespace MityDigital\Feedamic\Tests;

use Illuminate\Support\Facades\File;
use MityDigital\Feedamic\ServiceProvider;
use Statamic\Facades\AssetContainer;
use Statamic\Facades\Blueprint;
use Statamic\Statamic;
use Statamic\Testing\AddonTestCase;
use Statamic\Testing\Concerns\PreventsSavingStacheItemsToDisk;

abstract class TestCase extends AddonTestCase
{
    use PreventsSavingStacheItemsToDisk;

    protected array $fixtures = ['Models', 'Scopes'];

    protected $fakeStacheDirectory = __DIR__.'/__fixtures__/dev-null';

    protected string $addonServiceProvider = ServiceProvider::class;

    protected bool $shouldFakeVersion = true;

    protected function setUp(): void
    {
        parent::setUp();

        if (! file_exists($this->fakeStacheDirectory)) {
            mkdir($this->fakeStacheDirectory, 0777, true);
        }
    }

    protected function getEnvironmentSetUp($app)
    {
        parent::getEnvironmentSetUp($app);

        foreach ($this->fixtures as $folder) {
            File::copyDirectory(
                __DIR__.'/__fixtures__/app/'.$folder, app_path($folder)
            );
        }
    }

    protected function resolveApplicationConfiguration($app)
    {
        parent::resolveApplicationConfiguration($app);

        $app['config']->set('statamic.editions.pro', true);
        $app['config']->set('filesystems.disks.assets', [
            'driver' => 'local',
            'root' => public_path('assets'),
            'url' => '/assets',
            'visibility' => 'public',
            'throw' => false,
        ]);

        Statamic::booted(function () {
            Blueprint::setDirectory(__DIR__.'/__fixtures__/resources/blueprints');

            $assets = AssetContainer::make('assets');
            $assets->disk('assets');
            $assets->title('Assets');
            $assets->save();

            File::copyDirectory(__DIR__.'/__fixtures__/public/assets', public_path('assets'));
        });
    }

    protected function tearDown(): void
    {
        File::deleteDirectory(base_path('content'));
        File::delete(resource_path('sites.yaml'));

        foreach ($this->fixtures as $folder) {
            File::deleteDirectory(app_path($folder));
        }

        parent::tearDown();
    }
}
