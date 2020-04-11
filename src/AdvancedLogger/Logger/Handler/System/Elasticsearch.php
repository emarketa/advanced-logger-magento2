<?php
/**
 * Emarketa.
 */

namespace Emarketa\AdvancedLogger\Logger\Handler\System;

use Emarketa\AdvancedLogger\Formatter\ElasticsearchFormatter;
use Emarketa\AdvancedLogger\Model\Api\Record\ElasticsearchClientInterface;
use Exception;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\State;
use Magento\Framework\Filesystem\DriverInterface;
use Magento\Framework\Logger\Handler\Base;
use Monolog\Logger;

/**
 * Class Elasticsearch
 *
 * @package Emarketa\AdvancedLogger\Logger\Handler\System
 */
class Elasticsearch extends Base
{
    const CONFIG_ENABLED = 'advanced_logger/elasticsearch/enabled';
    const CONFIG_ACCEPTABLE_LEVEL = 'advanced_logger/elasticsearch/acceptable_level';
    const CONFIG_DEV_MODE_ENABLE = 'advanced_logger/elasticsearch/enabled_in_developer_mode';

    /**
     * @var ScopeConfigInterface
     */
    private $config;
    /**
     * @var ElasticsearchClientInterface
     */
    private $client;
    /**
     * @var State
     */
    private $state;

    /**
     * Elasticsearch constructor.
     * @param ElasticsearchFormatter $formatter
     * @param ElasticsearchClientInterface $client
     * @param ScopeConfigInterface $config
     * @param State $state
     * @param DriverInterface $filesystem
     * @param string $filePath
     * @param string $fileName
     * @throws Exception
     */
    public function __construct(
        ElasticsearchFormatter $formatter,
        ElasticsearchClientInterface $client,
        ScopeConfigInterface $config,
        State $state,
        DriverInterface $filesystem,
        $filePath = null,
        $fileName = null
    ) {
        parent::__construct($filesystem, $filePath, $fileName);
        $this->setFormatter($formatter);
        $this->config = $config;
        $this->client = $client;
        $this->state = $state;
    }

    /**
     * Method write
     *
     * @param array $record
     */
    public function write(array $record)
    {
        $recordArray =  $this->getFormatter()->format($record);
        if ($this->client->build()) {
            $this->client->sendRecordToElasticSearch($recordArray);
        }
    }

    /**
     * Method isHandling
     *
     * @param array $record
     * @return bool
     */
    public function isHandling(array $record)
    {
        return $this->isEnabled() &&
            $this->getDeveloperModePolicyResult() &&
            ($record['level'] >= $this->getMinimumLevel());
    }

    /**
     * Method isEnabled
     *
     * @return bool
     */
    public function isEnabled()
    {
        return (bool) (int) $this->config->getValue(self::CONFIG_ENABLED);
    }

    /**
     * Method getMinimumLevel
     *
     * @return int
     */
    public function getMinimumLevel()
    {
        $level = $this->config->getValue(self::CONFIG_ACCEPTABLE_LEVEL);
        return empty($level) ? Logger::WARNING : (int) $level;
    }

    /**
     * Method getDeveloperModePolicyResult
     *
     * @return bool
     */
    public function getDeveloperModePolicyResult()
    {
        $isDeveloperMode = $this->state->getMode() == "developer";
        $enabledInDeveloperMode = (bool) (int) $this->config->getValue(self::CONFIG_DEV_MODE_ENABLE);
        return $isDeveloperMode ? $enabledInDeveloperMode : true;
    }
}
