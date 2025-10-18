<?php

session_start();
include('verifica_login.php');

  include_once("audioConexao.php");
  $usuario = $_SESSION['usuario'];
// Query para trending topics nas últimas 24 horas
$sql = "
SELECT
    audio,
    usuario,
    COUNT(id) AS trendings
FROM
    chataudio
WHERE
    data >= DATE_SUB(NOW(), INTERVAL 24 HOUR)
GROUP BY
    audio,
    usuario
ORDER BY
    trendings DESC
";

$result = mysqli_query($conn, $sql);

if (mysqli_num_rows($result) > 0) {
    // Percorre os resultados
    while ($row = mysqli_fetch_assoc($result)) { ?>
<audio controls>
  <source src="<?php echo $row['audio'] ?>">
</audio>
<?php echo $row['usuario'] ?>
    <?php }
} else {

    echo "Nenhum resultado encontrado.";
}

mysqli_close($conn);
?>