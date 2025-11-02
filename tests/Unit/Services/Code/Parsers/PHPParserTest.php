<?php

namespace Tests\Unit\Services\Code\Parsers;

use App\Services\Code\Parsers\PHPParser;
use Tests\TestCase;

class PHPParserTest extends TestCase
{
    public function test_parses_functions_classes_namespaces_and_dependencies(): void
    {
        $parser = new PHPParser;

        $classPath = base_path('tests/Fixtures/code-samples/sample-class.php');
        $depsPath = base_path('tests/Fixtures/code-samples/sample-with-dependencies.php');
        $funcPath = base_path('tests/Fixtures/code-samples/sample.php');

        $classContent = file_get_contents($classPath);
        $depsContent = file_get_contents($depsPath);
        $funcContent = file_get_contents($funcPath);

        $resultClass = $parser->parse($classContent);
        $resultDeps = $parser->parse($depsContent);
        $resultFunc = $parser->parse($funcContent);

        // Classes and methods
        $this->assertNotEmpty($resultClass->classes);
        $this->assertSame('SampleClass', $resultClass->classes[0]['name']);
        $this->assertSame(2, $resultClass->classes[0]['methods_count']);

        // Namespaces
        $this->assertContains('App\\Sample', $resultClass->namespaces);

        // Dependencies
        $this->assertContains('DateTime', $resultClass->dependencies);
        $this->assertContains('Illuminate\\Support\\Collection', $resultClass->dependencies);
        $this->assertContains('Psr\\Log\\LoggerInterface', $resultDeps->dependencies);
        $this->assertContains('Illuminate\\Support\\Arr', $resultDeps->dependencies);

        // Functions
        $this->assertGreaterThanOrEqual(2, count($resultFunc->functions));
        $this->assertSame('helper_function_one', $resultFunc->functions[0]['name']);

        // Metadata
        $this->assertArrayHasKey('lines', $resultClass->metadata);
        $this->assertArrayHasKey('size_bytes', $resultClass->metadata);
    }

    public function test_supports_extension_php(): void
    {
        $parser = new PHPParser;
        $this->assertTrue($parser->supports('php'));
        $this->assertFalse($parser->supports('js'));
    }

    public function test_handles_empty_content(): void
    {
        $parser = new PHPParser;
        $result = $parser->parse('');
        $this->assertIsArray($result->toArray());
        $this->assertSame(0, count($result->classes));
        $this->assertSame(0, count($result->functions));
    }

    public function test_throws_on_malformed_code(): void
    {
        $this->expectException(\RuntimeException::class);
        $parser = new PHPParser;
        $parser->parse('<?php function broken( { ');
    }
}
