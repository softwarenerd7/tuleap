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

namespace Tuleap\ProgramManagement\Adapter\Program\Admin\Configuration;

use Tuleap\ProgramManagement\Adapter\Workspace\TrackerProxy;
use Tuleap\ProgramManagement\Domain\Program\Admin\Configuration\ConfigurationErrorsCollector;
use Tuleap\ProgramManagement\Domain\Program\Admin\Configuration\TrackerErrorPresenter;
use Tuleap\ProgramManagement\Domain\Program\Backlog\CreationCheck\ConfigurationErrorsGatherer;
use Tuleap\ProgramManagement\Domain\Program\Plan\RetrievePlannableTrackers;
use Tuleap\ProgramManagement\Domain\Program\ProgramIdentifier;
use Tuleap\ProgramManagement\Domain\ProgramTracker;
use Tuleap\ProgramManagement\Domain\Workspace\UserIdentifier;
use Tuleap\ProgramManagement\Domain\Workspace\VerifyTrackerSemantics;

final class ConfigurationErrorPresenterBuilder
{
    public function __construct(
        private ConfigurationErrorsGatherer $errors_gatherer,
        private RetrievePlannableTrackers $plannable_trackers_retriever,
        private VerifyTrackerSemantics $verify_tracker_semantics,
        private \TrackerFactory $tracker_factory
    ) {
    }

    public function buildProgramIncrementErrorPresenter(
        ProgramTracker $program_increment_tracker,
        ?ProgramIdentifier $program,
        UserIdentifier $user_identifier,
        ConfigurationErrorsCollector $errors_collector
    ): ?TrackerErrorPresenter {
        if (! $program) {
            return null;
        }

        return $this->buildTrackerErrorPresenter($program_increment_tracker, $user_identifier, $errors_collector);
    }

    public function buildIterationErrorPresenter(
        ?ProgramTracker $iteration_tracker,
        UserIdentifier $user_identifier,
        ConfigurationErrorsCollector $errors_collector
    ): ?TrackerErrorPresenter {
        if (! $iteration_tracker) {
            return null;
        }

        return $this->buildTrackerErrorPresenter($iteration_tracker, $user_identifier, $errors_collector);
    }

    private function buildTrackerErrorPresenter(
        ProgramTracker $program_tracker,
        UserIdentifier $user_identifier,
        ConfigurationErrorsCollector $errors_collector
    ): ?TrackerErrorPresenter {
        return TrackerErrorPresenter::fromTracker(
            $this->errors_gatherer,
            $program_tracker,
            $user_identifier,
            $errors_collector
        );
    }

    public function buildPlannableErrorPresenter(
        ProgramIdentifier $program,
        ConfigurationErrorsCollector $plannable_error_collector
    ): ?TrackerErrorPresenter {
        $plannable_tracker = $this->plannable_trackers_retriever->getPlannableTrackersOfProgram($program->getId());
        foreach ($plannable_tracker as $tracker_id) {
            if (! $this->verify_tracker_semantics->hasTitleSemantic($tracker_id)) {
                $tracker = $this->tracker_factory->getTrackerById($tracker_id);
                if (! $tracker) {
                    continue;
                }
                $program_tracker = TrackerProxy::fromTracker($tracker);
                $plannable_error_collector->addSemanticError('Title', 'title', [$program_tracker]);
            }

            if (! $this->verify_tracker_semantics->hasStatusSemantic($tracker_id)) {
                $tracker = $this->tracker_factory->getTrackerById($tracker_id);
                if (! $tracker) {
                    continue;
                }
                $program_tracker = TrackerProxy::fromTracker($tracker);
                $plannable_error_collector->addSemanticError('Status', 'status', [$program_tracker]);
            }
        }

        return TrackerErrorPresenter::fromAlreadyCollectedErrors($plannable_error_collector);
    }
}
