<?php

namespace App\Console\Commands;

use Closure;
use Exception;

trait Bliss
{
    protected $errors;

    public function bliss(Closure $fn, array $args = [])
    {
        try {
            return $fn->call($this, ...$args);
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
            $this->error("!!! " . count($this->errors) . " Errors !!!");

            if ($this->output->isVerbose()) {
                foreach ($this->errors as $e) {
                    $this->error($e->getMessage());
                }
            }
        }
    }
}