<?php

namespace Bereza\DaData\Dto;

use ArrayAccess;
use Closure;
use JsonSerializable;

/**
 * Абстрактрый класс для сущности из DaData
 *
 * Class AbstractEntityDto
 * @package Bereza\DaData\Dto
 * @property string $value - значение одной строкой (как показывается в списке подсказок)
 * @property string $unrestricted_value - значение одной строкой (полное)
 */
abstract class AbstractEntityDto implements ArrayAccess, JsonSerializable
{
    /**
     * @var array
     */
    protected $attributes = [];
    
    /**
     * AbstractEntityDto constructor.
     * @param  array  $attributes
     */
    public function __construct($attributes = [])
    {
        if (isset($attributes['data'])) {
            $attributes = array_merge($attributes, $attributes['data']);
            unset($attributes['data']);
        }
        
        foreach ($attributes as $key => $value) {
            $this->attributes[$key] = $value;
        }
    }
    
    /**
     * @param  string  $key
     * @param  mixed  $default
     * @return mixed
     */
    public function get($key, $default = null)
    {
        if (array_key_exists($key, $this->attributes)) {
            return $this->attributes[$key];
        }
        
        return $default instanceof Closure ? $default() : $default;
    }
    
    /**
     * Get the attributes from the fluent instance.
     * @return array
     */
    public function getAttributes(): array
    {
        return $this->attributes;
    }
    
    /**
     * Convert the fluent instance to an array.
     * @return array
     */
    public function toArray(): array
    {
        return $this->attributes;
    }
    
    /**
     * Convert the object into something JSON serializable.
     * @return array
     */
    public function jsonSerialize(): array
    {
        return $this->toArray();
    }
    
    /**
     * Convert the fluent instance to JSON.
     * @param  int  $options
     * @return string
     */
    public function toJson($options = 0): string
    {
        return json_encode($this->jsonSerialize(), $options);
    }
    
    /**
     * Determine if the given offset exists.
     * @param  string  $offset
     * @return bool
     */
    public function offsetExists($offset): bool
    {
        return isset($this->attributes[$offset]);
    }
    
    /**
     * Get the value for a given offset.
     * @param  string  $offset
     * @return mixed
     */
    public function offsetGet($offset)
    {
        return $this->get($offset);
    }
    
    /**
     * Set the value at the given offset.
     * @param  string  $offset
     * @param  mixed  $value
     * @return void
     */
    public function offsetSet($offset, $value)
    {
        $this->attributes[$offset] = $value;
    }
    
    /**
     * Unset the value at the given offset.
     * @param  string  $offset
     * @return void
     */
    public function offsetUnset($offset)
    {
        unset($this->attributes[$offset]);
    }
    
    /**
     * Handle dynamic calls to the fluent instance to set attributes.
     * @param  string  $method
     * @param  array  $parameters
     * @return $this
     */
    public function __call($method, $parameters): self
    {
        $this->attributes[$method] = count($parameters) > 0 ? $parameters[0] : true;
        
        return $this;
    }
    
    /**
     * Dynamically retrieve the value of an attribute.
     * @param  string  $key
     * @return mixed
     */
    public function __get($key)
    {
        return $this->get($key);
    }
    
    /**
     * Dynamically set the value of an attribute.
     * @param  string  $key
     * @param  mixed  $value
     * @return void
     */
    public function __set($key, $value)
    {
        $this->offsetSet($key, $value);
    }
    
    /**
     * Dynamically check if an attribute is set.
     * @param  string  $key
     * @return bool
     */
    public function __isset($key): bool
    {
        return $this->offsetExists($key);
    }
    
    /**
     * Dynamically unset an attribute.
     * @param  string  $key
     * @return void
     */
    public function __unset($key)
    {
        $this->offsetUnset($key);
    }
}
