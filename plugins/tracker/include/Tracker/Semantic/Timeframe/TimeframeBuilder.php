<?php
/**
 * Copyright Enalean (c) 2019 - Present. All rights reserved.
 *
 * Tuleap and Enalean names and logos are registrated trademarks owned by
 * Enalean SAS. All other trademarks or names are properties of their respective
 * owners.
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

namespace Tuleap\Tracker\Semantic\Timeframe;

use PFUser;
use TimePeriodWithoutWeekEnd;
use Tracker_Artifact;
use Tracker_Artifact_ChangesetValue_Date;
use Tracker_Artifact_ChangesetValue_Numeric;
use Tracker_FormElement_Chart_Field_Exception;
use Tracker_FormElement_Field_Date;
use Tracker_FormElementFactory;

class TimeframeBuilder
{
    private const START_DATE_FIELD_NAME = 'start_date';
    private const DURATION_FIELD_NAME   = 'duration';

    /**
     * @var Tracker_FormElementFactory
     */
    private $formelement_factory;

    public function __construct(Tracker_FormElementFactory $formelement_factory)
    {
        $this->formelement_factory = $formelement_factory;
    }

    public function buildTimePeriodWithoutWeekendForArtifact(Tracker_Artifact $artifact, PFUser $user) : TimePeriodWithoutWeekEnd
    {
        try {
            $start_date = $this->getTimestamp($user, $artifact);
        } catch (TimeframeFieldNotFoundException | TimeframeFieldNoValueException $exception) {
            $start_date = 0;
        }

        try {
            $duration = $this->getDurationFieldValue($user, $artifact);
        } catch (TimeframeFieldNotFoundException | TimeframeFieldNoValueException $exception) {
            $duration = 0;
        }

        return new TimePeriodWithoutWeekEnd($start_date, $duration);
    }

    public function buildTimePeriodWithoutWeekendForArtifactForREST(Tracker_Artifact $artifact, PFUser $user) : TimePeriodWithoutWeekEnd
    {
        try {
            $start_date = $this->getTimestamp($user, $artifact);
        } catch (TimeframeFieldNotFoundException | TimeframeFieldNoValueException $exception) {
            $start_date = null;
        }

        try {
            $duration = $this->getDurationFieldValue($user, $artifact);
        } catch (TimeframeFieldNotFoundException | TimeframeFieldNoValueException $exception) {
            $duration = null;
        }

        return new TimePeriodWithoutWeekEnd($start_date, $duration);
    }

    /**
     * @throws Tracker_FormElement_Chart_Field_Exception
     */
    public function buildTimePeriodWithoutWeekendForArtifactChartRendering(Tracker_Artifact $artifact, PFUser $user) : TimePeriodWithoutWeekEnd
    {
        try {
            $start_date = $this->getTimestamp($user, $artifact);

            if (! $start_date) {
                throw new Tracker_FormElement_Chart_Field_Exception(
                    $GLOBALS['Language']->getText('plugin_tracker', 'burndown_empty_start_date_warning')
                );
            }
        } catch (TimeframeFieldNotFoundException $exception) {
            throw new Tracker_FormElement_Chart_Field_Exception(
                $GLOBALS['Language']->getText('plugin_tracker', 'burndown_missing_start_date_warning')
            );
        } catch (TimeframeFieldNoValueException $exception) {
            $start_date = null;
        }

        try {
            $duration = $this->getDurationFieldValue($user, $artifact);

            if ($duration === null) {
                throw new Tracker_FormElement_Chart_Field_Exception(
                    $GLOBALS['Language']->getText('plugin_tracker', 'burndown_empty_duration_warning')
                );
            }

            if ($duration <= 0) {
                throw new Tracker_FormElement_Chart_Field_Exception(
                    $GLOBALS['Language']->getText('plugin_tracker', 'burndown_empty_duration_warning')
                );
            }

            if ($duration === 1) {
                throw new Tracker_FormElement_Chart_Field_Exception(
                    $GLOBALS['Language']->getText('plugin_tracker', 'burndown_duration_too_short')
                );
            }
        } catch (TimeframeFieldNotFoundException $exception) {
            throw new Tracker_FormElement_Chart_Field_Exception(
                $GLOBALS['Language']->getText('plugin_tracker', 'burndown_missing_duration_warning')
            );
        } catch (TimeframeFieldNoValueException $exception) {
            throw new Tracker_FormElement_Chart_Field_Exception(
                $GLOBALS['Language']->getText('plugin_tracker', 'burndown_empty_duration_warning')
            );
        }

        return new TimePeriodWithoutWeekEnd($start_date, $duration);
    }

    /**
     * @throws TimeframeFieldNotFoundException
     * @throws TimeframeFieldNoValueException
     */
    private function getTimestamp(PFUser $user, Tracker_Artifact $artifact) : int
    {
        $field = $this->formelement_factory->getDateFieldByNameForUser(
            $artifact->getTracker(),
            $user,
            self::START_DATE_FIELD_NAME
        );

        if ($field === null) {
            throw new TimeframeFieldNotFoundException();
        }

        assert($field instanceof Tracker_FormElement_Field_Date);

        $value = $field->getLastChangesetValue($artifact);
        if ($value === null) {
            throw new TimeframeFieldNoValueException();
        }

        assert($value instanceof Tracker_Artifact_ChangesetValue_Date);

        return (int) $value->getTimestamp();
    }

    /**
     * @throws TimeframeFieldNotFoundException
     * @throws TimeframeFieldNoValueException
     */
    private function getDurationFieldValue(PFUser $user, Tracker_Artifact $milestone_artifact)
    {
        $field = $this->formelement_factory->getNumericFieldByNameForUser(
            $milestone_artifact->getTracker(),
            $user,
            self::DURATION_FIELD_NAME
        );

        if ($field === null) {
            throw new TimeframeFieldNotFoundException();
        }

        $last_changeset_value = $field->getLastChangesetValue($milestone_artifact);
        if ($last_changeset_value === null) {
            throw new TimeframeFieldNoValueException();
        }

        assert($last_changeset_value instanceof Tracker_Artifact_ChangesetValue_Numeric);

        return $last_changeset_value->getNumeric();
    }
}
