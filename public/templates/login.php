<?php
ob_start();
?>

<div class="login-container">
    <div class="login-form">
        <h2>Connexion</h2>
        <form id="loginForm">
            <div class="form-group">
                <label for="username">Nom d'utilisateur :</label>
                <input type="text" id="username" name="username" required>
            </div>
            <div class="form-group">
                <label for="password">Mot de passe :</label>
                <input type="password" id="password" name="password" required>
            </div>
            <div class="form-group" id="totpGroup" style="display: none;">
                <label for="totpCode">Code TOTP (6 chiffres) :</label>
                <input type="text" id="totpCode" name="totpCode" maxlength="6" pattern="[0-9]{6}">
                <small>Entrez le code généré par votre application d'authentification</small>
            </div>
            <button type="submit" class="btn btn-primary">Se connecter</button>
        </form>
        
        <div id="loginMessage" class="message"></div>
        
        <div class="auth-links">
            <p>Pas encore de compte ? <a href="/register">Inscrivez-vous</a></p>
        </div>
        
        <div class="test-accounts">
            <h3>Comptes de test :</h3>
            <ul>
                <li><strong>Administrateur :</strong> admin / admin123</li>
                <li><strong>Utilisateur :</strong> user1 / password123</li>
            </ul>
        </div>
    </div>
</div>

<script>
document.getElementById('loginForm').addEventListener('submit', async function(e) {
    e.preventDefault();
    
    const username = document.getElementById('username').value;
    const password = document.getElementById('password').value;
    const totpCode = document.getElementById('totpCode').value;
    const messageDiv = document.getElementById('loginMessage');
    
    const requestBody = { username, password };
    if (totpCode) {
        requestBody.totp_code = totpCode;
    }
    
    try {
        const response = await fetch('/auth/login', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify(requestBody)
        });
        
        const data = await response.json();
        
        if (response.ok) {
            messageDiv.className = 'message success';
            messageDiv.textContent = data.message;
            setTimeout(() => {
                window.location.href = '/';
            }, 1000);
        } else {
            if (data.requires_totp) {
                // Afficher le champ TOTP
                document.getElementById('totpGroup').style.display = 'block';
                document.getElementById('totpCode').required = true;
                messageDiv.className = 'message info';
                messageDiv.textContent = 'Veuillez entrer votre code TOTP';
            } else {
                messageDiv.className = 'message error';
                messageDiv.textContent = data.error;
            }
        }
    } catch (error) {
        messageDiv.className = 'message error';
        messageDiv.textContent = 'Erreur de connexion';
    }
});
</script>

<?php
$content = ob_get_clean();
include 'layout.php';
?>