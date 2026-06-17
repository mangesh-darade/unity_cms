<?php
// Fetch laboratory equipment ordered by sequence
$home_equipment = $db->query("SELECT * FROM cms_equipment ORDER BY sequence ASC")->fetchAll();

function cmsEquipmentLines(string $description): array
{
    $lines = array_filter(array_map('trim', explode("\n", $description)));
    $intro = [];
    $bullets = [];
    foreach ($lines as $line) {
        if (str_starts_with($line, '•') || str_starts_with($line, '-')) {
            $bullets[] = ltrim($line, "•-\t ");
        } else {
            $intro[] = $line;
        }
    }
    return ['intro' => implode(' ', $intro), 'bullets' => $bullets];
}
?>
<!-- Laboratory Equipment Section -->
<section class="equipment-section section-padding">
    <div class="container">
        <?php renderSectionHeader($cms_sections, 'equipment', [
            'tag' => 'Clinical Equipment',
            'title' => 'Our Advanced Clinical Laboratory Infrastructure',
            'desc' => 'Sysmex hematology, Orbit biochemistry, Remi centrifuge, Labomed microscopy, and Leishman staining — operated by qualified ADMLT staff.',
        ]); ?>
        
        <div class="equipment-grid equipment-grid-detailed">
            <?php foreach ($home_equipment as $eq):
                $parsed = cmsEquipmentLines((string) ($eq['description'] ?? ''));
            ?>
                <article class="equipment-card equipment-card-detailed reveal">
                    <div class="equipment-img-wrap">
                        <img src="<?php echo htmlspecialchars($eq['image_path']); ?>"
                             alt="<?php echo htmlspecialchars($eq['title']); ?> — Unity Clinical Laboratory"
                             loading="lazy"
                             width="480"
                             height="320"
                             class="equipment-img team-photo-polish">
                    </div>
                    <div class="equipment-body">
                        <h3><?php echo htmlspecialchars($eq['title']); ?></h3>
                        <?php if ($parsed['intro'] !== ''): ?>
                            <p class="equipment-intro"><?php echo htmlspecialchars($parsed['intro']); ?></p>
                        <?php endif; ?>
                        <?php if (!empty($parsed['bullets'])): ?>
                            <ul class="equipment-spec-list">
                                <?php foreach (array_slice($parsed['bullets'], 0, 4) as $bullet): ?>
                                    <li><?php echo htmlspecialchars($bullet); ?></li>
                                <?php endforeach; ?>
                            </ul>
                        <?php endif; ?>
                    </div>
                </article>
            <?php endforeach; ?>
        </div>
        <div class="text-center section-cta-wrap" style="margin-top:28px;">
            <a href="about.php#equipment" class="btn btn-teal">View Full Equipment Details <i class="fa-solid fa-arrow-right"></i></a>
        </div>
    </div>
</section>
