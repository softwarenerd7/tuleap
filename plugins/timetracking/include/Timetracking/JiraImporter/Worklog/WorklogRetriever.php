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
 *
 */

declare(strict_types=1);

namespace Tuleap\Timetracking\JiraImporter\Worklog;

use Psr\Log\LoggerInterface;
use Tuleap\Tracker\Creation\JiraImporter\ClientWrapper;
use Tuleap\Tracker\Creation\JiraImporter\Import\Artifact\IssueAPIRepresentation;
use Tuleap\Tracker\Creation\JiraImporter\JiraClient;

class WorklogRetriever
{
    /**
     * @var JiraClient
     */
    private $client;

    /**
     * @var LoggerInterface
     */
    private $logger;

    public function __construct(JiraClient $client, LoggerInterface $logger)
    {
        $this->client = $client;
        $this->logger = $logger;
    }

    /**
     * @return Worklog[]
     */
    public function getIssueWorklogsFromAPI(IssueAPIRepresentation $issue_representation): array
    {
        $worklogs = [];
        $start_at = 0;
        $total    = 0;

        do {
            $worklog_url = $this->getWorklogUrl($issue_representation, $start_at);
            $this->logger->info("Get worklogs (start at $start_at) for " . $issue_representation->getKey());
            $json = $this->client->getUrl($worklog_url);
            if (! isset($json['total'], $json['worklogs'])) {
                throw new \RuntimeException(
                    sprintf(
                        '%s route did not return the expected format: `total` or `worklogs` key are missing',
                        $this->getWorklogUrl($issue_representation, $start_at)
                    )
                );
            }
            foreach ($json['worklogs'] as $json_worklog) {
                $worklogs[] = Worklog::buildFromAPIResponse($json_worklog);
                $start_at++;
                $total++;
            }
        } while ($total < (int) $json['total']);

        return $worklogs;
    }

    private function getWorklogUrl(IssueAPIRepresentation $issue_representation, int $start_at): string
    {
        return ClientWrapper::JIRA_CORE_BASE_URL .
            '/issue/' .
            $issue_representation->getKey() .
            '/worklog?' .
            http_build_query(['startAt' => $start_at]);
    }
}
