<?php

namespace SavSchema;

use SavSchema\SchemaBase;

class SchemaField extends SchemaBase
{
    public function create($val = null)
    {
        $ret = $this->ref->create($val);
        return $ret;
    }
    public function validate(&$obj, $opts)
    {
        $ref = $this->ref;
        $name = $this->name;
        $nullable = $this->nullable;
        $empty = $this->empty;
        $eql = $this->eql;
        $optional = $this->optional;
        $message = $this->message;
        $checks = $this->checks;
        if ($optional && !isset($obj[$name])) {
            return;
        }
        if ($nullable && is_null($obj[$name])) {
            return;
        }
        try {
            if (!(isset($obj[$name]))) {
                throw new SchemaError("require", [$name]);
            }
            $val = $obj[$name];
            if (!$empty && !is_null($val)) {
                if ($val === '') {
                    throw new SchemaError("empty", [$name]);
                }
            }
            if ($eql) {
                $eqlVal = $obj[$eql];
                if ($eqlVal !== $val) {
                    throw new SchemaError("eql", [$name, $eql]);
                }
            }
            if ($checks && count($checks)) {
                $rule = $this->schema->applyChecks($val, $checks);
                if ($rule) {
                    throw new SchemaError("check", [$name, $rule[0]]);
                }
            }
            $val = checkFieldValue($val, $opts, $ref);
            return $val;
        } catch (\Exception $err) {
            if ($message) {
                if (is_a("SavSchema\\SchemaError")) {
                    $err->setMsg($message);
                }
            }
            throw $err;
        }
    }
}

function checkFieldValue($val, $opts, $ref)
{
    if (is_callable($ref->parse)) {
        $val = $ref->parse($val);
    }
    if (is_callable($ref->validate)) {
        return $ref->validate($val, $opts);
    }
    if (!$ref->check($val)) {
        throw new SchemaError("type", [$ref->name, $val]);
    }
    return $val;
}
