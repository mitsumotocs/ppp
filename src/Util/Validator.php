<?php
namespace PPP\Util;

class Validator
{
    protected $rules = [];
    protected $results = [];

    /**
     * @param string $key
     * @param string $label
     * @param array ...$rules
     * @return $this
     */
    public function setRule($key, $label, ...$rules)
    {
        $this->rules[] = [
            'key' => $key,
            'label' => $label,
            'functions' => $rules
        ];
        return $this;
    }

    /**
     * @param array $data
     * @return $this
     * @throws \LogicException
     */
    protected function run(array $data)
    {
        foreach ($this->rules as $rule) {
            if (array_key_exists($rule['key'], $data)) {
                foreach ($rule['functions'] as $function) {
                    if (is_callable($function)) {
                        $result = call_user_func($function, $data[$rule['key']]);
                        $this->results[$rule['key']][] = ($result === true) ?: sprintf($result, $rule['label']);
                    } else {
                        if (method_exists($this, $function)) {
                            $result = forward_static_call('static::' . $function, $data[$rule['key']]);
                            $this->results[$rule['key']][] = ($result === true) ?: sprintf($result, $rule['label']);
                        } else {
                            throw new \LogicException(sprintf('Validation function "%s" is not implemented in %s', $function, get_class($this)));
                        }
                    }
                }
            }
        }
        return $this;
    }

    /**
     * @return array
     */
    public function getErrors()
    {
        $errors = [];
        foreach ($this->results as $key => $results) {
            foreach ($results as $result) {
                if ($result !== true) {
                    $errors[$key] = $result;
                    break;
                }
            }
        }
        return $errors;
    }

    /**
     * @param array $data
     * @param array $errors
     * @return bool
     */
    public function validate(array $data, &$errors = [])
    {
        $errors = $this->run($data)->getErrors();
        return empty($errors);
    }

    /**
     * @param string $value
     * @return bool|string
     */
    protected static function required($value)
    {
        return empty($value) ? '%s is required.' : true;
    }

    /**
     * @param string $value
     * @return bool|string
     */
    protected static function email($value)
    {
        return (filter_var($value, FILTER_VALIDATE_EMAIL) === false) ? '%s is invalid.' : true;
    }

    /**
     * @param string $value
     * @return bool|string
     */
    protected static function url($value)
    {
        return (filter_var($value, FILTER_VALIDATE_URL) === false) ? '%s is invalid.' : true;
    }
}