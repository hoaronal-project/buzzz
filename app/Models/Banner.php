<?php
#app/Models/Banner.php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Banner extends Model
{
    public $table = 'banner';
    /**
     * [getImage description]
     * @return [type] [description]
     */
    public function getImage()
    {

        return PATH_FILE . '/' . $this->image;

    }
//Scort
    public function scopeSort($query, $column = null)
    {
        $column = $column ?? 'sort';
        return $query->orderBy($column, 'asc')->orderBy('id', 'desc');
    }

}
