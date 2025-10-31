<?php
ob_start();
?>

<script src="https://cdnjs.cloudflare.com/ajax/libs/qrcode-generator/1.4.4/qrcode.min.js"></script>

<div class="totp-setup-container">
    <div class="totp-setup-form">
        <h2>Configuration de l'authentification à deux facteurs (TOTP)</h2>
        
        <div class="setup-steps">
            <div class="step" id="step1">
                <h3>Étape 1 : Installer une application d'authentification</h3>
                <p>Installez une application d'authentification TOTP sur votre téléphone :</p>
                <ul>
                    <li><strong>Google Authenticator</strong> (iOS/Android)</li>
                    <li><strong>Microsoft Authenticator</strong> (iOS/Android)</li>
                    <li><strong>TOTP Authenticator</strong> (Browser extension)</li>
                    <li><strong>Authy</strong> (iOS/Android)</li>
                </ul>
                <button id="startSetup" class="btn btn-primary">Commencer la configuration</button>
            </div>
            
            <div class="step" id="step2" style="display: none;">
                <h3>Étape 2 : Scanner le QR Code</h3>
                <div id="qrCodeContainer">
                    <p>Scannez ce QR code avec votre application d'authentification :</p>
                    <div id="qrCode"></div>
                    <div class="manual-entry">
                        <p><strong>Ou entrez manuellement cette clé secrète :</strong></p>
                        <code id="secretKey"></code>
                        <button id="copySecret" class="btn btn-secondary">Copier</button>
                    </div>
                </div>
            </div>
            
            <div class="step" id="step3" style="display: none;">
                <h3>Étape 3 : Vérifier le code</h3>
                <p>Entrez le code à 6 chiffres généré par votre application :</p>
                <form id="verifyForm">
                    <div class="form-group">
                        <label for="verifyCode">Code TOTP :</label>
                        <input type="text" id="verifyCode" name="verifyCode" maxlength="6" pattern="[0-9]{6}" required 
                               placeholder="123456" inputmode="numeric" autocomplete="one-time-code">
                    </div>
                    <button type="submit" class="btn btn-success">Activer TOTP</button>
                    <button type="button" id="cancelSetup" class="btn btn-secondary">Annuler</button>
                </form>
            </div>
            
            <div class="step" id="step4" style="display: none;">
                <h3>✅ TOTP activé avec succès !</h3>
                <p>L'authentification à deux facteurs est maintenant activée sur votre compte.</p>
                <p>Vous devrez entrer un code TOTP à chaque connexion.</p>
                <a href="/profile" class="btn btn-primary">Retour au profil</a>
            </div>
        </div>
        
        <div id="setupMessage" class="message"></div>
    </div>
</div>

<script>
document.getElementById('startSetup').addEventListener('click', async function() {
    const messageDiv = document.getElementById('setupMessage');
    
    try {
        const response = await fetch('/auth/totp/setup', {
            method: 'POST',
            credentials: 'include'
        });
        
        const data = await response.json();
        
        if (response.ok) {
            // Afficher le QR code
            document.getElementById('step1').style.display = 'none';
            document.getElementById('step2').style.display = 'block';
            
            // Générer le QR code
            const qr = qrcode(0, 'M');
            qr.addData(data.qr_code_url);
            qr.make();
            
            const qrCodeElement = document.getElementById('qrCode');
            qrCodeElement.innerHTML = qr.createImgTag(4, 8);
            qrCodeElement.style.textAlign = 'center';
            
            document.getElementById('secretKey').textContent = data.secret;
            
            
            // Ajouter un bouton pour passer à l'étape suivante
            const nextStepBtn = document.createElement('button');
            nextStepBtn.textContent = 'J\'ai scanné le QR code';
            nextStepBtn.className = 'btn btn-primary';
            nextStepBtn.style.marginTop = '20px';
            nextStepBtn.addEventListener('click', function() {
                document.getElementById('step2').style.display = 'none';
                document.getElementById('step3').style.display = 'block';
            });
            document.getElementById('qrCodeContainer').appendChild(nextStepBtn);
        } else {
            messageDiv.className = 'message error';
            messageDiv.textContent = data.error;
        }
    } catch (error) {
        messageDiv.className = 'message error';
        messageDiv.textContent = 'Erreur lors de la configuration';
    }
});

document.getElementById('copySecret').addEventListener('click', function() {
    const secretKey = document.getElementById('secretKey').textContent;
    navigator.clipboard.writeText(secretKey);
    this.textContent = 'Copié !';
    setTimeout(() => {
        this.textContent = 'Copier';
    }, 2000);
});

document.getElementById('verifyForm').addEventListener('submit', async function(e) {
    e.preventDefault();
    
    const code = document.getElementById('verifyCode').value.trim().replace(/\s/g, '');
    const messageDiv = document.getElementById('setupMessage');
    
    try {
        const response = await fetch('/auth/totp/enable', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            credentials: 'include',
            body: JSON.stringify({ totp_code: code })
        });
        
        const data = await response.json();
        
        if (response.ok) {
            document.getElementById('step3').style.display = 'none';
            document.getElementById('step4').style.display = 'block';
        } else {
            messageDiv.className = 'message error';
            messageDiv.textContent = data.error || 'Code TOTP invalide. Vérifiez que l\'heure de votre téléphone est correcte.';
        }
    } catch (error) {
        messageDiv.className = 'message error';
        messageDiv.textContent = 'Erreur lors de la vérification';
    }
});

document.getElementById('cancelSetup').addEventListener('click', function() {
    window.location.href = '/profile';
});

// Nettoyer l'input en temps réel
document.getElementById('verifyCode').addEventListener('input', function(e) {
    const value = e.target.value.replace(/[^0-9]/g, '');
    e.target.value = value;
});
</script>

<style>
.totp-setup-container {
    max-width: 600px;
    margin: 0 auto;
    padding: 20px;
}

.step {
    margin-bottom: 30px;
    padding: 20px;
    border: 1px solid #ddd;
    border-radius: 8px;
}

.step h3 {
    color: #333;
    margin-bottom: 15px;
}

.manual-entry {
    margin-top: 20px;
    padding: 15px;
    background-color: #f5f5f5;
    border-radius: 4px;
}

.manual-entry code {
    display: block;
    margin: 10px 0;
    padding: 10px;
    background-color: #fff;
    border: 1px solid #ddd;
    border-radius: 4px;
    font-family: monospace;
    word-break: break-all;
}

.form-group {
    margin-bottom: 15px;
}

.form-group label {
    display: block;
    margin-bottom: 5px;
    font-weight: bold;
}

.form-group input {
    width: 100%;
    max-width: 200px;
    padding: 10px;
    border: 1px solid #ddd;
    border-radius: 4px;
    font-size: 18px;
    text-align: center;
    letter-spacing: 2px;
}

.btn {
    padding: 10px 20px;
    border: none;
    border-radius: 4px;
    cursor: pointer;
    text-decoration: none;
    display: inline-block;
    margin-right: 10px;
}

.btn-primary {
    background-color: #007bff;
    color: white;
}

.btn-secondary {
    background-color: #6c757d;
    color: white;
}

.btn-success {
    background-color: #28a745;
    color: white;
}

.message {
    padding: 10px;
    margin: 15px 0;
    border-radius: 4px;
}

.message.error {
    background-color: #f8d7da;
    color: #721c24;
    border: 1px solid #f5c6cb;
}

.message.success {
    background-color: #d4edda;
    color: #155724;
    border: 1px solid #c3e6cb;
}
</style>

<?php
$content = ob_get_clean();
include 'layout.php';
?>