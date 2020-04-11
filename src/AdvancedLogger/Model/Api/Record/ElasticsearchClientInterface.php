<?php
/**
 * Emarketa.
 */

namespace Emarketa\AdvancedLogger\Model\Api\Record;

interface ElasticsearchClientInterface
{
    const INDEX_NAME_PREFIX = 'magento_log_';

    /**
     * Method build
     *
     * @return bool
     */
    public function build();

    /**
     * Method sendRecordToElasticSearch
     *
     * @param array $record
     * @return bool
     */
    public function sendRecordToElasticSearch(array $record);

    /**
     * Method getIndexParams
     *
     * @param string $indexName
     * @return array
     */
    public function getIndexParams(string $indexName);

    /**
     * Method getIndexName
     *
     * @param string $suffix
     * @return string
     */
    public function getIndexName(string $suffix = "");

    /**
     * Method provisionIndex
     *
     * @param string $suffix
     * @return bool
     */
    public function provisionIndex($suffix);
}
