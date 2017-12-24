<?php

namespace Laravie\Parser;

abstract class Reader
{
    /**
     * Document instance.
     *
     * @var \Laravie\Parser\Document
     */
    protected $document;

    /**
     * Construct a new reader.
     *
     * @param  \Laravie\Parser\Document  $document
     */
    public function __construct(Document $document)
    {
        $this->document = $document;
    }

    /**
     * Extract content from string.
     *
     * @param  string  $content
     *
     * @return \Laravie\Parser\Document
     */
    abstract public function extract(string $content): Document;

    /**
     * Load content from file.
     *
     * @param  string  $filename
     *
     * @return \Laravie\Parser\Document
     */
    abstract public function load(string $filename): Document;

    /**
     * Load content from local file.
     *
     * @param  string  $filename
     *
     * @return \Laravie\Parser\Document
     */
    abstract public function local(string $filename): Document;

    /**
     * Load content from remote file.
     *
     * @param  string  $filename
     *
     * @return \Laravie\Parser\Document
     */
    abstract public function remote(string $filename): Document;
}
