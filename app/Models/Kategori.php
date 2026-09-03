<?php

namespace App\Models;

use Sakuci\Database\Model;

class Kategori extends Model
{
    protected static ?string $table = 'kategori';

    protected array $fillable = ['keterangan'];
}
