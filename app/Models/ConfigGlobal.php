<?php
#app/Models/ConfigGlobal.php
namespace App\Models;

use App\Models\Language;
use Illuminate\Database\Eloquent\Model;

class ConfigGlobal extends Model
{
    public $timestamps  = false;
    public $table       = 'config_global';
    protected $fillable = ['locale', 'template'];
    protected $appends  = [
        'title',
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
    public function descriptions()
    {
        return $this->hasMany(ConfigGlobalDescription::class, 'config_id', 'id');
    }
    //Fields language
    public function getTitle()
    {
        return $this->processDescriptions()['title'] ?? '';
    }
    public function getDescription()
    {
        return $this->processDescriptions()['description'] ?? '';
    }
    public function getKeyword()
    {
        return $this->processDescriptions()['keyword'] ?? '';
    }
    //Get Attributes
    public function getTitleAttribute()
    {
        return $this->getTitle();
    }
    public function getDescriptionAttribute()
    {
        return $this->getDescription();
    }
    public function getKeywordAttribute()
    {
        return $this->getKeyword();
    }

    public function processDescriptions()
    {
        return $this->descriptions->keyBy('lang_id')[$this->lang_id];
    }
}
