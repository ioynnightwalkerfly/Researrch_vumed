<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ศูนย์วิทยาการเสพติด มหาวิทยาลัยวงษ์ชวลิตกุล</title>
    
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Sarabun:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <link rel="stylesheet" href="css/output.css">
    
    <script src="https://unpkg.com/@phosphor-icons/web"></script>

    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['Sarabun', 'sans-serif'],
                    },
                    colors: {
                        // Color Palette based on ASC Brand
                        asc: {
                            green: '#083D2D',      // Main Dark Green
                            lightGreen: '#1A5D44', // Secondary Green
                            gold: '#BCC628',       // Accent Gold
                            goldHover: '#9FA91E',  // Darker Gold for hover
                            gray: '#F3F4F6',       // Light Gray Background
                        }
                    },
                    animation: {
                        'fade-in': 'fadeIn 0.5s ease-in-out',
                    },
                    keyframes: {
                        fadeIn: {
                            '0%': { opacity: '0' },
                            '100%': { opacity: '1' },
                        }
                    }
                }
            }
        }
    </script>

    <style>
        /* Custom Styles */
        body { font-family: 'Sarabun', sans-serif; }
        
        .glass-panel {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
        }
        
        /* Custom Scrollbar */
        ::-webkit-scrollbar { width: 8px; }
        ::-webkit-scrollbar-track { background: #f1f1f1; }
        ::-webkit-scrollbar-thumb { background: #083D2D; border-radius: 4px; }
        ::-webkit-scrollbar-thumb:hover { background: #BCC628; }
    </style>
</head>
<body class="bg-gray-50 text-gray-800 flex flex-col min-h-screen"></body>