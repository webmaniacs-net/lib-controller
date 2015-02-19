<?php
namespace wmlib\controller\SessionStorage;

use wmlib\controller\ISessionStorage;

class Native implements ISessionStorage
{

    /**
     * Starts the session.
     *
     * @throws \RuntimeException If something goes wrong starting the session.
     * @param $id
     * @return bool True if started.
     */
    public function start($id)
    {
        session_id($id);
        session_start();
    }

    /**
     * Save the session to storage.
     *
     * @throws \RuntimeException If something goes wrong starting the session.
     * @param $id
     * @return bool
     */
    public function save($id)
    {
        if (session_id() === $id) {
            session_write_close();
            return true;
        } else {
            return false;
        }
    }

    /**
     * Remove session property
     *
     * @param $key
     * @return mixed|void Old value if found
     */
    public function remove($key)
    {
        if (isset($_SESSION[$key])) {
            $existed = $_SESSION[$key];
            unset($_SESSION[$key]);
            return $existed;
        }
        return;
    }

    /**
     * Set session property
     *
     * @param $key
     * @param $data
     * @return boolean
     */
    public function set($key, $data)
    {
        $_SESSION[$key] = $data;
        return true;
    }

    /**
     * Get session property
     *
     * @param $key
     * @return mixed
     */
    public function get($key)
    {
        if (isset($_SESSION[$key])) {
            return $_SESSION[$key];
        } else {
            throw new \OutOfBoundsException('Can\'t get "' . var_export($key, true) . '" session key');
        }
    }

    /**
     * Check session property exists
     *
     * @param $key
     * @return mixed
     */
    public function has($key)
    {
        return (isset($_SESSION[$key]));
    }
}