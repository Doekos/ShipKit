<?php

declare(strict_types=1);

use Illuminate\Support\Facades\File;

beforeEach(function (): void {
    // Clean up any existing pint.json files
    if (file_exists(base_path('pint.json'))) {
        unlink(base_path('pint.json'));
    }

    if (file_exists(base_path('pint.json.backup'))) {
        unlink(base_path('pint.json.backup'));
    }
});

it('publishes pint configuration file without a backup by default', function (): void {
    $this->artisan('shipkit:pint', ['--force' => true])
        ->assertExitCode(0);

    expect(file_exists(base_path('pint.json')))->toBeTrue();
    expect(file_exists(base_path('pint.json.backup')))->toBeFalse();
});

it('returns error when pint configuration file does not exist', function (): void {
    // The stub file should not exist
    File::shouldReceive('exists')
        ->once()
        ->andReturnFalse();

    $this->artisan('shipkit:pint', ['--force' => true])
        ->assertExitCode(1);
});

it('returns error when copy operation fails', function (): void {
    // The file should exist but not be copyable
    File::shouldReceive('exists')->andReturnTrue();
    File::shouldReceive('copy')
        ->once()
        ->andReturnFalse();

    $this->artisan('shipkit:pint', ['--force' => true])
        ->assertExitCode(1);
});

it('creates a backup when requested', function (): void {
    // Create a dummy pint.json file first
    File::put(base_path('pint.json'), '{"test": "original"}');

    $this->artisan('shipkit:pint', ['--backup' => true, '--force' => true])
        ->assertExitCode(0);

    expect(file_exists(base_path('pint.json.backup')))->toBeTrue();
});

it('warns when file exists and no force option', function (): void {
    // Create a dummy pint.json file first
    File::put(base_path('pint.json'), '{"test": "original"}');

    $this->artisan('shipkit:pint')
        ->expectsConfirmation('Do you wish to publish the Pint configuration file? This will override the existing [pint.json] file.', 'no')
        ->assertExitCode(0);

    // File should remain unchanged
    expect(file_get_contents(base_path('pint.json')))->toBe('{"test": "original"}');
});

it('publishes pint configuration file when user confirms', function (): void {
    // Create a dummy pint.json file first
    File::put(base_path('pint.json'), '{"test": "original"}');

    $this->artisan('shipkit:pint')
        ->expectsConfirmation('Do you wish to publish the Pint configuration file? This will override the existing [pint.json] file.', 'yes')
        ->assertExitCode(0);

    expect(file_exists(base_path('pint.json')))
        ->not()
        ->toBe('{"test": "original"}');
});

afterEach(function (): void {
    // Clean up any created files
    if (file_exists(base_path('pint.json'))) {
        unlink(base_path('pint.json'));
    }

    if (file_exists(base_path('pint.json.backup'))) {
        unlink(base_path('pint.json.backup'));
    }
});
