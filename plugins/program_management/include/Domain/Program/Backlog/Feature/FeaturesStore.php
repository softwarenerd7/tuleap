<?php
/**
 * Copyright (c) Enalean, 2021 - Present. All Rights Reserved.
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

namespace Tuleap\ProgramManagement\Domain\Program\Backlog\Feature;

use Tuleap\ProgramManagement\Domain\Program\ProgramIdentifier;

interface FeaturesStore
{
    /**
     * @psalm-return list<array{tracker_name: string, artifact_id: int, artifact_title: string, field_title_id: int}>
     */
    public function searchPlannableFeatures(ProgramIdentifier $program): array;

    /**
     * @psalm-return list<array{artifact_id: int, program_id: int, title: string}>
     */
    public function searchOpenFeatures(int $offset, int $limit, ProgramIdentifier ...$program_identifiers): array;

    public function searchOpenFeaturesCount(ProgramIdentifier ...$program_identifiers): int;
}
