<?php
/**
 * Copyright (c) Enalean 2021 -  Present. All Rights Reserved.
 *
 *  This file is a part of Tuleap.
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
 *
 */

declare(strict_types=1);

namespace Tuleap\ProgramManagement\Adapter\Workspace;

use Tracker;
use Tuleap\ProgramManagement\Domain\ProgramTracker;

/**
 * @psalm-immutable
 */
final class TrackerProxy implements ProgramTracker
{
    private function __construct(private int $id, private string $tracker_name, private int $project_id, private string $project_name)
    {
    }

    public function getTrackerName(): string
    {
        return $this->tracker_name;
    }

    public function getProjectId(): int
    {
        return $this->project_id;
    }

    public function getId(): int
    {
        return $this->id;
    }

    public static function fromTracker(Tracker $tracker): self
    {
        return new self($tracker->getId(), $tracker->getName(), (int) $tracker->getGroupId(), $tracker->getProject()->getPublicName());
    }

    public function getProjectName(): string
    {
        return $this->project_name;
    }
}
