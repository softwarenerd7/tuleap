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

namespace Tuleap\Timetracking\XML;

use Mockery;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use PFUser;
use SimpleXMLElement;
use Tracker;
use Tracker_ArtifactFactory;
use Tuleap\Timetracking\Admin\TimetrackingEnabler;
use Tuleap\Timetracking\Admin\TimetrackingUgroupRetriever;
use Tuleap\Timetracking\Time\Time;
use Tuleap\Timetracking\Time\TimeRetriever;
use Tuleap\Tracker\Artifact\Artifact;
use UserManager;
use UserXMLExportedCollection;
use UserXMLExporter;
use XML_RNGValidator;
use XML_SimpleXMLCDATAFactory;

final class XMLExportTest extends \Tuleap\Test\PHPUnit\TestCase
{
    use MockeryPHPUnitIntegration;

    /**
     * @var XMLExport
     */
    private $export;

    /**
     * @var Mockery\LegacyMockInterface|Mockery\MockInterface|TimetrackingEnabler
     */
    private $timetracking_enabler;

    /**
     * @var Mockery\LegacyMockInterface|Mockery\MockInterface|TimetrackingUgroupRetriever
     */
    private $timetracking_ugroup_retriever;

    /**
     * @var Mockery\LegacyMockInterface|Mockery\MockInterface|Tracker_ArtifactFactory
     */
    private $artifact_factory;

    /**
     * @var Mockery\LegacyMockInterface|Mockery\MockInterface|TimeRetriever
     */
    private $time_retriever;

    /**
     * @var UserXMLExporter
     */
    private $user_xml_exporter;

    /**
     * @var Mockery\LegacyMockInterface|Mockery\MockInterface|UserManager
     */
    private $user_manager;

    /**
     * @var Mockery\LegacyMockInterface|Mockery\MockInterface|PFUser
     */
    private $user;

    protected function setUp(): void
    {
        parent::setUp(); // TODO: Change the autogenerated stub

        $this->user_manager                  = Mockery::mock(UserManager::class);
        $this->timetracking_enabler          = Mockery::mock(TimetrackingEnabler::class);
        $this->timetracking_ugroup_retriever = Mockery::mock(TimetrackingUgroupRetriever::class);
        $this->artifact_factory              = Mockery::mock(Tracker_ArtifactFactory::class);
        $this->time_retriever                = Mockery::mock(TimeRetriever::class);
        $this->user_xml_exporter             = new UserXMLExporter(
            $this->user_manager,
            new UserXMLExportedCollection(new XML_RNGValidator(), new XML_SimpleXMLCDATAFactory())
        );

        $this->export = new XMLExport(
            $this->timetracking_enabler,
            $this->timetracking_ugroup_retriever,
            $this->artifact_factory,
            $this->time_retriever,
            $this->user_xml_exporter,
            $this->user_manager
        );

        $this->user = Mockery::mock(PFUser::class);
    }

    public function testItExportsTimetrackingInXML(): void
    {
        $exported_tracker  = Mockery::mock(Tracker::class)->shouldReceive('getId')->andReturn(789)->getMock();
        $exported_trackers = [
            'T789' => $exported_tracker
        ];

        $xml = new SimpleXMLElement(
            <<<EOS
            <?xml version="1.0" encoding="UTF-8"?>
            <project>
                <trackers>
                    <tracker id="T789">
                    </tracker>
                </trackers>
            </project>
            EOS
        );

        $this->timetracking_enabler->shouldReceive('isTimetrackingEnabledForTracker')
            ->with($exported_tracker)
            ->once()
            ->andReturnTrue();

        $this->timetracking_ugroup_retriever->shouldReceive('getReaderUgroupsForTracker')
            ->once()
            ->with($exported_tracker)
            ->andReturn(
                [new \ProjectUGroup(['ugroup_id' => 4])]
            );

        $this->timetracking_ugroup_retriever->shouldReceive('getWriterUgroupsForTracker')
            ->once()
            ->with($exported_tracker)
            ->andReturn(
                [new \ProjectUGroup(['ugroup_id' => 3])]
            );

        $artifact = Mockery::mock(Artifact::class);
        $this->artifact_factory->shouldReceive('getArtifactsByTrackerId')
            ->with(789)
            ->once()
            ->andReturn([$artifact]);

        $this->time_retriever->shouldReceive('getTimesForUser')
            ->with(
                $this->user,
                $artifact
            )
            ->once()
            ->andReturn([
                new Time(
                    1,
                    104,
                    45,
                    '2020-02-06',
                    600,
                    'Step 01'
                )
            ]);

        $another_user = Mockery::mock(PFUser::class);
        $another_user->shouldReceive('getLdapId')->andReturn(20004);
        $another_user->shouldReceive('getId')->andReturn(104);

        $this->user_manager->shouldReceive('getUserById')
            ->with(104)
            ->once()
            ->andReturn($another_user);

        $this->export->export(
            $xml,
            $this->user,
            $exported_trackers
        );

        $this->assertTrue(isset($xml->trackers->tracker->timetracking));
        $xml_timetracking = $xml->trackers->tracker->timetracking;
        $this->assertSame("1", (string) $xml_timetracking['is_enabled']);

        $this->assertTrue(isset($xml_timetracking->permissions));
        $this->assertTrue(isset($xml_timetracking->permissions->read));
        $this->assertTrue(isset($xml_timetracking->permissions->write));

        $this->assertSame("project_admins", (string) $xml_timetracking->permissions->read->ugroup);
        $this->assertSame("project_members", (string) $xml_timetracking->permissions->write->ugroup);

        $this->assertCount(1, $xml_timetracking->time);
        $this->assertSame("45", (string) $xml_timetracking->time['artifact_id']);
        $this->assertSame("600", (string) $xml_timetracking->time->minutes);
        $this->assertSame("Step 01", (string) $xml_timetracking->time->step);
        $this->assertSame("2020-02-06T00:00:00+01:00", (string) $xml_timetracking->time->day);
        $this->assertSame("20004", (string) $xml_timetracking->time->user);
    }

    public function testItExportsNothingIfTimetrackingIsNotEnabled(): void
    {
        $exported_tracker  = Mockery::mock(Tracker::class)->shouldReceive('getId')->andReturn(789)->getMock();
        $exported_trackers = [
            'T789' => $exported_tracker
        ];

        $xml = new SimpleXMLElement(
            <<<EOS
            <?xml version="1.0" encoding="UTF-8"?>
            <project>
                <trackers>
                    <tracker id="T789">
                    </tracker>
                </trackers>
            </project>
            EOS
        );

        $this->timetracking_enabler->shouldReceive('isTimetrackingEnabledForTracker')
            ->with($exported_tracker)
            ->once()
            ->andReturnFalse();

        $this->timetracking_ugroup_retriever->shouldNotReceive('getReaderUgroupsForTracker');
        $this->timetracking_ugroup_retriever->shouldNotReceive('getWriterUgroupsForTracker');
        $this->artifact_factory->shouldNotReceive('getArtifactsByTrackerId');
        $this->time_retriever->shouldNotReceive('getTimesForUser');
        $this->user_manager->shouldNotReceive('getUserById');

        $this->export->export(
            $xml,
            $this->user,
            $exported_trackers
        );

        $this->assertFalse(isset($xml->trackers->tracker->timetracking));
    }
}
