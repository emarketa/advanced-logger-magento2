<?php
/**
 * Emarketa.
 */

namespace Emarketa\AdvancedLogger\Logger\Handler\System;

use Emarketa\AdvancedLogger\Formatter\DatadogFormatter;
use Emarketa\AdvancedLogger\Model\Api\Record\DatadogHttpInterface;
use Exception;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\State;
use Magento\Framework\Filesystem\DriverInterface;
use Magento\Framework\Logger\Handler\Base;
use Monolog\Logger;

/**
 * Class Datadog
 *
 * @package Emarketa\AdvancedLogger\Logger\Handler\System
 */
class DatadogHttp extends Base
{
    const CONFIG_ENABLED = 'advanced_logger/datadog/http_endpoint/enabled';
    const CONFIG_DEV_MODE_ENABLE = 'advanced_logger/datadog/http_endpoint/enabled_in_developer_mode';
    const CONFIG_ACCEPTABLE_LEVEL = 'advanced_logger/datadog/http_endpoint/acceptable_level';

    /**
     * @var DatadogHttpInterface
     */
    private $client;
    /**
     * @var ScopeConfigInterface
     */
    private $config;
    /**
     * @var State
     */
    private $state;

    /**
     * Datadog constructor.
     * @param DatadogFormatter $formatter
     * @param ScopeConfigInterface $config
     * @param State $state
     * @param DatadogHttpInterface $client
     * @param DriverInterface $filesystem
     * @param string $filePath
     * @param string $fileName
     * @throws Exception
     */
    public function __construct(
        DatadogFormatter $formatter,
        ScopeConfigInterface $config,
        State $state,
        DatadogHttpInterface $client,
        DriverInterface $filesystem,
        $filePath = null,
        $fileName = null
    ) {
        parent::__construct($filesystem, $filePath, $fileName);
        $this->setFormatter($formatter);
        $this->client = $client;
        $this->config = $config;
        $this->state = $state;
    }

    /**
     * Method write
     *
     * @param array $record
     */
    public function write(array $record)
    {
        $record['ddtype'] = "http_endpoint";
        $recordForEndpoint = $this->getFormatter()->format($record);
        $this->client->sendRecordToHttpEndpoint($recordForEndpoint);
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
