<?php
function pdo_connect_mysql() {
  $DATABASE_HOST = 'localhost';
  $DATABASE_USER = 'root';
  $DATABASE_PASS = 'root';
  $DATABASE_NAME = 'printcost';
  try {
    return new PDO('mysql:host=' . $DATABASE_HOST . ';dbname=' . $DATABASE_NAME . ';charset=utf8', $DATABASE_USER, $DATABASE_PASS);
  } catch (PDOException $exception) {
  	// If there is an error with the connection, stop the script and display the error.
    exit('Er kan geen verbinding worden gemaakt met de database!');
  }
}

function format_duration($minutes) {
  $hours = floor($minutes / 60); // Integer division for hours
  $remaining_minutes = $minutes % 60; // Remainder for minutes
  
  $result = '';
  if ($hours > 0) {
      $result .= $hours . 'uur';
  }
  if ($remaining_minutes > 0) {
      if ($hours > 0) $result .= ' '; // Add space between hours and minutes
      $result .= $remaining_minutes . 'min';
  }
  return $result ?: '0min'; // Return '0min' if no hours or minutes
}

function template_header($title) {
echo <<<EOT
<!DOCTYPE html>
<html lang="nl">
	<head>
		<meta charset="utf-8">
		<meta name="viewport" content="width=device-width, initial-scale=1.0">
		<title>Print Prijs Calculator</title>
    <link href="images/favicon.ico" rel="icon" type="image/x-icon">
		<link href="css/style.css" rel="stylesheet" type="text/css">
		<link href="https://cdn.jsdelivr.net/npm/boxicons@latest/css/boxicons.min.css" rel="stylesheet" type="text/css">
	</head>
	<body>
	  <div class="container">
			<div class="top">
				<!--<div class="logo">
					<a href="../printcost/index.php"><img src="images/bs-logo.svg"></a>
				</div>-->
			</div>
EOT;
}

function template_footer() {
echo <<<EOT
			<div class="footer">
				<div>
					<div>&copy;<script>document.write(new Date().getFullYear())</script> - Bas Martens</div>
				</div>
				<div>
					<div>v1.0</div>
				</div>
			</div>
    </div>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.3/jquery.min.js" type="text/javascript"></script>
    <script src="js/main.js" type="text/javascript"></script>
    <script src="js/search.js" type="text/javascript"></script>
  </body>
</html>
EOT;
}
?>