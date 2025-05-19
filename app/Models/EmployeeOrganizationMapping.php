<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EmployeeOrganizationMapping extends Model
{
    use HasFactory;
    protected $table = "employee_organization_mapping";
    public function organization()
    {
        return $this->belongsTo(Organization::class);
    }
}
