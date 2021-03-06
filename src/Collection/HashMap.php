<?php


namespace Javareact\Xinge\Collection;

use Javareact\Xinge\Exceptions\InvalidArgumentException;
use Javareact\Xinge\Exceptions\OutOfBoundsException;

class HashMap implements MapInterface
{
    private const MSG_UNDEFINED_OFFSET = 'Offset %s does not exist!';

    /** @var array */
    private $keys = [];

    /** @var array */
    private $values = [];

    /**
     * @inheritdoc
     */
    public function clear(): void
    {
        $this->keys = $this->values = [];
    }

    /**
     * @inheritdoc
     */
    public function containsKey($key): bool
    {
        return isset($this->keys[self::stringifyKey($key)]);
    }

    /**
     * @inheritdoc
     */
    public function containsValue($value): bool
    {
        return in_array($value, $this->values, true);
    }

    /**
     * Returns a new map containing key-value mapping from specified array
     *
     * @param array $array
     * @return static
     */
    public static function createFromArray(array $array): self
    {
        $hashMap = new self;

        foreach ($array as $key => $value) {
            $hashMap->put($key, $value);
        }

        return $hashMap;
    }

    /**
     * @inheritdoc
     */
    public function count(): int
    {
        return $this->size();
    }

    /**
     * @inheritdoc
     */
    public function current()
    {
        $key = key($this->keys);

        if (array_key_exists($key, $this->values)) {
            return $this->values[$key];
        }

        return false;
    }

    /**
     * @inheritdoc
     * @throws OutOfBoundsException
     */
    public function get($key)
    {
        if ($this->containsKey($key) === false) {
            throw new OutOfBoundsException(sprintf(self::MSG_UNDEFINED_OFFSET, self::stringifyKey($key)));
        }

        return $this->values[self::stringifyKey($key)];
    }

    /**
     * @inheritdoc
     */
    public function isEmpty(): bool
    {
        return empty($this->keys);
    }

    /**
     * @inheritdoc
     */
    public function key()
    {
        $key = current($this->keys);

        return $key !== false ? $key : null;
    }

    /**
     * @inheritdoc
     */
    public function next()
    {
        $key = next($this->keys);

        if ($key === false) {
            return false;
        }

        return $this->values[key($this->keys)];
    }

    /**
     * @inheritdoc
     */
    public function offsetExists($offset): bool
    {
        return $this->containsKey($offset);
    }

    /**
     * @inheritdoc
     * @throws OutOfBoundsException
     */
    public function offsetGet($offset)
    {
        return $this->get($offset);
    }

    /**
     * @inheritdoc
     */
    public function offsetSet($offset, $value): void
    {
        $this->put($offset, $value);
    }

    /**
     * @inheritdoc
     */
    public function offsetUnset($offset): void
    {
        $this->remove($offset);
    }

    /**
     * @inheritdoc
     */
    public function put($key, $value): self
    {
        $index = self::stringifyKey($key);

        if (isset($this->keys[$index]) === false) {
            $this->keys[$index] = $key;
        }

        $this->values[$index] = $value;

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function putAll(MapInterface $map): self
    {
        foreach ($map as $key => $value) {
            $this->put($key, $value);
        }

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function remove($key): void
    {
        $index = self::stringifyKey($key);

        unset($this->keys[$index], $this->values[$index]);
    }

    /**
     * @inheritdoc
     */
    public function rewind(): void
    {
        reset($this->keys);
    }

    /**
     * @inheritdoc
     */
    public function size(): int
    {
        return count($this->keys);
    }

    /**
     * @inheritDoc
     * @throws InvalidArgumentException
     */
    public function slice(int $length, int $offset = 0, bool $preserveKeys = true): self
    {
        if ($preserveKeys === false) {
            throw new InvalidArgumentException('Slice is only allowed with preserving keys!');
        }

        $this->keys = array_slice($this->keys, $offset, $length, true);
        $this->values = array_slice($this->values, $offset, $length, true);

        return $this;
    }

    /**
     * @inheritDoc
     * @param boolean $keySort
     */
    public function sort(callable $callback, bool $keySort = false): self
    {
        $keys = $this->keys;
        $values = $this->values;

        if ($keySort) {
            uasort($keys, $callback);
            $values = array_replace($keys, $values);
        } else {
            uasort($values, $callback);
            $keys = array_replace($values, $keys);
        }

        $this->keys = $keys;
        $this->values = $values;

        return $this;
    }

    /**
     * Calculates string representing specified array key
     *
     * @param array $key
     * @return string
     */
    private static function stringifyArrayKey(array $key): string
    {
        ksort($key);

        foreach ($key as &$value) {
            if (is_array($value)) {
                $value = self::stringifyArrayKey($value);
            } elseif (is_object($value)) {
                $value = self::stringifyKey($value);
            }
        }

        return json_encode($key);
    }

    /**
     * Calculates string representing specified key
     *
     * @param mixed $key
     * @return string
     */
    private static function stringifyKey($key): string
    {
        if ($key === null || is_scalar($key)) {
            return (string)$key;
        }

        if (is_object($key)) {
            return spl_object_hash($key);
        }

        return static::stringifyArrayKey($key);
    }

    /**
     * @inheritdoc
     */
    public function toArray(): array
    {
        return array_combine($this->keys, $this->values);
    }

    /**
     * @inheritdoc
     */
    public function valid(): bool
    {
        return $this->key() !== null;
    }

    /**
     * @inheritdoc
     */
    public function values(): array
    {
        return array_values($this->values);
    }
}