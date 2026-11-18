<?php
/**
 * Admin Portal Login
 */
?>
<!DOCTYPE html>
<html lang="en" class="h-full bg-slate-900 text-white antialiased">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Portal Authentication | <?= STORE_NAME ?></title>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;700&family=Playfair+Display:wght@700&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/lucide@latest"></script>
</head>
<body class="flex min-h-full items-center justify-center p-4 font-sans bg-slate-950">
    <div class="w-full max-w-md bg-slate-900 border border-slate-800 rounded-3xl p-8 sm:p-10 shadow-2xl space-y-6">
        <div class="text-center space-y-2">
            <div class="w-12 h-12 mx-auto rounded-2xl bg-amber-500 text-slate-950 flex items-center justify-center font-bold text-2xl font-serif">
                A
            </div>
            <h1 class="font-serif text-2xl font-bold tracking-wider text-white uppercase"><?= STORE_NAME ?></h1>
            <p class="text-xs text-slate-400">Back-Office Command Console & Inventory Ledger</p>
        </div>

        <?php if (!empty($_SESSION['flash_error'])): ?>
            <div class="bg-rose-950/50 border border-rose-800 text-rose-300 p-3 rounded-xl text-xs text-center">
                <?= htmlspecialchars($_SESSION['flash_error']) ?>
            </div>
            <?php unset($_SESSION['flash_error']); ?>
        <?php endif; ?>

        <form action="/admin/login" method="POST" class="space-y-4 text-xs">
            <div>
                <label class="block font-semibold text-slate-300 mb-1">Administrative Email</label>
                <input type="email" name="email" id="adminEmail" required placeholder="admin@velorisluxe.com" 
                       class="w-full px-4 py-3 bg-slate-800 border border-slate-700 text-white rounded-xl focus:outline-none focus:border-amber-500">
            </div>

            <div>
                <label class="block font-semibold text-slate-300 mb-1">Password</label>
                <input type="password" name="password" id="adminPassword" required placeholder="••••••••" 
                       class="w-full px-4 py-3 bg-slate-800 border border-slate-700 text-white rounded-xl focus:outline-none focus:border-amber-500">
            </div>

            <button type="submit" class="w-full py-3.5 bg-amber-500 hover:bg-amber-400 text-slate-950 text-xs font-bold uppercase tracking-[0.2em] rounded-xl transition-all shadow-lg font-sans">
                Access Admin Console
            </button>
        </form>

        <div class="bg-slate-800/80 p-4 rounded-xl border border-slate-700/60 text-center space-y-2">
            <p class="text-[11px] font-bold text-slate-300 uppercase tracking-wider">Demo Super Admin Access</p>
            <button type="button" onclick="document.getElementById('adminEmail').value='admin@velorisluxe.com';document.getElementById('adminPassword').value='Admin@123';"
                    class="w-full py-2 bg-slate-700 hover:bg-slate-600 text-amber-300 rounded-lg text-xs font-mono font-semibold transition-colors">
                Auto-Fill: admin@velorisluxe.com / Admin@123
            </button>
        </div>

        <div class="text-center pt-2">
            <a href="/" class="text-xs text-slate-400 hover:text-white inline-flex items-center gap-1">
                <span>&larr; Return to Customer Storefront</span>
            </a>
        </div>
    </div>
</body>
</html>

