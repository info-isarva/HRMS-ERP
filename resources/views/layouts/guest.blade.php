<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login | {{ config('app.name', 'Laravel') }}</title>
    <link href="{{asset('plugins/bootstrap.min.css')}}" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
  <link rel="shortcut icon" type="image/x-icon" href="{{ URL::to('/images/isarvafavicon.png') }}">
    <script src="{{asset('plugins/bootstrap.bundle.min.js')}}"></script>
    <style>
        :root {
            /* Simple, Clean Blue Palette */
            --primary: #2563eb;       
            --primary-hover: #1d4ed8;
            --brand-blue: #2563eb;
            --text-main: #1e293b;
            --text-muted: #64748b;
            --border-color: #cbd5e1;
            --white: #ffffff;
            --bg-page: #f1f5f9;
        }

        body {
            font-family: 'Poppins', sans-serif;
            background: linear-gradient(135deg, #eff6ff 0%, #dbeafe 100%); /* Soft Light Blue Gradient */
            margin: 0;
            padding: 0;
            min-height: 100vh;
            display: flex;
            align-items: center; 
            justify-content: center;
            color: var(--text-main);
        }

        /* Card Container - Simple Rounded Rectangle */
        .login-card-container {
            display: flex;
            width: 100%;
            max-width: 1000px; /* Slightly compact */
            min-height: 600px;
            background: var(--white);
            border-radius: 12px; /* Standard rounded corners */
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.05); /* Minimal shadow */
            overflow: hidden; 
            margin: 20px;
        }

        /* 
         * Left Side: Content & Features 
         * (Swapped to Left as requested)
         */
        /* 
         * Left Side: Content & Features 
         * (Deep Navy Gradient with Circles like reference)
         */
        /* 
         * Left Side: Content & Features 
         * (Deep Navy Gradient with Circles like reference)
         */
        .login-content-side {
            flex: 1;
            /* Rich Blue Gradient with Strong Blend (Mid-tone to Deep) */
            background: linear-gradient(160deg, #1d4ed8 0%, #172554 100%);
            display: flex;
            flex-direction: column;
            justify-content: center;
            padding: 50px;
            color: white;
            position: relative;
            overflow: hidden; 
        }
        
        /* Top Left Bubble */
        .login-content-side::before {
            content: '';
            position: absolute;
            top: -120px;
            left: -120px;
            width: 350px;
            height: 350px;
            /* Solid low opacity circle, not radial gradient fading out */
            background: rgba(255, 255, 255, 0.03); 
            border-radius: 50%;
            z-index: 0;
        }

        /* Bottom Right Bubble */
        .login-content-side::after {
            content: '';
            position: absolute;
            bottom: -150px;
            right: -100px;
            width: 450px;
            height: 450px;
            background: rgba(255, 255, 255, 0.03);
            border-radius: 50%;
            z-index: 0;
        }
        .content-inner {
            position: relative;
            z-index: 1; 
            max-width: 400px;
            margin: 0 auto;
        }

        .content-title {
            font-size: 2rem;
            font-weight: 700;
            margin-bottom: 10px;
            line-height: 1.2;
        }
        
        .content-subtitle {
            font-size: 1rem;
            opacity: 0.9;
            margin-bottom: 30px;
            font-weight: 400;
        }

        .feature-item {
            /* Simple list style, no heavy glass effect */
            display: flex;
            align-items: center;
            gap: 15px;
            margin-bottom: 20px;
            border-bottom: 1px solid rgba(255,255,255,0.1);
            padding-bottom: 15px;
        }
        .feature-item:last-child { border: 0; }
        
        .feature-icon {
            background: rgba(255,255,255,0.2);
            color: white;
            width: 36px;
            height: 36px;
            border-radius: 8px; /* Slightly rounded square */
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1rem;
            flex-shrink: 0;
        }

        /* Right Side: Login Form */
        .login-form-side {
            flex: 1;
            background: var(--white);
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            padding: 50px;
        }

        .form-wrapper-width {
            width: 100%;
            max-width: 380px;
        }

        .brand-logo {
            height: 65px;
            margin-bottom: 20px;
            display: block;
            margin-left: auto;
            margin-right: auto;
        }
        
        .form-title {
            text-align: center;
            font-size: 1.5rem;
            font-weight: 700;
            margin-bottom: 30px;
        }
        
        .form-group { margin-bottom: 20px; }

        .form-label {
            font-size: 0.9rem;
            font-weight: 600;
            margin-bottom: 6px;
            display: block;
        }
        
        .form-control {
            width: 100%;
            padding: 10px 12px 10px 40px;
            border: 1px solid var(--border-color);
            border-radius: 6px; /* Simple radius */
            font-size: 0.95rem;
            transition: border-color 0.2s;
        }
        
        .form-control:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: none; /* Removed heavy glow */
        }
        
        .input-icon-wrap { position: relative; }
        
        .input-icon {
            position: absolute;
            left: 12px;
            top: 50%;
            transform: translateY(-50%);
            color: #94a3b8;
            font-size: 1rem;
        }
        
        .form-control:focus + .input-icon { color: var(--primary); }
        .input-icon-wrap:focus-within .input-icon { color: var(--primary); }

        .toggle-password {
            position: absolute;
            right: 12px;
            top: 50%;
            transform: translateY(-50%);
            color: #94a3b8;
            cursor: pointer;
        }

        .btn-primary {
            width: 100%;
            padding: 12px;
            background: var(--primary);
            color: white;
            border: none;
            border-radius: 6px;
            font-weight: 600;
            font-size: 1rem;
            cursor: pointer;
            transition: background 0.2s;
        }
        
        .btn-primary:hover {
            background: var(--primary-hover);
        }

        .divider {
            margin: 20px 0;
            text-align: center;
            font-size: 0.8rem;
            color: var(--text-muted);
            position: relative;
        }
        
        .divider:before, .divider:after {
            content: '';
            position: absolute;
            top: 50%;
            width: 35%;
            height: 1px;
            background: #e2e8f0;
        }
        .divider:before { left: 0; }
        .divider:after { right: 0; }

        .btn-google {
            width: 100%;
            padding: 10px;
            border: 1px solid #e2e8f0;
            border-radius: 6px;
            background: white;
            color: var(--text-main);
            font-weight: 500;
            font-size: 0.9rem;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            text-decoration: none;
            transition: all 0.2s;
        }
        
        .btn-google:hover {
            background: #ea4335; /* Google Red */
            color: white;
            border-color: #ea4335;
        }

        .checkbox-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            font-size: 0.9rem;
        }
        
        .forgot-link {
            color: var(--primary);
            text-decoration: none;
            font-weight: 500;
        }
        .forgot-link:hover { text-decoration: underline; }

        .custom-checkbox {
            display: flex;
            align-items: center;
            gap: 8px;
            cursor: pointer;
            user-select: none;
        }
        .custom-checkbox input { display: none; }
        .checkmark {
            width: 16px;
            height: 16px;
            border: 2px solid #cbd5e1;
            border-radius: 4px;
            position: relative;
            background: white;
        }
        .custom-checkbox input:checked + .checkmark {
            background: var(--primary);
            border-color: var(--primary);
        }
        .custom-checkbox input:checked + .checkmark::after {
            content: '';
            position: absolute;
            left: 5px;
            top: 1px;
            width: 4px;
            height: 8px;
            border: solid white;
            border-width: 0 2px 2px 0;
            transform: rotate(45deg);
            display: block;
        }

        .footer-copyright {
            margin-top: 30px;
            color: rgba(255, 255, 255, 0.7);
            font-size: 0.8rem;
        }
        .footer-copyright a {
            color: white;
            text-decoration: none;
        }

        @media (max-width: 900px) {
            .login-card-container {
                flex-direction: column;
                margin: 0;
                border-radius: 0;
            }
            .login-content-side { display: none; }
            .login-form-side { padding: 30px; }
        }
    </style>
</head>
<body>
        
{{ $slot }}
            
    </body>
</html>
