<?php

class SystemCall 
{
    private $callback;

    public function __construct(callable $callback) 
    {
        $this->callback = $callback;
    }

    public function __invoke(Task $task, Scheduler $scheduler) 
    {
        $callback = $this->callback;

        return $callback($task, $scheduler);
    }
}

class Task 
{
    private $taskId;
    private $coroutine;
    private $sendValue = null;
    private $beforeFirstYield = true;

    public function __construct($taskId, Generator $coroutine) 
    {
        $this->taskId = $taskId;
        $this->coroutine = $coroutine;
    }

    public function getTaskId() 
    {
        return $this->taskId;
    }

    public function setSendValue($sendValue) 
    {
        $this->sendValue = $sendValue;
    }

    public function run() 
    {
        if ($this->beforeFirstYield) 
        {
            $this->beforeFirstYield = false;

            return $this->coroutine->current();
        } 
        else 
        {
            $retval = $this->coroutine->send($this->sendValue);
            $this->sendValue = null;

            return $retval;
        }
    }

    public function isFinished() 
    {
        return !$this->coroutine->valid();
    }
}

class Scheduler 
{
    protected $maxTaskId = 0;
    protected $taskMap = [];
    protected $taskQueue;

    public function __construct() 
    {
        $this->taskQueue = new SplQueue();
    }

    public function newTask(Generator $coroutine) 
    {
        $tid = ++$this->maxTaskId;
        $task = new Task($tid, $coroutine);

        $this->taskMap[$tid] = $task;
        $this->schedule($task);

        return $tid;
    }

    public function schedule(Task $task) 
    {
        $this->taskQueue->enqueue($task);
    }

    public function run() 
    {
        while (!$this->taskQueue->isEmpty()) 
        {
            $task = $this->taskQueue->dequeue();
            $retval = $task->run();
    
            if ($retval instanceof SystemCall) 
            {
                $retval($task, $this);
                continue;
            }
    
            if ($task->isFinished()) 
            {
                unset($this->taskMap[$task->getTaskId()]);
                continue;
            } 
            
            $this->schedule($task);
        }
    }

    public function killTask($tid) 
    {
        if (!isset($this->taskMap[$tid]))
            return false;
        
    
        unset($this->taskMap[$tid]);
    
        foreach ($this->taskQueue as $i => $task) 
        {
            if ($task->getTaskId() === $tid) 
            {
                unset($this->taskQueue[$i]);
                break;
            }
        }
    
        return true;
    }
}

function newTask(Generator $coroutine) 
{
    echo "new task";
    return new SystemCall(
        function(Task $task, Scheduler $scheduler) use ($coroutine) 
        {
            $task->setSendValue($scheduler->newTask($coroutine));
            $scheduler->schedule($task);
        }
    );
}

function killTask($tid) 
{
    return new SystemCall(
        function(Task $task, Scheduler $scheduler) use ($tid) 
        {
            $task->setSendValue($scheduler->killTask($tid));
            $scheduler->schedule($task);
        }
    );
}

function getTaskId() 
{
    return new SystemCall(function(Task $task, Scheduler $scheduler) 
    {
        $task->setSendValue($task->getTaskId());
        $scheduler->schedule($task);
    });
}
