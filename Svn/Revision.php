<?php

/**
 * Handles fetching svn commit log and can store them into a file
 *
 * @author Lysender
 * @package trunk-spy
 */
class Svn_Revision
{
    public $revision;
    public $author;
    public $date;
    public $msg;
    public $xmlString;

    public function __construct($xmlString)
    {
        $xml = new SimpleXMLElement($xmlString);
        foreach ($xml->children() as $entry) {
            $attributes     = $entry->attributes();
            $this->revision = (int)$attributes['revision'];
            $this->author   = (string)$entry->author;
            $this->date     = (string)$entry->date;
            $this->msg      = (string)$entry->msg;
        }

        $this->xmlString = $xmlString;
    }
}
