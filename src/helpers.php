<?php


if (!function_exists('parse_attr')) {
    /**
    * 解析配置
    * @param string $value 配置值
    * @return array|string
    */
    function parse_attr($value = '') {
        $array = preg_split('/[,;\r\n]+/', trim($value, ",;\r\n"));
        if (strpos($value, ':')) {
            $values  = array();
            foreach ($array as $val) {
                list($k, $v) = explode(':', $val);
                $values[$k]   = $v;
            }
        } else {
            $values = $array;
        }
        return $values;
    }
}

if (!function_exists('setting')) {

    function setting($key, $default = null) {
        $cacheKey = config('admin.extensions.settings.cache_key', 'setting_cache');
        $settings = \Cache::remember($cacheKey, 30, function () {
            $settingData = \Pigzzz\Settings\Models\Setting::where('status', true)->get();
            $settings = [];
            foreach ($settingData as $setting) {
                $settings[$setting->key] = $setting->value_format;
            }
            return $settings;
        });

        return $settings[$key] ?: $default;
    }
}