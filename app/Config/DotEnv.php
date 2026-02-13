<?php

namespace Config;

use CodeIgniter\Config\DotEnv as BaseDotEnv;

/**
 * Custom DotEnv class that works without putenv()
 * 
 * This class extends the base DotEnv but uses $_SERVER and $_ENV
 * directly instead of putenv() which may be disabled on some servers.
 */
class DotEnv extends BaseDotEnv
{
    /**
     * Sets an environment variable.
     * 
     * Uses $_SERVER and $_ENV directly instead of putenv()
     * to work on servers where putenv() is disabled.
     *
     * @param string $name  The name of the environment variable
     * @param mixed  $value The value to set
     */
    protected function setVariable(string $name, $value = null): void
    {
        // Use $_SERVER and $_ENV directly instead of putenv()
        // This works even when putenv() is disabled
        $_SERVER[$name] = $value;
        $_ENV[$name]    = $value;
        
        // Try putenv() if available, but don't fail if it's disabled
        // Check if putenv is not in disabled_functions list
        $disabledFunctions = ini_get('disable_functions');
        $isPutenvDisabled = $disabledFunctions && in_array('putenv', explode(',', $disabledFunctions), true);
        
        if (function_exists('putenv') && !$isPutenvDisabled) {
            @putenv("{$name}={$value}");
        }
    }
}
