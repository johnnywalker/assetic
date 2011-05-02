<?php

namespace Assetic\Factory\Loader;

use Assetic\Factory\Resource\ResourceInterface;
use Symfony\Component\Yaml\Yaml;

class YamlFormulaLoader implements FormulaLoaderInterface
{
    private static $configKeys = array(
        array('inputs', 'filters', 'options'),
        array('options' => array('output', 'debug')),
    );
    
    private static $requiredKeys = array(
        array('inputs', 'filters', 'options'),
        array('options' => array()),
    );
    
    /**
     * Loads formulae from Yaml data
     *
     * @param ResourceInterface $resource The resource to load
     *
     * @return array Formulae
     *
     * @throws InvalidArgumentException when the YAML file specified is invalid
     */
    public function load(ResourceInterface $resource)
    {
        $yml = Yaml::load($resource->getContent());

        // empty file
        if (null === $yml)
            $yml = array();
        
        if (!is_array($yml))
            throw new \InvalidArgumentException(sprintf('The file "%s" must contain a valid YAML array.', $file));
        
        $formulae = array();
        foreach ($yml as $name => $config)
        {
            $config = $this->normalizeFormulaConfig($config);
            $formulae[$name] = array($config['inputs'], $config['filters'], $config['options']);
        }
        return $formulae;
    }

    /**
     * Normalizes a formula configuration
     *
     * @param array $config A formula configuration
     * @return array The normalized configuration
     *
     * @throws InvalidArgumentException when a configuration key or value is unsupported
     */
    protected function normalizeFormulaConfig($config)
    {
        foreach ($config as $key => &$value)
        {
            if (!in_array($key, static::$configKeys[0])) {
                throw new \InvalidArgumentException(sprintf(
                    'Assetic\'s Yaml loader does not support the key "%s". Expected keys are: %s',
                    $key,
                    implode(', ', static::$configKeys[0])
                ));
            }
            if (in_array($key, array_keys(static::$configKeys[1]))) {
                if (!is_array($value)) {
                    throw new \InvalidArgumentException(sprintf(
                                    'Unexpected type for key "%s": "%s". Expected type: array.',
                                    $key, gettype($value)));
                }
                foreach ($value as $subKey => $subValue)
                {
                    if (!in_array($subKey, static::$configKeys[1][$key])) {
                        throw new \InvalidArgumentException(sprintf(
                                        'Assetic\'s Yaml loader does not support the sub-key "%s" for key "%s". Expected sub-keys are: %s',
                                        $subKey, $key,
                                        implode(', ',
                                                static::$configKeys[1][$key])
                        ));
                    }
                    if ('options' === $key && 'debug' === $subKey) {
                        if (!is_bool($subValue)) {
                            throw new \InvalidArgumentException(sprintf(
                               'Unexpected type for sub-key "debug": "%s". Expected type: boolean.',
                               gettype($subValue)
                            ));
                        }
                    } elseif (!is_string($subValue)) {
                        throw new \InvalidArgumentException(sprintf(
                           'Unexpected type for sub-key "%s": "%s". Expected type: string.',
                           $subKey, gettype($subValue)
                        ));
                    }
                }
            } else {
                if (is_string($value)) {
                    $value = array($value);
                } elseif (is_array($value)) {
                    foreach ($value as $item) {
                        if (!is_string($item)) {
                            throw new \InvalidArgumentException(sprintf(
                                'Unexpected type for "%s"\'s array value: "%s". Expected string.',
                                $key, gettype($item)
                            ));
                        }
                    }
                } else {
                    throw new \InvalidArgumentException(sprintf(
                       'Unexpected type for key "%s": "%s". Expected types: (array, string).',
                       $key, gettype($value)
                    ));
                }
            }
        }
        unset($key);
        unset($value);
        $config = array_merge_recursive(array('filters' => array(),
            'options' => array()), $config);

        $missing = array_diff(static::$requiredKeys[0], array_keys($config));
        if (0 < count($missing)) {
            throw new \InvalidArgumentException(sprintf(
                'Required keys missing: "%s"', implode(', ', $missing)
            ));
        }
        foreach ($config as $key => $value) {
            if (in_array($key, array_keys(static::$requiredKeys[1]))) {
                $missing = array_diff(static::$requiredKeys[1][$key], array_keys($value));
                if (0 < count($missing)) {
                    throw new \InvalidArgumentException(sprintf(
                        'Required sub-keys missing: "%s"', implode(', ', $missing)
                    ));
                }
            }
        }
        
        return $config;
    }
}