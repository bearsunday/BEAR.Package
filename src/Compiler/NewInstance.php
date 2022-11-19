<?php

declare(strict_types=1);

namespace BEAR\Package\Compiler;

use Ray\Di\Exception\Unbound;
use Ray\Di\InjectorInterface;
use Throwable;

use function assert;
use function count;
use function in_array;
use function is_int;
use function printf;
use function sprintf;

use const PHP_EOL;

final class NewInstance
{
    /** @var list<string> */
    private array $compiled = [];

    /** @var array<string, string> */
    private array $failed = [];

    public function __construct(
        private InjectorInterface $injector,
    ) {
    }

    /** @param ''|class-string $interface */
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
            $this->failed[$dependencyIndex] = sprintf('%s: %s', $e::class, $e->getMessage());
            $this->progress('F');
            // @codeCoverageIgnoreEnd
        }
    }

    private function progress(string $char): void
    {
        static $cnt = 0;

        echo $char;
        assert(is_int($cnt));
        $cnt++;
        if ($cnt !== 60) {
            return;
        }

        $cnt = 0;
        echo PHP_EOL;
    }

    public function getCompiled(): int
    {
        return count($this->compiled);
    }

    /** @return array<string, string> */
    public function getFailed(): array
    {
        return $this->failed;
    }
}
