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

    public function testTemplate()
    {
        ob_start();
        $this->template->render('404');
        $output = ob_get_clean();

        $this->assertContains('<title>404 - Not found</title>', $output);
        $this->assertContains('<p>Hello parent block</p>', $output);
        $this->assertContains('<h1>404 - Not found</h1>', $output);
        $this->assertContains('<h1>Fetch - Not found</h1>', $output);
        $this->assertContains('&lt;a href=&quot;#&quot;&gt;Click&lt;/a&gt;', $output);
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
