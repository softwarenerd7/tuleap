<?php
/**
 * Copyright (c) Enalean, 2020 - Present. All Rights Reserved.
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
 * along with Tuleap; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
 */

declare(strict_types=1);

namespace Tuleap\Gitlab\Reference;

use Tuleap\Gitlab\Repository\GitlabRepository;
use Tuleap\Gitlab\Repository\Webhook\PostPush\Commits\CommitTuleapReferenceDAO;

class GitlabCommitFactory
{
    /**
     * @var CommitTuleapReferenceDAO
     */
    private $gitlab_commit_dao;

    public function __construct(CommitTuleapReferenceDAO $gitlab_commit_dao)
    {
        $this->gitlab_commit_dao = $gitlab_commit_dao;
    }

    public function getGitlabCommitInRepositoryWithSha1(GitlabRepository $repository, string $commit_sha1): ?GitlabCommit
    {
        $row = $this->gitlab_commit_dao->searchCommitInRepositoryWithSha1(
            $repository->getId(),
            $commit_sha1
        );

        if ($row === null) {
            return null;
        }

        return $this->getInstanceFromRow($row);
    }

    private function getInstanceFromRow(array $row): GitlabCommit
    {
        return new GitlabCommit(
            $row['gitlab_repository_id'],
            $row['commit_sha1'],
            $row['commit_date'],
            $row['commit_title'],
            $row['author_name'],
            $row['author_email']
        );
    }
}
