<?php

declare(strict_types=1);

/**
 * This file is part of CodeIgniter 4 framework.
 *
 * (c) CodeIgniter Foundation <admin@codeigniter.com>
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

namespace Tests\Support\Orchestrator;

use CodeIgniter\Test\Orchestrator\AbstractOrchestrator;

final class OrchestratorStub extends AbstractOrchestrator
{
    public int $afterBuildCalls = 0;

    /**
     * @param array<non-empty-string, string> $extraEnv
     */
    public function __construct(
        private readonly string $root,
        private readonly string $skeleton = '',
        private readonly array $extraEnv = [],
    ) {
    }

    public function getPath(): string
    {
        return $this->skeleton !== '' ? $this->skeleton : parent::getPath();
    }

    protected function getPackageRoot(): string
    {
        return $this->root;
    }

    protected function getFixtures(): array
    {
        return [__DIR__ . '/Fixtures', __DIR__ . '/ExtraFixtures'];
    }

    protected function getEnv(): array
    {
        return [
            'logger.threshold'    => '0',
            'orchestrator.quoted' => 'a b #c "d" \\e',
            ...$this->extraEnv,
        ];
    }

    protected function afterBuild(): void
    {
        $this->afterBuildCalls++;
    }
}
