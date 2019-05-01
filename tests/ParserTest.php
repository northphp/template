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

    public function testFiles()
    {
        foreach (glob(__DIR__ . '/testdata/parser/input/*.php') as $file) {
            $name = basename($file, '.php');
            $output = __DIR__ . '/testdata/parser/output/' . $name . '.php';

            if (!file_exists($output)) {
                continue;
            }

            $expected = file_get_contents($output);
            $actual = $this->parser->parse(file_get_contents($file));

            $actual = trim($actual);

            $this->assertSame($expected, $actual);
        }
    }
}
