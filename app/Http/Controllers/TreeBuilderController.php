<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use OpenTelemetry\Context\Context;
use OpenTelemetry\SDK\Trace\Span;
use OpenTelemetry\SDK\Trace\TracerProvider;

class TreeBuilderController extends Controller
{
    private int $sleepInUs = 100;
    private function recursion($currentDepth, $maxDepth) {
        if ($currentDepth > $maxDepth) {
            return;
        }
        $tracer = TracerProvider::getDefaultTracer();

        $currentActiveSpan = Span::fromContext(Context::getCurrent());

        // Left child
        $currentSpan = $tracer->spanBuilder($currentActiveSpan->getName() . '0')->startSpan();
        $currentSpan->setAttribute('Level', $currentDepth);
        $currentSpan->setAttribute('Child type', 'left');
        $currentScope = $currentSpan->activate();
        usleep($this->sleepInUs);
        $this->recursion($currentDepth + 1, $maxDepth);
        usleep($this->sleepInUs);
        $currentScope->detach();
        $currentSpan->end();

        // Right child
        $currentSpan = $tracer->spanBuilder($currentActiveSpan->getName() . '1')->startSpan();
        $currentSpan->setAttribute('Level', $currentDepth);
        $currentSpan->setAttribute('Child type', 'right');
        $currentScope = $currentSpan->activate();
        usleep($this->sleepInUs);
        $this->recursion($currentDepth + 1, $maxDepth);
        usleep($this->sleepInUs);
        $currentScope->detach();
        $currentSpan->end();

        return;
    }

    public function index($width = 2, $depth = 3){
        date_default_timezone_set('Asia/Kolkata');
        $date = date('d/m/Y h:i:s a', time());

        global $rootSpan;
        if ($rootSpan) {
            /** @var Span $rootSpan */
            $rootSpan->setAttribute('Total Span Count', 1 + $width * (pow(2, $depth + 1) - 1));
            $rootSpan->updateName('TreeBuilderController\\index dated ' . $date);

            $tracer = TracerProvider::getDefaultTracer();
            for ($i = 1; $i <= $width; $i++) {
                $currentSpan = $tracer->spanBuilder('Root('. $i . ') - r')->startSpan();
                $currentSpan->setAttribute('Generation', 'root');
                $currentScope = $currentSpan->activate();
                $this->recursion(1, $depth);
                $currentScope->detach();
                $currentSpan->end();
            }
        }

        return 'And all that I can see, Is just a Complete Binary Tree - \'Software\' Fools Garden';
    }
}
