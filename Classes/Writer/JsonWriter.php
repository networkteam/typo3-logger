<?php

namespace Networkteam\Logger\Writer;

use TYPO3\CMS\Core\Core\Environment;
use TYPO3\CMS\Core\Log\LogRecord;
use TYPO3\CMS\Core\Log\Writer\AbstractWriter;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * A JSON LogWriter that logs to stderr.
 */
class JsonWriter extends AbstractWriter
{
    protected array $options;

    public function __construct(array $options = [])
    {
        $this->options = $options;
    }

    public function writeLog(LogRecord $record)
    {
        $context = $record->getData();
        $message = $this->interpolate($record->getMessage(), $context);
        $throwable = $context['exception'] ?? null;

        if ($throwable instanceof \Throwable) {
            $message = sprintf(
                '%s: %s',
                $context['exception_class'] ?? $this->getExceptionClass($throwable),
                $throwable->getMessage()
            );
            $context['file'] = str_replace(Environment::getProjectPath() . '/', '', $throwable->getFile());
            unset(
                $context['exception'],
                $context['exception_class'],
                $context['message'],
                $context['request_url']
            );
        }

        $data = [
            'time' => date('r', (int)$record->getCreated()),
            'severity' => $record->getLevel(),
            'message' => $message,
            'component' => $record->getComponent(),
            'source' => 'typo3',
            'typo3_request_id' => $record->getRequestId(),
            'context' => $context
        ];

        $method = $_SERVER['REQUEST_METHOD'] ?? null;
        if ($method) {
            $data['url'] = $this->anonymizeToken(GeneralUtility::getIndpEnv('TYPO3_REQUEST_URL'));
            $data['method'] = $method;
            $requestId = $_SERVER['X-REQUEST-ID'] ?? $_SERVER['HTTP_X_REQUEST_ID'] ?? null;
            if ($requestId) {
                $data['request_id'] = $requestId;
            }
        } else {
            global $argv;
            $data['command_line'] = implode(' ', $argv);
        }

        if ($this->options !== []) {
            $data['logger_context'] = $this->options;
        }

        $stderr = fopen('php://stderr', 'a');
        fwrite($stderr, json_encode($data) . PHP_EOL);
        fclose($stderr);

        return $this;
    }

    protected function getExceptionClass(\Throwable $throwable): string
    {
        $classname = get_class($throwable);
        if ($pos = strrpos($classname, '\\')) {
            $classname = substr($classname, $pos + 1);
        }
        return $classname;
    }

    /**
     * Replaces the generated token with a generic equivalent
     *
     * @param string $requestedUrl
     * @return string
     */
    protected function anonymizeToken(string $requestedUrl): string
    {
        $pattern = '/(?:(?<=[tT]oken=)|(?<=[tT]oken%3D))[0-9a-fA-F]{40}/';
        return preg_replace($pattern, '--AnonymizedToken--', $requestedUrl);
    }
}
