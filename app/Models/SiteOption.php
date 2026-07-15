<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SiteOption extends Model
{
    protected $table = 'site_options';

    protected $fillable = ['key', 'value'];
}
