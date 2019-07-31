<?php
#app/Http/Admin/Controllers/Extensions/Total/DiscountController.php
namespace App\Admin\Controllers\Extensions\Total;

use App\Extensions\Total\Models\Discount as DiscountModel;
use App\Http\Controllers\Controller;
use Encore\Admin\Controllers\HasResourceActions;
use Encore\Admin\Facades\Admin;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Layout\Content;
use Encore\Admin\Show;

class DiscountController extends Controller
{
    use HasResourceActions;

    /**
     * Index interface.
     *
     * @return Content
     */
    public $arrType = [
        '0' => 'Cash',
        '1' => 'Point',
        '2' => '%',
    ];
    public function index(Content $content)
    {
        return $content
            ->header(trans('language.admin.promotion_manager'))
            ->description(' ')
            ->body($this->grid());
    }

    /**
     * Edit interface.
     *
     * @param $id
     * @return Content
     */
    public function edit($id)
    {
        return Admin::content(function (Content $content) use ($id) {

            $content->header(trans('language.admin.promotion_manager'));
            $content->description(' ');

            $content->body($this->form()->edit($id));
        });
    }

    /**
     * Create interface.
     *
     * @return Content
     */
    public function create()
    {
        return Admin::content(function (Content $content) {

            $content->header(trans('language.admin.promotion_manager'));
            $content->description(' ');

            $content->body($this->form());
        });
    }

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new DiscountModel);
        $grid->id('ID')->sortable();
        $grid->code(trans('promotion.code'));
        $grid->reward(trans('promotion.value'))->display(function ($reward) {
            return number_format($reward);
        });
        $arrType = $this->arrType;
        $grid->type(trans('promotion.type'))->display(function ($type) use ($arrType) {
            if ($type == 0) {
                return "<span class='label label-success'>$arrType[$type]</span>";
            } elseif ($type == 1) {
                return "<span class='label label-warning'>$arrType[$type]</span>";
            } elseif ($type == 2) {
                return "<span class='label label-info'>$arrType[$type]</span>";
            }
        });
        $grid->data(trans('promotion.description'));
        $grid->number_uses(trans('promotion.maximum'));
        $grid->used(trans('promotion.used'));
        $grid->users(trans('promotion.history'))->expand(function () {
            $html      = '';
            $dataPromo = DiscountModel::find($this->id);
            if ($dataPromo) {
                $html = '<br>';
                foreach ($dataPromo->users as $key => $value) {
                    $html .= '<span style="padding-left:20px;"><i class="fa fa-angle-double-right"></i> ' . trans('promotion.customer') . ' ID' . $value->pivot->user_id . trans('promotion.used_at') . $value->pivot->used_at . '.  ' . trans('promotion.content') . ': ' . $value->pivot->log . '</span><br>';
                }
            }
            return $html . "<br>";
        }, trans('promotion.history'));
        $grid->login(trans('promotion.login'))->switch();
        $grid->status(trans('promotion.status'))->switch();
        $grid->expires_at(trans('promotion.expire'));
        $grid->disableExport();
        $grid->actions(function ($actions) {
            $actions->disableView();
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
        $form = new Form(new DiscountModel);
        $form->text('code', trans('promotion.code'))->rules(function ($form) {
            return 'required|regex:/(^([0-9A-Za-z\-]+)$)/|unique:shop_discount,code,' . $form->model()->id . ',id';
        }, ['unique' => trans('promotion.exist'), 'regex' => trans('promotion.validate')])->placeholder(trans('promotion.example') . ' SAVEOFF2018,SAVE50,...')->help(trans('promotion.validate'));

        $form->number('reward', trans('promotion.value'))->rules('numeric|min:0');
        $form->select('type', trans('promotion.type'))->options($this->arrType)->help(trans('promotion.note'));
        $form->text('data', trans('promotion.description'));
        $form->number('number_uses', trans('promotion.maximum'))->default(1)->rules('numeric|min:0');
        $form->datetime('expires_at', trans('promotion.expire'));
        $form->switch('login', trans('promotion.login'));
        $form->switch('status', trans('promotion.status'));
        $form->disableViewCheck();
        $form->disableEditingCheck();
        $form->tools(function (Form\Tools $tools) {
            $tools->disableView();
        });
        return $form;
    }

    public function show($id)
    {
        return Admin::content(function (Content $content) use ($id) {
            $content->header('');
            $content->description('');
            $content->body(Admin::show(DiscountModel::findOrFail($id), function (Show $show) {
                $show->id('ID');
            }));
        });
    }
}
