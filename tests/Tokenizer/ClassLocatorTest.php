<?php
/**
 * Spiral, Core Components
 *
 * @author Wolfy-J
 */

namespace Spiral\Tokenizer\Tests;

use PHPUnit\Framework\TestCase;
use Psr\Log\AbstractLogger;
use Spiral\Tokenizer\Config\TokenizerConfig;
use Spiral\Tokenizer\Tests\Classes\ClassA;
use Spiral\Tokenizer\Tests\Classes\ClassB;
use Spiral\Tokenizer\Tests\Classes\ClassC;
use Spiral\Tokenizer\Tests\Classes\Inner\ClassD;
use Spiral\Tokenizer\Tests\Fixtures\TestInterface;
use Spiral\Tokenizer\Tests\Fixtures\TestTrait;
use Spiral\Tokenizer\Tokenizer;

class ClassLocatorTest extends TestCase
{
    public function testClassesAll()
    {
        $tokenizer = $this->getTokenizer();

        //Direct loading
        $classes = $tokenizer->classLocator()->getClasses();

        $this->assertArrayHasKey(self::class, $classes);
        $this->assertArrayHasKey(ClassA::class, $classes);
        $this->assertArrayHasKey(ClassB::class, $classes);
        $this->assertArrayHasKey(ClassC::class, $classes);
        $this->assertArrayHasKey(ClassD::class, $classes);

        //Excluded
        $this->assertArrayNotHasKey('Spiral\Tokenizer\Tests\Classes\Excluded\ClassXX', $classes);
        $this->assertArrayNotHasKey('Spiral\Tokenizer\Tests\Classes\Bad_Class', $classes);
    }

    public function testClassesByClass()
    {
        $tokenizer = $this->getTokenizer();

        //By namespace
        $classes = $tokenizer->classLocator()->getClasses(ClassD::class);

        $this->assertArrayHasKey(ClassD::class, $classes);

        $this->assertArrayNotHasKey(self::class, $classes);
        $this->assertArrayNotHasKey(ClassA::class, $classes);
        $this->assertArrayNotHasKey(ClassB::class, $classes);
        $this->assertArrayNotHasKey(ClassC::class, $classes);
    }

    public function testClassesByInterface()
    {
        $tokenizer = $this->getTokenizer();

        //By interface
        $classes = $tokenizer->classLocator()->getClasses(TestInterface::class);

        $this->assertArrayHasKey(ClassB::class, $classes);
        $this->assertArrayHasKey(ClassC::class, $classes);

        $this->assertArrayNotHasKey(self::class, $classes);
        $this->assertArrayNotHasKey(ClassA::class, $classes);
        $this->assertArrayNotHasKey(ClassD::class, $classes);
    }

    public function testClassesByTrait()
    {
        $tokenizer = $this->getTokenizer();

        //By trait
        $classes = $tokenizer->classLocator()->getClasses(TestTrait::class);

        $this->assertArrayHasKey(ClassB::class, $classes);
        $this->assertArrayHasKey(ClassC::class, $classes);

        $this->assertArrayNotHasKey(self::class, $classes);
        $this->assertArrayNotHasKey(ClassA::class, $classes);
        $this->assertArrayNotHasKey(ClassD::class, $classes);
    }

    public function testClassesByClassA()
    {
        $tokenizer = $this->getTokenizer();

        //By class
        $classes = $tokenizer->classLocator()->getClasses(ClassA::class);

        $this->assertArrayHasKey(ClassA::class, $classes);
        $this->assertArrayHasKey(ClassB::class, $classes);
        $this->assertArrayHasKey(ClassC::class, $classes);
        $this->assertArrayHasKey(ClassD::class, $classes);

        $this->assertArrayNotHasKey(self::class, $classes);
    }

    public function testClassesByClassB()
    {
        $tokenizer = $this->getTokenizer();
        $classes = $tokenizer->classLocator()->getClasses(ClassB::class);

        $this->assertArrayHasKey(ClassB::class, $classes);
        $this->assertArrayHasKey(ClassC::class, $classes);

        $this->assertArrayNotHasKey(self::class, $classes);
        $this->assertArrayNotHasKey(ClassA::class, $classes);
        $this->assertArrayNotHasKey(ClassD::class, $classes);
    }

    public function testLoggerErrors()
    {
        $tokenizer = $this->getTokenizer();

        //By class
        $locator = $tokenizer->classLocator();
        $logger = new class extends AbstractLogger
        {
            private $messages = [];

            public function log($level, $message, array $context = [])
            {
                $this->messages[] = compact('level', 'message');
            }

            public function getMessages()
            {
                return $this->messages;
            }
        };

        /**
         * @var \Spiral\Tokenizer\ClassLocator $locator
         */
        $locator->setLogger($logger);

        $classes = $locator->getClasses(ClassB::class);

        $this->assertContains(
            ' has includes and excluded from analysis',
            $logger->getMessages()[0]['message']
        );
    }

    protected function getTokenizer()
    {
        $config = new TokenizerConfig([
            'directories' => [__DIR__],
            'exclude' => ['Excluded']
        ]);

        $tokenizer = new Tokenizer($config);

        return $tokenizer;
    }
}
