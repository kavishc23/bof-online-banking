<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>BoF Online Banking Login</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background: #f4f7fb;
            margin: 0;
            padding: 0;
        }

        .login-container {
            width: 400px;
            margin: 80px auto;
            background: white;
            padding: 30px;
            border-radius: 12px;
            box-shadow: 0 4px 14px rgba(0,0,0,0.1);
        }

        h1 {
            text-align: center;
            color: #1d3557;
            margin-bottom: 10px;
        }

        p.subtitle {
            text-align: center;
            color: #666;
            margin-bottom: 25px;
        }

        label {
            font-weight: bold;
            display: block;
            margin-bottom: 6px;
            color: #333;
        }

        input[type="text"],
        input[type="password"] {
            width: 100%;
            padding: 12px;
            margin-bottom: 18px;
            border: 1px solid #ccc;
            border-radius: 8px;
            font-size: 14px;
            box-sizing: border-box;
        }

        button {
            width: 100%;
            background: #1d4ed8;
            color: white;
            border: none;
            padding: 12px;
            border-radius: 8px;
            font-size: 16px;
            cursor: pointer;
        }

        button:hover {
            background: #1e40af;
        }

        .error {
            background: #ffe5e5;
            color: #b00020;
            padding: 10px;
            border-radius: 8px;
            margin-bottom: 15px;
        }

        .validation-errors {
            background: #fff4e5;
            color: #8a5500;
            padding: 10px;
            border-radius: 8px;
            margin-bottom: 15px;
        }

        .footer-note {
            margin-top: 20px;
            text-align: center;
            font-size: 13px;
            color: #777;
        }
    </style>
</head>
<body>

    <div class="login-container">
        <h1>BoF Online Banking</h1>
        <p class="subtitle">Please log in using the credentials provided by the bank.</p>

        @if(session('error'))
            <div class="error">
                {{ session('error') }}
            </div>
        @endif

        @if($errors->any())
            <div class="validation-errors">
                <ul style="margin: 0; padding-left: 20px;">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form method="POST" action="{{ route('login.post') }}">
            @csrf

            <label for="identifier">Email or Username</label>
            <input
                type="text"
                id="identifier"
                name="identifier"
                value="{{ old('identifier') }}"
                required
            >

            <label for="password">Password</label>
            <input
                type="password"
                id="password"
                name="password"
                required
            >

            <button type="submit">Login</button>
        </form>

        <div class="footer-note">
            Access is available only to bank-approved customers.
        </div>
    </div>

</body>
</html>