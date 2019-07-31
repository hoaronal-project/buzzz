<?php
#app/Models/ShopCategory.php
namespace App\Models;

use App\Models\Language;
use App\Models\ShopCategoryDescription;
use App\Models\ShopProduct;
use Helper;
use Illuminate\Database\Eloquent\Model;

class ShopCategory extends Model
{
    public $timestamps = false;
    public $table      = 'shop_category';
    protected $appends = [
        'name',
        'keyword',
        'description',
    ];
    public $lang_id = 1;
    public function __construct()
    {
        parent::__construct();
        $lang          = Language::getArrayLanguages();
        $this->lang_id = $lang[app()->getLocale()];
    }
    public function products()
    {
        return $this->hasMany(ShopProduct::class, 'category_id', 'id');
    }
    public function descriptions()
    {
        return $this->hasMany(ShopCategoryDescription::class, 'shop_category_id', 'id');
    }

/**
 * Get category parent
 * @return [type]     [description]
 */
    public function getParent()
    {
        return $this->find($this->parent);

    }

/**
 * [getProductsToCategory description]
 * @param  [type]  $id        [description]
 * @param  [type]  $limit     [description]
 * @param  [type]  $opt       [description]
 * @param  [type]  $sortBy    [description]
 * @param  string  $sortOrder [description]
 * @param  integer $status    [description]
 * @return [type]             [description]
 */
    public function getProductsToCategory($id, $limit = null, $opt = null, $sortBy = null, $sortOrder = 'asc', $status = 1)
    {

        $query = (new ShopProduct);
        //product active
        if ($status) {
            $query = $query->where('status', 1);
        }
        //product of childen category
        if (empty(\Helper::configs()['show_product_of_category_children'])) {
            $query = $query->where('category_id', $id);
        } else {
            $arrCategory   = $this->getIdCategories($id);
            $arrCategory[] = $id;
            $query         = $query->whereIn('category_id', $arrCategory);
        }
        //Include products link to other category
        $query = $query->orWhereRaw('FIND_IN_SET(' . $id . ',category_other) >=1');

        //Hidden product out of stock
        if (empty(\Helper::configs()['product_display_out_of_stock'])) {
            $query = $query->where('stock', '>', 0);
        }
        //sort product
        $query = $query->sort($sortBy, $sortOrder);

        //Get all product
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
        }
        //
        else {
            return $query->limit($limit)->get();
        }

    }

    protected static function boot()
    {
        parent::boot();
        // before delete() method call this
        static::deleting(function ($category) {
            $category->descriptions()->delete();
        });
    }

/**
 * [getCategories description]
 * @param  [type] $parent    [description]
 * @param  [type] $limit     [description]
 * @param  [type] $opt       [description]
 * @param  [type] $sortBy    [description]
 * @param  string $sortOrder [description]
 * @return [type]            [description]
 */
    public function getCategories($parent, $limit = null, $opt = null, $sortBy = null, $sortOrder = 'asc')
    {
        $query = $this->where('status', 1)->where('parent', $parent);
        $query = $query->sort($sortBy, $sortOrder);
        if (!(int) $limit) {
            return $query->get();
        } else
        if ($opt == 'paginate') {
            return $query->paginate((int) $limit);
        } else
        if ($opt == 'random') {
            return $query->inRandomOrder()->limit($limit)->get();
        } else {
            return $query->limit($limit)->get();
        }

    }

/**
 * [getCategoriesAll description]
 * @param  boolean $all [description]
 * @return [object]       [description]
 */
    public function getCategoriesAll($all = true, $sortBy = null, $sortOrder = 'asc')
    {
        $lang_id = $this->lang_id;
        if ($all) {
            $listFullCategory = $this->with(['descriptions' => function ($q) use ($lang_id) {
                $q->where('lang_id', $lang_id);
            }])->sort($sortBy, $sortOrder)->get()->groupBy('parent');
        } else {
            $listFullCategory = $this->with(['descriptions' => function ($q) use ($lang_id) {
                $q->where('lang_id', $lang_id);
            }])->where('status', 1)->sort($sortBy, $sortOrder)->get()->groupBy('parent');
        }
        return $listFullCategory;
    }

/**
 * Get all ID category children of parent
 * @param  integer $parent     [description]
 * @param  [type]  &$arrayID      [description]
 * @param  [object]  $categories [description]
 * @return [array]              [description]
 */
    public function getIdCategories($parent = 0, &$arrayID = null, $categories = null)
    {
        $categories  = $categories ?? $this->getCategoriesAll();
        $arrayID     = $arrayID ?? [];
        $lisCategory = $categories[$parent] ?? [];
        if (count($lisCategory)) {
            foreach ($lisCategory as $category) {
                $arrayID[] = $category->id;
                if (!empty($categories[$category->id])) {
                    $this->getIdCategories($category->id, $arrayID, $categories);
                }
            }
        }
        return $arrayID;
    }

/**
 * Get tree category from parent
 * @param  integer $parent     [description]
 * @param  [type]  &$tree      [description]
 * @param  [type]  $categories [description]
 * @param  string  &$st        [description]
 * @return [array]              [tree]
 */
    public function getTreeCategories($parent = 0, &$tree = null, $categories = null, &$st = '')
    {
        $categories  = $categories ?? $this->getCategoriesAll();
        $tree        = $tree ?? [];
        $lisCategory = $categories[$parent];
        foreach ($lisCategory as $category) {
            $tree[$category->id] = $st . $category->getName();
            if (!empty($categories[$category->id])) {
                $st .= '--';
                $this->getTreeCategories($category->id, $tree, $categories, $st);
                $st = '';
            }
        }

        return $tree;
    }

/**
 * [getCategoriesTop description]
 * @return [type] [description]
 */
    public function getCategoriesTop()
    {
        $lang_id = $this->lang_id;
        return $this->with(['descriptions' => function ($q) use ($lang_id) {
            $q->where('lang_id', $lang_id);
        }])->where('status', 1)->where('top', 1)->get();
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

    public function getUrl()
    {
        return route('category', ['name' => Helper::strToUrl(empty($this->name) ? 'no-name' : $this->name), 'id' => $this->id]);
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

//Scort
    public function scopeSort($query, $sortBy = null, $sortOrder = 'desc')
    {
        $sortBy = $sortBy ?? 'sort';
        return $query->orderBy($sortBy, $sortOrder);
    }

    public function processDescriptions()
    {
        return $this->descriptions->keyBy('lang_id')[$this->lang_id] ?? [];
    }

}
