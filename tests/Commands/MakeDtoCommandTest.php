<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;

beforeEach(function (): void {
    $dtoPath = app_path('DTOs');

    if (File::isDirectory($dtoPath)) {
        File::deleteDirectory($dtoPath);
    }

    $stubsPath = base_path('stubs');
    if (File::exists($stubsPath)) {
        File::deleteDirectory($stubsPath);
    }
});

afterEach(function (): void {
    $dtoPath = app_path('DTOs');

    if (File::isDirectory($dtoPath)) {
        File::deleteDirectory($dtoPath);
    }

    $stubsPath = base_path('stubs');
    if (File::exists($stubsPath)) {
        File::deleteDirectory($stubsPath);
    }
});

it('creates a new dto file', function (): void {
    $name = 'UserDTO';
    $exitCode = Artisan::call('make:dto', ['name' => $name]);

    expect($exitCode)->toBe(0);

    $expectedPath = app_path('DTOs/UserDTO.php');
    expect(File::exists($expectedPath))->toBeTrue();

    $content = File::get($expectedPath);

    expect($content)
        ->toContain('namespace App\DTOs')
        ->toContain('class UserDTO')
        ->toContain('public function __construct(');
});

it('Should fail when the DTO file already exists', function (): void {
    $name = 'UserDTO';
    Artisan::call('make:dto', ['name' => $name]);
    $exitCode = Artisan::call('make:dto', ['name' => $name]);

    expect($exitCode)->toBe(1);
});

it('Add suffix "DTO" to the dto name if not provided', function (string $dtoName): void {
    $exitCode = Artisan::call('make:dto', ['name' => $dtoName]);

    expect($exitCode)->toBe(0);

    $expectedPath = app_path('DTOs/UserDTO.php');
    expect(File::exists($expectedPath))->toBeTrue();

    $content = File::get($expectedPath);

    expect($content)
        ->toContain('namespace App\DTOs;')
        ->toContain('class UserDTO')
        ->toContain('public function __construct(');
})->with([
    'User',
    'User.php',
]);

it('uses published stub when available', function (): void {
    $this->artisan('vendor:publish', ['--tag' => 'shipkit-stubs'])
        ->assertSuccessful();

    $publishedStubPath = base_path('stubs/dto.stub');
    $originalContent = File::get($publishedStubPath);

    File::put($publishedStubPath, $originalContent."\n// this is user modified stub");

    $dtoName = 'TestPublishedStubDTO';
    $this->artisan('make:dto', ['name' => $dtoName])
        ->assertSuccessful();

    $expectedPath = app_path('DTOs/TestPublishedStubDTO.php');
    expect(File::exists($expectedPath))->toBeTrue()
        ->and(File::get($expectedPath))->toContain(
            '// this is user modified stub'
        );
});
