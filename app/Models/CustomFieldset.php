<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Gate;
use Watson\Validating\ValidatingTrait;

class CustomFieldset extends Model
{
    use ValidatingTrait;

    protected $guarded=["id"];

    /**
     * Validation rules
     * @var array
     */
    protected $rules = [
        "name" => "required|unique:custom_fieldsets"
    ];

    /**
     * Whether the model should inject it's identifier to the unique
     * validation rules before attempting validation. If this property
     * is not set in the model it will default to true.
     *
     * @var boolean
     */
    protected $injectUniqueIdentifier = true;


    /**
     * Establishes the fieldset -> field relationship
     *
     * @author [Brady Wetherington] [<uberbrady@gmail.com>]
     * @since [v3.0]
     * @return \Illuminate\Database\Eloquent\Relations\Relation
     */
    public function fields()
    {
        return $this->belongsToMany('\App\Models\CustomField')->withPivot(["required","order"])->orderBy("pivot_order");
    }

    /**
     * Establishes the fieldset -> models relationship
     *
     * @author [Brady Wetherington] [<uberbrady@gmail.com>]
     * @since [v3.0]
     * @return \Illuminate\Database\Eloquent\Relations\Relation
     */
    public function models()
    {
        return $this->hasMany('\App\Models\AssetModel', "fieldset_id");
    }

    /**
     * Establishes the fieldset -> admin user relationship
     *
     * @author [Brady Wetherington] [<uberbrady@gmail.com>]
     * @since [v3.0]
     * @return \Illuminate\Database\Eloquent\Relations\Relation
     */
    public function user()
    {
        return $this->belongsTo('\App\Models\User'); //WARNING - not all CustomFieldsets have a User!!
    }

    /**
     * Determine the validation rules we should apply based on the
     * custom field format
     *
     * @author [A. Gianotto] [<snipe@snipe.net>]
     * @since [v3.0]
     * @return array
     */
    public function validation_rules()
    {
        $rules=[];
        foreach ($this->fields as $field) {
            $rule = [];

            if (($field->field_encrypted!='1') ||
                  (($field->field_encrypted =='1')  && (Gate::allows('admin')) )) {
                    $rule[] = ($field->pivot->required=='1') ? "required" : "nullable";
            }

            array_push($rule, $field->attributes['format']);
            $rules[$field->db_column_name()]=$rule;
        }
        return $rules;
    }
}
