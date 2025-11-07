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

// Verifica se il modulo di registrazione è stato inviato
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Prendi i valori dalla form
    $nome = trim($_POST['Nome'] ?? '');
    $cognome = trim($_POST['Cognome'] ?? '');
    $nickname = trim($_POST['Nickname'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    // Validazioni basilari
    if ($nome === '' || $cognome === '' || $nickname === '' || $email === '' || $password === '') {
        $error_message = "Compila tutti i campi obbligatori.";
    } else {
        // Verifica formato email
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $error_message = "Inserisci un indirizzo email valido.";
        } else {
        // Blocca alcuni nomi/nickname (case-insensitive)
        $blocked = array_map('strtolower', ['michele', 'michelangelo', 'miky']);
        if (in_array(strtolower($nome), $blocked, true) || in_array(strtolower($nickname), $blocked, true)) {
            $error_message = "Registrazione non consentita per questo nome o nickname.";
        } else {
            // Controlla che email o nickname non esistano già
            $check = $conn->prepare("SELECT 1 FROM Users WHERE Email = ? OR Nickname = ? LIMIT 1");
            if ($check) {
                $check->bind_param('ss', $email, $nickname);
                $check->execute();
                $res = $check->get_result();
                if ($res && $res->num_rows > 0) {
                    $error_message = "Email o nickname già in uso.";
                } else {
                    // Inserisci nuovo utente con password hashed
                    $hash = password_hash($password, PASSWORD_DEFAULT);
                    $insert = $conn->prepare("INSERT INTO Users (Nickname, Nome, Cognome, Email, Password) VALUES (?, ?, ?, ?, ?)");
                    if ($insert) {
                        $insert->bind_param('sssss', $nickname, $nome, $cognome, $email, $hash);
                        if ($insert->execute()) {
                            // Registrazione avvenuta: reindirizza al login
                            header('Location: login.php?registered=1');
                            exit();
                        } else {
                            $error_message = "Errore durante la registrazione. Riprova.";
                        }
                        $insert->close();
                    } else {
                        $error_message = "Errore interno. Riprova più tardi.";
                    }
                }
                $check->close();
            } else {
                $error_message = "Errore interno. Riprova più tardi.";
            }
        }
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
    <title>L'ultima - Registrazione</title>
</head>
<body>

    <div class="titolume">
            <img class="logo" src="images/logo.png" alt="L'ultima Logo"/>
    </div>

    <hr>

    <div class="login-container">
        <h2>Registrazione</h2>

        <!-- Mostra eventuali errori -->
        <?php if (!empty($error_message)): ?>
            <p class="error-message"><?= htmlspecialchars($error_message) ?></p>
        <?php endif; ?>

        <!-- Form di login -->
        <form method="post" action="">
            <label for="Nome">Nome:</label>
            <input type="text" name="Nome" id="Nome" required maxlength="40"><br>
            <label for="Cognome">Cognome:</label>
            <input type="text" name="Cognome" id="Cognome" required maxlength="40"><br>
            <label for="Nickname">Nickname:</label>
            <input type="text" name="Nickname" id="Nickname" required maxlength="40"><br>
            <label for="email">Email:</label>
            <input type="email" name="email" id="email" required maxlength="40"><br>

            <label for="password">Password:</label>
            <input type="password" name="password" id="password" required maxlength="255"><br>

            <input type="submit" value="Registrati">
        </form>
    </div>

    <footer>
        <p class="finalp">© 2025 L'Ultima – Testata giornalistica indipendente. Tutti i diritti riservati.</p>
    </footer>

</body>
</html>
