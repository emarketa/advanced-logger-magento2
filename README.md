# Emarketa Advanced Logger

### Version 0.3.0 (Beta) A module to send your Magento 2 logs to an Elasticsearch server or to Datadog.

This module was born from a need to centralise logs from multiple web nodes running a Magento 2 site. It currently has 3 solutions for sending logs to an external service for consumption and will capture just about everything the Monolog logger (which comes with Magento 2) handles.

## Installation

Install or upgrade via Composer with the command: 

`composer require emarketa/advanced-logger-magento2`. 

_Requires Magento 2 and PHP 7.0 or above._

## Elasticsearch Logger

Configuration is available within the Magento 2 admin via __STORES__ > __Configuration__ > __EMARKETA__ > __Advanced Logger__ > _Elasticsearch_.

### Configuration options:

* __Enable Elasticsearch Logger:__ Here you can enable logging to Elasticsearch server/s specified in the _Hosts_ configuration.

* __Enabled in Developer Mode:__ As developer mode creates a lot of log records, this may not be something you want to have enabled all the time (if ever) so it is disabled by default just in case you refresh your local development database from a copy of production and forget to disable this feature. However, the option is here if you need to override it.

* __Minimum Level:__ This is set to _WARNING_ by default to prevent a situation where Magento 2 debug logs causes a flood of log records to turn up in your Elasticsearch server. Lower the level with caution.

* __Index Label Suffix:__ When creating a new index to log to, Advanced Logger will attempt to create an index named `magento_log_[SUFFIX]`. If left blank, the index name will imaginatively default to the word `default` creating the index name `magento_log_default`. You have the opportunity here to have separate index names for different sites or even different store views in Magento 2.

#### Connection 

* __Hosts:__ Here you can specify the hosts passed to the Elasticsearch PHP module (https://github.com/elastic/elasticsearch-php). A comma separated list (avoid spaces here) will set-up multiple hosts. Your entry should conform to the following format: `host:port,host:port.` User names and passwords would also be included in the hosts for HTTP Auth.

> Examples:
> 
> For HTTP Auth use the following format: `http://user:pass@1.2.3.4:9200`
>
> For SSL the following format: `https://1.2.3.4:9200`
> 
> If you need to specify a certificate file, then please add this via the command line like so: 
> 
> `bin/magento config:set advanced_logger/elasticsearch/cafile /full/path/to/ca.pem`. 
> 
> This will be passed on to the `elastic/elasticsearch-php` module but please note, secure connections to Elasticsearch are not yet fully tested within the context of this module.

## Datadog Logger

Configuration is available within the Magento 2 admin via __STORES__ > __Configuration__ > __EMARKETA__ > __Advanced Logger__ > _Datadog_.

You have 2 ways to send logs to Datadog:

### Via the Datadog Agent (preferred).

* __Enabled:__ Again, this is to enable writing logs a json formatted file for the Datadog Agent to consume. See here for instructions on installing and setting up the agent: https://docs.datadoghq.com/getting_started/agent/?tab=datadogussite

> TIPS: You will need to configure the path to point to `/path/to/your/magento/var/log/datadog.log.json`
> 
> In your Datadog's PHP configuration file, you would have something like this:
> 
> ```yaml
> init_config:
> 
> instances:
> 
> ## Log section
> logs:
> 
>   - type: file
>     path: "/path/to/your/magento/var/log/datadog.log.json"
>     service: php
>     source: php
>     sourcecategory: sourcecode
> ```
> 
> _You can add your own tags in this file too._
> 
> Warning: If you enable HTTP API and the agent method at the same time, and, have a running Datadog agent linked to the same account as your API key, you will get every log twice in your account.

* __Enabled in Developer Mode:__ As developer mode creates a lot of log records, this may not be something you want to have enabled all the time (if ever) so it is disabled by default just in case you refresh your local development database from a copy of production and forget to disable this feature. However, the option is here if you need to override it.

* __Minimum Level:__ This is set to _WARNING_ by default to prevent a situation where Magento 2 debug logs causes a flood of log records to turn up in your Datadog account driving up the cost of storage. Lower the level with caution.

### Via HTTP API

* __Send via Cron:__ If you're unable to set up the Datadog agent on your host but you don't want to block the site in any way when sending logs, you can now use this feature to send logs every minute as part of the Magento cron. Note that this means there's up to a minute delay between the even and when the log is sent to Datadog. The cron will clear the file after each successful send to keep the request sizes to a minimum.


* __Send Logs Individually:__ Because PHP is a blocking language and because PHP Curl hangs until it receives a response, you might be trading off the convenience of not having to set-up the Datadog agent with site performance. You could mitigate this issue further by raising the minimum level to _ERROR_. If debug logs are active and being sent to Datadog, even with the 120 ms time-out, you may experience substantial hangs on levels lower than _WARNING_. 

> TIP: Really, this solution is probably most useful when you want to have some temporary insight into problems with a production cluster of web nodes or you may have it enabled on a staging environment to help audit a release.
> 
> If you do have HTTP enabled permanently, then please note that you should keep the minimum log level set to _WARNING_ or above and that all failed attempts to send a record fail silently.
> 
> Warning: If you enable http and agent methods at the same time, and, have a running Datadog agent linked to the same account, you will get every log twice in your account.

* __Enabled in Developer Mode:__ It's advised you do not enable this in developer mode as you will be charged for the storage and the with so much logging activity you will slow things down as PHP waits for every call to curl. But if you really must enable HTTP in developer mode then here's the option to do that. It is disabled by default just in case you refresh your local development database from a copy of production and forget to disable this feature.

* __Minimum Level:__ Setting below level _WARNING_ could potentially create a large amount of records which could substantially increase your costs; especially if debug logs are being created.

* __Account Region:__ Specify your account region. 	
If unsure, log into your dashboard and look to see if you are at `https://app.datadoghq.eu/` or `https://app.datadoghq.com/`.

* __API Key:__ You need to get this right because there is currently no way of knowing (due to a policy of fire and forget) if it's failing other than waiting seconds or so to see if a log appears.

> For EU accounts your API keys are available here: https://app.datadoghq.eu/account/settings#api
> 
> For US accounts your API keys are available here: https://app.datadoghq.com/account/settings#api

## Licence

Released under the GNU GENERAL PUBLIC LICENSE Version 3. Please remember to accredit usage.
