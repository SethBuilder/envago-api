<?php

namespace App\Logging;

use Aws\CloudWatchLogs\CloudWatchLogsClient;
use Maxbanton\Cwh\Handler\CloudWatch;
use Monolog\Formatter\LineFormatter;
use Monolog\Logger;

class CloudWatchLogger
{
    public function __invoke(array $config): Logger
    {
        $client = new CloudWatchLogsClient([
            'region'  => env('AWS_DEFAULT_REGION', 'sa-east-1'),
            'version' => 'latest',
        ]);

        $handler = new CloudWatch(
            $client,
            '/envago/api/laravel',
            env('APP_ENV', 'production'),
            14,
            10000,
            ['app' => config('app.name')],
            \Monolog\Logger::toMonologLevel($config['level'] ?? 'debug'),
        );

        $handler->setFormatter(new LineFormatter(
            "[%datetime%] %channel%.%level_name%: %message% %context% %extra%\n",
            'Y-m-d H:i:s',
            true,
            true,
        ));

        return new Logger('envago', [$handler]);
    }
}
