# TYPO3 Logger

This extension provides a JSON LogWriter that logs to stderr.

## Installation

```shell
composer req networkteam/typo3-logger
```

## Configuration

Basic configuration

```yaml
LOG:
  writerConfiguration:
    warning:
      Networkteam\Logger\Writer\JsonWriter:
        foo: 'bar'
```

In container environments it might be handy to register the JsonWriter as XClass for the FileWriter. This way no log
files are written to `typo3temp/var/log/`.

```yaml
SYS:
  Objects:
    TYPO3\CMS\Core\Log\Writer\FileWriter:
      className: 'Networkteam\Logger\Writer\JsonWriter'
```

## Example output

```json
{
  "time": "Fri, 09 Feb 2024 21:21:49 +0100",
  "severity": "critical",
  "message": "Exception: test",
  "component": "TYPO3.CMS.Core.Error.DebugExceptionHandler",
  "source": "typo3",
  "typo3_request_id": "6be0a6b53348f",
  "context": {
    "mode": "WEB",
    "application_mode": "BE",
    "exception_code": 0,
    "file": "typo3/typo3/sysext/backend/Classes/Middleware/BackendUserAuthenticator.php",
    "line": 95
  },
  "url": "http://localhost:8080/typo3/module/system/config",
  "method": "GET",
  "logger_context": {
    "foo": "bar"
  }
}
```
```json
{
  "time": "Fri, 09 Feb 2024 21:23:07 +0100",
  "severity": "warning",
  "message": "Illegal filepath \"EXT:calendarize/Configuration/TypoScript/setup.typoscript\".",
  "component": "TYPO3.CMS.Core.TypoScript.Parser.TypoScriptParser",
  "source": "typo3",
  "typo3_request_id": "c429ef02dcf9d",
  "external_request_id": "96a101dd-c49a-4fea-aee2-a76510f32190",
  "context": [],
  "url": "http://localhost:8080/typo3/module/system/config?token=--AnonymizedToken--",
  "method": "GET",
  "logger_context": {
    "foo": "bar"
  }
}
```

## PHP-FPM Configuration

```ini
[www]
catch_workers_output = yes
decorate_workers_output = no
```


## Usage by 3rd parties

Please feel free to use. Expect breaking changes at any time.