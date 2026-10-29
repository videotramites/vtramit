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
?>

<?php
/** @var \OCP\IL10N $l */
script('vtramit', 'publicPortal');
?>

<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@mdi/font@5.9.55/css/materialdesignicons.min.css">
<link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Roboto:100,300,400,500,700,900">

<!-- Passing a php data structure to the javascript side of the application -->
<script type="application/json" name="server-data">
<?php echo json_encode($data) ?>
</script>

<div id="q-publicPortal" class="public-portal">
</div>
<?php

// Optional in-page snippet. Resolve relative to this template file so it
// works whether vtramit lives under apps/ or custom_apps/ (the original
// hard-coded relative path "apps/vtramit/..." silently no-oped under
// custom_apps).
$_vtramit_publicPortalSnippet = __DIR__ . '/publicPortal-snippet.php';
if (file_exists($_vtramit_publicPortalSnippet)) {
	include $_vtramit_publicPortalSnippet;
}
unset($_vtramit_publicPortalSnippet);
?>
