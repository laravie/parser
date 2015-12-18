<?php namespace Laravie\Parser\Xml\TestCase;

use Laravie\Parser\Xml\Reader;
use Laravie\Parser\Xml\Document;

class ReaderTest extends \PHPUnit_Framework_TestCase
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
        $stub     = new Reader($document);
        $output   = $stub->extract($xml);

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
        $stub     = new Reader($document);
        $output   = $stub->load(__DIR__.'/stub/foo.xml');

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
        $stub     = new Reader($document);
        $output   = $stub->extract($xml);
    }
}
