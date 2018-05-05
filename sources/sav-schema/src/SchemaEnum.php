<?php
namespace SavSchema;

use SavSchema\SchemaBase as SchemaBase;

class SchemaEnum extends SchemaBase
{
    public function check($val)
    {
        $enums = $this->opts["enums"];
        $len = count($enums);
        while ($len) {
            $it = $enums[--$len];
            if ($it["value"] === $val || $it["key"] === $val) {
                return true;
            }
        }
        return false;
    }
    public function create($val = null)
    {
        if (isset($val)) {
            return $val;
        }
        return $this->opts["enums"][0]["value"];
    }
    public function parse($val)
    {
        if (is_null($val)) {
            return $val;
        }
        $enums = $this->opts["enums"];
        $len = count($enums);
        while ($len) {
            $it = $enums[--$len];
            if ($it["value"] == $val) {
                return $it["value"];
            }
            if ($it["key"] == $val) {
                return $it["key"];
            }
        }
        return $val;
    }
    public function getEnum($key, $val)
    {
        foreach ($this->opts["enums"] as $_ => $it) {
            if ($it[$key] === $val) {
                return $it;
            }
        }
    }
    public function key($val)
    {
        return $this->getEnum('value', $val)["key"];
    }
    public function value($val)
    {
        return $this->getEnum('key', $val)["value"];
    }
}
