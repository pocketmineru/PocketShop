<?php

declare(strict_types=1);

if (!function_exists('yaml_parse')) {
    function yaml_parse(string $input): array|false {
        $data = [];
        $lines = explode("\n", $input);
        $currentKey = null;
        $indent = 0;

        foreach ($lines as $line) {
            if (preg_match('/^#/', trim($line))) continue;
            if (trim($line) === '') continue;

            if (preg_match('/^(\w+):$/', trim($line), $matches)) {
                $currentKey = $matches[1];
                $data[$currentKey] = [];
                $indent = strlen($line) - strlen(ltrim($line));
                continue;
            }

            if (preg_match('/^-\s*\{(.+)\}$/', trim($line), $matches)) {
                $item = [];
                preg_match_all('/(\w+):\s*([^,]+)/', $matches[1], $parts);
                foreach ($parts[1] as $i => $key) {
                    $value = trim($parts[2][$i]);
                    if (is_numeric($value)) {
                        $value = (int) $value;
                    }
                    $item[$key] = $value;
                }
                $data[$currentKey][] = $item;
            }
        }

        return $data ?: false;
    }
}