<?php
/**
 * Copyright (c) Enalean, 2017 - Present. All Rights Reserved.
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

namespace Tuleap\Tracker\Admin\GlobalAdmin\ArtifactLinks;

use CSRFSynchronizerToken;
use EventManager;
use Feedback;
use Project;
use ProjectManager;
use TemplateRendererFactory;
use Tracker_FormElement_Field_ArtifactLink;
use TrackerManager;
use Tuleap\Layout\BaseLayout;
use Tuleap\Layout\IncludeAssets;
use Tuleap\Layout\JavascriptAsset;
use Tuleap\Request\DispatchableWithBurningParrot;
use Tuleap\Request\DispatchableWithProject;
use Tuleap\Request\DispatchableWithRequest;
use Tuleap\Request\ForbiddenException;
use Tuleap\Tracker\Admin\ArtifactLinksUsageDao;
use Tuleap\Tracker\Admin\ArtifactLinksUsageUpdater;
use Tuleap\Tracker\Admin\GlobalAdmin\GlobalAdminPermissionsChecker;
use Tuleap\Tracker\Events\ArtifactLinkTypeCanBeUnused;
use Tuleap\Tracker\FormElement\Field\ArtifactLink\Nature\NaturePresenter;
use Tuleap\Tracker\FormElement\Field\ArtifactLink\Nature\NaturePresenterFactory;
use Tuleap\Tracker\Hierarchy\HierarchyDAO;

class ArtifactLinksController implements DispatchableWithRequest, DispatchableWithBurningParrot, DispatchableWithProject
{
    public const URL = 'artifact-links';

    /**
     * @var ArtifactLinksUsageDao
     */
    private $dao;

    /**
     * @var ArtifactLinksUsageUpdater
     */
    private $updater;

    /**
     * @var NaturePresenterFactory
     */
    private $types_presenter_factory;

    /**
     * @var HierarchyDAO
     */
    private $hierarchy_dao;

    /**
     * @var EventManager
     */
    private $event_manager;
    /**
     * @var ProjectManager
     */
    private $project_manager;
    /**
     * @var TrackerManager
     */
    private $tracker_manager;
    /**
     * @var GlobalAdminPermissionsChecker
     */
    private $permissions_checker;

    public function __construct(
        ProjectManager $project_manager,
        TrackerManager $tracker_manager,
        GlobalAdminPermissionsChecker $permissions_checker,
        ArtifactLinksUsageDao $dao,
        ArtifactLinksUsageUpdater $updater,
        NaturePresenterFactory $types_presenter_factory,
        HierarchyDAO $hierarchy_dao,
        EventManager $event_manager
    ) {
        $this->dao                     = $dao;
        $this->updater                 = $updater;
        $this->types_presenter_factory = $types_presenter_factory;
        $this->hierarchy_dao           = $hierarchy_dao;
        $this->event_manager           = $event_manager;
        $this->project_manager         = $project_manager;
        $this->tracker_manager         = $tracker_manager;
        $this->permissions_checker     = $permissions_checker;
    }

    public function process(\HTTPRequest $request, BaseLayout $layout, array $variables)
    {
        $project = $this->getProject($variables);
        if (! $this->permissions_checker->doesUserHaveTrackerGlobalAdminRightsOnProject($project, $request->getCurrentUser())) {
            throw new ForbiddenException();
        }
        switch ($request->get('func')) {
            case 'edit-artifact-links':
                $this->updateGlobalAdministration($layout, $project);
                $layout->redirect(self::getURL($project));
                break;
            case 'use-artifact-link-type':
                $type_shortname = (string) $request->get('type-shortname');
                $this->updateArtifactLinkUsage($project, $type_shortname);
                $GLOBALS['Response']->redirect(self::getURL($project));
                break;
            case 'artifact-links':
            default:
                $this->displayGlobalAdministration($project, $layout);
                break;
        }
    }

    private function displayGlobalAdministration(Project $project, BaseLayout $response): void
    {
        $toolbar     = [];
        $params      = [];
        $breadcrumbs = [];

        $response->addJavascriptAsset(
            new JavascriptAsset(
                new IncludeAssets(__DIR__ . '/../../../../../../../src/www/assets/trackers', '/assets/trackers'),
                'global-admin-artifact-links.js'
            )
        );
        $this->tracker_manager->displayHeader(
            $project,
            dgettext('tuleap-tracker', 'Trackers'),
            $breadcrumbs,
            $toolbar,
            $params
        );

        $formatted_types = $this->buildFormattedTypes($project);

        $renderer  = TemplateRendererFactory::build()->getRenderer(TRACKER_TEMPLATE_DIR);
        $presenter = new ArtifactLinksPresenter(
            $project,
            $this->getCSRF($project),
            $this->dao->isProjectUsingArtifactLinkTypes((int) $project->getID()),
            $formatted_types,
            $this->hasAtLeastOneDisabledType($formatted_types)
        );

        $renderer->renderToPage(
            'global-admin/artifact-links',
            $presenter
        );

        $this->tracker_manager->displayFooter($project);
    }

    private function updateGlobalAdministration(BaseLayout $layout, Project $project): void
    {
        $this->getCSRF($project)->check();
        $layout->addFeedback(
            Feedback::INFO,
            dgettext('tuleap-tracker', 'Artifact link types has been enabled.')
        );
        $this->updater->forceUsageOfArtifactLinkTypes($project);
    }

    private function updateArtifactLinkUsage(Project $project, string $type_shortname): void
    {
        $type_presenter = $this->types_presenter_factory->getFromShortname($type_shortname);

        if (! $type_presenter) {
            $GLOBALS['Response']->addFeedback(
                Feedback::ERROR,
                dgettext('tuleap-tracker', 'The artifact link type does not exist')
            );

            return;
        }

        if ($this->dao->isTypeDisabledInProject((int) $project->getID(), $type_shortname)) {
            $GLOBALS['Response']->addFeedback(
                Feedback::INFO,
                sprintf(
                    dgettext('tuleap-tracker', 'The artifact link type "%s" is now enabled'),
                    $type_shortname
                )
            );

            $this->dao->enableTypeInProject((int) $project->getID(), $type_shortname);

            return;
        }

        if ($this->artifactLinkTypeCanBeUnused($project, $type_presenter)) {
            $GLOBALS['Response']->addFeedback(
                Feedback::INFO,
                sprintf(
                    dgettext('tuleap-tracker', 'The artifact link type "%s" is now disabled'),
                    $type_shortname
                )
            );

            $this->dao->disableTypeInProject((int) $project->getID(), $type_shortname);

            return;
        }

        $GLOBALS['Response']->addFeedback(
            Feedback::ERROR,
            sprintf(
                dgettext('tuleap-tracker', 'The artifact link type "%s" cannot be disabled'),
                $type_shortname
            )
        );
    }

    public static function getURL(Project $project): string
    {
        return \Tracker::getTrackerGlobalAdministrationURL($project) . '/' . self::URL;
    }

    /**
     * @return array
     */
    private function buildFormattedTypes(Project $project): array
    {
        $formatted_types = [];
        foreach ($this->types_presenter_factory->getAllTypesEditableInProject($project) as $type) {
            $formatted_type = [
                'shortname'     => $type->shortname,
                'forward_label' => $type->forward_label,
                'reverse_label' => $type->reverse_label,
                'is_used'       => ! $this->isTypeDisabledInProject($project, $type),
                'can_be_unused' => $this->artifactLinkTypeCanBeUnused($project, $type)
            ];

            $formatted_types[] = $formatted_type;
        }

        return $formatted_types;
    }

    private function hasAtLeastOneDisabledType(array $formatted_types): bool
    {
        foreach ($formatted_types as $type) {
            if (! $type['is_used']) {
                return true;
            }
        }

        return false;
    }

    private function isTypeDisabledInProject(Project $project, NaturePresenter $type): bool
    {
        return $this->dao->isTypeDisabledInProject((int) $project->getID(), $type->shortname);
    }

    private function artifactLinkTypeCanBeUnused(Project $project, NaturePresenter $type): bool
    {
        if ($type->shortname === Tracker_FormElement_Field_ArtifactLink::NATURE_IS_CHILD) {
            return false;
        }

        $event = new ArtifactLinkTypeCanBeUnused($project, $type);
        $this->event_manager->processEvent($event);

        if ($event->doesPluginCheckedTheType()) {
            return $event->canTypeBeUnused();
        }

        if (! $type->is_system) {
            return true;
        }

        return false;
    }

    private function getCSRF(Project $project): CSRFSynchronizerToken
    {
        return new CSRFSynchronizerToken(self::getURL($project));
    }

    public function getProject(array $variables): Project
    {
        return $this->project_manager->getProject($variables['id']);
    }
}
