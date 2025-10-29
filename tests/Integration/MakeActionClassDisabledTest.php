<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Artisan;

class DisabledMakeActionTestCase extends Shipkit\Tests\TestCase
{
    /**
     * Define environment setup to disable MakeAction before service provider boots.
     */
    protected function defineEnvironment($app): void
    {
        $app['config']->set('shipkit.'.Shipkit\Configurables\MakeAction::class, false);
    }
}

uses(DisabledMakeActionTestCase::class);

it('does not register make:action when disabled', function (): void {
    $commands = array_keys(Artisan::all());

    expect($commands)->not->toContain('make:action');
});

it('reports MakeAction configurable as disabled', function (): void {
    $makeActionConfigurable = app()->make(Shipkit\Configurables\MakeAction::class);

    expect($makeActionConfigurable->enabled())->toBeFalse();
});
