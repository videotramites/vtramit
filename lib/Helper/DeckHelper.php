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

namespace OCA\VTramit\Helper;

use DateTime;

use Psr\Log\LoggerInterface;
use OCP\IL10N;

use OCP\AppFramework\OCS\OCSException;
use OCP\AppFramework\OCS\OCSForbiddenException;
use OCP\AppFramework\OCS\OCSNotFoundException;

use OCA\VTramit\Constants;
use OCA\VTramit\Service\ConfigService;

class DeckHelper {

    /** @var LoggerInterface */
    private $logger;

    /** @var IL10N */
    private $translate;

    /** @var CardController */
    private $cardController;

    /** @var ConfigService */
    private $configService;

    public function __construct(
        LoggerInterface $logger,
        IL10N $translate,
        ConfigService $configService
    ) {
        $this->logger = $logger;
        $this->translate = $translate;
        $this->configService = $configService;
    }

    public function getCardController() {
        if ($this->cardController == null) {
            $app = new \OCP\AppFramework\App('deck');
            $this->cardController = $app->getContainer()->get('OCA\Deck\Controller\CardController');
        }

        return $this->cardController;
    }

    /**
     * Removes the tag we use to mark the relationship between appointments
     * and deck cards.
     *
     * @param string $text  the text where we will search and remove the tag
     * return string        copy of the string without tags
     */
    public static function removeTag($text) {
        $newText = preg_replace('/#deck\(\d+\)/', '', $text);
        $newText = rtrim($newText);

        return $newText;
    }

    /**
     * Adds or updates the tag that represent the relationship between
     * appointments and deck cards.
     *
     * @param string $text  the text where we will search and update the tag
     * @param string $id    the id of the deck card
     * return string        copy of the string with the new relationship tag
     */
    public static function addUpdateTag($text, $id) {
        $newText = preg_replace('/#deck\(\d+\)/', '', $text);
        $newText = rtrim($newText);
        if($newText) {
            $newText .= "\n\n";
        }
        $newText .= "#deck(".$id.")";

        return $newText;
    }

    /**
     * Looks for the tag that represent the relationship between
     * appointments and deck cards and returns the id of the deck card.
     *
     * @param string $text  the text where we will search for the tag
     * return string        the id of the deck card
     */
    public static function getIdFromTag($text) {
        $id = null;
        $matches = [];
        if(preg_match('/#deck\((\d+)\)/', $text, $matches)) {
            $id = (int) $matches[1];
        }

        return $id;
    }


    //
    // Procedure integration
    ////////////////////////////////////////////////////////////////////////////////

    /**
     * Creates a deck card (using directly the php controllers ... not the
     * best solution) that matches the given procedure information.
     *
     * @param Procedure            $procedure    the procedure information
     * @param ProcedureStakeholder $stakeholder  the procedure stakeholder information
     * return string                             the id of the deck card
     */
    public function createFromProcedure($stackId, $procedure, $stakeholder, $description) {
        $cardId = null;

        if (!empty($stackId)) {
            // We store some basic information so the deck card matches
            // the information from the procedure.
            $title = $procedure->getTopic();
            $type = 'plain';
            $order = 999;

            $duedate = $procedure->getFormattedAppointmentDate($this->configService->getDateTimezone(), $this->configService->getDatetimeFormat());

            // First we create the deck card.
            $cardCreate = $this->getCardController()->create($title, $stackId);
            $cardId = $cardCreate->id;

            // Then we update it to pass the extra info the creation
            // method does not have.
            $cardUpdate = $this->getCardController()->update($cardId, $title, $stackId, $type, $order, $description, $duedate, null);
        }

        return $cardId;
    }

    /**
     * Updates a deck card (using directly the php controllers ... not the
     * best solution) that matches the given procedure information.
     *
     * @param Procedure            $procedure    the procedure information
     * @param ProcedureStakeholder $stakeholder  the procedure stakeholder information
     * return string                    the id of the deck card
     */
    public function updateFromProcedure($stackId, $procedure, $stakeholder, $description) {
        if (!empty($stackId)) {
            // We store some basic information so the deck card matches
            // the information from the procedure.
            $title = $procedure->getTopic();
            $type = 'plain';
            $order = 999;

            $duedate = $procedure->getFormattedAppointmentDate($this->configService->getDateTimezone(), $this->configService->getDatetimeFormat());

            $cardId = $stakeholder->getDeckCardId();
            if (!empty($cardId)) {
                // Then we update it to pass the extra info the creation
                // method does not have.
                $cardUpdate = $this->getCardController()->update($cardId, $title, $stackId, $type, $order, $description, $duedate, null);
            }
        }
    }

    /**
     * Deletes a deck card (using directly the php controllers ... not the
     * best solution) that matches the given procedure information.
     *
     * @param Procedure stakeholder    the procedure stakeholder information
     */
    public function deleteFromProcedureStakeholder($stakeholder) {
        $cardId = $stakeholder->getDeckCardId();
        if (!empty($cardId)) {
            $cardUpdate = $this->getCardController()->delete($cardId);
        }
    }
}
