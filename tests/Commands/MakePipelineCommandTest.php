<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;

function cleanupPipelines(): void
{
    $pipelinesPath = app_path('Pipelines');

    if (File::isDirectory($pipelinesPath)) {
        File::deleteDirectory($pipelinesPath);
    }

    $stubsPath = base_path('stubs');
    if (File::exists($stubsPath)) {
        File::deleteDirectory($stubsPath);
    }
}

beforeEach(fn () => cleanupPipelines());
afterEach(fn () => cleanupPipelines());

it('creates a new pipeline file', function (): void {
    $name = 'UserRegistrationPipeline';

    $exitCode = Artisan::call('make:pipeline', ['name' => $name]);

    expect($exitCode)->toBe(0);

    $expectedPath = app_path('Pipelines/'.$name.'.php');
    expect(File::exists($expectedPath))->toBeTrue();

    $content = File::get($expectedPath);

    expect($content)
        ->toContain('namespace App\\Pipelines;')
        ->toContain('final readonly class '.$name)
        ->toContain('use Illuminate\\Pipeline\\Pipeline;')
        ->toContain('private array $pipes = [')
        ->toContain('public function handle(')
        ->toContain('->through($this->pipes)');
});

it('fails when the pipeline already exists', function (): void {
    $name = 'UserRegistrationPipeline';
    Artisan::call('make:pipeline', ['name' => $name]);
    $exitCode = Artisan::call('make:pipeline', ['name' => $name]);

    expect($exitCode)->toBe(1);
});

it('adds suffix "Pipeline" to the name when not provided', function (string $name): void {
    $exitCode = Artisan::call('make:pipeline', ['name' => $name]);

    expect($exitCode)->toBe(0);

    $expectedPath = app_path('Pipelines/UserRegistrationPipeline.php');
    expect(File::exists($expectedPath))->toBeTrue();

    $content = File::get($expectedPath);

    expect($content)
        ->toContain('namespace App\\Pipelines;')
        ->toContain('class UserRegistrationPipeline')
        ->toContain('public function handle(');
})->with([
    'UserRegistration',
    'UserRegistration.php',
]);

it('uses published stub when available', function (): void {
    $this->artisan('vendor:publish', ['--tag' => 'shipkit-stubs'])
        ->assertSuccessful();

    $publishedStubPath = base_path('stubs/pipeline.stub');
    $originalContent = File::get($publishedStubPath);
    File::put($publishedStubPath, $originalContent."\n// this is user modified stub for pipeline");

    $name = 'TestPublishedStubPipeline';
    $this->artisan('make:pipeline', ['name' => $name])
        ->assertSuccessful();

    $expectedPath = app_path('Pipelines/TestPublishedStubPipeline.php');
    expect(File::exists($expectedPath))->toBeTrue()
        ->and(File::get($expectedPath))->toContain(
            '// this is user modified stub for pipeline'
        );
});
