<?php

declare(strict_types=1);

namespace BEAR\Package\Compiler;

use Ray\Di\Exception\Unbound;
use Ray\Di\InjectorInterface;
use Throwable;

use function count;
use function get_class;
use function in_array;
use function printf;
use function sprintf;

use const PHP_EOL;

final class NewInstance
{
    /** @var list<string> */
    private $compiled = [];

    /** @var array<string, string> */
    private $failed = [];

    /** @var InjectorInterface */
    private $injector;

    public function __construct(InjectorInterface $injector)
    {
        $this->injector = $injector;
    }

    public function __invoke(string $interface, string $name = ''): void
    {
        $dependencyIndex = $interface . '-' . $name;
        if (in_array($dependencyIndex, $this->compiled, true)) {
            // @codeCoverageIgnoreStart
            printf("S %s:%s\n", $interface, $name);
            // @codeCoverageIgnoreEnd
        }

        try {
            $this->injector->getInstance($interface, $name);
            $this->compiled[] = $dependencyIndex;
            $this->progress('.');
        } catch (Unbound $e) {
            if ($dependencyIndex === 'Ray\Aop\MethodInvocation-') {
                return;
            }

            $this->failed[$dependencyIndex] = $e->getMessage();
            $this->progress('F');
            // @codeCoverageIgnoreStart
        } catch (Throwable $e) {
            $this->failed[$dependencyIndex] = sprintf('%s: %s', get_class($e), $e->getMessage());
            $this->progress('F');
            // @codeCoverageIgnoreEnd
        }
    }

    private function progress(string $char): void
    {
        /**
         * @var int
         */
        static $cnt = 0;

        echo $char;
        $cnt++;
        if ($cnt === 60) {
            $cnt = 0;
            echo PHP_EOL;
        }
    }

    public function getCompiled(): int
    {
        return count($this->compiled);
    }

    /**
     * @return array<string, string>
     */
    public function getFailed(): array
    {
        return $this->failed;
    }
}
