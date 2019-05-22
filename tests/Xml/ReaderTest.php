<?php

namespace Snowlyg\Parser\Xml\TestCase;

use Snowlyg\Parser\Xml\Reader;
use PHPUnit\Framework\TestCase;
use Snowlyg\Parser\Xml\Document;

class ReaderTest extends TestCase
{
    /**
     * Test Snowlyg\Parser\Xml\Reader::extract() method.
     *
     * @test
     */
    public function it_can_extract_document()
    {
        $xml = '<xml><foo>foobar</foo></xml>';

        $document = new Document();
        $stub = new Reader($document);
        $output = $stub->extract($xml);

        $this->assertInstanceOf('\Snowlyg\Parser\Xml\Document', $output);
    }

    /**
     * Test Snowlyg\Parser\Xml\Reader::load() method.
     *
     * @test
     */
    public function it_can_load_document()
    {
        $document = new Document();
        $stub = new Reader($document);
        $output = $stub->load(__DIR__.'/stubs/foo.xml');

        $this->assertInstanceOf('\Snowlyg\Parser\Xml\Document', $output);
    }

    /**
     * Test Snowlyg\Parser\Xml\Reader::load() method.
     *
     * @test
     */
    public function it_throws_exception_when_loading_a_none_existing_file()
    {
        $this->expectException('Snowlyg\Parser\FileNotFoundException');

        $document = new Document();
        $stub = new Reader($document);
        $output = $stub->local('');
    }

    /**
     * Test Snowlyg\Parser\Xml\Reader::load() method.
     *
     * @test
     */
    public function it_throws_exception_when_content_is_not_a_valid_xml_on_load()
    {
        $this->expectException('Snowlyg\Parser\InvalidContentException');

        $document = new Document();
        $stub = new Reader($document);
        $output = $stub->load(__DIR__.'/stubs/invalid.xml');
    }

    /**
     * Test Snowlyg\Parser\Xml\Reader::extract() method throws exception.
     *
     * @test
     */
    public function it_throws_exception_when_content_is_not_a_valid_xml_on_extract()
    {
        $this->expectException('Snowlyg\Parser\InvalidContentException');

        $xml = '<xml><foo>foobar<foo></xml>';

        $document = new Document();
        $stub = new Reader($document);
        $output = $stub->extract($xml);
    }
}
