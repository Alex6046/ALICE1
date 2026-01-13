<?php
// admin_styles.php - Shared styles for all admin pages
?>
<style>
    * {
        margin: 0;
        padding: 0;
        box-sizing: border-box;
    }

    body {
        font-family: 'Poppins', sans-serif;
        background: #0a0f1c url('https://images.unsplash.com/photo-1446776811953-b23d57bd21aa?auto=format&fit=crop&w=1600&q=80') no-repeat center center fixed;
        background-size: cover;
        color: #fff;
        margin: 0;
        padding: 0;
        min-height: 100vh;
        position: relative;
    }

    body::before {
        content: '';
        position: fixed;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: rgba(10, 15, 28, 0.92);
        z-index: -1;
    }

    .container {
        max-width: 1400px;
        margin: 0 auto;
        padding: 20px;
    }

    .page-header {
        margin-bottom: 30px;
        animation: slideInLeft 0.6s ease;
    }

    .page-header h1 {
        font-size: 2.5rem;
        color: #fff;
        margin-bottom: 10px;
    }

    .page-header p {
        font-size: 1.1rem;
        color: #94a3b8;
    }

    @keyframes slideInLeft {
        from {
            opacity: 0;
            transform: translateX(-50px);
        }
        to {
            opacity: 1;
            transform: translateX(0);
        }
    }

    .stats-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
        gap: 20px;
        margin-bottom: 40px;
        animation: fadeInUp 0.6s ease 0.2s both;
    }

    @keyframes fadeInUp {
        from {
            opacity: 0;
            transform: translateY(30px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    .stat-card {
        background: rgba(15, 23, 42, 0.8);
        backdrop-filter: blur(10px);
        border-radius: 16px;
        padding: 25px;
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.3);
        border: 1px solid rgba(124, 58, 237, 0.2);
        transition: all 0.3s ease;
    }

    .stat-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 8px 30px rgba(124, 58, 237, 0.4);
        border-color: rgba(124, 58, 237, 0.5);
    }

    .stat-card h4 {
        font-size: 1rem;
        color: #94a3b8;
        margin-bottom: 10px;
        font-weight: 500;
    }

    .stat-card h1 {
        font-size: 2.5rem;
        margin: 0;
        background: linear-gradient(135deg, #7c3aed 0%, #a78bfa 100%);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
    }

    .section {
        background: rgba(15, 23, 42, 0.8);
        backdrop-filter: blur(10px);
        padding: 25px;
        border-radius: 16px;
        margin-bottom: 30px;
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.3);
        border: 1px solid rgba(124, 58, 237, 0.2);
        animation: fadeInUp 0.6s ease 0.4s both;
    }

    .section-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 20px;
        flex-wrap: wrap;
        gap: 15px;
    }

    .section-header h2 {
        font-size: 1.5rem;
        color: #fff;
        margin: 0;
    }

    .button-group {
        display: flex;
        gap: 10px;
        flex-wrap: wrap;
    }

    .btn-purple {
        background: #7c3aed;
        color: white;
        border: none;
        border-radius: 8px;
        padding: 10px 18px;
        cursor: pointer;
        font-weight: 600;
        font-size: 14px;
        transition: all 0.3s ease;
    }

    .btn-purple:hover {
        background: #6d28d9;
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(124, 58, 237, 0.5);
    }

    .btn-black {
        background: #1e293b;
        color: white;
        border: none;
        border-radius: 8px;
        padding: 10px 18px;
        cursor: pointer;
        font-weight: 600;
        font-size: 14px;
        transition: all 0.3s ease;
    }

    .btn-black:hover {
        background: #334155;
        transform: translateY(-2px);
    }

    .search-bar {
        width: 100%;
        max-width: 500px;
        padding: 12px 20px;
        border-radius: 10px;
        border: 1px solid rgba(124, 58, 237, 0.3);
        background: rgba(13, 17, 23, 0.6);
        color: #fff;
        font-size: 15px;
        transition: all 0.3s ease;
    }

    .search-bar:focus {
        outline: none;
        border-color: #7c3aed;
        box-shadow: 0 0 0 3px rgba(124, 58, 237, 0.2);
    }

    table {
        width: 100%;
        border-collapse: collapse;
        color: #fff;
        margin-top: 20px;
    }

    thead {
        background: rgba(30, 41, 59, 0.8);
    }

    th, td {
        text-align: left;
        padding: 15px;
        border-bottom: 1px solid rgba(124, 58, 237, 0.1);
    }

    tbody tr {
        background: rgba(17, 24, 39, 0.5);
        transition: all 0.3s ease;
    }

    tbody tr:hover {
        background: rgba(124, 58, 237, 0.1);
        transform: scale(1.01);
    }

    .modal {
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(0, 0, 0, 0.8);
        backdrop-filter: blur(5px);
        display: none;
        justify-content: center;
        align-items: center;
        z-index: 2000;
        animation: fadeIn 0.3s ease;
    }

    .modal-content {
        background: rgba(15, 23, 42, 0.95);
        backdrop-filter: blur(10px);
        padding: 30px;
        border-radius: 16px;
        width: 90%;
        max-width: 500px;
        border: 1px solid rgba(124, 58, 237, 0.3);
        animation: slideInUp 0.4s ease;
    }

    @keyframes slideInUp {
        from {
            opacity: 0;
            transform: translateY(50px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    .modal-content h3 {
        margin-bottom: 20px;
        color: #fff;
        font-size: 1.5rem;
    }

    .modal input,
    .modal textarea,
    .modal select {
        width: 100%;
        padding: 12px;
        margin: 10px 0;
        border-radius: 8px;
        border: 1px solid rgba(124, 58, 237, 0.3);
        background: rgba(13, 17, 23, 0.6);
        color: #fff;
        font-size: 14px;
        transition: all 0.3s ease;
    }

    .modal input:focus,
    .modal textarea:focus,
    .modal select:focus {
        outline: none;
        border-color: #7c3aed;
        box-shadow: 0 0 0 3px rgba(124, 58, 237, 0.2);
    }

    .toast {
        visibility: hidden;
        min-width: 250px;
        background: #38a169;
        color: #fff;
        text-align: center;
        border-radius: 8px;
        padding: 16px;
        position: fixed;
        left: 50%;
        bottom: 30px;
        font-size: 17px;
        transform: translateX(-50%);
        opacity: 0;
        transition: all 0.5s ease;
        z-index: 3000;
    }

    .toast.show {
        visibility: visible;
        opacity: 1;
        animation: slideUp 0.5s ease;
    }

    @keyframes slideUp {
        from {
            bottom: 0;
            opacity: 0;
        }
        to {
            bottom: 30px;
            opacity: 1;
        }
    }

    .highlight-img {
        width: 80px;
        height: 80px;
        object-fit: cover;
        border-radius: 8px;
        border: 2px solid rgba(124, 58, 237, 0.3);
    }

    @media (max-width: 768px) {
        .page-header h1 {
            font-size: 2rem;
        }

        .section-header {
            flex-direction: column;
            align-items: flex-start;
        }

        table {
            font-size: 14px;
        }

        th, td {
            padding: 10px;
        }
    }
</style>
