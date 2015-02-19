<?php
namespace wmlib\controller;

class Session
{
    private $_id;
    /**
     * @var ISessionStorage
     */
    private $_storage;

    private $_storages = [];

    private $_started;

    public function __construct($id, ISessionStorage $storage)
    {
        $this->_storage = $storage;
        $this->_id = $id;
        $this->_started = new \SplObjectStorage();
    }

    public function getId()
    {
        return $this->_id;


    }

    public function isStarted()
    {
        return ($this->_started->count() > 0);
    }

    public function setNamespaceStorage($namespace, ISessionStorage $storage)
    {
        $this->_storages[$namespace] = $storage;

        return $this;
    }

    private function _namespacedName($namespace, $name)
    {
        return sprintf('%s:%s', $namespace, $name);
    }


    /**
     * Get session storage
     *
     * @param $namespace
     * @return ISessionStorage
     */
    private function _storage($namespace)
    {
        if (isset($this->_storages[$namespace])) {
            $storage = $this->_storages[$namespace];
        } else {
            $storage = $this->_storage;
        }
        /** @var $storage ISessionStorage */

        if (!$this->_started->contains($storage)) {
            $storage->start($this->_id);
            $this->_started->attach($storage);
        }

        return $storage;
    }

    /**
     * Remove session property
     *
     * @param $namespace
     * @param $key
     * @return mixed
     */
    public function remove($namespace, $key)
    {
        $name = $this->_namespacedName($namespace, $key);

        $storage = $this->_storage($namespace);
        if ($storage->has($name)) {
            return $storage->remove($name);
        }
        return;
    }

    /**
     * Set session property
     *
     * @param $namespace
     * @param $key
     * @param $data
     * @return mixed
     */
    public function set($namespace, $key, $data)
    {
        $name = $this->_namespacedName($namespace, $key);

        $storage = $this->_storage($namespace);
        if ($storage->set($name, $data)) {
            return $data;
        }
        return;
    }

    /**
     * Get session property
     *
     * @param $namespace
     * @param $key
     * @param null $default
     * @return mixed|null
     */
    public function get($namespace, $key, $default = null)
    {
        $name = $this->_namespacedName($namespace, $key);

        $storage = $this->_storage($namespace);
        if ($storage->has($name)) {
            return $storage->get($name);
        } else {
            return $default;
        }
    }

    public function saveAll()
    {

        if ($this->_started->count()) {
            $started = new \SplObjectStorage();
            foreach ($this->_started as $storage) {
                /** @var $storage ISessionStorage */
                $storage->save($this->_id);
                $started->attach($storage);
            }
            $this->_started->removeAll($started);
        }
    }

    public function __destruct()
    {
        try {
            $this->saveAll();
        } catch (\Exception $e) {
            // ignore all
        }
    }
}