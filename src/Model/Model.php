<?php
namespace PPP\Model;

use PDO;

class Model
{
    const TABLE = 'model';

    /** @var PDO $pdo */
    protected static $pdo;

    /** @var int $id */
    public $id;

    /**
     * @param static $instance
     * @throws \InvalidArgumentException
     * @return void
     */
    protected static function validateInstance($instance)
    {
        if (get_class($instance) !== static::class) {
            throw new \InvalidArgumentException(sprintf('%s only accepts an instance of itself.', static::class));
        }
    }

    /**
     * @return array
     */
    public function getValues()
    {
        return get_object_vars($this);
    }

    /**
     * @return array
     */
    public function toArray()
    {
        return $this->getValues();
    }

    /**
     * @param static $instance
     * @param array $values
     * @return void
     */
    protected static function deflate($instance, array &$values)
    {
        static::validateInstance($instance);
        $values['id'] = $instance->id;
    }

    /**
     * @param static $instance
     * @param array $values
     * @return void
     */
    protected static function inflate(&$instance, array $values)
    {
        static::validateInstance($instance);
        $instance->id = intval($values['id']);
    }

    /**
     * @param array $results
     * @return null|static
     */
    protected static function toInstance(array $results)
    {
        if (!empty($results)) {
            $instance = new static();
            static::inflate($instance, $results[0]);
            return $instance;
        } else {
            return null;
        }
    }

    /**
     * @param array $results
     * @return array
     */
    protected static function toInstanceArray(array $results)
    {
        $instances = [];
        foreach ($results as $result) {
            $instance = new static();
            static::inflate($instance, $result);
            $instances[] = $instance;
        }
        return $instances;
    }

    /**
     * @param PDO $database
     * @return void
     */
    public static function connect(PDO $database)
    {
        static::$pdo = $database;
    }

    /**
     * @return void
     */
    public static function disconnect()
    {
        static::$pdo = null;
    }

    /**
     * @param string $sql
     * @param array $params
     * @return array
     * @throws \LogicException
     */
    protected static function query($sql, array $params = [])
    {
        if (static::$pdo instanceof PDO) {
            static::$pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
            static::$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $statement = static::$pdo->prepare($sql);
            $statement->execute(array_values($params));
            $results = $statement->fetchAll();
            return $results;
        } else {
            throw new \LogicException(sprintf('A valid PDO instance must be given to %s or %s by using connect() method before querying.', static::class, __CLASS__));
        }
    }

    /**
     * @param int $id
     * @return static|null
     */
    public static function getById($id)
    {
        $sql = sprintf('SELECT * FROM %s WHERE id = ?;', static::TABLE);
        return static::toInstance(static::query($sql, [intval($id)]));
    }

    /**
     * @return array
     */
    public static function getAll()
    {
        $sql = sprintf('SELECT * FROM %s;', static::TABLE);
        return static::toInstanceArray(static::query($sql));
    }

    /**
     * @param int|null $id
     * @return static|array|null
     */
    public static function get($id = null)
    {
        return isset($id) ? static::getById($id) : static::getAll();
    }

    /**
     * @param int|null $count
     * @return array|static|null
     */
    public static function getLatest($count = 1)
    {
        $count = intval($count) ?: 1;
        $sql = sprintf('SELECT * FROM %s ORDER BY id DESC LIMIT %d;', static::TABLE, $count);
        $results = static::query($sql);
        if ($count === 1) {
            return static::toInstance($results);
        } else {
            return static::toInstanceArray($results);
        }
    }

    /**
     * @param static $instance
     * @return bool
     */
    public static function has($instance)
    {
        return static::getById($instance->id) instanceof static;
    }

    /**
     * @param static $instance
     * @return static
     */
    public static function add($instance)
    {
        static::validateInstance($instance);
        $instance->id = null;
        $params = [];
        static::deflate($instance, $params);
        $sql = sprintf('INSERT INTO %s (%s) VALUES (%s);',
            static::TABLE,
            implode(', ', array_keys($params)),
            implode(', ', array_pad([], count($params), '?'))
        );
        static::query($sql, $params);
        return static::getLatest();
    }

    /**
     * @param static $instance
     * @return static
     * @throws \LogicException
     */
    public static function update($instance)
    {
        static::validateInstance($instance);
        if (!static::has($instance)) {
            throw new \LogicException('An unsaved instance cannot be updated.');
        }
        $params = [];
        static::deflate($instance, $params);
        unset($params['id']);
        $sql = sprintf('UPDATE %s SET %s WHERE id = ?;',
            static::TABLE,
            implode(' = ?, ', array_keys($params)) . ' = ?'
        );
        static::query($sql, array_merge($params, [$instance->id]));
        return static::get($instance->id);
    }

    /**
     * @param static $instance
     * @return static
     */
    public static function save($instance)
    {
        if (static::has($instance)) {
            return static::update($instance);
        } else {
            return static::add($instance);
        }
    }

    /**
     * @param static $instance
     * @return void
     * @throws \LogicException
     */
    public static function delete($instance)
    {
        static::validateInstance($instance);
        if (!static::has($instance)) {
            throw new \LogicException('An unsaved instance cannot be deleted.');
        }
        $sql = sprintf('DELETE FROM %s WHERE id = ?;',static::TABLE);
        static::query($sql, [$instance->id]);
    }

    /**
     * @return bool
     */
    public static function beginTransaction()
    {
        return static::$pdo->beginTransaction();
    }

    /**
     * @return bool
     */
    public static function commit()
    {
        return static::$pdo->commit();
    }

    /**
     * @return bool
     */
    public static function rollBack()
    {
        return static::$pdo->rollBack();
    }
}