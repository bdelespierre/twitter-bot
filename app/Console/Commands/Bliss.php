<?php

namespace App\Console\Commands;

use Closure;
use Exception;
use Illuminate\Contracts\Debug\ExceptionHandler;

trait Bliss
{
    protected $errors;

    public function bliss(callable $fn, ...$args)
    {
        try {
            return $fn(...$args);
        } catch (Exception $e) {
            $this->errors[] = $e;
        }
    }

    public function hasErrors()
    {
        return (bool) count($this->errors);
    }

    public function report()
    {
        if ($this->hasErrors()) {
            $this->reportError(sprintf("[%d Errors]", count($this->errors)));

            foreach ($this->errors as $e) {
                if ($this->output->isDebug()) {
                    app()[ExceptionHandler::class]->renderForConsole($this->output, $e);
                    continue;
                }

                if ($this->output->isVerbose()) {
                    $this->reportError(sprintf("[%s]\n%s", get_class($e), $e->getMessage()));
                    continue;
                }
            }
        }
    }

    protected function reportError(string $error)
    {
        $lines = explode("\n", $error);
        $max = max(array_map('strlen', $lines));

        $this->output->writeln("");
        $this->output->writeln("<error>  " . str_pad("", $max) . "  </error>");
        foreach ($lines as $line) {
            $this->output->writeln("<error>  " . str_pad($line, $max) . "  </error>");
        }
        $this->output->writeln("<error>  " . str_pad("", $max) . "  </error>");
        $this->output->writeln("");
    }
}
