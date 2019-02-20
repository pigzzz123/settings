<?php

namespace Pigzzz\Settings\Http\Controllers;

use Encore\Admin\Widgets\Tab;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use \Pigzzz\Settings\Models\Setting;
use App\Http\Controllers\Controller;
use Encore\Admin\Controllers\HasResourceActions;
use Encore\Admin\Form;
use Encore\Admin\Widgets\Form as WidgetsFrom;
use Encore\Admin\Grid;
use Encore\Admin\Layout\Content;
use Encore\Admin\Show;

class SettingsController extends Controller
{
    use HasResourceActions;

    /**
     * Index interface.
     *
     * @param Content $content
     * @return Content
     */
    public function index(Content $content)
    {
        return $content
            ->header('配置')
            ->description('列表')
            ->body($this->grid());
    }

    /**
     * Edit interface.
     *
     * @param mixed $id
     * @param Content $content
     * @return Content
     */
    public function edit($id, Content $content)
    {
        return $content
            ->header('配置')
            ->description('编辑')
            ->body($this->form()->edit($id));
    }

    public function update($id)
    {
        $this->forgetSettingCache();
        return $this->form()->update($id);
    }

    /**
     * Create interface.
     *
     * @param Content $content
     * @return Content
     */
    public function create(Content $content)
    {
        return $content
            ->header('配置')
            ->description('创建')
            ->body($this->form());
    }

    public function store()
    {
        $this->forgetSettingCache();
        return $this->form()->store();
    }

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new Setting);

        $grid->model()->orderBy('order', 'asc')->orderBy('created_at', 'desc');

        $grid->id('ID');
        $grid->order('排序')->editable();
        $grid->group('分组')->display(function ($value) {
            return "<span class='label label-success'>{$this->group_text}</span>";
        });
        $grid->key('key');
        $grid->name('名称');
        $grid->type('类型')->display(function ($value) {
            return "<span class='label label-warning'>{$this->type_text}</span>";
        });
        $grid->status('状态')->switch([
            'on'  => ['value' => 1, 'text' => '启用', 'color' => 'primary'],
            'off' => ['value' => 0, 'text' => '禁用', 'color' => 'default'],
        ]);
        $grid->created_at('创建时间');

        $grid->actions(function ($actions) {
            $actions->disableView();
        });

        $grid->filter(function($filter){
            $types = [];
            foreach (Form::$availableFields as $key => $class) {
                $types[$key] = class_basename($class);
            }

            // 去掉默认的id过滤器
            $filter->disableIdFilter();

            $filter->equal('group', '分组')->select(config('admin.extensions.settings.groups'));
            $filter->equal('type', '类型')->select($types);
            $filter->like('key', 'key');
            $filter->like('name', '名称');
            $filter->equal('status', '状态')->radio([
                ''   => '全部',
                1    => '启用',
                0    => '禁用',
            ]);

        });

        return $grid;
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form()
    {
        $form = new Form(new Setting);

        $types = [];
        foreach (Form::$availableFields as $key => $class) {
            $types[$key] = class_basename($class);
        }

        $form->select('group', '分组')->options(config('admin.extensions.settings.groups'))->rules('nullable');
        $form->select('type', '类型')->options($types)->rules('required');
        $form->text('key', 'Key')->rules('required');
        $form->text('name', '名称')->rules('required');
        $form->textarea('value', '配置值')->rules('required');
        $form->textarea('options', '配置项')->rules('nullable');
        $form->text('description', '描述')->rules('nullable');
        $form->number('order', '排序')->default(99);
        $form->switch('status', '状态')->default(1)->states([
            'on'  => ['value' => 1, 'text' => '启用', 'color' => 'primary'],
            'off' => ['value' => 0, 'text' => '禁用', 'color' => 'default'],
        ]);

        $form->tools(function (Form\Tools $tools) {
        // 去掉`查看`按钮
        $tools->disableView();
    });

        $form->footer(function ($footer) {
            // 去掉`查看`checkbox
            $footer->disableViewCheck();
        });

        return $form;
    }

    public function display(Content $content)
    {
        return $content
            ->header('配置参数')
            ->description(' ')
            ->body($this->displayForm());
    }

    public function displayForm()
    {
        $form = new WidgetsFrom();
        $form->setTitle('管理');

        $tab = new Tab();

        $settings = Setting::where('status', true)
            ->orderBy('order', 'asc')
            ->orderBy('created_at', 'desc')
            ->get();
        $settingData = $settings->pluck('value', 'key');

        if (config('admin.extensions.settings.groups')) {
            $settingGroups = [];
            foreach ($settings as $setting) {
                $settingGroups[$setting['group']][] = $setting;
            }

            foreach ($settingGroups as $key => $settingGroup) {
                $tab->add(config('admin.extensions.settings.groups')[$key], $this->renderSetting($settingGroup, $settingData));
            }
            $content = $tab->render();
        }else {
            $content = $this->renderSetting($settings, $settingData);
        }

        return $content;
    }

    public function handleDisplay(Request $request, Setting $setting)
    {
        $data = $request->all();
        $items = $setting->where('status', true)->get()->pluck('type', 'key');
        foreach ($items as $key => $type) {
            if (!isset($data[$key])) {
                switch ($type) {
                    // 开关
                    case 'switch':
                        $data[$key] = 0;
                        break;
                    case 'checkbox':
                        $data[$key] = '';
                        break;
                }
            } else {
                // 如果值是数组则转换成字符串，适用于复选框等类型
                if (is_array($data[$key])) {
                    $data[$key] = implode(',', $data[$key]);
                }
                switch ($type) {
                    // 开关
                    case 'switch':
                        $data[$key] = 1;
                        break;
                }
            }
            $setting->where('key', $key)->update(['value' => $data[$key]]);
        }
        $this->forgetSettingCache();

        admin_toastr(trans('admin.save_succeeded'));

        return redirect(admin_base_path('settings/display'));
    }

    protected function renderSetting($settings = [], $settingData = [])
    {
        $form = new WidgetsFrom($settingData);
        $form->action(admin_base_path('settings/display'));

        foreach ($settings as $setting) {
            $className = Form::$availableFields[$setting->type];
            $element = (new $className($setting->key, [$setting->name]));
            switch ($setting->type) {
                case 'select':
                case 'multipleSelect':
                case 'radio':
                case 'checkbox':
                    $element->options($setting->optionsArray);
                    break;
            }
            $form->pushField($element);
        }

        return $form->render();
    }

    protected function forgetSettingCache()
    {
        $cacheKey = config('admin.extensions.settings.cache_key', 'setting_cache');
        \Cache::forget($cacheKey);
    }
}
