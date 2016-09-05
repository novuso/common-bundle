<?php declare(strict_types=1);

namespace Novuso\Common\Adapter\Process\Symfony;

use Exception;
use Novuso\Common\Application\Process\Process;
use Novuso\Common\Application\Process\ProcessManager;
use Novuso\System\Collection\LinkedQueue;
use Symfony\Component\Process\Process as Proc;

/**
 * SymfonyProcessManager is a Symfony process manager adapter
 *
 * @copyright Copyright (c) 2016, Novuso. <http://novuso.com>
 * @license   http://opensource.org/licenses/MIT The MIT License
 * @author    John Nickell <email@johnnickell.com>
 */
class SymfonyProcessManager implements ProcessManager
{
    /**
     * Max concurrent processes
     *
     * @var int
     */
    protected $maxCount;

    /**
     * Process queue
     *
     * @var LinkedQueue
     */
    protected $queue;

    /**
     * Active pids
     *
     * @var array
     */
    protected $pids;

    /**
     * Active processes
     *
     * @var array
     */
    protected $procs;

    /**
     * Running flag
     *
     * @var bool
     */
    protected $running;

    /**
     * Constructs SymfonyProcessManager
     *
     * @param int $maxCount The max concurrent processes or 0 for no limit
     */
    public function __construct(int $maxCount)
    {
        $this->maxCount = abs($maxCount);
        $this->queue = LinkedQueue::of(Process::class);
        $this->pids = [];
        $this->procs = [];
        $this->running = false;
    }

    /**
     * Destructs SymfonyProcessManager
     */
    public function __destruct()
    {
        $this->stop();
    }

    /**
     * {@inheritdoc}
     */
    public function attach(Process $process)
    {
        if ($this->running) {
            throw new Exception('Cannot attach process while running');
        }

        $this->queue->enqueue($process);
    }

    /**
     * {@inheritdoc}
     */
    public function clear()
    {
        if ($this->running) {
            throw new Exception('Cannot clear processes while running');
        }

        $this->queue = LinkedQueue::of(Process::class);
        $this->pids = [];
        $this->procs = [];
    }

    /**
     * {@inheritdoc}
     */
    public function run()
    {
        if ($this->running) {
            throw new Exception('Processes already running');
        }

        $this->running = true;

        while (!$this->queue->isEmpty()) {
            $this->init();
            $this->tick();
            $this->clean();
        }

        while (count($this->procs)) {
            $this->tick();
            $this->clean();
        }

        $this->running = false;
        $this->clear();
    }

    /**
     * Initializes a process if possible
     *
     * @return void
     */
    protected function init()
    {
        if ($this->maxCount === 0 || count($this->procs) < $this->maxCount) {
            /** @var Process $process */
            $process = $this->queue->dequeue();
            $proc = new Proc(
                $process->command(),
                $process->directory(),
                $process->environment(),
                $process->input(),
                $process->timeout()
            );
            if ($process->isOutputDisabled()) {
                $proc->disableOutput();
            }
            $this->startProcess($proc, $process->stdout(), $process->stderr());
            $pid = $proc->getPid();
            $this->pids[$pid] = true;
            $this->procs[$pid] = $proc;
        }
    }

    /**
     * Performs running checks on processes
     *
     * @return void
     */
    protected function tick()
    {
        usleep(1000);
        /** @var Proc $proc */
        foreach ($this->procs as $pid => $proc) {
            $proc->checkTimeout();
            if (!$proc->isRunning()) {
                $this->pids[$pid] = false;
            }
        }
    }

    /**
     * Cleans completed processes
     *
     * @return void
     *
     * @throws Exception When an error occurs in a child process
     */
    protected function clean()
    {
        foreach ($this->pids as $pid => $running) {
            if (!$running) {
                /** @var Proc $proc */
                $proc = $this->procs[$pid];
                if (!$proc->isSuccessful()) {
                    $command = $proc->getCommandLine();
                    $exitCode = $proc->getExitCode();
                    $exitText = $proc->getExitCodeText();
                    $stdErr = $proc->getErrorOutput();
                    $stdOut = $proc->getOutput();
                    $this->stop();
                    $fmt = 'Child process error (command:%s, exit_code:%s, exit_text:%s, stderr:%s, stdout:%s)';
                    $message = sprintf($fmt, $command, $exitCode, $exitText, $stdErr, $stdOut);
                    throw new Exception($message);
                }
                unset($this->pids[$pid]);
                unset($this->procs[$pid]);
            }
        }
    }

    /**
     * Stop running processes
     *
     * @return void
     */
    protected function stop()
    {
        /** @var Proc $proc */
        foreach ($this->procs as $proc) {
            $proc->stop();
        }
        $this->running = false;
        $this->clear();
    }

    /**
     * Starts a process
     *
     * @param Proc          $proc   The process
     * @param callable|null $stdout The STDOUT output callback
     * @param callable|null $stderr The STDERR output callback
     *
     * @return void
     */
    protected function startProcess(Proc $proc, callable $stdout = null, callable $stderr = null)
    {
        $out = Proc::OUT;
        $proc->start(function ($type, $data) use ($stdout, $stderr, $out) {
            if ($type === $out) {
                if ($stdout !== null) {
                    call_user_func($stdout, $data);
                }
            } else {
                if ($stderr !== null) {
                    call_user_func($stderr, $data);
                }
            }
        });
    }
}
