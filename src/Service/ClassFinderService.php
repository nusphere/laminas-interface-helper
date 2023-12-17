<?php

namespace Laminas\Interface\Helper\Service;

use Composer\Autoload\ClassLoader;

final class ClassFinderService
{
    private array $classes = [];

    public function findClassesByInterface(string $interfaceName): array
    {
        $classes = [];

        foreach ($this->findAllAvailableClasses() as $class) {
            $implements = class_implements($class);

            if ($implements !== false
                && (
                    (is_string($implements) && $implements === $interfaceName)
                    || in_array($interfaceName, $implements)
                )
            ) {
                $classes[] = $class;
            }
        }

        return $classes;
    }

    public function findClassesByExtends(string $extendedClassName): array
    {
        $classes = [];

        try {
            foreach ($this->findAllAvailableClasses() as $class) {
                if (is_a($class, $extendedClassName, true) && $class !== $extendedClassName) {
                    $classes[] = $class;
                }
            }
        } catch (\Throwable) {}

        return $classes;
    }

    public function findAllAvailableClasses(): array
    {
        /** @var ClassLoader $classLoader */
        $classLoader = require dirname(__DIR__, 2) . '/vendor/autoload.php';

        foreach (array_keys($classLoader->getClassMap()) as $className) {
            $this->classes[] = $className;
        }

        foreach ($classLoader->getPrefixesPsr4() as $prefix => $paths) {
            foreach ($paths as $dir) {
                $this->analyseDirectory($dir, $prefix);
            }
        }

        return $this->classes;
    }

    private function analyseDirectory(string $directory, string $prefix): void
    {
        $files = scandir($directory);
        foreach ($files as $file) {
            if ($file !== '.' && $file !== '..') {
                if (is_dir($directory . DIRECTORY_SEPARATOR . $file)) {
                    $this->analyseDirectory($directory . DIRECTORY_SEPARATOR . $file, $prefix . basename($file) . '\\');
                } elseif (str_ends_with($file, '.php')) {
                    $className = $prefix . basename($file, '.php');

                    $fileContent = file_get_contents($directory . DIRECTORY_SEPARATOR . $file);

                    if (
                        str_contains($fileContent, 'namespace ' . rtrim($prefix, '\\') . ';')
                    ) {
                        $this->classes[] = $className;
                    }
                }
            }
        }
    }
}
