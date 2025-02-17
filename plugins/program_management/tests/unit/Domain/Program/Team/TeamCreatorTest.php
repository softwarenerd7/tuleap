<?php
/**
 * Copyright (c) Enalean, 2020 - Present. All Rights Reserved.
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

namespace Tuleap\ProgramManagement\Domain\Team\Creation;

use Tuleap\ProgramManagement\Domain\Program\ProgramIsTeamException;
use Tuleap\ProgramManagement\Tests\Stub\RetrieveProjectStub;
use Tuleap\ProgramManagement\Tests\Stub\RetrieveUserStub;
use Tuleap\ProgramManagement\Tests\Stub\UserIdentifierStub;
use Tuleap\ProgramManagement\Tests\Stub\VerifyIsTeamStub;
use Tuleap\ProgramManagement\Tests\Stub\VerifyProjectPermissionStub;
use Tuleap\Test\Builders\ProjectTestBuilder;

final class TeamCreatorTest extends \Tuleap\Test\PHPUnit\TestCase
{
    private const PROGRAM_ID = 101;
    private const TEAM_ID    = 102;
    private RetrieveProjectStub $project_retriever;
    private VerifyIsTeamStub $team_verifier;
    private VerifyProjectPermissionStub $permission_verifier;
    /**
     * @var mixed|\PHPUnit\Framework\MockObject\Stub|BuildTeam
     */
    private $team_builder;
    /**
     * @var \PHPUnit\Framework\MockObject\MockObject|TeamStore
     */
    private $team_store;
    private \Project $program_project;
    private UserIdentifierStub $user_identifier;

    protected function setUp(): void
    {
        $this->program_project     = ProjectTestBuilder::aProject()->withId(self::PROGRAM_ID)->build();
        $this->project_retriever   = RetrieveProjectStub::withValidProjects($this->program_project);
        $this->team_verifier       = VerifyIsTeamStub::withNotValidTeam();
        $this->permission_verifier = VerifyProjectPermissionStub::withAdministrator();
        $this->team_builder        = $this->createStub(BuildTeam::class);
        $this->team_store          = $this->createMock(TeamStore::class);
        $this->user_identifier     = UserIdentifierStub::buildGenericUser();
    }

    private function getCreator(): TeamCreator
    {
        return new TeamCreator(
            $this->project_retriever,
            $this->team_verifier,
            $this->permission_verifier,
            $this->team_builder,
            $this->team_store,
            RetrieveUserStub::withGenericUser()
        );
    }

    public function testItCreatesAPlan(): void
    {
        $this->team_builder->method('checkProjectIsATeam');
        $this->team_store->expects(self::once())->method('save');

        $this->getCreator()->create($this->user_identifier, self::PROGRAM_ID, [self::TEAM_ID]);
    }

    public function testThrowExceptionWhenTeamIdsContainProgram(): void
    {
        $this->expectException(ProgramIsTeamException::class);

        $this->getCreator()->create($this->user_identifier, self::PROGRAM_ID, [self::TEAM_ID, self::PROGRAM_ID]);
    }
}
