<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Workspace Dashboard</title>
    <link href="{{asset('plugins/bootstrap.min.css')}}" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="shortcut icon" type="image/x-icon" href="{{ URL::to('/images/isarvafavicon.png') }}">
    <script src="{{asset('plugins/bootstrap.bundle.min.js')}}"></script>
    <style>
        :root {
            --payroll: #003366;
            --attendance: #2c5282;
            --payroll-light: #004080;
            --attendance-light: #3182ce;
            --dark: #1e293b;
            --light: #f8fafc;
            --gray: #64748b;
            --light-gray: #e2e8f0;
            --danger: #ef4444;
            --glass: rgba(255, 255, 255, 0.85);
            --card-radius: 20px;
        }
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Poppins', sans-serif;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            color: var(--dark);
            background: 
                linear-gradient(rgba(0, 20, 40, 0.85), rgba(0, 30, 60, 0.9)),
              	url('/images/background-image-hrms.avif');
               
            background-size: cover;
            background-position: center;
            background-attachment: fixed;
            position: relative;
            overflow-x: hidden;
        }
        
        /* Animated background elements */
        .bg-element {
            position: absolute;
            border-radius: 50%;
            z-index: -1;
            animation: float 15s infinite ease-in-out;
        }
        
        .bg-element:nth-child(1) {
            width: 300px;
            height: 300px;
            background: rgba(0, 51, 102, 0.15);
            top: 10%;
            left: 5%;
            animation-delay: 0s;
        }
        
        .bg-element:nth-child(2) {
            width: 200px;
            height: 200px;
            background: rgba(44, 82, 130, 0.15);
            bottom: 15%;
            right: 5%;
            animation-delay: 2s;
        }
        
        .bg-element:nth-child(3) {
            width: 150px;
            height: 150px;
            background: rgba(0, 64, 128, 0.15);
            top: 40%;
            right: 20%;
            animation-delay: 4s;
        }
        
        @keyframes float {
            0%, 100% { transform: translate(0, 0); }
            25% { transform: translate(20px, 20px); }
            50% { transform: translate(-20px, 10px); }
            75% { transform: translate(10px, -20px); }
        }
        
        .dashboard-header {
            background: rgba(0, 30, 60, 0.7);
            color: white;
            padding: 20px 0;
            backdrop-filter: blur(10px);
            -webkit-backdrop-filter: blur(10px);
            box-shadow: 0 4px 30px rgba(0, 0, 0, 0.2);
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        }
        
        .app-container {
            display: flex;
            justify-content: center;
            align-items: center;
            flex-grow: 1;
            padding: 30px 20px;
            min-height: calc(100vh - 76px);
        }
        
        .app-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 35px;
            max-width: 900px;
            width: 100%;
        }
        
        .app-card {
            background: var(--glass);
            border-radius: var(--card-radius);
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.15);
            transition: all 0.4s ease;
            padding: 35px 30px;
            display: flex;
            flex-direction: column;
            align-items: center;
            text-align: center;
            border: 1px solid rgba(255, 255, 255, 0.25);
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
            transform: translateY(0);
            position: relative;
            overflow: hidden;
        }
        
        .app-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 6px;
            background: linear-gradient(90deg, var(--payroll), var(--attendance));
            opacity: 0.8;
            transition: height 0.3s ease;
        }
        
        .app-card:hover::before {
            height: 100%;
            opacity: 0.1;
        }
        
        .app-card:hover {
            transform: translateY(-12px);
            box-shadow: 0 20px 50px rgba(0, 0, 0, 0.25);
            background: rgba(255, 255, 255, 0.92);
            border-color: rgba(255, 255, 255, 0.4);
        }
        
        .payroll-card .card-icon {
            background: linear-gradient(135deg, var(--payroll) 0%, var(--payroll-light) 100%);
            box-shadow: 0 10px 25px rgba(0, 51, 102, 0.4);
        }
        
        .attendance-card .card-icon {
            background: linear-gradient(135deg, var(--payroll) 0%, var(--payroll-light) 100%);
            box-shadow: 0 10px 25px rgba(0, 51, 102, 0.4);
        }
        
        .card-icon {
            width: 90px;
            height: 90px;
            border-radius: 22px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 25px;
            font-size: 36px;
            color: white;
            transition: all 0.4s ease;
            z-index: 1;
        }
        
        .app-card:hover .card-icon {
            transform: scale(1.08) rotate(5deg);
        }
        
        .app-card h3 {
            font-size: 1.7rem;
            margin-bottom: 20px;
            font-weight: 700;
            color: var(--payroll);
            z-index: 1;
        }
        
        .attendance-card h3 {
            color: var(--payroll);
        }
        
        .card-desc {
            color: #4b5563;
            margin-bottom: 25px;
            font-size: 1.05rem;
            line-height: 1.6;
            z-index: 1;
        }
        
        .features {
            display: flex;
            flex-wrap: wrap;
            justify-content: center;
            gap: 12px;
            margin-bottom: 25px;
            z-index: 1;
        }
        
        .feature-tag {
            background: rgba(0, 51, 102, 0.1);
            color: var(--payroll);
            padding: 6px 14px;
            border-radius: 50px;
            font-size: 0.85rem;
            font-weight: 500;
        }
        
        .attendance-card .feature-tag {
            background: rgba(44, 82, 130, 0.1);
            color: var(--attendance);
        }
        
        .btn-app {
            padding: 14px 35px;
            border-radius: 14px;
            font-weight: 600;
            font-size: 1.1rem;
            border: none;
            transition: all 0.3s;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            margin-top: 10px;
            width: 100%;
           /* max-width: 220px; */
            position: relative;
            overflow: hidden;
            z-index: 1;
        }
        
        .btn-app span {
            position: relative;
            z-index: 2;
        }
        
        .btn-app:before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(255, 255, 255, 0.2);
            transform: scaleX(0);
            transform-origin: right;
            transition: transform 0.4s ease;
            z-index: 1;
        }
        
        .btn-app:hover:before {
            transform: scaleX(1);
            transform-origin: left;
        }
        
        .btn-payroll {
            background: linear-gradient(135deg, var(--payroll) 0%, var(--payroll-light) 100%);
            color: white;
        }
        
        .btn-payroll:hover {
            background: linear-gradient(135deg, var(--payroll-light) 0%, var(--payroll) 100%);
            transform: translateY(-4px);
            box-shadow: 0 8px 20px rgba(0, 51, 102, 0.4);
            color: white;
        }
        
        .btn-attendance {
            background: linear-gradient(135deg, var(--payroll) 0%, var(--payroll-light) 100%);
            color: white;
        }
        
        .btn-attendance:hover {
            background: linear-gradient(135deg, var(--payroll-light) 0%, var(--payroll) 100%);
            transform: translateY(-4px);
            box-shadow: 0 8px 20px rgba(0, 51, 102, 0.4);
            color: white;
        }
        
        .logout-btn {
            background: rgba(255, 255, 255, 0.15);
            border: 2px solid rgba(255, 255, 255, 0.25);
            color: white;
            border-radius: 14px;
            padding: 10px 25px;
            transition: all 0.3s;
            font-weight: 500;
            display: flex;
            align-items: center;
            backdrop-filter: blur(5px);
            -webkit-backdrop-filter: blur(5px);
        }
        
        .logout-btn:hover {
            background: rgba(255, 255, 255, 0.25);
            border-color: rgba(255, 255, 255, 0.4);
            transform: translateY(-2px);
        }
        
        .brand-text {
            font-weight: 700;
            font-size: 1.8rem;
            letter-spacing: -0.5px;
        }
        
        .brand-text span:first-child {
            color: white;
            text-shadow: 0 2px 6px rgba(0, 0, 0, 0.2);
        }
        
        .brand-text span:last-child {
            color: rgba(255, 255, 255, 0.85);
            font-weight: 400;
        }
        
        .dashboard-footer {
            text-align: center;
            padding: 20px;
            color: rgba(255, 255, 255, 0.7);
            font-size: 0.95rem;
            background: rgba(0, 30, 60, 0.5);
            backdrop-filter: blur(5px);
            -webkit-backdrop-filter: blur(5px);
            border-top: 1px solid rgba(255, 255, 255, 0.1);
        }
        
        @media (max-width: 768px) {
            .app-grid {
                grid-template-columns: 1fr;
                max-width: 500px;
            }
            
            .app-card {
                padding: 30px 25px;
            }
            
            .card-icon {
                width: 80px;
                height: 80px;
                font-size: 32px;
            }
            
            .app-card h3 {
                font-size: 1.5rem;
            }
            
            .brand-text {
                font-size: 1.5rem;
            }
        }
        
        @media (max-width: 480px) {
            .app-card {
                padding: 25px 20px;
            }
            
            .card-icon {
                width: 75px;
                height: 75px;
                font-size: 30px;
                margin-bottom: 25px;
            }
            
            .app-card h3 {
                font-size: 1.4rem;
            }
            
            .btn-app {
                padding: 13px 25px;
                font-size: 1rem;
            }
        }
    </style>
</head>
<body>
    
                {{ $slot }}
            
    </body>
</html>
