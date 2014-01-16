<?php
namespace Fudge\Sknife\Service;

/**
 * Class SemaphoreShm
 * @author Yohann Marillet
 */
class SemaphoreShm
{
    /** @var string */
    private $filePath;

    /** @var string */
    private $projectIdentifier;

    /** @var int */
    private $shm_key;

    /** @var resource */
    private $segment;

    public function __construct($filePath=null, $memsize=1024, $perm=0666, $projectIdentifier='I')
    {
        if (null === $filePath) {
            $filePath = __FILE__;
        }
        $this->filePath = $filePath;

        if (!file_exists($this->filePath)) {
            touch($this->filePath);
        }

        $this->projectIdentifier = $projectIdentifier;
        $this->shm_key = ftok($this->filePath, $this->projectIdentifier);
        $this->segment = shm_attach($this->shm_key, $memsize, $perm);
    }

    /**
     * Gets the value of the semaphore
     * @author Yohann Marillet
     */
    public function get($key=1)
    {
        $return = false;
        if ($this->has($key, true)) {
            $return = shm_get_var($this->segment, $key)[0];
        }

        return $return;
    }

    /**
     * Returns if the semaphore is present in memory
     * @author Yohann Marillet
     */
    public function has($key=1)
    {
        $return = shm_has_var($this->segment, $key);

        return $return;
    }

    /**
     * @author Yohann Marillet
     */
    public function put($key=1, $value=true)
    {
        $res = shm_put_var($this->segment, $key, [$value]);
        if (!$res) {
            throw new \Exception('Cannot put data in semaphore');
        }

        return $this;
    }

    /**
     * @param int $key
     *
     * @throws \Exception
     * @return $this
     * @author Yohann Marillet
     */
    public function delete($key=1)
    {
        $res = false;
        if ($this->has($key, true)) {
            $value = $this->get($key,true);
            $res = shm_remove_var($this->segment, $key);
        }

        if (!$res) {
            throw new \Exception('Cannot delete data in semaphore');
        }

        return $this;
    }

    /**
     * Destroy the shared memory
     * @author Yohann Marillet
     */
    public function remove()
    {
        @shm_remove($this->shm_key);
    }

    /**
     * Destruct
     */
    public function __destruct()
    {
        shm_detach($this->segment);
    }
}
