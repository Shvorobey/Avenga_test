<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Document Validator</title>
    <style>
        html, body {
            height: 100%;
            margin: 0;
            padding: 0;
        }
        body {
            font-family: Arial, sans-serif;
            background-color: #f1f3f5;
            display: flex;
            justify-content: center;
            align-items: center;
        }
        .form-container {
            width: 100%;
            max-width: 480px;
            background: white;
            padding: 30px;
            border-radius: 12px;
            box-shadow: 0 10px 25px rgba(0,0,0,0.08);
            box-sizing: border-box;
        }
        h2 {
            margin-top: 0;
            margin-bottom: 20px;
            color: #212529;
            text-align: center;
        }
        .form-group {
            margin-bottom: 20px;
        }
        label {
            display: block;
            margin-bottom: 6px;
            font-weight: 600;
            color: #495057;
            font-size: 14px;
        }
        input[type="text"], textarea, select {
            width: 100%;
            padding: 12px;
            border: 1px solid #ced4da;
            border-radius: 6px;
            box-sizing: border-box;
            font-size: 14px;
            transition: border-color 0.2s;
        }
        input:focus, textarea:focus, select:focus {
            outline: none;
            border-color: #80bdff;
        }

        .error-message {
            color: #dc3545;
            font-size: 12px;
            margin-top: 6px;
            display: none;
            font-weight: 500;
        }
        button {
            background-color: #007bff;
            color: white;
            border: none;
            padding: 14px;
            border-radius: 6px;
            cursor: pointer;
            width: 100%;
            font-size: 16px;
            font-weight: bold;
            margin-top: 10px;
        }
        button:hover {
            background-color: #0056b3;
        }
        .alert {
            padding: 12px;
            margin-bottom: 20px;
            border-radius: 6px;
            display: none;
            font-size: 14px;
            text-align: center;
        }
        .alert-success {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
    </style>
</head>
<body>

<div class="form-container">
    <h2>Document Validator</h2>
    <div id="globalSuccess" class="alert alert-success"></div>

    <form id="docForm">
        <div class="form-group">
            <label for="tenantId">Subscription Plan (Tenant)</label>
            <select id="tenantId">
                <option value="tenant_basic">Basic (Max 50 bytes, requires Author)</option>
                <option value="tenant_premium">Premium (Max 100 bytes, requires Author & License)</option>
            </select>
        </div>

        <div class="form-group">
            <label for="content">Document Content</label>
            <textarea id="content" rows="4" placeholder="Type or paste document content here..."></textarea>
            <div class="error-message" id="error-content"></div>
        </div>

        <div class="form-group">
            <label for="author">Author (Metadata)</label>
            <input type="text" id="author" placeholder="e.g. John Doe">
            <div class="error-message" id="error-author"></div>
        </div>

        <div class="form-group">
            <label for="license">License (Metadata)</label>
            <input type="text" id="license" placeholder="e.g. MIT (Optional for Basic)">
            <div class="error-message" id="error-license"></div>
        </div>

        <button type="submit">Validate Document</button>
    </form>
</div>

<script>
    const form = document.getElementById('docForm');
    const inputs = form.querySelectorAll('textarea, input[type="text"], select');

    inputs.forEach(input => {
        input.addEventListener('blur', function() {
            // Check if this field currently has an error
            if (this.classList.contains('is-invalid')) {
                // Removing the red frame
                this.classList.remove('is-invalid');

                // Find and hide the block containing the error that is linked to this field
                const errorElement = document.getElementById(`error-${this.id}`);
                if (errorElement) {
                    errorElement.innerText = '';
                    errorElement.style.display = 'none';
                }
            }
        });
    });

    form.addEventListener('submit', async function(e) {
        e.preventDefault();

        document.querySelectorAll('.is-invalid').forEach(el => el.classList.remove('is-invalid'));
        document.querySelectorAll('.error-message').forEach(el => { el.innerText = ''; el.style.display = 'none'; });
        const successAlert = document.getElementById('globalSuccess');
        successAlert.style.display = 'none';

        const payload = {
            tenantId: document.getElementById('tenantId').value,
            content: document.getElementById('content').value,
            author: document.getElementById('author').value,
            license: document.getElementById('license').value
        };

        try {
            const response = await fetch('Controller.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(payload)
            });

            const result = await response.json();

            if (result.success) {
                successAlert.innerText = result.message;
                successAlert.style.display = 'block';
                form.reset();
            } else {
                // Display errors next to specific fields
                for (const [fieldId, errors] of Object.entries(result.errors)) {
                    const inputElement = document.getElementById(fieldId);
                    const errorElement = document.getElementById(`error-${fieldId}`);

                    if (inputElement && errorElement) {
                        inputElement.classList.add('is-invalid');
                        errorElement.innerText = errors.join(' ');
                        errorElement.style.display = 'block';
                    }
                }
            }
        } catch (error) {
            alert('Server connection error. Please try again.');
        }
    });
</script>

</body>
</html>
