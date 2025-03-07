<?php
include 'functions.php';
// Connect to MySQL database
$pdo = pdo_connect_mysql();
// Get the page via GET request (URL param: page), if non exists default the page to 1
$page = isset($_GET['page']) && is_numeric($_GET['page']) ? (int)$_GET['page'] : 1;
// Number of records to show on each page
$records_per_page = 10;
// Prepare the SQL statement and get records from our productprintcosts table, LIMIT will determine the page
$stmt = $pdo->prepare('SELECT * FROM productprintcosts ORDER BY id DESC LIMIT :current_page, :record_per_page');
$stmt->bindValue(':current_page', ($page-1)*$records_per_page, PDO::PARAM_INT);
$stmt->bindValue(':record_per_page', $records_per_page, PDO::PARAM_INT);
$stmt->execute();
// Fetch the records so we can display them in our template.
$productprintcosts = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get the total number of productprintcosts, this is so we can determine whether there should be a next and previous button
$num_productprintcosts = $pdo->query('SELECT COUNT(*) FROM productprintcosts')->fetchColumn();
?>

<?=template_header('Read')?>

<div class="nav"></div>
<div class="content read">
  <div class="create-article">
    <div class="hamburger-menu">
      <span class="hamburger-icon">☰</span>
      <div class="dropdown-content">
        <a href="index.php?page=<?=$page?>">Overzicht</a>
        <a href="create.php?page=<?=$page?>">Nieuwe Calculatie</a>
        <a href="filaments.php?page=<?=$page?>">Filamenten</a>
      </div>
    </div>
    <h2>Overzicht</h2>
    <div class="search-stats">
      <form method="POST" action="">
        <div class="search-container">
          <input type="text" class="big" id="search" name="search" placeholder="Zoeken..." autocomplete="off"/>
          <span class="clear-search">×</span>
        </div>
        <div class="list-group" id="show-list"></div>
      </form>
    </div>
  </div>
  <table>
    <thead>
      <tr>
        <th class="col-artikelnaam">Artikelnaam</th>
        <th class="col-gewicht">Gewicht</th>
        <th class="col-print-tijd">Print Tijd</th>
        <th class="col-print-prijs">Print Prijs</th>
        <th class="col-verkoop-prijs">Verkoop Prijs</th>
        <th class="col-kleur">Gebruikte kleuren</th>
        <th class="col-aangemaakt">Aangemaakt</th>
      </tr>
    </thead>
    <tbody>
      <?php foreach ($productprintcosts as $productprintcost): ?>
      <tr onclick="window.location='update.php?id=<?=$productprintcost['id']?>&page=<?=$page?>'" style="cursor: pointer;">
        <td><?=$productprintcost['artikelnaam']?></td>
        <td><?=$productprintcost['gewicht']?>gr</td>
        <td><?=format_duration($productprintcost['printtijd'])?></td>
        <td>€<?=$productprintcost['printprijs']?></td>
        <td>€<?=$productprintcost['verkoopprijs']?></td>
        <td><?=$productprintcost['gewicht']?></td>
        <td><?=date('d-m-Y', strtotime($productprintcost['aangemaakt']))?></td>
      </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
  <div class="pagination">
    <?php if ($page > 1): ?>
        <a href="index.php?page=<?=$page-1?>" class="prev">Vorige pagina</a>
    <?php endif; ?>
    
    <?php
    $total_pages = ceil($num_productprintcosts / $records_per_page);
    $start = max(1, $page - 2);
    $end = min($total_pages, $page + 2);

    for ($i = $start; $i <= $end; $i++): ?>
        <a href="index.php?page=<?=$i?>" 
           class="<?php echo $i == $page ? 'active' : ''; ?>">
           <?=$i?>
        </a>
    <?php endfor; ?>
    
    <?php if ($page*$records_per_page < $num_productprintcosts): ?>
        <a href="index.php?page=<?=$page+1?>" class="next">Volgende pagina</a>
    <?php endif; ?>
  </div>
</div>

<?=template_footer()?>