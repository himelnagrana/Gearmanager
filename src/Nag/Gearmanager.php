<?php

namespace Nag;

class Gearmanager
{
    /** @var array */
    protected $conf;

    /** @var \GearmanClient */
    protected $gearman;

    /**
     * @param array $config [example: array('host' => 'localhost', 'port' => '4730')]
     * @param array $tasks [give fully qualified name as index. example: array('Task/Sendmail')]
     */
    public function __construct(array $config, array $tasks = array())
    {
        $this->conf  = $config;
        $this->tasks = $tasks;

        $this->setupGearman();
    }

    /**
     * Sets up gearman client with given configuration
     */
    private function setupGearman()
    {
        if (class_exists('GearmanClient')) {
            $this->gearman = new \GearmanClient();
            $this->gearman->addServer($this->conf['gearman']['host'], $this->conf['gearman']['port']);
        }
    }

    /**
     * Adds a background task to be run in parallel with other tasks.
     * @param string $task [fully qualified task class name]
     * @param array $payload
     * @param string $priority [values: normal, low, high; default value is 'normal']
     */
    public function fireEvent($task, $payload, $priority = 'normal')
    {
        if ($this->gearman instanceof \GearmanClient) {

            if ($this->verifyTasks($task)) {

                switch ($priority)
                {
                    case 'normal':  $this->gearman->addTaskBackground($task, json_encode($payload));
                                    break;

                    case 'low'  :   $this->gearman->addTaskLowBackground($task, json_encode($payload));
                                    break;

                    case 'high' :   $this->gearman->addTaskHighBackground($task, json_encode($payload));
                                    break;
                }

                $this->gearman->runTasks();
            }
        }
    }

    /**
     * Adds a task to be run in parallel with other tasks.
     * @param string $task [fully qualified task class name]
     * @param array $payload
     * @param string $priority [values: normal, low, high; default value is 'normal']
     */
    public function fireParallel($task, $payload, $priority = 'normal')
    {
        if ($this->gearman instanceof \GearmanClient) {

            if ($this->verifyTasks($task)) {

                switch ($priority)
                {
                    case 'normal':  $this->gearman->addTask($task, json_encode($payload));
                        break;

                    case 'low'  :   $this->gearman->addTaskLow($task, json_encode($payload));
                        break;

                    case 'high' :   $this->gearman->addTaskHigh($task, json_encode($payload));
                        break;
                }

                $this->gearman->runTasks();
            }
        }
    }

    /**
     * Runs a single task and returns a string representation of the result
     * @param string $task [fully qualified task class name]
     * @param array $payload
     * @param string $priority [values: low, high; default value is 'low']
     * @return string $response
     */
    public function fireUrgent($task, $payload, $priority = 'low')
    {
        $response = '';

        if ($this->gearman instanceof \GearmanClient) {

            if ($this->verifyTasks($task)) {

                switch ($priority)
                {
                    case 'low'  :   $response = $this->gearman->doLow($task, json_encode($payload));
                        break;

                    case 'high' :   $response = $this->gearman->doHigh($task, json_encode($payload));
                        break;
                }
            }
        }

        return $response;
    }

    /**
     * Checks if any task is in the pool or not
     * @param $task
     * @return bool
     */
    private function verifyTasks($task)
    {
        if (empty($this->tasks)) {
            return true;
        }

        if (in_array($task, $this->tasks)) {
            return true;
        }

        return false;
    }
}