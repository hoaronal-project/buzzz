<?php
#app/Http/Admin/Controllers/ProcessController.php
namespace App\Admin\Controllers;

use App\Http\Controllers\Controller;
use App\Models\ShopProduct;
use App\Models\ShopProductDescription;
use App\Models\ShopSpecialPrice;
use Encore\Admin\Controllers\HasResourceActions;
use Encore\Admin\Facades\Admin;
use Encore\Admin\Layout\Content;
use Illuminate\Http\Request;
use PhpOffice\PhpSpreadsheet\IOFactory;

class ProcessController extends Controller
{
    use HasResourceActions;
    /**
     * Index interface.
     *
     * @return Content
     */
    public function index(Content $content)
    {

        return $content
            ->header('Index')
            ->description(' ')
            ->body('');
    }

    public function importProduct(Content $content, Request $request)
    {

        if ($request->getMethod() == 'POST') {
            $case = $request->get('case');
            switch ($case) {
                case 'import_file_info':
                    $reader        = IOFactory::createReader('Xls');
                    $validatedData = \Validator::make($request->all(), [
                        'import_file_info' => 'required|mimes:xlsx,xls',
                    ]);
                    if ($validatedData->fails()) {
                        return redirect()->back()->withErrors($validatedData->errors());
                    } else {

                        $spreadsheet = $reader->load($request->file('import_file_info'));
                        $sheet       = $spreadsheet->getActiveSheet();
                        $maxCol      = $sheet->getHighestColumn();
                        $maxRow      = $sheet->getHighestRow();
                        $data        = $sheet->rangeToArray('A3:' . $maxCol . $maxRow, true, true, true, true);
                        $arrError    = array();
                        $arrSuccess  = array();
                        // dd($data);
                        foreach ($data as $key => $row) {
                            $arrMapping                = array();
                            $sku                       = $row['A'];
                            $arrMapping['price']       = (int) $row['B'];
                            $arrMapping['cost']        = (int) $row['E'];
                            $arrMapping['stock']       = (int) $row['G'];
                            $arrMapping['category_id'] = (int) $row['B'];
                            $arrMapping['brand_id']    = (int) $row['C'];
                            $arrMapping['vendor_id']   = (int) $row['D'];
                            try {
                                (new ShopProduct)
                                    ->updateOrInsert(
                                        ['sku' => $sku],
                                        $arrMapping
                                    );
                                $arrSuccess[] = $sku;
                            } catch (\Exception $e) {
                                $arrError[] = $sku . ': ' . $e->getMessage();
                            }
                        }
                        if ($arrSuccess) {
                            $request->session()->flash('import_success', $arrSuccess);
                        }
                        if ($arrError) {
                            $request->session()->flash('import_error', $arrError);
                        }
                        return back();

                    }

                    break;

                case 'import_file_description':
                    $reader        = IOFactory::createReader('Xls');
                    $validatedData = \Validator::make($request->all(), [
                        'import_file_description' => 'required|mimes:xlsx,xls',
                    ]);
                    if ($validatedData->fails()) {
                        return redirect()->back()->withErrors($validatedData->errors());
                    } else {

                        $spreadsheet = $reader->load($request->file('import_file_description'));
                        $sheet       = $spreadsheet->getActiveSheet();
                        $maxCol      = $sheet->getHighestColumn();
                        $maxRow      = $sheet->getHighestRow();
                        $data        = $sheet->rangeToArray('A3:' . $maxCol . $maxRow, true, true, true, true);
                        $arrError    = array();
                        $arrSuccess  = array();
                        foreach ($data as $key => $row) {
                            $sku          = $row['A'];
                            $checkProduct = ShopProduct::where('sku', $sku)->first();
                            if (!$checkProduct) {
                                $arrError[] = $sku . ': already not exist!';
                            } else {
                                try {
                                    $arrUnique = ['product_id' => $checkProduct->id, 'lang_id' => (int) $row['B']];
                                    $fields    = [
                                        'name'        => $row['C'],
                                        'description' => $row['D'],
                                        'keyword'     => $row['E'],
                                        'content'     => $row['F'],
                                    ];
                                    (new ShopProductDescription)
                                        ->updateOrInsert(
                                            $arrUnique,
                                            $fields
                                        );
                                    $arrSuccess[] = $sku;
                                } catch (\Exception $e) {
                                    // $arrError[] = $sku . ': have error ' . $e->getMessage();
                                    $arrError[] = $sku . ': ' . $e->getMessage();
                                }
                            }

                        }
                        if ($arrSuccess) {
                            $request->session()->flash('import_success', $arrSuccess);
                        }
                        if ($arrError) {
                            $request->session()->flash('import_error', $arrError);
                        }
                        return back();

                    }
                    break;

                case 'import_file_special_price':
                    $reader        = IOFactory::createReader('Xls');
                    $validatedData = \Validator::make($request->all(), [
                        'import_file_special_price' => 'required|mimes:xlsx,xls',
                    ]);
                    if ($validatedData->fails()) {
                        return redirect()->back()->withErrors($validatedData->errors());
                    } else {

                        $spreadsheet = $reader->load($request->file('import_file_special_price'));
                        $sheet       = $spreadsheet->getActiveSheet();
                        $maxCol      = $sheet->getHighestColumn();
                        $maxRow      = $sheet->getHighestRow();
                        $data        = $sheet->rangeToArray('A3:' . $maxCol . $maxRow, true, true, true, true);
                        $arrError    = array();
                        $arrSuccess  = array();
                        foreach ($data as $key => $row) {
                            $sku          = $row['A'];
                            $checkProduct = ShopProduct::where('sku', $sku)->first();
                            if (!$checkProduct) {
                                $arrError[] = $sku . ': already not exist!';
                            } else {
                                try {
                                    $arrUnique = ['product_id' => $checkProduct->id];
                                    $fields    = [
                                        'price'   => (int) $row['B'],
                                        'status'  => (int) $row['C'],
                                        'comment' => (int) $row['D'],
                                    ];
                                    (new ShopSpecialPrice)
                                        ->updateOrInsert(
                                            $arrUnique,
                                            $fields
                                        );
                                    $arrSuccess[] = $sku;
                                } catch (\Exception $e) {
                                    $arrError[] = $sku . ': ' . $e->getMessage();
                                }
                            }

                        }
                        if ($arrSuccess) {
                            $request->session()->flash('import_success', $arrSuccess);
                        }
                        if ($arrError) {
                            $request->session()->flash('import_error', $arrError);
                        }
                        return back();

                    }
                    break;

                default:
                    # code...
                    break;
            }

        }

        return $content
            ->header(trans('language.process.productImport'))
            ->description(' ')
            ->body($this->getImportProduct());
    }

    public function getImportProduct()
    {
        return view('admin.importProduct')->render();

    }

}
