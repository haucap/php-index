<?php

namespace malkusch\index\test;
use malkusch\index as index;

require_once __DIR__ . "/classes/AbstractTest.php";

/**
 * Tests an index
 *
 * @author   Markus Malkusch <markus@malkusch.de>
 * @link     https://github.com/malkusch/php-index
 */
class TestIndex extends AbstractTest
{

    /**
     * Tests searching
     *
     * @param IndexGenerator $generator Index generator
     *
     * @return void
     * @dataProvider provideTestSearch
     */
    public function testSearch(IndexGenerator $generator)
    {
        $index = $generator->getIndex();
        for (
            $key = $generator->getMinimum();
            $key <= $generator->getMaximum();
            $key++
        ) {
            if (! $generator->isKey($key)) {
                continue;
                
            }
            $result = $index->search($key);
            $this->assertNotNull(
                $result,
                "key: $key, max: {$generator->getMaximum()},"
                . " step: {$generator->getStepSize()}"
                . " length: {$generator->getIndexLength()}"
            );
            $expected = preg_quote($generator->generateData($key));
            // $this->assertRegExp("/$expected/", $result->getData());
            
        }
    }
    
    /**
     * Test cases for testSearch()
     *
     * @return void
     */
    public function provideTestSearch()
    {
        $cases = array();
        
        $lengths = array(
            1,
            2,
            10,
            IndexGenerator::getBlockSize() - 1,
            IndexGenerator::getBlockSize(),
            IndexGenerator::getBlockSize() + 1,
            IndexGenerator::getBlockSize() * 4,
        );
        
        $steps = array(
            1,
            2,
            3,
            5,
            IndexGenerator::getBlockSize()
        );
        
        foreach ($lengths as $length) {
            foreach ($steps as $step) {
                $generator = new IndexGenerator_FixedSize();
                $generator->setIndexLength($length);
                $generator->setStepSize(2);
                $cases[] = array($generator);
                
            }
        }
        
        return $cases;
    }
    
    /**
     * Tests that failing terminates
     *
     * @return void
     * @dataProvider provideTestFailSearch
     */
    public function testFailSearch(index\Index $index, $key)
    {
        $this->assertNull($index->search($key));
    }

    /**
     * Test cases for testFailSearch()
     *
     * @return void
     */
    public function provideTestFailSearch()
    {
        $cases  = array();
        
        // different sizes
        $lengths = array(
            0,
            1,
            10,
            IndexGenerator::getBlockSize() - 1,
            IndexGenerator::getBlockSize(),
            IndexGenerator::getBlockSize() + 1,
            IndexGenerator::getBlockSize() * 4
        );
        
        foreach ($lengths as $length) {
            // Fail searching for MIN(index) - 1
            $generator = new IndexGenerator_FixedSize();
            $generator->setIndexLength($length);
            $generator->getIndex();
            $cases[] = array(
                $generator->getIndex(),
                $generator->getMinimum() - 1
            );
            
            // Fail searching for MAX(index) + 1
            $generator = new IndexGenerator_FixedSize();
            $generator->setIndexLength($length);
            $generator->getIndex();
            $cases[] = array(
                $generator->getIndex(),
                $generator->getMaximum() + 1
            );
            
            // Fail searching for any missing key inside the index range
            $generator = new IndexGenerator_FixedSize();
            $generator->setIndexLength($length);
            $generator->setStepSize(2);
            $generator->getIndex();
            $cases[] = array(
                $generator->getIndex(),
                $generator->getMaximum() / 2 + 0.1
            );
            
        }
        
        return $cases;
    }

}