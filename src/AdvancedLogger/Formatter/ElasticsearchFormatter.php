<?php
/**
 * Emarketa.
 */

namespace Emarketa\AdvancedLogger\Formatter;

use Monolog\Formatter\FormatterInterface;

/**
 * Class ElasticsearchFormatter
 *
 * @package Emarketa\AdvancedLogger\Formatter
 */
class ElasticsearchFormatter extends AbstractFormatter implements FormatterInterface
{
    /**
     * @inheritDoc
     */
    public function format(array $record)
    {
        $record['formatted'] = $this->getMessageString($record);
        return $this->getRecordArray($record);
    }

    /**
     * Method getRecordArray
     *
     * @param array $record
     * @return array
     */
    public function getRecordArray(array $record)
    {
        return [
            'hash_id' => hash('md5', $record['formatted']),
            'datetime' => date(DATE_ISO8601),
            'level' => $record["level_name"],
            'report_id' => $this->getReportId($record),
            'record' => $record['formatted']
        ];
    }
}
