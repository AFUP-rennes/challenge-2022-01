<?php

declare(strict_types=1);

namespace Challenge;

final class Player
{
    private const TIMEOUT = 5;

    private bool $debug;
    private string $name;
    /** @var resource */
    private $process;
    /** @var resource[] */
    private array $pipes = [];

    private float $microtimeSum = 0;
    private int $nbRequest = 0;

    public function __construct(string $name, string $script, bool $debug = false)
    {
        if (basename($script) !== 'battle.php') {
            throw new \RuntimeException('battle script must be base named "battle.php"');
        }
        $script = realpath($script);
        if ($script === false) {
            throw new \RuntimeException('Could not found script');
        }

        $cwd = dirname($script);
        $process = proc_open(
            'php ' . $script,
            [
                0 => ["pipe", "r"],
                1 => ["pipe", "w"],
                2 => ["pipe", "w"],
            ],
            $this->pipes,
            $cwd
        );

        if ($process === false) {
            throw new \RuntimeException('Could not start process php ' . $script);
        }

        $this->process = $process;

        stream_set_blocking($this->pipes[1], false);
        stream_set_blocking($this->pipes[2], false);
        $this->name = $name;
        $this->debug = $debug;
    }

    public function request(string $command): string
    {
        $meta = proc_get_status($this->process);
        if ($meta['running'] === false) {
            $error = stream_get_contents($this->pipes[2]);
            if ($error !== '') {
                $error = ': ' . $error;
            }
            throw new \RuntimeException("Process {$this->name} not running" . $error);
        }
        if ($this->debug) {
            echo "{$this->name} > {$command}\n";
        }
        fwrite($this->pipes[0], "{$command}\n");

        $line = '';
        $start = microtime(true);
        while ($line === '' || $line[-1] !== "\n") {
            $gets = fgets($this->pipes[1]);
            if (false === $gets) {
                $meta = proc_get_status($this->process);
                if ($meta['running'] === false) {
                    $error = stream_get_contents($this->pipes[2]);
                    $error = \trim(\preg_replace('`^Stack trace:.*`ms', '', $error));
                    if ($error !== '') {
                        throw new \RuntimeException($error);
                    }
                    throw new \RuntimeException("Process {$this->name} not running");
                }
                if ((microtime(true) - $start) > self::TIMEOUT) {
                    proc_terminate($this->process);
                    throw new \RuntimeException('timeout');
                }
                continue;
            }
            $line .= $gets;
        }

        $this->microtimeSum += microtime(true) - $start;
        $this->nbRequest++;

        $line = trim($line);
        if ($this->debug) {
            echo "{$this->name} < {$line}\n";
        }

        return $line;
    }

    public function stop(): void
    {
        try {
            fclose($this->pipes[0]);
            fclose($this->pipes[1]);
            fclose($this->pipes[2]);
            proc_close($this->process);
        } catch (\Throwable) {
        }
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getResponseTime(): float
    {
        if ($this->nbRequest === 0) {
            return 0;
        }

        return $this->microtimeSum / $this->nbRequest;
    }
}
