<?php
#app/Models/ShopProduct.php
namespace App\Models;

use App\Models\Language;
use App\Models\ShopAttributeGroup;
use App\Models\ShopProductDescription;
use App\Models\ShopProductOption;
use App\Models\ShopSpecialPrice;
use Illuminate\Database\Eloquent\Model;

class ShopProduct extends Model
{
    public $table      = 'shop_product';
    protected $guarded = [];
    protected $appends = [
        'name',
        'keyword',
        'description',
        'content',
    ];
    public $lang_id = 1;
    public function __construct()
    {
        parent::__construct();
        $lang          = Language::getArrayLanguages();
        $this->lang_id = $lang[app()->getLocale()];
    }
    public function brand()
    {
        return $this->belongsTo(ShopBrand::class, 'brand_id', 'id');
    }
    public function vendor()
    {
        return $this->belongsTo(ShopVendor::class, 'vendor_id', 'id');
    }
    public function category()
    {
        return $this->belongsTo(ShopCategory::class, 'category_id', 'id');
    }

    public function images()
    {
        return $this->hasMany(ShopProductImage::class, 'product_id', 'id');
    }
    public function likes()
    {
        return $this->hasMany(ShopProductLike::class, 'product_id', 'id');
    }
    public function descriptions()
    {
        return $this->hasMany(ShopProductDescription::class, 'product_id', 'id');
    }

    public function options()
    {
        return $this->hasMany(ShopProductOption::class, 'product_id', 'id');
    }

    public function specialPrice()
    {
        return $this->hasMany(ShopSpecialPrice::class, 'product_id', 'id');
    }
    public function attDetails()
    {
        return $this->hasMany(ShopAttributeDetail::class, 'product_id', 'id');
    }

/**
 * [getPrice description]
 * @param  [type] $opt_sku [description]
 * @return [type]          [description]
 */
    public function getPrice($opt_sku = null)
    {
        $id = $this->id;
//Process product type
        /*
        if product have type, will use price of type
         */
        if ($opt_sku) {
            return ShopProductOption::where('product_id', $id)->where('opt_sku', $opt_sku)->first()->opt_price;
        }
//End type
        $special = $this->processSpecialPrice();
        if ($special) {
            return $special;
        } else {
            return $this->price;
        }
    }

/**
 * [showPrice description]
 * @param  [type] $classNew [description]
 * @param  [type] $classOld [description]
 * @param  [type] $divWrap  [description]
 * @return [type]           [description]
 */
    public function showPrice($classNew = null, $classOld = null, $divWrap = null)
    {

        if ($this->price == $this->getPrice()) {
            $str = '<span class="' . (($classNew) ? $classNew : 'new-price') . '">' . \Helper::currencyRender($this->price) . '</span>';
            if ($divWrap != null) {
                $str = '<div class="' . $divWrap . '">' . $str . '</div>';
            }
            return $str;
        } else {
            $str = '<span class="' . (($classNew) ? $classNew : 'new-price') . '">' . \Helper::currencyRender($this->getPrice()) . '</span><span class="' . (($classNew) ? $classOld : 'old-price') . '">' . \Helper::currencyRender($this->price) . '</span>';
            if ($divWrap != null) {
                $str = '<div class="' . $divWrap . '">' . $str . '</div>';
            }
            return $str;
        }
    }

/**
 * [getProducts description]
 * @param  [type] $type      [description]
 * @param  [type] $limit     [description]
 * @param  [type] $opt       [description]
 * @param  [type] $sortBy    [description]
 * @param  string $sortOrder [description]
 * @return [type]            [description]
 */
    public function getProducts($type = null, $limit = null, $opt = null, $sortBy = null, $sortOrder = 'desc')
    {
        $lang_id = $this->lang_id;
        $query   = ShopProduct::where('status', 1)->with(['descriptions' => function ($q) use ($lang_id) {
            $q->where('lang_id', $lang_id);
        }]);
        if ($type) {
            $query = $query->where('type', $type);
        }

        //Hidden product out of stock
        if (empty(\Helper::configs()['product_display_out_of_stock'])) {
            $query = $query->where('stock', '>', 0);
        }
        $query = $query->sort($sortBy, $sortOrder);
        //get all
        if (!(int) $limit) {
            return $query->get();
        } else
        //paginate
        if ($opt == 'paginate') {
            return $query->paginate((int) $limit);
        } else
        //random
        if ($opt == 'random') {
            return $query->inRandomOrder()->limit($limit)->get();
        } else {
            return $query->limit($limit)->get();
        }
    }

    public function getSearch($keyword, $limit = 12, $sortBy = null, $sortOrder = 'desc')
    {
        $lang_id = $this->lang_id;

        return $this->where('status', 1)->with(['descriptions' => function ($q) use ($lang_id) {
            $q->where('lang_id', $lang_id);
        }])
            ->leftJoin('shop_product_description', 'shop_product_description.product_id', 'shop_product.id')
            ->where('shop_product_description.lang_id', $this->lang_id)
            ->where(function ($sql) use ($keyword) {
                $sql->where('shop_product_description.name', 'like', '%' . $keyword . '%')
                    ->orWhere('shop_product.sku', 'like', '%' . $keyword . '%');
            })
            ->sort($sortBy, $sortOrder)
            ->paginate($limit);
    }

    public function getProductsSpecial($limit = null, $random = true)
    {

        $special = (new ShopSpecialPrice)
            ->join($this->table, $this->table . '.id', '=', 'shop_special_price.product_id')
            ->where('shop_special_price.status', 1)
            ->where($this->table . '.status', 1)
            ->where(function ($query) {
                $query->where('shop_special_price.date_end', '>=', date("Y-m-d"))
                    ->orWhereNull('shop_special_price.date_end');
            })
            ->where(function ($query) {
                $query->where('shop_special_price.date_start', '<=', date("Y-m-d"))
                    ->orWhereNull('shop_special_price.date_start');
            })->with('product')
        ;
        if ($random) {
            $special = $special->inRandomOrder();
        }
        if ($limit) {
            $special = $special->limit($limit);
        }
        return $special->get();
    }

    public function addLike($pId, $uId)
    {
        $check = $this->checkCanLike($pId, $uId);
        if ($check === 2) {
            return $this->likes()->insert(['product_id' => $pId, 'users_id' => $uId]);
        } else {
            return false;
        }
    }

    public function checkCanLike($pId, $uId)
    {
        if (empty($pId) || empty($uId)) {
            return 0; // no exist
        } else {
            $check = $this->likes()->where('product_id', $pId)->where('users_id', $uId)->first();
            if ($check) {
                return 1; // liked
            } else {
                return 2; // can like
            }
        }

    }

    protected static function boot()
    {
        parent::boot();
        // before delete() method call this
        static::deleting(function ($product) {
            $product->likes()->delete();
            $product->images()->delete();
            $product->descriptions()->delete();
            $product->specialPrice()->delete();
            $product->options()->delete();
            $product->attDetails()->delete();
        });
    }

/**
 * Get value field category_order same array
 * @param  [type] $category_other [description]
 * @return [type]                 [description]
 */
    public function getCategoryOtherAttribute($category_other)
    {
        if (is_string($category_other) && $category_other) {
            return explode(',', $category_other);
        } else {
            return $category_other;
        }

    }

/**
 * Set value for field category_order
 * @param [type] $category_other [description]
 */
    public function setCategoryOtherAttribute($category_other)
    {
        if (is_array($category_other)) {
            $this->attributes['category_other'] = implode(',', $category_other);
        }

    }

/**
 * [getThumb description]
 * @return [type] [description]
 */
    public function getThumb()
    {
        if ($this->image) {

            if (!file_exists(PATH_FILE . '/thumb/' . $this->image)) {
                return $this->getImage();
            } else {
                if (!file_exists(PATH_FILE . '/thumb/' . $this->image)) {
                } else {
                    return PATH_FILE . '/thumb/' . $this->image;
                }
            }
        } else {
            return 'images/no-image.jpg';
        }

    }

/**
 * [getImage description]
 * @return [type] [description]
 */
    public function getImage()
    {
        if ($this->image) {

            if (!file_exists(PATH_FILE . '/' . $this->image)) {
                return 'images/no-image.jpg';
            } else {
                return PATH_FILE . '/' . $this->image;
            }
        } else {
            return 'images/no-image.jpg';
        }

    }
/**
 * [getUrl description]
 * @return [type] [description]
 */
    public function getUrl()
    {
        return route('product', ['name' => \Helper::strToUrl(empty($this->name) ? $this->sku : $this->name), 'id' => $this->id]);
    }

//Fields language
    public function getName()
    {
        return $this->processDescriptions()['name'] ?? '';
    }
    public function getKeyword()
    {
        return $this->processDescriptions()['keyword'] ?? '';
    }
    public function getDescription()
    {
        return $this->processDescriptions()['description'] ?? '';
    }
    public function getContent()
    {
        return $this->processDescriptions()['content'] ?? '';
    }

//Attributes
    public function getNameAttribute()
    {
        return $this->getName();
    }
    public function getKeywordAttribute()
    {
        return $this->getKeyword();

    }
    public function getDescriptionAttribute()
    {
        return $this->getDescription();

    }
    public function getContentAttribute()
    {
        return $this->getContent();

    }

/**
 * [getArrayProductName description]
 * @return [type] [description]
 */
    public static function getArrayProductName()
    {
        $products   = self::select('id', 'sku')->get();
        $arrProduct = [];
        foreach ($products as $key => $product) {
            $arrProduct[$product->id] = $product->name . ' (' . $product->sku . ')';
        }
        return $arrProduct;
    }

/**
 * [getPercentDiscount description]
 * @return [type] [description]
 */
    public function getPercentDiscount()
    {
        return round((($this->price - $this->getPrice()) / $this->price) * 100);
    }

    public function attGroupBy()
    {
        return $this->attDetails->groupBy('attribute_id');
    }

    public function renderAttDetails()
    {
        $html    = '';
        $details = $this->attGroupBy();
        $groups  = ShopAttributeGroup::pluck('name', 'id')->all();
        foreach ($details as $key => $detailsGroup) {
            $html .= '<br><b><label>' . $groups[$key] . '</label></b>: ';
            foreach ($detailsGroup as $k => $detail) {
                $html .= '<label class="radio-inline"><input ' . (($k == 0) ? "checked" : "") . ' type="radio" name="form_attr[' . $key . ']" value="' . $detail->id . '">' . $detail->name . '</label> ';
            }
        }
        return $html;
    }

//Scort
    public function scopeSort($query, $sortBy = null, $sortOrder = 'desc')
    {
        $sortBy = $sortBy ?? 'id';
        return $query->orderBy($sortBy, $sortOrder);
    }

/**
//Condition:
//Active
//In of stock or allow order out of stock
//Date availabe
 */
    public function allowSale()
    {
        if ($this->status &&
            (\Helper::configs()['product_preorder'] == 1 || $this->date_available == null || date('Y-m-d H:i:s') >= $this->date_available) &&
            (\Helper::configs()['product_buy_out_of_stock'] || $this->stock)) {
            return true;
        } else {
            return false;
        }
    }
    public function processDescriptions()
    {
        return $this->descriptions->keyBy('lang_id')[$this->lang_id] ?? [];
    }

    public function processSpecialPrice()
    {
        $specials = $this->specialPrice();
        foreach ($specials as $key => $special) {
            if (($special['date_end'] >= date("Y-m-d") || $special['date_end'] == null)
                && ($special['date_start'] <= date("Y-m-d") || $special['date_start'] == null)) {
                return $special['price'];
            }
        }
        return false;
    }

}
