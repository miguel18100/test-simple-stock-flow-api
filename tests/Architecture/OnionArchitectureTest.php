<?php

declare(strict_types=1);

namespace Tests\Architecture;

use PHPUnit\Framework\TestCase;
use ReflectionClass;
use ReflectionNamedType;

final class OnionArchitectureTest extends TestCase
{
    private string $root;

    protected function setUp(): void
    {
        parent::setUp();
        $this->root = dirname(__DIR__, 2);
    }

    public function test_domain_does_not_import_framework_or_external_packages(): void
    {
        $files = $this->phpFiles('app/Domain');
        self::assertNotEmpty($files, 'Domain must contain PHP source files.');

        foreach ($files as $file) {
            $source = file_get_contents($file);

            self::assertIsString($source);
            self::assertDoesNotMatchRegularExpression(
                '/^\s*use\s+(?!App\\\\Domain\\\\|Brick\\\\Math\\\\)[A-Za-z_\\\\][A-Za-z0-9_\\\\]*(?:\s+as\s+\w+)?\s*;/m',
                $source,
                "External or non-Domain import found in {$file}"
            );

            self::assertDoesNotMatchRegularExpression(
                '/\b(?:Illuminate|Laravel|Symfony)\\\\/',
                $source,
                "Framework reference found in {$file}"
            );
        }
    }

    public function test_application_imports_only_domain_and_application_code(): void
    {
        $files = $this->phpFiles('app/Application');
        self::assertNotEmpty($files, 'Application must contain PHP source files.');

        foreach ($files as $file) {
            $source = file_get_contents($file);

            self::assertIsString($source);
            self::assertDoesNotMatchRegularExpression(
                '/^\s*use\s+(?:App\\\\(?:Infrastructure|Presentation|Bootstrap)\\\\|Illuminate\\\\|Symfony\\\\|Brick\\\\Math\\\\)/m',
                $source,
                "Forbidden dependency found in {$file}"
            );
        }
    }

    public function test_presentation_does_not_import_infrastructure(): void
    {
        $files = $this->phpFiles('app/Presentation');
        self::assertNotEmpty($files, 'Presentation must contain PHP source files.');

        foreach ($files as $file) {
            $source = file_get_contents($file);

            self::assertIsString($source);
            self::assertDoesNotMatchRegularExpression(
                '/^\s*use\s+App\\\\Infrastructure\\\\/m',
                $source,
                "Presentation imports Infrastructure in {$file}"
            );

            self::assertDoesNotMatchRegularExpression(
                '/\bnew\s+\\\\?App\\\\Infrastructure\\\\/',
                $source,
                "Presentation instantiates Infrastructure in {$file}"
            );
        }
    }

    public function test_application_constructor_dependencies_are_domain_or_outbound_ports(): void
    {
        $this->assertConstructorDependencies(
            'app/Application',
            static fn (string $type): bool =>
                str_starts_with($type, 'App\\Domain\\')
                || str_starts_with($type, 'App\\Application\\Ports\\Outbound\\'),
            'Application constructors may depend only on Domain or outbound ports'
        );
    }

    public function test_presentation_constructor_dependencies_are_inbound_ports(): void
    {
        $this->assertConstructorDependencies(
            'app/Presentation',
            static fn (string $type): bool =>
                str_starts_with($type, 'App\\Application\\Ports\\Inbound\\'),
            'Presentation constructors may depend only on inbound ports'
        );
    }

    public function test_only_bootstrap_imports_or_instantiates_infrastructure(): void
    {
        foreach ($this->phpFiles('app') as $file) {
            $relative = str_replace('\\', '/', substr($file, strlen($this->root) + 1));
            if (str_starts_with($relative, 'app/Bootstrap/')) {
                continue;
            }

            $source = file_get_contents($file);
            self::assertIsString($source);

            self::assertDoesNotMatchRegularExpression(
                '/^\s*use\s+App\\\\Infrastructure\\\\/m',
                $source,
                "Infrastructure import outside Bootstrap in {$file}"
            );

            self::assertDoesNotMatchRegularExpression(
                '/\bnew\s+\\\\?App\\\\Infrastructure\\\\/',
                $source,
                "Infrastructure instantiation outside Bootstrap in {$file}"
            );
        }
    }

    /**
     * @return list<string>
     */
    private function phpFiles(string $directory): array
    {
        $path = $this->root . DIRECTORY_SEPARATOR . str_replace(
            '/',
            DIRECTORY_SEPARATOR,
            $directory
        );

        if (!is_dir($path)) {
            return [];
        }

        $files = [];
        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator(
                $path,
                \FilesystemIterator::SKIP_DOTS
            )
        );

        foreach ($iterator as $file) {
            if ($file->isFile() && $file->getExtension() === 'php') {
                $files[] = $file->getPathname();
            }
        }

        return $files;
    }

    private function assertConstructorDependencies(
        string $directory,
        callable $isAllowed,
        string $message
    ): void {
        $files = $this->phpFiles($directory);
        self::assertNotEmpty($files, "No PHP source files found in {$directory}.");

        foreach ($files as $file) {
            $relative = str_replace('\\', '/', substr($file, strlen($this->root) + 1));
            $class = 'App\\' . str_replace('/', '\\', substr($relative, strlen('app/'), -4));

            if (!class_exists($class) && !interface_exists($class)) {
                continue;
            }

            $reflection = new ReflectionClass($class);
            $constructor = $reflection->getConstructor();

            if ($constructor === null) {
                continue;
            }

            foreach ($constructor->getParameters() as $parameter) {
                $type = $parameter->getType();

                if (!$type instanceof ReflectionNamedType || $type->isBuiltin()) {
                    continue;
                }

                self::assertTrue(
                    $isAllowed($type->getName()),
                    "{$message}: {$class}::__construct(\${$parameter->getName()}) has type {$type->getName()}"
                );
            }
        }
    }
}