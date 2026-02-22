<?php
// test_api_points.php
?>
<!DOCTYPE html>
<html>
<head>
    <title>Test API Points Focaux</title>
    <style>
        body { font-family: Arial; padding: 20px; }
        input, button { margin: 5px; padding: 5px; }
        pre { background: #f4f4f4; padding: 10px; border-radius: 5px; }
        .success { color: green; }
        .error { color: red; }
    </style>
</head>
<body>
    <h1>🧪 Test API Points Focaux</h1>
    
    <div>
        <h3>Ajouter un point focal</h3>
        <input type="text" id="direction" placeholder="Direction" value="TEST_DIR"><br>
        <input type="text" id="nom" placeholder="Nom" value="Test User"><br>
        <input type="email" id="email" placeholder="Email" value="test@test.com"><br>
        <input type="text" id="telephone" placeholder="Téléphone" value="771234567"><br>
        <button onclick="testAjout()">Tester l'ajout</button>
    </div>
    
    <div>
        <h3>Résultat:</h3>
        <pre id="result">Cliquez sur le bouton pour tester</pre>
    </div>
    
    <script>
        async function testAjout() {
            const resultDiv = document.getElementById('result');
            resultDiv.textContent = 'Chargement...';
            
            const data = {
                direction: document.getElementById('direction').value,
                nom: document.getElementById('nom').value,
                email: document.getElementById('email').value,
                telephone: document.getElementById('telephone').value
            };
            
            try {
                const response = await fetch('api/points.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify(data)
                });
                
                const responseText = await response.text();
                
                let output = `Status: ${response.status}\n`;
                output += `Headers: ${JSON.stringify([...response.headers])}\n`;
                output += `Réponse brute:\n${responseText}\n`;
                
                try {
                    const json = JSON.parse(responseText);
                    output += `\nJSON parsé:\n${JSON.stringify(json, null, 2)}`;
                } catch (e) {
                    output += `\n❌ Erreur parsing JSON: ${e.message}`;
                }
                
                resultDiv.textContent = output;
                
            } catch (error) {
                resultDiv.textContent = `❌ Erreur: ${error.message}`;
            }
        }
    </script>
</body>
</html>