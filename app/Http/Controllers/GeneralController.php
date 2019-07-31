<?php
#app/Http/Controller/GeneralController.php
namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Subscribe;
use Illuminate\Http\Request;
use Mail;
use View;

class GeneralController extends Controller
{
    public $configs;
    public $configsGlobal;

    public function __construct()
    {
        //=======Config====
        $configs       = \Helper::configs();
        $configsGlobal = \Helper::configsGlobal();
        //============end config====
        $this->configsGlobal = $configsGlobal;
        $this->configs       = $configs;

    }

/**
 * [emailSubscribe description]
 * @param  Request $request [description]
 * @return [type]           [description]
 */
    public function emailSubscribe(Request $request)
    {
        $data      = $request->all();
        $validator = $request->validate([
            'subscribe_email' => 'required|email',
        ], [
            'subscribe_email.required' => trans('validation.required'),
            'subscribe_email.email'    => trans('validation.email'),
        ]);

        $checkEmail = Subscribe::where('email', $data['subscribe_email'])->first();
        if (!$checkEmail) {
            Subscribe::insert(['email' => $data['subscribe_email']]);
        }
        return redirect()->back()->with(['message' => trans('language.subscribe.subscribe_success')]);
    }

    public function pageNotFound()
    {
        return view(SITE_THEME . '.notfound',
            array(
                'title'       => '404 - Page not found',
                'msg'         => trans('language.page_not_found'),
                'description' => '',
                'keyword'     => '',

            )
        );
    }
    public function itemNotFound()
    {
        return view(SITE_THEME . '.notfound',
            array(
                'title'       => '404 - Item not found',
                'msg'         => trans('language.item_not_found'),
                'description' => '',
                'keyword'     => '',

            )
        );
    }

}
