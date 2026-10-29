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

namespace OCA\VTramit\Exception;

use Closure;

use OCP\AppFramework\Http;
use OCP\AppFramework\Http\DataResponse;
use OCP\AppFramework\Db\DoesNotExistException;
use OCP\AppFramework\Db\MultipleObjectsReturnedException;

use OCA\VTramit\Db\ResponseInfo;
use OCA\VTramit\Helper\Constants;
use OCA\VTramit\Exception\BaseException;
use OCA\VTramit\Exception\DataNotFoundException;
use OCA\VTramit\Exception\FormatNotValidException;
use OCA\VTramit\Exception\InvalidIdException;
use OCA\VTramit\Exception\MailQueueNotFound;
use OCA\VTramit\Exception\SignatureNotFound;

trait ControllerErrors {

    protected function handleExceptions(Closure $callback, $showTrace = false): DataResponse
    {
        try {
            return $callback();
        }
        catch (BaseException $e) {
            return $this->createResponse($e, $e->getHttpStatus(), $showTrace);
        }
        catch (\Swift_RfcComplianceException $e) {
            return $this->createResponse($e, Http::STATUS_INTERNAL_SERVER_ERROR, $showTrace);
        }
        catch (DataNotFoundException $e) {
            return $this->createResponse($e, Http::STATUS_NOT_FOUND, $showTrace);
        }
        catch (DoesNotExistException $e) {
            return $this->createResponse($e, Http::STATUS_NOT_FOUND, $showTrace);
        }
        catch (MultipleObjectsReturnedException $e) {
            return $this->createResponse($e, Http::STATUS_NOT_FOUND, $showTrace);
        }
        catch (\Exception $e) {
            return $this->createResponse($e, Http::STATUS_INTERNAL_SERVER_ERROR, $showTrace);
        }
        catch (\Throwable $e) {
            return $this->createResponse($e, Http::STATUS_INTERNAL_SERVER_ERROR, $showTrace);
        }
    }

    protected function handleErrorSendingMail(Closure $callback): DataResponse
    {
        try {
            return $callback();
        }
        catch (\Swift_RfcComplianceException $e) {
            return $this->createResponse($e, Http::STATUS_INTERNAL_SERVER_ERROR);
        }

    }

    /**
     * Create the response to return on error case.
     *
     * @param \Exception $e    exception to manage
     * @param string $status   the status code to return
     * @param bool $showTrace  indicates if stacktrace should be included in the response or not.
     *                         This boolean is for development purposes and should never be called
     *                         with any value; instead, set to true during development procedure
     *                         in order to catch stackstrace
     * @return DataResponse    the created response
     */
    private function createResponse($e, $status, $showTrace = false) {
        $info = new ResponseInfo();
        $info->setException($e->getMessage());

        if ($showTrace) {
            $info->setInfo('stacktrace', print_r($e->getTrace(), true));
        }

        return new DataResponse($info->getData(), $status);
    }
}
