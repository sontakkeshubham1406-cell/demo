<?php
/**
 * Customer Sign-In Screen
 */
?>
<div class="max-w-md mx-auto px-4 py-16 sm:py-24">
    <div class="bg-white border border-subtleBorder rounded-3xl p-8 sm:p-10 shadow-sm space-y-6">
        <div class="text-center space-y-1">
            <span class="text-[10px] font-bold tracking-[0.25em] text-champagne uppercase">Welcome Back</span>
            <h1 class="font-serif text-3xl font-bold text-obsidian">Sign In</h1>
            <p class="text-xs text-mutedText">Access your wardrobe, order tracking, and private styling edits.</p>
        </div>

        <form action="/login" method="POST" class="space-y-4 text-xs">
            <div>
                <label class="block font-semibold text-obsidian mb-1">Email Address</label>
                <input type="email" name="email" id="loginEmail" required placeholder="customer@example.com"
                       class="w-full px-3.5 py-2.5 bg-[#FAF9F6] border border-subtleBorder rounded-lg focus:outline-none focus:border-obsidian">
            </div>

            <div>
                <div class="flex justify-between items-center mb-1">
                    <label class="font-semibold text-obsidian">Password</label>
                    <a href="/forgot-password" class="text-mutedText hover:text-obsidian underline text-[11px]">Forgot?</a>
                </div>
                <input type="password" name="password" id="loginPassword" required placeholder="••••••••"
                       class="w-full px-3.5 py-2.5 bg-[#FAF9F6] border border-subtleBorder rounded-lg focus:outline-none focus:border-obsidian">
            </div>

            <button type="submit" class="w-full py-3.5 bg-obsidian text-white text-xs font-bold uppercase tracking-[0.2em] rounded-xl hover:bg-slateDark transition-all shadow-md">
                Sign In
            </button>
        </form>

        <!-- Quick 1-Click Demo Fill -->
        <div class="bg-[#FAF9F6] p-4 rounded-xl border border-subtleBorder text-center space-y-2">
            <p class="text-[11px] font-bold text-obsidian uppercase tracking-wider">Instant Demo Credentials</p>
            <div class="flex gap-2">
                <button type="button" onclick="document.getElementById('loginEmail').value='customer@example.com';document.getElementById('loginPassword').value='Customer@123';" 
                        class="flex-1 py-1.5 bg-white border border-subtleBorder text-[11px] font-semibold text-obsidian rounded hover:bg-neutral-100">
                    Customer Demo
                </button>
                <button type="button" onclick="document.getElementById('loginEmail').value='admin@velorisluxe.com';document.getElementById('loginPassword').value='Admin@123';" 
                        class="flex-1 py-1.5 bg-white border border-subtleBorder text-[11px] font-semibold text-champagne rounded hover:bg-neutral-100">
                    Admin Demo
                </button>
            </div>
        </div>

        <div class="text-center text-xs text-mutedText pt-2 border-t border-subtleBorder">
            New to VELORIS LUXE? 
            <a href="/register" class="text-obsidian font-bold underline ml-1">Create an Account</a>
        </div>
    </div>
</div>

