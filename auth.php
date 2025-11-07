<?php
// auth.php — controlla la sessione e blocca l'accesso alle pagine se non loggati
// Include questa pagina all'inizio di ogni file PHP (esclusi login.php e register.php)

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Se l'utente è loggato (user_id, email o nickname impostati) permetti l'accesso
if (!empty($_SESSION['user_id']) || !empty($_SESSION['email']) || !empty($_SESSION['nickname'])) {
    return;
}



http_response_code(403);
?><!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <link rel="stylesheet" href="styleauth.css">
    <title>Accesso richiesto</title>
</head>
<body>
    <header>
        <img src="images/logo.png" alt="Logo">
        <hr>
    </header>
    <div class="container">
        <div class="box">
            <h1>Per visualizzare devi accedere</h1>
            <p>Questa pagina è riservata agli utenti registrati. Effettua il login per proseguire.</p>
            <a class="btn" href="/ultima/login.php">Vai al Login</a>
        </div>
    </div>
    
</body>
</html>
<?php
exit();
