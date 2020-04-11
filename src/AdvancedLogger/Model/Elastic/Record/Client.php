<?php
/**
 * Emarketa
 */

namespace Emarketa\AdvancedLogger\Model\Elastic\Record;

use Elasticsearch\ClientBuilder;
use Emarketa\AdvancedLogger\Model\Api\Record\ElasticsearchClientInterface;
use Exception;
use Magento\Framework\App\Config\ScopeConfigInterface;

/**
 * Class Send
 *
 * @package Emarketa\AdvancedLogger\Model\Elastic\Record
 */
class Client implements ElasticsearchClientInterface
{
    const CONFIG_CONNECTION = 'advanced_logger/elasticsearch/connection';
    const CONFIG_STORE_SUFFIX = 'advanced_logger/elasticsearch/index_suffix';

    /**
     * @var \Elasticsearch\Client
     */
    private $client;
    /**
     * @var ScopeConfigInterface
     */
    private $config;

    /**
     * Client constructor.
     * @param ScopeConfigInterface $config
     */
    public function __construct(
        ScopeConfigInterface $config
    ) {
        $this->config = $config;
    }

    /**
     * Method build
     *
     * @return bool
     */
    public function build()
    {
        $connection = $this->getConnectionArrayFromConfig();
        if (empty($connection) || !isset($connection['hosts'])) {
            return false;
        }

        try {
            $hosts = explode(',', $connection['hosts']);
            $clientBuilder = ClientBuilder::create()->setHosts($hosts);

            if (isset($connection['ca'])) {
                $clientBuilder->setSSLVerification($connection['ca']);
            }

            // Only available in Magento 2.3.5 which offers support for ES 7.5
            // See https://devdocs-beta.magento.com/guides/v2.3/release-notes/release-notes-2-3-5-commerce.html
            if (isset($connection['api']['key']) &&
                isset($connection['api']['id']) &&
                method_exists($clientBuilder->setApiKey())
            ) {
                $clientBuilder->setApiKey($connection['api']['id'], $connection['api']['key']);
            }

            $this->client = $clientBuilder->build();
            $result = $this->provisionIndex($this->getStoreSuffixString());
        } catch (Exception $e) {
            // @todo need to send a notification to admin here.
            $result = false;
        }

        return $result;
    }

    /**
     * Method sendRecordToElasticSearch
     *
     * @param array $record
     * @return bool
     */
    public function sendRecordToElasticSearch(array $record)
    {
        try {
            $this->client->create(
                [
                'id' => microtime(),
                'index' => $this->getIndexName($this->getStoreSuffixString()),
                'type' => '_doc',
                'body' => $record
                ]
            );
        } catch (\Exception $e) {
            return false;
        }

        return true;
    }

    /**
     * Method getStorePrefixString
     *
     * @return string
     */
    public function getStoreSuffixString()
    {
        $inConfig = $this->config->getValue(self::CONFIG_STORE_SUFFIX);
        return empty($inConfig) ? "default" : $inConfig;
    }

    /**
     * Method getIndexParams
     *
     * @param string $indexName
     * @return array
     */
    public function getIndexParams(string $indexName)
    {
        return [
            'index' => $indexName,
            'body' => [
                'settings' => [
                    'number_of_shards' => 2,
                    'number_of_replicas' => 0
                ],
                'mappings' => [
                    'properties' => [
                        'hash_id' => [
                            'type' => 'keyword',
                        ],
                        'datetime' => [
                            'type' => 'date'
                        ],
                        'level' => [
                            'type' => 'keyword'
                        ],
                        'report_id' => [
                            'type' => 'keyword'
                        ],
                        'record' => [
                            'type' => 'text'
                        ]
                    ]
                ]
            ]
        ];
    }

    /**
     * Method getIndexName
     *
     * @param string $suffix
     * @return string
     */
    public function getIndexName(string $suffix = "")
    {
        return self::INDEX_NAME_PREFIX . strip_tags(str_replace(' ', '-', $suffix));
    }

    /**
     * Method provisionIndex
     *
     * @param string $suffix
     * @return bool
     */
    public function provisionIndex($suffix)
    {
        $indexExists = $this->client->indices()->exists(['index' => $this->getIndexName($suffix)]);
        if (!$indexExists) {
            $params = $this->getIndexParams($this->getIndexName($suffix));
            $this->client->indices()->create($params);
        }

        return $indexExists;
    }

    /**
     * Method getHostsFromConfig
     *
     * @return array
     */
    public function getConnectionArrayFromConfig()
    {
        return (array) $this->config->getValue(self::CONFIG_CONNECTION);
    }
}
