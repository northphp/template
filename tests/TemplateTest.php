<?php

use North\Template\Template;
use PHPUnit\Framework\TestCase;

class TemplateTest extends TestCase
{
    public function setUp()
    {
        $this->template = new Template(__DIR__ . '/testdata');
    }

    public function tearDown()
    {
        unset($this->template);
    }

    public function testFiles()
    {
        foreach (glob(__DIR__ . '/testdata/input/*.php') as $file) {
            $name = basename($file, '.php');
            $output = __DIR__ . '/testdata/output/' . $name . '.php';

            if (!file_exists($output)) {
                continue;
            }

            $expected = file_get_contents($output);

            ob_start();
            $this->template->render($file);
            $actual = trim(ob_get_clean());

            $this->assertSame($expected, $actual);
        }
    }

    public function testDotIncludeRender()
    {
        ob_start();
        $this->template->render('partials.title.php', ['title' => 'Test']);
        $output = ob_get_clean();

        $this->assertContains('<h1>Test</h1>', $output);
    }

    public function testCustomFunctions()
    {
        $this->template->addFunction('up', function ($t) {
            return strtoupper($t);
        });

        $this->assertSame('UP', $this->template->up('up'));
    }

    public function testFilterFunction()
    {
        $this->assertSame('UP', $this->template->filter('UP', 'strtolower|strtoupper'));
    }

    public function testFilterFunctionNotFoundException()
    {
        $this->expectException(Exception::class);

        $this->template->filter('UP', 'strtolower|strtoupper|missing');
    }

    public function testTemplateNotFoundException()
    {
        $this->expectException(Exception::class);

        $this->template->render('missing');
    }
}
