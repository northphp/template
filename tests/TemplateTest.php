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
        $this->assertContains('<h1>404 - Not found</h1>', $output);
        $this->assertContains('&lt;a href=&quot;#&quot;&gt;Click&lt;/a&gt;', $output);
    }
}
