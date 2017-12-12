<?php

namespace Laravie\Parser\Xml;

use Laravie\Parser\Reader as BaseReader;
use Laravie\Parser\InvalidContentException;
use Laravie\Parser\FileNotFoundException;
use Laravie\Parser\Document as BaseDocument;

class Reader extends BaseReader
{
    /**
     * {@inheritdoc}
     */
    public function extract(string $content): BaseDocument
    {
        $xml = @simplexml_load_string($content);

        return $this->resolveXmlObject($xml);
    }

    /**
     * {@inheritdoc}
     */
    public function load(string $filename): BaseDocument
    {
        if (!file_exists($filename)) {
            throw new FileNotFoundException('Could not find the file: ' . $filename);
        }

        $xml = @simplexml_load_file($filename);

        return $this->resolveXmlObject($xml);
    }

    /**
     * Validate given XML.
     *
     * @param  object $xml
     *
     * @return \Laravie\Parser\Document
     *
     * @throws \Laravie\Parser\InvalidContentException
     */
    protected function resolveXmlObject($xml): Document
    {
        if (! $xml) {
            throw new InvalidContentException('Unable to parse XML from string.');
        }

        return $this->document->setContent($xml);
    }
}
