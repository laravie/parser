<?php

namespace Laravie\Parser\Xml;

use Laravie\Parser\Document as BaseDocument;
use Laravie\Parser\FileNotFoundException;
use Laravie\Parser\InvalidContentException;
use Laravie\Parser\Reader as BaseReader;
use Throwable;

class Reader extends BaseReader
{
    /**
     * {@inheritdoc}
     */
    public function extract(string $content): BaseDocument
    {
        try {
            $xml = @simplexml_load_string($content);
        } catch (Throwable $e) {
            $xml = null;
        }

        return $this->resolveXmlObject($xml);
    }

    /**
     * {@inheritdoc}
     */
    public function load(string $filename): BaseDocument
    {
        try {
            $xml = @simplexml_load_file($filename);
        } catch (Throwable $e) {
            $xml = null;
        }

        return $this->resolveXmlObject($xml);
    }

    /**
     * {@inheritdoc}
     */
    public function local(string $filename): BaseDocument
    {
        if (! file_exists($filename)) {
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
     * @param  \SimpleXMLElement|bool|null  $xml
     * @return \Laravie\Parser\Document
     *
     * @throws \Laravie\Parser\InvalidContentException
     */
    protected function resolveXmlObject($xml): BaseDocument
    {
        if (! $xml) {
            throw new InvalidContentException('Unable to parse XML from string.');
        }

        return $this->document->setContent($xml);
    }
}
