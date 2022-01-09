<?php
/**
 * Emarketa - 2022
 *
 */

namespace Emarketa\AdvancedLogger\Crontab;

use Emarketa\AdvancedLogger\Logger\Handler\System\DatadogFile;
use Emarketa\AdvancedLogger\Logger\Handler\System\DatadogHttp;
use Emarketa\AdvancedLogger\Model\Api\Record\DatadogHttpInterface;
use Psr\Log\LoggerInterface;

class SendToDatadog
{
    private DatadogHttpInterface $datadogHttp;
    private DatadogFile $datadogFile;
    private LoggerInterface $logger;
    private DatadogHttp $datadogHttpHandler;

    /**
     * @param DatadogHttpInterface $datadogHttp
     */
    public function __construct(
        DatadogHttpInterface $datadogHttp,
        DatadogHttp $datadogHttpHandler,
        DatadogFile $datadogFile,
        LoggerInterface $logger
    )
    {
        $this->datadogHttp = $datadogHttp;
        $this->datadogHttpHandler = $datadogHttpHandler;
        $this->datadogFile = $datadogFile;
        $this->logger = $logger;
    }

    /**
     * @throws \Zend_Log_Exception
     */
    public function execute()
    {
        try {
            $fileToRead = BP . $this->datadogFile->getFileName();
            if(is_file($fileToRead) && $this->datadogHttpHandler->isCronEnabled()) {
                $result = false;
                $handle = fopen($fileToRead, "r");
                if ($handle) {
                    $sentry = 0;
                    while (($line = fgets($handle)) !== false) {
                        $sentry ++;
                        $record = json_decode($line);
                        $result = $this->datadogHttp->sendRecordToHttpEndpoint($record);
                        if($sentry >= $this->datadogHttpHandler->getCronMaxRecords()) {
                            break; // Remaining records are discarded. Refer to logs on disk.
                        }
                    }
                    fclose($handle);
                    if($result) {
                        $this->emptyFile($fileToRead);
                    }
                }
            }

        } catch (\Exception $e) {
            $writer = new \Zend_Log_Writer_Stream(BP . '/var/log/datadog.cron.log');
            $logger = new \Zend_Log();
            $logger->addWriter($writer);
            $logger->err('Send to datadog failed: ' . $e->getMessage());
        }
    }

    /**
     * @param $file
     * @return bool
     */
    public function emptyFile($file): bool
    {
        $f = @fopen($file, "r+");
        if ($f !== false) {
            ftruncate($f, 0);
            fclose($f);
        }

        return true;
    }
}
