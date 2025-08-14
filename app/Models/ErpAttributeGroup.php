<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Symfony\Component\CssSelector\Node\FunctionNode;

class ErpAttributeGroup extends Model
{
    use HasFactory;

    public function short_name()
    {
        return $this->short_name ?: $this->name;
    }
}
