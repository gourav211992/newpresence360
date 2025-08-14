<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class IssueType extends Model
{
    protected $table = 'erp_issue_types';

    use HasFactory;

    protected $fillable = ['name', 'status','group_id', 'company_id', 'organization_id'];



}
