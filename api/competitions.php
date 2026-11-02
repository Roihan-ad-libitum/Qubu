<?php
/**
 * Endpoint JSON API untuk Live Search, Filter Bidang, dan Detail Lomba Async
 * Mendukung query params:
 * - ?q={keyword}&bidang={bidang}&page={page}&limit={limit}
 * - ?id={id_lomba}
 * Output JSON: { "success": true, "total": N, "data": [...] }
 */

require_once __DIR__ . '/../config/app.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../core/Validator.php';
require_once __DIR__ . '/../app/Controllers/CompetitionController.php';

$controller = new CompetitionController();
$controller->apiCompetitions($_GET);

