<?php
namespace SavSchema;

class SchemaError extends \Exception
{
    private static $errors = array(
        "type" => 'Value [{value}] is not of [{type}] type',
        "require" => 'Field [{field}] not found',
        "empty" => 'Field [{field}] can not be empty',
        "check" => 'Field [{field}] can not matched [{rule}] rule',
        "rule" => 'Rule [{rule}] not found',
        "eql" => 'Fields [{field}] and [{field_eql}] not equal',
        "regexp" => 'Can not parse RegExp [{regexp}]'
    );

    public static function setErrors($errs)
    {
        foreach ($errs as $key => $value) {
            static::$errors[$key] = $value;
        }
    }

    static function getErrors()
    {
        return static::$errors;
    }

    public function __construct($type, $values = [], $message = null)
    {
        if (!isset($message)) {
            $message = static::$errors[$type];
        }
        switch ($type) {
            case "type":
                list($type, $value) = $values;
                $val = strval($value);
                $message = str_replace('{value}', $val, str_replace('{type}', $type, $message));
                $this->type = $type;
                $this->value = $value;
                break;
            case "require":
            case "empty":
                list($field) = $values;
                $message = str_replace('{field}', $field, $message);
                $this->field = $field;
                break;
            case "check":
                list($field, $rule) = $values;
                $message = str_replace('{rule}', $rule, str_replace('{field}', $field, $message));
                $this->field = $field;
                $this->rule = $rule;
                break;
            case "eql":
                list($field, $fieldEql) = $values;
                $message = str_replace('{field_eql}', $fieldEql, str_replace('{field}', $field, $message));
                $this->field = $field;
                $this->fieldEql = $fieldEql;
                break;
            case "rule":
                list($rule) = $values;
                $message = str_replace('{rule}', $rule, $message);
                $this->rule = $rule;
                break;
            case "regexp":
                list($regexp) = $values;
                $message = str_replace('{regexp}', $regexp, $message);
                $this->regexp = $regexp;
                break;
            default:
                break;
        }
        parent::__construct($message);
    }
    public function getMsg()
    {
        if (isset($this->msg)) {
            return $this->msg;
        }
        return $this->message;
    }
    public function setMsg($msg)
    {
        $this->$msg = $msg;
    }
}
