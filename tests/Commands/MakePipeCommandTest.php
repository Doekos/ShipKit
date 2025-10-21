<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;

function cleanupPipes(): void
{
    $pipesPath = app_path('Pipes');

    if (File::isDirectory($pipesPath)) {
        File::deleteDirectory($pipesPath);
    }

    $stubsPath = base_path('stubs');
    if (File::exists($stubsPath)) {
        File::deleteDirectory($stubsPath);
    }
}

beforeEach(fn () => cleanupPipes());
afterEach(fn () => cleanupPipes());

it('creates a new pipe file', function (): void {
    $name = 'SanitizeInputPipe';

    $exitCode = Artisan::call('make:pipe', ['name' => $name]);

    expect($exitCode)->toBe(0);

    $expectedPath = app_path('Pipes/'.$name.'.php');
    expect(File::exists($expectedPath))->toBeTrue();

    $content = File::get($expectedPath);

    expect($content)
        ->toContain('namespace App\\Pipes;')
        ->toContain('final readonly class '.$name)
        ->toContain('use Closure;')
        ->toContain('public function handle(')
        ->toContain('Closure $next')
        ->toContain('return $next');
});

it('fails when the pipe already exists', function (): void {
    $name = 'SanitizeInputPipe';
    Artisan::call('make:pipe', ['name' => $name]);
    $exitCode = Artisan::call('make:pipe', ['name' => $name]);

    expect($exitCode)->toBe(1);
});

it('adds suffix "Pipe" to the name when not provided', function (string $name): void {
    $exitCode = Artisan::call('make:pipe', ['name' => $name]);

    expect($exitCode)->toBe(0);

    $expectedPath = app_path('Pipes/SanitizeInputPipe.php');
    expect(File::exists($expectedPath))->toBeTrue();

    $content = File::get($expectedPath);

    expect($content)
        ->toContain('namespace App\\Pipes;')
        ->toContain('class SanitizeInputPipe')
        ->toContain('public function handle(');
})->with([
    'SanitizeInput',
    'SanitizeInput.php',
]);

it('uses published stub when available', function (): void {
    $this->artisan('vendor:publish', ['--tag' => 'shipkit-stubs'])
        ->assertSuccessful();

    $publishedStubPath = base_path('stubs/pipe.stub');
    $originalContent = File::get($publishedStubPath);
    File::put($publishedStubPath, $originalContent."\n// this is user modified stub for pipe");

    $name = 'TestPublishedStubPipe';
    $this->artisan('make:pipe', ['name' => $name])
        ->assertSuccessful();

    $expectedPath = app_path('Pipes/TestPublishedStubPipe.php');
    expect(File::exists($expectedPath))->toBeTrue()
        ->and(File::get($expectedPath))->toContain(
            '// this is user modified stub for pipe'
        );
});
