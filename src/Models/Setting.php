<?php

namespace Pigzzz\Settings\Models;

use Encore\Admin\Form;
use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
    protected $fillable = [
        'key', 'name', 'group', 'value',
        'type', 'value', 'options', 'description', 'status',
    ];

    protected $casts = [
        'status' => 'boolean',
    ];

    public function getGroupTextAttribute()
    {
        return config('admin.extensions.settings.groups')[$this->group];
    }

    public function getTypeTextAttribute()
    {
        return class_basename(Form::$availableFields[$this->type]);
    }

    public function getValueFormatAttribute()
    {
        switch ($this->type) {
            case 'checkbox':
            case 'multipleSelect':
                $value = parse_attr($this->value);
                break;
            default:
                $value = $this->value;
                break;
        }
        return $value;
    }

    public function getOptionsArrayAttribute()
    {
        return parse_attr($this->options);
    }
}
