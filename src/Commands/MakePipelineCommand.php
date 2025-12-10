<?php

declare(strict_types=1);

namespace Shipkit\Commands;

use Illuminate\Support\Str;

final class MakePipelineCommand extends AbstractModularGeneratorCommand
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $name = 'make:pipeline';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a new Pipeline class';

    /**
     * The type of class being generated.
     *
     * @var string
     */
    protected $type = 'Pipeline';

    /**
     * Get the name input.
     */
    protected function getNameInput(): string
    {
        /** @var string $name */
        $name = $this->argument('name');

        return Str::of(mb_trim($name))
            ->replaceEnd('.php', '')
            ->replaceEnd('Pipeline', '')
            ->append('Pipeline')
            ->toString();
    }

    /**
     * Get the stub file for the generator.
     */
    protected function getStub(): string
    {
        return $this->resolveStubPath('/stubs/pipeline.stub');
    }

    protected function subNamespace(): string
    {
        return 'Pipelines';
    }
}
