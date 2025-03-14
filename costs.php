<?php
include 'functions.php';
$pdo = pdo_connect_mysql();
$page = isset($_GET['page']) && is_numeric($_GET['page']) ? (int)$_GET['page'] : 1;

$stmt = $pdo->query('SELECT * FROM costs ORDER BY id');
$costs = $stmt->fetchAll(PDO::FETCH_ASSOC);

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id'])) {
    $id = (int)$_POST['id'];
    $cost_value = floatval($_POST['cost_value']);
    $stmt = $pdo->prepare('UPDATE costs SET cost_value = ?, date_updated = NOW() WHERE id = ?');
    $stmt->execute([$cost_value, $id]);
    header('Content-Type: application/json');
    echo json_encode(['success' => true, 'message' => 'Kosten bijgewerkt!', 'cost_value' => $cost_value, 'date_updated' => date('d-m-Y')]);
    exit;
}
?>

<?=template_header('Kosten')?>

<div class="wrapper">
    <!-- Sidebar -->
    <aside class="sidebar">
        <div class="sidebar-header">
            <img src="images/zintuigspel_logo.svg" alt="Zintuigspel Logo" class="sidebar-logo">
            <i class='bx bx-menu toggle-btn'></i>
        </div>
        <ul class="sidebar-nav">
            <li><a href="index.php?page=<?=$page?>" class="<?= basename($_SERVER['PHP_SELF']) == 'index.php' ? 'active' : '' ?>"><i class='bx bx-home'></i><span>Overzicht</span></a></li>
            <li><a href="filaments.php?page=<?=$page?>"><i class='bx bx-sushi'></i><span>Filamenten</span></a></li>
            <li><a href="costs.php?page=<?=$page?>" class="<?= basename($_SERVER['PHP_SELF']) == 'costs.php' ? 'active' : '' ?>"><i class='bx bx-dollar'></i><span>Kosten</span></a></li>
            <li><a href="printer.php?page=<?=$page?>"><i class='bx bx-printer'></i><span>Printer Beheer</span></a></li>
        </ul>
    </aside>

    <!-- Main Content -->
    <div class="main-content">
        <div class="content">
            <h2>Kosten</h2>
            <div class="costs-list">
                <?php
                $current_section = '';
                foreach ($costs as $cost):
                    // Format date_updated to dd-mm-yyyy
                    $formatted_date = date('d-m-Y', strtotime($cost['date_updated']));
                    // Define sections and their sub-items
                    if (in_array($cost['cost_type'], ['Arbeid', 'Energie', 'Printers'])) {
                        $current_section = $cost['cost_type'];
                        ?>
                        <div class="cost-section"><?=$cost['cost_type']?></div>
                        <?php
                    } elseif (
                        ($current_section === 'Arbeid' && in_array($cost['cost_type'], ['Voorbereiding', 'Nabehandeling'])) ||
                        ($current_section === 'Energie' && $cost['cost_type'] === 'Elektriciteit') ||
                        ($current_section === 'Printers' && $cost['cost_type'] === 'Onderhoud')
                    ) {
                        ?>
                        <div class="cost-item">
                            <div class="cost-type"><?=$cost['cost_type']?></div>
                            <div class="cost-details">
                                <form class="inline-update-form" action="costs.php?page=<?=$page?>" method="post">
                                    <input type="hidden" name="id" value="<?=$cost['id']?>">
                                    <span class="cost-unit">€</span>
                                    <input type="number" name="cost_value" class="cost-value" value="<?=$cost['cost_value']?>" step="0.01" data-original-value="<?=$cost['cost_value']?>">
                                    <?php if (in_array($cost['cost_type'], ['Voorbereiding', 'Nabehandeling', 'Onderhoud'])): ?>
                                        <span class="cost-unit-suffix">per uur</span>
                                    <?php elseif ($cost['cost_type'] === 'Elektriciteit'): ?>
                                        <span class="cost-unit-suffix">per kWh</span>
                                    <?php endif; ?>
                                    <button type="submit" class="update-btn" style="display: none;">Bijwerken</button>
                                </form>
                                <span class="date-updated"><?=$formatted_date?></span>
                            </div>
                        </div>
                        <?php
                    } else {
                        $current_section = ''; // Reset if not a recognized section or sub-item
                        ?>
                        <div class="cost-item">
                            <div class="cost-type"><?=$cost['cost_type']?></div>
                            <div class="cost-details">
                                <form class="inline-update-form" action="costs.php?page=<?=$page?>" method="post">
                                    <input type="hidden" name="id" value="<?=$cost['id']?>">
                                    <span class="cost-unit">€</span>
                                    <input type="number" name="cost_value" class="cost-value" value="<?=$cost['cost_value']?>" step="0.01" data-original-value="<?=$cost['cost_value']?>">
                                    <?php if (in_array($cost['cost_type'], ['Voorbereiding', 'Nabehandeling', 'Onderhoud'])): ?>
                                        <span class="cost-unit-suffix">per uur</span>
                                    <?php elseif ($cost['cost_type'] === 'Elektriciteit'): ?>
                                        <span class="cost-unit-suffix">per kWh</span>
                                    <?php endif; ?>
                                    <button type="submit" class="update-btn" style="display: none;">Bijwerken</button>
                                </form>
                                <span class="date-updated"><?=$formatted_date?></span>
                            </div>
                        </div>
                        <?php
                    }
                endforeach;
                ?>
            </div>
        </div>
    </div>
</div>

<?=template_footer()?>