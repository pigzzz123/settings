<?php

namespace Pigzzz\Settings;

use Encore\Admin\Extension;
use Pigzzz\Settings\Models\Setting;

class Settings extends Extension
{
    public $name = 'settings';

    public $views = __DIR__.'/../resources/views';

    public $assets = __DIR__.'/../resources/assets';
}