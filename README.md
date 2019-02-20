# Application settings for laravel-admin

这是一个 `laravel-admin` 扩展，为 `laravel-admin` 增加参数配置功能。

## 依赖

laravel-admin >= 1.6

## 截图

![screenshot1](https://user-images.githubusercontent.com/24596908/53073934-6a4c7080-3524-11e9-8470-94b9f5e1671d.png)
![screenshot2](https://user-images.githubusercontent.com/24596908/53073880-3ffab300-3524-11e9-8d62-0def21bb8601.png)

## 安装

```bash
composer require pigzzz123/settings
php artisan vendor:publish --provider=Pigzzz\Settings\SettingsServiceProvider.php
```

## 配置

在`config/admin.php`文件的`extensions`，加上属于这个扩展的一些配置

```php
'extensions' => [
    'settings' => [
        // 配置分组
        'groups' => [
            'base' => '基础'
        ],
        // 配置缓存key
        'cache_key' => 'setting_cache',
    ]
],
```

## 使用

**添加菜单**
- /settings 配置列表
- /settings/display 配置参数

License
------------
Licensed under [The MIT License (MIT)](LICENSE).

