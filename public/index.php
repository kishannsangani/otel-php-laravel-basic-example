<?php

use Illuminate\Contracts\Http\Kernel;
use Illuminate\Http\Request;

use OpenTelemetry\Contrib\Jaeger\Exporter as JaegerExporter;
use OpenTelemetry\Contrib\Zipkin\Exporter as ZipkinExporter;
use OpenTelemetry\SDK\AbstractClock;
use OpenTelemetry\SDK\Trace\SpanProcessor\BatchSpanProcessor;
use OpenTelemetry\SDK\Trace\SpanProcessor\SimpleSpanProcessor;
use OpenTelemetry\SDK\Trace\SpanProcessor\MultiSpanProcessor;
use OpenTelemetry\SDK\Trace\TracerProvider;
use GuzzleHttp\Client;
use GuzzleHttp\Psr7\HttpFactory;

define('LARAVEL_START', microtime(true));

/*
|--------------------------------------------------------------------------
| Check If The Application Is Under Maintenance
|--------------------------------------------------------------------------
|
| If the application is in maintenance / demo mode via the "down" command
| we will load this file so that any pre-rendered content can be shown
| instead of starting the framework, which could cause an exception.
|
*/

if (file_exists($maintenance = __DIR__.'/../storage/framework/maintenance.php')) {
    require $maintenance;
}

/*
|--------------------------------------------------------------------------
| Register The Auto Loader
|--------------------------------------------------------------------------
|
| Composer provides a convenient, automatically generated class loader for
| this application. We just need to utilize it! We'll simply require it
| into the script here so we don't need to manually load our classes.
|
*/

require __DIR__.'/../vendor/autoload.php';

/*
|--------------------------------------------------------------------------
| Run The Application
|--------------------------------------------------------------------------
|
| Once we have the application, we can handle the incoming request using
| the application's HTTP kernel. Then, we will send the response back
| to this client's browser, allowing them to enjoy our application.
|
*/

$jaegerExporter = new JaegerExporter(
    'Hello World Web Server Jaeger',
    'http://localhost:9412/api/v2/spans',
    new Client(),
    new HttpFactory(),
    new HttpFactory()
);

$zipkinExporter = new ZipkinExporter(
    'Hello World Web Server Zipkin',
    'http://localhost:9411/api/v2/spans',
    new Client(),
    new HttpFactory(),
    new HttpFactory()
);

$tracer = (new TracerProvider(
                              new MultiSpanProcessor(
                                                    new SimpleSpanProcessor($jaegerExporter),
                                                    new BatchSpanProcessor($zipkinExporter, AbstractClock::getDefault())
                                                    )
                             )
          )
          ->getTracer('io.opentelemetry.contrib.php');

$request = Request::createFromGlobals();
$rootSpan = $tracer->spanBuilder($request->getUri())->startSpan();
$rootScope = $rootSpan->activate();

$app = require_once __DIR__.'/../bootstrap/app.php';

$kernel = $app->make(Kernel::class);

$response = $kernel->handle(
    $request = Request::capture()
)->send();

$kernel->terminate($request, $response);

$rootScope->detach();
$rootSpan->end();