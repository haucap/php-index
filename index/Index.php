<?php

namespace malkusch\index;

/**
 * Autoloader
 *
 * @see http://php-autoloader.malkusch.de
 */
require_once __DIR__ . "/autoloader/autoloader.php";

/**
 * Index
 *
 * The index does a binary search on a key. That means that the data needs to
 * have a sorted index. You simply call the method Index::search() to find the
 * container for the key.
 *
 * @author   Markus Malkusch <markus@malkusch.de>
 * @link     https://github.com/malkusch/php-index
 */
abstract class Index
{

    /**
     * @var File
     */
    private $file;
    /**
     * @var KeyReader
     */
    private $keyReader;
    

    /**
     * Returns a parser for this index
     *
     * @return Parser
     */
    abstract public function getParser();

    /**
     * Sets the index file and inits the index
     *
     * @param string $path Index file
     *
     * @throws IndexException_IO_FileExists
     * @throws IndexException_IO
     */
    public function __construct($path)
    {
        $this->file = new File($path);
        
        $this->keyReader = new KeyReader();
        $this->keyReader->setIndex($this);
    }

    /**
     * Searches for the container with that key
     *
     * Returns null if the key wasn't found.
     * 
     * @param string $key Key in the index
     *
     * @return Result
     * @throws IndexException_ReadData
     */
    public function search($key)
    {
        $binarySearch = new BinarySearch($this);
        $result = $binarySearch->search($key);
        if (\is_null($result) || $result->getKey() != $key) {
            return null;
            
        }
        $result->setData($this->getParser()->getData($result->getOffset()));
        
        return $result;
    }

    /**
     * Returns the index file
     *
     * @return File
     */
    public function getFile()
    {
        return $this->file;
    }

    /**
     * Returns the KeyReader
     *
     * @return KeyReader
     */
    public function getKeyReader()
    {
        return $this->keyReader;
    }

}