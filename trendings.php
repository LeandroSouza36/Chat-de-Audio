<?php
session_start();
include('verifica_login.php');
include_once("audioConexao.php");

$usuario = $_SESSION['usuario'];

// Busca o áudio mais curtido nas últimas 24 horas
$sql = "
SELECT 
    idCurtida,
    AudioCurtido,
    audio,
    COUNT(*) AS totalCurtidas
FROM 
    curtidas
WHERE 
    HoraDaCurtida >= DATE_SUB(NOW(), INTERVAL 24 HOUR)
GROUP BY 
    AudioCurtido
ORDER BY 
    totalCurtidas DESC
LIMIT 10
";

$result = mysqli_query($conn, $sql);

if (mysqli_num_rows($result) > 0) {
    while ($row = mysqli_fetch_assoc($result)) {


        ?>
        
        <p><strong>ID da Curtida:</strong> <?php echo $row['idCurtida']; ?></p>
        <p><strong>ID do Áudio Curtido:</strong> <?php echo $row['AudioCurtido']; ?></p>
        <p><strong>Total de Curtidas:</strong> <?php echo $row['totalCurtidas']; ?></p>
        
        <audio controls>
            <source src="<?php echo $row['audio'] ?>" type="audio/mpeg">
            Seu navegador não suporta o elemento de áudio.
        </audio>
        
        <?php
    }
} else {
    echo "Nenhum resultado encontrado.";
}

mysqli_close($conn);
?>
