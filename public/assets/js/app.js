// Fonction de déconnexion
async function logout() {
    try {
        const response = await fetch('/auth/logout', {
            method: 'POST',
            credentials: 'include'
        });
        
        if (response.ok) {
            window.location.href = '/';
        } else {
            alert('Erreur lors de la déconnexion');
        }
    } catch (error) {
        alert('Erreur de connexion');
    }
}

// Vérifier l'expiration du token périodiquement
function checkTokenExpiration() {
    // Vérifier toutes les 30 secondes
    setInterval(async () => {
        try {
            const response = await fetch('/dashboard', {
                method: 'GET',
                credentials: 'include'
            });
            
            if (response.status === 401) {
                alert('Votre session a expiré. Vous allez être redirigé vers la page de connexion.');
                window.location.href = '/login';
            }
        } catch (error) {
            console.log('Erreur lors de la vérification du token');
        }
    }, 30000);
}

// Initialiser la vérification du token si on est sur une page protégée
document.addEventListener('DOMContentLoaded', function() {
    if (window.location.pathname === '/dashboard') {
        checkTokenExpiration();
    }
});

// Fonction pour afficher des messages
function showMessage(message, type = 'info') {
    const messageDiv = document.createElement('div');
    messageDiv.className = `message ${type}`;
    messageDiv.textContent = message;
    
    document.body.insertBefore(messageDiv, document.body.firstChild);
    
    setTimeout(() => {
        messageDiv.remove();
    }, 5000);
}