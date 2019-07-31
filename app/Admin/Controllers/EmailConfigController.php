<?php

namespace App\Admin\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Config;
use Encore\Admin\Controllers\HasResourceActions;
use Encore\Admin\Facades\Admin;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Layout\Content;
use Encore\Admin\Layout\Row;
use Encore\Admin\Widgets\Box;

class EmailConfigController extends Controller
{
    use HasResourceActions;

    /**
     * Index interface.
     *
     * @return Content
     */
    public function index()
    {
        return Admin::content(function (Content $content) {

            $content->header(trans('language.admin.config_control'));
            $content->description(' ');
            $body = $this->grid();
            // $content->body($this->grid());
            $content->row(function (Row $row) use ($content, $body) {
                $row->column(1 / 2, $body);
                $row->column(1 / 2, new Box(trans('language.admin.email_action.config_smtp'), $this->viewSMTPConfig()));
            });
        });
    }

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new Config);
        $grid->detail(trans('language.admin.email_action.manager'))->display(function ($detail) {
            return trans(htmlentities($detail));
        });
        $states = [
            '1' => ['value' => 1, 'text' => 'YES', 'color' => 'primary'],
            '0' => ['value' => 0, 'text' => 'NO', 'color' => 'default'],
        ];
        $grid->value(trans('language.admin.email_action.mode'))->switch($states);
        // $grid->sort(trans('language.admin.email_action.sort'));
        $grid->model()->where('code', 'email_action')->orderBy('sort', 'asc');
        $grid->disableCreation();
        $grid->disableExport();
        $grid->disableRowSelector();
        $grid->disableFilter();
        $grid->disableActions();
        $grid->disableTools();
        $grid->disablePagination();
        return $grid;
    }
    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form()
    {
        return Admin::form(Config::class, function (Form $form) {

            $form->display('id', 'ID');
            $form->text('code', 'Code');
            $form->text('key', 'Key');
            $form->number('sort', 'Sort');
            $states = [
                '1' => ['value' => 1, 'text' => 'YES', 'color' => 'primary'],
                '0' => ['value' => 0, 'text' => 'NO', 'color' => 'default'],
            ];
            $form->switch('value', 'Value')->states($states);
            $form->disableViewCheck();
            $form->disableEditingCheck();
            $form->tools(function (Form\Tools $tools) {
                $tools->disableView();
            });
        });
    }

    public function viewSMTPConfig()
    {
        $configs = Config::where('code', 'smtp')->orderBy('sort', 'desc')->get();
        if ($configs === null) {
            return trans('language.no_data');
        }
        $fields = [];
        foreach ($configs as $key => $field) {
            $data['title']    = $field->detail;
            $data['field']    = $field->key;
            $data['key']      = $field->key;
            $data['value']    = $field->value;
            $data['disabled'] = 0;
            $data['required'] = 0;
            $data['type']     = 'text';
            $data['source']   = '';
            if ($field->key == 'smtp_mode') {
                $data['type']   = 'select';
                $data['source'] = json_encode(
                    array(
                        ['value' => '0', 'text' => 'Not use'],
                        ['value' => '1', 'text' => 'SMTP'],
                    )
                );
            } elseif ($field->key == 'smtp_port') {
                $data['type'] = 'number';
            } elseif ($field->key == 'smtp_security') {
                $data['type']   = 'select';
                $data['source'] = json_encode(
                    array(
                        ['value' => 'tls', 'text' => 'TLS'],
                        ['value' => 'ssl', 'text' => 'SSL'],
                    )
                );
            }
            $data['url'] = route('updateConfigField');
            $fields[]    = $data;
        }
        return view('admin.CustomEdit')->with([
            "datas" => $fields,
        ])->render();
    }

}
