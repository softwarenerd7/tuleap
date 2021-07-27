/*
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

import type { ArtifactFieldValue, ArtifactReportResponse } from "../type";

export function createArtifactValuesCollection(
    report_artifacts: ArtifactReportResponse[]
): Array<ArtifactFieldValue> {
    const artifact_data = [];
    for (const artifact of report_artifacts) {
        const fields_content = [];
        for (const value of artifact.values) {
            if (value.type === "aid") {
                fields_content.push({
                    field_name: value.label,
                    field_value: value.value,
                });
            }
        }
        artifact_data.push(...fields_content);
    }

    return artifact_data;
}
