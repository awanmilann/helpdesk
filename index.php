<?php
require_once 'config.php';
// Cache-busting for logo image so updates appear immediately
$logoPath = 'assets/images/Logo BVT - Primary.png';
$logoUrl = $logoPath . (file_exists($logoPath) ? ('?v=' . filemtime($logoPath)) : '');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>IT Helpdesk System - Bamboo Village Trust</title>
    <link rel="icon" type="image/png" href="assets/images/Logo BVT - Primary.png">
    <link rel="apple-touch-icon" href="assets/images/Logo BVT - Primary.png">
    <script src="https://cdn.tailwindcss.com/3.3.0"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="user-dashboard.css">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap');
        body { font-family: 'Inter', sans-serif; background-color: #f3f4f6; }
        
        /* Body background when app-container is visible */
        body:has(#app-container:not(.hidden)) {
            background-color: transparent;
        }
        .fade-in { animation: fadeIn 0.5s ease-in-out; }
        @keyframes fadeIn { from { opacity: 0; transform: translateY(-10px); } to { opacity: 1; transform: translateY(0); } }
        .hidden { display: none; }
        .view-section { animation: fadeIn 0.3s ease-in-out; }
        .nav-tab { cursor: pointer; padding: 0.75rem 1rem; border-bottom: 2px solid transparent; font-weight: 500; color: #6b7280; }
        .nav-tab.active { border-color: #22c55e; color: #16a34a; font-weight: 600; }
        
        /* Sticky Navigation Tabs untuk Admin dan User */
        #user-view > nav,
        #admin-view > nav {
            position: sticky !important;
            top: 0 !important; /* Sticky di top saat scroll, akan berada di bawah header utama */
            z-index: 50 !important;
            margin-bottom: 1.5rem !important;
            margin-top: 0 !important;
            padding: 0.25rem !important;
            border-radius: 0.5rem 0.5rem 0 0 !important;
            background: rgba(255, 255, 255, 0.95) !important;
            backdrop-filter: blur(10px) !important;
            border: 1px solid rgba(255, 255, 255, 0.3) !important;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1) !important;
        }
        
        /* Untuk memastikan nav tabs tetap di bawah header saat scroll */
        main #user-view > nav,
        main #admin-view > nav {
            position: sticky !important;
            top: 0 !important;
        }
        
        /* Pastikan header utama selalu di atas navigation tabs */
        #app-container > header {
            position: sticky !important;
            top: 0 !important;
            z-index: 100 !important;
        }
        
        /* Untuk User View: Welcome Header juga sticky */
        #user-view > div.mb-6:first-child {
            position: sticky !important;
            top: 0 !important;
            z-index: 45 !important;
            margin-bottom: 0 !important;
        }
        
        /* Navigation tabs user harus di bawah welcome header - hitung tinggi welcome header */
        #user-view > nav {
            position: sticky !important;
            top: 0 !important;
            margin-top: 0 !important;
            z-index: 50 !important;
        }
        
        /* Jika welcome header ada, nav tabs harus di bawahnya */
        #user-view > div.mb-6:first-child ~ nav {
            position: sticky !important;
            top: 0 !important;
        }
        .chart-container { position: relative; }
        #category-description {
            background-color: #f9fafb;
            border: 1px solid #e5e7eb;
            border-radius: 0.5rem;
            padding: 0.75rem;
            font-size: 0.875rem;
            color: #4b5563;
            min-height: 80px;
        }
        .modal { 
            display: none; 
            position: fixed; 
            z-index: 1000 !important; 
            left: 0; 
            top: 0; 
            width: 100%; 
            height: 100%; 
            overflow: auto; 
            background-color: rgba(0,0,0,0.6); 
        }
        .modal.active { 
            display: flex; 
            align-items: center; 
            justify-content: center; 
            padding: 1rem; 
        }
        .modal-content {
            position: relative;
            background-color: white;
            border-radius: 0.5rem;
            box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
            width: 100%;
            max-width: 95%;
            max-height: 95vh;
            display: flex;
            flex-direction: column;
            overflow: hidden;
        }
        @media (max-width: 768px) {
            .modal-content {
                max-width: 100%;
                max-height: 100vh;
                border-radius: 0;
            }
            .modal.active {
                padding: 0;
            }
        }
        .ticket-highlight {
            background-color: #fef3c7 !important;
            border-left: 4px solid #f59e0b !important;
            animation: highlightPulse 2s ease-in-out;
        }
        @keyframes highlightPulse {
            0% { background-color: #fef3c7; }
            50% { background-color: #fde68a; }
            100% { background-color: #fef3c7; }
        }
        .chart-clickable {
            cursor: pointer;
        }
        
        /* Stats Slider Styles */
        #stats-container {
            position: relative;
        }
        
        #stats-slider {
            display: flex;
            transition: transform 0.3s ease-in-out;
        }
        
        .stats-nav-btn {
            position: absolute;
            top: 50%;
            transform: translateY(-50%);
            z-index: 10;
            background: white;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
            border-radius: 50%;
            width: 40px;
            height: 40px;
            display: flex;
            align-items: center;
            justify-content: center;
            border: none;
            cursor: pointer;
            transition: all 0.2s ease;
        }
        
        .stats-nav-btn:hover:not(:disabled) {
            background: #f9fafb;
            box-shadow: 0 6px 12px -2px rgba(0, 0, 0, 0.15);
        }
        
        .stats-nav-btn:disabled {
            opacity: 0.5;
            cursor: not-allowed;
        }
        
        #stats-prev {
            left: -20px;
        }
        
        #stats-next {
            right: -20px;
        }
        
        /* Responsive adjustments for slider */
        @media (max-width: 768px) {
            #stats-prev {
                left: -15px;
            }
            
            #stats-next {
                right: -15px;
            }
            
            .stats-nav-btn {
                width: 35px;
                height: 35px;
            }
        }
        
        /* Logo styling */
        .logo-img {
            max-width: 100%;
            max-height: 100%;
            object-fit: contain;
        }
        
        .logo-container {
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        /* Login/Signup logo styling */
        .auth-logo-container {
            width: 80px;
            height: 80px;
            margin: 0 auto 1rem auto;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 12px;
            background: linear-gradient(135deg, #f8fafc 0%, #e2e8f0 100%);
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
        }
        
        /* Dashboard header logo styling */
        .header-logo-container {
            width: 48px;
            height: 48px;
            margin-right: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 10px;
            background: #ffffff;
            box-shadow: 0 2px 6px rgba(0, 0, 0, 0.1);
            border: 1px solid #e5e7eb;
            padding: 4px;
        }
        
        .header-logo-container img {
            width: 100%;
            height: 100%;
            object-fit: contain;
        }
        
        /* User dashboard specific styles */
        .user-tab-content {
            animation: fadeIn 0.3s ease-in-out;
        }
        
        .ticket-card {
            transition: all 0.3s ease;
        }
        
        .ticket-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.1);
        }
        
        .notification {
            backdrop-filter: blur(10px);
            z-index: 9999 !important;
        }
        
        /* Status indicator animations */
        .status-indicator {
            position: relative;
            overflow: hidden;
        }
        
        .status-indicator::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255,255,255,0.3), transparent);
            transition: left 0.5s;
        }
        
        .status-indicator:hover::before {
            left: 100%;
        }
        
        /* Improved modal styling */
        .modal .bg-white {
            max-height: 90vh;
            overflow-y: auto;
        }
        
        /* Loading spinner */
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
        
        .fa-spin {
            animation: spin 1s linear infinite;
        }
        
        /* Fix for description column - ensure full text is visible */
        #all-tickets-table td:nth-child(4) {
            white-space: normal !important;
            word-wrap: break-word !important;
            overflow-wrap: break-word !important;
            overflow: visible !important;
            text-overflow: unset !important;
            max-width: 600px !important;
            min-width: 350px !important;
        }
        
        #all-tickets-table td:nth-child(4) div {
            white-space: pre-wrap !important;
            word-wrap: break-word !important;
            overflow-wrap: break-word !important;
            max-width: 100% !important;
            overflow: visible !important;
        }
        
        /* Ensure horizontal scroll works properly */
        #admin-all-tickets-view .overflow-x-auto {
            position: relative;
            width: 100%;
        }
        
        /* Ensure main container doesn't restrict scrolling */
        main {
            overflow-x: visible;
            overflow-y: auto;
        }
        
        #admin-all-tickets-view {
            overflow-x: visible;
            overflow-y: visible;
        }
        
        /* Sticky Header untuk Tabel All Tickets */
        .sticky-header {
            position: sticky;
            top: 0;
            z-index: 10;
        }
        
        .sticky-header th {
            background-color: #f9fafb;
            position: relative;
            box-shadow: 0 2px 2px -1px rgba(0, 0, 0, 0.1);
        }
        
        /* Sticky Scrollbar - Scrollbar horizontal lebih mudah diakses */
        .sticky-scrollbar {
            scrollbar-width: thin;
            scrollbar-color: #22c55e #f3f4f6;
        }
        
        .sticky-scrollbar::-webkit-scrollbar {
            height: 12px;
        }
        
        .sticky-scrollbar::-webkit-scrollbar-track {
            background: #f3f4f6;
            border-radius: 6px;
            margin: 0 4px;
        }
        
        .sticky-scrollbar::-webkit-scrollbar-thumb {
            background: #22c55e;
            border-radius: 6px;
            border: 2px solid #f3f4f6;
        }
        
        .sticky-scrollbar::-webkit-scrollbar-thumb:hover {
            background: #16a34a;
        }
        
        /* Horizontal scrollbar selalu visible dan accessible */
        /* Container table dengan flexbox untuk memastikan horizontal scrollbar selalu terlihat */
        #admin-all-tickets-view > div > div {
            /* Outer container menggunakan flexbox */
            display: flex;
            flex-direction: column;
            height: 100%;
        }
        
        #admin-all-tickets-view .sticky-scrollbar {
            /* Inner container dengan horizontal scroll - selalu terlihat di viewport */
            position: relative;
            flex: 1;
            overflow-y: auto;
            overflow-x: auto;
            /* Scrollbar horizontal akan selalu visible di bagian bawah viewport */
            padding-bottom: 2px;
        }
        
        /* Memastikan scrollbar horizontal selalu di posisi yang mudah diakses */
        #admin-all-tickets-view .sticky-scrollbar::-webkit-scrollbar:horizontal {
            position: sticky;
            bottom: 0;
            height: 14px;
            z-index: 5;
        }
        
        /* Scrollbar horizontal lebih visible */
        #admin-all-tickets-view .sticky-scrollbar {
            scrollbar-width: thin;
            scrollbar-color: #22c55e #f3f4f6;
        }
        
        #admin-all-tickets-view .sticky-scrollbar::-webkit-scrollbar {
            height: 14px;
            width: 100%;
        }
        
        #admin-all-tickets-view .sticky-scrollbar::-webkit-scrollbar-track {
            background: #f3f4f6;
            border-radius: 7px;
            margin: 0 4px;
        }
        
        #admin-all-tickets-view .sticky-scrollbar::-webkit-scrollbar-thumb {
            background: #22c55e;
            border-radius: 7px;
            border: 2px solid #f3f4f6;
        }
        
        #admin-all-tickets-view .sticky-scrollbar::-webkit-scrollbar-thumb:hover {
            background: #16a34a;
        }
        
        /* Mobile Responsive Styles */
        @media (max-width: 768px) {
            /* Header adjustments */
            header {
                flex-direction: column;
                padding: 1rem;
            }
            
            header > div:first-child {
                margin-bottom: 1rem;
            }
            
            /* Navigation tabs - stack on mobile */
            .nav-tab {
                font-size: 0.875rem;
                padding: 0.5rem 0.75rem;
            }
            
            /* Table container - improved horizontal scroll */
            #admin-all-tickets-view .overflow-x-auto {
                -webkit-overflow-scrolling: touch;
                overflow-x: auto !important;
                overflow-y: visible !important;
                width: 100%;
                display: block;
                position: relative;
                scroll-behavior: smooth;
                /* Make scrollbar more visible on mobile */
                scrollbar-width: thin;
                scrollbar-color: #22c55e #f3f4f6;
            }
            
            #admin-all-tickets-view .overflow-x-auto::-webkit-scrollbar {
                height: 8px;
            }
            
            #admin-all-tickets-view .overflow-x-auto::-webkit-scrollbar-track {
                background: #f3f4f6;
                border-radius: 4px;
            }
            
            #admin-all-tickets-view .overflow-x-auto::-webkit-scrollbar-thumb {
                background: #22c55e;
                border-radius: 4px;
            }
            
            /* Table responsive */
            #admin-all-tickets-view table {
                min-width: 1400px;
                font-size: 0.75rem;
            }
            
            #admin-all-tickets-view th,
            #admin-all-tickets-view td {
                padding: 0.5rem 0.25rem;
                white-space: nowrap;
            }
            
            /* Description column - wider on mobile */
            #all-tickets-table td:nth-child(4) {
                min-width: 200px !important;
                max-width: 250px !important;
                white-space: normal !important;
                word-wrap: break-word !important;
            }
            
            /* Buttons in table - smaller */
            #all-tickets-table button {
                padding: 0.25rem 0.5rem;
                font-size: 0.625rem;
            }
            
            /* Select dropdowns - smaller */
            #all-tickets-table select {
                padding: 0.25rem;
                font-size: 0.625rem;
            }
            
            /* Modal adjustments */
            .modal .bg-white {
                width: 95%;
                max-width: 95%;
                margin: 1rem auto;
            }
            
            /* Stats cards - stack on mobile */
            .grid {
                grid-template-columns: 1fr;
            }
            
            /* Export buttons - stack */
            #admin-all-tickets-view .flex.gap-2 {
                flex-direction: column;
            }
            
            #admin-all-tickets-view .flex.gap-2 button {
                width: 100%;
                margin-bottom: 0.5rem;
            }
        }
        
        @media (max-width: 480px) {
            /* Extra small screens */
            #admin-all-tickets-view table {
                min-width: 1200px;
                font-size: 0.7rem;
            }
            
            #all-tickets-table td:nth-child(4) {
                min-width: 150px !important;
                max-width: 200px !important;
            }
        }
        
        /* Panduan/Guideline Page Styling */
        .panduan-iframe-container {
            background: #f9fafb;
            overflow: hidden;
        }
        
        #panduan-iframe {
            border-radius: 0 0 0.75rem 0.75rem;
        }
        
        @media (max-width: 768px) {
            .panduan-iframe-container {
                height: calc(100vh - 200px) !important;
                min-height: 500px !important;
            }
        }
        
        @media (max-width: 640px) {
            .panduan-iframe-container {
                height: calc(100vh - 180px) !important;
                min-height: 400px !important;
            }
        }
        
        /* Auth Container with Bamboo Background */
        #auth-container {
            background-image: url('assets/images/back.jpg');
            background-size: cover;
            background-position: center;
            background-repeat: no-repeat;
            background-attachment: fixed;
            position: relative;
        }
        
        #auth-container::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: linear-gradient(135deg, rgba(34, 197, 94, 0.65) 0%, rgba(16, 185, 129, 0.65) 50%, rgba(5, 150, 105, 0.70) 100%);
            z-index: 0;
        }
        
        #auth-container > div {
            position: relative;
            z-index: 1;
        }
        
        /* Auth Card Styling */
        .auth-card {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.3);
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1);
            transition: all 0.3s ease;
        }
        
        .auth-card:hover {
            box-shadow: 0 12px 40px rgba(0, 0, 0, 0.15);
            transform: translateY(-2px);
        }
        
        /* Auth Logo Container */
        .auth-logo-container {
            margin-bottom: 1.5rem;
        }
        
        .auth-logo-container .logo-img {
            max-height: 80px;
            width: auto;
            filter: drop-shadow(0 4px 6px rgba(0, 0, 0, 0.1));
        }
        
        /* Auth Form Inputs */
        .auth-input {
            background: rgba(255, 255, 255, 0.9);
            border: 2px solid rgba(79, 70, 229, 0.2);
            transition: all 0.3s ease;
        }
        
        .auth-input:focus {
            background: rgba(255, 255, 255, 1);
            border-color: #22c55e;
            box-shadow: 0 0 0 3px rgba(34, 197, 94, 0.1);
        }
        
        /* Auth Buttons */
        .auth-btn-primary {
            background: linear-gradient(135deg, #22c55e 0%, #10b981 50%, #059669 100%);
            transition: all 0.3s ease;
            box-shadow: 0 4px 15px rgba(34, 197, 94, 0.4);
        }
        
        .auth-btn-primary:hover {
            background: linear-gradient(135deg, #16a34a 0%, #059669 50%, #047857 100%);
            box-shadow: 0 6px 20px rgba(34, 197, 94, 0.5);
            transform: translateY(-1px);
        }
        
        .auth-btn-primary:active {
            transform: translateY(0);
        }
        
        /* Auth Links */
        .auth-link {
            color: #22c55e;
            font-weight: 500;
            transition: all 0.2s ease;
            text-decoration: none;
        }
        
        .auth-link:hover {
            color: #16a34a;
            text-decoration: underline;
        }
        
        /* Auth Title */
        .auth-title {
            background: linear-gradient(135deg, #22c55e 0%, #10b981 50%, #059669 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            font-weight: 700;
        }
        
        /* Success/Error Messages */
        .auth-message {
            padding: 0.75rem 1rem;
            border-radius: 0.5rem;
            font-size: 0.875rem;
            font-weight: 500;
        }
        
        .auth-message.success {
            background: rgba(16, 185, 129, 0.1);
            color: #059669;
            border: 1px solid rgba(16, 185, 129, 0.3);
        }
        
        .auth-message.error {
            background: rgba(239, 68, 68, 0.1);
            color: #dc2626;
            border: 1px solid rgba(239, 68, 68, 0.3);
        }
        
        /* Dashboard Background with Bamboo */
        #app-container {
            background-image: url('assets/images/back.jpg');
            background-size: cover;
            background-position: center;
            background-repeat: no-repeat;
            background-attachment: fixed;
            min-height: 100vh;
            position: relative;
        }
        
        #app-container::before {
            content: '';
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: linear-gradient(135deg, rgba(34, 197, 94, 0.65) 0%, rgba(16, 185, 129, 0.65) 50%, rgba(5, 150, 105, 0.70) 100%);
            z-index: 0;
            pointer-events: none;
        }
        
        #app-container > header,
        #app-container > main {
            position: relative;
            z-index: 10;
        }
        
        /* Glassmorphism Header - SOLID WHITE */
        #app-container > header {
            background: #ffffff !important;
            background-color: #ffffff !important;
            backdrop-filter: none !important;
            -webkit-backdrop-filter: none !important;
            border-bottom: 1px solid #e5e7eb !important;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08) !important;
            z-index: 1000 !important;
            position: sticky !important;
            top: 0 !important;
            opacity: 1 !important;
            isolation: isolate;
        }
        
        /* Pastikan header tidak transparan di semua kondisi */
        #app-container > header,
        #app-container > header * {
            opacity: 1 !important;
        }
        
        /* Override semua kemungkinan transparansi pada header */
        header.p-4,
        header[class*="sticky"],
        #main-content-wrapper > header {
            background: #ffffff !important;
            background-color: #ffffff !important;
            opacity: 1 !important;
            backdrop-filter: none !important;
            -webkit-backdrop-filter: none !important;
        }
        
        /* Pastikan header menutupi background pattern dengan layer solid */
        #app-container > header::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: #ffffff;
            z-index: -1;
            pointer-events: none;
        }
        
        /* Glassmorphism Cards */
        .dashboard-card {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.3);
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1);
            transition: all 0.3s ease;
        }
        
        .dashboard-card:hover {
            background: rgba(255, 255, 255, 1);
            box-shadow: 0 12px 40px rgba(0, 0, 0, 0.15);
            transform: translateY(-2px);
        }
        
        /* Navigation Tabs with Glassmorphism - Already handled above */
        
        /* Stats Cards Enhanced */
        .stat-card {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.3);
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1);
            transition: all 0.3s ease;
            cursor: pointer;
        }
        
        .stat-card:hover {
            background: rgba(255, 255, 255, 1);
            box-shadow: 0 12px 40px rgba(0, 0, 0, 0.15);
            transform: translateY(-4px) scale(1.02);
        }
        
        /* Welcome Header Enhanced */
        .welcome-header {
            background: linear-gradient(135deg, rgba(34, 197, 94, 0.85) 0%, rgba(16, 185, 129, 0.85) 50%, rgba(5, 150, 105, 0.90) 100%);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.3);
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.2);
        }
        
        /* Form Container */
        .form-container {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.3);
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1);
        }
        
        /* Table Container */
        .table-container {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.3);
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1);
        }
        
        /* Notification Dropdown */
        #notif-dropdown {
            background: rgba(255, 255, 255, 0.98) !important;
            backdrop-filter: blur(15px);
            border: 1px solid rgba(255, 255, 255, 0.3);
            z-index: 10000 !important;
            position: fixed !important;
        }
        
        /* Buttons Enhanced */
        .btn-primary {
            background: linear-gradient(135deg, #22c55e 0%, #10b981 50%, #059669 100%);
            box-shadow: 0 4px 15px rgba(34, 197, 94, 0.4);
            transition: all 0.3s ease;
        }
        
        .btn-primary:hover {
            background: linear-gradient(135deg, #16a34a 0%, #059669 50%, #047857 100%);
            box-shadow: 0 6px 20px rgba(34, 197, 94, 0.5);
            transform: translateY(-1px);
        }
        
        /* Input Fields Enhanced */
        input, select, textarea {
            background: rgba(255, 255, 255, 0.9);
            border: 2px solid rgba(34, 197, 94, 0.2);
            transition: all 0.3s ease;
        }
        
        input:focus, select:focus, textarea:focus {
            background: rgba(255, 255, 255, 1);
            border-color: #22c55e;
            box-shadow: 0 0 0 3px rgba(34, 197, 94, 0.1);
        }
        
        /* Ticket Cards */
        .ticket-card {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.3);
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
            transition: all 0.3s ease;
        }
        
        .ticket-card:hover {
            background: rgba(255, 255, 255, 1);
            box-shadow: 0 8px 30px rgba(0, 0, 0, 0.15);
            transform: translateY(-2px);
        }
        
        /* Chart Container */
        .chart-container {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.3);
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1);
            position: relative;
        }
        
        /* Stats Navigation Buttons Enhanced */
        .stats-nav-btn {
            background: rgba(255, 255, 255, 0.95) !important;
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.3);
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.15);
        }
        
        .stats-nav-btn:hover:not(:disabled) {
            background: rgba(255, 255, 255, 1) !important;
            box-shadow: 0 6px 20px rgba(0, 0, 0, 0.2);
        }
        
        /* Table Styling Enhanced */
        table thead {
            background: rgba(249, 250, 251, 0.95);
            backdrop-filter: blur(10px);
        }
        
        /* Modal Enhanced */
        .modal {
            background-color: rgba(0, 0, 0, 0.7) !important;
            backdrop-filter: blur(5px);
            z-index: 1000 !important;
        }
        
        /* Pastikan modal content juga memiliki z-index yang tinggi */
        .modal-content {
            z-index: 1001 !important;
            position: relative !important;
        }
        
        /* Report modal khusus - pastikan header modal tidak tertutup */
        #report-view-modal {
            z-index: 1000 !important;
        }
        
        #report-view-modal .modal-content {
            z-index: 1001 !important;
        }
        
        #report-view-modal .bg-white {
            z-index: 1002 !important;
        }
        
        .modal .bg-white {
            background: rgba(255, 255, 255, 0.98) !important;
            backdrop-filter: blur(15px);
            border: 1px solid rgba(255, 255, 255, 0.3);
        }
        
        /* Report Modal Specific Styles */
        #report-view-modal .modal-content {
            display: flex !important;
            flex-direction: column !important;
            height: 90vh !important;
            max-height: 90vh !important;
        }
        
        #report-view-modal .bg-white {
            display: flex !important;
            flex-direction: column !important;
            height: 100% !important;
            max-height: 100% !important;
            overflow: hidden !important;
        }
        
        #report-content {
            display: flex !important;
            flex-direction: column !important;
            height: 100% !important;
            min-height: 0 !important;
            max-height: 100% !important;
            overflow: hidden !important;
        }
        
        #report-content .report-header {
            flex-shrink: 0 !important;
            padding: 1.5rem !important;
            margin-bottom: 1.5rem !important;
            overflow: visible !important;
            width: 100% !important;
            box-sizing: border-box !important;
        }
        
        #report-content .report-header h3,
        #report-content .report-header p {
            word-wrap: break-word !important;
            overflow-wrap: break-word !important;
            white-space: normal !important;
            line-height: 1.5 !important;
        }
        
        /* Report table container - menggunakan struktur sama seperti All Tickets */
        #report-content .sticky-scrollbar {
            position: relative !important;
            flex: 1 !important;
            min-height: 0 !important;
            overflow-y: auto !important;
            overflow-x: auto !important;
            -webkit-overflow-scrolling: touch !important;
            padding-bottom: 2px !important;
            max-height: 100% !important;
        }
        
        #report-content .sticky-scrollbar table {
            margin: 0 !important;
        }
        
        #report-content .sticky-scrollbar thead.sticky-header {
            position: sticky !important;
            top: 0 !important;
            z-index: 10 !important;
        }
        
        #report-content .sticky-scrollbar thead.sticky-header th {
            background-color: #f9fafb !important;
            position: relative !important;
            box-shadow: 0 2px 2px -1px rgba(0, 0, 0, 0.1) !important;
        }
        
        /* Scrollbar styling - sama seperti All Tickets */
        #report-content .sticky-scrollbar {
            scrollbar-width: thin !important;
            scrollbar-color: #22c55e #f3f4f6 !important;
        }
        
        #report-content .sticky-scrollbar::-webkit-scrollbar {
            height: 12px !important;
            width: 12px !important;
        }
        
        #report-content .sticky-scrollbar::-webkit-scrollbar-track {
            background: #f3f4f6 !important;
            border-radius: 6px !important;
            margin: 0 4px !important;
        }
        
        #report-content .sticky-scrollbar::-webkit-scrollbar-thumb {
            background: #22c55e !important;
            border-radius: 6px !important;
            border: 2px solid #f3f4f6 !important;
        }
        
        #report-content .sticky-scrollbar::-webkit-scrollbar-thumb:hover {
            background: #16a34a !important;
        }
        
        #report-content .sticky-scrollbar::-webkit-scrollbar-corner {
            background: #f3f4f6 !important;
        }
        
        #report-content .mt-4 {
            flex-shrink: 0 !important;
            margin-top: 1rem !important;
            padding-top: 1rem !important;
        }
        
        /* Sidebar Navigation Styles */
        .sidebar {
            position: fixed;
            left: 0;
            top: 0;
            height: 100vh;
            background: linear-gradient(180deg, #f0fdf4 0%, #dcfce7 50%, #bbf7d0 100%);
            color: #1f2937;
            transition: width 0.3s ease;
            z-index: 999;
            overflow: hidden;
            display: flex;
            flex-direction: column;
            box-shadow: 2px 0 10px rgba(0, 0, 0, 0.1);
        }
        
        .sidebar.expanded {
            width: 260px;
        }
        
        .sidebar.collapsed {
            width: 70px;
        }
        
        .sidebar-header {
            padding: 1.5rem 1rem;
            border-bottom: 1px solid rgba(34, 197, 94, 0.2);
            display: flex;
            align-items: center;
            justify-content: space-between;
            min-height: 80px;
            position: relative;
            background: rgba(255, 255, 255, 0.5);
            backdrop-filter: blur(10px);
        }
        
        .sidebar.collapsed .sidebar-header {
            padding: 1.5rem 0.5rem;
            justify-content: center;
        }
        
        .sidebar-logo {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            white-space: nowrap;
            flex: 1;
        }
        
        .sidebar.collapsed .sidebar-logo {
            justify-content: center;
            flex: 0;
        }
        
        .sidebar-logo img {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            flex-shrink: 0;
            border: 2px solid rgba(34, 197, 94, 0.3);
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
            background: white;
            padding: 2px;
        }
        
        .sidebar-logo-text {
            font-size: 0.875rem;
            font-weight: 700;
            line-height: 1.2;
            opacity: 1;
            transition: opacity 0.3s ease;
            color: #166534;
            text-shadow: 0 1px 2px rgba(255, 255, 255, 0.8);
        }
        
        .sidebar.collapsed .sidebar-logo-text {
            opacity: 0;
            width: 0;
            overflow: hidden;
            display: none;
        }
        
        .sidebar-toggle {
            background: none;
            border: none;
            color: #166534;
            cursor: pointer;
            padding: 0.5rem;
            border-radius: 0.25rem;
            transition: background 0.2s;
            flex-shrink: 0;
            width: 32px;
            height: 32px;
            display: flex;
            align-items: center;
            justify-content: center;
            position: absolute;
            right: 0.5rem;
            z-index: 10;
        }
        
        .sidebar.collapsed .sidebar-toggle {
            position: static;
            right: auto;
            margin: 0 auto;
        }
        
        .sidebar-toggle:hover {
            background: rgba(34, 197, 94, 0.2);
        }
        
        .sidebar-toggle i {
            font-size: 1rem;
            transition: transform 0.3s ease;
        }
        
        .sidebar-menu {
            flex: 1;
            overflow-y: auto;
            padding: 1rem 0;
        }
        
        .sidebar-menu-item {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            padding: 0.875rem 1rem;
            color: #374151;
            cursor: pointer;
            transition: all 0.2s;
            white-space: nowrap;
            text-decoration: none;
            font-weight: 500;
        }
        
        .sidebar-menu-item:hover {
            background: rgba(34, 197, 94, 0.15);
            color: #166534;
        }
        
        .sidebar-menu-item.active {
            background: rgba(34, 197, 94, 0.25);
            color: #166534;
            border-left: 3px solid #22c55e;
            font-weight: 600;
        }
        
        .sidebar-menu-item i {
            width: 20px;
            text-align: center;
            flex-shrink: 0;
        }
        
        .sidebar-menu-item-text {
            opacity: 1;
            transition: opacity 0.3s ease;
        }
        
        .sidebar.collapsed .sidebar-menu-item-text {
            opacity: 0;
            width: 0;
            overflow: hidden;
        }
        
        .main-content-wrapper {
            margin-left: 260px;
            transition: margin-left 0.3s ease;
        }
        
        .main-content-wrapper.sidebar-collapsed {
            margin-left: 70px;
        }
        
        /* Language Switcher */
        .language-switcher {
            padding: 1rem;
            border-top: 1px solid rgba(34, 197, 94, 0.2);
            display: flex;
            align-items: center;
            gap: 0.5rem;
            background: rgba(255, 255, 255, 0.3);
        }
        
        .language-switcher-btn {
            flex: 1;
            padding: 0.5rem;
            background: rgba(255, 255, 255, 0.6);
            border: 1px solid rgba(34, 197, 94, 0.3);
            color: #374151;
            border-radius: 0.25rem;
            cursor: pointer;
            transition: all 0.2s;
            font-size: 0.75rem;
            text-align: center;
            font-weight: 500;
        }
        
        .language-switcher-btn.active {
            background: #22c55e;
            border-color: #22c55e;
            color: white;
        }
        
        .language-switcher-btn:hover {
            background: rgba(34, 197, 94, 0.2);
            border-color: rgba(34, 197, 94, 0.5);
        }
        
        .language-switcher-btn.active:hover {
            background: #16a34a;
        }
        
        .sidebar.collapsed .language-switcher {
            flex-direction: column;
        }
        
        .sidebar.collapsed .language-switcher-btn {
            font-size: 0.625rem;
            padding: 0.375rem;
        }
        
        /* Scrollbar for sidebar */
        .sidebar-menu::-webkit-scrollbar {
            width: 6px;
        }
        
        .sidebar-menu::-webkit-scrollbar-track {
            background: rgba(34, 197, 94, 0.1);
        }
        
        .sidebar-menu::-webkit-scrollbar-thumb {
            background: rgba(34, 197, 94, 0.4);
            border-radius: 3px;
        }
        
        .sidebar-menu::-webkit-scrollbar-thumb:hover {
            background: rgba(34, 197, 94, 0.6);
        }
        
        @media (max-width: 768px) {
            .sidebar {
                width: 70px !important;
            }
            
            .main-content-wrapper {
                margin-left: 70px !important;
            }
        }
    </style>
</head>
<body class="text-gray-800">

    <!-- Auth Container (Login, Signup) -->
    <div id="auth-container" class="min-h-screen flex items-center justify-center p-4">
        <div class="w-full max-w-md">
            <!-- Login Form -->
            <div id="login-view" class="view-section">
                <div class="auth-card p-8 rounded-2xl">
                    <div class="text-center mb-6">
                        <div class="auth-logo-container">
                            <img src="<?php echo $logoUrl; ?>" alt="IT Helpdesk Logo" class="logo-img">
                        </div>
                        <h2 class="auth-title text-3xl font-bold">IT Helpdesk Login</h2>
                    </div>
                    <div id="login-error" class="auth-message error mb-4 hidden"></div>
                    <form id="login-form" class="space-y-4">
                        <div>
                            <label for="login-email" class="block text-sm font-medium text-gray-700 mb-2">
                                <i class="fas fa-user mr-2 text-green-600"></i>Username or Email
                            </label>
                            <input type="text" id="login-email" placeholder="Enter your username or email" class="auth-input w-full px-4 py-3 rounded-lg" required>
                        </div>
                        <div>
                            <label for="login-password" class="block text-sm font-medium text-gray-700 mb-2">
                                <i class="fas fa-lock mr-2 text-green-600"></i>Password
                            </label>
                            <input type="password" id="login-password" placeholder="Enter your password" class="auth-input w-full px-4 py-3 rounded-lg" required>
                        </div>
                        <button type="submit" class="auth-btn-primary w-full text-white py-3 rounded-lg font-semibold">
                            <i class="fas fa-sign-in-alt mr-2"></i>Login
                        </button>
                    </form>
                    <p class="text-center text-sm mt-4">
                        <a href="#" id="show-forgot-password-view" class="auth-link">Forgot Password?</a>
                    </p>
                    <p class="text-center text-sm mt-2">
                        Don't have an account? <a href="#" id="show-signup-view" class="auth-link">Sign Up</a>
                    </p>
                </div>
            </div>

            <!-- Forgot Password Form -->
            <div id="forgot-password-view" class="view-section hidden">
                <div class="auth-card p-8 rounded-2xl">
                    <div class="text-center mb-6">
                        <div class="auth-logo-container">
                            <img src="<?php echo $logoUrl; ?>" alt="IT Helpdesk Logo" class="logo-img">
                        </div>
                        <h2 class="auth-title text-3xl font-bold mb-2">Reset Password</h2>
                        <p class="text-sm text-gray-600 mt-2">Enter your email to receive a password reset link</p>
                    </div>
                    <div id="forgot-password-error" class="auth-message error mb-4 hidden"></div>
                    <div id="forgot-password-success" class="auth-message success mb-4 hidden"></div>
                    <form id="forgot-password-form" class="space-y-4">
                        <div>
                            <label for="forgot-password-email" class="block text-sm font-medium text-gray-700 mb-2">
                                <i class="fas fa-envelope mr-2 text-green-600"></i>Email Address
                            </label>
                            <input type="email" id="forgot-password-email" placeholder="your.email@domain.com" class="auth-input w-full px-4 py-3 rounded-lg" required>
                        </div>
                        <button type="submit" class="auth-btn-primary w-full text-white py-3 rounded-lg font-semibold">
                            <i class="fas fa-paper-plane mr-2"></i>Send Reset Link
                        </button>
                    </form>
                    <p class="text-center text-sm mt-6">
                        <a href="#" id="back-to-login-from-forgot" class="auth-link">
                            <i class="fas fa-arrow-left mr-1"></i>Back to Login
                        </a>
                    </p>
                </div>
            </div>

            <!-- Reset Password Form (with token) -->
            <div id="reset-password-view" class="view-section hidden">
                <div class="auth-card p-8 rounded-2xl">
                    <div class="text-center mb-6">
                        <div class="auth-logo-container">
                            <img src="<?php echo $logoUrl; ?>" alt="IT Helpdesk Logo" class="logo-img">
                        </div>
                        <h2 class="auth-title text-3xl font-bold mb-2">Set New Password</h2>
                        <p class="text-sm text-gray-600 mt-2">Enter your new password below</p>
                    </div>
                    <div id="reset-password-error" class="auth-message error mb-4 hidden"></div>
                    <div id="reset-password-success" class="auth-message success mb-4 hidden"></div>
                    <form id="reset-password-form" class="space-y-4">
                        <input type="hidden" id="reset-token" value="">
                        <div>
                            <label for="reset-password-new" class="block text-sm font-medium text-gray-700 mb-2">
                                <i class="fas fa-lock mr-2 text-green-600"></i>New Password
                            </label>
                            <input type="password" id="reset-password-new" placeholder="Minimum 6 characters" class="auth-input w-full px-4 py-3 rounded-lg" required minlength="6">
                        </div>
                        <div>
                            <label for="reset-password-confirm" class="block text-sm font-medium text-gray-700 mb-2">
                                <i class="fas fa-lock mr-2 text-green-600"></i>Confirm New Password
                            </label>
                            <input type="password" id="reset-password-confirm" placeholder="Re-enter your password" class="auth-input w-full px-4 py-3 rounded-lg" required minlength="6">
                        </div>
                        <button type="submit" class="auth-btn-primary w-full text-white py-3 rounded-lg font-semibold">
                            <i class="fas fa-key mr-2"></i>Reset Password
                        </button>
                    </form>
                    <p class="text-center text-sm mt-6">
                        <a href="#" id="back-to-login-from-reset" class="auth-link">
                            <i class="fas fa-arrow-left mr-1"></i>Back to Login
                        </a>
                    </p>
                </div>
            </div>

            <!-- Signup Form -->
            <div id="signup-view" class="view-section hidden">
                 <div class="auth-card p-8 rounded-2xl">
                    <div class="text-center mb-6">
                        <div class="auth-logo-container">
                            <img src="assets/images/Logo BVT - Primary.png" alt="IT Helpdesk Logo" class="logo-img">
                        </div>
                        <h2 class="auth-title text-3xl font-bold">Create New Account</h2>
                    </div>
                    <div id="signup-error" class="auth-message error mb-4 hidden"></div>
                    <form id="signup-form" class="space-y-4">
                        <div>
                            <label for="signup-username" class="block text-sm font-medium text-gray-700 mb-2">
                                <i class="fas fa-user-circle mr-2 text-green-600"></i>Username
                            </label>
                            <input type="text" id="signup-username" placeholder="Letters, numbers, underscore only" class="auth-input w-full px-4 py-3 rounded-lg" required pattern="[a-zA-Z0-9_]+">
                        </div>
                        <div>
                            <label for="signup-name" class="block text-sm font-medium text-gray-700 mb-2">
                                <i class="fas fa-id-card mr-2 text-green-600"></i>Full Name
                            </label>
                            <input type="text" id="signup-name" placeholder="Enter your full name" class="auth-input w-full px-4 py-3 rounded-lg" required>
                        </div>
                        <div>
                            <label for="signup-email" class="block text-sm font-medium text-gray-700 mb-2">
                                <i class="fas fa-envelope mr-2 text-green-600"></i>Email
                            </label>
                            <input type="email" id="signup-email" placeholder="your.email@domain.com" class="auth-input w-full px-4 py-3 rounded-lg" required>
                        </div>
                        <div>
                            <label for="signup-dept" class="block text-sm font-medium text-gray-700 mb-2">
                                <i class="fas fa-building mr-2 text-green-600"></i>Department
                            </label>
                            <input type="text" id="signup-dept" placeholder="Enter your department" class="auth-input w-full px-4 py-3 rounded-lg" required>
                        </div>
                        <div>
                            <label for="signup-password" class="block text-sm font-medium text-gray-700 mb-2">
                                <i class="fas fa-lock mr-2 text-green-600"></i>Password
                            </label>
                            <input type="password" id="signup-password" placeholder="Minimum 6 characters" class="auth-input w-full px-4 py-3 rounded-lg" required minlength="6">
                        </div>
                        <button type="submit" class="auth-btn-primary w-full text-white py-3 rounded-lg font-semibold">
                            <i class="fas fa-user-plus mr-2"></i>Sign Up
                        </button>
                    </form>
                    <p class="text-center text-sm mt-4">Already have an account? <a href="#" id="show-login-view-from-signup" class="auth-link">Login here</a>.</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Main App Container (Visible after login) -->
    <div id="app-container" class="hidden">
        <!-- Sidebar Navigation -->
        <aside id="sidebar" class="sidebar expanded">
            <div class="sidebar-header">
                <div class="sidebar-logo">
                    <img src="<?php echo $logoUrl; ?>" alt="BVT Logo">
                    <div class="sidebar-logo-text">
                        <div>BAMBOO</div>
                        <div>VILLAGE TRUST</div>
                    </div>
                </div>
                <button class="sidebar-toggle" id="sidebar-toggle" title="Toggle Sidebar">
                    <i class="fas fa-chevron-left"></i>
                </button>
            </div>
            
            <!-- Admin Sidebar Menu -->
            <nav id="admin-sidebar-menu" class="sidebar-menu hidden">
                <a href="#" id="admin-menu-dashboard" class="sidebar-menu-item active" data-tab="dashboard">
                    <i class="fas fa-chart-line"></i>
                    <span class="sidebar-menu-item-text" data-i18n="menu.main_dashboard">Main Dashboard</span>
                </a>
                <a href="#" id="admin-menu-all-tickets" class="sidebar-menu-item" data-tab="all-tickets">
                    <i class="fas fa-ticket-alt"></i>
                    <span class="sidebar-menu-item-text" data-i18n="menu.all_tickets">All Tickets</span>
                </a>
                <a href="#" id="admin-menu-report" class="sidebar-menu-item" data-tab="report">
                    <i class="fas fa-file-alt"></i>
                    <span class="sidebar-menu-item-text" data-i18n="menu.report">Report</span>
                </a>
                <a href="#" id="admin-menu-user-management" class="sidebar-menu-item" data-tab="user-management">
                    <i class="fas fa-users"></i>
                    <span class="sidebar-menu-item-text" data-i18n="menu.user_management">User Management</span>
                </a>
            </nav>
            
            <!-- User Sidebar Menu -->
            <nav id="user-sidebar-menu" class="sidebar-menu hidden">
                <a href="#" id="user-menu-dashboard" class="sidebar-menu-item active" data-tab="dashboard">
                    <i class="fas fa-chart-line"></i>
                    <span class="sidebar-menu-item-text" data-i18n="menu.main_dashboard">Main Dashboard</span>
                </a>
                <a href="#" id="user-menu-create" class="sidebar-menu-item" data-tab="create">
                    <i class="fas fa-plus-circle"></i>
                    <span class="sidebar-menu-item-text" data-i18n="menu.create_ticket">Create Ticket</span>
                </a>
                <a href="#" id="user-menu-tickets" class="sidebar-menu-item" data-tab="tickets">
                    <i class="fas fa-ticket-alt"></i>
                    <span class="sidebar-menu-item-text" data-i18n="menu.my_tickets">My Tickets</span>
                </a>
                <a href="#" id="user-menu-panduan" class="sidebar-menu-item" data-tab="panduan">
                    <i class="fas fa-book"></i>
                    <span class="sidebar-menu-item-text" data-i18n="menu.guideline">Panduan (Guideline)</span>
                </a>
            </nav>
            
            <!-- Language Switcher -->
            <div class="language-switcher">
                <button class="language-switcher-btn active" data-lang="en" id="lang-en">EN</button>
                <button class="language-switcher-btn" data-lang="id" id="lang-id">ID</button>
            </div>
        </aside>
        
        <!-- Main Content Wrapper -->
        <div class="main-content-wrapper" id="main-content-wrapper">
        <header class="p-4 flex justify-between items-center sticky top-0" style="z-index: 1000 !important; background: #ffffff !important; opacity: 1 !important;">
            <div class="flex items-center">
                <div class="header-logo-container">
                    <img src="<?php echo $logoUrl; ?>" alt="IT Helpdesk Logo" class="logo-img">
                </div>
                <div class="flex flex-col">
                    <h1 class="text-xl font-bold text-gray-800 leading-tight">IT Helpdesk System</h1>
                    <p class="text-sm font-semibold text-gray-600 leading-tight">Bamboo Village Trust</p>
                </div>
            </div>
            <div class="flex items-center">
                <div class="relative mr-4" style="z-index: 10000 !important;">
                    <button id="notif-bell-btn" class="relative text-gray-700 hover:text-gray-900" style="z-index: 10000 !important;" title="Notifications">
                        <i class="fas fa-bell text-lg"></i>
                        <span id="notif-badge" class="absolute -top-1 -right-1 bg-red-500 text-white text-[10px] leading-none px-1.5 py-0.5 rounded-full hidden" style="z-index: 10001 !important;">0</span>
                    </button>
                    <!-- Notification Dropdown -->
                    <div id="notif-dropdown" class="hidden w-80 bg-white rounded-lg shadow-xl border border-gray-200 max-h-96 overflow-y-auto" style="z-index: 10000 !important; position: fixed !important;">
                        <div class="p-4 border-b border-gray-200 flex justify-between items-center">
                            <h3 class="font-semibold text-gray-800">Notifications</h3>
                            <div class="flex items-center gap-2">
                                <button id="mark-all-read-btn" class="text-xs text-green-600 hover:text-green-700 font-medium px-2 py-1 hover:bg-green-50 rounded transition">
                                    <i class="fas fa-check-double mr-1"></i>Mark All as Read
                                </button>
                                <button id="clear-read-btn" class="text-xs text-orange-600 hover:text-orange-700 font-medium px-2 py-1 hover:bg-orange-50 rounded transition">
                                    <i class="fas fa-trash-alt mr-1"></i>Clear Read
                                </button>
                                <button id="close-notif-dropdown" class="text-gray-400 hover:text-gray-600">
                                    <i class="fas fa-times"></i>
                                </button>
                            </div>
                        </div>
                        <div id="notif-list" class="divide-y divide-gray-200">
                            <div class="p-4 text-center text-gray-500 text-sm">Loading...</div>
                        </div>
                        <div id="notif-empty" class="hidden p-4 text-center text-gray-500 text-sm">
                            No notifications
                        </div>
                    </div>
                </div>
                <!-- User Profile Section with Dropdown -->
                <div class="relative mr-4" style="z-index: 10000 !important;">
                    <div class="flex items-center gap-2 cursor-pointer hover:opacity-80 transition" id="user-profile-btn" title="Profile Menu">
                        <div class="relative">
                            <img id="user-profile-picture" src="" alt="Profile" class="w-10 h-10 rounded-full object-cover border-2 border-gray-300 shadow-sm" style="display: none;">
                            <div id="user-profile-placeholder" class="w-10 h-10 rounded-full bg-gradient-to-br from-green-400 to-green-600 flex items-center justify-center text-white font-semibold text-sm shadow-sm">
                                <i class="fas fa-user"></i>
                            </div>
                        </div>
                        <div class="flex flex-col">
                            <span id="user-name-display" class="text-sm font-semibold text-gray-800"></span>
                        </div>
                    </div>
                    <!-- Profile Dropdown Menu -->
                    <div id="user-profile-dropdown" class="hidden absolute right-0 mt-2 w-64 bg-white rounded-lg shadow-xl border border-gray-200" style="z-index: 10001 !important;">
                        <div class="p-4 border-b border-gray-200">
                            <div class="font-semibold text-gray-800" id="dropdown-user-name"></div>
                            <div class="text-sm text-gray-500 mt-1" id="dropdown-user-email"></div>
                        </div>
                        <div class="py-2">
                            <button id="edit-profile-menu-btn" class="w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 transition flex items-center gap-3">
                                <i class="fas fa-user-edit w-5 text-gray-400"></i>
                                <span>Edit Profile</span>
                            </button>
                            <button id="change-password-menu-btn" class="w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 transition flex items-center gap-3">
                                <i class="fas fa-key w-5 text-gray-400"></i>
                                <span>Change Password</span>
                            </button>
                            <button id="logout-menu-btn" class="w-full text-left px-4 py-2 text-sm text-red-600 hover:bg-red-50 transition flex items-center gap-3">
                                <i class="fas fa-sign-out-alt w-5 text-red-400"></i>
                                <span>Logout</span>
                            </button>
                        </div>
                    </div>
                </div>
                <!-- Hidden buttons for programmatic access -->
                <button id="change-password-btn" class="hidden">Change Password</button>
                <button id="logout-btn" class="hidden">Logout</button>
            </div>
        </header>

        <main class="p-4 md:p-8 min-h-screen">
            <!-- User View -->
            <div id="user-view" class="view-section hidden">
                <!-- User Dashboard Header -->
                <div class="mb-6" style="position: sticky !important; top: 0 !important; z-index: 45 !important; margin-bottom: 0 !important;">
                    <div class="welcome-header rounded-xl p-6 text-white" style="margin-bottom: 0 !important;">
                        <div class="flex items-center justify-between">
                            <div>
                                <h1 class="text-2xl font-bold mb-2">Welcome to IT Helpdesk</h1>
                                <p class="text-green-100" id="user-welcome-message">Manage your support tickets</p>
                            </div>
                            <div class="text-right">
                                <div class="bg-white bg-opacity-20 rounded-lg p-4">
                                    <p class="text-sm text-green-100">My Tickets</p>
                                    <p class="text-2xl font-bold" id="user-ticket-count">0</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- User Navigation Tabs - Hidden, replaced by sidebar -->
                <nav class="hidden">
                    <div id="user-tab-dashboard" class="nav-tab active">Dashboard</div>
                    <div id="user-tab-create" class="nav-tab">Create Ticket</div>
                    <div id="user-tab-tickets" class="nav-tab">My Tickets</div>
                    <div id="user-tab-panduan" class="nav-tab">Panduan (Guideline)</div>
                </nav>

                <!-- User Tab: Dashboard -->
                <div id="user-dashboard-view" class="user-tab-content">
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-6">
                        <div class="stat-card p-6 rounded-xl border-l-4 border-blue-500" data-status="all" data-user-stat="total">
                            <div class="flex items-center">
                                <div class="p-3 bg-blue-100 rounded-full">
                                    <i class="fas fa-ticket-alt text-blue-600 text-xl"></i>
                                </div>
                                <div class="ml-4">
                                    <p class="text-gray-500 text-sm">Total Tickets</p>
                                    <p id="user-stats-total" class="text-2xl font-bold text-gray-800">0</p>
                                </div>
                            </div>
                        </div>
                        <div class="stat-card p-6 rounded-xl border-l-4 border-yellow-500" data-status="In Progress" data-user-stat="progress">
                            <div class="flex items-center">
                                <div class="p-3 bg-yellow-100 rounded-full">
                                    <i class="fas fa-clock text-yellow-600 text-xl"></i>
                                </div>
                                <div class="ml-4">
                                    <p class="text-gray-500 text-sm">In Progress</p>
                                    <p id="user-stats-progress" class="text-2xl font-bold text-gray-800">0</p>
                                </div>
                            </div>
                        </div>
                        <div class="stat-card p-6 rounded-xl border-l-4 border-green-500" data-status="Done" data-user-stat="done">
                            <div class="flex items-center">
                                <div class="p-3 bg-green-100 rounded-full">
                                    <i class="fas fa-check-circle text-green-600 text-xl"></i>
                                </div>
                                <div class="ml-4">
                                    <p class="text-gray-500 text-sm">Completed</p>
                                    <p id="user-stats-done" class="text-2xl font-bold text-gray-800">0</p>
                                </div>
                            </div>
                        </div>
                        <div class="stat-card p-6 rounded-xl border-l-4 border-gray-500" data-status="Open" data-user-stat="open">
                            <div class="flex items-center">
                                <div class="p-3 bg-gray-100 rounded-full">
                                    <i class="fas fa-pause-circle text-gray-600 text-xl"></i>
                                </div>
                                <div class="ml-4">
                                    <p class="text-gray-500 text-sm">Open</p>
                                    <p id="user-stats-open" class="text-2xl font-bold text-gray-800">0</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Recent Tickets -->
                    <div class="dashboard-card p-6 rounded-xl">
                        <div class="flex justify-between items-center mb-4">
                            <h3 class="text-xl font-bold">Recent Tickets</h3>
                            <button id="user-view-all-tickets" class="text-green-600 hover:text-green-700 text-sm font-medium">
                                View All <i class="fas fa-arrow-right ml-1"></i>
                            </button>
                        </div>
                        <div id="user-recent-tickets" class="space-y-3"></div>
                    </div>
                </div>

                <!-- User Tab: Create Ticket -->
                <div id="user-create-view" class="user-tab-content hidden">
                    <div class="max-w-2xl mx-auto">
                        <div class="form-container p-8 rounded-xl">
                            <h2 class="text-2xl font-bold mb-6 text-center">Create New Support Ticket</h2>
                            <div id="user-notification" class="hidden mb-4 p-3 rounded-lg text-sm"></div>
                            <form id="ticket-form" class="space-y-6"></form>
                        </div>
                    </div>
                </div>

                <!-- User Tab: My Tickets -->
                <div id="user-tickets-view" class="user-tab-content hidden">
                    <div class="table-container rounded-xl">
                        <div class="p-6 border-b border-gray-200">
                            <div class="flex justify-between items-center">
                                <h2 class="text-2xl font-bold">My Support Tickets</h2>
                                <div class="flex gap-2">
                                    <select id="ticket-status-filter" class="border border-gray-300 rounded-md px-3 py-2 text-sm">
                                        <option value="">All Status</option>
                                        <option value="Open">Open</option>
                                        <option value="In Progress">In Progress</option>
                                        <option value="Done">Done</option>
                                        <option value="Delayed">Delayed</option>
                                        <option value="Revisi">Revisi</option>
                                    </select>
                                    <button id="refresh-tickets-btn" class="bg-green-600 text-white px-4 py-2 rounded-md text-sm hover:bg-green-700">
                                        <i class="fas fa-sync-alt mr-1"></i> Refresh
                                    </button>
                                </div>
                            </div>
                        </div>
                        <div class="p-6">
                            <div id="user-ticket-list" class="space-y-4"></div>
                        </div>
                    </div>
                </div>

                <!-- [START] Perubahan: Menambahkan Konten Tab Panduan -->
                <div id="user-panduan-view" class="user-tab-content hidden">
                    <div class="dashboard-card rounded-xl overflow-hidden">
                        <div class="p-4 border-b border-gray-200 bg-gradient-to-r from-green-50 to-emerald-50">
                            <h2 class="text-xl font-bold text-gray-800">Panduan Lengkap Sistem IT Helpdesk</h2>
                            <p class="text-sm text-gray-600 mt-1">Pelajari cara menggunakan sistem helpdesk dengan efektif</p>
                        </div>
                        <div class="panduan-iframe-container" style="height: calc(100vh - 280px); min-height: 600px; position: relative;">
                            <iframe 
                                src="assets/Panduan.html" 
                                style="width: 100%; height: 100%; border: none; display: block;"
                                id="panduan-iframe"
                                title="Panduan IT Helpdesk">
                            </iframe>
                        </div>
                    </div>
                </div>
                <!-- [END] Perubahan: Menambahkan Konten Tab Panduan -->

            </div>

            <!-- Admin View -->
            <div id="admin-view" class="view-section hidden">
                 <!-- Admin Navigation Tabs - Hidden, replaced by sidebar -->
                 <nav class="hidden">
                    <div id="tab-dashboard" class="nav-tab active">Dashboard</div>
                    <div id="tab-all-tickets" class="nav-tab">All Tickets</div>
                    <div id="tab-report" class="nav-tab">Report</div>
                    <div id="tab-user-management" class="nav-tab">User Management</div>
                </nav>

                <!-- Admin Tab: Dashboard -->
                <div id="admin-dashboard-view" class="admin-tab-content">
                    <!-- Stats Cards with Horizontal Navigation -->
                    <div class="relative mb-6">
                        <!-- Navigation Arrows -->
                        <button id="stats-prev" class="stats-nav-btn">
                            <i class="fas fa-chevron-left text-gray-600"></i>
                        </button>
                        <button id="stats-next" class="stats-nav-btn">
                            <i class="fas fa-chevron-right text-gray-600"></i>
                        </button>
                        
                        <!-- Stats Cards Container -->
                        <div id="stats-container" class="overflow-hidden mx-8">
                            <div id="stats-slider" class="flex transition-transform duration-300 ease-in-out gap-6">
                                <div class="stat-card p-6 rounded-xl min-w-[200px] flex-shrink-0" data-status="all">
                                    <p class="text-gray-500">Total Tickets</p>
                                    <p id="stats-total" class="text-3xl font-bold">0</p>
                                </div>
                                <div class="stat-card p-6 rounded-xl min-w-[200px] flex-shrink-0" data-status="Open">
                                    <p class="text-gray-500">Open</p>
                                    <p id="stats-open" class="text-3xl font-bold text-gray-600">0</p>
                                </div>
                                <div class="stat-card p-6 rounded-xl min-w-[200px] flex-shrink-0" data-status="In Progress">
                                    <p class="text-gray-500">In Progress</p>
                                    <p id="stats-progress" class="text-3xl font-bold text-green-600">0</p>
                                </div>
                                <div class="stat-card p-6 rounded-xl min-w-[200px] flex-shrink-0" data-status="Delayed">
                                    <p class="text-gray-500">Delayed</p>
                                    <p id="stats-delayed" class="text-3xl font-bold text-yellow-500">0</p>
                                </div>
                                <div class="stat-card p-6 rounded-xl min-w-[200px] flex-shrink-0" data-status="Done">
                                    <p class="text-gray-500">Done</p>
                                    <p id="stats-done" class="text-3xl font-bold text-green-600">0</p>
                                </div>
                                <div class="stat-card p-6 rounded-xl min-w-[200px] flex-shrink-0" data-status="Revisi">
                                    <p class="text-gray-500">Revisi</p>
                                    <p id="stats-revisi" class="text-3xl font-bold text-orange-600">0</p>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- Charts and Activity -->
                    <div class="grid grid-cols-1 lg:grid-cols-5 gap-8">
                        <div class="lg:col-span-3 chart-container p-6 rounded-xl">
                             <div class="flex justify-between items-center mb-4">
                                <h3 class="text-xl font-bold">Tickets by Status</h3>
                                <p class="text-sm text-gray-500">Click on chart segments to filter tickets</p>
                             </div>
                             <div class="h-72 md:h-96 chart-clickable">
                                <canvas id="status-chart"></canvas>
                             </div>
                        </div>
                        <div class="lg:col-span-2 dashboard-card p-6 rounded-xl">
                            <div class="flex justify-between items-center mb-4">
                                <h3 class="text-xl font-bold">Tickets by Category</h3>
                                <p class="text-sm text-gray-500">Click on bars to filter tickets</p>
                            </div>
                             <div class="h-72 md:h-96 chart-clickable" style="position: relative;">
                                <canvas id="category-chart"></canvas>
                             </div>
                        </div>
                        <div class="lg:col-span-5 dashboard-card p-6 rounded-xl mt-8">
                            <div class="flex justify-between items-center mb-4">
                                <h3 class="text-xl font-bold">Recent Activity</h3>
                                <div class="flex gap-2">
                                    <button id="export-dashboard-pdf-btn" class="bg-red-600 text-white px-3 py-1 rounded text-xs hover:bg-red-700">
                                        <i class="fas fa-file-pdf mr-1"></i> PDF
                                    </button>
                                    <button id="export-dashboard-excel-btn" class="bg-green-600 text-white px-3 py-1 rounded text-xs hover:bg-green-700">
                                        <i class="fas fa-file-excel mr-1"></i> Excel
                                    </button>
                                </div>
                            </div>
                            <div id="recent-activity-list" class="space-y-3 h-72 md:h-96 overflow-y-auto"></div>
                        </div>
                    </div>
                </div>

                <!-- Admin Tab: All Tickets -->
                <div id="admin-all-tickets-view" class="admin-tab-content hidden">
                    <div class="table-container rounded-xl">
                        <!-- Header Section - Sticky -->
                        <div class="sticky top-0 z-20 p-6 pb-4 border-b border-gray-200" style="background: rgba(255, 255, 255, 0.98); backdrop-filter: blur(10px);">
                            <div class="flex justify-between items-center">
                                <h2 class="text-2xl font-bold">All Incoming Tickets</h2>
                                <div class="flex gap-2">
                                    <button id="export-pdf-btn" class="bg-red-600 text-white px-4 py-2 rounded-md text-sm hover:bg-red-700">
                                        <i class="fas fa-file-pdf mr-1"></i> Export PDF
                                    </button>
                                    <button id="export-excel-btn" class="bg-green-600 text-white px-4 py-2 rounded-md text-sm hover:bg-green-700">
                                        <i class="fas fa-file-excel mr-1"></i> Export Excel
                                    </button>
                                </div>
                            </div>
                        </div>
                        <!-- Table Container dengan Scroll Vertikal dan Horizontal -->
                        <!-- Struktur: Outer container untuk vertical scroll, Inner container untuk horizontal scroll yang sticky -->
                        <div class="p-6 pt-4" style="display: flex; flex-direction: column; max-height: calc(100vh - 280px); position: relative;">
                            <!-- Inner container dengan horizontal scroll yang selalu accessible -->
                            <div class="overflow-x-auto sticky-scrollbar" style="flex: 1; overflow-y: auto; -webkit-overflow-scrolling: touch; position: relative;">
                                <table class="w-full text-sm text-left" style="table-layout: auto; min-width: 1400px;">
                                    <thead class="text-xs text-gray-700 uppercase bg-gray-50 sticky-header">
                                        <tr>
                                            <th class="px-4 py-3">Ticket No.</th>
                                            <th class="px-4 py-3">User</th>
                                            <th class="px-4 py-3">Category</th>
                                            <th class="px-4 py-3" style="min-width: 350px; max-width: 600px;">Description</th>
                                            <th class="px-4 py-3">Priority by User</th>
                                            <th class="px-4 py-3">Response Time</th>
                                            <th class="px-4 py-3">Assigned To</th>
                                            <th class="px-4 py-3">Reassign</th>
                                            <th class="px-4 py-3">Attachments</th>
                                            <th class="px-4 py-3">Priority by IT SLA</th>
                                            <th class="px-4 py-3">Resolution Time</th>
                                            <th class="px-4 py-3">Due Date</th>
                                            <th class="px-4 py-3">Admin Response</th>
                                            <th class="px-4 py-3">Start Date</th>
                                            <th class="px-4 py-3">End Date</th>
                                            <th class="px-4 py-3">Status</th>
                                            <th class="px-4 py-3" style="min-width: 200px; max-width: 300px;">Komentar Admin</th>
                                        </tr>
                                    </thead>
                                    <tbody id="all-tickets-table" class="divide-y"></tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Admin Tab: Report -->
                <div id="admin-report-view" class="admin-tab-content hidden">
                    <div class="dashboard-card p-6 rounded-xl">
                        <div class="mb-6">
                            <h2 class="text-2xl font-bold mb-4">Ticket Report</h2>
                            <div class="flex flex-wrap gap-4 items-end">
                                <div class="flex-1 min-w-[200px]">
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Filter by Admin</label>
                                    <select id="report-filter-admin" class="w-full border border-gray-300 rounded-md px-3 py-2 text-sm focus:ring-2 focus:ring-green-500 focus:border-green-500">
                                        <option value="all">All Tickets</option>
                                    </select>
                                </div>
                                <div class="flex gap-2">
                                    <button id="view-report-btn" class="bg-blue-600 text-white px-4 py-2 rounded-md text-sm hover:bg-blue-700">
                                        <i class="fas fa-eye mr-1"></i> View Report
                                    </button>
                                    <button id="export-report-pdf-btn" class="bg-red-600 text-white px-4 py-2 rounded-md text-sm hover:bg-red-700">
                                        <i class="fas fa-file-pdf mr-1"></i> Export PDF
                                    </button>
                                    <button id="export-report-excel-btn" class="bg-green-600 text-white px-4 py-2 rounded-md text-sm hover:bg-green-700">
                                        <i class="fas fa-file-excel mr-1"></i> Export Excel
                                    </button>
                                </div>
                            </div>
                        </div>
                        <div class="bg-gray-50 p-4 rounded-lg">
                            <p class="text-sm text-gray-600">
                                <i class="fas fa-info-circle mr-1"></i>
                                Select an admin to filter tickets or select "All Tickets" to view all tickets. 
                                Click "View Report" to preview the report, or export directly to PDF/Excel.
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Admin Tab: User Management -->
                <div id="admin-user-management-view" class="admin-tab-content hidden">
                    <div class="dashboard-card p-6 rounded-xl">
                        <div class="flex justify-between items-center mb-4">
                            <h2 class="text-2xl font-bold">User Management</h2>
                            <button id="add-user-btn" class="bg-green-600 text-white font-semibold py-2 px-4 rounded-md hover:bg-green-700 text-sm">Create New User</button>
                        </div>
                        <div class="overflow-x-auto">
                            <table class="w-full text-sm text-left">
                                <thead class="text-xs text-gray-700 uppercase bg-gray-50">
                                    <tr>
                                        <th class="px-4 py-3">Name</th>
                                        <th class="px-4 py-3">Username</th>
                                        <th class="px-4 py-3">Email</th>
                                        <th class="px-4 py-3">Department</th>
                                        <th class="px-4 py-3">Role</th>
                                        <th class="px-4 py-3">Actions</th>
                                    </tr>
                                </thead>
                                <tbody id="user-management-table" class="divide-y"></tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </main>
        </div> <!-- End main-content-wrapper -->
    </div> <!-- End app-container -->
    
    <!-- Edit Profile Modal -->
    <div id="edit-profile-modal" class="modal hidden">
        <div class="modal-content" style="max-width: 500px;">
            <div class="bg-white rounded-lg shadow-xl">
                <!-- Modal Header -->
                <div class="p-4 md:p-6 border-b border-gray-200 flex justify-between items-center">
                    <h2 class="text-xl md:text-2xl font-bold text-gray-800">Edit Profile</h2>
                    <button id="close-edit-profile-modal" class="text-gray-400 hover:text-gray-600 transition">
                        <i class="fas fa-times text-xl"></i>
                    </button>
                </div>
                <!-- Modal Body -->
                <div class="p-4 md:p-6">
                    <form id="edit-profile-form" enctype="multipart/form-data">
                        <!-- Profile Picture Upload - Clickable -->
                        <div class="mb-6 flex flex-col items-center">
                            <div class="relative cursor-pointer group" id="profile-picture-container">
                                <img id="profile-picture-preview" src="" alt="Profile Preview" class="w-24 h-24 rounded-full object-cover border-2 border-gray-300 shadow-sm" style="display: none;">
                                <div id="profile-picture-placeholder" class="w-24 h-24 rounded-full bg-gradient-to-br from-green-400 to-green-600 flex items-center justify-center text-white font-semibold text-3xl shadow-sm">
                                    <i class="fas fa-user"></i>
                                </div>
                                <div class="absolute inset-0 bg-black bg-opacity-0 group-hover:bg-opacity-30 rounded-full flex items-center justify-center transition-all">
                                    <i class="fas fa-camera text-white opacity-0 group-hover:opacity-100 transition-opacity"></i>
                                </div>
                            </div>
                            <input type="file" id="profile-picture-input" name="profile_picture" accept="image/*" class="hidden">
                            <p class="text-xs text-gray-500 mt-2">Click avatar to upload photo</p>
                        </div>
                        <!-- Full Name -->
                        <div class="mb-4">
                            <label for="edit-profile-name" class="block text-sm font-medium text-gray-700 mb-1">
                                Full Name <span class="text-red-500">*</span>
                            </label>
                            <input type="text" id="edit-profile-name" name="name" class="w-full px-4 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500 focus:border-blue-500" required>
                        </div>
                        <!-- Email -->
                        <div class="mb-4">
                            <label for="edit-profile-email" class="block text-sm font-medium text-gray-700 mb-1">
                                Email <span class="text-red-500">*</span>
                            </label>
                            <input type="email" id="edit-profile-email" name="email" class="w-full px-4 py-2 border border-gray-300 rounded-md bg-gray-100" readonly>
                        </div>
                        <!-- Position (Department) -->
                        <div class="mb-6">
                            <label for="edit-profile-department" class="block text-sm font-medium text-gray-700 mb-1">Position</label>
                            <input type="text" id="edit-profile-department" name="department" class="w-full px-4 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        </div>
                        <!-- Buttons -->
                        <div class="flex gap-3 justify-end">
                            <button type="button" id="cancel-edit-profile-btn" class="px-4 py-2 bg-gray-100 text-gray-700 rounded-md hover:bg-gray-200 transition font-medium">
                                Cancel
                            </button>
                            <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 transition font-medium">
                                Save Changes
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Report View Modal -->
    <div id="report-view-modal" class="modal hidden">
        <div class="modal-content" style="max-width: 95%; width: 95%; max-height: 90vh; margin: auto; height: 90vh; display: flex; flex-direction: column;">
            <div class="bg-white" style="display: flex; flex-direction: column; height: 100%; max-height: 100%; min-height: 0; overflow: hidden;">
                <!-- Modal Header -->
                <div class="p-4 md:p-6 border-b border-gray-200 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-3" style="flex-shrink: 0; background: linear-gradient(135deg, rgba(34, 197, 94, 0.1) 0%, rgba(22, 163, 74, 0.1) 100%); backdrop-filter: blur(10px);">
                    <div class="flex-1 min-w-0">
                        <h2 class="text-xl md:text-2xl font-bold text-gray-800" style="word-wrap: break-word; overflow-wrap: break-word;">Ticket Report</h2>
                        <p id="report-modal-filter" class="text-xs md:text-sm text-gray-600 mt-1" style="word-wrap: break-word; overflow-wrap: break-word;">All Tickets</p>
                    </div>
                    <div class="flex gap-2 w-full sm:w-auto flex-shrink-0">
                        <button id="print-report-btn" class="flex-1 sm:flex-none bg-green-600 hover:bg-green-700 text-white px-4 py-2.5 rounded-md text-sm font-medium shadow-md transition-all duration-200 flex items-center justify-center gap-2 min-w-[100px]">
                            <i class="fas fa-print"></i> 
                            <span class="hidden sm:inline">Print</span>
                        </button>
                        <button id="close-print-window-btn" class="flex-1 sm:flex-none bg-yellow-600 hover:bg-yellow-700 text-white px-4 py-2.5 rounded-md text-sm font-medium shadow-md transition-all duration-200 flex items-center justify-center gap-2 min-w-[100px] hidden">
                            <i class="fas fa-window-close"></i> 
                            <span class="hidden sm:inline">Close Print</span>
                        </button>
                        <button id="close-report-modal-btn" class="flex-1 sm:flex-none bg-red-600 hover:bg-red-700 text-white px-4 py-2.5 rounded-md text-sm font-medium shadow-md transition-all duration-200 flex items-center justify-center gap-2 min-w-[100px]">
                            <i class="fas fa-times"></i> 
                            <span class="hidden sm:inline">Close</span>
                        </button>
                    </div>
                </div>
                <!-- Modal Body -->
                <div class="p-3 md:p-6" style="flex: 1; overflow: hidden; background: #f9fafb; min-height: 0; display: flex; flex-direction: column; position: relative; padding: 1rem 1.5rem;">
                    <div id="report-content" style="width: 100%; flex: 1; min-height: 0; max-height: 100%; overflow: hidden; position: relative; display: flex; flex-direction: column;">
                        <div class="text-center py-8">
                            <i class="fas fa-spinner fa-spin text-4xl text-gray-400 mb-4"></i>
                            <p class="text-gray-600">Loading report...</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Placeholder -->
    <div id="modal-placeholder"></div>

    <!-- Global Footer -->
    <footer class="w-full text-center text-xs text-gray-500 py-6 mt-8">
        © 2025 Copyright Information Systems & Digital Infrastructure - Bamboo Village Trust
        <div class="mt-2">Heldesk System Versi 1.0</div>
    </footer>

    <script src="app.js"></script>
</body>
</html>