<?php
/**
 * Copyright (c) Enalean, 2018 - Present. All Rights Reserved.
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

namespace Tuleap\GraphOnTrackersV5\Async;

use GraphOnTrackersV5_Chart;
use GraphOnTrackersV5_ChartFactory;
use Laminas\HttpHandlerRunner\Emitter\SapiEmitter;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use Tracker_Report;
use Tracker_Report_RendererFactory;
use Tracker_ReportFactory;
use Tuleap\GraphOnTrackersV5\DataTransformation\ChartFieldNotFoundException;
use Tuleap\Http\HTTPFactoryBuilder;
use Tuleap\Http\Response\JSONResponseBuilder;
use Tuleap\Http\Server\NullServerRequest;
use Tuleap\Test\Builders\UserTestBuilder;

final class ChartDataControllerTest extends \Tuleap\Test\PHPUnit\TestCase
{
    use MockeryPHPUnitIntegration;

    /**
     * @var \Mockery\LegacyMockInterface|\Mockery\MockInterface|Tracker_ReportFactory
     */
    private $report_factory;
    /**
     * @var \Mockery\LegacyMockInterface|\Mockery\MockInterface|Tracker_Report_RendererFactory
     */
    private $renderer_factory;
    /**
     * @var GraphOnTrackersV5_ChartFactory|\Mockery\LegacyMockInterface|\Mockery\MockInterface
     */
    private $chart_factory;
    /**
     * @var ChartDataController
     */
    private $chart_data_controller;

    protected function setUp(): void
    {
        $this->report_factory   = \Mockery::mock(Tracker_ReportFactory::class);
        $this->renderer_factory = \Mockery::mock(Tracker_Report_RendererFactory::class);
        $this->chart_factory    = \Mockery::mock(GraphOnTrackersV5_ChartFactory::class);
        $user_manager           = \Mockery::mock(\UserManager::class);
        $user_manager->shouldReceive('getCurrentUser')->andReturn(UserTestBuilder::aUser()->build());
        $this->chart_data_controller = new ChartDataController(
            $this->report_factory,
            $this->renderer_factory,
            $this->chart_factory,
            $user_manager,
            new JSONResponseBuilder(HTTPFactoryBuilder::responseFactory(), HTTPFactoryBuilder::streamFactory()),
            new SapiEmitter()
        );
    }

    public function testAccessingAChartWithANonAccessibleFieldGivesA404JSONError(): void
    {
        $request = (new NullServerRequest())
            ->withAttribute('report_id', '123')
            ->withAttribute('renderer_id', '456')
            ->withAttribute('chart_id', '789');

        $this->report_factory->shouldReceive('getReportById')->andReturn(\Mockery::mock(Tracker_Report::class));
        $this->renderer_factory->shouldReceive('getReportRendererByReportAndId')->andReturn(\Mockery::mock(Tracker_Report::class));
        $chart = \Mockery::mock(GraphOnTrackersV5_Chart::class);
        $chart->shouldReceive('fetchAsArray')->andThrow(new ChartFieldNotFoundException('Foo'));
        $this->chart_factory->shouldReceive('getChart')->andReturn($chart);

        $response = $this->chart_data_controller->handle($request);

        self::assertEquals(404, $response->getStatusCode());
        self::assertJson($response->getBody()->getContents());
    }
}
