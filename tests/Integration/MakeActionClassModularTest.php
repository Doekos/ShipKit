<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;
use Shipkit\Configurables\ModularApproach;

class ModularMakeActionTestCase extends Shipkit\Tests\TestCase
{
    /**
     * Define environment setup to disable MakeAction before service provider boots.
     */
    protected function defineEnvironment($app): void
    {
        $app['config']->set('shipkit.'.Shipkit\Configurables\ModularApproach::class, true);
    }
}

uses(ModularMakeActionTestCase::class);

it('asserts ModularApproach config is true', function (): void {
    $modularApproachConfigurable = app()->make(Shipkit\Configurables\ModularApproach::class);

    expect($modularApproachConfigurable->enabled())->toBeTrue();
});

it('requires --module option when modular approach is enabled', function (): void {
    $exitCode = Artisan::call('make:action', [
        'name' => 'TestAction',
    ]);

    $output = Artisan::output();

    expect($exitCode)->toBe(1);
    expect($output)->toContain('The --module option is required when modular approach is enabled.');
});

it('creates action in specified module when modular approach is enabled', function (): void {
    config()->set('shipkit.' . ModularApproach::class, true);

    $actionName = 'TestAction';
    $moduleName = 'UserManagement';

    $expectedPath = app_path('Modules/'.$moduleName.'/Actions/'.$actionName.'.php');

    // Make sure previous runs don’t interfere
    if (File::exists($expectedPath)) {
        File::delete($expectedPath);
    }

    $exitCode = Artisan::call('make:action', [
        'name'    => $actionName,
        '--module' => $moduleName,
    ]);

    expect($exitCode)->toBe(0);

    expect(File::exists($expectedPath))->toBeTrue();

    $content = File::get($expectedPath);

    expect($content)
        ->toContain('namespace App\Modules\\'.$moduleName.'\Actions;')
        ->toContain('class '.$actionName)
        ->toContain('public function handle(): void');
});
