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

class Tables {
  public const MAIL_TEMPLATE                = "vtmail_template";
  public const MAIL_QUEUE                   = "vtmail_queue";
  public const MESSAGE_TEMPLATE             = "vtmsg_template";
  public const MESSAGE_QUEUE                = "vtmsg_queue";
  public const PROCEDURE_TYPE               = "vtproc_type";
  public const PROCEDURE_TYPE_GROUP         = "vtproc_type_group";
  public const PROCEDURE_TYPE_STATUS        = "vtproc_type_status";
  public const PROCEDURE_TYPE_STATUS_GROUP  = "vtproc_type_status_group";
  public const PROCEDURE_TYPE_PROTOCOL_STEP = "vtproc_type_protocol_step";
  public const PROCEDURE                    = "vtproc";
  public const PROCEDURE_PROTOCOL_LOG       = "vtproc_protocol_log";
  public const PROCEDURE_STAKEHOLDER        = "vtproc_stakeholder";
  public const VIDEOCONFERENCE              = "vtproc_videoconference";
  public const RGPD_OPTION                  = "vtrgpd_option";
  public const RGPD_ACTIVITY                = "vtrgpd_activity";
  public const SIGNATURE                    = "vtsign";
  public const SIGNATURE_FILE               = "vtsign_file";
  public const SIGNATURE_TYPE               = "vtsign_type";
  public const SIGNATURE_BUTTONS            = "vtsign_buttons";
  public const TOPIC                        = "vttopic";
  public const WEBHOOK                      = "vtwebhook";
  public const VIDEOCALL_TEST               = "vtvc_test";

}