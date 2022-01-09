<?php
/**
 * Emarketa.
 */

namespace Emarketa\AdvancedLogger\Logger\Handler\System;

use Emarketa\AdvancedLogger\Formatter\DatadogFormatter;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\State;
use Magento\Framework\Filesystem\DriverInterface;
use Magento\Framework\Logger\Handler\Base;
use Magento\Framework\Logger\Handler\Exception;
use Monolog\Logger;

/**
 * Class DatadogFile
 *
 * @package Emarketa\AdvancedLogger\Logger\Handler\System
 */
class DatadogFile extends Base
{
    const CONFIG_ENABLED = 'advanced_logger/datadog/file/enabled';
    const CONFIG_DEV_MODE_ENABLE = 'advanced_logger/datadog/file/enabled_in_developer_mode';
    const CONFIG_ACCEPTABLE_LEVEL = 'advanced_logger/datadog/file/acceptable_level';

    /**
     * @var string
     */
    protected $fileName = '/var/log/datadog.log.json';
    /**
     * @var Exception
     */
    private $exceptionHandler;
    /**
     * @var ScopeConfigInterface
     */
    private $config;
    /**
     * @var State
     */
    private $state;

    /**
     * DatadogFile constructor.
     *
     * @param DatadogFormatter $formatter
     * @param ScopeConfigInterface $config
     * @param State $state
     * @param DriverInterface $filesystem
     * @param string $filePath
     * @throws \Exception
     */
    public function __construct(
        DatadogFormatter $formatter,
        ScopeConfigInterface $config,
        State $state,
        DriverInterface $filesystem,
        $filePath = null
    ) {
        parent::__construct($filesystem, $filePath);
        $this->setFormatter($formatter);
        $this->config = $config;
        $this->state = $state;
    }

    /**
     * Method handle
     *
     * @param array $record
     * @return bool
     */
    public function handle(array $record)
    {
        $record['ddtype'] = "file";
        return parent::handle($record);
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

    /**
     * @return string
     */
    public function getFileName(): string
    {
        return $this->fileName;
    }
}
