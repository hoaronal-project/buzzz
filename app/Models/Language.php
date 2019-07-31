<?php
#app/Models/Language.php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Language extends Model
{
    public $table                     = 'language';
    public $timestamps                = false;
    private static $getLanguages      = null;
    private static $getArrayLanguages = null;
    public static function getLanguages()
    {
        if (self::$getLanguages !== null) {
            return self::$getLanguages;
        }
        self::$getLanguages = self::where('status', 1)->get()->keyBy('id');
        return self::$getLanguages;
    }
    public static function getArrayLanguages()
    {
        if (self::$getArrayLanguages !== null) {
            return self::$getArrayLanguages;
        }
        self::$getArrayLanguages = self::pluck('id', 'code')->all();
        return self::$getArrayLanguages;
    }

}
