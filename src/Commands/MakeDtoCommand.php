<?php

declare(strict_types=1);

namespace Shipkit\Commands;

use Illuminate\Support\Str;

final class MakeDtoCommand extends AbstractModularGeneratorCommand
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $name = 'make:dto';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a new Data Transfer Object class';

    /**
     * The type of class being generated.
     *
     * @var string
     */
    protected $type = 'Dto';

    /**
     * Get the name input
     **/
    protected function getNameInput(): string
    {
        /** @var string $name */
        $name = $this->argument('name');

        return Str::of(mb_trim($name))
            ->replaceEnd('.php', '')
            ->replaceEnd('DTO', '')
            ->append('DTO')
            ->toString();
    }

    /**
     * Get the stub file for the generator
     **/
    protected function getStub(): string
    {
        return $this->resolveStubPath('/stubs/dto.stub');
    }

    protected function subNamespace(): string
    {
        return 'DTOs';
    }
}
