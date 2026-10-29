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

use OCA\VTramit\Service\ConfigService;

class ResponseInfo implements JsonSerializable {
    public const RESPONSE_CODE_OK = 'OK';
    public const RESPONSE_CODE_ERROR = 'ERROR';
    public const RESPONSE_CODE_EXCEPTION = 'EXCEPTION';

    public const RESPONSE_INFO_RESULT = 'result';
    public const RESPONSE_INFO_MESSAGE = 'message';

    private $info = [];

    public function __construct() {
        $this->setInfo(self::RESPONSE_INFO_RESULT, self::RESPONSE_CODE_OK);
    }

    /**
     * Specify data which should be serialized to JSON
     *
     * @link http://php.net/manual/en/jsonserializable.jsonserialize.php
     * @return mixed data which can be serialized by <b>json_encode</b>,
     * which is a value of any type other than a resource.
     * @since 5.4.0
     */
    public function jsonSerialize(): array {
        if (!empty($this->info['entity']) && is_array($this->info['entity']) && empty($this->info['list'])) {
            $this->info['list'] = $this->info['entity'];
            unset($this->info['entity']);
        }

        return $this->info;
    }

    /**
     * Set the response result to 'OK'. The response result is stored associated to
     * the code 'result'.
     *
     * @param string $message  message to save with the response result
     */
    public function setOk(string $message = null) {
        $this->setInfo(self::RESPONSE_INFO_RESULT, self::RESPONSE_CODE_OK);

        if (!is_null($message)) {
            $this->setMessage($message);
        }
    }

    /**
     * Set the response result to 'ERROR'. The response result is stored associated to
     * the code 'result'.
     *
     * @param string $message  message to save with the response result
     */
    public function setError(string $message = null) {
      $this->setInfo(self::RESPONSE_INFO_RESULT, self::RESPONSE_CODE_ERROR);

      if (!is_null($message)) {
        $this->setMessage($message);
      }
    }

    /**
     * Set the response result to 'ERROR'. The response result is stored associated to
     * the code 'result'.
     *
     * @param string $message  message to add
     */
    public function addError(string $message = null) {
      $this->setInfo(self::RESPONSE_INFO_RESULT, self::RESPONSE_CODE_ERROR);

      if (!is_null($message)) {
        $this->addMessage($message);
      }
    }

    /**
     * Indicates wether the current response result is 'OK'.
     *
     * @return bool  true if current response result is 'OK'; otherwise, false
     */
    public function isOk() {
        return $this->info[self::RESPONSE_INFO_RESULT] == self::RESPONSE_CODE_OK;
    }

    /**
     * Indicates wether the current response result is 'ERROR'.
     *
     * @return bool  true if current response result is 'ERROR'; otherwise, false
     */
    public function isError() {
        return $this->info[self::RESPONSE_INFO_RESULT] == self::RESPONSE_CODE_ERROR;
    }

    /**
     * Set the response result to 'EXCEPTION'. The response result is stored associated to
     * the code 'result'.
     *
     * @param $message string  message to save with the response result
     */
    public function setException(string $message = null) {
      $this->setInfo(self::RESPONSE_INFO_RESULT, self::RESPONSE_CODE_EXCEPTION);

      if (!is_null($message)) {
        $this->setMessage($message);
      }
    }

    /**
     * Set a response message, replacing previous messages. This message is stored
     * associated to the code 'message'.
     *
     * @param $message string  message to set
     */
    public function setMessage(string $message) {
        $this->setInfo(self::RESPONSE_INFO_MESSAGE, $message);
    }

    /**
     * Add a new message to the response. This message is stored associated to the
     * code 'message'.
     *
     * @param $message string  message to add
     */
    public function addMessage(string $message) {
        $this->addInfo(self::RESPONSE_INFO_MESSAGE, $message);
    }

    /**
     * Add a new message to the response. This message is stored associated to the
     * code 'message'.
     *
     * @param $message string  message to add
     */
    public function addMessages($messageList) {
        if (is_array($messageList)) {
            foreach($messageList as $message) {
                $this->addInfo(self::RESPONSE_INFO_MESSAGE, $message);
            }
        }
        else {
            $this->addInfo(self::RESPONSE_INFO_MESSAGE, $messageList);
        }
    }

    /**
     * Get the current message to the response. This message is stored associated to the
     * code 'message'.
     *
     * @return string  current response message
     */
    public function getMessage(): string {
        return $this->getInfo(self::RESPONSE_INFO_MESSAGE);
    }

    /**
     * Add a value identified by given code into the info object.
     *
     * @param $code  string                        identifier in the value
     * @param $value string|int|float|bool|array   value to store
     */
    public function setInfo(string $code, $value) {
        $this->info[$code] = $value;
    }

    /**
     * Add a value to an array identified by given code into the info object.
     *
     * @param $code  string                       identifier in the array
     * @param $value string|int|float|bool|array  value to store
     */
    public function addInfo(string $code, $value) {
        // Avoid to process null values
        if (is_null($value)) {
            return;
        }

        if (!isset($this->info[$code])) {
            $this->info[$code] = [];
        }

        if (isset($this->info[$code]) && !is_array($this->info[$code])) {
            $old_value = $this->info[$code];
            $this->info[$code] = [];
            $this->info[$code][] = $old_value;
        }

        // Avoid duplicated messages
        if ($code == self::RESPONSE_INFO_MESSAGE) {
            if ((
                is_array($this->info[$code])
                && !in_array($value, $this->info[$code])
                ) || (
                !is_array($this->info[$code])
                && $this->info[$code] != $value
                )
            ) {
                $this->info[$code][] = $value;
            }
        }
        else {
            $this->info[$code][] = $value;
        }
    }

    /**
     * Get the value associated to the given code information.
     *
     * @param string $code  the entry code to remove
     */
    public function getInfo(string $code) {
        if (isset($this->info[$code])) {
            return $this->info[$code];
        }

        return null;
    }

    /**
     * Delete the given code information.
     *
     * @param string $code  the entry code to remove
     */
    public function deleteInfo(string $code) {
        if (isset($this->info[$code])) {
            unset($this->info[$code]);
        }
    }

    /**
     * Obtains the array with all the response info stored.
     */
    public function getData() {
        return $this->info;
    }

    /**
     * Merge the new info with the current one.
     *
     * @param ResponseInfo $responseInfo  object to merge
     */
    public function mergeResponseInfo(ResponseInfo $responseInfo) {
        $newInfo = $responseInfo->getData();
        return $this->mergeResponseInfoData($newInfo);
    }

    /**
     * Merge the new info with the current one.
     *
     * @param ResponseInfo $responseInfo  object to merge
     */
    public function mergeResponseInfoData(array $newInfo) {
        // Hold the major issue level
        if ($this->info[self::RESPONSE_INFO_RESULT] != $newInfo[self::RESPONSE_INFO_RESULT]) {
            $newResult = self::RESPONSE_CODE_OK;

            if ($this->info[self::RESPONSE_INFO_RESULT] == self::RESPONSE_CODE_EXCEPTION || $newInfo[self::RESPONSE_INFO_RESULT] == self::RESPONSE_CODE_EXCEPTION) {
                $newResult = self::RESPONSE_CODE_EXCEPTION;
            }
            else if ($this->info[self::RESPONSE_INFO_RESULT] == self::RESPONSE_CODE_ERROR || $newInfo[self::RESPONSE_INFO_RESULT] == self::RESPONSE_CODE_ERROR) {
                $newResult = self::RESPONSE_CODE_ERROR;
            }

            $this->info[self::RESPONSE_INFO_RESULT] = $newResult;
            unset($newInfo[self::RESPONSE_INFO_RESULT]);
        }

        if (isset($this->info['entity']) && isset($newInfo->info['entity'])) {
            $this->addInfo('entity', $newInfo->info['entity']);
            unset($newInfo->info['entity']);
        }

        if (!empty($newInfo[self::RESPONSE_INFO_MESSAGE])) {
            $messages = is_array($newInfo[self::RESPONSE_INFO_MESSAGE]) ? $newInfo[self::RESPONSE_INFO_MESSAGE] : [$newInfo[self::RESPONSE_INFO_MESSAGE]];
            foreach($messages as $message) {
                $this->addMessage($message);
            }

            unset($newInfo->info[self::RESPONSE_INFO_MESSAGE]);
        }

        // Merge any other info
        $this->info = $this->info + $newInfo;
    }
}