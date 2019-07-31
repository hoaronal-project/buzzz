<?php

namespace App\Admin\Controllers;

use App\Http\Controllers\Controller;
use App\Models\EmailTemplate;
use Encore\Admin\Controllers\HasResourceActions;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Layout\Content;
use Encore\Admin\Show;

class EmailTemplateController extends Controller
{
    use HasResourceActions;

    /**
     * Index interface.
     *
     * @param Content $content
     * @return Content
     */
    public $arrayGroup = [];
    public function __construct()
    {
        $this->arrayGroup = [
            'order_success_to_admin'    => trans('language.admin.email_action.order_success_to_admin'),
            'order_success_to_customer' => trans('language.admin.email_action.order_success_to_cutomer'),
            'forgot_password'           => trans('language.admin.email_action.forgot_password'),
            'welcome_customer'          => trans('language.admin.email_action.welcome_customer'),
            'contact_to_admin'          => trans('language.admin.email_action.contact_to_admin'),
            'other'                     => trans('language.admin.email_action.other'),
        ];
    }
    public function index(Content $content)
    {
        return $content
            ->header('Index')
            ->description('description')
            ->body($this->grid());
    }

    /**
     * Show interface.
     *
     * @param mixed $id
     * @param Content $content
     * @return Content
     */
    public function show($id, Content $content)
    {
        return $content
            ->header('Detail')
            ->description('description')
            ->body($this->detail($id));
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
            ->header('Edit')
            ->description('description')
            ->body($this->form()->edit($id));
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
            ->header('Create')
            ->description('description')
            ->body($this->form());
    }

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $arrayGroup = $this->arrayGroup;
        $grid       = new Grid(new EmailTemplate);

        $grid->id('Id');
        $grid->name('Name');
        $grid->group('Group')->display(function ($group) use ($arrayGroup) {
            return $arrayGroup[$group];
        });
        // $grid->text('Text')->display(function ($text) {
        //     return '<div style="max-width:250px; max-height:150px;overflow:auto;">' . htmlentities($text) . '</div>';
        // });
        $grid->status('Status')->switch();

        return $grid;
    }

    /**
     * Make a show builder.
     *
     * @param mixed $id
     * @return Show
     */
    protected function detail($id)
    {
        $show = new Show(EmailTemplate::findOrFail($id));

        $show->id('Id');
        $show->name('Name');
        $show->group('Group');
        // $show->text('Text');
        $show->status('Status');

        return $show;
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form()
    {
        $form = new Form(new EmailTemplate);

        $form->text('name', 'Name');
        $form->select('group', 'Group')->options($this->arrayGroup);
        $form->textarea('text', 'Text')->rows(20);
        $form->switch('status', 'Status');

        return $form;
    }
}
