<?php
/**
 * Emarketa.
 */

namespace Emarketa\AdvancedLogger\Formatter;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Monolog\Formatter\FormatterInterface;
use Monolog\Formatter\JsonFormatter;
use Monolog\Utils;

/**
 * Class DatadogFormatter
 *
 * @package Emarketa\AdvancedLogger\Formatter
 */
class DatadogFormatter extends AbstractFormatter implements FormatterInterface
{
    const CONFIG_DD_TAGS = 'advanced_logger/datadog/ddtags';
    /**
     * @var ScopeConfigInterface
     */
    private $config;

    /**
     * DatadogFormatter constructor.
     * @param ScopeConfigInterface $config
     */
    public function __construct(
        ScopeConfigInterface $config
    ) {
        $this->config = $config;
    }

    /**
     * Method format
     *
     * @param array $record
     * @return array|mixed|string
     */
    public function format(array $record)
    {
        if (!isset($record['ddtype'])) {
            return $record;
        }

        $type = $record['ddtype'];
        $record['formatted'] = $this->getMessageString($record);
        $recordArray = $this->getRecordArray($record);

        switch ($type) {
            case 'file':
                $data = array_merge($record, $recordArray);
                return $this->toJson($data);
            case 'http_endpoint':
            default:
                return $recordArray;
        }
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
            'level' => $record['level_name'],
            'report_id' => $this->getReportId($record),
            'message' => $record['formatted'],
            'ddtags' => $this->getCompiledDdTags(),
            'hostname' => gethostname(),
            'ddsource' => 'magento2',
            'service' => 'magento'
        ];
    }

    /**
     * Method getCompiledDdTags
     *
     * @return string
     */
    public function getCompiledDdTags()
    {
        $return = [];
        $tags = (array) $this->config->getValue(self::CONFIG_DD_TAGS);

        foreach ($tags as $key => $value) {
            if (!empty($value)) {
                $return[] = $key . ":" . $value;
            }
        }

        return implode(",", $return);
    }

    /**
     * Method toJson
     *
     * @param mixed $data
     * @return string
     */
    protected function toJson($data)
    {
        $formatter = new JsonFormatter();
        return $formatter->format($data);
    }
}
