<?php
/**
 * Copyright (c) Enalean, 2021-Present. All Rights Reserved.
 *
 * This file is a part of Tuleap.
 *
 * Tuleap is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * Tuleap is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with Tuleap. If not, see <http://www.gnu.org/licenses/>.
 */

declare(strict_types=1);

namespace Tuleap\ProgramManagement\Adapter\Events;

use Psr\Log\Test\TestLogger;
use Tuleap\ProgramManagement\Domain\Events\ProgramIncrementUpdateEvent;
use Tuleap\Queue\WorkerEvent;
use Tuleap\Test\PHPUnit\TestCase;

final class ProgramIncrementUpdateEventProxyTest extends TestCase
{
    private TestLogger $logger;

    protected function setUp(): void
    {
        $this->logger = new TestLogger();
    }

    public function testItBuildsFromValidWorkerEvent(): void
    {
        $worker_event = new WorkerEvent($this->logger, [
            'event_name' => ProgramIncrementUpdateEvent::TOPIC,
            'payload'    => [
                'artifact_id'  => 29,
                'user_id'      => 186,
                'changeset_id' => 7806,
            ]
        ]);
        $event        = ProgramIncrementUpdateEventProxy::fromWorkerEvent($this->logger, $worker_event);
        self::assertSame(29, $event->getArtifactId());
        self::assertSame(186, $event->getUserId());
        self::assertSame(7806, $event->getChangesetId());
    }

    public function testItReturnsNullWhenUnrelatedTopic(): void
    {
        $worker_event = new WorkerEvent($this->logger, [
            'event_name' => 'unrelated.topic',
            'payload'    => []
        ]);
        self::assertNull(ProgramIncrementUpdateEventProxy::fromWorkerEvent($this->logger, $worker_event));
    }

    public function testItLogsMalformedPayload(): void
    {
        $worker_event = new WorkerEvent($this->logger, [
            'event_name' => ProgramIncrementUpdateEventProxy::TOPIC,
            'payload'    => []
        ]);
        self::assertNull(ProgramIncrementUpdateEventProxy::fromWorkerEvent($this->logger, $worker_event));
        self::assertTrue(
            $this->logger->hasWarning(
                sprintf('The payload for %s seems to be malformed, ignoring', ProgramIncrementUpdateEvent::TOPIC)
            )
        );
    }
}
