<?php
/**
 * Emarketa.
 */

namespace Emarketa\AdvancedLogger\Model\Config\Source;

use Magento\Framework\Data\OptionSourceInterface;
use Monolog\Logger;

/**
 * Class ErrorLevels
 *
 * @package Emarketa\AdvancedLogger\Model\Config\Source
 */
class ErrorLevels implements OptionSourceInterface
{
    /**
     * @inheritDoc
     */
    public function toOptionArray()
    {
        return [
            ['value' => Logger::DEBUG, 'label' => 'DEBUG'],
            ['value' => Logger::INFO, 'label' => 'INFO'],
            ['value' => Logger::NOTICE, 'label' => 'NOTICE'],
            ['value' => Logger::WARNING, 'label' => 'WARNING'],
            ['value' => Logger::ERROR, 'label' => 'ERROR'],
            ['value' => Logger::CRITICAL, 'label' => 'CRITICAL'],
            ['value' => Logger::ALERT, 'label' => 'ALERT'],
            ['value' => Logger::EMERGENCY, 'label' => 'EMERGENCY']
        ];
    }

    /**
     * Get options in "key-value" format
     *
     * @return array
     */
    public function toArray()
    {
        return [
            Logger::DEBUG => 'DEBUG',
            Logger::INFO => 'INFO',
            Logger::NOTICE => 'NOTICE',
            Logger::WARNING => 'WARNING',
            Logger::ERROR => 'ERROR',
            Logger::CRITICAL => 'CRITICAL',
            Logger::ALERT => 'ALERT',
            Logger::EMERGENCY => 'EMERGENCY'
        ];
    }
}
