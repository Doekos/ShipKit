<?php

declare(strict_types=1);

namespace Shipkit\Commands;

use Illuminate\Support\Str;

final class MakeActionCommand extends AbstractModularGeneratorCommand
{
    protected $signature = 'make:action
                            {name : The name of the action}
                            {--module= : The module to place the action in}';

    protected $name = 'make:action';

    protected $description = 'Create a new action class';

    protected $type = 'Action';

    protected function getNameInput(): string
    {
        /** @var string $name */
        $name = $this->argument('name');

        return Str::of(mb_trim($name))
            ->replaceEnd('.php', '')
            ->replaceEnd('Action', '')
            ->append('Action')
            ->toString();
    }

    protected function getStub(): string
    {
        return $this->resolveStubPath('/stubs/action.stub');
    }

    protected function subNamespace(): string
    {
        return 'Actions';
    }
}
