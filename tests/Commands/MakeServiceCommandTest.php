<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;

function cleanupServices(): void
{
    $servicesPath = app_path('Services');

    if (File::isDirectory($servicesPath)) {
        File::deleteDirectory($servicesPath);
    }

    $stubsPath = base_path('stubs');
    if (File::exists($stubsPath)) {
        File::deleteDirectory($stubsPath);
    }
}

beforeEach(fn () => cleanupServices());
afterEach(fn () => cleanupServices());

it('creates a new service file', function (): void {
    $name = 'BillingService';

    $exitCode = Artisan::call('make:service', ['name' => $name]);

    expect($exitCode)->toBe(0);

    $expectedPath = app_path('Services/'.$name.'.php');
    expect(File::exists($expectedPath))->toBeTrue();

    $content = File::get($expectedPath);

    expect($content)
        ->toContain('namespace App\\Services;')
        ->toContain('final readonly class '.$name)
        ->toContain('public function handle(): void');
});

it('fails when the service already exists', function (): void {
    $name = 'BillingService';
    Artisan::call('make:service', ['name' => $name]);
    $exitCode = Artisan::call('make:service', ['name' => $name]);

    expect($exitCode)->toBe(1);
});

it('adds suffix "Service" to the name when not provided', function (string $name): void {
    $exitCode = Artisan::call('make:service', ['name' => $name]);

    expect($exitCode)->toBe(0);

    $expectedPath = app_path('Services/BillingService.php');
    expect(File::exists($expectedPath))->toBeTrue();

    $content = File::get($expectedPath);

    expect($content)
        ->toContain('namespace App\\Services;')
        ->toContain('class BillingService')
        ->toContain('public function handle(): void');
})->with([
    'Billing',
    'Billing.php',
]);

it('uses published stub when available', function (): void {
    $this->artisan('vendor:publish', ['--tag' => 'shipkit-stubs'])
        ->assertSuccessful();

    $publishedStubPath = base_path('stubs/service.stub');
    $originalContent = File::get($publishedStubPath);
    File::put($publishedStubPath, $originalContent."\n// this is user modified stub for service");

    $name = 'TestPublishedStubService';
    $this->artisan('make:service', ['name' => $name])
        ->assertSuccessful();

    $expectedPath = app_path('Services/TestPublishedStubService.php');
    expect(File::exists($expectedPath))->toBeTrue()
        ->and(File::get($expectedPath))->toContain(
            '// this is user modified stub for service'
        );
});
