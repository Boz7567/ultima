<?php
// Abilita la visualizzazione degli errori per debugging
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start(); // Inizializza la sessione

// Connessione al database
$host = "localhost";
$username = "root";
$db_password = ""; // La password predefinita di XAMPP è vuota
$database = "Ultimausers"; // Nome database

$conn = new mysqli($host, $username, $db_password, $database);

// Verifica la connessione
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Verifica se il modulo di login è stato inviato
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = trim($_POST['Nickname'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($email === '' || $password === '') {
        $error_message = "Inserisci Nickname e password.";
    } else {
        // Usa prepared statement per cercare l'utente
        $stmt = $conn->prepare("SELECT * FROM Users WHERE Nickname = ? LIMIT 1");
        if ($stmt) {
            $stmt->bind_param('s', $email);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result && $result->num_rows > 0) {
                $user = $result->fetch_assoc();

                // Blocca alcuni nickname specifici (case-insensitive)
                $nickname = strtolower($user['Nickname'] ?? $user['nickname'] ?? '');
                $blocked = array_map('strtolower', ['michele', 'michelangelo', 'miky']);
                if (in_array($nickname, $blocked, true)) {
                    $error_message = "Accesso non consentito per questo account.";
                } else {
                    $hash = $user['Password'] ?? $user['password'] ?? '';
                    if ($hash !== '' && password_verify($password, $hash)) {
                        // Imposta variabili di sessione
                        // Se la tabella non ha un id numerico, usa la email come identificatore di sessione
                        $_SESSION['user_id'] = $user['id'] ?? $user['ID'] ?? $user['Email'] ?? $user['email'] ?? null;
                        $_SESSION['nickname'] = $user['Nickname'] ?? $user['nickname'] ?? '';
                        $_SESSION['email'] = $user['Email'] ?? $user['email'] ?? $email;

                        header('Location: index.php');
                        exit();
                    } else {
                        $error_message = "Password errata.";
                    }
                }
            
            }else if($email="michele"||$email="michelangelo"||$email="miky"||$email="miky764"){
                $error_message = "Michele / Michelangelo / miky e simili non sono accettati";
            }
             else {
                $error_message = "Nome utente non trovato.";
            }
            

            $stmt->close();
        } else {
            $error_message = "Errore interno. Riprova più tardi.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="style.css">
    <title>L'ultima - Login</title>
</head>
<body>

    <div class="titolume">
            <img class="logo" src="images/logo.png" alt="L'ultima Logo"/>
    </div>

    <hr>

    <div class="login-container">
        <h2>Login</h2>

        <!-- Mostra eventuali errori -->
        <?php if (!empty($error_message)): ?>
            <p class="error-message"><?= htmlspecialchars($error_message) ?></p>
        <?php endif; ?>

        <!-- Form di login -->
        <form method="post" action="">
            <label for="Nickname">Nome utente:</label>
            <input type="text" name="Nickname" id="Nickname" required maxlength="40"><br>

            <label for="password">Password:</label>
            <input type="password" name="password" id="password" required maxlength="255"><br>

            <input type="submit" value="Login">
        </form>

        <p>Inizia a conoscere L'ultima, non fare come Michele.<br>
            <a href="register.php">Register here</a>
        </p>
    </div>

    <footer>
        <p class="finalp">© 2025 L'Ultima – Testata giornalistica indipendente. Tutti i diritti riservati.</p>
    </footer>

</body>
</html>
