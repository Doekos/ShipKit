<?php

declare(strict_types=1);

use Illuminate\Support\Facades\File;

beforeEach(function (): void {
    if (file_exists(base_path('rector.php'))) {
        unlink(base_path('rector.php'));
    }

    if (file_exists(base_path('rector.php.backup'))) {
        unlink(base_path('rector.php.backup'));
    }
});

it('publishes rector configuration file without a backup by default', function (): void {
    $this->artisan('shipkit:rector', ['--force' => true])
        ->assertExitCode(0);

    expect(file_exists(base_path('rector.php')))->toBeTrue();
    expect(file_exists(base_path('rector.json.backup')))->toBeFalse();
});

it('returns error when rector configuration file does not exist', function (): void {
    File::shouldReceive('exists')
        ->once()
        ->andReturnFalse();

    $this->artisan('shipkit:rector', ['--force' => true])
        ->expectsOutputToContain('Rector configuration stub file not found.')
        ->assertExitCode(1);
});

it('returns error when copy operation fails', function (): void {
    File::shouldReceive('exists')->andReturnTrue();
    File::shouldReceive('copy')
        ->once()
        ->andReturnFalse();

    $this->artisan('shipkit:rector', ['--force' => true])
        ->assertExitCode(1);
});

it('creates a backup when requested', function (): void {
    File::put(base_path('rector.php'), '<?php return [];');

    $this->artisan('shipkit:rector', ['--backup' => true, '--force' => true])
        ->assertExitCode(0);

    expect(file_exists(base_path('rector.php.backup')))->toBeTrue();
});

it('warns when file exists and no force option', function (): void {
    File::put(base_path('rector.php'), '<?php return [];');

    $this->artisan('shipkit:rector')
        ->expectsConfirmation('Do you wish to publish the Rector configuration file? This will override the existing [rector.php] file.', 'no')
        ->assertExitCode(0);

    expect(file_get_contents(base_path('rector.php')))->toBe('<?php return [];');
});

it('publishes rector configuration file when user confirms', function (): void {
    File::put(base_path('rector.php'), '<?php return [];');

    $this->artisan('shipkit:rector')
        ->expectsConfirmation('Do you wish to publish the Rector configuration file? This will override the existing [rector.php] file.', 'yes')
        ->assertExitCode(0);

    expect(file_get_contents(base_path('rector.php')))
        ->not()
        ->toBe('<?php return [];');
});

afterEach(function (): void {
    if (file_exists(base_path('rector.php'))) {
        unlink(base_path('rector.php'));
    }

    if (file_exists(base_path('rector.php.backup'))) {
        unlink(base_path('rector.php.backup'));
    }
});
