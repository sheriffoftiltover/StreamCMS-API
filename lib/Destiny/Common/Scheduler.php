<?php

namespace Destiny\Common;

use DateTime;
use Destiny\Common\Utils\Date;
use Destiny\Common\Utils\Options;
use PDO;
use Psr\Log\LoggerInterface;

/**
 * Simple way of executing actions based on logfiles and cooldowns
 */
class Scheduler
{

    /**
     * Public logger
     *
     * @var LoggerInterface
     */
    public $logger = null;

    /**
     * The schedule data
     *
     * @var array
     */
    public $schedule = [];

    /**
     * [logger,schedule]
     *
     * @param array $args
     */
    public function __construct(array $args = [])
    {
        Options::setOptions($this, $args);
    }

    /**
     * Load the schedule from the data source
     *
     * @return void
     */
    public function loadSchedule()
    {
        foreach ($this->schedule as $i => $action) {
            $task = $this->getTask($this->schedule [$i] ['action']);
            if (empty ($task)) {
                $this->schedule [$i] ['lastExecuted'] = date(DateTime::ATOM);
                $this->schedule [$i] ['executeCount'] = 0;
                $this->insertTask($this->schedule [$i]);
            } else {
                $this->schedule [$i] = array_merge($this->schedule [$i], $task);
            }
        }
    }

    /**
     * Load a task from the db by action name
     *
     * @param string $name
     */
    protected function getTask($name)
    {
        $conn = Application::instance()->getConnection();
        $stmt = $conn->prepare('SELECT * FROM dfl_scheduled_tasks WHERE action = :action LIMIT 0,1');
        $stmt->bindValue('action', $name, PDO::PARAM_STR);
        $stmt->execute();
        return $stmt->fetch();
    }

    /**
     * Insert a task into the db
     *
     * @param array $schedule
     */
    protected function insertTask(array $task)
    {
        $conn = Application::instance()->getConnection();
        $conn->insert('dfl_scheduled_tasks', ['action' => $task ['action'], 'lastExecuted' => $task ['lastExecuted'], 'frequency' => $task ['frequency'], 'period' => $task ['period'], 'executeOnNextRun' => ($task ['executeOnNextRun']) ? 1 : 0, 'executeCount' => $task ['executeCount']], [PDO::PARAM_STR, PDO::PARAM_STR, PDO::PARAM_INT, PDO::PARAM_STR, PDO::PARAM_INT, PDO::PARAM_INT]);
    }

    /**
     * Executes all the tasks
     *
     * @return void
     */
    public function executeShedule()
    {
        $this->logger->debug('Schedule starting');
        foreach ($this->schedule as $i => $action) {
            // First run/ Execute on next run
            if ($this->schedule [$i] ['executeOnNextRun']) {
                $this->schedule [$i] ['executeCount'] = intval($this->schedule [$i] ['executeCount']) + 1;
                $this->schedule [$i] ['lastExecuted'] = date(DateTime::ATOM);
                $this->schedule [$i] ['executeOnNextRun'] = false;
                $this->updateTask($this->schedule [$i]);
                $this->executeTask($this->schedule [$i]);
                continue;
            }
            // Schedule run
            $nextExecute = Date::getDateTime($this->schedule [$i] ['lastExecuted']);
            $nextExecute->modify('+' . $this->schedule [$i] ['frequency'] . ' ' . $this->schedule [$i] ['period']);
            if (time() > $nextExecute->getTimestamp()) {
                $this->schedule [$i] ['executeCount'] = intval($this->schedule [$i] ['executeCount']) + 1;
                $this->schedule [$i] ['lastExecuted'] = date(DateTime::ATOM);
                $this->schedule [$i] ['executeOnNextRun'] = false;
                $this->updateTask($this->schedule [$i]);
                $this->executeTask($this->schedule [$i]);
            }
        }
        $this->logger->debug('Schedule complete');
    }

    /**
     * Update the tasks last run schedule
     *
     * @param array $schedule
     */
    protected function updateTask(array $task)
    {
        $conn = Application::instance()->getConnection();
        $conn->update('dfl_scheduled_tasks', ['executeOnNextRun' => ($task ['executeOnNextRun']) ? 1 : 0, 'lastExecuted' => $task ['lastExecuted'], 'executeCount' => $task ['executeCount']], ['action' => $task ['action']], [PDO::PARAM_INT, PDO::PARAM_STR, PDO::PARAM_STR]);
    }

    /**
     * Execute schedule task
     *
     * @param array $task
     */
    protected function executeTask(array $task)
    {
        $this->logger->debug(sprintf('Execute start %s', $task ['action']));
        $actionClass = 'Destiny\\Tasks\\' . $task ['action'];
        if (class_exists($actionClass, true)) {
            $actionObj = new $actionClass ($task);
            $actionObj->execute($this->logger);
        } else {
            throw new Exception (sprintf('Action not found: %s', $actionClass));
        }
        $this->logger->debug(sprintf('Execute end %s', $task ['action']));
    }

    /**
     * Execute a task by name
     *
     * @param string $name
     */
    public function executeTaskByName($name)
    {
        $this->logger->debug(sprintf('Schedule task %s', $name));
        $task = $this->getTaskByName($name);
        if (!empty ($task)) {
            $task ['executeCount'] = intval($task ['executeCount']) + 1;
            $task ['lastExecuted'] = date(DateTime::ATOM);
            $this->updateTask($task);
            $this->executeTask($task);
        }
    }

    /**
     * Get a registered task by name
     *
     * @param string $name
     * @return array
     */
    public function getTaskByName($name)
    {
        foreach ($this->schedule as $i => $action) {
            if (strcasecmp($action ['action'], $name) === 0) {
                return $this->schedule [$i];
            }
        }
        return null;
    }

    public function getLogger()
    {
        return $this->logger;
    }

    public function setLogger(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    public function getSchedule()
    {
        return $this->schedule;
    }

    public function setSchedule(array $schedule)
    {
        $this->schedule = $schedule;
    }

}