<?php

namespace Laravie\Parser\Xml;

use Throwable;
use Laravie\Parser\Reader as BaseReader;
use Laravie\Parser\FileNotFoundException;
use Laravie\Parser\InvalidContentException;
use Laravie\Parser\Document as BaseDocument;

class Reader extends BaseReader
{
    /**
     * {@inheritdoc}
     */
    public function extract(string $content): BaseDocument
    {
        try {
            $xml = @\simplexml_load_string($content);
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
            $xml = @\simplexml_load_file($filename);
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
     * @throws \Laravie\Parser\InvalidContentException
     *
     * @return \Laravie\Parser\Document
     */
    protected function resolveXmlObject($xml): Document
    {
        if (! $xml) {
            throw new InvalidContentException('Unable to parse XML from string.');
        }

        return $this->document->setContent($xml);
    }
}
