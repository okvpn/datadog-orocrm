# OroPlatform datadog integration

OroPlatform [Datadog][1] integration, that bases on [Symfony datadog bundle][4] to monitor and track for application 
errors and send notifications about them.

[![Build Status](https://travis-ci.org/okvpn/datadog-orocrm.svg?branch=master)](https://travis-ci.org/okvpn/datadog-orocrm) [![Latest Stable Version](https://poser.pugx.org/okvpn/datadog-orocrm/v/stable)](https://packagist.org/packages/okvpn/datadog-orocrm) [![Latest Unstable Version](https://poser.pugx.org/okvpn/datadog-orocrm/v/unstable)](https://packagist.org/packages/okvpn/datadog-orocrm) [![Total Downloads](https://poser.pugx.org/okvpn/datadog-orocrm/downloads)](https://packagist.org/packages/okvpn/datadog-orocrm) [![License](https://poser.pugx.org/okvpn/datadog-orocrm/license)](https://packagist.org/packages/okvpn/datadog-orocrm)

## Benefits

Use datadog bundle for:

* Monitor production applications in realtime.
* Application performance insights to see when performance is geting degradated.
* Able to raise alarm when MQ is not running or cron is not enabled.
* Access to the `okvpn_datadog.client` through the container.
* Send notification about errors in Slack, email, telegram, etc.
* Create JIRA issue when some alarm/exception triggers using this [plugin][5]

## Compatible ORO Platform versions

Supported 2.6.* - 3.1.* ORO Platform versions on mysql or postgresql DB.

## Install
Install using [composer][2] following the official Composer [documentation][3]: 

1. Install via composer:
```
composer require okvpn/datadog-orocrm
```

2. Delete your cache `rm -rf app/cache/{dev,prod,test}/`

3. Run oro platform update `oro:platform:update --force --skip-search-reindexation`

4. Update `config.yml` to enable 

```
okvpn_datadog:
    profiling: true
    namespace: orocrm # You app namespace for custome metric app.*, see https://docs.datadoghq.com/developers/metrics/#naming-metrics
```

See more configuration example [here][4]

## Custom metrics that provided by OkvpnDatadogBundle

Where `orocrm` metrics namespace.

|    Name                          |    Type      |                         Description                                        |
|----------------------------------|:------------:|:--------------------------------------------------------------------------:|
| orocrm.exception                 | counter      | Track how many exception occurred in application per second                |
| orocrm.doctrine.median           | gauge        | Median execute time of sql query (ms.)                                     |
| orocrm.doctrine.avg              | gauge        | Avg execute time of sql query (ms.)                                        |
| orocrm.doctrine.count            | rate         | Count of sql queries per second                                            |
| orocrm.doctrine.95percentile     | gauge        | 95th percentile of execute time of sql query (ms.)                         |
| orocrm.exception                 | event        | Event then exception is happens                                            |
| orocrm.http_request              | timing       | Measure timing how long it takes to fully render a page                    |
| orocrm.mq.mem                    | gauge        | Gives memory usage by all consumers                                        |
| orocrm.mq.messages               | timing       | Gives timing message queue processing statistics                           |
| orocrm.mq.consumers              | set          | Gives count of running/active consumers                                    |
| orocrm.service:cron              | service_check| Track the status of cron                                                   |

## Usage

More usage example you can found [here][4] 

License
-------
MIT License. See [LICENSE](LICENSE).

[1]:    https://docs.datadoghq.com/getting_started/
[2]:    https://getcomposer.org/
[3]:    https://getcomposer.org/download/
[4]:    https://github.com/okvpn/datadog-symfony
[5]:    https://www.datadoghq.com/blog/jira-issue-tracking/
