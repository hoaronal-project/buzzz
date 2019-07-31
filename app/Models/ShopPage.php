<?php
#app/Models/ShopPage.php
namespace App\Models;

use App\Models\Language;
use Illuminate\Database\Eloquent\Model;

class ShopPage extends Model
{
    public $timestamps = false;
    public $table      = 'shop_page';
    protected $appends = [
        'title',
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
    public function descriptions()
    {
        return $this->hasMany(ShopPageDescription::class, 'page_id', 'id');
    }
    //Fields language
    public function getTitle()
    {
        return $this->processDescriptions()['title'] ?? '';
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

    public function getUrl()
    {
        return route('pages', ['name' => $this->uniquekey]);
    }

//Attributes
    public function getTitleAttribute()
    {
        return $this->getTitle();
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
    public function processDescriptions()
    {
        return $this->descriptions->keyBy('lang_id')[$this->lang_id] ?? [];
    }
}
