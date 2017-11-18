<?php

namespace Laravie\Parser\Xml\TestCase;

use Laravie\Parser\Xml\Reader;
use PHPUnit\Framework\TestCase;
use Laravie\Parser\Xml\Document;

class ReaderTest extends TestCase
{
    /**
     * Test Laravie\Parser\Xml\Reader::extract() method.
     *
     * @test
     */
    public function testExtractMethod()
    {
        $xml = '<xml><foo>foobar</foo></xml>';

        $document = new Document();
        $stub = new Reader($document);
        $output = $stub->extract($xml);

        $this->assertInstanceOf('\Laravie\Parser\Xml\Document', $output);
    }

    /**
     * Test Laravie\Parser\Xml\Reader::load() method.
     *
     * @test
     */
    public function testLoadMethod()
    {
        $document = new Document();
        $stub = new Reader($document);
        $output = $stub->load(__DIR__.'/stubs/foo.xml');

        $this->assertInstanceOf('\Laravie\Parser\Xml\Document', $output);
    }

    /**
     * Test Laravie\Parser\Xml\Reader::extract() method throws exception.
     *
     * @expectedException \Laravie\Parser\InvalidContentException
     */
    public function testExtractMethodThrowsException()
    {
        $xml = '<xml><foo>foobar<foo></xml>';

        $document = new Document();
        $stub = new Reader($document);
        $output = $stub->extract($xml);
    }
}
