<?php
#app/Models/EmailTemplate.php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EmailTemplate extends Model
{
    public $timestamps = false;
    public $table      = 'email_template';
}
