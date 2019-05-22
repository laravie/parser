<?php

namespace Snowlyg\Parser\Xml;

use Snowlyg\Parser\Reader as BaseReader;
use Snowlyg\Parser\FileNotFoundException;
use Snowlyg\Parser\InvalidContentException;
use Snowlyg\Parser\Document as BaseDocument;

class Reader extends BaseReader
{
    /**
     * {@inheritdoc}
     */
    public function extract(string $content): BaseDocument
    {
        $xml = @\simplexml_load_string($content);

        return $this->resolveXmlObject($xml);
    }

    /**
     * {@inheritdoc}
     */
    public function load(string $filename, $ns = ""): BaseDocument
    {
        $is_prefix = $ns ? true : false;
        $xml = @\simplexml_load_file($filename, "SimpleXMLElement", 0, $ns, $is_prefix);

        return $this->resolveXmlObject($xml);
    }

    /**
     * {@inheritdoc}
     */
    public function local(string $filename): BaseDocument
    {
        if (! \file_exists($filename)) {
            throw new FileNotFoundException('Could not find the file: '.$filename);
        }

        return $this->load($filename);
    }

    /**
     * {@inheritdoc}
     */
    public function remote(string $filename): BaseDocument
    {
        return $this->load($filename);
    }

    /**
     * Validate given XML.
     *
     * @param  object $xml
     *
     * @throws \Snowlyg\Parser\InvalidContentException
     *
     * @return \Snowlyg\Parser\Document
     */
    protected function resolveXmlObject($xml): Document
    {
        if (! $xml) {
            throw new InvalidContentException('Unable to parse XML from string.');
        }

        return $this->document->setContent($xml);
    }
}
