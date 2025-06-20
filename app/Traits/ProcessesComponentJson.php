<?php

namespace App\Traits;

trait ProcessesComponentJson
{
    public function processComponentJson(string $jsonField = 'components_json', array $uiKeysToUnset = ['component_item_name', 'product_station', 'product_vendor'], string $mergedKey = 'components'): void
    {
        if (!$this->filled($jsonField)) {
            return;
        }
        $decoded = json_decode($this->input($jsonField), true);
        $decoded = array_filter($decoded, function ($item) {
            if (!is_array($item)) return false;
            return collect($item)
                ->filter(fn($v, $k) => $k !== '' && $v !== null && $v !== '')
                ->isNotEmpty();
        });
        $components = [];
        foreach ($decoded as $index => $component) {
            $normalized = [];
            foreach ($component as $key => $value) {
                if ($key === '' || is_int($key)) {
                    continue; // Skip invalid keys
                }
                $this->arraySetByPath($normalized, $this->parseKeyToPath($key), $value);
            }
            // Handle nested components (e.g., ['components' => [1 => [..]]])
            if (isset($normalized[$mergedKey]) && is_array($normalized[$mergedKey])) {
                $nestedComp = reset($normalized[$mergedKey]);
                unset($normalized[$mergedKey]);
                foreach ($uiKeysToUnset as $uiKey) {
                    unset($normalized[$uiKey]);
                }
                $merged = array_merge($nestedComp, $normalized);
                if (isset($merged['remark']) && $merged['remark'] === '') {
                    $merged['remark'] = null;
                }
                $components[$index + 1] = $merged;
            } else {
                $components[$index + 1] = $normalized;
            }
        }
        $this->merge([$mergedKey => $components]);
    }

    protected function parseKeyToPath(string $key): array
    {
        $parts = [];
        preg_match_all('/([^\[\]]+)/', $key, $matches);
        return $matches[1] ?? [];
    }

    protected function arraySetByPath(array &$arr, array $path, $value): void
    {
        $temp = &$arr;
        foreach ($path as $key) {
            if (!isset($temp[$key]) || !is_array($temp[$key])) {
                $temp[$key] = [];
            }
            $temp = &$temp[$key];
        }
        $temp = $value;
    }
}
