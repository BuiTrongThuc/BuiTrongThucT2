<?php
$isLoggedIn = !empty($_SESSION['username']);
$isAdmin = !empty($_SESSION['role']) && $_SESSION['role'] === 'admin';
$displayName = $_SESSION['full_name'] ?? $_SESSION['username'] ?? '';
$currentSearch = htmlspecialchars($_GET['search'] ?? '', ENT_QUOTES, 'UTF-8');
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>
        <?php echo isset($pageTitle) ? htmlspecialchars($pageTitle, ENT_QUOTES, 'UTF-8') . ' - TechStore' : 'TechStore'; ?>
    </title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <link href="<?php echo BASE_URL; ?>/public/assets/css/shop.css" rel="stylesheet">
    <link href="<?php echo BASE_URL; ?>/public/assets/css/premium-api.css" rel="stylesheet">
    <link href="<?php echo BASE_URL; ?>/public/assets/css/ultra-vip.css" rel="stylesheet">

    <style>
        :root{
            --wb-red:#d70018;
            --wb-red-2:#f52b3a;
            --wb-dark:#141827;
            --wb-muted:#6b7280;
            --wb-soft:#f5f6fb;
            --wb-border:rgba(255,255,255,.22);
        }

        body{ padding-top:0; }

        .wb-topbar{
            background:linear-gradient(90deg,#ad0015 0%,#e60023 48%,#ff3348 100%);
            color:#fff;
            font-size:13px;
            font-weight:700;
            box-shadow:0 2px 12px rgba(180,0,20,.16);
        }
        .wb-topbar-inner{
            height:36px;
            display:flex;
            align-items:center;
            justify-content:space-between;
            gap:16px;
        }
        .wb-topbar-group{display:flex;align-items:center;gap:18px;white-space:nowrap;}
        .wb-topbar i{font-size:14px;}

        .wb-header{
            position:sticky;
            top:0;
            z-index:1020;
            background:
                radial-gradient(circle at top left,rgba(255,255,255,.20),transparent 34%),
                linear-gradient(135deg,#f71931 0%,#d70018 48%,#a90014 100%);
            box-shadow:0 14px 30px rgba(156,0,18,.24);
        }
        .wb-header-inner{padding:13px 0 12px;}

        .wb-main-row{
            display:grid;
            grid-template-columns:auto minmax(300px,1fr) auto;
            align-items:center;
            gap:14px;
        }

        .wb-logo{
            display:flex;
            align-items:center;
            gap:11px;
            text-decoration:none;
            color:#fff!important;
            min-width:max-content;
        }
        .wb-logo-icon{
            width:46px;height:46px;border-radius:18px;
            display:grid;place-items:center;
            background:#fff;color:var(--wb-red);
            font-size:23px;
            box-shadow:0 10px 24px rgba(105,0,13,.22);
        }
        .wb-logo-text{
            display:flex;flex-direction:column;line-height:1;
        }
        .wb-logo-name{font-size:26px;font-weight:950;letter-spacing:-.8px;}
        .wb-logo-sub{margin-top:5px;font-size:11px;font-weight:800;opacity:.86;letter-spacing:.6px;text-transform:uppercase;}

        .wb-search{
            height:48px;
            background:#fff;
            border:1px solid rgba(255,255,255,.55);
            border-radius:999px;
            display:flex;
            align-items:center;
            gap:10px;
            padding:0 18px;
            box-shadow:0 12px 26px rgba(75,0,12,.16);
            min-width:0;
        }
        .wb-search i{color:#444b5a;font-size:18px;}
        .wb-search input{
            width:100%;border:0;outline:0;background:transparent;
            font-size:15px;color:#111827;min-width:0;
        }
        .wb-search input::placeholder{color:#8b92a1;}

        .wb-account-zone{display:flex;align-items:center;gap:9px;min-width:0;justify-content:flex-end;}
        .wb-user-pill,.wb-auth-pill{
            min-height:44px;
            display:inline-flex;align-items:center;justify-content:center;gap:8px;
            padding:0 13px;
            border-radius:999px;
            text-decoration:none!important;
            color:#fff!important;
            background:rgba(255,255,255,.16);
            border:1px solid rgba(255,255,255,.20);
            font-weight:850;
            box-shadow:inset 0 1px 0 rgba(255,255,255,.12);
            transition:.18s ease;
            white-space:nowrap;
        }
        .wb-user-pill:hover,.wb-auth-pill:hover{background:#fff;color:var(--wb-red)!important;transform:translateY(-1px);}
        .wb-avatar{width:30px;height:30px;border-radius:50%;object-fit:cover;background:#fff;}
        .wb-user-name{max-width:120px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;}
        .wb-admin-mini{background:#fff;color:var(--wb-red);font-size:11px;font-weight:950;border-radius:999px;padding:3px 7px;}
        .wb-user-pill:hover .wb-admin-mini{background:var(--wb-red);color:#fff;}

        .wb-nav-shell{
            margin-top:12px;
            background:rgba(255,255,255,.96);
            border:1px solid rgba(255,255,255,.50);
            border-radius:22px;
            padding:8px;
            box-shadow:0 12px 30px rgba(83,0,10,.18);
        }
        .wb-nav{
            display:flex;
            align-items:center;
            gap:7px;
            overflow-x:auto;
            overflow-y:visible;
            scrollbar-width:thin;
            padding-bottom:1px;
        }
        .wb-nav::-webkit-scrollbar{height:6px;}
        .wb-nav::-webkit-scrollbar-thumb{background:#f3a0aa;border-radius:999px;}

        .wb-nav-link,.wb-admin-summary{
            height:40px;
            display:inline-flex;
            align-items:center;
            justify-content:center;
            gap:7px;
            flex:0 0 auto;
            border-radius:14px;
            padding:0 13px;
            color:#1f2937!important;
            text-decoration:none!important;
            font-size:14px;
            font-weight:850;
            border:1px solid transparent;
            background:transparent;
            transition:.16s ease;
            cursor:pointer;
            white-space:nowrap;
        }
        .wb-nav-link i,.wb-admin-summary i{color:var(--wb-red);font-size:16px;}
        .wb-nav-link:hover,.wb-admin-menu[open]>.wb-admin-summary{
            background:linear-gradient(135deg,#fff1f3,#ffe3e7);
            color:var(--wb-red)!important;
            border-color:#ffd1d8;
            transform:translateY(-1px);
        }
        .wb-cart-link{
            background:linear-gradient(135deg,#d70018,#f52b3a)!important;
            color:#fff!important;
            box-shadow:0 8px 18px rgba(215,0,24,.24);
        }
        .wb-cart-link i{color:#fff!important;}
        .wb-cart-link:hover{color:#fff!important;filter:brightness(1.03);}

        .top-strip,.main-header{display:none!important;}

        @media (max-width:1199px){
            .wb-logo-sub{display:none;}
            .wb-logo-name{font-size:23px;}
            .wb-user-name,.wb-logout-text{display:none;}
            .wb-user-pill,.wb-auth-pill{padding:0 12px;}
        }
        @media (max-width:768px){
            .wb-topbar-inner{height:auto;padding:8px 0;align-items:flex-start;}
            .wb-topbar-group:last-child{display:none;}
            .wb-main-row{grid-template-columns:1fr auto;gap:10px;}
            .wb-logo{grid-column:1/2;}
            .wb-account-zone{grid-column:2/3;}
            .wb-search{grid-column:1/-1;grid-row:2;height:46px;}
            .wb-logo-icon{width:42px;height:42px;border-radius:15px;}
            .wb-logo-name{font-size:22px;}
            .wb-nav-shell{border-radius:18px;padding:7px;}
            .wb-nav-link,.wb-admin-summary{height:38px;font-size:13px;padding:0 11px;}
        }
    
        /* Header menu bigger/easier to click */
        .wb-nav-shell {
            padding: 10px 12px 12px !important;
            border-radius: 26px !important;
            overflow-x: auto !important;
            scrollbar-width: thin;
        }

        .wb-nav {
            gap: 10px !important;
            min-height: 62px !important;
            align-items: center !important;
        }

        .wb-nav-link,
        .wb-admin-summary {
            min-height: 48px !important;
            padding: 12px 16px !important;
            border-radius: 18px !important;
            font-size: 15px !important;
            font-weight: 900 !important;
            white-space: nowrap !important;
        }

        .wb-nav-link i,
        .wb-admin-summary i {
            font-size: 17px !important;
        }

        .wb-cart-link {
            min-height: 52px !important;
            padding: 13px 20px !important;
            border-radius: 20px !important;
            font-size: 16px !important;
        }


        @media (max-width: 992px) {
            .wb-nav-shell {
                padding: 10px !important;
            }

            .wb-nav {
                min-height: 58px !important;
            }

            .wb-nav-link,
            .wb-admin-summary {
                min-height: 46px !important;
                padding: 11px 14px !important;
                font-size: 14px !important;
            }
        }

    
        .wb-admin-menu summary{list-style:none;}
        .wb-admin-menu summary::-webkit-details-marker{display:none;}

        /* Admin dropdown kiểu CellphoneS: bấm Quản trị mở bảng lớn + nền mờ */
        .wb-admin-menu {
            position: relative !important;
            z-index: 1060 !important;
        }

        .wb-admin-menu[open]::before {
            content: "";
            position: fixed;
            inset: 0;
            top: 0;
            background: rgba(15, 23, 42, .58);
            backdrop-filter: blur(3px);
            z-index: 1050;
            animation: adminFadeIn .16s ease-out;
        }

        .wb-admin-menu[open] .wb-admin-summary {
            position: relative;
            z-index: 1062;
            background: #ffffff !important;
            color: #dc2626 !important;
            box-shadow: 0 14px 28px rgba(15,23,42,.20);
        }

        .wb-admin-menu[open] .wb-admin-summary .bi-chevron-down {
            transform: rotate(180deg);
        }

        .wb-admin-dropdown {
            position: fixed !important;
            top: 118px !important;
            left: max(24px, calc((100vw - 1220px) / 2 + 10px)) !important;
            width: 360px !important;
            max-height: calc(100vh - 135px) !important;
            overflow-y: auto !important;
            padding: 14px !important;
            border-radius: 22px !important;
            background: #ffffff !important;
            border: 1px solid rgba(226, 232, 240, .92) !important;
            box-shadow: 0 28px 70px rgba(15, 23, 42, .28) !important;
            z-index: 1061 !important;
            display: grid !important;
            gap: 8px !important;
            animation: adminSlideIn .18s ease-out;
        }

        .wb-admin-dropdown-title {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 12px;
            border-radius: 18px;
            background: linear-gradient(135deg, #fff1f2, #ffffff);
            border: 1px solid #ffe4e6;
            margin-bottom: 4px;
        }

        .wb-admin-dropdown-title span {
            width: 44px;
            height: 44px;
            border-radius: 16px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            background: #ef233c;
            color: #ffffff;
            font-size: 21px;
            box-shadow: 0 12px 22px rgba(239, 35, 60, .22);
        }

        .wb-admin-dropdown-title strong {
            display: block;
            font-size: 15px;
            font-weight: 950;
            color: #111827;
            line-height: 1.15;
        }

        .wb-admin-dropdown-title small {
            display: block;
            margin-top: 3px;
            color: #64748b;
            font-size: 12px;
            font-weight: 700;
        }

        .wb-admin-dropdown a {
            min-height: 50px !important;
            display: flex !important;
            align-items: center !important;
            justify-content: space-between !important;
            gap: 12px !important;
            padding: 12px 14px !important;
            border-radius: 16px !important;
            background: #ffffff !important;
            color: #111827 !important;
            text-decoration: none !important;
            font-size: 15px !important;
            font-weight: 900 !important;
            border: 1px solid transparent !important;
            transition: .16s ease !important;
        }

        .wb-admin-dropdown a::after {
            content: "\F285";
            font-family: "bootstrap-icons";
            color: #94a3b8;
            font-size: 14px;
            margin-left: auto;
        }

        .wb-admin-dropdown a i {
            width: 28px;
            color: #ef233c !important;
            font-size: 19px !important;
        }

        .wb-admin-dropdown a:hover {
            background: #fff1f2 !important;
            border-color: #fecdd3 !important;
            color: #dc2626 !important;
            transform: translateX(4px);
        }

        @keyframes adminFadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }

        @keyframes adminSlideIn {
            from { opacity: 0; transform: translateY(-8px) scale(.98); }
            to { opacity: 1; transform: translateY(0) scale(1); }
        }

        @media (max-width: 768px) {
            .wb-admin-dropdown {
                left: 14px !important;
                right: 14px !important;
                width: auto !important;
                top: 96px !important;
                max-height: calc(100vh - 120px) !important;
            }
        }

    
        /* ================= ADMIN MEGA OVERLAY FULL WIDTH ================= */
        .wb-admin-menu { position: static !important; z-index: 1080 !important; }
        .wb-admin-menu summary { list-style: none; }
        .wb-admin-menu summary::-webkit-details-marker { display: none; }

        .wb-admin-menu[open]::before {
            content: "";
            position: fixed;
            inset: 0;
            background: rgba(15, 23, 42, .64);
            backdrop-filter: blur(7px);
            z-index: 1070;
            animation: adminMegaFade .16s ease-out;
        }

        .wb-admin-menu[open] .wb-admin-summary {
            position: relative;
            z-index: 1082;
            background: #ffffff !important;
            color: #dc2626 !important;
            box-shadow: 0 18px 34px rgba(15, 23, 42, .22);
        }

        .wb-admin-menu[open] .wb-admin-summary .bi-chevron-down {
            transform: rotate(180deg);
        }

        .wb-admin-dropdown {
            position: fixed !important;
            top: 118px !important;
            left: max(18px, calc((100vw - 1280px) / 2)) !important;
            right: max(18px, calc((100vw - 1280px) / 2)) !important;
            width: auto !important;
            max-width: 1280px !important;
            max-height: calc(100vh - 140px) !important;
            overflow-y: auto !important;
            z-index: 1081 !important;

            display: grid !important;
            grid-template-columns: 310px 1fr !important;
            gap: 18px !important;

            padding: 18px !important;
            border-radius: 30px !important;
            background: rgba(255,255,255,.96) !important;
            border: 1px solid rgba(255,255,255,.86) !important;
            box-shadow: 0 34px 90px rgba(15, 23, 42, .32) !important;
            backdrop-filter: blur(22px) !important;
            animation: adminMegaSlide .18s ease-out;
        }

        .wb-admin-dropdown-title {
            min-height: 100% !important;
            display: flex !important;
            flex-direction: column !important;
            justify-content: space-between !important;
            gap: 16px !important;
            padding: 22px !important;
            border-radius: 24px !important;
            color: #fff !important;
            background:
                radial-gradient(circle at 80% 20%, rgba(255,255,255,.22), transparent 24%),
                linear-gradient(135deg, #ef233c 0%, #b50018 56%, #111827 100%) !important;
            border: 0 !important;
            margin: 0 !important;
            overflow: hidden !important;
            position: relative !important;
        }

        .wb-admin-dropdown-title::after {
            content: "";
            position: absolute;
            right: -45px;
            bottom: -45px;
            width: 150px;
            height: 150px;
            border-radius: 999px;
            background: rgba(255,255,255,.14);
        }

        .wb-admin-dropdown-title span {
            width: 62px !important;
            height: 62px !important;
            border-radius: 22px !important;
            display: inline-flex !important;
            align-items: center !important;
            justify-content: center !important;
            background: rgba(255,255,255,.18) !important;
            color: #ffffff !important;
            font-size: 28px !important;
            box-shadow: inset 0 0 0 1px rgba(255,255,255,.30), 0 18px 34px rgba(15,23,42,.20) !important;
        }

        .wb-admin-dropdown-title strong {
            display: block !important;
            font-size: 25px !important;
            line-height: 1.08 !important;
            font-weight: 1000 !important;
            letter-spacing: -.8px !important;
            color: #fff !important;
        }

        .wb-admin-dropdown-title small {
            display: block !important;
            margin-top: 8px !important;
            color: rgba(255,255,255,.82) !important;
            font-size: 14px !important;
            font-weight: 750 !important;
        }

        .wb-admin-dropdown-title .admin-mega-hint {
            position: relative;
            z-index: 2;
            margin-top: auto;
            padding: 12px 14px;
            border-radius: 18px;
            background: rgba(255,255,255,.14);
            border: 1px solid rgba(255,255,255,.18);
            font-weight: 800;
            color: rgba(255,255,255,.92);
        }

        .wb-admin-mega-grid {
            display: grid !important;
            grid-template-columns: repeat(3, minmax(0, 1fr)) !important;
            gap: 12px !important;
        }

        .wb-admin-dropdown a {
            min-height: 78px !important;
            display: flex !important;
            align-items: center !important;
            justify-content: flex-start !important;
            gap: 14px !important;
            padding: 15px 16px !important;
            border-radius: 22px !important;
            background: #ffffff !important;
            color: #111827 !important;
            text-decoration: none !important;
            font-size: 15px !important;
            font-weight: 950 !important;
            border: 1px solid rgba(226,232,240,.82) !important;
            box-shadow: 0 14px 32px rgba(15,23,42,.06) !important;
            transition: .16s ease !important;
        }

        .wb-admin-dropdown a::after { content: none !important; }

        .wb-admin-dropdown a i {
            flex: 0 0 46px !important;
            width: 46px !important;
            height: 46px !important;
            border-radius: 16px !important;
            display: inline-flex !important;
            align-items: center !important;
            justify-content: center !important;
            color: #ef233c !important;
            font-size: 22px !important;
            background: #fff1f2 !important;
        }

        .wb-admin-dropdown a:hover {
            background: linear-gradient(135deg, #fff1f2, #ffffff) !important;
            border-color: #fecdd3 !important;
            color: #dc2626 !important;
            transform: translateY(-3px) !important;
            box-shadow: 0 22px 48px rgba(239, 35, 60, .13) !important;
        }

        .wb-admin-dropdown a:hover i {
            background: #ef233c !important;
            color: #ffffff !important;
        }

        @keyframes adminMegaFade { from { opacity: 0; } to { opacity: 1; } }
        @keyframes adminMegaSlide {
            from { opacity: 0; transform: translateY(-12px) scale(.985); }
            to { opacity: 1; transform: translateY(0) scale(1); }
        }

        @media (max-width: 992px) {
            .wb-admin-dropdown {
                top: 96px !important;
                left: 12px !important;
                right: 12px !important;
                grid-template-columns: 1fr !important;
                max-height: calc(100vh - 118px) !important;
            }
            .wb-admin-mega-grid {
                grid-template-columns: 1fr !important;
            }
            .wb-admin-dropdown-title {
                min-height: 180px !important;
            }
        }

    
        /* =============== ADMIN MENU OUTSIDE NAV - NOT CLIPPED =============== */
        .wb-admin-open-btn{
            border:0;
            cursor:pointer;
            white-space:nowrap;
        }

        .admin-outside-backdrop{
            position:fixed;
            inset:0;
            z-index:3000;
            background:rgba(15,23,42,.66);
            backdrop-filter:blur(8px);
            opacity:0;
            pointer-events:none;
            transition:.16s ease;
        }

        .admin-outside-backdrop.show{
            opacity:1;
            pointer-events:auto;
        }

        .admin-outside-panel{
            position:fixed;
            z-index:3001;
            top:118px;
            left:max(18px, calc((100vw - 1280px) / 2));
            right:max(18px, calc((100vw - 1280px) / 2));
            max-width:1280px;
            max-height:calc(100vh - 138px);
            overflow-y:auto;
            display:grid;
            grid-template-columns:320px 1fr;
            gap:18px;
            padding:18px;
            border-radius:32px;
            background:rgba(255,255,255,.96);
            border:1px solid rgba(255,255,255,.88);
            box-shadow:0 38px 100px rgba(15,23,42,.34);
            backdrop-filter:blur(24px);
            transform:translateY(-16px) scale(.985);
            opacity:0;
            pointer-events:none;
            transition:.18s ease;
        }

        .admin-outside-panel.show{
            transform:translateY(0) scale(1);
            opacity:1;
            pointer-events:auto;
        }

        .admin-outside-hero{
            min-height:360px;
            position:relative;
            overflow:hidden;
            border-radius:26px;
            padding:24px;
            color:#fff;
            background:
                radial-gradient(circle at 82% 18%,rgba(255,255,255,.22),transparent 24%),
                linear-gradient(135deg,#ef233c 0%,#b50018 52%,#111827 100%);
            display:flex;
            flex-direction:column;
            justify-content:space-between;
        }

        .admin-outside-hero::after{
            content:"";
            position:absolute;
            right:-50px;
            bottom:-50px;
            width:170px;
            height:170px;
            border-radius:999px;
            background:rgba(255,255,255,.13);
        }

        .admin-outside-hero-icon{
            width:66px;
            height:66px;
            border-radius:24px;
            display:flex;
            align-items:center;
            justify-content:center;
            background:rgba(255,255,255,.18);
            box-shadow:inset 0 0 0 1px rgba(255,255,255,.28);
            font-size:30px;
        }

        .admin-outside-hero h3{
            font-size:28px;
            line-height:1.05;
            font-weight:1000;
            letter-spacing:-.8px;
            margin:18px 0 8px;
            position:relative;
            z-index:2;
        }

        .admin-outside-hero p{
            margin:0;
            color:rgba(255,255,255,.82);
            font-weight:750;
            position:relative;
            z-index:2;
        }

        .admin-outside-hint{
            position:relative;
            z-index:2;
            padding:13px 15px;
            border-radius:18px;
            background:rgba(255,255,255,.14);
            border:1px solid rgba(255,255,255,.18);
            font-weight:850;
        }

        .admin-outside-grid{
            display:grid;
            grid-template-columns:repeat(3,minmax(0,1fr));
            gap:12px;
        }

        .admin-outside-grid a{
            min-height:78px;
            display:flex;
            align-items:center;
            gap:14px;
            padding:15px 16px;
            border-radius:22px;
            background:#fff;
            color:#111827;
            text-decoration:none;
            font-size:15px;
            font-weight:950;
            border:1px solid rgba(226,232,240,.86);
            box-shadow:0 14px 32px rgba(15,23,42,.06);
            transition:.16s ease;
        }

        .admin-outside-grid a i{
            flex:0 0 46px;
            width:46px;
            height:46px;
            border-radius:16px;
            display:flex;
            align-items:center;
            justify-content:center;
            background:#fff1f2;
            color:#ef233c;
            font-size:22px;
        }

        .admin-outside-grid a:hover{
            transform:translateY(-3px);
            background:linear-gradient(135deg,#fff1f2,#ffffff);
            border-color:#fecdd3;
            color:#dc2626;
            box-shadow:0 22px 48px rgba(239,35,60,.13);
        }

        .admin-outside-grid a:hover i{
            background:#ef233c;
            color:#fff;
        }

        .admin-outside-close{
            position:absolute;
            top:14px;
            right:14px;
            z-index:3;
            width:42px;
            height:42px;
            border:0;
            border-radius:16px;
            background:rgba(255,255,255,.18);
            color:#fff;
            font-size:20px;
        }

        @media(max-width:992px){
            .admin-outside-panel{
                top:96px;
                left:12px;
                right:12px;
                grid-template-columns:1fr;
                max-height:calc(100vh - 118px);
            }
            .admin-outside-grid{
                grid-template-columns:1fr;
            }
            .admin-outside-hero{
                min-height:210px;
            }
        }

    </style>

    <script>
        window.API_BASE_URL = '<?php echo BASE_URL; ?>';
    </script>
</head>

<body>
<div class="wb-topbar">
    <div class="container-xl wb-topbar-inner">
        <div class="wb-topbar-group">
            <span><i class="bi bi-truck"></i> Giao nhanh 2h</span>
            <span><i class="bi bi-receipt-cutoff"></i> Xuất VAT đầy đủ</span>
            <span><i class="bi bi-arrow-repeat"></i> Thu cũ đổi mới</span>
        </div>
        <div class="wb-topbar-group">
            <span><i class="bi bi-geo-alt"></i> Cửa hàng gần bạn</span>
            <span><i class="bi bi-telephone"></i> 1800 6736</span>
        </div>
    </div>
</div>

<header class="wb-header">
    <div class="container-xl wb-header-inner">
        <div class="wb-main-row">
            <a class="wb-logo" href="<?php echo BASE_URL; ?>/Product/list" aria-label="Trang chủ TechStore">
                <span class="wb-logo-icon"><i class="bi bi-phone"></i></span>
                <span class="wb-logo-text">
                    <span class="wb-logo-name">TechStore</span>
                    <span class="wb-logo-sub">Mobile Store Premium</span>
                </span>
            </a>

            <form class="wb-search" action="<?php echo BASE_URL; ?>/Product/list" method="get">
                <i class="bi bi-search"></i>
                <input id="globalSearch" name="search" type="search" placeholder="Bạn muốn mua gì hôm nay?" value="<?php echo $currentSearch; ?>">
            </form>

            <div class="wb-account-zone">
                <?php if ($isLoggedIn): ?>
                    <a class="wb-user-pill" href="<?php echo BASE_URL; ?>/User/profile" title="Hồ sơ cá nhân">
                        <?php if (!empty($_SESSION['avatar'])): ?>
                            <img src="<?php echo BASE_URL; ?>/public/uploads/avatars/<?php echo htmlspecialchars($_SESSION['avatar'], ENT_QUOTES, 'UTF-8'); ?>" alt="Avatar" class="wb-avatar">
                        <?php else: ?>
                            <i class="bi bi-person-check"></i>
                        <?php endif; ?>
                        <span class="wb-user-name"><?php echo htmlspecialchars($displayName, ENT_QUOTES, 'UTF-8'); ?></span>
                        <?php if (!empty($_SESSION['cultivation_level'])): ?><span class="wb-admin-mini">Lv. <?php echo (int)$_SESSION['cultivation_level']; ?></span><?php endif; ?>
                        <?php if ($isAdmin): ?><span class="wb-admin-mini">Admin</span><?php endif; ?>
                    </a>
                    <a class="wb-auth-pill" href="<?php echo BASE_URL; ?>/Auth/logout" onclick="localStorage.removeItem(\'api_token\');localStorage.removeItem(\'api_user\');" onclick="localStorage.removeItem(\'api_token\');localStorage.removeItem(\'api_user\');" title="Đăng xuất">
                        <i class="bi bi-box-arrow-right"></i>
                        <span class="wb-logout-text">Đăng xuất</span>
                    </a>
                <?php else: ?>
                    <a class="wb-auth-pill" href="<?php echo BASE_URL; ?>/Auth/login"><i class="bi bi-person-circle"></i><span>Đăng nhập</span></a>
                    <a class="wb-auth-pill" href="<?php echo BASE_URL; ?>/Auth/register"><i class="bi bi-person-plus"></i><span>Đăng ký</span></a>
                <?php endif; ?>
            </div>
        </div>

        <div class="wb-nav-shell">
            <nav class="wb-nav" aria-label="Điều hướng chính">
                <a class="wb-nav-link" href="<?php echo BASE_URL; ?>/Product/list"><i class="bi bi-house-door"></i> Trang chủ</a>
                <a class="wb-nav-link" href="<?php echo BASE_URL; ?>/Product/list"><i class="bi bi-grid-3x3-gap"></i> Sản phẩm</a>

                <?php if ($isAdmin): ?>
                    <button type="button" class="wb-admin-summary wb-admin-open-btn" id="openAdminMegaMenu">
                            <i class="bi bi-speedometer2"></i> Quản trị <i class="bi bi-chevron-down"></i>
                        </button>
                <?php endif; ?>

                <a class="wb-nav-link" href="<?php echo BASE_URL; ?>/Advanced/compare"><i class="bi bi-arrow-left-right"></i> So sánh</a>
                <a class="wb-nav-link" href="<?php echo BASE_URL; ?>/Advanced/warranty"><i class="bi bi-shield-check"></i> Bảo hành</a>
                <a class="wb-nav-link wb-cart-link" href="<?php echo BASE_URL; ?>/Cart/index"><i class="bi bi-cart3"></i> Giỏ hàng</a>

                <?php if ($isLoggedIn): ?>
                    <a class="wb-nav-link" href="<?php echo BASE_URL; ?>/Advanced/orders"><i class="bi bi-bag-check"></i> Đơn hàng</a>
                    <a class="wb-nav-link" href="<?php echo BASE_URL; ?>/Advanced/wishlist"><i class="bi bi-heart"></i> Yêu thích</a>
                    <a class="wb-nav-link" href="<?php echo BASE_URL; ?>/Advanced/notifications"><i class="bi bi-bell"></i> Thông báo</a>
                <?php endif; ?>

                <a class="wb-nav-link" href="<?php echo BASE_URL; ?>/Advanced/support"><i class="bi bi-headset"></i> Hỗ trợ</a>
                <a class="wb-nav-link" href="<?php echo BASE_URL; ?>/Advanced/faq"><i class="bi bi-question-circle"></i> FAQ</a>
            </nav>
        </div>
    </div>
</header>

<?php if ($isAdmin): ?>
<div class="admin-outside-backdrop" id="adminOutsideBackdrop"></div>
<div class="admin-outside-panel" id="adminOutsidePanel" aria-hidden="true">
    <div class="admin-outside-hero">
        <button type="button" class="admin-outside-close" id="closeAdminMegaMenu"><i class="bi bi-x-lg"></i></button>
        <div>
            <div class="admin-outside-hero-icon"><i class="bi bi-command"></i></div>
            <h3>Trung tâm<br>quản trị</h3>
            <p>Menu Admin nằm ngoài thanh nav, không bị kẹt trong khung cuộn.</p>
        </div>
        <div class="admin-outside-hint">
            <i class="bi bi-mouse2"></i> Chọn nhanh chức năng cần quản trị
        </div>
    </div>
    <div class="admin-outside-grid">
                <a href="<?php echo BASE_URL; ?>/Admin/center"><i class="bi bi-command"></i> Trung tâm quản trị</a>
                <a href="<?php echo BASE_URL; ?>/Admin/productsCenter"><i class="bi bi-box-seam"></i> Sản phẩm</a>
                <a href="<?php echo BASE_URL; ?>/Admin/inventoryCenter"><i class="bi bi-building-gear"></i> Kho hàng Pro</a>
                <a href="<?php echo BASE_URL; ?>/Admin/ordersCenter"><i class="bi bi-receipt-cutoff"></i> Đơn hàng Pro</a>
                <a href="<?php echo BASE_URL; ?>/Admin/customersCenter"><i class="bi bi-people"></i> Khách hàng</a>
                <a href="<?php echo BASE_URL; ?>/Admin/marketingCenter"><i class="bi bi-megaphone"></i> Marketing</a>
                <a href="<?php echo BASE_URL; ?>/Admin/reportsCenter"><i class="bi bi-graph-up-arrow"></i> Báo cáo</a>
                <a href="<?php echo BASE_URL; ?>/Admin/staffCenter"><i class="bi bi-shield-lock"></i> Phân quyền</a>
                <a href="<?php echo BASE_URL; ?>/Admin/settingsCenter"><i class="bi bi-sliders"></i> Cài đặt</a>
                <a href="<?php echo BASE_URL; ?>/Admin/supportCenter"><i class="bi bi-chat-dots"></i> Hỗ trợ</a>
                <a href="<?php echo BASE_URL; ?>/Admin/dashboard"><i class="bi bi-bar-chart-line"></i> Dashboard</a>
                <a href="<?php echo BASE_URL; ?>/Category/list"><i class="bi bi-grid"></i> Quản lý danh mục</a>
                <a href="<?php echo BASE_URL; ?>/Admin/orders"><i class="bi bi-receipt"></i> Quản lý đơn hàng</a>
                <a href="<?php echo BASE_URL; ?>/User/admin"><i class="bi bi-people"></i> Quản lý người dùng</a>
                <a href="<?php echo BASE_URL; ?>/Admin/vouchers"><i class="bi bi-ticket-perforated"></i> Voucher</a>
                <a href="<?php echo BASE_URL; ?>/Admin/banners"><i class="bi bi-images"></i> Banner</a>
                <a href="<?php echo BASE_URL; ?>/Admin/inventory"><i class="bi bi-box-seam"></i> Kho hàng</a>
                <a href="<?php echo BASE_URL; ?>/Admin/content"><i class="bi bi-layout-text-window"></i> Nội dung</a>
    </div>
</div>
<?php endif; ?>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const openBtn = document.getElementById('openAdminMegaMenu');
    const closeBtn = document.getElementById('closeAdminMegaMenu');
    const backdrop = document.getElementById('adminOutsideBackdrop');
    const panel = document.getElementById('adminOutsidePanel');

    function openAdminMenu() {
        if (!panel || !backdrop) return;
        panel.classList.add('show');
        backdrop.classList.add('show');
        panel.setAttribute('aria-hidden', 'false');
        document.body.style.overflow = 'hidden';
    }

    function closeAdminMenu() {
        if (!panel || !backdrop) return;
        panel.classList.remove('show');
        backdrop.classList.remove('show');
        panel.setAttribute('aria-hidden', 'true');
        document.body.style.overflow = '';
    }

    if (openBtn) openBtn.addEventListener('click', openAdminMenu);
    if (closeBtn) closeBtn.addEventListener('click', closeAdminMenu);
    if (backdrop) backdrop.addEventListener('click', closeAdminMenu);

    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') closeAdminMenu();
    });
});
</script>


<script>
document.addEventListener('DOMContentLoaded', function () {
    const adminMenu = document.querySelector('.wb-admin-menu');
    if (!adminMenu) return;

    document.addEventListener('click', function (e) {
        if (!adminMenu.open) return;
        if (!adminMenu.contains(e.target)) {
            adminMenu.removeAttribute('open');
        }
    });

    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape' && adminMenu.open) {
            adminMenu.removeAttribute('open');
        }
    });
});
</script>

<main class="container-xl py-4">
<?php if (!empty($_SESSION['flash_success'])): ?>
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <?php echo htmlspecialchars($_SESSION['flash_success'], ENT_QUOTES, 'UTF-8'); unset($_SESSION['flash_success']); ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
<?php endif; ?>
<?php if (!empty($_SESSION['flash_error'])): ?>
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <?php echo htmlspecialchars($_SESSION['flash_error'], ENT_QUOTES, 'UTF-8'); unset($_SESSION['flash_error']); ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
<?php endif; ?>
