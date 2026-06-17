<?php
/**
 * Seed lab owner, consulting doctors, equipment details, and lab process images.
 * Safe to re-run: php tools/seed_team_equipment.php
 */
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/cms_team_seed.php';

$db->beginTransaction();

try {
    cmsApplyTeamEquipmentSeed($db);
    dbReplaceSetting($db, 'team_content_version', '2');
    $db->commit();
    echo "Team & equipment seed completed.\n";
    echo "Owner: images/team/akshay-rakh-owner.png\n";
    echo "Doctors: Dr. Shubham Shirke, Dr. Akash Trimbake\n";
    echo 'Equipment rows: ' . $db->query('SELECT COUNT(*) FROM cms_equipment')->fetchColumn() . "\n";
} catch (Throwable $e) {
    $db->rollBack();
    fwrite(STDERR, 'Failed: ' . $e->getMessage() . PHP_EOL);
    exit(1);
}
