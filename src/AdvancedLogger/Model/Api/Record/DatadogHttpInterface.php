<?php
/**
 * Emarketa.
 */

namespace Emarketa\AdvancedLogger\Model\Api\Record;

/**
 * Interface DatadogHttpInterface
 *
 * @package Emarketa\AdvancedLogger\Model\Api\Record
 */
interface DatadogHttpInterface
{
    /**
     * Method sendRecordToDataDog
     *
     * @param array $record
     * @return bool
     */
    public function sendRecordToHttpEndpoint($record);

    /**
     * Method getPostUrl
     *
     * @return string
     */
    public function getPostUrl();
}
