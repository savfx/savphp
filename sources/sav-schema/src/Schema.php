<?php
namespace SavSchema;

require_once __DIR__."/consts.php";
require_once __DIR__."/types.php";
require_once __DIR__."/checks.php";

class Schema
{
    public function __construct($opts = array())
    {
        if (!array_key_exists('strict', $opts)) {
            $opts['strict'] = true;
        }
        $this->opts = $opts;
        $this->idMap = array();
        $this->nameMap = array();
        $this->checks = array();
        registerTypes($this);
        registerChecks($this);
    }
    public function build($data, $opts = array())
    {
        if (isArray($data)) {
            $ret = [];
            foreach ($data as $it) {
                array_push($ret, $this->build($it, $opts));
            }
            return $ret;
        } elseif (isObject($data)) {
            $ret = createSchema($this, $data, $opts);
            return $ret;
        }
    }
    public function load($data, $opts = array())
    {
        if (isset($data['fields'])) {
            createFields($this, $data['fields'], $opts);
        }
        foreach (['enums', 'lists', 'structs', 'schemas'] as $key) {
            if (isset($data[$key])) {
                $this->build($data[$key], $opts);
            }
        }
    }
    public function registerType($opts)
    {
        $ret = new SchemaType($this, $opts);
        $ret->schemaType = SAV_SCHEMA_TYPE;
        exportSchema($this, $ret);
        return $ret;
    }
    public function registerCheck($opts)
    {
        $ret = new SchemaCheck($this, $opts);
        $this->checks[$ret->name] = $ret;
        if ($ret->alias) {
            $this->checks[$ret->alias] = $ret;
        }
        return $ret;
    }
    public function applyChecks($value, $rules)
    {
        if (isArray($rules)) {
            foreach ($rules as $rule) {
                $ruller = $this->checks[$rule[0]];
                if ($ruller) {
                    if (!$ruller->check($value, $rule)) {
                        return $rule;
                    }
                } else {
                    throw new SchemaError("rule", [$rule[0]]);
                }
            }
        }
    }
    public function getRef($ret)
    {
        switch ($ret->schemaType) {
            case SAV_SCHEMA_REFER:
                return $this->{$ret->refer};
            case SAV_SCHEMA_LIST:
                return $this->{$ret->list};
            case SAV_SCHEMA_FIELD:
                return $this->{$ret->type};
        }
    }
    function __get($_property)
    {
        if (isset($this->idMap[$_property])) {
            return $this->idMap[$_property];
        }
        if (isset($this->nameMap[$_property])) {
            return $this->nameMap[$_property];
        }
    }
}

function createSchema($schema, $data, $opts)
{
    if (isset($data['refs'])) {
        $refs = $data['refs'];
        $arr = $refs;
        if (isObject($refs)) { // assoc array
            $arr = [];
            foreach ($refs as $key => $value) {
                $value[$key]["name"] = $key;
                array_push($arr, $value);
            }
        }
        $schema->declare($arr);
    }
    $ret = null;
    if (isset($data['props'])) {
        $props = $data['props'];
        if (isObject($props)) { // assoc array
            $propv = [];
            foreach ($props as $it => $val) {
                if (!isObject($val)) {
                    $val = array("type" => $val);
                }
                $val["name"] = $it;
                array_push($propv, $val);
            }
            $props = $propv;
        }
        $fields = [];
        foreach ($props as $it) {
            if (is_string($it) || is_numeric($it)) {
                $field = $schema->idMap[$it];
            } else {
                $field = new SchemaField($schema, $it);
                $field->schemaType = SAV_SCHEMA_FIELD;
            }
            array_push($fields, $field);
        }
        $ret = new SchemaStruct($schema, $data);
        $ret->schemaType = SAV_SCHEMA_STURCT;
        $ret->fields = $fields;
    } elseif (isset($data['refer'])) {
        $ret = new SchemaRefer($schema, $data);
        $ret->schemaType = SAV_SCHEMA_REFER;
    } elseif (isset($data['list'])) {
        $ret = new SchemaList($schema, $data);
        $ret->schemaType = SAV_SCHEMA_LIST;
    } elseif (isset($data['enums'])) {
        $ret = new SchemaEnum($schema, $data);
        $ret->schemaType = SAV_SCHEMA_ENUM;
    } elseif (isset($data['type'])) {
        $ret = new SchemaField($schema, $data);
        $ret->schemaType = SAV_SCHEMA_FIELD;
    }
    exportSchema($schema, $ret);
    return $ret;
}

function createFields($schema, $fields, $opts)
{
    foreach ($fields as $key => $value) {
        if (!isset($value['id'])) {
            $value['id'] = $key;
        }
        $ret = new SchemaField($schema, $value);
        $ret->schemaType = SAV_SCHEMA_FIELD;
        exportSchema($schema, $ret);
    }
}

function exportSchema($schema, $ref)
{
    if (!is_null($ref->id)) {
        $schema->idMap[$ref->id] = $ref;
    }
    if (($ref->schemaType != SAV_SCHEMA_FIELD) && $ref->name) {
        $schema->nameMap[$ref->name] = $ref;
    }
}
