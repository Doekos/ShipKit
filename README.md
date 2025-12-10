# ShipKit

ShipKit is a small helper package to help you ship Laravel apps faster and more consistently. It provides:

- Curated configuration toggles applied at boot (safe-by-default production settings)
- Artisan generators for Actions, DTOs, Pipes, Pipelines, and Services with opinionated stubs
- Simple commands to publish Pint, PHPStan, and Rector config files
- A command to append useful Composer test scripts to your project
- Publishable stubs and config so you can customize everything to your taste

## Requirements
- PHP: >= 8.4
- Laravel: 12.x (auto-discovered service provider)
- PHPStan (optional)
- Rector (optional)

## Installation
Install via Composer:

```bash
composer require doekos/shipkit --dev
```

That’s it. The service provider is auto-discovered.

If you want to customize defaults:

### Publish config/shipkit.php
```bash
php artisan vendor:publish --tag=shipkit-config
```
### Publish /stubs used by generators
```bash
php artisan vendor:publish --tag=shipkit-stubs
```

## What ShipKit configures for you
These “configurables” run automatically during application boot. You can enable/disable each one via config/shipkit.php.

- Prohibit destructive database commands (enabled by default)
  - Prevents destructive DB commands depending on environment.
  - Toggle: Shipkit\Configurables\ProhibitDestructiveCommands::class => true

- Strict Eloquent mode in non-production (enabled by default)
  - Calls Model::shouldBeStrict() outside production to catch lazy loading, silently discarded attributes, and access to missing attributes.
  - Toggle: Shipkit\Configurables\ShouldBeStrict::class => true

- Unguard Eloquent models (disabled by default)
  - Calls Model::unguard() to allow mass assignment for all models.
  - Toggle: Shipkit\Configurables\Unguard::class => false

- Make Action generator availability (enabled by default)
  - Controls whether the make:action command is registered.
  - Toggle: Shipkit\Configurables\MakeAction::class => true

- Modular approach for generators (disabled by default)
  - Enables the optional module-aware directory structure and requires the --module option on all generator commands.
  - Toggle: Shipkit\Configurables\ModularApproach::class => false

Update the booleans in config/shipkit.php to suit your needs.

## Artisan Generators
ShipKit adds a couple of handy generators that use publishable stubs. All generator commands support an optional --module flag when the Modular Approach is enabled (see the next section).

### make:action
Create an Action class under app/Actions. The name is normalized to end with "Action".

### app/Actions/CreateUserAction.php
```bash
php artisan make:action CreateUser
```

Default stub contents wrap handle() in a DB transaction. You can customize by publishing stubs and editing stubs/action.stub.

### make:dto
Create a DTO class under app/DTOs. The name is normalized to end with "DTO".

### app/DTOs/UserDTO.php
```bash
php artisan make:dto User
```

The default stub provides a readonly class with a promoted constructor. Customize via stubs/dto.stub after publishing stubs.

### make:pipe
Create a Pipe class under app/Pipes. The name is normalized to end with "Pipe".

### app/Pipes/SanitizeInputPipe.php
```bash
php artisan make:pipe SanitizeInput
```

The default stub exposes a handle(mixed $payload, Closure $next): mixed method and forwards the payload using return $next($payload). Customize via stubs/pipe.stub.

### make:pipeline
Create a Pipeline class under app/Pipelines. The name is normalized to end with "Pipeline".

### app/Pipelines/ProcessOrderPipeline.php
```bash
php artisan make:pipeline ProcessOrder
```

The default stub includes a private array $pipes = [] and injects Illuminate\Pipeline\Pipeline using constructor property promotion. The handle(mixed $payload): mixed method sends the payload through the defined pipes and returns the result. Customize via stubs/pipeline.stub.

### make:service
Create a Service class under app/Services. The name is normalized to end with "Service".

### app/Services/BillingService.php
```bash
php artisan make:service Billing
```

The default stub includes a handle(): void method. Customize via stubs/service.stub.

## Config File Publishers
ShipKit can drop in sensible defaults for common tooling. Each command supports:
- --force to skip the interactive confirm prompt
- --backup to create a .backup file if the destination exists

### shipkit:pint
Publish pint.json to the project root.

```bash
php artisan shipkit:pint --force
```

### shipkit:phpstan
Publish phpstan.neon.dist to the project root.

```bash
php artisan shipkit:phpstan --force
```

### shipkit:rector
Publish rector.php to the project root.

```bash
php artisan shipkit:rector --force
```

### shipkit:composer
Append test and fix scripts to your composer.json.

```bash
php artisan shipkit:composer --force
```

This merges the "scripts" section from ShipKit’s stub into your existing composer.json without removing anything you already have.

## Customizing Stubs
Publish the stubs once and tailor them to your project:

```bash
php artisan vendor:publish --tag=shipkit-stubs
```

This creates a stubs/ directory in your project with at least:
- stubs/action.stub (used by make:action)
- stubs/dto.stub (used by make:dto)
- stubs/pipe.stub (used by make:pipe)
- stubs/pipeline.stub (used by make:pipeline)
- stubs/service.stub (used by make:service)

When present, your local stubs are always preferred over the package defaults.

## Testing and QA scripts
If you use shipkit:composer, you’ll get a convenient set of scripts in composer.json, for example:

- composer test — runs unit tests, Pint (test mode), PHPStan, and Rector (dry-run)
- composer fix — runs Pint and Rector to auto-fix styling and refactors

## Publishing config
To toggle configurables:

```bash
php artisan vendor:publish --tag=shipkit-config
```

## Modular Approach for Generators
ShipKit supports an optional modular directory structure for all generator commands (Action, DTO, Pipe, Pipeline, Service).

- Enable it by setting the following in config/shipkit.php:
  - Shipkit\Configurables\ModularApproach::class => true
- When enabled:
  - You must pass --module=<ModuleName> to all make:action, make:dto, make:pipe, make:pipeline, and make:service commands.
  - Classes are generated under App\Modules\\<ModuleName>\\<SubNamespace> and stored at app/Modules/<ModuleName>/<SubNamespace>/<ClassName>.php.
  - The <ModuleName> is normalized to StudlyCase (e.g., user_management => UserManagement).
- When disabled (default):
  - The --module option is not allowed and the command will exit with an error if provided.
  - Classes are generated under the conventional namespaces (e.g., App\\Actions, App\\DTOs, etc.).

### Examples

```bash
# Modular enabled (set Shipkit\Configurables\ModularApproach::class => true in config/shipkit.php)
php artisan make:action CreateUser --module=UserManagement
# => App\Modules\UserManagement\Actions\CreateUserAction
#    app/Modules/UserManagement/Actions/CreateUserAction.php

php artisan make:dto User --module=Billing
# => App\Modules\Billing\DTOs\UserDTO
#    app/Modules/Billing/DTOs/UserDTO.php
```

Behavioral safeguards:
- If modular is enabled but you omit --module, the command exits with an error: "The --module option is required when modular approach is enabled."
- If modular is disabled and you pass --module, the command exits with an error: "Modular approach is disabled; remove the --module option."

Your published stubs (via shipkit-stubs) are still respected regardless of modular settings.

## Contributing
PRs and issues are welcome. Please run the provided QA scripts (composer test and composer fix) before submitting.

