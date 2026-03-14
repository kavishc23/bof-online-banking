<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>BoF Online Banking Login</title>
    <style>
        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            font-family: Arial, sans-serif;
            background: linear-gradient(135deg, #0f2b5b, #1d4ed8);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }

        .login-card {
            width: 100%;
            max-width: 430px;
            background: white;
            border-radius: 20px;
            padding: 35px 30px;
            box-shadow: 0 12px 30px rgba(0,0,0,0.18);
        }

        .brand {
            text-align: center;
            margin-bottom: 28px;
        }

        .brand h1 {
            margin: 0;
            font-size: 34px;
            color: #163d7a;
        }

        .brand p {
            margin-top: 10px;
            color: #6b7280;
            font-size: 14px;
        }

        label {
            display: block;
            font-weight: bold;
            margin-bottom: 8px;
            color: #1f2937;
        }

        input[type="text"],
        input[type="password"] {
            width: 100%;
            padding: 13px 14px;
            border: 1px solid #cbd5e1;
            border-radius: 12px;
            margin-bottom: 18px;
            font-size: 14px;
        }

        input:focus {
            outline: none;
            border-color: #1d4ed8;
            box-shadow: 0 0 0 3px rgba(29, 78, 216, 0.12);
        }

        .login-btn {
            width: 100%;
            background: #1d4ed8;
            color: white;
            border: none;
            padding: 14px;
            font-size: 15px;
            font-weight: bold;
            border-radius: 12px;
            cursor: pointer;
        }

        .login-btn:hover {
            background: #1e40af;
        }

        .error-box {
            background: #fee2e2;
            color: #991b1b;
            padding: 12px;
            border-radius: 10px;
            margin-bottom: 16px;
            font-size: 14px;
        }

        .validation-box {
            background: #fff7ed;
            color: #9a3412;
            padding: 12px;
            border-radius: 10px;
            margin-bottom: 16px;
            font-size: 14px;
        }

        .footer-note {
            margin-top: 18px;
            text-align: center;
            color: #6b7280;
            font-size: 13px;
        }
    </style>
</head>
<body>

    <div class="login-card">
        <div class="brand">
            <h1>BoF</h1>
            <p>Online Banking Portal</p>
        </div>

        @if(session('error'))
            <div class="error-box">
                {{ session('error') }}
            </div>
        @endif

        @if($errors->any())
            <div class="validation-box">
                <ul style="margin: 0; padding-left: 18px;">
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

            <button type="submit" class="login-btn">Login</button>
        </form>

        <div class="footer-note">
            Access is available only to bank-approved customers.
        </div>
    </div>

</body>
</html>