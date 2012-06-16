<?php

/* Include all dependencies */
require(__DIR__.'/../../vendor/autoload.php');

class SimpleRemoteRepository
{
    private $crSession = null;
    private $sessions = array();
    private $repository = null;
    private $dnode = null;

    /**
     * Constructor.
     *
     * Creates new remote repository for defined one.
     *
     * @param $repository - Repository created with RepositoryFactory.
     * @return new remote repository
     */
    public function __construct ($repository) {
        $this->loop = new React\EventLoop\StreamSelectLoop();

        $this->repository = $repository;
        $this->dnode = new DNode\DNode($this->loop, $this);
    }

    private function validateSessionName($name, $cb)
    {
        if (!array_key_exists($name, $this->sessions)) {
            $cb(null, 'RepositoryException', 'Named session $sessionName not found');
            return false;
        }
        return true;
    }

    /**
     * Get the names of children nodes
     *
     * @param $sessionName - name of the session
     * @param $path - absolute path of the parent node
     * @param $cb - callback function
     *
     * @return void
     */
    public function getNodes($sessionName, $path, $cb)
    {
        if (!$this->validateSessionName($sessionName, $cb))
            return false;

        $exception = null;
        $msg = null;
        $names = array ();

        try {
            $parent = $this->sessions[$sessionName]->getNode($path);
            $nodes = $parent->getNodes();
            $names = array_keys ($nodes->getArrayCopy());
        } catch (\Exception $e) {
            $exception = get_class($e);
            $msg = $e->getMessage();
        }

        $cb($names, $exception, $msg);
    }

    /**
     * Get the names of all properties
     *
     * @param $sessionName - name of the session
     * @param $path - absolute path of the node
     * @param $cb - callback function
     *
     * @return void
     */
    public function getProperties($sessionName, $path, $cb)
    {
        if (!$this->validateSessionName($sessionName, $cb))
            return false;

        $exception = null;
        $msg = null;
        $names = array ();

        try {
            $parent = $this->sessions[$sessionName]->getNode($path);
            $properties = $parent->getProperties ();
            $names = array_keys ($properties);
        } catch (\Exception $e) {
            $exception = get_class($e);
            $msg = $e->getMessage();
        }

        $cb($names, $exception, $msg);
    }

    /* Get value of the property at specified path */
    public function getPropertyValue($sessionName, $path, $cb)
    {
        if (!array_key_exists($sessionName, $this->sessions)) {
            $cb(null, 'RepositoryException', 'Named session $sessionName not found');
        }

        $exception = null;
        $msg = null;
        $val = null;

        try {
            $val = $this->sessions[$sessionName]->getProperty($path)->getValue();
        } catch (\Exception $e){
            $exception = get_class($e);
            $msg = $e->getMessage();
        }

        $cb($val, $exception, $msg);
    }

    /* Check if specified item exists at path specified path */
    public function itemExists($sessionName, $path, $cb)
    {
        if (!$this->validateSessionName($sessionName, $cb))
            return false;

        $exists = $this->sessions[$sessionName]->itemExists ($path);
        $cb($exists, null, null);
    }

    public function addNode($sessionName, $path, $name, $type, $cb)
    {
        if (!$this->validateSessionName($sessionName, $cb))
            return false;

        $exception = null;
        $msg = null;

        try {
            $parent = $this->sessions[$sessionName]->getNode($path);
            $parent->addNode($name, $type);
        } catch (\Exception $e) {
            $exception = get_class($e);
            $msg = $e->getMessage();
        }

        $cb($exception, $msg);
    }

    public function setProperty($sessionName, $path, $name, $value, $type, $cb)
    {
        if (!$this->validateSessionName($sessionName, $cb))
            return false;

        $exception = null;
        $msg = null;

        try {
            $parent = $this->sessions[$sessionName]->getNode($path);
            $parent->setProperty($name, $value, $type);
        } catch (\Exception $e) {
            $exception = get_class($e);
            $msg = $e->getMessage();
        }

        $cb($exception, $msg);
    }

    /* Create named session */
    public function createSession($sessionName, $name, $password)
    {
        $credentials = new \PHPCR\SimpleCredentials($name, $password);
        $this->sessions[$sessionName] = $this->repository->login($credentials);
    }

    public function listen($port)
    {
        $this->dnode->listen($port);
        $this->loop->run();
    }
}

/* Initialize PHPCR Repository */
$params = array (
    'midgard2.configuration.file' => getenv('MIDGARD_ENV_GLOBAL_SHAREDIR') . "/midgard2.conf"
);
$repository = Midgard\PHPCR\RepositoryFactory::getRepository($params);

/* Initialize server for given repository */
$server = new SimpleRemoteRepository($repository);
$server->createSession("default", "admin", "password");
$server->listen(7070);
