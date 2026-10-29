<?php
/**
 * @copyright Copyright (c) 2020-2024 VIDEOATENCION
 *
 * @author Kenneth Peiruza <kenneth@videoatencion.com>
 * @author Letizia Benítez <letizia@videoatencion.com>
 * @author Daniel Tamajon <dtamajon@litesolutions.es>
 * @author Marta González <marta.gonzalez@litesolutions.es>
 * @author Jaume Esteban <jaume.escu@gmail.com>
 * @author Ivan Reyné <ivanreyne@gmail.com>
 *
 * @license GNU AGPL version 3 or any later version
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as
 * published by the Free Software Foundation, either version 3 of the
 * License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with this program. If not, see <http://www.gnu.org/licenses/>.
 */

namespace OCA\VTramit\Db;

use JsonSerializable;
use OCP\AppFramework\Db\Entity;

class Topic extends Entity implements JsonSerializable {
    protected $name;
    protected $minRequiredDocs;
    protected $videoPreservationTime;
    protected $helpUrl;
    protected $helpTarget;

    /**
     * Specify data which should be serialized to JSON
     *
     * @return array
     *
     * @OA\Schema(
     *     schema="Topic",
     *     type="object",
     *     title="Topic model",
     *     @OA\Property(
     *         property="id",
     *         type="integer",
     *         format="int32"
     *     ),
     *     @OA\Property(
     *         property="name",
     *         type="string"
     *     ),
     *     @OA\Property(
     *         property="min_required_docs",
     *         type="integer",
     *         format="int32"
     *     ),
     *     @OA\Property(
     *         property="video_preservation_time",
     *         type="integer",
     *         format="int32"
     *     ),
     *     @OA\Property(
     *         property="help_url",
     *         type="string"
     *     ),
     *     @OA\Property(
     *         property="target",
     *         type="string"
     *     )
     * )
     */
    public function jsonSerialize(): array {
        return [
            'id'                    => (int) $this->id,
            'name'                  => $this->name,
            'minRequiredDocs'       => (int) $this->minRequiredDocs,
            'videoPreservationTime' => (int) $this->videoPreservationTime,
            'helpUrl'               => $this->helpUrl,
            'helpTarget'            => $this->helpTarget,
        ];
    }
}
