<?php

namespace Sourcecodeguy1\LaravelMcp\Tools;

use Illuminate\Support\Facades\File;
use ReflectionClass;
use ReflectionMethod;

class ListModelsTool extends Tool
{
    public function getName(): string
    {
        return 'list_models';
    }

    public function getDescription(): string
    {
        return 'List all Eloquent models in the application with their table name, fillable fields, hidden fields, and relationships.';
    }

    public function getInputSchema(): array
    {
        return [
            'type'       => 'object',
            'properties' => new \stdClass(),
        ];
    }

    public function execute(array $arguments): string
    {
        $modelsPath = file_exists(app_path('Models')) ? app_path('Models') : app_path();
        $models     = [];

        foreach (File::allFiles($modelsPath) as $file) {
            if ($file->getExtension() !== 'php') {
                continue;
            }

            $className = $this->classFromFile($file->getPathname());
            if (!$className) {
                continue;
            }

            try {
                $reflection = new ReflectionClass($className);

                if (!$reflection->isSubclassOf('Illuminate\Database\Eloquent\Model')) continue;
                if ($reflection->isAbstract()) continue;

                $instance      = $reflection->newInstanceWithoutConstructor();
                $relationships = $this->detectRelationships($reflection, $className);

                $models[] = [
                    'class'         => $className,
                    'table'         => $instance->getTable(),
                    'fillable'      => $instance->getFillable(),
                    'hidden'        => $instance->getHidden(),
                    'relationships' => $relationships,
                ];
            } catch (\Throwable) {
                // Skip models that can't be reflected safely
            }
        }

        return json_encode($models, JSON_PRETTY_PRINT);
    }

    private function detectRelationships(ReflectionClass $reflection, string $className): array
    {
        $relationTypes = [
            'HasOne', 'HasMany', 'BelongsTo', 'BelongsToMany',
            'HasOneThrough', 'HasManyThrough', 'MorphTo', 'MorphOne', 'MorphMany', 'MorphToMany',
        ];

        $relationships = [];

        foreach ($reflection->getMethods(ReflectionMethod::IS_PUBLIC) as $method) {
            if ($method->class !== $className || $method->getNumberOfParameters() > 0) {
                continue;
            }

            $returnType = $method->getReturnType();
            if (!$returnType) continue;

            $typeName = $returnType->getName();

            if (
                str_contains($typeName, 'Illuminate\Database\Eloquent\Relations') ||
                in_array(class_basename($typeName), $relationTypes)
            ) {
                $relationships[] = [
                    'method' => $method->getName(),
                    'type'   => class_basename($typeName),
                ];
            }
        }

        return $relationships;
    }

    private function classFromFile(string $path): ?string
    {
        $content = file_get_contents($path);

        preg_match('/^namespace\s+(.+?);/m', $content, $ns);
        preg_match('/^class\s+(\w+)/m', $content, $cls);

        if (empty($cls[1])) return null;

        return isset($ns[1]) ? $ns[1] . '\\' . $cls[1] : $cls[1];
    }
}
