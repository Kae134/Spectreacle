<?php
ob_start();
?>

<div class="login-container">
    <div class="login-form register-form">
        <h2>Inscription</h2>
        <form id="registerForm">
            <div class="form-group">
                <label for="username">Nom d'utilisateur :</label>
                <input type="text" id="username" name="username" required>
                <small>3-20 caractères, lettres, chiffres et tirets bas uniquement</small>
            </div>
            <div class="form-group">
                <label for="email">Adresse email :</label>
                <input type="email" id="email" name="email" required>
            </div>
            <div class="form-group">
                <label for="password">Mot de passe :</label>
                <input type="password" id="password" name="password" required>
                <small>Au moins 6 caractères</small>
            </div>
            <div class="form-group">
                <label for="confirmPassword">Confirmer le mot de passe :</label>
                <input type="password" id="confirmPassword" name="confirmPassword" required>
            </div>
            <button type="submit" class="btn btn-primary">S'inscrire</button>
        </form>
        
        <div id="registerMessage" class="message"></div>
        
        <div class="auth-links">
            <p>Vous avez déjà un compte ? <a href="/login">Connectez-vous</a></p>
        </div>
    </div>
</div>

<script>
document.getElementById('registerForm').addEventListener('submit', async function(e) {
    e.preventDefault();
    
    const username = document.getElementById('username').value;
    const email = document.getElementById('email').value;
    const password = document.getElementById('password').value;
    const confirmPassword = document.getElementById('confirmPassword').value;
    const messageDiv = document.getElementById('registerMessage');
    
    // Validation côté client
    if (username.length < 3) {
        messageDiv.className = 'message error';
        messageDiv.textContent = 'Le nom d\'utilisateur doit contenir au moins 3 caractères';
        return;
    }
    
    if (password.length < 6) {
        messageDiv.className = 'message error';
        messageDiv.textContent = 'Le mot de passe doit contenir au moins 6 caractères';
        return;
    }
    
    if (password !== confirmPassword) {
        messageDiv.className = 'message error';
        messageDiv.textContent = 'Les mots de passe ne correspondent pas';
        return;
    }
    
    try {
        const response = await fetch('/auth/register', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({ username, email, password, confirmPassword })
        });
        
        const data = await response.json();
        
        if (response.ok) {
            messageDiv.className = 'message success';
            messageDiv.textContent = data.message;
            setTimeout(() => {
                window.location.href = '/profile';
            }, 1500);
        } else {
            messageDiv.className = 'message error';
            messageDiv.textContent = data.error;
        }
    } catch (error) {
        messageDiv.className = 'message error';
        messageDiv.textContent = 'Erreur de connexion';
    }
});

// Validation en temps réel du nom d'utilisateur
document.getElementById('username').addEventListener('input', function() {
    const username = this.value;
    const messageDiv = document.getElementById('registerMessage');
    
    if (username.length > 0 && !/^[a-zA-Z0-9_]+$/.test(username)) {
        messageDiv.className = 'message error';
        messageDiv.textContent = 'Le nom d\'utilisateur ne peut contenir que des lettres, chiffres et tirets bas';
    } else if (messageDiv.textContent.includes('nom d\'utilisateur ne peut contenir')) {
        messageDiv.textContent = '';
        messageDiv.className = '';
    }
});

// Validation en temps réel de la confirmation du mot de passe
document.getElementById('confirmPassword').addEventListener('input', function() {
    const password = document.getElementById('password').value;
    const confirmPassword = this.value;
    const messageDiv = document.getElementById('registerMessage');
    
    if (confirmPassword.length > 0 && password !== confirmPassword) {
        messageDiv.className = 'message error';
        messageDiv.textContent = 'Les mots de passe ne correspondent pas';
    } else if (messageDiv.textContent.includes('mots de passe ne correspondent pas')) {
        messageDiv.textContent = '';
        messageDiv.className = '';
    }
});
</script>

<?php
$content = ob_get_clean();
include 'layout.php';
?>