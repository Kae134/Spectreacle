<?php
ob_start();
?>

<script src="https://cdnjs.cloudflare.com/ajax/libs/qrcode-generator/1.4.4/qrcode.min.js"></script>

<div class="totp-setup-container">
    <div class="totp-setup-form">
        <h2>Configuration de l'authentification à deux facteurs</h2>
        
        <div class="setup-steps">

            <!-- Choix du mode -->
            <div class="step" id="step0">
                <h3>Choisissez une méthode de double authentification</h3>
                <p>Sélectionnez comment vous voulez sécuriser vos connexions :</p>

                <button id="chooseTotp" class="btn btn-primary" style="margin-bottom:10px;">
                    Activer via application TOTP (Google Authenticator)
                </button>

                <button id="chooseSms" class="btn btn-secondary" style="margin-bottom:10px;">
                    Activer via SMS
                </button>

                <button id="chooseEmail" class="btn btn-secondary">
                    Activer via Email
                </button>
            </div>

            <!-- TOTP (déjà existant) -->
            <div class="step" id="step1" style="display:none;">
                <h3>Étape 1 : Installer une application d'authentification</h3>
                <p>Installez une application TOTP sur votre téléphone :</p>
                <ul>
                    <li><strong>Google Authenticator</strong></li>
                    <li><strong>Microsoft Authenticator</strong></li>
                    <li><strong>Authy</strong></li>
                </ul>
                <button id="startTotpSetup" class="btn btn-primary">Commencer la configuration TOTP</button>
            </div>

            <!-- QR code TOTP -->
            <div class="step" id="step2" style="display:none;">
                <h3>Étape 2 : Scanner le QR Code</h3>
                <div id="qrCodeContainer">
                    <div id="qrCode"></div>
                    <div class="manual-entry">
                        <p><strong>Ou saisissez la clé secret :</strong></p>
                        <code id="secretKey"></code>
                        <button id="copySecret" class="btn btn-secondary">Copier</button>
                    </div>
                </div>
            </div>

            <!-- Vérification du code TOTP -->
            <div class="step" id="step3" style="display:none;">
                <h3>Étape 3 : Vérifier le code TOTP</h3>
                <form id="verifyTotpForm">
                    <div class="form-group">
                        <label for="verifyTotp">Code TOTP :</label>
                        <input type="text" id="verifyTotp" maxlength="6" pattern="[0-9]{6}">
                    </div>
                    <button type="submit" class="btn btn-success">Activer TOTP</button>
                    <button type="button" id="cancel" class="btn btn-secondary">Annuler</button>
                </form>
            </div>


            <!-- SMS : Saisie du numéro -->
            <div class="step" id="smsStep1" style="display:none;">
                <h3>Activer la double authentification par SMS</h3>
                <p>Entrez votre numéro de téléphone (format international).</p>
                <form id="smsPhoneForm">
                    <div class="form-group">
                        <input type="text" id="smsPhone" placeholder="+33612345678" inputmode="tel" required>
                    </div>
                    <button type="submit" class="btn btn-primary">Recevoir le code</button>
                </form>
            </div>

            <!-- SMS : Vérification du code -->
            <div class="step" id="smsStep2" style="display:none;">
                <h3>Vérifier le code reçu par SMS</h3>
                <form id="smsVerifyForm">
                    <div class="form-group">
                        <input type="text" id="smsOtp" maxlength="6" pattern="[0-9]{6}" inputmode="numeric" required>
                    </div>
                    <button type="submit" class="btn btn-success">Activer SMS</button>
                </form>
            </div>


            <!-- Email : vérification du code -->
            <div class="step" id="emailStep1" style="display:none;">
                <h3>Activer la double authentification par Email</h3>
                <p>Nous allons envoyer un code de confirmation à votre adresse email.</p>
                <button id="sendEmailOtp" class="btn btn-primary">Recevoir le code</button>
            </div>

            <div class="step" id="emailStep2" style="display:none;">
                <h3>Entrez le code reçu par Email</h3>
                <form id="emailVerifyForm">
                    <div class="form-group">
                        <input type="text" id="emailOtp" maxlength="6" pattern="[0-9]{6}" required>
                    </div>
                    <button type="submit" class="btn btn-success">Activer Email</button>
                </form>
            </div>

            <!-- Final message -->
            <div class="step" id="step4" style="display:none;">
                <h3>✅ Authentification à deux facteurs activée !</h3>
                <a href="/profile" class="btn btn-primary">Retour au profil</a>
            </div>

        </div>

        <div id="setupMessage" class="message"></div>
    </div>
</div>

<script>

document.getElementById('chooseTotp').onclick = () => {
    document.getElementById('step0').style.display = 'none';
    document.getElementById('step1').style.display = 'block';
};

document.getElementById('chooseSms').onclick = () => {
    document.getElementById('step0').style.display = 'none';
    document.getElementById('smsStep1').style.display = 'block';
};

document.getElementById('chooseEmail').onclick = () => {
    document.getElementById('step0').style.display = 'none';
    document.getElementById('emailStep1').style.display = 'block';
};

document.getElementById('copySecret').addEventListener('click', function() {
    const secretKey = document.getElementById('secretKey').textContent;
    navigator.clipboard.writeText(secretKey);
    this.textContent = 'Copié !';
    setTimeout(() => {
        this.textContent = 'Copier';
    }, 2000);
});

document.getElementById('startTotpSetup').onclick = async () => {
    const res = await fetch('/auth/totp/setup', { method: 'POST', credentials: 'include' });
    const data = await res.json();

    document.getElementById('step1').style.display = 'none';
    document.getElementById('step2').style.display = 'block';

    const qr = qrcode(0, 'M');
    qr.addData(data.qr_code_url);
    qr.make();
    document.getElementById('qrCode').innerHTML = qr.createImgTag(4,8);
    document.getElementById('secretKey').textContent = data.secret;

    const next = document.createElement('button');
    next.textContent = "J'ai scanné le QR code";
    next.className = "btn btn-primary";
    next.style.marginTop = "20px";
    next.onclick = () => {
        document.getElementById('step2').style.display = 'none';
        document.getElementById('step3').style.display = 'block';
    };
    document.getElementById('qrCodeContainer').appendChild(next);
};

document.getElementById('verifyTotpForm').onsubmit = async e => {
    e.preventDefault();
    const code = document.getElementById('verifyTotp').value;

    const res = await fetch('/auth/totp/enable', {
        method: 'POST',
        headers: {'Content-Type': 'application/json'},
        credentials: 'include',
        body: JSON.stringify({ totp_code: code })
    });

    if (res.ok) {
        document.getElementById('step3').style.display = 'none';
        document.getElementById('step4').style.display = 'block';
    }
};


//
// ---- SMS ----
//

// Étape 1 : envoyer SMS
document.getElementById('smsPhoneForm').onsubmit = async e => {
    e.preventDefault();
    const phone = document.getElementById('smsPhone').value;

    const res = await fetch('/auth/2fa/sms/start', { // endpoint que je te génère
        method : 'POST',
        headers : {'Content-Type':'application/json'},
        credentials : 'include',
        body : JSON.stringify({ phone })
    });

    const data = await res.json();

    if (res.ok) {
        window._challengeId = data.challenge_id;

        document.getElementById('smsStep1').style.display = 'none';
        document.getElementById('smsStep2').style.display = 'block';
    }
};

// Étape 2 : vérifier le code SMS
document.getElementById('smsVerifyForm').onsubmit = async e => {
    e.preventDefault();
    const code = document.getElementById('smsOtp').value;

    const res = await fetch('/auth/2fa/sms/verify', {
        method: 'POST',
        headers: {'Content-Type':'application/json'},
        credentials: 'include',
        body: JSON.stringify({
            challenge_id: window._challengeId,
            code
        })
    });

    if (res.ok) {
        document.getElementById('smsStep2').style.display = 'none';
        document.getElementById('step4').style.display = 'block';
    }
};


//
// ---- Email ----
//

// Étape 1 : envoyer email
document.getElementById('sendEmailOtp').onclick = async () => {
    const res = await fetch('/auth/2fa/email/start', {
        method:'POST',
        credentials:'include'
    });

    const data = await res.json();

    if (res.ok) {
        window._challengeId = data.challenge_id;

        document.getElementById('emailStep1').style.display = 'none';
        document.getElementById('emailStep2').style.display = 'block';
    }
};

// Étape 2 : vérifier email OTP
document.getElementById('emailVerifyForm').onsubmit = async e => {
    e.preventDefault();
    const code = document.getElementById('emailOtp').value;

    const res = await fetch('/auth/2fa/email/verify', {
        method:'POST',
        headers:{'Content-Type':'application/json'},
        credentials:'include',
        body:JSON.stringify({
            challenge_id: window._challengeId,
            code
        })
    });

    if (res.ok) {
        document.getElementById('emailStep2').style.display = 'none';
        document.getElementById('step4').style.display = 'block';
    }
};
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