<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TemplateVariable extends Model
{
    use HasFactory;

    protected $table ="templates_variables";

    protected $fillable = [
        'variable_name', 
        'description', 
        'example_value'
    ];

    public static function getVaribles(){

        return self::get()->pluck('variable_name', 'description')->toArray();
    }

    /**
     * Get variables for dropdown, ordered alphabetically by description (label).
     * Returns [ variable_name => description ] for use in Select variable dropdowns.
     */
    public static function getVariablesForDropdown(): array
    {
        return self::query()
            ->orderBy('description')
            ->pluck('description', 'variable_name')
            ->toArray();
    }

    public static function viewVariable($value){
        return self::where(['variable_name'=>$value])->first()?->variable_name;
    }
}
