<?php
include 'functions.php';
$pdo = pdo_connect_mysql();
$page = isset($_GET['page']) && is_numeric($_GET['page']) ? (int)$_GET['page'] : 1;

// Fetch all costs
$stmt = $pdo->query('SELECT * FROM costs ORDER BY cost_type');
$costs = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Handle update submission
if (!empty($_POST) && isset($_POST['cost_id']) && isset($_POST['cost_value'])) {
    if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'Ongeldige CSRF-token']);
        exit;
    }

    $cost_id = (int)$_POST['cost_id'];
    $cost_value = (float)$_POST['cost_value'];

    if ($cost_value < 0) {
        header('Content$bbType: application/json');
        echo json_encode(['success' => false, 'message' => 'Kosten mogen niet negatief zijn!']);
        exit;
    }

    $stmt = $pdo->prepare('UPDATE costs SET cost_value = ? WHERE id = ?');
    $stmt->execute([$cost_value, $cost_id]);

    header('Content-Type: application/json');
    echo json_encode([
        'success' => true,
        'message' => 'Kosten succesvol bijgewerkt!',
        'cost_value' => number_format($cost_value, 2),
        'date_updated' => date('d-m-Y H:i')
    ]);
    exit;
}
?>

<?=template_header('Costs')?>

<div class="nav"></div>
<div class="content read">
    <div class="create-article">
        <div class="hamburger-menu" data-menu>
            <span class="hamburger-icon"><i class='bx bx-menu'></i></span>
            <div class="dropdown-content">
                <a href="index.php?page=<?=$page?>">Overzicht</a>
                <a href="create.php?page=<?=$page?>">Nieuwe Calculatie</a>
                <a href="filaments.php?page=<?=$page?>">Filamenten</a>
                <a href="costs.php?page=<?=$page?>">Kosten</a>
            </div>
        </div>
        <h2>Kosten</h2>
    </div>
    <?=get_flash_message()?>
    <div class="toast-container"></div> <!-- Added toast container -->

    <?php if (empty($costs)): ?>
        <p>Geen kosten gevonden.</p>
    <?php else: ?>
        <table>
            <thead>
                <tr>
                    <th>Kostensoort</th>
                    <th>Waarde (€)</th>
                    <th>Laatst Bijgewerkt</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($costs as $cost): ?>
                    <tr data-cost-id="<?=$cost['id']?>">
                        <td><?=htmlspecialchars($cost['cost_type'])?></td>
                        <td>
                            <form action="costs.php?page=<?=$page?>" method="post" class="inline-update-form">
                                <input type="hidden" name="csrf_token" value="<?=$_SESSION['csrf_token']?>">
                                <input type="hidden" name="cost_id" value="<?=$cost['id']?>">
                                <input type="number" class="big cost-value" name="cost_value" value="<?=number_format($cost['cost_value'], 2)?>" step="0.01" min="0" data-original-value="<?=number_format($cost['cost_value'], 2)?>">
                                <span class="cost-unit">
                                    <?php
                                    if ($cost['cost_type'] === 'Elektriciteit') {
                                        echo 'per kWh';
                                    } elseif ($cost['cost_type'] === 'Arbeid' || $cost['cost_type'] === 'Machine Onderhoud') {
                                        echo 'per uur';
                                    }
                                    ?>
                                </span>
                                <input type="submit" value="Update" class="update-btn" style="display: none;">
                            </form>
                        </td>
                        <td class="date-updated"><?=date('d-m-Y H:i', strtotime($cost['date_updated']))?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</div>

<?=template_footer()?>