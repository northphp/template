<?php

use North\Template\Template;
use PHPUnit\Framework\TestCase;

class TemplateTest extends TestCase
{
    public function testTemplate()
    {
        $template = new Template(__DIR__ . '/testdata');

        ob_start();
        $template->render('404');
        $output = ob_get_clean();

        $this->assertContains('<title>404 - Not found</title>', $output);
        $this->assertContains('<p>Hello parent block</p>', $output);
        $this->assertContains('<h1>404 - Not found</h1>', $output);
        $this->assertContains('<h1>Fetch - Not found</h1>', $output);
        $this->assertContains('&lt;a href=&quot;#&quot;&gt;Click&lt;/a&gt;', $output);
    }

    public function testCustomFunctions()
    {
        $template = new Template(__DIR__ . '/testdata');

        $template->addFunction('up', function ($t) {
            return strtoupper($t);
        });

        $this->assertSame('UP', $template->up('up'));
    }

    public function testFilterFunction()
    {
        $template = new Template(__DIR__ . '/testdata');

        $this->assertSame('UP', $template->filter('UP', 'strtolower|strtoupper'));
    }

    public function testFilterFunctionNotFoundException()
    {
        $this->expectException(Exception::class);

        $template = new Template(__DIR__ . '/testdata');

        $template->filter('UP', 'strtolower|strtoupper|missing');
    }

    public function testTemplateNotFoundException()
    {
        $this->expectException(Exception::class);

        $template = new Template(__DIR__ . '/testdata');

        $template->render('missing');
    }
}
