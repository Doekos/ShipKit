<?php

declare(strict_types=1);

return [
    /*
    |--------------------------------------------------------------------------
    | Prohibit Destructive Commands
    |--------------------------------------------------------------------------
    |
    | This option allows you to prohibit destructive commands
    | from being run in your application. When enabled, the
    | framework will prevent commands that could potentially
    | destroy data from being run in your application.
    |
    | Enabled by default.
    |
    */

    Shipkit\Configurables\ProhibitDestructiveCommands::class => true,

    /*
    |--------------------------------------------------------------------------
    | Model should be strict
    |--------------------------------------------------------------------------
    |
    | This option allows you to enable strict mode for your
    | application. It will prevent lazy loading, silently discarding
    | attributes and prevents accessing missing attributes.
    |
    | Enabled by default.
    |
    */

    Shipkit\Configurables\ShouldBeStrict::class => true,

    /*
    |--------------------------------------------------------------------------
    | Unguard models
    |--------------------------------------------------------------------------
    |
    | This option allows you to enable unguard mode for your
    | models. When enabled, the framework will unguard
    | all models, allowing you to mass assign any attributes.
    |
    | Disabled by default.
    |
    */

    Shipkit\Configurables\Unguard::class => false,
];
