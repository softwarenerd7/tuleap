<?php
/**
 * Copyright (c) Enalean, 2020 - Present. All Rights Reserved.
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

namespace Tuleap\Gitlab\API;

use Mockery;
use Tuleap\Gitlab\Test\Builder\CredentialsTestBuilder;

final class GitlabProjectBuilderTest extends \Tuleap\Test\PHPUnit\TestCase
{
    use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;

    /**
     * @var GitlabProjectBuilder
     */
    private $project_builder;

    /**
     * @var Mockery\LegacyMockInterface|Mockery\MockInterface|ClientWrapper
     */
    private $gitlab_api_client;
    /**
     * @var Credentials
     */
    private $credentials;

    protected function setUp(): void
    {
        parent::setUp();

        $this->gitlab_api_client = Mockery::mock(ClientWrapper::class);

        $this->credentials = CredentialsTestBuilder::get()->build();

        $this->project_builder = new GitlabProjectBuilder(
            $this->gitlab_api_client
        );
    }

    public function testItThrowsAnExceptionIfRequestBodyIsEmpty(): void
    {
        $this->gitlab_api_client->shouldReceive("getUrl")
            ->once()
            ->with($this->credentials, "/projects/1")
            ->andReturn([]);

        $this->expectException(GitlabResponseAPIException::class);

        $this->project_builder->getProjectFromGitlabAPI($this->credentials, 1);
    }

    public function testItThrowsAnExceptionIfRequestBodyDoesNotHaveIdKey(): void
    {
        $this->gitlab_api_client->shouldReceive("getUrl")
            ->once()
            ->with($this->credentials, "/projects/1")
            ->andReturn([
                'description' => 'My GitLab project',
                'web_url' => 'https://example.com/root/project01',
                'name' => 'Project 01',
                'path_with_namespace' => 'root/project01',
                'last_activity_at' => '2020-11-12',
            ]);

        $this->expectException(GitlabResponseAPIException::class);

        $this->project_builder->getProjectFromGitlabAPI($this->credentials, 1);
    }

    public function testItThrowsAnExceptionIfRequestBodyDoesNotHaveDescriptionKey(): void
    {
        $this->gitlab_api_client->shouldReceive("getUrl")
            ->once()
            ->with($this->credentials, "/projects/1")
            ->andReturn([
                'id' => 1,
                'web_url' => 'https://example.com/root/project01',
                'name' => 'Project 01',
                'path_with_namespace' => 'root/project01',
                'last_activity_at' => '2020-11-12',
            ]);

        $this->expectException(GitlabResponseAPIException::class);

        $this->project_builder->getProjectFromGitlabAPI($this->credentials, 1);
    }

    public function testItThrowsAnExceptionIfRequestBodyDoesNotHaveWebURLKey(): void
    {
        $this->gitlab_api_client->shouldReceive("getUrl")
            ->once()
            ->with($this->credentials, "/projects/1")
            ->andReturn([
                'id' => 1,
                'description' => 'My GitLab project',
                'name' => 'Project 01',
                'path_with_namespace' => 'root/project01',
                'last_activity_at' => '2020-11-12',
            ]);

        $this->expectException(GitlabResponseAPIException::class);

        $this->project_builder->getProjectFromGitlabAPI($this->credentials, 1);
    }

    public function testItThrowsAnExceptionIfRequestBodyDoesNotHavePathKey(): void
    {
        $this->gitlab_api_client->shouldReceive("getUrl")
            ->once()
            ->with($this->credentials, "/projects/1")
            ->andReturn([
                'id' => 1,
                'description' => 'My GitLab project',
                'web_url' => 'https://example.com/root/project01',
                'name' => 'Project 01',
                'last_activity_at' => '2020-11-12',
            ]);

        $this->expectException(GitlabResponseAPIException::class);

        $this->project_builder->getProjectFromGitlabAPI($this->credentials, 1);
    }

    public function testItThrowsAnExceptionIfRequestBodyDoesNotHaveLastActivityKey(): void
    {
        $this->gitlab_api_client->shouldReceive("getUrl")
            ->once()
            ->with($this->credentials, "/projects/1")
            ->andReturn([
                'id' => 1,
                'description' => 'My GitLab project',
                'web_url' => 'https://example.com/root/project01',
                'name' => 'Project 01',
                'path_with_namespace' => 'root/project01',
            ]);

        $this->expectException(GitlabResponseAPIException::class);

        $this->project_builder->getProjectFromGitlabAPI($this->credentials, 1);
    }

    public function testItBuildsAGitlabProjectObject(): void
    {
        $this->gitlab_api_client->shouldReceive("getUrl")
            ->once()
            ->with($this->credentials, "/projects/1")
            ->andReturn([
                'id' => 1,
                'description' => 'My GitLab project',
                'web_url' => 'https://example.com/root/project01',
                'name' => 'Project 01',
                'path_with_namespace' => 'root/project01',
                'last_activity_at' => '2020-11-12',
            ]);

        $gitlab_project = $this->project_builder->getProjectFromGitlabAPI($this->credentials, 1);

        self::assertSame(1, $gitlab_project->getId());
        self::assertSame("My GitLab project", $gitlab_project->getDescription());
        self::assertSame("root/project01", $gitlab_project->getPathWithNamespace());
        self::assertSame("https://example.com/root/project01", $gitlab_project->getWebUrl());
        self::assertSame(1605135600, $gitlab_project->getLastActivityAt()->getTimestamp());
    }

    public function testItBuildsAGitlabProjectObjectWithNullDescription(): void
    {
        $this->gitlab_api_client->shouldReceive("getUrl")
            ->once()
            ->with($this->credentials, "/projects/1")
            ->andReturn([
                'id' => 1,
                'description' => null,
                'web_url' => 'https://example.com/root/project01',
                'name' => 'Project 01',
                'path_with_namespace' => 'root/project01',
                'last_activity_at' => '2020-11-12',
            ]);

        $gitlab_project = $this->project_builder->getProjectFromGitlabAPI($this->credentials, 1);

        self::assertSame(1, $gitlab_project->getId());
        self::assertSame("", $gitlab_project->getDescription());
        self::assertSame("root/project01", $gitlab_project->getPathWithNamespace());
        self::assertSame("https://example.com/root/project01", $gitlab_project->getWebUrl());
        self::assertSame(1605135600, $gitlab_project->getLastActivityAt()->getTimestamp());
    }
}
