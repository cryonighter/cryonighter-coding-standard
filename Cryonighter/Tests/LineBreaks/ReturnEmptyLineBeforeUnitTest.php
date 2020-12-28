<?php

namespace Cryonighter\Tests\LineBreaks;

use PHP_CodeSniffer\Tests\Standards\AbstractSniffUnitTest;

/**
 * Unit test class for the MultiLineArrayComma sniff.
 * A sniff unit test checks a .inc file for expected violations of a single
 * coding standard. Expected errors and warnings are stored in this class.
 */
class ReturnEmptyLineBeforeUnitTest extends AbstractSniffUnitTest
{
    /**
     * Returns the lines where errors should occur.
     * The key of the array should represent the line number and the value
     * should represent the number of errors that should occur on that line.
     *
     * @return array
     */
    public function getErrorList()
    {
        return [
            78  => 1,
            483  => 1,
        ];
    }

    /**
     * Returns the lines where warnings should occur.
     * The key of the array should represent the line number and the value
     * should represent the number of errors that should occur on that line.
     *
     * @return array
     */
    public function getWarningList()
    {
        return [];
    }
}
