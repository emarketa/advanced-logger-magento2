<?php
/**
 * Emarketa.
 */

namespace Emarketa\AdvancedLogger\Model\Config\Source\Datadog;

use Magento\Framework\Data\OptionSourceInterface;

/**
 * Class Location
 *
 * @package Emarketa\AdvancedLogger\Model\Config\Source\Datadog
 */
class Location implements OptionSourceInterface
{
    const LOCATION_EU = "EU";
    const LOCATION_USA = "USA";

    /**
     * @inheritDoc
     */
    public function toOptionArray()
    {
        return [
            ['value' => self::LOCATION_EU, 'label' => 'Europe'],
            ['value' => self::LOCATION_USA, 'label' => 'United States'],
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
            self::LOCATION_EU => 'Europe',
            self::LOCATION_USA => 'United States'
        ];
    }

}
