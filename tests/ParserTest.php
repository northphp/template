<?php

use North\Template\Parser;
use PHPUnit\Framework\TestCase;

class ParserTest extends TestCase
{
    public function setUp()
    {
        $this->parser = new Parser;
    }

    public function tearDown()
    {
        unset($this->parser);
    }

    public function testVariableOutput()
    {
        $text = file_get_contents(__DIR__ . '/testdata/parser/var-output.php');
        $output = $this->parser->parse($text);

        $this->assertSame('<h1><?php echo $this->escape( $title ) ?></h1>', $output);
    }

    public function testNonEscapeOutput()
    {
        $text = file_get_contents(__DIR__ . '/testdata/parser/raw-output.php');
        $output = $this->parser->parse($text);

        $output = preg_replace('/\s+/', ' ', $output);

        $this->assertSame('<h1><?php echo $title ?></h1>', $output);
    }

    public function testForeachOutput()
    {
        $text = file_get_contents(__DIR__ . '/testdata/parser/foreach-output.php');
        $output = $this->parser->parse($text);

        $output = preg_replace('/\s+/', ' ', $output);

        $this->assertContains('<?php foreach ($items as $item): ?>', $output);
        $this->assertContains('<p><?php echo $this->escape( $item ) ?></p>', $output);
        $this->assertContains('<?php endforeach ?>', $output);
    }
}
