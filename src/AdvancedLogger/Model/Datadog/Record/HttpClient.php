<?php
/**
 * Emarketa.
 */

namespace Emarketa\AdvancedLogger\Model\Datadog\Record;

use Emarketa\AdvancedLogger\Model\Api\Record\DatadogHttpInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\HTTP\Client\Curl;
use Magento\Framework\HTTP\Client\CurlFactory;

/**
 * Class Client
 *
 * @package Emarketa\AdvancedLogger\Model\Datadog\Record
 */
class HttpClient implements DatadogHttpInterface
{
    const CONFIG_DD_HTTP_REGION = 'advanced_logger/datadog/http_endpoint/account_region';
    const CONFIG_DD_HTTP_API_KEY = 'advanced_logger/datadog/http_endpoint/api_key';
    const CONFIG_DD_HTTP_INTAKE_URLS = 'advanced_logger/datadog/http_endpoint/intake_url';

    /**
     * @var CurlFactory
     */
    private $curlFactory;
    /**
     * @var ScopeConfigInterface
     */
    private $config;

    /**
     * Client constructor.
     * @param CurlFactory $curlFactory
     * @param ScopeConfigInterface $config
     */
    public function __construct(
        CurlFactory $curlFactory,
        ScopeConfigInterface $config
    ) {
        $this->curlFactory = $curlFactory;
        $this->config = $config;
    }

    /**
     * Method sendRecordToDataDog
     *
     * @param array $record
     * @return bool
     */
    public function sendRecordToHttpEndpoint($record)
    {
        $url = $this->getPostUrl();
        $curl = $this->getCurl();
        $record = json_encode($record);

        try {
            $curl->post($url, $record);
            if ($curl->getStatus() !== 200) {
                throw new \ErrorException($curl->getBody());
            }
        } catch (\Exception $e) {
            // We fire and forget as we set the timeout to be 120 milliseconds.
            $timedOut = strpos($e->getMessage(), "timed out after");
            if ($timedOut > 0) {
                return true;
            } else {
                # TODO: Alert the end user.
                return false;
            }
        }

        return true;
    }

    /**
     * Method getConnectionFromConfig
     *
     * @return Curl
     */
    public function getCurl()
    {
        $curl = $this->curlFactory->create();
        $curl->addHeader("Content-Type", "application/json");
        $curl->setTimeout(0);
        $curl->setOptions(
            [
                CURLOPT_TIMEOUT_MS => 120,
                CURLOPT_RETURNTRANSFER => 1
            ]
        );
        return $curl;
    }

    /**
     * Method getPostUrl
     *
     * @return string
     */
    public function getPostUrl()
    {
        $region = $this->config->getValue(self::CONFIG_DD_HTTP_REGION);
        $urls = $this->config->getValue(self::CONFIG_DD_HTTP_INTAKE_URLS);
        return $urls[$region] . "input/" . $this->config->getValue(self::CONFIG_DD_HTTP_API_KEY);
    }
}
