    <!-- Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    
    <!-- KayXchange Navbar Styling -->
    <link href="https://fonts.googleapis.com/css?family=Open+Sans:300,300i,400,400i,600,600i,700,700i|Nunito:300,300i,400,400i,600,600i,700,700i|Poppins:300,300i,400,400i,500,500i,600,600i,700,700i" rel="stylesheet">
    <link href="{{ asset('assets/vendor/aos/aos.css') }}" rel="stylesheet">
    <link href="{{ asset('assets/vendor/bootstrap-icons/bootstrap-icons.css') }}" rel="stylesheet">
    <link href="{{ asset('assets/vendor/glightbox/css/glightbox.min.css') }}" rel="stylesheet">
    <link href="{{ asset('assets/vendor/remixicon/remixicon.css') }}" rel="stylesheet">
    <link href="{{ asset('assets/vendor/swiper/swiper-bundle.min.css') }}" rel="stylesheet">
    <link href="{{ asset('assets/css/style.css') }}" rel="stylesheet">
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    
    <!-- Custom Navbar Enhancements -->
    <style>
        /* Enhanced navbar styling with Tailwind support */
        .header {
            transition: all 0.3s ease;
            z-index: 997;
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            box-shadow: 0 2px 20px rgba(0, 0, 0, 0.1);
        }
        
        .navbar ul li a.active {
            color: #00cc00 !important;
            font-weight: 600;
        }
        
        .getstarted {
            background: linear-gradient(135deg, #00cc00 0%, #00b300 100%);
            color: white;
            padding: 10px 20px;
            border-radius: 25px;
            border: none;
            font-weight: 600;
            transition: all 0.3s ease;
            text-decoration: none;
            display: inline-block;
        }
        
        .getstarted:hover {
            background: linear-gradient(135deg, #00b300 0%, #009900 100%);
            color: white;
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0, 204, 0, 0.3);
            text-decoration: none;
        }
        
        /* Mobile navbar improvements */
        @media (max-width: 768px) {
            .navbar ul {
                padding: 20px 0;
            }
            
            .navbar ul li {
                margin: 5px 0;
            }
            
            .getstarted {
                margin: 10px 0;
                display: block;
                text-align: center;
            }
        }
    </style>