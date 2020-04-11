<?php
/**
 * Emarketa.
 */

namespace Emarketa\AdvancedLogger\Formatter;

use Exception;
use Monolog\Formatter\FormatterInterface;

/**
 * Class AbstractFormatter
 *
 * @package Emarketa\AdvancedLogger\Formatter
 */
abstract class AbstractFormatter implements FormatterInterface
{
    /**
     * @inheritDoc
     */
    public function getMessageString(array $record)
    {
        $context = $record['context'];
        if ($record instanceof Exception) {
            $formattedString = $this->getExceptionString($record);
        } elseif ($record['message'] instanceof Exception) {
            $formattedString = $this->getExceptionString($record['message']);
        } elseif (isset($context["exception"]) && $context['exception'] instanceof Exception) {
            $formattedString = $this->getExceptionString($context["exception"]);
        } elseif (isset($context['exception']) && is_string($context['exception'])) {
            $formattedString = $context['exception'];
        } else {
            try {
                $formattedString = (string) $record['message'];
            } catch (Exception $e) {
                $formattedString = "Unhandled message type: " . "\n\r" . serialize($record);
            }
        }

        return $formattedString;
    }

    /**
     * @inheritDoc
     */
    public function formatBatch(array $records)
    {
        foreach ($records as $key => $record) {
            $records[$key] = $this->format($record);
        }

        return $records;
    }

    /**
     * Method getReportId
     *
     * @todo Refactor: this is a duplicate method
     * @param array $record
     * @return mixed|string
     */
    protected function getReportId(array $record)
    {
        return isset($record["context"]["report_id"]) ? $record["context"]["report_id"] : "";
    }

    /**
     * Method getExceptionString
     *
     * @param Exception $e
     * @return string
     */
    protected function getExceptionString(\Exception $e)
    {
        return $e->getMessage() . " in " . $e->getFile() . " on line " . $e->getLine() . "\n\r" .
            $e->getTraceAsString();
    }
}
